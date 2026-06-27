<?php
$mysqli = new mysqli('127.0.0.1', 'root', '', 'wordpress_local');
$mysqli->set_charset('utf8mb4');
$content = $mysqli->query("SELECT post_content FROM wp_posts WHERE post_type='page' AND post_name='home' LIMIT 1")->fetch_assoc()['post_content'] ?? '';
echo 'overlay_css=' . substr_count($content, 'haja-saiba-mais-overlay-css') . PHP_EOL;
foreach ([
    'energia-solar-residencial',
    'energia-solar-comercial',
    'energia-solar-rural',
    'homologacao-para-integradores',
] as $slug) {
    echo $slug . '_selector=' . substr_count($content, 'href*="' . $slug . '"') . PHP_EOL;
}
echo 'bottom_overlay=' . substr_count($content, 'bottom: -21px') . PHP_EOL;
