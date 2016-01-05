<?php

/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 05.09.2015
 *
 **/
class muController extends Controller
{
    public function __construct(Model $model,content $view,$pages,$server)
    {
        parent::__construct($model,$view,$pages,$server);

        $cfg = Configs::readCfg("main",tbuild);

        if(!empty($cfg["snames"][0]))
            $this->view->set("sname",$cfg["snames"][0]);

        $this->view
            ->add_dict(get_class($this)) //подключаем словарь к модулю (если он, конечно, есть)
            ->add_dict("titles") //словарь тайтлов для страничек
            ->replace($this->pages[get_class($this)]["title"],"title"); //выставляем заголовок текущего модуля заместо |title|
    }
}