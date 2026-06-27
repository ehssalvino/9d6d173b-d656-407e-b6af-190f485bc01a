<?php
require 'C:/xampp/htdocs/hajageracaosolar/wp-load.php';
$page = get_page_by_path('calculadora-solar');
$content = $page ? $page->post_content : '';
echo 'page_found=' . ($page ? '1' : '0') . PHP_EOL;
echo 'has_shortcode=' . (has_shortcode($content, 'solar-calculator') ? '1' : '0') . PHP_EOL;
echo 'shortcode_exists=' . (shortcode_exists('solar-calculator') ? '1' : '0') . PHP_EOL;
$html = do_shortcode('[solar-calculator]');
echo 'render_bytes=' . strlen($html) . PHP_EOL;
echo 'has_solarrechner=' . (strpos($html, 'solarrechner') !== false ? '1' : '0') . PHP_EOL;