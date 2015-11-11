<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 11.10.2015
 * банк
 **/
class bank extends muController
{
    public function action_index()
    {
        if(!$this->model->isOnline()) //если пользователь не онлайн
        {
            $data = $this->model->aboutUser();
            $this->view
                ->set(array("webbank"=>Tools::number($data["mwc_bankZ"]),
                    "whmoney"=>Tools::number($data["Money"])))
                ->out("main","bank");
        }
        else
        {
            $this->view->out("error","bank");
        }
    }

    /**
     * переводы зен
     */
    public function action_transact()
    {
        if(!empty($_POST["typetrans"]) && !empty($_POST["zens"]) && !$this->model->isOnline())
        {
            $type = (int)$_POST["typetrans"];
            $zen = (int)$_POST["zens"];
            $data = $this->model->aboutUser();//получаем данные по состоянию акка вообще

            if($type == 1) //из банка в сундук
            {
                if($data["mwc_bankZ"]<$zen)
                    echo $this->view->getVal("noinweb");
                else
                {
                    $this->model->putInWh($zen);
                    echo $this->view->getVal("allok");
                }

            }
            else if($type == 2) //обратно
            {
                if($data["Money"]<$zen)
                    echo $this->view->getVal("noinhw");
                else
                {
                    $this->model->putInWeb($zen);
                    echo $this->view->getVal("allok");
                }

            }
        }
    }

    public function action_knowmoney()
    {
        $data = $this->model->aboutUser();//получаем данные по состоянию акка вообще
        echo Tools::number($data["mwc_bankZ"])."::".Tools::number($data["Money"]);
    }
}