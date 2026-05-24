<?php
require_once ('.././config.php');
if( empty( $_SESSION['idgerente'] or $_SESSION['idfaturador'] or $_SESSION['idoperador'] or $_SESSION['comissao']
    or $_SESSION['idreservamanager'] or $_SESSION['idreservaplus'] or $_SESSION['idcaixa'] or $_SESSION['idbaixa']
    or  $_SESSION['idpagarreserva'] or  $_SESSION['idfinanceiro2'] or  $_SESSION['folhaderosto'] or $_SESSION['comissaorelatoriofolha']  ) )
{
    header("location: ../");
}


ob_start();
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <!-- Required meta tags-->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistema de Reserva WISM Group">
    <meta name="author" content="WISM Group">
    <meta name="keywords" content="Cassi Turismo Reserva">

    <!-- Title Page-->
    <title>Cassi Turismo | Reserva</title>
    <link href="../images/icone.png" rel="icon">
    <!-- Fontfaces CSS-->
    <link href="../vendor/font-awesome-4.7/css/font-awesome.min.css" rel="stylesheet" media="all">
    <link href="../vendor/font-awesome-5/css/fontawesome-all.min.css" rel="stylesheet" media="all">

    <!-- Bootstrap CSS-->
    <link href="../vendor/bootstrap-4.1/bootstrap.min.css" rel="stylesheet" media="all">
    <!-- Data-Table  CSS-->
    <link href="../vendor/data-table/buttons.bootstrap.min.css" rel="stylesheet">
    <link href="../vendor/data-table/buttons.dataTables.min.css" rel="stylesheet">
    <link href="../vendor/data-table/dataTables.bootstrap.min.css" rel="stylesheet">

    <!-- Main CSS-->
    <link href="../css/theme.css" rel="stylesheet" media="all">

    <script>
        document.contentType = 'text/html; charset=utf-8';
    </script>
</head>

