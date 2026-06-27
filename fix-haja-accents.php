<?php
require 'D:/xampp/htdocs/hajageracaosolar/wp-load.php';
$main = get_option('haja_home_static_main_html', '');
$main = str_replace('SOLUCOES DE ENERGIA SOLAR SUSTENTAVEL E EFICIENTE', 'SOLU&Ccedil;&Otilde;ES DE ENERGIA SOLAR SUSTENT&Aacute;VEL E EFICIENTE', $main);
$main = str_replace('Transforme a luz solar em economia e contribua para um futuro sustentavel com nossas solucoes de energia solar.', 'Transforme a luz solar em economia e contribua para um futuro sustent&aacute;vel com nossas solu&ccedil;&otilde;es de energia solar.', $main);
update_option('haja_home_static_main_html', $main, false);
if (function_exists('wp_cache_flush')) { wp_cache_flush(); }
echo 'ok';