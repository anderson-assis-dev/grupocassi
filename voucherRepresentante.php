<?php
require_once( './config.php' );
header('Content-Type: text/html; charset=iso-8859-1');
$totalAdd  = 0;
$totalPago = 0;
if( isset( $_GET['voucher'] )  )
{
    $dadosReserva = $pdo->prepare(
        "SELECT r.id,pax, documento, dateinput, dateoutput, photoresident, c.fullname as cliente, s.fullname as `status`, r.horaap, u.firstname,
                  se.fullname as passeio, ag.fullname as agente, priceadult, namepayment, g.fullname as guia, qtdpax, qtdchild, qtdfree, ss.schedule,
                  pricechild,numbervoucher, r.valueservice, r.abertura, se.screenplay
                  FROM `ct_reserva` r left join ct_cliente c on c.id = r.idcliente
                  left join ct_usuario u on u.id = r.idresponsavel left join ct_status s on s.id = r.idstatus
                  left join ct_guia g on g.id = r.idguia join ct_servico se on se.id = r.idservico
                  left join ct_agentes as ag on r.idagente = ag.id
                  left join ct_service_schedule ss on ss.idshedule = r.idhorario left join `ct_form_of_ payment` as cfp
                  on cfp.id = r.idpayment  where `numbervoucher` = :numbervoucher ");
    $dadosReserva->execute( array(":numbervoucher" => $_GET['voucher'] ) );
    $dadosGerais = $dadosReserva->fetch(PDO::FETCH_ASSOC);

    $subTotal    = ( ( $dadosGerais['valueservice'] * $dadosGerais['qtdpax'] ) + ( $dadosGerais['valueservice'] * $dadosGerais['qtdchild'] ) );
    $comissao    = 20 * $dadosGerais['qtdpax'];
    $taxaCartao  = 15 * $dadosGerais['qtdpax'];
    $valorTotal  = $subTotal - $comissao;
    $totalCartao = $valorTotal + $taxaCartao;

}
ob_start();
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8">
    <title> Voucher </title>
    <link rel="stylesheet" href="materialize.min.css">
</head>
<style>
    strong#1{
        font-weight: bold;
        color: #000;
    }
    strong#2{
        font-weight: bold;
        color: red;
    }
    p#desc{
        font-size: 10px;
    }

</style>
<body>
<img src="./images/logo.png" style="margin-bottom: 20px;"><br>
<img src="./images/vouchercassi.jpeg">
<div >
    <h6 style="color: darkblue; font-weight: bold; font-size: 16px" align="center">VOUCHER VALOR ÚNICO</h6>
    <p style="color: darkblue; font-weight: bold; font-size: 14px" align="center">PAGAMENTO DE R$ 20,00 POR (SERVIÇO / TRECHO / PESSOA) NO MOMENTO DA EMISSÃO DESTE VOUCHER</p>
    <h6 align="center" style="color: red; font-weight: bold; font-size: 16px ">ATENÇÃO</h6>
    <p align="center" style="color: red; font-weight: bold; font-size: 14px">
        ESTE VOUCHER SÓ TERÁ VALIDADE APÓS O PAGAMENTO DA DIFERENÇA DO VALOR CORRESPONDENTE AO SERVIÇO CONTRATADO.</p>
    <p align="center" style="color: darkblue; font-weight: bold">ESTA DIFERENÇA SÓ PODE SER PAGA EM UMA DAS AGÊNCIAS CASSI TURISMO.</p>
    <table class="responsive-table">
        <thead>
        <tr>
            <th>Nome</th>
            <th>qtdpax</th>
            <th>Saída / Destino</th>
            <th>Embarque</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td><strong id="2"><?php echo($dadosGerais['pax']); ?></strong></td>
            <td><strong id="2"><?php echo("0".$dadosGerais['qtdpax']); ?></strong></td>
            <td><strong id="2"><?php echo($dadosGerais['passeio']); ?></strong></td>
            <td><strong id="2"><?php echo( date("d/m/Y", strtotime($dadosGerais['dateinput']))." às ".
                        date("H:i", strtotime( $dadosGerais['horaap'] ) ) ); ?></strong></td>
        </tr>
        </tbody>

    </table>
    <p id="desc">
        <?php
        echo("<strong id='1'>Valor da adesão R$ ".
                number_format(20, 2,",", "."). "por serviço / trecho / pessoa</strong> ")."<strong id='2'>R$  ".
            number_format($comissao, 2 ,",", ".")."</strong>";
        ?>
    </p>
    <p id="desc">
        <?php
        echo("<strong id='1'>Valor a ser pago na agência Cassi Turismo em dinheiro</strong><strong id='2'> R$ ".
            number_format($valorTotal, 2,",", ".")."</strong>");
        ?>
    </p>
    <p id="desc">
        <?php
        echo("<strong id='1'>ou Cartão(Débito / Crédito)</strong><strong id='2'> R$ ".
            number_format($totalCartao, 2,",", ".")."</strong>");
        ?>
    </p>
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

$mpdf->WriteHTML( $html, 0 );
$mpdf->Output( $arquivo, 'I' );
$mpdf->charset_in = 'windows-1252';
?>
