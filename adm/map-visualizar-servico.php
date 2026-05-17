<?php
require_once 'header.php';
require_once __DIR__ . '/includes/ref_cache.php';

const DATE_BR   = 'd/m/Y';
const DATE_ISO  = 'Y-m-d';

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

// ── Formulário e navegação de dia ─────────────────────────────────────────

if (isset($_POST['mapa'])) {
    $dateInput  = $_POST['datainicio'];
    $dateOutput = $_POST['datafim'];
    $idCliente  = (int) $_POST['cliente'];
    $servico    = array_map('intval', (array) $_POST['servico']);
    $idhorario  = (int) $_POST['horario'];
    $_SESSION['atual']      = $dateInput;
    $_SESSION['datainicio'] = $dateInput;
    $_SESSION['datafim']    = $dateOutput;
    $_SESSION['cliente']    = $idCliente;
    $_SESSION['servico']    = $servico;
    $_SESSION['horario']    = $idhorario;
    $filtrado = true;

} elseif (isset($_POST['mapaseguinte'])) {
    $proximo = $_POST['seguinte'];
    $_SESSION['atual']      = $proximo;
    $_SESSION['datainicio'] = $proximo;
    $_SESSION['datafim']    = $proximo;
    $_SESSION['cliente']    = (int) $_POST['cliente'];
    $_SESSION['servico']    = array_map('intval', (array) $_POST['servico']);
    $_SESSION['horario']    = (int) $_POST['horario'];
    $dateInput  = $proximo;
    $dateOutput = $proximo;
    $idCliente  = $_SESSION['cliente'];
    $servico    = $_SESSION['servico'];
    $idhorario  = $_SESSION['horario'];
    $filtrado   = true;

} elseif (isset($_POST['mapaanterior'])) {
    $anterior = date(DATE_ISO, strtotime('-1 day', strtotime($_SESSION['atual'] ?? date(DATE_ISO))));
    $_SESSION['atual']      = $anterior;
    $_SESSION['datainicio'] = $anterior;
    $_SESSION['datafim']    = $anterior;
    $_SESSION['cliente']    = (int) $_POST['cliente'];
    $_SESSION['servico']    = array_map('intval', (array) $_POST['servico']);
    $_SESSION['horario']    = (int) $_POST['horario'];
    $dateInput  = $anterior;
    $dateOutput = $anterior;
    $idCliente  = $_SESSION['cliente'];
    $servico    = $_SESSION['servico'];
    $idhorario  = $_SESSION['horario'];
    $filtrado   = true;

} else {
    $hoje       = date(DATE_ISO);
    $_SESSION['atual'] = $_SESSION['atual'] ?? $hoje;
    $dateInput  = $_SESSION['datainicio'] ?? $hoje;
    $dateOutput = $_SESSION['datafim']    ?? $hoje;
    $idCliente  = (int) ($_SESSION['cliente'] ?? 0);
    $servico    = (array) ($_SESSION['servico'] ?? [0]);
    $idhorario  = (int) ($_SESSION['horario']  ?? 0);
    $filtrado   = isset($_SESSION['datainicio']);
}

$diaAtual    = $_SESSION['atual'];
$diaSeguinte = date(DATE_ISO, strtotime('+1 day', strtotime($diaAtual)));
$diaAnterior = date(DATE_ISO, strtotime('-1 day', strtotime($diaAtual)));

// ── Dados de referência ──────────────────────────────────────────────────

