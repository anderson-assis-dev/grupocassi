<?php
/**
 * Carrega todos os dados necessários para renderizar a tela editar-pax.
 * Espera que $pdo e $numberVoucher estejam definidos no escopo chamador.
 *
 * Disponibiliza as seguintes variáveis no escopo chamador:
 *   $listaHorarios, $buscaCredito, $registroCredito, $contadorCredito,
 *   $dadosReservaAu, $registroAu, $contadorAuditoria,
 *   $administrativo, $contadorAdm,
 *   $dadosReserva, $dadosGerais,
 *   $adicionais, $registro, $contador,
 *   $todosCliente, $listaEmpresas, $listaServicos, $listaPagamentos,
 *   $status, $listaStatus,
 *   $comissoes, $despesa, $contadorDespesa,
 *   $contaCorrente, $registroCc,
 *   $planoContas, $registroPlan,
 *   $statusInvoice, $registroStI,
 *   $credito_debito_all, $registro_credito_debito,
 *   $data_total_servico, $data_total2,
 *   $buscaCredito1, $registroCredito1,
 *   $buscarResponsavel_todos, $dados_buscarResponsavel_todos
 */

$horarios = $pdo->prepare('SELECT * FROM `ct_service_schedule` ORDER BY `schedule`');
$horarios->execute();
$listaHorarios = $horarios->fetchAll(PDO::FETCH_CLASS);

