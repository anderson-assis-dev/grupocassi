<?php require_once ('header.php');
$todosCliente = $pdo->prepare('select * from `ct_fornecedor` order by fullname ');
$todosCliente->execute();
$todosStatus = $pdo->prepare('SELECT * FROM `ct_statusinvoice` ');
$todosStatus->execute();
$empresas = $pdo->prepare('select * from `ct_empresa`');
$empresas->execute();
$contas = $pdo->prepare('select * from `ct_currentaccount` ');
$contas->execute();
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
                                <li class="list-inline-item">Caixa: Relatório Contas</li>
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
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Relatório de Contas: </h4>
                    </div>
                    <div class="card-body">
                        <form action="./relatorio/pdf-fluxo-caixa" target="_blank" method="post">
                            <div class="col-md-6 pull-left">
                                <strong><label for="vencimentoinicial">Data Inicial</label></strong>
                                <input required type="date" name="vencimentoinicial" id="vencimentoinicial" class="form-control">
                            </div>
                            <div class="col-md-6 pull-right">
                                <strong><label for="vencimentofinal">Data Final</label></strong>
                                <input required type="date" name="vencimentofinal" id="vencimentofinal" class="form-control">
                            </div>
                            <div class="col-md-6 pull-left">
                                <strong><label for="cliente">Empresa</label></strong>
                                <select class="form-control" name="empresa" id="empresa" >
                                    <option value="0">TODOS</option>
                                    <?php while( $dadosEmpresa = $empresas->fetch( PDO::FETCH_ASSOC ) ){ ?>
                                        <option value="<?php echo($dadosEmpresa['id']); ?>"><?php echo($dadosEmpresa['fullname']); ?></option>
                                    <?php }?>
                                </select>
                            </div>
                            <div class="col-md-6 pull-right">
                                <strong><label for="cliente">Favorecido</label></strong>
                                <select class="form-control" name="cliente" id="cliente" >
                                    <option value="0">TODOS</option>
                                    <?php while( $dadosCliente = $todosCliente->fetch( PDO::FETCH_ASSOC ) ){ ?>
                                        <option value="<?php echo($dadosCliente['id']); ?>"><?php echo( utf8_encode( $dadosCliente['fullname'])); ?></option>
                                    <?php }?>
                                </select>
                            </div>
                            <div class="col-md-6 pull-left">
                                <strong><label for="conta">Conta</label></strong>
                                <select class="form-control" name="conta" id="conta" >
                                    <option value="0">TODOS</option>
                                    <?php while( $dados_contas = $contas->fetch( PDO::FETCH_ASSOC ) ){ ?>
                                        <option value="<?php echo($dados_contas['id']); ?>"><?php echo($dados_contas['name']); ?></option>
                                    <?php }?>
                                </select>
                            </div>
                            <div class="col-md-6 pull-right">
                                <strong><label for="tiporelatorio">Tipo de Relatório</label></strong>
                                <select class="form-control" name="tiporelatorio" id="tiporelatorio" >
                                    <option value="0">Fluxo de Caixa</option>
                                </select>
                            </div>
                            <div class="container-fluid">
                                <button type="submit" class="btn btn-success btn-lg" name="buscar" id="buscar">Gerar Relatório</button>

                            </div>
                        </form>
                    </div>
                </div>

                <hr>

            </div>
        </div>
    </div>
    <?php require_once ('footer.php'); ?>
