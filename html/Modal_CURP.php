<!-- html/Modal_CURP.php -->
<div class="modal fade" id="curpModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 rounded-3">
      <div id="curpModalBody" class="modal-body p-0" style="max-height:80vh;overflow:auto">
        <!-- aquí se inyecta Consulta.php -->
      </div>
    </div>
  </div>
</div>

<style>
  /* El card que genera Consulta.php ocupa el ancho del modal y sin márgenes extra */
  #curpModalBody > .card{
    max-width: 720px !important;   /* antes venía con 420px */
    margin: 24px auto !important;  /* centrado con margen cómodo */
    box-shadow: 0 6px 18px rgba(0,0,0,.08);
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
