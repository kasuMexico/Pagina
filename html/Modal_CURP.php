<!-- html/Modal_CURP.php -->
<div class="modal fade" id="curpModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered ">
    <div class="modal-content border-0 ">
      <div id="curpModalBody" class="modal-body p-0" >
        <!-- aquí se inyecta Consulta.php -->
      </div>
    </div>
  </div>
</div>

<style>
  /* El card que genera Consulta.php ocupa el ancho del modal y sin márgenes extra */
  #curpModalBody > .card{
    max-width: 520px !important;
    margin: 24px auto !important;
    box-shadow: var(--shadow-md, 0 12px 28px rgba(16,24,40,.12));
    border-radius: var(--radius-md, 14px);
    border: 1px solid var(--color-border, #e2e5ea);
  }

  /* Opcional: compactar márgenes internos */
  #curpModalBody .card-body{ padding: 24px; }
  #curpModalBody .card-footer{ padding: 16px 24px; }

  /* Impresión: sólo el modal, sin fondo */
  @media print{
    body > :not(#curpModal){ display:none !important; }
    #curpModal{ position:static; display:block !important; }
    #curpModal .modal-dialog{ max-width:100%; margin:0; }
    #curpModal .modal-body{ padding:0; }
  }
</style>
