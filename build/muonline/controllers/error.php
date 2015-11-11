<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 03.10.2015
 *
 **/
class error extends muController
{
    public function action_index()
    {
        if(!empty($_GET["get"]))
        {
            $this->view->error((int)$_GET["get"]);
        }
    }
}