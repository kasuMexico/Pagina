<?php
/********************************************************************************************
 * Qué hace: Página "Mesa Prospectos" de la PWA. Muestra y gestiona prospectos, abre modales
 *           para registrar venta, cancelar, reasignar, actualizar y enviar a LeadSales.
 * Fecha: 05/11/2025
 * Revisado por: JCCM
 ********************************************************************************************/

// =================== Sesión y dependencias ===================
// Qué hace: Inicia sesión, carga librerías y activa excepciones de mysqli para PHP 8.2
// Fecha: 05/11/2025 | Revisado por: JCCM
session_start();
require_once '../eia/librerias.php';
require_once __DIR__ . '/php/mesa_helpers.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// =================== Guardia de sesión ===================
// Qué hace: Valida autenticación y redirige a login si no hay sesión de Vendedor
// Fecha: 05/11/2025 | Revisado por: JCCM
if (empty($_SESSION["Vendedor"])) {
  header('Location: https://kasu.com.mx/login');
  exit;
}

// =================== Utilidades ===================
// Qué hace: Función de escape HTML segura para impresión en vista
// Fecha: 05/11/2025 | Revisado por: JCCM
if (!function_exists('h')) {
  function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}

// =================== Fechas periodo ===================
// Qué hace: Define inicio de mes y fecha de hoy para filtros y vistas
// Fecha: 05/11/2025 | Revisado por: JCCM
$FechIni = date("d-m-Y", strtotime('first day of this month'));
$FechFin = date("d-m-Y");

// =================== Vars base ===================
// Qué hace: Variables de control de ventana/modal y parámetros de búsqueda
// Fecha: 05/11/2025 | Revisado por: JCCM
$Reg     = null;
$Ventana = null;   // "Ventana1".. "Ventana9"
$Lanzar  = null;   // "#Ventana" (contenedor único)
$Metodo  = "Mesa";
$nombre  = $_POST['nombre'] ?? ($_GET['nombre'] ?? '');
if ($nombre === '') $nombre = ' ';

// =================== Selector (IdProspecto = {V}{Id}) ===================
// Qué hace: Interpreta el parámetro IdProspecto, determina la ventana a abrir y carga el prospecto
//           V = número de ventana; Id = Id numérico del prospecto
// Fecha: 05/11/2025 | Revisado por: JCCM
$IdProspecto = $_POST['IdProspecto'] ?? ($_GET['IdProspecto'] ?? null);
if ($IdProspecto !== null && $IdProspecto !== '') {
  $Vtn   = substr($IdProspecto, 0, 1);
  $idStr = substr($IdProspecto, 1);

  if (ctype_digit((string)$idStr)) {
    $CteInt = (int)$idStr;

    // Prospecto
    $stmt = $pros->prepare("SELECT * FROM prospectos WHERE Id = ?");
    if (!$stmt) { error_log($pros->error); die('Error SQL'); }
    $stmt->bind_param('i', $CteInt);
    $stmt->execute();
    $res = $stmt->get_result();
    $Reg = $res ? $res->fetch_assoc() : null;
    $stmt->close();
  } else {
    $Vtn = '';
  }

  if ($Vtn !== '' && ctype_digit((string)$Vtn)) {
    $Ventana = 'Ventana'. $Vtn; // para lógica interna
    $Lanzar  = '#Ventana';      // id real del modal único
  }
}

// =================== Alerta opcional por GET ?Msg= ===================
// Qué hace: Muestra alerta informativa si viene un mensaje por querystring
// Fecha: 05/11/2025 | Revisado por: JCCM
if (isset($_GET['Msg'])) {
  echo "<script>alert('".htmlspecialchars((string)$_GET['Msg'], ENT_QUOTES, 'UTF-8')."');</script>";
}

// =================== Cache bust estático ===================
// Qué hace: Versión de recursos estáticos para evitar caché del navegador
// Fecha: 05/11/2025 | Revisado por: JCCM
$VerCache = time();

// =================== Mapa de coordinadores por sucursal ===================
// Qué hace: Carga lista de coordinadores (Nivel=4) agrupados por sucursal para selects
// Fecha: 05/11/2025 | Revisado por: JCCM
$coorsMap = [];
if ($q = $mysqli->query("SELECT Id, Nombre, Sucursal FROM Empleados WHERE Nivel = 4 AND Nombre <> 'Vacante' ORDER BY Nombre")) {
  while ($r = $q->fetch_assoc()) {
    $sid    = (int)$r['Sucursal'];
    $nomSuc = $basicas->BuscarCampos($mysqli,'nombreSucursal','Sucursal','Id',$sid);
    $coorsMap[$sid][] = [
      'id'   => (int)$r['Id'],
      'text' => $r['Nombre'].' - Coordinador - '.$nomSuc
    ];
  }
}

