<?php

/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 30.11.2015
 * менятель билда
 **/
class bchanger extends muPController
{
    protected $postField = array(
        "abchenge" => array("type"=>"str","maxLength"=>20)
    );

    private $sinonims = array(
        "muonline"=>"build1",
        "muonline2"=>"build2",
    );

    public function action_index ()
    {
        $buildList = Tools::getAllBuilds();
        $sinList = array();
        $ai = new ArrayIterator($buildList);
        foreach ($ai as $id=>$vals)
        {
            if(substr_count($vals,"admin")>0)
            {
                unset($buildList[$id]);
            }
            else{
                if(!empty($this->sinonims[$vals]))
                    $sinList[$vals] = $this->sinonims[$vals];
                else
                    $sinList[$id] = $vals;
            }


        }
        unset($buildList[-1]);
        unset($sinList[-1]);

        if(!empty($_POST["abchenge"]))
        {
            if(!empty($buildList[$_POST["abchenge"]]))
            {
                unset($_SESSION["mwcuser"],$_SESSION["mwcpwd"],$_SESSION["mwcpoints"],$_SESSION["mwccharacter"]);
                $_SESSION["mwcbuild"] = $buildList[$_POST["abchenge"]];
                Tools::go();
            }
        }
        if(count($buildList)>1)
        {
            $this->view
                ->set("buildlist",html_::select($sinList,"abchenge",$_SESSION["mwcbuild"],'onchange="document.getElementById(\'abcnanger\').submit();" class="selectbox"'))
                ->out("plugin_bchanger");
        }
    }

}