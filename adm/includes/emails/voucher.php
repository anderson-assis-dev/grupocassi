<?php
/**
 * Template do e-mail "Meu Voucher".
 * Variaveis esperadas: $voucher, $tipo.
 *
 * Uso:
 *   $body = renderEmailVoucher($voucher, $tipo);
 */

function renderEmailVoucher(string $voucher, string $tipo): string
{
    $voucher = htmlspecialchars($voucher, ENT_QUOTES, 'UTF-8');
    $tipo    = htmlspecialchars($tipo, ENT_QUOTES, 'UTF-8');
    $link    = "http://grupocassi.com.br/vouchercliente.php?voucher={$voucher}&tipo={$tipo}";

    ob_start();
    ?>
<body leftmargin="0" marginwidth="0" topmargin="0" marginheight="0" offset="0">
<div id="wrapper" dir="ltr" style="margin: 0; padding: 70px 0 70px 0; -webkit-text-size-adjust: none !important; width: 100%;">
    <table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%">
        <tr>
            <td align="center" valign="top">
                <div id="template_header_image">
                    <p style="margin-top: 0; background-color: #4b3bfc;">
                        <img src="http://cassiturismo.com.br/wp-content/themes/travel-stories/images/cassi.png"
                             alt="Cassi Turismo"
                             style="border: none; display: inline-block; font-size: 14px; font-weight: bold; height: auto; outline: none; text-decoration: none; text-transform: capitalize; vertical-align: middle; margin-right: 10px;">
                    </p>
                </div>
                <table border="0" cellpadding="0" cellspacing="0" width="600" id="template_container"
                       style="box-shadow: 0 1px 4px rgba(0,0,0,0.1) !important; background-color: #ffffff; border: 1px solid #4335e3; border-radius: 3px !important;">
                    <tr>
                        <td align="center" valign="top">
                            <table border="0" cellpadding="0" cellspacing="0" width="600" id="template_header"
                                   style="background-color: #3f0ed1; border-radius: 3px 3px 0 0 !important; color: #ffffff; border-bottom: 0; font-weight: bold; line-height: 100%; vertical-align: middle;">
                                <tr>
                                    <td id="header_wrapper" style="padding: 36px 48px; display: block;">
                                        <h1 style="color: #ffffff; font-size: 30px; font-weight: 300; line-height: 150%; margin: 0; text-align: left; text-shadow: 0 1px 0 #653eda;">Meu Voucher</h1>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" valign="top">
                            <table border="0" cellpadding="0" cellspacing="0" width="600" id="template_body">
                                <tr>
                                    <td valign="top" id="body_content" style="background-color: #ffffff;">
                                        <table border="0" cellpadding="20" cellspacing="0" width="100%">
                                            <tr>
                                                <td valign="top" style="padding: 48px 48px 0;">
                                                    <div id="body_content_inner" style="color: #636363; font-size: 14px; line-height: 150%; text-align: left;">
                                                        <p style="text-align: justify;">
                                                            Há 15 anos no mercado a nossa empresa vem desenvolvendo o trade turístico no estado da Bahia
                                                            e temos como nosso maior mérito a criação do transfer semi-terrestre para Morro de São Paulo.
                                                            Equipados com uma frota marítima e terrestre de última geração desempenhamos nossos serviços
                                                            com altíssimo padrão de qualidade sempre presando pelo conforto e segurança dos passageiros.
                                                            Nossas agências são estrategicamente posicionadas para proporcionar o melhor atendimento
                                                            possível, oferecendo uma estrutura com alto padrão de qualidade onde o turista pode encontrar,
                                                            caixa eletrônico, lanchonete, ar-condicionado, Wifi dentre outros ítens de conforto que só a
                                                            Cassi Turismo oferece.
                                                        </p>
                                                        <h2 style="color: #3f0ed1; display: block; font-size: 18px; font-weight: bold; line-height: 130%; margin: 0 0 18px; text-align: left;">
                                                            <a href="<?= $link ?>">Visualizar Voucher</a>
                                                        </h2>
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" valign="top">
                            <table border="0" cellpadding="10" cellspacing="0" width="600" id="template_footer">
                                <tr>
                                    <td valign="top" style="padding: 0; -webkit-border-radius: 6px;">
                                        <table border="0" cellpadding="10" cellspacing="0" width="100%">
                                            <tr>
                                                <td colspan="2" valign="middle" id="credit"
                                                    style="padding: 0 48px 48px 48px; -webkit-border-radius: 6px; border: 0; color: #8c6ee3; font-family: Arial; font-size: 12px; line-height: 125%; text-align: center;">
                                                    <h1>Cassi Turismo 16 Anos</h1>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>
</body>
    <?php
    return (string) ob_get_clean();
}
