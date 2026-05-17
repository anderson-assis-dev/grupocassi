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
$totalfree1 = 0;
$totalfree2  = 0;

$previsao_one = $pdo->prepare('SELECT * FROM `ct_servico` where fullname like :servico');
$previsao_one->execute(array(":servico" => 'aeroporto%'));
$data_previsao_one = $previsao_one->fetchAll(PDO::FETCH_CLASS);

$previsao_two = $pdo->prepare('SELECT * FROM `ct_servico` where fullname like :servico');
$previsao_two->execute(array(":servico" => 'terminal%'));
$data_previsao_two = $previsao_two->fetchAll(PDO::FETCH_CLASS);

$previsao_tree = $pdo->prepare('SELECT * FROM `ct_servico` where fullname like :servico');
$previsao_tree->execute(array(":servico" => 'morro%'));
$data_previsao_tree = $previsao_tree->fetchAll(PDO::FETCH_CLASS);

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
                <div class="container-fluid">
                    <button class="btn btn-outline-success btn-block btn-large" type="submit" name="mapa"><strong> Selecionar Mapa </strong></button>
                </div>
            </form>
            <div class="card card-outline-primary">
                <div class="card-body">
                    <h3>Mapa de Serviço </h3>
                    <hr>
                    <h4>Previsões</h4>
                    <div class="col-md-4 pull-left">
                        <h5 align="center">AEROPORTO</h5>
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th>Horário</th>
                                <th>Serviço</th>
                                <th>Total</th>
                            </tr>

                            </thead>
                            <tbody>
                            <?php foreach ($data_previsao_one as $item){
                                $previsao_aeroporto = $pdo->prepare('SELECT sum(r.qtdpax + r.qtdchild + r.qtdfree ) as total FROM `ct_reserva` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservico = :servico and r.idhorario = :horario');
                                $previsao_aeroporto->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 1));
                                $data_previsao_aeroporto = $previsao_aeroporto->fetch(PDO::FETCH_ASSOC);

                                $previsao_aeroporto9= $pdo->prepare('SELECT sum(r.qtdpax + r.qtdchild + r.qtdfree ) as total FROM `ct_reserva` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservico = :servico and r.idhorario = :horario');
                                $previsao_aeroporto9->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 5));
                                $data_previsao_aeroporto9 = $previsao_aeroporto9->fetch(PDO::FETCH_ASSOC);

                                $previsao_aeroporto11 = $pdo->prepare('SELECT sum(r.qtdpax + r.qtdchild + r.qtdfree ) as total FROM `ct_reserva` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservico = :servico and r.idhorario = :horario');
                                $previsao_aeroporto11->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 7));
                                $data_previsao_aeroporto11 = $previsao_aeroporto11->fetch(PDO::FETCH_ASSOC);

                                $previsao_aeroporto13 = $pdo->prepare('SELECT sum(r.qtdpax + r.qtdchild + r.qtdfree ) as total FROM `ct_reserva` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservico = :servico and r.idhorario = :horario');
                                $previsao_aeroporto13->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 9));
                                $data_previsao_aeroporto13 = $previsao_aeroporto13->fetch(PDO::FETCH_ASSOC);

                                $previsao_aeroporto16 = $pdo->prepare('SELECT sum(r.qtdpax + r.qtdchild + r.qtdfree ) as total FROM `ct_reserva` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservico = :servico and r.idhorario = :horario');
                                $previsao_aeroporto16->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 12));
                                $data_previsao_aeroporto16 = $previsao_aeroporto16->fetch(PDO::FETCH_ASSOC);


                                $previsao_terminal_others1aeroporto = $pdo->prepare('SELECT sum(r.qpax + r.qchild + r.qfree ) as total FROM `ct_recentlyadd` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservice = :servico and r.idschedule = :horario');
                                $previsao_terminal_others1aeroporto->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 1));
                                $data_previsao_aeroporto2 = $previsao_terminal_others1aeroporto->fetch(PDO::FETCH_ASSOC);

                                $previsao_terminal_others101aeroporto = $pdo->prepare('SELECT sum(r.qpax + r.qchild + r.qfree ) as total FROM `ct_recentlyadd` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservice = :servico and r.idschedule = :horario');
                                $previsao_terminal_others101aeroporto->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 5));
                                $data_previsao_aeroporto92 = $previsao_terminal_others101aeroporto->fetch(PDO::FETCH_ASSOC);

                                $previsao_terminal_others121aeroporto = $pdo->prepare('SELECT sum(r.qpax + r.qchild + r.qfree ) as total FROM `ct_recentlyadd` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservice = :servico and r.idschedule = :horario');
                                $previsao_terminal_others121aeroporto->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 7));
                                $data_previsao_aeroporto112 = $previsao_terminal_others121aeroporto->fetch(PDO::FETCH_ASSOC);

                                $previsao_terminal_others151aeroporto = $pdo->prepare('SELECT sum(r.qpax + r.qchild + r.qfree ) as total FROM `ct_recentlyadd` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservice = :servico and r.idschedule = :horario');
                                $previsao_terminal_others151aeroporto->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 9));
                                $data_previsao_aeroporto132 = $previsao_terminal_others151aeroporto->fetch(PDO::FETCH_ASSOC);

                                $previsao_terminal_others171aeroporto = $pdo->prepare('SELECT sum(r.qpax + r.qchild + r.qfree ) as total FROM `ct_recentlyadd` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservice = :servico and r.idschedule = :horario');
                                $previsao_terminal_others171aeroporto->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 12));
                                $data_previsao_aeroporto162 = $previsao_terminal_others171aeroporto->fetch(PDO::FETCH_ASSOC);

                                ?>
                                <?php if($data_previsao_aeroporto['total'] <> null or $data_previsao_aeroporto2['total'] <> null){ ?>
                                    <tr>
                                        <td>05:30</td>
                                        <td><?php echo(strtoupper($item->fullname)); ?></td>
                                        <td><?php echo($data_previsao_aeroporto['total'] + $data_previsao_aeroporto2['total']); ?></td>
                                    </tr>
                                <?php }?>
                                <?php if($data_previsao_aeroporto9['total'] <> null or $data_previsao_aeroporto92['total'] <> null){ ?>
                                    <tr>
                                        <td>09:30</td>
                                        <td><?php echo(strtoupper($item->fullname)); ?></td>
                                        <td><?php echo($data_previsao_aeroporto9['total'] + $data_previsao_aeroporto92['total'] ); ?></td>
                                    </tr>
                                <?php }?>
                                <?php if($data_previsao_aeroporto11['total'] <> null or $data_previsao_aeroporto112['total'] <> null){ ?>
                                    <tr>
                                        <td>11:30</td>
                                        <td><?php echo(strtoupper($item->fullname)); ?></td>
                                        <td><?php echo($data_previsao_aeroporto11['total'] + $data_previsao_aeroporto112['total']); ?></td>
                                    </tr>
                                <?php }?>
                                <?php if($data_previsao_aeroporto13['total'] <> null or $data_previsao_aeroporto132['total'] <> null){ ?>
                                    <tr>
                                        <td>13:30</td>
                                        <td><?php echo(strtoupper($item->fullname)); ?></td>
                                        <td><?php echo($data_previsao_aeroporto13['total'] + $data_previsao_aeroporto132['total']); ?></td>
                                    </tr>
                                <?php }?>
                                <?php if($data_previsao_aeroporto16['total'] <> null or $data_previsao_aeroporto16['total'] <> null){ ?>
                                    <tr>
                                        <td>16:00</td>
                                        <td><?php echo(strtoupper($item->fullname)); ?></td>
                                        <td><?php echo($data_previsao_aeroporto16['total'] +$data_previsao_aeroporto16['total'] ); ?></td>
                                    </tr>
                                <?php }?>
                            <?php }?>

                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-4 pull-left">
                        <h5 align="center">CASSI COMÉRIO</h5>
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th>Horário</th>
                                <th>Serviço</th>
                                <th>Total</th>
                            </tr>

                            </thead>
                            <tbody>
                            <?php foreach ($data_previsao_two as $item){
                                $previsao_terminal_others = $pdo->prepare('SELECT sum(r.qtdpax + r.qtdchild + r.qtdfree ) as total FROM `ct_reserva` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservico = :servico and r.idhorario = :horario');
                                $previsao_terminal_others->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 3));
                                $data_previsao_terminal_others = $previsao_terminal_others->fetch(PDO::FETCH_ASSOC);

                                $previsao_terminal_others10 = $pdo->prepare('SELECT sum(r.qtdpax + r.qtdchild + r.qtdfree ) as total FROM `ct_reserva` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservico = :servico and r.idhorario = :horario');
                                $previsao_terminal_others10->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 6));
                                $data_previsao_terminal_others10 = $previsao_terminal_others10->fetch(PDO::FETCH_ASSOC);

                                $previsao_terminal_others12 = $pdo->prepare('SELECT sum(r.qtdpax + r.qtdchild + r.qtdfree ) as total FROM `ct_reserva` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservico = :servico and r.idhorario = :horario');
                                $previsao_terminal_others12->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 8));
                                $data_previsao_terminal_others12 = $previsao_terminal_others12->fetch(PDO::FETCH_ASSOC);

                                $previsao_terminal_others15 = $pdo->prepare('SELECT sum(r.qtdpax + r.qtdchild + r.qtdfree ) as total FROM `ct_reserva` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservico = :servico and r.idhorario = :horario');
                                $previsao_terminal_others15->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 10));
                                $data_previsao_terminal_others15 = $previsao_terminal_others15->fetch(PDO::FETCH_ASSOC);

                                $previsao_terminal_others17 = $pdo->prepare('SELECT sum(r.qtdpax + r.qtdchild + r.qtdfree ) as total FROM `ct_reserva` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservico = :servico and r.idhorario = :horario');
                                $previsao_terminal_others17->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 13));
                                $data_previsao_terminal_others17 = $previsao_terminal_others17->fetch(PDO::FETCH_ASSOC);


                                $previsao_terminal_others1 = $pdo->prepare('SELECT sum(r.qpax + r.qchild + r.qfree ) as total FROM `ct_recentlyadd` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservice = :servico and r.idschedule = :horario');
                                $previsao_terminal_others1->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 3));
                                $data_previsao_terminal_others1 = $previsao_terminal_others1->fetch(PDO::FETCH_ASSOC);

                                $previsao_terminal_others101 = $pdo->prepare('SELECT sum(r.qpax + r.qchild + r.qfree ) as total FROM `ct_recentlyadd` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservice = :servico and r.idschedule = :horario');
                                $previsao_terminal_others101->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 6));
                                $data_previsao_terminal_others101 = $previsao_terminal_others101->fetch(PDO::FETCH_ASSOC);

                                $previsao_terminal_others121 = $pdo->prepare('SELECT sum(r.qpax + r.qchild + r.qfree ) as total FROM `ct_recentlyadd` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservice = :servico and r.idschedule = :horario');
                                $previsao_terminal_others121->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 8));
                                $data_previsao_terminal_others121 = $previsao_terminal_others121->fetch(PDO::FETCH_ASSOC);

                                $previsao_terminal_others151 = $pdo->prepare('SELECT sum(r.qpax + r.qchild + r.qfree ) as total FROM `ct_recentlyadd` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservice = :servico and r.idschedule = :horario');
                                $previsao_terminal_others151->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 10));
                                $data_previsao_terminal_others151 = $previsao_terminal_others151->fetch(PDO::FETCH_ASSOC);

                                $previsao_terminal_others171 = $pdo->prepare('SELECT sum(r.qpax + r.qchild + r.qfree ) as total FROM `ct_recentlyadd` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservice = :servico and r.idschedule = :horario');
                                $previsao_terminal_others171->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 13));
                                $data_previsao_terminal_others171 = $previsao_terminal_others171->fetch(PDO::FETCH_ASSOC);

                                ?>
                                <?php if($data_previsao_terminal_others['total'] <> null or $data_previsao_terminal_others1['total'] <> null){ ?>
                                    <tr>
                                        <td>07:30</td>
                                        <td><?php echo(strtoupper($item->fullname)); ?></td>
                                        <td><?php echo($data_previsao_terminal_others['total'] + $data_previsao_terminal_others1['total'] ); ?></td>
                                    </tr>
                                <?php }?>
                                <?php if($data_previsao_terminal_others10['total'] <> null or ($data_previsao_terminal_others101['total'] <> null)){ ?>
                                    <tr>
                                        <td>10:30</td>
                                        <td><?php echo(strtoupper($item->fullname)); ?></td>
                                        <td><?php echo($data_previsao_terminal_others10['total'] + $data_previsao_terminal_others101['total'] ); ?></td>
                                    </tr>
                                <?php }?>
                                <?php if($data_previsao_terminal_others12['total'] <> null or $data_previsao_terminal_others121['total'] <> null){ ?>
                                    <tr>
                                        <td>12:30</td>
                                        <td><?php echo(strtoupper($item->fullname)); ?></td>
                                        <td><?php echo($data_previsao_terminal_others12['total'] + $data_previsao_terminal_others121['total'] ); ?></td>
                                    </tr>
                                <?php }?>
                                <?php if($data_previsao_terminal_others15['total'] <> null or $data_previsao_terminal_others151['total'] <> null){ ?>
                                    <tr>
                                        <td>15:00</td>
                                        <td><?php echo(strtoupper($item->fullname)); ?></td>
                                        <td><?php echo($data_previsao_terminal_others15['total'] + $data_previsao_terminal_others151['total']); ?></td>
                                    </tr>
                                <?php }?>
                                <?php if($data_previsao_terminal_others17['total'] <> null or $data_previsao_terminal_others171['total'] <> null){ ?>
                                    <tr>
                                        <td>17:00</td>
                                        <td><?php echo(strtoupper($item->fullname)); ?></td>
                                        <td><?php echo($data_previsao_terminal_others17['total'] + $data_previsao_terminal_others171['total'] ); ?></td>
                                    </tr>
                                <?php }?>
                            <?php }?>

                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-4 pull-right">
                        <h5 align="center">MORRO DE SÃO PAULO</h5>
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th>Horário</th>
                                <th>Serviço</th>
                                <th>Total</th>
                            </tr>

                            </thead>
                            <tbody>
                            <?php foreach ($data_previsao_tree as $item){
                                $previsao_morro = $pdo->prepare('SELECT sum(r.qtdpax + r.qtdchild + r.qtdfree ) as total FROM `ct_reserva` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservico = :servico and r.idhorario = :horario');
                                $previsao_morro->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 2));
                                $data_previsao_morro = $previsao_morro->fetch(PDO::FETCH_ASSOC);

                                $previsao_morro9= $pdo->prepare('SELECT sum(r.qtdpax + r.qtdchild + r.qtdfree ) as total FROM `ct_reserva` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservico = :servico and r.idhorario = :horario');
                                $previsao_morro9->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 4));
                                $data_previsao_morro9 = $previsao_morro9->fetch(PDO::FETCH_ASSOC);

                                $previsao_morror11 = $pdo->prepare('SELECT sum(r.qtdpax + r.qtdchild + r.qtdfree ) as total FROM `ct_reserva` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservico = :servico and r.idhorario = :horario');
                                $previsao_morror11->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 6));
                                $data_previsao_morro11 = $previsao_morror11->fetch(PDO::FETCH_ASSOC);

                                $previsao_morro13 = $pdo->prepare('SELECT sum(r.qtdpax + r.qtdchild + r.qtdfree ) as total FROM `ct_reserva` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservico = :servico and r.idhorario = :horario');
                                $previsao_morro13->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 9));
                                $data_previsao_morro13 = $previsao_morro13->fetch(PDO::FETCH_ASSOC);

                                $previsao_morro16 = $pdo->prepare('SELECT sum(r.qtdpax + r.qtdchild + r.qtdfree ) as total FROM `ct_reserva` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservico = :servico and r.idhorario = :horario');
                                $previsao_morro16->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 11));
                                $data_previsao_morro16 = $previsao_morro16->fetch(PDO::FETCH_ASSOC);


                                $previsao_morro1 = $pdo->prepare('SELECT sum(r.qpax + r.qchild + r.qfree ) as total FROM `ct_recentlyadd` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservice = :servico and r.idschedule = :horario');
                                $previsao_morro1->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 2));
                                $data_previsao_morro1 = $previsao_morro1->fetch(PDO::FETCH_ASSOC);

                                $previsao_morro9 = $pdo->prepare('SELECT sum(r.qpax + r.qchild + r.qfree ) as total FROM `ct_recentlyadd` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservice = :servico and r.idschedule = :horario');
                                $previsao_morro9->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 4));
                                $data_previsao_morro91 = $previsao_morro9->fetch(PDO::FETCH_ASSOC);

                                $previsao_morror11 = $pdo->prepare('SELECT sum(r.qpax + r.qchild + r.qfree ) as total FROM `ct_recentlyadd` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservice = :servico and r.idschedule = :horario');
                                $previsao_morror11->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 6));
                                $data_previsao_morro111 = $previsao_morror11->fetch(PDO::FETCH_ASSOC);

                                $previsao_morro131 = $pdo->prepare('SELECT sum(r.qpax + r.qchild + r.qfree ) as total FROM `ct_recentlyadd` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservice = :servico and r.idschedule = :horario');
                                $previsao_morro131->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 9));
                                $data_previsao_morro131 = $previsao_morro131->fetch(PDO::FETCH_ASSOC);

                                $previsao_morro161 = $pdo->prepare('SELECT sum(r.qpax + r.qchild + r.qfree ) as total FROM `ct_recentlyadd` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservice = :servico and r.idschedule = :horario');
                                $previsao_morro161->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 11));
                                $data_previsao_morro161 = $previsao_morro161->fetch(PDO::FETCH_ASSOC);

                                ?>
                                <?php if($data_previsao_morro['total'] <> null or $data_previsao_morro1['total'] <> null){ ?>
                                    <tr>
                                        <td>06:30</td>
                                        <td><?php echo(strtoupper($item->fullname)); ?></td>
                                        <td><?php echo($data_previsao_morro['total'] + $data_previsao_morro1['total']); ?></td>
                                    </tr>
                                <?php }?>
                                <?php if($data_previsao_morro9['total'] <> null or $data_previsao_morro91['total'] <> null){ ?>
                                    <tr>
                                        <td>08:30</td>
                                        <td><?php echo(strtoupper($item->fullname)); ?></td>
                                        <td><?php echo($data_previsao_morro9['total'] + $data_previsao_morro91['total']); ?></td>
                                    </tr>
                                <?php }?>
                                <?php if($data_previsao_morro111['total'] <> null or $data_previsao_morro11['total'] <> null){ ?>
                                    <tr>
                                        <td>10:30</td>
                                        <td><?php echo(strtoupper($item->fullname)); ?></td>
                                        <td><?php echo($data_previsao_morro111['total'] + $data_previsao_morro11['total']); ?></td>
                                    </tr>
                                <?php }?>
                                <?php if($data_previsao_morro131['total'] <> null or $data_previsao_morro13['total'] <> null){ ?>
                                    <tr>
                                        <td>13:30</td>
                                        <td><?php echo(strtoupper($item->fullname)); ?></td>
                                        <td><?php echo($data_previsao_morro13['total'] + $data_previsao_morro131['total']); ?></td>
                                    </tr>
                                <?php }?>
                                <?php if($data_previsao_morro161['total'] <> null or $data_previsao_morro16['total'] <> null){ ?>
                                    <tr>
                                        <td>15:30</td>
                                        <td><?php echo(strtoupper($item->fullname)); ?></td>
                                        <td><?php echo($data_previsao_morro16['total'] +$data_previsao_morro161['total'] ); ?></td>
                                    </tr>
                                <?php }?>
                            <?php }?>

                            </tbody>
                        </table>
                    </div>
                    <br>
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
                                        <th>Responsável</th>
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
                                                or $item->idservico == 168 or $item->idservico == 169 or $item->idservico == 228 or $item->idservico == 21  or $item->idservico == 39 or $item->idservico == 54 or $item->idservico == 56 or $item->idservico == 158 or $item->idservico == 223 or $item->idservico == 40
                                                or $item->idservico >= 59  and  $item->idservico <= 64 or $item->idservico >= 146 and  $item->idservico <= 147){ ?>
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

                                                    <?php if($item->totalservico-$item->totalcredito > 0 and $item->dateinput == date("Y-m-d", time()-(3600*27))){ ?>
                                                        <td class="bg-danger" style="color: white;"><?php echo("R$ ".number_format($item->totalservico-$item->totalcredito,2,",",".") ); ?></td>
                                                    <?php } else {?>
                                                        <td><?php echo("R$ ".number_format($item->totalservico-$item->totalcredito,2,",",".") ); ?></td>
                                                    <?php }?>

                                                    <td><?php echo( strtoupper(utf8_encode( $item->firstname." ".$item->lastname) )); ?></td>
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
                                                    <?php if($item->totalservico-$item->totalcredito > 0 and $item->dateinput == date("Y-m-d", time()-(3600*27))){ ?>
                                                        <td class="bg-danger" style="color: white;"><?php echo("R$ ".number_format($item->totalservico-$item->totalcredito,2,",",".") ); ?></td>
                                                    <?php } else {?>
                                                        <td><?php echo("R$ ".number_format($item->totalservico-$item->totalcredito,2,",",".") ); ?></td>
                                                    <?php }?>
                                                    <td><?php echo( strtoupper(utf8_encode( $item->firstname." ".$item->lastname) )); ?></td>
                                                </tr>
                                            <?php }?>

                                        <?php } ?>
                                        <?php if( $contador > 0 ){ ?>
                                            <?php foreach ($registro as $item2) {$totalPax2 += $item2->qpax; $totalchil2 += $item2->qchild; $totalfree2 += $item2->qfree; ?>
                                                <?php if( $item2->idservice >= 109 and  $item2->idservice <= 120 or $item2->idservice == 111 or $item2->idservice == 42 or $item2->idservice == 108 or $item2->idservice == 120 or $item2->idservice == 124 or $item2->idservice == 133 or $item2->idservice == 156 or $item2->idservice == 157
                                                    or $item2->idservice == 224  or $item2->idservice == 239 or $item2->idservice == 139 or $item2->idservice == 130 or $item2->idservice == 131 or $item2->idservice == 137 or $item2->idservice == 104 or $item2->idservice == 107 or $item2->idservice == 148 or $item2->idservice == 40
                                                    or $item2->idservice == 233  or $item2->idservice == 168 or $item2->idservice == 169 or $item2->idservice == 228 or $item2->idservice == 21  or $item2->idservice == 39 or $item2->idservice == 54 or $item2->idservice == 56 or $item2->idservice == 158 or $item2->idservice == 223
                                                    or $item2->idservice >= 59  and  $item2->idservice <= 64 or $item2->idservice >= 146 and  $item2->idservice <= 147){ ?>
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
                                                        <?php if($item2->totalservico-$item2->totalcredito > 0 and $item2->ap == date("Y-m-d", time()-(3600*27))){ ?>
                                                            <td class="bg-danger" style="color: white;"><?php echo("R$ ".number_format($item2->totalservico-$item2->totalcredito,2,",",".") ); ?></td>
                                                        <?php } else {?>
                                                            <td><?php echo("R$ ".number_format($item2->totalservico-$item2->totalcredito,2,",",".") ); ?></td>
                                                        <?php }?>
                                                        <td><?php echo( strtoupper(utf8_encode( $item2->firstname." ".$item2->lastname) )); ?></td>
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
                                                        <?php if($item2->totalservico-$item2->totalcredito > 0 and $item2->ap == date("Y-m-d", time()-(3600*27))){ ?>
                                                            <td class="bg-danger" style="color: white;"><?php echo("R$ ".number_format($item2->totalservico-$item2->totalcredito,2,",",".") ); ?></td>
                                                        <?php } else {?>
                                                            <td><?php echo("R$ ".number_format($item2->totalservico-$item2->totalcredito,2,",",".") ); ?></td>
                                                        <?php }?>
                                                        <td><?php echo( strtoupper(utf8_encode( $item2->firstname." ".$item2->lastname) )); ?></td>
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
