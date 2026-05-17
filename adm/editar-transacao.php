<?php require_once ('header.php');

if( isset( $_POST['idtransacao'] ) )
{
    $caixa         = $pdo->prepare(
        " select c.id,c.datevencimento,c.idusr, u.firstname, u.lastname, c.datecompetencia, c.datepagamento, c.descricao,idempresa, f.fullname, f.id as forid ,tc.`name` as tipo, cc.`name` as conta, c.nome,
p.`name` as plano, p.id as planoid ,s.`nameinvoice` as situacao, s.id as stid ,c.valor, tc.id as tipoid, cc.id as contaid, idempresa from  `ct_caixa` c left join ct_fornecedor f on f.id = c.idcliente 
left join ct_tipocaixa tc on tc.id = c.idtipo left join ct_currentaccount cc on cc.id = c.idconta left join ct_planaccounts p on p.id = c.idplano left join `ct_usuario` u on u.id = c.idusr
  left join ct_statusinvoice s on s.id = c.idstatus where c.id = :id ");
    $caixa->execute( array(":id" =>  $_POST['idtransacao']) );
}else{
    $caixa         = $pdo->prepare(
        " select c.id,c.datevencimento, c.idusr,u.firstname, u.lastname, c.datecompetencia, c.datepagamento,idempresa, c.descricao, f.fullname, tc.`name` as tipo, cc.`name` as conta,c.nome,
    p.`name` as plano, s.`nameinvoice` as situacao, c.valor from  `ct_caixa` c left join ct_fornecedor f on f.id = c.idcliente left join ct_tipocaixa tc on tc.id = c.idtipo
    left join `ct_usuario` u on u.id = c.idusr left join ct_currentaccount cc on cc.id = c.idconta left join ct_planaccounts p on p.id = c.idplano 
    left join ct_statusinvoice s on s.id = c.idstatus where c.id = :id ");
    $caixa->execute( array(":id" =>  $_GET['idtransacao']) );
}

$cliente       = $pdo->prepare('select * from `ct_fornecedor` ORDER BY `ct_fornecedor`.`fullname` DESC');
$cliente->execute();
$status        = $pdo->prepare('select * from `ct_statusinvoice`');
$status->execute();
$planoDeContas = $pdo->prepare("select * from `ct_planaccounts` order by `name` ");
$planoDeContas->execute();
$tipoCaixa = $pdo->prepare("select * from `ct_tipocaixa` order by `name` ");
$tipoCaixa->execute();
$contaCorrente = $pdo->prepare("select * from `ct_currentaccount` order by `name`");
$contaCorrente->execute();
$empresas = $pdo->prepare('select * from `ct_empresa`');
$empresas->execute();


if( isset( $_POST['updatetransition'] ) )
{
    $valor  = str_replace(".", "", $_POST['valor']);
    $valor1 = str_replace(",", ".", $valor);
    $updateTransition = $pdo->prepare(
        " update `ct_caixa` set `datevencimento` = :vencimento, `datecompetencia` = :competencia, `datepagamento` = :pagamento, `nome` = :nome,
  `descricao` = :descricao, `idcliente` = :cliente, `idtipo` = :tipo, `idconta` = :conta, `idplano` = :plano, `idstatus` = :statuus, `valor` = :valor, `idempresa` = :idempresa
        where id = :id  ");
    $updateTransition->execute(
        array(
            ":vencimento"  => $_POST['datavencimento'],
            ":pagamento"   => $_POST['datapagamento'],
            ":nome"        => $_POST['nome'],
            ":competencia" => $_POST['datacompetencia'],
            ":descricao"   => $_POST['documento'],
            ":cliente"     => $_POST['favorecido'],
            ":tipo"        => $_POST['tipo'],
            ":conta"       => $_POST['contacorrente'],
            ":plano"       => $_POST['planocontas'],
            ":statuus"     => $_POST['status'],
            ":valor"       => $valor1,
            ":idempresa"   => $_POST['empresa'],
            ":id"          => $_POST['idtransacao']
        )
    );
    header('location: editar-transacao?idtransacao='.$_POST['idtransacao']);



    $caixa         = $pdo->prepare(
        " select c.id,c.datevencimento, c.idusr, u.firstname, u.lastname,c.datecompetencia, c.datepagamento, c.descricao, f.fullname, f.id as forid ,tc.`name` as tipo, cc.`name` as conta, c.nome,
p.`name` as plano, p.id as planoid ,s.`nameinvoice` as situacao, s.id as stid ,c.valor, tc.id as tipoid, cc.id as contaid, idempresa from  `ct_caixa` c left join ct_fornecedor f on f.id = c.idcliente 
left join ct_tipocaixa tc on tc.id = c.idtipo left join ct_currentaccount cc on cc.id = c.idconta left join ct_planaccounts p on p.id = c.idplano left join `ct_usuario` u on u.id = c.idusr
left join ct_statusinvoice s on s.id = c.idstatus where c.id = :id ");
    $caixa->execute( array(":id" =>  $_POST['idtransacao']) );

    echo("<div class='alert au-alert-success' role='alertdialog'>Transação atualizada </div>");
}
$registroCaixa = $caixa->fetch(PDO::FETCH_ASSOC);

?>
<style>
    .col-md-6, .col-md-4{
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
                            <span class="au-breadcrumb-span">Você está aqui:</span>
                            <ul class="list-unstyled list-inline au-breadcrumb__list">
                                <li class="list-inline-item active">
                                    <a href="./index">Home</a>
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
    <div class="row">
        <div class="card">
            <div class="card-header">
                <div align="center" style="font-weight: bold;">
                    <h4>Transação - Nº<?php echo($registroCaixa['id']." - Responsável ".$registroCaixa['firstname']." ".$registroCaixa['lastname']); ?></h4>

                </div>
            </div>
            <div class="card-body">
                <div class="col-lg-12">
                    <form action="" method="post">

                        <div class="col-md-4 pull-left">
                            <strong><label for="datavencimento">Data de Vencimento</label></strong>
                            <input type="date" class="form-control" value="<?php echo($registroCaixa['datevencimento']); ?>" name="datavencimento" id="datavencimento">
                            <input type="hidden" class="form-control" value="<?php echo($registroCaixa['datevencimento']); ?>" name="datavencimento2">
                        </div>
                        <div class="col-md-4 pull-left">
                            <strong><label for="datapagamento">Data de Pagamento</label></strong>
                            <input type="date" class="form-control" value="<?php echo($registroCaixa['datepagamento']); ?>" name="datapagamento" id="datapagamento">
                            <input type="hidden" class="form-control" value="<?php echo($registroCaixa['datepagamento']); ?>" name="datapagamento2">
                        </div>
                        <div class="col-md-4 pull-right">
                            <strong><label for="datacompetencia">Data da Competência</label></strong>
                            <input type="date" class="form-control" name="datacompetencia" value="<?php echo($registroCaixa['datecompetencia']); ?>" id="datacompetencia">
                            <input type="hidden" class="form-control" name="datacompetencia2" value="<?php echo($registroCaixa['datecompetencia']); ?>">
                        </div>
                        <div class="col-md-4 pull-left">
                            <strong><label for="nome">Nome</label></strong>
                            <input type="text" name="nome" value="<?php echo($registroCaixa['nome']); ?>" id="nome" class="form-control" >
                            <input type="hidden" name="nome2" value="<?php echo($registroCaixa['nome']); ?>" class="form-control" >
                        </div>
                        <div class="col-md-4 pull-left">
                            <strong><label for="documento">Descrição</label></strong>
                            <input type="text" name="documento" value="<?php echo($registroCaixa['descricao']); ?>" id="documento" class="form-control" >
                            <input type="hidden" name="documento2" value="<?php echo($registroCaixa['descricao']); ?>" class="form-control" >
                            <input type="hidden" name="favorecido2" value="<?php echo($registroCaixa['forid']); ?>" class="form-control" >
                            <input type="hidden" name="tipo2" value="<?php echo($registroCaixa['tipoid']); ?>" class="form-control" >
                            <input type="hidden" name="empresa2" value="<?php echo($registroCaixa['idempresa']); ?>" class="form-control" >
                            <input type="hidden" name="idusr" value="<?php echo($registroCaixa['idusr']); ?>" class="form-control" >
                        </div>
                        <div class="col-md-4 pull-right">
                            <strong><label for="favorecido">Favorecido</label></strong>
                            <select class="form-control" name="favorecido" id="favorecido">
                                <?php while ( $dadosCliente = $cliente->fetch( PDO::FETCH_ASSOC ) ){ ?>
                                    <?php if( $registroCaixa['fullname'] ==  $dadosCliente['fullname']  ){ ?>
                                        <option selected value="<?php echo($dadosCliente['id']); ?>" ><?php echo( utf8_encode( $dadosCliente['fullname'])); ?></option>
                                    <?php } else{?>
                                        <option value="<?php echo($dadosCliente['id']); ?>" ><?php echo( utf8_encode( $dadosCliente['fullname'])); ?></option>
                                    <?php }?>

                                <?php }?>
                            </select>
                        </div>
                        <div class="col-md-4 pull-left">
                            <strong><label for="tipo">Empresa</label></strong>
                            <select required class="form-control" name="empresa" id="empresa">
                                <?php while ( $dadosEmpresa = $empresas->fetch( PDO::FETCH_ASSOC ) ){ ?>
                                    <?php if($registroCaixa['idempresa'] == $dadosEmpresa['id']){ ?>
                                        <option value="<?php echo($dadosEmpresa['id']); ?>" selected ><?php echo( utf8_encode( $dadosEmpresa['fullname'])); ?></option>

                                    <?php } else { ?>
                                        <option value="<?php echo($dadosEmpresa['id']); ?>" ><?php echo( utf8_encode( $dadosEmpresa['fullname'])); ?></option>

                                    <?php }?>
                                <?php }?>
                            </select>
                        </div>
                        <div class="col-md-4 pull-left">
                            <strong><label for="tipo">Tipo</label></strong>
                            <select class="form-control" name="tipo" id="tipo">
                                <?php while ( $dadosTipoCaixa = $tipoCaixa->fetch( PDO::FETCH_ASSOC ) ){ ?>
                                    <?php if( utf8_encode($registroCaixa['tipo']) == utf8_encode( $dadosTipoCaixa['name']) ){ ?>
                                        <option selected value="<?php echo($dadosTipoCaixa['id']); ?>" ><?php echo( utf8_encode( $dadosTipoCaixa['name'])); ?></option>
                                    <?php } else{ ?>
                                        <option  value="<?php echo($dadosTipoCaixa['id']); ?>" ><?php echo( utf8_encode( $dadosTipoCaixa['name'])); ?></option>
                                    <?php }?>

                                <?php }?>
                            </select>
                        </div>
                        <div class="col-md-4 pull-right">
                            <strong><label for="contacorrente">Conta Corrente</label></strong>
                            <select class="form-control" name="contacorrente" id="contacorrente">
                                <?php while ( $dadosConta = $contaCorrente->fetch( PDO::FETCH_ASSOC ) ){ ?>
                                    <?php if( utf8_encode($registroCaixa['conta']) == utf8_encode( $dadosConta['name']) ){ ?>
                                        <option selected value="<?php echo($dadosConta['id']); ?>" ><?php echo( utf8_encode( $dadosConta['name'] ) ); ?></option>
                                    <?php } else{ ?>
                                        <option value="<?php echo($dadosConta['id']); ?>" ><?php echo( utf8_encode( $dadosConta['name'] ) ); ?></option>
                                    <?php }?>

                                <?php }?>
                            </select>
                            <input type="hidden" name="contacorrente2" value="<?php echo($registroCaixa['contaid']); ?>" class="form-control" >
                        </div>
                        <div class="col-md-4 pull-left">
                            <strong><label for="planocontas">Plano de Contas</label></strong>
                            <select class="form-control" name="planocontas" id="planocontas">
                                <?php while ( $dadosPlano = $planoDeContas->fetch( PDO::FETCH_ASSOC ) ){ ?>

                                    <?php if( utf8_encode($registroCaixa['plano']) == utf8_encode( $dadosPlano['name']) ){ ?>
                                        <option selected value="<?php echo($dadosPlano['id']); ?>" ><?php echo( utf8_encode( $dadosPlano['name'] ) ); ?></option>
                                    <?php } else{ ?>
                                        <option value="<?php echo($dadosPlano['id']); ?>" ><?php echo( utf8_encode( $dadosPlano['name'] ) ); ?></option>
                                    <?php }?>

                                <?php }?>
                            </select>
                            <input type="hidden" name="planocontas2" value="<?php echo($registroCaixa['planoid']); ?>" class="form-control" >
                        </div>
                        <div class="col-md-4 pull-left">
                            <strong><label for="valor">Valor da Transação</label></strong>
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="basic-addon1">R$</span>
                                </div>
                                <input value="<?php echo( number_format( $registroCaixa['valor'],2, ",", "." ) ); ?>"
                                       type="text" class="form-control" name="valor" id="valor" onKeyPress="return(moeda(this,'.',',',event))">
                            </div>
                            <input type="hidden" name="valor2" value="<?php echo(number_format($registroCaixa['valor'], 2 , ",", ".")); ?>"
                                   class="form-control" >
                        </div>
                        <div class="col-md-4 pull-right">
                            <strong><label for="status">Status</label></strong>
                            <select class="form-control" name="status" id="status" required>
                                <?php while ( $dadosStatus = $status->fetch( PDO::FETCH_ASSOC ) ){ ?>

                                    <?php if( utf8_encode($registroCaixa['situacao']) == utf8_encode( $dadosStatus['nameinvoice']) ){ ?>
                                        <option selected value="<?php echo($dadosStatus['id']); ?>" ><?php echo($dadosStatus['nameinvoice']); ?></option>
                                    <?php } else{ ?>
                                        <option value="<?php echo($dadosStatus['id']); ?>" ><?php echo($dadosStatus['nameinvoice']); ?></option>
                                    <?php }?>


                                <?php }?>
                            </select>
                            <input type="hidden" name="status2" value="<?php echo($registroCaixa['stid']); ?>" class="form-control" >
                        </div>
                        <input type="hidden" name="idtransacao" value="<?php echo( $registroCaixa['id'] ); ?>" >
                        <div class="col-md-4 pull-right" style="margin-top: 17px;">
                            <button class="btn btn-outline-success btn-block btn-large" name="updatetransition" type="submit">
                                Atualizar transação
                            </button>
                        </div>
                    </form>
                    <div class="col-md-4 pull-left">
                            <a href="./caixa" class="btn btn-outline-warning btn-block btn-large">
                                Voltar
                            </a>
                        </form>

                    </div>
                    <div class="col-md-4 pull-left">
                        <form action="./relatorio/recibo-transacao.php" target="_blank" method="post">
                            <input type="hidden" name="idtransacao" value="<?php echo( $registroCaixa['id'] ); ?>" >
                            <button type="submit" class="btn btn-outline-success btn-block btn-large" >
                                Recibo
                            </button>
                        </form>
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
