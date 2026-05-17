<?php
require_once ('Sql.php');
class Cliente extends Sql
{
    private  $id_cliente;
    private  $nome_completo;
    private  $cpf_passaporte;
    private  $email;
    private  $senha;
    private  $telefone;
    private  $datanascimento;
    private  $paises_id_pais;
    private  $status_id_status;
    private  $id_revendedor;
    private  $comissao;
    private  $comissao_recebida;
    private  $comissao_a_receber;
    private  $data_ultimo_pagamento;

    /**
     * @return mixed
     */
    public function getIdCliente()
    {
        return $this->id_cliente;
    }

    /**
     * @param mixed $id_cliente
     */
    public function setIdCliente($id_cliente)
    {
        $this->id_cliente = $id_cliente;
    }

    /**
     * @return mixed
     */
    public function getNomeCompleto()
    {
        return $this->nome_completo;
    }

    /**
     * @param mixed $nome_completo
     */
    public function setNomeCompleto($nome_completo)
    {
        $this->nome_completo = $nome_completo;
    }

    /**
     * @return mixed
     */
    public function getCpfPassaporte()
    {
        return $this->cpf_passaporte;
    }

    /**
     * @param mixed $cpf_passaporte
     */
    public function setCpfPassaporte($cpf_passaporte)
    {
        $this->cpf_passaporte = $cpf_passaporte;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getSenha()
    {
        return $this->senha;
    }

    /**
     * @param mixed $senha
     */
    public function setSenha($senha)
    {
        $this->senha = $senha;
    }

    /**
     * @return mixed
     */
    public function getTelefone()
    {
        return $this->telefone;
    }

    /**
     * @param mixed $telefone
     */
    public function setTelefone($telefone)
    {
        $this->telefone = $telefone;
    }

    /**
     * @return mixed
     */
    public function getDatanascimento()
    {
        return $this->datanascimento;
    }

    /**
     * @param mixed $datanascimento
     */
    public function setDatanascimento($datanascimento)
    {
        $this->datanascimento = $datanascimento;
    }



    /**
     * @return mixed
     */
    public function getPaisesIdPais()
    {
        return $this->paises_id_pais;
    }

    /**
     * @param mixed $paises_id_pais
     */
    public function setPaisesIdPais($paises_id_pais)
    {
        $this->paises_id_pais = $paises_id_pais;
    }

    /**
     * @return mixed
     */
    public function getStatusIdStatus()
    {
        return $this->status_id_status;
    }

    /**
     * @param mixed $status_id_status
     */
    public function setStatusIdStatus($status_id_status)
    {
        $this->status_id_status = $status_id_status;
    }

    /**
     * @return mixed
     */
    public function getIdRevendedor()
    {
        return $this->id_revendedor;
    }

    /**
     * @param mixed $id_revendedor
     */
    public function setIdRevendedor($id_revendedor)
    {
        $this->id_revendedor = $id_revendedor;
    }

    /**
     * @return mixed
     */
    public function getComissao()
    {
        return $this->comissao;
    }

    /**
     * @param mixed $comissao
     */
    public function setComissao($comissao)
    {
        $this->comissao = $comissao;
    }

    /**
     * @return mixed
     */
    public function getComissaoRecebida()
    {
        return $this->comissao_recebida;
    }

    /**
     * @param mixed $comissao_recebida
     */
    public function setComissaoRecebida($comissao_recebida)
    {
        $this->comissao_recebida = $comissao_recebida;
    }

    /**
     * @return mixed
     */
    public function getComissaoAReceber()
    {
        return $this->comissao_a_receber;
    }

    /**
     * @param mixed $comissao_a_receber
     */
    public function setComissaoAReceber($comissao_a_receber)
    {
        $this->comissao_a_receber = $comissao_a_receber;
    }

    /**
     * @return mixed
     */
    public function getDataUltimoPagamento()
    {
        return $this->data_ultimo_pagamento;
    }

    /**
     * @param mixed $data_ultimo_pagamento
     */
    public function setDataUltimoPagamento($data_ultimo_pagamento)
    {
        $this->data_ultimo_pagamento = $data_ultimo_pagamento;
    }



    public function novoCliente()
    {
        $stmt = $this->conn->prepare('insert into `cassiturismo_cliente` values (DEFAULT, :nomecompleto, :cpf_passaporte, :nascimento ,:email, :senha, :telefone, :paises_idpaises, :statusidstatus)');
        $stmt->bindValue(":nomecompleto",           $this->getNomeCompleto());
        $stmt->bindValue(":cpf_passaporte",         $this->getCpfPassaporte());
        $stmt->bindValue(":nascimento",             $this->getDatanascimento());
        $stmt->bindValue(":email",                  $this->getEmail());
        $stmt->bindValue(":telefone",               $this->getTelefone());
        $stmt->bindValue(":senha",                  $this->getSenha());
        $stmt->bindValue(":paises_idpaises",        $this->getPaisesIdPais());
        $stmt->bindValue(":statusidstatus",              1);
        $stmt->execute();
        if (session_status() == PHP_SESSION_NONE)
        {
            session_start();
        }
        $_SESSION['id']   = $this->conn->lastInsertId();
        $_SESSION['nome'] = $this->getNomeCompleto();
    }
    public function novoClientePorrevendedor()
    {
        $stmt = $this->conn->prepare('insert into `cassiturismo_cliente` values (DEFAULT, :nomecompleto, :cpf_passaporte, :nascimento ,:email, :senha, :telefone, :paises_idpaises, :statusidstatus)');
        $stmt->bindValue(":nomecompleto",           $this->getNomeCompleto());
        $stmt->bindValue(":cpf_passaporte",         $this->getCpfPassaporte());
        $stmt->bindValue(":nascimento",             $this->getDatanascimento());
        $stmt->bindValue(":email",                  $this->getEmail());
        $stmt->bindValue(":telefone",               $this->getTelefone());
        $stmt->bindValue(":senha",                  $this->getSenha());
        $stmt->bindValue(":paises_idpaises",        $this->getPaisesIdPais());
        $stmt->bindValue(":statusidstatus",              1);
        $stmt->execute();
        if (session_status() == PHP_SESSION_NONE)
        {
            session_start();
        }
        $this->setIdCliente($this->conn->lastInsertId());
        $_SESSION['idcliente']   = $this->getIdCliente();
        $_SESSION['nomecliente'] = $this->getNomeCompleto();
        try
        {
            $stmt = $this->conn->prepare('insert into `cassiturismo_revendedor_cliente` values (DEFAULT,:idrevendedor, :idcliente, :comissao, :comissaorecebida, :areceber, :pagamento) ');
            $stmt->bindValue(":idrevendedor", $this->getIdRevendedor());
            $stmt->bindValue(":idcliente", $this->getIdCliente());
            $stmt->bindValue(":comissao", $this->getComissao());
            $stmt->bindValue(":comissaorecebida", $this->getComissaoRecebida());
            $stmt->bindValue(":areceber", $this->getComissaoAReceber());
            $stmt->bindValue(":pagamento", $this->getDataUltimoPagamento());
            $stmt->execute();
        }catch (Exception $e)
        {
            return $e->getMessage();
        }


    }
    public function pesquisarCliente()
    {
        $stmt = $this->conn->prepare('select * from `cassiturismo_cliente` where `idcliente` = :idcliente ');
        $stmt->execute(array(":idcliente" => $this->getIdCliente()));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function removercliente()
    {
        $stmt = $this->conn->prepare('delete from `cassiturismo_cliente` where `idcliente` = :idcliente ');
        $stmt->execute(array(":idcliente" => $this->getIdCliente()));

    }
    public function todosClientes()
    {
        $stmt = $this->conn->prepare('select * from `cassiturismo_cliente` ');
        $stmt->execute(array(":idcliente" => $this->getIdCliente()));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function atualizarDadosCliente()
    {
        try
        {
            if( $this->getSenha() <> null )
            {
                $stmt = $this->conn->prepare(
                    'update `cassiturismo_cliente` set `nomecompleto` = :nomecompleto, `cpf_passaporte` = :cpf_passaporte, `email` = :email, `senha` = :senha, `telefone` = :telefone, `paises_idpaises` = :paises_idpaises
                             where `idcliente` = :idcliente ');
                $stmt->bindValue(":nomecompleto",           $this->getNomeCompleto());
                $stmt->bindValue(":cpf_passaporte",         $this->getCpfPassaporte());
                $stmt->bindValue(":email",                  $this->getEmail());
                $stmt->bindValue(":telefone",               $this->getTelefone());
                $stmt->bindValue(":senha",                  $this->getSenha());
                $stmt->bindValue(":paises_idpaises",        $this->getPaisesIdPais());
                $stmt->bindValue(":idcliente",              $this->getIdCliente());
                $stmt->execute();
            }else
            {
                $stmt = $this->conn->prepare(
                    'update `cassiturismo_cliente` set `nomecompleto` = :nomecompleto, `cpf_passaporte` = :cpf_passaporte, `email` = :email, `telefone` = :telefone, `paises_idpaises` = :paises_idpaises where `idcliente` = :idcliente  ');
                $stmt->bindValue(":nomecompleto",           $this->getNomeCompleto());
                $stmt->bindValue(":cpf_passaporte",         $this->getCpfPassaporte());
                $stmt->bindValue(":email",                  $this->getEmail());
                $stmt->bindValue(":telefone",               $this->getTelefone());
                $stmt->bindValue(":paises_idpaises",        $this->getPaisesIdPais());
                $stmt->bindValue(":idcliente",              $this->getIdCliente());
                $stmt->execute();
            }
        }
        catch (Exception $e)
        {
            return $e->getMessage();
        }

    }
    public function clientesPorRevendedor()
    {
        $stmt = $this->conn->prepare(
            'select * from `cassiturismo_revendedor_cliente` rc left join `cassiturismo_cliente` c on rc.cassiturismo_cliente_idcliente = c.idcliente left join `cassiturismo_reserva` r on r.cassiturismo_cliente_idcliente =  c.idcliente where rc.cassiturismo_revendedor_idcassiturismo_revendedor = :revendedor and voucher <> 0 group by id');
        $stmt->bindValue(":revendedor", $this->getIdRevendedor());
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function clientesPorRevendedorValorestotais()
    {
        $stmt = $this->conn->prepare(
            'select sum(comissao) as total_a_pagar, sum(comissaorecebida) as total_recebido from `cassiturismo_revendedor_cliente` rc where rc.cassiturismo_revendedor_idcassiturismo_revendedor = :revendedor');
        $stmt->bindValue(":revendedor", $this->getIdRevendedor());
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function login()
    {
        $stmt = $this->conn->prepare('select * from `cassiturismo_cliente` where `email` = :email and `senha` = :senha ');
        $stmt->execute(array(":email" => $this->getEmail(), ":senha" => $this->getSenha()));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function atualizarClientePorRevendedor($data, $forma, $id){
        $getComissaoRecebida = $this->getComissaoRecebida();
        $getComissaoAReceber = $this->getComissaoAReceber();
        $getComissao = $this->getComissao();
        $getIdCliente = $this->getIdCliente();
        $sql ="update `cassiturismo_revendedor_cliente` set `comissaorecebida` = '$getComissaoRecebida', `comissaoareceber` = '$getComissaoAReceber', `comissao` = '$getComissao', `datadoultimopagamento` = '$data', `forma_pagamento` = '$forma' where `id` = '$id'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $sql;
    }

    public function atualizarClientePorRevendedorAtravesDoSistema(){
        $stmt = $this->conn->prepare("update `cassiturismo_revendedor_cliente` set `comissao` = :comissao, `comissaorecebida` = :comissaorecebida, `comissaoareceber` = :comissaoareceber where `cassiturismo_cliente_idcliente` = :id");
        $stmt->bindValue(":comissaorecebida", 0);
        $stmt->bindValue(":comissao", 0);
        $stmt->bindValue(":comissaoareceber", 0);
        $stmt->bindValue(":id",       $this->getIdCliente());
        $stmt->execute();
    }

}