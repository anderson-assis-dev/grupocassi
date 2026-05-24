<?php
require_once 'header.php';
require_once __DIR__ . '/includes/flash.php';

// ── reference data ────────────────────────────────────────────────────────────
$clientes = $pdo->query(
    'SELECT id, fullname FROM ct_cliente ORDER BY fullname ASC'
)->fetchAll(PDO::FETCH_ASSOC);

$statuses = $pdo->query('SELECT id, nameinvoice FROM ct_statusinvoice')->fetchAll(PDO::FETCH_ASSOC);

$faturas = $pdo->query(
    'SELECT f.id, f.dateinput, f.dateoutput, f.tarifa, f.credito,
            c.fullname, f.situacao, f.idcliente, f.status
     FROM ct_fatura f
     LEFT JOIN ct_cliente c ON c.id = f.idcliente
     ORDER BY f.id DESC'
)->fetchAll(PDO::FETCH_ASSOC);

// ── search ────────────────────────────────────────────────────────────────────
$searchDone  = false;
$searchRows  = [];
$grandTotal  = 0.0;
$grandCredit = 0.0;
$idcliente   = 0;
$datainicio  = '';
$datafinal   = '';
$statSel     = [];

if (isset($_POST['pesquisarfatura'])) {
    $searchDone = true;
    $datainicio = $_POST['periodoinicial'];
    $datafinal  = $_POST['periodofinal'];
    $idcliente  = (int)$_POST['cliente'];
    $statSel    = array_map('intval', (array)($_POST['status'] ?? []));

    $_SESSION['periodoinicial'] = $datainicio;
    $_SESSION['periodofinal']   = $datafinal;
    $_SESSION['cliente']        = $idcliente;
    $_SESSION['status']         = $statSel;

    if (!empty($statSel)) {
        $inSt = implode(',', $statSel);
        $st = $pdo->prepare(
            "SELECT r.id, r.pax, r.numbervoucher, r.idservico,
                    r.valueservice AS valorP, r.qtdpax, r.qtdchild, r.idcliente
             FROM ct_reserva r
             WHERE r.dateinput >= ? AND r.dateinput <= ?
               AND r.idstatusinvoice IN ($inSt)
               AND r.idcliente = ? AND r.idstatus <> 2
             ORDER BY r.numbervoucher"
        );
        $st->execute([$datainicio, $datafinal, $idcliente]);
        $reservas = $st->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($reservas)) {
            $resIds  = array_column($reservas, 'id');
            $vouchers = array_unique(array_column($reservas, 'numbervoucher'));
            $svcIds  = array_unique(array_column($reservas, 'idservico'));

            // bulk: net price per service
            $netMap = [];
            if (!empty($svcIds)) {
                $in = implode(',', array_map('intval', $svcIds));
                $q  = $pdo->prepare("SELECT idservice, valuenet FROM ct_clientservice WHERE idclient = ? AND idservice IN ($in)");
                $q->execute([$idcliente]);
                foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $r) {
                    $netMap[(int)$r['idservice']] = (float)$r['valuenet'];
                }
            }

            // bulk: credits per voucher
            $creditMap = [];
            if (!empty($vouchers)) {
                $ph = implode(',', array_fill(0, count($vouchers), '?'));
                $q  = $pdo->prepare("SELECT numbervoucher, SUM(valuecredit) AS credito FROM ct_createfaturacredit WHERE numbervoucher IN ($ph) GROUP BY numbervoucher");
                $q->execute($vouchers);
                foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $r) {
                    $creditMap[$r['numbervoucher']] = (float)$r['credito'];
                }
            }

            // bulk: add-on rows
            $addMap    = [];
            $addNetMap = [];
            if (!empty($resIds)) {
                $in = implode(',', array_map('intval', $resIds));
                $q  = $pdo->prepare(
                    "SELECT ra.idrecently, ra.qpax, ra.qchild, ra.valueservice AS valorS,
                            ra.idservice, r.pax, r.numbervoucher, r.idcliente
                     FROM ct_recentlyadd ra
                     LEFT JOIN ct_reserva r ON r.id = ra.idrecently
                     WHERE ra.idrecently IN ($in)"
                );
                $q->execute();
                $addRows = $q->fetchAll(PDO::FETCH_ASSOC);
                foreach ($addRows as $r) {
                    $addMap[(int)$r['idrecently']][] = $r;
                }
                $addSvcIds = array_unique(array_map('intval', array_column($addRows, 'idservice')));
                if (!empty($addSvcIds)) {
                    $in2 = implode(',', $addSvcIds);
                    $q   = $pdo->prepare("SELECT idservice, valuenet FROM ct_clientservice WHERE idclient = ? AND idservice IN ($in2)");
                    $q->execute([$idcliente]);
                    foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $r) {
                        $addNetMap[(int)$r['idservice']] = (float)$r['valuenet'];
                    }
                }
            }

            // build display rows
            foreach ($reservas as $r) {
                $net   = $netMap[(int)$r['idservico']] ?? 0;
                $price = $net > 0 ? $net : (float)$r['valorP'];
                $sub   = $price * $r['qtdpax'] + ($price / 2) * $r['qtdchild'];
                $grandTotal  += $sub;
                $grandCredit += $creditMap[$r['numbervoucher']] ?? 0;

                $addLines = [];
                foreach ($addMap[(int)$r['id']] ?? [] as $a) {
                    $aNet   = $addNetMap[(int)$a['idservice']] ?? 0;
                    $aPrice = $aNet > 0 ? $aNet : (float)$a['valorS'];
                    $aSub   = $aPrice * $a['qpax'] + ($aPrice / 2) * $a['qchild'];
                    $grandTotal += $aSub;
                    $addLines[] = ['voucher' => $a['numbervoucher'], 'pax' => $a['pax'], 'subtotal' => $aSub];
                }

                $searchRows[] = [
                    'voucher'  => $r['numbervoucher'],
                    'pax'      => $r['pax'],
                    'subtotal' => $sub,
                    'adds'     => $addLines,
                ];
            }
        }
    }
}

