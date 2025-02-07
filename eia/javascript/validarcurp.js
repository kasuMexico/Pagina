    //Función para validar una CURP
    function curpValida(curp) {
        var re = /^([A-Z][AEIOUX][A-Z]{2}\d{2}(?:0[1-9]|1[0-2])(?:0[1-9]|[12]\d|3[01])[HM](?:AS|B[CS]|C[CLMSH]|D[FG]|G[TR]|HG|JC|M[CNS]|N[ETL]|OC|PL|Q[TR]|S[PLR]|T[CSL]|VZ|YN|ZS)[B-DF-HJ-NP-TV-Z]{3}[A-Z\d])(\d)$/,
            validado = curp.match(re);
        if (!validado)  //Coincide con el formato general?
            return false;
        //Validar que coincida el dígito verificador
        function digitoVerificador(curp17) {
            //Fuente https://consultas.curp.gob.mx/CurpSP/
            var diccionario  = "0123456789ABCDEFGHIJKLMNÑOPQRSTUVWXYZ",
                lngSuma      = 0.0,
                lngDigito    = 0.0;
            for(var i=0; i<17; i++)
                lngSuma = lngSuma + diccionario.indexOf(curp17.charAt(i)) * (18 - i);
            lngDigito = 10 - lngSuma % 10;
            if (lngDigito == 10) return 0;
            return lngDigito;
        }
        if (validado[2] != digitoVerificador(validado[1]))
            return false;
        return true; //Validado
    }
//Handler para el evento cuando cambia el input
//Lleva la CURP a mayúsculas para validarlo
    function validarInput(input) {
        var curp = input.value.toUpperCase(),
            resultado = document.getElementById("resultado"),
            valido = "No válido";
        if (curpValida(curp)) { // ⬅️ Acá se comprueba
            valido = "Válido";
            resultado.classList.add("ok");
            buscarCurp(curp);
        } else {
            resultado.classList.remove("ok");
        }
        resultado.innerText = curp + "\nCURP: " + valido;
    }
//Lleva la CURP a mayúsculas para validarlo
    function validarInputVta(input) {
        var curp = input.value.toUpperCase(),
            resultado = document.getElementById("resultadoVta"),
            valido = "No válido";
        if (curpValida(curp)) { // ⬅️ Acá se comprueba
            valido = "Válido";
            resultado.classList.add("ok");
        } else {
            resultado.classList.remove("ok");
        }
        resultado.innerText = curp + "\nCURP: " + valido;
    }


//buscar curp
async function buscarCurp(curp){
  let nombre = 'Sin Datos';
  const response = await fetch("https://conectame.ddns.net/rest/api.php?m=curp&user=Kasu&pass=1234567890&val="+curp, {
    method: "get",
    headers: {
      "Content-Type": "application/json",
			"Accept": "application/json",
    },
    credentials: "same-origin",
  })
  const data = await response.json();
  if(data.Response == 'correct'){
    nombre = data.Nombre+' '+data.Paterno+' '+data.Materno;
  }
  var nombre_c = document.getElementById("nombre_completo");
  nombre_c.innerText = nombre;
}
