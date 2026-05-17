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
else{
    $dateInput  = date("Y-m-d");
    $dateOutput = date("Y-m-d");
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
if( isset( $_GET['voucher'] ) )
{
    $new_order_driver = $pdo->prepare('insert into `ct_orderservice` values (DEFAULT, :namedriver, :voucher, :phone, :tpax, :tchild, :idservico, :apanha, :doc, :namepax, :datee) ');
    $new_order_driver->execute(
        array(":namedriver" => $_GET['namedriver'],
            ":voucher"      =>  $_GET['voucher'],
            ":phone"        => $_GET['phone'],
            ":tpax"         => $_GET['tpax'],
            ":tchild"       => $_GET['tchild'],
            ":idservico" => $_GET['idservico'],
            ":apanha"   => $_GET['apanha'],
            ":doc"      => $_GET['doc'],
            ":namepax"  => $_GET['namepax'],
            ":datee"    => $_GET['datee']
        )
    );
    echo($pdo->lastInsertId());
}

if( isset($_GET['motorista']) )
{
    $find_driver = $pdo->prepare('SELECT `namedriver` as motorista FROM `ct_orderservice` GROUP by `namedriver`');
    $find_driver->execute();
    echo( json_encode($data_find_driver = $find_driver->fetchAll(PDO::FETCH_CLASS)) );
}
$todosClientes = $pdo->prepare('SELECT * FROM `ct_cliente`');
$todosClientes->execute();
$todosServicos = $pdo->prepare('select * from `ct_servico` order by ordem, fullname ');
$todosServicos->execute();
$schedule = $pdo->prepare("select * from `ct_service_schedule` where `schedule` not like '00:00%' order by `schedule`");
$schedule->execute();

$totalPax1   = 0;
$totalPax2   = 0;
$totalChild1 = 0;
$totalchil2  = 0;
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
    #snackbar {
        visibility: hidden;
        min-width: 250px;
        margin-left: -125px;
        background-color: #333;
        color: #fff;
        text-align: center;
        border-radius: 2px;
        padding: 16px;
        position: fixed;
        z-index: 1;
        left: 50%;
        bottom: 30px;
        font-size: 17px;
    }

    #snackbar.show {
        visibility: visible;
        -webkit-animation: fadein 0.5s, fadeout 0.5s 2.5s;
        animation: fadein 0.5s, fadeout 0.5s 2.5s;
    }

    @-webkit-keyframes fadein {
        from {bottom: 0; opacity: 0;}
        to {bottom: 30px; opacity: 1;}
    }

    @keyframes fadein {
        from {bottom: 0; opacity: 0;}
        to {bottom: 30px; opacity: 1;}
    }

    @-webkit-keyframes fadeout {
        from {bottom: 30px; opacity: 1;}
        to {bottom: 0; opacity: 0;}
    }

    @keyframes fadeout {
        from {bottom: 30px; opacity: 1;}
        to {bottom: 0; opacity: 0;}
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
        <div id="snackbar"></div>
        <div class="">
            <div class="card">
                <div class="card-header" id="headingOne">
                    <h5 class="mb-0">
                        <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseOne"  aria-controls="collapseOne">
                            Pesquisar Mapa
                        </button>
                    </h5>
                </div>

                <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionExample">
                    <div class="card-body">
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
                            <div class="container-fluid">
                                <button class="btn btn-outline-success btn-block btn-large" type="submit" name="mapa"><strong> Selecionar Mapa </strong></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header" id="headingTwo">
                    <h5 class="mb-0">
                        <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="true" aria-controls="collapseTwo">
                            Visualizar Mapa
                        </button>
                    </h5>
                </div>
                <div id="collapseTwo" class="collapse show" aria-labelledby="headingTwo" data-parent="#accordionExample">
                    <div class="card-body">
                        <h3>Mapa de Serviço </h3><br>
                        <form>
                            <input name="namemotorista" id="namemotorista" type="text" class="form-control" placeholder="Digite o nome do motorista">
                        </form>
                        <hr>
                        <div>
                            <div class="">

                                <div class="table-responsive">
                                    <table id="myTable" class="table table-striped table-bordered dataTable">
                                        <thead>
                                        <tr>
                                            <th>Pax/Child/Free</th>
                                            <th>File | Pax | TEL</th>
                                            <th>Serviço</th>
                                            <th>Apanha</th>
                                            <th>Complemento</th>
                                            <th>Salvar</th>
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
                                                            "SELECT r.id,pax, documento, dateinput, dateoutput, c.fullname as cliente,se.fullname as serivco, namepayment, r.idservico,
                                                    qtdpax, qtdchild, qtdfree, ss.schedule,numbervoucher, horaap, valueservice,u.firstname, u.lastname, r.photoresident FROM `ct_reserva` r left join ct_cliente c on
                                                    c.id = r.idcliente join ct_servico se on se.id = r.idservico left join ct_service_schedule ss on ss.idshedule = r.idhorario
                                                    left join `ct_form_of_ payment` as cfp on cfp.id = r.idpayment left join `ct_usuario` u on u.id = r.idresponsavel
                                                    where r.`dateinput` >= :inn and r.`dateinput` <= :outt 
                                                    and r.`idstatus` <> 2 and r.idhorario = :idhorario order by ss.schedule ");
                                                        $dadosReserva->execute( array(":inn" => $dateInput, ":outt" => $dateOutput, ":idhorario" => $idhorario  ));
                                                        $adicionais = $pdo->prepare(
                                                            'SELECT ra.dateinput as ap, s.fullname, ss.schedule, qpax, qchild, qfree,r.numbervoucher, c.fullname as
                                                    cliente, r.pax, ra.documento, ra.valueservice, ra.horaap, namepayment,u.firstname, u.lastname, r.photoresident, ra.idservice FROM `ct_recentlyadd` ra left join `ct_reserva` r 
                                                    on r.id = ra.idrecently left join ct_cliente c on c.id = r.idcliente left join `ct_form_of_ payment` as cfp on 
                                                    cfp.id = r.idpayment left join ct_servico s on s.id = ra.idservice left join ct_service_schedule ss on
                                                    ss.idshedule = ra.idschedule left join `ct_usuario` u on u.id = r.idresponsavel where ra.dateinput >= :inn and r.`idstatus` <> 2 and ra.`dateinput` <= :outt 
                                                    and ra.idschedule = :idhorario order by ss.schedule');
                                                        $adicionais->execute(array(":inn" => $dateInput, ":outt" => $dateOutput, ":idhorario" => $idhorario ) );
                                                    }
                                                    else{
                                                        $dadosReserva = $pdo->prepare(
                                                            "SELECT r.id,pax, documento, dateinput, dateoutput, c.fullname as cliente,se.fullname as serivco, namepayment, qtdpax, qtdchild, qtdfree, ss.schedule,
                                                        numbervoucher, horaap, valueservice,u.firstname, u.lastname, r.photoresident,r.idservico FROM `ct_reserva` r left join ct_cliente c on c.id = r.idcliente
                                                        join ct_servico se on se.id = r.idservico left join `ct_usuario` u on u.id = r.idresponsavel
                                                        left join ct_service_schedule ss on ss.idshedule = r.idhorario left join `ct_form_of_ payment` as cfp
                                                        on cfp.id = r.idpayment  where r.`dateinput` >= :inn and r.`dateinput` <= :outt and r.`idstatus` <> 2
                                                        and r.idservico = :servico and r.idhorario = :idhorario order by ss.schedule ");
                                                        $dadosReserva->execute( array(":inn" => $dateInput,
                                                            ":servico" => $servico[$i], ":outt" => $dateOutput, ":idhorario" => $idhorario  ));

                                                        $adicionais = $pdo->prepare(
                                                            'SELECT ra.dateinput as ap, s.fullname, ss.schedule, qpax, qchild, qfree,u.firstname, u.lastname,r.numbervoucher, c.fullname as cliente, r.pax, ra.documento, ra.valueservice, ra.horaap, 
                                                        namepayment, r.photoresident,ra.idservice FROM `ct_recentlyadd` ra left join `ct_reserva` r on r.id = ra.idrecently left join ct_cliente c on 
                                                       c.id = r.idcliente left join `ct_form_of_ payment` as cfp on cfp.id = r.idpayment left join `ct_usuario` u on u.id = r.idresponsavel
                                                       left join ct_servico s on s.id = ra.idservice left join ct_service_schedule ss on ss.idshedule = ra.idschedule  
                                                       where ra.dateinput >= :inn  and ra.idservice = :servico and r.`idstatus` <> 2
                                                       and ra.`dateinput` <= :outt and ra.idschedule = :idhorario order by ss.schedule');
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
                                                            "SELECT r.id,pax, documento, dateinput, dateoutput, c.fullname as cliente, se.fullname as serivco, namepayment,r.idservico,
                                                qtdpax, qtdchild, qtdfree, ss.schedule, numbervoucher, horaap, valueservice,u.firstname, u.lastname, r.photoresident FROM `ct_reserva` r left join ct_cliente c on 
                                                c.id = r.idcliente join ct_servico se on se.id = r.idservico left join ct_service_schedule ss on ss.idshedule = r.idhorario
                                                left join `ct_form_of_ payment` as cfp on cfp.id = r.idpayment left join `ct_usuario` u on u.id = r.idresponsavel  where r.`dateinput` >= :inn and r.`dateinput` <= :outt 
                                                and r.`idstatus` <> 2 order by ss.schedule ");
                                                        $dadosReserva->execute( array(":inn" => $dateInput,":outt" => $dateOutput ));

                                                        $adicionais = $pdo->prepare(
                                                            'SELECT ra.dateinput as ap, s.fullname, ss.schedule, qpax, qchild, qfree, r.numbervoucher, c.fullname as cliente,ra.idservice,
                                                r.pax, ra.documento, ra.valueservice, ra.horaap, namepayment,u.firstname, u.lastname, r.photoresident FROM `ct_recentlyadd` ra left join `ct_reserva` r on 
                                                r.id = ra.idrecently left join ct_cliente c on c.id = r.idcliente left join `ct_form_of_ payment` as cfp on cfp.id = r.idpayment
                                                left join ct_servico s on s.id = ra.idservice left join ct_service_schedule ss on ss.idshedule = ra.idschedule  left join `ct_usuario` u on u.id = r.idresponsavel
                                                where ra.dateinput >= :inn  and r.`idstatus` <> 2 and ra.`dateinput` <= :outt order by ss.schedule');
                                                        $adicionais->execute(array(":inn" => $dateInput,":outt" => $dateOutput) );
                                                    }
                                                    else{
                                                        $dadosReserva = $pdo->prepare(
                                                            "SELECT r.id,pax, documento, dateinput, dateoutput, c.fullname as cliente, se.fullname as serivco, namepayment,r.idservico,
                                                qtdpax, qtdchild, qtdfree, ss.schedule, numbervoucher, horaap, valueservice,u.firstname, u.lastname, r.photoresident FROM `ct_reserva` r left join ct_cliente c on 
                                                c.id = r.idcliente join ct_servico se on se.id = r.idservico left join ct_service_schedule ss on ss.idshedule = r.idhorario
                                                left join `ct_form_of_ payment` as cfp on cfp.id = r.idpayment left join `ct_usuario` u on u.id = r.idresponsavel  where r.`dateinput` >= :inn and r.`dateinput` <= :outt 
                                                and r.`idstatus` <> 2 and r.idservico = :servico order by ss.schedule ");
                                                        $dadosReserva->execute( array(":inn" => $dateInput,
                                                            ":servico" => $servico[$i], ":outt" => $dateOutput ));


                                                        $adicionais = $pdo->prepare(
                                                            'SELECT ra.dateinput as ap, s.fullname, ss.schedule, qpax, qchild, qfree, r.numbervoucher, c.fullname as cliente,ra.idservice,
                                                r.pax, ra.documento, ra.valueservice, ra.horaap, namepayment,u.firstname, u.lastname, r.photoresident FROM `ct_recentlyadd` ra left join `ct_reserva` r on 
                                                r.id = ra.idrecently left join ct_cliente c on c.id = r.idcliente left join `ct_form_of_ payment` as cfp on cfp.id = r.idpayment
                                                left join ct_servico s on s.id = ra.idservice left join ct_service_schedule ss on ss.idshedule = ra.idschedule left join `ct_usuario` u on u.id = r.idresponsavel
                                                where ra.dateinput >= :inn  and ra.idservice = :servico and r.`idstatus` <> 2
                                                and ra.`dateinput` <= :outt order by ss.schedule');
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
                                                            "SELECT r.id,pax, documento, dateinput, dateoutput, c.fullname as cliente,se.fullname as serivco, namepayment,r.idservico,
                                                qtdpax, qtdchild, qtdfree, ss.schedule,numbervoucher, horaap, valueservice,u.firstname, u.lastname, r.photoresident FROM `ct_reserva` r left join ct_cliente c 
                                                on c.id = r.idcliente join ct_servico se on se.id = r.idservico left join ct_service_schedule ss on ss.idshedule = r.idhorario
                                                left join `ct_form_of_ payment` as cfp on cfp.id = r.idpayment left join `ct_usuario` u on u.id = r.idresponsavel  where r.`dateinput` >= :inn and r.`idstatus` <> 2 
                                                and r.`dateinput` <= :outt and r.idservico = :servico and r.idcliente = :cliente and r.idhorario = :idhorario ");
                                                        $dadosReserva->execute( array(":inn" => $dateInput, ":servico" => $servico[$i],
                                                            ":cliente" => $idCliente, ":outt" => $dateOutput, ":idhorario" => $idhorario ));


                                                        $adicionais = $pdo->prepare(
                                                            'SELECT ra.dateinput as ap, s.fullname, ss.schedule, qpax, qchild, qfree, r.numbervoucher, c.fullname as cliente,ra.idservice,
                                                r.pax, ra.documento, ra.valueservice, ra.horaap, namepayment, r.photoresident FROM `ct_recentlyadd` ra left join `ct_reserva` r on 
                                                r.id = ra.idrecently left join ct_cliente c on c.id = r.idcliente left join `ct_form_of_ payment` as cfp on cfp.id = r.idpayment
                                                left join ct_servico s on s.id = ra.idservice left join ct_service_schedule ss on ss.idshedule = ra.idschedule 
                                                where ra.dateinput >= :inn and r.`idstatus` <> 2  and ra.idservice = :servico and ra.`dateinput` <= :outt and r.idcliente = 
                                                :cliente and ra.idschedule = :idhorario order by ss.schedule');
                                                        $adicionais->execute(array(":inn" => $dateInput, ":servico" => $servico[$i],
                                                            ":cliente" => $idCliente, ":outt" => $dateOutput, ":idhorario" => $idhorario ) );
                                                    }
                                                    else{
                                                        $dadosReserva = $pdo->prepare(
                                                            "SELECT r.id,pax, documento, dateinput, dateoutput, c.fullname as cliente,se.fullname as serivco, namepayment,r.idservico,
                                                qtdpax, qtdchild, qtdfree, ss.schedule,numbervoucher, horaap, valueservice,u.firstname, u.lastname, r.photoresident FROM `ct_reserva` r left join ct_cliente c 
                                                on c.id = r.idcliente join ct_servico se on se.id = r.idservico left join ct_service_schedule ss on ss.idshedule = r.idhorario
                                                left join `ct_form_of_ payment` as cfp on cfp.id = r.idpayment left join `ct_usuario` u on u.id = r.idresponsavel  where r.`dateinput` >= :inn and r.`idstatus` <> 2 
                                                and r.`dateinput` <= :outt and r.idservico = :servico and r.idcliente = :cliente and r.idhorario = :idhorario ");
                                                        $dadosReserva->execute( array(":inn" => $dateInput, ":servico" => $servico[$i],
                                                            ":cliente" => $idCliente, ":outt" => $dateOutput, ":idhorario" => $idhorario ));


                                                        $adicionais = $pdo->prepare(
                                                            'SELECT ra.dateinput as ap, s.fullname, ss.schedule, qpax, qchild, qfree, r.numbervoucher, c.fullname as cliente,ra.idservice,
                                                r.pax, ra.documento, ra.valueservice, ra.horaap, namepayment, r.photoresident FROM `ct_recentlyadd` ra left join `ct_reserva` r on 
                                                r.id = ra.idrecently left join ct_cliente c on c.id = r.idcliente left join `ct_form_of_ payment` as cfp on cfp.id = r.idpayment
                                                left join ct_servico s on s.id = ra.idservice left join ct_service_schedule ss on ss.idshedule = ra.idschedule 
                                                where ra.dateinput >= :inn and r.`idstatus` <> 2  and ra.idservice = :servico and ra.`dateinput` <= :outt and r.idcliente = 
                                                :cliente and ra.idschedule = :idhorario order by ss.schedule');
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
                                                            "SELECT r.id,pax, documento, dateinput, dateoutput, c.fullname as cliente,se.fullname as serivco, namepayment,r.idservico,
                                                qtdpax, qtdchild, qtdfree, ss.schedule, numbervoucher, horaap, valueservice,u.firstname, u.lastname, r.photoresident FROM `ct_reserva` r left join ct_cliente c on
                                                c.id = r.idcliente  join ct_servico se on se.id = r.idservico left join ct_service_schedule ss on ss.idshedule = r.idhorario
                                                left join `ct_form_of_ payment` as cfp on cfp.id = r.idpayment left join `ct_usuario` u on u.id = r.idresponsavel where r.`dateinput` >= :inn and r.`idstatus` <> 2 
                                                and r.`dateinput` <= :outt  and r.idcliente = :cliente  order by ss.schedule ");
                                                        $dadosReserva->execute( array(":inn" => $dateInput, ":cliente" => $idCliente, ":outt" => $dateOutput ));

                                                        $adicionais = $pdo->prepare(
                                                            'SELECT ra.dateinput as ap, s.fullname, ss.schedule, qpax, qchild, qfree,r.numbervoucher, c.fullname as cliente,ra.idservice,
                                                r.pax, ra.documento, ra.valueservice, ra.horaap, namepayment,u.firstname, u.lastname, r.photoresident FROM `ct_recentlyadd` ra left join `ct_reserva` r 
                                                on r.id = ra.idrecently left join ct_cliente c on c.id = r.idcliente left join `ct_form_of_ payment` as cfp on cfp.id = 
                                                r.idpayment left join ct_servico s on s.id = ra.idservice left join ct_service_schedule ss on ss.idshedule = ra.idschedule
                                                left join `ct_usuario` u on u.id = r.idresponsavel
                                                where ra.dateinput >= :inn and r.`idstatus` <> 2  and ra.`dateinput` <= :outt and r.idcliente = :cliente order by ss.schedule');
                                                        $adicionais->execute(array(":inn" => $dateInput, ":cliente" => $idCliente, ":outt" => $dateOutput ) );
                                                    }
                                                    else{
                                                        $dadosReserva = $pdo->prepare(
                                                            "SELECT r.id,pax, documento, dateinput, dateoutput, c.fullname as cliente,se.fullname as serivco, namepayment,r.idservico,
                                                qtdpax, qtdchild, qtdfree, ss.schedule, numbervoucher, horaap, valueservice,u.firstname, u.lastname, r.photoresident FROM `ct_reserva` r left join ct_cliente c on
                                                c.id = r.idcliente  join ct_servico se on se.id = r.idservico left join ct_service_schedule ss on ss.idshedule = r.idhorario
                                                left join `ct_form_of_ payment` as cfp on cfp.id = r.idpayment left join `ct_usuario` u on u.id = r.idresponsavel  where r.`dateinput` >= :inn and r.`idstatus` <> 2 
                                                and r.`dateinput` <= :outt and r.idservico = :servico and r.idcliente = :cliente  order by ss.schedule ");
                                                        $dadosReserva->execute( array(":inn" => $dateInput, ":servico" => $servico[$i],
                                                            ":cliente" => $idCliente, ":outt" => $dateOutput ));

                                                        $adicionais = $pdo->prepare(
                                                            'SELECT ra.dateinput as ap, s.fullname, ss.schedule, qpax, qchild, qfree,r.numbervoucher, c.fullname as cliente,ra.idservice,
                                                r.pax, ra.documento, ra.valueservice, ra.horaap, namepayment,u.firstname, u.lastname, r.photoresident FROM `ct_recentlyadd` ra left join `ct_reserva` r 
                                                on r.id = ra.idrecently left join ct_cliente c on c.id = r.idcliente left join `ct_form_of_ payment` as cfp on cfp.id = 
                                                r.idpayment left join ct_servico s on s.id = ra.idservice left join ct_service_schedule ss on ss.idshedule = ra.idschedule  
                                                left join `ct_usuario` u on u.id = r.idresponsavel
                                                where ra.dateinput >= :inn and r.`idstatus` <> 2  and ra.idservice = :servico and ra.`dateinput` <= :outt and r.idcliente =
                                                :cliente order by ss.schedule');
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
                                            <?php foreach ($dadosGerais as $item) {$totalPax1 = $totalPax1 + $item->qtdpax; $totalChild1 = $totalChild1 + $item->qtdchild; ?>
                                                <tr>
                                                    <td><?php echo($item->qtdpax."/".$item->qtdchild."/".$item->qtdfree); ?></td>
                                                    <td><?php echo($item->numbervoucher." | ".$item->pax." | ".$item->photoresident); ?></td>
                                                    <td><?php echo( utf8_encode( $item->serivco) ); ?></td>
                                                    <td><?php echo( date("H:i", strtotime($item->horaap)) ); ?></td>
                                                    <td><?php echo($item->documento); ?></td>
                                                    <td style="cursor: pointer;" onclick="orderService('<?php echo($item->numbervoucher); ?>','<?php echo($item->pax); ?>','<?php echo($item->photoresident); ?>', '<?php echo($item->qtdpax); ?>',
                                                            '<?php echo($item->qtdchild); ?>', '<?php echo($item->idservico); ?>', '<?php echo($item->horaap); ?>',
                                                            '<?php echo($item->documento); ?>', this, '<?php echo($item->dateinput); ?>')">Incluir Serviço</td>
                                                </tr>
                                            <?php } ?>
                                            <?php if( $contador > 0 ){ ?>
                                                <?php foreach ($registro as $item2) {$totalPax2 = $totalPax2 + $item2->qpax; $totalchil2 = $totalchil2 + $item2->qchild; ?>
                                                    <tr>
                                                        <td><?php echo($item2->qpax."/".$item2->qchild."/".$item2->qfree); ?></td>
                                                        <td><?php echo($item2->numbervoucher." | ".$item2->pax." | ".$item2->photoresident); ?></td>
                                                        <td><?php echo( utf8_encode( $item2->fullname) ); ?></td>
                                                        <td><?php echo( date("H:i", strtotime($item2->horaap)) ); ?></td>
                                                        <td><?php echo($item2->documento); ?></td>
                                                        <td style="cursor: pointer;" onclick="orderService('<?php echo($item2->numbervoucher); ?>','<?php echo($item2->pax); ?>','<?php echo($item2->photoresident); ?>', '<?php echo($item2->qpax); ?>',
                                                                '<?php echo($item2->qchild); ?>', '<?php echo($item2->idservice); ?>', '<?php echo($item2->horaap); ?>',
                                                                '<?php echo($item->documento); ?>', this, '<?php echo($item->dateinput); ?>')">Incluir Serviço</td>
                                                    </tr>
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
            </div>
            <div class="card">
                <div class="card-header" id="headingTree">
                    <h5 class="mb-0">
                        <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseTree" aria-expanded="true" aria-controls="collapseTree">
                            Imprimir Mapa
                        </button>
                    </h5>
                </div>
                <div id="collapseTree" class="collapse show" aria-labelledby="headingTree" data-parent="#accordionExample">
                    <div class="card-body">
                        <h3>Preencha os campos abaixo </h3><br>
                        <form autocomplete="on" action="relatorio/print-motorista.php" method="post" target="_blank">
                            <div class="col-md-4 pull-left autocomplete">
                                <label for="motorista">Nome do Motorista</label>
                                <select class="form-control" required name="motorista" id="motorista" onclick="findDriver()">
                                    <option>Selecione </option>
                                </select>
                            </div>
                            <div class="col-md-4 pull-left">
                                <label for="inicio">Data de Embarque Inicial</label>
                                <input required class="form-control" name="inicio" id="inicio" type="date">
                            </div>
                            <div class="col-md-4 pull-right">
                                <label for="fim">Data de Embarque Final</label>
                                <input required class="form-control" name="fim" id="fim" type="date">
                            </div>
                            <div class="container-fluid">
                                <button type="submit" name="gerarmapa" class="btn btn-success btn-lg">Imnprimir</button>
                            </div>
                        </form>
                        <hr>
                    </div>
                </div>
            </div>
        </div>

        <?php require_once ('footer.php'); ?>
        <script type="text/javascript">
            function orderService(voucher, namepax, phone, tpax, tchild, idservico, apanha, doc, linha,  checkin){
                var nomemotorista = document.getElementById('namemotorista').value;
                var x = document.getElementById("snackbar");
                if( nomemotorista == '' )
                {
                    document.getElementById('namemotorista').focus();
                    x.innerHTML = 'INFORME O NOME DO MOTORISTA';
                    x.className = "show";
                }else{
                    $.ajax({
                        type: 'GET',
                        url: 'http://grupocassi.com.br/adm/mapa-motorista.php',
                        data: {namedriver: nomemotorista, voucher: voucher, phone:phone, tpax:tpax, tchild:tchild, idservico:idservico, apanha:apanha, doc:doc, namepax:namepax, datee:checkin },
                        dataType: 'json',
                        success: function(response){
                            console.log(response);

                        }
                    });
                    x.innerHTML = 'O voucher ' + voucher + ' foi adicionado com sucesso para o motorista ' +  nomemotorista;
                    x.className = "show";
                    var tr = $(linha).closest('tr');

                    tr.fadeOut(400, function() {
                        tr.remove();
                    });

                }
                setTimeout(function(){ x.className = x.className.replace("show", ""); }, 4000);


            }
            function findDriver() {
                $.ajax({
                    type: 'GET',
                    url: 'http://grupocassi.com.br/adm/motorista.php',
                    dataType: 'json',
                    success: function(response){
                        $.each(response,  function (key, item) {
                            $("#motorista").append(`<option class="form-control" value="${item.motorista}">${item.motorista}</option>`);
                        });

                    }
                });
            }
        </script>