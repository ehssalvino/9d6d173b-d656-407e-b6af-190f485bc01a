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

$content = $row['post_content'];
$replacement = <<<'HTML'
<section class="haja-band"><div class="haja-wrap"><div class="haja-head"><h2>Simule sua economia solar</h2><p>Preencha a calculadora da Haja e veja uma estimativa de economia, potência, geração e investimento sem sair da página.</p></div>[si_solar_calculator mode="public"]</div></section><section class="haja-band alt"><div class="haja-wrap"><div class="haja-head"><h2>O que você recebe no estudo</h2><p>Estimativa de economia anual, potência recomendada, caminho de homologação, possibilidade de financiamento e próximos passos para instalar com segurança.</p></div><a class="haja-btn primary" href="https://api.whatsapp.com/send?phone=5521969022250">Solicitar Estudo Gratuito</a></div></section>
HTML;

$pattern = '/<section class="haja-band"><div class="haja-wrap haja-split"><div class="haja-simulator">\s*<h2>Simule sua economia solar<\/h2>.*?<h2>O que você recebe no estudo<\/h2>.*?<\/a><\/div><\/div><\/section>/s';
$new = preg_replace($pattern, $replacement, $content, 1, $count);
if (!$count) {
    throw new RuntimeException('Home simulator split section not found');
}

$stmt = $mysqli->prepare("UPDATE wp_posts SET post_content=?, post_modified=NOW(), post_modified_gmt=UTC_TIMESTAMP() WHERE ID=?");
$id = (int) $row['ID'];
$stmt->bind_param('si', $new, $id);
$stmt->execute();
$stmt->close();

echo "Public calculator embedded on Home.\n";