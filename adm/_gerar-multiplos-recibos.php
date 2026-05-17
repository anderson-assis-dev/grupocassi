<?php
require_once ('header.php');
$diarias = $pdo->prepare('select * from `ct_diarias` order by `nomediarista`, `valordiaria` ');
$diarias->execute();
$listar_diarias = $diarias->fetchAll(PDO::FETCH_CLASS);
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
                        <div class="accordion" id="accordionExample">
                            <div class="card">
                                <div class="card-header" id="headingOne">
                                    <h2 class="mb-0">
                                        <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                            Gerar recibos
                                        </button>
                                    </h2>
                                </div>

                                <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionExample">
                                    <div class="card-body">
                                        <form action="relatorio/imprimir-multiplos-recibos" method="post" target="_blank">
                                            <table border="1" id="tabela-herdeiro" class="table table-bordered">
                                                <thead>
                                                <tr>
                                                    <th>Nome</th>
                                                    <th>Descrição</th>
                                                    <th>Data do pagamento</th>
                                                    <th>Valor diária</th>
                                                    <th>#</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php if(count($listar_diarias) > 0){ $contador = 1; ?>
                                                    <input type="hidden" value="<?php echo(count($listar_diarias));?>" name="totallinhas" id="totallinhas">
                                                    <?php foreach($listar_diarias as $item){ ?>
                                                        <tr>
                                                            <td><input type="text" value="<?php echo($item->nomediarista); ?>" class="form-control" name="<?php echo("nome".$contador); ?>"></td>
                                                            <td><input type="text" value="<?php echo($item->documento); ?>" class="form-control" name="<?php echo("descricao".$contador); ?>"></td>
                                                            <td><input type="date" value="<?php echo(date("Y-m-d")); ?>" class="form-control" name="<?php echo("datapagamento".$contador); ?>"></td>
                                                            <td>
                                                                <div class="input-group mb-3">
                                                                    <div class="input-group-prepend">
                                                                        <span class="input-group-text" id="basic-addon1">R$</span>
                                                                    </div>
                                                                    <input type="text" value="<?php echo($item->valordiaria); ?>" class="form-control" name="<?php echo("diaria".$contador); ?>">
                                                                </div>
                                                            </td>
                                                            <td><button class="btn btn-outline-danger" id="remover" type="button" onclick="Excluir(this)">Remover diária</button></td>

                                                        </tr>
                                                        <?php $contador++; }?>
                                                <?php } else { ?>
                                                    <input type="hidden" value="0" name="totallinhas" id="totallinhas">
                                                    <tr>
                                                        <td><input type="text" value="Anderson" class="form-control" name="nome1"></td>
                                                        <td><input type="text" value="R$ 50,00 referente a almoço" class="form-control" name="descricao1"></td>
                                                        <td><input type="date" value="<?php echo(date("Y-m-d")); ?>" class="form-control" name="<?php echo("datapagamento1"); ?>"></td>
                                                        <td>
                                                            <div class="input-group mb-3">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text" id="basic-addon1">R$</span>
                                                                </div>
                                                                <input type="text" value="50" class="form-control" name="diaria1">
                                                            </div>
                                                        </td>
                                                        <td><button class="btn btn-outline-danger" id="remover" type="button" onclick="Excluir(this)">Remover diária</button></td>

                                                    </tr>
                                                <?php }?>


                                                </tbody>
                                            <br>
                                            </table>
                                            <button id="adicionar" class="btn btn-outline-success pull-left" type="button" onclick="Adicionar()">Adicionar nova diária</button>
                                            <button class="btn btn-outline-primary pull-right" type="submit">Gerar recibos </button>
                                        </form>

                                    </div>
                                </div>
                            </div>


                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        var contador = <?php echo(count($listar_diarias)); ?>;

        function Adicionar(){
            contador ++;
            $("#totallinhas").val(contador);
            html =  ' <tr id="linha' + contador + '">';
            html += '<td><input type="text" class="form-control" name="nome'+contador+'"></td>';
            html += '<td><input type="text" value="R$ 50,00 referente a almoço" class="form-control" name="descricao'+contador+'"></td>';
            html += '<td><input type="date" value="<?php echo(date("Y-m-d")) ?>" class="form-control" name="datapagamento'+contador+'"></td>';
            html += '<td><div class="input-group mb-3"> <div class="input-group-prepend"><span class="input-group-text" id="basic-addon1">R$</span> </div><input type="text" value="50" class="form-control" name="diaria'+contador+'"></div> </td>';
            html += '<td><button class="btn btn-outline-danger" type="button" id="remover" onclick="Excluir(this)">Remover diária</button></td>';

            $("#tabela-herdeiro tbody").append(html);
            $(".btnSalvar").bind("click", Salvar);
            $("#remover").bind("click", Excluir);
        };

        function Excluir(item){
            contador--;
            $("#totallinhas").val(contador);
            var tr = $(item).closest('tr');

            tr.fadeOut(400, function() {
                tr.remove();
            });
        };
    </script>
    <?php require_once ('footer.php'); ?>
