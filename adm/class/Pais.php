<?php
require_once ('Sql.php');

class Pais extends Sql
{
    private  $id_pais;
    private  $nome_pais;

    /**
     * @return mixed
     */
    public function getIdPais()
    {
        return $this->id_pais;
    }

    /**
     * @param mixed $id_pais
     */
    public function setIdPais($id_pais)
    {
        $this->id_pais = $id_pais;
    }

    /**
     * @return mixed
     */
    public function getNomePais()
    {
        return $this->nome_pais;
    }

    /**
     * @param mixed $nome_pais
     */
    public function setNomePais($nome_pais)
    {
        $this->nome_pais = $nome_pais;
    }

    public function todosPaises()
    {
        $stmt = $this->conn->prepare('SELECT * FROM `cassiturismo_paises` order by nome_pt, sigla');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


}