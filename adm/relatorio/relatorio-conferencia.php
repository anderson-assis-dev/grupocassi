<?php require_once ('header.php');
    $todosCliente = $pdo->prepare('select * from `ct_cliente` order by fullname');
    $todosCliente->execute();
    $todosCliente2 = $pdo->prepare('select * from `ct_cliente` order by fullname');
    $todosCliente2->execute();
    $todosStatus = $pdo->prepare('SELECT * FROM `ct_statusinvoice` ');
    $todosStatus->execute();
?>
<style>
    .col-md-6, .col-md-12, input, .btn{
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
                                <li class="list-inline-item">Financeiro: Relatório Conferência</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <div class="row">
        <div class="container">
            <div id="status" class="pull-left col-md-12"></div>
            <div class="col-lg-12">

                <h4>Relatório por periodo: </h4>
                <hr>
                <form action="./relatorio/pdf-relatorio-conferencia.php" target="_blank" method="post">
                    <div class="col-md-6 pull-left">
                        <strong><label for="periodoinicial">Periodo Inicial</label></strong>
                        <input required type="date" name="periodoinicial" id="periodoinicial" class="form-control">
                    </div>
                    <div class="col-md-6 pull-right">
                        <strong><label for="periodofinal">Periodo Final</label></strong>
                        <input required type="date" name="periodofinal" id="periodofinal" class="form-control">
                    </div>
                    <div class="col-md-6 pull-left">
                        <strong><label for="cliente">Cliente</label></strong>
                        <select class="form-control" name="cliente" id="cliente" >
                            <option value="0">TODOS</option>
                            <?php while( $dadosCliente = $todosCliente->fetch( PDO::FETCH_ASSOC ) ){ ?>
                                <option value="<?php echo($dadosCliente['id']); ?>"><?php echo($dadosCliente['fullname']); ?></option>
                            <?php }?>
                        </select>
                    </div>
                    <div class="col-md-6 pull-right">
                        <strong><label for="status">Status</label></strong>
                        <select class="form-control" name="status" id="status" >
                            <option value="0">TODOS</option>
                            <?php while( $dadosStatus = $todosStatus->fetch( PDO::FETCH_ASSOC ) ){ ?>
                                <option value="<?php echo($dadosStatus['id']); ?>"><?php echo($dadosStatus['nameinvoice']); ?></option>
                            <?php }?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-success btn-lg" name="buscar" id="buscar">Gerar Relatório</button>
                </form>
            </div>
            <div class="col-md-12">
                <h4>Relatorio por abertura:</h4>
                <hr>
                <form action="./relatorio/pdf-relatorio-conferencia-abertura.php" target="_blank" method="post">
                    <div class="col-md-6 pull-left">
                        <strong><label for="cliente">Cliente</label></strong>
                        <select  required class="form-control" name="cliente" id="cliente" >
                            <option value="0">TODOS</option>
                            <?php while( $cliente = $todosCliente2->fetch( PDO::FETCH_ASSOC ) ){ ?>
                                <option value="<?php echo($cliente['id']); ?>"><?php echo($cliente['fullname']); ?></option>
                            <?php }?>
                        </select>
                    </div>
                    <div class="col-md-6 pull-right">
                        <strong><label for="abertura">Abertura</label></strong>
                        <input required type="date" name="abertura" id="abertura" class="form-control">
                    </div>

                    <button type="submit" class="btn btn-success btn-lg" name="buscar" id="buscar">Gerar Relatório</button>
                </form>
            </div>
        </div>
    </div>
    <?php require_once ('footer.php'); ?>
