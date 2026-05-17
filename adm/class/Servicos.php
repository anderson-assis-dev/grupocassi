<?php


class Servicos extends Sql
{
    private  $id_servico;
    private  $nome_servico;
    private  $descricao;
    private  $id_servico_sistema;
    private  $valor_adulto;
    private  $valor_crianca;

    /**
     * @return mixed
     */
    public function getIdServico()
    {
        return $this->id_servico;
    }

    /**
     * @param mixed $id_servico
     */
    public function setIdServico($id_servico)
    {
        $this->id_servico = $id_servico;
    }

    /**
     * @return mixed
     */
    public function getNomeServico()
    {
        return $this->nome_servico;
    }

    /**
     * @param mixed $nome_servico
     */
    public function setNomeServico($nome_servico)
    {
        $this->nome_servico = $nome_servico;
    }

    /**
     * @return mixed
     */
    public function getDescricao()
    {
        return $this->descricao;
    }

    /**
     * @param mixed $descricao
     */
    public function setDescricao($descricao)
    {
        $this->descricao = $descricao;
    }

    /**
     * @return mixed
     */
    public function getIdServicoSistema()
    {
        return $this->id_servico_sistema;
    }

    /**
     * @param mixed $id_servico_sistema
     */
    public function setIdServicoSistema($id_servico_sistema)
    {
        $this->id_servico_sistema = $id_servico_sistema;
    }

    /**
     * @return mixed
     */
    public function getValorAdulto()
    {
        return $this->valor_adulto;
    }

    /**
     * @param mixed $valor_adulto
     */
    public function setValorAdulto($valor_adulto)
    {
        $this->valor_adulto = $valor_adulto;
    }

    /**
     * @return mixed
     */
    public function getValorCrianca()
    {
        return $this->valor_crianca;
    }

    /**
     * @param mixed $valor_crianca
     */
    public function setValorCrianca($valor_crianca)
    {
        $this->valor_crianca = $valor_crianca;
    }

    public function todasOrigems()
    {
        $stmt = $this->conn->prepare('SELECT * FROM `cassiturismo_servicos` where tipo = :tipo');
        $stmt->execute(array(":tipo" => 0));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function todosPasseios()
    {
        $stmt = $this->conn->prepare('SELECT s.idservico, sd.idservicosdestino, sd.nomedestino, sd.valoradulto, sd.valorcrianca, sd.caminhofoto,s.nomeservicoorigem, h.horario, h.idhorario FROM `cassiturismo_servisosdestino` sd left join cassiturismo_servicos s on sd.idservicoorigem = s.idservico 
                                                right join `cassiturismo_horarios` h on h.idservicodestino = sd.idservicosdestino where s.tipo = :tipo order by sd.idservicoorigem,sd.idservicosdestino,h.horario');
        $stmt->execute(array(":tipo" => 1));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}