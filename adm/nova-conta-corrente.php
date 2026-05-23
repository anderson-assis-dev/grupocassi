<?php
require_once 'header.php';
require_once __DIR__ . '/includes/flash.php';
$pdo->exec("set names utf8");

function ccEsc($valor): string
{
    return htmlentities((string)$valor, ENT_QUOTES, 'UTF-8');
}
function ccListar(PDO $pdo): array
{
    $st = $pdo->prepare('SELECT * FROM ct_currentaccount ORDER BY name');
    $st->execute();
    return $st->fetchAll(PDO::FETCH_ASSOC);
}
function ccEmUso(PDO $pdo, int $id): bool
{
    $st = $pdo->prepare('SELECT COUNT(*) FROM ct_caixa WHERE idconta = :id');
    $st->execute([':id' => $id]);
    return (int)$st->fetchColumn() > 0;
}

if (isset($_POST['conta'])) {
    $nome = trim($_POST['fullname'] ?? $_POST['contacorrente'] ?? '');
    if ($nome === '') {
        setFlash('danger', 'Informe o nome da conta corrente.');
        header('location: nova-conta-corrente');
        exit;
    }
    $st = $pdo->prepare('INSERT INTO ct_currentaccount (id, name) VALUES (DEFAULT, :nome)');
    $st->execute([':nome' => $nome]);
    setFlash('success', 'Conta "' . $nome . '" cadastrada com sucesso.');
    header('location: nova-conta-corrente');
    exit;
}

if (isset($_POST['editar'])) {
    $id   = (int)($_POST['idconta'] ?? 0);
    $nome = trim($_POST['fullname'] ?? '');
    if ($id <= 0 || $nome === '') {
        setFlash('danger', 'Dados inválidos para atualização.');
        header('location: nova-conta-corrente');
        exit;
    }
    $st = $pdo->prepare('UPDATE ct_currentaccount SET name = :nome WHERE id = :id');
    $st->execute([':nome' => $nome, ':id' => $id]);
    setFlash('success', 'Conta "' . $nome . '" atualizada com sucesso.');
    header('location: nova-conta-corrente');
    exit;
}

if (isset($_POST['excluir'])) {
    $id = (int)($_POST['idconta'] ?? 0);
    if ($id <= 0) {
        setFlash('danger', 'Conta inválida.');
        header('location: nova-conta-corrente');
        exit;
    }
    if (ccEmUso($pdo, $id)) {
        setFlash('danger', 'Não é possível excluir: conta vinculada a transações do caixa.');
        header('location: nova-conta-corrente');
        exit;
    }
    $st = $pdo->prepare('DELETE FROM ct_currentaccount WHERE id = :id');
    $st->execute([':id' => $id]);
    setFlash('success', 'Conta excluída com sucesso.');
    header('location: nova-conta-corrente');
    exit;
}

