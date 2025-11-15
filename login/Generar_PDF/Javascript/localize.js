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
    <input name='latitud' type='text' value='"+e+"'>\
    <input name='longitud' type='text' value='"+t+"' >\
    <input name='accuracy' type='text' value='"+n+"'>"
}
function error(o){
    1==o.code?alert("No has permitido buscar tu localizacion"):2==o.code?alert("Posicion no disponible"):alert("Ha ocurrido un error")
}
