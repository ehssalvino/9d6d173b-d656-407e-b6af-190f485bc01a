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

$css = <<<CSS
<style id="haja-saiba-mais-overlay-css">
#haja-servicos-principais .haja-card {
    position: relative;
    overflow: visible;
    padding-bottom: 54px;
}
#haja-servicos-principais .haja-card .haja-btn,
#haja-servicos-principais .haja-card .haja-btn.primary {
    position: absolute;
    left: 50%;
    bottom: -21px;
    transform: translateX(-50%);
    z-index: 4;
    white-space: nowrap;
}
</style>
CSS;

$content = preg_replace('/<style id="haja-saiba-mais-overlay-css">.*?<\/style>/s', '', $content);
$marker = '<section class="haja-band">';
$pos = strpos($content, 'Infinidade de serviços e produtos');
if ($pos === false) {
    throw new RuntimeException('Services title not found');
}
$insert = strrpos(substr($content, 0, $pos), '<section');
if ($insert === false) {
    throw new RuntimeException('Services section start not found');
}

$content = substr($content, 0, $insert) . $css . substr($content, $insert);

$stmt = $mysqli->prepare("UPDATE wp_posts SET post_content=?, post_modified=NOW(), post_modified_gmt=UTC_TIMESTAMP() WHERE ID=?");
$stmt->bind_param('si', $content, $homeId);
$stmt->execute();
$stmt->close();

echo "Saiba Mais overlay CSS applied.\n";
