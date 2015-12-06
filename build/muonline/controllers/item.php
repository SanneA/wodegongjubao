<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 31.10.2015
 * показ вещей
 **/
class item extends muController
{
    public function action_index()
    {

    }

    /**
     * показ вещей
     */
    public function action_view()
    {
        if(!empty($_GET["get"]))
        {
            $path = "build/".tbuild."/_dat/items/harmony.php";
            if(file_exists($path))
                require $path;
            else
                $harm = array();

            $img = "theme/".$this->view->getVal("theme")."/images/items";
            echo $this->model->itemShow($_GET["get"],$harm,1,0,$this->view->getAdr()."theme/".$this->view->getVal("theme")."/images/items",$img);
        }
    }
    public function action_viewhex()
    {
        if(!empty($_GET["get"]))
        {
            $path = "build/".tbuild."/_dat/items/harmony.php";
            if(file_exists($path))
                require $path;
            else
                $harm = array();

            Tools::debug(rItem::Read($_GET["get"],$harm)); //читаем вещь
        }
    }

}