// =================== Cancelación de prospecto (POST CancelaCte) ===================
// Qué hace: Registra auditoría, marca cancelación y notifica por mensaje
// Fecha: 05/11/2025 | Revisado por: JCCM
if (!empty($_POST['CancelaCte'])) {
    // Auditoría (GPS + fingerprint)
    $ids = $seguridad->auditoria_registrar(
        $mysqli,
        $basicas,
        $_POST,
        'Cancelar_Venta',
        $_POST['Host'] ?? $_SERVER['PHP_SELF']
    );
    // Mensaje de cancelación
    $_GET['Msg'] = "Se ha cancelado el prospecto";
    // Cambio de Status de Prospecto
    $basicas->ActCampo($pros, "prospectos", "Cancelacion", 1, (int)($_POST['IdVenta'] ?? 0));
}
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#F2F2F2">
  <link rel="icon" href="https://kasu.com.mx/assets/images/kasu_logo.jpeg">
  <title>Mesa Prospectos</title>

  <!-- =================== PWA / iOS ===================
       Qué hace: Manifiesto y meta para instalación como app
       Fecha: 05/11/2025 | Revisado por: JCCM -->
  <link rel="manifest" href="/login/manifest.webmanifest">
  <link rel="apple-touch-icon" href="/login/assets/img/icon-152x152.png">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

  <!-- =================== CSS unificado ===================
       Qué hace: Carga de estilos base de Bootstrap, iconos y hoja local
       Fecha: 05/11/2025 | Revisado por: JCCM -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link rel="stylesheet" href="/login/assets/css/styles.min.css?v=<?= h($VerCache) ?>">
  <link rel="stylesheet" href="assets/css/Grafica.css">
