"use strict";

/**
 * Función localize:
 * Verifica si el navegador soporta geolocalización y, en caso afirmativo, solicita la posición actual.
 * Si no es compatible, muestra una alerta informando al usuario.
 */
function localize() {
    if (navigator.geolocation) {
        // Solicita la posición actual, pasando dos funciones de callback:
        // - handlePosition: se ejecuta si se obtiene la posición correctamente.
        // - handleError: se ejecuta si ocurre algún error.
        navigator.geolocation.getCurrentPosition(handlePosition, handleError);
    } else {
        alert("Tu navegador no soporta geolocalización.");
    }
}

/**
 * Función handlePosition:
 * Maneja el objeto de posición obtenido por la API de geolocalización y actualiza el contenido de la página.
 *
 * @param {GeolocationPosition} position - Objeto que contiene las coordenadas y precisión.
 */
function handlePosition(position) {
    // Extrae las coordenadas y la precisión
    const latitud = position.coords.latitude;
    const longitud = position.coords.longitude;
    const accuracy = position.coords.accuracy;

    // Se obtiene el elemento donde se mostrarán los datos de la geolocalización.
    const gpsElement = document.getElementById("Gps");
    if (gpsElement) {
        // Se inserta en el HTML tres inputs que muestran latitud, longitud y precisión.
        gpsElement.innerHTML = `
            <input name="latitud" type="hidden" value="${latitud}">
            <input name="longitud" type="hidden" value="${longitud}">
            <input name="accuracy" type="hidden" value="${accuracy}">
        `;
    }
}

/**
 * Función handleError:
 * Se encarga de manejar los errores generados al intentar obtener la geolocalización y mostrar un mensaje acorde.
 *
 * @param {GeolocationPositionError} error - Objeto de error con el código de fallo.
 */
function handleError(error) {
    // Utilizamos una estructura switch para identificar el error según su código.
    switch (error.code) {
        case error.PERMISSION_DENIED:
            alert("No has permitido buscar tu localización.");
            break;
        case error.POSITION_UNAVAILABLE:
            alert("Posición no disponible.");
            break;
        case error.TIMEOUT:
            alert("La solicitud para obtener la localización ha expirado.");
            break;
        default:
            alert("Ha ocurrido un error al obtener la localización.");
            break;
    }
}
