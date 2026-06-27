<?php
$html = file_get_contents(__DIR__ . '/elementor-editor-response.html');
preg_match_all('/<(?:script|link|img)\b[^>]+(?:src|href)=["\']([^"\']+)["\']/i', $html, $matches);
$urls = array_unique($matches[1]);
foreach ($urls as $url) {
    $url = html_entity_decode($url, ENT_QUOTES);
    if (strpos($url, 'http') === 0 && strpos($url, 'http://localhost/') !== 0) {
        echo $url . PHP_EOL;
    }
}
