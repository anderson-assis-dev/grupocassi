<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title><?= $titulo ?> <?= htmlspecialchars($dadosGerais['numbervoucher'] ?? '') ?></title>
</head>
<style>
    *, *::before, *::after { box-sizing: border-box; }
    body {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 10px;
        margin: 12mm 14mm;
        color: #111;
    }
    @page { size: A4 portrait; margin: 0; }
    @media print { body { margin: 12mm 14mm; } }
    .doc-header { margin-bottom: 6px; }
    .doc-header-table { width: 100%; border-collapse: collapse; }
    .doc-header-table td { vertical-align: top; border: 0; padding: 0; }
    .doc-header-flex {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }
    .doc-header img { width: 200px; height: auto; max-width: 200px; max-height: 70px; }
    .doc-header .contact, .doc-header .realizacao {
        font-size: 9px;
        color: #333;
        text-align: right;
        line-height: 1.6;
    }
    .doc-meta { margin: 6px 0 8px; font-size: 10px; }
    .doc-meta-table { width: 100%; border-collapse: collapse; }
    .doc-meta-table td { border: 0; padding: 0; vertical-align: middle; }
    .doc-meta-flex {
        display: flex;
        justify-content: space-between;
        align-items: center;
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
        text-align: right;
    }
    hr { border: 0; border-top: 1px solid #ccc; margin: 6px 0; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
    th, td { border: 1px solid #ddd; padding: 5px 7px; font-size: 9px; text-align: left; }
    th { background: #1E4770; color: #fff; font-weight: bold; }
    td.ap { font-weight: bold; font-size: 14px; }
    td.valor { font-weight: bold; font-size: 12px; }
    h6, .section-title { font-size: 11px; font-weight: bold; margin: 8px 0 4px; padding: 0; background: transparent; color: #111; }
    p { font-size: 8px; text-align: justify; margin: 3px 0; }
    .subtotal { text-align: right; font-size: 11px; font-weight: bold; margin: 4px 0 6px; }
    .ident-mala { font-size: 20px; background: #1E4770; color: #fff; padding: 2px 6px; }
    .mala-footer { margin-top: 16px; text-align: right; font-weight: bold; color: #1E4770; }
    .assinatura-block { margin-top: 20px; }
    .assinatura-block p { font-size: 10px; margin: 10px 0; }
    .assinatura-block .linha { border-bottom: 1px solid #555; display: inline-block; width: 70%; }
</style>
<body>
<div class="doc-header">
    <?php if ($forPdf) { ?>
    <table class="doc-header-table">
        <tr>
            <td style="width:55%;">
                <?php if (empty($dadosGerais['observacao'])) { ?>
                    <img src="<?= htmlspecialchars($logoPadrao) ?>" alt="Logo" width="<?= (int) $logoLargura ?>" height="<?= (int) $logoAltura ?>">
                <?php } else { ?>
                    <img src="<?= htmlspecialchars($dadosGerais['observacao']) ?>" alt="Logo Agência" width="160">
                <?php } ?>
            </td>
            <td style="width:45%;" class="contact">
                <?php if (empty($dadosGerais['observacao'])) { ?>
                    cassiturismo.com.br | @cassiturismo<br>
                    Atendimento Nacional (71) 99121-1111<br>
                    Atendimento Operacional (71) 98444-4444
                <?php } else { ?>
                    Realização do Serviço<br>
                    <img src="<?= htmlspecialchars($forPdf ? imagemParaDataUri($projectRoot . '/images/' . ($dadosGerais['logo'] ?? '')) : '../.././images/' . ($dadosGerais['logo'] ?? '')) ?>" width="160" alt="Logo Cassi"><br>
                    +55 (71) 9.99111-2222
                <?php } ?>
            </td>
        </tr>
    </table>
    <?php } else { ?>
    <div class="doc-header-flex">
        <?php if (empty($dadosGerais['observacao'])) { ?>
            <img src="<?= htmlspecialchars($logoPadrao) ?>" alt="Logo">
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
    <?php } ?>
</div>
<hr>
<div class="doc-meta">
    <?php if ($forPdf) { ?>
    <table class="doc-meta-table">
        <tr>
            <td>
                <span style="font-size:9px;color:#555;"><?= $titulo ?>:</span><br>
                <span class="badge"><?= htmlspecialchars($dadosGerais['numbervoucher'] ?? '') ?></span>
            </td>
            <td class="impresso">
                Impresso em: <?= date('d/m/Y') ?> às <?= date('H:i:s') ?>
            </td>
        </tr>
    </table>
    <?php } else { ?>
    <div class="doc-meta-flex">
        <div>
            <span style="font-size:9px;color:#555;"><?= $titulo ?>:</span><br>
            <span class="badge"><?= htmlspecialchars($dadosGerais['numbervoucher'] ?? '') ?></span>
        </div>
        <div class="impresso">
            Impresso em: <?= date('d/m/Y') ?> às <?= date('H:i:s') ?>
        </div>
    </div>
    <?php } ?>
</div>
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
<div class="section-title" style="text-align:center;">Serviços contratados</div>
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
    <tr>
        <td><?= htmlspecialchars($dadosGerais['serivco'] . ' - ' . $dadosGerais['documento']) ?></td>
        <td><?= date('d/m/Y', $timestamp2) ?></td>
        <?php if ($dadosGerais['idservico'] == 15) { ?>
            <td class="ap"><strong><?= formatHorarioVoucher($dadosGerais['horaap'], $forPdf) ?></strong></td>
        <?php } else { ?>
            <td class="ap"><strong><?= formatHorarioVoucher($dadosGerais['dateinput'] . ' ' . $dadosGerais['schedule'], $forPdf) ?></strong></td>
        <?php } ?>
        <td class="ap"><strong><?= formatHorarioVoucher($dadosGerais['horaap'], $forPdf) ?></strong></td>
        <td><?= $dadosGerais['qtdpax'] . '/' . $dadosGerais['qtdchild'] . '/' . $dadosGerais['qtdfree'] ?></td>
        <?php if (!$folharosto) { ?>
            <td>R$ <?= number_format($dadosGerais['valueservice'], 2, ',', '.') ?></td>
            <?php if ($dadosGerais['qtdchild'] > 0) { ?>
                <td>R$ <?= number_format($dadosGerais['valueservice'] / 2, 2, ',', '.') ?></td>
            <?php } ?>
            <td>R$ <?= number_format($total, 2, ',', '.') ?></td>
        <?php } ?>
    </tr>
    <?php foreach ($registro as $item) {
        $timestampAdd = strtotime($item->ap);
        $totalAdd += ($item->valueservice * $item->qpax) + (($item->valueservice / 2) * $item->qchild);
        $valorSub = ($item->valueservice * $item->qpax) + (($item->valueservice / 2) * $item->qchild);
    ?>
    <tr>
        <td><?= htmlspecialchars($item->fullname . ' - ' . $item->documento) ?></td>
        <td><?= date('d/m/Y', $timestampAdd) ?></td>
        <?php if ($item->idservice == 15) { ?>
            <td class="ap"><strong><?= formatHorarioVoucher($item->horaap, $forPdf) ?></strong></td>
        <?php } else { ?>
            <td class="ap"><strong><?= formatHorarioVoucher($item->ap . ' ' . $item->schedule, $forPdf) ?></strong></td>
        <?php } ?>
        <td class="ap"><strong><?= formatHorarioVoucher($item->horaap, $forPdf) ?></strong></td>
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
    <?php if (!$folharosto && $dadosGerais['incluirtaxamala']) {
        $valorTaxaMala = 20;
        $totalTaxaMala = $valorTaxaMala * $dadosGerais['qntpessoataxamala'];
        $totalAdd += $totalTaxaMala;
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
<div class="subtotal">Sub-total R$ <?= number_format($valorDocumento, 2, ',', '.') ?></div>
<table>
    <thead><tr>
        <th>Pago em</th>
        <th>Forma de Pagamento</th>
        <th>Recebido por</th>
        <th>Valor Total</th>
    </tr></thead>
    <tbody>
    <?php
    $pagoLojaVirtual = 0;
    $isPagoLojaVirtual = false;
    foreach ($registroCredito as $item) {
        $totalPago += $item->credito;
        $nomeOp = strtoupper(trim($item->firstname) . ' ' . trim($item->lastname));
        if ($item->forma === 'LOJA VIRTUAL' || $nomeOp === 'LOJA VIRTUAL') {
            $pagoLojaVirtual += $item->credito;
            $isPagoLojaVirtual = true;
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
<?php } ?>
<div class="section-title">Descrição do Serviço</div>
<p style="font-size:9px;font-weight:bold;"><?= htmlspecialchars($dadosGerais['serivco'] ?? '') ?></p>
<p style="font-size:8px;"><?= nl2br(htmlspecialchars(normalizarTextoVoucher($dadosGerais['screenplay'] ?? ''))) ?></p>
<?php foreach ($registro as $item) { ?>
    <p style="font-size:9px;font-weight:bold;"><?= htmlspecialchars($item->fullname) ?></p>
    <p style="font-size:8px;"><?= nl2br(htmlspecialchars(normalizarTextoVoucher($item->screenplay ?? ''))) ?></p>
<?php } ?>
<?php if ($folharosto) { ?>
<div class="assinatura-block">
    <p>CPF / Passaporte: <span class="linha"></span></p>
    <p>E-mail: <span class="linha"></span></p>
    <p>Assinatura: <span class="linha"></span></p>
</div>
<?php } ?>
<?php if (!empty($dadosGerais['identificacao_mala'])) { ?>
<div class="mala-footer">
    DESPACHO DE MALA: <b class="ident-mala">&nbsp;<?= htmlspecialchars($dadosGerais['identificacao_mala']) ?>&nbsp;</b>
</div>
<?php } ?>
<?php if ($autoPrint) { ?><script>window.print();</script><?php } ?>
</body>
</html>
