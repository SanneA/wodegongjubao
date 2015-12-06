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

    public function action_index ()
    {
        $buildList = Tools::getAllBuilds();
        $ai = new ArrayIterator($buildList);
        foreach ($ai as $id=>$vals)
        {
            if(substr_count($vals,"admin")>0)
            {
                unset($buildList[$id]);
            }
        }
        unset($buildList[-1]);

        if(!empty($_POST["abchenge"]))
        {
            if(!empty($buildList[$_POST["abchenge"]]))
            {
                $_SESSION["mwcbuild"] = $buildList[$_POST["abchenge"]];
                Tools::go();
            }
        }
        if(count($buildList)>1)
        {
            $this->view
                ->set("buildlist",html_::select($buildList,"abchenge",$_SESSION["mwcbuild"],'onchange="document.getElementById(\'abcnanger\').submit();" class="selectbox"'))
                ->out("plugin_bchanger");
        }
    }

}