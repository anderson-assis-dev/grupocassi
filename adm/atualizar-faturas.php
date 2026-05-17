<?php require_once ('header.php');
$status = $pdo->prepare('select * from `ct_statusinvoice`');
$status->execute();
$contaCorrente = $pdo->prepare('SELECT * FROM `ct_currentaccount` ');
$contaCorrente->execute();
if( isset( $_POST['todosVoucher'] ) )
{
    $datainicio   = $_POST['datainicio'];
    $datafinal    = $_POST['datafim'];
    $idcliente    = $_POST['idcliente'];
    $ststus       = $_POST['statusescolhido'];
    $totalLiquido = $_POST['valorFaturaGeral'];
    $totalBruto   = $_POST['valorBruto'];
    $totalNet     = $_POST['valorNet'];
    $stual        = $_POST['stual'];


}
if (isset( $_POST['salvar'] ))
{
    $datainicio      = $_POST['periodoinicial'];
    $datafinal       = $_POST['periodofinal'];
    $idcliente       = $_POST['idcliente'];
    $statusEscolhido = $_POST['statusescolhido'];
    $contaC          = $_POST['ccfp'];
    $dataVencimento  = $_POST['datavencimento'];
    $dataPagamento   = $_POST['datapagamento'];
    $numeracao       = $_POST['numeracao'];
    $observacao      = $_POST['observacao'];
    $formapagamento  = $_POST['formapagamento'];

    $atualizarStatus = $pdo->prepare(
        'update `ct_reserva` set `idstatusinvoice` = :sinvoice where ct_reserva.dateinput >= :inicio 
                       and ct_reserva.dateinput <= :fim and ct_reserva.dateoutput >= :inicioo and ct_reserva.dateoutput <= :fimo and `idpayment` = :pag ');
    $atualizarStatus->execute( array(
        ":sinvoice" => $statusEscolhido,
        ":inicio"   => $datainicio,
        ":fim"      => $datafinal,
        ":inicioo"  => $datainicio,
        ":pag"      => $formapagamento,
        ":fimo"     => $datafinal

    ) );

    $buscarReservas = $pdo->prepare('select * from `ct_reserva` where ct_reserva.dateinput >= :inicio 
                       and ct_reserva.dateinput <= :fim and ct_reserva.dateoutput >= :inicioo and ct_reserva.dateoutput <= :fimo and `idpayment` = :pag  ');
    $buscarReservas->execute(array(
        ":inicio"   => $datainicio,
        ":fim"      => $datafinal,
        ":inicioo"  => $datainicio,
        ":pag"      => $formapagamento,
        ":fimo"     => $datafinal));
    $registro = $buscarReservas->fetchAll( PDO::FETCH_CLASS );

    foreach ($registro as $registros)
    {
        $verificarDados = $pdo->prepare('select * from `ct_createfatura` where `numbervoucher` = :voucher');
        $verificarDados->execute(array(":voucher" => $registros->numbervoucher ));
        $contador = $verificarDados->rowCount();
        if( $contador > 0 )
        {
            $updateDados = $pdo->prepare(
                'update `ct_createfatura` set `datematurity` = :dvencimento,`datepayment` = :dpagamento, `numberadd` = :numero, `obervacao` = :obs,
                      `idcurrentaccount` = :contac where `numbervoucher` = :voucher ');
            $updateDados->execute(array(
                ":dvencimento" => $dataVencimento,
                ":dpagamento"  => $dataPagamento,
                ":numero"      => $numeracao,
                ":obs"         => $observacao,
                ":contac"      => $contaC,
                ":voucher"     => $registros->numbervoucher
            ));

            $auditoria = $pdo->prepare(
                'insert into `ct_audit` (`id`, `idresponsible`, `voucher`, `description`, `date`) values (DEFAULT, :resp, :voucher, :des, :dataa) ');
            $auditoria->execute( array(
                ":resp"    => $_SESSION['id'],
                ":voucher" => $registros->numbervoucher,
                ":des"     => "Fatura Cadastrada",
                ":dataa"   => date("Y-m-d H:i:s" )) );
        }
        else{
            $salvarDados = $pdo->prepare(
                'insert into `ct_createfatura` (`id`, `numbervoucher`, `datematurity`, `datepayment`, `numberadd`, `obervacao`, `idcurrentaccount`, `idcliente`)
                          values (DEFAULT, :voucher, :vencimento, :pagamento, :numeracao, :observacao, :conta, :idcliente) ');
            $salvarDados->execute( array(
                ":voucher"    => $registros->numbervoucher,
                ":vencimento" => $dataVencimento,
                ":pagamento"  => $dataPagamento,
                ":numeracao"  => $numeracao,
                ":observacao" => $observacao,
                ":conta"      => $contaC,
                ":idcliente"  => $registros->idcliente
            ) );

            $auditoria = $pdo->prepare(
                'insert into `ct_audit` (`id`, `idresponsible`, `voucher`, `description`, `date`) values (DEFAULT, :resp, :voucher, :des, :dataa) ');
            $auditoria->execute( array(
                ":resp"    => $_SESSION['id'],
                ":voucher" => $registros->numbervoucher,
                ":des"     => "Fatura Cadastrada",
                ":dataa"   => date("Y-m-d H:i:s" )) );
        }

    }
    echo("<div class='alert alert-success' role='alert'>Faturas Cadastradas</div>");
    var_dump($datafinal, $datainicio, $dataPagamento, $dataVencimento, $formapagamento);
    //header("location: ./fatura.php");
}

?>
<style>
    textarea{
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
                                <li class="list-inline-item">Financeiro: Fatura - Atualizar Fatura</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <div class="row">
        <div id="status"></div>
        <div class="container">
            <div class="col-lg-12">
                <h4>Atualizar Informações da Fatura:</h4>
                <hr>
                <div class="">
                    <form action="" method="post">
                        <div class="col-md-6 pull-left">
                            <strong><label for="statusescolhido">Status</label></strong>
                            <select class="form-control" name="statusescolhido" id="statusescolhido" required>
                                <?php while ( $dadosStatus = $status->fetch( PDO::FETCH_ASSOC ) ){ ?>
                                    <option value="<?php echo($dadosStatus['id']); ?>" ><?php echo($dadosStatus['nameinvoice']); ?></option>
                                <?php }?>
                            </select>
                        </div>
                        <div class="col-md-6 pull-right">
                            <strong><label for="ccfp">Contato Corrente (Forma de Pagamento)</label></strong>
                            <select class="form-control" name="ccfp" id="ccfp" required>
                                <?php while ( $dadosConta = $contaCorrente->fetch( PDO::FETCH_ASSOC ) ){ ?>
                                    <option value="<?php echo($dadosConta['id']); ?>" ><?php echo( utf8_encode( strtoupper( $dadosConta['name'] ) ) ); ?></option>
                                <?php }?>
                            </select>
                        </div>
                        <div class="col-md-4 pull-left">
                            <strong><label for="datavencimento">Data Vencimento</label></strong>
                            <input type="date" class="form-control" name="datavencimento" id="datavencimento" >
                        </div>
                        <div class="col-md-4 pull-left">
                            <strong><label for="datapagamento">Data Pagamento</label></strong>
                            <input type="date" class="form-control" name="datapagamento" id="datapagamento" >
                        </div>
                        <div class="col-md-4 pull-right">
                            <strong><label for="numeracao">Númeração (Informações Adicionais)</label></strong>
                            <input type="text" class="form-control" value="Ok" name="numeracao" id="numeracao" >
                        </div>
                        <div class="">
                            <strong><label for="observacao">Observação</label></strong>
                            <textarea class="form-control" rows="5" cols="5" name="observacao" id="observacao"></textarea>
                        </div>
                        <input type="hidden" name="cliente" value="<?php echo( $idcliente ); ?>" >
                        <input type="hidden" name="periodoinicial" value="<?php echo( $datainicio ); ?>" >
                        <input type="hidden" name="periodofinal" value="<?php echo( $datafinal ); ?>" >
                        <input type="hidden" name="formapagamento" value="<?php echo( $ststus ); ?>" >
                        <button style="margin-bottom: 20px;" type="submit" class="btn btn-primary btn-lg" name="salvar" id="salvar">
                            Salvar informações
                        </button>
                    </form>
                </div>
                <h4>Resumo Financeiro:</h4>
                <hr>
                <?php var_dump($ststus); ?>
            </div>
        </div>
    </div>
    <?php require_once ('footer.php'); ?>
