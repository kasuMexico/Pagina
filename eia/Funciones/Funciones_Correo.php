<?php
/********************************************************************************************
 * Qué hace: Clase Correo para generar HTML de emails y enviar mensajes.
 * Fecha: 04/11/2025
 * Revisado por: JCCM
 * Archivo:eia/Funciones/Funciones_Correo.php
 *
 * Notas PHP 8.2:
 * - Se evita uso de propiedades dinámicas.
 * - Compatibilidad retro: Mensaje(...) acepta firma nueva (array $data) y la antigua
 *   con múltiples parámetros sueltos. Detecta y mapea sin cambiar retornos.
 * - Se agrega EnviarTelefono para descarga de archivo con cabeceras seguras.
 * - Sanitización básica en descarga; verificación de existencia y tipo MIME.
 ********************************************************************************************/

// Contador de veces que se usa cada funcion para seguimiento *JCCM
require_once __DIR__ . '/FunctionUsageTracker.php';

class Correo {

    use UsageTrackerTrait;

    /**
     * Genera el encabezado HTML de todos los correos.
     */
    private function mailHeader() {
        return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>KASU</title>
  <style>
    body { margin:0; padding:0; background:#f2f4f6; }
    table { border-collapse:collapse; }
    .wrapper { width:100%; background:#f2f4f6; padding:30px 0; }
    .container { width:600px; background:#ffffff; border-radius:20px; overflow:hidden; box-shadow:0 25px 65px rgba(15,23,42,.18); }
    .hero { background:#1a2d4f; color:#fff; text-align:center; padding:30px 20px; background-image:linear-gradient(120deg,#0d1b3f,#1e4174); }
    .hero img { max-width:160px; margin-bottom:15px; }
    .hero h1 { font-family:'Segoe UI',sans-serif; font-size:22px; margin:0; letter-spacing:.5px; }
    .hero p { font-family:'Segoe UI',sans-serif; margin:10px 0 0; font-size:15px; opacity:.85; }
    @media only screen and (max-width:600px){
      .container{width:90% !important;}
      .hero{padding:25px 15px;}
      .hero h1{font-size:19px;}
      .hero p{font-size:14px;}
    }
  </style>
</head>
<body>
  <table class="wrapper" align="center">
    <tr><td align="center">
      <table class="container" align="center">
        <tr>
          <td class="hero">
            <h1>KASU | Protege lo importante</h1>
            <p>Respaldamos cada paso con soluciones financieras confiables</p>
          </td>
        </tr>
HTML;
    }

    /**
     * Genera el pie de página HTML de todos los correos.
     */
    private function mailFooter($Id) {
        $Id = (string)$Id;
        return <<<HTML
        <tr>
          <td style="padding:25px;">
            <table width="100%" style="border-top:1px solid #e4e9f2;">
              <tr>
                <td align="center" style="padding:10px 0 15px;">
                  <a href="https://www.facebook.com/KasuMexico" style="margin:0 8px;">
                    <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/facebook-circle-colored.png" alt="Facebook" width="32" height="32" style="border:0;">
                  </a>
                  <a href="https://x.com/KASSU_11" style="margin:0 8px;">
                    <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/twitter-circle-colored.png" alt="X" width="32" height="32" style="border:0;">
                  </a>
                  <a href="https://www.instagram.com/kasumexico" style="margin:0 8px;">
                    <img src="https://xpnux.stripocdn.email/content/assets/img/social-icons/circle-colored/instagram-circle-colored.png" alt="Instagram" width="32" height="32" style="border:0;">
                  </a>
                </td>
              </tr>
              <tr>
                <td align="center" style="font-family:'Segoe UI',sans-serif; font-size:13px; color:#6b7785;">
                  © 2021 Kasu Servicios a Futuro · Creamos soluciones financieras para tus momentos clave.<br>
                  <a href="https://kasu.com.mx/index.php?Ml=4&Id={$Id}" style="color:#1e4fa3;">Cancelar suscripción</a>
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
    }

    /**
     * Genera el cuerpo del correo según el asunto y datos.
     * @param string $Asunto
     * @param array  $data Debe contener al menos Cte y DirUrl según plantilla.
     * @param string $Id   IdContact opcional para ligas.
     * @return string
     */
    private function mailBody($Asunto, $data, $Id = '') {
        // Normaliza claves esperadas
        $cte    = isset($data['Cte'])    ? (string)$data['Cte']    : (isset($data['Nombre']) ? (string)$data['Nombre'] : '');
        $dirUrl = isset($data['DirUrl']) ? (string)$data['DirUrl'] : '';
        $Nombre = isset($data['Nombre']) ? (string)$data['Nombre'] : $cte;
        $fechaCita = isset($data['FechaCita']) ? (string)$data['FechaCita'] : '';
        $fechaCitaHtml = '';
        if ($fechaCita !== '') {
            $fechaCitaSafe = htmlspecialchars($fechaCita, ENT_QUOTES, 'UTF-8');
            $fechaCitaHtml = '<p style="font-size:15px; font-family:arial, sans-serif; line-height:22px; color:#666666;">'
                           . 'Tu cita esta agendada para <strong>' . $fechaCitaSafe . '</strong>.'
                           . '</p>';
        }

        switch ($Asunto) {
            case '¡BIENVENIDO A KASU!':
                return <<<HTML
      <tr>
        <td align="center" style="padding:2px; font-size:25px; color:#153643; font-family:Arial, sans-serif;">
          <a href="https://kasu.com.mx" style="text-decoration:none; color:whitesmoke;">
            <img src="https://kasu.com.mx/assets/images/Correo/florredonda.png" alt="" width="100" height="100" style="display:block;" />
          </a>
        </td>
      </tr>
      <tr>
        <td bgcolor="#ffffff" align="justify" style="padding:20px 15px 5px 25px; color:#153643; font-family:Arial, sans-serif; font-size:16px;">
          <b>{$cte}</b>
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
            <a href="https://kasu.com.mx/login/Generar_PDF/Poliza_pdf.php?busqueda={$dirUrl}" class="es-button" target="_blank" style="text-decoration:none; font-family:Arial, sans-serif; font-size:30px; color:#FFFFFF; background:#ee3a87; border-radius:5px; line-height:30px;">Descargar Poliza</a>
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
            <img src="https://kasu.com.mx/assets/images/Correo/florredonda.png" alt="" width="100" height="100" style="display:block;" />
          </a>
        </td>
      </tr>
      <tr>
        <td bgcolor="#ffffff" align="justify" style="padding:20px 15px 5px 25px; font-family:Arial, sans-serif; font-size:16px; line-height:25px; color:#153643;">
          <b>{$cte}</b><br/>
          <p>Para continuar con tu pago por tarjeta, presiona el siguiente boton.</p>
        </td>
      </tr>
      <tr>
        <td bgcolor="#ffffff" align="center" style="padding:30px;">
          <span style="border:solid #ee3a87; background:#ee3a87; border-width:20px; display:inline-block; border-radius:5px;">
            <a href="{$dirUrl}" class="es-button" target="_blank" style="text-decoration:none; font-family:Arial, sans-serif; font-size:35px; color:#FFFFFF; background:#ee3a87; border-radius:5px; line-height:40px;">Pagar ahora</a>
          </span>
        </td>
      </tr>
      <tr>
        <td bgcolor="#ffffff" align="justify" style="padding:5px 25px 30px 25px; font-family:Arial, sans-serif; font-size:16px; line-height:20px;">
          <p style="background-color:azure;">"Recuerda que si pagas con tarjeta, NO se te llamará para solicitarte claves o NIP. No los proporciones si te los piden."</p>
          <p>Gracias por formar parte de la comunidad KASU.</p>
          <p style="font-size:12px; text-align:justify;">Si el boton no funciona, presiona el siguiente enlace: <a href="{$dirUrl}" style="color:cornflowerblue;"><b>PAGAR</b></a></p>
        </td>
      </tr>
HTML;

            case 'PAGO REALIZADO':
                return <<<HTML
      <tr>
        <td style="padding:30px 30px 10px 30px; font-family:'Segoe UI',sans-serif; color:#0f172a;">
          <p style="margin:0;font-size:16px;color:#475569;">Hola <strong>{$cte}</strong>,</p>
          <h2 style="margin:12px 0;font-size:22px;color:#0f172a;">¡Tu pago fue exitoso!</h2>
          <p style="margin:0;font-size:16px;color:#475569;line-height:1.6;">
            Pronto recibirás tu factura digital y podrás disfrutar todos los beneficios KASU. Gracias por seguir protegiendo a los tuyos con nosotros.
          </p>
        </td>
      </tr>
      <tr>
        <td style="padding:15px 30px 35px;font-family:'Segoe UI',sans-serif;color:#475569;font-size:14px;">
          Saludos cordiales,<br>
          <strong>Equipo KASU</strong>
        </td>
      </tr>
HTML;

            case 'ERROR DE PAGO':
                return <<<HTML
      <tr>
        <td style="padding:30px 30px 20px;font-family:'Segoe UI',sans-serif;color:#0f172a;">
          <h2 style="margin:0 0 12px;font-size:22px;">{$cte}, necesitamos tu ayuda</h2>
          <p style="margin:0;font-size:16px;color:#475569;line-height:1.6;">
            El pago no pudo completarse. Te sugerimos intentarlo nuevamente o llamarnos al <strong>55 8950 8098</strong> para ayudarte de inmediato.
          </p>
        </td>
      </tr>
      <tr>
        <td align="center" style="padding:0 30px 35px;">
          <a href="{$dirUrl}" target="_blank" style="display:inline-block;padding:14px 28px;background:#ff5e8e;border-radius:999px;font-family:'Segoe UI',sans-serif;font-size:17px;color:#fff;text-decoration:none;">Reintentar pago</a>
        </td>
      </tr>
HTML;

            case 'CAMBIO DE CONTRASEÑA':
                return <<<HTML
      <tr>
        <td style="padding:30px 30px 20px;font-family:'Segoe UI',sans-serif;color:#0f172a;">
          <h2 style="margin:0 0 10px;font-size:22px;">Contraseña actualizada</h2>
          <p style="margin:0;font-size:16px;color:#475569;line-height:1.6;">{$cte}, tu contraseña se modificó correctamente. Si no fuiste tú, contáctanos de inmediato al <strong>55 8950 8098</strong>.</p>
        </td>
      </tr>
      <tr>
        <td style="padding:5px 30px 35px;font-family:'Segoe UI',sans-serif;color:#475569;font-size:14px;">
          Saludos,<br>Equipo KASU
        </td>
      </tr>
HTML;

            case 'RESTABLECIMIENTO DE CONTRASEÑA':
                return <<<HTML
      <tr>
        <td style="padding:30px 30px 20px;font-family:'Segoe UI',sans-serif;color:#0f172a;">
          <h2 style="margin:0 0 12px;font-size:22px;">Solicitud para restablecer contraseña</h2>
          <p style="margin:0;font-size:16px;color:#475569;line-height:1.6;">
            {$cte}, haz clic en el botón para crear una nueva contraseña. Este enlace expira en 30 minutos.
          </p>
        </td>
      </tr>
      <tr>
        <td align="center" style="padding:0 30px 35px;">
          <a href="{$dirUrl}" target="_blank" style="display:inline-block;padding:14px 30px;background:#1e4fa3;border-radius:999px;font-family:'Segoe UI',sans-serif;font-size:17px;color:#fff;text-decoration:none;">Cambiar contraseña</a>
        </td>
      </tr>
HTML;

            case 'ENVÍO DE FACTURA':
                return <<<HTML
      <tr>
        <td style="padding:30px 30px 20px;font-family:'Segoe UI',sans-serif;color:#0f172a;">
          <h2 style="margin:0 0 10px;font-size:22px;">Tu factura está lista</h2>
          <p style="margin:0;font-size:16px;color:#475569;line-height:1.6;">
            {$cte}, adjuntamos la factura correspondiente a tu pago. Gracias por confiar en KASU.
          </p>
        </td>
      </tr>
      <tr>
        <td style="padding:5px 30px 35px;font-family:'Segoe UI',sans-serif;color:#475569;font-size:14px;">
          Saludos,<br>Equipo KASU
        </td>
      </tr>
HTML;

            case 'ERROR DE ARCHIVOS':
                return <<<HTML
      <tr>
        <td align="center" style="padding:2px; font-size:25px; color:#153643; font-family:Arial, sans-serif;">
          <a href="https://kasu.com.mx" style="text-decoration:none; color:white;">
            <img src="https://kasu.com.mx/assets/images/Correo/florredonda.png" alt="" width="100" height="100" style="display:block;" />
          </a>
        </td>
      </tr>
      <tr>
        <td bgcolor="#ffffff" align="justify" style="padding:20px 15px 5px 25px; font-family:Arial, sans-serif; font-size:16px; color:#153643;">
          <b>{$cte}</b>
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
            <img src="https://kasu.com.mx/assets/images/Correo/florredonda.png" alt="" width="100" height="100" style="display:block;" />
          </a>
        </td>
      </tr>
      <tr>
        <td bgcolor="#ffffff" align="justify" style="padding:20px 15px 5px 25px; font-family:Arial, sans-serif; font-size:16px; color:#153643;">
          <b>{$cte}</b>
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

            case 'ENVÍO DE ESTADO DE CUENTA':
                return <<<HTML
        <tr>
          <td align="center" style="padding:2px; font-size:25px; color:#153643; font-family:Arial, sans-serif;">
            <a href="https://kasu.com.mx" style="text-decoration:none; color:whitesmoke;">
              <img src="https://kasu.com.mx/assets/images/Correo/florredonda.png" alt="KASU" width="100" height="100" style="display:block;" />
            </a>
          </td>
        </tr>
        <tr>
          <td bgcolor="#ffffff" align="justify" style="padding:20px 15px 5px 25px; font-family:Arial, sans-serif; font-size:16px; color:#153643;">
            <b>{$cte}</b>
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
              <a href="{$dirUrl}" class="es-button" target="_blank" style="text-decoration:none; font-family:Arial, sans-serif; font-size:30px; color:#FFFFFF; background:#ee3a87; border-radius:5px; line-height:30px;">
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

            case 'ENVIO DE FICHAS DE PAGO':
                return <<<HTML
        <tr>
          <td align="center" style="padding:2px; font-size:25px; color:#153643; font-family:Arial, sans-serif;">
            <a href="https://kasu.com.mx" style="text-decoration:none; color:whitesmoke;">
              <img src="https://kasu.com.mx/assets/images/Correo/florredonda.png" alt="KASU" width="100" height="100" style="display:block;" />
            </a>
          </td>
        </tr>
        <tr>
          <td bgcolor="#ffffff" align="justify" style="padding:20px 15px 5px 25px; font-family:Arial, sans-serif; font-size:16px; color:#153643;">
            <b>{$cte}</b>
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
              <a href="{$dirUrl}" class="es-button" target="_blank" style="text-decoration:none; font-family:Arial, sans-serif; font-size:30px; color:#FFFFFF; background:#ee3a87; border-radius:5px; line-height:30px;">
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

            case 'ENVÍO DE COTIZACIÓN':
                return <<<HTML
        <tr>
          <td align="center" style="padding:2px; font-size:25px; color:#153643; font-family:Arial, sans-serif;">
            <a href="https://kasu.com.mx" style="text-decoration:none; color:whitesmoke;">
              <img src="https://kasu.com.mx/assets/images/Correo/florredonda.png" alt="KASU" width="100" height="100" style="display:block;" />
            </a>
          </td>
        </tr>
        <tr>
          <td bgcolor="#ffffff" align="justify" style="padding:20px 15px 5px 25px; font-family:Arial, sans-serif; font-size:16px; color:#153643;">
            <b>{$Nombre}</b>
          </td>
        </tr>
        <tr>
          <td align="left" bgcolor="#ffffff" style="padding:10px 30px 5px 30px;">
            <p style="font-size:16px; font-family:arial, sans-serif; line-height:24px; color:#666666;">
              Te enviamos el <b>Presupuesto de KASU</b>.<br>
              Puedes visualizarlo haciendo clic en el siguiente boton:
            </p>
          </td>
        </tr>
        <tr>
          <td bgcolor="#ffffff" align="center" style="padding:30px;">
            <span style="border:solid #ee3a87; background:#ee3a87; border-width:20px; display:inline-block; border-radius:5px;">
              <a href="{$dirUrl}" class="es-button" target="_blank" style="text-decoration:none; font-family:Arial, sans-serif; font-size:30px; color:#FFFFFF; background:#ee3a87; border-radius:5px; line-height:30px;">
                Revisar presupuesto
              </a>
            </span>
          </td>
        </tr>
        <tr>
          <td bgcolor="#ffffff" align="justify" style="padding:5px 25px 30px 25px; font-family:Arial, sans-serif; font-size:16px; line-height:20px; color:#666666;">
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

            case 'GUIA COMPLETA KASU':
                return <<<HTML
      <tr>
        <td align="center" style="padding:2px; font-size:25px; color:#153643; font-family:Arial, sans-serif;">
          <a href="https://kasu.com.mx" style="text-decoration:none; color:whitesmoke;">
            <img src="https://kasu.com.mx/assets/images/Correo/florredonda.png" alt="KASU" width="100" height="100" style="display:block;" />
          </a>
        </td>
      </tr>
      <tr>
        <td bgcolor="#ffffff" align="justify" style="padding:20px 15px 5px 25px; font-family:Arial, sans-serif; font-size:16px; color:#153643;">
          <b>{$Nombre}</b>
        </td>
      </tr>
      <tr>
        <td align="left" bgcolor="#ffffff" style="padding:10px 30px 5px 30px;">
          <p style="font-size:16px; font-family:arial, sans-serif; line-height:24px; color:#666666;">
            Gracias por registrarte. Aqui tienes la <b>Guia completa KASU</b>.
          </p>
          <p style="font-size:14px; font-family:arial, sans-serif; line-height:22px; color:#666666;">
            El enlace es de un solo uso.
          </p>
          {$fechaCitaHtml}
        </td>
      </tr>
      <tr>
        <td bgcolor="#ffffff" align="center" style="padding:30px;">
          <span style="border:solid #ee3a87; background:#ee3a87; border-width:18px; display:inline-block; border-radius:5px;">
            <a href="{$dirUrl}" class="es-button" target="_blank" style="text-decoration:none; font-family:Arial, sans-serif; font-size:26px; color:#FFFFFF; background:#ee3a87; border-radius:5px; line-height:30px;">
              Descargar guia
            </a>
          </span>
        </td>
      </tr>
      <tr>
        <td bgcolor="#ffffff" align="justify" style="padding:5px 25px 30px 25px; font-family:Arial, sans-serif; font-size:16px; line-height:20px; color:#666666;">
          <p style="font-size:12px; font-family:Arial, sans-serif; line-height:24px; color:#666666;">
            Saludos<br>Equipo KASU
          </p>
        </td>
      </tr>
HTML;

            case 'IA · CORREO PROSPECTO':
            case 'IA_CORREO_PROSPECTO':
                // Nombre del destinatario
                $nombrePros = $cte !== '' ? $cte : (isset($data['Nombre']) ? (string)$data['Nombre'] : 'Cliente');

                // Cuerpo generado por la IA (HTML acotado)
                $cuerpo = isset($data['CuerpoHtml']) ? (string)$data['CuerpoHtml'] : '';
                $cuerpoSan = strip_tags($cuerpo, '<p><br><ul><ol><li><strong><b><em><i>');

                if ($cuerpoSan === '') {
                    $cuerpoSan = '<p style="margin:0;font-size:16px;font-family:Arial,sans-serif;line-height:24px;color:#666666;">
                      Este es un correo informativo de KASU. En breve uno de nuestros asesores se pondrá en contacto contigo.
                    </p>';
                }

                $ctaTexto = trim((string)($data['CtaTexto'] ?? ''));
                $ctaUrl   = trim((string)($data['CtaUrl']   ?? ''));
                $ctaBloque = '';

                if ($ctaTexto !== '' && $ctaUrl !== '') {
                    $ctaBloque = <<<HTML
      <tr>
        <td align="center" style="padding:18px 30px 8px 30px;">
          <a href="{$ctaUrl}" target="_blank"
             style="display:inline-block;padding:12px 26px;background:#ee3a87;border-radius:999px;
                    font-family:'Segoe UI',Arial,sans-serif;font-size:15px;color:#FFFFFF;
                    text-decoration:none;">
            {$ctaTexto}
          </a>
        </td>
      </tr>
HTML;
                }

                return <<<HTML
      <tr>
        <td align="center" style="padding:20px 0 0 0;">
          <a href="https://kasu.com.mx" style="text-decoration:none;color:whitesmoke;">
            <img src="https://kasu.com.mx/assets/images/Correo/florredonda.png"
                 alt="KASU" width="90" height="90" style="display:block;" />
          </a>
        </td>
      </tr>
      <tr>
        <td bgcolor="#ffffff" align="left"
            style="padding:20px 24px 8px 24px;font-family:'Segoe UI',sans-serif;
                   font-size:16px;line-height:24px;color:#0f172a;">
          <p style="margin:0 0 8px 0;">Hola <strong>{$nombrePros}</strong>,</p>
        </td>
      </tr>
      <tr>
        <td bgcolor="#ffffff" align="left"
            style="padding:4px 24px 8px 24px;font-family:'Segoe UI',sans-serif;
                   font-size:15px;line-height:24px;color:#475569;">
          {$cuerpoSan}
        </td>
      </tr>
      {$ctaBloque}
      <tr>
        <td bgcolor="#ffffff" align="left"
            style="padding:8px 24px 24px 24px;font-family:'Segoe UI',sans-serif;
                   font-size:14px;line-height:22px;color:#64748b;">
          <p style="margin:0;">
            Saludos,<br>
            <strong>Equipo KASU</strong>
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
     * Firma compatible:
     *  - Nueva:  Mensaje(string $Asunto, array $data, string $Id=''): string
     *  - Antigua:Mensaje(string $Asunto, string $Cte, string $DirUrl, ... , string $Id=''): string
     *
     * @return string
     */
    public function Mensaje($Asunto /* , ... */) {
        $this->trackUsage();

        $args = func_get_args();

        // Caso NUEVO: segundo argumento es array asociativo
        if (isset($args[1]) && is_array($args[1])) {
            $data = $args[1];
            $Id   = isset($args[2]) ? (string)$args[2] : '';
            $header = $this->mailHeader();
            $body   = $this->mailBody($Asunto, $data, $Id);
            $footer = $this->mailFooter($Id);
            return $header . $body . $footer;
        }

        // Caso ANTIGUO: parámetros sueltos
        $cte    = isset($args[1]) ? (string)$args[1] : '';
        $dirUrl = isset($args[2]) ? (string)$args[2] : '';
        // Último parámetro no vacío podría ser Id
        $Id = '';
        if (!empty($args)) {
            $rev = array_reverse($args, true);
            foreach ($rev as $v) {
                if (is_string($v) && $v !== '' && $v !== $Asunto && $v !== $cte && $v !== $dirUrl) {
                    $Id = $v;
                    break;
                }
            }
        }

        $data = [
            'Cte'    => $cte,
            'DirUrl' => $dirUrl,
            'Nombre' => $cte
        ];

        $header = $this->mailHeader();
        $body   = $this->mailBody($Asunto, $data, $Id);
        $footer = $this->mailFooter($Id);
        return $header . $body . $footer;
    }

    /**
     * Envía un correo simple en HTML.
     * Firma conservadora usada en el sistema:
     *   EnviarCorreo(string $Cte, string $address, string $asunto, string $html): bool
     */
    public function EnviarCorreo($cte, $address, $asunto, $html) {
        $this->trackUsage();
        $to = (string)$address;
        if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) return false;

        // Encabezados mínimos para HTML
        $headers = [];
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-Type: text/html; charset=UTF-8";
        $headers[] = "From: KASU <no-reply@kasu.com.mx>";
        $headers[] = "Reply-To: soporte@kasu.com.mx";

        return @mail($to, (string)$asunto, (string)$html, implode("\r\n", $headers));
    }

    /**
     * Envía el archivo indicado para descarga.
     * @param string $nombre_archivo Nombre del archivo con extension.
     */
    public function descargar($nombre_archivo) {
        $this->trackUsage();
        if (empty($nombre_archivo)) exit();
        $root = "ArchivosKasu/";
        $file = basename((string)$nombre_archivo);
        $path = $root . $file;

        if (!is_file($path)) {
            echo "<script>alert('El archivo no existe');</script>";
            return;
        }

        if (function_exists('ob_get_level')) {
            while (ob_get_level() > 0) { ob_end_clean(); }
        }

        $type = function_exists('mime_content_type') ? mime_content_type($path) : "application/octet-stream";

        header('Content-Description: File Transfer');
        header("Content-Type: $type");
        header('Content-Transfer-Encoding: binary');
        header('Accept-Ranges: bytes');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: public');
        header('Expires: 0');
        header('Content-Length: ' . filesize($path));
        header('Content-Disposition: attachment; filename="' . $file . '"');

        $fp = fopen($path, 'rb');
        if ($fp) {
            while (!feof($fp)) {
                echo fread($fp, 8192);
            }
            fclose($fp);
        } else {
            readfile($path);
        }
        exit;
    }
}
?>
