<?php
require 'D:/xampp/htdocs/hajageracaosolar/wp-load.php';

delete_post_meta(3551, '_elementor_css');

if (class_exists('Elementor\\Plugin')) {
    Elementor\Plugin::instance()->files_manager->clear_cache();
    $doc = Elementor\Plugin::instance()->documents->get(3551);
    echo 'document=' . (is_object($doc) ? get_class($doc) : 'none') . PHP_EOL;
    $data = is_object($doc) ? $doc->get_elements_data() : array();
    echo 'elements=' . (is_array($data) ? count($data) : -1) . PHP_EOL;
}

do_action('litespeed_purge_all');
echo 'ok' . PHP_EOL;
