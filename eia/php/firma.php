<?php
session_start();
date_default_timezone_set('America/Mexico_City');

// Se incluye el archivo que carga las clases y funciones necesarias.
require_once 'Funciones_kasu.php';

// Se obtienen los parámetros GET
$id = isset($_GET['n']) ? $_GET['n'] : null;
$PromesaPago = isset($_GET['m']) ? $_GET['m'] : null;

if (!$id || !$PromesaPago) {
    die("Parámetros insuficientes.");
}

// Se define la fecha de promesa de pago en dos formatos:
// - $data: en formato "d-m-Y" (para mostrar en el PDF)
// - $fecha: la fecha actual en formato "Y-m-d" (si es necesaria para otros cálculos)
$data = date("d-m-Y", strtotime($PromesaPago));
$fecha = date("Y-m-d");

// Definir el arreglo de campos para la consulta de datos de venta.
$campos = [
    "Id"            => "Id",
    "IdContact"     => "IdContact",
    "Nombre"        => "Nombre",
    "TipoServicio"  => "TipoServicio",
    "Subtotal"      => "Subtotal",
    "NumeroPagos"   => "NumeroPagos",
    "IdFIrma"       => "IdFIrma"
];

// Se obtiene la información del cliente mediante la función PDF::Datos
$dataCte = PDF::Datos($mysqli, "Venta", $campos, "IdContact", $id);

if (!$dataCte) {
    die("No se encontraron datos para el cliente.");
}

// Si el número de pagos es 0 o 1, se procesa como pago único, de lo contrario como crédito.
if ($dataCte['NumeroPagos'] == 0 || $dataCte['NumeroPagos'] == 1) {
    echo processPagoUnico($dataCte, $data);
} else {
    echo processPagoCredito($dataCte, $data, $PromesaPago);
}


/**
 * Procesa el escenario de pago único.
 * 
 * - Suma 3 días a la fecha de promesa para definir la fecha con mora.
 * - Calcula el pago y la mora (para pago único, se divide el subtotal entre 1).
 * - Llama a PDF::Formato para generar el formato final.
 *
 * @param array $dataCte Datos obtenidos de la venta.
 * @param string $promesaFecha Fecha de promesa de pago en formato "d-m-Y".
 * @return string Resultado (por ejemplo, el formato PDF o mensaje de confirmación).
 */
function processPagoUnico($dataCte, $promesaFecha) {
    // Variables para pago único: se considera un pago (sin división) y una tasa de interés aplicada.
    $contado = 1;
    $NumPag = 1;
    $div = 1;
    
    // Crear objeto de fecha a partir de la fecha de promesa
    $fechaObj = date_create($promesaFecha);
    // Sumar 3 días para ajustar la fecha (mora)
    date_modify($fechaObj, "+3 days");
    $fechaConMora = date_format($fechaObj, "d-m-Y");

    // Creamos un arreglo para almacenar la fecha original y la fecha con mora.
    $datos = [];
    $datos[1]    = $promesaFecha;    // Fecha original
    $datos["1c"] = $fechaConMora;      // Fecha con mora aplicada

    // Calcula el pago y la mora. En pago único, ambos se basan en el subtotal.
    $pago = round($dataCte['Subtotal'] / ($div * $contado));
    $mora = round(($dataCte['Subtotal'] / ($div * $contado)) * 1.1);

    // Genera el formato PDF utilizando la función de la clase PDF.
    $formato = PDF::Formato(
        $dataCte["Nombre"],
        $dataCte["IdContact"],
        $dataCte["TipoServicio"],
        $NumPag,
        $datos[1],
        $pago,
        $datos["1c"],
        $mora,
        $dataCte["IdFIrma"]
    );

    return "Pago único generado. Formato PDF: " . $formato;
}


