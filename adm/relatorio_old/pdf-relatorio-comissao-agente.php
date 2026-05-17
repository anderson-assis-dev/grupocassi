<?php
require_once( '../.././config.php' );
ob_start();
define('MPDP_PATH', 'MPDF54/');
if( isset( $_POST['comissaoagente'] ) )
{
    $nomeAgente              = $_POST['nomeagente'];
    $voucher                 = $_POST['voucher'];
    $valorUnitario           = str_replace(",",".",str_replace(",",".", $_POST['valoragente']));
    $nomeServicoPago         = $_POST['comissaoservico'];
    $contadorPagamentoAgente = 0;

    $buscaNomeAgente = $pdo->prepare('select * from `ct_agentes` where fullname = :fullname');
    $buscaNomeAgente->execute( array(":fullname" => $nomeAgente) );
    $dadosAgente = $buscaNomeAgente->fetch( PDO::FETCH_ASSOC );
    $contadorAgt = $buscaNomeAgente->rowCount();

    $buscaDadosReserva = $pdo->prepare(
            'select r.id, fullname, r.idservico, r.numbervoucher, r.pax, r.documento, r.dateinput as embarque, r.idcliente as cliente, r.numberfatura from `ct_reserva` r 
                                                  left join ct_agentes a on r.idagente = a.id where `numbervoucher` = :voucher');
    $buscaDadosReserva->execute(array(":voucher" => $voucher));
    $dadosReserva      = $buscaDadosReserva->fetch(PDO::FETCH_ASSOC);

    $adicionais = $pdo->prepare(
            'select * from `ct_recentlyadd` where idrecently = :id and idservice <> 19 and idservice <> 30 and idservice <> 47 and idservice <> 48
                      and idservice <> 17 and idservice <> 18 and idservice <> 31 and idservice <> 53 and idservice <> 155 ');
    $adicionais->execute( array(":id" => $dadosReserva['id']) );
    $contadorAdicionais = 100;
    $dadosAdicionais = $adicionais->fetch(PDO::FETCH_ASSOC);

    $buscarPagamento = $pdo->prepare(
            'select  * from `ct_createfaturacredit` where numbervoucher = :voucher and `dataagente` > :dataa');
    $buscarPagamento->execute( array( ":voucher" => $voucher, ":dataa" => '0000-00-00' ) );
    $dadosPagamento    = $buscarPagamento->fetch(PDO::FETCH_ASSOC);
    $dadosPagamento2   = $buscarPagamento->fetchAll(PDO::FETCH_CLASS);
    $contadorPagamento = 100;
    if($dadosReserva['numberfatura'] > 0)
    {
        $minhasFaturas = $pdo->prepare("select * from `ct_fatura` where `id` = :id ");
        $minhasFaturas->execute( array(":id" => $dadosReserva['numberfatura']) );
        $dadosFatura   = $minhasFaturas->fetch(PDO::FETCH_ASSOC);
        $update_credito_fatura = $pdo->prepare("update `ct_fatura` set `tarifa` = :tarifa where `id` = :id ");
        $update_credito_fatura->execute( array(":tarifa" => $dadosFatura['tarifa']+$valorUnitario, ":id" => $dadosReserva['numberfatura']) );
    }


    if( $contadorPagamento == 0 and $dadosReserva['idservico'] <> 19 and $dadosReserva['idservico'] <> 30
        and $dadosReserva['idservico'] <> 47 and $dadosReserva['idservico'] <> 48 and $dadosReserva['idservico'] <> 17 and $dadosReserva['idservico'] <> 18
        and $dadosReserva['idservico'] <> 31 and $dadosReserva['idservico'] <> 53 and $dadosReserva['idservico'] <> 155 )
    {
        if($contadorAgt == 0)
        {
            $novoAgente = $pdo->prepare('insert into `ct_agentes` (`id`, `fullname`) values (DEFAULT, :nome) ');
            $novoAgente->execute(array(":nome" => strtoupper($nomeAgente)));

            $buscaagt = $pdo->prepare('select * from `ct_agentes` where fullname = :fullname');
            $buscaagt->execute(array(":fullname" => $nomeAgente));
            $dadosagt = $buscaagt->fetch(PDO::FETCH_ASSOC);

            $updateReservaAgt = $pdo->prepare('update `ct_reserva` set `idagente` = :novoid where `numbervoucher` = :voucher ');
            $updateReservaAgt->execute( array(":novoid" => $dadosagt['id'], ":voucher" => $voucher) );
            $sql = "insert into `ct_createfaturacredit` set `numbervoucher` = '".$voucher."', `tarifa` = 0, `desccredit` = now(),`datacredit` = '0000-00-00', `valuecredit` = 0, `valueguia`=0, `valueagente`= '".(float)$valorUnitario."', `dataagente` = now(),  `idaccountcurrent` = 1, `idplancount`= 1";
            $despesa = $pdo->prepare($sql);
            $despesa->execute();


            $novaTransacao = $pdo->prepare(
                "insert into `ct_caixa` (`id`, `datevencimento`, `datepagamento`, `datecompetencia`, `nome` ,`descricao`, `idcliente`, `idtipo`, `idconta`, `idplano`,
                      `idstatus`, `valor`, `idusr`, `dataabertura`) 
                      values (DEFAULT, :vencimento, :pagamento, :competencia, :nome ,:descricao, :cliente, :tipo, :conta, :plano, :statuus, :valor,:idusr, :abertura) ");
            $novaTransacao->execute(
                array(
                    ":vencimento"  => date("Y-m-d"),
                    ":pagamento"   => date("Y-m-d"),
                    ":competencia" => date("Y-m-d"),
                    ":nome"        => $nomeAgente,
                    ":descricao"   => "Pagamento de comissao para o voucher: ".$voucher,
                    ":cliente"     => 8,
                    ":tipo"        => 2,
                    ":conta"       => 14,
                    ":plano"       => 30,
                    ":statuus"     => 6,
                    ":valor"       => $valorUnitario,
                    "idusr"        => $_SESSION['id'],
                    ":abertura"    => date("Y-m-d")
                )
            );

            if( $_SESSION['id']  == 28  or $_SESSION['id']  == 34 or  $_SESSION['id']  == 46 or $_SESSION['id']  == 1)
            {
                $salvarDados = $pdo->prepare(
                    'insert into `ct_createfatura` (`id`, `numbervoucher`, `datematurity`, `datepayment`, `numberadd`, `obervacao`, `idcurrentaccount`, `idcliente`)
                          values (DEFAULT, :voucher, :vencimento, :pagamento, :numeracao, :observacao, :conta, :idcliente) ');
                $salvarDados->execute( array(
                    ":voucher"    => $voucher,
                    ":vencimento" => date("Y-m-d"),
                    ":pagamento"  => date("Y-m-d"),
                    ":numeracao"  => $_SESSION['nome']." -> ".date("d-m-Y")." COMISSAO PAGA AO ".$nomeAgente." R$ ".$valorUnitario,
                    ":observacao" => ".",
                    ":conta"      => 14,
                    ":idcliente"  => $dadosReserva['cliente']
                ) );

                $atualizarStatus = $pdo->prepare(
                    'update `ct_reserva` set `idstatusinvoice` = :sinvoice where numbervoucher = :voucher ');
                $atualizarStatus->execute( array(
                    ":sinvoice" => 5,
                    ":voucher"  => $voucher

                ) );

                $auditoria = $pdo->prepare(
                    'insert into `ct_audit` (`id`, `idresponsible`, `voucher`, `description`, `date`) values (DEFAULT, :resp, :voucher, :des, :dataa) ');
                $auditoria->execute( array(
                    ":resp"    => $_SESSION['id'],
                    ":voucher" => $voucher,
                    ":des"     => "Fatura Cadastrada ".$_SESSION['nome']." -> ".date("d-m-Y")." COMISSAO PAGA AO ".$nomeAgente." R$ ".$valorUnitario,
                    ":dataa"   => date("Y-m-d H:i:s" )) );
            }

            $auditoria = $pdo->prepare(
                'insert into `ct_audit` (`id`, `idresponsible`, `voucher`, `description`, `date`) values (DEFAULT, :resp, :voucher, :des, :dataa)');
            $auditoria->execute( array(
                ":resp"    => $_SESSION['id'],
                ":voucher" => $voucher,
                ":des"     => "A comissão de R$ ".$valorUnitario." foi paga ao agente ". $dadosagt['fullname']." para o serviço ".$nomeServicoPago,
                ":dataa"   => date("Y-m-d H:i:s" )) );

        }
        else{

            $buscaagt = $pdo->prepare('select * from `ct_agentes` where fullname = :fullname');
            $buscaagt->execute(array(":fullname" => $nomeAgente));
            $dadosagt = $buscaagt->fetch(PDO::FETCH_ASSOC);

            $updateReservaAgt = $pdo->prepare('update `ct_reserva` set `idagente` = :novoid where `numbervoucher` = :voucher ');
            $updateReservaAgt->execute( array(":novoid" => $dadosagt['id'], ":voucher" => $voucher) );

            $resp = $_SESSION['id'];
            $sql = "insert into `ct_createfaturacredit` set `numbervoucher` = '".$voucher."', `tarifa` = 0, `desccredit` = now(),`datacredit` = '0000-00-00', `valuecredit` = 0, `valueguia`=0, `valueagente`= '".(float)$valorUnitario."', `dataagente` = now(),  `idaccountcurrent` = 1, `idplancount`= 1";
            $despesa = $pdo->prepare($sql);
            $despesa->execute();

            $novaTransacao = $pdo->prepare(
                "insert into `ct_caixa` (`id`, `datevencimento`, `datepagamento`, `datecompetencia`, `nome` ,`descricao`, `idcliente`, `idtipo`, `idconta`, `idplano`,
                      `idstatus`, `valor`,`idusr`, `dataabertura`) 
                      values (DEFAULT, :vencimento, :pagamento, :competencia, :nome ,:descricao, :cliente, :tipo, :conta, :plano, :statuus, :valor,:idusr, :abertura) ");
            $novaTransacao->execute(
                array(
                    ":vencimento"  => date("Y-m-d"),
                    ":pagamento"   => date("Y-m-d"),
                    ":competencia" => date("Y-m-d"),
                    ":nome"        => $nomeAgente,
                    ":descricao"   => "Pagamento de comissao para o voucher: ".$voucher,
                    ":cliente"     => 8,
                    ":tipo"        => 2,
                    ":conta"       => 14,
                    ":plano"       => 30,
                    ":statuus"     => 6,
                    ":valor"       => $valorUnitario,
                    "idusr"        => $_SESSION['id'],
                    ":abertura"    => date("Y-m-d")
                )
            );

            if( $_SESSION['id']  == 28  or $_SESSION['id']  == 34 or  $_SESSION['id']  == 46 or $_SESSION['id']  == 1)
            {
                $salvarDados = $pdo->prepare(
                    'insert into `ct_createfatura` (`id`, `numbervoucher`, `datematurity`, `datepayment`, `numberadd`, `obervacao`, `idcurrentaccount`, `idcliente`)
                          values (DEFAULT, :voucher, :vencimento, :pagamento, :numeracao, :observacao, :conta, :idcliente) ');
                $salvarDados->execute( array(
                    ":voucher"    => $voucher,
                    ":vencimento" => date("Y-m-d"),
                    ":pagamento"  => date("Y-m-d"),
                    ":numeracao"  => $_SESSION['nome']." -> ".date("d-m-Y")." COMISSAO PAGA AO ".$nomeAgente." R$ ".$valorUnitario,
                    ":observacao" => ".",
                    ":conta"      => 14,
                    ":idcliente"  => $dadosReserva['cliente']
                ) );

                $atualizarStatus = $pdo->prepare(
                    'update `ct_reserva` set `idstatusinvoice` = :sinvoice where numbervoucher = :voucher ');
                $atualizarStatus->execute( array(
                    ":sinvoice" => 5,
                    ":voucher"  => $voucher

                ) );

                $auditoria = $pdo->prepare(
                    'insert into `ct_audit` (`id`, `idresponsible`, `voucher`, `description`, `date`) values (DEFAULT, :resp, :voucher, :des, :dataa) ');
                $auditoria->execute( array(
                    ":resp"    => $_SESSION['id'],
                    ":voucher" => $voucher,
                    ":des"     => "Fatura Cadastrada ".$_SESSION['nome']." -> ".date("d-m-Y")." COMISSAO PAGA AO ".$nomeAgente." R$ ".$valorUnitario,
                    ":dataa"   => date("Y-m-d H:i:s" )) );
            }

            $auditoria = $pdo->prepare(
                'insert into `ct_audit` (`id`, `idresponsible`, `voucher`, `description`, `date`) values (DEFAULT, :resp, :voucher, :des, :dataa) ');
            $auditoria->execute( array(
                ":resp"    => $_SESSION['id'],
                ":voucher" => $voucher,
                ":des"     => "A comissão de R$ ".$valorUnitario." foi paga ao agente ". $dadosagt['fullname']." para o serviço ".$nomeServicoPago,
                ":dataa"   => date("Y-m-d H:i:s" )) );
        }
    }
 else{
     if ($contadorAdicionais > 0 and $contadorPagamento <= $contadorAdicionais)
     {
         $despesa = $pdo->prepare(
             'insert into `ct_createfaturacredit` (`id`, `numbervoucher`, `tarifa`, `desccredit`, `datacredit`, `valuecredit`, `valueguia`, 
                     `valueagente`, `dataagente`, `idaccountcurrent`, `idplancount`) values 
                   (DEFAULT, :numbervoucher, :tarifa, :desccredit, :datacredit, :valuecredit, :vg ,:va, :vad, :cconte, :plano)');
         $despesa->execute( array(
             ":numbervoucher"    => $voucher,
             ":tarifa"           => 0,
             ":desccredit"       => 0,
             ":datacredit"       => 0,
             ":valuecredit"      => 0,
             ":vg"               => 0,
          
             ":va"               => $valorUnitario,
             ":vad"              => date("Y-m-d"),
             ":cconte"           => 1,
             ":plano"            => 1
         ) );

         $buscaagt = $pdo->prepare('select * from `ct_agentes` where fullname = :fullname');
         $buscaagt->execute(array(":fullname" => $nomeAgente));
         $dadosagt = $buscaagt->fetch(PDO::FETCH_ASSOC);

         $auditoria = $pdo->prepare(
             'insert into `ct_audit` (`id`, `idresponsible`, `voucher`, `description`, `date`) values (DEFAULT, :resp, :voucher, :des, :dataa) ');
         $auditoria->execute( array(
             ":resp"    => $_SESSION['id'],
             ":voucher" => $voucher,
             ":des"     => "A comissão de R$ ".$valorUnitario." foi paga ao agente ". $dadosagt['fullname']." para o serviço ".$nomeServicoPago,
             ":dataa"   => date("Y-m-d H:i:s" )) );
         if( $_SESSION['id']  == 28  or $_SESSION['id']  == 34 or  $_SESSION['id']  == 46 or $_SESSION['id']  == 1)
         {
             $salvarDados = $pdo->prepare(
                 'insert into `ct_createfatura` (`id`, `numbervoucher`, `datematurity`, `datepayment`, `numberadd`, `obervacao`, `idcurrentaccount`, `idcliente`)
                          values (DEFAULT, :voucher, :vencimento, :pagamento, :numeracao, :observacao, :conta, :idcliente) ');
             $salvarDados->execute( array(
                 ":voucher"    => $voucher,
                 ":vencimento" => date("Y-m-d"),
                 ":pagamento"  => date("Y-m-d"),
                 ":numeracao"  => $_SESSION['nome']." -> ".date("d-m-Y")." COMISSAO PAGA AO ".$nomeAgente." R$ ".$valorUnitario,
                 ":observacao" => ".",
                 ":conta"      => 14,
                 ":idcliente"  => $dadosReserva['cliente']
             ) );

             $atualizarStatus = $pdo->prepare(
                 'update `ct_reserva` set `idstatusinvoice` = :sinvoice where numbervoucher = :voucher ');
             $atualizarStatus->execute( array(
                 ":sinvoice" => 5,
                 ":voucher"  => $voucher

             ) );

             $auditoria = $pdo->prepare(
                 'insert into `ct_audit` (`id`, `idresponsible`, `voucher`, `description`, `date`) values (DEFAULT, :resp, :voucher, :des, :dataa) ');
             $auditoria->execute( array(
                 ":resp"    => $_SESSION['id'],
                 ":voucher" => $voucher,
                 ":des"     => "Fatura Cadastrada ".$_SESSION['nome']." -> ".date("d-m-Y")." COMISSAO PAGA AO ".$nomeAgente." R$ ".$valorUnitario,
                 ":dataa"   => date("Y-m-d H:i:s" )) );
         }

     }else{
         $auditoria = $pdo->prepare(
             'insert into `ct_audit` (`id`, `idresponsible`, `voucher`, `description`, `date`) values (DEFAULT, :resp, :voucher, :des, :dataa) ');
         $auditoria->execute( array(
             ":resp"    => $_SESSION['id'],
             ":voucher" => $voucher,
             ":des"     => "Tentou pagar a comissão mais de uma vez para ".$nomeAgente." com o valor de R$ ".$valorUnitario,
             ":dataa"   => date("Y-m-d H:i:s" )) );
     }

    }


}
ob_clean();
?>
<?php if($contadorPagamento  == 0  ){ ?>
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta charset="utf-8">
        <title><?php echo( utf8_decode( "Recibo de Comissão" ) ); ?></title>
        <link rel="stylesheet" href="materialize.min.css">
    </head>
    <body>
    <div class="container">
        <img  id="logo" src="../.././images/logo.png"/>
        <hr>
        <h4 align="center"><?php echo( utf8_decode( "Solicitação de pagamento ".$dadosReserva['numbervoucher'] )); ?></h4>
        <small style="font-size: 8px" ><?php echo( "Impresso em: ".date("d-m-Y") ); ?></small>
        <hr>
        <table class="highlight">
            <thead>
            <tr>
                <th>PAX</th>
                <th>Documento</th>
                
                <th>Valor</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td><?php echo( utf8_decode($dadosReserva['pax']) ); ?></td>
                <td><?php echo( utf8_decode($dadosReserva['documento']) ); ?></td>
            
                <td><?php echo("R$ ".number_format($valorUnitario, 2, ",", ".") ); ?></td>
            </tr>
            </tbody>
        </table>
        <hr>
        <table class="highlight">
            <thead>
            <tr>
                <th><?php echo( utf8_decode('Descrição') ) ?></th>
                <th>Valor Total</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td><?php echo( utf8_decode("Pagamento de Comissão de ".$nomeAgente." para o serviço ".$nomeServicoPago) ); ?></td>
                <td><?php echo("R$ ".number_format($valorUnitario, 2, ",", ".") ); ?></td>
            </tr>
            </tbody>
        </table>
        <hr>
        <table class="highlight">
            <tbody>
            <tr>
                <td>_______________________________________________________________________________________________</td>
                <td>_______________________________________________________________________________________________</td>
            </tr>
            </tbody>
            <tfoot>
            <tr>
                <th style="text-align: center"><?php echo($_SESSION['nome']); ?></th>
                <th style="text-align: center">Autorizado</th>
            </tr>
            </tfoot>
        </table>
    </div>
    </body>
    </html>
<?php } else { ?>
    <?php if($contadorAdicionais > 0 and $contadorPagamento <= $contadorAdicionais){ ?>
        <html xmlns="http://www.w3.org/1999/xhtml">
        <head>
            <meta charset="utf-8">
            <title><?php echo( utf8_decode( "Recibo de Comissão" ) ); ?></title>
            <link rel="stylesheet" href="materialize.min.css">
        </head>
        <body>
        <div class="container">
            <img  id="logo" src="../.././images/logo.png"/>
            <hr>
            <h4 align="center"><?php echo( utf8_decode( "Solicitação de pagamento ".$dadosReserva['numbervoucher'] )); ?></h4>
            <small style="font-size: 8px" ><?php echo( "Impresso em: ".date("d-m-Y") ); ?></small>
            <hr>
            <table class="highlight">
                <thead>
                <tr>
                    <th>PAX</th>
                    <th>Documento</th>
                 
                    <th>Valor</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td><?php echo( utf8_decode($dadosReserva['pax']) ); ?></td>
                    <td><?php echo( utf8_decode($dadosReserva['documento']) ); ?></td>
                    
                    <td><?php echo("R$ ".number_format($valorUnitario, 2, ",", ".") ); ?></td>
                </tr>
                </tbody>
            </table>
            <hr>
            <table class="highlight">
                <thead>
                <tr>
                    <th><?php echo( utf8_decode('Descrição') ) ?></th>
                    <th>Valor Total</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td><?php echo( utf8_decode("Pagamento de Comissão de ".$nomeAgente." para o serviço ".$nomeServicoPago) ); ?></td>
                    <td><?php echo("R$ ".number_format($valorUnitario, 2, ",", ".") ); ?></td>
                </tr>
                </tbody>
            </table>
            <hr>
            <table class="highlight">
                <tbody>
                <tr>
                    <td>_______________________________________________________________________________________________</td>
                    <td>_______________________________________________________________________________________________</td>
                </tr>
                </tbody>
                <tfoot>
                <tr>
                    <th style="text-align: center"><?php echo($_SESSION['nome']); ?></th>
                    <th style="text-align: center">Autorizado</th>
                </tr>
                </tfoot>
            </table>
        </div>
        </body>
        </html>
    <?php } else {?>
        <html xmlns="http://www.w3.org/1999/xhtml">
        <head>
            <meta charset="utf-8">
            <title><?php echo( utf8_decode( "Recibo de Comissão" ) ); ?></title>
            <link rel="stylesheet" href="materialize.min.css">
        </head>
        <body>
        <div class="container">
            <img  id="logo" src="../.././images/logo.png"/>
            <hr>
            <h4 align="center">
                <?php echo( utf8_decode( "O pagamento da comissão já foi realizado para o voucher: ".
                    $dadosReserva['numbervoucher']." em ".date( "d-m-Y", strtotime($dadosPagamento['dataagente']) )." ".$dadosReserva['fullname'] ));
                ?>
            </h4>
        </div>
        </body>
        </html>
    <?php }?>


<?php } ?>

<?php

$html = ob_get_clean();
$arquivo = "Comissao.pdf" ;
define( '_MPDF_TTFONTDATAPATH', sys_get_temp_dir() );
require_once( 'pdf/mpdf.php' );
$mpdf = new mPDF();
$mpdf->SetTitle( "relatório" );
$mpdf->SetAuthor( 'Cassi Turismo' );
$html = mb_convert_encoding($html, 'UTF-8', 'ISO-8859-1');
$mpdf->WriteHTML( $html, 0 );
$mpdf->Output( $arquivo, 'I' );


?>


