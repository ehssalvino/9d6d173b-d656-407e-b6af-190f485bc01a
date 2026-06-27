<?php
$mysqli = new mysqli('127.0.0.1', 'root', '', 'wordpress_local');
if ($mysqli->connect_errno) {
    fwrite(STDERR, $mysqli->connect_error . PHP_EOL);
    exit(1);
}
$mysqli->set_charset('utf8mb4');

$menuName = 'Menu-header';
$stmt = $mysqli->prepare("
    SELECT p.ID
    FROM wp_posts p
    JOIN wp_term_relationships tr ON tr.object_id=p.ID
    JOIN wp_term_taxonomy tt ON tr.term_taxonomy_id=tt.term_taxonomy_id
    JOIN wp_terms t ON t.term_id=tt.term_id
    WHERE p.post_type='nav_menu_item'
      AND p.post_status='publish'
      AND p.post_title='Serviços'
      AND t.name=?
    LIMIT 1
");
$stmt->bind_param('s', $menuName);
$stmt->execute();
$stmt->bind_result($servicesId);
if (!$stmt->fetch()) {
    throw new RuntimeException('Serviços menu item not found');
}
$stmt->close();
$servicesId = (int)$servicesId;

$stmt = $mysqli->prepare("UPDATE wp_postmeta SET meta_value='#' WHERE post_id=? AND meta_key='_menu_item_url'");
$stmt->bind_param('i', $servicesId);
$stmt->execute();
$stmt->close();

$css = <<<'CSS'

/* Haja: show Serviços submenu in Elementor header */
.elementor-nav-menu--main .menu-item-has-children {
    position: relative;
}
.elementor-nav-menu--main .menu-item-has-children > .sub-menu,
.elementor-nav-menu--main .menu-item-has-children > .elementor-nav-menu--dropdown {
    display: none;
    opacity: 0;
    visibility: hidden;
    position: absolute;
    top: 100%;
    left: 0;
    z-index: 9999;
    min-width: 240px;
    background: #ffffff;
    box-shadow: 0 16px 34px rgba(9, 40, 37, 0.16);
    border-radius: 6px;
    padding: 8px 0;
}
.elementor-nav-menu--main .menu-item-has-children:hover > .sub-menu,
.elementor-nav-menu--main .menu-item-has-children:focus-within > .sub-menu,
.elementor-nav-menu--main .menu-item-has-children:hover > .elementor-nav-menu--dropdown,
.elementor-nav-menu--main .menu-item-has-children:focus-within > .elementor-nav-menu--dropdown {
    display: block;
    opacity: 1;
    visibility: visible;
}
.elementor-nav-menu--main .menu-item-has-children > .sub-menu a {
    display: block;
    color: #16302d !important;
    padding: 12px 18px !important;
    white-space: nowrap;
}
.elementor-nav-menu--main .menu-item-has-children > .sub-menu a:hover,
.elementor-nav-menu--main .menu-item-has-children > .sub-menu a:focus {
    color: #008481 !important;
    background: #f6faf8;
}
CSS;

$postName = 'hello-elementor';
$stmt = $mysqli->prepare("SELECT ID, post_content FROM wp_posts WHERE post_type='custom_css' AND post_name=? ORDER BY ID DESC LIMIT 1");
$stmt->bind_param('s', $postName);
$stmt->execute();
$stmt->bind_result($cssPostId, $content);
$found = $stmt->fetch();
$stmt->close();

if (!$found) {
    throw new RuntimeException('hello-elementor custom CSS post not found');
}

if (strpos($content, 'Haja: show Serviços submenu') === false) {
    $content .= $css;
} else {
    $content = preg_replace('/\/\* Haja: show Serviços submenu \*\/.*$/s', trim($css), $content);
}

$now = date('Y-m-d H:i:s');
$stmt = $mysqli->prepare("UPDATE wp_posts SET post_content=?, post_modified=?, post_modified_gmt=UTC_TIMESTAMP() WHERE ID=?");
$stmt->bind_param('ssi', $content, $now, $cssPostId);
$stmt->execute();
$stmt->close();

echo "Services dropdown fixed.\n";
