<?php
/**
 * Created by PhpStorm.
 * User: Ander
 * Date: 27/05/2018
 * Time: 10:28
 */
require_once ( '.././config.php' );

unset( $_SESSION['idoperador'] );
unset( $_SESSION['id'] );
unset( $_SESSION['idresponsavel'] );
unset( $_SESSION['tempo'] );
unset( $_SESSION['img'] );
unset( $_SESSION['username'] );
unset( $_SESSION['email'] );
unset( $_SESSION['nome'] );
unset( $_SESSION['idreservamanager'] );
unset( $_SESSION['idmapservice'] );
unset( $_SESSION['idreservaplus'] );
unset( $_SESSION['idfaturador'] );
unset( $_SESSION['idgerente'] );
unset( $_SESSION['idbaixa'] );
unset( $_SESSION['idcaixa'] );
unset( $_SESSION['idpagarreserva'] );
unset( $_SESSION['idfinanceiro2'] );
unset( $_SESSION['comissao'] );
unset( $_SESSION['folhaderosto'] );
unset( $_SESSION['comissaorelatoriofolha'] );

session_unset();
session_destroy();

header( 'Location: .././index.php' );
exit();