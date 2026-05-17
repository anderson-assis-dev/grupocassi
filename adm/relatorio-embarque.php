<?php require_once ('header.php');
$todosCliente = $pdo->prepare('select * from `ct_cliente` order by fullname ');
$todosCliente->execute();

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
                                <li class="list-inline-item"> Relatório de embarque</li>
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
                            <div class="card-header" id="headingTwo1">
                                <h2 class="mb-0">
                                    <button style="color:black;" class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseTwo1" aria-expanded="true"
                                            aria-controls="collapseTwo1">
                                            Relatório de confirmação embarque
                                    </button>
                                </h2>
                            </div>
                            <div id="collapseTwo1" class="collapse show" aria-labelledby="headingTwo1" data-parent="#accordionExample">
                                <div class="card-body">
                                    <form action="./relatorio/pdf-relatorio-embarque" target="_blank" method="post">

                                        <div class="col-md-4 pull-left">
                                            <strong><label for="cliente">Agência / Revendedor</label></strong>
                                            <select  required class="form-control" name="cliente" id="cliente" >
                                                <option selected value="0">TODOS</option>
                                                <?php while( $cliente = $todosCliente->fetch( PDO::FETCH_ASSOC ) ){ ?>
                                                    <option value="<?php echo($cliente['id']); ?>"><?php echo($cliente['fullname']); ?></option>
                                                <?php }?>
                                            </select>
                                        </div>
             
                                        <div class="col-md-4 pull-left">
                                            <strong><label for="inicio">Data do Pagamento Inicial</label></strong>
                                            <input required type="date" name="inicio" id="inicio" class="form-control">
                                        </div>

                                        <div class="col-md-4 pull-right">
                                            <strong><label for="fim">Data do Pagamento Final</label></strong>
                                            <input required type="date" name="fim" id="fim" class="form-control">
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

        </div>
    </div>
    <?php require_once ('footer.php'); ?>
