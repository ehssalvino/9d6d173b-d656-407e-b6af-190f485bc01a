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

$replacement = <<<'HTML'
<div class="haja-simulator">
  <h2>Simule sua economia solar</h2>
  <p>Preencha os dados principais e continue na calculadora solar completa da Haja.</p>
  <form class="haja-form" action="/hajageracaosolar/calculadora-solar/" method="get">
    <label>Valor médio da conta de energia
      <input type="number" min="100" step="10" name="bill" placeholder="Ex.: 1200" required>
    </label>
    <label>Cidade
      <input type="text" name="city" placeholder="Ex.: Niterói" required>
    </label>
    <label>Tipo de imóvel
      <select name="kind"><option value="residential">Residencial</option><option value="commercial">Comercial</option><option value="rural">Rural</option></select>
    </label>
    <button class="haja-btn primary" type="submit">Continuar na calculadora</button>
  </form>
</div>
HTML;

$content = $row['post_content'];
$pattern = '/<div class="haja-simulator">\s*<h2>Simule sua economia solar<\/h2>.*?<\/div>\s*<script>\s*document\.addEventListener\(\'DOMContentLoaded\'.*?<\/script>/s';
$new = preg_replace($pattern, $replacement, $content, 1, $count);
if (!$count) {
    throw new RuntimeException('Home simulator block not found');
}

$stmt = $mysqli->prepare("UPDATE wp_posts SET post_content=?, post_modified=NOW(), post_modified_gmt=UTC_TIMESTAMP() WHERE ID=?");
$id = (int) $row['ID'];
$stmt->bind_param('si', $new, $id);
$stmt->execute();
$stmt->close();

echo "Home simulator connected to public calculator.\n";