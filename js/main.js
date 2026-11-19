// Mobile menu toggle and contact form. The page works without JS; the form needs it (the WhatsApp buttons do not).
(function () {
  var nav = document.querySelector(".nav");
  var btn = nav && nav.querySelector(".nav__toggle");
  if (!btn) return;

  function setOpen(open) {
    nav.classList.toggle("is-open", open);
    btn.setAttribute("aria-expanded", String(open));
    btn.setAttribute("aria-label", open ? "Cerrar menú" : "Abrir menú");
  }

  btn.addEventListener("click", function () {
    setOpen(!nav.classList.contains("is-open"));
  });
  nav.addEventListener("click", function (e) {
    if (e.target.closest("a")) setOpen(false);
  });
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape" && nav.classList.contains("is-open")) {
      setOpen(false);
      btn.focus();
    }
  });
})();

// Contact form: no server. Builds a prefilled wa.me message from the fields and opens WhatsApp.
(function () {
  var form = document.getElementById("contacto");
  if (!form) return;
  var ok = document.getElementById("enviado");
  var err = document.getElementById("error");
  var number = form.getAttribute("data-wa") || "";
  var topics = {
    auto: "cotizar un seguro de auto",
    vida: "cotizar un seguro de vida",
    salud: "cotizar un seguro de salud",
    "no-se": "cotizar un seguro, aún no sé cuál me conviene"
  };

  function show(el, text) {
    ok.classList.remove("is-visible");
    err.classList.remove("is-visible");
    if (text) el.textContent = text;
    el.classList.add("is-visible");
    el.scrollIntoView({ block: "nearest", behavior: "smooth" });
  }

  // A missing field reads as empty instead of throwing, so the handler never dies silently.
  function value(name) {
    var el = form.elements[name];
    return el ? el.value : "";
  }

  // Collapse whitespace and drop control characters before the text goes into the URL.
  function clean(value) {
    return String(value || "").replace(/[\u0000-\u001f\u007f]/g, " ").replace(/\s+/g, " ").trim();
  }

  form.addEventListener("submit", function (e) {
    e.preventDefault();
    ok.classList.remove("is-visible");
    err.classList.remove("is-visible");
    // Honeypot: only bots fill it. Do nothing, no feedback.
    if (value("sitio")) return;
    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }
    // Placeholder number (GitHub Pages preview, or WA_NUMBER not replaced yet): fail visibly.
    if (!/^\d{10,15}$/.test(number)) {
      show(err, "El formulario no está disponible en esta vista previa.");
      return;
    }
    var name = clean(value("nombre"));
    var details = clean(value("detalles")).slice(0, 300);
    var email = clean(value("correo"));
    var topic = topics[value("seguro")] || topics["no-se"];
    var text = "Hola, soy " + name + ". Me gustaría " + topic + ".";
    if (details) text += " Detalles: " + details;
    if (email) text += " Mi correo: " + email;
    show(ok);
    window.location.href = "https://wa.me/" + number + "?text=" + encodeURIComponent(text);
  });
})();
