<?php
require_once ("Sql.php");

class ServicosDestinos extends Sql
{
    private $id_servicos_destino;
    private $nome_destino;
    private $id_origem_destino;


    /**
     * @return mixed
     */
    public function getIdServicosDestino()
    {
        return $this->id_servicos_destino;
    }

    /**
     * @param mixed $id_servicos_destino
     */
    public function setIdServicosDestino($id_servicos_destino)
    {
        $this->id_servicos_destino = $id_servicos_destino;
    }

    /**
     * @return mixed
     */
    public function getNomeDestino()
    {
        return $this->nome_destino;
    }

    /**
     * @param mixed $nome_destino
     */
    public function setNomeDestino($nome_destino)
    {
        $this->nome_destino = $nome_destino;
    }

    /**
     * @return mixed
     */
    public function getIdOrigemDestino()
    {
        return $this->id_origem_destino;
    }

    /**
     * @param mixed $id_origem_destino
     */
    public function setIdOrigemDestino($id_origem_destino)
    {
        $this->id_origem_destino = $id_origem_destino;
    }

    public function buscarTodosDestinos()
    {
        $stmt = $this->conn->prepare('SELECT * FROM `cassiturismo_servisosdestino` st left join cassiturismo_servicos s on s.idservico = st.idservicoorigem where s.tipo = :tipo GROUP BY nomedestino');
        $stmt->execute(array(":tipo" => 0));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function buscarTodosTransfer()
    {
        $stmt = $this->conn->prepare('SELECT sd.idservicosdestino, sd.nomedestino, sd.valoradulto, sd.valorcrianca, sd.caminhofoto,s.nomeservicoorigem, h.horario FROM `cassiturismo_servisosdestino` sd left join cassiturismo_servicos s 
on sd.idservicoorigem = s.idservico right join `cassiturismo_horarios` h on h.idservicodestino = sd.idservicosdestino where s.tipo = :tipo group by idservicosdestino order by sd.idservicoorigem,sd.idservicosdestino,h.horario');
        $stmt->execute(array(":tipo" => 0));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function buscarTodosTransferPorHorario()
    {
        $stmt = $this->conn->prepare('SELECT sd.idservicosdestino, sd.nomedestino, sd.valoradulto, sd.valorcrianca, sd.caminhofoto,s.nomeservicoorigem, h.horario, h.idhorario FROM `cassiturismo_servisosdestino` sd left join cassiturismo_servicos s 
on sd.idservicoorigem = s.idservico right join `cassiturismo_horarios` h on h.idservicodestino = sd.idservicosdestino where s.tipo = :tipo order by sd.idservicoorigem,sd.idservicosdestino,h.horario');
        $stmt->execute(array(":tipo" => 0));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function buscarUnicoTransferPorHorario()
    {
        $stmt = $this->conn->prepare('SELECT sd.idservicosdestino, sd.nomedestino, sd.valoradulto, sd.valorcrianca, sd.caminhofoto,s.nomeservicoorigem, h.horario, h.idhorario FROM `cassiturismo_servisosdestino` sd left join cassiturismo_servicos s 
on sd.idservicoorigem = s.idservico 
                                                right join `cassiturismo_horarios` h on h.idservicodestino = sd.idservicosdestino where idservicodestino = :idservicodestino order by sd.idservicoorigem,sd.idservicosdestino,h.horario');
        $stmt->execute(array(":idservicodestino" => $this->getIdServicosDestino()));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function buscarPorOrigemeDestinoTransferPorHorario()
    {
        $stmt = $this->conn->prepare('SELECT sd.idservicosdestino, sd.nomedestino, sd.valoradulto, sd.valorcrianca, sd.caminhofoto,s.nomeservicoorigem, h.horario, h.idhorario FROM `cassiturismo_servisosdestino` sd left join cassiturismo_servicos s 
on sd.idservicoorigem = s.idservico right join `cassiturismo_horarios` h on h.idservicodestino = sd.idservicosdestino where sd.idservicoorigem = :origem and idservicodestino = :idservicodestino order by sd.idservicoorigem,sd.idservicosdestino,h.horario');
        $stmt->execute(array(":idservicodestino" => $this->getIdServicosDestino(), ":origem" => $this->getIdOrigemDestino()));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function destinoPorOrigem()
    {
        $stmt = $this->conn->prepare('SELECT * FROM `cassiturismo_servisosdestino` where idservicoorigem = :origem');
        $stmt->execute(array(":origem" => $this->getIdOrigemDestino()));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


}