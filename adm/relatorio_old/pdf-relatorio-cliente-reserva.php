<?php
require_once( '../.././config.php' );
ob_start();
define('MPDP_PATH', 'MPDF54/');
$idcliente  = $_POST['cliente'];
$datainicio = $_POST['periodoinicial'];
$datafim    = $_POST['periodofinal'];
$status     = $_POST['status'];
$total1 = 0;
$total  = 0;
$liquido = 0;
$buscaCliente = $pdo->prepare(
        'select datavencimento, tarifa, credito, fullname, corporatename, cnpj, cep, tel01,email, f.id, f.status
        from `ct_fatura` f left join `ct_cliente` c on f.idcliente = c.id where c.`id` = :id and f.`dateinput` = :inicio and f.`dateoutput` = :fim and f.situacao = 1 ');
$buscaCliente->execute( array(":id" => $idcliente, ":inicio" => $datainicio, ":fim" => $datafim) );
$dadosCliente = $buscaCliente->fetch( PDO::FETCH_ASSOC );

$buscarConferencia = $pdo->prepare(
    'select r.id, dateinput, dateoutput ,numbervoucher, r.idcliente as idcliente ,pax, u.firstname, u.lastname, s.fullname as servico,s.tarifaone, s.mabelazure,
cp.namepayment as pagamento, qtdpax, qtdchild, nameinvoice as statuu, r.valueservice as valorP, r.horaap, r.numberfatura, r.idservico
from `ct_reserva` r  left join `ct_cliente` c on c.`id` = r.`idcliente` left join `ct_usuario` u on u.`id` = r.`idresponsavel`
left join `ct_servico` s on s.`id` = r.`idservico` left join `ct_form_of_ payment` cp on cp.`id` = r.`idpayment` left join `ct_statusinvoice` si on
si.`id` = r.`idstatusinvoice` where r.`dateinput` >= :inicio and r.`dateinput` <= :fim  and r.`idcliente` = :cliente  and r.idstatusinvoice <> :statu ');
$buscarConferencia->execute( array( ":inicio" => $datainicio, ":fim" => $datafim, ":cliente" => $idcliente, ":statu" => 2 ) );
$linhas            = $buscarConferencia->rowCount();
$dadosPrincipais = $buscarConferencia->fetchAll(PDO::FETCH_CLASS);


$minhasFaturasDescricao = $pdo->prepare("select * from `ct_faturadesc` where `id_fatura` = :id ");
$minhasFaturasDescricao->execute( array(":id" => $dadosCliente['id']) );
$dadosFaturaDescricao   = $minhasFaturasDescricao->fetchAll(PDO::FETCH_CLASS);

ob_clean();
if($dadosCliente == false){ var_dump($idcliente, $datainicio, $datafim); die("Não há fatura para o cliente informado dentro do periodo solocitado.");}
?>
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta charset="utf-8">
        <title> Fatura para <?php echo( $dadosCliente['corporatename'] ); ?> </title>
        <link rel="stylesheet" href="materialize.min.css" >
    </head>
    <style>
       table{font-size: 10px;}
        th, td{border: 1px solid #ddd; padding: 8px;}
       td#desc{font-weight: bold;}
    </style>
    <body>
    <div class="container">
        <img style="width: 700px;" id="logo" src="../../images/logo.png"/>
        <div class="row">
            <div class="col-lg-12">
                <p>Fatura - (N <?php echo($dadosCliente['id'].") ".$dadosCliente['fullname'].
                        " ATE ".strtoupper( strftime( "%d de %B", strtotime( $datafim ) ) ) ); ?>
                </p>
                <hr>
                <table class="highlight">
                    <thead>
                        <tr>
                            <th>Para</th>
                            <th>CNPJ</th>
                            <th>Endereco</th>
                            <th>Telefone</th>
                            <th>E-mail</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php echo( $dadosCliente['corporatename']); ?></td>
                            <td><?php echo( $dadosCliente['cnpj']); ?></td>
                            <td><?php echo( $dadosCliente['cep']); ?></td>
                            <td><?php echo( $dadosCliente['tel01']); ?></td>
                            <td><?php echo( $dadosCliente['email']); ?></td>
                        </tr>
                    </tbody>
                </table>
                <table class="table table-bordered">
                    <tr>
                        <th>Data Vencimento</th>
                        <th>Valor Total</th>
                    </tr>
                    <tr>
                        <td><?php echo( date("d-m-Y", strtotime( $dadosCliente['datavencimento'] )) ); ?></td>
                        <td><?php echo(  "R$ ".number_format($dadosCliente['tarifa'],2,",",".") ) ?></td>
                    </tr>
                </table>
               <p>Resumo Financeiro: </p>
                <table class="highlight">
                    <thead>
                        <tr>
                            <th><?php echo( utf8_decode( "Valor" ) ); ?></th>
                            <th><?php echo( utf8_decode( "Descrição" ) ); ?></th>
                            <th>Data de pagamento</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php $totaldescricaocreditos = 0; foreach ( $dadosFaturaDescricao as $item4){ $totaldescricaocreditos += $item4->valor; ?>
                        <tr>
                            <td><?php echo(" R$ ".number_format($item4->valor,2,",","." ))."<br>"; ?></td>
                            <td><?php echo( utf8_decode($item4->descricao) ); ?></td>
                            <td><?php echo( date("d/m/Y", strtotime($item4->datapagamento)) ); ?></td>
                        </tr>

                    <?php }?>
                    </tbody>
                </table>
                <p style="font-weight: bold; font-size: 9px;">
                    <?php echo(utf8_decode("Total crédito do cliente R$ ").number_format($totaldescricaocreditos, 2, ",", ".").
                        utf8_decode(". Total dos créditos da reserva R$ ").number_format($dadosCliente['credito'], 2, ",", ".")); ?>
                </p>
                <table class="highlight">
                    <thead>
                        <tr>
                            <th><?php echo( utf8_decode( "Valor total dos serviços" ) ); ?></th>
                            <th><?php echo( utf8_decode( "Valor total dos créditos" ) ); ?></th>
                            <?php if( $dadosCliente['tarifa'] - $dadosCliente['credito'] < 0 ){ ?>
                                <th><?php echo( utf8_decode( "Crédito" ) ); ?></th>
                            <?php } else { ?>
                                <th>A Pagar</th>
                            <?php }?>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php echo(" R$ ".number_format($dadosCliente['tarifa'],2,",","." ))."<br>"; ?></td>
                            <td><?php echo(" R$ ".number_format($dadosCliente['credito'] + $totaldescricaocreditos,2,",","." ))."<br>"; ?></td>
                            <td><?php echo(" R$ ".number_format($dadosCliente['tarifa'] - ($dadosCliente['credito'] + $totaldescricaocreditos ),
                                            2,",","." ))."<br>"; ?></td>
                        </tr>
                    </tbody>
                </table>

                <table class="highlight">
                    <thead>
                    <tr>
                        <th>Voucher</th>
                        <th>PAX</th>
                        <th>Servico</th>
                        <th>Data</th>
                        <th>Hora</th>
                        <th>Pax/Child</th>
                        <th>Total</th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dadosPrincipais as $item){
                            $buscaNet = $pdo->prepare("select * from `ct_clientservice` where idclient = :cliente and idservice = :se");
                            $buscaNet->execute(array(":cliente" => $item->idcliente, ":se" => $item->idservico));
                            $dados = $buscaNet->fetch(PDO::FETCH_ASSOC);

                            if($dados['valuenet'] == 0 or count($dados) <= 0)
                            {
                                $totalReserva = ( ($item->valorP * $item->qtdpax ) +
                                    ( ($item->valorP / 2) * $item->qtdchild ) ) ;

                            }else{
                                $totalReserva = ( ($dados['valuenet'] * $item->qtdpax ) +
                                    ( ($dados['valuenet'] / 2) * $item->qtdchild )  );
                            }
                            $total = $total + $totalReserva;

                            $descricaoPagamento = $pdo->prepare(
                                'SELECT datacredit as dia, `name` as pagamento, valuecredit as valor FROM `ct_createfaturacredit` cfc 
                                  left join `ct_currentaccount` cc on cc.id = cfc.idaccountcurrent where numbervoucher = :voucher');
                            $descricaoPagamento->execute(array(":voucher" =>  $item->numbervoucher));
                            $registroDescricao = $descricaoPagamento->fetchAll(PDO::FETCH_CLASS);
                            $contadorDescricaoPagamento  = $descricaoPagamento->rowCount();


                            $buscarConferenciaAdd = $pdo->prepare(
                                'select  ra.dateinput, ra.dateoutput, numbervoucher, c.namefantazia as cliente ,pax,
                                             s.fullname as servico, s.tarifaone, s.mabelazure ,qpax, qchild, ra.idservice,
                                             ra.valueservice as valorS,  r.idcliente as idcliente, ra.horaap from `ct_recentlyadd` ra 
                                             left join `ct_reserva` r on ra.`idrecently` = r.id left join `ct_servico`s on ra.idservice = s.id  
                                             left join `ct_cliente` c on c.`id` = r.`idcliente` 
                                             where ra.idrecently = :id ');
                            $buscarConferenciaAdd->execute( array( ":id" => $item->id) );
                            $dadosSecundarios = $buscarConferenciaAdd->fetchAll(PDO::FETCH_CLASS);
                            ?>
                            <?php if($item->numberfatura == 0){
                                $recentlyUpdateNumberFat = $pdo->prepare(
                                    'update `ct_reserva` set numberfatura = :novo where numbervoucher = :voucher ');
                                $recentlyUpdateNumberFat->execute( array(":novo" => $dadosCliente['id'], ":voucher" => $item->numbervoucher) );

                                ?>
                                <tr>
                                    <td><?php echo( $item->numbervoucher); ?></td>
                                    <td><?php echo( utf8_decode( $item->pax )); ?></td>
                                    <td><?php echo( utf8_decode( $item->servico )); ?></td>
                                    <td><?php echo( date("d-m-Y", strtotime( $item->dateinput ) ) ); ?></td>
                                    <td><?php echo( $item->horaap); ?></td>
                                    <td><?php echo( $item->qtdpax."/".$item->qtdchild); ?></td>
                                    <td><?php echo("R$".number_format($totalReserva, 2, ",", ".")); ?></td>
                                </tr>
                            <?php } elseif($item->numberfatura == $dadosCliente['id']){ ?>
                                <tr>
                                    <td><?php echo( $item->numbervoucher); ?></td>
                                    <td><?php echo( utf8_decode( $item->pax )); ?></td>
                                    <td><?php echo( utf8_decode( $item->servico )); ?></td>
                                    <td><?php echo( date("d-m-Y", strtotime( $item->dateinput ) ) ); ?></td>
                                    <td><?php echo( $item->horaap); ?></td>
                                    <td><?php echo( $item->qtdpax."/".$item->qtdchild); ?></td>
                                    <td><?php echo("R$".number_format($totalReserva, 2, ",", ".")); ?></td>
                                </tr>
                            <?php } elseif($item->numberfatura <> $dadosCliente['id']){ ?>
                                <tr>
                                    <td><?php echo( utf8_decode( $item->numbervoucher." Faturado para a fatura de número ".$item->numberfatura)); ?></td>
                                </tr>
                            <?php }?>

                            <?php foreach ($dadosSecundarios as $item2){
                                $buscaNet2 = $pdo->prepare(
                                    "select * from `ct_clientservice` where idclient = :cliente and idservice = :se");
                                $buscaNet2->execute(array(":cliente" => $item2->idcliente, ":se" => $item2->idservice));
                                $dados1 = $buscaNet2->fetch(PDO::FETCH_ASSOC);

                                if($dados1['valuenet'] == 0 or count($dados1) <= 0)
                                {
                                    $totalReservaAdd = ( ($item2->valorS * $item2->qpax ) +
                                        ( ($item2->valorS / 2) * $item2->qchild ) ) ;

                                }else{
                                    $totalReservaAdd = ( ($dados1['valuenet'] * $item2->qpax ) +
                                        ( ($dados1['valuenet'] / 2) * $item2->qchild )  );
                                }
                                $total1 = $total1 + $totalReservaAdd
                                ?>
                                <tr>
                                    <td><?php echo($item2->numbervoucher); ?></td>
                                    <td><?php echo( utf8_decode( $item2->pax )); ?></td>
                                    <td><?php echo( utf8_decode( $item2->servico )); ?></td>
                                    <td><?php echo(date("d-m-Y", strtotime( $item2->dateinput ) ) ); ?></td>
                                    <td><?php echo($item2->horaap); ?></td>
                                    <td><?php echo( $item2->qpax."/".$item2->qchild); ?></td>
                                    <td><?php echo("R$".number_format($totalReservaAdd, 2, ",", ".")); ?></td>
                                </tr>
                            <?php }?>
                            <?php if($item->numberfatura == $dadosCliente['id']){ ?>
                                <tr>
                                    <td colspan="9" id="desc">
                                        <?php echo("Valor Bruto R$ ".number_format($total1+$total, 2, ",", ".")); ?>
                                    </td>
                                </tr>
                                <?php if( $contadorDescricaoPagamento > 0 ){ ?>
                                    <?php foreach ($registroDescricao as $item3){
                                        $liquido = $liquido + $item3->valor
                                        ?>
                                        <tr>
                                            <td id="desc" colspan="3"><?php echo("<strong>Data Pagamento</strong> ".date("d-m-Y", strtotime($item3->dia))); ?></td>
                                            <td id="desc" colspan="3"><?php echo("<strong>Metodo</strong> ".$item3->pagamento); ?></td>
                                            <td id="desc" colspan="3">
                                                <?php echo("<strong>Valor R$ </strong>".number_format($item3->valor, 2,",", ".")); ?>
                                            </td>
                                        </tr>
                                    <?php }?>
                                <?php }?>
                                <tr>
                                    <td colspan="9" id="desc"><?php echo("Valor Liquido R$ ".number_format(($total1+$total) - $liquido, 2, ",", ".")) ?></td>
                                </tr>
                                <hr style="color: black;">
                            <?php }?>


                        <?php $total = 0; $total1 = 0; $liquido = 0; }?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </body>
    </html>
<?php
$html = ob_get_clean();

//------------------------------------------------------------------------------------------------------------
$arquivo = date("d/m/Y")."-".$dadosCliente['corporatename'].".pdf" ;
define( '_MPDF_TTFONTDATAPATH', sys_get_temp_dir() );
require_once( 'pdf/mpdf.php' );
$mpdf = new mPDF();
$mpdf->setFooter('{PAGENO}'."/".$dadosCliente['id']);

$mpdf->SetTitle( "relatório" );
$mpdf->SetAuthor( 'Cassi Turismo' );
$html = mb_convert_encoding($html, 'UTF-8', 'ISO-8859-1');
$mpdf->WriteHTML( $html, 0 );
$mpdf->Output( $arquivo, 'I' );
$mpdf->charset_in = 'windows-1252';
$mpdf->setFooter("{PAGENO}");

?>
