<?php
require_once '.././config.php';
if( empty( $_SESSION['idgerente'] || $_SESSION['idfaturador'] || $_SESSION['idoperador'] || $_SESSION['comissao']
    || $_SESSION['idreservamanager'] || $_SESSION['idreservaplus'] || $_SESSION['idcaixa'] || $_SESSION['idbaixa']
    || $_SESSION['idpagarreserva'] || $_SESSION['idfinanceiro2'] || $_SESSION['folhaderosto'] || $_SESSION['comissaorelatoriofolha'] ) )
{
    header("location: ../");
}

ob_start();
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistema de Reserva WISM Group">
    <title>Cassi Turismo | Reserva</title>
    <link href="../images/icone.png" rel="icon">
    <link href="../vendor/font-awesome-4.7/css/font-awesome.min.css" rel="stylesheet" media="all">
    <link href="../vendor/font-awesome-5/css/fontawesome-all.min.css" rel="stylesheet" media="all">
    <link href="../vendor/bootstrap-4.1/bootstrap.min.css" rel="stylesheet" media="all">
    <link href="../vendor/data-table/buttons.bootstrap.min.css" rel="stylesheet">
    <link href="../vendor/data-table/buttons.dataTables.min.css" rel="stylesheet">
    <link href="../vendor/data-table/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="../css/theme.css" rel="stylesheet" media="all">
    <style>
        /* ─── TOPBAR ─────────────────────────────────────────────── */
        .cassi-topbar {
            position: fixed;
            top: 0; left: 0; right: 0;
            height: 56px;
            background: #1e4770;
            z-index: 1050;
            box-shadow: 0 2px 10px rgba(0,0,0,0.25);
            display: flex;
            align-items: center;
            padding: 0 16px;
            justify-content: space-between;
        }
        .cassi-topbar-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .cassi-hamburger {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 8px;
            color: #fff;
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.05rem;
            cursor: pointer;
            flex-shrink: 0;
            transition: background .2s;
        }
        .cassi-hamburger:hover { background: rgba(255,255,255,0.2); }

        .cassi-logo {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #fff;
            font-weight: 700;
            font-size: 1rem;
            text-decoration: none;
            white-space: nowrap;
        }
        .cassi-logo i { color: #4fc3f7; font-size: 1.1rem; }
        .cassi-logo:hover { color: #fff; text-decoration: none; }

        .cassi-topbar-right {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .cassi-user-name {
            color: rgba(255,255,255,0.7);
            font-size: 0.8rem;
            max-width: 160px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        @media (max-width: 400px) { .cassi-user-name { display: none; } }

        .cassi-user-btn {
            background: rgba(255,255,255,0.1) !important;
            border: 1px solid rgba(255,255,255,0.2) !important;
            color: #fff !important;
            border-radius: 8px !important;
            padding: 5px 10px !important;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .cassi-user-btn:hover,
        .cassi-user-btn:focus { background: rgba(255,255,255,0.2) !important; }
        .cassi-user-btn .fa-chevron-down { font-size: 0.65rem; }

        /* ─── USER DROPDOWN ──────────────────────────────────────── */
        .cassi-user-dropdown {
            min-width: 210px;
            border: none;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.18);
            padding: 0;
            overflow: hidden;
            margin-top: 6px;
        }
        .cassi-user-dropdown .ud-header {
            background: linear-gradient(135deg, #1e4770 0%, #2d6aa8 100%);
            padding: 14px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .cassi-user-dropdown .ud-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            color: #fff;
            flex-shrink: 0;
            font-weight: 700;
        }
        .cassi-user-dropdown .ud-info { overflow: hidden; }
        .cassi-user-dropdown .ud-name {
            color: #fff;
            font-weight: 600;
            font-size: 0.85rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.2;
        }
        .cassi-user-dropdown .ud-role {
            color: rgba(255,255,255,0.6);
            font-size: 0.72rem;
            margin-top: 1px;
        }
        .cassi-user-dropdown .ud-body {
            padding: 6px 0;
        }
        .cassi-user-dropdown .dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 16px;
            font-size: 0.87rem;
            color: #374151;
            border-radius: 0;
            transition: background .15s;
        }
        .cassi-user-dropdown .dropdown-item i {
            width: 16px;
            text-align: center;
            color: #6b7280;
            font-size: 0.85rem;
        }
        .cassi-user-dropdown .dropdown-item:hover {
            background: #f3f4f6;
            color: #1e4770;
        }
        .cassi-user-dropdown .dropdown-item:hover i { color: #1e4770; }
        .cassi-user-dropdown .ud-divider {
            height: 1px;
            background: #f0f0f0;
            margin: 4px 0;
        }
        .cassi-user-dropdown .ud-cache { color: #6b7280; }
        .cassi-user-dropdown .ud-cache i { color: #9ca3af; }
        .cassi-user-dropdown .ud-cache:hover { background: #f3f4f6; color: #1e4770; }
        .cassi-user-dropdown .ud-cache:hover i { color: #1e4770; }

        .cassi-user-dropdown .ud-sair {
            color: #dc2626;
            margin-bottom: 2px;
        }
        .cassi-user-dropdown .ud-sair i { color: #dc2626; }
        .cassi-user-dropdown .ud-sair:hover { background: #fef2f2; color: #b91c1c; }
        .cassi-user-dropdown .ud-sair:hover i { color: #b91c1c; }

        /* ─── BODY OFFSET para o topbar fixo ─────────────────────── */
        body { padding-top: 56px !important; }

        /* ─── OVERLAY ────────────────────────────────────────────── */
        .cassi-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1040;
        }
        .cassi-overlay.open { display: block; }

        /* ─── SIDEBAR ─────────────────────────────────────────────── */
        .cassi-sidebar {
            position: fixed;
            top: 56px;
            left: -280px;
            width: 268px;
            height: calc(100% - 56px);
            background: #1e4770;
            z-index: 1045;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            transition: left .28s ease;
        }
        .cassi-sidebar.open { left: 0; }

        .csb-section-title {
            color: rgba(255,255,255,0.42);
            font-size: 0.67rem;
            text-transform: uppercase;
            letter-spacing: 1.3px;
            padding: 14px 18px 5px;
        }
        .cassi-sidebar a {
            display: flex;
            align-items: center;
            gap: 10px;
            color: rgba(255,255,255,0.86);
            padding: 10px 18px;
            font-size: 0.88rem;
            text-decoration: none;
            transition: background .18s, color .18s;
        }
        .cassi-sidebar a:hover {
            background: rgba(255,255,255,0.1);
            color: #fff;
            text-decoration: none;
        }
        .cassi-sidebar a i {
            width: 18px;
            text-align: center;
            opacity: .75;
            flex-shrink: 0;
        }
        .csb-divider {
            height: 1px;
            background: rgba(255,255,255,0.09);
            margin: 4px 0;
        }
        .cassi-sidebar .csb-footer {
            padding: 8px 0 24px;
            border-top: 1px solid rgba(255,255,255,0.1);
            margin-top: 8px;
        }
        .cassi-sidebar .csb-footer a { color: rgba(255,100,100,0.85); }
        .cassi-sidebar .csb-footer a:hover { color: #ff6b6b; background: rgba(255,0,0,0.07); }

        .csb-relatorio { color: #4caf50 !important; font-weight: 700; }
        .csb-relatorio i { color: #4caf50 !important; opacity: 1 !important; }
    </style>
</head>

<body style="font-family:'Arial' !important;">
<div class="page-wrapper">

<!-- ═══════════════════ TOPBAR (desktop + mobile) ═══════════════════ -->
<header class="cassi-topbar">
    <div class="cassi-topbar-left">
        <button class="cassi-hamburger" onclick="cassNavOpen()" aria-label="Abrir menu">
            <i class="fas fa-bars"></i>
        </button>
        <a class="cassi-logo" href="./home">
            <i class="fas fa-compass"></i>
            <span>CASSI TURISMO</span>
        </a>
    </div>
    <div class="cassi-topbar-right">
        <span class="cassi-user-name"><?php echo htmlspecialchars($_SESSION['nome']); ?></span>
        <div class="btn-group">
            <button type="button" class="cassi-user-btn btn dropdown-toggle"
                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-user-circle"></i>
                <i class="fas fa-chevron-down"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-right cassi-user-dropdown">
                <div class="ud-header">
                    <div class="ud-avatar">
                        <?php echo mb_strtoupper(mb_substr($_SESSION['nome'], 0, 1, 'UTF-8'), 'UTF-8'); ?>
                    </div>
                    <div class="ud-info">
                        <div class="ud-name"><?php echo htmlspecialchars($_SESSION['nome']); ?></div>
                        <div class="ud-role">Cassi Turismo</div>
                    </div>
                </div>
                <div class="ud-body">
                    <a class="dropdown-item" href="./home">
                        <i class="fas fa-home"></i> Início
                    </a>
                    <a class="dropdown-item" href="./perfil">
                        <i class="fas fa-user"></i> Perfil
                    </a>
                    <div class="ud-divider"></div>
                    <a class="dropdown-item ud-cache" href="./limpar-cache"
                       onclick="return confirm('Limpar o cache de referência?\nOs dados serão recarregados do banco na próxima consulta.')">
                        <i class="fas fa-sync-alt"></i> Limpar Cache
                    </a>
                    <div class="ud-divider"></div>
                    <a class="dropdown-item ud-sair" href="./sair">
                        <i class="fas fa-sign-out-alt"></i> Sair
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>
<!-- ═══════════════════ /TOPBAR ═══════════════════ -->


<!-- OVERLAY -->
<button class="cassi-overlay" id="cassOverlay"
        onclick="cassNavClose()" aria-label="Fechar menu" tabindex="-1"></button>

<!-- ═══════════════════ SIDEBAR ═══════════════════ -->
<nav class="cassi-sidebar" id="cassiSidebar" aria-label="Menu principal">

    <div class="csb-section-title">Navegação</div>
    <a href="./home"><i class="fas fa-home"></i> Início</a>
    <a href="./perfil"><i class="fas fa-user"></i> Perfil</a>
    <div class="csb-divider"></div>

    <?php if( !empty($_SESSION['idgerente']) ): ?>

    <div class="csb-section-title">Caixa</div>
    <a href="./caixa"><i class="fas fa-cash-register"></i> Caixa</a>
    <a href="./nova-conta-corrente"><i class="fas fa-university"></i> Conta Corrente</a>
    <a href="./relatorio-contas"><i class="fas fa-file-alt"></i> Relatório de Contas</a>
    <div class="csb-divider"></div>

    <div class="csb-section-title">Cadastro | Consulta</div>
    <a href="./novo-servico"><i class="fas fa-concierge-bell"></i> Serviços</a>
    <a href="./pesquisa-cliente-fornecedor?tab=favorecido"><i class="fas fa-handshake"></i> Favorecidos</a>
    <div class="csb-divider"></div>

    <div class="csb-section-title">Financeiro</div>
    <a href="./index"><i class="fas fa-chart-line"></i> Analítico</a>
    <a href="./inicio-da-fatura"><i class="fas fa-file-invoice"></i> Cadastrar Fatura</a>
    <a href="./statusvoucher"><i class="fas fa-check-circle"></i> Dar Baixa</a>
    <a href="./cadastrar-diarista"><i class="fas fa-user-clock"></i> Cadastrar Diárista</a>
    <a href="./gerar-multiplos-recibos"><i class="fas fa-money-bill-wave"></i> Múltiplos Pagamentos</a>
    <a href="./informacoes-reserva"><i class="fas fa-hand-holding-usd"></i> Pagamento de Comissão</a>
    <a href="./relatorio-conferencia"><i class="fas fa-file-pdf"></i> Relatórios</a>
    <a href="./relatorio-baixa"><i class="fas fa-file-pdf"></i> Relatório de Baixa</a>
    <a href="./relatorio-inventario"><i class="fas fa-boxes"></i> Inventário</a>
    <a href="./relatorio-comissao"><i class="fas fa-percentage"></i> Relatório de Comissões</a>
    <a href="./mapa-de-servico-de-vouchers-cancelados"><i class="fas fa-ban"></i> Mapa de serviços cancelados</a>
    <div class="csb-divider"></div>

    <?php if( $_SESSION['id'] != 34 ): ?>
    <div class="csb-section-title">Cliente</div>
    <a href="./pesquisa-cliente-fornecedor"><i class="fas fa-search"></i> Clientes / Favorecidos</a>
    <div class="csb-divider"></div>

    <div class="csb-section-title">Revendedor</div>
    <a href="./lista_revendedores_geral"><i class="fas fa-users"></i> Todos personais</a>
    <a href="./lista_clientes_site"><i class="fas fa-globe"></i> Clientes site</a>
    <div class="csb-divider"></div>
    <?php endif; ?>

    <?php endif; // idgerente ?>

    <?php if( !empty($_SESSION['idreservamanager']) || !empty($_SESSION['idfinanceiro2']) || !empty($_SESSION['comissaorelatoriofolha']) || $_SESSION['id'] == 46 || $_SESSION['id'] == 218 ): ?>

    <div class="csb-section-title">Relatórios</div>
    <a href="./relatorio-conferencia"><i class="fas fa-file-pdf"></i> Relatório de Fatura</a>
    <div class="csb-divider"></div>

    <?php if( $_SESSION['id'] == 46 ): ?>
    <a href="./statusvoucher"><i class="fas fa-check-circle"></i> Dar Baixa</a>
    <?php endif; ?>

    <a href="./caixa"><i class="fas fa-receipt"></i> Gerar Recibo</a>

    <div class="csb-section-title">Cadastro</div>
    <a href="./novo-servico"><i class="fas fa-plus"></i> Novo Serviço</a>
    <a href="./pesquisa-cliente-fornecedor?tab=favorecido"><i class="fas fa-plus"></i> Novo Favorecido</a>
    <div class="csb-divider"></div>

    <div class="csb-section-title">Personais</div>
    <a href="./lista_revendedores"><i class="fas fa-clock"></i> Pendente de aprovação</a>
    <a href="./lista_revendedores_aprovados_a_pagar"><i class="fas fa-check"></i> Aprovados a pagar</a>
    <a href="./lista_revendedores_geral"><i class="fas fa-users"></i> Todos personais</a>
    <a href="./novo-usuario"><i class="fas fa-user-plus"></i> Usuários</a>
    <a href="./lista_clientes_site"><i class="fas fa-globe"></i> Clientes site</a>
    <div class="csb-divider"></div>

    <a href="./mapa-de-servico-de-vouchers-cancelados"><i class="fas fa-ban"></i> Mapa cancelados</a>
    <div class="csb-divider"></div>

    <?php endif; ?>

    <?php if( empty($_SESSION['idcaixa']) && empty($_SESSION['idbaixa']) ): ?>

        <?php if( $_SESSION['id'] == 30 || $_SESSION['id'] == 34 || $_SESSION['id'] == 265 ): ?>
        <a href="./caixa"><i class="fas fa-receipt"></i> Gerar Recibo</a>
        <?php endif; ?>

        <?php
        $ids_operadores = [57, 274, 275, 276, 277, 279, 280, 281, 282, 283, 284];
        if( in_array($_SESSION['id'], $ids_operadores) ): ?>
        <a href="./nova-reserva"><i class="fas fa-plus"></i> Nova Reserva</a>
        <a href="./caixa"><i class="fas fa-receipt"></i> Gerar Recibo</a>
        <a href="./relatorio-conferencia" class="csb-relatorio"><i class="fas fa-file-pdf"></i> RELATÓRIOS</a>
        <?php else: ?>

        <div class="csb-section-title">Mapa</div>
        <a href="./map-visualizar-servico"><i class="fas fa-street-view"></i> Mapa de Serviço</a>
        <a href="./mapa-motorista.php"><i class="fas fa-truck"></i> Mapa por Motorista</a>
        <div class="csb-divider"></div>

        <div class="csb-section-title">Reserva</div>
        <a href="./nova-reserva"><i class="fas fa-plus"></i> Nova Reserva</a>
        <a href="./informacoes-reserva"><i class="fas fa-search"></i> Consultar Reserva</a>
        <a href="./pesquisar-reserva"><i class="fas fa-print"></i> Impressão da OS</a>
        <div class="csb-divider"></div>

        <a href="./relatorio-conferencia" class="csb-relatorio"><i class="fas fa-file-pdf"></i> RELATÓRIOS</a>

        <?php if( $_SESSION['id'] == 273 ): ?>
        <div class="csb-divider"></div>
        <div class="csb-section-title">Caixa</div>
        <a href="./caixa"><i class="fas fa-receipt"></i> Gerar Recibo</a>

        <div class="csb-section-title">Financeiro</div>
        <a href="./statusvoucher"><i class="fas fa-check-circle"></i> Dar Baixa</a>
        <a href="./relatorio-baixa"><i class="fas fa-file-pdf"></i> Relatório de Baixa</a>
        <a href="./mapa-de-servico-de-vouchers-cancelados"><i class="fas fa-ban"></i> Mapa cancelados</a>
        <a href="./cadastrar-diarista"><i class="fas fa-user-clock"></i> Cadastrar Diárista</a>
        <a href="./gerar-multiplos-recibos"><i class="fas fa-money-bill-wave"></i> Múltiplos Pagamentos</a>
        <?php endif; ?>

        <?php endif; // operadores ?>

    <?php elseif( !empty($_SESSION['idcaixa']) ): ?>

    <div class="csb-section-title">Caixa</div>
    <a href="./caixa"><i class="fas fa-receipt"></i> Gerar Recibo</a>

    <?php elseif( !empty($_SESSION['idbaixa']) ): ?>

    <div class="csb-section-title">Financeiro</div>
    <a href="./statusvoucher"><i class="fas fa-check-circle"></i> Dar Baixa</a>
    <a href="./relatorio-baixa"><i class="fas fa-file-pdf"></i> Relatório de Baixa</a>

    <?php endif; ?>

    <div class="csb-footer">
        <a href="./sair"><i class="fas fa-sign-out-alt"></i> Sair</a>
    </div>

</nav>
<!-- ═══════════════════ /SIDEBAR ═══════════════════ -->

<script>
function cassNavOpen() {
    document.getElementById('cassiSidebar').classList.add('open');
    document.getElementById('cassOverlay').classList.add('open');
    document.body.style.overflow = 'hidden';
}
function cassNavClose() {
    document.getElementById('cassiSidebar').classList.remove('open');
    document.getElementById('cassOverlay').classList.remove('open');
    document.body.style.overflow = '';
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') cassNavClose();
});
</script>
