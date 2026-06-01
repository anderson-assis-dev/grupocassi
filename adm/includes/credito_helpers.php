<?php
require_once __DIR__ . '/ref_cache.php';
require_once __DIR__ . '/audit.php';
require_once __DIR__ . '/pax_helpers.php';
function creditoUsuarioGerente(): bool
{
    return !empty($_SESSION['idgerente'])
        || in_array((int)($_SESSION['id'] ?? 0), [30, 304], true);
}
function creditoParseValor(string $raw): float
{
    return (float)str_replace(',', '.', str_replace('.', '', $raw));
}
function creditoPorId(PDO $pdo, int $id): ?array
{
    $st = $pdo->prepare('SELECT * FROM ct_createfaturacredit WHERE id = :id');
    $st->execute([':id' => $id]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}
function creditoVincularCaixa(PDO $pdo, int $idCredito, int $idCaixa): void
{
    $pdo->prepare('UPDATE ct_createfaturacredit SET idcaixa = :caixa WHERE id = :id')
        ->execute([':caixa' => $idCaixa, ':id' => $idCredito]);
}
function creditoResolverIdCaixa(PDO $pdo, int $idCredito): ?int
{
    $row = creditoPorId($pdo, $idCredito);
    if (!$row || (float)$row['valuecredit'] <= 0) {
        return null;
    }
    $idcaixa = (int)($row['idcaixa'] ?? 0);
    if ($idcaixa > 0) {
        return $idcaixa;
    }
    $st = $pdo->prepare(
        "SELECT id FROM ct_caixa WHERE descricao LIKE :d AND idplano = 10
        AND valor = :v ORDER BY id DESC LIMIT 1"
    );
    $st->execute([
        ':d' => '%' . $row['numbervoucher'] . '%',
        ':v' => $row['valuecredit'],
    ]);
    $id = (int)($st->fetchColumn() ?: 0);
    if ($id > 0) {
        creditoVincularCaixa($pdo, $idCredito, $id);
    }
    return $id ?: null;
}
function creditoBuscarConta(PDO $pdo, int $id): ?array
{
    foreach (refContaCorrente($pdo) as $conta) {
        if ((int)$conta->id === $id) {
            return ['id' => (int)$conta->id, 'name' => $conta->name, 'idempr' => $conta->idempr ?? null];
        }
    }
    return null;
}
function creditoCriarCaixa(PDO $pdo, string $voucher, float $valor, string $data, int $ccfp, int $resp, ?int $idEmpresa): int
{
    $tipo = ($resp == 1) ? 2 : 1;
    $pdo->prepare(
        "INSERT INTO `ct_caixa` (`id`, `datevencimento`, `datepagamento`, `datecompetencia`, `nome`, `descricao`,
        `idcliente`, `idtipo`, `idconta`, `idplano`, `idempresa`, `idstatus`, `valor`, `idusr`, `dataabertura`)
        VALUES (DEFAULT, :vencimento, :pagamento, :competencia, :nome, :descricao, :cliente, :tipo, :conta, :plano,
        :empresa, :statuus, :valor, :idusr, :abertura)"
    )->execute([
        ':vencimento' => $data,
        ':pagamento' => $data,
        ':competencia' => $data,
        ':nome' => 'CREDITO DO VOUCHER ' . $voucher,
        ':descricao' => 'CREDITO DO VOUCHER ' . $voucher,
        ':cliente' => 15,
        ':tipo' => $tipo,
        ':conta' => $ccfp,
        ':plano' => 10,
        ':empresa' => $idEmpresa ?? 1,
        ':statuus' => 1,
        ':valor' => $valor,
        ':idusr' => $resp,
        ':abertura' => date('Y-m-d'),
    ]);
    return (int)$pdo->lastInsertId();
}
function creditoAdicionar(PDO $pdo, array $input, array $dadosGerais): void
{
    $voucher = (string)$input['voucher'];
    $desc = (string)($input['desc'] ?? 'Crédito Pago');
    $data = (string)$input['datacredito'];
    $valor = creditoParseValor((string)$input['valordocredito']);
    $ccfp = (int)$input['ccfp'];
    $resp = (int)$input['responsavel'];
    $conta = creditoBuscarConta($pdo, $ccfp);
    $idEmpresa = $conta['idempr'] ?? ($dadosGerais['idempresa'] ?? 1);
    $idCaixa = creditoCriarCaixa($pdo, $voucher, $valor, $data, $ccfp, $resp, (int)$idEmpresa);
    marcarReservaAlterada($pdo, $voucher);
    $pdo->prepare(
        "INSERT INTO ct_createfaturacredit SET `numbervoucher` = :voucher, tarifa = :valor, `desccredit` = :desc,
        `datacredit` = :data, `valuecredit` = :valor2, `valueguia` = '0.00', `valueagente` = '0.00',
        `idaccountcurrent` = :ccfp, `idplancount` = 1, `idusr` = :resp"
    )->execute([
        ':voucher' => $voucher,
        ':valor' => $valor,
        ':desc' => $desc,
        ':data' => $data,
        ':valor2' => $valor,
        ':ccfp' => $ccfp,
        ':resp' => $resp,
    ]);
    $idCredito = (int)$pdo->lastInsertId();
    creditoVincularCaixa($pdo, $idCredito, $idCaixa);
    $nomeAnexo = paxUploadAnexo('anexo');
    if ($nomeAnexo !== null) {
        $pdo->prepare('UPDATE ct_caixa SET anexo = :a WHERE id = :id')
            ->execute([':a' => $nomeAnexo, ':id' => $idCaixa]);
        $pdo->prepare('UPDATE ct_createfaturacredit SET anexo = :a WHERE id = :id')
            ->execute([':a' => $nomeAnexo, ':id' => $idCredito]);
    }
    creditarFatura($pdo, $voucher, $valor);
    $nomePagamento = $conta['name'] ?? '';
    logAudit($pdo, $voucher, 'Crédito no valor de R$ ' . $valor . ' pago com ' . $nomePagamento);
}
function creditoAtualizar(PDO $pdo, int $idCredito, string $voucher, float $valor, int $idConta): void
{
    $idCaixa = creditoResolverIdCaixa($pdo, $idCredito);
    $pdo->prepare('UPDATE ct_createfaturacredit SET valuecredit = :v, idaccountcurrent = :c WHERE id = :id')
        ->execute([':v' => $valor, ':c' => $idConta, ':id' => $idCredito]);
    if (!$idCaixa) {
        return;
    }
    $pdo->prepare('UPDATE ct_caixa SET valor = :v, idconta = :c WHERE id = :id')
        ->execute([':v' => $valor, ':c' => $idConta, ':id' => $idCaixa]);
    marcarReservaAlterada($pdo, $voucher);
}
function creditoExcluir(PDO $pdo, int $idCredito): void
{
    $idCaixa = creditoResolverIdCaixa($pdo, $idCredito);
    $pdo->prepare('DELETE FROM ct_createfaturacredit WHERE id = :id')->execute([':id' => $idCredito]);
    if (!$idCaixa) {
        return;
    }
    $pdo->prepare('DELETE FROM ct_caixa WHERE id = :id')->execute([':id' => $idCaixa]);
}
