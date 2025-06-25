<header class="header-area header-sticky">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <nav class="main-nav" style="color:black;">
                    <!-- ***** Logo Start ***** -->
                    <a href="/" class="logo">
                      <img src="../assets/images/Index/ksulogo.png" name="Logo" alt="Kasu Logo" />
                    </a>
                    <!-- ***** Logo End ***** -->
                    <!-- ***** Menu Start ***** -->
                    <ul class="nav">
                        <?
                        if($_SERVER['PHP_SELF'] != "/index"){
                          echo '<li ><a style="color:black;" href="/prospectos" class="comprar"><strong>+ Informacion</strong></a></li>';
                        }
                        ?>
                        <!--li><a href="materiales.php">Materiales Gratuitos</a></li-->
                        <li><a style="color:black;" href="/blog" target="#">Blog</a></li>
                        <li><a  style="color:black;" href="/nft">NFT</a></li>
                        <?
                        if($_SERVER['PHP_SELF'] != "/fundacion"){
                          echo '<li><a style="color:black;" href="fundacion">Fundación</a></li>';
                        }
                        if($_SERVER['PHP_SELF'] == "/index"){
                          echo '<li><a style="color:black;" href="#Clientes" class="comprar">Clientes</a></li>';
                        }
                        ?>
                    </ul>
                    <a class='menu-trigger'>
                        <span >Menú</span>
                    </a>
                    <!-- ***** Menu End ***** -->
                </nav>
            </div>
        </div>
    </div>
</header>
