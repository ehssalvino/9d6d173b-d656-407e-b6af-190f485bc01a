<?php
if (!defined('ABSPATH')) {
    exit;
}
$proposal = $result['proposal'] ?? SI_Proposal::build($input, $result, $this->settings());
$equipment = $proposal['equipment'];
$payments = $proposal['payments'];
$max_generation = 1;
foreach ($proposal['monthly'] as $month) {
    $max_generation = max($max_generation, $month['consumption'], $month['generation']);
}
$last_cash = end($proposal['cash_flow']);
$max_cash = max(1, (float) ($last_cash['cumulative'] ?? 1));
$currency = static function ($value) {
    return 'R$ ' . number_format_i18n((float) $value, 2);
};
$number = static function ($value, $decimals = 2) {
    return number_format_i18n((float) $value, $decimals);
};
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title><?php echo esc_html('Proposta solar - ' . $quote->customer_name); ?></title>
    <style>
        :root{--blue:#075b99;--blue2:#347fc3;--orange:#f7a24a;--ink:#153b5b;--muted:#59636d;--paper:#fff}
        *{box-sizing:border-box}body{margin:0;background:#dce5ec;color:var(--muted);font-family:Arial,sans-serif}
        .toolbar{position:sticky;top:0;z-index:9;display:flex;justify-content:center;gap:12px;padding:12px;background:#123b59}
        .toolbar button{border:0;border-radius:999px;padding:12px 20px;background:var(--orange);color:#17334a;font-weight:800;cursor:pointer}
        .proposal{width:210mm;margin:20px auto}.page{position:relative;width:210mm;min-height:297mm;padding:25mm 16mm 18mm;overflow:hidden;background:linear-gradient(145deg,#fff 0 55%,#fff5e9 100%);page-break-after:always;box-shadow:0 8px 35px rgba(0,0,0,.16)}
        .page:before{content:"";position:absolute;inset:0;background:linear-gradient(135deg,rgba(7,91,153,.06),transparent 42%);pointer-events:none}
        .topbar{position:absolute;left:0;right:0;top:0;height:17mm;background:var(--blue2);color:#fff;padding:6mm 16mm;text-align:right;letter-spacing:.08em}
        .footer{position:absolute;left:0;right:0;bottom:0;height:10mm;background:var(--blue)}
        h1,h2,h3{position:relative;margin:0;color:var(--ink);text-transform:uppercase}h1{font-size:34pt;line-height:1.02}h2{font-size:22pt;margin-bottom:8mm}h3{font-size:14pt;margin:6mm 0 3mm;color:var(--blue2)}
        p{position:relative;font-size:11pt;line-height:1.55}.cover{padding-top:95mm}.cover h1{font-size:42pt;color:var(--blue)}.brand{font-size:24pt;color:var(--orange);font-weight:800;margin-bottom:12mm}.meta{margin-top:25mm;font-size:13pt;line-height:2}
        .hero{position:absolute;left:0;right:0;top:0;height:88mm;background:linear-gradient(120deg,var(--blue),transparent),url('<?php echo esc_url(SI_URL . 'assets/images/proposal-solar.jpg'); ?>') center/cover}
        .metrics{display:grid;grid-template-columns:repeat(3,1fr);gap:6mm;margin:8mm 0}.metric{text-align:center;padding:6mm 3mm;background:#f4f8fb;border-top:3px solid var(--orange)}.metric strong{display:block;color:#111;font-size:20pt}.metric span{font-size:9pt;text-transform:uppercase}
        .services{display:grid;grid-template-columns:1fr 1fr;gap:5mm 10mm;margin-top:8mm}.service{padding-left:8mm;position:relative}.service:before{content:"✓";position:absolute;left:0;color:var(--blue2);font-size:18pt;font-weight:bold}
        table{position:relative;width:100%;border-collapse:collapse;margin:5mm 0;font-size:10pt}th{background:var(--blue2);color:#fff;text-transform:uppercase}th,td{padding:3.2mm;border-bottom:1px solid #aac0d1;text-align:left}
        .chart{position:relative;display:flex;align-items:flex-end;gap:3mm;height:72mm;padding:10mm 4mm 8mm;border:1px solid #cbd8e2;background:rgba(255,255,255,.75)}.month{flex:1;height:100%;display:flex;align-items:flex-end;justify-content:center;gap:1px;position:relative}.bar{width:42%;min-height:2px}.consumption{background:#f5b700}.generation{background:#087cc1}.month-label{position:absolute;bottom:-6mm;font-size:8pt}
        .legend{display:flex;justify-content:center;gap:8mm;font-size:9pt;margin-top:7mm}.dot{display:inline-block;width:10px;height:10px;margin-right:4px}
        .financial{display:grid;grid-template-columns:repeat(3,1fr);gap:5mm}.financial .metric{background:#fff;border:1px solid #cbd8e2;border-top:4px solid var(--blue2)}
        .cash-chart{height:105mm;display:flex;align-items:flex-end;gap:1.2mm;padding:8mm 3mm 10mm;border:1px solid #cbd8e2}.cash-col{flex:1;background:linear-gradient(var(--blue2),var(--blue));min-height:2px;position:relative}.cash-col span{position:absolute;bottom:-7mm;left:50%;transform:translateX(-50%);font-size:6.5pt}
        .price-box{margin:12mm auto;padding:10mm;background:linear-gradient(var(--blue2),var(--blue));color:#fff;text-align:center;border-radius:8mm;width:72%}.price-box strong{display:block;font-size:28pt;margin-top:3mm}
        .payments{display:grid;grid-template-columns:1fr 1fr;gap:3mm 8mm}.payment{padding:3mm;border-bottom:1px solid #d5dee5}.payment strong{color:var(--ink)}
        .signature{display:grid;grid-template-columns:1fr 1fr;gap:20mm;margin-top:18mm;text-align:center}.signature div{padding-top:4mm;border-top:1px solid var(--blue)}
        .note{padding:4mm;background:#edf6fc;border-left:4px solid var(--orange);font-size:9pt}.small{font-size:8.5pt}.regulation{columns:2;column-gap:10mm}
        @media(max-width:850px){.proposal{width:100%;margin:0}.page{width:100%;min-height:auto;padding:80px 24px 60px}.metrics,.financial{grid-template-columns:1fr}.services,.payments{grid-template-columns:1fr}.hero{height:240px}.cover{padding-top:280px}}
        @media print{
            @page{size:A4;margin:0}
            body{background:#fff}.toolbar{display:none}.proposal{margin:0}
            .page{box-shadow:none;margin:0;width:210mm;height:297mm;min-height:297mm;padding:25mm 16mm 18mm}
            .metrics,.financial{grid-template-columns:repeat(3,1fr)}
            .services,.payments{grid-template-columns:1fr 1fr}
            .hero{height:88mm}.cover{padding-top:95mm}
        }
    </style>
</head>
<body>
<div class="toolbar">
    <a href="<?php echo esc_url(add_query_arg('si_quote_pdf', $quote->public_token, home_url('/'))); ?>" style="border-radius:999px;padding:12px 20px;background:#f7a24a;color:#17334a;font-weight:800;text-decoration:none"><?php esc_html_e('Abrir orçamento em PDF', 'solar-integradores'); ?></a>
</div>
<main class="proposal">
    <section class="page cover">
        <div class="hero"></div>
        <div class="brand">Haja Geração Solar</div>
        <h1>Proposta<br>Comercial</h1>
        <div class="meta">
            <strong>Cliente:</strong> <?php echo esc_html($quote->customer_name); ?><br>
            <strong>Local:</strong> <?php echo esc_html($quote->municipality . ' - ' . $quote->state); ?><br>
            <strong>Data:</strong> <?php echo esc_html(mysql2date('d/m/Y', $quote->created_at)); ?><br>
            <strong>Validade:</strong> <?php echo esc_html($proposal['valid_days']); ?> dias
        </div>
        <div class="footer"></div>
    </section>

    <section class="page">
        <div class="topbar">PROPOSTA COMERCIAL</div>
        <h2>Conheça a Haja Geração Solar</h2>
        <p>Desenvolvemos soluções de geração distribuída para residências, empresas, indústrias e agronegócios. Esta proposta reúne dimensionamento, equipamentos, instalação, homologação e acompanhamento técnico.</p>
        <div class="services">
            <div class="service">Dimensionamento e proposta técnica inicial</div>
            <div class="service">Projeto elétrico e documentação</div>
            <div class="service">Fornecimento e logística dos equipamentos</div>
            <div class="service">Instalação e comissionamento</div>
            <div class="service">Solicitação de acesso à distribuidora</div>
            <div class="service">Monitoramento e orientação de uso</div>
        </div>
        <h3>Como funciona</h3>
        <p>Os módulos convertem a radiação solar em eletricidade. O inversor adequa essa energia à rede do imóvel, e o medidor bidirecional registra a energia consumida e injetada. Créditos excedentes seguem as regras da distribuidora e do Sistema de Compensação de Energia Elétrica.</p>
        <h3>Regulamentação no Brasil</h3>
        <div class="regulation">
            <p>A geração distribuída é disciplinada pela Lei nº 14.300/2022, pela regulamentação da ANEEL e pelos procedimentos técnicos da distribuidora. A compensação, a cobrança pelo uso da rede e os prazos dependem da modalidade, da data de conexão e das regras vigentes.</p>
            <p>O resultado definitivo depende da análise da unidade consumidora, padrão de entrada, demanda, rede local, sombreamento, estrutura e parecer de acesso.</p>
        </div>
        <p class="note">Esta proposta automatizada é preliminar e será validada tecnicamente antes da contratação.</p>
        <div class="footer"></div>
    </section>

    <section class="page">
        <div class="topbar">PROPOSTA COMERCIAL</div>
        <h2>Dimensionamento do sistema</h2>
        <p>O sistema foi estimado com os dados informados pelo cliente e a irradiação disponível para a localização selecionada.</p>
        <div class="metrics">
            <div class="metric"><strong><?php echo esc_html($number($result['system_kwp'])); ?> kWp</strong><span>Potência proposta</span></div>
            <div class="metric"><strong><?php echo esc_html($number($result['monthly_generation_kwh'], 0)); ?> kWh</strong><span>Geração mensal</span></div>
            <div class="metric"><strong><?php echo esc_html($number($proposal['area_m2'], 1)); ?> m²</strong><span>Área estimada</span></div>
        </div>
        <h3>Principais equipamentos e serviços</h3>
        <table>
            <thead><tr><th>Item</th><th>Modelo</th><th>Quantidade</th></tr></thead>
            <tbody>
            <tr><td>Módulos fotovoltaicos</td><td><?php echo esc_html($equipment['module'] . ' - ' . $equipment['module_power_w'] . ' W'); ?></td><td><?php echo esc_html($equipment['module_count'] . ' módulos / ' . $number($equipment['installed_kwp']) . ' kWp'); ?></td></tr>
            <tr><td>Inversor(es)</td><td><?php echo esc_html($equipment['inverter_model']); ?></td><td><?php echo esc_html($equipment['inverter_count'] . ' × ' . $number($equipment['inverter_unit_power_kw']) . ' kW'); ?></td></tr>
            <tr><td>Relação DC/AC</td><td><?php echo esc_html($equipment['inverter']); ?></td><td><?php echo esc_html($number($equipment['inverter_overload_percent']) . '% overload'); ?></td></tr>
            <tr><td>Fornecedor cotado</td><td><?php echo esc_html($equipment['supplier']); ?></td><td>Kit compatível</td></tr>
            </tbody>
        </table>
        <h3>Consumo x geração</h3>
        <div class="chart">
            <?php foreach ($proposal['monthly'] as $month) : ?>
                <div class="month">
                    <div class="bar consumption" style="height:<?php echo esc_attr(max(1, ($month['consumption'] / $max_generation) * 100)); ?>%"></div>
                    <div class="bar generation" style="height:<?php echo esc_attr(max(1, ($month['generation'] / $max_generation) * 100)); ?>%"></div>
                    <span class="month-label"><?php echo esc_html($month['month']); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="legend"><span><i class="dot consumption"></i>Consumo</span><span><i class="dot generation"></i>Geração</span></div>
        <div class="footer"></div>
    </section>

    <section class="page">
        <div class="topbar">PROPOSTA COMERCIAL</div>
        <h2>Análise financeira</h2>
        <p>Todos os indicadores abaixo usam o mesmo valor de investimento apresentado pela calculadora, evitando diferenças entre o site e a proposta.</p>
        <div class="financial">
            <div class="metric"><strong><?php echo esc_html($proposal['payback_years'] === null ? '-' : $number($proposal['payback_years'])); ?></strong><span>Payback simples (anos)</span></div>
            <div class="metric"><strong><?php echo esc_html($currency($proposal['npv'])); ?></strong><span>VPL em 25 anos</span></div>
            <div class="metric"><strong><?php echo esc_html($number($proposal['irr_percent'])); ?>%</strong><span>TIR estimada</span></div>
        </div>
        <h3>Premissas utilizadas</h3>
        <table>
            <tbody>
            <tr><td>Economia anual inicial</td><td><?php echo esc_html($currency($result['estimated_annual_savings'])); ?></td></tr>
            <tr><td>Reajuste anual da energia</td><td><?php echo esc_html($number($proposal['assumptions']['tariff_inflation_percent'])); ?>%</td></tr>
            <tr><td>Degradação anual dos módulos</td><td><?php echo esc_html($number($proposal['assumptions']['degradation_percent'])); ?>%</td></tr>
            <tr><td>Taxa de desconto do VPL</td><td><?php echo esc_html($number($proposal['assumptions']['discount_rate_percent'])); ?>%</td></tr>
            <tr><td>Manutenção anual estimada</td><td><?php echo esc_html($number($proposal['assumptions']['maintenance_percent'])); ?>% do investimento</td></tr>
            </tbody>
        </table>
        <div class="price-box">VALOR TOTAL DO INVESTIMENTO<strong><?php echo esc_html($currency($proposal['investment'])); ?></strong></div>
        <p class="note">Valores financeiros são projeções, não garantia de rentabilidade. Tarifa, geração real, incidência solar e regras de compensação podem variar.</p>
        <div class="footer"></div>
    </section>

    <section class="page">
        <div class="topbar">PROPOSTA COMERCIAL</div>
        <h2>Fluxo de caixa do projeto</h2>
        <div class="cash-chart">
            <?php foreach ($proposal['cash_flow'] as $flow) : ?>
                <div class="cash-col" style="height:<?php echo esc_attr(max(1, min(100, ($flow['cumulative'] / $max_cash) * 100))); ?>%"><span><?php echo esc_html($flow['year']); ?></span></div>
            <?php endforeach; ?>
        </div>
        <p class="small">Eixo horizontal: anos após a instalação. As barras mostram o saldo acumulado estimado, já descontada a manutenção configurada.</p>
        <div class="metrics">
            <div class="metric"><strong><?php echo esc_html($currency($proposal['investment'])); ?></strong><span>Investimento inicial</span></div>
            <div class="metric"><strong><?php echo esc_html($currency($last_cash['cumulative'] ?? 0)); ?></strong><span>Saldo em 25 anos</span></div>
            <div class="metric"><strong><?php echo esc_html($number($result['annual_generation_kwh'], 0)); ?> kWh</strong><span>Geração anual inicial</span></div>
        </div>
        <h3>Redução estimada da conta</h3>
        <p>A economia considera a energia compensável e a tarifa estimada da distribuidora. O custo de disponibilidade, iluminação pública, demanda contratada e itens não compensáveis permanecem conforme a unidade consumidora.</p>
        <div class="footer"></div>
    </section>

    <section class="page">
        <div class="topbar">PROPOSTA COMERCIAL</div>
        <h2>Formas de pagamento</h2>
        <div class="metrics">
            <div class="metric"><strong><?php echo esc_html($currency($proposal['cash_price'])); ?></strong><span>À vista</span></div>
            <div class="metric"><strong><?php echo esc_html($currency($proposal['pix_price'])); ?></strong><span>Pix Mercado Pago</span></div>
            <div class="metric"><strong><?php echo esc_html($currency($proposal['investment'])); ?></strong><span>Preço-base</span></div>
        </div>
        <h3>Cartão de crédito via Mercado Pago</h3>
        <div class="payments">
            <?php foreach ($payments as $payment) : ?>
                <div class="payment"><strong><?php echo esc_html($payment['installments']); ?>x de <?php echo esc_html($currency($payment['installment'])); ?></strong><br><span>Total: <?php echo esc_html($currency($payment['total'])); ?></span></div>
            <?php endforeach; ?>
        </div>
        <p class="note">As parcelas incluem as taxas configuradas para que o valor líquido do projeto permaneça compatível com o preço-base. As condições efetivas devem ser confirmadas na conta Mercado Pago antes da cobrança.</p>
        <h3>Escopo sujeito a validação</h3>
        <p>Estrutura de fixação, frete, padrão de entrada, reforço estrutural, obras civis, proteção adicional, adequações de rede e condições especiais serão confirmados após vistoria e projeto executivo.</p>
        <div class="footer"></div>
    </section>

    <section class="page">
        <div class="topbar">PROPOSTA COMERCIAL</div>
        <h2>Aceite da proposta</h2>
        <p><strong>Proposta:</strong> <?php echo esc_html($proposal['proposal_number']); ?><br>
        <strong>Emissão:</strong> <?php echo esc_html(mysql2date('d/m/Y', $quote->created_at)); ?><br>
        <strong>Validade:</strong> <?php echo esc_html($proposal['valid_days']); ?> dias corridos.</p>
        <p>Declaro que recebi esta proposta preliminar e autorizo o prosseguimento para vistoria, validação técnica e emissão do instrumento contratual definitivo.</p>
        <div class="signature">
            <div><?php echo esc_html($quote->customer_name); ?><br>Cliente</div>
            <div>Haja Geração Solar<br>Responsável comercial</div>
        </div>
        <h3>Contato</h3>
        <p><strong>Eduardo Salvino</strong><br>(21) 98251-8899<br>contato@hajageracaosolar.com.br<br>www.hajageracaosolar.com.br</p>
        <h3>Dados do cliente</h3>
        <p><?php echo esc_html($quote->customer_name); ?><br><?php echo esc_html($quote->customer_phone); ?><br><?php echo esc_html($quote->customer_email); ?><br><?php echo esc_html($quote->municipality . ' - ' . $quote->state); ?></p>
        <p class="note">O envio automático por WhatsApp exige a API oficial do WhatsApp Business. Até sua configuração, o sistema gera o link de compartilhamento e envia por e-mail conforme a opção do cliente.</p>
        <div class="footer"></div>
    </section>
</main>
</body>
</html>
