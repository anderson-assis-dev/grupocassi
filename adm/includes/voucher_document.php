<?php
function imagemParaDataUri(string $path): string
{
    if (!is_readable($path)) {
        return '';
    }
    $info = @getimagesize($path);
    if (!$info) {
        return '';
    }
    $mime = $info['mime'] ?? 'image/png';
    return 'data:' . $mime . ';base64,' . base64_encode((string) file_get_contents($path));
}
function logoCassiDataUri(): string
{
    static $uri = null;
    if ($uri === null) {
        $uri = imagemParaDataUri(dirname(__DIR__, 2) . '/images/logo.png');
    }
    return $uri;
}
function normalizarTextoVoucher(string $texto): string
{
    $texto = str_replace(["\r\n", "\r"], "\n", $texto);
    $texto = preg_replace('/[ \t]+/', ' ', $texto) ?? $texto;
    $texto = preg_replace('/\n[ \t]+/', "\n", $texto) ?? $texto;
    return trim($texto);
}
function formatHorarioVoucher(string $hora, bool $forPdf = false): string
{
    $formatado = date('H:i', strtotime($hora));
    if ($forPdf) {
        return $formatado;
    }
    return '&gt;' . $formatado . '&lt;';
}
function carregarDadosVoucher(PDO $pdo, string $voucher): array
{
    $totalAdd = 0;
    $totalPago = 0;
    $descreverCredito = $pdo->prepare(
        "SELECT valuecredit as credito, `name` as forma, datacredit, firstname, lastname
         FROM `ct_createfaturacredit` cfc
         LEFT JOIN `ct_currentaccount` cc ON cfc.idaccountcurrent = cc.id
         LEFT JOIN ct_usuario u ON u.id = cfc.idusr
         WHERE `numbervoucher` = :numbervoucher AND valueagente = '0.00'"
    );
    $descreverCredito->execute([':numbervoucher' => $voucher]);
    $registroCredito = $descreverCredito->fetchAll(PDO::FETCH_CLASS);
    $dadosReserva = $pdo->prepare(
        "SELECT r.idservico, r.id, pax, em.logo, documento, dateinput, dateoutput,
                photoresident, c.fullname AS cliente, c.observacao,
                s.fullname AS `status`, r.horaap, u.firstname, u.lastname,
                se.fullname AS serivco, ag.fullname AS agente, priceadult,
                namepayment, g.fullname AS guia, qtdpax, qtdchild, qtdfree,
                ss.schedule, r.voo, pricechild, numbervoucher, r.valueservice,
                r.abertura, se.screenplay, roteiro, r.identificacao_mala,
                r.incluirtaxamala, r.qntpessoataxamala
         FROM `ct_reserva` r
         LEFT JOIN ct_cliente c ON c.id = r.idcliente
         LEFT JOIN ct_empresa em ON em.id = r.idempresa
         LEFT JOIN ct_usuario u ON u.id = r.idresponsavel
         LEFT JOIN ct_status s ON s.id = r.idstatus
         LEFT JOIN ct_guia g ON g.id = r.idguia
         JOIN ct_servico se ON se.id = r.idservico
         LEFT JOIN ct_agentes ag ON r.idagente = ag.id
         LEFT JOIN `ct_servico_horario` sr ON sr.idservice = r.idservico AND sr.idschedule = r.idhorario
         LEFT JOIN ct_service_schedule ss ON ss.idshedule = r.idhorario
         LEFT JOIN `ct_form_of_ payment` cfp ON cfp.id = r.idpayment
         WHERE `numbervoucher` = :numbervoucher"
    );
    $dadosReserva->execute([':numbervoucher' => $voucher]);
    $dadosGerais = $dadosReserva->fetch(PDO::FETCH_ASSOC);
    if (!$dadosGerais) {
        return [
            'dadosGerais' => null,
            'registro' => [],
            'registroCredito' => [],
            'total' => 0,
            'totalAdd' => 0,
            'totalPago' => 0,
            'timestamp2' => 0,
        ];
    }
    $total = ($dadosGerais['valueservice'] * $dadosGerais['qtdpax'])
        + (($dadosGerais['valueservice'] / 2) * $dadosGerais['qtdchild']);
    $timestamp2 = strtotime($dadosGerais['dateinput']);
    $adicionais = $pdo->prepare(
        "SELECT ra.idservice, ra.dateinput AS ap, s.fullname, s.screenplay,
                s.priceadult, s.pricechild, ss.schedule, qpax, qchild, qfree,
                ra.valueservice, ra.horaap, ra.documento, sr.roteiro
         FROM `ct_recentlyadd` ra
         LEFT JOIN `ct_reserva` r ON r.id = ra.idrecently
         LEFT JOIN `ct_servico_horario` sr ON sr.idservice = ra.idservice AND sr.idschedule = ra.idschedule
         LEFT JOIN ct_servico s ON s.id = ra.idservice
         LEFT JOIN ct_service_schedule ss ON ss.idshedule = ra.idschedule
         WHERE r.id = :id ORDER BY ap"
    );
    $adicionais->execute([':id' => $dadosGerais['id']]);
    $registro = $adicionais->fetchAll(PDO::FETCH_CLASS);
    return [
        'dadosGerais' => $dadosGerais,
        'registro' => $registro,
        'registroCredito' => $registroCredito,
        'total' => $total,
        'totalAdd' => $totalAdd,
        'totalPago' => $totalPago,
        'timestamp2' => $timestamp2,
    ];
}
function registrarAuditoriaVoucher(PDO $pdo, string $voucher, bool $folharosto, string $contexto = 'Impresso'): void
{
    $descricaoAudit = $folharosto ? 'Folha de Rosto ' . $contexto : 'Voucher ' . $contexto;
    $dadosAuditoria = $pdo->prepare(
        'INSERT INTO `ct_audit` (`id`,`idresponsible`,`voucher`,`description`,`date`)
         VALUES (DEFAULT,:idres,:vou,:descr,:dat)'
    );
    $dadosAuditoria->execute([
        ':idres' => $_SESSION['idresponsavel'] ?? null,
        ':vou' => $voucher,
        ':descr' => $descricaoAudit,
        ':dat' => date('Y-m-d H:i:s'),
    ]);
}
function renderVoucherHtml(array $ctx): string
{
    extract($ctx, EXTR_SKIP);
    if (!isset($forPdf)) {
        $forPdf = false;
    }
    if (!isset($autoPrint)) {
        $autoPrint = false;
    }
    if (!isset($folharosto)) {
        $folharosto = false;
    }
    if (!isset($titulo)) {
        $titulo = $folharosto ? 'Folha de Rosto' : 'Voucher';
    }
    $projectRoot = dirname(__DIR__, 2);
    $logoPadrao = $forPdf
        ? logoCassiDataUri()
        : '../.././images/logo.png';
    $logoLargura = 200;
    $logoAltura = 24;
    ob_start();
    include __DIR__ . '/../relatorio/templates/voucher-document.php';
    return (string) ob_get_clean();
}
function gerarVoucherPdf(PDO $pdo, string $voucher, bool $folharosto = false): array
{
    $ctx = carregarDadosVoucher($pdo, $voucher);
    if (empty($ctx['dadosGerais'])) {
        throw new RuntimeException('Voucher não encontrado.');
    }
    $ctx['folharosto'] = $folharosto;
    $ctx['titulo'] = $folharosto ? 'Folha de Rosto' : 'Voucher';
    $ctx['forPdf'] = true;
    $ctx['autoPrint'] = false;
    $html = renderVoucherHtml($ctx);
    require_once dirname(__DIR__) . '/relatorio/dompdf/src/Autoloader.php';
    Dompdf\Autoloader::register();
    $root = dirname(__DIR__, 2);
    $dompdf = new Dompdf\Dompdf();
    $dompdf->getOptions()->setChroot($root);
    $dompdf->getOptions()->setIsRemoteEnabled(true);
    $dompdf->getOptions()->setIsHtml5ParserEnabled(true);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $slug = preg_replace('/[^a-zA-Z0-9_-]/', '', $voucher);
    $filename = ($folharosto ? 'folha-de-rosto-' : 'voucher-') . $slug . '.pdf';
    $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filename;
    file_put_contents($path, $dompdf->output());
    return [
        'path' => $path,
        'filename' => $filename,
        'passageiro' => $ctx['dadosGerais']['pax'] ?? '',
        'titulo' => $ctx['titulo'],
    ];
}
