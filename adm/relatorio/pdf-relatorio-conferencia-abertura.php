<?php
require_once '../../config.php';
require_once __DIR__ . '/../includes/audit.php';

$abertura      = $_POST['abertura']      ?? '';
$aberturaFinal = $_POST['aberturafinal'] ?? '';
$cliente       = (int)($_POST['cliente']      ?? 0);
$responsavel   = (int)($_POST['responsavel']  ?? 0);
$idservicos    = $_POST['servico'] ?? [0];
$tipo          = (int)($_POST['tiporelatorio'] ?? 0);

// Nome do cliente para exibir no cabeçalho
$nomeDoCliente = null;
if ($cliente > 0) {
    $st = $pdo->prepare('SELECT fullname FROM ct_cliente WHERE id = :id');
    $st->execute([':id' => $cliente]);
    $nomeDoCliente = $st->fetchColumn();
}

// Fragmento WHERE para serviços (multi-select)
$servicoWhere  = '';
$servicoParams = [];
if (!empty($idservicos) && (int)$idservicos[0] !== 0) {
    $holders      = implode(',', array_fill(0, count($idservicos), '?'));
    $servicoWhere = "AND r.idservico IN ($holders)";
    $servicoParams = array_map('intval', array_values($idservicos));
}

// --- Query principal: UMA chamada para todos os filtros ---
$sql = "SELECT r.id, r.dateinput, r.numbervoucher,
               c.namefantazia AS cliente, r.pax,
               u.firstname, u.lastname,
               s.fullname AS servico,
               r.qtdpax, r.qtdchild,
               si.nameinvoice AS statuu,
               r.valueservice, r.idstatusinvoice
        FROM ct_reserva r
        LEFT JOIN ct_cliente c       ON c.id  = r.idcliente
        LEFT JOIN ct_usuario u       ON u.id  = r.idresponsavel
        LEFT JOIN ct_servico s       ON s.id  = r.idservico
        LEFT JOIN `ct_form_of_ payment` cp ON cp.id = r.idpayment
        LEFT JOIN ct_statusinvoice si ON si.id = r.idstatusinvoice
        WHERE r.abertura >= ? AND r.abertura <= ?
          AND (? = 0 OR r.idcliente    = ?)
          AND (? = 0 OR r.idresponsavel = ?)
          $servicoWhere
        ORDER BY r.numbervoucher";

$params = [$abertura, $aberturaFinal, $cliente, $cliente, $responsavel, $responsavel];
$params = array_merge($params, $servicoParams);

$st = $pdo->prepare($sql);
$st->execute($params);
$registros = $st->fetchAll(PDO::FETCH_OBJ);

