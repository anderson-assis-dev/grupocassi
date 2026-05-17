<?php
require_once ('header.php');
require_once('class/Cliente.php');
$cliente = new Cliente();
if( isset($_POST['removercliente']) )
{
    $cliente->setIdCliente($_POST['id']);
    $cliente->removercliente();
}
?>
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
                                <li class="list-inline-item">Cliente: Site</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- END BREADCRUMB-->
    <div class="container">
        <?php if(isset($_POST['removercliente'])){ ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <strong>Cliente removido do site!</strong>
            </div>
        <?php }?>
        <div class="card">
            <div class="card-header">
                <div class="card-title">Cliente sites</div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="myTable">
                        <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Telefone</th>
                            <th>E-mail</th>
                            <th>#</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($cliente->todosClientes() as $key => $value){ ?>
                            <tr>
                                <td><?php echo($value['nomecompleto']); ?></td>
                                <td><?php echo($value['telefone']); ?></td>
                                <td><?php echo($value['email']); ?></td>
                                <td>
                                    <form action="lista_clientes_site.php" method="post">
                                        <input type="hidden" name="id" value="<?php echo($value['idcliente']); ?>">
                                        <button name="removercliente" style="background-color: transparent; border: none;">Remover</button>
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
<?php require_once ('footer.php'); ?>
