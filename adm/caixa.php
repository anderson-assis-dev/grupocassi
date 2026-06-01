<?php
require_once 'header.php';
require_once __DIR__ . '/includes/ref_cache.php';
require_once __DIR__ . '/includes/flash.php';
require_once __DIR__ . '/includes/transacao_helpers.php';
$pdo->exec("set names utf8");
$listaFornecedores = refFornecedores($pdo);
$listaEmpresas = refEmpresasTodas($pdo);
$listaStatus = refStatusInvoice($pdo);
$listaPlanoContas = refPlanoContas($pdo);
$listaTipoCaixa = refTipoCaixa($pdo);
$listaContaCorrente = refContaCorrente($pdo);
$podeEscolherResponsavel = in_array((int)$_SESSION['id'], [34, 208, 207], true);
$listaUsuarios = [];
if (in_array((int)$_SESSION['id'], [34, 208, 207], true)) {
    $listaUsuarios = refUsuarios($pdo);
}
$usersIds = [34, 285, 44, 366, 226, 376, 168, 281, 397, 355, 376, 59, 402, 405];
$hoje = date('Y-m-d');
$abaAtiva = 'consultar';
function caixaSelectSql(): string
{
    return "SELECT c.id,c.datevencimento,c.nome,c.datecompetencia,c.datepagamento,c.descricao,c.anexo,
        forne.fullname AS fornecedor,tc.name AS tipo,cc.name AS conta,p.name AS plano,
        s.nameinvoice AS situacao,c.valor,em.fullname AS empresa,c.idstatus
        FROM ct_caixa c
        LEFT JOIN ct_fornecedor forne ON forne.id = c.idcliente
        LEFT JOIN ct_tipocaixa tc ON tc.id = c.idtipo
        LEFT JOIN ct_currentaccount cc ON cc.id = c.idconta
        LEFT JOIN ct_planaccounts p ON p.id = c.idplano
        LEFT JOIN ct_statusinvoice s ON s.id = c.idstatus
        LEFT JOIN ct_empresa em ON em.id = c.idempresa";
}
function caixaListarDia(PDO $pdo, string $data, bool $somenteUsuario, int $idUsuario): array
{
    $sql = caixaSelectSql() . " WHERE c.datevencimento = :pagamento";
    $params = [':pagamento' => $data];
    if ($somenteUsuario) {
        $sql .= " AND c.idusr = :idusuario";
        $params[':idusuario'] = $idUsuario;
    }
    $sql .= " ORDER BY c.nome ASC, c.descricao ASC";
    $st = $pdo->prepare($sql);
    $st->execute($params);
    return $st->fetchAll(PDO::FETCH_CLASS);
}
function caixaPesquisar(PDO $pdo, array $filtros): array
{
    $sql = caixaSelectSql() . " WHERE 1=1";
    $params = [];
    if ((int)$filtros['favorecido'] > 0) {
        $sql .= " AND c.idcliente = :favorecido";
        $params[':favorecido'] = (int)$filtros['favorecido'];
    }
    if ((int)$filtros['idempresa'] > 0) {
        $sql .= " AND c.idempresa = :idempresa";
        $params[':idempresa'] = (int)$filtros['idempresa'];
    }
    if ((int)$filtros['nrecibo'] > 0) {
        $sql .= " AND c.id = :nrecibo";
        $params[':nrecibo'] = (int)$filtros['nrecibo'];
    }
    if (!empty($filtros['nomepesquisa'])) {
        $sql .= " AND c.nome LIKE :nomepesquisa";
        $params[':nomepesquisa'] = '%' . $filtros['nomepesquisa'] . '%';
    }
    if (!empty($filtros['datavencimentoinicial']) && !empty($filtros['datavencimentofinal'])) {
        $sql .= " AND c.datevencimento >= :dataini AND c.datevencimento <= :datafim";
        $params[':dataini'] = $filtros['datavencimentoinicial'];
        $params[':datafim'] = $filtros['datavencimentofinal'];
    }
    $sql .= " ORDER BY c.datevencimento DESC, c.id DESC";
    $st = $pdo->prepare($sql);
    $st->execute($params);
    return $st->fetchAll(PDO::FETCH_CLASS);
}
if (isset($_GET['hoje'])) {
    unset($_SESSION['datavencimentoinicial'], $_SESSION['datavencimentofinal'], $_SESSION['favorecido'], $_SESSION['nomepesquisa'], $_SESSION['nrecibo'], $_SESSION['idempresa']);
    header('location: caixa');
    exit;
}
if (isset($_POST['novatransacao'])) {
    $valor = str_replace('.', '', $_POST['valor']);
    $valor1 = str_replace(',', '.', $valor);
    $novaTransacao = $pdo->prepare(
        "INSERT INTO ct_caixa (id,datevencimento,datepagamento,datecompetencia,nome,descricao,idcliente,idtipo,idconta,
        idplano,idempresa,idstatus,valor,idusr,dataabertura) VALUES (DEFAULT,:vencimento,:pagamento,:competencia,:nome,:descricao,
        :cliente,:tipo,:conta,:plano,:empresa,:statuus,:valor,:idusr,:abertura)"
    );
    $novaTransacao->execute([
        ':vencimento' => $_POST['datavencimento'],
        ':pagamento' => $_POST['datapagamento'],
        ':competencia' => $_POST['datacompetencia'],
        ':nome' => $_POST['nome'],
        ':descricao' => $_POST['documento'],
        ':cliente' => $_POST['favorecido'],
        ':tipo' => $_POST['tipo'],
        ':conta' => $_POST['contacorrente'],
        ':plano' => $_POST['planocontas'],
        ':empresa' => $_POST['empresa'],
        ':statuus' => $_POST['status'],
        ':valor' => $valor1,
        ':idusr' => $_POST['responsavel'],
        ':abertura' => $hoje
    ]);
    $novoId = (int)$pdo->lastInsertId();
    $nomeAnexo = transacaoUploadAnexo($novoId);
    if ($nomeAnexo !== null) {
        $pdo->prepare("UPDATE ct_caixa SET anexo=:a WHERE id=:id")->execute([':a' => $nomeAnexo, ':id' => $novoId]);
    }
    header('location: editar-transacao?idtransacao=' . $novoId);
    exit;
}
if (isset($_POST['removertransacao'])) {
    $remover = $pdo->prepare('DELETE FROM ct_caixa WHERE id = :id');
    $remover->execute([':id' => (int)$_POST['idtransacao']]);
    setFlash('success', 'Transação "' . $_POST['nometransacao'] . '" removida com sucesso.');
    header('location: caixa');
    exit;
}
$filtros = [
    'datavencimentoinicial' => $_SESSION['datavencimentoinicial'] ?? $hoje,
    'datavencimentofinal' => $_SESSION['datavencimentofinal'] ?? $hoje,
    'favorecido' => $_SESSION['favorecido'] ?? 0,
    'nomepesquisa' => $_SESSION['nomepesquisa'] ?? '',
    'nrecibo' => $_SESSION['nrecibo'] ?? '',
    'idempresa' => $_SESSION['idempresa'] ?? 0
];
$modoPesquisa = false;
if (isset($_POST['pesquisartransacao'])) {
    $modoPesquisa = true;
    $abaAtiva = 'consultar';
    $filtros = [
        'datavencimentoinicial' => $_POST['datavencimentoinicial'] ?? $hoje,
        'datavencimentofinal' => $_POST['datavencimentofinal'] ?? $hoje,
        'favorecido' => (int)($_POST['favorecido'] ?? 0),
        'nomepesquisa' => trim($_POST['nomepesquisa'] ?? ''),
        'nrecibo' => trim($_POST['nrecibo'] ?? ''),
        'idempresa' => (int)($_POST['idempresa'] ?? 0)
    ];
    $_SESSION['datavencimentoinicial'] = $filtros['datavencimentoinicial'];
    $_SESSION['datavencimentofinal'] = $filtros['datavencimentofinal'];
    $_SESSION['favorecido'] = $filtros['favorecido'];
    $_SESSION['nomepesquisa'] = $filtros['nomepesquisa'];
    $_SESSION['nrecibo'] = $filtros['nrecibo'];
    $_SESSION['idempresa'] = $filtros['idempresa'];
    $registroCaixa = caixaPesquisar($pdo, $filtros);
} else {
    if (isset($_GET['aba']) && $_GET['aba'] === 'nova') {
        $abaAtiva = 'nova';
    }
    $registroCaixa = caixaListarDia($pdo, $hoje, empty($_SESSION['idgerente']), (int)$_SESSION['id']);
}
$totalTransacoes = count($registroCaixa);
$valorTotal = array_sum(array_map(static fn($item) => (float)$item->valor, $registroCaixa));
function caixaEsc($valor): string
{
    return htmlentities((string)$valor, ENT_QUOTES, 'UTF-8');
}
function caixaStatusClass($idstatus): string
{
    $map = [1 => 'caixa-badge--pendente', 2 => 'caixa-badge--pago', 3 => 'caixa-badge--cancelado'];
    return $map[(int)$idstatus] ?? 'caixa-badge--default';
}
?>
<link href="../css/reserva-ui.css" rel="stylesheet" media="all">
<style>
:root { --navy: #1e4770; --navy-lt: #2a5f96; }
.map-wrapper { padding: 20px 20px 80px; }
.bc-bar { padding: 0 0 16px; font-size: 13px; color: #6c757d; }
.bc-bar a { color: var(--navy); font-weight: 600; text-decoration: none; }
.bc-bar a:hover { text-decoration: underline; }
.bc-bar .sep { margin: 0 6px; color: #ccc; }
.filter-card { background: #fff; border-radius: 12px; box-shadow: 0 2px 14px rgba(0,0,0,.07); padding: 22px 26px 18px; margin-bottom: 20px; }
.fc-title { font-size: 12px; font-weight: 700; color: var(--navy); text-transform: uppercase; letter-spacing: .06em; margin-bottom: 16px; display: flex; align-items: center; gap: 7px; }
.filter-grid { display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 12px 18px; }
.filter-grid .span-2 { grid-column: span 2; }
.filter-grid label { font-size: 11px; font-weight: 700; color: #6c757d; text-transform: uppercase; letter-spacing: .05em; display: block; margin-bottom: 5px; }
.filter-grid .form-control { border: 1.5px solid #dee2e6; border-radius: 8px; font-size: 13px; height: 36px; transition: border-color .2s; }
.filter-grid .form-control:focus { border-color: var(--navy); box-shadow: 0 0 0 3px rgba(30,71,112,.12); }
.btn-nav { border: 2px solid var(--navy); background: transparent; color: var(--navy); border-radius: 8px; padding: 8px 16px; font-size: 13px; font-weight: 700; cursor: pointer; transition: all .2s; display: inline-flex; align-items: center; justify-content: center; gap: 6px; white-space: nowrap; }
.btn-nav:hover { background: var(--navy); color: #fff; }
.btn-nav.primary { background: var(--navy); color: #fff; }
.btn-nav.primary:hover { background: var(--navy-lt); }
.kpi-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin-bottom: 20px; }
.kpi-card { background: #fff; border-radius: 12px; box-shadow: 0 2px 14px rgba(0,0,0,.07); padding: 16px 18px; display: flex; align-items: center; gap: 12px; }
.kpi-icon { width: 42px; height: 42px; border-radius: 10px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 17px; }
.kpi-icon.trans { background: rgba(30,71,112,.1); color: var(--navy); }
.kpi-icon.valor { background: rgba(26,158,92,.12); color: #1a9e5c; }
.kpi-icon.periodo { background: rgba(108,117,125,.1); color: #555; }
.kpi-label { font-size: 10px; font-weight: 700; color: #999; text-transform: uppercase; letter-spacing: .05em; }
.kpi-value { font-size: 22px; font-weight: 800; color: #212529; line-height: 1.1; }
.kpi-value.green { color: #1a9e5c; }
.kpi-value.period { font-size: 14px; }
.results-card { background: #fff; border-radius: 12px; box-shadow: 0 2px 14px rgba(0,0,0,.07); overflow: hidden; }
.results-header { padding: 16px 22px 12px; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px; }
.results-title { font-size: 14px; font-weight: 700; color: var(--navy); display: flex; align-items: center; gap: 8px; }
.results-count { font-size: 11px; background: var(--navy); color: #fff; padding: 2px 9px; border-radius: 20px; font-weight: 600; }
.period-label { font-size: 12px; color: #6c757d; }
#caixa-table { font-size: 12.5px; }
#caixa-table thead th { background: var(--navy); color: #fff; font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: .04em; border: none; padding: 9px 8px; white-space: nowrap; }
#caixa-table tbody tr { transition: background .12s; cursor: pointer; }
#caixa-table tbody tr:hover td { background: #f0f6ff !important; }
#caixa-table tbody td { padding: 7px 8px; vertical-align: middle; border-color: #f0f0f0; }
#caixa-table th.caixa-col-acoes, #caixa-table td.caixa-col-acoes { background: #fff; box-shadow: -8px 0 16px rgba(15,23,42,.06); position: sticky; right: 0; z-index: 2; }
#caixa-table thead th.caixa-col-acoes { z-index: 3; }
.caixa-id-link { color: var(--navy); font-weight: 800; text-decoration: none; }
.caixa-id-link:hover { text-decoration: underline; }
.caixa-badge { border-radius: 999px; display: inline-block; font-size: 11px; font-weight: 700; padding: 3px 9px; }
.caixa-badge--pago { background: #dcfce7; color: #166534; }
.caixa-badge--pendente { background: #fef3c7; color: #92400e; }
.caixa-badge--cancelado { background: #fee2e2; color: #991b1b; }
.caixa-badge--default { background: #e2e8f0; color: #475569; }
.caixa-actions { display: flex; flex-wrap: wrap; gap: 4px; }
.btn-tbl-edit { background: var(--navy); color: #fff; border: none; border-radius: 6px; padding: 4px 10px; font-size: 12px; font-weight: 600; cursor: pointer; transition: background .2s; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; }
.btn-tbl-edit:hover { background: var(--navy-lt); color: #fff; text-decoration: none; }
.btn-tbl-print { background: #6c757d; color: #fff; border: none; border-radius: 6px; padding: 4px 10px; font-size: 12px; font-weight: 600; cursor: pointer; transition: background .2s; display: inline-flex; align-items: center; gap: 4px; }
.btn-tbl-print:hover { background: #495057; color: #fff; }
.btn-tbl-del { background: #dc3545; color: #fff; border: none; border-radius: 6px; padding: 4px 10px; font-size: 12px; font-weight: 600; cursor: pointer; transition: background .2s; display: inline-flex; align-items: center; gap: 4px; }
.btn-tbl-del:hover { background: #b91c1c; color: #fff; }
.caixa-form-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 6px; }
.caixa-form-actions .btn { min-width: 180px; }
.btn-tbl-anexo { background: #0d6efd; color: #fff; border: none; border-radius: 6px; padding: 4px 10px; font-size: 12px; font-weight: 600; cursor: pointer; transition: background .2s; display: inline-flex; align-items: center; gap: 4px; }
.btn-tbl-anexo:hover { background: #0a58ca; color: #fff; }
.anexo-upload-area { border: 2px dashed #dee2e6; border-radius: 10px; padding: 14px; text-align: center; cursor: pointer; transition: border-color .2s; background: #fafbfc; }
.anexo-upload-area:hover { border-color: var(--navy); background: #f0f6ff; }
@media (max-width: 991px) { .filter-grid { grid-template-columns: 1fr 1fr; } .kpi-row { grid-template-columns: 1fr 1fr; } }
@media (max-width: 575px) { .filter-grid { grid-template-columns: 1fr; } .kpi-row { grid-template-columns: 1fr; } .map-wrapper { padding: 14px 12px 80px; } .caixa-form-actions .btn { width: 100%; } }
</style>

<div class="page-content--bgf7">
<div class="map-wrapper">

    <div class="bc-bar">
        <a href="index"><i class="fas fa-home"></i> Home</a>
        <span class="sep">/</span>
        <span>Financeiro: Caixa</span>
    </div>

    <?php require_once __DIR__ . '/components/reserva-ui-icons.php'; ?>
    <?php $flash = getFlash(); if ($flash): ?>
    <div class="alert alert-<?= caixaEsc($flash['type']) ?> alert-dismissible fade show" role="alert">
        <?= caixaEsc($flash['msg']) ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    </div>
    <?php endif; ?>

    <!-- KPIs -->
    <div class="kpi-row">
        <div class="kpi-card">
            <div class="kpi-icon trans"><i class="fas fa-exchange-alt"></i></div>
            <div><div class="kpi-label">Transações</div><div class="kpi-value" id="caixaTotalRegistros"><?= $totalTransacoes ?></div></div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon valor"><i class="fas fa-dollar-sign"></i></div>
            <div><div class="kpi-label">Valor Total</div><div class="kpi-value green" id="caixaValorTotal">R$ <?= number_format($valorTotal, 2, ',', '.') ?></div></div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon periodo"><i class="fas fa-calendar-alt"></i></div>
            <div>
                <div class="kpi-label">Período</div>
                <div class="kpi-value period">
                    <?php if ($modoPesquisa): ?>
                        <?= date('d/m/Y', strtotime($filtros['datavencimentoinicial'])) ?> — <?= date('d/m/Y', strtotime($filtros['datavencimentofinal'])) ?>
                    <?php else: ?>
                        Hoje — <?= date('d/m/Y') ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs: Nova transação | Consultar -->
    <div class="filter-card" style="padding-bottom:0;">
        <ul class="nav nav-tabs" style="margin: 0 -26px; padding: 0 26px; border-bottom: none;">
            <li class="nav-item">
                <a class="nav-link <?= $abaAtiva === 'nova' ? 'active' : '' ?>" data-toggle="tab" href="#tabNova" role="tab">
                    <i class="fas fa-plus" style="margin-right:5px;"></i> Nova transação
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $abaAtiva === 'consultar' ? 'active' : '' ?>" data-toggle="tab" href="#tabConsultar" role="tab">
                    <i class="fas fa-search" style="margin-right:5px;"></i> Consultar
                </a>
            </li>
        </ul>
    </div>

    <div class="tab-content" style="margin-top:-12px;">
    <div class="tab-pane fade <?= $abaAtiva === 'nova' ? 'show active' : '' ?>" id="tabNova" role="tabpanel">
    <div class="filter-card">
<form action="" method="post" id="formNovaTransacao" enctype="multipart/form-data">
<div class="rui-section">
<div class="rui-section-title">Datas</div>
<div class="rui-grid-3">
<div class="rui-field">
<label for="datavencimento">Vencimento</label>
<input type="date" class="form-control" name="datavencimento" id="datavencimento" value="<?= $hoje ?>" required>
</div>
<div class="rui-field">
<label for="datapagamento">Pagamento</label>
<input type="date" class="form-control" name="datapagamento" id="datapagamento">
</div>
<div class="rui-field">
<label for="datacompetencia">Competência</label>
<input type="date" class="form-control" name="datacompetencia" id="datacompetencia" value="<?= $hoje ?>">
</div>
</div>
</div>
<div class="rui-section">
<div class="rui-section-title">Identificação</div>
<div class="rui-grid-<?= $podeEscolherResponsavel ? '4' : '3' ?>">
<div class="rui-field">
<label for="nome">Nome</label>
<input type="text" name="nome" id="nome" class="form-control" required>
</div>
<div class="rui-field">
<label for="documento">Descrição</label>
<input type="text" name="documento" id="documento" class="form-control">
</div>
<div class="rui-field">
<label for="favorecido">Fornecedor</label>
<select class="form-control" name="favorecido" id="favorecido" required>
<option value="" disabled selected>Selecione</option>
<?php foreach ($listaFornecedores as $item): ?>
<option value="<?= (int)$item->id ?>"><?= caixaEsc($item->fullname) ?></option>
<?php endforeach; ?>
</select>
</div>
<?php if ($podeEscolherResponsavel): ?>
<div class="rui-field">
<label for="responsavel">Responsável</label>
<select class="form-control" name="responsavel" id="responsavel" required>
<option value="<?= (int)$_SESSION['id'] ?>"><?= caixaEsc($_SESSION['nome'] ?? 'Eu') ?></option>
<?php foreach ($listaUsuarios as $item): ?>
<?php
$mostrarUsuario = in_array((int)$_SESSION['id'], [208, 207, 30], true) || in_array((int)$item->id, $usersIds, true);
if (!$mostrarUsuario) continue;
?>
<option value="<?= (int)$item->id ?>"><?= caixaEsc(strtoupper($item->firstname . ' ' . $item->lastname)) ?></option>
<?php endforeach; ?>
</select>
</div>
<?php else: ?>
<input type="hidden" name="responsavel" value="<?= (int)$_SESSION['id'] ?>">
<?php endif; ?>
</div>
</div>
<div class="rui-section">
<div class="rui-section-title">Classificação</div>
<div class="rui-grid-4">
<div class="rui-field">
<label for="empresa">Empresa</label>
<select required class="form-control" name="empresa" id="empresa">
<option value="" disabled selected>Selecione</option>
<?php foreach ($listaEmpresas as $item): ?>
<option value="<?= (int)$item->id ?>"><?= caixaEsc($item->fullname) ?></option>
<?php endforeach; ?>
</select>
</div>
<div class="rui-field">
<label for="tipo">Tipo</label>
<select class="form-control" name="tipo" id="tipo" required>
<option value="" disabled selected>Selecione</option>
<?php foreach ($listaTipoCaixa as $item): ?>
<option value="<?= (int)$item->id ?>"><?= caixaEsc($item->name) ?></option>
<?php endforeach; ?>
</select>
</div>
<div class="rui-field">
<label for="contacorrente">Conta corrente</label>
<select class="form-control" name="contacorrente" id="contacorrente" required>
<option value="" disabled selected>Selecione</option>
<?php foreach ($listaContaCorrente as $item): ?>
<option value="<?= (int)$item->id ?>"><?= caixaEsc($item->name) ?></option>
<?php endforeach; ?>
</select>
</div>
<div class="rui-field">
<label for="planocontas">Plano de contas</label>
<select class="form-control" name="planocontas" id="planocontas" required>
<option value="" disabled selected>Selecione</option>
<?php foreach ($listaPlanoContas as $item): ?>
<option value="<?= (int)$item->id ?>"><?= caixaEsc($item->name) ?></option>
<?php endforeach; ?>
</select>
</div>
</div>
</div>
<div class="rui-section">
<div class="rui-section-title">Valor e status</div>
<div class="rui-grid-2">
<div class="rui-field">
<label for="valor">Valor da transação</label>
<div class="input-group">
<div class="input-group-prepend"><span class="input-group-text">R$</span></div>
<input type="text" class="form-control" name="valor" id="valor" placeholder="0,00" autocomplete="off" inputmode="numeric" required>
</div>
</div>
<div class="rui-field">
<label for="status">Status</label>
<select class="form-control" name="status" id="status" required>
<option value="" disabled selected>Selecione</option>
<?php foreach ($listaStatus as $item): ?>
<option value="<?= (int)$item->id ?>"><?= caixaEsc($item->nameinvoice) ?></option>
<?php endforeach; ?>
</select>
</div>
</div>
<div class="rui-field" style="margin-top:14px;">
<label for="novaTxAnexo">Anexo <small style="color:#6c757d;font-weight:400">(PDF ou imagem, opcional — máx. 10 MB)</small></label>
<button type="button" class="anexo-upload-area" style="width:100%;border:2px dashed #dee2e6;" onclick="document.getElementById('novaTxAnexo').click()">
    <i class="fas fa-paperclip" style="font-size:18px;color:#adb5bd;"></i>
    <div style="margin-top:4px;font-size:13px;color:#6c757d;">Clique para selecionar arquivo</div>
    <div style="font-size:11px;color:#adb5bd;margin-top:2px;">PDF, JPG, JPEG, PNG</div>
</button>
<input type="file" name="anexo" id="novaTxAnexo" accept=".pdf,.jpg,.jpeg,.png" style="display:none;">
<div id="novaTxAnexoPreview" style="margin-top:8px;"></div>
</div>
<div class="caixa-form-actions">
<button class="btn btn-success" name="novatransacao" type="submit">
    <i class="fas fa-save"></i> Incluir transação
</button>
<button class="btn btn-outline-secondary" type="reset" onclick="document.getElementById('novaTxAnexoPreview').innerHTML=''">
    <i class="fas fa-undo"></i> Limpar formulário
</button>
</div>
</div>
</form>
    </div><!-- /filter-card nova -->
    </div><!-- /tabNova -->

    <div class="tab-pane fade <?= $abaAtiva === 'consultar' ? 'show active' : '' ?>" id="tabConsultar" role="tabpanel">

        <!-- Filtros -->
        <div class="filter-card" style="margin-top:12px;">
            <div class="fc-title"><i class="fas fa-filter"></i> Filtros</div>
            <form action="" method="post" id="formPesquisar">
                <div class="filter-grid">
                    <div>
                        <label for="datavencimentoinicial">Vencimento inicial</label>
                        <input value="<?= caixaEsc($filtros['datavencimentoinicial']) ?>" type="date" class="form-control" name="datavencimentoinicial" id="datavencimentoinicial">
                    </div>
                    <div>
                        <label for="datavencimentofinal">Vencimento final</label>
                        <input value="<?= caixaEsc($filtros['datavencimentofinal']) ?>" type="date" class="form-control" name="datavencimentofinal" id="datavencimentofinal">
                    </div>
                    <div>
                        <label for="idempresa">Empresa</label>
                        <select class="form-control" name="idempresa" id="idempresa">
                            <option value="0">Todas</option>
                            <?php foreach ($listaEmpresas as $item): ?>
                            <option value="<?= (int)$item->id ?>" <?= (int)$filtros['idempresa'] === (int)$item->id ? 'selected' : '' ?>><?= caixaEsc($item->fullname) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="favorecido_pesquisa">Favorecido</label>
                        <select class="form-control" name="favorecido" id="favorecido_pesquisa">
                            <option value="0">Todos</option>
                            <?php foreach ($listaFornecedores as $item): ?>
                            <option value="<?= (int)$item->id ?>" <?= (int)$filtros['favorecido'] === (int)$item->id ? 'selected' : '' ?>><?= caixaEsc($item->fullname) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="nomepesquisa">Nome</label>
                        <input value="<?= caixaEsc($filtros['nomepesquisa']) ?>" type="text" class="form-control" name="nomepesquisa" id="nomepesquisa" placeholder="Buscar por nome">
                    </div>
                    <div>
                        <label for="nrecibo">Nº recibo</label>
                        <input value="<?= caixaEsc($filtros['nrecibo']) ?>" type="number" class="form-control" name="nrecibo" id="nrecibo" placeholder="ID da transação">
                    </div>
                    <div class="span-2" style="display:flex;align-items:flex-end;gap:10px;">
                        <button type="submit" name="pesquisartransacao" class="btn-nav primary">
                            <i class="fas fa-search"></i> Pesquisar
                        </button>
                        <button type="button" class="btn-nav" id="btnHoje">
                            <i class="fas fa-calendar-day"></i> Vencimentos de hoje
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tabela de resultados -->
        <div class="results-card">
            <div class="results-header">
                <div class="results-title">
                    <i class="fas fa-list-alt"></i> Resultados
                    <span class="results-count"><?= $totalTransacoes ?></span>
                </div>
                <div class="period-label"><?= $modoPesquisa ? 'Resultados filtrados' : 'Vencimentos de hoje' ?></div>
            </div>
            <div class="table-responsive">
                <table id="caixa-table" class="table table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>Nº</th>
                            <th>Vencimento</th>
                            <th>Pagamento</th>
                            <th>Nome</th>
                            <th>Descrição</th>
                            <th>Favorecido</th>
                            <th>Valor</th>
                            <th>Situação</th>
                            <th>Empresa</th>
                            <th>Tipo</th>
                            <th>Conta</th>
                            <th>Plano</th>
                            <th>Competência</th>
                            <th class="caixa-col-acoes">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($registroCaixa as $item): ?>
                    <tr class="caixa-row-click" data-id="<?= (int)$item->id ?>">
                        <td><a class="caixa-id-link" href="editar-transacao?idtransacao=<?= (int)$item->id ?>">#<?= (int)$item->id ?></a></td>
                        <td><?= date('d/m/Y', strtotime($item->datevencimento)) ?></td>
                        <td><?= $item->datepagamento ? date('d/m/Y', strtotime($item->datepagamento)) : '—' ?></td>
                        <td><?= caixaEsc($item->nome) ?></td>
                        <td><?= caixaEsc($item->descricao) ?></td>
                        <td><?= caixaEsc($item->fornecedor) ?></td>
                        <td data-order="<?= (float)$item->valor ?>"><strong>R$ <?= number_format((float)$item->valor, 2, ',', '.') ?></strong></td>
                        <td><span class="caixa-badge <?= caixaStatusClass($item->idstatus ?? 0) ?>"><?= caixaEsc($item->situacao) ?></span></td>
                        <td><?= caixaEsc($item->empresa) ?></td>
                        <td><?= caixaEsc($item->tipo) ?></td>
                        <td><?= caixaEsc($item->conta) ?></td>
                        <td><?= caixaEsc($item->plano) ?></td>
                        <td><?= $item->datecompetencia ? date('d/m/Y', strtotime($item->datecompetencia)) : '—' ?></td>
                        <td class="caixa-col-acoes">
                            <div class="caixa-actions">
                                <a href="editar-transacao?idtransacao=<?= (int)$item->id ?>" class="btn-tbl-edit" title="Editar">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                                <form action="./relatorio/recibo-transacao.php" target="_blank" method="post" style="margin:0">
                                    <input type="hidden" name="idtransacao" value="<?= (int)$item->id ?>">
                                    <button type="submit" class="btn-tbl-print" title="Recibo">
                                        <i class="fas fa-print"></i> Recibo
                                    </button>
                                </form>
                                <?php if (!empty($item->anexo)):
                                    $cExt = strtolower(pathinfo($item->anexo, PATHINFO_EXTENSION));
                                    $cUrl = 'uploads/transacoes/' . rawurlencode($item->anexo);
                                ?>
                                <button type="button" class="btn-tbl-anexo" title="Ver anexo"
                                    onclick="caixaAbrirAnexo('<?= caixaEsc($cUrl) ?>','<?= $cExt === 'pdf' ? 'pdf' : 'img' ?>')">
                                    <i class="fas fa-paperclip"></i> Anexo
                                </button>
                                <?php endif; ?>
                                <form action="" method="post" class="form-remover" style="margin:0">
                                    <input type="hidden" name="nometransacao" value="<?= caixaEsc($item->nome) ?>">
                                    <input type="hidden" name="idtransacao" value="<?= (int)$item->id ?>">
                                    <button type="submit" name="removertransacao" class="btn-tbl-del" title="Remover">
                                        <i class="fas fa-trash"></i> Remover
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div><!-- /tabConsultar -->
    </div><!-- /tab-content -->

</div><!-- /map-wrapper -->
</div><!-- /page-content -->

<script>
function formatarMoedaInput(el){
    var d=el.value.replace(/\D/g,'');
    if(!d){el.value='';return;}
    if(d.length>15)d=d.slice(0,15);
    while(d.length<3)d='0'+d;
    var dec=d.slice(-2),inteiro=d.slice(0,-2).replace(/^0+(?=\d)/,'')||'0';
    el.value=inteiro.replace(/\B(?=(\d{3})+(?!\d))/g,'.')+','+dec;
}
function bindMoedaInput(el,selecionarAoFocar){
    if(!el)return;
    el.addEventListener('focus',function(){
        if(selecionarAoFocar&&el.value){el.select();}
    });
    el.addEventListener('input',function(){formatarMoedaInput(el);});
}
document.addEventListener('DOMContentLoaded', function () {
    bindMoedaInput(document.getElementById('valor'),false);
    document.querySelectorAll('.form-remover').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (!confirm('Deseja remover esta transação? Esta ação não pode ser desfeita.')) {
                e.preventDefault();
            }
        });
        form.addEventListener('click', function (e) { e.stopPropagation(); });
    });
    document.querySelectorAll('#caixa-table .caixa-actions a, #caixa-table .caixa-actions button, #caixa-table .caixa-actions form').forEach(function (el) {
        el.addEventListener('click', function (e) { e.stopPropagation(); });
    });
    document.querySelectorAll('#caixa-table tbody tr.caixa-row-click').forEach(function (row) {
        row.addEventListener('click', function () {
            var id = row.getAttribute('data-id');
            if (id) { window.location.href = 'editar-transacao?idtransacao=' + id; }
        });
    });
    if (window.jQuery && jQuery.fn.DataTable && document.getElementById('caixa-table')) {
        jQuery('#caixa-table').DataTable({
            dom: '<"d-flex justify-content-between align-items-center mb-2"Bf>rtip',
            buttons: [
                { extend: 'excelHtml5', text: '<i class="fas fa-file-excel"></i> Excel',   className: 'btn btn-sm btn-success mr-1' },
                { extend: 'csvHtml5',   text: '<i class="fas fa-file-csv"></i> CSV',       className: 'btn btn-sm btn-secondary mr-1' },
                { extend: 'print',      text: '<i class="fas fa-print"></i> Imprimir',     className: 'btn btn-sm btn-dark mr-1', orientation: 'landscape', pageSize: 'LEGAL' },
            ],
            pageLength: 50,
            order: [[3, 'asc'], [4, 'asc']],
            language: {
                search:      'Buscar:',
                lengthMenu:  'Exibir _MENU_ por página',
                info:        '_START_–_END_ de _TOTAL_',
                paginate:    { first: '«', last: '»', next: '›', previous: '‹' },
                zeroRecords: 'Nenhum registro encontrado',
                infoEmpty:   'Sem registros',
            },
            columnDefs: [{ orderable: false, targets: [13] }],
        });
    }
    var btnHoje = document.getElementById('btnHoje');
    if (btnHoje) {
        btnHoje.addEventListener('click', function () { window.location.href = 'caixa?hoje=1'; });
    }
    var novaTxAnexo = document.getElementById('novaTxAnexo');
    if (novaTxAnexo) {
        novaTxAnexo.addEventListener('change', function () {
            txPreviewAnexo(this, document.getElementById('novaTxAnexoPreview'));
        });
    }
});
function txPreviewAnexo(input, previewEl) {
    previewEl.innerHTML = '';
    if (!input.files || !input.files[0]) { return; }
    var file = input.files[0];
    var ext  = file.name.split('.').pop().toLowerCase();
    if (['jpg','jpeg','png'].indexOf(ext) !== -1) {
        var reader = new FileReader();
        reader.onload = function (e) {
            previewEl.innerHTML = '<img src="' + e.target.result + '" style="max-width:100%;max-height:180px;border-radius:8px;border:1px solid #dee2e6;" alt="Preview do anexo">';
        };
        reader.readAsDataURL(file);
    } else if (ext === 'pdf') {
        var mb = (file.size / 1024 / 1024).toFixed(2);
        previewEl.innerHTML = '<div style="display:flex;align-items:center;gap:8px;padding:8px 12px;background:#fff5f5;border:1px solid #fecaca;border-radius:8px;font-size:13px;"><i class="fas fa-file-pdf" style="color:#dc3545;font-size:18px;"></i><span>' + file.name + ' (' + mb + ' MB)</span></div>';
    }
}
function caixaAbrirAnexo(url, tipo) {
    if (tipo === 'pdf') {
        window.open(url, '_blank');
        return;
    }
    document.getElementById('overlayCaixaAnexoBody').innerHTML = '<img src="' + url + '" style="max-width:100%;max-height:72vh;" alt="Anexo">';
    var overlay = document.getElementById('overlayCaixaAnexo');
    overlay.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}
function fecharOverlayCaixaAnexo() {
    document.getElementById('overlayCaixaAnexo').style.display = 'none';
    document.body.style.overflow = '';
}
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') { fecharOverlayCaixaAnexo(); }
});
</script>
<!-- Overlay visualizar anexo (não usa classe Bootstrap .modal para evitar auto-show do footer) -->
<div id="overlayCaixaAnexo" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:1055;align-items:center;justify-content:center;" onclick="if(event.target===this)fecharOverlayCaixaAnexo()" aria-modal="true" role="dialog" aria-label="Anexo da Transação">
    <div style="background:#fff;border-radius:10px;max-width:840px;width:92%;max-height:90vh;overflow:auto;box-shadow:0 8px 32px rgba(0,0,0,.28);">
        <div style="padding:14px 18px;border-bottom:1px solid #dee2e6;display:flex;align-items:center;justify-content:space-between;">
            <strong>Anexo da Transação</strong>
            <button type="button" onclick="fecharOverlayCaixaAnexo()" style="background:none;border:none;font-size:22px;line-height:1;cursor:pointer;color:#6c757d;" aria-label="Fechar">&times;</button>
        </div>
        <div style="padding:16px;text-align:center;" id="overlayCaixaAnexoBody"></div>
    </div>
</div>
<?php require_once 'footer.php'; ?>
