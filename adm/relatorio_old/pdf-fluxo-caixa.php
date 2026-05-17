<?php
require_once( '../.././config.php' );
ob_start();
define('MPDP_PATH', 'MPDF54/');
$idcliente  = $_POST['cliente'];
$datainicio = $_POST['vencimentoinicial'];
$datafim    = $_POST['vencimentofinal'];
$tipo       = $_POST['tiporelatorio'];
$empresa    = $_POST['empresa'];
$conta      = $_POST['conta'];
if( $tipo == 0 )
{
    if( $empresa == 0 )
    {
        if($idcliente == 0 )
        {
            if( $conta == 0 )
            {
                $relatorioCaixa = $pdo->prepare(
                    "select c.datevencimento as vencimento, c.descricao ,cc.`name` as conta, cli.fullname as favorecido, c.valor, c.idtipo,c.idstatus,s.nameinvoice, 
                               c.nome,em.fullname as empresa from `ct_caixa` c left join `ct_currentaccount` cc on cc.id = c.idconta left join `ct_fornecedor` cli on 
                               cli.id = c.idcliente left join ct_empresa em on em.id = c.idempresa left join ct_statusinvoice s on s.id = c.idstatus 
                               where c.dataabertura >= :inicio and c.dataabertura <= :fim  order by cli.fullname");
                $relatorioCaixa->execute( array(":inicio" => $datainicio, ":fim" => $datafim) );
            }else{
                $relatorioCaixa = $pdo->prepare(
                    "select c.datevencimento as vencimento, c.descricao ,cc.`name` as conta, cli.fullname as favorecido, c.valor, c.idtipo,c.idstatus,s.nameinvoice, 
                               c.nome,em.fullname as empresa from `ct_caixa` c left join `ct_currentaccount` cc on cc.id = c.idconta left join `ct_fornecedor` cli on 
                               cli.id = c.idcliente left join ct_empresa em on em.id = c.idempresa left join ct_statusinvoice s on s.id = c.idstatus 
                               where c.dataabertura >= :inicio and c.dataabertura <= :fim and c.idconta = :idconta  order by cli.fullname");
                $relatorioCaixa->execute( array(":inicio" => $datainicio, ":fim" => $datafim, ":idconta" => $conta) );
            }

        }else
        {
            if( $conta == 0 )
            {
                $relatorioCaixa = $pdo->prepare(
                    "select c.datevencimento as vencimento, c.descricao ,cc.`name` as conta, cli.fullname as favorecido, c.valor, c.idtipo,c.idstatus,s.nameinvoice,c.nome,
                               em.fullname as empresa from `ct_caixa` c left join `ct_currentaccount` cc on cc.id = c.idconta left join `ct_fornecedor` cli 
                               on cli.id = c.idcliente left join ct_empresa em on em.id = c.idempresa left join ct_statusinvoice s on s.id = c.idstatus 
                               where c.dataabertura >= :inicio and c.dataabertura <= :fim and c.idcliente = :cliente");
                $relatorioCaixa->execute( array(":inicio" => $datainicio, ":fim" => $datafim, ":cliente" => $idcliente) );
            }else{
                $relatorioCaixa = $pdo->prepare(
                    "select c.datevencimento as vencimento, c.descricao ,cc.`name` as conta, cli.fullname as favorecido, c.valor, c.idtipo,c.idstatus,s.nameinvoice,c.nome,
                               em.fullname as empresa from `ct_caixa` c left join `ct_currentaccount` cc on cc.id = c.idconta left join `ct_fornecedor` cli 
                               on cli.id = c.idcliente left join ct_empresa em on em.id = c.idempresa left join ct_statusinvoice s on s.id = c.idstatus 
                               where c.dataabertura >= :inicio and c.dataabertura <= :fim and c.idcliente = :cliente and c.idconta = :idconta");
                $relatorioCaixa->execute( array(":inicio" => $datainicio, ":fim" => $datafim, ":cliente" => $idcliente, ":idconta" => $conta) );
            }

        }
    }else{
        if($idcliente == 0 )
        {
            if( $conta == 0 )
            {
                $relatorioCaixa = $pdo->prepare(
                    "select c.datevencimento as vencimento, c.descricao ,cc.`name` as conta, cli.fullname as favorecido, c.valor, c.idtipo, c.idstatus,s.nameinvoice,
                               c.nome,em.fullname as empresa from `ct_caixa` c left join `ct_currentaccount` cc on cc.id = c.idconta left join `ct_fornecedor` cli on 
                               cli.id = c.idcliente left join ct_empresa em on em.id = c.idempresa left join ct_statusinvoice s on s.id = c.idstatus
                               where c.dataabertura >= :inicio and c.dataabertura <= :fim and c.idempresa = :empresa order by cli.fullname");
                $relatorioCaixa->execute( array(":inicio" => $datainicio, ":fim" => $datafim, ":empresa" => $empresa) );
            }else{
                $relatorioCaixa = $pdo->prepare(
                    "select c.datevencimento as vencimento, c.descricao ,cc.`name` as conta, cli.fullname as favorecido, c.valor, c.idtipo, c.idstatus,s.nameinvoice,
                               c.nome,em.fullname as empresa from `ct_caixa` c left join `ct_currentaccount` cc on cc.id = c.idconta left join `ct_fornecedor` cli on 
                               cli.id = c.idcliente left join ct_empresa em on em.id = c.idempresa left join ct_statusinvoice s on s.id = c.idstatus
                               where c.dataabertura >= :inicio and c.dataabertura <= :fim and c.idempresa = :empresa and c.idconta = :idconta order by cli.fullname");
                $relatorioCaixa->execute( array(":inicio" => $datainicio, ":fim" => $datafim, ":empresa" => $empresa, ":idconta" => $conta) );
            }

        }else
        {
            if( $conta == 0 )
            {
                $relatorioCaixa = $pdo->prepare(
                    "select c.datevencimento as vencimento, c.descricao ,cc.`name` as conta, cli.fullname as favorecido, c.valor, c.idtipo, c.idstatus, s.nameinvoice,
                               c.nome,em.fullname as empresa from `ct_caixa` c left join `ct_currentaccount` cc on cc.id = c.idconta left join `ct_fornecedor` cli on 
                               cli.id = c.idcliente left join ct_empresa em on em.id = c.idempresa left join ct_statusinvoice s on s.id = c.idstatus 
                               where c.dataabertura >= :inicio and c.dataabertura <= :fim and c.idcliente = :cliente and c.idempresa = :empresa ");
                $relatorioCaixa->execute( array(":inicio" => $datainicio, ":fim" => $datafim, ":cliente" => $idcliente,
                    ":empresa" => $empresa) );
            }else{
                $relatorioCaixa = $pdo->prepare(
                    "select c.datevencimento as vencimento, c.descricao ,cc.`name` as conta, cli.fullname as favorecido, c.valor, c.idtipo, c.idstatus, s.nameinvoice,
                               c.nome,em.fullname as empresa from `ct_caixa` c left join `ct_currentaccount` cc on cc.id = c.idconta left join `ct_fornecedor` cli on 
                               cli.id = c.idcliente left join ct_empresa em on em.id = c.idempresa left join ct_statusinvoice s on s.id = c.idstatus 
                               where c.dataabertura >= :inicio and c.dataabertura <= :fim and c.idcliente = :cliente and c.idempresa = :empresa and c.idconta = :idconta ");
                $relatorioCaixa->execute( array(":inicio" => $datainicio, ":fim" => $datafim, ":cliente" => $idcliente, ":empresa" => $empresa, ":idconta" => $conta) );
            }

        }
    }



}elseif ($tipo == 1)
{
    if( $empresa == 0 )
    {
        $relatorioCaixa = $pdo->prepare("select forne.fullname as favorecido, sum(c.valor) as valor, c.idcliente, em.fullname as empresa
    from `ct_caixa` c left join `ct_currentaccount` cc on cc.id = c.idconta left join `ct_fornecedor` forne on forne.id = c.idcliente left join ct_empresa em 
    on em.id = c.idempresa where c.dataabertura >= :inicio and c.dataabertura <= :fim group by forne.fullname");
        $relatorioCaixa->execute( array(":inicio" => $datainicio, ":fim" => $datafim) );
    }else{
        $relatorioCaixa = $pdo->prepare("select cli.fullname as favorecido, sum(c.valor) as valor, c.idcliente, em.fullname as empresa
    from `ct_caixa` c left join `ct_currentaccount` cc on cc.id = c.idconta left join `ct_fornecedor` cli on cli.id = c.idcliente left join ct_empresa em 
    on em.id = c.idempresa where c.dataabertura >= :inicio and c.dataabertura <= :fim  and c.idempresa = :empresa group by cli.fullname");
        $relatorioCaixa->execute( array(":inicio" => $datainicio, ":fim" => $datafim, ":empresa" => $empresa) );
    }


}
$total        = 0;-
$totalDebito  = 0;
$totalCredito = 0;
$dadosRelatorioCaixa = $relatorioCaixa->fetchAll(PDO::FETCH_CLASS);

ob_clean();
?>
<?php if($tipo == 0 ){ ?>
    <?php if( empty( $dadosRelatorioCaixa ) ){ ?>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Fluxo de Caixa</title>
            <link rel="stylesheet" href="materialize.min.css" >
        </head>
        <style>
            table{font-size: 10px;}
            th, td{border: 1px solid #ddd; padding: 8px;}
        </style>
        <body>
        <div class="container">
            <img style="width: 700px;" id="logo" src="../../images/logo.png"/>
            <h5 align="center"><?php echo( ('Não encontramos registros para as informações inseridas.') ) ?></h5>
        </div>
        </body>
        </html>
    <?php } else {?>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Fluxo de Caixa</title>
            <link rel="stylesheet" href="materialize.min.css" >
        </head>
        <style>
            table{font-size: 10px;}
            th, td{border: 1px solid #ddd; padding: 8px;}
        </style>
        <body>
        <div class="container">
            <img style="width: 700px;" id="logo" src="../../images/logo.png"/>
            <p style="font-size: 7px; font-weight: bold;"><?php echo("Impresso em:". date("d-m-Y H:i:s") ) ?></p>
            <div class="row">
                <div class="col-lg-12">
                    <p>Fluxo de Caixa - (<?php echo (("de ".strtoupper( strftime( "%d de %B", strtotime( $datainicio ) ) ).
                            " até ".strtoupper( strftime( "%d de %B", strtotime( $datafim ) ) ) )); ?>)
                    </p>
                    <hr>
                    <table class="highlight">
                        <thead>
                        <tr>
                            <th>VENCI</th>
                            <th>CONTA</th>
                            <th>TIPO</th>
                            <th>NOME</th>
                            <th>DOC</th>
                            <th>EMPRESA</th>
                            <th>FAVORECIDO</th>
                            <th>SITUACAO</th>
                            <th>VALOR</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($dadosRelatorioCaixa as $item){
                            $total = $total + $item->valor;
                            if($item->idtipo == 1)
                            {
                                $nome_tipo = 'CREDITO';
                            }elseif ($item->idtipo == 2)
                            {
                                $totalDebito = $totalDebito + $item->valor;
                                $nome_tipo = 'DEBITO';
                            }
                            if( $item->idstatus == 3 and $item->idtipo == 1 )
                            {
                                $totalCredito = $totalCredito + $item->valor;
                            }
                            ?>
                            <tr>
                                <td><?php echo( date( "d-m-Y", strtotime( $item->vencimento ) ) ); ?></td>
                                <td><?php echo( ( $item->conta ) ); ?></td>
                                <td><?php echo( ( $nome_tipo ) ); ?></td>
                                <td><?php echo( ( $item->nome ) ); ?></td>
                                <td><?php echo( ( $item->descricao ) ); ?></td>
                                <td><?php echo( ( $item->empresa ) ); ?></td>
                                <td><?php echo( utf8_encode( $item->favorecido ) ); ?></td>
                                <td><?php echo( ( $item->nameinvoice ) ); ?></td>
                                <td><?php
                                    if($item->valor == ''){
                                        echo( "R$ ".number_format(0, 2, ",", ".") );
                                    }else{
                                        echo( "R$ ".number_format($item->valor, 2, ",", ".") );

                                    }
                                    ?>
                                </td>
                            </tr>

                        <?php }?>
                        </tbody>
                    </table>
                    <table class="highlight">
                        <thead>
                        <tr>
                            <th>TOTAL GERAL</th>
                            <th>TOTAL <?php echo(('CRÉDITO')) ?></th>
                            <th>TOTAL <?php echo(('DÉBITO')) ?></th>
                            <th>A CONFERIR</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td><?php echo( "R$ ".number_format($total, 2, ",", ".") ); ?></td>
                            <td><?php echo( "R$ ".number_format($totalCredito, 2, ",", ".") ); ?></td>
                            <td><?php echo( "R$ ".number_format($totalDebito, 2, ",", ".") ); ?></td>
                            <td><?php echo( "R$ ".number_format( $total - ($totalCredito+$totalDebito), 2, ",", ".") ); ?></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        </body>
        </html>
    <?php }?>

<?php } elseif( $tipo == 1 ){?>
    <?php if( empty( $dadosRelatorioCaixa ) ){ ?>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Fluxo de Caixa</title>
            <link rel="stylesheet" href="materialize.min.css" >
        </head>
        <style>
            table{font-size: 10px;}
            th, td{border: 1px solid #ddd; padding: 8px;}
        </style>
        <body>
        <div class="container">
            <img style="width: 700px;" id="logo" src="../../images/logo.png"/>
            <h5 align="center"><?php echo( ('Não encontramos registros para as informações inseridas.') ) ?></h5>
        </div>
        </body>
        </html>
    <?php } else { ?>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Fluxo de Caixa</title>
            <link rel="stylesheet" href="materialize.min.css" >
        </head>
        <style>
            table{font-size: 10px;}
            th, td#valido{border: 1px solid #ddd; padding: 8px;}
        </style>
        <body>
        <div class="container">
            <img style="width: 700px;" id="logo" src="../../images/logo.png"/>
            <p style="font-size: 7px; font-weight: bold;"><?php echo("Impresso em:". date("d-m-Y H:i:s") ) ?></p>
            <div class="row">
                <div class="col-lg-12">
                    <p>Fluxo de Caixa Por Fonecedor - (<?php echo("de ".strtoupper( strftime( "%d de %B", strtotime( $datainicio ) ) ).
                            " ate ".strtoupper( strftime( "%d de %B", strtotime( $datafim ) ) ) ); ?>)
                    </p>
                    <hr>
                    <table class="highlight">
                        <thead>
                        <tr>
                            <th>Vencimento</th>
                            <th>Conta Corrente</th>
                            <th>Documento</th>
                            <th>Empresa</th>
                            <th>Favorecido</th>
                            <th>Valor</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($dadosRelatorioCaixa as $item1)
                        {
                            $caixaPorClien = $pdo->prepare(
                                "select c.datevencimento as vencimento, c.descricao ,cc.`name` as conta, cli.fullname as favorecido,
                                  c.valor, em.fullname as empresa from `ct_caixa` c left join `ct_currentaccount` cc on cc.id = c.idconta left join `ct_fornecedor` cli on cli.id = c.idcliente 
                                 left join ct_empresa em on em.id = c.idempresa where c.datevencimento >= :inicio and c.datevencimento <= :fim and c.idcliente = :cliente and descricao not like :dados ");
                            $caixaPorClien->execute( array(":inicio" => $datainicio, ":fim" => $datafim,
                                ":cliente" => $item1->idcliente, ":dados" => '%Pagamento de comiss%') );
                            $dadosPorCliente = $caixaPorClien->fetchAll(PDO::FETCH_CLASS);

                            ?>
                            <?php foreach ($dadosPorCliente as $item){

                            $total = $total + $item->valor;

                            ?>
                            <tr>
                                <td id="valido" ><?php echo( date( "d-m-Y", strtotime( $item->vencimento ) ) ); ?></td>
                                <td id="valido"><?php echo( ( $item->conta ) ); ?></td>
                                <td id="valido"><?php echo( ( $item->descricao ) ); ?></td>
                                <td id="valido"><?php echo( ( $item->empresa ) ); ?></td>
                                <td id="valido"><?php echo( ( $item->favorecido ) ); ?></td>
                                <td id="valido"><?php echo( "R$ ".number_format($item->valor, 2, ",", ".") ); ?></td>
                            </tr>
                        <?php }?>
                            <tr style="text-align: center;">
                                <td style="text-align: center;" >
                                    <?php echo( "Resultado do Fornecedor: R$ ".number_format($item1->valor, 2, ",", ".") ); ?>
                                </td>
                            </tr>
                        <?php }?>
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
        </body>
        </html>
    <?php }?>

<?php }?>

<script type="text/javascript">
    window.print();
</script>