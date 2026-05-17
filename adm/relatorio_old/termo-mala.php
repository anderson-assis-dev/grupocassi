<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8">
    <title>Termo das malas</title>
    <link rel="stylesheet" href="materialize.min.css">
</head>
<body>
<div class="container">
    <h5 align="center">Termo de responsabilidade sobre as malas</h5><br>
    <p align="justify">
        Caso o(s) passageiro(s) tenha(m) interesse em contratar o serviço de mala fornecido pela Cassi turismo, em suas lojas com funcionamento das 06:00 até as 16:30, o
        mesmo deverá efetuar o pagamento de R$ 10,00 por mala. As malas que forem entregues aos cuidados da Cassi Turismo, estarão devidamente alocadas num recipiente
        reservado para as malas.
    </p><br>
    <p align="justify">
        Cabe a Cassi Turismo na prestação do serviço de mala, manter as malas no local reservado durante o período de funcionamento descrito no parágrafo anterior.
    </p><br>
    <p align="justify">
        Em casos nos quais o passageiro por um descuido esquecer a mala na loja em que o serviço foi contratado, a Cassi Turismo não tem responsabilidade/cumprimento de
        entregar a(s) mala(s) após o período de funcionamento do serviço.
    </p><br>
    <p align="justify">
        O passageiro terá total liberdade de retirar a(s) mala(s) entregues para o serviço a qualquer momento. Sendo que, uma vez retirada estará findado a validade do serviço.
        Sendo assim, para deixa-las aos cuidados da Cassi Turismo, o passageiro deverá contratar o serviço novamente.
    </p><br>
    <p align="justify">
        Após a remoção da(s) mala(s), fica sobre os cuidados do proprietário(passageiro) da(s) mala(s). A Cassi Turismo não se responsabiliza pela perda, roubo, extravio ou
        danos que as bagagens possam sofrer durante a viagem, por qualquer causa, incluindo sua manipulação durante o(s) traslados.
    </p><br>
    <h6 align="center"> AFIRMO TER CONCECIMENTO DE TODAS AS CLÁUSULAS DESTE TERMO.</h6><br>
    <h6 align="center">  Salvador, _______/____________________/_____________</h6><br>
    <h6 align="center"> __________________________- ___________________<br>
                Assinatura - RG / CPF / Passaporte</h6>


</div>
</body>
</html>


<?php

$html = ob_get_clean();
//------------------------------------------------------------------------------------------------------------
$arquivo = date("d/m/Y").".pdf" ;
define( '_MPDF_TTFONTDATAPATH', sys_get_temp_dir() );
require_once( 'pdf/mpdf.php' );
$mpdf = new mPDF();
$mpdf->SetTitle( "relatório" );
$mpdf->SetAuthor( 'Cassi Turismo' );
$mpdf->WriteHTML( $html, 0 );
$mpdf->Output( $arquivo, 'I' );

?>
