<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 07.11.2015
 * веб магазин
 **/
class webmarket extends muController
{
    public function init()
    {
        require "build/muonline/inc/rItem.php";
    }

    public function action_index()
    {
        $alloweds = explode(",",$this->configs["uGroups"]);

        if(in_array($_SESSION["mwcpoints"],$alloweds))
        {
            $this->view->set("isallows","");
        }
        else
        {
            $this->view->set("isallows","display:none;");
        }
        $this->view
            ->out("main","webshop");
    }

    public function action_inlist()
    {
        if(!empty($_POST))
        {
            $alloweds = explode(",",$this->configs["uGroups"]);
            $isallow = 0;

            if(in_array($_SESSION["mwcpoints"],$alloweds))
            {
                $isallow = 1;
                $user = $this->model->aboutUser($_SESSION["mwcuser"]);
            }
            else
                $user["mwc_bankZ"] =0;
            $harm_ = "build/muonline/_dat/items/harmony.php";

            if(file_exists($harm_))
                require $harm_;
            else
                $harm = array();

            if(!empty($_POST["itemname"]))
                $params["name"] = $_POST["itemname"];

            $params["lvlf"] = (int)$_POST["lvlf"];
            $params["lvlt"] = (int)$_POST["lvlt"];

            if(!empty($_POST["isExc"]))
                $params["isExc"] = 1;

            if(!empty($_POST["pn"]))
                $params["pn"] = (int)$_POST["pn"];
            else
                $params["pn"] = 1;

            if(!empty($_POST["isAnc"]))
                $params["isAnc"] = 1;

            if(!empty($_POST["isSkill"]))
                $params["isSkill"] = 1;

            if(!empty($_POST["isOpt"]))
                $params["isOpt"] = 1;

            if(!empty($_POST["isPVP"]))
                $params["isPVP"] = 1;

            if(!empty($_POST["isHarmony"]))
                $params["isHarmony"] = 1;

            $count = $this->model->getCount($params);

            if($count>0)
            {
                $pp = Tools::paginate($count,$this->configs["itemPp"],$params["pn"]);
                $params["min"]=$pp["min"];
                $params["max"]=$pp["max"];

                if($pp["count"]>1)
                {
                    for($i = 0; $i < $pp["count"]; $i++)
                    {
                        if($i+1 == $params["pn"])
                            $this->view->set("isactive","color:white");
                        else
                            $this->view->set("isactive","");

                        $this->view
                            ->set("i",($i+1))
                            ->out("paginator_c","webshop");
                    }

                    $this->view
                        ->setFContainer("pcontent",true)
                        ->out("pmain","webshop");
                }

                $list = $this->model->getlist($params);
                $ai = new ArrayIterator($list);
                foreach($ai as $vals)
                {
                    if(!empty($vals["col_isExc"]))
                    {
                        $vals["col_Name"] = "Excellent ".$vals["col_Name"];
                    }

                    if(!empty($vals["col_isAnc"]))
                    {
                        $vals["col_Name"] = "Ancient ".$vals["col_Name"];
                    }

                    if(!empty($vals["col_isSkill"]))
                    {
                        $vals["col_Name"].= " + skill" ;
                    }

                    if(!empty($vals["col_isOpt"]))
                    {
                        $vals["col_Name"].= " + options" ;
                    }

                    if(!empty($vals["col_isPVP"]))
                    {
                        $vals["col_Name"].= " + PvP" ;
                    }

                    if(!empty($vals["col_isHarmony"]))
                    {
                        $vals["col_Name"].= " + harmony" ;
                    }

                    if(!empty($vals["col_isSock"]))
                    {
                        $vals["col_Name"].= " with sockets" ;
                    }

                    switch($vals["col_level"])
                    {
                        case 0:
                        case 1:
                        case 2:
                            $cls = "I_Normal_l";break;
                        case 3:
                        case 4:
                        case 5:
                        case 6:
                            $cls = "I_Normal3_l";break;
                        case 7:
                        case 8:
                            $cls = "I_Normal7_l";break;
                        case 9:
                        case 10:
                        case 11:
                        case 12:
                        case 13:
                        case 14:
                        case 15:
                            $cls = "I_plus9_l"; break;
                        default:
                            $cls = "I_Normal_l";break;
                    }

                    if($vals["col_user"] == $_SESSION["mwcuser"])
                    {
                        $this->view->set("vizby","display:none");
                        $this->view->set("vizdr","");
                    }
                    elseif ($isallow>0) //если пользователь и не хозяин вещи
                    {
                        if($user["mwc_bankZ"] >= $vals["col_prise"])
                            $this->view->set("vizby","");
                        else
                            $this->view->set("vizby","display:none");

                        $this->view->set("vizdr","display:none");
                    }
                    else
                    {
                        $this->view->set("vizby","display:none");
                        $this->view->set("vizdr","display:none");
                    }
                    //todo : запилить возможность одмином снимать вещи


                    $vals["col_Name"] = "<span class='$cls'>{$vals["col_Name"]}</span>";

                    $vals["col_prise"] = Tools::number($vals["col_prise"]," Zen");
                    $this->view
                        ->add_dict($vals)
                        ->set("img",itemShow($vals["col_hex"],$harm,2,0,array("address"=>$this->view->getAdr(),"theme"=>$this->view->getVal("theme"))))
                        ->out("center","webshop");
                }

                if($pp>1)
                {
                    $this->view
                        ->out("pmain","webshop");
                }

            }

        }
        else
            echo "empty post";

    }

