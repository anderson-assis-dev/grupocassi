<?php require_once ('header.php');

$pdo->exec("set names utf8");
$todosServicos = $pdo->prepare("select * from `ct_servico` order by fullname");
$todosServicos->execute();

if( isset($_POST['cadastrarsservico']) )
{
    $nomeServico  = $_POST['nomeservico'];
    $roteiro      = $_POST['roteiro'];
    $newServico = $pdo->prepare(
        'insert into `ct_servico` (`id`, `fullname`, `screenplay`, `priceadult`, `pricechild`, `tarifaone`) values (DEFAULT, :nome, :roteiro, :precoadulto, :precocrianca, :tarifa) ');
    $newServico->execute(
        array(
            ":nome"         => $nomeServico,
            ":roteiro"      => $roteiro,
            ":precoadulto"  => 0,
            ":precocrianca" => 0,
            ":tarifa"       => 0
        ) );
    echo("<div class='alert alert-success' role='alert'>Serviço Cadastrado</div>");
    $todosServicos = $pdo->prepare("select * from `ct_servico` order by fullname");
    $todosServicos->execute();
}
if( isset( $_POST['editar'] ) )
{
  $idPasseio  = $_POST['idpasseio'];
  $fullname   = $_POST['fullname'];
  $screenPlay = $_POST['screenplay'];

  $updateServico = $pdo->prepare("update `ct_servico` set `fullname` = :fullname, `screenplay` = :screenplay where `id` = :id ");
  $updateServico->execute( array( ":fullname" => $fullname, ":screenplay" => $screenPlay, ":id" => $idPasseio ) );
  echo("<div class='alert alert-success' role='alert'>Serviço ".$fullname." atualizado "."</div>");
  $todosServicos = $pdo->prepare("select * from `ct_servico` order by fullname ");
  $todosServicos->execute();
}

if( isset( $_POST['excluir'] ) )
{
  $idPasseio  = $_POST['idpasseio'];

  $deleteService = $pdo->prepare("delete from `ct_servico` where `id` = :id ");
  $deleteService->execute( array( ":id" => $idPasseio ) );
  echo("<div class='alert alert-danger' role='alert'>Serviço Exluido </div>");
  $todosServicos = $pdo->prepare("select * from `ct_servico` order by fullname ");
  $todosServicos->execute();
}
?>

<style>
    .col-md-6, button{
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
                                    Cadastrar novo serviço
                                </button>
                            </h2>
                        </div>

                        <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionExample">
                            <div class="card-body">
                                <form action="" method="post">
                                    <div class="col-md-6 pull-left">
                                        <label>Nome do serviço</label>
                                        <input type="text" name="nomeservico" id="nomeservico" class="form-control">
                                    </div>
                                    <div class="col-md-6 pull-right">
                                        <label>Roteiro</label>
                                        <input type="text" name="roteiro" id="roteiro" class="form-control">
                                    </div>
                                    <div class="container-fluid">
                                        <button name="cadastrarsservico" id="cadastrarsservico" class="btn btn-success btn-lg">
                                            Cadastrar Serviço
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
                                    Serviços cadastrados
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
                                            <th>Nome do Serviço</th>
                                            <th>Roteiro</th>
                                            <th>#</th>
                                            <th>#</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php while( $registro = $todosServicos->fetch(PDO::FETCH_ASSOC) ){ ?>
                                            <tr>
                                                <form action="" method="post" >
                                                    <td><?php echo( $registro['id'] ); ?></td>
                                                    <td>
                                                        <input type="text" value="<?php echo htmlentities($registro['fullname'], ENT_QUOTES, 'UTF-8'); ?>" name="fullname">
                                                    </td>
                                                    <td>
                                                        <textarea class="form-control" rows="7" name="screenplay">
                                                            <?php echo htmlentities($registro['screenplay'], ENT_QUOTES, 'UTF-8'); ?>
                                                        </textarea>
                                                    </td>
                                                    <td>
                                                        <input type="hidden" name="idpasseio" value="<?php echo( $registro['id'] ); ?>">
                                                        <button type="submit" name="editar" style="backgroud:transparent; border:none; color:black;"><i style="font-size: 32px;" class="fa fa-save"></i></button>
                                                    </td>
                                                </form>
                                                <td>
                                                    <form action="" method="post">
                                                        <input type="hidden" name="idpasseio" value="<?php echo( $registro['id'] ); ?>">
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
