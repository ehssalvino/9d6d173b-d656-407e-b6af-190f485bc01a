<?php
$mysqli = new mysqli('127.0.0.1', 'root', '', 'wordpress_local');
if ($mysqli->connect_errno) {
    fwrite(STDERR, $mysqli->connect_error . PHP_EOL);
    exit(1);
}
$mysqli->set_charset('utf8mb4');

$site = 'http://localhost/hajageracaosolar';
$hero = $site . '/wp-content/uploads/2023/06/energia-solar-banner-final-2.jpg';
$oldHero = $site . '/wp-content/uploads/2024/07/Instalacao-Modulos-3.jpg';

$stmt = $mysqli->prepare("SELECT ID, post_content FROM wp_posts WHERE post_type='page' AND post_name='home' LIMIT 1");
$stmt->execute();
$stmt->bind_result($homeId, $content);
if (!$stmt->fetch()) {
    throw new RuntimeException('Home not found');
}
$stmt->close();

$content = str_replace($oldHero, $hero, $content);

$content = preg_replace(
    '/\.haja-hero\{[^}]+\}/',
    '.haja-hero{position:relative;min-height:680px;display:flex;align-items:center;color:#fff;background-size:cover;background-position:center right;overflow:hidden}',
    $content,
    1
);
$content = preg_replace(
    '/\.haja-hero:before\{[^}]+\}/',
    '.haja-hero:before{content:"";position:absolute;inset:0 auto 0 0;width:36%;min-width:390px;background:rgba(244,151,43,.88);z-index:0}',
    $content,
    1
);
$content = preg_replace(
    '/\.haja-hero \.haja-wrap\{[^}]+\}/',
    '.haja-hero .haja-wrap{position:relative;z-index:1;margin-left:0;max-width:520px;padding-left:54px;padding-right:34px}',
    $content,
    1
);
$content = preg_replace(
    '/\.haja-kicker\{[^}]+\}/',
    '.haja-kicker{font-size:13px;text-transform:uppercase;letter-spacing:.08em;font-weight:900;color:#fff;margin:0 0 14px}',
    $content,
    1
);
$content = preg_replace(
    '/\.haja-hero h1,\.haja-page h1\{[^}]+\}/',
    '.haja-hero h1,.haja-page h1{max-width:880px;font-size:clamp(36px,5vw,68px);line-height:1.02;margin:0 0 22px;font-weight:900;letter-spacing:0}',
    $content,
    1
);
$content = preg_replace(
    '/\.haja-hero h1\{font-size:36px\}/',
    '.haja-hero h1{font-size:34px}',
    $content,
    1
);

$extraCss = <<<'CSS'
<style>
.haja-hero .haja-kicker{font-size:18px;line-height:1.2;color:#fff}
.haja-hero h1{font-size:31px;line-height:1.14;color:#008481;text-transform:none}
.haja-hero p{font-size:17px;line-height:1.48;color:#fff}
.haja-hero .haja-btn.primary{background:#008481;color:#fff}
.haja-hero .haja-btn.secondary{background:transparent;border-color:#fff;color:#fff}
@media(max-width:900px){.haja-hero:before{width:100%;min-width:0;background:rgba(244,151,43,.9)}.haja-hero .haja-wrap{max-width:760px;padding:44px 24px}.haja-hero h1{font-size:31px}}
</style>
CSS;

if (strpos($content, '.haja-hero .haja-kicker{font-size:18px') === false) {
    $content = str_replace('</style><main class="haja-page">', '</style>' . $extraCss . '<main class="haja-page">', $content);
}

$content = str_replace(
    '<p class="haja-kicker">Energia solar com engenharia e resultado</p><h1>Economize até 95% na sua conta de energia com um sistema solar projetado por engenheiros especializados.</h1><p>Projeto, instalação, homologação e monitoramento completo para residências, empresas e propriedades rurais.</p>',
    '<p class="haja-kicker">Energia solar com engenharia e resultado</p><h1>Economize até 95% na sua conta de energia com um sistema solar projetado por engenheiros especializados.</h1><p>Projeto, instalação, homologação e monitoramento completo para residências, empresas e propriedades rurais.</p>',
    $content
);

$now = date('Y-m-d H:i:s');
$stmt = $mysqli->prepare("UPDATE wp_posts SET post_content=?, post_modified=?, post_modified_gmt=UTC_TIMESTAMP() WHERE ID=?");
$stmt->bind_param('ssi', $content, $now, $homeId);
$stmt->execute();
$stmt->close();

echo "Hero updated with orange panel and previous image.\n";
