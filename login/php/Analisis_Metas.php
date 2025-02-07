<?
/*************************************** Analisis de metas *****************************************************/
    //Se lanza el primer dia de el mes
    $Fec0 = date("Y-m-d",strtotime('first day of this month'));
    //se obtienen las metas de ventas de este usuario
    $IdAsig = Basicas::Buscar1Fechas($mysqli,"Id","Asignacion","Usuario",$_SESSION["Vendedor"],"Fecha",$Fec0);
    $IdAsig1 = Basicas::MaxDat($mysqli,"Usuario","Asignacion","Id",$IdAsig);
    //Buscamos los datos de las asignaciones
    $MetaVta = Basicas::BuscarCampos($mysqli,"MVtas","Asignacion","Id",$IdAsig);
    $MetaCob = Basicas::BuscarCampos($mysqli,"MCob","Asignacion","Id",$IdAsig);
    $Normali = Basicas::BuscarCampos($mysqli,"Normalidad","Asignacion","Id",$IdAsig);
    //Se valida el Usuario por permiso
    //Crear consulta
    $sqal = "SELECT * FROM Empleados WHERE Nombre != 'Vacante'";
    //Realiza consulta
    $r4e9s = $mysqli->query($sqal);
    //Si existe el registro se asocia en un fetch_assoc
    foreach ($r4e9s as $Resd5){
        //Se realiza la operacion con los niveles
        if($Resd5['Nivel'] >= 5){
            //SE suman las ventas de el usuario del mes
            $VtasHoy = Basicas::Sumar1Fechas($mysqli,"CostoVenta","Venta","Usuario",$Resd5["IdUsuario"],"FechaRegistro",$Fec0);
            //SE suman los pagos de el usuario del mes
            $CobHoy = Basicas::Sumar1Fechas($mysqli,"Cantidad","Pagos","Usuario",$Resd5["IdUsuario"],"FechaRegistro",$Fec0);
        }elseif($Resd5['Nivel'] <= 4){
            //Buscamos el IdUsuario de los asignados
            $s3ql1 = "SELECT * FROM Empleados WHERE Equipo = '".$Resd5['Id']."'";
            //Realiza consulta
            $r8e9s1 = $mysqli->query($s3ql1);
            //Si existe el registro se asocia en un fetch_assoc
            foreach ($r8e9s1 as $Re7g1){
                //SE suman las ventas de el usuario del mes
                $VtasHoy = $VtasHoy+Basicas::Sumar1Fechas($mysqli,"CostoVenta","Venta","Usuario",$Re7g1["IdUsuario"],"FechaRegistro",$Fec0);
                //SE suman los pagos de el usuario del mes
                $CobHoy = $CobHoy+Basicas::Sumar1Fechas($mysqli,"Cantidad","Pagos","Usuario",$Re7g1["IdUsuario"],"FechaRegistro",$Fec0);
            }
        }
        //SUeldos por pagar
        if($Resd5['Nivel'] >= 7){
          $Sueldo = 0;
        }elseif($Resd5['Nivel'] <= 6){
          $Sueldo = 6000;
        }elseif($Resd5['Nivel'] <= 5){
          $Sueldo = 6000;
        }elseif($Resd5['Nivel'] <= 4){
          $Sueldo = 8000;
        }elseif($Resd5['Nivel'] <= 3){
          $Sueldo = 10000;
        }elseif($Resd5['Nivel'] <= 2){
          $Sueldo = 15000;
        }elseif($Resd5['Nivel'] <= 1){
          $Sueldo = 20000;
        }
        //Suma de sueldos por pagar
        $SUeldos = $SUeldos+$Sueldo;
        //COmisiones por pagar
        //Se restan los pagos de las comisiones a las comisiones generadas
        $sj1 = Basicas::Sumar1cond($mysqli,"ComVtas","Comisiones","IdVendedor",$Resd5['IdUsuario']);
        $sj2 = Basicas::Sumar1cond($mysqli,"ComCob","Comisiones","IdVendedor",$Resd5['IdUsuario']);
        $tj = Basicas::Sumar1cond($mysqli,"Cantidad","Comisiones_pagos","IdVendedor",$Resd5['IdUsuario']);
        //Se restan los valores
        $sj = $sj1+$sj2;
        $Saldo = $sj-$tj;
        if($Saldo <= 0){
          $Saldo = 0;
        }
        //General de comisiones
        $comisiones = $comisiones+$Saldo;
    }
    //Sumamos 15 dias al primero de mes
    $Quincena = strtotime($Fec0.'+ 15 days');
    $hoy = strtotime(date("Y-m-d"));
    //Analisis sueldos por quincena
    if($hoy > $Quincena){$SUeldos = $SUeldos/2;}
    //ANalisis de ventas
    //Sacamos el porcentaje de ventas
    $AvVtas = $MetaVta/$VtasHoy;
    //aterrizamos a cero si no da valor
    if($VtasHoy <= 0){ $AvVtas = 0;}
    //Sacamos el porcentaje de pagos
    $AvCob = $CobHoy/$MetaCob;
    $AvCob = $AvCob*100;
    //aterrizamos a cero si no da valor
    if($AvCob <= 0){ $AvCob = 0;}
    //Sacamos la normalidad de el usuario
    $spv = Basicas::ColorPor($MetaCob,$CobHoy);
    $bxo = Basicas::ColorPor($MetaVta,$VtasHoy);
    //Reasignamos el avance de las ventas si el usuario es vendedor externo
    if($Resd5['Nivel'] == 7){
      $AvCob = $AvVtas;
    }
