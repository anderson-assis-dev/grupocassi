<?php
require_once '../../config.php';
require_once __DIR__ . '/../includes/recibo_helpers.php';
$pdo->exec("set names utf8");
$registro = reciboCarregar($pdo, $_POST);
if (!$registro) {
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Recibo</title></head><body style="font-family:sans-serif;padding:40px;text-align:center;color:#64748b"><h2>Transação não encontrada</h2><p>Não foi possível gerar o recibo.</p></body></html>';
    exit;
}
$tipo = reciboTipo($registro);
$html = reciboHtml($registro, $tipo);
$arquivo = 'recibo-' . (int)$registro['id'] . '.pdf';
define('_MPDF_TTFONTDATAPATH', sys_get_temp_dir());
require_once 'pdf/mpdf.php';
$mpdf = new mPDF('utf-8', 'A4', 0, '', 15, 15, 16, 16);
$mpdf->SetTitle('Recibo #' . (int)$registro['id']);
$mpdf->SetAuthor('Cassi Turismo');
$mpdf->WriteHTML($html);
$mpdf->Output($arquivo, 'I');
