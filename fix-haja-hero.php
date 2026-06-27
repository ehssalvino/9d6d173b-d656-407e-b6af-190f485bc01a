<?php
require 'D:/xampp/htdocs/hajageracaosolar/wp-load.php';

$main = get_option('haja_home_static_main_html', '');
if (!$main) {
    fwrite(STDERR, "Missing haja_home_static_main_html\n");
    exit(1);
}

$hero = '<main class="haja-page"><section class="haja-hero" style="background-image:url(' . "'" . home_url('/wp-content/uploads/2023/04/solar-panels-and-wind-energy-plants.jpg') . "'" . ')"><div class="haja-wrap"><p class="haja-kicker">ENERGIZE SEU FUTURO:</p><h1>SOLUCOES DE ENERGIA SOLAR SUSTENTAVEL E EFICIENTE</h1><p>Transforme a luz solar em economia e contribua para um futuro sustentavel com nossas solucoes de energia solar.</p><div class="haja-actions"><a class="haja-btn primary" href="#simulador">Descubra sua Economia!</a></div></div></section>';

$main = preg_replace('/<main class="haja-page"><section class="haja-hero".*?<\/section>/s', $hero, $main, 1);
$main = preg_replace('/\s*\.haja-services-links\s+\.haja-card\{.*?\.haja-services-links\s+\.haja-card:hover\{.*?\}\s*(?=<section class="haja-band">)/s', "\n", $main, 1);

update_option('haja_home_static_main_html', $main, false);
if (function_exists('wp_cache_flush')) {
    wp_cache_flush();
}

echo substr($main, 0, 900), "\n";