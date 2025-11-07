<?php
/**
 * Archivo que imprime la búsqueda del cliente en la página principal.
 * Fecha: 2025-11-03
 * Revisado por: JCCM
 */

require_once '../eia/librerias.php';

// Dependencias mínimas
if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
  http_response_code(500); exit('Error de conexión.');
}
if (!isset($basicas)) {
  http_response_code(500); exit('Dependencia $basicas no disponible.');
}
if (!isset($financieras)) { $financieras = null; }

// Helpers
function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8'); }
function nombreProductoMostrar(string $p): string {
  $p = trim($p);
  return $p==='Universidad' ? 'Inversión Universitaria' : ($p==='Retiro' ? 'Retiro Privado' : 'Gastos Funerarios');
}
function productosPorContacto(mysqli $db, int $id): array {
  $sql="SELECT Producto FROM Venta WHERE IdContact=? AND (Status IS NULL OR Status<>'CANCELADA')";
  $st=$db->prepare($sql); if(!$st) return [];
  $st->bind_param('i',$id); $st->execute(); $r=$st->get_result();
  $out=[]; while($row=$r->fetch_assoc()){ $p=trim((string)$row['Producto']); if($p!=='') $out[]=$p; }
  $st->close(); return array_values(array_unique($out));
}
function colExiste(mysqli $db, string $tabla, string $col): bool {
  $tabla=$db->real_escape_string($tabla); $col=$db->real_escape_string($col);
  $rs=$db->query("SHOW COLUMNS FROM `$tabla` LIKE '$col'");
  return $rs && $rs->num_rows>0;
}

// Param
if (!isset($_GET['value'])) { http_response_code(400); exit('Parámetro faltante.'); }
$dat_raw = base64_decode($_GET['value'], true);
if ($dat_raw === false) { http_response_code(400); exit('Valor inválido.'); }
$dat = $mysqli->real_escape_string($dat_raw);

// Contacto por CURP
$cont = (int)$basicas->BuscarCampos($mysqli,"IdContact","Usuario","ClaveCurp",$dat);

