<?php require_once ('header.php');
$todosPlanos = $pdo->prepare("select * from `ct_planaccounts`");
$todosPlanos->execute();
if( isset($_POST['plano']) )
{
    $planoDeContas    = $_POST['planodecontas'];
    $newPlanoDeContas = $pdo->prepare(
        'insert into `ct_planaccounts` (`id`, `name`) values (DEFAULT, :nome) ');
    $newPlanoDeContas->execute(
        array(
            ":nome"         => $planoDeContas,
        ) );
    echo("<div class='alert alert-success' role='alert'>Plano: ".$planoDeContas." cadastrado"."</div>");
}
if( isset( $_POST['editar'] ) )
{
  $idPlano       = $_POST['idplano'];
  $fullname      = $_POST['fullname'];
  $updateServico = $pdo->prepare("update `ct_planaccounts` set `name` = :nome where `id` = :id ");
  $updateServico->execute( array( ":nome" => $fullname, ":id" => $idPlano ) );
  echo("<div class='alert alert-success' role='alert'>Plano ".$nome." atualizado "."</div>");

  $todosPlanos = $pdo->prepare("select * from `ct_planaccounts`");
  $todosPlanos->execute();
}

if( isset( $_POST['excluir'] ) )
{
  $idPlano       = $_POST['idplano'];
  $deleteService = $pdo->prepare("delete from `ct_planaccounts` where `id` = :id ");
  $deleteService->execute( array( ":id" => $idPlano ) );

  echo("<div class='alert alert-danger' role='alert'>Plano Exluido</div>");

  $todosPlanos = $pdo->prepare("select * from `ct_planaccounts`");
  $todosPlanos->execute();
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
        <div class="container">
            <div id="status" class="pull-left col-md-12"></div>
            <div class="col-lg-12">

                <h4>Informações sobre o plano: </h4>
                <hr>
                <form action="" method="post">
                    <div class="col-md-12">
                        <label>Nome do plano</label>
                        <input type="text" name="planodecontas" id="planodecontas" class="form-control">
                    </div>

                    <button name="plano" id="conta" class="btn btn-success btn-lg btn-block">
                        Cadastrar Plano de Contas
                    </button>
                </form>
                <h4>Informações sobre os serviços cadastrados: </h4>
                <hr>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Nº</th>
                                <th>Nome do Plano</th>
                                <th>#</th>
                                <th>#</th>
                            </tr>
                        </thead>
                        <tbody>
                          <?php while( $registro = $todosPlanos->fetch(PDO::FETCH_ASSOC) ){ ?>
                            <tr>
                              <form action="" method="post" >
                                <td><?php echo( $registro['id'] ); ?></td>
                                <td>
                                  <input type="text" value="<?php echo( $registro['name'] ); ?>" name="fullname" >
                                </td>
                                <td>
                                    <input type="hidden" name="idplano" value="<?php echo( $registro['id'] ); ?>">
                                    <button type="submit" name="editar" style="backgroud:transparent; border:none; color:black;">Editar</button>
                                </td>
                              </form>
                              <td>
                                <form action="" method="post">
                                  <input type="hidden" name="idplano" value="<?php echo( $registro['id'] ); ?>">
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
