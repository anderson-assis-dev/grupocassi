<?php require_once ('header.php');
$todosCliente = $pdo->prepare('select * from `ct_cliente` order by fullname ');
$todosCliente->execute();
$todosCliente2 = $pdo->prepare('select * from `ct_cliente` order by fullname ' );
$todosCliente2->execute();
$todosCliente3 = $pdo->prepare('select * from `ct_cliente` order by fullname ' );
$todosCliente3->execute();
$todosStatus = $pdo->prepare('SELECT * FROM `ct_statusinvoice` ');
$todosStatus->execute();
$todosResponsaveis = $pdo->prepare('SELECT * FROM `ct_usuario` order by firstname ');
$todosResponsaveis->execute();
$todosResponsaveis2 = $pdo->prepare('SELECT * FROM `ct_usuario` order by firstname ');
$todosResponsaveis2->execute();
$todosResponsaveis3 = $pdo->prepare('SELECT * FROM `ct_usuario` order by firstname ');
$todosResponsaveis3->execute();
$todosServicos = $pdo->prepare("select * from `ct_servico` order by  fullname");
$todosServicos->execute();
$todosServicos1 = $pdo->prepare("select * from `ct_servico` order by  fullname");
$todosServicos1->execute();
$empresa = $pdo->prepare('select * from `ct_empresa` order by `fullname` ');
$empresa->execute();
$listaEmpresas = $empresa->fetchAll(PDO::FETCH_CLASS);
?>
<title>Minha Página</title>
    <style>
        body {
            background-color: #f5f5f5;
        }
    </style>
</head>
    <style>
<style>
    .col-md-6, .col-md-12, input, .btn,.col-md-4{
        margin-bottom: 20px;
    }
