<?php
// Contador de veces que se usa cada funcion para seguimiento *JCCM
require_once 'FunctionUsageTracker.php';

class Correo {

    use UsageTrackerTrait;

    /**
     * Genera el encabezado HTML de todos los correos.
     */
    private function mailHeader() {
        return <<<HTML
<!DOCTYPE html>
<html lang="ES">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>KASU</title>
  <style type="text/css">
    @media only screen and (max-width:600px){
      ul li, ol li, p { font-size:18px !important; line-height:150% !important; text-align:justify !important; }
      h1 { font-size:20px !important; text-align:justify; line-height:120% !important; }
      *[class="gmail-fix"] { display:none !important; }
      a.es-button { font-size:35px !important; color:whitesmoke !important; display:block !important; text-decoration:none; text-align:center !important; }
      table.es-table-not-adapt, .esd-block-html table { width:auto !important; }
    }
    #outlook a { padding:0; }
    a[x-apple-data-detectors] { color:inherit !important; text-decoration:none !important; font-size:inherit !important; font-family:inherit !important; font-weight:inherit !important; line-height:inherit !important; }
  </style>
</head>
<body style="margin:0; padding:0;">
  <table align="center" width="600px" style="border:1px solid #cccccc; background-image:url(http://kasu.com.mx/assets/images/Correo/fondo.jpg); background-position:center top;">
    <tr>
      <td>
HTML;
    }

    /**
     * Genera el pie de página HTML de todos los correos.
     */
    private function mailFooter($Id) {
        return <<<HTML
        <tr>
          <td style="padding:15px 30px;">
            <table align="center" width="100%">
              <tr>
                <td align="center">
                  <table border="0" cellpadding="0" cellspacing="0">
                    <tr>
                      <td>
                        <a href="https://www.facebook.com/KasuMexico">
                          <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/facebook-circle-colored.png" alt="Fb" width="32" height="32" style="display:block; border:0;" />
                        </a>
                      </td>
                      <td style="width:20px;">&nbsp;</td>
                      <td>
                        <a href="https://twitter.com/KASSU_11">
                          <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/twitter-circle-colored.png" alt="Tw" width="32" height="32" style="display:block; border:0;" />
                        </a>
                      </td>
                      <td style="width:20px;">&nbsp;</td>
                      <td>
                        <a href="https://www.instagram.com/kasumexico">
                          <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/instagram-circle-colored.png" alt="Ig" width="32" height="32" style="display:block; border:0;" />
                        </a>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
              <tr>
                <td align="center" style="font-family:Arial, sans-serif; font-size:14px; padding:0 10px;">
                  <p style="text-align:center; color:aliceblue;">
                    <font>© 2021 | Kasu Servicios a futuro<br>KASU desarrolla productos financieros para solventar momentos importantes en tu vida y la de los tuyos.</font>
                    <a href="https://kasu.com.mx/index.php?Ml=4&Id={$Id}" style="color:#153643;"><font>Ya no quiero recibir este correo</font></a>
                  </p>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;
    }

