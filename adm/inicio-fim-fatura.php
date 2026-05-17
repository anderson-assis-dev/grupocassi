<?php require_once ('header.php');
$status = $pdo->prepare('select * from `ct_statusinvoice`');
$status->execute();
$todosStatus = $status->fetchAll(PDO::FETCH_CLASS);
if( isset( $_POST['todosVoucher'] ) )
{
    $datainicio      = $_POST['inicio'];
    $datafinal       = $_POST['fim'];
    $idcliente       = $_POST['idcliente'];
    $total           = $_POST['total'];
    $credito         = $_POST['credito'];
    $statusEscolhido = $_POST['statusatual'];
    $contador = 0;


}
if( isset($_POST['salvar']) )
{
    $perirodoInicial    = $_POST['periodoinicial'];
    $perirodoFinal      = $_POST['periodofinal'];
    $clienteSelecionado = $_POST['cliente'];
    $statusAtual        = $_POST['status'] ;
    $statusNovo         = $_POST['statusescolhido'];
    $dataVencimento     = $_POST['datavencimento'];
    $credito            = $_POST['credito'];
    $total              = $_POST['total'];


    $salvarDados = $pdo->prepare(
        'insert into `ct_fatura` (`id`, `idcliente`, `datavencimento` ,`tarifa`, `credito`, `dateinput`, `dateoutput`, `situacao`, `status`)
                   values (DEFAULT, :cliente, :datavencimento ,:tarifa, :credito, :inicio,:fim , :situacao, :statuss)');
    $salvarDados->execute(
        array(
            ":cliente"        => $clienteSelecionado,
            ":datavencimento" => $dataVencimento,
            ":tarifa"         => $total ,
            ":credito"        => $credito,
            ":inicio"         => $perirodoInicial,
            ":fim"            => $perirodoFinal,
            ":situacao"       => 1,
            ":statuss"        => 1
        )
    );
    $numberFatura = $pdo->lastInsertId();
    $recentlyUpdateNumberFat = $pdo->prepare(
        'update `ct_reserva` set numberfatura = :novo where idcliente = :cliente and dateinput >= :inicio and dateinput <= :fim ');
    $recentlyUpdateNumberFat->execute( array(":novo" => $numberFatura, ":cliente" => $clienteSelecionado, ":inicio" => $perirodoInicial, ":fim" => $perirodoFinal) );
    echo('<div class="alert alert-success" role="alert">Fatura Cadastrada.</div>');
    $contador = 1;
}

?>
<style>
    .col-md-6{
        margin-bottom: 20px;
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
                                    <a href="./index">Home</a>
                                </li>
                                <li class="list-inline-item seprate">
                                    <span>/</span>
                                </li>
                                <li class="list-inline-item">Financeiro: Fatura - Finalizar Cadastro</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <div class="row">
        <div id="status"></div>
        <div class="container">
          <?php if( $contador == 0 ){ ?>
            <div class="col-lg-12">
                <h4>Atualizar Informações da Fatura:</h4>
                <hr>
                <form action="" method="post">
                    <div class="col-md-6 pull-left">
                        <strong><label for="statusescolhido">Status</label></strong>
                        <select class="form-control" name="statusescolhido" id="statusescolhido" required>
                            <?php foreach ($todosStatus as $item4){ ?>
                                <option  value="<?php echo($item4->id); ?>">
                                    <?php echo( utf8_encode( $item4->nameinvoice)); ?>
                                </option>
                            <?php }?>
                        </select>
                    </div>
                    <div class="col-md-6 pull-right">
                        <strong><label for="datavencimento">Data Vencimento</label></strong>
                        <input type="date" class="form-control" name="datavencimento" id="datavencimento" >
                    </div>

                    <input type="hidden" name="cliente" value="<?php echo( $idcliente ); ?>" >
                    <input type="hidden" name="periodoinicial" value="<?php echo( $datainicio ); ?>" >
                    <input type="hidden" name="periodofinal" value="<?php echo( $datafinal ); ?>" >
                    <input type="hidden" name="total" value="<?php echo( $total ); ?>" >
                    <input type="hidden" name="status" value="<?php echo( $statusEscolhido ); ?>" >
                    <input type="hidden" name="credito" value="<?php echo( $credito ); ?>" >
                    <div class="col-md-6 pull-left">
                        <button style="margin-bottom: 20px;" type="submit" name="salvar" class="btn btn-primary btn-lg" id="salvar">
                            Salvar informações
                        </button>
                    </div>

                </form>
                <div class="col-md-6 pull-right">
                    <form action="./inicio-da-fatura.php" method="post">
                        <?php if( count($_SESSION['status']) > 0 ){ ?>
                            <input type="hidden" name="periodoinicial" value="<?php echo( $_SESSION['periodoinicial'] ); ?>">
                            <input type="hidden" name="periodofinal"    value="<?php echo( $_SESSION['periodofinal'] ); ?>">
                            <input type="hidden" name="cliente"    value="<?php echo( $_SESSION['cliente'] ); ?>">
                            <select style="display: none;" class="form-control" name="status[]" multiple id="status">
                                <?php foreach ($todosStatus as $item4){ ?>
                                    <?php for ($i = 0; $i <= count( $_SESSION['status'] ); $i ++){ ?>
                                        <?php if( $item4->id == $_SESSION['status'][$i] ){ ?>
                                            <option selected value="<?php echo($item4->id); ?>">
                                                <?php echo( utf8_encode( $item4->nameinvoice)); ?>
                                            </option>
                                        <?php }?>
                                    <?php }?>
                                <?php }?>
                            </select>
                        <?php } else{ ?>
                            <input type="hidden" name="periodoinicial" value="<?php echo( $_SESSION['periodoinicial'] ); ?>">
                            <input type="hidden" name="periodofinal"    value="<?php echo( $_SESSION['periodofinal'] ); ?>">
                            <input type="hidden" name="cliente"    value="<?php echo( $_SESSION['cliente'] ); ?>">
                            <select style="display: none;" class="form-control" name="status[]" multiple id="status">
                                <option selected value="0">
                                    todos
                                </option>
                            </select>
                        <?php }?>
                        <button type="submit" style="margin-bottom: 20px;" name="pesquisarfatura" id="pesquisarfatura" class="btn btn-warning btn-lg pull-right">Voltar</button>
                    </form>
                </div>

                <h4>Resumo Financeiro:</h4>
                <hr>
                <p>Total : <?php echo("R$ ".number_format($total,2,",",".") );  ?></p>
                <p>Credito : <?php echo("R$ ".number_format($credito,2,",",".") );  ?></p>
            </div>
          <?php } else{ ?>
            <div class="col-md-12">
                <div class="alert alert-success" role="alert">
                    <form action="./relatorio/pdf-relatorio-cliente-reserva.php" method="post" target="_blank">
                      <input type="hidden" name="cliente" value="<?php echo( $clienteSelecionado ); ?>" >
                      <input type="hidden" name="periodoinicial" value="<?php echo( $perirodoInicial ); ?>" >
                      <input type="hidden" name="periodofinal" value="<?php echo( $perirodoFinal ); ?>" >
                      <input type="hidden" name="tarifa" value="<?php echo( $total ); ?>" >
                      <input type="hidden" name="status" value="<?php echo( $statusNovo ); ?>" >
                      <button style="backgroud:transparent;border:none;color:black;" type="submit">Visualizar Fatura Cadastrada</button>
                    </form>
                </div>
            </div>
          <?php }?>
        </div>
    </div>
    <?php require_once ('footer.php'); ?>
