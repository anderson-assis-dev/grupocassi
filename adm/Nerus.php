<?php


class Nerus
{

    private $idLoja;
    private $idPedidoWeb;
    private $dataPedido;
    private $dataExpiracao;
    private $dataEntrega;
    private $observacoesEntrega;
    private $observacoesPedido;
    private $idTransportadora;
    private $idFuncionario;
    private $codigoPedidoWeb;
    private $idCanal;
    private $status;
    private $sku;
    private $quantidade;
    private $precoCusto;
    private $precoUnitario;
    private $grade;
    private $marca;
    private $ncm;
    private $volumes;
    private $pesoLiquido;
    private $pesoBruto;
    private $dimensoes;
    private $tipoAnuncio;
    private $name;
    private $tipoPagamento;
    private $bandeira;
    private $nsu;
    private $autorizacao;
    private $pedidoDividido;
    private $valorDesconto;
    private $valorFrete;
    private $valorRecebimento;
    private $valorTotal;
    private $parcelas;

    /**
     * @return mixed
     */
    public function getIdLoja()
    {
        return $this->idLoja;
    }

    /**
     * @param mixed $idLoja
     */
    public function setIdLoja($idLoja)
    {
        $this->idLoja = $idLoja;
    }

    /**
     * @return mixed
     */
    public function getIdPedidoWeb()
    {
        return $this->idPedidoWeb;
    }

    /**
     * @param mixed $idPedidoWeb
     */
    public function setIdPedidoWeb($idPedidoWeb)
    {
        $this->idPedidoWeb = $idPedidoWeb;
    }

    /**
     * @return mixed
     */
    public function getDataPedido()
    {
        return $this->dataPedido;
    }

    /**
     * @param mixed $dataPedido
     */
    public function setDataPedido($dataPedido)
    {
        $this->dataPedido = $dataPedido;
    }

    /**
     * @return mixed
     */
    public function getDataExpiracao()
    {
        return $this->dataExpiracao;
    }

    /**
     * @param mixed $dataExpiracao
     */
    public function setDataExpiracao($dataExpiracao)
    {
        $this->dataExpiracao = $dataExpiracao;
    }

    /**
     * @return mixed
     */
    public function getDataEntrega()
    {
        return $this->dataEntrega;
    }

    /**
     * @param mixed $dataEntrega
     */
    public function setDataEntrega($dataEntrega)
    {
        $this->dataEntrega = $dataEntrega;
    }

    /**
     * @return mixed
     */
    public function getObservacoesEntrega()
    {
        return $this->observacoesEntrega;
    }

    /**
     * @param mixed $observacoesEntrega
     */
    public function setObservacoesEntrega($observacoesEntrega)
    {
        $this->observacoesEntrega = $observacoesEntrega;
    }

    /**
     * @return mixed
     */
    public function getObservacoesPedido()
    {
        return $this->observacoesPedido;
    }

    /**
     * @param mixed $observacoesPedido
     */
    public function setObservacoesPedido($observacoesPedido)
    {
        $this->observacoesPedido = $observacoesPedido;
    }

    /**
     * @return mixed
     */
    public function getIdTransportadora()
    {
        return $this->idTransportadora;
    }

    /**
     * @param mixed $idTransportadora
     */
    public function setIdTransportadora($idTransportadora)
    {
        $this->idTransportadora = $idTransportadora;
    }

    /**
     * @return mixed
     */
    public function getIdFuncionario()
    {
        return $this->idFuncionario;
    }

    /**
     * @param mixed $idFuncionario
     */
    public function setIdFuncionario($idFuncionario)
    {
        $this->idFuncionario = $idFuncionario;
    }

    /**
     * @return mixed
     */
    public function getCodigoPedidoWeb()
    {
        return $this->codigoPedidoWeb;
    }

    /**
     * @param mixed $codigoPedidoWeb
     */
    public function setCodigoPedidoWeb($codigoPedidoWeb)
    {
        $this->codigoPedidoWeb = $codigoPedidoWeb;
    }

    /**
     * @return mixed
     */
    public function getIdCanal()
    {
        return $this->idCanal;
    }

