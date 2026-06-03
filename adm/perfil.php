<?php
require_once 'header.php';

$st = $pdo->prepare("SELECT * FROM ct_usuario WHERE id = :id");
$st->execute([':id' => $_SESSION['id']]);
$usuario = $st->fetch(PDO::FETCH_ASSOC);

function pEsc($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$msg     = '';
$msgType = '';

if (isset($_POST['updatepassword'])) {
    $senhaAtual   = $_POST['senha_atual']   ?? '';
    $novaSenha    = $_POST['nova_senha']    ?? '';
    $confirmaSenha = $_POST['confirma_senha'] ?? '';

    if (md5($senhaAtual) !== ($usuario['password'] ?? '')) {
        $msg = 'Senha atual incorreta.';
        $msgType = 'danger';
    } elseif (strlen($novaSenha) < 6) {
        $msg = 'A nova senha deve ter pelo menos 6 caracteres.';
        $msgType = 'danger';
    } elseif ($novaSenha !== $confirmaSenha) {
        $msg = 'A nova senha e a confirmação não são iguais.';
        $msgType = 'danger';
    } else {
        $pdo->prepare("UPDATE ct_usuario SET password = :pass WHERE id = :id")
            ->execute([':pass' => md5($novaSenha), ':id' => $_SESSION['id']]);
        $msg = 'Senha alterada com sucesso!';
        $msgType = 'success';
    }
}

$nomeCompleto = trim(($usuario['firstname'] ?? '') . ' ' . ($usuario['lastname'] ?? ''));
$inicial = mb_strtoupper(mb_substr($nomeCompleto ?: ($usuario['username'] ?? 'U'), 0, 1, 'UTF-8'), 'UTF-8');
?>
<style>
:root { --navy: #1e4770; --navy-lt: #2a5f96; }
.perfil-wrapper { padding: 24px 20px 80px;  margin: 0 auto; }
.bc-bar { padding: 0 0 20px; font-size: 13px; color: #6c757d; }
.bc-bar a { color: var(--navy); font-weight: 600; text-decoration: none; }
.bc-bar a:hover { text-decoration: underline; }
.bc-bar .sep { margin: 0 6px; color: #ccc; }

.perfil-grid { display: grid; grid-template-columns: 240px 1fr; gap: 20px; align-items: start; }
@media (max-width: 680px) { .perfil-grid { grid-template-columns: 1fr; } }

/* ── Card lateral ── */
.card-avatar { background: #fff; border-radius: 16px; box-shadow: 0 2px 14px rgba(0,0,0,.07); overflow: hidden; text-align: center; }
.card-avatar-banner { background: linear-gradient(135deg, var(--navy) 0%, var(--navy-lt) 100%); height: 70px; }
.avatar-circle { width: 80px; height: 80px; border-radius: 50%; background: rgba(255,255,255,0.2); border: 4px solid #fff; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 800; color: #fff; margin: -40px auto 0; position: relative; z-index: 1; background: var(--navy); }
.card-avatar-body { padding: 14px 16px 22px; }
.avatar-nome { font-size: 15px; font-weight: 700; color: #1e293b; margin: 10px 0 2px; }
.avatar-user { font-size: 12px; color: #6c757d; }
.avatar-email { font-size: 12px; color: #6c757d; margin-top: 4px; word-break: break-all; }
.avatar-divider { height: 1px; background: #f0f0f0; margin: 14px 0; }
.avatar-meta { font-size: 11px; color: #9ca3af; }
.avatar-meta strong { color: #374151; }

/* ── Card principal ── */
.card-main { background: #fff; border-radius: 16px; box-shadow: 0 2px 14px rgba(0,0,0,.07); }
.card-main-header { padding: 18px 24px 14px; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; gap: 10px; }
.card-main-title { font-size: 15px; font-weight: 700; color: var(--navy); }
.card-main-body { padding: 20px 24px; }

.info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px 20px; }
@media (max-width: 500px) { .info-grid { grid-template-columns: 1fr; } }
.info-field label, .info-label { font-size: 10px; font-weight: 700; color: #6c757d; text-transform: uppercase; letter-spacing: .05em; display: block; margin-bottom: 5px; }
.info-field .info-value { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 9px 12px; font-size: 13px; color: #374151; }
.info-field.full { grid-column: 1 / -1; }

.section-title { font-size: 13px; font-weight: 700; color: var(--navy); display: flex; align-items: center; gap: 7px; margin: 24px 0 14px; padding-top: 20px; border-top: 1px solid #f0f0f0; }

.pw-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px; }
@media (max-width: 600px) { .pw-grid { grid-template-columns: 1fr; } }
.pw-field label { font-size: 10px; font-weight: 700; color: #6c757d; text-transform: uppercase; letter-spacing: .05em; display: block; margin-bottom: 5px; }
.pw-field input { width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 9px 12px; font-size: 13px; color: #374151; outline: none; transition: border .2s; }
.pw-field input:focus { border-color: var(--navy); box-shadow: 0 0 0 3px rgba(30,71,112,.1); }

.btn-save { background: var(--navy); color: #fff; border: none; border-radius: 8px; padding: 10px 28px; font-size: 13px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 7px; transition: background .2s; }
.btn-save:hover { background: var(--navy-lt); }

.pw-strength { height: 4px; border-radius: 2px; margin-top: 5px; transition: all .3s; }
</style>

<div class="perfil-wrapper">

    <div class="bc-bar">
        <a href="home"><i class="fas fa-home"></i> Home</a>
        <span class="sep">/</span>
        <span>Meu Perfil</span>
    </div>

    <?php if ($msg): ?>
    <div class="alert alert-<?= pEsc($msgType) ?> alert-dismissible fade show" role="alert" style="border-radius:10px;margin-bottom:18px;">
        <i class="fas fa-<?= $msgType === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
        <?= pEsc($msg) ?>
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
    <?php endif; ?>

    <div class="perfil-grid">

        <!-- Sidebar -->
        <div class="card-avatar">
            <div class="card-avatar-banner"></div>
            <div class="card-avatar-body">
                <div class="avatar-circle"><?= pEsc($inicial) ?></div>
                <div class="avatar-nome"><?= pEsc($nomeCompleto ?: ($usuario['username'] ?? '')) ?></div>
                <div class="avatar-user">@<?= pEsc($usuario['username'] ?? '') ?></div>
                <div class="avatar-email"><?= pEsc($_SESSION['email'] ?? '') ?></div>
                <div class="avatar-divider"></div>
                <div class="avatar-meta">
                    <strong>Empresa</strong><br>CASSI TURISMO
                </div>
                <div class="avatar-divider"></div>
                <div class="avatar-meta">
                    <strong>Sessão iniciada em</strong><br>
                    <?= date('d/m/Y H:i') ?>
                </div>
            </div>
        </div>

        <!-- Main -->
        <div class="card-main">
            <div class="card-main-header">
                <i class="fas fa-user-circle" style="color:var(--navy);font-size:1.1rem;"></i>
                <span class="card-main-title">Informações da Conta</span>
            </div>
            <div class="card-main-body">
                <form method="post" action="">

                    <div class="info-grid">
                        <div class="info-field">
                            <span class="info-label">Empresa</span>
                            <div class="info-value">CASSI TURISMO</div>
                        </div>
                        <div class="info-field">
                            <span class="info-label">Usuário</span>
                            <div class="info-value"><?= pEsc($usuario['username'] ?? '') ?></div>
                        </div>
                        <div class="info-field">
                            <span class="info-label">Nome</span>
                            <div class="info-value"><?= pEsc($usuario['firstname'] ?? '') ?></div>
                        </div>
                        <div class="info-field">
                            <span class="info-label">Sobrenome</span>
                            <div class="info-value"><?= pEsc($usuario['lastname'] ?? '') ?></div>
                        </div>
                        <div class="info-field full">
                            <span class="info-label">E-mail</span>
                            <div class="info-value"><?= pEsc($_SESSION['email'] ?? '') ?></div>
                        </div>
                    </div>

                    <div class="section-title">
                        <i class="fas fa-lock"></i> Alterar Senha
                    </div>

                    <div class="pw-grid">
                        <div class="pw-field">
                            <label for="senha_atual">Senha Atual <span style="color:#dc3545">*</span></label>
                            <input type="password" id="senha_atual" name="senha_atual" required
                                   placeholder="Senha atual" autocomplete="current-password">
                        </div>
                        <div class="pw-field">
                            <label for="nova_senha">Nova Senha <span style="color:#dc3545">*</span></label>
                            <input type="password" id="nova_senha" name="nova_senha" required
                                   placeholder="Mínimo 6 caracteres" autocomplete="new-password"
                                   oninput="pwStrength(this.value)">
                            <div class="pw-strength" id="pw-strength-bar"></div>
                        </div>
                        <div class="pw-field">
                            <label for="confirma_senha">Confirmar Nova Senha <span style="color:#dc3545">*</span></label>
                            <input type="password" id="confirma_senha" name="confirma_senha" required
                                   placeholder="Repita a nova senha" autocomplete="new-password">
                        </div>
                    </div>

                    <div style="margin-top:20px;">
                        <button type="submit" name="updatepassword" class="btn-save">
                            <i class="fas fa-save"></i> Salvar Nova Senha
                        </button>
                    </div>

                </form>
            </div>
        </div>

    </div>
</div>

<script>
function pwStrength(val) {
    var bar = document.getElementById('pw-strength-bar');
    if (!bar) return;
    var score = 0;
    if (val.length >= 6)  score++;
    if (val.length >= 10) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;
    var colors = ['#dc3545','#fd7e14','#ffc107','#20c997','#198754'];
    var widths = ['20%','40%','60%','80%','100%'];
    bar.style.background = val.length ? (colors[score - 1] || colors[0]) : 'transparent';
    bar.style.width      = val.length ? (widths[score - 1] || widths[0]) : '0';
}
</script>

<?php require_once 'footer.php'; ?>
