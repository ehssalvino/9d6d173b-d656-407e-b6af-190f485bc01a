<?php
$mysqli = new mysqli('127.0.0.1', 'root', '', 'wordpress_local');
if ($mysqli->connect_errno) {
    fwrite(STDERR, $mysqli->connect_error . PHP_EOL);
    exit(1);
}
$mysqli->set_charset('utf8mb4');
$row = $mysqli->query("SELECT ID, post_content FROM wp_posts WHERE post_type='page' AND post_name='home' LIMIT 1")->fetch_assoc();
$homeId = (int)$row['ID'];
$content = $row['post_content'];

$firstStart = strpos($content, '<style id="haja-services-third-figure-css">');
if ($firstStart === false) {
    $firstStart = strpos($content, '<style>' . "\n" . '#haja-servicos-principais');
}
if ($firstStart === false) {
    $firstStart = strpos($content, '<section id="haja-servicos-principais">');
}
if ($firstStart === false) {
    throw new RuntimeException('First services start not found');
}

$secondStart = strpos($content, '<style id="haja-services-third-figure-css">', $firstStart + 1);
if ($secondStart === false) {
    $secondStart = strpos($content, '<section id="haja-servicos-principais">', $firstStart + 1);
}
if ($secondStart === false) {
    throw new RuntimeException('Second services start not found');
}

$newContent = substr($content, 0, $firstStart) . substr($content, $secondStart);
$stmt = $mysqli->prepare("UPDATE wp_posts SET post_content=?, post_modified=NOW(), post_modified_gmt=UTC_TIMESTAMP() WHERE ID=?");
$stmt->bind_param('si', $newContent, $homeId);
$stmt->execute();
$stmt->close();

echo "First duplicate services section removed.\n";
