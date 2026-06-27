<?php
require 'C:/xampp/htdocs/hajageracaosolar/wp-load.php';

global $wpdb;
$row = $wpdb->get_row("SELECT ID, post_content FROM {$wpdb->posts} WHERE post_type='page' AND post_name='home' LIMIT 1", ARRAY_A);
if (!$row) {
    throw new RuntimeException('Home not found');
}

$content = $row['post_content'];
$content = preg_replace('/<script id="haja-service-links-js">.*?<\/script>/s', '', $content);
$content = preg_replace('/<style id="haja-service-links-css">.*?<\/style>/s', '', $content);
$content = preg_replace('/<style id="haja-saiba-mais-overlay-css">.*?<\/style>/s', '', $content);

$services = [
    [
        'title' => 'Energia Solar Residencial',
        'text' => 'A sua residência com sistema fotovoltaico dimensionado para reduzir a conta de energia com segurança.',
        'url' => '/hajageracaosolar/energia-solar-residencial/',
    ],
    [
        'title' => 'Energia Solar Comercial',
        'text' => 'Projetos para empresas que querem reduzir custo fixo e proteger margem com geração própria.',
        'url' => '/hajageracaosolar/energia-solar-comercial/',
    ],
    [
        'title' => 'Energia Solar Rural',
        'text' => 'Soluções para fazendas, sítios e propriedades produtivas com alto consumo de energia.',
        'url' => '/hajageracaosolar/energia-solar-rural/',
    ],
    [
        'title' => 'Homologação',
        'text' => 'Projetos, documentação e suporte técnico para integradores em processos de homologação.',
        'url' => '/hajageracaosolar/homologacao-para-integradores/',
    ],
];

$cards = '<div class="haja-grid haja-services-links">' . "\n";
foreach ($services as $service) {
    $url = esc_url($service['url']);
    $cards .= '      <div class="haja-card" data-haja-target="' . $url . '"><h3>' . esc_html($service['title']) . '</h3><p>' . esc_html($service['text']) . '</p><a class="haja-btn primary" href="' . $url . '" onclick="event.stopPropagation(); window.location.href=this.href; return false;">Saiba Mais</a></div>' . "\n";
}
$cards .= '    </div>';

$pattern = '/<div class="haja-grid haja-services-links">.*?<\/div>\s*<\/section>/s';
$replacement = $cards . "\n  </div>\n</section>";
$content2 = preg_replace($pattern, $replacement, $content, 1, $count);
if (!$count) {
    $pattern = '/<div class="haja-grid">\s*<(?:a|div) class="haja-card".*?<\/div>\s*<\/section>/s';
    $content2 = preg_replace($pattern, $replacement, $content, 1, $count);
}
if (!$count) {
    throw new RuntimeException('Services block not found');
}

$css = <<<'HTML'
<style id="haja-service-links-css">
.haja-services-links .haja-card{display:flex;flex-direction:column;align-items:flex-start;min-height:260px;cursor:default;position:relative}.haja-services-links .haja-card p{margin-bottom:22px}.haja-services-links .haja-card .haja-btn{margin-top:auto;align-self:flex-start;text-decoration:none;position:relative;z-index:10;pointer-events:auto}.haja-services-links .haja-card:hover{border-color:#f4ab17;box-shadow:0 16px 34px rgba(9,40,37,.10)}
</style>
HTML;
$insertPos = strpos($content2, '<section class="haja-band">');
if ($insertPos !== false) {
    $content2 = substr($content2, 0, $insertPos) . $css . substr($content2, $insertPos);
}

$wpdb->update(
    $wpdb->posts,
    [
        'post_content' => $content2,
        'post_modified' => current_time('mysql'),
        'post_modified_gmt' => current_time('mysql', true),
    ],
    ['ID' => (int) $row['ID']],
    ['%s','%s','%s'],
    ['%d']
);

clean_post_cache((int) $row['ID']);
wp_cache_flush();
if (class_exists('Elementor\Plugin')) {
    try {
        \Elementor\Plugin::instance()->files_manager->clear_cache();
    } catch (Throwable $e) {}
}
do_action('litespeed_purge_all');

foreach ($services as $service) {
    echo $service['title'] . ' => ' . $service['url'] . PHP_EOL;
}
echo "Caches cleared.\n";