<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 31.10.2015
 * веб жоп же
 **/
class webshop extends muController
{
    public function init()
    {
        $this->model->setFilters($this->configs); //загражаем вещи, что запрещены к загрузке
    }

    public function action_index()
    {
        $this->view
            ->set("itlist",$this->model->addItemlist($this->configs["hexLen"]))
            ->out("in","webshop");
    }

    /**
     * упихиваем в веб магазин
     */
    public function action_putin()
    {
        if(!empty($_POST))
        {
            $this->model->adding((int)$_POST["itm"],(int)$_POST["price"],$this->configs["hexLen"]);
        }
    }
}