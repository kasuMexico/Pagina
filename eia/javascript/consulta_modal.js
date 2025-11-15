// eia/javascript/consulta_modal.js
(function () {
  function showModal() {
    // Bootstrap 3/4 via jQuery
    if (window.jQuery && typeof jQuery.fn.modal === 'function') {
      jQuery('#curpModal').modal('show');
      return;
    }
    // Bootstrap 5
    if (window.bootstrap && bootstrap.Modal) {
      new bootstrap.Modal(document.getElementById('curpModal')).show();
    }
  }

  function consultaModal() {
    const inp = document.getElementById('curp');
    const curp = (inp?.value || '').trim().toUpperCase();
    if (!curp) return;

    const body = document.getElementById('curpModalBody');
    body.textContent = 'Consultando…';
    showModal();

    const url = '/php/Consulta.php?value=' + encodeURIComponent(btoa(curp));
    fetch(url, { credentials: 'include' })
      .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.text(); })
      .then(html => {
        body.innerHTML = html;
        // Abrir enlaces en nueva pestaña
        body.querySelectorAll('a[href]').forEach(a => a.target = '_blank');
      })
      .catch(err => { body.textContent = 'Error: ' + err.message; });
  }

  // Imprimir solo el modal
  function printModal() { window.print(); }

  // Exponer global
  window.consultaModal = consultaModal;
  window.printModal = printModal;
})();
