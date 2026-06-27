<?php

if (!defined('ABSPATH')) {
    exit;
}

class HTW_Task_Repository
{
    public function projects()
    {
        global $wpdb;

        return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}htw_projects ORDER BY name ASC", ARRAY_A);
    }

    public function find_project_by_name($name)
    {
        global $wpdb;

        $slug = sanitize_title($name);

        return $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$wpdb->prefix}htw_projects WHERE slug = %s LIMIT 1", $slug),
            ARRAY_A
        );
    }

    public function ensure_project($name)
    {
        global $wpdb;

        $name = trim((string) $name);
        if ('' === $name) {
            $name = 'Pessoal';
        }

        $project = $this->find_project_by_name($name);
        if ($project) {
            return $project;
        }

        $wpdb->insert(
            $wpdb->prefix . 'htw_projects',
            array(
                'name' => $name,
                'slug' => sanitize_title($name),
                'created_at' => current_time('mysql'),
            ),
            array('%s', '%s', '%s')
        );

        return $this->find_project_by_name($name);
    }

    public function create_task($data)
    {
        global $wpdb;

        $project = !empty($data['project']) ? $this->ensure_project($data['project']) : $this->ensure_project('Pessoal');
        $now = current_time('mysql');

        $wpdb->insert(
            $wpdb->prefix . 'htw_tasks',
            array(
                'project_id' => isset($project['id']) ? (int) $project['id'] : null,
                'epic_id' => !empty($data['epic_id']) ? (int) $data['epic_id'] : null,
                'parent_id' => !empty($data['parent_id']) ? (int) $data['parent_id'] : null,
                'title' => sanitize_text_field($data['title']),
                'description' => isset($data['description']) ? wp_kses_post($data['description']) : null,
                'status' => isset($data['status']) ? sanitize_key($data['status']) : 'backlog',
                'priority' => isset($data['priority']) ? sanitize_key($data['priority']) : 'media',
                'due_at' => !empty($data['due_at']) ? gmdate('Y-m-d H:i:s', strtotime($data['due_at'])) : null,
                'start_at' => !empty($data['start_at']) ? gmdate('Y-m-d H:i:s', strtotime($data['start_at'])) : null,
                'recurrence' => !empty($data['recurrence']) ? sanitize_text_field($data['recurrence']) : null,
                'labels' => !empty($data['labels']) ? sanitize_text_field(implode(',', (array) $data['labels'])) : null,
                'source' => !empty($data['source']) ? sanitize_key($data['source']) : 'manual',
                'created_at' => $now,
                'updated_at' => $now,
            ),
            array('%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
        );

        return (int) $wpdb->insert_id;
    }

    public function update_task_status($task_id, $status, $blocked_reason = '')
    {
        global $wpdb;

        $data = array(
            'status' => sanitize_key($status),
            'updated_at' => current_time('mysql'),
        );
        $formats = array('%s', '%s');

        if ('bloqueado' === $status) {
            $data['blocked_reason'] = wp_kses_post($blocked_reason);
            $formats[] = '%s';
        }

        if ('concluido' === $status) {
            $data['completed_at'] = current_time('mysql');
            $formats[] = '%s';
        }

        return false !== $wpdb->update(
            $wpdb->prefix . 'htw_tasks',
            $data,
            array('id' => (int) $task_id),
            $formats,
            array('%d')
        );
    }

    public function tasks_grouped_by_status()
    {
        global $wpdb;

        $rows = $wpdb->get_results(
            "SELECT t.*, p.name AS project_name
             FROM {$wpdb->prefix}htw_tasks t
             LEFT JOIN {$wpdb->prefix}htw_projects p ON p.id = t.project_id
             ORDER BY COALESCE(t.due_at, '9999-12-31') ASC, t.id DESC",
            ARRAY_A
        );

        $grouped = array();
        foreach (self::statuses() as $key => $label) {
            $grouped[$key] = array();
        }

        foreach ($rows as $row) {
            $status = isset($grouped[$row['status']]) ? $row['status'] : 'backlog';
            $grouped[$status][] = $row;
        }

        return $grouped;
    }

    public static function statuses()
    {
        return array(
            'backlog' => 'Backlog',
            'hoje' => 'Hoje',
            'em_andamento' => 'Em andamento',
            'bloqueado' => 'Bloqueado',
            'concluido' => 'Concluido',
        );
    }
}
