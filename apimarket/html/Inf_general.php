<br><br><br>
<!-- ***** Descripción General de la API ***** -->
<section class="section padding-top-70" id="">
  <div class="container">
    <div class="row">
      <div class="col-lg-4 col-md-12 col-sm-12 align-self-center" data-scroll-reveal="enter left move 30px over 0.6s after 0.4s">
        <div class="features-small-item">
          <div class="Consulta">
            <h2 class="titulos"><strong>DESCRIPCION</strong></h2>
            <br>
            <p style="text-align: justify;"><? echo $Reg['Descripcion'];?></p>
          </div>
        </div>
      </div>
      <div class="col-lg-2 col-md-12 col-sm-12 align-self-center" ></div>
      <div class="col-lg-6 col-md-12 col-sm-12 align-self-center" data-scroll-reveal="enter right move 30px over 0.6s after 0.4s">
        <div class="row">
          <table class="table table-responsive">
            <tr>
              <td><strong>Nombre Api:</strong></td>
              <td><? echo $Reg['Nombre'];?></td>
            </tr>
            <tr>
              <td><strong>Versión: </strong></td>
              <td><? echo $Reg['Version'];?></td>
            </tr>
            <tr>
              <td><strong>Protocolo: </strong></td>
              <td>HTTP</td>
            </tr>
            <tr>
              <td><strong>URI Live: </strong></td>
              <td><? echo $Reg['Live'];?></td>
            </tr>
            <tr>
              <td><strong>URI Sandbox: </strong></td>
              <td><? echo $Reg['Sandbox'];?></td>
            </tr>
          </table>
        </div>
      </div>
    </div>
  </div>
</section>
