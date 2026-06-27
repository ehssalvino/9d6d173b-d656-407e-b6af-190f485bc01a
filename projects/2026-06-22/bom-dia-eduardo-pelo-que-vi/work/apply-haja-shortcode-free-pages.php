<?php
$mysqli = new mysqli('127.0.0.1', 'root', '', 'wordpress_local');
if ($mysqli->connect_errno) {
    fwrite(STDERR, $mysqli->connect_error . PHP_EOL);
    exit(1);
}
$mysqli->set_charset('utf8mb4');

$site = 'http://localhost/hajageracaosolar';
$waPhone = '5521969022250';
$wa = 'https://api.whatsapp.com/send?phone=' . $waPhone;
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

function update_page(mysqli $db, string $slug, string $title, string $content, string $excerpt): void {
    $stmt = $db->prepare("SELECT ID FROM wp_posts WHERE post_name=? AND post_type='page' LIMIT 1");
    $stmt->bind_param('s', $slug);
    $stmt->execute();
    $stmt->bind_result($id);
    $found = $stmt->fetch();
    $stmt->close();
    if (!$found) {
        throw new RuntimeException("Page not found: {$slug}");
    }
    $now = date('Y-m-d H:i:s');
    q($db, "UPDATE wp_posts SET post_title=?, post_content=?, post_excerpt=?, post_modified=?, post_modified_gmt=UTC_TIMESTAMP() WHERE ID=?", 'ssssi', [$title, $content, $excerpt, $now, $id]);
    q($db, "DELETE FROM wp_postmeta WHERE post_id=? AND meta_key IN ('_elementor_data','_elementor_edit_mode','_elementor_css','_elementor_controls_usage')", 'i', [$id]);
}

