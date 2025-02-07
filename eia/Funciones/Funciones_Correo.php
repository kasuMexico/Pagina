<?PHP
class Correo {
/***********************************************************************************
Esta funcion debe generar un mensaje que se enviara por correo con  los datos de el cliente registrados en la venta;
->Nombre        $Asunto => es el asunto que definira la plantilla del para el mensaje del correo
->Correo        $Cte => Nombre requerido para personalizar el correo
->Asunto        $DirUrl => Direccion requerida para los enlaces que se incluyen en botones

        Variables empleadas solo para la contruccion del mensaje correspodiente a los articulos del blog
$dirUrl1 => url del 1 articulo          $imag1  => imagen del 1 articulo
$Titulo1  => titulo del 1 articulo      $Desc1  => descripcion del articulo 1
$dirUrl2  => url del 2 articulo         $imag2  => imagen del 2 articulo
$Titulo2  => titulo del 2 articulo      $Desc2  => descripcion del articulo 2
$dirUrl3  => url del 3 articulo         $imag3  => imagen del 3 articulo
$Titulo3  => titulo del 3 articulo      $Desc3  => descripcion del articulo 3
$dirUrl4  => url del 4 articulo         $imag4 => imagen del 4 articulo
$Titulo4  => titulo del 4 articulo      $Desc4  => descripcion del articulo 4

***********************************************************************************/
    public function Mensaje($Asunto,$Cte,$DirUrl,$dirUrl1,$imag1,$Titulo1,$Desc1,$dirUrl2,$imag2,$Titulo2,$Desc2,$dirUrl3,$imag3,$Titulo3,$Desc3,$dirUrl4,$imag4,$Titulo4,$Desc4,$Id){
              if($Asunto == '¡BIENVENIDO A KASU!'){
                  $message = $Bienvenida='<!DOCTYPE html>
                  <html lang="ES" xmlns="http://www.w3.org/1999/xhtml">
                      <head>
                          <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
                          <meta name="viewport" content="width=device-width, initial-scale=1.0" />
                          <title>KASU</title>
                          <style type="text/css">
                              @media only screen and (max-width:600px){
                                  ul li, ol li, p,{
                                    font-size: 18px !important;
                                    line-height: 150% !important;
                                    text-align: Justify !important;
                                  }
                                  h1 {
                                    font-size: 20px !important;
                                    text-align: Justify;
                                    line-height: 120% !important
                                  }
                                  h1 a {
                                    font-size: 18px !important
                                  }
                                  *[class="gmail-fix"] {
                                    display: none !important
                                  }
                                  a.es-button {
                                    font-size: 35px !important;
                                    color: whitesmoke !important;
                                    display: block !important;
                                    text-decoration: none;
                                    text-align: center !important;
                                    border-left-width: 0px !important;
                                    border-right-width: 0px !important
                                  }
                                  table.es-table-not-adapt,
                                  .esd-block-html table {
                                    width: auto !important
                                  }
                              }
                              #outlook a {
                                padding: 0;
                              }
                              a[x-apple-data-detectors] {
                                color: inherit !important;
                                text-decoration: none !important;
                                font-size: inherit !important;
                                font-family: inherit !important;
                                font-weight: inherit !important;
                                line-height: inherit !important;
                              }
                          </style>
                  </head>
                  <body style="margin: 0; padding: 0;">
                      <table align="center" cellpadding="0" cellspacing="0" width="600px" style="border: 1px solid #cccccc; background-position:center top;background-image:url(http://kasu.com.mx/assets/images/Correo/fondo.jpg);">
                          <tr>
                              <td style="padding: 20px 0 30px 0">
                                  <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:transparent;">
                                      <tr>
                                          <td align="center" style="padding: 2px 2px 15px 2px; background-repeat:no-repeat; color: #153643; font-family: Arial, sans-serif; font-size: 25px;">
                                              <a href="https://kasu.com.mx" style="text-decoration: none; color: whitesmoke;">
                                              <img src="https://kasu.com.mx/assets/images/Correo/florredonda.png" alt="" width="100px" height="100px" style="display: block;" /></a>
                                          </td>
                                      </tr>
                                      <tr>
                                          <td bgcolor="#ffffff" align="justify" style="padding:20px 15px 5px 25px; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 25px;">
                                              <b style="vertical-align:inherit;"> '.$Cte.'</b>
                                          </td>
                                      </tr>
                                      <tr style="border-collapse:collapse;">
                                          <td align="left" bgcolor="#ffffff" style="padding:10px 30px 5px 30px; background-repeat:no-repeat;">
                                              <p style="font-size:16px;font-family:arial, sans-serif;line-height:24px;color:#666666;">Felicidades por ser cliente KASU.</p>
                                              <p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:16px;font-family:arial, sans-serif;line-height:24px;color:#666666;">
                                              Es un honor informarte que tu póliza ya está activa, gracias por permitirnos solventar los momentos más importantes de tu vida y la de los tuyos. Descarga tu póliza dando click en el siguiente botón.</p>
                                          </td>
                                      </tr>
                                      <tr>
                                          <td bgcolor="#ffffff" align="center" style="padding:30px;">
                                              <span class="es-button-border" style="border-style:solid;border-color:#ee3a87;background:#ee3a87;border-width:20px;display:inline-block;border-radius:5px;width:auto;">
                                                  <a href="https://kasu.com.mx/login/Generar_PDF/Poliza_pdf.php?busqueda='.$DirUrl.'" class="es-button" target="_blank" style="mso-style-priority:100 !important;text-decoration:none;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial,sans-serif;font-size:30px;color:#FFFFFF;border-style:solid;border-color:#ee3a87;display:inline-block;background:#ee3a87;border-radius:5px;font-weight:normal;font-style:normal;line-height:30px;width:auto;text-align:center;">Descargar Poliza</a>
                                              </span>
                                          </td>
                                      </tr>
                                      <tr>
                                          <td bgcolor="#ffffff" align="justify" style="padding: 5px 25px 30px 25px; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
                                              <p align="justify" style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:16px;font-family:arial, sans-serif;line-height:24px;color:#666666;">Recuerda que en todo momento puedes consultar tus datos y descargar tu poliza en kasu.com.mx<br /> </p>
                                              <p align="justify" style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:12px;font-family:arial, sans-serif;line-height:24px;color:#666666;"><br/>Saludos <br />Equipo KASU</p>
                                          </td>
                                      </tr>
                                      <tr>
                                          <td style="padding: 15px 30px 5px 30px;">
                                              <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:transparent;">
                                                  <tr>
                                                      <td align="center" width="100%">
                                                          <table border="0" cellpadding="0" cellspacing="0">
                                                              <tr>
                                                                  <td>
                                                                      <a href="https://www.facebook.com/KasuMexico">
                                                                          <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/facebook-circle-colored.png" alt="Fb" title="Facebook" width="32" height="32" style="display:block; border:0; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic;" />
                                                                      </a>
                                                                  </td>
                                                                  <td style=" font-size: 0; line-height: 0; width: 20px">&nbsp;</td>
                                                                  <td>
                                                                      <a href="https://twitter.com/KASSU_11">
                                                                          <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/twitter-circle-colored.png" alt="Tw" title="Twitter" width="32" height="32" style="display:block; border:0; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic;" />
                                                                      </a>
                                                                  </td>
                                                                  <td style=" font-size: 0; line-height: 0; width: 20px">&nbsp;</td>
                                                                  <td>
                                                                      <a href="https://www.instagram.com/kasumexico">
                                                                          <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/instagram-circle-colored.png" alt="Ig" title="Instagram" width="32" height="32" style="display:block; border:0; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic;" />
                                                                      </a>
                                                                  </td>
                                                              </tr>
                                                          </table>
                                                      </td>
                                                      <tr>
                                                          <td align="center" width="100%" style="font-family: Arial, sans-serif; font-size: 14px; padding: 0 10px 0 10px">
                                                              <p style="text-align: center !important;">
                                                                  <font style="vertical-align:inherit;">
                                                                      © 2019&nbsp;Kasu Servicios a futuro&nbsp;<br>
                                                                  </font>
                                                                  <a href="https://kasu.com.mx/index.php?Ml=4&Id='.$Id.'" style="color: #153643;">
                                                                      <font>Ya no quiero recibir estos correo</font>
                                                                  </a>
                                                              </p>
                                                          </td>
                                                      </tr>
                                                  </table>
                                              </td>
                                          </tr>
                                      </table>
                                  </td>
                              </tr>
                          </table>
                      </body>
                  </html>';
              }
              elseif($Asunto == 'GRACIAS POR SUSCRIBIRTE'){
                  $message = $Suscripcion='<!DOCTYPE html>
                  <html lang="ES" xmlns="http://www.w3.org/1999/xhtml">
                      <head>
                          <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
                          <meta name="viewport" content="width=device-width, initial-scale=1.0" />
                          <title>KASU</title>
                          <style type="text/css">
                              @media only screen and (max-width:600px) {
                                  ul li, ol li, p, {
                                      font-size: 15px !important;
                                      line-height: 150% !important;
                                      text-align: Justify !important;
                                  }
                                  h1 {
                                      font-size: 18px !important;
                                      text-align: Justify;
                                      line-height: 120% !important
                                    }
                                  h1 a {
                                     font-size: 15px !important
                                  }
                                  *[class="gmail-fix"] {
                                    display: none !important
                                  }
                                  a.es-button {
                                    font-size: 15px !important;
                                    color: whitesmoke !important;
                                    display: block !important;
                                    text-decoration: none;
                                    text-align: center !important;
                                    border-left-width: 0px !important;
                                    border-right-width: 0px !important
                                }
                                table.es-table-not-adapt,
                                .esd-block-html table {
                                    width: auto !important
                                  }
                                }
                                #outlook a {
                                  padding: 0;
                                }
                                a[x-apple-data-detectors] {
                                    color: inherit !important;
                                    text-decoration: none !important;
                                    font-size: inherit !important;
                                    font-family: inherit !important;
                                    font-weight: inherit !important;
                                    line-height: inherit !important;
                                }
                          </style>
                      </head>
                      <body style="margin: 0; padding: 0;">
                          <table align="center" cellpadding="0" cellspacing="0" width="600px" style="border: 1px solid #cccccc; background-position:center top;background-image:url(http://kasu.com.mx/assets/images/Correo/fondo.jpg);">
                              <tr>
                                  <td style="padding: 20px 0 30px 0">
                                      <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:transparent;">
                                          <tr>
                                              <td align="center" style="padding: 2px 2px 15px 2px; background-repeat:no-repeat; color: #153643; font-family: Arial, sans-serif; font-size: 25px;">
                                                  <a href="https://kasu.com.mx" style="text-decoration: none; color: whitesmoke;">
                                                  <img src="https://kasu.com.mx/assets/images/Correo/florredonda.png" alt="" width="100px" height="100px" style="display: block;" /></a>
                                              </td>
                                          </tr>
                                          <tr>
                                              <td bgcolor="#ffffff" align="justify" style="padding:20px 15px 5px 25px; color: #153643; font-family: Arial, sans-serif; font-size: 16px;">
                                                  <h1><b>¡BIENVENIDO A KASU NEWS!</b></h1>
                                              </td>
                                          </tr>
                                          <tr>
                                              <td bgcolor="#ffffff" align="justify" style="padding: 5px 25px 30px 25px; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 25px;">
                                                  <p>Desde ahora, te mantendremos informado sobre las entradas mas recientes o bien te enviaremos unas sugerencias de lectura personalizadas para tí<br /> <br />He aquí las entradas de esta semana</p>
                                              </td>
                                          </tr>
                                          <tr>
                                              <td bgcolor="#ffffff" style="padding: 15px 25px 15px 25px;">
                                                  <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                                      <tr>
                                                          <td align="center" width="260px" valign="top">
                                                              <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                                                  <tr>
                                                                      <td>
                                                                          <a href="'.$dirUrl1.'">
                                                                              <img src="'.$imag1.'" alt="" width="100%" height="140px" style="display: block;" />
                                                                          </a>
                                                                      </td>
                                                                  </tr>
                                                                  <tr>
                                                                      <td style="padding: 25px 0 0 0; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
                                                                          <a href="'.$dirUrl1.'" style="text-decoration: none; color: black;">
                                                                              <b>'.$Titulo1.'
                                                                              </b>
                                                                              <br>
                                                                              <p>'.$Desc1.'</p>
                                                                          </a>
                                                                      </td>
                                                                  </tr>
                                                              </table>
                                                          </td>
                                                          <td style="font-size: 0; line-height: 0;" width="20px">&nbsp;</td>
                                                              <td align="center" width="260px" valign="top">
                                                                  <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                                                      <tr>
                                                                          <td style="color: #153643;">
                                                                              <a href="'.$dirUrl2.'">
                                                                                  <img src="'.$imag2.'" alt="" width="100%" height="140px" style="display: block;" />
                                                                              </a>
                                                                          </td>
                                                                      </tr>
                                                                      <tr>
                                                                          <td style="padding: 25px 0 0 0; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
                                                                              <a href="'.$dirUrl2.'" style="text-decoration: none; color: black;">
                                                                                  <b>'.$Titulo2.'
                                                                                  </b>
                                                                                  <br>
                                                                                  <p>'.$Desc2.'</p>
                                                                              </a>
                                                                          </td>
                                                                      </tr>
                                                                  </table>
                                                              </td>
                                                          </td>
                                                          <tr>
                                                              <td align="center" width="260px" valign="top">
                                                                  <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                                                      <tr>
                                                                          <td style="padding: 25px 0 0 0; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
                                                                              <a href="'.$dirUrl3.'">
                                                                                  <img src="'.$imag3.'" alt="" width="100%" height="140px" style="display: block;" />
                                                                              </a>
                                                                          </td>
                                                                      </tr>
                                                                      <tr>
                                                                          <td style="padding: 25px 0 0 0; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
                                                                              <a href="'.$dirUrl3.'" style="text-decoration: none; color: black;">
                                                                                  <b>'.$Titulo3.'
                                                                                  </b>
                                                                                  <br>
                                                                                  <p>'.$Desc3.'</p>
                                                                              </a>
                                                                          </td>
                                                                      </tr>
                                                                  </table>
                                                              </td>
                                                              <td style="font-size: 0; line-height: 0;" width="20px">&nbsp;</td>
                                                                  <td align="center" width="260px" valign="top">
                                                                      <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                                                          <tr>
                                                                              <td style="padding: 25px 0 0 0; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
                                                                                  <a href="'.$dirUrl4.'">
                                                                                      <img src="'.$imag4.'" alt="" width="100%" height="140px" style="display: block;" />
                                                                                  </a>
                                                                              </td>
                                                                          </tr>
                                                                          <tr>
                                                                              <td style="padding: 25px 0 0 0; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
                                                                                  <a href="'.$dirUrl4.'" style="text-decoration: none; color: black;">
                                                                                      <b>'.$Titulo4.'
                                                                                      </b>
                                                                                      <br>
                                                                                      <p>'.$Desc4.'</p>
                                                                                  </a>
                                                                              </td>
                                                                          </tr>
                                                                      </table>
                                                                  </td>
                                                              </td>
                                                          </tr>
                                                      </tr>
                                                  </table>
                                              </td>
                                          </tr>
                                          <tr>
                                              <td bgcolor="#ffffff" align="justify" style="padding: 5px 25px 30px 25px; color: #153643; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px;">
                                                  <p>Saludos <br/>Equipo KASU</p>
                                              </td>
                                          </tr>
                                          <tr>
                                              <td style="padding: 15px 30px 5px 30px;">
                                                  <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:transparent;">
                                                      <tr>
                                                          <td align="center" width="100%">
                                                              <table border="0" cellpadding="0" cellspacing="0">
                                                                  <tr>
                                                                      <td>
                                                                          <a href="https://www.facebook.com/KasuMexico">
                                                                              <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/facebook-circle-colored.png" alt="Fb" title="Facebook" width="32" height="32" style="display:block; border:0; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic;" />
                                                                          </a>
                                                                      </td>
                                                                      <td style=" font-size: 0; line-height: 0; width: 20px">&nbsp;</td>
                                                                      <td>
                                                                          <a href="https://twitter.com/KASSU_11">
                                                                              <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/twitter-circle-colored.png" alt="Tw" title="Twitter" width="32" height="32" style="display:block; border:0; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic;" />
                                                                          </a>
                                                                      </td>
                                                                      <td style=" font-size: 0; line-height: 0; width: 20px">&nbsp;</td>
                                                                      <td>
                                                                          <a href="https://www.instagram.com/kasumexico">
                                                                              <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/instagram-circle-colored.png" alt="Ig" title="Instagram" width="32" height="32" style="display:block; border:0; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic;" />
                                                                          </a>
                                                                      </td>
                                                                  </tr>
                                                              </table>
                                                          </td>
                                                          <tr>
                                                              <td align="center" width="100%" style="font-family: Arial, sans-serif; font-size: 14px; padding: 0 10px 0 10px">
                                                                  <p style="text-align: center !important;">
                                                                      <font style="vertical-align:inherit;">
                                                                          © 2019&nbsp;Kasu Servicios a futuro&nbsp;| <br> Bosque de Chapultepec, Pedregal 24, Molino del Rey <br> Ciudad de México, CDMX, Mexico C.P. 11000 <br>
                                                                      </font>
                                                                      <a href="https://kasu.com.mx/index.php?Ml=4&Id='.$Id.'" style="color: #153643;">
                                                                          <font>Ya no quiero recibir este correo</font>
                                                                      </a>
                                                                  </p>
                                                              </td>
                                                          </tr>
                                                      </tr>
                                                  </table>
                                              </td>
                                          </tr>
                                      </table>
                                  </td>
                              </tr>
                          </table>
                      </body>
                  </html>';
              }
              elseif($Asunto == 'ARTÍCULOS SUGERIDOS'){
                  $message = $Lectura='<!DOCTYPE html>
                  <html lang="ES" xmlns="http://www.w3.org/1999/xhtml">
                      <head>
                  	     <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
                  	     <meta name="viewport" content="width=device-width, initial-scale=1.0" />
                  	     <title>KASU</title>
                  	     <style type="text/css">
                              @media only screen and (max-width:600px) {
                                  ul li,ol li, p,{
                                      font-size: 18px !important;
                                      line-height: 150% !important;
                                      text-align: Justify !important;
                                  }h1 {
                                      font-size: 20px !important;
                                      text-align: justify;
                                      line-height: 120% !important
                                  }h1 a {
                                      font-size: 18px !important
                                  }*[class="gmail-fix"] {
                                      display: none !important
                                  }a.es-button {
                                      font-size: 20px !important;
                                      color: whitesmoke !important;
                                      display: block !important;
                                      text-decoration: none;
                                      text-align: center !important;
                                      border-left-width: 0px !important;
                                      border-right-width: 0px !important
                                  }table.es-table-not-adapt,.esd-block-html table {
                                      width: auto !important
                                  }
                              }
                              #outlook a {
                                  padding: 0;
                              }
                              a[x-apple-data-detectors] {
                                  color: inherit !important;
                                  text-decoration: none !important;
                                  font-size: inherit !important;
                                  font-family: inherit !important;
                                  font-weight: inherit !important;
                                  line-height: inherit !important;
                              }
                         </style>
                      </head>
                      <body style="margin: 0; padding: 0;">
                          <table align="center" cellpadding="0" cellspacing="0" width="600px" style="border: 1px solid #cccccc; border-collapse: collapse; background-position:center top;background-image:url(http://kasu.com.mx/assets/images/Correo/fondo.jpg);">
                      		    <tr>
                      			       <td style="padding: 20px 0 30px 0;">
                      				         <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:transparent;">
                      					            <tr>
                                                <td align="center" style="padding: 2px 2px 15px 2px; background-repeat:no-repeat; color: #153643; font-family: Arial, sans-serif; font-size: 25px;">
                                                    <a href="https://kasu.com.mx" style="text-decoration: none; color: whitesmoke;">
                                                        <img src="https://kasu.com.mx/assets/images/Correo/florredonda.png" alt="" width="100px" height="100px" style="display: block;" />
                                                    </a>
                                                </td>
                                            </tr>
                      					            <tr>
                      						              <td bgcolor="#ffffff" style="padding: 25px 25px 10px 25px; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
                      							                <b>¡'.$Cte.' estos articulos podrian interesarte!
                                                    </b>
                                                    <br>
                      							                <p>Queremos poner a tu alcance los mejores articulos de interes</p>
                      						              </td>
                      					            </tr>
                  					                <tr>
                  						                  <td bgcolor="#ffffff" style="padding: 15px 25px 15px 25px;">
                  							                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                  								                      <tr>
                  									                        <td align="center" width="260px" valign="top">
                  										                          <table border="0" cellpadding="0" cellspacing="0" width="100%">
                  											                            <tr>
                  												                              <td>
                  													                                <a href="'.$dirUrl1.'">
                  														                                  <img src="'.$imag1.'" alt="" width="100%" height="140px" style="display: block;" />
                                                                            </a>
                  												                              </td>
                  											                            </tr>
                  											                            <tr>
                                                												<td style="padding: 25px 0 0 0; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
                                                													  <a href="'.$dirUrl1.'" style="text-decoration: none; color: black;">
                                                    													 <b>'.$Titulo1.'
                                                                               </b>
                                                                               <br>
                                                    													 '.$Desc1.'
                                                													  </a>
                                                												</td>
                                                										</tr>
                                                								</table>
                                                            </td>
                  									                        <td style="font-size: 0; line-height: 0;" width="20px">&nbsp;</td>
                  									                        <td align="center" width="260px" valign="top">
                  										                          <table border="0" cellpadding="0" cellspacing="0" width="100%">
                  											                            <tr>
                  												                              <td style="color: #153643;">
                                                                            <a href="'.$dirUrl2.'">
                                                                                <img src="'.$imag2.'" alt="" width="100%" height="140px" style="display: block;" />
                                                                            </a>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="padding: 25px 0 0 0; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
                                                                            <a href="'.$dirUrl2.'" style="text-decoration: none; color: black;">
                                                                                <b>'.$Titulo2.'
                                                                                </b>
                                                                                <br>
                                                                                '.$Desc2.'
                                                                            </a>
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td align="center" width="260px" valign="top">
                                                                <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                                                    <tr>
                                                                        <td style="padding: 25px 0 0 0;">
                                                                            <a href="'.$dirUrl3.'">
                                                                                <img src="'.$imag3.'" alt="" width="100%" height="140px" style="display: block;" />
                                                                            </a>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="padding: 25px 0 0 0; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
                                                                            <a href="'.$dirUrl3.'" style="text-decoration: none; color: black;">
                                                                                <b>'.$Titulo3.'
                                                                                </b>
                                                                                <br>
                                                                                '.$Desc3.'
                                                                            </a>
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                            <td style="font-size: 0; line-height: 0;" width="20px">&nbsp;
                                                            </td>
                                                            <td align="center" width="260px" valign="top">
                                                                <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                                                    <tr>
                                                                        <td style="padding: 25px 0 0 0;">
                                                                            <a href="'.$dirUrl4.'">
                                                                                <img src="'.$imag4.'" alt="" width="100%" height="140px" style="display: block;" />
                                                                            </a>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="padding: 25px 0 0 0; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
                                                                            <a href="'.$dirUrl4.'" style="text-decoration: none; color: black;">
                                                                                <b>'.$Titulo4.'
                                                                                </b>
                                                                                <br>
                                                                                '.$Desc4.'
                                                                            </a>
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 45px 30px 5px 30px;">
                                                    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:transparent;">
                                                        <tr>
                                                            <td align="center" width="100%">
                                                                <table border="0" cellpadding="0" cellspacing="0">
                                                                    <tr>
                                                                        <td>
                                                                            <a href="https://www.facebook.com/KasuMexico">
                                                                                <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/facebook-circle-colored.png" alt="Fb" title="Facebook" width="32" height="32" style="display:block; border:0; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic;" />
                                                                            </a>
                                                                        </td>
                                                                        <td style=" font-size: 0; line-height: 0; width: 20px">&nbsp;</td>
                                                                        <td>
                                                                            <a href="https://twitter.com/KASSU_11">
                                                                                <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/twitter-circle-colored.png" alt="Tw" title="Twitter" width="32" height="32" style="display:block; border:0; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic;" />
                                                                            </a>
                                                                        </td>
                                                                        <td style=" font-size: 0; line-height: 0; width: 20px">&nbsp;</td>
                                                                        <td>
                                                                            <a href="https://www.instagram.com/kasumexico">
                                                                                <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/instagram-circle-colored.png" alt="Ig" title="Instagram" width="32" height="32" style="display:block; border:0; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic;" />
                                                                            </a>
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                            <tr>
                                                                <td align="center" width="100%" style="font-family: Arial, sans-serif; font-size: 14px; padding: 10px 10px 0 10px">
                                                                    <p style="text-align: center !important;">
                                                                        <font style="vertical-align:inherit;">
                                                                            © 2019&nbsp;Kasu Servicios a futuro&nbsp;| <br> Bosque de Chapultepec, Pedregal 24, Molino del Rey <br> Ciudad de México, CDMX, Mexico C.P. 11000 <br>
                                                                        </font>
                                                                        <a href="https://kasu.com.mx/index.php?Ml=4&Id='.$Id.'&dat='.$Desc1.'" style="color: #153643;">
                                                                            <font>Ya no quiero recibir este correo</font>
                                                                        </a>
                                                                    </p>
                                                                </td>
                                                            </tr>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </body>
                    </html>';
              }
              elseif($Asunto == 'PAGO PENDIENTE'){
                  $message = $Now='<!DOCTYPE html>
                  <html lang="ES" xmlns="http://www.w3.org/1999/xhtml">

                  <head>
                    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
                    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
                    <title>KASU</title>
                    <style type="text/css">
                      @media only screen and (max-width:600px) {
                        ul li,
                        ol li, p,
                         {
                          font-size: 16px !important;
                          line-height: 150% !important;
                          text-align: Justify !important;
                        }
                        h1 {
                          font-size: 20px !important;
                          text-align: center;
                          line-height: 120% !important
                        }
                        h1 a {
                          font-size: 18px !important
                        }
                        *[class="gmail-fix"] {
                          display: none !important
                        }
                        a.es-button {
                          font-size: 35px !important;
                          color: whitesmoke !important;
                          display: block !important;
                          text-decoration: none;
                          text-align: center !important;
                          border-left-width: 0px !important;
                          border-right-width: 0px !important
                        }
                        table.es-table-not-adapt,
                        .esd-block-html table {
                          width: auto !important
                        }
                      }
                      #outlook a {
                        padding: 0;
                      }
                      a[x-apple-data-detectors] {
                        color: inherit !important;
                        text-decoration: none !important;
                        font-size: inherit !important;
                        font-family: inherit !important;
                        font-weight: inherit !important;
                        line-height: inherit !important;
                      }
                    </style>
                  </head>
                  <body style="margin: 0; padding: 0;">
                    <table align="center" cellpadding="0" cellspacing="0" width="600px" style="border: 1px solid #cccccc; background-position:center top;background-image:url(http://kasu.com.mx/assets/images/Correo/fondo.jpg);">
                      <tr>
                        <td style="padding: 20px 0 30px 0">
                          <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:transparent;">
                            <tr>
                              <td align="center" style="padding: 2px 2px 15px 2px; background-repeat:no-repeat; color: #153643; font-family: Arial, sans-serif; font-size: 25px;">
                                <a href="https://kasu.com.mx" style="text-decoration: none; color: whitesmoke;">
                                  <img src="https://kasu.com.mx/assets/images/Correo/florredonda.png" alt="" width="100px" height="100px" style="display: block;" /></a>
                              </td>
                            </tr>
                            <tr>
                              <td bgcolor="#ffffff" align="justify" style="padding:20px 15px 5px 25px; color: #153643; font-family: Arial, sans-serif; font-size: 16px;">
                                <b>Estimado cliente: '.$Cte.' </b><br/>
                                 <p>Para continuar con tu pago por tarjeta preciona el siguiente botón.</p>
                              </td>
                            </tr>
                            <tr>
                              <td bgcolor="#ffffff" align="center" style="padding:30px;">
                                <span class="es-button-border" style="border-style:solid;border-color:#ee3a87;background:#ee3a87;border-width:20px;display:inline-block;border-radius:5px;width:auto;">
                                  <a href="'.$DirUrl.'" class="es-button" target="_blank" style="mso-style-priority:100 !important;text-decoration:none;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial,sans-serif;font-size:35px;color:#FFFFFF;border-style:solid;border-color:#ee3a87;display:inline-block;background:#ee3a87;border-radius:5px;font-weight:normal;font-style:normal;line-height:40px;width:auto;text-align:center;">Pagar ahora</a>
                                </span>
                              </td>
                            </tr>
                            <tr>
                              <td bgcolor="#ffffff" align="justify" style="padding: 5px 25px 30px 25px; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
                                <p align="justify" style="background-color:azure;">"Recuerda que si pagas con tarjeta NO se te llamara para solicitarte tu claves o NIP para ningun tramite. No lo proporciones si te llaman y te los piden"</p>
                                <p>Gracias por formar parte de la comunidad KASU.</p>
                                <p style="text-align: justify!important; font-family: Arial, sans-serif; font-size: 12px;">Si el botón no funciona preciona el siguiente enlace.
                                   <a href="'.$DirUrl.'" style="text-decoration: none; color:cornflowerblue; "><b>PAGAR</b></a>
                                </p>
                              </td>
                            </tr>
                            <tr>
                              <td style="padding: 15px 30px 5px 30px;">
                                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:transparent;">
                                  <tr>
                                    <td align="center" width="100%">
                                      <table border="0" cellpadding="0" cellspacing="0">
                                        <tr>
                                          <td>
                                            <a href="https://www.facebook.com/KasuMexico">
                                              <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/facebook-circle-colored.png" alt="Fb" title="Facebook" width="32" height="32" style="display:block; border:0; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic;" />
                                            </a>
                                          </td>
                                          <td style=" font-size: 0; line-height: 0; width: 20px">&nbsp;</td>
                                          <td>
                                            <a href="https://twitter.com/KASSU_11">
                                              <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/twitter-circle-colored.png" alt="Tw" title="Twitter" width="32" height="32" style="display:block; border:0; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic;" />
                                            </a>
                                          </td>
                                          <td style=" font-size: 0; line-height: 0; width: 20px">&nbsp;</td>
                                          <td>
                                            <a href="https://www.instagram.com/kasumexico">
                                              <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/instagram-circle-colored.png" alt="Ig" title="Instagram" width="32" height="32" style="display:block; border:0; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic;" />
                                            </a>
                                          </td>
                                        </tr>
                                      </table>
                                    </td>
                                  <tr>
                                    <td align="center" width="100%" style="font-family: Arial, sans-serif; font-size: 14px; padding: 0 10px 0 10px">
                                      <p style="text-align: center !important;">
                                        <font style="vertical-align:inherit;">
                                          © 2019&nbsp;Kasu Servicios a futuro&nbsp;| <br> Bosque de Chapultepec, Pedregal 24, Molino del Rey <br> Ciudad de México, CDMX, Mexico C.P. 11000 <br>
                                        </font>
                                        <a href="https://kasu.com.mx/index.php?Ml=4&Id='.$Id.'" style="color: #153643;">
                                            <font>Ya no quiero recibir este correo</font>
                                        </a>
                                      </p>
                                    </td>
                                  </tr>
                                </table>
                              </td>
                            </tr>
                          </table>
                        </td>
                      </tr>
                    </table>
                  </body>
                  </html>';
              }
              elseif($Asunto == 'FICHAS DE PAGO KASU'){
                  $message = $Fichas='<!DOCTYPE html>
                  <html lang="ES" xmlns="http://www.w3.org/1999/xhtml">
                  <head>
                    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
                    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
                    <title>KASU</title>
                    <style type="text/css">
                      @media only screen and (max-width:600px) {
                        ul li,
                        ol li, p,
                         {
                          font-size: 14px !important;
                          line-height: 150% !important;
                          text-align: Justify !important;
                        }
                        h1 {
                          font-size: 20px !important;
                          text-align: center;
                          line-height: 120% !important
                        }
                        h1 a {
                          font-size: 18px !important
                        }
                        *[class="gmail-fix"] {
                          display: none !important
                        }
                        a.es-button {
                          font-size: 20px !important;
                          color: whitesmoke !important;
                          display: block !important;
                          text-decoration: none;
                          text-align: center !important;
                          border-left-width: 0px !important;
                          border-right-width: 0px !important
                        }
                        table.es-table-not-adapt,
                        .esd-block-html table {
                          width: auto !important
                        }
                      }
                      #outlook a {
                        padding: 0;
                      }
                      a[x-apple-data-detectors] {
                        color: inherit !important;
                        text-decoration: none !important;
                        font-size: inherit !important;
                        font-family: inherit !important;
                        font-weight: inherit !important;
                        line-height: inherit !important;
                      }
                    </style>
                  </head>
                  <body style="margin: 0; padding: 0;">
                    <table align="center" cellpadding="0" cellspacing="0" width="600px" style="border: 1px solid #cccccc; background-position:center top;background-image:url(http://kasu.com.mx/assets/images/Correo/fondo.jpg);">
                      <tr>
                        <td style="padding: 20px 0 30px 0">
                          <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:transparent;">
                            <tr>
                              <td align="center" style="padding: 2px 2px 15px 2px; background-repeat:no-repeat; color: #153643; font-family: Arial, sans-serif; font-size: 25px;">
                                <a href="https://kasu.com.mx" style="text-decoration: none; color: whitesmoke;">
                                  <img src="https://kasu.com.mx/assets/images/Correo/florredonda.png" alt="" width="100px" height="100px" style="display: block;" /></a>
                              </td>
                            </tr>
                            <tr>
                              <td bgcolor="#ffffff" align="justify" style="padding:20px 15px 5px 25px; color: #153643; font-family: Arial, sans-serif; font-size: 16px;">
                                <b> '.$Cte.' </b><br/>
                                 <p>Con la finalidad que no olvides o pases por alto tus fechas de pago te enviamos tus fichas, solo da click en el boton y descargalas para que puedas realizar tus pagos en cualquier banco o tienda Oxxo.</p>
                              </td>
                            </tr>
                            <tr>
                              <td bgcolor="#ffffff" align="center" style="padding:30px;">
                                <span class="es-button-border" style="border-style:solid;border-color:#ee3a87;background:#ee3a87;border-width:20px;display:inline-block;border-radius:5px;width:auto;">
                                  <a href="https://kasu.com.mx/login/Generar_PDF/Fichas_Pago_pdf.php?Cte='.$DirUrl.'&data='.$dirUrl1.'" class="es-button" target="_blank" style="mso-style-priority:100 !important;text-decoration:none;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial,sans-serif;font-size:25px;color:#FFFFFF;border-style:solid;border-color:#ee3a87;display:inline-block;background:#ee3a87;border-radius:5px;font-weight:normal;font-style:normal;line-height:30px;width:auto;text-align:center;">Descargar ahora</a>
                                </span>
                              </td>
                            </tr>
                            <tr>
                              <td bgcolor="#ffffff" align="justify" style="padding: 5px 25px 30px 25px; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
                                <p>Para cualquier duda o asesoria sobre tus fichas de pago:  <b>llamanos 7125975763 </b> <br> o contactanos por medio de redes sociales, en nuestro chat de <a href="https://www.facebook.com/KasuMexico" style="text-decoration: none; color:cornflowerblue;"><b>facebook</b></a>.</p>
                                <p align="justify" style="background-color:azure;">"Recuerda que si pagas con tarjeta nunca se te llamara para solicitarte tu claves o NIP para ningun tramite. No lo proporciones si te llaman y te los piden"</p>
                                <p>Gracias por formar parte de la comunidad KASU.</p>

                              </td>
                            </tr>
                            <tr>
                              <td style="padding: 15px 30px 5px 30px;">
                                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:transparent;">
                                  <tr>
                                    <td align="center" width="100%">
                                      <table border="0" cellpadding="0" cellspacing="0">
                                        <tr>
                                          <td>
                                            <a href="https://www.facebook.com/KasuMexico">
                                              <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/facebook-circle-colored.png" alt="Fb" title="Facebook" width="32" height="32" style="display:block; border:0; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic;" />
                                            </a>
                                          </td>
                                          <td style=" font-size: 0; line-height: 0; width: 20px">&nbsp;</td>
                                          <td>
                                            <a href="https://twitter.com/KASSU_11">
                                              <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/twitter-circle-colored.png" alt="Tw" title="Twitter" width="32" height="32" style="display:block; border:0; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic;" />
                                            </a>
                                          </td>
                                          <td style=" font-size: 0; line-height: 0; width: 20px">&nbsp;</td>
                                          <td>
                                            <a href="https://www.instagram.com/kasumexico">
                                              <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/instagram-circle-colored.png" alt="Ig" title="Instagram" width="32" height="32" style="display:block; border:0; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic;" />
                                            </a>
                                          </td>
                                        </tr>
                                      </table>
                                    </td>
                                  <tr>
                                    <td align="center" width="100%" style="font-family: Arial, sans-serif; font-size: 14px; padding: 0 10px 0 10px">
                                      <p style="text-align: center !important;">
                                        <font style="vertical-align:inherit;">
                                          © 2019&nbsp;Kasu Servicios a futuro&nbsp;| <br> Bosque de Chapultepec, Pedregal 24, Molino del Rey <br> Ciudad de México, CDMX, Mexico C.P. 11000 <br>
                                        </font>
                                      </p>
                                    </td>
                                  </tr>
                                </table>
                              </td>
                            </tr>
                          </table>
                        </td>
                      </tr>
                    </table>
                  </body></html>';
              }
              elseif($Asunto == '¡INTEGRATE A KASU!'){
                  $message = $Venta='<!DOCTYPE html>
                  <html lang="ES" xmlns="http://www.w3.org/1999/xhtml">
                  <head>
                    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
                    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
                    <title>KASU</title>
                    <style type="text/css">
                      @media only screen and (max-width:600px) {
                        ul li,
                        ol li, p,
                         {
                          font-size: 20px !important;
                          line-height: 150% !important;
                          text-align: Justify !important;
                        }
                        h1 {
                          font-size: 16px !important;
                          text-align: justify;
                          line-height: 120% !important
                        }
                        h1 a {
                          font-size: 18px !important
                        }
                        *[class="gmail-fix"] {
                          display: none !important
                        }
                        a.es-button {
                          font-size: 20px !important;
                          color: whitesmoke !important;
                          display: block !important;
                          text-decoration: none;
                          text-align: center !important;
                          border-left-width: 0px !important;
                          border-right-width: 0px !important
                        }
                        table.es-table-not-adapt,
                        .esd-block-html table {
                          width: auto !important
                        }
                      }
                      #outlook a {
                        padding: 0;
                      }
                      a[x-apple-data-detectors] {
                        color: inherit !important;
                        text-decoration: none !important;
                        font-size: inherit !important;
                        font-family: inherit !important;
                        font-weight: inherit !important;
                        line-height: inherit !important;
                      }
                    </style>
                  </head>
                  <body style="margin: 0; padding: 0;">
                    <table align="center" cellpadding="0" cellspacing="0" width="600px" style="border: 1px solid #cccccc; background-position:center top;background-image:url(http://kasu.com.mx/assets/images/Correo/fondo.jpg);">
                      <tr>
                        <td style="padding: 20px 0 30px 0">
                          <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:transparent;">
                            <tr>
                              <td align="center" style="padding: 2px 2px 15px 2px; background-repeat:no-repeat; color: #153643; font-family: Arial, sans-serif; font-size: 25px;">
                                <a href="https://kasu.com.mx" style="text-decoration: none; color: whitesmoke;">
                                  <img src="https://kasu.com.mx/assets/images/Correo/florredonda.png" alt="" width="100px" height="100px" style="display: block;" /></a>
                              </td>
                            </tr>
                            <tr>
                              <td bgcolor="#ffffff" align="justify" style="padding:30px 20px 30px 60px; color: #153643; font-family: Arial, sans-serif; font-size: 16px;">
                                <font style="vertical-align:inherit;"><b> '.$Cte.'</b></font>
                              </td>
                            </tr>
                            <tr>
                              <td bgcolor="#ffffff" align="justify" style="padding: 5px 25px 30px 25px; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
                                <p>Bienvenid@ al equipo <b>KASU</b>! Ya casi terminas tu registro, por favor proporciónanos los siguientes datos.</p>
                                <p align="justify" style="background-color:azure;">
                                  <ul>
                                    <li>Cuenta Clabe (es donde te depositaremos tus comisiones)</li>
                                    <li>INE (lo requerimos para verificar tus datos)</li>
                                    <li>Comprobante de Domicilio (lo requerimos para verificar tus datos)</li>
                                  </ul>
                                </p>
                                <p>Para finalizar da click en el siguiente botón, en donde ingresaras los datos anteriores y recuerda hacer una copia de estos documentos a nuestro correo registros@kasu.com.mx</p>
                              </td>
                            </tr>
                            <tr>
                              <td bgcolor="#ffffff" align="center" style="padding:30px;">
                                <span class="es-button-border" style="border-style:solid;border-color:#ee3a87;background:#ee3a87;border-width:20px;display:inline-block;border-radius:5px;width:auto;">
                                  <a href="https://kasu.com.mx/prospectos.php?Usr='.$DirUrl.'" class="es-button" target="_blank" style="mso-style-priority:100 !important;text-decoration:none;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial,sans-serif;font-size:25px;color:#FFFFFF;border-style:solid;border-color:#ee3a87;display:inline-block;background:#ee3a87;border-radius:5px;font-weight:normal;font-style:normal;line-height:30px;width:auto;text-align:center;">Terminar Registro</a>
                                </span>
                              </td>
                            </tr>
                            <tr>
                              <td bgcolor="#ffffff" align="center" style="padding:30px;">
                                <p>Nos emociona tenerte con nosotros.</p>
                              </td>
                            </tr>
                            <tr>
                              <td style="padding: 15px 30px 5px 30px;">
                                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:transparent;">
                                  <tr>
                                    <td align="center" width="100%" style="font-family: Arial, sans-serif; font-size: 14px; padding: 20px 10px 0 10px">
                                      <table border="0" cellpadding="0" cellspacing="0">
                                        <tr>
                                          <td>
                                            <a href="https://www.facebook.com/KasuMexico">
                                              <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/facebook-circle-colored.png" alt="Fb" title="Facebook" width="32" height="32" style="display:block; border:0; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic;" />
                                            </a>
                                          </td>
                                          <td style=" font-size: 0; line-height: 0; width: 20px">&nbsp;</td>
                                          <td>
                                            <a href="https://twitter.com/KASSU_11">
                                              <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/twitter-circle-colored.png" alt="Tw" title="Twitter" width="32" height="32" style="display:block; border:0; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic;" />
                                            </a>
                                          </td>
                                          <td style=" font-size: 0; line-height: 0; width: 20px">&nbsp;</td>
                                          <td>
                                            <a href="https://www.instagram.com/kasumexico">
                                              <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/instagram-circle-colored.png" alt="Ig" title="Instagram" width="32" height="32" style="display:block; border:0; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic;" />
                                            </a>
                                          </td>
                                        </tr>
                                      </table>
                                    </td>
                                  <tr>
                                    <td align="center" width="100%" style="font-family: Arial, sans-serif; font-size: 14px; padding: 10px 10px 0 10px">
                                      <p style="text-align: center !important;">
                                        <font style="vertical-align:inherit;">
                                          © 2019&nbsp;Kasu Servicios a futuro&nbsp;| <br> Bosque de Chapultepec, Pedregal 24, Molino del Rey <br> Ciudad de México, CDMX, Mexico C.P. 11000 <br>
                                        </font>
                                        <a href="https://kasu.com.mx/index.php?Ml=4&Id='.$Id.'" style="color: #153643;">
                                          <font>Ya no quiero recibir este correo</font>
                                        </a>
                                      </p>
                                    </td>
                                  </tr>
                                </table>
                              </td>
                            </tr>
                          </table>
                        </td>
                      </tr>
                    </table>
                  </body>
                  </html>';
              }
              elseif($Asunto == 'RESTABLECIMIENTO DE CONTRASEÑA'){
                  $message = $RecoveryPassword='<!DOCTYPE html>
                  <html lang="ES" xmlns="http://www.w3.org/1999/xhtml">
                  <head>
                    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
                    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
                    <title>KASU</title>
                    <style type="text/css">
                      @media only screen and (max-width:600px) {
                        ul li,
                        ol li, p,
                         {
                          font-size: 16px !important;
                          line-height: 150% !important;
                          text-align: Justify !important;
                        }
                        h1 {
                          font-size: 18px !important;
                          text-align: center;
                          line-height: 120% !important
                        }
                        h1 a {
                          font-size: 15px !important
                        }
                        *[class="gmail-fix"] {
                          display: none !important
                        }
                        a.es-button {
                          font-size: 25px !important;
                          color: whitesmoke !important;
                          display: block !important;
                          text-decoration: none;
                          text-align: center !important;
                          border-left-width: 0px !important;
                          border-right-width: 0px !important
                        }
                        table.es-table-not-adapt,
                        .esd-block-html table {
                          width: auto !important
                        }
                      }
                      #outlook a {
                        padding: 0;
                      }
                      a[x-apple-data-detectors] {
                        color: inherit !important;
                        text-decoration: none !important;
                        font-size: inherit !important;
                        font-family: inherit !important;
                        font-weight: inherit !important;
                        line-height: inherit !important;
                      }
                    </style>
                  </head>
                  <body style="margin: 0; padding: 0;">
                    <table align="center" cellpadding="0" cellspacing="0" width="600px" style="border: 1px solid #cccccc; background-position:center top;background-image:url(http://kasu.com.mx/assets/images/Correo/fondo.jpg);">
                      <tr>
                        <td style="padding: 20px 0 30px 0">
                          <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:transparent;">
                            <tr>
                              <td align="center" style="padding: 2px 2px 15px 2px; background-repeat:no-repeat; color: #153643; font-family: Arial, sans-serif; font-size: 25px;">
                                <a href="https://kasu.com.mx" style="text-decoration: none; color: whitesmoke;">
                                  <img src="https://kasu.com.mx/assets/images/Correo/florredonda.png" alt="" width="100px" height="100px" style="display: block;" /></a>
                              </td>
                            </tr>
                            <tr>
                              <td bgcolor="#ffffff" align="justify" style="padding:20px 15px 5px 25px; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height:25px;">
                                <b>¡ '.$Cte.' !</b><br/>
                                  <p>Hemos recibido una petición para reestablecer la contraseña de tu cuenta de Kasu Servicios a futuro <br />Si hiciste esta peticion, haz clic en el siguiente enlace.</p>
                              </td>
                            </tr>
                            <tr>
                              <td bgcolor="#ffffff" align="center" style="padding:30px;">
                                <span class="es-button-border" style="border-style:solid;border-color:#ee3a87;background:#ee3a87;border-width:20px;display:inline-block;border-radius:5px;width:auto;">
                                  <a href="'.$DirUrl.'" class="es-button" target="_blank" style="mso-style-priority:100 !important;text-decoration:none;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial,sans-serif;font-size:30px;color:#FFFFFF;border-style:solid;border-color:#ee3a87;display:inline-block;background:#ee3a87;border-radius:5px;font-weight:normal;font-style:normal;line-height:35px;width:auto;text-align:center;">Cambiar Contraseña</a>
                                </span>
                              </td>
                            </tr>
                            <tr>
                              <td bgcolor="#ffffff" align="justify" style="padding: 5px 25px 30px 25px; color: #153643; font-family: Arial, sans-serif; font-size: 12px; line-height: 15px;">
                                <p align="justify" style="font-size: 12px">Si no hiciste esta peticion puedes ignorar este correo.<br/>Saludos <br />The Kasu Team</p>
                              </td>
                            </tr>
                            <tr>
                              <td style="padding: 15px 30px 5px 30px;">
                                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:transparent;">
                                  <tr>
                                    <td align="center" width="100%">
                                      <table border="0" cellpadding="0" cellspacing="0">
                                        <tr>
                                          <td>
                                            <a href="https://www.facebook.com/KasuMexico">
                                              <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/facebook-circle-colored.png" alt="Fb" title="Facebook" width="32" height="32" style="display:block; border:0; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic;" />
                                            </a>
                                          </td>
                                          <td style=" font-size: 0; line-height: 0; width: 20px">&nbsp;</td>
                                          <td>
                                            <a href="https://twitter.com/KASSU_11">
                                              <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/twitter-circle-colored.png" alt="Tw" title="Twitter" width="32" height="32" style="display:block; border:0; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic;" />
                                            </a>
                                          </td>
                                          <td style=" font-size: 0; line-height: 0; width: 20px">&nbsp;</td>
                                          <td>
                                            <a href="https://www.instagram.com/kasumexico">
                                              <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/instagram-circle-colored.png" alt="Ig" title="Instagram" width="32" height="32" style="display:block; border:0; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic;" />
                                            </a>
                                          </td>
                                        </tr>
                                      </table>
                                    </td>
                                  <tr>
                                    <td align="center" width="100%" style="font-family: Arial, sans-serif; font-size: 14px; padding: 0 10px 0 10px">
                                      <p style="text-align: center !important;">
                                        <font style="vertical-align:inherit;">
                                          © 2019&nbsp;Kasu Servicios a futuro&nbsp;| <br> Bosque de Chapultepec, Pedregal 24, Molino del Rey <br> Ciudad de México, CDMX, Mexico C.P. 11000 <br>
                                        </font>
                                        <a href="https://kasu.com.mx/index.php" style="color: #153643;">
                                          <font>KASU</font>
                                        </a>
                                      </p>
                                    </td>
                                  </tr>
                                </table>
                              </td>
                            </tr>
                          </table>
                        </td>
                      </tr>
                    </table>
                  </body></html>';
              }
              elseif($Asunto == 'ALTA DE COLABORADOR'){
                  $message = $RecoveryPassword='<!DOCTYPE html>
                  <html lang="ES" xmlns="http://www.w3.org/1999/xhtml">
                  <head>
                    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
                    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
                    <title>KASU</title>
                    <style type="text/css">
                      @media only screen and (max-width:600px) {
                        ul li,
                        ol li, p,
                         {
                          font-size: 16px !important;
                          line-height: 150% !important;
                          text-align: Justify !important;
                        }
                        h1 {
                          font-size: 18px !important;
                          text-align: center;
                          line-height: 120% !important
                        }
                        h1 a {
                          font-size: 15px !important
                        }
                        *[class="gmail-fix"] {
                          display: none !important
                        }
                        a.es-button {
                          font-size: 25px !important;
                          color: whitesmoke !important;
                          display: block !important;
                          text-decoration: none;
                          text-align: center !important;
                          border-left-width: 0px !important;
                          border-right-width: 0px !important
                        }
                        table.es-table-not-adapt,
                        .esd-block-html table {
                          width: auto !important
                        }
                      }
                      #outlook a {
                        padding: 0;
                      }
                      a[x-apple-data-detectors] {
                        color: inherit !important;
                        text-decoration: none !important;
                        font-size: inherit !important;
                        font-family: inherit !important;
                        font-weight: inherit !important;
                        line-height: inherit !important;
                      }
                    </style>
                  </head>
                  <body style="margin: 0; padding: 0;">
                    <table align="center" cellpadding="0" cellspacing="0" width="600px" style="border: 1px solid #cccccc; background-position:center top;background-image:url(http://kasu.com.mx/assets/images/Correo/fondo.jpg);">
                      <tr>
                        <td style="padding: 20px 0 30px 0">
                          <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:transparent;">
                            <tr>
                              <td align="center" style="padding: 2px 2px 15px 2px; background-repeat:no-repeat; color: #153643; font-family: Arial, sans-serif; font-size: 25px;">
                                <a href="https://kasu.com.mx" style="text-decoration: none; color: whitesmoke;">
                                  <img src="https://kasu.com.mx/assets/images/Correo/florredonda.png" alt="" width="100px" height="100px" style="display: block;" /></a>
                              </td>
                            </tr>
                            <tr>
                              <td bgcolor="#ffffff" align="justify" style="padding:20px 15px 5px 25px; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height:25px;">
                                <b>¡ '.$Cte.' !</b><br/>
                                  <p> KASU te ha asignado el siguiente usuario para ingresar a nuestro sistema de ventas </p>
                                  <p><strong>'.$DirUrl.'</strong></p>
                                  <p>Para terminar tu proceso de alta como colaborador debes crear una Contraseña</p>
                              </td>
                            </tr>
                            <tr>
                              <td bgcolor="#ffffff" align="center" style="padding:30px;">
                                <span class="es-button-border" style="border-style:solid;border-color:#ee3a87;background:#ee3a87;border-width:20px;display:inline-block;border-radius:5px;width:auto;">
                                  <a href="https://kasu.com.mx/login/index.php?data='.$dirUrl1.'&Usr='.$imag1.'" class="es-button" target="_blank" style="mso-style-priority:100 !important;text-decoration:none;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial,sans-serif;font-size:30px;color:#FFFFFF;border-style:solid;border-color:#ee3a87;display:inline-block;background:#ee3a87;border-radius:5px;font-weight:normal;font-style:normal;line-height:35px;width:auto;text-align:center;">Crear Contraseña</a>
                                </span>
                              </td>
                            </tr>
                            <tr>
                              <td bgcolor="#ffffff" align="justify" style="padding: 5px 25px 30px 25px; color: #153643; font-family: Arial, sans-serif; font-size: 12px; line-height: 15px;">
                                <p align="justify" style="font-size: 12px">Recuerda guardar tu Usuario y Contraseña en un lugar seguro.<br/>Saludos <br />The Kasu Team</p>
                              </td>
                            </tr>
                            <tr>
                              <td style="padding: 15px 30px 5px 30px;">
                                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:transparent;">
                                  <tr>
                                    <td align="center" width="100%">
                                      <table border="0" cellpadding="0" cellspacing="0">
                                        <tr>
                                          <td>
                                            <a href="https://www.facebook.com/KasuMexico">
                                              <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/facebook-circle-colored.png" alt="Fb" title="Facebook" width="32" height="32" style="display:block; border:0; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic;" />
                                            </a>
                                          </td>
                                          <td style=" font-size: 0; line-height: 0; width: 20px">&nbsp;</td>
                                          <td>
                                            <a href="https://twitter.com/KASSU_11">
                                              <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/twitter-circle-colored.png" alt="Tw" title="Twitter" width="32" height="32" style="display:block; border:0; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic;" />
                                            </a>
                                          </td>
                                          <td style=" font-size: 0; line-height: 0; width: 20px">&nbsp;</td>
                                          <td>
                                            <a href="https://www.instagram.com/kasumexico">
                                              <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/instagram-circle-colored.png" alt="Ig" title="Instagram" width="32" height="32" style="display:block; border:0; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic;" />
                                            </a>
                                          </td>
                                        </tr>
                                      </table>
                                    </td>
                                  <tr>
                                    <td align="center" width="100%" style="font-family: Arial, sans-serif; font-size: 14px; padding: 0 10px 0 10px">
                                      <p style="text-align: center !important;">
                                        <font style="vertical-align:inherit;">
                                          © 2019&nbsp;Kasu Servicios a futuro&nbsp;| <br> Bosque de Chapultepec, Pedregal 24, Molino del Rey <br> Ciudad de México, CDMX, Mexico C.P. 11000 <br>
                                        </font>
                                        <a href="https://kasu.com.mx/index.php" style="color: #153643;">
                                          <font>KASU</font>
                                        </a>
                                      </p>
                                    </td>
                                  </tr>
                                </table>
                              </td>
                            </tr>
                          </table>
                        </td>
                      </tr>
                    </table>
                  </body></html>';
              }
              elseif($Asunto == 'ALTA DISTRIBUIDOR'){
                  $message = $RecoveryPassword='<!DOCTYPE html>
                  <html lang="ES" xmlns="http://www.w3.org/1999/xhtml">
                  <head>
                    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
                    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
                    <title>KASU</title>
                    <style type="text/css">
                      @media only screen and (max-width:600px) {
                        ul li,
                        ol li, p,
                         {
                          font-size: 16px !important;
                          line-height: 150% !important;
                          text-align: Justify !important;
                        }
                        h1 {
                          font-size: 18px !important;
                          text-align: center;
                          line-height: 120% !important
                        }
                        h1 a {
                          font-size: 15px !important
                        }
                        *[class="gmail-fix"] {
                          display: none !important
                        }
                        a.es-button {
                          font-size: 25px !important;
                          color: whitesmoke !important;
                          display: block !important;
                          text-decoration: none;
                          text-align: center !important;
                          border-left-width: 0px !important;
                          border-right-width: 0px !important
                        }
                        table.es-table-not-adapt,
                        .esd-block-html table {
                          width: auto !important
                        }
                      }
                      #outlook a {
                        padding: 0;
                      }
                      a[x-apple-data-detectors] {
                        color: inherit !important;
                        text-decoration: none !important;
                        font-size: inherit !important;
                        font-family: inherit !important;
                        font-weight: inherit !important;
                        line-height: inherit !important;
                      }
                    </style>
                  </head>
                  <body style="margin: 0; padding: 0;">
                    <table align="center" cellpadding="0" cellspacing="0" width="600px" style="border: 1px solid #cccccc; background-position:center top;background-image:url(http://kasu.com.mx/assets/images/Correo/fondo.jpg);">
                      <tr>
                        <td style="padding: 20px 0 30px 0">
                          <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:transparent;">
                            <tr>
                              <td align="center" style="padding: 2px 2px 15px 2px; background-repeat:no-repeat; color: #153643; font-family: Arial, sans-serif; font-size: 25px;">
                                <a href="https://kasu.com.mx" style="text-decoration: none; color: whitesmoke;">
                                  <img src="https://kasu.com.mx/assets/images/Correo/florredonda.png" alt="" width="100px" height="100px" style="display: block;" /></a>
                              </td>
                            </tr>
                            <tr>
                              <td bgcolor="#ffffff" align="justify" style="padding:20px 15px 5px 25px; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height:25px;">
                                <b>¡ '.$Cte.' !</b><br/>
                                  <p> KASU te ha asignado el siguiente usuario para ingresar a nuestro sistema de ventas </p>
                                  <p><strong>'.$DirUrl.'</strong></p>
                                  <p>Para terminar tu proceso de alta como colaborador debes crear una Contraseña</p>
                              </td>
                            </tr>
                            <tr>
                              <td bgcolor="#ffffff" align="center" style="padding:30px;">
                                <span class="es-button-border" style="border-style:solid;border-color:#ee3a87;background:#ee3a87;border-width:20px;display:inline-block;border-radius:5px;width:auto;">
                                  <a href=https://kasu.com.mx/login/Generar_PDF/Contrato_Ejecutivo_pdf.php?Add='.$Titulo1.'" class="es-button" target="_blank" style="mso-style-priority:100 !important;text-decoration:none;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial,sans-serif;font-size:30px;color:#FFFFFF;border-style:solid;border-color:#ee3a87;display:inline-block;background:#ee3a87;border-radius:5px;font-weight:normal;font-style:normal;line-height:35px;width:auto;text-align:center;">Descargar contrato</a>
                                </span>
                              </td>
                            </tr>
                            <tr>
                              <td bgcolor="#ffffff" align="center" style="padding:30px;">
                                <span class="es-button-border" style="border-style:solid;border-color:#ee3a87;background:#ee3a87;border-width:20px;display:inline-block;border-radius:5px;width:auto;">
                                  <a href="https://kasu.com.mx/premio/Manual-KASU-EXTERNO.pdf" class="es-button" target="_blank" style="mso-style-priority:100 !important;text-decoration:none;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial,sans-serif;font-size:30px;color:#FFFFFF;border-style:solid;border-color:#ee3a87;display:inline-block;background:#ee3a87;border-radius:5px;font-weight:normal;font-style:normal;line-height:35px;width:auto;text-align:center;">Descargar GUIA DE USO</a>
                                </span>
                              </td>
                            </tr>
                            <tr>
                              <td bgcolor="#ffffff" align="center" style="padding:30px;">
                                <span class="es-button-border" style="border-style:solid;border-color:#ee3a87;background:#ee3a87;border-width:20px;display:inline-block;border-radius:5px;width:auto;">
                                  <a href="https://kasu.com.mx/login/index.php?data='.$dirUrl1.'&Usr='.$imag1.'" class="es-button" target="_blank" style="mso-style-priority:100 !important;text-decoration:none;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial,sans-serif;font-size:30px;color:#FFFFFF;border-style:solid;border-color:#ee3a87;display:inline-block;background:#ee3a87;border-radius:5px;font-weight:normal;font-style:normal;line-height:35px;width:auto;text-align:center;">Crear Contraseña</a>
                                </span>
                              </td>
                            </tr>
                            <tr>
                              <td bgcolor="#ffffff" align="justify" style="padding: 5px 25px 30px 25px; color: #153643; font-family: Arial, sans-serif; font-size: 12px; line-height: 15px;">
                                <p align="justify" style="font-size: 12px">Recuerda guardar tu Usuario y Contraseña en un lugar seguro.<br/>Saludos <br />The Kasu Team</p>
                              </td>
                            </tr>
                            <tr>
                              <td style="padding: 15px 30px 5px 30px;">
                                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:transparent;">
                                  <tr>
                                    <td align="center" width="100%">
                                      <table border="0" cellpadding="0" cellspacing="0">
                                        <tr>
                                          <td>
                                            <a href="https://www.facebook.com/KasuMexico">
                                              <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/facebook-circle-colored.png" alt="Fb" title="Facebook" width="32" height="32" style="display:block; border:0; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic;" />
                                            </a>
                                          </td>
                                          <td style=" font-size: 0; line-height: 0; width: 20px">&nbsp;</td>
                                          <td>
                                            <a href="https://twitter.com/KASSU_11">
                                              <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/twitter-circle-colored.png" alt="Tw" title="Twitter" width="32" height="32" style="display:block; border:0; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic;" />
                                            </a>
                                          </td>
                                          <td style=" font-size: 0; line-height: 0; width: 20px">&nbsp;</td>
                                          <td>
                                            <a href="https://www.instagram.com/kasumexico">
                                              <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/instagram-circle-colored.png" alt="Ig" title="Instagram" width="32" height="32" style="display:block; border:0; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic;" />
                                            </a>
                                          </td>
                                        </tr>
                                      </table>
                                    </td>
                                  <tr>
                                    <td align="center" width="100%" style="font-family: Arial, sans-serif; font-size: 14px; padding: 0 10px 0 10px">
                                      <p style="text-align: center !important;">
                                        <font style="vertical-align:inherit;">
                                          © 2019&nbsp;Kasu Servicios a futuro&nbsp;| <br> Bosque de Chapultepec, Pedregal 24, Molino del Rey <br> Ciudad de México, CDMX, Mexico C.P. 11000 <br>
                                        </font>
                                        <a href="https://kasu.com.mx/index.php" style="color: #153643;">
                                          <font>KASU</font>
                                        </a>
                                      </p>
                                    </td>
                                  </tr>
                                </table>
                              </td>
                            </tr>
                          </table>
                        </td>
                      </tr>
                    </table>
                  </body></html>';
              }
              elseif($Asunto == 'ENVIO ARCHIVO'){
                  $message = $RecoveryPassword='<!DOCTYPE html>
                  <html lang="ES" xmlns="http://www.w3.org/1999/xhtml">
                  <head>
                    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
                    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
                    <title>KASU</title>
                    <style type="text/css">
                      @media only screen and (max-width:600px) {
                        ul li,
                        ol li, p,
                         {
                          font-size: 16px !important;
                          line-height: 150% !important;
                          text-align: Justify !important;
                        }
                        h1 {
                          font-size: 18px !important;
                          text-align: center;
                          line-height: 120% !important
                        }
                        h1 a {
                          font-size: 15px !important
                        }
                        *[class="gmail-fix"] {
                          display: none !important
                        }
                        a.es-button {
                          font-size: 25px !important;
                          color: whitesmoke !important;
                          display: block !important;
                          text-decoration: none;
                          text-align: center !important;
                          border-left-width: 0px !important;
                          border-right-width: 0px !important
                        }
                        table.es-table-not-adapt,
                        .esd-block-html table {
                          width: auto !important
                        }
                      }
                      #outlook a {
                        padding: 0;
                      }
                      a[x-apple-data-detectors] {
                        color: inherit !important;
                        text-decoration: none !important;
                        font-size: inherit !important;
                        font-family: inherit !important;
                        font-weight: inherit !important;
                        line-height: inherit !important;
                      }
                    </style>
                  </head>
                  <body style="margin: 0; padding: 0;">
                    <table align="center" cellpadding="0" cellspacing="0" width="600px" style="border: 1px solid #cccccc; background-position:center top;background-image:url(http://kasu.com.mx/assets/images/Correo/fondo.jpg);">
                      <tr>
                        <td style="padding: 20px 0 30px 0">
                          <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:transparent;">
                            <tr>
                              <td align="center" style="padding: 2px 2px 15px 2px; background-repeat:no-repeat; color: #153643; font-family: Arial, sans-serif; font-size: 25px;">
                                <a href="https://kasu.com.mx" style="text-decoration: none; color: whitesmoke;">
                                  <img src="https://kasu.com.mx/assets/images/Correo/florredonda.png" alt="" width="100px" height="100px" style="display: block;" /></a>
                              </td>
                            </tr>
                            <tr>
                              <td bgcolor="#ffffff" align="justify" style="padding:20px 15px 5px 25px; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height:25px;">
                                <b>¡ '.$Cte.' !</b><br/>
                                  <p> KASU te ha enviado tu </p>
                                  <p><strong>'.$DirUrl.'</strong></p>
                                  <p>Solo da click en el siguiente boton para descargarlo</p>
                              </td>
                            </tr>
                            <tr>
                              <td bgcolor="#ffffff" align="center" style="padding:30px;">
                                <span class="es-button-border" style="border-style:solid;border-color:#ee3a87;background:#ee3a87;border-width:20px;display:inline-block;border-radius:5px;width:auto;">
                                  <a href="'.$dirUrl1.'?busqueda='.$imag1.'" class="es-button" target="_blank" style="mso-style-priority:100 !important;text-decoration:none;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial,sans-serif;font-size:30px;color:#FFFFFF;border-style:solid;border-color:#ee3a87;display:inline-block;background:#ee3a87;border-radius:5px;font-weight:normal;font-style:normal;line-height:35px;width:auto;text-align:center;">Descargar</a>
                                </span>
                              </td>
                            </tr>
                            <tr>
                              <td bgcolor="#ffffff" align="justify" style="padding: 5px 25px 30px 25px; color: #153643; font-family: Arial, sans-serif; font-size: 12px; line-height: 15px;">
                                <p align="justify" style="font-size: 12px">Recuerda guardar tu Usuario y Contraseña en un lugar seguro.<br/>Saludos <br />The Kasu Team</p>
                              </td>
                            </tr>
                            <tr>
                              <td style="padding: 15px 30px 5px 30px;">
                                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:transparent;">
                                  <tr>
                                    <td align="center" width="100%">
                                      <table border="0" cellpadding="0" cellspacing="0">
                                        <tr>
                                          <td>
                                            <a href="https://www.facebook.com/KasuMexico">
                                              <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/facebook-circle-colored.png" alt="Fb" title="Facebook" width="32" height="32" style="display:block; border:0; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic;" />
                                            </a>
                                          </td>
                                          <td style=" font-size: 0; line-height: 0; width: 20px">&nbsp;</td>
                                          <td>
                                            <a href="https://twitter.com/KASSU_11">
                                              <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/twitter-circle-colored.png" alt="Tw" title="Twitter" width="32" height="32" style="display:block; border:0; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic;" />
                                            </a>
                                          </td>
                                          <td style=" font-size: 0; line-height: 0; width: 20px">&nbsp;</td>
                                          <td>
                                            <a href="https://www.instagram.com/kasumexico">
                                              <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/instagram-circle-colored.png" alt="Ig" title="Instagram" width="32" height="32" style="display:block; border:0; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic;" />
                                            </a>
                                          </td>
                                        </tr>
                                      </table>
                                    </td>
                                  <tr>
                                    <td align="center" width="100%" style="font-family: Arial, sans-serif; font-size: 14px; padding: 0 10px 0 10px">
                                      <p style="text-align: center !important;">
                                        <font style="vertical-align:inherit;">
                                          © 2019&nbsp;Kasu Servicios a futuro&nbsp;| <br> Bosque de Chapultepec, Pedregal 24, Molino del Rey <br> Ciudad de México, CDMX, Mexico C.P. 11000 <br>
                                        </font>
                                        <a href="https://kasu.com.mx/index.php" style="color: #153643;">
                                          <font>KASU</font>
                                        </a>
                                      </p>
                                    </td>
                                  </tr>
                                </table>
                              </td>
                            </tr>
                          </table>
                        </td>
                      </tr>
                    </table>
                  </body></html>';
              }
              elseif($Asunto == 'ACTUALIZACION DE DATOS'){
                  $message = $Password='<!DOCTYPE html>
                  <html lang="ES" xmlns="http://www.w3.org/1999/xhtml">
                  <head>
                    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
                    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
                    <title>KASU</title>
                    <style type="text/css">
                      @media only screen and (max-width:600px) {
                        ul li,
                        ol li, p,
                         {
                          font-size: 16px !important;
                          line-height: 150% !important;
                          text-align: Justify !important;
                        }
                        h1 {
                          font-size: 18px !important;
                          text-align: Justify;
                          line-height: 120% !important
                        }
                        h1 a {
                          font-size: 18px !important
                        }
                        *[class="gmail-fix"] {
                          display: none !important
                        }
                        a.es-button {
                          font-size: 35px !important;
                          color: whitesmoke !important;
                          display: block !important;
                          text-decoration: none;
                          text-align: center !important;
                          border-left-width: 0px !important;
                          border-right-width: 0px !important
                        }
                        table.es-table-not-adapt,
                        .esd-block-html table {
                          width: auto !important
                        }
                      }
                      #outlook a {
                        padding: 0;
                      }
                      a[x-apple-data-detectors] {
                        color: inherit !important;
                        text-decoration: none !important;
                        font-size: inherit !important;
                        font-family: inherit !important;
                        font-weight: inherit !important;
                        line-height: inherit !important;
                      }
                    </style>
                  </head>
                  <body style="margin: 0; padding: 0;">
                    <table align="center" cellpadding="0" cellspacing="0" width="600px" style="border: 1px solid #cccccc; background-position:center top;background-image:url(http://kasu.com.mx/assets/images/Correo/fondo.jpg);">
                      <tr>
                        <td style="padding: 20px 0 30px 0">
                          <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:transparent;">
                            <tr>
                              <td align="center" style="padding: 2px 2px 15px 2px; background-repeat:no-repeat; color: #153643; font-family: Arial, sans-serif; font-size: 25px;">
                                <a href="https://kasu.com.mx" style="text-decoration: none; color: whitesmoke;">
                                  <img src="https://kasu.com.mx/assets/images/Correo/florredonda.png" alt="" width="100px" height="100px" style="display: block;" /></a>
                              </td>
                            </tr>
                            <tr>
                              <td bgcolor="#ffffff" align="justify" style="padding:20px 15px 5px 25px; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 25px;">
                                <b>¡'.$Cte.'!</b><br/>
                                  <p>Algunos datos de tu cuenta de KASU han sido actualizados reccientemente. <br/> Haz clic en el siguiente enlace para verificarlos</p>
                              </td>
                            </tr>
                            <tr>
                              <td bgcolor="#ffffff" align="center" style="padding:30px;">
                                <span class="es-button-border" style="border-style:solid;border-color:#ee3a87;background:#ee3a87;border-width:20px;display:inline-block;border-radius:5px;width:auto;">
                                  <a href="'.$DirUrl.'?value='.$dirUrl1.'" class="es-button" target="_blank" style="mso-style-priority:100 !important;text-decoration:none;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial,sans-serif;font-size:30px;color:#FFFFFF;border-style:solid;border-color:#ee3a87;display:inline-block;background:#ee3a87;border-radius:5px;font-weight:normal;font-style:normal;line-height:35px;width:auto;text-align:center;">Verificar</a>
                                </span>
                              </td>
                            </tr>
                            <tr>
                              <td bgcolor="#ffffff" align="justify" style="padding: 5px 25px 30px 25px; color: #153643; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px;">
                                <p align="justify" >¿No reconoces esta actividad? <br/> Haz clic <a href="https://kasu.com.mx">aqui</a> para obtener mas informacion sobre como cancelar esta modificacion. <br /> Atencion al Cliente</p>

                              </td>
                            </tr>
                            <tr>
                              <td style="padding: 15px 30px 5px 30px;">
                                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:transparent;">
                                  <tr>
                                    <td align="center" width="100%">
                                      <table border="0" cellpadding="0" cellspacing="0">
                                        <tr>
                                          <td>
                                            <a href="https://www.facebook.com/KasuMexico">
                                              <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/facebook-circle-colored.png" alt="Fb" title="Facebook" width="32" height="32" style="display:block; border:0; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic;" />
                                            </a>
                                          </td>
                                          <td style=" font-size: 0; line-height: 0; width: 20px">&nbsp;</td>
                                          <td>
                                            <a href="https://twitter.com/KASSU_11">
                                              <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/twitter-circle-colored.png" alt="Tw" title="Twitter" width="32" height="32" style="display:block; border:0; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic;" />
                                            </a>
                                          </td>
                                          <td style=" font-size: 0; line-height: 0; width: 20px">&nbsp;</td>
                                          <td>
                                            <a href="https://www.instagram.com/kasumexico">
                                              <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/instagram-circle-colored.png" alt="Ig" title="Instagram" width="32" height="32" style="display:block; border:0; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic;" />
                                            </a>
                                          </td>
                                        </tr>
                                      </table>
                                    </td>
                                  <tr>
                                    <td align="center" width="100%" style="font-family: Arial, sans-serif; font-size: 14px; padding: 0 10px 0 10px">
                                      <p style="text-align: center !important;">
                                        <font style="vertical-align:inherit;">
                                          © 2019&nbsp;Kasu Servicios a futuro&nbsp;| <br> Bosque de Chapultepec, Pedregal 24, Molino del Rey <br> Ciudad de México, CDMX, Mexico C.P. 11000 <br>
                                        </font>
                                        <a href="https://kasu.com.mx/index.php" style="color: #153643;">
                                          <font>KASU</font>
                                        </a>
                                      </p>
                                    </td>
                                  </tr>
                                </table>
                              </td>
                            </tr>
                          </table>
                        </td>
                      </tr>
                    </table>
                  </body>
                  </html>';
                }
              elseif($Asunto == 'Codigo de verificacion'){
                  $message = $CodVer='<!DOCTYPE html>
                  <html lang="ES" xmlns="http://www.w3.org/1999/xhtml">
                  <head>
                  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
                  <meta name="viewport" content="width=device-width, initial-scale=1.0">
                  <title>KASU</title>
                  <style type="text/css">
                  @media only screen and (max-width:600px) {
                  ul li, ol li, p,
                      {
                      font-size: 18px !important;
                      line-height: 150% !important;
                      text-align: Justify !important;
                      }
                    h1 {
                      font-size: 20px !important;
                      text-align: Justify;
                      line-height: 120% !important
                    }
                    h1 a {
                      font-size: 18px !important
                    }
                    *[class="gmail-fix"] {
                      display: none !important
                    }
                        a.es-button {
                          font-size: 35px !important;
                          color: whitesmoke !important;
                          display: block !important;
                          text-decoration: none;
                          text-align: center !important;
                          border-left-width: 0px !important;
                          border-right-width: 0px !important
                        }
                        table.es-table-not-adapt,
                        .esd-block-html table {
                          width: auto !important
                        }
                      }
                      #outlook a {
                        padding: 0;
                      }
                      a[x-apple-data-detectors] {
                        color: inherit !important;
                        text-decoration: none !important;
                        font-size: inherit !important;
                        font-family: inherit !important;
                        font-weight: inherit !important;
                        line-height: inherit !important;
                      }
                    </style>
                  </head>
                  <body style="margin: 0; padding: 0;">
                    <table align="center" cellpadding="0" cellspacing="0" width="600px" style="border: 1px solid #cccccc; background-position:center top;background-image:url(http://kasu.com.mx/assets/images/Correo/fondo.jpg);">
                      <tr>
                        <td style="padding: 20px 0 30px 0">
                          <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:transparent;">
                            <tr>
                              <td align="center" style="padding: 2px 2px 15px 2px; background-repeat:no-repeat; color: #153643; font-family: Arial, sans-serif; font-size: 25px;">
                                <a href="https://kasu.com.mx" style="text-decoration: none; color: whitesmoke;">
                                  <img src="https://kasu.com.mx/assets/images/Correo/florredonda.png" alt="" width="100px" height="100px" style="display: block;" /></a>
                              </td>
                            </tr>
                            <tr>
                              <td bgcolor="#ffffff" align="justify" style="padding:20px 15px 5px 25px; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 25px;">
                                  <b style="vertical-align:inherit;"> '.$Cte.'<br></b>
                              </td>
                            </tr>
                            <tr style="border-collapse:collapse;">
                                <td align="left" bgcolor="#ffffff" style="padding:10px 30px 5px 30px; background-repeat:no-repeat;">
                                  <p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:16px;font-family:arial, sans-serif;line-height:24px;color:#666666;">Ingresa el
                                  siguiente código de verificacion para actualizar tus datos</p><br>
                                    <b style="vertical-align:inherit;"><p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:16px;font-family:arial, sans-serif;line-height:24px;color:#666666;text-align: center;">'.$DirUrl.'</p></b>
                                </td>
                              </tr>
                            <tr>
                              <td bgcolor="#ffffff" align="justify" style="padding: 5px 25px 30px 25px; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
                                <p align="justify" style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:12px;font-family:arial, sans-serif;line-height:24px;color:#666666;"><br/>Saludos <br />The Kasu Team</p>
                              </td>
                            </tr>
                            <tr>
                              <td style="padding: 15px 30px 5px 30px;">
                                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:transparent;">
                                  <tr>
                                    <td align="center" width="100%">
                                      <table border="0" cellpadding="0" cellspacing="0">
                                        <tr>
                                          <td>
                                            <a href="https://www.facebook.com/KasuMexico">
                                              <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/facebook-circle-colored.png" alt="Fb" title="Facebook" width="32" height="32" style="display:block; border:0; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic;" />
                                            </a>
                                          </td>
                                          <td style=" font-size: 0; line-height: 0; width: 20px">&nbsp;</td>
                                          <td>
                                            <a href="https://twitter.com/KASSU_11">
                                              <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/twitter-circle-colored.png" alt="Tw" title="Twitter" width="32" height="32" style="display:block; border:0; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic;" />
                                            </a>
                                          </td>
                                          <td style=" font-size: 0; line-height: 0; width: 20px">&nbsp;</td>
                                          <td>
                                            <a href="https://www.instagram.com/kasumexico">
                                              <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/instagram-circle-colored.png" alt="Ig" title="Instagram" width="32" height="32" style="display:block; border:0; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic;" />
                                            </a>
                                          </td>
                                        </tr>
                                      </table>
                                    </td>
                                  <tr>
                                    <td align="center" width="100%" style="font-family: Arial, sans-serif; font-size: 14px; padding: 0 10px 0 10px">
                                      <p style="text-align: center !important;">
                                        <font style="vertical-align:inherit;">
                                          © 2019&nbsp;Kasu Servicios a futuro&nbsp;| <br> Bosque de Chapultepec, Pedregal 24, Molino del Rey <br> Ciudad de México, CDMX, Mexico C.P. 11000 <br>
                                        </font>
                                        <a href="https://kasu.com.mx/index.php" style="color: #153643;">
                                          <font>KASU</font>
                                        </a>
                                      </p>
                                    </td>
                                  </tr>
                                </table>
                              </td>
                            </tr>
                          </table>
                        </td>
                      </tr>
                    </table>
                  </body>
                  </html>';
              }
              elseif($Asunto == 'CONOCE KASU'){
                $message = $Press='<!DOCTYPE html>
                <html lang="ES" xmlns="http://www.w3.org/1999/xhtml">
                	<head>
                    	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
                    	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
                    	<title>KASU</title>
                    	<style type="text/css">
                    		@media only screen and (max-width:600px) {
                    			ul li,
                    			ol li,
                    			p	{
                    				font-size: 18px !important;
                    				line-height: 150% !important;
                    				text-align: Justify !important;
                    			}
                    			h1 {
                    				font-size: 20px !important;
                    				text-align: Justify;
                    				line-height: 120% !important
                    			}
                    			h1 a {
                    				font-size: 18px !important
                    			}
                    			*[class="gmail-fix"] {
                    				display: none !important
                    			}
                    			a.es-button {
                    				font-size: 35px !important;
                    				color: whitesmoke !important;
                    				display: block !important;
                    				text-decoration: none;
                    				text-align: center !important;
                    				border-left-width: 0px !important;
                    				border-right-width: 0px !important
                    			}
                    			table.es-table-not-adapt,
                    			.esd-block-html table {
                    				width: auto !important
                    			}
                    		}

                    		#outlook a {
                    			padding: 0;
                    		}

                    		a[x-apple-data-detectors] {
                    			color: inherit !important;
                    			text-decoration: none !important;
                    			font-size: inherit !important;
                    			font-family: inherit !important;
                    			font-weight: inherit !important;
                    			line-height: inherit !important;
                    		}
                    	</style>
                    </head>
                    <body style="margin: 0; padding: 0;">
                    	<table align="center" cellpadding="0" cellspacing="0" width="600px" style="border: 1px solid #cccccc; background-position:center top;background-image:url(http://kasu.com.mx/assets/images/Correo/fondo.jpg);">
                    		<tr>
                    			<td style="padding: 30px 0 30px 0">
                    				<table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:transparent;">
                    					<tr>
                    						<td align="center" style="padding: 2px 2px 15px 2px; background-repeat:no-repeat; color: #153643; font-family: Arial, sans-serif; font-size: 25px;">
                    							<a href="https://kasu.com.mx" style="text-decoration: none; color: white;">
                    								<img src="https://kasu.com.mx/assets/images/Correo/florredonda.png" alt="Logo" width="60px" height="60px" style="display: block;" /><br><b>'.$Cte.'<br>Te haz registrado en KASU</b></a>
                    						</td>
                    					</tr>
                    					<tr>
                    						<td bgcolor="#ffffff" align="justify" style="padding:20px 15px 5px 25px; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 25px;">
                    							<br><br>
                    							<p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:16px;font-family:arial, sans-serif;line-height:24px;color:#666666;">KASU es una empresa que ofrece Servicios a Futuro a bajo costo en México, los cuales se pagan una sola vez en la vida y no requiere renovación o pagos adicionales, lo cual es una característica única y diferenciadora en comparación con otros productos en el mercado.
                    							</p>
                    						</td>
                    					</tr>
                    					<tr>
                    						<td bgcolor="#ffffff" align="justify" style="padding:20px 15px 5px 25px; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 25px;">
                    							<p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:16px;font-family:arial, sans-serif;line-height:24px;color:#666666;">Estamos muy contentos que te hayas registrado en breve un ejecutivo te contactara, muestras eso pasa te dejamos un poco de informacion sobre KASU.</p>
                    						</td>
                    					</tr>
                    					<tr>
                    						<td bgcolor="#ffffff" align="justify" style="padding:0 15px 5px 25px; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 25px; text-align: right;">
                    							<a href="https://kasu.com.mx" style="padding:5px; text-shadow: 2px 2px  5px red; color:darkblue">Conoce la empresa</a>
                    						</td>
                    					</tr>
                    					<tr>
                    						<td bgcolor="#ffffff" align="center" style="padding:5px; font-family: Arial, sans-serif; font-size: 14px;">
                    							<a href="'.$DirUrl.'" style="padding:5px;">
                    								<img src="https://kasu.com.mx/assets/images/registro/correo_'.$imag1.'.jpg" alt="Consultar producto" width="300px" height="212px" style="display: block;" />
                    							</a>
                    						</td>
                    					</tr>
                    					<tr>
                    						<td bgcolor="#ffffff" align="justify" style="padding:20px 15px 5px 25px; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 25px;">
                    								<p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:16px;font-family:arial, sans-serif;line-height:24px;color:#666666;">
                    								De igual manera puedes agendar una llamada de uno de nuestros ejecutivos si no deseas que te marquen intempestuosamente.
                    								</p><br>
                    						</td>
                    					</tr>
                    					<tr>
                    						<td bgcolor="#ffffff" align="center" width="100%">
                    							<table border="0" cellpadding="0" cellspacing="0" width="100%">
                    								<tr>
                    									<td align="center" width="50%" style="padding:5px; font-family: Arial, sans-serif; font-size: 14px;">
                    										<a class="comprar" href="'.$dirUrl1.'">
                    											<img src="https://kasu.com.mx/assets/images/Correo/AGENDARCITA.png" alt="Agendar llamada" width="180px" height="50px" style="display: block;" />
                    										</a>
                    									</td>
                    								</tr>
                    							</table>
                    						</td>
                    					</tr>
                    					<tr>
                    						<td bgcolor="#ffffff" align="justify" style="padding: 5px 25px 30px 25px; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;"><br>
                    							<p align="justify" style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:14px;font-family:arial, sans-serif;line-height:24px;color:#666666;"><br/>Estamos contigo en todo momento.</p>
                    						</td>
                    					</tr>
                    					<tr>
                    						<td style="padding: 15px 30px 5px 30px;">
                    							<table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:transparent;">
                    								<tr>
                    									<td align="center" width="100%">
                    										<table border="0" cellpadding="0" cellspacing="0">
                    											<tr>
                    												<td>
                    													<a href="https://www.facebook.com/KasuMexico">
                    														<img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/facebook-circle-colored.png" alt="Fb" title="Facebook" width="32" height="32" style="display:block; border:0; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic;" />
                    													</a>
                    												</td>
                    												<td style=" font-size: 0; line-height: 0; width: 20px">&nbsp;</td>
                    												<td>
                    													<a href="https://twitter.com/KASSU_11">
                    														<img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/twitter-circle-colored.png" alt="Tw" title="Twitter" width="32" height="32" style="display:block; border:0; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic;" />
                    													</a>
                    												</td>
                    												<td style=" font-size: 0; line-height: 0; width: 20px">&nbsp;</td>
                    												<td>
                    													<a href="https://www.instagram.com/kasumexico">
                    														<img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/instagram-circle-colored.png" alt="Ig" title="Instagram" width="32" height="32" style="display:block; border:0; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic;" />
                    													</a>
                    												</td>
                    											</tr>
                    										</table>
                    									</td>
                    								<tr>
                    									<td align="center" width="100%" style="font-family: Arial, sans-serif; font-size: 14px; padding: 10px 10px 0 10px; color: aliceblue" >
                    										<p style="text-align: center !important;">
                    											<font style="vertical-align:inherit;">
                    												© 2021&nbsp;|&nbsp;Kasu Servicios a futuro&nbsp;<br>
                    												KASU desarrolla productos financieros, para solventar momentos importantes en tu vida y la de los tuyos.
                    											</font>
                                                              <a href="https://kasu.com.mx/index.php?Ml=4&Id='.$Id.'" style="color: #153643;">
                                                                <font>Ya no quiero recibir este correo</font>
                                                              </a>
                    										</p>
                    									</td>
                    								</tr>
                    							</table>
                    						</td>
                    					</tr>
                    				</table>
                    			</td>
                    		</tr>
                    	</table>
                    </body>
                </html>';
                }
              elseif($Asunto == '¿AUN TIENES DUDAS?'){
                  $message = $Duda='<!DOCTYPE html>
                  <html lang="ES" xmlns="http://www.w3.org/1999/xhtml">

                  <head>
                  	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
                  	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
                  	<title>KASU</title>
                  	<style type="text/css">
                  		@media only screen and (max-width:600px) {

                  			ul li,
                  			ol li,
                  			p,
                  				{
                  				font-size: 18px !important;
                  				line-height: 150% !important;
                  				text-align: Justify !important;
                  			}

                  			h1 {
                  				font-size: 20px !important;
                  				text-align: Justify;
                  				line-height: 120% !important
                  			}

                  			h1 a {
                  				font-size: 18px !important
                  			}

                  			*[class="gmail-fix"] {
                  				display: none !important
                  			}

                  			a.es-button {
                  				font-size: 35px !important;
                  				color: whitesmoke !important;
                  				display: block !important;
                  				text-decoration: none;
                  				text-align: center !important;
                  				border-left-width: 0px !important;
                  				border-right-width: 0px !important
                  			}

                  			table.es-table-not-adapt,
                  			.esd-block-html table {
                  				width: auto !important
                  			}
                  		}

                  		#outlook a {
                  			padding: 0;
                  		}

                  		a[x-apple-data-detectors] {
                  			color: inherit !important;
                  			text-decoration: none !important;
                  			font-size: inherit !important;
                  			font-family: inherit !important;
                  			font-weight: inherit !important;
                  			line-height: inherit !important;
                  		}

                  	</style>

                  </head>

                  <body style="margin: 0; padding: 0;">

                  	<table align="center" cellpadding="0" cellspacing="0" width="600px" style="border: 1px solid #cccccc; background-position:center center; background-image:url(http://kasu.com.mx/assets/images/Correo/fondo.jpg);">
                  		<tr>
                  			<td style="padding: 20px 0 30px 0">
                  				<table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:transparent;">
                  					<tr>
                  						<td align="center" style="padding: 30px 2px 30px 2px; background-repeat:no-repeat; color: #153643; font-family: Arial, sans-serif; font-size: 25px;">
                  							<a href="https://kasu.com.mx" style="text-decoration: none; color: whitesmoke;">
                  								<img src="https://kasu.com.mx/assets/images/Correo/florredonda.png" alt="" width="80px" height="80px" style="display: block;" /><br><b>'.$Cte.'<br> ¿aun tienes dudas?</b></a>
                  						</td>
                  					</tr>
                  					<tr>
                  						<td bgcolor="#ffffff" align="center" width="80%" style="padding:5px; font-family: Arial, sans-serif; font-size: 14px;">
                  							<div align="Justify" style="padding:20px 15px 5px 25px; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 25px;"><p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:16px;font-family:arial, sans-serif;line-height:24px;color:#666666;">¿Aún tienes dudas? Lee los testimonios de nuestros clientes que ya lograron un contrato de nuestros servicios.</p></div>
                  							<a href="https://kasu.com.mx/testimonios.php" style="padding:5px;">
                  								<img src="https://kasu.com.mx/assets/images/Correo/testimonios.png" alt="Testimoniales" width="580px" height="50px" style="display: block;" />
                  							</a>
                  						</td>
                  					</tr>
                  					<tr>
                  						<td bgcolor="#ffffff" align="center" width="80%" style="padding:5px; font-family: Arial, sans-serif; font-size: 14px;">
                  							<div align="Justify" style="padding:20px 15px 5px 25px; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 25px;"><p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:16px;font-family:arial, sans-serif;line-height:24px;color:#666666;">Si deseas que un ejecutivo te contacte y te dé a conocer los increíbles servicios que ofrece KASU, por favor no dudes en llamar y agendar una cita sin costo alguno, recuerda dar click en SABER MÁS para mayor información LEGAL de KASU.</p></div>
                  							<a href="'.$Titulo1.'" style="padding:5px;">
                  								<img src="https://kasu.com.mx/assets/images/Correo/sabermas.png" alt="Saber más" width="580px" height="50px" style="display: block;" />
                  							</a>
                  							<a href="'.$Desc1.'" style="padding:5px;">
                  								<img src="https://kasu.com.mx/assets/images/Correo/llamada3.png" alt="Agendar Cita" width="580px" height="50px" style="display: block;" />
                  							</a>
                  						</td>
                  					</tr>
                  					<tr>
                  						<td bgcolor="#ffffff" align="center" width="80%" style="padding:5px; font-family: Arial, sans-serif; font-size: 14px;">
                  							<div align="Justify" style="padding:20px 15px 5px 25px; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 25px;"><p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:16px;font-family:arial, sans-serif;line-height:24px;color:#666666;">Te brindamos un increíble servicio a base de productos, con el objetivo de proteger y ayudar a toda tú familia, sabias ¿Qué? Cada producto es una gran oportunidad exclusiva para prevenir y cuidar lo que tú más amas.</p></div>
                  							<a href="'.$dirUrl1.'">
                  								<img src="https://kasu.com.mx/assets/images/Correo/convencido1.png" alt="Me Han Convencido" width="400px" height="90px" />
                  							</a>
                  						</td>
                  					</tr>
                  					<tr>
                  						<td bgcolor="#ffffff" align="justify" style="padding: 5px 25px 30px 25px; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
                  							<p align="justify" style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:14px;font-family:arial, sans-serif;line-height:24px;color:#666666;"><br />Saludos: Atención a cliente KASU.</p>
                  						</td>
                  					</tr>
                  					<tr>
                  						<td style="padding: 15px 30px 5px 30px;">
                  							<table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:transparent;">
                  								<tr>
                  									<td align="center" width="100%">
                  										<table border="0" cellpadding="0" cellspacing="0">
                  											<tr>
                  												<td>
                  													<a href="https://www.facebook.com/KasuMexico">
                  														<img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/facebook-circle-colored.png" alt="Fb" title="Facebook" width="32" height="32" style="display:block; border:0; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic;" />
                  													</a>
                  												</td>
                  												<td style=" font-size: 0; line-height: 0; width: 20px">&nbsp;</td>
                  												<td>
                  													<a href="https://twitter.com/KASSU_11">
                  														<img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/twitter-circle-colored.png" alt="Tw" title="Twitter" width="32" height="32" style="display:block; border:0; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic;" />
                  													</a>
                  												</td>
                  												<td style=" font-size: 0; line-height: 0; width: 20px">&nbsp;</td>
                  												<td>
                  													<a href="https://www.instagram.com/kasumexico">
                  														<img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/instagram-circle-colored.png" alt="Ig" title="Instagram" width="32" height="32" style="display:block; border:0; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic;" />
                  													</a>
                  												</td>
                  											</tr>
                  										</table>
                  									</td>
                  								<tr>
                  									<td align="center" width="100%" style="font-family: Arial, sans-serif; font-size: 14px; padding: 0 10px 0 10px">
                  										<p style="text-align: center !important; color: aliceblue;">
                  											<font style="vertical-align:inherit;">
                  												© 2021&nbsp;|&nbsp;Kasu Servicios a futuro&nbsp;<br>
                  												KASU es una empresa que desarrolla productos financieros, para solventar momentos importantes en tu vida y la de los tuyos.
                  											</font>
                                        <a href="https://kasu.com.mx/index.php?Ml=4&Id='.$Id.'" style="color: #153643;">
                                          <font>Ya no quiero recibir este correo</font>
                                        </a>
                  										</p>
                  									</td>
                  								</tr>
                  							</table>
                  						</td>
                  					</tr>
                  				</table>
                  			</td>
                  		</tr>
                  	</table>
                  </body>
                  </html>';
                }
              elseif($Asunto == 'CONOCENOS UN POCO MÁS'){
                    $message = $Confia='<body style="margin: 0; padding: 0;">
                    	<table align="center" cellpadding="0" cellspacing="0" width="600px" style="border: 1px solid #cccccc; background-position:center center; background-image:url(http://kasu.com.mx/assets/images/Correo/fondo.jpg);">
                    		<tr>
                    			<td style="padding: 20px 0 30px 0">
                    				<table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:transparent;">
                    					<tr>
                    						<td align="center" style="padding: 30px 2px 30px 2px; background-repeat:no-repeat; color: #153643; font-family: Arial, sans-serif; font-size: 25px;">
                    							<a href="https://kasu.com.mx" style="text-decoration: none; color: whitesmoke;">
                    								<img src="https://kasu.com.mx/assets/images/Correo/florredonda.png" alt="Logo" width="60px" height="60px" style="display: block;" /><br><b>'.$Cte.' conoce KASU a fondo</b></a>
                    						</td>
                    					</tr>
                    					<tr>
                    						<td bgcolor="#ffffff" align="center" width="80%" style="padding:5px; font-family: Arial, sans-serif; font-size: 14px;">
                    							<div align="Justify" style="padding:20px 15px 5px 25px; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 25px;"><p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:16px;font-family:arial, sans-serif;line-height:24px;color:#666666;">¿Qué es un fideicomiso? Es un contrato auténtico en el cual una persona tiene y delega determinados bienes de su propiedad, a una empresa autorizada. Esto, con la finalidad de que administren exclusivamente los bienes en beneficio de un tercero o el mismo.
                    							Siempre nos preocupa que puede pasar en el futuro, ya que recomendamos la solución de este producto a largo plazo, por ello te invitamos a conocer y a comprobar el instrumento efectivo del producto que, le da fuerza a nuestra empresa “El fideicomiso”.</p></div>
                    							<a href="'.$dirUrl1.'" style="padding:5px;" download>
                    								<img src="https://kasu.com.mx/assets/images/Correo/fideicomiso1.png" alt="Consultar Fideicomiso" width="580px" height="50px" style="display: block;" />
                    							</a>
                    						</td>
                    					</tr>
                    					<tr>
                    						<td bgcolor="#ffffff" align="center" width="80%" style="padding:5px; font-family: Arial, sans-serif; font-size: 14px;">
                    							<div align="Justify" style="padding:20px 15px 5px 25px; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 25px;"><p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:16px;font-family:arial, sans-serif;line-height:24px;color:#666666;">Sí eres una persona insegura a la hora de un contrato y le temes a las letras pequeñas, procura revisar este enlace para conocer todas las aclaraciones que KASU brinda en su servicio.</p></div>
                    							<a href="'.$imag1.'?Ser='.$Titulo1.'" style="padding:5px;" >
                    								<img src="https://kasu.com.mx/assets/images/Correo/letritas.png" alt="Ver Letras Pequeñas" width="580px" height="50px" style="display: block;" />
                    							</a>
                    						</td>
                    					</tr>
                    					<tr>
                    						<td bgcolor="#ffffff" align="center" width="80%" style="padding:5px; font-family: Arial, sans-serif; font-size: 14px;">
                    							<div align="Justify" style="padding:20px 15px 5px 25px; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 25px;"><p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:16px;font-family:arial, sans-serif;line-height:24px;color:#666666;">¿Tienes dudas? Puedes agendar una cita telefónica con un ejecutivo, él/ella te va a contactar para resolver todas tus preguntas. También puedes dar click en SABER MÁS para recibir toda la información LEGAL de KASU.</p></div>
                    							<a href="'.$Desc1.'" style="padding:5px;">
                    								<img src="https://kasu.com.mx/assets/images/Correo/llamada3.png" alt="Agendar Cita" width="580px" height="50px" style="display: block;" />
                    							</a>
                    						</td>
                    					</tr>
                    					<tr>
                    						<td bgcolor="#ffffff" align="center" width="80%" style="padding:5px; font-family: Arial, sans-serif; font-size: 14px;">
                    							<div align="Justify" style="padding:20px 15px 5px 25px; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 25px;"><p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:16px;font-family:arial, sans-serif;line-height:24px;color:#666666;">Te brindamos garantías de los productos, con el objetivo de mejorar y proteger a toda tu familia. Cada servicio es un resultado exclusivo para prevenir y cuidar lo que realmente tú más amas.</p></div>
                    							<a href="'.$dirUrl2.'">
                    								<img src="https://kasu.com.mx/assets/images/Correo/convencido1.png" alt="Me Han Convencido" width="400px" height="90px" />
                    							</a>
                    						</td>
                    					</tr>
                    					<tr>
                    						<td bgcolor="#ffffff" align="justify" style="padding: 5px 25px 30px 25px; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
                    							<p align="justify" style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:14px;font-family:arial, sans-serif;line-height:24px;color:#666666;"><br/>Saludos: Atención a cliente KASU.</p>
                    						</td>
                    					</tr>
                    					<tr>
                    						<td style="padding: 15px 30px 5px 30px;">
                    							<table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:transparent;">
                    								<tr>
                    									<td align="center" width="100%">
                    										<table border="0" cellpadding="0" cellspacing="0">
                    											<tr>
                    												<td>
                    													<a href="https://www.facebook.com/KasuMexico">
                    														<img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/facebook-circle-colored.png" alt="Fb" title="Facebook" width="32" height="32" style="display:block; border:0; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic;" />
                    													</a>
                    												</td>
                    												<td style=" font-size: 0; line-height: 0; width: 20px">&nbsp;</td>
                    												<td>
                    													<a href="https://twitter.com/KASSU_11">
                    														<img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/twitter-circle-colored.png" alt="Tw" title="Twitter" width="32" height="32" style="display:block; border:0; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic;" />
                    													</a>
                    												</td>
                    												<td style=" font-size: 0; line-height: 0; width: 20px">&nbsp;</td>
                    												<td>
                    													<a href="https://www.instagram.com/kasumexico">
                    														<img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/instagram-circle-colored.png" alt="Ig" title="Instagram" width="32" height="32" style="display:block; border:0; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic;" />
                    													</a>
                    												</td>
                    											</tr>
                    										</table>
                    									</td>
                    								<tr>
                    									<td align="center" width="100%" style="font-family: Arial, sans-serif; font-size: 14px; padding: 0 10px 0 10px">
                    										<p style="text-align: center !important; color: aliceblue;">
                    											<font style="vertical-align:inherit;">
                    												© 2021&nbsp;|&nbsp;Kasu Servicios a futuro&nbsp;<br>
                    												KASU es una empresa que desarrolla productos financieros, para solventar momentos importantes en tu vida y la de los tuyos.
                    											</font>
                                          <a href="https://kasu.com.mx/index.php?Ml=4&Id='.$Id.'" style="color: #153643;">
                                            <font>Ya no quiero recibir este correo</font>
                                          </a>
                    										</p>
                    									</td>
                    								</tr>
                    							</table>
                    						</td>
                    					</tr>
                    				</table>
                    			</td>
                    		</tr>
                    	</table>
                    </body>
                    </html>';
					      }
              elseif($Asunto == 'PROCESO DE COMPRA DE SERVICIOS KASU'){
                   $message = $Compra='<!DOCTYPE html>
                    <html lang="ES" xmlns="http://www.w3.org/1999/xhtml">
                    <head>
                    	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
                    	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
                    	<title>KASU</title>
                    	<style type="text/css">
                    		@media only screen and (max-width:600px) {
                    			ul li,
                    			ol li,
                    			p
                    				{
                    				font-size: 20px !important;
                    				line-height: 150% !important;
                    				text-align: Justify !important;
                    			}

                    			h1 {
                    				font-size: 16px !important;
                    				text-align: justify;
                    				line-height: 120% !important
                    			}

                    			h1 a {
                    				font-size: 18px !important
                    			}

                    			*[class="gmail-fix"] {
                    				display: none !important
                    			}

                    			a.es-button {
                    				font-size: 20px !important;
                    				color: whitesmoke !important;
                    				display: block !important;
                    				text-decoration: none;
                    				text-align: center !important;
                    				border-left-width: 0px !important;
                    				border-right-width: 0px !important
                    			}

                    			table.es-table-not-adapt,
                    			.esd-block-html table {
                    				width: auto !important
                    			}
                    		}

                    		#outlook a {
                    			padding: 0;
                    		}

                    		a[x-apple-data-detectors] {
                    			color: inherit !important;
                    			text-decoration: none !important;
                    			font-size: inherit !important;
                    			font-family: inherit !important;
                    			font-weight: inherit !important;
                    			line-height: inherit !important;
                    		}
                    	</style>
                    </head>
                    <body style="margin: 0; padding: 0;">
                    	<table align="center" cellpadding="0" cellspacing="0" width="600px" style="border: 1px solid #cccccc; background-position:top; background-image:url(http://kasu.com.mx/assets/images/Correo/fondo.jpg);">
                    		<tr>
                    			<td style="padding: 20px 0 30px 0">
                    				<table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:transparent;">
                    					<tr>
                    						<td align="center" style="padding: 30px 2px 30px 2px; background-repeat:no-repeat; color: #153643; font-family: Arial, sans-serif; font-size: 25px;">
                    							<a href="https://kasu.com.mx" style="text-decoration: none; color: white;">
                    								<img src="https://kasu.com.mx/assets/images/Correo/florredonda.png" alt="" width="80px" height="80px" style="display: block;" /><br><b>'.$Cte.' comencemos con el proceso </b></a>
                    						</td>
                    					</tr>
                    					<tr>
                    						<td bgcolor="#ffffff" align="justify" style="padding:30px 20px 30px 20px; color: #153643; font-family: Arial, sans-serif; font-size: 16px;">
                    							<br/>
                    							<div align="Justify" style="padding:20px 15px 5px 2px; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 25px;"><p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:16px;font-family:arial, sans-serif;line-height:24px;color:#666666;">¿Cómo adquirir mi póliza de servicios KASU? Facil, solamente necesitas seguir los siguientes pasos</p></div>
                    						</td>
                    					</tr>
                    					<tr>
                    						<td bgcolor="#ffffff" align="center" style="padding: 20px; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
                    							<a href="'.$DirUrl.'" style="padding:5px;">
                    								<img style="padding:8px; border: 1px solid red; box-shadow: 0 4px 8px 0 rgba(255, 0, 0, 0.2), 0 6px 20px 0 rgba(255, 0, 0, 0.19);" src="https://kasu.com.mx/assets/images/Correo/GIFCOMPRA.gif" alt="Proceso de Compra" width="500px" height="280px">
                    							</a>
                    						</td>
                    					</tr>
                    					<tr>
                    						<td align="center" style="padding: 15px; font-family: Arial, sans-serif; font-size: 14px;">
                    							<a href="'.$dirUrl1.'" style="padding:5px;">
                    								<img src="https://kasu.com.mx/assets/images/Correo/contrata1.png" alt="Contratar" width="380px" height="90px" style="display: block;" />
                    							</a>
                    						</td>
                    					</tr>
                    					<tr>
                    						<td style="padding: 15px 30px 5px 30px;">
                    							<table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:transparent;">
                    								<tr>
                    									<td align="center" width="100%" style="font-family: Arial, sans-serif; font-size: 14px; padding: 20px 10px 0 10px">
                    										<table border="0" cellpadding="0" cellspacing="0">
                    											<tr>
                    												<td>
                    													<a href="https://www.facebook.com/KasuMexico">
                    														<img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/facebook-circle-colored.png" alt="Fb" title="Facebook" width="32" height="32" style="display:block; border:0; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic;" />
                    													</a>
                    												</td>
                    												<td style=" font-size: 0; line-height: 0; width: 20px">&nbsp;</td>
                    												<td>
                    													<a href="https://twitter.com/KASSU_11">
                    														<img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/twitter-circle-colored.png" alt="Tw" title="Twitter" width="32" height="32" style="display:block; border:0; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic;" />
                    													</a>
                    												</td>
                    												<td style=" font-size: 0; line-height: 0; width: 20px">&nbsp;</td>
                    												<td>
                    													<a href="https://www.instagram.com/kasumexico">
                    														<img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/instagram-circle-colored.png" alt="Ig" title="Instagram" width="32" height="32" style="display:block; border:0; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic;" />
                    													</a>
                    												</td>
                    											</tr>
                    										</table>
                    									</td>
                    								<tr>
                    									<td align="center" width="100%" style="font-family: Arial, sans-serif; font-size: 14px; padding: 10px 10px 0 10px">
                    										<p style="text-align: center !important; color: aliceblue;">
                    											<font style="vertical-align:inherit;">
                    												© 2021&nbsp;|&nbsp;Kasu Servicios a futuro&nbsp;<br>
                    												KASU es una empresa que desarrolla productos financieros, para solventar momentos importantes en tu vida y la de los tuyos.
                    											</font><br>
                    											<a href="https://kasu.com.mx/RegistDatos.php?Rastreo=Venta&Ev=Contratar" style="color:aliceblue">
                    												<font>Comprar ahora</font>
                                          </a>
                                          <a href="https://kasu.com.mx/index.php?Ml=4&Id='.$Id.'" style="color: #153643;">
                                            <font>Ya no quiero recibir este correo</font>
                    											</a>
                    										</p>
                    									</td>
                    								</tr>
                    							</table>
                    						</td>
                    					</tr>
                    				</table>
                    			</td>
                    		</tr>
                    	</table>
                    </body></html>';
    					  }
              elseif($Asunto == 'AGENDAR CITA'){
                      $message = $Confia='<body style="margin: 0; padding: 0;">
                      	<table align="center" cellpadding="0" cellspacing="0" width="600px" style="border: 1px solid #cccccc; background-position:center center; background-image:url(http://kasu.com.mx/assets/images/Correo/fondo.jpg);">
                      		<tr>
                      			<td style="padding: 20px 0 30px 0">
                      				<table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:transparent;">
                      					<tr>
                      						<td align="center" style="padding: 30px 2px 30px 2px; background-repeat:no-repeat; color: #153643; font-family: Arial, sans-serif; font-size: 25px;">
                      							<a href="https://kasu.com.mx" style="text-decoration: none; color: whitesmoke;">
                      								<img src="https://kasu.com.mx/assets/images/Correo/florredonda.png" alt="Logo" width="60px" height="60px" style="display: block;" /><br><b>'.$Cte.'<br> Recibe mas Informacion</b></a>
                      						</td>
                      					</tr>
                      					<tr>
                      						<td bgcolor="#ffffff" align="center" width="80%" style="padding:5px; font-family: Arial, sans-serif; font-size: 14px;">
                      							<div align="Justify" style="padding:20px 15px 5px 25px; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 25px;"><p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:16px;font-family:arial, sans-serif;line-height:24px;color:#666666;">Agenda una cita telefónica,y uno de nuestros ejecutivos te contactara para resolver todas tus preguntas, y darte la informacion que requieras sobre KASU.</p></div>
                      							<a href=https://kasu.com.mx/prospectos.php?data='.$DirUrl.'&Usr="'.$dirUrl1.'" style="padding:5px;">
                      								<img src="https://kasu.com.mx/assets/images/Correo/llamada3.png" alt="Agendar Cita" width="580px" height="50px" style="display: block;" />
                      							</a>
                      						</td>
                      					</tr>
                                <tr>
                                  <td bgcolor="#ffffff" align="center" width="80%" style="padding:5px; font-family: Arial, sans-serif; font-size: 14px;">
                                    <div align="Justify" style="padding:20px 15px 5px 25px; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 25px;"><p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:16px;font-family:arial, sans-serif;line-height:24px;color:#666666;">
                                    Con nuestra innovadora tecnologia de seguimiento, puedes generar ingresos de por vida, tan solo por compartir nuestras publicaciones sociales.
                                    </p></div>
                                  </td>
                                </tr>
                      					<tr>
                      						<td bgcolor="#ffffff" align="justify" style="padding: 5px 25px 30px 25px; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
                      							<p align="justify" style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:14px;font-family:arial, sans-serif;line-height:24px;color:#666666;"><br/>Saludos: Atención a cliente KASU.</p>
                      						</td>
                      					</tr>
                      					<tr>
                      						<td style="padding: 15px 30px 5px 30px;">
                      							<table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:transparent;">
                      								<tr>
                      									<td align="center" width="100%">
                      										<table border="0" cellpadding="0" cellspacing="0">
                      											<tr>
                      												<td>
                      													<a href="https://www.facebook.com/KasuMexico">
                      														<img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/facebook-circle-colored.png" alt="Fb" title="Facebook" width="32" height="32" style="display:block; border:0; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic;" />
                      													</a>
                      												</td>
                      												<td style=" font-size: 0; line-height: 0; width: 20px">&nbsp;</td>
                      												<td>
                      													<a href="https://twitter.com/KASSU_11">
                      														<img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/twitter-circle-colored.png" alt="Tw" title="Twitter" width="32" height="32" style="display:block; border:0; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic;" />
                      													</a>
                      												</td>
                      												<td style=" font-size: 0; line-height: 0; width: 20px">&nbsp;</td>
                      												<td>
                      													<a href="https://www.instagram.com/kasumexico">
                      														<img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/instagram-circle-colored.png" alt="Ig" title="Instagram" width="32" height="32" style="display:block; border:0; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic;" />
                      													</a>
                      												</td>
                      											</tr>
                      										</table>
                      									</td>
                      								<tr>
                      									<td align="center" width="100%" style="font-family: Arial, sans-serif; font-size: 14px; padding: 0 10px 0 10px">
                      										<p style="text-align: center !important; color: aliceblue;">
                      											<font style="vertical-align:inherit;">
                      												© 2021&nbsp;|&nbsp;Kasu Servicios a futuro&nbsp;<br>
                      												KASU es una empresa que desarrolla productos financieros, para solventar momentos importantes en tu vida y la de los tuyos.
                      											</font>
                                            <br>
                                            <a href="https://kasu.com.mx/index.php?Ml=4&Id='.$Id.'" style="color: #153643;">
                                              <font>Ya no quiero recibir este correo</font>
                                            </a>
                      										</p>
                      									</td>
                      								</tr>
                      							</table>
                      						</td>
                      					</tr>
                      				</table>
                      			</td>
                      		</tr>
                      	</table>
                      </body>
                      </html>';
  					     }
              elseif($Asunto == 'CITA TELEFONICA'){
                $message = $Cita='<!DOCTYPE html>
                <html lang="ES" xmlns="http://www.w3.org/1999/xhtml">
                <head>
                	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
                	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
                	<title>KASU</title>
                	<style type="text/css">
                		@media only screen and (max-width:600px) {
                			ul li,
                			ol li,
                			p,
                				{
                				font-size: 18px !important;
                				line-height: 150% !important;
                				text-align: Justify !important;
                			}

                			h1 {
                				font-size: 20px !important;
                				text-align: Justify;
                				line-height: 120% !important
                			}

                			h1 a {
                				font-size: 18px !important
                			}

                			*[class="gmail-fix"] {
                				display: none !important
                			}

                			a.es-button {
                				font-size: 35px !important;
                				color: whitesmoke !important;
                				display: block !important;
                				text-decoration: none;
                				text-align: center !important;
                				border-left-width: 0px !important;
                				border-right-width: 0px !important
                			}

                			table.es-table-not-adapt,
                			.esd-block-html table {
                				width: auto !important
                			}
                		}

                		#outlook a {
                			padding: 0;
                		}

                		a[x-apple-data-detectors] {
                			color: inherit !important;
                			text-decoration: none !important;
                			font-size: inherit !important;
                			font-family: inherit !important;
                			font-weight: inherit !important;
                			line-height: inherit !important;
                		}
                	</style>

                </head>

                <body style="margin: 0; padding: 0;">

                	<table align="center" cellpadding="0" cellspacing="0" width="600px" style="border: 1px solid #cccccc; background-position:center center;background-image:url(http://kasu.com.mx/assets/images/Correo/fondo.jpg);">
                		<tr>
                			<td style="padding: 20px 0 30px 0">
                				<table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:transparent;">
                					<tr>
                						<td align="center" style="padding: 2px 2px 15px 2px; background-repeat:no-repeat; color: #153643; font-family: Arial, sans-serif; font-size: 25px;">
                							<a href="https://kasu.com.mx" style="text-decoration: none; color: whitesmoke;">
                								<img src="https://kasu.com.mx/assets/images/Correo/florredonda.png" alt="" width="80px" height="80px" style="display: block;" /><br><b>'.$Cte.' tu cita esta lista</b></a>
                						</td>
                					</tr>
                					<tr>
                						<td bgcolor="#ffffff" align="justify" style="padding:20px 15px 5px 25px; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 25px;">
                							<b style="vertical-align:inherit;"></b>
                						</td>
                					</tr>
                					<tr style="border-collapse:collapse;">
                						<td align="justify" bgcolor="#ffffff" style="padding:10px 30px 5px 30px; background-repeat:no-repeat;">
                							<p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:16px;font-family:arial, sans-serif;line-height:24px;color:#666666;">¡Ya agendamos tu cita telefónica para ponernos en contacto contigo!
                                            <br> Corroboramos tus datos</p><br />
                						</td>
                					</tr>
                					<tr>
                						<td style="padding: 15px 50px 5px 50px;" bgcolor="#ffffff" align="center">
                							<table border="0" cellpadding="0" cellspacing="0" width="80%" style="background-color:transparent; box-shadow: 0 4px 8px 0 rgba(0, 0, 255, 0.3), 0 6px 20px 0 rgba(0, 0, 255, 0.5);">
                								<tr>
                									<td align="justify" width="20%" style="padding: 5px 0 5px 5px" >
                										Nombre:
                									</td>
                									<td align="justify" width="50%" style="padding: 5px 0 5px 5px">
                										'.$Cte.'
                									</td>
                								</tr>
                								<tr>
                									<td align="justify" width="20%" style="padding: 5px 0 5px 5px">
                										Telefono:
                									</td>
                									<td align="justify" width="50%" style="padding: 5px 0 5px 5px">
                										'.$DirUrl.'
                									</td>
                								</tr>
                								<tr>
                									<td align="justify" width="20%" style="padding: 5px 0 5px 5px">
                										Dia y Hora:
                									</td>
                									<td align="justify" width="50%" style="padding: 5px 0 5px 5px">
                										'.$dirUrl1.'
                									</td>
                								</tr>
                							</table>
                						</td>
                					</tr>
                					<tr>
                						<td bgcolor="#ffffff" align="justify" style="padding: 5px 25px 30px 25px; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
                							<p align="justify" style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:14px;font-family:arial, sans-serif;line-height:24px;color:#666666;"><br />El equipo de atención a clientes de KASU agradece tu interés, Saludos</p>
                						</td>
                					</tr>
                					<tr>
                						<td style="padding: 15px 30px 5px 30px;">
                							<table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:transparent;">
                								<tr>
                									<td align="center" width="100%">
                										<table border="0" cellpadding="0" cellspacing="0">
                											<tr>
                												<td>
                													<a href="https://www.facebook.com/KasuMexico">
                														<img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/facebook-circle-colored.png" alt="Fb" title="Facebook" width="32" height="32" style="display:block; border:0; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic;" />
                													</a>
                												</td>
                												<td style=" font-size: 0; line-height: 0; width: 20px">&nbsp;</td>
                												<td>
                													<a href="https://twitter.com/KASSU_11">
                														<img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/twitter-circle-colored.png" alt="Tw" title="Twitter" width="32" height="32" style="display:block; border:0; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic;" />
                													</a>
                												</td>
                												<td style=" font-size: 0; line-height: 0; width: 20px">&nbsp;</td>
                												<td>
                													<a href="https://www.instagram.com/kasumexico">
                														<img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/instagram-circle-colored.png" alt="Ig" title="Instagram" width="32" height="32" style="display:block; border:0; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic;" />
                													</a>
                												</td>
                											</tr>
                										</table>
                									</td>
                								<tr>
                									<td align="center" width="100%" style="font-family: Arial, sans-serif; font-size: 14px; padding: 0 10px 0 10px">
                										<p style="text-align: center !important; color: aliceblue;">
                											<font style="vertical-align:inherit;">
                												© 2021&nbsp;|&nbsp;Kasu Servicios a futuro&nbsp;<br>
                												KASU es una empresa que desarrolla productos financieros, para solventar momentos importantes en tu vida y la de los tuyos.
                											</font>
                                      <a href="https://kasu.com.mx/index.php?Ml=4&Id='.$Id.'" style="color: #153643;">
                                        <font>Ya no quiero recibir este correo</font>
                                      </a>
                										</p>
                									</td>
                								</tr>
                							</table>
                						</td>
                					</tr>
                				</table>
                			</td>
                		</tr>
                	</table>
                </body></html>';
    					 }
              elseif($Asunto=='Felices Fiestas'){
                        $message = '<!DOCTYPE html>
                                  <html lang="ES" xmlns="http://www.w3.org/1999/xhtml">
                                  <head>
                                    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
                                    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
                                    <title>KASU</title>
                                    <style type="text/css">
                                      @media only screen and (max-width:600px) {
                                        ul li,
                                        ol li, p,
                                         {
                                          font-size: 18px !important;
                                          line-height: 150% !important;
                                          text-align: Justify !important;
                                        }
                                        h1 {
                                          font-size: 20px !important;
                                          text-align: Justify;
                                          line-height: 120% !important
                                        }
                                        h1 a {
                                          font-size: 18px !important
                                        }
                                        *[class="gmail-fix"] {
                                          display: none !important
                                        }
                                        a.es-button {
                                          font-size: 35px !important;
                                          color: whitesmoke !important;
                                          display: block !important;
                                          text-decoration: none;
                                          text-align: center !important;
                                          border-left-width: 0px !important;
                                          border-right-width: 0px !important
                                        }
                                        table.es-table-not-adapt,
                                        .esd-block-html table {
                                          width: auto !important
                                        }
                                      }
                                      #outlook a {
                                        padding: 0;
                                      }
                                      a[x-apple-data-detectors] {
                                        color: inherit !important;
                                        text-decoration: none !important;
                                        font-size: inherit !important;
                                        font-family: inherit !important;
                                        font-weight: inherit !important;
                                        line-height: inherit !important;
                                      }
                                    </style>
                                  </head>
                                  <body style="margin: 0; padding: 0;">
                                    <table align="center" cellpadding="0" cellspacing="0" width="600px" style="border: 1px solid #cccccc; background-position:center top;background-image:url(http://kasu.com.mx/assets/images/Correo/fondo.jpg);">
                                      <tr>
                                        <td style="padding: 20px 0 30px 0">
                                          <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:transparent;">
                                            <tr>
                                              <td align="center" style="padding: 2px 2px 15px 2px; background-repeat:no-repeat; color: #153643; font-family: Arial, sans-serif; font-size: 25px;">
                                                <a href="https://kasu.com.mx/" style="text-decoration: none; color: whitesmoke;">
                                                  <img src="https://kasu.com.mx/assets/images/kasu_logo.jpeg" alt="" width="100px" height="100px" style="display: block;" /></a>
                                              </td>
                                            </tr>
                                            <tr>
                                              <td bgcolor="#ffffff" align="justify" style="padding:20px 15px 5px 25px; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 25px;">
                                                    <div class="" style="text-align:center">
                                                      <b style="vertical-align:inherit;">Hola '.$Cte.'</b><br>
                                                      <b style="text-align:center">“El compromiso con nuestros clientes crece año tras año su confianza es nuestro más grande valor”</b>
                                                    </div>
                                              </td>
                                            </tr>
                                            <tr style="border-collapse:collapse;">
                                                <td align="left" bgcolor="#ffffff" style="padding:10px 30px 5px 30px; background-repeat:no-repeat;">
                                                  <!-- <p style="font-size:16px;font-family:arial, sans-serif;line-height:24px;color:#666666;">Gracias por registrarte.</p> -->
                                                  <p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:16px;font-family:arial, sans-serif;line-height:24px;color:#666666;">En KASU estamos felices de contar con su preferencia, gracias por abrirnos las puertas de sus hogares, y depositar en nosotros el más grande valor de la confianza, para la protección de las personas que más ama; y por ello les extendemos nuestro compromiso para brindarles confianza y calidad por mucho tiempo más.
                                                  Les deseamos un nuevo año venturoso y lleno de amor.</p> <br>
                                                  <div style="text-align:center">
                                                    <p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:16px;font-family:arial, sans-serif;line-height:24px;color:Black;">¡Felices fiestas!</p>
                                                  </div>

                                                </td>
                                              </tr>
                                            <tr>
                                              <tr>
                                    						<td bgcolor="#ffffff" align="justify" style="padding: 5px 25px 30px 25px; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
                                                    <p align="justify" style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:14px;font-family:arial, sans-serif;line-height:24px;color:#666666;"><br><b>Equipo KASU.</b></p>
                                    						</td>
                                    					</tr>
                                              <td style="padding: 15px 30px 5px 30px;">
                                                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:transparent;">
                                                  <tr>
                                                    <td align="center" width="100%">
                                                      <table border="0" cellpadding="0" cellspacing="0">
                                                        <tr>
                                                          <td>
                                                            <a href="https://www.facebook.com/KasuMexico">
                                                              <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/facebook-circle-colored.png" alt="Fb" title="Facebook" width="32" height="32" style="display:block; border:0; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic;" />
                                                            </a>
                                                          </td>
                                                          <td style=" font-size: 0; line-height: 0; width: 20px">&nbsp;</td>
                                                          <td>
                                                            <a href="https://twitter.com/KASSU_11">
                                                              <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/twitter-circle-colored.png" alt="Tw" title="Twitter" width="32" height="32" style="display:block; border:0; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic;" />
                                                            </a>
                                                          </td>
                                                          <td style=" font-size: 0; line-height: 0; width: 20px">&nbsp;</td>
                                                          <td>
                                                            <a href="https://www.instagram.com/kasumexico">
                                                              <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/instagram-circle-colored.png" alt="Ig" title="Instagram" width="32" height="32" style="display:block; border:0; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic;" />
                                                            </a>
                                                          </td>
                                                        </tr>
                                                      </table>
                                                    </td>
                                                  <tr>
                                                    <td align="center" width="100%" style="font-family: Arial, sans-serif; font-size: 14px; padding: 0 10px 0 10px">
                                                      <p style="text-align: center !important;color:white">
                                                        <font style="vertical-align:inherit;">
                                                          © 2021&nbsp;Kasu Servicios a futuro&nbsp;| <br> Bosque de Chapultepec, Pedregal 24, Molino del Rey <br> Ciudad de México, CDMX, Mexico C.P. 11000 <br>
                                                        </font>
                                                        <a href="https://kasu.com.mx/registro.php" style="color: white;">
                                                          <font>Comprar ahora</font>
                                                        </a>
                                                      </p>
                                                    </td>
                                                  </tr>
                                                </table>
                                              </td>
                                            </tr>
                                          </table>
                                        </td>
                                      </tr>
                                    </table>
                                  </body></html>';
                             }
           return $message;
    }
    /**************************************************************
    Esta funcion hace que se descargue un archivo
    $archivo=> nombre del archivo con extencion, que se descargara
    **************************************************************/
    public function descargar($nombre_archivo) {
		 if (!isset($nombre_archivo) || empty($nombre_archivo)) {
			 exit();
		 }
				 $root = "ArchivosKasu/";
				 $file = basename($nombre_archivo);
				 $path = $root.$file;
				 $type = '';
				 if (is_file($path)) {
					 $size = filesize($path);
					 if (function_exists('mime_content_type')) {
						 $type = mime_content_type($path);
					 }
					 else if (function_exists('finfo_file')) {
						 $info = finfo_open(FILEINFO_MIME);
						 $type = finfo_file($info, $path);
						 finfo_close($info);
					 }
					 if ($type == '') {
						 $type = "application/force-download";
					 }
					 // Define los headers
					 header("Content-Type: $type");
					 header("Content-Disposition: attachment; filename=$file");
					 header("Content-Transfer-Encoding: binary");
					 header("Content-Length: " . $size);
					 // Descargar el archivo
					 readfile($path);
				 } else {
					 //die("El archivo no existe.");
					 echo "<script type='text/javascript'>alert('El archivo no existe');</script>";
					 }
				 }

}
