<?php
require_once('../.././config.php');
$totalAdd   = 0;
$totalPago  = 0;
$folharosto = !empty($_POST['folharosto']);

if (isset($_POST['voucher'])) {

    $descreverCredito = $pdo->prepare(
        "SELECT valuecredit as credito, `name` as forma, datacredit, firstname, lastname
         FROM `ct_createfaturacredit` cfc
         LEFT JOIN `ct_currentaccount` cc ON cfc.idaccountcurrent = cc.id
         LEFT JOIN ct_usuario u ON u.id = cfc.idusr
         WHERE `numbervoucher` = :numbervoucher AND valueagente = '0.00'"
    );
    $descreverCredito->execute([':numbervoucher' => $_POST['voucher']]);
    $registroCredito = $descreverCredito->fetchAll(PDO::FETCH_CLASS);

    $dadosReserva = $pdo->prepare(
        "SELECT r.idservico, r.id, pax, em.logo, documento, dateinput, dateoutput,
                photoresident, c.fullname AS cliente, c.observacao,
                s.fullname AS `status`, r.horaap, u.firstname, u.lastname,
                se.fullname AS serivco, ag.fullname AS agente, priceadult,
                namepayment, g.fullname AS guia, qtdpax, qtdchild, qtdfree,
                ss.schedule, r.voo, pricechild, numbervoucher, r.valueservice,
                r.abertura, se.screenplay, roteiro, r.identificacao_mala,
                r.incluirtaxamala, r.qntpessoataxamala
         FROM `ct_reserva` r
         LEFT JOIN ct_cliente c ON c.id = r.idcliente
         LEFT JOIN ct_empresa em ON em.id = r.idempresa
         LEFT JOIN ct_usuario u ON u.id = r.idresponsavel
         LEFT JOIN ct_status s ON s.id = r.idstatus
         LEFT JOIN ct_guia g ON g.id = r.idguia
         JOIN ct_servico se ON se.id = r.idservico
         LEFT JOIN ct_agentes ag ON r.idagente = ag.id
         LEFT JOIN `ct_servico_horario` sr ON sr.idservice = r.idservico AND sr.idschedule = r.idhorario
         LEFT JOIN ct_service_schedule ss ON ss.idshedule = r.idhorario
         LEFT JOIN `ct_form_of_ payment` cfp ON cfp.id = r.idpayment
         WHERE `numbervoucher` = :numbervoucher"
    );
    $dadosReserva->execute([':numbervoucher' => $_POST['voucher']]);
    $dadosGerais = $dadosReserva->fetch(PDO::FETCH_ASSOC);

    $total     = ($dadosGerais['valueservice'] * $dadosGerais['qtdpax'])
               + (($dadosGerais['valueservice'] / 2) * $dadosGerais['qtdchild']);
    $timestamp2 = strtotime($dadosGerais['dateinput']);

    $adicionais = $pdo->prepare(
        "SELECT ra.idservice, ra.dateinput AS ap, s.fullname, s.screenplay,
                s.priceadult, s.pricechild, ss.schedule, qpax, qchild, qfree,
                ra.valueservice, ra.horaap, ra.documento, sr.roteiro
         FROM `ct_recentlyadd` ra
         LEFT JOIN `ct_reserva` r ON r.id = ra.idrecently
         LEFT JOIN `ct_servico_horario` sr ON sr.idservice = ra.idservice AND sr.idschedule = ra.idschedule
         LEFT JOIN ct_servico s ON s.id = ra.idservice
         LEFT JOIN ct_service_schedule ss ON ss.idshedule = ra.idschedule
         WHERE r.id = :id ORDER BY ap"
    );
    $adicionais->execute([':id' => $dadosGerais['id']]);
    $registro = $adicionais->fetchAll(PDO::FETCH_CLASS);
    $contador = $adicionais->rowCount();

    $descricaoAudit = $folharosto ? 'Folha de Rosto Impressa' : 'Voucher Impresso';
    $dadosAuditoria = $pdo->prepare(
        'INSERT INTO `ct_audit` (`id`,`idresponsible`,`voucher`,`description`,`date`)
         VALUES (DEFAULT,:idres,:vou,:descr,:dat)'
    );
    $dadosAuditoria->execute([
        ':idres' => $_SESSION['idresponsavel'],
        ':vou'   => $_POST['voucher'],
        ':descr' => $descricaoAudit,
        ':dat'   => date('Y-m-d H:i:s'),
    ]);
}

