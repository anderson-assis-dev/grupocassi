<?php
ini_set('max_execution_time', 120);
require_once '../../config.php';
$pdo->exec("set names utf8");

// ── Entrada ───────────────────────────────────────────────────────────────────
$datainicio  = $_POST['periodoinicial'] ?? date('Y-m-01');
$datafim     = $_POST['periodofinal']   ?? date('Y-m-d');
$idcliente   = (int)($_POST['cliente']       ?? 0);
$responsavel = (int)($_POST['responsavel']   ?? 0);
$tipo        = (int)($_POST['tiporelatorio'] ?? 0);
$nomepax     = trim($_POST['nomepax']        ?? '');
$statusInput = array_map('intval', (array)($_POST['status'] ?? [0]));
$statuses    = array_values(array_filter($statusInput, fn($s) => $s > 0));

// ── Helpers ───────────────────────────────────────────────────────────────────
function esc($v): string   { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function brl(float $v): string { return 'R$ ' . number_format($v, 2, ',', '.'); }
function dt($v): string    { return ($v && $v !== '0000-00-00') ? date('d/m/Y', strtotime($v)) : '-'; }
function tm($v): string    { return ($v && $v !== '00:00:00')   ? date('H:i',   strtotime($v)) : '-'; }

// ── WHERE dinâmico para ct_reserva ────────────────────────────────────────────
$where  = ['r.dateinput >= ?', 'r.dateinput <= ?'];
$params = [$datainicio, $datafim];

if ($responsavel > 0) { $where[] = 'r.idresponsavel = ?'; $params[] = $responsavel; }
if ($idcliente   > 0) { $where[] = 'r.idcliente = ?';     $params[] = $idcliente; }
if ($statuses) {
    $ph      = implode(',', array_fill(0, count($statuses), '?'));
    $where[] = "r.idstatusinvoice IN ($ph)";
    $params  = array_merge($params, $statuses);
}
if ($nomepax !== '') { $where[] = 'r.pax = ?'; $params[] = $nomepax; }
$wSql = 'WHERE ' . implode(' AND ', $where);

// ─────────────────────────────────────────────────────────────────────────────
// TIPO 0 — Descritivo (por embarque)
// ─────────────────────────────────────────────────────────────────────────────
if ($tipo === 0) {

    // 1. Reservas principais
    $stRes = $pdo->prepare(
        "SELECT r.id, r.dateinput, r.abertura, r.horaap, r.numbervoucher,
                c.namefantazia AS cliente, r.documento, r.pax, r.qtdpax, r.qtdchild,
                CONCAT(u.firstname, ' ', u.lastname) AS responsavel,
                s.fullname AS servico, cp.namepayment AS pagamento,
                r.valueservice, r.idstatusinvoice, si.nameinvoice AS status
         FROM ct_reserva r
         LEFT JOIN ct_cliente              c  ON c.id  = r.idcliente
         LEFT JOIN ct_usuario              u  ON u.id  = r.idresponsavel
         LEFT JOIN ct_servico              s  ON s.id  = r.idservico
         LEFT JOIN `ct_form_of_ payment`  cp  ON cp.id = r.idpayment
         LEFT JOIN ct_statusinvoice        si ON si.id = r.idstatusinvoice
         $wSql ORDER BY r.dateinput, r.abertura"
    );
    $stRes->execute($params);
    $reservas   = $stRes->fetchAll(PDO::FETCH_ASSOC);
    $vouchers   = array_column($reservas, 'numbervoucher');
    $reservaIds = array_map('intval', array_column($reservas, 'id'));

    // 2. Financeiro bulk (credito / guia / agente) por voucher
    $finMap = [];
    if ($vouchers) {
        $inV    = implode(',', array_fill(0, count($vouchers), '?'));
        $stFin  = $pdo->prepare(
            "SELECT numbervoucher,
                    COALESCE(SUM(valuecredit), 0)  AS credito,
                    COALESCE(SUM(valueguia),   0)  AS guia,
                    COALESCE(SUM(valueagente), 0)  AS agente
             FROM ct_createfaturacredit
             WHERE numbervoucher IN ($inV) GROUP BY numbervoucher"
        );
        $stFin->execute($vouchers);
        foreach ($stFin->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $finMap[$row['numbervoucher']] = $row;
        }
    }

    // 3. Descrição de pagamentos bulk
    $pagMap = [];
    if ($vouchers) {
        $stPag = $pdo->prepare(
            "SELECT cfc.numbervoucher, cfc.datacredit AS dia,
                    cc.name AS metodo, cfc.valuecredit AS valor
             FROM ct_createfaturacredit cfc
             LEFT JOIN ct_currentaccount cc ON cc.id = cfc.idaccountcurrent
             WHERE cfc.numbervoucher IN ($inV) ORDER BY cfc.datacredit"
        );
        $stPag->execute($vouchers);
        foreach ($stPag->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $pagMap[$row['numbervoucher']][] = $row;
        }
    }

    // 4. Serviços adicionais bulk (ct_recentlyadd)
    $addMap = [];
    if ($reservaIds) {
        $inR    = implode(',', array_fill(0, count($reservaIds), '?'));
        $addSql = "SELECT ra.idrecently, ra.dateinput, ra.horaap, ra.valueservice,
                          ra.qpax, ra.qchild, s.fullname AS servico,
                          cp.namepayment AS pagamento, si.nameinvoice AS status
                   FROM ct_recentlyadd ra
                   LEFT JOIN ct_reserva              r  ON r.id  = ra.idrecently
                   LEFT JOIN ct_servico              s  ON s.id  = ra.idservice
                   LEFT JOIN `ct_form_of_ payment`  cp  ON cp.id = r.idpayment
                   LEFT JOIN ct_statusinvoice        si ON si.id = r.idstatusinvoice
                   WHERE ra.dateinput >= ? AND ra.dateinput <= ?
                     AND ra.idrecently IN ($inR)";
        $stAdd  = $pdo->prepare($addSql);
        $stAdd->execute(array_merge([$datainicio, $datafim], $reservaIds));
        foreach ($stAdd->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $addMap[(int)$row['idrecently']][] = $row;
        }
    }

    // 5. Totais globais
    $totalBruto = $totalComissao = $totalLiquido = $totalPago = 0.0;
    foreach ($reservas as $r) {
        $bruto  = ($r['valueservice'] * $r['qtdpax']) + (($r['valueservice'] / 2) * $r['qtdchild']);
        $fin    = $finMap[$r['numbervoucher']] ?? ['guia' => 0, 'agente' => 0, 'credito' => 0];
        $comis  = (float)$fin['guia'] + (float)$fin['agente'];
        $totalBruto    += $bruto;
        $totalComissao += $comis;
        $totalLiquido  += $bruto - $comis;
        if ($r['idstatusinvoice'] == 3) { $totalPago += (float)$fin['credito']; }
    }
    foreach ($addMap as $addRows) {
        foreach ($addRows as $add) {
            $totalBruto += ($add['valueservice'] * $add['qpax']) + (($add['valueservice'] / 2) * $add['qchild']);
        }
    }

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Relatório Conferência – Embarque</title>
<style>
    @page { size: A4 landscape; margin: 0; }
    @media print { body { margin: 7mm 9mm; } .no-print { display: none; } }
    body  { font-family: Arial, sans-serif; font-size: 9px; color: #222; margin: 10mm 12mm; }
    img   { width: 220px; margin-bottom: 6px; }
    h2    { font-size: 12px; margin: 0 0 2px; color: #1e4770; }
    p.sub { font-size: 9px; color: #555; margin: 0 0 10px; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
    th    { background: #1e4770; color: #fff; font-size: 8px; padding: 5px 4px;
            text-align: left; white-space: nowrap; }
    td    { border-bottom: 1px solid #e5e7eb; padding: 4px; font-size: 8.5px;
            vertical-align: middle; }
    tr.odd  td { background: #fff; }
    tr.even td { background: #f8fafc; }
    tr.add-row  td { background: #eef4ff; color: #2a5f96; font-size: 8px; }
    tr.pay-row  td { background: #fafafa; color: #555; font-size: 7.5px; font-style: italic; }
    tfoot td   { font-weight: bold; background: #e8f0fb;
                 border-top: 2px solid #1e4770; padding: 5px 4px; font-size: 8.5px; }
    h4  { font-size: 10px; margin: 14px 0 4px; color: #1e4770; border-bottom: 1px solid #e0e7ef; padding-bottom: 3px; }
    .status-pago     { color: #15803d; font-weight: 700; }
    .status-cancelado{ color: #dc2626; font-weight: 700; }
    .status-pendente { color: #ca8a04; font-weight: 700; }
    .text-right { text-align: right; }
    .btn-print { display: inline-block; margin-bottom: 12px; padding: 8px 20px;
                 background: #1e4770; color: #fff; border: 0; border-radius: 8px;
                 cursor: pointer; font-size: 12px; font-weight: 700; }
</style>
</head>
<body>
<button class="btn-print no-print" onclick="window.print()">Imprimir / Salvar PDF</button>
<img src="../../images/logo.png" alt="Logo">
<h2>Relatório de Conferência – Por Data de Embarque</h2>
<p class="sub">
    Período: <strong><?= dt($datainicio) ?> até <?= dt($datafim) ?></strong>
    <?= $idcliente   > 0 ? ' &nbsp;|&nbsp; Agência filtrada' : '' ?>
    <?= $responsavel > 0 ? ' &nbsp;|&nbsp; Responsável filtrado' : '' ?>
    <?= $nomepax     !== '' ? ' &nbsp;|&nbsp; Pax: ' . esc($nomepax) : '' ?>
    &nbsp;|&nbsp; Impresso em: <?= date('d/m/Y H:i') ?>
</p>

<?php if (empty($reservas) && empty($addMap)): ?>
<p style="color:#888;padding:20px 0;">Nenhum registro encontrado para os filtros informados.</p>
<?php else: ?>

<table>
    <thead>
        <tr>
            <th>Abertura</th>
            <th>Embarque</th>
            <th>AP</th>
            <th>Voucher</th>
            <th>Agência</th>
            <th>Documento</th>
            <th>Pax</th>
            <th>P/C</th>
            <th>Responsável</th>
            <th>Serviço</th>
            <th>Pagamento</th>
            <th class="text-right">Valor</th>
            <th class="text-right">Comissão</th>
            <th class="text-right">Líquido</th>
            <th class="text-right">Recebido</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
    <?php
    $rowNum = 0;
    foreach ($reservas as $r):
        $bruto  = ($r['valueservice'] * $r['qtdpax']) + (($r['valueservice'] / 2) * $r['qtdchild']);
        $fin    = $finMap[$r['numbervoucher']] ?? ['guia' => 0, 'agente' => 0, 'credito' => 0];
        $comis  = (float)$fin['guia'] + (float)$fin['agente'];
        $liquido= $bruto - $comis;
        $rowCss = ($rowNum++ % 2 === 0) ? 'even' : 'odd';

        $statusCss = match(true) {
            $r['idstatusinvoice'] == 3 => 'status-pago',
            $r['idstatusinvoice'] == 2 => 'status-cancelado',
            default                    => 'status-pendente',
        };
    ?>
    <tr class="<?= $rowCss ?>">
        <td><?= dt($r['abertura'])   ?></td>
        <td><?= dt($r['dateinput'])  ?></td>
        <td><?= tm($r['horaap'])     ?></td>
        <td><?= esc($r['numbervoucher']) ?></td>
        <td><?= esc($r['cliente'])   ?></td>
        <td><?= esc($r['documento']) ?></td>
        <td><?= esc($r['pax'])       ?></td>
        <td><?= (int)$r['qtdpax'] ?>/<?= (int)$r['qtdchild'] ?></td>
        <td><?= esc(strtoupper($r['responsavel'])) ?></td>
        <td><?= esc($r['servico'])   ?></td>
        <td><?= esc($r['pagamento']) ?></td>
        <td class="text-right"><?= brl($bruto)   ?></td>
        <td class="text-right"><?= brl($comis)   ?></td>
        <td class="text-right"><?= brl($liquido) ?></td>
        <td class="text-right"><?= brl((float)$fin['credito']) ?></td>
        <td class="<?= $statusCss ?>"><?= esc($r['status']) ?></td>
    </tr>

    <?php foreach ($addMap[(int)$r['id']] ?? [] as $add):
        $addBruto = ($add['valueservice'] * $add['qpax']) + (($add['valueservice'] / 2) * $add['qchild']);
    ?>
    <tr class="add-row">
        <td><?= dt($add['dateinput']) ?></td>
        <td><?= dt($add['dateinput']) ?></td>
        <td><?= tm($add['horaap'])    ?></td>
        <td><?= esc($r['numbervoucher']) ?></td>
        <td><?= esc($r['cliente'])    ?></td>
        <td>—</td>
        <td><?= esc($r['pax'])        ?></td>
        <td><?= (int)$add['qpax'] ?>/<?= (int)$add['qchild'] ?></td>
        <td><?= esc(strtoupper($r['responsavel'])) ?></td>
        <td><?= esc($add['servico'])  ?> <em style="font-size:7px">(adicional)</em></td>
        <td><?= esc($add['pagamento']) ?></td>
        <td class="text-right"><?= brl($addBruto) ?></td>
        <td class="text-right">—</td>
        <td class="text-right"><?= brl($addBruto) ?></td>
        <td class="text-right">—</td>
        <td><?= esc($add['status']) ?></td>
    </tr>
    <?php endforeach ?>

    <?php foreach ($pagMap[$r['numbervoucher']] ?? [] as $pag): ?>
    <tr class="pay-row">
        <td colspan="10" style="padding-left:16px">
            <strong>Pgto:</strong> <?= dt($pag['dia']) ?>
            &nbsp;&nbsp; <strong>Método:</strong> <?= esc($pag['metodo']) ?>
            &nbsp;&nbsp; <strong>Valor:</strong> <?= brl((float)$pag['valor']) ?>
        </td>
        <td colspan="6"></td>
    </tr>
    <?php endforeach ?>

    <?php endforeach ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="11">TOTAIS</td>
            <td class="text-right"><?= brl($totalBruto)    ?></td>
            <td class="text-right"><?= brl($totalComissao) ?></td>
            <td class="text-right"><?= brl($totalLiquido)  ?></td>
            <td class="text-right"><?= brl($totalPago)     ?></td>
            <td><?= brl($totalBruto - $totalPago) ?> a receber</td>
        </tr>
    </tfoot>
</table>

<?php endif ?>

<script>window.print();</script>
</body>
</html>

<?php
// ─────────────────────────────────────────────────────────────────────────────
// TIPO 1 — Resumido por cliente
// ─────────────────────────────────────────────────────────────────────────────
} else {

    // 1. Totais de reservas por cliente
    $stRes = $pdo->prepare(
        "SELECT r.idcliente, c.fullname,
                COALESCE(SUM((r.valueservice * r.qtdpax) + ((r.valueservice / 2) * r.qtdchild)), 0) AS total
         FROM ct_reserva r
         LEFT JOIN ct_cliente c ON c.id = r.idcliente
         $wSql AND c.fullname NOT LIKE 'cassi%'
         GROUP BY r.idcliente, c.fullname ORDER BY c.fullname"
    );
    $stRes->execute($params);
    $porCliente = [];
    foreach ($stRes->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $porCliente[$row['idcliente']] = ['fullname' => $row['fullname'], 'total' => (float)$row['total'], 'add' => 0.0, 'credito' => 0.0];
    }

    // 2. Totais de adicionais por cliente
    if ($porCliente) {
        $inC   = implode(',', array_fill(0, count($porCliente), '?'));
        $ids   = array_keys($porCliente);
        $addWhere = ['ra.dateinput >= ?', 'ra.dateinput <= ?', "r.idcliente IN ($inC)"];
        $addP  = array_merge([$datainicio, $datafim], $ids);
        if ($statuses) {
            $ph = implode(',', array_fill(0, count($statuses), '?'));
            $addWhere[] = "r.idstatusinvoice IN ($ph)";
            $addP = array_merge($addP, $statuses);
        }
        $stAdd = $pdo->prepare(
            "SELECT r.idcliente,
                    COALESCE(SUM((ra.valueservice * ra.qpax) + ((ra.valueservice / 2) * ra.qchild)), 0) AS total
             FROM ct_recentlyadd ra
             LEFT JOIN ct_reserva r ON r.id = ra.idrecently
             WHERE " . implode(' AND ', $addWhere) . "
             GROUP BY r.idcliente"
        );
        $stAdd->execute($addP);
        foreach ($stAdd->fetchAll(PDO::FETCH_ASSOC) as $row) {
            if (isset($porCliente[$row['idcliente']])) {
                $porCliente[$row['idcliente']]['add'] = (float)$row['total'];
            }
        }
    }

    // 3. Créditos recebidos por cliente (status faturado = 3)
    if ($porCliente) {
        $credP  = array_merge([$datainicio, $datafim], array_keys($porCliente));
        $stCred = $pdo->prepare(
            "SELECT r.idcliente, COALESCE(SUM(cfc.valuecredit), 0) AS credito
             FROM ct_createfaturacredit cfc
             LEFT JOIN ct_reserva r ON r.numbervoucher = cfc.numbervoucher
             WHERE r.dateinput >= ? AND r.dateinput <= ?
               AND r.idstatusinvoice = 3
               AND r.idcliente IN ($inC)
             GROUP BY r.idcliente"
        );
        $stCred->execute($credP);
        foreach ($stCred->fetchAll(PDO::FETCH_ASSOC) as $row) {
            if (isset($porCliente[$row['idcliente']])) {
                $porCliente[$row['idcliente']]['credito'] = (float)$row['credito'];
            }
        }
    }

    // Totais gerais
    $gtTotal = $gtCredito = 0.0;
    foreach ($porCliente as $c) {
        $gtTotal   += $c['total'] + $c['add'];
        $gtCredito += $c['credito'];
    }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Relatório Conferência – Resumido</title>
<style>
    @page { size: A4 portrait; margin: 0; }
    @media print { body { margin: 10mm 12mm; } .no-print { display: none; } }
    body  { font-family: Arial, sans-serif; font-size: 10px; color: #222; margin: 12mm 14mm; }
    img   { width: 220px; margin-bottom: 6px; }
    h2    { font-size: 13px; margin: 0 0 2px; color: #1e4770; }
    p.sub { font-size: 9px; color: #555; margin: 0 0 12px; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
    th    { background: #1e4770; color: #fff; font-size: 9px; padding: 6px 8px;
            text-align: left; }
    td    { border-bottom: 1px solid #e5e7eb; padding: 5px 8px; font-size: 9px;
            vertical-align: middle; }
    tr.odd  td { background: #fff; }
    tr.even td { background: #f8fafc; }
    tfoot td   { font-weight: bold; background: #e8f0fb;
                 border-top: 2px solid #1e4770; padding: 5px 8px; }
    .text-right { text-align: right; }
    .neg { color: #dc2626; }
    .btn-print { display: inline-block; margin-bottom: 12px; padding: 8px 20px;
                 background: #1e4770; color: #fff; border: 0; border-radius: 8px;
                 cursor: pointer; font-size: 12px; font-weight: 700; }
</style>
</head>
<body>
<button class="btn-print no-print" onclick="window.print()">Imprimir / Salvar PDF</button>
<img src="../../images/logo.png" alt="Logo">
<h2>Relatório de Conferência – Resumo por Cliente</h2>
<p class="sub">
    Período: <strong><?= dt($datainicio) ?> até <?= dt($datafim) ?></strong>
    &nbsp;|&nbsp; Impresso em: <?= date('d/m/Y H:i') ?>
</p>

<?php if (empty($porCliente)): ?>
<p style="color:#888;padding:20px 0;">Nenhum registro encontrado para os filtros informados.</p>
<?php else: ?>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Cliente</th>
            <th class="text-right">Total Vendido</th>
            <th class="text-right">Total Recebido</th>
            <th class="text-right">Saldo a Pagar</th>
        </tr>
    </thead>
    <tbody>
    <?php $rowNum = 0; foreach ($porCliente as $cli):
        $total    = $cli['total'] + $cli['add'];
        $saldo    = $total - $cli['credito'];
        $rowCss   = ($rowNum % 2 === 0) ? 'even' : 'odd';
        $rowNum++;
        if ($total <= 0) { continue; }
    ?>
    <tr class="<?= $rowCss ?>">
        <td><?= $rowNum ?></td>
        <td><?= esc($cli['fullname']) ?></td>
        <td class="text-right"><?= brl($total) ?></td>
        <td class="text-right"><?= brl($cli['credito']) ?></td>
        <td class="text-right <?= $saldo > 0 ? 'neg' : '' ?>"><?= brl($saldo) ?></td>
    </tr>
    <?php endforeach ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="2">TOTAL GERAL</td>
            <td class="text-right"><?= brl($gtTotal) ?></td>
            <td class="text-right"><?= brl($gtCredito) ?></td>
            <td class="text-right <?= ($gtTotal - $gtCredito) > 0 ? 'neg' : '' ?>">
                <?= brl($gtTotal - $gtCredito) ?>
            </td>
        </tr>
    </tfoot>
</table>

<?php endif ?>

<script>window.print();</script>
</body>
</html>
<?php } ?>
