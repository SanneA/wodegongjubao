<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 10.10.2015
 * стартовая страница после логина
 **/
class startpage extends muController
{
    public function action_index()
    {
        $this->view->out("startpage");
    }

}