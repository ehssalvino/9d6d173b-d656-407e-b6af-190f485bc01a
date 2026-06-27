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

$cardStyle = 'display:flex !important;flex-direction:column !important;align-items:center !important;text-align:center !important;min-height:510px !important;height:100% !important;padding:28px 18px 42px !important;border:1px solid #f4ab17 !important;background:#fff !important;text-decoration:none !important;color:#202020 !important;box-shadow:none !important;';
$iconBox = 'display:flex !important;align-items:center !important;justify-content:center !important;width:86px !important;height:86px !important;margin:0 auto 18px !important;background:#f4ab17 !important;color:#fff !important;';
$svgStyle = 'width:46px;height:46px;stroke:currentColor;fill:none;stroke-width:2.25;stroke-linecap:round;stroke-linejoin:round;';
$titleStyle = 'font-size:21px !important;line-height:1.18 !important;margin:0 0 12px !important;color:#202020 !important;font-weight:800 !important;';
$rule = '<span style="display:block;width:58px;height:2px;background:#f4ab17;margin:0 auto 18px;"></span>';
$textStyle = 'font-size:14px !important;line-height:1.55 !important;margin:0 !important;color:#5f6665 !important;max-width:185px !important;';
$buttonStyle = 'display:inline-flex !important;align-items:center !important;justify-content:center !important;min-height:40px !important;padding:9px 22px !important;border:1px solid #f4ab17 !important;color:#e98718 !important;background:#fff !important;font-weight:800 !important;margin-top:auto !important;';

$icons = [
    'home' => '<svg viewBox="0 0 24 24" style="' . $svgStyle . '"><path d="M3 11.5 12 4l9 7.5"/><path d="M5.5 10.5V20h13v-9.5"/><path d="M9.5 20v-5.5h5V20"/></svg>',
    'commercial' => '<svg viewBox="0 0 24 24" style="' . $svgStyle . '"><path d="M4 21h16"/><path d="M6 21V6h12v15"/><path d="M9 9h2M13 9h2M9 13h2M13 13h2"/><path d="M10 21v-4h4v4"/></svg>',
    'rural' => '<svg viewBox="0 0 24 24" style="' . $svgStyle . '"><path d="M12 21V10"/><path d="M12 10C8 10 5.2 7.7 4 3.8 8.2 4 11.1 6.1 12 10Z"/><path d="M12 14c4.2-.1 7-2.4 8-6.2-4.1.2-7 2.2-8 6.2Z"/></svg>',
    'doc' => '<svg viewBox="0 0 24 24" style="' . $svgStyle . '"><path d="M7 3h7l4 4v14H7z"/><path d="M14 3v5h5"/><path d="m9.5 14 2 2 4-5"/></svg>',
];

$services = <<<HTML
<section class="haja-band" id="haja-servicos-principais">
  <div class="haja-wrap">
    <div style="display:grid !important;grid-template-columns:repeat(4,minmax(0,1fr)) !important;gap:18px !important;align-items:stretch !important;width:100% !important;">
      <a href="$site/energia-solar-residencial/" style="$cardStyle">
        <span aria-hidden="true" style="$iconBox">{$icons['home']}</span>
        <h3 style="$titleStyle">Energia Solar<br>Residencial</h3>
        $rule
        <p style="$textStyle">Sistemas para casas e condomínios com economia mensal, valorização do imóvel e acompanhamento completo.</p>
        <span style="$buttonStyle">Saiba Mais</span>
      </a>
      <a href="$site/energia-solar-comercial/" style="$cardStyle">
        <span aria-hidden="true" style="$iconBox">{$icons['commercial']}</span>
        <h3 style="$titleStyle">Energia Solar<br>Comercial</h3>
        $rule
        <p style="$textStyle">Projetos para empresas que querem reduzir custo fixo, proteger margem e previsibilizar a conta de energia.</p>
        <span style="$buttonStyle">Saiba Mais</span>
      </a>
      <a href="$site/energia-solar-rural/" style="$cardStyle">
        <span aria-hidden="true" style="$iconBox">{$icons['rural']}</span>
        <h3 style="$titleStyle">Energia Solar Rural</h3>
        $rule
        <p style="$textStyle">Soluções para fazendas, sítios e atividades produtivas com alto consumo.</p>
        <span style="$buttonStyle">Saiba Mais</span>
      </a>
      <a href="$site/homologacao-para-integradores/" style="$cardStyle">
        <span aria-hidden="true" style="$iconBox">{$icons['doc']}</span>
        <h3 style="$titleStyle">Homologação</h3>
        $rule
        <p style="$textStyle">Projetos, documentação e suporte técnico para integradores.</p>
        <span style="$buttonStyle">Saiba Mais</span>
      </a>
    </div>
  </div>
</section>
<style>
@media(max-width:1000px){#haja-servicos-principais .haja-wrap>div{grid-template-columns:repeat(2,minmax(0,1fr)) !important}}
@media(max-width:640px){#haja-servicos-principais .haja-wrap>div{grid-template-columns:1fr !important}#haja-servicos-principais a{min-height:390px !important}}
</style>
HTML;

$pattern = '#<section class="haja-band" id="haja-servicos-principais">.*?</section>\s*<style>.*?</style>#s';
$newContent = preg_replace($pattern, $services, $content, 1);

if ($newContent === null || $newContent === $content) {
    throw new RuntimeException('Could not replace services block');
}

$now = date('Y-m-d H:i:s');
$stmt = $mysqli->prepare("UPDATE wp_posts SET post_content=?, post_modified=?, post_modified_gmt=UTC_TIMESTAMP() WHERE ID=?");
$stmt->bind_param('ssi', $newContent, $now, $homeId);
$stmt->execute();
$stmt->close();

echo "Services cards updated with icons inside.\n";
