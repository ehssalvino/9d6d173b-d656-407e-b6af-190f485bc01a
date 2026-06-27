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

$css = <<<'HTML'
<style id="haja-saiba-mais-overlay-css">
/* Sobrepoe o botao Saiba Mais nos 4 cards da secao Infinidade de servicos */
.haja-grid a.haja-card[href*="energia-solar-residencial"],
.haja-grid a.haja-card[href*="energia-solar-comercial"],
.haja-grid a.haja-card[href*="energia-solar-rural"],
.haja-grid a.haja-card[href*="homologacao-para-integradores"] {
    position: relative !important;
    overflow: visible !important;
    padding-bottom: 64px !important;
}
.haja-grid a.haja-card[href*="energia-solar-residencial"] .haja-btn,
.haja-grid a.haja-card[href*="energia-solar-comercial"] .haja-btn,
.haja-grid a.haja-card[href*="energia-solar-rural"] .haja-btn,
.haja-grid a.haja-card[href*="homologacao-para-integradores"] .haja-btn {
    position: absolute !important;
    left: 50% !important;
    bottom: -21px !important;
    transform: translateX(-50%) !important;
    z-index: 5 !important;
    white-space: nowrap !important;
}
</style>
HTML;

$content = preg_replace('/<style id="haja-saiba-mais-overlay-css">.*?<\/style>/s', '', $content);

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

echo "Overlay applied to all four Saiba Mais buttons.\n";
