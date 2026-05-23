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
    @page{size:A4;margin:0}
    html,body{margin:0;padding:0;background:#eef2f7;color:#1f2937}
    body{font-family:Arial,Helvetica,sans-serif;font-size:13px}
    .recibo-toolbar{align-items:center;background:#1e4770;box-shadow:0 4px 16px rgba(15,23,42,.18);display:flex;gap:10px;justify-content:center;left:0;padding:12px 16px;position:fixed;right:0;top:0;z-index:1000}
    .recibo-toolbar button{background:#fff;border:0;border-radius:8px;color:#1e4770;cursor:pointer;font-size:14px;font-weight:700;min-width:120px;padding:10px 18px}
    .recibo-toolbar button:hover{background:#f8fafc}
    .recibo-toolbar span{color:rgba(255,255,255,.85);font-size:13px}
    .recibo-page{margin:72px auto 24px;max-width:760px;padding:0 16px}
    .recibo-sheet{background:#fff;border:1px solid #dce3ec;border-radius:12px;box-shadow:0 10px 30px rgba(15,23,42,.08);padding:28px 32px 36px}
    .recibo-wrap{margin:0 auto;max-width:680px}
    .recibo-logo{margin-bottom:18px;text-align:center}
    .recibo-logo img{height:auto;max-width:280px}
    .recibo-head{border-bottom:2px solid #1e4770;margin-bottom:22px;padding-bottom:14px;text-align:center}
    .recibo-title{color:#1e4770;font-size:22px;font-weight:700;margin:0 0 6px}
    .recibo-meta{color:#64748b;font-size:11px;margin:2px 0}
    .recibo-body{font-size:14px;line-height:1.75;margin:28px 0;text-align:justify}
    .recibo-valor{color:#0f7a49;font-size:20px;font-weight:700;margin:18px 0;text-align:right}
    .recibo-campo{background:#f8fafc;border:1px solid #dce3ec;border-radius:8px;margin:14px 0;padding:12px 14px}
    .recibo-campo label{color:#64748b;display:block;font-size:10px;font-weight:700;margin-bottom:6px;text-transform:uppercase}
    .recibo-linha{border-bottom:1px solid #cbd5e1;height:22px;margin-top:4px}
    .recibo-local{font-size:13px;margin:36px 0 18px}
    .recibo-assinatura{margin-top:48px;text-align:center}
    .recibo-assinatura-linha{border-top:1px solid #1f2937;margin:0 auto 10px;width:80%}
    .recibo-assinatura-nome{font-size:13px;font-weight:700}
    .recibo-grid-2{margin-top:12px;width:100%}
    .recibo-grid-2 td{padding:6px 8px 6px 0;vertical-align:top;width:50%}
    @media print{
    .recibo-toolbar{display:none!important}
    html,body{background:#fff!important}
    .recibo-page{margin:0!important;max-width:none!important;padding:0!important}
    .recibo-sheet{border:0!important;border-radius:0!important;box-shadow:none!important;padding:18mm 16mm!important}
    .recibo-wrap{max-width:none!important}
    }
    </style>';
}
function reciboPrintScript(): string
{
    return '<script>
    function reciboImprimir(){window.print()}
    window.addEventListener("load",function(){setTimeout(reciboImprimir,300)});
    window.addEventListener("afterprint",function(){if(window.opener){window.close()}});
    </script>';
}
function reciboPrintToolbar(int $id): string
{
    return '<div class="recibo-toolbar no-print">'
        . '<span>Recibo Nº ' . $id . '</span>'
        . '<button type="button" onclick="reciboImprimir()">Imprimir</button>'
        . '<button type="button" onclick="window.close()">Fechar</button>'
        . '</div>';
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
function reciboConteudo(array $r, string $tipo): string
{
    $id = (int)($r['id'] ?? 0);
    $valor = reciboValor($r['valor'] ?? 0);
    $descricao = reciboEsc($r['descricao'] ?? '');
    $dataVenc = reciboData($r['datevencimento'] ?? '');
    $logoPadrao = '../../images/logo.png';
    $logoFolga = '../../images/logo2.png';
    if ($tipo === 'combustivel') {
        return '<div class="recibo-wrap">'
            . '<div class="recibo-head"><div class="recibo-title">COMBUSTÍVEL</div><div class="recibo-meta">Nº ' . $id . '</div>' . reciboMeta($r) . '</div>'
            . '<div class="recibo-body">Recebi da <strong>CASSI TURISMO</strong> a importância de <strong>R$ ' . $valor . '</strong>, referente a ' . $descricao . '.</div>'
            . '<table class="recibo-grid-2"><tr><td><div class="recibo-campo"><label>KM início</label><div class="recibo-linha"></div></div></td><td><div class="recibo-campo"><label>KM final</label><div class="recibo-linha"></div></div></td></tr></table>'
            . '<div class="recibo-campo"><label>Placa do carro</label><div class="recibo-linha"></div></div>'
            . '<div class="recibo-campo"><label>Rota</label><div class="recibo-linha"></div></div>'
            . '<div class="recibo-local">Salvador, ' . $dataVenc . '</div>'
            . reciboAssinatura($r) . '</div>';
    }
    if ($tipo === 'prestacao') {
        return '<div class="recibo-wrap">'
            . '<div class="recibo-head"><div class="recibo-title">RECIBO Nº ' . $id . '</div>' . reciboMeta($r) . '</div>'
            . '<div class="recibo-body">Recebi da <strong>CASSI TURISMO</strong> a importância de <strong>R$ ' . $valor . '</strong>, referente a ' . $descricao . '.</div>'
            . '<div class="recibo-local">Salvador, ' . $dataVenc . '</div>'
            . '<div class="recibo-campo"><label>Data de recebimento</label><div class="recibo-linha">____/____/______</div></div>'
            . reciboAssinatura($r) . '</div>';
    }
    if ($tipo === 'guia') {
        return '<div class="recibo-wrap">'
            . '<div class="recibo-head"><div class="recibo-title">RECIBO Nº ' . $id . '</div>' . reciboMeta($r) . '</div>'
            . '<div class="recibo-body">Recebi da <strong>CASSI TURISMO</strong> a importância de <strong>R$ ' . $valor . '</strong>, referente a ' . $descricao . '.</div>'
            . '<div class="recibo-local">Salvador, ' . $dataVenc . '</div>'
            . reciboAssinatura($r) . '</div>';
    }
    if ($tipo === 'folga') {
        $meses = ['','janeiro','fevereiro','março','abril','maio','junho','julho','agosto','setembro','outubro','novembro','dezembro'];
        $dataExt = date('j') . ' de ' . $meses[(int)date('n')] . ' de ' . date('Y');
        return '<div class="recibo-wrap">'
            . '<div class="recibo-logo"><img src="' . $logoFolga . '" alt="Cassi Turismo"></div>'
            . '<div class="recibo-head"><div class="recibo-title">RECIBO Nº ' . $id . '</div><div class="recibo-meta">Declaração de venda de folga</div>' . reciboMeta($r) . '</div>'
            . '<div class="recibo-valor">R$ ' . $valor . '</div>'
            . '<div class="recibo-body">Recebi da empresa <strong>CASSI TURISMO</strong> a quantia de <strong>R$ ' . $valor . '</strong>, referente ao pagamento da diária de serviço prestado no dia da minha folga em <strong>' . $dataVenc . '</strong>, onde me disponibilizei a trabalhar por iniciativa própria.</div>'
            . '<div class="recibo-local" style="text-align:center">' . $dataExt . '</div>'
            . reciboAssinatura($r) . '</div>';
    }
    return '<div class="recibo-wrap">'
        . '<div class="recibo-logo"><img src="' . $logoPadrao . '" alt="Cassi Turismo"></div>'
        . '<div class="recibo-head"><div class="recibo-title">RECIBO Nº ' . $id . '</div>' . reciboMeta($r) . '</div>'
        . '<div class="recibo-body">Recebi da <strong>CASSI TURISMO</strong> a importância de <strong>R$ ' . $valor . '</strong>, referente a ' . $descricao . '.</div>'
        . '<div class="recibo-local">Salvador, ' . $dataVenc . '</div>'
        . reciboAssinatura($r) . '</div>';
}
function reciboPaginaPrint(array $r, string $tipo): string
{
    $id = (int)($r['id'] ?? 0);
    return '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title></title>'
        . reciboStyles()
        . '</head><body>'
        . reciboPrintToolbar($id)
        . '<div class="recibo-page"><div class="recibo-sheet">'
        . reciboConteudo($r, $tipo)
        . '</div></div>'
        . reciboPrintScript()
        . '</body></html>';
}
function reciboHtml(array $r, string $tipo): string
{
    return reciboPaginaPrint($r, $tipo);
}
