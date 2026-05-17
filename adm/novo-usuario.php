<?php require_once ('header.php');

$pdo->exec("set names utf8");
$buscaPerfil = $pdo->prepare('select * from `ct_permission` ');
$buscaPerfil->execute();
$registros = $buscaPerfil->fetchAll(PDO::FETCH_CLASS);

$usuariosCadastrados = $pdo->prepare('select * from `ct_usuario` order by firstname');
$usuariosCadastrados->execute();
$listaUsusarios = $usuariosCadastrados->fetchAll(PDO::FETCH_CLASS);

$clientes = $pdo->prepare('select * from `ct_cliente` order by corporatename');
$clientes->execute();
$registroCliente = $clientes->fetchAll(PDO::FETCH_CLASS);

if( isset($_POST['cadastrar']) )
{
    $nome      =  strtolower( str_replace('', '', $_POST['nome'])  ) ;
    $senha     =  md5( $_POST['senha']);
    $perfil    =  $_POST['perfil'];
    $email     =  "@cassiturismo.com.br";
    $nameUsuario = $_POST['nome'];
    $novoUsuario = $pdo->prepare(
            "INSERT INTO `ct_usuario` (`id`, `username`, `password`, `firstname`, `lastname`, `email`, `datecreate`, `lastaccess`, `idpermission`, `img`)
                       VALUES (DEFAULT, :username, :passworda, :firstname, :lastname, :email, :datecreate, :lastaccess, :idpermission, :img) ");
    $novoUsuario->execute(
            array(
                    ":username"      => $nome,
                    ":passworda"     => $senha,
                    ":firstname"     => $nome,
                    ":lastname"      => "",
                    ":email"         => $nome."-".$email,
                    ":datecreate"    => date("Y-m-d"),
                    ":lastaccess"    => date("Y-m-d"),
                    ":idpermission"  => $perfil,
                    ":img"           => "cassi.png"
            ) );

    $ultimoId   = $pdo->lastInsertId();
    $newResponsavel = $pdo->prepare("INSERT INTO `ct_responsavel` (`id`, `usersid`) VALUES (:id, :idd)");
    $newResponsavel->execute( array( ":id" => $ultimoId, ":idd" => $ultimoId  ) );
    $usuariosCadastrados = $pdo->prepare('select * from `ct_usuario` ');
    $usuariosCadastrados->execute();
    $listaUsusarios = $usuariosCadastrados->fetchAll(PDO::FETCH_CLASS);

    if( $_POST['cliente'] > 0 )
    {
        $updateCliente = $pdo->prepare('update `ct_cliente` c set c.register = :usuario where c.id = :id');
        $updateCliente->execute( array(":usuario" => $ultimoId, ":id" => $_POST['cliente']) );
    }

    echo("<div class='alert alert-success' role='alert'>Usuário Cadastrado! Login de acesso: ".$nameUsuario."</div>");
}
if( isset( $_POST['bloquear'] ) )
{
    $block = $pdo->prepare('update `ct_usuario` set bloqueado = :situacao where id = :id');
    $block->execute( array(":situacao" => $_POST['situacao'], ":id" => $_POST['id'] ) );
    if( $_POST['situacao'] == 0 ){
        echo("<div class='alert alert-success' role='alert'>Usuário Desbloqueado</div>");
    }else{
        echo("<div class='alert alert-danger' role='alert'>Usuário Bloqueado</div>");
    }
    $usuariosCadastrados = $pdo->prepare('select * from `ct_usuario` order by firstname, lastname ');
    $usuariosCadastrados->execute();
    $listaUsusarios = $usuariosCadastrados->fetchAll(PDO::FETCH_CLASS);
}

?>
<style>
    .col-lg-6{margin-bottom: 20px;}
</style>
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
                                    <a href="./">Home</a>
                                </li>
                                <li class="list-inline-item seprate">
                                    <span>/</span>
                                </li>
                                <li class="list-inline-item">Usuário: Novo Usuário</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- END BREADCRUMB-->

    <div class="">
        <div class="">
            <div class="col-lg-12">
                <div class="accordion" id="accordionExample">
                    <div class="card">
                        <div class="card-header" id="headingOne">
                            <h2 class="mb-0">
                                <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                   Cadastrar usuário
                                </button>
                            </h2>
                        </div>

                        <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionExample">
                            <div class="card-body">
                                <form action="" method="post">
                                    <div class="col-lg-6 pull-left">
                                        <label for="nome">Nome de usuário </label>
                                        <input type="text" name="nome" id="nome" required class="form-control">
                                    </div>
                                    <div class="col-lg-6 pull-right">
                                        <label for="senha">Senha de acesso </label>
                                        <input type="password" name="senha" id="senha" required class="form-control">

                                    </div>
                                    <div class="col-lg-6 pull-left">
                                        <label for="perfil">Perfil de acesso</label>
                                        <select class="form-control" name="perfil">
                                            <?php foreach ($registros as $registro){ ?>
                                                <option title="<?php echo($registro->descricao); ?>"
                                                        value="<?php echo($registro->id); ?>" >
                                                    <?php echo($registro->namepermission); ?>
                                                </option>
                                            <?php }?>
                                        </select>
                                    </div>
                                    <div class="col-lg-6 pull-right">
                                        <label for="cliente">Cliente especifico</label>
                                        <select class="form-control" name="cliente">
                                            <?php foreach ($registroCliente as $item){ ?>
                                                <option value="<?php echo($item->id); ?>" ><?php echo($item->corporatename); ?></option>
                                            <?php }?>
                                            <option selected value="0">Nenhum</option>
                                        </select>
                                    </div>
                                    <div class="form-group container-fluid">
                                        <button class="btn btn-primary btn-lg" name="cadastrar">
                                            Cadastrar
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header" id="headingTwo">
                            <h2 class="mb-0">
                                <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false"
                                        aria-controls="collapseTwo">
                                    Usuários cadastrados
                                </button>
                            </h2>
                        </div>
                        <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionExample">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered"  id="example23" width="100%">
                                        <thead>
                                        <tr>
                                            <th>Nº</th>
                                            <th>Nome</th>
                                            <th>Login</th>
                                            <th>E-mail</th>
                                            <th>Ultimo Acesso</th>
                                            <th>Situação</th>
                                            <th>Editar</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach ($listaUsusarios as $listaUsusario) { $time = strtotime($listaUsusario->lastaccess);  ?>
                                            <?php if( $listaUsusario->bloqueado == 0 ){ ?>
                                                <tr>
                                                    <td><?php echo( $listaUsusario->id); ?></td>
                                                    <td><?php echo( strtoupper( utf8_encode($listaUsusario->firstname." ".$listaUsusario->lastname) ) ); ?></td>
                                                    <td>
                                                        <form action="./editar-usuario" method="post" >
                                                            <input type="hidden" name="id" value="<?php echo( $listaUsusario->id ); ?>">
                                                            <button type="submit" style="backgroud:transparent; border:none; color:black;" >
                                                                <?php echo( strtoupper( utf8_encode( $listaUsusario->username ) ) ); ?>
                                                            </button>
                                                        </form>
                                                    </td>
                                                    <td><?php echo( $listaUsusario->email); ?></td>
                                                    <td><?php echo( date("d-m-Y", $time)); ?></td>
                                                    <td>
                                                        <form action="#" method="post" >
                                                            <input type="hidden" name="id" value="<?php echo( $listaUsusario->id ); ?>">
                                                            <input type="hidden" name="situacao" value="1">
                                                            <button name="bloquear" type="submit" style="backgroud:transparent; border:none; color:black;" >
                                                                Bloquear
                                                            </button>
                                                        </form>
                                                    </td>
                                                    <td>
                                                        <form action="./editar-usuario" method="post" >
                                                            <input type="hidden" name="id" value="<?php echo( $listaUsusario->id ); ?>">
                                                            <button type="submit" style="backgroud:transparent; border:none; color:black;" >
                                                                <i class="fa fa-user-plus"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php } else{ ?>
                                                <tr class="table-danger">
                                                    <td><?php echo( $listaUsusario->id); ?></td>
                                                    <td><?php echo( strtoupper( utf8_encode($listaUsusario->firstname." ".$listaUsusario->lastname ) ) ); ?></td>
                                                    <td>
                                                        <form action="./editar-usuario" method="post" >
                                                            <input type="hidden" name="id" value="<?php echo( $listaUsusario->id ); ?>">
                                                            <button type="submit" style="backgroud:transparent; border:none; color:black;" >
                                                                <?php echo( strtoupper( utf8_encode($listaUsusario->username ) ) ); ?>
                                                            </button>
                                                        </form>
                                                    </td>
                                                    <td><?php echo( $listaUsusario->email); ?></td>
                                                    <td><?php echo( date("d-m-Y", $time)); ?></td>
                                                    <td>
                                                        <form action="#" method="post" >
                                                            <input type="hidden" name="id" value="<?php echo( $listaUsusario->id ); ?>">
                                                            <input type="hidden" name="situacao" value="0">
                                                            <button name="bloquear" type="submit" style="backgroud:transparent; border:none; color:black;" >
                                                                Desbloquear
                                                            </button>
                                                        </form>
                                                    </td>
                                                    <td>
                                                        <form action="./editar-usuario" method="post" >
                                                            <input type="hidden" name="id" value="<?php echo( $listaUsusario->id ); ?>">
                                                            <button type="submit" style="backgroud:transparent; border:none; color:black;" >
                                                                <i class="fa fa-user-plus"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php }?>

                                        <?php }?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>

<?php require_once ('footer.php'); ?>
