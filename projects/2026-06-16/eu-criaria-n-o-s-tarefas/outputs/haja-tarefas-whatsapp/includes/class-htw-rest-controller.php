<?php

if (!defined('ABSPATH')) {
    exit;
}

class HTW_REST_Controller
{
    private $repository;
    private $calendar;
    private $parser;

    public function __construct(HTW_Task_Repository $repository, HTW_Google_Calendar $calendar, HTW_Command_Parser $parser)
    {
        $this->repository = $repository;
        $this->calendar = $calendar;
        $this->parser = $parser;
    }

    public function init()
    {
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    public function register_routes()
    {
        register_rest_route('haja-tarefas/v1', '/whatsapp', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'verify_webhook'),
                'permission_callback' => '__return_true',
            ),
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'receive_whatsapp'),
                'permission_callback' => '__return_true',
            ),
        ));

        register_rest_route('haja-tarefas/v1', '/tasks/(?P<id>\d+)/status', array(
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => array($this, 'update_status'),
            'permission_callback' => function () {
                return current_user_can('manage_options');
            },
        ));
    }

    public function verify_webhook(WP_REST_Request $request)
    {
        $token = get_option('htw_whatsapp_verify_token', '');
        $mode = $request->get_param('hub_mode') ?: $request->get_param('hub.mode');
        $challenge = $request->get_param('hub_challenge') ?: $request->get_param('hub.challenge');
        $verify_token = $request->get_param('hub_verify_token') ?: $request->get_param('hub.verify_token');

        if ('subscribe' === $mode && hash_equals((string) $token, (string) $verify_token)) {
            return new WP_REST_Response($challenge, 200);
        }

        return new WP_REST_Response('Token invalido', 403);
    }

    public function receive_whatsapp(WP_REST_Request $request)
    {
        $payload = $request->get_json_params();
        $message = $this->extract_message($payload);

        if (empty($message['body'])) {
            return rest_ensure_response(array(
                'ok' => true,
                'message' => 'Mensagem recebida, mas sem texto processavel. Audio precisa de transcricao.',
            ));
        }

        $intent = $this->parser->parse($message['body']);
        $task_id = $this->repository->create_task(array_merge($intent, array('source' => 'whatsapp')));
        $calendar_result = null;

        if (!empty($intent['needs_calendar'])) {
            $calendar_result = $this->calendar->create_event_for_task($task_id, $intent);
        }

        $this->store_message($message, $intent, $task_id, is_wp_error($calendar_result) ? $calendar_result->get_error_message() : '');

        return rest_ensure_response(array(
            'ok' => true,
            'task_id' => $task_id,
            'intent' => $intent,
            'calendar' => is_wp_error($calendar_result) ? array('ok' => false, 'error' => $calendar_result->get_error_message()) : array('ok' => !empty($calendar_result), 'event_id' => $calendar_result),
            'reply' => $this->build_reply($intent, $calendar_result),
        ));
    }

    public function update_status(WP_REST_Request $request)
    {
        $status = sanitize_key($request->get_param('status'));
        $allowed = array_keys(HTW_Task_Repository::statuses());

        if (!in_array($status, $allowed, true)) {
            return new WP_Error('htw_invalid_status', 'Status invalido.', array('status' => 400));
        }

        $ok = $this->repository->update_task_status((int) $request['id'], $status, (string) $request->get_param('blocked_reason'));
        return rest_ensure_response(array('ok' => $ok));
    }

    private function extract_message($payload)
    {
        $message = array('from' => '', 'type' => 'text', 'body' => '');

        $entry = isset($payload['entry'][0]['changes'][0]['value']) ? $payload['entry'][0]['changes'][0]['value'] : array();
        $raw = isset($entry['messages'][0]) ? $entry['messages'][0] : array();

        if ($raw) {
            $message['from'] = isset($raw['from']) ? sanitize_text_field($raw['from']) : '';
            $message['type'] = isset($raw['type']) ? sanitize_key($raw['type']) : 'text';
            $message['body'] = isset($raw['text']['body']) ? sanitize_textarea_field($raw['text']['body']) : '';
        }

        return $message;
    }

    private function store_message($message, $intent, $task_id, $error = '')
    {
        global $wpdb;

        $wpdb->insert(
            $wpdb->prefix . 'htw_messages',
            array(
                'provider' => 'whatsapp',
                'message_type' => $message['type'],
                'from_number' => $message['from'],
                'body' => $message['body'],
                'intent_json' => wp_json_encode($intent),
                'created_task_id' => $task_id,
                'status' => $error ? 'calendar_pending' : 'processed',
                'error' => $error,
                'created_at' => current_time('mysql'),
            ),
            array('%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s')
        );
    }

    private function build_reply($intent, $calendar_result)
    {
        $reply = sprintf('Criado em %s -> %s. Prioridade: %s.', $intent['project'], $intent['status'], $intent['priority']);

        if (!empty($intent['needs_calendar']) && !is_wp_error($calendar_result)) {
            return $reply . ' Evento criado no Google Agenda.';
        }

        if (!empty($intent['needs_calendar']) && is_wp_error($calendar_result)) {
            return $reply . ' Nao consegui criar no Google Agenda ainda: ' . $calendar_result->get_error_message();
        }

        return $reply . ' Quer agendar um bloco ou so lembrete?';
    }
}
