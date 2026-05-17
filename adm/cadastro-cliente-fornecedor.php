<?php require_once ('header.php');

$todosPaises = $pdo->prepare('select * from `ct_pais` ');
$todosPaises->execute();
$todosEstados = $pdo->prepare('select * from `ct_estado` ');
$todosEstados->execute();
$todosCidade = $pdo->prepare('select * from `ct_cidade` ');
$todosCidade->execute();
$todasTarifa = $pdo->prepare('SELECT * FROM `ct_tarifa`  ');
$todasTarifa->execute();

if( isset( $_POST['novocliente'] ) )
{
    $cnpj             = $_POST['cnpj'];
    $razao            = strtoupper( $_POST['razaosocial']);
    $nome             = strtoupper( $_POST['nomefantazia']);
    $tipo             = $_POST['tipo'];
    $endereco         = strtoupper( $_POST['endereco']);
    $datafundacao     = $_POST['datanf'];
    $complemento      = strtoupper( $_POST['complemento']);
    $pais             = $_POST['pais'];
    $estado           = $_POST['estado'];
    $cidade           = $_POST['cidade'];
    $cep              = $_POST['cep'];
    $telefone1        = $_POST['telefone1'];
    $telefone2        = $_POST['telefone2'];
    $celular          = $_POST['celular'];
    $email            = $_POST['email'];
    $inscricaoes      = $_POST['inscricaoes'];
    $inscricaomu      = $_POST['inscricaomu'];
    $registroembratur = $_POST['registroembratur'];
    $valor  = str_replace(".", "", $_POST['limitereserva']);
    $valor1 = str_replace(",", ".", $valor);
    $novoCliente = $pdo->prepare(
        'insert into `ct_cliente` (`id`, `fullname`, `cnpj`, `namefantazia`, `corporatename`, `type`, `address`, `datefundation`, `idcountry`, `idstate`, `idcity`,
    `cep`, `tel01`, `tel02`, `phone`, `email`, `municipalregistration`, `stateenrollment`, `register`, `observacao`, `periodoinicial`, `periodofinal`, `limite`)
                         values (DEFAULT, :fullname, :cnpj, :fantazia, :razao, :tipo, :ende, :dfundation, :country, :estado, :cidade, :cep, :tel1, :tel2,
                          :phone, :email, :rmun, :restado, :embratur, :obs, :inicio, :fim, :limite)');
    $novoCliente->execute( array(
            ":fullname"   => $nome,
            ":cnpj"       => $cnpj,
            ":fantazia"   => $nome,
            ":razao"      => $razao,
            ":tipo"       => $tipo,
            ":ende"       => $endereco,
            ":dfundation" => $datafundacao,
            ":country"    => $pais,
            ":estado"     => $estado,
            ":cidade"     => $cidade,
            ":cep"        => $cep,
            ":tel1"       => $telefone1,
            ":tel2"       => $telefone2,
            ":phone"      => $celular,
            ":email"      => $email,
            ":rmun"       => $inscricaomu,
            ":restado"    => $inscricaoes,
            ":embratur"   => $registroembratur,
            ":obs"        => "NÃO HÁ",
            ":inicio"     => $_POST['periodoinicial'],
            ":fim"        => $_POST['periodofinal'],
            ":limite"     => 0 //$valor1,
        )
    );
    header("location: editar-cliente?cliente=".$pdo->lastInsertId());
}
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
                                <li class="list-inline-item">Cliente/Fornecedor: Cadastro</li>
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
                <div class="card-title">Informações para o cadstro</div>
            </div>
            <div class="card-body">
                <div class="col-lg-12">
                    <form action="" method="post">
                        <div class="col-lg-12">
                            <label for="cnpj">CPF / CNPJ / Passaporte </label>
                            <input type="text" name="cnpj" id="cnpj" required class="form-control">
                        </div>
                        <div class="col-lg-6 pull-left">
                            <label for="nomefantazia">Nome Fantazia</label>
                            <input type="text" required name="nomefantazia" id="nomefantazia" class="form-control">
                        </div>

                        <div class="col-lg-6 pull-right">
                            <label for="razaosocial">Razão Social</label>
                            <input type="text" required name="razaosocial" id="razaosocial"  class="form-control">
                        </div>
                        <!-- -->
                        <div class="col-lg-4 pull-left">
                            <label for="tipo">Tipo</label>
                            <select name="tipo" id="tipo" class="form-control" required>
                                <option value="Pessoa Fisíca"> Pessoa Fisíca </option>
                                <option value="Pessoa Juridica"> Pessoa Juridica </option>
                                <option value="Estrangeiro(a)"> Estrangeiro(a) </option>
                            </select>
                        </div>
                        <div class="col-lg-4 pull-left">
                            <label for="endereco">Endereço </label>
                            <input type="text" name="endereco" required id="endereco" class="form-control">
                        </div>
                        <div class="col-lg-4 pull-right">
                            <label for="datanf">Data (Nascimento/Fundação)</label>
                            <input type="date" value="<?php echo( date( "Y-m-d" ) ); ?>" name="datanf" id="datanf" class="form-control">
                        </div>

                        <!-- -->

                        <div class="col-lg-6 pull-left">
                            <label for="pais">Pais</label>
                            <select class="form-control" name="pais" id="pais">
                                <?php  while ( $country = $todosPaises->fetch( PDO::FETCH_ASSOC ) ){ ?>
                                    <option value="<?php echo( $country['id'] ); ?>"><?php echo( utf8_encode( $country['name'] ) ); ?></option>
                                <?php }?>
                            </select>
                        </div>

                        <div class="col-lg-6 pull-right">
                            <label for="estado">Estado</label>
                            <select class="form-control" name="estado" id="estado">
                                <?php  while ( $state = $todosEstados->fetch( PDO::FETCH_ASSOC ) ){ ?>
                                    <?php if( $state['name'] == 'Bahia' ){ ?>
                                        <option selected value="<?php echo( $state['id'] ); ?>"><?php echo( utf8_encode( $state['name'] ) ); ?></option>
                                    <?php } else{?>
                                        <option value="<?php echo( $state['id'] ); ?>"><?php echo( utf8_encode( $state['name'] ) ); ?></option>
                                    <?php }?>
                                <?php }?>
                            </select>
                        </div>
                        <!-- -->
                        <div class="col-lg-6 pull-left">
                            <label for="cidade">Cidade</label>
                            <select class="form-control" name="cidade" id="cidade">
                                <?php  while ( $city = $todosCidade->fetch( PDO::FETCH_ASSOC ) ){ ?>
                                    <?php if( $city['name'] == 'Salvador' ){ ?>
                                        <option selected value="<?php echo( $city['id'] ); ?>"><?php echo( utf8_encode( $city['name'] ) ); ?></option>
                                    <?php } else{ ?>
                                        <option value="<?php echo( $city['id'] ); ?>"><?php echo( utf8_encode( $city['name'] ) ); ?></option>
                                    <?php }?>
                                <?php }?>
                            </select>
                        </div>
                        <div class="col-lg-6 pull-right">
                            <label for="cep">CEP:</label>
                            <input type="text" name="cep" id="cep" class="form-control" >
                        </div>

                        <!-- -->
                        <div class="col-lg-4 pull-left">
                            <label for="telefone1">Telefone 01</label>
                            <input type="text" name="telefone1" id="telefone1" class="form-control" value="0" >
                        </div>
                        <div class="col-lg-4 pull-left">
                            <label for="telefone2">Telefone 02</label>
                            <input type="text" name="telefone2" id="telefone2" class="form-control" value="0" >
                        </div>
                        <div class="col-lg-4 pull-right">
                            <label for="celular">Celular</label>
                            <input type="text" name="celular" id="celular" class="form-control" required >
                        </div>

                        <!-- -->
                        <div class="col-lg-3 pull-left">
                            <label for="email">E-mail</label>
                            <input type="text" value="contato@cassiturismo.com.br" name="email" id="email" class="form-control" >
                        </div>
                        <div class="col-lg-3 pull-left">
                            <label for="inscricaoes">Inscrição Estadual</label>
                            <input type="number" name="inscricaoes" value="0" class="form-control" value="0">
                        </div>
                        <div class="col-lg-3 pull-left">
                            <label for="inscricaomu">Inscrição Municipal</label>
                            <input type="number" name="inscricaomu" value="0" class="form-control" value="0" >
                        </div>
                        <div class="col-lg-3 pull-right">
                            <label for="registroembratur">Registro Embratur</label>
                            <input type="number" value="0" name="registroembratur" id="registroembratur" class="form-control" >
                        </div>

                        <div class="col-lg-6 pull-left">
                            <label for="periodoinicial">Período Inicial</label>
                            <input type="date" name="periodoinicial" value="<?php echo(date("Y-m-d")); ?>" class="form-control">
                        </div>
                        <div class="col-lg-6 pull-right">
                            <label for="periodofinal">Período Final</label>
                            <input type="date" name="periodofinal" value="<?php echo(date("Y-m-d")); ?>" class="form-control">
                        </div>
                        <!--
                        <div class="col-lg-4 pull-right">
                            <label for="limitereserva">Limite de Reserva</label>
                            <input type="text" onKeyPress="return(moeda(this,'.',',',event))"
                                   value="<?php echo(number_format(0, 2,",", ".") ); ?>" name="limitereserva" id="limitereserva"
                                   class="form-control" >
                        </div>
                        -->
                        <div class="container-fluid">
                            <button class="btn btn-primary btn-lg btn-block" type="submit" name="novocliente">
                                Cadastrar
                            </button>
                        </div>

                    </form>
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
