<?php require_once ('header.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require './../vendor/autoload.php';

$mail = new PHPMailer(true);

$numberVoucher = $_POST['numbervoucher'];

$buscaCredito = $pdo->prepare(
        'SELECT cfc.id, valuecredit, `name`, datacredit FROM `ct_createfaturacredit` cfc left join `ct_currentaccount` cc on cfc.idaccountcurrent = cc.id where `numbervoucher` = :voucher');
$buscaCredito->execute( array(":voucher" => $numberVoucher ) );
$registroCredito = $buscaCredito->fetchAll(PDO::FETCH_CLASS);
$contadorCredito = $buscaCredito->rowCount();

$dadosReservaAu  = $pdo->prepare("select * from `ct_audit` where `voucher` = :numberVoucher ");
$dadosReservaAu->execute( array(":numberVoucher" => $numberVoucher ) );
$registroAu      =  $dadosReservaAu->fetchAll(PDO::FETCH_CLASS);
$contadorAuditoria = $dadosReservaAu->rowCount();

$dadosReserva = $pdo->prepare(
    "SELECT r.id,pax, documento,photoresident ,dateinput, dateoutput, photoresident, c.fullname as cliente, s.fullname as `status`, r.valueservice, r.horaap,
            se.fullname as serivco, ag.fullname as agente, namepayment, g.id as guia, qtdpax, qtdchild, qtdfree, ss.schedule,numbervoucher, r.idstatusinvoice, r.abertura
            FROM `ct_reserva` r left join ct_cliente c on c.id = r.idcliente
            left join ct_responsavel re on re.id = r.idresponsavel left join ct_status s on s.id = r.idstatus
            left join ct_guia g on g.id = r.idguia join ct_servico se on se.id = r.idservico
            left join ct_agentes as ag on r.idagente = ag.id
            left join ct_service_schedule ss on ss.idshedule = r.idhorario left join `ct_form_of_ payment` as cfp
            on cfp.id = r.idpayment  where `numbervoucher` = :numbervoucher");
$dadosReserva->execute( array(":numbervoucher" => $numberVoucher ));
$dadosGerais = $dadosReserva->fetch(PDO::FETCH_ASSOC);

$adicionais = $pdo->prepare(
    'SELECT ra.id,ra.dateinput as ap, s.fullname,s.screenplay, ss.schedule, qpax, qchild, qfree, ra.dateinput, ra.dateoutput,
               ra.valueservice, ra.horaap, ra.documento
               FROM `ct_recentlyadd` ra left join `ct_reserva` r on r.id = ra.idrecently
               left join ct_servico s on s.id = ra.idservice left join ct_service_schedule ss
               on ss.idshedule = ra.idschedule where r.id = :id');
$adicionais->execute(array(":id" => $dadosGerais['id'] ) );
$registro = $adicionais->fetchAll(PDO::FETCH_CLASS);
$contador = $adicionais->rowCount();

//Clientes
$cliente = $pdo->prepare('select * from `ct_cliente` order by fullname ');
$cliente->execute();
$todosCliente = $cliente->fetchAll(PDO::FETCH_CLASS);

//lista servico
$servicos = $pdo->prepare('select * from `ct_servico` order by `fullname` ');
$servicos->execute();
$listaServicos = $servicos->fetchAll(PDO::FETCH_CLASS);
//lista pagamento
$pagamentos = $pdo->prepare('select * from `ct_form_of_ payment` order by `namepayment` ');
$pagamentos->execute();
$listaPagamentos = $pagamentos->fetchAll(PDO::FETCH_CLASS);
//lista horarios
$horarios = $pdo->prepare('select * from `ct_service_schedule` ');
$horarios->execute();
$listaHorarios = $horarios->fetchAll(PDO::FETCH_CLASS);
//lista status
$status = $pdo->prepare('select id,fullname as situacao from `ct_status` ');
$status->execute();
$listaStatus = $status->fetchAll(PDO::FETCH_CLASS);

if( isset($_POST['atualizarreserva']) )
{
    $voucher          = $_POST['voucher'];
    $nomePax          = addslashes( strtoupper( trim( $_POST['pax'] ) ) );
    $documento        = addslashes( $_POST['documento'] );
    $quantidadePax    = addslashes( $_POST['quantidadepax'] );
    $quantidadeChild  = addslashes( $_POST['quantidadechild'] );
    $quantidadeFree   = addslashes( $_POST['quantidadefree'] );
    $dataInicio       = addslashes( $_POST['datainicio'] );
    $dataFim          = addslashes( $_POST['datafim'] );
    $novostatus       = $_POST['status'];
    $valor            = $_POST['valueservice'];
    $horaApanha       = $_POST['horariobusca'];
    $service          = addslashes( trim( $_POST['service']  ) );
    $payment          = addslashes( trim( $_POST['payment']  ) );
    $schedule         = addslashes( trim( $_POST['schedule'] ) );

    $dadosReservaAu  = $pdo->prepare("select * from `ct_audit` where `voucher` = :numberVoucher ");
    $dadosReservaAu->execute( array(":numberVoucher" => $voucher ) );
    $registroAu      =  $dadosReservaAu->fetchAll(PDO::FETCH_CLASS);
    $contadorAuditoria = $dadosReservaAu->rowCount();

    $updateReserva1 = $pdo->prepare(
        "UPDATE `ct_reserva` SET `dateinput` = :di, `dateoutput` = :doo, `idhorario` = :hor, `documento` = :doc, `idstatus` = :st, `pax` = :pax,
                   `valueservice` = :valor, `horaap` = :apanha, `idpayment` = :payment, `qtdpax` = :qp, `qtdchild` = :qc,
                    `qtdfree` = :qf, `idservico` = :servico, `idcliente` = :cliente WHERE `ct_reserva`.`numbervoucher` = :nv ");
    $updateReserva1->execute(
        array(
                ":di"      => $dataInicio,
                ":doo"     => $dataFim,
                ":hor"     => $schedule,
                ":st"      => $novostatus,
                ":doc"     => $documento,
                ":pax"     => $nomePax,
                ":valor"   => $valor,
                ":apanha"  => $horaApanha,
                ":payment" => $payment,
                ":qp"      => $quantidadePax,
                ":qc"      => $quantidadeChild,
                ":qf"      => $quantidadeFree,
                ":servico" => $service,
                ":cliente" => $_POST['cliente'],
                ":nv"    => $voucher ));

    if( $novostatus == 2 )
    {
        $atualizarStatus = $pdo->prepare(
            'update `ct_reserva` set `idstatusinvoice` = :sinvoice where numbervoucher = :voucher ');
        $atualizarStatus->execute( array(
            ":sinvoice" => $novostatus,
            ":voucher"  => $voucher

        ) );
    }elseif($novostatus = 4){
        $atualizarStatus = $pdo->prepare(
            'update `ct_reserva` set `idstatusinvoice` = :sinvoice where numbervoucher = :voucher ');
        $atualizarStatus->execute( array(
            ":sinvoice" => 8,
            ":voucher"  => $voucher

        ) );
    }elseif($novostatus = 3){
        $atualizarStatus = $pdo->prepare(
            'update `ct_reserva` set `idstatusinvoice` = :sinvoice where numbervoucher = :voucher ');
        $atualizarStatus->execute( array(
            ":sinvoice" => 4,
            ":voucher"  => $voucher

        ) );
    }

    $dadosAuditoria = $pdo->prepare(
        'insert into `ct_audit` (`id`, `idresponsible`, `voucher`, `description`, `date`) values (DEFAULT, :idres, :vou, :descr, :dat) ');
    $dadosAuditoria->execute(
        array(
            ":idres" =>  $_SESSION['id'],
            ":vou"   => $_POST['voucher'],
            ":descr" => "A reserva do ".$nomePax." foi atualizada por",
            ":dat"   => date("Y-m-d H:i:s")
        )
    );
    $buscaCredito = $pdo->prepare(
        'SELECT cfc.id, valuecredit, `name`, datacredit FROM `ct_createfaturacredit` cfc left join `ct_currentaccount` cc on cfc.idaccountcurrent = cc.id where `numbervoucher` = :voucher');
    $buscaCredito->execute( array(":voucher" => $_POST['voucher'] ) );
    $registroCredito = $buscaCredito->fetchAll(PDO::FETCH_CLASS);
    $contadorCredito = $buscaCredito->rowCount();

    $dadosReserva = $pdo->prepare(
        "SELECT r.id,pax, documento,photoresident , dateinput, dateoutput, photoresident, c.fullname as cliente, s.fullname as `status`, r.valueservice, r.horaap,
            se.fullname as serivco, ag.fullname as agente, namepayment, g.id as guia, qtdpax, qtdchild, qtdfree, ss.schedule,numbervoucher, r.idstatusinvoice, r.abertura
            FROM `ct_reserva` r left join ct_cliente c on c.id = r.idcliente
            left join ct_responsavel re on re.id = r.idresponsavel left join ct_status s on s.id = r.idstatus
            left join ct_guia g on g.id = r.idguia join ct_servico se on se.id = r.idservico
            left join ct_agentes as ag on r.idagente = ag.id
            left join ct_service_schedule ss on ss.idshedule = r.idhorario left join `ct_form_of_ payment` as cfp
            on cfp.id = r.idpayment  where `numbervoucher` = :numbervoucher");
    $dadosReserva->execute( array(":numbervoucher" => $_POST['voucher'] ));
    $dadosGerais = $dadosReserva->fetch(PDO::FETCH_ASSOC);


    $adicionais = $pdo->prepare(
        'SELECT ra.id,ra.dateinput as ap, s.fullname,s.screenplay, ss.schedule, qpax, qchild, qfree, ra.dateinput, ra.dateoutput,
               ra.valueservice, ra.horaap, ra.documento
               FROM `ct_recentlyadd` ra left join `ct_reserva` r on r.id = ra.idrecently
               left join ct_servico s on s.id = ra.idservice left join ct_service_schedule ss
               on ss.idshedule = ra.idschedule where r.id = :id');
    $adicionais->execute(array(":id" => $dadosGerais['id'] ) );
    $registro = $adicionais->fetchAll(PDO::FETCH_CLASS);
    $contador = $adicionais->rowCount();
    $buscaCredito = $pdo->prepare(
        'SELECT * FROM `ct_createfaturacredit` cfc left join `ct_currentaccount` cc on cfc.idaccountcurrent = cc.id where `numbervoucher` = :voucher');
    $buscaCredito->execute( array(":voucher" => $_POST['voucher'] ) );
    $registroCredito = $buscaCredito->fetchAll(PDO::FETCH_CLASS);
    $contadorCredito = $buscaCredito->rowCount();
    echo ("<div class='alert alert-success' role='alert'>Reserva atualizada com sucesso</div>");

}
if( isset($_POST['serviceadd']) )
{
    $idAdd            = $_POST['idAdd'];
    $idRserva         = $_POST['idreserva'];
    $dataInicio       = addslashes( $_POST['datainicio'] );
    $valor2           = $_POST['valueserviceadd'];
    $dataFim          = addslashes( $_POST['datafim'] );
    $service2         = addslashes( $_POST['serviceAdd2'] );
    $embarque         = $_POST['horarioembarque'];
    $documento2       = $_POST['documentoadd'];
    $horaApanha2      = $_POST['horaapadd'];
    $qtdpax           = $_POST['qpax'];
    $qchild           = $_POST['qchild'];
    $qfree            = $_POST['qfree'];

    $updateReserva2 = $pdo->prepare(
            'update `ct_recentlyadd` set `dateinput` = :inicio, `dateoutput` = :fim, `horaap` = :apanha, `documento` = :infoadd,
                       `idservice` = :servico, `valueservice` = :valor, `idschedule` = :embarque, `qpax` = :p, `qchild` = :c, `qfree` = :f where `id` = :id ');
    $updateReserva2->execute(
            array(
                    ":inicio"   => $dataInicio,
                    ":fim"      => $dataFim,
                    ":apanha"   => $horaApanha2,
                    ":infoadd"  => $documento2,
                    ":servico"  => $service2,
                    ":valor"    => $valor2,
                    ":embarque" => $embarque,
                    ":p"        => $qtdpax,
                    ":c"        => $qchild,
                    ":f"        => $qfree,
                    ":id"       => $idAdd
            )
    );

    $dadosAuditoria = $pdo->prepare(
        'insert into `ct_audit` (`id`, `idresponsible`, `voucher`, `description`, `date`) values (DEFAULT, :idres, :vou, :descr, :dat) ');
    $dadosAuditoria->execute(
        array(
            ":idres" =>  $_SESSION['id'],
            ":vou"   => $_POST['voucher'],
            ":descr" => "A reserva adicional Atualizado ",
            ":dat"   => date("Y-m-d H:i:s")
        )
    );

    $dadosReservaAu  = $pdo->prepare("select * from `ct_audit` where `voucher` = :numberVoucher ");
    $dadosReservaAu->execute( array(":numberVoucher" => $_POST['voucher'] ) );
    $registroAu      =  $dadosReservaAu->fetchAll(PDO::FETCH_CLASS);
    $contadorAuditoria = $dadosReservaAu->rowCount();

    $buscaCredito = $pdo->prepare(
        'SELECT cfc.id, valuecredit, `name`, datacredit FROM `ct_createfaturacredit` cfc left join `ct_currentaccount` cc on cfc.idaccountcurrent = cc.id where `numbervoucher` = :voucher');
    $buscaCredito->execute( array(":voucher" => $_POST['voucher'] ) );
    $registroCredito = $buscaCredito->fetchAll(PDO::FETCH_CLASS);
    $contadorCredito = $buscaCredito->rowCount();

    $dadosReserva = $pdo->prepare(
        "SELECT r.id,pax, documento,photoresident, dateinput, dateoutput, photoresident, c.fullname as cliente, s.fullname as `status`, r.valueservice, r.horaap,
            se.fullname as serivco, ag.fullname as agente, namepayment, g.id as guia, qtdpax, qtdchild, qtdfree, ss.schedule,numbervoucher, r.idstatusinvoice, r.abertura
            FROM `ct_reserva` r left join ct_cliente c on c.id = r.idcliente
            left join ct_responsavel re on re.id = r.idresponsavel left join ct_status s on s.id = r.idstatus
            left join ct_guia g on g.id = r.idguia join ct_servico se on se.id = r.idservico
            left join ct_agentes as ag on r.idagente = ag.id
            left join ct_service_schedule ss on ss.idshedule = r.idhorario left join `ct_form_of_ payment` as cfp
            on cfp.id = r.idpayment  where `numbervoucher` = :numbervoucher");
    $dadosReserva->execute( array(":numbervoucher" => $_POST['voucher'] ));
    $dadosGerais = $dadosReserva->fetch(PDO::FETCH_ASSOC);


    $adicionais = $pdo->prepare(
        'SELECT ra.id,ra.dateinput as ap, s.fullname,s.screenplay, ss.schedule, qpax, qchild, qfree, ra.dateinput, ra.dateoutput,
               ra.valueservice, ra.horaap, ra.documento
               FROM `ct_recentlyadd` ra left join `ct_reserva` r on r.id = ra.idrecently
               left join ct_servico s on s.id = ra.idservice left join ct_service_schedule ss
               on ss.idshedule = ra.idschedule where r.id = :id');
    $adicionais->execute(array(":id" => $dadosGerais['id'] ) );
    $registro = $adicionais->fetchAll(PDO::FETCH_CLASS);
    $contador = $adicionais->rowCount();
    echo ("<div class='alert alert-success' role='alert'>Serviço adicional atualizado com sucesso</div>");

}
if( isset($_POST['deleteserviceadd']) )
{
    $idAdd            = $_POST['idAdd'];

    $deleteForever  = $pdo->prepare('delete from `ct_recentlyadd` where id = :id ');
    $deleteForever->execute( array(":id" => $idAdd) );

    $dadosAuditoria = $pdo->prepare(
        'insert into `ct_audit` (`id`, `idresponsible`, `voucher`, `description`, `date`) values (DEFAULT, :idres, :vou, :descr, :dat) ');
    $dadosAuditoria->execute(
        array(
            ":idres" =>  $_SESSION['id'],
            ":vou"   =>  $_POST['voucher'],
            ":descr" => "A reserva adicional foi excluida ",
            ":dat"   => date("Y-m-d H:i:s")
        )
    );
    $buscaCredito = $pdo->prepare(
        'SELECT cfc.id, valuecredit, `name`, datacredit FROM `ct_createfaturacredit` cfc left join `ct_currentaccount` cc on cfc.idaccountcurrent = cc.id where `numbervoucher` = :voucher');
    $buscaCredito->execute( array(":voucher" => $_POST['voucher'] ) );
    $registroCredito = $buscaCredito->fetchAll(PDO::FETCH_CLASS);
    $contadorCredito = $buscaCredito->rowCount();

    $dadosReservaAu  = $pdo->prepare("select * from `ct_audit` where `voucher` = :numberVoucher ");
    $dadosReservaAu->execute( array(":numberVoucher" => $_POST['voucher'] ) );
    $registroAu      =  $dadosReservaAu->fetchAll(PDO::FETCH_CLASS);
    $contadorAuditoria = $dadosReservaAu->rowCount();

    $dadosReserva = $pdo->prepare(
        "SELECT r.id,pax, documento,photoresident , dateinput, dateoutput, photoresident, c.fullname as cliente, s.fullname as `status`, r.valueservice, r.horaap,
            se.fullname as serivco, ag.fullname as agente, namepayment, g.id as guia, qtdpax, qtdchild, qtdfree, ss.schedule,numbervoucher, r.idstatusinvoice, r.abertura
            FROM `ct_reserva` r left join ct_cliente c on c.id = r.idcliente
            left join ct_responsavel re on re.id = r.idresponsavel left join ct_status s on s.id = r.idstatus
            left join ct_guia g on g.id = r.idguia join ct_servico se on se.id = r.idservico
            left join ct_agentes as ag on r.idagente = ag.id
            left join ct_service_schedule ss on ss.idshedule = r.idhorario left join `ct_form_of_ payment` as cfp
            on cfp.id = r.idpayment  where `numbervoucher` = :numbervoucher");
    $dadosReserva->execute( array(":numbervoucher" => $_POST['voucher'] ));
    $dadosGerais = $dadosReserva->fetch(PDO::FETCH_ASSOC);


    $adicionais = $pdo->prepare(
        'SELECT ra.id,ra.dateinput as ap, s.fullname,s.screenplay, ss.schedule, qpax, qchild, qfree, ra.dateinput, ra.dateoutput,
               ra.valueservice, ra.horaap, ra.documento
               FROM `ct_recentlyadd` ra left join `ct_reserva` r on r.id = ra.idrecently
               left join ct_servico s on s.id = ra.idservice left join ct_service_schedule ss
               on ss.idshedule = ra.idschedule where r.id = :id');
    $adicionais->execute(array(":id" => $dadosGerais['id'] ) );
    $registro = $adicionais->fetchAll(PDO::FETCH_CLASS);
    $contador = $adicionais->rowCount();

    echo ("<div class='alert alert-danger' role='alert'>Serviço adicional excluido com sucesso</div>");

}
if( isset( $_POST['updatecredit'] ) )
{
    $idcredit = $_POST['idcredit'];
    $valor    = $_POST['valor'];

    $updateValor = $pdo->prepare('update `ct_createfaturacredit` set `valuecredit` = :newcredit where `id` = :id ');
    $updateValor->execute( array(":newcredit" => $valor, ":id" => $idcredit) );

    $dadosAuditoria = $pdo->prepare(
        'insert into `ct_audit` (`id`, `idresponsible`, `voucher`, `description`, `date`) values (DEFAULT, :idres, :vou, :descr, :dat) ');
    $dadosAuditoria->execute(
        array(
            ":idres" =>  $_SESSION['id'],
            ":vou"   =>  $_POST['voucher'],
            ":descr" => "Valor do crédito atualizado. para R$ ". $valor,
            ":dat"   => date("Y-m-d H:i:s")
        )
    );

    $buscaCredito = $pdo->prepare(
        'SELECT cfc.id, valuecredit, `name`, datacredit FROM `ct_createfaturacredit` cfc 
left join `ct_currentaccount` cc on cfc.idaccountcurrent = cc.id where `numbervoucher` = :voucher');
    $buscaCredito->execute( array(":voucher" => $_POST['voucher'] ) );
    $registroCredito = $buscaCredito->fetchAll(PDO::FETCH_CLASS);
    $contadorCredito = $buscaCredito->rowCount();

    $dadosReservaAu  = $pdo->prepare("select * from `ct_audit` where `voucher` = :numberVoucher ");
    $dadosReservaAu->execute( array(":numberVoucher" => $_POST['voucher'] ) );
    $registroAu      =  $dadosReservaAu->fetchAll(PDO::FETCH_CLASS);
    $contadorAuditoria = $dadosReservaAu->rowCount();

    $dadosReserva = $pdo->prepare(
        "SELECT r.id,pax, documento,photoresident , dateinput, dateoutput, photoresident, c.fullname as cliente, s.fullname as `status`, r.valueservice, r.horaap,
            se.fullname as serivco, ag.fullname as agente, namepayment, g.id as guia, qtdpax, qtdchild, qtdfree, ss.schedule,numbervoucher, r.idstatusinvoice, r.abertura
            FROM `ct_reserva` r left join ct_cliente c on c.id = r.idcliente
            left join ct_responsavel re on re.id = r.idresponsavel left join ct_status s on s.id = r.idstatus
            left join ct_guia g on g.id = r.idguia join ct_servico se on se.id = r.idservico
            left join ct_agentes as ag on r.idagente = ag.id
            left join ct_service_schedule ss on ss.idshedule = r.idhorario left join `ct_form_of_ payment` as cfp
            on cfp.id = r.idpayment  where `numbervoucher` = :numbervoucher");
    $dadosReserva->execute( array(":numbervoucher" => $_POST['voucher'] ));
    $dadosGerais = $dadosReserva->fetch(PDO::FETCH_ASSOC);


    $adicionais = $pdo->prepare(
        'SELECT ra.id,ra.dateinput as ap, s.fullname,s.screenplay, ss.schedule, qpax, qchild, qfree, ra.dateinput, ra.dateoutput,
               ra.valueservice, ra.horaap, ra.documento
               FROM `ct_recentlyadd` ra left join `ct_reserva` r on r.id = ra.idrecently
               left join ct_servico s on s.id = ra.idservice left join ct_service_schedule ss
               on ss.idshedule = ra.idschedule where r.id = :id');
    $adicionais->execute(array(":id" => $dadosGerais['id'] ) );
    $registro = $adicionais->fetchAll(PDO::FETCH_CLASS);
    $contador = $adicionais->rowCount();

    echo ("<div class='alert alert-success' role='alert'>Credito atualizado no valor de R$ ".$valor." para o voucher ".$_POST['voucher']."</div>");


}
if( isset( $_POST['deletecredit'] ) )
{
    $idcredit = $_POST['idcredit'];

    $updateValor = $pdo->prepare('delete from `ct_createfaturacredit` where `id` = :id ');
    $updateValor->execute( array(":id" => $idcredit) );
    $dadosAuditoria = $pdo->prepare(
        'insert into `ct_audit` (`id`, `idresponsible`, `voucher`, `description`, `date`) values (DEFAULT, :idres, :vou, :descr, :dat) ');
    $dadosAuditoria->execute(
        array(
            ":idres" =>  $_SESSION['id'],
            ":vou"   =>  $_POST['voucher'],
            ":descr" => "Crédito removido",
            ":dat"   => date("Y-m-d H:i:s")
        )
    );

    $dadosReservaAu  = $pdo->prepare("select * from `ct_audit` where `voucher` = :numberVoucher ");
    $dadosReservaAu->execute( array(":numberVoucher" => $_POST['voucher'] ) );
    $registroAu      =  $dadosReservaAu->fetchAll(PDO::FETCH_CLASS);
    $contadorAuditoria = $dadosReservaAu->rowCount();

    $buscaCredito = $pdo->prepare(
        'SELECT cfc.id, valuecredit, `name`, datacredit FROM `ct_createfaturacredit` cfc left join `ct_currentaccount` cc on cfc.idaccountcurrent = cc.id where `numbervoucher` = :voucher');
    $buscaCredito->execute( array(":voucher" => $_POST['voucher'] ) );
    $registroCredito = $buscaCredito->fetchAll(PDO::FETCH_CLASS);
    $contadorCredito = $buscaCredito->rowCount();

    $dadosReserva = $pdo->prepare(
        "SELECT r.id,pax, documento,photoresident , dateinput, dateoutput, photoresident, c.fullname as cliente, s.fullname as `status`, r.valueservice, r.horaap,
            se.fullname as serivco, ag.fullname as agente, namepayment, g.id as guia, qtdpax, qtdchild, qtdfree, ss.schedule,numbervoucher, r.idstatusinvoice, r.abertura
            FROM `ct_reserva` r left join ct_cliente c on c.id = r.idcliente
            left join ct_responsavel re on re.id = r.idresponsavel left join ct_status s on s.id = r.idstatus
            left join ct_guia g on g.id = r.idguia join ct_servico se on se.id = r.idservico
            left join ct_agentes as ag on r.idagente = ag.id
            left join ct_service_schedule ss on ss.idshedule = r.idhorario left join `ct_form_of_ payment` as cfp
            on cfp.id = r.idpayment  where `numbervoucher` = :numbervoucher");
    $dadosReserva->execute( array(":numbervoucher" => $_POST['voucher'] ));
    $dadosGerais = $dadosReserva->fetch(PDO::FETCH_ASSOC);


    $adicionais = $pdo->prepare(
        'SELECT ra.id,ra.dateinput as ap, s.fullname,s.screenplay, ss.schedule, qpax, qchild, qfree, ra.dateinput, ra.dateoutput,
               ra.valueservice, ra.horaap, ra.documento
               FROM `ct_recentlyadd` ra left join `ct_reserva` r on r.id = ra.idrecently
               left join ct_servico s on s.id = ra.idservice left join ct_service_schedule ss
               on ss.idshedule = ra.idschedule where r.id = :id');
    $adicionais->execute(array(":id" => $dadosGerais['id'] ) );
    $registro = $adicionais->fetchAll(PDO::FETCH_CLASS);
    $contador = $adicionais->rowCount();

    echo ("<div class='alert alert-danger' role='alert'>Crédito removido para o voucher: ".$_POST['voucher']."</div>");
}
$totalCredito = 0;
foreach ($registro as $item)
{
    $totalAdd = $totalAdd + ( ( $item->valueservice * $item->qpax ) + (  ($item->valueservice / 2) * $item->qchild ) );
}
foreach ($registroCredito as $item)
{
    $totalCredito = $totalCredito + $item->valuecredit;
}


if (isset($_POST['voucherEmail'])) {

    $vouchercliente = $_POST['voucher'];

    $dadosReservaAu  = $pdo->prepare("select * from `ct_audit` where `voucher` = :numberVoucher ");
    $dadosReservaAu->execute( array(":numberVoucher" => $_POST['voucher'] ) );
    $registroAu      =  $dadosReservaAu->fetchAll(PDO::FETCH_CLASS);
    $contadorAuditoria = $dadosReservaAu->rowCount();

    $buscaCredito = $pdo->prepare(
        'SELECT cfc.id, valuecredit, `name`, datacredit FROM `ct_createfaturacredit` cfc left join `ct_currentaccount` cc on cfc.idaccountcurrent = cc.id where `numbervoucher` = :voucher');
    $buscaCredito->execute( array(":voucher" => $_POST['voucher'] ) );
    $registroCredito = $buscaCredito->fetchAll(PDO::FETCH_CLASS);
    $contadorCredito = $buscaCredito->rowCount();

    $dadosReserva = $pdo->prepare(
        "SELECT r.id,pax, documento,photoresident, dateinput, dateoutput, photoresident, c.fullname as cliente, s.fullname as `status`, r.valueservice, r.horaap,
            se.fullname as serivco, ag.fullname as agente, namepayment, g.id as guia, qtdpax, qtdchild, qtdfree, ss.schedule,numbervoucher, r.idstatusinvoice, r.abertura
            FROM `ct_reserva` r left join ct_cliente c on c.id = r.idcliente
            left join ct_responsavel re on re.id = r.idresponsavel left join ct_status s on s.id = r.idstatus
            left join ct_guia g on g.id = r.idguia join ct_servico se on se.id = r.idservico
            left join ct_agentes as ag on r.idagente = ag.id
            left join ct_service_schedule ss on ss.idshedule = r.idhorario left join `ct_form_of_ payment` as cfp
            on cfp.id = r.idpayment  where `numbervoucher` = :numbervoucher");
    $dadosReserva->execute( array(":numbervoucher" => $_POST['voucher'] ));
    $dadosGerais = $dadosReserva->fetch(PDO::FETCH_ASSOC);


    $adicionais = $pdo->prepare(
        'SELECT ra.id,ra.dateinput as ap, s.fullname,s.screenplay, ss.schedule, qpax, qchild, qfree, ra.dateinput, ra.dateoutput,
               ra.valueservice, ra.horaap, ra.documento
               FROM `ct_recentlyadd` ra left join `ct_reserva` r on r.id = ra.idrecently
               left join ct_servico s on s.id = ra.idservice left join ct_service_schedule ss
               on ss.idshedule = ra.idschedule where r.id = :id');
    $adicionais->execute(array(":id" => $dadosGerais['id'] ) );
    $registro = $adicionais->fetchAll(PDO::FETCH_CLASS);
    $contador = $adicionais->rowCount();


    try {

        //Server settings
        $mail->SMTPDebug = 0;
        $mail->isSMTP();
        $mail->Host = 'email-ssl.com.br';
        $mail->SMTPAuth = true;
        $mail->Username = 'reservasonline@grupocassi.com.br';
        $mail->Password = 'A@nderson30116530';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        //Recipients
        $mail->setFrom('reservasonline@grupocassi.com.br', 'Reservas Online - Cassi Turismo');
        $mail->addAddress($_POST['emailcliente'], 'Cliente');     // Add a recipient
        //$mail->addAddress('ellen@example.com');               // Name is optional
        $mail->addReplyTo('cassi@cassiturismo.com.br', 'Information');
        $mail->addCC('cassi@cassiturismo.com.br');
        //$mail->addBCC('bcc@example.com');

        //Attachments
        //$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
        //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

        //Content
        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = 'Meu Voucher - Cassi turismo';
        $mail->Body    = "
                <body leftmargin='0' marginwidth='0' topmargin='0' marginheight='0' offset='0'>
<div id='wrapper' dir='ltr' style=' margin: 0; padding: 70px 0 70px 0; -webkit-text-size-adjust: none !important; width: 100%;'>
    <table border='0' cellpadding='0' cellspacing='0' height='100%' width='100%'><tr>
        <td align='center' valign='top'>
            <div id='template_header_image'>
                <p style='margin-top: 0; background-color: #4b3bfc;'>
                <img src='https://cassiturismo.com.br/wp-content/themes/travel-stories/images/cassi.png' alt='Cassi Turismo' 
                style='border: none; display: inline-block; font-size: 14px; font-weight: bold; height: auto; outline: none; text-decoration: none; 
                text-transform: capitalize; vertical-align: middle; margin-right: 10px;'>
                </p>
            </div>
            <table border='0' cellpadding='0' cellspacing='0' width='600' id='template_container' style='box-shadow: 0 1px 4px rgba(0,0,0,0.1) 
            !important; background-color: #ffffff; border: 1px solid #4335e3; border-radius: 3px !important;'>
                <tr>
                    <td align='center' valign='top'>
                        <!-- Header -->
                        <table border='0' cellpadding='0' cellspacing='0' width='600' id='template_header' 
                        style='background-color: #3f0ed1; border-radius: 3px 3px 0 0 !important; color: #ffffff; border-bottom: 0; 
                        font-weight: bold; line-height: 100%; vertical-align: middle; '>
                            <tr>
                            <td id='header_wrapper' style='padding: 36px 48px; display: block;'>
                                <h1 style='color: #ffffff;  font-size: 30px; 
                                font-weight: 300; line-height: 150%; margin: 0; text-align: left; text-shadow: 0 1px 0 #653eda;'>Meu Voucher</h1>
                            </td>
                        </tr></table>
                        <!-- End Header -->
                    </td>
                </tr>
                <tr>
                    <td align='center' valign='top'>
                        <!-- Body -->
                        <table border='0' cellpadding='0' cellspacing='0' width='600' id='template_body'><tr>
                            <td valign='top' id='body_content' style='background-color: #ffffff;'>
                                <!-- Content -->
                                <table border='0' cellpadding='20' cellspacing='0' width='100%'><tr>
                                    <td valign='top' style='padding: 48px 48px 0;'>
                                        <div id='body_content_inner' style='color: #636363;  font-size: 14px; line-height: 150%; text-align: left;'>
                                         <p style='text-align: justify;'>
                                                Há 15 anos no mercado a nossa empresa vem desenvolvendo o trade turístico no estado da Bahia e temos como nosso maior
                                                mérito a criação do transfer semi-terrestre para Morro de São Paulo. Equipados com uma frota marítima e terrestre de
                                                última geração desempenhamos nossos serviços com altíssimo padrão de qualidade sempre presando pelo conforto e
                                                segurança dos passageiros. Nossas agências são estrategicamente posicionadas para proporcionar o melhor atendimento
                                                possível, oferecendo uma estrutura com alto padrão de qualidade onde o turista pode encontrar, caixa eletrônico,
                                                lanchonete, ar-condicionado, Wifi dentre outros ítens de conforto que só a Cassi Turismo oferece.
                                            </p>
                                            <h2 style='color: #3f0ed1; display: block; 
                                            font-size: 18px; font-weight: bold; line-height: 130%; margin: 0 0 18px; text-align: left;'>
                                                <a href='https://grupocassi.com.br/vouchercliente?voucher=$vouchercliente' >Visualizar Voucher</a>
                                            </h2>
                                        </div>
                                    </td>
                                </tr></table>
                     
                            </td>
                        </tr></table>
     
                    </td>
                </tr>
                <tr>
                    <td align='center' valign='top'>
                        <!-- Footer -->
                        <table border='0' cellpadding='10' cellspacing='0' width='600' id='template_footer'>
                            <tr>
                            <td valign='top' style='padding: 0; -webkit-border-radius: 6px;'>
                                <table border='0' cellpadding='10' cellspacing='0' width='100%'>
                                    <tr>
                                        <td colspan='2' valign='middle' id='credit' style='padding: 0 48px 48px 48px; -webkit-border-radius: 6px; 
                                        border: 0; color: #8c6ee3; font-family: Arial; font-size: 12px; line-height: 125%; text-align: center;'>
                                            <h1>Cassi Turismo 16 Anos</h1>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr></table>
                 
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    </table>
</div>
</body>
        ";
        //$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

        $mail->send();
        echo ("<div class='alert alert-success' role='alert'>E-mail enviado  para o voucher: ".$_POST['voucher']."</div>");
    } catch (Exception $e) {
        echo ("<div class='alert alert-danger' role='alert'>E-mail não enviado  para o voucher: ". $mail->ErrorInfo."</div>");

    }

}

$total = ( ( $dadosGerais['valueservice'] * $dadosGerais['qtdpax'] ) + (  ($dadosGerais['valueservice'] / 2) * $dadosGerais['qtdchild'] ) );
?>
<style>
    .col-md-4, .col-md-3, .col-md-6{
        margin-bottom: 20px;
    }
    .form-group{
        margin-right: 10px;
    }
    h3{
        padding: 30px;

    }
    li{color:black;}
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
                                    <a href="index.php">Home</a>
                                </li>
                                <li class="list-inline-item seprate">
                                    <span>/</span>
                                </li>
                                <li class="list-inline-item">PAX: Editar pax</li>
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
                <h3 align="center"><?php echo("Voucher - ".$dadosGerais['numbervoucher']); ?></h3>
                <small style="font-size: 12px; text-align: center;"><?php echo("Abertura ".date("d-m-Y", strtotime( $dadosGerais['abertura'] )) ); ?></small>
                <div class="card-body">
                        <div class="col-lg-12">
                            <ul class="nav nav-tabs" role="tablist">
                                <li class="nav-item"> <a class="nav-link active" data-toggle="tab" href="#home" role="tab">
                                        <span class="hidden-sm-up"><i class="fa fa-home"></i></span>
                                        <span class="hidden-xs-down"><i class="fa fa-glasses"></i>Informações</span></a>
                                </li>
                                <li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#profile" role="tab">
                                        <span class="hidden-sm-up"><i class="fa fa-plus"></i></span>
                                        <span class="hidden-xs-down">Adicionais</span></a> </li>
                                <li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#messages" role="tab">
                                        <span class="hidden-sm-up"><i class="fa fa-dollar-sign"></i></span>
                                        <span class="hidden-xs-down">Créditos</span></a>
                                </li>
                                <li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#comprovante" role="tab">
                                        <span class="hidden-sm-up"><i class="fa fa-paperclip"></i></span>
                                        <span class="hidden-xs-down">Comprovante</span></a>
                                </li>
                                <li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#auditoria" role="tab">
                                        <span class="hidden-sm-up"><i class="fa fa-file-pdf"></i></span>
                                        <span class="hidden-xs-down">Auditoria</span></a>
                                </li>
                            </ul>
                            <!-- Tab panes -->
                            <div class="tab-content tabcontent-border">
                                <div class="tab-pane active" id="home" role="tabpanel">
                                    <form action="" autocomplete="off" method="post" enctype="multipart/form-data">
                                        <div class="col-md-4 pull-left">
                                            <strong><label for="cliente">Cliente</label></strong>
                                            <select class="form-control" name="cliente" id="cliente">
                                                <?php foreach ($todosCliente as $item){ ?>
                                                    <?php if( $dadosGerais['cliente'] == $item->fullname ){ ?>
                                                        <option selected value="<?php echo($item->id); ?>"><?php echo(utf8_decode($item->fullname)); ?></option>
                                                    <?php }else{?>
                                                        <option value="<?php echo($item->id); ?>"><?php echo(utf8_decode($item->fullname)); ?></option>
                                                    <?php }?>
                                                <?php }?>

                                            </select>
                                        </div>
                                        <div class="col-md-4 pull-left">
                                            <strong><label for="documento">Info. Adicionais sobre o Cliente</label></strong>
                                            <input  type="text" name="documento" id="documento" class="form-control" value="<?php echo( $dadosGerais['documento'] ); ?>">
                                        </div>
                                        <div class="col-md-4 pull-right">
                                            <strong><label for="pax">Pax</label></strong>
                                            <input type="text" name="pax" class="form-control" value="<?php echo( $dadosGerais['pax'] ); ?>">
                                        </div>

                                        <div class="col-md-4 pull-left">
                                            <strong><label for="status">Status</label></strong>
                                            <select name="status" id="status" class="form-control">
                                                <?php foreach ( $listaStatus as $item ){ ?>
                                                    <?php if($dadosGerais['status'] == $item->situacao){ ?>
                                                        <option selected value="<?php echo($item->id); ?>"><?php echo($item->situacao);?></option>
                                                    <?php } else{?>
                                                        <option value="<?php echo($item->id); ?>"><?php echo($item->situacao);?></option>
                                                    <?php }?>
                                                <?php }?>
                                            </select>
                                        </div>
                                        <div class="col-md-4 pull-left">
                                            <strong><label for="quantidadepax">Quantidade de Pax</label></strong>
                                            <input  type="number" name="quantidadepax" class="form-control" value="<?php echo( $dadosGerais['qtdpax'] ); ?>">
                                        </div>

                                        <div class="col-md-4 pull-right">
                                            <strong><label for="quantidadechild">Quantidade Child</label></strong>
                                            <input   type="number" name="quantidadechild" id="quantidadechild" class="form-control" value="<?php echo( $dadosGerais['qtdchild'] ); ?>">
                                        </div>
                                        <div class="col-md-4 pull-left">
                                            <strong><label for="quantidadefree">Quantidade Free</label></strong>
                                            <input  type="number" name="quantidadefree" class="form-control" value="<?php echo( $dadosGerais['qtdfree'] ); ?>">
                                        </div>

                                        <div class="col-md-4 pull-left">
                                            <strong><label for="datainicio">Data de Chegada</label></strong>
                                            <input  type="date" name="datainicio" id="datainicio" class="form-control" value="<?php echo( $dadosGerais['dateinput'] ); ?>">
                                        </div>
                                        <div class="col-md-4 pull-right">
                                            <strong><label for="datafim">Data de Sáida</label></strong>
                                            <input  type="date" name="datafim" id="datafim" class="form-control" value="<?php echo( $dadosGerais['dateoutput'] ); ?>">
                                        </div>
                                        <div class="col-md-4 pull-left">
                                            <strong><label for="service">Serviço contratado</label></strong>
                                            <select class="form-control" name="service">
                                                <?php foreach ($listaServicos as $item3){?>
                                                    <?php if( $dadosGerais['serivco'] == $item3->fullname ){ ?>
                                                        <option value="<?php echo( utf8_encode( $item3->id) ); ?>" selected>
                                                            <?php echo( utf8_decode( $item3->fullname) ); ?>
                                                        </option>
                                                    <?php } else{?>
                                                        <option value="<?php echo( utf8_encode( $item3->id) ); ?>">
                                                            <?php echo( utf8_decode( $item3->fullname) ); ?>
                                                        </option>
                                                    <?php }?>
                                                <?php }?>
                                            </select>
                                        </div>
                                        <div class="col-md-4 pull-left">
                                            <strong><label for="valueservice">Valor do Serviço</label></strong>
                                            <input   type="text" class="form-control" name="valueservice" id="valueservice" value="<?php echo( utf8_encode( $dadosGerais['valueservice'] ) ); ?>" >
                                        </div>
                                        <div class="col-md-4 pull-right ">
                                            <strong><label for="payment">Forma de Pagamento</label></strong>
                                            <select class="form-control" name="payment">
                                                <?php foreach ($listaPagamentos as $formaPagamento){  ?>
                                                    <?php if( $dadosGerais['namepayment'] == $formaPagamento->namepayment ){ ?>
                                                        <option value="<?php echo($formaPagamento->id); ?>" selected>
                                                            <?php echo($formaPagamento->namepayment); ?>
                                                        </option>
                                                    <?php }else{?>
                                                        <option value="<?php echo($formaPagamento->id); ?>">
                                                            <?php echo($formaPagamento->namepayment); ?>
                                                        </option>
                                                    <?php }?>
                                                <?php }?>
                                            </select>
                                        </div>
                                        <div class="col-md-4 pull-left">
                                            <strong><label for="schedule">Horário de Apresentação</label></strong>
                                            <input type="time" class="form-control" name="horariobusca" id="horariobusca" value="<?php echo( $dadosGerais['horaap'] ); ?>">
                                        </div>
                                        <div class="col-md-4 pull-left">
                                            <strong><label for="schedule">Horário de Embarque</label></strong>
                                            <select class="form-control" name="schedule">
                                                <?php foreach ($listaHorarios as $horariosEmbarque){  ?>
                                                    <?php if( $dadosGerais['schedule'] == $horariosEmbarque->schedule ){ ?>
                                                        <option value="<?php echo($horariosEmbarque->idshedule); ?>" selected>
                                                            <?php echo($horariosEmbarque->schedule); ?>
                                                        </option>
                                                    <?php }else{?>
                                                        <option value="<?php echo($horariosEmbarque->idshedule); ?>">
                                                            <?php echo($horariosEmbarque->schedule); ?>
                                                        </option>
                                                    <?php }?>
                                                <?php }?>
                                            </select>
                                        </div>
                                        <input type="hidden" value="<?php echo($dadosGerais['numbervoucher']); ?>" name="voucher">
                                        <div class="">
                                            <div class="col-md-6 pull-right">
                                                <a href="./map-visualizar-servico" class="btn btn-warning btn-lg btn-block">
                                                    Voltar para o mapa
                                                </a>
                                            </div>
                                            <div class="col-md-6 pull-right">
                                                <button type="submit" class="btn btn-primary btn-lg btn-block" name="atualizarreserva">
                                                    Atualizar
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="tab-pane  p-20" id="profile" role="tabpanel">
                                    <?php if( $contador > 0 ){ ?>
                                        <div class="col-lg-12">
                                            <?php foreach ($registro as $item){ ?>
                                                <form action="" method="post">
                                                    <div class="col-md-4 pull-left">
                                                        <strong><label for="serviceAdd autocomplete">Serviço contratado</label></strong>
                                                        <select class="form-control" name="serviceAdd2">
                                                            <?php foreach ($listaServicos as $item3){?>
                                                                <?php if( $item->fullname == $item3->fullname ){ ?>
                                                                    <option value="<?php echo( utf8_encode( $item3->id) ); ?>" selected>
                                                                        <?php echo( utf8_encode( $item3->fullname) ); ?>
                                                                    </option>
                                                                <?php } else{?>
                                                                    <option value="<?php echo( utf8_encode( $item3->id) ); ?>">
                                                                        <?php echo( utf8_encode( $item3->fullname) ); ?>
                                                                    </option>
                                                                <?php }?>
                                                            <?php }?>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4 pull-left">
                                                        <strong><label for="valueserviceadd">Valor do Serviço</label></strong>
                                                        <input  type="text" class="form-control" name="valueserviceadd" id="valueserviceadd" value="<?php echo( utf8_encode( ($item->valueservice ) )); ?>" >
                                                    </div>
                                                    <div class="col-md-4 pull-right autocomplete">
                                                        <strong><label for="documentoadd">Inform. Adicionais</label></strong>
                                                        <input  type="text" class="form-control" name="documentoadd" id="documentoadd" value="<?php echo( utf8_encode( $item->documento ) ); ?>" >
                                                    </div>
                                                    <div class="col-md-4 pull-left autocomplete">
                                                        <strong><label for="qpax">Quantidade Pax</label></strong>
                                                        <input  type="number" class="form-control" name="qpax" id="qpax" value="<?php echo( utf8_encode( $item->qpax ) ); ?>" >
                                                    </div>
                                                    <div class="col-md-4 pull-left autocomplete">
                                                        <strong><label for="qchild">Quantidade Child</label></strong>
                                                        <input  type="number" class="form-control" name="qchild" id="qchild" value="<?php echo( utf8_encode( $item->qchild ) ); ?>" >
                                                    </div>
                                                    <div class="col-md-4 pull-right autocomplete">
                                                        <strong><label for="qfree">Quantidade Free</label></strong>
                                                        <input  type="number" class="form-control" name="qfree" id="qfree" value="<?php echo( utf8_encode( $item->qfree ) ); ?>" >
                                                    </div>
                                                    <div class="col-md-3 pull-left">
                                                        <strong><label for="horaapadd">Horário de Apresentação</label></strong>
                                                        <input  type="time" class="form-control" name="horaapadd" id="horaapadd" value="<?php echo( $item->horaap ); ?>">
                                                    </div>
                                                    <div class="col-md-3 pull-left">
                                                        <strong><label for="horaapadd">Horário de Embarque</label></strong>
                                                        <select class="form-control" name="horarioembarque">
                                                            <?php foreach ($listaHorarios as $horariosEmbarque){  ?>
                                                                <?php if( $item->schedule == $horariosEmbarque->schedule ){ ?>
                                                                    <option value="<?php echo($horariosEmbarque->idshedule); ?>" selected>
                                                                        <?php echo($horariosEmbarque->schedule); ?>
                                                                    </option>
                                                                <?php }else{?>
                                                                    <option value="<?php echo($horariosEmbarque->idshedule); ?>">
                                                                        <?php echo($horariosEmbarque->schedule); ?>
                                                                    </option>
                                                                <?php }?>
                                                            <?php }?>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-3 pull-left">
                                                        <strong><label for="schedule">Data de Chegada </label></strong>
                                                        <input  type="date" class="form-control" name="datainicio" id="datainicio" value="<?php echo( $item->dateinput ); ?>">
                                                    </div>
                                                    <div class="col-md-3 pull-right">
                                                        <strong><label for="schedule">Data de saída</label></strong>
                                                        <input type="date" class="form-control" name="datafim" id="datafim" value="<?php echo( $item->dateoutput ); ?>">
                                                    </div>
                                                    <input type="hidden" value="<?php echo( $item->id); ?>" name="idAdd">
                                                    <div class="col-md-6 pull-left">
                                                        <input type="hidden" name="voucher" value="<?php echo($dadosGerais['numbervoucher']); ?>" >
                                                        <button class="btn btn-success btn-lg btn-block" name="serviceadd" type="submit">Atualizar Serviço Adicional</button>
                                                    </div>
                                                    <div class="col-md-6 pull-right">
                                                        <input type="hidden" name="voucher" value="<?php echo($dadosGerais['numbervoucher']); ?>" >
                                                        <button class="btn btn-danger btn-lg btn-block" name="deleteserviceadd" type="submit">Excluir Serviço Adicional</button>
                                                    </div>
                                                </form>
                                            <?php }?>
                                        </div>
                                    <?php } ?>
                                </div>
                                <div class="tab-pane p-20" id="messages" role="tabpanel">
                                    <?php if( $contadorCredito > 0 ){ ?>
                                        <div class="col-lg-12">
                                            <h4 style="margin-top: 20px;">Créditos  adicionados</h4>
                                            <hr>

                                            <div class="table-responsive">
                                                <form method="post" action="" class="form-inline">
                                                    <table class="table table-bordered">
                                                        <thead>
                                                        <tr>
                                                            <th>Forma de Pagamento</th>
                                                            <th>Data Credito</th>
                                                            <th>Valor</th>
                                                            <th>#</th>
                                                            <th>#</th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        <?php foreach ( $registroCredito as $item ){ ?>

                                                            <tr>
                                                                <td>
                                                                    <?php echo( $item->name );  ?>
                                                                </td>
                                                                <td><?php echo( date("d-m-Y", strtotime($item->datacredit)) ); ?></td>
                                                                <td>
                                                                    <input type="text" class="form-control" value="<?php echo( $item->valuecredit );  ?>"
                                                                           name="valor" style="margin-right: 20px;">
                                                                    <input type="hidden" name="idcredit" value="<?php echo($item->id); ?>" >
                                                                    <input type="hidden" name="voucher" value="<?php echo($dadosGerais['numbervoucher']); ?>" >
                                                                </td>

                                                                <td>
                                                                    <button style="margin-bottom: 15px;" type="submit" name="updatecredit"
                                                                            class="btn btn-success btn-block">Atualizar Valor</button>
                                                                </td>
                                                                <td>
                                                                    <button style="margin-bottom: 15px;" type="submit" name="deletecredit" class="btn btn-danger btn-block">Remover Valor</button>
                                                                </td>
                                                            </tr>
                                                        <?php }?>
                                                        </tbody>
                                                    </table>
                                                </form>
                                            </div>


                                        </div>

                                    <?php }?>
                                </div>
                                <div class="tab-pane p-20" id="auditoria" role="tabpanel">
                                    <?php if( $contadorAuditoria > 0  ){ ?>
                                        <hr>
                                        <h4>Auditoria do  Voucher <?php echo($numberVoucher); ?></h4>
                                        <div class="table-responsivo">
                                            <table class="table table-bordered">
                                                <thead>
                                                <th scope="row">Data</th>
                                                <th scope="row">Descrição</th>
                                                <th scope="row">Responsável</th>
                                                </thead>
                                                <tbody>
                                                <?php foreach ($registroAu as $item) {
                                                    $buscarResponsavel = $pdo->prepare('select * from `ct_usuario` where `id` = :id');
                                                    $buscarResponsavel->execute(array(":id" => $item->idresponsible));
                                                    $dados = $buscarResponsavel->fetch(PDO::FETCH_ASSOC);
                                                    $timestamp = strtotime($item->date);
                                                    ?>
                                                    <tr>
                                                        <td><?php echo( date("d-m-Y às H:i:s", $timestamp) ); ?></td>
                                                        <td><?php echo(  $item->description." (". $dados['firstname']." ".$dados['lastname'].")"); ?></td>
                                                        <td><?php echo( $dados['firstname']." ".$dados['lastname']); ?></td>
                                                    </tr>
                                                <?php }?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php }?>
                                </div>
                                <div class="tab-pane p-20" id="comprovante" role="tabpanel">
                                    <?php if( $dadosGerais['photoresident'] <> '' ){ ?>
                                        <div class="col-lg-12">
                                            <h4 style="margin-top: 20px;">Comprovante de Pagamento</h4>
                                            <hr>
                                            <div class="alert alert-success" role="alert">

                                                <button type="button" style="background-color: transparent; border: none;" data-toggle="modal"
                                                        data-target="#exampleModalCenter">
                                                    Acessar Comprovante
                                                </button>

                                                <!-- Modal -->
                                                <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="exampleModalCenterTitle">Informações sobre o comprovante</h5>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="table-bordered" id="comprovante">
                                                                    <img src="https://grupocassi.com.br/images/msp/<?php echo( $dadosGerais['photoresident']) ;?>">
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-outline-primary" id="abrir"><i class="fa fa-print"></i> Imprimir</button>

                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>

                                    <?php } else{ ?>
                                        <div class="alert alert-warning" role="alert">Não há comprovantes anexados.</div>
                                    <?php }?>
                                </div>
                            </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.getElementById('abrir').onclick = function() {
            var conteudo = document.getElementById('comprovante').innerHTML,
                tela_impressao = window.open('about:blank');

            tela_impressao.document.write(conteudo);
            tela_impressao.window.print();
            tela_impressao.window.close();
        };
    </script>
    <?php require_once ('footer.php'); ?>
