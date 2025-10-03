<?php
// =================== Sesión y dependencias ===================
session_start();
require_once '../eia/librerias.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Guardia de sesión
if (empty($_SESSION["Vendedor"])) {
  header('Location: https://kasu.com.mx/login');
  exit;
}

// Utils
if (!function_exists('h')) {
  function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}

// =================== Fechas periodo ===================
$FechIni = date("d-m-Y", strtotime('first day of this month'));
$FechFin = date("d-m-Y");

// =================== Vars base ===================
$Reg     = null;
$Ventana = null;   // "Ventana1".. "Ventana9"
$Lanzar  = null;   // "#Ventana" (contenedor único)
$Metodo  = "Mesa";
$nombre  = $_POST['nombre'] ?? ($_GET['nombre'] ?? '');
if ($nombre === '') $nombre = ' ';

// =================== Selector (IdProspecto = {V}{Id}) ===================
$IdProspecto = $_POST['IdProspecto'] ?? ($_GET['IdProspecto'] ?? null);
if ($IdProspecto !== null && $IdProspecto !== '') {
  $Vtn   = substr($IdProspecto, 0, 1);
  $idStr = substr($IdProspecto, 1);
  if (ctype_digit($idStr)) {
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

  if (!empty($Vtn) && ctype_digit($Vtn)) {
    $Ventana = 'Ventana'.$Vtn; // para lógica interna
    $Lanzar  = '#Ventana';     // id real del modal único
  }
}

// Alertas
if (isset($_GET['Msg'])) {
  echo "<script>alert('".h($_GET['Msg'])."');</script>";
}

// Cache bust
$VerCache = time();

// =================== Mapa de coordinadores por sucursal ===================
$coorsMap = [];
// Nivel 4 = Coordinador
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
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#F2F2F2">
  <link rel="icon" href="https://kasu.com.mx/assets/images/kasu_logo.jpeg">
  <title>Mesa Prospectos</title>

  <!-- PWA / iOS -->
  <link rel="manifest" href="/login/manifest.webmanifest">
  <link rel="apple-touch-icon" href="/login/assets/img/icon-152x152.png">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

  <!-- CSS unificado -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link rel="stylesheet" href="/login/assets/css/styles.min.css?v=<?= h($VerCache) ?>">
  <link rel="stylesheet" href="assets/css/Grafica.css">
</head>
<body onload="localize()">
  <!-- Top bar fija -->
  <div class="topbar">
    <div class="d-flex align-items-center w-100">
      <h4 class="title">Cartera de Prospectos</h4>

      <!-- botón crear prospecto -->
      <form class="BtnSocial m-0 ml-auto" method="POST" action="<?= h($_SERVER['PHP_SELF']) ?>">
        <input type="hidden" name="Host" value="<?= h($_SERVER['PHP_SELF']) ?>">
        <label for="btnCrear" class="btn mb-0" title="Crear nuevo prospecto" style="background:#F7DC6F;color:#000;">
          <i class="material-icons">person_add</i>
        </label>
        <input id="btnCrear" type="submit" name="CreaProsp" hidden>
      </form>
    </div>
  </div>

  <!-- Menú inferior compacto -->
  <section id="Menu" class="mb-2">
    <div class="MenuPrincipal">
      <a class="BtnMenu" href="Pwa_Principal.php"><img src="assets/img/FlorKasu.png" alt="Inicio"></a>
      <a class="BtnMenu" href="Mesa_Herramientas.php"><img src="assets/img/herramientas.png" style="background:#A9D0F5;" alt="Herramientas"></a>
    </div>
  </section>

  <!-- Modal único -->
  <div class="modal fade" id="Ventana" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document"><div class="modal-content">
      <?php if ($Ventana === "Ventana1"): ?>
        <!-- Registrar Venta -->
        <form method="POST" action="../eia/php/Registrar_Venta.php">
          <div class="modal-header">
            <h5 class="modal-title"><?= h($Reg['FullName'] ?? '') ?></h5>
          </div>
          <div class="modal-body">
            <input type="hidden" name="Host"        value="<?= h($_SERVER['PHP_SELF']) ?>">
            <input type="hidden" name="FullName"    value="<?= h($Reg['FullName'] ?? '') ?>">
            <input type="hidden" name="IdProspecto" value="<?= h($Reg['Id'] ?? '') ?>">
            <input type="hidden" name="Producto"    value="<?= h($Reg['Servicio_Interes'] ?? '') ?>">

            <pre id="resultado"></pre>
            <label>Clave CURP</label>
            <input class="form-control" type="text" name="CurClie" id="CurCli" maxlength="18" oninput="validarInput(this)" required>

            <label>Correo Electrónico</label>
            <input class="form-control" type="email" name="Mail" value="<?= h($Reg['Email'] ?? '') ?>" required>

            <label>Teléfono</label>
            <input class="form-control" type="number" name="Telefono" value="<?= h($Reg['NoTel'] ?? '') ?>" required>

            <label>Calle</label>
            <input class="form-control" type="text" name="calle" required>

            <label>Número</label>
            <input class="form-control" type="text" name="numero" required>

            <label>Colonia</label>
            <input class="form-control" type="text" name="colonia" required>

            <label>Municipio</label>
            <input class="form-control" type="text" name="municipio" required>

            <label>Estado</label>
            <input class="form-control" type="text" name="estado" required>

            <label>Código Postal</label>
            <input class="form-control" type="text" name="codigo_postal" required>

            <label>Tipo de servicio</label>
            <select class="form-control" name="TipoServicio">
              <option value="Tradicional">Tradicional</option>
              <option value="Ecologico" selected>Ecológico</option>
              <option value="Cremacion">Cremación</option>
            </select>

            <label>Tiempo de pago</label>
            <select class="form-control" name="Meses">
              <option value="0">Pago Único</option>
              <option value="3">3 Meses</option>
              <option value="6">6 Meses</option>
              <option value="9">9 Meses</option>
            </select>
            <br>
            <div class="Legales">
              <p><input type="checkbox" name="Terminos" value="acepto" required> Acepto Términos y Condiciones (kasu.com.mx/terminos-y-condiciones.php)</p>
              <p><input type="checkbox" name="Aviso" value="acepto" required> Acepto Aviso de privacidad (kasu.com.mx/Aviso-de-privacidad)</p>
              <p><input type="checkbox" name="Fideicomiso" value="acepto" required> Conozco el Fideicomiso KASU (kasu.com.mx/Fideicomiso_F0003.pdf)</p>
            </div>
          </div>
          <div class="modal-footer">
            <input type="submit" name="RegistroMesa" class="btn btn-primary" value="Registrar Venta">
          </div>
        </form>

      <?php elseif ($Ventana === "Ventana2"): ?>
        <!-- Registrar Distribuidor => crea Empleado (Nivel 7) -->
        <form method="POST" action="php/Funcionalidad_Empleados.php">
          <div class="modal-header">
            <h5 class="modal-title">Registrar distribuidor</h5>
          </div>
          <div class="modal-body">
            <?php
              // Datos desde prospectos
              $idPros = $Reg['Id']        ?? '';
              $nom    = $Reg['FullName']  ?? '';
              $tel    = $Reg['NoTel']     ?? '';
              $mai    = $Reg['Email']     ?? '';
              $dir    = $Reg['Direccion'] ?? '';
              $cla    = '';
            ?>
            <input type="hidden" name="Host"        value="<?= h($_SERVER['PHP_SELF']) ?>">
            <input type="hidden" name="nombre"      value="<?= h($nombre) ?>">
            <input type="hidden" name="Nivel"       value="7">
            <input type="hidden" name="IdProspecto" value="<?= h($idPros) ?>">

            <label>Nombre</label>
            <input type="hidden" name="Nombre" value="<?= h($nom) ?>">
            <input class="form-control" type="text" value="<?= h($nom) ?>" disabled>

            <label>Teléfono</label>
            <input type="hidden" name="Telefono" value="<?= h($tel) ?>">
            <input class="form-control" type="text" value="<?= h($tel) ?>" disabled>

            <label>Email</label>
            <input type="hidden" name="Email" value="<?= h($mai) ?>">
            <input class="form-control" type="text" value="<?= h($mai) ?>" disabled>

            <label>Dirección</label>
            <input type="hidden" name="Direccion" value="<?= h($dir) ?>">
            <input class="form-control" type="text" value="<?= h($dir) ?>" disabled>

            <label>Cuenta Bancaria (CLABE)</label>
            <input type="hidden" name="Cuenta" value="<?= h($cla) ?>">
            <input class="form-control" type="number" value="<?= h($cla) ?>" disabled>

            <label>Sucursal</label>
            <select class="form-control" name="Sucursal" id="selSucursal" required>
              <?php
              $sql1 = "SELECT * FROM Sucursal WHERE Status = 1 ORDER BY nombreSucursal";
              if ($S621 = $mysqli->query($sql1)) {
                while ($S631 = $S621->fetch_assoc()) {
                  echo '<option value="'.h($S631['id']).'">'.h($S631['nombreSucursal']).'</option>';
                }
              }
              ?>
            </select>

            <label class="mt-2">Coordinador</label>
            <select class="form-control" name="Lider" id="selLider" required>
              <?php
              $firstSid = null;
              if (!empty($coorsMap)) {
                $firstSid = array_key_first($coorsMap);
                foreach (($coorsMap[$firstSid] ?? []) as $o) {
                  echo '<option value="'.(int)$o['id'].'">'.h($o['text']).'</option>';
                }
              }
              ?>
            </select>
          </div>
          <div class="modal-footer">
            <input type="submit" name="CreaEmpl" class="btn btn-primary" value="Registrar distribuidor">
          </div>
        </form>

        <script>
          (function(){
            var map    = <?= json_encode($coorsMap, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?>;
            var selSuc = document.getElementById('selSucursal');
            var selLed = document.getElementById('selLider');
            function fill(){
              var sid  = selSuc.value;
              var list = map[sid] || [];
              selLed.innerHTML = '';
              list.forEach(function(o){
                var opt = document.createElement('option');
                opt.value = o.id;
                opt.textContent = o.text;
                selLed.appendChild(opt);
              });
            }
            if (selSuc && selLed) {
              fill();
              selSuc.addEventListener('change', fill);
            }
          })();
        </script>

      <?php elseif ($Ventana === "Ventana3"): ?>
        <!-- Asignar prospecto a ejecutivo -->
        <form method="POST" action="php/Registro_Prospectos.php">
          <div class="modal-header">
            <h5 class="modal-title"><?= h($Reg['FullName'] ?? '') ?></h5>
          </div>
          <div class="modal-body">
            <input type="hidden" name="Host"        value="<?= h($_SERVER['PHP_SELF']) ?>">
            <input type="hidden" name="nombre"      value="<?= h($nombre) ?>">
            <input type="hidden" name="IdProspecto" value="<?= h($Reg['Id'] ?? '') ?>">

            <p>Asignar prospecto a</p>
            <label>Selecciona a quién se asignará</label>
            <select class="form-control" name="NvoVend">
              <?php
              $Nvl  = (int)$basicas->BuscarCampos($mysqli,'Nivel','Empleados','IdUsuario',$_SESSION["Vendedor"]);
              $sql9 = ($Nvl === 1)
                ? "SELECT * FROM Empleados WHERE Nombre <> 'Vacante' ORDER BY Nombre"
                : "SELECT * FROM Empleados WHERE Nivel >= 5 AND Nombre <> 'Vacante' ORDER BY Nombre";
              if ($S629 = $mysqli->query($sql9)) {
                while ($S635 = $S629->fetch_assoc()) {
                  $Su2cur = $basicas->BuscarCampos($mysqli,"nombreSucursal","Sucursal","Id",$S635['Sucursal']);
                  $St2ats = $basicas->BuscarCampos($mysqli,"NombreNivel","Nivel","Id",$S635['Nivel']);
                  echo '<option value="'.h($S635['Id']).'">'.h($S635['Nombre'].' - '.$St2ats.' - '.$Su2cur).'</option>';
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
        <?php require_once 'html/NvoProspecto.php'; ?>

      <?php elseif ($Ventana === "Ventana5"): ?>
        <!-- Editar datos prospecto -->
        <form method="POST" action="php/Registro_Prospectos.php">
          <div class="modal-header">
            <h5 class="modal-title">Fecha de Alta <?= h($Reg['Alta'] ?? '') ?></h5>
          </div>
          <div class="modal-body">
            <input type="hidden" name="Host"        value="<?= h($_SERVER['PHP_SELF']) ?>">
            <input type="hidden" name="nombre"      value="<?= h($nombre) ?>">
            <input type="hidden" name="IdProspecto" value="<?= h($Reg['Id'] ?? '') ?>">

            <label>Nombre</label>
            <input class="form-control" type="text" name="FullName" value="<?= h($Reg['FullName'] ?? '') ?>">

            <label>Teléfono</label>
            <input class="form-control" type="text" name="NoTel" value="<?= h($Reg['NoTel'] ?? '') ?>">

            <label>Dirección</label>
            <input class="form-control" type="text" name="Direccion" value="<?= h($Reg['Direccion'] ?? '') ?>">

            <label>Email</label>
            <input class="form-control" type="text" name="Email" value="<?= h($Reg['Email'] ?? '') ?>">

            <label>Servicio de Interés ⇒ <?= h($Reg['Servicio_Interes'] ?? '') ?></label>
            <select class="form-control" name="Servicio_Interes">
              <option value="0">SELECCIONA UN SERVICIO</option>
              <option value="FUNERARIO">FUNERARIO</option>
              <option value="DISTRIBUIDOR">DISTRIBUIDOR</option>
              <option value="UNIVERSITARIO">INVERSION UNIVERSITARIA</option>
              <option value="RETIRO">RETIRO SEGURO</option>
            </select>
          </div>
          <div class="modal-footer">
            <input type="submit" name="CamDat" class="btn btn-primary" value="Modificar Datos">
          </div>
        </form>
      <?php endif; ?>
    </div></div>
  </div>

  <br><br>

  <!-- Tabla -->
  <section name="impresion de datos finales">
    <div class="table-responsive">
      <table class="table">
        <tr>
          <th>Nombre Prospecto</th>
          <th>Sem. Activo</th>
          <th>Servicio</th>
          <th>Art. Env</th>
          <th>Asignado</th>
          <th>Acciones</th>
        </tr>
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
          $ContSem = ($HoyA - $Sem) / 604800;

          $esDistribuidor = (strtoupper($row['Servicio_Interes']) === 'DISTRIBUIDOR');
        ?>
          <tr>
            <td><?= h($row['FullName']) ?></td>
            <td><?= round($ContSem,0) ?></td>
            <td><?= h($row['Servicio_Interes']) ?></td>
            <td><?= h($row['Sugeridos']) ?></td>
            <td><?= h($basicas->BuscarCampos($mysqli,'IdUsuario','Empleados','Id',$row['Asignado'])) ?></td>
            <td>
              <div class="d-flex">
                <form method="POST" action="<?= h($_SERVER['PHP_SELF']) ?>">
                  <input type="hidden" name="Host"   value="<?= h($_SERVER['PHP_SELF']) ?>">
                  <input type="hidden" name="nombre" value="<?= h($nombre) ?>">

                  <!-- Registrar Venta (1) -->
                  <label for="b1<?= (int)$row['Id'] ?>" title="Registrar Venta" class="btn" style="background:#58D68D;color:#F8F9F9;">
                    <i class="material-icons">attach_money</i>
                  </label>
                  <input id="b1<?= (int)$row['Id'] ?>" type="submit" name="IdProspecto" value="1<?= (int)$row['Id'] ?>" hidden>

                  <!-- Enviar lead Sales (6) -->
                  <label for="b6<?= (int)$row['Id'] ?>" title="Enviar lead Sales" class="btn" style="background:#21618C;color:#F8F9F9;">
                    <i class="material-icons">card_travel</i>
                  </label>
                  <input id="b6<?= (int)$row['Id'] ?>" type="submit" name="IdProspecto" value="6<?= (int)$row['Id'] ?>" hidden>

                  <!-- Quitar Presupuesto y Citas: no Ventana8/9 -->

                  <!-- Dar de Baja (6) -->
                  <label for="bd<?= (int)$row['Id'] ?>" title="Dar de Baja al Prospecto" class="btn" style="background:#E74C3C;color:#F8F9F9;">
                    <i class="material-icons">cancel</i>
                  </label>
                  <input id="bd<?= (int)$row['Id'] ?>" type="submit" name="IdProspecto" value="6<?= (int)$row['Id'] ?>" hidden>

                  <!-- Asignar a ejecutivo (3) -->
                  <label for="b3<?= (int)$row['Id'] ?>" title="Asignar prospecto a un ejecutivo" class="btn" style="background:#AF7AC5;color:#F8F9F9;">
                    <i class="material-icons">people_alt</i>
                  </label>
                  <input id="b3<?= (int)$row['Id'] ?>" type="submit" name="IdProspecto" value="3<?= (int)$row['Id'] ?>" hidden>

                  <!-- Enviar correo (7) -->
                  <label for="b7<?= (int)$row['Id'] ?>" title="Enviar correo electrónico" class="btn" style="background:#EB984E;color:#F8F9F9;">
                    <i class="material-icons">send_to_mobile</i>
                  </label>
                  <input id="b7<?= (int)$row['Id'] ?>" type="submit" name="IdProspecto" value="7<?= (int)$row['Id'] ?>" hidden>

                  <!-- Cambiar datos (5) -->
                  <label for="b5<?= (int)$row['Id'] ?>" title="Cambiar datos del Prospecto" class="btn" style="background:#AAB7B8;color:#F8F9F9;">
                    <i class="material-icons">badge</i>
                  </label>
                  <input id="b5<?= (int)$row['Id'] ?>" type="submit" name="IdProspecto" value="5<?= (int)$row['Id'] ?>" hidden>

                  <?php if ($esDistribuidor): ?>
                    <!-- Convertir en distribuidor (2) -> backend debe mover a Cancelacion=1 -->
                    <label for="b2<?= (int)$row['Id'] ?>" title="Convertir en distribuidor" class="btn" style="background:#58D68D;color:#F8F9F9;">
                      <i class="material-icons">verified_user</i>
                    </label>
                    <input id="b2<?= (int)$row['Id'] ?>" type="submit" name="IdProspecto" value="2<?= (int)$row['Id'] ?>" hidden>
                  <?php endif; ?>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </table>
    </div>
  </section>

  <!-- JS únicos y en orden -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
  <script src="https://www.gstatic.com/charts/loader.js"></script>
  <script src="Javascript/fingerprint-core-y-utils.js"></script>
  <script src="Javascript/finger.js"></script>
  <script src="Javascript/localize.js"></script>

  <!-- Abrir modal si corresponde -->
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      <?php if (!empty($Lanzar)): ?>
        $('<?= h($Lanzar) ?>').modal('show');
      <?php endif; ?>
    });
  </script>
</body>
</html>
