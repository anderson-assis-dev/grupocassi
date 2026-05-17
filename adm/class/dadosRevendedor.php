<?php
require_once ('header.php');
require_once('class/Revendedor.php');
$revendedor = new Revendedor();
require_once('class/Email.php');
$email = new Email();
if( isset($_POST['id']) )
{
    $revendedor = new Revendedor();
    $revendedor->setIdRevendedor($_POST['id']);
    $dados_revendedor = $revendedor->meuDados();
}

if( isset($_POST['aprovar']) )
{
    $revendedor->setIdRevendedor($_POST['idrevendedor']);
    $revendedor->cadastarDadosNoSistema();
    $revendedor->setStatus(2);
    $revendedor->aprovarRevendedor();
    $dados_revendedor = $revendedor->meuDados();
    $email->setMensagem("Seu cadastro foi aprovado para ser um PersonalTour - Cassi Turismo");
    $email->setEmail($dados_revendedor[0]['email']);
    $email->setNome($dados_revendedor[0]['nomecompleto']);
    $email->setAssunto('PersonalTour');

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
    <div class="container">
        <?php if(isset($_POST['aprovar'])){ ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <strong>Cadastro Aprovado!</strong>
            </div>
        <?php }?>
        <div class="card">
            <div class="card-header">
                <div class="card-title">Dados do Revendedor</div>
            </div>
            <div class="card-body">
                <div class="col-lg-6 pull-left">
                    <img width="200" src="<?php echo("https://cassiturismo.com.br/img/team/".$dados_revendedor[0]['nomelogo']); ?>" class="img-thumbnail">
                </div>
                <div class="col-lg-6 pull-right">
                    <img width="200" src="<?php echo("https://cassiturismo.com.br/img/team/".$dados_revendedor[0]['nomelogo']); ?>" class="img-thumbnail">
                </div>
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
                        <label for="cpf">CPF</label>
                        <input class="form-control" name="cpf" id="cpf" type="text" required value="<?php echo($dados_revendedor[0]['cpfcnpj']); ?>">
                    </div>
                    <div class="col-lg-6 pull-left">
                        <label for="datanascimento">Data de Nascimento</label>
                        <input class="form-control" name="datanascimento" id="datanascimento" type="date" required value="<?php echo($dados_revendedor[0]['datanascimento']); ?>">
                    </div>
                    <div class="col-lg-6 pull-right">
                        <label for="cep">CEP</label>
                        <input class="form-control" name="cep" id="cep" type="text" value="<?php echo($dados_revendedor[0]['cep']); ?>" required>
                    </div>
                    <div class="col-lg-4 pull-right">
                        <label for="endereco">Endereço</label>
                        <input class="form-control" name="endereco" id="endereco" type="text" required value="<?php echo($dados_revendedor[0]['endereco']); ?>">
                    </div>
                    <div class="col-lg-4 pull-right">
                        <label for="numero">Número</label>
                        <input class="form-control" name="numero" id="numero" type="number" required value="<?php echo($dados_revendedor[0]['numero']); ?>">
                    </div>
                    <div class="col-lg-4 pull-right">
                        <label for="complemento">Complemento</label>
                        <input class="form-control" name="complemento" id="complemento" type="text" required value="<?php echo($dados_revendedor[0]['complemento']); ?>">
                    </div>

                    <div class="container-fluid">
                        <h4 style="text-transform: uppercase;">Dados bancários</h4>
                        <hr style="color: #960000; background-color: #960000; height: 2px;">
                    </div>

                    <div class="col-lg-6 pull-left">
                        <label for="complemento">Nome do Banco</label>
                        <input class="form-control" name="nomebanco" id="nomebanco" type="text" required value="<?php echo($dados_revendedor[0]['nomebanco']); ?>">
                    </div>
                    <div class="col-lg-6 pull-right">
                        <label for="agencia">Agência</label>
                        <input class="form-control" name="agencia" id="agencia" type="text" required value="<?php echo($dados_revendedor[0]['agencia']); ?>">
                    </div>
                    <div class="col-lg-4 pull-left">
                        <label for="tipoconta">Tipo de Conta</label>
                        <select class="form-control" name="tipoconta" id="tipoconta" required>
                            <?php if( $dados_revendedor[0]['tipoconta'] == 'corrente' ){ ?>
                                <option selected id="corrente">Corrente</option>
                            <?php } else { ?>
                                <option id="poupanca">Poupança</option>
                            <?php }?>
                        </select>
                    </div>
                    <div class="col-lg-4 pull-left">
                        <label for="numerodaconta">Número da Conta</label>
                        <input class="form-control" name="numerodaconta" id="numerodaconta" type="text" required value="<?php echo($dados_revendedor[0]['tipoconta']); ?>">
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
</div>
<?php require_once ('footer.php'); ?>
