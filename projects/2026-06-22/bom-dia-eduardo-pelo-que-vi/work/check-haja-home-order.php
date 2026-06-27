<?php
$mysqli = new mysqli('127.0.0.1', 'root', '', 'wordpress_local');
$mysqli->set_charset('utf8mb4');
$content = $mysqli->query("SELECT post_content FROM wp_posts WHERE post_type='page' AND post_name='home' LIMIT 1")->fetch_assoc()['post_content'] ?? '';
$items = [
    'Infinidade de serviços e produtos',
    'Nossa visão',
    'Nossa missão',
    'Benefícios da energia fotovoltaica',
    'F.A.Qs',
    'Como funciona',
    'Simule sua economia solar',
    'O que você recebe no estudo',
    'Projetos reais, engenharia visível',
];
foreach ($items as $item) {
    echo $item . '=' . strpos($content, $item) . PHP_EOL;
}
echo 'haja-servico-card=' . substr_count($content, 'haja-servico-card') . PHP_EOL;
echo 'haja-services-third-figure-css=' . substr_count($content, 'haja-services-third-figure-css') . PHP_EOL;
echo 'haja-icon-frame=' . substr_count($content, 'haja-icon-frame') . PHP_EOL;
echo 'home-bytes=' . strlen($content) . PHP_EOL;
