<?php

if (!defined('ABSPATH')) {
    exit;
}

class HTW_Command_Parser
{
    public function parse($text)
    {
        $text = trim(wp_strip_all_tags((string) $text));
        $plain = function_exists('mb_strtolower') ? mb_strtolower(remove_accents($text), 'UTF-8') : strtolower(remove_accents($text));

        $intent = array(
            'action' => 'create_task',
            'title' => $text,
            'project' => 'Pessoal',
            'priority' => 'media',
            'status' => 'backlog',
            'due_at' => null,
            'start_at' => null,
            'needs_calendar' => false,
            'raw' => $text,
        );

        if (preg_match('/projeto\s+([^:,]+?)(:|,|\s+ate\s+|\s+prioridade\s+|$)/i', $plain, $matches)) {
            $intent['project'] = trim($matches[1]);
        }

        if (preg_match('/prioridade\s+(baixa|media|alta|urgente)/i', $plain, $matches)) {
            $intent['priority'] = sanitize_key($matches[1]);
        }

        if (false !== strpos($plain, 'hoje')) {
            $intent['status'] = 'hoje';
            $intent['due_at'] = current_time('Y-m-d') . ' 18:00:00';
        }

        if (preg_match('/\bas?\s+([0-2]?[0-9])h([0-5][0-9])?\b/i', $plain, $matches)) {
            $hour = max(0, min(23, (int) $matches[1]));
            $minute = isset($matches[2]) && '' !== $matches[2] ? (int) $matches[2] : 0;
            $intent['start_at'] = current_time('Y-m-d') . sprintf(' %02d:%02d:00', $hour, $minute);
            $intent['due_at'] = $intent['start_at'];
            $intent['status'] = 'hoje';
            $intent['needs_calendar'] = true;
        }

        if (preg_match('/\bate\s+(segunda|terca|quarta|quinta|sexta|sabado|domingo|amanha)\b/i', $plain, $matches)) {
            $intent['due_at'] = $this->relative_date($matches[1]) . ' 18:00:00';
        }

        $title = $this->extract_title($text);
        if ($title) {
            $intent['title'] = $title;
        }

        return apply_filters('htw_parsed_command', $intent, $text);
    }

    private function relative_date($word)
    {
        $word = sanitize_key(remove_accents(strtolower($word)));
        if ('amanha' === $word) {
            return gmdate('Y-m-d', strtotime('+1 day', current_time('timestamp')));
        }

        $map = array(
            'domingo' => 'sunday',
            'segunda' => 'monday',
            'terca' => 'tuesday',
            'quarta' => 'wednesday',
            'quinta' => 'thursday',
            'sexta' => 'friday',
            'sabado' => 'saturday',
        );

        $target = isset($map[$word]) ? $map[$word] : 'today';
        return gmdate('Y-m-d', strtotime('next ' . $target, current_time('timestamp')));
    }

    private function extract_title($text)
    {
        $clean = preg_replace('/^cria(r)?\s+(uma\s+)?tarefa\s+/iu', '', $text);

        if (preg_match('/^no\s+projeto\s+[^:]+:\s*(.+)$/iu', $clean, $matches)) {
            $clean = $matches[1];
        }

        if (preg_match('/^(.+?)\s+no\s+projeto\s+.+$/iu', $clean, $matches)) {
            $clean = $matches[1];
        }

        $clean = preg_replace('/\s+prioridade\s+(baixa|m(?:e|\x{00E9})dia|alta|urgente).*$/iu', '', $clean);
        $clean = preg_replace('/\s+at(?:e|\x{00E9})\s+.+$/iu', '', $clean);

        return trim($clean);
    }
}
