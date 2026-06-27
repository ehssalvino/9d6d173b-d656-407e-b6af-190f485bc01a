<?php
$mysqli = new mysqli('127.0.0.1', 'root', '', 'wordpress_local');
$mysqli->set_charset('utf8mb4');
$content = $mysqli->query("SELECT post_content FROM wp_posts WHERE post_type='page' AND post_name='home' LIMIT 1")->fetch_assoc()['post_content'] ?? '';
$checks = [
    'como_funciona' => '<h2>Como funciona</h2>',
    'simule' => 'Simule sua economia solar',
    'recebe_estudo' => 'O que você recebe no estudo',
    'projetos_reais' => 'Projetos reais, engenharia visível',
    'infinidade' => 'Infinidade de serviços e produtos',
    'faq' => 'F.A.Qs',
];
foreach ($checks as $label => $needle) {
    echo $label . '=' . substr_count($content, $needle) . PHP_EOL;
}
echo 'bytes=' . strlen($content) . PHP_EOL;
