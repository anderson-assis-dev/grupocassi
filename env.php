<?php
/**
 * Carregador minimalista de variaveis de ambiente.
 * Le um arquivo .env e popula $_ENV / getenv().
 * Nao sobrescreve variaveis ja definidas no ambiente real.
 */

if (!function_exists('loadEnv')) {
    function loadEnv(string $path): void
    {
        if (!is_readable($path)) {
            return;
        }
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') {
                continue;
            }
            if (strpos($line, '=') === false) {
                continue;
            }
            [$name, $value] = explode('=', $line, 2);
            $name  = trim($name);
            $value = trim($value);
            $len   = strlen($value);
            if ($len >= 2
                && (($value[0] === '"' && $value[$len - 1] === '"')
                    || ($value[0] === "'" && $value[$len - 1] === "'"))
            ) {
                $value = substr($value, 1, -1);
            }
            if (!array_key_exists($name, $_ENV)) {
                $_ENV[$name] = $value;
            }
            if (getenv($name) === false) {
                putenv("$name=$value");
            }
        }
    }
}

if (!function_exists('env')) {
    function env(string $key, $default = null)
    {
        $val = $_ENV[$key] ?? getenv($key);
        if ($val === false || $val === null || $val === '') {
            return $default;
        }
        if ($val === 'true' || $val === '(true)') {
            return true;
        }
        if ($val === 'false' || $val === '(false)') {
            return false;
        }
        if ($val === 'null' || $val === '(null)') {
            return null;
        }
        return $val;
    }
}
