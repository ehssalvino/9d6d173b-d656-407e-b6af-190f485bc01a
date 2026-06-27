<?php
/**
 * Plugin Name: Haja Home Static Renderer
 * Description: Renders the local Haja home page without relying on Elementor Pro widgets.
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

add_shortcode('haja_home_static', function () {
    $main = get_option('haja_home_static_main_html', '');

    if (!$main) {
        return '';
    }

    $logo = home_url('/wp-content/uploads/2023/06/Logotipo6.png');

    ob_start();
    ?>
    <style id="haja-home-static-css">
        .haja-site-header{position:sticky;top:0;z-index:1000;background:#fff;border-bottom:1px solid rgba(8,49,46,.10);box-shadow:0 8px 24px rgba(8,49,46,.06)}
        .haja-header-inner{max-width:1160px;margin:0 auto;min-height:86px;padding:10px 22px;display:flex;align-items:center;justify-content:space-between;gap:22px}
        .haja-site-logo{display:flex;align-items:center;text-decoration:none;flex:0 0 auto}
        .haja-site-logo img{display:block;width:auto;height:66px;max-width:260px;object-fit:contain}
        .haja-site-nav{display:flex;align-items:center;justify-content:flex-end;gap:22px;flex-wrap:wrap}
        .haja-site-nav a{color:#16302d;text-decoration:none;font-weight:800;font-size:14px;line-height:1.2}
        .haja-site-nav a:hover{color:#008481}
        .haja-site-nav .nav-cta{background:#008481;color:#fff;padding:12px 16px;border-radius:6px}
        .haja-site-nav .nav-cta:hover{background:#f4ab17;color:#102926}
        .haja-page{font-family:Montserrat,Arial,sans-serif;color:#16302d;background:#fff}
        .haja-page *{box-sizing:border-box}
        .haja-wrap{max-width:1160px;margin:0 auto;padding:0 22px}
        .haja-hero{position:relative;min-height:680px;display:flex;align-items:center;color:#fff;background-size:cover;background-position:center right;overflow:hidden}
        .haja-hero:before{content:"";position:absolute;inset:0 auto 0 0;width:36%;min-width:390px;background:rgba(244,151,43,.88);z-index:0}
        .haja-hero .haja-wrap{position:relative;z-index:1;margin-left:0;max-width:520px;padding-left:54px;padding-right:34px}
        .haja-hero-kicker{font-size:13px;text-transform:uppercase;letter-spacing:.08em;font-weight:900;color:#fff;margin:0 0 14px}
        .haja-hero h1,.haja-page h1{max-width:880px;font-size:clamp(36px,5vw,68px);line-height:1.02;margin:0 0 22px;font-weight:900;letter-spacing:0}
        .haja-hero p,.haja-page p{line-height:1.6}
        .haja-hero p{max-width:720px;font-size:20px;margin:0 0 28px;color:#eef7f3}
        .haja-actions{display:flex;gap:14px;flex-wrap:wrap}
        .haja-btn{display:inline-flex;align-items:center;justify-content:center;min-height:48px;padding:14px 22px;border-radius:6px;text-decoration:none;font-weight:800;border:2px solid transparent}
        .haja-btn-primary{background:#f4ab17;color:#102926}
        .haja-btn-secondary{background:rgba(255,255,255,.08);border-color:#fff;color:#fff}
        .haja-band{padding:72px 0}
        .haja-band.alt{background:#f6faf8}
        .haja-head{max-width:760px;margin:0 0 34px}
        .haja-head h2,.haja-page h2{font-size:clamp(28px,3vw,42px);line-height:1.12;margin:0 0 12px;color:#008481}
        .haja-head p{font-size:18px;color:#52615f;margin:0}
        .haja-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:18px}
        .haja-card{background:#fff;border:1px solid #dfe9e6;border-radius:8px;padding:24px;box-shadow:0 10px 26px rgba(9,40,37,.06);text-decoration:none}
        .haja-card h3{font-size:21px;margin:0 0 10px;color:#16302d}
        .haja-card p,.haja-card li{color:#52615f;line-height:1.55}
        .haja-stat strong{display:block;font-size:34px;color:#f4ab17;line-height:1}
        .haja-stat span{display:block;margin-top:8px;font-weight:700;color:#16302d}
        .haja-steps{counter-reset:step;display:grid;grid-template-columns:repeat(5,1fr);gap:14px}
        .haja-step{background:#fff;border-left:4px solid #f4ab17;padding:20px;border-radius:8px}
        .haja-step:before{counter-increment:step;content:counter(step);display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:50%;background:#008481;color:#fff;font-weight:900;margin-bottom:14px}
        .haja-case{overflow:hidden;padding:0}
        .haja-case img{width:100%;height:210px;object-fit:cover;display:block}
        .haja-case div{padding:22px}
        .haja-simulator{background:#08312e;color:#fff;border-radius:8px;padding:32px}
        .haja-simulator h2{color:#fff;margin-top:0}
        .haja-simulator p{color:#d8ebe6}
        .haja-form{display:grid;gap:14px}
        .haja-form label{display:grid;gap:6px;font-weight:800;color:#fff}
        .haja-form input,.haja-form select{min-height:46px;border:1px solid #bdd3ce;border-radius:6px;padding:10px 12px;font-size:16px}
        .haja-result{display:none;background:#fff;color:#16302d;border-radius:8px;padding:18px;margin-top:16px}
        .haja-result strong{color:#008481;font-size:26px}
        .haja-final{background:#008481;color:#fff;text-align:center}
        .haja-final h2{color:#fff;font-size:clamp(30px,4vw,50px);margin:0 0 16px}
        .haja-final p{max-width:760px;margin:0 auto 24px;color:#e8fffb}
        .haja-split{display:grid;grid-template-columns:1.1fr .9fr;gap:32px;align-items:center}
        .haja-photo{width:100%;border-radius:8px;display:block}
        @media (max-width:920px){.haja-grid{grid-template-columns:repeat(2,1fr)}.haja-steps{grid-template-columns:repeat(2,1fr)}.haja-split{grid-template-columns:1fr}.haja-hero{min-height:620px}}
        @media (max-width:760px){.haja-header-inner{align-items:flex-start;flex-direction:column}.haja-site-nav{justify-content:flex-start;gap:14px}.haja-site-logo img{height:50px}.haja-hero:before{width:100%;min-width:0}.haja-hero .haja-wrap{padding-left:24px}.haja-grid,.haja-steps{grid-template-columns:1fr}.haja-hero h1,.haja-page h1{font-size:36px}.haja-band{padding:54px 0}}
    </style>
    <header class="haja-site-header">
        <div class="haja-header-inner">
            <a class="haja-site-logo" href="<?php echo esc_url(home_url('/')); ?>" aria-label="Haja Geração Solar">
                <img src="<?php echo esc_url($logo); ?>" alt="Haja Geração Solar">
            </a>
            <nav class="haja-site-nav" aria-label="Menu principal">
                <a href="<?php echo esc_url(home_url('/')); ?>">Home</a>
                <a href="<?php echo esc_url(home_url('/residencial/')); ?>">Residencial</a>
                <a href="<?php echo esc_url(home_url('/comercial/')); ?>">Comercial</a>
                <a href="<?php echo esc_url(home_url('/rural/')); ?>">Rural</a>
                <a href="<?php echo esc_url(home_url('/homologacao-para-integradores/')); ?>">Homologação</a>
                <a class="nav-cta" href="#simulador">Simular economia</a>
            </nav>
        </div>
    </header>
    <?php
    echo do_shortcode($main);

    return ob_get_clean();
});
