<?php require_once ('header.php');

$todoCliente = $pdo->prepare('select * from  `ct_cliente` ');
$todoCliente->execute();

?>
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
                                <li class="list-inline-item">Cliente / Fornecedor: Pesquisa</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- END BREADCRUMB-->

    <div class="row">
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    Pesquisar Cliente
                </div>
            </div>
            <div class="card-body">
                <div class="col-lg-12 table-responsive">
                    <table class="table table-bordered" id="example23">
                        <thead>
                        <th>Nome F</th>
                        <th>Razão</th>
                        <th>CNPJ</th>
                        <th>Endereço</th>
                        <th>CEP</th>
                        <th>Telefone</th>
                        <th>E-mail</th>
                        </thead>
                        <tbody>
                        <?php while ( $clint = $todoCliente->fetch( PDO::FETCH_ASSOC ) ){ ?>
                            <tr>
                                <td><?php echo( utf8_encode( $clint['namefantazia'] ) ) ?></td>
                                <td><?php echo( utf8_encode( $clint['corporatename'] ) ) ?></td>
                                <td>
                                    <form action="./editar-cliente.php" method="post">
                                        <input type="hidden" name="cliente" value="<?php echo( utf8_encode( $clint['id'] ) ) ?>" >
                                        <button type="submit" style="background:transparent; border:none;color:black;">
                                            <?php echo( utf8_encode( $clint['cnpj'] ) ) ?>
                                        </button>
                                    </form>
                                </td>

                                <td><?php echo( utf8_encode( $clint['address'] ) ) ?></td>
                                <td><?php echo( utf8_encode( $clint['cep'] ) ) ?></td>
                                <td><?php echo( utf8_encode( $clint['phone'] ) ) ?></td>
                                <td><?php echo( utf8_encode( $clint['email'] ) ) ?></td>
                            </tr>
                        <?php }?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

<?php require_once ('footer.php'); ?>
