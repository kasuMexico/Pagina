"use strict";

/**
 * Crea y retorna un objeto XMLHttpRequest compatible con el navegador.
 */
function objetoAjax() {
    if (window.XMLHttpRequest) {
        return new XMLHttpRequest();
    } else if (window.ActiveXObject) {
        try {
            return new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
            try {
                return new ActiveXObject("Microsoft.XMLHTTP");
            } catch (E) {
                return null;
            }
        }
    }
    return null;
}

/**
 * Función enviarDatos:
 * - Utiliza la función Fingerprint definida en finger.js para generar una huella digital única.
 * - Inserta diversos datos (fingerprints) en el elemento con id "FingerPrint".
 * - Recolecta los datos de un formulario y los envía vía AJAX al archivo 'eia/consulta.php'.
 */
function enviarDatos() {
    // Se asume que Fingerprint (ya corregido) está definido en finger.js, el cual debe estar incluido antes.
    const fp = new Fingerprint({
        canvas: true,
        ie_activex: true,
        screen_resolution: true
    });
    const uid = fp.get();

    // Actualiza el elemento "FingerPrint" con inputs que muestran los datos recolectados.
    const fingerEl = document.getElementById("FingerPrint");
    if (fingerEl) {
        fingerEl.innerHTML = `
            <input name="fingerprint" type="text" id="fingerprint" value="${uid}">
            <input name="browser" type="text" id="browser" value="${fingerprint_browser()}">
            <input name="flash" type="text" id="flash" value="${fingerprint_flash()}">
            <input name="canvas" type="text" id="canvas" value="${fingerprint_canvas()}">
            <input name="connection" type="text" id="connection" value="${fingerprint_connection()}">
            <input name="cookie" type="text" id="cookie" value="${fingerprint_cookie()}">
            <input name="display" type="text" id="display" value="${fingerprint_display()}">
            <input name="fontsmoothing" type="text" id="fontsmoothing" value="${fingerprint_fontsmoothing()}">
            <input name="fonts" type="text" id="fonts" value="${fingerprint_fonts()}">
            <input name="formfields" type="text" id="formfields" value="${fingerprint_formfields()}">
            <input name="java" type="text" id="java" value="${fingerprint_java()}">
            <input name="language" type="text" id="language" value="${fingerprint_language()}">
            <input name="silverlight" type="text" id="silverlight" value="${fingerprint_silverlight()}">
            <input name="os" type="text" id="os" value="${fingerprint_os()}">
            <input name="timezone" type="text" id="timezone" value="${fingerprint_timezone()}">
            <input name="touch" type="text" id="touch" value="${fingerprint_touch()}">
            <input name="plugins" type="text" id="plugins" value="${fingerprint_plugins()}">
            <input name="useragent" type="text" id="useragent" value="${fingerprint_useragent()}">
            <input name="truebrowser" type="text" id="truebrowser" value="${fingerprint_truebrowser()}">
        `;
    }

    // Se crea un objeto AJAX para enviar los datos
    const ajax = objetoAjax();

    // Configura la función que se ejecutará cuando cambie el estado del objeto AJAX.
    ajax.onreadystatechange = function() {
        if (ajax.readyState === 4) {
            const regActEl = document.getElementById("RegAct");
            if (regActEl) {
                // Se asigna el contenido de respuesta al elemento (por ejemplo, para mostrar el resultado de consulta.php).
                regActEl.value = ajax.responseText;
            }
        }
    };

    // Abre una conexión POST al archivo 'eia/consulta.php'
    ajax.open('POST', 'eia/consulta.php', true);
    // Cuando se envían datos mediante FormData, no es necesario establecer manualmente el header 'Content-Type'
    // ya que el navegador lo configura automáticamente.

    // Se obtiene el formulario con nombre "formulario"
    const form = document.querySelector('form[name="formulario"]');
    const formData = new FormData(form);

    // Se agregan al FormData los valores de fingerprint generados por las funciones
    formData.set('fingerprint_browser', fingerprint_browser());
    formData.set('fingerprint_flash', fingerprint_flash());
    formData.set('fingerprint_canvas', fingerprint_canvas());
    formData.set('fingerprint_connection', fingerprint_connection());
    formData.set('fingerprint_cookie', fingerprint_cookie());
    formData.set('fingerprint_display', fingerprint_display());
    formData.set('fingerprint_fontsmoothing', fingerprint_fontsmoothing());
    formData.set('fingerprint_fonts', fingerprint_fonts());
    formData.set('fingerprint_formfields', fingerprint_formfields());
    formData.set('fingerprint_java', fingerprint_java());
    formData.set('fingerprint_language', fingerprint_language());
    formData.set('fingerprint_silverlight', fingerprint_silverlight());
    formData.set('fingerprint_os', fingerprint_os());
    formData.set('fingerprint_timezone', fingerprint_timezone());
    formData.set('fingerprint_touch', fingerprint_touch());
    formData.set('fingerprint_plugins', fingerprint_plugins());
    formData.set('fingerprint_useragent', fingerprint_useragent());
    formData.set('fingerprint_truebrowser', fingerprint_truebrowser());

    // Envía los datos al servidor
    ajax.send(formData);
}
