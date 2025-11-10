<?php
/********************************************************************************************
 * Qué hace: Post Sociales para EMPLEADOS autenticados.
 *           Muestra cupones y artículos activos (Validez_Fin vigente) en orden aleatorio.
 *           El payload al compartir es:  base64( IdPost | IdVendedorSesion )
 *           => constructor.php registra ese IdVendedorSesion en Eventos.IdUsr
 * Fecha: 09/11/2025
 * Revisado por: JCCM
 ********************************************************************************************/

declare(strict_types=1);

/* ==========================================================================================
 * Sesión y dependencias
 * ========================================================================================== */
session_start();
require_once __DIR__ . '/../eia/librerias.php';
date_default_timezone_set('America/Mexico_City');

/* ==========================================================================================
 * Validar sesión de empleado
 * ========================================================================================== */
if (empty($_SESSION['Vendedor'])) {
  header('Location: https://kasu.com.mx/login');
  exit;
}
$Vendedor = (string)$_SESSION['Vendedor'];

/* ==========================================================================================
 * Datos de usuario / comisiones por nivel
 * ========================================================================================== */
$NivRaw = $basicas->BuscarCampos($mysqli, 'Nivel', 'Empleados', 'IdUsuario', $Vendedor);
$Niv    = (int)($NivRaw ?? 0);
$col    = 'N' . ($Niv > 0 ? $Niv : 7); // fallback N7
$PorCom = (float)($basicas->BuscarCampos($mysqli, $col, 'Comision', 'Id', 2) ?? 0);

/* ==========================================================================================
 * Helpers de consulta local
 * ========================================================================================== */
/** Tarjetas ACTIVAS por tipo en orden aleatorio */
function getActivePosts(mysqli $db, string $tipo, int $limit): array {
  $sql = "SELECT Id, Red, DesA, TitA, Producto, Img
          FROM PostSociales
          WHERE Status = 1
            AND Tipo   = ?
            AND (Validez_Fin IS NULL OR Validez_Fin='' OR Validez_Fin >= CURDATE())
          ORDER BY RAND()
          LIMIT ?";
  $st = $db->prepare($sql);
  if ($st === false) return [];
  $st->bind_param('si', $tipo, $limit);
  $st->execute();
  $rs = $st->get_result();
  $rows = $rs ? $rs->fetch_all(MYSQLI_ASSOC) : [];
  $st->close();
  return $rows;
}

/** Comisión por producto mostrado */
function comisionPorProducto(mysqli $db, string $producto, float $porcentaje): float {
  $gen = (float)($GLOBALS['basicas']->BuscarCampos($db, 'comision', 'Productos', 'Producto', $producto) ?? 0);
  return $gen * ($porcentaje / 100.0);
}

/* ==========================================================================================
 * Share URLs — payload usa Id del VENDEDOR en sesión
 * ========================================================================================== */
function buildShareEmpleado(array $reg, string $idVendedor): array {
  $payload = (string)($reg['Id'] ?? '') . '|' . $idVendedor; // <- se registra en constructor.php como IdUsr
  $dest = 'https://kasu.com.mx/constructor.php?datafb=' . base64_encode($payload);

  $tit = (string)($reg['TitA'] ?? '');
  $txt = (string)($reg['DesA'] ?? '');
  $tx  = trim($tit . ' ' . $txt);

  return [
    'dest' => $dest,
    'fb'   => 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode($dest),
    'x'    => 'https://twitter.com/intent/tweet?text=' . rawurlencode($tx) . '&url=' . rawurlencode($dest),
    'li'   => 'https://www.linkedin.com/sharing/share-offsite/?url=' . rawurlencode($dest),
  ];
}

