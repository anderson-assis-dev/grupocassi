<?php
require_once( './config.php' );
$totalAdd  = 0;
$totalPago = 0;
if( isset( $_GET['voucher'] ) and !empty($_GET['voucher'])  )
{
    $buscarCredito = $pdo->prepare(
        'select SUM(cfc.valuecredit) as credito from `ct_createfaturacredit` cfc  where `numbervoucher` = :numbervoucher ');
    $buscarCredito->execute(
        array(
            ":numbervoucher" => $_GET['voucher']
        )
    );
    $dadosCredito = $buscarCredito->fetch(PDO::FETCH_ASSOC);
    $creditoPago  = $buscarCredito->fetchAll(PDO::FETCH_CLASS);
    $contadorCredito = $buscarCredito->rowCount();

    $descreverCredito = $pdo->prepare(
        'SELECT valuecredit as credito, `name` as forma, datacredit, u.firstname, u.lastname FROM `ct_createfaturacredit` cfc 
                    left join `ct_currentaccount` cc on cfc.idaccountcurrent = cc.id left join ct_usuario u on cfc.idusr = u.id where `numbervoucher` = :numbervoucher ');
    $descreverCredito->execute(
        array(
            ":numbervoucher" => $_GET['voucher']
        )
    );
    $registroCredito = $descreverCredito->fetchAll(PDO::FETCH_CLASS);

    $dadosReserva = $pdo->prepare(
        "SELECT r.idservico,r.id,pax, documento, dateinput, dateoutput, photoresident, c.fullname as cliente, c.observacao ,s.fullname as `status`, r.horaap, u.firstname, u.lastname,
                  se.fullname as serivco, ag.fullname as agente, priceadult, namepayment, g.fullname as guia, qtdpax, qtdchild, qtdfree, ss.schedule, r.voo,
                  pricechild,numbervoucher, r.valueservice, r.abertura, se.screenplay, sr.roteiro
                  FROM `ct_reserva` r left join ct_cliente c on c.id = r.idcliente
                  left join ct_usuario u on u.id = r.idresponsavel left join ct_status s on s.id = r.idstatus
                  left join ct_guia g on g.id = r.idguia join ct_servico se on se.id = r.idservico
                  left join ct_agentes as ag on r.idagente = ag.id left join `ct_servico_horario` sr on sr.idservice = r.idservico and sr.idschedule = r.idhorario 
                  left join ct_service_schedule ss on ss.idshedule = r.idhorario left join `ct_form_of_ payment` as cfp
                  on cfp.id = r.idpayment  where `numbervoucher` = :numbervoucher ");
    $dadosReserva->execute( array(":numbervoucher" => $_GET['voucher'] ) );
    $dadosGerais = $dadosReserva->fetch(PDO::FETCH_ASSOC);

    $total = ( ( $dadosGerais['valueservice'] * $dadosGerais['qtdpax'] ) + (  ($dadosGerais['valueservice'] / 2) * $dadosGerais['qtdchild'] ) );
    $timestamp  = strtotime($dadosGerais['dateoutput']);
    $timestamp2 = strtotime($dadosGerais['dateinput']);
    $timeAutal  = strtotime( date('Y-m-d') );

    $adicionais = $pdo->prepare(
        'SELECT ra.idservice,ra.dateinput as ap, s.fullname,s.screenplay, s.priceadult, s.pricechild, ss.schedule, qpax, qchild, qfree, ra.valueservice, ra.horaap,ra.documento, sr.roteiro
                      FROM `ct_recentlyadd` ra left join `ct_reserva` r on r.id = ra.idrecently left join `ct_servico_horario` sr on sr.idservice = ra.idservice and sr.idschedule = ra.idschedule
                      left join ct_servico s on s.id = ra.idservice left join ct_service_schedule ss
                      on ss.idshedule = ra.idschedule where r.id = :id order by ap');
    $adicionais->execute(array(":id" => $dadosGerais['id'] ) );
    $registro = $adicionais->fetchAll(PDO::FETCH_CLASS);
    $contador = $adicionais->rowCount();

}
else{die("N�o foi possivel encontrar o voucher.");}
ob_start();
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8">
    <title> Voucher <?php echo(" ".$dadosGerais['numbervoucher']); ?></title>
    <link rel="stylesheet" href="materialize.min.css">
</head>
<style>
     th, td{border: 1px solid #ddd; padding: 8px;}
	th, td, p{font-size: 8px;text-align: justify;}

        hr{background-color: #ddd; color: #ddd;}
        th{background: #094594 !important; color: white;}
        td#ap{font-weight: bold; font-size: 17px;}
        td#valor{font-weight: bold; font-size: 18px;}
        thead{border-radius: 50px;}
        p#valor2{font-weight: bold; font-size: 18px;}
        .col-md-6{-ms-flex:0 0 50%;flex:0 0 50%;max-width:50%;position:relative;width:100%;min-height:1px;padding-right:15px;padding-left:15px}
        .pull-left{float:left}
        .pull-right{float:right}
</style>
<body>
<div align="center" class="container">
    <div class="col-md-12">
             <?php if(empty($dadosGerais['observacao'] )){ ?>
	    <div class="col-md-6 pull-left">
                <img style="width: 200px;" id="logo" src="../.././images/logo.png"/>
            </div>
            <div class="" >
                <p style="margin-left: 340px; margin-top: -20px; position: absolute; font-size: 10px; color: #2468b3; text-align: right;">cassiturismo.com.br | @cassiturismo <br>
                    Atendimento Nacional (71)99121-1111 | <br>Atendimento Operacional (71)98444-4444 | <br> </p>
            </div>
            <?php } else { ?>
            <div class="col-md-6 pull-left">
                <img style="width: 100px;" id="logo" src="<?php echo($dadosGerais['observacao']); ?>"/>
            </div>
            <div class="" >
                <p style="margin-left: 340px; margin-top: -20px; position: absolute; font-size: 10px; color: #2468b3; text-indent: 20px;">
                    <?php echo(utf8_decode('Realização do serviço')) ?>
                    <br>
                    <img style="width: 200px;" id="logo" src="../.././images/logo.png"/><br>
                    <br>Atendimento Operacional (71)98444-4444
                </p>
            </div>
            <?php }?>	        <hr>
        <div style="margin-top: -20px;">
            <p class="" style="margin-right: 340px;  position: absolute; font-size: 12px;">VOUCHER:</p>
            <p style="margin-right: 340px;  position: absolute; font-size: 12px; padding: 5px; border-radius: 10px; border: 2px solid #dee2e6; text-align: center;"><?php echo($dadosGerais['numbervoucher']); ?></p>
        </div>

        <div style="margin-top: -85px;">
            <p class="" style="margin-left: 340px;  position: absolute; font-size: 12px;">Impresso em:</p>
            <p style="margin-left: 340px;  position: absolute; font-size: 12px; padding: 5px; border-radius: 10px; border: 2px solid #dee2e6; text-align: center;"><?php echo( date("d/m/Y").utf8_decode(" às ").date("H:i:s")); ?></p>
        </div>
        <table class="responsive-table" style="font-size: 10px;">
            <thead>
            <tr>
                <th><?php echo(utf8_decode('Agência')) ?></th>
                <th>Abertura</th>
		        <th>Hor�rio do Voo</th>
                
            </tr>
            </thead>
            <tr style=" margin-bottom:  -10px;">
                <thead>

                </thead>
            </tr>
            <tbody>
            <tr>
                <td><?php echo( ( $dadosGerais['cliente'] ) ); ?></td>
                <td><?php echo(date('d-m-Y', strtotime($dadosGerais['abertura']))); ?></td>
		<td><?php echo(date('H:i', strtotime($dadosGerais['voo']))); ?></td>
            </tr>
            </tbody>
        </table>
        <table class="responsive-table" style="font-size: 10px;">
            <thead>
            <tr>
                <th>Nome</th>
                <th>Telefone / CPF</th>
            </tr>
            </thead>
            <tr style=" margin-bottom:  -10px;">
                <thead>

                </thead>
            </tr>
            <tbody>
            <tr>
                <td><?php echo(utf8_decode($dadosGerais['pax'])); ?></td>
                <td><?php echo(($dadosGerais['photoresident'])); ?></td>
            </tr>
            </tbody>
        </table>
        <table class="responsive-table" style="font-size: 10px;">
            <thead>
                <tr style=" margin-bottom:  -10px;">
                    <th>Operador(a)</th>

                </tr>

            </thead>

            <tbody>
                <tr>
                    <td><?php echo(($dadosGerais['firstname']." ".$dadosGerais['lastname'])); ?></td>
                  
                </tr>
            </tbody>

        </table>
        <h6 align="center" style="font-weight: bold;"><?php echo( utf8_decode('Serviços contratados') ); ?></h6>
        <table class="responsive-table" style="font-size: 10px;">
            <tr style=" margin-bottom:  -10px;">
                <thead>
                <th><?php echo(utf8_decode('Serviço - Complemento')) ?></th>
                <th><?php echo(('Data')) ?></th>
                <th><?php echo(('Embarque')) ?></th>
                <th><?php echo(utf8_decode('Apresentação')) ?></th>
                <th>P/C/F</th>
                <th><?php echo(utf8_decode('Valor Unitário')) ?></th>
                <?php if( $dadosGerais['qtdchild']  > 0 ){ ?>
                    <th>Valor por CHILD</th>
                <?php }?>
                <th>Valor Total</th>
                </thead>
            </tr>
            <tr>
                <tbody>
                <td><?php echo($dadosGerais['serivco']." - ".( strip_tags(html_entity_decode($dadosGerais['documento'], ENT_QUOTES, 'UTF-8')))); ?></td>
                <td><?php  echo(date("d/m/Y", $timestamp2)  ); ?></td>
                <?php if($dadosGerais['idservico'] == 15){ ?>
                    <td id="ap"><?php echo("<strong> >".date("H:i", strtotime($dadosGerais['horaap']))."< </strong>" ); ?></td>
                <?php } else { ?>
                    <td><?php echo(date("H:i", strtotime($dadosGerais['dateinput']." ".$dadosGerais['schedule']))); ?></td>
                <?php }?>
                <td id="ap"><?php echo("<strong> >".date("H:i", strtotime($dadosGerais['horaap']))."< </strong>" ); ?></td>
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
                        <td><?php echo($item->fullname." - ".( strip_tags(html_entity_decode($item->documento, ENT_QUOTES, 'UTF-8')) )); ?></td>
                        <td><?php echo(date("d/m/Y",$timestampAdd)  ); ?></td>
                        <?php if($item->idservice == 15){ ?>
                            <td><?php echo("<strong> >".date("H:i", strtotime($item->horaap))."< </strong>"); ?></td>
                        <?php } else { ?>
                            <td><?php echo(date("H:i", strtotime($item->ap." ".$item->schedule))); ?></td>
                        <?php }?>

                        <td id="ap"><?php echo("<strong> >".date("H:i", strtotime($item->horaap))."< </strong>"); ?></td>
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
        <h6 style="margin-left: 480px; font-size: 12px; font-weight: bold;">Totais
            <?php $valorDocumento = $total + $totalAdd; echo("R$ ".number_format($valorDocumento ,2,",",".")); ?></h6>

        <table class="responsive-table" style="font-size: 10px;">
            <thead>
            <tr>
                <th>Pago em</th>
                <th>Forma de Pagamento</th>
                <th>Recebido por</th>
                <th>Valor Total</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach( $registroCredito as $item) { $totalPago = $totalPago + $item->credito; ?>
                <tr>
                    <td><?php echo(date("d-m-Y", strtotime($item->datacredit ) )); ?></td>
                    <td><?php echo($item->forma); ?></td>
                    <td><?php echo( strtoupper( $item->firstname." ".$item->lastname ) ); ?></td>
                    <td id="valor"><strong><?php echo("R$ ".number_format($item->credito ,2,",",".")); ?></strong></td>
                </tr>
            <?php }?>
            </tbody>
        </table>
        <table class="responsive-table">
            <thead>
            <tr>
                <th>Pago</th>
                <?php if(($totalPago - $valorDocumento) < 0 ) { ?>
                    <th>Falta Pagar</th>
                <?php } else { ?>
                    <th><?php echo(utf8_decode('Crédito')); ?></th>
                <?php }?>
            </tr>
            </thead>
            <tbody>
            <tr>

                <td><?php echo("R$ ".number_format($totalPago ,2,",",".")); ?></td>
                <td><?php echo("R$ ".number_format($totalPago - $valorDocumento ,2,",",".")); ?></td>
            </tr>
            </tbody>
        </table>
        <p id="valor2"></p>
        <h6>Sobre o Roteiro</h6>
    </div>

    <?php echo("<p style='font-size: 9px; font-weight: bold;'>".html_entity_decode( "SERVIÇO: ".$dadosGerais['serivco'], ENT_QUOTES, 'UTF-8')."</p>
                        <p style='font-size: 8px; text-align: justify;'>".strtoupper(html_entity_decode($dadosGerais['screenplay'], ENT_QUOTES, 'UTF-8'))."</p>"); ?>
    <?php if( $contador > 0 ){ ?>
        <?php foreach ( $registro as $item ){ ?>
            <?php echo("<p style='font-size: 9px; font-weight: bold;'>".html_entity_decode(strtoupper("SERVIÇO: ".$item->fullname), ENT_QUOTES, 'UTF-8')."</p>
                        <p style='font-size: 8px; text-align: justify;'>".strtoupper( html_entity_decode($item->screenplay, ENT_QUOTES, 'UTF-8'))."</p>"); ?>
        <?php }?>
    <?php }?>

    <p>CPF / Passaporte:_____________________________________________________________________</p>
    <p>E-mail:_____________________________________________________________________</p>
    <p>Assinatura:___________________________________________________________________________</p>
</div>
</body>
</html>
<?php

$html = ob_get_clean();

//------------------------------------------------------------------------------------------------------------

$arquivo = $dadosGerais['numbervoucher'].".pdf" ;
define( '_MPDF_TTFONTDATAPATH', sys_get_temp_dir() );
require_once( 'adm/relatorio/pdf/mpdf.php' );
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