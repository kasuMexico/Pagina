"use strict";

function localize(ctx){
  if (!("geolocation" in navigator)) { alert("Tu navegador no soporta geolocalización."); return; }
  if (!(location.protocol==="https:" || /^(localhost|127\.0\.0\.1)$/.test(location.hostname))) {
    alert("Geolocalización requiere HTTPS o localhost."); return;
  }
  navigator.geolocation.getCurrentPosition(function(pos){
    mapa(pos, ctx);
  }, error, {enableHighAccuracy:true, timeout:8000, maximumAge:30000});
}

function upsertHidden(parent, name, value){
  var el = parent.querySelector('input[name="'+name+'"]');
  if (!el) { el = document.createElement('input'); el.type='hidden'; el.name=name; parent.appendChild(el); }
  el.value = value;
}

function mapa(pos, ctx){
  var lat = pos.coords.latitude;
  var lon = pos.coords.longitude;
  var acc = Math.round(pos.coords.accuracy);

  var root   = ctx || document; // modal actual o documento
  var slot   = root.querySelector('[data-gps-slot]') || root.querySelector('#Gps');
  var target = slot || root.querySelector('form') || document.querySelector('form');
  if (!target) return;

  upsertHidden(target, "Latitud",   lat);
  upsertHidden(target, "Longitud",  lon);
  upsertHidden(target, "Precision", acc);   // nombre correcto
  upsertHidden(target, "Presicion", acc);   // alias legacy si tu backend lo usa
}

function error(err){
  switch (err && err.code) {
    case 1: alert("No has permitido buscar tu localización."); break;
    case 2: alert("Posición no disponible."); break;
    case 3: alert("La solicitud expiró."); break;
    default: alert("Ha ocurrido un error."); 
  }
}

window.localize = localize;
