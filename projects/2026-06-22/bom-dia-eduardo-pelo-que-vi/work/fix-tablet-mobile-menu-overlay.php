<?php
$mysqli = new mysqli('127.0.0.1', 'root', '', 'wordpress_local');
if ($mysqli->connect_errno) {
    fwrite(STDERR, $mysqli->connect_error . PHP_EOL);
    exit(1);
}
$mysqli->set_charset('utf8mb4');

$row = $mysqli->query("SELECT ID, post_content FROM wp_posts WHERE ID=4120 LIMIT 1")->fetch_assoc();
if (!$row) {
    throw new RuntimeException('Custom CSS post not found');
}

$css = <<<'CSS'

/* Haja: mobile/tablet menu overlay above page content */
@media (max-width: 1024px) {
    .elementor-location-header,
    .elementor-3570,
    .elementor-3570 .elementor-section,
    .elementor-3570 .elementor-container,
    .elementor-3570 .elementor-column,
    .elementor-3570 .elementor-widget-wrap,
    .elementor-3570 .elementor-widget-nav-menu,
    .elementor-3570 .elementor-element.elementor-element-599183f,
    .elementor-3570 .elementor-element.elementor-element-5fa23d2 {
        overflow: visible !important;
    }

    .elementor-location-header,
    .elementor-3570,
    .elementor-3570 .elementor-element.elementor-element-599183f {
        position: relative !important;
        z-index: 100000 !important;
    }

    .elementor-3570 .elementor-element.elementor-element-bdfa2ac,
    .elementor-3570 .elementor-element.elementor-element-bdfa2ac .elementor-widget-container {
        position: relative !important;
        z-index: 100001 !important;
    }

    .elementor-3570 .elementor-element.elementor-element-bdfa2ac .elementor-menu-toggle {
        position: relative !important;
        z-index: 100004 !important;
        background: #ffffff !important;
        border-radius: 8px !important;
        box-shadow: 0 10px 24px rgba(9, 40, 37, 0.12) !important;
    }

    .elementor-3570 .elementor-element.elementor-element-bdfa2ac .elementor-nav-menu--dropdown,
    .elementor-3570 .elementor-element.elementor-element-bdfa2ac nav.elementor-nav-menu--dropdown {
        position: fixed !important;
        top: 108px !important;
        left: 0 !important;
        right: 0 !important;
        z-index: 100003 !important;
        width: 100vw !important;
        max-width: none !important;
        max-height: calc(100vh - 108px) !important;
        overflow-y: auto !important;
        margin: 0 !important;
        padding: 10px 18px 18px !important;
        background: #ffffff !important;
        border: 0 !important;
        border-top: 1px solid rgba(0, 132, 129, 0.12) !important;
        border-radius: 0 !important;
        box-shadow: 0 28px 60px rgba(9, 40, 37, 0.30) !important;
    }

    .elementor-3570 .elementor-element.elementor-element-bdfa2ac .elementor-nav-menu--dropdown ul,
    .elementor-3570 .elementor-element.elementor-element-bdfa2ac .elementor-nav-menu--dropdown li {
        width: 100% !important;
    }

    .elementor-3570 .elementor-element.elementor-element-bdfa2ac .elementor-nav-menu--dropdown a,
    .elementor-3570 .elementor-element.elementor-element-bdfa2ac .elementor-nav-menu--dropdown .elementor-item,
    .elementor-3570 .elementor-element.elementor-element-bdfa2ac .elementor-nav-menu--dropdown .elementor-sub-item {
        display: flex !important;
        align-items: center !important;
        width: 100% !important;
        min-height: 48px !important;
        padding: 13px 18px !important;
        color: #16302d !important;
        background: #ffffff !important;
        border-bottom: 1px solid rgba(0, 132, 129, 0.08) !important;
        font-size: 17px !important;
        line-height: 1.25 !important;
        white-space: normal !important;
    }

    .elementor-3570 .elementor-element.elementor-element-bdfa2ac .elementor-nav-menu--dropdown a:hover,
    .elementor-3570 .elementor-element.elementor-element-bdfa2ac .elementor-nav-menu--dropdown a:focus,
    .elementor-3570 .elementor-element.elementor-element-bdfa2ac .elementor-nav-menu--dropdown a.highlighted {
        color: #008481 !important;
        background: #f6faf8 !important;
    }

    .elementor-3570 .elementor-element.elementor-element-bdfa2ac .elementor-nav-menu--dropdown .sub-menu,
    .elementor-3570 .elementor-element.elementor-element-bdfa2ac .elementor-nav-menu--dropdown .elementor-nav-menu--dropdown {
        position: static !important;
        width: 100% !important;
        max-height: none !important;
        margin: 0 !important;
        padding: 0 !important;
        background: #f6faf8 !important;
        box-shadow: none !important;
        border: 0 !important;
        border-radius: 0 !important;
    }

    .elementor-3570 .elementor-element.elementor-element-bdfa2ac .elementor-nav-menu--dropdown .sub-menu a {
        padding-left: 32px !important;
        font-size: 15px !important;
        background: #f6faf8 !important;
    }
}
CSS;

$content = preg_replace('/\n\/\* Haja: mobile menu overlay above page content \*\/.*?(?=\n\/\*|\z)/s', '', $row['post_content']);
$content = preg_replace('/\n\/\* Haja: mobile\/tablet menu overlay above page content \*\/.*?(?=\n\/\*|\z)/s', '', $content);
$content = rtrim($content) . $css;

$stmt = $mysqli->prepare("UPDATE wp_posts SET post_content=?, post_modified=NOW(), post_modified_gmt=UTC_TIMESTAMP() WHERE ID=?");
$id = (int) $row['ID'];
$stmt->bind_param('si', $content, $id);
$stmt->execute();
$stmt->close();

echo "Tablet/mobile full overlay CSS applied.\n";