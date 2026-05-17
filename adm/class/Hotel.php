<?php

require_once('Sql.php');
class Hotel extends Sql
{
    private $idhotel;
    private $nomehotel;
    private $id_bairro;

    /**
     * @return mixed
     */
    public function getIdhotel()
    {
        return $this->idhotel;
    }

    /**
     * @param mixed $idhotel
     */
    public function setIdhotel($idhotel)
    {
        $this->idhotel = $idhotel;
    }

    /**
     * @return mixed
     */
    public function getNomehotel()
    {
        return $this->nomehotel;
    }

    /**
     * @param mixed $nomehotel
     */
    public function setNomehotel($nomehotel)
    {
        $this->nomehotel = $nomehotel;
    }

    /**
     * @return mixed
     */
    public function getIdBairro()
    {
        return $this->id_bairro;
    }

    /**
     * @param mixed $id_bairro
     */
    public function setIdBairro($id_bairro)
    {
        $this->id_bairro = $id_bairro;
    }


    public function todosHoteis()
    {
        $stmt = $this->conn->prepare('select * from `cassiturismo_bairro` b left join `cassiturismo_hotel` h on h.bairro = b.id where b.valorhotel = 100 order by h.nomehotel');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function unicoHotelPorNome()
    {
        $stmt = $this->conn->prepare('select * from `cassiturismo_hotel` h left join cassiturismo_bairro b on b.id = h.bairro where nomehotel = :nomehotel ');
        $stmt->bindValue(":nomehotel", $this->getNomehotel());
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}