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

$simpleCss = <<<'CSS'
<style>
.haja-simple-services{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:22px}
.haja-simple-service{display:flex;flex-direction:column;align-items:center;text-align:center;min-height:280px;padding:30px 24px;border:1px solid #f4ab17;background:#fff;text-decoration:none;box-shadow:0 10px 24px rgba(9,40,37,.06)}
.haja-simple-service h3{font-size:23px;line-height:1.18;margin:0 0 18px;color:#202020}
.haja-simple-service h3:after{content:"";display:block;width:62px;height:2px;background:#f4ab17;margin:14px auto 0}
.haja-simple-service p{flex:1;margin:0 0 22px;color:#606b69;line-height:1.55}
.haja-simple-service span{display:inline-flex;align-items:center;justify-content:center;min-height:42px;padding:10px 24px;border:2px solid #f4ab17;color:#e98718;font-weight:800;background:#fff}
.haja-simple-service:hover span,.haja-simple-service:focus span{background:#f4ab17;color:#fff}
@media(max-width:1000px){.haja-simple-services{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:640px){.haja-simple-services{grid-template-columns:1fr}.haja-simple-service{min-height:240px}}
</style>
CSS;

$serviceSection = $simpleCss . <<<HTML
<section class="haja-band">
  <div class="haja-wrap">
    <div class="haja-simple-services">
      <a class="haja-simple-service" href="$site/energia-solar-residencial/">
        <h3>Energia Solar Residencial</h3>
        <p>Sistemas para casas e condomínios com economia mensal, valorização do imóvel e acompanhamento completo.</p>
        <span>Saiba Mais</span>
      </a>
      <a class="haja-simple-service" href="$site/energia-solar-comercial/">
        <h3>Energia Solar Comercial</h3>
        <p>Projetos para empresas que querem reduzir custo fixo, proteger margem e previsibilizar a conta de energia.</p>
        <span>Saiba Mais</span>
      </a>
      <a class="haja-simple-service" href="$site/energia-solar-rural/">
        <h3>Energia Solar Rural</h3>
        <p>Soluções para fazendas, sítios e atividades produtivas com alto consumo.</p>
        <span>Saiba Mais</span>
      </a>
      <a class="haja-simple-service" href="$site/homologacao-para-integradores/">
        <h3>Homologação para Integradores</h3>
        <p>Projetos, documentação e suporte técnico para integradores.</p>
        <span>Saiba Mais</span>
      </a>
    </div>
  </div>
</section>
HTML;

$pattern = '#<section class="haja-band"><div class="haja-wrap"><div class="haja-head"><h2>Uma empresa de engenharia solar, não apenas uma instaladora</h2>.*?</div></div></section><section class="haja-band alt">#s';
$replacement = $serviceSection . '<section class="haja-band alt">';
$newContent = preg_replace($pattern, $replacement, $content, 1);

if ($newContent === null || $newContent === $content) {
    throw new RuntimeException('Could not replace services section');
}

$now = date('Y-m-d H:i:s');
$stmt = $mysqli->prepare("UPDATE wp_posts SET post_content=?, post_modified=?, post_modified_gmt=UTC_TIMESTAMP() WHERE ID=?");
$stmt->bind_param('ssi', $newContent, $now, $homeId);
$stmt->execute();
$stmt->close();

echo "Services section simplified.\n";
