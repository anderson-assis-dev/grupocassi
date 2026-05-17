<?php require_once ('header.php'); ?>
<style>
    .col-md-4{
        margin-bottom: 20px;
    }
</style>
<!-- PAGE CONTENT-->
<div class="page-content--bgf7">
    <!-- BREADCRUMB-->
    <section class="au-breadcrumb2">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="au-breadcrumb-content">
                        <div class="au-breadcrumb-left">
                            <span class="au-breadcrumb-span">Você está aqui:</span>
                            <ul class="list-unstyled list-inline au-breadcrumb__list">
                                <li class="list-inline-item active">
                                    <a href="./index">Home</a>
                                </li>
                                <li class="list-inline-item seprate">
                                    <span>/</span>
                                </li>
                                <li class="list-inline-item">Voucher: Impressão</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <div class="">
        <div class="container">
            <div id="status" class="pull-left col-md-12"></div>
            <div class="col-lg-12">

                <h4>Informações sobre o pax: </h4>
                <hr>
                <form action="./relatorio/teste1" target="_blank" method="post">
                    <div class="col-md-4 pull-left">
                        <strong><label for="destino">Destino</label></strong>
                       <select class="form-control" name="destino" id="destino">
                           <optgroup label="Morro de São Paulo">
                               <option value="AERO/MSP">AERO/MSP</option>
                               <option value="HTL/MSP">HTL/MSP</option>
                               <option value="TERM/MSP">TERM/MSP</option>
                               <option value="MORRO/TERM">MORRO/TERM</option>
                               <option value="MORRO/HTL">MORRO/HTL</option>
                               <option value="MORRO/AERO">MORRO/AERO</option>
                           </optgroup>
                           <optgroup label="Ponta do Curral">
                               <option value="AERO/P.CURRAL">AERO/P.CURRAL</option>
                               <option value="HTL/P.CURRAL">HTL/P.CURRAL</option>
                               <option value="TERM/P.CURRAL">TERM/P.CURRAL</option>
                               <option value="P.CURRAL/TERM">P.CURRAL/TERM</option>
                               <option value="P.CURRAL/TERM">P.CURRAL/TERM</option>
                               <option value="P.CURRAL/AERO">P.CURRAL/AERO</option>
                           </optgroup>
                            <optgroup label="Guaibim">
                                <option value="AERO/GAUIBIM">AERO/GAUIBIM</option>
                                <option value="HTL/GUAIBIM">HTL/GUAIBIM</option>
                                <option value="TERM/GUAIBIMO">TERM/GUAIBIMO</option>
                                <option value="GAUIBIM/AERO">GAUIBIM/AERO</option>
                                <option value="GUAIBIM/HTL">GUAIBIM/HTL</option>
                                <option value="GUAIBIMO/TERM">GUAIBIMO/TERM</option>
                            </optgroup>
                           <optgroup label="Boipeba">
                               <option value="AERO/BOIPEBA">AERO/BOIPEBA</option>
                               <option value="HTL/BOIPEBA">HTL/BOIPEBA</option>
                               <option value="TERM/BOIPEBA">TERM/BOIPEBA</option>
                               <option value="BOIPEBA/AERO">BOIPEBA/AERO</option>
                               <option value="BOIPEBA/HTL">BOIPEBA/HTL</option>
                               <option value="BOIPEBA/TERM">BOIPEBA/TERM</option>
                           </optgroup>
                           <optgroup label="Valença">
                               <option value="AERO/VALENÇA">AERO/VALENÇA</option>
                               <option value="HTL/VALENÇA">HTL/VALENÇA</option>
                               <option value="TERM/VALENÇA">TERM/VALENÇA</option>
                               <option value="VALENÇA/AERO">VALENÇA/AERO</option>
                               <option value="VALENÇA/HTL">VALENÇA/HTL</option>
                               <option value="VALENÇA/TERM">VALENÇA/TERM</option>
                           </optgroup>
                           <optgroup label="Camamu">
                               <option value="AERO/CAMAMU">AERO/CAMAMU</option>
                               <option value="HTL/CAMAMU">HTL/CAMAMU</option>
                               <option value="TERM/CAMAMU">TERM/CAMAMU</option>
                               <option value="CAMAMU/AERO">CAMAMU/AERO</option>
                               <option value="CAMAMU/HTL">VALENÇA/HTL</option>
                               <option value="CAMAMU/TERM">CAMAMU/TERM</option>
                           </optgroup>
                           <optgroup label="Barra Grande">
                               <option value="AERO/BARRA G.">AERO/BARRA G.</option>
                               <option value="HTL/BARRA G.">HTL/BARRA G.</option>
                               <option value="TERM/BARRA G.">TERM/BARRA G.</option>
                               <option value="BARRA G./AERO">BARRA G./AERO</option>
                               <option value="BARRA G./HTL">BARRA G./HTL</option>
                               <option value="BARRA G./TERM">BARRA G./TERM</option>
                           </optgroup>
                           <optgroup label="Itacaré">
                               <option value="AERO/ITACARÉ">AERO/ITACARÉ</option>
                               <option value="HTL/ITACARÉ">HTL/ITACARÉ</option>
                               <option value="TERM/ITACARÉ">TERM/ITACARÉ</option>
                               <option value="ITACARÉ/AERO">ITACARÉ/AERO</option>
                               <option value="ITACARÉ/HTL">ITACARÉ/HTL</option>
                               <option value="ITACARÉ/TERM">ITACARÉ/TERM</option>
                           </optgroup>
                           <optgroup label="Pratigi">
                               <option value="AERO/PRATIGI">AERO/PRATIGI</option>
                               <option value="HTL/PRATIGI">HTL/PRATIGI</option>
                               <option value="TERM/PRATIGI">TERM/PRATIGI</option>
                               <option value="PRATIGI/AERO">PRATIGI/AERO</option>
                               <option value="PRATIGI/HTL">PRATIGI/HTL</option>
                               <option value="PRATIGI/TERM">PRATIGI/TERM</option>
                           </optgroup>
                           <optgroup label="Day Use">
                               <option value="DAY USE/MORRO S.P">DAY USE/MORRO S.P</option>
                               <option value="DAY USE/PRAIA DO FORTE">DAY USE/PRAIA DO FORTE</option>
                               <option value="DAY USE/ILHAS">DAY USE/ILHAS</option>
                               <option value="DAY USE/BOIPEBA">DAY USE/BOIPEBA</option>
                               <option value="DAY USE/MANGUE SECO">DAY USE/MANGUE SECO</option>
                               <option value="DAY USE/CACHOEIRA">DAY USE/CACHOEIRA</option>
                               <option value="DAY USE/ITACARÉ">DAY USE/ITACARÉ</option>
                               <option value="DAY USE/C.HISTÓRICO">DAY USE/C.HISTÓRICO</option>
                               <option value="DAY USE/PANORÂMICO">DAY USE/PANORÂMICO</option>
                               <option value="DAY USE/BAHIA NOITE">DAY USE/BAHIA NOITE</option>
                               <option value="DAY USE/C. HISTÓRICO E PANORÂMICO">DAY USE/C. HISTÓRICO E PANORÂMICO</option>
                           </optgroup>
                       </select>
                    </div>
                    <div class="col-md-4 pull-left">
                        <strong><label for="datainicio">Data de Embarque</label></strong>
                        <input required type="date" name="datainicio" id="datainicio" class="form-control">
                    </div>
                    <div class="col-md-4 pull-right">
                        <strong><label for="pax">PAX</label></strong>
                        <input required type="text" name="pax" id="pax" class="form-control" maxlength="13">
                    </div>

                    <button type="submit" class="btn btn-success btn-lg" name="imprimir" id="imprimir">Imprimir</button>
                </form>
            </div>
        </div>
    </div>
    <?php require_once ('footer.php'); ?>
