<?php if (!defined('ABSPATH')) { exit; } ?>
<section class="si-app si-narrow">
    <div class="si-heading">
        <span class="si-eyebrow"><?php esc_html_e('Área profissional', 'solar-integradores'); ?></span>
        <h2><?php esc_html_e('Cadastro de integrador', 'solar-integradores'); ?></h2>
    </div>
    <form class="si-form" data-si-registration>
        <label><span><?php esc_html_e('Nome completo', 'solar-integradores'); ?></span><input type="text" name="name" maxlength="190" required></label>
        <label><span><?php esc_html_e('E-mail', 'solar-integradores'); ?></span><input type="email" name="email" maxlength="190" required></label>
        <label><span><?php esc_html_e('Senha', 'solar-integradores'); ?></span><input type="password" name="password" minlength="8" required></label>
        <button class="si-button" type="submit"><?php esc_html_e('Criar cadastro', 'solar-integradores'); ?></button>
        <div class="si-message" data-si-message aria-live="polite"></div>
    </form>
</section>
