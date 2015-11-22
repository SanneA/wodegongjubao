<?php

/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 31.08.2015
 *
 **/
class adminmenu extends aPController
{
    protected $needValid = false; //выключаем валидацию POST & GET

    public function action_index()
    {
        $this->genadminmenu();
    }
    /**
     * генерация менюхи админа
     */
    private function genadminmenu()
    {
        if($this->isCached("adminmenu")) //кешик
            return;

        $memu = $this->model->getMenu();
        $aimenu = new ArrayIterator($memu);
        $tmp_ = "";

        foreach ( $aimenu as $mid => $mvalue)
        {
            $this->view->set($mvalue);
            $tmp_.= $this->view->out("adminmenu_c","public",1);
        }
        if(!empty($tmp_))
            $this->view->setFromCache($tmp_);


        if($this->cacheNeed()) //если нужен кеш
            $this->doCache("adminmenu");
    }
}