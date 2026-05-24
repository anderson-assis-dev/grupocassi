<?php
require_once '../../config.php';

// --- helpers -----------------------------------------------------------------
function esc(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function brl(float $v): string  { return 'R$ ' . number_format($v, 2, ',', '.'); }
function dt(?string $s): string { return $s && $s !== '0000-00-00' ? date('d/m/Y', strtotime($s)) : '—'; }

// --- inputs ------------------------------------------------------------------
$faturaId  = isset($_POST['idfatura'])       ? (int)$_POST['idfatura']       : null;
$idcliente = isset($_POST['cliente'])        ? (int)$_POST['cliente']        : 0;
$datainicio = $_POST['periodoinicial'] ?? '';
$datafim    = $_POST['periodofinal']   ?? '';

// --- fatura + cliente --------------------------------------------------------
if ($faturaId) {
    $st = $pdo->prepare(
        'SELECT f.id, f.tarifa, f.credito, f.datavencimento, f.status,
                c.fullname, c.corporatename, c.cnpj, c.cep, c.tel01, c.email,
                f.dateinput, f.dateoutput, f.idcliente
         FROM ct_fatura f LEFT JOIN ct_cliente c ON c.id = f.idcliente
         WHERE f.id = ?'
    );
    $st->execute([$faturaId]);
} else {
    $st = $pdo->prepare(
        'SELECT f.id, f.tarifa, f.credito, f.datavencimento, f.status,
                c.fullname, c.corporatename, c.cnpj, c.cep, c.tel01, c.email,
                f.dateinput, f.dateoutput, f.idcliente
         FROM ct_fatura f LEFT JOIN ct_cliente c ON c.id = f.idcliente
         WHERE c.id = ? AND f.dateinput = ? AND f.dateoutput = ? AND f.situacao = 1
         LIMIT 1'
    );
    $st->execute([$idcliente, $datainicio, $datafim]);
}
$fatura = $st->fetch(PDO::FETCH_ASSOC);

if (!$fatura) {
    echo '<p style="font-family:sans-serif;padding:24px;color:#b91c1c">Nenhuma fatura encontrada para os parâmetros informados.</p>';
    exit;
}

$faturaId   = (int)$fatura['id'];
$idcliente  = (int)$fatura['idcliente'];
$datainicio = $fatura['dateinput'];
$datafim    = $fatura['dateoutput'];

// --- main reservas -----------------------------------------------------------
$st = $pdo->prepare(
    'SELECT r.id, r.numbervoucher, r.numberfatura, r.pax, r.qtdpax, r.qtdchild,
            r.valueservice AS valorP, r.dateinput, r.horaap, r.idservico, r.idcliente,
            s.fullname AS servico
     FROM ct_reserva r
     LEFT JOIN ct_servico s ON s.id = r.idservico
     WHERE r.dateinput >= ? AND r.dateinput <= ?
       AND r.idcliente = ? AND r.idstatusinvoice <> 2
     ORDER BY r.numbervoucher'
);
$st->execute([$datainicio, $datafim, $idcliente]);
$reservas = $st->fetchAll(PDO::FETCH_ASSOC);

if (empty($reservas)) {
    echo '<p style="font-family:sans-serif;padding:24px">Nenhuma reserva encontrada para o período informado.</p>';
    exit;
}

// --- bulk queries ------------------------------------------------------------
$resIds   = array_column($reservas, 'id');
$vouchers = array_unique(array_column($reservas, 'numbervoucher'));
$svcIds   = array_unique(array_column($reservas, 'idservico'));

// net price per service for this client
$netMap = [];
if (!empty($svcIds)) {
    $in = implode(',', array_map('intval', $svcIds));
    $st = $pdo->prepare("SELECT idservice, valuenet FROM ct_clientservice WHERE idclient = ? AND idservice IN ($in)");
    $st->execute([$idcliente]);
    foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $netMap[(int)$r['idservice']] = (float)$r['valuenet'];
    }
}

// payment credits per voucher
$payMap = [];
if (!empty($vouchers)) {
    $ph = implode(',', array_fill(0, count($vouchers), '?'));
    $st = $pdo->prepare(
        "SELECT cfc.numbervoucher, cfc.datacredit AS dia, cfc.valuecredit AS valor, cc.name AS pagamento
         FROM ct_createfaturacredit cfc
         LEFT JOIN ct_currentaccount cc ON cc.id = cfc.idaccountcurrent
         WHERE cfc.numbervoucher IN ($ph)"
    );
    $st->execute($vouchers);
    foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $payMap[$r['numbervoucher']][] = $r;
    }
}

