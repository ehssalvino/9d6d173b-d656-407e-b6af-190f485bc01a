<?php
require 'D:/xampp/htdocs/hajageracaosolar/wp-load.php';
global $wpdb;
$rows = $wpdb->get_results("SELECT ID, post_type, post_title, post_date, post_modified, LENGTH(post_content) AS len, LEFT(post_content, 180) AS start FROM wp_posts WHERE post_content LIKE '%haja-hero%' OR post_content LIKE '%haja-services-links%' OR post_content LIKE '%<main class=\"haja-page\"%' ORDER BY post_modified DESC LIMIT 30", ARRAY_A);
foreach ($rows as $r) {
    echo $r['ID'] . ' | ' . $r['post_type'] . ' | ' . $r['post_title'] . ' | ' . $r['post_modified'] . ' | ' . $r['len'] . ' | ' . str_replace(array("\r", "\n"), ' ', $r['start']) . PHP_EOL;
}