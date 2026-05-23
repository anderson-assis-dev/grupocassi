<?php
require_once 'header.php';
require_once __DIR__ . '/includes/ref_cache.php';
require_once __DIR__ . '/includes/flash.php';
$pdo->exec("set names utf8");
function servEsc($valor): string
{
    return htmlentities((string)$valor, ENT_QUOTES, 'UTF-8');
}
function servParseValor($valor): float
{
    $valor = trim((string)$valor);
    if ($valor === '') {
        return 0.0;
    }
    $valor = str_replace('.', '', $valor);
    return (float)str_replace(',', '.', $valor);
}
function servFormatValor($valor): string
{
    return number_format((float)$valor, 2, ',', '.');
}
function servListar(PDO $pdo): array
{
    $st = $pdo->prepare('SELECT * FROM ct_servico ORDER BY ordem, fullname');
    $st->execute();
    return $st->fetchAll(PDO::FETCH_ASSOC);
}
function servFlushCache(): void
{
    refCacheFlush('servicos');
    refCacheFlush('servicos_ordem');
}
function servEmUso(PDO $pdo, int $id): bool
{
    $st = $pdo->prepare('SELECT COUNT(*) FROM ct_reserva WHERE idservico = :id');
    $st->execute([':id' => $id]);
    if ((int)$st->fetchColumn() > 0) {
        return true;
    }
    $st = $pdo->prepare('SELECT COUNT(*) FROM ct_recentlyadd WHERE idservice = :id');
    $st->execute([':id' => $id]);
    return (int)$st->fetchColumn() > 0;
}
function servDadosForm(array $post): array
{
    return [
        'fullname' => trim($post['fullname'] ?? $post['nomeservico'] ?? ''),
        'screenplay' => trim($post['screenplay'] ?? $post['roteiro'] ?? ''),
        'priceadult' => servParseValor($post['priceadult'] ?? $post['precoadulto'] ?? 0),
        'pricechild' => servParseValor($post['pricechild'] ?? $post['precocrianca'] ?? 0),
        'tarifaone' => servParseValor($post['tarifaone'] ?? $post['tarifa'] ?? 0),
        'ordem' => (int)($post['ordem'] ?? 0)
    ];
}
$abaAtiva = 'listagem';
if (isset($_GET['aba']) && $_GET['aba'] === 'nova') {
    $abaAtiva = 'nova';
}
if (isset($_POST['cadastrarsservico'])) {
    $dados = servDadosForm($_POST);
    if ($dados['fullname'] === '') {
        setFlash('danger', 'Informe o nome do serviço.');
        header('location: novo-servico?aba=nova');
        exit;
    }
    $st = $pdo->prepare(
        'INSERT INTO ct_servico (id,fullname,screenplay,priceadult,pricechild,tarifaone,ordem)
        VALUES (DEFAULT,:nome,:roteiro,:precoadulto,:precocrianca,:tarifa,:ordem)'
    );
    $st->execute([
        ':nome' => $dados['fullname'],
        ':roteiro' => $dados['screenplay'],
        ':precoadulto' => $dados['priceadult'],
        ':precocrianca' => $dados['pricechild'],
        ':tarifa' => $dados['tarifaone'],
        ':ordem' => $dados['ordem']
    ]);
    servFlushCache();
    setFlash('success', 'Serviço "' . $dados['fullname'] . '" cadastrado com sucesso.');
    header('location: novo-servico');
    exit;
}
if (isset($_POST['editar'])) {
    $id = (int)($_POST['idpasseio'] ?? 0);
    $dados = servDadosForm($_POST);
    if ($id <= 0 || $dados['fullname'] === '') {
        setFlash('danger', 'Dados inválidos para atualização.');
        header('location: novo-servico');
        exit;
    }
    $st = $pdo->prepare(
        'UPDATE ct_servico SET fullname=:nome,screenplay=:roteiro,priceadult=:precoadulto,
        pricechild=:precocrianca,tarifaone=:tarifa,ordem=:ordem WHERE id=:id'
    );
    $st->execute([
        ':nome' => $dados['fullname'],
        ':roteiro' => $dados['screenplay'],
        ':precoadulto' => $dados['priceadult'],
        ':precocrianca' => $dados['pricechild'],
        ':tarifa' => $dados['tarifaone'],
        ':ordem' => $dados['ordem'],
        ':id' => $id
    ]);
    servFlushCache();
    setFlash('success', 'Serviço "' . $dados['fullname'] . '" atualizado com sucesso.');
    header('location: novo-servico');
    exit;
}
if (isset($_POST['excluir'])) {
    $id = (int)($_POST['idpasseio'] ?? 0);
    if ($id <= 0) {
        setFlash('danger', 'Serviço inválido.');
        header('location: novo-servico');
        exit;
    }
    if (servEmUso($pdo, $id)) {
        setFlash('danger', 'Não é possível excluir: serviço vinculado a reservas.');
        header('location: novo-servico');
        exit;
    }
    $st = $pdo->prepare('DELETE FROM ct_servico WHERE id = :id');
    $st->execute([':id' => $id]);
    servFlushCache();
    setFlash('success', 'Serviço excluído com sucesso.');
    header('location: novo-servico');
    exit;
}
$listaServicos = servListar($pdo);
$totalServicos = count($listaServicos);
?>
<style>
:root {
    --navy:    #1e4770;
    --navy-lt: #2a5f96;
}
.map-wrapper { padding: 20px 20px 80px; }
.bc-bar { padding: 0 0 16px; font-size: 13px; color: #6c757d; }
.bc-bar a { color: var(--navy); font-weight: 600; text-decoration: none; }
.bc-bar a:hover { text-decoration: underline; }
.bc-bar .sep { margin: 0 6px; color: #ccc; }
.filter-card { background: #fff; border-radius: 12px; box-shadow: 0 2px 14px rgba(0,0,0,.07); padding: 22px 26px 18px; margin-bottom: 20px; }
.fc-title { font-size: 12px; font-weight: 700; color: var(--navy); text-transform: uppercase; letter-spacing: .06em; margin-bottom: 0; display: flex; align-items: center; gap: 7px; }
.fc-sub { font-size: 13px; color: #6c757d; margin-top: 3px; }
.serv-kpi { background: #f0f6ff; border-radius: 10px; padding: 12px 20px; text-align: center; }
.serv-kpi-label { font-size: 11px; font-weight: 700; color: #6c757d; text-transform: uppercase; letter-spacing: .04em; }
.serv-kpi-value { font-size: 26px; font-weight: 800; color: var(--navy); line-height: 1.1; }
.btn-novo { background: var(--navy); color: #fff; border: none; border-radius: 8px; padding: 10px 22px; font-size: 13px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 7px; transition: background .2s; white-space: nowrap; }
.btn-novo:hover { background: var(--navy-lt); color: #fff; text-decoration: none; }
.results-card { background: #fff; border-radius: 12px; box-shadow: 0 2px 14px rgba(0,0,0,.07); overflow: hidden; }
.results-header { padding: 16px 22px 12px; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px; }
.results-title { font-size: 14px; font-weight: 700; color: var(--navy); display: flex; align-items: center; gap: 8px; }
.results-count { font-size: 11px; background: var(--navy); color: #fff; padding: 2px 9px; border-radius: 20px; font-weight: 600; }
#serv-table { font-size: 12.5px; }
#serv-table thead th { background: var(--navy); color: #fff; font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: .04em; border: none; padding: 9px 8px; white-space: nowrap; }
#serv-table tbody tr { transition: background .12s; }
#serv-table tbody tr:hover td { background: #f0f6ff !important; }
#serv-table tbody td { padding: 7px 8px; vertical-align: middle; border-color: #f0f0f0; }
.serv-nome-cell { font-weight: 600; }
.roteiro-cell { max-width: 220px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 11px; color: #777; }
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
        <span>Operacional: Serviços</span>
    </div>

    <div class="filter-card">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;">
            <div>
                <div class="fc-title"><i class="fas fa-concierge-bell"></i> Serviços</div>
                <div class="fc-sub">Cadastro de passeios, preços e roteiros</div>
            </div>
            <div style="display:flex;align-items:center;gap:16px;">
                <div class="serv-kpi">
                    <div class="serv-kpi-label">Total</div>
                    <div class="serv-kpi-value"><?= $totalServicos ?></div>
                </div>
                <button type="button" class="btn-novo" onclick="abrirModalNovo()">
                    <i class="fas fa-plus"></i> Novo Serviço
                </button>
            </div>
        </div>
    </div>

    <?php $flash = getFlash(); if ($flash): ?>
    <div class="alert alert-<?= servEsc($flash['type']) ?> alert-dismissible fade show" role="alert">
        <?= servEsc($flash['msg']) ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    </div>
    <?php endif; ?>

    <div class="results-card">
        <div class="results-header">
            <div class="results-title">
                <i class="fas fa-list"></i> Serviços Cadastrados
                <span class="results-count"><?= $totalServicos ?></span>
            </div>
        </div>
        <div class="table-responsive">
            <table id="serv-table" class="table table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>Nº</th>
                        <th>Nome</th>
                        <th>Adulto</th>
                        <th>Criança</th>
                        <th>Tarifa</th>
                        <th>Ordem</th>
                        <th>Roteiro</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($listaServicos as $item): ?>
                <tr>
                    <td><?= (int)$item['id'] ?></td>
                    <td class="serv-nome-cell"><?= servEsc($item['fullname']) ?></td>
                    <td>R$ <?= servEsc(servFormatValor($item['priceadult'])) ?></td>
                    <td>R$ <?= servEsc(servFormatValor($item['pricechild'])) ?></td>
                    <td>R$ <?= servEsc(servFormatValor($item['tarifaone'])) ?></td>
                    <td><?= (int)$item['ordem'] ?></td>
                    <td class="roteiro-cell" title="<?= servEsc($item['screenplay']) ?>"><?= servEsc($item['screenplay']) ?></td>
                    <td style="white-space:nowrap;">
                        <button type="button" class="btn-tbl-edit btn-editar-serv"
                            data-id="<?= (int)$item['id'] ?>"
                            data-nome="<?= servEsc($item['fullname']) ?>"
                            data-adulto="<?= servEsc(servFormatValor($item['priceadult'])) ?>"
                            data-crianca="<?= servEsc(servFormatValor($item['pricechild'])) ?>"
                            data-tarifa="<?= servEsc(servFormatValor($item['tarifaone'])) ?>"
                            data-ordem="<?= (int)$item['ordem'] ?>"
                            data-roteiro="<?= servEsc($item['screenplay']) ?>">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                        <form action="" method="post" class="form-excluir-serv d-inline">
                            <input type="hidden" name="idpasseio" value="<?= (int)$item['id'] ?>">
                            <input type="hidden" name="nomeservico" value="<?= servEsc($item['fullname']) ?>">
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

<!-- Modal Novo / Editar Serviço -->
<div class="modal fade" id="modalServico" tabindex="-1" role="dialog" aria-labelledby="modalServicoTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalServicoTitle">
                    <i class="fas fa-concierge-bell"></i> <span id="modal-titulo">Novo Serviço</span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
            </div>
            <form action="" method="post" id="form-servico">
                <input type="hidden" name="idpasseio" id="modal-idpasseio" value="0">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label class="modal-label" for="modal-nome">Nome do Serviço</label>
                                <input type="text" name="fullname" id="modal-nome" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="modal-label" for="modal-ordem">Ordem</label>
                                <input type="number" name="ordem" id="modal-ordem" class="form-control" value="0" min="0">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="modal-label" for="modal-roteiro">Roteiro</label>
                        <textarea name="screenplay" id="modal-roteiro" class="form-control" rows="4" style="resize:vertical;"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="modal-label" for="modal-adulto">Preço Adulto</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">R$</span></div>
                                    <input type="text" name="priceadult" id="modal-adulto" class="form-control serv-moeda" value="0,00">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="modal-label" for="modal-crianca">Preço Criança</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">R$</span></div>
                                    <input type="text" name="pricechild" id="modal-crianca" class="form-control serv-moeda" value="0,00">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="modal-label" for="modal-tarifa">Tarifa</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">R$</span></div>
                                    <input type="text" name="tarifaone" id="modal-tarifa" class="form-control serv-moeda" value="0,00">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" name="cadastrarsservico" id="btn-submit-novo" class="btn btn-success">
                        <i class="fas fa-save"></i> Cadastrar Serviço
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
function moeda(a,e,r,t){
    var n="",h=0,j=0,u=0,tamanho2=0,l="",ajd2="",o=window.Event?t.which:t.keyCode;
    if(13==o||8==o)return true;
    if(n=String.fromCharCode(o),-1=="0123456789".indexOf(n))return false;
    for(u=a.value.length,h=0;h<u&&("0"==a.value.charAt(h)||a.value.charAt(h)==r);h++);
    for(l="";h<u;h++)-1!="0123456789".indexOf(a.value.charAt(h))&&(l+=a.value.charAt(h));
    if(l+=n,0==(u=l.length)&&(a.value=""),1==u&&(a.value="0"+r+"0"+l),2==u&&(a.value="0"+r+l),u>2){
        for(ajd2="",j=0,h=u-3;h>=0;h--)3==j&&(ajd2+=e,j=0),ajd2+=l.charAt(h),j++;
        for(a.value="",tamanho2=ajd2.length,h=tamanho2-1;h>=0;h--)a.value+=ajd2.charAt(h);
        a.value+=r+l.substr(u-2,u);
    }
    return false;
}

function abrirModalNovo() {
    document.getElementById('modal-titulo').textContent = 'Novo Serviço';
    document.getElementById('modal-idpasseio').value = '0';
    document.getElementById('modal-nome').value = '';
    document.getElementById('modal-adulto').value = '0,00';
    document.getElementById('modal-crianca').value = '0,00';
    document.getElementById('modal-tarifa').value = '0,00';
    document.getElementById('modal-ordem').value = '0';
    document.getElementById('modal-roteiro').value = '';
    document.getElementById('btn-submit-novo').style.display = '';
    document.getElementById('btn-submit-editar').style.display = 'none';
    $('#modalServico').modal('show');
}

function abrirModalEditar(btn) {
    var d = btn.dataset;
    document.getElementById('modal-titulo').textContent = 'Editar Serviço';
    document.getElementById('modal-idpasseio').value = d.id;
    document.getElementById('modal-nome').value = d.nome;
    document.getElementById('modal-adulto').value = d.adulto;
    document.getElementById('modal-crianca').value = d.crianca;
    document.getElementById('modal-tarifa').value = d.tarifa;
    document.getElementById('modal-ordem').value = d.ordem;
    document.getElementById('modal-roteiro').value = d.roteiro;
    document.getElementById('btn-submit-novo').style.display = 'none';
    document.getElementById('btn-submit-editar').style.display = '';
    $('#modalServico').modal('show');
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.btn-editar-serv').forEach(function (btn) {
        btn.addEventListener('click', function () { abrirModalEditar(this); });
    });
    document.querySelectorAll('.serv-moeda').forEach(function (input) {
        input.addEventListener('keypress', function (e) { return moeda(this, '.', ',', e); });
    });
    document.querySelectorAll('.form-excluir-serv').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            var nome = form.querySelector('[name="nomeservico"]').value;
            if (!confirm('Deseja excluir o serviço "' + nome + '"?')) {
                e.preventDefault();
            }
        });
    });
    if (window.jQuery && jQuery.fn.DataTable && document.getElementById('serv-table')) {
        jQuery('#serv-table').DataTable({
            dom: '<"d-flex justify-content-between align-items-center mb-2"Bf>rtip',
            buttons: [
                { extend: 'excelHtml5', text: '<i class="fas fa-file-excel"></i> Excel',   className: 'btn btn-sm btn-success mr-1' },
                { extend: 'csvHtml5',   text: '<i class="fas fa-file-csv"></i> CSV',       className: 'btn btn-sm btn-secondary mr-1' },
                { extend: 'copyHtml5',  text: '<i class="fas fa-copy"></i> Copiar',        className: 'btn btn-sm btn-dark mr-1' },
            ],
            pageLength: 50,
            order: [[5, 'asc'], [1, 'asc']],
            language: {
                search:      'Buscar:',
                lengthMenu:  'Exibir _MENU_ por página',
                info:        '_START_–_END_ de _TOTAL_',
                paginate:    { first: '«', last: '»', next: '›', previous: '‹' },
                zeroRecords: 'Nenhum serviço encontrado',
                infoEmpty:   'Sem registros',
            },
            columnDefs: [{ orderable: false, targets: [6, 7] }],
        });
    }
});
</script>
<?php require_once 'footer.php'; ?>
