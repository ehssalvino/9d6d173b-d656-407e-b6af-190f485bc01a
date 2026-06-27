<?php
$mysqli = new mysqli('127.0.0.1', 'root', '', 'wordpress_local');
$mysqli->set_charset('utf8mb4');

$row = $mysqli->query("SELECT post_content FROM wp_posts WHERE post_type='page' AND post_name='home' LIMIT 1")->fetch_assoc();
$content = $row['post_content'] ?? '';

echo 'overlay_css=' . substr_count($content, 'haja-saiba-mais-overlay-css') . PHP_EOL;
echo 'left_58=' . substr_count($content, 'left: 58%') . PHP_EOL;
echo 'bottom_23=' . substr_count($content, 'bottom: -23px') . PHP_EOL;

foreach ([
    'energia-solar-residencial',
    'energia-solar-comercial',
    'energia-solar-rural',
    'homologacao-para-integradores',
] as $slug) {
    echo $slug . '_selector=' . substr_count($content, 'href*="' . $slug . '"') . PHP_EOL;
}
