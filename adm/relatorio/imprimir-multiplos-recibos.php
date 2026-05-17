<?php
require_once( '../.././config.php' );
ob_start();
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8">
    <title><?php echo( "Recibos-gerados--em-".date("d-m-Y").".pdf" ); ?></title>
    <link rel="stylesheet" href="materialize.min.css">
</head>
<style>
    table{font-size: 10px;}

</style>
<body>
<?php for ($contador = 1; $contador <= $_POST['totallinhas']; $contador++){
    $nome = "nome"."".$contador;
    $descricao = "descricao"."".$contador;
    $diaria = "diaria"."".$contador;
    $data = "datapagamento"."".$contador;
    if($_POST["$data"] <> null)
    {
        $novaTransacao = $pdo->prepare(
            "insert into `ct_caixa` (`id`, `datevencimento`, `datepagamento`, `datecompetencia`, `nome` ,`descricao`, `idcliente`, `idtipo`, `idconta`, 
                     `idplano`, `idempresa` ,`idstatus`, `valor`, `idusr`, `dataabertura`) values (DEFAULT, :vencimento, :pagamento, :competencia, :nome ,:descricao, 
                      :cliente, :tipo, :conta, :plano, :empresa ,:statuus, :valor, :idusr, :abertura)");

        $novaTransacao->execute(
            array(
                ":vencimento"  => $_POST["$data"],
                ":pagamento"   => $_POST["$data"],
                ":competencia" => $_POST["$data"],
                ":nome"        => $_POST["$nome"],
                ":descricao"   => $_POST["$descricao"],
                ":cliente"     => 96,
                ":tipo"        => 2,
                ":conta"       => 18,
                ":plano"       => 1,
                ":empresa"     => 1,
                ":statuus"     => 3,
                ":valor"       => str_replace(",", ".", $_POST["$diaria"]),
                "idusr"        => $_SESSION['id'],
                ":abertura"    => date("Y-m-d")
            )
        );
        $idtransacao = $pdo->lastInsertId();
    }
?>
    <?php if($_POST["$data"] <> null) {?>
        <div class="container">
            <img width="30%" height="30%" style="margin-left: 210px; "  id="logo" src="../.././images/logo2.png"/>
            <h5 align="center"><?php echo( utf8_decode( "RECIBO " ) ); ?></h5>
            <p style="font-size: 7px; margin-top: -20px;">Impresso em: <?php echo(date("d/m/Y")); ?></p><br>
            <h5 align="center"><?php echo( utf8_decode( "RECIBO Nº ".$idtransacao) ); ?></h5>
            <p style="font-size: 7px; margin-top: -20px;">Pago por: <?php echo($_SESSION['nome']); ?></p>
            <p style="font-size: 13px; margin-top: 70px">
                <?php echo(  utf8_decode("RECEBI DA CASSI TURISMO a importância de R$ "
                    .number_format($_POST["$diaria"], 2, ",",".").", referente  ".utf8_decode($_POST["$descricao"]." em ".date("d/m/Y", strtotime($_POST["$data"])))) ); ?>
            </p>
            <p align="left" style="margin-top: 70px">
                <?php echo( "Salvador, ".date("d/m/Y") ); ?>
            </p>
            <p align="center" style="margin-bottom: 300px;">
                <?php echo( " _________________________________________________________________________<br><br>" ); ?>
                <?php echo( utf8_decode( $_POST["$nome"] ) ); ?>
            </p>
        </div>
    <?php }?>

<?php }?>

</body>
</html>

<?php

$html = ob_get_clean();
//------------------------------------------------------------------------------------------------------------
$arquivo = "recibos-gerados--em-".date("d-m-Y").".pdf" ;
define( '_MPDF_TTFONTDATAPATH', sys_get_temp_dir() );
require_once( 'pdf/mpdf.php' );
$mpdf = new mPDF();
$mpdf->SetTitle( "relatório" );
$mpdf->SetAuthor( 'Cassi Turismo' );
$html = mb_convert_encoding($html, 'UTF-8', 'ISO-8859-1');
$mpdf->WriteHTML( $html, 0 );
$mpdf->Output( $arquivo, 'I' );
$mpdf->charset_in = 'windows-1252';


?>
