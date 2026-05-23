<?php
/**
 * Cache de dados de referência na sessão.
 * Evita repetir consultas a tabelas estáticas em cada carregamento de página.
 * TTL padrão: 30 minutos. Chame refCacheFlush() para forçar recarga.
 */

define('REF_CACHE_TTL', 1800); // 30 minutos

function _refLoad(PDO $pdo, string $key, string $sql, array $params = [], int $ttl = REF_CACHE_TTL): array
{
    $now = time();
    if (isset($_SESSION['_ref'][$key]) && $_SESSION['_ref'][$key]['exp'] > $now) {
        return $_SESSION['_ref'][$key]['data'];
    }
    $st = $pdo->prepare($sql);
    $st->execute($params);
    $data = $st->fetchAll(PDO::FETCH_OBJ);
    $_SESSION['_ref'][$key] = ['data' => $data, 'exp' => $now + $ttl];
    return $data;
}

/** Remove todo o cache de referência (útil ao salvar novos registros) */
function refCacheFlush(string $key = null): void
{
    if ($key !== null) {
        unset($_SESSION['_ref'][$key]);
    } else {
        unset($_SESSION['_ref']);
    }
}

function refGuias(PDO $pdo): array
{
    return _refLoad($pdo, 'guias', 'SELECT * FROM `ct_guia` ORDER BY fullname');
}

function refClientes(PDO $pdo): array
{
    return _refLoad($pdo, 'clientes', 'SELECT * FROM `ct_cliente` ORDER BY fullname');
}

function refEmpresas(PDO $pdo): array
{
    return _refLoad($pdo, 'empresas', "SELECT * FROM `ct_empresa` WHERE id IN (1,2,7) ORDER BY fullname");
}

function refEmpresasTodas(PDO $pdo): array
{
    return _refLoad($pdo, 'empresas_todas', 'SELECT * FROM `ct_empresa` ORDER BY fullname');
}

function refServicos(PDO $pdo): array
{
    return _refLoad($pdo, 'servicos', 'SELECT * FROM `ct_servico` ORDER BY fullname');
}

function refServicosOrdem(PDO $pdo): array
{
    return _refLoad($pdo, 'servicos_ordem', 'SELECT * FROM `ct_servico` ORDER BY ordem, fullname DESC');
}

function refPagamentos(PDO $pdo): array
{
    return _refLoad($pdo, 'pagamentos', 'SELECT * FROM `ct_form_of_ payment` ORDER BY namepayment');
}

function refAgentes(PDO $pdo): array
{
    return _refLoad($pdo, 'agentes', 'SELECT * FROM `ct_agentes` ORDER BY fullname');
}

function refHorarios(PDO $pdo): array
{
    return _refLoad($pdo, 'horarios', 'SELECT * FROM `ct_service_schedule` ORDER BY schedule');
}

function refUsuarios(PDO $pdo): array
{
    return _refLoad($pdo, 'usuarios', 'SELECT * FROM `ct_usuario` WHERE bloqueado = 0 ORDER BY firstname');
}

function refStatusInvoice(PDO $pdo): array
{
    return _refLoad($pdo, 'status_invoice', 'SELECT * FROM `ct_statusinvoice`');
}
function refFornecedores(PDO $pdo): array
{
    return _refLoad($pdo, 'fornecedores', 'SELECT * FROM `ct_fornecedor` ORDER BY fullname');
}
function refTipoCaixa(PDO $pdo): array
{
    return _refLoad($pdo, 'tipo_caixa', 'SELECT * FROM `ct_tipocaixa` ORDER BY name');
}
function refContaCorrente(PDO $pdo): array
{
    return _refLoad($pdo, 'conta_corrente', 'SELECT * FROM `ct_currentaccount` ORDER BY name');
}
function refPlanoContas(PDO $pdo): array
{
    return _refLoad($pdo, 'plano_contas', 'SELECT * FROM `ct_planaccounts` ORDER BY name');
}
