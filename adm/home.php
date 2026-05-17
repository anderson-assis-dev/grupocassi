<?php
require_once 'header.php';

// ── KPIs ──────────────────────────────────────────────────────────────────────
$embarquesHoje = (int) $pdo->query(
    "SELECT COUNT(*) FROM ct_reserva
     WHERE dateinput = CURDATE() AND idstatus NOT IN (2,13)"
)->fetchColumn();

$vouchersMes = (int) $pdo->query(
    "SELECT COUNT(*) FROM ct_reserva
     WHERE YEAR(abertura)=YEAR(NOW()) AND MONTH(abertura)=MONTH(NOW())"
)->fetchColumn();

$receitaMes = (float) $pdo->query(
    "SELECT COALESCE(SUM(valuecredit),0) FROM ct_createfaturacredit
     WHERE YEAR(datacredit)=YEAR(NOW()) AND MONTH(datacredit)=MONTH(NOW())"
)->fetchColumn();

$saldoDevedor = (float) $pdo->query(
    "SELECT COALESCE(SUM(totalservico - totalcredito),0) FROM ct_reserva
     WHERE idstatus NOT IN (2,13) AND totalservico > totalcredito"
)->fetchColumn();

// ── Receita últimos 7 dias ────────────────────────────────────────────────────
$receita7raw = $pdo->query(
    "SELECT DATE(datacredit) AS dia, SUM(valuecredit) AS total
     FROM ct_createfaturacredit
     WHERE datacredit >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
     GROUP BY DATE(datacredit)"
)->fetchAll(PDO::FETCH_KEY_PAIR);

$receita7Labels = [];
$receita7Data   = [];
for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-{$i} days"));
    $receita7Labels[] = date('d/m', strtotime($d));
    $receita7Data[]   = (float)($receita7raw[$d] ?? 0);
}

// ── Vouchers por status (mês) ─────────────────────────────────────────────────
$statusRows = $pdo->query(
    "SELECT COALESCE(si.nameinvoice,'Sem status') AS label, COUNT(r.id) AS total
     FROM ct_reserva r
     LEFT JOIN ct_statusinvoice si ON si.id = r.idstatusinvoice
     WHERE YEAR(r.abertura)=YEAR(NOW()) AND MONTH(r.abertura)=MONTH(NOW())
     GROUP BY r.idstatusinvoice, si.nameinvoice
     ORDER BY total DESC"
)->fetchAll(PDO::FETCH_ASSOC);

// ── Top serviços (mês) ────────────────────────────────────────────────────────
$servicosRows = $pdo->query(
    "SELECT COALESCE(s.fullname,'Outros') AS label, COUNT(*) AS total
     FROM ct_reserva r
     LEFT JOIN ct_servico s ON s.id = r.idservico
     WHERE YEAR(r.abertura)=YEAR(NOW()) AND MONTH(r.abertura)=MONTH(NOW())
       AND r.idstatus != 2
     GROUP BY r.idservico, s.fullname
     ORDER BY total DESC
     LIMIT 8"
)->fetchAll(PDO::FETCH_ASSOC);

// ── Próximos embarques (7 dias) ───────────────────────────────────────────────
$proximosEmbarques = $pdo->query(
    "SELECT r.numbervoucher, r.pax, s.fullname AS servico, r.dateinput,
            r.qtdpax, r.qtdchild, r.qtdfree, u.firstname, u.lastname
     FROM ct_reserva r
     LEFT JOIN ct_servico s ON s.id = r.idservico
     LEFT JOIN ct_usuario u ON u.id = r.idresponsavel
     WHERE r.dateinput >= CURDATE()
       AND r.dateinput <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
       AND r.idstatus NOT IN (2,13)
     ORDER BY r.dateinput, r.numbervoucher
     LIMIT 30"
)->fetchAll(PDO::FETCH_ASSOC);

