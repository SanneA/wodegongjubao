<?php

/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 10.09.2015
 *
 **/
class ammanager extends aController
{

    public function action_index()
    {
        $this->view
            ->set("folderlist",Tools::htmlSelect($this->model->getFolerList(),"selfolderc",0,"onchange='amfilter()'"))
            ->out("main","ammanager");
    }

    /**
     * отобразить лист со страницами
     */
    public function action_plist()
    {
        $folder = !empty($_GET["f"]) ? substr($_GET["f"],0,20) : NULL;

        $pages = $this->model->getPageList($folder);


        $path = "build".DIRECTORY_SEPARATOR.$_SESSION["mwccfgread"].DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$_SESSION["mwclang"].DIRECTORY_SEPARATOR."titles.php";

        if(file_exists($path))
        {
            require $path;
        }
        else
            $lang = array();


        $ai = new ArrayIterator($pages);
        foreach ($ai as $id=>$array)
        {

            if(!empty($lang[$array["ptitle"]]))
                $array["ptitle"] = $lang[$array["ptitle"]];

            if($array["ison"] == 1)
                $this->view->set("swcolor","color:green");
            else
                $this->view->set("swcolor","color:red");

            $this->view
                ->add_dict($array)
                ->out("center","ammanager");

        }
    }

    public function action_info()
    {
        if(!empty($_GET["id"]))
        {
            $pid = (int)$_GET["id"];
            $pinfo = $this->model->knowInfo($pid);

            $path = "build".DIRECTORY_SEPARATOR.$_SESSION["mwccfgread"].DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$_SESSION["mwclang"].DIRECTORY_SEPARATOR."titles.php";

            if(file_exists($path))
            {
                require $path;

                $this->view->set("llist",Tools::htmlSelect($lang,"pagetitle",$pinfo["ptitle"],"style='width:182px;'"));
            }

            $path = "build".DIRECTORY_SEPARATOR.$_SESSION["mwccfgread"].DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$_SESSION["mwclang"].DIRECTORY_SEPARATOR."group.php";

            if(file_exists($path))
            {
                require $path;
            }


            $groups = $this->model->getGroups($pid);
            $groups[0] = "...";
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

            $this->view
                ->add_dict($pinfo)
                ->set("statelist",Tools::htmlSelect(array(0=>"Off",1=>"On"),"statepage",$pinfo["ison"]))
                ->set("grplist",Tools::htmlSelect($argroup,"newgroup",0,"style='width:100px;' onchange=\"addToPage(this,$pid)\""))
                ->out("infoform","ammanager");
        }
    }

    /**
     * сохранение данных по странице
     */
    public function action_applyinfo()
    {
        if(!empty($_GET["id"]))
        {
            if(!empty($_POST["pagetitle"]))
                $ptitle = $_POST["pagetitle"];
            else
                return;

            if(!empty($_POST["mname"]))
                $mname = $_POST["mname"];
            else
                return;

            if(!empty($_POST["ppath"]))
                $ppath = $_POST["ppath"];
            else
                return;

            if(!empty($_POST["mmname"]))
                $mmname = $_POST["mmname"];
            else
                $mmname = NULL;

            if(!empty($_POST["mpath"]))
                $mpath = $_POST["mpath"];
            else
                $mpath = NULL;

            if(!empty($_POST["cache"]))
                $cache = $_POST["cache"];
            else
                $cache = 0;

            $ison = (int)$_POST["statepage"];
            if($ison > 1)
                $ison = 1;

            $this->model->applyPage((int)$_GET["id"],$ptitle,$mname,$ppath,$mmname,$mpath,$cache,$ison);
        }

    }

    /**
     * генерация списка с группами, у которых есть доступ к странице
     */
    public function action_getinlist()
    {
        if(!empty($_GET["id"]))
        {
            $pid = (int)$_GET["id"];
            $gurops = $this->model->getInGroups($pid);
            $path = "build".DIRECTORY_SEPARATOR.$_SESSION["mwccfgread"].DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$_SESSION["mwclang"].DIRECTORY_SEPARATOR."group.php";

            if(file_exists($path))
            {
                require $path;
            }
            else
                $lang = array();


            $ai = new ArrayIterator($gurops);
            foreach ($ai as $id=>$val)
            {
                if(!empty($lang[$val]))
                    $gname = $lang[$val];
                else
                    $gname = $val;


                $this->view
                    ->set(array("g_name"=>$gname,"gp"=>$id,"id"=>$pid))
                    ->out("inlistgroup","ammanager");
            }
        }
    }

