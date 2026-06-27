<?php
$mysqli = new mysqli('127.0.0.1', 'root', '', 'wordpress_local');
if ($mysqli->connect_errno) {
    fwrite(STDERR, $mysqli->connect_error . PHP_EOL);
    exit(1);
}
$mysqli->set_charset('utf8mb4');
$post_id = (int)($argv[1] ?? 3551);
$stmt = $mysqli->prepare("SELECT meta_value FROM wp_postmeta WHERE post_id=? AND meta_key='_elementor_data'");
$stmt->bind_param('i', $post_id);
$stmt->execute();
$stmt->bind_result($data);
$stmt->fetch();
$stmt->close();

$nodes = json_decode($data, true);
if (!is_array($nodes)) {
    fwrite(STDERR, "Invalid or empty Elementor JSON\n");
    exit(1);
}

function clean_text($value): string {
    $value = html_entity_decode(strip_tags((string)$value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return trim(preg_replace('/\s+/u', ' ', $value));
}

function walk_nodes(array $nodes, array $path = []): void {
    foreach ($nodes as $idx => $node) {
        $widget = $node['widgetType'] ?? ($node['elType'] ?? 'unknown');
        $settings = $node['settings'] ?? [];
        $bits = [];
        foreach ($settings as $key => $value) {
            if (is_scalar($value) && preg_match('/(title|heading|subtitle|description|editor|text|button|label|html)/i', $key)) {
                $clean = clean_text($value);
                if ($clean !== '' && mb_strlen($clean) < 500) {
                    $bits[] = "{$key}: {$clean}";
                }
            }
        }
        if ($bits) {
            echo implode('.', array_merge($path, [$idx])) . " | {$widget} | " . implode(' || ', $bits) . PHP_EOL;
        }
        if (!empty($node['elements']) && is_array($node['elements'])) {
            walk_nodes($node['elements'], array_merge($path, [$idx]));
        }
    }
}

walk_nodes($nodes);
