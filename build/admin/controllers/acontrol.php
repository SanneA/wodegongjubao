<?php

/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 17.09.2015
 * управление учетками админа
 **/
class acontrol extends aController
{

    public function action_index()
    {
        $this->view->out("main","acontrol");
    }

    /**
     * список пользователей админки
     */
    public function action_getlist()
    {
        $alist = $this->model->getAdminsList();
        foreach ( $alist as $id=>$val )
        {
            $this->view
                ->set("login",$val[0])
                ->set("ids",$id)
                ->set("group",$val[1])
                ->out("center","acontrol");
        }
    }

    /**
     * инфа по админу
     */
    public function action_info()
    {
        if(!empty($_GET["id"]))
        {
            $info = $this->model->getInfo((int)$_GET["id"]);
            $this->view
                ->add_dict($info)
                ->set("grplist",html_::select($this->model->getCurrentList(),"checkedgroup",$info["access"],"style='width:183px;'"))
                ->set("id",$_GET["id"])
                ->out("form","acontrol");
        }
    }

    /**
     * применить изменения
     */
    public function action_apply()
    {
        if(isset($_GET["id"]))
        {
            if(empty($_POST["login"]))
                return;
            else
                $name = substr($_POST["login"],0,20);

            if(empty($_POST["nick"]))
                return;
            else
                $nick = substr($_POST["nick"],0,20);

            if(empty($_POST["checkedgroup"]))
                return;
            else
                $access = (int)$_POST["checkedgroup"];

            if(empty($_POST["passwd"]))
                $pwd = NULL;
            else
                $pwd = "'".md5($_POST["passwd"])."'";

            if(empty($_POST["mails"]))
                $mail = "NULL";
            else
                $mail = "'{$_POST["mails"]}'";

            if($_GET["id"] != "-")
            {
                $this->model->editAdmin((int)$_GET["id"],$name,$nick,$access,$pwd,$mail);
            }
            else
            {
                $this->model->addAdmin($name,$nick,$access,$pwd,$mail);
            }

        }

    }

    public function action_deladm()
    {
        if(!empty($_GET["id"]))
        {
            $this->model->delAdmin((int)$_GET["id"]);
        }
    }

}