<?php
require_once 'header.php';
require_once __DIR__ . '/includes/ref_cache.php';
require_once __DIR__ . '/includes/audit.php';

$pdo->exec("set names utf8");

$listaGuias    = refGuias($pdo);
$listaClientes = refClientes($pdo);
$listaEmpresas = refEmpresas($pdo);
$listaServicos = refServicos($pdo);
$listaPagamentos = refPagamentos($pdo);
//lista horarios
if(empty($_SESSION['idresponsavel']))
{
    header("location: sair");
}
if( isset( $_POST['novareserva'] ) )
{
    if( isset($_POST['horario']) ){
        $horarios = $pdo->prepare('select * from `ct_service_schedule` where `schedule` = :schedule ');
        $horarios->execute(array(":schedule" => $_POST['horario']));
        $listaHorarios = $horarios->fetch(PDO::FETCH_ASSOC);
    }
    if(isset($_POST['horario1']) ){
        $horarios = $pdo->prepare('select * from `ct_service_schedule` where `schedule` = :schedule ');
        $horarios->execute(array(":schedule" => $_POST['horario1']));
        $listaHorarios1 = $horarios->fetch(PDO::FETCH_ASSOC);
    }


    if(isset($_POST['numerovoo']))
    {
        $numerovoo = $_POST['numerovoo'];

    }else{
        $numerovoo = "Não há";
    }
    if(isset($_POST['numerovoo1']))
    {
        $numerovoo1 = $_POST['numerovoo1'];
    }
    else{

        $numerovoo1 = "Não há";
    }
    if( $numerovoo <> $numerovoo1 )
    {
        $numerovoo = $numerovoo1;
    }

    if(empty($_POST['datainicio1']))
    {
        $clienteEscolhido = addslashes( $_POST['cliente']);
        $idempresa = addslashes( $_POST['idempresa']);
        $pax              = addslashes($_POST['pax']);
        $documento        = addslashes( $_POST['documento']);
        $statusEscolhido  = 1;
        $agente           = addslashes( $_POST['agente']);
        $guia             = addslashes( $_POST['guia']);
        $quantidadePax    = addslashes( $_POST['quantidadepax']);
        $quantidadeChild  = addslashes( $_POST['quantidadechild']);
        $quantidadeFree   = addslashes( $_POST['quantidadefree']);
        $dataInicio       = addslashes( $_POST['datainicio']);
        $servico          = addslashes( $_POST['servico']);
        if( $servico == 49 )
        {
            $valor            = 30;
        }elseif($servico == 206){
            $valor            = 10;
        }
        else{
            $valor            = str_replace(",",".", $_POST['valorservico']);
        }
        $foto             = $_POST['foto'];
        $pagamento        = 1;
        $horario          = addslashes( $listaHorarios['idshedule']);
        $timechegada      = strtotime( $_POST['datainicio'] );
        $horarioap        = $_POST['horariobusca'];
        $novoAgente = $pdo->prepare('insert into `ct_agentes` (`id`, `fullname`) values (DEFAULT, :nome) ');
        $novoAgente->execute(array(":nome" => $agente));

        $total_servico = $valor * $quantidadePax + (($valor / 2) * $quantidadeChild);

        $salvarReserva = $pdo->prepare(
            'insert into `ct_reserva` (`id`, `numbervoucher`, `idcliente`, `idempresa` ,`idresponsavel`, `pax`, `documento`, `idstatus`,
                      `idagente`, `idguia`, `qtdpax`, `qtdchild`, `qtdfree`, `dateinput`, `dateoutput`, `idservico`, `valueservice`,`photoresident`, `idhorario`,
                      `horaap` ,`idpayment`, `idstatusinvoice`, `abertura`, `numberfatura`, `totalservico`, `voo`)
                       values (DEFAULT, :numberv ,:idcli, :idempresa ,:idres, :pax, :doc, :idst, :idag, :idgui, :qpax, :qch, :qfree, :din, :dou, :ser, :valor,
                 :photo, :idhor, :horaap ,:idpay, :invoice, :abertura, :fatura, :totalservico, :voo) ');
        $salvarReserva->execute( array(
            ":numberv" => date('y/m/', $timechegada) ,
            ":idcli"  => $clienteEscolhido,
            ":idempresa" => $idempresa,
            ":idres"  => $_SESSION['idresponsavel'],
            ":pax"    => $pax,
            ":doc"    => $documento,
            ":idst"   => $statusEscolhido,
            ":idag"   => $pdo->lastInsertId(),
            ":idgui"  => $guia,
            ":qpax"   => $quantidadePax,
            ":qch"    => $quantidadeChild,
            ":qfree"  => $quantidadeFree,
            ":din"    => $dataInicio,
            ":dou"    => $dataInicio,
            ":ser"    => $servico,
            ":valor"  => $valor,
            ":photo"  => $foto,
            ":idhor"  => $horario,
            ":horaap" => $horarioap,
            ":idpay"  => $pagamento,
            ":invoice"  => 1,
            ":abertura" => date("Y-m-d"),
            ":fatura"   => 0,
            ":totalservico" => $total_servico,
            ":voo"          => $numerovoo
        ) );
        $ultimoId = $pdo->lastInsertId();
        $gerar_voucher = $pdo->prepare('insert into `ct_voucher` (`voucher`) values (DEFAULT) ');
        $gerar_voucher->execute();

        $buscar_voucher = $pdo->prepare('select * from `ct_voucher` where `voucher` = :voucher ');
        $buscar_voucher->execute(array(":voucher" => $pdo->lastInsertId()));
        $id_voucher = $buscar_voucher->fetch(PDO::FETCH_ASSOC);

        $updateNumberVoucher = $pdo->prepare('update `ct_reserva` set `numbervoucher` = :voucher where id = :id ');
        $updateNumberVoucher->execute( array(":voucher" => date('y/m/', $timechegada).$id_voucher['voucher'], ":id" => $ultimoId ) );


        $nameSearchService = $pdo->prepare("select * from `ct_servico` where id = :id ");
        $nameSearchService->execute( array(":id" => $servico) );
        $searchData = $nameSearchService->fetch(PDO::FETCH_ASSOC);


        logAudit($pdo, date('y/m/', $timechegada).$id_voucher['voucher'],
            "A reserva do ".$pax." foi realizada com as seguintes informações:
            \n Embarque: ".date('d-m-Y', strtotime($dataInicio))." Apanha: ".$horarioap." Adultos: ".$quantidadePax." Crianças: ".$quantidadeChild." Free: "
            .$quantidadeFree." Serviço: ".$searchData['fullname']." Complemento: ".$documento." Valor R$ ".$valor." Telefone: ".$foto." Voo às ".$numerovoo." Horario de embarque ".$listaHorarios['schedule']
        );

        $_SESSION['newvoucher'] = date('y/m/', $timechegada).$id_voucher['voucher'];
        $newvoucher             = date('y/m/', $timechegada).$id_voucher['voucher'];
        if( isset($_POST['incluirtaxa']) )
        {
            $vincularServicoVoucher = $pdo->prepare(
                'INSERT INTO `ct_recentlyadd` (`id`, `idrecently`, `idservice`, `documento` ,`valueservice` ,`idschedule`, `horaap`, `dateinput`, `dateoutput`,
                  `qpax`, `qchild`, `qfree`) VALUES   (DEFAULT, :reserva, :service, :docu ,:valor ,:hora, :horaap ,:di, :doo, :qp, :qc, :qf) ');
            $vincularServicoVoucher->execute(
                array(
                    ":reserva" => $ultimoId,
                    ":service" => 19,
                    ":docu"     => $documento,
                    ":valor"   => 20,
                    ":hora"    => addslashes($horario),
                    ":horaap"  => addslashes($horarioap),
                    ":di"      => addslashes($dataInicio),
                    ":doo"     => addslashes( $dataInicio),
                    ":qp"      => addslashes( $quantidadePax),
                    ":qc"      => addslashes( $quantidadeChild),
                    ":qf"      => addslashes( $quantidadeFree)
                )
            );

            $nameSearchService = $pdo->prepare("select * from `ct_servico` where id = :id ");
            $nameSearchService->execute( array(":id" => 19 ) );
            $searchData = $nameSearchService->fetch(PDO::FETCH_ASSOC);

            logAudit($pdo, date('y/m/', $timechegada).$id_voucher['voucher'],
                "A reserva do ".$pax." foi realizada com as seguintes informações:"
                ."\n Embarque: ".date('d-m-Y', strtotime($dataInicio))." Apanha: ".$horarioap." Adultos: ".$quantidadePax." Crianças: ".$quantidadeChild." Free: "
                .$quantidadeFree." Serviço: ".$searchData['fullname']." Complemento: ".$documento." Valor R$ 10,00 Horario de embarque ".$listaHorarios['schedule']
            );
        }
        header("location: editar-pax?numbervoucher=".$newvoucher);
    }
    else{
        $clienteEscolhido = addslashes( $_POST['cliente']);
        $pax              = addslashes($_POST['pax']);
        if(isset($numerovoo) and  $numerovoo <> "Não há")
        {
            $documento        = addslashes( $_POST['documento']).$numerovoo;
        }
        else{
            $documento        = addslashes( $_POST['documento']);
        }
        $statusEscolhido  = 1;
        $agente           = addslashes( $_POST['agente']);
        $guia             = addslashes( $_POST['guia']);
        $quantidadePax    = addslashes( $_POST['quantidadepax']);
        $quantidadeChild  = addslashes( $_POST['quantidadechild']);
        $quantidadeFree   = addslashes( $_POST['quantidadefree']);
        $dataInicio       = addslashes( $_POST['datainicio']);
        $servico          = addslashes( $_POST['servico']);
        if( $servico == 49 )
        {
            $valor            = 30;
        }else{
            $valor            = str_replace(",",".", $_POST['valorservico']);
        }
        $foto             = $_POST['foto'];
        $pagamento        = 1;
        $horario          = addslashes( $listaHorarios['idshedule']);
        $timechegada      = strtotime( $_POST['datainicio'] );
        $horarioap        = $_POST['horariobusca'];
        $novoAgente = $pdo->prepare('insert into `ct_agentes` (`id`, `fullname`) values (DEFAULT, :nome) ');
        $novoAgente->execute(array(":nome" => $agente));

        $total_servico = $valor * $quantidadePax + (($valor / 2) * $quantidadeChild);
        $salvarReserva = $pdo->prepare(
            'insert into `ct_reserva` (`id`, `numbervoucher`, `idcliente`, `idresponsavel`, `pax`, `documento`, `idstatus`,
                      `idagente`, `idguia`, `qtdpax`, `qtdchild`, `qtdfree`, `dateinput`, `dateoutput`, `idservico`, `valueservice`,`photoresident`, `idhorario`,
                      `horaap` ,`idpayment`, `idstatusinvoice`, `abertura`, `numberfatura`, `totalservico`, `voo`)
                       values (DEFAULT, :numberv ,:idcli, :idres, :pax, :doc, :idst, :idag, :idgui, :qpax, :qch, :qfree, :din, :dou, :ser, :valor,
                 :photo, :idhor, :horaap ,:idpay, :invoice, :abertura, :fatura, :totalservico, :voo) ');
        $salvarReserva->execute( array(
            ":numberv" => date('y/m/', $timechegada) ,
            ":idcli"  => $clienteEscolhido,
            ":idres"  => $_SESSION['idresponsavel'],
            ":pax"    => $pax,
            ":doc"    => $documento,
            ":idst"   => $statusEscolhido,
            ":idag"   => $pdo->lastInsertId(),
            ":idgui"  => $guia,
            ":qpax"   => $quantidadePax,
            ":qch"    => $quantidadeChild,
            ":qfree"  => $quantidadeFree,
            ":din"    => $dataInicio,
            ":dou"    => $dataInicio,
            ":ser"    => $servico,
            ":valor"  => $valor,
            ":photo"  => $foto,
            ":idhor"  => $horario,
            ":horaap" => $horarioap,
            ":idpay"  => $pagamento,
            ":invoice"  => 1,
            ":abertura" => date("Y-m-d"),
            ":fatura"   => 0,
            ":totalservico" => $total_servico,
            ":voo"          => $numerovoo
        ) );
        $ultimoId = $pdo->lastInsertId();
        $gerar_voucher = $pdo->prepare('insert into `ct_voucher` (`voucher`) values (DEFAULT) ');
        $gerar_voucher->execute();

        $buscar_voucher = $pdo->prepare('select * from `ct_voucher` where `voucher` = :voucher ');
        $buscar_voucher->execute(array(":voucher" => $pdo->lastInsertId()));
        $id_voucher = $buscar_voucher->fetch(PDO::FETCH_ASSOC);

        $updateNumberVoucher = $pdo->prepare('update `ct_reserva` set `numbervoucher` = :voucher where id = :id ');
        $updateNumberVoucher->execute( array(":voucher" => date('y/m/', $timechegada).$id_voucher['voucher'], ":id" => $ultimoId ) );

        $nameSearchService = $pdo->prepare("select * from `ct_servico` where id = :id ");
        $nameSearchService->execute( array(":id" => $servico) );
        $searchData = $nameSearchService->fetch(PDO::FETCH_ASSOC);


        logAudit($pdo, date('y/m/', $timechegada).$id_voucher['voucher'],
            "A reserva do ".$pax." foi realizada com as seguintes informações:"
            ."\n Embarque: ".date('d-m-Y', strtotime($dataInicio))." Apanha: ".$horarioap." Adultos: ".$quantidadePax." Crianças: ".$quantidadeChild." Free: "
            .$quantidadeFree." Serviço: ".$searchData['fullname']." Complemento: ".$documento." Valor R$ ".$valor." Telefone: ".$foto." Voo às ".$numerovoo." Horario de embarque ".$listaHorarios['schedule']
        );

        $_SESSION['newvoucher'] = date('y/m/', $timechegada).$id_voucher['voucher'];
        $newvoucher             = date('y/m/', $timechegada).$id_voucher['voucher'];

        if( isset($_POST['incluirtaxa']) )
        {
            $vincularServicoVoucher = $pdo->prepare(
                'INSERT INTO `ct_recentlyadd` (`id`, `idrecently`, `idservice`, `documento` ,`valueservice` ,`idschedule`, `horaap`, `dateinput`, `dateoutput`,
                  `qpax`, `qchild`, `qfree`) VALUES   (DEFAULT, :reserva, :service, :docu ,:valor ,:hora, :horaap ,:di, :doo, :qp, :qc, :qf) ');
            $vincularServicoVoucher->execute(
                array(
                    ":reserva" => $ultimoId,
                    ":service" => 19,
                    ":docu"     => $documento,
                    ":valor"   => 20,
                    ":hora"    => addslashes($horario),
                    ":horaap"  => addslashes($horarioap),
                    ":di"      => addslashes($dataInicio),
                    ":doo"     => addslashes( $dataInicio),
                    ":qp"      => addslashes( $quantidadePax),
                    ":qc"      => addslashes( $quantidadeChild),
                    ":qf"      => addslashes( $quantidadeFree)
                )
            );

            $nameSearchService = $pdo->prepare("select * from `ct_servico` where id = :id ");
            $nameSearchService->execute( array(":id" => 19 ) );
            $searchData = $nameSearchService->fetch(PDO::FETCH_ASSOC);

            logAudit($pdo, date('y/m/', $timechegada).$id_voucher['voucher'],
                "A reserva do ".$pax." foi realizada com as seguintes informações:"
                ."\n Embarque: ".date('d-m-Y', strtotime($dataInicio))." Apanha: ".$horarioap." Adultos: ".$quantidadePax." Crianças: ".$quantidadeChild." Free: "
                .$quantidadeFree." Serviço: ".$searchData['fullname']." Complemento: ".$documento." Valor R$ 10,00 Horario de embarque ".$listaHorarios['schedule']
            );
        }

        $vincularServicoVoucher = $pdo->prepare(
            'INSERT INTO `ct_recentlyadd` (`id`, `idrecently`, `idservice`, `documento` ,`valueservice` ,`idschedule`, `horaap`, `dateinput`, `dateoutput`,
                  `qpax`, `qchild`, `qfree`) VALUES   (DEFAULT, :reserva, :service, :docu ,:valor ,:hora, :horaap ,:di, :doo, :qp, :qc, :qf) ');
        $vincularServicoVoucher->execute(
            array(
                ":reserva" => $ultimoId,
                ":service" => addslashes( $_POST['servico1']),
                ":docu"     => addslashes( $_POST['documento1']),
                ":valor"   => str_replace(",",".", $_POST['valorservico1']),
                ":hora"    => addslashes( $listaHorarios1['idshedule']),
                ":horaap"  => $_POST['horariobusca1'],
                ":di"      => addslashes( $_POST['datainicio1']),
                ":doo"     => addslashes( $_POST['datainicio1']),
                ":qp"      => addslashes( $_POST['quantidadepax1']),
                ":qc"      => addslashes( $_POST['quantidadechild1']),
                ":qf"      => addslashes( $_POST['quantidadefree1'])
            )
        );

        $nameSearchService = $pdo->prepare("select * from `ct_servico` where id = :id ");
        $nameSearchService->execute( array(":id" => addslashes( $_POST['servico1'])) );
        $searchData = $nameSearchService->fetch(PDO::FETCH_ASSOC);

        $dados_horario = $pdo->prepare("select * from `ct_service_schedule` where idshedule = :id ");
        $dados_horario->execute( array(":id" => $horario) );
        $dados_horario_value = $dados_horario->fetch(PDO::FETCH_ASSOC);

        logAudit($pdo, date('y/m/', $timechegada).$id_voucher['voucher'],
            "O serviço adicional de ".$pax." foi realizada com as seguintes informações:"
            ."\n Embarque: ".date('d-m-Y', strtotime($_POST['datainicio1']))." Apanha: ".$_POST['horariobusca']
            ." Adultos: ".addslashes($_POST['quantidadepax1'])." Crianças: ".addslashes($_POST['quantidadechild1'])." Free: ".addslashes($_POST['quantidadefree1'])
            ." Serviço: ".$searchData['fullname']." Complemento: ".$_POST['documento1']." Valor R$ "
            .str_replace(",", ".", $_POST['valorservico1'])." Horario de embarque ".$listaHorarios1['schedule']
        );
        if( isset($_POST['incluirtaxa']) )
        {
            $vincularServicoVoucher = $pdo->prepare(
                'INSERT INTO `ct_recentlyadd` (`id`, `idrecently`, `idservice`, `documento` ,`valueservice` ,`idschedule`, `horaap`, `dateinput`, `dateoutput`,
                  `qpax`, `qchild`, `qfree`) VALUES   (DEFAULT, :reserva, :service, :docu ,:valor ,:hora, :horaap ,:di, :doo, :qp, :qc, :qf) ');
            $vincularServicoVoucher->execute(
                array(
                    ":reserva" => $ultimoId,
                    ":service" => addslashes( 19),
                    ":docu"     => addslashes( $_POST['documento1']),
                    ":valor"   => 20,
                    ":hora"    => addslashes($listaHorarios1['idshedule']),
                    ":horaap"  => $_POST['horariobusca1'],
                    ":di"      => addslashes( $_POST['datainicio1']),
                    ":doo"     => addslashes( $_POST['datainicio1']),
                    ":qp"      => addslashes( $_POST['quantidadepax1']),
                    ":qc"      => addslashes( $_POST['quantidadechild1']),
                    ":qf"      => addslashes( $_POST['quantidadefree1'])
                )
            );

            $nameSearchService = $pdo->prepare("select * from `ct_servico` where id = :id ");
            $nameSearchService->execute( array(":id" => 19 ) );
            $searchData = $nameSearchService->fetch(PDO::FETCH_ASSOC);

            logAudit($pdo, date('y/m/', $timechegada).$id_voucher['voucher'],
                "O serviço adicional de ".$pax." foi realizada com as seguintes informações:"
                ."\n Embarque: ".date('d-m-Y', strtotime($_POST['datainicio1']))." Apanha: ".$_POST['horariobusca']
                ." Adultos: ".addslashes($_POST['quantidadepax1'])." Crianças: ".addslashes($_POST['quantidadechild1'])." Free: ".addslashes($_POST['quantidadefree1'])
                ." Serviço: ".$searchData['fullname']." Complemento: ".$_POST['documento1']." Valor R$ 10,00 Horario de embarque ".$listaHorarios1['schedule']
            );
        }
        header("location: editar-pax?numbervoucher=".$newvoucher);
    }


}
?>
<link href="../css/reserva-ui.css" rel="stylesheet" media="all">
<!-- PAGE CONTENT-->
<div class="page-content--bgf7 reserva-ui-page">
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
                                <li class="list-inline-item">Reserva: Nova Reserva</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- END BREADCRUMB-->

    <div class="row">
        <div class="container reserva-ui-container">
            <div class="card card-outline-primary reserva-ui-card">
                <div class="card-body">
                    <?php require_once __DIR__.'/components/reserva-ui-icons.php'; ?>
                    <div class="col-lg-12">
                        <div class="card-header reserva-ui-heading">
                            <div class="card-title">Ordem de serviço</div>
                        </div>
