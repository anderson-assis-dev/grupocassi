
<!-- COPYRIGHT-->
<section class="p-t-60 p-b-20">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="copyright">
                    <p>Copyright © <?php echo( date("Y") ); ?> CASSI TURISMO. Todos os direitos reservados.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- END COPYRIGHT-->
</div>

</div>
<!-- Jquery JS-->
<script src=".././vendor/jquery-3.2.1.min.js"></script>

<!-- Data Table -->
<script src=".././vendor/datatables/datatables.min.js"></script>
<script src=".././vendor/datatables/cdn.datatables.net/buttons/1.2.2/js/dataTables.buttons.min.js"></script>
<script src=".././vendor/datatables/cdn.datatables.net/buttons/1.2.2/js/buttons.flash.min.js"></script>
<script src=".././vendor/datatables/cdnjs.cloudflare.com/ajax/libs/jszip/2.5.0/jszip.min.js"></script>
<script src=".././vendor/datatables/cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/pdfmake.min.js"></script>
<script src=".././vendor/datatables/cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/vfs_fonts.js"></script>
<script src=".././vendor/datatables/cdn.datatables.net/buttons/1.2.2/js/buttons.html5.min.js"></script>
<script src=".././vendor/datatables/cdn.datatables.net/buttons/1.2.2/js/buttons.print.min.js"></script>
<script src=".././vendor/datatables/datatables-init.js"></script>
<!-- Bootstrap JS-->
<script src=".././vendor/bootstrap-4.1/popper.min.js"></script>
<script src=".././vendor/bootstrap-4.1/bootstrap.min.js"></script>
<!-- Main JS -->
<script src=".././js/main.js" ></script>
<script type="text/javascript">
    $(document).ready(function() {
        var total = 0;
        $('#valorservico').keyup(function () {
            total = parseFloat($(this).val().replace(',','.') * $("#quantidadepax").val());
            total += parseFloat($(this).val().replace(',','.') / 2 * $("#quantidadechild").val());
            document.getElementById('calculadora').style.display = 'block';
            $('#calculadora').html("Valor total da reserva R$ " + total);
        });
        $('#valorservico1').keyup(function () {
            total += parseFloat($(this).val().replace(',','.') * $("#quantidadepax1").val());
            total += parseFloat($(this).val().replace(',','.') / 2 * $("#quantidadechild1").val());
            document.getElementById('calculadora').style.display = 'block';
            $('#calculadora').text("Valor total da reserva R$ " + total);
        });
        $('#exemplomodal').modal('show');
        $('.modal').modal('show');
        $("#cadastrarvolta").click(function () {
            if( $("#cadastrarvolta").is(':checked') ){
                $("#adicionais").show();
            } else {
                $("#adicionais").hide();
            }

        });
        $("#pax").on("input", function(){

            //$(this).val($(this).val().toUpperCase());
        });
        $("#documento").on("input", function(){

            //$(this).val($(this).val().toUpperCase());
        });
    });
    $(document).ready(function(){
        <?php if($_SESSION['id'] == 46 or $_SESSION['id'] == 1 or $_SESSION['id'] == 273 ){ ?>
            $.ajax({
                type: 'POST',
                url: '.././atualizarValores.php',
                data: {facilpay_criartransacao: 1 },
                dataType: 'json',
                success: function(response){
                    console.log("atualizando.... ");
                },
                error: function(XMLHttpRequest, textStatus, errorThrown){
                    console.log("erro.... ");
                    console.log(XMLHttpRequest + textStatus + errorThrown);
                }
            });
        <?php }?>

        $('form#reserva').on('submit', function () {
            var agendamento = $("#datainicio").val().split('T');
            agendamento = new Date(agendamento[0]).setHours(48);
            var agendamento2 = $("#datainiciovolta").val().split('T');
            agendamento2 = new Date(agendamento2[0]).setHours(48);
            var hoje = new Date();
            if (agendamento < hoje) {
                alert('Data de check-in é inválida!');
                console.log(hoje);
                console.log(agendamento);
                return false;
            }
            else if(agendamento2 < hoje){
                alert('Data de check-out é inválida!');
                console.log(hoje);
                console.log(agendamento2);
                return false;
            }
            else{
                $("#novareservacadastro").text('Gerando voucher, aguarde..');
                $("#vincularservicos").text('Vinculando serviço, aguarde..');
            }
        });
        $(document).keydown(function(event) {
            if (event.ctrlKey == true && (event.which == '61' || event.which == '107' || event.which == '173' || event.which == '109'  ||
                event.which == '187'  || event.which == '189'  ) ) {
                event.preventDefault();
            }
        });

        $(window).bind('mousewheel DOMMouseScroll', function (event) {
            if (event.ctrlKey == true) {
                //alert('O Zomm está ');
                event.preventDefault();
            }
        });
        $("#salvar").click(function (){
            // desabilita o campo
            var cliente = document.getElementById("cliente").value;
            var servico = document.getElementById("servico").value;
            var pagamento = document.getElementById("pagamento").value;
            var horario = document.getElementById("horario").value;
            if( cliente == 0 ){
                alert("selecione o cliente");
                $("#cliente").select();
            }else if(servico == 0){
                alert("selecione o servico");
            }else if(pagamento == 0){
                alert("selecione a forma de pagamento");
            }else if(horario == 0){
                alert("selecione o horário de embarque");
            }else{
                $("#salvar").text("Salvando as informações. Aguarde !");
                //$("#salvar").prop("disabled", true);

            }
        });
        $('#pesquisarfatura').click(function () {
           $('#pesquisarfatura').text('Buscando informações. Aguarde ! ');
        });
        $('#todosVoucher').click(function () {
            $('#todosVoucher').text('Cadastrando informações. Aguarde ! ');
        });
        $('#salvar').click(function () {
            $('#salvar').text('Cadastrando informações. Aguarde ! ');
        });
        $('#salvarfatura').click(function () {
            $('#salvarfatura').text('Cadastrando informações. Aguarde ! ');
            window.setTimeout(function() {
                $("#salvarfatura").prop("disabled", true);
            }, 1000);
        });
        $('#atualizarfatura').click(function () {
            $('#atualizarfatura').text('Cadastrando informações. Aguarde ! ');
            window.setTimeout(function() {
                $("#atualizarfatura").prop("disabled", true);
            }, 1000);
        });

    });
</script>

<!-- Main JS-->
<script src=".././js/main.js"></script>

</body>

</html>
<!-- end document-->
<?php ob_end_flush(); ?>