<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 31.10.2015
 *
 **/
class m_item extends MuonlineUser
{
    /**
     * @param $hex хекс
     * @param $harm база хармони опций
     * @param int $type тип отображения
     * @param int $inx
     * @param string $saddr
     * @return string
     */
    public function itemShow($hex,$harm,$type=0,$inx=0,$saddr="",$iaddr="")
    {
        if($inx == 0)
            $item = rItem::Read($hex,$harm); //читаем вещь
        else
            $item = $hex;

        if (isset($item["isDivine"]) && $item["isDivine"] == 1)
        {
            $name_class = "I_Divine_l";
        }
        if (isset($item["isJewel"]) && $item["isJewel"] == 1)
        {
            $name_class = "I_Normal7_l";
        }
        if (!empty($item["exc"]) && is_array($item["exc"]))
        {
            $wings = array(0,1,2,3,4,5,6,7,8,9,36,37,38,39,40,41,42,43,49,50,130,131,132,133,134,135,262,263,264,265); //винги в 12 группе
            if(($item["group"] == 12 && in_array($item["id"],$wings)) OR ($item["group"]==13 && $item["id"]==30))
            {
                $name_class = "I_Normal_l";
            }
            else
            {
                $item["name"] = "Excellent ".$item["name"];
                $name_class = "I_Excellent_l";
            }
        }

        if(!isset($name_class) or $name_class == "I_Normal_l")
        {
            if ($item["level"]>6)
                $name_class = "I_Normal7_l";
            else if($item["level"]>2)
                $name_class = "I_Normal3_l";
            else
                $name_class = "I_Normal_l";
        }

        if (isset($item["level"]) && $item["level"]>0)
            $item["name"].=" +".$item["level"];

        if(isset($item["anc1"]) && $item["anc1"]!="no" && strlen($item["anc1"])>2)
        {
            $item["name"] = $item["anc1"]." ".$item["name"];
            $name_class = "I_Ancient";
        }
        else if(isset($item["anc2"]) && $item["anc2"]!="no" && strlen($item["anc2"])>2)
        {
            $item["name"] = $item["anc2"]." ".$item["name"];
            $name_class = "I_Ancient";
        }

        if(isset($item["sockHex"]) && $item["sockHex"] != "FFFFFFFFFF" && $item["sockHex"] != "0000000000")
        {
            $name_class = "I_Socket";
            $item["ispvp"]=0;
        }
        elseif(isset($item["elemental"]))
        {
            $name_class = "I_Socket";
        }
        $d_caption = "<li class='{$name_class}'>{$item["name"]}</li>";

        if($type == 1 && isset($item["img"]))
        {
            if (file_exists("{$iaddr}/{$item["img"]}.gif"))
                $d_caption.="<li><img src='{$saddr}/{$item["img"]}.gif' border='0'></li>";
            else if (file_exists("{$iaddr}/{$item["img"]}.png"))
                $d_caption.="<li><img src='{$saddr}/{$item["img"]}.png' border='0'></li>";
            else
                $d_caption.="<li>{$item["img"]}.gif/png</li>";

        }

        if (file_exists("{$iaddr}/{$item["img"]}.gif"))
            $img="<img src='{$saddr}/{$item["img"]}.gif' border='0'>";
        else if (file_exists("{$iaddr}/{$item["img"]}.png"))
            $img="<img src='{$saddr}/{$item["img"]}.png' border='0'>";
         else
             $img="<li>{$item["img"]}.gif/png</li>";


        //region hands & dmg
        if ($item["group"]<6)
        {
            /* if($item["x"]<2) $d_caption.= "<li class='I_Options'>One-Handed Damage: {$item["minDmg"]} ~ {$item["maxDmg"]}</li>";
             else $d_caption.= "<li class='I_Options'>Two-Handed Damage: {$item["minDmg"]} ~ {$item["maxDmg"]}</li>";*/
            $d_caption.= "<li class='I_Options'>Damage: {$item["minDmg"]} ~ {$item["maxDmg"]}</li>";
        }
        //endregion

        //region speed & Requirements
        if(isset($item["speed"]) && $item["speed"]>0)
        {
            $d_caption.="<li>Attack speed:{$item["speed"]}</li>";
        }
        if(isset($item["defence"]) && $item["defence"]>0)
        {
            $d_caption.="<li>Defense:{$item["defence"]}</li>";
        }

        if (!isset($item["maxDur"]) || (int)$item["maxDur"] == 0)
        {
            if (isset($item["curDur"]) && (int)$item["curDur"] > 0)
                $d_caption.="Durability:[{$item["curDur"]}]";
        }
        else
        {
            if($item["maxDur"]>255)
                $item["maxDur"] = 255;
            $d_caption.="Durability:[{$item["curDur"]}/{$item["maxDur"]}]";
        }

        if(isset($item["str"]))
        {
            $d_caption.="<li>Strength Requirement:{$item["str"]}</li>";
        }
        if(isset($item["agi"]))
        {
            $d_caption.="<li>Agility Requirement:{$item["agi"]}</li>";
        }
        if(isset($item["cmd"]))
        {
            $d_caption.="<li>Leadership Requirement:{$item["cmd"]}</li>";
        }
        if(isset($item["ene"]))
        {
            $d_caption.="<li>Energy Requirement:{$item["ene"]}</li>";
        }

        if(isset($item["lvlreq"]))
        {
            $d_caption.="<li>Level Requirement:{$item["lvlreq"]}</li>";
        }
        //endregion

        if(isset($item["equipment"]) && trim($item["equipment"][0]) != "no")
        {
            foreach($item["equipment"] as $p)
            {
                $d_caption.="<li class='I_ErEnqip'>$p</li>";
            }
        }

        if(isset($item["wizardy"]))
        {
            if ($item["group"]!=2)
                $d_caption.="<li class='I_WizOpt'>Wizardy dmg rise:{$item["wizardy"]}%</li>";
            else
                $d_caption.="<li class='I_WizOpt'>Increase pet attack as {$item["wizardy"]}%</li>";
        }


        if ( $item["ispvp"]>0)
        {
            $d_caption.="<li class='I_AddhzOpt'>";
            foreach($item["pvp"] as $p)
            {
                if(substr($p,-3)=="+ 0")
                    $p = substr($p,0,-3);
                $d_caption.="$p<br>";
            }
            $d_caption.="</li>";
        }

        if(isset($item["harmony"]))
        {
            $d_caption.="<li class='I_HarmOpt'>{$item["harmony"]}</li>";
        }

        if(isset($item["isSkill"]))
        {
            $d_caption.="<li class='I_Skill'>{$item["skillname"]}</li>";
        }

        if(!empty($item["isLuck"])>0)
        {
            $d_caption.="<li class='I_Options'>Luck(succes rate of Jewel of soul +25%)<br>
        Luck(critical damage rate +5%)</li>";
        }

        if(isset($item["lifeOpt"]))
        {
            switch($item["group"])
            {
                case 0:
                case 1:
                case 2:
                case 3:
                case 4: $d_caption.="<li class='I_Options'>Additional Damage +{$item["lifeOpt"]}%</li>";break;
                case 5: $d_caption.="<li class='I_Options'>Wizardy Damage +{$item["lifeOpt"]}%</li>";break;
                case 6:
                case 7:
                case 8:
                case 9:
                case 10:
                case 11: $d_caption.="<li class='I_Options'>Additional defence +{$item["lifeOpt"]}%</li>";break;
                case 12:
                case 13: $d_caption.="<li class='I_Options'>{$item["lifeOpt"]}</li>";break;
            }

        }

        if(isset($item["anc1"]) && $item["anc1"]!="no" && strlen($item["anc1"])>2)
        {
            if(isset($item["opt1"]))
            {
                $d_caption.="<li class='I_Options'>{$item["opt1"]}</li>";
            }
        }
        elseif(isset($item["anc2"]) && $item["anc2"]!="no" && strlen($item["anc2"])>2)
        {
            if(isset($item["opt2"]))
            {
                $d_caption.="<li class='I_Options'>{$item["opt2"]}</li>";
            }
        }

        if(!empty($item["exc"]) && is_array($item["exc"]))
        {
            $d_caption.="<li class='I_ExcOpt'>";
            foreach($item["exc"] as $p)
            {
                $d_caption.="$p<br>";
            }
            $d_caption.="</li>";
        }

        if(isset($item["sockHex"]) && $item["sockHex"] != "FFFFFFFFFF" && $item["sockHex"] != "0000000000")
        {
            $d_caption.=" <li class=\"I_SocketOpt\">Socket item option info</li><li class=\"I_Options\">".Sockets($item["sockHex"])."</li>";
        }
        elseif(isset($item["elemental"]))
        {
            $d_caption.=" <li class=\"I_SocketOpt\">Socket item option info</li><li class=\"I_Options\">{$item["elemental"]}</li>";
        }
        if(isset($item["descr"]))
        {
            $d_caption.="<li class='I_Options'>{$item["descr"]}</li>";
        }

        if (is_array($item))
            $itm = $item["hex"];
        else
            $itm = $item;

        if($type!=2)
            return "<div class=\"item_bg\"> <ul>{$d_caption}</ul></div>";
        else
            return $img;
    }

}