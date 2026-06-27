<?php
require 'C:/xampp/htdocs/hajageracaosolar/wp-load.php';
$page = get_page_by_path('calculadora-solar');
$content = $page ? $page->post_content : '';
echo 'page_found=' . ($page ? '1' : '0') . PHP_EOL;
echo 'has_si_shortcode=' . (has_shortcode($content, 'si_solar_calculator') ? '1' : '0') . PHP_EOL;
echo 'shortcode_exists=' . (shortcode_exists('si_solar_calculator') ? '1' : '0') . PHP_EOL;
$html = do_shortcode('[si_solar_calculator mode="public"]');
echo 'render_bytes=' . strlen($html) . PHP_EOL;
echo 'has_public_app=' . (strpos($html, 'si-public-app') !== false ? '1' : '0') . PHP_EOL;
echo 'has_heading=' . (strpos($html, 'Descubra quanto') !== false ? '1' : '0') . PHP_EOL;
echo 'has_step=' . (strpos($html, 'Etapa 1') !== false || strpos($html, 'ETAPA 1') !== false ? '1' : '0') . PHP_EOL;