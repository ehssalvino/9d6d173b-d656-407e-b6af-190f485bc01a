<?php
require 'D:/xampp/htdocs/hajageracaosolar/wp-load.php';

$hero_img = home_url('/wp-content/uploads/2023/04/solar-panels-and-wind-energy-plants.jpg');
$wa = 'https://api.whatsapp.com/send?phone=5521969022250';

$main = <<<HTML
<main class="haja-page">
<section class="haja-hero" style="background-image:url('{$hero_img}')">
  <div class="haja-wrap">
    <p class="haja-kicker">ENERGIZE SEU FUTURO:</p>
    <h1>SOLU&Ccedil;&Otilde;ES DE ENERGIA SOLAR SUSTENT&Aacute;VEL E EFICIENTE</h1>
    <p>Transforme a luz solar em economia e contribua para um futuro sustent&aacute;vel com nossas solu&ccedil;&otilde;es de energia solar.</p>
    <div class="haja-actions"><a class="haja-btn primary" href="#simulador">Descubra sua Economia!</a></div>
  </div>
</section>
<section class="haja-band haja-overlap">
  <div class="haja-wrap">
    <div class="haja-grid haja-services-links">
      <div class="haja-card"><h3>Energia Solar Residencial</h3><p>Sistema fotovoltaico dimensionado para reduzir a conta de energia da sua casa com seguran&ccedil;a.</p><a class="haja-btn primary" href="/hajageracaosolar/energia-solar-residencial/">Saiba Mais</a></div>
      <div class="haja-card"><h3>Energia Solar Comercial</h3><p>Solu&ccedil;&otilde;es para empresas que buscam previsibilidade, economia e melhor retorno financeiro.</p><a class="haja-btn primary" href="/hajageracaosolar/energia-solar-comercial/">Saiba Mais</a></div>
      <div class="haja-card"><h3>Energia Solar Rural</h3><p>Projetos para propriedades rurais, bombas, irriga&ccedil;&atilde;o e opera&ccedil;&otilde;es com alto consumo.</p><a class="haja-btn primary" href="/hajageracaosolar/energia-solar-rural/">Saiba Mais</a></div>
      <div class="haja-card"><h3>Homologa&ccedil;&atilde;o</h3><p>Documenta&ccedil;&atilde;o e acompanhamento t&eacute;cnico junto &agrave; concession&aacute;ria de energia.</p><a class="haja-btn primary" href="/hajageracaosolar/homologacao-para-integradores/">Saiba Mais</a></div>
    </div>
  </div>
</section>
<section class="haja-band alt">
  <div class="haja-wrap haja-split">
    <div>
      <div class="haja-head"><h2>Energia solar com engenharia e resultado</h2><p>Projeto, instala&ccedil;&atilde;o, homologa&ccedil;&atilde;o e monitoramento completo para resid&ecirc;ncias, empresas e propriedades rurais.</p></div>
      <div class="haja-grid haja-stats">
        <div class="haja-card haja-stat"><strong>95%</strong><span>de economia poss&iacute;vel na conta de energia</span></div>
        <div class="haja-card haja-stat"><strong>25+</strong><span>anos de vida &uacute;til do sistema solar</span></div>
      </div>
    </div>
    <img class="haja-photo" src="/hajageracaosolar/wp-content/uploads/2023/04/engineer-installing-solar-panel.jpg" alt="Instala&ccedil;&atilde;o de energia solar">
  </div>
</section>
<section id="simulador" class="haja-band">
  <div class="haja-wrap haja-simulator">
    <h2>Calcule sua economia</h2>
    <p>Envie sua conta de energia para receber um estudo personalizado da Haja Gera&ccedil;&atilde;o Solar.</p>
    <div class="haja-actions"><a class="haja-btn primary" href="{$wa}">Chamar no WhatsApp</a><a class="haja-btn secondary" href="/hajageracaosolar/calculadora-solar/">Abrir calculadora</a></div>
  </div>
</section>
<section class="haja-band haja-final">
  <div class="haja-wrap">
    <h2>Descubra quanto voc&ecirc; pode economizar ainda este m&ecirc;s.</h2>
    <p>Receba um estudo gratuito para sua casa, empresa ou propriedade rural.</p>
    <a class="haja-btn primary" href="{$wa}">Solicitar Estudo Gratuito</a>
  </div>
</section>
</main>
HTML;

update_option('haja_home_static_main_html', $main, false);
wp_update_post(array('ID' => 3551, 'post_content' => '[haja_home_static]'));
update_post_meta(3551, '_wp_page_template', 'default');
if (function_exists('wp_cache_flush')) { wp_cache_flush(); }

echo 'main_len=' . strlen($main) . PHP_EOL;
echo 'out_len=' . strlen(do_shortcode('[haja_home_static]')) . PHP_EOL;