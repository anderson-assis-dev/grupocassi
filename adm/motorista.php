<?php
require_once('../config.php');

$find_driver = $pdo->prepare('SELECT `namedriver` as motorista FROM `ct_orderservice` GROUP by `namedriver`');
$find_driver->execute();
$data_find_driver = $find_driver->fetchAll(PDO::FETCH_CLASS);
echo( json_encode( $data_find_driver ) );