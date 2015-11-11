<?php

/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 05.09.2015
 *
 **/
class aPController extends PController
{
    public function __construct(Model $model,content $view,$plugins,$server)
    {
        parent:: __construct($model, $view,$plugins,$server);
        $this->view->add_dict("plugin_".get_class($this));
        $this->view->add_dict("admin");
         if(!empty($_SESSION["mwcabuild"]))
             $this->configs = Configs::readCfg(get_class($this),$_SESSION["mwcabuild"]); //подгружаем конфиги модуля сразу);
    }

}