<body style="font-family: 'Arial' !important;">
    <div class="page-wrapper">
    <!-- HEADER DESKTOP-->
	
    <header class="header-desktop1 d-none d-lg-block">
        <div class="section__content section__content--p30" style="background-color: #1e4770; color: white; font-weight: bold;">
            <div class="header3-wrap">
 
                <div class="header__navbar" style="top: 0px !important;">
                    <ul class="list-unstyled">
                       
                                <span class="bot-line"></span>
                            </a>
                        </li>
                        <?php if( !empty( $_SESSION['idgerente'] ) ){ ?>

                        <li class="has-sub">
                            <a href="#">
                                <i
                                    <i class="fas fa-box" style="color: white;"></i><span style="color: white;">CAIXA</span>
                                    <span class="bot-line"></span>
                                </a>
                                <ul class="header3-sub-list list-unstyled">
                                    <li><a href="./caixa" </i>Caixa</a></li>
                                    <li>
                                        <a href="./nova-conta-corrente"> </i>Conta Corrente</a>
                                    </li>
                                    </li>
                                    <li><a href="./relatorio-contas"> </i>Relatório de Contas</a></li>
                                </ul>
                            </li>

                            <li class="has-sub">
                                <a href="#">
                                    <i class="fas fa-newspaper " style="color: white;"></i><span style="color: white;">CADASTRO|CONSULTA</span>
                                    <span class="bot-line"></span>
                                </a>
                                <ul class="header3-sub-list list-unstyled">
                                    <li>
                                        <a href="./novo-servico"> </i>Serviços</a>
                                    </li>
                                    <li>
                                        <a href="./novo-forncededor"> </i>Fornecedores</a>
                                    </li>


                                </ul>
                            </li>
                            <li class="has-sub">
                                <a href="#">
                                    <i class="fas fa-dollar-sign" style="color: white;"></i><span style="color: white;">FINANCEIRO</span>
                                    <span class="bot-line"></span>
                                </a>
                                <ul class="header3-sub-list list-unstyled">
								    <li><a href="./index"></i>Analítico</a></li>
                                    <li><a href="./inicio-da-fatura"></i>Cadastrar Fatura</a></li>
                                    <li><a href="./statusvoucher"></i>Dar Baixa</a></li>
                                    <li><a href="./cadastrar-diarista"></i>Cadastrar Diárista</a></li>
                                    <li><a href="./gerar-multiplos-recibos"></i>Múltiplos Pagamentos</a></li>
                                    <li><a href="./informacoes-reserva"></i>Pagamento de Comissão</a></li>
                                    <li><a href="./relatorio-conferencia"></i>Relatórios</a></li>
                                    <li><a href="./relatorio-baixa"></i>Relatório de Baixa</a></li>
                                    <li><a href="./relatorio-inventario"></i>Inventário</a></li>
                                    <li><a href="./relatorio-comissao"></i>Relatório de Comissões</a></li>
                                    <li><a href="./mapa-de-servico-de-vouchers-cancelados"></i>Mapa de serviços cancelados</a></li>
                                </ul>
                            </li>
         
                            <?php if($_SESSION['id'] <> 34){ ?>
                                <li class="has-sub">
                                    <a href="#">
                                        <i class="fas fa-user"<i class="fas fa-users" style="color: white;"></i><span style="color: white;">CLIENTE</span>
                                        <span class="bot-line"></span>
                                    </a>
                                    <ul class="header3-sub-list list-unstyled">
                                    
                                        <li>
                                            <a href="./pesquisa-cliente-fornecedor"><i class="fas fa-search"></i>Pesquisar Cliente</a>
                                        </li>
                                    </ul>
                                </li>
                                <li class="has-sub">
                                    <a href="#">
                                        <i class="fas fa-shopping-bag" style="color: white;"></i><span style="color: white;">REVENDEDOR</span>
                                        <span class="bot-line"></span>
                                    </a>
                                    <ul class="header3-sub-list list-unstyled">
                                        <li>
                                            <a href="./lista_revendedores_geral"> </i>Todos personais</a>
                                        </li>
                                            <a href="./lista_clientes_site"> </i>Clientes site</a>
                                        </li>

                                    </ul>
                                </li>
                            <?php }?>
                        <?php }?>
                        <?php if(!empty($_SESSION['idreservamanager'] or $_SESSION['idfinanceiro2'] or $_SESSION['comissaorelatoriofolha'] ) or $_SESSION['id'] == 46 or $_SESSION['id'] == 218  ){ ?>
                            <li class="has-sub">
                                <a href="#">
                                    <i class="fas fa-dollar-sign"></i>Relatórios
                                    <span class="bot-line"></span>
                                </a>
                                <ul class="header3-sub-list list-unstyled">
                                    <li><a href="./relatorio-conferencia"><i class="fas fa-file-pdf"></i>Relatório de Fatura</a></li>
                                </ul>
                            </li>
                            <?php if($_SESSION['id'] == 46){ ?>
                                <li><a href="./statusvoucher"><i class="fas fa-file"></i>Dar Baixa</a></li>
                            <?php }?>

                            <li><a href="./caixa"><i class="fas fa-bank"></i>Gerar Recibo</a></li>
                            <li class="has-sub">
                                <a href="#">
                                    <i class="fas fa-newspaper "></i>Cadastro
                                    <span class="bot-line"></span>
                                </a>
                                <ul class="header3-sub-list list-unstyled">
                                    <li>
                                        <a href="./novo-servico"> <i class="fas fa-plus "></i>Novo Serviço</a>
                                    </li>
                                    <li>
                                        <a href="./novo-forncededor"> <i class="fas fa-plus "></i>Novo Fornecedor</a>
                                    </li>


                                </ul>
                            </li>
                            <li class="has-sub">
                                    <a href="#">
                                        <i class="fas fa-user"></i>Personais
                                        <span class="bot-line"></span>
                                    </a>
                                    <ul class="header3-sub-list list-unstyled">
                                    <li>
                                            <a href="./lista_revendedores"> <i class="fas fa-plus"></i>Personais pendente de aprovação</a>
                                        </li>
                                        <li>
                                            <a href="./lista_revendedores_aprovados_a_pagar"> <i class="fas fa-plus"></i>Personais aprovados a pagar</a>
                                        </li>
                                        <li>
                                            <a href="./lista_revendedores_geral"> <i class="fas fa-plus"></i>Todos personais</a>
                                        </li>
                                        <li>
                                            <a href="./novo-usuario"> <i class="fas fa-plus"></i>Usuários</a>
                                        </li>
                                 
					                    <li>
                                            <a href="./lista_clientes_site"> <i class="fas fa-plus"></i>Clientes site</a>
                                        </li>

                                    </ul>
                                </li>
 				            <li><a href="./mapa-de-servico-de-vouchers-cancelados"><i class="fas fa-file-pdf"></i>Mapa de serviços cancelados</a></li>

                        <?php }?>
                        <?php if(empty( $_SESSION['idcaixa'] or $_SESSION['idbaixa'] ) ){ ?>
                            <?php if($_SESSION['id'] == 30 or $_SESSION['id'] == 34 or $_SESSION['id'] == 265 ){ ?>
                                <li><a href="./caixa"><i class="fas fa-bank"></i>Gerar Recibo</a></li>

                            <?php }?>
                            <?php if( $_SESSION['id'] == 57 or $_SESSION['id'] == 274 or $_SESSION['id'] == 275 or $_SESSION['id'] == 276 or  $_SESSION['id'] == 283 or $_SESSION['id'] == 282 or
                                $_SESSION['id'] == 277 or $_SESSION['id'] == 279 or $_SESSION['id'] == 280 or $_SESSION['id'] == 281 or $_SESSION['id'] == 284  ){ ?>
                                <li><a href="./nova-reserva"> <i class="fas fa-plus"></i>Nova Reserva</a></li>
                                <li><a href="./caixa"><i class="fas fa-bank"></i>Gerar Recibo</a></li>
                                <li><a href="./relatorio-conferencia"><i class="fas fa-file-pdf"></i>Relatórios</a></li>
                            <?php } else { ?>
                                <li class="has-sub">
                                    <a href="./map-visualizar-servico">
                                        <i class="fas fa-street-view " style="color: white;"></i><span style="color: white;">MAPA</span>
                                        <span class="bot-line"></span>
                                    </a>
                                    <ul class="header3-sub-list list-unstyled">
                                        <li>
                                            <a href="./map-visualizar-servico"> <i class="fas fa-street-view "></i>Mapa de Serviço</a>

                                        </li>
                                        <li>
                                            <a href="./mapa-motorista.php"> <i class="fas fa-street-view "></i>Mapa de Serviço por Motorista</a>
                                        </li>
                                        <li>

                                    </ul>
                                </li>
                                <li class="has-sub">
                                    <a href="#">
                                        <i class="fas fa-user-circle" style="color: white;"></i><span style="color: white;">RESERVA</span>
                                        <span class="bot-line"></span>
                                    </a>
                                    <ul class="header3-sub-list list-unstyled">
                                        <li>
                                            <a href="./nova-reserva"> <i class="fas fa-plus"></i>Nova Reserva</a>
                                        </li>
                                        <li>
                                            <a href="./informacoes-reserva"> <i class="fas fa-search"></i>Consultar Reserva</a>
                                        </li>

                                        <li>
                                            <a href="./pesquisar-reserva"> <i class="fas fa-print"></i>Impressão da OS</a>
                                        </li>
                                    </ul>
                                </li>
                                <li><a style="color: green;" href="./relatorio-conferencia"><i class="fas fa-file-pdf"></i>RELATÓRIOS</a></li>
                                <?php if($_SESSION['id'] == 273){?>
                                    <li class="has-sub">
                                        <a href="#">
                                            <i class="fas fa-box"></i>Caixa
                                            <span class="bot-line"></span>
                                        </a>
                                        <ul class="header3-sub-list list-unstyled">
                                            <li><a href="./caixa"><i class="fas fa-funil-dólar"></i>Gerar Recibo</a></li>
                                        </ul>
                                    </li>
                                    <li class="has-sub">
                                        <a href="#">
                                            <i class="fas fa-dollar-sign"></i>Financeiro
                                            <span class="bot-line"></span>
                                        </a>
                                        <ul class="header3-sub-list list-unstyled">
                                            <li><a href="./statusvoucher"><i class="fas fa-file"></i>Dar Baixa</a></li>
                                            <li><a href="./relatorio-baixa"><i class="fas fa-file-pdf"></i>Relatório de Baixa</a></li>
                                            <li><a href="./mapa-de-servico-de-vouchers-cancelados"><i class="fas fa-file-pdf"></i>Mapa de serviços cancelados</a></li>
                                            <li><a href="./cadastrar-diarista"><i class="fas fa-file"></i>Cadastrar diárista</a></li>
                                            <li><a href="./gerar-multiplos-recibos"><i class="fas fa-file"></i>Multiplos pagamentos</a></li>
                                        </ul>
                                    </li>

                                <?php }?>

                            <?php }?>

                        <?php } elseif( !empty( $_SESSION['idcaixa'])  ) { ?>
                            <li class="has-sub">
                                <a href="#">
                                    <i class="fas fa-box"></i>Caixa
                                    <span class="bot-line"></span>
                                </a>
                                <ul class="header3-sub-list list-unstyled">
                                    <li><a href="./caixa"><i class="fas fa-funil-dólar"></i>Gerar Recibo</a></li>
                                </ul>
                            </li>
                        <?php } elseif(!empty( $_SESSION['idbaixa']) ) { ?>
                            <li class="has-sub">
                                <a href="#">
                                    <i class="fas fa-dollar-sign"></i>Financeiro
                                    <span class="bot-line"></span>
                                </a>
                                <ul class="header3-sub-list list-unstyled">
                                    <li><a href="./statusvoucher"><i class="fas fa-file"></i>Dar Baixa</a></li>
                                    <li><a href="./relatorio-baixa"><i class="fas fa-file-pdf"></i>Relatório de Baixa</a></li>
                                </ul>
                            </li>

                        <?php }?>
                    </ul>
                </div>
                <div class="header__tool">
                    <div class="account-wrap">
                        <div class="account-item account-item--style3 clearfix js-item-menu">
                            <div class="">
                                <alt="/<?php echo($_SESSION['nome'] ); ?>" />
                            </div>
                            <div class="content">
                                <div class="dropdown">
                                    </button>
                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    </div> 
									<div class="btn-group">
   <button type="button" class="hamburger hamburger--slider btn btn-dark dropdown-toggle" type="button"
    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="background-color: #1e4770;">
        <span class="hamburger-box">
            <span class="hamburger-inner"></span>
        </span>
        <i class="fas fa-bars icone-branco"></i>
    </button>
    <div style="padding: 10px;" class="dropdown-menu">
        <a style="color: black;" href="./home"> <i class="fas fa-home"></i> Início</a>
        <a style="color: black;" href="./perfil"> <i class="fas fa-user"></i> Perfil</a><br>
        <a style="color: black;" href="./sair"> <i class="fas fa-sign-out-alt"></i> Sair</a><br>
    </div>
