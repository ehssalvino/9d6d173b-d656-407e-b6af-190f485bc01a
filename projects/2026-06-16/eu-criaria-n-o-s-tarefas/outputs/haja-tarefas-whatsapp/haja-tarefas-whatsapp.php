<?php
/**
 * Plugin Name: Haja Tarefas WhatsApp
 * Description: Mini-Jira pessoal via WhatsApp com painel Kanban e preparacao para Google Calendar.
 * Version: 0.3.2
 * Author: Haja Geracao Solar
 * Text Domain: haja-tarefas-whatsapp
 */

if (!defined('ABSPATH')) {
    exit;
}

define('HTW_VERSION', '0.3.2');
define('HTW_FILE', __FILE__);
define('HTW_DIR', plugin_dir_path(__FILE__));
define('HTW_URL', plugin_dir_url(__FILE__));

require_once HTW_DIR . 'includes/class-htw-activator.php';
require_once HTW_DIR . 'includes/class-htw-command-parser.php';
require_once HTW_DIR . 'includes/class-htw-task-repository.php';
require_once HTW_DIR . 'includes/class-htw-google-calendar.php';
require_once HTW_DIR . 'includes/class-htw-rest-controller.php';
require_once HTW_DIR . 'includes/class-htw-admin.php';
require_once HTW_DIR . 'includes/class-htw-plugin.php';

register_activation_hook(__FILE__, array('HTW_Activator', 'activate'));

add_action('plugins_loaded', function () {
    HTW_Plugin::instance()->init();
});





