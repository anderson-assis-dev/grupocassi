<?php require_once ('header.php');


$status = $pdo->prepare('select * from `ct_statusinvoice`');
$status->execute();
$contaCorrente = $pdo->prepare('SELECT * FROM `ct_currentaccount` order by `name`');
$contaCorrente->execute();

$status2 = $pdo->prepare('select * from `ct_statusinvoice`');
$status2->execute();
$contaCorrente2 = $pdo->prepare('SELECT * FROM `ct_currentaccount`');
$contaCorrente2->execute();

if( isset( $_POST['numberVoucher'] ) )
{
        $financeiroReserva = $pdo->prepare(
        'SELECT valuecredit as credito, datacredit as datapagamento FROM `ct_createfaturacredit` where numbervoucher = :voucher ');
        $financeiroReserva->execute(array(":voucher" => $_POST['numberVoucher']));
        $contadorFinanceiro = $financeiroReserva->rowCount();

        $data_one_value = $pdo->prepare(
            'select r.id,r.valueservice * r.qtdpax + r.valueservice / 2 * r.qtdchild as total from `ct_reserva` r where r.numbervoucher = :voucher');
        $data_one_value->execute(array(":voucher" => $_POST['numberVoucher'] ));
        $register_data_one_value = $data_one_value->fetch(PDO::FETCH_ASSOC);

        $data_two_value = $pdo->prepare(
            'select sum(ra.valueservice * ra.qpax + ra.valueservice / 2 * ra.qchild) as totaltwo from `ct_recentlyadd` ra where ra.idrecently =  :id');
        $data_two_value->execute(array(":id" => $register_data_one_value['id'] ));
        $register_data_two_value = $data_two_value->fetch(PDO::FETCH_ASSOC);


}

