<?php
require_once 'header.php';

// ── KPIs logísticos ───────────────────────────────────────────────────────────
$embarquesHoje = (int) $pdo->query(
    "SELECT COUNT(*) FROM ct_reserva
     WHERE dateinput = CURDATE() AND idstatus NOT IN (2,13)"
)->fetchColumn();

$embarquesSemana = (int) $pdo->query(
    "SELECT COUNT(*) FROM ct_reserva
     WHERE dateinput BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 6 DAY)
       AND idstatus NOT IN (2,13)"
)->fetchColumn();

$reservasMes = (int) $pdo->query(
    "SELECT COUNT(*) FROM ct_reserva
     WHERE YEAR(abertura)=YEAR(NOW()) AND MONTH(abertura)=MONTH(NOW())"
)->fetchColumn();

$cancelamentosMes = (int) $pdo->query(
    "SELECT COUNT(*) FROM ct_reserva
     WHERE YEAR(abertura)=YEAR(NOW()) AND MONTH(abertura)=MONTH(NOW())
       AND idstatus = 2"
)->fetchColumn();

// ── Embarques próximos 7 dias (para gráfico) ──────────────────────────────────
$emb7raw = $pdo->query(
    "SELECT dateinput AS dia, COUNT(*) AS total
     FROM ct_reserva
     WHERE dateinput BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 6 DAY)
       AND idstatus NOT IN (2,13)
     GROUP BY dateinput"
)->fetchAll(PDO::FETCH_KEY_PAIR);

$emb7Labels = [];
$emb7Data   = [];
for ($i = 0; $i <= 6; $i++) {
    $d = date('Y-m-d', strtotime("+{$i} days"));
    $emb7Labels[] = ($i === 0) ? 'Hoje' : date('d/m', strtotime($d));
    $emb7Data[]   = (int)($emb7raw[$d] ?? 0);
}

// ── Serviços mais reservados (mês) ────────────────────────────────────────────
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

// ── Embarques hoje por serviço (donut) ────────────────────────────────────────
$hojeServico = $pdo->query(
    "SELECT COALESCE(s.fullname,'Outros') AS label, COUNT(*) AS total
     FROM ct_reserva r
     LEFT JOIN ct_servico s ON s.id = r.idservico
     WHERE r.dateinput = CURDATE() AND r.idstatus NOT IN (2,13)
     GROUP BY r.idservico, s.fullname
     ORDER BY total DESC"
)->fetchAll(PDO::FETCH_ASSOC);

// ── Próximos embarques ────────────────────────────────────────────────────────
$proximosEmbarques = $pdo->query(
    "SELECT r.numbervoucher, r.pax, s.fullname AS servico, r.dateinput,
            r.qtdpax, r.qtdchild, r.qtdfree, u.firstname, u.lastname,
            ss.schedule AS horario
     FROM ct_reserva r
     LEFT JOIN ct_servico s           ON s.id  = r.idservico
     LEFT JOIN ct_usuario u           ON u.id  = r.idresponsavel
     LEFT JOIN ct_service_schedule ss ON ss.idshedule = r.idhorario
     WHERE r.dateinput BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 6 DAY)
       AND r.idstatus NOT IN (2,13)
     ORDER BY r.dateinput, ss.schedule, r.numbervoucher
     LIMIT 50"
)->fetchAll(PDO::FETCH_ASSOC);

// ── JSON para Charts ──────────────────────────────────────────────────────────
$chartEmb7Labels     = json_encode($emb7Labels);
$chartEmb7Data       = json_encode($emb7Data);
$chartServLabels     = json_encode(array_column($servicosRows, 'label'));
$chartServData       = json_encode(array_column($servicosRows, 'total'));
$chartHojeLabels     = json_encode(array_column($hojeServico,  'label'));
$chartHojeData       = json_encode(array_column($hojeServico,  'total'));

$meses   = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho',
            'Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
