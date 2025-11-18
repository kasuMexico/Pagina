<?php
/**************************************************************************************************
 * ARCHIVO: php/AnalisisDatos/a_Ventas_por_status.php
 * FECHA: 2025-11-05
 * REVISADO POR: JCCM
 *
 * ¿QUÉ HACE?
 *  - Genera un JSON (Google DataTable) con las unidades vendidas agrupadas por STATUS de la venta.
 *  - Si el usuario (vendedor) es de Nivel >= 5: cuenta solo sus ventas.
 *  - Si el usuario es de Nivel <= 4: suma las ventas de su equipo según la jerarquía.
 *
 * BLOQUES:
 *  1) Sesión y dependencias.
 *  2) Lectura de nivel y “líder/equipo” base del usuario en sesión.
 *  3) Consulta de catálogo de Status y cómputo de unidades por cada Status.
 *  4) Salida en formato JSON compatible con google.visualization.DataTable.
 **************************************************************************************************/

/* ========= 1) Sesión y dependencias ========= */
require_once dirname(__DIR__, 3) . '/eia/session.php';
kasu_session_start();
require_once __DIR__ . '/../../eia/librerias.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['Vendedor']) || $_SESSION['Vendedor'] === '') {
    echo json_encode(['cols'=>[], 'rows'=>[]], JSON_UNESCAPED_UNICODE);
    exit;
}
if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
    echo json_encode(['cols'=>[], 'rows'=>[]], JSON_UNESCAPED_UNICODE);
    exit;
}

/* ========= 2) Nivel del usuario y líder/equipo base ========= */
$NivelUsuario = (int)$basicas->BuscarCampos($mysqli, 'Nivel', 'Empleados', 'IdUsuario', $_SESSION['Vendedor']);
/* Id interno del registro del empleado actual. Se usa como “semilla” para el recorrido por equipos. */
$lider = (int)$basicas->BuscarCampos($mysqli, 'Id', 'Empleados', 'IdUsuario', $_SESSION['Vendedor']);

/* ========= 3) Consulta de Status y cómputo ========= */
$today = date('Y-m-d');
$minDefault = '2000-01-01';
$iniGet = filter_input(INPUT_GET, 'ini', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: $minDefault;
$finGet = filter_input(INPUT_GET, 'fin', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: $today;
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $iniGet)) { $iniGet = $minDefault; }
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $finGet)) { $finGet = $today; }
if ($iniGet > $finGet) { [$iniGet, $finGet] = [$finGet, $iniGet]; }
$iniFull = $iniGet . ' 00:00:00';
$finFull = $finGet . ' 23:59:59';

$sql = "SELECT * FROM Status";
$result = $mysqli->query($sql);

$stmtCount = $mysqli->prepare("SELECT COUNT(*) AS total FROM Venta WHERE Usuario = ? AND Status = ? AND FechaRegistro BETWEEN ? AND ?");
$countStatus = function(string $usuario, string $status) use (&$stmtCount, $iniFull, $finFull): int {
    $stmtCount->bind_param('ssss', $usuario, $status, $iniFull, $finFull);
    $stmtCount->execute();
    $res = $stmtCount->get_result()->fetch_assoc();
    $stmtCount->free_result();
    return (int)($res['total'] ?? 0);
};

/* Estructura DataTable: columnas fijas */
$data = [
    'cols' => [
        ['label' => 'Status',   'type' => 'string'],
        ['label' => 'Unidades', 'type' => 'number'],
    ],
    'rows' => []
];

while ($row = $result->fetch_assoc()) {
    $statusNombre = (string)$row['Nombre'];
    $unidades_vendidas = 0;
    $liderBase = $lider;

    if ($NivelUsuario >= 5) {
        // Ventas solo del usuario en sesión
        $unidades_vendidas = $countStatus((string)$_SESSION['Vendedor'], $statusNombre);

    } elseif ($NivelUsuario <= 4) {
        // Recorre jerarquía desde el nivel máximo hasta el nivel del usuario
        $nivelMax = (int)$basicas->Max1DifDat($mysqli, 'Nivel', 'Empleados', 'Nombre', 'Vacante');
        $a = $nivelMax;
        $liderIter = $liderBase;

        while ($a >= $NivelUsuario) {
            // ¿Existen asignaciones a nivel $a con el “lider” actual?
            $ExiReg = $basicas->ConDosCon($mysqli, 'Empleados', 'Equipo', $liderIter, 'Nivel', $a, 'Nombre', 'Vacante');

            if ($a === $nivelMax || !empty($ExiReg)) {
                $sql1 = "SELECT * FROM Empleados WHERE Nivel = {$a} AND Nombre <> 'Vacante'";
            } else {
                $sql1 = "SELECT * FROM Empleados WHERE Nivel = {$a} AND Nombre <> 'Vacante' AND Id = {$liderIter}";
            }

            if ($res1 = $mysqli->query($sql1)) {
                foreach ($res1 as $Reg1) {
                    // Actualiza “lider” al equipo del registro actual para el siguiente descenso
                    $liderIter = (int)$Reg1['Equipo'];
                    $unidades_vendidas += $countStatus((string)$Reg1['IdUsuario'], $statusNombre);
                }
            }
            $a--;
        }
    }

    $data['rows'][] = [
        'c' => [
            ['v' => $statusNombre],
            ['v' => (int)$unidades_vendidas]
        ]
    ];
}

/* ========= 4) Salida JSON ========= */
echo json_encode($data, JSON_UNESCAPED_UNICODE);
$stmtCount->close();
