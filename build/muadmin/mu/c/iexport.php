<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 30.10.2015
 * импорт вещей
 **/
class iexport extends aController
{
    public function action_index()
    {
        if(!empty($_POST))
        {
            require "build/muadmin/inc/iWork.php";
            if (isset($_REQUEST["addbtn"]))
            {
                $itemfile = file($_FILES["itemfile"]["tmp_name"]);
                $ist = file($_FILES["ist"]["tmp_name"]);
                $iso = file($_FILES["iso"]["tmp_name"]);
                $iao = file($_FILES["iao"]["tmp_name"]);
                $skill = file($_FILES["skill"]["tmp_name"]);
                $harm = file($_FILES["harm"]["tmp_name"]);


                if (is_array($itemfile))
                    $obj  = new  createIbase("build/".tbuild."/_dat/items",$itemfile,$skill,$iso,$ist,$iao);
                if (is_array($harm))
                    $hobj = new BuildHarmony($harm,"build/".tbuild."/_dat/items");

                $this->view->replace("l_itemok","itmmsg");

            }
            elseif (isset($_REQUEST["addsok"]))
            {
                $soket = file($_FILES["soket"]["tmp_name"]);
                $sok = new SoketInfo($soket,1);
                $sok->Save("build/".$_SESSION["mwccfgread"]."/_dat/items/sockets.php");

                $this->view->replace("l_skcmok","sckmsg");
            }
        }

        $this->view->out("main","iexport");
    }

}