<?php
/********************************************************************************************************************************************
                        										ESTE ARCHIVO REALIZA LOS REGISTROS DE VENTA
********************************************************************************************************************************************/
//indicar que se inicia una sesion
		session_start();
//inlcuir el archivo de funciones
  	require_once 'Funciones_kasu.php';
/********************************************************************************************************************************************
		                        		Realizamos los registros generales de fingerprint, evento, gps, vendedor
********************************************************************************************************************************************/
//Registramos el Usuario que esta realizando la venta
		if(!empty($_SESSION["IdUsr"])){
				//Si el usuario entro con un cupon se registra el usuario vendedor
				$VendeDor = $_SESSION["IdUsr"];
		}else{
				//Buscamos cual fue el primer usuario que registro al fingerprint
				$IdReg = Basicas::Min2Dat($mysqli,"Id","Eventos","IdFInger",$fingerprint,"Evento","Tarjeta");
				//Se busca el fingerprint en la base para saber de donde proviene la compra
				$BusFing = Basicas::BuscarCampos($mysqli,"Usuario","Eventos","Id",$IdReg);
				//si no esta un cupon se registra la venta en automatico a la plataforma
				if(!empty($BusFing)){
					//Registramos el Id qe genero el lead
					$VendeDor = $BusFing;
				}else{
					//Registramos a la plataforma como vendedora
					$VendeDor = "PLATAFORMA";
				}
		}
//Se registran las variables de post como registros
		foreach ($_POST as $key => $value){
				$asignacion="\$".$key."='".$value."';";
				eval($asignacion);
		}
/****************************************************** Registro de GPS **************************************************************/
//Valida los datos para determinar si ya se registro el FingerPrint
    if (empty($_SESSION["gps"])){
//Se crea el array que contiene los datos de GPS
				$DatGps = array (
						"Latitud"   => $mysqli -> real_escape_string($Latitud),
						"Longitud"  => $mysqli -> real_escape_string($Longitud),
						"Presicion" => $mysqli -> real_escape_string($Presicion)
				);
//Se realiza el insert en la base de datos del GPS
				$_SESSION["gps"] = Basicas::InsertCampo($mysqli,"gps",$DatGps);
		}
/************************************************** Registro de FINGERPRINT ***********************************************************/
//verificamos si existe el FingerPrint
		$sql = Basicas::BuscarCampos($mysqli,"id","FingerPrint","fingerprint",$fingerprint);
//Valida los datos para determinar si ya se registro el FingerPrint
        if (empty($sql)){
        //Se crea el array que contiene los datos de FINGERPRINT
            $DatFinger = array (
              	"fingerprint"   => $mysqli -> real_escape_string($fingerprint),
                "browser"       => $mysqli -> real_escape_string($browser),
                "flash"         => $mysqli -> real_escape_string($flash),
                "canvas"        => $mysqli -> real_escape_string($canvas),
                "connection"    => $mysqli -> real_escape_string($connection),
                "cookie"        => $mysqli -> real_escape_string($cookie),
                "display"       => $mysqli -> real_escape_string($display),
                "fontsmoothing" => $mysqli -> real_escape_string($fontsmoothing),
                "fonts"         => $mysqli -> real_escape_string($fonts),
                "formfields"    => $mysqli -> real_escape_string($formfields),
                "java"          => $mysqli -> real_escape_string($java),
                "language"      => $mysqli -> real_escape_string($language),
                "silverlight"   => $mysqli -> real_escape_string($silverlight),
                "os"            => $mysqli -> real_escape_string($os),
                "timezone"      => $mysqli -> real_escape_string($timezone),
                "touch"         => $mysqli -> real_escape_string($touch),
                "truebrowser"   => $mysqli -> real_escape_string($truebrowser),
                "plugins"       => $mysqli -> real_escape_string($plugins),
                "useragent"     => $mysqli -> real_escape_string($useragent)
            );
        //Se realiza el insert en la base de datos
                Basicas::InsertCampo($mysqli,"FingerPrint",$DatFinger);
        }
