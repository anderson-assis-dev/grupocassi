<?php
require_once 'header.php';
require_once __DIR__ . '/includes/ref_cache.php';
require_once __DIR__ . '/includes/audit.php';
require_once __DIR__ . '/includes/flash.php';
require_once __DIR__ . '/includes/pax_helpers.php';

$pdo->exec("set names utf8");
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require './../vendor/autoload.php';
$mail = new PHPMailer(true);
if(isset( $_POST['numbervoucher'] ))
{
    $numberVoucher = $_POST['numbervoucher'];
}else{
    $numberVoucher = $_GET['numbervoucher'];
}
$horarios = $pdo->prepare('select * from `ct_service_schedule` order by `schedule` ');
$horarios->execute();
$listaHorarios = $horarios->fetchAll(PDO::FETCH_CLASS);
$buscaCredito = $pdo->prepare(
    'SELECT cfc.id, valuecredit, `name`, datacredit, valueagente, dataagente FROM `ct_createfaturacredit` cfc left join `ct_currentaccount` cc
 on cfc.idaccountcurrent = cc.id where `numbervoucher` = :voucher');
$buscaCredito->execute( array(":voucher" => $numberVoucher ) );
$registroCredito = $buscaCredito->fetchAll(PDO::FETCH_CLASS);
$contadorCredito = $buscaCredito->rowCount();
$dadosReservaAu  = $pdo->prepare("select * from `ct_audit` where `voucher` = :numberVoucher ");
$dadosReservaAu->execute( array(":numberVoucher" => $numberVoucher ) );
$registroAu      =  $dadosReservaAu->fetchAll(PDO::FETCH_CLASS);
$contadorAuditoria = $dadosReservaAu->rowCount();
$administrativo = $pdo->prepare(
    'select c.id, c.datematurity, c.datepayment, c.numberadd, cc.name from `ct_createfatura` 
c left join ct_currentaccount cc on cc.id = c.idcurrentaccount where numbervoucher = :voucher');
$administrativo->execute(array(":voucher" => $numberVoucher));
$contadorAdm = $administrativo->rowCount();
$dadosReserva = $pdo->prepare(
    "SELECT r.id,pax, r.idempresa, documento,photoresident ,dateinput, dateoutput, photoresident, c.fullname as cliente, s.fullname as `status`, r.valueservice, se.fullname as serivco, ag.fullname as agente, namepayment, g.id as guia, qtdpax, qtdchild, qtdfree, ss.schedule,numbervoucher, r.idstatusinvoice, r.abertura, firstname,  r.horaap, r.idcliente,
               r.idresponsavel, r.totalservico, r.confirmacao, r.numberfatura, r.fl_altarar_valor_servico, r.identificacao_mala, r.incluirtaxamala, r.qntpessoataxamala FROM `ct_reserva` r left join ct_cliente c on c.id = r.idcliente left join ct_responsavel re on re.id = r.idresponsavel left join ct_status s on s.id = r.idstatus left join ct_guia g on g.id = r.idguia join ct_servico se on se.id = r.idservico left join ct_agentes as ag on r.idagente = ag.id
               left join ct_usuario us on us.id = r.idresponsavel left join ct_service_schedule ss on ss.idshedule = r.idhorario left join `ct_form_of_ payment` as cfp on cfp.id = r.idpayment  where `numbervoucher` = :numbervoucher");
$dadosReserva->execute( array(":numbervoucher" => $numberVoucher ));
$dadosGerais = $dadosReserva->fetch(PDO::FETCH_ASSOC);
$adicionais = $pdo->prepare(
    'SELECT ra.id,ra.dateinput as ap, s.fullname,s.screenplay, ss.schedule, qpax, qchild, qfree, ra.dateinput, ra.dateoutput, ra.valueservice, ra.horaap, ra.documento, s.id as idservico,ra.confirmacao2, ra.fl_altarar_valor_servico
               FROM `ct_recentlyadd` ra left join `ct_reserva` r on r.id = ra.idrecently
               left join ct_servico s on s.id = ra.idservice left join ct_service_schedule ss
               on ss.idshedule = ra.idschedule where r.id = :id');
$adicionais->execute(array(":id" => $dadosGerais['id'] ) );
$registro = $adicionais->fetchAll(PDO::FETCH_CLASS);
$contador = $adicionais->rowCount();
$todosCliente    = refClientes($pdo);
$listaEmpresas   = refEmpresasTodas($pdo);
$listaServicos   = refServicos($pdo);
$listaPagamentos = refPagamentos($pdo);

//lista status
$status = $pdo->prepare('select id,fullname as situacao from `ct_status` ');
$status->execute();
$listaStatus = $status->fetchAll(PDO::FETCH_CLASS);
$comissoes = $pdo->prepare(" select  * from `ct_createfaturacredit` where numbervoucher = :voucher and dataagente <> '0000-00-00 00:00:00' ");
$comissoes->execute(array(":voucher" => $numberVoucher));
$despesa = $comissoes->fetchAll(PDO::FETCH_CLASS);
$contadorDespesa = $comissoes->rowCount();
$contaCorrente = $pdo->prepare('SELECT * FROM `ct_currentaccount` ');
$contaCorrente->execute();
$registroCc = $contaCorrente->fetchAll(PDO::FETCH_CLASS);

$planoContas = $pdo->prepare('SELECT * FROM `ct_planaccounts` ');
$planoContas->execute();
$registroPlan = $planoContas->fetchAll(PDO::FETCH_CLASS);
$statusInvoice = $pdo->prepare('select * from `ct_statusinvoice` where `id` >= :maior and `id` <= :menor');
$statusInvoice->execute(array(":maior" => 6, ":menor" => 7));
$registroStI = $statusInvoice->fetchAll(PDO::FETCH_CLASS);
$credito_debito_all = $pdo->prepare('select * from  `ct_credito_deb_reserva` where `numbervoucher` = :voucher ');
$credito_debito_all->execute( array(":voucher" => $numberVoucher));
$registro_credito_debito = $credito_debito_all->fetchAll(PDO::FETCH_CLASS);


$data_total_servico = ($dadosGerais['valueservice'] * $dadosGerais['qtdpax'] + (($dadosGerais['valueservice'] / 2) * $dadosGerais['qtdchild']));
$busta_total_servico2 = $pdo->prepare('SELECT sum(valueservice * qpax +((valueservice/2) * qchild)) as total2 FROM `ct_recentlyadd` where idrecently = :id');
$busta_total_servico2->execute(array(":id"=> $dadosGerais['id']));
$data_total2 = $busta_total_servico2->fetch(PDO::FETCH_ASSOC);

$buscaCredito1 = $pdo->prepare(
    'SELECT sum(valuecredit) as totalpago FROM `ct_createfaturacredit`  where `numbervoucher` = :voucher');
$buscaCredito1->execute( array(":voucher" => $numberVoucher ) );
$registroCredito1 = $buscaCredito1->fetchAll(PDO::FETCH_CLASS);

//$updateReserva1 = $pdo->prepare("UPDATE `ct_reserva` SET `totalservico` = :totalservico,`totalcredito` = :totalcredito WHERE `ct_reserva`.`numbervoucher` = :nv ");
//$updateReserva1->execute(array(":totalservico" => $data_total_servico+$data_total2['total2'], ":totalcredito" => $registroCredito1['totalpago'] ,":nv" => $numberVoucher ));
$buscarResponsavel_todos = $pdo->prepare('select * from `ct_usuario` where bloqueado = 0 order by firstname');
$buscarResponsavel_todos->execute();
$dados_buscarResponsavel_todos = $buscarResponsavel_todos->fetchAll(PDO::FETCH_CLASS);
if( isset($_POST['adm'] ) )
{
    $idfatura      = $_POST['idfatura'];
    $dadosAdm      = $pdo->prepare(
        'select c.id, c.datematurity, c.datepayment, c.numberadd, cc.name from `ct_createfatura` 
                  c left join ct_currentaccount cc on cc.id = c.idcurrentaccount  where c.id = :id');
    $dadosAdm->execute(array(":id" => $idfatura));
    $dadosFatura  = $dadosAdm->fetch(PDO::FETCH_ASSOC);
    $contadorDados = $dadosAdm->rowCount();
    header('location: editar-pax?numbervoucher='.$_POST['voucher']);
}
if( isset($_POST['atualizarfatura'] ) )
{


    $id             = $_POST['idfatura'];
    $dataPagamento  = $_POST['datapagamento'];
    $dataVencimento = $_POST['datavencimento'];
    $numeracao      = $_POST['observacao'];
    $conta          = $_POST['formapagamento'];
    $numberVoucher = $_POST['voucher'];

    $updateFatura = $pdo->prepare(
        'update `ct_createfatura` set `datematurity` = :vencimento, `datepayment` = :pagamento, `numberadd` = :numeracao,
 `idcurrentaccount` = :conta where id = :id ');
    $updateFatura->execute(array(":vencimento" => $dataVencimento, ":pagamento" => $dataPagamento, ":numeracao" => $numeracao, ":conta" => $conta, ":id" => $id));

    logAudit($pdo, $numberVoucher,
            "Atualizou  a fatura da reserva para os seguintes dados: Data Pagamento" .
                date("d-m-Y", strtotime($dataPagamento)) . "Obs: " . $numeracao . "."
        );


    header('location: editar-pax?numbervoucher='.$_POST['voucher']);
}
if( isset($_POST['excluirfatura']) ) {
    $id = $_POST['idfatura'];
    $deleteForever = $pdo->prepare('delete from `ct_createfatura` where id = :id ');
    $deleteForever->execute(array(":id" => $id));

    logAudit($pdo, $_POST['voucher'],
            "Excluiu a fatura da reserva"
        );

    header('location: editar-pax?numbervoucher='.$_POST['voucher']);
}
if( isset($_POST['atualizarreserva']) )
{
    $voucher          = $_POST['voucher'];
    $nomePax = addslashes(trim($_POST['pax']));
    $documento        = addslashes( $_POST['documento'] );
    $quantidadePax    = addslashes( $_POST['quantidadepax'] );
    $quantidadeChild  = addslashes( $_POST['quantidadechild'] );
    $quantidadeFree   = addslashes( $_POST['quantidadefree'] );
    $dataInicio       = addslashes( $_POST['datainicio'] );
    $dataFim          = addslashes( $_POST['datainicio'] );
    $identificacaoMala= addslashes( $_POST['identificacao_mala'] );
    $incluirtaxamala  = addslashes( isset($_POST['incluirtaxamala']) ? 1 : 0 );
    $qntpessoataxamala= addslashes( (INT)$_POST['qntpessoataxamala'] );
    $novostatus       = $_POST['status'];
    $valor1  = str_replace(".", "", $_POST['valueservice']);
    $valor   = str_replace(",", ".", $valor1);

    $valor2   = str_replace(".", "", $_POST['valor2']);
    $valor3   = str_replace(",", ".", $valor2);


    $horaApanha       = $_POST['horariobusca'];
    $service          = addslashes( trim( $_POST['service']  ) );
    $payment          = addslashes( trim( $_POST['payment']  ) );
    $schedule         = addslashes( trim( $_POST['schedule'] ) );
    $id_cliente       = $_POST['cliente'];
    $idempresa       = $_POST['idempresa'];
    $dadosReservaAu  = $pdo->prepare("select * from `ct_audit` where `voucher` = :numberVoucher ");
    $dadosReservaAu->execute( array(":numberVoucher" => $voucher ) );
    $registroAu      =  $dadosReservaAu->fetchAll(PDO::FETCH_CLASS);
    $contadorAuditoria = $dadosReservaAu->rowCount();

    $total_servico = $valor * $quantidadePax + (($valor / 2) * $quantidadeChild);
    $tel = $_POST['telefone'];
   
    $canUpdateIdCliente = $_SESSION['idgerente'] == 2 ? "`idcliente` = $id_cliente, " : null;
    $canUpdateIdEmpresa = "`idempresa` = $idempresa, ";
    $updateReserva1 = $pdo->prepare(
        "UPDATE `ct_reserva` SET `dateinput` = '$dataInicio', `dateoutput` = '$dataFim', `idhorario` = $schedule, `documento` = '$documento', `idstatus` = $novostatus, `pax` = '$nomePax', `valueservice` = '$valor', `horaap` = '$horaApanha', `idpayment` = 1, `qtdpax` = $quantidadePax, `qtdchild` = $quantidadeChild, `qtdfree` = $quantidadeFree, `idservico` = $service, $canUpdateIdEmpresa $canUpdateIdCliente `photoresident` = '$tel'
            , `identificacao_mala` = '$identificacaoMala', `incluirtaxamala` = '$incluirtaxamala', qntpessoataxamala = '$qntpessoataxamala'
                   WHERE `ct_reserva`.`numbervoucher` = '$voucher' ");
    $updateReserva1->execute();
    $sql = "update ct_reserva set data_alteracao = now() where numbervoucher = '".$voucher."'";   
    $updateData = $pdo->prepare($sql);
    $updateData->execute();    
    if( $novostatus == 2 )
    {
        $atualizarStatus = $pdo->prepare(
            'update `ct_reserva` set `idstatusinvoice` = :sinvoice where numbervoucher = :voucher ');
        $atualizarStatus->execute( array(
            ":sinvoice" => $novostatus,
            ":voucher"  => $voucher

        ) );
    }
    elseif($novostatus == 4){
        $atualizarStatus = $pdo->prepare(
            'update `ct_reserva` set `idstatusinvoice` = :sinvoice where numbervoucher = :voucher ');
        $atualizarStatus->execute( array(
            ":sinvoice" => 8,
            ":voucher"  => $voucher

        ) );
    }
    elseif($novostatus == 3){
        $atualizarStatus = $pdo->prepare(
            'update `ct_reserva` set `idstatusinvoice` = :sinvoice where numbervoucher = :voucher ');
        $atualizarStatus->execute( array(
            ":sinvoice" => 4,
            ":voucher"  => $voucher

        ) );
    }


    $nameSearchService = $pdo->prepare("select * from `ct_servico` where id = :id ");
    $nameSearchService->execute( array(":id" => $service) );
    $searchData = $nameSearchService->fetch(PDO::FETCH_ASSOC);

    $nameStatus = $pdo->prepare('select * from `ct_status` where id = :id');
    $nameStatus->execute( array(":id" => $novostatus ) );
    $dadosStatus = $nameStatus->fetch(PDO::FETCH_ASSOC);

    logAudit($pdo, $_POST['voucher'],
            "A reserva de ".$nomePax." foi atualizada para as seguintes informações: Data de Embarque Inicial: "
                .date("d-m-Y", strtotime($dataInicio))."<strong> Data de Embarque Final:</strong>  ".date("d-m-Y", strtotime($dataFim)).
                " <strong> Complemento: </strong> ".$documento."<strong> Valor do Serviço R$ </strong> ".$valor."<strong> Horário de Apanha:</strong>  ".$horaApanha.
                " <strong> Adultos: </strong> ".$quantidadePax." <strong>Crianças:</strong> ".$quantidadeChild." <strong> Gratuitos: </strong> ".$quantidadeFree.
                " <strong> Serviço atual: </strong> ".$searchData['fullname'].
                " <strong> Status: </strong> ".$dadosStatus['fullname']." Telefone: ".$_POST['telefone']
        );
    if( $_POST['clienteatual'] <> $id_cliente )
    {
        $cliente_atual = $pdo->prepare('select * from `ct_cliente` where `id` = :id ');
        $cliente_atual->execute(array(":id" => $_POST['clienteatual']));
        $nome_atual_cliente = $cliente_atual->fetch(PDO::FETCH_ASSOC);

        $cliente_novo = $pdo->prepare('select * from `ct_cliente` where `id` = :id ');
        $cliente_novo->execute(array(":id" => $id_cliente));
        $nome_novo_cliente = $cliente_novo->fetch(PDO::FETCH_ASSOC);


        logAudit($pdo, $_POST['voucher'],
            "Atualizou o cliente do voucher. De: ".$nome_atual_cliente['fullname']." Para: ".$nome_novo_cliente['fullname']
        );
    }

    if( $_POST['empresaatual'] <> $idempresa )
    {
        $cliente_atual = $pdo->prepare('select * from `ct_empresa` where `id` = :id ');
        $cliente_atual->execute(array(":id" => $_POST['empresaatual']));
        $nome_atual_cliente = $cliente_atual->fetch(PDO::FETCH_ASSOC);

        $cliente_novo = $pdo->prepare('select * from `ct_empresa` where `id` = :id ');
        $cliente_novo->execute(array(":id" => $idempresa));
        $nome_novo_cliente = $cliente_novo->fetch(PDO::FETCH_ASSOC);


        logAudit($pdo, $_POST['voucher'],
            "Atualizou a empresa do voucher. De: ".$nome_atual_cliente['fullname']." Para: ".$nome_novo_cliente['fullname']
        );
    }

    if( isset($_POST['incluirtaxa']) )
    {
        $buscarReservaId = $pdo->prepare("select * from `ct_reserva` where `numbervoucher` = :numbervoucher ");
        $buscarReservaId->execute(array(":numbervoucher" => $_POST['voucher']));
        $dadosReservaId = $buscarReservaId->fetch(PDO::FETCH_ASSOC);

        $adicionais = $pdo->prepare('SELECT * FROM `ct_recentlyadd` ra where ra.	idrecently = :id');
        $adicionais->execute(array(":id" => $dadosReservaId['id'] ) );
        $dados_add  = $adicionais->fetch(PDO::FETCH_ASSOC);
        $contador_add = $adicionais->rowCount();

        $vincularServicoVoucher = $pdo->prepare(
            'INSERT INTO `ct_recentlyadd` (`id`, `idrecently`, `idservice`, `documento` ,`valueservice` ,`idschedule`, `horaap`, `dateinput`, `dateoutput`,
                  `qpax`, `qchild`, `qfree`) VALUES   (DEFAULT, :reserva, :service, :docu ,:valor ,:hora, :horaap ,:di, :doo, :qp, :qc, :qf) ');
        $vincularServicoVoucher->execute(
            array(
                ":reserva" => $dadosReservaId['id'],
                ":service" => 19,
                ":docu"     => ".",
                ":valor"   => 20,
                ":hora"    => addslashes($schedule),
                ":horaap"  => addslashes($horaApanha),
                ":di"      => addslashes($dataInicio),
                ":doo"     => addslashes( $dataInicio),
                ":qp"      => addslashes( $quantidadePax),
                ":qc"      => addslashes( $quantidadeChild),
                ":qf"      => addslashes( $quantidadeFree)
            )
        );

        $nameSearchService = $pdo->prepare("select * from `ct_servico` where id = :id ");
        $nameSearchService->execute( array(":id" => 19 ) );
        $searchData = $nameSearchService->fetch(PDO::FETCH_ASSOC);

        logAudit($pdo, $_POST['voucher'],
            "Reserva vinculada com as seguintes informações:
                \n Embarque: ".date('d-m-Y', strtotime($dataInicio))." Apanha: ".$horaApanha." Adultos: ".$quantidadePax." Crianças: ".$quantidadeChild." Free: "
                    .$quantidadeFree." Serviço: ".$searchData['fullname']. " Complemento: "." Valor R$ 10,00 "
        );

        if($contador_add > 0)
        {
            $vincularServicoVoucher = $pdo->prepare(
                'INSERT INTO `ct_recentlyadd` (`id`, `idrecently`, `idservice`, `documento` ,`valueservice` ,`idschedule`, `horaap`, `dateinput`, `dateoutput`,
                  `qpax`, `qchild`, `qfree`) VALUES   (DEFAULT, :reserva, :service, :docu ,:valor ,:hora, :horaap ,:di, :doo, :qp, :qc, :qf) ');
            $vincularServicoVoucher->execute(
                array(
                    ":reserva" => $dadosReservaId['id'],
                    ":service" => 19,
                    ":docu"     => ".",
                    ":valor"   => 20,
                    ":hora"    => addslashes($dados_add['idschedule']),
                    ":horaap"  => addslashes($dados_add['horaap']),
                    ":di"      => addslashes($dados_add['dateinput']),
                    ":doo"     => addslashes($dados_add['dateinput']),
                    ":qp"      => addslashes( $dados_add['qpax']),
                    ":qc"      => addslashes( $dados_add['qchild']),
                    ":qf"      => addslashes( $dados_add['qfree'])
                )
            );

            $nameSearchService = $pdo->prepare("select * from `ct_servico` where id = :id ");
            $nameSearchService->execute( array(":id" => 19 ) );
            $searchData = $nameSearchService->fetch(PDO::FETCH_ASSOC);

            logAudit($pdo, $_POST['voucher'],
            "Reserva vinculada com as seguintes informações:
                \n Embarque: ".date('d-m-Y', strtotime($dados_add['dateinput']))." Apanha: ".$dados_add['horaap']." Adultos: ".$dados_add['qpax']." Crianças: ".$dados_add['qchild']." Free: "
                        . $dados_add['qfree']." Serviço: ".$searchData['fullname']. " Complemento: "." Valor R$ 10,00"
        );
        }
    }

    if(isset($_POST['confirmarhorarioembarque1']))
    {
        $totalPax = $quantidadeChild+$quantidadePax+$quantidadeFree;
        $confirmacao_horario_embarque = $pdo->prepare("update `ct_reserva` set `confirmacao` = :confirmacao where numbervoucher = :numbervoucher");
        $confirmacao_horario_embarque->execute(array(":confirmacao" => 1, ":numbervoucher" =>$voucher));

        $confirmar_embarque_por_operador = $pdo->prepare('insert into `ct_confirmacao` values (DEFAULT, :operador, :idhorario, :idservico ,:dataa, :total)');
        $confirmar_embarque_por_operador->execute(array(":operador" => $_SESSION['id'], ":idhorario" => $schedule, ":idservico" => $service ,":dataa" => date("Y-m-d"), ":total" => $totalPax));

        $buscar_horario = $pdo->prepare('select `schedule` from `ct_service_schedule` where idshedule = :idshedule');
        $buscar_horario->execute(array(":idshedule" =>$schedule));
        $dados_busca_horario = $buscar_horario->fetch(PDO::FETCH_ASSOC);


        logAudit($pdo, $voucher,
            "Confirmou o horário de embarque para ".$dados_busca_horario['schedule']." do serviço ".$searchData['fullname']
        );
    }
 
    if($valor3 <> $valor)
    {
        
        $confirmacao_horario_embarque = $pdo->prepare("update `ct_reserva` set `fl_altarar_valor_servico` = :confirmacao where `numbervoucher` = :numbervoucher");
        $confirmacao_horario_embarque->execute(array(":confirmacao" => 1, ":numbervoucher" => $voucher));

        logAudit($pdo, $voucher,
            "Alterou o valor do serviço de R$ ".number_format($_POST['valor2'],2)." para R$ ".$valor
        );
    }

    header('location: editar-pax?numbervoucher='.$_POST['voucher']);

}
if( isset($_POST['serviceadd']) )
{
    $idAdd            = $_POST['idAdd'];
    $idRserva         = $_POST['idreserva'];
    $dataInicio       = addslashes( $_POST['datainicio'] );
    $valor2           = str_replace(".","", $_POST['valueserviceadd']);
    $valor2           = str_replace(",",".", $valor2);
    $valor3           = str_replace(",",".", $_POST['valor2']);
    $service2         = addslashes( $_POST['serviceAdd2'] );
    $embarque         = $_POST['horarioembarque'];
    $documento2       = $_POST['documentoadd'];
    $horaApanha2      = $_POST['horaapadd'];
    $qtdpax           = $_POST['qpax'];
    $qchild           = $_POST['qchild'];
    $qfree            = $_POST['qfree'];

    $updateReserva2 = $pdo->prepare(
        'update `ct_recentlyadd` set `dateinput` = :inicio, `dateoutput` = :fim, `horaap` = :apanha, `documento` = :infoadd,
                       `idservice` = :servico, `valueservice` = :valor, `idschedule` = :embarque, `qpax` = :p, `qchild` = :c, `qfree` = :f where `id` = :id ');
    $updateReserva2->execute(
        array(
            ":inicio"   => $dataInicio,
            ":fim"      => $dataInicio,
            ":apanha"   => $horaApanha2,
            ":infoadd"  => $documento2,
            ":servico"  => $service2,
            ":valor"    => $valor2,
            ":embarque" => $embarque,
            ":p"        => $qtdpax,
            ":c"        => $qchild,
            ":f"        => $qfree,
            ":id"       => $idAdd
        )
    );
    $sql = "update ct_reserva set data_alteracao = now() where numbervoucher = '".$voucher."'";   
    $updateData = $pdo->prepare($sql);
    $updateData->execute();
    $nameSearchService = $pdo->prepare("select * from `ct_servico` where id = :id ");
    $nameSearchService->execute( array(":id" => $service2) );
    $searchData = $nameSearchService->fetch(PDO::FETCH_ASSOC);

    logAudit($pdo, $_POST['voucher'],
            "A reserva ADICIONAL foi atualizada para as seguintes informações: Data de Embarque Inicial: "
                .date("d-m-Y", strtotime($dataInicio))." Data de Embarque Final: ".date("d-m-Y", strtotime($dataFim)).
                " Complemento: ".$documento2." Valor do Serviço R$ ".$valor2." Horário de Apanha: ".$horaApanha2.
                " Adultos: ".$qtdpax." Crianças: ".$qchild." Gratuitos: ".$qfree." Serviço atual: ".$searchData['fullname']
        );
    if(isset($_POST['confirmarhorarioembarque2']))
    {
        $totalPax2 = $qtdpax+$qchild+$qfree;
        $confirmacao_horario_embarque = $pdo->prepare("update `ct_recentlyadd` set `confirmacao2` = :confirmacao where `id` = :id");
        $confirmacao_horario_embarque->execute(array(":confirmacao" => 1, ":id" => $idAdd));

        $confirmar_embarque_por_operador = $pdo->prepare('insert into `ct_confirmacao` values (DEFAULT, :operador, :idhorario, :idservico , :dataa, :total)');
        $confirmar_embarque_por_operador->execute(array(":operador" => $_SESSION['id'], ":idhorario" => $embarque, ":idservico" => $service2 ,":dataa" => date("Y-m-d"), ":total" => $totalPax2));

        $buscar_horario = $pdo->prepare('select `schedule` from `ct_service_schedule` where idshedule = :idshedule order by `schedule`');
        $buscar_horario->execute(array(":idshedule" =>$embarque));
        $dados_busca_horario = $buscar_horario->fetch(PDO::FETCH_ASSOC);


        logAudit($pdo, $voucher,
            "Confirmou o horário de embarque para ".$dados_busca_horario['schedule']." do serviço ".$searchData['fullname']
        );
    }
    if($valor3 <> $valor2)
    {
        
        $confirmacao_horario_embarque = $pdo->prepare("update `ct_recentlyadd` set `fl_altarar_valor_servico` = :confirmacao where `id` = :id");
        $confirmacao_horario_embarque->execute(array(":confirmacao" => 1, ":id" => $idAdd));
        
        logAudit($pdo, $_POST['voucher'],
            "Alterou o valor do serviço adicional de R$ ".number_format($_POST['valor2'],2)." para R$ ".$valor2
        );

    }
    header('location: editar-pax?numbervoucher='.$_POST['voucher']);

}
if( isset($_POST['deleteserviceadd']) )
{
    $idAdd            = $_POST['idAdd'];

    $deleteForever  = $pdo->prepare('delete from `ct_recentlyadd` where id = :id ');
    $deleteForever->execute( array(":id" => $idAdd) );

    logAudit($pdo, $_POST['voucher'],
            "A reserva adicional foi excluida "
        );

    header('location: editar-pax?numbervoucher='.$_POST['voucher']);

}
if( isset($_POST['updatecredit'] ) )
{
    $idcredit        = $_POST['idcredit'];
    $valordocredito  = str_replace(".", "", $_POST['valor']);
    $valor           = str_replace(",", ".", $valordocredito);
    $idPagamento     = $_POST['pagamento'];

    $updateValor = $pdo->prepare('update `ct_createfaturacredit` set `valuecredit` = :newcredit, `idaccountcurrent` = :pagamento where `id` = :id ');
    $updateValor->execute([":newcredit" => str_replace(",", ".", str_replace(".", ".", $_POST['valor'])), ":pagamento" => $idPagamento, ":id" => $idcredit]);

    $pdo->prepare("update ct_reserva set data_alteracao = now() where numbervoucher = :v")->execute([':v' => $_POST['voucher']]);
    logAudit($pdo, $_POST['voucher'], "Valor do crédito atualizado. para R$ " . $valor);
    setInvoiceStatus($pdo, $_POST['voucher']);
    setFlash('success', "Crédito atualizado no valor de R$ {$valor} para o voucher {$_POST['voucher']}");
    header('location: editar-pax?numbervoucher=' . $_POST['voucher']);
    exit;
}
if( isset($_POST['updatedes'] ) )
{
    $idDespesa = $_POST['iddespesa'];
    $valor = str_replace(",", ".", $_POST['valor']);
    $updateValor = $pdo->prepare('update `ct_createfaturacredit` set `valueagente` = :newcredit where `id` = :id');
    $updateValor->execute([":newcredit" => $valor, ":id" => $idDespesa]);
    logAudit($pdo, $_POST['voucher'], "Valor da comissão foi atualizado. para R$ ".$valor);
    setFlash('success', "Comissão atualizada para o valor de R$ {$valor} para o voucher {$_POST['voucher']}");
    header('location: editar-pax?numbervoucher='.$_POST['voucher']);
    exit;
}
if( isset($_POST['deletecredit'] ) )
{
    $idcredit = $_POST['idcredit'];
    $updateValor = $pdo->prepare('delete from `ct_createfaturacredit` where `id` = :id');
    $updateValor->execute([":id" => $idcredit]);
    $pdo->prepare('update ct_reserva set data_alteracao = now() where numbervoucher = :v')->execute([':v' => $_POST['voucher']]);
    logAudit($pdo, $_POST['voucher'], "Crédito removido");
    setInvoiceStatus($pdo, $_POST['voucher']);
    syncReservaTotais($pdo, $_POST['voucher']);
    setFlash('danger', "Crédito removido para o voucher: {$_POST['voucher']}");
    header('location: editar-pax?numbervoucher='.$_POST['voucher']);
    exit;
}
if( isset($_POST['deletecomissao'] ) )
{
    $iddespesa = $_POST['iddespesa'];
    $updateValor = $pdo->prepare('delete from `ct_createfaturacredit` where `id` = :id');
    $updateValor->execute([":id" => $iddespesa]);
    logAudit($pdo, $_POST['voucher'], "Pagamento de comissão cancelado.");
    setFlash('danger', "COMISSÃO CANCELADA PARA O VOUCHER: {$_POST['voucher']}");
    header('location: editar-pax?numbervoucher='.$_POST['voucher']);
    exit;
}
if( isset($_POST['voucherEmail'])) {

    $vouchercliente = $_POST['voucher'];
    $tipo = $_POST['tipo'];

    try {

        //Server settings
        $mail->SMTPDebug = 0;
        $mail->isSMTP();
        $mail->Host = 'email-ssl.com.br';
        $mail->SMTPAuth = true;
        $mail->Username = 'reservasonline@grupocassi.com.br';
        $mail->Password = 'A@nderson30116530';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        //Recipients
        $mail->setFrom('reservasonline@grupocassi.com.br', 'Reservas Online - Cassi Turismo');
        $mail->addAddress($_POST['emailcliente'], 'Cliente');     // Add a recipient
        //$mail->addAddress('ellen@example.com');               // Name is optional
        $mail->addReplyTo('cassi@cassiturismo.com.br', 'Information');
        $mail->addCC('cassi@cassiturismo.com.br');
        //$mail->addBCC('bcc@example.com');

        //Attachments
        //$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
        //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

        //Content
        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = 'Meu Voucher - Cassi turismo';
        $mail->Body    = "
                <body leftmargin='0' marginwidth='0' topmargin='0' marginheight='0' offset='0'>
<div id='wrapper' dir='ltr' style=' margin: 0; padding: 70px 0 70px 0; -webkit-text-size-adjust: none !important; width: 100%;'>
    <table border='0' cellpadding='0' cellspacing='0' height='100%' width='100%'><tr>
        <td align='center' valign='top'>
            <div id='template_header_image'>
                <p style='margin-top: 0; background-color: #4b3bfc;'>
                <img src='http://cassiturismo.com.br/wp-content/themes/travel-stories/images/cassi.png' alt='Cassi Turismo' 
                style='border: none; display: inline-block; font-size: 14px; font-weight: bold; height: auto; outline: none; text-decoration: none; 
                text-transform: capitalize; vertical-align: middle; margin-right: 10px;'>
                </p>
            </div>
            <table border='0' cellpadding='0' cellspacing='0' width='600' id='template_container' style='box-shadow: 0 1px 4px rgba(0,0,0,0.1) 
            !important; background-color: #ffffff; border: 1px solid #4335e3; border-radius: 3px !important;'>
                <tr>
                    <td align='center' valign='top'>
                        <!-- Header -->
                        <table border='0' cellpadding='0' cellspacing='0' width='600' id='template_header' 
                        style='background-color: #3f0ed1; border-radius: 3px 3px 0 0 !important; color: #ffffff; border-bottom: 0; 
                        font-weight: bold; line-height: 100%; vertical-align: middle; '>
                            <tr>
                            <td id='header_wrapper' style='padding: 36px 48px; display: block;'>
                                <h1 style='color: #ffffff;  font-size: 30px; 
                                font-weight: 300; line-height: 150%; margin: 0; text-align: left; text-shadow: 0 1px 0 #653eda;'>Meu Voucher</h1>
                            </td>
                        </tr></table>
                        <!-- End Header -->
                    </td>
                </tr>
                <tr>
                    <td align='center' valign='top'>
                        <!-- Body -->
                        <table border='0' cellpadding='0' cellspacing='0' width='600' id='template_body'><tr>
                            <td valign='top' id='body_content' style='background-color: #ffffff;'>
                                <!-- Content -->
                                <table border='0' cellpadding='20' cellspacing='0' width='100%'><tr>
                                    <td valign='top' style='padding: 48px 48px 0;'>
                                        <div id='body_content_inner' style='color: #636363;  font-size: 14px; line-height: 150%; text-align: left;'>
                                         <p style='text-align: justify;'>
                                                Há 15 anos no mercado a nossa empresa vem desenvolvendo o trade turístico no estado da Bahia e temos como nosso maior
                                                mérito a criação do transfer semi-terrestre para Morro de São Paulo. Equipados com uma frota marítima e terrestre de
                                                última geração desempenhamos nossos serviços com altíssimo padrão de qualidade sempre presando pelo conforto e
                                                segurança dos passageiros. Nossas agências são estrategicamente posicionadas para proporcionar o melhor atendimento
                                                possível, oferecendo uma estrutura com alto padrão de qualidade onde o turista pode encontrar, caixa eletrônico,
                                                lanchonete, ar-condicionado, Wifi dentre outros ítens de conforto que só a Cassi Turismo oferece.
                                            </p>
                                            <h2 style='color: #3f0ed1; display: block; 
                                            font-size: 18px; font-weight: bold; line-height: 130%; margin: 0 0 18px; text-align: left;'>
                                                <a href='http://grupocassi.com.br/vouchercliente.php?voucher=$vouchercliente&tipo=$tipo' >Visualizar Voucher</a>
                                            </h2>
                                        </div>
                                    </td>
                                </tr></table>
                     
                            </td>
                        </tr></table>
     
                    </td>
                </tr>
                <tr>
                    <td align='center' valign='top'>
                        <!-- Footer -->
                        <table border='0' cellpadding='10' cellspacing='0' width='600' id='template_footer'>
                            <tr>
                            <td valign='top' style='padding: 0; -webkit-border-radius: 6px;'>
                                <table border='0' cellpadding='10' cellspacing='0' width='100%'>
                                    <tr>
                                        <td colspan='2' valign='middle' id='credit' style='padding: 0 48px 48px 48px; -webkit-border-radius: 6px; 
                                        border: 0; color: #8c6ee3; font-family: Arial; font-size: 12px; line-height: 125%; text-align: center;'>
                                            <h1>Cassi Turismo 16 Anos</h1>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr></table>
                 
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    </table>
</div>
</body>
        ";
        //$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

        $mail->send();
        logAudit($pdo, $_POST['voucher'],
            "Voucher enviado para o e-mail:".$_POST['emailcliente']
        );

        $dadosReservaAu  = $pdo->prepare("select * from `ct_audit` where `voucher` = :numberVoucher ");
        $dadosReservaAu->execute( array(":numberVoucher" => $_POST['voucher'] ) );
        $registroAu      =  $dadosReservaAu->fetchAll(PDO::FETCH_CLASS);
        $contadorAuditoria = $dadosReservaAu->rowCount();
        echo ("<div class='alert alert-success' role='alert' style='margin-top: 90px;'>E-mail enviado  para o voucher: ".$_POST['voucher']."</div>");

    } catch (Exception $e) {
        echo ("<div class='alert alert-danger' role='alert'>E-mail não enviado  para o voucher: ". $mail->ErrorInfo."</div>");

    }
    header('location: editar-pax?numbervoucher='.$_POST['voucher']);

}
if( isset($_POST['Addcredito'] ) )
{
    $voucher        = $_POST['voucher'];
    $desc           = $_POST['desc'];
    $datacredito    = $_POST['datacredito'];
    $valor          = str_replace(".", "", $_POST['valordocredito']);
    $valordocredito = str_replace(",", ".", $valor);
    $ccfp           = $_POST['ccfp'];
    $resp           = $_POST['responsavel'];
    $busca_empresa = $pdo->prepare('select * from `ct_currentaccount` where id = :id');
    $busca_empresa->execute([":id" => $ccfp]);
    $dados_busca_empresa = $busca_empresa->fetch(PDO::FETCH_ASSOC);
    $tipo = ($resp == 1) ? 2 : 1;
    $novaTransacao = $pdo->prepare(
        "insert into `ct_caixa` (`id`, `datevencimento`, `datepagamento`, `datecompetencia`, `nome` ,`descricao`, `idcliente`, `idtipo`, `idconta`,
                     `idplano`, `idempresa` ,`idstatus`, `valor`, `idusr`, `dataabertura`) values (DEFAULT, :vencimento, :pagamento, :competencia, :nome ,:descricao,
                      :cliente, :tipo, :conta, :plano, :empresa ,:statuus, :valor, :idusr, :abertura)");
    $novaTransacao->execute([
        ":vencimento"  => $datacredito,
        ":pagamento"   => $datacredito,
        ":competencia" => $datacredito,
        ":nome"        => "CREDITO DO VOUCHER ".$voucher,
        ":descricao"   => "CREDITO DO VOUCHER ".$voucher,
        ":cliente"     => 15,
        ":tipo"        => $tipo,
        ":conta"       => $ccfp,
        ":plano"       => 10,
        ":empresa"     => $dados_busca_empresa['idempr'],
        ":statuus"     => 1,
        ":valor"       => $valordocredito,
        "idusr"        => $_POST['responsavel'],
        ":abertura"    => date("Y-m-d"),
    ]);
    $pdo->prepare('update ct_reserva set data_alteracao = now() where numbervoucher = :v')->execute([':v' => $voucher]);
    $novoCredito = $pdo->prepare(
        "insert into ct_createfaturacredit set `numbervoucher` = :voucher, tarifa = :valor, `desccredit` = :desc, `datacredit` = :data,
         `valuecredit` = :valor2, `valueguia` = '0.00', `valueagente` = '0.00', `idaccountcurrent` = :ccfp, `idplancount` = 1, `idusr` = :resp");
    $novoCredito->execute([
        ':voucher' => $voucher,
        ':valor'   => $valordocredito,
        ':desc'    => $desc,
        ':data'    => $datacredito,
        ':valor2'  => $valordocredito,
        ':ccfp'    => $ccfp,
        ':resp'    => $resp,
    ]);
    $cartao = $pdo->prepare('SELECT name FROM `ct_currentaccount` where id = :id');
    $cartao->execute([":id" => $ccfp]);
    $dadosCartao = $cartao->fetch(PDO::FETCH_ASSOC);
    logAudit($pdo, $voucher, "Crédito no valor de R$ ".$valordocredito." pago com ".$dadosCartao['name']);
    setFlash('success', "Crédito adicionado no valor de R$ {$valordocredito} para o voucher {$voucher}");
    header('location: editar-pax?numbervoucher='.$voucher);
    exit;
}
if( isset($_POST['vincular']) )
{
    $numeroVoucher    = addslashes( $_POST['voucher'] );
    $dataInicio       = addslashes( $_POST['datainicio']);
    $service          = addslashes( $_POST['servico']);
    $documento        = addslashes( $_POST['documento']);
    $valor            = str_replace(",",".", $_POST['valorservico']);
    $horario          = addslashes( $_POST['horario']);
    $quantiPax        = addslashes( $_POST['quantidadepax']);
    $quantiChild      = addslashes( $_POST['quantidadechild']);
    $quantiFree       = addslashes( $_POST['quantidadefree']);
    $timechegada      = strtotime( $_POST['datainicio'] );
    $horarioap        = $_POST['horariobusca'];
    $buscarReservaId = $pdo->prepare("select * from `ct_reserva` where `numbervoucher` = :numbervoucher ");
    $buscarReservaId->execute(array(":numbervoucher" => $numeroVoucher));
    $dadosReservaId = $buscarReservaId->fetch(PDO::FETCH_ASSOC);

    if( $service == 14 or $service == 134 or $service == 135 or $service ==  138 )
    {
        echo("<div class='alert alert-danger' role=''>Serviço Bloqueado</div>");

    }elseif($valor == 0)
    {
        echo("<div class='alert alert-danger' role='' style='margin-top: 150px;'>O valor deve ser maior do que R$0,00</div>");
    }
    else{

        $vincularServicoVoucher = $pdo->prepare(
            'INSERT INTO `ct_recentlyadd` (`id`, `idrecently`, `idservice`, `documento` ,`valueservice` ,`idschedule`, `horaap`, `dateinput`, `dateoutput`,
                  `qpax`, `qchild`, `qfree`) VALUES   (DEFAULT, :reserva, :service, :docu ,:valor ,:hora, :horaap ,:di, :doo, :qp, :qc, :qf) ');
        $vincularServicoVoucher->execute(
            array(
                ":reserva" => $dadosReservaId['id'],
                ":service" => $service,
                ":docu"     => $documento,
                ":valor"   => $valor,
                ":hora"    => $horario,
                ":horaap"  => $horarioap,
                ":di"      => $dataInicio,
                ":doo"     => $dataInicio,
                ":qp"      => $quantiPax,
                ":qc"      => $quantiChild,
                ":qf"      => $quantiFree
            )
        );

        $nameSearchService = $pdo->prepare("select * from `ct_servico` where id = :id ");
        $nameSearchService->execute( array(":id" => $service) );
        $searchData = $nameSearchService->fetch(PDO::FETCH_ASSOC);

        logAudit($pdo, $numeroVoucher,
            "Reserva vinculada com as seguintes informações:
                \n Embarque: ".date('d-m-Y', strtotime($dataInicio))." Apanha: ".$horarioap." Adultos: ".$quantiPax." Crianças: ".$quantiChild." Free: "
                    .$quantiFree." Serviço: ".$searchData['fullname']. " Complemento: ".$documento." Valor R$ ".$valor
        );
        header('location: editar-pax?numbervoucher='.$_POST['voucher']);
    }
}
if( isset($_POST['incluircreditofatura']) ) {
    $valor  = str_replace(".", "", $_POST['valor']);
    $valor1 = str_replace(",", ".", $valor);

    $creditoDebitoFatura = $pdo->prepare(
            'insert into `ct_credito_deb_reserva` values (DEFAULT, :voucher, :dataa, :valor, :conta, :plano, :sta, :tipo, :descricao) ');
    $creditoDebitoFatura->execute(
            array(
                    ":voucher"      => $_POST['voucher'],
                    ":dataa"        => $_POST['datapagamento'],
                    ":valor"        => $valor1,
                    ":conta"        => $_POST['contacorrente'],
                    ":plano"        => $_POST['planodecontas'],
                    ":sta"          => $_POST['status'],
                    ":tipo"         => $_POST['tipo'],
                    ":descricao"    => $_POST['descricao']
            )
    );
    if($_POST['tipo'] == 1){
        $nome = 'Credito';
    }else{
        $nome = 'Debito';
    }
    logAudit($pdo, $_POST['voucher'],
            "Inseriu um ".$nome." no valor de R$ ".$_POST['valor']
        );

    header('location: editar-pax?numbervoucher='.$_POST['voucher']);

}
if( isset($_POST['atualizarCadFatura']) ) {
    $valor  = str_replace(".", "", $_POST['valor']);
    $valor1 = str_replace(",", ".", $valor);

    $update_cadastro_fatura = $pdo->prepare(
            'update `ct_credito_deb_reserva` set `data` = :dataa, `valor` = :valor, `idcurrentaccount` = :conta, `idplanaccount` = :plano, `idstatus` = :statuss,
                       `idtype` = :tipo, `descricao` = :descricao where `id` = :id ');
    $update_cadastro_fatura->execute(
            array(
                    ":dataa"     => $_POST['datafatura'],
                    ":valor"     => $valor1,
                    ":conta"     => $_POST['contacorrente'],
                    ":plano"     => $_POST['planodeContas'],
                    ":statuss"    => $_POST['status'],
                    ":tipo"      => $_POST['tipo'],
                    ":descricao" => $_POST['descricao'],
                    ":id"        => $_POST['idcadfatura']
            )
    );
    logAudit($pdo, $_POST['voucher'],
            "Atualizou o cadastro da fatura"
        );

    header('location: editar-pax?numbervoucher='.$_POST['voucher']);

}
if( isset($_POST['excluirCadFatura']) ) {

    $creditoDebitoFaturaDel = $pdo->prepare('delete from `ct_credito_deb_reserva` where id = :id ');
    $creditoDebitoFaturaDel->execute(array(":id" => $_POST['idcadfatura']));

    logAudit($pdo, $_POST['voucher'],
            "Excluiu o cadastro da fatura"
        );

    header('location: editar-pax?numbervoucher='.$_POST['voucher']);
}

?>
<link href="../css/reserva-ui.css" rel="stylesheet" media="all">
<style>
    .editar-pax-page{
        background:#f4f7fb;
        min-height:calc(100vh - 70px);
        padding-bottom:40px;
    }
    .editar-pax-page .au-breadcrumb2{
        background:transparent;
        padding:28px 0 14px;
    }
    .editar-pax-page .au-breadcrumb-span,
    .editar-pax-page .au-breadcrumb__list li,
    .editar-pax-page .au-breadcrumb__list span{
        color:#6b7280;
        font-size:13px;
    }
    .editar-pax-page .containerrrr{
        width:94%;
        max-width:1180px;
        margin:0 auto;
    }
    .editar-pax-page .voucher-card{
        border:0;
        border-radius:20px;
        background:#fff;
        box-shadow:0 18px 45px rgba(15,23,42,.08);
        overflow:hidden;
    }
    .editar-pax-page .voucher-heading{
        background:linear-gradient(135deg,#1e4770,#256aa0);
        color:#fff;
        padding:28px 32px;
        text-align:left;
    }
    .editar-pax-page .voucher-heading h3{
        color:#fff;
        font-size:24px;
        font-weight:700;
        margin:0 0 8px;
        padding:0;
    }
    .editar-pax-page .voucher-heading small{
        color:rgba(255,255,255,.82);
        display:block;
        font-size:13px;
        text-align:left;
    }
    .editar-pax-page .card-body{
        padding:26px 30px 34px;
    }
    .editar-pax-page .nav-tabs{
        border:0;
        display:flex;
        flex-wrap:wrap;
        gap:8px;
        margin-bottom:24px;
    }
    .editar-pax-page .nav-tabs .nav-link{
        border:1px solid #e5e7eb;
        border-radius:999px;
        color:#25633f;
        display:flex;
        align-items:center;
        gap:8px;
        font-weight:600;
        padding:10px 15px;
        background:#f8fafc;
        transition:all .2s ease;
    }
    .editar-pax-page .tab-icon{
        width:18px;
        height:18px;
        stroke:currentColor;
        stroke-width:2;
        fill:none;
        stroke-linecap:round;
        stroke-linejoin:round;
    }
    .editar-pax-page .nav-tabs .nav-link:hover{
        border-color:#19a66a;
        color:#0f7a49;
        background:#eefaf4;
    }
    .editar-pax-page .nav-tabs .nav-link.active{
        border-color:#13a463;
        color:#fff;
        background:#13a463;
        box-shadow:0 10px 22px rgba(19,164,99,.24);
    }
    .editar-pax-page .tabcontent-border{
        border:0;
    }
    .editar-pax-page .tab-pane{
        background:#fff;
    }
    .editar-pax-page .tab-pane.p-20{
        padding:0 !important;
    }
    .editar-pax-page .tab-pane h4{
        color:#0f172a;
        font-size:18px;
        font-weight:800;
        margin:0 0 18px !important;
    }
    .editar-pax-page .tab-pane hr{
        border-top:1px solid #e5e7eb;
        margin:0 0 20px;
    }
    .editar-pax-page .tab-content>.active{
        margin-top:0;
    }
    .editar-pax-page .accordion .card,
    .editar-pax-page .tab-pane>.col-lg-12,
    .editar-pax-page .table-responsive,
    .editar-pax-page .table-responsivo{
        border:1px solid #e5e7eb;
        border-radius:16px;
        background:#fff;
        box-shadow:0 10px 24px rgba(15,23,42,.05);
        overflow:hidden;
        margin-bottom:22px;
    }
    .editar-pax-page .accordion .card{
        overflow:hidden;
    }
    .editar-pax-page .accordion .card-header{
        border:0;
        background:#f8fafc;
        padding:0;
    }
    .editar-pax-page .accordion .btn-link{
        color:#0f172a;
        display:flex;
        justify-content:space-between;
        align-items:center;
        font-size:16px;
        font-weight:800;
        padding:16px 20px;
        text-decoration:none;
        width:100%;
    }
    .editar-pax-page .accordion .btn-link:after{
        content:"+";
        border:1px solid #dce3ec;
        border-radius:999px;
        color:#1e88d1;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        font-size:18px;
        height:28px;
        width:28px;
    }
    .editar-pax-page .accordion .card-body{
        padding:22px;
    }
    .editar-pax-page .table{
        margin-bottom:0;
    }
    .editar-pax-page .table thead th{
        background:#f8fafc;
        border-bottom:1px solid #e5e7eb;
        color:#475569;
        font-size:12px;
        letter-spacing:.03em;
        text-transform:uppercase;
        white-space:nowrap;
    }
    .editar-pax-page .table td,
    .editar-pax-page .table th{
        border-color:#edf2f7;
        vertical-align:middle;
    }
    .editar-pax-page .table tbody tr:hover{
        background:#f8fbff;
    }
    .editar-pax-page .input-group-text{
        border:1px solid #dce3ec;
        border-radius:10px 0 0 10px;
        background:#f8fafc;
        color:#64748b;
        font-weight:700;
    }
    .editar-pax-page .alert{
        border:0;
        border-radius:14px;
        font-weight:700;
    }
    .editar-pax-page .col-md-4,
    .editar-pax-page .col-md-3,
    .editar-pax-page .col-md-6,
    .editar-pax-page h4{
        margin-bottom:18px;
    }
    .editar-pax-page label{
        color:#374151;
        font-size:13px;
        font-weight:700;
        margin-bottom:8px;
    }
    .editar-pax-page label span{
        color:#15803d !important;
        font-weight:700;
    }
    .editar-pax-page .form-control,
    .editar-pax-page select.form-control{
        border:1px solid #dce3ec;
        border-radius:10px;
        background:#fff;
        color:#1f2937;
        min-height:42px;
        box-shadow:none;
        transition:border-color .2s ease,box-shadow .2s ease;
    }
    .editar-pax-page .form-control:focus,
    .editar-pax-page select.form-control:focus{
        border-color:#1e88d1;
        box-shadow:0 0 0 3px rgba(30,136,209,.14);
    }
    .editar-pax-page .form-control:disabled,
    .editar-pax-page select.form-control:disabled{
        background:#f1f5f9;
        color:#64748b;
    }
    .editar-pax-page .btn{
        align-items:center;
        border-radius:10px;
        display:inline-flex;
        gap:8px;
        justify-content:center;
        font-weight:700;
        min-height:42px;
    }
    .editar-pax-page .btn svg,
    .editar-pax-page .action-icon-button svg{
        width:18px;
        height:18px;
        stroke:currentColor;
        stroke-width:2;
        fill:none;
        stroke-linecap:round;
        stroke-linejoin:round;
    }
    .editar-pax-page .action-icon-button{
        align-items:center;
        background:#f8fafc;
        border:1px solid #dce3ec;
        border-radius:10px;
        color:#475569;
        display:inline-flex;
        gap:6px;
        justify-content:center;
        min-height:36px;
        padding:7px 12px;
    }
    .editar-pax-page .action-icon-button:hover{
        background:#eef6ff;
        color:#1e88d1;
    }
    .editar-pax-page .btn-primary{
        background:#1e88d1;
        border-color:#1e88d1;
        box-shadow:0 10px 20px rgba(30,136,209,.18);
    }
    .editar-pax-page .btn-outline-primary{
        border-color:#1e88d1;
        color:#1e88d1;
    }
    .editar-pax-page .btn-outline-primary:hover{
        background:#1e88d1;
        color:#fff;
    }
    .editar-pax-page .btn-outline-success{
        border-color:#13a463;
        color:#13a463;
    }
    .editar-pax-page .btn-outline-success:hover{
        background:#13a463;
        color:#fff;
    }
    .editar-pax-page .btn-warning{
        background:#f59e0b;
        border-color:#f59e0b;
        color:#fff;
    }
    .editar-pax-page .btn-success{
        background:#13a463;
        border-color:#13a463;
        box-shadow:0 10px 20px rgba(19,164,99,.16);
    }
    .editar-pax-page .btn-danger{
        background:#ef4444;
        border-color:#ef4444;
        box-shadow:0 10px 20px rgba(239,68,68,.14);
    }
    .editar-pax-page .btn:hover{
        transform:translateY(-1px);
    }
    .editar-pax-page .qntpessoataxamala{
        background:#f8fafc;
        border:1px solid #e5e7eb;
        border-radius:12px;
        color:#374151;
        padding:12px;
    }
    .editar-pax-page .qntpessoataxamala input{
        border:1px solid #dce3ec;
        border-radius:10px;
        margin-top:8px;
        width:100%;
    }
    @media only screen and (max-width: 375px) {
        .containerrrr {
            width: 100%;
            padding-right: 15px;
            padding-left: 15px;
            margin-right: auto;
            margin-left: auto;
        }
        table{
            white-space: pre;
        }
    }
    @media only screen and (max-width: 414px) {
        .containerrrr {
            width: 100%;
            padding-right: 15px;
            padding-left: 15px;
            margin-right: auto;
            margin-left: auto;
        }
        table{
            white-space: pre;
        }
    }
    @media only screen and (max-width: 411px) {
        .containerrrr {
            width: 100%;
            padding-right: 15px;
            padding-left: 15px;
            margin-right: auto;
            margin-left: auto;
        }
        table{
            white-space: pre;
        }
    }
    @media only screen and (max-width: 767px) {
        .editar-pax-page .containerrrr{
            width:100%;
            padding:0 12px;
        }
        .editar-pax-page .voucher-heading,
        .editar-pax-page .card-body{
            padding:22px 18px;
        }
        .editar-pax-page .nav-tabs .nav-link{
            width:100%;
            text-align:center;
        }
    }
</style>
<!-- PAGE CONTENT-->
<div class="page-content--bgf7 editar-pax-page reserva-ui-page">
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
                                    <a href="index.php">Home</a>
                                </li>
                                <li class="list-inline-item seprate">
                                    <span>/</span>
                                </li>
                                <li class="list-inline-item">PAX: Editar pax</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- END BREADCRUMB-->

    <div class="">
        <div class="containerrrr reserva-ui-container">
            <div class="card card-outline-primary voucher-card reserva-ui-card">
                <div class="voucher-heading reserva-ui-heading">
                    <h3><?php echo("Voucher - ".$dadosGerais['numbervoucher']); ?></h3>
                    <small><?php echo("Abertura ".date("d-m-Y", strtotime( $dadosGerais['abertura'] )) ); ?></small>
                </div>
                <div class="card-body">
                    <?php if( $dadosGerais['idresponsavel'] <> $_SESSION['id'] and $_SESSION['id'] == 45 ){ ?>
                        <div class="col-lg-12">
                            <div class="alert alert-danger" role="alert">
                                <h2>
                                    Você não possui permissão para editar este voucher. Entre em contato através do grupo(Whatsapp)
                                </h2>
                            </div>
                        </div>
                    <?php } else { ?>
                        <div class="col-lg-12">
                            <?php if($dadosGerais['dateinput'] == date("Y-m-d")){ ?>
                                <?php if( $dadosGerais['confirmacao'] == 1  ){ ?>
                                    <div class="modal" tabindex="-1" role="dialog">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Sistema</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">

                                                    <p>Horário de Embarque confirmado!</p>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                <?php } else {?>
                                    <div class="modal" tabindex="-1" role="dialog">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">AVISO</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">

                                                    <p>Confirmar Horário de Embarque!</p>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                    <?php }?>
                            <?php }?>

                            <?php if( !empty($_SESSION['idoperador']) or !empty($_SESSION['idreservamanager']) or !empty($_SESSION['idreservaplus'])){ ?>
                                <!-- verifica permissão pra editar a reserva -->
                                <?php if( $dadosGerais['idstatusinvoice'] >= 3 and $dadosGerais['idstatusinvoice'] <= 5 and $_SESSION['id'] <> 46 and $_SESSION['id'] <> 32
                                    and $_SESSION['id'] != 57 ){ ?>
                                    <div class="modal" tabindex="-1" role="dialog">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Sistema</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">

                                                    <p>Você não tem permissão para editar este voucher.<br>Entre em contato com o setor responsável.</p>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                <?php }?>

                            <?php }?>
                            <!-- Alerta de compra pra morro -->
                            <?php if( $dadosGerais['serivco'] == 'AEROPORTO / MORRO (SEMI - TERRESTRE).' or
                            $dadosGerais['serivco'] == 'TERMINAL SSA / MORRO ( SEMI - TERRESTRE ).' or  $dadosGerais['serivco'] == 'TRF - SEMI TERRESTRE AERO / MORRO'
                            or  $dadosGerais['serivco'] == 'TRF - HTL / MSP'){ ?>
                                <?php if( $contador <= 0 ){?>
                                    <div class="modal" tabindex="-1" role="dialog">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Sistema</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Está reserva ainda não comprou a volta de morro de SÃO PAULO</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                            <?php }?>
                            <?php require_once __DIR__.'/components/reserva-ui-icons.php'; ?>
                            <ul class="nav nav-tabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-toggle="tab" href="#home" role="tab">
                                        <svg class="tab-icon"><use href="#icon-info"></use></svg>
                                        <span>Informações</span>
                                    </a>
                                </li>

                                <li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#profile" role="tab">
                                        <svg class="tab-icon"><use href="#icon-plus"></use></svg>
                                        <span>Adicionais</span></a>
                                </li>
                                <?php if( empty($_SESSION['idpagarreserva']) ){ ?>
                                <li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#messages" role="tab">
                                        <svg class="tab-icon"><use href="#icon-credit"></use></svg>
                                        <span>Créditos</span></a>
                                </li>
                                <?php }?>
                                <?php if(!empty($_SESSION['comissao'] or $_SESSION['idfaturador'] or  $_SESSION['idgerente']
                                        or $_SESSION['idreservamanager'] or $_SESSION['comissaorelatoriofolha'] ) or $_SESSION['id'] == 46 or $_SESSION['id'] == 273 or $_SESSION['id'] == 225 ){ ?>
                                    <?php if($_SESSION['id'] <> 55  ){?>
                                        <?php if($_SESSION['id'] <> 31  ){?>
                                            <li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#despesas" role="tab">
                                                    <svg class="tab-icon"><use href="#icon-commission"></use></svg>
                                                    <span>Comissão</span></a>
                                            </li>
                                        <?php }?>
                                    <?php }?>

                                <?php }?>
                                <li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#auditoria" role="tab">
                                        <svg class="tab-icon"><use href="#icon-audit"></use></svg>
                                        <span>Auditoria</span></a>
                                </li>
                                <li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#fatura" role="tab">
                                        <svg class="tab-icon"><use href="#icon-check"></use></svg>
                                        <span>Baixa</span></a>
                                </li>
                                <li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#creditofatura" role="tab">
                                        <svg class="tab-icon"><use href="#icon-invoice"></use></svg>
                                        <span>Crédito de Fatura</span></a>
                                </li>
                            </ul>
                            <!-- Tab panes -->
                            <div class="tab-content tabcontent-border">
                                <div class="tab-pane active" id="home" role="tabpanel">
                                    <form action="" autocomplete="off" method="post" enctype="multipart/form-data">
                                        <input type="hidden" value="1" name="status">
                                        <input type="hidden" value="<?php echo($dadosGerais['numbervoucher']); ?>" name="voucher">
                                        <input type="hidden" value="<?php echo($dadosGerais['valueservice']); ?>" name="valor2">
                                        <input type="hidden" name="clienteatual" value="<?php echo($dadosGerais['idcliente']); ?>"/>
                                        <input type="hidden" name="empresaatual" value="<?php echo($dadosGerais['idempresa']); ?>"/>
                                        <input type="hidden" name="empresatual" value="<?php echo($dadosGerais['idempresa']); ?>"/>

                                        <div class="rui-section">
                                            <div class="rui-section-title">Identificação</div>
                                            <div class="rui-grid-3">
                                                <div class="rui-field">
                                                    <label for="cliente">Agência</label>
                                                    <select class="form-control" name="cliente" id="cliente" <?= ($_SESSION['idgerente'] != '2') ? 'disabled' : null ?>>
                                                        <?php foreach ($todosCliente as $item) { ?>
                                                            <?php if ($dadosGerais['cliente'] == $item->fullname) { ?>
                                                                <option selected value="<?php echo htmlentities($item->id, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlentities($item->fullname, ENT_QUOTES, 'UTF-8'); ?></option>
                                                            <?php } else { ?>
                                                                <option value="<?php echo htmlentities($item->id, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlentities($item->fullname, ENT_QUOTES, 'UTF-8'); ?></option>
                                                            <?php } ?>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                                <div class="rui-field">
                                                    <label for="idempresa">Empresa</label>
                                                    <select class="form-control" name="idempresa" id="idempresa" <?= ($_SESSION['idgerente'] != '2') ? 'disabled' : null ?>>
                                                        <?php foreach ($listaEmpresas as $item) { ?>
                                                            <?php if ($dadosGerais['idempresa'] == $item->id) { ?>
                                                                <option selected value="<?php echo htmlentities($item->id, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlentities($item->fullname, ENT_QUOTES, 'UTF-8'); ?></option>
                                                            <?php } else { ?>
                                                                <option value="<?php echo htmlentities($item->id, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlentities($item->fullname, ENT_QUOTES, 'UTF-8'); ?></option>
                                                            <?php } ?>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                                <div class="rui-field">
                                                    <label for="responsavel">Operador(a)</label>
                                                    <input disabled type="text" name="responsavel" id="responsavel" class="form-control" value="<?php echo($dadosGerais['firstname']); ?>">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="rui-section">
                                            <div class="rui-section-title">Passageiro</div>
                                            <div class="rui-grid-3">
                                                <div class="rui-field">
                                                    <label for="documento">Complemento | Observação</label>
                                                    <input type="text" name="documento" id="documento" class="form-control" value="<?php echo($dadosGerais['documento']); ?>">
                                                </div>
                                                <div class="rui-field">
                                                    <label for="pax">Passageiros <span>(pax)</span></label>
                                                    <input type="text" name="pax" class="form-control" value="<?php echo $dadosGerais['pax']; ?>">
                                                </div>
                                                <div class="rui-field">
                                                    <label for="telefone">Telefone <span>(Whatsapp)</span></label>
                                                    <input type="text" name="telefone" class="form-control" value="<?php echo($dadosGerais['photoresident']); ?>">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="rui-section">
                                            <div class="rui-section-title">Quantidades</div>
                                            <div class="rui-grid-3">
                                                <div class="rui-field">
                                                    <label for="quantidadepax">Adultos <span>(10 a 64 anos ou Idoso 65+)</span></label>
                                                    <input type="number" required name="quantidadepax" class="form-control" value="<?php echo($dadosGerais['qtdpax']); ?>">
                                                </div>
                                                <div class="rui-field">
                                                    <label for="quantidadechild">Meia <span>(5 a 6 anos e 11 meses)</span></label>
                                                    <input type="number" name="quantidadechild" id="quantidadechild" class="form-control" value="<?php echo($dadosGerais['qtdchild']); ?>">
                                                </div>
                                                <div class="rui-field">
                                                    <label for="quantidadefree">Free <span>(0 a 4 anos e 11 meses)</span></label>
                                                    <input type="number" name="quantidadefree" class="form-control" value="<?php echo($dadosGerais['qtdfree']); ?>">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="rui-section">
                                            <div class="rui-section-title">Logística</div>
                                            <div class="rui-grid-4">
                                                <div class="rui-field">
                                                    <label for="identmala">Identificação da Mala</label>
                                                    <input type="text" name="identificacao_mala" class="form-control" value="<?= $dadosGerais['identificacao_mala']?>" id="identmala">
                                                </div>
                                                <div class="rui-field">
                                                    <label for="datainicio">Data de Embarque</label>
                                                    <input type="date" name="datainicio" id="datainicio" class="form-control" value="<?php echo($dadosGerais['dateinput']); ?>">
                                                </div>
                                                <div class="rui-field">
                                                    <label for="horariobusca">Horário de Apresentação</label>
                                                    <input type="time" class="form-control" name="horariobusca" id="horariobusca" value="<?php echo($dadosGerais['horaap']); ?>">
                                                </div>
                                                <div class="rui-field">
                                                    <label for="horario">Horário de Embarque</label>
                                                    <select class="form-control" name="schedule" id="horario">
                                                        <?php foreach ($listaHorarios as $horariosEmbarque) { ?>
                                                            <?php if ($dadosGerais['schedule'] == $horariosEmbarque->schedule) { ?>
                                                                <option value="<?php echo($horariosEmbarque->idshedule); ?>" selected><?php echo($horariosEmbarque->schedule); ?></option>
                                                            <?php } else { ?>
                                                                <option value="<?php echo($horariosEmbarque->idshedule); ?>"><?php echo($horariosEmbarque->schedule); ?></option>
                                                            <?php } ?>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="rui-section">
                                            <div class="rui-section-title">Serviço</div>
                                            <div class="rui-grid-2">
                                                <div class="rui-field">
                                                    <label for="service">Serviço contratado</label>
                                                    <select class="form-control" name="service" id="service">
                                                        <?php foreach ($listaServicos as $item3) { ?>
                                                            <?php if ($dadosGerais['serivco'] == $item3->fullname) { ?>
                                                                <option value="<?php echo htmlentities($item3->id, ENT_QUOTES, 'UTF-8'); ?>" selected><?php echo htmlentities($item3->fullname, ENT_QUOTES, 'UTF-8'); ?></option>
                                                            <?php } else { ?>
                                                                <option value="<?php echo htmlentities($item3->id, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlentities($item3->fullname, ENT_QUOTES, 'UTF-8'); ?></option>
                                                            <?php } ?>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                                <div class="rui-field">
                                                    <label for="valueservice">Valor do Serviço</label>
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text">R$</span>
                                                        </div>
                                                        <input type="text" class="form-control" name="valueservice" id="valueservice" onKeyPress="return(moeda(this,'.',',',event))" value="<?php echo(number_format($dadosGerais['valueservice'], 2, ',', '.')); ?>">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="rui-section">
                                            <div class="rui-section-title">Ações</div>
                                            <?php if(!empty($_SESSION['idreservamanager']) or !empty($_SESSION['idgerente'])
                                                or !empty($_SESSION['idfaturador']) or !empty($_SESSION['folhaderosto'] or $_SESSION['comissaorelatoriofolha'])
                                                or $_SESSION['id'] == 208 or $_SESSION['id'] == 214 or $_SESSION['id'] == 40 or $_SESSION['id'] == 265 or $_SESSION['id'] == 44) { ?>
                                                <div class="rui-grid-2" style="align-items:flex-start; margin-bottom:16px;">
                                                    <div>
                                                        <label for="incluirtaxa"><input type="checkbox" name="incluirtaxa" id="incluirtaxa"> Incluir Taxa de Embarque?</label>
                                                        <label for="incluirtaxamala" style="margin-top:8px; display:block;">
                                                            <input type="checkbox" name="incluirtaxamala" id="incluirtaxamala" <?= ($dadosGerais['incluirtaxamala']) ? 'checked' : null?>>
                                                            Incluir Serviço de Mala?
                                                        </label>
                                                        <div class="qntpessoataxamala" style="margin-top:8px;">
                                                            Quantidade Pessoas
                                                            <input type="text" value="<?=$dadosGerais['qntpessoataxamala']?>" name="qntpessoataxamala" placeholder="Quantidade de pessoas para o serviço de mala">
                                                        </div>
                                                        <?php if($dadosGerais['dateinput'] == date("Y-m-d")) { ?>
                                                            <?php if($dadosGerais['confirmacao'] == 1) { ?>
                                                                <label for="confirmarhorarioembarque1" style="margin-top:8px; display:block;"><input type="checkbox" checked name="confirmarhorarioembarque1" id="confirmarhorarioembarque1"> Confirmar Horário de Embarque?</label>
                                                            <?php } else { ?>
                                                                <label for="confirmarhorarioembarque1" style="margin-top:8px; display:block;"><input type="checkbox" name="confirmarhorarioembarque1" id="confirmarhorarioembarque1"> Confirmar Horário de Embarque?</label>
                                                            <?php } ?>
                                                        <?php } ?>
                                                    </div>
                                                    <div>
                                                        <button type="submit" class="btn btn-primary btn-lg btn-block" id="salvarfatura" name="atualizarreserva">
                                                            <svg><use href="#icon-save"></use></svg><span>Atualizar</span>
                                                        </button>
                                                    </div>
                                                </div>
                                            <?php } else { ?>
                                                <button type="submit" class="btn btn-primary btn-lg btn-block" id="salvarfatura" name="atualizarreserva">
                                                    <svg><use href="#icon-save"></use></svg><span>Atualizar</span>
                                                </button>
                                            <?php } ?>
                                        </div>
                                    </form>

                                    <?php if(!empty($_SESSION['idreservamanager']) or !empty($_SESSION['idgerente'])
                                        or !empty($_SESSION['idfaturador']) or !empty($_SESSION['folhaderosto'] or $_SESSION['comissaorelatoriofolha'])
                                        or $_SESSION['id'] == 208 or $_SESSION['id'] == 214 or $_SESSION['id'] == 40 or $_SESSION['id'] == 265 or $_SESSION['id'] == 44) { ?>
                                        <div class="rui-section">
                                            <div class="rui-grid-2">
                                                <form action="" method="post">
                                                    <div class="input-group">
                                                        <input type="text" placeholder="E-mail" class="form-control" name="emailcliente">
                                                        <div class="input-group-append">
                                                            <button type="submit" name="voucherEmail" class="btn btn-outline-primary">
                                                                <svg><use href="#icon-mail"></use></svg><span>Enviar Voucher</span>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <input type="hidden" value="1" name="tipo">
                                                    <input type="hidden" value="<?php echo($dadosGerais['numbervoucher']); ?>" name="voucher">
                                                </form>
                                                <form action="" method="post">
                                                    <div class="input-group">
                                                        <input type="text" placeholder="E-mail" class="form-control" name="emailcliente">
                                                        <div class="input-group-append">
                                                            <button type="submit" name="voucherEmail" class="btn btn-outline-primary">
                                                                <svg><use href="#icon-mail"></use></svg><span>Enviar F. de Rosto</span>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <input type="hidden" value="2" name="tipo">
                                                    <input type="hidden" value="<?php echo($dadosGerais['numbervoucher']); ?>" name="voucher">
                                                </form>
                                            </div>
                                            <div class="rui-grid-2" style="margin-top:12px;">
                                                <form action="./relatorio/pdf-relatorio-voucher.php" target="_blank" method="post">
                                                    <input type="hidden" value="<?php echo($dadosGerais['numbervoucher']); ?>" name="voucher">
                                                    <button type="submit" class="btn btn-outline-primary btn-lg btn-block">
                                                        <svg><use href="#icon-print"></use></svg><span>Imprimir Voucher</span>
                                                    </button>
                                                </form>
                                                <form action="./relatorio/pdf-relatorio-voucher.php" target="_blank" method="post">
                                                    <input type="hidden" value="<?php echo($dadosGerais['numbervoucher']); ?>" name="voucher">
                                                    <input type="hidden" value="1" name="folharosto">
                                                    <button type="submit" class="btn btn-outline-success btn-lg btn-block">
                                                        <svg><use href="#icon-print"></use></svg><span>Imprimir Folha de Rosto</span>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    <?php } else { ?>
                                        <div class="rui-section">
                                            <div class="rui-grid-2">
                                                <form action="" method="post">
                                                    <div class="input-group">
                                                        <input type="text" placeholder="E-mail" class="form-control" name="emailcliente">
                                                        <div class="input-group-append">
                                                            <button type="submit" name="voucherEmail" class="btn btn-outline-primary">
                                                                <svg><use href="#icon-mail"></use></svg><span>Enviar Voucher</span>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <input type="hidden" value="1" name="tipo">
                                                    <input type="hidden" value="<?php echo($dadosGerais['numbervoucher']); ?>" name="voucher">
                                                </form>
                                                <form action="./relatorio/pdf-relatorio-voucher.php" target="_blank" method="post">
                                                    <input type="hidden" value="<?php echo($dadosGerais['numbervoucher']); ?>" name="voucher">
                                                    <button type="submit" class="btn btn-outline-primary btn-lg btn-block">
                                                        <svg><use href="#icon-print"></use></svg><span>Imprimir Voucher</span>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    <?php } ?>

                                    <?php if(!empty($_SESSION['idreservamanager']) or !empty($_SESSION['idgerente'])
                                        or !empty($_SESSION['idfaturador']) or !empty($_SESSION['folhaderosto'] or $_SESSION['comissaorelatoriofolha'])) { ?>
                                        <div style="margin-top:8px;">
                                            <form action="map-visualizar-servico.php" method="post">
                                                <?php if(isset($_SESSION['servico']) and count($_SESSION['servico']) > 0) { ?>
                                                    <input type="hidden" name="datainicio" value="<?php echo($_SESSION['datainicio']); ?>">
                                                    <input type="hidden" name="datafim"    value="<?php echo($_SESSION['datafim']); ?>">
                                                    <input type="hidden" name="cliente"    value="<?php echo($_SESSION['cliente']); ?>">
                                                    <input type="hidden" name="horario"    value="<?php echo($_SESSION['horario']); ?>">
                                                    <select style="display:none;" class="form-control" name="servico[]" multiple>
                                                        <?php foreach ($listaServicos as $item4) { ?>
                                                            <?php for ($i = 0; $i <= count($_SESSION['servico']); $i++) { ?>
                                                                <?php if($item4->id == $_SESSION['servico'][$i]) { ?>
                                                                    <option selected value="<?php echo($item4->id); ?>"><?php echo htmlentities($item4->fullname, ENT_QUOTES, 'UTF-8'); ?></option>
                                                                <?php } ?>
                                                            <?php } ?>
                                                        <?php } ?>
                                                    </select>
                                                <?php } else { ?>
                                                    <input type="hidden" name="datainicio" value="<?php echo date('Y-m-d'); ?>">
                                                    <input type="hidden" name="datafim"    value="<?php echo date('Y-m-d'); ?>">
                                                    <input type="hidden" name="cliente"    value="0">
                                                    <input type="hidden" name="horario"    value="0">
                                                    <select style="display:none;" class="form-control" name="servico[]" multiple>
                                                        <option selected value="0">todos</option>
                                                    </select>
                                                <?php } ?>
                                                <button class="btn btn-warning btn-lg btn-block" name="mapa" type="submit">
                                                    <svg><use href="#icon-map"></use></svg><span>Voltar Para o Mapa</span>
                                                </button>
                                            </form>
                                        </div>
                                    <?php } else { ?>
                                        <div style="margin-top:8px;">
                                            <form action="map-visualizar-servico.php" method="post">
                                                <?php if(count($_SESSION['servico']) > 0) { ?>
                                                    <input type="hidden" name="datainicio" value="<?php echo($_SESSION['datainicio']); ?>">
                                                    <input type="hidden" name="datafim"    value="<?php echo($_SESSION['datafim']); ?>">
                                                    <input type="hidden" name="cliente"    value="<?php echo($_SESSION['cliente']); ?>">
                                                    <input type="hidden" name="horario"    value="<?php echo($_SESSION['horario']); ?>">
                                                    <select style="display:none;" class="form-control" name="servico[]" multiple>
                                                        <?php foreach ($listaServicos as $item4) { ?>
                                                            <?php for ($i = 0; $i <= count($_SESSION['servico']); $i++) { ?>
                                                                <?php if($item4->id == $_SESSION['servico'][$i]) { ?>
                                                                    <option selected value="<?php echo($item4->id); ?>"><?php echo htmlentities($item4->fullname, ENT_QUOTES, 'UTF-8'); ?></option>
                                                                <?php } ?>
                                                            <?php } ?>
                                                        <?php } ?>
                                                    </select>
                                                <?php } else { ?>
                                                    <input type="hidden" name="datainicio" value="<?php echo date('Y-m-d'); ?>">
                                                    <input type="hidden" name="datafim"    value="<?php echo date('Y-m-d'); ?>">
                                                    <input type="hidden" name="cliente"    value="0">
                                                    <input type="hidden" name="horario"    value="0">
                                                    <select style="display:none;" class="form-control" name="servico[]" multiple>
                                                        <option selected value="0">todos</option>
                                                    </select>
                                                <?php } ?>
                                                <button class="btn btn-warning btn-lg btn-block" name="mapa" type="submit">
                                                    <svg><use href="#icon-map"></use></svg><span>Voltar Para o Mapa</span>
                                                </button>
                                            </form>
                                        </div>
                                    <?php } ?>

                                </div>
                                <div class="tab-pane  p-20" id="profile" role="tabpanel">
                                    <div class="accordion" id="accordionExample">
                                        <div class="card">
                                            <div class="card-header" id="headingOne">
                                                <h2 class="mb-0">
                                                    <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseOne"
                                                            aria-controls="collapseOne">
                                                         Serviços Cadastrados
                                                    </button>
                                                </h2>
                                            </div>

                                            <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionExample">

                                                <div class="card-body">
                                                    <?php if( $contador > 0 ){ ?>
                                                        <h4>Serviços Adicionais</h4>
                                                        <div class="col-lg-12">
                                                            <?php foreach ($registro as $item){ ?>
                                                                <form action="" method="post">
                                                                    <div class="col-md-4 pull-left">
                                                                        <strong><label for="serviceAdd autocomplete">Serviço Contratado</label></strong>
                                                                        <select class="form-control" onchange="servicoselecionado1()" name="serviceAdd2">
                                                                            <?php foreach ($listaServicos as $item3){?>
                                                                                <?php if( $item->fullname == $item3->fullname ){ ?>
                                                                                    <option value="<?php echo htmlentities($item3->id, ENT_QUOTES, 'UTF-8'); ?>" selected>
                                                                                        <?php echo htmlentities($item3->fullname, ENT_QUOTES, 'UTF-8'); ?>
                                                                                    </option>
                                                                                <?php } else{?>
                                                                                    <option value="<?php echo( utf8_encode( $item3->id) ); ?>">
                                                                                        <?php echo( utf8_encode( $item3->fullname) ); ?>
                                                                                    </option>
                                                                                <?php }?>
                                                                            <?php }?>
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-md-4 pull-left">
                                                                        <strong><label for="valueserviceadd">Valor do Serviço</label></strong>
                                                                        <input  type="text" class="form-control" name="valueserviceadd" id="valueserviceadd"
                                                                                onKeyPress="return(moeda(this,'.',',',event))"
                                                                                value="<?php echo( number_format( $item->valueservice, 2, ",",
                                                                                    "." ) ); ?>" >
                                                                    </div>
                                                                    <div class="col-md-4 pull-right autocomplete">
                                                                        <strong><label for="documentoadd">Complemento|Observação</label></strong>
                                                                        <input  type="text" class="form-control" name="documentoadd" id="documentoadd"
                                                                                value="<?php echo( utf8_encode( $item->documento ) ); ?>" >
                                                                    </div>
                                                                    <div class="col-md-4 pull-left autocomplete">
                                                                        <strong><label for="qpax">Quantidade Passageiros<span style="font-size: 12px; color: black;"> (Adulto entre 10 a 64 Anos, 11 meses e 29 dias ou Idoso 65+)</span></label></strong>
                                                                        <input  type="number" class="form-control" name="qpax" id="qpax"
                                                                                value="<?php echo( utf8_encode( $item->qpax ) ); ?>" >
                                                                    </div>
                                                                    <div class="col-md-4 pull-left autocomplete">
                                                                        <strong><label for="qchild">Quantidade Meia<span style="font-size: 12px; color: black;"> (Entre 5 a 9 anos, 11 meses e 29 dias)</span></label></strong>
                                                                        <input  type="number" class="form-control" name="qchild" id="qchild"
                                                                                value="<?php echo( utf8_encode( $item->qchild ) ); ?>" >
                                                                    </div>
                                                                    <div class="col-md-4 pull-right autocomplete">
                                                                        <strong><label for="qfree">Quantidade Free<span style="font-size: 10px; color: red;; font-size: 10px;color: red;"> (Free ou entre 0 a 4 anos, 11 meses e 29 dias)</span></label></strong>
                                                                        <input  type="number" class="form-control" name="qfree" id="qfree"
                                                                                value="<?php echo( utf8_encode( $item->qfree ) ); ?>" >
                                                                    </div>
                                                                    <div class="col-md-4 pull-left">
                                                                        <strong><label for="horaapadd">Horário de Apresentação</label></strong>
                                                                        <input  type="time" class="form-control" name="horaapadd" id="horaapadd"
                                                                                value="<?php echo( $item->horaap ); ?>">
                                                                    </div>
                                                                    <div class="col-md-4 pull-left">
                                                                        <strong><label for="horaapadd">Horário de Embarque</label></strong>
                                                                        <select class="form-control"  name="horarioembarque" id="horario1">
                                                                            <?php foreach ($listaHorarios as $horariosEmbarque){  ?>
                                                                                <?php if( $item->schedule == $horariosEmbarque->schedule ){ ?>
                                                                                    <option value="<?php echo($horariosEmbarque->idshedule); ?>" selected>
                                                                                        <?php echo($horariosEmbarque->schedule); ?>
                                                                                    </option>
                                                                                <?php }else{?>
                                                                                    <option value="<?php echo($horariosEmbarque->idshedule); ?>">
                                                                                        <?php echo($horariosEmbarque->schedule); ?>
                                                                                    </option>
                                                                                <?php }?>
                                                                            <?php }?>
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-md-4 pull-right">
                                                                        <strong><label for="schedule">Data Embarque </label></strong>
                                                                        <input  type="date" class="form-control" name="datainicio" id="datainicio"
                                                                                value="<?php echo( $item->dateinput ); ?>">
                                                                    </div>
                                                                    <input type="hidden" value="<?php echo( $item->id); ?>" name="idAdd">
                                                                    <div class="container-fluid">
                                                                        <?php if( $item->dateinput == date("Y-m-d") ){ ?>
                                                                            <?php if($item->confirmacao2 == 1){ ?>
                                                                                <label for="confirmarhorarioembarque2"><input type="checkbox" checked name="confirmarhorarioembarque2" id="confirmarhorarioembarque2"> Confirmar Horário de Embarque ?</label>
                                                                            <?php } else {?>
                                                                                <label for="confirmarhorarioembarque2"><input type="checkbox" name="confirmarhorarioembarque2" id="confirmarhorarioembarque2"> Confirmar Horário de Embarque ?</label>
                                                                            <?php }?>
                                                                        <?php }?>

                                                                    </div>

                                                                    <div class="col-md-6 pull-left">
                                                                        <input type="hidden" name="voucher" value="<?php echo($dadosGerais['numbervoucher']); ?>" >
                                                                        <input type="hidden" name="valor2" value="<?php echo($item->valueservice); ?>" >
                                                                        <button class="btn btn-success btn-lg btn-block" id="salvarfatura" name="serviceadd" type="submit">
                                                                            <svg><use href="#icon-refresh"></use></svg><span>Atualizar Serviço Adicional</span></button>

                                                                    </div>
                                                                    <div class="col-md-6 pull-right">
                                                                        <input type="hidden" name="voucher" value="<?php echo($dadosGerais['numbervoucher']); ?>" >
                                                                        <button class="btn btn-danger btn-lg btn-block" name="deleteserviceadd" type="submit">
                                                                            <svg><use href="#icon-trash"></use></svg><span>Excluir Serviço Adicional</span></button>
                                                                    </div>
                                                                </form>
                                                            <?php }?>
                                                        </div>
                                                    <?php }else{?>
                                                        <div class="alert alert-warning" role="alert">Não há serviços adicionais.</div>
                                                    <?php }?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card">
                                            <div class="card-header" id="headingTwo">
                                                <h2 class="mb-0">
                                                    <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseTwo"
                                                            aria-expanded="true" aria-controls="collapseTwo">
                                                        Novo Serviço
                                                    </button>
                                                </h2>
                                            </div>
                                            <div id="collapseTwo" class="collapse show" aria-labelledby="headingTwo" data-parent="#accordionExample">
                                                <div class="card-body">
                                                    <div class="col-lg-12">
                                                        <form method="post" action="">
                                                            <div class="col-md-6 pull-left">
                                                                <strong><label for="datainicio">Data de Embarque</label></strong>
                                                                <input required type="date" name="datainicio" id="datainicio" class="form-control"
                                                                       value="<?php echo(date("Y-m-d")); ?>">
                                                            </div>
                                                            <div class="col-md-6 pull-right">
                                                                <strong><label for="servico">Serviço Contratado</label></strong>
                                                                <select  name="servico" id="servico" onchange="servicoselecionado2()" class="form-control" required>
                                                                    <option selected value="3" >Selecione o Serviço</option>
                                                                    <?php foreach ($listaServicos as $listaServico) {?>
                                                                        <option value="<?php echo($listaServico->id); ?>">
                                                                            <?php echo(utf8_encode($listaServico->fullname)); ?>
                                                                        </option>
                                                                    <?php }?>
                                                                </select>
                                                            </div>
                                                            <div class="col-md-6 pull-right">
                                                                <strong><label for="documento">Complemento|Observação</label></strong>
                                                                <input  type="text" name="documento" id="documento" class="form-control">
                                                            </div>
                                                            <div class="col-md-6 pull-right">
                                                                <strong><label for="valorservico">Valor do Serviço</label></strong>
                                                                <input type="text" onKeyPress="return(moeda(this,'.',',',event))"
                                                                       required name="valorservico" id="valorservico"  class="form-control">
                                                            </div>
                                                            <div class="col-md-6 pull-left">
                                                                <strong><label for="quantidadepax">Quantidade Pessoas (pax)</label></strong>
                                                                <input value="1" type="number" name="quantidadepax" class="form-control">
                                                            </div>
                                                            <div class="col-md-6 pull-right">
                                                                <strong><label for="quantidadechild">Quantidade Meia<span style="font-size: 12px; color: black;"> (5 a 6 anos e 11 meses)</span></label></strong>
                                                                <input value="0" type="number" name="quantidadechild" class="form-control">
                                                            </div>
                                                            <div class="col-md-4 pull-left">
                                                                <strong><label for="quantidadepax">Quantidade Free<span style="font-size: 10px;"> Free ou entre 0 a 4 anos, 11 meses e 29 dias)</span></label></strong>
                                                                <input value="0" type="number" name="quantidadefree" class="form-control">
                                                            </div>
                                                            <div class="col-md-4 pull-left">
                                                                <strong><label for="horariobusca">Horário de Apresentação</label></strong>
                                                                <input type="time" required name="horariobusca" id="horariobusca" class="form-control">
                                                            </div>
                                                            <div class="col-md-4 pull-right">
                                                                <strong><label for="horario">Horário de Embarque</label></strong>
                                                                <select required name="horario" id="horario2" class="form-control">
                                                                    <?php foreach ($listaHorarios as $horariosEmbarque){  ?>
                                                                        <option value="<?php echo($horariosEmbarque->idshedule); ?>">
                                                                            <?php echo($horariosEmbarque->schedule); ?>
                                                                        </option>
                                                                    <?php }?>
                                                                </select>
                                                            </div>
                                                            <input type="hidden" value="<?php echo($dadosGerais['numbervoucher']); ?>" name="voucher">
                                                            <div class="form-group container-fluid">
                                                                <button type="submit" class="btn btn-primary btn-lg btn-block" id="salvarfatura" name="vincular">
                                                                    <svg><use href="#icon-link"></use></svg><span>Vincular Serviço</span>
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                </div>
                                <div class="tab-pane p-20" id="messages" role="tabpanel">
                                    <?php if( $contadorCredito > 0 ){ $total_credito_pago = 0; ?>
                                        <div class="col-lg-12">
                                            <h4 style="margin-top: 20px;">Créditos Adicionados</h4>
                                            <hr>

                                            <div class="table-responsive">
                                                <table class="table table-bordered">
                                                    <thead>
                                                    <tr>
                                                        <th>Forma de Pagamento</th>
                                                        <th>Data Crédito</th>
                                                        <th>Valor do Crédito</th>
                                                        <th>Atualizar Valor</th>
                                                        <th>Remover Valor</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <?php foreach ( $registroCredito as $item ){ $total_credito_pago += $item->valuecredit; ?>
                                                        <?php if( $item->valuecredit > 0 ){ ?>
                                                            <form action="" method="post">
                                                               <tr>
    <td>
        <select class="form-control" name="pagamento" id="pagamento">
            <?php foreach ($registroCc as $item4) { ?>
                <?php if ($item4->name == $item->name) { ?>
                    <option selected value="<?php echo $item4->id; ?>">
                        <?php echo $item4->name; ?>
                    </option>
                <?php } else { ?>
                    <option value="<?php echo $item4->id; ?>">
                        <?php echo $item4->name; ?>
                    </option>
                <?php } ?>
            <?php } ?>
        </select>
    </td>
                                                                    <td><?php echo( date("d-m-Y", strtotime($item->datacredit)) ); ?></td>
                                                                    <td>
                                                                        <div class="input-group mb-3">
                                                                            <div class="input-group-prepend">
                                                                                <span class="input-group-text" id="basic-addon1">R$</span>
                                                                            </div>
                                                                            <input type="text" class="form-control" onKeyPress="return(moeda(this,'.',',',event))"
                                                                                   value="<?php echo( number_format( $item->valuecredit, 2,",",
                                                                                       "." )  );  ?>"
                                                                                   name="valor">
                                                                        </div>
                                                                    </td>

                                                                    <td>
                                                                        <input type="hidden" name="idcredit" value="<?php echo($item->id); ?>" >
                                                                        <input type="hidden" name="voucher" value="<?php echo($dadosGerais['numbervoucher']); ?>" >
                                                                        <button style="margin-bottom: 15px;" type="submit" name="updatecredit" id="salvarfatura"
                                                                                class="btn btn-success btn-block"><svg><use href="#icon-refresh"></use></svg><span>Atualizar Valor</span></button>
                                                                    <td>
                                                                        <button style="margin-bottom: 15px;" type="submit" name="deletecredit"
                                                                                class="btn btn-danger btn-block"><svg><use href="#icon-trash"></use></svg><span>Remover Valor</span></button>
                                                                    </td>
                                                                </tr>
                                                            </form>

                                                        <?php } ?>

                                                    <?php }
                                                    $pdate_credito_pago = $pdo->prepare("UPDATE `ct_reserva` SET `totalcredito` = :totalcredito WHERE `ct_reserva`.`numbervoucher` = :nv");
                                                    $pdate_credito_pago->execute(array(":totalcredito" => $total_credito_pago, ":nv" => $numberVoucher ));

                                                    ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <h4 style="margin-top: 20px;">Adicionar Crédito</h4>
                                            <?php if( !empty($_SESSION['idgerente'] ) or $_SESSION['id'] == 30 or $_SESSION['id'] == 304){ ?>
                                                <form action="" method="post">
                                                    <input type="hidden" name="desc" id="desc" class="form-control" value="Crédito Pago">
                                                    <div class="col-md-3 pull-left">
                                                        <strong><label for="valordocredito">Valor do Crédito </label></strong>
                                                        <div class="input-group mb-3">
                                                            <div class="input-group-prepend" style="width: 100%;">
                                                                <span class="input-group-text" id="basic-addon1">R$</span>
                                                                <input type="text" onKeyPress="return(moeda(this,'.',',',event))"
                                                                       name="valordocredito" id="valordocredito" class="form-control" >
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 pull-left">
                                                        <strong><label for="valordocredito">Data do Pagamento </label></strong>
                                                        <input type="date" name="datacredito" id="datacredito" class="form-control">
                                                    </div>
                                                    <div class="col-md-3 pull-left">
                                                        <strong><label for="ccfp">Responsável </label></strong>
                                                        <select class="form-control" name="responsavel" id="responsavel" required>
                                                            <?php foreach ( $dados_buscarResponsavel_todos as $item_usuario ){ ?>
                                                                <option value="<?php echo($item_usuario->id); ?>"><?php echo(utf8_encode($item_usuario->firstname." ".$item_usuario->lastname)); ?></option>
                                                            <?php }?>
                                                            <option value="0" selected> Selecione</option>
                                                        </select>
                                                    </div>
<div class="col-md-3 pull-right">
    <strong><label for="ccfp">Forma de Pagamento</label></strong>
    <select class="form-control" name="ccfp" id="ccfp" required>
        <?php foreach ($registroCc as $itemC) { ?>
            <option value="<?php echo $itemC->id; ?>"><?php echo $itemC->name; ?></option>
        <?php } ?>
        <option value="0" selected>Selecione</option>
    </select>
</div>
                                                    <div class="container-fluid pull-left">
                                                        <input type="hidden" name="voucher" value="<?php echo($dadosGerais['numbervoucher']); ?>" >
                                                        <input type="hidden" name="idcliente" value="<?php echo($dadosGerais['idcliente']); ?>" >
                                                        <button type="submit" class="btn btn-success btn-lg" name="Addcredito" id="salvarfatura"><svg><use href="#icon-save"></use></svg><span>Salvar Crédito</span></button>
														<br>
														<br>
														<br>
                                                    </div>
                                                </form>
                                            <?php } else {?>
                                                <form action="" method="post">
                                                    <input type="hidden" name="desc" id="desc" class="form-control" value="Crédito Pago">
                                                    <div class="col-md-6 pull-left">
                                                        <strong><label for="valordocredito">Valor do Crédito </label></strong>
                                                        <div class="input-group mb-3">
                                                            <div class="input-group-prepend" style="width: 100%;">
                                                                <span class="input-group-text" id="basic-addon1">R$</span>
                                                                <input type="text" onKeyPress="return(moeda(this,'.',',',event))"
                                                                       name="valordocredito" id="valordocredito" class="form-control" >
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 pull-right">
                                                        <strong><label for="ccfp">Forma de Pagamento</label></strong>
                                                        <select class="form-control" name="ccfp" id="ccfp" required>
                                                            <?php foreach ( $registroCc as $itemC ){ ?>
                                                                <option value="<?php echo($itemC->id); ?>"><?php echo(utf8_encode($itemC->name)); ?></option>
                                                            <?php }?>
                                                            <option value="0" selected> Selecione</option>
                                                        </select>
                                                    </div>
                                                    <div class="container-fluid pull-left">
                                                        <input type="hidden" name="voucher" value="<?php echo($dadosGerais['numbervoucher']); ?>" >
                                                        <input type="hidden" name="responsavel" value="<?php echo($_SESSION['id']); ?>" >
                                                        <input type="hidden" name="idcliente" value="<?php echo($dadosGerais['idcliente']); ?>" >
                                                        <input type="hidden" name="datacredito" id="datacredito"
                                                               class="form-control" value="<?php echo(date('Y-m-d')); ?>" >
                                                        <button type="submit" class="btn btn-success btn-lg" name="Addcredito" id="salvarfatura"><svg><use href="#icon-save"></use></svg><span>Salvar Crédito</span></button>
                                                    </div>
                                                </form>
                                            <?php }?>
                                        </div>
                                    <?php }
                                    else{ ?>
                                        <div class="col-md-12">
                                            <h4 style="margin-top: 20px;">Adicionar Crédito</h4>
                                            <?php if( !empty($_SESSION['idgerente'] ) or $_SESSION['id'] == 30  or $_SESSION['id'] == 304){ ?>
                                                <form action="" method="post">
                                                    <input type="hidden" name="desc" id="desc" class="form-control" value="Crédito Pago">
                                                    <div class="col-md-3 pull-left">
                                                        <strong><label for="valordocredito">Valor do Crédito </label></strong>
                                                        <div class="input-group mb-3">
                                                            <div class="input-group-prepend" style="width: 100%;">
                                                                <span class="input-group-text" id="basic-addon1">R$</span>
                                                                <input type="text" onKeyPress="return(moeda(this,'.',',',event))"
                                                                       name="valordocredito" id="valordocredito" class="form-control" >
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 pull-left">
                                                        <strong><label for="valordocredito">Data do Pagamento </label></strong>
                                                        <input type="date" name="datacredito" id="datacredito" class="form-control">
                                                    </div>
                                                    <div class="col-md-3 pull-left">
                                                        <strong><label for="ccfp">Responsável </label></strong>
                                                        <select class="form-control" name="responsavel" id="responsavel" required>
                                                            <?php foreach ( $dados_buscarResponsavel_todos as $item_usuario ){ ?>
                                                                <option value="<?php echo($item_usuario->id); ?>"><?php echo(utf8_encode($item_usuario->firstname." ".$item_usuario->lastname)); ?></option>
                                                            <?php }?>
                                                            <option value="0" selected> Selecione</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-3 pull-right">
                                                        <strong><label for="ccfp">Forma de Pagamento</label></strong>
                                                        <select class="form-control" name="ccfp" id="ccfp" required>
                                                            <?php foreach ( $registroCc as $itemC ){ ?>
                                                                <option value="<?php echo($itemC->id); ?>"><?php echo(utf8_encode($itemC->name)); ?></option>
                                                            <?php }?>
                                                            <option value="0" selected> Selecione</option>
                                                        </select>
                                                    </div>
                                                    <div class="container-fluid pull-left">
                                                        <input type="hidden" name="voucher" value="<?php echo($dadosGerais['numbervoucher']); ?>" >
                                                        <input type="hidden" name="idcliente" value="<?php echo($dadosGerais['idcliente']); ?>" >
                                                        <button type="submit" class="btn btn-success btn-lg" name="Addcredito" id="salvarfatura"><svg><use href="#icon-save"></use></svg><span>Salvar Crédito</span></button>
                                                    </div>
                                                </form>
                                            <?php } else {?>
                                                <form action="" method="post">
                                                    <input type="hidden" name="desc" id="desc" class="form-control" value="Crédito Pago">
                                                    <div class="col-md-6 pull-left">
                                                        <strong><label for="valordocredito">Valor do Crédito </label></strong>
                                                        <div class="input-group mb-3">
                                                            <div class="input-group-prepend" style="width: 100%;">
                                                                <span class="input-group-text" id="basic-addon1">R$</span>
                                                                <input type="text" onKeyPress="return(moeda(this,'.',',',event))"
                                                                       name="valordocredito" id="valordocredito" class="form-control" >
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 pull-right">
                                                        <strong><label for="ccfp">Forma de Pagamento</label></strong>
                                                        <select class="form-control" name="ccfp" id="ccfp" required>
                                                            <?php foreach ( $registroCc as $itemC ){ ?>
                                                                <option value="<?php echo($itemC->id); ?>"><?php echo(utf8_encode($itemC->name)); ?></option>
                                                            <?php }?>
                                                            <option value="0" selected> Selecione</option>
                                                        </select>
                                                    </div>
                                                    <div class="container-fluid pull-left">
                                                        <input type="hidden" name="voucher" value="<?php echo($dadosGerais['numbervoucher']); ?>" >
                                                        <input type="hidden" name="responsavel" value="<?php echo($_SESSION['id']); ?>" >
                                                        <input type="hidden" name="idcliente" value="<?php echo($dadosGerais['idcliente']); ?>" >
                                                        <input type="hidden" name="datacredito" id="datacredito"
                                                               class="form-control" value="<?php echo(date('Y-m-d')); ?>" >
                                                        <button type="submit" class="btn btn-success btn-lg" name="Addcredito" id="salvarfatura"><svg><use href="#icon-save"></use></svg><span>Salvar Crédito</span></button>
														<br>
														<br>
                                                    </div>
                                                </form>
                                            <?php }?>
                                        </div>
                                    <?php }?>
                                </div>
                                <div class="tab-pane p-20" id="despesas" role="tabpanel">
                                    <?php if($contadorDespesa > 0){ $contadorservicec = 0; ?>
                                        <div class="col-lg-12">
                                            <h4 style="margin-top: 20px;">Comissão</h4>
                                            <hr>

                                            <div class="table-responsive">
                                                <form method="post" action="" class="form-inline">
                                                    <table class="table table-bordered">
                                                        <thead>
                                                        <tr>
                                                            <th>Data Pagamento</th>
                                                            <th>Serviço</th>
                                                            <th>Pago por</th>
                                                            <th>Valor</th>
                                                            <th>#</th>
                                                            <?php if($_SESSION['id'] == 1 or $_SESSION['id'] == 2 or $_SESSION['id'] == 30 ){ ?>
                                                                <th>#</th>
                                                            <?php }?>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        <?php foreach ($despesa as $item){
                                                            $nomePagador = $pdo->prepare(
                                                                'select a.description , u.firstname from `ct_audit` a left join `ct_usuario` u 
                                                            on u.id = a.idresponsible where description like \'%foi paga ao%\' and voucher = :voucher
                                                            and `date` >= :inn and `date` <= :outt ');
                                                            $nomePagador->execute(
                                                                array(
                                                                    ":voucher" => $item->numbervoucher,
                                                                    ":inn"  => date("Y-m-d", strtotime($item->dataagente))." 00:00:00",
                                                                    ":outt" => date("Y-m-d", strtotime($item->dataagente))." 23:59:59"));
                                                            $dadosPagador = $nomePagador->fetch(PDO::FETCH_ASSOC);
                                                            ?>
                                                            <tr>
                                                                <td><?php echo( date("d-m-Y", strtotime($item->dataagente)) ); ?></td>
                                                                <?php if($contadorservicec == 0){ ?>
                                                                    <?php foreach ($listaServicos as $items){ ?>
                                                                        <?php if($dadosGerais['serivco'] == $items->fullname ){ ?>
                                                                            <td><?php echo($items->fullname); ?></td>
                                                                        <?php }?>
                                                                    <?php }?>
                                                                <?php } else{ ?>
                                                                    <?php foreach ($registro as $items2){ ?>
                                                                        <?php if( $items2->idservico <> 19 and $items2->idservico <> 30
                                                                            and $items2->idservico <> 47 and $items2->idservico <> 48
                                                                            and $items2->idservico <> 17 and $items2->idservico <> 18 and $items2->idservico <> 31
                                                                            and $items2->idservico <> 53
                                                                            and $items2->idservico <> 155){ ?>
                                                                            <td><?php echo($items2->fullname); ?></td>
                                                                        <?php }?>
                                                                    <?php }?>
                                                                <?php }?>


                                                                <td><?php echo($dadosPagador['firstname']); ?></td>
                                                                <td>
                                                                    <div class="input-group mb-3">
                                                                        <div class="input-group-prepend">
                                                                            <span class="input-group-text" id="basic-addon1">R$</span>
                                                                        </div>
                                                                        <input type="text" class="form-control"
                                                                               value="<?php echo( number_format( $item->valueagente, 2,",",
                                                                                   "." )  );  ?>"
                                                                               name="valor" style="margin-right: 20px;">
                                                                    </div>

                                                                    <input type="hidden" name="iddespesa" value="<?php echo($item->id); ?>" >
                                                                    <input type="hidden" name="voucher" value="<?php echo($dadosGerais['numbervoucher']); ?>" >
                                                                </td>
                                                                <td>
                                                                    <button style="margin-bottom: 15px;" type="submit" name="updatedes"
                                                                            class="btn btn-success btn-block"><svg><use href="#icon-refresh"></use></svg><span>Atualizar Valor</span></button>
                                                                </td>
                                                                <?php if($_SESSION['id'] == 1 or $_SESSION['id'] == 2 or $_SESSION['id'] == 30 ){ ?>
                                                                    <td>
                                                                        <?php if( !empty($_SESSION['idoperador']) or !empty($_SESSION['idreservamanager'])
                                                                            or !empty($_SESSION['idreservaplus'])){ ?>
                                                                            <?php if( $dadosGerais['idstatusinvoice'] >= 3 and $dadosGerais['idstatusinvoice'] <= 5
                                                                                and $_SESSION['id'] <> 46 and $_SESSION['id'] <> 32 and $_SESSION['id'] != 57 ){ ?>
                                                                                <button  type="button" class="btn btn-success disabled ">
                                                                                    <svg><use href="#icon-check"></use></svg><span>Não é possivel cancelar a comissão</span></button>
                                                                            <?php }else{ ?>
                                                                                <button style="margin-bottom: 15px;" type="submit" name="deletecomissao"
                                                                                        class="btn btn-warning btn-block"><svg><use href="#icon-trash"></use></svg><span>Cancelar Pagamento</span></button>
                                                                            <?php }?>
                                                                        <?php } else{ ?>
                                                                            <button style="margin-bottom: 15px;" type="submit" name="deletecomissao"
                                                                                    class="btn btn-warning btn-block"><svg><use href="#icon-trash"></use></svg><span>Cancelar Pagamento</span></button>
                                                                        <?php }?>
                                                                    </td>
                                                                <?php }?>
                                                            </tr>
                                                            <?php $contadorservicec += 1; }?>

                                                        </tbody>
                                                    </table>
                                                </form>
                                                <br>
                                               
                                                <?php if( !empty( $_SESSION['idreservaplus']) or !empty( $_SESSION['idgerente'])
                                                    or !empty($_SESSION['idreservamanager'] ) or !empty($_SESSION['idfaturador'] ) or $_SESSION['id'] == 273 or $_SESSION['id'] == 225 ){ ?>
                                                                  <form action="relatorio/pdf-relatorio-comissao-agente.php" target="_blank" method="post">
                                                            <div class="col-lg-4 pull-left">
                                                                <strong><label for="nomeagente">Descrição</label></strong>
                                                                <input style="margin-bottom: 15px;" type="text" name="nomeagente" id="nomeagente" class="form-control">
                                                            </div>
                                                            <div class="col-lg-4 pull-left">
                                                                <strong><label for="comissaoservico">Serviço</label></strong>
                                                                <?php foreach ($listaServicos as $items){ ?>
                                                                    <?php if($dadosGerais['serivco'] == $items->fullname ){ ?>
                                                                        <input style="margin-bottom: 15px;" disabled type="text" name="comissaoservico"
                                                                               id="comissaoservico" class="form-control" value="<?php echo($items->fullname); ?>">
                                                                    <?php }?>
                                                                <?php }?>
                                                            </div>
                                                            <div class="col-lg-4 pull-right">
                                                                <strong><label for="valoragente">Valor</label></strong>
                                                                <div class="input-group mb-3">
                                                                    <div class="input-group-prepend">
                                                                        <span class="input-group-text" id="basic-addon1">R$</span>
                                                                        <input required type="text" name="valoragente" onKeyPress="return(moeda(this,'.',',',event))"
                                                                               id="valoragente" class="form-control">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="container-fluid">
                                                                <input type="hidden" class="form-control" name="voucher"
                                                                       value="<?php echo($dadosGerais['numbervoucher']); ?>" >
                                                                <button type="submit" class="btn btn-success btn-lg" name="comissaoagente"
                                                                        id="comissaoagente"><svg><use href="#icon-check"></use></svg><span>Confirmar Pagamento</span></button>
                                                            </div>
                                                        </form>
                                                        

                                                <?php }?>
                                            </div>
                                        </div>
                                    <?php }
                                    else{ ?>
                                        <div class="alert alert-warning" role="alert">Não há pagamentos de comissão cadastrado</div>
                                        <?php if( !empty( $_SESSION['idreservaplus']) or !empty( $_SESSION['idgerente'])
                                            or !empty($_SESSION['idreservamanager'] ) or !empty($_SESSION['idfaturador'] or $_SESSION['id'] == 36 ) ){?>
                                            <?php if ($contadorDespesa == 0){ ?>
                                                <form action="relatorio/pdf-relatorio-comissao-agente.php" target="_blank" method="post">
                                                    <div class="col-lg-4 pull-left">
                                                        <strong><label for="nomeagente">Descrição</label></strong>
                                                        <input style="margin-bottom: 15px;" type="text" name="nomeagente" id="nomeagente" class="form-control">
                                                    </div>
<div class="col-lg-4 pull-left">
    <strong><label for="comissaoservico">Serviço</label></strong>
    <?php foreach ($listaServicos as $items) { ?>
        <?php if ($dadosGerais['serivco'] == $items->fullname) { ?>
            <input style="margin-bottom: 15px;" type="text" name="comissaoservico"
                   id="comissaoservico" class="form-control" value="<?php echo (utf8_encode($items->fullname)); ?>">
        <?php } ?>
    <?php } ?>
</div>
                                                    <div class="col-lg-4 pull-right">
                                                        <strong><label for="valoragente">Valor</label></strong>
                                                        <div class="input-group mb-3">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text" id="basic-addon1">R$</span>
                                                                <input required type="text" name="valoragente" id="valoragente" class="form-control">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="container-fluid">
                                                        <input type="hidden" class="form-control" name="voucher"
                                                               value="<?php echo($dadosGerais['numbervoucher']); ?>" >
                                                        <button type="submit" class="btn btn-success btn-lg" name="comissaoagente"
                                                                id="comissaoagente"><svg><use href="#icon-check"></use></svg><span>Confirmar Pagamento</span></button>
                                                    </div>
                                                </form>
                                                <?php if( $contador > 0 ){ ?>
                                                    <?php foreach ($registro as $items2){ ?>
                                                        <?php if( $items2->idservico <> 19 and $items2->idservico <> 30 and $items2->idservico <> 47 and $items2->idservico <> 48
                                                            and $items2->idservico <> 17 and $items2->idservico <> 18 and $items2->idservico <> 31 and $items2->idservico <> 53
                                                            and $items2->idservico <> 155){ ?>
                                                            <form action="relatorio/pdf-relatorio-comissao-agente.php" target="_blank" method="post">
                                                                <div class="col-lg-4 pull-left">
                                                                    <strong><label for="nomeagente">Descrição</label></strong>
                                                                    <input style="margin-bottom: 15px;" type="text" name="nomeagente" id="nomeagente" class="form-control">

                                                                </div>
                                                                <div class="col-lg-4 pull-left">
                                                                    <strong><label for="comissaoservico">Serviço</label></strong>
                                                                    <input style="margin-bottom: 15px;"  type="text" name="comissaoservico"
                                                                           id="comissaoservico" class="form-control" value="<?php echo($items2->fullname); ?>">
                                                                </div>
                                                                <div class="col-lg-4 pull-right">
                                                                    <strong><label for="valoragente">Valor</label></strong>
                                                                    <div class="input-group mb-3">
                                                                        <div class="input-group-prepend">
                                                                            <span class="input-group-text" id="basic-addon1">R$</span>
                                                                            <input required type="text" onKeyPress="return(moeda(this,'.',',',event))"
                                                                                   name="valoragente" id="valoragente" class="form-control">
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="container-fluid">
                                                                    <input type="hidden" class="form-control" name="voucher"
                                                                           value="<?php echo($dadosGerais['numbervoucher']); ?>" >
                                                                    <button type="submit" class="btn btn-success btn-lg " name="comissaoagente"
                                                                            id="comissaoagente"><svg><use href="#icon-check"></use></svg><span>Confirmar Pagamento</span></button>
                                                                </div>
                                                            </form>
                                                        <?php }?>
                                                    <?php }?>
                                                <?php }?>
                                            <?php } else { ?>
                                                <?php if($contadorDespesa <= $contador){ ?>
                                                    <?php if( $contador > 0 ){ ?>
                                                        <?php foreach ($registro as $items2){ ?>
                                                            <?php if( $items2->idservico <> 19 and $items2->idservico <> 30 and $items2->idservico <> 47
                                                                    and $items2->idservico <> 48
                                                                and $items2->idservico <> 17 and $items2->idservico <> 18 and $items2->idservico <> 31
                                                                and $items2->idservico <> 53
                                                                and $items2->idservico <> 155){ ?>
                                                                <form action="relatorio/pdf-relatorio-comissao-agente.php" target="_blank" method="post">
                                                                    <div class="col-lg-4 pull-left">
                                                                        <strong><label for="nomeagente">Descrição</label></strong>
                                                                        <input style="margin-bottom: 15px;" type="text" name="nomeagente" id="nomeagente" class="form-control">

                                                                    </div>
                                                                    <div class="col-lg-4 pull-left">
                                                                        <strong><label for="comissaoservico">Serviço</label></strong>
                                                                        <input style="margin-bottom: 15px;"  type="text" name="comissaoservico"
                                                                               id="comissaoservico" class="form-control" value="<?php echo($items2->fullname); ?>">
                                                                    </div>
                                                                    <div class="col-lg-4 pull-right">
                                                                        <strong><label for="valoragente">Valor</label></strong>
                                                                        <div class="input-group mb-3">
                                                                            <div class="input-group-prepend">
                                                                                <span class="input-group-text" id="basic-addon1">R$</span>
                                                                                <input required type="text" onKeyPress="return(moeda(this,'.',',',event))"
                                                                                       name="valoragente" id="valoragente" class="form-control">
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div class="container-fluid">
                                                                        <input type="hidden" class="form-control" name="voucher"
                                                                               value="<?php echo($dadosGerais['numbervoucher']); ?>" >
                                                                        <button type="submit" class="btn btn-success btn-lg " name="comissaoagente"
                                                                                id="comissaoagente"><svg><use href="#icon-check"></use></svg><span>Confirmar Pagamento</span></button>
                                                                    </div>
                                                                </form>
                                                            <?php }?>
                                                        <?php }?>
                                                    <?php }?>
                                                <?php }?>
                                            <?php }?>

                                        <?php }?>
                                    <?php }?>
                                </div>
                                <div class="tab-pane p-20" id="auditoria" role="tabpanel">
                                    <?php if( $contadorAuditoria > 0  ){ ?>
                                        <hr>
                                        <h4>Auditoria do  Voucher <?php echo($dadosGerais['numbervoucher']); ?></h4>
                                        <div class="table-responsivo">
                                            <table class="table table-bordered">
                                                <thead>
                                                <th scope="row">Data</th>
                                                <th scope="row">Descrição</th>
                                                <th scope="row">Responsável</th>
                                                </thead>
                                                <tbody>
                                                <?php foreach ($registroAu as $item) {
                                                    $buscarResponsavel = $pdo->prepare('select * from `ct_usuario` where `id` = :id');
                                                    $buscarResponsavel->execute(array(":id" => $item->idresponsible));
                                                    $dados = $buscarResponsavel->fetch(PDO::FETCH_ASSOC);
                                                    $timestamp = strtotime($item->date);
                                                    ?>
                                                    <tr>
                                                        <td><?php echo( date("d-m-Y - H:i:s", $timestamp) ); ?></td>
                                                        <td><?php echo(  $item->description." (". $dados['firstname']." ".$dados['lastname'].")"); ?></td>
                                                        <td><?php echo( $dados['firstname']." ".$dados['lastname']); ?></td>
                                                    </tr>
                                                <?php }?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php }?>
                                </div>
                                <div class="tab-pane p-20" id="fatura" role="tabpanel">
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
                                                    <th>Editar</th>
                                                    <th>Excluir</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php while( $registroAdm = $administrativo->fetch(PDO::FETCH_ASSOC) ){ ?>
                                                    <tr>
                                                        <td><?php echo( date('d-m-Y', strtotime($registroAdm['datematurity'])) ); ?></td>
                                                        <td><?php echo( date('d-m-Y', strtotime($registroAdm['datepayment'])) ); ?></td>
                                                        <td><?php echo( utf8_encode($registroAdm['numberadd']) ); ?></td>
                                                        <td><?php echo( utf8_encode($registroAdm['name']) ); ?></td>
                                                        <td>
                                                            <form action="" method="post">
                                                                <input name="idfatura" type="hidden" value="<?php echo($registroAdm['id']); ?>" >
                                                                <input name="voucher" type="hidden" value="<?php echo($dadosGerais['numbervoucher']); ?>" >
                                                                <button type="submit" name="adm" class="action-icon-button"><svg><use href="#icon-edit"></use></svg><span>Editar</span></button>
                                                            </form>
                                                        </td>
                                                        <td>
                                                            <form action="" method="post">
                                                                <input name="idfatura" type="hidden" value="<?php echo($registroAdm['id']); ?>" >
                                                                <input name="voucher" type="hidden" value="<?php echo($dadosGerais['numbervoucher']); ?>" >
                                                                <button type="submit" name="excluirfatura" class="action-icon-button"><svg><use href="#icon-trash"></use></svg><span>Excluir</span></button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                <?php }?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php } else{ ?>
                                        <div class="alert alert-warning" role="alert">Não há faturas cadastradas.</div>
                                    <?php }?>

                                </div>
                                <div class="tab-pane p-20" id="creditofatura" role="tabpanel">
                                    <div class="accordion" id="accordionExample">
                                        <div class="card">
                                            <div class="card-header" id="headingOne">
                                                <h2 class="mb-0">
                                                    <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseOne1"
                                                            aria-expanded="true" aria-controls="collapseOne1">
                                                        Cadastrar Nova Fatura
                                                    </button>
                                                </h2>
                                            </div>
                                            <div id="collapseOne1" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionExample">
                                                <div class="card-body">
                                                    <form action="" method="post">
                                                        <div class="col-md-4 pull-left">
                                                            <label for="data">Data</label>
                                                            <input type="date" required name="datapagamento" id="data" class="form-control"
                                                                   value="<?php echo( date("Y-m-d") ); ?>">
                                                        </div>
                                                        <div class="col-md-4 pull-left">
                                                            <label for="valor">Valor</label>
                                                            <input type="text" required name="valor" id="valor" class="form-control" value="0,00">
                                                        </div>
                                                        <div class="col-md-4 pull-right">
                                                            <label for="descricao">Descrição</label>
                                                            <input type="text"  name="descricao" id="descricao" class="form-control" required>
                                                        </div>

                                                        <div class="col-md-6 pull-right">
                                                            <label for="contacorrente">Conta Corrente</label>
                                                            <select class="form-control" name="contacorrente" id="contacorrente" required>
                                                                <?php foreach ($registroCc as $itemc){ ?>
                                                                    <option value="<?php echo( $itemc->id ); ?>" ><?php echo( utf8_encode($itemc->name) ); ?></option>
                                                                <?php }?>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6 pull-right">
                                                            <label for="planodecontas">Plano de Contas</label>
                                                            <select class="form-control" name="planodecontas" id="planodecontas" required>
                                                                <?php foreach ($registroPlan as $itemp){ ?>
                                                                    <option value="<?php echo( $itemp->id ); ?>" ><?php echo( utf8_encode($itemp->name) ); ?></option>
                                                                <?php }?>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6 pull-left">
                                                            <label for="status">Status da Transação</label>
                                                            <select class="form-control" name="status" id="status">
                                                                <?php foreach ($registroStI as $items){ ?>
                                                                    <option value="<?php echo( $items->id ); ?>" ><?php echo( utf8_encode($items->nameinvoice) ); ?></option>
                                                                <?php }?>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6 pull-right">
                                                            <label for="tipo">Tipo</label>
                                                            <select class="form-control" name="tipo" id="tipo">
                                                                <option  value="1">Crédito</option>
                                                                <option  value="0">Débito</option>
                                                            </select>
                                                        </div>
                                                        <input name="voucher" type="hidden" value="<?php echo($dadosGerais['numbervoucher']); ?>" >
                                                        <div class="container-fluid pull-left">
                                                            <button type="submit" class="btn btn-lg btn-outline-primary" name="incluircreditofatura"><svg><use href="#icon-plus"></use></svg><span>Incluir</span></button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card">
                                            <div class="card-header" id="headingTwo">
                                                <h2 class="mb-0">
                                                    <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#headingTwo2"
                                                            aria-expanded="false" aria-controls="headingTwo2">
                                                        Faturas Cadastradas
                                                    </button>
                                                </h2>
                                            </div>
                                            <div id="headingTwo2" class="collapse" aria-labelledby="headingTwo2" data-parent="#accordionExample">
                                                <div class="card-body">
                                                    <?php if( count($registro_credito_debito) > 0 ){ ?>
                                                        <div class="table-responsive">
                                                            <table class="table table-striped table-bordered">
                                                                <thead>
                                                                <tr>
                                                                    <th>Data</th>
                                                                    <th>Valor</th>
                                                                    <th>Conta</th>
                                                                    <th>Plano</th>
                                                                    <th>Status</th>
                                                                    <th>Tipo</th>
                                                                    <th>Descrição</th>
                                                                    <th>Editar</th>
                                                                    <th>Excluir</th>
                                                                </tr>
                                                                </thead>
                                                                <tbody>
                                                                <?php foreach ($registro_credito_debito as $item){ ?>
                                                                    <form action="" method="post">
                                                                        <tr>
                                                                            <td><input type="date" name="datafatura"
                                                                                       value="<?php echo( $item->data); ?>" class="form-control">
                                                                            </td>
                                                                            <td>
                                                                                <input type="text" name="valor"
                                                                                       value="<?php echo( number_format($item->valor, 2,
                                                                                           ",", ".") ); ?>"
                                                                                       class="form-control" >
                                                                            </td>
                                                                            <td>
                                                                                <select class="form-control" name="contacorrente">
                                                                                    <?php foreach ($registroCc as $conta_corrente){ ?>
                                                                                        <?php if( $item->idcurrentaccount == $conta_corrente->id ){ ?>
                                                                                            <option selected value="<?php echo($conta_corrente->id); ?>">
                                                                                                <?php echo($conta_corrente->name); ?></option>
                                                                                        <?php } else { ?>
                                                                                            <option  value="<?php echo($conta_corrente->id); ?>">
                                                                                                <?php echo($conta_corrente->name); ?></option>
                                                                                        <?php }?>

                                                                                    <?php }?>
                                                                                </select>
                                                                            </td>
                                                                            <td>
                                                                                <select class="form-control" name="planodeContas">
                                                                                    <?php foreach ($registroPlan as $plano_conta){ ?>
                                                                                        <?php if( $item->idplanaccount == $plano_conta->id ){ ?>
                                                                                            <option selected value="<?php echo($plano_conta->id); ?>">
                                                                                                <?php echo($plano_conta->name); ?></option>
                                                                                        <?php } else { ?>
                                                                                            <option  value="<?php echo($plano_conta->id); ?>">
                                                                                                <?php echo($plano_conta->name); ?></option>
                                                                                        <?php }?>

                                                                                    <?php }?>
                                                                                </select>
                                                                            </td>
                                                                            <td>
                                                                                <select class="form-control" name="status">
                                                                                    <?php foreach ($registroStI as $status_invoice){ ?>
                                                                                        <?php if( $item->idstatus == $status_invoice->id ){ ?>
                                                                                            <option selected value="<?php echo($status_invoice->id); ?>">
                                                                                                <?php echo($status_invoice->nameinvoice); ?></option>
                                                                                        <?php } else { ?>
                                                                                            <option  value="<?php echo($status_invoice->id); ?>">
                                                                                                <?php echo($status_invoice->nameinvoice); ?></option>
                                                                                        <?php }?>

                                                                                    <?php }?>
                                                                                </select>
                                                                            </td>
                                                                            <td>
                                                                                <select class="form-control" name="tipo">
                                                                                    <?php if( $item->idtype == 1 ){ ?>
                                                                                        <option selected  value="1">Crédito</option>
                                                                                        <option   value="0">Débito</option>
                                                                                    <?php } else { ?>
                                                                                        <option selected  value="0">Débito</option>
                                                                                        <option   value="1">Crédito</option>
                                                                                    <?php }?>
                                                                                </select>
                                                                            </td>
                                                                            <td>
                                                                                <input class="form-control" type="text" name="descricao" value="<?php echo( $item->descricao); ?>">
                                                                            </td>
                                                                            <td>
                                                                                <button type="submit" name="atualizarCadFatura"
                                                                                        class="action-icon-button">
                                                                                    <svg><use href="#icon-save"></use></svg><span>Atualizar</span>
                                                                                </button>
                                                                            </td>
                                                                            <td>
                                                                                <button type="submit" name="excluirCadFatura"
                                                                                        class="action-icon-button">
                                                                                    <svg><use href="#icon-trash"></use></svg><span>Excluir</span>
                                                                                </button>
                                                                            </td>
                                                                        </tr>
                                                                        <input name="idcadfatura" type="hidden" value="<?php echo($item->id); ?>" >
                                                                        <input name="voucher" type="hidden" value="<?php echo($item->numbervoucher); ?>" >
                                                                    </form>
                                                                <?php }?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    <?php } else { ?>
                                                        <div class="alert alert-warning" role="alertdialog">Não há faturas cadastradas</div>
                                                    <?php }?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    <?php }?>

                </div>
            </div>
        </div>
    </div>
    <?php if($contadorDados > 0) {?>
        <div class="col-lg-12" style="margin-top: 40px;">
            <div class="modal fade" id="exemplomodal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                <div class="modal-dialog modal-lg" role="document">
                    <form action="" method="post">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="gridSystemModalLabel"><?php echo(" Voucher ".$dadosGerais['numbervoucher']) ?></h4>
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
                                <input type="hidden" name="voucher" id="voucher" value="<?php echo($dadosGerais['numbervoucher']); ?>" >
                                <button type="submit" class="btn btn-success pull-right" name="atualizarfatura">
                                    <svg><use href="#icon-save"></use></svg><span>Atualizar Fatura</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php }?>

    <script>
        const inpttaxamala = document.querySelector('#incluirtaxamala');
        const qntpessoataxamala = document.querySelector('.qntpessoataxamala');
        
        qntpessoataxamala.style.display = inpttaxamala.checked ? 'block' : 'none';
        inpttaxamala.addEventListener('change', function () {
            if(inpttaxamala.checked) {
                qntpessoataxamala.style.display = 'block';
            } else {
                qntpessoataxamala.style.display = 'none';
                document.querySelector('input[name="qntpessoataxamala"]').value = 0;
            }
        });

        $("#pagamento").on('change', function(e){
            alert($(this).val())
            return false;
        });
        function moeda(a, e, r, t) {
            let n = ""
                , h = j = 0
                , u = tamanho2 = 0
                , l = ajd2 = ""
                , o = window.Event ? t.which : t.keyCode;
            if (13 == o || 8 == o)
                return !0;
            if (n = String.fromCharCode(o),
            -1 == "0123456789".indexOf(n))
                return !1;
            for (u = a.value.length,
                     h = 0; h < u && ("0" == a.value.charAt(h) || a.value.charAt(h) == r); h++)
                ;
            for (l = ""; h < u; h++)
                -1 != "0123456789".indexOf(a.value.charAt(h)) && (l += a.value.charAt(h));
            if (l += n,
            0 == (u = l.length) && (a.value = ""),
            1 == u && (a.value = "0" + r + "0" + l),
            2 == u && (a.value = "0" + r + l),
            u > 2) {
                for (ajd2 = "",
                         j = 0,
                         h = u - 3; h >= 0; h--)
                    3 == j && (ajd2 += e,
                        j = 0),
                        ajd2 += l.charAt(h),
                        j++;
                for (a.value = "",
                         tamanho2 = ajd2.length,
                         h = tamanho2 - 1; h >= 0; h--)
                    a.value += ajd2.charAt(h);
                a.value += r + l.substr(u - 2, u)
            }
            return !1
        }
        var selecionado = document.getElementById('service').value;
        setTimeout('window.close()',420000)

    </script>

    <?php require_once ('footer.php'); ?>
