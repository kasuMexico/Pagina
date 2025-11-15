(function () {
  function injectGPS(slot, pos){
    if (!slot) return;
    var latitude = pos.coords.latitude;
    var longitud = pos.coords.longitude;
    var accuracy = pos.coords.accuracy;
    var ts  = Date.now();
    slot.innerHTML =
      "<input type='hidden' name='latitud' value='"+latitude+"'>" +
      "<input type='hidden' name='longitud' value='"+longitud+"'>" +
      "<input type='hidden' name='accuracy' value='"+accuracy+"'>" +
      "<input type='hidden' name='GeoTS' value='"+ts+"'>";
  }

  function requestAndSubmit(form, slot){
    if (!navigator.geolocation) { form.submit(); return; }
    navigator.geolocation.getCurrentPosition(
      function(pos){ injectGPS(slot, pos); form.submit(); },
      function(){ form.submit(); },
      { enableHighAccuracy:true, maximumAge:0, timeout:10000 }
    );
  }

  // Intercepta TODOS los formularios de la página
  document.addEventListener('submit', function(ev){
    var form = ev.target;
    if (!(form instanceof HTMLFormElement)) return;
    var slot = form.querySelector('[data-gps-slot]');
    if (!slot) return;              // este form no usa GPS
    // Si ya tiene campos GPS, no detengas
    if (slot.querySelector('input[name="latitud"], input[name="Lat"]')) return;

    ev.preventDefault();            // espera GPS y luego envía
    requestAndSubmit(form, slot);
  });
})();
