<?php

/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 05.09.2015
 *
 **/
class aController extends Controller
{
    public function __construct(Model $model,content $view,$pages,$server)
    {
        parent::__construct($model,$view,$pages,$server);
        $this->view->add_dict("admin");//словарик подключаем админский
        $this->view->add_dict(get_class($this)); //подключаем словарь к модулю (если он, конечно, есть)
    }

    /**
     * возвращает название модуля
     *
     * @param int $type 0 - выводит на экран, иначе - возвращает строкой
     * @return string
     */
    public function action_title($type=0)
    {
        $path = "build".DIRECTORY_SEPARATOR.$_SESSION["mwcabuild"].DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$_SESSION["mwclang"].DIRECTORY_SEPARATOR."titles.php";
        $pname = NULL;
        if(file_exists($path))
        {
            require $path;
            if(!empty($this->pages[get_class($this)]["title"]) )
            {
                if(!empty($lang[$this->pages[get_class($this)]["title"]]))
                {
                    $pname = $lang[$this->pages[get_class($this)]["title"]];
                }
                else
                    $pname = $this->pages[get_class($this)]["title"];
            }
        }
        else if(!empty($this->pages[get_class($this)]["title"]))
            $pname = $this->pages[get_class($this)]["title"];

        if(!$type && !is_null($pname))
            echo $pname;

        return $pname;
    }
}