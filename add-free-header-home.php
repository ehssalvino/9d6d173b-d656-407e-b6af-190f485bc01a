<?php
require 'D:/xampp/htdocs/hajageracaosolar/wp-load.php';

$post_id = 3551;
$post = get_post($post_id);
if (!$post) {
    fwrite(STDERR, "Home not found\n");
    exit(1);
}

$logo = home_url('/wp-content/uploads/2017/11/cropped-logohaja-300x131.jpg');
$header = <<<HTML
<style id="haja-free-header-css">
.haja-site-header{position:sticky;top:0;z-index:1000;background:#fff;border-bottom:1px solid rgba(8,49,46,.10);box-shadow:0 8px 24px rgba(8,49,46,.06)}
.haja-site-header .haja-header-inner{max-width:1160px;margin:0 auto;min-height:82px;padding:10px 22px;display:flex;align-items:center;justify-content:space-between;gap:22px}
.haja-site-logo{display:flex;align-items:center;text-decoration:none;flex:0 0 auto}
.haja-site-logo img{display:block;width:auto;height:58px;max-width:190px;object-fit:contain}
.haja-site-nav{display:flex;align-items:center;justify-content:flex-end;gap:22px;flex-wrap:wrap}
.haja-site-nav a{color:#16302d;text-decoration:none;font-weight:800;font-size:14px;line-height:1.2}
.haja-site-nav a:hover{color:#008481}
.haja-site-nav .haja-nav-cta{background:#008481;color:#fff;padding:12px 16px;border-radius:6px}
.haja-site-nav .haja-nav-cta:hover{background:#f4ab17;color:#102926}
@media(max-width:760px){.haja-site-header .haja-header-inner{align-items:flex-start;flex-direction:column}.haja-site-nav{justify-content:flex-start;gap:14px}.haja-site-logo img{height:50px}}
</style>
<header class="haja-site-header">
  <div class="haja-header-inner">
    <a class="haja-site-logo" href="/hajageracaosolar/" aria-label="Haja Geração Solar">
      <img src="$logo" alt="Haja Geração Solar">
    </a>
    <nav class="haja-site-nav" aria-label="Menu principal">
      <a href="/hajageracaosolar/">Home</a>
      <a href="/hajageracaosolar/energia-solar-residencial/">Residencial</a>
      <a href="/hajageracaosolar/energia-solar-comercial/">Comercial</a>
      <a href="/hajageracaosolar/energia-solar-rural/">Rural</a>
      <a href="/hajageracaosolar/homologacao-para-integradores/">Homologação</a>
      <a class="haja-nav-cta" href="/hajageracaosolar/calculadora-solar/">Simular economia</a>
    </nav>
  </div>
</header>
HTML;

$content = $post->post_content;
$content = preg_replace('/<style id="haja-free-header-css">.*?<\/header>\s*/s', '', $content);
$content = $header . "\n" . ltrim($content);

wp_update_post(array(
    'ID' => $post_id,
    'post_content' => wp_slash($content),
));

$data = get_post_meta($post_id, '_elementor_data', true);
$decoded = json_decode($data, true);
if (is_array($decoded)
    && isset($decoded[0]['elements'][0]['elements'][0]['settings']['html'])) {
    $decoded[0]['elements'][0]['elements'][0]['settings']['html'] = $content;
    update_post_meta($post_id, '_elementor_data', wp_slash(wp_json_encode($decoded)));
}

delete_post_meta($post_id, '_elementor_css');
delete_post_meta($post_id, '_elementor_element_cache');
delete_post_meta($post_id, '_edit_lock');

if (class_exists('Elementor\\Plugin')) {
    Elementor\Plugin::instance()->files_manager->clear_cache();
}

echo 'header_added=1' . PHP_EOL;
echo 'content_bytes=' . strlen($content) . PHP_EOL;
