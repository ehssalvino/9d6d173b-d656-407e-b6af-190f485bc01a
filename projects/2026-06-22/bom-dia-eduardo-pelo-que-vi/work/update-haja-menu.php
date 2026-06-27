<?php
$mysqli = new mysqli('127.0.0.1', 'root', '', 'wordpress_local');
if ($mysqli->connect_errno) {
    fwrite(STDERR, $mysqli->connect_error . PHP_EOL);
    exit(1);
}
$mysqli->set_charset('utf8mb4');

$site = 'http://localhost/hajageracaosolar';
$menuName = 'Menu-header';
$stmt = $mysqli->prepare("SELECT tt.term_taxonomy_id FROM wp_terms t JOIN wp_term_taxonomy tt ON t.term_id=tt.term_id WHERE t.name=? AND tt.taxonomy='nav_menu' LIMIT 1");
$stmt->bind_param('s', $menuName);
$stmt->execute();
$stmt->bind_result($termTaxonomyId);
if (!$stmt->fetch()) {
    throw new RuntimeException('Menu not found');
}
$stmt->close();

$items = [
    ['Energia Residencial', "{$site}/energia-solar-residencial/"],
    ['Energia Comercial', "{$site}/energia-solar-comercial/"],
    ['Energia Rural', "{$site}/energia-solar-rural/"],
    ['Homologação', "{$site}/homologacao-para-integradores/"],
    ['Simulador', "{$site}/calculadora-solar/"],
];

$order = 6;
foreach ($items as [$title, $url]) {
    $check = $mysqli->prepare("SELECT p.ID FROM wp_posts p JOIN wp_postmeta pm ON pm.post_id=p.ID AND pm.meta_key='_menu_item_url' WHERE p.post_type='nav_menu_item' AND pm.meta_value=? LIMIT 1");
    $check->bind_param('s', $url);
    $check->execute();
    $check->bind_result($existing);
    $found = $check->fetch();
    $check->close();
    if ($found) {
        $id = (int)$existing;
        $stmt = $mysqli->prepare("UPDATE wp_posts SET post_title=?, post_name=?, menu_order=? WHERE ID=?");
        $slug = sanitize_title_fallback($title);
        $stmt->bind_param('ssii', $title, $slug, $order, $id);
        $stmt->execute();
        $stmt->close();
    } else {
        $now = date('Y-m-d H:i:s');
        $slug = sanitize_title_fallback($title);
        $stmt = $mysqli->prepare("INSERT INTO wp_posts (post_author, post_date, post_date_gmt, post_content, post_title, post_excerpt, post_status, comment_status, ping_status, post_name, post_modified, post_modified_gmt, post_parent, guid, menu_order, post_type, post_mime_type, comment_count) VALUES (1, ?, UTC_TIMESTAMP(), '', ?, '', 'publish', 'closed', 'closed', ?, ?, UTC_TIMESTAMP(), 0, '', ?, 'nav_menu_item', '', 0)");
        $stmt->bind_param('ssssi', $now, $title, $slug, $now, $order);
        $stmt->execute();
        $id = (int)$mysqli->insert_id;
        $stmt->close();
        $mysqli->query("INSERT INTO wp_term_relationships (object_id, term_taxonomy_id, term_order) VALUES ({$id}, {$termTaxonomyId}, 0)");
        add_meta($mysqli, $id, '_menu_item_type', 'custom');
        add_meta($mysqli, $id, '_menu_item_menu_item_parent', '0');
        add_meta($mysqli, $id, '_menu_item_object_id', (string)$id);
        add_meta($mysqli, $id, '_menu_item_object', 'custom');
        add_meta($mysqli, $id, '_menu_item_target', '');
        add_meta($mysqli, $id, '_menu_item_classes', 'a:1:{i:0;s:0:"";}');
        add_meta($mysqli, $id, '_menu_item_xfn', '');
        add_meta($mysqli, $id, '_menu_item_url', $url);
    }
    $order++;
}

function add_meta(mysqli $db, int $postId, string $key, string $value): void {
    $stmt = $db->prepare("INSERT INTO wp_postmeta (post_id, meta_key, meta_value) VALUES (?, ?, ?)");
    $stmt->bind_param('iss', $postId, $key, $value);
    $stmt->execute();
    $stmt->close();
}

function sanitize_title_fallback(string $title): string {
    $map = ['ç'=>'c','Ç'=>'c','ã'=>'a','Ã'=>'a','á'=>'a','Á'=>'a','é'=>'e','É'=>'e','í'=>'i','Í'=>'i','ó'=>'o','Ó'=>'o','ú'=>'u','Ú'=>'u'];
    $slug = strtolower(strtr($title, $map));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    return trim($slug, '-');
}

echo "Menu updated.\n";
