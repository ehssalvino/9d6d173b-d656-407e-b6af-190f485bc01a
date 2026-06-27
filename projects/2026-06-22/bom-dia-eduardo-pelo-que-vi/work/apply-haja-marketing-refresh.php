<?php
$mysqli = new mysqli('127.0.0.1', 'root', '', 'wordpress_local');
if ($mysqli->connect_errno) {
    fwrite(STDERR, $mysqli->connect_error . PHP_EOL);
    exit(1);
}
$mysqli->set_charset('utf8mb4');

$site = 'http://localhost/hajageracaosolar';
$wa = 'https://api.whatsapp.com/send?phone=5521969022250&text=Ol%C3%A1%2C%20quero%20simular%20minha%20economia%20com%20energia%20solar.';
$hero = $site . '/wp-content/uploads/2024/07/Instalacao-Modulos-3.jpg';
$job1 = $site . '/wp-content/uploads/2024/07/IMG-20240421-WA0013.jpg';
$job2 = $site . '/wp-content/uploads/2024/07/Instalacao-Inversor-3.jpg';
$job3 = $site . '/wp-content/uploads/2024/07/IMG-20231009-WA0008.jpg';

function q(mysqli $db, string $sql, string $types = '', array $params = []): void {
    $stmt = $db->prepare($sql);
    if (!$stmt) {
        throw new RuntimeException($db->error);
    }
    if ($types) {
        $stmt->bind_param($types, ...$params);
    }
    if (!$stmt->execute()) {
        throw new RuntimeException($stmt->error);
    }
    $stmt->close();
}

function page_id_by_slug(mysqli $db, string $slug): ?int {
    $stmt = $db->prepare("SELECT ID FROM wp_posts WHERE post_name=? AND post_type='page' LIMIT 1");
    $stmt->bind_param('s', $slug);
    $stmt->execute();
    $stmt->bind_result($id);
    $found = $stmt->fetch();
    $stmt->close();
    return $found ? (int)$id : null;
}

function upsert_page(mysqli $db, string $title, string $slug, string $content, string $excerpt = ''): int {
    $now = date('Y-m-d H:i:s');
    $id = page_id_by_slug($db, $slug);
    if ($id) {
        q($db, "UPDATE wp_posts SET post_title=?, post_content=?, post_excerpt=?, post_status='publish', post_modified=?, post_modified_gmt=UTC_TIMESTAMP() WHERE ID=?", 'ssssi', [$title, $content, $excerpt, $now, $id]);
    } else {
        $guid = 'http://localhost/hajageracaosolar/' . $slug . '/';
        q($db, "INSERT INTO wp_posts (post_author, post_date, post_date_gmt, post_content, post_title, post_excerpt, post_status, comment_status, ping_status, post_name, post_modified, post_modified_gmt, post_parent, guid, menu_order, post_type, post_mime_type, comment_count) VALUES (1, ?, UTC_TIMESTAMP(), ?, ?, ?, 'publish', 'closed', 'closed', ?, ?, UTC_TIMESTAMP(), 0, ?, 0, 'page', '', 0)", 'sssssss', [$now, $content, $title, $excerpt, $slug, $now, $guid]);
        $id = (int)$db->insert_id;
    }
    q($db, "DELETE FROM wp_postmeta WHERE post_id=? AND meta_key IN ('_elementor_data','_elementor_edit_mode','_elementor_css','_elementor_controls_usage')", 'i', [$id]);
    q($db, "INSERT INTO wp_postmeta (post_id, meta_key, meta_value) VALUES (?, '_wp_page_template', 'elementor_header_footer') ON DUPLICATE KEY UPDATE meta_value=VALUES(meta_value)", 'i', [$id]);
    return $id;
}