/************************************* seccion de comiisones por contacto ******************************************/
    $Niv =  Basicas::BuscarCampos($mysqli,"Nivel","Empleados","IdUsuario",$_SESSION["Vendedor"]);
    //Buscamos la comision de el usuario
    $PorCom = Basicas::BuscarCampos($mysqli,"N".$Niv,"Comision","Id",2);
    //Reducimos el porcentaje a centecimas
    $as = $PorCom/100;
    //aterrizamos la fecha de ayer
    $Ayer = date("Y-m-d",strtotime(date("Y-m-d").'-1 day'));
    //Buscamos mi finger print
    $IdContacto = Basicas::BuscarCampos($mysqli,"IdContacto","Empleados","IdUsuario",$_SESSION["Vendedor"]);
    //Buscamos el fingerprint de el usuario
    $IdFing = Basicas::Max2Dat($mysqli,"Id","Eventos","Evento","Ingreso","Contacto",$IdContacto);
    //Obtenemos el fingerprint
    $Fingerprint = Basicas::BuscarCampos($mysqli,"IdFInger","Eventos","Id",$IdFing);
    //Crear consulta
    $sqal2 = "SELECT * FROM Eventos WHERE Evento = 'Tarjeta' AND IdFInger != '".$Fingerprint."' AND Usuario = '".$_SESSION["Vendedor"]."' AND FechaRegistro >= $Fec0";
    //Realiza consulta
    $r4e9s2 = $mysqli->query($sqal2);
    //Si existe el registro se asocia en un fetch_assoc
    foreach ($r4e9s2 as $Resd52){
      //Obnemos el producto de cada cupon
      $Prducto = Basicas::Buscar2Campos($mysqli,"Producto","PostSociales","Id",$Resd52["Cupon"],"Tipo","Art");
      //Buscamos el valor de la comision sobre la venta segun el nivel
      $ComGen = Basicas::BuscarCampos($mysqli,"comision","Productos","Producto",$Prducto);
      //Calculamos la comision degun el nivel
      $Comis = $ComGen*$as;
      //Selector de pago de comisiones
      if($Prducto == "Universidad"){
        //Comisiones por universitario
        $Comis = $Comis/2500;
      }elseif($Prducto == "Retiro"){
        //Comisiones por Retiro
        $Comis = $Comis/1000;
      }else{
        //Comisiones por funerario
        $Comis = $Comis/100;
      }
      //solo cuenta una vez por dia
      $CatLeid = Basicas::Cuenta1Fec1Cond($mysqli,"Eventos","IdFInger",$Resd52["IdFInger"],"Usuario",$_SESSION["Vendedor"],"FechaRegistro",$Ayer);
      if($CatLeid == 1){
        //Sumamos las comisiones para obtener el general
        $ComGenHoy = $ComGenHoy+$Comis;
      }
    }
