<?php
/**
 * Template: Estado de Cuenta (HTML para Dompdf) homologado a .doc-*
 * Requisitos en scope:
 *  - $venta, $persona, $datos, $saldo, $Credito, $mysqli, $tel
 */

if (!function_exists('h')) {
    function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
}

/* ===== Defaults y formatos ===== */
$tel        = $tel ?? '';
$saldoFmt   = isset($saldo) ? number_format((float)$saldo, 2) : '0.00';
$costoVenta = isset($venta['CostoVenta']) ? number_format((float)$venta['CostoVenta'], 2) : '0.00';
$idVenta    = isset($venta['Id']) ? (int)$venta['Id'] : 0;

$fechaVenta   = isset($venta['FechaRegistro'])   ? substr((string)$venta['FechaRegistro'], 0, 10)   : '';
$fechaPersona = isset($persona['FechaRegistro']) ? substr((string)$persona['FechaRegistro'], 0, 10) : '';

$direccion = trim((string)($datos['Direccion'] ?? ''));
$telefono  = (string)($datos['Telefono'] ?? '');
$email     = (string)($datos['Mail'] ?? '');
$producto  = (string)($venta['Producto'] ?? '');
$idFirma   = (string)($venta['IdFIrma'] ?? '');

/* ===== Pagos (consulta preparada) ===== */
$pagos = [];
if (isset($mysqli) && $mysqli instanceof mysqli && $idVenta > 0) {
    if ($st = $mysqli->prepare("SELECT FechaRegistro, status, Cantidad FROM Pagos WHERE IdVenta = ? ORDER BY FechaRegistro ASC")) {
        $st->bind_param('i', $idVenta);
        $st->execute();
        if ($rs = $st->get_result()) {
            while ($row = $rs->fetch_assoc()) { $row['Cantidad'] = (float)$row['Cantidad']; $pagos[] = $row; }
        }
        $st->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Estado de Cuenta</title>
    <!-- Mantén tu hoja sin cambios -->
    <link rel="stylesheet" href="https://kasu.com.mx/login/Generar_PDF/css/Cotizacion.css?v=3">
</head>
<body>

  <!-- ===== Encabezado ===== -->
  <table class="doc-header">
    <tr>
      <td class="doc-header__logo-cell">
        <div class="doc-header__logo-box">
            <img src="https://kasu.com.mx/assets/poliza/transp.jpg" class="doc-header__logo" alt="KASU">
        </div>
      </td>
      <td class="doc-header__text">
        <h1 class="doc-title">KASU, Servicios a Futuro S.A. de C.V.</h1>
        <h2 class="doc-subtitle">RFC: KSF201022441 &nbsp; WEB: www.kasu.com.mx</h2>
        <p class="u-muted">Bosque de Chapultepec, Pedregal 24, Molino del Rey, Ciudad de México, CDMX, C.P. 11000</p>
        <p class="u-muted">Teléfono: <?= h($tel) ?> &nbsp; Email: antcliente@kasu.com.mx</p>
      </td>
    </tr>
  </table>

  <div class="doc-container">
    <!-- ===== Sección: Datos del Cliente ===== -->
    <section class="doc-section">
      <div class="doc-section__header">Datos del Cliente</div>
      <div class="doc-section__body">
        Nombre: <?= h($persona['Nombre'] ?? '') ?><br>
        CURP: <?= h($persona['ClaveCurp'] ?? '') ?><br>
        Fecha Registro: <?= h($fechaVenta) ?><br>
        Fecha Última Modificación: <?= h($fechaPersona) ?><br>
      </div>
    </section>

    <!-- ===== Sección: Detalle de servicio ===== -->
    <section class="doc-section">
      <div class="doc-section__header">Detalle del servicio</div>
      <div class="doc-section__body">
        Dirección:
        <?php if ($direccion !== ''): ?>
          <?= h($direccion) ?>
        <?php else: ?>
          <span class="text-danger">No disponible</span>
        <?php endif; ?>
        <br>
        Teléfono: <?= h($telefono) ?><br>
        Email: <?= h($email) ?><br>
        Producto: <?= h($producto) ?><br>
        N. Activador: <?= h($idFirma) ?><br>
        <?= h($Credito ?? '') ?><br>
      </div>
    </section>

    <!-- ===== Sección: Historial de transacciones ===== -->
    <section class="doc-section avoid-break">
      <div class="doc-section__header">Historial de transacciones</div>
      <div class="doc-section__body">
        <table class="doc-table">
          <thead>
            <tr>
              <th>Fecha</th>
              <th>Concepto</th>
              <th>Saldo</th>
              <th>Pagos</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><?= h($fechaVenta) ?></td>
              <td>Compra de servicio <?= h($producto) ?></td>
              <td><?= h($costoVenta) ?></td>
              <td>-</td>
            </tr>
            <?php foreach ($pagos as $pago): ?>
              <tr>
                <td><?= h(substr((string)$pago['FechaRegistro'], 0, 10)) ?></td>
                <td><?= h((string)$pago['status']) ?> de Servicio <?= h($producto) ?></td>
                <td>-</td>
                <td><?= number_format((float)$pago['Cantidad'], 2) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <table class="doc-table" style="margin-top:8pt">
          <tbody>
            <tr>
              <td><strong>Saldo para liquidar</strong></td>
              <td class="u-right"><strong><?= h($saldoFmt) ?></strong></td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    <!-- Separador visual según tu CSS -->
    <div class="doc-divider"></div>

    <!-- Aviso de privacidad con estilo existente -->
    <p class="u-muted">Consulta nuestro aviso de privacidad en: kasu.com.mx/privacidad</p>
  </div>

</body>
</html>
