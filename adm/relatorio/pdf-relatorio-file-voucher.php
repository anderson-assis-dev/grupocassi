<?php
/**
 * Created by PhpStorm.
 * User: Ander
 * Date: 01/03/2019
 * Time: 11:25
 */
ob_start();
require_once( '../.././config.php' );
if( isset( $_GET['voucher'] ) )
{
    $totalSecundario = 0;
    $numberVoucher = $_GET['voucher'];
    $dadosReserva = $pdo->prepare(
        "SELECT r.id, r.valueservice as valorp, r.qtdpax ,r.qtdchild, ra.qpax, ra.qchild, ra.valueservice as valors, c.fullname as cliente, r.pax, r.documento,
                  se.fullname as servico, c.fullname as cliente, namepayment, nameinvoice, r.numberfatura, r.dateinput, r.numbervoucher, u.firstname FROM `ct_reserva` r
                  left join ct_cliente c on c.id = r.idcliente left join ct_usuario u on u.id = r.idresponsavel 
                  left join ct_status s on s.id = r.idstatus left join ct_guia g on g.id = r.idguia join ct_servico se on se.id = r.idservico
                  left join ct_agentes as ag on r.idagente = ag.id left join ct_statusinvoice sti on sti.id = r.idstatusinvoice
                  left join ct_recentlyadd ra on ra.idrecently = r.id left join ct_service_schedule ss on ss.idshedule = r.idhorario
                  left join `ct_form_of_ payment` as cfp on cfp.id = r.idpayment where `numbervoucher` = :numbervoucher");
    $dadosReserva->execute( array(":numbervoucher" => $numberVoucher ));
    $dadosGerais = $dadosReserva->fetch(PDO::FETCH_ASSOC);

    $contador = $dadosReserva->rowCount();


    $adicionais = $pdo->prepare(
        'SELECT r.pax, ra.dateinput, s.fullname, qpax, qchild, qfree, ra.valueservice, ra.horaap, ra.documento, c.fullname as cliente, r.numbervoucher, ra.documento
                  FROM `ct_recentlyadd` ra left join `ct_reserva` r on r.id = ra.idrecently
                  left join ct_servico s on s.id = ra.idservice left join ct_service_schedule ss on ss.idshedule = ra.idschedule
                  left join ct_cliente c on c.id = r.idcliente where r.id = :id order by ra.dateinput');
    $adicionais->execute(array(":id" => $dadosGerais['id'] ) );
    $registro = $adicionais->fetchAll(PDO::FETCH_CLASS);
    $contador = $adicionais->rowCount();
    $totalPrincipal =  ( ($dadosGerais['valorp'] * $dadosGerais['qtdpax']) +
        ( ($dadosGerais['valorp']/ 2) * $dadosGerais['qtdchild'] ) );

    foreach ($registro as $item)
    {
        $totalPrincipal += ($item->valueservice * $item->qpax ) + ( ($item->valueservice / 2) * $item->qchild );
    }

    $financeiroReserva = $pdo->prepare(
        'SELECT tarifa, SUM(valuecredit) as credito, SUM(valueagente) as agente, SUM(valueguia) as guia FROM `ct_createfaturacredit` 
                    where numbervoucher = :voucher ');
    $financeiroReserva->execute(array(":voucher" => $numberVoucher));
    $dadosFinanceiro = $financeiroReserva->fetch( PDO::FETCH_ASSOC );



    $descricaoPagamento = $pdo->prepare(
        'SELECT datacredit as dia, `name` as pagamento, valuecredit as valor  FROM `ct_createfaturacredit` cfc 
                                  left join `ct_currentaccount` cc on cc.id = cfc.idaccountcurrent where numbervoucher = :voucher');
    $descricaoPagamento->execute(array(":voucher" =>  $numberVoucher));
    $registroDescricao = $descricaoPagamento->fetchAll(PDO::FETCH_CLASS);
    $contadorDescricaoPagamento  = $descricaoPagamento->rowCount();
    
}
?>
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta charset="utf-8">
        <title><?php echo( utf8_decode( "Relatório de Voucher" ) ); ?></title>
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
                <p><?php echo( utf8_decode( "Relatório do voucher ".
                       $_GET['voucher'])); ?> </p><br>
                <p style="font-size: 9px; margin-top: -20px;">Impresso em: <?php echo(date("d/m/Y - H:i:s")); ?></p>

                <table class="highlight">
                    <thead>
                        <tr>
                            <th>EMBARQUE</th>
                            <th>VOUCHER</th>
                            <th>CLIENTE</th>
                            <th>PAX</th>
                            <th>DOCUMENTO</th>
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
                        <tr>
                            <td><?php echo( date("d-m-Y", strtotime($dadosGerais['dateinput']))   ); ?></td>
                            <td><?php echo( $dadosGerais['numbervoucher'] ); ?></td>
                            <td><?php echo( utf8_decode( $dadosGerais['cliente'] ) ); ?></td>
                            <td><?php echo( utf8_decode($dadosGerais['pax']) ); ?></td>
                            <td><?php echo(  utf8_decode($dadosGerais['documento']) ); ?></td>
                            <td><?php echo( $dadosGerais['qtdpax']."/".$dadosGerais['qtdchild'] ); ?></td>
                            <td><?php echo( utf8_decode($dadosGerais['firstname']) ); ?></td>
                            <td><?php echo( utf8_decode($dadosGerais['servico']) ); ?></td>
                            <td><?php echo( utf8_decode($dadosGerais['namepayment']) ); ?></td>
                            <td><?php echo("R$ ".number_format(( ($dadosGerais['valorp'] * $dadosGerais['qtdpax']) +
                                        ( ($dadosGerais['valorp']/ 2) * $dadosGerais['qtdchild'] ) ), 2, ",", ".")); ?></td>
                            <td>
                                <?php
                                echo("R$ ".number_format($dadosFinanceiro['agente']+$dadosFinanceiro['guia'], 2, ",", ".") ); ?>
                            </td>
                            <td><?php echo("R$ ".number_format(( ($dadosGerais['valorp'] * $dadosGerais['qtdpax']) +
                                            ( ($dadosGerais['valorp']/ 2) * $dadosGerais['qtdchild'] ) ) - $dadosFinanceiro['agente']+$dadosFinanceiro['guia'],
                                        2, ",", ".")); ?></td>
                            <td><?php echo("R$ ".number_format($dadosFinanceiro['credito'], 2, ",", ".") ); ?></td>
                            <td><?php echo( $dadosGerais['nameinvoice'] ); ?></td>
                        </tr>
                        <?php if($contador > 0) { ?>
                            <?php foreach ($registro as $item){ ?>
                                <tr>
                                    <td><?php echo(date("d-m-Y", strtotime($item->dateinput))); ?></td>
                                    <td><?php echo($item->numbervoucher); ?></td>
                                    <td><?php echo( utf8_decode( $item->cliente ) ); ?></td>
                                    <td><?php echo( utf8_decode( $item->pax ) ); ?></td>
                                    <td><?php echo( utf8_decode( $item->documento ) ); ?></td>
                                    <td><?php echo($item->qpax." / ".$item->qchild); ?></td>
                                    <td><?php echo( utf8_decode($dadosGerais['firstname']) ); ?></td>
                                    <td><?php echo(utf8_decode($item->fullname)); ?></td>
                                    <td><?php echo( $dadosGerais['namepayment'] ); ?></td>
                                    <td><?php echo("R$ ".number_format(( ( $item->valueservice * $item->qpax ) + (  ($item->valueservice / 2) * $item->qchild ) ),
                                                2,",",".") ); ?></td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td><?php echo( $dadosGerais['nameinvoice'] ); ?></td>
                                </tr>
                            <?php }?>
                        <?php }?>
                    </tbody>
                </table>
                <br>
                <?php if( $contadorDescricaoPagamento >0 ){ ?>
                    <h6><?php echo( utf8_decode("Descrição do Pagamento") ); ?></h6>
                    <table class="highlight">
                        <thead>
                            <tr>
                                <th>Data Pagamento</th>
                                <th>Forma de Pagamento</th>
                                <th>Valor Pago</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($registroDescricao as $item3){ ?>
                            <tr>
                                <td ><?php echo(date("d-m-Y", strtotime($item3->dia))); ?></td>
                                <td><?php echo($item3->pagamento); ?></td>
                                <td><?php echo(number_format($item3->valor, 2,",", ".")); ?></td>
                            </tr>
                        <?php }?>

                        </tbody>
                    </table>
                <?php }?>
                <br>
                <table class="highlight">
                    <thead>
                        <tr>
                            <th>Valor Total</th>
                            <th>Total Pago</th>
                            <th>A Pagar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php echo("R$ ".number_format($totalPrincipal, 2, ",", ".") ); ?></td>
                            <td><?php echo("R$ ".number_format($dadosFinanceiro['credito'], 2, ",", ".") ); ?></td>
                            <td><?php echo("R$ ".number_format($totalPrincipal - $dadosFinanceiro['credito'], 2, ",", ".") ); ?></td>

                        </tr>

                    </tbody>
                </table>
            </div>
        </body>
    </html>
<?php

$html = ob_get_clean();
$arquivo = "Relatorio-do-voucher-".$_POST['voucher'].".pdf" ;
define( '_MPDF_TTFONTDATAPATH', sys_get_temp_dir() );
require_once( 'pdf/mpdf.php' );


$mpdf = new mPDF('utf-8', 'A4-L');
$mpdf->SetTitle( "relatório" );
$mpdf->SetAuthor( 'Cassi Turismo' );
$html = mb_convert_encoding($html, 'UTF-8', 'ISO-8859-1');
$mpdf->WriteHTML( $html, 0 );
$mpdf->Output( $arquivo, 'I' );
$mpdf->charset_in = 'windows-1252';
ob_clean();//Limpa o buffer de saída

?>