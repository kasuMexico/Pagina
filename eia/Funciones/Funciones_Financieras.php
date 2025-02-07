<?PHP
class Financieras{
    public function HashMP($c0,$Pr,$Pl){
        //Crear consulta
            $sql = "SELECT Liga FROM MercadoPago WHERE (Producto = '$Pr') AND (Plazo = '$Pl')";
        //Realiza consulta
            $res = mysqli_query($c0, $sql);
        //Si existe el registro se asocia en un fetch_assoc
            if($Reg=mysqli_fetch_assoc($res)){
        //Si existe el campo a buscar
                return $Reg["Liga"];
            }
    }
    /****************************************************************************
        Esta funcion calcula el pago de un credito
                Io = el valor a financiar (Precio)
                $tasa  = Tasa anual de credito
                $per  = Numero de pagos
    ****************************************************************************/
        public function PagoSI($tasa,$Periodo,$I0){
            //Dividimos la tasa entre 100
            $ti = $tasa/100;
            //primera parte
            $a2 = 1+$ti;
            $b2 = pow($a2,$Periodo);
            $c = $b2*$ti;
            $d = $c*$I0;
            //Segunda parte
            $e = $b2-1;
            //Union
            $f = $d/$e;
            //Se retorna el valor
            return round($f, 2);
        }
    /********************************************************************************
                suma los valores en una tabla dada DOS condiciones
        c0 => Conexion a la base de datos
        $c1=> Columna a sumar
        $d1 => Tabla a consultar
        $d2 => Nombre de la columna
        $d3 => valor a consultar
        $d4 => Nombre de la columna
        $d5 => valor a consultar
    *********************************************************************************/
        public function SumPag2Con($c0,$c1,$d1,$d2,$d3,$d4,$d5){
            //Crear consulta
            $sql = "SELECT SUM($c1) FROM $d1 WHERE $d2 = '$d3' AND $d4 = '$d5' AND status != 'Mora'";
            //Realiza consulta
            $res = mysqli_query($c0, $sql);
            //Si existe el registro se asocia en un fetch_assoc
            $Reg = mysqli_fetch_assoc($res);
            //Si existe el campo a buscar
            return $Reg['SUM('.$c1.')'];
        }
    /********************************************************************************/
        public function SumarPagos($c0,$c1,$d1,$d2,$d3){
            //Crear consulta
            $sql = "SELECT SUM($c1) FROM $d1 WHERE $d2 = '$d3' AND status != 'Mora'";
            //Realiza consulta
            $res = mysqli_query($c0, $sql);
            //Si existe el registro se asocia en un fetch_assoc
            $Reg = mysqli_fetch_assoc($res);
            //Si existe el campo a buscar
            return $Reg['SUM('.$c1.')'];
        }
    /********************************************************************************/
        public function SumarMora($c0,$c1,$d1,$d2,$d3){
            //Crear consulta
            $sql = "SELECT SUM($c1) FROM $d1 WHERE $d2 = '$d3' AND status = 'Mora'";
            //Realiza consulta
            $res = mysqli_query($c0, $sql);
            //Si existe el registro se asocia en un fetch_assoc
            $Reg = mysqli_fetch_assoc($res);
            //Si existe el campo a buscar
            return $Reg['SUM('.$c1.')'];
        }
  /****************************************************************************
       Esta funcion calcula el valor total que se pagara de un producto a credito;
          Io = el valor a financiar (Precio)
          $tasa  = Tasa anual de credito
          $per  = Numero de pagos
  ****************************************************************************/
  public function PagoCredito($c0,$Vta){
      //se obtinen los datos de la venta
      $sql = "SELECT * FROM Venta WHERE Id = '$Vta'";
      //Realiza consulta
      $res = mysqli_query($c0, $sql);
      //Si existe el registro se asocia en un fetch_assoc
      if($Reg=mysqli_fetch_assoc($res)){
          //Buscar la tasa de credito de el producto
          $tasa = Basicas::BuscarCampos($c0,"TasaAnual","Productos","Producto",$Reg["Producto"]);
          $tasa = $tasa/12;
          //Calculamos el saldo sobre saldos insolutos
          $Pago = Financieras::PagoSI($tasa,$Reg["NumeroPagos"],$Reg["CostoVenta"]);
          //Valor futuro
          $i = $Pago*$Reg["NumeroPagos"];
          //Se retorna el valor
          return round($i, 2);
      }
  }
  /****************************************************************************
        Esta funcion calcula el el saldo al dia de un producto a credito;
          $c0 = Conexion a la base de datos
          $Vta = Id de Venta a liquidar
  ****************************************************************************/
  public function SaldoCredito($c0,$Vta){
  //Crear consulta
      $sql = "SELECT * FROM Venta WHERE Id = ".$Vta;
  //Realiza consulta
      $res = mysqli_query($c0, $sql);
  //Si existe el registro se asocia en un fetch_assoc
      if($Reg=mysqli_fetch_assoc($res)){
      //se obtiene el tiempo que ha usado en meses el credito
           $tm1 = strtotime(date("y-m-d"));
      //Buscamos el ultimo pago de el cliente y si no tiene la fecha de registro para calcular los dias que no ha pagado
           //Se busca la ultima fecha de pagos
           $IdPago = Basicas::Max1Dat($c0,"FechaRegistro","Pagos","IdVenta",$Reg['Id']);
           //Se busca la tasa de el producto
           $Tai = Basicas::BuscarCampos($c0,"TasaAnual","Productos","Producto",$Reg["Producto"]);
           $Tai = $Tai/12;
           //Se busca el numero de dias en que se paga el producto
           $DiaP = Basicas::BuscarCampos($c0,"PlazoPagos","Productos","Producto",$Reg["Producto"]);
           //Asignamos los dias de inicio de conteo segun el caso
           if(empty($IdPago)){
               //Se registran la fecha de registro de el cliente
               $tm2 = strtotime($Reg['FechaRegistro']);
           }else{
               //Registro de el ultimo pago
               $tm2 = strtotime($IdPago);
           }
           //Se registran la fecha de registro de el cliente
           $tm12 = strtotime($Reg['FechaRegistro']);
           //Se calcula la tasa de interes
           $t3 = $tm1-$tm12;
           $Dis = $t3/81600;
           $Dis = floor($Dis);
           //Restamos las dos fechas para obtener el tiempo
           $tm3 = $tm1-$tm2;
           //Se reducen la fecha Unix a numero de dias
           //Convertir segundos a meses
           $Dias = $tm3/81600;
           $Dias = floor($Dias);
           //Obtenermos la tasa de interes del producto
           $Tai = $Tai/$DiaP;
           //Se baja a porcentaje
           $i = $Tai / 100;
           //se agrega el 1 para potenciar
           $Inet = 1+$i;
           //Se potencia
           $pot = pow($Inet,$Dias);
           $pot2 = pow($Inet,$Dis);
           //Suma de los Pagos
           $SuPag = Financieras::SumarPagos($c0,"Cantidad","Pagos","IdVenta",$Reg["Id"]);
           //restamos el interes a los pagos al dia de hoy
           $Apg = $SuPag/$pot2;
           //Restamos el capital pagado al valor de compra
           $Cap = $Reg["CostoVenta"]-$Apg;
           //Saldo en capital por tiempo usado
           $Cap3 = $Cap*$pot;
           //Retornamos el valor a pagar
           return round($Cap3, 2);
      }
    }
    /******************************************************************************************************************
                  Esta variable retorna el pago que el cliente debe dar
                  c0 => Conexion a la base de datos
                  $IdVta = este es el id de la venta que se esta rastreando
    ******************************************************************************************************************/
      public function Pago($c0, $IdVta){
          //Suma de los Pagos
          $SuPag = Financieras::SumarPagos($c0,"Cantidad","Pagos","IdVenta",$IdVta);
          //Obtenemos la cantidad a pagar por el cliente segun venta
          $VaCre = Financieras::PagoCredito($c0,$IdVta);
          //Obtenemos el saldo de el credito
          $saldo = Financieras::SaldoCredito($c0,$IdVta);
          //Obtener el numero de periodos del credito
          $NumPag = Basicas::BuscarCampos($c0,"NumeroPagos","Venta","Id",$IdVta);
          //Se busca el producto de la venta
          $IdPro = Basicas::BuscarCampos($c0,"Producto","Venta","Id",$IdVta);
          //Tasa de ineteres de el producto
          $TsaIn = Basicas::BuscarCampos($c0,"TasaAnual","Productos","Producto",$IdPro);
          $TsaIn = $TsaIn/12;
          //Cantidad solicitada
          $CosVta = Basicas::BuscarCampos($c0,"CostoVenta","Venta","Id",$IdVta);
          //Se divide el costo total entre el numero de pagos
          $ValCre = Financieras::PagoSI($TsaIn,$NumPag,$CosVta);
          $ValCre = $ValCre/2;
          //EL saldo es mayor que el costo de compra
          if($saldo >= $VaCre){
              //el pago estara en linea con el saldo del credito
              //Se dividen la suma de los pagos entre el el valor del pago normal
              $pagos = $SuPag/$ValCre;
              //se restan el n de pagos completos a los q debia dar al inicio
              $pgRest = $NumPag-$pagos;
              //Se deivide el saldo entre los pagos restantes
              $Vre = $saldo/$pgRest;
              return round($Vre, 2);
          }else{
              //el pago estara en linea con el valor del producto
              $Sd = $SuPag-$VaCre;
              //Se calcula si los pagos del cliente son mayores a la deuda $ValCre
              if($Sd >= 0){
                  return 0;
              }else{
                  return round($ValCre, 2);
              }
          }
      }
  /******************************************************************************************************************
  ESta funcion retorna los pagos que le quedan al cliente por dar
                c0 => Conexion a la base de datos
                $IdVta = este es el id de la venta que se esta rastreando
  ******************************************************************************************************************/
  public function PagosPend($c0, $IdVta){
    //Suma de los Pagos
    $SuPag = Financieras::SumarPagos($c0,"Cantidad","Pagos","IdVenta",$IdVta);
    //Obtenemos la cantidad a pagar por el cliente segun venta
    $VaCre = Financieras::PagoCredito($c0,$IdVta);
    //Obtenemos el saldo de el credito
    $saldo = Financieras::SaldoCredito($c0,$IdVta);
    //Obtener el numero de periodos del credito
    $NumPag = Basicas::BuscarCampos($c0,"NumeroPagos","Venta","Id",$IdVta);
    //Se busca el producto de la venta
    $IdPro = Basicas::BuscarCampos($c0,"Producto","Venta","Id",$IdVta);
    //Cantidad solicitada
    $CosVta = Basicas::BuscarCampos($c0,"CostoVenta","Venta","Id",$IdVta);
    //Tasa de ineteres de el producto
    $TsaIn = Basicas::BuscarCampos($c0,"TasaAnual","Productos","Producto",$IdPro);
    $TsaIn = $TsaIn/12;
    //Pago de un credito
    $ValCre = Financieras::PagoSI($TsaIn,$NumPag,$CosVta);
    //Se dividen la suma de los pagos entre el el valor del pago normal
    $pagos = $SuPag/$ValCre;
    //se restan el n de pagos completos a los q debia dar al inicio
    $pgRest = $NumPag-$pagos;
    //se redondean los pagos
    $Day1 = round($pgRest, 0, PHP_ROUND_HALF_DOWN);
    //Se imprime el resultado
    return $Day1;
  }

