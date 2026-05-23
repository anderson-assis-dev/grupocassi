<?php
require_once 'header.php';
require_once __DIR__ . '/includes/ref_cache.php';
require_once __DIR__ . '/includes/flash.php';
require_once __DIR__ . '/includes/transacao_helpers.php';
$pdo->exec("set names utf8");
$idTransacao = (int)($_POST['idtransacao'] ?? $_GET['idtransacao'] ?? 0);
if ($idTransacao <= 0) {
    setFlash('danger', 'Transação não informada.');
    header('location: caixa');
    exit;
}
if (isset($_POST['updatetransition'])) {
    transacaoAtualizar($pdo, array_merge($_POST, ['idtransacao' => $idTransacao]));
    setFlash('success', 'Transação atualizada com sucesso.');
    header('location: editar-transacao?idtransacao=' . $idTransacao);
    exit;
}
$registro = transacaoPorId($pdo, $idTransacao);
if (!$registro) {
    setFlash('danger', 'Transação não encontrada.');
    header('location: caixa');
    exit;
}
$listaFornecedores = refFornecedores($pdo);
$listaEmpresas = refEmpresasTodas($pdo);
$listaStatus = refStatusInvoice($pdo);
$listaPlanoContas = refPlanoContas($pdo);
$listaTipoCaixa = refTipoCaixa($pdo);
$listaContaCorrente = refContaCorrente($pdo);
$responsavel = trim(($registro['firstname'] ?? '') . ' ' . ($registro['lastname'] ?? ''));
$valorFormatado = transacaoValorFormatado($registro['valor']);
?>
<link href="../css/reserva-ui.css" rel="stylesheet" media="all">
<style>
.tx-page .tx-summary{display:grid;gap:14px;grid-template-columns:repeat(4,1fr);margin-bottom:22px}
.tx-page .tx-stat{background:#f8fafc;border:1px solid #e5e7eb;border-radius:14px;padding:16px 18px}
.tx-page .tx-stat-label{color:#64748b;font-size:12px;font-weight:700;letter-spacing:.04em;text-transform:uppercase}
.tx-page .tx-stat-value{color:#0f172a;font-size:18px;font-weight:800;margin-top:6px}
.tx-page .tx-stat-value--green{color:#0f7a49;font-size:22px}
.tx-page .tx-badge{border-radius:999px;display:inline-block;font-size:11px;font-weight:700;padding:4px 10px}
.tx-page .tx-badge--pago{background:#dcfce7;color:#166534}
.tx-page .tx-badge--pendente{background:#fef3c7;color:#92400e}
.tx-page .tx-badge--cancelado{background:#fee2e2;color:#991b1b}
.tx-page .tx-badge--default{background:#e2e8f0;color:#475569}
.tx-page .tx-form-actions{display:flex;flex-wrap:wrap;gap:10px;margin-top:6px}
.tx-page .tx-form-actions .btn{min-width:170px}
@media only screen and (max-width:767px){
.tx-page .tx-summary{grid-template-columns:1fr 1fr}
.tx-page .tx-form-actions .btn{width:100%}
}
</style>
<div class="page-content--bgf7 reserva-ui-page tx-page">
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
<li class="list-inline-item"><a href="caixa">Caixa</a></li>
<li class="list-inline-item seprate"><span>/</span></li>
<li class="list-inline-item">Transação #<?= (int)$registro['id'] ?></li>
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
<div class="card-title">Transação #<?= (int)$registro['id'] ?></div>
<small>Responsável: <?= transacaoEsc($responsavel) ?></small>
</div>
<div class="card-body">
<?php require_once __DIR__ . '/components/reserva-ui-icons.php'; ?>
<?php $flash = getFlash(); if ($flash): ?>
<div class="alert alert-<?= transacaoEsc($flash['type']) ?> alert-dismissible fade show" role="alert">
<?= transacaoEsc($flash['msg']) ?>
<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
</div>
<?php endif; ?>
<div class="tx-summary">
<div class="tx-stat">
<div class="tx-stat-label">Valor</div>
<div class="tx-stat-value tx-stat-value--green">R$ <?= transacaoEsc($valorFormatado) ?></div>
</div>
<div class="tx-stat">
<div class="tx-stat-label">Situação</div>
<div class="tx-stat-value" style="font-size:14px">
<span class="tx-badge <?= transacaoStatusClass($registro['stid'] ?? 0) ?>"><?= transacaoEsc($registro['situacao']) ?></span>
</div>
</div>
<div class="tx-stat">
<div class="tx-stat-label">Vencimento</div>
<div class="tx-stat-value" style="font-size:16px"><?= transacaoEsc(transacaoData($registro['datevencimento'])) ?></div>
</div>
<div class="tx-stat">
<div class="tx-stat-label">Pagamento</div>
<div class="tx-stat-value" style="font-size:16px"><?= transacaoEsc(transacaoData($registro['datepagamento'])) ?></div>
</div>
</div>
<form action="" method="post" id="formEditarTransacao">
<input type="hidden" name="idtransacao" value="<?= (int)$registro['id'] ?>">
<div class="rui-section">
<div class="rui-section-title">Datas</div>
<div class="rui-grid-3">
<div class="rui-field">
<label for="datavencimento">Vencimento</label>
<input type="date" class="form-control" value="<?= transacaoEsc($registro['datevencimento']) ?>" name="datavencimento" id="datavencimento" required>
</div>
<div class="rui-field">
<label for="datapagamento">Pagamento</label>
<input type="date" class="form-control" value="<?= transacaoEsc($registro['datepagamento']) ?>" name="datapagamento" id="datapagamento">
</div>
<div class="rui-field">
<label for="datacompetencia">Competência</label>
<input type="date" class="form-control" name="datacompetencia" value="<?= transacaoEsc($registro['datecompetencia']) ?>" id="datacompetencia">
</div>
</div>
</div>
<div class="rui-section">
<div class="rui-section-title">Identificação</div>
<div class="rui-grid-3">
<div class="rui-field">
<label for="nome">Nome</label>
<input type="text" name="nome" value="<?= transacaoEsc($registro['nome']) ?>" id="nome" class="form-control" required>
</div>
<div class="rui-field">
<label for="documento">Descrição</label>
<input type="text" name="documento" value="<?= transacaoEsc($registro['descricao']) ?>" id="documento" class="form-control">
</div>
<div class="rui-field">
<label for="favorecido">Favorecido</label>
<select class="form-control" name="favorecido" id="favorecido" required>
<?php foreach ($listaFornecedores as $item): ?>
<option value="<?= (int)$item->id ?>" <?= (int)$registro['forid'] === (int)$item->id ? 'selected' : '' ?>><?= transacaoEsc($item->fullname) ?></option>
<?php endforeach; ?>
</select>
</div>
</div>
</div>
<div class="rui-section">
<div class="rui-section-title">Classificação</div>
<div class="rui-grid-4">
<div class="rui-field">
<label for="empresa">Empresa</label>
<select required class="form-control" name="empresa" id="empresa">
<?php foreach ($listaEmpresas as $item): ?>
<option value="<?= (int)$item->id ?>" <?= (int)$registro['idempresa'] === (int)$item->id ? 'selected' : '' ?>><?= transacaoEsc($item->fullname) ?></option>
<?php endforeach; ?>
</select>
</div>
<div class="rui-field">
<label for="tipo">Tipo</label>
<select class="form-control" name="tipo" id="tipo" required>
<?php foreach ($listaTipoCaixa as $item): ?>
<option value="<?= (int)$item->id ?>" <?= (int)$registro['tipoid'] === (int)$item->id ? 'selected' : '' ?>><?= transacaoEsc($item->name) ?></option>
<?php endforeach; ?>
</select>
</div>
<div class="rui-field">
<label for="contacorrente">Conta corrente</label>
<select class="form-control" name="contacorrente" id="contacorrente" required>
<?php foreach ($listaContaCorrente as $item): ?>
<option value="<?= (int)$item->id ?>" <?= (int)$registro['contaid'] === (int)$item->id ? 'selected' : '' ?>><?= transacaoEsc($item->name) ?></option>
<?php endforeach; ?>
</select>
</div>
<div class="rui-field">
<label for="planocontas">Plano de contas</label>
<select class="form-control" name="planocontas" id="planocontas" required>
<?php foreach ($listaPlanoContas as $item): ?>
<option value="<?= (int)$item->id ?>" <?= (int)$registro['planoid'] === (int)$item->id ? 'selected' : '' ?>><?= transacaoEsc($item->name) ?></option>
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
<input value="<?= transacaoEsc($valorFormatado) ?>" type="text" class="form-control" name="valor" id="valor" required>
</div>
</div>
<div class="rui-field">
<label for="status">Status</label>
<select class="form-control" name="status" id="status" required>
<?php foreach ($listaStatus as $item): ?>
<option value="<?= (int)$item->id ?>" <?= (int)$registro['stid'] === (int)$item->id ? 'selected' : '' ?>><?= transacaoEsc($item->nameinvoice) ?></option>
<?php endforeach; ?>
</select>
</div>
</div>
<div class="tx-form-actions">
<button class="btn btn-success" name="updatetransition" type="submit">
<svg><use href="#icon-save"></use></svg><span>Atualizar transação</span>
</button>
<a href="caixa" class="btn btn-outline-primary">
<svg><use href="#icon-refresh"></use></svg><span>Voltar ao caixa</span>
</a>
</div>
</div>
</form>
<form action="./relatorio/recibo-transacao.php" target="_blank" method="post" class="tx-form-actions" style="margin-top:14px">
<input type="hidden" name="idtransacao" value="<?= (int)$registro['id'] ?>">
<button type="submit" class="btn btn-outline-success">
<svg><use href="#icon-print"></use></svg><span>Gerar recibo PDF</span>
</button>
</form>
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
});
</script>
<?php require_once 'footer.php'; ?>
