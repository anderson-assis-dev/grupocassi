<?php
require_once dirname(__DIR__) . '/config.php';

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

runAlter($pdo, "ALTER TABLE ct_caixa ADD COLUMN anexo VARCHAR(255) DEFAULT NULL AFTER valor",
    'ct_caixa.anexo adicionada');
runAlter($pdo, "ALTER TABLE ct_createfaturacredit ADD COLUMN anexo VARCHAR(255) DEFAULT NULL",
    'ct_createfaturacredit.anexo adicionada');

echo '<p style="font-family:monospace"><a href="caixa">← Voltar ao Caixa</a></p>';
echo '<p style="color:#888;font-size:12px;font-family:monospace">Apague este arquivo após executar a migração.</p>';
