<?php if (!defined('ABSPATH')) { exit; } ?>
<section class="si-app" data-si-calculator>
    <div class="si-heading">
        <span class="si-eyebrow"><?php esc_html_e('Dimensionamento fotovoltaico', 'solar-integradores'); ?></span>
        <h2><?php esc_html_e('Calculadora solar', 'solar-integradores'); ?></h2>
        <p><?php esc_html_e('Estime potência, geração e quantidade de módulos. O resultado é preliminar e não substitui projeto elétrico.', 'solar-integradores'); ?></p>
    </div>

    <form class="si-form" data-si-form>
        <div class="si-grid si-grid-2">
            <label>
                <span><?php esc_html_e('Tipo de cliente', 'solar-integradores'); ?></span>
                <select name="customer_type" data-si-customer-type>
                    <option value="residential"><?php esc_html_e('Residencial / baixa tensão', 'solar-integradores'); ?></option>
                    <option value="commercial"><?php esc_html_e('Comercial sem demanda', 'solar-integradores'); ?></option>
                    <option value="commercial_demand"><?php esc_html_e('Comercial com demanda contratada', 'solar-integradores'); ?></option>
                </select>
            </label>
            <label>
                <span><?php esc_html_e('Calcular usando', 'solar-integradores'); ?></span>
                <select name="calculation_mode" data-si-mode>
                    <option value="kwh"><?php esc_html_e('Consumo em kWh', 'solar-integradores'); ?></option>
                    <option value="bill"><?php esc_html_e('Valor da conta em R$', 'solar-integradores'); ?></option>
                </select>
            </label>
        </div>

        <div class="si-grid si-grid-2" data-si-standard-consumption>
            <label data-si-kwh-field>
                <span><?php esc_html_e('Consumo médio mensal (kWh)', 'solar-integradores'); ?></span>
                <input type="number" name="monthly_consumption" min="0" step="0.01" placeholder="900">
            </label>
            <label data-si-bill-field hidden>
                <span><?php esc_html_e('Valor médio da conta (R$)', 'solar-integradores'); ?></span>
                <input type="number" name="monthly_bill" min="0" step="0.01" placeholder="950,00">
            </label>
        </div>

        <div class="si-panel" data-si-demand-fields hidden>
            <h3><?php esc_html_e('Grupo A: ponta e fora de ponta', 'solar-integradores'); ?></h3>
            <div class="si-grid si-grid-3">
                <label><span><?php esc_html_e('Consumo fora de ponta (kWh)', 'solar-integradores'); ?></span><input type="number" name="off_peak_consumption" min="0" step="0.01"></label>
                <label><span><?php esc_html_e('Consumo na ponta (kWh)', 'solar-integradores'); ?></span><input type="number" name="peak_consumption" min="0" step="0.01"></label>
                <label><span><?php esc_html_e('Demanda contratada (kW)', 'solar-integradores'); ?></span><input type="number" name="contracted_demand" min="0" step="0.01"></label>
                <label><span><?php esc_html_e('TE fora de ponta (R$/kWh)', 'solar-integradores'); ?></span><input type="number" name="off_peak_te" min="0" step="0.000001"></label>
                <label><span><?php esc_html_e('TE ponta (R$/kWh)', 'solar-integradores'); ?></span><input type="number" name="peak_te" min="0" step="0.000001"></label>
            </div>
        </div>

        <div class="si-grid si-grid-2">
            <label class="si-location">
                <span><?php esc_html_e('Município / UF', 'solar-integradores'); ?></span>
                <input type="text" name="municipality_search" autocomplete="off" placeholder="Fortaleza - CE" data-si-city-search required>
                <input type="hidden" name="municipality" data-si-city-name>
                <input type="hidden" name="state" data-si-city-state>
                <div class="si-suggestions" data-si-city-suggestions hidden></div>
            </label>
            <label>
                <span><?php esc_html_e('Ligação', 'solar-integradores'); ?></span>
                <select name="connection_type">
                    <option value="single_phase"><?php esc_html_e('Monofásica', 'solar-integradores'); ?></option>
                    <option value="two_phase"><?php esc_html_e('Bifásica', 'solar-integradores'); ?></option>
                    <option value="three_phase" selected><?php esc_html_e('Trifásica', 'solar-integradores'); ?></option>
                </select>
            </label>
        </div>
        <div class="si-location-info" data-si-location-info hidden></div>

        <?php if (is_user_logged_in()) : ?>
            <div class="si-grid si-grid-2">
                <label><span><?php esc_html_e('Nome do cliente', 'solar-integradores'); ?></span><input type="text" name="customer_name" maxlength="190"></label>
                <label><span><?php esc_html_e('E-mail do cliente', 'solar-integradores'); ?></span><input type="email" name="customer_email" maxlength="190"></label>
            </div>
        <?php endif; ?>

        <button class="si-button" type="submit"><?php esc_html_e('Calcular sistema', 'solar-integradores'); ?></button>
        <div class="si-message" data-si-message aria-live="polite"></div>
    </form>

    <div class="si-results" data-si-results hidden>
        <article><span><?php esc_html_e('Potência estimada', 'solar-integradores'); ?></span><strong data-si-result="system_kwp">0</strong><small>kWp</small></article>
        <article><span><?php esc_html_e('Geração mensal', 'solar-integradores'); ?></span><strong data-si-result="monthly_generation_kwh">0</strong><small>kWh/mês</small></article>
        <article><span><?php esc_html_e('Quantidade de módulos', 'solar-integradores'); ?></span><strong data-si-result="module_count">0</strong><small><?php esc_html_e('módulos', 'solar-integradores'); ?></small></article>
        <article><span><?php esc_html_e('Payback simples', 'solar-integradores'); ?></span><strong data-si-result="simple_payback_years">-</strong><small><?php esc_html_e('anos', 'solar-integradores'); ?></small></article>
        <article class="si-price-result">
            <span><?php esc_html_e('Investimento estimado', 'solar-integradores'); ?></span>
            <strong data-si-currency="estimated_system_price">R$ 0</strong>
            <small><span data-si-currency="estimated_system_price_min">R$ 0</span> a <span data-si-currency="estimated_system_price_max">R$ 0</span></small>
        </article>
        <article>
            <span><?php esc_html_e('Referência de mercado', 'solar-integradores'); ?></span>
            <strong data-si-currency="estimated_price_per_wp">R$ 0</strong>
            <small><?php esc_html_e('por Wp instalado', 'solar-integradores'); ?></small>
        </article>
        <article>
            <span><?php esc_html_e('Distribuidora local', 'solar-integradores'); ?></span>
            <strong class="si-result-text" data-si-text="distributor">-</strong>
            <small><span data-si-currency="estimated_energy_tariff">R$ 0</span>/kWh e COSIP média de <span data-si-currency="estimated_public_lighting_fee">R$ 0</span></small>
        </article>
        <div class="si-warnings" data-si-warnings hidden></div>
        <p class="si-disclaimer"><?php esc_html_e('Estimativa nacional de mercado para sistema on-grid instalado. O orçamento final depende de vistoria, estrutura, padrão elétrico, logística, equipamentos, impostos e homologação.', 'solar-integradores'); ?></p>
    </div>

    <div class="si-modal" data-si-quote-modal hidden>
        <div class="si-modal-backdrop" data-si-quote-close></div>
        <div class="si-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="si-quote-title">
            <button class="si-modal-x" type="button" data-si-quote-close aria-label="<?php esc_attr_e('Fechar', 'solar-integradores'); ?>">×</button>

            <div data-si-quote-step="confirm">
                <span class="si-eyebrow"><?php esc_html_e('Próximo passo', 'solar-integradores'); ?></span>
                <h3 id="si-quote-title"><?php esc_html_e('Vamos gerar o seu orçamento?', 'solar-integradores'); ?></h3>
                <p><?php esc_html_e('Preencha seus dados e receba um resumo personalizado desta estimativa.', 'solar-integradores'); ?></p>
                <div class="si-modal-actions">
                    <button class="si-button" type="button" data-si-quote-yes><?php esc_html_e('Sim, gerar orçamento', 'solar-integradores'); ?></button>
                    <button class="si-button si-button-secondary" type="button" data-si-quote-close><?php esc_html_e('Agora não', 'solar-integradores'); ?></button>
                </div>
            </div>

            <form class="si-form" data-si-quote-form data-si-quote-step="form" hidden>
                <input type="hidden" name="quote_token" data-si-quote-token>
                <input class="si-honeypot" type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">
                <label><span><?php esc_html_e('Nome completo', 'solar-integradores'); ?></span><input type="text" name="full_name" minlength="3" maxlength="190" autocomplete="name" required></label>
                <label><span><?php esc_html_e('Telefone com WhatsApp', 'solar-integradores'); ?></span><input type="tel" name="whatsapp" maxlength="20" autocomplete="tel" placeholder="(85) 99999-9999" required></label>
                <label><span><?php esc_html_e('E-mail', 'solar-integradores'); ?></span><input type="email" name="email" maxlength="190" autocomplete="email" required></label>
                <label>
                    <span><?php esc_html_e('Como prefere receber?', 'solar-integradores'); ?></span>
                    <select name="preferred_channel">
                        <option value="both"><?php esc_html_e('WhatsApp e e-mail', 'solar-integradores'); ?></option>
                        <option value="whatsapp"><?php esc_html_e('WhatsApp', 'solar-integradores'); ?></option>
                        <option value="email"><?php esc_html_e('E-mail', 'solar-integradores'); ?></option>
                    </select>
                </label>
                <label class="si-consent"><input type="checkbox" name="privacy_consent" value="1" required><span><?php esc_html_e('Autorizo o uso destes dados para gerar o orçamento e entrar em contato sobre o projeto.', 'solar-integradores'); ?></span></label>
                <button class="si-button" type="submit"><?php esc_html_e('Gerar meu orçamento', 'solar-integradores'); ?></button>
                <div class="si-message" data-si-quote-message aria-live="polite"></div>
            </form>

            <div data-si-quote-step="success" hidden>
                <span class="si-eyebrow"><?php esc_html_e('Solicitação concluída', 'solar-integradores'); ?></span>
                <h3><?php esc_html_e('Seu resumo está pronto', 'solar-integradores'); ?></h3>
                <p data-si-quote-success-message></p>
                <div class="si-modal-actions">
                    <a class="si-button" href="#" target="_blank" rel="noopener" data-si-quote-url><?php esc_html_e('Abrir orçamento em PDF', 'solar-integradores'); ?></a>
                    <a class="si-button si-button-whatsapp" href="#" target="_blank" rel="noopener" data-si-whatsapp-url><?php esc_html_e('Compartilhar no WhatsApp', 'solar-integradores'); ?></a>
                </div>
            </div>
        </div>
    </div>
</section>
