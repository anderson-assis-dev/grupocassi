<?php


class Cupom
{
    private $id_cupom;
    private $codigo;
    private $inicio;
    private $fim;
    private $valor;

    /**
     * @return mixed
     */
    public function getIdCupom()
    {
        return $this->id_cupom;
    }

    /**
     * @param mixed $id_cupom
     */
    public function setIdCupom($id_cupom)
    {
        $this->id_cupom = $id_cupom;
    }

    /**
     * @return mixed
     */
    public function getCodigo()
    {
        return $this->codigo;
    }

    /**
     * @param mixed $codigo
     */
    public function setCodigo($codigo)
    {
        $this->codigo = $codigo;
    }

    /**
     * @return mixed
     */
    public function getInicio()
    {
        return $this->inicio;
    }

    /**
     * @param mixed $inicio
     */
    public function setInicio($inicio)
    {
        $this->inicio = $inicio;
    }

    /**
     * @return mixed
     */
    public function getFim()
    {
        return $this->fim;
    }

    /**
     * @param mixed $fim
     */
    public function setFim($fim)
    {
        $this->fim = $fim;
    }

    /**
     * @return mixed
     */
    public function getValor()
    {
        return $this->valor;
    }

    /**
     * @param mixed $valor
     */
    public function setValor($valor)
    {
        $this->valor = $valor;
    }


}