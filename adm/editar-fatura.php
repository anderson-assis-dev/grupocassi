<?php
require_once 'header.php';
require_once __DIR__ . '/includes/flash.php';

$idFatura = (int)($_POST['idfatura'] ?? 0);
if (!$idFatura) {
    header('Location: ./inicio-da-fatura');
    exit;
}

$flash = null;

if (isset($_POST['updatefatura'])) {
    $tarifa  = (float)str_replace(',', '.', str_replace('.', '', $_POST['total']));
    $credito = (float)str_replace(',', '.', str_replace('.', '', $_POST['credito']));
    $pdo->prepare(
        'UPDATE ct_fatura SET tarifa=?, credito=?, dateinput=?, situacao=?, dateoutput=? WHERE id=?'
    )->execute([$tarifa, $credito, $_POST['periodoinicial'], (int)$_POST['situacao'], $_POST['periodofinal'], $idFatura]);

    if ((int)$_POST['situacao'] === 0) {
        $pdo->prepare(
            'UPDATE ct_reserva SET idstatusinvoice=1 WHERE idcliente=? AND dateinput>=? AND dateinput<=?'
        )->execute([$_POST['idcliente'], $_POST['periodoinicial'], $_POST['periodofinal']]);
    } elseif ((int)$_POST['situacao'] === 1) {
        $pdo->prepare(
            'UPDATE ct_reserva SET idstatusinvoice=? WHERE idcliente=? AND dateinput>=? AND dateinput<=?'
        )->execute([(int)$_POST['status'], $_POST['idcliente'], $_POST['periodoinicial'], $_POST['periodofinal']]);
    }
    $flash = ['type' => 'success', 'msg' => 'Informações da fatura atualizadas.'];

} elseif (isset($_POST['inserirpagamento'])) {
    $valor = (float)str_replace(',', '.', str_replace('.', '', $_POST['valorecebido']));
    $pdo->prepare('INSERT INTO ct_faturadesc VALUES (DEFAULT, ?, ?, ?, ?)')->execute([
        $valor, $_POST['descricaopagamento'], $_POST['datapagamento'], $idFatura,
    ]);
    $flash = ['type' => 'success', 'msg' => 'Crédito inserido com sucesso.'];

} elseif (isset($_POST['atualizarpagamento'])) {
    $valor = (float)str_replace(',', '.', str_replace('.', '', $_POST['valorecebido']));
    $pdo->prepare(
        'UPDATE ct_faturadesc SET valor=?, descricao=?, datapagamento=? WHERE id=?'
    )->execute([$valor, $_POST['descricaopagamento'], $_POST['datapagamento'], (int)$_POST['idcredito']]);
    $flash = ['type' => 'success', 'msg' => 'Crédito atualizado com sucesso.'];

} elseif (isset($_POST['excluirpagamento'])) {
    $pdo->prepare('DELETE FROM ct_faturadesc WHERE id=?')->execute([(int)$_POST['idcredito']]);
    $flash = ['type' => 'warning', 'msg' => 'Crédito removido.'];
}

// fetch current state
$st = $pdo->prepare('SELECT * FROM ct_fatura WHERE id=?');
$st->execute([$idFatura]);
$dadosFatura = $st->fetch(PDO::FETCH_ASSOC);

if (!$dadosFatura) {
    echo '<div class="alert alert-danger m-4">Fatura não encontrada.</div>';
    require_once 'footer.php';
    exit;
}

