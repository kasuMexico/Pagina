"use strict";

/**
 * Función OcuForCurp
 * 
 * Muestra u oculta secciones del formulario en función del valor del elemento.
 * Si el valor es "RegCurBen", se muestra el div con id "RegCurBen" y se oculta "RegCurCli".
 * En caso contrario, se oculta "RegCurBen" y se muestra "RegCurCli".
 *
 * @param {HTMLElement} e - Elemento (por ejemplo, un select) con propiedad value.
 */
function OcuForCurp(e) {
    // Obtenemos los elementos de destino
    const divCurBen = document.getElementById("RegCurBen");
    const divCurCli = document.getElementById("RegCurCli");

    // Si el valor es "RegCurBen", mostramos el div correspondiente y ocultamos el otro
    if (e.value === "RegCurBen") {
        if (divCurBen) divCurBen.style.display = "";
        if (divCurCli) divCurCli.style.display = "none";
    } else {
        if (divCurBen) divCurBen.style.display = "none";
        if (divCurCli) divCurCli.style.display = "";
    }
}

/**
 * Función CalPre
 *
 * Calcula el precio de una póliza según el número de pagos indicado en e.value.
 * Se utilizan variables globales (IntPhp y CostPhp) para la tasa de interés y el costo del producto.
 * Si e.value es "0", se considera que es un pago único; de lo contrario se calcula el pago mensual.
 * Luego, se actualiza el contenido del elemento con id "PagosCosto" con los resultados.
 *
 * @param {HTMLElement} e - Elemento de entrada (por ejemplo, un select) que determina el número de pagos.
 */
function CalPre(e) {
    // Se espera que IntPhp y CostPhp estén definidas en el ámbito global
    const IntB100 = parseFloat(IntPhp);
    const Cost = parseInt(CostPhp, 10);
    const TaInAn = IntB100 / 100; // Tasa anual en formato decimal
    const TaInMen = TaInAn / 12;  // Tasa mensual

    let Vpag, Tiempo, BaseA, BaseB, TaReXi, CuaVar, ValRes, BaseD, BaseC, Np, c, d;

    if (e.value === "0") {
        // Caso de pago único: no se calcula intereses mensuales
        Vpag = "1 solo Pago de";
        Np = "";
        c = Cost;
        d = Cost;
    } else {
        Vpag = "Meses de";
        Tiempo = parseInt(e.value, 10);
        BaseA = TaInMen + 1;                   // Suma 1 a la tasa mensual
        BaseB = Math.pow(BaseA, Tiempo);        // Factor acumulado en el tiempo
        TaReXi = BaseB * TaInMen;               // Tasa real a aplicar
        CuaVar = TaReXi * Cost;                 // Valor base multiplicado por el costo
        ValRes = BaseB - 1;                     // Resta para determinar el factor de cuota
        BaseD = CuaVar / ValRes;                // Cuota mensual
        BaseC = BaseD * Tiempo;                 // Total a pagar en el periodo
        Np = Tiempo;
        c = BaseC.toFixed(2);
        d = BaseD.toFixed(2);
    }

    // Actualiza el contenido del elemento "PagosCosto" con el resultado de los cálculos
    const pagosCostoEl = document.getElementById("PagosCosto");
    if (pagosCostoEl) {
        pagosCostoEl.innerHTML = `
            <h3 class="derTit">${Np} ${Vpag}</h3>
            <h3 class="IzqTit">$ ${d}</h3>
            <p class="derTit">Total a pagar</p>
            <h4 class="IzqTit">$ ${c} Mxn</h4>
            <input type="text" name="Subtotal" value="${c}" style="display: none;">
            <input type="text" name="Pagos" value="${d}" style="display: none;">
        `;
    }
}

/**
 * Función validate
 *
 * Verifica que se haya seleccionado al menos un producto (input con name "Producto")
 * en el formulario con id "form". Si no se selecciona ningún elemento, se muestra una alerta
 * y se previene el envío del formulario.
 *
 * @param {Event} e - Objeto del evento de envío del formulario.
 */
function validate(e) {
    const form = document.getElementById("form");
    let selected = false;

    if (form && form.Producto) {
        // form.Producto puede ser un array de inputs
        for (let i = 0; i < form.Producto.length; i++) {
            if (form.Producto[i].checked) {
                selected = true;
                break;
            }
        }
    }
    if (!selected) {
        alert("Debes seleccionar un Servicio para poder continuar");
        // Previene el envío del formulario
        if (e.preventDefault) {
            e.preventDefault();
        } else {
            e.returnValue = false;
        }
    }
}