$todosClientes = refClientes($pdo);
$todosServicos = refServicosOrdem($pdo);
$schedules     = $pdo->query("SELECT idshedule, schedule FROM ct_service_schedule
                               WHERE schedule NOT LIKE '00:00%' ORDER BY schedule")
                     ->fetchAll(PDO::FETCH_ASSOC);

// ── Query builder ─────────────────────────────────────────────────────────
// Substitui os 8 blocos de query condicional por 2 queries com WHERE dinâmico.

function fetchMapaServico(PDO $pdo, string $dateIn, string $dateOut,
                          int $idCliente, array $servicos, int $idhorario): array
{
    $excluidos  = '19,30,47,48';
    $todosServ  = ($servicos[0] === 0 && count($servicos) === 1);
    $servicosIn = $todosServ ? '' : implode(',', array_map('intval', $servicos));

    // ── Reservas principais ─────────────────────────────────────────────
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
                r.fl_altarar_valor_servico,
                c.fullname   AS cliente,
                se.fullname  AS servico,
                ss.schedule,
                u.firstname, u.lastname,
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

    // ── Serviços adicionais ─────────────────────────────────────────────
    $whereA  = ["ra.dateinput >= :inn", "ra.dateinput <= :outt",
                "r.idstatus <> 2", "ra.idservice NOT IN ($excluidos)"];
    $paramsA = [':inn' => $dateIn, ':outt' => $dateOut];

    if ($idCliente > 0) { $whereA[] = 'r.idcliente = :cliente';    $paramsA[':cliente']   = $idCliente; }
    if ($idhorario > 0) { $whereA[] = 'ra.idschedule = :idhorario'; $paramsA[':idhorario'] = $idhorario; }
    if (!$todosServ)    { $whereA[] = "ra.idservice IN ($servicosIn)"; }

    $stmtA = $pdo->prepare(
        "SELECT ra.dateinput, ra.documento, ra.valueservice, ra.horaap,
                ra.idservice AS idservico, ra.fl_altarar_valor_servico,
                ra.qpax AS qtdpax, ra.qchild AS qtdchild, ra.qfree AS qtdfree,
                s.fullname   AS servico,
                ss.schedule,
                r.numbervoucher, r.pax, r.photoresident,
                r.totalservico, r.totalcredito,
                c.fullname   AS cliente,
                u.firstname, u.lastname,
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
    :root {
        --navy:    #1e4770;
        --navy-lt: #2a5f96;
        --paid:    #1a9e5c;
        --unpaid:  #dc3545;
    }

    .map-wrapper { padding: 20px 20px 80px; }

    /* Breadcrumb */
    .bc-bar { padding: 0 0 16px; font-size: 13px; color: #6c757d; }
    .bc-bar a { color: var(--navy); font-weight: 600; text-decoration: none; }
    .bc-bar a:hover { text-decoration: underline; }
    .bc-bar .sep { margin: 0 6px; color: #ccc; }

    /* Filter card */
    .filter-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 14px rgba(0,0,0,.07);
        padding: 22px 26px 18px;
        margin-bottom: 20px;
    }
    .fc-title {
        font-size: 12px; font-weight: 700; color: var(--navy);
        text-transform: uppercase; letter-spacing: .06em;
        margin-bottom: 16px; display: flex; align-items: center; gap: 7px;
    }
    .filter-grid {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr 1fr;
        gap: 12px 18px;
    }
    .filter-grid .span-full { grid-column: 1 / -1; }
    .filter-grid label {
        font-size: 11px; font-weight: 700; color: #6c757d;
        text-transform: uppercase; letter-spacing: .05em;
        display: block; margin-bottom: 5px;
    }
    .filter-grid .form-control {
        border: 1.5px solid #dee2e6; border-radius: 8px;
        font-size: 13px; height: 36px; transition: border-color .2s;
    }
    .filter-grid .form-control:focus {
        border-color: var(--navy); box-shadow: 0 0 0 3px rgba(30,71,112,.12);
    }
    .services-select { height: 120px !important; }

    /* Nav + submit buttons row */
    .nav-btn-row {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 10px;
        margin-top: 14px;
    }
    .btn-nav {
        border: 2px solid var(--navy); background: transparent;
        color: var(--navy); border-radius: 8px; padding: 9px 16px;
        font-size: 13px; font-weight: 700; cursor: pointer;
        transition: all .2s; display: flex; align-items: center;
        justify-content: center; gap: 6px;
    }
    .btn-nav:hover { background: var(--navy); color: #fff; }
    .btn-nav.primary { background: var(--navy); color: #fff; }
    .btn-nav.primary:hover { background: var(--navy-lt); }

    /* KPI cards */
    .kpi-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 14px;
        margin-bottom: 20px;
    }
    .kpi-card {
        background: #fff; border-radius: 12px;
        box-shadow: 0 2px 14px rgba(0,0,0,.07);
        padding: 16px 18px; display: flex; align-items: center; gap: 12px;
    }
    .kpi-icon {
        width: 42px; height: 42px; border-radius: 10px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; font-size: 17px;
    }
    .kpi-icon.pax   { background: rgba(30,71,112,.1);  color: var(--navy); }
    .kpi-icon.child { background: rgba(255,193,7,.18); color: #c07c00; }
    .kpi-icon.free  { background: rgba(26,158,92,.12); color: var(--paid); }
    .kpi-icon.total { background: rgba(108,117,125,.1);color: #555; }
    .kpi-label { font-size: 10px; font-weight: 700; color: #999; text-transform: uppercase; letter-spacing: .05em; }
    .kpi-value { font-size: 26px; font-weight: 800; color: #212529; line-height: 1.1; }

    /* Results card */
    .results-card {
        background: #fff; border-radius: 12px;
        box-shadow: 0 2px 14px rgba(0,0,0,.07); overflow: hidden;
    }
    .results-header {
        padding: 16px 22px 12px;
        border-bottom: 1px solid #f0f0f0;
        display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;
    }
    .results-title {
        font-size: 14px; font-weight: 700; color: var(--navy);
        display: flex; align-items: center; gap: 8px;
    }
    .results-count {
        font-size: 11px; background: var(--navy); color: #fff;
        padding: 2px 9px; border-radius: 20px; font-weight: 600;
    }
    .period-label { font-size: 12px; color: #6c757d; }

    /* Tabela */
    #tabelaMapa { font-size: 12.5px; }
    #tabelaMapa thead th {
        background: var(--navy); color: #fff; font-weight: 600;
        font-size: 11px; text-transform: uppercase; letter-spacing: .04em;
        border: none; padding: 9px 8px; white-space: nowrap;
    }
    #tabelaMapa tbody tr { transition: background .12s; }
    #tabelaMapa tbody tr:hover td { background: #f0f6ff !important; color: #000 !important; }
    #tabelaMapa tbody td { padding: 7px 8px; vertical-align: middle; border-color: #f0f0f0; }

    /* Linha premium (serviços especiais) */
    #tabelaMapa tbody tr.premium td { background: #eef4fb; }
    #tabelaMapa tbody tr.premium td:first-child { border-left: 3px solid var(--navy); }

    /* Linha adicional */
    #tabelaMapa tbody tr.adicional td:first-child { border-left: 3px solid #f0ad00; }

    /* Valor alterado */
    .valor-alterado { background: var(--unpaid) !important; color: #fff !important; font-weight: 700; }

    /* Badges status */
    .badge-sit {
        display: inline-block; padding: 2px 7px; border-radius: 5px;
        font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .03em;
    }
    .badge-pago     { background: rgba(26,158,92,.12);  color: var(--paid); }
    .badge-pendente { background: rgba(220,53,69,.1);   color: var(--unpaid); }
    .badge-default  { background: #f0f0f0; color: #555; }

    /* Células */
    .pax-cell    { font-weight: 700; color: #333; white-space: nowrap; }
    .voucher-btn {
        background: none; border: none; padding: 0; color: var(--navy);
        font-weight: 600; font-size: 12px; cursor: pointer; text-decoration: underline dotted;
    }
    .voucher-btn:hover { color: var(--navy-lt); }
    .pax-name    { font-size: 11px; color: #888; margin-top: 2px; }
    .servico-cell { max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .operador-cell { font-size: 11px; color: #777; }
    .valor-pos { color: var(--unpaid); font-weight: 700; }
    .valor-pago{ color: var(--paid);   font-weight: 700; }

    /* Empty state */
    .empty-state { text-align: center; padding: 56px 24px; color: #bbb; }
    .empty-state i { font-size: 44px; margin-bottom: 12px; display: block; }

    /* Responsivo */
    @media (max-width: 991px) {
        .filter-grid { grid-template-columns: 1fr 1fr; }
        .kpi-row     { grid-template-columns: 1fr 1fr; }
    }
    @media (max-width: 575px) {
        .filter-grid { grid-template-columns: 1fr; }
        .kpi-row     { grid-template-columns: 1fr 1fr; }
        .nav-btn-row { grid-template-columns: 1fr; }
        .map-wrapper { padding: 14px 12px 80px; }
    }
</style>

<div class="page-content--bgf7">
<div class="map-wrapper">

    <!-- Breadcrumb -->
    <div class="bc-bar">
        <a href="index"><i class="fas fa-home"></i> Home</a>
        <span class="sep">/</span>
        <span>Mapa de Serviço</span>
    </div>

    <!-- Filtros -->
    <div class="filter-card">
        <div class="fc-title"><i class="fas fa-filter"></i> Filtros</div>
        <form method="post" action="">
            <div class="filter-grid">
                <div>
                    <label for="datainicio">Data Início</label>
                    <input type="date" name="datainicio" id="datainicio"
                           class="form-control" value="<?= htmlspecialchars($dateInput) ?>" required>
                </div>
                <div>
                    <label for="datafim">Data Fim</label>
                    <input type="date" name="datafim" id="datafim"
                           class="form-control" value="<?= htmlspecialchars($dateOutput) ?>" required>
                </div>
                <div>
                    <label for="cliente">Agência / Cliente</label>
                    <select class="form-control" name="cliente" id="cliente">
                        <option value="0">Todos</option>
                        <?php foreach ($todosClientes as $c): ?>
                            <option value="<?= $c->id ?>"
                                <?= $c->id == $idCliente ? 'selected' : '' ?>>
                                <?= htmlspecialchars(utf8_encode($c->fullname)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="horario">Horário</label>
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
                    <label for="servico">Serviço <small style="font-weight:400;text-transform:none;color:#aaa;">(Ctrl para múltiplos)</small></label>
                    <select class="form-control services-select" name="servico[]" id="servico" multiple>
                        <option value="0" <?= $servico[0] === 0 ? 'selected' : '' ?>>Todos os Serviços</option>
                        <?php foreach ($todosServicos as $s): ?>
                            <option value="<?= $s->id ?>"
                                <?= in_array($s->id, $servico) ? 'selected' : '' ?>>
                                <?= htmlspecialchars(utf8_encode($s->fullname)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Campos ocultos para navegação de dia -->
            <input type="hidden" name="seguinte" value="<?= $diaSeguinte ?>">
            <input type="hidden" name="anterior" value="<?= $diaAnterior ?>">

            <!-- Botões de ação -->
            <div class="nav-btn-row">
                <button type="submit" name="mapaanterior" class="btn-nav">
                    <i class="fas fa-chevron-left"></i> Dia Anterior
                </button>
                <button type="submit" name="mapa" class="btn-nav primary">
                    <i class="fas fa-search"></i> Selecionar Mapa
                </button>
                <button type="submit" name="mapaseguinte" class="btn-nav">
                    Dia Seguinte <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </form>
    </div>

    <?php if ($filtrado): ?>

    <!-- KPIs -->
    <div class="kpi-row">
        <div class="kpi-card">
            <div class="kpi-icon pax"><i class="fas fa-users"></i></div>
            <div><div class="kpi-label">Adultos</div><div class="kpi-value"><?= $totalPax ?></div></div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon child"><i class="fas fa-child"></i></div>
            <div><div class="kpi-label">Crianças</div><div class="kpi-value"><?= $totalChild ?></div></div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon free"><i class="fas fa-star"></i></div>
            <div><div class="kpi-label">Gratuitos</div><div class="kpi-value"><?= $totalFree ?></div></div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon total"><i class="fas fa-user-friends"></i></div>
            <div><div class="kpi-label">Total Pax</div><div class="kpi-value"><?= $totalGeral ?></div></div>
        </div>
    </div>

    <!-- Tabela -->
    <div class="results-card">
        <div class="results-header">
            <div class="results-title">
                <i class="fas fa-street-view"></i> Mapa de Serviço
                <span class="results-count"><?= count($itens) ?> registros</span>
            </div>
            <div class="period-label">
                <?= date(DATE_BR, strtotime($dateInput)) ?>
                <?php if ($dateInput !== $dateOutput): ?>
                    &mdash; <?= date(DATE_BR, strtotime($dateOutput)) ?>
                <?php endif; ?>
            </div>
        </div>

        <?php if (count($itens) > 0): ?>
        <div class="table-responsive">
            <table id="tabelaMapa" class="table table-bordered dataTable" style="width:100%">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>P&nbsp;|&nbsp;C&nbsp;|&nbsp;F</th>
                        <th>Voucher / Passageiro</th>
                        <th>Serviço</th>
                        <th>Apanha</th>
                        <th>Embarque</th>
                        <th>Complemento</th>
                        <th>T.&nbsp;Serviço</th>
                        <th>T.&nbsp;Reserva</th>
                        <th>T.&nbsp;Pago</th>
                        <th>A&nbsp;Pagar</th>
                        <th>Operador</th>
                        <th>Agência</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($itens as $item):
                    $id        = (int) $item['idservico'];
                    $premium   = isServicoPremium($id);
                    $adicional = $item['origem'] === 'adicional';
                    $tCalc     = calcTotal((float)$item['valueservice'], (int)$item['qtdpax'], (int)$item['qtdchild']);
                    $saldo     = (float)$item['totalservico'] - (float)$item['totalcredito'];
                    $situacao  = $item['situacao'] ?? '';
                    $alterado  = !empty($item['fl_altarar_valor_servico']);
                    $rowClass  = trim(($premium ? 'premium' : '') . ($adicional ? ' adicional' : ''));

                    $sit = strtolower($situacao);
                    if (str_contains($sit, 'pago') || str_contains($sit, 'confirm')) {
                        $badgeClass = 'badge-pago';
                    } elseif (str_contains($sit, 'cancel') || str_contains($sit, 'pendente')) {
                        $badgeClass = 'badge-pendente';
                    } else {
                        $badgeClass = 'badge-default';
                    }
                ?>
                <tr class="<?= $rowClass ?>">
                    <td><?= date(DATE_BR, strtotime($item['dateinput'])) ?></td>
                    <td class="pax-cell"><?= (int)$item['qtdpax'] ?>&nbsp;|&nbsp;<?= (int)$item['qtdchild'] ?>&nbsp;|&nbsp;<?= (int)$item['qtdfree'] ?></td>
                    <td>
                        <form method="post" action="./editar-pax" target="_blank" style="margin:0">
                            <input type="hidden" name="numbervoucher" value="<?= htmlspecialchars($item['numbervoucher']) ?>">
                            <button type="submit" class="voucher-btn"><?= htmlspecialchars($item['numbervoucher']) ?></button>
                        </form>
                        <div class="pax-name"><?= htmlspecialchars(utf8_encode($item['pax'] ?? '')) ?>
                            <?php if (!empty($item['photoresident'])): ?>
                                &middot; <span style="color:#bbb;"><?= htmlspecialchars($item['photoresident']) ?></span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="servico-cell" title="<?= htmlspecialchars(utf8_encode($item['servico'] ?? '')) ?>">
                        <?= htmlspecialchars(utf8_encode($item['servico'] ?? '')) ?>
                    </td>
                    <td><?= !empty($item['horaap'])   ? date('H:i', strtotime($item['horaap']))   : '—' ?></td>
                    <td><?= !empty($item['schedule'])  ? date('H:i', strtotime($item['schedule'])) : '—' ?></td>
                    <td style="font-size:11px;"><?= html_entity_decode($item['documento'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="<?= $alterado ? 'valor-alterado' : '' ?>"><?= formatBRL($tCalc) ?></td>
                    <td><?= formatBRL((float)$item['totalservico']) ?></td>
                    <td><?= formatBRL((float)$item['totalcredito']) ?></td>
                    <td>
                        <?php if ($saldo > 0): ?>
                            <span class="valor-pos"><?= formatBRL($saldo) ?></span>
                        <?php elseif ($saldo < 0): ?>
                            <span class="valor-pago"><?= formatBRL(abs($saldo)) ?> <small>cred.</small></span>
                        <?php else: ?>
                            <span class="valor-pago">Quitado</span>
                        <?php endif; ?>
                    </td>
                    <td class="operador-cell"><?= htmlspecialchars(strtoupper(utf8_encode(trim(($item['firstname'] ?? '') . ' ' . ($item['lastname'] ?? ''))))) ?></td>
                    <td><?= htmlspecialchars(strtoupper(utf8_encode($item['cliente'] ?? ''))) ?></td>
                    <td><span class="badge-sit <?= $badgeClass ?>"><?= htmlspecialchars(utf8_encode($situacao)) ?></span></td>
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
    localStorage.setItem('totalpax',   <?= $totalPax ?>);
    localStorage.setItem('totalchild', <?= $totalChild ?>);
    localStorage.setItem('totalfree',  <?= $totalFree ?>);

    $(document).ready(function () {
        if ($('#tabelaMapa').length) {
            $('#tabelaMapa').DataTable({
                dom: '<"d-flex justify-content-between align-items-center mb-2"Bf>rtip',
                buttons: [
                    { extend: 'excelHtml5', text: '<i class="fas fa-file-excel"></i> Excel',     className: 'btn btn-sm btn-success mr-1' },
                    { extend: 'csvHtml5',   text: '<i class="fas fa-file-csv"></i> CSV',         className: 'btn btn-sm btn-secondary mr-1' },
                    { extend: 'print',      text: '<i class="fas fa-print"></i> Imprimir',       className: 'btn btn-sm btn-dark mr-1' },
                ],
                pageLength: 50,
                language: {
                    search:      'Buscar:',
                    lengthMenu:  'Exibir _MENU_ por página',
                    info:        '_START_–_END_ de _TOTAL_',
                    paginate:    { first: '«', last: '»', next: '›', previous: '‹' },
                    zeroRecords: 'Nenhum registro encontrado',
                    infoEmpty:   'Sem registros',
                },
                order: [[4, 'asc'], [0, 'asc']],
                columnDefs: [{ orderable: false, targets: [2] }],
            });
        }
    });
</script>

<?php require_once 'footer.php'; ?>
