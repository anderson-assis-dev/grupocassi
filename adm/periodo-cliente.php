<?php require_once ('header.php');

if( isset( $_POST['registrar'] ) )
{
    $inicial = $_POST['inicial'];
    $final   = $_POST['final'];

    $updatePeriodo = $pdo->prepare("update `ct_cliente` c set c.periodoinicial = :inicial, c.periodofinal = :final  ");
    $updatePeriodo->execute(
        array(
            ":inicial" => $inicial, ":final" => $final
        )
    );
    echo("<div class='alert alert-success' role='alertdialog'>Periodo Cadastrado para todos os cliente</div>");
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
                                <li class="list-inline-item">Cliente: Periodo de Reservas(Clientes)</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <h4>Cadastrar Periodo de reservas para os clientes </h4>
                </div>
            </div>
            <div class="card-body">
                <form action="" method="post">
                    <div class="col-md-4 pull-left">
                        <label><strong>Período Inicial</strong></label>
                        <input class="form-control" name="inicial" type="date" value="<?php echo(date("Y-m-d")) ?>" >
                    </div>
                    <div class="col-md-4 pull-left">
                        <label><strong>Período Final</strong></label>
                        <input class="form-control" name="final" type="date" value="<?php echo(date("Y-m-d")) ?>" >
                    </div>
                    <div class="col-md-4 pull-right">
                        <button style="margin-top: 35px;" class="btn btn-large btn-success btn-block" name="registrar" type="submit">
                            Registrar Período
                        </button>
                    </div>
                </form>

            </div>

        </div>

    </div>
    <?php require_once ('footer.php'); ?>
