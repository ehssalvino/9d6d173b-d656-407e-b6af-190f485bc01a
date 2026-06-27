<?php
$mysqli = new mysqli('127.0.0.1', 'root', '', 'wordpress_local');
if ($mysqli->connect_errno) {
    fwrite(STDERR, $mysqli->connect_error . PHP_EOL);
    exit(1);
}
$mysqli->set_charset('utf8mb4');

$row = $mysqli->query("SELECT ID FROM wp_posts WHERE post_type='page' AND post_name='calculadora-solar' LIMIT 1")->fetch_assoc();
if (!$row) {
    throw new RuntimeException('Calculator page not found');
}

$content = <<<'HTML'
<style>
.haja-calc-page{font-family:Montserrat,Arial,sans-serif;color:#16302d;background:#fff}.haja-calc-page *{box-sizing:border-box}.haja-calc-wrap{max-width:1160px;margin:0 auto;padding:0 22px}.haja-calc-hero{padding:74px 0 34px;background:#f6faf8}.haja-calc-kicker{font-size:13px;text-transform:uppercase;letter-spacing:.08em;font-weight:800;color:#f4a51c;margin:0 0 12px}.haja-calc-page h1{max-width:860px;font-size:clamp(34px,4vw,58px);line-height:1.06;margin:0 0 18px;font-weight:900;color:#008481;letter-spacing:0}.haja-calc-lead{max-width:760px;font-size:19px;line-height:1.6;margin:0;color:#52615f}.haja-calc-body{padding:44px 0 80px}.haja-calc-plugin{background:#fff;border:1px solid #dfe9e6;border-radius:8px;padding:24px;box-shadow:0 12px 30px rgba(9,40,37,.07)}@media(max-width:640px){.haja-calc-hero{padding:54px 0 28px}.haja-calc-body{padding:32px 0 60px}.haja-calc-plugin{padding:16px}}
</style>
<main class="haja-calc-page">
  <section class="haja-calc-hero">
    <div class="haja-calc-wrap">
      <p class="haja-calc-kicker">Simulador de economia solar</p>
      <h1>Simule quanto você pode economizar com energia solar</h1>
      <p class="haja-calc-lead">Preencha os dados da sua conta de energia para receber uma estimativa inicial. Depois, a Haja faz um diagnóstico gratuito com projeto, homologação e opções de financiamento.</p>
    </div>
  </section>
  <section class="haja-calc-body">
    <div class="haja-calc-wrap">
      <div class="haja-calc-plugin">[solar-calculator]</div>
    </div>
  </section>
</main>
HTML;

$id = (int) $row['ID'];
$stmt = $mysqli->prepare("UPDATE wp_posts SET post_content=?, post_modified=NOW(), post_modified_gmt=UTC_TIMESTAMP() WHERE ID=?");
$stmt->bind_param('si', $content, $id);
$stmt->execute();
$stmt->close();

echo "Calculator shortcode restored on page ID {$id}.\n";