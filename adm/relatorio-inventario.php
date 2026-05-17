<?php require_once ('header.php'); ?>
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
                                <li class="list-inline-item">Financeiro: Inventário</li>
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
                        <div class="card-title">Inventário</div>
                    </div>
                    <div class="card-body">
                        <form action="./relatorio/relatorio-inventario" target="_blank" method="post">
                            <div class="col-md-6 pull-left">
                                <strong><label for="abertura">Periodo Inicial</label></strong>
                                <input required type="date" name="abertura" id="abertura" class="form-control">
                            </div>

                            <div class="col-md-6 pull-right">
                                <strong><label for="aberturafinal">Periodo Final</label></strong>
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
