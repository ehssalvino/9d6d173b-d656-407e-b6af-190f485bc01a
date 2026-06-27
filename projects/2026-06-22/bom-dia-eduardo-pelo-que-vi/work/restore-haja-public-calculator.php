<?php
$mysqli = new mysqli('127.0.0.1', 'root', '', 'wordpress_local');
if ($mysqli->connect_errno) {
    fwrite(STDERR, $mysqli->connect_error . PHP_EOL);
    exit(1);
}
$mysqli->set_charset('utf8mb4');

$row = $mysqli->query("SELECT ID FROM wp_posts WHERE post_type='page' AND post_name='calculadora-solar' LIMIT 1")->fetch_assoc();
if (!$row) {
    throw new RuntimeException('Calculator page not found');
}

$content = '[si_solar_calculator mode="public"]';
$id = (int) $row['ID'];
$stmt = $mysqli->prepare("UPDATE wp_posts SET post_content=?, post_modified=NOW(), post_modified_gmt=UTC_TIMESTAMP() WHERE ID=?");
$stmt->bind_param('si', $content, $id);
$stmt->execute();
$stmt->close();

$mysqli->query("UPDATE wp_options SET option_value='6043' WHERE option_name='si_calculator_page_id'");

echo "Haja public calculator restored on page ID {$id}.\n";