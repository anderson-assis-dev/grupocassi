<?php
/**
 * Created by PhpStorm.
 * User: Ander
 * Date: 01/04/2019
 * Time: 07:38
 */
?>
<?php require_once ('header.php');
$pendencias = $pdo->prepare(
    'select * from `ct_reserva` r left join ct_statusinvoice ct on r.idstatusinvoice = ct.id 
              where idresponsavel = :id and idstatusinvoice <> :paramone and idstatusinvoice <> :paramtwo and idstatusinvoice <> :paramtree ');
$pendencias->execute(
    array(":id" => $_SESSION['id'], ":paramone" => 3, ":paramtwo" => 2, ":paramtree" => 8 )
);
$registro = $pendencias->fetchAll(PDO::FETCH_CLASS);
?>
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
                                <li class="list-inline-item">Reserva: Pendências</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <div class="row">
        <div class="container">
            <div id="status" class="pull-left col-md-12"></div>
            <div class="col-lg-12">

                <h4><?php echo("Total de Reservas com Pendências Financeira ". count( $registro )); ?>: </h4>
                <hr>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Abertura</th>
                                <th>Voucher</th>
                                <th>Situação</th>
                                <th>#</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $registro as $item ) {?>
                                <tr>
                                    <td><?php echo( date( "d-m-Y", strtotime($item->abertura) ) ); ?></td>
                                    <td><?php echo( $item->numbervoucher ); ?></td></td>
                                    <td><?php echo( $item->nameinvoice ); ?></td>
                                    <td>
                                        <a target="_blank" href="<?php echo("editar-pax?numbervoucher=".$item->numbervoucher) ?>">
                                            Acessar Voucher
                                        </a>
                                    </td>
                                </tr>
                            <?php }?>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php require_once ('footer.php'); ?>

