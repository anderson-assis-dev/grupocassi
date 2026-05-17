<?php
require_once( '../.././config.php' );
header('Content-Type: text/html; charset=utf-8');
$totalAdd  = 0;
$totalPago = 0;
if( isset( $_POST['voucher'] )  )
{
    $buscarCredito = $pdo->prepare(
        'select SUM(cfc.valuecredit) as credito from `ct_createfaturacredit` cfc  where `numbervoucher` = :numbervoucher ');
    $buscarCredito->execute(
        array(
            ":numbervoucher" => $_POST['voucher']
        )
    );
    $dadosCredito = $buscarCredito->fetch(PDO::FETCH_ASSOC);
    $creditoPago  = $buscarCredito->fetchAll(PDO::FETCH_CLASS);
    $contadorCredito = $buscarCredito->rowCount();

    $descreverCredito = $pdo->prepare(
        'SELECT valuecredit as credito, `name` as forma, datacredit FROM `ct_createfaturacredit` cfc left join `ct_currentaccount` cc on cfc.idaccountcurrent = cc.id where `numbervoucher` = :numbervoucher ');
    $descreverCredito->execute(
        array(
            ":numbervoucher" => $_POST['voucher']
        )
    );
    $registroCredito = $descreverCredito->fetchAll(PDO::FETCH_CLASS);

    $dadosReserva = $pdo->prepare(
        "SELECT r.id,pax, documento, dateinput, dateoutput, photoresident, c.fullname as cliente, s.fullname as `status`, r.horaap, u.firstname,
                  se.fullname as serivco, ag.fullname as agente, priceadult, namepayment, g.fullname as guia, qtdpax, qtdchild, qtdfree, ss.schedule,
                  pricechild,numbervoucher, r.valueservice, r.abertura, se.screenplay
                  FROM `ct_reserva` r left join ct_cliente c on c.id = r.idcliente
                  left join ct_usuario u on u.id = r.idresponsavel left join ct_status s on s.id = r.idstatus
                  left join ct_guia g on g.id = r.idguia join ct_servico se on se.id = r.idservico
                  left join ct_agentes as ag on r.idagente = ag.id
                  left join ct_service_schedule ss on ss.idshedule = r.idhorario left join `ct_form_of_ payment` as cfp
                  on cfp.id = r.idpayment  where `numbervoucher` = :numbervoucher ");
    $dadosReserva->execute( array(":numbervoucher" => $_POST['voucher'] ) );
    $dadosGerais = $dadosReserva->fetch(PDO::FETCH_ASSOC);

    $total = ( ( $dadosGerais['valueservice'] * $dadosGerais['qtdpax'] ) + (  ($dadosGerais['valueservice'] / 2) * $dadosGerais['qtdchild'] ) );
    $timestamp  = strtotime($dadosGerais['dateoutput']);
    $timestamp2 = strtotime($dadosGerais['dateinput']);
    $timeAutal  = strtotime( date('Y-m-d') );

    $adicionais = $pdo->prepare(
        'SELECT ra.dateinput as ap, s.fullname,s.screenplay, s.priceadult, s.pricechild, ss.schedule, qpax, qchild, qfree, ra.valueservice, ra.horaap, ra.documento
                      FROM `ct_recentlyadd` ra left join `ct_reserva` r on r.id = ra.idrecently
                      left join ct_servico s on s.id = ra.idservice left join ct_service_schedule ss
                      on ss.idshedule = ra.idschedule where r.id = :id order by ap');
    $adicionais->execute(array(":id" => $dadosGerais['id'] ) );
    $registro = $adicionais->fetchAll(PDO::FETCH_CLASS);
    $contador = $adicionais->rowCount();



}
//if (empty($dadosGerais)){die("Você não informou o pax".$_POST['nomecliente']);}

ob_start();
?>
<?php if($dadosCredito['credito'] == 0 or $dadosCredito['credito']  < 0){
    $dadosAuditoria = $pdo->prepare(
        'insert into `ct_audit` (`id`, `idresponsible`, `voucher`, `description`, `date`) values (DEFAULT, :idres, :vou, :descr, :dat) ');
    $dadosAuditoria->execute(
        array(
            ":idres" =>  $_SESSION['idresponsavel'],
            ":vou"   => $_POST['voucher'],
            ":descr" => "Tentou imprimir o voucher. Porem não foi encontrado pagamento, logo o voucher nao foi impresso ",
            ":dat"   => date("Y-m-d H:i:s")
        )
    );
    ?>
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta charset="utf-8">
        <title> Voucher </title>
        <link rel="stylesheet" href="materialize.min.css">
    </head>
    <style>
        td#ap{font-weight: bold; font-size: 17px;}
    </style>
    <body>
    <img style="width: 700px;" id="logo" src="../../images/logo.png"/>
    <h6 class=""><strong style="font-weight: bold;" >VOUCHER <?php echo($dadosGerais['numbervoucher']); ?> </strong> </h6><br>
    <p style="font-size: 7px; margin-top: -20px;">Impresso em: <?php echo(date("d/m/Y")); ?></p>
    <div align="center" class="container">
        <h4>
            <?php echo( utf8_decode( "Você não pode imprimir um voucher que não foi pago :)" ) ); ?></h4>
    </div>
    </body>
    </html>

<?php } else{
    $dadosAuditoria = $pdo->prepare(
        'insert into `ct_audit` (`id`, `idresponsible`, `voucher`, `description`, `date`) values (DEFAULT, :idres, :vou, :descr, :dat) ');
    $dadosAuditoria->execute(
        array(
            ":idres" =>  $_SESSION['idresponsavel'],
            ":vou"   =>  $_POST['voucher'],
            ":descr" => "Voucher Impresso",
            ":dat"   => date("Y-m-d H:i:s")
        )
    );
    ?>
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta charset="utf-8">
        <title> Voucher </title>
        <link rel="stylesheet" href="materialize.min.css">
    </head>
    <style>
        td#ap{font-weight: bold; font-size: 17px;}
        td#valor{font-weight: bold; font-size: 18px;}
        p#valor2{font-weight: bold; font-size: 18px;}
    </style>
    <body>
    <div align="center" class="container">
        <div class="col-md-12">
            <img style="width: 700px;" id="logo" src="../../images/logo.png"/>
            <h6 class=""><strong style="font-weight: bold;" >VOUCHER <?php echo($dadosGerais['numbervoucher']); ?> </strong> </h6><br>
            <p style="font-size: 7px; margin-top: -20px;">Impresso em: <?php echo(date("d/m/Y - H:i:s")); ?></p>
            <table class="responsive-table" style="font-size: 10px;">
                <tr style=" margin-bottom:  -10px;">
                    <thead>
                    <th>Cliente</th>
                    <th>Status</th>
                    <th>Abertura</th>
                    <th>Chegada</th>
                    <th>Pax/Child/Free</th>
                    </thead>
                </tr>
                <tr>
                    <tbody>
                    <td><?php echo( ( $dadosGerais['cliente'] ) ); ?></td>
                    <td><?php echo($dadosGerais['status']); ?></td>
                    <td><?php echo(date('d-m-Y', strtotime($dadosGerais['abertura']))); ?></td>
                    <td><?php echo(date('d-m-Y', $timestamp)); ?></td>
                    <td><?php echo($dadosGerais['qtdpax']."/".$dadosGerais['qtdchild']."/".$dadosGerais['qtdfree']); ?></td>

                    </tbody>
                </tr>
            </table>
            <h6>PAX: <?php echo(utf8_decode($dadosGerais['pax'])); ?></h6>
            <table class="responsive-table" style="font-size: 10px;">
                <tr style=" margin-bottom:  -10px;">
                    <thead>
                    <th>Responsavel</th>
                    <th>Agente</th>
                    <th>Guia</th>
                    <th>Data de Saida</th>
                    </thead>
                </tr>
                <tr>
                    <tbody>
                    <td><?php echo(($dadosGerais['firstname'])); ?></td>
                    <td><?php echo($dadosGerais['agente']); ?></td>
                    <td><?php echo($dadosGerais['guia']); ?></td>
                    <td><?php echo( date('d-m-Y', $timestamp ) ) ; ?></td>
                    </tbody>
                </tr>
            </table>
            <h6><?php echo( utf8_decode('Serviços contratados') ); ?></h6>
            <table class="responsive-table" style="font-size: 10px;">
                <tr style=" margin-bottom:  -10px;">
                    <thead>
                    <th><?php echo(('Serviço')) ?></th>
                    <th><?php echo(('Info. Adicionais')) ?></th>
                    <th><?php echo(('Data')) ?></th>
                    <th><?php echo(('Apresentação')) ?></th>
                    <th><?php echo(('Embarque')) ?></th>

                    <th>P/C/F</th>

                    <th>Valor por PAX</th>
                    <?php if( $dadosGerais['qtdchild']  > 0 ){ ?>
                        <th>Valor por CHILD</th>
                    <?php }?>
                    <th>Sub Total</th>
                    </thead>
                </tr>
                <tr>
                    <tbody>
                    <td><?php echo($dadosGerais['serivco']); ?></td>
                    <td><?php echo( utf8_decode( $dadosGerais['documento'] ) ); ?></td>
                    <td><?php  echo(date("d/m/Y", $timestamp2)  ); ?></td>
                    <td id="ap"><?php echo("<strong> >".date("H:i", strtotime($dadosGerais['horaap']))."< </strong>" ); ?></td>
                    <td><?php echo(date("H:i", strtotime($dadosGerais['dateinput']." ".$dadosGerais['schedule']))); ?></td>

                    <td><?php echo($dadosGerais['qtdpax']."/".$dadosGerais['qtdchild']."/".$dadosGerais['qtdfree']); ?></td>

                    <td><?php echo("R$ ".number_format($dadosGerais['valueservice'],2,",",".")); ?></td>
                    <?php if( $dadosGerais['qtdchild']  > 0 ){ ?>
                        <td><?php echo("R$ ".number_format(($dadosGerais['valueservice'] / 2),2,",",".")); ?></td>
                    <?php }?>
                    <td><?php echo("R$ ".number_format($total,2,",",".") ); ?></td>
                    </tbody>
                </tr>
                <?php if($contador > 0) { ?>
                    <?php foreach ($registro as $item){
                        $timestampAdd = strtotime( $item->ap );
                        $totalAdd = $totalAdd + ( ( $item->valueservice * $item->qpax ) + (  ($item->valueservice / 2) * $item->qchild ) );
                        $valorSub =  ( ( $item->valueservice * $item->qpax ) + (  ($item->valueservice / 2) * $item->qchild ) );
                        ?>
                        <tr>
                            <tbody>
                            <td><?php echo($item->fullname); ?></td>
                            <td><?php echo( utf8_decode( $item->documento ) ); ?></td>
                            <td><?php echo(date("d/m/Y",$timestampAdd)  ); ?></td>
                            <td id="ap"><?php echo("<strong> >".date("H:i", strtotime($item->horaap))."< </strong>"); ?></td>
                            <td><?php echo(date("H:i", strtotime($item->ap." ".$item->schedule))); ?></td>
                            <td><?php echo($item->qpax."/".$item->qchild."/".$item->qfree); ?></td>
                            <td><?php echo("R$ ".number_format($item->valueservice,2,",",".")); ?></td>
                            <?php if( $item->qchild  > 0 ){ ?>
                                <td><?php echo("R$ ".number_format(($item->valueservice / 2 ),2,",",".")); ?></td>
                            <?php }?>
                            <td><?php echo("R$ ".number_format($valorSub,2,",",".") ); ?></td>
                            </tbody>
                        </tr>
                    <?php }?>
                <?php }?>
            </table>
            <h6>Pagamentos</h6>
            <?php $valorDocumento = ($total + $totalAdd) ; {?>
                <table class="responsive-table" style="font-size: 10px;">
                    <thead>
                    <tr>
                        <th>Data Pagamento</th>
                        <th>Forma de Pagamento</th>
                        <th>Valor Total</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach( $registroCredito as $item) { $totalPago = $totalPago + $item->credito; ?>
                    <tr>
                        <td><?php echo(date("d-m-Y", strtotime($item->datacredit ) )); ?></td>
                        <td><?php echo($item->forma); ?></td>
                        <td id="valor"><strong><?php echo("R$ ".number_format($item->credito ,2,",",".")); ?></strong></td>
                    </tr>
                    <?php }?>
                    </tbody>
                </table>
                <table class="responsive-table">
                    <thead>
                        <tr>
                            <th>Total</th>
                            <th>Total Pago</th>
                            <?php if(($valorDocumento-$totalPago) <> 0 ) { ?>
                                <th>Falta Pagar</th>
                            <?php }?>

                        </tr>

                    </thead>
                    <tbody>
                        <tr>
                            <td><?php echo("R$ ".number_format($valorDocumento ,2,",",".")); ?></td>
                            <td><?php echo("R$ ".number_format($totalPago ,2,",",".")); ?></td>
                            <?php if(($valorDocumento-$totalPago) <> 0 ) { ?>
                                <td><?php echo("R$ ".number_format($valorDocumento-$totalPago ,2,",",".")); ?></td>
                            <?php }?>

                        </tr>
                    </tbody>
                </table>
                <p id="valor2"></p>
            <?php }?>
            <h6>Sobre o Roteiro</h6>
        </div>
        <p align="justify" style="font-size: 9px;"><?php echo(($dadosGerais['screenplay'])); ?></p>
    </div>
    </body>
    </html>
<?php }?>

<?php

$html = ob_get_clean();

//------------------------------------------------------------------------------------------------------------
$arquivo = $dadosGerais['numbervoucher'].".pdf" ;
define( '_MPDF_TTFONTDATAPATH', sys_get_temp_dir() );
require_once( 'pdf/mpdf.php' );
$mpdf = new mPDF();
$mpdf->SetTitle( "relatório" );
$mpdf->SetAuthor( 'Cassi Turismo' );
$html = mb_convert_encoding($html, 'UTF-8', 'ISO-8859-1');
$mpdf->WriteHTML( $html, 0 );
$css = file_get_contents("../.././vendor/bootstrap-4.1/bootstrap.min.css");
$mpdf->WriteHTML($css,1);
$mpdf->Output( $arquivo, 'I' );
$mpdf->charset_in = 'windows-1252';
?>
