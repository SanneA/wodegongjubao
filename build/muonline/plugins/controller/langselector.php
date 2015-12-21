<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 21.12.2015
 * выбор языка
 **/

class langselector extends muPController
{
    public function action_index()
    {
        $Langs = $this->model->getLangs();
        if(count($Langs)<=1) //если для билда только 1 папка с языком, зачем ее показывать?
            return;

        $_REQUEST["langchooses"] = substr(trim($_REQUEST["langchooses"]),0,2);

        if(!empty($_REQUEST["langchooses"]) && !empty($Langs[$_REQUEST["langchooses"]]))
        {
           $_SESSION["mwclang"] = $_REQUEST["langchooses"];
           Tools::go();
        }

        $this->view
            ->set("langdirlist",html_::select($Langs,"langchooses",$_SESSION["mwclang"]," class='selectbox' onchange='document.getElementById(\"langmenuform\").submit()'"))
            ->out("plugin_langselector");
    }
}