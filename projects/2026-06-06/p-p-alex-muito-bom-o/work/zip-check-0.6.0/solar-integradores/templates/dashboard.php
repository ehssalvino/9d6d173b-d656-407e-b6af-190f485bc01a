<?php if (!defined('ABSPATH')) { exit; } ?>
<section class="si-app">
    <div class="si-heading">
        <span class="si-eyebrow"><?php esc_html_e('Área do integrador', 'solar-integradores'); ?></span>
        <h2><?php esc_html_e('Minhas simulações', 'solar-integradores'); ?></h2>
    </div>
    <?php if (!$items) : ?>
        <div class="si-notice"><?php esc_html_e('Nenhuma simulação salva ainda.', 'solar-integradores'); ?></div>
    <?php else : ?>
        <div class="si-table-wrap">
            <table class="si-table">
                <thead><tr><th><?php esc_html_e('Data', 'solar-integradores'); ?></th><th><?php esc_html_e('Cliente', 'solar-integradores'); ?></th><th><?php esc_html_e('Consumo', 'solar-integradores'); ?></th><th><?php esc_html_e('Sistema', 'solar-integradores'); ?></th><th><?php esc_html_e('Investimento estimado', 'solar-integradores'); ?></th></tr></thead>
                <tbody>
                <?php foreach ($items as $item) :
                    $result = json_decode($item->result_json, true);
                ?>
                    <tr>
                        <td><?php echo esc_html(mysql2date(get_option('date_format'), $item->created_at)); ?></td>
                        <td><?php echo esc_html($item->customer_name ?: __('Sem nome', 'solar-integradores')); ?></td>
                        <td><?php echo esc_html(number_format_i18n($result['monthly_consumption_kwh'] ?? 0, 2)); ?> kWh</td>
                        <td><strong><?php echo esc_html(number_format_i18n($result['system_kwp'] ?? 0, 2)); ?> kWp</strong></td>
                        <td>R$ <?php echo esc_html(number_format_i18n($result['estimated_system_price'] ?? 0, 2)); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
