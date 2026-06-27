<?php
require 'D:/xampp/htdocs/hajageracaosolar/wp-load.php';

$backup_name = 'codex_active_plugins_backup_' . gmdate('Ymd_His');
$active = get_option('active_plugins', array());
update_option($backup_name, $active, false);

$disable = array(
    'elementor-pro/elementor-pro.php',
    'elementskit-lite/elementskit-lite.php',
    'elementskit/elementskit.php',
    'essential-addons-for-elementor-lite/essential_adons_elementor.php',
    'envato-elements/envato-elements.php',
    'templately/templately.php',
);

$safe = array_values(array_diff($active, $disable));
update_option('active_plugins', $safe);

delete_post_meta(3551, '_elementor_css');
delete_post_meta(3551, '_edit_lock');

if (class_exists('Elementor\\Plugin')) {
    Elementor\Plugin::instance()->files_manager->clear_cache();
}

echo 'backup_option=' . $backup_name . PHP_EOL;
echo 'disabled=' . implode(',', array_values(array_intersect($active, $disable))) . PHP_EOL;
echo 'active_count=' . count($safe) . PHP_EOL;
