//Abajo del archivo
$(document).ready(function() {
  $('#clear').click(function() {
    mdtoast('Restablecer cambio', {
      duration: 10000, type: mdtoast.WARNING
    });
  });
  $('#active').click(function() {
    mdtoast('Cambio de status', {
      duration: 10000, type: mdtoast.SUCCESS
    });
  });
});

function Confirmar_cancelar() {
    var res = confirm('¿Estas seguro de CANCELAR?');
    return res;
}

function Confirmar_agregar() {
    var res = confirm('¿Estas seguro de PAGAR?');
    return res;
}

$(document).ready(function(){
  $("#cb_sucursal").change(function () {

    $('#cb_equipo').find('option').remove().end().append('<option value="whatever"></option>').val('whatever');
              $('#cb_vendedor').find('option').remove().end().append('<option value="whatever"></option>').val('whatever');

    $("#cb_sucursal option:selected").each(function () {
      IdEmpresa = $(this).val();
      $.post("php/Funcionalidad_Pwa.php", { IdEmpresa: IdEmpresa }, function(data){
        $("#cb_equipo").html(data);
      });
    });
  })
});

$(document).ready(function(){
  $("#cb_equipo").change(function () {
              $('#cb_vendedor').find('option').remove().end().append('<option value="whatever"></option>').val('whatever');
    $("#cb_equipo option:selected").each(function () {
      Equipo = $(this).val();
      $.post("php/Funcionalidad_Pwa.php", { Equipo: Equipo, IdEmpresa:IdEmpresa}, function(data){
        $("#cb_vendedor").html(data);
      });
    });
  })
});

document.addEventListener('DOMContentLoaded', function() {
   M.AutoInit();
  var elems = document.querySelectorAll('.modal');
  var instances = M.Modal.init(elems);
});

function buscarCliente(){
  var nombre = document.getElementById('nombreBuscar').value;
  if(nombre === null){
    console.log('Se debe ingresar un nombre');
  }else{
    //console.log(nombre);
    var xhttps = new XMLHttpRequest();
    xhttps.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        document.getElementById('resultadoBusqueda').innerHTML = this.responseText;
      }
    };
    xhttps.open('GET', 'https://kasu.com.mx/login/php/buscarCliente.php?nombre='+nombre, true);
    xhttps.send();
  }
}
