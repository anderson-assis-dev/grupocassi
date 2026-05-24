<?php
require_once 'header.php';
require_once __DIR__ . '/includes/ref_cache.php';
require_once __DIR__ . '/includes/flash.php';
$pdo->exec("set names utf8");
$todosCliente = refFornecedores($pdo);
$empresas     = refEmpresasTodas($pdo);
$contas       = refContaCorrente($pdo);

$hoje = date('Y-m-d');
$primeiroDiaMes = date('Y-m-01');
?>
<style>
:root { --navy: #1e4770; --navy-lt: #2a5f96; }
.map-wrapper { padding: 20px 20px 60px; }
.bc-bar { padding: 0 0 16px; font-size: 13px; color: #6c757d; }
.bc-bar a { color: var(--navy); font-weight: 600; text-decoration: none; }
.bc-bar a:hover { text-decoration: underline; }
.bc-bar .sep { margin: 0 6px; color: #ccc; }
.filter-card { background: #fff; border-radius: 12px; box-shadow: 0 2px 14px rgba(0,0,0,.07); padding: 26px 28px 22px; }
.fc-title { font-size: 12px; font-weight: 700; color: var(--navy); text-transform: uppercase; letter-spacing: .06em; margin-bottom: 18px; display: flex; align-items: center; gap: 7px; }
.filter-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px 20px; }
.filter-grid label { font-size: 11px; font-weight: 700; color: #6c757d; text-transform: uppercase; letter-spacing: .05em; display: block; margin-bottom: 5px; }
.filter-grid .form-control { border: 1.5px solid #dee2e6; border-radius: 8px; font-size: 13px; height: 38px; transition: border-color .2s; }
.filter-grid .form-control:focus { border-color: var(--navy); box-shadow: 0 0 0 3px rgba(30,71,112,.12); outline: none; }
.btn-gerar { background: var(--navy); color: #fff; border: none; border-radius: 8px; padding: 11px 28px; font-size: 14px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: background .2s; margin-top: 20px; }
.btn-gerar:hover { background: var(--navy-lt); }
.btn-email { background: #1a9e5c; color: #fff; border: none; border-radius: 8px; padding: 11px 28px; font-size: 14px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: background .2s; margin-top: 20px; }
.btn-email:hover { background: #157a47; }
.btn-row { display: flex; flex-wrap: wrap; gap: 12px; align-items: center; }
.info-box { background: #f0f6ff; border-left: 4px solid var(--navy); border-radius: 0 8px 8px 0; padding: 12px 16px; margin-top: 20px; font-size: 13px; color: #334155; }
@media (max-width: 767px) { .filter-grid { grid-template-columns: 1fr; } .map-wrapper { padding: 14px 12px 40px; } }
</style>

<div class="page-content--bgf7">
<div class="map-wrapper">

    <div class="bc-bar">
        <a href="index"><i class="fas fa-home"></i> Home</a>
        <span class="sep">/</span>
        <span>Financeiro: Relatório de Contas</span>
    </div>

    <?php $flash = getFlash(); if ($flash): ?>
    <div class="alert alert-<?= htmlspecialchars($flash['type'], ENT_QUOTES, 'UTF-8') ?> alert-dismissible fade show" role="alert">
        <?= $flash['msg'] ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
    </div>
    <?php endif; ?>

    <div class="filter-card">
        <div class="fc-title"><i class="fas fa-file-invoice-dollar"></i> Relatório de Fluxo de Caixa</div>

        <form id="form-relatorio" action="./relatorio/pdf-fluxo-caixa" target="_blank" method="post">
            <div class="filter-grid">
                <div>
                    <label for="vencimentoinicial">Data Inicial</label>
                    <input required type="date" name="vencimentoinicial" id="vencimentoinicial"
                           class="form-control" value="<?= $primeiroDiaMes ?>">
                </div>
                <div>
                    <label for="vencimentofinal">Data Final</label>
                    <input required type="date" name="vencimentofinal" id="vencimentofinal"
                           class="form-control" value="<?= $hoje ?>">
                </div>
                <div>
                    <label for="empresa">Empresa</label>
                    <select class="form-control" name="empresa" id="empresa">
                        <option value="0">Todas</option>
                        <?php foreach ($empresas as $e): ?>
                            <option value="<?= (int)$e->id ?>"><?= htmlspecialchars($e->fullname) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="cliente">Favorecido</label>
                    <select class="form-control" name="cliente" id="cliente">
                        <option value="0">Todos</option>
                        <?php foreach ($todosCliente as $c): ?>
                            <option value="<?= (int)$c->id ?>"><?= htmlspecialchars($c->fullname) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="conta">Conta Corrente</label>
                    <select class="form-control" name="conta" id="conta">
                        <option value="0">Todas</option>
                        <?php foreach ($contas as $cc): ?>
                            <option value="<?= (int)$cc->id ?>"><?= htmlspecialchars($cc->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="tiporelatorio">Tipo de Relatório</label>
                    <select class="form-control" name="tiporelatorio" id="tiporelatorio">
                        <option value="0">Fluxo de Caixa</option>
                        <option value="1">Por Fornecedor</option>
                    </select>
                </div>
            </div>

            <div class="info-box">
                <i class="fas fa-info-circle" style="color:var(--navy);margin-right:6px;"></i>
                <strong>Gerar no Navegador</strong> abre o relatório em nova aba (pode ser lento com muitos registros).
                <strong>Enviar por E-mail</strong> processa em segundo plano e envia o PDF para
                <strong><?= htmlspecialchars($_SESSION['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>.
            </div>

            <div class="btn-row">
                <button type="submit" name="buscar" class="btn-gerar">
                    <i class="fas fa-file-alt"></i> Gerar no Navegador
                </button>
                <button type="button" class="btn-email" onclick="enviarPorEmail()">
                    <i class="fas fa-envelope"></i> Enviar por E-mail
                </button>
            </div>
        </form>
    </div>

</div>
</div>

<script>
function enviarPorEmail() {
    var form = document.getElementById('form-relatorio');
    var orig = form.action;
    var origTarget = form.target;
    form.action = './relatorio/enviar-relatorio-email';
    form.target = '_self';
    form.submit();
    form.action  = orig;
    form.target  = origTarget;
}
</script>

<?php require_once 'footer.php'; ?>
