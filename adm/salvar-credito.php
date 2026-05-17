<?php
/**
 * Created by PhpStorm.
 * User: Anderson
 * Date: 09/08/2018
 * Time: 07:18
 */
require_once ('.././config.php');
$voucher         = $_POST['voucher'];
$desc            = $_POST['desc'];
$datacredito     = $_POST['datacredito'];
$valordocredito  = $_POST['valordocredito'];
$ccfp            = $_POST['ccfp'];
$planocontas     = $_POST['planocontas'];
$totalReserva    = 0;
$totalReservaAdd = 0;
$buscarVoucher = $pdo->prepare(
    'SELECT numbervoucher, pax, s.fullname, corporatename, qtdchild, qtdpax, pricechild, priceadult, firstname, lastname, nameinvoice, namepayment, tarifa
              FROM `ct_reserva` LEFT JOIN ct_servico s ON s.id = ct_reserva.idservico left JOIN ct_cliente c on c.id = ct_reserva.idcliente left join `ct_usuario` u 
              on u.id = ct_reserva.idresponsavel left join `ct_statusinvoice` si on si.id = ct_reserva.idstatusinvoice left join `ct_form_of_ payment` fp 
              on fp.id = ct_reserva.idpayment left join ct_tarifa t on t.id = c.idtarifa where ct_reserva.numbervoucher = :voucher');
$buscarVoucher->execute( array(":voucher" => $voucher) );
$dadosVoucher = $buscarVoucher->fetch( PDO::FETCH_ASSOC );
$totalReserva = ( ($dadosVoucher['priceadult'] * $dadosVoucher['qtdpax'] ) + ($dadosVoucher['pricechild'] * $dadosVoucher['qtdchild'] ) ) -
    ( ($dadosVoucher['tarifa'] * $dadosVoucher['qtdpax'] ) + ( ($dadosVoucher['pricechild'] / 2) * $dadosVoucher['qtdchild'] ) );

$buscarVoucherAdd = $pdo->prepare(
    'SELECT ra.dateinput as ap, s.fullname,s.screenplay, s.priceadult, s.pricechild, ss.schedule, qpax, qchild, qfree, r.numbervoucher, r.pax, r.documento, firstname, 
              lastname, namepayment,corporatename, nameinvoice, tarifa FROM `ct_recentlyadd` ra left join `ct_reserva` r on r.id = ra.idrecently left join `ct_usuario` u 
              on u.id = r.idresponsavel left join ct_servico s on s.id = ra.idservice left join ct_service_schedule ss on ss.idshedule = ra.idschedule 
              left join `ct_statusinvoice` si on si.id = r.idstatusinvoice left join `ct_form_of_ payment` fp on fp.id = r.idpayment left JOIN ct_cliente c 
              on c.id = r.idcliente left join ct_tarifa t on t.id = c.idtarifa where r.numbervoucher = :voucher ');
$buscarVoucherAdd->execute( array(":voucher" => $voucher));

while( $registro = $buscarVoucherAdd->fetch( PDO::FETCH_ASSOC ) )
{
    $totalReservaAdd = ( ($registro['priceadult'] * $registro['qpax'] ) + ($registro['pricechild'] * $registro['qchild'] ) ) -
        ( ($registro['tarifa'] * $registro['qpax'] ) + ( ($registro['pricechild'] / 2) * $registro['qchild'] ) );
}
$geral = $totalReservaAdd + $totalReserva;

$buscaFatura = $pdo->prepare('SELECT * FROM `ct_createfatura` where `numbervoucher` = :voucher');
$buscaFatura->execute( array(":voucher" => $voucher) );
$contador = $buscaFatura->rowCount();

if( $contador > 0 )
{
    $novoCredito = $pdo->prepare(
        'insert into `ct_createfaturacredit` (`id`, `numbervoucher`, `tarifa`, `desccredit`, `datacredit`, `valuecredit`, `valuedes` ,`idaccountcurrent`, `idplancount`) values 
                   (DEFAULT, :numbervoucher, :tarifa, :desccredit, :datacredit, :valuecredit, :despesa ,:idaccountcurrent, :idplancount)');
    $novoCredito->execute( array(
        ":numbervoucher"    => $voucher,
        ":tarifa"           => $geral,
        ":desccredit"       => $desc,
        ":datacredit"       => $datacredito,
        ":valuecredit"      => $valordocredito,
        ":despesa"          => 0,
        ":idaccountcurrent" => $ccfp,
        ":idplancount"      => $planocontas
    ) );

    $auditoria = $pdo->prepare(
        'insert into `ct_audit` (`id`, `idresponsible`, `voucher`, `description`, `date`) values (DEFAULT, :resp, :voucher, :des, :dataa) ');
    $auditoria->execute( array(
        ":resp"    => $_SESSION['id'],
        ":voucher" => $voucher,
        ":des"     => "Crédito de Fatura cadastrado",
        ":dataa"   => date("Y-m-d H:i:s" )) );


}else{
    echo( json_encode("Não encontramos o voucher informado. Provalvmente você ainda não cadastrou a fatura do voucher informado.
     Caso o possível erro persista entre em contato com Departamento de T.I") );
}