<?
//Imprimir el alert con el dato
if($_GET['Ml'] == 1){
    echo "<script type='text/javascript'>
         alert('El correo se ha enviado');
         </script>";
}elseif($_GET['Ml'] == 2){
    echo "<script type='text/javascript'>
         alert('El correo que proporcionaste no esta registrado');
         </script>";
}elseif($_GET['Ml'] == 3){
    echo "<script type='text/javascript'>
         alert('Servicio de interes no identificado');
         </script>";
}elseif($_GET['Ml'] == 4){
    echo "<script type='text/javascript'>
         alert('Baja Exitosa');
         </script>";
}elseif($_GET['Ml'] == 5){
    echo "<script type='text/javascript'>
         alert('Asignacion Exitosa');
         </script>";
}elseif($_GET['Ml'] == 6){
    echo "<script type='text/javascript'>
         alert('La clave CURP ".$_GET['curp']." que intentaste registrar ya se encuentra en nuestro sistema bajo el nombre ".$_GET['Name']." porfavor verifica los datos');
         </script>";
}elseif($_GET['Ml'] == 7){
    echo "<script type='text/javascript'>
         alert('Se ha registrado exitosamente a ".$_GET['Name']." con la clave CURP ".$_GET['curp']." no olvides enviar las fichas o entregarselas en persona');
         </script>";
}
