<?php
$mysqli = new mysqli('127.0.0.1', 'root', '', 'wordpress_local');
$mysqli->set_charset('utf8mb4');
$content = $mysqli->query("SELECT post_content FROM wp_posts WHERE post_type='page' AND post_name='home' LIMIT 1")->fetch_assoc()['post_content'] ?? '';
$sectionStart = strpos($content, '<section id="haja-servicos-principais">');
$sectionEnd = strpos($content, '</section>', $sectionStart);
$section = ($sectionStart !== false && $sectionEnd !== false) ? substr($content, $sectionStart, $sectionEnd - $sectionStart) : '';
echo 'actual_cards=' . substr_count($section, '<a class="haja-servico-card"') . PHP_EOL;
echo 'actual_frames=' . substr_count($section, '<span class="haja-icon-frame"') . PHP_EOL;
echo 'actual_buttons=' . substr_count($section, 'haja-servico-btn') . PHP_EOL;
echo 'css_blocks=' . substr_count($content, 'haja-services-third-figure-css') . PHP_EOL;
echo 'sections=' . substr_count($content, '<section id="haja-servicos-principais">') . PHP_EOL;
