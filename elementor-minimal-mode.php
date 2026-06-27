<?php
require 'D:/xampp/htdocs/hajageracaosolar/wp-load.php';

$backup_name = 'codex_active_plugins_backup_minimal_' . gmdate('Ymd_His');
$active = get_option('active_plugins', array());
update_option($backup_name, $active, false);

update_option('active_plugins', array('elementor/elementor.php'));

delete_post_meta(3551, '_elementor_css');
delete_post_meta(3551, '_edit_lock');

global $wpdb;
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_elementor_%' OR option_name LIKE '_site_transient_elementor_%'");
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_elementor_%' OR option_name LIKE '_site_transient_timeout_elementor_%'");

update_option('elementor_editor_loader_method', '1');

if (class_exists('Elementor\\Plugin')) {
    Elementor\Plugin::instance()->files_manager->clear_cache();
}

echo 'backup_option=' . $backup_name . PHP_EOL;
echo 'active_plugins=elementor/elementor.php' . PHP_EOL;
