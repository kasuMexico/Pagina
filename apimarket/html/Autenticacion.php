<!-- ***** AUTENTICACION ***** -->
<section class="section padding-top-70" id="Autentica">
  <div class="container">
    <div class="Consulta">
        <h2 class="titulos"><strong>AUTENTICACION</strong></h2>
        <p><strong>token_full</strong></p>
        <br>
        <p>Esta API está protegida por el protocolo de autenticación abierta <strong>OAuth</strong> de dos vias. Después del protocolo de enlace de <strong>OAuth</strong>, se otorga un token de <strong>OAuth</strong> válido para acceder a los diferentes puntos finales de la API en nombre de un usuario de KASU.</p>
        <br>
    </div>
    <div class="row">
      <div class="col-lg-5 col-md-12 col-sm-12 align-self-center" data-scroll-reveal="enter left move 30px over 0.6s after 0.4s">
        <div class="row">
          <div class="Consulta">
            <p style="text-align: justify;">La peticion debe ser por metodo <strong>POST</strong> y el cuerpo de la solicitud debe estar en formato <strong>(Content-Type: application/json)</strong> y debe contener los siguientes parámetros:</p>
            <br>
          </div>
          <table class="table table-responsive justify">
            <tr>
              <td><strong>Parámetro</strong></td>
              <td><strong>Descripción</strong></td>
            </tr>
            <tr>
              <td>Tipo_Peticion</td>
              <td style="text-align: justify;">Especifica el tipo de petición, debe ser establecido segun las tablas de acceso</td>
            </tr>
            <tr>
              <td>YOUR_APPUSER</td>
              <td style="text-align: justify;">Tu nombre de usuario registrado en la aplicación KASU.</td>
            </tr>
            <tr>
              <td>Firma_KEY</td>
              <td style="text-align: justify;">Firma la clave CURP de tu cliente con tu <strong>PRIVATE_KEY</strong> mediante el algoritmo criptográfico HMAC.</td>
            </tr>
            <tr>
              <td>curp_en_uso</td>
              <td style="text-align: justify;">La clave CURP de el cliente con el que interactuaras se liga a la peticion.</td>
            </tr>
          </table>
        </div>
      </div>
      <div class="col-lg-1 col-md-12 col-sm-12 align-self-center" ></div>
      <div class="col-lg-6 col-md-12 col-sm-12 align-self-center" data-scroll-reveal="enter right move 30px over 0.6s after 0.4s">
        <!-- ***** REGISTRAR EL CODIGO DE ENVIO DE EL TOKEN INICIAL  ***** -->
        <div class="code-window">
          <pre id="codecopindex" class="userContent" style="white-space: pre-wrap;">
            <code>
POST https://apimarket.kasu.com.mx/api/Registro_V1

  Headers:
  Content-Type: application/json
  User-Agent: Your-Application-Name/1.0
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
<section class="section padding-top-70 colored" id="">
  <div class="container">
    <div class="Consulta">
        <h2 class="titulos"><strong>REGISTRO DE DATOS DE AUTENTICACION</strong></h2>
        <br>
        <p>Resuelve los datos de las peticiones con codigos de error cuando no resuelve correctamente la <strong>API_REGISTRO</strong>, y requiere intrucciones que le indiquen que funcion ejecutar, aqui podras encontrar aambas para que puedas determinar el mejor funcionamiento de tu implementacion</p>
        <br>
    </div>
    <div class="row">
      <div class="col-lg-5 col-md-12 col-sm-12 align-self-center" data-scroll-reveal="enter left move 30px over 0.6s after 0.4s">
        <!-- ***** REGISTRAR EL CODIGO DE ENVIO DE EL TOKEN INICIAL  ***** -->
        <div class="row">
          <table class="table table-responsive justify">
            <tr>
              <td><strong>PETICION</strong></td>
              <td><strong>DESCRIPCION</strong></td>
            </tr>
            <tr>
              <td>token_full</td>
              <td style="text-align: justify;">Solicita la generacion de un token de autorizacion de usuo con una vigencia de 10 munitos.</td>
            </tr>
            <tr>
              <td></td>
              <td></td>
            </tr>
          </table>
        </div>
        <div class="row">
          <table class="table table-responsive justify">
            <tr>
              <td><strong>CODIGO</strong></td>
              <td><strong>ERRORES DE PETICION</strong></td>
            </tr>
            <tr>
              <td>403</td>
              <td style="text-align: justify;">Las credenciales son inválidas, o el usuario no existe</td>
            </tr>
          </table>
        </div>
      </div>
      <div class="col-lg-1 col-md-12 col-sm-12 align-self-center" ></div>
      <div class="col-lg-6 col-md-12 col-sm-12 align-self-center" data-scroll-reveal="enter right move 30px over 0.6s after 0.4s">
        <!-- ***** REGISTRAR EL CODIGO DE ENVIO DE EL TOKEN INICIAL  ***** -->
        <div class="row">
          <div class="Consulta">
            <p style="text-align: justify;">Cuando la <strong>API_REGISTRO</strong> retorna una respuesta positiva retorna los siguientes valores:</p>
            <br>
          </div>
          <table class="table table-responsive justify">
            <tr>
              <td><strong>LLAVE</strong></td>
              <td><strong>DESCRIPCION</strong></td>
            </tr>
            <tr>
              <td>token</td>
              <td style="text-align: justify;"><strong>TOKEN</strong> generado por la <strong>API_REGISTRO</strong>.</td>
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
</section>
