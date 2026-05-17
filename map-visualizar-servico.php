<?php require_once 'header.php';

const DATE_BR = 'd/m/Y';

// ── Helpers ──────────────────────────────────────────────────────────────

function formatBRL(float $v): string {
    return 'R$ ' . number_format($v, 2, ',', '.');
}

function calcTotal(float $vs, int $pax, int $child): float {
    return ($vs * $pax) + (($vs / 2) * $child);
}

function isServicoPremium(int $id): bool {
    static $fixos = [21,39,40,42,54,56,65,104,107,108,111,120,124,130,131,
                     133,137,139,148,156,157,158,223,224,231,233,239,249];
    return in_array($id, $fixos)
        || ($id >= 59  && $id <= 64)
        || ($id >= 109 && $id <= 120)
        || $id >= 146;
}

// ── Formulário ───────────────────────────────────────────────────────────

if (isset($_POST['mapa'])) {
    $dateInput  = $_POST['datainicio'];
    $dateOutput = $_POST['datafim'];
    $idCliente  = (int) $_POST['cliente'];
    $servico    = array_map('intval', (array) $_POST['servico']);
    $idhorario  = (int) $_POST['horario'];
    $_SESSION['datainicio'] = $dateInput;
    $_SESSION['datafim']    = $dateOutput;
    $_SESSION['cliente']    = $idCliente;
    $_SESSION['servico']    = $servico;
    $_SESSION['horario']    = $idhorario;
    $filtrado = true;
} else {
    $dateInput  = $_SESSION['datainicio'] ?? date('Y-m-d');
    $dateOutput = $_SESSION['datafim']    ?? date('Y-m-d');
    $idCliente  = (int) ($_SESSION['cliente'] ?? 0);
    $servico    = (array) ($_SESSION['servico'] ?? [0]);
    $idhorario  = (int) ($_SESSION['horario']  ?? 0);
    $filtrado   = isset($_SESSION['datainicio']);
}

// ── Dados de referência ───────────────────────────────────────────────────

$todosClientes = $pdo->query('SELECT id, fullname FROM ct_cliente ORDER BY fullname')
                     ->fetchAll(PDO::FETCH_ASSOC);
$todosServicos = $pdo->query('SELECT id, fullname FROM ct_servico ORDER BY ordem, fullname DESC')
                     ->fetchAll(PDO::FETCH_ASSOC);
