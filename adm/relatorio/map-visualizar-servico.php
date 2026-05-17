<?php require_once ('header.php');
if( isset( $_POST['mapa'] ) )
{
    $dateInput  = $_POST['datainicio'];
    $dateOutput = $_POST['datafim'];
    $idCliente  = $_POST['cliente'];
    $servico    = $_POST['servico'];
    $idhorario  = $_POST['horario'];
    $_SESSION['datainicio'] = $_POST['datainicio'];
    $_SESSION['datafim']    = $_POST['datafim'];
    $_SESSION['cliente']    = $_POST['cliente'];
    $_SESSION['servico']    = $_POST['servico'];
    $_SESSION['horario']    = $_POST['horario'];
}
elseif( isset( $_POST['mapaseguinte'] ) )
{
    $_SESSION['seguinte'] = $_POST['seguinte'];
    $_SESSION['atual'] = $_POST['seguinte'];
    echo("proximo ".$_SESSION['atual']);
    $dateInput  = $_SESSION['atual'];
    $dateOutput = $_SESSION['atual'];
    $idCliente  = $_POST['cliente'];
    $servico    = $_POST['servico'];
    $idhorario  = $_POST['horario'];
    $_SESSION['datainicio'] = $_SESSION['atual'];
    $_SESSION['datafim']    = $_SESSION['atual'];
    $_SESSION['cliente']    = $_POST['cliente'];
    $_SESSION['servico']    = $_POST['servico'];
    $_SESSION['horario']    = $_POST['horario'];
}
elseif( isset( $_POST['mapaanterior'] ) )
{
    $_SESSION['anterior'] = $_POST['anterior'];
    
    $_SESSION['atual'] = date("Y-m-d", strtotime("-1 days",strtotime($_SESSION['atual'])));
    echo("anterior ".$_SESSION['atual']);
    $dateInput  = $_SESSION['atual'];
    $dateOutput = $_SESSION['atual'];
    $idCliente  = $_POST['cliente'];
    $servico    = $_POST['servico'];
    $idhorario  = $_POST['horario'];
    $_SESSION['datainicio'] = $_SESSION['atual'];
    $_SESSION['datafim']    = $_SESSION['atual'];
    $_SESSION['cliente']    = $_POST['cliente'];
    $_SESSION['servico']    = $_POST['servico'];
    $_SESSION['horario']    = $_POST['horario'];
}
else{
    $dateInput  = date("Y-m-d");
    $dateOutput = date("Y-m-d");
    $_SESSION['atual'] = date("Y-m-d");
    $idCliente  = 0;
    $servico    = array("0" => 0);
    $idhorario  = 0;
    $_SESSION['datainicio'] = $_POST['datainicio'];
    $_SESSION['datafim']    = $_POST['datafim'];
    $_SESSION['cliente']    = $_POST['cliente'];
    $_SESSION['servico']    = $_POST['servico'];
    $_SESSION['horario']    = $_POST['horario'];
    $_SESSION['datainicio'] = date("Y-m-d");
    $_SESSION['datafim']    = date("Y-m-d");
}

$todosClientes = $pdo->prepare('SELECT * FROM `ct_cliente`');
$todosClientes->execute();
$todosServicos = $pdo->prepare('select * from `ct_servico` order by ordem,fullname desc');
$todosServicos->execute();
$schedule = $pdo->prepare("select * from `ct_service_schedule` where `schedule` not like '00:00%' order by `schedule`");
$schedule->execute();
$totalPax1   = 0;
$totalPax2   = 0;
$totalChild1 = 0;
$totalchil2  = 0;
$totalfree1 = 0;
$totalfree2  = 0;