$buscaCredito = $pdo->prepare(
    'SELECT cfc.id, valuecredit, `name`, datacredit, valueagente, dataagente
     FROM `ct_createfaturacredit` cfc
     LEFT JOIN `ct_currentaccount` cc ON cfc.idaccountcurrent = cc.id
     WHERE `numbervoucher` = :voucher');
$buscaCredito->execute([':voucher' => $numberVoucher]);
$registroCredito = $buscaCredito->fetchAll(PDO::FETCH_CLASS);
$contadorCredito = $buscaCredito->rowCount();

$dadosReservaAu = $pdo->prepare('SELECT * FROM `ct_audit` WHERE `voucher` = :voucher');
$dadosReservaAu->execute([':voucher' => $numberVoucher]);
$registroAu = $dadosReservaAu->fetchAll(PDO::FETCH_CLASS);
$contadorAuditoria = $dadosReservaAu->rowCount();

$administrativo = $pdo->prepare(
    'SELECT c.id, c.datematurity, c.datepayment, c.numberadd, cc.name
     FROM `ct_createfatura` c
     LEFT JOIN ct_currentaccount cc ON cc.id = c.idcurrentaccount
     WHERE numbervoucher = :voucher');
$administrativo->execute([':voucher' => $numberVoucher]);
$contadorAdm = $administrativo->rowCount();

$dadosReserva = $pdo->prepare(
    'SELECT r.id, pax, r.idempresa, documento, photoresident, dateinput, dateoutput,
            c.fullname AS cliente, s.fullname AS `status`, r.valueservice,
            se.fullname AS serivco, ag.fullname AS agente, namepayment, g.id AS guia,
            qtdpax, qtdchild, qtdfree, ss.schedule, numbervoucher, r.idstatusinvoice,
            r.abertura, firstname, r.horaap, r.idcliente, r.idresponsavel,
            r.totalservico, r.confirmacao, r.numberfatura, r.fl_altarar_valor_servico,
            r.identificacao_mala, r.incluirtaxamala, r.qntpessoataxamala
     FROM `ct_reserva` r
     LEFT JOIN ct_cliente c ON c.id = r.idcliente
     LEFT JOIN ct_responsavel re ON re.id = r.idresponsavel
     LEFT JOIN ct_status s ON s.id = r.idstatus
     LEFT JOIN ct_guia g ON g.id = r.idguia
     JOIN ct_servico se ON se.id = r.idservico
     LEFT JOIN ct_agentes AS ag ON r.idagente = ag.id
     LEFT JOIN ct_usuario us ON us.id = r.idresponsavel
     LEFT JOIN ct_service_schedule ss ON ss.idshedule = r.idhorario
     LEFT JOIN `ct_form_of_ payment` AS cfp ON cfp.id = r.idpayment
     WHERE `numbervoucher` = :numbervoucher');
$dadosReserva->execute([':numbervoucher' => $numberVoucher]);
$dadosGerais = $dadosReserva->fetch(PDO::FETCH_ASSOC);

$adicionais = $pdo->prepare(
    'SELECT ra.id, ra.dateinput AS ap, s.fullname, s.screenplay, ss.schedule,
            qpax, qchild, qfree, ra.dateinput, ra.dateoutput, ra.valueservice,
            ra.horaap, ra.documento, s.id AS idservico, ra.confirmacao2,
            ra.fl_altarar_valor_servico
     FROM `ct_recentlyadd` ra
     LEFT JOIN `ct_reserva` r ON r.id = ra.idrecently
     LEFT JOIN ct_servico s ON s.id = ra.idservice
     LEFT JOIN ct_service_schedule ss ON ss.idshedule = ra.idschedule
     WHERE r.id = :id');
$adicionais->execute([':id' => $dadosGerais['id']]);
$registro = $adicionais->fetchAll(PDO::FETCH_CLASS);
$contador = $adicionais->rowCount();

$todosCliente    = refClientes($pdo);
$listaEmpresas   = refEmpresasTodas($pdo);
$listaServicos   = refServicos($pdo);
$listaPagamentos = refPagamentos($pdo);

$status = $pdo->prepare('SELECT id, fullname AS situacao FROM `ct_status`');
$status->execute();
$listaStatus = $status->fetchAll(PDO::FETCH_CLASS);

$comissoes = $pdo->prepare(
    "SELECT * FROM `ct_createfaturacredit`
     WHERE numbervoucher = :voucher AND dataagente <> '0000-00-00 00:00:00'");
$comissoes->execute([':voucher' => $numberVoucher]);
$despesa = $comissoes->fetchAll(PDO::FETCH_CLASS);
$contadorDespesa = $comissoes->rowCount();

$contaCorrente = $pdo->prepare('SELECT * FROM `ct_currentaccount`');
$contaCorrente->execute();
$registroCc = $contaCorrente->fetchAll(PDO::FETCH_CLASS);

$planoContas = $pdo->prepare('SELECT * FROM `ct_planaccounts`');
$planoContas->execute();
$registroPlan = $planoContas->fetchAll(PDO::FETCH_CLASS);

$statusInvoice = $pdo->prepare('SELECT * FROM `ct_statusinvoice` WHERE `id` >= :maior AND `id` <= :menor');
$statusInvoice->execute([':maior' => 6, ':menor' => 7]);
$registroStI = $statusInvoice->fetchAll(PDO::FETCH_CLASS);

$credito_debito_all = $pdo->prepare('SELECT * FROM `ct_credito_deb_reserva` WHERE `numbervoucher` = :voucher');
$credito_debito_all->execute([':voucher' => $numberVoucher]);
$registro_credito_debito = $credito_debito_all->fetchAll(PDO::FETCH_CLASS);

$data_total_servico = ($dadosGerais['valueservice'] * $dadosGerais['qtdpax']
    + (($dadosGerais['valueservice'] / 2) * $dadosGerais['qtdchild']));
$busta_total_servico2 = $pdo->prepare(
    'SELECT sum(valueservice * qpax + ((valueservice / 2) * qchild)) AS total2
     FROM `ct_recentlyadd` WHERE idrecently = :id');
$busta_total_servico2->execute([':id' => $dadosGerais['id']]);
$data_total2 = $busta_total_servico2->fetch(PDO::FETCH_ASSOC);

$buscaCredito1 = $pdo->prepare(
    'SELECT sum(valuecredit) AS totalpago FROM `ct_createfaturacredit` WHERE `numbervoucher` = :voucher');
$buscaCredito1->execute([':voucher' => $numberVoucher]);
$registroCredito1 = $buscaCredito1->fetchAll(PDO::FETCH_CLASS);

$buscarResponsavel_todos = $pdo->prepare('SELECT * FROM `ct_usuario` WHERE bloqueado = 0 ORDER BY firstname');
$buscarResponsavel_todos->execute();
$dados_buscarResponsavel_todos = $buscarResponsavel_todos->fetchAll(PDO::FETCH_CLASS);

marcarReservaAlterada($pdo, $numberVoucher);