</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <!-- END HEADER DESKTOP-->

    <!-- HEADER MOBILE-->
    <header class="header-mobile header-mobile-2 d-block d-lg-none" style="background-color: #1e4770;">
        <div class="header-mobile__bar">
            <div class="container-fluid">
                <div class="header-mobile-inner">
                    <a class="logo" href="home">
                        <h2 style="color: white;">CASSI TURISMO</h2>
                    </a>
                    <!-- Example single danger button -->
                    <div class="btn-group">
                        <button type="button" class="hamburger hamburger--slider btn btn-dark dropdown-toggle" type="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="background-color: #1e4770;">
                                     <span class="hamburger-box">
<span class="hamburger-box">
<span class="hamburger-box">
                                        <span class="hamburger-inner"></span>
                                    </span>
                           <i class="fas fa-bars icone-branco"></i>
                        </button>
                        <div style="padding: 3px;" class="dropdown-menu">
                            <a style="color: black;" href="./home"> <i class="fas fa-home"></i> Início</a>
							<a style="color: black;" href="./perfil"> <i class="fas fa-user"></i> Perfil</a><br>
							<a style="color: black;" href="./sair"> <i class="fas fa-sign-out-alt"></i> Sair</a><br>
                            <div class="dropdown-divider"></div>
                            <a style="color: black;" href="./nova-reserva"> <i class="fas fa-plus"></i> Nova Reserva</a>
                            <a style="color: black;" href="./informacoes-reserva"> <i class="fas fa-search"></i> Consultar Reserva</a>
                            <div class="dropdown-divider"></div>
                            <a style="color: black;" href="./map-visualizar-servico"> <i class="fas fa-street-view "></i> Mapa de Serviço</a>
                            <div class="dropdown-divider"></div>
                            <a style="color: green;" href="./relatorio-conferencia"><i class="fas fa-file-pdf"></i>Relatórios</a>                   
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <div class="sub-header-mobile-3 d-block d-lg-none">
        <div class="header__tool">
            <div class="account-wrap">
                <div class="account-item account-item--style2 clearfix js-item-menu">
                    <div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END HEADER MOBILE -->