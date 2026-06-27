<?php
$m = new mysqli('127.0.0.1', 'root', '', 'wordpress_local');
$m->set_charset('utf8mb4');
$c = $m->query("SELECT post_content FROM wp_posts WHERE post_type='page' AND post_name='home' LIMIT 1")->fetch_assoc()['post_content'] ?? '';
$count = preg_match_all('/<div class="haja-card"><h3>(.*?)<\/h3>.*?<a class="haja-btn primary" href="([^"]+)">Saiba Mais<\/a>/s', $c, $matches);
echo 'service_buttons=' . $count . PHP_EOL;
foreach ($matches[1] as $i => $title) {
    echo html_entity_decode(strip_tags($title)) . ' => ' . $matches[2][$i] . PHP_EOL;
}
echo 'service_css=' . substr_count($c, 'haja-service-links-css') . PHP_EOL;