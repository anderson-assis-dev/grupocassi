<?php
require_once( '../.././config.php' );

if( isset( $_POST['auditoria'] )  )
{
    $numberVoucher = $_POST['voucher'];
    $dadosReserva  = $pdo->prepare("select * from `ct_audit` where `voucher` = :numberVoucher ");
    $dadosReserva->execute( array(":numberVoucher" => $numberVoucher ) );
    $registro      =  $dadosReserva->fetchAll(PDO::FETCH_CLASS);
}

ob_start();
?>
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta charset="utf-8">
        <title>Auditoria |CASSI TURISMO </title>
        <link rel="stylesheet" href="materialize.min.css">
    </head>
    <body>
    <div class="container">
        <img style="width: 700px; margin-left: 100px;" id="logo" src="../../images/logo.png"/>
        <h4>Auditoria do Voucher <?php echo($numberVoucher); ?> </h4>
        <div class="table-responsivo">
            <table class="table table-bordeded">
                <thead>
                <th>Data</th>
                <th>Descrição</th>
                </thead>
                <tbody>
                <?php foreach ($registro as $item) {
                    $buscarResponsavel = $pdo->prepare('select * from `ct_usuario` where `id` = :id');
                    $buscarResponsavel->execute(array(":id" => $item->idresponsible));
                    $dados = $buscarResponsavel->fetch(PDO::FETCH_ASSOC);
                    $timestamp = strtotime($item->date);
                    ?>
                    <tr>
                        <td><?php echo( strftime("%A,  %d de %B de %Y", $timestamp) ); ?></td>
                        <td><?php echo($item->description." ". $dados['firstname']." ".$dados['lastname']); ?></td>
                    </tr>
                <?php }?>
                </tbody>
            </table>
        </div>
    </div>

    </body>
    </html>
<?php
$html = ob_get_clean();

//------------------------------------------------------------------------------------------------------------
$arquivo = "lista.pdf" ;
define( '_MPDF_TTFONTDATAPATH', sys_get_temp_dir() );
require_once( 'pdf/mpdf.php' );
$mpdf = new mPDF();
$mpdf->SetTitle( "relatório" );
$mpdf->SetAuthor( 'Cassi Turismo' );
$mpdf->WriteHTML( $html, 0 );
$css = file_get_contents("../.././vendor/bootstrap-4.1/bootstrap.min.css");
$mpdf->WriteHTML($css,1);
$mpdf->Output( $arquivo, 'I' );
$mpdf->charset_in = 'windows-1252';
?>