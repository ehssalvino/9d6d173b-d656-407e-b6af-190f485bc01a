<?php
require 'D:/xampp/htdocs/hajageracaosolar/wp-load.php';

$post_id = 3551;
$post = get_post($post_id);
if (!$post) {
    fwrite(STDERR, "Home not found\n");
    exit(1);
}

$logo = home_url('/wp-content/uploads/2023/06/Logotipo6.png');

$header = <<<HTML
<header class="haja-site-header" style="position:sticky;top:0;z-index:1000;background:#fff;border-bottom:1px solid rgba(8,49,46,.10);box-shadow:0 8px 24px rgba(8,49,46,.06);">
  <div class="haja-header-inner" style="max-width:1160px;margin:0 auto;min-height:82px;padding:10px 22px;display:flex;align-items:center;justify-content:space-between;gap:22px;">
    <a class="haja-site-logo" href="/hajageracaosolar/" aria-label="Haja Geração Solar" style="display:flex;align-items:center;text-decoration:none;flex:0 0 auto;">
      <img src="$logo" alt="Haja Geração Solar" style="display:block;width:auto;height:68px;max-width:260px;object-fit:contain;">
    </a>
    <nav class="haja-site-nav" aria-label="Menu principal" style="display:flex;align-items:center;justify-content:flex-end;gap:22px;flex-wrap:wrap;">
      <a href="/hajageracaosolar/" style="color:#16302d;text-decoration:none;font-weight:800;font-size:14px;line-height:1.2;">Home</a>
      <a href="/hajageracaosolar/energia-solar-residencial/" style="color:#16302d;text-decoration:none;font-weight:800;font-size:14px;line-height:1.2;">Residencial</a>
      <a href="/hajageracaosolar/energia-solar-comercial/" style="color:#16302d;text-decoration:none;font-weight:800;font-size:14px;line-height:1.2;">Comercial</a>
      <a href="/hajageracaosolar/energia-solar-rural/" style="color:#16302d;text-decoration:none;font-weight:800;font-size:14px;line-height:1.2;">Rural</a>
      <a href="/hajageracaosolar/homologacao-para-integradores/" style="color:#16302d;text-decoration:none;font-weight:800;font-size:14px;line-height:1.2;">Homologação</a>
      <a href="/hajageracaosolar/calculadora-solar/" style="background:#008481;color:#fff;padding:12px 16px;border-radius:6px;text-decoration:none;font-weight:800;font-size:14px;line-height:1.2;">Simular economia</a>
    </nav>
  </div>
</header>
HTML;

$content = $post->post_content;

// Remove the leaked style text and any previous manual header.
$content = preg_replace('/<style id="haja-free-header-css">.*?<\/style>\s*/s', '', $content);
$content = preg_replace('/\.haja-site-header\{.*?@media\(max-width:760px\).*?\}\s*/s', '', $content);
$content = preg_replace('/<header class="haja-site-header".*?<\/header>\s*/s', '', $content);
$content = $header . "\n" . ltrim($content);

wp_update_post(array(
    'ID' => $post_id,
    'post_content' => wp_slash($content),
));

update_post_meta($post_id, '_wp_page_template', 'elementor_canvas');

$data = json_decode(get_post_meta($post_id, '_elementor_data', true), true);
if (is_array($data)
    && isset($data[0]['elements'][0]['elements'][0]['settings']['html'])) {
    $data[0]['elements'][0]['elements'][0]['settings']['html'] = $content;
    update_post_meta($post_id, '_elementor_data', wp_slash(wp_json_encode($data)));
}

delete_post_meta($post_id, '_elementor_css');
delete_post_meta($post_id, '_elementor_element_cache');
delete_post_meta($post_id, '_edit_lock');

if (class_exists('Elementor\\Plugin')) {
    Elementor\Plugin::instance()->files_manager->clear_cache();
}

echo 'fixed=1' . PHP_EOL;
echo 'logo=' . $logo . PHP_EOL;
