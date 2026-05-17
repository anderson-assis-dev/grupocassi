<?php
require_once('header.php');
require_once __DIR__ . '/includes/ref_cache.php';

$todosClientes     = refClientes($pdo);
$todosUsuarios     = refUsuarios($pdo);
$todosServicos     = refServicosOrdem($pdo);
$listaEmpresas     = refEmpresasTodas($pdo);
$listaStatusInvoice= refStatusInvoice($pdo);
?>
<link href="../css/reserva-ui.css" rel="stylesheet" media="all">
<style>
    .conf-page { background: #f4f7fb; min-height: calc(100vh - 70px); padding-bottom: 40px; }
    .conf-page .containerrrr { width: 94%; max-width: 1100px; margin: 0 auto; }
    .conf-page .au-breadcrumb2 { background: transparent; padding: 28px 0 14px; }
    .conf-page .conf-card { border: 0; border-radius: 20px; background: #fff; box-shadow: 0 18px 45px rgba(15,23,42,.08); overflow: hidden; }
    .conf-page .conf-heading { background: linear-gradient(135deg,#1e4770,#256aa0); color: #fff; padding: 24px 32px; }
    .conf-page .conf-heading h3 { color: #fff; font-size: 22px; font-weight: 700; margin: 0; }
    .conf-page .conf-heading small { color: rgba(255,255,255,.8); font-size: 13px; }
    .conf-page .nav-tabs { border: 0; display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 26px; padding: 20px 24px 0; }
    .conf-page .nav-tabs .nav-link { border: 1px solid #e5e7eb; border-radius: 999px; color: #25633f; font-weight: 600; padding: 9px 18px; background: #f8fafc; transition: all .2s; }
    .conf-page .nav-tabs .nav-link.active,
    .conf-page .nav-tabs .nav-link:hover { background: #1e4770; color: #fff; border-color: #1e4770; }
    .conf-page .tab-content { padding: 0 24px 28px; }
    .conf-page label { color: #374151; font-size: 13px; font-weight: 700; margin-bottom: 6px; display: block; }
    .conf-page .form-control, .conf-page select.form-control {
        border: 1px solid #dce3ec; border-radius: 10px; min-height: 42px;
        box-shadow: none; transition: border-color .2s;
    }
    .conf-page .form-control:focus { border-color: #1e88d1; box-shadow: 0 0 0 3px rgba(30,136,209,.14); }
    .conf-page .btn-gerar { background: #1e4770; color: #fff; border: 0; border-radius: 10px;
        font-weight: 700; padding: 11px 28px; min-height: 44px; cursor: pointer; }
    .conf-page .btn-gerar:hover { background: #256aa0; }
    .conf-page .form-row-gap { display: flex; flex-wrap: wrap; gap: 16px; margin-bottom: 18px; }
    .conf-page .form-row-gap .field { flex: 1; min-width: 180px; }
    .conf-page .field-full { width: 100%; margin-bottom: 18px; }
    .conf-page select[multiple] { min-height: 110px; }
    .conf-access-denied { padding: 48px 24px; text-align: center; color: #64748b; }
    .conf-access-denied svg { width: 48px; height: 48px; margin-bottom: 12px; stroke: #94a3b8; fill: none; stroke-width: 1.5; }
</style>

<div class="page-content--bgf7 conf-page">
    <section class="au-breadcrumb2">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <ul class="list-unstyled list-inline au-breadcrumb__list" style="margin:0;padding:0">
                        <li class="list-inline-item active"><a href="./index.php">Home</a></li>
                        <li class="list-inline-item seprate"><span>/</span></li>
                        <li class="list-inline-item">Financeiro: Relatório Conferência</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <div class="containerrrr">
        <?php
        $isFinanceiro = !empty($_SESSION['idfinanceiro2'])
            || in_array($_SESSION['id'], [55,31,57,283,274,275,276,277,279,280,281,46,282,284,218])
            || !empty($_SESSION['idoperador']);
        $isOperadorOuAdmin = $_SESSION['id'] == 40 || $_SESSION['id'] == 265 || $_SESSION['id'] == 211
            || $_SESSION['id'] == 35  || $_SESSION['id'] == 57 || $_SESSION['id'] == 283
            || in_array($_SESSION['id'], [274,275,276,277,279,280,281,46,282,284,218])
            || !empty($_SESSION['idoperador']);
        ?>

        <?php if ($isFinanceiro): ?>
            <div class="conf-card">
                <div class="conf-heading">
                    <h3>Relatório de Conferência</h3>
                    <small>Geração de relatórios financeiros por período</small>
                </div>

                <?php if ($isOperadorOuAdmin): ?>
                    <!-- FORM: por pagamento (operadores/admin) -->
                    <div style="padding:24px">
                        <p style="color:#64748b;font-size:13px;margin-bottom:20px">Relatório por <strong>Data de Pagamento</strong> — filtrado pelo seu usuário</p>
                        <form action="./relatorio/pdf-relatorio-conferencia-por-pagamento" target="_blank" method="post">
                            <input type="hidden" name="responsavel" value="<?= (int)$_SESSION['id'] ?>">
                            <input type="hidden" name="tipo" value="1">
                            <div class="form-row-gap">
                                <div class="field">
                                    <label>Data do Pagamento Inicial</label>
                                    <input required type="date" name="inicio" class="form-control">
                                </div>
                                <div class="field">
                                    <label>Data do Pagamento Final</label>
                                    <input required type="date" name="fim" class="form-control">
                                </div>
                                <div class="field">
                                    <label>Agência / Revendedor</label>
                                    <select class="form-control" name="cliente">
                                        <option value="0">TODOS</option>
                                        <?php foreach ($todosClientes as $c): ?>
                                            <option value="<?= $c->id ?>"><?= htmlspecialchars($c->fullname) ?></option>
                                        <?php endforeach ?>
                                    </select>
                                </div>
                                <div class="field">
                                    <label>Empresa</label>
                                    <select name="idempresa" class="form-control">
                                        <option value="0">TODAS</option>
                                        <?php foreach ($listaEmpresas as $e): ?>
                                            <option value="<?= $e->id ?>"><?= htmlspecialchars($e->fullname) ?></option>
                                        <?php endforeach ?>
                                    </select>
                                </div>
                            </div>
                            <button type="submit" class="btn-gerar">Gerar Relatório</button>
                        </form>
                    </div>

                <?php else: ?>
                    <!-- FORM: por abertura (financeiro restrito) -->
                    <div style="padding:24px">
                        <form action="./relatorio/pdf-relatorio-conferencia-abertura" target="_blank" method="post">
                            <div class="form-row-gap">
                                <div class="field">
                                    <label>Serviço</label>
                                    <select required class="form-control" name="servico[]" multiple>
                                        <option value="0" selected>Todos os Serviços</option>
                                        <?php foreach ($todosServicos as $s): ?>
                                            <option value="<?= $s->id ?>"><?= htmlspecialchars($s->fullname) ?></option>
                                        <?php endforeach ?>
                                    </select>
                                </div>
                                <div class="field">
                                    <label>Agência / Cliente</label>
                                    <select required class="form-control" name="cliente">
                                        <?php if ($_SESSION['id'] == 38): ?>
                                            <?php foreach ($todosClientes as $c): if ($c->id == 169): ?>
                                                <option selected value="<?= $c->id ?>"><?= htmlspecialchars($c->fullname) ?></option>
                                            <?php endif; endforeach ?>
                                        <?php elseif ($_SESSION['id'] == 31): ?>
                                            <?php foreach ($todosClientes as $c): if ($c->id == 35): ?>
                                                <option selected value="<?= $c->id ?>"><?= htmlspecialchars($c->fullname) ?></option>
                                            <?php endif; endforeach ?>
                                        <?php else: ?>
                                            <option selected value="0">TODOS</option>
                                            <?php foreach ($todosClientes as $c): ?>
                                                <option value="<?= $c->id ?>"><?= htmlspecialchars($c->fullname) ?></option>
                                            <?php endforeach ?>
                                        <?php endif ?>
                                    </select>
                                </div>
                                <div class="field">
                                    <label>Responsável</label>
                                    <select required class="form-control" name="responsavel">
                                        <?php if ($_SESSION['id'] == 38): ?>
                                            <option selected value="0">TODOS</option>
                                            <?php foreach ($todosUsuarios as $u): ?>
                                                <option value="<?= $u->id ?>"><?= htmlspecialchars(strtoupper($u->firstname . ' ' . $u->lastname)) ?></option>
                                            <?php endforeach ?>
                                        <?php elseif (!empty($_SESSION['idoperador']) || !empty($_SESSION['idfinanceiro2']) || $_SESSION['id'] == 55): ?>
                                            <option selected value="<?= (int)$_SESSION['id'] ?>"><?= htmlspecialchars($_SESSION['nome'] ?? '') ?></option>
                                        <?php else: ?>
                                            <option selected value="0">TODOS</option>
                                            <?php foreach ($todosUsuarios as $u): ?>
                                                <option value="<?= $u->id ?>"><?= htmlspecialchars(strtoupper($u->firstname . ' ' . $u->lastname)) ?></option>
                                            <?php endforeach ?>
                                        <?php endif ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-row-gap">
                                <div class="field">
                                    <label>Abertura Inicial</label>
                                    <input required type="date" name="abertura" class="form-control">
                                </div>
                                <div class="field">
                                    <label>Abertura Final</label>
                                    <input required type="date" name="aberturafinal" class="form-control">
                                </div>
                                <div class="field">
                                    <label>Tipo de Relatório</label>
                                    <select class="form-control" name="tiporelatorio" required>
                                        <option value="0" selected>COMPLETO</option>
                                    </select>
                                </div>
                            </div>
                            <button type="submit" class="btn-gerar">Gerar Relatório</button>
                        </form>
                    </div>
                <?php endif ?>
            </div>

        <?php else: ?>
            <!-- ACESSO COMPLETO: 3 tipos de relatório em abas -->
            <div class="conf-card">
                <div class="conf-heading">
                    <h3>Relatório de Conferência</h3>
                    <small>Selecione o tipo de relatório e configure os filtros</small>
                </div>

                <ul class="nav nav-tabs" id="confTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="tab-embarque" data-toggle="tab" href="#embarque" role="tab">Por Embarque</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-abertura" data-toggle="tab" href="#abertura-tab" role="tab">Por Abertura</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-pagamento" data-toggle="tab" href="#pagamento-tab" role="tab">Por Pagamento</a>
                    </li>
                </ul>

                <div class="tab-content">
                    <!-- ABA 1: Por Data de Embarque -->
                    <div class="tab-pane fade show active" id="embarque" role="tabpanel">
                        <form action="./relatorio/pdf-relatorio-conferencia.php" target="_blank" method="post">
                            <div class="form-row-gap">
                                <div class="field">
                                    <label>Período Inicial</label>
                                    <input required type="date" name="periodoinicial" class="form-control">
                                </div>
                                <div class="field">
                                    <label>Período Final</label>
                                    <input required type="date" name="periodofinal" class="form-control">
                                </div>
                                <div class="field">
                                    <label>Agência</label>
                                    <select class="form-control" name="cliente">
                                        <option selected value="0">TODOS</option>
                                        <?php foreach ($todosClientes as $c): ?>
                                            <option value="<?= $c->id ?>"><?= htmlspecialchars($c->fullname) ?></option>
                                        <?php endforeach ?>
                                    </select>
                                </div>
                                <div class="field">
                                    <label>Empresa</label>
                                    <select name="idempresa" class="form-control" required>
                                        <option value="" selected disabled>Selecione</option>
                                        <?php foreach ($listaEmpresas as $e): ?>
                                            <option value="<?= $e->id ?>"><?= htmlspecialchars($e->fullname) ?></option>
                                        <?php endforeach ?>
                                    </select>
                                </div>
                                <div class="field">
                                    <label>Responsável</label>
                                    <select required class="form-control" name="responsavel">
                                        <option selected value="0">TODOS</option>
                                        <?php foreach ($todosUsuarios as $u): ?>
                                            <option value="<?= $u->id ?>"><?= htmlspecialchars(strtoupper($u->firstname . ' ' . $u->lastname)) ?></option>
                                        <?php endforeach ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-row-gap">
                                <div class="field">
                                    <label>Nome do Pax</label>
                                    <input type="text" name="nomepax" class="form-control" placeholder="Buscar por nome...">
                                </div>
                                <div class="field">
                                    <label>Tipo de Relatório</label>
                                    <select class="form-control" name="tiporelatorio">
                                        <option value="0">Descritivo</option>
                                        <option value="1">Resumido</option>
                                    </select>
                                </div>
                                <div class="field">
                                    <label>Status</label>
                                    <select class="form-control" name="status[]" multiple>
                                        <option selected value="0">TODOS</option>
                                        <?php foreach ($listaStatusInvoice as $st): ?>
                                            <option value="<?= $st->id ?>"><?= htmlspecialchars($st->nameinvoice) ?></option>
                                        <?php endforeach ?>
                                    </select>
                                </div>
                            </div>
                            <button type="submit" class="btn-gerar">Gerar Relatório</button>
                        </form>
                    </div>

                    <!-- ABA 2: Por Data de Abertura -->
                    <div class="tab-pane fade" id="abertura-tab" role="tabpanel">
                        <form action="./relatorio/pdf-relatorio-conferencia-abertura" target="_blank" method="post">
                            <div class="form-row-gap">
                                <div class="field">
                                    <label>Serviço</label>
                                    <select required class="form-control" name="servico[]" multiple>
                                        <option value="0" selected>Todos os Serviços</option>
                                        <?php foreach ($todosServicos as $s): ?>
                                            <option value="<?= $s->id ?>"><?= htmlspecialchars($s->fullname) ?></option>
                                        <?php endforeach ?>
                                    </select>
                                </div>
                                <div class="field">
                                    <label>Agência / Revendedor</label>
                                    <select required class="form-control" name="cliente">
                                        <option selected value="0">TODOS</option>
                                        <?php foreach ($todosClientes as $c): ?>
                                            <option value="<?= $c->id ?>"><?= htmlspecialchars($c->fullname) ?></option>
                                        <?php endforeach ?>
                                    </select>
                                </div>
                                <div class="field">
                                    <label>Empresa</label>
                                    <select name="idempresa" class="form-control" required>
                                        <option value="" selected disabled>Selecione</option>
                                        <?php foreach ($listaEmpresas as $e): ?>
                                            <option value="<?= $e->id ?>"><?= htmlspecialchars($e->fullname) ?></option>
                                        <?php endforeach ?>
                                    </select>
                                </div>
                                <div class="field">
                                    <label>Responsável</label>
                                    <select required class="form-control" name="responsavel">
                                        <option selected value="0">TODOS</option>
                                        <?php foreach ($todosUsuarios as $u): ?>
                                            <option value="<?= $u->id ?>"><?= htmlspecialchars(strtoupper($u->firstname . ' ' . $u->lastname)) ?></option>
                                        <?php endforeach ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-row-gap">
                                <div class="field">
                                    <label>Abertura Inicial</label>
                                    <input required type="date" name="abertura" class="form-control">
                                </div>
                                <div class="field">
                                    <label>Abertura Final</label>
                                    <input required type="date" name="aberturafinal" class="form-control">
                                </div>
                                <div class="field">
                                    <label>Tipo de Relatório</label>
                                    <select class="form-control" name="tiporelatorio" required>
                                        <option value="0" selected>COMPLETO</option>
                                        <option value="1">RESUMIDO</option>
                                    </select>
                                </div>
                            </div>
                            <button type="submit" class="btn-gerar">Gerar Relatório</button>
                        </form>
                    </div>

                    <!-- ABA 3: Por Data de Pagamento -->
                    <div class="tab-pane fade" id="pagamento-tab" role="tabpanel">
                        <form action="./relatorio/pdf-relatorio-conferencia-por-pagamento" target="_blank" method="post">
                            <div class="form-row-gap">
                                <div class="field">
                                    <label>Responsável</label>
                                    <select required class="form-control" name="responsavel">
                                        <option selected value="0">TODOS</option>
                                        <?php foreach ($todosUsuarios as $u): ?>
                                            <option value="<?= $u->id ?>"><?= htmlspecialchars(strtoupper($u->firstname . ' ' . $u->lastname)) ?></option>
                                        <?php endforeach ?>
                                    </select>
                                </div>
                                <div class="field">
                                    <label>Agência / Revendedor</label>
                                    <select required class="form-control" name="cliente">
                                        <option selected value="0">TODOS</option>
                                        <?php foreach ($todosClientes as $c): ?>
                                            <option value="<?= $c->id ?>"><?= htmlspecialchars($c->fullname) ?></option>
                                        <?php endforeach ?>
                                    </select>
                                </div>
                                <div class="field">
                                    <label>Empresa</label>
                                    <select name="idempresa" class="form-control" required>
                                        <option value="" selected disabled>Selecione</option>
                                        <?php foreach ($listaEmpresas as $e): ?>
                                            <option value="<?= $e->id ?>"><?= htmlspecialchars($e->fullname) ?></option>
                                        <?php endforeach ?>
                                    </select>
                                </div>
                                <div class="field">
                                    <label>Tipo</label>
                                    <select required class="form-control" name="tipo">
                                        <option selected value="1">Descritivo</option>
                                        <option value="2">Resumido</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-row-gap">
                                <div class="field">
                                    <label>Data do Pagamento Inicial</label>
                                    <input required type="date" name="inicio" class="form-control">
                                </div>
                                <div class="field">
                                    <label>Data do Pagamento Final</label>
                                    <input required type="date" name="fim" class="form-control">
                                </div>
                            </div>
                            <button type="submit" class="btn-gerar">Gerar Relatório</button>
                        </form>
                    </div>
                </div><!-- /tab-content -->
            </div><!-- /conf-card -->
        <?php endif ?>
    </div><!-- /containerrrr -->
</div>
<?php require_once('footer.php'); ?>
