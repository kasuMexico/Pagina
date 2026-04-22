<?php
/********************************************************************************************
 * Qué hace: Renderiza sección SANDBOX con datos y enlaces tomando la ÚLTIMA versión.
 * Fecha: 04/11/2025
 * Revisado por: JCCM
 ********************************************************************************************/

// Prioridad: $RegLast (último registro ya calculado arriba). Fallback: $Reg.
$ctx = isset($RegLast) && is_array($RegLast) ? $RegLast : (isset($Reg) && is_array($Reg) ? $Reg : []);

// Texto introductorio
$intro = (string)($ctx['Introduccion'] ?? 'Esta API incluye un entorno Sandbox para pruebas previas a producción.');

// Credenciales / datos sandbox con valores por defecto seguros
$sbxUser = (string)($ctx['SandboxUser']   ?? 'Api_KASU_Sandbox');
$sbxKey  = (string)($ctx['SandboxKey']    ?? '');
$curp1   = (string)($ctx['SandboxCurp1']  ?? 'CAMC880526HMCBNR04');
$pol1    = (string)($ctx['SandboxPoliza1']?? 'e0ab0e9a');
$curp2   = (string)($ctx['SandboxCurp2']  ?? 'REAE060617MMCYLVA4');
$pol2    = (string)($ctx['SandboxPoliza2']?? 'ae670d65');

if (!function_exists('apimarket_mask_secret')) {
  function apimarket_mask_secret(string $secret): string {
    $secret = trim($secret);
    if ($secret === '') {
      return 'Se entrega por canal seguro';
    }
    if (strlen($secret) <= 10) {
      return str_repeat('*', strlen($secret));
    }
    return substr($secret, 0, 4) . str_repeat('*', max(4, strlen($secret) - 8)) . substr($secret, -4);
  }
}
?>
<!-- *****          SANDBOX                 ***** -->
<section class="doc-section doc-section--muted" id="sandbox">
  <div class="container">
    <div class="doc-heading">
      <span class="api-kicker">Sandbox</span>
      <h2>Pruebas sin impacto productivo</h2>
      <p>Usa estos valores para construir solicitudes y validar respuesta JSON. Las llaves privadas reales se entregan por canal seguro y no deben publicarse en documentación.</p>
    </div>
    <div class="row">
      <!-- Columna izq 50% -->
      <div class="col-12 col-md-6 align-self-center" data-scroll-reveal="enter right move 30px over 0.6s after 0.4s">
        <div class="doc-panel">
          <div>
            <span class="doc-pill">Entorno de integración</span>
            <p><?= htmlspecialchars($intro, ENT_QUOTES, 'UTF-8') ?></p>
            <p class="doc-note">Las pruebas de alta de cuentas y pagos deben ejecutarse únicamente con usuarios sandbox autorizados.</p>
            <h3>Recursos relacionados</h3>
            <p><a target="_blank" rel="noopener" href="https://learning.postman.com/docs/getting-started/introduction/">Postman tutorial</a></p>
          </div>
        </div>
      </div>

      <div class="col-12 d-block d-md-none" style="height:12px;"></div>

      <!-- Columna der 50% -->
      <div class="col-12 col-md-6 align-self-center" data-scroll-reveal="enter left move 30px over 0.6s after 0.4s">
        <div class="doc-table">
          <div class="table-responsive">
            <table class="table table-responsive justify">
              <thead>
                <tr>
                  <th><strong>PETICIÓN</strong></th>
                  <th><strong>DATOS PARA MODO SANDBOX</strong></th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>PRIVATE_KEY</td>
                  <td class="text-justify"><?= htmlspecialchars(apimarket_mask_secret($sbxKey), ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
                <tr>
                  <td>nombre_de_usuario</td>
                  <td class="text-justify"><?= htmlspecialchars($sbxUser, ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
                <tr>
                  <td>curp_en_uso</td>
                  <td class="text-justify"><?= htmlspecialchars($curp1, ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
                <tr>
                  <td>poliza_en_uso</td>
                  <td class="text-justify"><?= htmlspecialchars($pol1, ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
                <tr>
                  <td>curp_en_uso</td>
                  <td class="text-justify"><?= htmlspecialchars($curp2, ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
                <tr>
                  <td>poliza_en_uso</td>
                  <td class="text-justify"><?= htmlspecialchars($pol2, ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>
