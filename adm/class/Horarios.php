<?php


class Horarios
{
    private  $id_horario;
    private  $horario;
    private  $servicos_id_servico;

    /**
     * @return mixed
     */
    public function getIdHorario()
    {
        return $this->id_horario;
    }

    /**
     * @param mixed $id_horario
     */
    public function setIdHorario($id_horario)
    {
        $this->id_horario = $id_horario;
    }

    /**
     * @return mixed
     */
    public function getHorario()
    {
        return $this->horario;
    }

    /**
     * @param mixed $horario
     */
    public function setHorario($horario)
    {
        $this->horario = $horario;
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


}