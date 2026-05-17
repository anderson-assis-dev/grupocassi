<?php
require_once('adm/class/Hotel.php');
$hotel = new Hotel();

foreach ($hotel->todosHoteis() as $item)
{
    echo(strtoupper("MORRO DE SÃO PAULO x ".$item['nomehotel']." | "));
}