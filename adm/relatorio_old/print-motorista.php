<?php
/**
 * Created by PhpStorm.
 * User: Ander
 * Date: 01/03/2019
 * Time: 11:25
 */
ob_start();
require_once( '../.././config.php' );
$motorista = $_POST['motorista'];
$inicio    = $_POST['inicio'];
$fim       = $_POST['fim'];
$adulto  = 0;
$crianca = 0;
$free    = 0;
$order_service = $pdo->prepare('select * from `ct_orderservice` os left join `ct_servico` s on s.id = os.idservico  where `namedriver` = :namedriver and `date` >= :dataone and `date` <= :datetwo ');
$order_service->execute(array(":namedriver" => $motorista, ":dataone" => $inicio, ":datetwo" => $fim));
$register = $order_service->fetchAll(PDO::FETCH_CLASS);
?>
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta charset="utf-8">
        <title><?php echo( utf8_decode( "Ordem de serviço" ) ); ?></title>
        <link rel="stylesheet" href="materialize.min.css">
    </head>
    <style>
        th, td{border: 1px solid #ddd; padding: 8px; font-size: 9px;}
    </style>
    <body>
    <div class="container">
        <img style="width: 700px; margin-left: 50px; " id="logo" src="../../images/logo.png"/>
        <hr>
        <h5><?php echo( utf8_decode( "Ordem de serviço para o motorista ".
                $motorista)); ?> </h5><br>
        <p style="font-size: 9px; margin-top: -20px;">Impresso em: <?php echo(date("d/m/Y - H:i:s")); ?></p>

        <table class="highlight">
            <thead>
                <tr>
                    <th>PAX | CHILD | FREE</th>
                    <th>FILE | PAX | TEL</th>
                    <th>SERVICO</th>
                    <th>APANHA</th>
                    <th>COMPLEMENTO</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($register as $item){ $adulto += $item->tpax; $crianca += $item->tchild; $free += $item->tfree; ?>
                <tr>
                    <td><?php echo($item->tpax." | ".$item->tchild." | ".$item->tfree); ?></td>
                    <td><?php echo($item->voucher." | ".utf8_decode($item->namepax)." | ".$item->phone); ?></td>
                    <td><?php echo($item->fullname); ?></td>
                    <td><?php echo(date("H:i", strtotime($item->apanha))); ?></td>
                    <td><?php echo($item->complemento); ?></td>
                </tr>
            <?php }?>
            </tbody>
        </table>
        <br>
        <p align="center"><?php echo("Total de Pax (".$adulto.") Total de Child (".$crianca.")  Total de Free (".$free.")"); ?></p>
    </div>
    </body>
    </html>
<?php

$html = ob_get_clean();
$arquivo = "Ordem de serviço para o motorista ".$motorista." em ".date("d-m-Y")  ;
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