?>
<style>
    @media only screen and (max-width: 375px) {
        .containerrrr {
            width: 100%;
            padding-right: 15px;
            padding-left: 15px;
            margin-right: auto;
            margin-left: auto;

        }
    }
    @media only screen and (max-width: 414px) {
        .containerrrr {
            width: 100%;
            padding-right: 15px;
            padding-left: 15px;
            margin-right: auto;
            margin-left: auto;

        }
    }
    @media only screen and (max-width: 411px) {
        .containerrrr {
            width: 100%;
            padding-right: 15px;
            padding-left: 15px;
            margin-right: auto;
            margin-left: auto;

        }
    }
    .col-md-6{margin-bottom: 20px;}
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
                                    <a href="index">Home</a>
                                </li>
                                <li class="list-inline-item seprate">
                                    <span>/</span>
                                </li>
                                <li class="list-inline-item">Mapa de Serviço: Novo Serviço</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- END BREADCRUMB-->
    <div class="">
        <div class="containerrrr">
            <form class="" method="post" action="">
                <div class="col-md-6 pull-left">
                    <label for="datainicio"><strong>Data Inicio:</strong></label>
                    <input required type="date" name="datainicio" id="datainicio" class="form-control" value="<?php echo( $_SESSION['datainicio'] ); ?>">
                </div>
                <div class="col-md-6 pull-right">
                    <label for="datafim"><strong>Data Fim:</strong></label>
                    <input required type="date" name="datafim" id="datafim" class="form-control" value="<?php echo( $_SESSION['datafim'] ); ?>">
                </div>
                <div class="col-md-6 pull-left">
                    <label for="servico"><strong>Cliente</strong></label>
                    <select  class="form-control" name="cliente" id="cliente">
                        <option value="0" selected >Todos</option>
                        <?php while($clientes = $todosClientes->fetch(PDO::FETCH_ASSOC)){ ?>
                            <option value="<?php echo($clientes['id']); ?>">
                                <?php echo( utf8_encode( $clientes['fullname'])); ?>
                            </option>
                        <?php }?>
                    </select>
                </div>
                <div class="col-md-6 pull-right">
                    <label for="servico"><strong>Horário</strong></label>
                    <select  class="form-control" name="horario" id="horario">
                        <option value="0" selected >Todos</option>
                        <?php while($horarios = $schedule->fetch(PDO::FETCH_ASSOC)){ ?>
                            <option value="<?php echo($horarios['idshedule']); ?>">
                                <?php echo( $horarios['schedule']); ?>
                            </option>
                        <?php }?>
                    </select>
                </div>
                <div class="container-fluid" style="margin-bottom: 20px;">
                    <label for="servico" class="pull-left"><strong>Serviço:</strong></label>
                    <select style="height: 200px;" class="form-control" name="servico[]" multiple id="servico">
                        <option selected value="0">Todos Serviços</option>
                        <?php while($servicos = $todosServicos->fetch(PDO::FETCH_ASSOC)){ ?>
                            <option value="<?php echo($servicos['id']); ?>">
                                <?php echo( ( $servicos['fullname'])); ?>
                            </option>
                        <?php }?>
                    </select>
                </div>
                <input type="hidden" value="<?php echo(date("Y-m-d", strtotime("+1 days",strtotime($_SESSION['atual'])))); ?>" name="seguinte">
                <?php if($_SESSION['atual'] > date("Y-m-d")){ ?>
                    <input type="hidden" value="<?php echo(date("Y-m-d", strtotime($_SESSION['atual']))); ?>" name="anterior">
                <?php } else { ?>
                    <input type="hidden" value="<?php echo(date("Y-m-d", strtotime("-1 days",strtotime($_SESSION['atual'])))); ?>" name="anterior">
                <?php }?>

                <div class="col-md-4 pull-left">
                    <button class="btn btn-outline-success btn-block btn-large" type="submit" name="mapaanterior"><strong> Mapa anterior </strong></button>
                </div>
                <div class="col-md-4 pull-left">
                    <button class="btn btn-outline-success btn-block btn-large" type="submit" name="mapa"><strong> Selecionar Mapa </strong></button>
                </div>
                <div class="col-md-4 pull-right">
                    <button class="btn btn-outline-success btn-block btn-large" type="submit" name="mapaseguinte"><strong> Mapa seguinte </strong></button>
                </div>
            </form>
            <div class="card card-outline-primary">
                <div class="card-body">
                    <h3>Mapa de Serviço </h3>
                    <div>
                        <div class="">

                            <div class="table-responsive">
                                <table id="example23" class="table table-striped table-bordered dataTable">
                                    <thead>
                                    <tr>
                                        <th>Data</th>
                                        <th>P | C | F</th>
                                        <th>File | Pax | TEL</th>
                                        <th>Serviço</th>
                                        <th>Apanha</th>
                                        <th>Embarque</th>
                                        <th>Complemento</th>
                                        <th>T serviço</th>
                                        <th>T Reserva</th>
                                        <th>T Pago</th>
                                        <th>A pagar</th>
                                        <th>Status</th>
                                        <th>Operador</th>
                                        <th>Agência</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php for( $i= 0; $i <= count( $servico ); $i++ ){
                                        if( $idCliente == 0 )
                                        {
                                            if( $idhorario > 0 )
                                            {
                                                if($servico[0] == 0 and count($servico) == 1)
                                                {
                                                    $dadosReserva = $pdo->prepare(
                                                        "SELECT r.id,pax, documento, dateinput, dateoutput, c.fullname as cliente,se.fullname as serivco, namepayment, tdpax, qtdchild, qtdfree, ss.schedule,numbervoucher, horaap, valueservice,u.firstname, u.lastname, r.photoresident, r.idservico,
                                                    r.totalservico, r.totalcredito FROM `ct_reserva` r left join ct_cliente c on c.id = r.idcliente join ct_servico se on se.id = r.idservico left join ct_service_schedule ss on ss.idshedule = r.idhorario left join `ct_form_of_ payment` as cfp on cfp.id = r.idpayment left join `ct_usuario` u 
                                                    on u.id = r.idresponsavel where r.`dateinput` >= :inn and r.`dateinput` <= :outt and r.`idstatus` <> 2 and r.idhorario = :idhorario and r.idservico <> 19 and r.idservico <> 30 and r.idservico <> 47 and r.idservico <> 48 order by ss.schedule ");
                                                    $dadosReserva->execute( array(":inn" => $dateInput, ":outt" => $dateOutput, ":idhorario" => $idhorario  ));
                                                    $adicionais = $pdo->prepare(
                                                        'SELECT ra.dateinput as ap, s.fullname, ss.schedule, qpax, qchild, qfree,r.numbervoucher, c.fullname as cliente, r.pax, ra.documento, ra.valueservice, ra.horaap, namepayment,u.firstname, u.lastname, r.photoresident, ra.idservice, r.totalservico, r.totalcredito FROM
                                                                    `ct_recentlyadd` ra left join `ct_reserva` r on r.id = ra.idrecently left join ct_cliente c on c.id = r.idcliente left join `ct_form_of_ payment` as cfp on cfp.id = r.idpayment left join ct_servico s on s.id = ra.idservice left join ct_service_schedule ss on
                                                    ss.idshedule = ra.idschedule left join `ct_usuario` u on u.id = r.idresponsavel where ra.dateinput >= :inn and r.`idstatus` <> 2 and ra.`dateinput` <= :outt  and ra.idschedule = :idhorario and ra.idservice <> 19 and ra.idservice <> 30 and ra.idservice <> 47 and ra.idservice <> 48 order by ss.schedule');
                                                    $adicionais->execute(array(":inn" => $dateInput, ":outt" => $dateOutput, ":idhorario" => $idhorario ) );
                                                }
                                                else{
                                                    $dadosReserva = $pdo->prepare(
                                                        "SELECT r.id,pax, documento, dateinput, dateoutput, c.fullname as cliente, se.fullname as serivco, namepayment, qtdpax, qtdchild, qtdfree, ss.schedule,numbervoucher, horaap, valueservice,u.firstname, u.lastname, r.photoresident, r.idservico, r.totalservico, r.totalcredito 
                                                        FROM `ct_reserva` r left join ct_cliente c on c.id = r.idcliente join ct_servico se on se.id = r.idservico left join `ct_usuario` u on u.id = r.idresponsavel left join ct_service_schedule ss on ss.idshedule = r.idhorario left join `ct_form_of_ payment` as cfp
                                                        on cfp.id = r.idpayment  where r.`dateinput` >= :inn and r.`dateinput` <= :outt and r.`idstatus` <> 2
                                                        and r.idservico = :servico and r.idhorario = :idhorario and r.idservico <> 19 and r.idservico <> 30 and r.idservico <> 47 and r.idservico <> 48 order by ss.schedule ");
                                                    $dadosReserva->execute( array(":inn" => $dateInput,
                                                        ":servico" => $servico[$i], ":outt" => $dateOutput, ":idhorario" => $idhorario  ));


                                                    $adicionais = $pdo->prepare(
                                                        'SELECT ra.dateinput as ap, s.fullname, ss.schedule, qpax, qchild, qfree,u.firstname, u.lastname, r.numbervoucher, c.fullname as cliente, r.pax, ra.documento, ra.valueservice, ra.horaap, namepayment, r.photoresident, ra.idservice, r.totalservico, r.totalcredito
                                                       FROM `ct_recentlyadd` ra left join `ct_reserva` r on r.id = ra.idrecently left join ct_cliente c on  c.id = r.idcliente left join `ct_form_of_ payment` as cfp on cfp.id = r.idpayment left join `ct_usuario` u on u.id = r.idresponsavel
                                                       left join ct_servico s on s.id = ra.idservice left join ct_service_schedule ss on ss.idshedule = ra.idschedule  where ra.dateinput >= :inn  and ra.idservice = :servico and r.`idstatus` <> 2
                                                       and ra.`dateinput` <= :outt and ra.idschedule = :idhorario and ra.idservice <> 19 and ra.idservice <> 30 and ra.idservice <> 47 and ra.idservice <> 48 order by ss.schedule');
                                                    $adicionais->execute(array(":inn" => $dateInput,
                                                        ":servico" => $servico[$i], ":outt" => $dateOutput, ":idhorario" => $idhorario ) );
                                                }
                                                $dadosGerais = $dadosReserva->fetchAll(PDO::FETCH_CLASS);
                                                $contador2   = $dadosReserva->rowCount();
                                                $registro    = $adicionais->fetchAll(PDO::FETCH_CLASS);
                                                $contador    = $adicionais->rowCount();
                                            }else
                                            {
                                                if($servico[0] == 0 and count($servico) == 1)
                                                {
                                                    $dadosReserva = $pdo->prepare(
                                                        "SELECT r.id,pax, documento, dateinput, dateoutput, c.fullname as cliente, se.fullname as serivco, namepayment, r.totalservico, r.totalcredito,qtdpax, qtdchild, qtdfree, ss.schedule, numbervoucher, horaap, valueservice,u.firstname, u.lastname, r.photoresident, r.idservico
                                                                   FROM `ct_reserva` r left join ct_cliente c on  c.id = r.idcliente join ct_servico se on se.id = r.idservico left join ct_service_schedule ss on ss.idshedule = r.idhorario left join `ct_form_of_ payment` as cfp on cfp.id = r.idpayment left join `ct_usuario` u on u.id = r.idresponsavel
                                                                  where r.`dateinput` >= :inn and r.`dateinput` <= :outt and r.`idstatus` <> 2 and r.idservico <> 19 and r.idservico <> 30 and r.idservico <> 47 and r.idservico <> 48 order by ss.schedule ");
                                                    $dadosReserva->execute( array(":inn" => $dateInput,":outt" => $dateOutput ));

                                                    $adicionais = $pdo->prepare(
                                                        'SELECT ra.dateinput as ap, s.fullname, ss.schedule, qpax, qchild, qfree, r.numbervoucher, c.fullname as cliente, r.pax, ra.documento, ra.valueservice, ra.horaap, namepayment,u.firstname, u.lastname, r.photoresident, ra.idservice, r.totalservico, r.totalcredito FROM
                                                        `ct_recentlyadd` ra left join `ct_reserva` r on r.id = ra.idrecently left join ct_cliente c on c.id = r.idcliente left join `ct_form_of_ payment` as cfp on cfp.id = r.idpayment left join ct_servico s on s.id = ra.idservice left join ct_service_schedule ss on ss.idshedule = ra.idschedule 
                                                         left join `ct_usuario` u on u.id = r.idresponsavel where ra.dateinput >= :inn  and r.`idstatus` <> 2 and ra.`dateinput` <= :outt and ra.idservice <> 19 and ra.idservice <> 30 and ra.idservice <> 47 and ra.idservice <> 48 order by ss.schedule');
                                                    $adicionais->execute(array(":inn" => $dateInput,":outt" => $dateOutput) );
                                                }
                                                else{
                                                    $dadosReserva = $pdo->prepare(
                                                        "SELECT r.id,pax, documento, dateinput, dateoutput, c.fullname as cliente, se.fullname as serivco, namepayment, r.totalservico, r.totalcredito,
                                                qtdpax, qtdchild, qtdfree, ss.schedule, numbervoucher, horaap, valueservice,u.firstname, u.lastname, r.photoresident, r.idservico FROM `ct_reserva` r left join ct_cliente c on 
                                                c.id = r.idcliente join ct_servico se on se.id = r.idservico left join ct_service_schedule ss on ss.idshedule = r.idhorario
                                                left join `ct_form_of_ payment` as cfp on cfp.id = r.idpayment left join `ct_usuario` u on u.id = r.idresponsavel  where r.`dateinput` >= :inn and r.`dateinput` <= :outt 
                                                and r.`idstatus` <> 2 and r.idservico = :servico and r.idservico <> 19 and r.idservico <> 30 and r.idservico <> 47 and r.idservico <> 48  order by ss.schedule ");
                                                    $dadosReserva->execute( array(":inn" => $dateInput,
                                                        ":servico" => $servico[$i], ":outt" => $dateOutput ));


                                                    $adicionais = $pdo->prepare(
                                                        'SELECT ra.dateinput as ap, s.fullname, ss.schedule, qpax, qchild, qfree, r.numbervoucher, c.fullname as cliente, r.pax, ra.documento, ra.valueservice, ra.horaap, namepayment,u.firstname, u.lastname, r.photoresident, ra.idservice, r.totalservico, r.totalcredito FROM 
                                                                     `ct_recentlyadd` ra left join `ct_reserva` r on  r.id = ra.idrecently left join ct_cliente c on c.id = r.idcliente left join `ct_form_of_ payment` as cfp on cfp.id = r.idpayment
                                                left join ct_servico s on s.id = ra.idservice left join ct_service_schedule ss on ss.idshedule = ra.idschedule left join `ct_usuario` u on u.id = r.idresponsavel
                                                where ra.dateinput >= :inn  and ra.idservice = :servico and r.`idstatus` <> 2 and ra.`dateinput` <= :outt and ra.idservice <> 19 and ra.idservice <> 30 and ra.idservice <> 47 and ra.idservice <> 48 order by ss.schedule');
                                                    $adicionais->execute(array(":inn" => $dateInput,
                                                        ":servico" => $servico[$i], ":outt" => $dateOutput) );
                                                }
                                                $dadosGerais = $dadosReserva->fetchAll(PDO::FETCH_CLASS);
                                                $contador2   = $dadosReserva->rowCount();
                                                $registro    = $adicionais->fetchAll(PDO::FETCH_CLASS);
                                                $contador    = $adicionais->rowCount();
                                            }
                                        }
                                        else{
                                            if( $idhorario > 0)
                                            {
                                                if($servico[0] == 0 and count($servico) == 1)
                                                {
                                                    $dadosReserva = $pdo->prepare(
                                                        "SELECT r.id,pax, documento, dateinput, dateoutput, c.fullname as cliente,se.fullname as serivco, namepayment,r.totalservico, r.totalcredito,
                                                qtdpax, qtdchild, qtdfree, ss.schedule,numbervoucher, horaap, valueservice,u.firstname, u.lastname, r.photoresident, r.idservico FROM `ct_reserva` r left join ct_cliente c 
                                                on c.id = r.idcliente join ct_servico se on se.id = r.idservico left join ct_service_schedule ss on ss.idshedule = r.idhorario
                                                left join `ct_form_of_ payment` as cfp on cfp.id = r.idpayment left join `ct_usuario` u on u.id = r.idresponsavel  where r.`dateinput` >= :inn and r.`idstatus` <> 2 
                                                and r.`dateinput` <= :outt and r.idservico = :servico and r.idcliente = :cliente and r.idhorario = :idhorario and r.idservico <> 19 and r.idservico <> 30 and r.idservico <> 47 and r.idservico <> 48 ");
                                                    $dadosReserva->execute( array(":inn" => $dateInput, ":servico" => $servico[$i],
                                                        ":cliente" => $idCliente, ":outt" => $dateOutput, ":idhorario" => $idhorario ));


                                                    $adicionais = $pdo->prepare(
                                                        'SELECT ra.dateinput as ap, s.fullname, ss.schedule, qpax, qchild, qfree, r.numbervoucher, c.fullname as cliente,r.totalservico, r.totalcredito,
                                                r.pax, ra.documento, ra.valueservice, ra.horaap, namepayment, r.photoresident, ra.idservice, r.totalservico, r.totalcredito FROM `ct_recentlyadd` ra left join `ct_reserva` r on 
                                                r.id = ra.idrecently left join ct_cliente c on c.id = r.idcliente left join `ct_form_of_ payment` as cfp on cfp.id = r.idpayment
                                                left join ct_servico s on s.id = ra.idservice left join ct_service_schedule ss on ss.idshedule = ra.idschedule 
                                                where ra.dateinput >= :inn and r.`idstatus` <> 2  and ra.idservice = :servico and ra.`dateinput` <= :outt and r.idcliente = 
                                                :cliente and ra.idschedule = :idhorario and ra.idservice <> 19 and ra.idservice <> 30 and ra.idservice <> 47 and ra.idservice <> 48 order by ss.schedule');
                                                    $adicionais->execute(array(":inn" => $dateInput, ":servico" => $servico[$i],
                                                        ":cliente" => $idCliente, ":outt" => $dateOutput, ":idhorario" => $idhorario ) );
                                                }
                                                else{
                                                    $dadosReserva = $pdo->prepare(
                                                        "SELECT r.id,pax, documento, dateinput, dateoutput, c.fullname as cliente,se.fullname as serivco, namepayment,r.totalservico, r.totalcredito,
                                                qtdpax, qtdchild, qtdfree, ss.schedule,numbervoucher, horaap, valueservice,u.firstname, u.lastname, r.photoresident, r.idservico FROM `ct_reserva` r left join ct_cliente c 
                                                on c.id = r.idcliente join ct_servico se on se.id = r.idservico left join ct_service_schedule ss on ss.idshedule = r.idhorario
                                                left join `ct_form_of_ payment` as cfp on cfp.id = r.idpayment left join `ct_usuario` u on u.id = r.idresponsavel  where r.`dateinput` >= :inn and r.`idstatus` <> 2 
                                                and r.`dateinput` <= :outt and r.idservico = :servico and r.idcliente = :cliente and r.idhorario = :idhorario and r.idservico <> 19 and r.idservico <> 30 and r.idservico <> 47 and r.idservico <> 48 ");
                                                    $dadosReserva->execute( array(":inn" => $dateInput, ":servico" => $servico[$i],
                                                        ":cliente" => $idCliente, ":outt" => $dateOutput, ":idhorario" => $idhorario ));


                                                    $adicionais = $pdo->prepare(
                                                        'SELECT ra.dateinput as ap, s.fullname, ss.schedule, qpax, qchild, qfree, r.numbervoucher, c.fullname as cliente,
                                                r.pax, ra.documento, ra.valueservice, ra.horaap, namepayment, r.photoresident, ra.idservice, r.totalservico, r.totalcredito FROM `ct_recentlyadd` ra left join `ct_reserva` r on 
                                                r.id = ra.idrecently left join ct_cliente c on c.id = r.idcliente left join `ct_form_of_ payment` as cfp on cfp.id = r.idpayment
                                                left join ct_servico s on s.id = ra.idservice left join ct_service_schedule ss on ss.idshedule = ra.idschedule 
                                                where ra.dateinput >= :inn and r.`idstatus` <> 2  and ra.idservice = :servico and ra.`dateinput` <= :outt and r.idcliente = 
                                                :cliente and ra.idschedule = :idhorario and ra.idservice <> 19 and ra.idservice <> 30 and ra.idservice <> 47 and ra.idservice <> 48 order by ss.schedule');
                                                    $adicionais->execute(array(":inn" => $dateInput, ":servico" => $servico[$i],
                                                        ":cliente" => $idCliente, ":outt" => $dateOutput, ":idhorario" => $idhorario ) );
                                                }
                                                $dadosGerais = $dadosReserva->fetchAll(PDO::FETCH_CLASS);
                                                $contador2   = $dadosReserva->rowCount();
                                                $registro = $adicionais->fetchAll(PDO::FETCH_CLASS);
                                                $contador = $adicionais->rowCount();
                                            }else
                                            {
                                                if($servico[0] == 0 and count($servico) == 1)
                                                {
                                                    $dadosReserva = $pdo->prepare(
                                                        "SELECT r.id,pax, documento, dateinput, dateoutput, c.fullname as cliente,se.fullname as serivco, namepayment,r.totalservico, r.totalcredito,
                                                qtdpax, qtdchild, qtdfree, ss.schedule, numbervoucher, horaap, valueservice,u.firstname, u.lastname, r.photoresident, r.idservico FROM `ct_reserva` r left join ct_cliente c on
                                                c.id = r.idcliente  join ct_servico se on se.id = r.idservico left join ct_service_schedule ss on ss.idshedule = r.idhorario
                                                left join `ct_form_of_ payment` as cfp on cfp.id = r.idpayment left join `ct_usuario` u on u.id = r.idresponsavel where r.`dateinput` >= :inn and r.`idstatus` <> 2 
                                                and r.`dateinput` <= :outt  and r.idcliente = :cliente and r.idservico <> 19 and r.idservico <> 30 and r.idservico <> 47 and r.idservico <> 48  order by ss.schedule ");
                                                    $dadosReserva->execute( array(":inn" => $dateInput, ":cliente" => $idCliente, ":outt" => $dateOutput ));

                                                    $adicionais = $pdo->prepare(
                                                        'SELECT ra.dateinput as ap, s.fullname, ss.schedule, qpax, qchild, qfree,r.numbervoucher, c.fullname as cliente,
                                                r.pax, ra.documento, ra.valueservice, ra.horaap, namepayment,u.firstname, u.lastname, r.photoresident, ra.idservice, r.totalservico, r.totalcredito FROM `ct_recentlyadd` ra left join `ct_reserva` r 
                                                on r.id = ra.idrecently left join ct_cliente c on c.id = r.idcliente left join `ct_form_of_ payment` as cfp on cfp.id = 
                                                r.idpayment left join ct_servico s on s.id = ra.idservice left join ct_service_schedule ss on ss.idshedule = ra.idschedule
                                                left join `ct_usuario` u on u.id = r.idresponsavel
                                                where ra.dateinput >= :inn and r.`idstatus` <> 2  and ra.`dateinput` <= :outt and r.idcliente = :cliente and ra.idservice <> 19 and ra.idservice <> 30 and ra.idservice <> 47 and ra.idservice <> 48 order by ss.schedule');
                                                    $adicionais->execute(array(":inn" => $dateInput, ":cliente" => $idCliente, ":outt" => $dateOutput ) );
                                                }
                                                else{
                                                    $dadosReserva = $pdo->prepare(
                                                        "SELECT r.id,pax, documento, dateinput, dateoutput, c.fullname as cliente,se.fullname as serivco, namepayment,r.totalservico, r.totalcredito,
                                                qtdpax, qtdchild, qtdfree, ss.schedule, numbervoucher, horaap, valueservice,u.firstname, u.lastname, r.photoresident, r.idservico FROM `ct_reserva` r left join ct_cliente c on
                                                c.id = r.idcliente  join ct_servico se on se.id = r.idservico left join ct_service_schedule ss on ss.idshedule = r.idhorario
                                                left join `ct_form_of_ payment` as cfp on cfp.id = r.idpayment left join `ct_usuario` u on u.id = r.idresponsavel  where r.`dateinput` >= :inn and r.`idstatus` <> 2 
                                                and r.`dateinput` <= :outt and r.idservico = :servico and r.idcliente = :cliente and r.idservico <> 19 and r.idservico <> 30 and r.idservico <> 47 and r.idservico <> 48 order by ss.schedule ");
                                                    $dadosReserva->execute( array(":inn" => $dateInput, ":servico" => $servico[$i],
                                                        ":cliente" => $idCliente, ":outt" => $dateOutput ));

                                                    $adicionais = $pdo->prepare(
                                                        'SELECT ra.dateinput as ap, s.fullname, ss.schedule, qpax, qchild, qfree,r.numbervoucher, c.fullname as cliente,
                                                r.pax, ra.documento, ra.valueservice, ra.horaap, namepayment,u.firstname, u.lastname, r.photoresident, ra.idservice, r.totalservico, r.totalcredito FROM `ct_recentlyadd` ra left join `ct_reserva` r 
                                                on r.id = ra.idrecently left join ct_cliente c on c.id = r.idcliente left join `ct_form_of_ payment` as cfp on cfp.id = 
                                                r.idpayment left join ct_servico s on s.id = ra.idservice left join ct_service_schedule ss on ss.idshedule = ra.idschedule  
                                                left join `ct_usuario` u on u.id = r.idresponsavel where ra.dateinput >= :inn and r.`idstatus` <> 2  and ra.idservice = :servico and ra.`dateinput` <= :outt and r.idcliente = :cliente and ra.idservice <> 19 and ra.idservice <> 30 and ra.idservice <> 47 and ra.idservice <> 48 order by ss.schedule');
                                                    $adicionais->execute(array(":inn" => $dateInput, ":servico" => $servico[$i],
                                                        ":cliente" => $idCliente, ":outt" => $dateOutput ) );
                                                }
                                                $dadosGerais = $dadosReserva->fetchAll(PDO::FETCH_CLASS);
                                                $contador2   = $dadosReserva->rowCount();
                                                $registro = $adicionais->fetchAll(PDO::FETCH_CLASS);
                                                $contador = $adicionais->rowCount();
                                            }
                                        }
                                        ?>
                                        <?php foreach ($dadosGerais as $item) {$totalPax1 += $item->qtdpax; $totalChild1 += $item->qtdchild; $totalfree1 += $item->qtdfree; ?>
                                            <?php if( $item->idservico >= 109 and  $item->idservico <= 120 or $item->idservico == 111 or $item->idservico == 42 or $item->idservico == 108 or $item->idservico == 120 or $item->idservico == 124 or $item->idservico == 133 or $item->idservico == 156 or $item->idservico == 157
                                                or $item->idservico == 224 or $item->idservico == 239 or $item->idservico == 139 or $item->idservico == 130 or $item->idservico == 131 or $item->idservico == 137 or $item->idservico == 104 or $item->idservico == 107 or $item->idservico == 148 or $item->idservico == 233
                                                or $item->idservico == 231 or $item->idservico == 249 or $item->idservico == 65 or $item->idservico == 21  or $item->idservico == 39 or $item->idservico == 54 or $item->idservico == 56 or $item->idservico == 158 or $item->idservico == 223 or $item->idservico == 40
                                                or $item->idservico >= 59  and  $item->idservico <= 64 or $item->idservico >= 146 or $item2->idservice >= 207 or $item2->idservice >= 33 or $item->idservico == 233 or $item->idservico == 243 or $item->idservico == 242 or $item2->idservice >= 34 or $item2->idservice >= 35 and $item->idservico <= 147){ ?>
                                                <tr class="bg-primary" style="color: white;">
                                                    <td><?php echo( date('d-m-Y', strtotime($item->dateinput)) ); ?></td>
                                                    <td style="width: 100px;"><?php echo($item->qtdpax."|".$item->qtdchild."|".$item->qtdfree); ?></td>
                                                    <td>
                                                        <form method="post" action="./editar-pax" target="_blank">
                                                            <input type="hidden" name="numbervoucher" value="<?php echo($item->numbervoucher); ?>"/>
                                                            <button type="submit" style="background-color: transparent; border:  none;">
                                                                <?php echo($item->numbervoucher." | ".$item->pax." | ".$item->photoresident); ?>
                                                            </button>
                                                        </form>
                                                    </td>
                                                    <td><?php echo( utf8_encode( $item->serivco) ); ?></td>
                                                    <td><?php echo( date("H:i", strtotime($item->horaap)) ); ?></td>
                                                    <td><?php echo( date("H:i", strtotime($item->schedule)) ); ?></td>
                                                    <td><?php echo(utf8_encode($item->documento)); ?></td>
                                                    <td><?php echo("R$ ".number_format(( ( $item->valueservice * $item->qtdpax) +
                                                                (  ($item->valueservice / 2 ) * $item->qtdchild ) ),2,",",".") ); ?></td>

                                                    <td><?php echo("R$ ".number_format($item->totalservico,2,",",".") ); ?></td>
                                                    <td><?php echo("R$ ".number_format($item->totalcredito,2,",",".") ); ?></td>

                                                    <?php if($item->totalservico-$item->totalcredito > 0 ){ ?>
                                                        <td class="bg-danger" style="color: white;"><?php echo("R$ ".number_format($item->totalservico-$item->totalcredito,2,",",".") ); ?></td>
                                                    <?php } else {?>
                                                        <td><?php echo("R$ ".number_format(($item->totalservico-$item->totalcredito) * -1,2,",",".") ); ?></td>
                                                    <?php }?>
                                                    <?php if($item->cliente == "BUZIOS TRANSFER" or $item->cliente == "POUSADA GRAUCA" or $item->cliente == "Hotel Morro da Saudade" ){ ?>
                                                        <td>Não Cobrar</td>
                                                    <?php } else {?>
                                                        <td>Cobrar</td>
                                                    <?php }?>
                                                    <td><?php echo( strtoupper(utf8_encode( $item->firstname." ".$item->lastname) )); ?></td>
                                                    <td><?php echo( strtoupper(utf8_encode( $item->cliente) )); ?></td>
                                                </tr>
                                            <?php } elseif($item->idservico == 143  or $item->idservico == 2 or $item->idservico == 240 or $item->idservico == 241 or $item->idservico == 193 or $item->idservico == 133 or $item->idservico == 137
                                                or $item->idservico == 168 or $item->idservico == 169 or $item->idservico == 228 or $item->idservico == 10 or $item->idservico == 9 or $item->idservico == 162 or $item->idservico == 167
                                                or $item->idservico == 170 or $item->idservico == 216 or $item->idservico == 232 or $item->idservico == 249){ ?>
                                                <tr class="bg-success">
                                                    <td><?php echo( date('d-m-Y', strtotime($item->dateinput)) ); ?></td>
                                                    <td style="width: 100px;"><?php echo($item->qtdpax."|".$item->qtdchild."|".$item->qtdfree); ?></td>
                                                    <td>
                                                        <form method="post" action="./editar-pax" target="_blank">
                                                            <input type="hidden" name="numbervoucher" value="<?php echo($item->numbervoucher); ?>"/>
                                                            <button type="submit" style="background-color: transparent; border:  none;">
                                                                <?php echo($item->numbervoucher." | ".$item->pax." | ".$item->photoresident); ?>
                                                            </button>
                                                        </form>
                                                    </td>
                                                    <td><?php echo( utf8_encode( $item->serivco) ); ?></td>
                                                    <td><?php echo( date("H:i", strtotime($item->horaap)) ); ?></td>
                                                    <td><?php echo( date("H:i", strtotime($item->schedule)) ); ?></td>
                                                    <td><?php echo(utf8_encode($item->documento)); ?></td>
                                                    <td><?php echo("R$ ".number_format(( ( $item->valueservice * $item->qtdpax) + (  ($item->valueservice / 2 ) * $item->qtdchild ) ),2,",",".") ); ?></td>
                                                    <td><?php echo("R$ ".number_format($item->totalservico,2,",",".") ); ?></td>
                                                    <td><?php echo("R$ ".number_format($item->totalcredito,2,",",".") ); ?></td>
                                                    <?php if($item->totalservico-$item->totalcredito > 0){ ?>
                                                        <td class="bg-danger" style="color: white;"><?php echo("R$ ".number_format($item->totalservico-$item->totalcredito,2,",",".") ); ?></td>
                                                    <?php } else {?>
                                                        <td><?php echo("R$ ".number_format(($item->totalservico-$item->totalcredito) * -1,2,",",".") ); ?></td>
                                                    <?php }?>
                                                    <?php if($item->cliente == "BUZIOS TRANSFER" or $item->cliente == "POUSADA GRAUCA" or $item->cliente == "Hotel Morro da Saudade" ){ ?>
                                                        <td>Não Cobrar</td>
                                                    <?php } else {?>
                                                        <td>Cobrar</td>
                                                    <?php }?>
                                                    <td><?php echo( strtoupper(utf8_encode( $item->firstname." ".$item->lastname) )); ?></td>
                                                    <td><?php echo( strtoupper(utf8_encode( $item->cliente) )); ?></td>
                                                </tr>
                                            <?php } else { ?>
                                                <tr>
                                                    <td><?php echo( date('d-m-Y', strtotime($item->dateinput)) ); ?></td>
                                                    <td style="width: 100px;"><?php echo($item->qtdpax."|".$item->qtdchild."|".$item->qtdfree); ?></td>
                                                    <td>
                                                        <form method="post" action="./editar-pax" target="_blank">
                                                            <input type="hidden" name="numbervoucher" value="<?php echo($item->numbervoucher); ?>"/>
                                                            <button type="submit" style="background-color: transparent; border:  none;">
                                                                <?php echo($item->numbervoucher." | ".$item->pax." | ".$item->photoresident); ?>
                                                            </button>
                                                        </form>
                                                    </td>
                                                    <td><?php echo( utf8_encode( $item->serivco) ); ?></td>
                                                    <td><?php echo( date("H:i", strtotime($item->horaap)) ); ?></td>
                                                    <td><?php echo( date("H:i", strtotime($item->schedule)) ); ?></td>
                                                    <td><?php echo(utf8_encode($item->documento)); ?></td>
                                                    <td><?php echo("R$ ".number_format(( ( $item->valueservice * $item->qtdpax) + (  ($item->valueservice / 2 ) * $item->qtdchild ) ),2,",",".") ); ?></td>
                                                    <td><?php echo("R$ ".number_format($item->totalservico,2,",",".") ); ?></td>
                                                    <td><?php echo("R$ ".number_format($item->totalcredito,2,",",".") ); ?></td>
                                                    <?php if($item->totalservico-$item->totalcredito > 0){ ?>
                                                        <td class="bg-danger" style="color: white;"><?php echo("R$ ".number_format($item->totalservico-$item->totalcredito,2,",",".") ); ?></td>
                                                    <?php } else {?>
                                                        <td><?php echo("R$ ".number_format(($item->totalservico-$item->totalcredito) * -1,2,",",".") ); ?></td>
                                                    <?php }?>
                                                    <?php if($item->cliente == "BUZIOS TRANSFER" or $item->cliente == "POUSADA GRAUCA" or $item->cliente == "Hotel Morro da Saudade" ){ ?>
                                                        <td>Não Cobrar</td>
                                                    <?php } else {?>
                                                        <td>Cobrar</td>
                                                    <?php }?>
                                                    <td><?php echo( strtoupper(utf8_encode( $item->firstname." ".$item->lastname) )); ?></td>
                                                    <td><?php echo( strtoupper(utf8_encode( $item->cliente) )); ?></td>
                                                </tr>
                                            <?php }?>

                                        <?php } ?>
                                        <?php if( $contador > 0 ){ ?>
                                            <?php foreach ($registro as $item2) {$totalPax2 += $item2->qpax; $totalchil2 += $item2->qchild; $totalfree2 += $item2->qfree; ?>
                                                <?php if( $item2->idservice >= 109 and  $item2->idservice <= 120 or $item2->idservice == 111 or $item2->idservice == 42 or $item2->idservice == 108 or $item2->idservice == 120 or $item2->idservice == 124 or $item2->idservice == 133 or $item2->idservice == 156 or $item2->idservice == 157
                                                    or $item2->idservice == 224  or $item2->idservice == 239 or $item2->idservice == 139 or $item2->idservice == 130 or $item2->idservice == 131 or $item2->idservice == 137 or $item2->idservice == 104 or $item2->idservice == 107 or $item2->idservice == 148 or $item2->idservice == 40
                                                    or $item2->idservice == 231  or $item2->idservice == 65 or $item2->idservice == 249 or $item2->idservice == 228 or $item2->idservice == 21  or $item2->idservice == 39 or $item2->idservice == 54 or $item2->idservice == 56 or $item2->idservice == 158 or $item2->idservice == 223
                                                    or $item2->idservice >= 59  and  $item2->idservice <= 64 or $item2->idservice >= 146 or $item2->idservice >= 207 or $item2->idservice >= 33 or $item2->idservice >= 242 or $item2->idservice >= 243 or $item2->idservice >= 233 or $item2->idservice >= 34 or $item2->idservice >= 35 and  $item2->idservice <= 147){ ?>
                                                    <tr class="bg-primary" style="color: white;">
                                                        <td><?php echo( date('d-m-Y',  strtotime($item2->ap)) ); ?></td>
                                                        <td style="width: 100px;"><?php echo($item2->qpax."|".$item2->qchild."|".$item2->qfree); ?></td>
                                                        <td>
                                                            <form method="post" action="./editar-pax" target="_blank">
                                                                <input type="hidden" name="numbervoucher" value="<?php echo($item2->numbervoucher); ?>"/>
                                                                <button type="submit" style="background-color: transparent; border:  none;">
                                                                    <?php echo($item2->numbervoucher." | ".$item2->pax." | ".$item2->photoresident); ?>
                                                                </button>
                                                            </form>
                                                        </td>
                                                        <td><?php echo( utf8_encode( $item2->fullname) ); ?></td>
                                                        <td><?php echo( date("H:i", strtotime($item2->horaap)) ); ?></td>
                                                        <td><?php echo( date("H:i", strtotime($item2->schedule)) ); ?></td>
                                                        <td><?php echo(utf8_encode($item2->documento)); ?></td>

                                                        <td><?php echo("R$ ".number_format(( ( $item2->valueservice * $item2->qpax ) + (  ($item2->valueservice / 2) * $item2->qchild ) ),2,",",".") ); ?></td>
                                                        <td><?php echo("R$ ".number_format($item2->totalservico,2,",",".") ); ?></td>
                                                        <td><?php echo("R$ ".number_format($item2->totalcredito,2,",",".") ); ?></td>
                                                        <?php if($item2->totalservico-$item2->totalcredito > 0){ ?>
                                                            <td class="bg-danger" style="color: white;"><?php echo("R$ ".number_format($item2->totalservico-$item2->totalcredito,2,",",".") ); ?></td>
                                                        <?php } else {?>
                                                            <td><?php echo("R$ ".number_format($item2->totalservico-$item2->totalcredito,2,",",".") ); ?></td>
                                                        <?php }?>
                                                        <?php if($item2->cliente == "BUZIOS TRANSFER" or $item->cliente == "POUSADA GRAUCA" or $item->cliente == "Hotel Morro da Saudade" ){ ?>
                                                            <td>Não Cobrar</td>
                                                        <?php } else {?>
                                                            <td>Cobrar</td>
                                                        <?php }?>
                                                        <td><?php echo( strtoupper(utf8_encode( $item2->firstname." ".$item2->lastname) )); ?></td>
                                                        <td><?php echo( strtoupper(utf8_encode( $item2->cliente) )); ?></td>
                                                    </tr>
                                                <?php } elseif($item2->idservice == 143 or $item2->idservice == 2 or $item2->idservice == 240 or $item2->idservice == 241 or $item2->idservice == 193
                                                    or $item2->idservice == 168 or $item2->idservice == 169 or $item2->idservice == 228 or $item2->idservice == 10 or $item2->idservice == 9 or $item2->idservice == 129 or $item2->idservice == 133
                                                    or $item2->idservice == 137 or $item2->idservice == 162 or $item2->idservice == 167 or $item2->idservice == 170 or $item2->idservice == 216 or $item2->idservice == 232 or $item2->idservice == 249) { ?>
                                                    <tr class="bg-success">
                                                        <td><?php echo( date('d-m-Y',  strtotime($item2->ap)) ); ?></td>
                                                        <td style="width: 100px;"><?php echo($item2->qpax."|".$item2->qchild."|".$item2->qfree); ?></td>
                                                        <td>
                                                            <form method="post" action="./editar-pax" target="_blank">
                                                                <input type="hidden" name="numbervoucher" value="<?php echo($item2->numbervoucher); ?>"/>
                                                                <button type="submit" style="background-color: transparent; border:  none;">
                                                                    <?php echo($item2->numbervoucher." | ".$item2->pax." | ".$item2->photoresident); ?>
                                                                </button>
                                                            </form>
                                                        </td>
                                                        <td><?php echo( utf8_encode( $item2->fullname) ); ?></td>
                                                        <td><?php echo( date("H:i", strtotime($item2->horaap)) ); ?></td>
                                                        <td><?php echo( date("H:i", strtotime($item2->schedule)) ); ?></td>
                                                        <td><?php echo(utf8_encode($item2->documento)); ?></td>
                                                        <td><?php echo("R$ ".number_format(( ( $item2->valueservice * $item2->qpax ) + (  ($item2->valueservice / 2) * $item2->qchild ) ),2,",",".") ); ?></td>
                                                        <td><?php echo("R$ ".number_format($item2->totalservico,2,",",".") ); ?></td>
                                                        <td><?php echo("R$ ".number_format($item2->totalcredito,2,",",".") ); ?></td>
                                                        <?php if($item2->totalservico-$item2->totalcredito > 0 ){ ?>
                                                            <td class="bg-danger" style="color: white;"><?php echo("R$ ".number_format($item2->totalservico-$item2->totalcredito,2,",",".") ); ?></td>
                                                        <?php } else {?>
                                                            <td><?php echo("R$ ".number_format($item2->totalservico-$item2->totalcredito,2,",",".") ); ?></td>
                                                        <?php }?>
                                                        <?php if($item2->cliente == "BUZIOS TRANSFER" or $item->cliente == "POUSADA GRAUCA" or $item->cliente == "Hotel Morro da Saudade" ){ ?>
                                                            <td>Não Cobrar</td>
                                                        <?php } else {?>
                                                            <td>Cobrar</td>
                                                        <?php }?>
                                                        <td><?php echo( strtoupper(utf8_encode( $item2->firstname." ".$item2->lastname) )); ?></td>
                                                        <td><?php echo( strtoupper(utf8_encode( $item2->cliente) )); ?></td>
                                                    </tr>
                                                <?php } else { ?>
                                                    <tr>
                                                        <td><?php echo( date('d-m-Y',  strtotime($item2->ap)) ); ?></td>
                                                        <td style="width: 100px;"><?php echo($item2->qpax."|".$item2->qchild."|".$item2->qfree); ?></td>
                                                        <td>
                                                            <form method="post" action="./editar-pax" target="_blank">
                                                                <input type="hidden" name="numbervoucher" value="<?php echo($item2->numbervoucher); ?>"/>
                                                                <button type="submit" style="background-color: transparent; border:  none;">
                                                                    <?php echo($item2->numbervoucher." | ".$item2->pax." | ".$item2->photoresident); ?>
                                                                </button>
                                                            </form>
                                                        </td>
                                                        <td><?php echo( utf8_encode( $item2->fullname) ); ?></td>
                                                        <td><?php echo( date("H:i", strtotime($item2->horaap)) ); ?></td>
                                                        <td><?php echo( date("H:i", strtotime($item2->schedule)) ); ?></td>
                                                        <td><?php echo(utf8_encode($item2->documento)); ?></td>
                                                        <td><?php echo("R$ ".number_format(( ( $item2->valueservice * $item2->qpax ) + (  ($item2->valueservice / 2) * $item2->qchild ) ),2,",",".") ); ?></td>
                                                        <td><?php echo("R$ ".number_format($item2->totalservico,2,",",".") ); ?></td>
                                                        <td><?php echo("R$ ".number_format($item2->totalcredito,2,",",".") ); ?></td>
                                                        <?php if($item2->totalservico-$item2->totalcredito > 0 ){ ?>
                                                            <td class="bg-danger" style="color: white;"><?php echo("R$ ".number_format($item2->totalservico-$item2->totalcredito,2,",",".") ); ?></td>
                                                        <?php } else {?>
                                                            <td><?php echo("R$ ".number_format($item2->totalservico-$item2->totalcredito,2,",",".") ); ?></td>
                                                        <?php }?>
                                                        <?php if($item2->cliente == "BUZIOS TRANSFER" or $item->cliente == "POUSADA GRAUCA" or $item->cliente == "Hotel Morro da Saudade" ){ ?>
                                                            <td>Não Cobrar</td>
                                                        <?php } else {?>
                                                            <td>Cobrar</td>
                                                        <?php }?>
                                                        <td><?php echo( strtoupper(utf8_encode( $item2->firstname." ".$item2->lastname) )); ?></td>
                                                        <td><?php echo( strtoupper(utf8_encode( $item2->cliente) )); ?></td>
                                                    </tr>
                                                <?php }?>


                                            <?php } ?>
                                        <?php }?>
                                        <?php if($servico[0] == 0 and count($servico) == 1){$i += 1;} }  ?>
                                    </tbody>
                                </table>

                            </div>

                        </div>
                    </div>
                </div>

            </div>
            <p align="center" style="font-weight: bold; font-size: 22px;"><?php echo("PAX (".($totalPax1+$totalPax2).") CHILD (".($totalChild1+$totalchil2).") FREE (".($totalfree1+$totalfree2).")"); ?></p>
        </div>
        <script type="text/javascript">
            localStorage.setItem("totalpax", <?php echo($totalPax1+$totalPax2); ?>);
            localStorage.setItem("totalchild", <?php echo($totalChild1+$totalchil2); ?>);
            localStorage.setItem("totalfree", <?php echo($totalfree1+$totalfree2); ?>);
        </script>
        <?php require_once ('footer.php'); ?>
