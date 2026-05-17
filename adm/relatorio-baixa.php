<?php require_once ('header.php');
$todosusuario = $pdo->prepare('select * from `ct_usuario` order by `firstname` ');
$todosusuario->execute();
?>
<style>
    .col-md-6, .col-md-12, input, .btn,.col-md-4{
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
                                    <a href="./index.php">Home</a>
                                </li>
                                <li class="list-inline-item seprate">
                                    <span>/</span>
                                </li>
                                <li class="list-inline-item">Financeiro: Relatório das baixas</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <div class="">
        <div class="">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Relatório de Baixa</div>
                    </div>
                    <div class="card-body">
                        <form action="./relatorio/pdf-relatorio-baixa" target="_blank" method="post">
                            <div class="col-md-4 pull-left">
                                <strong><label for="responsavel">Responsável</label></strong>
                                <select  required class="form-control" name="responsavel" id="responsavel" >
                                    <option selected value="0">TODOS</option>
                                    <?php while( $responsavel = $todosusuario->fetch( PDO::FETCH_ASSOC ) ){ ?>
                                        <option value="<?php echo($responsavel['id']); ?>"><?php echo($responsavel['firstname']); ?></option>
                                    <?php }?>
                                </select>
                            </div>
                            <div class="col-md-4 pull-left">
                                <strong><label for="abertura">Abertura Inicial</label></strong>
                                <input required type="date" name="abertura" id="abertura" class="form-control">
                            </div>

                            <div class="col-md-4 pull-right">
                                <strong><label for="aberturafinal">Abertura Final</label></strong>
                                <input required type="date" name="aberturafinal" id="aberturafinal" class="form-control">
                            </div>
                            <div class="container-fluid pull-left">
                                <button type="submit" class="btn btn-success btn-lg" name="buscar" id="buscar">Gerar Relatório</button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php require_once ('footer.php'); ?>
