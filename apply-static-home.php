<?php

require 'D:/xampp/htdocs/hajageracaosolar/wp-load.php';

$home_id = 3551;
$post = get_post($home_id);

if (!$post) {
    fwrite(STDERR, "Home page not found.\n");
    exit(1);
}

$content = $post->post_content;
$start = strpos($content, '<main class="haja-page"');

if ($start === false) {
    fwrite(STDERR, "Could not find main Haja page markup.\n");
    exit(1);
}

$main = substr($content, $start);

if (strpos($main, '</main>') !== false) {
    $main = substr($main, 0, strpos($main, '</main>') + strlen('</main>'));
}

update_option('haja_home_static_main_html', $main, false);

wp_update_post([
    'ID' => $home_id,
    'post_content' => '[haja_home_static]',
]);

delete_post_meta($home_id, '_elementor_data');
delete_post_meta($home_id, '_elementor_edit_mode');
delete_post_meta($home_id, '_elementor_css');
delete_post_meta($home_id, '_elementor_element_cache');
delete_post_meta($home_id, '_elementor_page_assets');
update_post_meta($home_id, '_wp_page_template', 'elementor_canvas');

if (function_exists('wp_cache_flush')) {
    wp_cache_flush();
}

echo "Home converted to [haja_home_static]. Main bytes: " . strlen($main) . "\n";
