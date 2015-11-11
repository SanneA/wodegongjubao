<?php

/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 17.09.2015
 * упправление языком
 **/
class lmanage extends aController
{
    public function action_index()
    {
        $this->view
            ->set("langfileslist",Tools::htmlSelect($this->model->getFileList(),"selFolder",0,"onchange='lmanfilter();'"))
            ->out("main","lmanage");
    }

    /**
     * содержимое файла языка
     */
    public function action_genvals()
    {
        if(isset($_GET["fid"]))
        {
            $files = $this->model->getFileList();
            if(!empty($files[$_GET["fid"]]))
            {
                $path = "build".DIRECTORY_SEPARATOR.$_SESSION["mwccfgread"].DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$_SESSION["mwclang"].DIRECTORY_SEPARATOR.$files[$_GET["fid"]].".php";
                if(file_exists($path))
                {
                    require $path;
                    if(isset($lang) && count($lang)>0)
                    {
                        $ai = new ArrayIterator($lang);
                        foreach ($ai as $id=>$val)
                        {
                            $this->view
                                ->set( array("_name"=>$id,
                                    "_val"=>$val,
                                    "fid"=>$_GET["fid"]
                                ))
                                ->out("center","lmanage");
                        }
                    }
                }
            }

        }
    }

    /**
     * форма добавления/изменения
     */
    public function action_edit()
    {
        if(isset($_GET["fid"]))
        {
            $files = $this->model->getFileList();
            if(!empty($files[$_GET["fid"]])) //если конфиг есть
            {
                $path = "build".DIRECTORY_SEPARATOR.$_SESSION["mwccfgread"].DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$_SESSION["mwclang"].DIRECTORY_SEPARATOR.$files[$_GET["fid"]].".php";
                if(file_exists($path))
                {
                    require $path;

                    if(!empty($_GET["pname"]) && isset($lang[$_GET["pname"]]))
                    {
                        $this->view
                            ->set("cname",$_GET["pname"])
                            ->set("cval",$lang[$_GET["pname"]]);
                    }
                }
            }

            $this->view
                ->set("fid",$_GET["fid"])
                ->out("form","lmanage");
        }
    }

    /**
     * изменение
     */
    public function action_applyed()
    {
        Tools::debug($_GET);
        Tools::debug($_POST);
        if(isset($_GET["fid"]) && !empty($_POST["cval"]))
        {

            $id = (int)$_GET["fid"];
            $files = $this->model->getFileList();
            if(!empty($files[$id])) //если конфиг есть
            {
                $path = "build".DIRECTORY_SEPARATOR.$_SESSION["mwccfgread"].DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$_SESSION["mwclang"].DIRECTORY_SEPARATOR.$files[$id].".php";
                if(file_exists($path))
                {
                    require $path;

                    if(empty($_POST["cname"]))
                    {
                        $i=1;
                        while(!empty($lang["auto_lang".$i]))
                        {
                            $i++;
                        }

                        $_POST["cname"] = "auto_lang$i";
                    }


                    $lang[$_POST["cname"]] = $_POST["cval"];

                    $ai = new ArrayIterator($lang);
                    $content='<?php ';

                    foreach ($ai as $id => $val)
                    {
                        $content.='$lang["'.$id.'"]="'.$val.'"; ';
                    }

                    $fh = fopen($path,"w");
                    fwrite($fh,$content);
                    fclose($fh);
                }
            }
        }
    }

    public function action_del()
    {
        if(isset($_GET["fid"]) && !empty($_GET["pname"]))
        {
            $id = (int)$_GET["fid"];
            $files = $this->model->getFileList();
            if(!empty($files[$id])) //если конфиг есть
            {
                $path = "build" . DIRECTORY_SEPARATOR . $_SESSION["mwccfgread"] . DIRECTORY_SEPARATOR . "lang" . DIRECTORY_SEPARATOR . $_SESSION["mwclang"] . DIRECTORY_SEPARATOR . $files[$id] . ".php";
                if (file_exists($path))
                {
                    require $path;
                    unset($lang[$_GET["pname"]]);
                    $ai = new ArrayIterator($lang);
                    $content='<?php ';

                    foreach ($ai as $id => $val)
                    {
                        $content.='$lang["'.$id.'"]="'.$val.'"; ';
                    }

                    $fh = fopen($path,"w");
                    fwrite($fh,$content);
                    fclose($fh);
                }
            }
        }
    }
}