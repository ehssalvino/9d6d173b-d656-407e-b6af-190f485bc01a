<?php

if (!defined('ABSPATH')) {
    exit;
}

class HTW_Admin
{
    private $repository;

    public function __construct(HTW_Task_Repository $repository)
    {
        $this->repository = $repository;
    }

    public function init()
    {
        add_action('admin_menu', array($this, 'menu'));
        add_action('admin_init', array($this, 'settings'));
        add_action('admin_enqueue_scripts', array($this, 'assets'));
        add_action('admin_post_htw_save_connections', array($this, 'save_connections'));
        add_action('admin_post_htw_google_oauth_start', array($this, 'google_oauth_start'));
        add_action('admin_post_htw_google_oauth_callback', array($this, 'google_oauth_callback'));
        add_filter('plugin_action_links_' . plugin_basename(HTW_FILE), array($this, 'plugin_links'));
    }

    public function menu()
    {
        add_menu_page(
            'Haja Tarefas',
            'Haja Tarefas',
            'manage_options',
            'haja-tarefas',
            array($this, 'render_kanban'),
            'dashicons-clipboard',
            28
        );

        add_submenu_page(
            'haja-tarefas',
            'Conexoes',
            'Conexoes',
            'manage_options',
            'haja-tarefas-settings',
            array($this, 'render_settings')
        );
    }

    public function plugin_links($links)
    {
        array_unshift($links, '<a href="' . esc_url(admin_url('admin.php?page=haja-tarefas-settings')) . '">Conexoes</a>');
        return $links;
    }

    public function settings()
    {
        if (!get_option('htw_whatsapp_verify_token', '')) {
            update_option('htw_whatsapp_verify_token', wp_generate_password(32, false, false), false);
        }
    }

    public function assets($hook)
    {
        if (false === strpos($hook, 'haja-tarefas')) {
            return;
        }

        wp_enqueue_style('htw-admin', HTW_URL . 'assets/admin.css', array(), HTW_VERSION);
        wp_enqueue_script('htw-admin', HTW_URL . 'assets/admin.js', array(), HTW_VERSION, true);
        wp_localize_script('htw-admin', 'HTW_ADMIN', array(
            'nonce' => wp_create_nonce('wp_rest'),
            'restUrl' => esc_url_raw(rest_url('haja-tarefas/v1')),
        ));
    }

