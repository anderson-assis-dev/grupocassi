<?php require_once ('header.php');

$pdo->exec("set names utf8");
$todasContas = $pdo->prepare("select * from `ct_currentaccount`");
$todasContas->execute();

if( isset($_POST['conta']) )
{
    $contaCorrente    = $_POST['contacorrente'];
    $newContaCorrente = $pdo->prepare(
        'insert into `ct_currentaccount` (`id`, `name`) values (DEFAULT, :nome) ');
    $newContaCorrente->execute(
        array(
            ":nome"         => $contaCorrente,
        ) );
    $todasContas = $pdo->prepare("select * from `ct_currentaccount`");
    $todasContas->execute();
    echo("<div class='alert alert-success' role='alert'>Conta ".$contaCorrente." cadastrada"."</div>");
}
if( isset( $_POST['editar'] ) )
{
  $idConta  = $_POST['idconta'];
  $fullname   = $_POST['fullname'];

  $updateServico = $pdo->prepare("update `ct_currentaccount` set `name` = :fullname where `id` = :id ");
  $updateServico->execute( array( ":fullname" => $fullname,  ":id" => $idConta ) );
  echo("<div class='alert alert-success' role='alert'>Conta ".$fullname." atualizada "."</div>");
  $todasContas = $pdo->prepare("select * from `ct_currentaccount`");
  $todasContas->execute();
}

if( isset( $_POST['excluir'] ) )
{
  $idConta  = $_POST['idconta'];

  $deleteService = $pdo->prepare("delete from `ct_currentaccount` where `id` = :id ");
  $deleteService->execute( array( ":id" => $idConta ) );
  echo("<div class='alert alert-danger' role='alert'>Conta Exluida</div>");
  $todasContas = $pdo->prepare("select * from `ct_currentaccount`");
  $todasContas->execute();
}
?>

<style>
    .col-md-12, button{
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
                            <span class="au-breadcrumb-span">Navegação:</span>
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
        <div class="container">
            <div id="status" class="pull-left col-md-12"></div>
            <div class="col-lg-12">

                <h4>Informações sobre a Conta: </h4>
                <hr>
                <form action="" method="post">
                    <div class="col-md-12">
                        <label>Nome da conta</label>
                        <input type="text" name="contacorrente" id="contacorrente" class="form-control">
                    </div>

                    <button name="conta" id="conta" class="btn btn-success btn-lg btn-block">
                        Cadastrar Conta Corrente
                    </button>
                </form>
                <h4>Informações sobre os serviços cadastrados: </h4>
                <hr>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Nº</th>
                                <th>Nome da Conta</th>
                                <th>#</th>
                                <th>#</th>
                            </tr>
                        </thead>
                        <tbody>
                          <?php while( $registro = $todasContas->fetch(PDO::FETCH_ASSOC) ){ ?>
                            <tr>
                              <form action="" method="post" >
                                <td><?php echo( $registro['id'] ); ?></td>
                                <td>
                                  <input type="text" value="<?php echo( $registro['name'] ); ?>" name="fullname" >
                                </td>
                                <td>
                                    <input type="hidden" name="idconta" value="<?php echo( $registro['id'] ); ?>">
                                    <button type="submit" name="editar" style="backgroud:transparent; border:none; color:black;">Editar</button>
                                </td>
                              </form>
                              <td>
                                <form action="" method="post">
                                  <input type="hidden" name="idconta" value="<?php echo( $registro['id'] ); ?>">
                                  <button type="submit" name="excluir" style="backgroud:transparent; border:none; color:black;">Excluir</button>
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
    <?php require_once ('footer.php'); ?>
