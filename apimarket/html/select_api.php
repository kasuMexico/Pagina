<?
if($_SERVER['PHP_SELF'] == "/index.php"){
  $doc_customer = "documentacion/doc_customer.php";
  $doc_payments = "documentacion/doc_payments.php";
  $doc_accounts = "documentacion/doc_accounts.php";
}else{
  $doc_customer = "../documentacion/doc_customer.php";
  $doc_payments = "../documentacion/doc_payments.php";
  $doc_accounts = "../documentacion/doc_accounts.php";
}
?>
<div class="container">
  <div class="row">
          <div class="col-lg-4 col-md-6 col-sm-6 col-16" data-scroll-reveal="enter bottom move 50px over 0.6s after 0.2s">
              <div class="features-small-item">
                <div class="section-title">
                    <h2><strong>API_CUSTOMER</strong></h2>
                </div>
                <br>
                  <p>La <strong>API_CUSTOMER</strong> permite a clientes de <strong>KASU</strong> compartir su información en un solo clic. Evita los interminables formularios de registro con datos clave, veraces y actualizados. Elimina fricciones y mejora la experiencia de tus clientes.</p>
                  <div class="consulta">
                      <br>
                      <a class="btn btn-info" href="<? echo $doc_customer;?>">Ir a la documentación</a>
                  </div>
              </div>
          </div>
          <div class="col-lg-4 col-md-6 col-sm-6 col-16" data-scroll-reveal="enter bottom move 50px over 0.6s after 0.2s">
              <div class="features-small-item">
                <div class="section-title">
                    <h2><strong>API_PAYMENTS</strong></h2>
                </div>
                <br>
                  <p>Con <strong>API_PAYMENTS</strong> Puedes confirmar en tiempo real los pagos que los clientes <strong>KASU</strong> realicen en tu negocio. De esta forma, puedes generar interesantes comisiones. También puedes mostrar el adeudo para facilitar su cobranza al momento.</p>
                  <div class="consulta">
                      <br>
                      <a class="btn btn-info" href="<? echo $doc_payments;?>">Ir a la documentación</a>
                  </div>
              </div>
          </div>
          <div  class="col-lg-4 col-md-6 col-sm-6 col-16" data-scroll-reveal="enter bottom move 50px over 0.6s after 0.2s">
              <div class="features-small-item">
                    <div class="section-title">
                        <h2><strong>API_ACCOUNTS</strong></h2>
                    </div>
                    <br>
                      <p>Con la <strong>API_ACCOUNTS</strong> podrás ofrecer la apertura de servicios <strong>KASU</strong> en tu app o plataforma digital en cuestión de minutos. Fideliza a tus clientes, empleados o proveedores generando en el camino interesantes comisiones.</p>
                  <div class="consulta">
                      <br>
                      <a class="btn btn-info" href="<? echo $doc_accounts;?>">Ir a la documentación</a>
                  </div>
              </div>
          </div>
      </div>
  </div>
