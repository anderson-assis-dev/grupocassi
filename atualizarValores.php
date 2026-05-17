<?php
$pdo = new PDO("mysql:host=grupocassi.vpshost3690.mysql.dbaas.com.br;dbname=grupocassi", "grupocassi", "A@nderson10");

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$data = date("Y-m-d");
$sql = "select * from `ct_reserva` r where  data_alteracao > data_integracao " ;

echo($sql);

$reservas = $pdo->prepare($sql);

$reservas->execute();

$dadosGerais = $reservas->fetchAll(PDO::FETCH_CLASS);

foreach ($dadosGerais as $item)

{

    $total = $item->valueservice * $item->qtdpax  + ( ($item->valueservice / 2) * $item->qtdchild ) ;



    $buscaCredito1 = $pdo->prepare('SELECT sum(valuecredit) as totalpago, numbervoucher FROM `ct_createfaturacredit`  where `numbervoucher` = :voucher');

    $buscaCredito1->execute( array(":voucher" => $item->numbervoucher ) );

    $registroCredito1 = $buscaCredito1->fetch(PDO::FETCH_ASSOC);



    $busta_total_servico2 = $pdo->prepare('SELECT sum(valueservice * qpax +((valueservice/2) * qchild)) as total2 FROM `ct_recentlyadd` where idrecently = :id');

    $busta_total_servico2->execute(array(":id"=> $item->id));

    $data_total2 = $busta_total_servico2->fetch(PDO::FETCH_ASSOC);


    //echo("Total 1 ".$total." total 2 ".$data_total2['total2']."voucher ".$item->numbervoucher );	
    $updateReserva1 = $pdo->prepare("UPDATE `ct_reserva` SET `totalservico` = :totalservico,`totalcredito` = :totalcredito, `fl_total_atualizada` = 1, data_integracao = now() WHERE `ct_reserva`.`numbervoucher` = :nv ");

    $updateReserva1->execute(array(":totalservico" => $total+$data_total2['total2'], ":totalcredito" => $registroCredito1['totalpago'] ,":nv" => $item->numbervoucher ));

    //echo("Total Pago ".$registroCredito1['totalpago']."<br>"." Voucher ".$item->numbervoucher." total da reserva ".($total+$data_total2['total2'])."<br><br>");

}