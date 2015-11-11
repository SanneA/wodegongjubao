<?php

/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 28.08.2015
 *
 **/
class admin extends aController
{
    public function action_index()
    {
        $this->view->out("admin_main","admin");
    }
}