$schedules     = $pdo->query("SELECT idshedule, schedule FROM ct_service_schedule
                               WHERE schedule NOT LIKE '00:00%' ORDER BY schedule")
                     ->fetchAll(PDO::FETCH_ASSOC);

// ── Query builder ─────────────────────────────────────────────────────────
// Substitui os 8 blocos de query condicional por uma única função dinâmica.

function fetchMapaServico(PDO $pdo, string $dateIn, string $dateOut,
                          int $idCliente, array $servicos, int $idhorario): array
{
    $excluidos   = '19,30,47,48';
    $todosServ   = ($servicos[0] === 0 && count($servicos) === 1);
    $servicosIn  = $todosServ ? '' : implode(',', array_map('intval', $servicos));

    // ── Reservas principais (ct_reserva)
    $where  = ["r.dateinput >= :inn", "r.dateinput <= :outt",
               "r.idstatus <> 2", "r.idservico NOT IN ($excluidos)"];
    $params = [':inn' => $dateIn, ':outt' => $dateOut];

    if ($idCliente > 0) { $where[] = 'r.idcliente = :cliente';   $params[':cliente']   = $idCliente; }
    if ($idhorario > 0) { $where[] = 'r.idhorario = :idhorario'; $params[':idhorario'] = $idhorario; }
    if (!$todosServ)    { $where[] = "r.idservico IN ($servicosIn)"; }

    $stmt = $pdo->prepare(
        "SELECT r.id, r.pax, r.documento, r.dateinput, r.numbervoucher,
                r.horaap, r.valueservice, r.qtdpax, r.qtdchild, r.qtdfree,
                r.totalservico, r.totalcredito, r.photoresident, r.idservico,
                c.fullname   AS cliente,
                se.fullname  AS servico,
                ss.schedule,
                u.firstname, u.lastname,
                cfp.namepayment,
                sta.fullname AS situacao,
                'reserva'    AS origem
         FROM ct_reserva r
         LEFT JOIN ct_cliente c              ON c.id  = r.idcliente
         JOIN  ct_servico se                 ON se.id = r.idservico
         LEFT JOIN ct_service_schedule ss    ON ss.idshedule = r.idhorario
         LEFT JOIN `ct_form_of_ payment` cfp ON cfp.id = r.idpayment
         LEFT JOIN ct_usuario u              ON u.id  = r.idresponsavel
         LEFT JOIN ct_status sta             ON sta.id = r.idstatus
         WHERE " . implode(' AND ', $where) . "
         ORDER BY ss.schedule, r.dateinput"
    );
    $stmt->execute($params);
    $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ── Serviços adicionais (ct_recentlyadd)
    $whereA  = ["ra.dateinput >= :inn", "ra.dateinput <= :outt",
                "r.idstatus <> 2", "ra.idservice NOT IN ($excluidos)"];
    $paramsA = [':inn' => $dateIn, ':outt' => $dateOut];

    if ($idCliente > 0) { $whereA[] = 'r.idcliente = :cliente';    $paramsA[':cliente']   = $idCliente; }
    if ($idhorario > 0) { $whereA[] = 'ra.idschedule = :idhorario'; $paramsA[':idhorario'] = $idhorario; }
    if (!$todosServ)    { $whereA[] = "ra.idservice IN ($servicosIn)"; }

    $stmtA = $pdo->prepare(
        "SELECT ra.dateinput, ra.documento, ra.valueservice, ra.horaap, ra.idservice AS idservico,
                ra.qpax AS qtdpax, ra.qchild AS qtdchild, ra.qfree AS qtdfree,
                s.fullname   AS servico,
                ss.schedule,
                r.numbervoucher, r.pax, r.photoresident,
                r.totalservico, r.totalcredito,
                c.fullname   AS cliente,
                u.firstname, u.lastname,
                cfp.namepayment,
                sta.fullname AS situacao,
                'adicional'  AS origem
         FROM ct_recentlyadd ra
         LEFT JOIN ct_reserva r              ON r.id  = ra.idrecently
         LEFT JOIN ct_cliente c              ON c.id  = r.idcliente
         LEFT JOIN ct_servico s              ON s.id  = ra.idservice
         LEFT JOIN ct_service_schedule ss    ON ss.idshedule = ra.idschedule
         LEFT JOIN `ct_form_of_ payment` cfp ON cfp.id = r.idpayment
         LEFT JOIN ct_usuario u              ON u.id  = r.idresponsavel
         LEFT JOIN ct_status sta             ON sta.id = r.idstatus
         WHERE " . implode(' AND ', $whereA) . "
         ORDER BY ss.schedule, ra.dateinput"
    );
    $stmtA->execute($paramsA);
    $adicionais = $stmtA->fetchAll(PDO::FETCH_ASSOC);

    // Mescla e ordena por horário de embarque
    $todos = array_merge($reservas, $adicionais);
    usort($todos, fn($a, $b) => strcmp($a['schedule'] ?? '', $b['schedule'] ?? ''));

    return $todos;
}

$itens = $filtrado
    ? fetchMapaServico($pdo, $dateInput, $dateOutput, $idCliente, $servico, $idhorario)
    : [];

$totalPax   = array_sum(array_column($itens, 'qtdpax'));
$totalChild = array_sum(array_column($itens, 'qtdchild'));
$totalFree  = array_sum(array_column($itens, 'qtdfree'));
$totalGeral = $totalPax + $totalChild + $totalFree;
?>

<style>
    /* ── Variáveis ──────────────────────────────────────────────── */
    :root {
        --navy:   #1e4770;
        --navy-lt:#2a5f96;
        --paid:   #1a9e5c;
        --unpaid: #dc3545;
        --premium-bg: #eef4fb;
        --premium-border: #1e4770;
    }

    /* ── Layout ─────────────────────────────────────────────────── */
    .map-wrapper { padding: 24px 20px 60px; max-width: 100%; }

    /* ── Breadcrumb ─────────────────────────────────────────────── */
    .breadcrumb-bar { background: transparent; padding: 0 0 16px; }
    .breadcrumb-bar .bc-path { font-size: 13px; color: #6c757d; }
    .breadcrumb-bar .bc-path a { color: var(--navy); text-decoration: none; font-weight: 600; }
    .breadcrumb-bar .bc-path a:hover { text-decoration: underline; }
    .breadcrumb-bar .bc-path .sep { margin: 0 6px; color: #adb5bd; }

    /* ── Filter Card ────────────────────────────────────────────── */
    .filter-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 16px rgba(0,0,0,.07);
        padding: 24px 28px 20px;
        margin-bottom: 24px;
    }
    .filter-card .fc-title {
        font-size: 13px;
        font-weight: 700;
        color: var(--navy);
        text-transform: uppercase;
        letter-spacing: .06em;
        margin-bottom: 18px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .filter-card .fc-title i { font-size: 15px; }
    .filter-grid {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr 1fr;
        gap: 14px 20px;
    }
    .filter-grid .span-full { grid-column: 1 / -1; }
    .filter-grid label {
        font-size: 12px;
        font-weight: 600;
        color: #495057;
        margin-bottom: 5px;
        display: block;
        text-transform: uppercase;
        letter-spacing: .04em;
    }
    .filter-grid .form-control {
        border: 1.5px solid #dee2e6;
        border-radius: 8px;
        font-size: 14px;
        height: 38px;
        transition: border-color .2s;
    }
    .filter-grid .form-control:focus {
        border-color: var(--navy);
        box-shadow: 0 0 0 3px rgba(30,71,112,.12);
    }
    .services-select {
        height: 130px !important;
        resize: none;
    }
    .btn-filter {
        background: var(--navy);
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: 10px 32px;
        font-size: 14px;
        font-weight: 700;
        letter-spacing: .04em;
        cursor: pointer;
        transition: background .2s, transform .1s;
        width: 100%;
        height: 42px;
    }
    .btn-filter:hover { background: var(--navy-lt); }
    .btn-filter:active { transform: scale(.98); }

    /* ── KPI Cards ──────────────────────────────────────────────── */
    .kpi-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }
    .kpi-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 16px rgba(0,0,0,.07);
        padding: 18px 20px;
        display: flex;
        align-items: center;
        gap: 14px;
    }
    .kpi-icon {
        width: 44px; height: 44px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }
    .kpi-icon.pax   { background: rgba(30,71,112,.1);  color: var(--navy); }
    .kpi-icon.child { background: rgba(255,193,7,.15); color: #d68910; }
    .kpi-icon.free  { background: rgba(26,158,92,.12); color: var(--paid); }
    .kpi-icon.total { background: rgba(108,117,125,.1);color: #495057; }
    .kpi-label { font-size: 11px; color: #6c757d; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; }
    .kpi-value { font-size: 28px; font-weight: 800; color: #212529; line-height: 1; }

    /* ── Results Card ───────────────────────────────────────────── */
    .results-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 16px rgba(0,0,0,.07);
        overflow: hidden;
    }
    .results-header {
        padding: 18px 24px 14px;
        border-bottom: 1px solid #f0f0f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
    }
    .results-title {
        font-size: 15px;
        font-weight: 700;
        color: var(--navy);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .results-count {
        font-size: 12px;
        background: var(--navy);
        color: #fff;
        padding: 2px 10px;
        border-radius: 20px;
        font-weight: 600;
    }

    /* ── Tabela ─────────────────────────────────────────────────── */
    #tabelaMapa { font-size: 13px; }
    #tabelaMapa thead th {
        background: var(--navy);
        color: #fff;
        font-weight: 600;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .04em;
        border: none;
        padding: 10px 10px;
        white-space: nowrap;
    }
    #tabelaMapa tbody tr { transition: background .15s; }
    #tabelaMapa tbody tr:hover { background: #f4f8ff !important; }
    #tabelaMapa tbody td {
        padding: 8px 10px;
        vertical-align: middle;
        border-color: #f0f0f0;
    }

    /* Linha de serviço premium (categorias especiais) */
    #tabelaMapa tbody tr.premium td:first-child {
        border-left: 3px solid var(--navy);
    }
    #tabelaMapa tbody tr.premium { background: var(--premium-bg); }

    /* Linha adicional */
    #tabelaMapa tbody tr.adicional td:first-child {
        border-left: 3px solid #f0ad00;
    }

    /* ── Badges ─────────────────────────────────────────────────── */
    .badge-situacao {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 6px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
    }
    .badge-pago    { background: rgba(26,158,92,.12);  color: var(--paid); }
    .badge-pendente{ background: rgba(220,53,69,.1);   color: var(--unpaid); }
    .badge-default { background: #f0f0f0; color: #555; }

    .pax-cell    { font-size: 12px; font-weight: 700; color: #444; letter-spacing: .03em; }
    .voucher-btn {
        background: none; border: none; padding: 0;
        color: var(--navy); font-weight: 600; font-size: 12px;
        cursor: pointer; text-decoration: underline dotted;
    }
    .voucher-btn:hover { color: var(--navy-lt); }

    .valor-positivo { color: var(--unpaid); font-weight: 700; }
    .valor-pago     { color: var(--paid);   font-weight: 700; }

    .operador-cell  { font-size: 11px; color: #6c757d; text-transform: uppercase; }
    .servico-cell   { max-width: 160px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    /* ── Empty State ────────────────────────────────────────────── */
    .empty-state {
        text-align: center;
        padding: 64px 32px;
        color: #adb5bd;
    }
    .empty-state i { font-size: 48px; margin-bottom: 16px; display: block; }
    .empty-state p { font-size: 16px; margin: 0; }

    /* ── Responsivo ─────────────────────────────────────────────── */
    @media (max-width: 991px) {
        .filter-grid { grid-template-columns: 1fr 1fr; }
        .kpi-row     { grid-template-columns: 1fr 1fr; }
    }
    @media (max-width: 575px) {
        .filter-grid { grid-template-columns: 1fr; }
        .kpi-row     { grid-template-columns: 1fr 1fr; }
        .map-wrapper { padding: 16px 12px 60px; }
        .filter-card { padding: 18px 16px; }
    }
</style>

<div class="page-content--bgf7">
<div class="map-wrapper">

    <!-- Breadcrumb -->
    <div class="breadcrumb-bar">
        <span class="bc-path">
            <a href="index"><i class="fas fa-home"></i> Home</a>
            <span class="sep">/</span>
            <span>Mapa de Serviço</span>
        </span>
    </div>

    <!-- Filtros -->
    <div class="filter-card">
        <div class="fc-title">
            <i class="fas fa-filter"></i> Filtros de Busca
        </div>
        <form method="post" action="">
            <div class="filter-grid">
                <div>
                    <label for="datainicio">Data Início</label>
                    <input type="date" name="datainicio" id="datainicio" class="form-control"
                           value="<?= htmlspecialchars($dateInput) ?>" required>
                </div>
                <div>
                    <label for="datafim">Data Fim</label>
                    <input type="date" name="datafim" id="datafim" class="form-control"
                           value="<?= htmlspecialchars($dateOutput) ?>" required>
                </div>
                <div>
                    <label for="cliente">Agência / Cliente</label>
                    <select class="form-control" name="cliente" id="cliente">
                        <option value="0">Todos</option>
                        <?php foreach ($todosClientes as $c): ?>
                            <option value="<?= $c['id'] ?>"
                                <?= $c['id'] == $idCliente ? 'selected' : '' ?>>
                                <?= htmlspecialchars(utf8_encode($c['fullname'])) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="horario">Horário de Embarque</label>
                    <select class="form-control" name="horario" id="horario">
                        <option value="0">Todos</option>
                        <?php foreach ($schedules as $h): ?>
                            <option value="<?= $h['idshedule'] ?>"
                                <?= $h['idshedule'] == $idhorario ? 'selected' : '' ?>>
                                <?= htmlspecialchars($h['schedule']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="span-full">
                    <label for="servico">Serviço <small style="font-weight:400;text-transform:none;color:#999;">(segure Ctrl para selecionar múltiplos)</small></label>
                    <select class="form-control services-select" name="servico[]" id="servico" multiple>
                        <option value="0" <?= $servico[0] === 0 ? 'selected' : '' ?>>Todos os Serviços</option>
                        <?php foreach ($todosServicos as $s): ?>
                            <option value="<?= $s['id'] ?>"
                                <?= in_array($s['id'], $servico) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s['fullname']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="span-full">
                    <button type="submit" name="mapa" class="btn-filter">
                        <i class="fas fa-search"></i> &nbsp;Visualizar Mapa
                    </button>
                </div>
            </div>
        </form>
    </div>

    <?php if ($filtrado): ?>

    <!-- KPIs -->
    <div class="kpi-row">
        <div class="kpi-card">
            <div class="kpi-icon pax"><i class="fas fa-users"></i></div>
            <div>
                <div class="kpi-label">Adultos</div>
                <div class="kpi-value"><?= $totalPax ?></div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon child"><i class="fas fa-child"></i></div>
            <div>
                <div class="kpi-label">Crianças</div>
                <div class="kpi-value"><?= $totalChild ?></div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon free"><i class="fas fa-star"></i></div>
            <div>
                <div class="kpi-label">Gratuitos</div>
                <div class="kpi-value"><?= $totalFree ?></div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon total"><i class="fas fa-user-friends"></i></div>
            <div>
                <div class="kpi-label">Total Pax</div>
                <div class="kpi-value"><?= $totalGeral ?></div>
            </div>
        </div>
    </div>

    <!-- Tabela de Resultados -->
    <div class="results-card">
        <div class="results-header">
            <div class="results-title">
                <i class="fas fa-street-view"></i>
                Mapa de Serviço
                <span class="results-count"><?= count($itens) ?> registros</span>
            </div>
            <div style="font-size:13px;color:#6c757d;">
                <?= date(DATE_BR, strtotime($dateInput)) ?>
                <?= $dateInput !== $dateOutput ? ' — ' . date(DATE_BR, strtotime($dateOutput)) : '' ?>
            </div>
        </div>

        <?php if (count($itens) > 0): ?>
        <div class="table-responsive">
            <table id="tabelaMapa" class="table table-bordered table-hover dataTable" style="width:100%">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>P&nbsp;|&nbsp;C&nbsp;|&nbsp;F</th>
                        <th>Voucher / Passageiro</th>
                        <th>Serviço</th>
                        <th>Apanha</th>
                        <th>Embarque</th>
                        <th>Complemento</th>
                        <th>T. Serviço</th>
                        <th>T. Reserva</th>
                        <th>T. Pago</th>
                        <th>A Pagar</th>
                        <th>Operador</th>
                        <th>Agência</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($itens as $item):
                    $id       = (int) $item['idservico'];
                    $premium  = isServicoPremium($id);
                    $adicional = $item['origem'] === 'adicional';
                    $tCalc    = calcTotal((float)$item['valueservice'], (int)$item['qtdpax'], (int)$item['qtdchild']);
                    $saldo    = (float)$item['totalservico'] - (float)$item['totalcredito'];
                    $situacao = $item['situacao'] ?? '';
                    $rowClass = $premium  ? 'premium'  : '';
                    $rowClass.= $adicional ? ' adicional' : '';
                ?>
                    <tr class="<?= trim($rowClass) ?>">
                        <td><?= date(DATE_BR, strtotime($item['dateinput'])) ?></td>
                        <td class="pax-cell">
                            <?= (int)$item['qtdpax'] ?>&nbsp;|&nbsp;<?= (int)$item['qtdchild'] ?>&nbsp;|&nbsp;<?= (int)$item['qtdfree'] ?>
                        </td>
                        <td>
                            <form method="post" action="./editar-pax" target="_blank" style="margin:0">
                                <input type="hidden" name="numbervoucher"
                                       value="<?= htmlspecialchars($item['numbervoucher']) ?>">
                                <button type="submit" class="voucher-btn" title="Abrir reserva">
                                    <?= htmlspecialchars($item['numbervoucher']) ?>
                                </button>
                            </form>
                            <div style="font-size:11px;color:#555;margin-top:2px;">
                                <?= htmlspecialchars(utf8_encode($item['pax'] ?? '')) ?>
                                <?php if (!empty($item['photoresident'])): ?>
                                    &nbsp;<span style="color:#999;">· <?= htmlspecialchars($item['photoresident']) ?></span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="servico-cell" title="<?= htmlspecialchars(utf8_encode($item['servico'] ?? '')) ?>">
                            <?= htmlspecialchars(utf8_encode($item['servico'] ?? '')) ?>
                        </td>
                        <td><?= !empty($item['horaap'])  ? date('H:i', strtotime($item['horaap']))  : '—' ?></td>
                        <td><?= !empty($item['schedule']) ? date('H:i', strtotime($item['schedule'])) : '—' ?></td>
                        <td style="font-size:12px;"><?= htmlspecialchars(utf8_encode($item['documento'] ?? '')) ?></td>
                        <td><?= formatBRL($tCalc) ?></td>
                        <td><?= formatBRL((float)$item['totalservico']) ?></td>
                        <td><?= formatBRL((float)$item['totalcredito']) ?></td>
                        <td>
                            <?php if ($saldo > 0): ?>
                                <span class="valor-positivo"><?= formatBRL($saldo) ?></span>
                            <?php elseif ($saldo < 0): ?>
                                <span class="valor-pago"><?= formatBRL(abs($saldo)) ?> <small>crédito</small></span>
                            <?php else: ?>
                                <span class="valor-pago">Quitado</span>
                            <?php endif; ?>
                        </td>
                        <td class="operador-cell">
                            <?= htmlspecialchars(utf8_encode(trim(($item['firstname'] ?? '') . ' ' . ($item['lastname'] ?? '')))) ?>
                        </td>
                        <td><?= htmlspecialchars(utf8_encode($item['cliente'] ?? '')) ?></td>
                        <td>
                            <?php
                                $sit = strtolower($situacao);
                                if (str_contains($sit, 'pago') || str_contains($sit, 'confirm')) {
                                    $badgeClass = 'badge-pago';
                                } elseif (str_contains($sit, 'cancel') || str_contains($sit, 'pendente')) {
                                    $badgeClass = 'badge-pendente';
                                } else {
                                    $badgeClass = 'badge-default';
                                }
                            ?>
                            <span class="badge-situacao <?= $badgeClass ?>">
                                <?= htmlspecialchars(utf8_encode($situacao)) ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-calendar-times"></i>
            <p>Nenhuma reserva encontrada para os filtros selecionados.</p>
        </div>
        <?php endif; ?>
    </div>

    <?php endif; ?>

</div>
</div>

<script>
    // Persiste totais para uso em outros módulos
    localStorage.setItem('totalpax',   <?= $totalPax ?>);
    localStorage.setItem('totalchild', <?= $totalChild ?>);
    localStorage.setItem('totalfree',  <?= $totalFree ?>);

    // Inicializa DataTable com suporte a export
    $(document).ready(function () {
        if ($('#tabelaMapa').length) {
            $('#tabelaMapa').DataTable({
                dom: '<"d-flex justify-content-between align-items-center mb-2"Bf>rtip',
                buttons: [
                    { extend: 'excelHtml5', text: '<i class="fas fa-file-excel"></i> Excel', className: 'btn btn-sm btn-success mr-1' },
                    { extend: 'print',      text: '<i class="fas fa-print"></i> Imprimir',   className: 'btn btn-sm btn-secondary mr-1' },
                ],
                pageLength: 50,
                language: {
                    search:      'Buscar:',
                    lengthMenu:  'Exibir _MENU_ registros',
                    info:        'Mostrando _START_ a _END_ de _TOTAL_',
                    paginate:    { first: 'Início', last: 'Fim', next: '›', previous: '‹' },
                    zeroRecords: 'Nenhum registro encontrado',
                },
                order: [[0, 'asc'], [4, 'asc']],
                columnDefs: [{ orderable: false, targets: [2] }],
            });
        }
    });
</script>

<?php require_once 'footer.php'; ?>
