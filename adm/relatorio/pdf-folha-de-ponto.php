<?php
require_once( '../.././config.php' );
header('Content-Type: text/html; charset=iso-8859-1');
$totalAdd  = 0;
if( isset( $_POST['folhadeponto'] )  )
{
   $nomeFuncionario = $_POST['nomefuncionario'];
   $funcao          = $_POST['funcao'];
   $mes             = $_POST['mesreferencia'];
   $empresa         = $_POST['empresa'];

   $buscarEmpresas = $pdo->prepare("SELECT * FROM `ct_cliente` cli left join ct_cidade c on c.id = cli.idcity where cli.id = :id ");
   $buscarEmpresas->execute(array(":id" => $empresa));
   $registro       = $buscarEmpresas->fetch(PDO::FETCH_ASSOC);
}

ob_start();
?>
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta charset="utf-8">
        <title> FOLHA DE PONTO </title>
        <link rel="stylesheet" href="materialize.min.css">
    </head>
    <style>
       table{font-size: 08px;}
        th, td{border: 1px solid #ddd; padding: 8px;}
    </style>
    <body>
    <table class="responsive-table">
        <tr>
            <thead>
                <th>EMPREGADOR / EMPRESA</th>
                <th>CEI - CNPJ</th>
                <th><?php echo( utf8_decode("ENDEREÇO") ); ?></th>
                <th>BAIRRO</th>

            </thead>
        </tr>
        <tr>
            <tbody>
                <td><?php echo( strtoupper( utf8_decode( $registro['corporatename'] ) ) ); ?></td>
                <td><?php echo( strtoupper( utf8_decode( $registro['cnpj'] ) ) ); ?></td>
                <td><?php echo( strtoupper( utf8_decode( $registro['address'] ) ) ); ?></td>
                <td>--</td>

            </tbody>
        </tr>
        <tr>
            <thead>
                <th>EMPREGADO(A)</th>
                <th><?php echo( utf8_decode("FUNÇÃO") ); ?></th>
                <th><?php echo( utf8_decode("MÊS") ); ?></th>
                <th>CIDADE</th>
            </thead>
        </tr>
        <tr>
            <tbody>
                <td><?php echo( utf8_decode($nomeFuncionario) ); ?></td>
                <td><?php echo( utf8_decode($funcao) ); ?></td>
                <td><?php echo( strtoupper( utf8_decode(strftime("%B", strtotime($mes))) ) ); ?></td>
                <td><?php echo( strtoupper( utf8_decode( $registro['name'] ) ) ); ?></td>
            </tbody>
        </tr>
    </table>
    <table class="responsive-table">
        <thead>
            <tr>
                <th>DIAS</th>
                <th>ENTRADA</th>
                <th><?php echo( utf8_decode("ENTRADA ALMOÇO") ); ?> </th>
                <th><?php echo( utf8_decode("SAÍDA  ALMOÇO") ); ?></th>
                <th><?php echo( utf8_decode("SAÍDA") ); ?></th>
                <th>ASSINATURA DO EMPREGADO(A) </th>
            </tr>

        </thead>
        <tbody>
        <?php for ($contador = 1; $contador <= 31; $contador++){ $mes = $contador."-".date("m-Y", strtotime($mes)) ?>
            <tr>
                <td><?php echo( strtoupper( utf8_decode(strftime("%a ->  ", strtotime($mes))).$contador ) ); ?></td>
                <td>__________:_________</td>
                <td>__________:_________</td>
                <td>__________:_________</td>
                <td>__________:_________</td>
                <td>____________________________________________________________</td>
            </tr>
        <?php }?>
        </tbody>
    </table>
       
    </body>
    </html>

<?php
$html = ob_get_clean();

//------------------------------------------------------------------------------------------------------------
$arquivo = $nomeFuncionario.".pdf" ;
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
