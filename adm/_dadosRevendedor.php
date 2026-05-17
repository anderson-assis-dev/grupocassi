<?php
require_once ('header.php');
require_once('class/Revendedor.php');
$revendedor = new Revendedor();
require_once('class/Cliente.php');
$cliente = new Cliente();
require_once('class/Email.php');
$email = new Email();
$total_cmoissao = 0;
$total_paga     = 0;
$total_areceber = 0;
if( isset($_POST['id']) )
{
    $revendedor = new Revendedor();
    $revendedor->setIdRevendedor($_POST['id']);
    $cliente->setIdRevendedor($_POST['id'] );
    $dados_revendedor = $revendedor->meuDados();
    $_SESSION['idcliente'] = $_POST['id'];

}

if( isset($_POST['aprovar']) )
{
    $revendedor->setIdRevendedor($_POST['idrevendedor']);
    $revendedor->setStatus(2);
    $revendedor->aprovarRevendedor();
    $dados_revendedor = $revendedor->meuDados();
    $email->setMensagem("Seu cadastro foi aprovado para ser um PersonalTour - Cassi Turismo");
    $email->setEmail($dados_revendedor[0]['email']);
    $email->setNome($dados_revendedor[0]['nomefantasia']);
    $email->setNome($dados_revendedor[0]['nomefantasia']);
    $email->setAssunto('PersonalTour');
    $email->enviarEmail();
    $revendedor->setNomeCompleto($dados_revendedor[0]['nomefantasia']);
    $revendedor->setEmail($dados_revendedor[0]['email']);
    $revendedor->setCpfCnpj($dados_revendedor[0]['cpfcnpj']);
    $revendedor->setTelefone($dados_revendedor[0]['telefone']);
    $revendedor->setEndereco($dados_revendedor[0]['endereco']);
    $revendedor->setCep($dados_revendedor[0]['cep']);
    $revendedor->setNomeLogo("https://cassiturismo.com.br/img/team/".$dados_revendedor[0]['nomelogo']);
    $revendedor->setDataNascimento($dados_revendedor[0]['datanascimento']);
    $revendedor->cadastarDadosNoSistema();

}
if( isset($_POST['atualizarvalor']) )
{
    $valor  = str_replace(".", "", $_POST['valorpago']);
    $valor1 = str_replace(",", ".", $valor);
    $revendedor->setIdRevendedor($_POST['idrevendedor']);
    $dados_revendedor = $revendedor->meuDados();
    $cliente->setIdRevendedor($_POST['idrevendedor'] );
    $cliente->setIdCliente($_POST['idcliente']);
    $cliente->setComissao(str_replace(",", ".",$_POST['comissao']));
    $cliente->setComissaoRecebida($_POST['comissaorecebida'] + str_replace(",", ".", $_POST['valorpago']));
    $cliente->setComissaoAReceber(str_replace(",", ".",$_POST['comissaorecebida']) - $_POST['comissao']);
    print_r($cliente->atualizarClientePorRevendedor($_POST['datapagamento'], $_POST['formapagamento'], $_POST['id']));
    $novaTransacao = $pdo->prepare(
        "insert into `ct_caixa` (`id`, `datevencimento`, `datepagamento`, `datecompetencia`, `nome` ,`descricao`, `idcliente`, `idtipo`, `idconta`, 
                     `idplano`, `idempresa` ,`idstatus`, `valor`, `idusr`, `dataabertura`) values (DEFAULT, :vencimento, :pagamento, :competencia, :nome ,:descricao, 
                      :cliente, :tipo, :conta, :plano, :empresa ,:statuus, :valor, :idusr, :abertura)");
    $novaTransacao->execute(
        array(
            ":vencimento"  => date("Y-m-d"),
            ":pagamento"   => date("Y-m-d"),
            ":competencia" => date("Y-m-d"),
            ":nome"        => $_POST['nome'],
            ":descricao"   => "PAGAMENTO DE COMISSAO DO PPERSONALTOUR ".$_POST['nome'],
            ":cliente"     => 113,
            ":tipo"        => 2,
            ":conta"       => 1,
            ":plano"       => 1,
            ":empresa"     => 1,
            ":statuus"     => 1,
            ":valor"       => $valor1,
            "idusr"        => $_SESSION['id'],
            ":abertura"    => date("Y-m-d")
        )
    );
}
?>
<style>
    .col-lg-6, .col-lg-4{margin-bottom: 20px;}
