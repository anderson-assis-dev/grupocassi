<?php
function reciboNorm(string $valor): string
{
    return mb_strtoupper(trim($valor), 'UTF-8');
}
function reciboEsc($valor): string
{
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}
function reciboValor($valor): string
{
    return number_format((float)$valor, 2, ',', '.');
}
function reciboData($data): string
{
    if (empty($data) || $data === '0000-00-00') {
        return '—';
    }
    return date('d/m/Y', strtotime($data));
}
function reciboCombustivelNomes(): array
{
    return [
        'POSTO DE GASOLINA',
        'COMBUSTÍVEL CARROS CASSI ERNANES',
        'COMBUSTÍVEL CARROS CASSI',
        'COMBUSTÍVEL CARROS CASSI ALEX',
        'COMBUSTÍVEL CARROS CASSI JOSE CLAUDIO',
        'COMBUSTÍVEL CARROS CASSI MARIO',
        'COMBUSTÍVEL CARROS CASSI ROMENIL',
        'COMBUSTÍVEL CARROS CASSI WELLINGTON',
        'COMBUSTIVEL CARROS CASSI REGINALDO PERREIRA',
        'COMBUSTÍVEL CARROS CASSI MERCÊS',
        'CARRO CONTRATADO ISAC EDER',
        'CARRO CONTRATADO CARLOS',
        'CARRO CONTRATADO IGOR',
        'CARRO CONTRATADO EDGAR',
        'CARRO CONTRATADO ANDRE',
        'CARRO CONTRATADO JEAN',
        'CARRO CONTRATADO PITA',
        'CARRO CONTRATADO DJAVAN',
        'CARRO CONTRATADO ROCK',
        'VAN CONTRATADA EDNEI',
        'VAN CONTRATADA AILTON',
        'CARROS CONTRATADOS MAR GRANDE',
        'CARRO CONTRATADO VALDINEI',
        'CARRO CONTRATADO NETO',
        'CARRO CONTRATADO LUCIANO'
    ];
}
function reciboTipo(array $registro): string
{
    $nome = reciboNorm($registro['fullname'] ?? '');
    if (in_array($nome, array_map('reciboNorm', reciboCombustivelNomes()), true)) {
        return 'combustivel';
    }
    if ($nome === reciboNorm('PRESTAÇÃO DE SERVIÇO')) {
        return 'prestacao';
    }
    if ($nome === reciboNorm('GUIA DE TURISMO')) {
        return 'guia';
    }
    if ($nome === reciboNorm('COMPRA DE FOLGA')) {
        return 'folga';
    }
    return 'padrao';
}
function reciboCarregar(PDO $pdo, array $input): ?array
{
    if (!empty($input['segundavia'])) {
        $st = $pdo->prepare(
            "SELECT c.id,c.datevencimento,c.datecompetencia,c.datepagamento,c.descricao,forne.fullname,
            tc.name AS tipo,cc.name AS conta,p.name AS plano,s.nameinvoice AS situacao,c.valor,c.nome,
            c.idcliente,u.firstname,u.lastname
            FROM ct_caixa c
            LEFT JOIN ct_fornecedor forne ON forne.id = c.idcliente
            LEFT JOIN ct_tipocaixa tc ON tc.id = c.idtipo
            LEFT JOIN ct_currentaccount cc ON cc.id = c.idconta
            LEFT JOIN ct_planaccounts p ON p.id = c.idplano
            LEFT JOIN ct_statusinvoice s ON s.id = c.idstatus
            LEFT JOIN ct_usuario u ON u.id = c.idusr
            WHERE c.descricao LIKE :descricao AND c.valor = :valor LIMIT 1"
        );
        $st->execute([
            ':descricao' => '%' . ($input['voucher'] ?? '') . '%',
            ':valor' => $input['valor'] ?? 0
        ]);
    } else {
        $id = (int)($input['idtransacao'] ?? 0);
        if ($id <= 0) {
            return null;
        }
        $st = $pdo->prepare(
            "SELECT c.id,c.datevencimento,c.datecompetencia,c.datepagamento,c.descricao,forne.fullname,
            tc.name AS tipo,cc.name AS conta,p.name AS plano,s.nameinvoice AS situacao,c.valor,c.nome,
            c.idcliente,u.firstname,u.lastname
            FROM ct_caixa c
            LEFT JOIN ct_fornecedor forne ON forne.id = c.idcliente
            LEFT JOIN ct_tipocaixa tc ON tc.id = c.idtipo
            LEFT JOIN ct_currentaccount cc ON cc.id = c.idconta
            LEFT JOIN ct_planaccounts p ON p.id = c.idplano
            LEFT JOIN ct_statusinvoice s ON s.id = c.idstatus
            LEFT JOIN ct_usuario u ON u.id = c.idusr
            WHERE c.id = :id"
        );
        $st->execute([':id' => $id]);
    }
    $row = $st->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}
