<?php
require_once( '../.././config.php' );
ob_start();
define('MPDP_PATH', 'MPDF54/');
$abertura        = $_POST['abertura'];
$aberturaFinal   = $_POST['aberturafinal'];
$tipo            = $_POST['tipo'];

$buscarConferencia = $pdo->prepare(
    'select r.id, dateinput, dateoutput ,numbervoucher, c.namefantazia as cliente, pax, u.firstname, u.lastname, s.fullname as servico,
               cp.namepayment as pagamento, qtdpax, qtdchild, nameinvoice as statuu, r.valueservice, r.idstatusinvoice
               from `ct_reserva` r  left join `ct_cliente` c on c.`id` = r.`idcliente` left join `ct_usuario` u on u.`id` = r.`idresponsavel`
               left join `ct_servico` s on s.`id` = r.`idservico` left join `ct_form_of_ payment` cp on cp.`id` = r.`idpayment`
               left join `ct_statusinvoice` si on si.`id` = r.`idstatusinvoice` where r.abertura >= :abertura and r.abertura <= :aberturafinal and r.idstatusinvoice = :st
               order by r.numbervoucher ');
$buscarConferencia->execute( array( ":abertura" => $abertura,":aberturafinal" => $aberturaFinal, ":st" => 2 ) );
$registro_one = $buscarConferencia->fetchAll(PDO::FETCH_CLASS);
$totais = 0;
ob_clean();
?>
<style>
    th,td{font-size: 12px;}
</style>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8">
    <title><?php echo( utf8_decode( "Inventário" ) ); ?></title>
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
    <p><?php echo( utf8_decode( "Relatório de Inventário de reservas canceladas de: ".
            date("d/m/Y ", strtotime( $abertura ))." até ".date("d/m/Y ", strtotime( $aberturaFinal )))); ?> </p><br>
    <p style="font-size: 9px; margin-top: -20px;">Impresso em: <?php echo(date("d/m/Y - H:i:s")); ?></p>

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
            <th>VALOR</th>
            <th>STATUS</th>
        </tr>
        </thead>
        <tbody>
            <?php foreach ( $registro_one as $item ){
                    $valorBruto = ($item->valueservice * $item->qtdpax) + ($item->valueservice * ($item->qtdchild / 2) );
                    $totais += (($item->valueservice * $item->qtdpax) + ($item->valueservice * ($item->qtdchild / 2) ) );
                ?>
            <tr>
                <td><?php echo( date("d/m/Y", strtotime($item->dateinput))  ); ?></td>
                <td><?php echo( $item->numbervoucher ); ?></td>
                <td><?php echo( utf8_decode($item->cliente)   ); ?></td>
                <td><?php echo( utf8_decode($item->pax)  ); ?></td>
                <td><?php echo( $item->qtdpax."/".$item->qtdchild); ?></td>
                <td><?php echo( utf8_decode($item->firstname." ". $item->lastname)  ); ?></td>
                <td><?php echo( $item->servico ); ?></td>
                <td><?php echo( "R$ ".number_format( $valorBruto,2,",","." ) ); ?></td>
                <td><?php echo( $item->statuu ); ?></td>
            </tr>
            <?php }?>
        </tbody>
    </table>
    <hr>
    <table class="highlight">
        <thead>
        <tr>
            <th>VALOR TOTAL</th>
            <th>TOTAL DE VENDAS</th>
        </tr>
        </thead>
        <tbody>
            <tr>
                <td><?php echo( "R$ ".number_format( $totais,2,",","." ) ); ?></td>
                <td><?php echo( $rows = $buscarConferencia->rowCount() ); ?></td>
        </tr>
        </tbody>

    </table>

</div>
</body>
</html>

<?php
$html = ob_get_clean();
$arquivo = "Relatório-Inventário".date("d/m/Y", strtotime($abertura) ).".pdf" ;
define( '_MPDF_TTFONTDATAPATH', sys_get_temp_dir() );
require_once( 'pdf/mpdf.php' );
$mpdf = new mPDF('utf-8', 'A4-L');
$mpdf->SetTitle( "relatório" );
$mpdf->SetAuthor( 'Cassi Turismo' );
$html = mb_convert_encoding($html, 'UTF-8', 'ISO-8859-1');
$mpdf->WriteHTML( $html, 0 );
$mpdf->Output( $arquivo, 'I' );
$mpdf->charset_in = 'windows-1252';


?>
