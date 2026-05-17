<?php require_once ('header.php'); ?>
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
                                    <a href="index">Home</a>
                                </li>
                                <li class="list-inline-item seprate">
                                    <span>/</span>
                                </li>
                                <li class="list-inline-item">Reserva: Relatório</li>
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
                    <h3>Relatórios</h3>
                    <hr>
                    <div class="col-lg-3 pull-left ">
                        <h4>Relatório de Reserva por data</h4>
                        <hr>
                        <form method="post" action="">
                            <div class="form-group">
                                <label for="datainicio">Data Inicial:</label>
                                <input type="date" name="datainicio" id="datainicio" class="form-control" >
                            </div>
                            <div class="form-group">
                                <label for="datafim">Data Final:</label>
                                <input type="date" name="datafim" id="datafim" class="form-control" >
                            </div>
                            <div class="form-group">
                                <button class="btn btn-primary btn-lg btn-block" >Gerar Relatório</button>
                            </div>
                        </form>
                    </div>
                    <div class="col-lg-3 pull-left ">
                        <h4>Relatório de Reserva por Hotéis</h4>
                        <hr>
                        <form method="post" action="">
                            <div class="form-group">
                                <label for="hotel">Hotel:</label>
                                <select name="hotel" class="form-control" >
                                    <option>Selecionar</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <button class="btn btn-primary btn-lg btn-block" >Gerar Relatório</button>
                            </div>
                        </form>
                    </div>
                    <div class="col-lg-3 pull-left ">
                        <h4>Relatório de Reserva por Operador</h4>
                        <hr>
                        <form method="post" action="">
                            <div class="form-group">
                                <label for="operador">Operador:</label>
                                <select name="operador" class="form-control" >
                                    <option>Selecionar</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <button class="btn btn-primary btn-lg btn-block" >Gerar Relatório</button>
                            </div>
                        </form>
                    </div>
                    <div class="col-lg-3 pull-right ">
                        <h4>Relatório de Reserva por Status</h4>
                        <hr>
                        <form method="post" action="">
                            <div class="form-group">
                                <label for="status">Status:</label>
                                <select name="status" class="form-control" >
                                    <option value="Reservado" >Reservado</option>
                                    <option value="Cancelado" >Cancelado</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <button class="btn btn-primary btn-lg btn-block" >Gerar Relatório</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php require_once ('footer.php'); ?>
