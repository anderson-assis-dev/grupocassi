<?php require_once ('header.php');

$todosCliente = $pdo->prepare('select * from `ct_cliente` order by `fullname` ');
$todosCliente->execute();

?>
<style>
    .col-md-4{
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
                                <li class="list-inline-item">Financeiro: Relatório Cliente</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <div class="">
        <div class="">

            <div class="col-lg-12">

                <div class="card">
                    <div class="card-header">
                        <div class="card-title">
                            <h4>Informações sobre o cliente: </h4>
                        </div>
                    </div>
                    <div class="card-body">

                        <form action="./relatorio/pdf-relatorio-zuluturismo.php" target="_blank" method="post">
                            <div class="col-md-4 pull-left">
                                <strong><label for="periodoinicial">Periodo Inicial</label></strong>
                                <input required type="date" name="periodoinicial" id="periodoinicial" class="form-control">
                            </div>
                            <div class="col-md-4 pull-left">
                                <strong><label for="periodofinal">Periodo Final</label></strong>
                                <input required type="date" name="periodofinal" id="periodofinal" class="form-control">
                            </div>
                            <div class="col-md-4 pull-right">
                                <strong><label for="cliente">Cliente</label></strong>
                                <select required name="cliente" class="form-control" id="cliente">
                                    <?php while( $registro = $todosCliente->fetch( PDO::FETCH_ASSOC ) ){ ?>
                                        <option value="<?php echo( $registro['id'] ); ?>"><?php echo( $registro['fullname'] ); ?></option>
                                    <?php }?>
                                </select>
                            </div>
                            <div class="pull-left col-md-6 container-fluid">
                                <button type="submit" class="btn btn-success btn-lg" name="buscar" id="buscar">Gerar Relatório</button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <?php require_once ('footer.php'); ?>
