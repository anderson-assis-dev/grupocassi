<?php
require_once '.././config.php';
require_once __DIR__ . '/includes/ref_cache.php';

if (empty($_SESSION['idgerente'] || $_SESSION['idfaturador'] || $_SESSION['idoperador'] || $_SESSION['comissao']
    || $_SESSION['idreservamanager'] || $_SESSION['idreservaplus'] || $_SESSION['idcaixa'] || $_SESSION['idbaixa']
    || $_SESSION['idpagarreserva'] || $_SESSION['idfinanceiro2'] || $_SESSION['folhaderosto'] || $_SESSION['comissaorelatoriofolha'])) {
    header('location: ../');
    exit;
}

refCacheFlush();

$back = $_SERVER['HTTP_REFERER'] ?? './home';
header('location: ' . $back . (str_contains($back, '?') ? '&' : '?') . 'cache_cleared=1');
exit;
