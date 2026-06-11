<?php
/**
 * Respuestas públicas basadas en el contrato de prestación de servicios funerarios a futuro.
 * La póliza y el contrato individual prevalecen sobre este resumen informativo.
 */
session_start();
require_once __DIR__ . '/eia/analytics_bootstrap.php';
$VerCache = (string) (filemtime(__FILE__) ?: time());
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
  <meta charset="utf-8">
  <title>Preguntas frecuentes sobre planes funerarios KASU</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Respuestas claras sobre activación, cobertura, Fideicomiso F/0003, uso y condiciones de los servicios funerarios a futuro KASU.">
  <meta name="robots" content="index,follow,max-snippet:-1,max-image-preview:large,max-video-preview:-1">
  <meta name="author" content="KASU Servicios a Futuro">
  <link rel="canonical" href="https://kasu.com.mx/preguntas-frecuentes">
  <link rel="alternate" href="https://kasu.com.mx/preguntas-frecuentes" hreflang="es-MX">
  <link rel="alternate" href="https://kasu.com.mx/preguntas-frecuentes" hreflang="x-default">

  <meta property="og:type" content="website">
  <meta property="og:locale" content="es_MX">
  <meta property="og:site_name" content="KASU">
  <meta property="og:title" content="Preguntas frecuentes sobre KASU">
  <meta property="og:description" content="Conoce cómo se activa y utiliza un servicio funerario a futuro KASU.">
  <meta property="og:url" content="https://kasu.com.mx/preguntas-frecuentes">
  <meta property="og:image" content="https://kasu.com.mx/assets/images/guiafuneraria-512.png">
  <meta property="og:image:alt" content="Guía funeraria KASU">
  <meta name="twitter:card" content="summary_large_image">

  <link rel="icon" type="image/png" sizes="48x48" href="/assets/images/Index/florkasu-48.png">
  <link rel="apple-touch-icon" sizes="180x180" href="/assets/images/Index/florkasu-180.png">
  <link rel="stylesheet" type="text/css" href="/assets/css/fonts.css?v=<?php echo $VerCache; ?>">
  <link rel="stylesheet" type="text/css" href="/assets/css/bootstrap.min.css?v=<?php echo $VerCache; ?>">
  <link rel="stylesheet" type="text/css" href="/assets/css/font-awesome.css?v=<?php echo $VerCache; ?>">
  <link rel="stylesheet" type="text/css" href="/assets/css/kasu-menu.css?v=<?php echo $VerCache; ?>">
  <link rel="stylesheet" type="text/css" href="/assets/css/index-home.css?v=<?php echo $VerCache; ?>">
  <link rel="stylesheet" type="text/css" href="/assets/css/preguntas-frecuentes.css?v=<?php echo $VerCache; ?>">

  <script type="application/ld+json">
  {
    "@context":"https://schema.org",
    "@type":"FAQPage",
    "name":"Preguntas frecuentes sobre planes funerarios KASU",
    "url":"https://kasu.com.mx/preguntas-frecuentes",
    "inLanguage":"es-MX",
    "dateModified":"2026-06-11",
    "publisher":{
      "@type":"Organization",
      "name":"KASU Servicios a Futuro",
      "url":"https://kasu.com.mx/",
      "logo":"https://kasu.com.mx/assets/images/Index/florkasu.png"
    },
    "mainEntity":[
      {
        "@type":"Question",
        "name":"¿Cuándo se activa un servicio funerario KASU?",
        "acceptedAnswer":{"@type":"Answer","text":"El contrato indica que el servicio entra en vigencia después de liquidar totalmente el precio y transcurrir 30 días naturales posteriores a la liquidación y activación en el fideicomiso."}
      },
      {
        "@type":"Question",
        "name":"¿Qué incluye el servicio funerario?",
        "acceptedAnswer":{"@type":"Answer","text":"Según el contrato, puede incluir traslados dentro del límite contratado, sala y equipo de velación, cafetería, acondicionamiento del cuerpo, ataúd o urna, trámites y cremación cuando haya sido seleccionada. La póliza individual determina el servicio contratado."}
      },
      {
        "@type":"Question",
        "name":"¿Cómo se solicita el servicio?",
        "acceptedAnswer":{"@type":"Answer","text":"Se debe contactar a KASU mediante los datos indicados en la póliza o desde kasu.com.mx y proporcionar la CURP a la funeraria designada para validar al titular."}
      },
      {
        "@type":"Question",
        "name":"¿Qué relación tiene el servicio con el Fideicomiso F/0003?",
        "acceptedAnswer":{"@type":"Answer","text":"La póliza expedida después de la liquidación acredita al titular como beneficiario del Fideicomiso F/0003. El documento señala un respaldo máximo equivalente a 2,600 UDI, menos impuestos y costos de administración, sujeto al contrato del fideicomiso y sus reglas operativas."}
      },
      {
        "@type":"Question",
        "name":"¿El servicio es transferible?",
        "acceptedAnswer":{"@type":"Answer","text":"No. La documentación contractual consultada indica que el servicio no es transferible."}
      },
      {
        "@type":"Question",
        "name":"¿Se puede cambiar el servicio o la funeraria?",
        "acceptedAnswer":{"@type":"Answer","text":"El contrato y su anexo de preguntas indican que se puede solicitar el cambio con un ejecutivo KASU o en sucursal, sujeto al límite de costo y antes del fallecimiento del titular."}
      }
    ]
  }
  </script>
