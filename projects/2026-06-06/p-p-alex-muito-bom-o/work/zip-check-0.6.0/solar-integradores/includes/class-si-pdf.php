<?php

if (!defined('ABSPATH')) {
    exit;
}

final class SI_PDF
{
    public static function generate(array $quote, array $input, array $result)
    {
        $autoload = SI_DIR . 'vendor/autoload.php';
        if (!is_readable($autoload)) {
            return new WP_Error('pdf_library_missing', __('A biblioteca de PDF não está instalada.', 'solar-integradores'));
        }
        require_once $autoload;

        $uploads = wp_upload_dir();
        if (!empty($uploads['error'])) {
            return new WP_Error('pdf_upload_error', $uploads['error']);
        }

        $directory = trailingslashit($uploads['basedir']) . 'solar-integradores/propostas';
        if (!wp_mkdir_p($directory)) {
            return new WP_Error('pdf_directory_error', __('Não foi possível criar a pasta de propostas.', 'solar-integradores'));
        }

        $filename = 'proposta-' . sanitize_file_name($quote['public_token']) . '.pdf';
        $path = trailingslashit($directory) . $filename;
        $url = trailingslashit($uploads['baseurl']) . 'solar-integradores/propostas/' . $filename;

        $proposal = $result['proposal'] ?? array();
        ob_start();
        include SI_DIR . 'templates/proposal-pdf.php';
        $html = ob_get_clean();

        try {
            $options = new Dompdf\Options();
            $options->set('defaultFont', 'DejaVu Sans');
            $options->set('isRemoteEnabled', false);
            $options->set('isHtml5ParserEnabled', true);
            $dompdf = new Dompdf\Dompdf($options);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->render();
            $written = file_put_contents($path, $dompdf->output(), LOCK_EX);
        } catch (Throwable $exception) {
            return new WP_Error('pdf_render_error', $exception->getMessage());
        }

        if (!$written || !is_readable($path)) {
            return new WP_Error('pdf_write_error', __('Não foi possível salvar a proposta em PDF.', 'solar-integradores'));
        }

        return array(
            'path' => $path,
            'url' => $url,
            'download_url' => add_query_arg('si_quote_pdf', $quote['public_token'], home_url('/')),
            'filename' => $filename,
        );
    }

    public static function existing($token)
    {
        $uploads = wp_upload_dir();
        $filename = 'proposta-' . sanitize_file_name($token) . '.pdf';
        return array(
            'path' => trailingslashit($uploads['basedir']) . 'solar-integradores/propostas/' . $filename,
            'url' => trailingslashit($uploads['baseurl']) . 'solar-integradores/propostas/' . $filename,
            'download_url' => add_query_arg('si_quote_pdf', $token, home_url('/')),
            'filename' => $filename,
        );
    }
}
