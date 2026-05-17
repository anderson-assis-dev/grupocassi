<?php
require_once( '../.././config.php' );
ob_start();
define('MPDP_PATH', 'MPDF54/');
$datainicio  = $_POST['periodoinicial'];
$datafim     = $_POST['periodofinal'];
$cliente     = $_POST['cliente'];
$status      = $_POST['status'];
$tipo        = $_POST['tiporelatorio'];
$responsavel = $_POST['responsavel'];
$totalClienteAdd = 0;
?>
<?php if($tipo == 0){ ?>
    <html>
    <head>
        <meta charset="utf-8">
        <title><?php echo( utf8_decode( "Relatório de Fatura" ) ); ?></title>
        <link rel="stylesheet" href="materialize.min.css">
    </head>
    <style>
        th, td{border: 1px solid #ddd; padding: 8px;}
        td#desc{font-weight: bold;}
    </style>
    <body>
    <div class="container">
        <img style="width: 700px; margin-left: 50px; " id="logo" src="../../images/logo.png"/>
        <hr>
        <p><?php echo( utf8_decode( "Relatório de Fatura  de: ".
                date("d/m/Y ", strtotime( $datainicio ))." ate ".date("d/m/Y ", strtotime( $datafim )))); ?> </p><br>
        <p style="font-size: 9px; margin-top: -20px;">Impresso em: <?php echo(date("d/m/Y - H:i:s")); ?></p>

        <table class="highlight">
            <thead>
            <tr>
                <th>ABERTURA</th>
                <th>EMBARQUE</th>
                <th>AP</th>
                <th>VOUCHER</th>
                <th>CLIENTE</th>
                <th>INFO</th>
                <th>PAX</th>
                <th>P/C</th>
                <th>RES</th>
                <th>SERVICO</th>
                <th>PAGAMENTO</th>
                <th>VALOR</th>
                <th>COMISSAO</th>
                <th>LIQUIDO</th>
                <th>RECEBIDO</th>
                <th>STATUS</th>
            </tr>
            </thead>
            <tbody>
            <?php if( $status[0] == 0 ) {
                $sql = "select dateinput, dateoutput ,numbervoucher, c.namefantazia as cliente, pax, u.firstname, u.lastname, s.fullname as servico, r.documento,
                                   s.priceadult,cp.namepayment as pagamento, qtdpax, qtdchild, nameinvoice as statuu, valueservice, r.abertura, r.idstatusinvoice, r.horaap
                                   from `ct_reserva` r  left join `ct_cliente` c on c.`id` = r.`idcliente` left join `ct_usuario` u on u.`id` = r.`idresponsavel`
                        left join `ct_servico` s on s.`id` = r.`idservico` left join `ct_form_of_ payment` cp on cp.`id` = r.`idpayment` left join `ct_statusinvoice` si on
                        si.`id` = r.`idstatusinvoice` where r.`dateinput` >= '".$datainicio."' and r.`dateinput` <= '".$datafim."'";
                if($responsavel > 0){
                    $sql .= " and r.idresponsavel = ".$responsavel;
                }
                if($cliente > 0){
                    $sql .= " and r.`idcliente` = ".$cliente;
                }
                if($status[0] > 0){
                    $sql .= " and r.`idstatusinvoice` = ".$status[0];
                }
                if(!empty($_POST['nomepax'])){
                    $sql .= " and r.`pax` = '".$_POST['nomepax']."'";
                }

                $buscarConferencia = $pdo->prepare($sql);
                $buscarConferencia->execute();
                $registro          = $buscarConferencia->fetchAll( PDO::FETCH_CLASS );
                $dadosCliente      = $buscarConferencia->fetch( PDO::FETCH_ASSOC );
                $linhas            = $buscarConferencia->rowCount();
                $sql = "select  ra.dateinput, ra.dateoutput, numbervoucher, c.namefantazia as cliente, pax, u.firstname, u.lastname, s.fullname as servico,r.documento,
                                      cp.namepayment as pagamento, qpax, qchild, nameinvoice as statuus, ra.valueservice, r.abertura, ra.horaap
                                      from `ct_recentlyadd` ra left join `ct_reserva` r on ra.`idrecently` = r.id left join `ct_servico`
                                      s on ra.idservice = s.id  left join `ct_cliente` c on c.`id` = r.`idcliente` left join `ct_usuario` u on u.`id` = r.`idresponsavel`
                                      left join `ct_form_of_ payment` cp on cp.`id` = r.`idpayment` left join `ct_statusinvoice` si on si.`id` = r.`idstatusinvoice` where ra.`dateinput` >= '".$datainicio."' and ra.`dateinput` <= '".$datafim."'";
                if($responsavel > 0){
                    $sql .= " and r.idresponsavel = ".$responsavel;
                }
                if($cliente > 0){
                    $sql .= " and r.`idcliente` = ".$cliente;
                }
                if($status[0] > 0){
                    $sql .= " and r.`idstatusinvoice` = ".$status[0];
                }
                if(!empty($_POST['nomepax'])){
                    $sql .= " and r.`pax` = '".$_POST['nomepax']."'";
                }
                $buscarConferenciaAdd = $pdo->prepare($sql);
                $buscarConferenciaAdd->execute();
                $linhasAdd         = $buscarConferenciaAdd->rowCount();


                ?>
                <?php foreach( $registro as $item ) {

                    $financeiroReserva = $pdo->prepare(
                        'SELECT tarifa, SUM(valuecredit) as credito, SUM(valueguia) as guia, SUM(valueagente) AS agente
                              FROM `ct_createfaturacredit` where numbervoucher = :voucher ');
                    $financeiroReserva->execute(array(":voucher" =>  $item->numbervoucher));
                    $dadosFinanceiro = $financeiroReserva->fetch( PDO::FETCH_ASSOC );

                    $descricaoPagamento = $pdo->prepare(
                        'SELECT datacredit as dia, `name` as pagamento, valuecredit as valor  FROM `ct_createfaturacredit` cfc 
                                  left join `ct_currentaccount` cc on cc.id = cfc.idaccountcurrent where numbervoucher = :voucher');
                    $descricaoPagamento->execute(array(":voucher" =>  $item->numbervoucher));
                    $registroDescricao = $descricaoPagamento->fetchAll(PDO::FETCH_CLASS);
                    $contadorDescricaoPagamento  = $descricaoPagamento->rowCount();

                    $valorBruto = ( ( $item->valueservice * $item->qtdpax ) +
                        ( ($item->valueservice / 2) * $item->qtdchild ) );
                    $somaDosServicos1  = $somaDosServicos1 + $valorBruto;
                    $valorLiquido      = $valorBruto   - ($dadosFinanceiro['guia'] + $dadosFinanceiro['agente']);
                    if( $item->idstatusinvoice == 3 )
                    {
                        $totalPago = $totalPago + $dadosFinanceiro['credito'];
                    }
                    ?>
                    <tr>
                        <td><?php echo( date("d/m/Y", strtotime($item->abertura))  ); ?></td>
                        <td><?php echo( date("d/m/Y", strtotime($item->dateinput))  ); ?></td>
                        <td><?php echo( date("H:i", strtotime($item->horaap))  ); ?></td>
                        <td><?php echo( $item->numbervoucher ); ?></td>
                        <td><?php echo( utf8_decode($item->cliente)   ); ?></td>
                        <td><?php echo( utf8_decode($item->documento)   ); ?></td>
                        <td><?php echo( utf8_decode($item->pax)  ); ?></td>
                        <td><?php echo( $item->qtdpax."/".$item->qtdchild); ?></td>
                        <td><?php echo( utf8_decode($item->firstname." ". $item->lastname)  ); ?></td>
                        <td><?php echo( $item->servico ); ?></td>
                        <td><?php echo( $item->pagamento ); ?></td>
                        <td><?php echo( "R$ ".number_format( $valorBruto,2,",","." ) ); ?></td>
                        <td><?php echo( "R$ ".number_format( $dadosFinanceiro['guia'] + $dadosFinanceiro['agente'],2,",","." ) ); ?></td>
                        <td><?php echo( "R$ ".number_format( $valorLiquido,2,",","." ) ); ?></td>
                        <td><?php echo( "R$ ".number_format( $dadosFinanceiro['credito'],2,",","." ) ); ?></td>
                        <td><?php echo( $item->statuu ); ?></td>
                    </tr>
                    <?php if( $contadorDescricaoPagamento >0 ){ ?>
                        <?php foreach ($registroDescricao as $item3){ ?>
                            <tr>
                                <td id="desc" colspan="5"><?php echo("<strong>Data Pagamento</strong> ".date("d-m-Y", strtotime($item3->dia))); ?></td>
                                <td id="desc" colspan="5"><?php echo("<strong>Metodo</strong> ".$item3->pagamento); ?></td>
                                <td id="desc" colspan="5">
                                    <?php echo("<strong>Valor R$ </strong>".number_format($item3->valor, 2,",", ".")); ?>
                                </td>
                            </tr>
                        <?php }?>

                    <?php }?>


                <?php }?>
                <?php while( $registrosAdd = $buscarConferenciaAdd->fetch( PDO::FETCH_ASSOC ) ){
                    $financeiroReserva = $pdo->prepare(
                        'SELECT tarifa, SUM(valuecredit) as credito, SUM(valueguia) as guia, SUM(valueagente) AS agente
                                   FROM `ct_createfaturacredit` where numbervoucher = :voucher ');
                    $financeiroReserva->execute(array(":voucher" =>  $registrosAdd['numbervoucher']));
                    $dadosFinanceiro = $financeiroReserva->fetch( PDO::FETCH_ASSOC );

                    $somaDosServicos2 = $somaDosServicos2 + ( ( $registrosAdd['valueservice'] * $registrosAdd['qpax'] ) +
                            ( ($registrosAdd['valueservice'] / 2) * $registrosAdd['qchild'] ) );
                    ?>
                    <tr>
                        <td><?php echo( date("d/m/Y", strtotime($registrosAdd['abertura']))  ); ?></td>
                        <td><?php echo( date("d/m/Y", strtotime($registrosAdd['dateinput']))  ); ?></td>
                        <td><?php echo( date("H:i", strtotime($registrosAdd['horaap']))  ); ?></td>
                        <td><?php echo( $registrosAdd['numbervoucher'] ); ?></td>
                        <td><?php echo( utf8_decode($registrosAdd['cliente'])  ); ?></td>
                        <td><?php echo( utf8_decode($registrosAdd['pax'])  ); ?></td>
                        <td><?php echo( $registrosAdd['qpax']."/".$registrosAdd['qchild'] ); ?></td>
                        <td><?php echo( $registrosAdd['firstname'] ); ?></td>
                        <td><?php echo( utf8_decode($registrosAdd['servico'])  ); ?></td>
                        <td><?php echo( $registrosAdd['pagamento'] ); ?></td>
                        <td>
                            <?php echo( "R$ ".number_format( ( ( $registrosAdd['valueservice'] * $registrosAdd['qpax'] ) +
                                    ( ($registrosAdd['valueservice'] / 2) * $registrosAdd['qchild'] ) ),2,",","." ) ); ?>
                        </td>
                        <td><?php echo( "R$ ".number_format($dadosFinanceiro['guia'] + $dadosFinanceiro['agente'],2,",","." )  ); ?></td>
                        <td>
                            <?php echo( "R$ ".number_format( ( ( $registrosAdd['valueservice'] * $registrosAdd['qpax'] ) +
                                        ( ($registrosAdd['valueservice'] / 2) * $registrosAdd['qchild'] ) ) - ($dadosFinanceiro['guia'] +
                                        $dadosFinanceiro['agente']) ,2,",","." ) ); ?>
                        </td>
                        <td>-</td>
                        <td><?php echo( $registrosAdd['statuus'] ); ?></td>
                    </tr>
                <?php }?>
            <?php }
            else {?>
                <?php for($contadorStatus = 0; $contadorStatus < count($status); $contadorStatus ++){
                    if( $cliente == 0 and $status[$contadorStatus] == 0  )
                    {
                        if( $responsavel == 0 )
                        {
                            $buscarConferencia = $pdo->prepare(
                                'select dateinput, dateoutput ,numbervoucher, c.namefantazia as cliente, pax, u.firstname, u.lastname, s.fullname as servico,
                          s.priceadult,cp.namepayment as pagamento, qtdpax, qtdchild, nameinvoice as statuu, valueservice, r.abertura, r.idstatusinvoice, r.horaap
                          from `ct_reserva` r  left join `ct_cliente` c on c.`id` = r.`idcliente` left join `ct_usuario` u on u.`id` = r.`idresponsavel`
                          left join `ct_servico` s on s.`id` = r.`idservico` left join `ct_form_of_ payment` cp on cp.`id` = r.`idpayment` left join `ct_statusinvoice` si on
                          si.`id` = r.`idstatusinvoice` where r.`dateinput` >= :inicio and r.`dateinput` <= :fim ');
                            $buscarConferencia->execute( array( ":inicio" => $datainicio, ":fim" => $datafim ) );

                            $buscarConferenciaAdd = $pdo->prepare(
                                'select  ra.dateinput, ra.dateoutput, numbervoucher, c.namefantazia as cliente, pax, u.firstname, u.lastname, s.fullname as servico,
                                      cp.namepayment as pagamento, qpax, qchild, nameinvoice as statuus, ra.valueservice, r.abertura, ra.horaap
                                      from `ct_recentlyadd` ra left join `ct_reserva` r on ra.`idrecently` = r.id left join `ct_servico`
                                      s on ra.idservice = s.id  left join `ct_cliente` c on c.`id` = r.`idcliente` left join `ct_usuario` u on u.`id` = r.`idresponsavel`
                                      left join `ct_form_of_ payment` cp on cp.`id` = r.`idpayment` left join `ct_statusinvoice` si on si.`id` = r.`idstatusinvoice`
                                      where ra.`dateinput` >= :inicio and ra.`dateinput` <= :fim ');
                            $buscarConferenciaAdd->execute( array( ":inicio" => $datainicio, ":fim" => $datafim) );
                        }else{
                            $buscarConferencia = $pdo->prepare(
                                'select dateinput, dateoutput ,numbervoucher, c.namefantazia as cliente, pax, u.firstname, u.lastname, s.fullname as servico,
                          s.priceadult,cp.namepayment as pagamento, qtdpax, qtdchild, nameinvoice as statuu, valueservice, r.abertura, r.idstatusinvoice, r.horaap
                          from `ct_reserva` r  left join `ct_cliente` c on c.`id` = r.`idcliente` left join `ct_usuario` u on u.`id` = r.`idresponsavel`
                          left join `ct_servico` s on s.`id` = r.`idservico` left join `ct_form_of_ payment` cp on cp.`id` = r.`idpayment` left join `ct_statusinvoice` si on
                          si.`id` = r.`idstatusinvoice` where r.`dateinput` >= :inicio and r.`dateinput` <= :fim and r.`idresponsavel` = :res ');
                            $buscarConferencia->execute( array( ":inicio" => $datainicio, ":fim" => $datafim, ":res" => $responsavel ) );

                            $buscarConferenciaAdd = $pdo->prepare(
                                'select  ra.dateinput, ra.dateoutput, numbervoucher, c.namefantazia as cliente, pax, u.firstname, u.lastname, s.fullname as servico,
                                      cp.namepayment as pagamento, qpax, qchild, nameinvoice as statuus, ra.valueservice, r.abertura, ra.horaap
                                      from `ct_recentlyadd` ra left join `ct_reserva` r on ra.`idrecently` = r.id left join `ct_servico`
                                      s on ra.idservice = s.id  left join `ct_cliente` c on c.`id` = r.`idcliente` left join `ct_usuario` u on u.`id` = r.`idresponsavel`
                                      left join `ct_form_of_ payment` cp on cp.`id` = r.`idpayment` left join `ct_statusinvoice` si on si.`id` = r.`idstatusinvoice`
                                      where ra.`dateinput` >= :inicio and ra.`dateinput` <= :fim and r.`idresponsavel` = :res ');
                            $buscarConferenciaAdd->execute( array( ":inicio" => $datainicio, ":fim" => $datafim, ":res" => $responsavel) );
                        }

                    }
                    elseif ( $cliente > 0 and $status[$contadorStatus] == 0 )
                    {
                        if( $responsavel == 0 )
                        {
                            $buscarConferencia = $pdo->prepare(
                                'select dateinput, dateoutput ,numbervoucher, c.namefantazia as cliente, pax, u.firstname, u.lastname, s.fullname as servico, 
                          s.priceadult,cp.namepayment as pagamento, qtdpax, qtdchild, nameinvoice as statuu, valueservice, r.abertura, r.idstatusinvoice, r.horaap
                          from `ct_reserva` r  left join `ct_cliente` c on c.`id` = r.`idcliente` left join `ct_usuario` u on u.`id` = r.`idresponsavel`
                          left join `ct_servico` s on s.`id` = r.`idservico` left join `ct_form_of_ payment` cp on cp.`id` = r.`idpayment` left join `ct_statusinvoice` si on
                          si.`id` = r.`idstatusinvoice` where r.`dateinput` >= :inicio and r.`dateinput` <= :fim and r.`idcliente` = :cliente ');
                            $buscarConferencia->execute( array( ":inicio" => $datainicio, ":fim" => $datafim, ":cliente" =>$cliente ) );

                            $buscarConferenciaAdd = $pdo->prepare(
                                'select  ra.dateinput, ra.dateoutput, numbervoucher, c.namefantazia as cliente, pax, u.firstname, u.lastname, s.fullname as servico,
                                  s.priceadult, cp.namepayment as pagamento, qpax, qchild, nameinvoice as statuus, ra.valueservice, r.abertura, ra.horaap
                                  from `ct_recentlyadd` ra left join `ct_reserva` r on ra.`idrecently` = r.id left join `ct_servico`
                                  s on ra.idservice = s.id  left join `ct_cliente` c on c.`id` = r.`idcliente` left join `ct_usuario` u on u.`id` = r.`idresponsavel`
                                  left join `ct_form_of_ payment` cp on cp.`id` = r.`idpayment` left join `ct_statusinvoice` si on si.`id` = r.`idstatusinvoice`
                                  where ra.`dateinput` >= :inicio and ra.`dateinput` <= :fim and r.`idcliente` = :cliente ');
                            $buscarConferenciaAdd->execute( array( ":inicio" => $datainicio, ":fim" => $datafim, ":cliente" =>$cliente ) );
                        }else{
                            $buscarConferencia = $pdo->prepare(
                                'select dateinput, dateoutput ,numbervoucher, c.namefantazia as cliente, pax, u.firstname, u.lastname, s.fullname as servico, 
                          s.priceadult,cp.namepayment as pagamento, qtdpax, qtdchild, nameinvoice as statuu, valueservice, r.abertura, r.idstatusinvoice, r.horaap
                          from `ct_reserva` r  left join `ct_cliente` c on c.`id` = r.`idcliente` left join `ct_usuario` u on u.`id` = r.`idresponsavel`
                          left join `ct_servico` s on s.`id` = r.`idservico` left join `ct_form_of_ payment` cp on cp.`id` = r.`idpayment` left join `ct_statusinvoice` si on
                          si.`id` = r.`idstatusinvoice` where r.`dateinput` >= :inicio and r.`dateinput` <= :fim and r.`idcliente` = :cliente and r.`idresponsavel` = :res ');
                            $buscarConferencia->execute( array( ":inicio" => $datainicio, ":fim" => $datafim, ":cliente" =>$cliente, ":res" => $responsavel ) );

                            $buscarConferenciaAdd = $pdo->prepare(
                                'select  ra.dateinput, ra.dateoutput, numbervoucher, c.namefantazia as cliente, pax, u.firstname, u.lastname, s.fullname as servico,
                                  s.priceadult, cp.namepayment as pagamento, qpax, qchild, nameinvoice as statuus, ra.valueservice, r.abertura, ra.horaap
                                  from `ct_recentlyadd` ra left join `ct_reserva` r on ra.`idrecently` = r.id left join `ct_servico`
                                  s on ra.idservice = s.id  left join `ct_cliente` c on c.`id` = r.`idcliente` left join `ct_usuario` u on u.`id` = r.`idresponsavel`
                                  left join `ct_form_of_ payment` cp on cp.`id` = r.`idpayment` left join `ct_statusinvoice` si on si.`id` = r.`idstatusinvoice`
                                  where ra.`dateinput` >= :inicio and ra.`dateinput` <= :fim and r.`idcliente` = :cliente and r.`idresponsavel` = :res ');
                            $buscarConferenciaAdd->execute( array( ":inicio" => $datainicio, ":fim" => $datafim, ":cliente" =>$cliente, ":res" => $responsavel ) );
                        }

                    }
                    elseif ( $cliente == 0 and $status[$contadorStatus] > 0 )
                    {
                        if( $responsavel == 0 )
                        {
                            $buscarConferencia = $pdo->prepare(
                                'select dateinput, dateoutput ,numbervoucher, c.namefantazia as cliente, pax, u.firstname, u.lastname, s.fullname as servico, 
                          s.priceadult,cp.namepayment as pagamento, qtdpax, qtdchild, nameinvoice as statuu, valueservice, r.abertura, r.idstatusinvoice, r.horaap
                          from `ct_reserva` r  left join `ct_cliente` c on c.`id` = r.`idcliente` left join `ct_usuario` u on u.`id` = r.`idresponsavel`
                          left join `ct_servico` s on s.`id` = r.`idservico` left join `ct_form_of_ payment` cp on cp.`id` = r.`idpayment` left join `ct_statusinvoice` si on
                          si.`id` = r.`idstatusinvoice`
                          where r.`dateinput` >= :inicio and r.`dateinput` <= :fim and r.`idstatusinvoice` = :statuss ');
                            $buscarConferencia->execute( array( ":inicio" => $datainicio, ":fim" => $datafim, ":statuss" => $status[$contadorStatus] ) );

                            $buscarConferenciaAdd = $pdo->prepare(
                                'select  ra.dateinput, ra.dateoutput, numbervoucher, c.namefantazia as cliente, pax, u.firstname, u.lastname, s.fullname as servico,
                          s.priceadult,cp.namepayment as pagamento, qpax, qchild, nameinvoice as statuus, ra.valueservice, r.abertura, ra.horaap
                          from `ct_recentlyadd` ra left join `ct_reserva` r on ra.`idrecently` = r.id left join `ct_servico`
                          s on ra.idservice = s.id  left join `ct_cliente` c on c.`id` = r.`idcliente` left join `ct_usuario` u on u.`id` = r.`idresponsavel`
                          left join `ct_form_of_ payment` cp on cp.`id` = r.`idpayment` left join `ct_statusinvoice` si on si.`id` = r.`idstatusinvoice`
                          where ra.`dateinput` >= :inicio and ra.`dateinput` <= :fim  and r.`idstatusinvoice` = :statuss ');
                            $buscarConferenciaAdd->execute( array( ":inicio" => $datainicio, ":fim" => $datafim, ":statuss" => $status[$contadorStatus]) );
                        }else {
                            $buscarConferencia = $pdo->prepare(
                                'select dateinput, dateoutput ,numbervoucher, c.namefantazia as cliente, pax, u.firstname, u.lastname, s.fullname as servico, 
                          s.priceadult,cp.namepayment as pagamento, qtdpax, qtdchild, nameinvoice as statuu, valueservice, r.abertura, r.idstatusinvoice, r.horaap
                          from `ct_reserva` r  left join `ct_cliente` c on c.`id` = r.`idcliente` left join `ct_usuario` u on u.`id` = r.`idresponsavel`
                          left join `ct_servico` s on s.`id` = r.`idservico` left join `ct_form_of_ payment` cp on cp.`id` = r.`idpayment` left join `ct_statusinvoice` si on
                          si.`id` = r.`idstatusinvoice` where r.`dateinput` >= :inicio and r.`dateinput` <= :fim
                          and r.`idstatusinvoice` = :statuss and r.`idresponsavel` = :res ');
                            $buscarConferencia->execute( array( ":inicio" => $datainicio, ":fim" => $datafim,
                                ":statuss" => $status[$contadorStatus], ":res" => $responsavel ) );

                            $buscarConferenciaAdd = $pdo->prepare(
                                'select  ra.dateinput, ra.dateoutput, numbervoucher, c.namefantazia as cliente, pax, u.firstname, u.lastname, s.fullname as servico,
                          s.priceadult,cp.namepayment as pagamento, qpax, qchild, nameinvoice as statuus, ra.valueservice, r.abertura, ra.horaap
                          from `ct_recentlyadd` ra left join `ct_reserva` r on ra.`idrecently` = r.id left join `ct_servico`
                          s on ra.idservice = s.id  left join `ct_cliente` c on c.`id` = r.`idcliente` left join `ct_usuario` u on u.`id` = r.`idresponsavel`
                          left join `ct_form_of_ payment` cp on cp.`id` = r.`idpayment` left join `ct_statusinvoice` si on si.`id` = r.`idstatusinvoice`
                          where ra.`dateinput` >= :inicio and ra.`dateinput` <= :fim  and r.`idstatusinvoice` = :statuss and r.`idresponsavel` = :res ');
                            $buscarConferenciaAdd->execute( array( ":inicio" => $datainicio,
                                ":fim" => $datafim, ":statuss" => $status[$contadorStatus], ":res" => $responsavel) );
                        }

                    }
                    elseif ( $cliente > 0 and $status[$contadorStatus] > 0 )
                    {
                        if( $responsavel == 0 )
                        {
                            $buscarConferencia = $pdo->prepare(
                                'select dateinput, dateoutput ,numbervoucher, c.namefantazia as cliente, pax, u.firstname, u.lastname, s.fullname as servico, 
                                  s.priceadult,cp.namepayment as pagamento, qtdpax, qtdchild, nameinvoice as statuu, valueservice, r.abertura, r.idstatusinvoice, r.horaap
                                  from `ct_reserva` r  left join `ct_cliente` c on c.`id` = r.`idcliente` left join `ct_usuario` u on u.`id` = r.`idresponsavel`
                                  left join `ct_servico` s on s.`id` = r.`idservico` left join `ct_form_of_ payment` cp on cp.`id` = r.`idpayment` left join `ct_statusinvoice` si on
                                  si.`id` = r.`idstatusinvoice` where r.`dateinput` >= :inicio and r.`dateoutput` <= :fim
                                  and r.`idstatusinvoice` = :statuss and r.`idcliente` = :cliente ');
                            $buscarConferencia->execute(
                                array( ":inicio" => $datainicio, ":fim" => $datafim, ":statuss" => $status[$contadorStatus], ":cliente" => $cliente ) );

                            $buscarConferenciaAdd = $pdo->prepare(
                                'select  ra.dateinput, ra.dateoutput, numbervoucher, c.namefantazia as cliente, pax, u.firstname, u.lastname, s.fullname as servico,
                              s.priceadult,cp.namepayment as pagamento, qpax, qchild, nameinvoice as statuus, ra.valueservice, r.abertura, ra.horaap
                              from `ct_recentlyadd` ra left join `ct_reserva` r on ra.`idrecently` = r.id left join `ct_servico`
                              s on ra.idservice = s.id  left join `ct_cliente` c on c.`id` = r.`idcliente` left join `ct_usuario` u on u.`id` = r.`idresponsavel`
                              left join `ct_form_of_ payment` cp on cp.`id` = r.`idpayment` left join `ct_statusinvoice` si on si.`id` = r.`idstatusinvoice`
                              where ra.`dateinput` >= :inicio and ra.`dateinput` <= :fim and r.`idstatusinvoice` = :statuss and r.`idcliente` = :cliente ');
                            $buscarConferenciaAdd->execute(
                                array( ":inicio" => $datainicio, ":fim" => $datafim, ":statuss" => $status[$contadorStatus], ":cliente" => $cliente) );
                        }else{
                            $buscarConferencia = $pdo->prepare(
                                'select dateinput, dateoutput ,numbervoucher, c.namefantazia as cliente, pax, u.firstname, u.lastname, s.fullname as servico, 
                                  s.priceadult,cp.namepayment as pagamento, qtdpax, qtdchild, nameinvoice as statuu, valueservice, r.abertura, r.idstatusinvoice, r.horaap
                                  from `ct_reserva` r  left join `ct_cliente` c on c.`id` = r.`idcliente` left join `ct_usuario` u on u.`id` = r.`idresponsavel`
                                  left join `ct_servico` s on s.`id` = r.`idservico` left join `ct_form_of_ payment` cp on cp.`id` = r.`idpayment` left join `ct_statusinvoice` si on
                                  si.`id` = r.`idstatusinvoice` where r.`dateinput` >= :inicio and r.`dateoutput` <= :fim
                                  and r.`idstatusinvoice` = :statuss and r.`idcliente` = :cliente and r.`idresponsavel` = :res ');
                            $buscarConferencia->execute(
                                array( ":inicio" => $datainicio, ":fim" => $datafim, ":statuss" => $status[$contadorStatus],
                                    ":cliente" => $cliente, ":res" => $responsavel ) );

                            $buscarConferenciaAdd = $pdo->prepare(
                                'select  ra.dateinput, ra.dateoutput, numbervoucher, c.namefantazia as cliente, pax, u.firstname, u.lastname, s.fullname as servico,
                              s.priceadult,cp.namepayment as pagamento, qpax, qchild, nameinvoice as statuus, ra.valueservice, r.abertura, ra.horaap
                              from `ct_recentlyadd` ra left join `ct_reserva` r on ra.`idrecently` = r.id left join `ct_servico`
                              s on ra.idservice = s.id  left join `ct_cliente` c on c.`id` = r.`idcliente` left join `ct_usuario` u on u.`id` = r.`idresponsavel`
                              left join `ct_form_of_ payment` cp on cp.`id` = r.`idpayment` left join `ct_statusinvoice` si on si.`id` = r.`idstatusinvoice`
                              where ra.`dateinput` >= :inicio and ra.`dateinput` <= :fim and r.`idstatusinvoice` = :statuss
                              and r.`idcliente` = :cliente and r.`idresponsavel` = :res ');
                            $buscarConferenciaAdd->execute(
                                array( ":inicio" => $datainicio, ":fim" => $datafim, ":statuss" => $status[$contadorStatus], ":cliente" => $cliente, ":res" => $responsavel) );
                        }


                    }
                    $registro          = $buscarConferencia->fetchAll( PDO::FETCH_CLASS );
                    $dadosCliente      = $buscarConferencia->fetch( PDO::FETCH_ASSOC );
                    $linhas            = $buscarConferencia->rowCount();
                    $linhasAdd         = $buscarConferenciaAdd->rowCount();
                    ?>
                    <?php foreach( $registro as $item ) {

                        $financeiroReserva = $pdo->prepare(
                            'SELECT tarifa, SUM(valuecredit) as credito, SUM(valueguia) as guia, SUM(valueagente) AS agente
                              FROM `ct_createfaturacredit` where numbervoucher = :voucher ');
                        $financeiroReserva->execute(array(":voucher" =>  $item->numbervoucher));
                        $dadosFinanceiro = $financeiroReserva->fetch( PDO::FETCH_ASSOC );

                        $descricaoPagamento = $pdo->prepare(
                            'SELECT datacredit as dia, `name` as pagamento, valuecredit as valor  FROM `ct_createfaturacredit` cfc 
                                  left join `ct_currentaccount` cc on cc.id = cfc.idaccountcurrent where numbervoucher = :voucher');
                        $descricaoPagamento->execute(array(":voucher" =>  $item->numbervoucher));
                        $registroDescricao = $descricaoPagamento->fetchAll(PDO::FETCH_CLASS);
                        $contadorDescricaoPagamento  = $descricaoPagamento->rowCount();

                        $valorBruto = ( ( $item->valueservice * $item->qtdpax ) +
                            ( ($item->valueservice / 2) * $item->qtdchild ) );
                        $somaDosServicos1  = $somaDosServicos1 + $valorBruto;
                        $valorLiquido      = $valorBruto   - ($dadosFinanceiro['guia'] + $dadosFinanceiro['agente']);
                        if( $item->idstatusinvoice == 3 )
                        {
                            $totalPago = $totalPago + $dadosFinanceiro['credito'];
                        }


                        ?>
                        <tr>
                            <td><?php echo( date("d/m/Y", strtotime($item->abertura))  ); ?></td>
                            <td><?php echo( date("d/m/Y", strtotime($item->dateinput))  ); ?></td>
                            <td><?php echo( date("H:i", strtotime($item->horaap))  ); ?></td>
                            <td><?php echo( $item->numbervoucher ); ?></td>
                            <td><?php echo( utf8_decode($item->cliente)   ); ?></td>
                            <td><?php echo( utf8_decode($item->pax)  ); ?></td>
                            <td><?php echo( $item->qtdpax."/".$item->qtdchild); ?></td>
                            <td><?php echo( utf8_decode($item->firstname." ". $item->lastname)  ); ?></td>
                            <td><?php echo( $item->servico ); ?></td>
                            <td><?php echo( $item->pagamento ); ?></td>
                            <td><?php echo( "R$ ".number_format( $valorBruto,2,",","." ) ); ?></td>
                            <td><?php echo( "R$ ".number_format( $dadosFinanceiro['guia'] + $dadosFinanceiro['agente'],2,",","." ) ); ?></td>
                            <td><?php echo( "R$ ".number_format( $valorLiquido,2,",","." ) ); ?></td>
                            <td><?php echo( "R$ ".number_format( $dadosFinanceiro['credito'],2,",","." ) ); ?></td>
                            <td><?php echo( $item->statuu ); ?></td>
                        </tr>
                        <?php if( $contadorDescricaoPagamento >0 ){ ?>
                            <?php foreach ($registroDescricao as $item3){ ?>
                                <tr>
                                    <td id="desc" colspan="5"><?php echo("<strong>Data Pagamento</strong> ".date("d-m-Y", strtotime($item3->dia))); ?></td>
                                    <td id="desc" colspan="5"><?php echo("<strong>Metodo</strong> ".$item3->pagamento); ?></td>
                                    <td id="desc" colspan="5">
                                        <?php echo("<strong>Valor R$ </strong>".number_format($item3->valor, 2,",", ".")); ?>
                                    </td>
                                </tr>
                            <?php }?>

                        <?php }?>


                    <?php }?>
                    <?php while( $registrosAdd = $buscarConferenciaAdd->fetch( PDO::FETCH_ASSOC ) ){
                        $financeiroReserva = $pdo->prepare(
                            'SELECT tarifa, SUM(valuecredit) as credito, SUM(valueguia) as guia, SUM(valueagente) AS agente
                                   FROM `ct_createfaturacredit` where numbervoucher = :voucher ');
                        $financeiroReserva->execute(array(":voucher" =>  $registrosAdd['numbervoucher']));
                        $dadosFinanceiro = $financeiroReserva->fetch( PDO::FETCH_ASSOC );

                        $somaDosServicos2 = $somaDosServicos2 + ( ( $registrosAdd['valueservice'] * $registrosAdd['qpax'] ) +
                                ( ($registrosAdd['valueservice'] / 2) * $registrosAdd['qchild'] ) );
                        ?>
                        <tr>
                            <td><?php echo( date("d/m/Y", strtotime($registrosAdd['abertura']))  ); ?></td>
                            <td><?php echo( date("d/m/Y", strtotime($registrosAdd['dateinput']))  ); ?></td>
                            <td><?php echo( date("H:i", strtotime($registrosAdd['horaap']))  ); ?></td>
                            <td><?php echo( $registrosAdd['numbervoucher'] ); ?></td>
                            <td><?php echo( utf8_decode($registrosAdd['cliente'])  ); ?></td>
                            <td><?php echo( utf8_decode($registrosAdd['documento'])  ); ?></td>
                            <td><?php echo( utf8_decode($registrosAdd['pax'])  ); ?></td>
                            <td><?php echo( $registrosAdd['qpax']."/".$registrosAdd['qchild'] ); ?></td>
                            <td><?php echo( $registrosAdd['firstname'] ); ?></td>
                            <td><?php echo( utf8_decode($registrosAdd['servico'])  ); ?></td>
                            <td><?php echo( $registrosAdd['pagamento'] ); ?></td>
                            <td>
                                <?php echo( "R$ ".number_format( ( ( $registrosAdd['valueservice'] * $registrosAdd['qpax'] ) +
                                        ( ($registrosAdd['valueservice'] / 2) * $registrosAdd['qchild'] ) ),2,",","." ) ); ?>
                            </td>
                            <td><?php echo( "R$ ".number_format($dadosFinanceiro['guia'] + $dadosFinanceiro['agente'],2,",","." )  ); ?></td>
                            <td>
                                <?php echo( "R$ ".number_format( ( ( $registrosAdd['valueservice'] * $registrosAdd['qpax'] ) +
                                            ( ($registrosAdd['valueservice'] / 2) * $registrosAdd['qchild'] ) ) - ($dadosFinanceiro['guia'] +
                                            $dadosFinanceiro['agente']) ,2,",","." ) ); ?>
                            </td>
                            <td>-</td>
                            <td><?php echo( $registrosAdd['statuus'] ); ?></td>
                        </tr>
                    <?php }?>
                <?php }?>

            <?php }?>


            </tbody>

        </table>
        <table class="highlight">
            <thead>
            <tr>
                <th>Valor total</th>
                <th>Total Pago</th>
                <th>A pagar</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td><?php echo("R$ ".number_format( $somaDosServicos1+$somaDosServicos2, 2, ",", "." ) ) ?></td>
                <td><?php echo("R$ ".number_format( $totalPago, 2, ",", "." ) ) ?></td>
                <td><?php echo("R$ ".number_format(  ( $somaDosServicos1+$somaDosServicos2 ) - $totalPago, 2, ",", "." ) ) ?></td>
            </tr>
            </tbody>
        </table>
    </div>
    </body>
    </html>
<?php } else{?>
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta charset="utf-8">
        <title><?php echo( utf8_decode( "Relatório de Fatura" ) ); ?></title>
        <link rel="stylesheet" href="materialize.min.css">
    </head>
    <style>
        table{font-size: 10px;}
        th, td{border: 1px solid #ddd; padding: 8px;}
    </style>
    <body>
    <div class="container">
        <img style="width: 700px;" id="logo" src="../../images/logo.png"/>
        <hr>
        <p><?php echo( utf8_decode( "Relatório resumido dos clientes no periodo de : ".
                    date("d/m/Y", strtotime( $datainicio ))." até ").date("d/m/Y", strtotime( $datafim ))); ?> </p><br>
        <table>
            <thead>
            <tr>
                <th>Cliente</th>
                <th>Total</th>
                <th>Recebido</th>
                <th>A pagar</th>
            </tr>
            </thead>
            <tbody>
            <?php if( $status[0] == 0 ){
                if( $cliente == 0 )
                {
                    $buscarClientePeriodo = $pdo->prepare(
                        "select idcliente, fullname from `ct_reserva` r left join `ct_cliente` c on c.id = r.idcliente
     where r.dateinput >= :inicio and r.dateinput <= :fim and c.fullname not like 'cassi%'  group by r.idcliente");
                    $buscarClientePeriodo->execute( array( ":inicio" => $datainicio, ":fim" => $datafim ) );
                }else
                {
                    $buscarClientePeriodo = $pdo->prepare(
                        "select idcliente, fullname from `ct_reserva` r left join `ct_cliente` c on c.id = r.idcliente
     where r.dateinput >= :inicio and r.dateinput <= :fim  and r.idcliente = :cliente group by r.idcliente");
                    $buscarClientePeriodo->execute( array( ":inicio" => $datainicio, ":fim" => $datafim, ":cliente" => $cliente ) );
                }
                $registro        = $buscarClientePeriodo->fetchAll(PDO::FETCH_CLASS);

                $totalClienteAdd = 0;
                ?>
                <?php foreach ($registro as $key) {
                    $reservasDoCliente = $pdo->prepare(
                        "select SUM((valueservice * qtdpax) + ((valueservice / 2) *  qtdchild)) as totalCliente
                                from `ct_reserva` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idcliente = :cliente");
                    $reservasDoCliente->execute( array(":inicio" => $datainicio, ":fim" => $datafim ,":cliente" => $key->idcliente) );
                    $registroReservasCliente = $reservasDoCliente->fetch(PDO::FETCH_ASSOC);

                    $buscarCreditosReservasCliente = $pdo->prepare(
                        "select SUM(cfc.valuecredit) as totalCredito from `ct_reserva` r left join `ct_createfaturacredit` cfc 
                                    on r.numbervoucher = cfc.numbervoucher  where r.dateinput >= :inicio  and r.dateinput <= :fim  and r.idcliente = :cliente and r.`idstatusinvoice` = :st ");
                    $buscarCreditosReservasCliente->execute( array( ":inicio" => $datainicio, ":fim" => $datafim ,":cliente" => $key->idcliente, ":st" => 3) );
                    $totalCreditoCliente = $buscarCreditosReservasCliente->fetch(PDO::FETCH_ASSOC);

                    $reservasDoClienteAdd = $pdo->prepare(
                        "select SUM((ra.valueservice * ra.qpax) + ((ra.valueservice / 2) *  ra.qchild)) as totalAdd from `ct_recentlyadd` ra 
                    left join `ct_reserva` r on r.id = ra.idrecently where ra.dateinput >= :inicio and ra.dateinput <= :fim and r.idcliente = :cliente ");
                    $reservasDoClienteAdd->execute( array(":inicio" => $datainicio, ":fim" => $datafim ,":cliente" => $key->idcliente) );
                    $registroReservasClienteAdd = $reservasDoClienteAdd->fetch(PDO::FETCH_ASSOC);
                    $total     = $registroReservasCliente['totalCliente'] + $registroReservasClienteAdd['totalAdd'];
                    $resultado = ($registroReservasCliente['totalCliente'] + $registroReservasClienteAdd['totalAdd']) - $totalCreditoCliente['totalCredito'];

                    ?>
                    <?php if($total > 0){ ?>
                        <tr>
                            <td><?php echo( utf8_decode( $key->fullname ) ); ?></td>
                            <td><?php
                                echo( "R$ ".number_format($total, 2, ",", ".")); ?>
                            </td>
                            <td>
                                <?php echo("R$ ".number_format($totalCreditoCliente['totalCredito'], 2, ",", ".") ); ?>
                            </td>
                            <td>
                                <?php echo("R$ ".number_format($resultado, 2,",", ".") ); ?>
                            </td>
                        </tr>
                    <?php }?>

                <?php }?>
            <?php } else{ ?>
                <?php for( $contadorStatus = 0; $contadorStatus < count($status); $contadorStatus++ ){

                    if($status[$contadorStatus] == 0){
                        if( $cliente == 0 )
                        {
                            $buscarClientePeriodo = $pdo->prepare(
                                "select idcliente, fullname from `ct_reserva` r left join `ct_cliente` c on c.id = r.idcliente
     where r.dateinput >= :inicio and r.dateinput <= :fim and c.fullname not like 'cassi%'  group by r.idcliente");
                            $buscarClientePeriodo->execute( array( ":inicio" => $datainicio, ":fim" => $datafim ) );
                        }else
                        {
                            $buscarClientePeriodo = $pdo->prepare(
                                "select idcliente, fullname from `ct_reserva` r left join `ct_cliente` c on c.id = r.idcliente
     where r.dateinput >= :inicio and r.dateinput <= :fim  and r.idcliente = :cliente group by r.idcliente");
                            $buscarClientePeriodo->execute( array( ":inicio" => $datainicio, ":fim" => $datafim, ":cliente" => $cliente ) );
                        }

                    }
                    else{
                        if( $cliente == 0 )
                        {
                            $buscarClientePeriodo = $pdo->prepare(
                                "select idcliente, fullname from `ct_reserva` r left join `ct_cliente` c on c.id = r.idcliente
                          where r.dateinput >= :inicio and r.dateinput <= :fim and c.fullname not like 'cassi%' and r.`idstatusinvoice` = :st  group by r.idcliente order by fullname ");
                            $buscarClientePeriodo->execute( array( ":inicio" => $datainicio, ":fim" => $datafim, ":st" => $status[$contadorStatus] ) );
                        }else{
                                $buscarClientePeriodo = $pdo->prepare(
                                "select idcliente, fullname from `ct_reserva` r left join `ct_cliente` c on c.id = r.idcliente
                          where r.dateinput >= :inicio and r.dateinput <= :fim  and r.`idstatusinvoice` = :st  and r.idcliente = :cliente group by r.idcliente");
                            $buscarClientePeriodo->execute(
                                array( ":inicio" => $datainicio, ":fim" => $datafim, ":st" => $status[$contadorStatus], ":cliente" => $cliente ) );
                        }
                    }
                    $registro        = $buscarClientePeriodo->fetchAll(PDO::FETCH_CLASS);

                    ?>
                    <?php foreach ($registro as $key) {
                        if($status[$contadorStatus] == 0)
                        {
                            $reservasDoCliente = $pdo->prepare(
                                "select SUM((valueservice * qtdpax) ) as totalCliente
                                from `ct_reserva` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idcliente = :cliente");
                            $reservasDoCliente->execute( array(":inicio" => $datainicio, ":fim" => $datafim ,":cliente" => $key->idcliente) );
                            $registroReservasCliente = $reservasDoCliente->fetch(PDO::FETCH_ASSOC);

                            $reservasDoCliente1 = $pdo->prepare(
                                "select SUM(((valueservice / 2) *  qtdchild)) as totalCliente
                                from `ct_reserva` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idcliente = :cliente");
                            $reservasDoCliente1->execute( array(":inicio" => $datainicio, ":fim" => $datafim ,":cliente" => $key->idcliente) );
                            $registroReservasCliente1 = $reservasDoCliente1->fetch(PDO::FETCH_ASSOC);

                            $buscarCreditosReservasCliente = $pdo->prepare(
                                "select SUM(cfc.valuecredit) as totalCredito from `ct_reserva` r left join `ct_createfaturacredit` cfc 
                                    on r.numbervoucher = cfc.numbervoucher  where r.dateinput >= :inicio  and r.dateinput <= :fim  and r.idcliente = :cliente and r.`idstatusinvoice` = :st ");
                            $buscarCreditosReservasCliente->execute( array( ":inicio" => $datainicio, ":fim" => $datafim ,":cliente" => $key->idcliente, ":st" => 3) );
                            $totalCreditoCliente = $buscarCreditosReservasCliente->fetch(PDO::FETCH_ASSOC);

                            $reservasDoClienteAdd = $pdo->prepare(
                                "select SUM((ra.valueservice * ra.qpax)) as totalAdd from `ct_recentlyadd` ra 
                                           left join `ct_reserva` r on r.id = ra.idrecently where ra.dateinput >= :inicio and ra.dateinput <= :fim and r.idcliente = :cliente ");
                            $reservasDoClienteAdd->execute( array(":inicio" => $datainicio, ":fim" => $datafim ,":cliente" => $key->idcliente) );
                            $registroReservasClienteAdd = $reservasDoClienteAdd->fetch(PDO::FETCH_ASSOC);

                            $reservasDoClienteAdd1 = $pdo->prepare(
                                "select SUM(((ra.valueservice / 2) *  ra.qchild)) as totalAdd from `ct_recentlyadd` ra 
                                           left join `ct_reserva` r on r.id = ra.idrecently where ra.dateinput >= :inicio and ra.dateinput <= :fim and r.idcliente = :cliente ");
                            $reservasDoClienteAdd1->execute( array(":inicio" => $datainicio, ":fim" => $datafim ,":cliente" => $key->idcliente) );
                            $registroReservasClienteAdd1 = $reservasDoClienteAdd1->fetch(PDO::FETCH_ASSOC);

                        }
                        else{
                            $reservasDoCliente = $pdo->prepare(
                                "select SUM((valueservice * qtdpax) ) as totalCliente
                                from `ct_reserva` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idcliente = :cliente and r.`idstatusinvoice` = :st");
                            $reservasDoCliente->execute(
                                array(":inicio" => $datainicio, ":fim" => $datafim ,":cliente" => $key->idcliente, ":st" => $status[$contadorStatus]) );
                            $registroReservasCliente = $reservasDoCliente->fetch(PDO::FETCH_ASSOC);

                            $reservasDoCliente1 = $pdo->prepare(
                                "select SUM(((valueservice / 2) *  qtdchild)) as totalCliente
                                from `ct_reserva` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idcliente = :cliente and r.`idstatusinvoice` = :st");
                            $reservasDoCliente1->execute(
                                array(":inicio" => $datainicio, ":fim" => $datafim ,":cliente" => $key->idcliente, ":st" => $status[$contadorStatus]) );
                            $registroReservasCliente1 = $reservasDoCliente1->fetch(PDO::FETCH_ASSOC);

                            $buscarCreditosReservasCliente = $pdo->prepare(
                                "select SUM(cfc.valuecredit) as totalCredito from `ct_reserva` r left join `ct_createfaturacredit` cfc 
                                    on r.numbervoucher = cfc.numbervoucher  where r.dateinput >= :inicio  and r.dateinput <= :fim  and r.idcliente = :cliente
                                    and r.`idstatusinvoice` = :st ");
                            $buscarCreditosReservasCliente->execute(
                                array( ":inicio" => $datainicio, ":fim" => $datafim ,":cliente" => $key->idcliente, ":st" => $status[$contadorStatus] ) );
                            $totalCreditoCliente = $buscarCreditosReservasCliente->fetch(PDO::FETCH_ASSOC);

                            $reservasDoClienteAdd = $pdo->prepare(
                                "select SUM((ra.valueservice * ra.qpax)) as totalAdd from `ct_recentlyadd` ra 
                                          left join `ct_reserva` r on r.id = ra.idrecently where ra.dateinput >= :inicio and ra.dateinput <= :fim and r.idcliente = :cliente 
                                          and r.`idstatusinvoice` = :st ");
                            $reservasDoClienteAdd->execute( array(":inicio" => $datainicio, ":fim" => $datafim ,":cliente" => $key->idcliente, ":st" => $status[$contadorStatus]) );
                            $registroReservasClienteAdd = $reservasDoClienteAdd->fetch(PDO::FETCH_ASSOC);

                            $reservasDoClienteAdd1 = $pdo->prepare(
                                "select SUM(((ra.valueservice / 2) *  ra.qchild)) as totalAdd from `ct_recentlyadd` ra 
                                          left join `ct_reserva` r on r.id = ra.idrecently where ra.dateinput >= :inicio and ra.dateinput <= :fim and r.idcliente = :cliente 
                                          and r.`idstatusinvoice` = :st ");
                            $reservasDoClienteAdd1->execute(
                                    array(":inicio" => $datainicio, ":fim" => $datafim ,":cliente" => $key->idcliente, ":st" => $status[$contadorStatus]) );
                            $registroReservasClienteAdd1 = $reservasDoClienteAdd1->fetch(PDO::FETCH_ASSOC);

                        }
                        $total     = ( ( $registroReservasCliente['totalCliente'] + $registroReservasClienteAdd['totalAdd'] )
                            + ($registroReservasCliente1['totalCliente'] + $registroReservasClienteAdd1['totalAdd'] ) ) ;
                        $resultado = $total - $totalCreditoCliente['totalCredito'];

                        ?>
                        <?php if($total > 0){ ?>
                            <tr>
                                <td><?php echo( utf8_decode( $key->fullname ) ); ?></td>
                                <td><?php
                                    echo( "R$ ".number_format($total, 2, ",", ".")); ?>
                                </td>
                                <td>
                                    <?php echo("R$ ".number_format($totalCreditoCliente['totalCredito'], 2, ",", ".") ); ?>
                                </td>
                                <td>
                                    <?php echo("R$ ".number_format($resultado, 2,",", ".") ); ?>
                                </td>
                            </tr>
                        <?php }?>
                    <?php }?>

                 <?php }?>

            <?php }?>

            </tbody>
        </table>
    </div>


    </body>
    </html>



<?php  }?>
<?php
if( $tipo == 0 )
{
    $html = ob_get_clean();
    $arquivo = "Fatura-Conferência-".date("d/m/Y", strtotime($datainicio) )."--".date("d/m/Y", strtotime($datafim) ).".pdf" ;
    define( '_MPDF_TTFONTDATAPATH', sys_get_temp_dir() );
    require_once( 'pdf/mpdf.php' );
    $mpdf = new mPDF('utf-8', 'A4-L');
    $mpdf->SetTitle( "relatório" );
    $mpdf->SetAuthor( 'Cassi Turismo' );
    $html = mb_convert_encoding($html, 'UTF-8', 'ISO-8859-1');
    $mpdf->WriteHTML( $html, 0 );
    $mpdf->Output( $arquivo, 'I' );
}else{
    $html = ob_get_clean();
    $arquivo = "Fatura-Conferência-Resumido-".date("d/m/Y", strtotime($datainicio) )."--".date("d/m/Y", strtotime($datafim) ).".pdf" ;
    define( '_MPDF_TTFONTDATAPATH', sys_get_temp_dir() );
    require_once( 'pdf/mpdf.php' );
    $mpdf = new mPDF();
    $mpdf->SetTitle( "relatório" );
    $mpdf->SetAuthor( 'Cassi Turismo' );
    $html = mb_convert_encoding($html, 'UTF-8', 'ISO-8859-1');
    $mpdf->WriteHTML( $html, 0 );
    $mpdf->Output( $arquivo, 'I' );
}


?>

