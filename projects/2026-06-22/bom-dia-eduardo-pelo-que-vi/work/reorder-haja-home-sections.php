<?php
$mysqli = new mysqli('127.0.0.1', 'root', '', 'wordpress_local');
if ($mysqli->connect_errno) {
    fwrite(STDERR, $mysqli->connect_error . PHP_EOL);
    exit(1);
}
$mysqli->set_charset('utf8mb4');

$site = 'http://localhost/hajageracaosolar';
$wa = 'https://api.whatsapp.com/send?phone=5521969022250';
$bg = $site . '/wp-content/uploads/2024/07/mou-brazil-solar-1.webp';

$row = $mysqli->query("SELECT ID, post_content FROM wp_posts WHERE post_type='page' AND post_name='home' LIMIT 1")->fetch_assoc();
if (!$row) {
    throw new RuntimeException('Home not found');
}
$homeId = (int) $row['ID'];
$content = $row['post_content'];

$oldSections = <<<HTML
<section class="haja-band">
  <div class="haja-wrap">
    <div class="haja-head">
      <h2>Infinidade de serviços e produtos</h2>
      <p>Soluções sustentáveis e inovadoras para todas as suas necessidades energéticas.</p>
    </div>
    <div class="haja-grid">
      <a class="haja-card" href="$site/energia-solar-residencial/">
        <h3>Energia Solar Residencial</h3>
        <p>A sua residência com sistema fotovoltaico dimensionado para reduzir a conta de energia com segurança.</p>
        <span class="haja-btn primary">Saiba Mais</span>
      </a>
      <a class="haja-card" href="$site/energia-solar-comercial/">
        <h3>Energia Solar Comercial</h3>
        <p>Projetos para empresas que querem reduzir custo fixo e proteger margem com geração própria.</p>
        <span class="haja-btn primary">Saiba Mais</span>
      </a>
      <a class="haja-card" href="$site/energia-solar-rural/">
        <h3>Energia Solar Rural</h3>
        <p>Soluções para fazendas, sítios e propriedades produtivas com alto consumo de energia.</p>
        <span class="haja-btn primary">Saiba Mais</span>
      </a>
      <a class="haja-card" href="$site/homologacao-para-integradores/">
        <h3>Homologação</h3>
        <p>Projetos, documentação e suporte técnico para integradores em processos de homologação.</p>
        <span class="haja-btn primary">Saiba Mais</span>
      </a>
    </div>
  </div>
</section>
<section class="haja-band alt">
  <div class="haja-wrap haja-split">
    <div>
      <div class="haja-head">
        <h2>Nossa visão</h2>
        <p>Ser reconhecida como uma empresa de engenharia solar capaz de entregar economia, segurança elétrica e soluções completas para residências, empresas, propriedades rurais e integradores.</p>
      </div>
      <div class="haja-head">
        <h2>Nossa missão</h2>
        <p>Projetar, viabilizar e instalar sistemas fotovoltaicos com qualidade técnica, responsabilidade e foco em resultado financeiro para cada cliente.</p>
      </div>
    </div>
    <img class="haja-photo" src="$bg" alt="Sistema de energia solar">
  </div>
</section>
<section class="haja-band">
  <div class="haja-wrap">
    <div class="haja-head">
      <h2>Benefícios da energia fotovoltaica</h2>
      <p>Energia solar é uma decisão técnica e financeira: reduz custos, aumenta previsibilidade e valoriza o imóvel.</p>
    </div>
    <div class="haja-grid">
      <div class="haja-card"><h3>Economia na conta</h3><p>Redução de até 95% no gasto mensal com energia, conforme o perfil de consumo e dimensionamento.</p></div>
      <div class="haja-card"><h3>Projeto seguro</h3><p>Sistema dimensionado com atenção à instalação elétrica, proteção, homologação e desempenho.</p></div>
      <div class="haja-card"><h3>Valorização</h3><p>Imóveis e empresas com geração própria ganham previsibilidade e maior atratividade.</p></div>
      <div class="haja-card"><h3>Energia limpa</h3><p>Uso de fonte renovável com menor impacto ambiental e maior independência energética.</p></div>
    </div>
  </div>
</section>
<section class="haja-band alt">
  <div class="haja-wrap">
    <div class="haja-head">
      <h2>F.A.Qs</h2>
      <p>Respostas rápidas para as dúvidas mais comuns antes de solicitar um estudo solar.</p>
    </div>
    <div class="haja-grid">
      <div class="haja-card"><h3>Energia solar vale a pena?</h3><p>Sim, principalmente para quem tem consumo recorrente e quer reduzir custo de energia no médio e longo prazo.</p></div>
      <div class="haja-card"><h3>Precisa homologar?</h3><p>Sim. A homologação junto à distribuidora regulariza o sistema para conexão à rede.</p></div>
      <div class="haja-card"><h3>Posso financiar?</h3><p>Existem linhas de crédito para energia solar, sujeitas à análise e às condições de cada instituição.</p></div>
      <div class="haja-card"><h3>Como começo?</h3><p>Envie sua conta de energia para a Haja avaliar consumo, potência recomendada e economia estimada.</p></div>
    </div>
  </div>
</section>
HTML;

$stepsMarker = '<section class="haja-band alt"><div class="haja-wrap"><div class="haja-head"><h2>Como funciona</h2>';
$stepsStart = strpos($content, $stepsMarker);
if ($stepsStart === false) {
    throw new RuntimeException('Como funciona section not found');
}

$statsMarker = '<section class="haja-band alt">';
$statsStart = strpos($content, $statsMarker);
if ($statsStart === false) {
    throw new RuntimeException('Stats section not found');
}
$statsEnd = strpos($content, '</section>', $statsStart);
if ($statsEnd === false) {
    throw new RuntimeException('Stats section end not found');
}
$statsEnd += strlen('</section>');

$newContent = substr($content, 0, $statsEnd) . $oldSections . substr($content, $stepsStart);

$stmt = $mysqli->prepare("UPDATE wp_posts SET post_content=?, post_modified=NOW(), post_modified_gmt=UTC_TIMESTAMP() WHERE ID=?");
$stmt->bind_param('si', $newContent, $homeId);
$stmt->execute();
$stmt->close();

echo "Home sections reordered and old-style service block restored.\n";
