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

        $obj = new TopParse(Tools::unhtmlentities($this->configs["mmo_adress"]),'	', array(
            "fields"=>array(
                "acc" => 3,
                "vote" => 4,
                "date" => 1,
                )));

        $array = $obj->getResult();

        if(!empty($array))
        {
           $this->model->getPrize($array);
        }

        if($this->cacheNeed()) //если нужен кеш
            $this->doCache("mmovote");
    }

}