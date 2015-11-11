<?php

/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 09.09.2015
 * управление группами
 **/
class agroup extends aController
{
    /**
     * вывод на экран всех групп
     */
    public function action_index()
    {
        self::action_getlist();

        $this->view
            ->setFContainer("gencontent",true)
            ->out("agroup_main","agroup");
    }

    /**
     * список
     */
    public function action_getlist()
    {
        $groups = $this->model->getGroups();
        $path = "build".DIRECTORY_SEPARATOR.$_SESSION["mwcabuild"].DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$_SESSION["mwclang"].DIRECTORY_SEPARATOR."group.php";

        if(file_exists($path))
        {
            require $path;
        }

        $ai = new ArrayIterator($groups);
        foreach ($ai as $id=>$val)
        {
            if(!empty($lang[$val]))
                $_name = $lang[$val];
            else
                $_name = $val;

            $this->view
                ->set(array("id"=>$id,"g_name"=>$_name))
                ->out("agroup_c","agroup");
        }

    }

    /**
     * Удаление группы
     */
    public function action_delgroup()
    {
        if(!empty($_GET["get"]))
        {
            $id = (int)$_GET["get"];
            $this->model->delGroup($id);
        }
    }

    /**
     * форма для новой группы
     */
    public function action_groupform()
    {
        $this->view->out("agroup_form","agroup");
    }

    /**
     * Добавление группы
     */
    public function action_addgroup()
    {
        if(!empty($_GET["grpname"]))
        {
            $this->model->addGroup($_GET["grpname"]);
        }
    }

    /**
     * информация о доступных группе страницах
     */
    public function action_groupinfo()
    {
        if(!empty($_GET["id"]))
        {
            $pid = (int)$_GET["id"];
            $opages = $this->model->nonpageList($pid);

            self::action_getplist($pid); //генерируем список страниц для группы

            $this->view
                ->setFContainer("knownpages",true)
                ->set("id",$pid)
                ->set("grplist",Tools::htmlSelect($opages,"newpage"))
                ->out("groupinfo","agroup");
        }
    }

    /**
     * добавление страниц в группу
     */
    public function action_addpage()
    {
        if(!empty($_GET["id"]) && !empty($_POST["newpage"]))
        {
            $gid = (int)$_GET["id"];
            $pid = (int)$_POST["newpage"];

            $this->model->addNewPage($gid,$pid);
        }
    }

    /**
     * удаление страницы
     */
    public function action_dellpage()
    {
        if(!empty($_GET["id"]))
        {
            $gid = (int)$_GET["id"];
            $this->model->dellPage($gid);
        }
    }

    /**
     * список страниц у группы
     * @param null|int $pid номер группы
     */
    public function action_getplist($pid = null)
    {
        if(is_null($pid))
        {
            if(!empty($_GET["id"]))
                $pid = (int)$_GET["id"];
            else
                return;
        }

        $gpages = $this->model->pageList($pid);

        $ai = new ArrayIterator($gpages);
        foreach ($ai as $id=>$val)
        {
            $this->view
                ->set(array("aid"=>$id,"p_name"=>$val))
                ->out("knownpages","agroup");
        }
    }
}