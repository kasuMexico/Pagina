<?php
/**************************************************************************************************
 * ARCHIVO: c_Ventas_Totales.php
 * Qué hace: Genera un DataTable JSON con el TOTAL DE VENTAS (conteo de pólizas) por producto.
 * Fecha: 05/11/2025
 * Revisado por: JCCM
 *
 * Compatibilidad PHP 8.2:
 * - Uso de etiquetas completas <?php
 * - Manejo de errores con mysqli_report y try/catch
 * - Salida JSON UTF-8 con estructura Google DataTable (cols + rows)
 **************************************************************************************************/

declare(strict_types=1);

//Calcula el total de las polizas compradas por los clientes
//indicar que se inicia una sesion
session_start();
//inlcuir el archivo de funciones
require_once '../../../eia/librerias.php';

header('Content-Type: application/json; charset=utf-8');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
        throw new RuntimeException('DB no inicializada');
    }
    $mysqli->set_charset('utf8mb4');

    //create an array
    $data = [
        'cols' => [
            ['label' => 'Producto',      'type' => 'string'],
            ['label' => 'Total Ventas',  'type' => 'number'],
        ],
        'rows' => [],
    ];

    //Realizamos la busqueda de los status de la venta
    $sql1 = "SELECT Producto FROM Productos";
    //Realiza consulta
    $res1 = $mysqli->query($sql1);

    //Si existe el registro se asocia en un fetch_assoc
    foreach ($res1 as $Reg1) {
        $producto = (string)$Reg1['Producto'];
        //Se Suma las ventas de los Usuarios q tienen el Id del equipo
        // Nota: ConUno cuenta registros en tabla 'Venta' con columna 'Producto' = $producto
        $unidades_vendidas = (int)$basicas->ConUno($mysqli, 'Venta', 'Producto', $producto);

        //Insertamos el valor en el array
        $data['rows'][] = [
            'c' => [
                ['v' => $producto],
                ['v' => $unidades_vendidas],
            ],
        ];
    }

    echo json_encode($data, JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error'  => 'Error al generar datos.',
        'detail' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}
