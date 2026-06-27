<?php

if (!defined('ABSPATH')) {
    exit;
}

final class SI_Plugin
{
    private static $instance;

    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        add_action('wp_enqueue_scripts', array($this, 'register_assets'));
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('init', array($this, 'maybe_upgrade'));
        add_action('wp_ajax_si_calculate', array($this, 'ajax_calculate'));
        add_action('wp_ajax_nopriv_si_calculate', array($this, 'ajax_calculate'));
        add_action('wp_ajax_si_register_integrator', array($this, 'ajax_register_integrator'));
        add_action('wp_ajax_nopriv_si_register_integrator', array($this, 'ajax_register_integrator'));
        add_action('wp_ajax_si_search_municipalities', array($this, 'ajax_search_municipalities'));
        add_action('wp_ajax_nopriv_si_search_municipalities', array($this, 'ajax_search_municipalities'));
        add_action('wp_ajax_si_request_quote', array($this, 'ajax_request_quote'));
        add_action('wp_ajax_nopriv_si_request_quote', array($this, 'ajax_request_quote'));
        add_action('template_redirect', array($this, 'render_public_quote'));

        add_shortcode('si_solar_calculator', array($this, 'calculator_shortcode'));
        add_shortcode('si_integrator_registration', array($this, 'registration_shortcode'));
        add_shortcode('si_integrator_dashboard', array($this, 'dashboard_shortcode'));
    }

    public static function activate()
    {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $charset = $wpdb->get_charset_collate();
        $simulations = $wpdb->prefix . 'si_simulations';
        $municipalities = $wpdb->prefix . 'si_municipalities';
        $location_energy = $wpdb->prefix . 'si_location_energy';
        $quotes = $wpdb->prefix . 'si_quotes';

        dbDelta("CREATE TABLE {$simulations} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned NOT NULL DEFAULT 0,
            customer_name varchar(190) NOT NULL DEFAULT '',
            customer_email varchar(190) NOT NULL DEFAULT '',
            input_json longtext NOT NULL,
            result_json longtext NOT NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY created_at (created_at)
        ) {$charset};");

        dbDelta("CREATE TABLE {$municipalities} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            municipality varchar(190) NOT NULL,
            state char(2) NOT NULL,
            latitude decimal(10,6) DEFAULT NULL,
            longitude decimal(10,6) DEFAULT NULL,
            hsp decimal(6,3) DEFAULT NULL,
            source varchar(190) NOT NULL DEFAULT '',
            updated_at datetime NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY municipality_state (municipality, state),
            KEY state (state)
        ) {$charset};");

        dbDelta("CREATE TABLE {$location_energy} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            municipality varchar(190) NOT NULL,
            state char(2) NOT NULL,
            distributor varchar(190) NOT NULL DEFAULT '',
            regulated_tariff decimal(10,6) DEFAULT NULL,
            tariff_start date DEFAULT NULL,
            tariff_end date DEFAULT NULL,
            multiple_distributors tinyint(1) NOT NULL DEFAULT 0,
            PRIMARY KEY (id),
            UNIQUE KEY municipality_state (municipality, state),
            KEY state (state),
            KEY distributor (distributor)
        ) {$charset};");

        dbDelta("CREATE TABLE {$quotes} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            public_token char(64) NOT NULL,
            user_id bigint(20) unsigned NOT NULL DEFAULT 0,
            customer_name varchar(190) NOT NULL,
            customer_phone varchar(40) NOT NULL,
            customer_email varchar(190) NOT NULL,
            preferred_channel varchar(20) NOT NULL DEFAULT 'whatsapp',
            municipality varchar(190) NOT NULL DEFAULT '',
            state char(2) NOT NULL DEFAULT '',
            input_json longtext NOT NULL,
            result_json longtext NOT NULL,
            status varchar(30) NOT NULL DEFAULT 'requested',
            consent_at datetime NOT NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY public_token (public_token),
            KEY user_id (user_id),
            KEY status (status),
            KEY created_at (created_at)
        ) {$charset};");

        add_role('solar_integrator', __('Integrador solar', 'solar-integradores'), array(
            'read' => true,
            'si_manage_own_simulations' => true,
        ));

        if (!get_option('si_settings')) {
            add_option('si_settings', array(
                'default_hsp' => 4.8,
                'default_losses_percent' => 17,
                'default_module_power_w' => 550,
                'default_tariff' => 1.00,
                'tariff_additions_percent' => 25,
                'default_public_lighting_fee' => 25,
                'price_range_percent' => 12,
                'price_bands' => SI_Calculator::default_price_bands(),
                'registration_enabled' => 1,
                'quote_notification_email' => get_option('admin_email'),
                'business_whatsapp' => '',
                'proposal_valid_days' => 10,
                'cash_discount_percent' => 5,
                'mercado_pago_pix_fee' => 0.99,
                'mercado_pago_card_processing_fee' => 3.99,
                'mercado_pago_installment_fees' => SI_Proposal::default_installment_fees(),
                'financial_discount_rate' => 8,
                'tariff_inflation_percent' => 5,
                'annual_degradation_percent' => 0.8,
                'annual_maintenance_percent' => 0.5,
                'module_area_m2' => 2.58,
                'default_module_model' => 'Módulo N-Type 550 W ou superior',
                'default_inverter_model' => 'Inversor compatível com o projeto',
                'supplier_catalog' => '',
            ));
        } else {
            $settings = get_option('si_settings', array());
            if (empty($settings['default_tariff'])) {
                $settings['default_tariff'] = 1.00;
            }
            if (!isset($settings['tariff_additions_percent'])) {
                $settings['tariff_additions_percent'] = 25;
            }
            if (!isset($settings['default_public_lighting_fee'])) {
                $settings['default_public_lighting_fee'] = 25;
            }
            if (empty($settings['quote_notification_email'])) {
                $settings['quote_notification_email'] = get_option('admin_email');
            }
            if (!isset($settings['business_whatsapp'])) {
                $settings['business_whatsapp'] = '';
            }
            update_option('si_settings', $settings);
        }

        self::seed_municipalities();
        self::seed_location_energy();
        update_option('si_db_version', SI_VERSION);
    }

    public static function deactivate()
    {
        // Data is intentionally retained.
    }

    public function maybe_upgrade()
    {
        if (get_option('si_db_version') !== SI_VERSION && current_user_can('manage_options')) {
            self::activate();
        }
    }

    public function register_assets()
    {
        wp_register_style('si-calculator', SI_URL . 'assets/css/calculator.css', array(), SI_VERSION);
        wp_register_script('si-calculator', SI_URL . 'assets/js/calculator.js', array(), SI_VERSION, true);
        wp_localize_script('si-calculator', 'SI_CONFIG', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('si_calculator'),
            'currency' => 'BRL',
            'locale' => 'pt-BR',
        ));
    }

    public function calculator_shortcode()
    {
        $settings = $this->settings();
        wp_enqueue_style('si-calculator');
        wp_enqueue_script('si-calculator');

        ob_start();
        include SI_DIR . 'templates/calculator.php';
        return ob_get_clean();
    }

    public function registration_shortcode()
    {
        if (is_user_logged_in()) {
            return '<div class="si-notice">' . esc_html__('Você já está conectado.', 'solar-integradores') . '</div>';
        }
        if (empty($this->settings()['registration_enabled'])) {
            return '<div class="si-notice">' . esc_html__('O cadastro de integradores está temporariamente fechado.', 'solar-integradores') . '</div>';
        }

        wp_enqueue_style('si-calculator');
        wp_enqueue_script('si-calculator');
        ob_start();
        include SI_DIR . 'templates/registration.php';
        return ob_get_clean();
    }

    public function dashboard_shortcode()
    {
        if (!is_user_logged_in()) {
            return sprintf(
                '<div class="si-notice">%s <a href="%s">%s</a></div>',
                esc_html__('Entre para consultar suas simulações.', 'solar-integradores'),
                esc_url(wp_login_url(get_permalink())),
                esc_html__('Fazer login', 'solar-integradores')
            );
        }

        global $wpdb;
        $table = $wpdb->prefix . 'si_simulations';
        $items = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table} WHERE user_id = %d ORDER BY created_at DESC LIMIT 100",
            get_current_user_id()
        ));

        wp_enqueue_style('si-calculator');
        ob_start();
        include SI_DIR . 'templates/dashboard.php';
        return ob_get_clean();
    }

    public function ajax_calculate()
    {
        check_ajax_referer('si_calculator', 'nonce');
        $input = $this->sanitize_calculation($_POST);
        if (!$input['municipality'] || !$input['state']) {
            wp_send_json_error(array(
                'message' => __('Selecione o município na lista de sugestões.', 'solar-integradores'),
            ), 422);
        }
        $settings = $this->settings();
        $location = $this->location_energy($input['municipality'], $input['state']);
        if ($input['customer_type'] !== 'commercial_demand') {
            $regulated_tariff = $location ? (float) $location['regulated_tariff'] : 0;
            $input['energy_tariff'] = $regulated_tariff > 0
                ? $regulated_tariff * (1 + ((float) $settings['tariff_additions_percent'] / 100))
                : (float) $settings['default_tariff'];
            $input['public_lighting_fee'] = $this->public_lighting_fee(
                $input['state'],
                (float) $settings['default_public_lighting_fee']
            );
        } else {
            $off_peak = (float) str_replace(',', '.', $input['off_peak_consumption']);
            $peak = (float) str_replace(',', '.', $input['peak_consumption']);
            $off_peak_te = (float) str_replace(',', '.', $input['off_peak_te']);
            $peak_te = (float) str_replace(',', '.', $input['peak_te']);
            $total = $off_peak + $peak;
            $input['energy_tariff'] = $total > 0
                ? (($off_peak * $off_peak_te) + ($peak * $peak_te)) / $total
                : (float) $settings['default_tariff'];
        }
        $input['hsp'] = $settings['default_hsp'];
        $input['losses_percent'] = $settings['default_losses_percent'];
        $input['module_power_w'] = $settings['default_module_power_w'];
        $input['price_bands'] = $settings['price_bands'];
        $input['price_range_percent'] = $settings['price_range_percent'];
        $result = SI_Calculator::calculate($input);

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()), 422);
        }
        $result['distributor'] = $location['distributor'] ?? __('Não identificada', 'solar-integradores');
        $result['regulated_tariff'] = isset($location['regulated_tariff']) ? round((float) $location['regulated_tariff'], 6) : null;
        $result['estimated_energy_tariff'] = round((float) $input['energy_tariff'], 6);
        $result['estimated_public_lighting_fee'] = round((float) $input['public_lighting_fee'], 2);
        $result['tariff_end'] = $location['tariff_end'] ?? null;
        $result['multiple_distributors'] = !empty($location['multiple_distributors']);
        if ($result['multiple_distributors']) {
            $result['warnings'][] = __('Este município possui mais de uma distribuidora registrada. A estimativa usa a distribuidora predominante; confirme pela conta de energia.', 'solar-integradores');
        }
        if (!$location || empty($location['regulated_tariff'])) {
            $result['warnings'][] = __('Não foi encontrada tarifa vigente para esta localização. Foi usada a tarifa média configurada no painel.', 'solar-integradores');
        }

        $quote_token = wp_generate_password(32, false, false);
        set_transient('si_quote_' . $quote_token, array(
            'input' => $input,
            'result' => $result,
        ), 2 * HOUR_IN_SECONDS);
        $result['quote_token'] = $quote_token;

        if (is_user_logged_in()) {
            $this->save_simulation($input, $result);
        }
        wp_send_json_success($result);
    }

    public function ajax_register_integrator()
    {
        check_ajax_referer('si_calculator', 'nonce');
        if (empty($this->settings()['registration_enabled'])) {
            wp_send_json_error(array('message' => __('Cadastros desativados.', 'solar-integradores')), 403);
        }

        $email = sanitize_email(wp_unslash($_POST['email'] ?? ''));
        $name = sanitize_text_field(wp_unslash($_POST['name'] ?? ''));
        $password = (string) wp_unslash($_POST['password'] ?? '');
        if (!$email || !$name || strlen($password) < 8) {
            wp_send_json_error(array('message' => __('Informe nome, e-mail válido e senha com ao menos 8 caracteres.', 'solar-integradores')), 422);
        }
        if (email_exists($email)) {
            wp_send_json_error(array('message' => __('Este e-mail já está cadastrado.', 'solar-integradores')), 409);
        }

        $base = sanitize_user(strstr($email, '@', true), true);
        $login = $base ?: 'integrador';
        $suffix = 1;
        while (username_exists($login)) {
            $login = $base . $suffix;
            $suffix++;
        }

        $user_id = wp_insert_user(array(
            'user_login' => $login,
            'user_email' => $email,
            'display_name' => $name,
            'user_pass' => $password,
            'role' => 'solar_integrator',
        ));
        if (is_wp_error($user_id)) {
            wp_send_json_error(array('message' => $user_id->get_error_message()), 422);
        }

        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id, true);
        wp_send_json_success(array('message' => __('Cadastro concluído.', 'solar-integradores')));
    }

    public function ajax_search_municipalities()
    {
        check_ajax_referer('si_calculator', 'nonce');
        global $wpdb;

        $query = sanitize_text_field(wp_unslash($_GET['query'] ?? $_POST['query'] ?? ''));
        if (strlen($query) < 2) {
            wp_send_json_success(array());
        }

        $table = $wpdb->prefix . 'si_municipalities';
        $like = '%' . $wpdb->esc_like($query) . '%';
        $energy_table = $wpdb->prefix . 'si_location_energy';
        $items = $wpdb->get_results($wpdb->prepare(
            "SELECT m.municipality, m.state, m.latitude, m.longitude, m.hsp,
                    e.distributor, e.regulated_tariff, e.tariff_end, e.multiple_distributors
             FROM {$table} m
             LEFT JOIN {$energy_table} e
               ON e.municipality = m.municipality AND e.state = m.state
             WHERE m.municipality LIKE %s OR CONCAT(m.municipality, ' - ', m.state) LIKE %s
             ORDER BY CASE WHEN m.municipality LIKE %s THEN 0 ELSE 1 END, m.municipality
             LIMIT 20",
            $like,
            $like,
            $wpdb->esc_like($query) . '%'
        ), ARRAY_A);

        wp_send_json_success($items);
    }

    public function ajax_request_quote()
    {
        check_ajax_referer('si_calculator', 'nonce');
        if (!empty($_POST['website'])) {
            wp_send_json_error(array('message' => __('Não foi possível processar a solicitação.', 'solar-integradores')), 400);
        }

        $ip = sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
        $rate_key = 'si_quote_rate_' . md5($ip);
        $rate = (int) get_transient($rate_key);
        if ($rate >= 10) {
            wp_send_json_error(array(
                'message' => __('Muitas solicitações foram enviadas. Tente novamente mais tarde.', 'solar-integradores'),
            ), 429);
        }

        $quote_token = sanitize_text_field(wp_unslash($_POST['quote_token'] ?? ''));
        $calculation = get_transient('si_quote_' . $quote_token);
        if (!$quote_token || !is_array($calculation)) {
            wp_send_json_error(array(
                'message' => __('A simulação expirou. Faça o cálculo novamente.', 'solar-integradores'),
            ), 410);
        }

        $name = sanitize_text_field(wp_unslash($_POST['full_name'] ?? ''));
        $phone = preg_replace('/\D+/', '', (string) wp_unslash($_POST['whatsapp'] ?? ''));
        $email = sanitize_email(wp_unslash($_POST['email'] ?? ''));
        $channel = sanitize_key(wp_unslash($_POST['preferred_channel'] ?? 'whatsapp'));
        $consent = !empty($_POST['privacy_consent']);

        if (strlen($name) < 3 || strlen($phone) < 10 || !$email || !$consent) {
            wp_send_json_error(array(
                'message' => __('Preencha nome, WhatsApp e e-mail e aceite o uso dos dados para gerar o orçamento.', 'solar-integradores'),
            ), 422);
        }
        if (!in_array($channel, array('whatsapp', 'email', 'both'), true)) {
            $channel = 'whatsapp';
        }

        global $wpdb;
        $public_token = hash('sha256', wp_generate_uuid4() . microtime(true) . wp_rand());
        $input = $calculation['input'];
        $result = $calculation['result'];
        $result['proposal'] = SI_Proposal::build($input, $result, $this->settings());
        $inserted = $wpdb->insert(
            $wpdb->prefix . 'si_quotes',
            array(
                'public_token' => $public_token,
                'user_id' => get_current_user_id(),
                'customer_name' => $name,
                'customer_phone' => $phone,
                'customer_email' => $email,
                'preferred_channel' => $channel,
                'municipality' => $input['municipality'],
                'state' => $input['state'],
                'input_json' => wp_json_encode($input),
                'result_json' => wp_json_encode($result),
                'status' => 'requested',
                'consent_at' => current_time('mysql'),
                'created_at' => current_time('mysql'),
            ),
            array('%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
        );

        if (!$inserted) {
            wp_send_json_error(array(
                'message' => __('Não foi possível registrar a solicitação.', 'solar-integradores'),
            ), 500);
        }
        set_transient($rate_key, $rate + 1, HOUR_IN_SECONDS);

        delete_transient('si_quote_' . $quote_token);
        $quote_url = add_query_arg('si_quote', $public_token, home_url('/'));
        $pdf = SI_PDF::generate(array(
            'public_token' => $public_token,
            'customer_name' => $name,
            'customer_phone' => $phone,
            'customer_email' => $email,
            'municipality' => $input['municipality'],
            'state' => $input['state'],
        ), $input, $result);
        if (is_wp_error($pdf)) {
            $wpdb->update(
                $wpdb->prefix . 'si_quotes',
                array('status' => 'pdf_error'),
                array('public_token' => $public_token),
                array('%s'),
                array('%s')
            );
            wp_send_json_error(array(
                'message' => __('O pedido foi registrado, mas não foi possível gerar o PDF. A equipe foi notificada.', 'solar-integradores'),
            ), 500);
        }
        $pdf_url = $pdf['download_url'];
        $email_sent = $this->send_quote_emails($name, $email, $phone, $pdf_url, $result, $channel, $pdf['path']);

        $share_text = sprintf(
            __('Olá, %1$s. Seu orçamento solar em PDF para um sistema de %2$s kWp, no valor estimado de R$ %3$s, está disponível em %4$s', 'solar-integradores'),
            $name,
            number_format_i18n($result['system_kwp'], 2),
            number_format_i18n($result['estimated_system_price'], 2),
            $pdf_url
        );

        wp_send_json_success(array(
            'message' => __('Solicitação registrada. Seu orçamento em PDF está pronto.', 'solar-integradores'),
            'quote_url' => $pdf_url,
            'summary_url' => $quote_url,
            'pdf_url' => $pdf_url,
            'whatsapp_share_url' => 'https://wa.me/?text=' . rawurlencode($share_text),
            'email_sent' => $email_sent,
        ));
    }

    public function render_public_quote()
    {
        if (!empty($_GET['si_quote_pdf'])) {
            $this->download_quote_pdf();
        }
        if (empty($_GET['si_quote'])) {
            return;
        }

        global $wpdb;
        $token = sanitize_text_field(wp_unslash($_GET['si_quote']));
        $quote = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}si_quotes WHERE public_token = %s LIMIT 1",
            $token
        ));
        if (!$quote) {
            status_header(404);
            wp_die(esc_html__('Orçamento não encontrado.', 'solar-integradores'));
        }

        $input = json_decode($quote->input_json, true);
        $result = json_decode($quote->result_json, true);
        status_header(200);
        nocache_headers();
        include SI_DIR . 'templates/public-quote.php';
        exit;
    }

    private function download_quote_pdf()
    {
        global $wpdb;
        $token = sanitize_text_field(wp_unslash($_GET['si_quote_pdf']));
        $quote = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}si_quotes WHERE public_token = %s LIMIT 1",
            $token
        ), ARRAY_A);
        if (!$quote) {
            status_header(404);
            wp_die(esc_html__('Orçamento em PDF não encontrado.', 'solar-integradores'));
        }

        $pdf = SI_PDF::existing($token);
        if (!is_readable($pdf['path'])) {
            $input = json_decode($quote['input_json'], true);
            $result = json_decode($quote['result_json'], true);
            $pdf = SI_PDF::generate($quote, $input, $result);
        }
        if (is_wp_error($pdf) || !is_readable($pdf['path'])) {
            status_header(500);
            wp_die(esc_html__('Não foi possível gerar o orçamento em PDF.', 'solar-integradores'));
        }

        nocache_headers();
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . sanitize_file_name($pdf['filename']) . '"');
        header('Content-Length: ' . filesize($pdf['path']));
        readfile($pdf['path']);
        exit;
    }

    public function admin_menu()
    {
        add_menu_page(
            __('Orçamentos solares', 'solar-integradores'),
            __('Orçamentos solares', 'solar-integradores'),
            'manage_options',
            'si-quotes',
            array($this, 'quotes_page'),
            'dashicons-media-spreadsheet',
            26
        );
        add_options_page(
            __('Solar Integradores', 'solar-integradores'),
            __('Solar Integradores', 'solar-integradores'),
            'manage_options',
            'solar-integradores',
            array($this, 'settings_page')
        );
    }

    public function register_settings()
    {
        register_setting('si_settings_group', 'si_settings', array($this, 'sanitize_settings'));
    }

    public function sanitize_settings($input)
    {
        $bands = array();
        $default_bands = SI_Calculator::default_price_bands();
        foreach ($default_bands as $index => $default_band) {
            $bands[] = array(
                'max_kwp' => max(0.01, (float) str_replace(',', '.', $input['price_bands'][$index]['max_kwp'] ?? $default_band['max_kwp'])),
                'price_per_wp' => max(0.01, (float) str_replace(',', '.', $input['price_bands'][$index]['price_per_wp'] ?? $default_band['price_per_wp'])),
            );
        }
        usort($bands, static function ($a, $b) {
            return $a['max_kwp'] <=> $b['max_kwp'];
        });

        return array(
            'default_hsp' => max(0.1, (float) str_replace(',', '.', $input['default_hsp'] ?? 4.8)),
            'default_losses_percent' => min(45, max(0, (float) str_replace(',', '.', $input['default_losses_percent'] ?? 17))),
            'default_module_power_w' => max(1, absint($input['default_module_power_w'] ?? 550)),
            'default_tariff' => max(0, (float) str_replace(',', '.', $input['default_tariff'] ?? 1.00)),
            'tariff_additions_percent' => min(100, max(0, (float) str_replace(',', '.', $input['tariff_additions_percent'] ?? 25))),
            'default_public_lighting_fee' => max(0, (float) str_replace(',', '.', $input['default_public_lighting_fee'] ?? 25)),
            'quote_notification_email' => sanitize_email($input['quote_notification_email'] ?? get_option('admin_email')),
            'business_whatsapp' => preg_replace('/\D+/', '', (string) ($input['business_whatsapp'] ?? '')),
            'proposal_valid_days' => max(1, absint($input['proposal_valid_days'] ?? 10)),
            'cash_discount_percent' => min(30, max(0, (float) str_replace(',', '.', $input['cash_discount_percent'] ?? 5))),
            'mercado_pago_pix_fee' => min(30, max(0, (float) str_replace(',', '.', $input['mercado_pago_pix_fee'] ?? 0.99))),
            'mercado_pago_card_processing_fee' => min(30, max(0, (float) str_replace(',', '.', $input['mercado_pago_card_processing_fee'] ?? 3.99))),
            'mercado_pago_installment_fees' => sanitize_text_field($input['mercado_pago_installment_fees'] ?? SI_Proposal::default_installment_fees()),
            'financial_discount_rate' => min(50, max(0, (float) str_replace(',', '.', $input['financial_discount_rate'] ?? 8))),
            'tariff_inflation_percent' => min(50, max(0, (float) str_replace(',', '.', $input['tariff_inflation_percent'] ?? 5))),
            'annual_degradation_percent' => min(10, max(0, (float) str_replace(',', '.', $input['annual_degradation_percent'] ?? 0.8))),
            'annual_maintenance_percent' => min(20, max(0, (float) str_replace(',', '.', $input['annual_maintenance_percent'] ?? 0.5))),
            'module_area_m2' => min(10, max(0.1, (float) str_replace(',', '.', $input['module_area_m2'] ?? 2.58))),
            'default_module_model' => sanitize_text_field($input['default_module_model'] ?? ''),
            'default_inverter_model' => sanitize_text_field($input['default_inverter_model'] ?? ''),
            'supplier_catalog' => sanitize_textarea_field($input['supplier_catalog'] ?? ''),
            'price_range_percent' => min(40, max(0, (float) str_replace(',', '.', $input['price_range_percent'] ?? 12))),
            'price_bands' => $bands,
            'registration_enabled' => empty($input['registration_enabled']) ? 0 : 1,
        );
    }

    public function settings_page()
    {
        $settings = $this->settings();
        include SI_DIR . 'templates/admin-settings.php';
    }

    public function quotes_page()
    {
        global $wpdb;
        $quotes = $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}si_quotes ORDER BY created_at DESC LIMIT 200"
        );
        include SI_DIR . 'templates/admin-quotes.php';
    }

    private function settings()
    {
        return wp_parse_args(get_option('si_settings', array()), array(
            'default_hsp' => 4.8,
            'default_losses_percent' => 17,
            'default_module_power_w' => 550,
            'default_tariff' => 1.00,
            'tariff_additions_percent' => 25,
            'default_public_lighting_fee' => 25,
            'quote_notification_email' => get_option('admin_email'),
            'business_whatsapp' => '',
            'proposal_valid_days' => 10,
            'cash_discount_percent' => 5,
            'mercado_pago_pix_fee' => 0.99,
            'mercado_pago_card_processing_fee' => 3.99,
            'mercado_pago_installment_fees' => SI_Proposal::default_installment_fees(),
            'financial_discount_rate' => 8,
            'tariff_inflation_percent' => 5,
            'annual_degradation_percent' => 0.8,
            'annual_maintenance_percent' => 0.5,
            'module_area_m2' => 2.58,
            'default_module_model' => 'Módulo N-Type 550 W ou superior',
            'default_inverter_model' => 'Inversor compatível com o projeto',
            'supplier_catalog' => '',
            'price_range_percent' => 12,
            'price_bands' => SI_Calculator::default_price_bands(),
            'registration_enabled' => 1,
        ));
    }

    private function sanitize_calculation($source)
    {
        $allowed_text = array(
            'calculation_mode', 'customer_type', 'connection_type',
            'customer_name', 'customer_email', 'municipality', 'state',
        );
        $allowed_numbers = array(
            'monthly_consumption', 'monthly_bill', 'energy_tariff', 'hsp',
            'losses_percent', 'off_peak_consumption', 'peak_consumption',
            'off_peak_te', 'peak_te', 'contracted_demand', 'module_power_w',
            'public_lighting_fee',
        );
        $clean = array();
        foreach ($allowed_text as $key) {
            $clean[$key] = sanitize_text_field(wp_unslash($source[$key] ?? ''));
        }
        foreach ($allowed_numbers as $key) {
            $clean[$key] = sanitize_text_field(wp_unslash($source[$key] ?? '0'));
        }
        return $clean;
    }

    private function save_simulation(array $input, array $result)
    {
        global $wpdb;
        $wpdb->insert(
            $wpdb->prefix . 'si_simulations',
            array(
                'user_id' => get_current_user_id(),
                'customer_name' => $input['customer_name'],
                'customer_email' => sanitize_email($input['customer_email']),
                'input_json' => wp_json_encode($input),
                'result_json' => wp_json_encode($result),
                'created_at' => current_time('mysql'),
            ),
            array('%d', '%s', '%s', '%s', '%s', '%s')
        );
    }

    private function location_energy($municipality, $state)
    {
        global $wpdb;
        if (!$municipality || !$state) {
            return null;
        }
        return $wpdb->get_row($wpdb->prepare(
            "SELECT distributor, regulated_tariff, tariff_start, tariff_end, multiple_distributors
             FROM {$wpdb->prefix}si_location_energy
             WHERE municipality = %s AND state = %s
             LIMIT 1",
            $this->normalize_location($municipality),
            strtoupper($state)
        ), ARRAY_A);
    }

    private function public_lighting_fee($state, $fallback)
    {
        $averages = array(
            'AC' => 18, 'AL' => 24, 'AP' => 20, 'AM' => 22, 'BA' => 28,
            'CE' => 26, 'DF' => 32, 'ES' => 27, 'GO' => 28, 'MA' => 22,
            'MT' => 27, 'MS' => 27, 'MG' => 30, 'PA' => 23, 'PB' => 24,
            'PR' => 29, 'PE' => 26, 'PI' => 22, 'RJ' => 35, 'RN' => 25,
            'RS' => 30, 'RO' => 22, 'RR' => 20, 'SC' => 29, 'SP' => 31,
            'SE' => 24, 'TO' => 22,
        );
        $state = strtoupper((string) $state);
        return isset($averages[$state]) ? $averages[$state] : $fallback;
    }

    private function normalize_location($value)
    {
        return strtoupper(remove_accents(trim((string) $value)));
    }

    private function send_quote_emails($name, $email, $phone, $pdf_url, array $result, $channel, $pdf_path)
    {
        $settings = $this->settings();
        $subject = sprintf(__('Estimativa solar - %s', 'solar-integradores'), $name);
        $message = sprintf(
            "Olá, %s.\n\nSeu orçamento solar em PDF está pronto:\nPotência: %s kWp\nInvestimento estimado: R$ %s\n\nPDF: %s\n\nO valor final depende de vistoria e projeto.",
            $name,
            number_format_i18n($result['system_kwp'], 2),
            number_format_i18n($result['estimated_system_price'], 2),
            $pdf_url
        );
        $attachments = is_readable($pdf_path) ? array($pdf_path) : array();
        $customer_sent = false;
        if (in_array($channel, array('email', 'both'), true)) {
            $customer_sent = wp_mail($email, $subject, $message, array(), $attachments);
        }

        $notification_email = sanitize_email($settings['quote_notification_email']);
        if ($notification_email) {
            wp_mail(
                $notification_email,
                sprintf(__('Novo pedido de orçamento - %s', 'solar-integradores'), $name),
                $message . "\n\nWhatsApp do cliente: " . $phone,
                array(),
                $attachments
            );
        }
        return $customer_sent;
    }

    private static function seed_municipalities()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'si_municipalities';
        $count = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table}");
        $file = SI_DIR . 'data/municipalities.csv';
        if ($count > 0 || !is_readable($file)) {
            return;
        }

        $handle = fopen($file, 'r');
        if (!$handle) {
            return;
        }

        fgetcsv($handle);
        $batch = array();
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 4) {
                continue;
            }
            $batch[] = $wpdb->prepare(
                '(%s, %s, %f, %f, NULL, %s, %s)',
                sanitize_text_field($row[0]),
                sanitize_text_field($row[1]),
                (float) $row[2],
                (float) $row[3],
                'Planilha de dimensionamento fornecida',
                current_time('mysql')
            );
            if (count($batch) >= 300) {
                $wpdb->query("INSERT IGNORE INTO {$table}
                    (municipality, state, latitude, longitude, hsp, source, updated_at)
                    VALUES " . implode(',', $batch));
                $batch = array();
            }
        }
        fclose($handle);

        if ($batch) {
            $wpdb->query("INSERT IGNORE INTO {$table}
                (municipality, state, latitude, longitude, hsp, source, updated_at)
                VALUES " . implode(',', $batch));
        }
    }

    private static function seed_location_energy()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'si_location_energy';
        $file = SI_DIR . 'data/location-energy.csv';
        $data_version = '2026-06-06';
        if (!is_readable($file)) {
            return;
        }

        $count = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table}");
        if ($count > 0 && get_option('si_location_data_version') === $data_version) {
            return;
        }
        if ($count > 0) {
            $wpdb->query("TRUNCATE TABLE {$table}");
        }

        $handle = fopen($file, 'r');
        if (!$handle) {
            return;
        }
        fgetcsv($handle);
        $batch = array();
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 7) {
                continue;
            }
            $tariff = $row[3] === '' ? 'NULL' : (string) (float) $row[3];
            $start = $row[4] === '' ? 'NULL' : $wpdb->prepare('%s', $row[4]);
            $end = $row[5] === '' ? 'NULL' : $wpdb->prepare('%s', $row[5]);
            $batch[] = $wpdb->prepare(
                '(%s, %s, %s, ' . $tariff . ', ' . $start . ', ' . $end . ', %d)',
                sanitize_text_field($row[0]),
                sanitize_text_field($row[1]),
                sanitize_text_field($row[2]),
                (int) $row[6]
            );
            if (count($batch) >= 300) {
                $wpdb->query("INSERT IGNORE INTO {$table}
                    (municipality, state, distributor, regulated_tariff, tariff_start, tariff_end, multiple_distributors)
                    VALUES " . implode(',', $batch));
                $batch = array();
            }
        }
        fclose($handle);

        if ($batch) {
            $wpdb->query("INSERT IGNORE INTO {$table}
                (municipality, state, distributor, regulated_tariff, tariff_start, tariff_end, multiple_distributors)
                VALUES " . implode(',', $batch));
        }
        update_option('si_location_data_version', $data_version);
    }
}
