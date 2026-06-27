<?php
$m = new mysqli('127.0.0.1', 'root', '', 'wordpress_local');
$m->set_charset('utf8mb4');
$c = $m->query("SELECT post_content FROM wp_posts WHERE post_type='page' AND post_name='home' LIMIT 1")->fetch_assoc()['post_content'] ?? '';
foreach (['energia-solar-residencial','energia-solar-comercial','energia-solar-rural','homologacao-para-integradores'] as $slug) {
    echo $slug . '=' . substr_count($c, $slug) . PHP_EOL;
}
$count = preg_match_all('/<a class="haja-card" href="([^"]+)"[^>]*>\s*<h3>(.*?)<\/h3>/s', $c, $matches);
echo 'haja-card-links=' . $count . PHP_EOL;
foreach ($matches[1] as $i => $href) {
    echo ($i + 1) . ': ' . html_entity_decode(strip_tags($matches[2][$i])) . ' => ' . $href . PHP_EOL;
}