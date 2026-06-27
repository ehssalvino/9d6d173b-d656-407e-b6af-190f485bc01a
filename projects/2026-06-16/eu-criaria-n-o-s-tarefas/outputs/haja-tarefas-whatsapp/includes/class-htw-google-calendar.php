<?php

if (!defined('ABSPATH')) {
    exit;
}

class HTW_Google_Calendar
{
    public function create_event_for_task($task_id, $intent)
    {
        $access_token = $this->access_token();
        $calendar_id = get_option('htw_google_calendar_id', 'primary');

        if (empty($access_token) || empty($intent['start_at'])) {
            return new WP_Error('htw_calendar_not_ready', 'Google Agenda ainda nao esta configurado.');
        }

        $start = gmdate('c', strtotime($intent['start_at']));
        $end = gmdate('c', strtotime($intent['start_at'] . ' +1 hour'));

        $response = wp_remote_post(
            sprintf('https://www.googleapis.com/calendar/v3/calendars/%s/events', rawurlencode($calendar_id)),
            array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $access_token,
                    'Content-Type' => 'application/json',
                ),
                'body' => wp_json_encode(array(
                    'summary' => $intent['title'],
                    'description' => 'Criado pelo Haja Tarefas WhatsApp. Tarefa #' . $task_id,
                    'start' => array('dateTime' => $start),
                    'end' => array('dateTime' => $end),
                )),
                'timeout' => 15,
            )
        );

        if (is_wp_error($response)) {
            return $response;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        if (wp_remote_retrieve_response_code($response) >= 300) {
            return new WP_Error('htw_calendar_api_error', isset($body['error']['message']) ? $body['error']['message'] : 'Erro ao criar evento.');
        }

        return isset($body['id']) ? $body['id'] : '';
    }

    private function access_token()
    {
        $access_token = get_option('htw_google_access_token', '');
        $expires_at = (int) get_option('htw_google_token_expires_at', 0);

        if ($access_token && $expires_at > time()) {
            return $access_token;
        }

        $refresh_token = get_option('htw_google_refresh_token', '');
        $client_id = get_option('htw_google_client_id', '');
        $client_secret = get_option('htw_google_client_secret', '');

        if (!$refresh_token || !$client_id || !$client_secret) {
            return '';
        }

        $response = wp_remote_post('https://oauth2.googleapis.com/token', array(
            'body' => array(
                'client_id' => $client_id,
                'client_secret' => $client_secret,
                'refresh_token' => $refresh_token,
                'grant_type' => 'refresh_token',
            ),
            'timeout' => 15,
        ));

        if (is_wp_error($response)) {
            return '';
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        if (empty($body['access_token'])) {
            return '';
        }

        update_option('htw_google_access_token', sanitize_text_field($body['access_token']), false);
        update_option('htw_google_token_expires_at', time() + absint($body['expires_in'] ?? 3600) - 60, false);

        return $body['access_token'];
    }
}