if (empty($registros)) {
    ?><!DOCTYPE html><html lang="pt-BR"><head><meta charset="utf-8"><title>Relatório</title>
    <style>body{font-family:Arial,sans-serif;padding:40px;color:#555}</style></head>
    <body><h3>Nenhum registro encontrado para os filtros selecionados.</h3></body></html>
    <?php exit;
}

// Coleta IDs para bulk queries
$voucherNums = array_map(fn($r) => $r->numbervoucher, $registros);
$reservaIds  = array_map(fn($r) => (int)$r->id, $registros);

$inV = implode(',', array_fill(0, count($voucherNums), '?'));
$inR = implode(',', array_fill(0, count($reservaIds),  '?'));

// Bulk: totais financeiros por voucher (1 query no lugar de N)
$finSt = $pdo->prepare(
    "SELECT numbervoucher,
            COALESCE(SUM(valuecredit), 0) AS credito,
            COALESCE(SUM(valueguia),   0) AS guia,
            COALESCE(SUM(valueagente), 0) AS agente
     FROM ct_createfaturacredit
     WHERE numbervoucher IN ($inV)
     GROUP BY numbervoucher"
);
$finSt->execute($voucherNums);
$financials = [];
foreach ($finSt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $financials[$row['numbervoucher']] = $row;
}

// Bulk: detalhes de pagamento por voucher (1 query no lugar de N)
$pagSt = $pdo->prepare(
    "SELECT cfc.numbervoucher, cfc.datacredit AS dia,
            cc.name AS pagamento, cfc.valuecredit AS valor,
            u.firstname, u.lastname
     FROM ct_createfaturacredit cfc
     LEFT JOIN ct_currentaccount cc ON cc.id  = cfc.idaccountcurrent
     LEFT JOIN ct_usuario u         ON u.id   = cfc.idusr
     WHERE cfc.numbervoucher IN ($inV)"
);
$pagSt->execute($voucherNums);
$pagamentos = [];
foreach ($pagSt->fetchAll(PDO::FETCH_OBJ) as $row) {
    $pagamentos[$row->numbervoucher][] = $row;
}

// Bulk: serviços adicionais por reserva (1 query no lugar de N)
$addSt = $pdo->prepare(
    "SELECT ra.idrecently, ra.dateinput, r.numbervoucher,
            c.namefantazia AS cliente, r.pax,
            u.firstname, u.lastname,
            s.fullname AS servico,
            ra.qpax, ra.qchild, ra.valueservice,
            si.nameinvoice AS statuus
     FROM ct_recentlyadd ra
     LEFT JOIN ct_reserva r      ON r.id  = ra.idrecently
     LEFT JOIN ct_servico s      ON s.id  = ra.idservice
     LEFT JOIN ct_cliente c      ON c.id  = r.idcliente
     LEFT JOIN ct_usuario u      ON u.id  = r.idresponsavel
     LEFT JOIN ct_statusinvoice si ON si.id = r.idstatusinvoice
     WHERE ra.idrecently IN ($inR)"
);
$addSt->execute($reservaIds);
$adicionais = [];
foreach ($addSt->fetchAll(PDO::FETCH_OBJ) as $row) {
    $adicionais[(int)$row->idrecently][] = $row;
}

// Auditoria: 1 registro por relatório gerado (não por voucher)
logAudit(
    $pdo,
    'RELATORIO-ABERTURA',
    "Relatório de abertura gerado: " .
    date("d/m/Y", strtotime($abertura)) . " a " . date("d/m/Y", strtotime($aberturaFinal)) .
    " | vouchers: " . count($registros)
);

// Pré-calcula totais
$somaDosServicos1 = 0.0;
$somaDosServicos2 = 0.0;
$totalPago        = 0.0;
$totalRecebido    = 0.0;

foreach ($registros as $item) {
    $fin  = $financials[$item->numbervoucher] ?? ['credito' => 0, 'guia' => 0, 'agente' => 0];
    $bruto = ($item->valueservice * $item->qtdpax) + (($item->valueservice / 2) * $item->qtdchild);
    $somaDosServicos1 += $bruto;
    if ((int)$item->idstatusinvoice === 3) {
        $totalPago += (float)$fin['credito'];
    }
    $totalRecebido += (float)$fin['credito'];
    foreach ($adicionais[(int)$item->id] ?? [] as $add) {
        $somaDosServicos2 += ($add->valueservice * $add->qpax) + (($add->valueservice / 2) * $add->qchild);
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Relatório de Conferência – Abertura</title>
    <style>
        @page  { size: A4 landscape; margin: 0; }
        @media print { body { margin: 10mm 12mm; } .no-print { display: none; } }
        body   { font-family: Arial, sans-serif; font-size: 11px; color: #222; margin: 14mm 16mm; }
        img#logo { width: 260px; margin-bottom: 6px; }
        h2  { font-size: 13px; margin: 0 0 2px; }
        p.periodo { font-size: 10px; color: #555; margin: 0 0 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        th  { background: #1e4770; color: #fff; font-size: 10px; padding: 6px 5px; text-align: left; }
        td  { border-bottom: 1px solid #e5e7eb; padding: 5px; font-size: 10px; }
        tr.add-row td { background: #f8fafc; color: #555; }
        tr.pay-row td { background: #fffbeb; font-style: italic; }
        tfoot td { font-weight: bold; background: #f1f5f9; border-top: 2px solid #1e4770; padding: 7px 5px; }
        .btn-print { display: inline-block; margin: 16px 0; padding: 9px 22px; background: #1e4770;
                     color: #fff; border: 0; border-radius: 8px; cursor: pointer; font-size: 12px; font-weight: 700; }
    </style>
</head>
<body>
<button class="btn-print no-print" onclick="window.print()">Imprimir / Salvar PDF</button>

<img id="logo" src="../../images/logo.png" alt="Logo">
<h2>Relatório de Conferência por Data de Abertura</h2>
<p class="periodo">
    Período: <?= date("d/m/Y", strtotime($abertura)) ?> até <?= date("d/m/Y", strtotime($aberturaFinal)) ?>
    <?php if ($nomeDoCliente): ?> — Cliente: <?= htmlspecialchars($nomeDoCliente) ?><?php endif ?>
    &nbsp;|&nbsp; Impresso em: <?= date("d/m/Y H:i") ?>
</p>

<?php if ($tipo == 0): /* COMPLETO */ ?>
<table>
    <thead>
        <tr>
            <th>EMBARQUE</th>
            <th>VOUCHER</th>
            <th>CLIENTE</th>
            <th>PAX</th>
            <th>P/C</th>
            <th>RES</th>
            <th>SERVIÇO</th>
            <th>VALOR</th>
            <th>STATUS</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($registros as $item):
        $fin    = $financials[$item->numbervoucher] ?? ['credito' => 0, 'guia' => 0, 'agente' => 0];
        $bruto  = ($item->valueservice * $item->qtdpax) + (($item->valueservice / 2) * $item->qtdchild);
        $pags   = $pagamentos[$item->numbervoucher]  ?? [];
        $adds   = $adicionais[(int)$item->id] ?? [];
    ?>
        <tr>
            <td><?= date("d/m/Y", strtotime($item->dateinput)) ?></td>
            <td><?= htmlspecialchars($item->numbervoucher) ?></td>
            <td><?= htmlspecialchars($item->cliente) ?></td>
            <td><?= htmlspecialchars($item->pax) ?></td>
            <td><?= (int)$item->qtdpax ?>/<?= (int)$item->qtdchild ?></td>
            <td><?= htmlspecialchars($item->firstname . ' ' . $item->lastname) ?></td>
            <td><?= htmlspecialchars($item->servico) ?></td>
            <td>R$ <?= number_format($bruto, 2, ',', '.') ?></td>
            <td><?= htmlspecialchars($item->statuu) ?></td>
        </tr>
        <?php foreach ($adds as $add): ?>
        <tr class="add-row">
            <td><?= date("d/m/Y", strtotime($add->dateinput)) ?></td>
            <td><?= htmlspecialchars($add->numbervoucher) ?></td>
            <td><?= htmlspecialchars($add->cliente) ?></td>
            <td><?= htmlspecialchars($add->pax) ?></td>
            <td><?= (int)$add->qpax ?>/<?= (int)$add->qchild ?></td>
            <td><?= htmlspecialchars($add->firstname) ?></td>
            <td><?= htmlspecialchars($add->servico) ?></td>
            <td>R$ <?= number_format(($add->valueservice * $add->qpax) + (($add->valueservice / 2) * $add->qchild), 2, ',', '.') ?></td>
            <td><?= htmlspecialchars($add->statuus) ?></td>
        </tr>
        <?php endforeach ?>
        <?php foreach ($pags as $pag): if ((float)$pag->valor <= 0) continue; ?>
        <tr class="pay-row">
            <td colspan="3"><strong>Data Pagamento:</strong> <?= date("d/m/Y", strtotime($pag->dia)) ?></td>
            <td colspan="2"><strong>Método:</strong> <?= htmlspecialchars($pag->pagamento) ?></td>
            <td colspan="2"><strong>Valor:</strong> R$ <?= number_format((float)$pag->valor, 2, ',', '.') ?></td>
            <td colspan="2"><strong>Recebido por:</strong> <?= htmlspecialchars(strtoupper($pag->firstname . ' ' . $pag->lastname)) ?></td>
        </tr>
        <?php endforeach ?>
    <?php endforeach ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="4">Valor Total</td>
            <td>Total Pago (quitados)</td>
            <td>Total Recebido</td>
            <td>A Pagar</td>
            <td colspan="2"></td>
        </tr>
        <tr>
            <td colspan="4">R$ <?= number_format($somaDosServicos1 + $somaDosServicos2, 2, ',', '.') ?></td>
            <td>R$ <?= number_format($totalPago, 2, ',', '.') ?></td>
            <td>R$ <?= number_format($totalRecebido, 2, ',', '.') ?></td>
            <td>R$ <?= number_format(($somaDosServicos1 + $somaDosServicos2) - $totalPago, 2, ',', '.') ?></td>
            <td colspan="2"></td>
        </tr>
    </tfoot>
</table>

<?php else: /* RESUMIDO */ ?>
<table>
    <thead>
        <tr>
            <th>EMBARQUE</th>
            <th>VOUCHER</th>
            <th>CLIENTE</th>
            <th>PAX</th>
            <th>P/C</th>
            <th>RES</th>
            <th>SERVIÇO</th>
            <th>BRUTO</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($registros as $item):
        $fin   = $financials[$item->numbervoucher] ?? ['credito' => 0, 'guia' => 0, 'agente' => 0];
        $bruto = ($item->valueservice * $item->qtdpax) + (($item->valueservice / 2) * $item->qtdchild);
        $adds  = $adicionais[(int)$item->id] ?? [];
    ?>
        <tr>
            <td><?= date("d/m/Y", strtotime($item->dateinput)) ?></td>
            <td><?= htmlspecialchars($item->numbervoucher) ?></td>
            <td><?= htmlspecialchars($item->cliente) ?></td>
            <td><?= htmlspecialchars($item->pax) ?></td>
            <td><?= (int)$item->qtdpax ?>/<?= (int)$item->qtdchild ?></td>
            <td><?= htmlspecialchars($item->firstname . ' ' . $item->lastname) ?></td>
            <td><?= htmlspecialchars($item->servico) ?></td>
            <td>R$ <?= number_format($bruto, 2, ',', '.') ?></td>
        </tr>
        <?php foreach ($adds as $add): ?>
        <tr class="add-row">
            <td><?= date("d/m/Y", strtotime($add->dateinput)) ?></td>
            <td><?= htmlspecialchars($add->numbervoucher) ?></td>
            <td><?= htmlspecialchars($add->cliente) ?></td>
            <td><?= htmlspecialchars($add->pax) ?></td>
            <td><?= (int)$add->qpax ?>/<?= (int)$add->qchild ?></td>
            <td><?= htmlspecialchars($add->firstname) ?></td>
            <td><?= htmlspecialchars($add->servico) ?></td>
            <td>R$ <?= number_format(($add->valueservice * $add->qpax) + (($add->valueservice / 2) * $add->qchild), 2, ',', '.') ?></td>
        </tr>
        <?php endforeach ?>
    <?php endforeach ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="4">Valor Total</td>
            <td>Total Pago</td>
            <td>Total Recebido</td>
            <td>A Pagar</td>
            <td></td>
        </tr>
        <tr>
            <td colspan="4">R$ <?= number_format($somaDosServicos1 + $somaDosServicos2, 2, ',', '.') ?></td>
            <td>R$ <?= number_format($totalPago, 2, ',', '.') ?></td>
            <td>R$ <?= number_format($totalRecebido, 2, ',', '.') ?></td>
            <td>R$ <?= number_format(($somaDosServicos1 + $somaDosServicos2) - $totalPago, 2, ',', '.') ?></td>
            <td></td>
        </tr>
    </tfoot>
</table>
<?php endif ?>

<script>window.print();</script>
</body>
</html>
