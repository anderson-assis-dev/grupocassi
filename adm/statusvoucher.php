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
    .sv-page { background: #f4f7fb; min-height: calc(100vh - 70px); padding-bottom: 48px; }
    .sv-page .containerrrr { width: 94%; max-width: 960px; margin: 0 auto; }
    .sv-card { border: 0; border-radius: 20px; background: #fff; box-shadow: 0 18px 45px rgba(15,23,42,.08); overflow: hidden; margin-top: 20px; }
    .sv-heading { background: linear-gradient(135deg,#1e4770,#256aa0); color: #fff; padding: 20px 28px; }
    .sv-heading h3 { color: #fff; font-size: 20px; font-weight: 700; margin: 0; }
    .sv-heading small { color: rgba(255,255,255,.75); font-size: 13px; }
    .sv-body { padding: 24px 28px; }
    .sv-info-pill { display: inline-block; background: #f1f5f9; border-radius: 8px; padding: 6px 14px;
        font-size: 13px; color: #475569; margin: 0 6px 8px 0; }
    .sv-info-pill strong { color: #1e293b; }
    .sv-totals { display: flex; gap: 14px; flex-wrap: wrap; margin: 18px 0 24px; }
    .sv-total-box { flex: 1; min-width: 140px; border-radius: 12px; padding: 16px 18px; }
    .sv-total-box.servico  { background: #eff6ff; border: 1px solid #bfdbfe; }
    .sv-total-box.pago     { background: #f0fdf4; border: 1px solid #bbf7d0; }
    .sv-total-box.saldo    { background: #fefce8; border: 1px solid #fde68a; }
    .sv-total-box.negativo { background: #fef2f2; border: 1px solid #fecaca; }
    .sv-total-box .label { font-size: 11px; text-transform: uppercase; letter-spacing: .6px; font-weight: 700; color: #64748b; }
    .sv-total-box .value { font-size: 22px; font-weight: 800; margin-top: 4px; color: #1e293b; }
    .sv-section-title { font-size: 14px; font-weight: 700; color: #1e4770; text-transform: uppercase;
        letter-spacing: .5px; border-bottom: 2px solid #e5e7eb; padding-bottom: 8px; margin: 26px 0 14px; }
    .sv-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .sv-table th { background: #f8fafc; color: #64748b; font-size: 11px; text-transform: uppercase;
        letter-spacing: .5px; padding: 10px 14px; border-bottom: 2px solid #e5e7eb; text-align: left; }
    .sv-table td { padding: 10px 14px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
    .sv-table tr:last-child td { border-bottom: 0; }
    .sv-form-row { display: flex; flex-wrap: wrap; gap: 14px; margin-bottom: 16px; }
    .sv-form-row .field { flex: 1; min-width: 160px; }
    .sv-form-row label { font-size: 12px; font-weight: 700; color: #374151; margin-bottom: 5px; display: block; }
    .sv-form-row .form-control { border: 1px solid #dce3ec; border-radius: 10px; min-height: 40px; font-size: 13px; }
    .sv-form-row .form-control:focus { border-color: #1e88d1; box-shadow: 0 0 0 3px rgba(30,136,209,.14); outline: none; }
    .btn-sv-primary { background: #1e4770; color: #fff; border: 0; border-radius: 10px; font-weight: 700;
        padding: 10px 26px; min-height: 42px; cursor: pointer; font-size: 14px; }
    .btn-sv-primary:hover { background: #256aa0; }
    .btn-sv-sm { font-size: 12px; padding: 4px 12px; border-radius: 6px; border: 1px solid #cbd5e1;
        background: #f8fafc; color: #475569; cursor: pointer; }
    .btn-sv-sm:hover { background: #e2e8f0; }
    .badge-status { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; }
    .badge-pago    { background: #dcfce7; color: #166534; }
    .badge-parcial { background: #fef9c3; color: #92400e; }
    .badge-cancelado { background: #fee2e2; color: #991b1b; }
    .badge-default { background: #f1f5f9; color: #475569; }
</style>

<div class="page-content--bgf7 sv-page">
    <section class="au-breadcrumb2">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <ul class="list-unstyled list-inline au-breadcrumb__list" style="margin:0;padding:18px 0 10px">
                        <li class="list-inline-item active"><a href="./index.php">Home</a></li>
                        <li class="list-inline-item seprate"><span>/</span></li>
                        <li class="list-inline-item">Financeiro: Dar Baixa</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <div class="containerrrr">

        <?php if ($flash): ?>
            <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show" role="alert" style="border-radius:12px;margin-top:16px">
                <?= htmlspecialchars($flash['msg']) ?>
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        <?php endif; ?>

        <!-- Search card -->
        <div class="sv-card">
            <div class="sv-heading">
                <h3>Financeiro: Dar Baixa</h3>
                <small>Busque o voucher para registrar ou atualizar faturas</small>
            </div>
            <div class="sv-body">
                <form method="post" style="max-width:340px">
                    <label for="numberVoucher" style="font-size:12px;font-weight:700;color:#374151;margin-bottom:5px;display:block">Nº do Voucher</label>
                    <div class="input-group">
                        <input type="text" name="numberVoucher" id="numberVoucher" class="form-control" style="border-radius:10px 0 0 10px;border:1px solid #dce3ec;min-height:42px"
                               value="<?= htmlspecialchars($voucher) ?>" placeholder="ex: 12345" autofocus>
                        <div class="input-group-append">
                            <button class="btn-sv-primary" type="submit" style="border-radius:0 10px 10px 0">Buscar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($voucher !== '' && $dadosGerais === null): ?>
            <div class="alert alert-warning" style="margin-top:18px;border-radius:12px">
                Voucher <strong><?= htmlspecialchars($voucher) ?></strong> não encontrado.
                <a href="statusvoucher">Tentar novamente</a>
            </div>

        <?php elseif ($dadosGerais !== null):
            $saldo = $totalServico - $totalPago;
            $badgeClass = match(true) {
                $saldo <= 0  => 'badge-pago',
                $saldo < $totalServico => 'badge-parcial',
                default => 'badge-default',
            };
        ?>

        <!-- Voucher info card -->
        <div class="sv-card">
            <div class="sv-heading">
                <h3>Voucher <?= htmlspecialchars($dadosGerais['numbervoucher']) ?></h3>
                <small>
                    Abertura: <?= date('d/m/Y', strtotime($dadosGerais['abertura'])) ?>
                    &nbsp;·&nbsp;
                    Status: <?= htmlspecialchars($dadosGerais['statuu'] ?? '—') ?>
                </small>
            </div>
            <div class="sv-body">

                <!-- Info pills -->
                <div>
                    <span class="sv-info-pill"><strong>PAX:</strong> <?= htmlspecialchars($dadosGerais['pax']) ?></span>
                    <span class="sv-info-pill"><strong>Serviço:</strong> <?= htmlspecialchars($dadosGerais['servico'] ?? '—') ?></span>
                    <span class="sv-info-pill"><strong>Cliente:</strong> <?= htmlspecialchars($dadosGerais['cliente'] ?? '—') ?></span>
                    <span class="sv-info-pill"><strong>Responsável:</strong> <?= htmlspecialchars(($dadosGerais['firstname'] ?? '') . ' ' . ($dadosGerais['lastname'] ?? '')) ?></span>
                    <span class="sv-info-pill"><strong>Pax:</strong> <?= (int)$dadosGerais['qtdpax'] ?> adulto(s) + <?= (int)$dadosGerais['qtdchild'] ?> criança(s)</span>
                </div>

                <!-- Totals -->
                <div class="sv-totals">
                    <div class="sv-total-box servico">
                        <div class="label">Total Serviço</div>
                        <div class="value">R$ <?= number_format($totalServico, 2, ',', '.') ?></div>
                    </div>
                    <div class="sv-total-box pago">
                        <div class="label">Total Pago</div>
                        <div class="value">R$ <?= number_format($totalPago, 2, ',', '.') ?></div>
                    </div>
                    <div class="sv-total-box <?= $saldo > 0 ? 'negativo' : 'pago' ?>">
                        <div class="label">Saldo Devedor</div>
                        <div class="value">R$ <?= number_format(max(0, $saldo), 2, ',', '.') ?></div>
                    </div>
                </div>

                <!-- Payments -->
                <?php if (!empty($pagamentos)): ?>
                    <div class="sv-section-title">Pagamentos Registrados</div>
                    <div class="table-responsive">
                        <table class="sv-table">
                            <thead><tr>
                                <th>Data</th>
                                <th>Forma</th>
                                <th style="text-align:right">Valor</th>
                            </tr></thead>
                            <tbody>
                            <?php foreach ($pagamentos as $p): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($p['datacredit'])) ?></td>
                                    <td><?= htmlspecialchars($p['forma'] ?? '—') ?></td>
                                    <td style="text-align:right;font-weight:600">R$ <?= number_format((float)$p['valuecredit'], 2, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <!-- Faturas -->
                <?php if (!empty($faturas)): ?>
                    <div class="sv-section-title">Faturas Cadastradas</div>
                    <div class="table-responsive">
                        <table class="sv-table">
                            <thead><tr>
                                <th>#</th>
                                <th>Vencimento</th>
                                <th>Pagamento</th>
                                <th>Observação</th>
                                <th>Conta</th>
                                <th></th>
                            </tr></thead>
                            <tbody>
                            <?php foreach ($faturas as $f): ?>
                                <tr>
                                    <td><?= (int)$f['id'] ?></td>
                                    <td><?= date('d/m/Y', strtotime($f['datematurity'])) ?></td>
                                    <td><?= date('d/m/Y', strtotime($f['datepayment'])) ?></td>
                                    <td><?= htmlspecialchars($f['numberadd'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($f['conta'] ?? '—') ?></td>
                                    <td>
                                        <button class="btn-sv-sm" type="button"
                                            data-toggle="modal" data-target="#modalEditFatura"
                                            data-id="<?= (int)$f['id'] ?>"
                                            data-venci="<?= htmlspecialchars($f['datematurity']) ?>"
                                            data-pag="<?= htmlspecialchars($f['datepayment']) ?>"
                                            data-num="<?= htmlspecialchars($f['numberadd'] ?? '') ?>"
                                            data-conta="<?= (int)$f['idcurrentaccount'] ?>"
                                            data-voucher="<?= htmlspecialchars($dadosGerais['numbervoucher']) ?>">
                                            Editar
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <!-- Add fatura form -->
                <div class="sv-section-title">Cadastrar Fatura</div>
                <form method="post">
                    <input type="hidden" name="voucher" value="<?= htmlspecialchars($dadosGerais['numbervoucher']) ?>">
                    <div class="sv-form-row">
                        <div class="field">
                            <label>Status Invoice</label>
                            <select name="statusescolhido" class="form-control" required>
                                <?php foreach ($statusList as $s): ?>
                                    <option value="<?= (int)$s->id ?>"
                                        <?= ((int)$s->id === (int)$dadosGerais['idstatusinvoice']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($s->nameinvoice) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="field">
                            <label>Forma de Pagamento</label>
                            <select name="ccfp" class="form-control" required>
                                <option value="">Selecione</option>
                                <?php foreach ($contas as $c): ?>
                                    <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars(strtoupper($c['name'])) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="field" style="min-width:140px;max-width:160px">
                            <label>Data Vencimento</label>
                            <input type="date" name="datavencimento" class="form-control" value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="field" style="min-width:140px;max-width:160px">
                            <label>Data Pagamento</label>
                            <input type="date" name="datapagamento" class="form-control" value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="field">
                            <label>Observação</label>
                            <input type="text" name="numeracao" class="form-control" value="Ok" placeholder="Info adicional">
                        </div>
                    </div>
                    <button type="submit" name="salvar" class="btn-sv-primary">Cadastrar Fatura</button>
                </form>

            </div>
        </div>

        <!-- Edit fatura modal -->
        <div class="modal fade" id="modalEditFatura" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content" style="border-radius:16px;overflow:hidden">
                    <div class="modal-header" style="background:linear-gradient(135deg,#1e4770,#256aa0);color:#fff;border:0">
                        <h5 class="modal-title" style="color:#fff;font-weight:700">Editar Fatura</h5>
                        <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:.8"><span>&times;</span></button>
                    </div>
                    <div class="modal-body" style="padding:24px 28px">
                        <form method="post">
                            <input type="hidden" name="idfatura" id="ef_idfatura">
                            <input type="hidden" name="voucher" id="ef_voucher">
                            <div class="sv-form-row">
                                <div class="field">
                                    <label for="ef_status">Status Invoice</label>
                                    <select name="novostatus" class="form-control" id="ef_status">
                                        <?php foreach ($statusList as $s): ?>
                                            <option value="<?= (int)$s->id ?>"><?= htmlspecialchars($s->nameinvoice) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="field">
                                    <label for="ef_conta">Forma de Pagamento</label>
                                    <select name="formapagamento" class="form-control" id="ef_conta">
                                        <?php foreach ($contas as $c): ?>
                                            <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars(strtoupper($c['name'])) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="field" style="min-width:140px;max-width:160px">
                                    <label for="ef_venci">Data Vencimento</label>
                                    <input type="date" name="datavencimento" class="form-control" id="ef_venci">
                                </div>
                                <div class="field" style="min-width:140px;max-width:160px">
                                    <label for="ef_pag">Data Pagamento</label>
                                    <input type="date" name="datapagamento" class="form-control" id="ef_pag">
                                </div>
                                <div class="field">
                                    <label for="ef_num">Observação</label>
                                    <input type="text" name="numeracao" class="form-control" id="ef_num">
                                </div>
                            </div>
                            <button type="submit" name="atualizar" class="btn-sv-primary">Salvar alterações</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <?php endif; ?>

    </div><!-- /containerrrr -->
</div><!-- /sv-page -->

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
