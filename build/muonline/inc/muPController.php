<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 04.10.2015
 *
 **/
class muPController extends PController
{
    public function __construct(Model $model,content $view,$plugins,$server)
    {
        parent::__construct($model, $view,$plugins,$server);
        $this->view->add_dict("plugin_".get_class($this));
        $this->configs = Configs::readCfg("plugin_".get_class($this),"muonline");
    }
}