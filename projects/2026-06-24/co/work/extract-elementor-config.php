<?php
$html = file_get_contents(__DIR__ . '/elementor-editor-response.html');

$keys = array('previewUrl', 'ajaxurl', 'document', 'initial_document', 'urls', 'wpRestApi');
foreach ($keys as $key) {
    if (preg_match_all('/"' . preg_quote($key, '/') . '"\s*:\s*("[^"]*"|\{.*?\}|\[.*?\]|[^,}\n]+)/s', $html, $matches)) {
        foreach (array_slice($matches[0], 0, 5) as $m) {
            echo $key . ': ' . substr($m, 0, 500) . PHP_EOL;
        }
    }
}

if (preg_match_all('/https?:\\\\?\/\\\\?\/localhost[^"\'<> ]+/i', $html, $matches)) {
    $urls = array_unique(array_map(function ($u) {
        return stripcslashes(html_entity_decode($u, ENT_QUOTES));
    }, $matches[0]));
    foreach (array_slice($urls, 0, 80) as $url) {
        if (stripos($url, 'preview') !== false || stripos($url, 'elementor') !== false || stripos($url, 'rest') !== false || stripos($url, 'admin-ajax') !== false) {
            echo 'URL ' . $url . PHP_EOL;
        }
    }
}
