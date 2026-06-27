<?php
$mysqli = new mysqli('127.0.0.1', 'root', '', 'wordpress_local');
if ($mysqli->connect_errno) {
    fwrite(STDERR, $mysqli->connect_error . PHP_EOL);
    exit(1);
}
$mysqli->set_charset('utf8mb4');

$result = $mysqli->query("SELECT ID FROM wp_posts WHERE post_type='page'");
while ($row = $result->fetch_assoc()) {
    $postId = (int)$row['ID'];
    $meta = $mysqli->query("SELECT meta_id FROM wp_postmeta WHERE post_id={$postId} AND meta_key='_wp_page_template' ORDER BY meta_id");
    $ids = [];
    while ($m = $meta->fetch_assoc()) {
        $ids[] = (int)$m['meta_id'];
    }
    if (count($ids) > 1) {
        array_pop($ids);
        $mysqli->query("DELETE FROM wp_postmeta WHERE meta_id IN (" . implode(',', $ids) . ")");
    }
}
echo "Duplicate page template meta cleaned.\n";
