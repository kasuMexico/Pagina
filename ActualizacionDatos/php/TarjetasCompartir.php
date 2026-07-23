<?php
/********************************************************************************************
 * Qué hace: Funciones de consulta de PostSociales + carga de datos ($cupones, $articulos).
 *           Requerido UNA vez (require_once) antes de usar _tarjetas_html.php
 * Fecha: 23/07/2026
 ********************************************************************************************/

declare(strict_types=1);

/** Tarjetas activas por tipo en orden aleatorio */
function getActivePosts(mysqli $db, string $tipo, int $limit): array {
  $sql = "SELECT Id, Red, DesA, TitA, Producto, Img
          FROM PostSociales
          WHERE Status = 1
            AND Tipo   = ?
            AND (Validez_Fin IS NULL OR Validez_Fin = '' OR Validez_Fin >= CURDATE())
          ORDER BY RAND()
          LIMIT ?";
  $stmt = $db->prepare($sql);
  if ($stmt === false) return [];
  $stmt->bind_param('si', $tipo, $limit);
  $stmt->execute();
  $res  = $stmt->get_result();
  $rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
  $stmt->close();
  return $rows;
}

/** Comisión por producto mostrado */
function comisionPorProducto(mysqli $db, string $producto, float $porcentaje): float {
  $gen = (float)($GLOBALS['basicas']->BuscarCampos($db, 'comision', 'Productos', 'Producto', $producto) ?? 0);
  return $gen * ($porcentaje / 100.0);
}

/** URLs de share por red */
function buildShare(array $reg, string $idFirma): array {
  $payload = (string)($reg['Id'] ?? '') . '|' . $idFirma;
  $dest = 'https://kasu.com.mx/constructor.php?datafb=' . base64_encode($payload);
  $tit  = (string)($reg['TitA'] ?? '');
  $txt  = (string)($reg['DesA'] ?? '');

  return [
    'dest' => $dest,
    'fb'   => 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode($dest),
    'x'    => 'https://twitter.com/intent/tweet?text=' . rawurlencode(trim("$tit $txt"))
             . '&url=' . rawurlencode($dest),
    'li'   => 'https://www.linkedin.com/sharing/share-offsite/?url=' . rawurlencode($dest),
  ];
}

/* ===== Datos compartidos (se ejecutan una vez) ===== */
$PorCom  = (float)($basicas->BuscarCampos($mysqli, 'N' . 7, 'Comision', 'Id', 2) ?? 0);
$IdFirma = (string)($venta['IdFIrma'] ?? ($_SESSION['Vendedor'] ?? ''));

$cupones   = getActivePosts($mysqli, 'Vta', 6);
$articulos = getActivePosts($mysqli, 'Art', 4);
