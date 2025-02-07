function startTime(hraIni,milDes,texto) {
    if(hraIni != ""){
        var hEnt = new Date(hraIni);
        var hAct = new Date();
        var mils = hAct-hEnt-milDes;
        var sali = document.getElementById("salida");
        if(texto == "Entrada"){
            document.getElementById("clock").innerHTML = "TEAM KASU";
        }else if(texto == "Descanso"){
            document.getElementById("clock").innerHTML = "Llevas "+mil_HMS(mils)+" Horas";
            (mil_H(mils)<7?sali.value = "Salida E":sali.value = "Salida")
            var time = setTimeout(function(){ startTime(hraIni,milDes,texto) }, 1000);
        }else{
            document.getElementById("clock").innerHTML = "Llevas "+mil_HMS(mils)+" Horas";
            (mil_H(mils)<7?sali.value = "Salida E":sali.value = "Salida")
        }
    }else{
        document.getElementById("clock").innerHTML = "¡Bienvenido!";
    }
}
function mil_HMS(s){
    var ms = s % 1000;
    s = (s - ms) / 1000;
    var secs = s % 60;
    s = (s - secs) / 60;
    var mins = s % 60;
    var hrs = (s - mins) / 60;
    return addZ(hrs) + ':' + addZ(mins) + ':'+ addZ(secs);
}
function mil_H(s){
    var ms = s % 1000;
    s = (s - ms) / 1000;
    var secs = s % 60;
    s = (s - secs) / 60;
    var mins = s % 60;
    var hrs = (s - mins) / 60;
    return hrs;
}
function addZ(n) {
	    return (n<10? '0':'') + n;
}
function cambiar(){
    var pdrs = document.getElementById('subirImg').files[0].name;
    document.getElementById('info').innerHTML = pdrs +"<br><input class='enviarFoto' type='submit' name='btnEnviar' value='Enviar'>";
}

function mostrarUbicacionEnt(ubicacion){
    var latMax = 19.798689;
    var latMin = 19.798416;
    var lonMax = -99.873839;
    var lonMin = -99.873389;
    var lat = ubicacion.coords.latitude;
    var lon = ubicacion.coords.longitude;
    if(!(lat<latMax && lat>latMin && lon>lonMax && lon<lonMin)){
        alert("Entrada registrada fuera de oficina");
    }
}
function mostrarUbicacionSal(ubicacion){
    var latMax = 19.798689;
    var latMin = 19.798416;
    var lonMax = -99.873839;
    var lonMin = -99.873389;
    var lat = ubicacion.coords.latitude;
    var lon = ubicacion.coords.longitude;
    if(!(lat<latMax && lat>latMin && lon>lonMax && lon<lonMin)){
        alert("Salida registrada fuera de oficina");
    }
}
function validarLocEnt(){
    var val1 = document.getElementById("entrada").value;
    var form = document.getElementById("form2");
    form.onsubmit = function(e){
        if(val1 == "Entrada"){
         navigator.geolocation.getCurrentPosition(mostrarUbicacionEnt);
        }else if(val1 == "Descanso"){
            var respuesta = confirm("¿Deseas registrar un descanso?");
            if(!respuesta)
                e.preventDefault();
        }
    }
}

function validarLocSal(){
    var val1 = document.getElementById("entrada").value;
    var val2 = document.getElementById("salida").value;
    var form = document.getElementById("form2");
    form.onsubmit = function(e){
        if(val2 == "Salida E" && val1 != "Oficina"){
            var respuesta = confirm("¿Desea registrar su salida como emergencia?");
            if(respuesta){
               if(navigator.geolocation){
                   navigator.geolocation.getCurrentPosition(mostrarUbicacionSal);
               }
            }else{
                e.preventDefault();
            }
        }else if(val2 == "Salida" && val1 != "Oficina"){
            var respuesta = confirm("¿Deseas registrar su salida?");
            if(respuesta){
               if(navigator.geolocation){
                   navigator.geolocation.getCurrentPosition(mostrarUbicacionSal);
               }
            }else{
                e.preventDefault();
            }
        }else{
            alert("Registre primero su llegada del descanso");
            e.preventDefault();
        }
    }
}
