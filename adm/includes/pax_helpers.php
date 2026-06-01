<?php
/**
 * Recalcula e atualiza idstatusinvoice com base no total pago vs total do serviço.
 * status 4 = parcial, 1 = não pago/zerado
 */
function setInvoiceStatus(PDO $pdo, string $voucher): void
{
    $res = $pdo->prepare('SELECT id, valueservice, qtdpax, qtdchild FROM ct_reserva WHERE numbervoucher = :v');
    $res->execute([':v' => $voucher]);
    $r = $res->fetch(PDO::FETCH_ASSOC);

    $totalServico = ($r['valueservice'] * $r['qtdpax']) + (($r['valueservice'] / 2) * $r['qtdchild']);

    $adds = $pdo->prepare('SELECT valueservice, qpax, qchild FROM ct_recentlyadd WHERE idrecently = :id');
    $adds->execute([':id' => $r['id']]);
    foreach ($adds->fetchAll(PDO::FETCH_OBJ) as $a) {
        $totalServico += ($a->valueservice * $a->qpax) + (($a->valueservice / 2) * $a->qchild);
    }

    $pagRow = $pdo->prepare('SELECT sum(valuecredit) as total FROM ct_createfaturacredit WHERE numbervoucher = :v');
    $pagRow->execute([':v' => $voucher]);
    $totalPago = (float) ($pagRow->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

    $sinvoice = ($totalPago > 0 && $totalPago < $totalServico) ? 4 : 1;
    $upd = $pdo->prepare('UPDATE ct_reserva SET idstatusinvoice = :s WHERE numbervoucher = :v');
    $upd->execute([':s' => $sinvoice, ':v' => $voucher]);
}

/**
 * Sincroniza os campos denormalizados totalservico e totalcredito em ct_reserva.
 */
function syncReservaTotais(PDO $pdo, string $voucher): void
{
    $res = $pdo->prepare('SELECT id, valueservice, qtdpax, qtdchild FROM ct_reserva WHERE numbervoucher = :v');
    $res->execute([':v' => $voucher]);
    $r = $res->fetch(PDO::FETCH_ASSOC);

    $totalServico = ($r['valueservice'] * $r['qtdpax']) + (($r['valueservice'] / 2) * $r['qtdchild']);

    $addTot = $pdo->prepare(
        'SELECT COALESCE(sum(valueservice * qpax + (valueservice / 2) * qchild), 0) as tot FROM ct_recentlyadd WHERE idrecently = :id'
    );
    $addTot->execute([':id' => $r['id']]);
    $totalServico += (float) $addTot->fetch(PDO::FETCH_ASSOC)['tot'];

    $cred = $pdo->prepare('SELECT COALESCE(sum(valuecredit), 0) as totalpago FROM ct_createfaturacredit WHERE numbervoucher = :v');
    $cred->execute([':v' => $voucher]);
    $totalPago = (float) $cred->fetch(PDO::FETCH_ASSOC)['totalpago'];

    $upd = $pdo->prepare('UPDATE ct_reserva SET totalservico = :ts, totalcredito = :tc WHERE numbervoucher = :v');
    $upd->execute([':ts' => $totalServico, ':tc' => $totalPago, ':v' => $voucher]);
}

/**
 * Marca a reserva como alterada agora (data_alteracao = now()).
 */
function marcarReservaAlterada(PDO $pdo, string $voucher): void
{
    $pdo->prepare('UPDATE ct_reserva SET data_alteracao = now() WHERE numbervoucher = :v')
        ->execute([':v' => $voucher]);
}

/**
 * Atualiza idstatusinvoice com um valor fixo (sem recalcular).
 * Use setInvoiceStatus() quando precisar recalcular com base no total pago.
 */
function setInvoiceStatusFixo(PDO $pdo, string $voucher, int $sinvoice): void
{
    $pdo->prepare('UPDATE ct_reserva SET idstatusinvoice = :s WHERE numbervoucher = :v')
        ->execute([':s' => $sinvoice, ':v' => $voucher]);
}

/**
 * Retorna o fullname de um serviço; vazio se não encontrar.
 */
function buscarNomeServico(PDO $pdo, int $id): string
{
    $stmt = $pdo->prepare('SELECT fullname FROM ct_servico WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row['fullname'] ?? '';
}

/**
 * Retorna o fullname de um status; vazio se não encontrar.
 */
function buscarNomeStatus(PDO $pdo, int $id): string
{
    $stmt = $pdo->prepare('SELECT fullname FROM ct_status WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row['fullname'] ?? '';
}

/**
 * Incrementa o campo credito da ct_fatura vinculada ao voucher.
 * Não faz nada se a reserva não estiver associada a uma fatura (numberfatura = 0).
 */
function creditarFatura(PDO $pdo, string $voucher, float $valor): void
{
    $stmt = $pdo->prepare('SELECT numberfatura FROM ct_reserva WHERE numbervoucher = :v');
    $stmt->execute([':v' => $voucher]);
    $numberfatura = (int) ($stmt->fetch(PDO::FETCH_ASSOC)['numberfatura'] ?? 0);
    if ($numberfatura <= 0) {
        return;
    }
    $upd = $pdo->prepare('UPDATE ct_fatura SET credito = credito + :valor WHERE id = :id');
    $upd->execute([':valor' => $valor, ':id' => $numberfatura]);
}

function paxUploadAnexo(string $inputName = 'anexo'): ?string
{
    $file = $_FILES[$inputName] ?? null;
    $ext  = $file ? strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) : '';
    $valido = $file
        && $file['error'] === UPLOAD_ERR_OK
        && in_array($ext, ['pdf', 'jpg', 'jpeg', 'png'], true)
        && $file['size'] <= 10 * 1024 * 1024;
    if (!$valido) {
        return null;
    }
    $uploadDir = __DIR__ . '/../uploads/transacoes/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    $filename = 'pax_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
    return move_uploaded_file($file['tmp_name'], $uploadDir . $filename) ? $filename : null;
}
