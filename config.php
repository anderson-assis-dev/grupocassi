<?php
require_once __DIR__ . '/env.php';
loadEnv(__DIR__ . '/.env');

try {
    header('Content-Type: text/html; charset=utf-8');

    $dbHost    = env('DB_HOST', '127.0.0.1');
    $dbName    = env('DB_NAME');
    $dbUser    = env('DB_USER');
    $dbPass    = env('DB_PASS', '');
    $dbCharset = env('DB_CHARSET', 'utf8');

    $dsn = "mysql:host={$dbHost};dbname={$dbName};charset={$dbCharset}";
    $pdo = new PDO($dsn, $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    setlocale(LC_ALL, 'pt_BR', 'pt_BR.iso-8859-1', env('APP_LOCALE', 'pt_BR.utf-8'), 'portuguese');
    date_default_timezone_set(env('APP_TIMEZONE', 'America/Bahia'));

    session_start();
    error_reporting(1);
    ini_set('mysql.connect_timeout', 3000);
    ini_set('default_socket_timeout', 3000);
} catch (Exception $exception) {
    echo $exception->getMessage();
}
