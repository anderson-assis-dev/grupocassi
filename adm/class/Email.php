<?php
use PHPMailer\PHPMailer\PHPMailer;
require_once('Exception.php');
require_once('SMTP.php');
require_once('POP3.php');
require_once('PHPMailer.php');
class Email
{
    private $nome;
    private $email;
    private $assunto;
    private $mensagem;

    /**
     * @return mixed
     */
    public function getNome()
    {
        return $this->nome;
    }

    /**
     * @param mixed $nome
     */
    public function setNome($nome)
    {
        $this->nome = $nome;
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
    public function getMensagem()
    {
        return $this->mensagem;
    }

    /**
     * @param mixed $mensagem
     */
    public function setMensagem($mensagem)
    {
        $this->mensagem = $mensagem;
    }

    /**
     * @return mixed
     */
    public function getAssunto()
    {
        return $this->assunto;
    }

    /**
     * @param mixed $assunto
     */
    public function setAssunto($assunto)
    {
        $this->assunto = $assunto;
    }


    public function enviarEmail()
    {
        $mail = new PHPMailer();
        $mail->setLanguage('br', 'mail/language/');
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        $mail->IsSMTP();
        $mail->Host = "mail.cassiturismo.com.br";
        $mail->SMTPAuth = true; // Autenticação
        $mail->SMTPSecure = "ssl";
        $mail->Port = "465";
        $mail->Username = 'cassi@cassiturismo.com.br';
        $mail->Password = 'cassi213681';

        $mail->From = "naoresponda@cassiturismo.com.br";
        $mail->FromName = utf8_decode("Cassi Turismo");

        $mail->AddAddress($this->getEmail(), $this->getNome());
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = utf8_decode($this->getAssunto()); // Assunto da mensagems
        $mail->Body = $this->getMensagem();
        //$mail->msgHTML(file_get_contents('conteudo.html'), __DIR__);
        //$mail->AddAttachment("./Fatura.pdf", "Fatura.pdf");

        $enviado = $mail->Send();

        $mail->ClearAllRecipients();
        $mail->ClearAttachments();
    }

}