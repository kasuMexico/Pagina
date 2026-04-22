<!-- ***** AUTENTICACION ***** -->
<section class="doc-section" id="Autentica">
  <div class="container">
    <div class="doc-heading">
        <span class="api-kicker">Autenticación</span>
        <h2>Token_Full y Bearer token</h2>
        <p>Flujo de autenticación: firma <strong>HMAC</strong>, generación de <strong>token_full</strong> y consumo de APIs V1 con <strong>Authorization: Bearer</strong>. Los ejemplos específicos de consumo están en la documentación de cada API.</p>
    </div>
    <div class="row">
      <div class="col-lg-5 col-md-12 col-sm-12 align-self-center" data-scroll-reveal="enter left move 30px over 0.6s after 0.4s">
        <div class="row">
          <div class="Consulta">
            <p style="text-align: justify;">La petición debe ser por método <strong>POST</strong>; el cuerpo debe enviarse como <strong>Content-Type: application/json</strong>.</p>
            <br>
          </div>
          <div class="doc-table">
          <table class="table table-responsive justify">
            <tr>
              <td><strong>Parámetro</strong></td>
              <td><strong>Descripción</strong></td>
            </tr>
            <tr>
              <td>tipo_peticion</td>
              <td style="text-align: justify;">Debe enviarse como <strong>token_full</strong>.</td>
            </tr>
            <tr>
              <td>User-Agent</td>
              <td style="text-align: justify;">Identificador técnico asignado en <strong>Secret_KEY</strong>, con formato <strong>USUARIO_ID</strong>.</td>
            </tr>
            <tr>
              <td>YOUR_APPUSER</td>
              <td style="text-align: justify;">Tu nombre de usuario registrado en la aplicación KASU.</td>
            </tr>
            <tr>
              <td>Firma_KEY</td>
              <td style="text-align: justify;">Firma la CURP de tu cliente con tu <strong>PRIVATE_KEY</strong> mediante HMAC SHA-256.</td>
            </tr>
            <tr>
              <td>curp_en_uso</td>
              <td style="text-align: justify;">CURP que quedará ligada al token y a la petición.</td>
            </tr>
          </table>
          </div>
        </div>
      </div>
      <div class="col-lg-1 col-md-12 col-sm-12 align-self-center" ></div>
      <div class="col-lg-6 col-md-12 col-sm-12 align-self-center" data-scroll-reveal="enter right move 30px over 0.6s after 0.4s">
        <!-- ***** REGISTRAR EL CODIGO DE ENVIO DE EL TOKEN INICIAL  ***** -->
        <div class="code-window">
          <pre id="codecopindex" class="userContent" style="white-space: pre-wrap;">
            <code>
POST https://apimarket.kasu.com.mx/api/Token_Full

  Headers:
  Content-Type: application/json
  User-Agent: SECRET_KEY_USUARIO_SECRET_KEY_ID
  Body:
  {
    "tipo_peticion"     : "token_full",
    "nombre_de_usuario" : "YOUR_APPUSER",
    "firma_KEY"         : "FIRMA_KEY",
    "curp_en_uso"       : "CURP_CODE"
  }
            </code>
          </pre>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- ***** REGISTRO DE DATOS DE AUTENTICACION ***** -->
<section class="doc-section doc-section--muted" id="">
  <div class="container">
    <div class="doc-heading">
        <h2>Respuesta y errores de autenticación</h2>
        <p><strong>Token_Full</strong> resuelve la credencial de API Market V1 y retorna errores JSON cuando no puede generar el token.</p>
    </div>
    <div class="row">
      <div class="col-lg-5 col-md-12 col-sm-12 align-self-center" data-scroll-reveal="enter left move 30px over 0.6s after 0.4s">
        <!-- ***** REGISTRAR EL CODIGO DE ENVIO DE EL TOKEN INICIAL  ***** -->
        <div class="row">
          <div class="doc-table">
          <table class="table table-responsive justify">
            <tr>
              <td><strong>PETICIÓN</strong></td>
              <td><strong>DESCRIPCIÓN</strong></td>
            </tr>
            <tr>
              <td>token_full</td>
              <td style="text-align: justify;">Solicita la generación de un token de autorización con vigencia de 10 minutos.</td>
            </tr>
            <tr>
              <td></td>
              <td></td>
            </tr>
          </table>
          </div>
        </div>
        <div class="row">
          <div class="doc-table" style="margin-top:16px;">
          <table class="table table-responsive justify">
            <tr>
              <td><strong>CODIGO</strong></td>
              <td><strong>ERRORES DE PETICION</strong></td>
            </tr>
            <tr>
              <td>403</td>
              <td style="text-align: justify;">Credenciales inválidas, firma incorrecta o usuario inexistente.</td>
            </tr>
            <tr>
              <td>417</td>
              <td style="text-align: justify;">CURP inválida o no elegible.</td>
            </tr>
          </table>
          </div>
        </div>
      </div>
      <div class="col-lg-1 col-md-12 col-sm-12 align-self-center" ></div>
      <div class="col-lg-6 col-md-12 col-sm-12 align-self-center" data-scroll-reveal="enter right move 30px over 0.6s after 0.4s">
        <!-- ***** REGISTRAR EL CODIGO DE ENVIO DE EL TOKEN INICIAL  ***** -->
        <div class="row">
          <div class="Consulta">
            <p style="text-align: justify;">Cuando <strong>Token_Full</strong> retorna una respuesta positiva entrega los siguientes valores:</p>
            <br>
          </div>
          <div class="doc-table">
          <table class="table table-responsive justify">
            <tr>
              <td><strong>LLAVE</strong></td>
              <td><strong>DESCRIPCIÓN</strong></td>
            </tr>
            <tr>
              <td>token</td>
              <td style="text-align: justify;"><strong>TOKEN</strong> generado por API Market V1.</td>
            </tr>
            <tr>
              <td>nombre</td>
              <td style="text-align: justify;">Retorna el nombre completo de el cliente.</td>
            </tr>
            <tr>
              <td>timestamp</td>
              <td style="text-align: justify;">Dentro de <strong>token_data</strong> tiempo que se genero el <strong>TOKEN</strong></td>
            </tr>
            <tr>
              <td>expires_in</td>
              <td style="text-align: justify;">Dentro de <strong>token_data</strong> vigencia del <strong>TOKEN</strong> generado</td>
            </tr>
          </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
