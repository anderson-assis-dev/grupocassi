<?php
ini_set('max_execution_time', 120);
require_once '../../config.php';
require_once __DIR__ . '/../includes/audit.php';

$inicio    = $_POST['inicio']    ?? '';
$fim       = $_POST['fim']       ?? '';
$responsavel = (int)($_POST['responsavel'] ?? 0);
$agencia     = (int)($_POST['cliente']     ?? 0);
$idempresa   = (int)($_POST['idempresa']   ?? 0);
$tipo        = (int)($_POST['tipo']        ?? 1);

$sqlEmpresa = $idempresa > 0 ? " AND r.idempresa = $idempresa " : '';

// ---------------------------------------------------------------------------
// TIPO 1 — Descritivo (por voucher)
// ---------------------------------------------------------------------------
if ($tipo == 1) {

    // 1. Lista de vouchers no período, filtrada por usuário/agência
    $params = [];
    $where  = "cfc.datacredit >= ? AND cfc.datacredit <= ?";
    $params = [$inicio, $fim];

    if ($responsavel > 0) {
        $where .= " AND cfc.idusr = ?";
        $params[] = $responsavel;
    }
    if ($agencia > 0) {
        $where .= " AND r.idcliente = ?";
    }

    if ($agencia > 0) {
        $stVouchers = $pdo->prepare(
            "SELECT cfc.numbervoucher FROM ct_createfaturacredit cfc
             LEFT JOIN ct_reserva r ON r.numbervoucher = cfc.numbervoucher
             WHERE $where $sqlEmpresa
             GROUP BY cfc.numbervoucher"
        );
        $params[] = $agencia;
    } else {
        $stVouchers = $pdo->prepare(
            "SELECT cfc.numbervoucher FROM ct_createfaturacredit cfc
             WHERE $where GROUP BY cfc.numbervoucher"
        );
    }
    $stVouchers->execute($params);
    $vouchers = $stVouchers->fetchAll(PDO::FETCH_COLUMN);

    // 2. Despesas do operador (ct_caixa), se responsável informado
    $registroCaixa = [];
    $totalOut = 0.0;
    if ($responsavel > 0) {
        $stCaixa = $pdo->prepare(
            "SELECT c.*, forne.fullname AS fornecedor, tc.name AS tipo,
                    cc.name AS conta, p.name AS plano, s.nameinvoice AS situacao, em.fullname AS empresa
             FROM ct_caixa c
             LEFT JOIN ct_fornecedor forne   ON forne.id = c.idcliente
             LEFT JOIN ct_tipocaixa tc       ON tc.id   = c.idtipo
             LEFT JOIN ct_currentaccount cc  ON cc.id   = c.idconta
             LEFT JOIN ct_planaccounts p     ON p.id    = c.idplano
             LEFT JOIN ct_statusinvoice s    ON s.id    = c.idstatus
             LEFT JOIN ct_empresa em         ON em.id   = c.idempresa
             WHERE c.datepagamento >= ? AND c.datepagamento <= ?
               AND c.idusr = ? AND c.idtipo = 2"
        );
        $stCaixa->execute([$inicio, $fim, $responsavel]);
        $registroCaixa = $stCaixa->fetchAll(PDO::FETCH_OBJ);
        foreach ($registroCaixa as $r) {
            $totalOut += (float)$r->valor;
        }
    }

    if (empty($vouchers)) {
        ?><!DOCTYPE html><html lang="pt-BR"><head><meta charset="utf-8"><title>Relatório</title>
        <style>body{font-family:Arial,sans-serif;padding:40px;color:#555}</style></head>
        <body>
        <?php if (!empty($registroCaixa)): ?>
            <h3>Nenhum pagamento encontrado no período. Despesas abaixo:</h3>
        <?php else: ?>
            <h3>Não encontramos pagamentos/reservas realizados no período informado.</h3>
        <?php endif ?>
        </body></html>
        <?php exit;
    }

    // 3. Bulk: detalhes das reservas
    $inV = implode(',', array_fill(0, count($vouchers), '?'));
    $stRes = $pdo->prepare(
        "SELECT r.id, r.dateinput, r.numbervoucher, c.namefantazia AS cliente, r.pax,
                s.fullname AS servico, r.qtdpax, r.qtdchild, r.qtdfree,
                si.nameinvoice AS situacao, r.valueservice, r.idstatusinvoice,
                u.firstname, u.lastname, r.data_integracao
         FROM ct_reserva r
         LEFT JOIN ct_cliente c       ON c.id  = r.idcliente
         LEFT JOIN ct_servico s       ON s.id  = r.idservico
         LEFT JOIN ct_usuario u       ON u.id  = r.idresponsavel
         LEFT JOIN ct_statusinvoice si ON si.id = r.idstatusinvoice
         WHERE r.numbervoucher IN ($inV)"
    );
    $stRes->execute($vouchers);
    $reservas = [];
    foreach ($stRes->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $reservas[$row['numbervoucher']] = $row;
    }

    // 4. Bulk: total pago por voucher (no período, filtrado)
    $pagWhere  = "cfc.numbervoucher IN ($inV) AND cfc.datacredit >= ? AND cfc.datacredit <= ?";
    $pagParams = array_merge($vouchers, [$inicio, $fim]);
    if ($responsavel > 0) { $pagWhere .= " AND cfc.idusr = ?"; $pagParams[] = $responsavel; }
    if ($agencia > 0)     { $pagWhere .= " AND r.idcliente = ?"; $pagParams[] = $agencia; }

    $joinRes = $agencia > 0
        ? "LEFT JOIN ct_reserva r ON r.numbervoucher = cfc.numbervoucher"
        : '';

    $stPag = $pdo->prepare(
        "SELECT cfc.numbervoucher, SUM(cfc.valuecredit) AS valor,
                u.firstname, u.lastname, cfc.datacredit AS dia, cc.name AS pagamento
         FROM ct_createfaturacredit cfc
         LEFT JOIN ct_currentaccount cc ON cc.id = cfc.idaccountcurrent
         LEFT JOIN ct_usuario u         ON u.id  = cfc.idusr
         $joinRes
         WHERE $pagWhere
         GROUP BY cfc.numbervoucher, u.firstname, u.lastname, cfc.datacredit, cc.name"
    );
    $stPag->execute($pagParams);
    $pagamentos = [];
    foreach ($stPag->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $pagamentos[$row['numbervoucher']] = $row;
    }

    // 5. Bulk: serviços adicionais
    $reservaIds = array_filter(array_map(fn($v) => $reservas[$v]['id'] ?? null, $vouchers));
    $addRows = [];
    if (!empty($reservaIds)) {
        $inR   = implode(',', array_fill(0, count($reservaIds), '?'));
        $stAdd = $pdo->prepare(
            "SELECT ra.idrecently, ra.dateinput, ra.valueservice, ra.qpax, ra.qchild, ra.qfree,
                    s.fullname
             FROM ct_recentlyadd ra
             LEFT JOIN ct_servico s ON s.id = ra.idservice
             WHERE ra.idrecently IN ($inR)"
        );
        $stAdd->execute(array_values($reservaIds));
        foreach ($stAdd->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $addRows[(int)$row['idrecently']][] = $row;
        }
    }

    // 6. Totais por método de pagamento (1 query no lugar de 7)
    $totParams  = [$inicio, $fim];
    $totWhere   = "datacredit >= ? AND datacredit <= ?";
    if ($responsavel > 0) { $totWhere .= " AND idusr = ?"; $totParams[] = $responsavel; }

    $stTot = $pdo->prepare(
        "SELECT
            COALESCE(SUM(CASE WHEN idaccountcurrent = 36 THEN valuecredit END), 0) AS transferencia,
            COALESCE(SUM(CASE WHEN idaccountcurrent = 24 THEN valuecredit END), 0) AS cartao_credito,
            COALESCE(SUM(CASE WHEN idaccountcurrent = 25 THEN valuecredit END), 0) AS cartao_debito,
            COALESCE(SUM(CASE WHEN idaccountcurrent = 22 THEN valuecredit END), 0) AS paypal,
            COALESCE(SUM(CASE WHEN idaccountcurrent = 18 THEN valuecredit END), 0) AS dinheiro,
            COALESCE(SUM(CASE WHEN idaccountcurrent = 41 THEN valuecredit END), 0) AS linkstone,
            COALESCE(SUM(CASE WHEN idaccountcurrent = 23 THEN valuecredit END), 0) AS panda,
            COALESCE(SUM(CASE WHEN idaccountcurrent = 39 THEN valuecredit END), 0) AS cortesia
         FROM ct_createfaturacredit WHERE $totWhere"
    );
    $stTot->execute($totParams);
    $totais = $stTot->fetch(PDO::FETCH_ASSOC);

    // Calcula totais globais iterando registros
    $totalGeral    = 0.0;
    $totalRecebido = 0.0;
    foreach ($vouchers as $v) {
        $res = $reservas[$v] ?? null;
        if (!$res || date('d/m/Y', strtotime($res['dateinput'])) === '31/12/1969') {
            continue;
        }
        $totalGeral    += ($res['valueservice'] * $res['qtdpax']) + (($res['valueservice'] / 2) * $res['qtdchild']);
        $totalRecebido += (float)($pagamentos[$v]['valor'] ?? 0);
        foreach ($addRows[(int)$res['id']] ?? [] as $add) {
            $totalGeral += ($add['valueservice'] * $add['qpax']) + (($add['valueservice'] / 2) * $add['qchild']);
        }
    }

    logAudit($pdo, 'RELATORIO-PAGAMENTO',
        "Rel. por pagamento gerado: $inicio a $fim | " . count($vouchers) . " vouchers"
    );
    ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Relatório Conferência – Pagamento</title>
    <style>
        @page { size: A4 landscape; margin: 0; }
        @media print { body { margin: 8mm 10mm; } .no-print { display: none; } }
        body  { font-family: Arial, sans-serif; font-size: 10px; color: #222; margin: 12mm 14mm; }
        img   { width: 240px; margin-bottom: 6px; }
        h2    { font-size: 12px; margin: 0 0 2px; }
        p.periodo { font-size: 10px; color: #555; margin: 0 0 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th  { background: #1e4770; color: #fff; font-size: 9px; padding: 5px 4px; }
        td  { border-bottom: 1px solid #e5e7eb; padding: 4px; font-size: 9px; }
        tr.add-row td { background: #f8fafc; color: #555; }
        tfoot td { font-weight: bold; background: #f1f5f9; border-top: 2px solid #1e4770; padding: 6px 4px; }
        h4  { font-size: 11px; margin: 12px 0 4px; color: #1e4770; }
        .btn-print { display: inline-block; margin: 14px 0; padding: 8px 20px; background: #1e4770;
                     color: #fff; border: 0; border-radius: 8px; cursor: pointer; font-weight: 700; }
    </style>
</head>
<body>
<button class="btn-print no-print" onclick="window.print()">Imprimir / Salvar PDF</button>
<img src="../../images/logo.png" alt="Logo">
<h2>Relatório de Conferência por Data de Pagamento</h2>
<p class="periodo">Período: <?= date("d/m/Y", strtotime($inicio)) ?> até <?= date("d/m/Y", strtotime($fim)) ?> &nbsp;|&nbsp; Impresso em: <?= date("d/m/Y H:i") ?></p>

<table>
    <thead>
        <tr>
            <th>Embarque</th><th>Voucher</th><th>Agência</th><th>Pax</th><th>P|C|F</th>
            <th>Vendedor</th><th>Serviço</th><th>Valor Unit.</th><th>Valor Total</th>
            <th>Recebido por</th><th>Pago em</th><th>Valor Recebido</th><th>Situação</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($vouchers as $v):
        $res = $reservas[$v] ?? null;
        if (!$res || date('d/m/Y', strtotime($res['dateinput'])) === '31/12/1969') continue;
        $pag   = $pagamentos[$v] ?? [];
        $adds  = $addRows[(int)$res['id']] ?? [];
        $total = ($res['valueservice'] * $res['qtdpax']) + (($res['valueservice'] / 2) * $res['qtdchild']);
    ?>
    <tr>
        <td><?= date("d/m/Y", strtotime($res['dateinput'])) ?></td>
        <td><?= htmlspecialchars($v) ?></td>
        <td><?= htmlspecialchars($res['cliente']) ?></td>
        <td><?= htmlspecialchars($res['pax']) ?></td>
        <td><?= (int)$res['qtdpax'] ?> | <?= (int)$res['qtdchild'] ?> | <?= (int)$res['qtdfree'] ?></td>
        <td><?= htmlspecialchars(strtoupper($res['firstname'] . ' ' . $res['lastname'])) ?></td>
        <td><?= htmlspecialchars($res['servico']) ?></td>
        <td>R$ <?= number_format((float)$res['valueservice'], 2, ',', '.') ?></td>
        <td>R$ <?= number_format($total, 2, ',', '.') ?></td>
        <td><?= htmlspecialchars(strtoupper(($pag['firstname'] ?? '') . ' ' . ($pag['lastname'] ?? ''))) ?></td>
        <td><?= !empty($pag['dia']) ? date("d/m/Y", strtotime($pag['dia'])) : '-' ?></td>
        <td>R$ <?= number_format((float)($pag['valor'] ?? 0), 2, ',', '.') ?></td>
        <td><?= htmlspecialchars($res['situacao']) ?></td>
    </tr>
    <?php foreach ($adds as $add):
        $addTotal = ($add['valueservice'] * $add['qpax']) + (($add['valueservice'] / 2) * $add['qchild']); ?>
    <tr class="add-row">
        <td><?= date("d/m/Y", strtotime($add['dateinput'])) ?></td>
        <td><?= htmlspecialchars($v) ?></td>
        <td><?= htmlspecialchars($res['cliente']) ?></td>
        <td><?= htmlspecialchars($res['pax']) ?></td>
        <td><?= (int)$add['qpax'] ?> | <?= (int)$add['qchild'] ?> | <?= (int)$add['qfree'] ?></td>
        <td><?= htmlspecialchars(strtoupper($res['firstname'] . ' ' . $res['lastname'])) ?></td>
        <td><?= htmlspecialchars($add['fullname']) ?></td>
        <td>R$ <?= number_format((float)$add['valueservice'], 2, ',', '.') ?></td>
        <td>R$ <?= number_format($addTotal, 2, ',', '.') ?></td>
        <td>-</td><td>-</td><td>-</td>
        <td><?= htmlspecialchars($res['situacao']) ?></td>
    </tr>
    <?php endforeach ?>
    <?php endforeach ?>
    </tbody>
</table>

<?php if (!empty($registroCaixa)): ?>
<h4>Despesas</h4>
<table>
    <thead>
        <tr>
            <th>Data Venci.</th><th>Data Pag.</th><th>Nome</th><th>Descrição</th>
            <th>Favorecido</th><th>Tipo</th><th>Forma Pagamento</th><th>Plano</th>
            <th>Valor</th><th>Situação</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($registroCaixa as $r): ?>
    <tr>
        <td><?= date("d/m/Y", strtotime($r->datevencimento)) ?></td>
        <td><?= date("d/m/Y", strtotime($r->datepagamento)) ?></td>
        <td><?= htmlspecialchars($r->nome) ?></td>
        <td><?= htmlspecialchars($r->descricao) ?></td>
        <td><?= htmlspecialchars($r->fornecedor) ?></td>
        <td><?= htmlspecialchars($r->tipo) ?></td>
        <td><?= htmlspecialchars($r->conta) ?></td>
        <td><?= htmlspecialchars($r->plano) ?></td>
        <td>R$ <?= number_format((float)$r->valor, 2, ',', '.') ?></td>
        <td><?= htmlspecialchars($r->situacao) ?></td>
    </tr>
    <?php endforeach ?>
    </tbody>
</table>
<?php endif ?>

<h4>Totais</h4>
<table>
    <thead>
        <tr>
            <th>Total Vendido</th><th>Total Recebido</th><th>Transferência</th>
            <th>Cartão Crédito</th><th>Cartão Débito</th><th>Pag Seguro</th>
            <th>Dinheiro</th><th>Panda</th><th>Cortesia</th><th>Link Stone</th>
            <th>Despesas</th><th>Saldo</th><th>Dinheiro Líquido</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>R$ <?= number_format($totalGeral,              2, ',', '.') ?></td>
            <td>R$ <?= number_format($totalRecebido,           2, ',', '.') ?></td>
            <td>R$ <?= number_format((float)$totais['transferencia'],  2, ',', '.') ?></td>
            <td>R$ <?= number_format((float)$totais['cartao_credito'], 2, ',', '.') ?></td>
            <td>R$ <?= number_format((float)$totais['cartao_debito'],  2, ',', '.') ?></td>
            <td>R$ <?= number_format((float)$totais['paypal'],         2, ',', '.') ?></td>
            <td>R$ <?= number_format((float)$totais['dinheiro'],       2, ',', '.') ?></td>
            <td>R$ <?= number_format((float)$totais['panda'],          2, ',', '.') ?></td>
            <td>R$ <?= number_format((float)$totais['cortesia'],       2, ',', '.') ?></td>
            <td>R$ <?= number_format((float)$totais['linkstone'],      2, ',', '.') ?></td>
            <td>R$ <?= number_format($totalOut,                2, ',', '.') ?></td>
            <td>R$ <?= number_format($totalRecebido - $totalOut, 2, ',', '.') ?></td>
            <td>R$ <?= number_format((float)$totais['dinheiro'] - $totalOut, 2, ',', '.') ?></td>
        </tr>
    </tbody>
</table>
<script>window.print();</script>
</body>
</html>

<?php
// ---------------------------------------------------------------------------
// TIPO 2 — Resumido por vendedor
// ---------------------------------------------------------------------------
} else {

    // 1 query: todos os vendedores + totais por método de pagamento
    $stVendedores = $pdo->prepare(
        "SELECT cfc.idusr, u.firstname, u.lastname,
                COALESCE(SUM(cfc.valuecredit), 0) AS total,
                COALESCE(SUM(CASE WHEN idaccountcurrent = 36 THEN valuecredit END), 0) AS transferencia,
                COALESCE(SUM(CASE WHEN idaccountcurrent = 24 THEN valuecredit END), 0) AS cartao_credito,
                COALESCE(SUM(CASE WHEN idaccountcurrent = 25 THEN valuecredit END), 0) AS cartao_debito,
                COALESCE(SUM(CASE WHEN idaccountcurrent = 22 THEN valuecredit END), 0) AS paypal,
                COALESCE(SUM(CASE WHEN idaccountcurrent = 18 THEN valuecredit END), 0) AS dinheiro,
                COALESCE(SUM(CASE WHEN idaccountcurrent = 41 THEN valuecredit END), 0) AS linkstone,
                COALESCE(SUM(CASE WHEN idaccountcurrent = 23 THEN valuecredit END), 0) AS panda
         FROM ct_createfaturacredit cfc
         LEFT JOIN ct_usuario u ON u.id = cfc.idusr
         WHERE cfc.datacredit >= ? AND cfc.datacredit <= ?
         GROUP BY cfc.idusr, u.firstname, u.lastname
         ORDER BY u.firstname"
    );
    $stVendedores->execute([$inicio, $fim]);
    $vendedores = $stVendedores->fetchAll(PDO::FETCH_ASSOC);

    // 1 query: despesas por vendedor
    $stDesp = $pdo->prepare(
        "SELECT c.idusr, u.firstname, u.lastname, COALESCE(SUM(c.valor), 0) AS tot
         FROM ct_caixa c
         LEFT JOIN ct_usuario u ON u.id = c.idusr
         WHERE c.datepagamento >= ? AND c.datepagamento <= ? AND c.idtipo = 2
         GROUP BY c.idusr, u.firstname, u.lastname"
    );
    $stDesp->execute([$inicio, $fim]);
    $despesasPorUsuario = [];
    $totalDespesas = 0.0;
    foreach ($stDesp->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $despesasPorUsuario[$row['idusr']] = $row;
        $totalDespesas += (float)$row['tot'];
    }

    // Totais gerais
    $totDia = array_sum(array_column($vendedores, 'total'));

    if ($agencia > 0) {
        // Resumo por agência
        $stAg = $pdo->prepare(
            "SELECT cf.numbervoucher, cf.valuecredit,
                    c.fullname, u.firstname, u.lastname
             FROM ct_createfaturacredit cf
             LEFT JOIN ct_reserva r ON r.numbervoucher = cf.numbervoucher
             LEFT JOIN ct_cliente c ON c.id = r.idcliente
             LEFT JOIN ct_usuario u ON u.id = r.idresponsavel
             WHERE cf.datacredit >= ? AND cf.datacredit <= ?
               AND r.idstatus <> 2 AND r.idcliente = ? $sqlEmpresa"
        );
        $stAg->execute([$inicio, $fim, $agencia]);
        $reservasAgencia = $stAg->fetchAll(PDO::FETCH_OBJ);
        ?>
<!DOCTYPE html>
<html lang="pt-BR"><head><meta charset="utf-8"><title>Relatório Resumido – Agência</title>
<style>
    @page { size: A4 landscape; margin: 0; }
    @media print { body { margin: 8mm 10mm; } .no-print { display: none; } }
    body { font-family: Arial, sans-serif; font-size: 10px; margin: 12mm 14mm; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
    th { background: #1e4770; color: #fff; padding: 5px; font-size: 9px; }
    td { border-bottom: 1px solid #e5e7eb; padding: 4px; font-size: 9px; }
    tfoot td { font-weight: bold; background: #f1f5f9; border-top: 2px solid #1e4770; }
    .btn-print { display: inline-block; margin: 12px 0; padding: 8px 20px; background: #1e4770;
                 color: #fff; border: 0; border-radius: 8px; cursor: pointer; font-weight: 700; }
</style></head>
<body>
<button class="btn-print no-print" onclick="window.print()">Imprimir / Salvar PDF</button>
<img src="../../images/logo.png" alt="Logo" style="width:240px;margin-bottom:6px"><br>
<strong>Relatório Resumido por Agência — <?= date("d/m/Y", strtotime($inicio)) ?> a <?= date("d/m/Y", strtotime($fim)) ?></strong>
<p style="font-size:9px;color:#555">Impresso em: <?= date("d/m/Y H:i") ?></p>
<table>
    <thead><tr><th>Agência</th><th>Operador</th><th>Voucher</th><th>Subtotal</th></tr></thead>
    <tbody>
    <?php $totalAgencia = 0.0; foreach ($reservasAgencia as $item): $totalAgencia += (float)$item->valuecredit; ?>
    <tr>
        <td><?= htmlspecialchars($item->fullname) ?></td>
        <td><?= htmlspecialchars(strtoupper($item->firstname . ' ' . $item->lastname)) ?></td>
        <td><?= htmlspecialchars($item->numbervoucher) ?></td>
        <td>R$ <?= number_format((float)$item->valuecredit, 2, ',', '.') ?></td>
    </tr>
    <?php endforeach ?>
    </tbody>
    <tfoot><tr><td colspan="3">Total</td><td>R$ <?= number_format($totalAgencia, 2, ',', '.') ?></td></tr></tfoot>
</table>
<script>window.print();</script>
</body></html>
        <?php
    } else {
        // Resumo por vendedor
        ?>
<!DOCTYPE html>
<html lang="pt-BR"><head><meta charset="utf-8"><title>Relatório Resumido – Vendedores</title>
<style>
    @page { size: A4 landscape; margin: 0; }
    @media print { body { margin: 8mm 10mm; } .no-print { display: none; } }
    body { font-family: Arial, sans-serif; font-size: 10px; margin: 12mm 14mm; }
    img  { width: 240px; margin-bottom: 6px; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
    th { background: #1e4770; color: #fff; padding: 5px; font-size: 9px; }
    td { border-bottom: 1px solid #e5e7eb; padding: 4px; font-size: 9px; }
    tfoot td { font-weight: bold; background: #f1f5f9; border-top: 2px solid #1e4770; }
    h4  { font-size: 11px; color: #1e4770; margin: 10px 0 4px; }
    .btn-print { display: inline-block; margin: 12px 0; padding: 8px 20px; background: #1e4770;
                 color: #fff; border: 0; border-radius: 8px; cursor: pointer; font-weight: 700; }
</style></head>
<body>
<button class="btn-print no-print" onclick="window.print()">Imprimir / Salvar PDF</button>
<img src="../../images/logo.png" alt="Logo">
<strong>Relatório Resumido por Vendedor — <?= date("d/m/Y", strtotime($inicio)) ?> a <?= date("d/m/Y", strtotime($fim)) ?></strong>
<p style="font-size:9px;color:#555">Impresso em: <?= date("d/m/Y H:i") ?></p>

<h4>Recebimentos por Operador</h4>
<table>
    <thead>
        <tr>
            <th>Operador</th><th>Total</th><th>Transferência</th><th>Cartão Créd.</th>
            <th>Cartão Déb.</th><th>Pag Seguro</th><th>Dinheiro</th><th>Panda</th><th>Link Stone</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($vendedores as $v): ?>
    <tr>
        <td><?= htmlspecialchars(strtoupper($v['firstname'] . ' ' . $v['lastname'])) ?></td>
        <td>R$ <?= number_format((float)$v['total'],        2, ',', '.') ?></td>
        <td>R$ <?= number_format((float)$v['transferencia'],2, ',', '.') ?></td>
        <td>R$ <?= number_format((float)$v['cartao_credito'],2,',', '.') ?></td>
        <td>R$ <?= number_format((float)$v['cartao_debito'], 2,',', '.') ?></td>
        <td>R$ <?= number_format((float)$v['paypal'],        2,',', '.') ?></td>
        <td>R$ <?= number_format((float)$v['dinheiro'],      2,',', '.') ?></td>
        <td>R$ <?= number_format((float)$v['panda'],         2,',', '.') ?></td>
        <td>R$ <?= number_format((float)$v['linkstone'],     2,',', '.') ?></td>
    </tr>
    <?php endforeach ?>
    </tbody>
    <tfoot>
        <tr>
            <td>TOTAL</td>
            <td>R$ <?= number_format($totDia, 2, ',', '.') ?></td>
            <td>R$ <?= number_format((float)array_sum(array_column($vendedores,'transferencia')),  2,',','.') ?></td>
            <td>R$ <?= number_format((float)array_sum(array_column($vendedores,'cartao_credito')), 2,',','.') ?></td>
            <td>R$ <?= number_format((float)array_sum(array_column($vendedores,'cartao_debito')),  2,',','.') ?></td>
            <td>R$ <?= number_format((float)array_sum(array_column($vendedores,'paypal')),         2,',','.') ?></td>
            <td>R$ <?= number_format((float)array_sum(array_column($vendedores,'dinheiro')),       2,',','.') ?></td>
            <td>R$ <?= number_format((float)array_sum(array_column($vendedores,'panda')),          2,',','.') ?></td>
            <td>R$ <?= number_format((float)array_sum(array_column($vendedores,'linkstone')),      2,',','.') ?></td>
        </tr>
    </tfoot>
</table>

<?php if (!empty($despesasPorUsuario)): ?>
<h4>Despesas por Operador</h4>
<table>
    <thead><tr><th>Operador</th><th>Despesas</th></tr></thead>
    <tbody>
    <?php foreach ($despesasPorUsuario as $d): ?>
    <tr>
        <td><?= htmlspecialchars(strtoupper($d['firstname'] . ' ' . $d['lastname'])) ?></td>
        <td>R$ <?= number_format((float)$d['tot'], 2, ',', '.') ?></td>
    </tr>
    <?php endforeach ?>
    </tbody>
</table>
<?php endif ?>

<table>
    <thead>
        <tr><th>Total Recebido</th><th>Total Despesas</th><th>Saldo</th></tr>
    </thead>
    <tbody>
        <tr>
            <td>R$ <?= number_format($totDia,             2, ',', '.') ?></td>
            <td>R$ <?= number_format($totalDespesas,       2, ',', '.') ?></td>
            <td>R$ <?= number_format($totDia - $totalDespesas, 2, ',', '.') ?></td>
        </tr>
    </tbody>
</table>
<script>window.print();</script>
</body></html>
        <?php
    }
}
?>
