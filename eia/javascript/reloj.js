"use strict";

/**
 * Inicia el reloj y la fecha en la página.
 * Actualiza periódicamente (cada 500 ms) el contenido de los elementos con id "clock" y "date".
 */
function startTime() {
    // Obtenemos la fecha y hora actuales
    const today = new Date();
    let hr = today.getHours();
    let min = today.getMinutes();
    let sec = today.getSeconds();

    // Determinamos si es AM o PM
    const ap = (hr < 12) ? "<span>AM</span>" : "<span>PM</span>";

    // Convertimos la hora al formato 12 horas:
    // Si es 0 (medianoche), la mostramos como 12; si es mayor a 12, se le resta 12.
    hr = (hr === 0) ? 12 : (hr > 12 ? hr - 12 : hr);

    // Agregamos un cero delante si el número es menor que 10
    hr = checkTime(hr);
    min = checkTime(min);
    sec = checkTime(sec);

    // Actualizamos el elemento con id "clock" con la hora formateada
    document.getElementById("clock").innerHTML = `${hr}:${min}:${sec} ${ap}`;

    // Definimos los arrays con los nombres de los meses y días de la semana
    const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

    // Obtenemos la información del día actual
    const curWeekDay = days[today.getDay()];
    const curDay = today.getDate();
    const curMonth = months[today.getMonth()];
    const curYear = today.getFullYear();

    // Formateamos la fecha en el formato "Día, DD Mes AAAA"
    const dateStr = `${curWeekDay}, ${curDay} ${curMonth} ${curYear}`;

    // Actualizamos el elemento con id "date" con la fecha formateada
    document.getElementById("date").innerHTML = dateStr;

    // Llama a startTime de nuevo después de 500 milisegundos para actualizar la hora
    setTimeout(startTime, 500);
}

/**
 * checkTime(i)
 * Añade un cero delante del número si es menor a 10 para mantener un formato de dos dígitos.
 *
 * @param {number} i - Número a formatear.
 * @returns {string} El número formateado como cadena de dos dígitos.
 */
function checkTime(i) {
    return (i < 10) ? "0" + i : i.toString();
}

// Inicia el reloj al cargar la página
startTime();
