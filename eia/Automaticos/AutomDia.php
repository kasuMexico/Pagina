<?php
session_start();

// Incluir el archivo de funciones (se asume que Funciones_kasu.php carga o require_once
require_once '../librerias.php';




//Esto debe ir en regstrar pago para que si esta pagado completamente se envie un correo


//Valida que la venta se encuentre pagada en su totalidad y envia el correo de forma automatica
if($Vta_Liquidada == "ACTIVA"){

    }