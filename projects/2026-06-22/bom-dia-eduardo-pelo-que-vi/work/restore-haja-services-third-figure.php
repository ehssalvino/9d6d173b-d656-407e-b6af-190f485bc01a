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
<style id="haja-services-third-figure-css">
#haja-servicos-principais {
    background: #fff;
    padding: 72px 0 76px;
}
#haja-servicos-principais * {
    box-sizing: border-box;
}
#haja-servicos-principais .haja-servicos-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 18px;
    width: min(100%, 920px);
    margin: 0 auto;
    padding: 0 12px;
    align-items: stretch;
}
#haja-servicos-principais .haja-servico-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    min-height: 450px;
    padding: 22px 12px 36px;
    border: 1px solid #f4ab17;
    background: #fff;
    color: #222;
    text-align: center;
    text-decoration: none;
    box-shadow: none;
    border-radius: 0;
}
#haja-servicos-principais .haja-icon-frame {
    position: relative;
    display: flex;
    justify-content: center;
    align-items: center;
    width: 166px;
    height: 76px;
    margin: 0 auto 16px;
}
#haja-servicos-principais .haja-icon-frame::before {
    content: "";
    position: absolute;
    inset: -8px -10px 8px -8px;
    border: 4px solid #050505;
    background: transparent;
    z-index: 0;
}
#haja-servicos-principais .haja-servico-icon {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 146px;
    height: 60px;
    background: #f4ab17;
    color: #fff;
}
#haja-servicos-principais .haja-servico-icon svg {
    width: 32px;
    height: 32px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2.5;
    stroke-linecap: round;
    stroke-linejoin: round;
}
#haja-servicos-principais h3 {
    margin: 0;
    color: #202020;
    font-size: 18px;
    font-weight: 800;
    line-height: 1.22;
    letter-spacing: 0;
    text-transform: none;
}
#haja-servicos-principais .haja-servico-line {
    display: block;
    width: 50px;
    height: 2px;
    margin: 13px auto 17px;
    background: #f4ab17;
}
#haja-servicos-principais p {
    max-width: 178px;
    margin: 0 auto;
    color: #555;
    font-size: 13px;
    font-weight: 400;
    line-height: 1.55;
}
#haja-servicos-principais .haja-servico-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 118px;
    min-height: 40px;
    margin-top: auto;
    padding: 8px 18px;
    border: 1px solid #f4ab17;
    background: #fff;
    color: #e98718;
    font-size: 14px;
    font-weight: 800;
    line-height: 1;
}
#haja-servicos-principais .haja-servico-card:hover .haja-servico-btn {
    background: #f4ab17;
    color: #fff;
}
@media (max-width: 1000px) {
    #haja-servicos-principais .haja-servicos-grid {
        grid-template-columns: repeat(2, 1fr);
        width: min(100%, 620px);
    }
}
@media (max-width: 640px) {
    #haja-servicos-principais .haja-servicos-grid {
        grid-template-columns: 1fr;
        width: min(100%, 260px);
    }
}
</style>
<section id="haja-servicos-principais">
    <div class="haja-servicos-grid">
        <a class="haja-servico-card" href="$site/energia-solar-residencial/">
            <span class="haja-icon-frame"><span class="haja-servico-icon" aria-hidden="true">{$icons['home']}</span></span>
            <h3>Energia Solar<br>Residencial</h3>
            <span class="haja-servico-line"></span>
            <p>Sistemas para casas e condomínios com economia mensal, valorização do imóvel e acompanhamento completo.</p>
            <span class="haja-servico-btn">Saiba Mais</span>
        </a>
        <a class="haja-servico-card" href="$site/energia-solar-comercial/">
            <span class="haja-icon-frame"><span class="haja-servico-icon" aria-hidden="true">{$icons['commercial']}</span></span>
            <h3>Energia Solar<br>Comercial</h3>
            <span class="haja-servico-line"></span>
            <p>Projetos para empresas que querem reduzir custo fixo, proteger margem e previsibilizar a conta de energia.</p>
            <span class="haja-servico-btn">Saiba Mais</span>
        </a>
        <a class="haja-servico-card" href="$site/energia-solar-rural/">
            <span class="haja-icon-frame"><span class="haja-servico-icon" aria-hidden="true">{$icons['rural']}</span></span>
            <h3>Energia Solar Rural</h3>
            <span class="haja-servico-line"></span>
            <p>Soluções para fazendas, sítios e atividades produtivas com alto consumo.</p>
            <span class="haja-servico-btn">Saiba Mais</span>
        </a>
        <a class="haja-servico-card" href="$site/homologacao-para-integradores/">
            <span class="haja-icon-frame"><span class="haja-servico-icon" aria-hidden="true">{$icons['doc']}</span></span>
            <h3>Homologação</h3>
            <span class="haja-servico-line"></span>
            <p>Projetos, documentação e suporte técnico para integradores.</p>
            <span class="haja-servico-btn">Saiba Mais</span>
        </a>
    </div>
</section>
HTML;

$next = strpos($content, '<section class="haja-band alt"><div class="haja-wrap"><div class="haja-head"><h2>Como funciona</h2>');
if ($next === false) {
    throw new RuntimeException('Como funciona section not found');
}

$statsEnd = strrpos(substr($content, 0, $next), '</section>');
if ($statsEnd === false) {
    throw new RuntimeException('Stats section end not found');
}
$statsEnd += strlen('</section>');

$newContent = substr($content, 0, $statsEnd) . $services . substr($content, $next);

$now = date('Y-m-d H:i:s');
$stmt = $mysqli->prepare("UPDATE wp_posts SET post_content=?, post_modified=?, post_modified_gmt=UTC_TIMESTAMP() WHERE ID=?");
$stmt->bind_param('ssi', $newContent, $now, $homeId);
$stmt->execute();
$stmt->close();

echo "Services replaced between stats and steps.\n";
