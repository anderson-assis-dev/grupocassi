<?php
require_once 'header.php';
require_once __DIR__ . '/includes/flash.php';
require_once __DIR__ . '/includes/ref_cache.php';
$pdo->exec("set names utf8");

function cliEsc($v): string { return htmlentities((string)$v, ENT_QUOTES, 'UTF-8'); }

// ── Criação de novo cliente/fornecedor ────────────────────────────────────────
if (isset($_POST['novocliente'])) {
    $nome    = strtoupper(trim($_POST['nomefantazia'] ?? ''));
    $razao   = strtoupper(trim($_POST['razaosocial']  ?? ''));
    $cnpj    = trim($_POST['cnpj']    ?? '');
    $tipo    = $_POST['tipo']         ?? 'Pessoa Fisica';
    $celular = trim($_POST['celular'] ?? '');
    $email   = trim($_POST['email']   ?? '');
    $endereco = strtoupper(trim($_POST['endereco'] ?? ''));
    $cep      = trim($_POST['cep']    ?? '');
    $datanf   = $_POST['datanf']      ?? date('Y-m-d');

    if ($nome === '') {
        setFlash('danger', 'Informe o nome do cliente/fornecedor.');
        header('location: pesquisa-cliente-fornecedor');
        exit;
    }

    $defEstado = (int)($pdo->query("SELECT id FROM ct_estado WHERE name = 'Bahia'   LIMIT 1")->fetchColumn() ?: 0);
    $defCidade = (int)($pdo->query("SELECT id FROM ct_cidade WHERE name = 'Salvador' LIMIT 1")->fetchColumn() ?: 0);
    $defPais   = (int)($pdo->query("SELECT id FROM ct_pais   WHERE name LIKE 'Brasil%' LIMIT 1")->fetchColumn() ?: 1);

    $st = $pdo->prepare(
        "INSERT INTO ct_cliente
            (id, fullname, cnpj, namefantazia, corporatename, type, address, datefundation,
             idcountry, idstate, idcity, cep, tel01, tel02, phone, email,
             municipalregistration, stateenrollment, register, observacao,
             periodoinicial, periodofinal, limite)
         VALUES
            (DEFAULT, :fullname, :cnpj, :fantazia, :razao, :tipo, :ende, :dnasc,
             :country, :estado, :cidade, :cep, '0', '0', :phone, :email,
             '0', '0', '0', 'NÃO HÁ', :inicio, :fim, 0)"
    );
    $st->execute([
        ':fullname' => $nome,
        ':cnpj'     => $cnpj,
        ':fantazia' => $nome,
        ':razao'    => $razao ?: $nome,
        ':tipo'     => $tipo,
        ':ende'     => $endereco,
        ':dnasc'    => $datanf,
        ':country'  => $defPais,
        ':estado'   => $defEstado,
        ':cidade'   => $defCidade,
        ':cep'      => $cep,
        ':phone'    => $celular,
        ':email'    => $email,
        ':inicio'   => date('Y-m-d'),
        ':fim'      => date('Y-m-d'),
    ]);

    $newId = (int)$pdo->lastInsertId();
    refCacheFlush('clientes');
    header('location: editar-cliente?cliente=' . $newId);
    exit;
}

// ── Aba ativa ─────────────────────────────────────────────────────────────────
$tab = in_array($_GET['tab'] ?? '', ['favorecido']) ? $_GET['tab'] : 'clientes';

// ── Colunas reais de ct_fornecedor (detectadas em tempo de execução) ──────────
$favColsMap = array_flip(array_column(
    $pdo->query("SHOW COLUMNS FROM ct_fornecedor")->fetchAll(PDO::FETCH_ASSOC),
    'Field'
));

function favBuildFields(array $colsMap, array $postMap): array {
    $fields = []; $vals = [];
    foreach ($postMap as $col => $val) {
        if (isset($colsMap[$col])) {
            $fields[] = $col;
            $vals[":$col"] = $val;
        }
    }
    return [$fields, $vals];
}

