<?php

/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 02.09.2015
 * плагин для показа группы админа
 **/
class admgroup extends aPController
{

    public function action_index()
    {
        if(isset($_SESSION["mwcagroupname"]))
        $this->view->set("vgrname",$_SESSION["mwcagroupname"]); //указываем группу пользователя (скорее для красоты...)
    }
}