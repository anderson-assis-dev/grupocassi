<?php
require_once( '../.././config.php' );
$totalAdd  = 0;
$totalPago = 0;
if( isset( $_POST['buscar'] )  )
{

    $sql = "SELECT r.idservico,r.id,pax, documento, dateinput, dateoutput, photoresident, c.fullname as cliente, c.observacao ,s.fullname as `status`, r.horaap, u.firstname, u.lastname,
    se.fullname as serivco, ag.fullname as agente, priceadult, namepayment, g.fullname as guia, qtdpax, qtdchild, qtdfree, ss.schedule, r.voo, r.confirmacao,
    pricechild,numbervoucher, r.valueservice, r.abertura, se.screenplay, roteiro
    FROM `ct_reserva` r left join ct_cliente c on c.id = r.idcliente
    left join ct_usuario u on u.id = r.idresponsavel left join ct_status s on s.id = r.idstatus
    left join ct_guia g on g.id = r.idguia join ct_servico se on se.id = r.idservico
    left join ct_agentes as ag on r.idagente = ag.id left join `ct_servico_horario` sr on sr.idservice = r.idservico and sr.idschedule = r.idhorario 
    left join ct_service_schedule ss on ss.idshedule = r.idhorario left join `ct_form_of_ payment` as cfp
    on cfp.id = r.idpayment  where 1=1";
    if($_POST['cliente'] >0 )
    {
        $sql .= " and r.idcliente = ".$_POST['cliente'];
    }
    if(isset( $_POST['inicio'] ) )
    {
        $sql .= " and r.dateinput = '".$_POST['inicio']."' and r.dateinput = '".$_POST['fim']."' ";
    }
    echo($sql);
    $dadosReserva = $pdo->prepare($sql);
    $dadosReserva->execute();
    $dadosGerais = $dadosReserva->fetchAll(PDO::FETCH_CLASS);
    $sql = "SELECT ra.idservice,ra.dateinput as ap, s.fullname,s.screenplay, s.priceadult, s.pricechild, ss.schedule, qpax, qchild, qfree, ra.valueservice, ra.horaap,ra.documento, sr.roteiro, ra.confirmacao2, r.numbervoucher
    FROM `ct_recentlyadd` ra left join `ct_reserva` r on r.id = ra.idrecently left join `ct_servico_horario` sr on sr.idservice = ra.idservice and sr.idschedule = ra.idschedule
    left join ct_servico s on s.id = ra.idservice left join ct_service_schedule ss
    on ss.idshedule = ra.idschedule where 1=1 ";
    if($_POST['cliente'] >0 )
    {
        $sql .= " and r.idcliente = ".$_POST['cliente'];
    }
    if(isset( $_POST['inicio'] ) )
    {
        $sql .= " and ra.dateinput = '".$_POST['inicio']."' and ra.dateinput = '".$_POST['fim']."' ";
    }
    $adicionais = $pdo->prepare($sql);
    $adicionais->execute();
    $registro = $adicionais->fetchAll(PDO::FETCH_CLASS);
    $contador = $adicionais->rowCount();
}


ob_start();
?>
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta charset="utf-8">
        <title> <?php echo("NUMERO-DO-VOUCHER".$dadosGerais['numbervoucher']); ?></title>
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
            <h6 align="center" style="font-weight: bold;"><?php echo( utf8_decode('Relatório de confirmação de embarque') ); ?></h6>
            <table class="responsive-table" style="font-size: 10px;">
                <tr style=" margin-bottom:  -10px;">
                    <thead>
                    <th><?php echo(utf8_decode('Serviço')) ?></th>
                    <th><?php echo(('Voucher')) ?></th>
                    <th><?php echo(('Data')) ?></th>
                    <th><?php echo(('Hora')) ?></th>
                    <th><?php echo(utf8_decode('Apresentação')) ?></th>
                    <th>P/C/F</th>
                    <th>Embarcado</th>
                    </thead>
                </tr>
                <?php foreach ($dadosGerais as $item){ 
                        if($item->confirmacao)
                        {
                            $situacao = "Sim";
                            $totalAdd += 1;
                        }
                        else
                        {
                            $situacao = "Não";
                        }
                    ?>
                    <tr>
                        <tbody>
                            <td><?php echo($item->serivco); ?></td>
                            <td><?php echo($item->numbervoucher); ?></td>
                            <td><?php  echo(date("d/m/Y", strtotime($item->dateinput))  ); ?></td>
                            <?php if($item->idservico == 15){ ?>
                                <td id="ap"><?php echo("<strong> >".date("H:i", strtotime($item->horaap))."< </strong>" ); ?></td>
                            <?php } else { ?>
                                <td id="ap"><?php echo("<strong> >".date("H:i", strtotime($item->dateinput." ".$item->schedule))."< </strong>"); ?></td>
                            <?php }?>
                            <td id="ap"><?php echo("<strong> >".date("H:i", strtotime($item->horaap))."< </strong>" ); ?></td>
                            <td><?php echo($item->qtdpax."/".$item->qtdchild."/".$item->qtdfree); ?></td>
                            <td><?php echo(utf8_decode($situacao)) ?></td>
                        </tbody>
                    </tr>
                <?php }?>    

                <?php if($contador > 0) { ?>
                    <?php foreach ($registro as $item){
                        $timestampAdd = strtotime( $item->ap );
                        if($item->confirmacao2)
                        {
                            $situacao = "Sim";
                            $totalAdd += 1;
                        }
                        else
                        {
                            $situacao = "Não";
                        }
                        ?>
                        <tr>
                           
                            <td><?php echo($item->fullname); ?></td>
                            <td><?php echo($item->numbervoucher); ?></td>
                            <td><?php echo(date("d/m/Y",$timestampAdd)  ); ?></td>
                            <?php if($item->idservice == 15){ ?>
                                <td id="ap"><?php echo("<strong> >".date("H:i", strtotime($item->horaap))."< </strong>"); ?></td>
                            <?php } else { ?>
                                <td id="ap"><?php echo("<strong> >".date("H:i", strtotime($item->ap." ".$item->schedule))."< </strong>"); ?></td>
                            <?php }?>

                            <td id="ap"><?php echo("<strong> >".date("H:i", strtotime($item->horaap))."< </strong>"); ?></td>
                            <td><?php echo($item->qpax."/".$item->qchild."/".$item->qfree); ?></td>
                            <td><?php echo(utf8_decode($situacao)) ?></td>
                          
                        </tr>
                    <?php }?>
                <?php }?>
            </table>
        </div>
    </div>
    </body>
    </html><?php

$html = ob_get_clean();

//------------------------------------------------------------------------------------------------------------
$arquivo = "relatorio.pdf" ;
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
