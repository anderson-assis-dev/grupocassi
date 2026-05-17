<?php require_once ('header.php');

if( isset( $_POST['editar'] ) )
{
  $idFatura      = $_POST['idfatura'];
  $minhasFaturas = $pdo->prepare("select * from `ct_fatura` where `id` = :id ");
  $minhasFaturas->execute( array(":id" => $idFatura) );
  $dadosFatura   = $minhasFaturas->fetch(PDO::FETCH_ASSOC);


}
if( isset( $_POST['updatefatura'] ) )
{
  $idFatura      = $_POST['idfatura'];
  $updateFatura  = $pdo->prepare(
          'update `ct_fatura` set `tarifa` = :tarifa , `credito` = :credito, `dateinput` = :dateinput ,
                      `situacao` = :situacao, `dateoutput` = :dateoutput where `id` = :id  ');
  $updateFatura->execute(
          array(
                  ":tarifa"     => str_replace(",", ".", str_replace(".", "",$_POST['total'])),
                  ":credito"    => str_replace(",", ".", str_replace(".", "", $_POST['credito'])),
                  ":dateinput"  => $_POST['periodoinicial'],
                  ":situacao"   => $_POST['situacao'],
                  ":dateoutput" => $_POST['periodofinal'],
                  ":id"         => $_POST['idfatura']
          )
  );
  if( $_POST['situacao'] == 0 )
  {
      $updateReservas = $pdo->prepare(
              "update `ct_reserva` set `idstatusinvoice` = 1 where `idcliente` = :cliente and `dateinput` >= :inicio and `dateinput` <= :fim ");
      $updateReservas->execute( array(":cliente" => $_POST['idcliente'], ":inicio" => $_POST['periodoinicial'], ":fim" => $_POST['periodofinal'] ) );
  }elseif($_POST['situacao'] == 1)
  {
      $updateReservas = $pdo->prepare(
          "update `ct_reserva` set `idstatusinvoice` = :returnstatus where `idcliente` = :cliente and `dateinput` >= :inicio and `dateinput` <= :fim ");
      $updateReservas->execute(
              array(
                  ":cliente"      => $_POST['idcliente'],
                  ":inicio"       => $_POST['periodoinicial'],
                  ":fim"          => $_POST['periodofinal'],
                  ":returnstatus" => $_POST['status']
              ) );
  }

  $minhasFaturas = $pdo->prepare("select * from `ct_fatura` where `id` = :id ");
  $minhasFaturas->execute( array(":id" => $idFatura) );
  echo("<div class='alert alert-success' role='alert'>As informações da fatura foram atualizadas</div>");


}
elseif (isset($_POST['inserirpagamento']))
{
    $idFatura       = $_POST['idfatura'];
    $valor  = str_replace(".", "", $_POST['valorecebido']);
    $valor1 = str_replace(",", ".", $valor);
    $datapagamento = $_POST['datapagamento'];
    $descricao = $_POST['descricaopagamento'];


    $novo_pagamento = $pdo->prepare('insert into `ct_faturadesc` values (DEFAULT, :valor, :descricao, :datapagamento, :idfatura)');
    $novo_pagamento->execute(array(":valor" => $valor1, ":descricao" => $descricao, ":datapagamento" => $datapagamento, ":idfatura" => $idFatura));
    echo("<div class='alert alert-success' role='alert'>Crédito inserido com sucesso</div>");

    $minhasFaturas = $pdo->prepare("select * from `ct_fatura` where `id` = :id ");
    $minhasFaturas->execute( array(":id" => $idFatura) );
    $dadosFatura   = $minhasFaturas->fetch(PDO::FETCH_ASSOC);

    $minhasFaturasDescricao = $pdo->prepare("select * from `ct_faturadesc` where `id_fatura` = :id ");
    $minhasFaturasDescricao->execute( array(":id" => $idFatura) );
    $dadosFaturaDescricao   = $minhasFaturasDescricao->fetchAll(PDO::FETCH_CLASS);

}
elseif (isset($_POST['atualizarpagamento']))
{
    $idcredito       = $_POST['idcredito'];
    $valor  = str_replace(".", "", $_POST['valorecebido']);
    $valor1 = str_replace(",", ".", $valor);
    $datapagamento = $_POST['datapagamento'];
    $descricao = $_POST['descricaopagamento'];


    $novo_pagamento = $pdo->prepare('update `ct_faturadesc` set `valor` = :valor, `descricao` = :descricao, `datapagamento` = :datapagamento where id = :id ');
    $novo_pagamento->execute(array(":valor" => $valor1, ":descricao" => $descricao, ":datapagamento" => $datapagamento, ":id" => $idcredito));
    echo("<div class='alert alert-success' role='alert'>Crédito atualizado com sucesso</div>");
    $idFatura      = $_POST['idfatura'];
    $minhasFaturas = $pdo->prepare("select * from `ct_fatura` where `id` = :id ");
    $minhasFaturas->execute( array(":id" => $idFatura) );
    $dadosFatura   = $minhasFaturas->fetch(PDO::FETCH_ASSOC);

    $minhasFaturasDescricao = $pdo->prepare("select * from `ct_faturadesc` where `id_fatura` = :id ");
    $minhasFaturasDescricao->execute( array(":id" => $idFatura) );
    $dadosFaturaDescricao   = $minhasFaturasDescricao->fetchAll(PDO::FETCH_CLASS);

}
elseif (isset($_POST['excluirpagamento']))
{
    $idcredito       = $_POST['idcredito'];
    $valor  = str_replace(".", "", $_POST['valorecebido']);
    $valor1 = str_replace(",", ".", $valor);
    $descricao = $_POST['descricaopagamento'];


    $novo_pagamento = $pdo->prepare('delete from `ct_faturadesc` where id = :id ');
    $novo_pagamento->execute(array( ":id" => $idcredito));
    echo("<div class='alert alert-danger' role='alert'>Crédito removido</div>");
    $idFatura      = $_POST['idfatura'];
    $minhasFaturas = $pdo->prepare("select * from `ct_fatura` where `id` = :id ");
    $minhasFaturas->execute( array(":id" => $idFatura) );
    $dadosFatura   = $minhasFaturas->fetch(PDO::FETCH_ASSOC);

    $minhasFaturasDescricao = $pdo->prepare("select * from `ct_faturadesc` where `id_fatura` = :id ");
    $minhasFaturasDescricao->execute( array(":id" => $idFatura) );
    $dadosFaturaDescricao   = $minhasFaturasDescricao->fetchAll(PDO::FETCH_CLASS);

}

$idFatura      = $_POST['idfatura'];
$minhasFaturas = $pdo->prepare("select * from `ct_fatura` where `id` = :id ");
$minhasFaturas->execute( array(":id" => $idFatura) );
$dadosFatura   = $minhasFaturas->fetch(PDO::FETCH_ASSOC);

$minhasFaturasDescricao = $pdo->prepare("select * from `ct_faturadesc` where `id_fatura` = :id ");
$minhasFaturasDescricao->execute( array(":id" => $idFatura) );
$dadosFaturaDescricao   = $minhasFaturasDescricao->fetchAll(PDO::FETCH_CLASS);

?>
<style>
  .col-lg-4,.col-lg-6,.col-lg-12{margin-bottom: 20px;}
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
                                <li class="list-inline-item">Editar Faura</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="">
        <div class="">
            <div class="col-lg-12">
                <div class="accordion" id="accordionExample">
                    <div class="card">
                        <div class="card-header" id="headingOne">
                            <h2 class="mb-0">
                                <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                    Dados da fatura
                                </button>
                            </h2>
                        </div>

                        <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionExample">
                            <div class="card-body">
                                <form action="" method="post">
                                    <div class="col-lg-6 pull-left ">
                                        <label for="total">Total</label>
                                        <div class="input-group flex-nowrap">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text" id="addon-wrapping">R$</span>
                                            </div>
                                            <input type="text" name="total" id="total"
                                                   value="<?php echo( number_format(str_replace("", "", str_replace(",", "",$dadosFatura['tarifa'])), 2, ",", ".") ); ?>"
                                                   class="form-control">
                                        </div>

                                    </div>
                                    <div class="col-lg-6 pull-right">
                                        <label for="credito">Crédito</label>
                                        <div class="input-group flex-nowrap">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text" id="addon-wrapping">R$</span>
                                            </div>
                                            <input type="text"
                                                   value="<?php echo( number_format(str_replace("", "", str_replace(",", "",$dadosFatura['credito'])), 2, ",", ".") ); ?>"
                                                   name="credito" id="credito" class="form-control">
                                        </div>

                                    </div>

                                    <div class="col-lg-4 pull-left">
                                        <label for="periodoinicial">Periodo Inicial</label>
                                        <div class="input-group flex-nowrap">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text" id="addon-wrapping"><i class="fa fa-calendar"></i></span>
                                            </div>
                                            <input type="date" value="<?php echo( $dadosFatura['dateinput'] ); ?>" name="periodoinicial" id="periodoinicial"
                                                   class="form-control">
                                        </div>

                                    </div>
                                    <div class="col-lg-4 pull-left">
                                        <label for="situacao">Situação</label>
                                        <select name="situacao" class="form-control">
                                            <?php if( $dadosFatura['situacao'] == 0 ){ ?>
                                                <option selected value="0">Inativo</option>
                                                <option value="1">Ativo</option>
                                            <?php } else{ ?>
                                                <option  value="0">Inativo</option>
                                                <option selected value="1">Ativo</option>
                                            <?php }?>

                                        </select>
                                    </div>
                                    <div class="col-lg-4 pull-right">
                                        <label for="periodofinal">Periodo Final</label>
                                        <div class="input-group flex-nowrap">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text" id="addon-wrapping"><i class="fa fa-calendar"></i></span>
                                            </div>
                                            <input type="date" value="<?php echo( $dadosFatura['dateoutput'] ); ?>" name="periodofinal" id="periodofinal" class="form-control">
                                        </div>

                                    </div>

                                    <div class="col-lg-4 pull-right">
                                        <input type="hidden" name="idfatura" value="<?php echo( $dadosFatura['id'] ); ?>">
                                        <input type="hidden" name="idcliente" value="<?php echo( $dadosFatura['idcliente'] ); ?>">
                                        <input type="hidden" name="status" value="<?php echo( $dadosFatura['status'] ); ?>">
                                        <button class="btn btn-outline-success btn-lg btn-block" type="submit" name="updatefatura">
                                            Atualizar Fatura
                                        </button>
                                    </div>
                                    <div class="col-lg-4 pull-left">
                                        <a href="./inicio-da-fatura#collapseTwo" class="btn btn-outline-warning btn-lg btn-block">
                                            Voltar
                                        </a>
                                    </div>

                                </form>
                                <div class="col-lg-4 pull-right">
                                    <form action="./relatorio/pdf-relatorio-cliente-reserva" method="post" target="_blank">
                                        <input type="hidden" name="idfatura" value="<?php echo( $dadosFatura['id'] ); ?>">
                                        <input type="hidden" value="<?php echo( $dadosFatura['dateoutput'] ); ?>" name="periodofinal" id="periodofinal">
                                        <input type="hidden" name="cliente" value="<?php echo( $dadosFatura['idcliente'] ); ?>">
                                        <input type="hidden" value="<?php echo( $dadosFatura['dateinput'] ); ?>" name="periodoinicial" id="periodoinicial">
                                        <input type="hidden" name="status" value="<?php echo( $dadosFatura['status'] ); ?>">
                                        <button style="margin-top: -66px;" class="btn btn-outline-primary btn-lg btn-block" type="submit" name="gerar">
                                            Imprimir Fatura
                                        </button>

                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header" id="headingTwo">
                            <h2 class="mb-0">
                                <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseTwo"
                                        aria-expanded="false" aria-controls="collapseTwo">
                                    Adicionar Créditos
                                </button>
                            </h2>
                        </div>
                        <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionExample">
                            <div class="card-body">
                                <form action="" method="post">
                                    <div class="col-lg-4 pull-left ">
                                        <label for="valorecebido">Valor Recebido</label>
                                        <div class="input-group flex-nowrap">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text" id="addon-wrapping">R$</span>
                                            </div>
                                            <input type="text" name="valorecebido" id="valorecebido" onKeyPress="return(moeda(this,'.',',',event))" class="form-control">
                                        </div>

                                    </div>
                                    <div class="col-lg-4 pull-left">
                                        <label for="datapagamento">Data do pagamento</label>
                                        <div class="input-group flex-nowrap">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text" id="addon-wrapping"><i class="fa fa-calendar"></i></span>
                                            </div>
                                            <input type="date" name="datapagamento" id="datapagamento" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-lg-4 pull-right">
                                        <label for="descricaopagamento">Descrição do pagamento</label>
                                        <input type="text" name="descricaopagamento" id="descricaopagamento" class="form-control">
                                    </div>
                                    <div class="col-lg-6 pull-left">
                                        <input type="hidden" name="idfatura" value="<?php echo( $dadosFatura['id'] ); ?>">
                                        <button class="btn btn-outline-success btn-lg" name="inserirpagamento" type="submit">Inserir Crédito</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header" id="headingThree">
                            <h2 class="mb-0">
                                <button class="btn btn-link collapsed" type="button" data-toggle="collapse"
                                        data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                    Créditos Adicionados
                                </button>
                            </h2>
                        </div>
                        <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordionExample">
                            <div class="card-body">
                                <?php if( count($dadosFaturaDescricao) > 0 ){ ?>
                                    <?php $totalcredito = 0; foreach ( $dadosFaturaDescricao as $item ){ $totalcredito += $item->valor ?>
                                        <form action="" method="post">
                                            <div class="col-lg-4 pull-left ">
                                                <label for="valorecebido">Valor Recebido</label>
                                                <div class="input-group flex-nowrap">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text" id="addon-wrapping">R$</span>
                                                    </div>
                                                    <input type="text" value="<?php echo(number_format($item->valor, 2 ,",", ".")) ?>"
                                                           name="valorecebido" id="valorecebido" onKeyPress="return(moeda(this,'.',',',event))" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-lg-4 pull-left">
                                                <label for="datapagamento">Data do pagamento</label>
                                                <div class="input-group flex-nowrap">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text" id="addon-wrapping"><i class="fa fa-calendar"></i></span>
                                                    </div>
                                                    <input type="date" value="<?php echo( $item->datapagamento ) ?>"
                                                           name="datapagamento" id="datapagamento" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-lg-4 pull-right">
                                                <label for="descricaopagamento">Descrição do pagamento</label>
                                                <input type="text" name="descricaopagamento" value="<?php echo($item->descricao) ?>"
                                                       id="descricaopagamento" class="form-control">
                                            </div>
                                            <input type="hidden" value="<?php echo($item->id) ?>" name="idcredito">
                                            <input type="hidden" name="idfatura" value="<?php echo( $dadosFatura['id'] ); ?>">

                                            <div class="col-lg-6 pull-left">
                                                <button class="btn btn-outline-success btn-lg" name="atualizarpagamento" type="submit">Atualizar Crédito</button>
                                            </div>
                                            <div class="col-lg-6 pull-right">
                                                <button class="btn btn-outline-danger btn-lg" name="excluirpagamento" type="submit">Excluir Crédito</button>
                                            </div>
                                        </form>

                                    <?php }?>
                                    <p class="pull-right" style="font-weight: bold; margin-bottom: 20px;">
                                        <?php echo("Valor total do créditos R$ ".number_format($totalcredito, 2, ",", ".")); ?></p>

                                <?php } else { ?>
                                    <div role="alert" class="alert alert-warning">Não há créditos para essa fatura.</div>
                                <?php }?>
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
