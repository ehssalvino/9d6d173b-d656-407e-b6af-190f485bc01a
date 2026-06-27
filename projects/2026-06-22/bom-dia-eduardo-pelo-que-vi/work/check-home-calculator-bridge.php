<?php
$m = new mysqli('127.0.0.1', 'root', '', 'wordpress_local');
$m->set_charset('utf8mb4');
$c = $m->query("SELECT post_content FROM wp_posts WHERE post_type='page' AND post_name='home' LIMIT 1")->fetch_assoc()['post_content'] ?? '';
echo 'home_action=' . substr_count($c, '/hajageracaosolar/calculadora-solar/') . PHP_EOL;
echo 'old_data_haja_economy=' . substr_count($c, 'data-haja-economy') . PHP_EOL;
echo 'new_button=' . substr_count($c, 'Continuar na calculadora') . PHP_EOL;
echo 'kind_rural=' . substr_count($c, 'value="rural"') . PHP_EOL;