<?php
// login/php/Selector_Emergentes_Ml.php

// Mostrar errores (sólo para desarrollo)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Obtener y sanear el parámetro 'Ml' de la URL
$ml = filter_input(INPUT_GET, 'Ml', FILTER_SANITIZE_NUMBER_INT);

// Si no viene Ml o no es un número válido, salimos sin hacer nada
if ($ml === null || $ml === false) {
    return;
}

// Mostrar el alert correspondiente
switch ((int)$ml) {
    case 1:
        echo "<script>alert('Tu mensaje para Ml=1');</script>";
        break;
    case 2:
        echo "<script>alert('Tu mensaje para Ml=2');</script>";
        break;
    case 3:
        echo "<script>alert('Tu mensaje para Ml=3');</script>";
        break;
    case 4:
        echo "<script>alert('Tu mensaje para Ml=4');</script>";
        break;
    default:
        // ningún mensaje por defecto
        break;
}

