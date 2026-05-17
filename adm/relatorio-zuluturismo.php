<?php require_once ('header.php'); ?>
<style>
    .col-md-6{
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
    <div class="row">
        <div class="container">
            <div id="status" class="pull-left col-md-12"></div>
            <div class="col-lg-12">

                <h4>Informações sobre o cliente: </h4>
                <hr>
                <form action="./relatorio/pdf-relatorio-zuluturismo" target="_blank" method="post">
                    <div class="col-md-6 pull-left">
                        <strong><label for="periodoinicial">Periodo Inicial</label></strong>
                        <input required type="date" name="periodoinicial" id="periodoinicial" class="form-control">
                    </div>
                    <div class="col-md-6 pull-right">
                        <strong><label for="periodofinal">Periodo Final</label></strong>
                        <input required type="date" name="periodofinal" id="periodofinal" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-success btn-lg" name="buscar" id="buscar">Buscar</button>
                </form>
            </div>
        </div>
    </div>
    <?php require_once ('footer.php'); ?>
