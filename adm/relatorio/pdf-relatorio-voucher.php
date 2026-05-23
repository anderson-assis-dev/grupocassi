<?php
require_once('../.././config.php');
require_once __DIR__ . '/../includes/voucher_document.php';
$folharosto = !empty($_POST['folharosto']);
if (!isset($_POST['voucher'])) {
    exit;
}
$ctx = carregarDadosVoucher($pdo, $_POST['voucher']);
if (empty($ctx['dadosGerais'])) {
    exit;
}
registrarAuditoriaVoucher($pdo, $_POST['voucher'], $folharosto);
$ctx['folharosto'] = $folharosto;
$ctx['titulo'] = $folharosto ? 'Folha de Rosto' : 'Voucher';
$ctx['forPdf'] = false;
$ctx['autoPrint'] = true;
echo renderVoucherHtml($ctx);
