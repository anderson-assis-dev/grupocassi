<?php
require_once 'header.php';
require_once __DIR__ . '/includes/flash.php';
$pdo->exec("set names utf8");

function ecli($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

// ── Resolve ID ────────────────────────────────────────────────────────────────
$idCliente = (int)($_POST['cliente'] ?? $_GET['cliente'] ?? 0);
if ($idCliente <= 0) {
    header('location: pesquisa-cliente-fornecedor');
    exit;
}

$redirBase   = 'location: editar-cliente?cliente=' . $idCliente;
$redirTarifa = $redirBase . '&tab=tarifa';

// ── Handler: atualizar dados do cliente ───────────────────────────────────────
if (isset($_POST['clientupdate'])) {
    $lim = str_replace(',', '.', str_replace('.', '', $_POST['limitereserva'] ?? '0'));
    $pdo->prepare(
        "UPDATE ct_cliente SET cnpj=:cnpj, namefantazia=:namef, corporatename=:rs, type=:tipo,
         address=:ende, datefundation=:df, stateenrollment=:re, municipalregistration=:mr,
         idcountry=:pais, idstate=:estado, idcity=:cidade, cep=:cep, tel01=:tel01, tel02=:tel02,
         phone=:phone, email=:email, register=:embratur, periodoinicial=:inicio,
         periodofinal=:final, limite=:limite, observacao=:obs WHERE id=:id"
    )->execute([
        ':cnpj'     => $_POST['cnpj'],           ':namef'    => strtoupper($_POST['nomefantazia']),
        ':rs'       => strtoupper($_POST['razaosocial']), ':tipo' => $_POST['tipo'],
        ':ende'     => $_POST['endereco'],        ':df'       => $_POST['datanf'],
        ':pais'     => $_POST['pais'],            ':estado'   => $_POST['estado'],
        ':cidade'   => $_POST['cidade'],          ':cep'      => $_POST['cep'],
        ':tel01'    => $_POST['telefone1'],        ':tel02'    => $_POST['telefone2'],
        ':phone'    => $_POST['celular'],          ':email'    => $_POST['email'],
        ':re'       => $_POST['inscricaoes'],      ':mr'       => $_POST['inscricaomu'],
        ':embratur' => $_POST['registroembratur'], ':inicio'  => $_POST['periodoinicial'],
        ':final'    => $_POST['periodofinal'],     ':limite'  => $lim,
        ':obs'      => $_POST['observacao'],       ':id'      => $idCliente,
    ]);
    setFlash('success', 'Dados do cliente atualizados com sucesso.');
    header($redirBase);
    exit;
}

// ── Handler: adicionar tarifas (múltiplos serviços) ──────────────────────────
if (isset($_POST['salvartarifa'])) {
    $ids = array_filter(array_map('intval', (array)($_POST['idservice'] ?? [])));
    if (empty($ids)) {
        setFlash('warning', 'Selecione ao menos um serviço.');
        header($redirTarifa);
        exit;
    }
    $ins = $pdo->prepare("INSERT INTO ct_clientservice VALUES (DEFAULT, :c, :s, :v)");
    $chk = $pdo->prepare("SELECT COUNT(*) FROM ct_clientservice WHERE idclient=:c AND idservice=:s");
    $added = $skipped = 0;
    foreach ($ids as $sid) {
        $rawVal = str_replace(',', '.', str_replace('.', '', $_POST['val_' . $sid] ?? '0,00'));
        $chk->execute([':c' => $idCliente, ':s' => $sid]);
        if ((int)$chk->fetchColumn() > 0) {
            $skipped++;
        } else {
            $ins->execute([':c' => $idCliente, ':s' => $sid, ':v' => $rawVal]);
            $added++;
        }
    }
    if ($added > 0) {
        $msg = $added . ' tarifa(s) adicionada(s) com sucesso.';
        if ($skipped > 0) {
            $msg .= ' ' . $skipped . ' serviço(s) ignorado(s) por já existirem no tarifário.';
        }
        setFlash('success', $msg);
    } else {
        setFlash('warning', 'Todos os serviços selecionados já estavam no tarifário.');
    }
    header($redirTarifa);
    exit;
}

// ── Handler: editar tarifa ────────────────────────────────────────────────────
if (isset($_POST['salvar'])) {
    $pdo->prepare("UPDATE ct_clientservice SET valuenet=:v WHERE id=:id AND idclient=:c")
        ->execute([':v' => $_POST['valor'], ':id' => (int)$_POST['idnet'], ':c' => $idCliente]);
    setFlash('success', 'Tarifa atualizada com sucesso.');
    header($redirTarifa);
    exit;
}

// ── Handler: excluir tarifa ───────────────────────────────────────────────────
if (isset($_POST['excluirtarifa'])) {
    $pdo->prepare("DELETE FROM ct_clientservice WHERE id=:id AND idclient=:c")
        ->execute([':id' => (int)$_POST['idnet'], ':c' => $idCliente]);
    setFlash('success', 'Tarifa removida.');
    header($redirTarifa);
    exit;
}

// ── Carrega dados ─────────────────────────────────────────────────────────────
$stCli = $pdo->prepare('SELECT * FROM ct_cliente WHERE id=:id');
$stCli->execute([':id' => $idCliente]);
$cli = $stCli->fetch(PDO::FETCH_ASSOC);
if (!$cli) {
    setFlash('danger', 'Cliente não encontrado.');
    header('location: pesquisa-cliente-fornecedor');
    exit;
}

$paises  = $pdo->query('SELECT id, name FROM ct_pais   ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
$estados = $pdo->query('SELECT id, name FROM ct_estado ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
$cidades = $pdo->query('SELECT id, name FROM ct_cidade ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);

// Tarifário atual
$stTar = $pdo->prepare(
    "SELECT cs.id, cs.idservice, cs.valuenet, s.fullname AS servico
     FROM ct_clientservice cs
     LEFT JOIN ct_servico s ON s.id = cs.idservice
     WHERE cs.idclient = :id ORDER BY s.fullname"
);
$stTar->execute([':id' => $idCliente]);
$tarifas      = $stTar->fetchAll(PDO::FETCH_ASSOC);
$totalTarifas = count($tarifas);

// Serviços disponíveis para adicionar
$idsUsados = array_column($tarifas, 'idservice');
if ($idsUsados) {
    $ph = implode(',', array_fill(0, count($idsUsados), '?'));
    $stDisp = $pdo->prepare("SELECT id, fullname FROM ct_servico WHERE id NOT IN ($ph) ORDER BY fullname");
    $stDisp->execute($idsUsados);
} else {
    $stDisp = $pdo->query("SELECT id, fullname FROM ct_servico ORDER BY fullname");
}
$servicosDisp = $stDisp->fetchAll(PDO::FETCH_ASSOC);

$activeTab = ($_GET['tab'] ?? '') === 'tarifa' ? 'tarifa' : 'dados';
?>
<style>
:root { --navy: #1e4770; --navy-lt: #2a5f96; }
.map-wrapper { padding: 20px 20px 80px; }
.bc-bar { padding: 0 0 16px; font-size: 13px; color: #6c757d; }
.bc-bar a { color: var(--navy); font-weight: 600; text-decoration: none; }
.bc-bar a:hover { text-decoration: underline; }
.bc-bar .sep { margin: 0 6px; color: #ccc; }

/* Header card */
.hdr-card { background: #fff; border-radius: 12px; box-shadow: 0 2px 14px rgba(0,0,0,.07); padding: 18px 24px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; }
.hdr-name { font-size: 17px; font-weight: 800; color: var(--navy); }
.hdr-sub  { font-size: 12px; color: #6c757d; margin-top: 2px; }
.badge-tipo { background: #e8f0fb; color: var(--navy); border-radius: 6px; padding: 3px 10px; font-size: 11px; font-weight: 700; }
.btn-voltar { background: transparent; color: var(--navy); border: 2px solid var(--navy); border-radius: 8px; padding: 8px 18px; font-size: 13px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; transition: all .2s; }
.btn-voltar:hover { background: var(--navy); color: #fff; text-decoration: none; }

/* Tabs */
.tab-wrapper { background: #fff; border-radius: 12px; box-shadow: 0 2px 14px rgba(0,0,0,.07); overflow: hidden; }
.nav-tabs-navy { border-bottom: 2px solid #e9ecef !important; padding: 0 24px; flex-wrap: nowrap; }
.nav-tabs-navy .nav-item { margin-bottom: -2px; }
.nav-tabs-navy .nav-link { border: none !important; border-radius: 0 !important; border-bottom: 2px solid transparent !important; background: transparent !important; color: #6c757d; font-size: 13px; font-weight: 600; padding: 14px 20px; display: inline-flex; align-items: center; gap: 7px; transition: color .2s; }
.nav-tabs-navy .nav-link:hover { color: var(--navy); text-decoration: none; }
.nav-tabs-navy .nav-link.active { color: var(--navy) !important; border-bottom-color: var(--navy) !important; }
.tab-badge { background: var(--navy); color: #fff; border-radius: 20px; font-size: 10px; font-weight: 700; padding: 1px 7px; }
.tab-pane { padding: 26px 28px 24px; }

/* Form */
.form-section { margin-bottom: 22px; }
.form-section-title { font-size: 11px; font-weight: 700; color: var(--navy); text-transform: uppercase; letter-spacing: .07em; padding-bottom: 8px; border-bottom: 1px solid #e9ecef; margin-bottom: 14px; display: flex; align-items: center; gap: 6px; }
.fg2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px 20px; }
.fg3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px 20px; }
.fg4 { display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 14px 20px; }
.full { grid-column: 1 / -1; }
.fg-label { font-size: 11px; font-weight: 700; color: #6c757d; text-transform: uppercase; letter-spacing: .05em; display: block; margin-bottom: 5px; }
.fg-label span { color: #dc3545; }
.form-control { border: 1.5px solid #dee2e6; border-radius: 8px; font-size: 13px; height: 38px; transition: border-color .2s; }
.form-control:focus { border-color: var(--navy); box-shadow: 0 0 0 3px rgba(30,71,112,.12); outline: none; }
select.form-control { height: 38px; }
textarea.form-control { height: auto; resize: vertical; }
.btn-save { background: var(--navy); color: #fff; border: none; border-radius: 8px; padding: 11px 28px; font-size: 14px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: background .2s; }
.btn-save:hover { background: var(--navy-lt); }
.form-actions { display: flex; gap: 12px; justify-content: flex-end; margin-top: 6px; padding-top: 18px; border-top: 1px solid #f0f0f0; }

/* Tarifário */
.tar-header { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 18px; }
.tar-title  { font-size: 14px; font-weight: 700; color: var(--navy); display: flex; align-items: center; gap: 8px; }
.tar-count  { font-size: 11px; background: var(--navy); color: #fff; padding: 2px 9px; border-radius: 20px; font-weight: 600; }
.btn-novo   { background: var(--navy); color: #fff; border: none; border-radius: 8px; padding: 9px 20px; font-size: 13px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 7px; transition: background .2s; white-space: nowrap; }
.btn-novo:hover { background: var(--navy-lt); }
#tar-table { font-size: 12.5px; }
#tar-table thead th { background: var(--navy); color: #fff; font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: .04em; border: none; padding: 9px 8px; white-space: nowrap; }
#tar-table tbody tr { transition: background .12s; }
#tar-table tbody tr:hover td { background: #f0f6ff !important; }
#tar-table tbody td { padding: 7px 8px; vertical-align: middle; border-color: #f0f0f0; }
.tar-val  { font-weight: 700; color: var(--navy); }
.btn-tbl-edit { background: var(--navy); color: #fff; border: none; border-radius: 6px; padding: 4px 11px; font-size: 12px; font-weight: 600; cursor: pointer; transition: background .2s; }
.btn-tbl-edit:hover { background: var(--navy-lt); color: #fff; }
.btn-tbl-del  { background: #dc3545; color: #fff; border: none; border-radius: 6px; padding: 4px 11px; font-size: 12px; font-weight: 600; cursor: pointer; transition: background .2s; margin-left: 4px; }
.btn-tbl-del:hover { background: #b91c1c; color: #fff; }
.empty-tar { text-align: center; padding: 40px 24px; color: #aaa; }
.empty-tar i { font-size: 36px; display: block; margin-bottom: 10px; }

/* Modal serviços multi-select */
.serv-search-bar { padding: 14px 18px 10px; background: #f8fafc; border-bottom: 1px solid #e9ecef; }
.serv-search-row { display: flex; align-items: center; gap: 14px; }
.serv-search-input { flex: 1; height: 36px; border-radius: 8px; border: 1.5px solid #dee2e6; font-size: 13px; padding: 0 10px; }
.serv-search-input:focus { border-color: var(--navy); outline: none; }
.serv-selall { display: flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; color: #6c757d; margin: 0; white-space: nowrap; cursor: pointer; user-select: none; }
.serv-scroll { max-height: 340px; overflow-y: auto; }
.serv-table { font-size: 12.5px; width: 100%; border-collapse: collapse; }
.serv-table thead th { background: #f0f4f8; color: #6c757d; font-size: 10px; text-transform: uppercase; letter-spacing: .04em; font-weight: 700; border-bottom: 1px solid #e9ecef; padding: 8px 10px; position: sticky; top: 0; z-index: 2; }
.serv-table tbody td { padding: 6px 10px; border-bottom: 1px solid #f5f5f5; vertical-align: middle; }
.serv-row.sv-hidden { display: none; }
.serv-row.selected td { background: #f0f6ff; }
.serv-nome-label { margin: 0; font-weight: 400; cursor: pointer; font-size: 12.5px; }
.serv-row.selected .serv-nome-label { font-weight: 600; color: var(--navy); }
.serv-val { border: 1.5px solid #dee2e6 !important; border-radius: 6px !important; height: 30px; padding: 0 7px; font-size: 12px; width: 110px; }
.serv-val:not([readonly]) { border-color: var(--navy) !important; background: #fff; }
.serv-val[readonly] { background: #f8fafc; color: #aaa; cursor: default; }
.serv-pagination { display: flex; align-items: center; justify-content: space-between; padding: 8px 16px; border-top: 1px solid #f0f0f0; background: #fafafa; font-size: 12px; color: #6c757d; }
.serv-pag-btns { display: flex; align-items: center; gap: 10px; font-size: 12px; }
.serv-pag-btn { background: #fff; border: 1px solid #dee2e6; border-radius: 6px; padding: 3px 11px; font-size: 12px; cursor: pointer; transition: all .15s; }
.serv-pag-btn:hover:not(:disabled) { background: var(--navy); color: #fff; border-color: var(--navy); }
.serv-pag-btn:disabled { opacity: .4; cursor: default; }

/* Modal */
.modal-header { background: var(--navy); color: #fff; }
.modal-header .modal-title { color: #fff; font-size: 15px; font-weight: 700; }
.modal-header .close { color: #fff; opacity: .8; text-shadow: none; }
.modal-header .close:hover { opacity: 1; }
.modal-label { font-size: 11px; font-weight: 700; color: #6c757d; text-transform: uppercase; letter-spacing: .05em; display: block; margin-bottom: 5px; }

@media (max-width: 991px) { .fg3 { grid-template-columns: 1fr 1fr; } .fg4 { grid-template-columns: 1fr 1fr; } }
@media (max-width: 767px)  { .map-wrapper { padding: 14px 12px 80px; } .fg2,.fg3,.fg4 { grid-template-columns: 1fr; } .tab-pane { padding: 18px 16px; } }
</style>

<div class="page-content--bgf7">
<div class="map-wrapper">

    <div class="bc-bar">
        <a href="index"><i class="fas fa-home"></i> Home</a>
        <span class="sep">/</span>
        <a href="pesquisa-cliente-fornecedor">Clientes / Fornecedores</a>
        <span class="sep">/</span>
        <span><?= ecli($cli['fullname']) ?></span>
    </div>

    <?php $flash = getFlash(); if ($flash): ?>
    <div class="alert alert-<?= ecli($flash['type']) ?> alert-dismissible fade show" role="alert">
        <?= $flash['msg'] ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
    </div>
    <?php endif; ?>

    <!-- Cabeçalho do cliente -->
    <div class="hdr-card">
        <div>
            <div class="hdr-name"><?= ecli($cli['fullname']) ?></div>
            <div class="hdr-sub">
                CPF/CNPJ: <strong><?= ecli($cli['cnpj']) ?></strong>
                &nbsp;&bull;&nbsp; ID: <strong><?= $idCliente ?></strong>
                &nbsp;&bull;&nbsp; <span class="badge-tipo"><?= ecli($cli['type']) ?></span>
            </div>
        </div>
        <a href="pesquisa-cliente-fornecedor" class="btn-voltar">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>

    <!-- Tabs -->
    <div class="tab-wrapper">
        <ul class="nav nav-tabs nav-tabs-navy" id="clienteTabs">
            <li class="nav-item">
                <a class="nav-link" id="tab-dados-link"
                   data-toggle="tab" href="#tab-dados" role="tab">
                    <i class="fas fa-user-edit"></i> Dados Cadastrais
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab-tarifa-link"
                   data-toggle="tab" href="#tab-tarifa" role="tab">
                    <i class="fas fa-tags"></i> Tarifário
                    <span class="tab-badge"><?= $totalTarifas ?></span>
                </a>
            </li>
        </ul>

        <div class="tab-content">

            <!-- ── Tab: Dados Cadastrais ─────────────────────────────────── -->
            <div class="tab-pane fade" id="tab-dados" role="tabpanel">
                <form action="" method="post" autocomplete="off">
                    <input type="hidden" name="cliente" value="<?= $idCliente ?>">

                    <div class="form-section">
                        <div class="form-section-title"><i class="fas fa-id-card"></i> Identificação</div>
                        <div class="fg2">
                            <div class="full">
                                <label class="fg-label" for="cnpj">CPF / CNPJ / Passaporte</label>
                                <input type="text" name="cnpj" id="cnpj" class="form-control"
                                       value="<?= ecli($cli['cnpj']) ?>">
                            </div>
                            <div>
                                <label class="fg-label" for="nomefantazia">Nome Fantasia</label>
                                <input type="text" name="nomefantazia" id="nomefantazia" class="form-control"
                                       value="<?= ecli($cli['namefantazia']) ?>">
                            </div>
                            <div>
                                <label class="fg-label" for="razaosocial">Razão Social</label>
                                <input type="text" name="razaosocial" id="razaosocial" class="form-control"
                                       value="<?= ecli($cli['corporatename']) ?>">
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="form-section-title"><i class="fas fa-info-circle"></i> Informações Gerais</div>
                        <div class="fg3">
                            <div>
                                <label class="fg-label" for="tipo">Tipo</label>
                                <select name="tipo" id="tipo" class="form-control">
                                    <?php
                                    $tipos = ['Pessoa Fisica', 'Pessoa Juridica', 'Estrangeiro(a)'];
                                    foreach ($tipos as $t) { ?>
                                    <option value="<?= ecli($t) ?>" <?= $cli['type'] === $t ? 'selected' : '' ?>>
                                        <?= ecli($t) ?>
                                    </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div>
                                <label class="fg-label" for="datanf">Data Nascimento / Fundação</label>
                                <input type="date" name="datanf" id="datanf" class="form-control"
                                       value="<?= ecli($cli['datefundation']) ?>">
                            </div>
                            <div>
                                <label class="fg-label" for="observacao">Observação</label>
                                <input type="text" name="observacao" id="observacao" class="form-control"
                                       value="<?= ecli($cli['observacao']) ?>">
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="form-section-title"><i class="fas fa-map-marker-alt"></i> Endereço</div>
                        <div class="fg2">
                            <div class="full">
                                <label class="fg-label" for="endereco">Endereço</label>
                                <input type="text" name="endereco" id="endereco" class="form-control"
                                       value="<?= ecli($cli['address']) ?>">
                            </div>
                            <div>
                                <label class="fg-label" for="pais">País</label>
                                <select name="pais" id="pais" class="form-control">
                                    <?php foreach ($paises as $p): ?>
                                    <option value="<?= (int)$p['id'] ?>" <?= $cli['idcountry'] == $p['id'] ? 'selected' : '' ?>>
                                        <?= ecli($p['name']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="fg-label" for="estado">Estado</label>
                                <select name="estado" id="estado" class="form-control">
                                    <?php foreach ($estados as $e): ?>
                                    <option value="<?= (int)$e['id'] ?>" <?= $cli['idstate'] == $e['id'] ? 'selected' : '' ?>>
                                        <?= ecli($e['name']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="fg-label" for="cidade">Cidade</label>
                                <select name="cidade" id="cidade" class="form-control">
                                    <?php foreach ($cidades as $c): ?>
                                    <option value="<?= (int)$c['id'] ?>" <?= $cli['idcity'] == $c['id'] ? 'selected' : '' ?>>
                                        <?= ecli($c['name']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="fg-label" for="cep">CEP</label>
                                <input type="text" name="cep" id="cep" class="form-control"
                                       value="<?= ecli($cli['cep']) ?>">
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="form-section-title"><i class="fas fa-phone"></i> Contato</div>
                        <div class="fg4">
                            <div>
                                <label class="fg-label" for="telefone1">Telefone 01</label>
                                <input type="text" name="telefone1" id="telefone1" class="form-control"
                                       value="<?= ecli($cli['tel01']) ?>">
                            </div>
                            <div>
                                <label class="fg-label" for="telefone2">Telefone 02</label>
                                <input type="text" name="telefone2" id="telefone2" class="form-control"
                                       value="<?= ecli($cli['tel02']) ?>">
                            </div>
                            <div>
                                <label class="fg-label" for="celular">Celular</label>
                                <input type="text" name="celular" id="celular" class="form-control"
                                       value="<?= ecli($cli['phone']) ?>">
                            </div>
                            <div>
                                <label class="fg-label" for="email">E-mail</label>
                                <input type="email" name="email" id="email" class="form-control"
                                       value="<?= ecli($cli['email']) ?>">
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="form-section-title"><i class="fas fa-file-alt"></i> Registros e Período</div>
                        <div class="fg4">
                            <div>
                                <label class="fg-label" for="inscricaoes">Inscrição Estadual</label>
                                <input type="text" name="inscricaoes" id="inscricaoes" class="form-control"
                                       value="<?= ecli($cli['stateenrollment']) ?>">
                            </div>
                            <div>
                                <label class="fg-label" for="inscricaomu">Inscrição Municipal</label>
                                <input type="text" name="inscricaomu" id="inscricaomu" class="form-control"
                                       value="<?= ecli($cli['municipalregistration']) ?>">
                            </div>
                            <div>
                                <label class="fg-label" for="registroembratur">Id Usuário</label>
                                <input type="text" name="registroembratur" id="registroembratur" class="form-control"
                                       value="<?= ecli($cli['register']) ?>">
                            </div>
                            <div>
                                <label class="fg-label" for="limitereserva">Limite de Reserva</label>
                                <input type="text" name="limitereserva" id="limitereserva" class="form-control"
                                       value="<?= ecli(number_format((float)$cli['limite'], 2, ',', '.')) ?>"
                                       onkeypress="return moeda(this,'.',',',event)">
                            </div>
                            <div>
                                <label class="fg-label" for="periodoinicial">Período Inicial</label>
                                <input type="date" name="periodoinicial" id="periodoinicial" class="form-control"
                                       value="<?= ecli($cli['periodoinicial']) ?>">
                            </div>
                            <div>
                                <label class="fg-label" for="periodofinal">Período Final</label>
                                <input type="date" name="periodofinal" id="periodofinal" class="form-control"
                                       value="<?= ecli($cli['periodofinal']) ?>">
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" name="clientupdate" class="btn-save">
                            <i class="fas fa-save"></i> Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>

            <!-- ── Tab: Tarifário ────────────────────────────────────────── -->
            <div class="tab-pane fade" id="tab-tarifa" role="tabpanel">

                <div class="tar-header">
                    <div class="tar-title">
                        <i class="fas fa-tags"></i> Tarifário
                        <span class="tar-count"><?= $totalTarifas ?></span>
                    </div>
                    <?php if ($servicosDisp): ?>
                    <button type="button" class="btn-novo" data-toggle="modal" data-target="#modalAdicionarTarifa">
                        <i class="fas fa-plus"></i> Adicionar Tarifa
                    </button>
                    <?php endif; ?>
                </div>

                <?php if ($totalTarifas === 0): ?>
                <div class="empty-tar">
                    <i class="fas fa-tags"></i>
                    Nenhuma tarifa cadastrada para este cliente.<br>
                    <small>Clique em "Adicionar Tarifa" para começar.</small>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table id="tar-table" class="table table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>Serviço</th>
                                <th class="text-right">Valor Net (R$)</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($tarifas as $tar): ?>
                        <tr>
                            <td><?= ecli($tar['servico'] ?? '—') ?></td>
                            <td class="tar-val text-right">
                                <?= number_format((float)$tar['valuenet'], 2, ',', '.') ?>
                            </td>
                            <td style="white-space:nowrap;">
                                <button type="button" class="btn-tbl-edit btn-editar-tarifa"
                                    data-id="<?= (int)$tar['id'] ?>"
                                    data-servico="<?= ecli($tar['servico'] ?? '') ?>"
                                    data-valor="<?= number_format((float)$tar['valuenet'], 2, ',', '.') ?>">
                                    <i class="fas fa-edit"></i> Editar
                                </button>
                                <form action="" method="post" class="form-excluir-tar d-inline">
                                    <input type="hidden" name="cliente" value="<?= $idCliente ?>">
                                    <input type="hidden" name="idnet"   value="<?= (int)$tar['id'] ?>">
                                    <input type="hidden" name="nometar" value="<?= ecli($tar['servico'] ?? '') ?>">
                                    <button type="submit" name="excluirtarifa" class="btn-tbl-del">
                                        <i class="fas fa-trash"></i> Excluir
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>

        </div><!-- /.tab-content -->
    </div><!-- /.tab-wrapper -->

</div>
</div>

<!-- ── Modal: Adicionar Tarifa ────────────────────────────────────────────── -->
<div class="modal fade" id="modalAdicionarTarifa" tabindex="-1" role="dialog" aria-labelledby="modalAddTarTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAddTarTitle">
                    <i class="fas fa-plus-circle"></i> Adicionar Tarifa
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
            </div>
            <form action="" method="post" id="form-add-tarifa">
                <input type="hidden" name="cliente" value="<?= $idCliente ?>">

                <div class="serv-search-bar">
                    <div class="serv-search-row">
                        <input type="text" id="serv-search" class="serv-search-input" placeholder="Buscar serviço...">
                        <label class="serv-selall" for="serv-selall-chk">
                            <input type="checkbox" id="serv-selall-chk">
                            Selecionar página
                        </label>
                    </div>
                </div>

                <div class="serv-scroll">
                    <table class="serv-table">
                        <thead>
                            <tr>
                                <th style="width:36px;"></th>
                                <th>Serviço</th>
                                <th style="width:130px;">Valor Net (R$)</th>
                            </tr>
                        </thead>
                        <tbody id="serv-tbody">
                            <?php foreach ($servicosDisp as $sv): ?>
                            <tr class="serv-row" data-nome="<?= ecli(mb_strtolower($sv['fullname'])) ?>">
                                <td style="text-align:center;">
                                    <input type="checkbox" name="idservice[]"
                                           value="<?= (int)$sv['id'] ?>"
                                           id="sc<?= (int)$sv['id'] ?>"
                                           class="serv-chk">
                                </td>
                                <td>
                                    <label class="serv-nome-label" for="sc<?= (int)$sv['id'] ?>">
                                        <?= ecli($sv['fullname']) ?>
                                    </label>
                                </td>
                                <td>
                                    <input type="text" name="val_<?= (int)$sv['id'] ?>"
                                           class="serv-val"
                                           value="0,00" readonly
                                           onkeypress="return moeda(this,'.',',',event)">
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="serv-pagination">
                    <span id="serv-pag-info">Exibindo 1–20 de <?= count($servicosDisp) ?></span>
                    <div class="serv-pag-btns">
                        <button type="button" class="serv-pag-btn" id="serv-pag-prev" disabled>‹ Anterior</button>
                        <button type="button" class="serv-pag-btn" id="serv-pag-next">Próxima ›</button>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" name="salvartarifa" id="btn-salvar-tarifas" class="btn btn-success" disabled>
                        <i class="fas fa-save"></i> <span id="btn-salvar-label">Selecione serviços</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── Modal: Editar Tarifa ───────────────────────────────────────────────── -->
<div class="modal fade" id="modalEditarTarifa" tabindex="-1" role="dialog" aria-labelledby="modalEditTarTitle" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditTarTitle">
                    <i class="fas fa-edit"></i> Editar Tarifa
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
            </div>
            <form action="" method="post">
                <input type="hidden" name="cliente" value="<?= $idCliente ?>">
                <input type="hidden" name="idnet"   id="edit-idnet" value="">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="modal-label" for="edit-servico-nome">Serviço</label>
                        <input type="text" id="edit-servico-nome" class="form-control" disabled>
                    </div>
                    <div class="form-group">
                        <label class="modal-label" for="edit-valor">Valor Net (R$) <span style="color:#dc3545">*</span></label>
                        <input type="text" name="valor" id="edit-valor" class="form-control" required
                               onkeypress="return moeda(this,'.',',',event)">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" name="salvar" class="btn btn-primary">
                        <i class="fas fa-save"></i> Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// ── Máscara monetária ──────────────────────────────────────────────────────────
function moeda(a, e, r, t) {
    var n='',h=j=0,u=tam2=0,l=ajd2='',o=window.Event?t.which:t.keyCode;
    if(13===o||8===o)return true;
    n=String.fromCharCode(o);
    if(-1==='0123456789'.indexOf(n))return false;
    for(u=a.value.length,h=0;h<u&&('0'===a.value.charAt(h)||a.value.charAt(h)===r);h++);
    for(l='';h<u;h++)-1!=='0123456789'.indexOf(a.value.charAt(h))&&(l+=a.value.charAt(h));
    l+=n;
    if(0===(u=l.length)){a.value='';}
    else if(1===u){a.value='0'+r+'0'+l;}
    else if(2===u){a.value='0'+r+l;}
    else{
        ajd2='';j=0;
        for(h=u-3;h>=0;h--){3===j&&(ajd2+=e,j=0);ajd2+=l.charAt(h);j++;}
        a.value='';tam2=ajd2.length;
        for(h=tam2-1;h>=0;h--)a.value+=ajd2.charAt(h);
        a.value+=r+l.substr(u-2,u);
    }
    return false;
}

document.addEventListener('DOMContentLoaded', function () {
    // ── Ativar tab correta baseada na URL ─────────────────────────────────────
    var tabParam = new URLSearchParams(window.location.search).get('tab');
    $('#' + (tabParam === 'tarifa' ? 'tab-tarifa-link' : 'tab-dados-link')).tab('show');

    // ── DataTable tarifário ───────────────────────────────────────────────────
    if (window.jQuery && jQuery.fn.DataTable && document.getElementById('tar-table')) {
        jQuery('#tar-table').DataTable({
            dom: '<"d-flex justify-content-between align-items-center mb-2"Bf>rtip',
            buttons: [
                { extend: 'excelHtml5', text: '<i class="fas fa-file-excel"></i> Excel', className: 'btn btn-sm btn-success mr-1' },
                { extend: 'copyHtml5',  text: '<i class="fas fa-copy"></i> Copiar',      className: 'btn btn-sm btn-dark mr-1' },
            ],
            pageLength: 50,
            order: [[0, 'asc']],
            language: {
                search: 'Buscar:', lengthMenu: 'Exibir _MENU_ por página',
                info: '_START_–_END_ de _TOTAL_',
                paginate: { first:'«', last:'»', next:'›', previous:'‹' },
                zeroRecords: 'Nenhum serviço encontrado', infoEmpty: 'Sem registros',
            },
            columnDefs: [{ orderable: false, targets: [2] }],
        });
    }

    // ── Modal editar tarifa ───────────────────────────────────────────────────
    document.querySelectorAll('.btn-editar-tarifa').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var d = this.dataset;
            document.getElementById('edit-idnet').value       = d.id;
            document.getElementById('edit-servico-nome').value = d.servico;
            document.getElementById('edit-valor').value        = d.valor;
            $('#modalEditarTarifa').modal('show');
        });
    });

    // ── Confirmar exclusão ────────────────────────────────────────────────────
    document.querySelectorAll('.form-excluir-tar').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            var nome = form.querySelector('[name="nometar"]').value;
            if (!confirm('Excluir a tarifa de "' + nome + '"?')) e.preventDefault();
        });
    });

    // ── Modal: Adicionar Tarifa — multi-select com busca e paginação ──────────
    (function () {
        var PAGE_SIZE = 20;
        var curPage   = 0;
        var searchVal = '';
        var allRows   = [];

        function getFiltered() {
            if (!searchVal) return allRows;
            return allRows.filter(function (r) {
                return r.dataset.nome.indexOf(searchVal) !== -1;
            });
        }

        function renderPage() {
            var filtered = getFiltered();
            var total    = filtered.length;
            var start    = curPage * PAGE_SIZE;
            var end      = Math.min(start + PAGE_SIZE, total);

            allRows.forEach(function (r) { r.style.display = 'none'; });
            for (var i = start; i < end; i++) { filtered[i].style.display = ''; }

            var infoEl = document.getElementById('serv-pag-info');
            infoEl.textContent = total === 0
                ? 'Nenhum serviço encontrado'
                : 'Exibindo ' + (start + 1) + '–' + end + ' de ' + total;

            document.getElementById('serv-pag-prev').disabled = curPage === 0;
            document.getElementById('serv-pag-next').disabled = end >= total;

            updateSelAll();
        }

        function updateSelAll() {
            var filtered  = getFiltered();
            var start     = curPage * PAGE_SIZE;
            var end       = Math.min(start + PAGE_SIZE, filtered.length);
            var pageRows  = filtered.slice(start, end);
            var allChk    = pageRows.length > 0 && pageRows.every(function (r) {
                return r.querySelector('.serv-chk').checked;
            });
            document.getElementById('serv-selall-chk').checked = allChk;
        }

        function updateBtn() {
            var n   = document.querySelectorAll('#serv-tbody .serv-chk:checked').length;
            var btn = document.getElementById('btn-salvar-tarifas');
            var lbl = document.getElementById('btn-salvar-label');
            btn.disabled      = n === 0;
            lbl.textContent   = n === 0 ? 'Selecione serviços' : 'Salvar ' + n + ' selecionado(s)';
        }

        function toggleRow(row, checked) {
            var inp = row.querySelector('.serv-val');
            if (checked) {
                row.classList.add('selected');
                inp.removeAttribute('readonly');
            } else {
                row.classList.remove('selected');
                inp.setAttribute('readonly', '');
                inp.value = '0,00';
            }
        }

        $('#modalAdicionarTarifa').on('show.bs.modal', function () {
            allRows   = Array.from(document.querySelectorAll('#serv-tbody .serv-row'));
            curPage   = 0;
            searchVal = '';
            document.getElementById('serv-search').value = '';
            document.getElementById('serv-selall-chk').checked = false;
            allRows.forEach(function (r) {
                var chk = r.querySelector('.serv-chk');
                chk.checked = false;
                toggleRow(r, false);
            });
            renderPage();
            updateBtn();
        });

        document.getElementById('serv-search').addEventListener('input', function () {
            searchVal = this.value.toLowerCase().trim();
            curPage   = 0;
            renderPage();
        });

        document.getElementById('serv-pag-prev').addEventListener('click', function () {
            if (curPage > 0) { curPage--; renderPage(); }
        });

        document.getElementById('serv-pag-next').addEventListener('click', function () {
            if ((curPage + 1) * PAGE_SIZE < getFiltered().length) { curPage++; renderPage(); }
        });

        document.getElementById('serv-selall-chk').addEventListener('change', function () {
            var filtered = getFiltered();
            var start    = curPage * PAGE_SIZE;
            var end      = Math.min(start + PAGE_SIZE, filtered.length);
            var checked  = this.checked;
            for (var i = start; i < end; i++) {
                var chk = filtered[i].querySelector('.serv-chk');
                chk.checked = checked;
                toggleRow(filtered[i], checked);
            }
            updateBtn();
        });

        document.getElementById('serv-tbody').addEventListener('change', function (e) {
            if (e.target.classList.contains('serv-chk')) {
                toggleRow(e.target.closest('.serv-row'), e.target.checked);
                updateSelAll();
                updateBtn();
            }
        });
    }());
});
</script>

<?php require_once 'footer.php'; ?>
