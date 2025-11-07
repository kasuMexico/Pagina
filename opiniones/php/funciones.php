<?php
require_once('../../eia/php/Funciones_kasu.php');
$mnsAlert = "";
if($_POST['btnEnvOpn'] == "Enviar"){
    if($_POST['txtNomOpn'] != ""){
        if($_POST['cbxSerOpn'] != ""){
            if($_POST['txtComOpn'] != ""){
                if(isset($_FILES['uplFotOpn']) && $_FILES['uplFotOpn']['error'] === UPLOAD_ERR_OK){
                    $fileTmpPath = $_FILES['uplFotOpn']['tmp_name'];
                    $fileName = $_FILES['uplFotOpn']['name'];
                    $fileSize = $_FILES['uplFotOpn']['size'];
                    $fileType = $_FILES['uplFotOpn']['type'];
                    $fileNameCmps = explode(".", $fileName);
                    $fileExtension = strtolower(end($fileNameCmps));

                    $txtNomOpn = strtoupper($_POST['txtNomOpn']);
                    $cbxSerOpn = $_POST['cbxSerOpn'];
                    $txtComOpn = $_POST['txtComOpn'];

                    // se agregan las extenciones permitidas
                    $allowedfileExtensions = array('jpg','jpeg','png','bmp');
                    if (in_array($fileExtension, $allowedfileExtensions)){
                        // directory in which the uploaded file will be moved
                        $uploadFileDir = '../ImgPerfil/';
                        $dest_path = $uploadFileDir .$txtNomOpn.'.'.$fileExtension;
                        if(move_uploaded_file($fileTmpPath, $dest_path)){
                            $senSQLOpn = "INSERT INTO opiniones (Nombre, Servicio, Opinion, foto)VALUES ('".$txtNomOpn."','".$cbxSerOpn."','".$txtComOpn."','https://kasu.com.mx/opiniones/ImgPerfil/".$txtNomOpn.".".$fileExtension."');";
                            if(!mysqli_query($mysqli,$senSQLOpn)){
                                echo'<script type="text/javascript">alert("'.mysqli_error($mysqli).'");</script>';
                                header("Refresh: 0; URL=/opiniones/");
                            }else{
                                echo'<script type="text/javascript">alert("Su opinion ha sido guardada");</script>';
                                header("Refresh: 0; URL=/opiniones/");
                            }
                        }else{
                            $mnsAlert = "No se subio archivo";
                        }
                    }else{
                        $mnsAlert = "Archivo no permitido";
                    }
                }else{
                    $mnsAlert = "Suba la foto de perfil";
                }
            }else{
                $mnsAlert = "Escriba un comentario";
            }
        }else{
            $mnsAlert = "Elija un Servicio";
        }
    }else{
        $mnsAlert = "Ingrese un nombre";
    }
    echo'<script type="text/javascript">alert("'.$mnsAlert.'");</script>';
    //header("Refresh: 0; URL=/opiniones/");
}
?>
