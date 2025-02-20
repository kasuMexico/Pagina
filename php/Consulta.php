<?php
//Archivo que imprime la busqueda de el cliente en la pagina prinicipal
	    require_once '../eia/librerias.php';
			//SE imprimen las muestras
	    if(isset($_GET['value'])){
				//Se desencripta el valore
				$dat = base64_decode($_GET['value']);
				//Protegemos el valor
				$dat = $mysqli -> real_escape_string($dat);
        //Buscamos si existe el registro CURP en la base de datos
        $cont = $basicas->BuscarCampos($mysqli,"IdContact","Usuario","ClaveCurp",$dat);
				//Se realiza la busqueda de valores
	      if($cont >= 1){
                //Variables Generales;
                $Tabla = "Venta";
                $Refer = "IdContact";
								//Se imprime la informacion a mostrar
                $producto = $basicas->BuscarCampos($mysqli,"Producto",$Tabla,$Refer,$cont);
								//Seleccionamos el producto por categoria
								if($producto == "Universidad"){
									$prodMost = "Inversion Universitaria";
								}elseif($producto == "Retiro"){
									$prodMost = "Retiro Privado";
								}else{
									$prodMost = "Gastos Funerarios";
								}
								//Se crea el array que contiene los datos para REGISTRO DE EVENTOS
								$DatEventos = array(
										"Contacto"  		=> $cont,
										"Host"      		=> $_SERVER['PHP_SELF'],
										"Evento"    		=> "ConsultaCURP",
										"Usuario"    		=> "PLATAFORMA",
										"IdVta"   			=> $basicas->BuscarCampos($mysqli,"Id","Venta","IdContact",$cont),
										"FechaRegistro" => date('Y-m-d')." ".date('H:i:s')
								);
								//Se realiza el insert en la base de datos
								$basicas->InsertCampo($mysqli, "Eventos", $DatEventos);
								//Buscamos el nombre de el cliente
								$Nombre = $basicas->BuscarCampos($mysqli,"Nombre",$Tabla,$Refer,$cont);
								$Paterno = $basicas->BuscarCampos($mysqli,"Paterno",$Tabla,$Refer,$cont);
								$Materno = $basicas->BuscarCampos($mysqli,"Materno",$Tabla,$Refer,$cont);
								//Creamos el nombre
								$Nombre = $Nombre." ".$Paterno." ".$Materno;
                //Imprimimos los datos del cliente
		            echo "
								<div id='FingerPrint' style='display: ;'></div>
								<br>
		              <div>
		                <p>Cliente:</p>
		                <h4><strong>".$Nombre."</strong></h4>
										<br>
		                <p>CURP:</p>
		                <h5>".$dat."</h5>
		                <p>Producto:</p>
		                <h4 >".$prodMost."</h4>
		                <p>Tipo Servicio:</p>
		                <h4 >".$basicas->BuscarCampos($mysqli,"TipoServicio",$Tabla,$Refer,$cont)."</h4>
										<br>
		                <p>Estatus:</p>
		                <h4 >".$basicas->BuscarCampos($mysqli,"Status",$Tabla,$Refer,$cont)."</h4>
		              </div>";
									//Se selecciona el valor por status de la venta
		            if($status == 'COBRANZA' || $status == 'PREVENTA'){
		                //Se imprimen las consultas
		                    echo "
		                      <div>
		                        <p>Pagos Realizados</p>
		                        <h4>".Financieras::SumarPagos($mysqli,"Cantidad","Pagos","IdVenta",$idvta)."</h4>
		                        <p>Pendiente de pagar</p>
		                        <h4>".Financieras::SaldoCredito($mysqli,$idvta)."</h4>
		                      </div>";
													//se
													if($status == 'PREVENTA'){
														//Si el cliente esta en preventa debe contactar a un ejecutivo
													echo '
															<a  class="btn btn-primary" style="margin-top:1.5em; width: 80%;" target="_blank" rel="noopener noreferrer" href="https://api.whatsapp.com/send?phone=527121000245&text=Buen%20dia%20estoy%20interesado%20en%20retomar%20mi%20proceso%20de%20venta%20de%20mi%20Servicio%20'.$producto.'%20mi%20nombre%20es%20'.$nombre.'"> Contactar un Ejecutivo</a>
															<br><br>
															';
													}else{
													echo "
																	<a href='https://kasu.com.mx/login/Generar_PDF/Estado_Cuenta_pdf.php?busqueda=".base64_encode($idvta)."' class='btn btn-secondary' style='margin-top:1.5em; width: 80%;'>Descargar estado de Cta</a>
																	<br><br>
																";
													}
		            }else{
									echo "<div style='margin-top:1em;'>
			                				<a href='https://kasu.com.mx/login/Generar_PDF/Poliza_pdf.php?busqueda=".base64_encode($cont)."' class='btn btn-secondary btn-sm' style='margin-top:.5em;' download>Descargar mi Poliza</a>
			              				</div>";
			               	echo "<div style='margin-top:1em;'>
			                 				<a href='https://kasu.com.mx/ActualizacionDatos/index.php?value=".base64_encode($dat)."' class='btn btn-secondary btn-sm' style='margin-top:.5em; background:#ec7c26'; >Ingresar a mi Cuenta</a>
														</div>
														<br>";
													}
	          }else{
									//Se crea el array que contiene los datos para REGISTRO DE EVENTOS
									$DatEventos = array(
											"Contacto"  		=> $dat,
											"Host"      		=> $_SERVER['PHP_SELF'],
											"Evento"    		=> "ErrorConsulta",
											"Usuario"    		=> "PLATAFORMA",
											"FechaRegistro" => date('Y-m-d')." ".date('H:i:s')
									);
									//Se realiza el insert en la base de datos
									$basicas->InsertCampo($mysqli, "Eventos", $DatEventos);
									//Imprime mensaje que la curp no esta registrada
									echo "
										<div style='padding:2rem; height:250px;'>
											<h6 style='color:black;'>
												No se tiene registro de esta CURP, Verifique si es correcta.
											</h6>
											<br>
											<p>
												Si no se ha resgitrado o le interesa el servicio le invitamos a registrarse en este
												<br>
												<a href='https://kasu.com.mx/registro.php' target='_blank' style='color:#911F66; font-size:1.5rem;'>link</a>
											</p>
										</div>
									";
						}
	      }