// ── Novo Favorecido ───────────────────────────────────────────────────────────
if (isset($_POST['novo_favorecido'])) {
    $fnome = strtoupper(trim($_POST['fav_nome'] ?? ''));
    if ($fnome === '') {
        setFlash('danger', 'Informe o nome do favorecido.');
    } else {
        [$fields, $vals] = favBuildFields($favColsMap, [
            'fullname'    => $fnome,
            'cnpj'        => trim($_POST['fav_cnpj']      ?? ''),
            'phone'       => trim($_POST['fav_phone']     ?? ''),
            'email'       => trim($_POST['fav_email']     ?? ''),
            'nomebanco'   => trim($_POST['fav_banco']     ?? ''),
            'agencia'     => trim($_POST['fav_agencia']   ?? ''),
            'numeroconta' => trim($_POST['fav_conta']     ?? ''),
            'tipoconta'   => trim($_POST['fav_tipoconta'] ?? ''),
            'chavepix'    => trim($_POST['fav_pix']       ?? ''),
        ]);
        $cols = implode(',', $fields);
        $ph   = implode(',', array_map(fn($c) => ":$c", $fields));
        $pdo->prepare("INSERT INTO ct_fornecedor ($cols) VALUES ($ph)")->execute($vals);
        refCacheFlush('fornecedores');
        setFlash('success', 'Favorecido cadastrado com sucesso.');
    }
    header('location: pesquisa-cliente-fornecedor?tab=favorecido');
    exit;
}

// ── Editar Favorecido ─────────────────────────────────────────────────────────
if (isset($_POST['editar_favorecido'])) {
    $eid = (int)($_POST['fav_id'] ?? 0);
    if ($eid > 0) {
        [$fields, $vals] = favBuildFields($favColsMap, [
            'fullname'    => strtoupper(trim($_POST['fav_nome']      ?? '')),
            'cnpj'        => trim($_POST['fav_cnpj']      ?? ''),
            'phone'       => trim($_POST['fav_phone']     ?? ''),
            'email'       => trim($_POST['fav_email']     ?? ''),
            'nomebanco'   => trim($_POST['fav_banco']     ?? ''),
            'agencia'     => trim($_POST['fav_agencia']   ?? ''),
            'numeroconta' => trim($_POST['fav_conta']     ?? ''),
            'tipoconta'   => trim($_POST['fav_tipoconta'] ?? ''),
            'chavepix'    => trim($_POST['fav_pix']       ?? ''),
        ]);
        $sets = implode(', ', array_map(fn($c) => "$c = :$c", $fields));
        $vals[':id'] = $eid;
        $pdo->prepare("UPDATE ct_fornecedor SET $sets WHERE id = :id")->execute($vals);
        refCacheFlush('fornecedores');
        setFlash('success', 'Favorecido atualizado com sucesso.');
    }
    header('location: pesquisa-cliente-fornecedor?tab=favorecido');
    exit;
}

