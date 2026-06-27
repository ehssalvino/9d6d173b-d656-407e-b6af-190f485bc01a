<?php
require 'D:/xampp/htdocs/hajageracaosolar/wp-load.php';

$disable = array(
    'elementor-pro/elementor-pro.php',
    'elementor-pro-old2/elementor-pro.php',
    'elementor-pro-old6/elementor-pro.php',
    'elementskit-lite/elementskit-lite.php',
    'elementskit-lite-old8/elementskit-lite.php',
    'elementskit/elementskit.php',
    'elementskit-old7/elementskit.php',
    'envato-elements/envato-elements.php',
    'essential-addons-for-elementor-lite/essential_adons_elementor.php',
    'essential-grid/essential-grid.php',
    'templately/templately.php',
);

$active = get_option('active_plugins', array());
update_option('codex_active_plugins_before_free_home_fix_' . gmdate('Ymd_His'), $active, false);
$active = array_values(array_diff($active, $disable));
if (!in_array('elementor/elementor.php', $active, true)) {
    $active[] = 'elementor/elementor.php';
}
update_option('active_plugins', $active);

$auto_updates = get_option('auto_update_plugins', array());
if (is_array($auto_updates)) {
    $auto_updates = array_values(array_filter($auto_updates, function ($plugin) {
        return strpos($plugin, 'elementor') === false;
    }));
    update_option('auto_update_plugins', $auto_updates);
}

$post = get_post(3551);
if (!$post) {
    fwrite(STDERR, "Home 3551 not found\n");
    exit(1);
}

$html = $post->post_content;
$data = array(
    array(
        'id' => 'codex_home_section',
        'elType' => 'section',
        'settings' => array(
            'layout' => 'full_width',
            'gap' => 'no',
        ),
        'elements' => array(
            array(
                'id' => 'codex_home_column',
                'elType' => 'column',
                'settings' => array(
                    '_column_size' => 100,
                    '_inline_size' => null,
                ),
                'elements' => array(
                    array(
                        'id' => 'codex_home_html',
                        'elType' => 'widget',
                        'widgetType' => 'html',
                        'settings' => array(
                            'html' => $html,
                        ),
                        'elements' => array(),
                    ),
                ),
                'isInner' => false,
            ),
        ),
        'isInner' => false,
    ),
);

update_post_meta(3551, '_elementor_data', wp_slash(wp_json_encode($data)));
update_post_meta(3551, '_elementor_edit_mode', 'builder');
update_post_meta(3551, '_elementor_template_type', 'wp-page');
update_post_meta(3551, '_elementor_version', '3.13.4');
delete_post_meta(3551, '_elementor_pro_version');
delete_post_meta(3551, '_elementor_css');
delete_post_meta(3551, '_elementor_page_assets');
delete_post_meta(3551, '_elementor_element_cache');
delete_post_meta(3551, '_edit_lock');

update_option('elementor_version', '3.13.4');
delete_option('elementor_pro_version');

global $wpdb;
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_elementor_%' OR option_name LIKE '_site_transient_elementor_%'");
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_elementor_%' OR option_name LIKE '_site_transient_timeout_elementor_%'");

if (class_exists('Elementor\\Plugin')) {
    Elementor\Plugin::instance()->files_manager->clear_cache();
}

echo 'active_plugins=' . implode(',', get_option('active_plugins', array())) . PHP_EOL;
echo 'home_elementor_data_bytes=' . strlen(get_post_meta(3551, '_elementor_data', true)) . PHP_EOL;
