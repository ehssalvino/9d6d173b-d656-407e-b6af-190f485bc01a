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
<style id="haja-services-third-figure-css">
#haja-servicos-principais{background:#fff!important;padding:72px 0 76px!important}
#haja-servicos-principais *{box-sizing:border-box!important}
#haja-servicos-principais .haja-servicos-grid{display:grid!important;grid-template-columns:repeat(4,1fr)!important;gap:18px!important;width:min(100%,920px)!important;margin:0 auto!important;padding:0 12px!important;align-items:stretch!important}
#haja-servicos-principais .haja-servico-card{display:flex!important;flex-direction:column!important;align-items:center!important;min-height:450px!important;padding:22px 12px 36px!important;border:1px solid #f4ab17!important;background:#fff!important;color:#222!important;text-align:center!important;text-decoration:none!important;box-shadow:none!important;border-radius:0!important}
#haja-servicos-principais .haja-icon-frame{position:relative!important;display:flex!important;justify-content:center!important;align-items:center!important;width:166px!important;height:76px!important;margin:0 auto 16px!important}
#haja-servicos-principais .haja-icon-frame:before{content:""!important;position:absolute!important;inset:-8px -10px 8px -8px!important;border:4px solid #050505!important;background:transparent!important;z-index:0!important}
#haja-servicos-principais .haja-servico-icon{position:relative!important;z-index:1!important;display:flex!important;align-items:center!important;justify-content:center!important;width:146px!important;height:60px!important;background:#f4ab17!important;color:#fff!important}
#haja-servicos-principais .haja-servico-icon svg{width:32px!important;height:32px!important;stroke:currentColor!important;fill:none!important;stroke-width:2.5!important;stroke-linecap:round!important;stroke-linejoin:round!important}
#haja-servicos-principais h3{margin:0!important;color:#202020!important;font-size:18px!important;font-weight:800!important;line-height:1.22!important;letter-spacing:0!important;text-transform:none!important}
#haja-servicos-principais .haja-servico-line{display:block!important;width:50px!important;height:2px!important;margin:13px auto 17px!important;background:#f4ab17!important}
#haja-servicos-principais p{max-width:178px!important;margin:0 auto!important;color:#555!important;font-size:13px!important;font-weight:400!important;line-height:1.55!important}
#haja-servicos-principais .haja-servico-btn{display:inline-flex!important;align-items:center!important;justify-content:center!important;min-width:118px!important;min-height:40px!important;margin-top:auto!important;padding:8px 18px!important;border:1px solid #f4ab17!important;background:#fff!important;color:#e98718!important;font-size:14px!important;font-weight:800!important;line-height:1!important}
#haja-servicos-principais .haja-servico-card:hover .haja-servico-btn{background:#f4ab17!important;color:#fff!important}
@media(max-width:1000px){#haja-servicos-principais .haja-servicos-grid{grid-template-columns:repeat(2,1fr)!important;width:min(100%,620px)!important}}
@media(max-width:640px){#haja-servicos-principais .haja-servicos-grid{grid-template-columns:1fr!important;width:min(100%,260px)!important}}
</style>
HTML;

$content = preg_replace('/<style id="haja-services-third-figure-css">.*?<\/style>/s', '', $content);
$sectionPos = strpos($content, '<section id="haja-servicos-principais">');
if ($sectionPos === false) {
    throw new RuntimeException('Services section not found');
}

$content = substr($content, 0, $sectionPos) . $css . substr($content, $sectionPos);

$stmt = $mysqli->prepare("UPDATE wp_posts SET post_content=?, post_modified=NOW(), post_modified_gmt=UTC_TIMESTAMP() WHERE ID=?");
$stmt->bind_param('si', $content, $homeId);
$stmt->execute();
$stmt->close();

echo "Services CSS reapplied.\n";
