<?php require_once ('header.php');


?>
<style>
    .form-group {
        margin: 1rem;
    }
    .col-md-2{
        max-width: 20%;
    }
    .table-bordered td{text-align: center;}
    tr#undefined{display: none;}
    .table td, .table th{ vertical-align: middle !important;}
    #snackbar {
        visibility: hidden;
        min-width: 250px;
        margin-left: -125px;
        background-color: #333;
        color: #fff;
        text-align: center;
        border-radius: 2px;
        padding: 16px;
        position: fixed;
        z-index: 1;
        left: 50%;
        bottom: 30px;
        font-size: 17px;
    }

    #snackbar.show {
        visibility: visible;
        -webkit-animation: fadein 0.5s, fadeout 0.5s 2.5s;
        animation: fadein 0.5s, fadeout 0.5s 2.5s;
    }

    @-webkit-keyframes fadein {
        from {bottom: 0; opacity: 0;}
        to {bottom: 30px; opacity: 1;}
    }

    @keyframes fadein {
        from {bottom: 0; opacity: 0;}
        to {bottom: 30px; opacity: 1;}
    }

    @-webkit-keyframes fadeout {
        from {bottom: 30px; opacity: 1;}
        to {bottom: 0; opacity: 0;}
    }

    @keyframes fadeout {
        from {bottom: 30px; opacity: 1;}
        to {bottom: 0; opacity: 0;}
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
                                    <a href="index">Home</a>
                                </li>
                                <li class="list-inline-item seprate">
                                    <span>/</span>
                                </li>
                                <li class="list-inline-item">Mapa de Serviço: Novo Serviço</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- END BREADCRUMB-->
    <div class="card">
        <div class="card">
            <div class="card-header">
                <div class="card-title">Buscar Previsão</div>
            </div>
            <div class="card-body">
                <div id="snackbar">Carregando informações, Aguarde !</div>
                <form class="" method="post" action="">
                    <div class="col-md-6 pull-left">
                        <label for="datainicio"><strong>Data Inicio:</strong></label>
                        <input required type="date" name="datainicio" id="inicio" class="form-control" value="<?php echo( date("Y-m-d") ); ?>">
                    </div>
                    <div class="col-md-6 pull-right">
                        <label for="datafim"><strong>Data Fim:</strong></label>
                        <input required type="date" name="datafim" id="fim" class="form-control" value="<?php echo( date("Y-m-d") ); ?>">
                    </div>

                </form>
            </div>

        </div>
        <div class="card">
            <div class="card-header">
                <div class="card-title">Morro de São Paulo</div>
            </div>
            <div class="col-md-12">
                <div class="col-md-3 pull-left">
                    <div class="form-group">
                        <h6 align="center">06:30  <button type="button" onclick="visualizar()"><i class="fa fa-refresh"></i></button></h6>

                    </div>

                    <table class="table table-bordered" id="seismorro">
                        <thead>
                        <tr>
                            <th>Serviço</th>
                            <th>Previsto</th>
                            <th>Confirmado</th>
                        </tr>
                        </thead>
                        <tbody id="dadosseisetrinta">

                        </tbody>
                    </table>
                    <br>
                    <table class="table table-bordered" id="seismorroporoperador">
                        <thead>
                            <tr>
                                <th>Operador</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody id="dadosseisetrintaporoperador">

                        </tbody>
                    </table>
                </div>
                <div class="col-md-3 pull-left">
                    <div class="form-group">
                        <h6 align="center">08:30  <button type="button" onclick="visualizarOito()"><i class="fa fa-refresh"></i></button></h6>

                    </div>

                    <table class="table table-bordered" id="oitomorro">
                        <thead>
                        <tr>
                            <th>Serviço</th>
                            <th>Previsto</th>
                            <th>Confirmado</th>
                        </tr>
                        </thead>
                        <tbody id="dadosoitroetrinta">

                        </tbody>
                    </table>
                    <br>
                    <table class="table table-bordered" id="oitomorroporoperador">
                        <thead>
                        <tr>
                            <th>Operador</th>
                            <th>Total</th>
                        </tr>
                        </thead>
                        <tbody id="dadosoitoetrintaporoperador">

                        </tbody>
                    </table>
                </div>
                <div class="col-md-3 pull-left">
                    <div class="form-group">
                        <h6 align="center">09:30  <button type="button" onclick="visualizarNoveTrintaMorro()"><i class="fa fa-refresh"></i></button></h6>

                    </div>

                    <table class="table table-bordered" id="novetrintamorro">
                        <thead>
                        <tr>
                            <th>Serviço</th>
                            <th>Previsto</th>
                            <th>Confirmado</th>
                        </tr>
                        </thead>
                        <tbody id="dadosnovetrintamorro">

                        </tbody>
                    </table>
                    <br>
                    <table class="table table-bordered" id="novemorroporoperador">
                        <thead>
                        <tr>
                            <th>Operador</th>
                            <th>Total</th>
                        </tr>
                        </thead>
                        <tbody id="dadosnoveoetrintaporoperador">

                        </tbody>
                    </table>
                </div>
                <div class="col-md-3 pull-right">
                    <div class="form-group">
                        <h6 align="center">10:30  <button type="button" onclick="visualizarDez()"><i class="fa fa-refresh"></i></button></h6>

                    </div>

                    <table class="table table-bordered" id="dezmorro">
                        <thead>
                        <tr>
                            <th>Serviço</th>
                            <th>Previsto</th>
                            <th>Confirmado</th>
                        </tr>
                        </thead>
                        <tbody id="dadosdezetrinta">

                        </tbody>
                    </table>
                    <br>
                    <table class="table table-bordered" id="dezmorroporoperador">
                        <thead>
                        <tr>
                            <th>Operador</th>
                            <th>Total</th>
                        </tr>
                        </thead>
                        <tbody id="dadosdezoetrintaporoperador">

                        </tbody>
                    </table>
                </div>

                <div class="col-md-4 pull-left">
                    <div class="form-group">
                        <h6 align="center">12:30  <button type="button" onclick="visualizarDozeTrinta()"><i class="fa fa-refresh"></i></button></h6>

                    </div>

                    <table class="table table-bordered" id="dozeetrintamorro">
                        <thead>
                        <tr>
                            <th>Serviço</th>
                            <th>Previsto</th>
                            <th>Confirmado</th>
                        </tr>
                        </thead>
                        <tbody id="dadosdozeetrintamorro">

                        </tbody>
                    </table>
                    <br>
                    <table class="table table-bordered" id="dezemorroporoperador">
                        <thead>
                        <tr>
                            <th>Operador</th>
                            <th>Total</th>
                        </tr>
                        </thead>
                        <tbody id="dadosdozeoetrintaporoperador">

                        </tbody>
                    </table>
                </div>

                <div class="col-md-4 pull-left">
                    <div class="form-group">
                        <h6 align="center">13:30  <button type="button" onclick="visualizarTreze()"><i class="fa fa-refresh"></i></button></h6>

                    </div>

                    <table class="table table-bordered" id="trezemorro">
                        <thead>
                        <tr>
                            <th>Serviço</th>
                            <th>Previsto</th>
                            <th>Confirmado</th>
                        </tr>
                        </thead>
                        <tbody id="dadostrezeetrinta">

                        </tbody>
                    </table>
                    <br>
                    <table class="table table-bordered" id="trezeemorroporoperador">
                        <thead>
                        <tr>
                            <th>Operador</th>
                            <th>Total</th>
                        </tr>
                        </thead>
                        <tbody id="dadostrezeoetrintaporoperador">

                        </tbody>
                    </table>
                </div>
                <div class="col-md-4 pull-right">
                    <div class="form-group">
                        <h6 align="center">15:30  <button type="button" onclick="visualizarQuinze()"><i class="fa fa-refresh"></i></button></h6>

                    </div>

                    <table class="table table-bordered" id="quinzemorro">
                        <thead>
                        <tr>
                            <th>Serviço</th>
                            <th>Previsto</th>
                            <th>Confirmado</th>
                        </tr>
                        </thead>
                        <tbody id="dadosquinzeetrinta">

                        </tbody>
                    </table>
                    <br>
                    <table class="table table-bordered" id="quinzemorroporoperador">
                        <thead>
                        <tr>
                            <th>Operador</th>
                            <th>Total</th>
                        </tr>
                        </thead>
                        <tbody id="dadosquinzeetrintaporoperador">

                        </tbody>
                    </table>
                </div>
            </div>

        </div>
        <div class="card">
            <div class="card-header">
                <div class="card-title">Aeroporto</div>
            </div>
            <div class="col-md-12">
                <div class="col-md-4 pull-left">
                    <div class="form-group">
                        <h6 align="center">06:00  <button type="button" onclick="visualizarCincoAeroporto()"><i class="fa fa-refresh"></i></button></h6>

                    </div>

                    <table class="table table-bordered" id="cincoaeroporto">
                        <thead>
                        <tr>
                            <th>Serviço</th>
                            <th>Previsto</th>
                            <th>Confirmado</th>
                        </tr>
                        </thead>
                        <tbody id="dadoscincoaeroporto">

                        </tbody>
                    </table>
                    <br>
                    <table class="table table-bordered" id="cincoaeroportooperador">
                        <thead>
                        <tr>
                            <th>Operador</th>
                            <th>Total</th>
                        </tr>
                        </thead>
                        <tbody id="dadoscincoaeroportooperador">

                        </tbody>
                    </table>
                </div>
                <div class="col-md-4 pull-left">
                    <div class="form-group">
                        <h6 align="center">09:30  <button type="button" onclick="visualizarNoveAeroporto()"><i class="fa fa-refresh"></i></button></h6>

                    </div>

                    <table class="table table-bordered" id="noveaeroporto">
                        <thead>
                        <tr>
                            <th>Serviço</th>
                            <th>Previsto</th>
                            <th>Confirmado</th>
                        </tr>
                        </thead>
                        <tbody id="dadosnoveaeroporto">

                        </tbody>
                    </table>
                    <br>
                    <table class="table table-bordered" id="noveaeroportooperador">
                        <thead>
                        <tr>
                            <th>Operador</th>
                            <th>Total</th>
                        </tr>
                        </thead>
                        <tbody id="dadosnoveoaeroportooperador">

                        </tbody>
                    </table>
                </div>
                <div class="col-md-4 pull-right">
                    <div class="form-group">
                        <h6 align="center">11:30  <button type="button" onclick="visualizarOnzeAeroport()"><i class="fa fa-refresh"></i></button></h6>

                    </div>

                    <table class="table table-bordered" id="onzeaeroporto">
                        <thead>
                        <tr>
                            <th>Serviço</th>
                            <th>Previsto</th>
                            <th>Confirmado</th>
                        </tr>
                        </thead>
                        <tbody id="dadosonzeaeroporto">

                        </tbody>
                    </table>
                    <br>
                    <table class="table table-bordered" id="onzeaeroportooperador">
                        <thead>
                        <tr>
                            <th>Operador</th>
                            <th>Total</th>
                        </tr>
                        </thead>
                        <tbody id="dadosonzeoaeroportooperador">

                        </tbody>
                    </table>
                </div>
                <div class="col-md-6 pull-left">
                    <div class="form-group">
                        <h6 align="center">13:30  <button type="button" onclick="visualizarTrezeAeroporto()"><i class="fa fa-refresh"></i></button></h6>

                    </div>

                    <table class="table table-bordered" id="trezeaeroporto">
                        <thead>
                        <tr>
                            <th>Serviço</th>
                            <th>Previsto</th>
                            <th>Confirmado</th>
                        </tr>
                        </thead>
                        <tbody id="dadostrezeaeroporto">

                        </tbody>
                    </table>
                    <br>
                    <table class="table table-bordered" id="trezeaeroportooperador">
                        <thead>
                        <tr>
                            <th>Operador</th>
                            <th>Total</th>
                        </tr>
                        </thead>
                        <tbody id="dadostrezeoaeroportooperador">

                        </tbody>
                    </table>
                </div>
                <div class="col-md-6 pull-right">
                    <div class="form-group">
                        <h6 align="center">16:00  <button type="button" onclick="visualizarDezessseisAeroporto()"><i class="fa fa-refresh"></i></button></h6>

                    </div>

                    <table class="table table-bordered" id="dezesseisaeroporto">
                        <thead>
                        <tr>
                            <th>Serviço</th>
                            <th>Previsto</th>
                            <th>Confirmado</th>
                        </tr>
                        </thead>
                        <tbody id="dadosdezesseisaeroporto">

                        </tbody>
                    </table>
                    <br>
                    <table class="table table-bordered" id="dezesseisaeroportooperador">
                        <thead>
                        <tr>
                            <th>Operador</th>
                            <th>Total</th>
                        </tr>
                        </thead>
                        <tbody id="dadosdezesseisoaeroportooperador">

                        </tbody>
                    </table>
                </div>
            </div>

        </div>
        <div class="card">
            <div class="card-header">
                <div class="card-title">Cassi Comércio</div>
            </div>
            <div class="col-md-12">
                <div class="col-md-3 pull-left">
                    <div class="form-group">
                        <h6 align="center">07:30  <button type="button" onclick="visualizarSeteCassi()" id="setecassiclick"><i class="fa fa-refresh"></i></button></h6>

                    </div>

                    <table class="table table-bordered" id="setecassi">
                        <thead>
                        <tr>
                            <th>Serviço</th>
                            <th>Previsto</th>
                            <th>Confirmado</th>
                        </tr>
                        </thead>
                        <tbody id="dadossetecassi">

                        </tbody>
                    </table>
                    <br>
                    <table class="table table-bordered" id="sete-cassi-operador">
                        <thead>
                        <tr>
                            <th>Operador</th>
                            <th>Total</th>
                        </tr>
                        </thead>
                        <tbody id="dados-sete-cassi-operador">

                        </tbody>
                    </table>
                </div>
                <div class="col-md-3 pull-left">
                    <div class="form-group">
                        <h6 align="center">10:00  <button type="button" onclick="visualizarDezCAssi()"><i class="fa fa-refresh"></i></button></h6>

                    </div>

                    <table class="table table-bordered" id="dezcassi">
                        <thead>
                        <tr>
                            <th>Serviço</th>
                            <th>Previsto</th>
                            <th>Confirmado</th>
                        </tr>
                        </thead>
                        <tbody id="dadosdezcassi">

                        </tbody>
                    </table>
                    <br>
                    <table class="table table-bordered" id="dez-cassi-operador">
                        <thead>
                        <tr>
                            <th>Operador</th>
                            <th>Total</th>
                        </tr>
                        </thead>
                        <tbody id="dados-dez-cassi-operador">

                        </tbody>
                    </table>
                </div>
                <div class="col-md-3 pull-left">
                    <div class="form-group">
                        <h6 align="center">11:00  <button type="button" onclick="visualizarDozeCassi()"><i class="fa fa-refresh"></i></button></h6>

                    </div>

                    <table class="table table-bordered" id="dozecassi">
                        <thead>
                        <tr>
                            <th>Serviço</th>
                            <th>Previsto</th>
                            <th>Confirmado</th>
                        </tr>
                        </thead>
                        <tbody id="dadosdozecassi">

                        </tbody>
                    </table>
                    <br>
                    <table class="table table-bordered" id="doze-cassi-operador">
                        <thead>
                        <tr>
                            <th>Operador</th>
                            <th>Total</th>
                        </tr>
                        </thead>
                        <tbody id="dados-doze-cassi-operador">

                        </tbody>
                    </table>
                </div>
                <div class="col-md-3 pull-right">
                    <div class="form-group">
                        <h6 align="center">13:00  <button type="button" onclick="visualizarTrezeCassi()"><i class="fa fa-refresh"></i></button></h6>

                    </div>

                    <table class="table table-bordered" id="trezecassi">
                        <thead>
                        <tr>
                            <th>Serviço</th>
                            <th>Previsto</th>
                            <th>Confirmado</th>
                        </tr>
                        </thead>
                        <tbody id="dadostrezecassi">

                        </tbody>
                    </table>
                    <br>
                    <table class="table table-bordered" id="treze-cassi-operador">
                        <thead>
                        <tr>
                            <th>Operador</th>
                            <th>Total</th>
                        </tr>
                        </thead>
                        <tbody id="dados-treze-cassi-operador">

                        </tbody>
                    </table>

                </div>
                <div class="col-md-6 pull-left">
                    <div class="form-group">
                        <h6 align="center">15:00  <button type="button" onclick="visualizarQuinzeCassi()"><i class="fa fa-refresh"></i></button></h6>

                    </div>

                    <table class="table table-bordered" id="quinzecassi">
                        <thead>
                        <tr>
                            <th>Serviço</th>
                            <th>Previsto</th>
                            <th>Confirmado</th>
                        </tr>
                        </thead>
                        <tbody id="dadosquinzecassi">

                        </tbody>
                    </table>
                    <br>
                    <table class="table table-bordered" id="quinze-cassi-operador">
                        <thead>
                        <tr>
                            <th>Operador</th>
                            <th>Total</th>
                        </tr>
                        </thead>
                        <tbody id="dados-quinze-cassi-operador">

                        </tbody>
                    </table>
                </div>
                <div class="col-md-6 pull-right">
                    <div class="form-group">
                        <h6 align="center">17:00  <button type="button" onclick="visualizarDezesseteCassi()"><i class="fa fa-refresh"></i></button></h6>

                    </div>

                    <table class="table table-bordered" id="dezessetecassi">
                        <thead>
                        <tr>
                            <th>Serviço</th>
                            <th>Previsto</th>
                            <th>Confirmado</th>
                        </tr>
                        </thead>
                        <tbody id="dadosdezessetecassi">

                        </tbody>
                    </table>
                    <br>
                    <table class="table table-bordered" id="dezessete-cassi-operador">
                        <thead>
                        <tr>
                            <th>Operador</th>
                            <th>Total</th>
                        </tr>
                        </thead>
                        <tbody id="dados-dezessete-cassi-operador">

                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    </div>
    <script>

        function mensagem(msg) {
            var x = document.getElementById("snackbar");
            x.innerText = msg;
            x.className = "show";
        }
        function visualizar(){
            var nome          = [];
            var previsto      = [];
            var confirmado    = [];
            var operador      = [];
            var totaloperador = [];

            $('#seismorro tbody tr').remove();
            $('#dadosseisetrintaporoperador tbody tr').remove();
            $.ajax({
                type: 'POST',
                url: 'dadosprevisao.php',
                data: {previsaomorro: 1, seis: 1, inicio: $("#inicio").val(), fim:$("#fim").val() },
                dataType: 'json',
                beforeSend : function(){
                    setTimeout(function(){ mensagem("Buscando passageiros para o horário de 06:30, saindo de Morro de São Paulo. Aguarde !") });
                }
            }).done(function(response){
                if( response.length > 0 )
                {
                    $.each(response, function(key, item){
                        if(item.servico != null){
                            nome.push(item.servico);
                        }
                        if(item.previsto != null){
                            previsto.push(item.previsto);
                        }
                        if(item.confirmado != null){
                            confirmado.push(item.confirmado);
                        }
                        if(item.nomeoperador != null){
                            operador.push(item.nomeoperador);
                        }
                        if(item.totaloperador != null){
                            totaloperador.push(item.totaloperador);
                        }

                    });
                    for (var contador = 0; contador <= nome.length; contador++){
                        $("#dadosseisetrinta").append(
                            `<tr id="${nome[contador]}">
                                    <td>${nome[contador]}</td>
                                    <td>${previsto[contador]}</td>
                                    <td>${confirmado[contador]}</td>
                                </tr>`
                        );
                        $("#dadosseisetrintaporoperador").append(
                            `<tr id="${operador[contador]}">
                                    <td>${operador[contador]}</td>
                                    <td>${totaloperador[contador]}</td>
                                </tr>`
                        );
                        $("#undefined").closest('tr').remove();
                    }


                    setTimeout(function(){ mensagem("Passageiros encontrados, vamos para o proxímo horário 08:30, saindo de Morro de São Paulo.") });
                    setTimeout(function(){ visualizarOito() }, 3000);
                }else{
                    setTimeout(function(){ mensagem("Não encontramos passageiros para data e horário informado, vamos para o proxímo horário 08:30, saindo de Morro de São Paulo.") });
                    setTimeout(function(){ visualizarOito() }, 3000);
                }

            }).fail(function(jqXHR, textStatus, response){
                setTimeout(function(){ mensagem("Não encontramos passageiros para data e horário informado. Vamos para o próximo horário 08:30, saindo de Morro de São Paulo.!") });
                setTimeout(function(){ visualizarOito() }, 3000);
            });



        }
        function visualizarOito(){
            var nome = [];
            var previsto = [];
            var confirmado = [];
            var operador      = [];
            var totaloperador = [];
            $('#oitomorro tbody tr').remove();
            $('#oitomorroporoperador tbody tr').remove();
            $.ajax({
                type: 'POST',
                url: 'dadosprevisao.php',
                data: {previsaomorro: 1, oito: 1, inicio: $("#inicio").val(), fim:$("#fim").val() },
                dataType: 'json',
                beforeSend : function(){
                    setTimeout(function(){ mensagem("Buscando passageiros para o horário de 08:30, saindo de Morro de São Paulo. Aguarde !") });
                }
            }).done(function(response){
                if( response.length > 0 )
                {
                    $.each(response, function(key, item){
                        if(item.servico != null){
                            nome.push(item.servico);
                        }
                        if(item.previsto != null){
                            previsto.push(item.previsto);
                        }
                        if(item.confirmado != null){
                            confirmado.push(item.confirmado);
                        }
                        if(item.nomeoperador != null){
                            operador.push(item.nomeoperador);
                        }
                        if(item.totaloperador != null){
                            totaloperador.push(item.totaloperador);
                        }
                    });
                    for (var contador = 0; contador <= nome.length; contador++){
                        $("#dadosoitroetrinta").append(
                            `<tr id="${nome[contador]}">
                                    <td>${nome[contador]}</td>
                                    <td>${previsto[contador]}</td>
                                    <td>${confirmado[contador]}</td>
                                </tr>`
                        );
                        $("#dadosoitoetrintaporoperador").append(
                            `<tr id="${operador[contador]}">
                                    <td>${operador[contador]}</td>
                                    <td>${totaloperador[contador]}</td>
                                </tr>`
                        );
                    }
                    $("#undefined").closest('tr').remove();
                    setTimeout(function(){ mensagem("Passageiros encontrados, vamos para o proxímo horário 09:30, saindo de Morro de São Paulo.") });
                    setTimeout(function(){  visualizarNoveTrintaMorro() }, 3000);
                }else{
                    setTimeout(function(){ mensagem("Não encontramos passageiros para data e horário informado, vamos para o proxímo horário 09:30, saindo de Morro de São Paulo.") });
                    setTimeout(function(){  visualizarNoveTrintaMorro() }, 3000);
                }

            }).fail(function(jqXHR, textStatus, response){
                setTimeout(function(){ mensagem("Não encontramos passageiros para data e horário informado. Vamos para o próximo horário 09:30, saindo de Morro de São Paulo!") });
                setTimeout(function(){  visualizarNoveTrintaMorro() }, 3000);
            });

        }
        function visualizarNoveTrintaMorro(){
            var nome = [];
            var previsto = [];
            var confirmado = [];
            var operador      = [];
            var totaloperador = [];
            $('#novetrintamorro tbody tr').remove();
            $('#novemorroporoperador tbody tr').remove();
            $.ajax({
                type: 'POST',
                url: 'dadosprevisao.php',
                data: {previsaomorro: 1, novetrinta: 1, inicio: $("#inicio").val(), fim:$("#fim").val() },
                dataType: 'json',
                beforeSend : function(){
                    setTimeout(function(){ mensagem("Buscando passageiros para o horário de 09:30, saindo de Morro de São Paulo. Aguarde !") });
                }
            }).done(function(response){
                if( response.length > 0 )
                {
                    $.each(response, function(key, item){
                        if(item.servico != null){
                            nome.push(item.servico);
                        }
                        if(item.previsto != null){
                            previsto.push(item.previsto);
                        }
                        if(item.confirmado != null){
                            confirmado.push(item.confirmado);
                        }
                        if(item.nomeoperador != null){
                            operador.push(item.nomeoperador);
                        }
                        if(item.totaloperador != null){
                            totaloperador.push(item.totaloperador);
                        }
                    });
                    for (var contador = 0; contador <= nome.length; contador++){
                        $("#dadosnovetrintamorro").append(
                            `<tr id="${nome[contador]}">
                                    <td>${nome[contador]}</td>
                                    <td>${previsto[contador]}</td>
                                    <td>${confirmado[contador]}</td>
                                </tr>`
                        );
                        $("#dadosnoveoetrintaporoperador").append(
                            `<tr id="${operador[contador]}">
                                    <td>${operador[contador]}</td>
                                    <td>${totaloperador[contador]}</td>
                                </tr>`
                        );
                    }
                    $("#undefined").closest('tr').remove();
                    setTimeout(function(){ mensagem("Passageiros encontrados, vamos para o proxímo horário 10:30, saindo de Morro de São Paulo.") });
                    setTimeout(function(){ visualizarDez() }, 3000);
                }else{
                    setTimeout(function(){ mensagem("Não encontramos passageiros para data e horário informado, vamos para o proxímo horário 10:30, saindo de Morro de São Paulo.") });
                    setTimeout(function(){ visualizarDez() }, 3000);
                }

            }).fail(function(jqXHR, textStatus, response){
                setTimeout(function(){ mensagem("Não encontramos passageiros para data e horário informado. Vamos para o próximo horário 10:30, saindo de Morro de São Paulo!") });
                setTimeout(function(){ visualizarDez() }, 3000);
            });

        }
        function visualizarDez(){
            var nome = [];
            var previsto = [];
            var confirmado = [];
            var operador      = [];
            var totaloperador = [];
            $('#dezmorro tbody tr').remove();
            $('#dezmorroporoperador tbody tr').remove();
            $.ajax({
                type: 'POST',
                url: 'dadosprevisao.php',
                data: {previsaomorro: 1, dez: 1, inicio: $("#inicio").val(), fim:$("#fim").val() },
                dataType: 'json',
                beforeSend : function(){
                    setTimeout(function(){ mensagem("Buscando passageiros para o horário de 10:30, saindo de Morro de São Paulo. Aguarde !") });
                }
            }).done(function(response){
                if( response.length > 0 )
                {
                    $.each(response, function(key, item){
                        if(item.servico != null){
                            nome.push(item.servico);
                        }
                        if(item.previsto != null){
                            previsto.push(item.previsto);
                        }
                        if(item.confirmado != null){
                            confirmado.push(item.confirmado);
                        }
                        if(item.nomeoperador != null){
                            operador.push(item.nomeoperador);
                        }
                        if(item.totaloperador != null){
                            totaloperador.push(item.totaloperador);
                        }

                    });
                    for (var contador = 0; contador <= nome.length; contador++){
                        $("#dadosdezetrinta").append(
                            `<tr id="${nome[contador]}">
                                    <td>${nome[contador]}</td>
                                    <td>${previsto[contador]}</td>
                                    <td>${confirmado[contador]}</td>
                                </tr>`
                        );
                        $("#dadosdezoetrintaporoperador").append(
                            `<tr id="${operador[contador]}">
                                    <td>${operador[contador]}</td>
                                    <td>${totaloperador[contador]}</td>
                                </tr>`
                        );
                    }
                    $("#undefined").closest('tr').remove();
                    setTimeout(function(){ mensagem("Passageiros encontrados, vamos para o proxímo horário 12:30, saindo de Morro de São Paulo.") });
                    setTimeout(function(){ visualizarDozeTrinta() }, 3000);
                }else{
                    setTimeout(function(){ mensagem("Não encontramos passageiros para data e horário informado, vamos para o proxímo horário 12:30, saindo de Morro de São Paulo.") });
                    setTimeout(function(){ visualizarDozeTrinta() }, 3000);
                }

            }).fail(function(jqXHR, textStatus, response){
                setTimeout(function(){ mensagem("Não encontramos passageiros para data e horário informado. Vamos para o próximo horário 12:30, saindo de Morro de São Paulo.!") });
                setTimeout(function(){ visualizarDozeTrinta() }, 3000);
            });

        }
        function visualizarDozeTrinta(){
            var nome = [];
            var previsto = [];
            var confirmado = [];
            var operador      = [];
            var totaloperador = [];
            $('#dozeetrintamorro tbody tr').remove();
            $('#dezemorroporoperador tbody tr').remove();
            $.ajax({
                type: 'POST',
                url: 'dadosprevisao.php',
                data: {previsaomorro: 1, doze: 1, inicio: $("#inicio").val(), fim:$("#fim").val() },
                dataType: 'json',
                beforeSend : function(){
                    setTimeout(function(){ mensagem("Buscando passageiros para o horário de 12:30, saindo de Morro de São Paulo. Aguarde !") });
                }
            }).done(function(response){
                if( response.length > 0 )
                {
                    $.each(response, function(key, item){
                        if(item.servico != null){
                            nome.push(item.servico);
                        }
                        if(item.previsto != null){
                            previsto.push(item.previsto);
                        }
                        if(item.confirmado != null){
                            confirmado.push(item.confirmado);
                        }
                        if(item.nomeoperador != null){
                            operador.push(item.nomeoperador);
                        }
                        if(item.totaloperador != null){
                            totaloperador.push(item.totaloperador);
                        }
                    });
                    for (var contador = 0; contador <= nome.length; contador++){
                        $("#dadosdozeetrintamorro").append(
                            `<tr id="${nome[contador]}">
                                    <td>${nome[contador]}</td>
                                    <td>${previsto[contador]}</td>
                                    <td>${confirmado[contador]}</td>
                                </tr>`
                        );
                        $("#dadosdozeoetrintaporoperador").append(
                            `<tr id="${operador[contador]}">
                                    <td>${operador[contador]}</td>
                                    <td>${totaloperador[contador]}</td>
                                </tr>`
                        );
                    }
                    $("#undefined").closest('tr').remove();
                    setTimeout(function(){ mensagem("Passageiros encontrados, vamos para o proxímo horário 13:30 saindo de Morro de São Paulo") });
                    setTimeout(function(){ visualizarTreze() }, 3000);

                }else{
                    setTimeout(function(){ mensagem("Não encontramos passageiros para data e horário informado, vamos para o proxímo horário 13:30 saindo de Morro de São Paulo") });
                    setTimeout(function(){ visualizarTreze() }, 3000);
                }

            }).fail(function(jqXHR, textStatus, response){
                setTimeout(function(){ mensagem("Não encontramos passageiros para data e horário informado. Vamos para o próximo horário 13:30 saindo de Morro de São Paulo!") });
                setTimeout(function(){ visualizarTreze() }, 3000);
            });

        }
        function visualizarTreze(){
            var nome = [];
            var previsto = [];
            var confirmado = [];
            var operador      = [];
            var totaloperador = [];
            $('#trezemorro tbody tr').remove();
            $('#trezeemorroporoperador tbody tr').remove();
            $.ajax({
                type: 'POST',
                url: 'dadosprevisao.php',
                data: {previsaomorro: 1, treze: 1, inicio: $("#inicio").val(), fim:$("#fim").val() },
                dataType: 'json',
                beforeSend : function(){
                    setTimeout(function(){ mensagem("Buscando passageiros para o horário de 13:30 saindo do Morro de São Paulo. Aguarde !") });
                }
            }).done(function(response){
                if( response.length > 0 )
                {
                    $.each(response, function(key, item){
                        if(item.servico != null){
                            nome.push(item.servico);
                        }
                        if(item.previsto != null){
                            previsto.push(item.previsto);
                        }
                        if(item.confirmado != null){
                            confirmado.push(item.confirmado);
                        }
                        if(item.nomeoperador != null){
                            operador.push(item.nomeoperador);
                        }
                        if(item.totaloperador != null){
                            totaloperador.push(item.totaloperador);
                        }


                        });
                    for (var contador = 0; contador <= nome.length; contador++){
                        $("#dadostrezeetrinta").append(
                            `<tr id="${nome[contador]}">
                                    <td>${nome[contador]}</td>
                                    <td>${previsto[contador]}</td>
                                    <td>${confirmado[contador]}</td>
                                </tr>`
                        );
                        $("#dadostrezeoetrintaporoperador").append(
                            `<tr id="${operador[contador]}">
                                    <td>${operador[contador]}</td>
                                    <td>${totaloperador[contador]}</td>
                                </tr>`
                        );
                    }
                    $("#undefined").closest('tr').remove();
                    setTimeout(function(){ mensagem("Passageiros encontrados, vamos para o proxímo horário 15:30 saindo de Morro de São Paulo") });
                    setTimeout(function(){ visualizarQuinze() }, 3000);

                }else{
                    setTimeout(function(){ mensagem("Não encontramos passageiros para data e horário informado, vamos para o proxímo horário 15:30 saindo de Morro de São Paulo") });
                    setTimeout(function(){ visualizarQuinze() }, 3000);
                }

            }).fail(function(jqXHR, textStatus, response){
                setTimeout(function(){ mensagem("Não encontramos passageiros para data e horário informado. Vamos para o próximo horário 15:30 saindo de Morro de São Paulo!") });
                setTimeout(function(){ visualizarQuinze() }, 3000);
            });

        }
        function visualizarQuinze(){
            var nome = [];
            var previsto = [];
            var confirmado = [];
            var operador      = [];
            var totaloperador = [];
            $('#quinzemorro tbody tr').remove();
            $('#quinzemorroporoperador tbody tr').remove();
            $.ajax({
                type: 'POST',
                url: 'dadosprevisao.php',
                data: {previsaomorro: 1, quinze: 1, inicio: $("#inicio").val(), fim:$("#fim").val() },
                dataType: 'json',
                beforeSend : function(){
                    setTimeout(function(){ mensagem("Buscando passageiros para o horário de 15:30 saindo do Morro de São Paulo. Aguarde !") });
                }
            }).done(function(response){
                if( response.length > 0 )
                {
                    $.each(response, function(key, item){
                        if(item.servico != null){
                            nome.push(item.servico);
                        }
                        if(item.previsto != null){
                            previsto.push(item.previsto);
                        }
                        if(item.confirmado != null){
                            confirmado.push(item.confirmado);
                        }
                        if(item.nomeoperador != null){
                            operador.push(item.nomeoperador);
                        }
                        if(item.totaloperador != null){
                            totaloperador.push(item.totaloperador);
                        }
                    });
                    for (var contador = 0; contador <= nome.length; contador++){
                        $("#dadosquinzeetrinta").append(
                            `<tr id="${nome[contador]}">
                                    <td>${nome[contador]}</td>
                                    <td>${previsto[contador]}</td>
                                    <td>${confirmado[contador]}</td>
                                </tr>`
                        );
                        $("#dadosquinzeetrintaporoperador").append(
                            `<tr id="${operador[contador]}">
                                    <td>${operador[contador]}</td>
                                    <td>${totaloperador[contador]}</td>
                                </tr>`
                        );
                    }
                    $("#undefined").closest('tr').remove();
                    setTimeout(function(){ mensagem("Passageiros encontrados, vamos para o proxímo horário 05:30 saindo do Aeroporto Internacional de Salvador") });
                    setTimeout(function(){  visualizarCincoAeroporto() }, 3000);
                }else{
                    setTimeout(function(){ mensagem("Não encontramos passageiros para data e horário informado, vamos para o proxímo horário 05:30 saindo do Aeroporto Internacional de Salvador") });
                    setTimeout(function(){  visualizarCincoAeroporto() }, 3000);
                }

            }).fail(function(jqXHR, textStatus, response){
                setTimeout(function(){ mensagem("Não encontramos passageiros para data e horário informado. Vamos para o próximo horário 05:30 saindo do Aeroporto Internacional de Salvador!") });
                setTimeout(function(){  visualizarCincoAeroporto() }, 3000);
            });

        }

        function visualizarCincoAeroporto(){
            var nome = [];
            var previsto = [];
            var confirmado = [];
            var operador      = [];
            var totaloperador = [];
            $('#cincoaeroporto tbody tr').remove();
            $('#cincoaeroportooperador tbody tr').remove();
            $.ajax({
                type: 'POST',
                url: 'dadosprevisao.php',
                data: {aeroporto: 1, aeroportocinco: 1, inicio: $("#inicio").val(), fim:$("#fim").val() },
                dataType: 'json',
                beforeSend : function(){
                    setTimeout(function(){ mensagem("Buscando passageiros para o horário de 05:30 saindo do Aeroporto Internacional de Salvador. Aguarde !") });
                }
            }).done(function(response){
                if( response.length > 0 )
                {
                    $.each(response, function(key, item){
                        if(item.servico != null){
                            nome.push(item.servico);
                        }
                        if(item.previsto != null){
                            previsto.push(item.previsto);
                        }
                        if(item.confirmado != null){
                            confirmado.push(item.confirmado);
                        }
                        if(item.nomeoperador != null){
                            operador.push(item.nomeoperador);
                        }
                        if(item.totaloperador != null){
                            totaloperador.push(item.totaloperador);
                        }

                    });
                    for (var contador = 0; contador <= nome.length; contador++){
                        $("#dadoscincoaeroporto").append(
                            `<tr id="${nome[contador]}">
                                    <td>${nome[contador]}</td>
                                    <td>${previsto[contador]}</td>
                                    <td>${confirmado[contador]}</td>
                                </tr>`
                        );
                        $("#dadoscincoaeroportooperador").append(
                            `<tr id="${operador[contador]}">
                                    <td>${operador[contador]}</td>
                                    <td>${totaloperador[contador]}</td>
                                </tr>`
                        );
                    }
                    $("#undefined").closest('tr').remove();
                    setTimeout(function(){ mensagem("Passageiros encontrados, vamos para o proxímo horário 09:30 saindo do Aeroporto Internacional de Salvador") });
                    setTimeout(function(){  visualizarNoveAeroporto() }, 3000);
                }else{
                    setTimeout(function(){ mensagem("Não encontramos passageiros para data e horário informado, vamos para o proxímo horário 09:30 saindo do Aeroporto Internacional de Salvador") });
                    setTimeout(function(){  visualizarNoveAeroporto() }, 3000);
                }

            }).fail(function(jqXHR, textStatus, response){
                setTimeout(function(){ mensagem("Não encontramos passageiros para data e horário informado, vamos para o proxímo horário 09:30 saindo do Aeroporto Internacional de Salvador") });
                setTimeout(function(){  visualizarNoveAeroporto() }, 3000);
            });

        }
        function visualizarNoveAeroporto(){
            var nome = [];
            var previsto = [];
            var confirmado = [];
            var operador      = [];
            var totaloperador = [];
            $('#noveaeroporto tbody tr').remove();
            $('#noveaeroportooperador tbody tr').remove();
            $.ajax({
                type: 'POST',
                url: 'dadosprevisao.php',
                data: {aeroporto: 1, aeroportonove: 1, inicio: $("#inicio").val(), fim:$("#fim").val() },
                dataType: 'json',
                beforeSend : function(){
                    setTimeout(function(){ mensagem("Buscando passageiros para o horário de 09:30 saindo do Aeroporto Internacional de Salvador. Aguarde !") });
                }
            }).done(function(response){
                    if( response.length > 0 )
                    {
                        $.each(response, function(key, item){
                            if(item.servico != null){
                                nome.push(item.servico);
                            }
                            if(item.previsto != null){
                                previsto.push(item.previsto);
                            }
                            if(item.confirmado != null){
                                confirmado.push(item.confirmado);
                            }
                            if(item.nomeoperador != null){
                                operador.push(item.nomeoperador);
                            }
                            if(item.totaloperador != null){
                                totaloperador.push(item.totaloperador);
                            }

                        });
                        for (var contador = 0; contador <= nome.length; contador++){
                            $("#dadosnoveaeroporto").append(
                                `<tr id="${nome[contador]}">
                                    <td>${nome[contador]}</td>
                                    <td>${previsto[contador]}</td>
                                    <td>${confirmado[contador]}</td>
                                </tr>`
                            );
                            $("#dadosnoveoaeroportooperador").append(
                                `<tr id="${operador[contador]}">
                                    <td>${operador[contador]}</td>
                                    <td>${totaloperador[contador]}</td>
                                </tr>`
                            );
                        }
                        $("#undefined").closest('tr').remove();
                        setTimeout(function(){ mensagem("Passageiros encontrados, vamos para o proxímo horário 11:30 saindo do Aeroporto Internacional de Salvador") });
                        setTimeout(function(){  visualizarOnzeAeroport() }, 3000);
                    }else{
                        setTimeout(function(){ mensagem("Não encontramos passageiros para data e horário informado, vamos para o proxímo horário 11:30 saindo do Aeroporto Internacional de Salvador") });
                        setTimeout(function(){  visualizarOnzeAeroport() }, 3000);
                    }

                }).fail(function(jqXHR, textStatus, response){
                setTimeout(function(){ mensagem("Não encontramos passageiros para data e horário informado, vamos para o proxímo horário 11:30 saindo do Aeroporto Internacional de Salvador") });
                setTimeout(function(){  visualizarOnzeAeroport() }, 3000);
                });

        }
        function visualizarOnzeAeroport(){
            var nome = [];
            var previsto = [];
            var confirmado = [];
            var operador      = [];
            var totaloperador = [];
            $('#onzeaeroporto tbody tr').remove();
            $('#onzeaeroportooperador tbody tr').remove();
            $.ajax({
                type: 'POST',
                url: 'dadosprevisao.php',
                data: {aeroporto: 1, aeroportoonze: 1, inicio: $("#inicio").val(), fim:$("#fim").val() },
                dataType: 'json',
                beforeSend : function(){
                    setTimeout(function(){ mensagem("Buscando passageiros para o horário de 11:30 saindo do Aeroporto Internacional de Salvador. Aguarde !") });
                }
            })
                .done(function(response){
                    if( response.length > 0 )
                    {
                        $.each(response, function(key, item){
                            if(item.servico != null){
                                nome.push(item.servico);
                            }
                            if(item.previsto != null){
                                previsto.push(item.previsto);
                            }
                            if(item.confirmado != null){
                                confirmado.push(item.confirmado);
                            }
                            if(item.nomeoperador != null){
                                operador.push(item.nomeoperador);
                            }
                            if(item.totaloperador != null){
                                totaloperador.push(item.totaloperador);
                            }
                        });
                        for (var contador = 0; contador <= nome.length; contador++){
                            $("#dadosonzeaeroporto").append(
                                `<tr id="${nome[contador]}">
                                    <td>${nome[contador]}</td>
                                    <td>${previsto[contador]}</td>
                                    <td>${confirmado[contador]}</td>
                                </tr>`
                            );
                            $("#dadosonzeoaeroportooperador").append(
                                `<tr id="${operador[contador]}">
                                    <td>${operador[contador]}</td>
                                    <td>${totaloperador[contador]}</td>
                                </tr>`
                            );
                        }
                        $("#undefined").closest('tr').remove();
                        setTimeout(function(){ mensagem("Passageiros encontrados, vamos para o proxímo horário 13:30 saindo do Aeroporto Internacional de Salvador") });
                        setTimeout(function(){  visualizarTrezeAeroporto() }, 3000);
                    }else{
                        setTimeout(function(){ mensagem("Não encontramos passageiros para data e horário informado, vamos para o proxímo horário 13:30 saindo do Aeroporto Internacional de Salvador") });
                        setTimeout(function(){  visualizarTrezeAeroporto() }, 3000);
                    }

                })
                .fail(function(jqXHR, textStatus, response){
                    setTimeout(function(){ mensagem("Não encontramos passageiros para data e horário informado, vamos para o proxímo horário 13:30 saindo do Aeroporto Internacional de Salvador") });
                    setTimeout(function(){  visualizarTrezeAeroporto() }, 3000);
                });

        }
        function visualizarTrezeAeroporto(){
            var nome = [];
            var previsto = [];
            var confirmado = [];
            var operador      = [];
            var totaloperador = [];
            $('#trezeaeroporto tbody tr').remove();
            $('#trezeaeroportooperador tbody tr').remove();
            $.ajax({
                type: 'POST',
                url: 'dadosprevisao.php',
                data: {aeroporto: 1, aeroportotreze: 1, inicio: $("#inicio").val(), fim:$("#fim").val() },
                dataType: 'json',
                beforeSend : function(){
                    setTimeout(function(){ mensagem("Buscando passageiros para o horário de 13:30 saindo do Aeroporto Internacional de Salvador. Aguarde !") });
                }
            })
                .done(function(response){
                    if( response.length > 0 )
                    {
                        $.each(response, function(key, item){
                            if(item.servico != null){
                                nome.push(item.servico);
                            }
                            if(item.previsto != null){
                                previsto.push(item.previsto);
                            }
                            if(item.confirmado != null){
                                confirmado.push(item.confirmado);
                            }
                            if(item.nomeoperador != null){
                                operador.push(item.nomeoperador);
                            }
                            if(item.totaloperador != null){
                                totaloperador.push(item.totaloperador);
                            }


                            });
                        for (var contador = 0; contador <= nome.length; contador++){
                            $("#dadostrezeaeroporto").append(
                                `<tr id="${nome[contador]}">
                                    <td>${nome[contador]}</td>
                                    <td>${previsto[contador]}</td>
                                    <td>${confirmado[contador]}</td>
                                </tr>`
                            );
                            $("#dadostrezeoaeroportooperador").append(
                                `<tr id="${operador[contador]}">
                                    <td>${operador[contador]}</td>
                                    <td>${totaloperador[contador]}</td>
                                </tr>`
                            );
                        }
                        $("#undefined").closest('tr').remove();
                        setTimeout(function(){ mensagem("Passageiros encontrados, vamos para o proxímo horário 16:00 saindo do Aeroporto Internacional de Salvador") });
                        setTimeout(function(){  visualizarDezessseisAeroporto() }, 3000);
                    }else{
                        setTimeout(function(){ mensagem("Não encontramos passageiros para data e horário informado, vamos para o proxímo horário 16:00 saindo do Aeroporto Internacional de Salvador") });
                        setTimeout(function(){  visualizarDezessseisAeroporto() }, 3000);
                    }

                })
                .fail(function(jqXHR, textStatus, response){
                    setTimeout(function(){ mensagem("Não encontramos passageiros para data e horário informado, vamos para o proxímo horário 16:00 saindo do Aeroporto Internacional de Salvador") });
                    setTimeout(function(){  visualizarDezessseisAeroporto() }, 3000);
                });


        }
        function visualizarDezessseisAeroporto(){
            var nome = [];
            var previsto = [];
            var confirmado = [];
            var operador      = [];
            var totaloperador = [];
            $('#dezesseisaeroporto tbody tr').remove();
            $('#dezesseisaeroportooperador tbody tr').remove();
            $.ajax({
                type: 'POST',
                url: 'dadosprevisao.php',
                data: {aeroporto: 1, aeroportodezesseis: 1, inicio: $("#inicio").val(), fim:$("#fim").val() },
                dataType: 'json',
                beforeSend : function(){
                    setTimeout(function(){ mensagem("Buscando passageiros para o horário de 16:00 saindo do Aeroporto Internacional de Salvador. Aguarde !") });
                }
            })
             .done(function(response){
                 if( response.length > 0 )
                 {
                     $.each(response, function(key, item){
                         if(item.servico != null){
                             nome.push(item.servico);
                         }
                         if(item.previsto != null){
                             previsto.push(item.previsto);
                         }
                         if(item.confirmado != null){
                             confirmado.push(item.confirmado);
                         }
                         if(item.nomeoperador != null){
                             operador.push(item.nomeoperador);
                         }
                         if(item.totaloperador != null){
                             totaloperador.push(item.totaloperador);
                         }

                     });
                     for (var contador = 0; contador <= nome.length; contador++){
                         $("#dadosdezesseisaeroporto").append(
                             `<tr id="${nome[contador]}">
                                    <td>${nome[contador]}</td>
                                    <td>${previsto[contador]}</td>
                                    <td>${confirmado[contador]}</td>
                                </tr>`
                         );
                         $("#dadosdezesseisoaeroportooperador").append(
                             `<tr id="${operador[contador]}">
                                    <td>${operador[contador]}</td>
                                    <td>${totaloperador[contador]}</td>
                                </tr>`
                         );
                     }
                     $("#undefined").closest('tr').remove();
                     setTimeout(function(){ mensagem("Passageiros encontrados, vamos para o proxímo horário 07:30 saindo do Terminal Marítimo de Salvador") });
                     setTimeout(function(){  visualizarSeteCassi() }, 3000);
                 }else{
                     setTimeout(function(){ mensagem("Não encontramos passageiros para data e horário informado, vamos para o proxímo horário 07:30 saindo do Terminal Marítimo de Salvador") });
                     setTimeout(function(){  visualizarSeteCassi() }, 3000);
                 }

             })
             .fail(function(jqXHR, textStatus, response){
                 setTimeout(function(){ mensagem("Não encontramos passageiros para data e horário informado, vamos para o proxímo horário 07:30 saindo do Terminal Marítimo de Salvador") });
                 setTimeout(function(){  visualizarSeteCassi() }, 3000);
             });

        }



        function visualizarSeteCassi(){
            var nome = [];
            var previsto = [];
            var confirmado = [];
            var operador      = [];
            var totaloperador = [];
            $('#setecassi tbody tr').remove();
            $('#sete-cassi-operador tbody tr').remove();
            $.ajax({
                type: 'POST',
                url: 'dadosprevisao.php',
                data: {cassicomercio: 1, setetrintacassi: 1, inicio: $("#inicio").val(), fim:$("#fim").val() },
                dataType: 'json',
                beforeSend : function(){
                    setTimeout(function(){ mensagem("Buscando passageiros para o horário de 07:30 saindo doaindo do Terminal Marítimo de Salvador. Aguarde !") });
                }
            })
                .done(function(response){
                    if( response.length > 0 )
                    {
                        $.each(response, function(key, item){
                            if(item.servico != null){
                                nome.push(item.servico);
                            }
                            if(item.previsto != null){
                                previsto.push(item.previsto);
                            }
                            if(item.confirmado != null){
                                confirmado.push(item.confirmado);
                            }
                            if(item.nomeoperador != null){
                                operador.push(item.nomeoperador);
                            }
                            if(item.totaloperador != null){
                                totaloperador.push(item.totaloperador);
                            }

                        });
                        for (var contador = 0; contador <= nome.length; contador++){
                            $("#dadossetecassi").append(
                                `<tr id="${nome[contador]}">
                                    <td>${nome[contador]}</td>
                                    <td>${previsto[contador]}</td>
                                    <td>${confirmado[contador]}</td>
                                </tr>`
                            );
                            $("#dados-sete-cassi-operador").append(
                                `<tr id="${operador[contador]}">
                                    <td>${operador[contador]}</td>
                                    <td>${totaloperador[contador]}</td>
                                </tr>`
                            );
                        }
                        $("#undefined").closest('tr').remove();
                        setTimeout(function(){ mensagem("Passageiros encontrados, vamos para o proxímo horário 10:00 saindo do Terminal Marítimo de Salvador") });
                        setTimeout(function(){  visualizarDezCAssi() }, 3000);
                    }else{
                        setTimeout(function(){ mensagem("Não encontramos passageiros para data e horário informado, vamos para o proxímo horário 10:00 saindo do Terminal Marítimo de Salvador") });
                        setTimeout(function(){  visualizarDezCAssi() }, 3000);
                    }

                })
                .fail(function(jqXHR, textStatus, response){
                    setTimeout(function(){ mensagem("Não encontramos passageiros para data e horário informado, vamos para o proxímo horário 10:00 saindo do Terminal Marítimo de Salvador") });
                    setTimeout(function(){  visualizarDezCAssi() }, 3000);
                });
        }
        function visualizarDezCAssi(){
            var nome = [];
            var previsto = [];
            var confirmado = [];
            var operador      = [];
            var totaloperador = [];
            $('#dezcassi tbody tr').remove();
            $('#dez-cassi-operador tbody tr').remove();
            $.ajax({
                type: 'POST',
                url: 'dadosprevisao.php',
                data: {cassicomercio: 1, deztrintacassi: 1, inicio: $("#inicio").val(), fim:$("#fim").val() },
                dataType: 'json',
                beforeSend : function(){
                    setTimeout(function(){ mensagem("Buscando passageiros para o horário de 07:30 saindo doaindo do Terminal Marítimo de Salvador. Aguarde !") });
                }
            })
                .done(function(response){
                    if( response.length > 0 )
                    {
                        $.each(response, function(key, item){
                            if(item.servico != null){
                                nome.push(item.servico);
                            }
                            if(item.previsto != null){
                                previsto.push(item.previsto);
                            }
                            if(item.confirmado != null){
                                confirmado.push(item.confirmado);
                            }
                            if(item.nomeoperador != null){
                                operador.push(item.nomeoperador);
                            }
                            if(item.totaloperador != null){
                                totaloperador.push(item.totaloperador);
                            }

                        });
                        for (var contador = 0; contador <= nome.length; contador++){
                            $("#dadossetecassi").append(
                                `<tr id="${nome[contador]}">
                                    <td>${nome[contador]}</td>
                                    <td>${previsto[contador]}</td>
                                    <td>${confirmado[contador]}</td>
                                </tr>`
                            );
                            $("#dados-dez-cassi-operador").append(
                                `<tr id="${operador[contador]}">
                                    <td>${operador[contador]}</td>
                                    <td>${totaloperador[contador]}</td>
                                </tr>`
                            );
                        }
                        $("#undefined").closest('tr').remove();
                        setTimeout(function(){ mensagem("Passageiros encontrados, vamos para o proxímo horário 11:00 saindo do Terminal Marítimo de Salvador") });
                        setTimeout(function(){  visualizarDozeCassi() }, 3000);
                    }else{
                        setTimeout(function(){ mensagem("Não encontramos passageiros para data e horário informado, vamos para o proxímo horário 11:00 saindo do Terminal Marítimo de Salvador") });
                        setTimeout(function(){  visualizarDozeCassi() }, 3000);
                    }

                })
                .fail(function(jqXHR, textStatus, response){
                    setTimeout(function(){ mensagem("Não encontramos passageiros para data e horário informado, vamos para o proxímo horário 11:00 saindo do Terminal Marítimo de Salvador") });
                    setTimeout(function(){  visualizarDozeCassi() }, 3000);
                });
        }
        function visualizarDozeCassi(){
            var nome = [];
            var previsto = [];
            var confirmado = [];
            var operador      = [];
            var totaloperador = [];
            $('#dozecassi tbody tr').remove();
            $('#doze-cassi-operador tbody tr').remove();
            $.ajax({
                type: 'POST',
                url: 'dadosprevisao.php',
                data: {cassicomercio: 1, dozetrintacassi: 1, inicio: $("#inicio").val(), fim:$("#fim").val() },
                dataType: 'json',
                beforeSend : function(){
                    setTimeout(function(){ mensagem("Buscando passageiros para o horário de 11:00 saindo doaindo do Terminal Marítimo de Salvador. Aguarde !") });
                }
            })
                .done(function(response){
                    if( response.length > 0 )
                    {
                        $.each(response, function(key, item){
                            if(item.servico != null){
                                nome.push(item.servico);
                            }
                            if(item.previsto != null){
                                previsto.push(item.previsto);
                            }
                            if(item.confirmado != null){
                                confirmado.push(item.confirmado);
                            }
                            if(item.nomeoperador != null){
                                operador.push(item.nomeoperador);
                            }
                            if(item.totaloperador != null){
                                totaloperador.push(item.totaloperador);
                            }

                        });
                        for (var contador = 0; contador <= nome.length; contador++){
                            $("#dadosdozecassi").append(
                                `<tr id="${nome[contador]}">
                                    <td>${nome[contador]}</td>
                                    <td>${previsto[contador]}</td>
                                    <td>${confirmado[contador]}</td>
                                </tr>`
                            );
                            $("#dados-doze-cassi-operadorr").append(
                                `<tr id="${operador[contador]}">
                                    <td>${operador[contador]}</td>
                                    <td>${totaloperador[contador]}</td>
                                </tr>`
                            );
                        }
                        $("#undefined").closest('tr').remove();
                        setTimeout(function(){ mensagem("Passageiros encontrados, vamos para o proxímo horário 13:00 saindo do Terminal Marítimo de Salvador") });
                        setTimeout(function(){  visualizarTrezeCassi() }, 3000);
                    }else{
                        setTimeout(function(){ mensagem("Não encontramos passageiros para data e horário informado, vamos para o proxímo horário 13:00 saindo do Terminal Marítimo de Salvador") });
                        setTimeout(function(){  visualizarTrezeCassi() }, 3000);
                    }

                })
                .fail(function(jqXHR, textStatus, response){
                    setTimeout(function(){ mensagem("Não encontramos passageiros para data e horário informado, vamos para o proxímo horário 13:00 saindo do Terminal Marítimo de Salvador") });
                    setTimeout(function(){  visualizarTrezeCassi() }, 3000);
                });
        }
        function visualizarTrezeCassi(){
            var nome = [];
            var previsto = [];
            var confirmado = [];
            var operador      = [];
            var totaloperador = [];
            $('#trezecassi tbody tr').remove();
            $('#treze-cassi-operador tbody tr').remove();
            $.ajax({
                type: 'POST',
                url: 'dadosprevisao.php',
                data: {cassicomercio: 1, trezecassi: 1, inicio: $("#inicio").val(), fim:$("#fim").val() },
                dataType: 'json',
                beforeSend : function(){
                    setTimeout(function(){ mensagem("Buscando passageiros para o horário de 13:00 saindo doaindo do Terminal Marítimo de Salvador. Aguarde !") });
                }
            })
                .done(function(response){
                    if( response.length > 0 )
                    {
                        $.each(response, function(key, item){
                            if(item.servico != null){
                                nome.push(item.servico);
                            }
                            if(item.previsto != null){
                                previsto.push(item.previsto);
                            }
                            if(item.confirmado != null){
                                confirmado.push(item.confirmado);
                            }
                            if(item.nomeoperador != null){
                                operador.push(item.nomeoperador);
                            }
                            if(item.totaloperador != null){
                                totaloperador.push(item.totaloperador);
                            }
                        });
                        for (var contador = 0; contador <= nome.length; contador++){
                            $("#dadostrezecassi").append(
                                `<tr id="${nome[contador]}">
                                    <td>${nome[contador]}</td>
                                    <td>${previsto[contador]}</td>
                                    <td>${confirmado[contador]}</td>
                                </tr>`
                            );
                            $("#dados-treze-cassi-operador").append(
                                `<tr id="${operador[contador]}">
                                    <td>${operador[contador]}</td>
                                    <td>${totaloperador[contador]}</td>
                                </tr>`
                            );
                        }
                        $("#undefined").closest('tr').remove();
                        setTimeout(function(){ mensagem("Passageiros encontrados, vamos para o proxímo horário 15:00 saindo do Terminal Marítimo de Salvador") });
                        setTimeout(function(){  visualizarQuinzeCassi() }, 3000);
                    }else{
                        setTimeout(function(){ mensagem("Não encontramos passageiros para data e horário informado, vamos para o proxímo horário 15:00 saindo do Terminal Marítimo de Salvador") });
                        setTimeout(function(){  visualizarQuinzeCassi() }, 3000);
                    }

                })
                .fail(function(jqXHR, textStatus, response){
                    setTimeout(function(){ mensagem("Não encontramos passageiros para data e horário informado, vamos para o proxímo horário 15:00 saindo do Terminal Marítimo de Salvador") });
                    setTimeout(function(){  visualizarQuinzeCassi() }, 3000);
                });
        }
        function visualizarQuinzeCassi(){
            var nome = [];
            var previsto = [];
            var confirmado = [];
            var operador      = [];
            var totaloperador = [];
            $('#quinzecassi tbody tr').remove();
            $('#quinze-cassi-operador tbody tr').remove();
            $.ajax({
                type: 'POST',
                url: 'dadosprevisao.php',
                data: {cassicomercio: 1, quinzecassi: 1, inicio: $("#inicio").val(), fim:$("#fim").val() },
                dataType: 'json',
                beforeSend : function(){
                    setTimeout(function(){ mensagem("Buscando passageiros para o horário de 15:00 saindo doaindo do Terminal Marítimo de Salvador. Aguarde !") });
                }
            })
                .done(function(response){
                    if( response.length > 0 )
                    {
                        $.each(response, function(key, item){
                            if(item.servico != null){
                                nome.push(item.servico);
                            }
                            if(item.previsto != null){
                                previsto.push(item.previsto);
                            }
                            if(item.confirmado != null){
                                confirmado.push(item.confirmado);
                            }
                            if(item.nomeoperador != null){
                                operador.push(item.nomeoperador);
                            }
                            if(item.totaloperador != null){
                                totaloperador.push(item.totaloperador);
                            }

                        });
                        for (var contador = 0; contador <= nome.length; contador++){
                            $("#dadosquinzecassi").append(
                                `<tr id="${nome[contador]}">
                                    <td>${nome[contador]}</td>
                                    <td>${previsto[contador]}</td>
                                    <td>${confirmado[contador]}</td>
                                </tr>`
                            );
                            $("#dados-quinze-cassi-operador").append(
                                `<tr id="${operador[contador]}">
                                    <td>${operador[contador]}</td>
                                    <td>${totaloperador[contador]}</td>
                                </tr>`
                            );
                        }
                        $("#undefined").closest('tr').remove();
                        setTimeout(function(){ mensagem("Passageiros encontrados, vamos para o proxímo horário 17:00 saindo do Terminal Marítimo de Salvador") });
                        setTimeout(function(){  visualizarDezesseteCassi() }, 3000);
                    }else{
                        setTimeout(function(){ mensagem("Não encontramos passageiros para data e horário informado, vamos para o proxímo horário 17:00 saindo do Terminal Marítimo de Salvador") });
                        setTimeout(function(){  visualizarDezesseteCassi() }, 3000);
                    }

                })
                .fail(function(jqXHR, textStatus, response){
                    setTimeout(function(){ mensagem("Não encontramos passageiros para data e horário informado, vamos para o proxímo horário 17:00 saindo do Terminal Marítimo de Salvador") });
                    setTimeout(function(){  visualizarDezesseteCassi() }, 3000);
                });
        }
        function visualizarDezesseteCassi(){
            var nome = [];
            var previsto = [];
            var confirmado = [];
            var operador      = [];
            var totaloperador = [];
            $('#dezessetecassi tbody tr').remove();
            $('#dezessete-cassi-operador tbody tr').remove();
            $.ajax({
                type: 'POST',
                url: 'dadosprevisao.php',
                data: {cassicomercio: 1, dezessetecassi: 1, inicio: $("#inicio").val(), fim:$("#fim").val() },
                dataType: 'json',
                beforeSend : function(){
                    setTimeout(function(){ mensagem("Buscando passageiros para o horário de 15:00 saindo doaindo do Terminal Marítimo de Salvador. Aguarde !") });
                }
            })
                .done(function(response){
                    if( response.length > 0 )
                    {
                        $.each(response, function(key, item){
                            if(item.servico != null){
                                nome.push(item.servico);
                            }
                            if(item.previsto != null){
                                previsto.push(item.previsto);
                            }
                            if(item.confirmado != null){
                                confirmado.push(item.confirmado);
                            }
                            if(item.nomeoperador != null){
                                operador.push(item.nomeoperador);
                            }
                            if(item.totaloperador != null){
                                totaloperador.push(item.totaloperador);
                            }

                        });
                        for (var contador = 0; contador <= nome.length; contador++){
                            $("#dadosdezessetecassi").append(
                                `<tr id="${nome[contador]}">
                                    <td>${nome[contador]}</td>
                                    <td>${previsto[contador]}</td>
                                    <td>${confirmado[contador]}</td>
                                </tr>`
                            );
                            $("#dados-dezessete-cassi-operador").append(
                                `<tr id="${operador[contador]}">
                                    <td>${operador[contador]}</td>
                                    <td>${totaloperador[contador]}</td>
                                </tr>`
                            );
                        }
                        $("#undefined").closest('tr').remove();
                        setTimeout(function(){ mensagem("Passageiros encontrados, Após dez minutos todos os horários serão atualizados automáticamente.") });
                        setTimeout(function(){  visualizar() }, 600000);
                    }else{
                        setTimeout(function(){ mensagem("Não encontramos passageiros para data e horário informado, Após dez minutos todos os horários serão atualizados automáticamente.") });
                        setTimeout(function(){  visualizar() }, 600000);
                    }

                })
                .fail(function(jqXHR, textStatus, response){
                    setTimeout(function(){ mensagem("Não encontramos passageiros para data e horário informado, Após dez minutos todos os horários serão atualizados automáticamente.") });
                    setTimeout(function(){  visualizar() }, 600000);
                });
        }
        window.onload = initPage;

        function initPage()
        {
            visualizar();
        }

    </script>
    <?php require_once ('footer.php'); ?>