if ($cont >= 1) {
  $TAB_VTA = "Venta";
  $TAB_USR = "Usuario";
  $TAB_CTO = "Contacto";

  // Datos base
  $producto = (string)$basicas->BuscarCampos($mysqli,"Producto",$TAB_VTA,"IdContact",$cont);
  $estatus  = (string)$basicas->BuscarCampos($mysqli,"Status",$TAB_VTA,"IdContact",$cont);
  $idvta    = (int)$basicas->BuscarCampos($mysqli,"Id",$TAB_VTA,"IdContact",$cont);

  $esFallecido = (mb_strtoupper($estatus,'UTF-8') === 'FALLECIDO');

  // Productos del contacto
  $productosRaw = productosPorContacto($mysqli,$cont);
  if (!$productosRaw && $producto!=='') { $productosRaw = [$producto]; }
  $productosMostrar = array_map('nombreProductoMostrar',$productosRaw);

  // Evento
  $basicas->InsertCampo($mysqli,"Eventos",[
    "Contacto"=>$cont,
    "Host"=>$_SERVER['PHP_SELF'] ?? '',
    "Evento"=>"ConsultaCURP",
    "Usuario"=>"PLATAFORMA",
    "IdVta"=>$idvta,
    "FechaRegistro"=>date('Y-m-d H:i:s'),
  ]);

  // Nombre completo: Nombre desde Usuario→Venta→Contacto. Apellidos desde Usuario, con fallback a Contacto(APaterno/AMaterno)
  $Nombre = (string)(
      $basicas->BuscarCampos($mysqli,"Nombre",$TAB_USR,"IdContact",$cont) ?:
      $basicas->BuscarCampos($mysqli,"Nombre",$TAB_VTA,"IdContact",$cont) ?:
      $basicas->BuscarCampos($mysqli,"Nombre",$TAB_CTO,"id",$cont)
  );

  $Paterno = '';
  if (colExiste($mysqli,$TAB_USR,'Paterno')) {
    $Paterno = (string)($basicas->BuscarCampos($mysqli,"Paterno",$TAB_USR,"IdContact",$cont) ?? '');
  }
  if ($Paterno === '' && colExiste($mysqli,$TAB_CTO,'APaterno')) {
    $Paterno = (string)($basicas->BuscarCampos($mysqli,"APaterno",$TAB_CTO,"id",$cont) ?? '');
  }

  $Materno = '';
  if (colExiste($mysqli,$TAB_USR,'Materno')) {
    $Materno = (string)($basicas->BuscarCampos($mysqli,"Materno",$TAB_USR,"IdContact",$cont) ?? '');
  }
  if ($Materno === '' && colExiste($mysqli,$TAB_CTO,'AMaterno')) {
    $Materno = (string)($basicas->BuscarCampos($mysqli,"AMaterno",$TAB_CTO,"id",$cont) ?? '');
  }

  $NombreCompleto = trim(preg_replace('/\s+/', ' ', trim("$Nombre $Paterno $Materno")));

  // URLs
  $polizaHref = 'https://kasu.com.mx/login/Generar_PDF/Poliza_pdf.php?busqueda=' . base64_encode((string)$cont);
  $cuentaHref = 'https://kasu.com.mx/ActualizacionDatos/index.php?value=' . base64_encode($dat);
  ?>
  <div class="card shadow-sm border-0 mx-auto my-3" style="max-width:420px;">
    <div class="card-header bg-white border-0 text-center pt-3 pb-0">
      <h6 class="mb-0">Resumen del cliente</h6>
      <small class="text-muted">Validación y datos de póliza</small>
    </div>

    <div class="card-body pt-3 pb-2">
      <div id="FingerPrint" class="d-none"></div>

      <div class="text-center mb-3">
        <div class="text-uppercase text-muted small mb-1">Cliente</div>
        <div class="fs-5 fw-semibold lh-sm"><?= e($NombreCompleto) ?></div>
      </div>

      <div class="mb-3">
        <div class="row g-2 align-items-start">
          <div class="col-12">
            <div class="text-uppercase text-muted small">Clave CURP</div>
            <code class="d-inline-block fs-6 fw-semibold" style="word-break:break-all;">
              <?= mb_strtoupper((string)$dat,'UTF-8') ?>
            </code>
          </div>

          <div class="col-12 mt-2">
            <div class="text-uppercase text-muted small">Productos seleccionados</div>
            <ul class="list-unstyled d-flex flex-wrap gap-2 mb-0">
              <?php foreach ($productosMostrar as $p): ?>
                <li><span class="badge bg-light text-dark border"><?= e($p) ?></span></li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>
      </div>

      <div class="pt-2 mt-1 border-top">
        <div class="text-uppercase text-muted small mb-1">Estatus de la póliza</div>
        <div class="fs-6"><?= e($estatus) ?></div>
      </div>

      <?php if ($estatus==='COBRANZA' || $estatus==='PREVENTA'): ?>
        <?php
          $pagosRealizados = ($idvta && $financieras) ? (float)$financieras->SumarPagos($mysqli,"Cantidad","Pagos","IdVenta",$idvta) : 0.0;
          $saldoPendiente  = ($idvta && $financieras) ? (float)$financieras->SaldoCredito($mysqli,$idvta) : 0.0;
        ?>
        <hr class="my-3">
        <div class="row g-2">
          <div class="col-12 col-sm-6">
            <div class="text-uppercase text-muted small mb-1">Pagos realizados</div>
            <div class="fs-6"><?= e($pagosRealizados) ?></div>
          </div>
          <div class="col-12 col-sm-6">
            <div class="text-uppercase text-muted small mb-1">Pendiente de pagar</div>
            <div class="fs-6"><?= e($saldoPendiente) ?></div>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <div class="card-footer bg-white border-0 pt-0 pb-3">
      <?php if ($esFallecido): ?>
        <!-- sin botones -->
      <?php elseif ($estatus==='PREVENTA'): ?>
        <?php
          $waText = sprintf('Buen día estoy interesado en retomar mi proceso de venta de mi Servicio %s mi nombre es %s',$producto,$NombreCompleto);
          $waHref = 'https://api.whatsapp.com/send?phone=527208177632&text='.urlencode($waText);
        ?>
        <a class="btn btn-primary w-100" target="_blank" rel="noopener noreferrer" href="<?= e($waHref) ?>">Contactar un Ejecutivo</a>
      <?php elseif ($estatus==='COBRANZA'): ?>
        <?php if ($idvta): $pdfHref='https://kasu.com.mx/login/Generar_PDF/Estado_Cuenta_pdf.php?busqueda='.base64_encode((string)$idvta); ?>
          <a href="<?= e($pdfHref) ?>" class="btn btn-secondary w-100">Descargar estado de Cta</a>
        <?php endif; ?>
        <a href="#" class="btn btn-success w-100" style="margin-top:10px;">Realizar un pago</a>
      <?php else: ?>
        <div class="row g-2">
          <div class="col-12 col-sm-6">
            <a href="<?= e($polizaHref) ?>" class="btn btn-secondary w-100" download>Descargar mi Póliza</a>
          </div>
          <div class="col-12 col-sm-6">
            <a href="<?= e($cuentaHref) ?>" class="btn w-100" style="background:#ec7c26;border-color:#ec7c26;color:#fff;">Ingresar a mi Cuenta</a>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
  <?php
} else {
  // Evento de error
  $basicas->InsertCampo($mysqli,"Eventos",[
    "Contacto"=>$dat,
    "Host"=>$_SERVER['PHP_SELF'] ?? '',
    "Evento"=>"ErrorConsulta",
    "Usuario"=>"PLATAFORMA",
    "FechaRegistro"=>date('Y-m-d H:i:s'),
  ]);
  ?>
  <div class="card border-0 shadow-sm my-3 mx-auto" style="max-width:420px;">
    <div class="card-body" style="min-height:220px;">
      <h6 class="text-dark mb-3">No se tiene registro de esta CURP. Verifique si es correcta.</h6>
      <p class="mb-2">Si no se ha registrado o le interesa el servicio le invitamos a registrarse en este</p>
      <p class="mb-0">
        <a href="https://kasu.com.mx/registro.php" target="_blank" rel="noopener" style="color:#911F66; font-size:1.05rem;">
          enlace de registro
        </a>
      </p>
    </div>
  </div>
  <?php
}