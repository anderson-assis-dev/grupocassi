<?php
$creditoGerente = creditoUsuarioGerente();
?>
<h4 style="margin-top:20px;">Adicionar Crédito</h4>
<form action="" method="post" enctype="multipart/form-data">
    <input type="hidden" name="desc" value="Crédito Pago">
    <div class="col-md-<?= $creditoGerente ? '3' : '6' ?> pull-left">
        <strong><label for="valordocredito">Valor do Crédito</label></strong>
        <div class="input-group mb-3">
            <div class="input-group-prepend" style="width:100%;">
                <span class="input-group-text">R$</span>
                <input type="text" onKeyPress="return(moeda(this,'.',',',event))"
                    name="valordocredito" id="valordocredito" class="form-control" required>
            </div>
        </div>
    </div>
    <?php if ($creditoGerente): ?>
        <div class="col-md-3 pull-left">
            <strong><label for="datacredito">Data do Pagamento</label></strong>
            <input type="date" name="datacredito" id="datacredito" class="form-control" required>
        </div>
        <div class="col-md-3 pull-left">
            <strong><label for="responsavel">Responsável</label></strong>
            <select class="form-control" name="responsavel" id="responsavel" required>
                <?php foreach ($dados_buscarResponsavel_todos as $item_usuario): ?>
                    <option value="<?= (int)$item_usuario->id ?>">
                        <?= htmlspecialchars($item_usuario->firstname . ' ' . $item_usuario->lastname, ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
                <option value="0" selected>Selecione</option>
            </select>
        </div>
    <?php else: ?>
        <input type="hidden" name="responsavel" value="<?= (int)$_SESSION['id'] ?>">
        <input type="hidden" name="datacredito" value="<?= date('Y-m-d') ?>">
    <?php endif; ?>
    <div class="col-md-<?= $creditoGerente ? '3' : '6' ?> pull-right">
        <strong><label for="ccfp">Forma de Pagamento</label></strong>
        <select class="form-control" name="ccfp" id="ccfp" required>
            <?php foreach ($registroCc as $itemC): ?>
                <option value="<?= (int)$itemC->id ?>"><?= htmlspecialchars($itemC->name, ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
            <option value="0" selected>Selecione</option>
        </select>
    </div>
    <div class="container-fluid pull-left">
        <input type="hidden" name="voucher" value="<?= htmlspecialchars($dadosGerais['numbervoucher'], ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="idcliente" value="<?= (int)$dadosGerais['idcliente'] ?>">
        <div style="margin-bottom:10px;margin-top:6px;">
            <label style="font-size:12px;font-weight:700;color:#6c757d;display:block;margin-bottom:4px;">Anexo <small style="font-weight:400;">(PDF ou imagem, opcional — máx. 10 MB)</small></label>
            <div style="border:2px dashed #dee2e6;border-radius:8px;padding:8px 12px;text-align:center;cursor:pointer;background:#fafbfc;" onclick="document.getElementById('paxAnexoCreditoInput').click()">
                <i class="fas fa-paperclip" style="color:#adb5bd;"></i>
                <span style="font-size:12px;color:#6c757d;margin-left:5px;">Clique para selecionar arquivo</span>
                <span style="font-size:11px;color:#adb5bd;display:block;">PDF, JPG, JPEG, PNG</span>
            </div>
            <input type="file" name="anexo" id="paxAnexoCreditoInput" accept=".pdf,.jpg,.jpeg,.png" style="display:none;" onchange="paxPreviewAnexo(this,'paxAnexoCreditoPreview')">
            <div id="paxAnexoCreditoPreview" style="margin-top:6px;"></div>
        </div>
        <button type="submit" class="btn btn-success btn-lg" name="Addcredito">
            <svg><use href="#icon-save"></use></svg><span>Salvar Crédito</span>
        </button>
    </div>
</form>
