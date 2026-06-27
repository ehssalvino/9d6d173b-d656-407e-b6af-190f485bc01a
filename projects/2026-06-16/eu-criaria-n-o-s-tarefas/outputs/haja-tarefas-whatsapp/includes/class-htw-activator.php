<?php

if (!defined('ABSPATH')) {
    exit;
}

class HTW_Activator
{
    public static function activate()
    {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset = $wpdb->get_charset_collate();
        $projects = $wpdb->prefix . 'htw_projects';
        $epics = $wpdb->prefix . 'htw_epics';
        $tasks = $wpdb->prefix . 'htw_tasks';
        $messages = $wpdb->prefix . 'htw_messages';

        dbDelta("CREATE TABLE $projects (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(120) NOT NULL,
            slug VARCHAR(140) NOT NULL,
            color VARCHAR(20) DEFAULT '#f4b400',
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY slug (slug)
        ) $charset;");

        dbDelta("CREATE TABLE $epics (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            project_id BIGINT UNSIGNED NOT NULL,
            title VARCHAR(220) NOT NULL,
            status VARCHAR(40) NOT NULL DEFAULT 'backlog',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NULL,
            PRIMARY KEY (id),
            KEY project_id (project_id),
            KEY status (status)
        ) $charset;");

        dbDelta("CREATE TABLE $tasks (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            project_id BIGINT UNSIGNED NULL,
            epic_id BIGINT UNSIGNED NULL,
            parent_id BIGINT UNSIGNED NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT NULL,
            status VARCHAR(40) NOT NULL DEFAULT 'backlog',
            priority VARCHAR(20) NOT NULL DEFAULT 'media',
            due_at DATETIME NULL,
            start_at DATETIME NULL,
            recurrence VARCHAR(120) NULL,
            labels TEXT NULL,
            blocked_reason TEXT NULL,
            google_event_id VARCHAR(255) NULL,
            source VARCHAR(40) NOT NULL DEFAULT 'manual',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NULL,
            completed_at DATETIME NULL,
            PRIMARY KEY (id),
            KEY project_id (project_id),
            KEY epic_id (epic_id),
            KEY parent_id (parent_id),
            KEY status (status),
            KEY due_at (due_at)
        ) $charset;");

        dbDelta("CREATE TABLE $messages (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            provider VARCHAR(40) NOT NULL DEFAULT 'whatsapp',
            message_type VARCHAR(40) NOT NULL DEFAULT 'text',
            from_number VARCHAR(80) NULL,
            body TEXT NULL,
            transcript TEXT NULL,
            intent_json LONGTEXT NULL,
            created_task_id BIGINT UNSIGNED NULL,
            status VARCHAR(40) NOT NULL DEFAULT 'received',
            error TEXT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY from_number (from_number),
            KEY status (status)
        ) $charset;");

        self::seed_projects();

        if (!get_option('htw_whatsapp_verify_token', '')) {
            update_option('htw_whatsapp_verify_token', wp_generate_password(32, false, false), false);
        }
    }

    private static function seed_projects()
    {
        global $wpdb;

        $table = $wpdb->prefix . 'htw_projects';
        $count = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table");

        if ($count > 0) {
            return;
        }

        foreach (array('Casa', 'Trabalho', 'Estudos', 'Pessoal') as $name) {
            $wpdb->insert(
                $table,
                array(
                    'name' => $name,
                    'slug' => sanitize_title($name),
                    'created_at' => current_time('mysql'),
                ),
                array('%s', '%s', '%s')
            );
        }
    }
}

