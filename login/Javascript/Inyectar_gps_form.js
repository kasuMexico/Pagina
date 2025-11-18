"use strict";

(function () {
  var OPTIONS = { enableHighAccuracy: true, maximumAge: 0, timeout: 10000 };
  var pendingForms = [];
  var lastCoords = null;
  var isRequesting = false;

  function cloneCoords(position) {
    if (!position || !position.coords) return null;
    return {
      latitude: position.coords.latitude,
      longitude: position.coords.longitude,
      accuracy: position.coords.accuracy
    };
  }

  function ensureSlot(form) {
    var slot = form.querySelector('[data-gps-slot]');
    if (slot) return slot;
    slot = document.createElement('div');
    slot.style.display = 'none';
    slot.setAttribute('data-gps-slot', '1');
    form.appendChild(slot);
    return slot;
  }

  function ensureSlotsInContext(ctx) {
    var forms = (ctx || document).querySelectorAll('form');
    [].forEach.call(forms, ensureSlot);
  }

  function injectInputs(form, coords) {
    var slot = ensureSlot(form);
    if (!slot) {
      return;
    }
    var latitude = coords ? coords.latitude : '';
    var longitude = coords ? coords.longitude : '';
    var accuracy = coords ? coords.accuracy : '';
    var ts = Date.now();
    slot.innerHTML =
      "<input type='hidden' name='latitud' value='" + latitude + "'>" +
      "<input type='hidden' name='longitud' value='" + longitude + "'>" +
      "<input type='hidden' name='accuracy' value='" + accuracy + "'>" +
      "<input type='hidden' name='GeoTS' value='" + ts + "'>";
  }

  function flushQueue(coords) {
    while (pendingForms.length) {
      var form = pendingForms.shift();
      injectInputs(form, coords);
      form.dataset.gpsReady = '1';
      form.submit();
    }
    isRequesting = false;
  }

  function requestPosition() {
    if (lastCoords) {
      flushQueue(lastCoords);
      return;
    }
    if (!navigator.geolocation) {
      flushQueue(null);
      return;
    }
    isRequesting = true;
    navigator.geolocation.getCurrentPosition(
      function (pos) {
        lastCoords = cloneCoords(pos);
        flushQueue(lastCoords);
      },
      function () {
        flushQueue(null);
      },
      OPTIONS
    );
  }

  document.addEventListener('DOMContentLoaded', function () {
    ensureSlotsInContext(document);
  });

  window.ensureGpsSlots = ensureSlotsInContext;

  if (window.jQuery) {
    window.jQuery(document).on('shown.bs.modal', '.modal', function () {
      ensureSlotsInContext(this);
    });
  }

  document.addEventListener(
    "submit",
    function (ev) {
      var form = ev.target;
      if (!(form instanceof HTMLFormElement)) return;
      if (form.dataset.gpsSkip === "1") return;
      if (form.dataset.gpsReady === "1") return;
      if (form.querySelector('input[name="latitud"]')) return;

      ev.preventDefault();
      pendingForms.push(form);

      if (!isRequesting) {
        requestPosition();
      }
    },
    true
  );
})();
