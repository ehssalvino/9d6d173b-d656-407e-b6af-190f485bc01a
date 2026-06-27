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

$css = <<<'HTML'
<style id="haja-saiba-mais-overlay-css">
.haja-grid a.haja-card[href*="energia-solar-residencial"],
.haja-grid a.haja-card[href*="energia-solar-comercial"],
.haja-grid a.haja-card[href*="energia-solar-rural"],
.haja-grid a.haja-card[href*="homologacao-para-integradores"] {
    position: relative !important;
    overflow: visible !important;
    padding-bottom: 100px !important;
}
.haja-grid a.haja-card[href*="energia-solar-residencial"] .haja-btn,
.haja-grid a.haja-card[href*="energia-solar-comercial"] .haja-btn,
.haja-grid a.haja-card[href*="energia-solar-rural"] .haja-btn,
.haja-grid a.haja-card[href*="homologacao-para-integradores"] .haja-btn {
    position: absolute !important;
    left: calc(50% + 115px) !important;
    bottom: 34px !important;
    transform: translateX(-50%) !important;
    z-index: 5 !important;
    white-space: nowrap !important;
}
</style>
HTML;

$content = $row['post_content'];
$new = preg_replace('/<style id="haja-saiba-mais-overlay-css">.*?<\/style>/s', $css, $content, 1, $count);
if (!$count) {
    throw new RuntimeException('Overlay CSS block not found');
}

$stmt = $mysqli->prepare("UPDATE wp_posts SET post_content=?, post_modified=NOW(), post_modified_gmt=UTC_TIMESTAMP() WHERE ID=?");
$id = (int) $row['ID'];
$stmt->bind_param('si', $new, $id);
$stmt->execute();
$stmt->close();

echo "Button offset applied.\n";