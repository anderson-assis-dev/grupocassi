<?php

require_once('../config.php');
if($_POST['allproduct'] == 1)
{
    $buscar_horario = $pdo->prepare('select * from `ct_service_schedule` where morroaero = 1 order by `schedule`');
    $buscar_horario->execute();
    $dados = $buscar_horario->fetchAll(PDO::FETCH_CLASS);
    echo( json_encode($dados) );
}
elseif($_POST['allproduct'] == 2)
{
    $buscar_horario = $pdo->prepare('SELECT ss.idshedule, schedule FROM `ct_servico_horario` sh left join ct_service_schedule ss on sh.idschedule = ss.idshedule where sh.idservice = :idservice');
    $buscar_horario->execute(array(":idservice" => $_POST['idservice']));
    $dados = $buscar_horario->fetchAll(PDO::FETCH_CLASS);
    echo( json_encode($dados) );
}
elseif($_POST['allproduct'] == 0){
    $buscar_horario = $pdo->prepare('select * from `ct_service_schedule` order by `schedule`');
    $buscar_horario->execute();
    $dados = $buscar_horario->fetchAll(PDO::FETCH_CLASS);
    echo( json_encode($dados) );
}