$flash = getFlash();
?>
<style>
:root { --navy: #1e4770; --navy-lt: #2a5f96; }
.map-wrapper { padding: 20px 20px 80px; }
.bc-bar { padding: 0 0 16px; font-size: 13px; color: #6c757d; }
.bc-bar a { color: var(--navy); font-weight: 600; text-decoration: none; }
.bc-bar a:hover { text-decoration: underline; }
.bc-bar .sep { margin: 0 6px; color: #ccc; }

.ft-card { background: #fff; border-radius: 12px; box-shadow: 0 2px 14px rgba(0,0,0,.07); overflow: hidden; margin-bottom: 24px; }
.ft-card-hd { background: linear-gradient(135deg, var(--navy), var(--navy-lt)); color: #fff; padding: 15px 22px; display: flex; align-items: center; gap: 9px; font-weight: 700; font-size: 15px; }
.ft-card-hd i { font-size: 16px; opacity: .85; }
.ft-body { padding: 22px 24px; }

.ft-form-row { display: flex; flex-wrap: wrap; gap: 16px; margin-bottom: 16px; }
.ft-form-col { flex: 1 1 180px; min-width: 0; }
.ft-form-col label { display: block; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 5px; }
.ft-form-col select,
.ft-form-col input { width: 100%; border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 9px 12px; font-size: 14px; color: #1e293b; background: #f8fafc; transition: border-color .2s; }
.ft-form-col select:focus,
.ft-form-col input:focus  { border-color: var(--navy); outline: none; box-shadow: 0 0 0 3px rgba(30,71,112,.1); }

.btn-ft-search { background: var(--navy); color: #fff; border: none; border-radius: 8px; padding: 10px 26px; font-size: 14px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 7px; transition: background .2s; }
.btn-ft-search:hover { background: var(--navy-lt); }

.ft-table { width: 100%; border-collapse: collapse; font-size: 14px; margin-top: 20px; }
.ft-table thead tr { background: var(--navy); color: #fff; }
.ft-table thead th { padding: 10px 14px; font-weight: 600; font-size: 13px; text-align: left; }
.ft-table tbody tr { border-bottom: 1px solid #f1f5f9; }
.ft-table tbody tr:hover { background: #f8fafc; }
.ft-table tbody tr.add-row td { background: #f0f4ff; font-size: 13px; color: #475569; }
.ft-table td { padding: 9px 14px; }
.ft-table tfoot td { background: #f1f5f9; font-weight: 700; padding: 11px 14px; border-top: 2px solid var(--navy); }

.voucher-btn { background: none; border: none; color: var(--navy); font-weight: 600; cursor: pointer; padding: 0; text-decoration: underline dotted; font-size: inherit; }
.btn-ft-create { background: #10b981; color: #fff; border: none; border-radius: 8px; padding: 12px 24px; font-size: 15px; font-weight: 700; width: 100%; margin-top: 18px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: background .2s; }
.btn-ft-create:hover { background: #059669; }

.badge-ativo   { background: #dcfce7; color: #166534; border-radius: 20px; padding: 3px 10px; font-size: 12px; font-weight: 600; white-space: nowrap; }
.badge-inativo { background: #fee2e2; color: #991b1b; border-radius: 20px; padding: 3px 10px; font-size: 12px; font-weight: 600; white-space: nowrap; }
.act-btn { background: none; border: 1.5px solid var(--navy); color: var(--navy); border-radius: 6px; padding: 4px 10px; font-size: 12px; cursor: pointer; transition: background .15s, color .15s; white-space: nowrap; }
.act-btn:hover { background: var(--navy); color: #fff; }
.act-btn-g { border-color: #10b981; color: #10b981; }
.act-btn-g:hover { background: #10b981; color: #fff; }
</style>

<div class="map-wrapper">

    <div class="bc-bar">
        <a href="./index">Home</a>
        <span class="sep">/</span>
        <span>Financeiro: Faturas</span>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'warning' ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($flash['msg']) ?>
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    <?php endif ?>

    <!-- ── Cadastrar Fatura ─────────────────────────────────────────────── -->
    <div class="ft-card">
        <div class="ft-card-hd">
            <i class="fas fa-file-invoice-dollar"></i> Cadastrar Fatura
        </div>
        <div class="ft-body">
            <form method="post" action="">
                <div class="ft-form-row">
                    <div class="ft-form-col">
                        <label for="periodoinicial">Período Inicial</label>
                        <input type="date" name="periodoinicial" id="periodoinicial"
                               value="<?= htmlspecialchars($_SESSION['periodoinicial'] ?? '') ?>" required>
                    </div>
                    <div class="ft-form-col">
                        <label for="periodofinal">Período Final</label>
                        <input type="date" name="periodofinal" id="periodofinal"
                               value="<?= htmlspecialchars($_SESSION['periodofinal'] ?? '') ?>" required>
                    </div>
                    <div class="ft-form-col" style="flex: 2 1 240px">
                        <label for="cliente">Cliente</label>
                        <select name="cliente" id="cliente" required>
                            <option value="">Selecione…</option>
                            <?php foreach ($clientes as $c): ?>
                                <option value="<?= $c['id'] ?>"
                                    <?= $idcliente == $c['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['fullname']) ?>
                                </option>
                            <?php endforeach ?>
                        </select>
                    </div>
                    <div class="ft-form-col">
                        <label for="status">Status <small style="font-size:10px;text-transform:none">(Ctrl+clique)</small></label>
                        <select name="status[]" id="status" multiple required style="min-height: 90px">
                            <?php foreach ($statuses as $s): ?>
                                <option value="<?= $s['id'] ?>"
                                    <?= in_array((int)$s['id'], $statSel, true) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($s['nameinvoice']) ?>
                                </option>
                            <?php endforeach ?>
                        </select>
                    </div>
                </div>
                <button type="submit" name="pesquisarfatura" class="btn-ft-search">
                    <i class="fas fa-search"></i> Pesquisar Reservas
                </button>
            </form>

            <?php if ($searchDone): ?>
                <?php if (empty($searchRows)): ?>
                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Nenhuma reserva encontrada para os filtros selecionados.
                    </div>
                <?php else: ?>
                    <table class="ft-table">
                        <thead>
                            <tr>
                                <th>Voucher</th>
                                <th>Pax</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($searchRows as $row): ?>
                                <tr>
                                    <td>
                                        <form method="post" target="_blank" action="./informacoes-reserva.php" style="display:inline">
                                            <input type="hidden" name="voucher" value="<?= htmlspecialchars($row['voucher']) ?>">
                                            <button type="submit" class="voucher-btn"><?= htmlspecialchars($row['voucher']) ?></button>
                                        </form>
                                    </td>
                                    <td><?= htmlspecialchars($row['pax']) ?></td>
                                    <td>R$ <?= number_format($row['subtotal'], 2, ',', '.') ?></td>
                                </tr>
                                <?php foreach ($row['adds'] as $add): ?>
                                    <tr class="add-row">
                                        <td style="padding-left:28px">
                                            <i class="fas fa-plus-circle mr-1" style="font-size:11px;opacity:.5"></i>
                                            <form method="post" target="_blank" action="./informacoes-reserva.php" style="display:inline">
                                                <input type="hidden" name="voucher" value="<?= htmlspecialchars($add['voucher']) ?>">
                                                <button type="submit" class="voucher-btn" style="font-size:13px"><?= htmlspecialchars($add['voucher']) ?></button>
                                            </form>
                                        </td>
                                        <td><?= htmlspecialchars($add['pax']) ?></td>
                                        <td>R$ <?= number_format($add['subtotal'], 2, ',', '.') ?></td>
                                    </tr>
                                <?php endforeach ?>
                            <?php endforeach ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2">Total da Fatura</td>
                                <td>R$ <?= number_format($grandTotal, 2, ',', '.') ?></td>
                            </tr>
                        </tfoot>
                    </table>

                    <form action="inicio-fim-fatura.php" method="post">
                        <input type="hidden" name="idcliente"   value="<?= $idcliente ?>">
                        <input type="hidden" name="total"       value="<?= $grandTotal ?>">
                        <input type="hidden" name="credito"     value="<?= $grandCredit ?>">
                        <input type="hidden" name="inicio"      value="<?= htmlspecialchars($datainicio) ?>">
                        <input type="hidden" name="fim"         value="<?= htmlspecialchars($datafinal) ?>">
                        <input type="hidden" name="statusatual" value="<?= htmlspecialchars(implode(',', $statSel)) ?>">
                        <button type="submit" name="todosVoucher" class="btn-ft-create">
                            <i class="fas fa-check-circle"></i> Cadastrar Fatura
                        </button>
                    </form>
                <?php endif ?>
            <?php endif ?>
        </div>
    </div>

    <!-- ── Faturas Cadastradas ──────────────────────────────────────────── -->
    <div class="ft-card">
        <div class="ft-card-hd">
            <i class="fas fa-list-alt"></i> Faturas Cadastradas
            <span style="margin-left:auto;font-size:13px;font-weight:400;opacity:.8"><?= count($faturas) ?> fatura(s)</span>
        </div>
        <div class="ft-body">
            <?php if (empty($faturas)): ?>
                <div class="alert alert-info">Nenhuma fatura cadastrada.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="ft-table">
                        <thead>
                            <tr>
                                <th>Nº</th>
                                <th>Cliente</th>
                                <th>Total</th>
                                <th>Crédito</th>
                                <th>De</th>
                                <th>Até</th>
                                <th>Situação</th>
                                <th style="width:80px"></th>
                                <th style="width:100px"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($faturas as $f): ?>
                                <tr>
                                    <td><strong>#<?= $f['id'] ?></strong></td>
                                    <td><?= htmlspecialchars($f['fullname'] ?? '—') ?></td>
                                    <td>R$ <?= number_format($f['tarifa'], 2, ',', '.') ?></td>
                                    <td>R$ <?= number_format($f['credito'], 2, ',', '.') ?></td>
                                    <td><?= date('d/m/Y', strtotime($f['dateinput'])) ?></td>
                                    <td><?= date('d/m/Y', strtotime($f['dateoutput'])) ?></td>
                                    <td>
                                        <?php if ($f['situacao'] == 1): ?>
                                            <span class="badge-ativo">Ativo</span>
                                        <?php else: ?>
                                            <span class="badge-inativo">Inativo</span>
                                        <?php endif ?>
                                    </td>
                                    <td>
                                        <form action="./editar-fatura.php" method="post" target="_blank">
                                            <input type="hidden" name="idfatura" value="<?= $f['id'] ?>">
                                            <button name="editar" type="submit" class="act-btn">
                                                <i class="fas fa-edit"></i> Editar
                                            </button>
                                        </form>
                                    </td>
                                    <td>
                                        <form action="./relatorio/pdf-relatorio-cliente-reserva.php" method="post" target="_blank">
                                            <input type="hidden" name="cliente"        value="<?= $f['idcliente'] ?>">
                                            <input type="hidden" name="periodoinicial" value="<?= htmlspecialchars($f['dateinput']) ?>">
                                            <input type="hidden" name="periodofinal"   value="<?= htmlspecialchars($f['dateoutput']) ?>">
                                            <input type="hidden" name="tarifa"         value="<?= $f['tarifa'] ?>">
                                            <input type="hidden" name="status"         value="<?= $f['status'] ?>">
                                            <button type="submit" class="act-btn act-btn-g">
                                                <i class="fas fa-print"></i> Fatura
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach ?>
                        </tbody>
                    </table>
                </div>
            <?php endif ?>
        </div>
    </div>

</div>
<?php require_once 'footer.php'; ?>
