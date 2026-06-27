<?php
$mysqli = new mysqli('127.0.0.1', 'root', '', 'wordpress_local');
$mysqli->set_charset('utf8mb4');
$row = $mysqli->query("SELECT ID, post_content FROM wp_posts WHERE post_type='page' AND post_name='home' LIMIT 1")->fetch_assoc();
$homeId = (int)$row['ID'];
$content = $row['post_content'];

$first = strpos($content, '<section id="haja-servicos-principais">');
$second = strpos($content, '<section id="haja-servicos-principais">', $first + 1);
if ($first === false || $second === false) {
    throw new RuntimeException('Could not find duplicate service sections');
}

$styleStart = strrpos(substr($content, 0, $first), '<style');
if ($styleStart !== false) {
    $start = $styleStart;
} else {
    $start = $first;
}

$newContent = substr($content, 0, $start) . substr($content, $second);
$stmt = $mysqli->prepare("UPDATE wp_posts SET post_content=?, post_modified=NOW(), post_modified_gmt=UTC_TIMESTAMP() WHERE ID=?");
$stmt->bind_param('si', $newContent, $homeId);
$stmt->execute();
$stmt->close();
echo "Leading duplicate services section removed.\n";
