<?php require_once ('header.php'); ?>
<head>
    <title>Minha Página</title>
    <style>
        body {
            background-color: #f5f5f5;
        }
    </style>
</head>
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
                                    <a href="index">Home</a>
                                </li>
                                <li class="list-inline-item seprate">
                                    <span>/</span>
                                </li>
                                <li class="list-inline-item">Reserva: Pesquisar Reserva</li>
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

                    <div class="col-lg-6 pull-left ">
                        <h4>Imprimir Voucher</h4>
                        <hr>
                        <form method="post" target="_blank" action="./relatorio/pdf-relatorio-voucher.php">
                            <div class="form-group">
                                <label for="nomecliente">Número do voucher <strong>(somente números):</strong></label>
                                <input type="number" name="voucher" id="voucher" class="form-control" >
                            </div>
                            <div class="form-group">
                                    <button type="submit" class="btn btn-primary btn-lg btn-block" style="background-color: #1e4770;">Buscar</button>
                            </div>
                        </form>
                    </div>
                    <?php if( !empty( $_SESSION['idreservaplus']) or !empty( $_SESSION['idgerente'])
                    or !empty($_SESSION['idreservamanager'] ) or !empty($_SESSION['idfaturador'] ) ) { ?>
                    <div class="col-lg-6 pull-right ">
                        <h4>Imprimir Folha de Rosto</h4>
                        <hr>
                        <form method="post" target="_blank" action="./relatorio/pdf-relatorio-reserva.php">
                            <div class="form-group">
                                <label for="nomecliente">Número do Voucher <strong>(somente números):</strong></label>
                                <input type="text" name="voucher" id="voucher" class="form-control" >
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary btn-lg btn-block" style="background-color: #1e4770;">Buscar</button>
                            </div>
                        </form>
                    </div>
                    <?php }?>
                </div>

            </div>

        </div>
    </div>

    <?php require_once ('footer.php'); ?>
