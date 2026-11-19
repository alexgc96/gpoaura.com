# gpoaura.com

Landing page for Aura Seguros, an insurance promotoría in Guadalajara, on gpoaura.com.

Static HTML/CSS/JS. The contact form builds a prefilled WhatsApp message and opens `wa.me`; there is no server-side code. No build step, no dependencies.

## Run

```
python3 -m http.server 8000
```

While `WA_NUMBER` is a placeholder the form shows an error instead of opening WhatsApp. To try the full flow locally, put a test number (digits only) in the `data-wa` attribute of the form in a scratch copy; don't commit it.

## Layout

- `index.html`, `aviso-de-privacidad.html`, `404.html`
- `css/` (`fonts.css`, `style.css`), `fonts/` (self-hosted Nunito, latin + latin-ext), `img/`, `js/main.js`
- `deploy/nginx.conf.example`: static nginx server blocks (reference only)

## Contact form

No data reaches a server of ours. `js/main.js` builds `Hola, soy <nombre>. Me gustaría cotizar un seguro de <tipo>. Detalles: <texto>. Mi correo: <correo>` (detalles, max 300 characters, and correo are optional), URL-encodes it and navigates to `https://wa.me/<data-wa>?text=...`. A honeypot field (`sitio`) makes the script do nothing when filled. The number comes from `data-wa` on the form; it is not a secret, but it stays a placeholder in the public repo until launch. Without JS the form shows a note pointing to the WhatsApp button.

## Deploy

VPS (nginx, static files only, no php-fpm needed), deployed manually. The web root is the repo contents minus dev files; see `deploy/nginx.conf.example`.

## Before launch

1. No placeholders left: `grep -rnE '\[[A-ZÓÍÚ]+( [A-ZÓÍÚ0-9]+)*\]' *.html` prints nothing (phone, WhatsApp, correo, dirección, horario, razón social, fecha, insurer logos), all with the client's approval.
2. Remove the `noindex` meta tags from the pages and the PRE-LAUNCH comments.
3. Aviso de privacidad reviewed (draft adapted from another agent's notice).
4. Insurer-logo strip replaced with approved logos, or removed.
5. Replace `WA_NUMBER` everywhere in `index.html` (three card links, the floating button and `data-wa` on the form) with the client's approved WhatsApp number: country code + number, digits only, for example `523312345678`. `grep -n WA_NUMBER index.html` must print nothing.
6. Submit the form on the live site (valid, then honeypot filled) and confirm WhatsApp opens with the right text.
7. `og:image` and an `og-preview.png` are still to do.

Agents: read `AGENTS.md` first.
