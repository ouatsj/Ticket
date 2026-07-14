<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Réception des signalements navigateur (403/408, burst JS) — sans auth, log fichier uniquement.
 */
class Cdnhealth extends CI_Controller
{
    public function report()
    {
        if (strtoupper($this->input->method()) !== 'POST') {
            show_404();
            return;
        }

        $config = require APPPATH . 'config/cdn_watch.php';
        $raw = file_get_contents('php://input');
        $max = isset($config['max_log_payload']) ? (int) $config['max_log_payload'] : 2048;

        if ($raw === false || strlen($raw) > $max) {
            $this->output->set_status_header(413);
            return;
        }

        $data = json_decode($raw, true);
        if (!is_array($data)) {
            $this->output->set_status_header(400);
            return;
        }

        $event = isset($data['event'])
            ? substr(preg_replace('/[^a-z0-9_]/', '', (string) $data['event']), 0, 40)
            : 'unknown';

        $entry = array(
            'event' => $event,
            'page' => isset($data['page']) ? substr((string) $data['page'], 0, 200) : '',
            'url' => isset($data['url']) ? substr((string) $data['url'], 0, 300) : '',
            'status' => isset($data['status']) ? (int) $data['status'] : 0,
            'scripts' => isset($data['scripts']) ? (int) $data['scripts'] : 0,
            'role' => isset($data['role']) ? substr((string) $data['role'], 0, 8) : '',
        );

        $line = gmdate('Y-m-d H:i:s') . ' UTC'
            . "\t" . $this->input->ip_address()
            . "\t" . json_encode($entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            . "\n";

        $log = APPPATH . 'logs/cdn_watch-' . gmdate('Y-m-d') . '.log';
        @file_put_contents($log, $line, FILE_APPEND | LOCK_EX);

        $this->output->set_status_header(204);
    }
}
