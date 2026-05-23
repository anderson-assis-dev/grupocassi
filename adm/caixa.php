<?php
require_once 'header.php';
require_once __DIR__ . '/includes/ref_cache.php';
require_once __DIR__ . '/includes/flash.php';
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
    return "SELECT c.id,c.datevencimento,c.nome,c.datecompetencia,c.datepagamento,c.descricao,
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
    header('location: editar-transacao?idtransacao=' . $pdo->lastInsertId());
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
.caixa-page .caixa-stats{display:grid;gap:14px;grid-template-columns:repeat(3,1fr);margin-bottom:22px}
.caixa-page .caixa-stat{background:#f8fafc;border:1px solid #e5e7eb;border-radius:14px;padding:16px 18px}
.caixa-page .caixa-stat-label{color:#64748b;font-size:12px;font-weight:700;letter-spacing:.04em;text-transform:uppercase}
.caixa-page .caixa-stat-value{color:#0f172a;font-size:24px;font-weight:800;margin-top:6px}
.caixa-page .caixa-stat-value--green{color:#0f7a49}
.caixa-page .caixa-badge{border-radius:999px;display:inline-block;font-size:11px;font-weight:700;padding:4px 10px}
.caixa-page .caixa-badge--pago{background:#dcfce7;color:#166534}
.caixa-page .caixa-badge--pendente{background:#fef3c7;color:#92400e}
.caixa-page .caixa-badge--cancelado{background:#fee2e2;color:#991b1b}
.caixa-page .caixa-badge--default{background:#e2e8f0;color:#475569}
.caixa-page .caixa-actions{display:flex;flex-wrap:wrap;gap:8px}
.caixa-page .caixa-table-wrap{margin-top:20px}
.caixa-page .caixa-toolbar{align-items:center;display:flex;flex-wrap:wrap;gap:10px;justify-content:space-between;margin-bottom:14px}
.caixa-page .caixa-toolbar small{color:#64748b;font-size:13px}
.caixa-page .caixa-form-actions{display:flex;flex-wrap:wrap;gap:10px;margin-top:6px}
.caixa-page .caixa-form-actions .btn{min-width:180px}
@media only screen and (max-width:767px){
.caixa-page .caixa-stats{grid-template-columns:1fr}
.caixa-page .caixa-form-actions .btn{width:100%}
}
</style>
<div class="page-content--bgf7 reserva-ui-page caixa-page">
<section class="au-breadcrumb2">
<div class="container">
<div class="row">
<div class="col-md-12">
<div class="au-breadcrumb-content">
<div class="au-breadcrumb-left">
<span class="au-breadcrumb-span">Navegação:</span>
<ul class="list-unstyled list-inline au-breadcrumb__list">
<li class="list-inline-item active"><a href="index">Home</a></li>
<li class="list-inline-item seprate"><span>/</span></li>
<li class="list-inline-item">Financeiro: Caixa</li>
</ul>
</div>
</div>
</div>
</div>
</div>
</section>
<div class="row">
<div class="container reserva-ui-container">
<div class="card card-outline-primary reserva-ui-card">
<div class="card-header reserva-ui-heading">
<div class="card-title">Caixa</div>
<small>Gestão de transações financeiras — vencimentos, pagamentos e recibos</small>
</div>
<div class="card-body">
<?php require_once __DIR__ . '/components/reserva-ui-icons.php'; ?>
<?php $flash = getFlash(); if ($flash): ?>
<div class="alert alert-<?= caixaEsc($flash['type']) ?> alert-dismissible fade show" role="alert">
<?= caixaEsc($flash['msg']) ?>
<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
</div>
<?php endif; ?>
<div class="caixa-stats">
<div class="caixa-stat">
<div class="caixa-stat-label">Transações</div>
<div class="caixa-stat-value" id="caixaTotalRegistros"><?= $totalTransacoes ?></div>
</div>
<div class="caixa-stat">
<div class="caixa-stat-label">Valor total</div>
<div class="caixa-stat-value caixa-stat-value--green" id="caixaValorTotal">R$ <?= number_format($valorTotal, 2, ',', '.') ?></div>
</div>
<div class="caixa-stat">
<div class="caixa-stat-label">Período</div>
<div class="caixa-stat-value" style="font-size:16px">
<?php if ($modoPesquisa): ?>
<?= date('d/m/Y', strtotime($filtros['datavencimentoinicial'])) ?> — <?= date('d/m/Y', strtotime($filtros['datavencimentofinal'])) ?>
<?php else: ?>
Hoje — <?= date('d/m/Y') ?>
<?php endif; ?>
</div>
</div>
</div>
<ul class="nav nav-tabs" role="tablist">
<li class="nav-item">
<a class="nav-link <?= $abaAtiva === 'nova' ? 'active' : '' ?>" data-toggle="tab" href="#tabNova" role="tab">
<svg class="tab-icon"><use href="#icon-plus"></use></svg><span>Nova transação</span>
</a>
</li>
<li class="nav-item">
<a class="nav-link <?= $abaAtiva === 'consultar' ? 'active' : '' ?>" data-toggle="tab" href="#tabConsultar" role="tab">
<svg class="tab-icon"><use href="#icon-invoice"></use></svg><span>Consultar</span>
</a>
</li>
</ul>
<div class="tab-content tabcontent-border">
<div class="tab-pane fade <?= $abaAtiva === 'nova' ? 'show active' : '' ?>" id="tabNova" role="tabpanel">
<form action="" method="post" id="formNovaTransacao">
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
<input type="text" class="form-control" name="valor" id="valor" placeholder="0,00" required>
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
<div class="caixa-form-actions">
<button class="btn btn-success" name="novatransacao" type="submit">
<svg><use href="#icon-save"></use></svg><span>Incluir transação</span>
</button>
<button class="btn btn-outline-primary" type="reset">
<svg><use href="#icon-refresh"></use></svg><span>Limpar formulário</span>
</button>
</div>
</div>
</form>
</div>
<div class="tab-pane fade <?= $abaAtiva === 'consultar' ? 'show active' : '' ?>" id="tabConsultar" role="tabpanel">
<form action="" method="post" id="formPesquisar">
<div class="rui-section">
<div class="rui-section-title">Filtros</div>
<div class="rui-grid-4">
<div class="rui-field">
<label for="datavencimentoinicial">Vencimento inicial</label>
<input value="<?= caixaEsc($filtros['datavencimentoinicial']) ?>" type="date" class="form-control" name="datavencimentoinicial" id="datavencimentoinicial">
</div>
<div class="rui-field">
<label for="datavencimentofinal">Vencimento final</label>
<input value="<?= caixaEsc($filtros['datavencimentofinal']) ?>" type="date" class="form-control" name="datavencimentofinal" id="datavencimentofinal">
</div>
<div class="rui-field">
<label for="idempresa">Empresa</label>
<select class="form-control" name="idempresa" id="idempresa">
<option value="0">Todas</option>
<?php foreach ($listaEmpresas as $item): ?>
<option value="<?= (int)$item->id ?>" <?= (int)$filtros['idempresa'] === (int)$item->id ? 'selected' : '' ?>><?= caixaEsc($item->fullname) ?></option>
<?php endforeach; ?>
</select>
</div>
<div class="rui-field">
<label for="favorecido_pesquisa">Favorecido</label>
<select class="form-control" name="favorecido" id="favorecido_pesquisa">
<option value="0">Todos</option>
<?php foreach ($listaFornecedores as $item): ?>
<option value="<?= (int)$item->id ?>" <?= (int)$filtros['favorecido'] === (int)$item->id ? 'selected' : '' ?>><?= caixaEsc($item->fullname) ?></option>
<?php endforeach; ?>
</select>
</div>
<div class="rui-field">
<label for="nomepesquisa">Nome</label>
<input value="<?= caixaEsc($filtros['nomepesquisa']) ?>" type="text" class="form-control" name="nomepesquisa" id="nomepesquisa" placeholder="Buscar por nome">
</div>
<div class="rui-field">
<label for="nrecibo">Nº recibo</label>
<input value="<?= caixaEsc($filtros['nrecibo']) ?>" type="number" class="form-control" name="nrecibo" id="nrecibo" placeholder="ID da transação">
</div>
<div class="rui-field rui-col-2">
<label>&nbsp;</label>
<div class="caixa-form-actions" style="margin-top:0">
<button type="submit" name="pesquisartransacao" class="btn btn-primary">
<svg><use href="#icon-invoice"></use></svg><span>Pesquisar</span>
</button>
<button type="button" class="btn btn-outline-primary" id="btnHoje">
<svg><use href="#icon-refresh"></use></svg><span>Vencimentos de hoje</span>
</button>
</div>
</div>
</div>
</div>
</form>
<div class="caixa-table-wrap">
<div class="caixa-toolbar">
<h4 style="margin:0!important">Resultados</h4>
<small><?= $modoPesquisa ? 'Exibindo resultados filtrados' : 'Exibindo vencimentos de hoje' ?></small>
</div>
<div class="table-responsive">
<table id="example23" class="table table-bordered">
<thead>
<tr>
<th>Nº</th>
<th>Vencimento</th>
<th>Pagamento</th>
<th>Competência</th>
<th>Nome</th>
<th>Descrição</th>
<th>Favorecido</th>
<th>Empresa</th>
<th>Tipo</th>
<th>Conta</th>
<th>Plano</th>
<th>Valor</th>
<th>Situação</th>
<th>Ações</th>
</tr>
</thead>
<tbody>
<?php foreach ($registroCaixa as $item): ?>
<tr>
<td><?= (int)$item->id ?></td>
<td><?= date('d/m/Y', strtotime($item->datevencimento)) ?></td>
<td><?= $item->datepagamento ? date('d/m/Y', strtotime($item->datepagamento)) : '—' ?></td>
<td><?= $item->datecompetencia ? date('d/m/Y', strtotime($item->datecompetencia)) : '—' ?></td>
<td><?= caixaEsc($item->nome) ?></td>
<td><?= caixaEsc($item->descricao) ?></td>
<td><?= caixaEsc($item->fornecedor) ?></td>
<td><?= caixaEsc($item->empresa) ?></td>
<td><?= caixaEsc($item->tipo) ?></td>
<td><?= caixaEsc($item->conta) ?></td>
<td><?= caixaEsc($item->plano) ?></td>
<td data-order="<?= (float)$item->valor ?>"><strong>R$ <?= number_format((float)$item->valor, 2, ',', '.') ?></strong></td>
<td><span class="caixa-badge <?= caixaStatusClass($item->idstatus ?? 0) ?>"><?= caixaEsc($item->situacao) ?></span></td>
<td>
<div class="caixa-actions">
<form action="./editar-transacao.php" method="post">
<input type="hidden" name="idtransacao" value="<?= (int)$item->id ?>">
<button type="submit" name="editartransacao" class="action-icon-button" title="Editar">
<svg><use href="#icon-edit"></use></svg><span>Editar</span>
</button>
</form>
<form action="./relatorio/recibo-transacao.php" target="_blank" method="post">
<input type="hidden" name="idtransacao" value="<?= (int)$item->id ?>">
<button type="submit" class="action-icon-button" title="Recibo">
<svg><use href="#icon-print"></use></svg><span>Recibo</span>
</button>
</form>
<form action="" method="post" class="form-remover">
<input type="hidden" name="nometransacao" value="<?= caixaEsc($item->nome) ?>">
<input type="hidden" name="idtransacao" value="<?= (int)$item->id ?>">
<button type="submit" name="removertransacao" class="action-icon-button" title="Remover" style="color:#b91c1c">
<svg><use href="#icon-trash"></use></svg><span>Remover</span>
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
</div>
</div>
</div>
</div>
</div>
</div>
</div>
<script>
function moeda(a,e,r,t){
let n="",h=j=0,u=tamanho2=0,l=ajd2="",o=window.Event?t.which:t.keyCode;
if(13==o||8==o)return!0;
if(n=String.fromCharCode(o),-1=="0123456789".indexOf(n))return!1;
for(u=a.value.length,h=0;h<u&&("0"==a.value.charAt(h)||a.value.charAt(h)==r);h++);
for(l="";h<u;h++)-1!="0123456789".indexOf(a.value.charAt(h))&&(l+=a.value.charAt(h));
if(l+=n,0==(u=l.length)&&(a.value=""),1==u&&(a.value="0"+r+"0"+l),2==u&&(a.value="0"+r+l),u>2){
for(ajd2="",j=0,h=u-3;h>=0;h--)3==j&&(ajd2+=e,j=0),ajd2+=l.charAt(h),j++;
for(a.value="",tamanho2=ajd2.length,h=tamanho2-1;h>=0;h--)a.value+=ajd2.charAt(h);
a.value+=r+l.substr(u-2,u)
}
return!1
}
document.addEventListener('DOMContentLoaded',function(){
var valorInput=document.getElementById('valor');
if(valorInput){
valorInput.addEventListener('keypress',function(e){return moeda(this,'.',',',e)});
}
document.querySelectorAll('.form-remover').forEach(function(form){
form.addEventListener('submit',function(e){
if(!confirm('Deseja remover esta transação? Esta ação não pode ser desfeita.')){
e.preventDefault();
}
});
});
var btnHoje=document.getElementById('btnHoje');
if(btnHoje){
btnHoje.addEventListener('click',function(){
window.location.href='caixa?hoje=1';
});
}
});
</script>
<?php require_once 'footer.php'; ?>