$mesAtual = $meses[(int)date('m') - 1];
$anoAtual = date('Y');
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js" crossorigin="anonymous"></script>
<style>
    .dash-page { background:#f4f7fb; min-height:calc(100vh - 70px); padding:28px 0 52px; }
    .dash-page .containerrrr { width:96%; max-width:1300px; margin:0 auto; }

    .dash-greeting { margin-bottom:28px; }
    .dash-greeting h2 { font-size:26px; font-weight:800; color:#1e293b; margin:0 0 2px; }
    .dash-greeting p  { font-size:14px; color:#64748b; margin:0; }

    .kpi-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:18px; margin-bottom:24px; }
    @media(max-width:900px){ .kpi-grid{ grid-template-columns:repeat(2,1fr); } }
    @media(max-width:520px){ .kpi-grid{ grid-template-columns:1fr; } }

    .kpi-card { border-radius:18px; padding:22px 22px 18px; position:relative; overflow:hidden;
        box-shadow:0 12px 32px rgba(15,23,42,.08); color:#fff; }
    .kpi-card .kpi-label { font-size:12px; font-weight:700; text-transform:uppercase;
        letter-spacing:.6px; opacity:.85; margin-bottom:10px; }
    .kpi-card .kpi-value { font-size:40px; font-weight:900; line-height:1; margin-bottom:6px; }
    .kpi-card .kpi-sub   { font-size:12px; opacity:.75; }
    .kpi-card .kpi-icon  { position:absolute; right:18px; top:18px; opacity:.18;
        width:52px; height:52px; }
    .kpi-card.blue    { background:linear-gradient(135deg,#1e4770,#2563eb); }
    .kpi-card.teal    { background:linear-gradient(135deg,#134e4a,#0d9488); }
    .kpi-card.green   { background:linear-gradient(135deg,#065f46,#059669); }
    .kpi-card.red     { background:linear-gradient(135deg,#7f1d1d,#dc2626); }

    .chart-grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:18px; margin-bottom:24px; }
    @media(max-width:860px){ .chart-grid-2{ grid-template-columns:1fr; } }

    .chart-grid-3 { display:grid; grid-template-columns:2fr 1fr; gap:18px; margin-bottom:24px; }
    @media(max-width:860px){ .chart-grid-3{ grid-template-columns:1fr; } }

    .dash-card { background:#fff; border-radius:20px; box-shadow:0 12px 32px rgba(15,23,42,.07); overflow:hidden; }
    .dash-card-head { padding:18px 22px 0; }
    .dash-card-head h5 { font-size:14px; font-weight:800; color:#1e293b; text-transform:uppercase;
        letter-spacing:.5px; margin:0 0 2px; }
    .dash-card-head small { font-size:12px; color:#94a3b8; }
    .dash-card-body { padding:18px 22px 22px; }

    .dash-table { width:100%; border-collapse:collapse; font-size:13px; }
    .dash-table th { background:#f8fafc; color:#64748b; font-size:11px; text-transform:uppercase;
        letter-spacing:.5px; padding:10px 14px; border-bottom:2px solid #e5e7eb; text-align:left;
        white-space:nowrap; }
    .dash-table td { padding:10px 14px; border-bottom:1px solid #f1f5f9; vertical-align:middle; }
    .dash-table tr:last-child td { border-bottom:0; }
    .dash-table tr:hover td { background:#f8fafc; }

    .badge-today  { background:#fef9c3; color:#92400e; font-size:11px; font-weight:700;
        padding:2px 9px; border-radius:999px; white-space:nowrap; }
    .badge-future { background:#eff6ff; color:#1d4ed8; font-size:11px; font-weight:700;
        padding:2px 9px; border-radius:999px; white-space:nowrap; }
    .pax-count { display:inline-flex; align-items:center; gap:5px; font-size:12px; }
    .pax-count span { background:#f1f5f9; border-radius:6px; padding:1px 7px; color:#475569; font-weight:600; }

    .section-divider { font-size:11px; font-weight:800; color:#94a3b8; text-transform:uppercase;
        letter-spacing:.8px; margin:8px 0 14px; }
</style>

<div class="dash-page">
<div class="containerrrr">

    <!-- greeting -->
    <div class="dash-greeting">
        <h2>Olá, <?= htmlspecialchars(ucwords(strtolower($_SESSION['nome'] ?? 'Usuário'))) ?></h2>
        <p>Painel de Logística &mdash; <?= $mesAtual . ' ' . $anoAtual ?></p>
    </div>

    <!-- KPIs -->
    <div class="kpi-grid">
        <div class="kpi-card blue">
            <div class="kpi-label">Embarques Hoje</div>
            <div class="kpi-value"><?= $embarquesHoje ?></div>
            <div class="kpi-sub">saídas em <?= date('d/m/Y') ?></div>
            <svg class="kpi-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
        <div class="kpi-card teal">
            <div class="kpi-label">Embarques esta Semana</div>
            <div class="kpi-value"><?= $embarquesSemana ?></div>
            <div class="kpi-sub">próximos 7 dias</div>
            <svg class="kpi-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
        </div>
        <div class="kpi-card green">
            <div class="kpi-label">Reservas no Mês</div>
            <div class="kpi-value"><?= $reservasMes ?></div>
            <div class="kpi-sub"><?= $mesAtual ?> <?= $anoAtual ?></div>
            <svg class="kpi-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </div>
        <div class="kpi-card red">
            <div class="kpi-label">Cancelamentos</div>
            <div class="kpi-value"><?= $cancelamentosMes ?></div>
            <div class="kpi-sub"><?= $mesAtual ?> <?= $anoAtual ?></div>
            <svg class="kpi-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
    </div>

    <!-- Charts: embarques 7 dias + hoje por serviço -->
    <div class="chart-grid-2">
        <div class="dash-card">
            <div class="dash-card-head">
                <h5>Embarques — Próximos 7 dias</h5>
                <small>Contagem diária de saídas ativas</small>
            </div>
            <div class="dash-card-body">
                <canvas id="chartEmb7" height="110"></canvas>
            </div>
        </div>
        <div class="dash-card">
            <div class="dash-card-head">
                <h5>Embarques Hoje por Serviço</h5>
                <small><?= date('d/m/Y') ?></small>
            </div>
            <div class="dash-card-body">
                <?php if (!empty($hojeServico)): ?>
                    <canvas id="chartHoje" height="110"></canvas>
                <?php else: ?>
                    <div style="display:flex;align-items:center;justify-content:center;height:140px;color:#94a3b8;font-size:13px">
                        Nenhum embarque hoje.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Próximos embarques + top serviços -->
    <div class="chart-grid-3">

        <div class="dash-card">
            <div class="dash-card-head" style="padding-bottom:16px">
                <h5>Próximos Embarques</h5>
                <small>Saídas nos próximos 7 dias — ordenado por data e horário</small>
            </div>
            <div style="overflow-x:auto">
                <?php if (!empty($proximosEmbarques)):
                    $diaAtual = null;
                ?>
                <table class="dash-table">
                    <thead><tr>
                        <th>Voucher</th>
                        <th>Data</th>
                        <th>Horário</th>
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
                                   aria-label="Editar voucher <?= htmlspecialchars($e['numbervoucher']) ?>"
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
                            <td style="color:#475569;font-size:12px"><?= htmlspecialchars($e['horario'] ?? '—') ?></td>
                            <td style="max-width:160px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
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
                            <td style="white-space:nowrap;font-size:12px">
                                <?= htmlspecialchars(trim(($e['firstname'] ?? '') . ' ' . ($e['lastname'] ?? ''))) ?>
                            </td>
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

        <!-- Top serviços do mês -->
        <div class="dash-card">
            <div class="dash-card-head">
                <h5>Top Serviços</h5>
                <small><?= $mesAtual . ' ' . $anoAtual ?></small>
            </div>
            <div class="dash-card-body">
                <?php if (!empty($servicosRows)): ?>
                    <canvas id="chartServ" height="220"></canvas>
                <?php else: ?>
                    <div style="padding:32px 0;text-align:center;color:#94a3b8;font-size:13px">Sem dados este mês.</div>
                <?php endif; ?>
            </div>
        </div>

    </div>

</div><!-- /containerrrr -->
</div><!-- /dash-page -->

<script>
(function () {
    Chart.defaults.font.family = "'Inter','Segoe UI',sans-serif";
    Chart.defaults.color = '#64748b';

    const palette = ['#2563eb','#0d9488','#059669','#dc2626','#7c3aed','#d97706','#0891b2','#65a30d'];

    // ── Embarques 7 dias ─────────────────────────────────────────────────────
    new Chart(document.getElementById('chartEmb7'), {
        type: 'bar',
        data: {
            labels: <?= $chartEmb7Labels ?>,
            datasets: [{
                label: 'Embarques',
                data: <?= $chartEmb7Data ?>,
                backgroundColor: <?= $chartEmb7Labels ?>.map((l,i) => i===0 ? '#1e4770' : 'rgba(13,148,136,.75)'),
                borderRadius: 8,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } },
                x: { grid: { display: false } }
            }
        }
    });

    // ── Hoje por serviço ─────────────────────────────────────────────────────
    <?php if (!empty($hojeServico)): ?>
    new Chart(document.getElementById('chartHoje'), {
        type: 'doughnut',
        data: {
            labels: <?= $chartHojeLabels ?>,
            datasets: [{
                data: <?= $chartHojeData ?>,
                backgroundColor: palette,
                borderWidth: 2,
                borderColor: '#fff',
                hoverOffset: 8,
            }]
        },
        options: {
            responsive: true,
            cutout: '60%',
            plugins: {
                legend: { position: 'bottom', labels: { padding: 12, boxWidth: 12, font: { size: 11 } } }
            }
        }
    });
    <?php endif; ?>

    // ── Top serviços ─────────────────────────────────────────────────────────
    <?php if (!empty($servicosRows)): ?>
    new Chart(document.getElementById('chartServ'), {
        type: 'bar',
        data: {
            labels: <?= $chartServLabels ?>,
            datasets: [{
                label: 'Reservas',
                data: <?= $chartServData ?>,
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
                x: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } },
                y: { grid: { display: false },
                     ticks: { font: { size: 11 },
                              callback: function(val) {
                                  const s = this.getLabelForValue(val);
                                  return s.length > 20 ? s.slice(0,20)+'…' : s;
                              }
                     }
                }
            }
        }
    });
    <?php endif; ?>
})();
</script>

<?php require_once 'footer.php'; ?>
