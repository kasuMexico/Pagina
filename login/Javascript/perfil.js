// /login/Javascript/perfil.js
(function () {
  var form = document.getElementById('perfilForm');
  var file = document.getElementById('subirImg');
  var img  = document.getElementById('FotoPerfil');
  var btn  = document.getElementById('btnFoto');
  var uploading = false;

  if (!form || !file || !img) {
    return;
  }

  function triggerPicker() {
    if (!uploading) {
      file.click();
    }
  }

  img.addEventListener('click', triggerPicker);
  if (btn) {
    btn.addEventListener('click', triggerPicker);
  }

  file.addEventListener('change', function () {
    if (!file.files || !file.files[0] || uploading) return;

    var reader = new FileReader();
    reader.onload = function (evt) {
      if (evt.target && evt.target.result) {
        img.src = evt.target.result;
        img.classList.add('updating');
      }
    };
    reader.readAsDataURL(file.files[0]);

    uploading = true;
    form.submit();
  });
})();