/********************************************************************************************************************************************
                																	Carga de DATOS CONTACTO del cliente
********************************************************************************************************************************************/
    if(isset($_POST['Registro'])){
    //Se crea el array que contiene los datos de registro
        $DatContac = array (
            "Usuario"   => $VendeDor,
            "Idgps"     => $_SESSION["gps"],
            "Host"      => $mysqli -> real_escape_string($Host),
            "Mail"      => $mysqli -> real_escape_string($Mail),
            "Telefono"  => $mysqli -> real_escape_string($Telefono),
            "calle" 		=> $mysqli -> real_escape_string($Direccion),
            "Producto"  => $mysqli -> real_escape_string($Producto)
        );
    //Se realiza el insert en la base de datos
        $_SESSION["Cnc"] = Basicas::InsertCampo($mysqli,"Contacto",$DatContac);
		//Se crea la variable de el Correo
				$_SESSION["Mail"] = $Mail;
    //Se crea el array que contiene los datos para REGISTRO DE EVENTOS
        $DatEventos = array(
            "IdFInger"    => $mysqli -> real_escape_string($fingerprint),
            "Contacto"    => $_SESSION["Cnc"],
            "Idgps"       => $_SESSION["gps"],
            "Evento"      => "Registro",
            "Host"        => $mysqli -> real_escape_string($Host),
            "MetodGet"    => $mysqli -> real_escape_string($formfields),
            "connection"  => $mysqli -> real_escape_string($connection),
            "timezone"    => $mysqli -> real_escape_string($timezone),
            "touch"       => $mysqli -> real_escape_string($touch),
						"Cupon"       => $_SESSION["tarjeta"],
        	  "FechaRegistro" => date('Y-m-d')." ".date('H:i:s')
        );
        //Se realiza el insert en la base de datos
        Basicas::InsertCampo($mysqli,"Eventos",$DatEventos);
    //Creamos las variables de Session
        $_SESSION["Producto"] = $Producto;
    //Redireccionamos a la pagina de inicio
        header('Location: https://kasu.com.mx/registro.php');
    }
/********************************************************************************************************************************************
                													Carga de CURP cuando la venta es para el cliente
********************************************************************************************************************************************/
    if(isset($_POST['BtnRegCurBen'])){
    		//se busca que el cliente no este duplicado
    		$OPsd = Basicas::BuscarCampos($mysqli,"Nombre","Usuario","ClaveCurp",$CurClie);
				//Se busca que el cliente exista
				$ArrayRes = Seguridad::peticion_get($CurClie);
				//Validamos que el CURP sea de la persona que estaba en prospectos
				$_SESSION["NombreCOm"] = $ArrayRes["Nombre"]." ".$ArrayRes["Paterno"]." ".$ArrayRes["Materno"];
        //si el cliente esta duplicado se activa este if
        if(!empty($OPsd)){
            //Se destruyen las sessiones
            session_destroy();
            //Se envia el dato al registro para la impresion
            header('Location: https://kasu.com.mx/registro.php?curp='.$CurClie.'&stat=4&Name='.$OPsd);
				}elseif($ArrayRes["Response"] == "correct" AND $ArrayRes["StatusCurp"] != "BD"){//Verificamos que el curp exista
        //Se crea el array que contiene los datos de registro
            $DatUser = array (
                "IdContact"    => $_SESSION["Cnc"],
                "Usuario"       => $VendeDor,
                "Tipo"          => "Cliente",
								"Nombre"        => $ArrayRes["Nombre"],
								"Paterno"       => $ArrayRes["Paterno"],
								"Materno"       => $ArrayRes["Materno"],
                "ClaveCurp"     => $ArrayRes["Curp"],
                "Email"         => $_SESSION["Mail"]
            );
        //Se realiza el insert en la base de datos
            Basicas::InsertCampo($mysqli,"Usuario",$DatUser);
        //Se crea el producto que se esta comprando en base a la edad
            if($_SESSION["Producto"] == "Funerario"){
                $edad = Basicas::ObtenerEdad($CurClie);
                $SubProd = Basicas::ProdFune($edad);
                //Creamos las variables de Session
                $_SESSION["Producto"] = $SubProd;
								$_SESSION["Edad"] = $edad;
            }
        //Buscar precios y tasas
            $_SESSION["Costo"] = Basicas::BuscarCampos($mysqli,"Costo","Productos","Producto",$_SESSION["Producto"]);
            $_SESSION["Tasa"] = Basicas::BuscarCampos($mysqli,"TasaAnual","Productos","Producto",$_SESSION["Producto"]);
        //Variable de session de control
            $_SESSION["Ventana"] = "Ventana2";
        //Redireccionamos a la pagina de inicio
            header('Location: https://kasu.com.mx/registro.php');
        }else{
					//Se destruyen las sessiones
					session_destroy();
					//Se envia el dato al registro para la impresion
					header('Location: https://kasu.com.mx/registro.php?curp='.$CurClie.'&stat=5&Name='.$OPsd);
				}
    }
