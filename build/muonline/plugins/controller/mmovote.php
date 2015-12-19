<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 19.12.2015
 * голосовалка
 **/

class mmovote extends muPController
{
    public function action_index()
    {
        if($this->isCached("mmovote")) //кешик
            return;

        require "build/".tbuild."/inc/parse.php";

        $obj = new TopParse(Tools::unhtmlentities($this->configs["mmo_adress"]),'	');//,array('static'=>'true',"onlyone"=>"false"));
        $array = $obj->parce();

        if(!empty($array))
        {
           $this->model->getPrize($array);
        }

        if($this->cacheNeed()) //если нужен кеш
            $this->doCache("mmovote");
    }

}