<?php require_once ('header.php');
$numberVoucher = $_POST['voucher'];
$dadosReserva  = $pdo->prepare("select * from `ct_audit` where `voucher` = :numberVoucher ");
$dadosReserva->execute( array(":numberVoucher" => $numberVoucher ) );
$registro      =  $dadosReserva->fetchAll(PDO::FETCH_CLASS);
$contadorAuditoria = $dadosReserva->rowCount();

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
                                    <a href="index">Home</a>
                                </li>
                                <li class="list-inline-item seprate">
                                    <span>/</span>
                                </li>
                                <li class="list-inline-item">PAX: Auditoria pax</li>
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
            <div class="table-responsivo">
                <table class="table table-bordered">
                    <thead>
                    <th scope="row">Data</th>
                    <th scope="row">Descrição</th>
                    <th scope="row">Responsável</th>
                    </thead>
                    <tbody>
                    <?php foreach ($registro as $item) {
                        $buscarResponsavel = $pdo->prepare('select * from `ct_usuario` where `id` = :id');
                        $buscarResponsavel->execute(array(":id" => $item->idresponsible));
                        $dados = $buscarResponsavel->fetch(PDO::FETCH_ASSOC);
                        $timestamp = strtotime($item->date);
                        ?>
                        <tr>
                            <td><?php echo( strftime("%A,  %d de %B de %Y às %r", $timestamp) ); ?></td>
                            <td><?php echo( utf8_encode( $item->description)." (". $dados['firstname']." ".$dados['lastname'].")"); ?></td>
                            <td><?php echo( $dados['firstname']." ".$dados['lastname']); ?></td>
                        </tr>
                    <?php }?>
                    </tbody>
                </table>
            </div>
            <div class="pull-left">
                <a href="./map-visualizar-servico" class="btn btn-warning">
                    Voltar para o mapa
                </a>
            </div>
            <div class="pull-right">
                <form action="./editar-pax" method="post">
                    <input type="hidden" value="<?php echo($numberVoucher); ?>" name="numbervoucher">
                    <button type="submit" class="btn btn-primary">
                        Continuar Editando
                    </button>
                </form>
            </div>
        </div>
    </div>

    <?php require_once ('footer.php'); ?>
