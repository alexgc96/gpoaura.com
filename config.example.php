<?php
// Copy to config.php (gitignored) and edit. Never commit the real file.
// Without a config.php, contacto.php falls back to this file, so local runs only log leads and send no mail.
return [
    // Where lead emails go. Set the real inbox in config.php on the server.
    'recipient'   => 'leads@example.com',
    'from'        => 'noreply@gpoaura.com',

    // false = only write the lead log (local dev). true = also send the email.
    'send_mail'   => false,

    // One JSON line per lead. On the server keep this OUTSIDE the web root, e.g. /var/lib/gpoaura/leads.jsonl
    'leads_path'  => __DIR__ . '/leads/leads.jsonl',

    // Per-IP rate limit (hashed IPs, no raw addresses stored).
    'rate_dir'    => sys_get_temp_dir() . '/gpoaura-rate',
    'rate_max'    => 5,
    'rate_window' => 3600,

    // Hosts allowed in the Origin header of a form POST.
    'allowed_hosts' => ['gpoaura.com', 'www.gpoaura.com', 'localhost', '127.0.0.1'],
];
