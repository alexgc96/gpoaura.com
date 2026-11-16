# gpoaura.com

Landing page for Aura Seguros (Grupo Aura), an insurance promotoría in Guadalajara. Courtesy add-on to Ramon Garcia's insurance portal project. It replaces the old Wix site on gpoaura.com.

Static HTML/CSS with one small PHP contact form. No build step, no dependencies.

## Run

```
php -S localhost:8000
```

Copy `config.example.php` to `config.php` and fill in the form settings (gitignored).

## Deploy

VPS (nginx + php-fpm), deployed manually by Alex. DNS is on Cloudflare (DNS only, A record to the VPS). Exact steps TBD once the server exists.

Agents: read `AGENTS.md` first.
