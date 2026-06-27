<?php
require 'D:/xampp/htdocs/hajageracaosolar/wp-load.php';

function walk_elementor($nodes, &$out) {
    foreach ($nodes as $node) {
        if (!empty($node['settings']) && is_array($node['settings'])) {
            foreach ($node['settings'] as $key => $value) {
                if (is_array($value)) {
                    if (!empty($value['url'])) {
                        $out[] = array(
                            'node' => $node['id'] ?? '',
                            'type' => $node['widgetType'] ?? $node['elType'] ?? '',
                            'key' => $key,
                            'url' => $value['url'],
                            'id' => $value['id'] ?? '',
                        );
                    }
                } elseif (is_string($value) && preg_match('~https?://|/wp-content/uploads/~', $value)) {
                    $out[] = array(
                        'node' => $node['id'] ?? '',
                        'type' => $node['widgetType'] ?? $node['elType'] ?? '',
                        'key' => $key,
                        'url' => $value,
                        'id' => '',
                    );
                }
            }
        }
        if (!empty($node['elements']) && is_array($node['elements'])) {
            walk_elementor($node['elements'], $out);
        }
    }
}

$out = array();
foreach (array(3553, 3570) as $id) {
    $data = json_decode(get_post_meta($id, '_elementor_data', true), true);
    if (is_array($data)) {
        walk_elementor($data, $out);
    }
}

foreach ($out as $item) {
    echo implode("\t", array($item['node'], $item['type'], $item['key'], $item['id'], $item['url'])) . PHP_EOL;
}
