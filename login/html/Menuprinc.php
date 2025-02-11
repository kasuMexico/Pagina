<?php
// Se inicializan las variables de estilo para cada botón del menú
$a1 = $a2 = $a3 = $a4 = $a5 = $a6 = $a7 = "";

// Se comprueba el nivel del usuario utilizando la función BuscarCampos de la clase Basicas
$Niv = $basicas->BuscarCampos($mysqli, "Nivel", "Empleados", "IdUsuario", $_SESSION["Vendedor"]);

// Se extrae una parte de la ruta actual para determinar en qué página se encuentra el usuario
$CoMn = substr($_SERVER['PHP_SELF'], 7, 20);

// Asigna estilos dependiendo de la página actual (el estilo deshabilita el botón actual)
if ($CoMn == "Pwa_Principal.php") {
    $a1 = "background: #D7BDE2; pointer-events: none; cursor: default;";
} elseif ($CoMn == "Pwa_Prospectos.php") {
    $a2 = "background: #D7BDE2; pointer-events: none; cursor: default;";
} elseif ($CoMn == "Pwa_Registro_Pagos.php") {
    $a3 = "background: #D7BDE2; pointer-events: none; cursor: default;";
} elseif ($CoMn == "Pwa_Clientes.php") {
    $a4 = "background: #D7BDE2; pointer-events: none; cursor: default;";
} elseif ($CoMn == "Mesa_Herramientas.php") {
    $a5 = "background: #D7BDE2; pointer-events: none; cursor: default;";
} elseif ($CoMn == "Pwa_Analisis_Ventas.php") {
    $a6 = "background: #D7BDE2; pointer-events: none; cursor: default;";
} elseif ($CoMn == "Pwa_Sociales.php") {
    $a7 = "background: #D7BDE2; pointer-events: none; cursor: default;";
}
?>
<div class="MenuPrincipal">
    <?php
    // Botón para la página principal
    echo '<a class="BtnMenu" href="Pwa_Principal.php"><img src="assets/img/FlorKasu.png" style="' . $a1 . '"></a>';

    // Si el nivel del usuario es 1, se muestra el botón para análisis de ventas
    if ($Niv == 1) {
        echo '<a class="BtnMenu" href="Pwa_Analisis_Ventas.php"><img src="assets/img/analisis.png" style="' . $a6 . '"></a>';
    }

    // Para niveles distintos de 5 y 3 se muestran botones de prospectos y sociales
    if ($Niv != 5 && $Niv != 3) {
        echo '
            <a class="BtnMenu" href="Pwa_Prospectos.php"><img src="assets/img/usuario.png" style="' . $a2 . '"></a>
            <!-- Opción de prospectos en Mesa (comentada) -->
            <a class="BtnMenu" href="Pwa_Sociales.php"><img src="assets/img/Sociales.png" style="' . $a7 . '"></a>
        ';
    }

    // Si el usuario no es de nivel 3 se muestra el botón para cartera de clientes
    if ($Niv != 3) {
        echo '<a class="BtnMenu" href="Pwa_Clientes.php"><img alt="cartera" src="assets/img/cartera.png" style="' . $a4 . '"></a>';
    }

    // Si el usuario no es de nivel 7 se muestra el botón para gestor de cobranza
    if ($Niv != 7) {
        echo '<a class="BtnMenu" href="Pwa_Registro_Pagos.php"><img src="assets/img/cobranza.png" style="' . $a3 . '"></a>';
    }

    // Botón para la página de ajustes (Mesa Herramientas)
    echo '<a class="BtnMenu" href="Mesa_Herramientas.php"><img src="assets/img/ajustes.png" style="' . $a5 . '"></a>';
    ?>
</div>
