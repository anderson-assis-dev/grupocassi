<?php

require_once('Sql.php');
class Revendedor extends Sql
{
    private $id_revendedor;
    private $nome_completo;
    private $cpf_cnpj;
    private $cnpj;
    private $telefone;
    private $email;
    private $data_nascimento;
    private $status;
    private $cep;
    private $numero;
    private $complemento;
    private $endereco;
    private $rosto;
    private $nome_logo;
    private $nome_fantasia;
    private $nome_banco;
    private $agencia;
    private $conta;
    private $tipo_conta;
    private $numero_conta;
    private $senha_acesso;
    private $id_sistema;

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
    public function getCpfCnpj()
    {
        return $this->cpf_cnpj;
    }

    /**
     * @param mixed $cpf_cnpj
     */
    public function setCpfCnpj($cpf_cnpj)
    {
        $this->cpf_cnpj = $cpf_cnpj;
    }

    /**
     * @return mixed
     */
    public function getCnpj()
    {
        return $this->cnpj;
    }

    /**
     * @param mixed $cnpj
     */
    public function setCnpj($cnpj)
    {
        $this->cnpj = $cnpj;
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
    public function getDataNascimento()
    {
        return $this->data_nascimento;
    }

    /**
     * @param mixed $data_nascimento
     */
    public function setDataNascimento($data_nascimento)
    {
        $this->data_nascimento = $data_nascimento;
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
    public function getCep()
    {
        return $this->cep;
    }

    /**
     * @param mixed $cep
     */
    public function setCep($cep)
    {
        $this->cep = $cep;
    }

    /**
     * @return mixed
     */
    public function getNumero()
    {
        return $this->numero;
    }

    /**
     * @param mixed $numero
     */
    public function setNumero($numero)
    {
        $this->numero = $numero;
    }

    /**
     * @return mixed
     */
    public function getComplemento()
    {
        return $this->complemento;
    }

    /**
     * @param mixed $complemento
     */
    public function setComplemento($complemento)
    {
        $this->complemento = $complemento;
    }

    /**
     * @return mixed
     */
    public function getEndereco()
    {
        return $this->endereco;
    }

    /**
     * @param mixed $endereco
     */
    public function setEndereco($endereco)
    {
        $this->endereco = $endereco;
    }

    /**
     * @return mixed
     */
    public function getRosto()
    {
        return $this->rosto;
    }

    /**
     * @param mixed $rosto
     */
    public function setRosto($rosto)
    {
        $this->rosto = $rosto;
    }

    /**
     * @return mixed
     */



    public function getNomeLogo()
    {
        return $this->nome_logo;
    }

    /**
     * @param mixed $nome_logo
     */
    public function setNomeLogo($nome_logo)
    {
        $this->nome_logo = $nome_logo;
    }

    /**
     * @return mixed
     */
    public function getNomeFantasia()
    {
        return $this->nome_fantasia;
    }

    /**
     * @param mixed $nome_fantasia
     */
    public function setNomeFantasia($nome_fantasia)
    {
        $this->nome_fantasia = $nome_fantasia;
    }

    /**
     * @return mixed
     */
    public function getNomeBanco()
    {
        return $this->nome_banco;
    }

    /**
     * @param mixed $nome_banco
     */
    public function setNomeBanco($nome_banco)
    {
        $this->nome_banco = $nome_banco;
    }

    /**
     * @return mixed
     */
    public function getAgencia()
    {
        return $this->agencia;
    }

    /**
     * @param mixed $agencia
     */
    public function setAgencia($agencia)
    {
        $this->agencia = $agencia;
    }

    /**
     * @return mixed
     */
    public function getConta()
    {
        return $this->conta;
    }

    /**
     * @param mixed $conta
     */
    public function setConta($conta)
    {
        $this->conta = $conta;
    }

    /**
     * @return mixed
     */
    public function getTipoConta()
    {
        return $this->tipo_conta;
    }

    /**
     * @param mixed $tipo_conta
     */
    public function setTipoConta($tipo_conta)
    {
        $this->tipo_conta = $tipo_conta;
    }

    /**
     * @return mixed
     */
    public function getNumeroConta()
    {
        return $this->numero_conta;
    }

    /**
     * @param mixed $numero_conta
     */
    public function setNumeroConta($numero_conta)
    {
        $this->numero_conta = $numero_conta;
    }

    /**
     * @return mixed
     */
    public function getSenhaAcesso()
    {
        return $this->senha_acesso;
    }

    /**
     * @param mixed $senha_acesso
     */
    public function setSenhaAcesso($senha_acesso)
    {
        $this->senha_acesso = $senha_acesso;
    }

    /**
     * @return mixed
     */
    public function getIdSistema()
    {
        return $this->id_sistema;
    }

    /**
     * @param mixed $id_sistema
     */
    public function setIdSistema($id_sistema)
    {
        $this->id_sistema = $id_sistema;
    }



    public function novoRevendedor()
    {
        $stmt = $this->conn->prepare(
            'insert into `cassiturismo_revendedor` values (DEFAULT, :nome, :cpf, :cnpj ,:telefone, :email, :datanascimento, :status, :cep, :numero, :complemento, :endereco, :rosto ,:logo, :nomefantasia, :nomebanco, :agencia, :tipoconta, :numeroconta, :senha, :idsistema) ');
        $stmt->bindValue(":nome", $this->getNomeCompleto());
        $stmt->bindValue(":cpf", $this->getCpfCnpj());
        $stmt->bindValue(":cnpj", $this->getCnpj());
        $stmt->bindValue(":telefone", $this->getTelefone());
        $stmt->bindValue(":email", $this->getEmail());
        $stmt->bindValue(":datanascimento", $this->getDataNascimento());
        $stmt->bindValue(":status", $this->getStatus());
        $stmt->bindValue(":cep", $this->getCep());
        $stmt->bindValue(":numero", $this->getNumero());
        $stmt->bindValue(":complemento", $this->getComplemento());
        $stmt->bindValue(":endereco", $this->getEndereco());
        $stmt->bindValue(":rosto", $this->getRosto());
        $stmt->bindValue(":logo", $this->getNomeLogo());
        $stmt->bindValue(":nomefantasia", $this->getNomeFantasia());
        $stmt->bindValue(":nomebanco", $this->getNomeBanco());
        $stmt->bindValue(":agencia", $this->getAgencia());
        $stmt->bindValue(":tipoconta", $this->getTipoConta());
        $stmt->bindValue(":numeroconta", $this->getNumeroConta());
        $stmt->bindValue(":senha", $this->getSenhaAcesso());
        $stmt->bindValue(":idsistema", 0);
        $stmt->execute();
        //return $this->conn->lastInsertId();

    }
    public function cadastarDadosNoSistema()
    {
        $pdo = new PDO("mysql:host=grupocassi.vpshost3690.mysql.dbaas.com.br;dbname=grupocassi", "grupocassi", "A@nderson10");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $novoCliente = $pdo->prepare(
            'insert into `ct_cliente` (`id`, `fullname`, `cnpj`, `namefantazia`, `corporatename`, `type`, `address`, `datefundation`, `idcountry`, `idstate`, `idcity`,`cep`, `tel01`, `tel02`, `phone`, `email`, `municipalregistration`,
                    `stateenrollment`, `register`, `observacao`, `periodoinicial`, `periodofinal`, `limite`)
                         values (DEFAULT, :fullname, :cnpj, :fantazia, :razao, :tipo, :ende, :dfundation, :country, :estado, :cidade, :cep, :tel1, :tel2, :phone, :email, :rmun, :restado, :embratur, :obs, :inicio, :fim, :limite)');
        $novoCliente->execute( array(
                ":fullname"   => $this->getNomeCompleto(),
                ":cnpj"       => $this->getCpfCnpj(),
                ":fantazia"   => $this->getNomeCompleto(),
                ":razao"      => $this->getNomeCompleto(),
                ":tipo"       => 1,
                ":ende"       => $this->getEndereco(),
                ":dfundation" => $this->getDataNascimento(),
                ":country"    => 1,
                ":estado"     => 1,
                ":cidade"     => 1,
                ":cep"        => $this->getCep(),
                ":tel1"       => $this->getTelefone(),
                ":tel2"       => $this->getTelefone(),
                ":phone"      => $this->getTelefone(),
                ":email"      => $this->getEmail(),
                ":rmun"       => 1,
                ":restado"    => 1,
                ":embratur"   => 1,
                ":obs"        => $this->getNomeLogo(),
                ":inicio"     => '2020-01-31',
                ":fim"        => '2070-01-31',
                ":limite"     => 0
            )
        );
        $this->setIdSistema($pdo->lastInsertId());
        $this->atualizarIdSistema();

    }
    public function atualizarMeusDados()
    {
        if( $this->getSenhaAcesso() <> null and $this->getNomeLogo() <> null )
        {
            $stmt = $this->conn->prepare(
                'update `cassiturismo_revendedor` set `nomecompleto` = :nome, `cpfcnpj` = :cpf, `cnpj` = :cnpj ,`telefone` = :telefone, `email` = :email, `datanascimento` = :datanascimento, `status` = :status, `cep` = :cep, `numero` = :numero, 
                         `complemento` = :complemento, `endereco` = :endereco, `nomelogo` = :logo, `nomefantasia` = :nomefantasia, `nomebanco` = :nomebanco, `agencia` = :agencia, `tipoconta` = :tipoconta, `numeroconta` = :numeroconta,
                          `senhaacesso` = :senha where idcassiturismo_revendedor = :id ');
            $stmt->bindValue(":nome", $this->getNomeCompleto());
            $stmt->bindValue(":cpf", $this->getCpfCnpj());
            $stmt->bindValue(":cnpj", $this->getCnpj());
            $stmt->bindValue(":telefone", $this->getTelefone());
            $stmt->bindValue(":email", $this->getEmail());
            $stmt->bindValue(":datanascimento", $this->getDataNascimento());
            $stmt->bindValue(":status", $this->getStatus());
            $stmt->bindValue(":cep", $this->getCep());
            $stmt->bindValue(":numero", $this->getNumero());
            $stmt->bindValue(":complemento", $this->getComplemento());
            $stmt->bindValue(":endereco", $this->getEndereco());
            $stmt->bindValue(":logo", $this->getNomeLogo());
            $stmt->bindValue(":nomefantasia", $this->getNomeFantasia());
            $stmt->bindValue(":nomebanco", $this->getNomeBanco());
            $stmt->bindValue(":agencia", $this->getAgencia());
            $stmt->bindValue(":tipoconta", $this->getTipoConta());
            $stmt->bindValue(":numeroconta", $this->getNumeroConta());
            $stmt->bindValue(":senha", $this->getSenhaAcesso());
            $stmt->bindValue(":id", $this->getIdRevendedor());
            $stmt->execute();
        }
        elseif( $this->getSenhaAcesso() == null and $this->getNomeLogo() == null  )
            {
                $stmt = $this->conn->prepare(
                    'update `cassiturismo_revendedor` set `nomecompleto` = :nome, `cpfcnpj` = :cpf,`cnpj` = :cnpj, `telefone` = :telefone, `email` = :email, `datanascimento` = :datanascimento, `status` = :status, `cep` = :cep, `numero` = :numero, 
                         `complemento` = :complemento, `endereco` = :endereco,  `nomefantasia` = :nomefantasia, `nomebanco` = :nomebanco, `agencia` = :agencia, `tipoconta` = :tipoconta, `numeroconta` = :numeroconta
                        where idcassiturismo_revendedor = :id');
                $stmt->bindValue(":nome", $this->getNomeCompleto());
                $stmt->bindValue(":cpf", $this->getCpfCnpj());
                $stmt->bindValue(":cnpj", $this->getCnpj());
                $stmt->bindValue(":telefone", $this->getTelefone());
                $stmt->bindValue(":email", $this->getEmail());
                $stmt->bindValue(":datanascimento", $this->getDataNascimento());
                $stmt->bindValue(":status", $this->getStatus());
                $stmt->bindValue(":cep", $this->getCep());
                $stmt->bindValue(":numero", $this->getNumero());
                $stmt->bindValue(":complemento", $this->getComplemento());
                $stmt->bindValue(":endereco", $this->getEndereco());
                $stmt->bindValue(":nomefantasia", $this->getNomeFantasia());
                $stmt->bindValue(":nomebanco", $this->getNomeBanco());
                $stmt->bindValue(":agencia", $this->getAgencia());
                $stmt->bindValue(":tipoconta", $this->getTipoConta());
                $stmt->bindValue(":numeroconta", $this->getNumeroConta());
                $stmt->bindValue(":id", $this->getIdRevendedor());
                $stmt->execute();
            }
        elseif ($this->getSenhaAcesso() <> null)
        {
            $stmt = $this->conn->prepare(
                'update `cassiturismo_revendedor` set `nomecompleto` = :nome, `cpfcnpj` = :cpf,`cnpj` = :cnpj, `telefone` = :telefone, `email` = :email, `datanascimento` = :datanascimento, `status` = :status, `cep` = :cep, `numero` = :numero, 
                         `complemento` = :complemento, `endereco` = :endereco, `nomefantasia` = :nomefantasia, `nomebanco` = :nomebanco, `agencia` = :agencia, `tipoconta` = :tipoconta, `numeroconta` = :numeroconta,
                          `senhaacesso` = :senha  where idcassiturismo_revendedor = :id ');
            $stmt->bindValue(":nome", $this->getNomeCompleto());
            $stmt->bindValue(":cpf", $this->getCpfCnpj());
            $stmt->bindValue(":cnpj", $this->getCnpj());
            $stmt->bindValue(":telefone", $this->getTelefone());
            $stmt->bindValue(":email", $this->getEmail());
            $stmt->bindValue(":datanascimento", $this->getDataNascimento());
            $stmt->bindValue(":status", $this->getStatus());
            $stmt->bindValue(":cep", $this->getCep());
            $stmt->bindValue(":numero", $this->getNumero());
            $stmt->bindValue(":complemento", $this->getComplemento());
            $stmt->bindValue(":endereco", $this->getEndereco());
            $stmt->bindValue(":nomefantasia", $this->getNomeFantasia());
            $stmt->bindValue(":nomebanco", $this->getNomeBanco());
            $stmt->bindValue(":agencia", $this->getAgencia());
            $stmt->bindValue(":tipoconta", $this->getTipoConta());
            $stmt->bindValue(":numeroconta", $this->getNumeroConta());
            $stmt->bindValue(":senha", $this->getSenhaAcesso());
            $stmt->bindValue(":id", $this->getIdRevendedor());
            $stmt->execute();
        }
        elseif ($this->getNomeLogo() <> null)
        {
            $stmt = $this->conn->prepare(
                'update `cassiturismo_revendedor` set `nomecompleto` = :nome, `cpfcnpj` = :cpf,`cnpj` = :cnpj, `telefone` = :telefone, `email` = :email, `datanascimento` = :datanascimento, `status` = :status, `cep` = :cep, `numero` = :numero, 
                         `complemento` = :complemento, `endereco` = :endereco,`nomelogo` = :logo, `nomefantasia` = :nomefantasia, `nomebanco` = :nomebanco, `agencia` = :agencia, `tipoconta` = :tipoconta, `numeroconta` = :numeroconta,
                          `senhaacesso` = :senha  where idcassiturismo_revendedor = :id ');
            $stmt->bindValue(":nome", $this->getNomeCompleto());
            $stmt->bindValue(":cpf", $this->getCpfCnpj());
            $stmt->bindValue(":cnpj", $this->getCnpj());
            $stmt->bindValue(":telefone", $this->getTelefone());
            $stmt->bindValue(":email", $this->getEmail());
            $stmt->bindValue(":datanascimento", $this->getDataNascimento());
            $stmt->bindValue(":status", $this->getStatus());
            $stmt->bindValue(":cep", $this->getCep());
            $stmt->bindValue(":numero", $this->getNumero());
            $stmt->bindValue(":complemento", $this->getComplemento());
            $stmt->bindValue(":endereco", $this->getEndereco());
            $stmt->bindValue(":logo", $this->getNomeLogo());
            $stmt->bindValue(":nomefantasia", $this->getNomeFantasia());
            $stmt->bindValue(":nomebanco", $this->getNomeBanco());
            $stmt->bindValue(":agencia", $this->getAgencia());
            $stmt->bindValue(":tipoconta", $this->getTipoConta());
            $stmt->bindValue(":numeroconta", $this->getNumeroConta());
            $stmt->bindValue(":id", $this->getIdRevendedor());
            $stmt->execute();
        }
    }

    public function loginRevendedor()
    {
        $stmt = $this->conn->prepare('select * from `cassiturismo_revendedor` where `email` = :email and `senhaacesso` = :senha ');
        $stmt->execute(array(":email" => $this->getEmail(), ":senha" => $this->getSenhaAcesso()));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function meuDados()
    {
        $stmt = $this->conn->prepare('select * from `cassiturismo_revendedor` where `idcassiturismo_revendedor` = :id');
        $stmt->execute(array(":id" => $this->getIdRevendedor()));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function listarRevendedoresPendenteAprovacao()
    {
        $stmt = $this->conn->prepare('select * from `cassiturismo_revendedor` where idsistema = 0  and status = 1');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function listarRevendedoresGeral($nome="",$data="")
    {
        $sql = 'SELECT cr.*, (SELECT sum(crc.comissao) FROM cassiturismo_revendedor_cliente crc WHERE crc.cassiturismo_revendedor_idcassiturismo_revendedor = cr.idcassiturismo_revendedor 
        GROUP by crc.cassiturismo_revendedor_idcassiturismo_revendedor) total_comissao,(SELECT sum(crc.comissaorecebida) FROM cassiturismo_revendedor_cliente crc WHERE crc.cassiturismo_revendedor_idcassiturismo_revendedor = cr.idcassiturismo_revendedor )
         total_pago FROM cassiturismo_revendedor cr  where 1=1';
        if(!empty($nome))
        {
            $sql .= " and cr.nomecompleto like '%".$nome."%'";
        }
        if(!empty($data))
        {
            $sql .= " and cr.abertura = '".$data."'";
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function listarRevendedoresAprovados($nome = "")
    {
        $sql ='SELECT cr.*, (SELECT sum(crc.comissao) FROM cassiturismo_revendedor_cliente crc WHERE crc.cassiturismo_revendedor_idcassiturismo_revendedor = cr.idcassiturismo_revendedor 
        GROUP by crc.cassiturismo_revendedor_idcassiturismo_revendedor) total_comissao,(SELECT sum(crc.comissaorecebida) FROM cassiturismo_revendedor_cliente crc WHERE crc.cassiturismo_revendedor_idcassiturismo_revendedor = cr.idcassiturismo_revendedor )
         total_pago FROM cassiturismo_revendedor cr where cr.idsistema <> 0 and cr.status = 2';
        if(!empty($nome))
        {
            $sql .= "and cr.nomecompleto like '%".$nome."%'";
        } 
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

    public function aprovarRevendedor()
    {
        $stmt = $this->conn->prepare('update `cassiturismo_revendedor` set `status` = :status where `idcassiturismo_revendedor` = :id');
        $stmt->execute(array(":id" => $this->getIdRevendedor(), ":status" => $this->getStatus()));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function removerRevendedor()
    {
        $stmt = $this->conn->prepare('delete from `cassiturismo_revendedor_cliente` where `cassiturismo_revendedor_idcassiturismo_revendedor` = :id');
        $stmt->execute(array(":id" => $this->getIdRevendedor()));
        $stmt1 = $this->conn->prepare('delete from `cassiturismo_revendedor` where `idcassiturismo_revendedor` = :id');
        $stmt1->execute(array(":id" => $this->getIdRevendedor()));

    }
    public function atualizarIdSistema()
    {
        $stmt = $this->conn->prepare('update `cassiturismo_revendedor` set `idsistema` = :idsistema where `idcassiturismo_revendedor` = :id');
        $stmt->execute(array(":id" => $this->getIdRevendedor(), ":idsistema" => $this->getIdSistema()));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
}