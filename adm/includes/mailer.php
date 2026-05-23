<?php
/**
 * Helper centralizado para envio de e-mails.
 *
 * Toda a configuracao SMTP fica em variaveis de ambiente (.env):
 *   MAIL_HOST, MAIL_PORT, MAIL_USERNAME, MAIL_PASSWORD, MAIL_ENCRYPTION,
 *   MAIL_FROM_ADDRESS, MAIL_FROM_NAME, MAIL_REPLY_TO, MAIL_CC
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

/**
 * Envia um e-mail via SMTP autenticado.
 *
 * @param string|array $to       Destinatario(s). String para um, array para varios.
 * @param string       $subject  Assunto.
 * @param string       $body     Corpo do e-mail (HTML por padrao).
 * @param array        $opts     Opcoes adicionais:
 *   - 'isHtml'   bool       (default true)
 *   - 'cc'       string|array
 *   - 'bcc'      string|array
 *   - 'replyTo'  string
 *   - 'attachments' array de paths
 * @return bool true em sucesso.
 * @throws PHPMailerException em falha.
 */
function enviarEmail($to, string $subject, string $body, array $opts = []): bool
{
    $mail = new PHPMailer(true);
    $mail->CharSet = 'UTF-8';
    $mail->SMTPDebug = 0;
    $mail->isSMTP();
    $mail->Host       = env('MAIL_HOST');
    $mail->SMTPAuth   = true;
    $mail->Username   = env('MAIL_USERNAME');
    $mail->Password   = env('MAIL_PASSWORD');
    $mail->SMTPSecure = env('MAIL_ENCRYPTION', PHPMailer::ENCRYPTION_STARTTLS);
    $mail->Port       = (int) env('MAIL_PORT', 587);
    $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME', ''));
    foreach ((array) $to as $addr) {
        $mail->addAddress($addr);
    }
    $replyTo = $opts['replyTo'] ?? env('MAIL_REPLY_TO');
    if ($replyTo) {
        $mail->addReplyTo($replyTo);
    }
    $cc = $opts['cc'] ?? env('MAIL_CC');
    if ($cc) {
        foreach ((array) $cc as $addr) {
            $mail->addCC($addr);
        }
    }
    if (!empty($opts['bcc'])) {
        foreach ((array) $opts['bcc'] as $addr) {
            $mail->addBCC($addr);
        }
    }
    if (!empty($opts['attachments'])) {
        foreach ($opts['attachments'] as $path) {
            $mail->addAttachment($path);
        }
    }
    $mail->isHTML($opts['isHtml'] ?? true);
    $mail->Subject = $subject;
    $mail->Body    = $body;
    return $mail->send();
}

/**
 * Envia o e-mail "Meu Voucher" para o cliente.
 *
 * @param string $email   Endereco do cliente.
 * @param string $voucher Codigo do voucher.
 * @param string $tipo    Tipo do voucher.
 * @return bool
 */
function enviarVoucherCliente(string $email, string $voucher, string $tipo): bool
{
    require_once __DIR__ . '/emails/voucher.php';
    return enviarEmail(
        $email,
        'Meu Voucher - Cassi Turismo',
        renderEmailVoucher($voucher, $tipo)
    );
}
