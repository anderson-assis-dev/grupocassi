<?php
require_once('header.php');

$totalReservaConsolidada = $pdo->prepare("select * from `ct_reserva` where `dateinput` >= :inicio and `dateinput` <= :fim and `idstatus` = :id ");
$totalReservaConsolidada->execute(array(":inicio" => date('Y-m-01'), ":fim" => date('Y-m-31'), ":id" => 1));

$totalReservaConsolidadaAdd = $pdo->prepare("select * from `ct_recentlyadd` where `dateinput` >= :inicio and `dateinput` <= :fim ");
$totalReservaConsolidadaAdd->execute(array(":inicio" => date('Y-m-01'), ":fim" => date('Y-m-31')));

$contador = ($totalReservaConsolidada->rowCount() + $totalReservaConsolidadaAdd->rowCount());
$registro = $totalReservaConsolidada->fetchAll(PDO::FETCH_CLASS);

$totalReservaCancelada = $pdo->prepare("select * from `ct_reserva` where `dateinput` >= :inicio and `dateinput` <= :fim and `idstatus` = :id ");
$totalReservaCancelada->execute(array(":inicio" => date('Y-m-01'), ":fim" => date('Y-m-31'), ":id" => 2));
$contadorCancelado = $totalReservaCancelada->rowCount();

$timestamp = strtotime(date('Y-m-d'));
?>

<!DOCTYPE html>
<html>

<head>
    <title>Minha Página</title>
    <style>
        body {
            background-color: #f5f5f5;
        }
    </style>
</head>

<body>
    <!-- PAGE CONTENT-->
    <div class="page-content--bgf7" style="background-color: #f5f5f5;">
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
                                        <a href="#">Home</a>
                                    </li>
                                    <li class="list-inline-item seprate">
                                        <span>/</span>
                                    </li>
                                    <li class="list-inline-item">Dashboard</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- END BREADCRUMB-->

        <!-- WELCOME-->
        <section class="welcome p-t-10">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <h1 class="title-4">Olá
                            <span><?php echo (strtoupper($_SESSION['nome'])); ?>!</span>
                        </h1>
                        <p>Boas-vindas à Cassi Turismo.</p>
                    </div>
                </div>
            </div>
        </section>
        <!-- END WELCOME-->

        <!-- STATISTIC-->
        <section class="statistic statistic2">
            <div class="container">
                <div class="row">
                    <div class="col-md-6 pull-right ">
                        <div class="statistic__item statistic__item--green">
                            <h2 class="number"><?php echo ($contador); ?></h2>
                            <span class="desc">RESERVAS DE <?php echo (strftime('%B', $timestamp)); ?></span>
                            <div class="icon">
                                <i class="zmdi zmdi-account-o"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 pull-left">
                        <div class="statistic__item statistic__item--red">
                            <h2 class="number"><?php echo ($contadorCancelado); ?></h2>
                            <span class="desc">RESERVAS CANCELADAS DE <?php echo (strftime('%B', $timestamp)); ?></span>
                            <div class="icon">
                                <i class="zmdi zmdi-shopping-cart"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <?php require_once('footer.php'); ?>
</body>

</html>
