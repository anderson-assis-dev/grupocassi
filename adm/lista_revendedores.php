<?php
require_once ('header.php');
require_once('class/Revendedor.php');
$revendedor = new Revendedor();
require_once('class/Cliente.php');
$cliente = new Cliente();
if( isset($_POST['removerrevendedor']) )
{
    $revendedor->setIdRevendedor($_POST['id']);
    $revendedor->removerRevendedor();
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
                                <li class="list-inline-item">Revendedores: Todos</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- END BREADCRUMB-->
    <?php if( isset( $_POST['removerrevendedor1'] ) ){ ?>
        <div class="modal fade" id="exemplomodal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
            <div class="modal-dialog modal-lg" role="document">
                <form action="" method="post">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="gridSystemModalLabel">Excluir</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">X</span></button>

                        </div>
                        <div class="modal-body">
                            <div class="container">
                                <p>Deseja realmente excluir ?</p>
                            </div>
                        </div>
                        <div class="modal-footer">

                            <input type="hidden" name="id" id="id" value="<?php echo($_POST['id']); ?>" >
                            <button type="submit" class="btn btn-success pull-left" name="removerrevendedor">Sim</button>
                            <button type="button" class="btn btn-outline-warning pull-right" data-dismiss="modal" aria-label="Close" >Não</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    <?php }?>
    <div class="container">
        <?php if(isset($_POST['removerrevendedor'])){ ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <strong>Revendedor removido!</strong>
            </div>
        <?php }?> 
        <div class="card">
            <div class="card-header">
                <div class="card-title">Listando Personais Tour</div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="myTable">
                        <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Empresa</th>
                            <th>Telefone</th>
                            <th>E-mail</th>
                            <th>Status</th>
                            <th>Comissão total</th>
                            <th>Comissão recebida</th>
                            <th>Comissão a receber</th>
                            <th>#</th>
                            <th>#</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($revendedor->listarRevendedoresPendenteAprovacao() as $key => $value){
                                $cliente->setIdRevendedor($value['idcassiturismo_revendedor']);
                            ?>
                            <tr>
                                <td><?php echo($value['nomecompleto']); ?></td>
                                <td><?php echo($value['nomefantasia']); ?></td>
                                <td><?php echo($value['telefone']); ?></td>
                                <td><?php echo($value['email']); ?></td>
                                <td><?php if($value['status'] == 1){echo("Aguardando Aprovação");}else{echo("Aprovado");} ?></td>
                                <td><?php echo("R$ ".number_format($cliente->clientesPorRevendedorValorestotais()[0]['total_a_pagar'], 2 ,",", ".")); ?></td>
                                <td><?php echo("R$ ".number_format($cliente->clientesPorRevendedorValorestotais()[0]['total_recebido'], 2 ,",", ".")); ?></td>
                                <td><?php echo("R$ ".number_format($cliente->clientesPorRevendedorValorestotais()[0]['total_a_pagar']-$cliente->clientesPorRevendedorValorestotais()[0]['total_recebido'], 2 ,",", ".")); ?></td>
                                <td>
                                    <form action="dadosRevendedor" method="post">
                                        <input type="hidden" name="id" value="<?php echo($value['idcassiturismo_revendedor']); ?>">
                                        <button name="visualizarRevendedor" style="background-color: transparent; border: none;">Abrir</button>
                                    </form>
                                </td>
                                <td>
                                    <form action="lista_revendedores" method="post">
                                        <input type="hidden" name="id" value="<?php echo($value['idcassiturismo_revendedor']); ?>">
                                        <button name="removerrevendedor1" style="background-color: transparent; border: none;">Remover</button>
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
<script>
    $(document).ready(function() {
        $('#exemplomodal').modal('show');
    });
</script>
