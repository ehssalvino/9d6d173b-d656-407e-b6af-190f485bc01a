<?php if (!defined('ABSPATH')) { exit; } ?>
<div class="wrap">
    <h1><?php esc_html_e('Pedidos de orçamento solar', 'solar-integradores'); ?></h1>
    <p><?php esc_html_e('Solicitações geradas pela calculadora pública.', 'solar-integradores'); ?></p>
    <?php if (!$quotes) : ?>
        <div class="notice notice-info"><p><?php esc_html_e('Nenhum pedido recebido ainda.', 'solar-integradores'); ?></p></div>
    <?php else : ?>
        <table class="widefat striped">
            <thead>
            <tr>
                <th><?php esc_html_e('Data', 'solar-integradores'); ?></th>
                <th><?php esc_html_e('Cliente', 'solar-integradores'); ?></th>
                <th><?php esc_html_e('WhatsApp', 'solar-integradores'); ?></th>
                <th><?php esc_html_e('Local', 'solar-integradores'); ?></th>
                <th><?php esc_html_e('Sistema', 'solar-integradores'); ?></th>
                <th><?php esc_html_e('Estimativa', 'solar-integradores'); ?></th>
                <th><?php esc_html_e('Canal', 'solar-integradores'); ?></th>
                <th><?php esc_html_e('Resumo', 'solar-integradores'); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($quotes as $quote) :
                $result = json_decode($quote->result_json, true);
                $url = add_query_arg('si_quote', $quote->public_token, home_url('/'));
                $pdf_url = add_query_arg('si_quote_pdf', $quote->public_token, home_url('/'));
                $phone = preg_replace('/\D+/', '', $quote->customer_phone);
            ?>
                <tr>
                    <td><?php echo esc_html(mysql2date('d/m/Y H:i', $quote->created_at)); ?></td>
                    <td><strong><?php echo esc_html($quote->customer_name); ?></strong><br><a href="mailto:<?php echo esc_attr($quote->customer_email); ?>"><?php echo esc_html($quote->customer_email); ?></a></td>
                    <td><a href="<?php echo esc_url('https://wa.me/' . $phone); ?>" target="_blank" rel="noopener"><?php echo esc_html($quote->customer_phone); ?></a></td>
                    <td><?php echo esc_html($quote->municipality . ' - ' . $quote->state); ?></td>
                    <td><?php echo esc_html(number_format_i18n($result['system_kwp'] ?? 0, 2)); ?> kWp</td>
                    <td>R$ <?php echo esc_html(number_format_i18n($result['estimated_system_price'] ?? 0, 2)); ?></td>
                    <td><?php echo esc_html($quote->preferred_channel); ?></td>
                    <td><a class="button button-primary button-small" href="<?php echo esc_url($pdf_url); ?>" target="_blank" rel="noopener"><?php esc_html_e('PDF', 'solar-integradores'); ?></a> <a class="button button-small" href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener"><?php esc_html_e('Resumo', 'solar-integradores'); ?></a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
