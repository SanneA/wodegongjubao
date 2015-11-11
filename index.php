<?php session_start();
/**
 * MuWebCloneEngine
 * 1.6.2
 * coded by epmak
 * 24.08.2015
 * ->
 **/
error_reporting(E_ALL);
header('Content-Type: text/html; charset=utf-8');
ob_start();
$start_time = microtime();

function __autoload($name)
{
    if(file_exists("app".DIRECTORY_SEPARATOR."$name.php"))
    {
        require "app".DIRECTORY_SEPARATOR."$name.php"; //родительские классы
    }
    else if(file_exists("build".DIRECTORY_SEPARATOR.tbuild.DIRECTORY_SEPARATOR."inc".DIRECTORY_SEPARATOR."$name.php"))
    {
        require "build".DIRECTORY_SEPARATOR.tbuild.DIRECTORY_SEPARATOR."inc".DIRECTORY_SEPARATOR."$name.php"; //классы-расширения билдов
    }
}

router::start();

echo "<!--".round(microtime()-$start_time,4)."-->";
//todo:: spl_autoload_register
