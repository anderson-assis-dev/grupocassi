<?php
$comissaoPendentes = comissaoServicosPendentes($dadosGerais, $registro, $listaServicos, $contadorDespesa);
if (count($comissaoPendentes) === 0) {
    return;
}
?>
<form action="relatorio/pdf-relatorio-comissao-agente.php" target="_blank" method="post" enctype="multipart/form-data">
    <div class="col-lg-4 pull-left">
        <strong><label for="nomeagente">Descrição</label></strong>
        <input style="margin-bottom:15px;" type="text" name="nomeagente" id="nomeagente" class="form-control" required>
    </div>
    <div class="col-lg-4 pull-left">
        <strong><label for="comissaoservico">Serviço</label></strong>
        <?php if (count($comissaoPendentes) === 1): ?>
            <input style="margin-bottom:15px;" type="text" name="comissaoservico" id="comissaoservico"
                class="form-control" value="<?= htmlspecialchars($comissaoPendentes[0]['nome'], ENT_QUOTES, 'UTF-8') ?>" readonly>
        <?php else: ?>
            <select style="margin-bottom:15px;" name="comissaoservico" id="comissaoservico" class="form-control" required>
                <?php foreach ($comissaoPendentes as $servico): ?>
                    <option value="<?= htmlspecialchars($servico['nome'], ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlspecialchars($servico['nome'], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>
    </div>
    <div class="col-lg-4 pull-right">
        <strong><label for="valoragente">Valor</label></strong>
        <div class="input-group mb-3">
            <div class="input-group-prepend">
                <span class="input-group-text">R$</span>
                <input required type="text" name="valoragente" onKeyPress="return(moeda(this,'.',',',event))"
                    id="valoragente" class="form-control">
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <input type="hidden" name="voucher" value="<?= htmlspecialchars($dadosGerais['numbervoucher'], ENT_QUOTES, 'UTF-8') ?>">
        <div style="margin-bottom:10px;">
            <label style="font-size:12px;font-weight:700;color:#6c757d;display:block;margin-bottom:4px;">Comprovante <small style="font-weight:400;">(PDF ou imagem, opcional — máx. 10 MB)</small></label>
            <input type="file" name="anexo" accept=".pdf,.jpg,.jpeg,.png" style="font-size:13px;" onchange="paxPreviewAnexo(this,'paxComPreview')">
            <div id="paxComPreview" style="margin-top:6px;"></div>
        </div>
        <button type="submit" class="btn btn-success btn-lg" name="comissaoagente" id="comissaoagente">
            <svg><use href="#icon-check"></use></svg><span>Confirmar Pagamento</span>
        </button>
    </div>
</form>