$st = $pdo->prepare('SELECT * FROM ct_faturadesc WHERE id_fatura=? ORDER BY datapagamento ASC');
$st->execute([$idFatura]);
$creditos = $st->fetchAll(PDO::FETCH_ASSOC);
$totalCreditos = array_sum(array_column($creditos, 'valor'));
?>
<style>
:root { --navy: #1e4770; --navy-lt: #2a5f96; }
.map-wrapper { padding: 20px 20px 80px; }
.bc-bar { padding: 0 0 16px; font-size: 13px; color: #6c757d; }
.bc-bar a { color: var(--navy); font-weight: 600; text-decoration: none; }
.bc-bar a:hover { text-decoration: underline; }
.bc-bar .sep { margin: 0 6px; color: #ccc; }

.ef-card { background: #fff; border-radius: 12px; box-shadow: 0 2px 14px rgba(0,0,0,.07); overflow: hidden; margin-bottom: 24px; }
.ef-card-hd { background: linear-gradient(135deg, var(--navy), var(--navy-lt)); color: #fff; padding: 15px 22px; display: flex; align-items: center; gap: 9px; font-weight: 700; font-size: 15px; }
.ef-card-hd i { font-size: 16px; opacity: .85; }
.ef-body { padding: 22px 24px; }

.ef-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px; margin-bottom: 20px; }
.ef-grid-wide { grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); }
.ef-field label { display: block; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 5px; }
.ef-field .input-group-text { background: #f1f5f9; border: 1.5px solid #e2e8f0; border-right: none; border-radius: 8px 0 0 8px; color: #64748b; }
.ef-field .form-control { border: 1.5px solid #e2e8f0; border-radius: 0 8px 8px 0; font-size: 14px; height: 42px; background: #f8fafc; }
.ef-field .form-control:focus { border-color: var(--navy); box-shadow: 0 0 0 3px rgba(30,71,112,.1); outline: none; }
.ef-field select.form-control { border-radius: 8px; }
.ef-field input[type="date"].form-control { border-radius: 0 8px 8px 0; }

.btn-ef-save  { background: var(--navy); color: #fff; border: none; border-radius: 8px; padding: 10px 28px; font-size: 14px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 7px; transition: background .2s; }
.btn-ef-save:hover  { background: var(--navy-lt); }
.btn-ef-back  { display: inline-flex; align-items: center; gap: 7px; border: 1.5px solid var(--navy); color: var(--navy); background: none; border-radius: 8px; padding: 9px 22px; font-size: 14px; font-weight: 600; text-decoration: none; transition: background .2s, color .2s; }
.btn-ef-back:hover  { background: var(--navy); color: #fff; text-decoration: none; }
.btn-ef-print { display: inline-flex; align-items: center; gap: 7px; background: #0ea5e9; color: #fff; border: none; border-radius: 8px; padding: 10px 22px; font-size: 14px; font-weight: 700; cursor: pointer; transition: background .2s; }
.btn-ef-print:hover { background: #0284c7; }
.btn-ef-add   { background: #10b981; color: #fff; border: none; border-radius: 8px; padding: 10px 22px; font-size: 14px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 7px; transition: background .2s; }
.btn-ef-add:hover   { background: #059669; }
.btn-ef-del   { background: #ef4444; color: #fff; border: none; border-radius: 8px; padding: 10px 22px; font-size: 14px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 7px; transition: background .2s; }
.btn-ef-del:hover   { background: #dc2626; }
.btn-ef-upd   { background: #f59e0b; color: #fff; border: none; border-radius: 8px; padding: 10px 22px; font-size: 14px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 7px; transition: background .2s; }
.btn-ef-upd:hover   { background: #d97706; }

.ef-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 8px; }

.ef-credit-item { border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 18px 20px; margin-bottom: 14px; background: #f8fafc; }
.ef-credit-total { font-weight: 700; font-size: 15px; color: var(--navy); text-align: right; margin-top: 8px; }
</style>

<div class="map-wrapper">

    <div class="bc-bar">
        <a href="./index">Home</a>
        <span class="sep">/</span>
        <a href="./inicio-da-fatura">Faturas</a>
        <span class="sep">/</span>
        <span>Editar Fatura #<?= $idFatura ?></span>
    </div>

    <?php if ($flash):
        $flashClass = match($flash['type']) { 'success' => 'success', 'warning' => 'warning', default => 'danger' };
    ?>
        <div class="alert alert-<?= $flashClass ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($flash['msg']) ?>
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    <?php endif ?>

    <!-- ── Dados da Fatura ──────────────────────────────────────────────── -->
    <div class="ef-card">
        <div class="ef-card-hd">
            <i class="fas fa-file-invoice"></i> Dados da Fatura
        </div>
        <div class="ef-body">
            <form action="" method="post">
                <div class="ef-grid ef-grid-wide">
                    <div class="ef-field">
                        <label for="total">Valor Total</label>
                        <div class="input-group flex-nowrap">
                            <div class="input-group-prepend">
                                <span class="input-group-text">R$</span>
                            </div>
                            <input type="text" name="total" id="total" class="form-control"
                                   value="<?= number_format((float)$dadosFatura['tarifa'], 2, ',', '.') ?>">
                        </div>
                    </div>
                    <div class="ef-field">
                        <label for="credito">Crédito</label>
                        <div class="input-group flex-nowrap">
                            <div class="input-group-prepend">
                                <span class="input-group-text">R$</span>
                            </div>
                            <input type="text" name="credito" id="credito" class="form-control"
                                   value="<?= number_format((float)$dadosFatura['credito'], 2, ',', '.') ?>">
                        </div>
                    </div>
                    <div class="ef-field">
                        <label for="periodoinicial">Período Inicial</label>
                        <div class="input-group flex-nowrap">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                            </div>
                            <input type="date" name="periodoinicial" id="periodoinicial" class="form-control"
                                   value="<?= htmlspecialchars($dadosFatura['dateinput']) ?>">
                        </div>
                    </div>
                    <div class="ef-field">
                        <label for="situacao">Situação</label>
                        <select name="situacao" id="situacao" class="form-control">
                            <option value="0" <?= $dadosFatura['situacao'] == 0 ? 'selected' : '' ?>>Inativo</option>
                            <option value="1" <?= $dadosFatura['situacao'] == 1 ? 'selected' : '' ?>>Ativo</option>
                        </select>
                    </div>
                    <div class="ef-field">
                        <label for="periodofinal">Período Final</label>
                        <div class="input-group flex-nowrap">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                            </div>
                            <input type="date" name="periodofinal" id="periodofinal" class="form-control"
                                   value="<?= htmlspecialchars($dadosFatura['dateoutput']) ?>">
                        </div>
                    </div>
                </div>
                <input type="hidden" name="idfatura"  value="<?= $idFatura ?>">
                <input type="hidden" name="idcliente" value="<?= (int)$dadosFatura['idcliente'] ?>">
                <input type="hidden" name="status"    value="<?= (int)$dadosFatura['status'] ?>">
                <div class="ef-actions">
                    <button type="submit" name="updatefatura" class="btn-ef-save">
                        <i class="fas fa-save"></i> Atualizar Fatura
                    </button>
                    <a href="./inicio-da-fatura" class="btn-ef-back">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>
            </form>

            <div style="margin-top:16px">
                <form action="./relatorio/pdf-relatorio-cliente-reserva.php" method="post" target="_blank" style="display:inline">
                    <input type="hidden" name="idfatura"      value="<?= $idFatura ?>">
                    <input type="hidden" name="cliente"       value="<?= (int)$dadosFatura['idcliente'] ?>">
                    <input type="hidden" name="periodoinicial" value="<?= htmlspecialchars($dadosFatura['dateinput']) ?>">
                    <input type="hidden" name="periodofinal"   value="<?= htmlspecialchars($dadosFatura['dateoutput']) ?>">
                    <input type="hidden" name="status"         value="<?= (int)$dadosFatura['status'] ?>">
                    <button type="submit" name="gerar" class="btn-ef-print">
                        <i class="fas fa-print"></i> Imprimir Fatura
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- ── Adicionar Crédito ────────────────────────────────────────────── -->
    <div class="ef-card">
        <div class="ef-card-hd">
            <i class="fas fa-plus-circle"></i> Adicionar Crédito
        </div>
        <div class="ef-body">
            <form action="" method="post">
                <div class="ef-grid">
                    <div class="ef-field">
                        <label for="valorecebido">Valor Recebido</label>
                        <div class="input-group flex-nowrap">
                            <div class="input-group-prepend">
                                <span class="input-group-text">R$</span>
                            </div>
                            <input type="text" name="valorecebido" id="valorecebido" class="form-control"
                                   onKeyPress="return moeda(this,'.',',',event)">
                        </div>
                    </div>
                    <div class="ef-field">
                        <label for="datapagamento">Data do Pagamento</label>
                        <div class="input-group flex-nowrap">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                            </div>
                            <input type="date" name="datapagamento" id="datapagamento" class="form-control">
                        </div>
                    </div>
                    <div class="ef-field">
                        <label for="descricaopagamento">Descrição</label>
                        <input type="text" name="descricaopagamento" id="descricaopagamento" class="form-control" style="border-radius:8px;height:42px;border:1.5px solid #e2e8f0;font-size:14px;padding:0 12px">
                    </div>
                </div>
                <input type="hidden" name="idfatura" value="<?= $idFatura ?>">
                <button type="submit" name="inserirpagamento" class="btn-ef-add">
                    <i class="fas fa-plus"></i> Inserir Crédito
                </button>
            </form>
        </div>
    </div>

    <!-- ── Créditos Adicionados ─────────────────────────────────────────── -->
    <div class="ef-card">
        <div class="ef-card-hd">
            <i class="fas fa-money-bill-wave"></i> Créditos Adicionados
        </div>
        <div class="ef-body">
            <?php if (empty($creditos)): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Nenhum crédito registrado para esta fatura.
                </div>
            <?php else: ?>
                <?php foreach ($creditos as $item): ?>
                    <div class="ef-credit-item">
                        <form action="" method="post">
                            <div class="ef-grid">
                                <div class="ef-field">
                                    <label for="ve-<?= $item['id'] ?>">Valor Recebido</label>
                                    <div class="input-group flex-nowrap">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">R$</span>
                                        </div>
                                        <input type="text" name="valorecebido" id="ve-<?= $item['id'] ?>" class="form-control"
                                               value="<?= number_format($item['valor'], 2, ',', '.') ?>"
                                               onKeyPress="return moeda(this,'.',',',event)">
                                    </div>
                                </div>
                                <div class="ef-field">
                                    <label for="dp-<?= $item['id'] ?>">Data do Pagamento</label>
                                    <div class="input-group flex-nowrap">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                        </div>
                                        <input type="date" name="datapagamento" id="dp-<?= $item['id'] ?>" class="form-control"
                                               value="<?= htmlspecialchars($item['datapagamento']) ?>">
                                    </div>
                                </div>
                                <div class="ef-field">
                                    <label for="desc-<?= $item['id'] ?>">Descrição</label>
                                    <input type="text" name="descricaopagamento" id="desc-<?= $item['id'] ?>" class="form-control"
                                           style="border-radius:8px;height:42px;border:1.5px solid #e2e8f0;font-size:14px;padding:0 12px"
                                           value="<?= htmlspecialchars($item['descricao']) ?>">
                                </div>
                            </div>
                            <input type="hidden" name="idcredito" value="<?= $item['id'] ?>">
                            <input type="hidden" name="idfatura"  value="<?= $idFatura ?>">
                            <div class="ef-actions">
                                <button type="submit" name="atualizarpagamento" class="btn-ef-upd">
                                    <i class="fas fa-sync-alt"></i> Atualizar
                                </button>
                                <button type="submit" name="excluirpagamento" class="btn-ef-del"
                                        onclick="return confirm('Confirma a exclusão deste crédito?')">
                                    <i class="fas fa-trash"></i> Excluir
                                </button>
                            </div>
                        </form>
                    </div>
                <?php endforeach ?>
                <div class="ef-credit-total">
                    Total de Créditos: R$ <?= number_format($totalCreditos, 2, ',', '.') ?>
                </div>
            <?php endif ?>
        </div>
    </div>

</div>

<script>
function moeda(a, e, r, t) {
    var n = '', h = 0, j = 0, u = 0, tamanho2 = 0, l = '', ajd2 = '';
    var o = window.Event ? t.which : t.keyCode;
    if (o === 13 || o === 8) return true;
    n = String.fromCharCode(o);
    if ('0123456789'.indexOf(n) === -1) return false;
    for (u = a.value.length, h = 0; h < u && (a.value.charAt(h) === '0' || a.value.charAt(h) === r); h++) {}
    for (l = ''; h < u; h++) {
        if ('0123456789'.indexOf(a.value.charAt(h)) !== -1) l += a.value.charAt(h);
    }
    l += n;
    u = l.length;
    if (u === 0) { a.value = ''; }
    else if (u === 1) { a.value = '0' + r + '0' + l; }
    else if (u === 2) { a.value = '0' + r + l; }
    else {
        for (ajd2 = '', j = 0, h = u - 3; h >= 0; h--) {
            if (j === 3) { ajd2 += e; j = 0; }
            ajd2 += l.charAt(h); j++;
        }
        for (a.value = '', tamanho2 = ajd2.length, h = tamanho2 - 1; h >= 0; h--) a.value += ajd2.charAt(h);
        a.value += r + l.substr(u - 2, u);
    }
    return false;
}
</script>
<?php require_once 'footer.php'; ?>