$listaContas   = ccListar($pdo);
$totalContas   = count($listaContas);
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
.cc-kpi { background: #f0f6ff; border-radius: 10px; padding: 12px 20px; text-align: center; }
.cc-kpi-label { font-size: 11px; font-weight: 700; color: #6c757d; text-transform: uppercase; letter-spacing: .04em; }
.cc-kpi-value { font-size: 26px; font-weight: 800; color: var(--navy); line-height: 1.1; }
.btn-novo { background: var(--navy); color: #fff; border: none; border-radius: 8px; padding: 10px 22px; font-size: 13px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 7px; transition: background .2s; white-space: nowrap; }
.btn-novo:hover { background: var(--navy-lt); color: #fff; text-decoration: none; }
.results-card { background: #fff; border-radius: 12px; box-shadow: 0 2px 14px rgba(0,0,0,.07); overflow: hidden; }
.results-header { padding: 16px 22px 12px; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px; }
.results-title { font-size: 14px; font-weight: 700; color: var(--navy); display: flex; align-items: center; gap: 8px; }
.results-count { font-size: 11px; background: var(--navy); color: #fff; padding: 2px 9px; border-radius: 20px; font-weight: 600; }
#cc-table { font-size: 12.5px; }
#cc-table thead th { background: var(--navy); color: #fff; font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: .04em; border: none; padding: 9px 8px; white-space: nowrap; }
#cc-table tbody tr { transition: background .12s; }
#cc-table tbody tr:hover td { background: #f0f6ff !important; }
#cc-table tbody td { padding: 7px 8px; vertical-align: middle; border-color: #f0f0f0; }
.cc-nome-cell { font-weight: 600; }
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
        <span>Financeiro: Contas Correntes</span>
    </div>

    <div class="filter-card">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;">
            <div>
                <div class="fc-title"><i class="fas fa-university"></i> Contas Correntes</div>
                <div class="fc-sub">Cadastro e gestão de contas bancárias e caixas</div>
            </div>
            <div style="display:flex;align-items:center;gap:16px;">
                <div class="cc-kpi">
                    <div class="cc-kpi-label">Total</div>
                    <div class="cc-kpi-value"><?= $totalContas ?></div>
                </div>
                <button type="button" class="btn-novo" onclick="abrirModalNovo()">
                    <i class="fas fa-plus"></i> Nova Conta
                </button>
            </div>
        </div>
    </div>

    <?php $flash = getFlash(); if ($flash): ?>
    <div class="alert alert-<?= ccEsc($flash['type']) ?> alert-dismissible fade show" role="alert">
        <?= ccEsc($flash['msg']) ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    </div>
    <?php endif; ?>

    <div class="results-card">
        <div class="results-header">
            <div class="results-title">
                <i class="fas fa-list"></i> Contas Cadastradas
                <span class="results-count"><?= $totalContas ?></span>
            </div>
        </div>
        <div class="table-responsive">
            <table id="cc-table" class="table table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>Nº</th>
                        <th>Nome da Conta</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($listaContas as $item): ?>
                <tr>
                    <td><?= (int)$item['id'] ?></td>
                    <td class="cc-nome-cell"><?= ccEsc($item['name']) ?></td>
                    <td style="white-space:nowrap;">
                        <button type="button" class="btn-tbl-edit btn-editar-cc"
                            data-id="<?= (int)$item['id'] ?>"
                            data-nome="<?= ccEsc($item['name']) ?>">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                        <form action="" method="post" class="form-excluir-cc d-inline">
                            <input type="hidden" name="idconta" value="<?= (int)$item['id'] ?>">
                            <input type="hidden" name="nomeconta" value="<?= ccEsc($item['name']) ?>">
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

<!-- Modal Nova / Editar Conta Corrente -->
<div class="modal fade" id="modalConta" tabindex="-1" role="dialog" aria-labelledby="modalContaTitle" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalContaTitle">
                    <i class="fas fa-university"></i> <span id="modal-titulo">Nova Conta</span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
            </div>
            <form action="" method="post" id="form-conta">
                <input type="hidden" name="idconta" id="modal-idconta" value="0">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="modal-label" for="modal-nome">Nome da Conta</label>
                        <input type="text" name="fullname" id="modal-nome" class="form-control" required placeholder="Ex: CAIXA GERAL, BANCO BRADESCO">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" name="conta" id="btn-submit-novo" class="btn btn-success">
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
    document.getElementById('modal-titulo').textContent = 'Nova Conta';
    document.getElementById('modal-idconta').value = '0';
    document.getElementById('modal-nome').value = '';
    document.getElementById('btn-submit-novo').style.display = '';
    document.getElementById('btn-submit-editar').style.display = 'none';
    $('#modalConta').modal('show');
}

function abrirModalEditar(btn) {
    var d = btn.dataset;
    document.getElementById('modal-titulo').textContent = 'Editar Conta';
    document.getElementById('modal-idconta').value = d.id;
    document.getElementById('modal-nome').value = d.nome;
    document.getElementById('btn-submit-novo').style.display = 'none';
    document.getElementById('btn-submit-editar').style.display = '';
    $('#modalConta').modal('show');
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.btn-editar-cc').forEach(function (btn) {
        btn.addEventListener('click', function () { abrirModalEditar(this); });
    });
    document.querySelectorAll('.form-excluir-cc').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            var nome = form.querySelector('[name="nomeconta"]').value;
            if (!confirm('Deseja excluir a conta "' + nome + '"?')) {
                e.preventDefault();
            }
        });
    });
    if (window.jQuery && jQuery.fn.DataTable && document.getElementById('cc-table')) {
        jQuery('#cc-table').DataTable({
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
                zeroRecords: 'Nenhuma conta encontrada',
                infoEmpty:   'Sem registros',
            },
            columnDefs: [{ orderable: false, targets: [2] }],
        });
    }
});
</script>
<?php require_once 'footer.php'; ?>
