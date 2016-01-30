<?php

class router
{
    private static $instanse = null;

    private function __construct()
    {
        /**
         * если не установлен билд, то сначала грузим главный конфиг. в нем читаем билд,
         * устанавливаем и далее вычитываем конфиг конкретного билда.
         */

        $mainpath = "configs".DIRECTORY_SEPARATOR."configs.php";
        if(file_exists($mainpath))
            require_once $mainpath;
        else
        {
            die("main cfg error!");
        }

        if(empty($_SESSION["mwcbuild"]))
        {
            $_SESSION["mwcbuild"] = $cfg["defaultbuild"];
        }

        define("tbuild",$_SESSION["mwcbuild"]);

        $globalcfg = Configs::readCfg("main",$_SESSION["mwcbuild"]);

        if(empty($_SESSION["mwcserver"])) //сервер
            $_SESSION["mwcserver"] = 0;

        if(empty($_SESSION["mwcbuild"])) //сервер
            $_SESSION["mwcbuild"] = $globalcfg["tbuild"];

        if(empty($_SESSION["mwclang"])) //язык
            $_SESSION["mwclang"] = $globalcfg["dlang"];

        if(empty($_SESSION["mwcpoints"])) //группа
            $_SESSION["mwcpoints"] = $globalcfg["defgrp"];

        $adres = $globalcfg["address"];

        try
        {
            $content = new content($adres,$globalcfg["theme"],$_SESSION["mwclang"]);
        }
        catch(Exception $e)
        {
            echo $e->getMessage();
            die();
        }

        $isBackground = (isset($_GET["bg"]))? 1:NULL; //если идет обращение к серверу

        try
        {
            $db = connect::start();
            if(empty($_SESSION["mwcuid"]))
                $uid = 0;
            else
                $uid = $_SESSION["mwcuid"];

            $builder = new builder(tbuild,$_SESSION["mwclang"],$_SESSION["mwcserver"]); // проверяем наличие списка модулей и плагинов

            //region плагины
            $plugin = "";
            require_once "build".DIRECTORY_SEPARATOR.tbuild.DIRECTORY_SEPARATOR."_dat".DIRECTORY_SEPARATOR.$_SESSION["mwcserver"]."_".$_SESSION["mwclang"]."_plugins.php";

            if(is_array($plugin) && is_null($isBackground)) //если в бекграунде, то плагины не включаем.
            {
                foreach ($plugin as $name=>$param)
                {
                    if($param["pstate"] == 1)//если плагин включен
                    {
                        $contoller_path = "build".DIRECTORY_SEPARATOR.tbuild.DIRECTORY_SEPARATOR."plugins".DIRECTORY_SEPARATOR."controller".DIRECTORY_SEPARATOR.$name.".php";

                        //region проверка на пользователя (если есть)
                        $ccfg = Configs::readCfg("plugin_".$name,$_SESSION["mwcbuild"]);
                        if(!empty($ccfg["allowedUsrs"]))
                        {
                            $usrs = explode(",",$ccfg["allowedUsrs"]);

                            if(!in_array($uid,$usrs))
                            {
                                $err = 2;
                            }
                            else
                                $err = 0;
                        }
                        else
                            $err = 2;
                        //endregion

                        if(file_exists($contoller_path) && (!empty($param["groups"]) || $err == 0))
                        {
                            if(empty($param["groups"]))
                                $paccess = array();
                            else
                                $paccess = explode(",",$param["groups"]);


                            try
                            {
                                if (!empty($param["mname"])) //если это MVC плагин
                                {
                                    $model_path = "build" . DIRECTORY_SEPARATOR . tbuild . DIRECTORY_SEPARATOR . "plugins" . DIRECTORY_SEPARATOR . "model" . DIRECTORY_SEPARATOR . $param["mname"] . ".php";

                                    if (file_exists($model_path)) //модель может и не лежать в папке, а быть стандартным для сборки классом в папке Inc.
                                    {
                                        require $model_path;
                                    }

                                    require $contoller_path;

                                    if ((in_array($_SESSION["mwcpoints"], $paccess) || in_array(4, $paccess) || $err == 0) && class_exists($param["mname"])) //если есть доступ к плагинам показываем
                                    {
                                        $tmp = $param["mname"];
                                        $model = new $tmp();
                                        $pcontoller = new $name($model, $content, $plugin, $_SESSION["mwcserver"]);

                                        if (method_exists($name, "action_index")) {
                                            $pcontoller->init();
                                            $pcontoller->action_index();
                                            $pcontoller->parentOut();
                                        }
                                    }
                                } else {
                                    if (in_array($_SESSION["mwcpoints"], $paccess) || in_array(4, $paccess)) //если есть доступ к плагинам показываем
                                    {
                                        $model = new $globalcfg["defModel"]();
                                        $pcontoller = new PController($model, $content, $plugin, $_SESSION["mwcserver"]);
                                        $pcontoller->genNonMVC($contoller_path);
                                        $pcontoller->parentOut($name);
                                    }
                                }
                            }
                            catch (Exception $e)
                            {
                                $content->error(1);
                                $content->setFContainer("plugin_$name",true);
                            }
                        }
                    }
                }
            }
            //endregion

            //region страницы

            //region получение контроллера и экшена
            $action_name = "action_index";

            if(!isset($_GET["p"]))
            {
                $controller = $globalcfg["defpage"];
            }
            else
            {
                $controller = htmlspecialchars($_GET["p"],ENT_QUOTES);

                if($controller == "index")
                    $controller = $globalcfg["defpage"];
            }

            if(isset($_GET["a"]))
            {
                $action_name = "action_".$_GET["a"];
            }
            //endregion

            require_once "build".DIRECTORY_SEPARATOR.tbuild.DIRECTORY_SEPARATOR."_dat".DIRECTORY_SEPARATOR.$_SESSION["mwcserver"]."_".$_SESSION["mwclang"]."_pages.php";

            if(isset($page[$controller])) //если есть такая страничка
            {
                $path = "build".DIRECTORY_SEPARATOR.tbuild.DIRECTORY_SEPARATOR.$page[$controller]["ppath"].DIRECTORY_SEPARATOR.$controller.".php";

                if(file_exists($path))
                {
                    $access = explode(",",$page[$controller]["groups"]);

                    //region проверка на пользователя (если есть)
                    $ccfg = Configs::readCfg($controller,$_SESSION["mwcbuild"]);


                    if(!empty($ccfg["allowedUsrs"]))
                    {
                        $usrs = explode(",", $ccfg["allowedUsrs"]);
                        if (!in_array($uid, $usrs))
                        {
                            $err = 2;
                        }
                        else
                            $err = 0;

                    }
                    else
                        $err = 2;
                    //endregion

                    try{
                        if(in_array($_SESSION["mwcpoints"],$access) ||  in_array(4,$access) || $err == 0 )//если пользователю дозволен вход и нет проблем с allowedUsrs
                        {

                            if(!empty($page[$controller]["mname"]) && !empty($page[$controller]["mpath"])) //если модуль является православным MVC
                            {
                                $modelpath = "build".DIRECTORY_SEPARATOR.tbuild.DIRECTORY_SEPARATOR.$page[$controller]["mpath"].DIRECTORY_SEPARATOR.$page[$controller]["mname"].".php";
                                require_once $path;

                                if(file_exists($modelpath))
                                {
                                    require_once $modelpath;
                                    $modelname = $page[$controller]["mname"];
                                }
                                else //если нет модели, то назначаем модель по умолчанию, пишем в лог что нету нифига, и плавно вываливаемся в ошибку
                                {
                                    $modelname="Model";
                                    $action_name = "showError";
                                    $db->SQLog("model $modelpath wasn't found","router",2);
                                }

                                $model = new $modelname($db);
                                $contolinst = new $controller($model,$content,$page,$_SESSION["mwcserver"]);
                                $contolinst->init();
                                if (method_exists($controller, $action_name))
                                {
                                    $contolinst->$action_name();
                                }
                                else //ежели нету действий, вызываем действие по умолчанию
                                {
                                    $contolinst->action_index();
                                    $db->SQLog("$controller hasn't action $action_name", "router", 3);
                                }
                                $contolinst->parentOut($isBackground);
                            }
                            else //если написан без ооп
                            {

                                $model = new $globalcfg["defModel"]();
                                $contolinst = new $globalcfg["defController"]($model,$content,$page,$_SESSION["mwcserver"]);
                                $contolinst->genNonMVC($path);
                                $contolinst->parentOut($isBackground);
                            }
                        }
                        else
                        {
                            $model = new Model($db);
                            $contolinst = new Controller($model,$content,$page,$_SESSION["mwcserver"]);
                            $contolinst->init();
                            $contolinst->showError(2);
                            $contolinst->parentOut($isBackground);
                            if(!empty($_SESSION["mwcuid"]))
                                $theGuy = $_SESSION["mwcuid"];
                            else
                                $theGuy = "?";

                            $db->SQLog("user($theGuy) try access to $controller but he hasn't access","router",6);
                        }
                    }
                    catch (Exception $e)
                    {
                        $model = new Model($db);
                        $contolinst = new Controller($model,$content,$page,$_SESSION["mwcserver"]);
                        $contolinst->init();
                        $contolinst->showErrorText($e->getMessage());
                        $contolinst->parentOut($isBackground);
                    }

                }
                else //нет нужной страницы
                {
                    $model = new Model($db);
                    $contolinst = new Controller($model,$content,$page,$_SESSION["mwcserver"]);
                    $contolinst->init();
                    $contolinst->showError(3);
                    $contolinst->parentOut($isBackground);
                    $db->SQLog("controller $path ($controller) wasn't found","router",1,true);
                }
            }
            else
            {
                $model = new Model($db);
                $contolinst = new Controller($model,$content,$page,$_SESSION["mwcserver"]);
                $contolinst->init();
                $contolinst->showError(3);
                $contolinst->parentOut($isBackground);
                $db->SQLog("controller $controller wasn't register","router",1);
            }
            //endregion
        }
        catch (Exception $ex)
        {
            $stack = $ex->getTrace();
            $msg = $ex->getMessage()." in file: ".basename($stack[3]["file"])." line: ".$stack[3]["line"];
            self::addlog(tbuild."_error",$msg,"log");
            content::showError("Something went wrong","please, check logs.");
        }
    }

    /**
     * обычные txt логи
     *
     * @param string $title - заголовок
     * @param string $content - текст лога
     * @param string $path - куда сохранять
     */
    public static function addlog($title="not_response",$content="Oops, no data?",$path = "log")
    {
        if (!is_dir($path))
        {
            mkdir("log");
            $path = "log";
        }

        $filename = $path.'/['.@date("d_m_Y", time()).']'.$title.'.txt';

        if($handle = fopen($filename, 'a+'))
        {
            if (isset($_SESSION["mwcuser"]))
                $usr = " auth user:".$_SESSION["mwcuser"];
            else
                $usr = "";

            if (fwrite($handle, "[".@date("H:i:s", time())."] ".trim($content)." $usr \r\n IP: ".getenv("REMOTE_ADDR").",  addres: ".$_SERVER['QUERY_STRING']." from: ".getenv('HTTP_REFERER')."  browser: {$_SERVER['HTTP_USER_AGENT']} \r\n") === FALSE) fclose($handle);
        }
    }

    public static function start()
    {
        if(is_null(self::$instanse))
            self::$instanse = new router();
    }
}