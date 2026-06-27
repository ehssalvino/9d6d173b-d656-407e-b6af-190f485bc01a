<?php
$mysqli = new mysqli('127.0.0.1', 'root', '', 'wordpress_local');
if ($mysqli->connect_errno) {
    fwrite(STDERR, $mysqli->connect_error . PHP_EOL);
    exit(1);
}
$mysqli->set_charset('utf8mb4');

$row = $mysqli->query("SELECT ID, post_content FROM wp_posts WHERE post_type='page' AND post_name='home' LIMIT 1")->fetch_assoc();
if (!$row) {
    throw new RuntimeException('Home not found');
}

$homeId = (int) $row['ID'];
$content = $row['post_content'];
$needle = '<h2>Como funciona</h2>';

$first = strpos($content, $needle);
$second = strpos($content, $needle, $first === false ? 0 : $first + strlen($needle));

if ($first === false) {
    throw new RuntimeException('Como funciona not found');
}

if ($second !== false) {
    $sectionStart = strrpos(substr($content, 0, $second), '<section');
    $sectionEnd = strpos($content, '</section>', $second);
    if ($sectionStart === false || $sectionEnd === false) {
        throw new RuntimeException('Could not isolate duplicate Como funciona section');
    }
    $sectionEnd += strlen('</section>');
    $content = substr($content, 0, $sectionStart) . substr($content, $sectionEnd);
}

$stmt = $mysqli->prepare("UPDATE wp_posts SET post_content=?, post_modified=NOW(), post_modified_gmt=UTC_TIMESTAMP() WHERE ID=?");
$stmt->bind_param('si', $content, $homeId);
$stmt->execute();
$stmt->close();

echo "Como funciona occurrences before: " . (1 + ($second !== false ? 1 : 0)) . PHP_EOL;
echo "Duplicate Como funciona removed if present.\n";
