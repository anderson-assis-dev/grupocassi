<?php
require_once __DIR__ . '/ref_cache.php';
require_once __DIR__ . '/audit.php';
require_once __DIR__ . '/pax_helpers.php';
require_once __DIR__ . '/transacao_helpers.php';
function comissaoServicosExcluidos(): array
{
    return [19, 30, 47, 48, 17, 18, 31, 53, 155];
}
function comissaoServicoElegivel(int $idservico): bool
{
    return !in_array($idservico, comissaoServicosExcluidos(), true);
}
function comissaoUsuarioPodePagar(): bool
{
    return !empty($_SESSION['idreservaplus'])
        || !empty($_SESSION['idgerente'])
        || !empty($_SESSION['idreservamanager'])
        || !empty($_SESSION['idfaturador'])
        || in_array((int)($_SESSION['id'] ?? 0), [36, 225, 273], true);
}
function comissaoListaServicos(array $dadosGerais, array $registro, array $listaServicos): array
{
    $servicos = [];
    foreach ($listaServicos as $item) {
        if ($item->fullname === $dadosGerais['serivco'] && comissaoServicoElegivel((int)$item->id)) {
            $servicos[] = ['nome' => $item->fullname, 'idservico' => (int)$item->id];
            break;
        }
    }
    foreach ($registro as $item) {
        if (comissaoServicoElegivel((int)$item->idservico)) {
            $servicos[] = ['nome' => $item->fullname, 'idservico' => (int)$item->idservico];
        }
    }
    return $servicos;
}
function comissaoServicosPendentes(array $dadosGerais, array $registro, array $listaServicos, int $contadorDespesa): array
{
    return array_slice(comissaoListaServicos($dadosGerais, $registro, $listaServicos), $contadorDespesa);
}
function comissaoExibirFormulario(array $dadosGerais, array $registro, array $listaServicos, int $contadorDespesa): bool
{
    return comissaoUsuarioPodePagar()
        && count(comissaoServicosPendentes($dadosGerais, $registro, $listaServicos, $contadorDespesa)) > 0;
}
function comissaoBuscarAgente(PDO $pdo, string $nome): ?array
{
    $nome = trim($nome);
    foreach (refAgentes($pdo) as $ag) {
        if (strcasecmp($ag->fullname, $nome) === 0) {
            return ['id' => (int)$ag->id, 'fullname' => $ag->fullname];
        }
    }
    return null;
}
function comissaoGarantirAgente(PDO $pdo, string $nomeAgente): array
{
    $dados = comissaoBuscarAgente($pdo, $nomeAgente);
    if ($dados !== null) {
        return $dados;
    }
    $pdo->prepare('INSERT INTO `ct_agentes` (`id`, `fullname`) VALUES (DEFAULT, :nome)')
        ->execute([':nome' => strtoupper($nomeAgente)]);
    refCacheFlush('agentes');
    $dados = comissaoBuscarAgente($pdo, $nomeAgente);
    if ($dados === null) {
        throw new RuntimeException('Não foi possível cadastrar o agente.');
    }
    return $dados;
}
function comissaoVincularAgenteReserva(PDO $pdo, int $idAgente, string $voucher): void
{
    $pdo->prepare('UPDATE `ct_reserva` SET `idagente` = :novoid WHERE `numbervoucher` = :voucher')
        ->execute([':novoid' => $idAgente, ':voucher' => $voucher]);
}
function comissaoCreditarFatura(PDO $pdo, int $numberfatura, float $valor): void
{
    if ($numberfatura <= 0) {
        return;
    }
    $st = $pdo->prepare('SELECT tarifa FROM `ct_fatura` WHERE `id` = :id');
    $st->execute([':id' => $numberfatura]);
    $tarifa = (float)($st->fetch(PDO::FETCH_ASSOC)['tarifa'] ?? 0);
    $pdo->prepare('UPDATE `ct_fatura` SET `tarifa` = :tarifa WHERE `id` = :id')
        ->execute([':tarifa' => $tarifa + $valor, ':id' => $numberfatura]);
}
function comissaoInserirCredito(PDO $pdo, string $voucher, float $valor, bool $comCaixa): int
{
    if ($comCaixa) {
        $sql = "INSERT INTO `ct_createfaturacredit` SET `numbervoucher` = :voucher, `tarifa` = 0, `desccredit` = NOW(),
            `datacredit` = '0000-00-00', `valuecredit` = 0, `valueguia` = 0, `valueagente` = :valor,
            `dataagente` = NOW(), `idaccountcurrent` = 1, `idplancount` = 1";
        $pdo->prepare($sql)->execute([':voucher' => $voucher, ':valor' => $valor]);
        return (int)$pdo->lastInsertId();
    }
    $pdo->prepare(
        'INSERT INTO `ct_createfaturacredit` (`id`, `numbervoucher`, `tarifa`, `desccredit`, `datacredit`, `valuecredit`,
        `valueguia`, `valueagente`, `dataagente`, `idaccountcurrent`, `idplancount`)
        VALUES (DEFAULT, :numbervoucher, 0, 0, 0, 0, 0, :va, :vad, 1, 1)'
    )->execute([
        ':numbervoucher' => $voucher,
        ':va' => $valor,
        ':vad' => date('Y-m-d'),
    ]);
    return (int)$pdo->lastInsertId();
}
function comissaoVincularCaixa(PDO $pdo, int $idComissao, int $idCaixa): void
{
    $pdo->prepare('UPDATE ct_createfaturacredit SET idcaixa = :caixa WHERE id = :id')
        ->execute([':caixa' => $idCaixa, ':id' => $idComissao]);
}
function comissaoPorId(PDO $pdo, int $id): ?array
{
    $st = $pdo->prepare('SELECT * FROM ct_createfaturacredit WHERE id = :id');
    $st->execute([':id' => $id]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}
function comissaoResolverIdCaixa(PDO $pdo, int $idComissao): ?int
{
    $row = comissaoPorId($pdo, $idComissao);
    if (!$row || (float)$row['valueagente'] <= 0) {
        return null;
    }
    $idcaixa = (int)($row['idcaixa'] ?? 0);
    if ($idcaixa > 0) {
        return $idcaixa;
    }
    $st = $pdo->prepare(
        "SELECT id FROM ct_caixa WHERE descricao LIKE :d AND idplano = 30 AND idtipo = 2
        AND valor = :v ORDER BY id DESC LIMIT 1"
    );
    $st->execute([
        ':d' => '%' . $row['numbervoucher'] . '%',
        ':v' => $row['valueagente'],
    ]);
    $id = (int)($st->fetchColumn() ?: 0);
    if ($id > 0) {
        comissaoVincularCaixa($pdo, $idComissao, $id);
    }
    return $id ?: null;
}
function comissaoAtualizarValor(PDO $pdo, int $idComissao, float $valor): void
{
    $idCaixa = comissaoResolverIdCaixa($pdo, $idComissao);
    $pdo->prepare('UPDATE ct_createfaturacredit SET valueagente = :v WHERE id = :id')
        ->execute([':v' => $valor, ':id' => $idComissao]);
    if (!$idCaixa) {
        return;
    }
    $pdo->prepare('UPDATE ct_caixa SET valor = :v WHERE id = :id')
        ->execute([':v' => $valor, ':id' => $idCaixa]);
}
function comissaoExcluir(PDO $pdo, int $idComissao): void
{
    $idCaixa = comissaoResolverIdCaixa($pdo, $idComissao);
    $pdo->prepare('DELETE FROM ct_createfaturacredit WHERE id = :id')->execute([':id' => $idComissao]);
    if (!$idCaixa) {
        return;
    }
    $pdo->prepare('DELETE FROM ct_caixa WHERE id = :id')->execute([':id' => $idCaixa]);
}
function comissaoRegistrarPagamento(PDO $pdo, string $voucher, float $valor, string $nomeAgente): int
{
    $idComissao = comissaoInserirCredito($pdo, $voucher, $valor, true);
    $idCaixa = comissaoCriarTransacaoCaixa($pdo, $nomeAgente, $voucher, $valor);
    comissaoVincularCaixa($pdo, $idComissao, $idCaixa);
    return $idComissao;
}
function comissaoCriarTransacaoCaixa(PDO $pdo, string $nomeAgente, string $voucher, float $valor): int
{
    $pdo->prepare(
        "INSERT INTO `ct_caixa` (`id`, `datevencimento`, `datepagamento`, `datecompetencia`, `nome`, `descricao`,
        `idcliente`, `idtipo`, `idconta`, `idplano`, `idstatus`, `valor`, `idusr`, `dataabertura`)
        VALUES (DEFAULT, :vencimento, :pagamento, :competencia, :nome, :descricao, :cliente, :tipo, :conta, :plano,
        :statuus, :valor, :idusr, :abertura)"
    )->execute([
        ':vencimento' => date('Y-m-d'),
        ':pagamento' => date('Y-m-d'),
        ':competencia' => date('Y-m-d'),
        ':nome' => $nomeAgente,
        ':descricao' => 'Pagamento de comissao para o voucher: ' . $voucher,
        ':cliente' => 8,
        ':tipo' => 2,
        ':conta' => 14,
        ':plano' => 30,
        ':statuus' => 6,
        ':valor' => $valor,
        ':idusr' => $_SESSION['id'],
        ':abertura' => date('Y-m-d'),
    ]);
    return (int)$pdo->lastInsertId();
}
function comissaoUsuarioPodeFaturar(): bool
{
    return in_array((int)($_SESSION['id'] ?? 0), [28, 34, 46, 1], true);
}
function comissaoRegistrarFatura(PDO $pdo, string $voucher, string $nomeAgente, float $valor, int $idCliente): void
{
    if (!comissaoUsuarioPodeFaturar()) {
        return;
    }
    $texto = $_SESSION['nome'] . ' -> ' . date('d-m-Y') . ' COMISSAO PAGA AO ' . $nomeAgente . ' R$ ' . $valor;
    $pdo->prepare(
        'INSERT INTO `ct_createfatura` (`id`, `numbervoucher`, `datematurity`, `datepayment`, `numberadd`,
        `obervacao`, `idcurrentaccount`, `idcliente`) VALUES (DEFAULT, :voucher, :vencimento, :pagamento,
        :numeracao, :observacao, :conta, :idcliente)'
    )->execute([
        ':voucher' => $voucher,
        ':vencimento' => date('Y-m-d'),
        ':pagamento' => date('Y-m-d'),
        ':numeracao' => $texto,
        ':observacao' => '.',
        ':conta' => 14,
        ':idcliente' => $idCliente,
    ]);
    $pdo->prepare('UPDATE `ct_reserva` SET `idstatusinvoice` = :sinvoice WHERE numbervoucher = :voucher')
        ->execute([':sinvoice' => 5, ':voucher' => $voucher]);
    logAudit($pdo, $voucher, 'Fatura Cadastrada ' . $texto);
}
function comissaoSalvarAnexo(PDO $pdo, string $voucher): void
{
    $nomeAnexo = paxUploadAnexo('anexo');
    if ($nomeAnexo === null) {
        return;
    }
    $st = $pdo->prepare(
        "SELECT id FROM ct_createfaturacredit WHERE numbervoucher = :v AND dataagente > '0000-00-00'
        ORDER BY id DESC LIMIT 1"
    );
    $st->execute([':v' => $voucher]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $pdo->prepare('UPDATE ct_createfaturacredit SET anexo = :a WHERE id = :id')
            ->execute([':a' => $nomeAnexo, ':id' => $row['id']]);
    }
}
function comissaoContarAdicionais(PDO $pdo, int $idReserva): int
{
    $ids = implode(',', comissaoServicosExcluidos());
    $st = $pdo->prepare(
        "SELECT COUNT(*) FROM `ct_recentlyadd` WHERE idrecently = :id AND idservice NOT IN ($ids)"
    );
    $st->execute([':id' => $idReserva]);
    return (int)$st->fetchColumn();
}
function comissaoContarPagamentos(PDO $pdo, string $voucher): int
{
    $st = $pdo->prepare(
        "SELECT COUNT(*) FROM `ct_createfaturacredit` WHERE numbervoucher = :voucher AND `dataagente` > :dataa"
    );
    $st->execute([':voucher' => $voucher, ':dataa' => '0000-00-00']);
    return (int)$st->fetchColumn();
}
function comissaoUltimoPagamento(PDO $pdo, string $voucher): ?array
{
    $st = $pdo->prepare(
        "SELECT * FROM `ct_createfaturacredit` WHERE numbervoucher = :voucher AND `dataagente` > :dataa
        ORDER BY id DESC LIMIT 1"
    );
    $st->execute([':voucher' => $voucher, ':dataa' => '0000-00-00']);
    $row = $st->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}
