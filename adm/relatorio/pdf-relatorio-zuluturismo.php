<?php
require_once( '../.././config.php' );
$arquivo = "Relatorio-da-zulu periodode".date("d-m-Y", strtotime($_POST['periodoinicial']))." ate ".date("d-m-Y", strtotime($_POST['periodofinal']));

header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header ("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");
header ("Pragma: no-cache");
header ("Content-type: application/x-msexcel");
header ("Content-Disposition: attachment; filename=\"{$arquivo}\"" );
header ("Content-Description: PHP Generated Data" );

ob_start();
$idcliente  = $_POST['cliente'];
$datainicio = $_POST['periodoinicial'];
$datafim    = $_POST['periodofinal'];

$buscaCliente = $pdo->prepare(
    'select datavencimento, tarifa, credito, fullname, corporatename, cnpj, cep, tel01,email, f.id 
                  from `ct_cliente` c left join `ct_fatura` f on f.idcliente = c.id where c.`id` = :id and f.`dateinput` = :inicio and f.`dateoutput` = :fim ');
$buscaCliente->execute( array(":id" => $idcliente, ":inicio" => $datainicio, ":fim" => $datafim) );
$dadosCliente = $buscaCliente->fetch( PDO::FETCH_ASSOC );

$buscarConferencia = $pdo->prepare(
    'select dateinput ,numbervoucher, r.idcliente as idcliente, pax, s.fullname as servico, qtdpax, qtdchild, r.valueservice, valuenet ,ss.schedule from `ct_reserva` r
              left join `ct_cliente` c on c.`id` = r.`idcliente` left join `ct_servico` s on s.`id` = r.`idservico` left join ct_service_schedule ss on ss.idshedule = r.idhorario
              left join `ct_clientservice` cs on cs.idservice = r.idservico where r.`dateinput` >= :inicio and r.`dateoutput` <= :fim  and r.`idcliente` = :cliente
              and idstatus <> :st and cs.idclient = :cl ');
$buscarConferencia->execute( array( ":inicio" => $datainicio, ":fim" => $datafim, ":cliente" => $idcliente, ":st" => 2, ":cl" => $idcliente ) );

$buscarConferenciaAdd = $pdo->prepare(
    'select  ra.dateinput, numbervoucher, c.namefantazia as cliente, pax, s.fullname as servico, qpax, qchild, ss.schedule, ra.valueservice, valuenet
from `ct_recentlyadd` ra right join `ct_reserva` r on ra.`idrecently` = r.id  left join `ct_servico` s on ra.idservice = s.id left join `ct_cliente` c 
on c.`id` = r.`idcliente` left join ct_service_schedule ss on ss.idshedule = ra.idschedule left join `ct_statusinvoice` si on si.`id` = r.`idstatusinvoice`
left join `ct_clientservice` cs on cs.idservice = ra.idservice where ra.`dateinput` >= :inicio and ra.`dateoutput` <= :fim  and r.`idcliente` = :cliente
and idstatus <> :st and cs.idclient = :cl ');
$buscarConferenciaAdd->execute( array( ":inicio" => $datainicio, ":fim" => $datafim, ":cliente" => $idcliente, ":st" => 2, ":cl" => $idcliente) );
$linhas            = $buscarConferencia->rowCount();
$linhasAdd         = $buscarConferenciaAdd->rowCount();
$dadosPrincipais   = $buscarConferencia->fetchAll(PDO::FETCH_CLASS);
$dadosSecundarios  = $buscarConferenciaAdd->fetchAll(PDO::FETCH_CLASS);

?>
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta charset="utf-8">

    </head>
    <body>
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <h6> <?php echo("RELATORIO DA ZULU PERIODO ".date("d-m-Y", strtotime($_POST['periodoinicial']))." ate "
                        .date("d-m-Y", strtotime($_POST['periodofinal']))); ?></h6>
                <table class="highlight">
                    <thead>
                    <tr>
                        <th>Data</th>
                        <th>Voucher</th>
                        <th>Servico</th>
                        <th>Hora</th>
                        <th>Pax/Child</th>
                        <th>PAX</th>
                        <th>Unitário</th>
                        <th>Total</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($dadosPrincipais as $item){?>
                        <tr>
                            <td><?php echo( date("d-m-Y", strtotime( $item->dateinput ) ) ); ?></td>
                            <td><?php echo( $item->numbervoucher); ?></td>
                            <td><?php echo( utf8_decode( $item->servico )); ?></td>
                            <td><?php echo( $item->schedule); ?></td>
                            <td><?php echo( $item->qtdpax."/".$item->qtdchild); ?></td>
                            <td><?php echo( utf8_decode( $item->pax )); ?></td>
                            <td><?php echo("R$ ".number_format($item->valuenet, 2, ",", ".")); ?></td>
                            <td>
                                <?php echo("R$ ".number_format((($item->valuenet * $item->qtdpax) + (($item->valuenet / 2) * $item->qtdchild)),
                                        2, ",", ".")); ?></td>
                        </tr>

                    <?php }?>
                    <?php if($linhasAdd > 0){ ?>
                        <?php foreach ($dadosSecundarios as $item2){?>
                            <tr>
                                <td><?php echo(date("d-m-Y", strtotime( $item2->dateinput ) ) ); ?></td>
                                <td><?php echo($item2->numbervoucher); ?></td>
                                <td><?php echo( utf8_decode( $item2->servico )); ?></td>
                                <td><?php echo($item2->schedule); ?></td>
                                <td><?php echo( $item2->qpax."/".$item2->qchild); ?></td>
                                <td><?php echo( utf8_decode( $item2->pax )); ?></td>
                                <td><?php echo("R$ ".number_format($item2->valuenet, 2, ",", ".")); ?></td>
                                <td>
                                    <?php echo("R$ ".number_format(($item2->valuenet * $item2->qpax) + (($item2->valuenet / 2) * $item2->qchild),
                                            2, ",", ".")); ?></td>
                            </tr>
                        <?php }?>
                    <?php }?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </body>
    </html>
