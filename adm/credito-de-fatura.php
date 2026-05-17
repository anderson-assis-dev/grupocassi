<?php require_once ('header.php');
$contaCorrente = $pdo->prepare('SELECT * FROM `ct_currentaccount` ');
$contaCorrente->execute();
$planoContas = $pdo->prepare('SELECT * FROM `ct_planaccounts` ');
$planoContas->execute();

$total = 0;


if( isset($_POST['credito']) )
{
    $voucher         = $_POST['numberVoucher'];
    $desc            = $_POST['desc'];
    $datacredito     = $_POST['datacredito'];
    $valordocredito  = $_POST['valordocredito'];
    $ccfp            = $_POST['ccfp'];

    $cartao = $pdo->prepare('SELECT * FROM `ct_currentaccount` where id = :id ');
    $cartao->execute( array(":id" => $ccfp ) );
    $dadosCartao = $cartao->fetch(PDO::FETCH_ASSOC);

    $pago = $pdo->prepare('select sum(valuecredit) as total from `ct_createfaturacredit` where numbervoucher = :voucher ');
    $pago->execute(array(":voucher" => $voucher));
    $totalPago = $pago->fetch(PDO::FETCH_ASSOC);

    if($ccfp == 0)
    {
        echo("<div class='alert alert-danger' role='alert'>Você precisar selecionar um forma de pagamento</div>");
    }elseif ($valordocredito <= 0){
        echo("<div class='alert alert-danger' role='alert'>Você não pode lançar um valor menor ou igual a R$0.00</div>");
    }
    else{
        $buscarVoucher = $pdo->prepare(
            'SELECT pax, qtdpax, qtdchild, valueservice, idcliente, r.idservico, s.tarifaone, r.pax, s.mabelazure FROM `ct_reserva` r
                  left join `ct_servico` s on r.idservico =  s.id
                  where r.numbervoucher = :voucher');
        $buscarVoucher->execute( array(":voucher" => $voucher) );
        $dadosVoucher = $buscarVoucher->fetch( PDO::FETCH_ASSOC );

        $totalReserva = ( ($dadosVoucher['valueservice'] * $dadosVoucher['qtdpax'] ) + ( ($dadosVoucher['valueservice'] / 2) * $dadosVoucher['qtdchild'] ) ) ;

        $buscarVoucherAdd = $pdo->prepare(
            'SELECT ra.valueservice, qpax, qchild, r.idcliente, ra.idservice, s.tarifaone,s.mabelazure FROM `ct_recentlyadd` ra 
                  left join `ct_reserva` r on r.id = ra.idrecently left join `ct_servico` s on ra.idservice =  s.id  where r.numbervoucher = :voucher ');
        $buscarVoucherAdd->execute( array(":voucher" => $voucher));
        $registro = $buscarVoucherAdd->fetchAll( PDO::FETCH_CLASS);

        foreach ($registro as $item)
        {
            $totalReservaAdd = ( ($item->valueservice * $item->qpax ) + (($item->valueservice  / 2) * $item->qchild ) );
            $total = $total + $totalReservaAdd;
        }
        $geral = $total + $totalReserva;
        $novoCredito = $pdo->prepare(
            'insert into `ct_createfaturacredit` (`id`, `numbervoucher`, `tarifa`, `desccredit`, `datacredit`, `valuecredit`, `valueguia`, 
                    `dataguia`, `valueagente`, `dataagente`, `idaccountcurrent`, `idplancount`) values 
                   (DEFAULT, :numbervoucher, :tarifa, :desccredit, :datacredit, :valuecredit, :vg ,:vgd, :va, :vad, :cconte, :plano)');
        $novoCredito->execute( array(
            ":numbervoucher"    => $voucher,
            ":tarifa"           => 0,
            ":desccredit"       => $desc,
            ":datacredit"       => $datacredito,
            ":valuecredit"      => $valordocredito,
            ":vg"               => 0,
            ":vgd"              => '0000-00-00',
            ":va"               => 0,
            ":vad"              => '0000-00-00',
            ":cconte"           => $ccfp,
            ":plano"            => 1
        ) );

        if( $ccfp == 24 or $ccfp == 25 or $ccfp == 37 or $ccfp == 12)
        {
            if(  $totalPago['total'] == $geral )
            {
                $salvarDados = $pdo->prepare(
                    'insert into `ct_createfatura` (`id`, `numbervoucher`, `datematurity`, `datepayment`, `numberadd`, `obervacao`, `idcurrentaccount`, `idcliente`)
                          values (DEFAULT, :voucher, :vencimento, :pagamento, :numeracao, :observacao, :conta, :idcliente) ');
                $salvarDados->execute( array(
                    ":voucher"    => $voucher,
                    ":vencimento" => $datacredito,
                    ":pagamento"  => $datacredito,
                    ":numeracao"  => $_SESSION['nome']."  ".date("d-m-Y", strtotime( $datacredito))." -> R$".$valordocredito." ".$dadosCartao['name'],
                    ":observacao" => ".",
                    ":conta"      => $ccfp,
                    ":idcliente"  => $dadosGerais['idcliente']
                ) );

                $atualizarStatus = $pdo->prepare(
                    'update `ct_reserva` set `idstatusinvoice` = :sinvoice where numbervoucher = :voucher ');
                $atualizarStatus->execute( array(
                    ":sinvoice" => 3,
                    ":voucher"  => $voucher

                ) );

                $auditoria = $pdo->prepare(
                    'insert into `ct_audit` (`id`, `idresponsible`, `voucher`, `description`, `date`) values (DEFAULT, :resp, :voucher, :des, :dataa) ');
                $auditoria->execute( array(
                    ":resp"    => $_SESSION['id'],
                    ":voucher" => $voucher,
                    ":des"     => "Fatura Cadastrada ".$_SESSION['nome']." ".date("d-m-Y", strtotime( $datacredito))." R$".$valordocredito." ".$dadosCartao['name'],
                    ":dataa"   => date("Y-m-d H:i:s" )) );
            }
            else{

                $atualizarStatus = $pdo->prepare(
                    'update `ct_reserva` set `idstatusinvoice` = :sinvoice where numbervoucher = :voucher ');
                $atualizarStatus->execute( array(
                    ":sinvoice" => 5,
                    ":voucher"  => $voucher

                ) );

                if( $ccfp == 24 or $ccfp == 25 or $ccfp == 37 or $ccfp == 12 )
                {
                    $salvarDados = $pdo->prepare(
                        'insert into `ct_createfatura` (`id`, `numbervoucher`, `datematurity`, `datepayment`, `numberadd`, `obervacao`, `idcurrentaccount`, `idcliente`)
                          values (DEFAULT, :voucher, :vencimento, :pagamento, :numeracao, :observacao, :conta, :idcliente) ');
                    $salvarDados->execute( array(
                        ":voucher"    => $voucher,
                        ":vencimento" => $datacredito,
                        ":pagamento"  => $datacredito,
                        ":numeracao"  => $_SESSION['nome']."  ".date("d-m-Y", strtotime( $datacredito))." -> R$".$valordocredito." ".$dadosCartao['name'],
                        ":observacao" => ".",
                        ":conta"      => $ccfp,
                        ":idcliente"  => $dadosGerais['idcliente']
                    ) );
                    $auditoria = $pdo->prepare(
                        'insert into `ct_audit` (`id`, `idresponsible`, `voucher`, `description`, `date`) values (DEFAULT, :resp, :voucher, :des, :dataa) ');
                    $auditoria->execute( array(
                        ":resp"    => $_SESSION['id'],
                        ":voucher" => $voucher,
                        ":des"     => "Fatura Cadastrada ".$_SESSION['nome']." ".date("d-m-Y", strtotime( $datacredito))." R$".$valordocredito." ".$dadosCartao['name'],
                        ":dataa"   => date("Y-m-d H:i:s" )) );
                }
            }

        }

        if( $_SESSION['id']  == 28 or $_SESSION['id']  == 1 and $dadosVoucher['idservico'] == 15)
        {
            $salvarDados = $pdo->prepare(
                'insert into `ct_createfatura` (`id`, `numbervoucher`, `datematurity`, `datepayment`, `numberadd`, `obervacao`, `idcurrentaccount`, `idcliente`)
                          values (DEFAULT, :voucher, :vencimento, :pagamento, :numeracao, :observacao, :conta, :idcliente) ');
            $salvarDados->execute( array(
                ":voucher"    => $voucher,
                ":vencimento" => $datacredito,
                ":pagamento"  => $datacredito,
                ":numeracao"  => $_SESSION['nome']." -> ".date("d-m-Y", strtotime( $datacredito))." -> R$".$valordocredito,
                ":observacao" => ".",
                ":conta"      => $ccfp,
                ":idcliente"  => $dadosVoucher['idcliente']
            ) );

            $atualizarStatus = $pdo->prepare(
                'update `ct_reserva` set `idstatusinvoice` = :sinvoice where numbervoucher = :voucher ');
            $atualizarStatus->execute( array(
                ":sinvoice" => 3,
                ":voucher"  => $voucher

            ) );

            $auditoria = $pdo->prepare(
                'insert into `ct_audit` (`id`, `idresponsible`, `voucher`, `description`, `date`) values (DEFAULT, :resp, :voucher, :des, :dataa) ');
            $auditoria->execute( array(
                ":resp"    => $_SESSION['id'],
                ":voucher" => $voucher,
                ":des"     => "Fatura Cadastrada ".$_SESSION['nome']." -> ".date("d-m-Y", strtotime( $datacredito))." -> R$".$valordocredito,
                ":dataa"   => date("Y-m-d H:i:s" )) );
        }

        $auditoria = $pdo->prepare(
            'insert into `ct_audit` (`id`, `idresponsible`, `voucher`, `description`, `date`) values (DEFAULT, :resp, :voucher, :des, :dataa) ');
        $auditoria->execute( array(
            ":resp"    => $_SESSION['id'],
            ":voucher" => $voucher,
            ":des"     => "Crédito no valor de R$ ". $valordocredito." cadastrado com sucesso.",
            ":dataa"   => date("Y-m-d H:i:s" )) );
        echo (
            "<div class='alert alert-success' role='alert'>
                   Credito no valor de R$".number_format($valordocredito, 2, ",", ".")." adicionado para ".$dadosVoucher['pax']."
                    <form action='./relatorio/pdf-relatorio-voucher' target='_blank' method='post'>
                        <input type='hidden' name='voucher' value='$voucher' >
                        <button type='submit' style='background: transparent; border: none; color: red;'> Imprimir Voucher </button>
                    </form>
                    
                </div>"
        );
    }


}

?>
<style>
    .col-lg-4, .col-lg-6{
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
                                <li class="list-inline-item">Financeiro: Cadastar Pagamento</li>
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
                <h4>Informações de Pagamento: </h4>
                <hr>
                <form action="" method="post">
                    <div class="col-lg-6 pull-left">
                        <strong><label for="numberVoucher">Nº Voucher</label></strong>
                        <input type="number" name="numberVoucher" value="<?php echo(  $_SESSION['newvoucher'] ); ?>" id="numberVoucher" class="form-control">
                    </div>
                    <div class="col-lg-6 pull-right">
                        <strong><label for="desc">Descrição do Crédito</label></strong>
                        <input type="text" name="desc" id="desc" class="form-control" value="Crédito Pago">
                    </div>
                    <div class="col-lg-4 pull-left">
                        <strong><label for="datacredito">Data do Creditamento</label></strong>
                        <input type="date" name="datacredito" id="datacredito" class="form-control" value="<?php echo(date('Y-m-d')); ?>" >
                    </div>
                    <div class="col-lg-4 pull-left">
                        <strong><label for="valordocredito">Valor do Crédito </label></strong>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1">R$</span>
                                <input type="text" name="valordocredito" id="valordocredito" class="form-control" >
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 pull-right">
                        <strong><label for="ccfp">(Forma de Pagamento)</label></strong>
                        <select class="form-control" name="ccfp" id="ccfp" required>
                            <?php while ( $dadosConta = $contaCorrente->fetch( PDO::FETCH_ASSOC ) ){ ?>
                                <option value="<?php echo($dadosConta['id']); ?>" ><?php echo( utf8_encode( strtoupper( $dadosConta['name'] ) ) ); ?></option>
                            <?php }?>
                            <option value="0" selected> Selecione</option>
                        </select>
                    </div>
                    <div class="container pull-left">
                        <button type="submit" class="btn btn-success btn-lg" name="credito" id="credito">Salvar Crédito</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php require_once ('footer.php'); ?>
