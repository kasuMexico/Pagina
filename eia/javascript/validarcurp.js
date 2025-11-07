"use strict";

/**
 * Valida el formato de una CURP y su dígito verificador.
 * @param {string} curp - La CURP a validar.
 * @returns {boolean} true si la CURP es válida, false de lo contrario.
 */
function curpValida(curp) {
  // Expresión regular para validar el formato general de la CURP:
  const re = /^([A-Z][AEIOUX][A-Z]{2}\d{2}(?:0[1-9]|1[0-2])(?:0[1-9]|[12]\d|3[01])[HM](?:AS|B[CS]|C[CLMSH]|D[FG]|G[TR]|HG|JC|M[CNS]|N[ETL]|OC|PL|Q[TR]|S[PLR]|T[CSL]|VZ|YN|ZS)[B-DF-HJ-NP-TV-Z]{3}[A-Z\d])(\d)$/;
  const validado = curp.match(re);
  if (!validado) {
    // No coincide con el formato general
    return false;
  }

  /**
   * Función interna para calcular el dígito verificador a partir de los primeros 17 caracteres.
   * @param {string} curp17 - Los primeros 17 caracteres de la CURP.
   * @returns {number} El dígito verificador calculado.
   */
  const digitoVerificador = (curp17) => {
    const diccionario = "0123456789ABCDEFGHIJKLMNÑOPQRSTUVWXYZ";
    let lngSuma = 0;
    for (let i = 0; i < 17; i++) {
      lngSuma += diccionario.indexOf(curp17.charAt(i)) * (18 - i);
    }
    let lngDigito = 10 - (lngSuma % 10);
    if (lngDigito === 10) return 0;
    return lngDigito;
  };

  // Compara el dígito verificador capturado (validado[2]) con el calculado a partir de validado[1]
  if (parseInt(validado[2], 10) !== digitoVerificador(validado[1])) {
    return false;
  }
  return true;
}

/**
 * Handler para validar la CURP en el formulario.
 * Convierte la entrada a mayúsculas, la valida y actualiza el elemento "resultado".
 * Si la CURP es válida, llama a buscarCurp para obtener datos adicionales.
 *
 * @param {HTMLInputElement} input - Elemento de entrada que contiene la CURP.
 */
function validarInput(input) {
  const curp = input.value.toUpperCase();
  const resultado = document.getElementById("resultado");
  let valido = "No válido";
  
  if (curpValida(curp)) { // Se valida la CURP
    valido = "Válido";
    resultado.classList.add("ok");
    // Llama a la función para buscar información asociada a la CURP
    buscarCurp(curp);
  } else {
    resultado.classList.remove("ok");
  }
  resultado.innerText = `${curp}\nCURP: ${valido}`;
}

/**
 * Handler similar a validarInput, pero actualiza el elemento "resultadoVta".
 *
 * @param {HTMLInputElement} input - Elemento de entrada que contiene la CURP.
 */
function validarInputVta(input) {
  const curp = input.value.toUpperCase();
  const resultado = document.getElementById("resultadoVta");
  let valido = "No válido";
  
  if (curpValida(curp)) {
    valido = "Válido";
    resultado.classList.add("ok");
  } else {
    resultado.classList.remove("ok");
  }
  resultado.innerText = `${curp}\nCURP: ${valido}`;
}

/**
 * Función asíncrona para buscar datos asociados a la CURP mediante una solicitud fetch.
 * Si la respuesta es correcta, actualiza el contenido del elemento "nombre_completo".
 *
 * @param {string} curp - La CURP a consultar.
 */
async function buscarCurp(curp) {
  let nombre = 'Sin Datos';
  try {
    const response = await fetch(`https://conectame.ddns.net/rest/api.php?m=curp&user=Kasu&pass=1234567890&val=${curp}`, {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
        "Accept": "application/json"
      },
      credentials: "same-origin"
    });
    const data = await response.json();
    if (data.Response === 'correct') {
      nombre = `${data.Nombre} ${data.Paterno} ${data.Materno}`;
    }
  } catch (error) {
    console.error("Error al buscar CURP:", error);
  }
  const nombre_c = document.getElementById("nombre_completo");
  if (nombre_c) {
    nombre_c.innerText = nombre;
  }
}
