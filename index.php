<?php

if(isset($_POST['acessar']))
{
    require_once('config.php');
    try
    {
        $autenticar = $pdo->prepare("select * from `ct_usuario` where `username` = :users and `password` = :pass");
        $autenticar->execute(
            array(
                ":users"  => addslashes( $_POST['username'] ),
                ":pass"  =>  addslashes( md5( $_POST['pass'] ) )
            )
        );
        $dados = $autenticar->fetch(PDO::FETCH_ASSOC);
        $count = $autenticar->rowCount();
        if( $count == 1 and $dados['username'] == $_POST['username'] and $dados['bloqueado'] == 0 )
        {
            $_SESSION['idresponsavel'] = $dados['id'];
            $_SESSION['id']       = $dados['id'];
            $_SESSION['nome']     = $dados['firstname']." ".$dados['lastname'];
            $_SESSION['email']    = $dados['email'];
            $_SESSION['img']      = $dados['img'];
            $_SESSION['tempo']    = date("i");
            //die(var_dump($_SESSION['idresponsavel']));
            $update_lastaccess = $pdo->prepare('update `ct_usuario` set `lastaccess` = :lastaccess where id = :id ');
            $update_lastaccess->execute(array(":lastaccess" => date("Y-m-d H:i:s"), ":id" => $dados['id']));
            if($dados['idpermission'] == 1)
            {
                $_SESSION['idoperador'] = $dados['idpermission'];
                header("location: adm/index ");
            }
            elseif($dados['idpermission'] == 2  )
            {
                $_SESSION['idgerente']   = $dados['idpermission'];
                header("location: adm/home ");
            }

            elseif($dados['idpermission'] == 3)
            {
                $_SESSION['idfaturador']   = $dados['idpermission'];
                header("location: adm/index ");
            }

            elseif($dados['idpermission'] == 4)
            {
                $_SESSION['idreservamanager']   = $dados['idpermission'];
                header("location: adm/index ");
            }
            elseif($dados['idpermission'] == 5)
            {
                $_SESSION['idmapservice']   = $dados['idpermission'];
                header("location: mapservice/index ");
            }
            elseif($dados['idpermission'] == 6)
            {
                $_SESSION['idreservaplus']   = $dados['idpermission'];
                header("location: adm/index ");
            }
            elseif($dados['idpermission'] == 7)
            {
                $_SESSION['idbaixa']   = $dados['idpermission'];
                header("location: adm/index ");
            }
            elseif($dados['idpermission'] == 10)
            {
                $_SESSION['idcaixa']   = $dados['idpermission'];
                header("location: adm/index ");
            }
            elseif($dados['idpermission'] == 11)
            {
                $_SESSION['idpagarreserva']   = $dados['idpermission'];
                header("location: adm/index ");
            }
            elseif($dados['idpermission'] == 12)
            {
                $_SESSION['idfinanceiro2']   = $dados['idpermission'];
                header("location: adm/index ");
            }
            elseif($dados['idpermission'] == 13)
            {
                $_SESSION['folhaderosto']   = $dados['idpermission'];
                header("location: adm/index ");
            }
            elseif($dados['idpermission'] == 14)
            {
                $_SESSION['comissao']   = $dados['idpermission'];
                header("location: adm/index ");
            }
            elseif($dados['idpermission'] == 15)
            {
                $_SESSION['comissaorelatoriofolha']  = $dados['idpermission'];
                header("location: adm/index ");
            }
            else{
                echo("<div class='alert alert-danger' role='alert'>Não encontramos o seu usuário</div>");

            }
        }
        elseif ($dados['bloqueado'] == 1)
        {
            echo("<div class='alert alert-warning' role='alert'>Usuário Bloqueado entre em contato com o Administrador</div>");
        }
        else
            {
                echo("<div class='alert alert-danger' role='alert'>Não encontramos o seu usuário</div>");
            }
    }catch (Exception $e){
        echo("<div class='alert alert-danger' role='alert'>Não encontramos o seu usuário</div>");
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
	<title>CASSI TURISMO </title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
<!--===============================================================================================-->	
	<link rel="icon" type="image/png" href="images/icons/favicon.ico"/>
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="vendor/bootstrap/css/bootstrap.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="fonts/font-awesome-4.7.0/css/font-awesome.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="fonts/iconic/css/material-design-iconic-font.min.css">

<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="css/util.css">
	<link rel="stylesheet" type="text/css" href="css/main.css">
<!--===============================================================================================-->
</head>
<body>
	
	
	<div class="container-login100" style="background-image: url('images/fundo.jpg');">
		<div class="wrap-login100 p-l-20 p-r-20 p-t-80 p-b-10">
			<form class="login100-form validate-form" action="" method="post">
				<span class="login100-form-title p-b-50">
					<img src="images/logo.png" width="320">
				</span>

				<div class="wrap-input100 validate-input m-b-20" data-validate="Você precisar informar seu nome de usuário">
                    <input class="input100" type="text" required autocomplete="off" readonly
                           onfocus="this.removeAttribute('readonly');" name="username" placeholder="Usuário">
					<span class="focus-input100"></span>
				</div>

				<div class="wrap-input100 validate-input m-b-25" data-validate = "Você precisa informar sua senha de acesso">
					<input class="input100" type="password" autocomplete="off" readonly
                           onfocus="this.removeAttribute('readonly');" required name="pass" placeholder="Senha">
					<span class="focus-input100"></span>
				</div>

				<div class="container-login100-form-btn">
					<button name="acessar" type="submit" id="bt_login"  class="login100-form-btn" style="background-color: blue;">
						Entrar
					</button>
				</div>


				<div class="flex-c p-b-70">

				</div>

				<div class="text-center">
				</div>
			</form>

			
		</div>
	</div>
	
	

	<div id="dropDownSelect1"></div>
	
<!--===============================================================================================-->
	<script src="vendor/jquery/jquery-3.2.1.min.js"></script>

	<script src="vendor/bootstrap/js/bootstrap.min.js"></script>
<!--===============================================================================================-->
	<script src="js/main.js"></script>
</body>
</html>