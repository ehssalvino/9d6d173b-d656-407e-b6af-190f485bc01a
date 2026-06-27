<?php
require 'D:/xampp/htdocs/hajageracaosolar/wp-load.php';

$post_id = 3551;
$logo = home_url('/wp-content/uploads/2023/06/Logotipo6.png');
$post = get_post($post_id);
if (!$post) {
    fwrite(STDERR, "Home not found\n");
    exit(1);
}

$content = $post->post_content;
$content = preg_replace(
    '~(<a class="haja-site-logo"[^>]*>\s*<img\s+src=")[^"]+(" alt="Haja Geração Solar")~s',
    '$1' . esc_url($logo) . '$2',
    $content
);
$content = str_replace(
    '.haja-site-logo img{display:block;width:auto;height:58px;max-width:190px;object-fit:contain}',
    '.haja-site-logo img{display:block;width:auto;height:68px;max-width:260px;object-fit:contain}',
    $content
);

wp_update_post(array(
    'ID' => $post_id,
    'post_content' => wp_slash($content),
));

update_post_meta($post_id, '_wp_page_template', 'elementor_canvas');

$data = json_decode(get_post_meta($post_id, '_elementor_data', true), true);
if (is_array($data)
    && isset($data[0]['elements'][0]['elements'][0]['settings']['html'])) {
    $data[0]['elements'][0]['elements'][0]['settings']['html'] = $content;
    update_post_meta($post_id, '_elementor_data', wp_slash(wp_json_encode($data)));
}

delete_post_meta($post_id, '_elementor_css');
delete_post_meta($post_id, '_elementor_element_cache');
delete_post_meta($post_id, '_edit_lock');

if (class_exists('Elementor\\Plugin')) {
    Elementor\Plugin::instance()->files_manager->clear_cache();
}

echo 'template=' . get_post_meta($post_id, '_wp_page_template', true) . PHP_EOL;
echo 'logo=' . $logo . PHP_EOL;
