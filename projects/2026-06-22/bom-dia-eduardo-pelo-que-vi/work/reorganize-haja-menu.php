<?php
$mysqli = new mysqli('127.0.0.1', 'root', '', 'wordpress_local');
if ($mysqli->connect_errno) {
    fwrite(STDERR, $mysqli->connect_error . PHP_EOL);
    exit(1);
}
$mysqli->set_charset('utf8mb4');

$menuName = 'Menu-header';
$stmt = $mysqli->prepare("SELECT tt.term_taxonomy_id FROM wp_terms t JOIN wp_term_taxonomy tt ON t.term_id=tt.term_id WHERE t.name=? AND tt.taxonomy='nav_menu' LIMIT 1");
$stmt->bind_param('s', $menuName);
$stmt->execute();
$stmt->bind_result($termTaxonomyId);
if (!$stmt->fetch()) {
    throw new RuntimeException('Menu not found');
}
$stmt->close();

$stmt = $mysqli->prepare("
    SELECT p.ID
    FROM wp_posts p
    JOIN wp_term_relationships tr ON tr.object_id=p.ID
    JOIN wp_postmeta pm ON pm.post_id=p.ID AND pm.meta_key='_menu_item_url'
    WHERE p.post_type='nav_menu_item'
      AND tr.term_taxonomy_id=?
      AND p.post_title='Serviços'
    LIMIT 1
");
$stmt->bind_param('i', $termTaxonomyId);
$stmt->execute();
$stmt->bind_result($servicesId);
if (!$stmt->fetch()) {
    throw new RuntimeException('Serviços menu item not found');
}
$stmt->close();
$servicesId = (int)$servicesId;

$submenuTitles = [
    'Energia Residencial',
    'Energia Comercial',
    'Energia Rural',
    'Homologação',
];

$order = 1;
foreach ($submenuTitles as $title) {
    $stmt = $mysqli->prepare("
        SELECT p.ID
        FROM wp_posts p
        JOIN wp_term_relationships tr ON tr.object_id=p.ID
        WHERE p.post_type='nav_menu_item'
          AND tr.term_taxonomy_id=?
          AND p.post_title=?
        LIMIT 1
    ");
    $stmt->bind_param('is', $termTaxonomyId, $title);
    $stmt->execute();
    $stmt->bind_result($id);
    if ($stmt->fetch()) {
        $stmt->close();
        $id = (int)$id;
        $update = $mysqli->prepare("UPDATE wp_posts SET menu_order=? WHERE ID=?");
        $update->bind_param('ii', $order, $id);
        $update->execute();
        $update->close();

        $meta = $mysqli->prepare("UPDATE wp_postmeta SET meta_value=? WHERE post_id=? AND meta_key='_menu_item_menu_item_parent'");
        $parent = (string)$servicesId;
        $meta->bind_param('si', $parent, $id);
        $meta->execute();
        if ($meta->affected_rows === 0) {
            $insert = $mysqli->prepare("INSERT INTO wp_postmeta (post_id, meta_key, meta_value) VALUES (?, '_menu_item_menu_item_parent', ?)");
            $insert->bind_param('is', $id, $parent);
            $insert->execute();
            $insert->close();
        }
        $meta->close();
    } else {
        $stmt->close();
    }
    $order++;
}

$stmt = $mysqli->prepare("
    SELECT p.ID
    FROM wp_posts p
    JOIN wp_term_relationships tr ON tr.object_id=p.ID
    WHERE p.post_type='nav_menu_item'
      AND tr.term_taxonomy_id=?
      AND p.post_title='Simulador'
");
$stmt->bind_param('i', $termTaxonomyId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $id = (int)$row['ID'];
    $mysqli->query("DELETE FROM wp_term_relationships WHERE object_id={$id} AND term_taxonomy_id={$termTaxonomyId}");
    $mysqli->query("UPDATE wp_posts SET post_status='trash' WHERE ID={$id}");
}
$stmt->close();

echo "Menu reorganized.\n";