function style_block(): string {
    return <<<'HTML'
<style>
.haja-page{font-family:Montserrat,Arial,sans-serif;color:#16302d;background:#fff}
.haja-page *{box-sizing:border-box}.haja-wrap{max-width:1160px;margin:0 auto;padding:0 22px}
.haja-hero{position:relative;min-height:680px;display:flex;align-items:center;color:#fff;background-size:cover;background-position:center}
.haja-hero:before{content:"";position:absolute;inset:0;background:linear-gradient(90deg,rgba(8,34,32,.86),rgba(8,34,32,.54),rgba(8,34,32,.24))}
.haja-hero .haja-wrap{position:relative}.haja-kicker{font-size:13px;text-transform:uppercase;letter-spacing:.08em;font-weight:800;color:#ffc44d;margin:0 0 14px}
.haja-hero h1{max-width:880px;font-size:clamp(36px,5vw,68px);line-height:1.02;margin:0 0 22px;font-weight:900;letter-spacing:0}
.haja-hero p{max-width:720px;font-size:20px;line-height:1.55;margin:0 0 28px;color:#eef7f3}
.haja-actions{display:flex;gap:14px;flex-wrap:wrap}.haja-btn{display:inline-flex;align-items:center;justify-content:center;min-height:48px;padding:14px 22px;border-radius:6px;text-decoration:none;font-weight:800;border:2px solid transparent}
.haja-btn.primary{background:#f4ab17;color:#102926}.haja-btn.secondary{background:rgba(255,255,255,.08);border-color:#fff;color:#fff}
.haja-band{padding:72px 0}.haja-band.alt{background:#f6faf8}.haja-head{max-width:760px;margin:0 0 34px}.haja-head h2{font-size:clamp(28px,3vw,42px);line-height:1.12;margin:0 0 12px;color:#008481}.haja-head p{font-size:18px;line-height:1.6;color:#52615f;margin:0}
.haja-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:18px}.haja-card{background:#fff;border:1px solid #dfe9e6;border-radius:8px;padding:24px;box-shadow:0 10px 26px rgba(9,40,37,.06)}
.haja-card h3{font-size:21px;margin:0 0 10px;color:#16302d}.haja-card p,.haja-card li{color:#52615f;line-height:1.55}.haja-card ul{padding-left:18px;margin:0}.haja-stat strong{display:block;font-size:34px;color:#f4ab17;line-height:1}.haja-stat span{display:block;margin-top:8px;font-weight:700;color:#16302d}
.haja-steps{counter-reset:step;display:grid;grid-template-columns:repeat(5,1fr);gap:14px}.haja-step{background:#fff;border-left:4px solid #f4ab17;padding:20px;border-radius:8px}.haja-step:before{counter-increment:step;content:counter(step);display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:50%;background:#008481;color:#fff;font-weight:900;margin-bottom:14px}
.haja-case{overflow:hidden;padding:0}.haja-case img{width:100%;height:210px;object-fit:cover;display:block}.haja-case div{padding:22px}.haja-simulator{background:#08312e;color:#fff;border-radius:8px;padding:32px}.haja-simulator h2{color:#fff;margin-top:0}.haja-simulator p{color:#d8ebe6}.haja-final{background:#008481;color:#fff;text-align:center}.haja-final h2{color:#fff;font-size:clamp(30px,4vw,50px);margin:0 0 16px}.haja-final p{max-width:760px;margin:0 auto 26px;font-size:19px;color:#e8fffb}
.haja-split{display:grid;grid-template-columns:1.1fr .9fr;gap:32px;align-items:center}.haja-photo{width:100%;border-radius:8px;display:block}
@media(max-width:900px){.haja-grid,.haja-steps,.haja-split{grid-template-columns:1fr 1fr}.haja-hero{min-height:620px}}@media(max-width:640px){.haja-grid,.haja-steps,.haja-split{grid-template-columns:1fr}.haja-hero h1{font-size:36px}.haja-band{padding:54px 0}}
</style>
HTML;
}

$home = style_block() . <<<HTML
<main class="haja-page">
  <section class="haja-hero" style="background-image:url('$hero')">
    <div class="haja-wrap">
      <p class="haja-kicker">Energia solar com engenharia e resultado</p>
      <h1>Economize até 95% na sua conta de energia com um sistema solar projetado por engenheiros especializados.</h1>
      <p>Projeto, instalação, homologação e monitoramento completo para residências, empresas e propriedades rurais.</p>
      <div class="haja-actions">
        <a class="haja-btn primary" href="$site/calculadora-solar/">Simular Minha Economia</a>
        <a class="haja-btn secondary" href="$wa">Falar no WhatsApp</a>
      </div>
    </div>
  </section>
  <section class="haja-band alt">
    <div class="haja-wrap haja-grid">
      <div class="haja-card haja-stat"><strong>Até 95%</strong><span>de redução na conta de luz</span></div>
      <div class="haja-card haja-stat"><strong>Projeto</strong><span>dimensionado por engenharia</span></div>
      <div class="haja-card haja-stat"><strong>Completo</strong><span>instalação, homologação e monitoramento</span></div>
      <div class="haja-card haja-stat"><strong>B2B</strong><span>homologação para integradores em todo o Brasil</span></div>
    </div>
  </section>
  <section class="haja-band">
    <div class="haja-wrap">
      <div class="haja-head"><h2>Uma empresa de engenharia solar, não apenas uma instaladora</h2><p>A Haja desenvolve sistemas fotovoltaicos com análise técnica, segurança elétrica e foco em retorno financeiro. O objetivo é simples: gerar economia real com um projeto bem dimensionado desde o primeiro estudo.</p></div>
      <div class="haja-grid">
        <a class="haja-card" href="$site/energia-solar-residencial/"><h3>Energia Solar Residencial</h3><p>Sistemas para casas e condomínios com economia mensal, valorização do imóvel e acompanhamento completo.</p></a>
        <a class="haja-card" href="$site/energia-solar-comercial/"><h3>Energia Solar Comercial</h3><p>Projetos para empresas que querem reduzir custo fixo, proteger margem e previsibilizar a conta de energia.</p></a>
        <a class="haja-card" href="$site/energia-solar-rural/"><h3>Energia Solar Rural</h3><p>Soluções para fazendas, sítios e atividades produtivas com alto consumo e necessidade de confiabilidade.</p></a>
        <a class="haja-card" href="$site/homologacao-para-integradores/"><h3>Homologação para Integradores</h3><p>Projetos, documentação e suporte técnico para integradores que precisam aprovar sistemas com distribuidoras.</p></a>
      </div>
    </div>
  </section>
  <section class="haja-band alt">
    <div class="haja-wrap">
      <div class="haja-head"><h2>Como funciona</h2><p>Um processo direto, técnico e transparente para sair da conta alta para a geração própria.</p></div>
      <div class="haja-steps">
        <div class="haja-step"><h3>Análise da conta</h3><p>Entendemos seu consumo, tarifa, padrão de uso e objetivo financeiro.</p></div>
        <div class="haja-step"><h3>Projeto personalizado</h3><p>Dimensionamento técnico para o seu telhado, carga e distribuidora.</p></div>
        <div class="haja-step"><h3>Instalação</h3><p>Equipe qualificada executa o sistema com segurança e padrão técnico.</p></div>
        <div class="haja-step"><h3>Homologação</h3><p>Cuidamos da aprovação junto à concessionária de energia.</p></div>
        <div class="haja-step"><h3>Economia</h3><p>Você acompanha a geração e a redução na conta mês a mês.</p></div>
      </div>
    </div>
  </section>
  <section class="haja-band">
    <div class="haja-wrap haja-split">
      <div class="haja-simulator">
        <h2>Simule sua economia solar</h2>
        <p>Informe sua conta de energia e veja uma estimativa inicial. Depois a equipe faz o diagnóstico gratuito pelo WhatsApp.</p>
        [si_solar_calculator mode="public"]
      </div>
      <div>
        <div class="haja-head"><h2>O que você recebe no estudo</h2><p>Estimativa de economia anual, potência recomendada, caminho de homologação, possibilidade de financiamento e próximos passos para instalar com segurança.</p></div>
        <a class="haja-btn primary" href="$wa">Solicitar Estudo Gratuito</a>
      </div>
    </div>
  </section>
  <section class="haja-band alt">
    <div class="haja-wrap">
      <div class="haja-head"><h2>Projetos reais, engenharia visível</h2><p>Use esta seção para inserir os resultados finais de cada cliente: conta anterior, conta atual e economia obtida. As imagens já mostram obras e instalações da Haja.</p></div>
      <div class="haja-grid">
        <div class="haja-card haja-case"><img src="$job1" alt="Instalação solar residencial"><div><h3>Residencial</h3><p>Projeto para reduzir a conta mensal e aumentar a previsibilidade do custo de energia.</p></div></div>
        <div class="haja-card haja-case"><img src="$job2" alt="Instalação de inversor solar"><div><h3>Comercial</h3><p>Dimensionamento para empresas que precisam proteger margem e diminuir despesa fixa.</p></div></div>
        <div class="haja-card haja-case"><img src="$job3" alt="Equipe em instalação solar"><div><h3>Projetos complexos</h3><p>Homologação, laudos e suporte técnico para cenários que exigem engenharia.</p></div></div>
        <div class="haja-card"><h3>Prova social</h3><p>Próximo passo recomendado: adicionar vídeos, prints de WhatsApp e avaliações do Google para aumentar a confiança antes do contato.</p></div>
      </div>
    </div>
  </section>
  <section class="haja-band">
    <div class="haja-wrap">
      <div class="haja-head"><h2>Por que escolher a Haja</h2><p>Energia solar é investimento. Por isso, a decisão precisa ser técnica, financeira e segura.</p></div>
      <div class="haja-grid">
        <div class="haja-card"><h3>Engenharia especializada</h3><p>Projetos dimensionados por profissionais que entendem de elétrica, homologação e retorno financeiro.</p></div>
        <div class="haja-card"><h3>Segurança elétrica</h3><p>Instalação com atenção a normas, componentes, proteção e qualidade de execução.</p></div>
        <div class="haja-card"><h3>Homologação completa</h3><p>Acompanhamento da documentação junto à concessionária até o sistema estar regularizado.</p></div>
        <div class="haja-card"><h3>Financiamento solar</h3><p>Opções para parcelar o sistema e trocar a conta de luz por um investimento mensal.</p></div>
      </div>
    </div>
  </section>
  <section class="haja-band haja-final">
    <div class="haja-wrap">
      <h2>Descubra quanto você pode economizar ainda este mês.</h2>
      <p>Envie sua conta de energia e receba um estudo gratuito para entender o melhor sistema para sua casa, empresa ou propriedade rural.</p>
      <a class="haja-btn primary" href="$wa">Solicitar Estudo Gratuito</a>
    </div>
  </section>
</main>
HTML;

$landingStyle = style_block();
$pages = [
    ['Energia Solar Residencial', 'energia-solar-residencial', $landingStyle . <<<HTML
<main class="haja-page"><section class="haja-band"><div class="haja-wrap haja-split"><div><p class="haja-kicker">Energia solar para casas</p><h1>Energia Solar Residencial com projeto de engenharia</h1><p>Reduza a conta de luz da sua casa em até 95% com um sistema fotovoltaico dimensionado para o seu consumo, telhado e distribuidora.</p><div class="haja-actions"><a class="haja-btn primary" href="$site/calculadora-solar/">Simular economia</a><a class="haja-btn secondary" style="color:#008481;border-color:#008481" href="$wa">Falar no WhatsApp</a></div></div><img class="haja-photo" src="$job1" alt="Energia solar residencial"></div></section><section class="haja-band alt"><div class="haja-wrap haja-grid"><div class="haja-card"><h3>Conta menor</h3><p>Troque uma despesa recorrente por geração própria de energia.</p></div><div class="haja-card"><h3>Projeto personalizado</h3><p>Dimensionamento para consumo, área disponível e padrão elétrico.</p></div><div class="haja-card"><h3>Homologação inclusa</h3><p>A Haja acompanha a aprovação junto à concessionária.</p></div><div class="haja-card"><h3>Monitoramento</h3><p>Acompanhe a geração e o desempenho do sistema.</p></div></div></section><section class="haja-band"><div class="haja-wrap"><h2>Energia solar para casas</h2><p>A energia solar residencial é indicada para famílias que querem reduzir a conta de energia, aumentar a previsibilidade dos gastos e valorizar o imóvel. A Haja cuida do estudo, projeto, instalação e homologação para que o sistema seja seguro e economicamente viável.</p><a class="haja-btn primary" href="$wa">Solicitar estudo residencial</a></div></section></main>
HTML, 'Energia solar residencial para casas com projeto, instalação e homologação.'],
    ['Energia Solar Comercial', 'energia-solar-comercial', $landingStyle . <<<HTML
<main class="haja-page"><section class="haja-band"><div class="haja-wrap haja-split"><div><p class="haja-kicker">Energia solar para empresas</p><h1>Energia Solar Comercial para reduzir custo fixo</h1><p>Projetos solares para empresas, lojas, galpões, clínicas e indústrias que precisam controlar despesas e melhorar margem.</p><div class="haja-actions"><a class="haja-btn primary" href="$site/calculadora-solar/">Simular economia</a><a class="haja-btn secondary" style="color:#008481;border-color:#008481" href="$wa">Falar no WhatsApp</a></div></div><img class="haja-photo" src="$job2" alt="Energia solar comercial"></div></section><section class="haja-band alt"><div class="haja-wrap haja-grid"><div class="haja-card"><h3>Menos custo operacional</h3><p>Reduza uma das despesas mais sensíveis do negócio.</p></div><div class="haja-card"><h3>Estudo financeiro</h3><p>Estimativa de economia, retorno e melhor forma de aquisição.</p></div><div class="haja-card"><h3>Segurança técnica</h3><p>Projeto elétrico adequado ao padrão de consumo da empresa.</p></div><div class="haja-card"><h3>Financiamento</h3><p>Opções para parcelar e preservar capital de giro.</p></div></div></section><section class="haja-band"><div class="haja-wrap"><h2>Energia solar empresarial</h2><p>Empresas com consumo recorrente podem transformar energia solar em vantagem competitiva. A Haja dimensiona o sistema com foco em economia real, conformidade técnica e homologação junto à distribuidora.</p><a class="haja-btn primary" href="$wa">Solicitar estudo comercial</a></div></section></main>
HTML, 'Energia solar para empresas com foco em economia, engenharia e retorno financeiro.'],
    ['Energia Solar Rural', 'energia-solar-rural', $landingStyle . <<<HTML
<main class="haja-page"><section class="haja-band"><div class="haja-wrap haja-split"><div><p class="haja-kicker">Energia solar para fazendas e sítios</p><h1>Energia Solar Rural para produzir com mais previsibilidade</h1><p>Sistemas solares para propriedades rurais, bombas, irrigação, granjas, galpões e atividades com alto consumo de energia.</p><div class="haja-actions"><a class="haja-btn primary" href="$site/calculadora-solar/">Simular economia</a><a class="haja-btn secondary" style="color:#008481;border-color:#008481" href="$wa">Falar no WhatsApp</a></div></div><img class="haja-photo" src="$job3" alt="Energia solar rural"></div></section><section class="haja-band alt"><div class="haja-wrap haja-grid"><div class="haja-card"><h3>Alto consumo</h3><p>Projetos para operações em que energia pesa no custo produtivo.</p></div><div class="haja-card"><h3>Confiabilidade</h3><p>Dimensionamento técnico para reduzir riscos e perda de desempenho.</p></div><div class="haja-card"><h3>Homologação</h3><p>Suporte na documentação e relacionamento com a distribuidora.</p></div><div class="haja-card"><h3>Retorno financeiro</h3><p>Estudo para entender economia, investimento e payback estimado.</p></div></div></section><section class="haja-band"><div class="haja-wrap"><h2>Energia solar para fazendas, sítios e propriedades rurais</h2><p>A Haja desenvolve soluções para propriedades rurais que precisam reduzir custos, aumentar previsibilidade e manter a operação produtiva com segurança elétrica.</p><a class="haja-btn primary" href="$wa">Solicitar estudo rural</a></div></section></main>
HTML, 'Energia solar rural para fazendas, sítios e propriedades produtivas.'],
    ['Laudos Técnicos', 'laudos-tecnicos', $landingStyle . <<<HTML
<main class="haja-page"><section class="haja-band"><div class="haja-wrap"><p class="haja-kicker">Engenharia elétrica e conformidade</p><h1>Laudos Técnicos Elétricos, SPDA e NR10</h1><p>Documentação técnica para instalações elétricas, sistemas fotovoltaicos, inspeções, segurança e conformidade.</p><div class="haja-actions"><a class="haja-btn primary" href="$wa">Solicitar laudo técnico</a></div></div></section><section class="haja-band alt"><div class="haja-wrap haja-grid"><div class="haja-card"><h3>Laudo SPDA</h3><p>Avaliação técnica de sistema de proteção contra descargas atmosféricas.</p></div><div class="haja-card"><h3>Laudo NR10</h3><p>Documentação para segurança em instalações e serviços em eletricidade.</p></div><div class="haja-card"><h3>Laudo elétrico</h3><p>Inspeção de instalações, quadros, cargas e condições de segurança.</p></div><div class="haja-card"><h3>Projetos solares</h3><p>Suporte técnico para adequações e regularizações fotovoltaicas.</p></div></div></section><section class="haja-band"><div class="haja-wrap"><h2>Laudo elétrico com responsabilidade técnica</h2><p>A Haja une experiência em energia solar e engenharia elétrica para entregar laudos claros, objetivos e adequados à necessidade do cliente.</p><a class="haja-btn primary" href="$wa">Falar com a engenharia</a></div></section></main>
HTML, 'Laudos técnicos elétricos, SPDA, NR10 e documentação de engenharia.'],
    ['Financiamento Solar', 'financiamento-solar', $landingStyle . <<<HTML
<main class="haja-page"><section class="haja-band"><div class="haja-wrap"><p class="haja-kicker">Energia solar parcelada</p><h1>Financiamento Solar para instalar sem descapitalizar</h1><p>Conheça opções de financiamento para energia solar e transforme a economia da conta de luz em investimento.</p><div class="haja-actions"><a class="haja-btn primary" href="$wa">Simular financiamento</a><a class="haja-btn secondary" style="color:#008481;border-color:#008481" href="$site/calculadora-solar/">Calcular economia</a></div></div></section><section class="haja-band alt"><div class="haja-wrap haja-grid"><div class="haja-card"><h3>Parcelamento</h3><p>Alternativas para pagar o sistema ao longo do tempo.</p></div><div class="haja-card"><h3>Economia mensal</h3><p>A redução da conta ajuda a compensar o investimento.</p></div><div class="haja-card"><h3>Análise de crédito</h3><p>Aprovação sujeita às condições das instituições financeiras.</p></div><div class="haja-card"><h3>Projeto completo</h3><p>Financiamento alinhado ao sistema dimensionado pela engenharia.</p></div></div></section><section class="haja-band"><div class="haja-wrap"><h2>Financiamento energia solar</h2><p>Energia solar parcelada pode ser uma boa alternativa para residências, empresas e propriedades rurais que querem reduzir a conta sem usar todo o capital de uma vez.</p><a class="haja-btn primary" href="$wa">Ver opções de financiamento</a></div></section></main>
HTML, 'Financiamento de energia solar e energia solar parcelada.'],
];

$homeId = upsert_page($mysqli, 'Home', 'home', $home, 'Energia solar com engenharia, economia e homologação completa.');
q($mysqli, "UPDATE wp_options SET option_value='page' WHERE option_name='show_on_front'");
q($mysqli, "UPDATE wp_options SET option_value=? WHERE option_name='page_on_front'", 's', [(string)$homeId]);
q($mysqli, "UPDATE wp_options SET option_value='Haja Geração Solar' WHERE option_name='blogname'");

foreach ($pages as $page) {
    upsert_page($mysqli, $page[0], $page[1], $page[2], $page[3]);
}

$calc = $landingStyle . <<<HTML
<main class="haja-page"><section class="haja-band"><div class="haja-wrap"><p class="haja-kicker">Simulador de economia solar</p><h1>Simule quanto você pode economizar com energia solar</h1><p>Informe os dados da sua conta de energia para receber uma estimativa inicial. Depois, a equipe da Haja faz um diagnóstico gratuito com projeto, homologação e opções de financiamento.</p><div class="haja-simulator">[si_solar_calculator mode="public"]</div></div></section><section class="haja-band alt"><div class="haja-wrap haja-grid"><div class="haja-card"><h3>Residencial</h3><p>Calcule a economia para sua casa.</p></div><div class="haja-card"><h3>Comercial</h3><p>Entenda o impacto no custo fixo da empresa.</p></div><div class="haja-card"><h3>Rural</h3><p>Projete economia para propriedades produtivas.</p></div><div class="haja-card"><h3>WhatsApp</h3><p>Receba orientação da equipe após a simulação.</p></div></div></section></main>
HTML;
upsert_page($mysqli, 'Simulador de Economia Solar', 'calculadora-solar', $calc, 'Simulador de economia com energia solar.');

$homolog = $landingStyle . <<<HTML
<main class="haja-page"><section class="haja-band"><div class="haja-wrap"><p class="haja-kicker">B2B para integradores solares</p><h1>Homologação de Projetos Solares para Integradores em Todo o Brasil</h1><p>A Haja apoia integradores com projeto, documentação técnica e acompanhamento de homologação para sistemas fotovoltaicos residenciais, comerciais e rurais.</p><div class="haja-actions"><a class="haja-btn primary" href="$wa">Falar com a engenharia</a></div></div></section><section class="haja-band alt"><div class="haja-wrap haja-grid"><div class="haja-card"><h3>Distribuidoras</h3><p>Experiência com processos em concessionárias como Light, Enel, Energisa, Neoenergia e CPFL.</p></div><div class="haja-card"><h3>Documentação</h3><p>Organização de memorial, diagramas, formulários e exigências técnicas.</p></div><div class="haja-card"><h3>Correções</h3><p>Suporte para responder pendências e exigências da distribuidora.</p></div><div class="haja-card"><h3>Escala B2B</h3><p>Ideal para integradores que querem vender mais sem travar na homologação.</p></div></div></section><section class="haja-band"><div class="haja-wrap"><h2>Calcule sua homologação</h2><p>Use a calculadora abaixo ou chame a equipe para avaliar o seu projeto.</p>[hp_calculadora_homologacao]</div></section><section class="haja-band haja-final"><div class="haja-wrap"><h2>Projetos complexos precisam de engenharia.</h2><p>Conte com a Haja para resolver a parte técnica enquanto você foca na venda e instalação.</p><a class="haja-btn primary" href="$wa">Solicitar suporte de homologação</a></div></section></main>
HTML;
upsert_page($mysqli, 'Homologação para Integradores', 'homologacao-para-integradores', $homolog, 'Homologação de projetos solares para integradores em todo o Brasil.');

q($mysqli, "UPDATE wp_options SET option_value=REPLACE(option_value, 'Haja Gera‡Æo Solar', 'Haja Geração Solar') WHERE option_name='blogname'");

echo "Marketing refresh applied. Home ID: {$homeId}\n";
