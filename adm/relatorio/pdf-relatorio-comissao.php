<?php
require_once( '../.././config.php' );
ob_start();
define('MPDP_PATH', 'MPDF54/');

if( isset( $_POST['comissaoguia'] ) )
{
    $nomeGuia      = $_POST['nomeguia'];
    $datainicio    = $_POST['data'];
    $valorUnitario = $_POST['valorguia'];
    $contadorPagamentoGuia = 0;

    $buscaNomeGuia = $pdo->prepare('select * from `ct_guia` where id = :id ');
    $buscaNomeGuia->execute( array(":id" => $nomeGuia) );
    $dadosGuia = $buscaNomeGuia->fetch( PDO::FETCH_ASSOC );

    $buscarReservasDoGuia = $pdo->prepare(
            'select pax ,numbervoucher, s.fullname, c.namefantazia from `ct_reserva` r left join ct_servico s on r.idservico = s.id 
                       left join ct_cliente c on c.id = r.idcliente  where r.`dateinput` = :datein and r.`idguia` = :nomeguia and r.`idstatus` = 1 ');
    $buscarReservasDoGuia->execute( array( ":datein" => $datainicio, ":nomeguia" => $nomeGuia ) );
    $contadorReservasGuia = $buscarReservasDoGuia->rowCount();
    $registro = $buscarReservasDoGuia->fetchAll(PDO::FETCH_CLASS);

    $buscarReservasDoGuiaAdd = $pdo->prepare(
        'select * from `ct_recentlyadd` ra left join `ct_reserva` r on r.id = ra.idrecently where ra.`dateinput` = :datein and r.`idguia` = :idguia and r.`idstatus` = 1 ');
    $buscarReservasDoGuiaAdd->execute( array( ":datein" => $datainicio, ":idguia" => $nomeGuia ) );
    $contadorReservasGuiaAdd = $buscarReservasDoGuiaAdd->rowCount();

    $totalDiariaGuia = $valorUnitario * ($contadorReservasGuia  + $contadorReservasGuiaAdd);
    $somarGuia = $contadorReservasGuia  + $contadorReservasGuiaAdd;

    foreach ( $registro as $item )
    {
        $financeiroReserva = $pdo->prepare(
          'SELECT count(*) as total, dataguia, valueguia FROM `ct_createfaturacredit` where numbervoucher = :voucher ');
        $financeiroReserva->execute(array(":voucher" => $item->numbervoucher));
        $linhas = $financeiroReserva->fetch(PDO::FETCH_ASSOC);
        $contadorPagamentoGuia = $contadorPagamentoGuia + $linhas['total'];

        if($linhas['total'] == 0 and $contadorReservasGuia > 0)
        {
            $novoCredito = $pdo->prepare(
                'INSERT INTO `ct_createfaturacredit` (`id`, `numbervoucher`, `tarifa`, `desccredit`, `datacredit`, `valuecredit`, `valueguia`, `dataguia`,
                          `valueagente`, `dataagente`, `idaccountcurrent`, `idplancount`) values
            (DEFAULT, :numbervoucher, :tarifa, :desccredit, :datacredit, :valuecredit, :vagt, :dguia, :vagente, :dagente ,:idaccountcurrent, :idplancount)');
            $novoCredito->execute( array(
                ":numbervoucher"    => $item->numbervoucher,
                ":tarifa"           => 0,
                ":desccredit"       => 0,
                ":datacredit"       => 0,
                ":valuecredit"      => 0,
                ":vagt"             => $valorUnitario,
                ":dguia"            => date("Y-m-d" ),
                ":vagente"          => 0,
                ":dagente"          => '0000-00-00',
                ":idaccountcurrent" => 0,
                ":idplancount"      => 0
            ) );
        }

        if($somarGuia <> $contadorPagamentoGuia)
        {
            $salvarDespesaReserva = $pdo->prepare(
                'update `ct_createfaturacredit` set `valueguia` = :valor, `dataguia` = :datapagamento where `numbervoucher` = :voucher');
            $salvarDespesaReserva->execute( array( ":valor" => $valorUnitario, ":datapagamento" => date("Y-m-d" ) ,":voucher" => $item->numbervoucher ) );

            $auditoria = $pdo->prepare(
                'insert into `ct_audit` (`id`, `idresponsible`, `voucher`, `description`, `date`) values (DEFAULT, :resp, :voucher, :des, :dataa) ');
            $auditoria->execute( array(
                ":resp"    => $_SESSION['id'],
                ":voucher" => $item->numbervoucher,
                ":des"     => "A comissão de R$ ".$valorUnitario." foi paga ao guia ". $dadosGuia['fullname'],
                ":dataa"   => date("Y-m-d H:i:s" )) );
        }

    }
}


ob_clean();
?>
<?php if(  $somarGuia > $contadorPagamentoGuia ){ ?>
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta charset="utf-8">
        <title><?php echo( utf8_decode( "Recibo de Comissão" ) ); ?></title>
        <link rel="stylesheet" href="materialize.min.css">
    </head>
    <body>
    <div class="container">
        <img style="width: 700px;" id="logo" src="../.././images/logo.png"/>
        <hr>
        <h4 align="center"><?php echo( utf8_decode( "Recibo de Comissão " ).date("y/m/").$somarGuia); ?></h4>
        <?php if( !empty( $nomeGuia ) ){ ?>
            <table class="highlight">
                <thead>
                    <tr>
                        <th>PAX</th>
                        <th>Cliente</th>
                        <th><?php echo( utf8_decode("Serviço")); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($registro as $registros){ ?>
                    <tr>
                        <td><?php echo($registros->pax); ?></td>
                        <td><?php echo($registros->namefantazia); ?></td>
                        <td><?php echo($registros->fullname); ?></td>

                    </tr>
                    <?php }?>
                </tbody>
            </table>
            <hr>
            <table class="highlight">
                <thead>
                <tr>
                    <th><?php echo( utf8_decode("Descrição")); ?></th>
                    <th>Valor</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td><?php echo( utf8_decode("Pagamento de Comissão")); ?></td>
                    <td><?php echo("R$".number_format($totalDiariaGuia,2,",","." )); ?></td>
                </tr>
                </tbody>
            </table>
            <hr>
            <table class="highlight">
                <tbody>
                    <tr>
                        <td>_________________________________________________________________________</td>
                        <td>_________________________________________________________________________</td>
                    </tr>
                </tbody>
                <tfoot>
                <tr>
                    <th><?php echo( utf8_decode($dadosGuia['fullname'])); ?></th>
                    <th>Autorizado</th>
                </tr>
                </tfoot>
            </table>
        <?php }?>
    </div>
    </body>
    </html>
<?php }elseif($somarGuia == $contadorPagamentoGuia and $contadorPagamentoGuia > 0 ) { ?>
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta charset="utf-8">
        <title><?php echo( utf8_decode( "Recibo de Comissão" ) ); ?></title>
        <link rel="stylesheet" href="materialize.min.css">
    </head>
    <body>
    <div class="container">
        <img style="width: 700px;" id="logo" src="../.././images/logo.png"/>
        <hr>
        <h4 align="center"><?php echo( utf8_decode( "Comissão paga" )); ?></h4>
        <?php if( !empty( $nomeGuia ) ){ ?>
            <p align="center">
                <?php echo( utf8_decode( "A comissão do guia ".$dadosGuia['fullname']." com o valor de R$"
                    .number_format($linhas['valueguia'],2,",","." )." foi paga em "
                    . date("d-m-Y", strtotime($linhas['dataguia']))) );
                ?>
            </p>
        <?php }?>
    </div>
    </body>
    </html>
<?php } elseif($contadorPagamentoGuia == 0){?>
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta charset="utf-8">
        <title><?php echo( utf8_decode( "Recibo de Comissão" ) ); ?></title>
        <link rel="stylesheet" href="materialize.min.css">
    </head>
    <body>
    <div class="container">
        <img style="width: 700px;" id="logo" src="../.././images/logo.png"/>
        <hr>
        <h4 align="center"><?php echo( utf8_decode( "Ops! :)" )); ?></h4>
        <?php if( !empty( $nomeGuia ) ){ ?>
            <p align="center">
                <?php echo( utf8_decode( "Não conseguimos encontras as informações forncedidas. Tente Novamente !") );
                var_dump($registro);
                ?>
            </p>
        <?php }?>
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
