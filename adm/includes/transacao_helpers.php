<?php
function transacaoSelectSql(): string
{
    return "SELECT c.id,c.datevencimento,c.idusr,u.firstname,u.lastname,c.datecompetencia,c.datepagamento,
        c.descricao,c.idempresa,f.fullname,f.id AS forid,tc.name AS tipo,cc.name AS conta,c.nome,
        p.name AS plano,p.id AS planoid,s.nameinvoice AS situacao,s.id AS stid,c.valor,c.anexo,
        tc.id AS tipoid,cc.id AS contaid,c.idstatus,c.idcliente,c.idtipo,c.idconta,c.idplano
        FROM ct_caixa c
        LEFT JOIN ct_fornecedor f ON f.id = c.idcliente
        LEFT JOIN ct_tipocaixa tc ON tc.id = c.idtipo
        LEFT JOIN ct_currentaccount cc ON cc.id = c.idconta
        LEFT JOIN ct_planaccounts p ON p.id = c.idplano
        LEFT JOIN ct_statusinvoice s ON s.id = c.idstatus
        LEFT JOIN ct_usuario u ON u.id = c.idusr";
}
function transacaoPorId(PDO $pdo, int $id): ?array
{
    $st = $pdo->prepare(transacaoSelectSql() . " WHERE c.id = :id");
    $st->execute([':id' => $id]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}
function transacaoEsc($valor): string
{
    return htmlentities((string)$valor, ENT_QUOTES, 'UTF-8');
}
function transacaoValorFormatado($valor): string
{
    return number_format((float)$valor, 2, ',', '.');
}
function transacaoData($data): string
{
    if (empty($data) || $data === '0000-00-00') {
        return '—';
    }
    return date('d/m/Y', strtotime($data));
}
function transacaoStatusClass($idstatus): string
{
    $map = [1 => 'tx-badge--pendente', 2 => 'tx-badge--pago', 3 => 'tx-badge--cancelado'];
    return $map[(int)$idstatus] ?? 'tx-badge--default';
}
function transacaoUploadAnexo(int $idTransacao): ?string
{
    $file = $_FILES['anexo'] ?? null;
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
    $filename = 'tx_' . $idTransacao . '_' . time() . '.' . $ext;
    return move_uploaded_file($file['tmp_name'], $uploadDir . $filename) ? $filename : null;
}
function transacaoAtualizar(PDO $pdo, array $dados): void
{
    $valor  = str_replace('.', '', $dados['valor']);
    $valor1 = str_replace(',', '.', $valor);
    $sql = "UPDATE ct_caixa SET datevencimento=:vencimento,datecompetencia=:competencia,datepagamento=:pagamento,
        nome=:nome,descricao=:descricao,idcliente=:cliente,idtipo=:tipo,idconta=:conta,idplano=:plano,
        idstatus=:statuus,valor=:valor,idempresa=:idempresa";
    $params = [
        ':vencimento'  => $dados['datavencimento'],
        ':pagamento'   => $dados['datapagamento'],
        ':nome'        => $dados['nome'],
        ':competencia' => $dados['datacompetencia'],
        ':descricao'   => $dados['documento'],
        ':cliente'     => $dados['favorecido'],
        ':tipo'        => $dados['tipo'],
        ':conta'       => $dados['contacorrente'],
        ':plano'       => $dados['planocontas'],
        ':statuus'     => $dados['status'],
        ':valor'       => $valor1,
        ':idempresa'   => $dados['empresa'],
        ':id'          => $dados['idtransacao'],
    ];
    if (!empty($dados['anexo'])) {
        $sql .= ",anexo=:anexo";
        $params[':anexo'] = $dados['anexo'];
    }
    $sql .= " WHERE id=:id";
    $st = $pdo->prepare($sql);
    $st->execute($params);
}
