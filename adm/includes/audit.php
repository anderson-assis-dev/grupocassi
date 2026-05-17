<?php
/**
 * Centraliza o registro de auditoria na tabela ct_audit.
 * Uso: logAudit($pdo, $voucher, 'Descrição da ação');
 */
function logAudit(PDO $pdo, string $voucher, string $descricao): void
{
    $st = $pdo->prepare(
        'INSERT INTO `ct_audit` (`id`, `idresponsible`, `voucher`, `description`, `date`)
         VALUES (DEFAULT, :idres, :vou, :descr, :dat)'
    );
    $st->execute([
        ':idres' => $_SESSION['idresponsavel'] ?? null,
        ':vou'   => $voucher,
        ':descr' => $descricao,
        ':dat'   => date('Y-m-d H:i:s'),
    ]);
}
