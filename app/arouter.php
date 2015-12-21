<?php

/**
 * Class arouter
 *
 * Точка входа для админки
 */
class arouter
{
    private static $instanse = null;

    private function __construct()
    {
        $mainpath = "configs".DIRECTORY_SEPARATOR."configs.php";
        if(file_exists($mainpath))
            require_once $mainpath;
        else
        {
            die("main cfg error!");
        }


        $globalcfg = Configs::readCfg("main",$cfg["defaultabuild"]);

        if(empty($_SESSION["mwcabuild"])) //билд админа по умолчанию, если есть иной - то не трогаем.
        {
            $_SESSION["mwcabuild"] = $cfg["defaultabuild"];
        }

        define("tbuild",$cfg["defaultabuild"]);
        //$baseDir = substr(dirname( __FILE__ ),0,-4);

        if(empty($_SESSION["mwcserver"])) //сервер
            $_SESSION["mwcserver"] = 0;

        if(empty($_SESSION["mwclang"])) //язык
            $_SESSION["mwclang"] = $globalcfg["dlang"];

        if(empty($_SESSION["mwcapoints"])) //группа в админке
            $_SESSION["mwcapoints"] = $globalcfg["defgrp"];

        $adres = $globalcfg["address"];

        //для редактирования билдов (подгрузка всего и вся (настроек)
        if(isset($_POST["whosconfig"]))
        {
            $builds = Tools::getAllBuilds();
            if(!empty($builds[$_POST["whosconfig"]]))
                $_SESSION["mwccfgread"] = $_POST["whosconfig"];
        }
        else
        {
            if(empty($_SESSION["whosconfig"]))
                $_SESSION["whosconfig"] = "admin";
        }

        $content = new content($adres,$globalcfg["theme"],$_SESSION["mwclang"]);
        $isBackground = (isset($_GET["bg"]))? 1:NULL; //если идет обращение к серверу

        try
        {
            $db = connect::start();
            $builder = new builder(tbuild,$_SESSION["mwclang"],$_SESSION["mwcserver"]); // проверяем наличие списка модулей и плагинов
            $action_name = "action_index";

            //region плагины
            $plugin = "";
            require_once "build".DIRECTORY_SEPARATOR.tbuild.DIRECTORY_SEPARATOR."_dat".DIRECTORY_SEPARATOR.$_SESSION["mwcserver"]."_".$_SESSION["mwclang"]."_plugins.php";

            if(is_array($plugin) && is_null($isBackground))
            {
                $aplugin = new ArrayIterator($plugin);
                foreach ($aplugin as $name=>$param)
                {
                    if($param["pstate"] == 2)//если плагин включен и для админки
                    {
                        $contoller_path = "build".DIRECTORY_SEPARATOR.tbuild.DIRECTORY_SEPARATOR."plugins".DIRECTORY_SEPARATOR."controller".DIRECTORY_SEPARATOR.$name.".php";
                        if(file_exists($contoller_path) && !empty($param["groups"]))
                        {

                            $paccess = explode(",",$param["groups"]);

                            if(!empty($param["mname"])) //если это MVC плагин
                            {
                                $model_path = "build".DIRECTORY_SEPARATOR.tbuild.DIRECTORY_SEPARATOR."plugins".DIRECTORY_SEPARATOR."model".DIRECTORY_SEPARATOR.$param["mname"].".php";
                                if(file_exists($model_path))
                                {
                                    require $model_path;
                                }

                                require $contoller_path;

                                if(in_array($_SESSION["mwcapoints"],$paccess) || in_array(4,$paccess) && class_exists($param["mname"])) //если есть доступ к плагинам показываем
                                {
                                    $tmp = $param["mname"];
                                    $model = new $tmp();
                                    $pcontoller = new $name($model,$content,$plugin,$_SESSION["mwcserver"]);

                                    if(method_exists($name,"action_index"))
                                    {
                                        $pcontoller->init();
                                        $pcontoller->action_index();
                                        $pcontoller->parentOut();
                                    }
                                }

                            }
                            else
                            {
                                if(in_array($_SESSION["mwcapoints"],$paccess) || in_array(4,$paccess)) //если есть доступ к плагинам показываем
                                {
                                    $model = new $globalcfg["defModel"]();
                                    $pcontoller = new PController($model,$content,$plugin,$_SESSION["mwcserver"]);
                                    $pcontoller->genNonMVC($contoller_path);
                                    $pcontoller->parentOut($name);
                                }
                            }
                        }
                    }
                }
            }
            //endregion


            //region страницы

            //region получение контроллера и экшена

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
                $action_name = "action_".strtolower($_GET["a"]);
            }
            //endregion

            require_once "build".DIRECTORY_SEPARATOR.tbuild.DIRECTORY_SEPARATOR."_dat".DIRECTORY_SEPARATOR.$_SESSION["mwcserver"]."_".$_SESSION["mwclang"]."_pages.php";
            if(empty($page))
                $page = array();

            if(isset($page[$controller])) //если есть такая страничка
            {
                $path = "build".DIRECTORY_SEPARATOR.tbuild.DIRECTORY_SEPARATOR.$page[$controller]["ppath"].DIRECTORY_SEPARATOR.$controller.".php";
                if(file_exists($path))
                {
                    $access = explode(",",$page[$controller]["groups"]);

                    if(in_array($_SESSION["mwcapoints"],$access) || $_SESSION["mwcapoints"] == 1)//если пользователю дозволен вход или это главные админы
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
                                $db->SQLog("model $modelpath wasn't found","arouter",3);
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
                                $db->SQLog("action $action_name wasn't found for $controller", "arouter", 3);
                            }

                            $contolinst->parentOut($isBackground);
                        }
                        else //если написан без ооп
                        {
                            $model = new $globalcfg["defModel"]();
                            $contolinst = new $globalcfg["defController"]($model,$content,$page,$_SESSION["mwcserver"]);

                            if (method_exists($contolinst, $action_name) && $action_name!="action_index" && (in_array($_SESSION["mwcapoints"],$access) || $_SESSION["mwcapoints"] == 1))
                                $contolinst->$action_name();
                            else
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
                        if(!empty($_SESSION["mwcauid"]))
                            $theGuy = $_SESSION["mwcauid"];

                        $db->SQLog("user ($theGuy) try access to $controller but he hasn't access","arouter",6);
                    }
                }
                else
                {
                    $model = new Model();
                    $contolinst = new Controller($model,$content,$page,$_SESSION["mwcserver"]);
                    $contolinst->init();
                    $contolinst->showError(3);
                    $contolinst->parentOut($isBackground);
                    $db->SQLog("controller '$path' wasn't found","arouter",1);
                }
            }
            else
            {
                $model = new Model();
                $contolinst = new Controller($model,$content,$page,$_SESSION["mwcserver"]);
                $contolinst->init();
                $contolinst->showError(3);
                $contolinst->parentOut($isBackground);

                $db->SQLog("controller '$controller' does't register in system","arouter",1);
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

    /**
     * точка входа singleton
     */
    public static function start()
    {
        if(is_null(self::$instanse))
            self::$instanse = new arouter();
    }
}