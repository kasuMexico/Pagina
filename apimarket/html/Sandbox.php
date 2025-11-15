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
$sbxKey  = (string)($ctx['SandboxKey']    ?? 'ef235aacf90d9f4aadd8c92e4b2562e1d9eb97f0');
$curp1   = (string)($ctx['SandboxCurp1']  ?? 'CAMC880526HMCBNR04');
$pol1    = (string)($ctx['SandboxPoliza1']?? 'e0ab0e9a');
$curp2   = (string)($ctx['SandboxCurp2']  ?? 'REAE060617MMCYLVA4');
$pol2    = (string)($ctx['SandboxPoliza2']?? 'ae670d65');
?>
<!-- *****          SANDBOX                 ***** -->
<section class="section padding-top-70 colored" id="sandbox">
  <div class="container">
    <div class="row">
      <!-- Columna izq 50% -->
      <div class="col-12 col-md-6 align-self-center" data-scroll-reveal="enter right move 30px over 0.6s after 0.4s">
        <div class="features-small-item">
          <div class="Consulta">
            <h2 class="titulos"><strong>SANDBOX</strong></h2>
            <br>
            <p><?= htmlspecialchars($Introduccion, ENT_QUOTES, 'UTF-8') ?></p>
            <br>
            <h2 class="titulos"><strong>RELATED RESOURCES</strong></h2>
            <br>
            <a target="_blank" rel="noopener" href="https://learning.postman.com/docs/getting-started/introduction/">Postman tutorial</a>
            <br>
            <a target="_blank" rel="noopener" href="#">Calculadora de préstamos</a>
            <br>
          </div>
        </div>
      </div>

      <div class="col-12 d-block d-md-none" style="height:12px;"></div>

      <!-- Columna der 50% -->
      <div class="col-12 col-md-6 align-self-center" data-scroll-reveal="enter left move 30px over 0.6s after 0.4s">
        <div class="row">
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
                  <td class="text-justify"><?= htmlspecialchars($sbxKey, ENT_QUOTES, 'UTF-8') ?></td>
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