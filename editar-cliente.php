<?php require_once ('header.php');
$todosPaises = $pdo->prepare('select * from `ct_pais` ');
$todosPaises->execute();

$todosEstados = $pdo->prepare('select * from `ct_estado` ');
$todosEstados->execute();

$todosCidade = $pdo->prepare('select * from `ct_cidade` ');
$todosCidade->execute();

$todasTarifa = $pdo->prepare('SELECT * FROM `ct_tarifa`  ');
$todasTarifa->execute();

$todosServicos = $pdo->prepare("select * from `ct_servico` order by fullname");
$todosServicos->execute();
$registroServicos = $todosServicos->fetchAll(PDO::FETCH_CLASS);
if( isset( $_POST['cliente'] ) )
{
    $todoCliente  = $pdo->prepare('select * from `ct_cliente` where id = :id ');
    $todoCliente->execute( array(":id" => $_POST['cliente']) );
    $dadosCliente = $todoCliente->fetch( PDO::FETCH_ASSOC );
  $buscarNet = $pdo->prepare(
          'select cs.id,c.namefantazia, s.fullname, cs.valuenet from `ct_cliente` c right join `ct_clientservice` cs on c.id = cs.idclient
                    left join `ct_servico` s on cs.idservice = s.id where c.id = :id order by s.fullname');
  $buscarNet->execute(array(":id" => $_POST['cliente']));
  $registro  = $buscarNet->fetchAll(PDO::FETCH_CLASS);




}else{

    $todoCliente  = $pdo->prepare('select * from `ct_cliente` where id = :id ');
    $todoCliente->execute( array(":id" => $_GET['cliente']) );
    $dadosCliente = $todoCliente->fetch( PDO::FETCH_ASSOC );


    $buscarNet = $pdo->prepare(
        'select cs.id,c.namefantazia, s.fullname, cs.valuenet from `ct_cliente` c right join `ct_clientservice` cs on c.id = cs.idclient
                    left join `ct_servico` s on cs.idservice = s.id where c.id = :id order by s.fullname');
    $buscarNet->execute(array(":id" => $_GET['cliente']));
    $registro  = $buscarNet->fetchAll(PDO::FETCH_CLASS);


}
if( isset( $_POST['clientupdate'] ) )
{

    $valor  = str_replace(".", "", $_POST['limitereserva']);
    $valor1 = str_replace(",", ".", $valor);
  $updateClient = $pdo->prepare(
    "update `ct_cliente` set `cnpj` = :cnpj, `namefantazia` = :namef, `corporatename` = :rs, `type` = :tipo, `address` = :ende, `datefundation` = :df,
    `stateenrollment` = :re, `municipalregistration` = :mr,
    `idcountry` = :pais, `idstate` = :estado, `idcity` = :cidade, `cep` = :cep, `tel01` = :tel01, `tel02` = :tel02, `phone` = :phone, `email` = :email,
    `register` = :embratur, `periodoinicial` = :inicio, `periodofinal` = :final, `limite` = :limite, `observacao` = :obs where `id` = :id ");
    $updateClient->execute(
      array(
          ":cnpj"       => $_POST['cnpj'],
          ":namef"      => $_POST['nomefantazia'],
          ":rs"         => $_POST['razaosocial'],
          ":tipo"       => $_POST['tipo'],
          ":ende"       => $_POST['endereco'],
          ":df"         => $_POST['datanf'],
          ":pais"       => $_POST['pais'],
          ":estado"     => $_POST['estado'],
          ":cidade"     => $_POST['cidade'],
          ":cep"        => $_POST['cep'],
          ":tel01"      => $_POST['telefone1'],
          ":tel02"      => $_POST['telefone2'],
          ":phone"      => $_POST['celular'],
          ":email"      => $_POST['email'],
          ":re"         => $_POST['inscricaoes'],
          ":mr"         => $_POST['inscricaomu'],
          ":embratur"   => $_POST['registroembratur'],
          ":inicio"     => $_POST['periodoinicial'],
          ":final"      => $_POST['periodofinal'],
          ":limite"     => $valor1,
          ":obs"        => $_POST['observacao'],
          ":id"         => $_POST['cliente']
       )
     );
    
     $todoCliente  = $pdo->prepare('select * from `ct_cliente` where id = :id ');
     $todoCliente->execute( array(":id" => $_POST['cliente']) );
     $dadosCliente = $todoCliente->fetch( PDO::FETCH_ASSOC );

     $todosPaises = $pdo->prepare('select * from `ct_pais` ');
     $todosPaises->execute();

     $todosEstados = $pdo->prepare('select * from `ct_estado` ');
     $todosEstados->execute();

     $todosCidade = $pdo->prepare('select * from `ct_cidade` ');
     $todosCidade->execute();

     $todasTarifa = $pdo->prepare('SELECT * FROM `ct_tarifa`  ');
     $todasTarifa->execute();

    $buscarNet = $pdo->prepare(
        'select cs.id,c.namefantazia, s.fullname, cs.valuenet from `ct_cliente` c right join `ct_clientservice` cs on c.id = cs.idclient
                    left join `ct_servico` s on cs.idservice = s.id where c.id = :id order by s.fullname');
    $buscarNet->execute(array(":id" => $_POST['cliente']));
    $registro  = $buscarNet->fetchAll(PDO::FETCH_CLASS);

     echo("<div class='alert alert-success' role='alert'>As informações do cliente ".$_POST['razaosocial']." forma atualizadas "."</div>");

}

if( isset( $_POST['salvar'] ) )
{
    $updateNet = $pdo->prepare("update `ct_clientservice` set `valuenet` = :valor where id = :id ");
    $updateNet->execute(array(":valor" => $_POST['valor'], ":id" => $_POST['idnet']));

    $todoCliente  = $pdo->prepare('select * from `ct_cliente` where id = :id ');
    $todoCliente->execute( array(":id" => $_POST['cliente']) );
    $dadosCliente = $todoCliente->fetch( PDO::FETCH_ASSOC );

    $todosPaises = $pdo->prepare('select * from `ct_pais` ');
    $todosPaises->execute();

    $todosEstados = $pdo->prepare('select * from `ct_estado` ');
    $todosEstados->execute();

    $todosCidade = $pdo->prepare('select * from `ct_cidade` ');
    $todosCidade->execute();

    $todasTarifa = $pdo->prepare('SELECT * FROM `ct_tarifa`  ');
    $todasTarifa->execute();

    $buscarNet = $pdo->prepare(
        'select cs.id,c.namefantazia, s.fullname, cs.valuenet from `ct_cliente` c right join `ct_clientservice` cs on c.id = cs.idclient
                    left join `ct_servico` s on cs.idservice = s.id where c.id = :id order by s.fullname');
    $buscarNet->execute(array(":id" => $_POST['cliente']));
    $registro  = $buscarNet->fetchAll(PDO::FETCH_CLASS);
    echo("<div class='alert alert-success' role='alert'>Valor Atualizado "."</div>");
}
if( isset($_POST['salvartarifa']) )
{
    $idservico = $_POST['idservice'];
    $valor     = $_POST['valor'];
    $cliente   = $_POST['cliente'];

    $salvarTarifa = $pdo->prepare("insert into `ct_clientservice` values (default, :cliente, :servico, :valor)");
    $salvarTarifa->execute(array(':cliente' => $cliente, ":servico" => $idservico, ":valor" => $valor));

    $buscarNet = $pdo->prepare(
        'select cs.id,c.namefantazia, s.fullname, cs.valuenet from `ct_cliente` c right join `ct_clientservice` cs on c.id = cs.idclient
                    left join `ct_servico` s on cs.idservice = s.id where c.id = :id order by s.fullname');
    $buscarNet->execute(array(":id" => $_POST['cliente']));
    $registro  = $buscarNet->fetchAll(PDO::FETCH_CLASS);

    echo("<div class='alert alert-success' role='alert'>Tarifário salvo</div>");
}
?>
<style>
  .col-lg-4,.col-lg-6,.col-lg-12{margin-bottom: 20px;}
  #snackbar {
      visibility: hidden;
      min-width: 250px;
      margin-left: -125px;
      background-color: #333;
      color: #fff;
      text-align: center;
      border-radius: 2px;
      padding: 16px;
      position: fixed;
      z-index: 1;
      left: 50%;
      bottom: 30px;
      font-size: 17px;
  }

  #snackbar.show {
      visibility: visible;
      -webkit-animation: fadein 0.5s, fadeout 0.5s 2.5s;
      animation: fadein 0.5s, fadeout 0.5s 2.5s;
  }

  @-webkit-keyframes fadein {
      from {bottom: 0; opacity: 0;}
      to {bottom: 30px; opacity: 1;}
  }

  @keyframes fadein {
      from {bottom: 0; opacity: 0;}
      to {bottom: 30px; opacity: 1;}
  }

  @-webkit-keyframes fadeout {
      from {bottom: 30px; opacity: 1;}
      to {bottom: 0; opacity: 0;}
  }

  @keyframes fadeout {
      from {bottom: 30px; opacity: 1;}
      to {bottom: 0; opacity: 0;}
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
                                <li class="list-inline-item">Cliente/Fornecedor: Cadastro</li>
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
                        <?php if( isset($_GET['cliente']) ){ ?>
                            <?php echo("<div class='alert alert-success pull-right' role='alert' style='width: 50%;'>Cliente Cadastrado</div>"); ?>
                        <?php }?>
                        <div class="card-header" id="headingOne">
                            <h2 class="mb-0">
                                <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                    Informações para cadastro
                                </button>
                            </h2>
                        </div>

                        <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionExample">
                            <div class="card-body">
                                <form action="" method="post">
                                    <div class="col-lg-12">
                                        <label for="cnpj">CPF / CNPJ / Passaporte </label>
                                        <input type="text" name="cnpj" id="cnpj" value="<?php echo( $dadosCliente['cnpj'] ); ?>" class="form-control">
                                    </div>
                                    <div class="col-lg-6 pull-left">
                                        <label for="nomefantazia">Nome Fantazia</label>
                                        <input type="text" value="<?php echo( $dadosCliente['namefantazia'] ); ?>" name="nomefantazia" id="nomefantazia" class="form-control">
                                    </div>

                                    <div class="col-lg-6 pull-right">
                                        <label for="razaosocial">Razão Social</label>
                                        <input type="text" value="<?php echo( $dadosCliente['corporatename'] ); ?>" name="razaosocial" id="razaosocial"  class="form-control">
                                    </div>
                                    <!-- -->
                                    <div class="col-lg-4 pull-left">
                                        <label for="tipo">Tipo</label>
                                        <select name="tipo" id="tipo" class="form-control">
                                            <option value="<?php echo( utf8_decode ($dadosCliente['type'] ) ); ?>" selected>
                                                <?php echo( utf8_decode ($dadosCliente['type'] )); ?>
                                            </option>
                                            <option value="Pessoa Fisíca"> Pessoa Fisíca </option>
                                            <option value="Pessoa Juridica"> Pessoa Juridica </option>
                                            <option value="Estrangeiro(a)"> Estrangeiro(a) </option>
                                        </select>
                                    </div>
                                    <div class="col-lg-4 pull-left">
                                        <label for="endereco">Endereço </label>
                                        <input type="text" name="endereco" value="<?php echo( $dadosCliente['address']  ); ?>" id="endereco" class="form-control">
                                    </div>
                                    <div class="col-lg-4 pull-right">
                                        <label for="datanf">Data (Nascimento/Fundação)</label>
                                        <input type="date" value="<?php echo($dadosCliente['datefundation'] ); ?>" name="datanf" id="datanf" class="form-control">
                                    </div>
                                    <!-- -->
                                    <div class="col-lg-6 pull-left">
                                        <label for="pais">Pais</label>
                                        <select class="form-control" name="pais" id="pais">
                                            <?php  while ( $country = $todosPaises->fetch( PDO::FETCH_ASSOC ) ){ ?>
                                                <?php if( $dadosCliente['idcountry'] == $country['id'] ) { ?>
                                                    <option selected value="<?php echo( $country['id'] ); ?>"><?php echo( utf8_encode( $country['name'] ) ); ?></option>
                                                <?php } else{?>
                                                    <option value="<?php echo( $country['id'] ); ?>"><?php echo( utf8_encode( $country['name'] ) ); ?></option>
                                                <?php }?>
                                            <?php }?>
                                        </select>
                                    </div>

                                    <div class="col-lg-6 pull-right">
                                        <label for="estado">Estado</label>
                                        <select class="form-control" name="estado" id="estado">
                                            <?php  while ( $state = $todosEstados->fetch( PDO::FETCH_ASSOC ) ){ ?>
                                                <?php if( $dadosCliente['idstate'] == $state['id']){ ?>
                                                    <option selected value="<?php echo( $state['id'] ); ?>"><?php echo(utf8_encode(   $state['name'])  ); ?></option>
                                                <?php } else{?>
                                                    <option value="<?php echo( $state['id'] ); ?>"><?php echo( utf8_encode(  $state['name'] ) ); ?></option>
                                                <?php }?>
                                            <?php }?>
                                        </select>
                                    </div>
                                    <!-- -->
                                    <div class="col-lg-6 pull-left">
                                        <label for="cidade">Cidade</label>
                                        <select class="form-control" name="cidade" id="cidade">
                                            <?php  while ( $city = $todosCidade->fetch( PDO::FETCH_ASSOC ) ){ ?>
                                                <?php if( $dadosCliente['idcity'] ==  $city['id'] ){ ?>
                                                    <option selected value="<?php echo( $city['id'] ); ?>"><?php echo( utf8_encode($city['name']) ); ?></option>
                                                <?php } else{ ?>
                                                    <option value="<?php echo( $city['id'] ); ?>"><?php echo( utf8_encode($city['name']) ); ?></option>
                                                <?php }?>
                                            <?php }?>
                                        </select>
                                    </div>
                                    <div class="col-lg-6 pull-right">
                                        <label for="cep">CEP:</label>
                                        <input type="text" value="<?php echo($dadosCliente['cep'] ); ?>" name="cep" id="cep" class="form-control" >
                                    </div>
                                    <!-- -->
                                    <div class="col-lg-4 pull-left">
                                        <label for="telefone1">Telefone 01</label>
                                        <input type="text" value="<?php echo($dadosCliente['tel01'] ); ?>" name="telefone1" id="telefone1" class="form-control" >
                                    </div>
                                    <div class="col-lg-4 pull-left">
                                        <label for="telefone2">Telefone 02</label>
                                        <input type="text" value="<?php echo($dadosCliente['tel02'] ); ?>" name="telefone2" id="telefone2" class="form-control">
                                    </div>
                                    <div class="col-lg-4 pull-right">
                                        <label for="celular">Celular</label>
                                        <input type="text" name="celular" id="celular" class="form-control" value="<?php echo($dadosCliente['phone'] ); ?>" >
                                    </div>


                                    <!-- -->
                                    <div class="col-lg-3 pull-left">
                                        <label for="email">E-mail</label>
                                        <input type="text" value="<?php echo($dadosCliente['email'] ); ?>" name="email" id="email" class="form-control" >
                                    </div>
                                    <div class="col-lg-3 pull-left">
                                        <label for="inscricaoes">Inscrição Estadual</label>
                                        <input type="number" name="inscricaoes" value="0" class="form-control" value="<?php echo($dadosCliente['stateenrollment'] ); ?>">
                                    </div>
                                    <div class="col-lg-3 pull-left">
                                        <label for="inscricaomu">Inscrição Municipal</label>
                                        <input type="number" name="inscricaomu" value="0" class="form-control" value="<?php echo($dadosCliente['municipalregistration'] ); ?>" >
                                    </div>
                                    <div class="col-lg-3 pull-right">
                                        <label for="registroembratur">Id usuário</label>
                                        <input type="number" value="<?php echo($dadosCliente['register'] ); ?>" name="registroembratur" id="registroembratur" class="form-control" >
                                    </div>

                                    <div class="col-lg-6 pull-left">
                                        <label for="periodoinicial">Período Inicial</label>
                                        <input type="date" name="periodoinicial" value="<?php echo($dadosCliente['periodoinicial'] ); ?>" class="form-control">
                                    </div>
                                    <div class="col-lg-6 pull-right">
                                        <label for="periodofinal">Período Final</label>
                                        <input type="date" name="periodofinal" value="<?php echo($dadosCliente['periodofinal'] ); ?>" class="form-control">
                                    </div>
                                    <div class="col-lg-6 pull-left">

                                        <label for="limitereserva">Limite de Reserva</label>
                                        <input type="text" value="<?php echo( number_format($dadosCliente['limite'], 2,",", ".")); ?>"
                                               name="limitereserva" onKeyPress="return(moeda(this,'.',',',event))"  id="limitereserva" class="form-control" >
                                    </div>
                                    <div class="col-lg-6 pull-right">
                                        <label for="observacao">Observação</label>
                                        <input type="text" value="<?php echo($dadosCliente['observacao'] ); ?>"
                                               name="observacao" id="observacao" class="form-control" >
                                    </div>

                                    <input type="hidden" name="cliente" value="<?php echo($dadosCliente['id'] ); ?>">
                                    <div class="col-lg-6 pull-left">
                                        <a href="./pesquisa-cliente-fornecedor" class="btn btn-outline-primary btn-lg btn-block">
                                            Voltar
                                        </a>
                                    </div>
                                    <div class="col-lg-6 pull-right">
                                        <button class="btn btn-outline-success btn-lg btn-block" type="submit" name="clientupdate">
                                            Atualizar
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header" id="headingTwo">
                            <h2 class="mb-0">
                                <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                   Tarifário
                                </button>
                            </h2>
                        </div>
                        <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionExample">
                            <div class="card-body">
                                <?php if( count($registro) > 0 ){ ?>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                            <tr>
                                                <th>Serviço</th>
                                                <th>Net</th>
                                                <th>#</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php foreach ($registro as $item){ ?>
                                                <tr>
                                                    <form action="" method="post">
                                                        <td><?php echo($item->fullname); ?></td>
                                                        <td>
                                                            <input name="valor" type="text"
                                                                   value="<?php echo(number_format($item->valuenet, 2, ".", ".")); ?>">
                                                        </td>
                                                        <td>
                                                            <input type="hidden" name="cliente" value="<?php echo($_POST['cliente']); ?>">
                                                            <input type="hidden" name="idnet" value="<?php echo($item->id); ?>">
                                                            <button type="submit" name="salvar" style="backgroud:transparent; border:none; color:black;">
                                                                Salvar
                                                            </button>
                                                        </td>
                                                    </form>
                                                </tr>
                                            <?php }?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="container">
                                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target=".bd-example-modal-lg">Adicionar Tarifário</button>
                                    </div>
                                <?php } else{?>
                                    <div class="container-fluid">
                                        <div class="alert alert-warning" role="alert">Não há tarifário cadastrado</div>
                                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target=".bd-example-modal-lg">Criar Tarifário</button>
                                    </div>
                                <?php }?>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
    <div class="row">
        <div class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Criar Tarifário</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th>Serviço</th>
                                <th>Valor</th>
                                <th>#</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($registroServicos as $item){ ?>
                                <tr>
                                    <form method="post" action="">
                                        <td><?php echo($item->fullname); ?></td>
                                        <td>
                                            <input name="valor" id="valor" type="text" class="form-control" value="0.00">
                                        </td>
                                        <td>
                                            <input type="hidden" id="idservice" name="idservice" value="<?php echo($item->id); ?>">
                                            <input type="hidden" id="cliente" name="cliente" value="<?php echo($_POST['cliente']); ?>">
                                            <button type="submit" name="salvartarifa" style="backgroud:transparent; border:none; color:black;">
                                                Salvar
                                            </button>
                                        </td>
                                    </form>
                                </tr>
                            <?php }?>
                            </tbody>
                        </table>
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
        var selecionado = document.getElementById('service').value;

    </script>
<?php require_once ('footer.php'); ?>
