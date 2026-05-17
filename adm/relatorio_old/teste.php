<?php
require_once( '../.././config.php' );
ob_start();
define('MPDP_PATH', 'MPDF54/');
$abertura        = $_POST['abertura'];
$aberturaFinal   = $_POST['aberturafinal'];
$cliente         = $_POST['cliente'];
$responsavel     = $_POST['responsavel'];
$idservicos      = $_POST['servico'];
$tipo            = $_POST['tiporelatorio'];

if($cliente > 0){
    $nomeCliente = $pdo->prepare('select * from `ct_cliente` where `id` = :id ');
    $nomeCliente->execute(array(":id" => $cliente));
    $nomeDoCliente = $nomeCliente->fetch(PDO::FETCH_ASSOC);
}


$somaDosServicos1 = 0;
$somaDosServicos2 = 0;
$totalPago        = 0;
ob_clean();
?>
<style>
    th,td{font-size: 12px;}
</style>
<?php if($tipo == 0){ ?>
    <html xmlns="http://www.w3.org/1999/xhtml">
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
        <p><?php echo( utf8_decode( "Relatório de Conferência de abertura dos voucher de: ".
                date("d/m/Y ", strtotime( $abertura ))." ate ".date("d/m/Y ", strtotime( $aberturaFinal )))); ?> </p><br>
        <p style="font-size: 9px; margin-top: -20px;">Impresso em: <?php echo(date("d/m/Y - H:i:s")); ?></p>
        <?php if($cliente > 0){?>
            <h6><?php echo("Cliente: ".utf8_decode( $nomeDoCliente['fullname'] )); ?></h6>
        <?php }?>

        <table class="highlight">
            <thead>
            <tr>
                <th>EMBARQUE</th>
                <th>VOUCHER</th>
                <th>CLIENTE</th>
                <th>PAX</th>
                <th>P/C</th>
                <th>RES</th>
                <th>SERVICO</th>
                <th>PAGAMENTO</th>
                <th>BRUTO</th>
                <th>COMISSAO</th>
                <th>LIQUIDO</th>
                <th>RECEBIDO</th>
                <th>STATUS</th>
            </tr>
            </thead>
            <tbody>
            <?php if( $idservicos[0] == 0 ) {
                if($cliente == 0)
                {
                    if( $responsavel == 0 )
                    {
                        $buscarConferencia = $pdo->prepare(
                            'select r.id, dateinput, dateoutput ,numbervoucher, c.namefantazia as cliente, pax, u.firstname, u.lastname, s.fullname as servico,
                                  cp.namepayment as pagamento, qtdpax, qtdchild, nameinvoice as statuu, r.valueservice, r.idstatusinvoice
                                  from `ct_reserva` r  left join `ct_cliente` c on c.`id` = r.`idcliente` left join `ct_usuario` u on u.`id` = r.`idresponsavel`
                                  left join `ct_servico` s on s.`id` = r.`idservico` left join `ct_form_of_ payment` cp on cp.`id` = r.`idpayment`
                                  left join `ct_statusinvoice` si on si.`id` = r.`idstatusinvoice` where r.abertura >= :abertura and r.abertura <= :aberturafinal
                                  order by r.numbervoucher ');
                        $buscarConferencia->execute( array( ":abertura" => $abertura,":aberturafinal" => $aberturaFinal ) );

                    }else
                    {
                        $buscarConferencia = $pdo->prepare(
                            'select r.id, dateinput, dateoutput ,numbervoucher, c.namefantazia as cliente, pax, u.firstname, u.lastname, s.fullname as servico,
                                  cp.namepayment as pagamento, qtdpax, qtdchild, nameinvoice as statuu, r.valueservice, r.idstatusinvoice
                                  from `ct_reserva` r  left join `ct_cliente` c on c.`id` = r.`idcliente` left join `ct_usuario` u on u.`id` = r.`idresponsavel`
                                  left join `ct_servico` s on s.`id` = r.`idservico` left join `ct_form_of_ payment` cp on cp.`id` = r.`idpayment` 
                                  left join `ct_statusinvoice` si on si.`id` = r.`idstatusinvoice` where r.abertura >= :abertura and r.abertura <= :aberturafinal
                                  and r.idresponsavel = :res order by r.numbervoucher ');
                        $buscarConferencia->execute(
                            array(
                                ":abertura" => $abertura,":aberturafinal" => $aberturaFinal,
                                ":res" => $responsavel
                            )
                        );
                    }

                }else{
                    if($responsavel == 0)
                    {
                        $buscarConferencia = $pdo->prepare(
                            'select r.id, dateinput, dateoutput ,numbervoucher, c.namefantazia as cliente, pax, u.firstname, u.lastname, s.fullname as servico,
                                   cp.namepayment as pagamento, qtdpax, qtdchild, nameinvoice as statuu, r.valueservice, r.idstatusinvoice
                                   from `ct_reserva` r  left join `ct_cliente` c on c.`id` = r.`idcliente` left join `ct_usuario` u on u.`id` = r.`idresponsavel`
                                   left join `ct_servico` s on s.`id` = r.`idservico` left join `ct_form_of_ payment` cp on cp.`id` = r.`idpayment` 
                                   left join `ct_statusinvoice` si on si.`id` = r.`idstatusinvoice` where r.abertura >= :abertura and r.idcliente = :cliente
                                   and r.abertura <= :aberturafinal order by r.numbervoucher '
                        );
                        $buscarConferencia->execute(array(
                            ":abertura" => $abertura, ":cliente" => $cliente,
                            ":aberturafinal" => $aberturaFinal ) );

                    }else
                    {
                        $buscarConferencia = $pdo->prepare(
                            'select r.id, dateinput, dateoutput ,numbervoucher, c.namefantazia as cliente, pax, u.firstname, u.lastname, s.fullname as servico,
                                  cp.namepayment as pagamento, qtdpax, qtdchild, nameinvoice as statuu, r.valueservice, r.idstatusinvoice
                                  from `ct_reserva` r  left join `ct_cliente` c on c.`id` = r.`idcliente` left join `ct_usuario` u on u.`id` = r.`idresponsavel`
                                  left join `ct_servico` s on s.`id` = r.`idservico` left join `ct_form_of_ payment` cp on cp.`id` = r.`idpayment` 
                                  left join `ct_statusinvoice` si on si.`id` = r.`idstatusinvoice` where r.abertura >= :abertura and r.idcliente = :cliente
                                  and r.abertura <= :aberturafinal and r.idresponsavel = :res order by r.numbervoucher ');
                        $buscarConferencia->execute(
                            array(
                                ":abertura" => $abertura, ":cliente" => $cliente,
                                ":aberturafinal" => $aberturaFinal, ":res" => $responsavel) );
                    }
                }
                $registro     = $buscarConferencia->fetchAll( PDO::FETCH_CLASS );
                $dadosCliente = $buscarConferencia->fetch( PDO::FETCH_ASSOC );

                ?>
                <?php foreach( $registro as $item ) {
                    $financeiroReserva = $pdo->prepare(
                        'SELECT tarifa, SUM(valuecredit) as credito, SUM(valueguia) as guia, SUM(valueagente) AS agente
                              FROM `ct_createfaturacredit` where numbervoucher = :voucher ');
                    $financeiroReserva->execute(array(":voucher" =>  $item->numbervoucher));
                    $dadosFinanceiro = $financeiroReserva->fetch( PDO::FETCH_ASSOC );

                    $auditoria = $pdo->prepare(
                        'insert into `ct_audit` (`id`, `idresponsible`, `voucher`, `description`, `date`) values (DEFAULT, :resp, :voucher, :des, :dataa) ');
                    $auditoria->execute( array(
                        ":resp"    => $_SESSION['id'],
                        ":voucher" => $item->numbervoucher,
                        ":des"     => "Incluido no relatório de abertura por: ",
                        ":dataa"   => date("Y-m-d H:i:s" )) );

                    $descricaoPagamento = $pdo->prepare(
                        'SELECT datacredit as dia, `name` as pagamento, valuecredit as valor, u.firstname, u.lastname  FROM `ct_createfaturacredit` cfc 
                                  left join `ct_currentaccount` cc on cc.id = cfc.idaccountcurrent left join `ct_usuario` u on cfc.idusr = u.id where numbervoucher = :voucher');
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

                    $buscarConferenciaAdd = $pdo->prepare(
                        'select  ra.dateinput, ra.dateoutput, numbervoucher, c.namefantazia as cliente, pax, u.firstname, u.lastname, s.fullname as servico,
                                cp.namepayment as pagamento, qpax, qchild, nameinvoice as statuus, ra.valueservice
                                from `ct_recentlyadd` ra left join `ct_reserva` r on ra.`idrecently` = r.id left join `ct_servico`
                                s on ra.idservice = s.id  left join `ct_cliente` c on c.`id` = r.`idcliente` left join `ct_usuario` u on u.`id` = r.`idresponsavel`
                                left join `ct_form_of_ payment` cp on cp.`id` = r.`idpayment` left join `ct_statusinvoice` si on si.`id` = r.`idstatusinvoice`
                                where r.abertura >= :abertura  and r.abertura <= :aberturafinal and ra.idrecently = :id order by r.numbervoucher ');
                    $buscarConferenciaAdd->execute(
                        array(
                            ":abertura" => $abertura,":aberturafinal" => $aberturaFinal,
                            ":id" => $item->id)
                    );
                    $registoAdd   = $buscarConferenciaAdd->fetchAll( PDO::FETCH_CLASS );
                    ?>
                    <tr>
                        <td><?php echo( date("d/m/Y", strtotime($item->dateinput))  ); ?></td>
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

                    <?php foreach( $registoAdd as $item2 )
                    {
                        $somaDosServicos2 = $somaDosServicos2 + ( ( $item2->valueservice * $item2->qpax ) +
                                ( ($item2->valueservice / 2) * $item2->qchild ) );
                        ?>
                        <tr>
                            <td><?php echo( date("d/m/Y", strtotime($item2->dateinput))  ); ?></td>
                            <td><?php echo( $item2->numbervoucher ); ?></td>
                            <td><?php echo( utf8_decode($item2->cliente)  ); ?></td>
                            <td><?php echo( utf8_decode($item2->pax)  ); ?></td>
                            <td><?php echo( $item2->qpax."/".$item2->qchild ); ?></td>
                            <td><?php echo( $item2->firstname ); ?></td>
                            <td><?php echo( utf8_decode($item2->servico)  ); ?></td>
                            <td><?php echo( $item2->pagamento ); ?></td>
                            <td>
                                <?php echo( "R$ ".number_format( ( ( $item2->valueservice * $item2->qpax ) +
                                        ( ($item2->valueservice / 2) * $item2->qchild ) ),2,",","." ) ); ?>
                            </td>
                            <td>
                                <?php
                                echo( "R$ ".number_format($dadosFinanceiro['guia'] + $dadosFinanceiro['agente'],2,",","." )  ); ?>
                            </td>
                            <td>
                                <?php echo( "R$ ".number_format( ( ( $item2->valueservice * $item2->qpax) +
                                            ( ($item2->valueservice / 2) * $item2->qchild ) ) - ($dadosFinanceiro['guia'] +
                                            $dadosFinanceiro['agente']) ,2,",","." ) ); ?>
                            </td>
                            <td>-</td>
                            <td><?php echo( $item2->statuus ); ?></td>
                        </tr>
                    <?php }?>

                    <?php if( $contadorDescricaoPagamento >0 ){ ?>
                        <?php foreach ($registroDescricao as $item3){ ?>
                            <tr>
                                <td id="desc" colspan="3"><?php echo("<strong>Data Pagamento</strong> ".date("d-m-Y", strtotime($item3->dia))); ?></td>
                                <td id="desc" colspan="4"><?php echo("<strong>Metodo</strong> ".$item3->pagamento); ?></td>
                                <td id="desc" colspan="2">
                                    <?php echo("<strong>Valor R$ </strong>".number_format($item3->valor, 2,",", ".")); ?>
                                </td>
                                <td id="desc" colspan="4">
                                    <?php echo("<strong>Recebido por </strong>".strtoupper( $item3->firstname." ".$item3->lastname)); ?>
                                </td>
                            </tr>
                        <?php }?>

                    <?php }?>

                <?php }?>

            <?php } else {
                for ($contador = 0; $contador <= count($idservicos); $contador++ ){
                    if($cliente == 0)
                    {
                        if( $responsavel == 0 )
                        {
                            $buscarConferencia = $pdo->prepare(
                                'select r.id, dateinput, dateoutput ,numbervoucher, c.namefantazia as cliente, pax, u.firstname, u.lastname, s.fullname as servico,
                                  cp.namepayment as pagamento, qtdpax, qtdchild, nameinvoice as statuu, r.valueservice, r.idstatusinvoice
                                  from `ct_reserva` r  left join `ct_cliente` c on c.`id` = r.`idcliente` left join `ct_usuario` u on u.`id` = r.`idresponsavel`
                                  left join `ct_servico` s on s.`id` = r.`idservico` left join `ct_form_of_ payment` cp on cp.`id` = r.`idpayment`
                                  left join `ct_statusinvoice` si on si.`id` = r.`idstatusinvoice` where r.abertura >= :abertura and r.abertura <= :aberturafinal
                                  and r.idservico = :servico order by r.numbervoucher ');
                            $buscarConferencia->execute( array( ":abertura" => $abertura,":aberturafinal" => $aberturaFinal,
                                ":servico" => $idservicos[$contador] ) );

                        }else
                        {
                            $buscarConferencia = $pdo->prepare(
                                'select r.id, dateinput, dateoutput ,numbervoucher, c.namefantazia as cliente, pax, u.firstname, u.lastname, s.fullname as servico,
                                  cp.namepayment as pagamento, qtdpax, qtdchild, nameinvoice as statuu, r.valueservice, r.idstatusinvoice
                                  from `ct_reserva` r  left join `ct_cliente` c on c.`id` = r.`idcliente` left join `ct_usuario` u on u.`id` = r.`idresponsavel`
                                  left join `ct_servico` s on s.`id` = r.`idservico` left join `ct_form_of_ payment` cp on cp.`id` = r.`idpayment` 
                                  left join `ct_statusinvoice` si on si.`id` = r.`idstatusinvoice` where r.abertura >= :abertura and r.abertura <= :aberturafinal
                                  and r.idresponsavel = :res and r.idservico = :servico order by r.numbervoucher ');
                            $buscarConferencia->execute(
                                array(
                                    ":abertura" => $abertura,":aberturafinal" => $aberturaFinal,
                                    ":res" => $responsavel, ":servico" => $idservicos[$contador]
                                )
                            );
                        }

                    }else{
                        if($responsavel == 0)
                        {
                            $buscarConferencia = $pdo->prepare(
                                'select r.id, dateinput, dateoutput ,numbervoucher, c.namefantazia as cliente, pax, u.firstname, u.lastname, s.fullname as servico,
                                   cp.namepayment as pagamento, qtdpax, qtdchild, nameinvoice as statuu, r.valueservice, r.idstatusinvoice
                                   from `ct_reserva` r  left join `ct_cliente` c on c.`id` = r.`idcliente` left join `ct_usuario` u on u.`id` = r.`idresponsavel`
                                   left join `ct_servico` s on s.`id` = r.`idservico` left join `ct_form_of_ payment` cp on cp.`id` = r.`idpayment` 
                                   left join `ct_statusinvoice` si on si.`id` = r.`idstatusinvoice` where r.abertura >= :abertura and r.idcliente = :cliente
                                   and r.abertura <= :aberturafinal and r.idservico = :servico order by r.numbervoucher '
                            );
                            $buscarConferencia->execute(array(
                                ":abertura" => $abertura, ":cliente" => $cliente,
                                ":aberturafinal" => $aberturaFinal, ":servico" => $idservicos[$contador] ) );

                        }else
                        {
                            $buscarConferencia = $pdo->prepare(
                                'select r.id, dateinput, dateoutput ,numbervoucher, c.namefantazia as cliente, pax, u.firstname, u.lastname, s.fullname as servico,
                                  cp.namepayment as pagamento, qtdpax, qtdchild, nameinvoice as statuu, r.valueservice, r.idstatusinvoice
                                  from `ct_reserva` r  left join `ct_cliente` c on c.`id` = r.`idcliente` left join `ct_usuario` u on u.`id` = r.`idresponsavel`
                                  left join `ct_servico` s on s.`id` = r.`idservico` left join `ct_form_of_ payment` cp on cp.`id` = r.`idpayment` 
                                  left join `ct_statusinvoice` si on si.`id` = r.`idstatusinvoice` where r.abertura >= :abertura and r.idcliente = :cliente
                                  and r.abertura <= :aberturafinal and r.idresponsavel = :res and r.idservico = :servico order by r.numbervoucher ');
                            $buscarConferencia->execute(
                                array(
                                    ":abertura" => $abertura, ":cliente" => $cliente,
                                    ":aberturafinal" => $aberturaFinal, ":res" => $responsavel,
                                    ":servico" => $idservicos[$contador] ) );
                        }
                    }
                    $registro     = $buscarConferencia->fetchAll( PDO::FETCH_CLASS );
                    $dadosCliente = $buscarConferencia->fetch( PDO::FETCH_ASSOC );

                    ?>
                    <?php foreach( $registro as $item ) {
                        $financeiroReserva = $pdo->prepare(
                            'SELECT tarifa, SUM(valuecredit) as credito, SUM(valueguia) as guia, SUM(valueagente) AS agente
                              FROM `ct_createfaturacredit` where numbervoucher = :voucher ');
                        $financeiroReserva->execute(array(":voucher" =>  $item->numbervoucher));
                        $dadosFinanceiro = $financeiroReserva->fetch( PDO::FETCH_ASSOC );

                        $auditoria = $pdo->prepare(
                            'insert into `ct_audit` (`id`, `idresponsible`, `voucher`, `description`, `date`) values (DEFAULT, :resp, :voucher, :des, :dataa) ');
                        $auditoria->execute( array(
                            ":resp"    => $_SESSION['id'],
                            ":voucher" => $item->numbervoucher,
                            ":des"     => "Incluido no relatório de abertura por: ",
                            ":dataa"   => date("Y-m-d H:i:s" )) );

                        $descricaoPagamento = $pdo->prepare(
                            'SELECT datacredit as dia, `name` as pagamento, valuecredit as valor, u.firstname, u.lastname  FROM `ct_createfaturacredit` cfc 
                                  left join `ct_currentaccount` cc on cc.id = cfc.idaccountcurrent left join `ct_usuario` u on cfc.idusr = u.id where numbervoucher = :voucher');
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

                        $buscarConferenciaAdd = $pdo->prepare(
                            'select  ra.dateinput, ra.dateoutput, numbervoucher, c.namefantazia as cliente, pax, u.firstname, u.lastname, s.fullname as servico,
                                cp.namepayment as pagamento, qpax, qchild, nameinvoice as statuus, ra.valueservice
                                from `ct_recentlyadd` ra left join `ct_reserva` r on ra.`idrecently` = r.id left join `ct_servico`
                                s on ra.idservice = s.id  left join `ct_cliente` c on c.`id` = r.`idcliente` left join `ct_usuario` u on u.`id` = r.`idresponsavel`
                                left join `ct_form_of_ payment` cp on cp.`id` = r.`idpayment` left join `ct_statusinvoice` si on si.`id` = r.`idstatusinvoice`
                                where r.abertura >= :abertura  and r.abertura <= :aberturafinal and ra.idrecently = :id order by r.numbervoucher ');
                        $buscarConferenciaAdd->execute(
                            array(
                                ":abertura" => $abertura,":aberturafinal" => $aberturaFinal,
                                ":id" => $item->id)
                        );
                        $registoAdd   = $buscarConferenciaAdd->fetchAll( PDO::FETCH_CLASS );
                        ?>
                        <tr>
                            <td><?php echo( date("d/m/Y", strtotime($item->dateinput))  ); ?></td>
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

                        <?php foreach( $registoAdd as $item2 )
                        {
                            $somaDosServicos2 = $somaDosServicos2 + ( ( $item2->valueservice * $item2->qpax ) +
                                    ( ($item2->valueservice / 2) * $item2->qchild ) );
                            ?>
                            <tr>
                                <td><?php echo( date("d/m/Y", strtotime($item2->dateinput))  ); ?></td>
                                <td><?php echo( $item2->numbervoucher ); ?></td>
                                <td><?php echo( utf8_decode($item2->cliente)  ); ?></td>
                                <td><?php echo( utf8_decode($item2->pax)  ); ?></td>
                                <td><?php echo( $item2->qpax."/".$item2->qchild ); ?></td>
                                <td><?php echo( $item2->firstname ); ?></td>
                                <td><?php echo( utf8_decode($item2->servico)  ); ?></td>
                                <td><?php echo( $item2->pagamento ); ?></td>
                                <td>
                                    <?php echo( "R$ ".number_format( ( ( $item2->valueservice * $item2->qpax ) +
                                            ( ($item2->valueservice / 2) * $item2->qchild ) ),2,",","." ) ); ?>
                                </td>
                                <td>
                                    <?php
                                    echo( "R$ ".number_format($dadosFinanceiro['guia'] + $dadosFinanceiro['agente'],2,",","." )  ); ?>
                                </td>
                                <td>
                                    <?php echo( "R$ ".number_format( ( ( $item2->valueservice * $item2->qpax) +
                                                ( ($item2->valueservice / 2) * $item2->qchild ) ) - ($dadosFinanceiro['guia'] +
                                                $dadosFinanceiro['agente']) ,2,",","." ) ); ?>
                                </td>
                                <td>-</td>
                                <td><?php echo( $item2->statuus ); ?></td>
                            </tr>
                        <?php }?>

                        <?php if( $contadorDescricaoPagamento >0 ){ ?>
                            <?php foreach ($registroDescricao as $item3){ ?>
                                <?php if( $item3->valor > 0 ){ ?>
                                    <tr>
                                        <td id="desc" colspan="3"><?php echo("<strong>Data Pagamento</strong> ".date("d-m-Y", strtotime($item3->dia))); ?></td>
                                        <td id="desc" colspan="4"><?php echo("<strong>Metodo</strong> ".$item3->pagamento); ?></td>
                                        <td id="desc" colspan="2">
                                            <?php echo("<strong>Valor R$ </strong>".number_format($item3->valor, 2,",", ".")); ?>
                                        </td>
                                        <td id="desc" colspan="4">
                                            <?php echo("<strong>Recebido por </strong>".strtoupper( $item3->firstname." ".$item3->lastname)); ?>
                                        </td>
                                    </tr>
                                <?php }?>

                            <?php }?>

                        <?php }?>

                    <?php }?>

                <?php }?>

                ?>

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
<?php  } else { ?>
    <html xmlns="http://www.w3.org/1999/xhtml">
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
        <p><?php echo( utf8_decode( "Relatório de Conferência de abertura dos voucher de: ".
                date("d/m/Y ", strtotime( $abertura ))." ate ".date("d/m/Y ", strtotime( $aberturaFinal )))); ?> </p><br>
        <p style="font-size: 9px; margin-top: -20px;">Impresso em: <?php echo(date("d/m/Y - H:i:s")); ?></p>
        <?php if($cliente > 0){?>
            <h6><?php echo("Cliente: ".utf8_decode( $nomeDoCliente['fullname'] )); ?></h6>
        <?php }?>

        <table class="highlight">
            <thead>
            <tr>
                <th>EMBARQUE</th>
                <th>VOUCHER</th>
                <th>CLIENTE</th>
                <th>PAX</th>
                <th>P/C</th>
                <th>RES</th>
                <th>SERVICO</th>
                <th>PAGAMENTO</th>
                <th>BRUTO</th>
                <th>RECEBIDO</th>

            </tr>
            </thead>
            <tbody>
            <?php if( $idservicos[0] == 0 ) {
                if($cliente == 0)
                {
                    if( $responsavel == 0 )
                    {
                        $buscarConferencia = $pdo->prepare(
                            'select r.id, dateinput, dateoutput ,numbervoucher, c.namefantazia as cliente, pax, u.firstname, u.lastname, s.fullname as servico,
                                  cp.namepayment as pagamento, qtdpax, qtdchild, nameinvoice as statuu, r.valueservice, r.idstatusinvoice
                                  from `ct_reserva` r  left join `ct_cliente` c on c.`id` = r.`idcliente` left join `ct_usuario` u on u.`id` = r.`idresponsavel`
                                  left join `ct_servico` s on s.`id` = r.`idservico` left join `ct_form_of_ payment` cp on cp.`id` = r.`idpayment`
                                  left join `ct_statusinvoice` si on si.`id` = r.`idstatusinvoice` where r.abertura >= :abertura and r.abertura <= :aberturafinal
                                  order by r.numbervoucher ');
                        $buscarConferencia->execute( array( ":abertura" => $abertura,":aberturafinal" => $aberturaFinal ) );

                    }else
                    {
                        $buscarConferencia = $pdo->prepare(
                            'select r.id, dateinput, dateoutput ,numbervoucher, c.namefantazia as cliente, pax, u.firstname, u.lastname, s.fullname as servico,
                                  cp.namepayment as pagamento, qtdpax, qtdchild, nameinvoice as statuu, r.valueservice, r.idstatusinvoice
                                  from `ct_reserva` r  left join `ct_cliente` c on c.`id` = r.`idcliente` left join `ct_usuario` u on u.`id` = r.`idresponsavel`
                                  left join `ct_servico` s on s.`id` = r.`idservico` left join `ct_form_of_ payment` cp on cp.`id` = r.`idpayment` 
                                  left join `ct_statusinvoice` si on si.`id` = r.`idstatusinvoice` where r.abertura >= :abertura and r.abertura <= :aberturafinal
                                  and r.idresponsavel = :res order by r.numbervoucher ');
                        $buscarConferencia->execute(
                            array(
                                ":abertura" => $abertura,":aberturafinal" => $aberturaFinal,
                                ":res" => $responsavel
                            )
                        );
                    }

                }else{
                    if($responsavel == 0)
                    {
                        $buscarConferencia = $pdo->prepare(
                            'select r.id, dateinput, dateoutput ,numbervoucher, c.namefantazia as cliente, pax, u.firstname, u.lastname, s.fullname as servico,
                                   cp.namepayment as pagamento, qtdpax, qtdchild, nameinvoice as statuu, r.valueservice, r.idstatusinvoice
                                   from `ct_reserva` r  left join `ct_cliente` c on c.`id` = r.`idcliente` left join `ct_usuario` u on u.`id` = r.`idresponsavel`
                                   left join `ct_servico` s on s.`id` = r.`idservico` left join `ct_form_of_ payment` cp on cp.`id` = r.`idpayment` 
                                   left join `ct_statusinvoice` si on si.`id` = r.`idstatusinvoice` where r.abertura >= :abertura and r.idcliente = :cliente
                                   and r.abertura <= :aberturafinal order by r.numbervoucher '
                        );
                        $buscarConferencia->execute(array(
                            ":abertura" => $abertura, ":cliente" => $cliente,
                            ":aberturafinal" => $aberturaFinal ) );

                    }else
                    {
                        $buscarConferencia = $pdo->prepare(
                            'select r.id, dateinput, dateoutput ,numbervoucher, c.namefantazia as cliente, pax, u.firstname, u.lastname, s.fullname as servico,
                                  cp.namepayment as pagamento, qtdpax, qtdchild, nameinvoice as statuu, r.valueservice, r.idstatusinvoice
                                  from `ct_reserva` r  left join `ct_cliente` c on c.`id` = r.`idcliente` left join `ct_usuario` u on u.`id` = r.`idresponsavel`
                                  left join `ct_servico` s on s.`id` = r.`idservico` left join `ct_form_of_ payment` cp on cp.`id` = r.`idpayment` 
                                  left join `ct_statusinvoice` si on si.`id` = r.`idstatusinvoice` where r.abertura >= :abertura and r.idcliente = :cliente
                                  and r.abertura <= :aberturafinal and r.idresponsavel = :res  order by r.numbervoucher ');
                        $buscarConferencia->execute(
                            array(
                                ":abertura" => $abertura, ":cliente" => $cliente,
                                ":aberturafinal" => $aberturaFinal, ":res" => $responsavel) );
                    }
                }
                $registro     = $buscarConferencia->fetchAll( PDO::FETCH_CLASS );
                $dadosCliente = $buscarConferencia->fetch( PDO::FETCH_ASSOC );

                ?>
                <?php foreach( $registro as $item ) {
                    $financeiroReserva = $pdo->prepare(
                        'SELECT tarifa, SUM(valuecredit) as credito, SUM(valueguia) as guia, SUM(valueagente) AS agente
                              FROM `ct_createfaturacredit` where numbervoucher = :voucher ');
                    $financeiroReserva->execute(array(":voucher" =>  $item->numbervoucher));
                    $dadosFinanceiro = $financeiroReserva->fetch( PDO::FETCH_ASSOC );

                    $descricaoPagamento = $pdo->prepare(
                        'SELECT datacredit as dia, `name` as pagamento, valuecredit as valor, u.firstname, u.lastname  FROM `ct_createfaturacredit` cfc 
                                  left join `ct_currentaccount` cc on cc.id = cfc.idaccountcurrent left join `ct_usuario` u on cfc.idusr = u.id where numbervoucher = :voucher');
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

                    $buscarConferenciaAdd = $pdo->prepare(
                        'select  ra.dateinput, ra.dateoutput, numbervoucher, c.namefantazia as cliente, pax, u.firstname, u.lastname, s.fullname as servico,
                                cp.namepayment as pagamento, qpax, qchild, nameinvoice as statuus, ra.valueservice
                                from `ct_recentlyadd` ra left join `ct_reserva` r on ra.`idrecently` = r.id left join `ct_servico`
                                s on ra.idservice = s.id  left join `ct_cliente` c on c.`id` = r.`idcliente` left join `ct_usuario` u on u.`id` = r.`idresponsavel`
                                left join `ct_form_of_ payment` cp on cp.`id` = r.`idpayment` left join `ct_statusinvoice` si on si.`id` = r.`idstatusinvoice`
                                where r.abertura >= :abertura  and r.abertura <= :aberturafinal and ra.idrecently = :id order by r.numbervoucher ');
                    $buscarConferenciaAdd->execute(
                        array(
                            ":abertura" => $abertura,":aberturafinal" => $aberturaFinal,
                            ":id" => $item->id)
                    );
                    $registoAdd   = $buscarConferenciaAdd->fetchAll( PDO::FETCH_CLASS );
                    ?>
                    <tr>
                        <td><?php echo( date("d/m/Y", strtotime($item->dateinput))  ); ?></td>
                        <td><?php echo( $item->numbervoucher ); ?></td>
                        <td><?php echo( utf8_decode($item->cliente)   ); ?></td>
                        <td><?php echo( utf8_decode($item->pax)  ); ?></td>
                        <td><?php echo( $item->qtdpax."/".$item->qtdchild); ?></td>
                        <td><?php echo( utf8_decode($item->firstname." ". $item->lastname)  ); ?></td>
                        <td><?php echo( $item->servico ); ?></td>
                        <td><?php echo( $item->pagamento ); ?></td>
                        <td><?php echo( "R$ ".number_format( $valorBruto,2,",","." ) ); ?></td>
                        <td><?php echo( "R$ ".number_format( $dadosFinanceiro['credito'],2,",","." ) ); ?></td>
                    </tr>

                    <?php foreach( $registoAdd as $item2 )
                    {
                        $somaDosServicos2 = $somaDosServicos2 + ( ( $item2->valueservice * $item2->qpax ) +
                                ( ($item2->valueservice / 2) * $item2->qchild ) );
                        ?>
                        <tr>
                            <td><?php echo( date("d/m/Y", strtotime($item2->dateinput))  ); ?></td>
                            <td><?php echo( $item2->numbervoucher ); ?></td>
                            <td><?php echo( utf8_decode($item2->cliente)  ); ?></td>
                            <td><?php echo( utf8_decode($item2->pax)  ); ?></td>
                            <td><?php echo( $item2->qpax."/".$item2->qchild ); ?></td>
                            <td><?php echo( $item2->firstname ); ?></td>
                            <td><?php echo( utf8_decode($item2->servico)  ); ?></td>
                            <td><?php echo( $item2->pagamento ); ?></td>
                            <td>
                                <?php echo( "R$ ".number_format( ( ( $item2->valueservice * $item2->qpax ) +
                                        ( ($item2->valueservice / 2) * $item2->qchild ) ),2,",","." ) ); ?>
                            </td>
                            <td>-</td>
                        </tr>
                    <?php }?>

                    <?php if( $contadorDescricaoPagamento >0 ){ ?>
                        <?php foreach ($registroDescricao as $item3){ ?>
                            <?php if( $item3->valor > 0 ){ ?>
                                <tr>
                                    <td id="desc" colspan="3"><?php echo("<strong>Data Pagamento</strong> ".date("d-m-Y", strtotime($item3->dia))); ?></td>
                                    <td id="desc" colspan="4"><?php echo("<strong>Metodo</strong> ".$item3->pagamento); ?></td>
                                    <td id="desc" colspan="2">
                                        <?php echo("<strong>Valor R$ </strong>".number_format($item3->valor, 2,",", ".")); ?>
                                    </td>
                                    <td id="desc" colspan="4">
                                        <?php echo("<strong>Recebido por </strong>".strtoupper( $item3->firstname." ".$item3->lastname)); ?>
                                    </td>
                                </tr>
                            <?php }?>
                        <?php }?>

                    <?php }?>

                <?php }?>

            <?php } else {
                for ($contador = 0; $contador <= count($idservicos); $contador++ ){
                    if($cliente == 0)
                    {
                        if( $responsavel == 0 )
                        {
                            $buscarConferencia = $pdo->prepare(
                                'select r.id, dateinput, dateoutput ,numbervoucher, c.namefantazia as cliente, pax, u.firstname, u.lastname, s.fullname as servico,
                                  cp.namepayment as pagamento, qtdpax, qtdchild, nameinvoice as statuu, r.valueservice, r.idstatusinvoice
                                  from `ct_reserva` r  left join `ct_cliente` c on c.`id` = r.`idcliente` left join `ct_usuario` u on u.`id` = r.`idresponsavel`
                                  left join `ct_servico` s on s.`id` = r.`idservico` left join `ct_form_of_ payment` cp on cp.`id` = r.`idpayment`
                                  left join `ct_statusinvoice` si on si.`id` = r.`idstatusinvoice` where r.abertura >= :abertura and r.abertura <= :aberturafinal
                                  and r.idservico = :servico order by r.numbervoucher ');
                            $buscarConferencia->execute( array( ":abertura" => $abertura,":aberturafinal" => $aberturaFinal, ":servico" => $idservicos[$contador] ) );

                        }else
                        {
                            $buscarConferencia = $pdo->prepare(
                                'select r.id, dateinput, dateoutput ,numbervoucher, c.namefantazia as cliente, pax, u.firstname, u.lastname, s.fullname as servico,
                                  cp.namepayment as pagamento, qtdpax, qtdchild, nameinvoice as statuu, r.valueservice, r.idstatusinvoice
                                  from `ct_reserva` r  left join `ct_cliente` c on c.`id` = r.`idcliente` left join `ct_usuario` u on u.`id` = r.`idresponsavel`
                                  left join `ct_servico` s on s.`id` = r.`idservico` left join `ct_form_of_ payment` cp on cp.`id` = r.`idpayment` 
                                  left join `ct_statusinvoice` si on si.`id` = r.`idstatusinvoice` where r.abertura >= :abertura and r.abertura <= :aberturafinal
                                  and r.idresponsavel = :res and r.idservico = :servico order by r.numbervoucher ');
                            $buscarConferencia->execute(
                                array(
                                    ":abertura" => $abertura,":aberturafinal" => $aberturaFinal,
                                    ":res" => $responsavel, ":servico" => $idservicos[$contador]
                                )
                            );
                        }

                    }else{
                        if($responsavel == 0)
                        {
                            $buscarConferencia = $pdo->prepare(
                                'select r.id, dateinput, dateoutput ,numbervoucher, c.namefantazia as cliente, pax, u.firstname, u.lastname, s.fullname as servico,
                                   cp.namepayment as pagamento, qtdpax, qtdchild, nameinvoice as statuu, r.valueservice, r.idstatusinvoice
                                   from `ct_reserva` r  left join `ct_cliente` c on c.`id` = r.`idcliente` left join `ct_usuario` u on u.`id` = r.`idresponsavel`
                                   left join `ct_servico` s on s.`id` = r.`idservico` left join `ct_form_of_ payment` cp on cp.`id` = r.`idpayment` 
                                   left join `ct_statusinvoice` si on si.`id` = r.`idstatusinvoice` where r.abertura >= :abertura and r.idcliente = :cliente
                                   and r.abertura <= :aberturafinal and r.idservico = :servico  order by r.numbervoucher '
                            );
                            $buscarConferencia->execute(array(
                                ":abertura" => $abertura, ":cliente" => $cliente,
                                ":aberturafinal" => $aberturaFinal, ":servico" => $idservicos[$contador] ) );

                        }else
                        {
                            $buscarConferencia = $pdo->prepare(
                                'select r.id, dateinput, dateoutput ,numbervoucher, c.namefantazia as cliente, pax, u.firstname, u.lastname, s.fullname as servico,
                                  cp.namepayment as pagamento, qtdpax, qtdchild, nameinvoice as statuu, r.valueservice, r.idstatusinvoice
                                  from `ct_reserva` r  left join `ct_cliente` c on c.`id` = r.`idcliente` left join `ct_usuario` u on u.`id` = r.`idresponsavel`
                                  left join `ct_servico` s on s.`id` = r.`idservico` left join `ct_form_of_ payment` cp on cp.`id` = r.`idpayment` 
                                  left join `ct_statusinvoice` si on si.`id` = r.`idstatusinvoice` where r.abertura >= :abertura and r.idcliente = :cliente
                                  and r.abertura <= :aberturafinal and r.idresponsavel = :res and r.idservico = :servico   order by r.numbervoucher ');
                            $buscarConferencia->execute(
                                array(
                                    ":abertura" => $abertura, ":cliente" => $cliente,
                                    ":aberturafinal" => $aberturaFinal, ":res" => $responsavel,
                                    ":servico" => $idservicos[$contador] ) );
                        }
                    }
                    $registro     = $buscarConferencia->fetchAll( PDO::FETCH_CLASS );
                    $dadosCliente = $buscarConferencia->fetch( PDO::FETCH_ASSOC );

                    ?>
                    <?php foreach( $registro as $item ) {
                        $financeiroReserva = $pdo->prepare(
                            'SELECT tarifa, SUM(valuecredit) as credito, SUM(valueguia) as guia, SUM(valueagente) AS agente
                              FROM `ct_createfaturacredit` where numbervoucher = :voucher ');
                        $financeiroReserva->execute(array(":voucher" =>  $item->numbervoucher));
                        $dadosFinanceiro = $financeiroReserva->fetch( PDO::FETCH_ASSOC );

                        $descricaoPagamento = $pdo->prepare(
                            'SELECT datacredit as dia, `name` as pagamento, valuecredit as valor, u.firstname, u.lastname  FROM `ct_createfaturacredit` cfc 
                                  left join `ct_currentaccount` cc on cc.id = cfc.idaccountcurrent left join `ct_usuario` u on cfc.idusr = u.id where numbervoucher = :voucher');
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

                        $buscarConferenciaAdd = $pdo->prepare(
                            'select  ra.dateinput, ra.dateoutput, numbervoucher, c.namefantazia as cliente, pax, u.firstname, u.lastname, s.fullname as servico,
                                cp.namepayment as pagamento, qpax, qchild, nameinvoice as statuus, ra.valueservice
                                from `ct_recentlyadd` ra left join `ct_reserva` r on ra.`idrecently` = r.id left join `ct_servico`
                                s on ra.idservice = s.id  left join `ct_cliente` c on c.`id` = r.`idcliente` left join `ct_usuario` u on u.`id` = r.`idresponsavel`
                                left join `ct_form_of_ payment` cp on cp.`id` = r.`idpayment` left join `ct_statusinvoice` si on si.`id` = r.`idstatusinvoice`
                                where r.abertura >= :abertura  and r.abertura <= :aberturafinal and ra.idrecently = :id order by r.numbervoucher ');
                        $buscarConferenciaAdd->execute(
                            array(
                                ":abertura" => $abertura,":aberturafinal" => $aberturaFinal,
                                ":id" => $item->id)
                        );
                        $registoAdd   = $buscarConferenciaAdd->fetchAll( PDO::FETCH_CLASS );
                        ?>
                        <tr>
                            <td><?php echo( date("d/m/Y", strtotime($item->dateinput))  ); ?></td>
                            <td><?php echo( $item->numbervoucher ); ?></td>
                            <td><?php echo( utf8_decode($item->cliente)   ); ?></td>
                            <td><?php echo( utf8_decode($item->pax)  ); ?></td>
                            <td><?php echo( $item->qtdpax."/".$item->qtdchild); ?></td>
                            <td><?php echo( utf8_decode($item->firstname." ". $item->lastname)  ); ?></td>
                            <td><?php echo( $item->servico ); ?></td>
                            <td><?php echo( $item->pagamento ); ?></td>
                            <td><?php echo( "R$ ".number_format( $valorBruto,2,",","." ) ); ?></td>
                            <td><?php echo( "R$ ".number_format( $dadosFinanceiro['credito'],2,",","." ) ); ?></td>
                        </tr>

                        <?php foreach( $registoAdd as $item2 )
                        {
                            $somaDosServicos2 = $somaDosServicos2 + ( ( $item2->valueservice * $item2->qpax ) +
                                    ( ($item2->valueservice / 2) * $item2->qchild ) );
                            ?>
                            <tr>
                                <td><?php echo( date("d/m/Y", strtotime($item2->dateinput))  ); ?></td>
                                <td><?php echo( $item2->numbervoucher ); ?></td>
                                <td><?php echo( utf8_decode($item2->cliente)  ); ?></td>
                                <td><?php echo( utf8_decode($item2->pax)  ); ?></td>
                                <td><?php echo( $item2->qpax."/".$item2->qchild ); ?></td>
                                <td><?php echo( $item2->firstname ); ?></td>
                                <td><?php echo( utf8_decode($item2->servico)  ); ?></td>
                                <td><?php echo( $item2->pagamento ); ?></td>
                                <td>
                                    <?php echo( "R$ ".number_format( ( ( $item2->valueservice * $item2->qpax ) +
                                            ( ($item2->valueservice / 2) * $item2->qchild ) ),2,",","." ) ); ?>
                                </td>
                                <td>-</td>

                            </tr>
                        <?php }?>

                        <?php if( $contadorDescricaoPagamento >0 ){ ?>
                            <?php foreach ($registroDescricao as $item3){ ?>
                                <tr>
                                    <td id="desc" colspan="3"><?php echo("<strong>Data Pagamento</strong> ".date("d-m-Y", strtotime($item3->dia))); ?></td>
                                    <td id="desc" colspan="4"><?php echo("<strong>Metodo</strong> ".$item3->pagamento); ?></td>
                                    <td id="desc" colspan="2">
                                        <?php echo("<strong>Valor R$ </strong>".number_format($item3->valor, 2,",", ".")); ?>
                                    </td>
                                    <td id="desc" colspan="4">
                                        <?php echo("<strong>Recebido por </strong>".strtoupper( $item3->firstname." ".$item3->lastname)); ?>
                                    </td>
                                </tr>
                            <?php }?>

                        <?php }?>

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
<?php }?>

<?php
$html = ob_get_clean();
$arquivo = "Fatura-Conferência-".date("d/m/Y", strtotime($abertura) ).".pdf" ;
define( '_MPDF_TTFONTDATAPATH', sys_get_temp_dir() );
require_once( 'pdf/mpdf.php' );
$mpdf = new mPDF('utf-8', 'A4-L');
$mpdf->setFooter("{PAGENO}");
$mpdf->SetTitle( "relatório" );
$mpdf->SetAuthor( 'Cassi Turismo' );
$html = mb_convert_encoding($html, 'UTF-8', 'ISO-8859-1');
$mpdf->WriteHTML( $html, 0 );
$mpdf->Output( $arquivo, 'I' );
$mpdf->charset_in = 'windows-1252';
$mpdf->setFooter("{PAGENO}");


?>