    /**
     * удалить группу со страниц
     */
    public function action_deletegrp()
    {
        if(!empty($_GET["id"]) && !empty($_GET["gp"]))
        {
            $this->model->delGroup((int)$_GET["id"],(int)$_GET["gp"]);
        }
    }

    public function action_addgrp()
    {
        if(!empty($_GET["id"]) && !empty($_GET["gp"]))
        {
            $this->model->setInAccess((int)$_GET["id"],(int)$_GET["gp"]);
        }
    }

    /**
     * заполнение добавления страниц
     */
    public function action_step1()
    {
        $path = "build".DIRECTORY_SEPARATOR.$_SESSION["mwccfgread"].DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$_SESSION["mwclang"].DIRECTORY_SEPARATOR."titles.php";


        if(file_exists($path))
        {
            require $path;

            if(!empty($lang))
            {
                $ar = $lang;
                $ar[-1] = "...";
                $this->view->set("llist",Tools::htmlSelect($ar,"pagetitle",-1,"style='width:100px;'"));
            }
        }

        $path = "build".DIRECTORY_SEPARATOR.$_SESSION["mwccfgread"].DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$_SESSION["mwclang"].DIRECTORY_SEPARATOR."group.php";

        if(file_exists($path))
        {
            unset($lang);
            require $path;

            $this->view->set("groupsel",Tools::htmlSelect($lang,"groupz[]",-1,"multiple size='5'"));
        }
        $this->view->out("step1","ammanager");
    }

    /**
     * реакция на нажатую кнопку длаее степа1
     */
    public function action_step2()
    {
        if(!empty($_POST["cname"]))
            $cname = "'".$_POST["cname"]."'";
        else
            return;

        if(!empty($_POST["fname"]))
            $fname = "'".$_POST["fname"]."'";
        else
            return;

        $mname = !empty($_POST["mname"]) ? "'{$_POST["mname"]}'" : "NULL";
        $fmname = !empty($_POST["fmname"]) ? "'{$_POST["fmname"]}'" : "NULL";
        $cache = !empty($_POST["cache"]) ? (int)$_POST["cache"] : 0;

        if(!empty($_POST["title_n"])) //если указано имя на живую, то игнорируем выбранное, и забиваем в язык новое
        {
            $path = "build".DIRECTORY_SEPARATOR.$_SESSION["mwccfgread"].DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$_SESSION["mwclang"].DIRECTORY_SEPARATOR."titles.php";
            if (file_exists($path))
                require $path;
            else
                $lang = array();


            $i=1;
            while(!empty($lang["auto_title".$i]))
            {
                $i++;
            }

            $lang["auto_title".$i] = substr($_POST["title_n"],0,50);

            $ai = new ArrayIterator($lang);
            $content='<?php ';

            foreach ($ai as $id => $val)
            {
                $content.='$lang["'.$id.'"]="'.$val.'"; ';
            }

            $fh = fopen($path,"w");
            fwrite($fh,$content);
            fclose($fh);
            $title_name = "'auto_title$i'";
        }
        else
        {
            if(!empty($_POST["pagetitle"]) && $_POST["pagetitle"]!="-1")
                $title_name = "'".$_POST["pagetitle"]."'";
            else
                return;
        }

        $lid = $this->model->addNewPage($cname,$fname,$title_name,$mname,$fmname,$cache); //добавляем новую страницу и получаем ее ид

        if(!empty($_POST["groupz"]))
        {
            $grps = "";
            foreach ($_POST["groupz"] as $id=>$val)
            {
                if(!empty($grps))
                    $grps.=",";

                $grps.="'$val'";
            }

            if(!empty($lid) && $lid>0)
                $this->model->setInGroupAccess($lid,$grps); //добавление разрешений
        }
    }

    public function action_delpage()
    {
        if(!empty($_GET["id"]))
        {
            $this->model->delPage((int)$_GET["id"]);
        }
    }

    public function action_allclear()
    {
        $path = "build".DIRECTORY_SEPARATOR.$_SESSION["mwccfgread"].DIRECTORY_SEPARATOR."_dat".DIRECTORY_SEPARATOR.$_SESSION["mwcserver"]."_".$_SESSION["mwclang"]."_pages.php";
        if(file_exists($path))
            unlink($path);
    }
}