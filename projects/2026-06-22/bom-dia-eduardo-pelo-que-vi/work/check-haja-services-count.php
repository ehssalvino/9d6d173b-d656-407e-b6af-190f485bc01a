<?php
$mysqli = new mysqli('127.0.0.1', 'root', '', 'wordpress_local');
$mysqli->set_charset('utf8mb4');
$row = $mysqli->query("SELECT post_content FROM wp_posts WHERE post_type='page' AND post_name='home' LIMIT 1")->fetch_assoc();
$content = $row['post_content'] ?? '';
echo 'cards=' . substr_count($content, '<a class="haja-servico-card"') . PHP_EOL;
echo 'frames=' . substr_count($content, 'haja-icon-frame') . PHP_EOL;
echo 'buttons=' . substr_count($content, 'Saiba Mais') . PHP_EOL;
echo 'css_blocks=' . substr_count($content, 'haja-services-third-figure-css') . PHP_EOL;
echo 'old_simple=' . substr_count($content, 'haja-simple-services') . PHP_EOL;
