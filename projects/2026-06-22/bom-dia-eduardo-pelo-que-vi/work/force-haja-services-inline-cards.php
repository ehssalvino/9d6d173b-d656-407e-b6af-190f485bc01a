<?php
$mysqli = new mysqli('127.0.0.1', 'root', '', 'wordpress_local');
if ($mysqli->connect_errno) {
    fwrite(STDERR, $mysqli->connect_error . PHP_EOL);
    exit(1);
}
$mysqli->set_charset('utf8mb4');

$site = 'http://localhost/hajageracaosolar';

$stmt = $mysqli->prepare("SELECT ID, post_content FROM wp_posts WHERE post_type='page' AND post_name='home' LIMIT 1");
$stmt->execute();
$stmt->bind_result($homeId, $content);
if (!$stmt->fetch()) {
    throw new RuntimeException('Home not found');
}
$stmt->close();

$cardBase = 'display:flex !important;flex-direction:column !important;align-items:center !important;justify-content:flex-start !important;text-align:center !important;min-height:300px !important;height:100% !important;padding:30px 22px 26px !important;border:1px solid #f4ab17 !important;background:#fff !important;text-decoration:none !important;box-shadow:0 10px 24px rgba(9,40,37,.06) !important;color:#202020 !important;';
$titleStyle = 'font-size:22px !important;line-height:1.2 !important;margin:0 0 18px !important;color:#202020 !important;font-weight:700 !important;';
$line = '<span style="display:block;width:62px;height:2px;background:#f4ab17;margin:14px auto 0;"></span>';
$textStyle = 'flex:1 !important;margin:0 0 22px !important;color:#606b69 !important;line-height:1.55 !important;font-size:15px !important;';
$buttonStyle = 'display:inline-flex !important;align-items:center !important;justify-content:center !important;min-height:42px !important;padding:10px 24px !important;border:2px solid #f4ab17 !important;color:#e98718 !important;font-weight:800 !important;background:#fff !important;margin-top:auto !important;';

$services = <<<HTML
<section class="haja-band" id="haja-servicos-principais">
  <div class="haja-wrap">
    <div style="display:grid !important;grid-template-columns:repeat(4,minmax(0,1fr)) !important;gap:22px !important;align-items:stretch !important;width:100% !important;">
      <a href="$site/energia-solar-residencial/" style="$cardBase">
        <h3 style="$titleStyle">Energia Solar Residencial$line</h3>
        <p style="$textStyle">Sistemas para casas e condomínios com economia mensal, valorização do imóvel e acompanhamento completo.</p>
        <span style="$buttonStyle">Saiba Mais</span>
      </a>
      <a href="$site/energia-solar-comercial/" style="$cardBase">
        <h3 style="$titleStyle">Energia Solar Comercial$line</h3>
        <p style="$textStyle">Projetos para empresas que querem reduzir custo fixo, proteger margem e previsibilizar a conta de energia.</p>
        <span style="$buttonStyle">Saiba Mais</span>
      </a>
      <a href="$site/energia-solar-rural/" style="$cardBase">
        <h3 style="$titleStyle">Energia Solar Rural$line</h3>
        <p style="$textStyle">Soluções para fazendas, sítios e atividades produtivas com alto consumo.</p>
        <span style="$buttonStyle">Saiba Mais</span>
      </a>
      <a href="$site/homologacao-para-integradores/" style="$cardBase">
        <h3 style="$titleStyle">Homologação$line</h3>
        <p style="$textStyle">Projetos, documentação e suporte técnico para integradores.</p>
        <span style="$buttonStyle">Saiba Mais</span>
      </a>
    </div>
  </div>
</section>
<style>
@media(max-width:1000px){#haja-servicos-principais .haja-wrap>div{grid-template-columns:repeat(2,minmax(0,1fr)) !important}}
@media(max-width:640px){#haja-servicos-principais .haja-wrap>div{grid-template-columns:1fr !important}}
</style>
HTML;

$pattern = '#<style>\s*\.haja-simple-services.*?</style>\s*<section class="haja-band">\s*<div class="haja-wrap">\s*<div class="haja-simple-services">.*?</div>\s*</div>\s*</section>#s';
$newContent = preg_replace($pattern, $services, $content, 1);

if ($newContent === null || $newContent === $content) {
    $pattern = '#<section class="haja-band" id="haja-servicos-principais">.*?</section>\s*<style>.*?</style>#s';
    $newContent = preg_replace($pattern, $services, $content, 1);
}

if ($newContent === null || $newContent === $content) {
    throw new RuntimeException('Could not replace services block');
}

$now = date('Y-m-d H:i:s');
$stmt = $mysqli->prepare("UPDATE wp_posts SET post_content=?, post_modified=?, post_modified_gmt=UTC_TIMESTAMP() WHERE ID=?");
$stmt->bind_param('ssi', $newContent, $now, $homeId);
$stmt->execute();
$stmt->close();

echo "Inline services cards forced.\n";
