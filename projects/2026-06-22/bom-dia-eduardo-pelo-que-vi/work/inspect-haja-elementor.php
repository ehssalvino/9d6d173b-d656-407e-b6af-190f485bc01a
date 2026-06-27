<?php
require 'C:/xampp/htdocs/hajageracaosolar/wp-load.php';

$post_id = (int)($argv[1] ?? 3551);
$data = get_post_meta($post_id, '_elementor_data', true);
if (!$data) {
    fwrite(STDERR, "No Elementor data for post {$post_id}\n");
    exit(1);
}

$nodes = json_decode($data, true);
if (!is_array($nodes)) {
    fwrite(STDERR, "Invalid Elementor JSON\n");
    exit(1);
}

function walk_nodes(array $nodes, array $path = []): void {
    foreach ($nodes as $idx => $node) {
        $widget = $node['widgetType'] ?? ($node['elType'] ?? 'unknown');
        $settings = $node['settings'] ?? [];
        $bits = [];
        foreach (['title', 'subtitle', 'description', 'editor', 'text', 'button_text', 'selected_icon', 'link'] as $key) {
            if (isset($settings[$key]) && is_scalar($settings[$key])) {
                $value = trim(wp_strip_all_tags((string)$settings[$key]));
                if ($value !== '') {
                    $bits[] = "{$key}: " . preg_replace('/\s+/', ' ', $value);
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
