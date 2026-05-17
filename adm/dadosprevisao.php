<?php

require_once('../config.php');
$dateInput  = $_POST['inicio'];
$dateOutput = $_POST['fim'];
$usuariosCadastrados = $pdo->prepare('select u.id from ct_confirmacao c left join ct_usuario u on c.idoperador = u.id group by u.id');
$usuariosCadastrados->execute();
$listaUsusarios = $usuariosCadastrados->fetchAll(PDO::FETCH_CLASS);

if(isset($_POST['previsaomorro']))
{
    $previsao_tree = $pdo->prepare('SELECT * FROM `ct_servico` where fullname like :servico');
    $previsao_tree->execute(array(":servico" => 'morro%'));
    $data_previsao_tree = $previsao_tree->fetchAll(PDO::FETCH_CLASS);
    $treze_trinta_morro   = array();
    $seis_trinta_morro    = array();
    $oito_trinta_morro    = array();
    $nove_trinta_morro    = array();
    $dez_trinta_morro     = array();
    $doze_trinta_morro    = array();
    $quinze_trinta_morro  = array();
    foreach ($data_previsao_tree as $item)
    {
        if(isset($_POST['seis']))
        {
            $previsao_morro = $pdo->prepare('SELECT sum(r.qtdpax + r.qtdchild + r.qtdfree ) as total FROM `ct_reserva` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservico = :servico and r.idhorario = :horario and confirmacao = :previsto and r.idstatus <> 2');
            $previsao_morro->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 2, ":previsto" => 0));
            $data_previsao_morro = $previsao_morro->fetch(PDO::FETCH_ASSOC);
            $previsao_morro1 = $pdo->prepare('SELECT sum(r.qpax + r.qchild + r.qfree ) as total FROM `ct_recentlyadd` r left join `ct_reserva` re on re.id = r.idrecently where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservice = :servico and r.idschedule = :horario and confirmacao2 = :previsto and re.idstatus <> 2');
            $previsao_morro1->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 2, ":previsto" => 0));
            $data_previsao_morro1 = $previsao_morro1->fetch(PDO::FETCH_ASSOC);

            $previsao_morro_confirmacao = $pdo->prepare('SELECT sum(r.qtdpax + r.qtdchild + r.qtdfree ) as total FROM `ct_reserva` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservico = :servico and r.idhorario = :horario and confirmacao = :confirmado and r.idstatus <> 2');
            $previsao_morro_confirmacao->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 2, ":confirmado" => 1));
            $data_previsao_morro_confirmacao = $previsao_morro_confirmacao->fetch(PDO::FETCH_ASSOC);
            $previsao_morro1_confirmacao = $pdo->prepare('SELECT sum(r.qpax + r.qchild + r.qfree ) as total FROM `ct_recentlyadd` r left join `ct_reserva` re on re.id = r.idrecently where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservice = :servico and r.idschedule = :horario and confirmacao2 = :confirmado and re.idstatus <> 2');
            $previsao_morro1_confirmacao->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 2, ":confirmado" => 1));
            $data_previsao_morro1_confirmacao = $previsao_morro1_confirmacao->fetch(PDO::FETCH_ASSOC);

            if ($data_previsao_morro['total'] <> null or $data_previsao_morro_confirmacao['total'] <> null or $data_previsao_morro1['total'] <> null or $data_previsao_morro1_confirmacao['total'] <> null)
            {
                array_push($seis_trinta_morro, array("servico" => strtoupper($item->fullname)));
                array_push($seis_trinta_morro, array("previsto" => $data_previsao_morro['total']+$data_previsao_morro1['total']));
                array_push($seis_trinta_morro, array("confirmado" => $data_previsao_morro_confirmacao['total']+$data_previsao_morro1_confirmacao['total']));
            }

            foreach ($listaUsusarios as $item2)
            {
                $buscar_total_operador_confirmado = $pdo->prepare('select u.firstname, u.lastname,sum(c.totalpax) as total from ct_confirmacao c left join ct_usuario u on c.idoperador = u.id where c.`data` >= :inicio and c.`data` <= :fim and c.idhorario = :horario and c.idoperador = :operador and c.idservico = :servico');
                $buscar_total_operador_confirmado->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput,":horario" => 2, ":operador" => $item2->id, ":servico" => $item->id));
                $dados_operador = $buscar_total_operador_confirmado->fetch(PDO::FETCH_ASSOC);
                if( $dados_operador['firstname'] <> null )
                {
                    array_push($seis_trinta_morro, array("nomeoperador" => $dados_operador['firstname']." ".$dados_operador['lastname']));
                    array_push($seis_trinta_morro, array("totaloperador" => $dados_operador['total']));
                }
            }



        }
        elseif (isset($_POST['oito']))
        {
            $previsao_morro9 = $pdo->prepare('SELECT sum(r.qtdpax + r.qtdchild + r.qtdfree ) as total FROM `ct_reserva` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservico = :servico and r.idhorario = :horario and confirmacao = :previsto and r.idstatus <> 2');
            $previsao_morro9->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 4 , ":previsto" => 0));
            $data_previsao_morro9 = $previsao_morro9->fetch(PDO::FETCH_ASSOC);
            $previsao_morro9 = $pdo->prepare('SELECT sum(r.qpax + r.qchild + r.qfree ) as total FROM `ct_recentlyadd` r left join `ct_reserva` re on re.id = r.idrecently where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservice = :servico and r.idschedule = :horario and confirmacao2 = :previsto and re.idstatus <> 2');
            $previsao_morro9->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 4 , ":previsto" => 0));
            $data_previsao_morro91 = $previsao_morro9->fetch(PDO::FETCH_ASSOC);

            $previsao_morro9_confirmado = $pdo->prepare('SELECT sum(r.qtdpax + r.qtdchild + r.qtdfree ) as total FROM `ct_reserva` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservico = :servico and r.idhorario = :horario and confirmacao = :confirmado and r.idstatus <> 2');
            $previsao_morro9_confirmado->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 4, ":confirmado" => 1));
            $data_previsao_morro9_confirmado = $previsao_morro9_confirmado->fetch(PDO::FETCH_ASSOC);
            $previsao_morro9_confirmado = $pdo->prepare('SELECT sum(r.qpax + r.qchild + r.qfree ) as total FROM `ct_recentlyadd` r left join `ct_reserva` re on re.id = r.idrecently where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservice = :servico and r.idschedule = :horario and confirmacao2 = :confirmado and re.idstatus <> 2');
            $previsao_morro9_confirmado->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 4, ":confirmado" => 1));
            $data_previsao_morro91_confirmado = $previsao_morro9_confirmado->fetch(PDO::FETCH_ASSOC);
            if ($data_previsao_morro9['total'] <> null or $data_previsao_morro91['total'] <> null or $data_previsao_morro9_confirmado['total'] <> null or $data_previsao_morro91_confirmado['total'] <> null)  {
                array_push($oito_trinta_morro, array("servico" => strtoupper($item->fullname)));
                array_push($oito_trinta_morro, array("previsto" => $data_previsao_morro9['total']+ $data_previsao_morro91['total']));
                array_push($oito_trinta_morro, array("confirmado" => $data_previsao_morro9_confirmado['total']+ $data_previsao_morro91_confirmado['total']));
            }
            foreach ($listaUsusarios as $item2)
            {
                $buscar_total_operador_confirmado = $pdo->prepare('select u.firstname, u.lastname,sum(c.totalpax) as total from ct_confirmacao c left join ct_usuario u on c.idoperador = u.id where c.`data` >= :inicio and c.`data` <= :fim and c.idhorario = :horario and c.idoperador = :operador and c.idservico = :servico');
                $buscar_total_operador_confirmado->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput,":horario" => 4, ":operador" => $item2->id, ":servico" => $item->id));
                $dados_operador = $buscar_total_operador_confirmado->fetch(PDO::FETCH_ASSOC);
                if( $dados_operador['firstname'] <> null )
                {
                    array_push($oito_trinta_morro, array("nomeoperador" => $dados_operador['firstname']." ".$dados_operador['lastname']));
                    array_push($oito_trinta_morro, array("totaloperador" => $dados_operador['total']));
                }
            }
        }
        elseif (isset($_POST['novetrinta']))
        {
            $previsao_morro_nove_e_trinta01 = $pdo->prepare(
                'SELECT sum(r.qtdpax + r.qtdchild + r.qtdfree ) as total FROM `ct_reserva` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservico = :servico and r.idhorario = :horario and confirmacao = :previsto and r.idstatus <> 2');
            $previsao_morro_nove_e_trinta01->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 5 , ":previsto" => 0));
            $data_previsao_morro9_trinta = $previsao_morro_nove_e_trinta01->fetch(PDO::FETCH_ASSOC);
            $previsao_morro9_trinta = $pdo->prepare('SELECT sum(r.qpax + r.qchild + r.qfree ) as total FROM `ct_recentlyadd` r left join `ct_reserva` re on re.id = r.idrecently where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservice = :servico and r.idschedule = :horario and confirmacao2 = :previsto re.idstatus <> 2 ');
            $previsao_morro9_trinta->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 5 , ":previsto" => 0));
            $data_previsao_morro91_trinta = $previsao_morro9_trinta->fetch(PDO::FETCH_ASSOC);

            $previsao_morro9_trinta_confirmado = $pdo->prepare('SELECT sum(r.qtdpax + r.qtdchild + r.qtdfree ) as total FROM `ct_reserva` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservico = :servico and r.idhorario = :horario and confirmacao = :confirmado and r.idstatus <> 2 ');
            $previsao_morro9_trinta_confirmado->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 5, ":confirmado" => 1));
            $data_previsao_morro9_trinta_confirmado = $previsao_morro9_trinta_confirmado->fetch(PDO::FETCH_ASSOC);
            $previsao_morro91_trinta_confirmado = $pdo->prepare('SELECT sum(r.qpax + r.qchild + r.qfree ) as total FROM `ct_recentlyadd` r left join `ct_reserva` re on re.id = r.idrecently where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservice = :servico and r.idschedule = :horario and confirmacao2 = :confirmado re.idstatus <> 2');
            $previsao_morro91_trinta_confirmado->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 5, ":confirmado" => 1));
            $data_previsao_morro91_trinta_confirmado = $previsao_morro91_trinta_confirmado->fetch(PDO::FETCH_ASSOC);
            if ($data_previsao_morro9_trinta['total'] <> null or $data_previsao_morro91_trinta['total'] <> null or $data_previsao_morro9_trinta_confirmado['total'] <> null or $data_previsao_morro91_trinta_confirmado['total'] <> null)  {
                array_push($nove_trinta_morro, array("servico" => strtoupper($item->fullname)));
                array_push($nove_trinta_morro, array("previsto" => $data_previsao_morro9_trinta['total']+ $data_previsao_morro91_trinta['total']));
                array_push($nove_trinta_morro, array("confirmado" => $data_previsao_morro9_trinta_confirmado['total']+ $data_previsao_morro91_trinta_confirmado['total']));
            }
            foreach ($listaUsusarios as $item2)
            {
                $buscar_total_operador_confirmado = $pdo->prepare('select u.firstname, u.lastname,sum(c.totalpax) as total from ct_confirmacao c left join ct_usuario u on c.idoperador = u.id where c.`data` >= :inicio and c.`data` <= :fim and c.idhorario = :horario and c.idoperador = :operador and c.idservico = :servico');
                $buscar_total_operador_confirmado->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput,":horario" => 5, ":operador" => $item2->id, ":servico" => $item->id));
                $dados_operador = $buscar_total_operador_confirmado->fetch(PDO::FETCH_ASSOC);
                if( $dados_operador['firstname'] <> null )
                {
                    array_push($nove_trinta_morro, array("nomeoperador" => $dados_operador['firstname']." ".$dados_operador['lastname']));
                    array_push($nove_trinta_morro, array("totaloperador" => $dados_operador['total']));
                }
            }
        }
        elseif (isset($_POST['dez']))
        {
            $previsao_morror11 = $pdo->prepare('SELECT sum(r.qtdpax + r.qtdchild + r.qtdfree ) as total FROM `ct_reserva` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservico = :servico and r.idhorario = :horario and confirmacao = :previsto and r.idstatus <> 2');
            $previsao_morror11->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 6 , ":previsto" => 0));
            $data_previsao_morro11 = $previsao_morror11->fetch(PDO::FETCH_ASSOC);
            $previsao_morror11 = $pdo->prepare('SELECT sum(r.qpax + r.qchild + r.qfree ) as total FROM `ct_recentlyadd` r left join `ct_reserva` re on re.id = r.idrecently where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservice = :servico and r.idschedule = :horario and confirmacao2 = :previsto and re.idstatus <> 2');
            $previsao_morror11->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 6 , ":previsto" => 0));
            $data_previsao_morro111 = $previsao_morror11->fetch(PDO::FETCH_ASSOC);

            $previsao_morror11_confirmado = $pdo->prepare('SELECT sum(r.qtdpax + r.qtdchild + r.qtdfree ) as total FROM `ct_reserva` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservico = :servico and r.idhorario = :horario and confirmacao = :confirmado and r.idstatus <> 2');
            $previsao_morror11_confirmado->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 6 , ":confirmado" => 1));
            $data_previsao_morro11_confirmado = $previsao_morror11_confirmado->fetch(PDO::FETCH_ASSOC);
            $previsao_morror11_confirmado = $pdo->prepare('SELECT sum(r.qpax + r.qchild + r.qfree ) as total FROM `ct_recentlyadd` r left join `ct_reserva` re on re.id = r.idrecently where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservice = :servico and r.idschedule = :horario and confirmacao2 = :confirmado and re.idstatus <> 2');
            $previsao_morror11_confirmado->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 6 , ":confirmado" => 1));
            $data_previsao_morro111_confirmado = $previsao_morror11_confirmado->fetch(PDO::FETCH_ASSOC);
            if ($data_previsao_morro111['total'] <> null or $data_previsao_morro11['total'] <>  null or $data_previsao_morro111_confirmado['total'] <>  null or $data_previsao_morro11_confirmado['total'] <>  null)
            {
                array_push($dez_trinta_morro, array("servico" => strtoupper($item->fullname)));
                array_push($dez_trinta_morro, array("previsto" => $data_previsao_morro111['total']+$data_previsao_morro11['total']));
                array_push($dez_trinta_morro, array("confirmado" => $data_previsao_morro111_confirmado['total']+$data_previsao_morro11_confirmado['total']));
            }
            foreach ($listaUsusarios as $item2)
            {
                $buscar_total_operador_confirmado = $pdo->prepare('select u.firstname, u.lastname,sum(c.totalpax) as total from ct_confirmacao c left join ct_usuario u on c.idoperador = u.id where c.`data` >= :inicio and c.`data` <= :fim and c.idhorario = :horario and c.idoperador = :operador and c.idservico = :servico');
                $buscar_total_operador_confirmado->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput,":horario" => 6, ":operador" => $item2->id, ":servico" => $item->id));
                $dados_operador = $buscar_total_operador_confirmado->fetch(PDO::FETCH_ASSOC);
                if( $dados_operador['firstname'] <> null )
                {
                    array_push($dez_trinta_morro, array("nomeoperador" => $dados_operador['firstname']." ".$dados_operador['lastname']));
                    array_push($dez_trinta_morro, array("totaloperador" => $dados_operador['total']));
                }
            }
        }
        elseif (isset($_POST['doze']))
        {
            $previsao_morror12 = $pdo->prepare('SELECT sum(r.qtdpax + r.qtdchild + r.qtdfree ) as total FROM `ct_reserva` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservico = :servico and r.idhorario = :horario and confirmacao = :previsto and r.idstatus <> 2');
            $previsao_morror12->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 8 , ":previsto" => 0));
            $data_previsao_morro12 = $previsao_morror12->fetch(PDO::FETCH_ASSOC);
            $previsao_morror12 = $pdo->prepare('SELECT sum(r.qpax + r.qchild + r.qfree ) as total FROM `ct_recentlyadd` r left join `ct_reserva` re on re.id = r.idrecently where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservice = :servico and r.idschedule = :horario and confirmacao2 = :previsto and re.idstatus <> 2');
            $previsao_morror12->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 8 , ":previsto" => 0));
            $data_previsao_morro112 = $previsao_morror12->fetch(PDO::FETCH_ASSOC);

            $previsao_morror12_confirmado = $pdo->prepare('SELECT sum(r.qtdpax + r.qtdchild + r.qtdfree ) as total FROM `ct_reserva` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservico = :servico and r.idhorario = :horario and confirmacao = :confirmado and r.idstatus <> 2');
            $previsao_morror12_confirmado->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 8 , ":confirmado" => 1));
            $data_previsao_morro12_confirmado = $previsao_morror12_confirmado->fetch(PDO::FETCH_ASSOC);
            $previsao_morror12_confirmado = $pdo->prepare('SELECT sum(r.qpax + r.qchild + r.qfree ) as total FROM `ct_recentlyadd` r left join `ct_reserva` re on re.id = r.idrecently where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservice = :servico and r.idschedule = :horario and confirmacao2 = :confirmado and re.idstatus <> 2');
            $previsao_morror11_confirmado->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 8 , ":confirmado" => 1));
            $data_previsao_morro112_confirmado = $previsao_morror12_confirmado->fetch(PDO::FETCH_ASSOC);
            if ($data_previsao_morro112['total'] <> null or $data_previsao_morro12['total'] <>  null or $data_previsao_morro112_confirmado['total'] <>  null or $data_previsao_morro12_confirmado['total'] <>  null)
            {
                array_push($doze_trinta_morro, array("servico" => strtoupper($item->fullname)));
                array_push($doze_trinta_morro, array("previsto" => $data_previsao_morro112['total']+$data_previsao_morro12['total']));
                array_push($doze_trinta_morro, array("confirmado" => $data_previsao_morro112_confirmado['total']+$data_previsao_morro12_confirmado['total']));
            }
            foreach ($listaUsusarios as $item2)
            {
                $buscar_total_operador_confirmado = $pdo->prepare('select u.firstname, u.lastname,sum(c.totalpax) as total from ct_confirmacao c left join ct_usuario u on c.idoperador = u.id where c.`data` >= :inicio and c.`data` <= :fim and c.idhorario = :horario and c.idoperador = :operador and c.idservico = :servico');
                $buscar_total_operador_confirmado->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput,":horario" => 8, ":operador" => $item2->id, ":servico" => $item->id));
                $dados_operador = $buscar_total_operador_confirmado->fetch(PDO::FETCH_ASSOC);
                if( $dados_operador['firstname'] <> null )
                {
                    array_push($doze_trinta_morro, array("nomeoperador" => $dados_operador['firstname']." ".$dados_operador['lastname']));
                    array_push($doze_trinta_morro, array("totaloperador" => $dados_operador['total']));
                }
            }
        }
        elseif (isset($_POST['treze']))
        {
            $previsao_morro13 = $pdo->prepare('SELECT sum(r.qtdpax + r.qtdchild + r.qtdfree ) as total FROM `ct_reserva` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservico = :servico and r.idhorario = :horario and confirmacao = :previsto and r.idstatus <> 2');
            $previsao_morro13->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 9, ":previsto" => 0));
            $data_previsao_morro13 = $previsao_morro13->fetch(PDO::FETCH_ASSOC);
            $previsao_morro131 = $pdo->prepare('SELECT sum(r.qpax + r.qchild + r.qfree ) as total FROM `ct_recentlyadd` r left join `ct_reserva` re on re.id = r.idrecently where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservice = :servico and r.idschedule = :horario and confirmacao2 = :previsto and re.idstatus <> 2');
            $previsao_morro131->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 9, ":previsto" => 0));
            $data_previsao_morro131 = $previsao_morro131->fetch(PDO::FETCH_ASSOC);
            $previsao_morro13_confirmado = $pdo->prepare('SELECT sum(r.qtdpax + r.qtdchild + r.qtdfree ) as total FROM `ct_reserva` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservico = :servico and r.idhorario = :horario and confirmacao = :confirmado and r.idstatus <> 2');
            $previsao_morro13_confirmado->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 9, ":confirmado" => 1));
            $data_previsao_morro13_confirmado = $previsao_morro13_confirmado->fetch(PDO::FETCH_ASSOC);
            $previsao_morro131_confirmado = $pdo->prepare('SELECT sum(r.qpax + r.qchild + r.qfree ) as total FROM `ct_recentlyadd` r left join `ct_reserva` re on re.id = r.idrecently where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservice = :servico and r.idschedule = :horario and confirmacao2 = :confirmado and re.idstatus <> 2');
            $previsao_morro131_confirmado->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 9, ":confirmado" => 1));
            $data_previsao_morro131_confirmado = $previsao_morro131_confirmado->fetch(PDO::FETCH_ASSOC);
            if ($data_previsao_morro131['total'] <> null or $data_previsao_morro13['total'] <> null or $data_previsao_morro131_confirmado['total'] <> null or $data_previsao_morro13_confirmado['total'] <> null) {
                array_push($treze_trinta_morro, array("servico" => strtoupper($item->fullname)));
                array_push($treze_trinta_morro, array("previsto" => $data_previsao_morro131['total']+$data_previsao_morro13['total']));
                array_push($treze_trinta_morro, array("confirmado" => $data_previsao_morro131_confirmado['total']+$data_previsao_morro13_confirmado['total']));
            }
            foreach ($listaUsusarios as $item2)
            {
                $buscar_total_operador_confirmado = $pdo->prepare('select u.firstname, u.lastname,sum(c.totalpax) as total from ct_confirmacao c left join ct_usuario u on c.idoperador = u.id where c.`data` >= :inicio and c.`data` <= :fim and c.idhorario = :horario and c.idoperador = :operador and c.idservico = :servico');
                $buscar_total_operador_confirmado->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput,":horario" => 9, ":operador" => $item2->id, ":servico" => $item->id));
                $dados_operador = $buscar_total_operador_confirmado->fetch(PDO::FETCH_ASSOC);
                if( $dados_operador['firstname'] <> null )
                {
                    array_push($treze_trinta_morro, array("nomeoperador" => $dados_operador['firstname']." ".$dados_operador['lastname']));
                    array_push($treze_trinta_morro, array("totaloperador" => $dados_operador['total']));
                }
            }
        }
        elseif(isset($_POST['quinze']))
        {
            $previsao_morro16 = $pdo->prepare('SELECT sum(r.qtdpax + r.qtdchild + r.qtdfree ) as total FROM `ct_reserva` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservico = :servico and r.idhorario = :horario and confirmacao = :previsto and r.idstatus <> 2');
            $previsao_morro16->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 11, ":previsto" => 0));
            $data_previsao_morro16 = $previsao_morro16->fetch(PDO::FETCH_ASSOC);
            $previsao_morro161 = $pdo->prepare('SELECT sum(r.qpax + r.qchild + r.qfree ) as total FROM `ct_recentlyadd` r left join `ct_reserva` re on re.id = r.idrecently where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservice = :servico and r.idschedule = :horario and confirmacao2 = :previsto and re.idstatus <> 2 ');
            $previsao_morro161->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 11, ":previsto" => 0));
            $data_previsao_morro161 = $previsao_morro161->fetch(PDO::FETCH_ASSOC);
            $previsao_morro16_confirmado = $pdo->prepare('SELECT sum(r.qtdpax + r.qtdchild + r.qtdfree ) as total FROM `ct_reserva` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservico = :servico and r.idhorario = :horario and confirmacao = :confirmado and r.idstatus <> 2');
            $previsao_morro16_confirmado->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 11, ":confirmado" => 1));
            $data_previsao_morro16_confirmado = $previsao_morro16_confirmado->fetch(PDO::FETCH_ASSOC);
            $previsao_morro161_confirmado = $pdo->prepare('SELECT sum(r.qpax + r.qchild + r.qfree ) as total FROM `ct_recentlyadd` r left join `ct_reserva` re on re.id = r.idrecently where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservice = :servico and r.idschedule = :horario and confirmacao2 = :confirmado and re.idstatus <> 2');
            $previsao_morro161_confirmado->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 11, ":confirmado" => 1));
            $data_previsao_morro161_confirmado = $previsao_morro161_confirmado->fetch(PDO::FETCH_ASSOC);

            if ($data_previsao_morro161['total'] <> null or $data_previsao_morro16['total'] <> null or $data_previsao_morro161_confirmado['total'] <> null or $data_previsao_morro16_confirmado['total'] <> null ) {
                array_push($quinze_trinta_morro, array("servico" => strtoupper($item->fullname)));
                array_push($quinze_trinta_morro, array("previsto" => $data_previsao_morro161['total']+$data_previsao_morro16['total']));
                array_push($quinze_trinta_morro, array("confirmado" => $data_previsao_morro161_confirmado['total']+$data_previsao_morro16_confirmado['total']));
            }
            foreach ($listaUsusarios as $item2)
            {
                $buscar_total_operador_confirmado = $pdo->prepare('select u.firstname, u.lastname,sum(c.totalpax) as total from ct_confirmacao c left join ct_usuario u on c.idoperador = u.id where c.`data` >= :inicio and c.`data` <= :fim and c.idhorario = :horario and c.idoperador = :operador and c.idservico = :servico');
                $buscar_total_operador_confirmado->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput,":horario" => 11, ":operador" => $item2->id, ":servico" => $item->id));
                $dados_operador = $buscar_total_operador_confirmado->fetch(PDO::FETCH_ASSOC);
                if( $dados_operador['firstname'] <> null )
                {
                    array_push($quinze_trinta_morro, array("nomeoperador" => $dados_operador['firstname']." ".$dados_operador['lastname']));
                    array_push($quinze_trinta_morro, array("totaloperador" => $dados_operador['total']));
                }
            }
        }

    }
    if(isset($_POST['seis']))
    {
        echo( json_encode($seis_trinta_morro) );
    }
    elseif (isset($_POST['oito']))
    {
        echo( json_encode($oito_trinta_morro) );
    }
    elseif (isset($_POST['novetrinta']))
    {
        echo( json_encode($nove_trinta_morro) );
    }
    elseif (isset($_POST['dez']))
    {
        echo( json_encode($dez_trinta_morro) );
    }
    elseif (isset($_POST['doze']))
    {
        echo( json_encode($doze_trinta_morro) );
    }
    elseif (isset($_POST['treze']))
    {
        echo( json_encode($treze_trinta_morro) );
    }
    elseif (isset($_POST['quinze']))
    {
        echo( json_encode($quinze_trinta_morro) );
    }

}
elseif (isset($_POST['cassicomercio']))
{
    $previsao_two = $pdo->prepare('SELECT * FROM `ct_servico` where fullname like :servico or fullname like :servico2');
    $previsao_two->execute(array(":servico" => 'terminal%', ":servico2" => "HOTEL%"));
    $data_previsao_two = $previsao_two->fetchAll(PDO::FETCH_CLASS);

    $sete_trinta_cassi   = array();
    $dez_trinta_cassi    = array();
    $doze_trinta_cassi   = array();
    $treze_cassi         = array();
    $quinze_cassi        = array();
    $dezessete_cassi     = array();
    foreach ($data_previsao_two as $item)
    {
        if( isset($_POST['setetrintacassi']) )
        {
            $previsao_terminal_others = $pdo->prepare('SELECT sum(r.qtdpax + r.qtdchild + r.qtdfree ) as total FROM `ct_reserva` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservico = :servico and r.idhorario = :horario and confirmacao = :previsto and r.idstatus <> 2');
            $previsao_terminal_others->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 3 , ":previsto" => 0));
            $data_previsao_terminal_others = $previsao_terminal_others->fetch(PDO::FETCH_ASSOC);
            $previsao_terminal_others1 = $pdo->prepare('SELECT sum(r.qpax + r.qchild + r.qfree ) as total FROM `ct_recentlyadd` r left join `ct_reserva` re on re.id = r.idrecently where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservice = :servico and r.idschedule = :horario and confirmacao2 = :previsto and re.idstatus <> 2');
            $previsao_terminal_others1->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 3 , ":previsto" => 0));
            $data_previsao_terminal_others1 = $previsao_terminal_others1->fetch(PDO::FETCH_ASSOC);

            $previsao_terminal_others_confirmacao = $pdo->prepare('SELECT sum(r.qtdpax + r.qtdchild + r.qtdfree ) as total FROM `ct_reserva` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservico = :servico and r.idhorario = :horario and confirmacao = :previsto and r.idstatus <> 2');
            $previsao_terminal_others_confirmacao->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 3 , ":previsto" => 1));
            $data_previsao_terminal_others_confirmacao = $previsao_terminal_others_confirmacao->fetch(PDO::FETCH_ASSOC);
            $previsao_terminal_others1_confirmacao = $pdo->prepare('SELECT sum(r.qpax + r.qchild + r.qfree ) as total FROM `ct_recentlyadd` r left join `ct_reserva` re on re.id = r.idrecently where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservice = :servico and r.idschedule = :horario and confirmacao2 = :previsto and re.idstatus <> 2');
            $previsao_terminal_others1_confirmacao->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 3 , ":previsto" => 1));
            $data_previsao_terminal_others1_confirmacao = $previsao_terminal_others1_confirmacao->fetch(PDO::FETCH_ASSOC);

            if ($data_previsao_terminal_others['total'] <> null or $data_previsao_terminal_others1['total'] <> null or $data_previsao_terminal_others_confirmacao['total'] <> null or $data_previsao_terminal_others1_confirmacao['total'] <> null)
            {
                array_push($sete_trinta_cassi, array("servico" => strtoupper($item->fullname)));
                array_push($sete_trinta_cassi, array("previsto" => $data_previsao_terminal_others['total']+$data_previsao_terminal_others1['total']));
                array_push($sete_trinta_cassi, array("confirmado" => $data_previsao_terminal_others_confirmacao['total']+$data_previsao_terminal_others1_confirmacao['total']));
            }
            foreach ($listaUsusarios as $item2)
            {
                $buscar_total_operador_confirmado = $pdo->prepare('select u.firstname, u.lastname,sum(c.totalpax) as total from ct_confirmacao c left join ct_usuario u on c.idoperador = u.id where c.`data` >= :inicio and c.`data` <= :fim and c.idhorario = :horario and c.idoperador = :operador and c.idservico = :servico');
                $buscar_total_operador_confirmado->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput,":horario" => 3, ":operador" => $item2->id, ":servico" => $item->id));
                $dados_operador = $buscar_total_operador_confirmado->fetch(PDO::FETCH_ASSOC);
                if( $dados_operador['firstname'] <> null )
                {
                    array_push($sete_trinta_cassi, array("nomeoperador" => $dados_operador['firstname']." ".$dados_operador['lastname']));
                    array_push($sete_trinta_cassi, array("totaloperador" => $dados_operador['total']));
                }
            }
        }
        elseif ( isset($_POST['deztrintacassi']) )
        {
            $previsao_terminal_others10 = $pdo->prepare('SELECT sum(r.qtdpax + r.qtdchild + r.qtdfree ) as total FROM `ct_reserva` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservico = :servico and r.idhorario = :horario and confirmacao = :previsto and r.idstatus <> 2');
            $previsao_terminal_others10->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 15, ":previsto" => 0));
            $data_previsao_terminal_others10 = $previsao_terminal_others10->fetch(PDO::FETCH_ASSOC);
            $previsao_terminal_others101 = $pdo->prepare('SELECT sum(r.qpax + r.qchild + r.qfree ) as total FROM `ct_recentlyadd` r left join `ct_reserva` re on re.id = r.idrecently where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservice = :servico and r.idschedule = :horario and confirmacao2 = :previsto and re.idstatus <> 2');
            $previsao_terminal_others101->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 15, ":previsto" => 0));
            $data_previsao_terminal_others101 = $previsao_terminal_others101->fetch(PDO::FETCH_ASSOC);

            $previsao_terminal_others10_confirmacao = $pdo->prepare('SELECT sum(r.qtdpax + r.qtdchild + r.qtdfree ) as total FROM `ct_reserva` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservico = :servico and r.idhorario = :horario and confirmacao = :previsto and r.idstatus <> 2');
            $previsao_terminal_others10_confirmacao->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 15, ":previsto" => 1));
            $data_previsao_terminal_others10_confirmacao = $previsao_terminal_others10_confirmacao->fetch(PDO::FETCH_ASSOC);
            $previsao_terminal_others101_confirmacao = $pdo->prepare('SELECT sum(r.qpax + r.qchild + r.qfree ) as total FROM `ct_recentlyadd` r left join `ct_reserva` re on re.id = r.idrecently where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservice = :servico and r.idschedule = :horario and confirmacao2 = :previsto and re.idstatus <> 2');
            $previsao_terminal_others101_confirmacao->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 15, ":previsto" => 1));
            $data_previsao_terminal_others101_confirmacao = $previsao_terminal_others101_confirmacao->fetch(PDO::FETCH_ASSOC);

            if ($data_previsao_terminal_others10['total'] <> null or $data_previsao_terminal_others101['total'] <> null or $data_previsao_terminal_others10_confirmacao['total'] <> null or $data_previsao_terminal_others101_confirmacao['total'] <> null)
            {
                array_push($dez_trinta_cassi, array("servico" => strtoupper($item->fullname)));
                array_push($dez_trinta_cassi, array("previsto" => $data_previsao_terminal_others10['total']+$data_previsao_terminal_others101['total']));
                array_push($dez_trinta_cassi, array("confirmado" => $data_previsao_terminal_others10_confirmacao['total']+$data_previsao_terminal_others101_confirmacao['total']));
            }
            foreach ($listaUsusarios as $item2)
            {
                $buscar_total_operador_confirmado = $pdo->prepare('select u.firstname, u.lastname,sum(c.totalpax) as total from ct_confirmacao c left join ct_usuario u on c.idoperador = u.id where c.`data` >= :inicio and c.`data` <= :fim and c.idhorario = :horario and c.idoperador = :operador and c.idservico = :servico');
                $buscar_total_operador_confirmado->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput,":horario" => 15, ":operador" => $item2->id, ":servico" => $item->id));
                $dados_operador = $buscar_total_operador_confirmado->fetch(PDO::FETCH_ASSOC);
                if( $dados_operador['firstname'] <> null )
                {
                    array_push($dez_trinta_cassi, array("nomeoperador" => $dados_operador['firstname']." ".$dados_operador['lastname']));
                    array_push($dez_trinta_cassi, array("totaloperador" => $dados_operador['total']));
                }
            }
        }
        elseif ( isset( $_POST['dozetrintacassi'] ) )
        {
            $previsao_terminal_others12 = $pdo->prepare('SELECT sum(r.qtdpax + r.qtdchild + r.qtdfree ) as total FROM `ct_reserva` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservico = :servico and r.idhorario = :horario and confirmacao = :previsto and r.idstatus <> 2');
            $previsao_terminal_others12->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 21 , ":previsto" => 0));
            $data_previsao_terminal_others12 = $previsao_terminal_others12->fetch(PDO::FETCH_ASSOC);
            $previsao_terminal_others121 = $pdo->prepare('SELECT sum(r.qpax + r.qchild + r.qfree ) as total FROM `ct_recentlyadd` r left join `ct_reserva` re on re.id = r.idrecently where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservice = :servico and r.idschedule = :horario and confirmacao2 = :previsto and re.idstatus <> 2');
            $previsao_terminal_others121->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 21, ":previsto" => 0));
            $data_previsao_terminal_others121 = $previsao_terminal_others121->fetch(PDO::FETCH_ASSOC);

            $previsao_terminal_others12_confirmacao = $pdo->prepare('SELECT sum(r.qtdpax + r.qtdchild + r.qtdfree ) as total FROM `ct_reserva` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservico = :servico and r.idhorario = :horario and confirmacao = :previsto and r.idstatus <> 2');
            $previsao_terminal_others12_confirmacao->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 21 , ":previsto" => 1));
            $data_previsao_terminal_others12_confirmacao = $previsao_terminal_others12_confirmacao->fetch(PDO::FETCH_ASSOC);
            $previsao_terminal_others121_confirmacao = $pdo->prepare('SELECT sum(r.qpax + r.qchild + r.qfree ) as total FROM `ct_recentlyadd` r left join `ct_reserva` re on re.id = r.idrecently where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservice = :servico and r.idschedule = :horario and confirmacao2 = :previsto and re.idstatus <> 2');
            $previsao_terminal_others121_confirmacao->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 21, ":previsto" => 1));
            $data_previsao_terminal_others121_confirmacao = $previsao_terminal_others121_confirmacao->fetch(PDO::FETCH_ASSOC);

            if ($data_previsao_terminal_others12['total'] <> null or $data_previsao_terminal_others121['total'] <> null or $data_previsao_terminal_others12_confirmacao['total'] <> null or $data_previsao_terminal_others121_confirmacao['total'] <> null)
            {
                array_push($doze_trinta_cassi, array("servico" => strtoupper($item->fullname)));
                array_push($doze_trinta_cassi, array("previsto" => $data_previsao_terminal_others12['total']+$data_previsao_terminal_others121['total']));
                array_push($doze_trinta_cassi, array("confirmado" => $data_previsao_terminal_others12_confirmacao['total']+$data_previsao_terminal_others121_confirmacao['total']));
            }
            foreach ($listaUsusarios as $item2)
            {
                $buscar_total_operador_confirmado = $pdo->prepare('select u.firstname, u.lastname,sum(c.totalpax) as total from ct_confirmacao c left join ct_usuario u on c.idoperador = u.id where c.`data` >= :inicio and c.`data` <= :fim and c.idhorario = :horario and c.idoperador = :operador and c.idservico = :servico');
                $buscar_total_operador_confirmado->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput,":horario" => 21, ":operador" => $item2->id, ":servico" => $item->id));
                $dados_operador = $buscar_total_operador_confirmado->fetch(PDO::FETCH_ASSOC);
                if( $dados_operador['firstname'] <> null )
                {
                    array_push($doze_trinta_cassi, array("nomeoperador" => $dados_operador['firstname']." ".$dados_operador['lastname']));
                    array_push($doze_trinta_cassi, array("totaloperador" => $dados_operador['total']));
                }
            }
        }
        elseif ( isset( $_POST['trezecassi'] ) )
        {
            $previsao_terminal_others13 = $pdo->prepare('SELECT sum(r.qtdpax + r.qtdchild + r.qtdfree ) as total FROM `ct_reserva` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservico = :servico and r.idhorario = :horario and confirmacao = :previsto and r.idstatus <> 2');
            $previsao_terminal_others13->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 23, ":previsto" => 0));
            $data_previsao_terminal_others13 = $previsao_terminal_others13->fetch(PDO::FETCH_ASSOC);
            $previsao_terminal_others131 = $pdo->prepare('SELECT sum(r.qpax + r.qchild + r.qfree ) as total FROM `ct_recentlyadd` r left join `ct_reserva` re on re.id = r.idrecently where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservice = :servico and r.idschedule = :horario and confirmacao2 = :previsto and re.idstatus <> 2');
            $previsao_terminal_others131->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 23, ":previsto" => 0));
            $data_previsao_terminal_others131 = $previsao_terminal_others131->fetch(PDO::FETCH_ASSOC);

            $previsao_terminal_others13_confirmacao = $pdo->prepare('SELECT sum(r.qtdpax + r.qtdchild + r.qtdfree ) as total FROM `ct_reserva` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservico = :servico and r.idhorario = :horario and confirmacao = :previsto and r.idstatus <> 2');
            $previsao_terminal_others13_confirmacao->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 23, ":previsto" => 1));
            $data_previsao_terminal_others13_confirmacao = $previsao_terminal_others13_confirmacao->fetch(PDO::FETCH_ASSOC);
            $previsao_terminal_others131_confirmacao = $pdo->prepare('SELECT sum(r.qpax + r.qchild + r.qfree ) as total FROM `ct_recentlyadd` r left join `ct_reserva` re on re.id = r.idrecently where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservice = :servico and r.idschedule = :horario and confirmacao2 = :previsto and re.idstatus <> 2');
            $previsao_terminal_others131_confirmacao->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 23, ":previsto" => 1));
            $data_previsao_terminal_others131_confirmacao = $previsao_terminal_others131_confirmacao->fetch(PDO::FETCH_ASSOC);

            if ($data_previsao_terminal_others13['total'] <> null or $data_previsao_terminal_others131['total'] <> null or $data_previsao_terminal_others13_confirmacao['total'] <> null or $data_previsao_terminal_others131_confirmacao['total'] <> null)
            {
                array_push($treze_cassi, array("servico" => strtoupper($item->fullname)));
                array_push($treze_cassi, array("previsto" => $data_previsao_terminal_others13['total']+$data_previsao_terminal_others131['total']));
                array_push($treze_cassi, array("confirmado" => $data_previsao_terminal_others13_confirmacao['total']+$data_previsao_terminal_others131_confirmacao['total']));
            }
            foreach ($listaUsusarios as $item2)
            {
                $buscar_total_operador_confirmado = $pdo->prepare('select u.firstname, u.lastname,sum(c.totalpax) as total from ct_confirmacao c left join ct_usuario u on c.idoperador = u.id where c.`data` >= :inicio and c.`data` <= :fim and c.idhorario = :horario and c.idoperador = :operador and c.idservico = :servico');
                $buscar_total_operador_confirmado->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput,":horario" => 23, ":operador" => $item2->id, ":servico" => $item->id));
                $dados_operador = $buscar_total_operador_confirmado->fetch(PDO::FETCH_ASSOC);
                if( $dados_operador['firstname'] <> null )
                {
                    array_push($treze_cassi, array("nomeoperador" => $dados_operador['firstname']." ".$dados_operador['lastname']));
                    array_push($treze_cassi, array("totaloperador" => $dados_operador['total']));
                }
            }
        }
        elseif (isset( $_POST['quinzecassi'] ))
        {
            $previsao_terminal_others15 = $pdo->prepare('SELECT sum(r.qtdpax + r.qtdchild + r.qtdfree ) as total FROM `ct_reserva` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservico = :servico and r.idhorario = :horario and confirmacao = :previsto and r.idstatus <> 2');
            $previsao_terminal_others15->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 10, ":previsto" => 0));
            $data_previsao_terminal_others15 = $previsao_terminal_others15->fetch(PDO::FETCH_ASSOC);
            $previsao_terminal_others151 = $pdo->prepare('SELECT sum(r.qpax + r.qchild + r.qfree ) as total FROM `ct_recentlyadd` r left join `ct_reserva` re on re.id = r.idrecently where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservice = :servico and r.idschedule = :horario and confirmacao2 = :previsto and re.idstatus <> 2');
            $previsao_terminal_others151->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 10, ":previsto" => 0));
            $data_previsao_terminal_others151 = $previsao_terminal_others151->fetch(PDO::FETCH_ASSOC);

            $previsao_terminal_others15_confirmacao = $pdo->prepare('SELECT sum(r.qtdpax + r.qtdchild + r.qtdfree ) as total FROM `ct_reserva` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservico = :servico and r.idhorario = :horario and confirmacao = :previsto and r.idstatus <> 2');
            $previsao_terminal_others15_confirmacao->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 10, ":previsto" => 1));
            $data_previsao_terminal_others15_confirmacao = $previsao_terminal_others15_confirmacao->fetch(PDO::FETCH_ASSOC);
            $previsao_terminal_others151_confirmacao = $pdo->prepare('SELECT sum(r.qpax + r.qchild + r.qfree ) as total FROM `ct_recentlyadd` r left join `ct_reserva` re on re.id = r.idrecently where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservice = :servico and r.idschedule = :horario and confirmacao2 = :previsto and re.idstatus <> 2');
            $previsao_terminal_others151_confirmacao->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 10, ":previsto" => 1));
            $data_previsao_terminal_others151_confirmacao = $previsao_terminal_others151_confirmacao->fetch(PDO::FETCH_ASSOC);

            if ($data_previsao_terminal_others15['total'] <> null or $data_previsao_terminal_others151['total'] <> null or $data_previsao_terminal_others15_confirmacao['total'] <> null or $data_previsao_terminal_others151_confirmacao['total'] <> null )
            {
                array_push($quinze_cassi, array("servico" => strtoupper($item->fullname)));
                array_push($quinze_cassi, array("previsto" => $data_previsao_terminal_others15['total']+$data_previsao_terminal_others151['total']));
                array_push($quinze_cassi, array("confirmado" => $data_previsao_terminal_others15_confirmacao['total']+$data_previsao_terminal_others151_confirmacao['total']));
            }
            foreach ($listaUsusarios as $item2)
            {
                $buscar_total_operador_confirmado = $pdo->prepare('select u.firstname, u.lastname,sum(c.totalpax) as total from ct_confirmacao c left join ct_usuario u on c.idoperador = u.id where c.`data` >= :inicio and c.`data` <= :fim and c.idhorario = :horario and c.idoperador = :operador and c.idservico = :servico');
                $buscar_total_operador_confirmado->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput,":horario" => 10, ":operador" => $item2->id, ":servico" => $item->id));
                $dados_operador = $buscar_total_operador_confirmado->fetch(PDO::FETCH_ASSOC);
                if( $dados_operador['firstname'] <> null )
                {
                    array_push($quinze_cassi, array("nomeoperador" => $dados_operador['firstname']." ".$dados_operador['lastname']));
                    array_push($quinze_cassi, array("totaloperador" => $dados_operador['total']));
                }
            }
        }
        elseif ( isset( $_POST['dezessetecassi'] ) )
        {
            $previsao_terminal_others17 = $pdo->prepare('SELECT sum(r.qtdpax + r.qtdchild + r.qtdfree ) as total FROM `ct_reserva` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservico = :servico and r.idhorario = :horario and confirmacao = :previsto and r.idstatus <> 2');
            $previsao_terminal_others17->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 13, ":previsto" => 0));
            $data_previsao_terminal_others17 = $previsao_terminal_others17->fetch(PDO::FETCH_ASSOC);
            $previsao_terminal_others171 = $pdo->prepare('SELECT sum(r.qpax + r.qchild + r.qfree ) as total FROM `ct_recentlyadd` r left join `ct_reserva` re on re.id = r.idrecently where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservice = :servico and r.idschedule = :horario and confirmacao2 = :previsto and re.idstatus <> 2');
            $previsao_terminal_others171->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 13, ":previsto" => 0));
            $data_previsao_terminal_others171 = $previsao_terminal_others171->fetch(PDO::FETCH_ASSOC);

            $previsao_terminal_others17_confirmacao = $pdo->prepare('SELECT sum(r.qtdpax + r.qtdchild + r.qtdfree ) as total FROM `ct_reserva` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservico = :servico and r.idhorario = :horario and confirmacao = :previsto and r.idstatus <> 2');
            $previsao_terminal_others17_confirmacao->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 13, ":previsto" => 1));
            $data_previsao_terminal_others17_confirmacao = $previsao_terminal_others17_confirmacao->fetch(PDO::FETCH_ASSOC);
            $previsao_terminal_others171_confirmacao = $pdo->prepare('SELECT sum(r.qpax + r.qchild + r.qfree ) as total FROM `ct_recentlyadd` r left join `ct_reserva` re on re.id = r.idrecently where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservice = :servico and r.idschedule = :horario and confirmacao2 = :previsto and re.idstatus <> 2');
            $previsao_terminal_others171_confirmacao->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 13, ":previsto" => 1));
            $data_previsao_terminal_others171_confirmacao = $previsao_terminal_others171_confirmacao->fetch(PDO::FETCH_ASSOC);

            if ($data_previsao_terminal_others17['total'] <> null or $data_previsao_terminal_others171['total'] <> null or $data_previsao_terminal_others17_confirmacao['total'] <> null or $data_previsao_terminal_others171_confirmacao['total'] <> null)
            {
                array_push($dezessete_cassi, array("servico" => strtoupper($item->fullname)));
                array_push($dezessete_cassi, array("previsto" => $data_previsao_terminal_others17['total']+$data_previsao_terminal_others171['total']));
                array_push($dezessete_cassi, array("confirmado" => $data_previsao_terminal_others17_confirmacao['total']+$data_previsao_terminal_others171_confirmacao['total']));
            }
            foreach ($listaUsusarios as $item2)
            {
                $buscar_total_operador_confirmado = $pdo->prepare('select u.firstname, u.lastname,sum(c.totalpax) as total from ct_confirmacao c left join ct_usuario u on c.idoperador = u.id where c.`data` >= :inicio and c.`data` <= :fim and c.idhorario = :horario and c.idoperador = :operador and c.idservico = :servico');
                $buscar_total_operador_confirmado->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput,":horario" => 13, ":operador" => $item2->id, ":servico" => $item->id));
                $dados_operador = $buscar_total_operador_confirmado->fetch(PDO::FETCH_ASSOC);
                if( $dados_operador['firstname'] <> null )
                {
                    array_push($dezessete_cassi, array("nomeoperador" => $dados_operador['firstname']." ".$dados_operador['lastname']));
                    array_push($dezessete_cassi, array("totaloperador" => $dados_operador['total']));
                }
            }
        }

    }
    if(isset($_POST['setetrintacassi']))
    {
        echo( json_encode($sete_trinta_cassi) );
    }
    elseif (isset($_POST['deztrintacassi']))
    {
        echo( json_encode($dez_trinta_cassi) );
    }
    elseif (isset($_POST['dozetrintacassi']))
    {
        echo( json_encode($doze_trinta_cassi) );
    }
    elseif (isset($_POST['trezecassi']))
    {
        echo( json_encode($treze_cassi) );
    }
    elseif (isset($_POST['quinzecassi']))
    {
        echo( json_encode($quinze_cassi) );
    }
    elseif (isset($_POST['dezessetecassi']))
    {
        echo( json_encode($dezessete_cassi) );
    }


}
elseif(isset($_POST['aeroporto']) == 1)
{
    $previsao_one = $pdo->prepare('SELECT * FROM `ct_servico` where fullname like :servico');
    $previsao_one->execute(array(":servico" => 'aeroporto%'));
    $data_previsao_one = $previsao_one->fetchAll(PDO::FETCH_CLASS);
    $cinco_trinta_aeroporto   = array();
    $nove_trinta_aeroporto    = array();
    $onze_trinta_aeroporto    = array();
    $treze_trinta_aeroporto   = array();
    $dezesseis_aeroporto      = array();
    foreach ($data_previsao_one as $item)
    {
        if(isset($_POST['aeroportocinco']))
        {
            $previsao_aeroporto = $pdo->prepare('SELECT sum(r.qtdpax + r.qtdchild + r.qtdfree ) as total FROM `ct_reserva` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservico = :servico and r.idhorario = :horario and confirmacao = :previsto and r.idstatus <> 2');
            $previsao_aeroporto->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 18, ":previsto" => 0));
            $data_previsao_aeroporto = $previsao_aeroporto->fetch(PDO::FETCH_ASSOC);
            $previsao_terminal_others1aeroporto = $pdo->prepare('SELECT sum(r.qpax + r.qchild + r.qfree ) as total FROM `ct_recentlyadd` r left join `ct_reserva` re on re.id = r.idrecently where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservice = :servico and r.idschedule = :horario and confirmacao2 = :previsto and re.idstatus <> 2');
            $previsao_terminal_others1aeroporto->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 18, ":previsto" => 0));
            $data_previsao_aeroporto2 = $previsao_terminal_others1aeroporto->fetch(PDO::FETCH_ASSOC);

            $previsao_aeroporto_confirmacao = $pdo->prepare('SELECT sum(r.qtdpax + r.qtdchild + r.qtdfree ) as total FROM `ct_reserva` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservico = :servico and r.idhorario = :horario and confirmacao = :previsto and r.idstatus <> 2');
            $previsao_aeroporto_confirmacao->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 18, ":previsto" => 1));
            $data_previsao_aeroporto_confirmacao = $previsao_aeroporto_confirmacao->fetch(PDO::FETCH_ASSOC);
            $previsao_terminal_others1aeroporto_confirmacao = $pdo->prepare('SELECT sum(r.qpax + r.qchild + r.qfree ) as total FROM `ct_recentlyadd` r left join `ct_reserva` re on re.id = r.idrecently where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservice = :servico and r.idschedule = :horario and confirmacao2 = :previsto and re.idstatus <> 2');
            $previsao_terminal_others1aeroporto_confirmacao->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 18, ":previsto" => 1));
            $data_previsao_aeroporto2_confirmacao = $previsao_terminal_others1aeroporto_confirmacao->fetch(PDO::FETCH_ASSOC);

            if ($data_previsao_aeroporto['total'] <> null or $data_previsao_aeroporto2['total'] <> null or $data_previsao_aeroporto_confirmacao['total'] <> null or $data_previsao_aeroporto2_confirmacao['total'] <> null)
            {
                array_push($cinco_trinta_aeroporto, array("servico" => strtoupper($item->fullname)));
                array_push($cinco_trinta_aeroporto, array("previsto" => $data_previsao_aeroporto['total']+$data_previsao_aeroporto2['total']));
                array_push($cinco_trinta_aeroporto, array("confirmado" => $data_previsao_aeroporto_confirmacao['total']+$data_previsao_aeroporto2_confirmacao['total']));
            }
            foreach ($listaUsusarios as $item2)
            {
                $buscar_total_operador_confirmado = $pdo->prepare('select u.firstname, u.lastname,sum(c.totalpax) as total from ct_confirmacao c left join ct_usuario u on c.idoperador = u.id where c.`data` >= :inicio and c.`data` <= :fim and c.idhorario = :horario and c.idoperador = :operador and c.idservico = :servico');
                $buscar_total_operador_confirmado->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput,":horario" => 1, ":operador" => $item2->id, ":servico" => $item->id));
                $dados_operador = $buscar_total_operador_confirmado->fetch(PDO::FETCH_ASSOC);
                if( $dados_operador['firstname'] <> null )
                {
                    array_push($cinco_trinta_aeroporto, array("nomeoperador" => $dados_operador['firstname']." ".$dados_operador['lastname']));
                    array_push($cinco_trinta_aeroporto, array("totaloperador" => $dados_operador['total']));
                }
            }
        }
        elseif (isset($_POST['aeroportonove']))
        {
            $previsao_aeroporto9= $pdo->prepare('SELECT sum(r.qtdpax + r.qtdchild + r.qtdfree ) as total FROM `ct_reserva` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservico = :servico and r.idhorario = :horario  and confirmacao = :previsto and r.idstatus <> 2');
            $previsao_aeroporto9->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 5, ":previsto" => 0));
            $data_previsao_aeroporto9 = $previsao_aeroporto9->fetch(PDO::FETCH_ASSOC);
            $previsao_terminal_others101aeroporto = $pdo->prepare('SELECT sum(r.qpax + r.qchild + r.qfree ) as total FROM `ct_recentlyadd` r left join `ct_reserva` re on re.id = r.idrecently where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservice = :servico and r.idschedule = :horario and confirmacao2 = :previsto and re.idstatus <> 2');
            $previsao_terminal_others101aeroporto->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 5, ":previsto" => 0));
            $data_previsao_aeroporto92 = $previsao_terminal_others101aeroporto->fetch(PDO::FETCH_ASSOC);

            $previsao_aeroporto9_confirmacao= $pdo->prepare('SELECT sum(r.qtdpax + r.qtdchild + r.qtdfree ) as total FROM `ct_reserva` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservico = :servico and r.idhorario = :horario  and confirmacao = :previsto and r.idstatus <> 2');
            $previsao_aeroporto9_confirmacao->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 5, ":previsto" => 1));
            $data_previsao_aeroporto9_confirmacao = $previsao_aeroporto9_confirmacao->fetch(PDO::FETCH_ASSOC);
            $previsao_terminal_others101aeroporto_confirmacao = $pdo->prepare('SELECT sum(r.qpax + r.qchild + r.qfree ) as total FROM `ct_recentlyadd` r left join `ct_reserva` re on re.id = r.idrecently where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservice = :servico and r.idschedule = :horario and confirmacao2 = :previsto and re.idstatus <> 2');
            $previsao_terminal_others101aeroporto_confirmacao->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 5, ":previsto" => 1));
            $data_previsao_aeroporto92_confirmacao = $previsao_terminal_others101aeroporto_confirmacao->fetch(PDO::FETCH_ASSOC);

            if ($data_previsao_aeroporto9['total'] <> null or $data_previsao_aeroporto92_confirmacao['total'] <> null or $data_previsao_aeroporto92['total'] <> null or $data_previsao_aeroporto9_confirmacao['total'] <> null)
            {
                array_push($nove_trinta_aeroporto, array("servico" => strtoupper($item->fullname)));
                array_push($nove_trinta_aeroporto, array("previsto" => $data_previsao_aeroporto9['total']+$data_previsao_aeroporto92['total']));
                array_push($nove_trinta_aeroporto, array("confirmado" => $data_previsao_aeroporto9_confirmacao['total']+$data_previsao_aeroporto92_confirmacao['total']));
            }
            foreach ($listaUsusarios as $item2)
            {
                $buscar_total_operador_confirmado = $pdo->prepare('select u.firstname, u.lastname,sum(c.totalpax) as total from ct_confirmacao c left join ct_usuario u on c.idoperador = u.id where c.`data` >= :inicio and c.`data` <= :fim and c.idhorario = :horario and c.idoperador = :operador and c.idservico = :servico');
                $buscar_total_operador_confirmado->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput,":horario" => 5, ":operador" => $item2->id, ":servico" => $item->id));
                $dados_operador = $buscar_total_operador_confirmado->fetch(PDO::FETCH_ASSOC);
                if( $dados_operador['firstname'] <> null )
                {
                    array_push($nove_trinta_aeroporto, array("nomeoperador" => $dados_operador['firstname']." ".$dados_operador['lastname']));
                    array_push($nove_trinta_aeroporto, array("totaloperador" => $dados_operador['total']));
                }
            }
        }
        elseif (isset($_POST['aeroportoonze']))
        {
            $previsao_aeroporto11 = $pdo->prepare('SELECT sum(r.qtdpax + r.qtdchild + r.qtdfree ) as total FROM `ct_reserva` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservico = :servico and r.idhorario = :horario and confirmacao = :previsto and r.idstatus <> 2');
            $previsao_aeroporto11->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 7, ":previsto" => 0));
            $data_previsao_aeroporto11 = $previsao_aeroporto11->fetch(PDO::FETCH_ASSOC);
            $previsao_terminal_others121aeroporto = $pdo->prepare('SELECT sum(r.qpax + r.qchild + r.qfree ) as total FROM `ct_recentlyadd` r left join `ct_reserva` re on re.id = r.idrecently where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservice = :servico and r.idschedule = :horario and confirmacao2 = :previsto and re.idstatus <> 2');
            $previsao_terminal_others121aeroporto->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 7, ":previsto" => 0));
            $data_previsao_aeroporto112 = $previsao_terminal_others121aeroporto->fetch(PDO::FETCH_ASSOC);

            $previsao_aeroporto11_confirmacao = $pdo->prepare('SELECT sum(r.qtdpax + r.qtdchild + r.qtdfree ) as total FROM `ct_reserva` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservico = :servico and r.idhorario = :horario and confirmacao = :previsto and r.idstatus <> 2');
            $previsao_aeroporto11_confirmacao->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 7, ":previsto" => 1));
            $data_previsao_aeroporto11_confirmacao = $previsao_aeroporto11_confirmacao->fetch(PDO::FETCH_ASSOC);
            $previsao_terminal_others121aeroporto_confirmacao = $pdo->prepare('SELECT sum(r.qpax + r.qchild + r.qfree ) as total FROM `ct_recentlyadd` r left join `ct_reserva` re on re.id = r.idrecently where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservice = :servico and r.idschedule = :horario and confirmacao2 = :previsto and re.idstatus <> 2');
            $previsao_terminal_others121aeroporto_confirmacao->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 7, ":previsto" => 1));
            $data_previsao_aeroporto112_confirmacao = $previsao_terminal_others121aeroporto_confirmacao->fetch(PDO::FETCH_ASSOC);

            if ($data_previsao_aeroporto11['total'] <> null or $data_previsao_aeroporto112['total'] <> null or $data_previsao_aeroporto11_confirmacao['total'] <> null or $data_previsao_aeroporto112_confirmacao['total'] <> null)
            {
                array_push($onze_trinta_aeroporto, array("servico" => strtoupper($item->fullname)));
                array_push($onze_trinta_aeroporto, array("previsto" => $data_previsao_aeroporto11['total']+$data_previsao_aeroporto112['total']));
                array_push($onze_trinta_aeroporto, array("confirmado" => $data_previsao_aeroporto11_confirmacao['total']+$data_previsao_aeroporto112_confirmacao['total']));
            }
            foreach ($listaUsusarios as $item2)
            {
                $buscar_total_operador_confirmado = $pdo->prepare('select u.firstname, u.lastname,sum(c.totalpax) as total from ct_confirmacao c left join ct_usuario u on c.idoperador = u.id where c.`data` >= :inicio and c.`data` <= :fim and c.idhorario = :horario and c.idoperador = :operador and c.idservico = :servico');
                $buscar_total_operador_confirmado->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput,":horario" => 7, ":operador" => $item2->id, ":servico" => $item->id));
                $dados_operador = $buscar_total_operador_confirmado->fetch(PDO::FETCH_ASSOC);
                if( $dados_operador['firstname'] <> null )
                {
                    array_push($onze_trinta_aeroporto, array("nomeoperador" => $dados_operador['firstname']." ".$dados_operador['lastname']));
                    array_push($onze_trinta_aeroporto, array("totaloperador" => $dados_operador['total']));
                }
            }
        }
        elseif (isset($_POST['aeroportotreze']))
        {
            $previsao_aeroporto13 = $pdo->prepare('SELECT sum(r.qtdpax + r.qtdchild + r.qtdfree ) as total FROM `ct_reserva` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservico = :servico and r.idhorario = :horario and confirmacao = :previsto and r.idstatus <> 2');
            $previsao_aeroporto13->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 9, ":previsto" => 0));
            $data_previsao_aeroporto13 = $previsao_aeroporto13->fetch(PDO::FETCH_ASSOC);
            $previsao_terminal_others151aeroporto = $pdo->prepare('SELECT sum(r.qpax + r.qchild + r.qfree ) as total FROM `ct_recentlyadd` r left join `ct_reserva` re on re.id = r.idrecently where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservice = :servico and r.idschedule = :horario and confirmacao2 = :previsto and re.idstatus <> 2');
            $previsao_terminal_others151aeroporto->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 9, ":previsto" => 0));
            $data_previsao_aeroporto132 = $previsao_terminal_others151aeroporto->fetch(PDO::FETCH_ASSOC);

            $previsao_aeroporto13_confirmacao = $pdo->prepare('SELECT sum(r.qtdpax + r.qtdchild + r.qtdfree ) as total FROM `ct_reserva` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservico = :servico and r.idhorario = :horario and confirmacao = :previsto and r.idstatus <> 2');
            $previsao_aeroporto13_confirmacao->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 9, ":previsto" => 1));
            $data_previsao_aeroporto13_confirmacao = $previsao_aeroporto13_confirmacao->fetch(PDO::FETCH_ASSOC);
            $previsao_terminal_others151aeroporto_confirmacao = $pdo->prepare('SELECT sum(r.qpax + r.qchild + r.qfree ) as total FROM `ct_recentlyadd` r left join `ct_reserva` re on re.id = r.idrecently where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservice = :servico and r.idschedule = :horario and confirmacao2 = :previsto and re.idstatus <> 2');
            $previsao_terminal_others151aeroporto_confirmacao->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 9, ":previsto" => 1));
            $data_previsao_aeroporto132_confirmacao = $previsao_terminal_others151aeroporto_confirmacao->fetch(PDO::FETCH_ASSOC);

            if ($data_previsao_aeroporto13['total'] <> null or $data_previsao_aeroporto132['total'] <> null or $data_previsao_aeroporto13_confirmacao['total'] <> null or $data_previsao_aeroporto132_confirmacao['total'] <> null)
            {
                array_push($treze_trinta_aeroporto, array("servico" => strtoupper($item->fullname)));
                array_push($treze_trinta_aeroporto, array("previsto" => $data_previsao_aeroporto13['total']+$data_previsao_aeroporto132['total']));
                array_push($treze_trinta_aeroporto, array("confirmado" => $data_previsao_aeroporto13_confirmacao['total']+$data_previsao_aeroporto132_confirmacao['total']));
            }
            foreach ($listaUsusarios as $item2)
            {
                $buscar_total_operador_confirmado = $pdo->prepare('select u.firstname, u.lastname,sum(c.totalpax) as total from ct_confirmacao c left join ct_usuario u on c.idoperador = u.id where c.`data` >= :inicio and c.`data` <= :fim and c.idhorario = :horario and c.idoperador = :operador and c.idservico = :servico');
                $buscar_total_operador_confirmado->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput,":horario" => 9, ":operador" => $item2->id, ":servico" => $item->id));
                $dados_operador = $buscar_total_operador_confirmado->fetch(PDO::FETCH_ASSOC);
                if( $dados_operador['firstname'] <> null )
                {
                    array_push($treze_trinta_aeroporto, array("nomeoperador" => $dados_operador['firstname']." ".$dados_operador['lastname']));
                    array_push($treze_trinta_aeroporto, array("totaloperador" => $dados_operador['total']));
                }
            }
        }
        elseif (isset($_POST['aeroportodezesseis']))
        {
            $previsao_aeroporto16 = $pdo->prepare('SELECT sum(r.qtdpax + r.qtdchild + r.qtdfree ) as total FROM `ct_reserva` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservico = :servico and r.idhorario = :horario and confirmacao = :previsto and r.idstatus <> 2');
            $previsao_aeroporto16->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 12, ":previsto" => 0));
            $data_previsao_aeroporto16 = $previsao_aeroporto16->fetch(PDO::FETCH_ASSOC);
            $previsao_terminal_others171aeroporto = $pdo->prepare('SELECT sum(r.qpax + r.qchild + r.qfree ) as total FROM `ct_recentlyadd` r left join `ct_reserva` re on re.id = r.idrecently where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservice = :servico and r.idschedule = :horario and confirmacao2 = :previsto and re.idstatus <> 2');
            $previsao_terminal_others171aeroporto->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 12, ":previsto" => 0));
            $data_previsao_aeroporto162 = $previsao_terminal_others171aeroporto->fetch(PDO::FETCH_ASSOC);

            $previsao_aeroporto16_confirmacao = $pdo->prepare('SELECT sum(r.qtdpax + r.qtdchild + r.qtdfree ) as total FROM `ct_reserva` r where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservico = :servico and r.idhorario = :horario and confirmacao = :previsto and r.idstatus <> 2');
            $previsao_aeroporto16_confirmacao->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 12, ":previsto" => 1));
            $data_previsao_aeroporto16_confirmacao = $previsao_aeroporto16_confirmacao->fetch(PDO::FETCH_ASSOC);
            $previsao_terminal_others171aeroporto_confirmacao = $pdo->prepare('SELECT sum(r.qpax + r.qchild + r.qfree ) as total FROM `ct_recentlyadd` r left join `ct_reserva` re on re.id = r.idrecently where r.dateinput >= :inicio and r.dateinput <= :fim and r.idservice = :servico and r.idschedule = :horario and confirmacao2 = :previsto and re.idstatus <> 2');
            $previsao_terminal_others171aeroporto_confirmacao->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput, ":servico" => $item->id, ":horario" => 12, ":previsto" => 1));
            $data_previsao_aeroporto162_confirmacao = $previsao_terminal_others171aeroporto_confirmacao->fetch(PDO::FETCH_ASSOC);

            if ($data_previsao_aeroporto16['total'] <> null or $data_previsao_aeroporto162['total'] <> null or $data_previsao_aeroporto16_confirmacao['total'] <> null or $data_previsao_aeroporto162_confirmacao['total'] <> null)
            {
                array_push($dezesseis_aeroporto, array("servico" => strtoupper($item->fullname)));
                array_push($dezesseis_aeroporto, array("previsto" => $data_previsao_aeroporto16['total']+$data_previsao_aeroporto162['total']));
                array_push($dezesseis_aeroporto, array("confirmado" => $data_previsao_aeroporto16_confirmacao['total']+$data_previsao_aeroporto162_confirmacao['total']));
            }
            foreach ($listaUsusarios as $item2)
            {
                $buscar_total_operador_confirmado = $pdo->prepare('select u.firstname, u.lastname,sum(c.totalpax) as total from ct_confirmacao c left join ct_usuario u on c.idoperador = u.id where c.`data` >= :inicio and c.`data` <= :fim and c.idhorario = :horario and c.idoperador = :operador and c.idservico = :servico');
                $buscar_total_operador_confirmado->execute(array(":inicio" => $dateInput, ":fim" => $dateOutput,":horario" => 12, ":operador" => $item2->id, ":servico" => $item->id));
                $dados_operador = $buscar_total_operador_confirmado->fetch(PDO::FETCH_ASSOC);
                if( $dados_operador['firstname'] <> null )
                {
                    array_push($dezesseis_aeroporto, array("nomeoperador" => $dados_operador['firstname']." ".$dados_operador['lastname']));
                    array_push($dezesseis_aeroporto, array("totaloperador" => $dados_operador['total']));
                }
            }
        }
    }
    if(isset($_POST['aeroportocinco']))
    {
        echo( json_encode($cinco_trinta_aeroporto) );
    }
    elseif (isset($_POST['aeroportonove']))
    {
        echo( json_encode($nove_trinta_aeroporto) );
    }
    elseif (isset($_POST['aeroportoonze']))
    {
        echo( json_encode($onze_trinta_aeroporto) );
    }
    elseif (isset($_POST['aeroportotreze']))
    {
        echo( json_encode($treze_trinta_aeroporto) );
    }
    elseif (isset($_POST['aeroportodezesseis']))
    {
        echo( json_encode($dezesseis_aeroporto) );
    }
}