/********************************************************************************************************************************************
                														Carga de DATOS cuando la venta es para el BENEFICIARIO
********************************************************************************************************************************************/
    if(isset($_POST['BtnRegCurCli'])){
        //se busca que el cliente no este duplicado
        $OPsd = Basicas::BuscarCampos($mysqli,"Nombre","Usuario","ClaveCurp",$CurBen);
				//Se busca que el cliente exista
				$ArrayRes = Seguridad::peticion_get($CurBen);
				//Validamos que el CURP sea de la persona que estaba en prospectos
				$_SESSION["NombreCOm"] = $ArrayRes["Nombre"]." ".$ArrayRes["Paterno"]." ".$ArrayRes["Materno"];
        //si el cliente esta duplicado se activa este if
        if(!empty($OPsd)){
            //Se destruye la session
            session_destroy();
            //Se envia el dato al registro para la impresion
            header('Location: https://kasu.com.mx/registro.php?curp='.$CurBen.'&stat=4&Name='.$OPsd);
				}elseif($ArrayRes["Response"] == "correct" AND $ArrayRes["StatusCurp"] != "BD"){//Verificamos que el curp exista
        //Se crea el array que contiene los datos de registro
            $DatUser = array (
                "IdContact"    => $_SESSION["Cnc"],
                "Usuario"       => $VendeDor,
                "Tipo"          => "Beneficiario",
								"Nombre"        => $ArrayRes["Nombre"],
								"Paterno"       => $ArrayRes["Paterno"],
								"Materno"       => $ArrayRes["Materno"],
								"ClaveCurp"     => $ArrayRes["Curp"],
                "Email"         => $mysqli -> real_escape_string($EmaBen)
            );
        //Se realiza el insert en la base de datos
            Basicas::InsertCampo($mysqli,"Usuario",$DatUser);
        //Se crea el producto que se esta comprando en base a la edad
            if($_SESSION["Producto"] == "Funerario"){
                $edad = Basicas::ObtenerEdad($CurBen);
                $SubProd = Basicas::ProdFune($edad);
                //Creamos las variables de Session
                $_SESSION["Producto"] = $SubProd;
            }
        //Buscar precios y tasas
            $_SESSION["Costo"] = Basicas::BuscarCampos($mysqli,"Costo","Productos","Producto",$_SESSION["Producto"]);
            $_SESSION["Tasa"] = Basicas::BuscarCampos($mysqli,"TasaAnual","Productos","Producto",$_SESSION["Producto"]);
        //Variable de session de control
            $_SESSION["Ventana"] = "Ventana2";
        //Redireccionamos a la pagina de inicio
            header('Location: https://kasu.com.mx/registro.php');
					}else{
						//Se destruyen las sessiones
						session_destroy();
						//Se envia el dato al registro para la impresion
						header('Location: https://kasu.com.mx/registro.php?curp='.$CurBen.'&stat=5&Name='.$OPsd);
					}
    }
