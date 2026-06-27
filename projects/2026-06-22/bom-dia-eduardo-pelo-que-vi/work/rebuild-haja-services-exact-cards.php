<?php
$mysqli = new mysqli('127.0.0.1', 'root', '', 'wordpress_local');
if ($mysqli->connect_errno) {
    fwrite(STDERR, $mysqli->connect_error . PHP_EOL);
    exit(1);
}
$mysqli->set_charset('utf8mb4');

$site = 'http://localhost/hajageracaosolar';

$stmt = $mysqli->prepare("SELECT ID, post_content FROM wp_posts WHERE post_type='page' AND post_name='home' LIMIT 1");
$stmt->execute();
$stmt->bind_result($homeId, $content);
if (!$stmt->fetch()) {
    throw new RuntimeException('Home not found');
}
$stmt->close();

$css = <<<'CSS'
<style>
#haja-servicos-principais{padding:70px 0;background:#fff}
#haja-servicos-principais .haja-servicos-grid{display:grid !important;grid-template-columns:repeat(4,1fr) !important;gap:18px !important;align-items:stretch !important;max-width:980px !important;margin:0 auto !important}
#haja-servicos-principais .haja-servico-card{box-sizing:border-box !important;display:flex !important;flex-direction:column !important;align-items:center !important;text-align:center !important;min-height:512px !important;padding:22px 12px 40px !important;border:1px solid #f4ab17 !important;background:#fff !important;color:#222 !important;text-decoration:none !important;box-shadow:none !important;border-radius:0 !important}
#haja-servicos-principais .haja-servico-icon{display:flex !important;align-items:center !important;justify-content:center !important;width:182px !important;height:76px !important;margin:0 auto 14px !important;background:#f4ab17 !important;color:#fff !important}
#haja-servicos-principais .haja-servico-icon svg{width:42px !important;height:42px !important;stroke:currentColor !important;fill:none !important;stroke-width:2.25 !important;stroke-linecap:round !important;stroke-linejoin:round !important}
#haja-servicos-principais .haja-servico-card h3{font-size:18px !important;line-height:1.22 !important;margin:0 !important;color:#222 !important;font-weight:800 !important;letter-spacing:0 !important;text-transform:none !important}
#haja-servicos-principais .haja-servico-line{display:block !important;width:50px !important;height:2px !important;background:#f4ab17 !important;margin:13px auto 17px !important}
#haja-servicos-principais .haja-servico-card p{font-size:13px !important;line-height:1.55 !important;color:#555 !important;margin:0 auto !important;max-width:178px !important;font-weight:400 !important}
#haja-servicos-principais .haja-servico-btn{display:inline-flex !important;align-items:center !important;justify-content:center !important;margin-top:auto !important;min-width:118px !important;min-height:40px !important;padding:8px 18px !important;border:1px solid #f4ab17 !important;background:#fff !important;color:#e98718 !important;font-size:14px !important;font-weight:800 !important;text-decoration:none !important}
#haja-servicos-principais .haja-servico-card:hover .haja-servico-btn{background:#f4ab17 !important;color:#fff !important}
@media(max-width:1000px){#haja-servicos-principais .haja-servicos-grid{grid-template-columns:repeat(2,1fr) !important;max-width:620px !important}}
@media(max-width:640px){#haja-servicos-principais .haja-servicos-grid{grid-template-columns:1fr !important;max-width:260px !important}#haja-servicos-principais .haja-servico-card{min-height:430px !important}}
</style>
CSS;

$icons = [
    'home' => '<svg viewBox="0 0 24 24"><path d="M3 11.5 12 4l9 7.5"/><path d="M5.5 10.5V20h13v-9.5"/><path d="M9.5 20v-5.5h5V20"/></svg>',
    'commercial' => '<svg viewBox="0 0 24 24"><path d="M4 21h16"/><path d="M6 21V6h12v15"/><path d="M9 9h2M13 9h2M9 13h2M13 13h2"/><path d="M10 21v-4h4v4"/></svg>',
    'rural' => '<svg viewBox="0 0 24 24"><path d="M12 21V10"/><path d="M12 10C8 10 5.2 7.7 4 3.8 8.2 4 11.1 6.1 12 10Z"/><path d="M12 14c4.2-.1 7-2.4 8-6.2-4.1.2-7 2.2-8 6.2Z"/></svg>',
    'doc' => '<svg viewBox="0 0 24 24"><path d="M7 3h7l4 4v14H7z"/><path d="M14 3v5h5"/><path d="m9.5 14 2 2 4-5"/></svg>',
];

$services = $css . <<<HTML
<section id="haja-servicos-principais">
  <div class="haja-servicos-grid">
    <a class="haja-servico-card" href="$site/energia-solar-residencial/">
      <span class="haja-servico-icon" aria-hidden="true">{$icons['home']}</span>
      <h3>Energia Solar<br>Residencial</h3>
      <span class="haja-servico-line"></span>
      <p>Sistemas para casas e condomínios com economia mensal, valorização do imóvel e acompanhamento completo.</p>
      <span class="haja-servico-btn">Saiba Mais</span>
    </a>
    <a class="haja-servico-card" href="$site/energia-solar-comercial/">
      <span class="haja-servico-icon" aria-hidden="true">{$icons['commercial']}</span>
      <h3>Energia Solar<br>Comercial</h3>
      <span class="haja-servico-line"></span>
      <p>Projetos para empresas que querem reduzir custo fixo, proteger margem e previsibilizar a conta de energia.</p>
      <span class="haja-servico-btn">Saiba Mais</span>
    </a>
    <a class="haja-servico-card" href="$site/energia-solar-rural/">
      <span class="haja-servico-icon" aria-hidden="true">{$icons['rural']}</span>
      <h3>Energia Solar Rural</h3>
      <span class="haja-servico-line"></span>
      <p>Soluções para fazendas, sítios e atividades produtivas com alto consumo.</p>
      <span class="haja-servico-btn">Saiba Mais</span>
    </a>
    <a class="haja-servico-card" href="$site/homologacao-para-integradores/">
      <span class="haja-servico-icon" aria-hidden="true">{$icons['doc']}</span>
      <h3>Homologação</h3>
      <span class="haja-servico-line"></span>
      <p>Projetos, documentação e suporte técnico para integradores.</p>
      <span class="haja-servico-btn">Saiba Mais</span>
    </a>
  </div>
</section>
HTML;

$patterns = [
    '#<style>\s*#haja-servicos-principais.*?</style>\s*<section id="haja-servicos-principais">.*?</section>#s',
    '#<section class="haja-band" id="haja-servicos-principais">.*?</section>\s*<style>.*?</style>#s',
    '#<section id="haja-servicos-principais">.*?</section>#s',
];

$newContent = $content;
foreach ($patterns as $pattern) {
    $replaced = preg_replace($pattern, $services, $newContent, 1);
    if ($replaced !== null && $replaced !== $newContent) {
        $newContent = $replaced;
        break;
    }
}

if ($newContent === $content) {
    throw new RuntimeException('Could not replace services section');
}

$now = date('Y-m-d H:i:s');
$stmt = $mysqli->prepare("UPDATE wp_posts SET post_content=?, post_modified=?, post_modified_gmt=UTC_TIMESTAMP() WHERE ID=?");
$stmt->bind_param('ssi', $newContent, $now, $homeId);
$stmt->execute();
$stmt->close();

echo "Services rebuilt exactly.\n";
