<?php
$m = new mysqli('127.0.0.1', 'root', '', 'wordpress_local');
if ($m->connect_errno) {
    fwrite(STDERR, $m->connect_error . PHP_EOL);
    exit(1);
}
$m->set_charset('utf8mb4');
$row = $m->query("SELECT ID, post_content FROM wp_posts WHERE post_type='page' AND post_name='home' LIMIT 1")->fetch_assoc();
if (!$row) {
    throw new RuntimeException('Home not found');
}
$content = $row['post_content'];

$content = preg_replace('/<style id="haja-saiba-mais-overlay-css">.*?<\/style>/s', '', $content);
$content = preg_replace('/<style id="haja-service-links-css">.*?<\/style>/s', '', $content);
$content = preg_replace('/<script id="haja-service-links-js">.*?<\/script>/s', '', $content);

$css = <<<'HTML'
<style id="haja-service-links-css">
.haja-services-links .haja-card{display:flex;flex-direction:column;align-items:flex-start;min-height:260px;cursor:pointer}.haja-services-links .haja-card p{margin-bottom:22px}.haja-services-links .haja-card .haja-btn{margin-top:auto;align-self:flex-start;text-decoration:none;position:relative;z-index:3}.haja-services-links .haja-card:focus-within,.haja-services-links .haja-card:hover{border-color:#f4ab17;box-shadow:0 16px 34px rgba(9,40,37,.10)}
</style>
HTML;

$script = <<<'HTML'
<script id="haja-service-links-js">
document.addEventListener('DOMContentLoaded',function(){
  document.querySelectorAll('.haja-services-links .haja-card').forEach(function(card){
    var link=card.querySelector('a.haja-btn[href]');
    if(!link){return;}
    card.setAttribute('data-haja-target',link.href);
    card.addEventListener('click',function(event){
      var target=event.target.closest('a');
      var href=link.href;
      if(target){
        href=target.href || href;
      }
      if(href){
        event.preventDefault();
        window.location.href=href;
      }
    });
  });
});
</script>
HTML;

$insertPos = strpos($content, '<section class="haja-band">');
if ($insertPos === false) {
    throw new RuntimeException('First services section not found');
}
$content = substr($content, 0, $insertPos) . $css . substr($content, $insertPos);

$servicesEnd = strpos($content, '</section>', $insertPos + strlen($css));
if ($servicesEnd === false) {
    throw new RuntimeException('Services section end not found');
}
$servicesEnd += strlen('</section>');
$content = substr($content, 0, $servicesEnd) . $script . substr($content, $servicesEnd);

$stmt = $m->prepare("UPDATE wp_posts SET post_content=?, post_modified=NOW(), post_modified_gmt=UTC_TIMESTAMP() WHERE ID=?");
$id = (int) $row['ID'];
$stmt->bind_param('si', $content, $id);
$stmt->execute();
$stmt->close();

echo "Service card navigation forced.\n";