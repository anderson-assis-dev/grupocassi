<?php
/**
 * Created by PhpStorm.
 * User: Ander
 * Date: 11/03/2019
 * Time: 13:21
 */
?>
<?php
require_once( '../.././config.php' );
ob_start();
define('MPDP_PATH', 'MPDF54/');
$abertura        = $_POST['abertura'];
$aberturaFinal   = $_POST['aberturafinal'];
$responsavel     = $_POST['responsavel'];
$cliente         = $_POST['cliente'];
if($responsavel > 0 and $cliente == 0)
{
    $informacoes = $pdo->prepare(
        'select a.voucher, a.description, a.`date` as dia, u.firstname, u.lastname,cfc.valueagente, c.fullname from `ct_audit` a
                left join `ct_createfaturacredit` cfc on a.voucher = cfc.numbervoucher left join `ct_reserva` r on r.numbervoucher = cfc.numbervoucher 
                left join `ct_cliente` c on c.id = r.idcliente left join `ct_usuario`u on u.id = a.idresponsible where cfc.valueagente > 0 and 
                cfc.dataagente >= :inicio and cfc.dataagente <= :fim and a.description like :dados and a.idresponsible = :por ');
    $informacoes->execute(
        array(
            ":dados"    => '%agente%',
            ":inicio"   => $abertura,
            ":fim"      => $aberturaFinal,
            ":por"      => $responsavel
        )
    );
}elseif ($responsavel == 0 and $cliente > 0)
{
    $informacoes = $pdo->prepare(
        'select a.voucher, a.description, a.`date` as dia, u.firstname, u.lastname,cfc.valueagente, c.fullname from `ct_audit` a left join `ct_createfaturacredit` cfc on a.voucher = cfc.numbervoucher
left join `ct_usuario`u on u.id = a.idresponsible left join `ct_reserva` r on r.numbervoucher = cfc.numbervoucher left join `ct_cliente` c on c.id = r.idcliente
where cfc.valueagente > 0 and cfc.dataagente >= :inicio and cfc.dataagente <= :fim and a.description like :dados and c.id = :por ');
    $informacoes->execute(
        array(
            ":dados"    => '%agente%',
            ":inicio"   => $abertura,
            ":fim"      => $aberturaFinal,
            ":por"      => $cliente
        ));
}
elseif ($responsavel > 0 and $cliente > 0)
{
    $informacoes = $pdo->prepare(
        'select a.voucher, a.description, a.`date` as dia, u.firstname, u.lastname,cfc.valueagente, c.fullname from `ct_audit` a left join `ct_createfaturacredit` cfc on a.voucher = cfc.numbervoucher
left join `ct_usuario`u on u.id = a.idresponsible left join `ct_reserva` r on r.numbervoucher = cfc.numbervoucher left join `ct_cliente` c on c.id = r.idcliente
where cfc.valueagente > 0 and cfc.dataagente >= :inicio and cfc.dataagente <= :fim and a.description like :dados and c.id = :por and a.idresponsible = :por2 ');
    $informacoes->execute(
        array(
            ":dados"    => '%agente%',
            ":inicio"   => $abertura,
            ":fim"      => $aberturaFinal,
            ":por"      => $cliente,
            ":por2"      => $responsavel
        ));
}
else{
    $informacoes = $pdo->prepare(
        'select a.voucher, a.description, a.`date` as dia, u.firstname, u.lastname,cfc.valueagente, c.fullname from `ct_audit` a left join `ct_createfaturacredit` cfc on a.voucher = cfc.numbervoucher
left join `ct_usuario`u on u.id = a.idresponsible left join `ct_reserva` r on r.numbervoucher = cfc.numbervoucher left join `ct_cliente` c on c.id = r.idcliente
where cfc.valueagente > 0 and cfc.dataagente >= :inicio and cfc.dataagente <= :fim and a.description like :dados ');
    $informacoes->execute(
        array(
            ":dados"    => '%agente%',
            ":inicio"   => $abertura,
            ":fim"      => $aberturaFinal
        ));
}
$registros = $informacoes->fetchAll(PDO::FETCH_CLASS);
$contador  = $informacoes->rowCount();
$totalPago = 0;
$cont = 0;
ob_clean();
?>
<style>
    th,td{font-size: 12px;}
</style>
<?php if ($contador > 0){  ?>
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta charset="utf-8">
        <title><?php echo( utf8_decode( "Relatório de Comissões" ) ); ?></title>
        <link rel="stylesheet" href="materialize.min.css">
    </head>
    <style>
        th, td{border: 1px solid #ddd; padding: 8px;}
    </style>
    <body>
    <div class="container">
        <img style="width: 700px; margin-left: 50px; " id="logo" src="../../images/logo.png"/>
        <hr>
        <p><?php echo( utf8_decode( "Relatório de Comissões: ".
                date("d/m/Y ", strtotime( $abertura ))." até ".date("d/m/Y ", strtotime( $aberturaFinal )))); ?> </p><br>
        <p style="font-size: 9px; margin-top: -20px;">Impresso em: <?php echo(date("d/m/Y - H:i:s")); ?></p>
        <table class="highlight">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Cliente</th>
                    <th>Voucher</th>
                    <th>Valor</th>
                    <th>Pago Por</th>
                    <th>Data do Pagamento</th>
                    <th><?php echo( utf8_decode( "Descrição do Pagamento" ) ); ?></th>

                </tr>
            </thead>
            <tbody>
            <?php foreach ($registros as $item){ $totalPago += $item->valueagente; ?>
                <tr>
                    <td><?php echo( $cont += 1 ); ?></td>
                    <td><?php echo( utf8_decode($item->fullname) ) ?></td>
                    <td><?php echo( $item->voucher ) ?></td>
                    <td><?php echo("R$ ". number_format($item->valueagente, 2, ",", "." ) ); ?></td>
                    <td><?php echo( $item->firstname." ".$item->lastname ) ?></td>
                    <td><?php echo( date( "d/m/Y H:i", strtotime($item->dia) ) ); ?></td>
                    <td><?php echo( utf8_decode( $item->description ) ); ?></td>

                </tr>
            <?php }?>
            </tbody>
        </table>
        <h5 align="center"><?php echo("Total Pago R$ ". number_format($totalPago, 2, ",", "." ) ); ?></h5>
    </div>
    </body>
    </html>
<?php } else { ?>
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta charset="utf-8">
        <title><?php echo( utf8_decode( "Relatório de Comissões" ) ); ?></title>
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
        <p><?php echo( utf8_decode( "Relatório de baixa: ".
                date("d/m/Y ", strtotime( $abertura ))." até ".date("d/m/Y ", strtotime( $aberturaFinal )))); ?> </p><br>
        <p style="font-size: 9px; margin-top: -20px;">Impresso em: <?php echo(date("d/m/Y - H:i:s")); ?></p>
        <h2>
            <?php echo( utf8_decode( "Não encontramos comissão pagas para o periódo informado.")); ?>
        </h2>

    </div>
    </body>
    </html>
<?php }?>
<?php
$html = ob_get_clean();

//------------------------------------------------------------------------------------------------------------
$arquivo = "relatorio-de-comissao.pdf" ;
define( '_MPDF_TTFONTDATAPATH', sys_get_temp_dir() );
require_once( 'pdf/mpdf.php' );
$mpdf = new mPDF('utf-8', 'A4-L');
$mpdf->SetTitle( "relatório" );
$mpdf->SetAuthor( 'Cassi Turismo' );
$html = mb_convert_encoding($html, 'UTF-8', 'ISO-8859-1');
$mpdf->WriteHTML( $html, 0 );
$css = file_get_contents("../.././vendor/bootstrap-4.1/bootstrap.min.css");
$mpdf->WriteHTML($css,1);
$mpdf->Output( $arquivo, 'I' );
$mpdf->charset_in = 'windows-1252';
?>