// ── Listagem ──────────────────────────────────────────────────────────────────
if ($tab === 'favorecido') {
    $registros = refFornecedores($pdo);
} else {
    $registros = refClientes($pdo);
}
$clientes = $registros; // compatibilidade com código abaixo
$total    = count($registros);
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
.cli-kpi { background: #f0f6ff; border-radius: 10px; padding: 12px 20px; text-align: center; }
.cli-kpi-label { font-size: 11px; font-weight: 700; color: #6c757d; text-transform: uppercase; letter-spacing: .04em; }
.cli-kpi-value { font-size: 26px; font-weight: 800; color: var(--navy); line-height: 1.1; }
.btn-novo { background: var(--navy); color: #fff; border: none; border-radius: 8px; padding: 10px 22px; font-size: 13px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 7px; transition: background .2s; white-space: nowrap; }
.btn-novo:hover { background: var(--navy-lt); color: #fff; text-decoration: none; }
.results-card { background: #fff; border-radius: 12px; box-shadow: 0 2px 14px rgba(0,0,0,.07); overflow: hidden; }
.results-header { padding: 16px 22px 12px; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px; }
.results-title { font-size: 14px; font-weight: 700; color: var(--navy); display: flex; align-items: center; gap: 8px; }
.results-count { font-size: 11px; background: var(--navy); color: #fff; padding: 2px 9px; border-radius: 20px; font-weight: 600; }
#cli-table { font-size: 12.5px; }
#cli-table thead th { background: var(--navy); color: #fff; font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: .04em; border: none; padding: 9px 8px; white-space: nowrap; }
#cli-table tbody tr { transition: background .12s; }
#cli-table tbody tr:hover td { background: #f0f6ff !important; }
#cli-table tbody td { padding: 7px 8px; vertical-align: middle; border-color: #f0f0f0; }
.cli-nome-cell { font-weight: 600; }
.btn-tbl-edit { background: var(--navy); color: #fff; border: none; border-radius: 6px; padding: 4px 11px; font-size: 12px; font-weight: 600; cursor: pointer; transition: background .2s; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; }
.btn-tbl-edit:hover { background: var(--navy-lt); color: #fff; text-decoration: none; }
.modal-header { background: var(--navy); color: #fff; }
.modal-header .modal-title { color: #fff; font-size: 15px; font-weight: 700; }
.modal-header .close { color: #fff; opacity: .8; text-shadow: none; }
.modal-header .close:hover { opacity: 1; }
.modal-label { font-size: 11px; font-weight: 700; color: #6c757d; text-transform: uppercase; letter-spacing: .05em; display: block; margin-bottom: 5px; }
.modal-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px 18px; }
.modal-grid .full { grid-column: 1 / -1; }
.modal-info { background: #f0f6ff; border-left: 3px solid var(--navy); padding: 9px 13px; font-size: 12px; color: #334155; border-radius: 0 6px 6px 0; grid-column: 1 / -1; }
@media (max-width: 767px) { .map-wrapper { padding: 14px 12px 80px; } .modal-grid { grid-template-columns: 1fr; } }
</style>

<div class="page-content--bgf7">
<div class="map-wrapper">

    <div class="bc-bar">
        <a href="index"><i class="fas fa-home"></i> Home</a>
        <span class="sep">/</span>
        <span>Clientes / Fornecedores</span>
    </div>

    <?php $flash = getFlash(); if ($flash): ?>
    <div class="alert alert-<?= cliEsc($flash['type']) ?> alert-dismissible fade show" role="alert">
        <?= cliEsc($flash['msg']) ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
    </div>
    <?php endif; ?>

    <!-- Abas -->
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:16px;">
        <a href="pesquisa-cliente-fornecedor"
           style="display:inline-flex;align-items:center;gap:6px;padding:8px 18px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;transition:all .18s;
                  <?= $tab !== 'favorecido' ? 'background:var(--navy);color:#fff;' : 'background:#f0f4f8;color:var(--navy);border:1.5px solid #d0dcea;' ?>">
            <i class="fas fa-users"></i> Clientes
        </a>
        <a href="pesquisa-cliente-fornecedor?tab=favorecido"
           style="display:inline-flex;align-items:center;gap:6px;padding:8px 18px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;transition:all .18s;
                  <?= $tab === 'favorecido' ? 'background:var(--navy);color:#fff;' : 'background:#f0f4f8;color:var(--navy);border:1.5px solid #d0dcea;' ?>">
            <i class="fas fa-hand-holding-usd"></i> Favorecidos
        </a>
    </div>

    <div class="filter-card">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;">
            <div>
                <?php if ($tab === 'favorecido'): ?>
                <div class="fc-title"><i class="fas fa-hand-holding-usd"></i> Favorecidos</div>
                <div class="fc-sub">Beneficiários de pagamentos (ct_fornecedor)</div>
                <?php else: ?>
                <div class="fc-title"><i class="fas fa-users"></i> Clientes / Fornecedores</div>
                <div class="fc-sub">Cadastro e gestão de clientes e fornecedores</div>
                <?php endif; ?>
            </div>
            <div style="display:flex;align-items:center;gap:16px;">
                <div class="cli-kpi">
                    <div class="cli-kpi-label">Total</div>
                    <div class="cli-kpi-value"><?= $total ?></div>
                </div>
                <?php if ($tab === 'favorecido'): ?>
                <button type="button" class="btn-novo" onclick="$('#modalFavorecido').modal('show')">
                    <i class="fas fa-plus"></i> Novo Favorecido
                </button>
                <?php else: ?>
                <button type="button" class="btn-novo" onclick="$('#modalCliente').modal('show')">
                    <i class="fas fa-plus"></i> Novo Cadastro
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="results-card">
        <div class="results-header">
            <div class="results-title">
                <i class="fas fa-list"></i> <?= $tab === 'favorecido' ? 'Favorecidos' : 'Cadastros' ?>
                <span class="results-count"><?= $total ?></span>
            </div>
        </div>
        <div class="table-responsive">
            <?php if ($tab === 'favorecido'): ?>
            <table id="fav-table" class="table table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>Nº</th>
                        <th>Nome</th>
                        <th>CPF / CNPJ</th>
                        <th>Telefone</th>
                        <th>E-mail</th>
                        <th>Banco</th>
                        <th>Chave Pix</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($registros as $f): ?>
                <tr>
                    <td><?= (int)$f->id ?></td>
                    <td class="cli-nome-cell"><?= cliEsc($f->fullname) ?></td>
                    <td><?= cliEsc($f->cnpj ?? '') ?></td>
                    <td><?= cliEsc($f->phone ?? '') ?></td>
                    <td><?= cliEsc($f->email ?? '') ?></td>
                    <td><?= cliEsc($f->nomebanco ?? '') ?></td>
                    <td><?= cliEsc($f->chavepix ?? '') ?></td>
                    <td style="white-space:nowrap;">
                        <button type="button" class="btn-tbl-edit btn-fav-edit"
                            data-id="<?= (int)$f->id ?>"
                            data-nome="<?= cliEsc($f->fullname) ?>"
                            data-cnpj="<?= cliEsc($f->cnpj ?? '') ?>"
                            data-phone="<?= cliEsc($f->phone ?? '') ?>"
                            data-email="<?= cliEsc($f->email ?? '') ?>"
                            data-banco="<?= cliEsc($f->nomebanco ?? '') ?>"
                            data-agencia="<?= cliEsc($f->agencia ?? '') ?>"
                            data-conta="<?= cliEsc($f->numeroconta ?? '') ?>"
                            data-tipoconta="<?= cliEsc($f->tipoconta ?? '') ?>"
                            data-pix="<?= cliEsc($f->chavepix ?? '') ?>">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <table id="cli-table" class="table table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>Nº</th>
                        <th>Nome</th>
                        <th>CPF / CNPJ</th>
                        <th>Tipo</th>
                        <th>Telefone</th>
                        <th>E-mail</th>
                        <th>Endereço</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($clientes as $c): ?>
                <tr>
                    <td><?= (int)$c->id ?></td>
                    <td class="cli-nome-cell"><?= cliEsc($c->fullname) ?></td>
                    <td><?= cliEsc($c->cnpj) ?></td>
                    <td><?= cliEsc($c->type) ?></td>
                    <td><?= cliEsc($c->phone ?: $c->tel01) ?></td>
                    <td><?= cliEsc($c->email) ?></td>
                    <td><?= cliEsc($c->address) ?></td>
                    <td style="white-space:nowrap;">
                        <a href="editar-cliente?cliente=<?= (int)$c->id ?>" class="btn-tbl-edit">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

</div>
</div>

<!-- Modal Novo Cliente / Fornecedor -->
<div class="modal cassi-modal fade" id="modalCliente" tabindex="-1" role="dialog" aria-labelledby="modalCliTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCliTitle">
                    <i class="fas fa-user-plus"></i> Novo Cliente / Fornecedor
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
            </div>
            <form action="" method="post" id="form-novo-cliente">
                <div class="modal-body">
                    <div class="modal-grid">

                        <div class="modal-info">
                            <i class="fas fa-info-circle" style="color:var(--navy);margin-right:5px;"></i>
                            Preencha os dados essenciais. Após cadastrar você será redirecionado para completar endereço, estado, cidade e demais informações.
                        </div>

                        <div class="full">
                            <label class="modal-label" for="m-nome">Nome / Nome Fantasia <span style="color:#dc3545">*</span></label>
                            <input type="text" name="nomefantazia" id="m-nome" class="form-control" required
                                   placeholder="Ex: MARIA DA SILVA, AGÊNCIA XYZ LTDA">
                        </div>

                        <div>
                            <label class="modal-label" for="m-razao">Razão Social</label>
                            <input type="text" name="razaosocial" id="m-razao" class="form-control"
                                   placeholder="Deixe em branco para usar o nome acima">
                        </div>

                        <div>
                            <label class="modal-label" for="m-cnpj">CPF / CNPJ / Passaporte <span style="color:#dc3545">*</span></label>
                            <input type="text" name="cnpj" id="m-cnpj" class="form-control" required
                                   placeholder="000.000.000-00">
                        </div>

                        <div>
                            <label class="modal-label" for="m-tipo">Tipo <span style="color:#dc3545">*</span></label>
                            <select name="tipo" id="m-tipo" class="form-control" required>
                                <option value="Pessoa Fisica">Pessoa Física</option>
                                <option value="Pessoa Juridica">Pessoa Jurídica</option>
                                <option value="Estrangeiro(a)">Estrangeiro(a)</option>
                            </select>
                        </div>

                        <div>
                            <label class="modal-label" for="m-celular">Celular <span style="color:#dc3545">*</span></label>
                            <input type="text" name="celular" id="m-celular" class="form-control" required
                                   placeholder="(71) 99999-9999">
                        </div>

                        <div>
                            <label class="modal-label" for="m-email">E-mail</label>
                            <input type="email" name="email" id="m-email" class="form-control"
                                   placeholder="cliente@email.com">
                        </div>

                        <div>
                            <label class="modal-label" for="m-datanf">Data de Nascimento / Fundação</label>
                            <input type="date" name="datanf" id="m-datanf" class="form-control"
                                   value="<?= date('Y-m-d') ?>">
                        </div>

                        <div>
                            <label class="modal-label" for="m-endereco">Endereço</label>
                            <input type="text" name="endereco" id="m-endereco" class="form-control"
                                   placeholder="Rua, número, bairro">
                        </div>

                        <div>
                            <label class="modal-label" for="m-cep">CEP</label>
                            <input type="text" name="cep" id="m-cep" class="form-control"
                                   placeholder="00000-000">
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" name="novocliente" class="btn btn-success">
                        <i class="fas fa-save"></i> Cadastrar e Completar Dados
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (!window.jQuery || !jQuery.fn.DataTable) return;

    var dtOptions = {
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
            zeroRecords: 'Nenhum cadastro encontrado',
            infoEmpty:   'Sem registros',
        },
    };

    if (document.getElementById('fav-table')) {
        jQuery('#fav-table').DataTable(dtOptions);
    } else if (document.getElementById('cli-table')) {
        jQuery('#cli-table').DataTable(Object.assign({}, dtOptions, {
            columnDefs: [{ orderable: false, targets: [7] }],
        }));
    }
});
</script>

<!-- Modal Novo Favorecido -->
<div class="modal cassi-modal fade" id="modalFavorecido" tabindex="-1" role="dialog" aria-labelledby="modalFavTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalFavTitle">
                    <i class="fas fa-hand-holding-usd"></i> Novo Favorecido
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
            </div>
            <form action="pesquisa-cliente-fornecedor?tab=favorecido" method="post">
                <div class="modal-body">
                    <div class="modal-grid">
                        <div class="full">
                            <label class="modal-label" for="n-nome">Nome <span style="color:#dc3545">*</span></label>
                            <input type="text" name="fav_nome" id="n-nome" class="form-control" required placeholder="Ex: EMPRESA XYZ LTDA">
                        </div>
                        <div>
                            <label class="modal-label" for="n-cnpj">CPF / CNPJ</label>
                            <input type="text" name="fav_cnpj" id="n-cnpj" class="form-control" placeholder="00.000.000/0001-00">
                        </div>
                        <div>
                            <label class="modal-label" for="n-phone">Telefone</label>
                            <input type="text" name="fav_phone" id="n-phone" class="form-control" placeholder="(71) 99999-9999">
                        </div>
                        <div class="full">
                            <label class="modal-label" for="n-email">E-mail</label>
                            <input type="email" name="fav_email" id="n-email" class="form-control" placeholder="contato@empresa.com">
                        </div>
                        <div style="grid-column:1/-1; border-top:1px solid #e9ecef; padding-top:12px; margin-top:4px;">
                            <p style="font-size:11px;font-weight:700;color:#6c757d;text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px;">
                                <i class="fas fa-university" style="color:var(--navy);"></i> Dados Bancários
                            </p>
                        </div>
                        <div>
                            <label class="modal-label" for="n-banco">Banco</label>
                            <input type="text" name="fav_banco" id="n-banco" class="form-control" placeholder="Nome do banco">
                        </div>
                        <div>
                            <label class="modal-label" for="n-agencia">Agência</label>
                            <input type="text" name="fav_agencia" id="n-agencia" class="form-control" placeholder="0000-0">
                        </div>
                        <div>
                            <label class="modal-label" for="n-conta">Número da Conta</label>
                            <input type="text" name="fav_conta" id="n-conta" class="form-control" placeholder="00000-0">
                        </div>
                        <div>
                            <label class="modal-label" for="n-tipoconta">Tipo de Conta</label>
                            <select name="fav_tipoconta" id="n-tipoconta" class="form-control">
                                <option value="">Selecione</option>
                                <option value="Corrente">Corrente</option>
                                <option value="Poupança">Poupança</option>
                                <option value="Pagamento">Pagamento</option>
                            </select>
                        </div>
                        <div class="full">
                            <label class="modal-label" for="n-pix">Chave Pix</label>
                            <input type="text" name="fav_pix" id="n-pix" class="form-control" placeholder="CPF, e-mail, telefone ou chave aleatória">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" name="novo_favorecido" class="btn btn-success">
                        <i class="fas fa-save"></i> Cadastrar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Favorecido -->
<div class="modal cassi-modal fade" id="modalEditarFavorecido" tabindex="-1" role="dialog" aria-labelledby="modalEditFavTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditFavTitle">
                    <i class="fas fa-edit"></i> Editar Favorecido
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
            </div>
            <form action="pesquisa-cliente-fornecedor?tab=favorecido" method="post">
                <input type="hidden" name="fav_id" id="edit-fav-id">
                <div class="modal-body">
                    <div class="modal-grid">
                        <div class="full">
                            <label class="modal-label" for="edit-fav-nome">Nome <span style="color:#dc3545">*</span></label>
                            <input type="text" name="fav_nome" id="edit-fav-nome" class="form-control" required>
                        </div>
                        <div>
                            <label class="modal-label" for="edit-fav-cnpj">CPF / CNPJ</label>
                            <input type="text" name="fav_cnpj" id="edit-fav-cnpj" class="form-control">
                        </div>
                        <div>
                            <label class="modal-label" for="edit-fav-phone">Telefone</label>
                            <input type="text" name="fav_phone" id="edit-fav-phone" class="form-control">
                        </div>
                        <div class="full">
                            <label class="modal-label" for="edit-fav-email">E-mail</label>
                            <input type="email" name="fav_email" id="edit-fav-email" class="form-control">
                        </div>
                        <div style="grid-column:1/-1; border-top:1px solid #e9ecef; padding-top:12px; margin-top:4px;">
                            <p style="font-size:11px;font-weight:700;color:#6c757d;text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px;">
                                <i class="fas fa-university" style="color:var(--navy);"></i> Dados Bancários
                            </p>
                        </div>
                        <div>
                            <label class="modal-label" for="edit-fav-banco">Banco</label>
                            <input type="text" name="fav_banco" id="edit-fav-banco" class="form-control">
                        </div>
                        <div>
                            <label class="modal-label" for="edit-fav-agencia">Agência</label>
                            <input type="text" name="fav_agencia" id="edit-fav-agencia" class="form-control">
                        </div>
                        <div>
                            <label class="modal-label" for="edit-fav-conta">Número da Conta</label>
                            <input type="text" name="fav_conta" id="edit-fav-conta" class="form-control">
                        </div>
                        <div>
                            <label class="modal-label" for="edit-fav-tipoconta">Tipo de Conta</label>
                            <select name="fav_tipoconta" id="edit-fav-tipoconta" class="form-control">
                                <option value="">Selecione</option>
                                <option value="Corrente">Corrente</option>
                                <option value="Poupança">Poupança</option>
                                <option value="Pagamento">Pagamento</option>
                            </select>
                        </div>
                        <div class="full">
                            <label class="modal-label" for="edit-fav-pix">Chave Pix</label>
                            <input type="text" name="fav_pix" id="edit-fav-pix" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" name="editar_favorecido" class="btn btn-primary">
                        <i class="fas fa-save"></i> Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// ── Botão Editar Favorecido ───────────────────────────────────────────────────
document.addEventListener('click', function(e) {
    var btn = e.target.closest('.btn-fav-edit');
    if (!btn) return;
    var d = btn.dataset;
    document.getElementById('edit-fav-id').value       = d.id;
    document.getElementById('edit-fav-nome').value     = d.nome;
    document.getElementById('edit-fav-cnpj').value     = d.cnpj;
    document.getElementById('edit-fav-phone').value    = d.phone;
    document.getElementById('edit-fav-email').value    = d.email;
    document.getElementById('edit-fav-banco').value    = d.banco;
    document.getElementById('edit-fav-agencia').value  = d.agencia;
    document.getElementById('edit-fav-conta').value    = d.conta;
    document.getElementById('edit-fav-pix').value      = d.pix;
    var sel = document.getElementById('edit-fav-tipoconta');
    for (var i = 0; i < sel.options.length; i++) {
        sel.options[i].selected = (sel.options[i].value === d.tipoconta);
    }
    if (window.jQuery) jQuery('#modalEditarFavorecido').modal('show');
});

// ── Fix: footer.php tem $('.modal').modal('show') que abre todas as modais ─────
// window.load dispara APÓS os handlers $(document).ready do footer, cancelando o auto-show.
window.addEventListener('load', function() {
    if (window.jQuery) {
        jQuery('.cassi-modal').modal('hide');
    }
});
</script>

<?php require_once 'footer.php'; ?>