    /**
     * Genera el cuerpo del correo según el asunto y datos.
     * @param string $Asunto
     * @param array $data
     * @param string $Id
     * @return string
     */
    private function mailBody($Asunto, $data, $Id = '') {
    switch ($Asunto) {
        case '¡BIENVENIDO A KASU!':
            return <<<HTML
      <tr>
        <td align="center" style="padding:2px; font-size:25px; color:#153643; font-family:Arial, sans-serif;">
          <a href="https://kasu.com.mx" style="text-decoration:none; color:whitesmoke;">
            <img src="https://kasu.com.mx/assets/images/Correo/florredonda.png" alt="" width="100px" height="100px" style="display:block;" />
          </a>
        </td>
      </tr>
      <tr>
        <td bgcolor="#ffffff" align="justify" style="padding:20px 15px 5px 25px; color:#153643; font-family:Arial, sans-serif; font-size:16px;">
          <b>{$data['Cte']}</b>
        </td>
      </tr>
      <tr>
        <td align="left" bgcolor="#ffffff" style="padding:10px 30px 5px 30px;">
          <p style="font-size:16px; font-family:arial, sans-serif; line-height:24px; color:#666666;">Felicidades por ser cliente KASU.</p>
          <p style="font-size:16px; font-family:arial, sans-serif; line-height:24px; color:#666666;">
            Es un honor informarte que tu poliza ya está activa. Descarga tu poliza dando clic en el siguiente boton.
          </p>
        </td>
      </tr>
      <tr>
        <td bgcolor="#ffffff" align="center" style="padding:30px;">
          <span style="border:solid #ee3a87; background:#ee3a87; border-width:20px; display:inline-block; border-radius:5px;">
            <a href="https://kasu.com.mx/login/Generar_PDF/Poliza_pdf.php?busqueda={$data['DirUrl']}" class="es-button" target="_blank" style="text-decoration:none; font-family:Arial, sans-serif; font-size:30px; color:#FFFFFF; background:#ee3a87; border-radius:5px; line-height:30px;">Descargar Poliza</a>
          </span>
        </td>
      </tr>
      <tr>
        <td bgcolor="#ffffff" align="justify" style="padding:5px 25px 30px 25px; font-family:Arial, sans-serif; font-size:16px; line-height:20px; color:#153643;">
          <p style="font-size:16px; font-family:Arial, sans-serif; line-height:24px; color:#666666;">
            Recuerda que en todo momento puedes consultar tus datos y descargar tu poliza en kasu.com.mx
          </p>
          <p style="font-size:12px; font-family:Arial, sans-serif; line-height:24px; color:#666666;">
            Saludos<br>Equipo KASU
          </p>
        </td>
      </tr>
      HTML;

              case 'PAGO PENDIENTE':
                  return <<<HTML
      <tr>
        <td align="center" style="padding:2px; font-size:25px; color:#153643; font-family:Arial, sans-serif;">
          <a href="https://kasu.com.mx" style="text-decoration:none; color:white;">
            <img src="https://kasu.com.mx/assets/images/Correo/florredonda.png" alt="" width="100px" height="100px" style="display:block;" />
          </a>
        </td>
      </tr>
      <tr>
        <td bgcolor="#ffffff" align="justify" style="padding:20px 15px 5px 25px; font-family:Arial, sans-serif; font-size:16px; line-height:25px; color:#153643;">
          <b>{$data['Cte']}</b><br/>
          <p>Para continuar con tu pago por tarjeta, presiona el siguiente boton.</p>
        </td>
      </tr>
      <tr>
        <td bgcolor="#ffffff" align="center" style="padding:30px;">
          <span style="border:solid #ee3a87; background:#ee3a87; border-width:20px; display:inline-block; border-radius:5px;">
            <a href="{$data['DirUrl']}" class="es-button" target="_blank" style="text-decoration:none; font-family:Arial, sans-serif; font-size:35px; color:#FFFFFF; background:#ee3a87; border-radius:5px; line-height:40px;">Pagar ahora</a>
          </span>
        </td>
      </tr>
      <tr>
        <td bgcolor="#ffffff" align="justify" style="padding:5px 25px 30px 25px; font-family:Arial, sans-serif; font-size:16px; line-height:20px;">
          <p style="background-color:azure;">"Recuerda que si pagas con tarjeta, NO se te llamará para solicitarte claves o NIP. No los proporciones si te los piden."</p>
          <p>Gracias por formar parte de la comunidad KASU.</p>
          <p style="font-size:12px; text-align:justify;">Si el boton no funciona, presiona el siguiente enlace: <a href="{$data['DirUrl']}" style="color:cornflowerblue;"><b>PAGAR</b></a></p>
        </td>
      </tr>
      HTML;

              case 'PAGO REALIZADO':
                  return <<<HTML
      <tr>
        <td align="center" style="padding:2px; font-size:25px; color:#153643; font-family:Arial, sans-serif;">
          <a href="https://kasu.com.mx" style="text-decoration:none; color:whitesmoke;">
            <img src="https://kasu.com.mx/assets/images/Correo/florredonda.png" alt="" width="100px" height="100px" style="display:block;" />
          </a>
        </td>
      </tr>
      <tr>
        <td bgcolor="#ffffff" align="justify" style="padding:20px 15px 5px 25px; font-family:Arial, sans-serif; font-size:16px; color:#153643;">
          <b>{$data['Cte']}</b>
        </td>
      </tr>
      <tr>
        <td align="left" bgcolor="#ffffff" style="padding:10px 30px 5px 30px;">
          <p style="font-size:16px; font-family:arial, sans-serif; line-height:24px; color:#666666;">¡Pago recibido con éxito!</p>
          <p style="font-size:16px; font-family:arial, sans-serif; line-height:24px; color:#666666;">
            Pronto te haremos llegar tu factura y tendrás acceso a todos los beneficios de KASU.
          </p>
        </td>
      </tr>
      <tr>
        <td bgcolor="#ffffff" align="justify" style="padding:5px 25px 30px 25px; font-family:Arial, sans-serif; font-size:16px; line-height:20px; color:#153643;">
          <p>Gracias por tu preferencia.</p>
          <p style="font-size:12px; font-family:Arial, sans-serif; line-height:24px; color:#666666;">
            Saludos<br>Equipo KASU
          </p>
        </td>
      </tr>
      HTML;

              case 'ERROR DE PAGO':
                  return <<<HTML
      <tr>
        <td align="center" style="padding:2px; font-size:25px; color:#153643; font-family:Arial, sans-serif;">
          <a href="https://kasu.com.mx" style="text-decoration:none; color:white;">
            <img src="https://kasu.com.mx/assets/images/Correo/florredonda.png" alt="" width="100px" height="100px" style="display:block;" />
          </a>
        </td>
      </tr>
      <tr>
        <td bgcolor="#ffffff" align="justify" style="padding:20px 15px 5px 25px; font-family:Arial, sans-serif; font-size:16px; color:#153643;">
          <b>{$data['Cte']}</b>
        </td>
      </tr>
      <tr>
        <td align="left" bgcolor="#ffffff" style="padding:10px 30px 5px 30px;">
          <p style="font-size:16px; font-family:arial, sans-serif; line-height:24px; color:#666666;">
            Hubo un error en tu pago.<br>
            Te pedimos intentarlo de nuevo o comunicarte a nuestro centro de atencion: <b>55 8950 8098</b>
          </p>
        </td>
      </tr>
      <tr>
        <td bgcolor="#ffffff" align="center" style="padding:30px;">
          <span style="border:solid #ee3a87; background:#ee3a87; border-width:20px; display:inline-block; border-radius:5px;">
            <a href="{$data['DirUrl']}" class="es-button" target="_blank" style="text-decoration:none; font-family:Arial, sans-serif; font-size:35px; color:#FFFFFF; background:#ee3a87; border-radius:5px; line-height:40px;">Reintentar pago</a>
          </span>
        </td>
      </tr>
      <tr>
        <td bgcolor="#ffffff" align="justify" style="padding:5px 25px 30px 25px; font-family:Arial, sans-serif; font-size:16px; line-height:20px;">
          <p>Gracias por tu preferencia.</p>
          <p style="font-size:12px; font-family:Arial, sans-serif; line-height:24px; color:#666666;">
            Saludos<br>Equipo KASU
          </p>
        </td>
      </tr>
      HTML;

              case 'CAMBIO DE CONTRASEÑA':
                  return <<<HTML
      <tr>
        <td align="center" style="padding:2px; font-size:25px; color:#153643; font-family:Arial, sans-serif;">
          <a href="https://kasu.com.mx" style="text-decoration:none; color:white;">
            <img src="https://kasu.com.mx/assets/images/Correo/florredonda.png" alt="" width="100px" height="100px" style="display:block;" />
          </a>
        </td>
      </tr>
      <tr>
        <td bgcolor="#ffffff" align="justify" style="padding:20px 15px 5px 25px; font-family:Arial, sans-serif; font-size:16px; color:#153643;">
          <b>{$data['Cte']}</b>
        </td>
      </tr>
      <tr>
        <td align="left" bgcolor="#ffffff" style="padding:10px 30px 5px 30px;">
          <p style="font-size:16px; font-family:arial, sans-serif; line-height:24px; color:#666666;">
            Tu contraseña ha sido actualizada exitosamente.<br>
            Si no reconoces este cambio, comunicate con nosotros a la brevedad: <b>55 8950 8098</b>
          </p>
        </td>
      </tr>
      <tr>
        <td bgcolor="#ffffff" align="justify" style="padding:5px 25px 30px 25px; font-family:Arial, sans-serif; font-size:16px; line-height:20px;">
          <p>Gracias por tu preferencia.</p>
          <p style="font-size:12px; font-family:Arial, sans-serif; line-height:24px; color:#666666;">
            Saludos<br>Equipo KASU
          </p>
        </td>
      </tr>
      HTML;

              case 'RESTABLECIMIENTO DE CONTRASEÑA':
                  return <<<HTML
      <tr>
        <td align="center" style="padding:2px; font-size:25px; color:#153643; font-family:Arial, sans-serif;">
          <a href="https://kasu.com.mx" style="text-decoration:none; color:white;">
            <img src="https://kasu.com.mx/assets/images/Correo/florredonda.png" alt="" width="100px" height="100px" style="display:block;" />
          </a>
        </td>
      </tr>
      <tr>
        <td bgcolor="#ffffff" align="justify" style="padding:20px 15px 5px 25px; font-family:Arial, sans-serif; font-size:16px; color:#153643;">
          <b>{$data['Cte']}</b>
        </td>
      </tr>
      <tr>
        <td align="left" bgcolor="#ffffff" style="padding:10px 30px 5px 30px;">
          <p style="font-size:16px; font-family:arial, sans-serif; line-height:24px; color:#666666;">
            Se ha solicitado el restablecimiento de tu contraseña.<br>
            Da clic en el siguiente boton para cambiar tu contraseña:
          </p>
        </td>
      </tr>
      <tr>
        <td bgcolor="#ffffff" align="center" style="padding:30px;">
          <span style="border:solid #ee3a87; background:#ee3a87; border-width:20px; display:inline-block; border-radius:5px;">
            <a href="{$data['DirUrl']}" class="es-button" target="_blank" style="text-decoration:none; font-family:Arial, sans-serif; font-size:35px; color:#FFFFFF; background:#ee3a87; border-radius:5px; line-height:40px;">Cambiar contraseña</a>
          </span>
        </td>
      </tr>
      <tr>
        <td bgcolor="#ffffff" align="justify" style="padding:5px 25px 30px 25px; font-family:Arial, sans-serif; font-size:16px; line-height:20px;">
          <p>Si no solicitaste este cambio, comunicate con nosotros: <b>55 8950 8098</b></p>
          <p style="font-size:12px; font-family:Arial, sans-serif; line-height:24px; color:#666666;">
            Saludos<br>Equipo KASU
          </p>
        </td>
      </tr>
      HTML;

              case 'ENVÍO DE FACTURA':
                  return <<<HTML
      <tr>
        <td align="center" style="padding:2px; font-size:25px; color:#153643; font-family:Arial, sans-serif;">
          <a href="https://kasu.com.mx" style="text-decoration:none; color:white;">
            <img src="https://kasu.com.mx/assets/images/Correo/florredonda.png" alt="" width="100px" height="100px" style="display:block;" />
          </a>
        </td>
      </tr>
      <tr>
        <td bgcolor="#ffffff" align="justify" style="padding:20px 15px 5px 25px; font-family:Arial, sans-serif; font-size:16px; color:#153643;">
          <b>{$data['Cte']}</b>
        </td>
      </tr>
      <tr>
        <td align="left" bgcolor="#ffffff" style="padding:10px 30px 5px 30px;">
          <p style="font-size:16px; font-family:arial, sans-serif; line-height:24px; color:#666666;">
            Adjuntamos tu factura correspondiente a tu pago.<br>
            Gracias por confiar en KASU.
          </p>
        </td>
      </tr>
      <tr>
        <td bgcolor="#ffffff" align="justify" style="padding:5px 25px 30px 25px; font-family:Arial, sans-serif; font-size:16px; line-height:20px;">
          <p>Si necesitas algo más, comunicate a nuestro centro de atencion: <b>55 8950 8098</b></p>
          <p style="font-size:12px; font-family:Arial, sans-serif; line-height:24px; color:#666666;">
            Saludos<br>Equipo KASU
          </p>
        </td>
      </tr>
      HTML;

              case 'ERROR DE ARCHIVOS':
                  return <<<HTML
      <tr>
        <td align="center" style="padding:2px; font-size:25px; color:#153643; font-family:Arial, sans-serif;">
          <a href="https://kasu.com.mx" style="text-decoration:none; color:white;">
            <img src="https://kasu.com.mx/assets/images/Correo/florredonda.png" alt="" width="100px" height="100px" style="display:block;" />
          </a>
        </td>
      </tr>
      <tr>
        <td bgcolor="#ffffff" align="justify" style="padding:20px 15px 5px 25px; font-family:Arial, sans-serif; font-size:16px; color:#153643;">
          <b>{$data['Cte']}</b>
        </td>
      </tr>
      <tr>
        <td align="left" bgcolor="#ffffff" style="padding:10px 30px 5px 30px;">
          <p style="font-size:16px; font-family:arial, sans-serif; line-height:24px; color:#666666;">
            Hubo un error en el envio de tus archivos.<br>
            Intenta de nuevo o comunicate a nuestro centro de atencion: <b>55 8950 8098</b>
          </p>
        </td>
      </tr>
      <tr>
        <td bgcolor="#ffffff" align="justify" style="padding:5px 25px 30px 25px; font-family:Arial, sans-serif; font-size:16px; line-height:20px;">
          <p>Gracias por tu preferencia.</p>
          <p style="font-size:12px; font-family:Arial, sans-serif; line-height:24px; color:#666666;">
            Saludos<br>Equipo KASU
          </p>
        </td>
      </tr>
      HTML;

              case 'CAMBIO DE CORREO':
                  return <<<HTML
      <tr>
        <td align="center" style="padding:2px; font-size:25px; color:#153643; font-family:Arial, sans-serif;">
          <a href="https://kasu.com.mx" style="text-decoration:none; color:white;">
            <img src="https://kasu.com.mx/assets/images/Correo/florredonda.png" alt="" width="100px" height="100px" style="display:block;" />
          </a>
        </td>
      </tr>
      <tr>
        <td bgcolor="#ffffff" align="justify" style="padding:20px 15px 5px 25px; font-family:Arial, sans-serif; font-size:16px; color:#153643;">
          <b>{$data['Cte']}</b>
        </td>
      </tr>
      <tr>
        <td align="left" bgcolor="#ffffff" style="padding:10px 30px 5px 30px;">
          <p style="font-size:16px; font-family:arial, sans-serif; line-height:24px; color:#666666;">
            Tu correo electronico ha sido actualizado exitosamente.<br>
            Si no reconoces este cambio, comunicate con nosotros a la brevedad: <b>55 8950 8098</b>
          </p>
        </td>
      </tr>
      <tr>
        <td bgcolor="#ffffff" align="justify" style="padding:5px 25px 30px 25px; font-family:Arial, sans-serif; font-size:16px; line-height:20px;">
          <p>Gracias por tu preferencia.</p>
          <p style="font-size:12px; font-family:Arial, sans-serif; line-height:24px; color:#666666;">
            Saludos<br>Equipo KASU
          </p>
        </td>
      </tr>
      HTML;

      case 'ENVIO DE ESTADO DE CUENTA':
            return <<<HTML
        <tr>
          <td align="center" style="padding:2px; font-size:25px; color:#153643; font-family:Arial, sans-serif;">
            <a href="https://kasu.com.mx" style="text-decoration:none; color:whitesmoke;">
              <img src="https://kasu.com.mx/assets/images/Correo/florredonda.png" alt="KASU" width="100px" height="100px" style="display:block;" />
            </a>
          </td>
        </tr>
        <tr>
          <td bgcolor="#ffffff" align="justify" style="padding:20px 15px 5px 25px; font-family:Arial, sans-serif; font-size:16px; color:#153643;">
            <b>{$data['Cte']}</b>
          </td>
        </tr>
        <tr>
          <td align="left" bgcolor="#ffffff" style="padding:10px 30px 5px 30px;">
            <p style="font-size:16px; font-family:arial, sans-serif; line-height:24px; color:#666666;">
              Te enviamos tu <b>Estado de Cuenta</b> correspondiente.<br>
              Puedes descargar el documento haciendo clic en el siguiente boton:
            </p>
          </td>
        </tr>
        <tr>
          <td bgcolor="#ffffff" align="center" style="padding:30px;">
            <span style="border:solid #ee3a87; background:#ee3a87; border-width:20px; display:inline-block; border-radius:5px;">
              <a href="{$data['DirUrl']}" class="es-button" target="_blank" style="text-decoration:none; font-family:Arial, sans-serif; font-size:30px; color:#FFFFFF; background:#ee3a87; border-radius:5px; line-height:30px;">
                Descargar Estado de Cuenta
              </a>
            </span>
          </td>
        </tr>
        <tr>
          <td bgcolor="#ffffff" align="justify" style="padding:5px 25px 30px 25px; font-family:Arial, sans-serif; font-size:16px; line-height:20px; color:#153643;">
            <p style="font-size:16px; font-family:Arial, sans-serif; line-height:24px; color:#666666;">
              Si tienes alguna duda o necesitas asistencia, por favor comunicate a nuestro centro de atencion:<br>
              <b>55 8950 8098</b>
            </p>
            <p style="font-size:12px; font-family:Arial, sans-serif; line-height:24px; color:#666666;">
              Saludos<br>Equipo KASU
            </p>
          </td>
        </tr>
        HTML;

        case 'ENVIO DE FICHAS':
            return <<<HTML
        <tr>
          <td align="center" style="padding:2px; font-size:25px; color:#153643; font-family:Arial, sans-serif;">
            <a href="https://kasu.com.mx" style="text-decoration:none; color:whitesmoke;">
              <img src="https://kasu.com.mx/assets/images/Correo/florredonda.png" alt="KASU" width="100px" height="100px" style="display:block;" />
            </a>
          </td>
        </tr>
        <tr>
          <td bgcolor="#ffffff" align="justify" style="padding:20px 15px 5px 25px; font-family:Arial, sans-serif; font-size:16px; color:#153643;">
            <b>{$data['Cte']}</b>
          </td>
        </tr>
        <tr>
          <td align="left" bgcolor="#ffffff" style="padding:10px 30px 5px 30px;">
            <p style="font-size:16px; font-family:arial, sans-serif; line-height:24px; color:#666666;">
              Te enviamos tus <b>Fichas de Pago</b>.<br>
              Puedes descargarlas  haciendo clic en el siguiente boton:
            </p>
          </td>
        </tr>
        <tr>
          <td bgcolor="#ffffff" align="center" style="padding:30px;">
            <span style="border:solid #ee3a87; background:#ee3a87; border-width:20px; display:inline-block; border-radius:5px;">
              <a href="{$data['DirUrl']}" class="es-button" target="_blank" style="text-decoration:none; font-family:Arial, sans-serif; font-size:30px; color:#FFFFFF; background:#ee3a87; border-radius:5px; line-height:30px;">
                Descargar Fichas
              </a>
            </span>
          </td>
        </tr>
        <tr>
          <td bgcolor="#ffffff" align="justify" style="padding:5px 25px 30px 25px; font-family:Arial, sans-serif; font-size:16px; line-height:20px; color:#153643;">
            <p style="font-size:16px; font-family:Arial, sans-serif; line-height:24px; color:#666666;">
              Si tienes alguna duda o necesitas asistencia, por favor comunicate a nuestro centro de atencion:<br>
              <b>720 817 7632</b>
            </p>
            <p style="font-size:12px; font-family:Arial, sans-serif; line-height:24px; color:#666666;">
              Saludos<br>Equipo KASU
            </p>
          </td>
        </tr>
        HTML;

              default:
                  return "<tr><td>No se encontró plantilla para el asunto especificado.</td></tr>";
          }
      }

    /**
     * Retorna el HTML del mensaje de correo según el asunto.
     * @param string $Asunto
     * @param array $data
     * @param string $Id
     * @return string
     */
    public function Mensaje($Asunto, $data, $Id = '') {
        $this->trackUsage();
        $header = $this->mailHeader();
        $body = $this->mailBody($Asunto, $data, $Id);
        $footer = $this->mailFooter($Id);
        return $header . $body . $footer;
    }

    /**
     * Envía el archivo indicado para descarga.
     * @param string $nombre_archivo Nombre del archivo con extension.
     */
    public function descargar($nombre_archivo) {
        $this->trackUsage();
        if (empty($nombre_archivo)) exit();
        $root = "ArchivosKasu/";
        $file = basename($nombre_archivo);
        $path = $root . $file;
        if (!is_file($path)) {
            echo "<script>alert('El archivo no existe');</script>";
            return;
        }
        $type = function_exists('mime_content_type') ? mime_content_type($path) : "application/force-download";
        header("Content-Type: $type");
        header("Content-Disposition: attachment; filename=$file");
        header("Content-Length: " . filesize($path));
        readfile($path);
        exit;
    }
}
?>
