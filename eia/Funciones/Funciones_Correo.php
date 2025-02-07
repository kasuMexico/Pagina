<?php
//Contador de veces que se usa cada funcion para seguimiento *JCCM
require_once 'FunctionUsageTracker.php';

class Correo {
    
  // Usa el trait para poder registrar el uso de los métodos.
    use UsageTrackerTrait;

    /**
     * Retorna el contenido HTML del mensaje de correo según el asunto.
     *
     * @param string $Asunto El asunto (selecciona la plantilla).
     * @param string $Cte    Nombre del cliente (para personalización).
     * @param string $DirUrl Enlace o código para botones.
     * @param string $dirUrl1 URL o dato 1 (por ejemplo, artículo o enlace adicional).
     * @param string $imag1   Imagen 1.
     * @param string $Titulo1 Título 1.
     * @param string $Desc1   Descripción 1.
     * @param string $dirUrl2 URL o dato 2.
     * @param string $imag2   Imagen 2.
     * @param string $Titulo2 Título 2.
     * @param string $Desc2   Descripción 2.
     * @param string $dirUrl3 URL o dato 3.
     * @param string $imag3   Imagen 3.
     * @param string $Titulo3 Título 3.
     * @param string $Desc3   Descripción 3.
     * @param string $dirUrl4 URL o dato 4.
     * @param string $imag4   Imagen 4.
     * @param string $Titulo4 Título 4.
     * @param string $Desc4   Descripción 4.
     * @param mixed  $Id      Identificador para enlaces de "no recibir correo", etc.
     *
     * @return string El HTML del mensaje.
     */
    public function Mensaje($Asunto, $Cte, $DirUrl, $dirUrl1, $imag1, $Titulo1, $Desc1, $dirUrl2, $imag2, $Titulo2, $Desc2, $dirUrl3, $imag3, $Titulo3, $Desc3, $dirUrl4, $imag4, $Titulo4, $Desc4, $Id) {
      $this->trackUsage();  // Registra el uso de este método.
        $message = "";
        switch ($Asunto) {
            case '¡BIENVENIDO A KASU!':
                $message = <<<HTML
<!DOCTYPE html>
<html lang="ES" xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>KASU</title>
    <style type="text/css">
      @media only screen and (max-width:600px){
        ul li, ol li, p {
          font-size: 18px !important;
          line-height: 150% !important;
          text-align: Justify !important;
        }
        h1 {
          font-size: 20px !important;
          text-align: Justify;
          line-height: 120% !important;
        }
        h1 a {
          font-size: 18px !important;
        }
        *[class="gmail-fix"] {
          display: none !important;
        }
        a.es-button {
          font-size: 35px !important;
          color: whitesmoke !important;
          display: block !important;
          text-decoration: none;
          text-align: center !important;
          border-left-width: 0px !important;
          border-right-width: 0px !important;
        }
        table.es-table-not-adapt,
        .esd-block-html table {
          width: auto !important;
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
    <table align="center" cellpadding="0" cellspacing="0" width="600px" style="border: 1px solid #cccccc; background-image:url(http://kasu.com.mx/assets/images/Correo/fondo.jpg); background-position:center top;">
      <tr>
        <td style="padding: 20px 0 30px 0">
          <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:transparent;">
            <tr>
              <td align="center" style="padding:2px; font-size:25px; color:#153643; font-family:Arial, sans-serif;">
                <a href="https://kasu.com.mx" style="text-decoration:none; color:whitesmoke;">
                  <img src="https://kasu.com.mx/assets/images/Correo/florredonda.png" alt="" width="100px" height="100px" style="display:block;" />
                </a>
              </td>
            </tr>
            <tr>
              <td bgcolor="#ffffff" align="justify" style="padding:20px 15px 5px 25px; color:#153643; font-family:Arial, sans-serif; font-size:16px;">
                <b>{$Cte}</b>
              </td>
            </tr>
            <tr style="border-collapse:collapse;">
              <td align="left" bgcolor="#ffffff" style="padding:10px 30px 5px 30px;">
                <p style="font-size:16px; font-family:arial, sans-serif; line-height:24px; color:#666666;">Felicidades por ser cliente KASU.</p>
                <p style="font-size:16px; font-family:arial, sans-serif; line-height:24px; color:#666666;">
                  Es un honor informarte que tu póliza ya está activa. Descarga tu póliza dando clic en el siguiente botón.
                </p>
              </td>
            </tr>
            <tr>
              <td bgcolor="#ffffff" align="center" style="padding:30px;">
                <span style="border:solid #ee3a87; background:#ee3a87; border-width:20px; display:inline-block; border-radius:5px;">
                  <a href="https://kasu.com.mx/login/Generar_PDF/Poliza_pdf.php?busqueda={$DirUrl}" class="es-button" target="_blank" style="text-decoration:none; font-family:Arial, sans-serif; font-size:30px; color:#FFFFFF; background:#ee3a87; border-radius:5px; line-height:30px;">Descargar Póliza</a>
                </span>
              </td>
            </tr>
            <tr>
              <td bgcolor="#ffffff" align="justify" style="padding:5px 25px 30px 25px; font-family:Arial, sans-serif; font-size:16px; line-height:20px; color:#153643;">
                <p style="font-size:16px; font-family:Arial, sans-serif; line-height:24px; color:#666666;">
                  Recuerda que en todo momento puedes consultar tus datos y descargar tu póliza en kasu.com.mx
                </p>
                <p style="font-size:12px; font-family:Arial, sans-serif; line-height:24px; color:#666666;">
                  Saludos<br>Equipo KASU
                </p>
              </td>
            </tr>
            <tr>
              <td style="padding:15px 30px;">
                <table align="center" cellpadding="0" cellspacing="0" width="100%">
                  <tr>
                    <td align="center">
                      <table border="0" cellpadding="0" cellspacing="0">
                        <tr>
                          <td>
                            <a href="https://www.facebook.com/KasuMexico">
                              <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/facebook-circle-colored.png" alt="Fb" title="Facebook" width="32" height="32" style="display:block; border:0;" />
                            </a>
                          </td>
                          <td style="width:20px;">&nbsp;</td>
                          <td>
                            <a href="https://twitter.com/KASSU_11">
                              <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/twitter-circle-colored.png" alt="Tw" title="Twitter" width="32" height="32" style="display:block; border:0;" />
                            </a>
                          </td>
                          <td style="width:20px;">&nbsp;</td>
                          <td>
                            <a href="https://www.instagram.com/kasumexico">
                              <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/instagram-circle-colored.png" alt="Ig" title="Instagram" width="32" height="32" style="display:block; border:0;" />
                            </a>
                          </td>
                        </tr>
                      </table>
                    </td>
                  </tr>
                  <tr>
                    <td align="center" style="font-family:Arial, sans-serif; font-size:14px; padding:0 10px;">
                      <p style="text-align:center;">
                        <font>© 2019 KASU Servicios a futuro<br> Bosque de Chapultepec, Pedregal 24, Molino del Rey<br> Ciudad de México, CDMX, Mexico C.P. 11000</font>
                        <a href="https://kasu.com.mx/index.php?Ml=4&Id={$Id}" style="color:#153643;">
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
</html>
HTML;
                break;
            case 'GRACIAS POR SUSCRIBIRTE':
                $message = <<<HTML
<!DOCTYPE html>
<html lang="ES">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>KASU</title>
  <style type="text/css">
    @media only screen and (max-width:600px){
      ul li, ol li, p { font-size:15px !important; line-height:150% !important; text-align:justify !important; }
      h1 { font-size:18px !important; text-align:justify; line-height:120% !important; }
      *[class="gmail-fix"] { display:none !important; }
      a.es-button { font-size:15px !important; color:#FFFFFF !important; display:block !important; text-decoration:none; text-align:center !important; }
      table.es-table-not-adapt, .esd-block-html table { width:auto !important; }
    }
    #outlook a { padding:0; }
    a[x-apple-data-detectors] { color:inherit !important; text-decoration:none !important; font-size:inherit !important; font-family:inherit !important; }
  </style>
</head>
<body style="margin:0; padding:0;">
  <!-- Plantilla de agradecimiento por suscribirse -->
  <table align="center" cellpadding="0" cellspacing="0" width="600px" style="border:1px solid #cccccc; background-image:url(http://kasu.com.mx/assets/images/Correo/fondo.jpg);">
    <tr>
      <td style="padding:20px;">
        <table align="center" width="100%" style="background-color:transparent;">
          <tr>
            <td align="center" style="font-size:25px; color:#153643; font-family:Arial, sans-serif;">
              <a href="https://kasu.com.mx" style="text-decoration:none; color:white;">
                <img src="https://kasu.com.mx/assets/images/Correo/florredonda.png" alt="" style="display:block;" />
              </a>
            </td>
          </tr>
          <tr>
            <td bgcolor="#ffffff" align="center" style="padding:25px; font-family:Arial, sans-serif; font-size:16px;">
              <h1>¡BIENVENIDO A KASU NEWS!</h1>
            </td>
          </tr>
          <tr>
            <td bgcolor="#ffffff" align="center" style="padding:5px 25px 30px 25px; font-family:Arial, sans-serif; font-size:16px; line-height:25px; color:#153643;">
              <p>Te mantendremos informado sobre nuestras últimas entradas y sugerencias de lectura.</p>
            </td>
          </tr>
          <tr>
            <td align="center" style="padding:15px;">
              <p style="font-family:Arial, sans-serif; font-size:14px; text-align:center;">
                © 2019 KASU Servicios a futuro
              </p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;
                break;
            case 'PAGO PENDIENTE':
                $message = <<<HTML
<!DOCTYPE html>
<html lang="ES">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>KASU</title>
  <style type="text/css">
    @media only screen and (max-width:600px){
      ul li, ol li, p { font-size:16px !important; line-height:150% !important; text-align:Justify !important; }
      h1 { font-size:18px !important; text-align:center; line-height:120% !important; }
      h1 a { font-size:15px !important; }
      *[class="gmail-fix"] { display:none !important; }
      a.es-button { font-size:25px !important; color:#FFFFFF !important; display:block !important; text-decoration:none; text-align:center !important; border-left-width:0px !important; border-right-width:0px !important; }
      table.es-table-not-adapt, .esd-block-html table { width:auto !important; }
    }
    #outlook a { padding:0; }
    a[x-apple-data-detectors] { color:inherit !important; text-decoration:none !important; font-size:inherit !important; font-family:inherit !important; }
  </style>
</head>
<body style="margin:0; padding:0;">
  <table align="center" width="600px" style="border:1px solid #cccccc; background-image:url(http://kasu.com.mx/assets/images/Correo/fondo.jpg);">
    <tr>
      <td style="padding:20px 0 30px 0">
        <table align="center" width="100%" style="background-color:transparent;">
          <tr>
            <td align="center" style="padding:2px; font-size:25px; color:#153643; font-family:Arial, sans-serif;">
              <a href="https://kasu.com.mx" style="text-decoration:none; color:white;">
                <img src="https://kasu.com.mx/assets/images/Correo/florredonda.png" alt="" width="100px" height="100px" style="display:block;" />
              </a>
            </td>
          </tr>
          <tr>
            <td bgcolor="#ffffff" align="justify" style="padding:20px 15px 5px 25px; font-family:Arial, sans-serif; font-size:16px; line-height:25px; color:#153643;">
              <b>{$Cte}</b><br/>
              <p>Para continuar con tu pago por tarjeta, presiona el siguiente botón.</p>
            </td>
          </tr>
          <tr>
            <td bgcolor="#ffffff" align="center" style="padding:30px;">
              <span style="border:solid #ee3a87; background:#ee3a87; border-width:20px; display:inline-block; border-radius:5px;">
                <a href="{$DirUrl}" class="es-button" target="_blank" style="text-decoration:none; font-family:Arial, sans-serif; font-size:35px; color:#FFFFFF; background:#ee3a87; border-radius:5px; line-height:40px;">Pagar ahora</a>
              </span>
            </td>
          </tr>
          <tr>
            <td bgcolor="#ffffff" align="justify" style="padding:5px 25px 30px 25px; font-family:Arial, sans-serif; font-size:16px; line-height:20px;">
              <p style="background-color:azure;">"Recuerda que si pagas con tarjeta, NO se te llamará para solicitarte claves o NIP. No los proporciones si te los piden."</p>
              <p>Gracias por formar parte de la comunidad KASU.</p>
              <p style="font-size:12px; text-align:justify;">Si el botón no funciona, presiona el siguiente enlace: <a href="{$DirUrl}" style="color:cornflowerblue;"><b>PAGAR</b></a></p>
            </td>
          </tr>
          <tr>
            <td style="padding:15px 30px;">
              <table align="center" width="100%">
                <tr>
                  <td align="center">
                    <table border="0" cellpadding="0" cellspacing="0">
                      <tr>
                        <td>
                          <a href="https://www.facebook.com/KasuMexico">
                            <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/facebook-circle-colored.png" alt="Fb" title="Facebook" width="32" height="32" style="display:block; border:0;" />
                          </a>
                        </td>
                        <td style="width:20px;">&nbsp;</td>
                        <td>
                          <a href="https://twitter.com/KASSU_11">
                            <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/twitter-circle-colored.png" alt="Tw" title="Twitter" width="32" height="32" style="display:block; border:0;" />
                          </a>
                        </td>
                        <td style="width:20px;">&nbsp;</td>
                        <td>
                          <a href="https://www.instagram.com/kasumexico">
                            <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/instagram-circle-colored.png" alt="Ig" title="Instagram" width="32" height="32" style="display:block; border:0;" />
                          </a>
                        </td>
                      </tr>
                    </table>
                  </td>
                  <tr>
                    <td align="center" style="font-family:Arial, sans-serif; font-size:14px; padding:0 10px;">
                      <p style="text-align:center; color:aliceblue;">
                        <font>© 2021 | Kasu Servicios a futuro<br> Bosque de Chapultepec, Pedregal 24, Molino del Rey<br> Ciudad de México, CDMX, Mexico C.P. 11000</font>
                        <a href="https://kasu.com.mx/index.php?Ml=4&Id={$Id}" style="color:#153643;"><font>Ya no quiero recibir este correo</font></a>
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
</html>
HTML;
                break;
            case 'FICHAS DE PAGO KASU':
                $message = <<<HTML
<!DOCTYPE html>
<html lang="ES">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>KASU</title>
  <style type="text/css">
    @media only screen and (max-width:600px){
      ul li, ol li, p { font-size:14px !important; line-height:150% !important; text-align:Justify !important; }
      h1 { font-size:16px !important; text-align:center; line-height:120% !important; }
      a.es-button { font-size:20px !important; color:#FFFFFF !important; display:block !important; text-decoration:none; text-align:center !important; }
      table.es-table-not-adapt, .esd-block-html table { width:auto !important; }
    }
    #outlook a { padding:0; }
    a[x-apple-data-detectors] { color:inherit !important; text-decoration:none !important; font-size:inherit !important; font-family:inherit !important; }
  </style>
</head>
<body style="margin:0; padding:0;">
  <table align="center" width="600px" style="border:1px solid #cccccc; background-image:url(http://kasu.com.mx/assets/images/Correo/fondo.jpg);">
    <tr>
      <td style="padding:20px 0 30px 0">
        <table align="center" width="100%" style="background-color:transparent;">
          <tr>
            <td align="center" style="font-size:25px; color:#153643; font-family:Arial, sans-serif;">
              <a href="https://kasu.com.mx" style="text-decoration:none; color:white;">
                <img src="https://kasu.com.mx/assets/images/Correo/florredonda.png" alt="" style="display:block;" />
              </a>
            </td>
          </tr>
          <tr>
            <td bgcolor="#ffffff" align="justify" style="padding:20px 15px 5px 25px; font-family:Arial, sans-serif; font-size:16px; line-height:25px; color:#153643;">
              <b>{$Cte}</b><br/>
              <p>Para no olvidar tus fechas de pago te enviamos tus fichas. Da clic en el botón para descargarlas.</p>
            </td>
          </tr>
          <tr>
            <td bgcolor="#ffffff" align="center" style="padding:30px;">
              <span style="border:solid #ee3a87; background:#ee3a87; border-width:20px; display:inline-block; border-radius:5px;">
                <a href="https://kasu.com.mx/login/Generar_PDF/Fichas_Pago_pdf.php?Cte={$DirUrl}&data={$dirUrl1}" class="es-button" target="_blank" style="text-decoration:none; font-family:Arial, sans-serif; font-size:25px; color:#FFFFFF; background:#ee3a87; border-radius:5px; line-height:35px;">Descargar ahora</a>
              </span>
            </td>
          </tr>
          <tr>
            <td bgcolor="#ffffff" align="justify" style="padding:5px 25px 30px 25px; font-family:Arial, sans-serif; font-size:12px; line-height:15px;">
              <p style="font-size:12px;">Para cualquier duda o asesoría sobre tus fichas de pago: <b>llámanos 7125975763</b><br/>O contáctanos por redes sociales.</p>
            </td>
          </tr>
          <tr>
            <td style="padding:15px 30px;">
              <table align="center" width="100%">
                <tr>
                  <td align="center">
                    <table border="0" cellpadding="0" cellspacing="0">
                      <tr>
                        <td>
                          <a href="https://www.facebook.com/KasuMexico">
                            <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/facebook-circle-colored.png" alt="Fb" title="Facebook" width="32" height="32" style="display:block; border:0;" />
                          </a>
                        </td>
                        <td style="width:20px;">&nbsp;</td>
                        <td>
                          <a href="https://twitter.com/KASSU_11">
                            <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/twitter-circle-colored.png" alt="Tw" title="Twitter" width="32" height="32" style="display:block; border:0;" />
                          </a>
                        </td>
                        <td style="width:20px;">&nbsp;</td>
                        <td>
                          <a href="https://www.instagram.com/kasumexico">
                            <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/instagram-circle-colored.png" alt="Ig" title="Instagram" width="32" height="32" style="display:block; border:0;" />
                          </a>
                        </td>
                      </tr>
                    </table>
                  </td>
                  <tr>
                    <td align="center" style="font-family:Arial, sans-serif; font-size:14px; padding:0 10px;">
                      <p style="text-align:center; color:aliceblue;">
                        <font>© 2021 | Kasu Servicios a futuro<br> Bosque de Chapultepec, Pedregal 24, Molino del Rey<br> Ciudad de México, CDMX, Mexico C.P. 11000</font>
                        <a href="https://kasu.com.mx/index.php?Ml=4&Id={$Id}" style="color:#153643;"><font>Ya no quiero recibir este correo</font></a>
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
</html>
HTML;
                break;
            case 'ALTA DISTRIBUIDOR':
                $message = <<<HTML
<!DOCTYPE html>
<html lang="ES">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>KASU</title>
  <style type="text/css">
    @media only screen and (max-width:600px) {
      ul li, ol li, p { font-size:16px !important; line-height:150% !important; text-align:Justify !important; }
      h1 { font-size:18px !important; text-align:center; line-height:120% !important; }
      h1 a { font-size:15px !important; }
      *[class="gmail-fix"] { display:none !important; }
      a.es-button { font-size:25px !important; color:whitesmoke !important; display:block !important; text-decoration:none; text-align:center !important; border-left-width:0px !important; border-right-width:0px !important; }
      table.es-table-not-adapt, .esd-block-html table { width:auto !important; }
    }
    #outlook a { padding:0; }
    a[x-apple-data-detectors] { color:inherit !important; text-decoration:none !important; font-size:inherit !important; font-family:inherit !important; font-weight:inherit !important; line-height:inherit !important; }
  </style>
</head>
<body style="margin:0; padding:0;">
  <table align="center" width="600px" style="border:1px solid #cccccc; background-image:url(http://kasu.com.mx/assets/images/Correo/fondo.jpg); background-position:center top;">
    <tr>
      <td style="padding:20px 0 30px 0">
        <table align="center" width="100%" style="background-color:transparent;">
          <tr>
            <td align="center" style="padding:2px; font-size:25px; color:#153643; font-family:Arial, sans-serif;">
              <a href="https://kasu.com.mx" style="text-decoration:none; color:whitesmoke;">
                <img src="https://kasu.com.mx/assets/images/Correo/florredonda.png" alt="" width="100px" height="100px" style="display:block;" />
              </a>
            </td>
          </tr>
          <tr>
            <td bgcolor="#ffffff" align="justify" style="padding:20px 15px 5px 25px; font-family:Arial, sans-serif; font-size:16px; line-height:25px; color:#153643;">
              <b>¡ {$Cte} !</b><br/>
              <p>KASU te ha asignado el siguiente usuario para ingresar a nuestro sistema de ventas</p>
              <p><strong>{$DirUrl}</strong></p>
              <p>Para terminar tu proceso de alta como colaborador debes crear una Contraseña</p>
            </td>
          </tr>
          <tr>
            <td bgcolor="#ffffff" align="center" style="padding:30px;">
              <span style="border:solid #ee3a87; background:#ee3a87; border-width:20px; display:inline-block; border-radius:5px;">
                <a href="https://kasu.com.mx/login/index.php?data={$dirUrl1}&Usr={$imag1}" class="es-button" target="_blank" style="text-decoration:none; font-family:Arial, sans-serif; font-size:30px; color:#FFFFFF; background:#ee3a87; border-radius:5px; line-height:35px;">Crear Contraseña</a>
              </span>
            </td>
          </tr>
          <tr>
            <td bgcolor="#ffffff" align="justify" style="padding:5px 25px 30px 25px; font-family:Arial, sans-serif; font-size:12px; line-height:15px;">
              <p style="font-size:12px;">Recuerda guardar tu Usuario y Contraseña en un lugar seguro.<br/>Saludos<br>The Kasu Team</p>
            </td>
          </tr>
          <tr>
            <td style="padding:15px 30px;">
              <table align="center" width="100%">
                <tr>
                  <td align="center">
                    <table border="0" cellpadding="0" cellspacing="0">
                      <tr>
                        <td>
                          <a href="https://www.facebook.com/KasuMexico">
                            <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/facebook-circle-colored.png" alt="Fb" title="Facebook" width="32" height="32" style="display:block; border:0;" />
                          </a>
                        </td>
                        <td style="width:20px;">&nbsp;</td>
                        <td>
                          <a href="https://twitter.com/KASSU_11">
                            <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/twitter-circle-colored.png" alt="Tw" title="Twitter" width="32" height="32" style="display:block; border:0;" />
                          </a>
                        </td>
                        <td style="width:20px;">&nbsp;</td>
                        <td>
                          <a href="https://www.instagram.com/kasumexico">
                            <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/instagram-circle-colored.png" alt="Ig" title="Instagram" width="32" height="32" style="display:block; border:0;" />
                          </a>
                        </td>
                      </tr>
                    </table>
                  </td>
                  <tr>
                    <td align="center" style="font-family:Arial, sans-serif; font-size:14px; padding:0 10px;">
                      <p style="text-align:center; color:aliceblue;">
                        <font>© 2021 | Kasu Servicios a futuro<br>KASU desarrolla productos financieros para solventar momentos importantes en tu vida y la de los tuyos.</font>
                        <a href="https://kasu.com.mx/index.php?Ml=4&Id={$Id}" style="color:#153643;"><font>Ya no quiero recibir este correo</font></a>
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
</html>
HTML;
                break;
            case 'RESTABLECIMIENTO DE CONTRASEÑA':
                $message = <<<HTML
<!DOCTYPE html>
<html lang="ES">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>KASU</title>
  <style type="text/css">
    @media only screen and (max-width:600px) {
      ul li, ol li, p { font-size:16px !important; line-height:150% !important; text-align:Justify !important; }
      h1 { font-size:18px !important; text-align:center; line-height:120% !important; }
      h1 a { font-size:15px !important; }
      *[class="gmail-fix"] { display:none !important; }
      a.es-button { font-size:25px !important; color:#FFFFFF !important; display:block !important; text-decoration:none; text-align:center !important; border-left-width:0px !important; border-right-width:0px !important; }
      table.es-table-not-adapt, .esd-block-html table { width:auto !important; }
    }
    #outlook a { padding:0; }
    a[x-apple-data-detectors] { color:inherit !important; text-decoration:none !important; font-size:inherit !important; font-family:inherit !important; }
  </style>
</head>
<body style="margin:0; padding:0;">
  <table align="center" width="600px" style="border:1px solid #cccccc; background-image:url(http://kasu.com.mx/assets/images/Correo/fondo.jpg); background-position:center top;">
    <tr>
      <td style="padding:20px 0 30px 0">
        <table align="center" width="100%" style="background-color:transparent;">
          <tr>
            <td align="center" style="padding:2px; font-size:25px; color:#153643; font-family:Arial, sans-serif;">
              <a href="https://kasu.com.mx" style="text-decoration:none; color:whitesmoke;">
                <img src="https://kasu.com.mx/assets/images/Correo/florredonda.png" alt="" width="100px" height="100px" style="display:block;" />
              </a>
            </td>
          </tr>
          <tr>
            <td bgcolor="#ffffff" align="justify" style="padding:20px 15px 5px 25px; font-family:Arial, sans-serif; font-size:16px; line-height:25px; color:#153643;">
              <b>{$Cte}<br></b>
            </td>
          </tr>
          <tr style="border-collapse:collapse;">
            <td align="left" bgcolor="#ffffff" style="padding:10px 30px 5px 30px;">
              <p style="font-size:16px; font-family:Arial, sans-serif; line-height:24px; color:#666666;">Ingresa el siguiente código de verificación para actualizar tus datos</p><br>
              <b><p style="font-size:16px; font-family:Arial, sans-serif; line-height:24px; color:#666666; text-align:center;">{$DirUrl}</p></b>
            </td>
          </tr>
          <tr>
            <td bgcolor="#ffffff" align="center" style="padding:30px;">
              <span style="border:solid #ee3a87; background:#ee3a87; border-width:20px; display:inline-block; border-radius:5px;">
                <a href="{$DirUrl}" class="es-button" target="_blank" style="text-decoration:none; font-family:Arial, sans-serif; font-size:30px; color:#FFFFFF; background:#ee3a87; border-radius:5px; line-height:35px;">Cambiar Contraseña</a>
              </span>
            </td>
          </tr>
          <tr>
            <td bgcolor="#ffffff" align="justify" style="padding:5px 25px 30px 25px; font-family:Arial, sans-serif; font-size:12px; line-height:15px;">
              <p style="font-size:12px;">Si no hiciste esta petición, ignora este correo.<br>Saludos<br>The Kasu Team</p>
            </td>
          </tr>
          <tr>
            <td style="padding:15px 30px;">
              <table align="center" width="100%">
                <tr>
                  <td align="center">
                    <table border="0" cellpadding="0" cellspacing="0">
                      <tr>
                        <td>
                          <a href="https://www.facebook.com/KasuMexico">
                            <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/facebook-circle-colored.png" alt="Fb" title="Facebook" width="32" height="32" style="display:block; border:0;" />
                          </a>
                        </td>
                        <td style="width:20px;">&nbsp;</td>
                        <td>
                          <a href="https://twitter.com/KASSU_11">
                            <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/twitter-circle-colored.png" alt="Tw" title="Twitter" width="32" height="32" style="display:block; border:0;" />
                          </a>
                        </td>
                        <td style="width:20px;">&nbsp;</td>
                        <td>
                          <a href="https://www.instagram.com/kasumexico">
                            <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/instagram-circle-colored.png" alt="Ig" title="Instagram" width="32" height="32" style="display:block; border:0;" />
                          </a>
                        </td>
                      </tr>
                    </table>
                  </td>
                  <tr>
                    <td align="center" style="font-family:Arial, sans-serif; font-size:14px; padding:0 10px;">
                      <p style="text-align:center; color:aliceblue;">
                        <font>© 2021 | Kasu Servicios a futuro<br> Bosque de Chapultepec, Pedregal 24, Molino del Rey<br> Ciudad de México, CDMX, Mexico C.P. 11000</font>
                        <a href="https://kasu.com.mx/index.php?Ml=4&Id={$Id}" style="color:#153643;"><font>Ya no quiero recibir este correo</font></a>
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
</html>
HTML;
                break;
            case 'CONOCENOS UN POCO MÁS':
                $message = <<<HTML
<body style="margin:0; padding:0;">
  <table align="center" width="600px" style="border:1px solid #cccccc; background-position:center center; background-image:url(http://kasu.com.mx/assets/images/Correo/fondo.jpg);">
    <tr>
      <td style="padding:20px 0 30px 0">
        <table align="center" width="100%" style="background-color:transparent;">
          <tr>
            <td align="center" style="padding:30px 2px; background-repeat:no-repeat; color:#153643; font-family:Arial, sans-serif; font-size:25px;">
              <a href="https://kasu.com.mx" style="text-decoration:none; color:white;">
                <img src="https://kasu.com.mx/assets/images/Correo/florredonda.png" alt="Logo" width="60px" height="60px" style="display:block;" /><br><b>{$Cte} conoce KASU a fondo</b>
              </a>
            </td>
          </tr>
          <tr>
            <td bgcolor="#ffffff" align="center" style="padding:5px; font-family:Arial, sans-serif; font-size:14px;">
              <div style="padding:20px; color:#153643; font-family:Arial, sans-serif; font-size:16px; line-height:25px;">
                <p>¿Qué es un fideicomiso? Es un contrato en el cual delegas bienes a una entidad para que los administre en beneficio de un tercero.</p>
              </div>
              <a href="{$dirUrl1}" style="padding:5px;" download>
                <img src="https://kasu.com.mx/assets/images/Correo/fideicomiso1.png" alt="Consultar Fideicomiso" width="580px" height="50px" style="display:block;" />
              </a>
            </td>
          </tr>
          <tr>
            <td bgcolor="#ffffff" align="center" style="padding:5px; font-family:Arial, sans-serif; font-size:14px;">
              <div style="padding:20px; color:#153643; font-family:Arial, sans-serif; font-size:16px; line-height:25px;">
                <p>Si eres de los que teme las letras pequeñas, haz clic para ver todas las aclaraciones legales de KASU.</p>
              </div>
              <a href="{$imag1}?Ser={$Titulo1}" style="padding:5px;">
                <img src="https://kasu.com.mx/assets/images/Correo/letritas.png" alt="Ver Letras Pequeñas" width="580px" height="50px" style="display:block;" />
              </a>
            </td>
          </tr>
          <tr>
            <td bgcolor="#ffffff" align="center" style="padding:5px; font-family:Arial, sans-serif; font-size:14px;">
              <div style="padding:20px; color:#153643; font-family:Arial, sans-serif; font-size:16px; line-height:25px;">
                <p>¿Tienes dudas? Agenda una cita con un ejecutivo para recibir toda la información legal de KASU.</p>
              </div>
              <a href="{$Desc1}" style="padding:5px;">
                <img src="https://kasu.com.mx/assets/images/Correo/llamada3.png" alt="Agendar Cita" width="580px" height="50px" style="display:block;" />
              </a>
            </td>
          </tr>
          <tr>
            <td bgcolor="#ffffff" align="center" style="padding:5px; font-family:Arial, sans-serif; font-size:14px;">
              <div style="padding:20px; color:#153643; font-family:Arial, sans-serif; font-size:16px; line-height:25px;">
                <p>Te brindamos garantía en nuestros productos para cuidar a quienes más amas.</p>
              </div>
              <a href="{$dirUrl2}" style="padding:5px;">
                <img src="https://kasu.com.mx/assets/images/Correo/convencido1.png" alt="Me Han Convencido" width="400px" height="90px" style="display:block;" />
              </a>
            </td>
          </tr>
          <tr>
            <td bgcolor="#ffffff" align="center" style="padding:5px; font-family:Arial, sans-serif; font-size:14px; line-height:20px;">
              <p>Saludos: Atención a cliente KASU.</p>
            </td>
          </tr>
          <tr>
            <td style="padding:15px 30px;">
              <table align="center" width="100%">
                <tr>
                  <td align="center" style="font-family:Arial, sans-serif; font-size:14px; padding:10px;">
                    <p style="text-align:center; color:aliceblue;">
                      <font>© 2021 | Kasu Servicios a futuro<br> Kasu desarrolla productos financieros para solventar momentos importantes en tu vida.</font>
                      <a href="https://kasu.com.mx/index.php?Ml=4&Id={$Id}" style="color:#153643;"><font>Ya no quiero recibir este correo</font></a>
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
</html>
HTML;
                break;
            case 'FICHAS DE PAGO KASU':
                $message = <<<HTML
<!DOCTYPE html>
<html lang="ES">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>KASU</title>
  <style type="text/css">
    @media only screen and (max-width:600px){
      ul li, ol li, p { font-size:14px !important; line-height:150% !important; text-align:Justify !important; }
      h1 { font-size:16px !important; text-align:center; line-height:120% !important; }
      a.es-button { font-size:20px !important; color:#FFFFFF !important; display:block !important; text-decoration:none; text-align:center !important; }
      table.es-table-not-adapt, .esd-block-html table { width:auto !important; }
    }
    #outlook a { padding:0; }
    a[x-apple-data-detectors] { color:inherit !important; text-decoration:none !important; font-size:inherit !important; font-family:inherit !important; }
  </style>
</head>
<body style="margin:0; padding:0;">
  <table align="center" width="600px" style="border:1px solid #cccccc; background-image:url(http://kasu.com.mx/assets/images/Correo/fondo.jpg);">
    <tr>
      <td style="padding:20px 0 30px 0">
        <table align="center" width="100%" style="background-color:transparent;">
          <tr>
            <td align="center" style="font-size:25px; color:#153643; font-family:Arial, sans-serif;">
              <a href="https://kasu.com.mx" style="text-decoration:none; color:white;">
                <img src="https://kasu.com.mx/assets/images/Correo/florredonda.png" alt="" style="display:block;" />
              </a>
            </td>
          </tr>
          <tr>
            <td bgcolor="#ffffff" align="justify" style="padding:20px 15px 5px 25px; font-family:Arial, sans-serif; font-size:16px; line-height:25px; color:#153643;">
              <b>{$Cte}</b><br/>
              <p>Para no olvidar tus fechas de pago te enviamos tus fichas. Da clic en el botón para descargarlas.</p>
            </td>
          </tr>
          <tr>
            <td bgcolor="#ffffff" align="center" style="padding:30px;">
              <span style="border:solid #ee3a87; background:#ee3a87; border-width:20px; display:inline-block; border-radius:5px;">
                <a href="https://kasu.com.mx/login/Generar_PDF/Fichas_Pago_pdf.php?Cte={$DirUrl}&data={$dirUrl1}" class="es-button" target="_blank" style="text-decoration:none; font-family:Arial, sans-serif; font-size:25px; color:#FFFFFF; background:#ee3a87; border-radius:5px; line-height:35px;">Descargar ahora</a>
              </span>
            </td>
          </tr>
          <tr>
            <td bgcolor="#ffffff" align="justify" style="padding:5px 25px 30px 25px; font-family:Arial, sans-serif; font-size:12px; line-height:15px;">
              <p style="font-size:12px;">Para cualquier duda o asesoría sobre tus fichas de pago, llámanos: <b>7125975763</b></p>
            </td>
          </tr>
          <tr>
            <td style="padding:15px 30px;">
              <table align="center" width="100%">
                <tr>
                  <td align="center">
                    <table border="0" cellpadding="0" cellspacing="0">
                      <tr>
                        <td>
                          <a href="https://www.facebook.com/KasuMexico">
                            <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/facebook-circle-colored.png" alt="Fb" title="Facebook" width="32" height="32" style="display:block; border:0;" />
                          </a>
                        </td>
                        <td style="width:20px;">&nbsp;</td>
                        <td>
                          <a href="https://twitter.com/KASSU_11">
                            <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/twitter-circle-colored.png" alt="Tw" title="Twitter" width="32" height="32" style="display:block; border:0;" />
                          </a>
                        </td>
                        <td style="width:20px;">&nbsp;</td>
                        <td>
                          <a href="https://www.instagram.com/kasumexico">
                            <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/instagram-circle-colored.png" alt="Ig" title="Instagram" width="32" height="32" style="display:block; border:0;" />
                          </a>
                        </td>
                      </tr>
                    </table>
                  </td>
                  <tr>
                    <td align="center" style="font-family:Arial, sans-serif; font-size:14px; padding:0 10px;">
                      <p style="text-align:center; color:aliceblue;">
                        <font>© 2021 | Kasu Servicios a futuro<br> Bosque de Chapultepec, Pedregal 24, Molino del Rey<br> Ciudad de México, CDMX, Mexico C.P. 11000</font>
                        <a href="https://kasu.com.mx/index.php?Ml=4&Id={$Id}" style="color:#153643;"><font>Ya no quiero recibir este correo</font></a>
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
</html>
HTML;
                break;
            case 'ALTA DE COLABORADOR':
                $message = <<<HTML
<!DOCTYPE html>
<html lang="ES">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>KASU</title>
  <style type="text/css">
    @media only screen and (max-width:600px) {
      ul li, ol li, p { font-size:16px !important; line-height:150% !important; text-align:Justify !important; }
      h1 { font-size:18px !important; text-align:center; line-height:120% !important; }
      h1 a { font-size:15px !important; }
      *[class="gmail-fix"] { display:none !important; }
      a.es-button { font-size:25px !important; color:whitesmoke !important; display:block !important; text-decoration:none; text-align:center !important; border-left-width:0px !important; border-right-width:0px !important; }
      table.es-table-not-adapt, .esd-block-html table { width:auto !important; }
    }
    #outlook a { padding:0; }
    a[x-apple-data-detectors] { color:inherit !important; text-decoration:none !important; font-size:inherit !important; font-family:inherit !important; }
  </style>
</head>
<body style="margin:0; padding:0;">
  <table align="center" width="600px" style="border:1px solid #cccccc; background-image:url(http://kasu.com.mx/assets/images/Correo/fondo.jpg); background-position:center top;">
    <tr>
      <td style="padding:20px 0 30px 0">
        <table align="center" width="100%" style="background-color:transparent;">
          <tr>
            <td align="center" style="padding:2px; font-size:25px; color:#153643; font-family:Arial, sans-serif;">
              <a href="https://kasu.com.mx" style="text-decoration:none; color:whitesmoke;">
                <img src="https://kasu.com.mx/assets/images/Correo/florredonda.png" alt="" width="100px" height="100px" style="display:block;" />
              </a>
            </td>
          </tr>
          <tr>
            <td bgcolor="#ffffff" align="justify" style="padding:20px 15px 5px 25px; font-family:Arial, sans-serif; font-size:16px; line-height:25px; color:#153643;">
              <b>¡ {$Cte} !</b><br/>
              <p>KASU te ha asignado el siguiente usuario para ingresar a nuestro sistema de ventas</p>
              <p><strong>{$DirUrl}</strong></p>
              <p>Para terminar tu proceso de alta como colaborador debes crear una Contraseña</p>
            </td>
          </tr>
          <tr>
            <td bgcolor="#ffffff" align="center" style="padding:30px;">
              <span style="border:solid #ee3a87; background:#ee3a87; border-width:20px; display:inline-block; border-radius:5px;">
                <a href="https://kasu.com.mx/login/index.php?data={$dirUrl1}&Usr={$imag1}" class="es-button" target="_blank" style="text-decoration:none; font-family:Arial, sans-serif; font-size:30px; color:#FFFFFF; background:#ee3a87; border-radius:5px; line-height:35px;">Crear Contraseña</a>
              </span>
            </td>
          </tr>
          <tr>
            <td bgcolor="#ffffff" align="justify" style="padding:5px 25px 30px 25px; font-family:Arial, sans-serif; font-size:12px; line-height:15px;">
              <p style="font-size:12px;">Recuerda guardar tu Usuario y Contraseña en un lugar seguro.<br/>Saludos<br>The Kasu Team</p>
            </td>
          </tr>
          <tr>
            <td style="padding:15px 30px;">
              <table align="center" width="100%">
                <tr>
                  <td align="center">
                    <table border="0" cellpadding="0" cellspacing="0">
                      <tr>
                        <td>
                          <a href="https://www.facebook.com/KasuMexico">
                            <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/facebook-circle-colored.png" alt="Fb" title="Facebook" width="32" height="32" style="display:block; border:0;" />
                          </a>
                        </td>
                        <td style="width:20px;">&nbsp;</td>
                        <td>
                          <a href="https://twitter.com/KASSU_11">
                            <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/twitter-circle-colored.png" alt="Tw" title="Twitter" width="32" height="32" style="display:block; border:0;" />
                          </a>
                        </td>
                        <td style="width:20px;">&nbsp;</td>
                        <td>
                          <a href="https://www.instagram.com/kasumexico">
                            <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/instagram-circle-colored.png" alt="Ig" title="Instagram" width="32" height="32" style="display:block; border:0;" />
                          </a>
                        </td>
                      </tr>
                    </table>
                  </td>
                  <tr>
                    <td align="center" style="font-family:Arial, sans-serif; font-size:14px; padding:0 10px;">
                      <p style="text-align:center; color:aliceblue;">
                        <font>© 2021 | Kasu Servicios a futuro<br>KASU desarrolla productos financieros para solventar momentos importantes en tu vida y la de los tuyos.</font>
                        <a href="https://kasu.com.mx/index.php?Ml=4&Id={$Id}" style="color:#153643;"><font>Ya no quiero recibir este correo</font></a>
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
</html>
HTML;
                break;
            case 'RESTABLECIMIENTO DE DATOS':
                $message = <<<HTML
<!DOCTYPE html>
<html lang="ES">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>KASU</title>
  <style type="text/css">
    @media only screen and (max-width:600px) {
      ul li, ol li, p { font-size:16px !important; line-height:150% !important; text-align:Justify !important; }
      h1 { font-size:18px !important; text-align:Justify; line-height:120% !important; }
      h1 a { font-size:18px !important; }
      *[class="gmail-fix"] { display:none !important; }
      a.es-button { font-size:35px !important; color:#FFFFFF !important; display:block !important; text-decoration:none; text-align:center !important; border-left-width:0px !important; border-right-width:0px !important; }
      table.es-table-not-adapt, .esd-block-html table { width:auto !important; }
    }
    #outlook a { padding:0; }
    a[x-apple-data-detectors] { color:inherit !important; text-decoration:none !important; font-size:inherit !important; font-family:inherit !important; }
  </style>
</head>
<body style="margin:0; padding:0;">
  <table align="center" width="600px" style="border:1px solid #cccccc; background-image:url(http://kasu.com.mx/assets/images/Correo/fondo.jpg); background-position:center top;">
    <tr>
      <td style="padding:20px 0 30px 0">
        <table align="center" width="100%" style="background-color:transparent;">
          <tr>
            <td align="center" style="padding:2px; font-size:25px; color:#153643; font-family:Arial, sans-serif;">
              <a href="https://kasu.com.mx" style="text-decoration:none; color:whitesmoke;">
                <img src="https://kasu.com.mx/assets/images/Correo/florredonda.png" alt="" width="100px" height="100px" style="display:block;" />
              </a>
            </td>
          </tr>
          <tr>
            <td bgcolor="#ffffff" align="justify" style="padding:20px 15px 5px 25px; font-family:Arial, sans-serif; font-size:16px; line-height:25px; color:#153643;">
              <b>{$Cte}<br></b>
            </td>
          </tr>
          <tr style="border-collapse:collapse;">
            <td align="left" bgcolor="#ffffff" style="padding:10px 30px 5px 30px;">
              <p style="font-size:16px; font-family:Arial, sans-serif; line-height:24px; color:#666666;">Ingresa el siguiente enlace para verificar los datos actualizados de tu cuenta</p><br>
              <b><p style="font-size:16px; font-family:Arial, sans-serif; line-height:24px; color:#666666; text-align:center;">{$DirUrl}?value={$dirUrl1}</p></b>
            </td>
          </tr>
          <tr>
            <td bgcolor="#ffffff" align="center" style="padding:30px;">
              <span style="border:solid #ee3a87; background:#ee3a87; border-width:20px; display:inline-block; border-radius:5px;">
                <a href="{$DirUrl}?value={$dirUrl1}" class="es-button" target="_blank" style="text-decoration:none; font-family:Arial, sans-serif; font-size:30px; color:#FFFFFF; background:#ee3a87; border-radius:5px; line-height:35px;">Verificar</a>
              </span>
            </td>
          </tr>
          <tr>
            <td bgcolor="#ffffff" align="justify" style="padding:5px 25px 30px 25px; font-family:Arial, sans-serif; font-size:14px; line-height:20px;">
              <p>¿No reconoces esta actividad? Haz clic <a href="https://kasu.com.mx" style="text-decoration:none; color:cornflowerblue;"><b>aquí</b></a> para más información sobre cómo cancelar esta modificación. Atención al Cliente</p>
            </td>
          </tr>
          <tr>
            <td style="padding:15px 30px;">
              <table align="center" width="100%">
                <tr>
                  <td align="center">
                    <table border="0" cellpadding="0" cellspacing="0">
                      <tr>
                        <td>
                          <a href="https://www.facebook.com/KasuMexico">
                            <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/facebook-circle-colored.png" alt="Fb" title="Facebook" width="32" height="32" style="display:block; border:0;" />
                          </a>
                        </td>
                        <td style="width:20px;">&nbsp;</td>
                        <td>
                          <a href="https://twitter.com/KASSU_11">
                            <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/twitter-circle-colored.png" alt="Tw" title="Twitter" width="32" height="32" style="display:block; border:0;" />
                          </a>
                        </td>
                        <td style="width:20px;">&nbsp;</td>
                        <td>
                          <a href="https://www.instagram.com/kasumexico">
                            <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/instagram-circle-colored.png" alt="Ig" title="Instagram" width="32" height="32" style="display:block; border:0;" />
                          </a>
                        </td>
                      </tr>
                    </table>
                  </td>
                  <tr>
                    <td align="center" style="font-family:Arial, sans-serif; font-size:14px; padding:0 10px;">
                      <p style="text-align:center; color:aliceblue;">
                        <font>© 2021 | Kasu Servicios a futuro<br> KASU desarrolla productos financieros para solventar momentos importantes en tu vida y la de los tuyos.</font>
                        <a href="https://kasu.com.mx/index.php?Ml=4&Id={$Id}" style="color:#153643;"><font>Ya no quiero recibir este correo</font></a>
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
</html>
HTML;
                break;
            case 'CITA TELEFONICA':
                $message = <<<HTML
<!DOCTYPE html>
<html lang="ES">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>KASU</title>
  <style type="text/css">
    @media only screen and (max-width:600px){
      ul li, ol li, p { font-size:18px !important; line-height:150% !important; text-align:Justify !important; }
      h1 { font-size:20px !important; text-align:Justify; line-height:120% !important; }
      h1 a { font-size:18px !important; }
      *[class="gmail-fix"] { display:none !important; }
      a.es-button { font-size:35px !important; color:#FFFFFF !important; display:block !important; text-decoration:none; text-align:center !important; border-left-width:0px !important; border-right-width:0px !important; }
      table.es-table-not-adapt, .esd-block-html table { width:auto !important; }
    }
    #outlook a { padding:0; }
    a[x-apple-data-detectors] { color:inherit !important; text-decoration:none !important; font-size:inherit !important; font-family:inherit !important; }
  </style>
</head>
<body style="margin:0; padding:0;">
  <table align="center" width="600px" style="border:1px solid #cccccc; background-image:url(http://kasu.com.mx/assets/images/Correo/fondo.jpg); background-position:center center;">
    <tr>
      <td style="padding:20px 0 30px 0">
        <table align="center" width="100%" style="background-color:transparent;">
          <tr>
            <td align="center" style="padding:30px; font-size:25px; color:#153643; font-family:Arial, sans-serif;">
              <a href="https://kasu.com.mx" style="text-decoration:none; color:white;">
                <img src="https://kasu.com.mx/assets/images/Correo/florredonda.png" alt="" width="80px" height="80px" style="display:block;" /><br><b>{$Cte} tu cita esta lista</b>
              </a>
            </td>
          </tr>
          <tr>
            <td bgcolor="#ffffff" align="justify" style="padding:20px 15px 5px 25px; font-family:Arial, sans-serif; font-size:16px; line-height:25px; color:#153643;">
              <b></b>
            </td>
          </tr>
          <tr style="border-collapse:collapse;">
            <td align="justify" bgcolor="#ffffff" style="padding:10px 30px 5px 30px;">
              <p style="font-size:16px; font-family:arial, sans-serif; line-height:24px; color:#666666;">¡Ya agendamos tu cita telefónica para ponernos en contacto contigo!<br> Corroboramos tus datos</p><br />
            </td>
          </tr>
          <tr>
            <td style="padding:15px 50px;" bgcolor="#ffffff" align="center">
              <table align="center" width="80%" style="background-color:transparent; box-shadow:0 4px 8px 0 rgba(0,0,255,0.3), 0 6px 20px 0 rgba(0,0,255,0.5);">
                <tr>
                  <td align="justify" width="20%" style="padding:5px;">Nombre:</td>
                  <td align="justify" width="50%" style="padding:5px;">{$Cte}</td>
                </tr>
                <tr>
                  <td align="justify" width="20%" style="padding:5px;">Telefono:</td>
                  <td align="justify" width="50%" style="padding:5px;">{$DirUrl}</td>
                </tr>
                <tr>
                  <td align="justify" width="20%" style="padding:5px;">Dia y Hora:</td>
                  <td align="justify" width="50%" style="padding:5px;">{$dirUrl1}</td>
                </tr>
              </table>
            </td>
          </tr>
          <tr>
            <td bgcolor="#ffffff" align="justify" style="padding:5px 25px 30px 25px; font-family:Arial, sans-serif; font-size:16px; line-height:20px;">
              <p style="font-size:14px; font-family:arial, sans-serif; line-height:24px; color:#666666;"><br/>El equipo de atención a clientes de KASU agradece tu interés, Saludos</p>
            </td>
          </tr>
          <tr>
            <td style="padding:15px 30px;">
              <table align="center" width="100%">
                <tr>
                  <td align="center">
                    <table border="0" cellpadding="0" cellspacing="0">
                      <tr>
                        <td>
                          <a href="https://www.facebook.com/KasuMexico">
                            <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/facebook-circle-colored.png" alt="Fb" title="Facebook" width="32" height="32" style="display:block; border:0;" />
                          </a>
                        </td>
                        <td style="width:20px;">&nbsp;</td>
                        <td>
                          <a href="https://twitter.com/KASSU_11">
                            <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/twitter-circle-colored.png" alt="Tw" title="Twitter" width="32" height="32" style="display:block; border:0;" />
                          </a>
                        </td>
                        <td style="width:20px;">&nbsp;</td>
                        <td>
                          <a href="https://www.instagram.com/kasumexico">
                            <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/instagram-circle-colored.png" alt="Ig" title="Instagram" width="32" height="32" style="display:block; border:0;" />
                          </a>
                        </td>
                      </tr>
                    </table>
                  </td>
                  <tr>
                    <td align="center" style="font-family:Arial, sans-serif; font-size:14px; padding:0 10px;">
                      <p style="text-align:center; color:aliceblue;">
                        <font>© 2021 | Kasu Servicios a futuro<br>KASU desarrolla productos financieros para solventar momentos importantes en tu vida y la de los tuyos.</font>
                        <a href="https://kasu.com.mx/index.php?Ml=4&Id={$Id}" style="color:#153643;"><font>Ya no quiero recibir este correo</font></a>
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
</html>
HTML;
                break;
            case 'Felices Fiestas':
                $message = <<<HTML
<!DOCTYPE html>
<html lang="ES">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>KASU</title>
  <style type="text/css">
    @media only screen and (max-width:600px) {
      ul li, ol li, p { font-size:18px !important; line-height:150% !important; text-align:Justify !important; }
      h1 { font-size:20px !important; text-align:Justify; line-height:120% !important; }
      h1 a { font-size:18px !important; }
      *[class="gmail-fix"] { display:none !important; }
      a.es-button { font-size:35px !important; color:#FFFFFF !important; display:block !important; text-decoration:none; text-align:center !important; border-left-width:0px !important; border-right-width:0px !important; }
      table.es-table-not-adapt, .esd-block-html table { width:auto !important; }
    }
    #outlook a { padding:0; }
    a[x-apple-data-detectors] { color:inherit !important; text-decoration:none !important; font-size:inherit !important; font-family:inherit !important; line-height:inherit !important; }
  </style>
</head>
<body style="margin:0; padding:0;">
  <table align="center" width="600px" style="border:1px solid #cccccc; background-image:url(http://kasu.com.mx/assets/images/Correo/fondo.jpg); background-position:center top;">
    <tr>
      <td style="padding:20px 0 30px 0">
        <table align="center" width="100%">
          <tr>
            <td align="center" style="font-size:25px; color:#153643; font-family:Arial, sans-serif;">
              <a href="https://kasu.com.mx/" style="text-decoration:none; color:white;">
                <img src="https://kasu.com.mx/assets/images/kasu_logo.jpeg" alt="" width="100px" height="100px" style="display:block;" />
              </a>
            </td>
          </tr>
          <tr>
            <td bgcolor="#ffffff" align="center" style="padding:20px;">
              <div style="text-align:center">
                <b>Hola {$Cte}</b><br>
                <b>“El compromiso con nuestros clientes crece año tras año, su confianza es nuestro mayor valor”</b>
              </div>
            </td>
          </tr>
          <tr style="border-collapse:collapse;">
            <td align="left" bgcolor="#ffffff" style="padding:10px 30px 5px 30px;">
              <p style="font-size:16px; font-family:Arial, sans-serif; line-height:24px; color:#666666;">
                En KASU estamos felices de contar con su preferencia, gracias por abrirnos las puertas de sus hogares y depositar en nosotros el mayor valor: su confianza. Les deseamos un nuevo año venturoso y lleno de amor.
              </p>
              <br>
              <div style="text-align:center">
                <p style="font-size:16px; font-family:Arial, sans-serif; color:Black;">¡Felices fiestas!</p>
              </div>
            </td>
          </tr>
          <tr style="border-collapse:collapse;">
            <td align="center" style="padding:15px;">
              <table align="center" width="100%">
                <tr>
                  <td align="center">
                    <table border="0" cellpadding="0" cellspacing="0">
                      <tr>
                        <td>
                          <a href="https://www.facebook.com/KasuMexico">
                            <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/facebook-circle-colored.png" alt="Fb" title="Facebook" width="32" height="32" style="display:block; border:0;" />
                          </a>
                        </td>
                        <td style="width:20px;">&nbsp;</td>
                        <td>
                          <a href="https://twitter.com/KASSU_11">
                            <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/twitter-circle-colored.png" alt="Tw" title="Twitter" width="32" height="32" style="display:block; border:0;" />
                          </a>
                        </td>
                        <td style="width:20px;">&nbsp;</td>
                        <td>
                          <a href="https://www.instagram.com/kasumexico">
                            <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/instagram-circle-colored.png" alt="Ig" title="Instagram" width="32" height="32" style="display:block; border:0;" />
                          </a>
                        </td>
                      </tr>
                    </table>
                  </td>
                  <tr>
                    <td align="center" style="font-family:Arial, sans-serif; font-size:14px; padding:0 10px;">
                      <p style="text-align:center; color:white;">
                        <font>© 2021 | Kasu Servicios a futuro<br> Kasu desarrolla productos financieros para solventar momentos importantes en tu vida.</font>
                        <a href="https://kasu.com.mx/index.php?Ml=4&Id={$Id}" style="color:#153643;"><font>Ya no quiero recibir este correo</font></a>
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
</html>
HTML;
                break;
            case 'PROCESO DE COMPRA DE SERVICIOS KASU':
                $message = <<<HTML
<!DOCTYPE html>
<html lang="ES">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>KASU</title>
  <style type="text/css">
    @media only screen and (max-width:600px) {
      ul li, ol li, p { font-size:20px !important; line-height:150% !important; text-align:Justify !important; }
    }
  </style>
</head>
<body style="margin:0; padding:0;">
  <table align="center" width="600px" style="border:1px solid #cccccc; background-image:url(http://kasu.com.mx/assets/images/Correo/fondo.jpg); background-position:top;">
    <tr>
      <td style="padding:20px 0 30px 0">
        <table align="center" width="100%" style="background-color:transparent;">
          <tr>
            <td align="center" style="font-size:25px; color:#153643; font-family:Arial, sans-serif;">
              <a href="https://kasu.com.mx" style="text-decoration:none; color:white;">
                <img src="https://kasu.com.mx/assets/images/Correo/florredonda.png" alt="" style="display:block;" />
              </a>
            </td>
          </tr>
          <tr>
            <td bgcolor="#ffffff" align="center" style="padding:20px; font-family:Arial, sans-serif; font-size:16px;">
              <br/>
              <div style="padding:20px; color:#153643; font-family:Arial, sans-serif; font-size:16px;">
                <p>¿Cómo adquirir mi póliza de servicios KASU? Es sencillo, solo sigue estos pasos:</p>
              </div>
            </td>
          </tr>
          <tr>
            <td align="center" style="padding:20px; font-family:Arial, sans-serif; font-size:16px;">
              <a href="{$DirUrl}" style="text-decoration:none;">
                <img src="https://kasu.com.mx/assets/images/Correo/GIFCOMPRA.gif" alt="Proceso de Compra" width="500px" height="280px" style="display:block;" />
              </a>
            </td>
          </tr>
          <tr>
            <td align="center" style="padding:15px; font-family:Arial, sans-serif; font-size:14px;">
              <a href="{$dirUrl1}" style="text-decoration:none;">
                <img src="https://kasu.com.mx/assets/images/Correo/contrata1.png" alt="Contratar" width="380px" height="90px" style="display:block;" />
              </a>
            </td>
          </tr>
          <tr>
            <td style="padding:15px;">
              <table align="center" width="100%">
                <tr>
                  <td align="center" style="font-family:Arial, sans-serif; font-size:14px; padding:10px;">
                    <p style="text-align:center;">
                      <font>© 2021 | Kasu Servicios a futuro<br> Kasu desarrolla productos financieros para solventar momentos importantes en tu vida.</font>
                      <a href="https://kasu.com.mx/index.php?Ml=4&Id={$Id}" style="color:#153643;"><font>Ya no quiero recibir este correo</font></a>
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
</html>
HTML;
                break;
            case 'ALTA DISTRIBUIDOR':
                $message = <<<HTML
<!DOCTYPE html>
<html lang="ES">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>KASU</title>
  <style type="text/css">
    @media only screen and (max-width:600px) {
      ul li, ol li, p { font-size:16px !important; line-height:150% !important; text-align:Justify !important; }
      h1 { font-size:18px !important; text-align:center; line-height:120% !important; }
      h1 a { font-size:15px !important; }
      *[class="gmail-fix"] { display:none !important; }
      a.es-button { font-size:25px !important; color:whitesmoke !important; display:block !important; text-decoration:none; text-align:center !important; border-left-width:0px !important; border-right-width:0px !important; }
      table.es-table-not-adapt, .esd-block-html table { width:auto !important; }
    }
    #outlook a { padding:0; }
    a[x-apple-data-detectors] { color:inherit !important; text-decoration:none !important; font-size:inherit !important; font-family:inherit !important; }
  </style>
</head>
<body style="margin:0; padding:0;">
  <table align="center" width="600px" style="border:1px solid #cccccc; background-image:url(http://kasu.com.mx/assets/images/Correo/fondo.jpg); background-position:center top;">
    <tr>
      <td style="padding:20px 0 30px 0">
        <table align="center" width="100%" style="background-color:transparent;">
          <tr>
            <td align="center" style="padding:2px; font-size:25px; color:#153643; font-family:Arial, sans-serif;">
              <a href="https://kasu.com.mx" style="text-decoration:none; color:whitesmoke;">
                <img src="https://kasu.com.mx/assets/images/Correo/florredonda.png" alt="" width="100px" height="100px" style="display:block;" />
              </a>
            </td>
          </tr>
          <tr>
            <td bgcolor="#ffffff" align="justify" style="padding:20px 15px 5px 25px; font-family:Arial, sans-serif; font-size:16px; line-height:25px; color:#153643;">
              <b>¡ {$Cte} !</b><br/>
              <p>KASU te ha asignado el siguiente usuario para ingresar a nuestro sistema de ventas</p>
              <p><strong>{$DirUrl}</strong></p>
              <p>Para terminar tu proceso de alta como colaborador debes crear una Contraseña</p>
            </td>
          </tr>
          <tr>
            <td bgcolor="#ffffff" align="center" style="padding:30px;">
              <span style="border:solid #ee3a87; background:#ee3a87; border-width:20px; display:inline-block; border-radius:5px;">
                <a href="https://kasu.com.mx/login/index.php?data={$dirUrl1}&Usr={$imag1}" class="es-button" target="_blank" style="text-decoration:none; font-family:Arial, sans-serif; font-size:30px; color:#FFFFFF; background:#ee3a87; border-radius:5px; line-height:35px;">Crear Contraseña</a>
              </span>
            </td>
          </tr>
          <tr>
            <td bgcolor="#ffffff" align="justify" style="padding:5px 25px 30px 25px; font-family:Arial, sans-serif; font-size:12px; line-height:15px;">
              <p style="font-size:12px;">Recuerda guardar tu Usuario y Contraseña en un lugar seguro.<br/>Saludos<br>The Kasu Team</p>
            </td>
          </tr>
          <tr>
            <td style="padding:15px 30px;">
              <table align="center" width="100%">
                <tr>
                  <td align="center">
                    <table border="0" cellpadding="0" cellspacing="0">
                      <tr>
                        <td>
                          <a href="https://www.facebook.com/KasuMexico">
                            <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/facebook-circle-colored.png" alt="Fb" title="Facebook" width="32" height="32" style="display:block; border:0;" />
                          </a>
                        </td>
                        <td style="width:20px;">&nbsp;</td>
                        <td>
                          <a href="https://twitter.com/KASSU_11">
                            <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/twitter-circle-colored.png" alt="Tw" title="Twitter" width="32" height="32" style="display:block; border:0;" />
                          </a>
                        </td>
                        <td style="width:20px;">&nbsp;</td>
                        <td>
                          <a href="https://www.instagram.com/kasumexico">
                            <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/instagram-circle-colored.png" alt="Ig" title="Instagram" width="32" height="32" style="display:block; border:0;" />
                          </a>
                        </td>
                      </tr>
                    </table>
                  </td>
                  <tr>
                    <td align="center" style="font-family:Arial, sans-serif; font-size:14px; padding:0 10px;">
                      <p style="text-align:center; color:aliceblue;">
                        <font>© 2021 | Kasu Servicios a futuro<br>KASU desarrolla productos financieros para solventar momentos importantes en tu vida y la de los tuyos.</font>
                        <a href="https://kasu.com.mx/index.php?Ml=4&Id={$Id}" style="color:#153643;"><font>Ya no quiero recibir este correo</font></a>
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
</html>
HTML;
                break;
            case 'RESTABLECIMIENTO DE CONTRASEÑA':
                // Ya se incluyó anteriormente, se omite aquí para evitar duplicidad.
                break;
            case 'CONOCENOS UN POCO MÁS':
                $message = <<<HTML
<body style="margin:0; padding:0;">
  <table align="center" width="600px" style="border:1px solid #cccccc; background-position:center center; background-image:url(http://kasu.com.mx/assets/images/Correo/fondo.jpg);">
    <tr>
      <td style="padding:20px 0 30px 0">
        <table align="center" width="100%" style="background-color:transparent;">
          <tr>
            <td align="center" style="padding:30px 2px; background-repeat:no-repeat; color:#153643; font-family:Arial, sans-serif; font-size:25px;">
              <a href="https://kasu.com.mx" style="text-decoration:none; color:white;">
                <img src="https://kasu.com.mx/assets/images/Correo/florredonda.png" alt="Logo" width="60px" height="60px" style="display:block;" /><br><b>{$Cte} conoce KASU a fondo</b>
              </a>
            </td>
          </tr>
          <tr>
            <td bgcolor="#ffffff" align="center" style="padding:5px; font-family:Arial, sans-serif; font-size:14px;">
              <div style="padding:20px; color:#153643; font-family:Arial, sans-serif; font-size:16px; line-height:25px;">
                <p>¿Qué es un fideicomiso? Es un contrato en el cual delegas bienes a una entidad para que los administre en beneficio de un tercero.</p>
              </div>
              <a href="{$dirUrl1}" style="padding:5px;" download>
                <img src="https://kasu.com.mx/assets/images/Correo/fideicomiso1.png" alt="Consultar Fideicomiso" width="580px" height="50px" style="display:block;" />
              </a>
            </td>
          </tr>
          <tr>
            <td bgcolor="#ffffff" align="center" style="padding:5px; font-family:Arial, sans-serif; font-size:14px;">
              <div style="padding:20px; color:#153643; font-family:Arial, sans-serif; font-size:16px; line-height:25px;">
                <p>Si temes las letras pequeñas, haz clic para ver todas las aclaraciones legales de KASU.</p>
              </div>
              <a href="{$imag1}?Ser={$Titulo1}" style="padding:5px;">
                <img src="https://kasu.com.mx/assets/images/Correo/letritas.png" alt="Ver Letras Pequeñas" width="580px" height="50px" style="display:block;" />
              </a>
            </td>
          </tr>
          <tr>
            <td bgcolor="#ffffff" align="center" style="padding:5px; font-family:Arial, sans-serif; font-size:14px;">
              <div style="padding:20px; color:#153643; font-family:Arial, sans-serif; font-size:16px; line-height:25px;">
                <p>Si deseas que un ejecutivo te contacte para resolver tus dudas y darte toda la información legal de KASU, da clic en SABER MÁS.</p>
              </div>
              <a href="{$Desc1}" style="padding:5px;">
                <img src="https://kasu.com.mx/assets/images/Correo/llamada3.png" alt="Agendar Cita" width="580px" height="50px" style="display:block;" />
              </a>
            </td>
          </tr>
          <tr>
            <td bgcolor="#ffffff" align="center" style="padding:5px; font-family:Arial, sans-serif; font-size:14px;">
              <div style="padding:20px; color:#153643; font-family:Arial, sans-serif; font-size:16px; line-height:25px;">
                <p>Te brindamos garantía en nuestros productos para cuidar a quienes más amas.</p>
              </div>
              <a href="{$dirUrl2}" style="padding:5px;">
                <img src="https://kasu.com.mx/assets/images/Correo/convencido1.png" alt="Me Han Convencido" width="400px" height="90px" style="display:block;" />
              </a>
            </td>
          </tr>
          <tr>
            <td bgcolor="#ffffff" align="center" style="padding:5px; font-family:Arial, sans-serif; font-size:14px;">
              <p>Saludos: Atención a cliente KASU.</p>
            </td>
          </tr>
          <tr>
            <td style="padding:15px;">
              <table align="center" width="100%">
                <tr>
                  <td align="center" style="font-family:Arial, sans-serif; font-size:14px; padding:10px;">
                    <p style="text-align:center; color:aliceblue;">
                      <font>© 2021 | Kasu Servicios a futuro<br>Kasu desarrolla productos financieros para solventar momentos importantes en tu vida.</font>
                      <a href="https://kasu.com.mx/index.php?Ml=4&Id={$Id}" style="color:#153643;"><font>Ya no quiero recibir este correo</font></a>
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
</html>
HTML;
                break;
            case 'FICHAS DE PAGO KASU':  // Si se repite, se omite duplicado.
                break;
            case 'ALTA DISTRIBUIDOR':
                // Caso ya incluido anteriormente.
                break;
            case 'RESTABLECIMIENTO DE DATOS':
                $message = <<<HTML
<!DOCTYPE html>
<html lang="ES">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>KASU</title>
  <style type="text/css">
    @media only screen and (max-width:600px) {
      ul li, ol li, p { font-size:16px !important; line-height:150% !important; text-align:Justify !important; }
      h1 { font-size:18px !important; text-align:Justify; line-height:120% !important; }
      h1 a { font-size:18px !important; }
      *[class="gmail-fix"] { display:none !important; }
      a.es-button { font-size:35px !important; color:#FFFFFF !important; display:block !important; text-decoration:none; text-align:center !important; border-left-width:0px !important; border-right-width:0px !important; }
      table.es-table-not-adapt, .esd-block-html table { width:auto !important; }
    }
    #outlook a { padding:0; }
    a[x-apple-data-detectors] { color:inherit !important; text-decoration:none !important; font-size:inherit !important; font-family:inherit !important; }
  </style>
</head>
<body style="margin:0; padding:0;">
  <table align="center" width="600px" style="border:1px solid #cccccc; background-image:url(http://kasu.com.mx/assets/images/Correo/fondo.jpg); background-position:center top;">
    <tr>
      <td style="padding:20px 0 30px 0">
        <table align="center" width="100%">
          <tr>
            <td align="center" style="padding:2px; font-size:25px; color:#153643; font-family:Arial, sans-serif;">
              <a href="https://kasu.com.mx" style="text-decoration:none; color:whitesmoke;">
                <img src="https://kasu.com.mx/assets/images/Correo/florredonda.png" alt="" width="100px" height="100px" style="display:block;" />
              </a>
            </td>
          </tr>
          <tr>
            <td bgcolor="#ffffff" align="justify" style="padding:20px 15px 5px 25px; font-family:Arial, sans-serif; font-size:16px; line-height:25px; color:#153643;">
              <b>{$Cte}<br></b>
            </td>
          </tr>
          <tr style="border-collapse:collapse;">
            <td align="left" bgcolor="#ffffff" style="padding:10px 30px 5px 30px;">
              <p style="font-size:16px; font-family:Arial, sans-serif; line-height:24px; color:#666666;">
                Ingresa el siguiente enlace para verificar los datos actualizados de tu cuenta
              </p><br>
              <b>
                <p style="font-size:16px; font-family:Arial, sans-serif; line-height:24px; color:#666666; text-align:center;">{$DirUrl}?value={$dirUrl1}</p>
              </b>
            </td>
          </tr>
          <tr>
            <td bgcolor="#ffffff" align="center" style="padding:30px;">
              <span style="border:solid #ee3a87; background:#ee3a87; border-width:20px; display:inline-block; border-radius:5px;">
                <a href="{$DirUrl}?value={$dirUrl1}" class="es-button" target="_blank" style="text-decoration:none; font-family:Arial, sans-serif; font-size:30px; color:#FFFFFF; background:#ee3a87; border-radius:5px; line-height:35px;">Verificar</a>
              </span>
            </td>
          </tr>
          <tr>
            <td bgcolor="#ffffff" align="justify" style="padding:5px 25px 30px 25px; font-family:Arial, sans-serif; font-size:14px; line-height:20px;">
              <p>¿No reconoces esta actividad? Haz clic <a href="https://kasu.com.mx" style="text-decoration:none; color:cornflowerblue;"><b>aquí</b></a> para obtener más información sobre cómo cancelar esta modificación. Atención al Cliente</p>
            </td>
          </tr>
          <tr>
            <td style="padding:15px 30px;">
              <table align="center" width="100%">
                <tr>
                  <td align="center">
                    <table border="0" cellpadding="0" cellspacing="0">
                      <tr>
                        <td>
                          <a href="https://www.facebook.com/KasuMexico">
                            <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/facebook-circle-colored.png" alt="Fb" title="Facebook" width="32" height="32" style="display:block; border:0;" />
                          </a>
                        </td>
                        <td style="width:20px;">&nbsp;</td>
                        <td>
                          <a href="https://twitter.com/KASSU_11">
                            <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/twitter-circle-colored.png" alt="Tw" title="Twitter" width="32" height="32" style="display:block; border:0;" />
                          </a>
                        </td>
                        <td style="width:20px;">&nbsp;</td>
                        <td>
                          <a href="https://www.instagram.com/kasumexico">
                            <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/instagram-circle-colored.png" alt="Ig" title="Instagram" width="32" height="32" style="display:block; border:0;" />
                          </a>
                        </td>
                      </tr>
                    </table>
                  </td>
                  <tr>
                    <td align="center" style="font-family:Arial, sans-serif; font-size:14px; padding:0 10px;">
                      <p style="text-align:center; color:aliceblue;">
                        <font>© 2021 | Kasu Servicios a futuro<br>KASU desarrolla productos financieros para solventar momentos importantes en tu vida y la de los tuyos.</font>
                        <a href="https://kasu.com.mx/index.php?Ml=4&Id={$Id}" style="color:#153643;"><font>Ya no quiero recibir este correo</font></a>
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
</html>
HTML;
                break;
            case 'AGENDAR CITA':
                $message = <<<HTML
<body style="margin:0; padding:0;">
  <table align="center" width="600px" style="border:1px solid #cccccc; background-position:center center; background-image:url(http://kasu.com.mx/assets/images/Correo/fondo.jpg);">
    <tr>
      <td style="padding:20px 0 30px 0">
        <table align="center" width="100%" style="background-color:transparent;">
          <tr>
            <td align="center" style="padding:30px 2px; background-repeat:no-repeat; color:#153643; font-family:Arial, sans-serif; font-size:25px;">
              <a href="https://kasu.com.mx" style="text-decoration:none; color:whitesmoke;">
                <img src="https://kasu.com.mx/assets/images/Correo/florredonda.png" alt="Logo" width="60px" height="60px" style="display:block;" /><br><b>{$Cte}<br> Recibe más Información</b>
              </a>
            </td>
          </tr>
          <tr>
            <td bgcolor="#ffffff" align="center" style="padding:5px; font-family:Arial, sans-serif; font-size:14px;">
              <div style="padding:20px; color:#153643; font-family:Arial, sans-serif; font-size:16px; line-height:25px;">
                <p>Agenda una cita telefónica y uno de nuestros ejecutivos te contactará para resolver todas tus preguntas.</p>
              </div>
              <a href="https://kasu.com.mx/prospectos.php?data={$DirUrl}&Usr={$dirUrl1}" style="padding:5px;">
                <img src="https://kasu.com.mx/assets/images/Correo/llamada3.png" alt="Agendar Cita" width="580px" height="50px" style="display:block;" />
              </a>
            </td>
          </tr>
          <tr>
            <td style="padding:15px;">
              <table align="center" width="100%">
                <tr>
                  <td align="center" style="font-family:Arial, sans-serif; font-size:14px; padding:10px;">
                    <p style="text-align:center; color:aliceblue;">
                      <font>© 2021 | Kasu Servicios a futuro<br>KASU desarrolla productos financieros para solventar momentos importantes en tu vida y la de los tuyos.</font>
                      <a href="https://kasu.com.mx/index.php?Ml=4&Id={$Id}" style="color:#153643;"><font>Ya no quiero recibir este correo</font></a>
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
</html>
HTML;
                break;
            case 'Felices Fiestas':
                $message = <<<HTML
<!DOCTYPE html>
<html lang="ES">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>KASU</title>
  <style type="text/css">
    @media only screen and (max-width:600px) {
      ul li, ol li, p { font-size:18px !important; line-height:150% !important; text-align:Justify !important; }
      h1 { font-size:20px !important; text-align:Justify; line-height:120% !important; }
      h1 a { font-size:18px !important; }
      *[class="gmail-fix"] { display:none !important; }
      a.es-button { font-size:35px !important; color:#FFFFFF !important; display:block !important; text-decoration:none; text-align:center !important; border-left-width:0px !important; border-right-width:0px !important; }
      table.es-table-not-adapt, .esd-block-html table { width:auto !important; }
    }
    #outlook a { padding:0; }
    a[x-apple-data-detectors] { color:inherit !important; text-decoration:none !important; font-size:inherit !important; font-family:inherit !important; line-height:inherit !important; }
  </style>
</head>
<body style="margin:0; padding:0;">
  <table align="center" width="600px" style="border:1px solid #cccccc; background-image:url(http://kasu.com.mx/assets/images/Correo/fondo.jpg); background-position:center top;">
    <tr>
      <td style="padding:20px 0 30px 0">
        <table align="center" width="100%">
          <tr>
            <td align="center" style="font-size:25px; color:#153643; font-family:Arial, sans-serif;">
              <a href="https://kasu.com.mx/" style="text-decoration:none; color:whitesmoke;">
                <img src="https://kasu.com.mx/assets/images/kasu_logo.jpeg" alt="" width="100px" height="100px" style="display:block;" />
              </a>
            </td>
          </tr>
          <tr>
            <td bgcolor="#ffffff" align="center" style="padding:20px;">
              <div style="text-align:center">
                <b>Hola {$Cte}</b><br>
                <b>“El compromiso con nuestros clientes crece año tras año, su confianza es nuestro mayor valor”</b>
              </div>
            </td>
          </tr>
          <tr style="border-collapse:collapse;">
            <td align="left" bgcolor="#ffffff" style="padding:10px 30px 5px 30px;">
              <p style="font-size:16px; font-family:Arial, sans-serif; line-height:24px; color:#666666;">
                En KASU estamos felices de contar con su preferencia, gracias por abrirnos las puertas de sus hogares y depositar en nosotros el mayor valor: su confianza. Les deseamos un nuevo año venturoso y lleno de amor.
              </p>
              <br>
              <div style="text-align:center">
                <p style="font-size:16px; font-family:Arial, sans-serif; color:Black;">¡Felices fiestas!</p>
              </div>
            </td>
          </tr>
          <tr style="border-collapse:collapse;">
            <td align="center" style="padding:15px;">
              <table align="center" width="100%">
                <tr>
                  <td align="center">
                    <table border="0" cellpadding="0" cellspacing="0">
                      <tr>
                        <td>
                          <a href="https://www.facebook.com/KasuMexico">
                            <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/facebook-circle-colored.png" alt="Fb" title="Facebook" width="32" height="32" style="display:block; border:0;" />
                          </a>
                        </td>
                        <td style="width:20px;">&nbsp;</td>
                        <td>
                          <a href="https://twitter.com/KASSU_11">
                            <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/twitter-circle-colored.png" alt="Tw" title="Twitter" width="32" height="32" style="display:block; border:0;" />
                          </a>
                        </td>
                        <td style="width:20px;">&nbsp;</td>
                        <td>
                          <a href="https://www.instagram.com/kasumexico">
                            <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/instagram-circle-colored.png" alt="Ig" title="Instagram" width="32" height="32" style="display:block; border:0;" />
                          </a>
                        </td>
                      </tr>
                    </table>
                  </td>
                  <tr>
                    <td align="center" style="font-family:Arial, sans-serif; font-size:14px; padding:0 10px;">
                      <p style="text-align:center; color:white;">
                        <font>© 2021 | Kasu Servicios a futuro<br>Kasu desarrolla productos financieros para solventar momentos importantes en tu vida.</font>
                        <a href="https://kasu.com.mx/index.php?Ml=4&Id={$Id}" style="color:#153643;"><font>Ya no quiero recibir este correo</font></a>
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
</html>
HTML;
                break;
            case 'PROCESO DE COMPRA DE SERVICIOS KASU':
                // Caso ya incluido.
                break;
            case 'AGENDAR CITA':
                // Caso ya incluido.
                break;
            case 'CITA TELEFONICA':
                $message = <<<HTML
<!DOCTYPE html>
<html lang="ES">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>KASU</title>
  <style type="text/css">
    @media only screen and (max-width:600px){
      ul li, ol li, p { font-size:18px !important; line-height:150% !important; text-align:Justify !important; }
      h1 { font-size:20px !important; text-align:Justify; line-height:120% !important; }
      h1 a { font-size:18px !important; }
      *[class="gmail-fix"] { display:none !important; }
      a.es-button { font-size:35px !important; color:#FFFFFF !important; display:block !important; text-decoration:none; text-align:center !important; border-left-width:0px !important; border-right-width:0px !important; }
      table.es-table-not-adapt, .esd-block-html table { width:auto !important; }
    }
    #outlook a { padding:0; }
    a[x-apple-data-detectors] { color:inherit !important; text-decoration:none !important; font-size:inherit !important; font-family:inherit !important; }
  </style>
</head>
<body style="margin:0; padding:0;">
  <table align="center" width="600px" style="border:1px solid #cccccc; background-image:url(http://kasu.com.mx/assets/images/Correo/fondo.jpg); background-position:center center;">
    <tr>
      <td style="padding:20px 0 30px 0">
        <table align="center" width="100%">
          <tr>
            <td align="center" style="padding:30px; font-size:25px; color:#153643; font-family:Arial, sans-serif;">
              <a href="https://kasu.com.mx" style="text-decoration:none; color:white;">
                <img src="https://kasu.com.mx/assets/images/Correo/florredonda.png" alt="" width="80px" height="80px" style="display:block;" /><br><b>{$Cte} tu cita esta lista</b>
              </a>
            </td>
          </tr>
          <tr>
            <td bgcolor="#ffffff" align="justify" style="padding:20px 15px 5px 25px; font-family:Arial, sans-serif; font-size:16px; line-height:25px; color:#153643;">
              <b></b>
            </td>
          </tr>
          <tr style="border-collapse:collapse;">
            <td align="left" bgcolor="#ffffff" style="padding:10px 30px 5px 30px;">
              <p style="font-size:16px; font-family:arial, sans-serif; line-height:24px; color:#666666;">
                ¡Ya agendamos tu cita telefónica para ponernos en contacto contigo!<br> Corroboramos tus datos
              </p><br />
            </td>
          </tr>
          <tr>
            <td style="padding:15px 50px;" bgcolor="#ffffff" align="center">
              <table align="center" width="80%" style="background-color:transparent; box-shadow:0 4px 8px 0 rgba(0,0,255,0.3), 0 6px 20px 0 rgba(0,0,255,0.5);">
                <tr>
                  <td align="justify" width="20%" style="padding:5px;">Nombre:</td>
                  <td align="justify" width="50%" style="padding:5px;">{$Cte}</td>
                </tr>
                <tr>
                  <td align="justify" width="20%" style="padding:5px;">Telefono:</td>
                  <td align="justify" width="50%" style="padding:5px;">{$DirUrl}</td>
                </tr>
                <tr>
                  <td align="justify" width="20%" style="padding:5px;">Dia y Hora:</td>
                  <td align="justify" width="50%" style="padding:5px;">{$dirUrl1}</td>
                </tr>
              </table>
            </td>
          </tr>
          <tr>
            <td bgcolor="#ffffff" align="justify" style="padding:5px 25px 30px 25px; font-family:Arial, sans-serif; font-size:16px; line-height:20px;">
              <p style="font-size:14px; font-family:arial, sans-serif; line-height:24px; color:#666666;"><br/>El equipo de atención a clientes de KASU agradece tu interés, Saludos</p>
            </td>
          </tr>
          <tr>
            <td style="padding:15px 30px;">
              <table align="center" width="100%">
                <tr>
                  <td align="center">
                    <table border="0" cellpadding="0" cellspacing="0">
                      <tr>
                        <td>
                          <a href="https://www.facebook.com/KasuMexico">
                            <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/facebook-circle-colored.png" alt="Fb" title="Facebook" width="32" height="32" style="display:block; border:0;" />
                          </a>
                        </td>
                        <td style="width:20px;">&nbsp;</td>
                        <td>
                          <a href="https://twitter.com/KASSU_11">
                            <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/twitter-circle-colored.png" alt="Tw" title="Twitter" width="32" height="32" style="display:block; border:0;" />
                          </a>
                        </td>
                        <td style="width:20px;">&nbsp;</td>
                        <td>
                          <a href="https://www.instagram.com/kasumexico">
                            <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/instagram-circle-colored.png" alt="Ig" title="Instagram" width="32" height="32" style="display:block; border:0;" />
                          </a>
                        </td>
                      </tr>
                    </table>
                  </td>
                  <tr>
                    <td align="center" style="font-family:Arial, sans-serif; font-size:14px; padding:0 10px;">
                      <p style="text-align:center; color:aliceblue;">
                        <font>© 2021 | Kasu Servicios a futuro<br>KASU desarrolla productos financieros para solventar momentos importantes en tu vida y la de los tuyos.</font>
                        <a href="https://kasu.com.mx/index.php?Ml=4&Id={$Id}" style="color:#153643;"><font>Ya no quiero recibir este correo</font></a>
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
</html>
HTML;
                break;
            default:
                $message = "No se encontró plantilla para el asunto especificado.";
                break;
        }
        return $message;
    }

    /**
     * Envía el archivo indicado para descarga.
     *
     * @param string $nombre_archivo Nombre del archivo con extensión.
     */
    public function descargar($nombre_archivo) {
      $this->trackUsage();  // Registra el uso de este método.
        if (!isset($nombre_archivo) || empty($nombre_archivo)) {
            exit();
        }
        $root = "ArchivosKasu/";
        $file = basename($nombre_archivo);
        $path = $root . $file;
        if (is_file($path)) {
            $size = filesize($path);
            $type = "";
            if (function_exists("mime_content_type")) {
                $type = mime_content_type($path);
            } elseif (function_exists("finfo_file")) {
                $info = finfo_open(FILEINFO_MIME);
                $type = finfo_file($info, $path);
                finfo_close($info);
            }
            if ($type == "") {
                $type = "application/force-download";
            }
            header("Content-Type: $type");
            header("Content-Disposition: attachment; filename=$file");
            header("Content-Transfer-Encoding: binary");
            header("Content-Length: " . $size);
            readfile($path);
        } else {
            echo "<script type='text/javascript'>alert('El archivo no existe');</script>";
        }
    }
}
?>