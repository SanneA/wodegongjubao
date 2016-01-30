<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 23.01.2016
 * скрипт ресета
 **/
class reset extends muController
{
    public function init()
    {
        parent::init();
        $this->configs += Configs::readCfg("unic",tbuild);
    }

    public function action_index()
    {

        if($this->model->isOnline() > 0)
            throw new Exception($this->view->getVal("l_accisonline"));

        if(empty($_SESSION["mwccharacter"]))
            throw new Exception($this->view->getVal("l_err_nochar"));

        $charInfo = $this->model->chracterInfo($_SESSION["mwccharacter"],$_SESSION["mwcuser"]);

        $needZen = ($charInfo[$this->configs["rescolumn"]]+1) * $this->configs["resZen"];

        if($needZen > $this->configs["maxZenPrice"])
            $needZen = $this->configs["maxZenPrice"];

        if(isset($_REQUEST["gotores"]))
        {
            if($charInfo["cLevel"] < $this->configs["lvlRes"])
                throw new Exception($this->view->getVal("l_err_nolevel"));

            if($charInfo["Money"] < $needZen)
                throw new Exception($this->view->getVal("l_err_nozen"));

            $this->model->getRes($this->configs);
            Tools::go($this->view->getAdr()."page/freepoints.html");
        }

        $this->view
            ->add_dict($this->configs)
            ->set("zen4res",Tools::number($needZen,0))
            ->out("index",get_class($this));
    }


}