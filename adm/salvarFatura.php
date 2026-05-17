<?php
require_once ('.././config.php');
$datainicio      = $_POST['datainicio'];
$datafinal       = $_POST['datafim'];
$idcliente       = $_POST['idcliente'];
$statusEscolhido = $_POST['statusescolhido'];
$contaC          = $_POST['ccfp'];
$dataVencimento  = $_POST['datavencimento'];
$dataPagamento   = $_POST['datapagamento'];
$numeracao       = $_POST['numeracao'];
$observacao      = $_POST['observacao'];

$atualizarStatus = $pdo->prepare(
    'update `ct_reserva` set `idstatusinvoice` = :sinvoice where ct_reserva.dateinput >= :inicio 
                       and ct_reserva.dateinput <= :fim and ct_reserva.dateoutput >= :inicioo and ct_reserva.dateoutput <= :fimo ');
$atualizarStatus->execute( array(
    ":sinvoice" => $statusEscolhido,
    ":inicio"   => $datainicio,
    ":fim"      => $datafinal,
    ":inicioo"  => $datainicio,
    ":fimo"     => $datafinal

) );

$buscarReservas = $pdo->prepare('select * from `ct_reserva` where ct_reserva.dateinput >= :inicio 
                       and ct_reserva.dateinput <= :fim and ct_reserva.dateoutput >= :inicioo and ct_reserva.dateoutput <= :fimo ');
$buscarReservas->execute(array(
    ":inicio"   => $datainicio,
    ":fim"      => $datafinal,
    ":inicioo"  => $datainicio,
    ":fimo"     => $datafinal));
$registro = $buscarReservas->fetchAll( PDO::FETCH_CLASS );

foreach ($registro as $registros)
{
    $verificarDados = $pdo->prepare('select * from `ct_createfatura` where `numbervoucher` = :voucher');
    $verificarDados->execute(array(":voucher" => $registros->numbervoucher ));
    $contador = $verificarDados->rowCount();
    if( $contador > 0 )
    {
        $updateDados = $pdo->prepare(
            'update `ct_createfatura` set `datematurity` = :dvencimento,`datepayment` = :dpagamento, `numberadd` = :numero, `obervacao` = :obs,
                      `idcurrentaccount` = :contac where `numbervoucher` = :voucher ');
        $updateDados->execute(array(
            ":dvencimento" => $dataVencimento,
            ":dpagamento"  => $dataPagamento,
            ":numero"      => $numeracao,
            ":obs"         => $observacao,
            ":contac"      => $contaC,
            ":voucher"     => $registros->numbervoucher
        ));

        $auditoria = $pdo->prepare(
            'insert into `ct_audit` (`id`, `idresponsible`, `voucher`, `description`, `date`) values (DEFAULT, :resp, :voucher, :des, :dataa) ');
        $auditoria->execute( array(
            ":resp"    => $_SESSION['id'],
            ":voucher" => $registros->numbervoucher,
            ":des"     => "Fatura Cadastrada",
            ":dataa"   => date("Y-m-d H:i:s" )) );
    }
    else{
        $salvarDados = $pdo->prepare(
            'insert into `ct_createfatura` (`id`, `numbervoucher`, `datematurity`, `datepayment`, `numberadd`, `obervacao`, `idcurrentaccount`, `idcliente`)
                          values (DEFAULT, :voucher, :vencimento, :pagamento, :numeracao, :observacao, :conta, :idcliente) ');
        $salvarDados->execute( array(
            ":voucher"    => $registros->numbervoucher,
            ":vencimento" => $dataVencimento,
            ":pagamento"  => $dataPagamento,
            ":numeracao"  => $numeracao,
            ":observacao" => $observacao,
            ":conta"      => $contaC,
            ":idcliente"  => $registros->idcliente
        ) );

        $auditoria = $pdo->prepare(
            'insert into `ct_audit` (`id`, `idresponsible`, `voucher`, `description`, `date`) values (DEFAULT, :resp, :voucher, :des, :dataa) ');
        $auditoria->execute( array(
            ":resp"    => $_SESSION['id'],
            ":voucher" => $registros->numbervoucher,
            ":des"     => "Fatura Cadastrada",
            ":dataa"   => date("Y-m-d H:i:s" )) );
    }

}