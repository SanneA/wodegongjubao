<?php

/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 04.12.2015
 *
 **/
class downloads extends aController
{
    public function action_index ()
    {
        $list = $this->model->getList();
        if(!empty($list))
        {
            $ai = new ArrayIterator($list);
            foreach ($ai as $vals)
            {
                $this->view
                    ->set($vals) //Добавляем в новости
                    ->out("main_c",get_class($this)); //сохраняем результат по каждой строке с шаблоном main_c.html с папки anews
            }
        }

        $this->view
            ->setFContainer("newshistory",true) //суем полученные по всем строкам новости в newshistory и чистим контенер
            ->out("main",get_class($this)); //выводим на экран
    }

    public function action_getnums()
    {
        if(!empty($_GET["get"]))
        {
            $nn = (int)$_GET["get"];
            if($nn>0)
            {
                $news = $this->model->info($nn);

                $news["col_title"] = Tools::unhtmlentities($news["col_title"]);
                $news["col_desc"] = Tools::unhtmlentities($news["col_desc"]);
                $news["col_address"] = Tools::unhtmlentities($news["col_address"]);

                echo  implode("_+separator+_",$news);
            }
        }
    }

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
            $news["title"] = $_POST["ntitlez"];
            $news["desc"] = $_POST["newsinput"];
            $news["address"] = $_POST["ntag"];

            $this->model->edit($news);
        }

    }

    public function action_newnews()
    {
        if(!empty($_POST["ntitlez"]) && !empty($_POST["newsinput"]) && !empty($_POST["ntag"]))
        {
            if (function_exists("get_magic_quotes_gpc") && get_magic_quotes_gpc())
            {
                $_POST["newsinput"] = stripslashes($_POST["newsinput"]);
                $_POST["ntitlez"] = stripslashes($_POST["ntitlez"]);
            }

            $news["title"] = $_POST["ntitlez"];
            $news["desc"] = $_POST["newsinput"];
            $news["address"] = $_POST["ntag"];

            $this->model->add($news);
        }

    }

    public function action_delnews()
    {
        if(!empty($_POST["id"]))
        {
            $this->model->delete((int)$_POST["id"]);
        }
    }

    public function action_bglist()
    {
        $allnews = $this->model->getList();
        $iter = new ArrayIterator($allnews);
        foreach ($iter as $news)
        {
            $news["indate"] = date_::transDate($news["indate"]);

            $this->view
                ->set($news) //Добавляем в новости
                ->out("main_c",get_class($this)); //сохраняем результат по каждой строке с шаблоном main_c.html с папки anews
        }
    }

}