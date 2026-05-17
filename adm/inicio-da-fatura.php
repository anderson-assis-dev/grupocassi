<?php require_once ('header.php');

$cliente = $pdo->prepare('select * from `ct_cliente` ORDER BY `ct_cliente`.`fullname` DESC');
$cliente->execute();
$status = $pdo->prepare('select * from `ct_statusinvoice`');
$status->execute();
$totalFatura  = 0;
$totalCredito = 0;
$total  = 0;
$total1 = 0;
$minhasFaturas = $pdo->prepare(
        "select f.id, f.dateinput, f.dateoutput, f.tarifa, f.credito, c.fullname, f.situacao, f.idcliente, f.status 
                  from `ct_fatura` f left join `ct_cliente` c on c.id = f.idcliente order by f.id ");
$minhasFaturas->execute();
$contadorFaturas = $minhasFaturas->rowCount();

if( isset( $_POST['pesquisarfatura'] ) )
{
    $datainicio = $_POST['periodoinicial'];
    $datafinal  = $_POST['periodofinal'];
    $idcliente  = $_POST['cliente'];
    $ststus     = $_POST['status'];

    $_SESSION['periodoinicial'] = $_POST['periodoinicial'];
    $_SESSION['periodofinal']   = $_POST['periodofinal'];
    $_SESSION['cliente']        = $_POST['cliente'];
    $_SESSION['status']         = $_POST['status'];

    $totalFaturaADD = 0;
}
?>
<style>
    .col-md-6{
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
                                <li class="list-inline-item">Financeiro: Fatura</li>
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
                    <h5 class="mb-0">
                      <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true"
                       aria-controls="collapseOne">
                        Cadastar Fatura
                      </button>
                    </h5>
                  </div>

                  <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionExample">
                    <div class="card-body">
                      <h4>Informações da Fatura: </h4>
                      <hr>
                      <form action="" method="post">
                          <div class="col-md-6 pull-left">
                              <strong><label for="periodoinicial">Periodo Inicial</label></strong>
                              <input type="date" name="periodoinicial" id="periodoinicial" class="form-control" required>
                          </div>
                          <div class="col-md-6 pull-right">
                              <strong><label for="periodofinal">Periodo Final</label></strong>
                              <input type="date" name="periodofinal" id="periodofinal" class="form-control" required>
                          </div>
                          <div class="col-md-6 pull-left">
                              <strong><label for="cliente">Cliente</label></strong>
                              <select class="form-control" name="cliente" id="cliente" required>
                                  <?php while ( $dadosCliente = $cliente->fetch( PDO::FETCH_ASSOC ) ){ ?>
                                      <option value="<?php echo($dadosCliente['id']); ?>" ><?php echo($dadosCliente['fullname']); ?></option>
                                  <?php }?>
                              </select>
                          </div>
                          <div class="col-md-6 pull-right">
                              <strong><label for="status">Status</label></strong>
                              <select class="form-control" name="status[]" multiple id="status" required>
                                  <?php while ( $dadosStatus = $status->fetch( PDO::FETCH_ASSOC ) ){ ?>
                                      <option value="<?php echo($dadosStatus['id']); ?>" ><?php echo($dadosStatus['nameinvoice']); ?></option>
                                  <?php }?>
                              </select>
                          </div>
                          <div class="form-group container-fluid">
                              <button class="btn btn-primary btn-block btn-lg" name="pesquisarfatura" id="pesquisarfatura">
                                  Pesquisar
                              </button>
                          </div>
                      </form>
                      <?php if( isset( $_POST['pesquisarfatura'] )){ ?>
                          <table class="table table-bordered">
                              <thead>
                              <tr>
                                  <th>Voucher</th>
                                  <th>Pax</th>
                                  <th>Total</th>
                              </tr>
                              </thead>
                              <tbody>
                                    <?php for($i = 0; $i <= count($ststus); $i++){
                                        $buscarConferencia = $pdo->prepare(
                                            'select r.id,r.pax, r.numbervoucher,r.idservico,r.valueservice as valorP, s.fullname, 
                                                      r.qtdpax, r.qtdchild,r.idcliente from `ct_reserva` r left join `ct_servico` s on r.idservico = s.id 
                                                      where r.`dateinput` >= :inicio and r.`dateinput` <= :fim and r.`idstatusinvoice` = :statuss 
                                                      and r.`idcliente` = :cliente and r.`idstatus` <> 2 order by r.numbervoucher');
                                        $buscarConferencia->execute( array( ":inicio" => $datainicio, ":fim" => $datafinal,
                                            ":statuss" => $ststus[$i], ":cliente" => $idcliente ) );
                                        $registroReserva = $buscarConferencia->fetchAll(PDO::FETCH_CLASS);
                                        ?>
                                        <?php foreach ($registroReserva as $item)
                                        {
                                            $buscaNet = $pdo->prepare("select * from `ct_clientservice` where idclient = :cliente and idservice = :se");
                                            $buscaNet->execute(array(":cliente" => $item->idcliente, ":se" => $item->idservico));
                                            $dados = $buscaNet->fetch(PDO::FETCH_ASSOC);

                                            if($dados['valuenet'] == 0)
                                            {
                                                $totalReserva = ( ($item->valorP * $item->qtdpax ) +
                                                    ( ($item->valorP / 2) * $item->qtdchild ) ) ;

                                            }else{
                                                $totalReserva = ( ($dados['valuenet'] * $item->qtdpax ) +
                                                    ( ($dados['valuenet'] / 2) * $item->qtdchild )  );
                                            }

                                            $total = $total + $totalReserva;
                                            $buscaTarifaCredito = $pdo->prepare(
                                                'SELECT SUM(valuecredit) as credito FROM `ct_createfaturacredit` where numbervoucher = :voucher');
                                            $buscaTarifaCredito->execute( array(":voucher" => $item->numbervoucher ) );
                                            $informacoes = $buscaTarifaCredito->fetch( PDO::FETCH_ASSOC );
                                            $contador = $buscaTarifaCredito->rowCount();
                                            $totalCredito = $totalCredito + $informacoes['credito'];

                                            $buscarConferenciaADD = $pdo->prepare(
                                                '
                                                    select r.idcliente, ra.qpax, ra.qchild, ra.valueservice as valorS, r.pax, r.numbervoucher, ra.idservice
                                                    from `ct_recentlyadd` ra left join `ct_reserva` r on r.id = ra.idrecently left join `ct_servico` s 
                                                    on ra.idservice = s.id where ra.idrecently = :id');
                                            $buscarConferenciaADD->execute( array( ":id" => $item->id ) );

                                            $registroReservaADD = $buscarConferenciaADD->fetchAll(PDO::FETCH_CLASS);
                                            ?>
                                            <tr>
                                                <td>
                                                    <form method="post" target="_blank" action="./informacoes-reserva.php">
                                                        <input type="hidden" name="voucher" id="voucher" value="<?php echo($item->numbervoucher); ?>" >
                                                        <button type="submit" style="background-color: transparent; border: none;">
                                                            <?php echo($item->numbervoucher); ?>
                                                        </button>
                                                    </form>
                                                </td>
                                                <td><?php echo($item->pax); ?></td>
                                                <td><?php echo("R$".number_format($totalReserva, 2, ",", ".")); ?></td>
                                            </tr>
                                            <?php foreach ($registroReservaADD as $item2)
                                            {
                                                $buscaNet2 = $pdo->prepare(
                                                    "select * from `ct_clientservice` where idclient = :cliente and idservice = :se");
                                                $buscaNet2->execute(array(":cliente" => $item2->idcliente, ":se" => $item2->idservice));
                                                $dados1 = $buscaNet2->fetch(PDO::FETCH_ASSOC);

                                                if($dados1['valuenet'] == 0)
                                                {
                                                    $totalReservaAdd = ( ($item2->valorS * $item2->qpax ) +
                                                        ( ($item2->valorS / 2) * $item2->qchild ) ) ;

                                                }else{
                                                    $totalReservaAdd = ( ($dados1['valuenet'] * $item2->qpax ) +
                                                        ( ($dados1['valuenet'] / 2) * $item2->qchild )  );
                                                }
                                                $total1 = $total1 + $totalReservaAdd

                                                ?>
                                                <tr>
                                                    <td>
                                                        <form method="post" target="_blank" action="./informacoes-reserva.php">
                                                            <input type="hidden" name="voucher" id="voucher" value="<?php echo($item2->numbervoucher); ?>" >
                                                            <button type="submit" style="background-color: transparent; border: none;">
                                                                <?php echo($item2->numbervoucher); ?>
                                                            </button>
                                                        </form>
                                                    </td>
                                                    <td><?php echo($item2->pax); ?></td>
                                                    <td><?php echo("R$".number_format($totalReservaAdd, 2, ",", ".")); ?></td>
                                                </tr>
                                            <?php  }?>
                                        <?php }?>
                                    <?php }?>
                              </tbody>
                          </table>
                          <form action="inicio-fim-fatura.php" method="post">
                              <input type="hidden" name="idcliente" value="<?php echo($idcliente); ?>" >
                              <input type="hidden" name="total"     value="<?php echo($total + $total1); ?>" >
                              <input type="hidden" name="credito"     value="<?php echo($totalCredito); ?>" >
                              <input type="hidden" name="inicio"     value="<?php echo($datainicio); ?>" >
                              <input type="hidden" name="fim"     value="<?php echo($datafinal); ?>" >
                              <input type="hidden" name="statusatual"     value="<?php echo($ststus); ?>" >
                              <button style="margin-top: 20px;" type="submit" name="todosVoucher" id="todosVoucher" class="btn btn-success btn-large btn-block">
                                Cadastrar Fatura</button>
                          </form>
                      <?php }?>
                    </div>
                  </div>
                </div>
                <div class="card">
                  <div class="card-header" id="headingTwo">
                    <h5 class="mb-0">
                      <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseTwo"
                       aria-expanded="false" aria-controls="collapseTwo">
                        Visualizar Faturas Cadastradas
                      </button>
                    </h5>
                  </div>
                  <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionExample">
                    <div class="card-body">
                        <div class="table-reponsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Nº</th>
                                        <th>Cliente</th>
                                        <th>Total</th>
                                        <th>Crédito</th>
                                        <th>De</th>
                                        <th>Até</th>
                                        <th>Situaçao</th>
                                        <th>#</th>
                                        <th>#</th>
                                    </tr>
                                </thead>
                                <tbody>
                                  <?php if( $contadorFaturas > 0 ){ ?>

                                    <?php while( $registro = $minhasFaturas->fetch(PDO::FETCH_ASSOC) ){ ?>
                                      <tr>
                                          <td><?php echo( $registro['id'] ); ?></td>
                                          <td><?php echo( $registro['fullname'] ); ?></td>
                                          <td><?php echo("R$". number_format( $registro['tarifa'],2, ",", "." )  ); ?></td>
                                          <td><?php echo("R$". number_format( $registro['credito'],2, ",", "." )  ); ?></td>
                                          <td><?php echo( date("d-m-Y", strtotime( $registro['dateinput'] ) )  ); ?></td>
                                          <td><?php echo( date("d-m-Y", strtotime( $registro['dateoutput'] ) )  ); ?></td>
                                          <?php if( $registro['situacao'] == 1  ){ ?>
                                              <td>Ativo</td>
                                          <?php } else{ ?>
                                              <td>Inativo</td>
                                          <?php }?>
                                         <td>
                                            <form action="./editar-fatura.php" method="post" target="_blank" >
                                                <input type="hidden" name="idfatura" value="<?php echo( $registro['id'] ); ?>">
                                                <button name="editar" style="backgroud:transparent;border:none;color:black;" type="submit">Editar</button>
                                            </form>
                                         </td>
                                          <td>
                                              <form action="./relatorio/pdf-relatorio-cliente-reserva.php" method="post" target="_blank">
                                                  <input type="hidden" name="cliente" value="<?php echo( $registro['idcliente'] ); ?>" >
                                                  <input type="hidden" name="periodoinicial" value="<?php echo( $registro['dateinput'] ); ?>" >
                                                  <input type="hidden" name="periodofinal" value="<?php echo( $registro['dateoutput'] ); ?>" >
                                                  <input type="hidden" name="tarifa" value="<?php echo( $registro['tarifa'] ); ?>" >
                                                  <input type="hidden" name="status" value="<?php echo( $registro['status'] ); ?>" >
                                                  <button style="backgroud:transparent;border:none;color:black;" type="submit">Gerar Fatura</button>
                                              </form>
                                          </td>
                                      </tr>
                                    <?php }?>

                                  <?php } else{?>
                                    <div class="alert alert-success" role="alert">Não possível encontar faturas</div>
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

    </div>
</div>
<?php require_once ('footer.php'); ?>
