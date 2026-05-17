<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8">
    <title>Termo Chapada</title>
    <link rel="stylesheet" href="materialize.min.css">
</head>
<body>
<style>
    p{font-size: 7px;}
</style>
<div class="container">
    <P align="center">CONTRATO DE TRANSPORTE DE PASSAGEIROS</P>
    <p style="color: black;">
        A CASSI TURISMO é uma empresa que realiza seus serviços com frota própria, contudo o destino Chapada Diamantina é
        executado com empresas contratadas que detêm concessão pública para operar no trecho Salvador/Chapara.
        Com o objetivo de qualificar este serviço com o já conhecido padrão de qualidade Cassi Turismo a finalidade é
        oferecer um atendimento diferenciado através de profissionais qualificados para o trade. Com uma equipe de
        funcionários é composto por poliglotas com ampla experiência no turismo e aptos a passar todas as informações
        relativas ao destino. Como diferencial a Cassi Turismo oferece um atendimento VIP, igualando a outros destinos
        onde opera, tais como Morro de São Paulo, Boipeba, Itacaré, Praia do Forte dentre outros, sempre com uma unidade
        Cassi Turismo para lhe proporcionar o melhor atendimento. Esta estrutura é pensada exclusivamente para você,
        que deseja ter um tratamento a altura de suas expectativas, por isso cobramos valores diferenciados.
    </p>
    <P align="center">RESPONSABILIDADES DURANTE O PRCURSO DA VIAGEM</P>
    <p style="color: black;">
        A REAL EXPRESSO/RÁPIDO FEDERAL responsabiliza-se por acidentes na execução dos serviços, bem como responder civil
        e/ou criminalmente, por quaisquer danos causados, diretamente ou indiretamente, à CASSI TURISMO ou a terceiros,
        decorrentes de sua culpa ou dolo.
    </p>
    <p style="color: black;">
        A responsabilidade sob o bem estar dos passageiros durante toda a viagem é única e exclusivamente da
        REAL EXPRESSO/RÁPIDO FEDERAL, cabendo a ela responder civil e/ou criminalmente por qualquer dano ou constrangimento
        a ele causado. Ex.: tratamento descortês, maus tratos, injuria ou ofensa, todo e qualquer tipo de agressão física ou
        moral, tratamento discriminatório ou preconceituoso em maior ou menor grau;
    </p>

    <p style="color: black;">
        Cabe a REAL EXPRESSO/RÁPIDO FEDERAL os cuidados pelo manuseio e transporte de bagagem durante todos o período de
        viagem, assim como estar atento a eventual possibilidade do transporte de itens ilegais, tais como armas,
        entorpecentes e outros;
    </p>

    <p style="color: black;">
        É da REAL EXPRESSO/RÁPIDO FEDERAL toda a responsabilidade por qualquer ação judicial movida por parte do passageiro
        em detrimento de insatisfação pelo serviço prestado inerente a viagem e/ou tratamento dado a ele ou a seus pertences
        , podendo a CASSI TURISMO agir solidariamente em função da REAL EXPRESSO/RÁPIDO FEDERAL.
    </p>
    <p style="color: black;">
        Em caso de atraso no cumprimento de horários de viagem, a REAL EXPRESSO/RÁPIDO FEDERAL se responsabilizará por
        qualquer reclamação seja esta judicial ou extrajudicial, cabendo a esta a resolução do problema sem comprometer a
        CASSI TURISMO.
    </p>
    <P align="center">AFIRMO TER CONCECIMENTO DE TODAS AS CLÁUSULAS DESTE CONTRATO</P>
    <p style="color: black;">
        Fica expressamente estabelecida uma multa equivalente a dois meses do contrato caso a CONTRATANTE venha recindir o
        mesmo antes do término de sua vigência.
    </p>
    <p align="center"> AFIRMO TER CONCECIMENTO DE TODAS AS CLÁUSULAS DESTE TERMO.</p>
    <p align="center">  Salvador, _______/____________________/_____________</p>
    <p align="center"> _____________________________________________<br>
        Nome</p>
    <p align="center"> __________________________- ___________________<br>
        Assinatura - RG / CPF / Passaporte</p>


</div>
</body>
</html>


<?php

$html = ob_get_clean();
//------------------------------------------------------------------------------------------------------------
$arquivo = "termo-chapada.pdf" ;
define( '_MPDF_TTFONTDATAPATH', sys_get_temp_dir() );
require_once( 'pdf/mpdf.php' );
$mpdf = new mPDF();
$mpdf->SetTitle( "relatório" );
$mpdf->SetAuthor( 'Cassi Turismo' );
$mpdf->WriteHTML( $html, 0 );
$mpdf->Output( $arquivo, 'I' );

?>