if (isset( $_POST['salvar'] ))
{
    $valorPago = $pdo->prepare(
        'SELECT SUM(valuecredit) as credito FROM `ct_createfaturacredit` where numbervoucher = :voucher');
    $valorPago->execute( array(":voucher" => $_POST['numberVoucher']) );
    $registroPago = $valorPago->fetch(PDO::FETCH_ASSOC);

    $dadosReserva = $pdo->prepare(
        "SELECT * FROM `ct_reserva` where `numbervoucher` = :numbervoucher");
    $dadosReserva->execute( array(":numbervoucher" => $_POST['numberVoucher'] ));
    $dadosGerais = $dadosReserva->fetch(PDO::FETCH_ASSOC);
    $contador = $dadosReserva->rowCount();
    if($contador > 0)
    {
        $adicionais = $pdo->prepare(
            'SELECT * FROM `ct_recentlyadd` r where r.idrecently = :id');
        $adicionais->execute(array(":id" => $dadosGerais['id'] ) );
        $dadosGerais2  = $adicionais->fetch(PDO::FETCH_ASSOC);

        $total1 =  ( $dadosGerais['valueservice'] * $dadosGerais['qtdpax'] ) + (  ($dadosGerais['valueservice'] / 2 ) * $dadosGerais['qtdchild'] );
        $total2 =  ( $dadosGerais2['valueservice'] * $dadosGerais2['qpax'] ) + (  ($dadosGerais2['valueservice'] / 2 ) * $dadosGerais2['qchild'] );
        if( $registroPago['credito'] >= ( $total1 + $total2 ) )
        {
            $statusEscolhido = $_POST['statusescolhido'];

        }else{
            if($_POST['statusescolhido'] == 2)
            {
                $statusEscolhido = $_POST['statusescolhido'];
            }else{
                $statusEscolhido = 4;
            }

        }
        $voucher         = $_POST['numberVoucher'];
        $contaC          = $_POST['ccfp'];
        $dataVencimento  = $_POST['datavencimento'];
        $dataPagamento   = $_POST['datapagamento'];
        $numeracao       = $_POST['numeracao'];

        $atualizarStatus = $pdo->prepare(
            'update `ct_reserva` set `idstatusinvoice` = :sinvoice where numbervoucher = :voucher ');
        $atualizarStatus->execute( array(
            ":sinvoice" => $statusEscolhido,
            ":voucher"  => $voucher

        ) );

        if( $statusEscolhido == 2 )
        {
            $atualizarStatus = $pdo->prepare(
                'update `ct_reserva` set `idstatus` = :sinvoice where numbervoucher = :voucher ');
            $atualizarStatus->execute( array(
                ":sinvoice" => $statusEscolhido,
                ":voucher"  => $voucher

            ) );
            $auditoria = $pdo->prepare(
                'insert into `ct_audit` (`id`, `idresponsible`, `voucher`, `description`, `date`) values (DEFAULT, :resp, :voucher, :des, :dataa) ');
            $auditoria->execute( array(
                ":resp"    => $_SESSION['id'],
                ":voucher" => $voucher,
                ":des"     => "Status Atualizado Para CANCELADO",
                ":dataa"   => date("Y-m-d H:i:s" )) );
            cacelarVoucher($voucher);
        }
        elseif ($statusEscolhido == 13)
        {
            cacelarVoucher($voucher);
        }
        elseif( $statusEscolhido == 3 )
        {
            $atualizarStatus = $pdo->prepare(
                'update `ct_reserva` set `idstatus` = :sinvoice where numbervoucher = :voucher ');
            $atualizarStatus->execute( array(
                ":sinvoice" => 1,
                ":voucher"  => $voucher
            ) );
            $auditoria = $pdo->prepare(
                'insert into `ct_audit` (`id`, `idresponsible`, `voucher`, `description`, `date`) values (DEFAULT, :resp, :voucher, :des, :dataa) ');
            $auditoria->execute( array(
                ":resp"    => $_SESSION['id'],
                ":voucher" => $voucher,
                ":des"     => "Status Atualizado Para PAGO",
                ":dataa"   => date("Y-m-d H:i:s" )) );
        }
        elseif( $statusEscolhido == 4 )
        {
            $atualizarStatus = $pdo->prepare(
                'update `ct_reserva` set `idstatus` = :sinvoice where numbervoucher = :voucher ');
            $atualizarStatus->execute( array(
                ":sinvoice" => 3,
                ":voucher"  => $voucher
            ) );
            $auditoria = $pdo->prepare(
                'insert into `ct_audit` (`id`, `idresponsible`, `voucher`, `description`, `date`) values (DEFAULT, :resp, :voucher, :des, :dataa) ');
            $auditoria->execute( array(
                ":resp"    => $_SESSION['id'],
                ":voucher" => $voucher,
                ":des"     => "Status Atualizado Para PARCIAL",
                ":dataa"   => date("Y-m-d H:i:s" )) );
        }
        elseif( $statusEscolhido == 8 )
        {
            $atualizarStatus = $pdo->prepare(
                'update `ct_reserva` set `idstatus` = :sinvoice where numbervoucher = :voucher ');
            $atualizarStatus->execute( array(
                ":sinvoice" => 4,
                ":voucher"  => $voucher
            ) );
            $auditoria = $pdo->prepare(
                'insert into `ct_audit` (`id`, `idresponsible`, `voucher`, `description`, `date`) values (DEFAULT, :resp, :voucher, :des, :dataa) ');
            $auditoria->execute( array(
                ":resp"    => $_SESSION['id'],
                ":voucher" => $voucher,
                ":des"     => "Status Atualizado Para REEMBOLSADO",
                ":dataa"   => date("Y-m-d H:i:s" )) );
        }


        $buscarReservas = $pdo->prepare('select * from `ct_reserva` where numbervoucher = :voucher  ');
        $buscarReservas->execute(
            array(
                ":voucher"  => $voucher
            )
        );
        $registro = $buscarReservas->fetch( PDO::FETCH_ASSOC );


        $salvarDados = $pdo->prepare(
            'insert into `ct_createfatura` (`id`, `numbervoucher`, `datematurity`, `datepayment`, `numberadd`, `obervacao`, `idcurrentaccount`, `idcliente`)
                       values (DEFAULT, :voucher, :vencimento, :pagamento, :numeracao, :observacao, :conta, :idcliente) ');
        $salvarDados->execute( array(
            ":voucher"    => $voucher,
            ":vencimento" => $dataVencimento,
            ":pagamento"  => $dataPagamento,
            ":numeracao"  => $numeracao,
            ":observacao" => ".",
            ":conta"      => $contaC,
            ":idcliente"  => $registro['idcliente']
        ) );
        $ultimoIdFatura = $pdo->lastInsertId();

        $auditoria = $pdo->prepare(
            'insert into `ct_audit` (`id`, `idresponsible`, `voucher`, `description`, `date`) values (DEFAULT, :resp, :voucher, :des, :dataa) ');
        $auditoria->execute( array(
            ":resp"    => $_SESSION['id'],
            ":voucher" => $voucher,
            ":des"     => "Fatura Cadastrada ".$numeracao,
            ":dataa"   => date("Y-m-d H:i:s" )) );

        $administrativo = $pdo->prepare(
            'select c.id, c.datematurity, c.datepayment, c.numberadd, cc.name from `ct_createfatura` 
                      c left join ct_currentaccount cc on cc.id = c.idcurrentaccount where numbervoucher = :voucher');
        $administrativo->execute(array(":voucher" => $voucher));
        $contadorAdm = $administrativo->rowCount();

        $buscaid_voucher = $pdo->prepare('select * from `ct_caixa` where nome like :voucheer ');
        $buscaid_voucher->execute(array(":voucheer" => "%".$voucher));
        $dados_buscaid = $buscaid_voucher->fetch(PDO::FETCH_ASSOC);

        $ststus_credito_voucher_caixa = $pdo->prepare(
            'update `ct_caixa` set `idstatus` = :sinvoice, `descricao` = :descricao where id = :voucher');
        $ststus_credito_voucher_caixa->execute(array(":sinvoice" => $statusEscolhido, ":descricao" => $numeracao ,":voucher" => $dados_buscaid['id'] ));

        echo("<div class='alert alert-success' role='alert'>Fatura Cadastradas</div>");
    }

}
if( isset($_POST['atualizar']) )
{
    $voucher = $_POST['voucher'];
    $valorPago = $pdo->prepare(
        'SELECT SUM(valuecredit) as credito FROM `ct_createfaturacredit` where numbervoucher = :voucher');
    $valorPago->execute( array(":voucher" => $_POST['voucher']) );
    $registroPago = $valorPago->fetch(PDO::FETCH_ASSOC);

    $dadosReserva = $pdo->prepare(
        "SELECT * FROM `ct_reserva` where `numbervoucher` = :numbervoucher");
    $dadosReserva->execute( array(":numbervoucher" => $_POST['voucher'] ));
    $dadosGerais = $dadosReserva->fetch(PDO::FETCH_ASSOC);

    $adicionais = $pdo->prepare(
        'SELECT * FROM `ct_recentlyadd` ra where ra.idrecently = :id');
    $adicionais->execute(array(":id" => $dadosGerais['id'] ) );
    $dadosGerais2  = $adicionais->fetch(PDO::FETCH_ASSOC);

    $total1 =  ( $dadosGerais['valueservice'] * $dadosGerais['qtdpax'] ) + (  ($dadosGerais['valueservice'] / 2 ) * $dadosGerais['qtdchild'] );
    $total2 =  ( $dadosGerais2['valueservice'] * $dadosGerais2['qpax'] ) + (  ($dadosGerais2['valueservice'] / 2 ) * $dadosGerais2['qchild'] );

    if( $registroPago['credito'] >= ( $total1 + $total2 ) )
    {
        $statusEscolhido = $_POST['novostatus'];

    }else{
        if( $_POST['novostatus'] == 2 )
        {
            $statusEscolhido = $_POST['novostatus'];
        }else{
            $statusEscolhido = 4;
        }

    }


    $atualizarStatus = $pdo->prepare(
        'update `ct_reserva` set `idstatusinvoice` = :sinvoice where numbervoucher = :voucher ');
    $atualizarStatus->execute( array(
        ":sinvoice" => $statusEscolhido,
        ":voucher"  => $_POST['voucher']

    ) );

    if( $statusEscolhido == 2 )
    {
        $atualizarStatus = $pdo->prepare(
            'update `ct_reserva` set `idstatus` = :sinvoice where numbervoucher = :voucher ');
        $atualizarStatus->execute( array(
            ":sinvoice" =>  $statusEscolhido,
            ":voucher"  =>  $_POST['voucher']

        ) );
        $auditoria = $pdo->prepare(
            'insert into `ct_audit` (`id`, `idresponsible`, `voucher`, `description`, `date`) values (DEFAULT, :resp, :voucher, :des, :dataa) ');
        $auditoria->execute( array(
            ":resp"    => $_SESSION['id'],
            ":voucher" => $voucher,
            ":des"     => "Status Atualizado Para CANCELADO",
            ":dataa"   => date("Y-m-d H:i:s" )) );
        cacelarVoucher($voucher);
    }
    elseif ($statusEscolhido == 13)
    {
        cacelarVoucher($voucher);
    }
    elseif(  $statusEscolhido == 3 )
    {
        $atualizarStatus = $pdo->prepare(
            'update `ct_reserva` set `idstatus` = :sinvoice where numbervoucher = :voucher ');
        $atualizarStatus->execute( array(
            ":sinvoice" => 1,
            ":voucher"  => $_POST['voucher']
        ) );
        $auditoria = $pdo->prepare(
            'insert into `ct_audit` (`id`, `idresponsible`, `voucher`, `description`, `date`) values (DEFAULT, :resp, :voucher, :des, :dataa) ');
        $auditoria->execute( array(
            ":resp"    => $_SESSION['id'],
            ":voucher" => $voucher,
            ":des"     => "Status Atualizado Para PAGO",
            ":dataa"   => date("Y-m-d H:i:s" )) );
    }
    elseif(  $statusEscolhido == 4 )
    {
        $atualizarStatus = $pdo->prepare(
            'update `ct_reserva` set `idstatus` = :sinvoice where numbervoucher = :voucher ');
        $atualizarStatus->execute( array(
            ":sinvoice" => 3,
            ":voucher"  => $_POST['voucher']
        ) );
        $auditoria = $pdo->prepare(
            'insert into `ct_audit` (`id`, `idresponsible`, `voucher`, `description`, `date`) values (DEFAULT, :resp, :voucher, :des, :dataa) ');
        $auditoria->execute( array(
            ":resp"    => $_SESSION['id'],
            ":voucher" => $voucher,
            ":des"     => "Status Atualizado Para PARCIAL",
            ":dataa"   => date("Y-m-d H:i:s" )) );
    }
    elseif(  $statusEscolhido == 8 )
    {
        $atualizarStatus = $pdo->prepare(
            'update `ct_reserva` set `idstatus` = :sinvoice where numbervoucher = :voucher ');
        $atualizarStatus->execute( array(
            ":sinvoice" => 5,
            ":voucher"  => $_POST['voucher']
        ) );
        $auditoria = $pdo->prepare(
            'insert into `ct_audit` (`id`, `idresponsible`, `voucher`, `description`, `date`) values (DEFAULT, :resp, :voucher, :des, :dataa) ');
        $auditoria->execute( array(
            ":resp"    => $_SESSION['id'],
            ":voucher" => $voucher,
            ":des"     => "Status Atualizado Para REEMBOLSADO",
            ":dataa"   => date("Y-m-d H:i:s" )) );
    }

    $status = $pdo->prepare('select * from `ct_statusinvoice` where id = :id');
    $status->execute( array(":id" => $statusEscolhido) );
    $newStatus = $status->fetch(PDO::FETCH_ASSOC);

    $contaCorrente = $pdo->prepare('SELECT * FROM `ct_currentaccount` where `id` = :id');
    $contaCorrente->execute( array(":id" =>$_POST['formapagamento']) );
    $newconta = $contaCorrente->fetch(PDO::FETCH_ASSOC);

    $updateFatura = $pdo->prepare(
            'update `ct_createfatura` set `datematurity` = :venci, `datepayment` = :pagamento, `numbervoucher` = :voucher,
                      `numberadd` = :numeracao, `idcurrentaccount` = :conta where `id` = :id '
    );
    $updateFatura->execute(
            array(
                    ":venci"      => $_POST['vencimento'],
                    ":pagamento"  => $_POST['pagamento'],
                    ":voucher"    => $_POST['voucher'],
                    ":numeracao"  => $_POST['info'],
                    ":conta"      => $_POST['formapagamento'],
                    ":id"         => $_POST['id']
            )
    );
    $auditoria = $pdo->prepare(
        'insert into `ct_audit` (`id`, `idresponsible`, `voucher`, `description`, `date`) values (DEFAULT, :resp, :voucher, :des, :dataa) ');
    $auditoria->execute( array(
        ":resp"    => $_SESSION['id'],
        ":voucher" => $_POST['voucher'],
        ":des"     => "Fatura Atualizada ".$_POST['info'],
        ":dataa"   => date("Y-m-d H:i:s" )) );
    echo("<div class='alert alert-primary' role='alert'>Fatura Atualizada</div>");

}
function cacelarVoucher($voucher)
{
    require_once('class/Cliente.php');
    $cliente = new Cliente();
    require_once('class/Reserva.php');
    $reserva = new Reserva();
    $reserva->setNumeroVoucher($voucher);
    $dados = $reserva->buscarReservaPorVoucher();
    $cliente->setIdCliente($dados[0]['cassiturismo_cliente_idcliente']);
    $cliente->atualizarClientePorRevendedorAtravesDoSistema();
}
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
                                <li class="list-inline-item">Financeiro: Dar baixa</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <div class="row">
        <div id="status"></div>
        <div class="">
            <?php if($contador <= 0 and isset($_POST['numberVoucher'])){ ?>
                <div class="alert alert-danger" role="alert">Não encontramos o voucher <?php echo($_POST['numberVoucher']) ?> .
                    <a href="statusvoucher">Tentar novamente</a></div>
            <?php } else { ?>
                <div class="card">
                    <div class="card-header">
                        <div class="container-fluid">
                            <h4>Atualizar Informações da Fatura:</h4>
                        </div>

                    </div>
                    <div class="card-body">
                        <div class="col-lg-12">

                            <div class="">
                                <form action="" method="post">
                                    <div class="col-md-4 pull-left">
                                        <strong><label for="statusescolhido">Status</label></strong>
                                        <select class="form-control" name="statusescolhido" id="statusescolhido" required>
                                            <option value="1">SELECIONE</option>
                                            <?php while ( $dadosStatus = $status->fetch( PDO::FETCH_ASSOC ) ){ ?>
                                                <option value="<?php echo($dadosStatus['id']); ?>" ><?php echo( utf8_encode( $dadosStatus['nameinvoice'])); ?></option>
                                            <?php }?>
                                        </select>
                                    </div>
                                    <div class="col-md-4 pull-left">
                                        <strong><label for="numberVoucher">Nº Voucher</label></strong>
                                        <input type="text" name="numberVoucher" id="numberVoucher" class="form-control">
                                    </div>
                                    <div class="col-md-4 pull-right">
                                        <strong><label for="ccfp">Forma de Pagamento</label></strong>
                                        <select class="form-control" name="ccfp" id="ccfp" required>
                                            <option value="1">SELECIONE</option>
                                            <?php while ( $dadosConta = $contaCorrente->fetch( PDO::FETCH_ASSOC ) ){ ?>
                                                <option value="<?php echo($dadosConta['id']); ?>" ><?php echo( utf8_encode( strtoupper( $dadosConta['name'] ) ) ); ?></option>
                                            <?php }?>
                                        </select>
                                    </div>
                                    <div class="col-md-4 pull-left">
                                        <strong><label for="datavencimento">Data Vencimento</label></strong>
                                        <input type="date" class="form-control" name="datavencimento" id="datavencimento" value="<?php echo(date("Y-m-d")); ?>" >
                                    </div>
                                    <div class="col-md-4 pull-left">
                                        <strong><label for="datapagamento">Data Pagamento</label></strong>
                                        <input type="date" class="form-control" name="datapagamento" id="datapagamento" value="<?php echo(date("Y-m-d")); ?>" >
                                    </div>
                                    <div class="col-md-4 pull-right">
                                        <strong><label for="numeracao">Númeração (Informações Adicionais)</label></strong>
                                        <input type="text" class="form-control" value="Ok" name="numeracao" id="numeracao" >
                                    </div>
                                    <div class="container-fluid">
                                        <button style="margin-bottom: 20px;" type="submit" class="btn btn-primary btn-block btn-lg" name="salvar" id="salvarfatura">
                                            Salvar informações
                                        </button>
                                    </div>

                                </form>
                                <br>
                                <hr>
                                <?php if( isset( $_POST['salvar'] ) and count($dadosGerais) > 0 ){ ?>

                                    <div class="col-lg-12" style="margin-top: 40px;">
                                        <div class="modal fade" id="exemplomodal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                                            <div class="modal-dialog modal-lg" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title" id="gridSystemModalLabel">Informações registradas</h4>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">X</span></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form action="" method="post">
                                                            <div class="col-md-4 pull-left">
                                                                <strong><label for="voucher">Voucher</label></strong>
                                                                <input class="form-control" name="voucher" id="voucher" value="<?php echo( $voucher ); ?>">
                                                            </div>
                                                            <div class="col-md-4 pull-left">
                                                                <strong><label for="novostatus">Status</label></strong>
                                                                <select class="form-control" name="novostatus" id="novostatus">
                                                                    <?php while ($updateStatus = $status2->fetch(PDO::FETCH_ASSOC) ){ ?>
                                                                        <?php  if($updateStatus['id'] == $statusEscolhido){ ?>
                                                                            <option selected value="<?php echo($updateStatus['id']); ?>">
                                                                                <?php echo( utf8_encode( $updateStatus['nameinvoice'])); ?>
                                                                            </option>
                                                                        <?php } else{?>
                                                                            <option  value="<?php echo($updateStatus['id']); ?>">
                                                                                <?php echo(utf8_encode( $updateStatus['nameinvoice'])); ?>
                                                                            </option>
                                                                        <?php }?>
                                                                    <?php }?>
                                                                </select>

                                                            </div>
                                                            <div class="col-md-4 pull-right">
                                                                <strong><label for="formapagamento">CC</label></strong>
                                                                <select class="form-control" name="formapagamento">
                                                                    <?php while ($infoAdm = $contaCorrente2->fetch(PDO::FETCH_ASSOC) ){ ?>
                                                                        <?php  if($infoAdm['id'] == $contaC){ ?>
                                                                            <option selected value="<?php echo($infoAdm['id']); ?>">
                                                                                <?php echo( utf8_encode( $infoAdm['name'])); ?>
                                                                            </option>
                                                                        <?php } else{?>
                                                                            <option  value="<?php echo($infoAdm['id']); ?>">
                                                                                <?php echo(utf8_encode( $infoAdm['name'])); ?>
                                                                            </option>
                                                                        <?php }?>
                                                                    <?php }?>
                                                                </select>

                                                            </div>
                                                            <div class="col-md-4 pull-left">
                                                                <strong><label for="vencimento">Vencimento</label></strong>
                                                                <input type="date" name="vencimento" id="vencimento" value="<?php echo($dataVencimento); ?>">

                                                            </div>
                                                            <div class="col-md-4 pull-left">
                                                                <strong><label for="pagamento">Pagamento</label></strong>
                                                                <input type="date" name="pagamento" id="pagamento" value="<?php echo($dataPagamento); ?>">

                                                            </div>
                                                            <div class="col-md-4 pull-right">
                                                                <strong><label for="info">Info</label></strong>
                                                                <input class="form-control" name="info" id="info" value="<?php echo( $numeracao ); ?>">
                                                            </div>
                                                            <input type="hidden" name="id" id="id" value="<?php echo($ultimoIdFatura); ?>">
                                                            <button style="margin-bottom: 20px;" type="submit" class="btn btn-primary btn-block btn-lg" name="atualizar" id="atualizarfatura">
                                                                Atualizar informações
                                                            </button>
                                                        </form>
                                                        <p class="pull-right">
                                                            <?php echo("Total do Serviço R$ ".number_format(
                                                                    $register_data_one_value['total'] + $register_data_two_value['totaltwo'], 2,
                                                                    ",", ".")); ?>
                                                        </p><br>
                                                        <?php if($contadorFinanceiro > 0){ ?>
                                                            <h4>Dados Financeiros</h4>
                                                            <hr>

                                                            <div class="table-responsive">
                                                                <table class="table table-bordered">
                                                                    <thead>
                                                                    <tr>
                                                                        <th>Data de Pagamento</th>
                                                                        <th>Valor Pago</th>
                                                                    </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                    <?php while($dadosFinanceiro = $financeiroReserva->fetch( PDO::FETCH_ASSOC ) ){ ?>
                                                                        <tr>
                                                                            <td><?php echo( date('d-m-Y', strtotime($dadosFinanceiro['datapagamento'])) ); ?></td>
                                                                            <td><?php echo( "R$ ".number_format($dadosFinanceiro['credito'], 2, ",", ".") ); ?></td>
                                                                        </tr>
                                                                    <?php }?>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        <?php }?>

                                                        <?php if($contadorAdm > 0){ ?>
                                                            <h4>Dados Administrativos</h4>
                                                            <hr>
                                                            <div class="table-responsive">
                                                                <table class="table table-bordered">
                                                                    <thead>
                                                                    <tr>
                                                                        <th>Data de Vencimento</th>
                                                                        <th>Data de Pagamento</th>
                                                                        <th>Observação</th>
                                                                        <th>Forma de Pagamento</th>

                                                                    </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                    <?php while( $registroAdm = $administrativo->fetch(PDO::FETCH_ASSOC) ){ ?>
                                                                        <tr>
                                                                            <td><?php echo( date('d-m-Y', strtotime($registroAdm['datematurity'])) ); ?></td>
                                                                            <td><?php echo( date('d-m-Y', strtotime($registroAdm['datepayment'])) ); ?></td>
                                                                            <td><?php echo( utf8_encode($registroAdm['numberadd']) ); ?></td>
                                                                            <td><?php echo( utf8_encode($registroAdm['name']) ); ?></td>

                                                                        </tr>
                                                                    <?php }?>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        <?php }?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                <?php } elseif(!empty( $_POST['salvar']) and count($dadosGerais)  == 0 ) {?>
                                    <div class="alert alert-warning" role="alertdialog">Não foi possível encontrar  o vouher. <a href="statusvoucher">Tente Novamente</a></div>
                                <?php }?>

                                <?php if( isset($_POST['atualizar']) ){ ?>

                                    <div class="col-lg-12" style="margin-top: 40px;">
                                        <div class="modal fade" id="exemplomodal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                                            <div class="modal-dialog modal-lg" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title" id="gridSystemModalLabel">Informações fornecidas</h4>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">X</span></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="table-responsive">
                                                            <table class="table table-bordered">
                                                                <thead>
                                                                <tr>
                                                                    <th>Voucher</th>
                                                                    <th>Status</th>
                                                                    <th>Data Vencimento</th>
                                                                    <th>Data Pagamento</th>
                                                                    <th>Númeração</th>
                                                                    <th>Pagamento</th>
                                                                </tr>
                                                                </thead>
                                                                <tbody>
                                                                <tr>
                                                                    <td><?php echo($_POST['voucher']); ?></td>
                                                                    <td><?php echo($newStatus['name']); ?></td>
                                                                    <td><?php echo(date("d/m/Y", strtotime($_POST['vencimento']))); ?></td>
                                                                    <td><?php echo(date("d/m/Y", strtotime($_POST['pagamento']))); ?></td>
                                                                    <td><?php echo($_POST['info']); ?></td>
                                                                    <td><?php echo($newconta['nameinvoice']); ?></td>
                                                                </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                <?php }?>
                            </div>
                        </div>
                    </div>
                </div>

            <?php }?>

        </div>
    </div>

    <?php require_once ('footer.php'); ?>
