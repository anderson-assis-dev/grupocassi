<?php require_once ('header.php');
$dadodsUsuario = $pdo->prepare("select * from `ct_usuario` where id = :id ");
$dadodsUsuario->execute(array(":id" => $_SESSION['id']));
$registro = $dadodsUsuario->fetch(PDO::FETCH_ASSOC);

if( isset( $_POST['updatepassword'] ) )
{
    $novaSenha     = $_POST['currentpassword'];
    $confirmaSenha = $_POST['newpassword'];
    if ($novaSenha === $confirmaSenha)
    {
        $updatePassword = $pdo->prepare('update `ct_usuario` set `password` = :pass where `ct_usuario`.`id` = :id ');
        $updatePassword->execute( array( ":pass" => md5( $confirmaSenha ) ,":id" => $_SESSION['id'] ) );
        echo("<div class='alert alert-success' role='alert'>Senha alterada.</div>");
    }
    else{
        echo("<div class='alert alert-danger' role='alert'>As senhas informadas não são iguais. Verifique-as novamente.</div>");
    }

}

?>
<!-- PAGE CONTENT-->
<div class="page-content--bgf7">
    <!-- BREADCRUMB-->
    <section class="au-breadcrumb2">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="au-breadcrumb-content">
                        <div class="au-breadcrumb-left">
                            <span class="au-breadcrumb-span">Navegação:</span>
                            <ul class="list-unstyled list-inline au-breadcrumb__list">
                                <li class="list-inline-item active">
                                    <a href="index">Home</a>
                                </li>
                                <li class="list-inline-item seprate">
                                    <span>/</span>
                                </li>
                                <li class="list-inline-item">Perfil: Meu Perfil</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- END BREADCRUMB-->

    <div class="row">
        <div class="container">
            <div class="col-md-8 pull-left">
                <div class="card">
                    <div class="card-header">
                        <h3 class="title">Meu Perfil</h3>
                    </div>
                    <div class="card-body">
                        <form method="post" action="">
                            <div class="row">
                                <div class="col-md-5 pr-1">
                                    <div class="form-group">
                                        <label>Empresa</label>
                                        <input type="text" class="form-control" disabled="" placeholder="Company" value="CASSI TURISMO">
                                    </div>
                                </div>
                                <div class="col-md-3 px-1">
                                    <div class="form-group">
                                        <label>Nome de Usuário</label>
                                        <input type="text" class="form-control" disabled placeholder="Username" value="<?php echo( $_SESSION['nome'] ); ?>">
                                    </div>
                                </div>
                                <div class="col-md-4 pl-1">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">E-mail</label>
                                        <input type="email" class="form-control"disabled value="<?php echo( $_SESSION['email'] ); ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 pr-1">
                                    <div class="form-group">
                                        <label>Nome</label>
                                        <input type="text" class="form-control" disabled placeholder="Company" value="<?php echo($registro['firstname'] ); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6 pr-1">
                                    <div class="form-group">
                                        <label>Nome</label>
                                        <input type="text" class="form-control" disabled placeholder="Company" value="<?php echo($registro['lastname'] ); ?>">
                                    </div>
                                </div>
                            </div>
                            <h4>Atualizar Senha de Acesso</h4>
                            <hr>
                            <div class="row">
                                <div class="col-md-6 pr-1">
                                    <div class="form-group">
                                        <label>Nova Senha</label>
                                        <input required type="password" class="form-control" name="currentpassword">
                                    </div>
                                </div>
                                <div class="col-md-6 pr-1">
                                    <div class="form-group">
                                        <label>Confirme a Senha (Repita a Senha)</label>
                                        <input required type="password" class="form-control" name="newpassword">
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-warning btn-block btn-lg" name="updatepassword">Atualizar Senha</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-4 pull-right">
                <div class="card card-user">
                    <div class="">
                    </div>
                    <div class="card-body">
                        <div class="author" style="text-align: center;">
                            <a href="#">
                                <h5 class="title"><?php echo($registro['firstname']." ". $registro['lastname'] ); ?></h5>
                            </a>
                            <p class="description">
                                <?php echo($registro['username'] ); ?>
                            </p>
                        </div>
                    </div>
                    <hr>
                    <div class="button-container">
                    </div>
                </div>
            </div>
        </div>
                </div>

            </div>

        </div>
    </div>
<script>
    $("#upload").dropzone({ url: "upload.php" });

</script>
    <?php require_once ('footer.php'); ?>
