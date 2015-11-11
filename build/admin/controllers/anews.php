<?php

/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 02.09.2015
 * управление новостями
 **/
class anews extends aController
{
    public function action_index()
    {
       // $this->view->add_dict("anews");
        $allnews = $this->model->getNhistory();
        $iter = new ArrayIterator($allnews);
        foreach ($iter as $news)
        {
            $news["indate"] = Tools::transDate($news["indate"]);

            $this->view
                ->set($news) //Добавляем в новости
                ->out("main_c","anews"); //сохраняем результат по каждой строке с шаблоном main_c.html с папки anews
        }

        $this->view
            ->setFContainer("newshistory",true) //суем полученные по всем строкам новости в newshistory и чистим контенер
            ->out("main","anews"); //выводим на экран
    }



    /**
     * выводит на экран сформированный массив с новостью
     */
    public function action_getnums()
    {
        if(!empty($_GET["get"]))
        {
            $nn = (int)$_GET["get"];
            if($nn>0)
            {
                $news = $this->model->ninfo($nn);

                $news["indate"] = Tools::transDate($news["indate"]);
                $news["ntitle"] = Tools::unhtmlentities($news["ntitle"]);
                $news["news"] = Tools::unhtmlentities($news["news"]);


                echo  implode("_+separator+_",$news);
            }
        }
    }

    /**
     * применить изменения к новости
     */
    public function action_apply()
    {
        if(!empty($_POST["id"]) && !empty($_POST["ntitlez"]) && !empty($_POST["newsinput"]))
        {
            if (function_exists("get_magic_quotes_gpc") && get_magic_quotes_gpc())
            {
                $_POST["newsinput"] = stripslashes($_POST["newsinput"]);
                $_POST["ntitlez"] = stripslashes($_POST["ntitlez"]);
            }

            $news["id"] = (int)$_POST["id"];
            $news["ntitlez"] = $_POST["ntitlez"];
            $news["newsinput"] = $_POST["newsinput"];
            if(!empty($_POST["ntag"]))
                $news["ntag"] = $_POST["ntag"];
            $this->model->apply($news);
        }

    }

    /**
     * добавление новости
     */
    public function action_newnews()
    {
        if(!empty($_POST["ntitlez"]) && !empty($_POST["newsinput"]))
        {
            if (function_exists("get_magic_quotes_gpc") && get_magic_quotes_gpc())
            {
                $_POST["newsinput"] = stripslashes($_POST["newsinput"]);
                $_POST["ntitlez"] = stripslashes($_POST["ntitlez"]);
            }


            $news["ntitlez"] = $_POST["ntitlez"];
            $news["newsinput"] = $_POST["newsinput"];
            if(!empty($_POST["ntag"]))
                $news["ntag"] = $_POST["ntag"];

            if(!empty($_SESSION["mwcaname"]))
                $news["autor"] = $_SESSION["mwcaname"];
            else
                $news["autor"] = "Admin";

            $this->model->addnews($news);
        }

    }

    /**
     * удаление новости
     */
    public function action_delnews()
    {
        if(!empty($_POST["id"]))
        {
            $this->model->delnews((int)$_POST["id"]);
        }
    }

    /**
     * возвращает лист доступных новостей
     */
    public function action_bglist()
    {
        //$this->view->add_dict("anews");
        $allnews = $this->model->getNhistory();
        $iter = new ArrayIterator($allnews);
        foreach ($iter as $news)
        {
            $news["indate"] = Tools::transDate($news["indate"]);

            $this->view
                ->set($news) //Добавляем в новости
                ->out("main_c","anews"); //сохраняем результат по каждой строке с шаблоном main_c.html с папки anews
        }
    }

}