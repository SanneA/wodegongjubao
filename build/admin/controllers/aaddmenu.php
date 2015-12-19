<?php

/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 16.09.2015
 * управление данными меню в базе
 **/
class aaddmenu extends aController
{
    public function action_index()
    {
        $this->view
            ->set("mtypelist",html_::select($this->model->getMenuList(),"menutype",0,"onchange='filter(this.value);currentmenu=this.value;'"))
            ->out("main","aaddmenu");
    }

    /**
     * список позиций меню
     */
    public function action_getinmenu()
    {
        if(!empty($_GET["id"]))
        {
            $id = (int)$_GET["id"];
            $list = $this->model->getMtitles($id);


            $lpath = "build".DIRECTORY_SEPARATOR.$_SESSION["mwccfgread"].DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$_SESSION["mwclang"].DIRECTORY_SEPARATOR."titles.php";
            if (file_exists($lpath))
                require $lpath;
            else
                $lang = array();

            $iMenu = new ArrayIterator($list);

            foreach ($iMenu as $num =>$arr )
            {

                if(!empty($lang[$arr["mtitle"]]))
                    $arr["mltitle"]=$lang[$arr["mtitle"]]." <b>|</b> ";
                else
                    $arr["mltitle"]="";

                $this->view
                    ->add_dict($arr)
                    ->set("id",$num)
                    ->out("center","aaddmenu");
            }
        }
    }

    /**
     * форма добавления нового меню
     */
    public function action_addmenuform()
    {
        $this->view->out("admenuForm","aaddmenu");
    }

    /**
     * добавить новое меню
     */
    public function action_applemenu()
    {
        if(!empty($_POST["newmenu"]))
        {
            $this->model->newMenu(substr($_POST["newmenu"],0,50));
        }
    }

    /**
     * удалить меню
     */
    public function action_delmenu()
    {
        if(!empty($_GET["id"]))
        {
            $this->model->delMenu((int)$_GET["id"]);
        }

    }

    /**
     * форма редактирования позиции в меню
     */
    public function action_infoperp()
    {
        if(!empty($_GET["pid"]))
        {
            $ar = $this->model->getInfoPerPage((int)$_GET["pid"]);

            $lpath = "build".DIRECTORY_SEPARATOR.$_SESSION["mwccfgread"].DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$_SESSION["mwclang"].DIRECTORY_SEPARATOR."titles.php";
            if (file_exists($lpath))
                require $lpath;
            else
                $lang = array();

            $this->view
                ->add_dict($ar)
                ->set("mtypelist",html_::select($this->model->getMenuList(),"typemenu",$ar["mtype"]))
                ->set("titlest",html_::select($lang,"mtitel",$ar["mtitle"]))
                ->set("modullist",html_::select($this->model->pageList(),"pagesList",$ar["modul"],"onchange='getlink();'"))
                ->out("editPosition","aaddmenu");
        }
    }

    /**
     * форма добавления позиции в меню
     */
    public function action_addp()
    {
        if(!empty($_GET["pid"]))
        {
            $tmenu = (int)$_GET["pid"];

            $lpath = "build".DIRECTORY_SEPARATOR.$_SESSION["mwccfgread"].DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$_SESSION["mwclang"].DIRECTORY_SEPARATOR."titles.php";
            if (file_exists($lpath))
                require $lpath;
            else
                $lang = array();

            $this->view
                ->set("mtypelist",html_::select($this->model->getMenuList(),"typemenu",$tmenu))
                ->set("titlest",html_::select($lang,"mtitel",1))
                ->set("modullist",html_::select($this->model->pageList(),"pagesList",-1,"onchange='getlink();'"))
                ->out("addPosition","aaddmenu");
        }
    }

    public function action_aplympos()
    {
        if(!empty($_GET["pid"]))
        {
            $position = (int)$_GET["pid"];

            if(empty($_POST["mtitel"]))
                return;
            else
                $mtytle = $_POST["mtitel"];

            if(empty($_POST["typemenu"]))
                return;
            else
                $mtype = (int)$_POST["typemenu"];

            if(empty($_POST["linkadr"]))
                return;
            else
                $link = $_POST["linkadr"];

            if(empty($_POST["pagesList"]))
                return;
            else
                $modul = $_POST["pagesList"];

            $sequence  = isset($_POST["seq"]) ? (int)$_POST["seq"] : 0;

            $this->model->editToMenu($mtytle,$mtype,$link,$modul,$position,$sequence);
        }
    }

    /**
     * применяем добавленнок
     */
    public function action_applyadd()
    {
        if(empty($_POST["mtitel"]))
                return;
            else
                $mtytle = $_POST["mtitel"];

            if(empty($_POST["typemenu"]))
                return;
            else
                $mtype = (int)$_POST["typemenu"];

            if(empty($_POST["linkadr"]))
                return;
            else
                $link = $_POST["linkadr"];

            if(empty($_POST["pagesList"]))
                return;
            else
                $modul = $_POST["pagesList"];

            $sequence  = isset($_POST["seq"]) ? (int)$_POST["seq"] : 0;

            if(!empty($_POST["newtitle"])) //если введен новый татйтл
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

                $lang["auto_title".$i] = substr($_POST["newtitle"],0,50);

                $ai = new ArrayIterator($lang);
                $content='<?php ';

                foreach ($ai as $id => $val)
                {
                    $content.='$lang["'.$id.'"]="'.$val.'"; ';
                }

                $fh = fopen($path,"w");
                fwrite($fh,$content);
                fclose($fh);
                $mtytle = "auto_title$i";
            }
            $this->model->addToMenu($mtytle,$mtype,$link,$modul,$sequence);
    }

    /**
     * удаление позиции в меню
     */
    public function action_delmpos()
    {
        if(!empty($_GET["id"]))
        {
            $this->model->delMpos((int)$_GET["id"]);
        }
    }

    public function action_delcache()
    {
        if(!empty($_GET["id"]))
        {
            $path = "build".DIRECTORY_SEPARATOR.$_SESSION["mwccfgread"].DIRECTORY_SEPARATOR."_dat".DIRECTORY_SEPARATOR."cache".DIRECTORY_SEPARATOR."0_".$_SESSION["mwclang"]."_plugin_".$_GET["id"];

            if (file_exists($path))
            {
                unlink($path);
                echo "qq";
            }


        }
    }
}