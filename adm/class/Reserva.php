<?php

require_once ('Sql.php');
class Reserva extends Sql
{
    private $id_reserva;
    private $cliente_id_cliente;
    private $servicos_id_servico;
    private $horarios_id_horario;
    private $data_embarque;
    private $quantidade_adulto;
    private $quantidade_crianca;
    private $cupom_id;
    private $numero_voucher;

    /**
     * @return mixed
     */
    public function getIdReserva()
    {
        return $this->id_reserva;
    }

    /**
     * @param mixed $id_reserva
     */
    public function setIdReserva($id_reserva)
    {
        $this->id_reserva = $id_reserva;
    }

    /**
     * @return mixed
     */
    public function getClienteIdCliente()
    {
        return $this->cliente_id_cliente;
    }

    /**
     * @param mixed $cliente_id_cliente
     */
    public function setClienteIdCliente($cliente_id_cliente)
    {
        $this->cliente_id_cliente = $cliente_id_cliente;
    }

    /**
     * @return mixed
     */
    public function getServicosIdServico()
    {
        return $this->servicos_id_servico;
    }

    /**
     * @param mixed $servicos_id_servico
     */
    public function setServicosIdServico($servicos_id_servico)
    {
        $this->servicos_id_servico = $servicos_id_servico;
    }

    /**
     * @return mixed
     */
    public function getHorariosIdHorario()
    {
        return $this->horarios_id_horario;
    }

    /**
     * @param mixed $horarios_id_horario
     */
    public function setHorariosIdHorario($horarios_id_horario)
    {
        $this->horarios_id_horario = $horarios_id_horario;
    }

    /**
     * @return mixed
     */
    public function getDataEmbarque()
    {
        return $this->data_embarque;
    }

    /**
     * @param mixed $data_embarque
     */
    public function setDataEmbarque($data_embarque)
    {
        $this->data_embarque = $data_embarque;
    }

    /**
     * @return mixed
     */
    public function getQuantidadeAdulto()
    {
        return $this->quantidade_adulto;
    }

    /**
     * @param mixed $quantidade_adulto
     */
    public function setQuantidadeAdulto($quantidade_adulto)
    {
        $this->quantidade_adulto = $quantidade_adulto;
    }

    /**
     * @return mixed
     */
    public function getQuantidadeCrianca()
    {
        return $this->quantidade_crianca;
    }

    /**
     * @param mixed $quantidade_crianca
     */
    public function setQuantidadeCrianca($quantidade_crianca)
    {
        $this->quantidade_crianca = $quantidade_crianca;
    }

    /**
     * @return mixed
     */
    public function getCupomId()
    {
        return $this->cupom_id;
    }

    /**
     * @param mixed $cupom_id
     */
    public function setCupomId($cupom_id)
    {
        $this->cupom_id = $cupom_id;
    }

    /**
     * @return mixed
     */
    public function getNumeroVoucher()
    {
        return $this->numero_voucher;
    }

    /**
     * @param mixed $numero_voucher
     */
    public function setNumeroVoucher($numero_voucher)
    {
        $this->numero_voucher = $numero_voucher;
    }

    public function novaReserva()
    {
        $stmt = $this->conn->prepare('insert into `cassiturismo_reserva` values (DEFAULT, :idcliente, :idservicodestino, :idhorario, :datadeembarque, :qadulto, :qcrianca, :cupom, :voucher) ');
        $stmt->bindValue(":idcliente",           $this->getClienteIdCliente());
        $stmt->bindValue(":idservicodestino",    $this->getServicosIdServico());
        $stmt->bindValue(":idhorario",           $this->getHorariosIdHorario());
        $stmt->bindValue(":datadeembarque",      $this->getDataEmbarque());
        $stmt->bindValue(":qadulto",             $this->getQuantidadeAdulto());
        $stmt->bindValue(":qcrianca",           0);
        $stmt->bindValue(":cupom",              null);
        $stmt->bindValue(":voucher",            null);
        $stmt->execute();
        return $this->getClienteIdCliente();
    }

    public function reservaDoCliente()
    {
        $stmt = $this->conn->prepare(
            'select c.idcliente,c.email,c.telefone,c.nomecompleto,r.datadeembarque, r.quatidadeadulto, r.numerovoucher, s.nomeservicoorigem, sd.idservicosistema, sd.nomedestino, sd.valoradulto, h.horario, r.idreserva, c.nomecompleto, c.telefone, sd.idservicosistema, h.idsistema
                     from `cassiturismo_reserva` r left join `cassiturismo_servisosdestino` sd on r.idservisodestinoid = sd.idservicosdestino left join `cassiturismo_servicos` s on s.idservico = sd.idservicoorigem left join `cassiturismo_horarios` h
                     on h.idhorario = r.cassiturismo_horarios_idhorario left join `cassiturismo_cliente` c on c.idcliente = r.`cassiturismo_cliente_idcliente` where r.`cassiturismo_cliente_idcliente` = :idcliente');
        $stmt->bindValue(":idcliente",           $this->getClienteIdCliente());
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function removerDoCarrinho()
    {
        $stmt = $this->conn->prepare('delete from `cassiturismo_reserva` where `idreserva` = :id');
        $stmt->bindValue(":id", $this->getIdReserva());
        $stmt->execute();
    }

    public function atualizarNumeroVoucher()
    {
        $stmt = $this->conn->prepare('update `cassiturismo_reserva` set `numerovoucher` = :numero where `cassiturismo_cliente_idcliente` = :idcliente ');
        $stmt->bindValue(":idcliente",           $this->getClienteIdCliente());
        $stmt->bindValue(":numero",              $this->getNumeroVoucher());
        $stmt->execute();
    }

    public function buscarReservaPorVoucher()
    {
        $stmt = $this->conn->prepare('select r.numerovoucher, r.cassiturismo_cliente_idcliente from `cassiturismo_reserva` r  where r.numerovoucher = :numero ');
        $stmt->bindValue(":numero",   $this->getNumeroVoucher());
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


}