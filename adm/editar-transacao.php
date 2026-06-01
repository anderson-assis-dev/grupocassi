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
    $nomeAnexo = transacaoUploadAnexo($idTransacao);
    transacaoAtualizar($pdo, array_merge($_POST, ['idtransacao' => $idTransacao, 'anexo' => $nomeAnexo]));
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
.tx-page .anexo-upload-area{border:2px dashed #dee2e6;border-radius:10px;padding:14px;text-align:center;cursor:pointer;transition:border-color .2s,background .2s;background:#fafbfc;width:100%}
.tx-page .anexo-upload-area:hover{border-color:#1e4770;background:#f0f6ff}
.tx-page .anexo-existing{background:#f0f6ff;border:1px solid #cce0ff;border-radius:8px;padding:10px 14px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-bottom:10px}
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
<form action="" method="post" id="formEditarTransacao" enctype="multipart/form-data">
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
<input value="<?= transacaoEsc($valorFormatado) ?>" type="text" class="form-control" name="valor" id="valor" autocomplete="off" inputmode="numeric" required>
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
</div>
</div>
<div class="rui-section">
<div class="rui-section-title">Anexo</div>
<?php
$extAnexo = !empty($registro['anexo']) ? strtolower(pathinfo($registro['anexo'], PATHINFO_EXTENSION)) : '';
$urlAnexo = !empty($registro['anexo']) ? 'uploads/transacoes/' . rawurlencode($registro['anexo']) : '';
?>
<?php if (!empty($registro['anexo'])): ?>
<div class="anexo-existing">
    <div style="display:flex;align-items:center;gap:10px;">
        <i class="fas fa-<?= $extAnexo === 'pdf' ? 'file-pdf' : 'file-image' ?>" style="font-size:22px;color:<?= $extAnexo === 'pdf' ? '#dc3545' : '#0d6efd' ?>;"></i>
        <div>
            <div style="font-size:13px;font-weight:600;">Anexo atual</div>
            <div style="font-size:11px;color:#6c757d;"><?= transacaoEsc($registro['anexo']) ?></div>
        </div>
    </div>
    <?php if ($extAnexo === 'pdf'): ?>
    <a href="<?= transacaoEsc($urlAnexo) ?>" target="_blank" class="btn btn-sm btn-outline-danger">
        <i class="fas fa-file-pdf"></i> Abrir PDF
    </a>
    <?php else: ?>
    <button type="button" class="btn btn-sm btn-outline-primary"
        onclick="abrirOverlayTxAnexo('<?= transacaoEsc($urlAnexo) ?>')">
        <i class="fas fa-eye"></i> Visualizar
    </button>
    <?php endif; ?>
</div>
<div style="font-size:12px;color:#6c757d;margin-bottom:6px;">Para substituir, selecione um novo arquivo abaixo:</div>
<?php else: ?>
<div style="font-size:13px;color:#6c757d;margin-bottom:8px;">Nenhum anexo cadastrado.</div>
<?php endif; ?>
<div class="rui-field">
<label for="editTxAnexo">Novo anexo <small style="color:#6c757d;font-weight:400">(PDF ou imagem, opcional — máx. 10 MB)</small></label>
<button type="button" class="anexo-upload-area" onclick="document.getElementById('editTxAnexo').click()">
    <i class="fas fa-paperclip" style="font-size:18px;color:#adb5bd;"></i>
    <div style="margin-top:4px;font-size:13px;color:#6c757d;">Clique para selecionar arquivo</div>
    <div style="font-size:11px;color:#adb5bd;margin-top:2px;">PDF, JPG, JPEG, PNG</div>
</button>
<input type="file" name="anexo" id="editTxAnexo" accept=".pdf,.jpg,.jpeg,.png" style="display:none;">
<div id="editTxAnexoPreview" style="margin-top:8px;"></div>
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
<svg><use href="#icon-print"></use></svg><span>Imprimir recibo</span>
</button>
</form>
</div>
</div>
</div>
</div>
</div>
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
document.addEventListener('DOMContentLoaded',function(){
    bindMoedaInput(document.getElementById('valor'),true);
    var editTxAnexo=document.getElementById('editTxAnexo');
    if(editTxAnexo){
        editTxAnexo.addEventListener('change',function(){
            txEditPreviewAnexo(this,document.getElementById('editTxAnexoPreview'));
        });
    }
});
function txEditPreviewAnexo(input,previewEl){
    previewEl.innerHTML='';
    if(!input.files||!input.files[0]){return;}
    var file=input.files[0];
    var ext=file.name.split('.').pop().toLowerCase();
    if(['jpg','jpeg','png'].indexOf(ext)!==-1){
        var reader=new FileReader();
        reader.onload=function(e){
            previewEl.innerHTML='<img src="'+e.target.result+'" style="max-width:100%;max-height:180px;border-radius:8px;border:1px solid #dee2e6;" alt="Preview do anexo">';
        };
        reader.readAsDataURL(file);
    } else if(ext==='pdf'){
        var mb=(file.size/1024/1024).toFixed(2);
        previewEl.innerHTML='<div style="display:flex;align-items:center;gap:8px;padding:8px 12px;background:#fff5f5;border:1px solid #fecaca;border-radius:8px;font-size:13px;"><i class="fas fa-file-pdf" style="color:#dc3545;font-size:18px;"></i><span>'+file.name+' ('+mb+' MB)</span></div>';
    }
}
function abrirOverlayTxAnexo(url){
    document.getElementById('overlayTxAnexoImg').src=url;
    document.getElementById('overlayTxAnexo').style.display='flex';
    document.body.style.overflow='hidden';
}
function fecharOverlayTxAnexo(){
    document.getElementById('overlayTxAnexo').style.display='none';
    document.getElementById('overlayTxAnexoImg').src='';
    document.body.style.overflow='';
}
document.addEventListener('keydown',function(e){if(e.key==='Escape'){fecharOverlayTxAnexo();}});
</script>
<!-- Overlay visualizar imagem do anexo (sem classe Bootstrap .modal para evitar auto-show do footer) -->
<div id="overlayTxAnexo" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:1055;align-items:center;justify-content:center;" onclick="if(event.target===this)fecharOverlayTxAnexo()">
    <div style="background:#fff;border-radius:10px;max-width:840px;width:92%;max-height:90vh;overflow:auto;box-shadow:0 8px 32px rgba(0,0,0,.28);">
        <div style="padding:14px 18px;border-bottom:1px solid #dee2e6;display:flex;align-items:center;justify-content:space-between;">
            <strong>Anexo da Transação</strong>
            <button type="button" onclick="fecharOverlayTxAnexo()" style="background:none;border:none;font-size:22px;line-height:1;cursor:pointer;color:#6c757d;" aria-label="Fechar">&times;</button>
        </div>
        <div style="padding:16px;text-align:center;">
            <img id="overlayTxAnexoImg" src="" style="max-width:100%;max-height:72vh;" alt="Anexo da transação">
        </div>
    </div>
</div>
<?php require_once 'footer.php'; ?>