/********************************************************************************************************************************************
                																Carga de MEDIOS DE PAGO de la segunda ventana
********************************************************************************************************************************************/
    if(isset($_POST['BtnMetPago'])){
    		//Se crea el array que contiene los datos de registro
        $DatLegal = array (
            "IdContacto"    => $_SESSION["Cnc"],
            "Meses"         => $mysqli -> real_escape_string($Meses),
            "Terminos"      => $mysqli -> real_escape_string($Terminos),
            "Aviso"         => $mysqli -> real_escape_string($Aviso),
            "Fideicomiso"   => $mysqli -> real_escape_string($Fideicomiso)
        );
    		//Se realiza el insert en la base de datos
        Basicas::InsertCampo($mysqli,"Legal",$DatLegal);
				//Si el pago es de Contado
				if($Meses == 0){
						//Registro para que el pago de contado sea 1
						$Meses = 1;
				}
				//Se genera la referencia unica del cte MMN
				$firma = Seguridad::Firma($mysqli,$_SESSION["Cnc"],$_SESSION["Costo"]);
				//Buscamos el descuento de la Tarjeta
				$Descuento = Basicas::BuscarCampos($mysqli,"Descuento","PostSociales","Id",$_SESSION["tarjeta"]);
				//Seleccionamos el valor del descuento
				if(!empty($Descuento)){
					$_SESSION["Costo"] = $_SESSION["Costo"]-$Descuento;
				}
    		//Buscamos los datos y realizamos un registro en la venta
        $Venta = array (
            "Usuario"       => $VendeDor,
            "IdContact"     => $_SESSION["Cnc"],
            "Nombre"        => Basicas::BuscarCampos($mysqli,"Nombre","Usuario","IdContact",$_SESSION["Cnc"]),
            "Producto"      => $_SESSION["Producto"],
            "CostoVenta"    => $_SESSION["Costo"],
            "Idgps"         => $_SESSION["gps"],
            "NumeroPagos"   => $mysqli -> real_escape_string($Meses),
            "IdFIrma"       => $mysqli -> real_escape_string($firma),
            "Status"        => "PREVENTA",
            "Mes"           => date("M"),
            "Cupon"         => $_SESSION["tarjeta"],
            "TipoServicio"  => $mysqli -> real_escape_string($TipoServicio)
        );
    		//Insertar los datos en la base
        $_SESSION["Venta"] = Basicas::InsertCampo($mysqli,"Venta",$Venta);
				//Variables de el primer pago
				if($Meses != 1){
						//Calculamos el pago y la mora
						  $pago = Financieras::Pago($mysqli,$_SESSION["Venta"]);
							$mora = Financieras::Mora($pago);
					  //SE registra el primer pago de el cliente
					    $Pripg = array (
					        "vta"    	 => $_SESSION["Venta"],
					        "fec_pri" 	=> date('Y-m-d'),
					        "pago"    	=> $pago,
					        "mora"    	=> $mora,
									"FechaReg"  => date('Y-m-d'),
					    		"url"     	=> "PLATAFORMA"
					    );
					  //Insertar los datos en la base
					    Basicas::InsertCampo($mysqli,"PromesaPago",$Pripg);
				}
				//construimos el hash de mercado pago
				if(!empty($Descuento)){
					$ValDesc = substr($Descuento, 0, 1);
					//Producto con descuento
					$ProdNvo = $_SESSION["Producto"]."D".$ValDesc;
				}else{
					//Producto directo sin Descuento
					$ProdNvo = $_SESSION["Producto"];
				}
				//Buscamos el producto de el usuario
			  $hash = Financieras::HashMP($mysqli,$ProdNvo,$Meses);
				//Enviamos a la pagina para enviar correo con los datos
				header('Location: https://kasu.com.mx/eia/EnviarCorreo.php?EnFi='.$Meses.'&hash='.$hash);
    }
/********************************************************************************************************************************************
            												realiza el registro de un cliente cuando realiza su pago atravez de mercado PAGO
																				https://kasu.com.mx/eia/php/Registrar_Venta.php?stat=1&collection_status=approved
																				https://kasu.com.mx/eia/php/Registrar_Venta.php?stat=1&collection_status=notapproved
********************************************************************************************************************************************/
    if (isset($_GET['stat'])){
        if($_GET['collection_status'] != "approved"){
					//Buscar el ultimo registro de venta en preventa
					$MaxVta = Basicas::Max1Dat($mysqli,"Id","Venta","Status","PREVENTA");
					//Enviamos a la pagina para enviar correo con los datos
					header('Location: https://kasu.com.mx/eia/EnviarCorreo.php?MxVta='.$MaxVta);
        }else{
            //Buscar el costo en mercado pago
            $Valor = Basicas::BuscarCampos($mysqli,"Valor","MercadoPago","Referencia",$_GET['external_reference']);
            //Se registra el array de registro de pago
            $DatPago = array(
                "Referencia"        => $_GET['collection_id'],
                "Usuario"           => $VendeDor,
                "Cantidad"          => $Valor,
                "Metodo"            => $_GET['payment_type'],
                "Dia"               => date("j"),
                "Mes"               => date("M"),
                "Ano"               => date("Y"),
                "status"            => $_GET['collection_status'],
                "merchant_order_id" => $_GET['merchant_order_id'],
                "external_reference"=> $_GET['external_reference']
            );
            //Se realiza el insert en la base de datos
            $fyn = Basicas::InsertCampo($mysqli,"Pagos",$DatPago);
            //Redireccionamos
            if($_GET['external_reference'] == "T7G9TD8D"){
                header('Location: https://kasu.com.mx/ActualizacionDatos/index.php?stat='.$_GET['stat'].'&Dtpg='.$fyn);
            }else{
                header('Location: https://kasu.com.mx/registro.php?stat='.$_GET['stat'].'&Dtpg='.$fyn);

            }
        }
  	}