$titulo = $folharosto ? 'Folha de Rosto' : 'Voucher';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title><?= $titulo ?> <?= htmlspecialchars($dadosGerais['numbervoucher'] ?? '') ?></title>
</head>
<style>
    /* ── Reset ── */
    *, *::before, *::after { box-sizing: border-box; }
    body {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 10px;
        margin: 12mm 14mm;
        color: #111;
    }

    /* ── Suprimir URL/data/página do browser no print ── */
    @page {
        size: A4 portrait;
        margin: 0;
    }
    @media print {
        body { margin: 12mm 14mm; }
    }

    /* ── Header ── */
    .doc-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 6px;
    }
    .doc-header img { max-width: 180px; max-height: 70px; }
    .doc-header .contact {
        font-size: 9px;
        color: #333;
        text-align: right;
        line-height: 1.6;
    }
    .doc-header .realizacao {
        font-size: 9px;
        color: #333;
        text-align: right;
    }
    .doc-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin: 6px 0 8px;
        font-size: 10px;
    }
    .doc-meta .badge {
        border: 2px solid #1E4770;
        border-radius: 8px;
        padding: 3px 8px;
        font-weight: bold;
        font-size: 11px;
        color: #1E4770;
    }
    .doc-meta .impresso {
        border: 1px solid #aaa;
        border-radius: 8px;
        padding: 3px 8px;
        font-size: 9px;
        color: #555;
    }

    /* ── Tables ── */
    hr { border: 0; border-top: 1px solid #ccc; margin: 6px 0; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
    th, td { border: 1px solid #ddd; padding: 5px 7px; font-size: 9px; text-align: left; }
    th { background: #1E4770; color: #fff; font-weight: bold; }
    td.ap { font-weight: bold; font-size: 14px; }
    td.valor { font-weight: bold; font-size: 12px; }
    h6 { font-size: 11px; font-weight: bold; margin: 8px 0 4px; }
    p { font-size: 8px; text-align: justify; margin: 3px 0; }
    .subtotal { text-align: right; font-size: 11px; font-weight: bold; margin: 4px 0 6px; }

    /* ── Mala ── */
    .ident-mala { font-size: 20px; background: #1E4770; color: #fff; padding: 2px 6px; }
    .mala-footer { margin-top: 16px; text-align: right; font-weight: bold; color: #1E4770; }

    /* ── Assinatura (folha de rosto) ── */
    .assinatura-block { margin-top: 20px; }
    .assinatura-block p { font-size: 10px; margin: 10px 0; }
    .assinatura-block .linha { border-bottom: 1px solid #555; display: inline-block; width: 70%; }
</style>
<body>

<!-- ══ CABEÇALHO ══ -->
<div class="doc-header">
    <?php if (empty($dadosGerais['observacao'])) { ?>
        <img src="../.././images/logo.png" alt="Logo">
        <div class="contact">
            cassiturismo.com.br | @cassiturismo<br>
            Atendimento Nacional (71) 99121-1111<br>
            Atendimento Operacional (71) 98444-4444
        </div>
    <?php } else { ?>
        <img src="<?= htmlspecialchars($dadosGerais['observacao']) ?>" alt="Logo Agência">
        <div class="realizacao">
            Realização do Serviço<br>
            <img src="../.././images/<?= htmlspecialchars($dadosGerais['logo']) ?>" style="max-width:160px;max-height:55px;"><br>
            +55 (71) 9.99111-2222
        </div>
    <?php } ?>
</div>

<hr>

<div class="doc-meta">
    <div>
        <span style="font-size:9px;color:#555;"><?= $titulo ?>:</span><br>
        <span class="badge"><?= htmlspecialchars($dadosGerais['numbervoucher'] ?? '') ?></span>
    </div>
    <div class="impresso">
        Impresso em: <?= date('d/m/Y') ?> às <?= date('H:i:s') ?>
    </div>
</div>

<!-- ══ AGÊNCIA ══ -->
<table>
    <thead><tr>
        <th>Agência</th>
        <th>Abertura</th>
    </tr></thead>
    <tbody><tr>
        <td><?= htmlspecialchars($dadosGerais['cliente'] ?? '') ?></td>
        <td><?= date('d/m/Y', strtotime($dadosGerais['abertura'])) ?></td>
    </tr></tbody>
</table>

<!-- ══ PASSAGEIRO ══ -->
<table>
    <thead><tr>
        <th>Cliente</th>
        <th>Telefone | Whatsapp</th>
        <?php if (!empty($dadosGerais['voo']) && $dadosGerais['voo'] !== '00:00:00') { ?>
            <th>Horário do Voo</th>
        <?php } ?>
    </tr></thead>
    <tbody><tr>
        <td><?= htmlspecialchars($dadosGerais['pax'] ?? '') ?></td>
        <td><?= htmlspecialchars($dadosGerais['photoresident'] ?? '') ?></td>
        <?php if (!empty($dadosGerais['voo']) && $dadosGerais['voo'] !== '00:00:00') { ?>
            <td><?= date('H:i', strtotime($dadosGerais['voo'])) ?></td>
        <?php } ?>
    </tr></tbody>
</table>

<!-- ══ OPERADOR ══ -->
<table>
    <thead><tr>
        <th>Operador(a)</th>
        <th>Vendedor(a)</th>
    </tr></thead>
    <tbody><tr>
        <td><?= htmlspecialchars(($dadosGerais['firstname'] ?? '') . ' ' . ($dadosGerais['lastname'] ?? '')) ?></td>
        <td><?= htmlspecialchars($dadosGerais['agente'] ?? '') ?></td>
    </tr></tbody>
</table>

<!-- ══ SERVIÇOS ══ -->
<h6 style="text-align:center;">Serviços contratados</h6>
<table>
    <thead><tr>
        <th>Serviço | Complemento</th>
        <th>Data</th>
        <th>Embarque</th>
        <th>Apresentação</th>
        <th>P|C|F</th>
        <?php if (!$folharosto) { ?>
            <th>Valor Unitário</th>
            <?php if ($dadosGerais['qtdchild'] > 0) { ?><th>Valor Child</th><?php } ?>
            <th>Valor Total</th>
        <?php } ?>
    </tr></thead>
    <tbody>
    <!-- Serviço principal -->
    <tr>
        <td><?= htmlspecialchars($dadosGerais['serivco'] . ' - ' . $dadosGerais['documento']) ?></td>
        <td><?= date('d/m/Y', $timestamp2) ?></td>
        <?php if ($dadosGerais['idservico'] == 15) { ?>
            <td class="ap"><strong>&gt;<?= date('H:i', strtotime($dadosGerais['horaap'])) ?>&lt;</strong></td>
        <?php } else { ?>
            <td class="ap"><strong>&gt;<?= date('H:i', strtotime($dadosGerais['dateinput'] . ' ' . $dadosGerais['schedule'])) ?>&lt;</strong></td>
        <?php } ?>
        <td class="ap"><strong>&gt;<?= date('H:i', strtotime($dadosGerais['horaap'])) ?>&lt;</strong></td>
        <td><?= $dadosGerais['qtdpax'] . '/' . $dadosGerais['qtdchild'] . '/' . $dadosGerais['qtdfree'] ?></td>
        <?php if (!$folharosto) { ?>
            <td>R$ <?= number_format($dadosGerais['valueservice'], 2, ',', '.') ?></td>
            <?php if ($dadosGerais['qtdchild'] > 0) { ?>
                <td>R$ <?= number_format($dadosGerais['valueservice'] / 2, 2, ',', '.') ?></td>
            <?php } ?>
            <td>R$ <?= number_format($total, 2, ',', '.') ?></td>
        <?php } ?>
    </tr>

    <!-- Adicionais -->
    <?php foreach ($registro as $item) {
        $timestampAdd = strtotime($item->ap);
        $totalAdd    += ($item->valueservice * $item->qpax) + (($item->valueservice / 2) * $item->qchild);
        $valorSub     = ($item->valueservice * $item->qpax) + (($item->valueservice / 2) * $item->qchild);
    ?>
    <tr>
        <td><?= htmlspecialchars($item->fullname . ' - ' . $item->documento) ?></td>
        <td><?= date('d/m/Y', $timestampAdd) ?></td>
        <?php if ($item->idservice == 15) { ?>
            <td class="ap"><strong>&gt;<?= date('H:i', strtotime($item->horaap)) ?>&lt;</strong></td>
        <?php } else { ?>
            <td class="ap"><strong>&gt;<?= date('H:i', strtotime($item->ap . ' ' . $item->schedule)) ?>&lt;</strong></td>
        <?php } ?>
        <td class="ap"><strong>&gt;<?= date('H:i', strtotime($item->horaap)) ?>&lt;</strong></td>
        <td><?= $item->qpax . '/' . $item->qchild . '/' . $item->qfree ?></td>
        <?php if (!$folharosto) { ?>
            <td>R$ <?= number_format($item->valueservice, 2, ',', '.') ?></td>
            <?php if ($item->qchild > 0) { ?>
                <td>R$ <?= number_format($item->valueservice / 2, 2, ',', '.') ?></td>
            <?php } ?>
            <td>R$ <?= number_format($valorSub, 2, ',', '.') ?></td>
        <?php } ?>
    </tr>
    <?php } ?>

    <!-- Taxa de Mala -->
    <?php if (!$folharosto && $dadosGerais['incluirtaxamala']) {
        $valorTaxaMala = 20;
        $totalTaxaMala = $valorTaxaMala * $dadosGerais['qntpessoataxamala'];
        $totalAdd     += $totalTaxaMala;
    ?>
    <tr>
        <td>SERVIÇO DE MALA</td>
        <td>-</td><td>-</td><td>-</td>
        <td><?= $dadosGerais['qntpessoataxamala'] ?></td>
        <td>R$ <?= number_format($valorTaxaMala, 2, ',', '.') ?></td>
        <?php if ($dadosGerais['qtdchild'] > 0) { ?><td>-</td><?php } ?>
        <td>R$ <?= number_format($totalTaxaMala, 2, ',', '.') ?></td>
    </tr>
    <?php } ?>
    </tbody>
</table>

<?php if (!$folharosto) {
    $valorDocumento = $total + $totalAdd;
?>
<!-- ══ SUB-TOTAL ══ -->
<div class="subtotal">Sub-total R$ <?= number_format($valorDocumento, 2, ',', '.') ?></div>

<!-- ══ PAGAMENTOS ══ -->
<table>
    <thead><tr>
        <th>Pago em</th>
        <th>Forma de Pagamento</th>
        <th>Recebido por</th>
        <th>Valor Total</th>
    </tr></thead>
    <tbody>
    <?php
    $pagoLojaVirtual   = 0;
    $isPagoLojaVirtual = false;
    foreach ($registroCredito as $item) {
        $totalPago += $item->credito;
        $nomeOp     = strtoupper(trim($item->firstname) . ' ' . trim($item->lastname));
        if ($item->forma === 'LOJA VIRTUAL' || $nomeOp === 'LOJA VIRTUAL') {
            $pagoLojaVirtual   += $item->credito;
            $isPagoLojaVirtual  = true;
        }
    ?>
    <tr>
        <td><?= date('d/m/Y', strtotime($item->datacredit)) ?></td>
        <td><?= htmlspecialchars($item->forma) ?></td>
        <td><?= htmlspecialchars($nomeOp) ?></td>
        <td class="valor"><strong>R$ <?= number_format($item->credito, 2, ',', '.') ?></strong></td>
    </tr>
    <?php } ?>
    </tbody>
</table>

<table>
    <thead><tr>
        <th>Pago</th>
        <th><?= ($totalPago - $valorDocumento) < 0 ? 'Falta Pagar' : 'Crédito' ?></th>
    </tr></thead>
    <tbody><tr>
        <td>R$ <?= number_format($totalPago, 2, ',', '.') ?></td>
        <td>R$ <?= number_format($totalPago - $valorDocumento, 2, ',', '.') ?></td>
    </tr></tbody>
</table>

<?php if ($isPagoLojaVirtual) { ?>
<table>
    <thead><tr><th>Total Pago a Cassi</th></tr></thead>
    <tbody><tr><td>R$ <?= number_format($valorDocumento - $pagoLojaVirtual, 2, ',', '.') ?></td></tr></tbody>
</table>
<?php } ?>

<?php } /* fim !$folharosto */ ?>

<!-- ══ DESCRIÇÃO DO SERVIÇO ══ -->
<h6>Descrição do Serviço</h6>
<p style="font-size:9px;font-weight:bold;"><?= htmlspecialchars($dadosGerais['serivco'] ?? '') ?></p>
<p style="font-size:8px;"><?= nl2br(htmlspecialchars($dadosGerais['screenplay'] ?? '')) ?></p>
<?php foreach ($registro as $item) { ?>
    <p style="font-size:9px;font-weight:bold;"><?= htmlspecialchars($item->fullname) ?></p>
    <p style="font-size:8px;"><?= nl2br(htmlspecialchars($item->screenplay ?? '')) ?></p>
<?php } ?>

<!-- ══ ASSINATURA (folha de rosto) ══ -->
<?php if ($folharosto) { ?>
<div class="assinatura-block">
    <p>CPF / Passaporte: <span class="linha"></span></p>
    <p>E-mail: <span class="linha"></span></p>
    <p>Assinatura: <span class="linha"></span></p>
</div>
<?php } ?>

<!-- ══ DESPACHO DE MALA ══ -->
<?php if (!empty($dadosGerais['identificacao_mala'])) { ?>
<div class="mala-footer">
    DESPACHO DE MALA: <b class="ident-mala">&nbsp;<?= htmlspecialchars($dadosGerais['identificacao_mala']) ?>&nbsp;</b>
</div>
<?php } ?>

<script>window.print();</script>
</body>
</html>
