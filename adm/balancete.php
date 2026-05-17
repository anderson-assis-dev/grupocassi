<?php require_once ('header.php');



if( isset( $_POST['buscarbalencete'] ) )
{
    $buscarContasCorrente = $pdo->prepare(
        'select idconta, cc.name from  `ct_caixa` c left join ct_currentaccount cc on cc.id = c.idconta
              where c.dataabertura >= :inicio and c.dataabertura <= :fim  group by cc.name');
    $buscarContasCorrente->execute(
        array(":inicio" => date("Y-m-d",
            strtotime($_POST['vencimentoinicial'])), ":fim" => date("Y-m-d", strtotime($_POST['vencimentofinal']))) );

    $inicio = date("d/m/Y", strtotime($_POST['vencimentoinicial']));
    $fim    = date("d/m/Y", strtotime($_POST['vencimentofinal']));

    $inicio_convertida = date("Y-m-d", strtotime($_POST['vencimentoinicial']));
    $fim_convertida    = date("Y-m-d", strtotime($_POST['vencimentofinal']));

}else{
    $buscarContasCorrente = $pdo->prepare(
        'select idconta, cc.name from  `ct_caixa` c left join ct_currentaccount cc on cc.id = c.idconta
              where c.dataabertura >= :inicio and c.dataabertura <= :fim  group by cc.name');
    $buscarContasCorrente->execute( array(":inicio" => date("Y-m-01"), ":fim" => date("Y-m-31")) );
    $inicio = date("01/m/Y");
    $fim    = date("31/m/Y");

    $inicio_convertida = date("Y-m-01");
    $fim_convertida    = date("Y-m-31");

}
$registro = $buscarContasCorrente->fetchAll(PDO::FETCH_CLASS);
$total_geral = 0;
$total_conferido_geral = 0;
$total_a_conferir_geral = 0;
$total_geral_debito = 0;

?>
<style>
    .col-lg-6, .col-md-4, button{
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
                                    <a href="./index.php">Home</a>
                                </li>
                                <li class="list-inline-item seprate">
                                    <span>/</span>
                                </li>
                                <li class="list-inline-item">Caixa: Balancete</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <div class="">
        <div class="">
            <div class="col-lg-12">
                <div class="accordion" id="accordionExample">
                    <div class="card">
                        <div class="card-header" id="headingOne">
                            <h2 class="mb-0">
                                <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                    Buscar Balancete
                                </button>
                            </h2>
                        </div>

                        <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionExample">
                            <div class="card-body">
                                <form action="" method="post">
                                    <div class="col-lg-6 pull-left">
                                        <strong><label for="vencimentoinicial">Data de  Vencimento Inicial</label></strong>
                                        <input type="date" class="form-control" name="vencimentoinicial" id="vencimentoinicial">
                                    </div>
                                    <div class="col-lg-6 pull-right">
                                        <strong><label for="vencimentofinal">Data de  Vencimento Final</label></strong>
                                        <input type="date" class="form-control" name="vencimentofinal" id="vencimentofinal">
                                    </div>
                                    <div class="container-fluid">
                                        <button class="btn btn-outline-success btn-large btn-block" type="submit" name="buscarbalencete">Buscar Balancete</button>
                                    </div>

                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header" id="headingTwo">
                            <h2 class="mb-0">
                                <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="true"
                                        aria-controls="collapseTwo">
                                    Dados
                                </button>
                            </h2>
                        </div>
                        <div id="collapseTwo" class="collapse show" aria-labelledby="headingTwo" data-parent="#accordionExample">
                            <div class="card-body" id="conteudo">
                                <?php if( count($registro) > 0 ){ ?>
                                    <div class="table-responsive">
                                        <h4>Balancete <?php echo(" De: ".$inicio." Até ".$fim); ?> </h4>
                                        <hr>
                                        <table class="table table-bordered">
                                            <thead>
                                            <tr>
                                                <th>Conta</th>
                                                <th>Total</th>
                                                <th>Total de Crédito Conferido</th>
                                                <th>Total de Crédito À Conferir</th>
                                                <th>Total Débito</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php foreach ($registro as $item) {
                                                $totalConta = $pdo->prepare(
                                                    "select sum(valor) as total from  `ct_caixa` where idconta = :contacorrente and dataabertura >= :inicio 
                                                                and dataabertura <= :fim ");
                                                $totalConta->execute( array(":inicio" => $inicio_convertida, ":fim" => $fim_convertida, ":contacorrente" => $item->idconta ) );
                                                $dadosTotalConta = $totalConta->fetch(PDO::FETCH_ASSOC);

                                                $credito_conferido = $pdo->prepare(
                                                    "select sum(valor) as total from  `ct_caixa` where idconta = :contacorrente and dataabertura >= :inicio 
                                                               and idstatus = :situacao and dataabertura <= :fim and idtipo = :tipo");
                                                $credito_conferido->execute(
                                                    array(":inicio" => $inicio_convertida, ":fim" => $fim_convertida, ":contacorrente" => $item->idconta,
                                                        ":situacao" => 3, ":tipo" => 1 ) );
                                                $dados_total_credito_conferido = $credito_conferido->fetch(PDO::FETCH_ASSOC);

                                                $credito_a_conferir = $pdo->prepare(
                                                    "select sum(valor) as total from  `ct_caixa` where idconta = :contacorrente and dataabertura >= :inicio 
                                                               and idstatus <> :situacao and dataabertura <= :fim and idtipo = :tipo");
                                                $credito_a_conferir->execute(
                                                    array(":inicio" => $inicio_convertida, ":fim" => $fim_convertida, ":contacorrente" => $item->idconta,
                                                        ":situacao" => 3, ":tipo" => 1 ) );
                                                $dados_total_credito_a_conferir = $credito_a_conferir->fetch(PDO::FETCH_ASSOC);

                                                $debito = $pdo->prepare(
                                                    "select sum(valor) as total from  `ct_caixa` where idconta = :contacorrente and dataabertura >= :inicio 
                                                                and idtipo = :situacao and dataabertura <= :fim ");
                                                $debito->execute(
                                                    array(":inicio" => $inicio_convertida, ":fim" => $fim_convertida, ":contacorrente" => $item->idconta, ":situacao" => 2 ) );
                                                $dados_total_debito = $debito->fetch(PDO::FETCH_ASSOC);

                                                $total_geral            += $dadosTotalConta['total'];
                                                $total_conferido_geral  += $dados_total_credito_conferido['total'];
                                                $total_a_conferir_geral += $dados_total_credito_a_conferir['total'];
                                                $total_geral_debito     += $dados_total_debito['total'];
                                                ?>
                                                <tr>
                                                    <td><?php echo( utf8_decode( strtoupper( $item->name ) ) ); ?></td>
                                                    <td><?php echo("R$ ".number_format( $dadosTotalConta['total'], 2,",", "." ) ); ?></td>
                                                    <td><?php echo("R$ ".number_format( $dados_total_credito_conferido['total'], 2,",", "." ) ); ?></td>
                                                    <td><?php echo("R$ ".number_format( $dados_total_credito_a_conferir['total'], 2,",", "." ) ); ?></td>
                                                    <td><?php echo("R$ ".number_format( $dados_total_debito['total'], 2,",", "." ) ); ?></td>
                                                </tr>
                                            <?php }?>
                                            </tbody>
                                        </table>
                                        <table class="table table-bordered">
                                            <thead>
                                            <tr>
                                                <th>Total Geral</th>
                                                <th>Total Crédito Conferido Geral</th>
                                                <th>Total Crédito À Conferir Geral</th>
                                                <th>Total Débito Geral</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td><?php echo("R$ ".number_format( $total_geral, 2,".", "." ) ); ?></td>
                                                <td><?php echo("R$ ".number_format( $total_conferido_geral, 2,".", "." ) ); ?></td>
                                                <td><?php echo("R$ ".number_format( $total_a_conferir_geral, 2,".", "." ) ); ?></td>
                                                <td><?php echo("R$ ".number_format( $total_geral_debito, 2,".", "." ) ); ?></td>
                                            </tr>
                                            </tbody>
                                        </table>

                                    </div>
                                    <button class="btn btn-outline-primary btn-block" onclick="cont();">Imprimir</button>
                                <?php } else{ ?>
                                    <div class="alert alert-warning" role="alertdialog" >Não encontramos registros</div>
                                <?php }?>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>
<script>
    function cont(){
        var conteudo = document.getElementById('conteudo').innerHTML;
        tela_impressao = window.open('about:blank');
        tela_impressao.document.write(conteudo);
        tela_impressao.window.print();
        tela_impressao.window.close();
    }
</script>
<?php require_once ('footer.php'); ?>
