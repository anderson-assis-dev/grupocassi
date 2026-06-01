<?php
require_once dirname(__DIR__) . '/config.php';
set_time_limit(0);
function runAlter(PDO $pdo, string $sql, string $label): void {
    try {
        $pdo->exec($sql);
        echo '<p style="color:green;font-family:monospace">✔ ' . htmlspecialchars($label) . '</p>';
    } catch (PDOException $e) {
        if (str_contains($e->getMessage(), 'Duplicate column name')) {
            echo '<p style="color:orange;font-family:monospace">⚠ ' . htmlspecialchars($label) . ' — coluna já existe.</p>';
        } else {
            echo '<p style="color:red;font-family:monospace">✘ Erro em "' . htmlspecialchars($label) . '": ' . htmlspecialchars($e->getMessage()) . '</p>';
        }
    }
}
function migracaoBackfillTipo(PDO $pdo, string $tipo, int $limite): int
{
    if ($tipo === 'comissao') {
        $where = "dataagente > '0000-00-00' AND valueagente > 0 AND idcaixa IS NULL";
        $prefixo = 'Pagamento de comissao para o voucher: ';
        $plano = 30;
        $tipoCaixa = 2;
        $campoValor = 'valueagente';
    } else {
        $where = "valuecredit > 0 AND idcaixa IS NULL";
        $prefixo = 'CREDITO DO VOUCHER ';
        $plano = 10;
        $tipoCaixa = null;
        $campoValor = 'valuecredit';
    }
    if ($tipoCaixa !== null) {
        $find = $pdo->prepare(
            "SELECT id FROM ct_caixa WHERE descricao = :d AND idplano = :plano AND idtipo = :tipo
            AND valor = :v ORDER BY id DESC LIMIT 1"
        );
    } else {
        $find = $pdo->prepare(
            "SELECT id FROM ct_caixa WHERE descricao = :d AND idplano = :plano
            AND valor = :v ORDER BY id DESC LIMIT 1"
        );
    }
    $upd = $pdo->prepare('UPDATE ct_createfaturacredit SET idcaixa = :cx WHERE id = :id AND idcaixa IS NULL');
    $ultimoId = 0;
    $vinculados = 0;
    while (true) {
        $sql = "SELECT id, numbervoucher, {$campoValor} AS valor FROM ct_createfaturacredit
            WHERE {$where} AND id > {$ultimoId} ORDER BY id ASC LIMIT {$limite}";
        $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        if (!$rows) {
            break;
        }
        foreach ($rows as $row) {
            $ultimoId = (int)$row['id'];
            $desc = $prefixo . $row['numbervoucher'];
            $params = [':d' => $desc, ':plano' => $plano, ':v' => $row['valor']];
            if ($tipoCaixa !== null) {
                $params[':tipo'] = $tipoCaixa;
            }
            $find->execute($params);
            $cxId = (int)$find->fetchColumn();
            if ($cxId <= 0) {
                continue;
            }
            $upd->execute([':cx' => $cxId, ':id' => $row['id']]);
            $vinculados += $upd->rowCount();
        }
        if (count($rows) < $limite) {
            break;
        }
    }
    return $vinculados;
}
function migracaoContarPendentes(PDO $pdo, string $tipo): int
{
    if ($tipo === 'comissao') {
        $sql = "SELECT COUNT(*) FROM ct_createfaturacredit
            WHERE dataagente > '0000-00-00' AND valueagente > 0 AND idcaixa IS NULL";
    } else {
        $sql = "SELECT COUNT(*) FROM ct_createfaturacredit WHERE valuecredit > 0 AND idcaixa IS NULL";
    }
    return (int)$pdo->query($sql)->fetchColumn();
}
runAlter($pdo, "ALTER TABLE ct_createfaturacredit ADD COLUMN idcaixa INT UNSIGNED DEFAULT NULL",
    'ct_createfaturacredit.idcaixa adicionada');
$lote = max(50, min(500, (int)($_GET['lote'] ?? 200)));
$totalComissao = 0;
$totalCredito = 0;
if (empty($_GET['skip'])) {
    $totalComissao = migracaoBackfillTipo($pdo, 'comissao', $lote);
    $totalCredito = migracaoBackfillTipo($pdo, 'credito', $lote);
}
$pendComissao = migracaoContarPendentes($pdo, 'comissao');
$pendCredito = migracaoContarPendentes($pdo, 'credito');
echo '<p style="font-family:monospace">Comissões vinculadas: ' . $totalComissao . '</p>';
echo '<p style="font-family:monospace">Créditos vinculados: ' . $totalCredito . '</p>';
echo '<p style="font-family:monospace">Pendentes comissão (sem match no caixa): ' . $pendComissao . '</p>';
echo '<p style="font-family:monospace">Pendentes crédito (sem match no caixa): ' . $pendCredito . '</p>';
if ($pendComissao > 0 || $pendCredito > 0) {
    echo '<p style="font-family:monospace;color:#856404">Pendentes serão vinculados na primeira edição do registro.</p>';
}
echo '<p style="font-family:monospace"><a href="editar-pax">← Voltar</a></p>';
echo '<p style="color:#888;font-size:12px;font-family:monospace">Apague este arquivo após executar. Use ?skip=1 para só criar a coluna.</p>';
