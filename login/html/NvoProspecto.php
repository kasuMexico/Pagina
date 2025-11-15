<?php
/********************************************************************************************
 * Qué hace: Formulario modal para registrar nuevo prospecto. Incluye slots GPS/fingerprint,
 *           contexto oculto, control de origen según $Metodo y opciones de servicio por $Niv.
 * Fecha: 05/11/2025
 * Revisado por: JCCM
 ********************************************************************************************/

declare(strict_types=1);

// Normaliza variables de contexto usadas por la vista
$nombreSafe = htmlspecialchars((string)($nombre ?? ''), ENT_QUOTES, 'UTF-8');
$selfSafe   = htmlspecialchars((string)($_SERVER['PHP_SELF'] ?? ''), ENT_QUOTES, 'UTF-8');

$idVenta    = isset($Reg['Id'])   ? (int)$Reg['Id']   : 0;
$idContact  = isset($Recg['id'])  ? (int)$Recg['id']  : 0;
$idUsuario  = isset($Recg1['id']) ? (int)$Recg1['id'] : 0;
$producto   = htmlspecialchars((string)($Reg['Producto'] ?? ''), ENT_QUOTES, 'UTF-8');

$nivInt     = (int)($Niv ?? 0);
$metodoStr  = (string)($Metodo ?? 'Mesa');
$metodoSafe = htmlspecialchars($metodoStr, ENT_QUOTES, 'UTF-8');

// CSRF: usa csrf_auth si existe; si no, reusa csrf; si no, genera
$csrf = $_SESSION['csrf_auth'] ?? ($_SESSION['csrf'] ?? null);
if (!$csrf) {
  $_SESSION['csrf_auth'] = bin2hex(random_bytes(32));
  $csrf = $_SESSION['csrf_auth'];
}
$csrfSafe = htmlspecialchars((string)$csrf, ENT_QUOTES, 'UTF-8');
?>
<form action="https://kasu.com.mx/login/php/Registro_Prospectos.php" method="post" autocomplete="off">
  <!-- Slots de GPS y Fingerprint -->
  <div id="Gps"></div>
  <div data-fingerprint-slot></div>

  <!-- Hidden context -->
  <input type="hidden" name="nombre"    value="<?= $nombreSafe ?>">
  <input type="hidden" name="Host"      value="<?= $selfSafe ?>">
  <input type="hidden" name="IdVenta"   value="<?= $idVenta ?>">
  <input type="hidden" name="IdContact" value="<?= $idContact ?>">
  <input type="hidden" name="IdUsuario" value="<?= $idUsuario ?>">
  <input type="hidden" name="Producto"  value="<?= $producto ?>">
  <input type="hidden" name="csrf"      value="<?= $csrfSafe ?>">

  <div class="modal-header">
    <h5 class="modal-title" id="exampleModalLabel">Registrar Nuevo Prospecto</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
      <span aria-hidden="true">&times;</span>
    </button>
  </div>

  <div class="modal-body">
    <div class="form-group mb-2">
      <label class="mb-1">Clave CURP Prospecto</label>
      <input class="form-control text-uppercase"
             type="text"
             name="CURP"
             placeholder="CLAVE CURP"
             pattern="[A-Za-z0-9]{18}"
             maxlength="18"
             minlength="18"
             oninput="this.value=this.value.toUpperCase()"
             autocomplete="off">
    </div>

    <div class="form-group mb-2">
      <label class="mb-1">E-mail</label>
      <input class="form-control"
             type="email"
             name="Email"
             placeholder="Correo electrónico"
             inputmode="email"
             autocomplete="email">
    </div>

    <div class="form-group mb-2">
      <label class="mb-1">Teléfono</label>
      <input class="form-control"
             type="tel"
             name="Telefono"
             placeholder="10 dígitos"
             required
             inputmode="numeric"
             pattern="[0-9]{10}"
             maxlength="10"
             minlength="10"
             autocomplete="tel">
    </div>

    <?php if ($metodoStr === 'Mesa') : ?>
      <!-- Selección de origen visible cuando $Metodo == 'Mesa' -->
      <div class="form-group mb-2">
        <label class="mb-1">Origen</label>
        <select class="form-control" name="Origen" required>
          <option value="fb">Facebook</option>
          <option value="Gg">Google</option>
          <option value="hub">HubSpot</option>
          <option value="Vtas">Vendedor</option>
        </select>
      </div>
    <?php else: ?>
      <!-- Origen oculto con el valor de $Metodo -->
      <input type="hidden" name="Origen" value="<?= $metodoSafe ?>">
    <?php endif; ?>

    <!-- Lanzamos el select con base en los niveles -->
    <div class="form-group mb-0">
      <label class="mb-1">El usuario está interesado en</label>
      <select class="form-control" name="Servicio" required>
        <option value="FUNERARIO">GASTOS FUNERARIOS</option>
        <option value="RETIRO">AHORRO PARA EL RETIRO</option>

        <?php if ($nivInt === 1 || $nivInt === 3): ?>
          <option value="SEGURIDAD">GASTOS FUNERARIOS OFICIALES</option>
          <option value="TRANSPORTE">GASTOS FUNERARIOS SERVICIO DE TRANSPORTE</option>
        <?php endif; ?>

        <?php if (in_array($nivInt, [1, 3, 4], true)): ?>
          <option value="DISTRIBUIDOR">SER DISTRIBUIDOR</option>
        <?php endif; ?>
      </select>
    </div>

  </div>

  <div class="modal-footer">
    <input type="submit" name="prospectoNvo" class="btn btn-primary" value="Registrar y enviar">
  </div>
</form>
