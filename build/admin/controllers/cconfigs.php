<?php

/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 05.09.2015
 *
 **/
class cconfigs  extends aController
{
    public function action_index()
    {
        $this->view->out("cconfigs","cconfigs");
    }

    /**
     * отображение общего списка настроек
     */
    public function action_getlist()
    {

        if(empty($_SESSION["mwccfgread"]))
        {
            $_SESSION["mwccfgread"] = "admin";
        }

        $dirInfo = scandir("build".DIRECTORY_SEPARATOR.$_SESSION["mwccfgread"].DIRECTORY_SEPARATOR."configs");

        $lpath = "build".DIRECTORY_SEPARATOR.$_SESSION["mwccfgread"].DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$_SESSION["mwclang"].DIRECTORY_SEPARATOR."cconfigsdesc.php";
        if(file_exists($lpath))
            require_once $lpath;
        else
            $lang = array();

        $adirInfo = new ArrayIterator($dirInfo);
        foreach ($adirInfo as $id=>$fname)
        {
            if($fname!="." && $fname!=".." && substr($fname,-4) == ".cfg")
            {
                $tm_ = substr($fname,0,-4);
                if(!empty($lang[$tm_]))
                    $tm__ = $lang[$tm_];
                else
                    $tm__="";

                $this->view
                    ->set(array("vRname"=>$tm_,"fn"=>$id,"vFname"=>$tm__))
                    ->out("c_cconfigs","cconfigs");
            }
        }
    }

    /**
     * Генерация конфига сайта
     */
    public function action_showcfg()
    {
        if(isset($_GET["get"]))
            $gt = (int)$_GET["get"];
        else
            return;

        $dirInfo = scandir("build".DIRECTORY_SEPARATOR.$_SESSION["mwccfgread"].DIRECTORY_SEPARATOR."configs");
        $adirInfo = new ArrayIterator($dirInfo);

        foreach ($adirInfo as $id=>$fname)
        {
            if($id == $gt)
            {
                $tm_ = substr($fname,0,-4);
                break;
            }
        }


        $this->view->set("fid",$tm_);//заранее ставим

        $cfg = Configs::readCfg($tm_,$_SESSION["mwccfgread"]);
        if(!is_array($cfg) or count($cfg)<1)
        {
            $this->view->out("cfg_main","cconfigs");
        }
        else
        {
            $acfg = new ArrayIterator($cfg);

            $lpath = "build".DIRECTORY_SEPARATOR.$_SESSION["mwccfgread"].DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$_SESSION["mwclang"].DIRECTORY_SEPARATOR."cfg_$tm_.php";
            if(file_exists($lpath))
            {
                require $lpath;
            }
            else
                $lang = array();



            foreach ($acfg as $id=>$v)
            {
                if($id=="snames")
                {
                    $v = implode(";",$v);
                }
                elseif($id=="db_host")
                {
                    $v = implode(";",$v);
                }
                elseif($id=="db_name")
                {
                    $v = implode(";",$v);
                }
                elseif($id=="db_user")
                {
                    $v = implode(";",$v);
                }
                elseif($id=="db_upwd")
                {
                    $v = implode(";",$v);
                }

                if(!empty($lang[$id]))
                    $id_ = "(".$lang[$id].")";
                else
                    $id_ = "";


                $this->view->set(array("tname"=>$id,
                    "vname"=>$v,
                    "mname"=>$id_))
                    ->out("c_cfg","cconfigs");
            }

            $this->view
                ->setFContainer("tbodys",true) //собранный кеш по настройкам суем в отведенное под него ключевое слово и вываливаем на жкран уже в общем
                ->out("cfg_main","cconfigs");
        }
    }

    /**
     * Применение изменения конфигов
     */
    public function action_apply()
    {
        if(!empty($_POST["id"]))
        {
            $cfgname = $_POST["id"];
            unset($_POST["id"]);
            $dirInfo = "build".DIRECTORY_SEPARATOR.$_SESSION["mwccfgread"].DIRECTORY_SEPARATOR."configs".DIRECTORY_SEPARATOR.$cfgname.".cfg";

            if(file_exists($dirInfo))
            {
                $ai = new ArrayIterator($_POST);
                $newCfg = array();

                foreach($ai as $id=>$v)
                {
                    if($id=="snames")
                    {
                        $v = explode(";",$v);
                    }
                    elseif($id=="db_host")
                    {
                        $v = explode(";",$v);
                    }
                    elseif($id=="db_name")
                    {
                        $v = explode(";",$v);
                    }
                    elseif($id=="db_user")
                    {
                        $v = explode(";",$v);
                    }
                    elseif($id=="db_upwd")
                    {
                        $v = explode(";",$v);
                    }
                    $newCfg[$id] = $v;
                }
                Configs::writeCfg($newCfg,$cfgname,$_SESSION["mwccfgread"]);
            }
        }
    }

    /**
     * удалить параметр из конфига
     */
    public function action_delpos()
    {
        if(!empty($_GET["pos"]) && !empty($_GET["c"]))
        {
            $cfgName = $_GET["c"];
            $params = $_GET["pos"];

            $cfg_ = Configs::readCfg($cfgName,$_SESSION["mwccfgread"]);

            if(isset($cfg_[$params]))
            {
                Tools::debug($cfg_[$params]);
                unset($cfg_[$params]);
                Configs::writeCfg($cfg_,$cfgName,$_SESSION["mwccfgread"]);
            }
        }
    }

    /**
     * форма добавления нового параметра
     */
    public function action_newparam()
    {
        if(isset($_GET["cid"]))
        {
            $param = $_GET["cid"];
            $this->view
                ->set("cid",$_GET["cid"])
                ->out("newParam","cconfigs");
        }
        else
            echo "no parameters o0";
    }

    /**
     * добавляем новый параметр
     */
    public function action_addparam()
    {
        if(!empty($_GET["cid"]) && !empty($_POST["pname"]) && isset($_POST["pval"])&& !empty($_POST["id"]))
        {
            $id_ = (int)$_GET["cid"];
            $name = $_POST["pname"];
            $val = $_POST["pval"];
            $cid = $_POST["id"];

            $dirInfo = scandir("build".DIRECTORY_SEPARATOR.$_SESSION["mwccfgread"].DIRECTORY_SEPARATOR."configs");
            $adirInfo = new ArrayIterator($dirInfo);

            foreach ($adirInfo as $id=>$fname)
            {
                if($id == $id_)
                {
                    $tm_ = substr($fname,0,-4);
                    break;
                }
            }

            if($tm_ == $cid)
            {
                $cfg = Configs::readCfg($tm_,$_SESSION["mwccfgread"]);
                if(!is_array($cfg))
                    $cfg = array();

                $cfg[$name]=$val;
                Configs::writeCfg($cfg,$tm_,$_SESSION["mwccfgread"]);
            }
        }
    }
}