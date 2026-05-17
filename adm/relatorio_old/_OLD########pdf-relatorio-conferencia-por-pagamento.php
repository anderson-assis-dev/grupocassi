<?php
ini_set('max_execution_time', 800);
require_once('../.././config.php');
ob_start();

$inicio          = $_POST['inicio'];
$fim             = $_POST['fim'];
$responsavel     = $_POST['responsavel'];
$agencia         = $_POST['cliente'];
$total_cartaocredito = 0;
$total_cartaodebito  = 0;
$total_dinheiro      = 0;
$total_transferencia = 0;
$total_paypal        = 0;
$total_cortesia      = 0;
$total_panda         = 0;
if (isset($_POST['tipo'])) {
    $tipo        = $_POST['tipo'];
} else {
    $tipo        = 1;
}

if ($tipo == 1) {
    if ($responsavel == 0) {

        if($agencia) {

            $find_file_salesmen = $pdo->prepare('select cfc.numbervoucher from `ct_createfaturacredit` cfc
                left join `ct_reserva` r on r.numbervoucher = cfc.numbervoucher
                where r.idcliente = :cliente and datacredit >= :inicio and datacredit <= :fim group by cfc.numbervoucher');
            $find_file_salesmen->execute(
                array(
                    ":inicio" => $inicio,
                    ":fim"    => $fim,
                    ":cliente" => $agencia
                )
            );

        } else {
            $find_file_salesmen = $pdo->prepare('select cfc.numbervoucher from `ct_createfaturacredit` cfc where datacredit >= :inicio and datacredit <= :fim group by cfc.numbervoucher');
            $find_file_salesmen->execute(
                array(
                    ":inicio" => $inicio,
                    ":fim"    => $fim
                )
            );
        }


    } else {
        $find_file_salesmen = $pdo->prepare('select cfc.numbervoucher from `ct_createfaturacredit` cfc  where cfc.datacredit >= :inicio and cfc.datacredit <= :fim and cfc.idusr = :usuario group by cfc.numbervoucher');
        $find_file_salesmen->execute(
            array(
                ":inicio"   => $inicio,
                ":fim"      => $fim,
                ":usuario"  => $responsavel
            )
        );
        $caixa         = $pdo->prepare(
            " select c.id,c.datevencimento, c.nome ,c.datecompetencia, c.datepagamento, c.descricao, forne.fullname as fornecedor, tc.`name` as tipo, cc.`name`
              as conta,p.`name` as plano, s.`nameinvoice` as situacao, c.valor, em.fullname as empresa from  `ct_caixa` c left join ct_fornecedor forne on forne.id = c.idcliente
              left join ct_tipocaixa tc on tc.id = c.idtipo left join ct_currentaccount cc on cc.id = c.idconta left join ct_planaccounts p on p.id = c.idplano
              left join ct_statusinvoice s on s.id = c.idstatus left join ct_empresa em on em.id = c.idempresa where c.`datepagamento` >= :inicio and c.`datepagamento` <= :fim and c.idusr = :idusuario and c.`idtipo` = :tipo  "
        );
        $caixa->execute(array(":inicio" => $inicio, ":fim" => $fim, ":idusuario" => $responsavel, ":tipo" => 2));
    }

    $data_find_file_salesmen = $find_file_salesmen->fetchAll(PDO::FETCH_CLASS);
    // $find_file_salesmen->debugDumpParams();
    // die;

    $verify = $find_file_salesmen->rowCount();

    if ($responsavel > 0) {
        $registroCaixa = $caixa->fetchAll(PDO::FETCH_CLASS);
        // print_r('$registroCaixa => ');
        // $caixa->debugDumpParams();
        // die;
        $verify_caixa = $caixa->rowCount();
    }


    $controler = 1;
    $total_adulto  = 0;
    $total_crianca = 0;
    $total_free    = 0;
    $total_general = 0;
    $total_recebido = 0;

    $total_one = 0;
    $total_two = 0;
    $total_out = 0;
} else {
    $find_file_salesmen = $pdo->prepare('select distinct(idusr), numbervoucher from `ct_createfaturacredit` cfc where datacredit >= :inicio and datacredit <= :fim  ORDER by idusr');
    $find_file_salesmen->execute(
        array(
            ":inicio" => $inicio,
            ":fim"    => $fim
        )
    );
    $data_find_file_salesmen = $find_file_salesmen->fetchAll(PDO::FETCH_CLASS);
    // print_r('$data_find_file_salesmen');
    // $find_file_salesmen->debugDumpParams();

    $data = array();
    $ant = 0;
    foreach ($data_find_file_salesmen as $item) {
        if ($item->idusr <> $ant and !empty($item->numbervoucher)) {
            $data[] = (object)array(
                "idusr" => $item->idusr,
                "numbervoucher" => $item->numbervoucher
            );
            $ant = $item->idusr;
        }
    }
    $data_find_file_salesmen = $data;
    $tot_dia = 0;

    $find_despesas = $pdo->prepare('select distinct(idusr) from `ct_caixa` c where c.`datepagamento` >= :inicio and c.`datepagamento` <= :fim and c.`idtipo` = 2  ORDER by c.idusr');
    $find_despesas->execute(
        array(
            ":inicio" => $inicio,
            ":fim"    => $fim
        )
    );
    $data_find_despesas = $find_despesas->fetchAll(PDO::FETCH_CLASS);
    // print_r('$data_find_despesas');
    // $find_despesas->debugDumpParams();

    $ant = 0;
    $data = array();
    foreach ($data_find_despesas as $item) {
        if ($item->idusr <> $ant) {
            $data[] = (object)array(
                "idusr" => $item->idusr,
            );
            $ant = $item->idusr;
        }
    }
    $data_find_despesas = $data;
    $total_despesas = 0;
    if ($agencia > 0) {
        $dadosReserva = $pdo->prepare(
            "select * from `ct_createfaturacredit` cf left join `ct_reserva` r on cf.numbervoucher = r.numbervoucher left join ct_cliente c on c.id = r.idcliente left join `ct_usuario` u on u.id = r.idresponsavel where cf.`datacredit` >= :inn and r.`idstatus` <> 2
                    and cf.`datacredit` <= :outt and r.idcliente = :cliente  "
        );
        $dadosReserva->execute(array(":inn" => $inicio, ":cliente" => $agencia, ":outt" => $fim));
        $reservas = $dadosReserva->fetchAll(PDO::FETCH_CLASS);
        // print_r('$reservas');
        // $dadosReserva->debugDumpParams();
    }
}

ob_clean();
?>
<?php if ($tipo == 1) { ?>
    <?php if ($verify > 0) { ?>
        <html xmlns="http://www.w3.org/1999/xhtml">

        <head>
            <meta charset="utf-8">
            <title>Relatório de conferência do vendedor</title>
            <link rel="stylesheet" href="materialize.min.css">
        </head>
        <style>
            th,
            td {
                border: 1px solid #ddd;
                padding: 8px;
                font-size: 10px;
            }

            td#desc {
                font-weight: bold;
            }
        </style>

        <body>
            <div class="container">
                <img style="width: 700px;" id="logo" src="../../images/logo.png" />

                <hr>
                <p><?php echo (utf8_decode(
                        "Relatório de Conferência de: " .
                            date("d/m/Y ", strtotime($inicio)) . " ate " . date("d/m/Y ", strtotime($fim))
                    )); ?> </p><br>
                <p style="font-size: 9px; margin-top: -20px;">Impresso em: <?php echo (date("d/m/Y - H:i:s")); ?></p>
                <table class="highlight">
                    <thead>
                        <tr>
                            <th>Embarque</th>
                            <th>Voucher</th>
                            <th>Agência</th>
                            <th>Pax</th>
                            <th>P | C | F</th>
                            <th>Vendedor(a)</th>
                            <th>Serviço</th>
                            <th>Valor</th>
                            <th>Valor Total</th>
                            <th>Recebido Por</th>
                            <th>Pago em</th>
                            <th>Valor recebido</th>
                            <th>Situação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data_find_file_salesmen as $key => $item) {

                            if ($agencia > 0) {
                                $find_reservation_primary = $pdo->prepare(
                                    'select r.id, dateinput , c.namefantazia as cliente, pax, s.fullname as servico,qtdpax, qtdchild, nameinvoice as situacao, r.valueservice, r.idstatusinvoice, qtdfree, u.firstname, u.lastname, data_integracao
                                    from `ct_reserva` r  left join `ct_cliente` c on c.`id` = r.`idcliente` left join `ct_servico` s on s.`id` = r.`idservico` left join `ct_usuario` u on u.`id` = r.`idresponsavel`
                                    left join `ct_statusinvoice` si on si.`id` = r.`idstatusinvoice` where r.numbervoucher = :voucher and r.`idcliente` = :cliente'
                                );
                                $find_reservation_primary->execute(array(":voucher" => $item->numbervoucher, ":cliente" => $agencia));
                                if(!$find_reservation_primary->rowCount()){
                                    continuer;
                                }


                                if ($responsavel > 0) {
                                    echo ("SELECT datacredit as dia, `name` as pagamento, sum(valuecredit) as valor, u.firstname, u.lastname  FROM `ct_createfaturacredit` cfc
                                        left join `ct_currentaccount` cc on cc.id = cfc.idaccountcurrent left join `ct_usuario` u on cfc.idusr = u.id left join ct_reserva cr on cfc.numbervoucher = cr.numbervoucher
                                        where cr.numbervoucher = '$item->numbervoucher' and cfc.idusr = $responsavel and cr.`idcliente` = $agencia and datacredit >= '$inicio' and datacredit <= '$fim' ");
                                    $find_data_payments_of_client = $pdo->prepare(
                                        "SELECT datacredit as dia, `name` as pagamento, sum(valuecredit) as valor, u.firstname, u.lastname  FROM `ct_createfaturacredit` cfc
                                          left join `ct_currentaccount` cc on cc.id = cfc.idaccountcurrent left join `ct_usuario` u on cfc.idusr = u.id left join ct_reserva cr on cfc.numbervoucher = cr.numbervoucher
                                          where cr.numbervoucher = '$item->numbervoucher' and cfc.idusr = $responsavel and cr.`idcliente` = $agencia and datacredit >= '$inicio' and datacredit <= '$fim' GROUP by idusr"
                                    );
                                    $find_data_payments_of_client->execute();
                                    continue;
                                    $transferencia = $pdo->prepare("select sum(cfc.valuecredit) as totaltransferencia from `ct_createfaturacredit` cfc  where datacredit >= '$inicio' and datacredit <= '$fim' and cfc.idusr = $responsavel and idaccountcurrent = 36 ");
                                    $transferencia->execute();
                                    $data_tranferenci = $transferencia->fetch(PDO::FETCH_ASSOC);
                                    // print_r('$data_tranferenci');
                                    // $transferencia->debugDumpParams();
                                    $total_transferencia += $data_tranferenci['totaltransferencia'];

                                    $cartao_credito = $pdo->prepare('select sum(cfc.valuecredit) as totalcartaocredito from `ct_createfaturacredit` cfc  where datacredit >= :inicio and datacredit <= :fim and cfc.idusr = :id and idaccountcurrent = :forma ');
                                    $cartao_credito->execute(
                                        array(
                                            ":inicio" => $inicio,
                                            ":fim"    => $fim,
                                            ":id"     => $responsavel,
                                            ":forma"  => 24,
                                        )
                                    );
                                    $data_cartao_credito = $cartao_credito->fetch(PDO::FETCH_ASSOC);
                                    // print_r('$data_cartao_credito');
                                    // $cartao_credito->debugDumpParams();

                                    $total_cartaocredito += $data_cartao_credito['totalcartaocredito'];

                                    $cartao_debito = $pdo->prepare('select sum(cfc.valuecredit) as totalcartaodebito from `ct_createfaturacredit` cfc  where datacredit >= :inicio and datacredit <= :fim and cfc.idusr = :id and idaccountcurrent = :forma ');
                                    $cartao_debito->execute(
                                        array(
                                            ":inicio" => $inicio,
                                            ":fim"    => $fim,
                                            ":id"     => $responsavel,
                                            ":forma"  => 25,
                                        )
                                    );
                                    $data_cartao_debito = $cartao_debito->fetch(PDO::FETCH_ASSOC);
                                    // print_r('$data_cartao_debito');
                                    // $cartao_debito->debugDumpParams();

                                    $total_cartaodebito += $data_cartao_debito['totalcartaodebito'];

                                    $paypal = $pdo->prepare('select sum(cfc.valuecredit) as totalpaypal from `ct_createfaturacredit` cfc  where datacredit >= :inicio and datacredit <= :fim and cfc.idusr = :id  and idaccountcurrent = :forma ');
                                    $paypal->execute(
                                        array(
                                            ":inicio" => $inicio,
                                            ":fim"    => $fim,
                                            ":id"     => $responsavel,
                                            ":forma"  => 22,
                                        )
                                    );
                                    $data_paypal = $paypal->fetch(PDO::FETCH_ASSOC);
                                    // print_r('$data_paypal');
                                    // $paypal->debugDumpParams();

                                    $total_paypal += $data_paypal['totalpaypal'];

                                    $dinheiro = $pdo->prepare('select sum(cfc.valuecredit) as totaldinheiro from `ct_createfaturacredit` cfc  where datacredit >= :inicio and datacredit <= :fim and cfc.idusr = :id   and idaccountcurrent = :forma ');
                                    $dinheiro->execute(
                                        array(
                                            ":inicio" => $inicio,
                                            ":fim"    => $fim,
                                            ":id"     => $responsavel,
                                            ":forma"  => 18,
                                        )
                                    );

                                    $data_dinheiro = $dinheiro->fetch(PDO::FETCH_ASSOC);
                                    // print_r('$data_dinheiro');
                                    // $dinheiro->debugDumpParams();

                                    $total_dinheiro += $data_dinheiro['totaldinheiro'];

                                    $panda = $pdo->prepare('select sum(cfc.valuecredit) as totalpanda from `ct_createfaturacredit` cfc  where datacredit >= :inicio and datacredit <= :fim and cfc.idusr = :id  and idaccountcurrent = :forma ');
                                    $panda->execute(
                                        array(
                                            ":inicio" => $inicio,
                                            ":fim"    => $fim,
                                            ":id"     => $responsavel,
                                            ":forma"  => 23,
                                        )
                                    );
                                    $data_panda = $panda->fetch(PDO::FETCH_ASSOC);
                                    // print_r('$data_panda');
                                    // $panda->debugDumpParams();

                                    $total_panda += $data_panda['totalpanda'];

                                    $cortesia = $pdo->prepare('select sum(cfc.valuecredit) as totalcortesia from `ct_createfaturacredit` cfc  where datacredit >= :inicio and datacredit <= :fim and cfc.idusr = :id  and idaccountcurrent = :forma ');
                                    $cortesia->execute(
                                        array(
                                            ":inicio" => $inicio,
                                            ":fim"    => $fim,
                                            ":id"     => $responsavel,
                                            ":forma"  => 39,
                                        )
                                    );
                                    $data_cortesia = $cortesia->fetch(PDO::FETCH_ASSOC);
                                    // print_r('$data_cortesia');
                                    // $cortesia->debugDumpParams();

                                    $total_cortesia += $data_cortesia['totalcortesia'];
                                } else {
                                    $find_data_payments_of_client = $pdo->prepare(
                                        'SELECT sum(valuecredit) as valor FROM `ct_createfaturacredit` cfc
                                          left join `ct_currentaccount` cc on cc.id = cfc.idaccountcurrent left join `ct_usuario` u on cfc.idusr = u.id left join ct_reserva cr on cfc.numbervoucher = cr.numbervoucher
                                          where cr.numbervoucher = :voucher  and cr.`idcliente` = :cliente and datacredit >= :inicio and datacredit <= :fim'
                                    );
                                    $find_data_payments_of_client->execute(array(":voucher" =>  $item->numbervoucher, ":cliente" => $agencia, ":inicio" => $inicio, ":fim"    => $fim,));
                                    $find_data_payments_of_client2 = $pdo->prepare(
                                        'SELECT datacredit as dia, `name` as pagamento, u.firstname, u.lastname  FROM `ct_createfaturacredit` cfc
                                          left join `ct_currentaccount` cc on cc.id = cfc.idaccountcurrent left join `ct_usuario` u on cfc.idusr = u.id left join ct_reserva cr on cfc.numbervoucher = cr.numbervoucher
                                          where cr.numbervoucher = :voucher  and cr.`idcliente` = :cliente and datacredit >= :inicio and datacredit <= :fim'
                                    );
                                    $find_data_payments_of_client2->execute(array(":voucher" =>  $item->numbervoucher, ":cliente" => $agencia, ":inicio" => $inicio, ":fim"    => $fim,));

                                    // $transferencia = $pdo->prepare('select sum(cfc.valuecredit) as totaltransferencia from `ct_createfaturacredit` cfc  where datacredit >= :inicio and datacredit <= :fim and idaccountcurrent = :forma and numbervoucher = :voucher ');
                                    // $transferencia->execute(
                                    //     array(
                                    //         ":inicio" => $inicio,
                                    //         ":fim"    => $fim,
                                    //         ":forma"  => 36,
                                    //         ":voucher" => $item->numbervoucher
                                    //     )
                                    // );
                                    // $data_tranferenci = $transferencia->fetch(PDO::FETCH_ASSOC);
                                    // print_r('$data_tranferenci ===> ');
                                    // $transferencia->debugDumpParams();

                                    // $total_transferencia += $data_tranferenci['totaltransferencia'];



                                    // $cartao_credito = $pdo->prepare('select sum(cfc.valuecredit) as totalcartaocredito from `ct_createfaturacredit` cfc  where datacredit >= :inicio and datacredit <= :fim and idaccountcurrent = :forma and numbervoucher = :voucher ');
                                    // $cartao_credito->execute(
                                    //     array(
                                    //         ":inicio" => $inicio,
                                    //         ":fim"    => $fim,
                                    //         ":forma"  => 24,
                                    //         ":voucher" => $item->numbervoucher
                                    //     )
                                    // );
                                    // $data_cartao_credito = $cartao_credito->fetch(PDO::FETCH_ASSOC);
                                    // print_r('$data_cartao_credito');
                                    // $cartao_credito->debugDumpParams();
                                    // die;
                                    // $total_cartaocredito += $data_cartao_credito['totalcartaocredito'];



                                    // $cartao_debito = $pdo->prepare('select sum(cfc.valuecredit) as totalcartaodebito from `ct_createfaturacredit` cfc  where datacredit >= :inicio and datacredit <= :fim and idaccountcurrent = :forma and numbervoucher = :voucher ');
                                    // $cartao_debito->execute(
                                    //     array(
                                    //         ":inicio" => $inicio,
                                    //         ":fim"    => $fim,
                                    //         ":forma"  => 25,
                                    //         ":voucher" => $item->numbervoucher
                                    //     )
                                    // );
                                    // $data_cartao_debito = $cartao_debito->fetch(PDO::FETCH_ASSOC);
                                    // print_r('$data_cartao_debito');
                                    // $cartao_debito->debugDumpParams();
                                    // $total_cartaodebito += $data_cartao_debito['totalcartaodebito'];



                                    // $paypal = $pdo->prepare('select sum(cfc.valuecredit) as totalpaypal from `ct_createfaturacredit` cfc  where datacredit >= :inicio and datacredit <= :fim and idaccountcurrent = :forma and numbervoucher = :voucher ');
                                    // $paypal->execute(
                                    //     array(
                                    //         ":inicio" => $inicio,
                                    //         ":fim"    => $fim,
                                    //         ":forma"  => 22,
                                    //         ":voucher" => $item->numbervoucher
                                    //     )
                                    // );
                                    // $data_paypal = $paypal->fetch(PDO::FETCH_ASSOC);
                                    // print_r('$data_paypal');
                                    // $paypal->debugDumpParams();

                                    // $total_paypal += $data_paypal['totalpaypal'];



                                    // $dinheiro = $pdo->prepare('select sum(cfc.valuecredit) as totaldinheiro from `ct_createfaturacredit` cfc  where datacredit >= :inicio and datacredit <= :fim and idaccountcurrent = :forma and numbervoucher = :voucher ');
                                    // $dinheiro->execute(
                                    //     array(
                                    //         ":inicio" => $inicio,
                                    //         ":fim"    => $fim,
                                    //         ":forma"   => 18,
                                    //         ":voucher" => $item->numbervoucher
                                    //     )
                                    // );

                                    // $data_dinheiro = $dinheiro->fetch(PDO::FETCH_ASSOC);
                                    // print_r('$data_dinheiro');
                                    // $find_file_salesmen->debugDumpParams();

                                    // $total_dinheiro += $data_dinheiro['totaldinheiro'];


                                    // $panda = $pdo->prepare('select sum(cfc.valuecredit) as totalpanda from `ct_createfaturacredit` cfc  where datacredit >= :inicio and datacredit <= :fim and idaccountcurrent = :forma and numbervoucher = :voucher ');
                                    // $panda->execute(
                                    //     array(
                                    //         ":inicio" => $inicio,
                                    //         ":fim"    => $fim,
                                    //         ":forma"  => 23,
                                    //         ":voucher" => $item->numbervoucher
                                    //     )
                                    // );
                                    // $data_panda = $panda->fetch(PDO::FETCH_ASSOC);
                                    // print_r('$data_panda');
                                    // $panda->debugDumpParams();

                                    // $total_panda += $data_panda['totalpanda'];

                                    // $cortesia = $pdo->prepare('select sum(cfc.valuecredit) as totalcortesia from `ct_createfaturacredit` cfc  where datacredit >= :inicio and datacredit <= :fim and idaccountcurrent = :forma and numbervoucher = :voucher ');
                                    // $cortesia->execute(
                                    //     array(
                                    //         ":inicio" => $inicio,
                                    //         ":fim"    => $fim,
                                    //         ":forma"  => 39,
                                    //         ":voucher" => $item->numbervoucher
                                    //     )
                                    // );
                                    // $data_cortesia = $cortesia->fetch(PDO::FETCH_ASSOC);
                                    // print_r('$data_cortesia');
                                    // $cortesia->debugDumpParams();
                                    // die;

                                    // $total_cortesia += $data_cortesia['totalcortesia'];
                                }
                            } else {
                                $find_reservation_primary = $pdo->prepare(
                                    'select r.id, dateinput , c.namefantazia as cliente, pax, s.fullname as servico,qtdpax, qtdchild, nameinvoice as situacao, r.valueservice, r.idstatusinvoice, qtdfree, u.firstname, u.lastname
                                        from `ct_reserva` r  left join `ct_cliente` c on c.`id` = r.`idcliente` left join `ct_servico` s on s.`id` = r.`idservico` left join `ct_usuario` u on u.`id` = r.`idresponsavel`
                                        left join `ct_statusinvoice` si on si.`id` = r.`idstatusinvoice` where r.numbervoucher = :voucher ');
                                $find_reservation_primary->execute(array(":voucher" => $item->numbervoucher));

                                $find_data_payments_of_client = $pdo->prepare(
                                    'SELECT  sum(valuecredit) as valor  FROM `ct_createfaturacredit` cfc
                                          left join `ct_currentaccount` cc on cc.id = cfc.idaccountcurrent left join `ct_usuario` u on cfc.idusr = u.id where numbervoucher = :voucher and cfc.idusr = :id and datacredit >= :inicio and datacredit <= :fim'
                                );
                                $find_data_payments_of_client->execute(array(":voucher" =>  $item->numbervoucher, ":id" => $responsavel, ":inicio" => $inicio, ":fim"    => $fim,));
                                $find_data_payments_of_client2 = $pdo->prepare(
                                    'SELECT  u.firstname, u.lastname, cc.`name` as pagamento, cfc.datacredit as dia FROM `ct_createfaturacredit` cfc
                                          left join `ct_currentaccount` cc on cc.id = cfc.idaccountcurrent left join `ct_usuario` u on cfc.idusr = u.id where numbervoucher = :voucher and cfc.idusr = :id and datacredit >= :inicio and datacredit <= :fim'
                                );
                                $find_data_payments_of_client2->execute(array(":voucher" =>  $item->numbervoucher, ":id" => $responsavel, ":inicio" => $inicio, ":fim"    => $fim,));
                                $transferencia = $pdo->prepare('select sum(cfc.valuecredit) as totaltransferencia from `ct_createfaturacredit` cfc  where datacredit >= :inicio and datacredit <= :fim and cfc.idusr = :id  and idaccountcurrent = :forma ');
                                $transferencia->execute(
                                    array(
                                        ":inicio" => $inicio,
                                        ":fim"    => $fim,
                                        ":id"     => $responsavel,
                                        ":forma"  => 36
                                    )
                                );
                                $data_tranferenci = $transferencia->fetch(PDO::FETCH_ASSOC);
                                // print_r('$data_tranferenci');
                                // $transferencia->debugDumpParams();

                                $total_transferencia += $data_tranferenci['totaltransferencia'];

                                $cartao_credito = $pdo->prepare('select sum(cfc.valuecredit) as totalcartaocredito from `ct_createfaturacredit` cfc  where datacredit >= :inicio and datacredit <= :fim and cfc.idusr = :id and idaccountcurrent = :forma ');
                                $cartao_credito->execute(
                                    array(
                                        ":inicio" => $inicio,
                                        ":fim"    => $fim,
                                        ":id"     => $responsavel,
                                        ":forma"  => 24,
                                    )
                                );
                                $data_cartao_credito = $cartao_credito->fetch(PDO::FETCH_ASSOC);
                                // print_r('$data_cartao_credito');
                                // $cartao_credito->debugDumpParams();

                                $total_cartaocredito += $data_cartao_credito['totalcartaocredito'];

                                $cartao_debito = $pdo->prepare('select sum(cfc.valuecredit) as totalcartaodebito from `ct_createfaturacredit` cfc  where datacredit >= :inicio and datacredit <= :fim and cfc.idusr = :id and idaccountcurrent = :forma ');
                                $cartao_debito->execute(
                                    array(
                                        ":inicio" => $inicio,
                                        ":fim"    => $fim,
                                        ":id"     => $responsavel,
                                        ":forma"  => 25,
                                    )
                                );
                                $data_cartao_debito = $cartao_debito->fetch(PDO::FETCH_ASSOC);
                                // print_r('$data_cartao_debito');
                                // $cartao_debito->debugDumpParams();

                                $total_cartaodebito += $data_cartao_debito['totalcartaodebito'];

                                $paypal = $pdo->prepare('select sum(cfc.valuecredit) as totalpaypal from `ct_createfaturacredit` cfc  where datacredit >= :inicio and datacredit <= :fim and cfc.idusr = :id  and idaccountcurrent = :forma ');
                                $paypal->execute(
                                    array(
                                        ":inicio" => $inicio,
                                        ":fim"    => $fim,
                                        ":id"     => $responsavel,
                                        ":forma"  => 22,
                                    )
                                );
                                $data_paypal = $paypal->fetch(PDO::FETCH_ASSOC);
                                // print_r('$data_paypal');
                                // $paypal->debugDumpParams();

                                $total_paypal += $data_paypal['totalpaypal'];

                                $dinheiro = $pdo->prepare('select sum(cfc.valuecredit) as totaldinheiro from `ct_createfaturacredit` cfc  where datacredit >= :inicio and datacredit <= :fim and cfc.idusr = :id   and idaccountcurrent = :forma ');
                                $dinheiro->execute(
                                    array(
                                        ":inicio" => $inicio,
                                        ":fim"    => $fim,
                                        ":id"     => $responsavel,
                                        ":forma"  => 18,
                                    )
                                );

                                $data_dinheiro = $dinheiro->fetch(PDO::FETCH_ASSOC);
                                // print_r('$data_dinheiro');
                                // $dinheiro->debugDumpParams();

                                $total_dinheiro += $data_dinheiro['totaldinheiro'];

                                $panda = $pdo->prepare('select sum(cfc.valuecredit) as totalpanda from `ct_createfaturacredit` cfc  where datacredit >= :inicio and datacredit <= :fim and cfc.idusr = :id  and idaccountcurrent = :forma ');
                                $panda->execute(
                                    array(
                                        ":inicio" => $inicio,
                                        ":fim"    => $fim,
                                        ":id"     => $responsavel,
                                        ":forma"  => 23,
                                    )
                                );
                                $data_panda = $panda->fetch(PDO::FETCH_ASSOC);
                                // print_r('$data_panda');
                                // $panda->debugDumpParams();

                                $total_panda += $data_panda['totalpanda'];

                                $cortesia = $pdo->prepare('select sum(cfc.valuecredit) as totalcortesia from `ct_createfaturacredit` cfc  where datacredit >= :inicio and datacredit <= :fim and cfc.idusr = :id  and idaccountcurrent = :forma ');
                                $cortesia->execute(
                                    array(
                                        ":inicio" => $inicio,
                                        ":fim"    => $fim,
                                        ":id"     => $responsavel,
                                        ":forma"  => 39,
                                    )
                                );
                                $data_cortesia = $cortesia->fetch(PDO::FETCH_ASSOC);
                                // print_r('$data_cortesia');
                                // $cortesia->debugDumpParams();

                                $total_cortesia += $data_cortesia['totalcortesia'];
                            }

                            $data_payment_of_cliente = $find_data_payments_of_client->fetch(PDO::FETCH_ASSOC);
                            // print_r('$data_payment_of_cliente');
                            // $find_data_payments_of_client->debugDumpParams();
                            // die;

                            // $data_payment_of_cliente2 = $find_data_payments_of_client2->fetch(PDO::FETCH_ASSOC);
                            // print_r('$data_payment_of_cliente2');
                            // $find_data_payments_of_client2->debugDumpParams();
                            // die;

                            // Removi temporario, colocando na linha 192 para evitar um monte de queries.
                            $data_find_reservation_primary = $find_reservation_primary->fetch(PDO::FETCH_ASSOC);
                            // print_r('$data_find_reservation_primary');
                            // $find_reservation_primary->debugDumpParams();

                            if ($data_find_reservation_primary && date("d/m/Y", strtotime($data_find_reservation_primary['dateinput'])) <> '31/12/1969'){

                            } else {
                                continue;
                            }

                            $find_reservation_secundarys = $pdo->prepare('select * from `ct_recentlyadd` ra left join `ct_servico` s on ra.idservice = s.id where ra.idrecently = :idprimary');
                            $find_reservation_secundarys->execute(array(":idprimary" => $data_find_reservation_primary['id']));
                            $count = $find_reservation_secundarys->rowCount();

                            $total_one = (($data_find_reservation_primary['valueservice'] * $data_find_reservation_primary['qtdpax']) + (($data_find_reservation_primary['valueservice'] / 2) * $data_find_reservation_primary['qtdchild']));

                            $total_recebido += $data_payment_of_cliente['valor'];
                            $total_general += $total_one;

                            // echo'<pre>';print_r($data_payment_of_cliente);echo'</pre>';
                            // die;

                        ?>
                            <?php if ($data_find_reservation_primary && date("d/m/Y", strtotime($data_find_reservation_primary['dateinput'])) <> '31/12/1969') { ?>
                                <tr>
                                    <td><?php echo (date("d/m/Y", strtotime($data_find_reservation_primary['dateinput']))); ?></td>
                                    <td><?php echo ($item->numbervoucher); ?></td>
                                    <td><?php echo (utf8_encode($data_find_reservation_primary['cliente'])); ?></td>
                                    <td><?php echo (utf8_encode($data_find_reservation_primary['pax'])); ?></td>
                                    <td><?php echo ($data_find_reservation_primary['qtdpax'] . " | " . $data_find_reservation_primary['qtdchild'] . " | " . $data_find_reservation_primary['qtdfree']); ?></td>
                                    <td><?php echo (strtoupper($data_find_reservation_primary['firstname'] . " " . $data_find_reservation_primary['lastname'])); ?></td>
                                    <td><?php echo (utf8_encode($data_find_reservation_primary['servico'])); ?></td>
                                    <td><?php echo ("R$ " . number_format($data_find_reservation_primary['valueservice'], 2, ",", ".")); ?></td>
                                    <td><?php echo ("R$ " . number_format($total_one, 2, ",", ".")); ?></td>
                                    <td><?php echo ($data_payment_of_cliente2) ? (strtoupper($data_payment_of_cliente2['firstname'] . " " . $data_payment_of_cliente2['lastname'])) : null; ?></td>
                                    <td><?php echo (date("d/m/Y", strtotime($data_find_reservation_primary['data_integracao']))); ?></td>
                                    <td><?php echo ("R$ " . number_format($data_payment_of_cliente['valor'], 2, ",", ".")); ?></td>
                                    <td><?php echo ($data_find_reservation_primary['situacao']); ?></td>
                                </tr>
                                <?php if ($count >= 1) { ?>
                                    <?php while ($data_find_reservation_secundarys = $find_reservation_secundarys->fetch(PDO::FETCH_ASSOC)) {
                                        // print_r('$data_find_reservation_secundarys');
                                        // $find_reservation_secundarys->debugDumpParams();

                                        $total_two = (($data_find_reservation_secundarys['valueservice'] * $data_find_reservation_secundarys['qpax']) + (($data_find_reservation_secundarys['valueservice'] / 2) *
                                            $data_find_reservation_secundarys['qchild']));
                                        $total_general += $total_two;
                                    ?>
                                        <tr>
                                            <td><?php echo (date("d/m/Y", strtotime($data_find_reservation_secundarys['dateinput']))); ?></td>
                                            <td><?php echo ($item->numbervoucher); ?></td>
                                            <td><?php echo (utf8_encode($data_find_reservation_primary['cliente'])); ?></td>
                                            <td><?php echo (utf8_encode($data_find_reservation_primary['pax'])); ?></td>
                                            <td><?php echo ($data_find_reservation_secundarys['qpax'] . " | " . $data_find_reservation_secundarys['qchild'] . " | " . $data_find_reservation_secundarys['qfree']); ?></td>
                                            <td><?php echo (strtoupper($data_find_reservation_primary['firstname'] . " " . $data_find_reservation_primary['lastname'])); ?></td>
                                            <td><?php echo (utf8_encode($data_find_reservation_secundarys['fullname'])); ?></td>
                                            <td><?php echo ("R$ " . number_format($data_find_reservation_secundarys['valueservice'], 2, ",", ".")); ?></td>
                                            <td><?php echo ("R$ " . number_format($total_two, 2, ",", ".")); ?></td>
                                            <td><?php echo (strtoupper($data_payment_of_cliente2['firstname'] . " " . $data_payment_of_cliente2['lastname'])); ?></td>
                                            <td><?php echo (date("d/m/Y", strtotime($data_find_reservation_primary['data_integracao']))); ?></td>
                                            <td>-</td>
                                            <td><?php echo ($data_find_reservation_primary['situacao']); ?></td>
                                        </tr>
                                    <?php  } ?>

                                <?php  } ?>
                            <?php } ?>

                        <?php } ?>
                    </tbody>
                </table>
                <?php if ($verify_caixa > 0 and !empty($verify_caixa)) { ?>
                    <p>Despesas</p>

                    <table id="" class="highlight">
                        <thead>
                            <tr>
                                <th>Data Venci.</th>
                                <th>Data Pag.</th>
                                <th>Data Compe.</th>
                                <th>Nome</th>
                                <th>Descrição</th>
                                <th>Favorecido</th>
                                <th>Tipo</th>
                                <th>Forma de Pagamento.</th>
                                <th>Plano de C.</th>
                                <th>Valor</th>
                                <th>Situação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($registroCaixa as $item) {
                                $total_out += $item->valor ?>
                                <tr>
                                    <td><?php echo (date("d-m-Y", strtotime($item->datevencimento))); ?></td>
                                    <td><?php echo (date("d-m-Y", strtotime($item->datepagamento))); ?></td>
                                    <td><?php echo (date("d-m-Y", strtotime($item->datecompetencia))); ?></td>
                                    <td><?php echo ($item->nome); ?></td>
                                    <td><?php echo ($item->descricao); ?></td>
                                    <td><?php echo (utf8_encode($item->fornecedor)); ?></td>
                                    <td><?php echo (utf8_encode($item->tipo)); ?></td>
                                    <td><?php echo ($item->conta); ?></td>
                                    <td><?php echo ($item->plano); ?></td>
                                    <td><?php echo ("R$" . number_format($item->valor, 2, ",", ".")); ?></td>
                                    <td>
                                        <?php echo ($item->situacao); ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                <?php } ?>
                <br>
                <p>Totais</p>
                <br>
                <table class="highlight">
                    <thead>
                        <tr>
                            <th>Total vendido</th>
                            <th>Total recebido</th>
                            <th>Tranferência</th>
                            <th>Em Cartão de Crédito</th>
                            <th>Em Cartão de Débito</th>
                            <th>Pag seguro</th>
                            <th>Em Dinheiro</th>
                            <th>Posto Panda</th>
                            <th>Cortesia</th>
                            <th>Total de Despesas</th>
                            <th>Saldo</th>
                            <th>Dinheiro Liquido</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php echo ("R$ " . number_format($total_general, 2, ",", ".")) ?></td>
                            <td><?php echo ("R$ " . number_format($total_recebido, 2, ",", ".")) ?></td>
                            <td><?php echo ("R$ " . number_format($data_tranferenci['totaltransferencia'], 2, ",", ".")); ?></td>
                            <td><?php echo ("R$ " . number_format($data_cartao_credito['totalcartaocredito'], 2, ",", ".")); ?></td>
                            <td><?php echo ("R$ " . number_format($data_cartao_debito['totalcartaodebito'], 2, ",", ".")); ?></td>
                            <td><?php echo ("R$ " . number_format($data_paypal['totalpaypal'], 2, ",", ".")); ?></td>
                            <td><?php echo ("R$ " . number_format($data_dinheiro['totaldinheiro'], 2, ",", ".")); ?></td>
                            <td><?php echo ("R$ " . number_format($data_panda['totalpanda'], 2, ",", ".")); ?></td>
                            <td><?php echo ("R$ " . number_format($data_cortesia['totalcortesia'], 2, ",", ".")); ?></td>
                            <td><?php echo ("R$ " . number_format($total_out, 2, ",", ".")) ?></td>
                            <td><?php echo ("R$ " . number_format($total_recebido - $total_out, 2, ",", ".")) ?></td>
                            <td><?php echo ("R$ " . number_format($data_dinheiro['totaldinheiro'] - $total_out, 2, ",", ".")) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </body>

        </html>

    <?php } elseif ($verify_caixa > 0) { ?>
        <html xmlns="http://www.w3.org/1999/xhtml">

        <head>
            <meta charset="utf-8">
            <title>Relatório de conferência do vendedor</title>
            <link rel="stylesheet" href="materialize.min.css">
        </head>
        <style>
            th,
            td {
                border: 1px solid #ddd;
                padding: 8px;
                font-size: 10px;
            }

            td#desc {
                font-weight: bold;
            }
        </style>

        <body>
            <div class="container">
                <img style="width: 700px;" id="logo" src="../../images/logo.png" />

                <p>Despesas</p>

                <table id="" class="highlight">
                    <thead>
                        <tr>
                            <th>Data Venci.</th>
                            <th>Data Pag.</th>
                            <th>Data Compe.</th>
                            <th>Nome</th>
                            <th>Descrição</th>
                            <th>Favorecido</th>
                            <th>Tipo</th>
                            <th>Forma de Pagamento</th>
                            <th>Plano de C.</th>
                            <th>Valor</th>
                            <th>Situação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($registroCaixa as $item) {
                            $total_out += $item->valor; ?>
                            <tr>
                                <td><?php echo (date("d-m-Y", strtotime($item->datevencimento))); ?></td>
                                <td><?php echo (date("d-m-Y", strtotime($item->datepagamento))); ?></td>
                                <td><?php echo (date("d-m-Y", strtotime($item->datecompetencia))); ?></td>
                                <td><?php echo ($item->nome); ?></td>
                                <td><?php echo ($item->descricao); ?></td>
                                <td><?php echo (utf8_encode($item->fornecedor)); ?></td>
                                <td><?php echo (utf8_encode($item->tipo)); ?></td>
                                <td><?php echo ($item->conta); ?></td>
                                <td><?php echo ($item->plano); ?></td>
                                <td><?php echo ("R$" . number_format($item->valor, 2, ",", ".")); ?></td>
                                <td>
                                    <?php echo ($item->situacao); ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <p>Totais</p>
                <br>
                <table class="highlight">
                    <thead>
                        <tr>
                            <th>Total de Despesas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php echo ("R$ " . number_format($total_out, 2, ",", ".")) ?></td>

                        </tr>
                    </tbody>
                </table>
            </div>
        </body>

        </html>
    <?php } else { ?>
        <html xmlns="http://www.w3.org/1999/xhtml">

        <head>
            <meta charset="utf-8">
            <title>Relatório de conferência do vendedor</title>
            <link rel="stylesheet" href="materialize.min.css">
        </head>
        <style>
            th,
            td {
                border: 1px solid #ddd;
                padding: 8px;
                font-size: 10px;
            }

            td#desc {
                font-weight: bold;
            }
        </style>

        <body>
            <div class="container">
                <img style="width: 700px; margin-left: 50px; " id="logo" src="../../images/logo.png" />

                <hr>
                <h3>Não encontramos pagamentos/reservas realizados por esse vendedor(a)</h3>
            </div>
        </body>

        </html>
    <?php } ?>
<?php } else {

?>
    <html xmlns="http://www.w3.org/1999/xhtml">

    <head>
        <meta charset="utf-8">
        <title>Relatório de conferência do vendedor</title>
        <link rel="stylesheet" href="materialize.min.css">
    </head>
    <style>
        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            font-size: 10px;
        }

        td#desc {
            font-weight: bold;
        }
    </style>

    <body>
        <div class="container">
            <img style="width: 700px; margin-left: 50px; " id="logo" src="../../images/logo.png" />

            <hr>
            <p><?php echo (("Relatório de Conferência de: " .
                    date("d/m/Y ", strtotime($inicio)) . " ate " . date("d/m/Y ", strtotime($fim))) . " Por vendedor."); ?> </p><br>
            <p style="font-size: 9px; margin-top: -20px;">Impresso em: <?php echo (date("d/m/Y - H:i:s")); ?></p>
            <?php if ($agencia == 0) { ?>
                <h5>Recebido</h5>
                <hr>
                <table class="highlight">
                    <thead>
                        <tr>
                            <th>Operador</th>
                            <th>Recebido</th>
                            <th>Tranferência</th>
                            <th>Em Cartão de Crédito</th>
                            <th>Em Cartão de Débito</th>
                            <th>Pag seguro</th>
                            <th>Em Dinheiro</th>
                            <th>Posto Panda</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data_find_file_salesmen as $item) {
                            $moneyop = $pdo->prepare('select sum(cfc.valuecredit) as tot, u.firstname, u.lastname from `ct_createfaturacredit` cfc left join ct_usuario u on u.id = cfc.idusr  where datacredit >= :inicio and datacredit <= :fim and cfc.idusr = :id ');
                            $moneyop->execute(
                                array(
                                    ":inicio" => $inicio,
                                    ":fim"    => $fim,
                                    ":id"     => $item->idusr
                                )
                            );
                            $data_moneyop = $moneyop->fetch(PDO::FETCH_ASSOC);
                            // print_r('$data_moneyop');
                            // $moneyop->debugDumpParams();

                            $tot_dia += $data_moneyop['tot'];

                            $transferencia = $pdo->prepare('select sum(cfc.valuecredit) as totaltransferencia from `ct_createfaturacredit` cfc  where datacredit >= :inicio and datacredit <= :fim and cfc.idusr = :id  and idaccountcurrent = :forma ');
                            $transferencia->execute(
                                array(
                                    ":inicio" => $inicio,
                                    ":fim"    => $fim,
                                    ":id"     => $item->idusr,
                                    ":forma"  => 36
                                )
                            );
                            $data_tranferenci = $transferencia->fetch(PDO::FETCH_ASSOC);
                            // print_r('$data_tranferenci');
                            // $transferencia->debugDumpParams();

                            $total_transferencia += $data_tranferenci['totaltransferencia'];

                            $cartao_credito = $pdo->prepare('select sum(cfc.valuecredit) as totalcartaocredito from `ct_createfaturacredit` cfc  where datacredit >= :inicio and datacredit <= :fim and cfc.idusr = :id and idaccountcurrent = :forma ');
                            $cartao_credito->execute(
                                array(
                                    ":inicio" => $inicio,
                                    ":fim"    => $fim,
                                    ":id"     => $item->idusr,
                                    ":forma"  => 24,
                                )
                            );
                            $data_cartao_credito = $cartao_credito->fetch(PDO::FETCH_ASSOC);
                            // print_r('$data_cartao_credito');
                            // $find_file_salesmen->debugDumpParams();

                            $total_cartaocredito += $data_cartao_credito['totalcartaocredito'];

                            $cartao_debito = $pdo->prepare('select sum(cfc.valuecredit) as totalcartaodebito from `ct_createfaturacredit` cfc  where datacredit >= :inicio and datacredit <= :fim and cfc.idusr = :id and idaccountcurrent = :forma ');
                            $cartao_debito->execute(
                                array(
                                    ":inicio" => $inicio,
                                    ":fim"    => $fim,
                                    ":id"     => $item->idusr,
                                    ":forma"  => 25,
                                )
                            );
                            $data_cartao_debito = $cartao_debito->fetch(PDO::FETCH_ASSOC);
                            // print_r('$data_cartao_debito');
                            // $cartao_debito->debugDumpParams();

                            $total_cartaodebito += $data_cartao_debito['totalcartaodebito'];

                            $paypal = $pdo->prepare('select sum(cfc.valuecredit) as totalpaypal from `ct_createfaturacredit` cfc  where datacredit >= :inicio and datacredit <= :fim and cfc.idusr = :id  and idaccountcurrent = :forma ');
                            $paypal->execute(
                                array(
                                    ":inicio" => $inicio,
                                    ":fim"    => $fim,
                                    ":id"     => $item->idusr,
                                    ":forma"  => 22,
                                )
                            );
                            $data_paypal = $paypal->fetch(PDO::FETCH_ASSOC);
                            // print_r('$data_paypal');
                            // $paypal->debugDumpParams();

                            $total_paypal += $data_paypal['totalpaypal'];

                            $dinheiro = $pdo->prepare('select sum(cfc.valuecredit) as totaldinheiro from `ct_createfaturacredit` cfc  where datacredit >= :inicio and datacredit <= :fim and cfc.idusr = :id   and idaccountcurrent = :forma ');
                            $dinheiro->execute(
                                array(
                                    ":inicio" => $inicio,
                                    ":fim"    => $fim,
                                    ":id"     => $item->idusr,
                                    ":forma"  => 18,
                                )
                            );

                            $data_dinheiro = $dinheiro->fetch(PDO::FETCH_ASSOC);
                            // print_r('$data_dinheiro');
                            // $dinheiro->debugDumpParams();

                            $total_dinheiro += $data_dinheiro['totaldinheiro'];

                            $panda = $pdo->prepare('select sum(cfc.valuecredit) as totalpanda from `ct_createfaturacredit` cfc  where datacredit >= :inicio and datacredit <= :fim and cfc.idusr = :id  and idaccountcurrent = :forma ');
                            $panda->execute(
                                array(
                                    ":inicio" => $inicio,
                                    ":fim"    => $fim,
                                    ":id"     => $item->idusr,
                                    ":forma"  => 23,
                                )
                            );
                            $data_panda = $panda->fetch(PDO::FETCH_ASSOC);
                            // print_r('$data_panda');
                            // $panda->debugDumpParams();

                            $total_panda += $data_panda['totalpanda'];

                        ?>
                            <tr>
                                <td><?php echo (strtoupper($data_moneyop['firstname'] . " " . $data_moneyop['lastname'])); ?></td>
                                <td><?php echo ("R$ " . number_format($data_moneyop['tot'], 2, ",", ".")); ?></td>
                                <td><?php echo ("R$ " . number_format($data_tranferenci['totaltransferencia'], 2, ",", ".")); ?></td>
                                <td><?php echo ("R$ " . number_format($data_cartao_credito['totalcartaocredito'], 2, ",", ".")); ?></td>
                                <td><?php echo ("R$ " . number_format($data_cartao_debito['totalcartaodebito'], 2, ",", ".")); ?></td>
                                <td><?php echo ("R$ " . number_format($data_paypal['totalpaypal'], 2, ",", ".")); ?></td>
                                <td><?php echo ("R$ " . number_format($data_dinheiro['totaldinheiro'], 2, ",", ".")); ?></td>
                                <td><?php echo ("R$ " . number_format($data_panda['totalpanda'], 2, ",", ".")); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <table class="highlight">
                    <thead>
                        <tr>
                            <th>Total Recebido</th>
                            <th>Total Tranferência</th>
                            <th>Total Cartão de Crédito</th>
                            <th>Total Cartão de Débito</th>
                            <th>Total Pag seguro</th>
                            <th>Total Em Dinheiro</th>
                            <th>Total Posto Panda</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php echo ("R$ " . number_format($tot_dia, 2, ",", ".")); ?></td>
                            <td><?php echo ("R$ " . number_format($total_transferencia, 2, ",", ".")); ?></td>
                            <td><?php echo ("R$ " . number_format($total_cartaocredito, 2, ",", ".")); ?></td>
                            <td><?php echo ("R$ " . number_format($total_cartaodebito, 2, ",", ".")); ?></td>
                            <td><?php echo ("R$ " . number_format($total_paypal, 2, ",", ".")); ?></td>
                            <td><?php echo ("R$ " . number_format($total_dinheiro, 2, ",", ".")); ?></td>
                            <td><?php echo ("R$ " . number_format($total_panda, 2, ",", ".")); ?></td>
                        </tr>
                    </tbody>
                </table>
                <br>
                <h5>Despesas</h5>
                <hr>
                <table class="highlight">
                    <thead>
                        <tr>
                            <th>Operador</th>
                            <th>Despesas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data_find_despesas as $item2) {
                            $despesas = $pdo->prepare('select sum(c.valor) as tot, u.firstname, u.lastname from `ct_caixa` c left join ct_usuario u on u.id = c.idusr  where c.datepagamento >= :inicio and c.datepagamento <= :fim and c.idusr = :id  and c.`idtipo` = 2 ');
                            $despesas->execute(
                                array(
                                    ":inicio" => $inicio,
                                    ":fim"    => $fim,
                                    ":id"     => $item2->idusr
                                )
                            );
                            $data_g_despesas = $despesas->fetch(PDO::FETCH_ASSOC);
                            // print_r('$data_g_despesas');
                            // $despesas->debugDumpParams();

                            $total_despesas += $data_g_despesas['tot'];

                        ?>
                            <tr>
                                <td><?php echo (strtoupper($data_g_despesas['firstname'] . " " . $data_g_despesas['lastname'])); ?></td>
                                <td><?php echo ("R$ " . number_format($data_g_despesas['tot'], 2, ",", ".")); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <table class="highlight">
                    <thead>
                        <tr>
                            <th>Total de Recebido</th>
                            <th>Total de Despesas</th>
                            <th>Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php echo ("R$ " . number_format($tot_dia, 2, ",", ".")); ?></td>
                            <td><?php echo ("R$ " . number_format($total_despesas, 2, ",", ".")); ?></td>
                            <td><?php echo ("R$ " . number_format($tot_dia - $total_despesas, 2, ",", ".")); ?></td>
                        </tr>
                    </tbody>
                </table>
            <?php } else { ?>
                <table class="highlight">
                    <thead>
                        <tr>
                            <th>Agência</th>
                            <th>Operador</th>
                            <th>Voucher</th>
                            <th>Subtotal</th>

                        </tr>
                    </thead>
                    <tbody>
                        <?php $total = 0;
                        foreach ($reservas as $item) {
                            $total += $item->valuecredit; ?>
                            <tr>
                                <td><?php echo ($item->fullname); ?></td>
                                <td><?php echo (strtoupper(utf8_encode($item->firstname . " " . $item->lastname))); ?></td>
                                <td><?php echo ($item->numbervoucher); ?></td>
                                <td><?php echo ("R$ " . number_format($item->valuecredit, 2, ",", ".")); ?></td>
                            </tr>
                        <?php } ?>

                    </tbody>
                </table>
                <table class="highlight">
                    <thead>
                        <tr>
                            <th>Total Vendido</th>
                        </tr>


                    </thead>
                    <tbody>
                        <tr>
                            <td><?php echo ("R$ " . number_format($total, 2, ",", ".")); ?></td>
                        </tr>
                    </tbody>
                </table>
            <?php } ?>

        </div>
    </body>

    </html>
<?php } ?>

<?php
/*
if($agencia > 0)
{
    $html = ob_get_clean();

//------------------------------------------------------------------------------------------------------------
    $arquivo = "relatorio.pdf" ;
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
}*/

?>