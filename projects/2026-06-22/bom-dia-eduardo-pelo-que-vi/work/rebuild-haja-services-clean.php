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

$icons = [
    'home' => '<svg viewBox="0 0 24 24"><path d="M3 11.5 12 4l9 7.5"/><path d="M5.5 10.5V20h13v-9.5"/><path d="M9.5 20v-5.5h5V20"/></svg>',
    'commercial' => '<svg viewBox="0 0 24 24"><path d="M4 21h16"/><path d="M6 21V6h12v15"/><path d="M9 9h2M13 9h2M9 13h2M13 13h2"/><path d="M10 21v-4h4v4"/></svg>',
    'rural' => '<svg viewBox="0 0 24 24"><path d="M12 21V10"/><path d="M12 10C8 10 5.2 7.7 4 3.8 8.2 4 11.1 6.1 12 10Z"/><path d="M12 14c4.2-.1 7-2.4 8-6.2-4.1.2-7 2.2-8 6.2Z"/></svg>',
    'doc' => '<svg viewBox="0 0 24 24"><path d="M7 3h7l4 4v14H7z"/><path d="M14 3v5h5"/><path d="m9.5 14 2 2 4-5"/></svg>',
];

$services = <<<HTML
<style>
#haja-servicos-principais {
    background: #fff;
    padding: 72px 0 76px;
}
#haja-servicos-principais .haja-servicos-wrap {
    max-width: 1120px;
    margin: 0 auto;
    padding: 0 22px;
}
#haja-servicos-principais .haja-servicos-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 26px;
    align-items: stretch;
}
#haja-servicos-principais .haja-servico-card {
    box-sizing: border-box;
    display: flex;
    flex-direction: column;
    align-items: center;
    min-height: 360px;
    padding: 28px 22px 26px;
    border: 1px solid #f4ab17;
    background: #fff;
    color: #222;
    text-align: center;
    text-decoration: none;
    box-shadow: none;
    border-radius: 0;
}
#haja-servicos-principais .haja-servico-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 74px;
    height: 74px;
    margin: 0 0 22px;
    background: #ffa154;
    color: #fff;
}
#haja-servicos-principais .haja-servico-icon svg {
    width: 38px;
    height: 38px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2.35;
    stroke-linecap: round;
    stroke-linejoin: round;
}
#haja-servicos-principais .haja-servico-card h3 {
    margin: 0;
    color: #202020;
    font-size: 22px;
    font-weight: 700;
    line-height: 1.18;
    letter-spacing: 0;
    text-transform: none;
}
#haja-servicos-principais .haja-servico-line {
    display: block;
    width: 62px;
    height: 2px;
    margin: 14px auto 22px;
    background: #ffa154;
}
#haja-servicos-principais .haja-servico-card p {
    max-width: 220px;
    margin: 0 auto 24px;
    color: #666;
    font-size: 15px;
    font-weight: 400;
    line-height: 1.6;
}
#haja-servicos-principais .haja-servico-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 42px;
    min-width: 124px;
    margin-top: auto;
    padding: 9px 22px;
    border: 1px solid #ff8f36;
    background: #fff;
    color: #ff8f36;
    font-size: 15px;
    font-weight: 700;
    line-height: 1;
}
#haja-servicos-principais .haja-servico-card:hover .haja-servico-btn,
#haja-servicos-principais .haja-servico-card:focus .haja-servico-btn {
    background: #ff8f36;
    color: #fff;
}
@media (max-width: 1024px) {
    #haja-servicos-principais .haja-servicos-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}
@media (max-width: 640px) {
    #haja-servicos-principais .haja-servicos-grid {
        grid-template-columns: 1fr;
    }
}
</style>
<section id="haja-servicos-principais">
    <div class="haja-servicos-wrap">
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
                <h3>Energia Solar<br>Rural</h3>
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
    </div>
</section>
HTML;

$anchors = [
    strpos($content, '#haja-servicos-principais'),
    strpos($content, '<section id="haja-servicos-principais"'),
    strpos($content, '<section class="haja-band" id="haja-servicos-principais"'),
    strpos($content, 'haja-simple-services'),
];
$anchors = array_values(array_filter($anchors, static fn($value) => $value !== false));
if (!$anchors) {
    throw new RuntimeException('Services section not found');
}
$anchor = min($anchors);
$start = strrpos(substr($content, 0, $anchor), '<style>');
$sectionStart = strrpos(substr($content, 0, $anchor), '<section');
if ($sectionStart !== false && ($start === false || $sectionStart < $start)) {
    $start = $sectionStart;
}
if ($start === false) {
    throw new RuntimeException('Services start not found');
}

$next = strpos($content, '<section class="haja-band alt">', $anchor);
if ($next === false) {
    throw new RuntimeException('Next section not found');
}

$newContent = substr($content, 0, $start) . $services . substr($content, $next);

$now = date('Y-m-d H:i:s');
$stmt = $mysqli->prepare("UPDATE wp_posts SET post_content=?, post_modified=?, post_modified_gmt=UTC_TIMESTAMP() WHERE ID=?");
$stmt->bind_param('ssi', $newContent, $now, $homeId);
$stmt->execute();
$stmt->close();

echo "Clean services section rebuilt.\n";
