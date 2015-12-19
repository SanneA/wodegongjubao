<?php

/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 14.09.2015
 * управление плагинами
 **/
class apman extends aController
{

    public function action_index()
    {
        $this->view
            ->set("nrlist",html_::select($this->model->getNonRegPlugins(),"newplgns"))
            ->out("main","apman");
    }

    /**
     * список зарегистрированных плагинов
     */
    public function action_getlist()
    {
        $plugin = $this->model->getlist();

        $ai = new ArrayIterator($plugin);

        foreach ($ai as $id=>$val)
        {
            switch($val["pstate"])
            {
                case 0: $this->view->set("rowstyle","color:red");  break;
                case 1: $this->view->set("rowstyle","color:green");  break;
                case 2: $this->view->set("rowstyle","color:darkgreen");  break;
                case 3: $this->view->set("rowstyle","color:green;font-weight:bold;");  break;
                default:$this->view->set("rowstyle","");  break;
            }

            $val["pstate"] = $this->view->getVal("state".$val["pstate"]);
            $this->view
                ->set($val)
                ->out("center","apman");
        }
    }

    /**
     * форма редактирования плагина
     */
    public function action_info()
    {
        if(!empty($_GET["id"]))
        {
            $idp = (int)$_GET["id"];

            $info = $this->model->getInfo($idp);
            $stateAr = array(
                0 => $this->view->getVal("state0"),
                1 => $this->view->getVal("state1"),
                2 => $this->view->getVal("state2"),
                3 => $this->view->getVal("state3"),
            );

            $path = "build".DIRECTORY_SEPARATOR.$_SESSION["mwcabuild"].DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$_SESSION["mwclang"].DIRECTORY_SEPARATOR."group.php";

            if(file_exists($path))
            {
                require $path;
            }

            $groups = $this->model->getGroups($idp);
            $ai = new ArrayIterator($groups);
            $argroup = array();
            foreach ($ai as $id=>$val)
            {
                if(!empty($lang[$val]))
                    $_name = $lang[$val];
                else
                    $_name = $val;

                $argroup[$id] = $_name;
            }

            $argroup[0] = "...";
            $this->view
                ->add_dict($info)
                ->set("grplist",html_::select($argroup,"newgroup",0,"style='width:100px;' onchange=\"addToPlugin(this,$idp)\""))
                ->set("statelist",html_::select($stateAr,"chosedstate",$info["pstate"],"style='width:100px;'"))
                ->out("editform","apman");
        }
    }


    /**
     * добавить новый плагин
     */
    public function action_register()
    {
        if(!empty($_GET["newplgns"]))
        {
            $this->model->addPlugin(substr($_GET["newplgns"],0,50));
        }
    }

    /**
     * применить изменения плагинов
     */
    public function action_applyinfo()
    {
        if(!empty($_GET["id"]) && !empty($_POST["pname"]))
        {
            $pid = (int)$_GET["id"];
            $plugin = substr($_POST["pname"],0,50);
            $cache = (int)$_POST["pcache"];
            $state = (int)$_POST["chosedstate"];
            $seq = (int)$_POST["seq"];
            $model = !empty($_POST["mname"]) ? "'".substr($_POST["mname"],0,50)."'" : "''";

            $this->model->applyPlugin($pid,$plugin,$cache,$state,$model,$seq);
        }
    }


    /**
     * добавить группу доступа к плагину
     */
    public function action_addgrp()
    {
        if(!empty($_GET["id"]) && !empty($_GET["gp"]))
        {
            $this->model->setInAccess((int)$_GET["id"],(int)$_GET["gp"]);
        }
    }


    /**
     * показать группы для плагинов
     */
    public function action_showgroup()
    {
        if(!empty($_GET["id"]))
        {
            $plid = (int)$_GET["id"];
            $groups = $this->model->getInGroups($plid);
            $path = "build".DIRECTORY_SEPARATOR.$_SESSION["mwcabuild"].DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$_SESSION["mwclang"].DIRECTORY_SEPARATOR."group.php";

            if(file_exists($path))
            {
                require $path;
            }
            else
                $lang = array();

            $ai = new ArrayIterator($groups);
            foreach ($ai as $id=>$val)
            {
                if(!empty($lang[$val]))
                    $gname = $lang[$val];
                else
                    $gname = $val;


                $this->view
                    ->set(array("g_name"=>$gname,"gp"=>$id,"id"=>$plid))
                    ->out("inlistgroup","apman");
            }
        }
    }

    /**
     * удаление группы с плагина
     */
    public function action_deletegrp()
    {
        if(!empty($_GET["id"]) && !empty($_GET["gp"]))
        {
            $id = (int)$_GET["id"];
            $gp = (int)$_GET["gp"];
            $this->model->delGroup($id,$gp);
        }
    }

    public function action_delplugin()
    {
        if(!empty($_GET["id"]))
        {
            $this->model->delPlugin((int)$_GET["id"]);
        }
    }

    public function action_clearcache()
    {
        $ppath = "build".DIRECTORY_SEPARATOR.$_SESSION["mwccfgread"].DIRECTORY_SEPARATOR."_dat".DIRECTORY_SEPARATOR.$_SESSION["mwcserver"]."_".$_SESSION["mwclang"]."_plugins.php";
        if(file_exists($ppath))
        {
            unlink($ppath);
        }
    }
}