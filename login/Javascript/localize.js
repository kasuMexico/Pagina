"use strict";

const GPS_SELECTOR = "#Gps";
const GEO_OPTIONS = {
    enableHighAccuracy: true,
    timeout: 10000,
    maximumAge: 0
};

/**
 * Verifica si existe al menos un contenedor #Gps en la página y, en caso afirmativo,
 * solicita la geolocalización para inyectar los datos. Evita pedir permisos si la
 * vista no utiliza geolocalización.
 */
function localize() {
    if (!document.querySelector(GPS_SELECTOR)) {
        return;
    }

    if (!navigator.geolocation) {
        alert("Tu navegador no soporta geolocalización.");
        return;
    }

    navigator.geolocation.getCurrentPosition(handlePosition, handleError, GEO_OPTIONS);
}

/**
 * Actualiza todos los contenedores #Gps visibles con los datos obtenidos.
 * @param {GeolocationPosition} position
 */
function handlePosition(position) {
    const latitud = position.coords.latitude;
    const longitud = position.coords.longitude;
    const accuracy = position.coords.accuracy;
    const slots = document.querySelectorAll(GPS_SELECTOR);

    if (!slots.length) {
        return;
    }

    const hiddenInputs = `
        <input name="latitud" type="hidden" value="${latitud}">
        <input name="longitud" type="hidden" value="${longitud}">
        <input name="accuracy" type="hidden" value="${accuracy}">
    `;

    slots.forEach((slot) => {
        slot.innerHTML = hiddenInputs;
    });
}

/**
 * Maneja los diferentes escenarios de error de la API de geolocalización.
 * @param {GeolocationPositionError} error
 */
function handleError(error) {
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