    public function render_kanban()
    {
        $groups = $this->repository->tasks_grouped_by_status();
        $statuses = HTW_Task_Repository::statuses();
        ?>
        <div class="wrap htw-wrap">
            <h1>Haja Tarefas</h1>
            <p class="description">Kanban interno para tarefas capturadas pelo WhatsApp e pelo site.</p>
            <p><a class="button button-primary" href="<?php echo esc_url(admin_url('admin.php?page=haja-tarefas-settings')); ?>">Abrir conexoes e autenticacao</a></p>
            <div class="htw-board">
                <?php foreach ($statuses as $key => $label) : ?>
                    <section class="htw-column" data-status="<?php echo esc_attr($key); ?>">
                        <header><?php echo esc_html($label); ?></header>
                        <?php foreach ($groups[$key] as $task) : ?>
                            <article class="htw-card">
                                <strong><?php echo esc_html($task['title']); ?></strong>
                                <span><?php echo esc_html($task['project_name'] ?: 'Pessoal'); ?></span>
                                <small>Prioridade: <?php echo esc_html($task['priority']); ?></small>
                                <?php if (!empty($task['due_at'])) : ?>
                                    <small>Prazo: <?php echo esc_html(mysql2date('d/m/Y H:i', $task['due_at'])); ?></small>
                                <?php endif; ?>
                                <select class="htw-status" data-task-id="<?php echo esc_attr($task['id']); ?>">
                                    <?php foreach ($statuses as $status_key => $status_label) : ?>
                                        <option value="<?php echo esc_attr($status_key); ?>" <?php selected($task['status'], $status_key); ?>>
                                            <?php echo esc_html($status_label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </article>
                        <?php endforeach; ?>
                    </section>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }

    public function render_settings()
    {
        $webhook_url = rest_url('haja-tarefas/v1/whatsapp');
        $redirect_uri = $this->google_redirect_uri();
        $google_connected = (bool) get_option('htw_google_refresh_token', '');
        $whatsapp_ready = get_option('htw_whatsapp_access_token', '') && get_option('htw_whatsapp_phone_number_id', '');
        ?>
        <div class="wrap htw-wrap htw-setup">
            <h1>Conexoes do Haja Tarefas</h1>
            <p class="description">Tudo que o plugin precisa para falar com Google Agenda e WhatsApp Business fica nesta tela.</p>

            <?php if (isset($_GET['htw_saved'])) : ?>
                <div class="notice notice-success is-dismissible"><p>Conexoes salvas pelo plugin.</p></div>
            <?php endif; ?>
            <?php if (isset($_GET['htw_connected']) && 'google' === $_GET['htw_connected']) : ?>
                <div class="notice notice-success is-dismissible"><p>Google Agenda conectado com sucesso.</p></div>
            <?php endif; ?>
            <?php if (isset($_GET['htw_error']) && 'google_access_denied' === $_GET['htw_error']) : ?>
                <div class="notice notice-error is-dismissible"><p>Google bloqueou o acesso porque o app OAuth esta em modo de testes. Adicione o e-mail usado no login como testador em Google Cloud &gt; Google Auth Platform &gt; Audience &gt; Test users.</p></div>
            <?php endif; ?>

            <div class="htw-status-row">
                <?php $this->status_badge('Google Agenda', $google_connected); ?>
                <?php $this->status_badge('WhatsApp Business', $whatsapp_ready); ?>
                <?php $this->status_badge('Webhook URL', !empty($webhook_url)); ?>
            </div>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="htw_save_connections">
                <?php wp_nonce_field('htw_save_connections'); ?>

                <div class="htw-setup-grid">
                    <section class="htw-panel">
                        <h2>Google Agenda</h2>
                        <p>O plugin cria e renova o acesso ao Google Agenda por OAuth. Voce cola aqui as credenciais do Google e clica em conectar.</p>

                        <label for="htw_google_calendar_id">Calendario</label>
                        <input class="regular-text" id="htw_google_calendar_id" name="htw_google_calendar_id" value="<?php echo esc_attr(get_option('htw_google_calendar_id', 'primary')); ?>">
                        <p class="description">Use <code>primary</code> para a agenda principal.</p>

                        <label for="htw_google_client_id">Google Client ID</label>
                        <input class="large-text" id="htw_google_client_id" name="htw_google_client_id" value="<?php echo esc_attr(get_option('htw_google_client_id', '')); ?>">

                        <label for="htw_google_client_secret">Google Client Secret</label>
                        <input class="large-text" id="htw_google_client_secret" name="htw_google_client_secret" value="<?php echo esc_attr(get_option('htw_google_client_secret', '')); ?>">

                        <label>URI de redirecionamento do plugin</label>
                        <div class="htw-copy-line">
                            <input class="large-text code" readonly value="<?php echo esc_attr($redirect_uri); ?>">
                            <button type="button" class="button htw-copy">Copiar</button>
                        </div>
                        <p class="description">Cadastre esta URI no OAuth Client do Google. Depois salve esta tela e conecte.</p>
                        <div class="htw-help-box">
                            <strong>Se aparecer Erro 403 access_denied</strong>
                            <p>O projeto do Google esta em fase de testes. No Google Cloud, abra Google Auth Platform &gt; Audience e adicione o e-mail da agenda em Test users. Para liberar para qualquer usuario, publique e conclua a verificacao do Google.</p>
                        </div>

                        <p class="htw-actions">
                            <?php submit_button('Salvar conexoes', 'secondary', 'submit', false); ?>
                            <a class="button button-primary" href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=htw_google_oauth_start'), 'htw_google_oauth_start')); ?>">Conectar Google Agenda</a>
                        </p>
                    </section>

                    <section class="htw-panel">
                        <h2>WhatsApp Business</h2>
                        <p>O WhatsApp chama o webhook do plugin. O token de verificacao e criado aqui; o access token vem da Meta.</p>

                        <label for="htw_whatsapp_verify_token">Verify Token do webhook</label>
                        <div class="htw-copy-line">
                            <input class="large-text code" id="htw_whatsapp_verify_token" name="htw_whatsapp_verify_token" value="<?php echo esc_attr(get_option('htw_whatsapp_verify_token', '')); ?>">
                            <button type="button" class="button htw-copy">Copiar</button>
                        </div>
                        <p class="description">Use exatamente este valor no campo Verify Token do webhook da Meta.</p>

                        <label>Callback URL do webhook</label>
                        <div class="htw-copy-line">
                            <input class="large-text code" readonly value="<?php echo esc_attr($webhook_url); ?>">
                            <button type="button" class="button htw-copy">Copiar</button>
                        </div>

                        <label for="htw_whatsapp_access_token">WhatsApp Access Token</label>
                        <input class="large-text" id="htw_whatsapp_access_token" name="htw_whatsapp_access_token" value="<?php echo esc_attr(get_option('htw_whatsapp_access_token', '')); ?>">

                        <label for="htw_whatsapp_phone_number_id">Phone Number ID</label>
                        <input class="large-text" id="htw_whatsapp_phone_number_id" name="htw_whatsapp_phone_number_id" value="<?php echo esc_attr(get_option('htw_whatsapp_phone_number_id', '')); ?>">

                        <label for="htw_whatsapp_business_account_id">WhatsApp Business Account ID</label>
                        <input class="large-text" id="htw_whatsapp_business_account_id" name="htw_whatsapp_business_account_id" value="<?php echo esc_attr(get_option('htw_whatsapp_business_account_id', '')); ?>">

                        <p class="htw-actions"><?php submit_button('Salvar conexoes', 'secondary', 'submit', false); ?></p>
                    </section>

                    <section class="htw-panel htw-panel-wide">
                        <h2>O que o plugin aponta automaticamente</h2>
                        <div class="htw-url-list">
                            <div>
                                <strong>Webhook que recebe mensagens</strong>
                                <code><?php echo esc_html($webhook_url); ?></code>
                            </div>
                            <div>
                                <strong>Retorno OAuth do Google</strong>
                                <code><?php echo esc_html($redirect_uri); ?></code>
                            </div>
                            <div>
                                <strong>Escopo solicitado ao Google</strong>
                                <code>https://www.googleapis.com/auth/calendar.events</code>
                            </div>
                        </div>
                        <p>Essas URLs pertencem ao plugin. Voce so precisa colar a URL correspondente nas telas do Google e da Meta quando eles pedirem.</p>
                    </section>
                </div>
            </form>
        </div>
        <?php
    }

    public function save_connections()
    {
        if (!current_user_can('manage_options') || !check_admin_referer('htw_save_connections')) {
            wp_die('Acesso negado.');
        }

        $fields = array(
            'htw_whatsapp_verify_token',
            'htw_whatsapp_access_token',
            'htw_whatsapp_phone_number_id',
            'htw_whatsapp_business_account_id',
            'htw_google_calendar_id',
            'htw_google_client_id',
            'htw_google_client_secret',
        );

        foreach ($fields as $field) {
            $value = isset($_POST[$field]) ? sanitize_text_field(wp_unslash($_POST[$field])) : '';
            update_option($field, $value, false);
        }

        if (!get_option('htw_whatsapp_verify_token', '')) {
            update_option('htw_whatsapp_verify_token', wp_generate_password(32, false, false), false);
        }

        wp_safe_redirect(admin_url('admin.php?page=haja-tarefas-settings&htw_saved=1'));
        exit;
    }

    public function google_oauth_start()
    {
        if (!current_user_can('manage_options') || !check_admin_referer('htw_google_oauth_start')) {
            wp_die('Acesso negado.');
        }

        $client_id = get_option('htw_google_client_id', '');
        if (!$client_id) {
            wp_safe_redirect(admin_url('admin.php?page=haja-tarefas-settings&htw_error=missing_google_client_id'));
            exit;
        }

        $url = add_query_arg(
            array(
                'client_id' => $client_id,
                'redirect_uri' => $this->google_redirect_uri(),
                'response_type' => 'code',
                'scope' => 'https://www.googleapis.com/auth/calendar.events',
                'access_type' => 'offline',
                'prompt' => 'consent',
                'include_granted_scopes' => 'true',
                'state' => wp_create_nonce('htw_google_oauth_callback'),
            ),
            'https://accounts.google.com/o/oauth2/v2/auth'
        );

        wp_redirect($url);
        exit;
    }

    public function google_oauth_callback()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Acesso negado.');
        }

