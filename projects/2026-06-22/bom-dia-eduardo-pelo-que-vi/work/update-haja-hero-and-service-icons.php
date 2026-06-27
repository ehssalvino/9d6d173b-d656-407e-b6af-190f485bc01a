<?php
$mysqli = new mysqli('127.0.0.1', 'root', '', 'wordpress_local');
if ($mysqli->connect_errno) {
    fwrite(STDERR, $mysqli->connect_error . PHP_EOL);
    exit(1);
}
$mysqli->set_charset('utf8mb4');

$site = 'http://localhost/hajageracaosolar';
$newHero = $site . '/wp-content/uploads/2024/07/mou-brazil-solar-1.webp';
$oldHeroes = [
    $site . '/wp-content/uploads/2023/06/energia-solar-banner-final-2.jpg',
    $site . '/wp-content/uploads/2024/07/Instalacao-Modulos-3.jpg',
    'https://hajageracaosolar.com.br/wp-content/uploads/2024/07/mou-brazil-solar-1.webp',
];

$stmt = $mysqli->prepare("SELECT ID, post_content FROM wp_posts WHERE post_type='page' AND post_name='home' LIMIT 1");
$stmt->execute();
$stmt->bind_result($homeId, $content);
if (!$stmt->fetch()) {
    throw new RuntimeException('Home not found');
}
$stmt->close();

foreach ($oldHeroes as $oldHero) {
    $content = str_replace($oldHero, $newHero, $content);
}

$serviceCss = <<<'CSS'
<style>
.haja-services-grid{align-items:stretch;padding-top:34px}
.haja-service-card{position:relative;text-align:center;border:1px solid #f4ab17;border-radius:0;padding:74px 28px 30px;min-height:270px;background:linear-gradient(rgba(255,255,255,.86),rgba(255,255,255,.86)),url('http://localhost/hajageracaosolar/wp-content/uploads/2024/07/mou-brazil-solar-1.webp');background-size:cover;background-position:center;box-shadow:none}
.haja-service-card .haja-service-icon{position:absolute;top:-49px;left:50%;transform:translateX(-50%);width:100px;height:100px;background:#ffa154;display:flex;align-items:center;justify-content:center}
.haja-service-card .haja-service-icon svg{width:48px;height:48px;stroke:#fff;fill:none;stroke-width:2.4;stroke-linecap:round;stroke-linejoin:round}
.haja-service-card h3{font-size:24px;line-height:1.16;margin-bottom:28px;color:#202020}
.haja-service-card h3:after{content:"";display:block;width:66px;height:2px;background:#ffa154;margin:14px auto 0}
.haja-service-card p{font-size:16px;color:#666;min-height:76px}
.haja-service-card .haja-card-link{display:inline-flex;align-items:center;justify-content:center;border:2px solid #ff8f36;color:#ff8f36;text-decoration:none;min-height:44px;padding:10px 24px;font-weight:700;margin-top:10px;background:rgba(255,255,255,.72)}
.haja-service-card .haja-card-link:hover,.haja-service-card .haja-card-link:focus{background:#ff8f36;color:#fff}
@media(max-width:900px){.haja-services-grid{gap:72px}.haja-service-card{margin-top:30px}}
</style>
CSS;

if (strpos($content, '.haja-service-card') === false) {
    $content = str_replace('</style><main class="haja-page">', '</style>' . $serviceCss . '<main class="haja-page">', $content);
}

$house = '<span class="haja-service-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M3 11.5 12 4l9 7.5"/><path d="M5 10.5V20h14v-9.5"/><path d="M9 20v-6h6v6"/></svg></span>';
$shop = '<span class="haja-service-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 21h16"/><path d="M6 21V7h12v14"/><path d="M8 10h2M14 10h2M8 14h2M14 14h2"/><path d="M10 21v-4h4v4"/></svg></span>';
$rural = '<span class="haja-service-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 21V9"/><path d="M12 9c-4.5 0-7-2.5-8-6 4.6.2 7.3 2.4 8 6Z"/><path d="M12 13c4.8-.2 7.2-2.8 8-6-4.7.2-7.1 2.5-8 6Z"/></svg></span>';
$check = '<span class="haja-service-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M7 3h7l4 4v14H7z"/><path d="M14 3v5h5"/><path d="m9 14 2 2 4-5"/></svg></span>';

$replacements = [
    '<a class="haja-card" href="' . $site . '/energia-solar-residencial/"><h3>Energia Solar Residencial</h3><p>Sistemas para casas e condomínios com economia mensal, valorização do imóvel e acompanhamento completo.</p></a>' =>
        '<a class="haja-card haja-service-card" href="' . $site . '/energia-solar-residencial/">' . $house . '<h3>Energia Solar Residencial</h3><p>Sistemas para casas e condomínios com economia mensal, valorização do imóvel e acompanhamento completo.</p><span class="haja-card-link">Saiba Mais</span></a>',
    '<a class="haja-card" href="' . $site . '/energia-solar-comercial/"><h3>Energia Solar Comercial</h3><p>Projetos para empresas que querem reduzir custo fixo, proteger margem e previsibilizar a conta de energia.</p></a>' =>
        '<a class="haja-card haja-service-card" href="' . $site . '/energia-solar-comercial/">' . $shop . '<h3>Energia Solar Comercial</h3><p>Projetos para empresas que querem reduzir custo fixo, proteger margem e previsibilizar a conta de energia.</p><span class="haja-card-link">Saiba Mais</span></a>',
    '<a class="haja-card" href="' . $site . '/energia-solar-rural/"><h3>Energia Solar Rural</h3><p>Soluções para fazendas, sítios e atividades produtivas com alto consumo.</p></a>' =>
        '<a class="haja-card haja-service-card" href="' . $site . '/energia-solar-rural/">' . $rural . '<h3>Energia Solar Rural</h3><p>Soluções para fazendas, sítios e atividades produtivas com alto consumo.</p><span class="haja-card-link">Saiba Mais</span></a>',
    '<a class="haja-card" href="' . $site . '/homologacao-para-integradores/"><h3>Homologação para Integradores</h3><p>Projetos, documentação e suporte técnico para integradores.</p></a>' =>
        '<a class="haja-card haja-service-card" href="' . $site . '/homologacao-para-integradores/">' . $check . '<h3>Homologação para Integradores</h3><p>Projetos, documentação e suporte técnico para integradores.</p><span class="haja-card-link">Saiba Mais</span></a>',
];

foreach ($replacements as $search => $replace) {
    $content = str_replace($search, $replace, $content);
}

$content = str_replace('<div class="haja-grid"><a class="haja-card haja-service-card"', '<div class="haja-grid haja-services-grid"><a class="haja-card haja-service-card"', $content);

$now = date('Y-m-d H:i:s');
$stmt = $mysqli->prepare("UPDATE wp_posts SET post_content=?, post_modified=?, post_modified_gmt=UTC_TIMESTAMP() WHERE ID=?");
$stmt->bind_param('ssi', $content, $now, $homeId);
$stmt->execute();
$stmt->close();

echo "Hero image and service icons updated.\n";