    /****************************************************************************
    Esta funcion calcula la mora de un pago
            Io = el valor a financiar (Precio)
            $tasa  = Tasa anual de credito
            $per  = Numero de pagos
    ****************************************************************************/
    public function Mora($Pag){
        //Calculos de la mora
        $SubMr = $Pag/10;
        $Val = $SubMr+$Pag;
        //Se retorna el valor
        return round($Val, 2);
    }

    /****************************************Simulador de Pagos ********************************************/
    public function SimulaCredi($c0,$IdCnc){
      $sqlCn = "SELECT * FROM Contacto WHERE id = '$IdCnc'";
      //Realiza consulta
          $ResCn = mysqli_query($c0, $sqlCn);
      //Si existe el registro se asocia en un fetch_assoc
          if($RegCn=mysqli_fetch_assoc($ResCn)){
            //Tasa de ineteres de el producto
            $a = Basicas::BuscarCampos($c0,"TasaAnual","Productos","Producto",$RegCn['Producto']);
            $a = $a/12;
            //Pago de un credito
            $ValCre = Financieras::PagoSI($a,$RegCn['Periodo'],$RegCn['Cantidad']);
            //Valor del pagare
              $E = $ValCre*$RegCn['Periodo'];
          }
          return $E;
    }
    /****************************************************************************************************************
                                        Esta funcion actualiza los valores de las ventas
    *****************************************************************************************************************/
        public function actualizaVts($c0){
          //Creamos variables primarias
          $Hoy = strtotime(date("Y-m-d"));
          $contador = 1;
          //Crear consulta
          $limite = Basicas::MaxDat($c0,"Id","Venta");
          //realiza el while para validar cada una de las ventas
          while($contador <= $limite){
              //Se Suman los pagos de los clientes
              $SuPag = Financieras::SumarPagos($c0,"Cantidad","Pagos","IdVenta",$contador);
              //Se calcula el saldo de la venta
              $saldo = Financieras::PagoCredito($c0,$contador);
              //Se obtienen los datos de contacto
              $sql = "SELECT * FROM Venta WHERE Id = '".$contador."'";
              //Realiza consulta
              $res = mysqli_query($c0, $sql);
              //Si existe el registro se asocia en un fetch_assoc
              if($Reg=mysqli_fetch_assoc($res)){
                  //Crear consulta sobre los pagos
                  $Max="SELECT MAX(FechaRegistro) FROM Pagos WHERE IdVenta = $contador";
                  //Realiza consulta
                  $res = mysqli_query($c0, $Max);
                  //Si existe el registro se asocia en un fetch_assoc
                  $IdPago = mysqli_fetch_assoc($res);
                  //Se identifica el status de la venta actual
                  if($Reg['Status'] == "PREVENTA"){
                      //Verificamos que sea correcto
                      if(empty($SuPag)){
                        //Se busca si el cliente tiene mas de 6 meses de alta y se reasiga el usuario
                        $FecVta = Basicas::BuscarCampos($c0,"FechaRegistro","Venta","Id",$contador);
                        //Calcular la fecha
                        $g95 = strtotime($FecVta."+ 180 days");
                        $g96 = strtotime(date("Y-m-d"));
                        //de vendido y se cambia el IdVendedor a SISTEMA
                        if($g96 >= $g95){
                        Basicas::ActCampo($c0,"Venta","Usuario","SISTEMA",$contador);
                        }
                      }else{
                        //Se actualiz el status de la venta
                        Basicas::ActCampo($c0,"Venta","Status","COBRANZA",$contador);
                      }
                  }elseif($Reg['Status'] == "COBRANZA"){
                      //calculamos si tiene mas de 90 dias de haber dado su ultimo pago
                      $Canc = strtotime(date("Y-m-d",strtotime($IdPago['MAX(FechaRegistro)']."+ 90 days")));
                      //calculamos si tiene mas de noventa dias sin dar un pago0
                      if($Hoy > $Canc){
                          Basicas::ActCampo($c0,"Venta","Status","CANCELADO",$contador);
                      }elseif($SuPag >= $saldo){
                          //si el cliente ya termino de pagar se pasa a ACTIVACION
                          Basicas::ActCampo($c0,"Venta","Status","ACTIVACION",$contador);
                      }
                  }elseif($Reg['Status'] == "ACTIVACION"){
                      //Creamos la fecha donde se ACTIVA la poliza
                      $Act = strtotime($IdPago['MAX(FechaRegistro)']."+ 30 days");
                      //se miden la fecha del ultimo pago y la fecha de hoy
                      if($Hoy > $Act){
                      //Se actualiza el status de la venta
                            Basicas::ActCampo($c0,"Venta","Status","ACTIVO",$contador);
                            //Armamos las variables para el correo
                                $Asunto = '¡BIENVENIDO A KASU!';
                                $Difa = Basicas::BuscarCampos($c0,"IdContact","Venta","Id",$contador);
                                $DirUrl = base64_encode($Difa);
                                $Cte = Basicas::BuscarCampos($c0,"Nombre","Usuario","IdContact",$Difa);
                                $Address = Basicas::BuscarCampos($c0,"Mail","Contacto","Id",$Difa);
                            if(!empty($Address)){
                            //Generamos el correo electronico
                                $Mensaje = Correo::Mensaje($Asunto,$Cte,$DirUrl,'','','','','','','','','','','','','','','','',$Difa);
                        //Enviamos el correo electronico
                                Correo::EnviarCorreo($Cte,$Address,$Asunto,$Mensaje);
                            }
                      }
                  }
              }
          //Aumentamos el contador en un valor
              $contador++;
          }
          return $contador;
        }
    /***************************************************************************************************************
                                          Actualiza la tabla de comisiones
    ***************************************************************************************************************/
      public function ActualComis($c0){
        //Se vacian la tabla de comisiones
        mysqli_query($c0,"TRUNCATE TABLE Comisiones");
        //Variables base de calculo
        $contador = 1;
        $ContSem = 1;
        $Mult = 0;
        $limite = Basicas::MaxDat($c0,"id","Empleados");
        //Se busca el pasado domingo
        $FecFin = date("Y-m-d",strtotime("last Sunday"));
        //Se le restan 7 dias para establecer la fecha inicial
        $FechIni = date("Y-m-d",strtotime($FecFin."- 7 days"));
        //Contar la semanas que han pasado desde la primer venta
        $Fec0 = Basicas::MinDat($c0,"FechaRegistro","Venta");
        //Se cuentan los segundos desde el inicio de ventas
        $F01 = strtotime($FecFin);
        $F02 = strtotime($Fec0."last Monday");
        //Se reducen los segundos a numero de dias
        $F03 = $F01-$F02;
        $F04 = $F03/86400;
        //Se cuentan las semanas
        $F06 = $F04/7;
        $semanas = round($F06, 0);
        //For que se calcula para registrar las comisiones historicas
        while($ContSem <= $semanas){
        //Reasignar las fechas a semana 1
            $contador = 1;
            //Se crea un multiplicador de dias
            $MulSF = $Mult*7;
            $MultSemFi = $MulSF+1;
            $MultSemFn = $ContSem*7;
            $FechIni = date("Y-m-d",strtotime($Fec0."+ ".$MultSemFi." days"));
            $FecFin = date("Y-m-d",strtotime($Fec0."+ ".$MultSemFn." days"));
        //For que calcula las comisiones
            while($contador <= $limite){
               //Solo calcula si el Vendedor esta activo
               //Crear consulta
                   $sql = "SELECT * FROM Empleados WHERE Id = '$contador' AND Nombre != 'Vacante'";
               //Realiza consulta
                   $res = mysqli_query($c0, $sql);
               //Si existe el registro se asocia en un fetch_assoc
                   if($Reg=mysqli_fetch_assoc($res)){
               //Establecer comision segun nivel
               $Niv = $Reg['Nivel'];
               //Se busca el usuario segun el Id para los registros de venta y comision
               $Vendedor =  $Reg['IdUsuario'];
               //Se busca el equipo de el vendedor
               $Equipo = $Reg['Equipo'];
               //Ventas de la semana
               if($Niv >= 5){
                 //hacemos un while sobre los productos
                 $sql = "SELECT * FROM Productos";
                 //Realiza consulta
                 $result = $c0->query($sql);
                 //hacemos un recorrido por el resultado
                 while($row = mysqli_fetch_assoc($result)){
                     //Sumamos el valor de las ventas en un rango de fechas
                     $sqlCont = Basicas::ContarFechas4($c0,"Venta","Usuario",$Vendedor,"FechaRegistro",$FecFin,"FechaRegistro",$FechIni,"Status","PREVENTA","Producto",$row['Producto']);
                     //Obtenemos el valor en porcentaje
                     $Porcenaje = Basicas::BuscarCampos($c0,"N".$Niv,"Comision","Tipo","Colocacion")/100;
                     //Obtenemos el valor de la comision
                     $ValCol = $row['comision']*$Porcenaje;
                     //Multiplicamos la venta por el porcentaje de comision
                     $sqlSum = $sqlCont*$ValCol;
                     //Sumamos al general de el asesor
                     $sk = $sk+$sqlSum;
                   }
                //Se Suma el valor de las ventas realizadas SumarFechas($c0,"CostoVenta","Venta","Usuario",$Vendedor,"FechaRegistro",$FecFin,"FechaRegistro",$FechIni);
                $sqlSum = Basicas::SumarFechasIndis($c0,"CostoVenta","Venta","Usuario",$Vendedor,"FechaRegistro",$FecFin,"FechaRegistro",$FechIni,"Status","PREVENTA");
                //Se Suman la cantidad de pagos realizados
                $SumCob = Basicas::SumarFechas($c0,"Cantidad","Pagos","Usuario",$Vendedor,"FechaRegistro",$FecFin,"FechaRegistro",$FechIni);
                //Calculadora de comisiones para cobranza
                $Xy = $SumCob/10000;
                //Asignacion de comisiones por # de ventas para ejecutivos de venta
                if($sqlCont >= 3){
                      $cim = $sk;
                }elseif($sqlCont == 2){
                      $ci = $sk/100;
                      $con = $ci*75;
                      $cim = round($con, 2);
                }elseif($sqlCont == 1){
                     $ci = $sk/100;
                     $con = $ci*50;
                     $cim = round($con, 2);
                }
                //asignado de comisiones por nivel
                if($Niv == 7){
                     //Comision Ventas para Vendedores externos
                     $ComVtas = $sk;
                }elseif($Niv == 6){
                     //comision Ventas ejecutivos de Venta
                     $ComVtas = $cim;
                     //comision cobranza ejecutivos de Venta
                     $cib = $Xy*Basicas::BuscarCampos($c0,"N".$Reg['Nivel'],"Comision","Tipo","Cobranza");
                     $ComCob = round($cib, 2);
                   }elseif($Niv == 5){
                     //comision cobranza ejecutivos de cobranza
                     $cib = $Xy*Basicas::BuscarCampos($c0,"N".$Reg['Nivel'],"Comision","Tipo","Cobranza");
                     $ComCob = round($cib, 2);
                   }
                   //Si la comision y cobranza es igual a cero nos e escribe
                   if($ComVtas > 0 || $ComCob > 0){
                       //Se registra el array de registro de pago
                       $DatCob = array(
                            "IdVendedor"   => $Vendedor,
                            "Equipo"       => $Equipo,
                            "Inicio"       => $FechIni,
                            "FIn"          => $FecFin,
                            "VtasUni"      => $sqlCont,
                            "VtasVal"      => $sqlSum,
                            "CobUni"       => $ContCob,
                            "CobVal"       => $SumCob,
                            "ComVtas"      => $ComVtas,
                            "ComCob"       => $ComCob

                        );
                       //Se realiza el insert en la base de datos
                       Basicas::InsertCampo($c0,"Comisiones",$DatCob);
                       //Genera las comisiones con base en los equipos de Venta
                   }
                 }
              }
               //Se aumenta el contador en 1
               $contador++;
            }
            $ContSem++;
            $Mult++;
        }
        //Crear consulta
            $sql = "SELECT * FROM Empleados WHERE Nivel <= 4 AND Nombre != 'Vacante'";
        //Realiza consulta
            $res = $c0->query($sql);
        //Si existe el registro se asocia en un fetch_assoc
            foreach ($res as $Reg){
        //Se suman las unidades de ventas de el equipo al cual pertenece
            $SumNVtas3 = Basicas::Sumar1Fechas($c0,"VtasUni","Comisiones","Equipo",$Reg['Id'],"Inicio",$Reg['FechaAlta']);
            //Se suman las ventas de el equipo al cual pertenece
            $SumVtas3 = Basicas::Sumar1Fechas($c0,"VtasVal","Comisiones","Equipo",$Reg['Id'],"Inicio",$Reg['FechaAlta']);
        //Se suman las unidades de cobranza de el equipo al cual pertenece
            $SumNCob3 = Basicas::Sumar1Fechas($c0,"CobUni","Comisiones","Equipo",$Reg['Id'],"Inicio",$Reg['FechaAlta']);
            //Se suman las cobranzas del equipo
            $SumCob3 = Basicas::Sumar1Fechas($c0,"CobVal","Comisiones","Equipo",$Reg['Id'],"Inicio",$Reg['FechaAlta']);
            //Calculo de Comision sobre ventas para empleados
            $sk = $SumVtas3/10000;
            //Calculadora de comisiones para cobranza
            $Xy = $SumCob3/10000;
            //Calculo de las Comisiones
            if($Reg['Nivel'] == 4){
              //Comision colocacion
              $dsl = $sk*Basicas::BuscarCampos($c0,"N".$Reg['Nivel'],"Comision","Tipo","Colocacion");
              $ComCol = round($dsl, 2);
              //comision cobranza coordinadores
              $cib = $Xy*Basicas::BuscarCampos($c0,"N".$Reg['Nivel'],"Comision","Tipo","Cobranza");
              $ComCob = round($cib, 2);
            }elseif($Reg['Nivel'] == 3){
              //Comision colocacion
              $dsl = $sk*Basicas::BuscarCampos($c0,"N".$Reg['Nivel'],"Comision","Tipo","Colocacion");
              $ComCol = round($dsl, 2);
              //comision cobranza gerentes
              $cib = $Xy*Basicas::BuscarCampos($c0,"N".$Reg['Nivel'],"Comision","Tipo","Cobranza");
              $ComCob = round($cib, 2);
            }elseif($Reg['Nivel'] == 2){
              //Comision colocacion
              $dsl = $sk*Basicas::BuscarCampos($c0,"N".$Reg['Nivel'],"Comision","Tipo","Colocacion");
              $ComCol = round($dsl, 2);
              //comision cobranza regionales
              $cib = $Xy*Basicas::BuscarCampos($c0,"N".$Reg['Nivel'],"Comision","Tipo","Cobranza");
              $ComCob = round($cib, 2);
            }elseif($Reg['Nivel'] == 1){
              //Comision colocacion
              $dsl = $sk*Basicas::BuscarCampos($c0,"N".$Reg['Nivel'],"Comision","Tipo","Colocacion");
              $ComCol = round($dsl, 2);
              //comision cobranza regionales
              $cib = $Xy*Basicas::BuscarCampos($c0,"N".$Reg['Nivel'],"Comision","Tipo","Cobranza");
              $ComCob = round($cib, 2);
            }
            //Si la comision y cobranza es igual a cero nos e escribe
            if($ComCol > 0 || $ComCob > 0){
                //Se registra el array de registro de pago
                $DatCob = array(
                     "IdVendedor"   => $Reg['IdUsuario'],
                     "Equipo"       => $Reg['Equipo'],
                     "Inicio"       => $Reg['FechaAlta'],
                     "FIn"          => date('Y-m-d'),
                     "VtasUni"      => $SumNVtas3,
                     "VtasVal"      => $SumVtas3,
                     "CobUni"       => $SumNCob3,
                     "CobVal"       => $SumCob3,
                     "ComVtas"      => $ComCol,
                     "ComCob"       => $ComCob

                 );
                //Se realiza el insert en la base de datos
                Basicas::InsertCampo($c0,"Comisiones",$DatCob);
                //Genera las comisiones con base en los equipos de Venta
            }
          }
      }
}