    public function action_drop()
    {
        if(!empty($_GET["get"]))
        {
            $inom = (int)$_GET["get"];
            $harm_ = "build/muonline/_dat/items/harmony.php";
            if(file_exists($harm_))
                require $harm_;
            else
                $harm = array();

            if(file_exists("build/muonline/_dat/items/items.php"))
                require "build/muonline/_dat/items/items.php";
            else
                $item = array();

            $cfg = Configs::readCfg("webshop","muonline");
            $emptyItem = str_pad("", $cfg["hexLen"],"F",STR_PAD_BOTH);

            $item_ = $this->model->getInfo($inom);
            if($item_["col_hex"]!=$emptyItem)
            {
                $_item = rItem::Read($item_["col_hex"],$harm);
                $wh = $this->model->getWH($_SESSION["mwcuser"],$cfg["hexLen"]);
                $place = rItem::search($wh,$_item["x"],$_item["y"],$item,$cfg["hexLen"],120);
                if($place>=0)
                {
                    if($this->model->dropItm($place,$cfg["hexLen"],$inom,$item_["col_hex"]))
                        echo "1";
                    else
                        echo "0";
                }
            }
        }
    }

    public function action_buy()
    {
        if(!empty($_GET["get"]))
        {
            $inom = (int)$_GET["get"];
            $harm_ = "build/muonline/_dat/items/harmony.php";
            if(file_exists($harm_))
                require $harm_;
            else
                $harm = array();

            if(file_exists("build/muonline/_dat/items/items.php"))
                require "build/muonline/_dat/items/items.php";
            else
                $item = array();

            $cfg = Configs::readCfg("webshop","muonline");
            $emptyItem = str_pad("", $cfg["hexLen"],"F",STR_PAD_BOTH);

            $item_ = $this->model->getInfo($inom);

            if($item_["col_hex"]!=$emptyItem)
            {
                $user = $this->model->aboutUser($_SESSION["mwcuser"]);
                if($user["mwc_bankZ"]>=$item_["col_prise"])
                {
                    $_item = rItem::Read($item_["col_hex"],$harm);
                    $wh = $this->model->getWH($_SESSION["mwcuser"],$cfg["hexLen"]);
                    $place = rItem::search($wh,$_item["x"],$_item["y"],$item,$cfg["hexLen"],120);

                    if($place>=0)
                    {
                        if($this->model->buyItm($place,$cfg["hexLen"],$inom,$item_["col_hex"]))
                            echo ($user["mwc_bankZ"]-$item_["col_prise"]);
                        else
                            echo "0";
                    }
                }
            }
        }
    }


}