$VerCacheSafe = isset($VerCache) ? (string)$VerCache : '1';
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
  <meta charset="utf-8">
  <title>Post Sociales</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#F2F2F2">
  <link rel="icon" href="https://kasu.com.mx/assets/images/kasu_logo.jpeg">

  <!-- CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css">
  <link rel="stylesheet" href="/login/assets/css/styles.min.css?v=<?= htmlspecialchars($VerCacheSafe, ENT_QUOTES) ?>">
  <link rel="stylesheet" href="/login/assets/css/cupones.css">

  <style>
    .topbar{position:sticky;top:0;z-index:10;background:#fff;border-bottom:1px solid #e5e7eb;padding:.75rem 1rem}
    .title{font-weight:600}
    .share-row{display:flex;gap:.5rem;align-items:center;margin:.5rem 0}
    .ico-social{width:36px;height:36px;object-fit:contain;vertical-align:middle}
  </style>
</head>
<body>

  <!-- Barra superior fija -->
  <div class="topbar">
    <div class="d-flex align-items-center w-100">
      <h4 class="title m-0">Post Sociales — Empleados</h4>
    </div>
  </div>

  <!-- Menú inferior fijo -->
  <section id="Menu">
    <?php require_once __DIR__ . '/html/Menuprinc.php'; ?>
  </section>

  <!-- Contenido -->
  <main class="page-content">
    <div class="container">
      <div class="row">

        <?php
        /* ======= Cupones activos aleatorios (6) ======= */
        $cupones = getActivePosts($mysqli, 'Vta', 6);
        if (!$cupones) {
          echo '<div class="col-12"><p class="text-muted">No hay cupones activos.</p></div>';
        }
        foreach ($cupones as $Reg) {
          $share = buildShareEmpleado($Reg, $Vendedor);
          $Comis = comisionPorProducto($mysqli, (string)$Reg['Producto'], $PorCom);
          ?>
          <div class="col-12 col-md-6 mb-4">
            <div class="card h-100">
              <div class="card-body">
                <!-- Imagen principal: NO es link; el share solo con íconos -->
                <img class="img-fluid w-100"
                     src="https://kasu.com.mx/assets/images/cupones/<?= htmlspecialchars((string)$Reg['Img'], ENT_QUOTES) ?>"
                     alt="Cupón">

                <!-- Íconos de share -->
                <a href="<?= htmlspecialchars($share['fb'], ENT_QUOTES) ?>"
                  target="_blank" rel="external noopener noreferrer" aria-label="Compartir en Facebook">
                  <img class="ico-social" src="/login/assets/img/sociales/facebook.png" alt="Facebook">
                </a>
                <a href="<?= htmlspecialchars($share['x'], ENT_QUOTES) ?>"
                  target="_blank" rel="external noopener noreferrer" aria-label="Compartir en X">
                  <img class="ico-social" src="/login/assets/img/sociales/x.png" alt="X">
                </a>
                <a href="<?= htmlspecialchars($share['li'], ENT_QUOTES) ?>"
                  target="_blank" rel="external noopener noreferrer" aria-label="Compartir en LinkedIn">
                  <img class="ico-social" src="/login/assets/img/sociales/LinkedIn.png" alt="LinkedIn">
                </a>
                <hr>
                <br>
                <div class="ContCupon">
                  <h2 class="h5"><Strong>Comision por compra</strong></h2>
                  <h3 class="h6 mb-2"><?= htmlspecialchars((string)$Reg['TitA'], ENT_QUOTES) ?></h3>
                  <p class="mb-0"><?= htmlspecialchars((string)$Reg['DesA'], ENT_QUOTES) ?></p>
                </div>
              </div>
            </div>
          </div>
          <?php
        }

        /* ======= Artículos activos aleatorios (4) ======= */
        $articulos = getActivePosts($mysqli, 'Art', 4);
        if (!$articulos) {
          echo '<div class="col-12"><p class="text-muted">No hay artículos activos.</p></div>';
        }
        foreach ($articulos as $Reg) {
          $share = buildShareEmpleado($Reg, $Vendedor);

          $Comis = comisionPorProducto($mysqli, (string)$Reg['Producto'], $PorCom);
          // Ajuste por producto para artículos
          if (($Reg['Producto'] ?? '') === 'Universidad') {
            $Comis /= 2500;
          } elseif (($Reg['Producto'] ?? '') === 'Retiro') {
            $Comis /= 1000;
          } else {
            $Comis /= 100;
          }
          ?>
          <div class="col-12 col-md-6 mb-4">
            <div class="card h-100">
              <div class="card-body">
                <img class="img-fluid w-100"
                     src="<?= htmlspecialchars((string)$Reg['Img'], ENT_QUOTES) ?>" alt="Artículo">

                <div class="share-row">
                  <a href="<?= htmlspecialchars($share['fb'], ENT_QUOTES) ?>" target="_blank" rel="noopener" aria-label="Compartir en Facebook">
                    <img class="ico-social" src="/login/assets/img/sociales/facebook.png" alt="Facebook">
                  </a>
                  <a href="<?= htmlspecialchars($share['x'], ENT_QUOTES) ?>" target="_blank" rel="noopener" aria-label="Compartir en X">
                    <img class="ico-social" src="/login/assets/img/sociales/x.png" alt="X">
                  </a>
                  <a href="<?= htmlspecialchars($share['li'], ENT_QUOTES) ?>" target="_blank" rel="noopener" aria-label="Compartir en LinkedIn">
                    <img class="ico-social" src="/login/assets/img/sociales/LinkedIn.png" alt="LinkedIn">
                  </a>
                  <a href="#" onclick="shareInstagram('<?= htmlspecialchars($share['dest'], ENT_QUOTES) ?>');return false;" aria-label="Compartir en Instagram">
                    <img class="ico-social" src="/login/assets/img/sociales/instagram.png" alt="Instagram">
                  </a>
                </div>

                <hr>
                <div class="ContCupon">
                  <h2 class="h5">Lectura $<?= number_format($Comis, 2) ?></h2>
                  <h3 class="h6 mb-2"><?= htmlspecialchars((string)$Reg['TitA'], ENT_QUOTES) ?></h3>
                  <p class="mb-1"><?= htmlspecialchars((string)$Reg['DesA'], ENT_QUOTES) ?></p>
                  <small class="text-muted">*Comisión por usuario único, por día de lectura</small>
                </div>
              </div>
            </div>
          </div>
          <?php
        }
        ?>
      </div><!-- /.row -->
      <br><br><br><br>
    </div><!-- /.container -->
  </main>

  <!-- JS -->
  <script>
  function shareInstagram(url){
    if (navigator.share){
      navigator.share({url}).catch(function(){copyUrl(url);});
    } else {
      copyUrl(url);
    }
  }
  function copyUrl(text){
    try{
      navigator.clipboard.writeText(text);
      alert('Enlace copiado. Abre Instagram y pégalo en tu publicación o bio.');
    }catch(e){
      prompt('Copia el enlace:', text);
    }
  }
  </script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
  <script src="/login/Javascript/finger.js"></script>
  <script src="/login/Javascript/localize.js"></script>
</body>
</html>