// add-on services per reservation
$addMap = [];
if (!empty($resIds)) {
    $in = implode(',', array_map('intval', $resIds));
    $st = $pdo->prepare(
        'SELECT ra.idrecently, ra.qpax, ra.qchild, ra.valueservice AS valorS, ra.idservice,
                ra.horaap, ra.dateinput,
                r.pax, r.numbervoucher, r.idcliente,
                s.fullname AS servico
         FROM ct_recentlyadd ra
         LEFT JOIN ct_reserva r ON r.id = ra.idrecently
         LEFT JOIN ct_servico s ON s.id = ra.idservice
         WHERE ra.idrecently IN (' . $in . ')'
    );
    $st->execute();
    $addRows = $st->fetchAll(PDO::FETCH_ASSOC);
    foreach ($addRows as $r) {
        $addMap[(int)$r['idrecently']][] = $r;
    }

    // bulk net for add-on services
    $addSvcIds = array_unique(array_map('intval', array_column($addRows, 'idservice')));
    if (!empty($addSvcIds)) {
        $in2 = implode(',', $addSvcIds);
        $st  = $pdo->prepare("SELECT idservice, valuenet FROM ct_clientservice WHERE idclient = ? AND idservice IN ($in2)");
        $st->execute([$idcliente]);
        foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $netMap[(int)$r['idservice']] = (float)$r['valuenet'];
        }
    }
}

// fatura payment credits (ct_faturadesc)
$st = $pdo->prepare('SELECT valor, descricao, datapagamento FROM ct_faturadesc WHERE id_fatura = ? ORDER BY datapagamento ASC');
$st->execute([$faturaId]);
$faturaCreditos     = $st->fetchAll(PDO::FETCH_ASSOC);
$totalFaturaCreditos = array_sum(array_column($faturaCreditos, 'valor'));

// --- update numberfatura for unlinked reservations ---------------------------
$toUpdate = [];
foreach ($reservas as $r) {
    if ((int)$r['numberfatura'] === 0) {
        $toUpdate[] = $r['numbervoucher'];
    }
}
if (!empty($toUpdate)) {
    $ph = implode(',', array_fill(0, count($toUpdate), '?'));
    $params = array_merge([$faturaId], $toUpdate);
    $pdo->prepare("UPDATE ct_reserva SET numberfatura=? WHERE numbervoucher IN ($ph)")->execute($params);
}

// --- pre-compute per-reservation totals --------------------------------------
$rows = [];
foreach ($reservas as $r) {
    $net   = $netMap[(int)$r['idservico']] ?? 0;
    $price = $net > 0 ? $net : (float)$r['valorP'];
    $sub   = $price * $r['qtdpax'] + ($price / 2) * $r['qtdchild'];

    $addLines = [];
    $addSub   = 0.0;
    foreach ($addMap[(int)$r['id']] ?? [] as $a) {
        $aNet   = $netMap[(int)$a['idservice']] ?? 0;
        $aPrice = $aNet > 0 ? $aNet : (float)$a['valorS'];
        $aSub   = $aPrice * $a['qpax'] + ($aPrice / 2) * $a['qchild'];
        $addSub   += $aSub;
        $addLines[] = array_merge($a, ['_sub' => $aSub]);
    }

    $pags   = $payMap[$r['numbervoucher']] ?? [];
    $liquid = array_sum(array_column($pags, 'valor'));
    $nf     = (int)$r['numberfatura'];
    $linked = $nf === 0 || $nf === $faturaId;

    $rows[] = [
        'r'       => $r,
        'sub'     => $sub,
        'addSub'  => $addSub,
        'adds'    => $addLines,
        'pags'    => $pags,
        'liquid'  => $liquid,
        'linked'  => $linked,
        'other'   => (!$linked) ? $nf : null,
    ];
}

