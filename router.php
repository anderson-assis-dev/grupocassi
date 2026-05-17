<?php
$path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
$file=__DIR__.$path;
if($path!=='/'&&is_file($file)){
return false;
}
if($path!=='/'&&is_file($file.'.php')){
$target=$file.'.php';
chdir(dirname($target));
require_once $target;
return true;
}
if($path==='/'&&is_file(__DIR__.'/index.php')){
$target=__DIR__.'/index.php';
chdir(dirname($target));
require_once $target;
return true;
}
return false;
