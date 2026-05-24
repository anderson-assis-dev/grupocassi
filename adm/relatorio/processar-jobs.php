<?php
/**
 * Script CLI — processador de jobs de relatório por e-mail.
 *
 * Execução manual:
 *   php /caminho/para/adm/relatorio/processar-jobs.php
 *
 * Configuração no cron (processar a cada minuto):
 *   * * * * * /usr/bin/php /var/www/html/adm/relatorio/processar-jobs.php >> /tmp/cassi_jobs.log 2>&1
 *
 * Processa UM job por execução para não sobrecarregar o servidor.
 */

// ── Evita execuções simultâneas ───────────────────────────────────────────────
$lock = sys_get_temp_dir() . '/cassi_report_jobs.lock';
if (file_exists($lock) && (time() - filemtime($lock)) < 300) {
    // Lock recente (< 5 min): outra instância está rodando
    exit(0);
}
file_put_contents($lock, (string)getmypid());
register_shutdown_function(static fn() => @unlink($lock));

// ── Bootstrap ─────────────────────────────────────────────────────────────────
ini_set('memory_limit', '256M');
set_time_limit(300);

require_once dirname(__DIR__, 2) . '/config.php';
require_once dirname(__DIR__)    . '/includes/mailer.php';
$pdo->exec("set names utf8");

// ── Busca próximo job pendente ────────────────────────────────────────────────
$job = $pdo->query(
    "SELECT * FROM ct_report_jobs WHERE status = 'pendente' ORDER BY criado_em ASC LIMIT 1"
)->fetch(PDO::FETCH_ASSOC);

if (!$job) {
    exit(0);
}

// Marca como em processamento
$pdo->prepare("UPDATE ct_report_jobs SET status = 'processando' WHERE id = :id")
    ->execute([':id' => $job['id']]);