$grandBruto = array_sum(array_column($rows, 'sub')) + array_sum(array_column($rows, 'addSub'));
$saldo      = $fatura['tarifa'] - ($fatura['credito'] + $totalFaturaCreditos);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Fatura #<?= $faturaId ?> — <?= esc($fatura['corporatename'] ?: $fatura['fullname']) ?></title>
<style>
    @page { size: A4 portrait; margin: 0; }
    @media print {
        body { margin: 8mm 12mm; }
        .no-print { display: none !important; }
        .page-break { page-break-before: always; }
    }
    * { box-sizing: border-box; }
    body { font-family: Arial, sans-serif; font-size: 10px; color: #1e293b; margin: 14mm 16mm; }

    /* no-print toolbar */
    .toolbar { display: flex; gap: 10px; margin-bottom: 14px; }
    .btn-print { background: #1e4770; color: #fff; border: none; border-radius: 8px;
                 padding: 8px 20px; font-size: 13px; font-weight: 700; cursor: pointer; }
    .btn-print:hover { background: #2a5f96; }
    .btn-back  { background: none; border: 1.5px solid #1e4770; color: #1e4770; border-radius: 8px;
                 padding: 7px 18px; font-size: 13px; font-weight: 600; cursor: pointer; text-decoration: none;
                 display: inline-flex; align-items: center; }
    .btn-back:hover { background: #1e4770; color: #fff; }

    /* header */
    .inv-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 10px; }
    .inv-logo   { width: 180px; }
    .inv-title  { text-align: right; }
    .inv-title h1 { font-size: 16px; font-weight: 800; color: #1e4770; margin: 0 0 2px; }
    .inv-title .inv-num { font-size: 12px; color: #64748b; }

    /* divider */
    .inv-divider { border: none; border-top: 2px solid #1e4770; margin: 8px 0; }

    /* client block */
    .inv-client { display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px 14px; margin-bottom: 10px; }
    .inv-client dt { font-size: 8px; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin: 0; }
    .inv-client dd { font-size: 10px; margin: 0 0 4px; font-weight: 600; }

    /* summary box */
    .inv-summary { display: flex; gap: 0; margin-bottom: 12px; border: 1.5px solid #e2e8f0; border-radius: 8px; overflow: hidden; }
    .inv-summary-cell { flex: 1; padding: 8px 12px; border-right: 1px solid #e2e8f0; }
    .inv-summary-cell:last-child { border-right: none; }
    .inv-summary-cell .lbl { font-size: 8px; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin-bottom: 3px; }
    .inv-summary-cell .val { font-size: 13px; font-weight: 800; color: #1e4770; }
    .inv-summary-cell .val.neg { color: #b91c1c; }
    .inv-summary-cell .val.pos { color: #166534; }

    /* tables */
    table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
    thead tr { background: #1e4770; color: #fff; }
    thead th { padding: 5px 6px; font-size: 9px; text-align: left; font-weight: 700; }
    tbody td { padding: 5px 6px; border-bottom: 1px solid #f1f5f9; font-size: 9px; }
    tbody tr:nth-child(even) td { background: #f8fafc; }
    tbody tr.add-row td { background: #eff6ff; color: #334155; font-style: italic; }
    tbody tr.pay-row td { background: #f0fdf4; color: #166534; }
    tbody tr.other-row td { background: #fef9c3; color: #713f12; font-style: italic; }
    tfoot td { font-weight: 700; background: #f1f5f9; border-top: 2px solid #1e4770; padding: 6px; font-size: 9px; }
    tfoot td.bruto { color: #1e4770; }
    tfoot td.liquid { color: #166534; }

    /* credits section */
    .credits-title { font-size: 11px; font-weight: 800; color: #1e4770; margin: 14px 0 6px; border-left: 3px solid #1e4770; padding-left: 8px; }

    /* footer */
    .inv-footer { margin-top: 16px; font-size: 8px; color: #94a3b8; text-align: center; border-top: 1px solid #e2e8f0; padding-top: 6px; }
</style>
</head>
<body>

<div class="no-print toolbar">
    <button class="btn-print" onclick="window.print()">
        Imprimir / Salvar PDF
    </button>
    <a class="btn-back" href="javascript:history.back()">← Voltar</a>
</div>

<!-- header -->
<div class="inv-header">
    <img class="inv-logo" src="../../images/logo.png" alt="Cassi Turismo">
    <div class="inv-title">
        <h1>FATURA</h1>
        <div class="inv-num">#<?= $faturaId ?></div>
        <div style="font-size:9px;color:#64748b;margin-top:4px">
            Período: <?= dt($datainicio) ?> a <?= dt($datafim) ?>
        </div>
        <?php if ($fatura['datavencimento'] && $fatura['datavencimento'] !== '0000-00-00'): ?>
            <div style="font-size:9px;color:#64748b">Vencimento: <?= dt($fatura['datavencimento']) ?></div>
        <?php endif ?>
    </div>
</div>
<hr class="inv-divider">

<!-- client info -->
<dl class="inv-client">
    <div>
        <dt>Razão Social</dt>
        <dd><?= esc($fatura['corporatename'] ?: $fatura['fullname']) ?></dd>
    </div>
    <div>
        <dt>CNPJ</dt>
        <dd><?= esc($fatura['cnpj'] ?: '—') ?></dd>
    </div>
    <div>
        <dt>CEP / Endereço</dt>
        <dd><?= esc($fatura['cep'] ?: '—') ?></dd>
    </div>
    <div>
        <dt>Telefone</dt>
        <dd><?= esc($fatura['tel01'] ?: '—') ?></dd>
    </div>
    <div>
        <dt>E-mail</dt>
        <dd><?= esc($fatura['email'] ?: '—') ?></dd>
    </div>
    <div>
        <dt>Emissão</dt>
        <dd><?= date('d/m/Y H:i') ?></dd>
    </div>
</dl>

<!-- financial summary -->
<div class="inv-summary">
    <div class="inv-summary-cell">
        <div class="lbl">Total Bruto</div>
        <div class="val"><?= brl((float)$fatura['tarifa']) ?></div>
    </div>
    <div class="inv-summary-cell">
        <div class="lbl">Crédito Fatura</div>
        <div class="val"><?= brl((float)$fatura['credito']) ?></div>
    </div>
    <div class="inv-summary-cell">
        <div class="lbl">Pagamentos</div>
        <div class="val"><?= brl($totalFaturaCreditos) ?></div>
    </div>
    <?php
        $saldoLbl   = $saldo < 0 ? 'Crédito a Favor' : 'Saldo a Pagar';
        if ($saldo < 0)      { $saldoClass = 'pos'; }
        elseif ($saldo > 0)  { $saldoClass = 'neg'; }
        else                 { $saldoClass = ''; }
    ?>
    <div class="inv-summary-cell">
        <div class="lbl"><?= $saldoLbl ?></div>
        <div class="val <?= $saldoClass ?>"><?= brl(abs($saldo)) ?></div>
    </div>
</div>

<!-- reservations table -->
<table>
    <thead>
        <tr>
            <th>Voucher</th>
            <th>Pax</th>
            <th>Serviço</th>
            <th>Data</th>
            <th>Hora</th>
            <th>Pax/Inf</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($rows as $row):
        $r = $row['r'];
    ?>
        <?php if (!$row['linked']): ?>
            <tr class="other-row">
                <td colspan="7">
                    <?= esc($r['numbervoucher']) ?> — já faturado na Fatura #<?= $row['other'] ?>
                </td>
            </tr>
        <?php else: ?>
            <tr>
                <td><strong><?= esc($r['numbervoucher']) ?></strong></td>
                <td><?= esc($r['pax']) ?></td>
                <td><?= esc($r['servico']) ?></td>
                <td><?= dt($r['dateinput']) ?></td>
                <td><?= esc($r['horaap']) ?></td>
                <td><?= (int)$r['qtdpax'] ?>/<?= (int)$r['qtdchild'] ?></td>
                <td><?= brl($row['sub']) ?></td>
            </tr>
            <?php foreach ($row['adds'] as $a): ?>
                <tr class="add-row">
                    <td style="padding-left:14px">+ <?= esc($a['numbervoucher']) ?></td>
                    <td><?= esc($a['pax']) ?></td>
                    <td><?= esc($a['servico']) ?></td>
                    <td><?= dt($a['dateinput']) ?></td>
                    <td><?= esc($a['horaap']) ?></td>
                    <td><?= (int)$a['qpax'] ?>/<?= (int)$a['qchild'] ?></td>
                    <td><?= brl($a['_sub']) ?></td>
                </tr>
            <?php endforeach ?>
            <?php foreach ($row['pags'] as $p): ?>
                <tr class="pay-row">
                    <td colspan="3">Pgto: <?= esc($p['pagamento'] ?? '—') ?></td>
                    <td colspan="2"><?= dt($p['dia']) ?></td>
                    <td></td>
                    <td>(<?= brl((float)$p['valor']) ?>)</td>
                </tr>
            <?php endforeach ?>
            <tfoot>
                <tr>
                    <td colspan="6" class="bruto">Valor Bruto</td>
                    <td class="bruto"><?= brl($row['sub'] + $row['addSub']) ?></td>
                </tr>
                <tr>
                    <td colspan="6" class="liquid">Valor Líquido</td>
                    <td class="liquid"><?= brl($row['sub'] + $row['addSub'] - $row['liquid']) ?></td>
                </tr>
            </tfoot>
        <?php endif ?>
    <?php endforeach ?>
    </tbody>
</table>

<?php if (!empty($faturaCreditos)): ?>
    <div class="credits-title">Resumo Financeiro — Pagamentos Registrados</div>
    <table>
        <thead>
            <tr>
                <th>Valor</th>
                <th>Descrição</th>
                <th>Data de Pagamento</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($faturaCreditos as $c): ?>
                <tr>
                    <td><?= brl((float)$c['valor']) ?></td>
                    <td><?= esc($c['descricao']) ?></td>
                    <td><?= dt($c['datapagamento']) ?></td>
                </tr>
            <?php endforeach ?>
        </tbody>
        <tfoot>
            <tr>
                <td class="liquid"><?= brl($totalFaturaCreditos) ?></td>
                <td colspan="2" class="liquid">Total de créditos do cliente</td>
            </tr>
        </tfoot>
    </table>
<?php endif ?>

<div class="inv-footer">
    Cassi Turismo &mdash; documento gerado em <?= date('d/m/Y H:i') ?>
</div>

<script>window.print();</script>
</body>
</html>
