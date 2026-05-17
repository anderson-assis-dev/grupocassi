<?php require_once ('header.php');
    $listaAgente = $pdo->prepare('SELECT * FROM `ct_agentes` order by `fullname`');
    $listaAgente->execute();
    $listaGuia = $pdo->prepare('SELECT * FROM `ct_guia` order by `fullname`');
$listaGuia->execute();
if(isset( $_POST['buscardespesa'] ))
{
    $voucher         = $_POST['numberVoucher'];
    $buscaCredito = $pdo->prepare('SELECT * FROM `ct_createfaturacredit` where `numbervoucher` = :voucher');
    $buscaCredito->execute( array(":voucher" => $voucher ) );
    $contadorCredito = $buscaCredito->rowCount();

}
if(isset($_POST['atualizarcredito']))
{
    $valorAtual = $_POST['creditoatual'];
    $novoValor  = $_POST['novocredito'];

    $updateCredito = $pdo->prepare('update `ct_createfaturacredit` set valueagente = :novovalor where id = :id ');
    $updateCredito->execute( array(":novovalor" => $novoValor, ":id" => $valorAtual) );
    echo("<div class='alert alert-success' role='alert'>Valor atualizado.</div>");

    $auditoria = $pdo->prepare(
        'insert into `ct_audit` (`id`, `idresponsible`, `voucher`, `description`, `date`) values (DEFAULT, :resp, :voucher, :des, :dataa) ');
    $auditoria->execute( array(
        ":resp"    => $_SESSION['id'],
        ":voucher" => $voucher,
        ":des"     => "Valor da comissão  atualizada",
        ":dataa"   => date("Y-m-d H:i:s" )) );
}
?>
<style>
    .col-lg-4{
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
            <div class="col-lg-12">
                <h4>Informações sobre à comissão: </h4>
                <hr>
                <div class="col-lg-12">
                    <form action="relatorio/pdf-relatorio-comissao-agente.php" target="_blank" method="post">
                        <h5>Cadastro de comissão para agente</h5>
                        <hr>
                        <div class="col-lg-4 pull-left">
                            <strong><label for="nomeagente">Nome do Agente</label></strong>
                            <input type="text" name="nomeagente" id="nomeagente" class="form-control">

                        </div>
                        <div class="col-lg-4 pull-left">
                            <strong><label for="data">Voucher</label></strong>
                            <input type="number" class="form-control" name="voucher" id="voucher" >
                        </div>
                        <div class="col-lg-4 pull-right">
                            <strong><label for="valoragente">Valor</label></strong>
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="basic-addon1">R$</span>
                                    <input required type="text" name="valoragente" id="valoragente" class="form-control">
                                </div>
                            </div>


                        </div>

                        <div class="container-fluid">
                            <button type="submit" class="btn btn-success btn-lg" name="comissaoagente" id="comissaoagente">Gerar Relatório</button>
                        </div>
                    </form>
                    <!--
                    <h4>Buscar despesa do voucher </h4>
                    <hr>
                    <form action="" method="post">
                        <div class="form-group">
                            <strong><label for="numberVoucher">Nº Voucher</label></strong>
                            <input type="number" name="numberVoucher" id="numberVoucher" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-outline-danger btn-lg" name="buscardespesa" id="buscardespesa">Buscar</button>
                    </form>
                    -->
                </div>
                <!--
                <div class="col-lg-6 pull-right">
                    <form action="relatorio/pdf-relatorio-comissao.php" target="_blank" method="post">
                        <h5>Cadastro de comissão para guias</h5>
                        <hr>
                        <div class="form-group">
                            <strong><label for="nomeguia">Nome do Guia</label></strong>
                            <select class="form-control" id="nomeguia" name="nomeguia">
                                <?php // while( $registroGuia = $listaGuia->fetch( PDO::FETCH_ASSOC ) ){ ?>
                                    <option value="<?php// echo( $registroGuia['id'] ); ?>">
                                        <?php //echo( strtoupper( utf8_encode( $registroGuia['fullname'] ) )  ); ?>
                                    </option>
                                <?php //}?>
                            </select>
                        </div>
                        <div class="form-group">
                            <strong><label for="data">Data</label></strong>
                            <input type="date" class="form-control" name="data" id="data" >
                        </div>
                        <div class="form-group">
                            <strong><label for="valorguia">Valor unitário</label></strong>
                            <input required type="number" name="valorguia" id="valorguia" class="form-control">
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-success btn-lg" name="comissaoguia" id="comissaoguia">Gerar Relatório</button>
                        </div>
                    </form>
                </div>
                -->


                <?php if($contadorCredito > 0) {?>
                    <div class="col-lg-12" style="margin-top: 40px;">
                        <div class="modal fade" id="exemplomodal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                            <div class="modal-dialog modal-lg" role="document">
                                <form action="" method="post">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h4 class="modal-title" id="gridSystemModalLabel">Atualizar Comissão <?php echo(" Voucher ".$voucher) ?></h4>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">X</span></button>

                                        </div>
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label for="creditoscadastrados">Crédito a ser atualizado</label>
                                                <select name="creditoatual" class="form-control" id="creditoscadastrados">
                                                    <?php while ($todosCredito = $buscaCredito->fetch(PDO::FETCH_ASSOC)){ ?>
                                                        <option value="<?php echo($todosCredito['id']) ?>">
                                                            <?php echo("R$ ".$todosCredito['valueagente']) ?>
                                                        </option>
                                                    <?php }?>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="novocredito">Novo Valor</label><br>
                                                <div class="input-group mb-3">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text" id="basic-addon1">R$</span>
                                                    </div>
                                                    <input type="number" class="form-control" name="novocredito"  aria-label="novocredito" aria-describedby="novocredito">
                                                </div>
                                            </div>

                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-success pull-right" name="atualizarcredito">
                                                Atualizar Despesa
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php }?>
            </div>
        </div>
    </div>
    <?php require_once ('footer.php'); ?>
