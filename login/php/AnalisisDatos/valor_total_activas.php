<?php
declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/eia/session.php';
kasu_session_start();
require_once __DIR__ . '/../../../eia/librerias.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
header('Content-Type: application/json; charset=utf-8');

function normalize_date(string $value, string $fallback): string {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
        return $fallback;
    }
    return $value;
}

try {
    if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
        throw new RuntimeException('DB no inicializada');
    }
    $mysqli->set_charset('utf8mb4');

    $today = date('Y-m-d');
    $ini = normalize_date(
        filter_input(INPUT_GET, 'ini', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: $today,
        $today
    );
    $fin = normalize_date(
        filter_input(INPUT_GET, 'fin', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: $today,
        $today
    );
    if ($ini > $fin) {
        [$ini, $fin] = [$fin, $ini];
    }
    $iniFull = $ini . ' 00:00:00';
    $finFull = $fin . ' 23:59:59';

    $stmt = $mysqli->prepare(
        "SELECT Producto, COALESCE(SUM(CostoVenta),0) AS total
         FROM Venta
         WHERE Status = 'ACTIVO' AND FechaRegistro BETWEEN ? AND ?
         GROUP BY Producto
         ORDER BY total DESC"
    );
    $stmt->bind_param('ss', $iniFull, $finFull);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [
        'cols' => [
            ['label' => 'Concepto', 'type' => 'string'],
            ['label' => 'Monto (MXN)', 'type' => 'number'],
        ],
        'rows' => [],
    ];

    while ($row = $result->fetch_assoc()) {
        $producto = (string)$row['Producto'];
        $total = (float)$row['total'];
        $data['rows'][] = [
            'c' => [
                ['v' => $producto ?: 'Sin producto'],
                ['v' => $total],
            ],
        ];
    }

    $stmt->close();

    echo json_encode($data, JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'No se pudo calcular el valor total.',
        'detail' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}
