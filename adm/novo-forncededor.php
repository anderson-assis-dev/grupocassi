<?php
require_once 'header.php';
require_once __DIR__ . '/includes/ref_cache.php';
require_once __DIR__ . '/includes/flash.php';
$pdo->exec("set names utf8");
function fornEsc($valor): string
{
    return htmlentities((string)$valor, ENT_QUOTES, 'UTF-8');
}
function fornListar(PDO $pdo): array
{
    $st = $pdo->prepare('SELECT * FROM ct_fornecedor ORDER BY fullname');
    $st->execute();
    return $st->fetchAll(PDO::FETCH_ASSOC);
}
function fornEmUso(PDO $pdo, int $id): bool
{
    $st = $pdo->prepare('SELECT COUNT(*) FROM ct_caixa WHERE idcliente = :id');
    $st->execute([':id' => $id]);
    return (int)$st->fetchColumn() > 0;
}
$abaAtiva = 'listagem';
if (isset($_GET['aba']) && $_GET['aba'] === 'nova') {
    $abaAtiva = 'nova';
}
if (isset($_POST['cadastrarfornecedor'])) {
    $nome = trim($_POST['nomefornecedor'] ?? '');
    if ($nome === '') {
        setFlash('danger', 'Informe o nome do fornecedor.');
        header('location: novo-forncededor?aba=nova');
        exit;
    }
    $st = $pdo->prepare('INSERT INTO ct_fornecedor (id, fullname) VALUES (DEFAULT, :nome)');
    $st->execute([':nome' => $nome]);
    refCacheFlush('fornecedores');
    setFlash('success', 'Fornecedor "' . $nome . '" cadastrado com sucesso.');
    header('location: novo-forncededor');
    exit;
}
if (isset($_POST['editar'])) {
    $id = (int)($_POST['idfornecedor'] ?? 0);
    $nome = trim($_POST['fullname'] ?? '');
    if ($id <= 0 || $nome === '') {
        setFlash('danger', 'Dados inválidos para atualização.');
        header('location: novo-forncededor');
        exit;
    }
    $st = $pdo->prepare('UPDATE ct_fornecedor SET fullname = :nome WHERE id = :id');
    $st->execute([':nome' => $nome, ':id' => $id]);
    refCacheFlush('fornecedores');
    setFlash('success', 'Fornecedor "' . $nome . '" atualizado com sucesso.');
    header('location: novo-forncededor');
    exit;
}
if (isset($_POST['excluir'])) {
    $id = (int)($_POST['idfornecedor'] ?? 0);
    if ($id <= 0) {
        setFlash('danger', 'Fornecedor inválido.');
        header('location: novo-forncededor');
        exit;
    }
    if (fornEmUso($pdo, $id)) {
        setFlash('danger', 'Não é possível excluir: fornecedor vinculado a transações do caixa.');
        header('location: novo-forncededor');
        exit;
    }
    $st = $pdo->prepare('DELETE FROM ct_fornecedor WHERE id = :id');
    $st->execute([':id' => $id]);
    refCacheFlush('fornecedores');
    setFlash('success', 'Fornecedor excluído com sucesso.');
    header('location: novo-forncededor');
    exit;
}
$listaFornecedores = fornListar($pdo);
$totalFornecedores = count($listaFornecedores);
?>
<style>
:root { --navy: #1e4770; --navy-lt: #2a5f96; }
.map-wrapper { padding: 20px 20px 80px; }
.bc-bar { padding: 0 0 16px; font-size: 13px; color: #6c757d; }
.bc-bar a { color: var(--navy); font-weight: 600; text-decoration: none; }
.bc-bar a:hover { text-decoration: underline; }
.bc-bar .sep { margin: 0 6px; color: #ccc; }
.filter-card { background: #fff; border-radius: 12px; box-shadow: 0 2px 14px rgba(0,0,0,.07); padding: 22px 26px 18px; margin-bottom: 20px; }
.fc-title { font-size: 12px; font-weight: 700; color: var(--navy); text-transform: uppercase; letter-spacing: .06em; margin-bottom: 0; display: flex; align-items: center; gap: 7px; }
.fc-sub { font-size: 13px; color: #6c757d; margin-top: 3px; }
.forn-kpi { background: #f0f6ff; border-radius: 10px; padding: 12px 20px; text-align: center; }
.forn-kpi-label { font-size: 11px; font-weight: 700; color: #6c757d; text-transform: uppercase; letter-spacing: .04em; }
.forn-kpi-value { font-size: 26px; font-weight: 800; color: var(--navy); line-height: 1.1; }
.btn-novo { background: var(--navy); color: #fff; border: none; border-radius: 8px; padding: 10px 22px; font-size: 13px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 7px; transition: background .2s; white-space: nowrap; }
.btn-novo:hover { background: var(--navy-lt); color: #fff; text-decoration: none; }
.results-card { background: #fff; border-radius: 12px; box-shadow: 0 2px 14px rgba(0,0,0,.07); overflow: hidden; }
.results-header { padding: 16px 22px 12px; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px; }
.results-title { font-size: 14px; font-weight: 700; color: var(--navy); display: flex; align-items: center; gap: 8px; }
.results-count { font-size: 11px; background: var(--navy); color: #fff; padding: 2px 9px; border-radius: 20px; font-weight: 600; }
#forn-table { font-size: 12.5px; }
#forn-table thead th { background: var(--navy); color: #fff; font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: .04em; border: none; padding: 9px 8px; white-space: nowrap; }
#forn-table tbody tr { transition: background .12s; }
#forn-table tbody tr:hover td { background: #f0f6ff !important; }
#forn-table tbody td { padding: 7px 8px; vertical-align: middle; border-color: #f0f0f0; }
.forn-nome-cell { font-weight: 600; }
.btn-tbl-edit { background: var(--navy); color: #fff; border: none; border-radius: 6px; padding: 4px 11px; font-size: 12px; font-weight: 600; cursor: pointer; transition: background .2s; }
.btn-tbl-edit:hover { background: var(--navy-lt); color: #fff; }
.btn-tbl-del { background: #dc3545; color: #fff; border: none; border-radius: 6px; padding: 4px 11px; font-size: 12px; font-weight: 600; cursor: pointer; transition: background .2s; margin-left: 4px; }
.btn-tbl-del:hover { background: #b91c1c; color: #fff; }
.modal-header { background: var(--navy); color: #fff; }
.modal-header .modal-title { color: #fff; font-size: 15px; font-weight: 700; }
.modal-header .close { color: #fff; opacity: .8; text-shadow: none; }
.modal-header .close:hover { opacity: 1; }
.modal-label { font-size: 11px; font-weight: 700; color: #6c757d; text-transform: uppercase; letter-spacing: .05em; display: block; margin-bottom: 5px; }
@media (max-width: 767px) { .map-wrapper { padding: 14px 12px 80px; } }
</style>

<div class="page-content--bgf7">
<div class="map-wrapper">

    <div class="bc-bar">
        <a href="index"><i class="fas fa-home"></i> Home</a>
        <span class="sep">/</span>
        <span>Financeiro: Fornecedores</span>
    </div>

    <div class="filter-card">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;">
            <div>
                <div class="fc-title"><i class="fas fa-handshake"></i> Fornecedores</div>
                <div class="fc-sub">Cadastro e gestão de favorecidos do caixa</div>
            </div>
            <div style="display:flex;align-items:center;gap:16px;">
                <div class="forn-kpi">
                    <div class="forn-kpi-label">Total</div>
                    <div class="forn-kpi-value"><?= $totalFornecedores ?></div>
                </div>
                <button type="button" class="btn-novo" onclick="abrirModalNovo()">
                    <i class="fas fa-plus"></i> Novo Fornecedor
                </button>
            </div>
        </div>
    </div>

    <?php $flash = getFlash(); if ($flash): ?>
    <div class="alert alert-<?= fornEsc($flash['type']) ?> alert-dismissible fade show" role="alert">
        <?= fornEsc($flash['msg']) ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    </div>
    <?php endif; ?>

    <div class="results-card">
        <div class="results-header">
            <div class="results-title">
                <i class="fas fa-list"></i> Fornecedores Cadastrados
                <span class="results-count"><?= $totalFornecedores ?></span>
            </div>
        </div>
        <div class="table-responsive">
            <table id="forn-table" class="table table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>Nº</th>
                        <th>Nome do Fornecedor</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($listaFornecedores as $item): ?>
                <tr>
                    <td><?= (int)$item['id'] ?></td>
                    <td class="forn-nome-cell"><?= fornEsc($item['fullname']) ?></td>
                    <td style="white-space:nowrap;">
                        <button type="button" class="btn-tbl-edit btn-editar-forn"
                            data-id="<?= (int)$item['id'] ?>"
                            data-nome="<?= fornEsc($item['fullname']) ?>">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                        <form action="" method="post" class="form-excluir-forn d-inline">
                            <input type="hidden" name="idfornecedor" value="<?= (int)$item['id'] ?>">
                            <input type="hidden" name="nomefornecedor" value="<?= fornEsc($item['fullname']) ?>">
                            <button type="submit" name="excluir" class="btn-tbl-del">
                                <i class="fas fa-trash"></i> Excluir
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
</div>

<!-- Modal Novo / Editar Fornecedor -->
<div class="modal fade" id="modalFornecedor" tabindex="-1" role="dialog" aria-labelledby="modalFornTitle" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalFornTitle">
                    <i class="fas fa-handshake"></i> <span id="modal-titulo">Novo Fornecedor</span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
            </div>
            <form action="" method="post" id="form-fornecedor">
                <input type="hidden" name="idfornecedor" id="modal-idforn" value="0">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="modal-label" for="modal-nome">Nome do Fornecedor</label>
                        <input type="text" name="fullname" id="modal-nome" class="form-control" required placeholder="Ex: POSTO DE GASOLINA">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" name="cadastrarfornecedor" id="btn-submit-novo" class="btn btn-success">
                        <i class="fas fa-save"></i> Cadastrar
                    </button>
                    <button type="submit" name="editar" id="btn-submit-editar" class="btn btn-primary" style="display:none">
                        <i class="fas fa-save"></i> Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function abrirModalNovo() {
    document.getElementById('modal-titulo').textContent = 'Novo Fornecedor';
    document.getElementById('modal-idforn').value = '0';
    document.getElementById('modal-nome').value = '';
    document.getElementById('btn-submit-novo').style.display = '';
    document.getElementById('btn-submit-editar').style.display = 'none';
    $('#modalFornecedor').modal('show');
}

function abrirModalEditar(btn) {
    var d = btn.dataset;
    document.getElementById('modal-titulo').textContent = 'Editar Fornecedor';
    document.getElementById('modal-idforn').value = d.id;
    document.getElementById('modal-nome').value = d.nome;
    document.getElementById('btn-submit-novo').style.display = 'none';
    document.getElementById('btn-submit-editar').style.display = '';
    $('#modalFornecedor').modal('show');
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.btn-editar-forn').forEach(function (btn) {
        btn.addEventListener('click', function () { abrirModalEditar(this); });
    });
    document.querySelectorAll('.form-excluir-forn').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            var nome = form.querySelector('[name="nomefornecedor"]').value;
            if (!confirm('Deseja excluir o fornecedor "' + nome + '"?')) {
                e.preventDefault();
            }
        });
    });
    if (window.jQuery && jQuery.fn.DataTable && document.getElementById('forn-table')) {
        jQuery('#forn-table').DataTable({
            dom: '<"d-flex justify-content-between align-items-center mb-2"Bf>rtip',
            buttons: [
                { extend: 'excelHtml5', text: '<i class="fas fa-file-excel"></i> Excel',  className: 'btn btn-sm btn-success mr-1' },
                { extend: 'csvHtml5',   text: '<i class="fas fa-file-csv"></i> CSV',      className: 'btn btn-sm btn-secondary mr-1' },
                { extend: 'copyHtml5',  text: '<i class="fas fa-copy"></i> Copiar',       className: 'btn btn-sm btn-dark mr-1' },
            ],
            pageLength: 50,
            order: [[1, 'asc']],
            language: {
                search:      'Buscar:',
                lengthMenu:  'Exibir _MENU_ por página',
                info:        '_START_–_END_ de _TOTAL_',
                paginate:    { first: '«', last: '»', next: '›', previous: '‹' },
                zeroRecords: 'Nenhum fornecedor encontrado',
                infoEmpty:   'Sem registros',
            },
            columnDefs: [{ orderable: false, targets: [2] }],
        });
    }
});
</script>
<?php require_once 'footer.php'; ?>
