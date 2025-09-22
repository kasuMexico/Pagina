/* ==== Colector e inyección global de fingerprint ==== */
(function () {
  // --- helpers de seguridad (tolerantes a errores) ---
  function safe(fn, fallback) { try { return fn(); } catch (_) { return fallback; } }

  function collect() {
    // Cache simple por carga de página
    if (collect._cache) return collect._cache;

    var id = safe(function () {
      if (!window.Fingerprint) return '';
      var fp = new Fingerprint({ canvas: true, ie_activex: true, screen_resolution: true });
      return fp.get();
    }, '');

    var data = {
      fingerprint: id,
      browser:        safe(() => fingerprint_browser(), ''),
      flash:          safe(() => fingerprint_flash(), 'N/A'),
      canvas:         safe(() => fingerprint_canvas(), ''),
      connection:     safe(() => fingerprint_connection(), 'N/A'),
      cookie:         safe(() => fingerprint_cookie(), ''),
      display:        safe(() => fingerprint_display(), ''),
      fontsmoothing:  safe(() => fingerprint_fontsmoothing(), ''),
      fonts:          safe(() => fingerprint_fonts(), ''),
      formfields:     safe(() => fingerprint_formfields(), ''),
      java:           safe(() => fingerprint_java(), ''),
      language:       safe(() => fingerprint_language(), ''),
      silverlight:    safe(() => fingerprint_silverlight(), ''),
      os:             safe(() => fingerprint_os(), ''),
      timezone:       safe(() => fingerprint_timezone(), ''),
      touch:          safe(() => fingerprint_touch(), ''),
      truebrowser:    safe(() => fingerprint_truebrowser(), ''),
      plugins:        safe(() => fingerprint_plugins(), ''),
      useragent:      safe(() => fingerprint_useragent(), '')
    };

    collect._cache = data;
    return data;
  }

  function ensureHidden(form, name, value) {
    var input = form.querySelector('input[name="'+name+'"]');
    if (!input) {
      input = document.createElement('input');
      input.type = 'hidden';
      input.name = name;
      form.appendChild(input);
    }
    input.value = value == null ? '' : String(value);
  }

  function injectIntoForm(form) {
    if (!form || form.dataset.fpInjected === '1') return;
    var vals = collect();
    for (var k in vals) ensureHidden(form, k, vals[k]);
    form.dataset.fpInjected = '1';
  }

  function injectAll(ctx) {
    var root = ctx || document;

    // 1) todos los formularios
    var forms = root.querySelectorAll('form');
    for (var i = 0; i < forms.length; i++) injectIntoForm(forms[i]);

    // 2) slots opcionales
    var slots = root.querySelectorAll('[data-fingerprint-slot], .fp-slot, #FingerPrint');
    for (var j = 0; j < slots.length; j++) {
      var s = slots[j];
      if (s.dataset.fpRendered === '1') continue;
      var vals = collect(), html = '';
      for (var k in vals) {
        html += "<input type='hidden' name='"+k+"' value='"+String(vals[k]).replace(/'/g,"&#39;")+"'>";
      }
      s.innerHTML = html;
      s.dataset.fpRendered = '1';
    }
  }

  // Exponer por si lo quieres llamar manualmente
  window.renderFingerprint = function (ctx) { injectAll(ctx || document); };
  window.injectFingerprintAllForms = function (ctx) { injectAll(ctx || document); };

  // Al cargar DOM
  document.addEventListener('DOMContentLoaded', function () { injectAll(document); });

  // Antes de enviar cualquier form (por si se creó dinámicamente)
  document.addEventListener('submit', function (ev) { injectIntoForm(ev.target); }, true);

  // Al mostrar modales de Bootstrap
  if (window.jQuery) {
    jQuery(document).on('shown.bs.modal', '.modal', function () { injectAll(this); });
  }

  // Observar DOM por nuevos formularios/slots
  if (window.MutationObserver) {
    new MutationObserver(function (mutations) {
      for (var m = 0; m < mutations.length; m++) {
        var list = mutations[m].addedNodes;
        for (var n = 0; n < list.length; n++) {
          var node = list[n];
          if (node.nodeType !== 1) continue;
          if (node.tagName === 'FORM') injectIntoForm(node);
          else injectAll(node);
        }
      }
    }).observe(document.documentElement, { childList: true, subtree: true });
  }
})();
