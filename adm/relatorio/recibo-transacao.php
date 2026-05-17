<?php
require_once( '../.././config.php' );

$caixa         = $pdo->prepare(
    " select c.id,c.datevencimento, c.datecompetencia, c.datepagamento, c.descricao, forne.fullname, tc.`name` as tipo, cc.`name` as conta,
p.`name` as plano, s.`nameinvoice` as situacao, c.valor, c.nome,c.idcliente, u.firstname, u.lastname from  `ct_caixa` c left join ct_fornecedor forne on forne.id = c.idcliente left join ct_tipocaixa tc on tc.id = c.idtipo
left join ct_currentaccount cc on cc.id = c.idconta left join ct_planaccounts p on p.id = c.idplano left join ct_statusinvoice s on s.id = c.idstatus left join `ct_usuario` u on u.id = c.idusr where c.id = :id ");
$caixa->execute( array(":id" =>  $_POST['idtransacao']) );
$registroCaixa = $caixa->fetch(PDO::FETCH_ASSOC);

if( isset( $_POST['segundavia'] ) )
{
    $voucher = $_POST['voucher'];
    $valor   = $_POST['valor'];
    $teste = "%.$voucher.%";
    $caixa         = $pdo->prepare(
        " select c.id,c.datevencimento, c.datecompetencia, c.datepagamento, c.descricao, forne.fullname, tc.`name` as tipo, cc.`name` as conta,
p.`name` as plano, s.`nameinvoice` as situacao, c.valor, c.nome,c.idcliente, u.firstname, u.lastname from  `ct_caixa` c left join ct_fornecedor forne on forne.id = c.idcliente 
left join ct_tipocaixa tc on tc.id = c.idtipo left join ct_currentaccount cc on cc.id = c.idconta left join ct_planaccounts p on p.id = c.idplano 
left join ct_statusinvoice s on s.id = c.idstatus left join `ct_usuario` u on u.id = c.idusr where c.descricao like :descricao and c.valor = :valor ");
    $caixa->execute( array(":descricao" =>  '%'.$voucher.'%', ":valor" => $valor) );
    $registroCaixa = $caixa->fetch(PDO::FETCH_ASSOC);

}
ob_start();

?>


<?php if( $registroCaixa['fullname'] == 'POSTO DE GASOLINA' or utf8_encode($registroCaixa['fullname']) == 'COMBUSTÍVEL CARROS CASSI ERNANES'
    or utf8_encode($registroCaixa['fullname']) == 'COMBUSTÍVEL CARROS CASSI' or utf8_encode($registroCaixa['fullname']) == 'COMBUSTÍVEL CARROS CASSI ALEX'
    or utf8_encode($registroCaixa['fullname']) == 'COMBUSTÍVEL CARROS CASSI JOSE CLAUDIO' or utf8_encode($registroCaixa['fullname']) == 'COMBUSTÍVEL CARROS CASSI MARIO'
    or utf8_encode($registroCaixa['fullname']) == 'COMBUSTÍVEL CARROS CASSI ROMENIL' or utf8_encode($registroCaixa['fullname']) == 'COMBUSTÍVEL CARROS CASSI WELLINGTON'
    or utf8_encode($registroCaixa['fullname']) == 'COMBUSTIVEL CARROS CASSI REGINALDO PERREIRA' or utf8_encode($registroCaixa['fullname']) == 'COMBUSTÍVEL CARROS CASSI MERCÊS'
    or utf8_encode($registroCaixa['fullname']) == 'CARRO CONTRATADO ISAC EDER' or utf8_encode($registroCaixa['fullname']) == 'CARRO CONTRATADO CARLOS'
    or utf8_encode($registroCaixa['fullname']) == 'CARRO CONTRATADO IGOR' or utf8_encode($registroCaixa['fullname']) == 'CARRO CONTRATADO EDGAR'
    or utf8_encode($registroCaixa['fullname']) == 'CARRO CONTRATADO ANDRE' or utf8_encode($registroCaixa['fullname']) == 'CARRO CONTRATADO JEAN'
    or utf8_encode($registroCaixa['fullname']) == 'CARRO CONTRATADO PITA' or utf8_encode($registroCaixa['fullname']) == 'CARRO CONTRATADO DJAVAN'
    or utf8_encode($registroCaixa['fullname']) == 'CARRO CONTRATADO ROCK' or utf8_encode($registroCaixa['fullname']) == 'VAN CONTRATADA EDNEI'
    or utf8_encode($registroCaixa['fullname']) == 'VAN CONTRATADA AILTON' or utf8_encode($registroCaixa['fullname']) == 'CARROS CONTRATADOS MAR GRANDE'
    or utf8_encode($registroCaixa['fullname']) == 'CARRO CONTRATADO VALDINEI' or utf8_encode($registroCaixa['fullname']) == 'CARRO CONTRATADO NETO'
    or utf8_encode($registroCaixa['fullname']) == 'CARRO CONTRATADO LUCIANO'){ ?>
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta charset="utf-8">
        <title><?php echo( utf8_decode( "Recibo do caixa ".$registroCaixa['conta'] ) ); ?></title>
        <link rel="stylesheet" href="materialize.min.css">
    </head>
    <body>
    <div class="container">
	<p>N� <?php echo($registroCaixa['id']); ?></p>
        <h5 align="center"><?php echo( utf8_decode( "COMBUSTÍVEL
         " ) ); ?></h5>
        <p style="font-size: 7px; margin-top: -20px;">Impresso em: <?php echo(date("d/m/Y")); ?></p><br>
        <p style="font-size: 7px; margin-top: -20px;">Pago por: <?php echo($registroCaixa['firstname']." ".$registroCaixa['lastname']); ?></p>

        <p style="font-size: 13px; margin-top: 70px">
            <?php echo(  utf8_decode("Recebi DA CASSI TURISMO R$ "
                .number_format($registroCaixa['valor'], 2, ",",".").", ".$registroCaixa['descricao']) ); ?>
        </p>
        <p style="font-size: 13px; margin-top: 70px">
            KM _______________INICIO_____________________ <br><br>
            KM________________FINAL______________________
        </p>
        <p style="font-size: 13px; margin-top: 70px">
            PLACA DO CARRO: <br>
            ROTA:____________________________________________________________________________
        </p>
        <p align="left" style="margin-top: 70px">
            <?php echo( "Salvador, ".date("d/m/Y", strtotime($registroCaixa['datevencimento'])) ); ?>
        </p>
        <p align="center" style="margin-top: 70px">
            <?php echo( " _________________________________________________________________________<br><br>" ); ?>
            <?php echo( utf8_decode( $registroCaixa['nome'] ) ); ?>
        </p>
    </div>
    </body>
    </html>
<?php } elseif( utf8_encode($registroCaixa['fullname']) == 'PRESTAÇÃO DE SERVIÇO' ) { ?>
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta charset="utf-8">
        <title><?php echo( utf8_decode( "Recibo do caixa ".$registroCaixa['conta'] ) ); ?></title>
        <link rel="stylesheet" href="materialize.min.css">
    </head>
    <style>
        table{font-size: 10px;}
    </style>
    <body>
    <div class="container">
	

        <h5 align="center"><?php echo( utf8_decode( "RECIBO N�" )." ".$registroCaixa['id'] ); ?></h5>
        <p style="font-size: 7px; margin-top: -20px;">Impresso em: <?php echo(date("d/m/Y")); ?></p><br>
        <p style="font-size: 7px; margin-top: -20px;">Pago por: <?php echo($registroCaixa['firstname']." ".$registroCaixa['lastname']); ?></p>

        <p style="font-size: 13px; margin-top: 70px">
            <?php echo(  utf8_decode("RECEBI DA CASSI TURISMO a importância de R$ "
                .number_format($registroCaixa['valor'], 2, ",",".").", referente a ".$registroCaixa['descricao']) ); ?>
        </p>
        <p align="left" style="margin-top: 70px">
            <?php echo( "Salvador, ".date("d/m/Y", strtotime($registroCaixa['datevencimento'])) ); ?>
        </p>
        <p align="left" style="margin-top: 70px">
            <?php echo( "Data Recebimento, ____/____/____" ); ?>
        </p>
        <p align="center" style="margin-top: 70px">
            <?php echo( " _________________________________________________________________________<br><br>" ); ?>
            <?php echo( utf8_decode( $registroCaixa['nome'] ) ); ?>
        </p>
    </div>
    </body>
    </html>
<?php }  elseif( $registroCaixa['fullname'] == 'GUIA DE TURISMO') { ?>
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta charset="utf-8">
        <title><?php echo( utf8_decode( "Recibo do caixa ".$registroCaixa['id'] ) ); ?></title>
        <link rel="stylesheet" href="materialize.min.css">
    </head>
    <style>
        table{font-size: 10px;}

    </style>
    <body>
    <div class="container">
        <h5 align="center"><?php echo( utf8_decode( "RECIBOS " )." ".$registroCaixa['id'] ); ?></h5>
        <p style="font-size: 7px; margin-top: -20px;">Impresso em: <?php echo(date("d/m/Y")); ?></p><br>
        <p style="font-size: 7px; margin-top: -20px;">Pago por: <?php echo($registroCaixa['firstname']." ".$registroCaixa['lastname']); ?></p>

        <p style="font-size: 13px; margin-top: 70px">
            <?php echo(  utf8_decode("RECEBI DA CASSI TURISMO a importância de R$ "
                .number_format($registroCaixa['valor'], 2, ",",".").", referente a ".$registroCaixa['descricao']) ); ?>
        </p>
        <p align="left" style="margin-top: 70px">
            <?php echo( "Salvador, ".date("d/m/Y", strtotime($registroCaixa['datevencimento'])) ); ?>
        </p>
        <p align="center" style="margin-top: 70px">
            <?php echo( " _________________________________________________________________________<br><br>" ); ?>
            <?php echo( utf8_decode( $registroCaixa['nome'] ) ); ?>
        </p>
    </div>
    </body>
    </html>
<?php } elseif( $registroCaixa['fullname'] == 'COMPRA DE FOLGA')  { ?>
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta charset="utf-8">
        <title><?php echo( utf8_decode( "DECLARAÇÃO DE VENDA DE FOLGA R$".$registroCaixa['valor'] ) ); ?></title>
        <link rel="stylesheet" href="materialize.min.css">
    </head>
    <body >
    <div class="container">
	<?php echo( utf8_decode( "RECIBO Nº".$registroCaixa['id'] ) ); ?>
        <img width="30%" height="30%" style="margin-left: 210px; margin-top: 50px;"  id="logo" src="../.././images/logo2.png"/>
        <p style="font-size: 7px; margin-top: -20px;">Pago por: <?php echo($registroCaixa['firstname']." ".$registroCaixa['lastname']); ?></p>
        <p align="right" style="margin-top: 85px; font-weight: bold;">
            <?php echo( "R$ ".number_format($registroCaixa['valor'], 2, ",",".") ); ?>
        </p>
        <p style="font-size: 13px; margin-top: 85px; text-align: justify;">
            <?php echo(  utf8_decode("Recebi da empresa CASSI TURISO, quantia de  R$ "
                .number_format($registroCaixa['valor'], 2, ",",".").", referente ao
                 pagamento da diária de serviço prestado no dia da minha folga ".date("d/m/Y",strtotime( $registroCaixa['datevencimento'] ) )
                ." onde me disponibilizei a trabalhar por iniciativa própria.") ); ?>
        </p>
        <p align="center" style="margin-top: 70px;">
            <?php echo( strtoupper( strftime("%d de %B de %Y" ) ) ); ?>
        </p>
        <p align="center" style="margin-top: 60px;">
            <?php echo( " _________________________________________________________________________<br><br>" ); ?>
            <?php echo( utf8_decode( $registroCaixa['nome'] ) ); ?>
        </p>
    </div>
    </body>
    </html>
<?php } else { ?>
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta charset="utf-8">
        <title><?php echo( utf8_decode( "Recibo do caixa ".$registroCaixa['conta'] ) ); ?></title>
        <link rel="stylesheet" href="materialize.min.css">
    </head>
    <style>
        table{font-size: 10px;}

    </style>
    <body>
    <div class="container">
        <img style="width: 700px;" id="logo" src="../.././images/logo.png"/>
        <hr>
        <h5 align="center"><?php echo( utf8_decode( "RECIBO N�".$registroCaixa['id']) ); ?></h5>
        <p style="font-size: 7px; margin-top: -20px;">Impresso em: <?php echo(date("d/m/Y")); ?></p><br>
        <p style="font-size: 7px; margin-top: -20px;">Pago por: <?php echo($registroCaixa['firstname']." ".$registroCaixa['lastname']); ?></p>

        <p style="font-size: 13px; margin-top: 70px">
            <?php echo(  utf8_decode("RECEBI DA CASSI TURISMO a importância de R$ "
                .number_format($registroCaixa['valor'], 2, ",",".").", referente a ".$registroCaixa['descricao']) ); ?>
        </p>
        <p align="left" style="margin-top: 70px">
            <?php echo( "Salvador, ".date("d/m/Y", strtotime($registroCaixa['datevencimento'])) ); ?>
        </p>
        <p align="center" style="margin-top: 70px">
            <?php echo( " _________________________________________________________________________<br><br>" ); ?>
            <?php echo( utf8_decode( $registroCaixa['nome'] ) ); ?>
        </p>
    </div>
    </body>
    </html>
<?php }?>


<?php

$html = ob_get_clean();
//------------------------------------------------------------------------------------------------------------
$arquivo = date("d/m/Y").".pdf" ;
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