function reciboStyles(): string
{
    return '<style>
    body{font-family:DejaVu Sans,sans-serif;font-size:12px;color:#1f2937;margin:0;padding:24px}
    .recibo-wrap{max-width:720px;margin:0 auto}
    .recibo-logo{text-align:center;margin-bottom:18px}
    .recibo-logo img{max-width:280px;height:auto}
    .recibo-head{border-bottom:2px solid #1e4770;padding-bottom:14px;margin-bottom:22px;text-align:center}
    .recibo-title{font-size:20px;font-weight:700;color:#1e4770;margin:0 0 6px}
    .recibo-meta{color:#64748b;font-size:10px;margin:2px 0}
    .recibo-body{font-size:13px;line-height:1.7;margin:28px 0;text-align:justify}
    .recibo-valor{font-size:18px;font-weight:700;color:#0f7a49;text-align:right;margin:18px 0}
    .recibo-campo{border:1px solid #dce3ec;border-radius:8px;padding:12px 14px;margin:14px 0;background:#f8fafc}
    .recibo-campo label{color:#64748b;font-size:10px;font-weight:700;text-transform:uppercase;display:block;margin-bottom:6px}
    .recibo-linha{border-bottom:1px solid #cbd5e1;height:22px;margin-top:4px}
    .recibo-local{margin:36px 0 18px;font-size:13px}
    .recibo-assinatura{margin-top:48px;text-align:center}
    .recibo-assinatura-linha{border-top:1px solid #1f2937;width:80%;margin:0 auto 10px}
    .recibo-assinatura-nome{font-weight:700;font-size:13px}
    .recibo-grid-2{width:100%;margin-top:12px}
    .recibo-grid-2 td{width:50%;padding:6px 8px 6px 0;vertical-align:top}
    </style>';
}
function reciboMeta(array $r): string
{
    $pago = reciboEsc(trim(($r['firstname'] ?? '') . ' ' . ($r['lastname'] ?? '')));
    $html = '<div class="recibo-meta">Impresso em: ' . date('d/m/Y H:i') . '</div>';
    if ($pago !== '') {
        $html .= '<div class="recibo-meta">Pago por: ' . $pago . '</div>';
    }
    return $html;
}
function reciboAssinatura(array $r): string
{
    return '<div class="recibo-assinatura"><div class="recibo-assinatura-linha"></div><div class="recibo-assinatura-nome">' . reciboEsc($r['nome'] ?? '') . '</div></div>';
}
function reciboHtml(array $r, string $tipo): string
{
    $styles = reciboStyles();
    $id = (int)($r['id'] ?? 0);
    $valor = reciboValor($r['valor'] ?? 0);
    $descricao = reciboEsc($r['descricao'] ?? '');
    $dataVenc = reciboData($r['datevencimento'] ?? '');
    $logoPadrao = '../../images/logo.png';
    $logoFolga = '../../images/logo2.png';
    if ($tipo === 'combustivel') {
        return '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Recibo Combustível #' . $id . '</title>' . $styles . '</head><body><div class="recibo-wrap">'
            . '<div class="recibo-head"><div class="recibo-title">COMBUSTÍVEL</div><div class="recibo-meta">Nº ' . $id . '</div>' . reciboMeta($r) . '</div>'
            . '<div class="recibo-body">Recebi da <strong>CASSI TURISMO</strong> a importância de <strong>R$ ' . $valor . '</strong>, referente a ' . $descricao . '.</div>'
            . '<table class="recibo-grid-2"><tr><td><div class="recibo-campo"><label>KM início</label><div class="recibo-linha"></div></div></td><td><div class="recibo-campo"><label>KM final</label><div class="recibo-linha"></div></div></td></tr></table>'
            . '<div class="recibo-campo"><label>Placa do carro</label><div class="recibo-linha"></div></div>'
            . '<div class="recibo-campo"><label>Rota</label><div class="recibo-linha"></div></div>'
            . '<div class="recibo-local">Salvador, ' . $dataVenc . '</div>'
            . reciboAssinatura($r) . '</div></body></html>';
    }
    if ($tipo === 'prestacao') {
        return '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Recibo #' . $id . '</title>' . $styles . '</head><body><div class="recibo-wrap">'
            . '<div class="recibo-head"><div class="recibo-title">RECIBO Nº ' . $id . '</div>' . reciboMeta($r) . '</div>'
            . '<div class="recibo-body">Recebi da <strong>CASSI TURISMO</strong> a importância de <strong>R$ ' . $valor . '</strong>, referente a ' . $descricao . '.</div>'
            . '<div class="recibo-local">Salvador, ' . $dataVenc . '</div>'
            . '<div class="recibo-campo"><label>Data de recebimento</label><div class="recibo-linha">____/____/______</div></div>'
            . reciboAssinatura($r) . '</div></body></html>';
    }
    if ($tipo === 'guia') {
        return '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Recibo Guia #' . $id . '</title>' . $styles . '</head><body><div class="recibo-wrap">'
            . '<div class="recibo-head"><div class="recibo-title">RECIBO Nº ' . $id . '</div>' . reciboMeta($r) . '</div>'
            . '<div class="recibo-body">Recebi da <strong>CASSI TURISMO</strong> a importância de <strong>R$ ' . $valor . '</strong>, referente a ' . $descricao . '.</div>'
            . '<div class="recibo-local">Salvador, ' . $dataVenc . '</div>'
            . reciboAssinatura($r) . '</div></body></html>';
    }
    if ($tipo === 'folga') {
        $meses = ['','janeiro','fevereiro','março','abril','maio','junho','julho','agosto','setembro','outubro','novembro','dezembro'];
        $dataExt = date('j') . ' de ' . $meses[(int)date('n')] . ' de ' . date('Y');
        return '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Declaração Folga #' . $id . '</title>' . $styles . '</head><body><div class="recibo-wrap">'
            . '<div class="recibo-logo"><img src="' . $logoFolga . '" alt="Cassi Turismo"></div>'
            . '<div class="recibo-head"><div class="recibo-title">RECIBO Nº ' . $id . '</div><div class="recibo-meta">Declaração de venda de folga</div>' . reciboMeta($r) . '</div>'
            . '<div class="recibo-valor">R$ ' . $valor . '</div>'
            . '<div class="recibo-body">Recebi da empresa <strong>CASSI TURISMO</strong> a quantia de <strong>R$ ' . $valor . '</strong>, referente ao pagamento da diária de serviço prestado no dia da minha folga em <strong>' . $dataVenc . '</strong>, onde me disponibilizei a trabalhar por iniciativa própria.</div>'
            . '<div class="recibo-local" style="text-align:center">' . $dataExt . '</div>'
            . reciboAssinatura($r) . '</div></body></html>';
    }
    return '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Recibo #' . $id . '</title>' . $styles . '</head><body><div class="recibo-wrap">'
        . '<div class="recibo-logo"><img src="' . $logoPadrao . '" alt="Cassi Turismo"></div>'
        . '<div class="recibo-head"><div class="recibo-title">RECIBO Nº ' . $id . '</div>' . reciboMeta($r) . '</div>'
        . '<div class="recibo-body">Recebi da <strong>CASSI TURISMO</strong> a importância de <strong>R$ ' . $valor . '</strong>, referente a ' . $descricao . '.</div>'
        . '<div class="recibo-local">Salvador, ' . $dataVenc . '</div>'
        . reciboAssinatura($r) . '</div></body></html>';
}
