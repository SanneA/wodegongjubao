<?php
/**
 * MuWebCloneEngine
 * Created by epmak
 * version: 1.6.1
 * 19.09.2015
 * скрипт установки админки
 **/
session_start();
error_reporting(E_ALL);
function __autoload($name)
{
    if(file_exists("app".DIRECTORY_SEPARATOR."$name.php"))
    {
        require "app" . DIRECTORY_SEPARATOR .$name.".php"; //родительские классы
    }
}
define("tbuild","admin");

$ai = new ArrayIterator($_POST);

foreach ($ai as $id=>$v)
{
    if(is_array($v))
        continue;

    $v = trim(htmlspecialchars(preg_replace("!<script[^>]*>|</script>|<(\s{0,})iframe(\s{0,})>|</(\s{0,})iframe(\s{0,})>!isU",'!removed bad word!',$v),ENT_QUOTES));
    if(function_exists("get_magic_quotes_gpc"))
    {
        if (get_magic_quotes_gpc())
            $v = stripslashes($v);
        $v = str_replace('`','&quot;',$v);
    }

    $_POST[$id]=$v;
}


$list = explode("/",$_SERVER["PHP_SELF"]);
array_pop($list);
$gaddress ="http://".getenv("HTTP_HOST").implode("/",$list)."/";


$content = new content($gaddress,"install","ru");

