<?php
require_once __DIR__ . '/../voucher_document.php';
function renderEmailVoucher(string $voucher, string $tipo, string $passageiro = '', bool $folharosto = false): string
{
    $voucher = htmlspecialchars($voucher, ENT_QUOTES, 'UTF-8');
    $passageiro = htmlspecialchars($passageiro, ENT_QUOTES, 'UTF-8');
    $tituloDoc = $folharosto ? 'Folha de Rosto' : 'Voucher';
    $saudacao = $passageiro !== '' ? "Olá, <strong>{$passageiro}</strong>." : 'Olá.';
    $logoSrc = logoCassiDataUri();
    ob_start();
    ?>
<body style="margin:0;padding:0;background:#f4f6fb;font-family:Arial,Helvetica,sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6fb;padding:32px 16px;">
    <tr>
        <td align="center">
            <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 8px 24px rgba(30,71,112,0.12);">
                <tr>
                    <td style="background:#1E4770;padding:28px 32px;text-align:center;">
                        <?php if ($logoSrc !== '') { ?>
                        <img src="<?= $logoSrc ?>" alt="Cassi Turismo" width="200" height="24" style="width:200px;height:auto;max-width:200px;margin-bottom:12px;display:block;margin-left:auto;margin-right:auto;">
                        <?php } ?>
                        <h1 style="margin:0;color:#ffffff;font-size:24px;font-weight:600;"><?= $tituloDoc ?></h1>
                        <p style="margin:8px 0 0;color:#d7e6f5;font-size:14px;">Nº <?= $voucher ?></p>
                    </td>
                </tr>
                <tr>
                    <td style="padding:32px;color:#334155;font-size:15px;line-height:1.7;">
                        <p style="margin:0 0 16px;"><?= $saudacao ?></p>
                        <p style="margin:0 0 16px;">
                            Segue em anexo o <?= strtolower($tituloDoc) ?> da sua reserva com a <strong>Cassi Turismo</strong>.
                            Guarde o arquivo PDF para apresentação no embarque e conferência dos serviços contratados.
                        </p>
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;margin:0 0 20px;">
                            <tr>
                                <td style="padding:16px 18px;">
                                    <p style="margin:0 0 6px;font-size:12px;color:#64748b;text-transform:uppercase;letter-spacing:0.4px;">Documento anexo</p>
                                    <p style="margin:0;font-size:16px;color:#1E4770;font-weight:700;"><?= $tituloDoc ?> - <?= $voucher ?>.pdf</p>
                                </td>
                            </tr>
                        </table>
                        <p style="margin:0 0 16px;">
                            Há mais de 15 anos desenvolvemos o trade turístico na Bahia, com transfer semi-terrestre para Morro de São Paulo,
                            frota marítima e terrestre moderna e atendimento com alto padrão de qualidade.
                        </p>
                        <p style="margin:0;">
                            Dúvidas? Responda este e-mail ou fale conosco:<br>
                            <strong>(71) 99121-1111</strong> | <strong>(71) 98444-4444</strong><br>
                            cassiturismo.com.br
                        </p>
                    </td>
                </tr>
                <tr>
                    <td style="background:#eef2ff;padding:20px 32px;text-align:center;color:#1E4770;font-size:13px;">
                        Cassi Turismo · Transfer e receptivo na Bahia
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
    <?php
    return (string) ob_get_clean();
}
function renderEmailVoucherAlt(string $voucher, string $passageiro, bool $folharosto): string
{
    $tituloDoc = $folharosto ? 'Folha de Rosto' : 'Voucher';
    $linhas = [
        'Cassi Turismo',
        $tituloDoc . ' nº ' . $voucher,
    ];
    if ($passageiro !== '') {
        $linhas[] = 'Passageiro: ' . $passageiro;
    }
    $linhas[] = 'O documento em PDF está em anexo neste e-mail.';
    $linhas[] = 'Contato: (71) 99121-1111 | (71) 98444-4444 | cassiturismo.com.br';
    return implode("\n", $linhas);
}
