<?php

class Integrar extends Reserva
{
    private $id_cliente;

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



    public function grupoCassi()
    {
        $contador = 0;
        $id_reserva = 0;
        $voucher = 0;
        $pdo = new PDO("mysql:host=cassitur.mysql.dbaas.com.br;dbname=cassitur", "cassitur", "A@nderson30");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        //$this->setClienteIdCliente($this->getIdCliente());
        foreach ($this->reservaDoCliente() as $key => $value)
        {
            if( $contador == 0 )
            {
                $salvarReserva = $pdo->prepare('insert into `ct_reserva` (`id`, `numbervoucher`, `idcliente`, `idresponsavel`, `pax`, `documento`, `idstatus`,
                      `idagente`, `idguia`, `qtdpax`, `qtdchild`, `qtdfree`, `dateinput`, `dateoutput`, `idservico`, `valueservice`,`photoresident`, `idhorario`,
                      `horaap` ,`idpayment`, `idstatusinvoice`, `abertura`, `numberfatura`, `totalservico`, `voo`)
                       values (DEFAULT, :numberv ,:idcli, :idres, :pax, :doc, :idst, :idag, :idgui, :qpax, :qch, :qfree, :din, :dou, :ser, :valor,
                 :photo, :idhor, :horaap ,:idpay, :invoice, :abertura, :fatura, :totalservico, :voo) ');
                $salvarReserva->execute(array(
                    ":numberv" => date('y/m/', strtotime($value['datadeembarque'])) ,
                    ":idcli"  => $this->getIdCliente(),
                    ":idres"  => 1,
                    ":pax"    => $value['nomecompleto'],
                    ":doc"    => "",
                    ":idst"   => 1,
                    ":idag"   => 1,
                    ":idgui"  => 11,
                    ":qpax"   => $value['quatidadeadulto'],
                    ":qch"    => 0,
                    ":qfree"  => 0,
                    ":din"    => $value['datadeembarque'],
                    ":dou"    => $value['datadeembarque'],
                    ":ser"    => $value['idservicosistema'],
                    ":valor"  => $value['valoradulto'],
                    ":photo"  => "",
                    ":idhor"  => $value['idsistema'],
                    ":horaap" => $value['horario'],
                    ":idpay"  => 1,
                    ":invoice"  => 1,
                    ":abertura" => date("Y-m-d"),
                    ":fatura"   => 0,
                    ":totalservico" => $value['valoradulto'] * $value['quatidadeadulto'],
                    ":voo"          => 1
                ) );
                $ultimoId = $pdo->lastInsertId();
                $id_reserva = $ultimoId;
                $gerar_voucher = $pdo->prepare('insert into `ct_voucher` (`voucher`) values (DEFAULT) ');
                $gerar_voucher->execute();

                $buscar_voucher = $pdo->prepare('select * from `ct_voucher` where `voucher` = :voucher ');
                $buscar_voucher->execute(array(":voucher" => $pdo->lastInsertId()));
                $id_voucher = $buscar_voucher->fetch(PDO::FETCH_ASSOC);

                $voucher = date('y/m/', strtotime($value['datadeembarque'])).$id_voucher['voucher'] ;

                $updateNumberVoucher = $pdo->prepare('update `ct_reserva` set `numbervoucher` = :voucher where id = :id ');
                $updateNumberVoucher->execute( array(":voucher" => $voucher, ":id" => $ultimoId ) );


                $nameSearchService = $pdo->prepare("select * from `ct_servico` where id = :id ");
                $nameSearchService->execute( array(":id" => $value['idservicosistema']) );
                $searchData = $nameSearchService->fetch(PDO::FETCH_ASSOC);

                $dadosAuditoria = $pdo->prepare(
                    'insert into `ct_audit` (`id`, `idresponsible`, `voucher`, `description`, `date`) values (DEFAULT, :idres, :vou, :descr, :dat) ');
                $dadosAuditoria->execute(array(
                        ":idres" =>  1,
                        ":vou"   => $voucher,
                        ":descr" => "A reserva do ".$value['nomecompleto']." foi realizada com as seguintes informações:
                \n Embarque: ".date('d-m-Y', strtotime($value['datadeembarque']))." Apanha: ".$value['horario']." Adultos: ".$value['quatidadeadulto']." Crianças: 0"." Free: 0 Serviço: ".$searchData['fullname']
                            . " Complemento: ".''." Valor R$ ".$value['valoradulto']." Telefone: ".$value['telefone']." Voo às".'',
                        ":dat"   => date("Y-m-d H:i:s")
                    ));
                $this->setNumeroVoucher(date('y/m/', strtotime($value['datadeembarque'])).$id_voucher['voucher']);
                $this->atualizarNumeroVoucher();

            }else
                {
                    $vincularServicoVoucher = $pdo->prepare(
                        'INSERT INTO `ct_recentlyadd` (`id`, `idrecently`, `idservice`, `documento` ,`valueservice` ,`idschedule`, `horaap`, `dateinput`, `dateoutput`,
                  `qpax`, `qchild`, `qfree`) VALUES   (DEFAULT, :reserva, :service, :docu ,:valor ,:hora, :horaap ,:di, :doo, :qp, :qc, :qf) ');
                    $vincularServicoVoucher->execute(
                        array(
                            ":reserva" => $id_reserva,
                            ":service" => $value['idservicosistema'],
                            ":docu"     => "",
                            ":valor"   => $value['valoradulto'],
                            ":hora"    => addslashes($value['idsistema']),
                            ":horaap"  => addslashes($value['horario']),
                            ":di"      => addslashes($value['datadeembarque']),
                            ":doo"     => addslashes( $value['datadeembarque']),
                            ":qp"      => addslashes( $value['quatidadeadulto']),
                            ":qc"      => 0,
                            ":qf"      => 0
                        )
                    );

                    $nameSearchService = $pdo->prepare("select * from `ct_servico` where id = :id ");
                    $nameSearchService->execute( array(":id" => $value['idservicosistema'] ) );
                    $searchData = $nameSearchService->fetch(PDO::FETCH_ASSOC);

                    $dadosAuditoria = $pdo->prepare(
                        'insert into `ct_audit` (`id`, `idresponsible`, `voucher`, `description`, `date`) values (DEFAULT, :idres, :vou, :descr, :dat) ');
                    $dadosAuditoria->execute(
                        array(
                            ":idres" =>  1,
                            ":vou"   => $voucher,
                            ":descr" => "A reserva do ".$value['nomecompleto']." foi realizada com as seguintes informações:
                \n Embarque: ".date('d-m-Y', strtotime($value['datadeembarque']))." Apanha: ".$value['horario']." Adultos: ".$value['quatidadeadulto']." Crianças: 0"." Free: 0 Serviço: ".$searchData['fullname']
                                . " Complemento: ".''." Valor R$ ".$value['valoradulto']." Telefone: ".$value['telefone']." Voo às".'',
                            ":dat"   => date("Y-m-d H:i:s")
                        )
                    );

                }
            $contador +=1;
        }
    }
}