// ── Helpers ───────────────────────────────────────────────────────────────────
function jEsc(string $v): string { return htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function jBrl(float $v): string  { return 'R$ ' . number_format($v, 2, ',', '.'); }

function jPeriodo(string $ini, string $fim): string {
    $m = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho',
          'Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
    $di = date('d', strtotime($ini)) . ' de ' . $m[(int)date('n', strtotime($ini)) - 1] . ' de ' . date('Y', strtotime($ini));
    $df = date('d', strtotime($fim)) . ' de ' . $m[(int)date('n', strtotime($fim)) - 1] . ' de ' . date('Y', strtotime($fim));
    return $di === $df ? $di : "$di até $df";
}

// ── Processa ──────────────────────────────────────────────────────────────────
try {
    $datainicio = $job['datainicio'];
    $datafim    = $job['datafim'];
    $empresa    = (int)$job['empresa'];
    $idcliente  = (int)$job['idcliente'];
    $conta      = (int)$job['idconta'];
    $tipo       = (int)$job['tiporelatorio'];

    // ── SQL base ──────────────────────────────────────────────────────────────
    $joins = "FROM ct_caixa c
              LEFT JOIN ct_currentaccount cc  ON cc.id  = c.idconta
              LEFT JOIN ct_fornecedor     cli ON cli.id = c.idcliente
              LEFT JOIN ct_empresa        em  ON em.id  = c.idempresa
              LEFT JOIN ct_statusinvoice  s   ON s.id   = c.idstatus";

    if ($tipo === 0) {
        $where  = ['c.dataabertura >= :inicio', 'c.dataabertura <= :fim'];
        $params = [':inicio' => $datainicio, ':fim' => $datafim];
        if ($empresa   > 0) { $where[] = 'c.idempresa = :empresa'; $params[':empresa'] = $empresa; }
        if ($idcliente > 0) { $where[] = 'c.idcliente = :cliente'; $params[':cliente'] = $idcliente; }
        if ($conta     > 0) { $where[] = 'c.idconta   = :idconta'; $params[':idconta'] = $conta; }
    } else {
        $where  = [
            'c.datevencimento >= :inicio',
            'c.datevencimento <= :fim',
            "(c.descricao NOT LIKE '%Pagamento de comiss%' OR c.descricao IS NULL)",
        ];
        $params = [':inicio' => $datainicio, ':fim' => $datafim];
        if ($empresa > 0) { $where[] = 'c.idempresa = :empresa'; $params[':empresa'] = $empresa; }
    }
    $wSql = ' WHERE ' . implode(' AND ', $where);

    // ── Totais via SQL ────────────────────────────────────────────────────────
    $total = $totalDebito = $totalCredito = 0.0;
    $totalRows = 0;

    if ($tipo === 0) {
        $aggStmt = $pdo->prepare(
            "SELECT COUNT(*) AS cnt,
                    COALESCE(SUM(c.valor), 0) AS total,
                    COALESCE(SUM(CASE WHEN c.idtipo = 2 THEN c.valor ELSE 0 END), 0) AS totalDebito,
                    COALESCE(SUM(CASE WHEN c.idstatus = 3 AND c.idtipo = 1 THEN c.valor ELSE 0 END), 0) AS totalCredito
             $joins $wSql"
        );
        $aggStmt->execute($params);
        $agg = $aggStmt->fetch(PDO::FETCH_ASSOC);
        $total        = (float)$agg['total'];
        $totalDebito  = (float)$agg['totalDebito'];
        $totalCredito = (float)$agg['totalCredito'];
        $totalRows    = (int)$agg['cnt'];
    } else {
        $cntStmt = $pdo->prepare("SELECT COUNT(*) $joins $wSql");
        $cntStmt->execute($params);
        $totalRows = (int)$cntStmt->fetchColumn();
    }

    // ── Cursor de linhas (streaming) ──────────────────────────────────────────
    $selectCols = "SELECT c.datevencimento AS vencimento, c.descricao,
                          cc.name AS conta, cli.fullname AS favorecido,
                          c.idcliente, c.valor, c.idtipo, c.idstatus,
                          s.nameinvoice, c.nome, em.fullname AS empresa";
    $stmt = $pdo->prepare("$selectCols $joins $wSql ORDER BY cli.fullname, c.datevencimento");
    $stmt->execute($params);

    $titulo  = $tipo === 0 ? 'Fluxo de Caixa' : 'Fluxo de Caixa por Fornecedor';
    $periodo = jPeriodo($datainicio, $datafim);

    // ── Gera HTML compatível com Dompdf ───────────────────────────────────────
    ob_start();
    ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<style>
  body        { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #1a1a1a; }
  h1          { font-size: 15px; color: #1e4770; margin: 0 0 2px; }
  .sub        { font-size: 10px; color: #555; margin-bottom: 12px; }
  .kpi-table  { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
  .kpi-table td { width: 25%; border: 1px solid #c8d6e8; padding: 8px 10px; text-align: center; }
  .kpi-label  { font-size: 9px; color: #6c757d; text-transform: uppercase; letter-spacing: .04em; }
  .kpi-value  { font-size: 14px; font-weight: bold; margin-top: 3px; }
  .navy       { color: #1e4770; }
  .green      { color: #1a9e5c; }
  .red        { color: #dc3545; }
  table.data  { width: 100%; border-collapse: collapse; }
  table.data thead th { background-color: #1e4770; color: #fff; font-size: 9px; text-transform: uppercase;
                        padding: 6px 5px; text-align: left; }
  table.data tbody td { padding: 5px 5px; border-bottom: 1px solid #e9ecef; font-size: 10px; }
  table.data tbody tr:nth-child(even) td { background-color: #f8fafc; }
  .tr-subtotal td  { background-color: #dce9f8 !important; font-weight: bold; color: #1e4770; }
  .tr-total    td  { background-color: #1e4770 !important; color: #fff; font-weight: bold; font-size: 11px; padding: 7px 5px; }
  .tr-group    td  { background-color: #e8f0fb !important; font-weight: bold; color: #1e4770;
                     border-top: 2px solid #1e4770; padding: 6px 5px; }
  .badge      { padding: 1px 5px; border-radius: 3px; font-size: 9px; font-weight: bold; }
  .bc         { background-color: #dcfce7; color: #166534; }
  .bd         { background-color: #fee2e2; color: #991b1b; }
  .bf         { background-color: #e2e8f0; color: #475569; }
  .right      { text-align: right; }
  .empty      { text-align: center; padding: 40px; color: #aaa; font-size: 13px; }
</style>
</head>
<body>
<h1><?= jEsc($titulo) ?></h1>
<div class="sub">
  Período: <?= jEsc($periodo) ?> &nbsp;|&nbsp;
  Gerado em: <?= date('d/m/Y H:i:s') ?> &nbsp;|&nbsp;
  <?= $totalRows ?> registro(s)
</div>

<?php if ($tipo === 0): ?>
<table class="kpi-table">
  <tr>
    <td><div class="kpi-label">Total Geral</div><div class="kpi-value navy"><?= jBrl($total) ?></div></td>
    <td><div class="kpi-label">Total Crédito</div><div class="kpi-value green"><?= jBrl($totalCredito) ?></div></td>
    <td><div class="kpi-label">Total Débito</div><div class="kpi-value red"><?= jBrl($totalDebito) ?></div></td>
    <td><div class="kpi-label">A Conferir</div><div class="kpi-value"><?= jBrl($total - ($totalCredito + $totalDebito)) ?></div></td>
  </tr>
</table>
<?php endif; ?>

<?php if ($totalRows === 0): ?>
  <div class="empty">Nenhum registro encontrado para os filtros selecionados.</div>

<?php elseif ($tipo === 0): ?>
  <table class="data">
    <thead><tr>
      <th>Vencimento</th><th>Conta</th><th>Tipo</th><th>Nome</th>
      <th>Documento</th><th>Empresa</th><th>Favorecido</th><th>Situação</th>
      <th class="right">Valor</th>
    </tr></thead>
    <tbody>
    <?php while ($r = $stmt->fetch(PDO::FETCH_OBJ)):
        $idtipo = (int)$r->idtipo;
        if ($idtipo === 1)     { $tl = 'CRÉDITO'; $tc = 'bc'; }
        elseif ($idtipo === 2) { $tl = 'DÉBITO';  $tc = 'bd'; }
        else                   { $tl = '—';        $tc = 'bf'; }
    ?>
    <tr>
      <td><?= date('d/m/Y', strtotime($r->vencimento)) ?></td>
      <td><?= jEsc((string)$r->conta) ?></td>
      <td><span class="badge <?= $tc ?>"><?= $tl ?></span></td>
      <td><?= jEsc((string)$r->nome) ?></td>
      <td><?= jEsc((string)$r->descricao) ?></td>
      <td><?= jEsc((string)$r->empresa) ?></td>
      <td><?= jEsc((string)$r->favorecido) ?></td>
      <td><?= jEsc((string)$r->nameinvoice) ?></td>
      <td class="right"><strong><?= jBrl((float)$r->valor) ?></strong></td>
    </tr>
    <?php endwhile; ?>
    <tr class="tr-total">
      <td colspan="8">TOTAL GERAL</td>
      <td class="right"><?= jBrl($total) ?></td>
    </tr>
    </tbody>
  </table>

<?php else: ?>
  <?php
  $grandTotal    = 0.0;
  $groupTotal    = 0.0;
  $groupName     = '';
  $currentClient = null;
  ?>
  <table class="data">
    <thead><tr>
      <th>Vencimento</th><th>Conta</th><th>Documento</th>
      <th>Empresa</th><th>Favorecido</th><th class="right">Valor</th>
    </tr></thead>
    <tbody>
    <?php while ($r = $stmt->fetch(PDO::FETCH_OBJ)):
        $ck = $r->idcliente ?? '__sem__';
        if ($currentClient !== $ck):
            if ($currentClient !== null): ?>
    <tr class="tr-subtotal">
      <td colspan="5" class="right">Subtotal — <?= jEsc($groupName) ?></td>
      <td class="right"><?= jBrl($groupTotal) ?></td>
    </tr>
    <?php endif;
            $currentClient = $ck;
            $groupName     = (string)($r->favorecido ?? '(sem favorecido)');
            $groupTotal    = 0.0;
    ?>
    <tr class="tr-group"><td colspan="6"><?= jEsc($groupName) ?></td></tr>
    <?php endif;
        $groupTotal += (float)$r->valor;
        $grandTotal += (float)$r->valor;
    ?>
    <tr>
      <td><?= date('d/m/Y', strtotime($r->vencimento)) ?></td>
      <td><?= jEsc((string)$r->conta) ?></td>
      <td><?= jEsc((string)$r->descricao) ?></td>
      <td><?= jEsc((string)$r->empresa) ?></td>
      <td><?= jEsc((string)$r->favorecido) ?></td>
      <td class="right"><?= jBrl((float)$r->valor) ?></td>
    </tr>
    <?php endwhile; ?>
    <?php if ($currentClient !== null): ?>
    <tr class="tr-subtotal">
      <td colspan="5" class="right">Subtotal — <?= jEsc($groupName) ?></td>
      <td class="right"><?= jBrl($groupTotal) ?></td>
    </tr>
    <?php endif; ?>
    <tr class="tr-total">
      <td colspan="5">TOTAL GERAL</td>
      <td class="right"><?= jBrl($grandTotal) ?></td>
    </tr>
    </tbody>
  </table>
<?php endif; ?>
</body>
</html>
    <?php
    $html = ob_get_clean();

    // ── Converte HTML → PDF via Dompdf ────────────────────────────────────────
    require_once __DIR__ . '/dompdf/src/Autoloader.php';
    Dompdf\Autoloader::register();

    $dompdf = new Dompdf\Dompdf();
    $dompdf->getOptions()->setChroot(dirname(__DIR__, 2));
    $dompdf->getOptions()->setIsHtml5ParserEnabled(true);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();

    $filename = 'relatorio-caixa-' . date('Y-m-d') . '.pdf';
    $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filename;
    file_put_contents($path, $dompdf->output());

    // ── Envia e-mail com PDF em anexo ─────────────────────────────────────────
    $emailBody = '
        <p style="font-family:Arial,sans-serif;font-size:14px;">Olá,</p>
        <p style="font-family:Arial,sans-serif;font-size:14px;">
            Segue em anexo o <strong>' . jEsc($titulo) . '</strong>
            referente ao período de <strong>' . jEsc($periodo) . '</strong>.
        </p>
        <p style="font-family:Arial,sans-serif;font-size:13px;color:#555;">
            Gerado automaticamente em: ' . date('d/m/Y H:i:s') . '<br>
            Total de registros: ' . $totalRows . '
        </p>
        <hr style="border:none;border-top:1px solid #eee;margin:20px 0;">
        <p style="font-family:Arial,sans-serif;font-size:12px;color:#888;">
            Cassi Turismo — Sistema de Gestão
        </p>';

    enviarEmail(
        $job['email_destino'],
        $titulo . ' — ' . $periodo,
        $emailBody,
        [
            'attachments'     => [$path],
            'attachmentNames' => [$filename],
            'altBody'         => "Relatório {$titulo} — {$periodo}\nGerado em: " . date('d/m/Y H:i:s'),
        ]
    );

    if (is_file($path)) {
        unlink($path);
    }

    // ── Marca como enviado ────────────────────────────────────────────────────
    $pdo->prepare("UPDATE ct_report_jobs SET status = 'enviado', processado_em = NOW() WHERE id = :id")
        ->execute([':id' => $job['id']]);

    echo '[' . date('Y-m-d H:i:s') . "] Job #{$job['id']} enviado para {$job['email_destino']}\n";

} catch (Throwable $e) {
    $pdo->prepare(
        "UPDATE ct_report_jobs SET status = 'erro', erro_msg = :msg, processado_em = NOW() WHERE id = :id"
    )->execute([':msg' => $e->getMessage(), ':id' => $job['id']]);

    echo '[' . date('Y-m-d H:i:s') . "] Job #{$job['id']} ERRO: " . $e->getMessage() . "\n";
}