</head>
<body onload="localize()">
  <!-- =================== Top bar fija ===================
       Qué hace: Encabezado con título y botón para crear prospecto
       Fecha: 05/11/2025 | Revisado por: JCCM -->
  <div class="topbar">
    <div class="d-flex align-items-center w-100">
      <h4 class="title">Cartera de Prospectos</h4>

      <!-- botón crear prospecto -->
      <form class="BtnSocial m-0 ml-auto" method="POST" action="<?= h($_SERVER['PHP_SELF']) ?>">
        <input type="hidden" name="Host" value="<?= h($_SERVER['PHP_SELF']) ?>">
        <label for="btnCrear" class="btn mb-0" title="Nuevo prospecto" style="background:#F7DC6F;color:#000;">
          <i class="material-icons">person_add</i>
        </label>
        <!-- Enviar IdProspecto = "20" (2 + 0) para que el selector abra Ventana2 -->
        <input id="btnCrear" type="submit" name="IdProspecto" value="40" hidden>
      </form>
    </div>
  </div>

  <!-- =================== Menú inferior compacto ===================
       Qué hace: Navegación inferior de la PWA
       Fecha: 05/11/2025 | Revisado por: JCCM -->
  <!-- Menú inferior fijo -->
  <section id="Menu">
    <?php require_once __DIR__ . '/html/Menuprinc.php'; ?>
  </section>

  <!-- =================== Modal único ===================
       Qué hace: Contenedor de modales. Carga vistas por require según $Ventana
       Fecha: 05/11/2025 | Revisado por: JCCM -->
  <div class="modal fade" id="Ventana" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document"><div class="modal-content">
      <?php if ($Ventana === "Ventana1"): ?>
        <!-- Registrar Venta HTML Listo para revision Formulario  -->
        <?php require 'html/NvoCliente.php'; ?>

      <?php elseif ($Ventana === "Ventana2"): ?>
        <!-- (2) Cancelar Prospecto HTML Listo para revision Formulario -->
        <?php require 'html/CancelUsr.php'; ?>

      <?php elseif ($Ventana === "Ventana3"): ?>
        <!-- (3) Asignar prospecto a ejecutivo HTML Listo para revision Formulario -->
        <form method="POST" action="php/Registro_Prospectos.php">
          <div class="modal-header">
            <h5 class="modal-title" id="modalV3">Reasignar prospecto</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="IdProspecto" value="<?= h($Reg['Id'] ?? '') ?>">
            <input type="hidden" name="nombre"  value="<?= h($nombre) ?>">
            <input type="hidden" name="Status"  value="<?= h($_POST['Status'] ?? '') ?>">
            <input type="hidden" name="Host" value="<?= h($_SERVER['PHP_SELF']) ?>">

            <p>Nombre del prospecto</p>
            <h4 class="text-center"><strong><?= h($Reg['FullName'] ?? '') ?></strong></h4>
            <p>Este prospecto está asignado a</p>
            <h4 class="text-center">
              <strong>
                <?php
                  //Seleccionamos el nivel del Usuario actual
                  $Niv = (int)$basicas->BuscarCampos($mysqli, "Nivel", "Empleados", "IdUsuario", $_SESSION["Vendedor"]);
                  //Validamos el nombre del Usuario que tiene asignado el prospecto
                  $RegUsuario = $basicas->BuscarCampos($mysqli, "Nombre", "Empleados", "Id", $Reg['Asignado'] ?? 0);

                  if (($RegUsuario ?? '') === "SISTEMA") {
                    echo "SISTEMA";
                    $Niv = 4;
                    $sql = "SELECT * FROM Empleados WHERE Nivel >= $Niv AND Nombre != 'Vacante'";
                  } elseif ($Niv === 1) {
                    $UsrPro = $RegUsuario;
                    echo $UsrPro ?: "Sin Asignar";
                    $sql = "SELECT * FROM Empleados WHERE Nombre != 'Vacante'";
                  } else {
                    echo h($basicas->BuscarCampos($mysqli, "Nombre", "Empleados", "IdUsuario", $Reg['Usuario'] ?? 0));
                    $Suc = (int)$basicas->BuscarCampos($mysqli, "Sucursal", "Empleados", "IdUsuario", $Reg['Usuario'] ?? 0);
                    $Niv = (int)$basicas->BuscarCampos($mysqli, "Nivel", "Empleados", "IdUsuario", $Reg['Usuario'] ?? 0);
                    $sql = "SELECT * FROM Empleados WHERE Nivel >= $Niv AND Nombre != 'Vacante' AND Sucursal = $Suc";
                  }
                ?>
              </strong>
            </h4>

            <label>Selecciona el nuevo Ejecutivo</label>
            <select class="form-control" name="NvoVend">
            <?php
            // Solo nivel 6 o superior, sin "Vacante"
            $sql = "
              SELECT e.Id, e.Nombre, COALESCE(s.nombreSucursal,'Sin sucursal') AS Sucursal
              FROM Empleados e
              LEFT JOIN Sucursal s ON s.Id = e.Sucursal
              WHERE e.Nivel >= 6 AND e.Nombre <> 'Vacante'
              ORDER BY s.nombreSucursal, e.Nombre
            ";
            if ($res = $mysqli->query($sql)) {
              while ($row = $res->fetch_assoc()) {
                echo '<option value="'.h($row['Id']).'">'.h($row['Nombre'].' - '.$row['Sucursal']).'</option>';
              }
            }
            ?>
            </select>
            <br>
          </div>
          <div class="modal-footer">
            <input type="submit" name="AsigVende" class="btn btn-primary" value="Asignar Prospecto">
          </div>
        </form>

      <?php elseif ($Ventana === "Ventana4"): ?>
        <!-- (4) Crear datos prospecto HTML Listo para revision Formulario -->
        <?php require_once 'html/NvoProspecto.php'; ?>

      <?php elseif ($Ventana === "Ventana5"): ?>
        <!-- (5) Editar datos prospecto HTML Listo para revision Formulario-->
        <?php require 'html/ActualizarDatos.php'; ?>

      <?php elseif ($Ventana === "Ventana6"): ?>
        <!-- (6) Enviar a LeadSales HTML Listo para revision Formulario -->
        <?php require 'html/CrearLeadSales.php'; ?>
      <?php endif; ?>
    </div></div>
  </div>

  <br><br>

  <!-- =================== Tabla de prospectos ===================
       Qué hace: Lista prospectos activos, calcula semanas y muestra acciones
       Fecha: 05/11/2025 | Revisado por: JCCM -->
  <section name="impresion de datos finales">
    <div class="table-responsive mesa-table-wrapper">
      <table class="table mesa-table" data-mesa="prospectos">
        <thead>
          <tr>
            <th>Nombre Prospecto</th>
            <th>Sem. Activo</th>
            <th>Servicio</th>
            <th>Origen</th>
            <th>Asignado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
        <?php
        // Mostrar solo Cancelacion = 0
        if ($nombre === ' ') {
          $buscar = $basicas->BLikesD2($pros,'prospectos','FullName',$nombre,'Cancelacion',0,'Automatico',0);
        } else {
          $buscar = $basicas->BLikesCan($pros,'prospectos','FullName',$nombre,'Cancelacion',0);
        }

        foreach ($buscar as $row):
          $Sem     = strtotime($row['Alta']);
          $HoyA    = strtotime(date("Y-m-d"));
          $ContSem = ($HoyA - $Sem) / 604800; // 7*24*3600

          $esDistribuidor = (strtoupper((string)$row['Servicio_Interes']) === 'DISTRIBUIDOR');
        ?>
          <tr>
            <td><?= h($row['FullName']) ?></td>
            <td><?= (int)round($ContSem, 0) ?></td>
            <td><?= h($row['Servicio_Interes']) ?></td>
            <td><?= h($row['Origen']) ?></td>
            <td><?= h($basicas->BuscarCampos($mysqli,'IdUsuario','Empleados','Id',(int)$row['Asignado'])) ?></td>
            <td class="mesa-actions" data-label="Acciones">
              <div class="mesa-actions-grid">
                <form method="POST" action="<?= h($_SERVER['PHP_SELF']) ?>">
                  <input type="hidden" name="Host"   value="<?= h($_SERVER['PHP_SELF']) ?>">
                  <input type="hidden" name="nombre" value="<?= h($nombre) ?>">

                  <!-- Registrar Venta (1) -->
                  <label for="b1<?= (int)$row['Id'] ?>" title="Registrar Venta" class="btn" style="background:#58D68D;color:#F8F9F9;">
                    <i class="material-icons">verified_user</i>
                  </label>
                  <input id="b1<?= (int)$row['Id'] ?>" type="submit" name="IdProspecto" value="1<?= (int)$row['Id'] ?>" hidden>

                  <!-- Enviar lead Sales (6) -->
                  <label for="b6<?= (int)$row['Id'] ?>" title="Enviar lead Sales" class="btn" style="background:#21618C;color:#F8F9F9;">
                    <i class="material-icons">card_travel</i>
                  </label>
                  <input id="b6<?= (int)$row['Id'] ?>" type="submit" name="IdProspecto" value="6<?= (int)$row['Id'] ?>" hidden>

                  <!-- Dar de Baja (2) -->
                  <label for="b2<?= (int)$row['Id'] ?>" title="Dar de Baja al Prospecto" class="btn" style="background:#E74C3C;color:#F8F9F9;">
                    <i class="material-icons">cancel</i>
                  </label>
                  <input id="b2<?= (int)$row['Id'] ?>" type="submit" name="IdProspecto" value="2<?= (int)$row['Id'] ?>" hidden>

                  <!-- Reasignar a ejecutivo (3) -->
                  <label for="b3<?= (int)$row['Id'] ?>" title="Asignar prospecto a un ejecutivo" class="btn" style="background:#AF7AC5;color:#F8F9F9;">
                    <i class="material-icons">people_alt</i>
                  </label>
                  <input id="b3<?= (int)$row['Id'] ?>" type="submit" name="IdProspecto" value="3<?= (int)$row['Id'] ?>" hidden>

                  <!-- Enviar correo (7) 
                  <label for="b7<?= (int)$row['Id'] ?>" title="Enviar correo electrónico" class="btn" style="background:#EB984E;color:#F8F9F9;">
                    <i class="material-icons">send_to_mobile</i>
                  </label>
                  <input id="b7<?= (int)$row['Id'] ?>" type="submit" name="IdProspecto" value="7<?= (int)$row['Id'] ?>" hidden>
                  -->
                  <!-- Cambiar datos (5) -->
                  <label for="b5<?= (int)$row['Id'] ?>" title="Cambiar datos del Prospecto" class="btn" style="background:#AAB7B8;color:#F8F9F9;">
                    <i class="material-icons">badge</i>
                  </label>
                  <input id="b5<?= (int)$row['Id'] ?>" type="submit" name="IdProspecto" value="5<?= (int)$row['Id'] ?>" hidden>

                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>

  <!-- =================== JS únicos y en orden ===================
       Qué hace: Carga dependencias JS y utilidades de la PWA
       Fecha: 05/11/2025 | Revisado por: JCCM -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
  <script src="https://www.gstatic.com/charts/loader.js"></script>
  <script src="Javascript/finger.js?v=3"></script>
  <script src="Javascript/localize.js?v=3"></script>
  <script src="Javascript/Inyectar_gps_form.js"></script>

  <!-- =================== Auto-apertura de modal ===================
       Qué hace: Si $Lanzar tiene valor, abre el modal #Ventana al cargar
       Fecha: 05/11/2025 | Revisado por: JCCM -->
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      <?php if (!empty($Lanzar)): ?>
        $('<?= h($Lanzar) ?>').modal('show');
      <?php endif; ?>
    });
  </script>
</body>
</html>
