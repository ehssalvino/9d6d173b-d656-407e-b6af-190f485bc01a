<?php
/**
 * Plugin Name: Solar Integradores
 * Description: Calculadora fotovoltaica pública e área de integradores com dimensionamento residencial e comercial.
 * Version: 0.6.0
 * Author: Haja Geração Solar
 * Text Domain: solar-integradores
 * Requires at least: 6.4
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

define('SI_VERSION', '0.6.0');
define('SI_FILE', __FILE__);
define('SI_DIR', plugin_dir_path(__FILE__));
define('SI_URL', plugin_dir_url(__FILE__));

require_once SI_DIR . 'includes/class-si-calculator.php';
require_once SI_DIR . 'includes/class-si-proposal.php';
require_once SI_DIR . 'includes/class-si-pdf.php';
require_once SI_DIR . 'includes/class-si-plugin.php';

register_activation_hook(__FILE__, array('SI_Plugin', 'activate'));
register_deactivation_hook(__FILE__, array('SI_Plugin', 'deactivate'));

SI_Plugin::instance();
