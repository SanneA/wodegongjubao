<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 20.09.2015
 *
 **/
class news extends muController
{
    public function action_index()
    {
        $this->view->replace("title","keywords");
        $this->view->replace("title","description");


        $nar = $this->model->getNewsList($this->configs["cntPerPage"]); //выборка новостей

        if(count($nar)>0)
        {
            $ai = new ArrayIterator($nar);

            foreach ($ai as $i_=> $values)
            {
                $values["indate"] = date_::transDate($values["indate"]);
                $values["news"] = Tools::unhtmlentities($values["news"]);
                $this->view
                    ->add_dict($values)
                    ->out("center","news");
            }
        }
        $this->view
            ->setFContainer("newscontent",1)
            ->out("main","news");

    }

}