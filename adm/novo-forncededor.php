<?php require_once ('header.php');

$pdo->exec("set names utf8");
$todosFornecedores = $pdo->prepare("select * from `ct_fornecedor`");
$todosFornecedores->execute();

if( isset($_POST['cadastrarfornecedor']) )
{
    $nomeFornecedor  = $_POST['nomefornecedor'];

    $newServico = $pdo->prepare(
        'insert into `ct_fornecedor` (`id`, `fullname`) values (DEFAULT, :nome) ');
    $newServico->execute(
        array(
            ":nome"         => $nomeFornecedor,
        ) );
    echo("<div class='alert alert-success' role='alert'>Fornecedor ".$nomeFornecedor." Cadastrado</div>");
    $todosFornecedores = $pdo->prepare("select * from `ct_fornecedor`");
    $todosFornecedores->execute();
}
if( isset( $_POST['editar'] ) )
{
    $idfornecedor  = $_POST['idfornecedor'];
    $fullname   = $_POST['fullname'];


    $updateServico = $pdo->prepare("update `ct_fornecedor` set `fullname` = :fullname where `id` = :id ");
    $updateServico->execute( array( ":fullname" => $fullname, ":id" => $idfornecedor ) );
    echo("<div class='alert alert-success' role='alert'>Fornecedor ".$fullname." atualizado "."</div>");
    $todosFornecedores = $pdo->prepare("select * from `ct_fornecedor`");
    $todosFornecedores->execute();
}

if( isset( $_POST['excluir'] ) )
{
    $idPasseio  = $_POST['idfornecedor'];

    $deleteService = $pdo->prepare("delete from `ct_fornecedor` where `id` = :id ");
    $deleteService->execute( array( ":id" => $idPasseio ) );
    echo("<div class='alert alert-danger' role='alert'>Fornecedor Excluido </div>");
    $todosFornecedores = $pdo->prepare("select * from `ct_fornecedor`");
    $todosFornecedores->execute();
}
?>

<style>
    input, button{
        margin-bottom: 20px;
    }
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
                            <span class="au-breadcrumb-span">Você está aqui:</span>
                            <ul class="list-unstyled list-inline au-breadcrumb__list">
                                <li class="list-inline-item active">
                                    <a href="./index">Home</a>
                                </li>
                                <li class="list-inline-item seprate">
                                    <span>/</span>
                                </li>
                                <li class="list-inline-item">Servico: Novo Serviço</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <div class="">
        <div class="">
            <div id="status" class="pull-left col-md-12"></div>
            <div class="col-lg-12">
                <div class="accordion" id="accordionExample">
                    <div class="card">
                        <div class="card-header" id="headingOne">
                            <h2 class="mb-0">
                                <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                    Cadastrar novo fornecedor
                                </button>
                            </h2>
                        </div>

                        <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionExample">
                            <div class="card-body">
                                <form action="" method="post">
                                    <label>Nome do Fornecedor</label>
                                    <input type="text" name="nomefornecedor" id="nomefornecedor" class="form-control">
                                    <div class="form-group">
                                        <button name="cadastrarfornecedor" id="cadastrarfornecedor" class="btn btn-success btn-lg btn-block">
                                            Cadastrar Fornecedor
                                        </button>
                                    </div>

                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header" id="headingTwo">
                            <h2 class="mb-0">
                                <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                    Fornecedores cadastrados
                                </button>
                            </h2>
                        </div>
                        <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionExample">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered">
                                        <thead>
                                        <tr>
                                            <th>Nº</th>
                                            <th>Nome do fornecedor</th>
                                            <th>#</th>
                                            <th>#</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php while( $registro = $todosFornecedores->fetch(PDO::FETCH_ASSOC) ){ ?>
                                            <tr>
                                                <form action="" method="post" >
                                                    <td><?php echo( $registro['id'] ); ?></td>
                                                    <td>
                                                        <input type="text" value="<?php echo( utf8_encode( $registro['fullname'] ) ); ?>" name="fullname" >
                                                    </td>

                                                    <td>
                                                        <input type="hidden" name="idfornecedor" value="<?php echo( $registro['id'] ); ?>">
                                                        <button type="submit" name="editar" style="backgroud:transparent; border:none; color:black;"><i style="font-size: 32px;" class="fa fa-save"></i></button>
                                                    </td>
                                                </form>
                                                <td>
                                                    <form action="" method="post">
                                                        <input type="hidden" name="idfornecedor" value="<?php echo( $registro['id'] ); ?>">
                                                        <button type="submit" name="excluir" style="backgroud:transparent; border:none; color:black;"><i style="font-size: 32px;" class="fa fa-trash"></i></button>
                                                    </form>

                                                </td>
                                            </tr>
                                        <?php } ?>
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
