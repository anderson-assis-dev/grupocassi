<?php
require_once( '../.././config.php' );
ob_start();
define('MPDP_PATH', 'MPDF54/');
$datainicio = $_POST['periodoinicial'];
$datafim    = $_POST['periodofinal'];

$todosCliente = $pdo->prepare('select * from `ct_cliente` ');
$todosCliente->execute();
$registro = $todosCliente->fetchAll( PDO::FETCH_CLASS );


$tarifadoTotalCliente  = 0;
$creditadoTotalCliente = 0;

foreach ($registro as $item)
{
    $buscarReservasAFaturar = $pdo->prepare(
        'select r.numbervoucher, c.id as cliente, s.tarifaone, r.qtdpax, r.qtdchild, r.valueservice from `ct_reserva` r left join `ct_cliente` c on r.idcliente = c.id
              left join `ct_servico` s on r.idservico =  s.id
              where r.`dateinput` >= :inicio and r.`dateinput` <= :fim  and r.`idstatusinvoice` = :statusinvoice and r.idcliente = :cliente ');
    $buscarReservasAFaturar->execute( array(":inicio" => $datainicio, ":fim" => $datafim, ":statusinvoice" =>  1, ":cliente" => $item->id ) );
    $contarReservasCliente = $buscarReservasAFaturar->rowCount();

    if($contarReservasCliente > 0)
    {
        while(  $dados = $buscarReservasAFaturar->fetch(PDO::FETCH_ASSOC) )
        {
            $buscarVoucherAdd = $pdo->prepare(
                'SELECT ra.valueservice, ra.qpax, ra.qchild, ra.idservice, s.tarifaone FROM `ct_recentlyadd` ra 
                  left join `ct_reserva` r on r.id = ra.idrecently left join `ct_servico` s on r.idservico =  s.id  where r.numbervoucher = :voucher ');
            $buscarVoucherAdd->execute( array(":voucher" => $dados['numbervoucher']));
            $registro = $buscarVoucherAdd->fetch( PDO::FETCH_ASSOC);

            if( $dados['cliente'] == 17 or $dados['cliente'] == 20  )
            {
                $totalReserva = ( ($dados['tarifaone'] * $dados['qtdpax'] ) + ( ($dados['tarifaone'] / 2) * $dados['qtdchild'] )  );
            }
            else{
                $totalReserva = ( ($dados['valueservice'] * $dados['qtdpax'] ) + ( ($dados['valueservice'] / 2) * $dados['qtdchild'] ) ) ;
            }

            if( $dados['cliente'] == 17 or $dados['cliente'] == 20  )
            {
                if( $dados['tarifaone'] == 0 )
                {
                    $totalReservaAdd = ( ($registro['valueservice'] * $registro['qpax'] ) + (($registro['valueservice']  / 2) * $registro['qchild'] ) );
                }
                else{
                    $totalReservaAdd = ( ($registro['tarifaone'] * $registro['qpax'] ) + (($registro['tarifaone']  / 2) * $registro['qchild'] ) );
                }

            }else{

                $totalReservaAdd = ( ($registro['valueservice'] * $registro['qpax'] ) + (($registro['valueservice']  / 2) * $registro['qchild'] ) );
            }

            $buscaTarifaCredito = $pdo->prepare('SELECT SUM(valuecredit) as credito, tarifa FROM `ct_createfaturacredit` where numbervoucher = :voucher');
            $buscaTarifaCredito->execute( array(":voucher" => $dados['numbervoucher'] ) );
            $informacoes = $buscaTarifaCredito->fetch( PDO::FETCH_ASSOC );
            $salvarDados = $pdo->prepare(
                'insert into `ct_fatura` (`id`, `idcliente`, `numbervoucher` ,`tarifa`, `credito`, `dateinput`, `dateoutput`) 
                               values (DEFAULT, :cliente, :numbervoucher ,:tarifa, :credito, :inicio,:fim)');
            $salvarDados->execute(
                array(
                    ":cliente"       => $dados['cliente'],
                    ":numbervoucher" => $dados['numbervoucher'],
                    ":tarifa"        => ( $totalReserva + $totalReservaAdd ),
                    ":credito"       => $informacoes['tarifa'],
                    ":inicio"        => $datainicio,
                    ":fim"           => $datafim
                )
            );

        }
    }
}

$checkList = $pdo->prepare(
        'select * from `ct_fatura` f left join ct_cliente c on f.idcliente = c.id  where `dateinput` = :inicio and `dateoutput` = :fim group by f.idcliente ');
$checkList->execute( array(":inicio" => $datainicio, ":fim" => $datafim) );
ob_clean();
?>
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta charset="utf-8">
        <title><?php echo( utf8_decode( "Relatório de Fatura" ) ); ?></title>
        <link rel="stylesheet" href="materialize.min.css">
    </head>
    <body>
    <div class="container">
        <img style="width: 700px; margin-left: -50px; " id="logo" src="../../images/logo.png"/>
        <hr>
        <h3><?php echo( utf8_decode( "Relatório de Fatura de ".
                strftime("%d de %B", strtotime( $datainicio )) )); ?> </h3><br>
        <table class="highlight">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Faturado</th>
                    <th>Credito</th>
                    <th>Saldo</th>
                </tr>
            </thead>
            <tbody>
            <?php while( $dadosFatura = $checkList->fetch(PDO::FETCH_ASSOC) ){ ?>
                <tr>
                    <td><?php echo( utf8_decode( $dadosFatura['fullname']) ); ?></td>
                    <td><?php echo(" R$ ".number_format($dadosFatura['tarifa'],2,",","." )); ?></td>
                    <td><?php echo(" R$ ".number_format($dadosFatura['credito'],2,",","." )); ?></td>
                    <td><?php echo(" R$ ".number_format($dadosFatura['tarifa'] - $dadosFatura['credito'],2,",","." )); ?></td>
                </tr>
            <?php }?>
            </tbody>
        </table>
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
$html = mb_convert_encoding($html, 'UTF-8', 'ISO-8859-1');
$mpdf->WriteHTML( $html, 0 );
$mpdf->Output( $arquivo, 'I' );
$mpdf->charset_in = 'windows-1252';
?>