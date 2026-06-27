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

$fixCss = <<<'CSS'
<style>
.haja-services-grid{grid-template-columns:repeat(4,minmax(0,1fr));gap:28px;align-items:stretch;padding-top:54px}
.haja-services-grid .haja-service-card{display:flex;flex-direction:column;align-items:center;justify-content:flex-start;width:100%;height:100%;min-height:330px;padding:84px 28px 26px;position:relative;overflow:visible}
.haja-services-grid .haja-service-card .haja-service-icon{top:-50px}
.haja-services-grid .haja-service-card p{flex:1;width:100%;margin:0 0 18px}
.haja-services-grid .haja-service-card .haja-card-link{position:static;display:inline-flex;margin:0 auto;z-index:2}
@media(max-width:1100px){.haja-services-grid{grid-template-columns:repeat(2,minmax(0,1fr));row-gap:78px}}
@media(max-width:640px){.haja-services-grid{grid-template-columns:1fr;row-gap:78px}.haja-services-grid .haja-service-card{min-height:300px}}
</style>
CSS;

if (strpos($content, '.haja-services-grid .haja-service-card{display:flex') === false) {
    $content = str_replace('</style><main class="haja-page">', '</style>' . $fixCss . '<main class="haja-page">', $content);
}

$now = date('Y-m-d H:i:s');
$stmt = $mysqli->prepare("UPDATE wp_posts SET post_content=?, post_modified=?, post_modified_gmt=UTC_TIMESTAMP() WHERE ID=?");
$stmt->bind_param('ssi', $content, $now, $homeId);
$stmt->execute();
$stmt->close();

echo "Service card buttons fixed.\n";
