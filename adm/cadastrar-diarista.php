<?php
require_once ('header.php');
$diarias = $pdo->prepare('select * from `ct_diarias` order by `nomediarista`, `valordiaria` ');
$diarias->execute();
$listar_diarias = $diarias->fetchAll(PDO::FETCH_CLASS);
if(isset($_POST['cadastrardiaria']))
{
    $novo_diarista = $pdo->prepare("insert into `ct_diarias` values (DEFAULT, :nome, :descricao, :valor, 1) ");
    $novo_diarista->execute(array(":nome" => $_POST['nome'], ":descricao" => $_POST['documento'], ":valor" => str_replace(",", ".",$_POST['valor'])));
    header("Location: cadastrar-diarista?q=ok");
}
if(isset($_POST['removerdiarista']))
{
    $remover_diarista = $pdo->prepare("delete from `ct_diarias` where iddiarista = :iddiarista ");
    $remover_diarista->execute(array(":iddiarista" => $_POST['iddiarista']));
    header("Location: cadastrar-diarista?q=ok2");
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
                            <span class="au-breadcrumb-span">Você está aqui:</span>
                            <ul class="list-unstyled list-inline au-breadcrumb__list">
                                <li class="list-inline-item active">
                                    <a href="./">Home</a>
                                </li>
                                <li class="list-inline-item seprate">
                                    <span>/</span>
                                </li>
                                <li class="list-inline-item">Diarias: Cadastrar diarista</li>
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
            <div class="card card-outline-primary">
                <div class="card-body">
                    <div class="col-lg-12">
                        <?php if(isset($_GET['q'])){ ?>
                            <div class="alert alert-success" role="">Diarista cadastrado!</div>
                        <?php }?>
                        <?php if(isset($_GET['q']) and $_GET['q'] == 'ok2'){ ?>
                            <div class="alert alert-danger" role="">Diarista removido!</div>
                        <?php }?>
                        <div class="accordion" id="accordionExample">
                            <div class="card">
                                <div class="card-header" id="headingOne">
                                    <h2 class="mb-0">
                                        <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                            Cadastrar diária
                                        </button>
                                    </h2>
                                </div>

                                <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionExample">
                                    <div class="card-body">
                                        <form action="" method="post">
                                            <div class="col-lg-4 pull-left">
                                                <label for="nome">Nome completo</label>
                                                <input type="text" name="nome" id="nome" required class="form-control">
                                            </div>
                                            <div class="col-lg-4 pull-left">
                                                <label for="documento">Documento (CPF, RG)</label>
                                                <input type="text" name="documento" id="documento" required class="form-control">
                                            </div>
                                            <div class="col-lg-4 pull-left">
                                                <label for="valor">Valor da diaria</label>
                                                <div class="input-group mb-3">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text" id="basic-addon1">R$</span>
                                                    </div>
                                                    <input type="text" name="valor" id="valor" required class="form-control">
                                                </div>
                                            </div>
                                            <div class="form-group container-fluid">
                                                <button type="submit" id="cadastrardiaria" class="btn btn-primary btn-lg " name="cadastrardiaria">Cadastrar diarista</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-header" id="headingTwo">
                                    <h2 class="mb-0">
                                        <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                            Listar diárias
                                        </button>
                                    </h2>
                                </div>
                                <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionExample">
                                    <div class="card-body">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Nome do diárista</th>
                                                    <th>Descrição da diária</th>
                                                    <th>Valor da diária</th>
                                                    <th>#</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php foreach($listar_diarias as $item){ ?>
                                                <tr>
                                                    <td><?php echo($item->nomediarista); ?></td>
                                                    <td><?php echo($item->documento); ?></td>
                                                    <td><?php echo(number_format($item->valordiaria, 2, ",", ".")); ?></td>
                                                    <td>
                                                        <form action="#" method="post">

                                                            <input type="hidden" name="iddiarista" value="<?php echo( $item->iddiarista ); ?>" >
                                                            <button type="submit" name="removerdiarista" style="background-color: transparent; border: none;">
                                                                Remover
                                                            </button>
                                                        </form>

                                                    </td>
                                                </tr>
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
    </div>
    <?php require_once ('footer.php'); ?>
