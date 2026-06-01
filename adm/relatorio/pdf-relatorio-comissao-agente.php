<?php
require_once '../.././config.php';
require_once __DIR__ . '/../includes/comissao_helpers.php';
header('Content-Type: text/html; charset=utf-8');
if (!isset($_POST['comissaoagente'])) {
    http_response_code(400);
    echo 'Requisição inválida.';
    exit;
}
$nomeAgente = trim((string)($_POST['nomeagente'] ?? ''));
$voucher = (string)($_POST['voucher'] ?? '');
$valorUnitario = (float)str_replace(',', '.', str_replace('.', '', (string)($_POST['valoragente'] ?? '0')));
$nomeServicoPago = trim((string)($_POST['comissaoservico'] ?? ''));
$resultado = comissaoProcessarPagamento($pdo, $nomeAgente, $voucher, $valorUnitario, $nomeServicoPago);
$dadosReserva = $resultado['dadosReserva'] ?? null;
if ($resultado['status'] === 'ok' && $dadosReserva) {
    echo comissaoHtmlRecibo($dadosReserva, $nomeAgente, $nomeServicoPago, $valorUnitario);
    exit;
}
if ($resultado['status'] === 'duplicado' && $dadosReserva) {
    echo comissaoHtmlJaPago($dadosReserva, $resultado['dadosPagamento'] ?? null);
    exit;
}
if (!$dadosReserva) {
    echo '<html><head><meta charset="utf-8"></head><body><p>Voucher não encontrado.</p></body></html>';
    exit;
}
echo comissaoHtmlJaPago($dadosReserva, $resultado['dadosPagamento'] ?? null);