        if (isset($_GET['error']) && 'access_denied' === sanitize_key(wp_unslash($_GET['error']))) {
            wp_safe_redirect(admin_url('admin.php?page=haja-tarefas-settings&htw_error=google_access_denied'));
            exit;
        }

        $state = isset($_GET['state']) ? sanitize_text_field(wp_unslash($_GET['state'])) : '';
        if (!wp_verify_nonce($state, 'htw_google_oauth_callback')) {
            wp_die('Estado OAuth invalido.');
        }

        $code = isset($_GET['code']) ? sanitize_text_field(wp_unslash($_GET['code'])) : '';
        if (!$code) {
            wp_safe_redirect(admin_url('admin.php?page=haja-tarefas-settings&htw_error=missing_google_code'));
            exit;
        }

        $response = wp_remote_post('https://oauth2.googleapis.com/token', array(
            'body' => array(
                'code' => $code,
                'client_id' => get_option('htw_google_client_id', ''),
                'client_secret' => get_option('htw_google_client_secret', ''),
                'redirect_uri' => $this->google_redirect_uri(),
                'grant_type' => 'authorization_code',
            ),
            'timeout' => 15,
        ));

        if (is_wp_error($response)) {
            wp_safe_redirect(admin_url('admin.php?page=haja-tarefas-settings&htw_error=google_token_request'));
            exit;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        if (empty($body['access_token'])) {
            wp_safe_redirect(admin_url('admin.php?page=haja-tarefas-settings&htw_error=google_token_response'));
            exit;
        }

        update_option('htw_google_access_token', sanitize_text_field($body['access_token']), false);
        update_option('htw_google_token_expires_at', time() + absint($body['expires_in'] ?? 3600) - 60, false);

        if (!empty($body['refresh_token'])) {
            update_option('htw_google_refresh_token', sanitize_text_field($body['refresh_token']), false);
        }

        wp_safe_redirect(admin_url('admin.php?page=haja-tarefas-settings&htw_connected=google'));
        exit;
    }

    private function google_redirect_uri()
    {
        return admin_url('admin-post.php?action=htw_google_oauth_callback');
    }

    private function status_badge($label, $ok)
    {
        ?>
        <span class="htw-status-badge <?php echo $ok ? 'is-ok' : 'is-pending'; ?>">
            <strong><?php echo esc_html($label); ?></strong>
            <?php echo $ok ? 'configurado' : 'pendente'; ?>
        </span>
        <?php
    }
}

