<?php require_once ('header.php');
header('Content-Type: text/html; charset=utf-8');

if( isset($_POST['auditoria']) )
{
    $numberVoucher = $_POST['voucher'];
    $dadosReserva  = $pdo->prepare("select * from `ct_audit` where `voucher` = :numberVoucher ");
    $dadosReserva->execute( array(":numberVoucher" => $numberVoucher ) );
    $registro      =  $dadosReserva->fetchAll(PDO::FETCH_CLASS);
    $contadorAuditoria = $dadosReserva->rowCount();
}

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
                                    <a href="./index">Home</a>
                                </li>
                                <li class="list-inline-item seprate">
                                    <span>/</span>
                                </li>
                                <li class="list-inline-item">Reserva: Auditoria</li>
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
                <div class="card-body">
                    <h3>Auditoria</h3>
                    <hr>
                    <div class="col-lg-12">
                        <h4>Informar número do Voucher</h4>
                        <hr>
                        <form method="post" action="">
                            <div class="form-group">
                                <label for="nomecliente">Numero do voucher <strong>(somente números):</strong></label>
                                <input type="text" name="voucher" id="voucher" class="form-control" >
                                <input type="hidden" name="idres" value="<?php echo($_SESSION['id']); ?>" >
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary btn-lg btn-block" name="auditoria" >Buscar</button>
                            </div>
                        </form>
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
                                    <?php foreach ($registro as $item) {
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

                </div>

            </div>

        </div>
    </div>
    <?php require_once ('footer.php'); ?>