/**
 * Procesa el escenario de pago a crédito.
 * 
 * - Calcula el número total de pagos multiplicando el valor en la base de datos por 2.
 * - Para cada pago, se suma 15 días y se ajusta la fecha:
 *   * Si el día es entre 1 y 16, se fija a 15; si es mayor, se fija a 01 del siguiente mes.
 *   * Se suma 3 días para la fecha de mora.
 * - Se calcula la cuota (pago) y la mora usando el subtotal y el número de pagos.
 * - Finalmente, se genera el formato PDF con la función PDF::Formato.
 *
 * @param array $dataCte Datos de la venta.
 * @param string $promesaFecha Fecha de promesa en formato "d-m-Y".
 * @param string $PromesaPago Valor original de la promesa (para pasar a PDF::Formato si es necesario).
 * @return string Resultado final, por ejemplo, la longitud del formato PDF.
 */
function processPagoCredito($dataCte, $promesaFecha, $PromesaPago) {
    $credito = 2;
    // Número total de pagos es el doble del valor almacenado en la base de datos.
    $NumPag = $dataCte['NumeroPagos'] * 2;
    $div = $dataCte['NumeroPagos'];
    
    // Se crea un objeto de fecha a partir de la fecha de promesa.
    $FechaCredito = date_create($promesaFecha);
    $datos = [];
    $pago = 0;
    $mora = 0;

    // Bucle para calcular la fecha de cada pago.
    for ($i = 1; $i <= $NumPag; $i++) {
        // Sumar 15 días para cada pago.
        date_modify($FechaCredito, "+15 days");
        $FormatoFC = date_format($FechaCredito, "d-m-Y");
        
        // Extraer componentes de la fecha.
        $dateParts = date_parse($FormatoFC);
        $DI = $dateParts['day'];
        $ME = $dateParts['month'];
        $AN = $dateParts['year'];
        
        // En la primera iteración se puede asignar el mes en letras (opcional)
        if ($i === 1) {
            $MesLetra = PDF::Mes($ME);
        }
        
        // Ajuste de la fecha según el día:
        if ($DI >= 1 && $DI <= 16) {
            // Para días entre 1 y 16, se fija el día a 15.
            $DI = 15;
            $Mes = $ME;
            $Year = $AN;
            if ($Mes == 13) {
                $Mes = '01';
                $Year = $AN + 1;
            }
        } elseif ($DI >= 17) {
            // Para días mayores, se fija el día a 01 del siguiente mes.
            $DI = "01";
            $Mes = $ME + 1;
            $Year = $AN;
            if ($Mes == 13) {
                $Mes = '01';
                $Year = $AN + 1;
            }
        }
        
        // Definir la fecha para este pago.
        if ($i === 1) {
            $S = $promesaFecha; // Primera iteración: usa la fecha original.
        } else {
            $S = sprintf("%02d-%02d-%04d", $DI, $Mes, $Year);
        }
        
        // Calcula la fecha con mora sumando 3 días.
        $dm = date_create($S);
        date_modify($dm, "+3 days");
        $tre = date_format($dm, "d-m-Y");
        
        // Guarda las fechas en el arreglo.
        $datos[$i]    = $S;
        $datos[$i.'c'] = $tre;
        
        // Calcula la cuota mensual y la mora (estas variables se sobreescriben en cada iteración, se asume que el valor final es el deseado).
        $pago = round($dataCte['Subtotal'] / ($div * $credito));
        $mora = round(($dataCte['Subtotal'] / ($div * $credito)) * 1.1);
    }
    
    // Genera el formato PDF utilizando la última fecha calculada y otros datos.
    $formato = PDF::Formato(
        $dataCte["Nombre"],
        $dataCte["IdContact"],
        $dataCte["TipoServicio"],
        $NumPag,
        $datos[$i],
        $pago,
        $datos[$i.'c'],
        $mora,
        $dataCte["IdFIrma"],
        $PromesaPago
    );
    
    $fichas = strlen($formato);
    return "Pago a crédito generado. Longitud del formato PDF: " . $fichas;
}
?>
