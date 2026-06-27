<?php
require 'D:/xampp/htdocs/hajageracaosolar/wp-load.php';

$backup = get_option('codex_active_plugins_backup_20260624_141950');
if ($backup && is_array($backup)) {
    update_option('active_plugins', $backup);
}

update_option('elementor_version', '3.13.4');
delete_option('elementor_remote_info_library');
delete_option('elementor_remote_info_feed_data');
delete_post_meta(3551, '_elementor_css');
delete_post_meta(3551, '_edit_lock');

global $wpdb;
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_elementor_%' OR option_name LIKE '_site_transient_elementor_%'");
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_elementor_%' OR option_name LIKE '_site_transient_timeout_elementor_%'");

if (class_exists('Elementor\\Plugin')) {
    Elementor\Plugin::instance()->files_manager->clear_cache();
}

echo 'active_plugins=' . count(get_option('active_plugins', array())) . PHP_EOL;
echo 'elementor_version=' . get_option('elementor_version') . PHP_EOL;