// ── Prepare chart JSON ────────────────────────────────────────────────────────
$chartReceita7Labels  = json_encode($receita7Labels);
$chartReceita7Data    = json_encode($receita7Data);
$chartStatusLabels    = json_encode(array_column($statusRows,  'label'));
$chartStatusData      = json_encode(array_column($statusRows,  'total'));
$chartServicosLabels  = json_encode(array_column($servicosRows,'label'));
$chartServicosData    = json_encode(array_column($servicosRows,'total'));

$meses     = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho',
              'Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
$mesAtual  = $meses[(int)date('m') - 1];
$anoAtual  = date('Y');
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js" crossorigin="anonymous"></script>
<style>
    .dash-page { background:#f4f7fb; min-height:calc(100vh - 70px); padding:28px 0 52px; }
    .dash-page .containerrrr { width:96%; max-width:1300px; margin:0 auto; }

    /* greeting */
    .dash-greeting { margin-bottom:28px; }
    .dash-greeting h2 { font-size:26px; font-weight:800; color:#1e293b; margin:0 0 2px; }
    .dash-greeting p  { font-size:14px; color:#64748b; margin:0; }

    /* KPI cards */
    .kpi-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:18px; margin-bottom:24px; }
    @media(max-width:900px){ .kpi-grid{ grid-template-columns:repeat(2,1fr); } }
    @media(max-width:520px){ .kpi-grid{ grid-template-columns:1fr; } }

    .kpi-card { border-radius:18px; padding:22px 22px 18px; position:relative; overflow:hidden;
        box-shadow:0 12px 32px rgba(15,23,42,.08); color:#fff; }
    .kpi-card .kpi-label { font-size:12px; font-weight:700; text-transform:uppercase;
        letter-spacing:.6px; opacity:.85; margin-bottom:10px; }
    .kpi-card .kpi-value { font-size:32px; font-weight:900; line-height:1; margin-bottom:6px; }
    .kpi-card .kpi-sub   { font-size:12px; opacity:.75; }
    .kpi-card .kpi-icon  { position:absolute; right:18px; top:18px; opacity:.18;
        width:52px; height:52px; }
    .kpi-card.blue   { background:linear-gradient(135deg,#1e4770,#2563eb); }
    .kpi-card.green  { background:linear-gradient(135deg,#065f46,#059669); }
    .kpi-card.purple { background:linear-gradient(135deg,#4c1d95,#7c3aed); }
    .kpi-card.amber  { background:linear-gradient(135deg,#92400e,#d97706); }

    /* chart cards */
    .chart-grid { display:grid; grid-template-columns:1fr 1fr; gap:18px; margin-bottom:24px; }
    .chart-grid-3 { display:grid; grid-template-columns:2fr 1fr; gap:18px; margin-bottom:24px; }
    @media(max-width:860px){ .chart-grid,.chart-grid-3{ grid-template-columns:1fr; } }

    .dash-card { background:#fff; border-radius:20px; box-shadow:0 12px 32px rgba(15,23,42,.07);
        overflow:hidden; }
    .dash-card-head { padding:18px 22px 0; border-bottom:0; }
    .dash-card-head h5 { font-size:14px; font-weight:800; color:#1e293b; text-transform:uppercase;
        letter-spacing:.5px; margin:0 0 2px; }
    .dash-card-head small { font-size:12px; color:#94a3b8; }
    .dash-card-body { padding:18px 22px 22px; }

    /* table */
    .dash-table { width:100%; border-collapse:collapse; font-size:13px; }
    .dash-table th { background:#f8fafc; color:#64748b; font-size:11px; text-transform:uppercase;
        letter-spacing:.5px; padding:10px 14px; border-bottom:2px solid #e5e7eb; text-align:left;
        white-space:nowrap; }
    .dash-table td { padding:10px 14px; border-bottom:1px solid #f1f5f9; vertical-align:middle; }
    .dash-table tr:last-child td { border-bottom:0; }
    .dash-table tr:hover td { background:#f8fafc; }
    .badge-today { background:#fef9c3; color:#92400e; font-size:11px; font-weight:700;
        padding:2px 8px; border-radius:999px; }
    .badge-future { background:#eff6ff; color:#1d4ed8; font-size:11px; font-weight:700;
        padding:2px 8px; border-radius:999px; }
    .pax-count { display:inline-flex; align-items:center; gap:6px; font-size:12px; color:#475569; }
    .pax-count span { background:#f1f5f9; border-radius:6px; padding:1px 7px; }
</style>

<div class="dash-page">
<div class="containerrrr">

    <!-- greeting -->
    <div class="dash-greeting">
        <h2>Olá, <?= htmlspecialchars(ucwords(strtolower($_SESSION['nome'] ?? 'Usuário'))) ?></h2>
        <p>Bem-vindo ao painel Cassi Turismo &mdash; <?= $mesAtual . ' ' . $anoAtual ?></p>
    </div>

    <!-- KPIs -->
    <div class="kpi-grid">
        <div class="kpi-card blue">
            <div class="kpi-label">Embarques Hoje</div>
            <div class="kpi-value"><?= $embarquesHoje ?></div>
            <div class="kpi-sub">serviços com saída hoje</div>
            <svg class="kpi-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
        <div class="kpi-card green">
            <div class="kpi-label">Vouchers no Mês</div>
            <div class="kpi-value"><?= $vouchersMes ?></div>
            <div class="kpi-sub"><?= $mesAtual ?> <?= $anoAtual ?></div>
            <svg class="kpi-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </div>
        <div class="kpi-card purple">
            <div class="kpi-label">Receita no Mês</div>
            <div class="kpi-value" style="font-size:22px;padding-top:5px">R$ <?= number_format($receitaMes, 2, ',', '.') ?></div>
            <div class="kpi-sub"><?= $mesAtual ?> <?= $anoAtual ?></div>
            <svg class="kpi-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="kpi-card amber">
            <div class="kpi-label">Saldo Devedor</div>
            <div class="kpi-value" style="font-size:22px;padding-top:5px">R$ <?= number_format($saldoDevedor, 2, ',', '.') ?></div>
            <div class="kpi-sub">vouchers em aberto</div>
            <svg class="kpi-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
    </div>

    <!-- Charts row 1: Receita 7 dias + Vouchers por status -->
    <div class="chart-grid">
        <div class="dash-card">
            <div class="dash-card-head">
                <h5>Receita &mdash; Últimos 7 dias</h5>
                <small>Pagamentos registrados em ct_createfaturacredit</small>
            </div>
            <div class="dash-card-body">
                <canvas id="chartReceita" height="110"></canvas>
            </div>
        </div>
        <div class="dash-card">
            <div class="dash-card-head">
                <h5>Vouchers por Status</h5>
                <small><?= $mesAtual . ' ' . $anoAtual ?></small>
            </div>
            <div class="dash-card-body">
                <canvas id="chartStatus" height="110"></canvas>
            </div>
        </div>
    </div>

    <!-- Charts row 2: Próximos embarques + Top serviços -->
    <div class="chart-grid-3">
        <!-- Próximos embarques -->
        <div class="dash-card">
            <div class="dash-card-head" style="padding-bottom:16px">
                <h5>Próximos Embarques</h5>
                <small>Saídas nos próximos 7 dias (ativos)</small>
            </div>
            <div style="overflow-x:auto">
                <?php if (!empty($proximosEmbarques)): ?>
                <table class="dash-table">
                    <thead><tr>
                        <th>Voucher</th>
                        <th>Data</th>
                        <th>Serviço</th>
                        <th>PAX</th>
                        <th>Responsável</th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($proximosEmbarques as $e):
                        $isToday = ($e['dateinput'] === date('Y-m-d'));
                        $total   = (int)$e['qtdpax'] + (int)$e['qtdchild'] + (int)$e['qtdfree'];
                    ?>
                        <tr>
                            <td>
                                <a href="editar-pax?numbervoucher=<?= htmlspecialchars($e['numbervoucher']) ?>"
                                   style="color:#1e4770;font-weight:700;text-decoration:none">
                                    <?= htmlspecialchars($e['numbervoucher']) ?>
                                </a>
                            </td>
                            <td>
                                <?php if ($isToday): ?>
                                    <span class="badge-today">Hoje</span>
                                <?php else: ?>
                                    <span class="badge-future"><?= date('d/m', strtotime($e['dateinput'])) ?></span>
                                <?php endif; ?>
                            </td>
                            <td style="max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                                <?= htmlspecialchars($e['servico'] ?? '—') ?>
                            </td>
                            <td>
                                <div class="pax-count">
                                    <span title="Adultos"><?= (int)$e['qtdpax'] ?>A</span>
                                    <?php if ((int)$e['qtdchild']): ?>
                                        <span title="Meia"><?= (int)$e['qtdchild'] ?>M</span>
                                    <?php endif; ?>
                                    <?php if ((int)$e['qtdfree']): ?>
                                        <span title="Free"><?= (int)$e['qtdfree'] ?>F</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td style="white-space:nowrap"><?= htmlspecialchars(($e['firstname'] ?? '') . ' ' . ($e['lastname'] ?? '')) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <div style="padding:32px 22px;text-align:center;color:#94a3b8;font-size:13px">
                        Nenhum embarque nos próximos 7 dias.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Top serviços -->
        <div class="dash-card">
            <div class="dash-card-head">
                <h5>Top Serviços</h5>
                <small><?= $mesAtual . ' ' . $anoAtual ?></small>
            </div>
            <div class="dash-card-body">
                <canvas id="chartServicos" height="180"></canvas>
            </div>
        </div>
    </div>

</div><!-- /containerrrr -->
</div><!-- /dash-page -->

<script>
(function () {
    Chart.defaults.font.family = "'Inter','Segoe UI',sans-serif";
    Chart.defaults.color = '#64748b';

    const palette = ['#2563eb','#7c3aed','#059669','#d97706','#dc2626','#0891b2','#65a30d','#9333ea'];

    // ── Receita 7 dias ───────────────────────────────────────────────────────
    new Chart(document.getElementById('chartReceita'), {
        type: 'bar',
        data: {
            labels: <?= $chartReceita7Labels ?>,
            datasets: [{
                label: 'Receita (R$)',
                data: <?= $chartReceita7Data ?>,
                backgroundColor: 'rgba(37,99,235,.75)',
                borderColor: '#2563eb',
                borderWidth: 0,
                borderRadius: 8,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false }, tooltip: {
                callbacks: { label: ctx => ' R$ ' + ctx.parsed.y.toLocaleString('pt-BR',{minimumFractionDigits:2}) }
            }},
            scales: {
                y: { beginAtZero: true, grid: { color: '#f1f5f9' },
                     ticks: { callback: v => 'R$ ' + (v/1000).toFixed(0) + 'k' } },
                x: { grid: { display: false } }
            }
        }
    });

    // ── Vouchers por status ──────────────────────────────────────────────────
    new Chart(document.getElementById('chartStatus'), {
        type: 'doughnut',
        data: {
            labels: <?= $chartStatusLabels ?>,
            datasets: [{
                data: <?= $chartStatusData ?>,
                backgroundColor: palette,
                borderWidth: 2,
                borderColor: '#fff',
                hoverOffset: 8,
            }]
        },
        options: {
            responsive: true,
            cutout: '62%',
            plugins: {
                legend: { position: 'bottom', labels: { padding: 14, boxWidth: 12, font: { size: 12 } } },
                tooltip: { callbacks: { label: ctx => ' ' + ctx.label + ': ' + ctx.parsed } }
            }
        }
    });

    // ── Top serviços ─────────────────────────────────────────────────────────
    new Chart(document.getElementById('chartServicos'), {
        type: 'bar',
        data: {
            labels: <?= $chartServicosLabels ?>,
            datasets: [{
                label: 'Vouchers',
                data: <?= $chartServicosData ?>,
                backgroundColor: palette,
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { precision: 0 } },
                y: { grid: { display: false },
                     ticks: { font: { size: 11 },
                              callback: function(val) {
                                  const s = this.getLabelForValue(val);
                                  return s.length > 22 ? s.slice(0,22)+'…' : s;
                              }
                     }
                }
            }
        }
    });
})();
</script>

<?php require_once 'footer.php'; ?>
