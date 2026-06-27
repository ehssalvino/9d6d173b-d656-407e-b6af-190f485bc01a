<?php
$mysqli = new mysqli('127.0.0.1', 'root', '', 'wordpress_local');
if ($mysqli->connect_errno) {
    fwrite(STDERR, $mysqli->connect_error . PHP_EOL);
    exit(1);
}
$mysqli->set_charset('utf8mb4');

$stmt = $mysqli->prepare("SELECT ID, post_content FROM wp_posts WHERE post_type='page' AND post_name='home' LIMIT 1");
$stmt->execute();
$stmt->bind_result($homeId, $content);
if (!$stmt->fetch()) {
    throw new RuntimeException('Home not found');
}
$stmt->close();

$content = preg_replace_callback('/<style>.*?<\/style>/s', function ($match) {
    $css = $match[0];
    if (strpos($css, 'haja-service-card') !== false || strpos($css, 'haja-service-icon') !== false) {
        return '';
    }
    return $css;
}, $content);

$now = date('Y-m-d H:i:s');
$stmt = $mysqli->prepare("UPDATE wp_posts SET post_content=?, post_modified=?, post_modified_gmt=UTC_TIMESTAMP() WHERE ID=?");
$stmt->bind_param('ssi', $content, $now, $homeId);
$stmt->execute();
$stmt->close();

echo "Old service CSS cleaned.\n";
