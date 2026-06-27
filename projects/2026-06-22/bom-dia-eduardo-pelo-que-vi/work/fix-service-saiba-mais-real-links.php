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

$old = <<<'HTML'
<div class="haja-grid">
      <a class="haja-card" href="http://localhost/hajageracaosolar/energia-solar-residencial/"><h3>Energia Solar Residencial</h3><p>A sua residˆncia com sistema fotovoltaico dimensionado para reduzir a conta de energia com seguran‡a.</p><span class="haja-btn primary">Saiba Mais</span></a>
      <a class="haja-card" href="http://localhost/hajageracaosolar/energia-solar-comercial/"><h3>Energia Solar Comercial</h3><p>Projetos para empresas que querem reduzir custo fixo e proteger margem com gera‡Æo pr¢pria.</p><span class="haja-btn primary">Saiba Mais</span></a>
      <a class="haja-card" href="http://localhost/hajageracaosolar/energia-solar-rural/"><h3>Energia Solar Rural</h3><p>Solu‡äes para fazendas, s¡tios e propriedades produtivas com alto consumo de energia.</p><span class="haja-btn primary">Saiba Mais</span></a>
      <a class="haja-card" href="http://localhost/hajageracaosolar/homologacao-para-integradores/"><h3>Homologa‡Æo</h3><p>Projetos, documenta‡Æo e suporte t‚cnico para integradores em processos de homologa‡Æo.</p><span class="haja-btn primary">Saiba Mais</span></a>
    </div>
HTML;

$new = <<<'HTML'
<div class="haja-grid haja-services-links">
      <div class="haja-card"><h3>Energia Solar Residencial</h3><p>A sua residência com sistema fotovoltaico dimensionado para reduzir a conta de energia com segurança.</p><a class="haja-btn primary" href="http://localhost/hajageracaosolar/energia-solar-residencial/">Saiba Mais</a></div>
      <div class="haja-card"><h3>Energia Solar Comercial</h3><p>Projetos para empresas que querem reduzir custo fixo e proteger margem com geração própria.</p><a class="haja-btn primary" href="http://localhost/hajageracaosolar/energia-solar-comercial/">Saiba Mais</a></div>
      <div class="haja-card"><h3>Energia Solar Rural</h3><p>Soluções para fazendas, sítios e propriedades produtivas com alto consumo de energia.</p><a class="haja-btn primary" href="http://localhost/hajageracaosolar/energia-solar-rural/">Saiba Mais</a></div>
      <div class="haja-card"><h3>Homologação</h3><p>Projetos, documentação e suporte técnico para integradores em processos de homologação.</p><a class="haja-btn primary" href="http://localhost/hajageracaosolar/homologacao-para-integradores/">Saiba Mais</a></div>
    </div>
HTML;

if (strpos($content, $old) === false) {
    $pattern = '/<div class="haja-grid">\s*<a class="haja-card" href="http:\/\/localhost\/hajageracaosolar\/energia-solar-residencial\/">.*?<\/div>\s*<\/section>/s';
    $replacement = $new . "\n  </div>\n</section>";
    $content2 = preg_replace($pattern, $replacement, $content, 1, $count);
    if (!$count) {
        throw new RuntimeException('Services cards block not found');
    }
    $content = $content2;
} else {
    $content = str_replace($old, $new, $content);
}

$css = <<<'CSS'
<style id="haja-service-links-css">
.haja-services-links .haja-card{display:flex;flex-direction:column;align-items:flex-start;min-height:260px}.haja-services-links .haja-card p{margin-bottom:22px}.haja-services-links .haja-card .haja-btn{margin-top:auto;align-self:flex-start;text-decoration:none}
</style>
CSS;
$content = preg_replace('/<style id="haja-service-links-css">.*?<\/style>/s', '', $content);
$pos = strpos($content, '<section class="haja-band">');
if ($pos !== false) {
    $content = substr($content, 0, $pos) . $css . substr($content, $pos);
}

$stmt = $m->prepare("UPDATE wp_posts SET post_content=?, post_modified=NOW(), post_modified_gmt=UTC_TIMESTAMP() WHERE ID=?");
$id = (int) $row['ID'];
$stmt->bind_param('si', $content, $id);
$stmt->execute();
$stmt->close();

echo "Service Saiba Mais links fixed.\n";