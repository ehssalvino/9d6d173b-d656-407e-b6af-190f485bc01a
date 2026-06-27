<?php
if (!defined('ABSPATH')) {
    exit;
}
$currency = static function ($value) {
    return 'R$ ' . number_format((float) $value, 2, ',', '.');
};
$number = static function ($value, $decimals = 2) {
    return number_format((float) $value, $decimals, ',', '.');
};
$equipment = $proposal['equipment'] ?? array();
$monthly = $proposal['monthly'] ?? array();
$payments = $proposal['payments'] ?? array();
$cash_flow = $proposal['cash_flow'] ?? array();
$max_generation = 1;
foreach ($monthly as $month) {
    $max_generation = max($max_generation, $month['consumption'], $month['generation']);
}
$last_cash = $cash_flow ? end($cash_flow) : array('cumulative' => 0);
$max_cash = max(1, (float) ($last_cash['cumulative'] ?? 1));
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<style>
    @page{margin:0}
    *{box-sizing:border-box}
    body{margin:0;font-family:"DejaVu Sans",sans-serif;color:#53616e;font-size:10pt}
    .page{position:relative;height:247mm;padding:28mm 16mm 15mm;page-break-after:always;background:#fff;overflow:hidden}
    .last{page-break-after:auto}
    .top{position:absolute;left:0;right:0;top:0;height:17mm;background:#347fc3;color:#fff;text-align:right;padding:6mm 16mm;font-size:11pt;letter-spacing:1px}
    .bottom{position:absolute;left:0;right:0;bottom:0;height:10mm;background:#075b99}
    h1,h2,h3{margin:0;color:#153b5b;text-transform:uppercase}
    h1{font-size:36pt;line-height:1.05}h2{font-size:22pt;margin-bottom:8mm}h3{font-size:14pt;color:#347fc3;margin:7mm 0 3mm}
    p{line-height:1.55}
    .cover{height:191mm;padding-top:84mm;background:#fff}
    .cover-band{position:absolute;left:0;right:0;top:0;height:68mm;background:#126aa4}
    .brand{font-size:23pt;color:#f49a3e;font-weight:bold;margin-bottom:12mm}
    .meta{margin-top:24mm;font-size:12pt;line-height:2}
    table{width:100%;border-collapse:collapse}
    .metrics{margin:8mm 0}.metrics td{width:33.33%;padding:7mm 3mm;text-align:center;background:#edf3f7;border-top:3px solid #f49a3e}
    .metrics strong{display:block;font-size:18pt;color:#111}.metrics small{text-transform:uppercase}
    .services td{width:50%;padding:3mm 5mm;color:#40566a;font-size:11pt}
    .services b{color:#347fc3;font-size:16pt}
    .data th{padding:3mm;background:#347fc3;color:#fff;text-align:left;text-transform:uppercase}
    .data td{padding:3mm;border-bottom:1px solid #aec4d4}
    .note{padding:4mm;background:#edf6fc;border-left:4px solid #f49a3e;font-size:9pt}
    .chart{height:75mm;border:1px solid #c7d6e0;padding:5mm 3mm 9mm;vertical-align:bottom}
    .chart td{height:62mm;vertical-align:bottom;text-align:center;font-size:7pt}
    .bars{width:100%;border-collapse:separate;border-spacing:1px}.bars td{vertical-align:bottom;padding:0}
    .bar-consumption{background:#f5b700;width:46%;display:inline-block}.bar-generation{background:#087cc1;width:46%;display:inline-block}
    .legend{text-align:center;margin-top:4mm}.yellow,.blue{display:inline-block;width:10px;height:10px}.yellow{background:#f5b700}.blue{background:#087cc1}
    .price{width:72%;margin:12mm auto;padding:10mm;background:#126aa4;color:#fff;text-align:center;border-radius:8mm}
    .price strong{display:block;font-size:27pt;margin-top:3mm}
    .cash td{height:92mm;vertical-align:bottom;text-align:center;padding:0 1px;font-size:6pt}
    .cash-bar{width:100%;background:#126aa4}
    .payments td{width:50%;padding:3mm 4mm;border-bottom:1px solid #cbd8e2}.payments strong{color:#153b5b}
    .signature{margin-top:22mm}.signature td{width:50%;padding-top:4mm;text-align:center;border-top:1px solid #126aa4}
    .small{font-size:8pt}
</style>
</head>
<body>
<section class="page cover">
    <div class="cover-band"></div>
    <div class="brand">Haja Geração Solar</div>
    <h1>Proposta<br>Comercial</h1>
    <div class="meta">
        <strong>Cliente:</strong> <?php echo esc_html($quote['customer_name']); ?><br>
        <strong>Local:</strong> <?php echo esc_html($quote['municipality'] . ' - ' . $quote['state']); ?><br>
        <strong>Data:</strong> <?php echo esc_html(date_i18n('d/m/Y')); ?><br>
        <strong>Validade:</strong> <?php echo esc_html($proposal['valid_days'] ?? 10); ?> dias
    </div>
    <div class="bottom"></div>
</section>

<section class="page">
    <div class="top">PROPOSTA COMERCIAL</div>
    <h2>Conheça a Haja Geração Solar</h2>
    <p>Desenvolvemos soluções de geração distribuída para residências, empresas, indústrias e agronegócios. Esta proposta reúne dimensionamento, equipamentos, instalação, homologação e acompanhamento técnico.</p>
    <table class="services">
        <tr><td><b>✓</b> Dimensionamento e proposta técnica</td><td><b>✓</b> Projeto elétrico e documentação</td></tr>
        <tr><td><b>✓</b> Fornecimento e logística</td><td><b>✓</b> Instalação e comissionamento</td></tr>
        <tr><td><b>✓</b> Solicitação de acesso</td><td><b>✓</b> Monitoramento e orientação</td></tr>
    </table>
    <h3>Como funciona</h3>
    <p>Os módulos convertem a radiação solar em eletricidade. O inversor adequa essa energia à rede do imóvel, e o medidor bidirecional registra a energia consumida e injetada.</p>
    <h3>Regulamentação no Brasil</h3>
    <p>A geração distribuída é disciplinada pela Lei nº 14.300/2022, pela regulamentação da ANEEL e pelos procedimentos técnicos da distribuidora. A compensação e os prazos dependem da modalidade, da data de conexão e das regras vigentes.</p>
    <p>O resultado definitivo depende da análise da unidade consumidora, padrão de entrada, demanda, rede local, sombreamento, estrutura e parecer de acesso.</p>
    <p class="note">Esta proposta automatizada é preliminar e será validada tecnicamente antes da contratação.</p>
    <div class="bottom"></div>
</section>

<section class="page">
    <div class="top">PROPOSTA COMERCIAL</div>
    <h2>Dimensionamento do sistema</h2>
    <p>Estimativa preparada com os dados informados pelo cliente e a irradiação disponível para a localização selecionada.</p>
    <table class="metrics"><tr>
        <td><strong><?php echo esc_html($number($result['system_kwp'] ?? 0)); ?> kWp</strong><small>Potência proposta</small></td>
        <td><strong><?php echo esc_html($number($result['monthly_generation_kwh'] ?? 0, 0)); ?> kWh</strong><small>Geração mensal</small></td>
        <td><strong><?php echo esc_html($number($proposal['area_m2'] ?? 0, 1)); ?> m²</strong><small>Área estimada</small></td>
    </tr></table>
    <h3>Principais equipamentos e serviços</h3>
    <table class="data">
        <tr><th>Item</th><th>Modelo</th><th>Quantidade</th></tr>
        <tr><td>Módulos</td><td><?php echo esc_html($equipment['module'] ?? 'A definir'); ?></td><td><?php echo esc_html($equipment['module_count'] ?? 0); ?></td></tr>
        <tr><td>Inversor(es)</td><td><?php echo esc_html($equipment['inverter'] ?? 'A definir'); ?></td><td>Conforme projeto</td></tr>
        <tr><td>Fornecedor cotado</td><td><?php echo esc_html($equipment['supplier'] ?? 'A definir'); ?></td><td>Kit compatível</td></tr>
    </table>
    <h3>Consumo x geração</h3>
    <table class="chart"><tr>
        <?php foreach ($monthly as $month) : ?>
        <td>
            <div class="bar-consumption" style="height:<?php echo esc_attr(max(2, ($month['consumption'] / $max_generation) * 55)); ?>mm"></div>
            <div class="bar-generation" style="height:<?php echo esc_attr(max(2, ($month['generation'] / $max_generation) * 55)); ?>mm"></div><br>
            <?php echo esc_html($month['month']); ?>
        </td>
        <?php endforeach; ?>
    </tr></table>
    <div class="legend"><span class="yellow"></span> Consumo &nbsp;&nbsp; <span class="blue"></span> Geração</div>
    <div class="bottom"></div>
</section>

<section class="page">
    <div class="top">PROPOSTA COMERCIAL</div>
    <h2>Análise financeira</h2>
    <p>Todos os indicadores usam o mesmo investimento apresentado na calculadora.</p>
    <table class="metrics"><tr>
        <td><strong><?php echo esc_html($number($proposal['payback_years'] ?? 0)); ?></strong><small>Payback simples (anos)</small></td>
        <td><strong><?php echo esc_html($currency($proposal['npv'] ?? 0)); ?></strong><small>VPL em 25 anos</small></td>
        <td><strong><?php echo esc_html($number($proposal['irr_percent'] ?? 0)); ?>%</strong><small>TIR estimada</small></td>
    </tr></table>
    <h3>Premissas utilizadas</h3>
    <table class="data">
        <tr><td>Economia anual inicial</td><td><?php echo esc_html($currency($result['estimated_annual_savings'] ?? 0)); ?></td></tr>
        <tr><td>Reajuste anual da energia</td><td><?php echo esc_html($number($proposal['assumptions']['tariff_inflation_percent'] ?? 0)); ?>%</td></tr>
        <tr><td>Degradação anual</td><td><?php echo esc_html($number($proposal['assumptions']['degradation_percent'] ?? 0)); ?>%</td></tr>
        <tr><td>Taxa de desconto do VPL</td><td><?php echo esc_html($number($proposal['assumptions']['discount_rate_percent'] ?? 0)); ?>%</td></tr>
        <tr><td>Manutenção anual</td><td><?php echo esc_html($number($proposal['assumptions']['maintenance_percent'] ?? 0)); ?>% do investimento</td></tr>
    </table>
    <div class="price">VALOR TOTAL DO INVESTIMENTO<strong><?php echo esc_html($currency($proposal['investment'] ?? 0)); ?></strong></div>
    <p class="note">Valores financeiros são projeções, não garantia de rentabilidade. Tarifa, geração real e regras de compensação podem variar.</p>
    <div class="bottom"></div>
</section>

<section class="page">
    <div class="top">PROPOSTA COMERCIAL</div>
    <h2>Fluxo de caixa do projeto</h2>
    <table class="cash"><tr>
        <?php foreach ($cash_flow as $flow) : ?>
        <td><div class="cash-bar" style="height:<?php echo esc_attr(max(2, min(82, ($flow['cumulative'] / $max_cash) * 82))); ?>mm"></div><?php echo esc_html($flow['year']); ?></td>
        <?php endforeach; ?>
    </tr></table>
    <table class="metrics"><tr>
        <td><strong><?php echo esc_html($currency($proposal['investment'] ?? 0)); ?></strong><small>Investimento inicial</small></td>
        <td><strong><?php echo esc_html($currency($last_cash['cumulative'] ?? 0)); ?></strong><small>Saldo em 25 anos</small></td>
        <td><strong><?php echo esc_html($number($result['annual_generation_kwh'] ?? 0, 0)); ?> kWh</strong><small>Geração anual inicial</small></td>
    </tr></table>
    <h3>Redução estimada da conta</h3>
    <p>A economia considera a energia compensável e a tarifa estimada. Custo de disponibilidade, iluminação pública, demanda contratada e itens não compensáveis permanecem conforme a unidade consumidora.</p>
    <div class="bottom"></div>
</section>

<section class="page">
    <div class="top">PROPOSTA COMERCIAL</div>
    <h2>Formas de pagamento</h2>
    <table class="metrics"><tr>
        <td><strong><?php echo esc_html($currency($proposal['cash_price'] ?? 0)); ?></strong><small>À vista</small></td>
        <td><strong><?php echo esc_html($currency($proposal['pix_price'] ?? 0)); ?></strong><small>Pix Mercado Pago</small></td>
        <td><strong><?php echo esc_html($currency($proposal['investment'] ?? 0)); ?></strong><small>Preço-base</small></td>
    </tr></table>
    <h3>Cartão de crédito via Mercado Pago</h3>
    <table class="payments">
        <?php foreach (array_chunk($payments, 2) as $row) : ?><tr>
            <?php foreach ($row as $payment) : ?>
            <td><strong><?php echo esc_html($payment['installments']); ?>x de <?php echo esc_html($currency($payment['installment'])); ?></strong><br>Total: <?php echo esc_html($currency($payment['total'])); ?></td>
            <?php endforeach; ?>
            <?php if (count($row) === 1) : ?><td></td><?php endif; ?>
        </tr><?php endforeach; ?>
    </table>
    <p class="note">As parcelas incluem as taxas configuradas. As condições efetivas devem ser confirmadas na conta Mercado Pago antes da cobrança.</p>
    <h3>Escopo sujeito a validação</h3>
    <p>Estrutura, frete, padrão de entrada, reforço estrutural, obras civis, proteções e adequações serão confirmados após vistoria e projeto executivo.</p>
    <div class="bottom"></div>
</section>

<section class="page last">
    <div class="top">PROPOSTA COMERCIAL</div>
    <h2>Aceite da proposta</h2>
    <p><strong>Proposta:</strong> <?php echo esc_html($proposal['proposal_number'] ?? ''); ?><br>
    <strong>Emissão:</strong> <?php echo esc_html(date_i18n('d/m/Y')); ?><br>
    <strong>Validade:</strong> <?php echo esc_html($proposal['valid_days'] ?? 10); ?> dias corridos.</p>
    <p>Declaro que recebi esta proposta preliminar e autorizo o prosseguimento para vistoria, validação técnica e emissão do instrumento contratual definitivo.</p>
    <table class="signature"><tr><td><?php echo esc_html($quote['customer_name']); ?><br>Cliente</td><td>Haja Geração Solar<br>Responsável comercial</td></tr></table>
    <h3>Contato</h3>
    <p><strong>Eduardo Salvino</strong><br>(21) 98251-8899<br>contato@hajageracaosolar.com.br<br>www.hajageracaosolar.com.br</p>
    <h3>Dados do cliente</h3>
    <p><?php echo esc_html($quote['customer_name']); ?><br><?php echo esc_html($quote['customer_phone']); ?><br><?php echo esc_html($quote['customer_email']); ?><br><?php echo esc_html($quote['municipality'] . ' - ' . $quote['state']); ?></p>
    <p class="note">Documento gerado automaticamente pelo sistema Solar Integradores.</p>
    <div class="bottom"></div>
</section>
</body>
</html>
