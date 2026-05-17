<?php
require_once('header.php');
?>
    <!-- WELCOME -->
    <section class="welcome p-t-10">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h1 class="title-4">Olá
                        <span><?php echo(strtoupper($_SESSION['nome'])); ?></span>
                    </h1>
                    <p>Boas-vindas à Cassi Turismo.</p>
                </div>
            </div>
        </div>
    </section>
    <!-- END WELCOME -->

    <!-- MARCA -->
    <br>
    <br>
    <?php
    $caminhoImagem = "../images/cassivetorizadaazul.png";
    $descricaoImagem = "Descrição da imagem";
    $largura = "340px"; // Defina a largura desejada da imagem
    $altura = "300px"; // Defina a altura desejada da imagem
    echo '<img src="' . $caminhoImagem . '" alt="' . $descricaoImagem . '" style="display: block; margin: 0 auto; width: ' . $largura . '; height: ' . $altura . ';">';
    ?>
</div>
<?php require_once('footer.php'); ?>
