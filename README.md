# gpoaura.com

Landing page for Aura Seguros (Grupo Aura), an insurance promotoría in Guadalajara. Courtesy add-on to Ramon Garcia's insurance portal project. It replaces the old Wix site on gpoaura.com.

Static HTML/CSS with one small PHP contact form. No build step, no dependencies.

## Run

```
php -S localhost:8000
```

Without a `config.php` the form falls back to `config.example.php`: leads are written to `leads/leads.jsonl` and no mail is sent. Copy `config.example.php` to `config.php` (gitignored) to change that.

## Layout

- `index.html`, `aviso-de-privacidad.html`, `404.html`
- `css/` (`fonts.css`, `style.css`), `fonts/` (self-hosted Nunito, latin + latin-ext), `img/`, `js/main.js`
- `contacto.php`: form handler (POST only). Validates, rate-limits per hashed IP, appends a JSON line to the lead log, then emails the recipient from `config.php`
- `deploy/nginx.conf.example`: server blocks for the VPS (reference only)

## Deploy

VPS (nginx + php-fpm), deployed manually by Alex. DNS lives at the registrar (Squarespace Domains), with an A record to the VPS. Keep `leads_path` outside the web root on the server.

## Before launch

1. No placeholders left: `grep -rnE '\[[A-ZÓÍÚ]+( [A-ZÓÍÚ0-9]+)*\]' *.html` prints nothing (phone, WhatsApp, correo, dirección, horario, razón social, fecha, insurer logos), all with Ramon's approval.
2. Remove the `noindex` meta tags from the pages and the PRE-LAUNCH comments.
3. Aviso de privacidad reviewed (draft adapted from another agent's notice).
4. Real `config.php` on the server (recipient, `send_mail` true, `leads_path` outside the web root).
5. SPF and DKIM cover the VPS IP (merge into the existing Google SPF, don't replace it), and a real submission lands in the inbox.
6. Insurer-logo strip replaced with approved logos, or removed.
7. `og:image` and an `og-preview.png` are still to do.

Agents: read `AGENTS.md` first.
