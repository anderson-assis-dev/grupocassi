<?php
/**
 * Created by PhpStorm.
 * User: anderson
 * Date: 11/12/18
 * Time: 07:00
 */

require_once ('config.php');

if(isset($_POST['create']))
{
    $firstName =  $_POST['firstname'];
    $lastName  =  $_POST['lastname'];
    $cpf       =  $_POST['cpfcnpj'];
    $senha     =  md5( $_POST['senha']);
    $perfil    =  9;
    $email     =  $_POST['mail'];
    $foto      = $_POST['photo'];
    $account   = $_POST['account'];
    $agency    = $_POST['agency'];
    $banck     = $_POST['banck'];
    $telephone = $_POST['telephone'];

    if(isset($foto['tmp_name']) && !empty($foto['tmp_name']))
    {
        move_uploaded_file($foto['tmp_name'], "./images/".$foto['name']);
    }

    $novoUsuario = $pdo->prepare(
        "INSERT INTO `ct_usuario` (`id`, `username`, `password`, `firstname`, `lastname`, `email`, `datecreate`, `lastaccess`, `idpermission`, `img`)
                       VALUES (DEFAULT, :username, :passworda, :firstname, :lastname, :email, :datecreate, :lastaccess, :idpermission, :img) ");
    $novoUsuario->execute(
        array(
            ":username"      => strtolower($firstName.".".$lastName),
            ":passworda"     => $senha,
            ":firstname"     => $firstName,
            ":lastname"      => $lastName,
            ":email"         => $email,
            ":datecreate"    => date("Y-m-d"),
            ":lastaccess"    => date("Y-m-d"),
            ":idpermission"  => $perfil,
            ":img"           => $foto['name']
        ) );
    $ultimoId   = $pdo->lastInsertId();
    $dataUser = $pdo->prepare('insert into `ct_datauser` values (DEFAULT, :agency, :accountt, :banck, :cpf, :telephone, :iduser )');
    $dataUser->execute(
            array(":agency" => $agency, ":accountt" => $account, ":banck" => $banck, ":cpf" => $cpf, ":telephone" => $telephone, ":iduser" => $ultimoId)
    );

    $newResponsavel = $pdo->prepare("INSERT INTO `ct_responsavel` (`id`, `usersid`) VALUES (:id, :idd)");
    $newResponsavel->execute( array( ":id" => $ultimoId, ":idd" => $ultimoId  ) );
}
?>

<!doctype html>
<html lang="pt-br">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css"
          integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">

</head>
<style>
    .form-group{margin-bottom: 20px;}
    h6{
        margin-top: 20px;
    }
    body{
        overflow-x: hidden;;
    }
</style>
<body>
<div class="row">
    <div class="container">

        <div class="col-md-12">

            <div class="card card-outline-primary">
                <div class="card-body">
                    <form action="" method="post" class="dropzone">
                        <div class="form-group">
                            <label for="firstname">Nome</label>
                            <input type="text" required class="form-control" name="firstname" id="firstname">
                        </div>
                        <div class="form-group">
                            <label for="lastname">Sobrenome</label>
                            <input type="text" required class="form-control" name="lastname" id="lastname">
                        </div>
                        <div class="form-group">
                            <label for="cpfcnpj">CPF / CNPJ *</label>
                            <input type="text" required class="form-control" name="cpfcnpj" id="cpfcnpj">
                        </div>
                        <div class="form-group">
                            <label for="telephone">Telefone *</label>
                            <input type="text" required class="form-control" name="telephone" id="telephone">
                        </div>
                        <!-- -->
                        <div class="form-group">
                            <label for="mail">E-mail *</label>
                            <input type="email" required class="form-control" name="mail" id="mail">
                        </div>
                        <div class="form-group">
                            <label for="photo">Foto *</label>
                            <input type="file" required class="form-control" name="photo" id="photo">
                        </div>
                        <!-- -->
                        <h6>Dados Bancários</h6>
                        <hr>
                        <div class="form-group">
                            <label for="agency">Agência *</label>
                            <input type="text" required class="form-control" name="agency" id="agency">
                        </div>
                        <div class="form-group">
                            <label for="account">Conta </label>
                            <input type="text" required class="form-control" name="account" id="account">
                        </div>
                        <div class="form-group">
                            <label for="banck">Banco *</label>
                            <input type="text" required class="form-control" name="banck" id="banck">
                        </div>
                        <button type="submit" class="btn btn-block btn-large btn-success" name="create">
                            Solicitar Acesso
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>

</div>

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
        integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo"
        crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"
        integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49"
        crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"
        integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy"
        crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.10/jquery.mask.js"></script>

<script>
    $(document).ready(function () {

        $("#cpfcnpj").keydown(function(){
            try {
                $("#cpfcnpj").unmask();
            } catch (e) {}

            var tamanho = $("#cpfcnpj").val().length;

            if(tamanho < 11){
                $("#cpfcnpj").mask("999.999.999-99");
            } else if(tamanho >= 11){
                $("#cpfcnpj").mask("99.999.999/9999-99");
            }

            // ajustando foco
            var elem = this;
            setTimeout(function(){
                // mudo a posição do seletor
                elem.selectionStart = elem.selectionEnd = 10000;
            }, 0);
            // reaplico o valor para mudar o foco
            var currentValue = $(this).val();
            $(this).val('');
            $(this).val(currentValue);
        });
        $('#telephone').keydown(function () {
           var tamanho = $('#telephone').val().length;
           if(tamanho > 10) {
               $('#telephone').mask("(99) 99999-99999");
           }else{
               $('#telephone').mask("(99) 99999-99999");
           }
        });

    });

</script>
</body>
</html>
