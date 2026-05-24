<?php
$erro = '';

if (isset($_POST['acessar'])) {
    require_once('config.php');
    try {
        $autenticar = $pdo->prepare(
            "SELECT * FROM ct_usuario WHERE username = :u AND password = :p"
        );
        $autenticar->execute([
            ':u' => $_POST['username'],
            ':p' => md5($_POST['pass']),
        ]);
        $dados = $autenticar->fetch(PDO::FETCH_ASSOC);

        if ($dados && $dados['username'] === $_POST['username'] && $dados['bloqueado'] == 0) {
            $_SESSION['idresponsavel'] = $dados['id'];
            $_SESSION['id']            = $dados['id'];
            $_SESSION['nome']          = $dados['firstname'] . ' ' . $dados['lastname'];
            $_SESSION['email']         = $dados['email'];
            $_SESSION['img']           = $dados['img'];
            $_SESSION['tempo']         = date('i');

            $pdo->prepare("UPDATE ct_usuario SET lastaccess = ? WHERE id = ?")
                ->execute([date('Y-m-d H:i:s'), $dados['id']]);

            $dest = match((int)$dados['idpermission']) {
                5  => 'mapservice/index',
                2  => 'adm/home',
                default => 'adm/index',
            };

            $sessionKey = match((int)$dados['idpermission']) {
                1  => 'idoperador',
                2  => 'idgerente',
                3  => 'idfaturador',
                4  => 'idreservamanager',
                5  => 'idmapservice',
                6  => 'idreservaplus',
                7  => 'idbaixa',
                10 => 'idcaixa',
                11 => 'idpagarreserva',
                12 => 'idfinanceiro2',
                13 => 'folhaderosto',
                14 => 'comissao',
                15 => 'comissaorelatoriofolha',
                default => null,
            };

            if ($sessionKey) {
                $_SESSION[$sessionKey] = $dados['idpermission'];
                header('location: ' . $dest);
                exit;
            }
            $erro = 'invalido';

        } elseif ($dados && $dados['bloqueado'] == 1) {
            $erro = 'bloqueado';
        } else {
            $erro = 'invalido';
        }
    } catch (Exception $e) {
        $erro = 'invalido';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cassi Turismo — Acesso</title>
    <link rel="icon" href="images/icons/favicon.ico">
    <link rel="stylesheet" href="vendor/bootstrap-4.1/bootstrap.min.css">
    <link rel="stylesheet" href="vendor/font-awesome-5/css/fontawesome-all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            height: 100vh;
            overflow: hidden;
            background: #0f1e30;
        }

        /* ── Layout ─────────────────────────────────────────────────────── */
        .lp-wrap {
            display: flex;
            height: 100vh;
        }

        /* ── Painel esquerdo — imagem ────────────────────────────────────── */
        .lp-hero {
            flex: 1 1 60%;
            position: relative;
            overflow: hidden;
        }
        .lp-hero-bg {
            position: absolute; inset: 0;
            background: url('images/fundo.jpg') center/cover no-repeat;
            transform: scale(1.04);
            transition: transform 8s ease;
        }
        .lp-hero-bg.loaded { transform: scale(1); }
        .lp-hero-overlay {
            position: absolute; inset: 0;
            background: linear-gradient(
                135deg,
                rgba(10, 25, 50, 0.78) 0%,
                rgba(20, 50, 90, 0.60) 60%,
                rgba(10, 25, 50, 0.55) 100%
            );
        }
        .lp-hero-content {
            position: relative; z-index: 2;
            height: 100%;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            padding: 40px;
            text-align: center;
        }
        .lp-hero-logo {
            width: 110px;
            margin-bottom: 24px;
            filter: drop-shadow(0 4px 16px rgba(0,0,0,.4));
            animation: fadeUp .8s ease both;
        }
        .lp-hero-title {
            color: #fff;
            font-size: 32px;
            font-weight: 800;
            letter-spacing: .03em;
            line-height: 1.2;
            animation: fadeUp .9s ease .1s both;
        }
        .lp-hero-sub {
            color: rgba(255,255,255,.75);
            font-size: 15px;
            margin-top: 10px;
            font-weight: 400;
            animation: fadeUp 1s ease .2s both;
        }
        .lp-hero-divider {
            width: 48px; height: 3px;
            background: rgba(255,255,255,.35);
            border-radius: 2px;
            margin: 20px auto 0;
            animation: fadeUp 1s ease .3s both;
        }

        /* ── Painel direito — formulário ─────────────────────────────────── */
        .lp-form-panel {
            flex: 0 0 400px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            box-shadow: -8px 0 40px rgba(0,0,0,.18);
            padding: 40px 36px;
            position: relative;
            z-index: 3;
        }
        .lp-form-inner {
            width: 100%;
            max-width: 320px;
            animation: fadeUp .7s ease .1s both;
        }
        .lp-form-logo {
            display: block;
            width: 200px;
            margin: 0 auto 28px;
        }
        .lp-form-heading {
            font-size: 20px;
            font-weight: 800;
            color: #1e4770;
            margin-bottom: 4px;
        }
        .lp-form-desc {
            font-size: 13px;
            color: #94a3b8;
            margin-bottom: 28px;
        }

        /* Alerts */
        .lp-alert {
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 13px;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .lp-alert-danger  { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
        .lp-alert-warning { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }

        /* Inputs */
        .lp-field {
            position: relative;
            margin-bottom: 16px;
        }
        .lp-field-icon {
            position: absolute;
            left: 0; top: 0; bottom: 0;
            width: 42px;
            display: flex; align-items: center; justify-content: center;
            color: #1e4770;
            font-size: 14px;
            pointer-events: none;
        }
        .lp-input {
            width: 100%;
            height: 46px;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            padding: 0 14px 0 42px;
            font-size: 14px;
            color: #1e293b;
            background: #f8fafc;
            transition: border-color .2s, box-shadow .2s, background .2s;
            outline: none;
        }
        .lp-input:focus {
            border-color: #1e4770;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(30,71,112,.12);
        }
        .lp-input::placeholder { color: #b0bec5; }

        /* Botão */
        .lp-btn {
            width: 100%;
            height: 48px;
            background: #1e4770;
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            margin-top: 8px;
            transition: background .2s, transform .1s, box-shadow .2s;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .lp-btn:hover  { background: #2a5f96; box-shadow: 0 4px 14px rgba(30,71,112,.3); }
        .lp-btn:active { transform: scale(.98); }

        /* Footer */
        .lp-footer {
            margin-top: 28px;
            text-align: center;
            font-size: 11px;
            color: #b0bec5;
        }

        /* Animação */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(18px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            body { overflow: auto; }
            .lp-wrap { flex-direction: column; height: auto; min-height: 100vh; }
            .lp-hero {
                flex: 0 0 200px;
                min-height: 200px;
            }
            .lp-hero-logo  { width: 80px; margin-bottom: 14px; }
            .lp-hero-title { font-size: 22px; }
            .lp-hero-sub   { font-size: 13px; }
            .lp-form-panel {
                flex: 1;
                box-shadow: none;
                padding: 32px 24px;
            }
        }
    </style>
</head>
<body>

<div class="lp-wrap">

    <!-- ── Painel esquerdo ──────────────────────────────────────────────── -->
    <div class="lp-hero">
        <div class="lp-hero-bg" id="heroBg"></div>
        <div class="lp-hero-overlay"></div>
        <div class="lp-hero-content">
            <img src="images/cassivetorizadabranca.png" class="lp-hero-logo" alt="Cassi">
            <h1 class="lp-hero-title">Grupo Cassi</h1>
            <p class="lp-hero-sub">Turismo &amp; Experiências Inesquecíveis</p>
            <div class="lp-hero-divider"></div>
        </div>
    </div>

    <!-- ── Painel direito ───────────────────────────────────────────────── -->
    <div class="lp-form-panel">
        <div class="lp-form-inner">

            <img src="images/logo.png" class="lp-form-logo" alt="Cassi Turismo">
            <h2 class="lp-form-heading">Bem-vindo de volta</h2>
            <p class="lp-form-desc">Acesse sua conta para continuar</p>

            <?php if ($erro === 'bloqueado'): ?>
            <div class="lp-alert lp-alert-warning">
                <i class="fas fa-ban"></i>
                Usuário bloqueado. Entre em contato com o Administrador.
            </div>
            <?php elseif ($erro === 'invalido'): ?>
            <div class="lp-alert lp-alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                Usuário ou senha incorretos. Tente novamente.
            </div>
            <?php endif ?>

            <form method="post" action="" autocomplete="off">
                <div class="lp-field">
                    <span class="lp-field-icon"><i class="fas fa-user"></i></span>
                    <input class="lp-input" type="text" name="username"
                           placeholder="Usuário" required
                           readonly onfocus="this.removeAttribute('readonly')">
                </div>

                <div class="lp-field">
                    <span class="lp-field-icon"><i class="fas fa-lock"></i></span>
                    <input class="lp-input" type="password" name="pass"
                           placeholder="Senha" required
                           readonly onfocus="this.removeAttribute('readonly')">
                </div>

                <button type="submit" name="acessar" class="lp-btn">
                    <i class="fas fa-sign-in-alt"></i> Entrar
                </button>
            </form>

            <p class="lp-footer">&copy; <?= date('Y') ?> Cassi Turismo &mdash; Todos os direitos reservados</p>
        </div>
    </div>

</div>

<script>
    document.getElementById('heroBg').classList.add('loaded');
</script>
</body>
</html>
