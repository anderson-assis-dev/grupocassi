<?php require_once ('header.php');
$dadosFinanceiro['credito'] = 0;
$dadosFinanceiro['guia'] = 0;
$dadosFinanceiro['agente'] = 0;
$dadosFinanceiro['name'] = '';
if( isset( $_POST['voucher'] )  )
{
    $totalSecundario = 0;
    $numberVoucher = $_POST['voucher'];
    $dadosReserva = $pdo->prepare(
        "SELECT r.id, r.valueservice as valorp, r.qtdpax, photoresident ,r.qtdchild, ra.qpax, ra.qchild, ra.valueservice as valors, c.fullname as cliente, r.pax,
                          r.documento,se.fullname as servico, c.fullname as cliente, namepayment, nameinvoice, r.numberfatura, r.dateinput FROM `ct_reserva` r
                  left join ct_cliente c on c.id = r.idcliente left join ct_responsavel re on re.id = r.idresponsavel
                  left join ct_status s on s.id = r.idstatus left join ct_guia g on g.id = r.idguia join ct_servico se on se.id = r.idservico
                  left join ct_agentes as ag on r.idagente = ag.id left join ct_statusinvoice sti on sti.id = r.idstatusinvoice
                  left join ct_recentlyadd ra on ra.idrecently = r.id left join ct_service_schedule ss on ss.idshedule = r.idhorario
                  left join `ct_form_of_ payment` as cfp on cfp.id = r.idpayment where `numbervoucher` = :numbervoucher");
    $dadosReserva->execute( array(":numbervoucher" => $numberVoucher ));
    $dadosGerais = $dadosReserva->fetch(PDO::FETCH_ASSOC);

    $contador = $dadosReserva->rowCount();

    $administrativo = $pdo->prepare(
        'select c.id, c.datematurity, c.datepayment, c.numberadd, cc.name from `ct_createfatura` c left join ct_currentaccount cc on cc.id = c.idcurrentaccount
                    where numbervoucher = :voucher');
    $administrativo->execute(array(":voucher" => $numberVoucher));
    $contadorAdm = $administrativo->rowCount();
    $registroAdm = $administrativo->fetchAll(PDO::FETCH_CLASS);

    $adicionais = $pdo->prepare(
        'SELECT r.pax, ra.dateinput as ap, s.fullname, qpax, qchild, qfree, ra.valueservice, ra.horaap, ra.documento, c.fullname as cliente, ra.dateinput
                FROM `ct_recentlyadd` ra left join `ct_reserva` r on r.id = ra.idrecently
                      left join ct_servico s on s.id = ra.idservice left join ct_service_schedule ss on ss.idshedule = ra.idschedule
                      left join ct_cliente c on c.id = r.idcliente where r.id = :id order by ap');
    $adicionais->execute(array(":id" => $dadosGerais['id'] ) );
    $registro = $adicionais->fetchAll(PDO::FETCH_CLASS);
    $contador = $adicionais->rowCount();
    $totalPrincipal =  ( ($dadosGerais['valorp'] * $dadosGerais['qtdpax']) +
        ( ($dadosGerais['valorp']/ 2) * $dadosGerais['qtdchild'] ) );

    while ($calculcar = $dadosReserva->fetch(PDO::FETCH_ASSOC))
    {
        $totalSecundario = $totalSecundario +  ( ($calculcar['valors'] * $calculcar['qpax']) +
                ( ($calculcar['valors']/ 2) * $calculcar['qchild'] ) );
    }
    $total = $totalPrincipal;

    $financeiroReserva = $pdo->prepare(
        "SELECT tarifa, valuecredit as credito, valueagente as agente, valueguia as guia, `name` FROM `ct_createfaturacredit`
    cfc left join `ct_currentaccount` cc on cfc.idaccountcurrent = cc.id where numbervoucher = '$numberVoucher' ");
    $financeiroReserva->execute();
    
    while ($dados_fi = $financeiroReserva->fetch( PDO::FETCH_ASSOC ))
    {
        $dadosFinanceiro['credito'] += $dados_fi['credito'];
        $dadosFinanceiro['agente'] += $dados_fi['agente'];
        $dadosFinanceiro['guia'] += $dados_fi['guia'];
        $dadosFinanceiro['name'] = $dados_fi['name'];
    }

}
if( isset( $_POST['adm'] ) )
{
    $idfatura      = $_POST['idfatura'];
    $dadosAdm      = $pdo->prepare(
            'select c.id, c.datematurity, c.datepayment, c.numberadd, cc.name from `ct_createfatura` 
c left join ct_currentaccount cc on cc.id = c.idcurrentaccount  where c.id = :id');
    $dadosAdm->execute(array(":id" => $idfatura));
    $dadosFatura  = $dadosAdm->fetch(PDO::FETCH_ASSOC);
    $contadorDados = $dadosAdm->rowCount();
    $contaCorrente = $pdo->prepare('SELECT * FROM `ct_currentaccount` ');
    $contaCorrente->execute();

    $totalSecundario = 0;
    $numberVoucher = $_POST['voucher'];
    $dadosReserva = $pdo->prepare(
        "SELECT r.id, r.valueservice as valorp, r.qtdpax, photoresident ,r.qtdchild, ra.qpax, ra.qchild, ra.valueservice as valors, c.fullname as cliente, r.pax,
                          r.documento,se.fullname as servico, c.fullname as cliente, namepayment, nameinvoice, r.numberfatura, r.dateinput FROM `ct_reserva` r
                  left join ct_cliente c on c.id = r.idcliente left join ct_responsavel re on re.id = r.idresponsavel
                  left join ct_status s on s.id = r.idstatus left join ct_guia g on g.id = r.idguia join ct_servico se on se.id = r.idservico
                  left join ct_agentes as ag on r.idagente = ag.id left join ct_statusinvoice sti on sti.id = r.idstatusinvoice
                  left join ct_recentlyadd ra on ra.idrecently = r.id left join ct_service_schedule ss on ss.idshedule = r.idhorario
                  left join `ct_form_of_ payment` as cfp on cfp.id = r.idpayment where `numbervoucher` = :numbervoucher");
    $dadosReserva->execute( array(":numbervoucher" => $numberVoucher ));
    $dadosGerais = $dadosReserva->fetch(PDO::FETCH_ASSOC);

    $contador = $dadosReserva->rowCount();

    $administrativo = $pdo->prepare(
        'select c.id, c.datematurity, c.datepayment, c.numberadd, cc.name from `ct_createfatura` c left join ct_currentaccount cc on cc.id = c.idcurrentaccount
                    where numbervoucher = :voucher');
    $administrativo->execute(array(":voucher" => $numberVoucher));
    $contadorAdm = $administrativo->rowCount();
    $registroAdm = $administrativo->fetchAll(PDO::FETCH_CLASS);

    $adicionais = $pdo->prepare(
        'SELECT r.pax, ra.dateinput as ap, s.fullname, qpax, qchild, qfree, ra.valueservice, ra.horaap, ra.documento, c.fullname as cliente, ra.dateinput
                FROM `ct_recentlyadd` ra left join `ct_reserva` r on r.id = ra.idrecently
                      left join ct_servico s on s.id = ra.idservice left join ct_service_schedule ss on ss.idshedule = ra.idschedule
                      left join ct_cliente c on c.id = r.idcliente where r.id = :id order by ap');
    $adicionais->execute(array(":id" => $dadosGerais['id'] ) );
    $registro = $adicionais->fetchAll(PDO::FETCH_CLASS);
    $contador = $adicionais->rowCount();
    $totalPrincipal =  ( ($dadosGerais['valorp'] * $dadosGerais['qtdpax']) +
        ( ($dadosGerais['valorp']/ 2) * $dadosGerais['qtdchild'] ) );

    while ($calculcar = $dadosReserva->fetch(PDO::FETCH_ASSOC))
    {
        $totalSecundario = $totalSecundario +  ( ($calculcar['valors'] * $calculcar['qpax']) +
                ( ($calculcar['valors']/ 2) * $calculcar['qchild'] ) );
    }
    $total = $totalPrincipal;
    $financeiroReserva = $pdo->prepare(
        "SELECT tarifa, valuecredit as credito, valueagente as agente, valueguia as guia, `name` FROM `ct_createfaturacredit`
    cfc left join `ct_currentaccount` cc on cfc.idaccountcurrent = cc.id where numbervoucher = '$numberVoucher' ");
    $financeiroReserva->execute();
    
    while ($dados_fi = $financeiroReserva->fetch( PDO::FETCH_ASSOC ))
    {
        $dadosFinanceiro['credito'] += $dados_fi['credito'];
        $dadosFinanceiro['agente'] += $dados_fi['agente'];
        $dadosFinanceiro['guia'] += $dados_fi['guia'];
        $dadosFinanceiro['name'] = $dados_fi['name'];
    }
}
if( isset( $_POST['atualizarfatura'] ) )
{
    $id             = $_POST['idfatura'];
    $dataPagamento  = $_POST['datapagamento'];
    $dataVencimento = $_POST['datavencimento'];
    $numeracao      = $_POST['observacao'];
    $conta          = $_POST['formapagamento'];
    $numberVoucher  = $_POST['voucher'];

    $updateFatura   = $pdo->prepare(
        'update `ct_createfatura` set `datematurity` = :vencimento, `datepayment` = :pagamento, `numberadd` = :numeracao,
 `idcurrentaccount` = :conta where id = :id ');
    $updateFatura->execute(array(":vencimento" => $dataVencimento, ":pagamento" => $dataPagamento, ":numeracao" => $numeracao, ":conta" => $conta, ":id" => $id));

    $dadosAuditoria = $pdo->prepare(
        'insert into `ct_audit` (`id`, `idresponsible`, `voucher`, `description`, `date`) values (DEFAULT, :idres, :vou, :descr, :dat) ');
    $dadosAuditoria->execute(
        array(
            ":idres" =>  $_SESSION['id'],
            ":vou"   => $numberVoucher,
            ":descr" => "Atualizou  a fatura da reserva para os seguintes dados: Data Pagamento".date("d-m-Y", strtotime($dataPagamento))."Obs: ".$numeracao.".",
            ":dat"   => date("Y-m-d H:i:s")
        )
    );
    $totalSecundario = 0;
    $numberVoucher = $_POST['voucher'];
    $dadosReserva = $pdo->prepare(
        "SELECT r.id, r.valueservice as valorp, r.qtdpax, photoresident ,r.qtdchild, ra.qpax, ra.qchild, ra.valueservice as valors, c.fullname as cliente, r.pax,
                          r.documento,se.fullname as servico, c.fullname as cliente, namepayment, nameinvoice, r.numberfatura, r.dateinput FROM `ct_reserva` r
                  left join ct_cliente c on c.id = r.idcliente left join ct_responsavel re on re.id = r.idresponsavel
                  left join ct_status s on s.id = r.idstatus left join ct_guia g on g.id = r.idguia join ct_servico se on se.id = r.idservico
                  left join ct_agentes as ag on r.idagente = ag.id left join ct_statusinvoice sti on sti.id = r.idstatusinvoice
                  left join ct_recentlyadd ra on ra.idrecently = r.id left join ct_service_schedule ss on ss.idshedule = r.idhorario
                  left join `ct_form_of_ payment` as cfp on cfp.id = r.idpayment where `numbervoucher` = :numbervoucher");
    $dadosReserva->execute( array(":numbervoucher" => $numberVoucher ));
    $dadosGerais = $dadosReserva->fetch(PDO::FETCH_ASSOC);

    $contador = $dadosReserva->rowCount();

    $administrativo = $pdo->prepare(
        'select c.id, c.datematurity, c.datepayment, c.numberadd, cc.name from `ct_createfatura` c left join ct_currentaccount cc on cc.id = c.idcurrentaccount
                    where numbervoucher = :voucher');
    $administrativo->execute(array(":voucher" => $numberVoucher));
    $contadorAdm = $administrativo->rowCount();
    $registroAdm = $administrativo->fetchAll(PDO::FETCH_CLASS);

    $adicionais = $pdo->prepare(
        'SELECT r.pax, ra.dateinput as ap, s.fullname, qpax, qchild, qfree, ra.valueservice, ra.horaap, ra.documento, c.fullname as cliente, ra.dateinput
                FROM `ct_recentlyadd` ra left join `ct_reserva` r on r.id = ra.idrecently
                      left join ct_servico s on s.id = ra.idservice left join ct_service_schedule ss on ss.idshedule = ra.idschedule
                      left join ct_cliente c on c.id = r.idcliente where r.id = :id order by ap');
    $adicionais->execute(array(":id" => $dadosGerais['id'] ) );
    $registro = $adicionais->fetchAll(PDO::FETCH_CLASS);
    $contador = $adicionais->rowCount();
    $totalPrincipal =  ( ($dadosGerais['valorp'] * $dadosGerais['qtdpax']) +
        ( ($dadosGerais['valorp']/ 2) * $dadosGerais['qtdchild'] ) );

    while ($calculcar = $dadosReserva->fetch(PDO::FETCH_ASSOC))
    {
        $totalSecundario = $totalSecundario +  ( ($calculcar['valors'] * $calculcar['qpax']) +
                ( ($calculcar['valors']/ 2) * $calculcar['qchild'] ) );
    }
    $total = $totalPrincipal;
    $financeiroReserva = $pdo->prepare(
        "SELECT tarifa, valuecredit as credito, valueagente as agente, valueguia as guia, `name` FROM `ct_createfaturacredit`
    cfc left join `ct_currentaccount` cc on cfc.idaccountcurrent = cc.id where numbervoucher = '$numberVoucher' ");
    $financeiroReserva->execute();
    
    while ($dados_fi = $financeiroReserva->fetch( PDO::FETCH_ASSOC ))
    {
        $dadosFinanceiro['credito'] += $dados_fi['credito'];
        $dadosFinanceiro['agente'] += $dados_fi['agente'];
        $dadosFinanceiro['guia'] += $dados_fi['guia'];
        $dadosFinanceiro['name'] = $dados_fi['name'];
    }
    echo ('<div class="alert alert-success" role="alert">Fatura Atualizada.</div>');
}
if( isset($_POST['excluirfatura']) )
{
    $id             = $_POST['idfatura'];
    $numberVoucher  = $_POST['voucher'];
    $deleteForever  = $pdo->prepare('delete from `ct_createfatura` where id = :id ');
    $deleteForever->execute( array(":id" => $id) );

    $dadosAuditoria = $pdo->prepare(
        'insert into `ct_audit` (`id`, `idresponsible`, `voucher`, `description`, `date`) values (DEFAULT, :idres, :vou, :descr, :dat) ');
    $dadosAuditoria->execute(
        array(
            ":idres" =>  $_SESSION['id'],
            ":vou"   => $numberVoucher,
            ":descr" => "Excluiu a Fatura da Reserva",
            ":dat"   => date("Y-m-d H:i:s")
        )
    );
    $totalSecundario = 0;
    $dadosReserva = $pdo->prepare(
        "SELECT r.id, r.valueservice as valorp, r.qtdpax, photoresident ,r.qtdchild, ra.qpax, ra.qchild, ra.valueservice as valors, c.fullname as cliente, r.pax,
                          r.documento,se.fullname as servico, c.fullname as cliente, namepayment, nameinvoice, r.numberfatura, r.dateinput FROM `ct_reserva` r
                  left join ct_cliente c on c.id = r.idcliente left join ct_responsavel re on re.id = r.idresponsavel
                  left join ct_status s on s.id = r.idstatus left join ct_guia g on g.id = r.idguia join ct_servico se on se.id = r.idservico
                  left join ct_agentes as ag on r.idagente = ag.id left join ct_statusinvoice sti on sti.id = r.idstatusinvoice
                  left join ct_recentlyadd ra on ra.idrecently = r.id left join ct_service_schedule ss on ss.idshedule = r.idhorario
                  left join `ct_form_of_ payment` as cfp on cfp.id = r.idpayment where `numbervoucher` = :numbervoucher");
    $dadosReserva->execute( array(":numbervoucher" => $numberVoucher ));
    $dadosGerais = $dadosReserva->fetch(PDO::FETCH_ASSOC);

    $contador = $dadosReserva->rowCount();

    $administrativo = $pdo->prepare(
        'select c.id, c.datematurity, c.datepayment, c.numberadd, cc.name from `ct_createfatura` c left join ct_currentaccount cc on cc.id = c.idcurrentaccount
                    where numbervoucher = :voucher');
    $administrativo->execute(array(":voucher" => $numberVoucher));
    $contadorAdm = $administrativo->rowCount();
    $registroAdm = $administrativo->fetchAll(PDO::FETCH_CLASS);

    $adicionais = $pdo->prepare(
        'SELECT r.pax, ra.dateinput as ap, s.fullname, qpax, qchild, qfree, ra.valueservice, ra.horaap, ra.documento, c.fullname as cliente, ra.dateinput
                FROM `ct_recentlyadd` ra left join `ct_reserva` r on r.id = ra.idrecently
                      left join ct_servico s on s.id = ra.idservice left join ct_service_schedule ss on ss.idshedule = ra.idschedule
                      left join ct_cliente c on c.id = r.idcliente where r.id = :id order by ap');
    $adicionais->execute(array(":id" => $dadosGerais['id'] ) );
    $registro = $adicionais->fetchAll(PDO::FETCH_CLASS);
    $contador = $adicionais->rowCount();
    $totalPrincipal =  ( ($dadosGerais['valorp'] * $dadosGerais['qtdpax']) +
        ( ($dadosGerais['valorp']/ 2) * $dadosGerais['qtdchild'] ) );

    while ($calculcar = $dadosReserva->fetch(PDO::FETCH_ASSOC))
    {
        $totalSecundario = $totalSecundario +  ( ($calculcar['valors'] * $calculcar['qpax']) +
                ( ($calculcar['valors']/ 2) * $calculcar['qchild'] ) );
    }
    $total = $totalPrincipal;
    $financeiroReserva = $pdo->prepare(
        "SELECT tarifa, valuecredit as credito, valueagente as agente, valueguia as guia, `name` FROM `ct_createfaturacredit`
    cfc left join `ct_currentaccount` cc on cfc.idaccountcurrent = cc.id where numbervoucher = '$numberVoucher' ");
    $financeiroReserva->execute();
    
    while ($dados_fi = $financeiroReserva->fetch( PDO::FETCH_ASSOC ))
    {
        $dadosFinanceiro['credito'] += $dados_fi['credito'];
        $dadosFinanceiro['agente'] += $dados_fi['agente'];
        $dadosFinanceiro['guia'] += $dados_fi['guia'];
        $dadosFinanceiro['name'] = $dados_fi['name'];
    }
    echo ("<div style='margin-top: 100px;' class='alert alert-danger' role='alert'>Fatura Excluida</div>");

}
if( isset($_POST['pax']) )
{
    $reservas = $pdo->prepare(
            'select r.pax, r.numbervoucher, r.qtdpax, r.qtdchild, r.qtdfree, u.firstname, c.fullname, s.fullname as 	, r.dateinput 
                      from `ct_reserva` r left join `ct_usuario` u on r.idresponsavel = u.id left join ct_servico s on r.idservico = s.id
                      left join `ct_cliente` c on r.idcliente = c.id where pax like :nome ');
    $reservas->execute( array(":nome" => "%".$_POST['pax']."%") );
    $all_reservas = $reservas->fetchAll(PDO::FETCH_CLASS);
}
$totais = 0;
?>
<title>Minha Página</title>
    <style>
        body {
            background-color: #f5f5f5;
        }
    </style>
</head>
    <style>
    <style>
        .col-lg-6{margin-top: 20px;}
    </style>
    <!-- PAGE CONTENT-->
    <div class="page-content--bgf7">
    <!-- BREADCRUMB-->
    <section class="au-breadcrumb2">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="au-breadcrumb-content">
                        <div class="au-breadcrumb-left">
                            <span class="au-breadcrumb-span">Navegação:</span>
                            <ul class="list-unstyled list-inline au-breadcrumb__list">
                                <li class="list-inline-item active">
                                    <a href="index">Home</a>
                                </li>
                                <li class="list-inline-item seprate">
                                    <span>/</span>
                                </li>
                                <li class="list-inline-item">Reserva: Pesquisar Reserva</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- END BREADCRUMB-->

    <div class="">
        <div class="">
            <div class="card card-outline-primary">
                <div class="card-body">
                    <div class="col-lg-12">
                        <h4>Ordem de Serviço</h4>
                        <hr>
                        <div class="col-lg-6 pull-left">
                            <form method="post"  action="">
                                <div class="form-group ">
                                    <label for="voucher">Número do Voucher </label>
                                    <input type="text" name="voucher" id="voucher" class="form-control" >
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary btn-lg btn-block" style="background-color: #1e4770;">Buscar</button>
                                </div>
                            </form><br>
                        </div>
                        <div class="col-lg-6 pull-right">
                            <form method="post"  action="">
                                <div class="form-group ">
                                    <label for="pax">Nome do Passageiro</label>
                                    <input type="text" name="pax" id="pax" class="form-control" >
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary btn-lg btn-block" style="background-color: #1e4770;">Buscar</button>
                                </div>
                            </form><br>
                        </div>
                        <?php if( isset( $_POST['excluirfatura01'] ) ){ ?>
                            <div class="modal fade" id="exemplomodal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                                <div class="modal-dialog modal-lg" role="document">
                                    <form action="" method="post">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h4 class="modal-title" id="gridSystemModalLabel"><?php echo(" Voucher ".$numberVoucher) ?></h4>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">X</span></button>

                                            </div>
                                            <div class="modal-body">
                                                <div class="container">
                                                   <p>Deseja realmente excluir ?</p>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <input name="idfatura" type="hidden" value="<?php echo($_POST['idfatura']); ?>" >
                                                <input type="hidden" name="voucher" id="voucher" value="<?php echo($_POST['voucher']); ?>" >
                                                <button type="submit" class="btn btn-success pull-left" name="excluirfatura">Sim</button>
                                                <button type="button" class="btn btn-outline-warning pull-right" data-dismiss="modal" aria-label="Close" >Não</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php }?>
                        <?php if( !empty($numberVoucher) and !empty($dadosGerais) and isset($_POST['voucher']) ){ ?>
                            <h4>Dados da Reserva - voucher <?php echo($numberVoucher); ?> </h4>
                            <hr>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>AGÊNCIA</th>
                                            <th>PAX</th>
                                            <th>COMPLEMENTO</th>
                                            <th>PAX|CH</th>
                                            <th>SERVIÇO</th>
                                            <th>EMBARQUE</th>
                                            <th>PAGAMENTO</th>
                                            <?php if(!empty($_SESSION['idfaturador']) or  !empty($_SESSION['idgerente'])){ ?>
                                                <th>STATUS</th>
                                            <?php }?>
                                            <th>VALOR</th>
                                            <th>TOTAL CRÉDITO</th>
                                            <th>TOTAL DESPESA</th>
                                            <?php if($dadosGerais['numberfatura'] > 0 ){ ?>
                                                <th>Nº FATURA</th>
                                            <?php }?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php if( $dadosGerais['servico'] == 'AEROPORTO / MORRO (SEMI - TERRESTRE).' or
                                        $dadosGerais['servico'] == 'TERMINAL SSA / MORRO ( SEMI - TERRESTRE ).' or $dadosGerais['servico'] == 'CASSI COMERCIO / MORRO' or
                                        $dadosGerais['servico'] == 'TRF - SEMI TERRESTRE AERO / MORRO' or  $dadosGerais['servico'] == 'TRF - HTL / MSP'){ ?>
                                        <tr><td colspan="12" style="font-weight: bold; text-align: center;">IDA:</td></tr>
                                    <?php }?>
                                        <tr>
                                            <td><?php echo( ( $dadosGerais['cliente'] ) ); ?></td>
                                            <td><?php echo( utf8_encode($dadosGerais['pax']) ); ?></td>
                                            <td><?php echo( $dadosGerais['documento'] ); ?></td>
                                            <td><?php echo( $dadosGerais['qtdpax']."/".$dadosGerais['qtdchild'] ); ?></td>
                                            <td><?php echo utf8_encode($dadosGerais['servico']); ?></td>
                                            <td><?php echo( date("d/m/Y", strtotime( $dadosGerais['dateinput'] )) ); ?></td>
                                            <?php if(count($dadosFinanceiro['name']) > 0){ ?>
                                                <td><?php echo( $dadosFinanceiro['name'] ); ?></td>
                                            <?php } else {?>
                                                <td>Falta Pagar</td>
                                            <?php }?>
                                            <?php if(!empty($_SESSION['idfaturador'] or  $_SESSION['idgerente'])){ ?>
                                                <td><?php echo( $dadosGerais['nameinvoice'] ); ?></td>
                                            <?php }?>

                                            <td><?php echo("R$ ".number_format($total, 2, ",", ".")); ?></td>
                                            <td><?php echo("R$ ".number_format($dadosFinanceiro['credito'], 2, ",", ".") ); ?></td>
                                            <td>
                                                <?php
                                                    echo("R$ ".number_format($dadosFinanceiro['agente']+$dadosFinanceiro['guia'],
                                                        2, ",", ".") ); ?>
                                            </td>
                                            <?php if($dadosGerais['numberfatura'] > 0){ ?>
                                                <td><?php echo($dadosGerais['numberfatura']); ?></td>
                                            <?php }?>
                                        </tr>
                                    <?php if($contador > 0) { ?>
                                        <?php foreach ($registro as $item){
                                            $timestampAdd = strtotime( $item->ap );
                                            $totalAdd = $totalAdd + ( ( $item->valueservice * $item->qpax ) + (  ($item->valueservice / 2) * $item->qchild ) );
                                            $valorSub =  ( ( $item->valueservice * $item->qpax ) + (  ($item->valueservice / 2) * $item->qchild ) );
                                            $totais += $valorSub;
                                            ?>
                                            <?php if( $item->fullname == 'MORRO / HOTEL'or$item->fullname == 'MORRO / SALVADOR (SEMI - TERRESTRE).'
                                                or $item->fullname == 'MORRO / TERMINAL SSA (SEMI - TERRESTRE).' or  $item->fullname == 'MORRO / HOTEL SSA (SEMI - TERRESTRE).'
                                                or  $item->fullname == 'MORRO / AEROPORTO SSA (SEMI - TERRESTRE).' or  $item->fullname == 'MORRO / HOTEL DEPOIS RIO VERMELHO SSA (SEMI - TERRESTRE).'){ ?>
                                                <tr><td colspan="12" style="font-weight: bold; text-align: center;">VOLTA:</td></tr>
                                            <?php }?>
                                            <tr>
                                                <td><?php echo(( $item->cliente )); ?></td>
                                                <td><?php echo(strtoupper($item->pax)); ?></td>
                                                <td><?php echo( strtoupper( $item->documento ) ); ?></td>
                                                <td><?php echo( utf8_decode( $item->qpax."/".$item->qchild ) ); ?></td>
                                                <td><?php echo($item->fullname); ?></td>
                                                <td><?php echo( date("d/m/Y", strtotime( $item->dateinput )) ); ?></td>
                                                <?php if(count($dadosFinanceiro['name']) > 0){ ?>
                                                    <td><?php echo( $dadosFinanceiro['name'] ); ?></td>
                                                <?php } else {?>
                                                    <td>Falta Pagar</td>
                                                <?php }?>
                                                <?php if(!empty($_SESSION['idfaturador'] or  $_SESSION['idgerente'])){ ?>
                                                    <td><?php echo( $dadosGerais['nameinvoice'] ); ?></td>
                                                <?php }?>
                                                <td><?php echo("R$ ".number_format($valorSub,2,",",".") ); ?></td>
                                                <td>-</td>
                                                <td>-</td>
                                            </tr>
                                        <?php }?>
                                    <?php }?>
                                        <tr>
                                            <td colspan="4"></td>
                                            <td colspan="4"></td>
                                            <td colspan="4" style="font-weight: bold; text-align: center;">
                                                <?php echo("TOTAL DO SERVIÇO: R$ ".number_format($totais+$total, 2, ",", ".")); ?>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table><br><br>
                                <?php if($contadorAdm > 0){ ?>
                                    <h4>Dados Administrativos</h4>
                                    <hr>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                            <tr>
                                                <th>Data de Vencimento</th>
                                                <th>Data de Pagamento</th>
                                                <th>Observação</th>
                                                <th>Forma de Pagamento</th>
                                                <th>#</th>
                                                <th>#</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php foreach( $registroAdm as $item5) { ?>
                                                <tr>
                                                    <td><?php echo( date('d-m-Y', strtotime($item5->datematurity)) ); ?></td>
                                                    <td><?php echo( date('d-m-Y', strtotime($item5->datepayment)) ); ?></td>
                                                    <td><?php echo( utf8_encode($item5->numberadd) ); ?></td>
                                                    <td><?php echo( utf8_encode($item5->name) ); ?></td>
                                                    <td>
                                                        <form action="" method="post">
                                                            <input name="idfatura" type="hidden" value="<?php echo($item5->id); ?>" >
                                                            <input name="voucher" type="hidden" value="<?php echo($numberVoucher); ?>" >
                                                            <button type="submit" name="adm" style="background: transparent;">Editar</button>
                                                        </form>
                                                    </td>
                                                    <td>
                                                        <form action="" method="post">
                                                            <input name="idfatura" type="hidden" value="<?php echo($item5->id); ?>" >
                                                            <input name="voucher" type="hidden" value="<?php echo($numberVoucher); ?>" >
                                                            <button type="submit" name="excluirfatura01" style="background: transparent;">Excluir</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php }?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php }?>
                                <?php if($contadorDados > 0) {?>
                                    <div class="col-lg-12" style="margin-top: 40px;">
                                        <div class="modal fade" id="exemplomodal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                                            <div class="modal-dialog modal-lg" role="document">
                                                <form action="" method="post">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h4 class="modal-title" id="gridSystemModalLabel"><?php echo(" Voucher ".$numberVoucher) ?></h4>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">X</span></button>

                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="container">
                                                                <div class="col-md-6 pull-left">
                                                                    <label for="datavencimento"><strong>Data de Vencimento</strong></label><br><br>
                                                                    <input name="datavencimento" id="datavencimento" type="date" value="<?php echo($dadosFatura['datematurity']); ?>" >
                                                                </div>
                                                                <div class="col-md-6 pull-left">
                                                                    <label for="datapagamento"><strong>Data de Pagamento</strong></label><br><br>
                                                                    <input name="datapagamento" id="datapagamento" type="date" value="<?php echo($dadosFatura['datepayment']); ?>" >
                                                                </div>
                                                                <div class="col-md-8 pull-left">
                                                                    <label for="observacao"><strong>Observação</strong></label><br><br>
                                                                    <input name="observacao" id="observacao" type="text" value="<?php echo($dadosFatura['numberadd']); ?>" >
                                                                </div>
                                                                <div class="col-md-4 pull-right">
                                                                    <label for="formapagamento"><strong>Forma de Pagamento</strong></label><br><br>
                                                                    <select class="form-control" name="formapagamento">
                                                                        <?php while ($infoAdm = $contaCorrente->fetch(PDO::FETCH_ASSOC) ){ ?>
                                                                            <?php  if($infoAdm['name'] == $dadosFatura['name']){ ?>
                                                                                <option selected value="<?php echo($infoAdm['id']); ?>">
                                                                                    <?php echo( utf8_encode( $infoAdm['name'])); ?>
                                                                                </option>
                                                                            <?php } else{?>
                                                                                <option  value="<?php echo($infoAdm['id']); ?>">
                                                                                    <?php echo(utf8_encode( $infoAdm['name'])); ?>
                                                                                </option>
                                                                            <?php }?>
                                                                        <?php }?>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <input name="idfatura" type="hidden" value="<?php echo($dadosFatura['id']); ?>" >
                                                            <input type="hidden" name="voucher" id="voucher" value="<?php echo($numberVoucher); ?>" >
                                                            <button type="submit" class="btn btn-success pull-right" name="atualizarfatura">
                                                                Atualizar Fatura
                                                            </button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php }?>
                                <div class="col-lg-6 pull-left">
                                    <form action="./relatorio/pdf-relatorio-voucher.php" target="_blank" method="post">
                                        <input type="hidden" name="voucher" id="voucher" value="<?php echo($numberVoucher); ?>" >
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-primary btn-lg btn-block" style="background-color: #1e4770;">Imprimir Voucher</button>
                                        </div>
                                    </form>
                                </div>
                                <div class="col-lg-6 pull-right">
                                    <form action="./editar-pax.php" target="_blank" method="post">
                                        <input type="hidden" name="numbervoucher" id="numbervoucher" value="<?php echo($numberVoucher); ?>" >
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-primary btn-lg btn-block" style="background-color: #1e4770;">Editar Reserva</button>
											<br>
											<br>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php } else { ?>
                            <?php if( isset($_POST['voucher']) ){ ?>
                                <div>
                                    Não encontramos reservas com o voucher<?php echo(" ".$_POST['voucher']); ?></div>
                            <?php }?>

                        <?php }?>
                        <?php if( isset($_POST['pax']) and count($all_reservas) > 0 ){ ?>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                    <tr>
                                        <th>VOUCHER</th>
                                        <th>PAX</th>
                                        <th>P|C|F</th>
                                        <th>RESPOSÁVEL</th>
                                        <th>CLIENTE</th>
                                        <th>SERVIÇO</th>
                                        <th>EMBARQUE</th>
                                        <th>ACESSAR</th>
                                    </tr>

                                    </thead>
                                    <tbody>
                                    <?php foreach ($all_reservas as $item){ ?>
                                        <tr>
                                            <td><?php echo( $item->numbervoucher ) ?></td>
                                            <td><?php echo( utf8_decode($item->pax) ) ?></td>
                                            <td><?php echo( $item->qtdpax."/".$item->qtdchild."/".$item->qtdfree ) ?></td>
                                            <td><?php echo( utf8_decode($item->firstname) ) ?></td>
                                            <td><?php echo( utf8_decode($item->fullname) ) ?></td>
                                            <td><?php echo( utf8_decode($item->servico) ) ?></td>
                                            <td><?php echo( date("d-m-Y", strtotime($item->dateinput)) ) ?></td>
                                            <td>
                                                <a target="_blank" href="<?php echo("editar-pax?numbervoucher=".$item->numbervoucher) ?>">Acessar Reserva</a>
                                            </td>
                                        </tr>
                                    <?php }?>

                                    </tbody>
                                </table>
                            </div>
                        <?php } else { ?>
                            <?php if( isset($_POST['pax']) ){ ?>
                                <div>
                                    Não encontramos reservas com o nome<?php echo(" ".$_POST['pax']); ?></div>
                            <?php }?>

                        <?php }?>
                    </div>
                </div>

            </div>

        </div>
    </div>
<?php require_once ('footer.php'); ?>
