<?php require_once ('header.php');

$pdo->exec("set names utf8");
$cliente       = $pdo->prepare('select * from `ct_fornecedor` ORDER BY `ct_fornecedor`.`fullname`');
$cliente->execute();
$clientess = $cliente->fetchAll(PDO::FETCH_CLASS);
$empresas = $pdo->prepare('select * from `ct_empresa`');
$empresas->execute();
$empresas2 = $pdo->prepare('select * from `ct_empresa`');
$empresas2->execute();
$status        = $pdo->prepare('select * from `ct_statusinvoice`');
$status->execute();
$planoDeContas = $pdo->prepare("select * from `ct_planaccounts` order by `name` ");
$planoDeContas->execute();
$tipoCaixa = $pdo->prepare("select * from `ct_tipocaixa` order by `name` ");
$tipoCaixa->execute();
$contaCorrente = $pdo->prepare("select * from `ct_currentaccount` order by `name`");

$contaCorrente->execute();
if($_SESSION['id']  == 34 or $_SESSION['id']  == 208)
{
    $usuariosCadastrados = $pdo->prepare('select * from `ct_usuario` order by firstname');
    $usuariosCadastrados->execute();
    $listaUsusarios = $usuariosCadastrados->fetchAll(PDO::FETCH_CLASS);
}
if( isset( $_POST['novatransacao'] ) )
{
    $valor  = str_replace(".", "", $_POST['valor']);
    $valor1 = str_replace(",", ".", $valor);
    $novaTransacao = $pdo->prepare(
            "insert into `ct_caixa` (`id`, `datevencimento`, `datepagamento`, `datecompetencia`, `nome` ,`descricao`, `idcliente`, `idtipo`, `idconta`, 
                     `idplano`, `idempresa` ,`idstatus`, `valor`, `idusr`, `dataabertura`) values (DEFAULT, :vencimento, :pagamento, :competencia, :nome ,:descricao, 
                      :cliente, :tipo, :conta, :plano, :empresa ,:statuus, :valor, :idusr, :abertura)");
    $novaTransacao->execute(
            array(
                    ":vencimento"  => $_POST['datavencimento'],
                    ":pagamento"   => $_POST['datapagamento'],
                    ":competencia" => $_POST['datacompetencia'],
                    ":nome"        => $_POST['nome'],
                    ":descricao"   => $_POST['documento'],
                    ":cliente"     => $_POST['favorecido'],
                    ":tipo"        => $_POST['tipo'],
                    ":conta"       => $_POST['contacorrente'],
                    ":plano"       => $_POST['planocontas'],
                    ":empresa"     => $_POST['empresa'],
                    ":statuus"     => $_POST['status'],
                    ":valor"       => $valor1,
                    "idusr"        => $_POST['responsavel'],
                    ":abertura"    => date("Y-m-d")
            )
    );
    $idtransacao = $pdo->lastInsertId();
    header('location: editar-transacao?idtransacao='.$idtransacao);
}
if( isset( $_POST['removertransacao'] ) )
{
    $remover_transacao = $pdo->prepare('delete from `ct_caixa` where id = :id');
    $remover_transacao->execute(array(":id" => $_POST['idtransacao']));

    echo("<div class='alert alert-danger' style='margin-top: 100px;' role='alert'>Transação de ".$_POST['nometransacao']." foi removida</div>");
    if(empty( $_SESSION['idcaixa'] ))
    {
        $caixa         = $pdo->prepare(
            " select c.id,c.datevencimento, c.nome ,c.datecompetencia, c.datepagamento, c.descricao, forne.fullname as fornecedor, tc.`name` as tipo, cc.`name`
  as conta,p.`name` as plano, s.`nameinvoice` as situacao, c.valor, em.fullname as empresa from  `ct_caixa` c left join ct_fornecedor forne on forne.id = c.idcliente
  left join ct_tipocaixa tc on tc.id = c.idtipo left join ct_currentaccount cc on cc.id = c.idconta left join ct_planaccounts p on p.id = c.idplano
  left join ct_statusinvoice s on s.id = c.idstatus left join ct_empresa em on em.id = c.idempresa where c.`datevencimento` = :pagamento  ");
        $caixa->execute( array(":pagamento" => date("Y-m-d")));
    }else{
        $caixa         = $pdo->prepare(
            " select c.id,c.datevencimento, c.nome ,c.datecompetencia, c.datepagamento, c.descricao, forne.fullname as fornecedor, tc.`name` as tipo, cc.`name`
  as conta,p.`name` as plano, s.`nameinvoice` as situacao, c.valor, em.fullname as empresa from  `ct_caixa` c left join ct_fornecedor forne on forne.id = c.idcliente
  left join ct_tipocaixa tc on tc.id = c.idtipo left join ct_currentaccount cc on cc.id = c.idconta left join ct_planaccounts p on p.id = c.idplano
  left join ct_statusinvoice s on s.id = c.idstatus left join ct_empresa em on em.id = c.idempresa where c.`datevencimento` = :pagamento and c.idusr = :idusuario  ");
        $caixa->execute( array(":pagamento" => date("Y-m-d"), ":idusuario" => $_SESSION['id']));
    }


}
if( isset( $_POST['pesquisartransacao'] ) )
{
    $_SESSION['datavencimentoinicial'] = $_POST['datavencimentoinicial'];
    $_SESSION['datavencimentofinal']   = $_POST['datavencimentofinal'];
    $_SESSION['favorecido']            = $_POST['favorecido'];
    $_SESSION['nomepesquisa']            = $_POST['nomepesquisa'];
    $_SESSION['nrecibo']            = $_POST['nrecibo'];
    $_SESSION['idempresa']            = $_POST['idempresa'];
    $sql = "select c.id,c.datevencimento, c.nome ,c.datecompetencia, c.datepagamento, c.descricao, forne.fullname  as fornecedor, tc.`name` as tipo, cc.`name` as conta,
    p.`name` as plano, s.`nameinvoice` as situacao, c.valor, em.fullname as empresa from  `ct_caixa` c left join ct_fornecedor forne on forne.id = c.idcliente left join ct_tipocaixa tc
    on tc.id = c.idtipo left join ct_currentaccount cc on cc.id = c.idconta left join ct_planaccounts p on p.id = c.idplano left join ct_statusinvoice s 
    on s.id = c.idstatus left join ct_empresa em on em.id = c.idempresa where 1=1";
    if( $_POST['favorecido'] > 0)
    {
        $sql .= " and c.idcliente = ".$_POST['favorecido']." ";
    }
    if( $_POST['idempresa'] > 0)
    {
        $sql .= " and c.idempresa = ".$_POST['idempresa']." ";
    }
    if( $_POST['nrecibo'] > 0 )
    {
        $sql .= " and c.id = ".$_POST['nrecibo']." ";
    }
    if( !empty($_POST['nomepesquisa']))
    {
        $sql .= " and c.nome like '%".$_POST['nomepesquisa']."%' ";
    }
    if( !empty($_POST['datavencimentoinicial']) > 0)
    {
        $sql .= " and c.datevencimento >= '".$_POST['datavencimentoinicial']."' and c.datevencimento <= '".$_POST['datavencimentofinal']."'";
    }
    echo($sql);
    $caixa= $pdo->prepare($sql);
    $caixa->execute();

}
else{
    if(!empty( $_SESSION['idgerente'] ))
    {
        $caixa         = $pdo->prepare(
            " select c.id,c.datevencimento, c.nome ,c.datecompetencia, c.datepagamento, c.descricao, forne.fullname as fornecedor, tc.`name` as tipo, cc.`name`
  as conta,p.`name` as plano, s.`nameinvoice` as situacao, c.valor, em.fullname as empresa from  `ct_caixa` c left join ct_fornecedor forne on forne.id = c.idcliente
  left join ct_tipocaixa tc on tc.id = c.idtipo left join ct_currentaccount cc on cc.id = c.idconta left join ct_planaccounts p on p.id = c.idplano
  left join ct_statusinvoice s on s.id = c.idstatus left join ct_empresa em on em.id = c.idempresa where c.`datevencimento` = :pagamento  ");
        $caixa->execute( array(":pagamento" => date("Y-m-d")));
    }else{
        $caixa         = $pdo->prepare(
            " select c.id,c.datevencimento, c.nome ,c.datecompetencia, c.datepagamento, c.descricao, forne.fullname as fornecedor, tc.`name` as tipo, cc.`name`
  as conta,p.`name` as plano, s.`nameinvoice` as situacao, c.valor, em.fullname as empresa from  `ct_caixa` c left join ct_fornecedor forne on forne.id = c.idcliente
  left join ct_tipocaixa tc on tc.id = c.idtipo left join ct_currentaccount cc on cc.id = c.idconta left join ct_planaccounts p on p.id = c.idplano
  left join ct_statusinvoice s on s.id = c.idstatus left join ct_empresa em on em.id = c.idempresa where c.`datevencimento` = :pagamento and c.idusr = :idusuario  ");
        $caixa->execute( array(":pagamento" => date("Y-m-d"), ":idusuario" => $_SESSION['id']));
    }

}
$registroCaixa = $caixa->fetchAll(PDO::FETCH_CLASS);


?>
<style>
    .col-md-6, .col-md-4, .col-md-4{
        margin-bottom: 20px;
    }
</style>
<!-- PAGE CONTENT-->
<div class="page-content--bgf7">
    <!-- BREADCRUMB-->
    <section class="au-breadcrumb2">
        <div class="container">
            <div class="row">
                <div class="col-md-12" style="">
                    <div class="au-breadcrumb-content">
                        <div class="au-breadcrumb-left">
                            <span class="au-breadcrumb-span">Você está aqui:</span>
                            <ul class="list-unstyled list-inline au-breadcrumb__list">
                                <li class="list-inline-item active">
                                    <a href="./index.php">Home</a>
                                </li>
                                <li class="list-inline-item seprate">
                                    <span>/</span>
                                </li>
                                <li class="list-inline-item">Caixa</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <div class="">
        <div class="">
            <div class="col-lg-12" style="margin-right: 20px; margin-left: 20px;">
                <div class="accordion" id="accordionExample">
                    <hr>
                    <div class="card">
                        <div class="card-header" id="headingOne">
                            <h5 class="mb-0">
                                <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseOne"  aria-controls="collapseOne">
                                    Incluir Transação
                                </button>
                            </h5>
                        </div>

                        <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionExample">
                            <div class="card-body">
                                <form action="" method="post">
                                    <div class="col-md-4 pull-left">
                                        <strong><label for="datavencimento">Data de Vencimento</label></strong>
                                        <input type="date" class="form-control" name="datavencimento" id="datavencimento">
                                    </div>
                                    <div class="col-md-4 pull-left">
                                        <strong><label for="datapagamento">Data de Pagamento</label></strong>
                                        <input type="date" class="form-control" name="datapagamento" id="datapagamento">
                                    </div>
                                    <div class="col-md-4 pull-right">
                                        <strong><label for="datacompetencia">Data da Competência</label></strong>
                                        <input type="date" class="form-control" name="datacompetencia" id="datacompetencia">
                                    </div>

                                    <?php if($_SESSION['id'] == 34 or $_SESSION['id'] == 208 or $_SESSION['id'] == 207){ ?>
                                        <div class="col-md-3 pull-left">
                                            <strong><label for="nome">Nome</label></strong>
                                            <input type="text" name="nome" id="nome" class="form-control" >
                                        </div>
                                        <div class="col-md-3 pull-left">
                                            <strong><label for="documento">Descrição</label></strong>
                                            <input type="text" name="documento" id="documento" class="form-control" >
                                        </div>
                                        <div class="col-md-3 pull-left">
                                            <strong><label for="favorecido">Fornecedor</label></strong>
                                            <select class="form-control" name="favorecido" id="favorecido">
                                                <option value="1" selected>Selecione</option>
                                                <?php foreach ( $clientess as $item ){ ?>
                                                    <option value="<?php echo($item->id); ?>" ><?php echo( utf8_encode( $item->fullname)); ?></option>
                                                <?php }?>
                                            </select>
                                        </div>
                                        <div class="col-md-3 pull-right">
                                            <strong><label for="responsavel">Responsável</label></strong>
                                            <select class="form-control" name="responsavel" id="responsavel">
                                                <option value="<?php echo($_SESSION['id']); ?>" selected>Selecione</option>
                                                <?php if($_SESSION['id'] == 208 or $_SESSION['id'] == 207 or $_SESSION['id'] == 30) { ?>
                                                    <?php foreach ( $listaUsusarios as $item ){ ?>
							                                <option value="<?php echo($item->id); ?>" ><?php echo(  strtoupper( utf8_encode($item->firstname." ".$item->lastname) )); ?></option>
                                                    <?php }?>
                                                <?php } else { ?>
                                                    <?php $usersIds = [34, 285, 44, 366, 226, 376, 168, 281, 397, 355, 376, 59, 402, 405]; ?>
                                                    <?php foreach ( $listaUsusarios as $item ){ ?>
                                                        <?php if( in_array($item->id, $usersIds) ) { ?>
                                                            <option value="<?php echo($item->id); ?>" ><?php echo(  strtoupper( utf8_encode($item->firstname." ".$item->lastname) )); ?></option>
                                                        <?php } ?>
                                                    <?php }?>
                                                <?php }?>

                                            </select>
                                        </div>
                                    <?php } else { ?>
                                        <div class="col-md-4 pull-left">
                                            <strong><label for="nome">Nome</label></strong>
                                            <input type="text" name="nome" id="nome" class="form-control" >
                                        </div>
                                        <div class="col-md-4 pull-left">
                                            <strong><label for="documento">Descrição</label></strong>
                                            <input type="text" name="documento" id="documento" class="form-control" >
                                        </div>
                                        <div class="col-md-4 pull-right">
                                            <strong><label for="favorecido">Fornecedor</label></strong>
                                            <select class="form-control" name="favorecido" id="favorecido">
                                                <option value="1" selected>Selecione</option>
                                                <?php foreach ( $clientess as $item ){ ?>
                                                    <option value="<?php echo($item->id); ?>" ><?php echo( utf8_encode( $item->fullname)); ?></option>
                                                <?php }?>
                                            </select>
                                        </div>
                                        <input type="hidden" name="responsavel" id="responsavel" value="<?php echo($_SESSION['id']); ?>" >
                                    <?php }?>

                                    <div class="col-md-4 pull-left">
                                        <strong><label for="tipo">Empresa</label></strong>
                                        <select required class="form-control" name="empresa" id="empresa">
                                            <option value="1" >Selecione</option>
                                            <?php while ( $dadosEmpresa = $empresas->fetch( PDO::FETCH_ASSOC ) ){ ?>
                                                <option value="<?php echo($dadosEmpresa['id']); ?>"><?php echo(htmlentities($dadosEmpresa['fullname'], ENT_QUOTES, 'UTF-8')); ?></option>
                                            <?php }?>
                                        </select>
                                    </div>
                                    <div class="col-md-4 pull-left">
                                        <strong><label for="tipo">Tipo</label></strong>
                                        <select class="form-control" name="tipo" id="tipo">
                                            <option value="1" selected>Selecione</option>
                                            <?php while ( $dadosTipoCaixa = $tipoCaixa->fetch( PDO::FETCH_ASSOC ) ){ ?>
                                                <option value="<?php echo($dadosTipoCaixa['id']); ?>" ><?php echo( utf8_encode( $dadosTipoCaixa['name'])); ?></option>
                                            <?php }?>
                                        </select>
                                    </div>
                                    <div class="col-md-4 pull-right">
                                        <strong><label for="contacorrente">Conta Corrente</label></strong>
                                        <select class="form-control" name="contacorrente" id="contacorrente">
                                            <option value="1" selected>Selecione</option>
                                            <?php while ( $dadosConta = $contaCorrente->fetch( PDO::FETCH_ASSOC ) ){ ?>
                                                <option value="<?php echo($dadosConta['id']); ?>"><?php echo(htmlentities($dadosConta['name'], ENT_QUOTES, 'UTF-8')); ?></option>
                                            <?php }?>
                                        </select>
                                    </div>
                                    <div class="col-md-4 pull-left">
                                        <strong><label for="planocontas">Plano de Contas</label></strong>
                                        <select class="form-control" name="planocontas" id="planocontas">
                                            <option value="1" selected>Selecione</option>
                                            <?php while ( $dadosPlano = $planoDeContas->fetch( PDO::FETCH_ASSOC ) ){ ?>
                                               <option value="<?php echo($dadosPlano['id']); ?>"><?php echo(htmlentities($dadosPlano['name'], ENT_QUOTES, 'UTF-8')); ?></option>
                                            <?php }?>
                                        </select>
                                    </div>
                                    <div class="col-md-4 pull-left">
                                        <strong><label for="valor">Valor da Transação</label></strong>
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon1">R$</span>
                                            </div>
                                            <input type="text" class="form-control" name="valor" id="valor" onKeyPress="return(moeda(this,'.',',',event))">
                                        </div>
                                    </div>
                                    <div class="col-md-4 pull-right">
                                        <strong><label for="status">Status</label></strong>
                                        <select class="form-control" name="status" id="status" required>
                                            <option value="1" selected>Selecione</option>
                                            <?php while ( $dadosStatus = $status->fetch( PDO::FETCH_ASSOC ) ){ ?>
                                                <option value="<?php echo($dadosStatus['id']); ?>" ><?php echo($dadosStatus['nameinvoice']); ?></option>
                                            <?php }?>
                                        </select>
                                    </div>
                                    <div class="col-md-6 pull-left">
                                        <button class="btn btn-outline-success btn-block btn-large" name="novatransacao" type="submit">
                                            Incluir transação
                                        </button>
                                    </div>
                                    <div class="col-md-6 pull-left">
                                        <button class="btn btn-outline-warning btn-block btn-large" type="reset">
                                            Corrigir informações
                                        </button>
                                    </div>
                                </form>

                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header" id="headingTwo">
                            <h5 class="mb-0">
                                <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="true" aria-controls="collapseTwo">
                                   Pesquisar Transação
                                </button>
                            </h5>
                        </div>
                        <div id="collapseTwo" class="collapse show" aria-labelledby="headingTwo" data-parent="#accordionExample">
                            <div class="card-body">
                                <form action="" method="post">
                                    <div class="col-md-6 pull-left">
                                        <strong><label for="datavencimentoinicial">Data de Vencimento Inicial</label></strong>
                                        <?php if(!empty($_SESSION['datavencimentoinicial'])){ ?>
                                            <input value="<?php echo($_SESSION['datavencimentoinicial']); ?>" type="date" 
                                                   class="form-control" name="datavencimentoinicial" id="datavencimentoinicial">
                                        <?php } else { ?>
                                            <input value="<?php echo(date("Y-m-d")); ?>" type="date" 
                                                   class="form-control" name="datavencimentoinicial" id="datavencimentoinicial">
                                        <?php }?>

                                    </div>
                                    <div class="col-md-6 pull-right">
                                        <strong><label for="datavencimentofinal">Data de Vencimento Final </label></strong>
                                        <?php if(!empty($_SESSION['datavencimentoinicial'])){ ?>
                                            <input value="<?php echo($_SESSION['datavencimentofinal']); ?>" type="date" 
                                                   class="form-control" name="datavencimentofinal" id="datavencimentofinal">
                                        <?php } else { ?>
                                            <input value="<?php echo(date("Y-m-d")); ?>" type="date" 
                                                   class="form-control" name="datavencimentofinal" id="datavencimentofinal">
                                        <?php }?>
                                    </div>
                                    <div class="col-md-3 pull-left">
                                        <strong><label for="tipo">Empresa</label></strong>
                                        <select required class="form-control" name="idempresa" id="idempresa">
                                            <option value="1" >Selecione</option>
                                            <?php while ( $dadosEmpresa = $empresas2->fetch( PDO::FETCH_ASSOC ) ){ ?>
                                                <option value="<?php echo($dadosEmpresa['id']); ?>"><?php echo(htmlentities($dadosEmpresa['fullname'], ENT_QUOTES, 'UTF-8')); ?></option>
                                            <?php }?>
                                        </select>
                                    </div>
                                    <div class="col-md-3 pull-left">
                                        <strong><label for="nomepesquisa">Nome  </label></strong>
                                        <?php if(!empty($_SESSION['nomepesquisa'])){ ?>
                                            <input value="<?php echo($_SESSION['nomepesquisa']); ?>" type="text" 
                                                   class="form-control" name="nomepesquisa" id="nomepesquisa">
                                        <?php } else { ?>
                                            <input value="<?php echo($_SESSION['nomepesquisa']); ?>" type="text" 
                                                   class="form-control" name="nomepesquisa" id="nomepesquisa">
                                        <?php }?>
                                    </div>
                                    <div class="col-md-3 pull-left">
                                        <strong><label for="nomepesquisa">Nº do recibo </label></strong>
                                        <?php if(!empty($_SESSION['nrecibo'])){ ?>
                                            <input value="<?php echo($_SESSION['nrecibo']); ?>" type="text" 
                                                   class="form-control" name="nrecibo" id="nrecibo">
                                        <?php } else { ?>
                                            <input value="<?php echo($_SESSION['nrecibo']); ?>" type="text" 
                                                   class="form-control" name="nrecibo" id="nrecibo">
                                        <?php }?>
                                    </div>
                                    <div class="col-md-3 pull-right">
                                        <strong><label for="favorecido">Favorecido</label></strong>
                                        <select required class="form-control" name="favorecido" id="favorecido">
                                            <option value="0"> Todos </option>
                                            <?php foreach ( $clientess as $item ){ ?>
                                                <option value="<?php echo($item->id); ?>" ><?php echo( utf8_encode( $item->fullname)); ?></option>
                                            <?php }?>
                                        </select>
                                    </div>
                                    <div class="col-md-6 pull-left">
                                        <button type="submit" name="pesquisartransacao" class="btn btn-large btn-outline-success">Pesquisar</button>
                                    </div>

                                </form>

                                <div class="table-responsive">
                                    <table id="example23" class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>N</th>
                                                <th>Data Venci.</th>
                                                <th>Data Pag.</th>
                                                <th>Data Compe.</th>
                                                <th>Nome</th>
                                                <th>Descrição</th>
                                                <th>Favorecido</th>
                                                <th>Empresa</th>
                                                <th>Tipo</th>
                                                <th>Conta C.</th>
                                                <th>Plano de C.</th>
                                                <th>Valor</th>
                                                <th>Situação</th>
                                                <th>#</th>
                                                <th>#</th>
                                                <th>#</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach( $registroCaixa as $item ){ ?>
                                                <tr>
                                                    <td><?php echo( $item->id ); ?></td>
                                                    <td><?php echo( date("d-m-Y", strtotime( $item->datevencimento ) ) ); ?></td>
                                                    <td><?php echo( date("d-m-Y", strtotime( $item->datepagamento  ) ) ); ?></td>
                                                    <td><?php echo( date("d-m-Y", strtotime( $item->datecompetencia ) ) ); ?></td>
                                                    <td><?php echo( $item->nome ); ?></td>
                                                    <td><?php echo( $item->descricao ); ?></td>
                                                    <td><?php echo(htmlentities($item->fornecedor, ENT_QUOTES, 'UTF-8')); ?></td>
                                                    <td><?php echo(htmlentities($item->empresa, ENT_QUOTES, 'UTF-8')); ?></td>
                                                    <td><?php echo(htmlentities($item->tipo, ENT_QUOTES, 'UTF-8')); ?></td>
                                                    <td><?php echo( $item->conta ); ?></td>
                                                    <td><?php echo( $item->plano ); ?></td>
                                                    <td><?php echo( "R$". number_format( $item->valor, 2, ",", "." )  ); ?></td>
                                                    <td>
                                                        <?php echo( $item->situacao ); ?>
                                                    </td>
                                                    <td>
                                                        <form action="#" method="post">
                                                            <input type="hidden" name="nometransacao" value="<?php echo( $item->nome ); ?>" >
                                                            <input type="hidden" name="idtransacao" value="<?php echo( $item->id ); ?>" >
                                                            <button type="submit" name="removertransacao" style="background-color: transparent; border: none;">
                                                                Remover
                                                            </button>
                                                        </form>

                                                    </td>
                                                    <td>
                                                        <form action="./editar-transacao.php" method="post">
                                                            <input type="hidden" name="idtransacao" value="<?php echo( $item->id ); ?>" >
                                                            <button type="submit" name="editartransacao" style="background-color: transparent; border: none;">
                                                                Editar
                                                            </button>
                                                        </form>

                                                    </td>
                                                    <td>
                                                        <form action="./relatorio/recibo-transacao.php" target="_blank" method="post">
                                                            <input type="hidden" name="idtransacao" value="<?php echo( $item->id ); ?>" >
                                                            <button type="submit"  style="background-color: transparent; border: none;">
                                                                Recibo
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php }?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
<script>
    function moeda(a, e, r, t) {
        let n = ""
            , h = j = 0
            , u = tamanho2 = 0
            , l = ajd2 = ""
            , o = window.Event ? t.which : t.keyCode;
        if (13 == o || 8 == o)
            return !0;
        if (n = String.fromCharCode(o),
        -1 == "0123456789".indexOf(n))
            return !1;
        for (u = a.value.length,
                 h = 0; h < u && ("0" == a.value.charAt(h) || a.value.charAt(h) == r); h++)
            ;
        for (l = ""; h < u; h++)
            -1 != "0123456789".indexOf(a.value.charAt(h)) && (l += a.value.charAt(h));
        if (l += n,
        0 == (u = l.length) && (a.value = ""),
        1 == u && (a.value = "0" + r + "0" + l),
        2 == u && (a.value = "0" + r + l),
        u > 2) {
            for (ajd2 = "",
                     j = 0,
                     h = u - 3; h >= 0; h--)
                3 == j && (ajd2 += e,
                    j = 0),
                    ajd2 += l.charAt(h),
                    j++;
            for (a.value = "",
                     tamanho2 = ajd2.length,
                     h = tamanho2 - 1; h >= 0; h--)
                a.value += ajd2.charAt(h);
            a.value += r + l.substr(u - 2, u)
        }
        return !1
    }
</script>
<?php require_once ('footer.php'); ?>
