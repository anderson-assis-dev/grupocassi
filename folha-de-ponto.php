<?php require_once ('header.php');
      $buscarEmpresas = $pdo->prepare("SELECT * FROM `ct_cliente` where fullname LIKE 'cassi%' LIMIT 2");
      $buscarEmpresas->execute();
      $registro       = $buscarEmpresas->fetchAll(PDO::FETCH_CLASS);
 ?>
<style>
    .col-lg-4, .col-lg-6, .col-lg-3{
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
                                    <a href="./index.php">Home</a>
                                </li>
                                <li class="list-inline-item seprate">
                                    <span>/</span>
                                </li>
                                <li class="list-inline-item">RH: Folha de Ponto</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <div class="row">
        <div class="container">
            <div class="col-lg-12">
                <h4>Informações sobre o funcionário: </h4>
                <hr>
                <form action="./relatorio/pdf-folha-de-ponto.php" method="post" target="_blank">
                    <div class="col-lg-3 pull-left">
                        <strong><label for="nomefuncionario">Nome do Funcionário</label></strong>
                        <input type="text" name="nomefuncionario"  id="nomefuncionario" class="form-control">
                    </div>
                    <div class="col-lg-3 pull-left">
                        <strong><label for="empresa">Empresa</label></strong>
                        <select class="form-control" name="empresa">
                            <?php foreach ($registro as $key ) { ?>
                                <option value="<?php echo($key->id); ?>">
                                  <?php echo($key->corporatename); ?>
                                </option>
                            <?php }?>
                        </select>

                    </div>
                    <div class="col-lg-3 pull-left">
                        <strong><label for="funcao">Função</label></strong>
                        <input type="text" name="funcao" id="funcao" class="form-control">
                    </div>
                    <div class="col-lg-3 pull-right">
                        <strong><label for="mesreferencia">Mês de Referência</label></strong>
                        <input type="date" name="mesreferencia" id="mesreferencia" class="form-control"  >
                    </div>
                    <div class="container pull-left">
                        <button type="submit" class="btn btn-success btn-lg" name="folhadeponto" id="folhadeponto">Gerar Folha</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
    <?php require_once ('footer.php'); ?>