</head>
<body class="kasu-ui faq-page">
  <?php require_once __DIR__ . '/html/MenuPrincipal.php'; ?>

  <main>
    <section class="faq-hero">
      <div class="container">
        <p class="faq-eyebrow">Información para decidir con claridad</p>
        <h1>Preguntas frecuentes sobre KASU</h1>
        <p class="faq-intro">Respuestas basadas en el contrato de prestación de servicios funerarios a futuro y la póliza KASU. Revisa siempre las condiciones específicas de tu contratación.</p>
        <p class="faq-reviewed">Última revisión del contenido: 11 de junio de 2026.</p>
        <div class="faq-actions">
          <a class="faq-button faq-button--primary" href="/productos/gastos-funerarios">Conocer el plan funerario</a>
          <a class="faq-button" href="/Fideicomiso_F0003.pdf" target="_blank" rel="noopener">Consultar Fideicomiso F/0003</a>
        </div>
      </div>
    </section>

    <section class="faq-content" aria-labelledby="faq-compra">
      <div class="container faq-layout">
        <aside class="faq-summary" aria-label="Resumen importante">
          <h2>Antes de contratar</h2>
          <ul>
            <li>Confirma quién será el titular.</li>
            <li>Revisa el servicio seleccionado y sus límites.</li>
            <li>Conserva contrato, recibos y póliza.</li>
            <li>Verifica datos y CURP del titular.</li>
          </ul>
          <p><strong>Importante:</strong> esta página es un resumen informativo. La póliza, el contrato individual y las reglas operativas aplicables son los documentos que prevalecen.</p>
        </aside>

        <div class="faq-groups">
          <section class="faq-group">
            <h2 id="faq-compra">Compra y activación</h2>

            <details open>
              <summary>¿Qué es un servicio funerario a futuro KASU?</summary>
              <div class="faq-answer">
                <p>Es una contratación para que, al momento de necesitarlo, se presten los servicios funerarios definidos en la póliza y el contrato. La prestación puede realizarse por KASU, empresas relacionadas o terceros autorizados, según el contrato.</p>
              </div>
            </details>

            <details>
              <summary>¿Es lo mismo que un seguro?</summary>
              <div class="faq-answer">
                <p>No. La documentación consultada lo identifica como un servicio a futuro: al hacerse válido se presta directamente el servicio funerario contratado, conforme a la póliza y al contrato.</p>
              </div>
            </details>

            <details>
              <summary>¿Cuándo se activa el servicio funerario?</summary>
              <div class="faq-answer">
                <p>El contrato indica que el servicio entra en vigencia una vez liquidado totalmente el precio y después de que transcurran <strong>30 días naturales</strong> posteriores a la liquidación y activación en el fideicomiso.</p>
              </div>
            </details>

            <details>
              <summary>¿Cuándo se expide la póliza?</summary>
              <div class="faq-answer">
                <p>La documentación consultada señala que la póliza se expide cuando el precio del servicio ha sido pagado en su totalidad y que no se emite mientras exista saldo pendiente.</p>
              </div>
            </details>

            <details>
              <summary>¿Qué debo revisar antes de contratar?</summary>
              <div class="faq-answer">
                <p>Revisa el nombre y CURP del titular, el tipo de servicio seleccionado, precio, forma de pago, fecha de activación, límites, exclusiones y causas de terminación. Solicita que cualquier duda quede aclarada antes de firmar.</p>
              </div>
            </details>
          </section>

          <section class="faq-group">
            <h2>Servicio, cobertura y uso</h2>

            <details>
              <summary>¿Qué incluye el servicio funerario?</summary>
              <div class="faq-answer">
                <p>El contrato contempla traslados dentro del límite contratado, sala y equipo de velación, cafetería, acondicionamiento del cuerpo, ataúd o urna, trámites y cremación cuando este tipo de servicio haya sido seleccionado. La póliza individual define exactamente lo contratado.</p>
              </div>
            </details>

            <details>
              <summary>¿Cuál es el límite de traslado?</summary>
              <div class="faq-answer">
                <p>El contrato consultado contempla traslados que no excedan <strong>60 kilómetros</strong>. Los gastos extraordinarios por exceder ese límite deben ser cubiertos por el cliente o sus familiares, conforme a las condiciones aplicables.</p>
              </div>
            </details>

            <details>
              <summary>¿Cómo se solicita el servicio cuando se necesita?</summary>
              <div class="faq-answer">
                <p>Contacta a KASU mediante los teléfonos indicados en tu póliza o desde <a href="/">kasu.com.mx</a>. La plataforma coordina la atención y la funeraria designada solicita la CURP para validar al titular.</p>
              </div>
            </details>

            <details>
              <summary>¿KASU presta directamente todos los servicios?</summary>
              <div class="faq-answer">
                <p>El contrato permite que el servicio sea prestado por KASU, empresas relacionadas o terceros autorizados y subcontratados, según el tipo de servicio y la operación requerida.</p>
              </div>
            </details>

            <details>
              <summary>¿El servicio es transferible?</summary>
              <div class="faq-answer">
                <p>No. La documentación contractual consultada indica que el servicio no es transferible.</p>
              </div>
            </details>
          </section>

          <section class="faq-group">
            <h2>Fideicomiso, cambios y condiciones</h2>

            <details>
              <summary>¿Qué relación tiene mi póliza con el Fideicomiso F/0003?</summary>
              <div class="faq-answer">
                <p>La póliza expedida después de la liquidación acredita al titular como beneficiario del Fideicomiso F/0003. El documento señala un respaldo de servicio funerario de hasta un máximo equivalente a <strong>2,600 UDI</strong> al momento de exigibilidad, menos impuestos y costos de administración, sujeto al contrato del fideicomiso y sus reglas operativas.</p>
                <p><a href="/Fideicomiso_F0003.pdf" target="_blank" rel="noopener">Consulta el documento del Fideicomiso F/0003</a>.</p>
              </div>
            </details>

            <details>
              <summary>¿Puedo cambiar el servicio o la funeraria?</summary>
              <div class="faq-answer">
                <p>La documentación consultada indica que puedes solicitar un cambio con un ejecutivo KASU o en sucursal, sujeto al límite de costo del servicio. El tipo de servicio no puede cambiarse después del fallecimiento del titular.</p>
              </div>
            </details>

            <details>
              <summary>¿Qué puede causar la terminación del contrato?</summary>
              <div class="faq-answer">
                <p>Entre las causas señaladas están la falta de pago por más de 60 días naturales, acumular más de tres atrasos, incumplir las obligaciones contractuales o el acuerdo voluntario de las partes. El contrato consultado también indica que la cancelación del servicio no genera reembolso, salvo la excepción que señala para el servicio universitario. Confirma cómo aplica esta condición a tu contratación.</p>
              </div>
            </details>

            <details>
              <summary>¿Existen exclusiones o situaciones en las que puede reservarse el servicio?</summary>
              <div class="faq-answer">
                <p>Sí. La documentación menciona incumplimientos u omisiones en la información y determinados eventos extraordinarios, incluidos conflictos armados, catástrofe o calamidad nacional y eventos nucleares o radiactivos. Consulta la póliza y el contrato para conocer el alcance aplicable.</p>
              </div>
            </details>

            <details>
              <summary>¿El contrato tiene una fecha de vencimiento?</summary>
              <div class="faq-answer">
                <p>La cláusula de duración del contrato consultado lo define por tiempo indefinido, sujeto a sus condiciones, obligaciones y causas de terminación.</p>
              </div>
            </details>

            <details>
              <summary>¿Dónde consulto mis condiciones exactas?</summary>
              <div class="faq-answer">
                <p>Consulta tu póliza y contrato individual, porque ahí se identifica al titular, el servicio elegido y sus condiciones. También puedes revisar el <a href="/Fideicomiso_F0003.pdf" target="_blank" rel="noopener">Fideicomiso F/0003</a>, el <a href="/privacidad">aviso de privacidad</a> y los <a href="/terminos-y-condiciones">términos del sitio</a>.</p>
              </div>
            </details>
          </section>
        </div>
      </div>
    </section>

    <section class="faq-contact">
      <div class="container">
        <div>
          <p class="faq-eyebrow">¿Necesitas revisar un caso específico?</p>
          <h2>Habla con atención a clientes KASU</h2>
          <p>Ten a la mano la CURP del titular y los datos de la póliza.</p>
        </div>
        <a class="faq-button faq-button--primary" href="/prospectos">Solicitar información</a>
      </div>
    </section>
  </main>

  <footer class="site-footer">
    <?php require_once __DIR__ . '/html/footer.php'; ?>
  </footer>

  <script src="/assets/js/jquery-2.1.0.min.js"></script>
  <script src="/assets/js/bootstrap.min.js" defer></script>
  <script src="/assets/js/custom.js" defer></script>
</body>
</html>