function comissaoProcessarPagamento(PDO $pdo, string $nomeAgente, string $voucher, float $valorUnitario, string $nomeServicoPago): array
{
    $st = $pdo->prepare(
        'SELECT r.id, a.fullname, r.idservico, r.numbervoucher, r.pax, r.documento, r.dateinput AS embarque,
        r.idcliente AS cliente, r.numberfatura FROM `ct_reserva` r
        LEFT JOIN ct_agentes a ON r.idagente = a.id WHERE `numbervoucher` = :voucher'
    );
    $st->execute([':voucher' => $voucher]);
    $dadosReserva = $st->fetch(PDO::FETCH_ASSOC);
    if (!$dadosReserva) {
        return ['status' => 'erro', 'dadosReserva' => null];
    }
    $contadorPagamento = comissaoContarPagamentos($pdo, $voucher);
    $contadorAdicionais = comissaoContarAdicionais($pdo, (int)$dadosReserva['id']);
    comissaoCreditarFatura($pdo, (int)$dadosReserva['numberfatura'], $valorUnitario);
    $pagamentoPrincipal = $contadorPagamento === 0
        && comissaoServicoElegivel((int)$dadosReserva['idservico']);
    if ($pagamentoPrincipal) {
        $dadosagt = comissaoGarantirAgente($pdo, $nomeAgente);
        comissaoVincularAgenteReserva($pdo, (int)$dadosagt['id'], $voucher);
        comissaoRegistrarPagamento($pdo, $voucher, $valorUnitario, $nomeAgente);
        comissaoRegistrarFatura($pdo, $voucher, $nomeAgente, $valorUnitario, (int)$dadosReserva['cliente']);
        logAudit(
            $pdo,
            $voucher,
            'A comissão de R$ ' . $valorUnitario . ' foi paga ao agente ' . $dadosagt['fullname']
            . ' para o serviço ' . $nomeServicoPago
        );
        comissaoSalvarAnexo($pdo, $voucher);
        return ['status' => 'ok', 'dadosReserva' => $dadosReserva];
    }
    if ($contadorAdicionais > 0 && $contadorPagamento <= $contadorAdicionais) {
        comissaoRegistrarPagamento($pdo, $voucher, $valorUnitario, $nomeAgente);
        $dadosagt = comissaoBuscarAgente($pdo, $nomeAgente);
        $nomeAgt = $dadosagt['fullname'] ?? $nomeAgente;
        logAudit(
            $pdo,
            $voucher,
            'A comissão de R$ ' . $valorUnitario . ' foi paga ao agente ' . $nomeAgt
            . ' para o serviço ' . $nomeServicoPago
        );
        comissaoRegistrarFatura($pdo, $voucher, $nomeAgente, $valorUnitario, (int)$dadosReserva['cliente']);
        comissaoSalvarAnexo($pdo, $voucher);
        return ['status' => 'ok', 'dadosReserva' => $dadosReserva];
    }
    logAudit(
        $pdo,
        $voucher,
        'Tentou pagar a comissão mais de uma vez para ' . $nomeAgente . ' com o valor de R$ ' . $valorUnitario
    );
    return [
        'status' => 'duplicado',
        'dadosReserva' => $dadosReserva,
        'dadosPagamento' => comissaoUltimoPagamento($pdo, $voucher),
    ];
}
function comissaoHtmlRecibo(array $dadosReserva, string $nomeAgente, string $nomeServicoPago, float $valorUnitario): string
{
    $voucher = htmlspecialchars($dadosReserva['numbervoucher'] ?? '', ENT_QUOTES, 'UTF-8');
    $pax = htmlspecialchars($dadosReserva['pax'] ?? '', ENT_QUOTES, 'UTF-8');
    $documento = htmlspecialchars($dadosReserva['documento'] ?? '', ENT_QUOTES, 'UTF-8');
    $valor = number_format($valorUnitario, 2, ',', '.');
    $descricao = htmlspecialchars(
        'Pagamento de Comissão de ' . $nomeAgente . ' para o serviço ' . $nomeServicoPago,
        ENT_QUOTES,
        'UTF-8'
    );
    $usuario = htmlspecialchars($_SESSION['nome'] ?? '', ENT_QUOTES, 'UTF-8');
    return '<html xmlns="http://www.w3.org/1999/xhtml"><head><meta charset="utf-8">'
        . '<title>Recibo de Comissão</title><link rel="stylesheet" href="materialize.min.css"></head><body>'
        . '<div class="container"><img id="logo" src="../.././images/logo.png"/><hr>'
        . '<h4 align="center">Solicitação de pagamento ' . $voucher . '</h4>'
        . '<small style="font-size:8px">Impresso em: ' . date('d-m-Y') . '</small><hr>'
        . '<table class="highlight"><thead><tr><th>PAX</th><th>Documento</th><th>Valor</th></tr></thead>'
        . '<tbody><tr><td>' . $pax . '</td><td>' . $documento . '</td><td>R$ ' . $valor . '</td></tr></tbody></table><hr>'
        . '<table class="highlight"><thead><tr><th>Descrição</th><th>Valor Total</th></tr></thead>'
        . '<tbody><tr><td>' . $descricao . '</td><td>R$ ' . $valor . '</td></tr></tbody></table><hr>'
        . '<table class="highlight"><tbody><tr>'
        . '<td>_______________________________________________________________________________________________</td>'
        . '<td>_______________________________________________________________________________________________</td>'
        . '</tr></tbody><tfoot><tr>'
        . '<th style="text-align:center">' . $usuario . '</th>'
        . '<th style="text-align:center">Autorizado</th>'
        . '</tr></tfoot></table></div></body></html>';
}
function comissaoHtmlJaPago(array $dadosReserva, ?array $dadosPagamento): string
{
    $voucher = htmlspecialchars($dadosReserva['numbervoucher'] ?? '', ENT_QUOTES, 'UTF-8');
    $agente = htmlspecialchars($dadosReserva['fullname'] ?? '', ENT_QUOTES, 'UTF-8');
    $data = !empty($dadosPagamento['dataagente'])
        ? date('d-m-Y', strtotime($dadosPagamento['dataagente']))
        : date('d-m-Y');
    return '<html xmlns="http://www.w3.org/1999/xhtml"><head><meta charset="utf-8">'
        . '<title>Recibo de Comissão</title><link rel="stylesheet" href="materialize.min.css"></head><body>'
        . '<div class="container"><img id="logo" src="../.././images/logo.png"/><hr>'
        . '<h4 align="center">O pagamento da comissão já foi realizado para o voucher: '
        . $voucher . ' em ' . $data . ' ' . $agente . '</h4></div></body></html>';
}