function style_block(): string {
    return <<<'HTML'
<style>
.haja-page{font-family:Montserrat,Arial,sans-serif;color:#16302d;background:#fff}.haja-page *{box-sizing:border-box}.haja-wrap{max-width:1160px;margin:0 auto;padding:0 22px}.haja-hero{position:relative;min-height:680px;display:flex;align-items:center;color:#fff;background-size:cover;background-position:center}.haja-hero:before{content:"";position:absolute;inset:0;background:linear-gradient(90deg,rgba(8,34,32,.86),rgba(8,34,32,.54),rgba(8,34,32,.24))}.haja-hero .haja-wrap{position:relative}.haja-kicker{font-size:13px;text-transform:uppercase;letter-spacing:.08em;font-weight:800;color:#ffc44d;margin:0 0 14px}.haja-hero h1,.haja-page h1{max-width:880px;font-size:clamp(36px,5vw,68px);line-height:1.02;margin:0 0 22px;font-weight:900;letter-spacing:0}.haja-hero p,.haja-page p{line-height:1.6}.haja-hero p{max-width:720px;font-size:20px;margin:0 0 28px;color:#eef7f3}.haja-actions{display:flex;gap:14px;flex-wrap:wrap}.haja-btn{display:inline-flex;align-items:center;justify-content:center;min-height:48px;padding:14px 22px;border-radius:6px;text-decoration:none;font-weight:800;border:2px solid transparent}.haja-btn.primary{background:#f4ab17;color:#102926}.haja-btn.secondary{background:rgba(255,255,255,.08);border-color:#fff;color:#fff}.haja-band{padding:72px 0}.haja-band.alt{background:#f6faf8}.haja-head{max-width:760px;margin:0 0 34px}.haja-head h2,.haja-page h2{font-size:clamp(28px,3vw,42px);line-height:1.12;margin:0 0 12px;color:#008481}.haja-head p{font-size:18px;color:#52615f;margin:0}.haja-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:18px}.haja-card{background:#fff;border:1px solid #dfe9e6;border-radius:8px;padding:24px;box-shadow:0 10px 26px rgba(9,40,37,.06);text-decoration:none}.haja-card h3{font-size:21px;margin:0 0 10px;color:#16302d}.haja-card p,.haja-card li{color:#52615f;line-height:1.55}.haja-stat strong{display:block;font-size:34px;color:#f4ab17;line-height:1}.haja-stat span{display:block;margin-top:8px;font-weight:700;color:#16302d}.haja-steps{counter-reset:step;display:grid;grid-template-columns:repeat(5,1fr);gap:14px}.haja-step{background:#fff;border-left:4px solid #f4ab17;padding:20px;border-radius:8px}.haja-step:before{counter-increment:step;content:counter(step);display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:50%;background:#008481;color:#fff;font-weight:900;margin-bottom:14px}.haja-case{overflow:hidden;padding:0}.haja-case img{width:100%;height:210px;object-fit:cover;display:block}.haja-case div{padding:22px}.haja-simulator{background:#08312e;color:#fff;border-radius:8px;padding:32px}.haja-simulator h2{color:#fff;margin-top:0}.haja-simulator p{color:#d8ebe6}.haja-form{display:grid;gap:14px}.haja-form label{display:grid;gap:6px;font-weight:800;color:#fff}.haja-form input,.haja-form select{width:100%;min-height:46px;border:1px solid #bdd3ce;border-radius:6px;padding:10px 12px;font-size:16px}.haja-result{display:none;background:#fff;color:#16302d;border-radius:8px;padding:18px;margin-top:16px}.haja-result strong{color:#008481;font-size:26px}.haja-final{background:#008481;color:#fff;text-align:center}.haja-final h2{color:#fff;font-size:clamp(30px,4vw,50px);margin:0 0 16px}.haja-final p{max-width:760px;margin:0 auto 26px;font-size:19px;color:#e8fffb}.haja-split{display:grid;grid-template-columns:1.1fr .9fr;gap:32px;align-items:center}.haja-photo{width:100%;border-radius:8px;display:block}@media(max-width:900px){.haja-grid,.haja-steps,.haja-split{grid-template-columns:1fr 1fr}.haja-hero{min-height:620px}}@media(max-width:640px){.haja-grid,.haja-steps,.haja-split{grid-template-columns:1fr}.haja-hero h1,.haja-page h1{font-size:36px}.haja-band{padding:54px 0}}
</style>
HTML;
}

function economy_calculator(string $waPhone): string {
    return <<<HTML
<div class="haja-simulator">
  <h2>Simule sua economia solar</h2>
  <p>Preencha os dados abaixo para estimar a economia anual e iniciar um diagnóstico gratuito pelo WhatsApp.</p>
  <form class="haja-form" data-haja-economy>
    <label>Valor médio da conta de energia
      <input type="number" min="100" step="10" name="bill" placeholder="Ex.: 1200" required>
    </label>
    <label>Cidade
      <input type="text" name="city" placeholder="Ex.: Niterói" required>
    </label>
    <label>Tipo de imóvel
      <select name="kind"><option>Residencial</option><option>Comercial</option><option>Rural</option></select>
    </label>
    <button class="haja-btn primary" type="submit">Calcular economia</button>
  </form>
  <div class="haja-result" data-haja-result></div>
</div>
<script>
document.addEventListener('DOMContentLoaded',function(){document.querySelectorAll('[data-haja-economy]').forEach(function(form){form.addEventListener('submit',function(event){event.preventDefault();var bill=Number(form.bill.value||0);var city=form.city.value||'';var kind=form.kind.value||'';var monthly=bill*.9;var annual=monthly*12;var box=form.parentElement.querySelector('[data-haja-result]');var money=annual.toLocaleString('pt-BR',{style:'currency',currency:'BRL'});var text='Olá, quero um estudo solar para '+kind+' em '+city+'. Minha conta média é R$ '+bill.toFixed(2).replace('.',',')+' e a simulação indicou economia anual de '+money+'.';var link='https://api.whatsapp.com/send?phone=$waPhone&text='+encodeURIComponent(text);box.style.display='block';box.innerHTML='<p>Sua economia estimada pode chegar a</p><strong>'+money+' por ano</strong><p>Esta é uma estimativa inicial considerando redução de até 90% da conta. O estudo técnico confirma potência, investimento e payback.</p><a class="haja-btn primary" href="'+link+'">Receber diagnóstico no WhatsApp</a>';});});});
</script>
HTML;
}

function homologation_calculator(string $waPhone): string {
    return <<<HTML
<div class="haja-simulator">
  <h2>Calcule uma estimativa de homologação</h2>
  <p>Informe a potência aproximada do projeto para iniciar o atendimento com a engenharia.</p>
  <form class="haja-form" data-haja-homolog>
    <label>Potência do sistema (kWp)
      <input type="number" min="1" step="0.1" name="kwp" placeholder="Ex.: 12.5" required>
    </label>
    <label>Distribuidora
      <select name="utility"><option>Light</option><option>Enel</option><option>Energisa</option><option>Neoenergia</option><option>CPFL</option><option>Outra</option></select>
    </label>
    <button class="haja-btn primary" type="submit">Iniciar orçamento</button>
  </form>
  <div class="haja-result" data-haja-result></div>
</div>
<script>
document.addEventListener('DOMContentLoaded',function(){document.querySelectorAll('[data-haja-homolog]').forEach(function(form){form.addEventListener('submit',function(event){event.preventDefault();var kwp=Number(form.kwp.value||0);var utility=form.utility.value||'';var box=form.parentElement.querySelector('[data-haja-result]');var text='Olá, preciso de homologação para um projeto solar de '+kwp+' kWp na distribuidora '+utility+'.';var link='https://api.whatsapp.com/send?phone=$waPhone&text='+encodeURIComponent(text);box.style.display='block';box.innerHTML='<p>Projeto informado:</p><strong>'+kwp.toLocaleString('pt-BR')+' kWp</strong><p>A equipe técnica confirma escopo, documentos e prazo conforme a distribuidora.</p><a class="haja-btn primary" href="'+link+'">Enviar para a engenharia</a>';});});});
</script>
HTML;
}

$style = style_block();
$calc = economy_calculator($waPhone);
$homologCalc = homologation_calculator($waPhone);
$home = $style . <<<HTML
<main class="haja-page"><section class="haja-hero" style="background-image:url('$hero')"><div class="haja-wrap"><p class="haja-kicker">Energia solar com engenharia e resultado</p><h1>Economize até 95% na sua conta de energia com um sistema solar projetado por engenheiros especializados.</h1><p>Projeto, instalação, homologação e monitoramento completo para residências, empresas e propriedades rurais.</p><div class="haja-actions"><a class="haja-btn primary" href="$site/calculadora-solar/">Simular Minha Economia</a><a class="haja-btn secondary" href="$wa">Falar no WhatsApp</a></div></div></section><section class="haja-band alt"><div class="haja-wrap haja-grid"><div class="haja-card haja-stat"><strong>Até 95%</strong><span>de redução na conta de luz</span></div><div class="haja-card haja-stat"><strong>Projeto</strong><span>dimensionado por engenharia</span></div><div class="haja-card haja-stat"><strong>Completo</strong><span>instalação, homologação e monitoramento</span></div><div class="haja-card haja-stat"><strong>B2B</strong><span>homologação para integradores em todo o Brasil</span></div></div></section><section class="haja-band"><div class="haja-wrap"><div class="haja-head"><h2>Uma empresa de engenharia solar, não apenas uma instaladora</h2><p>A Haja desenvolve sistemas fotovoltaicos com análise técnica, segurança elétrica e foco em retorno financeiro.</p></div><div class="haja-grid"><a class="haja-card" href="$site/energia-solar-residencial/"><h3>Energia Solar Residencial</h3><p>Sistemas para casas e condomínios com economia mensal, valorização do imóvel e acompanhamento completo.</p></a><a class="haja-card" href="$site/energia-solar-comercial/"><h3>Energia Solar Comercial</h3><p>Projetos para empresas que querem reduzir custo fixo, proteger margem e previsibilizar a conta de energia.</p></a><a class="haja-card" href="$site/energia-solar-rural/"><h3>Energia Solar Rural</h3><p>Soluções para fazendas, sítios e atividades produtivas com alto consumo.</p></a><a class="haja-card" href="$site/homologacao-para-integradores/"><h3>Homologação para Integradores</h3><p>Projetos, documentação e suporte técnico para integradores.</p></a></div></div></section><section class="haja-band alt"><div class="haja-wrap"><div class="haja-head"><h2>Como funciona</h2><p>Um processo direto para sair da conta alta para a geração própria.</p></div><div class="haja-steps"><div class="haja-step"><h3>Análise da conta</h3><p>Consumo, tarifa, padrão de uso e objetivo financeiro.</p></div><div class="haja-step"><h3>Projeto personalizado</h3><p>Dimensionamento para telhado, carga e distribuidora.</p></div><div class="haja-step"><h3>Instalação</h3><p>Execução com segurança e padrão técnico.</p></div><div class="haja-step"><h3>Homologação</h3><p>Aprovação junto à concessionária.</p></div><div class="haja-step"><h3>Economia</h3><p>Acompanhamento da geração mês a mês.</p></div></div></div></section><section class="haja-band"><div class="haja-wrap haja-split">$calc<div><div class="haja-head"><h2>O que você recebe no estudo</h2><p>Estimativa de economia anual, potência recomendada, caminho de homologação, possibilidade de financiamento e próximos passos para instalar com segurança.</p></div><a class="haja-btn primary" href="$wa">Solicitar Estudo Gratuito</a></div></div></section><section class="haja-band alt"><div class="haja-wrap"><div class="haja-head"><h2>Projetos reais, engenharia visível</h2><p>Use esta seção para inserir resultados finais: conta anterior, conta atual e economia obtida. As imagens já mostram obras e instalações da Haja.</p></div><div class="haja-grid"><div class="haja-card haja-case"><img src="$job1" alt="Instalação solar residencial"><div><h3>Residencial</h3><p>Projeto para reduzir a conta mensal e aumentar previsibilidade.</p></div></div><div class="haja-card haja-case"><img src="$job2" alt="Instalação de inversor solar"><div><h3>Comercial</h3><p>Dimensionamento para empresas que precisam proteger margem.</p></div></div><div class="haja-card haja-case"><img src="$job3" alt="Equipe em instalação solar"><div><h3>Projetos complexos</h3><p>Homologação, laudos e suporte técnico com engenharia.</p></div></div><div class="haja-card"><h3>Prova social</h3><p>Próximo passo: adicionar vídeos, prints de WhatsApp e avaliações do Google.</p></div></div></div></section><section class="haja-band"><div class="haja-wrap"><div class="haja-head"><h2>Por que escolher a Haja</h2><p>Energia solar é investimento. A decisão precisa ser técnica, financeira e segura.</p></div><div class="haja-grid"><div class="haja-card"><h3>Engenharia especializada</h3><p>Projetos dimensionados por profissionais que entendem de elétrica, homologação e retorno.</p></div><div class="haja-card"><h3>Segurança elétrica</h3><p>Atenção a normas, componentes, proteção e qualidade de execução.</p></div><div class="haja-card"><h3>Homologação completa</h3><p>Acompanhamento da documentação junto à concessionária.</p></div><div class="haja-card"><h3>Financiamento solar</h3><p>Opções para parcelar o sistema e trocar a conta de luz por investimento.</p></div></div></div></section><section class="haja-band haja-final"><div class="haja-wrap"><h2>Descubra quanto você pode economizar ainda este mês.</h2><p>Envie sua conta de energia e receba um estudo gratuito para sua casa, empresa ou propriedade rural.</p><a class="haja-btn primary" href="$wa">Solicitar Estudo Gratuito</a></div></section></main>
HTML;

update_page($mysqli, 'home', 'Home', $home, 'Energia solar com engenharia, economia e homologação completa.');
update_page($mysqli, 'calculadora-solar', 'Simulador de Economia Solar', $style . '<main class="haja-page"><section class="haja-band"><div class="haja-wrap"><p class="haja-kicker">Simulador de economia solar</p><h1>Simule quanto você pode economizar com energia solar</h1><p>Informe os dados da sua conta de energia para receber uma estimativa inicial. Depois, a Haja faz um diagnóstico gratuito com projeto, homologação e opções de financiamento.</p>' . $calc . '</div></section><section class="haja-band alt"><div class="haja-wrap haja-grid"><div class="haja-card"><h3>Residencial</h3><p>Calcule a economia para sua casa.</p></div><div class="haja-card"><h3>Comercial</h3><p>Entenda o impacto no custo fixo da empresa.</p></div><div class="haja-card"><h3>Rural</h3><p>Projete economia para propriedades produtivas.</p></div><div class="haja-card"><h3>WhatsApp</h3><p>Receba orientação da equipe após a simulação.</p></div></div></section></main>', 'Simulador de economia com energia solar.');
update_page($mysqli, 'homologacao-para-integradores', 'Homologação para Integradores', $style . '<main class="haja-page"><section class="haja-band"><div class="haja-wrap"><p class="haja-kicker">B2B para integradores solares</p><h1>Homologação de Projetos Solares para Integradores em Todo o Brasil</h1><p>A Haja apoia integradores com projeto, documentação técnica e acompanhamento de homologação para sistemas fotovoltaicos residenciais, comerciais e rurais.</p><div class="haja-actions"><a class="haja-btn primary" href="' . $wa . '">Falar com a engenharia</a></div></div></section><section class="haja-band alt"><div class="haja-wrap haja-grid"><div class="haja-card"><h3>Distribuidoras</h3><p>Experiência com processos em concessionárias como Light, Enel, Energisa, Neoenergia e CPFL.</p></div><div class="haja-card"><h3>Documentação</h3><p>Organização de memorial, diagramas, formulários e exigências técnicas.</p></div><div class="haja-card"><h3>Correções</h3><p>Suporte para responder pendências e exigências da distribuidora.</p></div><div class="haja-card"><h3>Escala B2B</h3><p>Ideal para integradores que querem vender mais sem travar na homologação.</p></div></div></section><section class="haja-band"><div class="haja-wrap">' . $homologCalc . '</div></section><section class="haja-band haja-final"><div class="haja-wrap"><h2>Projetos complexos precisam de engenharia.</h2><p>Conte com a Haja para resolver a parte técnica enquanto você foca na venda e instalação.</p><a class="haja-btn primary" href="' . $wa . '">Solicitar suporte de homologação</a></div></section></main>', 'Homologação de projetos solares para integradores em todo o Brasil.');
echo "Shortcode-free public pages applied.\n";
