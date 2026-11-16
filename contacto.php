<?php
declare(strict_types=1);

// Contact form handler. POST only. Works with a plain form post (303 redirect to /#enviado or /#error)
// and with fetch (Accept: application/json).

$config = require is_file(__DIR__ . '/config.php') ? __DIR__ . '/config.php' : __DIR__ . '/config.example.php';

const SEGUROS = ['auto' => 'Auto', 'vida' => 'Vida', 'salud' => 'Salud', 'no-se' => 'Aún no lo sabe'];

$wantsJson = str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');

function finish(bool $ok, int $status, bool $wantsJson, string $reason = ''): never
{
    if ($wantsJson) {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode($ok ? ['ok' => true] : ['ok' => false, 'error' => $reason]);
    } else {
        $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/\\');
        header('Location: ' . $base . '/#' . ($ok ? 'enviado' : 'error'), true, 303);
    }
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    if (!$wantsJson) {
        header('Location: ' . rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/\\') . '/', true, 303);
        exit;
    }
    finish(false, 405, $wantsJson, 'method');
}

// Same-origin check (browsers always send Origin on cross-site POSTs).
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin !== '') {
    $host = parse_url($origin, PHP_URL_HOST);
    if (!is_string($host) || !in_array(strtolower($host), $config['allowed_hosts'], true)) {
        finish(false, 403, $wantsJson, 'origin');
    }
}

if ((int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 4096) {
    finish(false, 413, $wantsJson, 'size');
}

// Bots: filled honeypot, or submitted faster than a person can (only when the JS timestamp is present).
// Answer success so they learn nothing, and store nothing.
$trap = trim((string)($_POST['sitio'] ?? ''));
$t = $_POST['t'] ?? '';
$tooFast = is_string($t) && ctype_digit($t) && (time() - intdiv((int)$t, 1000)) < 3;
if ($trap !== '' || $tooFast) {
    finish(true, 200, $wantsJson);
}

// Validate.
$nombre = preg_replace('/[\x00-\x1F\x7F]+/u', ' ', trim((string)($_POST['nombre'] ?? ''))) ?? '';
$nombre = trim($nombre);
$telRaw = trim((string)($_POST['telefono'] ?? ''));
$digits = preg_replace('/\D/', '', $telRaw) ?? '';
$seguro = (string)($_POST['seguro'] ?? '');

$len = mb_strlen($nombre);
if ($len < 2 || $len > 80
    || !preg_match('/^[0-9 +().-]{8,20}$/', $telRaw) || strlen($digits) < 8 || strlen($digits) > 15
    || !array_key_exists($seguro, SEGUROS)) {
    finish(false, 422, $wantsJson, 'invalid');
}

// Rate limit per hashed IP. Only valid submissions count, so typos never lock anyone out.
$ipHash = hash('sha256', $_SERVER['REMOTE_ADDR'] ?? 'unknown');
$rateDir = $config['rate_dir'];
if (!is_dir($rateDir)) {
    @mkdir($rateDir, 0700, true);
}
$rateFile = $rateDir . '/' . $ipHash;
$now = time();
$fh = @fopen($rateFile, 'c+');
if ($fh !== false && flock($fh, LOCK_EX)) {
    $hits = array_filter(array_map('intval', explode("\n", (string)stream_get_contents($fh))),
        fn(int $ts): bool => $ts > $now - $config['rate_window']);
    if (count($hits) >= $config['rate_max']) {
        flock($fh, LOCK_UN);
        fclose($fh);
        finish(false, 429, $wantsJson, 'rate');
    }
    $hits[] = $now;
    ftruncate($fh, 0);
    rewind($fh);
    fwrite($fh, implode("\n", $hits));
    flock($fh, LOCK_UN);
}
if ($fh !== false) {
    fclose($fh);
}

// Store the lead (no IP, no user agent) and email it.
$saved = false;
$lead = json_encode([
    'ts'       => gmdate('c'),
    'nombre'   => $nombre,
    'seguro'   => $seguro,
    'telefono' => $telRaw,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$dir = dirname($config['leads_path']);
if (!is_dir($dir)) {
    @mkdir($dir, 0700, true);
}
if ($lead !== false && @file_put_contents($config['leads_path'], $lead . "\n", FILE_APPEND | LOCK_EX) !== false) {
    $saved = true;
}

$mailed = false;
if ($config['send_mail']) {
    $subject = '=?UTF-8?B?' . base64_encode('Nueva solicitud de cotización (gpoaura.com)') . '?=';
    $body = "Nueva solicitud desde gpoaura.com\r\n\r\n"
        . "Nombre: $nombre\r\n"
        . 'Seguro: ' . SEGUROS[$seguro] . "\r\n"
        . "Teléfono: $telRaw\r\n";
    $headers = 'From: aura seguros <' . $config['from'] . ">\r\n"
        . "MIME-Version: 1.0\r\n"
        . "Content-Type: text/plain; charset=UTF-8\r\n"
        . 'Content-Transfer-Encoding: 8bit';
    $mailed = @mail($config['recipient'], $subject, $body, $headers);
}

// Success if the lead is stored or emailed; failing both is the only error the visitor sees.
finish($saved || $mailed, ($saved || $mailed) ? 200 : 500, $wantsJson, 'server');