</style>
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
                                    <a href="index.php">Home</a>
                                </li>
                                <li class="list-inline-item seprate">
                                    <span>/</span>
                                </li>
                                <li class="list-inline-item">PAX: Editar pax</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- END BREADCRUMB-->
    <div class="">
        <?php if(isset($_POST['aprovar'])){ ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <strong>Cadastro Aprovado!</strong>
            </div>
        <?php }?>
        <?php if(isset($_POST['atualizarvalor'])){ ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <strong>Valor da comissão atualizado!</strong>
            </div>
        <?php }?>
        <div class="accordion" id="accordionExample">
            <div class="card">
                <div class="card-header" id="headingOne">
                    <h2 class="mb-0">
                        <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                            Dados do Revendedor | Cadatro realizado em: <?php echo(date("d/m/Y", strtotime($dados_revendedor[0]['abertura']))); ?>
                        </button>
                    </h2>
                </div>

                <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionExample">
                    <div class="card-body">
                        <div class="col-lg-6 pull-left">
                            <img width="200" src="<?php echo("https://cassiturismo.com.br/img/team/".$dados_revendedor[0]['rosto']); ?>" class="img-thumbnail">
                        </div>
                        <div class="col-lg-6 pull-right">
                            <img width="200" src="<?php echo("https://cassiturismo.com.br/img/team/".$dados_revendedor[0]['nomelogo']); ?>" class="img-thumbnail">
                        </div>
                    </div>
                    <div class="card-body">

                        <form enctype="multipart/form-data" action="" class="" method="post">
                            <div class="col-lg-6 pull-left">
                                <label for="nome">Nome Completo</label>
                                <input class="form-control" name="nome" id="nome" type="text" required value="<?php echo($dados_revendedor[0]['nomecompleto']); ?>">
                            </div>
                            <div class="col-lg-6 pull-right">
                                <label for="telefone">Telefone</label>
                                <input class="form-control" name="telefone" id="telefone" type="tel" required value="<?php echo($dados_revendedor[0]['telefone']); ?>">
                            </div>
                            <div class="col-lg-6 pull-left">
                                <label for="email">E-mail</label>
                                <input class="form-control" name="email" id="email" type="email" required value="<?php echo($dados_revendedor[0]['email']); ?>">
                            </div>
                            <div class="col-lg-6 pull-right">
                                <label for="cnpj">CNPJ</label>
                                <input class="form-control" name="cnpj" id="cnpj" type="text" required value="<?php echo($dados_revendedor[0]['cnpj']); ?>">
                            </div>
                            <div class="col-lg-6 pull-left">
                                <label for="cpf">CPF</label>
                                <input class="form-control" name="cpf" id="cpf" type="text" required value="<?php echo($dados_revendedor[0]['cpfcnpj']); ?>">
                            </div>
                            <div class="col-lg-6 pull-right">
                                <label for="datanascimento">Data de Nascimento</label>
                                <input class="form-control" name="datanascimento" id="datanascimento" type="date" required value="<?php echo($dados_revendedor[0]['datanascimento']); ?>">
                            </div>
                            <div class="col-lg-6 pull-left">
                                <label for="cep">CEP</label>
                                <input class="form-control" name="cep" id="cep" type="text" value="<?php echo($dados_revendedor[0]['cep']); ?>" required>
                            </div>
                            <div class="col-lg-6 pull-right">
                                <label for="endereco">Endereço</label>
                                <input class="form-control" name="endereco" id="endereco" type="text" required value="<?php echo($dados_revendedor[0]['endereco']); ?>">
                            </div>
                            <div class="col-lg-6 pull-left">
                                <label for="numero">Número</label>
                                <input class="form-control" name="numero" id="numero" type="number" required value="<?php echo($dados_revendedor[0]['numero']); ?>">
                            </div>
                            <div class="col-lg-6 pull-right">
                                <label for="complemento">Complemento</label>
                                <input class="form-control" name="complemento" id="complemento" type="text" required value="<?php echo($dados_revendedor[0]['complemento']); ?>">
                            </div>

                            <div class="container-fluid">
                                <h4 style="text-transform: uppercase;">Dados bancários</h4>
                                <hr style="color: #960000; background-color: #960000; height: 2px;">
                            </div>

                            <div class="col-lg-4 pull-left">
                                <label for="complemento">Nome do Banco</label>
                                <input class="form-control" name="nomebanco" id="nomebanco" type="text" required value="<?php echo($dados_revendedor[0]['nomebanco']); ?>">
                            </div>
                            <div class="col-lg-4 pull-left">
                                <label for="complemento">Chave pix</label>
                                <input class="form-control" name="chavepix" id="chavepix" type="text" required value="<?php echo($dados_revendedor[0]['chavepix']); ?>">
                            </div>
                            <div class="col-lg-4 pull-right">
                                <label for="agencia">Agência</label>
                                <input class="form-control" name="agencia" id="agencia" type="text" required value="<?php echo($dados_revendedor[0]['agencia']); ?>">
                            </div>
                            <div class="col-lg-4 pull-left">
                                <label for="tipoconta">Tipo de Conta</label>
                                <select class="form-control" name="tipoconta" id="tipoconta" required>
                                    <?php if( $dados_revendedor[0]['tipoconta'] == 'Corrente' ){ ?>
                                        <option selected id="corrente">Corrente</option>
                                    <?php } else { ?>
                                        <option id="poupanca">Poupança</option>
                                    <?php }?>
                                </select>
                            </div>
                            <div class="col-lg-4 pull-left">
                                <label for="numerodaconta">Número da Conta</label>
                                <input class="form-control" name="numerodaconta" id="numerodaconta" type="text" required value="<?php echo($dados_revendedor[0]['numeroconta']); ?>">
                            </div>

                            <div class="col-lg-4 pull-right">
                                <label for="nomefantazia">Nome Fantazia</label>
                                <input class="form-control" name="nomefantazia" id="nomefantazia" type="text" disabled value="<?php echo($dados_revendedor[0]['nomefantasia']); ?>">
                            </div>
                            <input class="form-control" name="idrevendedor" id="idrevendedor" type="hidden"  value="<?php echo($dados_revendedor[0]['idcassiturismo_revendedor']); ?>">
                            <div class="container-fluid">
                                <a class="btn btn-outline-primary pull-left" href="lista_revendedores">Voltar</a>
                                <button class="btn btn-outline-success pull-right" style="font-weight: bold;" type="submit" name="aprovar">Aprovar Cadastro</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header" id="headingTwo">
                    <h2 class="mb-0">
                        <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                            Clientes do revendedor
                        </button>
                    </h2>
                </div>
                <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionExample">
                    <div class="card-body">
                        <div class="accordion" id="accordionExample">
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Comissão</th>
                                    <th>Comissão Paga</th>
				                    <th>Voucher</th>
                                    <th>Data de pagamento</th>
                                    <th>Forma de pagamento</th>
                                    <th>Status</th>
                                    <th>Pagar</th>
                                    <th>#</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                $contador =0;
                                foreach ( $cliente->clientesPorRevendedor() as $key => $value ){
                                    $contador+=1;
                                    $total_cmoissao += $value['comissao'];
                                    $total_paga     += $value['comissaorecebida'];
                                    $total_areceber += $value['comissaoareceber'];
                                    if($value['numerovoucher'] <> null)
                                    {

                                        $dadosReserva = $pdo->prepare("SELECT * FROM `ct_reserva` where `numbervoucher` = :numbervoucher");
                                        $dadosReserva->execute( array(":numbervoucher" => $value['numerovoucher']));
                                        $dadosGerais = $dadosReserva->fetch(PDO::FETCH_ASSOC);
                                    }

                                ?>
                                    <tr>
                                        <form action="" method="post">  
                                        <td><?php echo($value['nomecompleto']); ?></td>
                                        <td><input class="form-control" name="comissao" id="comissao" type="text"  value="<?php echo(number_format($value['comissao'], 2, ",", ".")); ?>"></td>
                                        <td><?php echo("R$ ".number_format($value['comissaorecebida'], 2, ",", ".")); ?></td>
                                        <?php if($value['numerovoucher'] <> null){?>
                                        <td><a target="_blank" href="<?php echo("http://grupocassi.com.br/vouchercliente?voucher=".$value['numerovoucher']); ?>"><?php echo($value['numerovoucher']); ?></a></td>
                                        <?php } else { ?>
                                        <td>Sem voucher</td>
                                        <?php }?>
                                        <td><input class="form-control" name="datapagamento" id="datapagamento" type="date"  value="<?php echo($value['datadoultimopagamento']); ?>"></td>
                                        <td><input class="form-control" name="formapagamento" id="formapagamento" type="text"  value="<?php echo($value['forma_pagamento']); ?>"></td>
                                     
                                        <?php if(!empty($dadosGerais)){?>
                                            <td><?php if($dadosGerais['totalservico'] - $dadosGerais['totalcredito'] > 0){echo("Falta Pagar");} else{echo("Voucher Pago");}  ?></td>
                                        <?php } else { ?>
                                            <td>Sem voucher</td>
                                        <?php }?>
                                        
                                            <input class="form-control" name="idrevendedor" id="idrevendedor" type="hidden"  value="<?php echo($dados_revendedor[0]['idcassiturismo_revendedor']); ?>">
                                            <input class="form-control" name="nome" id="nome" type="hidden"  value="<?php echo($dados_revendedor[0]['nomecompleto']); ?>">
                                            <input class="form-control" name="idcliente" id="idcliente" type="hidden"  value="<?php echo($value['idcliente']); ?>">
                                            <input class="form-control" name="id" id="id" type="hidden"  value="<?php echo($value['id']); ?>">
                
                                            <td>
                                                <div class="input-group mb-3">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text" id="basic-addon1">R$</span>
                                                    </div>
                                                    <input name="valorpago" class="form-control" type="text" value="<?php echo(number_format($value['comissao'] - $value['comissaorecebida'], 2,",",".")); ?>" id="valorpago"></td>
                                                </div>

                                            <td><button type="submit" name="atualizarvalor" class="btn btn-outline-primary">Atualizar</button></td>
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
    </div>
</div>
<?php require_once ('footer.php'); ?>
