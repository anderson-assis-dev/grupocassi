<?php
require_once '../../config.php';
require_once __DIR__ . '/../includes/recibo_helpers.php';
$pdo->exec("set names utf8");
$input = array_merge($_GET, $_POST);
$registro = reciboCarregar($pdo, $input);
header('Content-Type: text/html; charset=utf-8');
if (!$registro) {
    echo '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="utf-8"><title>Recibo</title></head><body style="font-family:sans-serif;padding:40px;text-align:center;color:#64748b"><h2>Transação não encontrada</h2><p>Informe o ID da transação para gerar o recibo.</p></body></html>';
    exit;
}
echo reciboPaginaPrint($registro, reciboTipo($registro));