</style>
<!-- PAGE CONTENT-->
<div class="page-content--bgf7">
    <!-- BREADCRUMB-->
    <section class="au-breadcrumb2">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="au-breadcrumb-content">
                        <div class="au-breadcrumb-left">
                            <span class="au-breadcrumb-span">Navegação:</span>
                            <ul class="list-unstyled list-inline au-breadcrumb__list">
                                <li class="list-inline-item active">
                                    <a href="./index.php">Home</a>
                                </li>
                                <li class="list-inline-item seprate">
                                    <span>/</span>
                                </li>
                                <li class="list-inline-item">Financeiro: Relatório Conferência</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <div class="">
        <div class="">
            <div id="status" class="pull-left col-md-12"></div>

            <?php if( !empty( $_SESSION['idfinanceiro2'] ) or $_SESSION['id'] == 55 or $_SESSION['id'] == 31  or $_SESSION['id'] == 57 or $_SESSION['id'] == 283
                or $_SESSION['id'] == 274 or $_SESSION['id'] == 275 or $_SESSION['id'] == 276 or $_SESSION['id'] == 277 or $_SESSION['id'] == 279 or $_SESSION['id'] == 280
                or $_SESSION['id'] == 281 or $_SESSION['id'] == 46  or $_SESSION['id'] == 282 or $_SESSION['id'] == 284 or $_SESSION['id'] == 218  or !empty($_SESSION['idoperador'])){ ?>
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">RELATÓRIO VOUCHERS </div>
                        </div>
                        <div class="card-body">
                            <?php if($_SESSION['id'] == 40 or $_SESSION['id'] == 265 or $_SESSION['id'] == 211 or $_SESSION['id'] == 35  or $_SESSION['id'] == 57or $_SESSION['id'] == 283
                                or $_SESSION['id'] == 274 or $_SESSION['id'] == 275 or $_SESSION['id'] == 276 or $_SESSION['id'] == 277 or $_SESSION['id'] == 279 or $_SESSION['id'] == 280
                                or $_SESSION['id'] == 281 or $_SESSION['id'] == 46 or $_SESSION['id'] == 282 or $_SESSION['id'] == 284 or $_SESSION['id'] == 218 or !empty($_SESSION['idoperador']) ){ ?>
                                <form action="./relatorio/pdf-relatorio-conferencia-por-pagamento" target="_blank" method="post">
                                    <div class="col-md-4 pull-left">
                                        <strong><label for="responsavel">Responsável</label></strong>
                                        <select  required class="form-control" name="responsavel" id="responsavel" >
                                            <option selected value="<?php echo($_SESSION['id']); ?>"><?php echo($_SESSION['nome']); ?></option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 pull-left">
                                        <strong><label for="inicio">Data do Pagamento Inicial</label></strong>
                                        <input required type="date" name="inicio" id="inicio" class="form-control">
                                    </div>

                                    <div class="col-md-4 pull-right">
                                        <strong><label for="fim">Data do Pagamento Final</label></strong>
                                        <input required type="date" name="fim" id="fim" class="form-control">
                                    </div>
                                    <div class="container-fluid pull-left">
                                        <button type="submit" class="btn btn-success btn-lg" name="buscar" id="buscar">Gerar Relatório</button>
                                    </div>

                                </form>
                            <?php } else { ?>
                                <form action="./relatorio/pdf-relatorio-conferencia-abertura" target="_blank" method="post">
                                    <div class="col-md-6 pull-left">
                                        <strong><label for="servico">Serviço</label></strong>
                                        <select  required class="form-control" name="servico[]" multiple id="servico">
                                            <option value="0" selected>Todos Serviços</option>
                                            <?php while( $servicos = $todosServicos->fetch( PDO::FETCH_ASSOC ) ){ ?>
                                                <option value="<?php echo($servicos['id']); ?>"><?php echo($servicos['fullname']); ?></option>
                                            <?php }?>
                                        </select>
                                    </div>
                                    <div class="col-md-6 pull-right">
                                        <strong><label for="cliente">Cliente</label></strong>
                                        <select  required class="form-control" name="cliente" id="cliente" >
                                            <?php if($_SESSION['id'] == 38){ ?>
                                                <?php while( $cliente = $todosCliente2->fetch( PDO::FETCH_ASSOC ) ){ ?>
                                                    <?php if($cliente['id'] == 169){ ?>
                                                        <option selected value="<?php echo($cliente['id']); ?>"><?php echo($cliente['fullname']); ?></option>
                                                    <?php }?>
                                                <?php }?>
                                            <?php } elseif ($_SESSION['id'] == 31) {?>
                                                <?php while( $cliente = $todosCliente2->fetch( PDO::FETCH_ASSOC ) ){ ?>
                                                    <?php if($cliente['id'] == 35){ ?>
                                                        <option selected value="<?php echo($cliente['id']); ?>"><?php echo($cliente['fullname']); ?></option>
                                                    <?php }?>
                                                <?php }?>
                                            <?php } else {?>
                                                <option selected value="0">TODOS</option>
                                                <?php while( $cliente = $todosCliente2->fetch( PDO::FETCH_ASSOC ) ){ ?>
                                                    <option value="<?php echo($cliente['id']); ?>"><?php echo($cliente['fullname']); ?></option>
                                                <?php }?>
                                            <?php }?>

                                        </select>
                                    </div>

                                    <div class="col-md-6 pull-left">
                                        <strong><label for="responsavel">Responsável</label></strong>
                                        <select  required class="form-control" name="responsavel" id="responsavel" >
                                            <?php if($_SESSION['id'] == 38){  ?>
                                                <option selected value="0">TODOS</option>
                                                <?php while( $responsavel = $todosResponsaveis->fetch( PDO::FETCH_ASSOC ) ){ ?>
                                                    <option value="<?php echo($responsavel['id']); ?>"><?php echo(strtoupper($responsavel['firstname']." ".$responsavel['lastname'])); ?></option>
                                                <?php }?>
                                            <?php } else { ?>
                                                <?php if( !empty($_SESSION['idoperador'] or $_SESSION['idfinanceiro2']) or $_SESSION['id'] == 55 ){ ?>
                                                    <option selected value="<?php echo($_SESSION['id']); ?>">
                                                        <?php echo($_SESSION['nome']); ?>
                                                    </option>
                                                <?php } else { ?>
                                                    <option selected value="0">TODOS</option>
                                                    <?php while( $responsavel = $todosResponsaveis->fetch( PDO::FETCH_ASSOC ) ){ ?>
                                                        <option value="<?php echo($responsavel['id']); ?>"><?php echo(strtoupper($responsavel['firstname']." ".$responsavel['lastname'])); ?></option>
                                                    <?php }?>
                                                <?php }?>
                                            <?php }?>

                                        </select>
                                    </div>
                                    <div class="col-md-4 pull-left">
                                        <strong><label for="abertura">Abertura Inicial</label></strong>
                                        <input required type="date" name="abertura" id="abertura" class="form-control">
                                    </div>

                                    <div class="col-md-4 pull-left">
                                        <strong><label for="aberturafinal">Abertura Final</label></strong>
                                        <input required type="date" name="aberturafinal" id="aberturafinal" class="form-control">
                                    </div>
                                    <div class="col-md-4 pull-right">
                                        <strong><label for="abertura">Tipo de Relatório</label></strong>
                                        <select class="form-control" name="tiporelatorio" id="tiporelatorio" required>
                                            <option value="0" selected>COMPLETO</option>
                                            <!--
                                            <option value="1" >RESUMIDO</option>
                                            -->
                                        </select>
                                    </div>
                                    <div class="container-fluid pull-left">
                                        <button type="submit" class="btn btn-success btn-lg" name="buscar" id="buscar">Gerar Relatório</button>
                                    </div>

                                </form>
                            <?php }?>


                        </div>
                    </div>

                </div>
            <?php } else{ ?>
                <div class="col-lg-12">
                    <div class="accordion" id="accordionExample">
                        <div class="card">
                            <div class="card-header" id="headingOne" style="background-color: white;">
                                <h2 class="mb-0">
                                    <button style="color:black;"  class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                        Relatório por Data de Embarque
                                    </button>
                                </h2>
                            </div>

                            <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionExample">
                                <div class="card-body">
                                    <form action="./relatorio/pdf-relatorio-conferencia.php" target="_blank" method="post">
                                        <div class="col-md-6 pull-left">
                                            <strong><label for="periodoinicial">Periodo Inicial</label></strong>
                                            <input required type="date" name="periodoinicial" id="periodoinicial" class="form-control">
                                        </div>
                                        <div class="col-md-6 pull-right">
                                            <strong><label for="periodofinal">Periodo Final</label></strong>
                                            <input required type="date" name="periodofinal" id="periodofinal" class="form-control">
                                        </div>
                                        <div class="col-md-3 pull-left">
                                            <strong><label for="cliente">Agência</label></strong>
                                            <select class="form-control" name="cliente" id="cliente" >
                                                <option selected value="0">TODOS</option>
                                                <?php while( $dadosCliente = $todosCliente->fetch( PDO::FETCH_ASSOC ) ){ ?>
                                                    <option value="<?php echo($dadosCliente['id']); ?>"><?php echo($dadosCliente['fullname']); ?></option>
                                                <?php }?>
                                            </select>
                                        </div>
                                        <div class="col-md-3 pull-left">
                                            <strong><label for="cliente"> Empresa</label></strong>
                                            <select name="idempresa" id="idempresa" class="form-control" required>
                                                <option value="" selected disabled>Selecione a Empresa</option>
                                                <?php foreach ($listaEmpresas as $item_empresa) { ?>
                                                    <option value="<?php echo htmlentities($item_empresa->id, ENT_QUOTES, 'UTF-8'); ?>">
                                                        <?php echo htmlentities($item_empresa->fullname, ENT_QUOTES, 'UTF-8'); ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6 pull-right">
                                            <strong><label for="responsavel">Responsável</label></strong>
                                            <select  required class="form-control" name="responsavel" id="responsavel" >
                                                <option selected value="0">TODOS</option>
                                                <?php while( $responsavel = $todosResponsaveis->fetch( PDO::FETCH_ASSOC ) ){ ?>
                                                    <option value="<?php echo($responsavel['id']); ?>"><?php echo(strtoupper($responsavel['firstname']." ".$responsavel['lastname'])); ?></option>
                                                <?php }?>
                                            </select>
                                        </div>
                                        <div class="col-md-4 pull-left">
                                            <strong><label for="nomepax">Nome do pax</label></strong>
                                            <input type="text" name="nomepax" id="nomepax" class="form-control">
                                        </div>
                                        <div class="col-md-4 pull-left">
                                            <strong><label for="tiporelatorio">Tipo de Relatório</label></strong>
                                            <select class="form-control" name="tiporelatorio" id="tiporelatorio" >
                                                <option value="0">Descritivo</option>
                                                <option value="1">Resumido</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 pull-right">
                                            <strong><label for="status">Status</label></strong>
                                            <select class="form-control" name="status[]" multiple id="status" >
                                                <option selected value="0">TODOS</option>
                                                <?php while( $dadosStatus = $todosStatus->fetch( PDO::FETCH_ASSOC ) ){ ?>
                                                    <option value="<?php echo($dadosStatus['id']); ?>"><?php echo($dadosStatus['nameinvoice']); ?></option>
                                                <?php }?>
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-success btn-lg" name="buscar" id="buscar">Gerar Relatório</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header" id="headingTwo" style="background-color: white;">
                                <h2 class="mb-0">
                                    <button style="color:black;"  class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false"
                                            aria-controls="collapseTwo">
                                        Relatório por Data de Abertura
                                    </button>
                                </h2>
                            </div>
                            <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionExample">
                                <div class="card-body">
                                    <form action="./relatorio/pdf-relatorio-conferencia-abertura" target="_blank" method="post">
                                        <div class="col-md-6 pull-left">
                                            <strong><label for="servico">Serviço</label></strong>
                                            <select  required class="form-control" name="servico[]" multiple id="servico">
                                                <option value="0" selected>Todos Serviços</option>
                                                <?php while( $servicos = $todosServicos1->fetch( PDO::FETCH_ASSOC ) ){ ?>
                                                    <option value="<?php echo($servicos['id']); ?>"><?php echo($servicos['fullname']); ?></option>
                                                <?php }?>
                                            </select>
                                        </div>
                                        <div class="col-md-3 pull-right">
                                            <strong><label for="cliente">Agência / Revendedor </label></strong>
                                            <select  required class="form-control" name="cliente" id="cliente" >
                                                <option selected value="0">TODOS</option>
                                                <?php while( $cliente = $todosCliente2->fetch( PDO::FETCH_ASSOC ) ){ ?>
                                                    <option value="<?php echo($cliente['id']); ?>"><?php echo($cliente['fullname']); ?></option>
                                                <?php }?>
                                            </select>
                                        </div>
                                        <div class="col-md-3 pull-left">
                                            <strong><label for="cliente"> Empresa</label></strong>
                                            <select name="idempresa" id="idempresa" class="form-control" required>
                                                <option value="" selected disabled>Selecione a Empresa</option>
                                                <?php foreach ($listaEmpresas as $item_empresa) { ?>
                                                    <option value="<?php echo htmlentities($item_empresa->id, ENT_QUOTES, 'UTF-8'); ?>">
                                                        <?php echo htmlentities($item_empresa->fullname, ENT_QUOTES, 'UTF-8'); ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6 pull-left">
                                            <strong><label for="responsavel">Responsável</label></strong>
                                            <select  required class="form-control" name="responsavel" id="responsavel" >
                                                <option selected value="0">TODOS</option>
                                                <?php while( $responsavel = $todosResponsaveis2->fetch( PDO::FETCH_ASSOC ) ){ ?>
                                                    <option value="<?php echo($responsavel['id']); ?>"><?php echo(strtoupper($responsavel['firstname']." ".$responsavel['lastname'])); ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="col-md-4 pull-left">
                                            <strong><label for="abertura">Abertura Inicial</label></strong>
                                            <input required type="date" name="abertura" id="abertura" class="form-control">
                                        </div>

                                        <div class="col-md-4 pull-left">
                                            <strong><label for="aberturafinal">Abertura Final</label></strong>
                                            <input required type="date" name="aberturafinal" id="aberturafinal" class="form-control">
                                        </div>
                                        <div class="col-md-4 pull-right">
                                            <strong><label for="abertura">Tipo de Relatório</label></strong>
                                            <select class="form-control" name="tiporelatorio" id="tiporelatorio" required>
                                                <option value="0" selected>COMPLETO</option>
                                                <option value="1" >RESUMIDO</option>
                                            </select>
                                        </div>
                                        <div class="container-fluid pull-left">
                                            <button type="submit" class="btn btn-success btn-lg" name="buscar" id="buscar">Gerar Relatório</button>
                                        </div>

                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header" id="headingTwo1" style="background-color: white;">
                                <h2 class="mb-0">
                                    <button style="color:black;" class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseTwo1" aria-expanded="false"
                                            aria-controls="collapseTwo1">
                                        Relatório por Data de Pagamento
                                    </button>
                                </h2>
                            </div>
                            <div id="collapseTwo1" class="collapse" aria-labelledby="headingTwo1" data-parent="#accordionExample">
                                <div class="card-body">
                                    <form action="./relatorio/pdf-relatorio-conferencia-por-pagamento" target="_blank" method="post">
                                        <div class="col-md-4 pull-left">
                                            <strong><label for="responsavel">Responsável</label></strong>
                                            <select  required class="form-control" name="responsavel" id="responsavel" >
                                                <option selected value="0">TODOS</option>
                                                <?php while( $responsavel = $todosResponsaveis3->fetch( PDO::FETCH_ASSOC ) ){ ?>
                                                    <option value="<?php echo($responsavel['id']); ?>"><?php echo(strtoupper($responsavel['firstname']." ".$responsavel['lastname'])); ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="col-md-2 pull-left">
                                            <strong><label for="cliente">Agência / Revendedor</label></strong>
                                            <select  required class="form-control" name="cliente" id="cliente" >
                                                <option selected value="0">TODOS</option>
                                                <?php while( $cliente = $todosCliente3->fetch( PDO::FETCH_ASSOC ) ){ ?>
                                                    <option value="<?php echo($cliente['id']); ?>"><?php echo($cliente['fullname']); ?></option>
                                                <?php }?>
                                            </select>
                                        </div>
                                        <div class="col-md-2 pull-left">
                                            <strong><label for="cliente"> Empresa</label></strong>
                                            <select name="idempresa" id="idempresa" class="form-control" required>
                                                <option value="" selected disabled>Selecione a Empresa</option>
                                                <?php foreach ($listaEmpresas as $item_empresa) { ?>
                                                    <option value="<?php echo htmlentities($item_empresa->id, ENT_QUOTES, 'UTF-8'); ?>">
                                                        <?php echo htmlentities($item_empresa->fullname, ENT_QUOTES, 'UTF-8'); ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="col-md-4 pull-left">
                                            <strong><label for="tipo">Tipo</label></strong>
                                            <select  required class="form-control" name="tipo" id="tipo" >
                                                <option selected value="1">Descritivo</option>
                                                <option  value="2">Resumido</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 pull-left">
                                            <strong><label for="inicio">Data do Pagamento Inicial</label></strong>
                                            <input required type="date" name="inicio" id="inicio" class="form-control">
                                        </div>

                                        <div class="col-md-6 pull-right">
                                            <strong><label for="fim">Data do Pagamento Final</label></strong>
                                            <input required type="date" name="fim" id="fim" class="form-control">
                                        </div>
                                        <div class="container-fluid pull-left">
                                            <button type="submit" class="btn btn-success btn-lg" name="buscar" id="buscar">Gerar Relatório</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            <?php }?>

        </div>
    </div>
    <?php require_once ('footer.php'); ?>
