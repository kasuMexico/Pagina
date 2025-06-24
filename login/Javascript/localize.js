function localize(){
    navigator.geolocation?navigator.geolocation.getCurrentPosition(mapa,error):
    alert("Tu navegador no soporta geolocalizacion.")
}
function mapa(o){
    var e=o.coords.latitude,
        t=o.coords.longitude,
        n=o.coords.accuracy;
    document.getElementById("map");
    document.getElementById("Gps").innerHTML="\
    <input name='Latitud' type='hidden' value='"+e+"'>\
    <input name='Longitud' type='hidden' value='"+t+"' >\
    <input name='Presicion' type='hidden' value='"+n+"'>"
}
function error(o){
    1==o.code?alert("No has permitido buscar tu localizacion"):2==o.code?alert("Posicion no disponible"):alert("Ha ocurrido un error")
}
