<?php
require 'C:/xampp/htdocs/hajageracaosolar/wp-load.php';
$page = get_page_by_path('home');
$content = $page ? $page->post_content : '';
echo 'has_si_shortcode=' . (has_shortcode($content, 'si_solar_calculator') ? '1' : '0') . PHP_EOL;
echo 'has_redirect_form=' . (strpos($content, 'action="/hajageracaosolar/calculadora-solar/"') !== false ? '1' : '0') . PHP_EOL;
echo 'has_simule=' . (strpos($content, 'Simule sua economia solar') !== false ? '1' : '0') . PHP_EOL;
echo 'has_recebe=' . (strpos($content, 'O que você recebe no estudo') !== false ? '1' : '0') . PHP_EOL;