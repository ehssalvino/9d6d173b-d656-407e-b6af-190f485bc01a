<?php
$mysqli = new mysqli('127.0.0.1', 'root', '', 'wordpress_local');
$mysqli->set_charset('utf8mb4');
$content = $mysqli->query("SELECT post_content FROM wp_posts WHERE post_type='page' AND post_name='home' LIMIT 1")->fetch_assoc()['post_content'] ?? '';
echo 'overlay_css=' . substr_count($content, 'haja-saiba-mais-overlay-css') . PHP_EOL;
echo 'left_calc_115=' . substr_count($content, 'left: calc(50% + 115px)') . PHP_EOL;
echo 'bottom_34=' . substr_count($content, 'bottom: 34px') . PHP_EOL;
echo 'padding_100=' . substr_count($content, 'padding-bottom: 100px') . PHP_EOL;
foreach (['energia-solar-residencial','energia-solar-comercial','energia-solar-rural','homologacao-para-integradores'] as $slug) {
    echo $slug . '=' . substr_count($content, 'href*="' . $slug . '"') . PHP_EOL;
}