if(!isset($_GET["st"]))
{
    $content->out("index","public",false);
}
else
{
    switch($_GET["st"])
    {
        //1 шаг. форма
        case 1:
            $dir = scandir("build");
            $list = array();

            foreach ($dir as $did =>$name)
            {
                if($name!=".htaccess" && $name!="." && $name!="..")
                {
                    $list[$did] = $name;
                }
            }

            $content
                ->set("admlist",Tools::htmlSelect($list,"dirselect",3,"style='width:173px'"))
                ->out("step1","public",false);
            break;
        //проверка коннекта
        case 2:
            try
            {
                if(!empty($_POST["conType"])
                    && !empty($_POST["db_host"])
                    // && !empty($_POST["db_name"])
                    && !empty($_POST["db_usr"])
                    && !empty($_POST["db_pwd"])
                )
                {
                    $conType = (int)$_POST["conType"];

                        $db = connect::start(
                            $conType,
                            $_POST["db_host"],
                            NULL,
                            $_POST["db_usr"],
                            $_POST["db_pwd"]
                        );
                    $_SESSION["installmwcct"] = $conType;
                    $_SESSION["installmwcdb_host"] = $_POST["db_host"];

                    if(!empty($_POST["adb_name"]))
                        $_SESSION["installamwcdb_name"] = $_POST["adb_name"];

                    $_SESSION["installmwcdb_usr"] = $_POST["db_usr"];
                    $_SESSION["installmwcdb_pwd"] = $_POST["db_pwd"];


                    echo '<input type="button" value="Next >" onclick=\'genIn({
                element:"maintebody",
                address:"'.$content->getAdr().'install.php?st=3",
                        loadicon:"loading..."
                        })\'> <b>Connection work -> may continue</b>';
                }
                else
                    echo '<input type="button" value="Next >" onclick=\'genIn({
                element:"checkConnect",
                address:"'.$content->getAdr().'install.php?st=2",
                type:"POST",
                        data:$("#mainform").serialize(),
                        loadicon:"Checking connecton..."
                        })\'> Please, check the entered data ';

            }
            catch (Exception $ex)
            {
                echo '<input type="button" value="Next >" onclick=\'genIn({
                element:"checkConnect",
                address:"'.$content->getAdr().'install.php?st=2",
                type:"POST",
                        data:$("#mainform").serialize(),
                        loadicon:"Checking connecton..."
                        })\'>'. $ex->getMessage();
            }
            break;

        //утановка таблиц
        case 3:
            echo "<tr><td colspan='2'> Adding tables in database.. <br>";
            try
            {
                 //если база не указана, то создаем отдельно
                    $db = connect::start(
                        $_SESSION["installmwcct"],
                        $_SESSION["installmwcdb_host"],
                        NULL,
                        $_SESSION["installmwcdb_usr"],
                        $_SESSION["installmwcdb_pwd"]
                    );
                    $_SESSION["installmwcdb_name"] = "mwce_settings";




                if($_SESSION["installmwcct"]>3)
                {
                    $query = file_get_contents("configs/mysql.sql");
                    $pref = "mysql";
                }
                else
                {
                    $pref = "mssql";
                    if(file_exists("configs/createmssql.sql"))
                    {
                        $db->query(file_get_contents("configs/createmssql.sql"));
                        $db->query("USE mwce_settings");
                    }
                    $query = file_get_contents("configs/mssql.sql");
                }

                $db->query($query);

                if(!empty($_SESSION["installamwcdb_name"]) && file_exists("configs/aditional_$pref.sql"))
                {
                    $db->query("USE {$_SESSION["installamwcdb_name"]};");
                    $db->query(file_get_contents("configs/aditional_$pref.sql"));
                }

            }
            catch (Exception $ex)
            {
                echo $ex->getMessage();
            }

            echo '<input type="button" value="Next >" onclick=\'genIn({
                element:"maintebody",
                address:"'.$content->getAdr().'install.php?st=4",
                type:"POST",
                data:$("#mainform").serialize(),
                        loadicon:"Loading..."
                        })\'></td></tr>';
            break;
        //заполнение данных одмина
        case 4:
            $content->out("step4","public",false);
            break;
        //создание админа, убийство инсталяшки
        case 5:
            try
            {
                $db = connect::start(
                    $_SESSION["installmwcct"],
                    $_SESSION["installmwcdb_host"],
                    $_SESSION["installmwcdb_name"],
                    $_SESSION["installmwcdb_usr"],
                    $_SESSION["installmwcdb_pwd"]
                );

                if(!empty($_POST["aname"]) && !empty($_POST["apw"]))
                {
                    $name = (substr($_POST["aname"],0,19));
                    $pwd = md5($_POST["apw"]);

                    $db->query("INSERT INTO mwc_admin (name,pwd,nick,sacc,access) VALUES ('$name','$pwd','$name','$name',1)");//одмин пошел!

                    $config = array(
                        "ctype" => $_SESSION["installmwcct"],
                        "address" => $gaddress,
                        "dlang" => "ru",
                        "theme" => "admin",
                        "db_host" => array($_SESSION["installmwcdb_host"]),
                        "db_name" => array($_SESSION["installmwcdb_name"]),
                        "db_user" => array($_SESSION["installmwcdb_usr"]),
                        "db_upwd" => array($_SESSION["installmwcdb_pwd"]),
                        "licecount" => 1,
                        "defgrp" => 2,
                        "defpage" => "admin",
                        "defController" => "aController",
                        "defModel" => "ausermodel"
                    );
                    $mainpath = "configs".DIRECTORY_SEPARATOR."configs.php";
                    if(file_exists($mainpath))
                        require_once $mainpath;
                    else
                    {
                        die("main cfg error!");
                    }

                    Configs::writeCfg($config,"main",$cfg["defaultabuild"]);
                    rename("install.php","configs/install.php"); //убираем модуль админки, юольше не нужен

                    //region for muonline
                    //admin
                    $config = array(
                        "ctype" => $_SESSION["installmwcct"],
                        "address" => $gaddress,
                        "dlang" => "ru",
                        "theme" => "muadmin",
                        "db_host" => array($_SESSION["installmwcdb_host"]),
                        "db_name" => array($_SESSION["installmwcdb_name"]),
                        "db_user" => array($_SESSION["installmwcdb_usr"]),
                        "db_upwd" => array($_SESSION["installmwcdb_pwd"]),
                        "licecount" => 1,
                        "defgrp" => 2,
                        "defpage" => "admin",
                        "defController" => "aController",
                        "defModel" => "ausermodel"
                    );
                    Configs::writeCfg($config,"main","muadmin");
                    //muonline site
                    $config = array(
                        "ctype" => $_SESSION["installmwcct"],
                        "address" => $gaddress,
                        "dlang" => "ru",
                        "theme" => "espada-legend",
                        "usemd5" =>0,
                        "db_host" => array($_SESSION["installmwcdb_host"]),
                        "db_name" => array($_SESSION["installmwcdb_name"]),
                        "db_user" => array($_SESSION["installmwcdb_usr"]),
                        "db_upwd" => array($_SESSION["installmwcdb_pwd"]),
                        "licecount" => 1,
                        "defgrp" => 2,
                        "defpage" => "news",
                        "tryCount" => 5,
                        "banMin" => 15,
                        "defController" => "muController",
                        "defModel" => "MuonlineUser"
                    );
                    Configs::writeCfg($config,"main","muonline");
                    //endregion
                    
                    session_unset();
                    session_destroy();

                    echo "<tr><td colspan='2' style='font-weight: bold;'>Congratulation! Core installation completed! Please, replace install.php from root directory, if it doesn't. We remind you, that was core install. Next step - build install.
 Core Admin-panel <a href='{$gaddress}control.php'>here</a></td></tr>";

                }
            }
            catch (Exception $ex)
            {
                echo $ex->getMessage();
            }
            break;
    }
}