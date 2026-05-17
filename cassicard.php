<?php

$arquivo = $_FILES['arquivo'];
$extensao = substr($arquivo['type'], 6, 3);
$arquivo_nomenovo = $_POST['nome'].".".$extensao;
//move_uploaded_file($arquivo['tmp_name'], 'images/'.$arquivo_nomenovo);

?>

<html>
<head>
    <title>CASSI CARD</title>
</head>
<style>
    @font-face {font-family: "Antartida Rounded Light"; src: url("//db.onlinewebfonts.com/t/b4733944b11576dc42870d7f5103b282.eot"); src: url("//db.onlinewebfonts.com/t/b4733944b11576dc42870d7f5103b282.eot?#iefix")
    format("embedded-opentype"), url("//db.onlinewebfonts.com/t/b4733944b11576dc42870d7f5103b282.woff2") format("woff2"), url("//db.onlinewebfonts.com/t/b4733944b11576dc42870d7f5103b282.woff")
    format("woff"), url("//db.onlinewebfonts.com/t/b4733944b11576dc42870d7f5103b282.ttf") format("truetype"), url("//db.onlinewebfonts.com/t/b4733944b11576dc42870d7f5103b282.svg#Antartida Rounded Light")
    format("svg"); }
    img{
        width: 920px;
        margin-top: 380px;
        margin-left: 20px;
        position: relative;
    }
    p{
        font-family: "Antartida Rounded Light" !important;
        position: absolute;
        margin-left: -210px !important;
        color: white;
        z-index: 11;
    }
    p#nome{margin-top:740px;}
    p#numerocartao{margin-top:700px;}
</style>
<body>
<p id="numerocartao"><?php echo(date("Y 0d", strtotime($_POST['datadeembarque']))." 000".$_POST['id']); ?></p>
<p style="margin-left: 140px; position: absolute;" id="nome"><?php echo($_POST['nome']); ?></p>
<img src="images/cassi-card-cartao.png">
</body>
<script>
    window.print();
</script>
</html>

<?php

$html = ob_get_clean();

//------------------------------------------------------------------------------------------------------------
$arquivo = $_POST['nome']."-cassicard-".".pdf" ;
define( '_MPDF_TTFONTDATAPATH', sys_get_temp_dir() );
require_once( 'adm/relatorio/pdf/mpdf.php' );
$mpdf = new mPDF();
$mpdf->SetTitle( "CASSI CARD" );
$mpdf->SetAuthor( 'Cassi Turismo' );

$mpdf->WriteHTML( $html, 0 );
$mpdf->Output( $arquivo, 'I' );
$mpdf->charset_in = 'windows-1252';
?>
