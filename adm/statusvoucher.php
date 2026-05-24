<?php
require_once('header.php');
require_once __DIR__ . '/includes/ref_cache.php';
require_once __DIR__ . '/includes/audit.php';
require_once __DIR__ . '/includes/flash.php';
require_once __DIR__ . '/includes/pax_helpers.php';

// ── legacy cancel helper ──────────────────────────────────────────────────────
function cacelarVoucher(string $voucher): void
{
    require_once('class/Cliente.php');
    $cliente = new Cliente();
    require_once('class/Reserva.php');
    $reserva = new Reserva();
    $reserva->setNumeroVoucher($voucher);
    $dados = $reserva->buscarReservaPorVoucher();
    $cliente->setIdCliente($dados[0]['cassiturismo_cliente_idcliente']);
    $cliente->atualizarClientePorRevendedorAtravesDoSistema();
}

// ── resolve invoice status with paid-vs-total guard ──────────────────────────
function resolveStatusInvoice(PDO $pdo, string $voucher, int $solicitado): int
{
    $st = $pdo->prepare(
        'SELECT r.valueservice, r.qtdpax, r.qtdchild,
                COALESCE((SELECT SUM(ra.valueservice * ra.qpax + ra.valueservice / 2 * ra.qchild)
                           FROM ct_recentlyadd ra WHERE ra.idrecently = r.id), 0) AS totaladd,
                COALESCE((SELECT SUM(valuecredit) FROM ct_createfaturacredit
                           WHERE numbervoucher = :v2), 0) AS totalpago
         FROM ct_reserva r WHERE r.numbervoucher = :v'
    );
    $st->execute([':v' => $voucher, ':v2' => $voucher]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
    if (!$row) { return $solicitado; }

    $totalServico = ($row['valueservice'] * $row['qtdpax'])
                  + ($row['valueservice'] / 2 * $row['qtdchild'])
                  + (float) $row['totaladd'];
    $totalPago    = (float) $row['totalpago'];

    if ($totalPago >= $totalServico) { return $solicitado; }
    return ($solicitado === 2) ? 2 : 4;
}

// ── apply invoice + reservation status, audit ────────────────────────────────
function aplicarStatus(PDO $pdo, string $voucher, int $status): void
{
    $pdo->prepare('UPDATE ct_reserva SET idstatusinvoice = :s WHERE numbervoucher = :v')
        ->execute([':s' => $status, ':v' => $voucher]);

    $idstatusMap = [2 => 2, 3 => 1, 4 => 3, 8 => 4];
    $descMap     = [2 => 'CANCELADO', 3 => 'PAGO', 4 => 'PARCIAL', 8 => 'REEMBOLSADO'];

    if (isset($idstatusMap[$status])) {
        $pdo->prepare('UPDATE ct_reserva SET idstatus = :s WHERE numbervoucher = :v')
            ->execute([':s' => $idstatusMap[$status], ':v' => $voucher]);
        logAudit($pdo, $voucher, 'Status Atualizado Para ' . $descMap[$status]);
    }

    if ($status === 2 || $status === 13) {
        cacelarVoucher($voucher);
    }
}

// ── POST: busca voucher ───────────────────────────────────────────────────────
if (isset($_POST['numberVoucher'])) {
    header('location: statusvoucher?v=' . urlencode(trim($_POST['numberVoucher'])));
    exit;
}

// ── POST: cadastrar fatura ────────────────────────────────────────────────────
if (isset($_POST['salvar'])) {
    $voucher         = trim($_POST['voucher']);
    $statusEscolhido = resolveStatusInvoice($pdo, $voucher, (int)$_POST['statusescolhido']);
    aplicarStatus($pdo, $voucher, $statusEscolhido);

    $stIdCli = $pdo->prepare('SELECT idcliente FROM ct_reserva WHERE numbervoucher = :v');
    $stIdCli->execute([':v' => $voucher]);
    $idcliente = (int)($stIdCli->fetchColumn() ?? 0);

    $pdo->prepare(
        'INSERT INTO ct_createfatura (numbervoucher, datematurity, datepayment, numberadd, obervacao, idcurrentaccount, idcliente)
         VALUES (:voucher, :venci, :pag, :num, :obs, :conta, :cliente)'
    )->execute([
        ':voucher'  => $voucher,
        ':venci'    => $_POST['datavencimento'],
        ':pag'      => $_POST['datapagamento'],
        ':num'      => $_POST['numeracao'],
        ':obs'      => '.',
        ':conta'    => $_POST['ccfp'],
        ':cliente'  => $idcliente,
    ]);
    logAudit($pdo, $voucher, 'Fatura Cadastrada ' . $_POST['numeracao']);

    $cx = $pdo->prepare('SELECT id FROM ct_caixa WHERE nome LIKE :n LIMIT 1');
    $cx->execute([':n' => '%' . $voucher]);
    $cxId = $cx->fetchColumn();
    if ($cxId) {
        $pdo->prepare('UPDATE ct_caixa SET idstatus = :s, descricao = :d WHERE id = :id')
            ->execute([':s' => $statusEscolhido, ':d' => $_POST['numeracao'], ':id' => $cxId]);
    }

    setFlash('success', 'Fatura cadastrada para o voucher ' . $voucher);
    header('location: statusvoucher?v=' . urlencode($voucher));
    exit;
}

// ── POST: atualizar fatura ────────────────────────────────────────────────────
if (isset($_POST['atualizar'])) {
    $voucher         = trim($_POST['voucher']);
    $statusEscolhido = resolveStatusInvoice($pdo, $voucher, (int)$_POST['novostatus']);
    aplicarStatus($pdo, $voucher, $statusEscolhido);

    $pdo->prepare(
        'UPDATE ct_createfatura SET datematurity = :venci, datepayment = :pag, numberadd = :num, idcurrentaccount = :conta WHERE id = :id'
    )->execute([
        ':venci' => $_POST['datavencimento'],
        ':pag'   => $_POST['datapagamento'],
        ':num'   => $_POST['numeracao'],
        ':conta' => $_POST['formapagamento'],
        ':id'    => (int)$_POST['idfatura'],
    ]);
    logAudit($pdo, $voucher, 'Fatura Atualizada ' . $_POST['numeracao']);

    setFlash('success', 'Fatura atualizada para o voucher ' . $voucher);
    header('location: statusvoucher?v=' . urlencode($voucher));
    exit;
}

// ── page data ─────────────────────────────────────────────────────────────────
$voucher     = trim($_GET['v'] ?? '');
$dadosGerais = null;
$pagamentos  = [];
$faturas     = [];
$totalServico = 0.0;
$totalPago    = 0.0;

if ($voucher !== '') {
    $stRes = $pdo->prepare(
        'SELECT r.*, s.fullname AS servico, c.namefantazia AS cliente,
                u.firstname, u.lastname, si.nameinvoice AS statuu
         FROM ct_reserva r
         LEFT JOIN ct_servico s        ON s.id  = r.idservico
         LEFT JOIN ct_cliente c        ON c.id  = r.idcliente
         LEFT JOIN ct_usuario u        ON u.id  = r.idresponsavel
         LEFT JOIN ct_statusinvoice si ON si.id = r.idstatusinvoice
         WHERE r.numbervoucher = :v'
    );
    $stRes->execute([':v' => $voucher]);
    $dadosGerais = $stRes->fetch(PDO::FETCH_ASSOC);

    if ($dadosGerais) {
        $totalServico = ($dadosGerais['valueservice'] * $dadosGerais['qtdpax'])
                      + ($dadosGerais['valueservice'] / 2 * $dadosGerais['qtdchild']);

        $stAdd = $pdo->prepare(
            'SELECT COALESCE(SUM(valueservice * qpax + valueservice / 2 * qchild), 0) AS tot
             FROM ct_recentlyadd WHERE idrecently = :id'
        );
        $stAdd->execute([':id' => $dadosGerais['id']]);
        $totalServico += (float) $stAdd->fetchColumn();

        $stPag = $pdo->prepare(
            'SELECT cfc.datacredit, cfc.valuecredit, cc.name AS forma
             FROM ct_createfaturacredit cfc
             LEFT JOIN ct_currentaccount cc ON cc.id = cfc.idaccountcurrent
             WHERE cfc.numbervoucher = :v ORDER BY cfc.datacredit'
        );
        $stPag->execute([':v' => $voucher]);
        $pagamentos = $stPag->fetchAll(PDO::FETCH_ASSOC);
        $totalPago  = (float) array_sum(array_column($pagamentos, 'valuecredit'));

        $stFat = $pdo->prepare(
            'SELECT f.id, f.datematurity, f.datepayment, f.numberadd, cc.name AS conta, f.idcurrentaccount
             FROM ct_createfatura f
             LEFT JOIN ct_currentaccount cc ON cc.id = f.idcurrentaccount
             WHERE f.numbervoucher = :v ORDER BY f.id DESC'
        );
        $stFat->execute([':v' => $voucher]);
        $faturas = $stFat->fetchAll(PDO::FETCH_ASSOC);
    }
}

$statusList = refStatusInvoice($pdo);
$contas     = $pdo->query('SELECT id, name FROM ct_currentaccount ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
$flash      = getFlash();
?>
<style>
:root { --navy: #1e4770; --navy-lt: #2a5f96; }
.map-wrapper { padding: 20px 20px 80px; }
.bc-bar { padding: 0 0 16px; font-size: 13px; color: #6c757d; }
.bc-bar a { color: var(--navy); font-weight: 600; text-decoration: none; }
.bc-bar a:hover { text-decoration: underline; }
.bc-bar .sep { margin: 0 6px; color: #ccc; }

/* Cards */
.sv-card { background: #fff; border-radius: 12px; box-shadow: 0 2px 14px rgba(0,0,0,.07); overflow: hidden; margin-bottom: 20px; }
.sv-heading { background: linear-gradient(135deg, var(--navy), var(--navy-lt)); color: #fff; padding: 18px 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; }
.sv-heading-left h3 { color: #fff; font-size: 17px; font-weight: 800; margin: 0; }
.sv-heading-left small { color: rgba(255,255,255,.78); font-size: 12px; display: block; margin-top: 2px; }
.sv-heading-badge { background: rgba(255,255,255,.18); border: 1px solid rgba(255,255,255,.3); color: #fff; border-radius: 20px; padding: 4px 14px; font-size: 12px; font-weight: 700; white-space: nowrap; }
.sv-heading-badge.badge-pago     { background: rgba(22,163,74,.3);  border-color: rgba(22,163,74,.5); }
.sv-heading-badge.badge-parcial  { background: rgba(234,179,8,.3);  border-color: rgba(234,179,8,.5); }
.sv-heading-badge.badge-cancelado{ background: rgba(220,38,38,.3);  border-color: rgba(220,38,38,.5); }
.sv-body { padding: 22px 24px; }

/* Search */
.search-card { background: #fff; border-radius: 12px; box-shadow: 0 2px 14px rgba(0,0,0,.07); padding: 22px 24px; margin-bottom: 20px; }
.search-card label { font-size: 11px; font-weight: 700; color: #6c757d; text-transform: uppercase; letter-spacing: .05em; display: block; margin-bottom: 6px; }
.search-card .form-control { border: 1.5px solid #dee2e6; border-radius: 8px 0 0 8px; font-size: 14px; height: 42px; }
.search-card .form-control:focus { border-color: var(--navy); box-shadow: 0 0 0 3px rgba(30,71,112,.12); outline: none; }
.btn-search { background: var(--navy); color: #fff; border: none; border-radius: 0 8px 8px 0; padding: 0 22px; height: 42px; font-size: 14px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 7px; transition: background .2s; white-space: nowrap; }
.btn-search:hover { background: var(--navy-lt); }

/* Info grid */
.sv-info-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 12px; margin-bottom: 20px; }
.sv-info-item { background: #f8fafc; border-radius: 8px; padding: 10px 14px; border: 1px solid #e9ecef; }
.sv-info-item .lbl { font-size: 10px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 3px; }
.sv-info-item .val { font-size: 13px; font-weight: 600; color: #1e293b; }

/* KPI boxes */
.sv-kpis { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin-bottom: 24px; }
.sv-kpi  { border-radius: 10px; padding: 16px 18px; }
.sv-kpi.blue   { background: #eff6ff; border: 1px solid #bfdbfe; }
.sv-kpi.green  { background: #f0fdf4; border: 1px solid #bbf7d0; }
.sv-kpi.yellow { background: #fefce8; border: 1px solid #fde68a; }
.sv-kpi.red    { background: #fef2f2; border: 1px solid #fecaca; }
.sv-kpi .kpi-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #64748b; margin-bottom: 4px; display: flex; align-items: center; gap: 5px; }
.sv-kpi .kpi-value { font-size: 20px; font-weight: 800; color: #1e293b; }

/* Section titles */
.sv-section-title { font-size: 11px; font-weight: 700; color: var(--navy); text-transform: uppercase; letter-spacing: .07em; border-bottom: 2px solid #e9ecef; padding-bottom: 8px; margin: 24px 0 14px; display: flex; align-items: center; gap: 6px; }

/* Tables */
.sv-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.sv-table thead th { background: var(--navy); color: #fff; font-size: 11px; text-transform: uppercase; letter-spacing: .04em; padding: 9px 12px; font-weight: 600; border: none; }
.sv-table tbody td { padding: 9px 12px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
.sv-table tbody tr:last-child td { border-bottom: 0; }
.sv-table tbody tr:hover td { background: #f8fafc; }

/* Form */
.sv-form-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(170px, 1fr)); gap: 14px; margin-bottom: 18px; }
.sv-form-grid label { font-size: 11px; font-weight: 700; color: #6c757d; text-transform: uppercase; letter-spacing: .05em; display: block; margin-bottom: 5px; }
.sv-form-grid .form-control { border: 1.5px solid #dee2e6; border-radius: 8px; font-size: 13px; height: 38px; transition: border-color .2s; }
.sv-form-grid .form-control:focus { border-color: var(--navy); box-shadow: 0 0 0 3px rgba(30,71,112,.12); outline: none; }
select.form-control { height: 38px; }

/* Buttons */
.btn-sv-primary { background: var(--navy); color: #fff; border: none; border-radius: 8px; padding: 10px 26px; font-size: 14px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 7px; transition: background .2s; }
.btn-sv-primary:hover { background: var(--navy-lt); }
.btn-sv-edit { background: transparent; color: var(--navy); border: 1.5px solid var(--navy); border-radius: 6px; padding: 4px 12px; font-size: 12px; font-weight: 600; cursor: pointer; transition: all .15s; }
.btn-sv-edit:hover { background: var(--navy); color: #fff; }

/* Modal */
.modal-header { background: linear-gradient(135deg, var(--navy), var(--navy-lt)); }
.modal-header .modal-title { color: #fff; font-size: 15px; font-weight: 700; }
.modal-header .close { color: #fff; opacity: .8; text-shadow: none; }
.modal-header .close:hover { opacity: 1; }
.modal-body { padding: 22px 24px; }

@media (max-width: 767px) {
    .map-wrapper { padding: 14px 12px 60px; }
    .sv-kpis { grid-template-columns: 1fr 1fr; }
    .sv-info-grid { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 480px) {
    .sv-kpis { grid-template-columns: 1fr; }
    .sv-info-grid { grid-template-columns: 1fr; }
}
</style>

<div class="page-content--bgf7">
<div class="map-wrapper" style="max-width:980px;margin:0 auto;">

    <div class="bc-bar">
        <a href="index"><i class="fas fa-home"></i> Home</a>
        <span class="sep">/</span>
        <span>Financeiro: Dar Baixa</span>
    </div>

    <?php if ($flash): ?>
    <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show mb-3" role="alert" style="border-radius:10px">
        <?= $flash['msg'] ?>
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
    <?php endif; ?>

    <!-- Search card -->
    <div class="search-card">
        <form method="post" style="max-width:400px">
            <label for="numberVoucher"><i class="fas fa-search" style="margin-right:4px"></i> Nº do Voucher</label>
            <div class="input-group">
                <input type="text" name="numberVoucher" id="numberVoucher" class="form-control"
                       value="<?= htmlspecialchars($voucher) ?>" placeholder="ex: 12345" autofocus>
                <div class="input-group-append">
                    <button class="btn-search" type="submit">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                </div>
            </div>
        </form>
    </div>

    <?php if ($voucher !== '' && $dadosGerais === null): ?>
    <div class="alert alert-warning" style="border-radius:10px">
        <i class="fas fa-exclamation-triangle mr-1"></i>
        Voucher <strong><?= htmlspecialchars($voucher) ?></strong> não encontrado.
        <a href="statusvoucher" class="ml-2">Tentar novamente</a>
    </div>

    <?php elseif ($dadosGerais !== null):
        $dtFmt = 'd/m/Y';
        $saldo = $totalServico - $totalPago;
        $badgeClass = match(true) {
            $saldo <= 0              => 'badge-pago',
            $saldo < $totalServico   => 'badge-parcial',
            default                  => 'badge-default',
        };
        $badgeLabel = match(true) {
            $saldo <= 0              => 'Pago',
            $saldo < $totalServico   => 'Parcial',
            default                  => 'Em aberto',
        };
    ?>

    <!-- Voucher card -->
    <div class="sv-card">
        <div class="sv-heading">
            <div class="sv-heading-left">
                <h3><i class="fas fa-file-invoice-dollar mr-2"></i>Voucher <?= htmlspecialchars($dadosGerais['numbervoucher']) ?></h3>
                <small>
                    Abertura: <?= date($dtFmt, strtotime($dadosGerais['abertura'])) ?>
                    &nbsp;·&nbsp;
                    <?= htmlspecialchars($dadosGerais['statuu'] ?? '—') ?>
                </small>
            </div>
            <span class="sv-heading-badge <?= $badgeClass ?>">
                <?= $badgeLabel ?>
            </span>
        </div>
        <div class="sv-body">

            <!-- Info grid -->
            <div class="sv-info-grid">
                <div class="sv-info-item">
                    <div class="lbl"><i class="fas fa-user fa-xs mr-1"></i> Pax</div>
                    <div class="val"><?= htmlspecialchars($dadosGerais['pax']) ?></div>
                </div>
                <div class="sv-info-item">
                    <div class="lbl"><i class="fas fa-concierge-bell fa-xs mr-1"></i> Serviço</div>
                    <div class="val"><?= htmlspecialchars($dadosGerais['servico'] ?? '—') ?></div>
                </div>
                <div class="sv-info-item">
                    <div class="lbl"><i class="fas fa-building fa-xs mr-1"></i> Agência</div>
                    <div class="val"><?= htmlspecialchars($dadosGerais['cliente'] ?? '—') ?></div>
                </div>
                <div class="sv-info-item">
                    <div class="lbl"><i class="fas fa-user-tie fa-xs mr-1"></i> Responsável</div>
                    <div class="val"><?= htmlspecialchars(trim(($dadosGerais['firstname'] ?? '') . ' ' . ($dadosGerais['lastname'] ?? ''))) ?></div>
                </div>
                <div class="sv-info-item">
                    <div class="lbl"><i class="fas fa-users fa-xs mr-1"></i> Passageiros</div>
                    <div class="val"><?= (int)$dadosGerais['qtdpax'] ?> adulto(s) &bull; <?= (int)$dadosGerais['qtdchild'] ?> criança(s)</div>
                </div>
                <div class="sv-info-item">
                    <div class="lbl"><i class="fas fa-calendar fa-xs mr-1"></i> Embarque</div>
                    <div class="val"><?= date($dtFmt, strtotime($dadosGerais['dateinput'])) ?></div>
                </div>
            </div>

            <!-- KPIs -->
            <div class="sv-kpis">
                <div class="sv-kpi blue">
                    <div class="kpi-label"><i class="fas fa-tag"></i> Total do Serviço</div>
                    <div class="kpi-value">R$ <?= number_format($totalServico, 2, ',', '.') ?></div>
                </div>
                <div class="sv-kpi green">
                    <div class="kpi-label"><i class="fas fa-check-circle"></i> Total Pago</div>
                    <div class="kpi-value">R$ <?= number_format($totalPago, 2, ',', '.') ?></div>
                </div>
                <div class="sv-kpi <?= $saldo > 0 ? 'red' : 'green' ?>">
                    <div class="kpi-label"><i class="fas fa-balance-scale"></i> Saldo Devedor</div>
                    <div class="kpi-value">R$ <?= number_format(max(0, $saldo), 2, ',', '.') ?></div>
                </div>
            </div>

            <!-- Pagamentos registrados -->
            <?php if (!empty($pagamentos)): ?>
            <div class="sv-section-title"><i class="fas fa-money-bill-wave"></i> Pagamentos Registrados</div>
            <div class="table-responsive">
                <table class="sv-table">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Forma de Pagamento</th>
                            <th style="text-align:right">Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($pagamentos as $p): ?>
                        <tr>
                            <td><?= date($dtFmt, strtotime($p['datacredit'])) ?></td>
                            <td><?= htmlspecialchars($p['forma'] ?? '—') ?></td>
                            <td style="text-align:right;font-weight:700;color:var(--navy)">R$ <?= number_format((float)$p['valuecredit'], 2, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

            <!-- Faturas -->
            <?php if (!empty($faturas)): ?>
            <div class="sv-section-title"><i class="fas fa-file-alt"></i> Faturas Cadastradas</div>
            <div class="table-responsive">
                <table class="sv-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Vencimento</th>
                            <th>Pagamento</th>
                            <th>Observação</th>
                            <th>Conta</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($faturas as $f): ?>
                        <tr>
                            <td style="color:#94a3b8;font-size:12px"><?= (int)$f['id'] ?></td>
                            <td><?= date($dtFmt, strtotime($f['datematurity'])) ?></td>
                            <td><?= date($dtFmt, strtotime($f['datepayment'])) ?></td>
                            <td><?= htmlspecialchars($f['numberadd'] ?? '') ?></td>
                            <td><?= htmlspecialchars($f['conta'] ?? '—') ?></td>
                            <td>
                                <button class="btn-sv-edit" type="button"
                                    data-toggle="modal" data-target="#modalEditFatura"
                                    data-id="<?= (int)$f['id'] ?>"
                                    data-venci="<?= htmlspecialchars($f['datematurity']) ?>"
                                    data-pag="<?= htmlspecialchars($f['datepayment']) ?>"
                                    data-num="<?= htmlspecialchars($f['numberadd'] ?? '') ?>"
                                    data-conta="<?= (int)$f['idcurrentaccount'] ?>"
                                    data-voucher="<?= htmlspecialchars($dadosGerais['numbervoucher']) ?>">
                                    <i class="fas fa-edit"></i> Editar
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

            <!-- Cadastrar fatura -->
            <div class="sv-section-title"><i class="fas fa-plus-circle"></i> Cadastrar Fatura</div>
            <form method="post">
                <input type="hidden" name="voucher" value="<?= htmlspecialchars($dadosGerais['numbervoucher']) ?>">
                <div class="sv-form-grid">
                    <div>
                        <label for="add-status">Status Invoice</label>
                        <select name="statusescolhido" id="add-status" class="form-control" required>
                            <?php foreach ($statusList as $s): ?>
                                <option value="<?= (int)$s->id ?>"
                                    <?= ((int)$s->id === (int)$dadosGerais['idstatusinvoice']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($s->nameinvoice) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="add-conta">Forma de Pagamento</label>
                        <select name="ccfp" id="add-conta" class="form-control" required>
                            <option value="">Selecione</option>
                            <?php foreach ($contas as $c): ?>
                                <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars(strtoupper($c['name'])) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="add-venci">Data Vencimento</label>
                        <input type="date" name="datavencimento" id="add-venci" class="form-control" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div>
                        <label for="add-pag">Data Pagamento</label>
                        <input type="date" name="datapagamento" id="add-pag" class="form-control" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div>
                        <label for="add-obs">Observação</label>
                        <input type="text" name="numeracao" id="add-obs" class="form-control" value="Ok" placeholder="Info adicional">
                    </div>
                </div>
                <button type="submit" name="salvar" class="btn-sv-primary">
                    <i class="fas fa-save"></i> Cadastrar Fatura
                </button>
            </form>

        </div>
    </div>

    <!-- Modal: Editar Fatura -->
    <div class="modal fade" id="modalEditFatura" tabindex="-1" role="dialog" aria-labelledby="modalEditFatTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" style="border-radius:12px;overflow:hidden">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditFatTitle"><i class="fas fa-edit mr-2"></i>Editar Fatura</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <form method="post">
                        <input type="hidden" name="idfatura" id="ef_idfatura">
                        <input type="hidden" name="voucher"  id="ef_voucher">
                        <div class="sv-form-grid">
                            <div>
                                <label for="ef_status">Status Invoice</label>
                                <select name="novostatus" class="form-control" id="ef_status">
                                    <?php foreach ($statusList as $s): ?>
                                        <option value="<?= (int)$s->id ?>"><?= htmlspecialchars($s->nameinvoice) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label for="ef_conta">Forma de Pagamento</label>
                                <select name="formapagamento" class="form-control" id="ef_conta">
                                    <?php foreach ($contas as $c): ?>
                                        <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars(strtoupper($c['name'])) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label for="ef_venci">Data Vencimento</label>
                                <input type="date" name="datavencimento" class="form-control" id="ef_venci">
                            </div>
                            <div>
                                <label for="ef_pag">Data Pagamento</label>
                                <input type="date" name="datapagamento" class="form-control" id="ef_pag">
                            </div>
                            <div>
                                <label for="ef_num">Observação</label>
                                <input type="text" name="numeracao" class="form-control" id="ef_num">
                            </div>
                        </div>
                        <button type="submit" name="atualizar" class="btn-sv-primary">
                            <i class="fas fa-save"></i> Salvar Alterações
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php endif; ?>

</div>
</div>

<script>
$('#modalEditFatura').on('show.bs.modal', function (e) {
    var btn = $(e.relatedTarget);
    $('#ef_idfatura').val(btn.data('id'));
    $('#ef_voucher').val(btn.data('voucher'));
    $('#ef_venci').val(btn.data('venci'));
    $('#ef_pag').val(btn.data('pag'));
    $('#ef_num').val(btn.data('num'));
    $('#ef_conta').val(btn.data('conta'));
});
</script>

<?php require_once('footer.php'); ?>
