<?php
ini_set('memory_limit', '256M');
set_time_limit(120);
require_once '../../config.php';
$pdo->exec("set names utf8");

// ── Entrada ───────────────────────────────────────────────────────────────
$idcliente  = (int)($_POST['cliente']          ?? 0);
$datainicio =       $_POST['vencimentoinicial'] ?? date('Y-m-01');
$datafim    =       $_POST['vencimentofinal']   ?? date('Y-m-d');
$tipo       = (int)($_POST['tiporelatorio']     ?? 0);
$empresa    = (int)($_POST['empresa']           ?? 0);
$conta      = (int)($_POST['conta']            ?? 0);

// ── Helpers ───────────────────────────────────────────────────────────────
function esc($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function brl(float $v): string { return 'R$&nbsp;' . number_format($v, 2, ',', '.'); }

function periodoLabel(string $ini, string $fim): string {
    $meses = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho',
              'Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
    $di = date('d', strtotime($ini)) . ' de ' . $meses[(int)date('n', strtotime($ini)) - 1] . ' de ' . date('Y', strtotime($ini));
    $df = date('d', strtotime($fim)) . ' de ' . $meses[(int)date('n', strtotime($fim)) - 1] . ' de ' . date('Y', strtotime($fim));
    return $di === $df ? $di : "$di até $df";
}

// ── Joins base ────────────────────────────────────────────────────────────
$joins = "FROM ct_caixa c
          LEFT JOIN ct_currentaccount cc  ON cc.id  = c.idconta
          LEFT JOIN ct_fornecedor     cli ON cli.id = c.idcliente
          LEFT JOIN ct_empresa        em  ON em.id  = c.idempresa
          LEFT JOIN ct_statusinvoice  s   ON s.id   = c.idstatus";

// ── Filtros dinâmicos ─────────────────────────────────────────────────────
if ($tipo === 0) {
    $where  = ['c.dataabertura >= :inicio', 'c.dataabertura <= :fim'];
    $params = [':inicio' => $datainicio, ':fim' => $datafim];
    if ($empresa   > 0) { $where[] = 'c.idempresa = :empresa'; $params[':empresa'] = $empresa; }
    if ($idcliente > 0) { $where[] = 'c.idcliente = :cliente'; $params[':cliente'] = $idcliente; }
    if ($conta     > 0) { $where[] = 'c.idconta   = :idconta'; $params[':idconta'] = $conta; }
} else {
    $where  = ['c.datevencimento >= :inicio', 'c.datevencimento <= :fim',
               "(c.descricao NOT LIKE '%Pagamento de comiss%' OR c.descricao IS NULL)"];
    $params = [':inicio' => $datainicio, ':fim' => $datafim];
    if ($empresa > 0) { $where[] = 'c.idempresa = :empresa'; $params[':empresa'] = $empresa; }
}
$wSql = ' WHERE ' . implode(' AND ', $where);

// ── Totais via SQL (sem carregar linhas em memória) ───────────────────────
$total = $totalDebito = $totalCredito = 0.0;
$totalRows = 0;

if ($tipo === 0) {
    $aggSql = "SELECT
        COUNT(*)                                                               AS cnt,
        COALESCE(SUM(c.valor), 0)                                              AS total,
        COALESCE(SUM(CASE WHEN c.idtipo = 2 THEN c.valor ELSE 0 END), 0)      AS totalDebito,
        COALESCE(SUM(CASE WHEN c.idstatus = 3 AND c.idtipo = 1 THEN c.valor ELSE 0 END), 0) AS totalCredito
        $joins $wSql";
    $aggStmt = $pdo->prepare($aggSql);
    $aggStmt->execute($params);
    $agg = $aggStmt->fetch(PDO::FETCH_ASSOC);
    $total        = (float)$agg['total'];
    $totalDebito  = (float)$agg['totalDebito'];
    $totalCredito = (float)$agg['totalCredito'];
    $totalRows    = (int)$agg['cnt'];
} else {
    // Para tipo 1 contamos registros simples
    $cntStmt = $pdo->prepare("SELECT COUNT(*) $joins $wSql");
    $cntStmt->execute($params);
    $totalRows = (int)$cntStmt->fetchColumn();
}

// ── Cursor de linhas (fetch streaming — sem fetchAll) ─────────────────────
$selectCols = "SELECT c.datevencimento AS vencimento, c.descricao,
                      cc.name AS conta, cli.fullname AS favorecido,
                      c.idcliente, c.valor, c.idtipo, c.idstatus,
                      s.nameinvoice, c.nome, em.fullname AS empresa";
$stmt = $pdo->prepare("$selectCols $joins $wSql ORDER BY cli.fullname, c.datevencimento");
$stmt->execute($params);
// PDO buffered=false para não duplicar resultado em memória
$stmt->setFetchMode(PDO::FETCH_OBJ);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Fluxo de Caixa — <?= esc(periodoLabel($datainicio, $datafim)) ?></title>
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
:root { --navy: #1e4770; --navy-lt: #2a5f96; --paid: #1a9e5c; --debt: #dc3545; }
body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 13px; background: #f4f6fa; color: #212529; }
.print-bar { background: var(--navy); padding: 10px 24px; display: flex; align-items: center; justify-content: space-between; }
.print-bar-title { color: #fff; font-size: 14px; font-weight: 700; }
.btn-print { background: #fff; color: var(--navy); border: none; border-radius: 6px; padding: 7px 18px; font-size: 13px; font-weight: 700; cursor: pointer; }
.btn-print:hover { opacity: .85; }
.page { max-width: 1100px; margin: 20px auto; background: #fff; border-radius: 12px; box-shadow: 0 2px 18px rgba(0,0,0,.09); overflow: hidden; }
.rpt-header { background: var(--navy); padding: 22px 28px 18px; display: flex; align-items: flex-start; justify-content: space-between; gap: 20px; flex-wrap: wrap; }
.rpt-logo { height: 48px; filter: brightness(0) invert(1); }
.rpt-info { color: #fff; text-align: right; }
.rpt-title { font-size: 18px; font-weight: 800; }
.rpt-period { font-size: 12px; opacity: .85; margin-top: 4px; }
.rpt-printed { font-size: 10px; opacity: .6; margin-top: 6px; }
.kpi-row { display: grid; grid-template-columns: repeat(4, 1fr); border-bottom: 1px solid #e9ecef; }
.kpi-card { padding: 16px 20px; border-right: 1px solid #e9ecef; }
.kpi-card:last-child { border-right: none; }
.kpi-label { font-size: 10px; font-weight: 700; color: #6c757d; text-transform: uppercase; letter-spacing: .05em; }
.kpi-value { font-size: 20px; font-weight: 800; margin-top: 4px; }
.kpi-value.navy  { color: var(--navy); }
.kpi-value.green { color: var(--paid); }
.kpi-value.red   { color: var(--debt); }
.tbl-wrap { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; font-size: 12px; }
thead th { background: var(--navy); color: #fff; font-weight: 600; font-size: 10px; text-transform: uppercase; letter-spacing: .04em; padding: 9px 8px; white-space: nowrap; border: none; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
tbody td { padding: 7px 8px; border-bottom: 1px solid #f0f0f0; vertical-align: middle; }
tbody tr:hover td { background: #f0f6ff; }
.tr-subtotal td { background: #e8f0fb; font-weight: 700; color: var(--navy); -webkit-print-color-adjust: exact; print-color-adjust: exact; }
.tr-total td { background: var(--navy); color: #fff; font-weight: 700; font-size: 13px; padding: 10px 8px; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
.group-header td { background: #f0f6ff; font-weight: 700; color: var(--navy); padding: 8px 10px; font-size: 12px; border-top: 2px solid var(--navy); -webkit-print-color-adjust: exact; print-color-adjust: exact; }
.badge { display: inline-block; padding: 2px 8px; border-radius: 999px; font-size: 10px; font-weight: 700; }
.badge-cred { background: #dcfce7; color: #166534; }
.badge-deb  { background: #fee2e2; color: #991b1b; }
.badge-def  { background: #e2e8f0; color: #475569; }
.val-right { text-align: right; white-space: nowrap; }
.empty-state { padding: 60px 24px; text-align: center; color: #aaa; font-size: 15px; }
@media print {
    body { background: #fff; }
    .print-bar { display: none !important; }
    .page { box-shadow: none; border-radius: 0; margin: 0; max-width: 100%; }
}
@media (max-width: 767px) { .kpi-row { grid-template-columns: 1fr 1fr; } }
</style>
</head>
<body>

<div class="print-bar">
    <span class="print-bar-title">
        &#128438; <?= $tipo === 0 ? 'Fluxo de Caixa' : 'Fluxo de Caixa por Fornecedor' ?>
    </span>
    <button class="btn-print" onclick="window.print()">&#128438; Imprimir / Salvar PDF</button>
</div>

<div class="page">

    <div class="rpt-header">
        <img class="rpt-logo" id="rpt-logo" src="../../images/logo.png" alt="Logo">
        <div class="rpt-info">
            <div class="rpt-title"><?= $tipo === 0 ? 'Fluxo de Caixa' : 'Fluxo de Caixa por Fornecedor' ?></div>
            <div class="rpt-period"><?= esc(periodoLabel($datainicio, $datafim)) ?></div>
            <div class="rpt-printed">Gerado em: <?= date('d/m/Y H:i:s') ?> &mdash; <?= $totalRows ?> registro(s)</div>
        </div>
    </div>

    <?php if ($tipo === 0): ?>
    <div class="kpi-row">
        <div class="kpi-card">
            <div class="kpi-label">Total Geral</div>
            <div class="kpi-value navy"><?= brl($total) ?></div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Total Crédito</div>
            <div class="kpi-value green"><?= brl($totalCredito) ?></div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Total Débito</div>
            <div class="kpi-value red"><?= brl($totalDebito) ?></div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">A Conferir</div>
            <div class="kpi-value"><?= brl($total - ($totalCredito + $totalDebito)) ?></div>
        </div>
    </div>
    <?php endif; ?>

    <div class="tbl-wrap">
    <?php if ($totalRows === 0): ?>
        <div class="empty-state">
            &#128197; Nenhum registro encontrado para os filtros selecionados.
        </div>

    <?php elseif ($tipo === 0): ?>
        <table>
            <thead><tr>
                <th>Vencimento</th><th>Conta</th><th>Tipo</th><th>Nome</th>
                <th>Documento</th><th>Empresa</th><th>Favorecido</th><th>Situação</th>
                <th class="val-right">Valor</th>
            </tr></thead>
            <tbody>
            <?php while ($r = $stmt->fetch()): ?>
            <?php
                $idtipo = (int)$r->idtipo;
                if ($idtipo === 1)     { $tipoLabel = 'CRÉDITO'; $tipoClass = 'badge-cred'; }
                elseif ($idtipo === 2) { $tipoLabel = 'DÉBITO';  $tipoClass = 'badge-deb';  }
                else                  { $tipoLabel = '—';        $tipoClass = 'badge-def';  }
            ?>
            <tr>
                <td><?= date('d/m/Y', strtotime($r->vencimento)) ?></td>
                <td><?= esc($r->conta) ?></td>
                <td><span class="badge <?= $tipoClass ?>"><?= $tipoLabel ?></span></td>
                <td><?= esc($r->nome) ?></td>
                <td><?= esc($r->descricao) ?></td>
                <td><?= esc($r->empresa) ?></td>
                <td><?= esc($r->favorecido) ?></td>
                <td><?= esc($r->nameinvoice) ?></td>
                <td class="val-right"><strong><?= brl((float)$r->valor) ?></strong></td>
            </tr>
            <?php endwhile; ?>
            <tr class="tr-total">
                <td colspan="8">TOTAL GERAL</td>
                <td class="val-right"><?= brl($total) ?></td>
            </tr>
            </tbody>
        </table>

    <?php else: ?>
        <?php
        // Tipo 1: streaming com agrupamento em tempo real — sem guardar tudo em memória
        $grandTotal    = 0.0;
        $groupTotal    = 0.0;
        $groupName     = '';
        $currentClient = null;
        $firstGroup    = true;
        ?>
        <table>
            <thead><tr>
                <th>Vencimento</th><th>Conta</th><th>Documento</th>
                <th>Empresa</th><th>Favorecido</th><th class="val-right">Valor</th>
            </tr></thead>
            <tbody>
            <?php while ($r = $stmt->fetch()):
                $clientKey = $r->idcliente ?? '__sem__';

                if ($currentClient !== $clientKey):
                    // Fecha grupo anterior
                    if ($currentClient !== null): ?>
                    <tr class="tr-subtotal">
                        <td colspan="5" class="val-right">Subtotal — <?= esc($groupName) ?></td>
                        <td class="val-right"><?= brl($groupTotal) ?></td>
                    </tr>
                    <?php endif;
                    $currentClient = $clientKey;
                    $groupName     = $r->favorecido ?? '(sem favorecido)';
                    $groupTotal    = 0.0;
                    ?>
                    <tr class="group-header">
                        <td colspan="6">&#128100; <?= esc($groupName) ?></td>
                    </tr>
                <?php endif;
                $groupTotal  += (float)$r->valor;
                $grandTotal  += (float)$r->valor;
                ?>
                <tr>
                    <td><?= date('d/m/Y', strtotime($r->vencimento)) ?></td>
                    <td><?= esc($r->conta) ?></td>
                    <td><?= esc($r->descricao) ?></td>
                    <td><?= esc($r->empresa) ?></td>
                    <td><?= esc($r->favorecido) ?></td>
                    <td class="val-right"><?= brl((float)$r->valor) ?></td>
                </tr>
            <?php endwhile; ?>
            <?php if ($currentClient !== null): ?>
            <tr class="tr-subtotal">
                <td colspan="5" class="val-right">Subtotal — <?= esc($groupName) ?></td>
                <td class="val-right"><?= brl($groupTotal) ?></td>
            </tr>
            <?php endif; ?>
            <tr class="tr-total">
                <td colspan="5">TOTAL GERAL</td>
                <td class="val-right"><?= brl($grandTotal) ?></td>
            </tr>
            </tbody>
        </table>
    <?php endif; ?>
    </div>

</div>
<script>
var logo = document.getElementById('rpt-logo');
if (logo) { logo.addEventListener('error', function () { this.style.display = 'none'; }); }
</script>
</body>
</html>
