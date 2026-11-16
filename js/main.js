// Mobile menu toggle and contact form enhancement. Both are optional: the page and the form work without JS.
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

// Contact form: fetch the PHP handler and show the result inline.
(function () {
  var form = document.getElementById("contacto");
  if (!form || !window.fetch) return;
  var ok = document.getElementById("enviado");
  var err = document.getElementById("error");
  var btn = form.querySelector("button[type=submit]");
  var stamp = form.querySelector("input[name=t]");
  var errDefault = err.textContent;
  if (stamp) stamp.value = String(Date.now());

  function show(el, text) {
    ok.classList.remove("is-visible");
    err.classList.remove("is-visible");
    if (text) el.textContent = text;
    el.classList.add("is-visible");
    el.scrollIntoView({ block: "nearest", behavior: "smooth" });
  }

  form.addEventListener("submit", function (e) {
    e.preventDefault();
    ok.classList.remove("is-visible");
    err.classList.remove("is-visible");
    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }
    btn.disabled = true;
    fetch(form.action, {
      method: "POST",
      body: new FormData(form),
      headers: { Accept: "application/json" }
    })
      .then(function (res) {
        var type = res.headers.get("content-type") || "";
        if (type.indexOf("application/json") === -1) {
          // Static host (for example the GitHub Pages preview): there is no PHP to answer.
          show(err, "El formulario no está disponible en esta vista previa.");
          return;
        }
        return res.json().then(function (data) {
          if (res.ok && data.ok) {
            form.reset();
            if (stamp) stamp.value = String(Date.now());
            show(ok);
          } else if (res.status === 422) {
            show(err, "Revisa tu nombre y tu teléfono e inténtalo de nuevo.");
          } else if (res.status === 429) {
            show(err, "Demasiados intentos. Inténtalo más tarde o escríbenos por WhatsApp.");
          } else {
            show(err, errDefault);
          }
        });
      })
      .catch(function () { show(err, errDefault); })
      .then(function () { btn.disabled = false; });
  });
})();