<form novalidate action="" method="post" id="reserva">
    <input type="hidden" name="status" value="1">
    <input type="hidden" name="guia" value="11">
    <div class="alert alert-success" role="alert" style="display:none;" id="calculadora"></div>

    <div class="rui-section">
        <div class="rui-section-title">Identificação</div>
        <div class="rui-grid-3">
            <div class="rui-field">
                <label for="cliente">Agência</label>
                <select name="cliente" id="cliente" class="form-control" required>
                    <option value="" selected disabled>Selecione Agência</option>
                    <?php foreach ($listaClientes as $listaCliente) { ?>
                        <option value="<?php echo htmlentities($listaCliente->id, ENT_QUOTES, 'UTF-8'); ?>">
                            <?php echo htmlentities($listaCliente->fullname, ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <div class="rui-field">
                <label for="idempresa">Empresa</label>
                <select name="idempresa" id="idempresa" class="form-control" required>
                    <option value="" selected disabled>Selecione a Empresa</option>
                    <?php foreach ($listaEmpresas as $item_empresa) { ?>
                        <option value="<?php echo htmlentities($item_empresa->id, ENT_QUOTES, 'UTF-8'); ?>">
                            <?php echo htmlentities($item_empresa->fullname, ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <div class="rui-field">
                <label>Operador(a)</label>
                <input disabled type="text" value="<?php echo $_SESSION['nome']; ?>" class="form-control">
            </div>
        </div>
    </div>

    <div class="rui-section">
        <div class="rui-section-title">Passageiro</div>
        <div class="rui-grid-3">
            <div class="rui-field">
                <label for="documento">Complemento</label>
                <input required type="text" name="documento" id="documento" class="form-control">
            </div>
            <div class="rui-field">
                <label for="pax">Nome dos Passageiros <span>(pax)</span></label>
                <input required onblur="checkNumber(this.value);" type="text" name="pax" id="pax" class="form-control">
            </div>
            <div class="rui-field">
                <label for="foto">Telefone</label>
                <input required type="number" min="10000" name="foto" id="foto" class="form-control">
            </div>
        </div>
    </div>

    <div class="rui-section">
        <div class="rui-section-title">Quantidades</div>
        <div class="rui-grid-3">
            <div class="rui-field">
                <label for="quantidadepax">Adultos <span>(Pax 10+)</span></label>
                <input value="1" type="number" id="quantidadepax" name="quantidadepax" class="form-control">
            </div>
            <div class="rui-field">
                <label for="quantidadechild">Meia <span>(5 a 9 anos, 11 meses e 29 dias)</span></label>
                <input value="0" type="number" name="quantidadechild" id="quantidadechild" class="form-control">
            </div>
            <div class="rui-field">
                <label for="quantidadefree">Free <span>(0 a 4 anos, 11 meses e 29 dias)</span></label>
                <input value="0" type="number" name="quantidadefree" class="form-control">
            </div>
        </div>
    </div>

    <div class="rui-section">
        <div class="rui-section-title">Serviço</div>
        <div class="rui-grid-4">
            <div class="rui-field">
                <label for="datainicio">Data Ida</label>
                <input required type="date" name="datainicio" id="datainicio" class="form-control" value="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="rui-field">
                <label for="servico">Serviço contratado</label>
                <select onchange="servicoselecionado(value);" name="servico" id="servico" class="form-control" required>
                    <option value="3">Selecione o Serviço</option>
                    <?php foreach ($listaServicos as $listaServico) { ?>
                        <?php if ($listaServico->fullname == 'SERVICO DESABILITADO' || $listaServico->fullname == 'TESTE') { ?>
                        <?php } else { ?>
                            <option required value="<?php echo htmlentities($listaServico->id, ENT_QUOTES, 'UTF-8'); ?>">
                                <?php echo htmlentities($listaServico->fullname, ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php } ?>
                    <?php } ?>
                </select>
            </div>
            <div class="rui-field">
                <label for="valorservico">Valor do Serviço</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">R$</span>
                    </div>
                    <input type="text" onKeyPress="return(moeda(this,'.',',',event))" required name="valorservico" id="valorservico" class="form-control">
                </div>
            </div>
            <div class="rui-field">
                <label for="agente">Indicado por</label>
                <input required type="text" value="..." name="agente" id="agente" class="form-control">
            </div>
        </div>
        <div id="voo" style="display:none; margin-top:16px;">
            <div class="rui-grid-2">
                <div class="rui-field">
                    <label for="numerovoo">Horário do Voo</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><svg class="reserva-ui-icon"><use href="#icon-plane"></use></svg></span>
                        </div>
                        <input onblur="habilitarbotao(this.value)" class="form-control" name="numerovoo" id="numerovoo" required type="time">
                    </div>
                </div>
                <div class="alert alert-danger mb-0" style="align-self:center;">
                    Atenção: Se o cliente não souber horário do voo, esse serviço não pode ser criado, favor mudar o serviço.<br>
                    Sugestão: Morro/Cassi Comércio — quando souber o horário do voo, solicitar mudança para Morro/Aeroporto.
                </div>
            </div>
        </div>
    </div>

    <div class="rui-section">
        <div class="rui-section-title">Horários</div>
        <div class="rui-grid-2">
            <div class="rui-field">
                <label for="horariobusca">Horário de Apresentação</label>
                <input type="time" required name="horariobusca" id="horariobusca" class="form-control">
            </div>
            <div class="rui-field">
                <label for="horario">Horário de Embarque</label>
                <select required name="horario" id="horario" class="form-control">
                    <option value="1">Selecione o Horário</option>
                </select>
            </div>
        </div>
    </div>

    <div class="rui-section">
        <div class="rui-section-title">Opções</div>
        <div class="rui-grid-2">
            <div class="rui-field">
                <label for="incluirtaxa"><input type="checkbox" name="incluirtaxa" id="incluirtaxa"> Incluir Taxa de Embarque?</label>
            </div>
            <div class="rui-field">
                <label for="cadastrarvolta"><input type="checkbox" name="cadastrarvolta" id="cadastrarvolta"> Deseja Cadastrar Volta?</label>
            </div>
        </div>
    </div>

    <div id="adicionais">
        <div class="rui-section">
            <div class="rui-section-title">Serviço de Volta</div>
            <div class="rui-grid-2">
                <div class="rui-field">
                    <label>Data Retorno</label>
                    <input required type="date" name="datainicio1" id="datainiciovolta" class="form-control">
                </div>
                <div class="rui-field">
                    <label>Serviço Contratado</label>
                    <select name="servico1" onchange="servicoselecionado1(value);" id="servico1" class="form-control" required>
                        <option selected value="3">Selecione o Serviço</option>
                        <?php foreach ($listaServicos as $listaServico) { ?>
                            <option value="<?php echo htmlentities($listaServico->id, ENT_QUOTES, 'UTF-8'); ?>">
                                <?php echo htmlentities($listaServico->fullname, ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <div class="rui-grid-2" style="margin-top:16px;">
                <div class="rui-field">
                    <label>Complemento</label>
                    <input type="text" name="documento1" id="documento1" class="form-control">
                </div>
                <div class="rui-field">
                    <label>Valor do Serviço</label>
                    <input type="text" onKeyPress="return(moeda(this,'.',',',event))" required name="valorservico1" id="valorservico1" class="form-control">
                </div>
            </div>
            <div class="rui-grid-3" style="margin-top:16px;">
                <div class="rui-field">
                    <label>Adultos <span>(Pax 10+)</span></label>
                    <input value="1" type="number" id="quantidadepax1" name="quantidadepax1" class="form-control">
                </div>
                <div class="rui-field">
                    <label>Meia <span>(5 a 9 anos, 11 meses e 29 dias)</span></label>
                    <input value="0" type="number" id="quantidadechild1" name="quantidadechild1" class="form-control">
                </div>
                <div class="rui-field">
                    <label>Free</label>
                    <input value="0" type="number" name="quantidadefree1" class="form-control">
                </div>
            </div>
            <div class="rui-grid-2" style="margin-top:16px;">
                <div class="rui-field">
                    <label>Horário de Embarque</label>
                    <select required name="horario1" id="horario1" class="form-control"></select>
                </div>
                <div class="rui-field">
                    <label>Horário de Apresentação</label>
                    <input type="time" required name="horariobusca1" id="horariobusca1" class="form-control">
                </div>
            </div>
        </div>
        <div id="voo1" style="display:none;">
            <div class="rui-section">
                <div class="rui-grid-2">
                    <div class="rui-field">
                        <label for="numerovoo1">Horário do Voo (Volta)</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><svg class="reserva-ui-icon"><use href="#icon-plane"></use></svg></span>
                            </div>
                            <input onblur="habilitarbotao1(this.value)" class="form-control" name="numerovoo1" id="numerovoo1" required type="time">
                        </div>
                    </div>
                    <div class="alert alert-danger mb-0" style="align-self:center;">
                        Atenção: Se o cliente não souber horário do voo, esse serviço não pode ser criado, favor mudar o serviço.<br>
                        Sugestão: Morro/Cassi Comércio — quando souber o horário do voo, solicitar mudança para Morro/Aeroporto.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div style="margin-top:8px;">
        <button type="submit" id="novareservacadastro" class="btn btn-primary btn-lg btn-block" name="novareserva">
            <svg><use href="#icon-save"></use></svg><span>Cadastrar</span>
        </button>
    </div>
</form>
						<br>
                    </div
                </div>
            </div>
        </div>
    </div>

    <?php require_once ('footer.php'); ?>
    <script type="text/javascript">
        document.getElementById('cadastrarvolta').addEventListener('change', function() {
            document.getElementById('adicionais').style.display = this.checked ? 'block' : 'none';
        });

        var horarioembarque;
        var horariovoo;
        var resultado;
        function checkNumber(valor) {
            var regra = /^[A-z0-9-""]+$/;
            var regra1 = /^[0-9-" "]+$/;
            var regra2 = /^[A-z]+$/;
            if (valor.match(regra2))
            {
                console.log("validado")
            }else if(valor.match(regra1))
            {
                alert("Informe o nome do passageiro");
            }else if(valor.match(regra))
            {
                alert("Informe o nome do passageiro");
            }
        };
        function moeda(a, e, r, t) {
            let n = ""
                , h = j = 0
                , u = tamanho2 = 0
                , l = ajd2 = ""
                , o = window.Event ? t.which : t.keyCode;
            if (13 == o || 8 == o)
                return !0;
            if (n = String.fromCharCode(o),
            -1 == "0123456789".indexOf(n))
                return !1;
            for (u = a.value.length,
                     h = 0; h < u && ("0" == a.value.charAt(h) || a.value.charAt(h) == r); h++)
                ;
            for (l = ""; h < u; h++)
                -1 != "0123456789".indexOf(a.value.charAt(h)) && (l += a.value.charAt(h));
            if (l += n,
            0 == (u = l.length) && (a.value = ""),
            1 == u && (a.value = "0" + r + "0" + l),
            2 == u && (a.value = "0" + r + l),
            u > 2) {
                for (ajd2 = "",
                         j = 0,
                         h = u - 3; h >= 0; h--)
                    3 == j && (ajd2 += e,
                        j = 0),
                        ajd2 += l.charAt(h),
                        j++;
                for (a.value = "",
                         tamanho2 = ajd2.length,
                         h = tamanho2 - 1; h >= 0; h--)
                    a.value += ajd2.charAt(h);
                a.value += r + l.substr(u - 2, u)
            }
            return !1
        }
        function servicoselecionado(value) {
            if(value == 49)
            {
                console.log(value);
                document.getElementById('valorservico').value = '30,00';
                document.getElementById("valorservico").disabled = true; // Desabilitar
            }else if(value == 206){
                document.getElementById('valorservico').value = '10,00';
                document.getElementById("valorservico").disabled = true; // Desabilitar
            } else if(value == 8){
                document.getElementById("novareservacadastro").disabled = true; // Habilitar
                document.getElementById('voo').style.display = 'block';
                $.ajax({
                    type: 'POST',
                    url: 'teste.php',
                    data: {allproduct: 1 },
                    dataType: 'json',
                    success: function(response){
                        console.log(response);
                        $("#horario").empty();
                        $.each(response, function(key, item){
                            $("#horario").append(
                                `<option id="${item.idshedule}">${item.schedule}</option>`);
                        });
                    }
                });
            }
            else if(value == 8){
                document.getElementById("novareservacadastro").disabled = true; // Habilitar
                document.getElementById('voo').style.display = 'block';
                $.ajax({
                    type: 'POST',
                    url: 'teste.php',
                    data: {allproduct: 2, idservice:3 },
                    dataType: 'json',
                    success: function(response){
                        $("#horario").empty();
                        $.each(response, function(key, item){
                            $("#horario").append(
                                `<option id="${item.idshedule}">${item.schedule}</option>`);
                        });
                    }
                });

            }else{
                $.ajax({
                    type: 'POST',
                    url: 'teste.php',
                    data: {allproduct: 0 },
                    dataType: 'json',
                    success: function(response){
                        $("#horario").empty();
                        $.each(response, function(key, item){
                            $("#horario").append(`<option id="${item.idshedule}">${item.schedule}</option>`);
                        });
                    }
                });
                document.getElementById('voo').style.display = 'none';
                document.getElementById("valorservico").disabled = false; //
                //document.getElementById('valorservico').value = '';
            }
        }

        function servicoselecionado1(value) {
            if(value == 49)
            {
                console.log(value);
                document.getElementById('valorservico').value = '30,00';
                document.getElementById("valorservico").disabled = true; // Desabilitar
            }else if(value == 206){
                document.getElementById('valorservico').value = '10,00';
                document.getElementById("valorservico").disabled = true; // Desabilitar
            } else if(value ==  8){
                document.getElementById("novareservacadastro").disabled = true; // Habilitar
                document.getElementById('voo1').style.display = 'block';
                $.ajax({
                    type: 'POST',
                    url: 'teste.php',
                    data: {allproduct: 1 },
                    dataType: 'json',
                    success: function(response){
                        $("#horario1").empty();
                        $.each(response, function(key, item){
                            $("#horario1").append(
                                `<option id="${item.idshedule}">${item.schedule}</option>`);
                        });
                    }
                });

            }else{
                $.ajax({
                    type: 'POST',
                    url: 'teste.php',
                    data: {allproduct: 0 },
                    dataType: 'json',
                    success: function(response){
                        $("#horario1").empty();
                        $.each(response, function(key, item){
                            $("#horario1").append(`<option id="${item.idshedule}">${item.schedule}</option>`);
                        });
                    }
                });
                document.getElementById('voo1').style.display = 'none';
                document.getElementById("valorservico").disabled = false; //
                //document.getElementById('valorservico').value = '';
            }
        }
        function habilitarbotao(){
            if(document.getElementById('numerovoo').value.length  < 1){

                document.getElementById("novareservacadastro").disabled = true;
            }else {
                document.getElementById("novareservacadastro").disabled = false;

            }
        }
        function habilitarbotao1(){
            if(document.getElementById('numerovoo1').value.length  < 1){

                document.getElementById("novareservacadastro").disabled = true;
            }else {
                document.getElementById("novareservacadastro").disabled = false;

            }
        }

    </script>