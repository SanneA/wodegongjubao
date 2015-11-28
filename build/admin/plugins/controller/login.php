<?php

/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 28.08.2015
 *
 **/
class login extends aPController
{
    protected $postField = array(
        "mwcalogin" => array("type"=>"str","maxLength"=>22),
        "mwcapwd" => array("type"=>"str","maxLength"=>22)
    );

    public function action_index()
    {
        if(isset($_POST["mwcalogin"]) && isset($_POST["mwcapwd"]) && !$this->model->isLogged())
        {
            if($this->model->auth($_POST["mwcalogin"],$_POST["mwcapwd"]) != false)
            {
                Tools::go(); //если авторизация прошла - страницу перезагружаем и даем доступы
            }
            else
            {
                $this->view->defHtml = "login";
                $_GET["a"] = "login"; 
                $this->model->toLog("wrong auth login: {$_POST["mwcalogin"]}, pwd: {$_POST["mwcapwd"]}","plugin_login",5); //если кто-то пытается вломится, пишем лог о неверной авторизации
            }
        }
        elseif (isset($_REQUEST["aloguot"]) && $this->model->isLogged())
        {
            $this->model->logOut();
            Tools::go($this->view->getAdr()); //редирект на главную сайта
        }
        else if (!$this->model->isLogged())
        {
            $this->view->defHtml = "login"; //указываем, что основной шаблон для вывода будет логин шаблон (в дефольтной теме он на всю страницу)
            $_GET["a"] = "login"; //устанваливаем экшн логина для основного шаблона.(в дефолте, он просто выведет страничку с логином).
        }
    }
}