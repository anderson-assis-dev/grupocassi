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
runAlter($pdo, "ALTER TABLE ct_cliente ADD COLUMN nomebanco VARCHAR(120) NOT NULL DEFAULT '' AFTER limite",
    'ct_cliente.nomebanco adicionada');
runAlter($pdo, "ALTER TABLE ct_cliente ADD COLUMN agencia VARCHAR(20) NOT NULL DEFAULT '' AFTER nomebanco",
    'ct_cliente.agencia adicionada');
runAlter($pdo, "ALTER TABLE ct_cliente ADD COLUMN numeroconta VARCHAR(30) NOT NULL DEFAULT '' AFTER agencia",
    'ct_cliente.numeroconta adicionada');
runAlter($pdo, "ALTER TABLE ct_cliente ADD COLUMN tipoconta VARCHAR(20) NOT NULL DEFAULT '' AFTER numeroconta",
    'ct_cliente.tipoconta adicionada');
runAlter($pdo, "ALTER TABLE ct_cliente ADD COLUMN chavepix VARCHAR(120) NOT NULL DEFAULT '' AFTER tipoconta",
    'ct_cliente.chavepix adicionada');
echo '<p style="font-family:monospace"><a href="pesquisa-cliente-fornecedor">← Voltar a Clientes / Fornecedores</a></p>';
echo '<p style="color:#888;font-size:12px;font-family:monospace">Apague este arquivo após executar a migração.</p>';