/********************************************************************************************************************************************
                													Carga de CURP cuando la venta es para el cliente
********************************************************************************************************************************************/
    if(isset($_POST['ActuPago'])){
        //Buscar los datos de el cliente en la base de datos
        $UsrVta = Basicas::BuscarCampos($mysqli,"IdContact","Usuario","ClaveCurp",$CurBen);
        $IdVtaCo = Basicas::BuscarCampos($mysqli,"Id","Venta","IdContact",$UsrVta);
        //Se se actualiza el registro del pago
        Basicas::ActCampo($mysqli,"Pagos","IdVenta",$IdVtaCo,$Dtpg);
        //Redireccionamos a la pagina de inicio
        header('Location: https://kasu.com.mx/registro.php');
    }
/********************************************************************************************************************************************
														Registra la venta por ejecutivo de atencion al cliente
********************************************************************************************************************************************/
  if(isset($_POST['RegistroMesa'])){
/********************************************************Carga de DATOS CONTACTO del cliente***********************************************/
		    //Se crea el array que contiene los datos de registro
		    $DatContac = array (
		       "Usuario"   			=> $VendeDor,
		       "Host"      			=> $mysqli -> real_escape_string($Host),
		       "Mail"      			=> $mysqli -> real_escape_string($Mail),
		       "Telefono"  			=> $mysqli -> real_escape_string($Telefono),
					 "calle"          => $mysqli -> real_escape_string($calle),
					 "numero"         => $mysqli -> real_escape_string($numero),
					 "colonia"        => $mysqli -> real_escape_string($colonia),
					 "municipio"      => $mysqli -> real_escape_string($municipio),
					 "estado"         => $mysqli -> real_escape_string($estado),
					 "codigo_postal"  => $mysqli -> real_escape_string($codigo_postal),
		       "Producto"  			=> $mysqli -> real_escape_string($Producto)
		    );
		    //Se realiza el insert en la base de datos
		    $IdContacto = Basicas::InsertCampo($mysqli,"Contacto",$DatContac);
/***************************************Carga de CURP cuando la venta es para el cliente*******************************************/
		    //se busca que el cliente no este duplicado
		    $OPsd = Basicas::BuscarCampos($mysqli,"id","Usuario","ClaveCurp",$CurClie);
				//Se busca que el cliente exista
				$ArrayRes = Seguridad::peticion_get($CurClie);
				//Validamos que el CURP sea de la persona que estaba en prospectos
				$nombre = $ArrayRes["Nombre"]." ".$ArrayRes["Paterno"]." ".$ArrayRes["Materno"];
		    //si el cliente esta duplicado se activa este if
		    if(!empty($OPsd)){
		      	//Se envia el dato al registro para la impresion
		      	header('Location: https://kasu.com.mx/'.$Host.'?curp='.$CurClie.'&Ml=6&Name='.$OPsd);
		      	//Verificamos que el curp exista
				}elseif($ArrayRes["Response"] == "correct" AND $ArrayRes["StatusCurp"] != "BD"){
		      	//Se crea el array que contiene los datos de registro
		        $DatUser = array (
		           	"IdContact"     => $IdContacto,
		            "Usuario"       => $VendeDor,
		            "Tipo"          => "Cliente",
								"Nombre"        => $ArrayRes["Nombre"],
								"Paterno"       => $ArrayRes["Paterno"],
								"Materno"       => $ArrayRes["Materno"],
		            "ClaveCurp"     => $ArrayRes["Curp"],
		            "Email"         => $Mail
		        );
		        //Se realiza el insert en la base de datos
		        Basicas::InsertCampo($mysqli,"Usuario",$DatUser);
		        //Se crea el producto que se esta comprando en base a la edad
		        if($Producto == "FUNERARIO"){
		        		$edad = Basicas::ObtenerEdad($CurClie);
		        		$SubProd = Basicas::ProdFune($edad);
		        		//Creamos las variables de Session
		        		$Producto = $SubProd;
		        }
		        //Buscar precios y tasas
		        $Costo = Basicas::BuscarCampos($mysqli,"Costo","Productos","Producto",$Producto);
		        $Tasa = Basicas::BuscarCampos($mysqli,"TasaAnual","Productos","Producto",$Producto);
/******************************** Carga de MEDIOS DE PAGO de la segunda ventana*******************************************/
		        //Se crea el array que contiene los datos de registro
						$DatLegal = array (
								"IdContacto"    => $IdContacto,
		            "Meses"         => $mysqli -> real_escape_string($Meses),
		            "Terminos"      => $mysqli -> real_escape_string($Terminos),
		            "Aviso"         => $mysqli -> real_escape_string($Aviso),
		            "Fideicomiso"   => $mysqli -> real_escape_string($Fideicomiso)
		        );
						//Se realiza el insert en la base de datos
						Basicas::InsertCampo($mysqli,"Legal",$DatLegal);
						//Si el pago es de Contado para que el pago de contado sea 1
						if($Meses == 0){$Meses = 1;}
						//Se genera la referencia unica del cte MMN
						$firma = Seguridad::Firma($mysqli,$IdContacto,$Costo);
						//Buscamos los datos y realizamos un registro en la venta
						$Venta = array (
								"Usuario"       => $VendeDor,
								"IdContact"     => $IdContacto,
								"Nombre"        => $nombre,
								"Producto"      => $Producto,
								"CostoVenta"    => $Costo,
								"NumeroPagos"   => $mysqli -> real_escape_string($Meses),
								"IdFIrma"       => $mysqli -> real_escape_string($firma),
								"Status"        => "COBRANZA",
								"Mes"           => date("M"),
								"TipoServicio"  => $mysqli -> real_escape_string($TipoServicio)
							);
							//Insertar los datos en la base
							$IdVenta = Basicas::InsertCampo($mysqli,"Venta",$Venta);
							//Se crea el array que contiene los datos para REGISTRO DE EVENTOS
							$DatEventos = array(
									"IdFInger"    => $mysqli -> real_escape_string($fingerprint),
									"Contacto"    => $IdContacto,
									"Evento"      => "Vta",
									"Host"        => $mysqli -> real_escape_string($Host),
									"MetodGet"    => $mysqli -> real_escape_string($formfields),
									"connection"  => $mysqli -> real_escape_string($connection),
									"timezone"    => $mysqli -> real_escape_string($timezone),
									"touch"       => $mysqli -> real_escape_string($touch),
									"FechaRegistro" => date('Y-m-d')." ".date('H:i:s')
							);
							//Se realiza el insert en la base de datos
							Basicas::InsertCampo($mysqli,"Eventos",$DatEventos);
/******************************** Genera la promesa de pago o el pago *******************************************/
							//Variables de el primer pago
							if($Meses != 1){
									//Calculamos el pago y la mora
									$pago = Financieras::Pago($mysqli,$IdVenta);
									$mora = Financieras::Mora($pago);
									//Se registran las promesas de pagos
					    		$Pripg = array (
					    				"IdVta"     => $IdVenta,
					    				"FechaPago" => date('Y-m-d'),
					    				"pago"    	=> $pago,
					    				"Mora"    	=> $mora,
					    				"FechaReg"  => date('Y-m-d'),
					    				"User"     	=> Basicas::BuscarCampos($mysqli,"Usuario","Venta","Id",$IdVenta)
					    		);
					    		//Insertar los datos en la base
					    		Basicas::InsertCampo($mysqli,"PromesaPago",$Pripg);
							}else{
									//Se registra el array de registro de pago
									$DatPago = array(
											"IdVenta"       => $IdVenta,
											"Usuario"       => $_SESSION["Vendedor"],
											"Cantidad"      => $Costo,
											"Metodo"        => "Cobro",
											"status"        => "Normal",
											"FechaRegistro" => date('Y-m-d')." ".date('H:i:s')
									);
									//Se realiza el insert en la base de datos
									Basicas::InsertCampo($mysqli,"Pagos",$DatPago);
									//Calculamos si el credito se ha pagado y se cambia el status de la venta
									$SubTotl = Financieras::SaldoCredito($mysqli,$IdVenta);
									//Se valida que el pago sea menor a cero o igual a cero
									if($SubTotl <= 0 ){
									//Se cambia el status de la venta a ACTIVACION
											Basicas::ActCampo($mysqli,"Venta","Status","ACTIVACION",$IdVenta);
									}
							}
							//Se inserta el estado en la base de datos
							Basicas::ActCampo($pros,"prospectos","Cancelacion",2,$IdProspecto);
							//Se envia el dato al registro para la impresion
							header('Location: https://kasu.com.mx/'.$Host.'?curp='.$CurClie.'&Ml=7&Name='.$OPsd);
		        }else{
							//Se envia el dato al registro para la impresion
							header('Location: https://kasu.com.mx/'.$Host.'?curp='.$CurClie.'&Ml=6&Name='.$OPsd);
						}
		      }
