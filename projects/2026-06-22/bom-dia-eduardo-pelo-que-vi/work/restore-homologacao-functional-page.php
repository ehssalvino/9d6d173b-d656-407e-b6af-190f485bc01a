<?php
$mysqli = new mysqli('127.0.0.1', 'root', '', 'wordpress_local');
if ($mysqli->connect_errno) {
    fwrite(STDERR, $mysqli->connect_error . PHP_EOL);
    exit(1);
}
$mysqli->set_charset('utf8mb4');

$row = $mysqli->query("SELECT ID FROM wp_posts WHERE post_type='page' AND post_name='homologacao-para-integradores' LIMIT 1")->fetch_assoc();
if (!$row) {
    throw new RuntimeException('Homologacao page not found');
}

$content = <<<'HTML'
<style>
.haja-page{font-family:Montserrat,Arial,sans-serif;color:#16302d;background:#fff}.haja-page *{box-sizing:border-box}.haja-wrap{max-width:1160px;margin:0 auto;padding:0 22px}.haja-hero{position:relative;min-height:620px;display:flex;align-items:center;color:#fff;background-size:cover;background-position:center}.haja-hero:before{content:"";position:absolute;inset:0;background:linear-gradient(90deg,rgba(8,34,32,.88),rgba(8,34,32,.62),rgba(8,34,32,.30))}.haja-hero .haja-wrap{position:relative}.haja-kicker{font-size:13px;text-transform:uppercase;letter-spacing:.08em;font-weight:800;color:#ffc44d;margin:0 0 14px}.haja-hero h1{max-width:920px;font-size:clamp(36px,5vw,66px);line-height:1.03;margin:0 0 22px;font-weight:900;letter-spacing:0}.haja-hero p{max-width:760px;font-size:20px;line-height:1.55;margin:0 0 28px;color:#eef7f3}.haja-actions{display:flex;gap:14px;flex-wrap:wrap}.haja-btn{display:inline-flex;align-items:center;justify-content:center;min-height:48px;padding:14px 22px;border-radius:6px;text-decoration:none;font-weight:800;border:2px solid transparent}.haja-btn.primary{background:#f4ab17;color:#102926}.haja-btn.secondary{background:rgba(255,255,255,.08);border-color:#fff;color:#fff}.haja-band{padding:72px 0}.haja-band.alt{background:#f6faf8}.haja-head{max-width:820px;margin:0 0 34px}.haja-head h2{font-size:clamp(28px,3vw,42px);line-height:1.12;margin:0 0 12px;color:#008481}.haja-head p{font-size:18px;color:#52615f;margin:0;line-height:1.6}.haja-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:18px}.haja-card{background:#fff;border:1px solid #dfe9e6;border-radius:8px;padding:24px;box-shadow:0 10px 26px rgba(9,40,37,.06)}.haja-card h3{font-size:21px;margin:0 0 10px;color:#16302d}.haja-card p{color:#52615f;line-height:1.55;margin:0}.haja-functional-box{background:#fff;border:1px solid #dfe9e6;border-radius:8px;padding:28px;box-shadow:0 18px 44px rgba(9,40,37,.08)}@media(max-width:900px){.haja-grid{grid-template-columns:1fr}.haja-hero{min-height:560px}.haja-functional-box{padding:18px}}
</style>
<main class="haja-page">
  <section class="haja-hero" style="background-image:url('http://localhost/hajageracaosolar/wp-content/uploads/2024/07/mou-brazil-solar-1.webp')">
    <div class="haja-wrap">
      <p class="haja-kicker">Homologação para integradores</p>
      <h1>Projetos solares e homologação para integradores em todo o Brasil</h1>
      <p>Use a calculadora pronta para estimar o valor do projeto, enviar a solicitação e registrar o lead no fluxo de homologação da Haja.</p>
      <div class="haja-actions"><a class="haja-btn primary" href="#calculadora-homologacao">Calcular projeto</a><a class="haja-btn secondary" href="https://api.whatsapp.com/send?phone=5521969022250">Falar no WhatsApp</a></div>
    </div>
  </section>
  <section class="haja-band alt">
    <div class="haja-wrap">
      <div class="haja-head"><h2>Engenharia que apoia o integrador</h2><p>A Haja cuida de documentação, laudos, padrão de entrada e acompanhamento técnico para projetos que precisam andar com segurança.</p></div>
      <div class="haja-grid"><div class="haja-card"><h3>Dimensionamento e análise</h3><p>Informe potência em kWp ou quantidade de módulos para estimar o valor do serviço.</p></div><div class="haja-card"><h3>Dados da concessionária</h3><p>Registre estado, distribuidora, padrão de entrada e disjuntor do local.</p></div><div class="haja-card"><h3>Lead e acompanhamento</h3><p>O formulário envia a solicitação e mantém o fluxo pronto para orçamento e atendimento.</p></div></div>
    </div>
  </section>
  <section id="calculadora-homologacao" class="haja-band">
    <div class="haja-wrap">
      <div class="haja-head"><h2>Calcule e solicite a homologação</h2><p>Este é o formulário funcional já existente no sistema. Preencha os dados do projeto e envie a solicitação.</p></div>
      <div class="haja-functional-box">[hp_calculadora_homologacao]</div>
    </div>
  </section>
</main>
HTML;

$id = (int) $row['ID'];
$stmt = $mysqli->prepare("UPDATE wp_posts SET post_content=?, post_modified=NOW(), post_modified_gmt=UTC_TIMESTAMP() WHERE ID=?");
$stmt->bind_param('si', $content, $id);
$stmt->execute();
$stmt->close();

echo "Homologacao functional page restored.\n";