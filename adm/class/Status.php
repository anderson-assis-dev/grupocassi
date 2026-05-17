<?php


class Status
{
    private $id_status;
    private $nome_status;

    /**
     * @return mixed
     */
    public function getIdStatus()
    {
        return $this->id_status;
    }

    /**
     * @param mixed $id_status
     */
    public function setIdStatus($id_status)
    {
        $this->id_status = $id_status;
    }

    /**
     * @return mixed
     */
    public function getNomeStatus()
    {
        return $this->nome_status;
    }

    /**
     * @param mixed $nome_status
     */
    public function setNomeStatus($nome_status)
    {
        $this->nome_status = $nome_status;
    }


}