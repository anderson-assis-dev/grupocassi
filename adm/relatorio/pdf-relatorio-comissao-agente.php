<?php
require_once '../../config.php';
require_once __DIR__ . '/../includes/comissao_helpers.php';
$pdo->exec('set names utf8');
header('Content-Type: text/html; charset=utf-8');
if (!empty($_GET['recibo'])) {
    comissaoExibirRecibo(
        $pdo,
        (int)($_GET['id'] ?? 0),
        (string)($_GET['voucher'] ?? '')
    );
}
if (!isset($_POST['comissaoagente'])) {
    http_response_code(400);
    echo comissaoReciboMensagem('Requisição inválida', 'Confirme o pagamento da comissão para gerar o recibo.');
    exit;
}
$nomeAgente = trim((string)($_POST['nomeagente'] ?? ''));
$voucher = (string)($_POST['voucher'] ?? '');
$valorUnitario = (float)str_replace(',', '.', str_replace('.', '', (string)($_POST['valoragente'] ?? '0')));
$nomeServicoPago = trim((string)($_POST['comissaoservico'] ?? ''));
$resultado = comissaoProcessarPagamento($pdo, $nomeAgente, $voucher, $valorUnitario, $nomeServicoPago);
if ($resultado['status'] === 'ok' && !empty($resultado['idCaixa'])) {
    echo comissaoReciboHtml($pdo, (int)$resultado['idCaixa']);
    exit;
}
if ($resultado['status'] === 'duplicado') {
    echo comissaoReciboMensagem('Pagamento já realizado', 'A comissão informada já foi registrada para este voucher.');
    exit;
}
echo comissaoReciboMensagem('Não foi possível gerar o recibo', 'Verifique os dados e tente novamente.');
