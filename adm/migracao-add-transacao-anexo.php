<?php
require_once dirname(__DIR__) . '/config.php';
try {
    $pdo->exec("ALTER TABLE ct_caixa ADD COLUMN anexo VARCHAR(255) DEFAULT NULL AFTER valor");
    echo '<p style="color:green;font-family:monospace">✔ Coluna "anexo" adicionada com sucesso à tabela ct_caixa.</p>';
} catch (PDOException $e) {
    if (str_contains($e->getMessage(), 'Duplicate column name')) {
        echo '<p style="color:orange;font-family:monospace">⚠ A coluna "anexo" já existe na tabela ct_caixa.</p>';
    } else {
        echo '<p style="color:red;font-family:monospace">✘ Erro: ' . htmlspecialchars($e->getMessage()) . '</p>';
    }
}
echo '<p style="font-family:monospace"><a href="caixa">← Voltar ao Caixa</a></p>';
echo '<p style="color:#888;font-size:12px;font-family:monospace">Apague este arquivo após executar a migração.</p>';
