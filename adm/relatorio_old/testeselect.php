<?php
/**
 * Created by PhpStorm.
 * User: Ander
 * Date: 11/03/2019
 * Time: 13:21
 */
?>
<?php
require_once( '../.././config.php' );
ob_start();
define('MPDP_PATH', 'MPDF54/');
$abertura        = '2020-01-01 00:00:00'; //$_POST['abertura']." 00:00:00";
$aberturaFinal   = "2020-01-15 23:59:59"; //$_POST['aberturafinal']." 23:59:59";
$responsavel     = 0; // $_POST['responsavel'];
$clausula        = '%posto%';
if($responsavel > 0)
{
    $informacoes = $pdo->prepare(
        'select * from `ct_audit` a left join `ct_createfatura` cf on cf.numbervoucher = a.voucher left join `ct_reserva` r on r.numbervoucher = a.voucher
left join `ct_cliente` c on c.id = r.idcliente left join `ct_usuario` u on u.id = a.idresponsible left join `ct_currentaccount` ca on ca.id = cf.idcurrentaccount
where a.description like :dados and a.`date` >= :inicio and a.`date` <= :fim and a.idresponsible = :por order by a.`date` ');
    $informacoes->execute(
        array(
            ":dados"    => '%fatura cadastrada%',
            ":inicio"   => $abertura,
            ":fim"      => $aberturaFinal,
            ":por"      => $responsavel
        ));
}else{
    $informacoes = $pdo->prepare(
        'select * from `ct_audit` a left join `ct_createfatura` cf on cf.numbervoucher = a.voucher left join `ct_reserva` r on r.numbervoucher = a.voucher
left join `ct_cliente` c on c.id = r.idcliente left join `ct_usuario` u on u.id = a.idresponsible left join `ct_currentaccount` ca on ca.id = cf.idcurrentaccount
where a.description like :dados and a.`date` >= :inicio and a.`date` <= :fim and cf.numberadd like :clausula order by a.`date`');
    $informacoes->execute(
        array(
            ":dados"    => '%fatura cadastrada%',
            ":inicio"   => $abertura,
            ":fim"      => $aberturaFinal,
            ":clausula" => $clausula
        ));
}
$registros = $informacoes->fetchAll(PDO::FETCH_CLASS);
$contador  = $informacoes->rowCount();

ob_clean();
?>
<style>
    th,td{font-size: 12px;}
</style>
<?php if ($contador > 0){  ?>
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta charset="utf-8">
        <title>Relatório de Baixa</title>
        <link rel="stylesheet" href="materialize.min.css">
    </head>
    <style>
        th, td{border: 1px solid #ddd; padding: 8px;}
    </style>
    <body>
    <div class="container">
        <img style="width: 700px; margin-left: 50px; " id="logo" src="../../images/logo.png"/>
        <hr>
        <p><?php echo( ( "Relatório de baixa: ".
                date("d/m/Y ", strtotime( $abertura ))." ate ".date("d/m/Y ", strtotime( $aberturaFinal )))); ?> </p><br>
        <p style="font-size: 9px; margin-top: -20px;">Impresso em: <?php echo(date("d/m/Y - H:i:s")); ?></p>
        <table class="highlight">
            <thead>
            <tr>
                <th>Em</th>
                <th>Por</th>
                <th>Voucher</th>
                <th>Cliente</th>
                <th>Informações</th>
                <th>Data de Pagamento</th>
                <th>Conta</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($registros as $item){ ?>
                <tr>
                    <td><?php echo( date("d-m-Y H:i", strtotime($item->date)) ) ?></td>
                    <td><?php echo( ( $item->firstname ) ); ?></td>
                    <td><?php echo( $item->voucher ) ?></td>
                    <td><?php echo( ( $item->namefantazia ) ); ?></td>
                    <td><?php echo( ( $item->numberadd ) ); ?></td>
                    <td><?php echo( date("d-m-Y ", strtotime($item->datepayment)) ) ?></td>
                    <td><?php echo( utf8_encode( $item->name ) ); ?></td>
                </tr>
            <?php }?>
            </tbody>
        </table>
    </div>
    </body>
    </html>
<?php } else { ?>
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta charset="utf-8">
        <title><?php echo( utf8_decode( "Relatório de Baixa" ) ); ?></title>
        <link rel="stylesheet" href="materialize.min.css">
    </head>
    <style>
        th, td{border: 1px solid #ddd; padding: 8px;}
        td#desc{font-weight: bold;}
    </style>
    <body>
    <div class="container">
        <img style="width: 700px; margin-left: 50px; " id="logo" src="../../images/logo.png"/>
        <hr>
        <p><?php echo( utf8_decode( "Relatório de baixa: ".
                date("d/m/Y ", strtotime( $abertura ))." ate ".date("d/m/Y ", strtotime( $aberturaFinal )))); ?> </p><br>
        <p style="font-size: 9px; margin-top: -20px;">Impresso em: <?php echo(date("d/m/Y - H:i:s")); ?></p>
        <h2>
            <?php echo( utf8_decode( "Não encontramos registros para os dados informados.")); ?>
        </h2>

    </div>
    </body>
    </html>
<?php }?>

<script type="text/javascript">
    window.print();
</script>

