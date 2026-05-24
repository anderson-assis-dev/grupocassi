<?php
require_once dirname(__DIR__, 2) . '/config.php';
require_once dirname(__DIR__)    . '/includes/flash.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../relatorio-contas');
    exit;
}

$datainicio = trim($_POST['vencimentoinicial'] ?? '');
$datafim    = trim($_POST['vencimentofinal']   ?? '');
$empresa    = (int)($_POST['empresa']          ?? 0);
$idcliente  = (int)($_POST['cliente']          ?? 0);
$idconta    = (int)($_POST['conta']            ?? 0);
$tipo       = (int)($_POST['tiporelatorio']    ?? 0);

if (!$datainicio || !$datafim) {
    setFlash('danger', 'Informe as datas do relatório.');
    header('Location: ../relatorio-contas');
    exit;
}

$email = trim(env('MAIL_USERNAME') ?? '');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    setFlash('danger', 'E-mail do usuário logado não é válido. Atualize o seu cadastro em Perfil.');
    header('Location: ../relatorio-contas');
    exit;
}

$pdo->exec("set names utf8");

$pdo->exec("CREATE TABLE IF NOT EXISTS ct_report_jobs (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    datainicio    DATE         NOT NULL,
    datafim       DATE         NOT NULL,
    empresa       INT          NOT NULL DEFAULT 0,
    idcliente     INT          NOT NULL DEFAULT 0,
    idconta       INT          NOT NULL DEFAULT 0,
    tiporelatorio TINYINT      NOT NULL DEFAULT 0,
    email_destino VARCHAR(255) NOT NULL,
    status        ENUM('pendente','processando','enviado','erro') NOT NULL DEFAULT 'pendente',
    criado_em     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    processado_em DATETIME     NULL,
    erro_msg      TEXT         NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$st = $pdo->prepare(
    "INSERT INTO ct_report_jobs
        (datainicio, datafim, empresa, idcliente, idconta, tiporelatorio, email_destino)
     VALUES
        (:ini, :fim, :emp, :cli, :cnt, :tipo, :email)"
);
$st->execute([
    ':ini'   => $datainicio,
    ':fim'   => $datafim,
    ':emp'   => $empresa,
    ':cli'   => $idcliente,
    ':cnt'   => $idconta,
    ':tipo'  => $tipo,
    ':email' => $email,
]);

setFlash('success', 'Relatório agendado com sucesso! Você receberá o PDF no e-mail <strong>'
    . htmlspecialchars($email, ENT_QUOTES, 'UTF-8')
    . '</strong> em instantes.');
header('Location: ../relatorio-contas');
exit;