    /**
     * @param mixed $idCanal
     */
    public function setIdCanal($idCanal)
    {
        $this->idCanal = $idCanal;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getSku()
    {
        return $this->sku;
    }

    /**
     * @param mixed $sku
     */
    public function setSku($sku)
    {
        $this->sku = $sku;
    }

    /**
     * @return mixed
     */
    public function getQuantidade()
    {
        return $this->quantidade;
    }

    /**
     * @param mixed $quantidade
     */
    public function setQuantidade($quantidade)
    {
        $this->quantidade = $quantidade;
    }

    /**
     * @return mixed
     */
    public function getPrecoCusto()
    {
        return $this->precoCusto;
    }

    /**
     * @param mixed $precoCusto
     */
    public function setPrecoCusto($precoCusto)
    {
        $this->precoCusto = $precoCusto;
    }

    /**
     * @return mixed
     */
    public function getPrecoUnitario()
    {
        return $this->precoUnitario;
    }

    /**
     * @param mixed $precoUnitario
     */
    public function setPrecoUnitario($precoUnitario)
    {
        $this->precoUnitario = $precoUnitario;
    }

    /**
     * @return mixed
     */
    public function getGrade()
    {
        return $this->grade;
    }

    /**
     * @param mixed $grade
     */
    public function setGrade($grade)
    {
        $this->grade = $grade;
    }

    /**
     * @return mixed
     */
    public function getMarca()
    {
        return $this->marca;
    }

    /**
     * @param mixed $marca
     */
    public function setMarca($marca)
    {
        $this->marca = $marca;
    }

    /**
     * @return mixed
     */
    public function getNcm()
    {
        return $this->ncm;
    }

    /**
     * @param mixed $ncm
     */
    public function setNcm($ncm)
    {
        $this->ncm = $ncm;
    }

    /**
     * @return mixed
     */
    public function getVolumes()
    {
        return $this->volumes;
    }

    /**
     * @param mixed $volumes
     */
    public function setVolumes($volumes)
    {
        $this->volumes = $volumes;
    }

    /**
     * @return mixed
     */
    public function getPesoLiquido()
    {
        return $this->pesoLiquido;
    }

    /**
     * @param mixed $pesoLiquido
     */
    public function setPesoLiquido($pesoLiquido)
    {
        $this->pesoLiquido = $pesoLiquido;
    }

    /**
     * @return mixed
     */
    public function getPesoBruto()
    {
        return $this->pesoBruto;
    }

    /**
     * @param mixed $pesoBruto
     */
    public function setPesoBruto($pesoBruto)
    {
        $this->pesoBruto = $pesoBruto;
    }

    /**
     * @return mixed
     */
    public function getDimensoes()
    {
        return $this->dimensoes;
    }

    /**
     * @param mixed $dimensoes
     */
    public function setDimensoes($dimensoes)
    {
        $this->dimensoes = $dimensoes;
    }

    /**
     * @return mixed
     */
    public function getTipoAnuncio()
    {
        return $this->tipoAnuncio;
    }

    /**
     * @param mixed $tipoAnuncio
     */
    public function setTipoAnuncio($tipoAnuncio)
    {
        $this->tipoAnuncio = $tipoAnuncio;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getTipoPagamento()
    {
        return $this->tipoPagamento;
    }

    /**
     * @param mixed $tipoPagamento
     */
    public function setTipoPagamento($tipoPagamento)
    {
        $this->tipoPagamento = $tipoPagamento;
    }

    /**
     * @return mixed
     */
    public function getBandeira()
    {
        return $this->bandeira;
    }

    /**
     * @param mixed $bandeira
     */
    public function setBandeira($bandeira)
    {
        $this->bandeira = $bandeira;
    }

    /**
     * @return mixed
     */
    public function getNsu()
    {
        return $this->nsu;
    }

    /**
     * @param mixed $nsu
     */
    public function setNsu($nsu)
    {
        $this->nsu = $nsu;
    }

    /**
     * @return mixed
     */
    public function getAutorizacao()
    {
        return $this->autorizacao;
    }

    /**
     * @param mixed $autorizacao
     */
    public function setAutorizacao($autorizacao)
    {
        $this->autorizacao = $autorizacao;
    }

    /**
     * @return mixed
     */
    public function getPedidoDividido()
    {
        return $this->pedidoDividido;
    }

    /**
     * @param mixed $pedidoDividido
     */
    public function setPedidoDividido($pedidoDividido)
    {
        $this->pedidoDividido = $pedidoDividido;
    }

    /**
     * @return mixed
     */
    public function getValorDesconto()
    {
        return $this->valorDesconto;
    }

    /**
     * @param mixed $valorDesconto
     */
    public function setValorDesconto($valorDesconto)
    {
        $this->valorDesconto = $valorDesconto;
    }

    /**
     * @return mixed
     */
    public function getValorFrete()
    {
        return $this->valorFrete;
    }

    /**
     * @param mixed $valorFrete
     */
    public function setValorFrete($valorFrete)
    {
        $this->valorFrete = $valorFrete;
    }

    /**
     * @return mixed
     */
    public function getValorRecebimento()
    {
        return $this->valorRecebimento;
    }

    /**
     * @param mixed $valorRecebimento
     */
    public function setValorRecebimento($valorRecebimento)
    {
        $this->valorRecebimento = $valorRecebimento;
    }

    /**
     * @return mixed
     */
    public function getValorTotal()
    {
        return $this->valorTotal;
    }

    /**
     * @param mixed $valorTotal
     */
    public function setValorTotal($valorTotal)
    {
        $this->valorTotal = $valorTotal;
    }

    /**
     * @return mixed
     */
    public function getParcelas()
    {
        return $this->parcelas;
    }

    /**
     * @param mixed $parcelas
     */
    public function setParcelas($parcelas)
    {
        $this->parcelas = $parcelas;
    }




}