<?php

/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 03.09.2015
 * плагин для селекта билда для настроек, новостей и прочих ништяков
 **/
class selserver extends aPController
{
    protected $postField = array(
        "whosconfig" => array("type"=>"str","maxLength"=>30)
    );

    public function action_index()
    {
        if(!empty($_SESSION["mwccfgread"]))
            $selectted = $_SESSION["mwccfgread"];
        else
        {
            require "configs/configs.php";
            $selectted = $cfg["defaultabuild"];
            $_SESSION["mwccfgread"] = $cfg["defaultabuild"];
        }


        $this->view
            ->set("buildsList",Tools::htmlSelect(Tools::getAllBuilds(),"whosconfig",$selectted,"class='selectserv' onchange='document.getElementById(\"bselecter\").submit()'"))
            ->out("selserver");
    }
}