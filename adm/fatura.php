<?php require_once ('header.php');

$cliente = $pdo->prepare('select * from `ct_cliente` ORDER BY `ct_cliente`.`corporatename` DESC');
$cliente->execute();
$status = $pdo->prepare('select * from `ct_form_of_ payment`');
$status->execute();

$valorGeral  = 0;
$totalBruto1 = 0;
$totalBruto2 = 0;
$tarifa1     = 0;
$tarifa2     = 0;
if( isset( $_POST['pesquisarfatura'] ) )
{
    $datainicio = $_POST['periodoinicial'];
    $datafinal  = $_POST['periodofinal'];
    $idcliente  = $_POST['cliente'];
    $ststus     = $_POST['status'];
    if( $datainicio > 0  and $datafinal > 0 and $idcliente > 0 )
    {
        $reservasPeriodo = $pdo->prepare(
            'SELECT numbervoucher, pax, s.fullname, c.namefantazia, qtdchild, qtdpax, valueservice, firstname, lastname, nameinvoice, namepayment, tarifa 
                          FROM `ct_reserva` LEFT JOIN ct_servico s ON s.id = ct_reserva.idservico left JOIN ct_cliente c on c.id = ct_reserva.idcliente
                          left join `ct_usuario` u on u.id =  ct_reserva.idresponsavel left join `ct_statusinvoice` si on si.id = ct_reserva.idstatusinvoice
                          left join `ct_form_of_ payment` fp on fp.id = ct_reserva.idpayment left join ct_tarifa t on t.id = c.idtarifa
                          WHERE ct_reserva.dateinput >= :inicio and ct_reserva.dateinput <= :fim and ct_reserva.dateoutput >= :inicioo and ct_reserva.dateoutput <= :fimo 
                          and ct_reserva.idcliente = :cliente and `ct_reserva`.idpayment = :statuss group by numbervoucher ');
        $reservasPeriodo->execute(array(
            ":inicio"  => $datainicio,
            ":fim"     => $datafinal,
            ":inicioo" => $datainicio,
            ":fimo"    => $datafinal,
            ":cliente" => $idcliente,
            ":statuss" => $ststus
        ));
        $contador = $reservasPeriodo->rowCount();

        $adicionais = $pdo->prepare(
            'SELECT ra.dateinput as ap, s.fullname,s.screenplay, ra.valueservice, ss.schedule, qpax, qchild, qfree, r.numbervoucher, r.pax, r.documento,
                      firstname, lastname, namepayment,c.namefantazia, nameinvoice, tarifa
                      FROM `ct_recentlyadd` ra left join `ct_reserva` r on r.id = ra.idrecently  left join `ct_usuario` u on u.id =  r.idresponsavel
                      left join ct_servico s on s.id = ra.idservice left join ct_service_schedule ss on ss.idshedule = ra.idschedule
                      left join `ct_statusinvoice` si on si.id = r.idstatusinvoice left join `ct_form_of_ payment` fp on fp.id = r.idpayment
                      left JOIN ct_cliente c on c.id = r.idcliente left join ct_tarifa t on t.id = c.idtarifa
                      where ra.dateinput >= :inicio  and ra.dateoutput <= :fim 
                      and r.idpayment = :statuss');
        $adicionais->execute(array(
            ":inicio"  => $datainicio,
            ":fim"     => $datafinal,
            ":statuss" => $ststus
        ) );
        $registro = $adicionais->fetchAll(PDO::FETCH_CLASS);
        $contadorAdd = $adicionais->rowCount();
    }
    if( $datainicio > 0  and $datafinal > 0 and $idcliente == 0  )
    {
        $reservasPeriodo = $pdo->prepare(
            'SELECT numbervoucher, pax, s.fullname, c.namefantazia, qtdchild, qtdpax, valueservice , firstname, lastname, nameinvoice, namepayment, tarifa
                          FROM `ct_reserva` LEFT JOIN ct_servico s ON s.id = ct_reserva.idservico left JOIN ct_cliente c on c.id = ct_reserva.idcliente
                          left join `ct_usuario` u on u.id =  ct_reserva.idresponsavel left join `ct_statusinvoice` si on si.id = ct_reserva.idstatusinvoice
                          left join `ct_form_of_ payment` fp on fp.id = ct_reserva.idpayment left join ct_tarifa t on t.id = c.idtarifa
                          WHERE ct_reserva.dateinput >= :inicio and ct_reserva.dateinput <= :fim 
                          and ct_reserva.dateoutput >= :inicioo and ct_reserva.dateoutput <= :fimo and `ct_reserva`.idpayment = :statuss group by numbervoucher ');
        $reservasPeriodo->execute(array(
            ":inicio"  => $datainicio,
            ":fim"     => $datafinal,
            ":inicioo" => $datainicio,
            ":fimo"    => $datafinal,
            ":statuss" => $ststus
        ));
        $contador = $reservasPeriodo->rowCount();

        $adicionais = $pdo->prepare(
            'SELECT ra.dateinput as ap, s.fullname,s.screenplay, ra.valueservice, ss.schedule, qpax, qchild, qfree, r.numbervoucher, r.pax, r.documento,
                      firstname, lastname, namepayment,c.namefantazia, nameinvoice, tarifa
                      FROM `ct_recentlyadd` ra left join `ct_reserva` r on r.id = ra.idrecently  left join `ct_usuario` u on u.id =  r.idresponsavel
                      left join ct_servico s on s.id = ra.idservice left join ct_service_schedule ss on ss.idshedule = ra.idschedule
                      left join `ct_statusinvoice` si on si.id = r.idstatusinvoice left join `ct_form_of_ payment` fp on fp.id = r.idpayment
                      left JOIN ct_cliente c on c.id = r.idcliente left join ct_tarifa t on t.id = c.idtarifa
                      where ra.dateinput >= :inicio  and ra.dateoutput <= :fim  and r.idpayment = :statuss');
        $adicionais->execute(array(
            ":inicio"  => $datainicio,
            ":fim"     => $datafinal,
            ":statuss" => $ststus
        ) );
        $registro = $adicionais->fetchAll(PDO::FETCH_CLASS);
        $contadorAdd = $adicionais->rowCount();
    }


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
                                <li class="list-inline-item">Financeiro: Fatura</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <div class="row">
        <div class="container">
            <div class="col-lg-12">
                <h4>Informações da Fatura: </h4>
                <hr>
                <form action="" method="post">
                    <div class="col-md-6 pull-left">
                        <strong><label for="periodoinicial">Periodo Inicial</label></strong>
                        <input type="date" name="periodoinicial" id="periodoinicial" class="form-control" required>
                    </div>
                    <div class="col-md-6 pull-right">
                        <strong><label for="periodofinal">Periodo Final</label></strong>
                        <input type="date" name="periodofinal" id="periodofinal" class="form-control" required>
                    </div>
                    <div class="col-md-6 pull-left">
                        <strong><label for="cliente">Cliente</label></strong>
                        <select class="form-control" name="cliente" id="cliente" required>
                            <option value="0" >Todos os clientes</option>
                            <?php while ( $dadosCliente = $cliente->fetch( PDO::FETCH_ASSOC ) ){ ?>
                            <option value="<?php echo($dadosCliente['id']); ?>" ><?php echo($dadosCliente['namefantazia']); ?></option>
                            <?php }?>
                        </select>
                    </div>
                    <div class="col-md-6 pull-right">
                        <strong><label for="status">Status/F. Pagamento</label></strong>
                        <select class="form-control" name="status" id="status" required>
                            <?php while ( $dadosStatus = $status->fetch( PDO::FETCH_ASSOC ) ){ ?>
                                <option value="<?php echo($dadosStatus['id']); ?>" ><?php echo( utf8_encode( $dadosStatus['namepayment'])); ?></option>
                            <?php }?>
                        </select>
                    </div>
                    <div class="form-group container-fluid">
                        <button class="btn btn-primary btn-lg" name="pesquisarfatura">
                            Pesquisar
                        </button>
                    </div>
                </form>
                <?php if( $contador > 0 ){ ?>
                    <h4>Faturas Encontradas</h4>
                    <div class="table-responsive" style="margin-bottom: 20px;" >
                        <table class="table table-bordered">
                            <thead>
                            <th>Nº</th>
                            <th>PAX</th>
                            <th>Responsável</th>
                            <th>Clien.</th>
                            <th>Serviço</th>
                            <th>Valor Pax</th>
                            <th>Valor Ch</th>
                            <th>Total</th>
                            <th>Pagamento</th>
                            <th>Status</th>
                            </thead>
                            <tbody>
                            <?php while ($dados = $reservasPeriodo->fetch( PDO::FETCH_ASSOC )) {
                                $total = ( ($dados['valueservice'] * $dados['qtdpax'] ) + (($dados['valueservice'] / 2) * $dados['qtdchild'] ) ) -
                                    ( ($dados['tarifa'] * $dados['qtdpax'] ) + ( ($dados['valueservice'] / 2) * $dados['qtdchild'] ) );

                                $totalBruto1 = $totalBruto1 + ( ($dados['valueservice'] * $dados['qtdpax'] ) + (($dados['valueservice'] / 2) * $dados['qtdchild'] ) );
                                $tarifa1 = $tarifa1 + ( ($dados['tarifa'] * $dados['qtdpax'] ) + ( ($dados['valueservice'] / 2) * $dados['qtdchild'] ) );
                                $valorpax1 = $dados['valueservice'] - $dados['tarifa'];
                                $valorchd1 = $dados['valueservice'] / 2;
                                ?>
                                <tr>
                                    <td><?php echo( $dados['numbervoucher'] ); ?></td>
                                    <td><?php echo( $dados['pax'] ); ?></td>
                                    <td><?php echo( $dados['firstname']." ".$dados['lastname'] ); ?></td>
                                    <td><?php echo( $dados['c.namefantazia'] ); ?></td>
                                    <td><?php echo( utf8_encode( $dados['fullname'] ) ); ?></td>
                                    <td><?php echo( "R$ ".number_format($valorpax1,2,",",".") ); ?></td>
                                    <td><?php echo( "R$ ".number_format($valorchd1,2,",",".") ); ?></td>
                                    <td><?php echo( "R$ ".number_format($total,2,",",".") ); ?></td>
                                    <td><?php echo( utf8_encode( $dados['namepayment'] ) ); ?></td>
                                    <td><?php echo( $dados['nameinvoice'] ); ?></td>
                                </tr>
                                <?php if ($contadorAdd > 0){ ?>
                                    <?php foreach ($registro as $item){
                                        $valorSub =  ( ( $item->valueservice * $item->qpax ) + ( ($item->valueservice / 2) * $item->qchild ) ) -
                                            ( ( $item->tarifa * $item->qpax ) + ( ($item->valueservice / 2) * $item->qchild ) );
                                        $totalBruto2 = $totalBruto2 + ( ( $item->valueservice * $item->qpax ) + ( ($item->valueservice) * $item->qchild ) );
                                        $tarifa2 = $tarifa2 +  ( ( $item->tarifa * $item->qpax ) + ( ($item->valueservice / 2) * $item->qchild ) );
                                        $valorpax2 =  $item->valueservice - $item->tarifa;
                                        $valorchd2 = $item->valueservice / 2;
                                        ?>
                                        <tr>
                                            <td><?php echo( $item->numbervoucher ); ?></td>
                                            <td><?php echo( $item->pax ); ?></td>
                                            <td><?php echo( $item->firstname." ".$item->lastname ); ?></td>
                                            <td><?php echo( $item->namefantazia ); ?></td>
                                            <td><?php echo( utf8_encode( $item->fullname ) ); ?></td>
                                            <td><?php echo("R$ ".number_format($valorpax2,2,",",".") ); ?></td>
                                            <td><?php echo("R$ ".number_format($valorchd2,2,",",".") ); ?></td>
                                            <td><?php echo("R$ ".number_format($valorSub,2,",",".") ); ?></td>
                                            <td><?php echo( utf8_encode( $item->namepayment ) ); ?></td>
                                            <td><?php echo( $item->nameinvoice ); ?></td>
                                        </tr>
                                    <?php }?>
                                <?php }?>
                            <?php $valorGeral = $valorGeral + ($valorSub + $total); }?>
                            </tbody>
                        </table>
                    </div>
                    <form action="./atualizar-faturas" method="post">
                        <input type="hidden" name="idcliente" value="<?php echo( $idcliente ); ?>" >
                        <input type="hidden" name="statusescolhido" value="<?php echo( $ststus ); ?>" >
                        <input type="hidden" name="datainicio" value="<?php echo( $datainicio ); ?>" >
                        <input type="hidden" name="datafim" value="<?php echo( $datafinal ); ?>" >
                        <input type="hidden" name="valorFaturaGeral" value="<?php echo($valorGeral); ?>" >
                        <input type="hidden" name="valorBruto" value="<?php echo($totalBruto1 + $totalBruto2) ?>" >
                        <input type="hidden" name="valorNet" value="<?php echo($tarifa1 + $tarifa2) ?>" >
                        <div class="col-md-6 pull-left">
                            <button type="submit" class="btn btn-warning btn-lg" name="todosVoucher">
                                Atualizar informações gerais.
                            </button>
                        </div>
                    </form>
                <?php } if( isset( $_POST['pesquisarfatura'] ) and $contador == 0 )  { ?>
                    <div class="alert alert-warning" style="color: black;" role="alert">
                        Não encontramos registros para os dados informados. Realize uma nova pesquisa
                    </div>
                <?php }?>
            </div>
        </div>
    </div>
    <?php require_once ('footer.php'); ?>
