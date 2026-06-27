<?php
$mysqli = new mysqli('127.0.0.1', 'root', '', 'wordpress_local');
$mysqli->set_charset('utf8mb4');
$content = $mysqli->query("SELECT post_content FROM wp_posts WHERE post_type='page' AND post_name='home' LIMIT 1")->fetch_assoc()['post_content'] ?? '';
$needle = '<a class="haja-servico-card"';
$offset = 0;
while (($pos = strpos($content, $needle, $offset)) !== false) {
    echo $pos . PHP_EOL;
    $offset = $pos + 1;
}
echo 'steps=' . strpos($content, 'Como funciona') . PHP_EOL;
echo 'services_section=' . strpos($content, 'haja-servicos-principais') . PHP_EOL;
