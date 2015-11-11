<?php
/**
 * MuWebCloneEngine
 * Created by epmak
 * version: 1.6
 * класс для обработки вещей
 */

//region Класс чтения вещей

class rItem
{
    static private $divine = array(
        0=>19,
        2=>13,
        4=>18,
        5=>array(10=>1,36=>1)
    );
    static private $jewels = array(
        12=>array(15=>1,30=>1,31=>1,136=>1,137=>1,138=>1,139=>1,140=>1,141=>1,150=>1),
        14=>array(13=>1,14=>1,16=>1,22=>1,31=>1,42=>1,160=>1,161=>1)
    );

    static private $elementals = array(0=>"Unknown?",1=>"Fire element",2=>"Water element",3=>"Earth element",4=>"Wind element",5=>"Darkness element");


    /**
     * функция принимает хекс отдельно взятой вещи
     * @param string $hex
     * @param integer $size: 16, 32,64 размер(сезон) вещи
     * @param null $hbase база хармони (сгенерированная)
     * @return array|string
     */
    static public function Read($hex,$hbase=null,$size = null)
    {
        $all = $size;
        if (!isset($all))
        {
            $all = strlen(trim($hex));
        }

        switch($all)
        {
            case 16: return self::getHilight(self::read16($hex,$hbase));
            case 32: return self::getHilight(self::read32($hex,$hbase));
            case 64: return self::getHilight(self::read64($hex,$hbase));
            default: return "not supported parameters!";
        }
    }

    /**
     * чтение вещей певого сезона
     * @param string $hex
     * @param $hbase
     * @return string
     */
    static private function read16($hex,$hbase)
    {
        return "under costruction!";
    }

    /**
     * чтение вещей 2-5 сезонов
     * @param string $hex
     * @param $hbase
     * @return array
     */
    static private function read32($hex,$hbase)
    {
        //region получение данных из хекса
        $result["hex"] = $hex;//хекс код
        $result["id"] = self::dehex($hex,0,2);
        $result["intopt"] = self::dehex($hex,2,2); //лайф офпции, мана, скилл и т.п.
        $result["group"] = self::dehex($hex,18,1);
        $result["ispvp"] = self::dehex($hex,19,1);
        $result["serial1"] = substr($hex,6,8);
        $result["excnum"] = self::dehex($hex,14,2); //екселлентные опции циферкой
        $result["excnum_"] = $result["excnum"];
        $result["curDur"] = self::dehex($hex,4,2); //текущая прочность
        $result["harmonyOpt"] = self::dehex($hex,20,1); //опция хармони
        $result["harmonyLvl"] = self::dehex($hex,21,1); //уровень опции хармони
        $result["sockHex"] = strtoupper(substr($hex,22));//сокетовые опции
        $result["ancnum"] = self::dehex($hex,17,1); //эншент опция цифрой
        //endregion

        //до рассчета обязательно запускай сначала лайф адд, потом экселлент!
        $result = self::getOtions($result); //получение уровня, скилла, лака вещи

        switch($result["group"])
        {
            case 0:
            case 1:
            case 2:
            case 3:
            case 4:
            case 5: $result = self::readW($result,$hbase);break;
            case 6:
            case 7:
            case 8:
            case 9:
            case 10:
            case 11: $result = self::readArmor($result,$hbase); break;
            case 12: $result = self::read12($result,$hbase); break;
            case 13: $result = self::read13($result,$hbase); break;
            case 14: $result = self::read14($result,$hbase); break;
            case 15: $result = self::readScrolls($result); break;
        }

        return $result;
    }

    /**
     * чтение вещей 6-x сезонов
     * @param string $hex
     * @param $hbase
     * @return string
     */
    static private function read64($hex,$hbase)
    {
        $result = self::read32(substr($hex,0,32),$hbase);
        $result["hex"] = $hex;//хекс код
        $result["serial2"] = substr($hex,32,8);;//хекс код
        return $result;
    }

    /**
     * функция чтения оружия
     * @param array $itemInfo
     * @param       $hbase
     * @return array|mixed
     */
    static private function readW($itemInfo,$hbase)
    {
        $got = self::readfile("{$itemInfo["group"]}.{$itemInfo["id"]}");

        $itemInfo["name"]= htmlspecialchars(str_replace("\n",'',$got[0]),ENT_QUOTES);
        $f = array($got[3],$got[6],$got[7]);
        $itemInfo["x"] = (int)$got[1];
        $itemInfo["y"] = (int)$got[2];

        $itemInfo = self::getLifeOpt($itemInfo,4); //лайф опции
        $itemInfo = self::getStats($itemInfo,$f); //требования силы, аги
        $itemInfo = self::getExcellent($itemInfo,1);

        $itemInfo["maxDur"] = $got["11"] + self::CalcDur($itemInfo["level"]);

        $dmg = self::GetDmg($itemInfo);
        $itemInfo["minDmg"] = $dmg + $got[4];
        $itemInfo["maxDmg"] = $dmg + $got[5];
        if ($got[14]>0)
        {
            $itemInfo["wizardy"] = (int)($itemInfo["level"]*4 + $got[14]/2);
        }

        if(isset($itemInfo["exc"]))
        {
            $itemInfo["minDmg"] += 30;
            $itemInfo["maxDmg"] += 30;
            $itemInfo["maxDur"] += 15;
            $itemInfo["str"] = intval($itemInfo["str"] + $itemInfo["str"]*0.132);
            $itemInfo["agi"] = intval($itemInfo["agi"] + $itemInfo["agi"]*0.132);
            if (isset($itemInfo["wizardy"]))
                $itemInfo["wizardy"] += intval(($itemInfo["wizardy"]/100)*14,5);
        }
        $itemInfo["equipment"] = self::getEq($got[13]);
        $itemInfo["speed"] = (int)$got[12];
        $itemInfo["equipmentn"] = $got[18];
        if ($got[15]!="no") //pvp
            $itemInfo["pvp"] = self::getEq($got[15]);


        if($got[16]!="no|no" && $itemInfo["ancnum"]>0) //ancient
        {
            $t = self::ancopt($got[16],$itemInfo["ancnum"]);
            if(is_array($t))
            {
                $itemInfo+=$t;
            }
        }
        if (isset($itemInfo["isSkill"]))
        {
            if($got[17]!="no" && (int)$got[17]==0) //skill
            {
                $itemInfo["skillname"]="Weapon skill: ".$got[17];
            }
            else
                $itemInfo["skillname"]="Have specific skill";
        }

        if ($itemInfo["harmonyOpt"]>0 && $itemInfo["harmonyLvl"]>=0 && $hbase!=null)
        {
            if($itemInfo["group"] == 5)
                $gr = 2;
            else
                $gr = 1;

            if (isset($hbase[$gr][$itemInfo["harmonyOpt"]]["name"]) && isset($hbase[$gr][$itemInfo["harmonyOpt"]][$itemInfo["harmonyLvl"]]["req"]))
                $itemInfo["harmony"] = $hbase[$gr][$itemInfo["harmonyOpt"]]["name"]." +".$hbase[$gr][$itemInfo["harmonyOpt"]][$itemInfo["harmonyLvl"]]["req"];
        }

        $itemInfo["img"]="{$itemInfo["group"]}{$itemInfo["id"]}0"; //group +id+lvl

        return $itemInfo;
    }

    /**
     * возвращает нужную анц опцию из списка
     * @param $ancstr список
     * @param $num цифра опции
     * @return mixed|string
     */
    static private function ancopt($ancstr,$num)
    {
        $acn = explode("|",$ancstr);
        if ($acn[0]!="no" && ($num == 5 || $num == 9 ))
        {
            $opt["anc1"] = str_replace("\n",'',$acn[0]);
            if($num == 9)
            {
                $opt["opt1"]= "+10 ancient bonus";
            }
            else
            {
                $opt["opt1"]= "+5 ancient bonus";
            }
        }
        elseif ($acn[1]!="no" && ($num == 6 || $num == 10 ))
        {
            $opt["anc1"] = str_replace("\n",'',$acn[1]);
            if($num == 10)
            {
                $opt["opt1"]= "+10 ancient bonus";
            }
            else
            {
                $opt["opt1"]= "+5 ancient bonus";
            }
        }
        else
            return false;

        return $opt;
    }

    /**
     * чтение арморов
     * @param $itemInfo
     * @param $hbase
     * @return mixed
     */
    static private function readArmor($itemInfo,$hbase)
    {
        $got = self::readfile("{$itemInfo["group"]}.{$itemInfo["id"]}");

        $itemInfo["x"] = (int)$got[1];
        $itemInfo["y"] = (int)$got[2];
        $itemInfo["name"]= htmlspecialchars(str_replace("\n",'',$got[0]),ENT_QUOTES);
        $f = array($got[8],$got[9],$got[10]);

        $itemInfo = self::getLifeOpt($itemInfo,4); //лайф опции
        $itemInfo = self::getStats($itemInfo,$f); //требования силы, аги
        $itemInfo = self::getExcellent($itemInfo,2);
        if ($itemInfo["group"] !=6)//если не щиты
        {
            if (isset($itemInfo["exc"]))
                $itemInfo["defence"] = self::getDef($itemInfo,$f,true)+$got[3];
            else
                $itemInfo["defence"] = self::getDef($itemInfo,$f)+$got[3];
            $itemInfo["speed"] = (int)$got[4];
        }
        else
        {
            $itemInfo["defence"] = self::getDef($itemInfo,$f)+$got[3];
                //$got[3] * $got[8];   //def = +def*itemlevel  for shields
        }

        $itemInfo["maxDur"] = $got["11"] + self::CalcDur($itemInfo["level"]);

        if(isset($itemInfo["exc"]))
        {
            $itemInfo["maxDur"] = $itemInfo["maxDur"] + 15;
            $itemInfo["str"] = intval($itemInfo["str"] + $itemInfo["str"]*0.132);
            $itemInfo["agi"] = intval($itemInfo["agi"] + $itemInfo["agi"]*0.132);
        }
        $itemInfo["equipment"] = self::getEq($got[5]);


         if ($got[6]!="no" && $itemInfo["ispvp"]>0) //pvp
        $itemInfo["pvp"] = self::getEq($got[6]);


        if($got[7]!="no|no" && $itemInfo["ancnum"]>0) //ancient
        {
            $t = self::ancopt($got[7],$itemInfo["ancnum"]);
            if(is_array($t))
            {
                $itemInfo+=$t;
            }
        }

        if(isset($itemInfo["isSkill"])) //skill
        {
            $itemInfo["skillname"]="Have specific skill";
        }

        if ($itemInfo["harmonyOpt"]>0 && $itemInfo["harmonyLvl"]>=0 && $hbase!=null)
        {
            if (isset($hbase[3][$itemInfo["harmonyOpt"]]["name"]) && isset($hbase[3][$itemInfo["harmonyOpt"]][$itemInfo["harmonyLvl"]]["req"]))
                $itemInfo["harmony"] = $hbase[3][$itemInfo["harmonyOpt"]]["name"]." +".$hbase[3][$itemInfo["harmonyOpt"]][$itemInfo["harmonyLvl"]]["req"];
        }

        $itemInfo["img"]="{$itemInfo["group"]}{$itemInfo["id"]}0"; //group +id+lvl
        $itemInfo["equipmentn"] = $got[13];
        return $itemInfo;
    }

    /**
     * чтение 12 группы (винги и проч)
     * @param $itemInfo
     * @param $hbase
     * @return mixed
     */
    static private function read12($itemInfo,$hbase)
    {
        $got = self::readfile("{$itemInfo["group"]}.{$itemInfo["id"]}");
        $itemInfo["x"] = (int)$got[1];
        $itemInfo["y"] = (int)$got[2];
        $itemInfo["name"]= htmlspecialchars(str_replace("\n",'',$got[0]),ENT_QUOTES);

        $wings = array(0,1,2,3,4,5,6,6,36,37,38,39,40,41,42,43,49,50,130,131,132,133,134,135,262,263,264,265); //винги в 12 группе
        $seeds = array(60,61,62,63,64,65,100,101,102,103,104,105,106,107,108,109,110,111,112,113,114,115,116,117,118,119,120,121,122,123,124,125,126,127,128,129); //сокеты
        $elementalSys = array(5,200,201,221,231,241,251); //05 errtal of gale элементальные моменты
        $itemInfo["descr"] = $got[17]; //то, что может вписать пользователь для отображения инфы про вещи
        $itemInfo["equipmentn"] = $got[16];
        $thirdWings = array(36,37,38,39,40,41,43,50);
        $firstwings = array(0,1,2);

        if($itemInfo["excnum"]>127) //подозрение на винги 2.5
        {
            $itemInfo["excnum"]-=128;
            if ($itemInfo["id"] == 6) // cloack of death
            {
                $f = array($got[3],$got[8]);
                $itemInfo["name"] = "Cloak of Death";
                $itemInfo["img"]="cloakofdeath";
                $itemInfo["x"] = 2;
                $itemInfo["y"] = 3;
                $itemInfo = self::getLifeOpt($itemInfo,4,1);
                if (isset($itemInfo["exc"]))
                    $itemInfo["defence"] = self::getDef($itemInfo,$f,true);
                else
                    $itemInfo["defence"] = self::getDef($itemInfo,$f);

                $itemInfo["maxDur"] = $got["10"] + self::CalcDur($itemInfo["level"]);
                $itemInfo["equipment"] = array("Can be equipment by Rage Fighter","Can be equipment by Dark Lord");
                $itemInfo["lvlreq"] = 230;
            }
            if ($itemInfo["id"] == 7)
            {
                $f = array($got[3],$got[8]);
                $itemInfo["name"] = "Wings of Chaos";
                $itemInfo["img"]="wingsofchaos";
                $itemInfo["x"] = 3;
                $itemInfo["y"] = 2;
                $itemInfo = self::getLifeOpt($itemInfo,4,1);
                if (isset($itemInfo["exc"]))
                    $itemInfo["defence"] = self::getDef($itemInfo,$f,true);
                else
                    $itemInfo["defence"] = self::getDef($itemInfo,$f);

                $itemInfo["maxDur"] = $got["10"] + self::CalcDur($itemInfo["level"]);
                $itemInfo["equipment"] = array("Can be equipment by Magic Gladiator","Can be equipment by Blade Knight");
                $itemInfo["lvlreq"] = 230;
            }
            if ($itemInfo["id"] == 8)
            {
                $f = array($got[3],$got[8]);
                $itemInfo["name"] = "Wings of Magic";
                $itemInfo["img"]="wingsofmagic";
                $itemInfo["x"] = 3;
                $itemInfo["y"] = 2;
                $itemInfo = self::getLifeOpt($itemInfo,4,1);
                if (isset($itemInfo["exc"]))
                    $itemInfo["defence"] = self::getDef($itemInfo,$f,true);
                else
                    $itemInfo["defence"] = self::getDef($itemInfo,$f);

                $itemInfo["maxDur"] = $got["10"] + self::CalcDur($itemInfo["level"]);
                $itemInfo["equipment"] = array("Can be equipment by Magic Gladiator","Can be equipment by Soul Master","Can be equipment by Bloody Summoner");
                $itemInfo["lvlreq"] = 230;
            }
            if ($itemInfo["id"] == 9)
            {
                $f = array($got[3],$got[8]);
                $itemInfo["name"] = "Wings of Life";
                $itemInfo["img"]="wingsoflife";
                $itemInfo["x"] = 4;
                $itemInfo["y"] = 3;
                $itemInfo = self::getLifeOpt($itemInfo,4,1);
                if (isset($itemInfo["exc"]))
                    $itemInfo["defence"] = self::getDef($itemInfo,$f,true);
                else
                    $itemInfo["defence"] = self::getDef($itemInfo,$f);

                $itemInfo["maxDur"] = $got["10"] + self::CalcDur($itemInfo["level"]);
                $itemInfo["equipment"] = array("Can be equipment by Muse Elf");
                $itemInfo["lvlreq"] = 230;
            }
            $itemInfo = self::getExcellent($itemInfo,6);
        }
        else if(in_array($itemInfo["id"], $wings) && !($itemInfo["id"] == 5 && $itemInfo["harmonyLvl"]>0)) //если это винги
        {
            $itemInfo = self::getLifeOpt($itemInfo,4,1);

            if(!in_array($itemInfo["id"],$thirdWings))
            {
                $itemInfo = self::getExcellent($itemInfo,3);
            }
            else
            {
                $itemInfo = self::getExcellent($itemInfo,5);
            }

            $f = array($got[3],$got[8]);

            if (isset($itemInfo["exc"]))
                $itemInfo["defence"] = self::getDef($itemInfo,$f,true);
            else
                $itemInfo["defence"] = self::getDef($itemInfo,$f);

            if(in_array($itemInfo["id"],$firstwings))
            {
                $itemInfo["defence"]=$got[3]+$itemInfo["level"]*2;
            }
            elseif(in_array($itemInfo["id"],$thirdWings))
            {
                $itemInfo["defence"]=$got[3]+$itemInfo["level"]*4;
            }
            else
            {
                $itemInfo["defence"]=$got[3]+$itemInfo["level"]*2;
            }

            $itemInfo["maxDur"] = $got["10"] + self::CalcDur($itemInfo["level"]);
            $itemInfo["equipment"] = self::getEq($got[4]);


            if(in_array($itemInfo["id"],$firstwings))
                $itemInfo["lvlreq"] = $got["11"]+ ($itemInfo["level"]*4); //1st & 3rd wings
            elseif(in_array($itemInfo["id"],$thirdWings))
                $itemInfo["lvlreq"] = $got["11"]; //1st & 3rd wings
            else
                $itemInfo["lvlreq"] = $got["11"]+ ($itemInfo["level"]*3)+(10+$itemInfo["level"]* 3); //2nd wings

            if($itemInfo["lvlreq"]>400)
                $itemInfo["lvlreq"]=400;

            if($itemInfo["ispvp"]!=0)
            {
             if ($got[6]!="no") //pvp
            $itemInfo["pvp"] = self::getEq($got[6]);
            }

            if(isset($itemInfo["isSkill"])) //skill
            {
                $itemInfo["skillname"]="Have specific skill";
            }

            $itemInfo["img"]="{$itemInfo["group"]}{$itemInfo["id"]}0"; //group +id+lvl
        }
        elseif(in_array($itemInfo["id"],$seeds)) //если сокеты
        {
            $itemInfo = self::Seeds($itemInfo);
        }
        elseif(in_array($itemInfo["id"],$elementalSys))//books & errtels elemental
        {
            if($itemInfo["id"] == 5)
            {
                $itemInfo["id"] = 261;
                $itemInfo["name"] = "Errtel of Gale";
                $itemInfo["x"] = 3;
                $itemInfo["y"] = 3;
            }
            $itemInfo["img"]="{$itemInfo["group"]}{$itemInfo["id"]}0"; //group +id+lvl
            $itemInfo["name"] = $itemInfo["name"]." ".self::$elementals[$itemInfo["harmonyLvl"]]; //пририсовываем знак элемента

            $i=0;
            $sockets = "";
            $j =1;

            $elList = array(
                0=>"Errtel of Anger mounted",
                1=>"Errtel of Blessing mounted",
                2=>"Errtel of Integraty mounted",
                3=>"Errtel of Divinity mounted",
                4=>"Errtel of Gale mounted"
            );
            while ($i<10)
            {
                if(substr($itemInfo["sockHex"],$i,2) == "FE")
                    $sockets.="<br> Empty slot";
                else if(substr($itemInfo["sockHex"],$i,2) != "FF")
                {
                    $sockets.="<br>".$elList[$j-1];
                }

               // if ( isset($elList[substr($itemInfo["sockHex"],$i,2)])) $sockets.="<br>".$elList[substr($itemInfo["sockHex"],$i,2)];
                $i+=2;
                $j++;
            }


            $itemInfo["elHex"]=$itemInfo["sockHex"];
            $itemInfo["sockHex"]="FFFFFFFFFF";

            $itemInfo["elemental"] = "";
            switch($itemInfo["id"])
            {
                case 221:
                    if(substr($itemInfo["elHex"],1,1)!="F")
                    {
                        $itemInfo["elemental"].="<br>Elemental Dmg lvl ".hexdec(substr($itemInfo["elHex"],0,1));
                    }
                    if(substr($itemInfo["elHex"],3,1)!="F")
                    {
                        $itemInfo["elemental"].="<br>Attack Against ".self::$elementals[hexdec(substr($itemInfo["elHex"],3,1))]." lvl ".hexdec(substr($itemInfo["elHex"],2,1));
                    }
                    if(substr($itemInfo["elHex"],5,1)!="F")
                    {
                        $opt[1] = "(in PvP)";
                        $opt[2] = "(in Raids)";
                        $itemInfo["elemental"].="<br>Elemental Attack Dmg ".$opt[hexdec(substr($itemInfo["elHex"],5,1))]." lvl ".hexdec(substr($itemInfo["elHex"],4,1));
                    }
                    if(substr($itemInfo["elHex"],7,1)!="F")
                    {
                        $opt[1] = "Ranged Elemental Attack Dmg (in PvP)";
                        $opt[2] = "Melee Elemental Attack Dmg (in PvP)";
                        $itemInfo["elemental"].="<br>".$opt[hexdec(substr($itemInfo["elHex"],7,1))]." lvl ".hexdec(substr($itemInfo["elHex"],6,1));
                    }
                    if(substr($itemInfo["elHex"],9,1)!="F")
                    {
                        $opt[1] = "Elemental Critical Rate (in PvP)";
                        $opt[2] = "Elemental Critical Rate (in Raids)";
                        $itemInfo["elemental"].="<br>".$opt[hexdec(substr($itemInfo["elHex"],9,1))]." lvl ".hexdec(substr($itemInfo["elHex"],8,1));
                    }
                    break;
                case 231:
                    if(substr($itemInfo["elHex"],1,1)!="F")
                    {
                        $itemInfo["elemental"].="<br>Elemental Defense lvl ".hexdec(substr($itemInfo["elHex"],0,1));
                    }
                    if(substr($itemInfo["elHex"],3,1)!="F")
                    {
                        $itemInfo["elemental"].="<br>Defense Against ".self::$elementals[hexdec(substr($itemInfo["elHex"],3,1))]." lvl ".hexdec(substr($itemInfo["elHex"],2,1));
                    }
                    if(substr($itemInfo["elHex"],5,1)!="F")
                    {
                        $opt[1] = "(in PvP)";
                        $opt[2] = "(in Raids)";
                        $itemInfo["elemental"].="<br>Elemental Defense  ".$opt[hexdec(substr($itemInfo["elHex"],5,1))]." lvl ".hexdec(substr($itemInfo["elHex"],4,1));
                    }
                    if(substr($itemInfo["elHex"],7,1)!="F")
                    {
                        $opt[1] = "Ranged Elemental Defense (in PvP)";
                        $opt[2] = "Melee Elemental Defense (in PvP)";
                        $itemInfo["elemental"].="<br>".$opt[hexdec(substr($itemInfo["elHex"],7,1))]." lvl ".hexdec(substr($itemInfo["elHex"],6,1));
                    }
                    if(substr($itemInfo["elHex"],9,1)!="F")
                    {
                        $opt[1] = "Elemental Dmg (in PvP)";
                        $opt[2] = "Elemental Dmg (in Raids)";
                        $itemInfo["elemental"].="<br>".$opt[hexdec(substr($itemInfo["elHex"],9,1))]." lvl ".hexdec(substr($itemInfo["elHex"],8,1));
                    }
                    break;
                case 241:
                    $itemInfo["elemental"] = "";
                    if(substr($itemInfo["elHex"],1,1)!="F")
                    {
                        $itemInfo["elemental"].="<br>Elemental Attack Succes Rate lvl ".hexdec(substr($itemInfo["elHex"],0,1));
                    }
                    if(substr($itemInfo["elHex"],3,1)!="F")
                    {
                        $itemInfo["elemental"].="<br>Attack Against ".self::$elementals[hexdec(substr($itemInfo["elHex"],3,1))]." lvl ".hexdec(substr($itemInfo["elHex"],2,1));
                    }
                    if(substr($itemInfo["elHex"],5,1)!="F")
                    {
                        $opt[1] = "(in PvP)";
                        $opt[2] = "(in Raids)";
                        $itemInfo["elemental"].="<br>Elemental Attack Dmg  ".$opt[hexdec(substr($itemInfo["elHex"],5,1))]." lvl ".hexdec(substr($itemInfo["elHex"],4,1));
                    }
                    if(substr($itemInfo["elHex"],7,1)!="F")
                    {
                        $opt[1] = "Ranged Elemental Attack Dmg (in PvP)";
                        $opt[2] = "Melee Elemental Attack Dmg (in PvP)";
                        $itemInfo["elemental"].="<br>".$opt[hexdec(substr($itemInfo["elHex"],7,1))]." lvl ".hexdec(substr($itemInfo["elHex"],6,1));
                    }
                    if(substr($itemInfo["elHex"],9,1)!="F")
                    {
                        $opt[1] = "Elemental Dmg (in PvP)";
                        $opt[2] = "Elemental Dmg (in Raids)";
                        $itemInfo["elemental"].="<br>".$opt[hexdec(substr($itemInfo["elHex"],9,1))]." lvl ".hexdec(substr($itemInfo["elHex"],8,1));
                    }
                    break;
                case 251:
                    $itemInfo["elemental"] = "";
                    if(substr($itemInfo["elHex"],1,1)!="F")
                    {
                        $itemInfo["elemental"].="<br>Defense Success Rate lvl ".hexdec(substr($itemInfo["elHex"],0,1));
                    }
                    if(substr($itemInfo["elHex"],3,1)!="F")
                    {
                        $itemInfo["elemental"].="<br>Defense Against ".self::$elementals[hexdec(substr($itemInfo["elHex"],3,1))]." lvl ".hexdec(substr($itemInfo["elHex"],2,1));
                    }
                    if(substr($itemInfo["elHex"],5,1)!="F")
                    {
                        $opt[1] = "(in PvP)";
                        $opt[2] = "(in Raids)";
                        $itemInfo["elemental"].="<br>Elemental Defense ".$opt[hexdec(substr($itemInfo["elHex"],5,1))]." lvl ".hexdec(substr($itemInfo["elHex"],4,1));
                    }
                    if(substr($itemInfo["elHex"],7,1)!="F")
                    {
                        $opt[1] = "Ranged Elemental Defense (in PvP)";
                        $opt[2] = "Melee Elemental Defense (in PvP)";
                        $itemInfo["elemental"].="<br>".$opt[hexdec(substr($itemInfo["elHex"],7,1))]." lvl ".hexdec(substr($itemInfo["elHex"],6,1));
                    }
                    if(substr($itemInfo["elHex"],9,1)!="F")
                    {
                        $opt[1] = "Elemental Damage Absorb (in PvP)";
                        $opt[2] = "Elemental Damage Absorb (in Raids)";
                        $itemInfo["elemental"].="<br>".$opt[hexdec(substr($itemInfo["elHex"],9,1))]." lvl ".hexdec(substr($itemInfo["elHex"],8,1));
                    }
                    break;
                case 261:
                    $itemInfo["elemental"] = "Elemental Debuff Succeess Rate";
                    break;
                default:
                    if(!empty($sockets))
                        $itemInfo["elemental"] = $sockets;
                    else
                        unset($itemInfo["elemental"]);
            }
            unset($itemInfo["curDur"]);
        }
        else
        {
            $itemInfo["img"]="{$itemInfo["group"]}{$itemInfo["id"]}{$itemInfo['level']}"; //group +id+lvl
            switch ($itemInfo["id"])
            {
                case 11:
                    switch ($itemInfo["level"])
                    {
                        case 0 or "": $opt["name"]="Summoning Goblin";break;
                        case 1: $itemInfo["name"]="Summoning Stone Golim";break;
                        case 2: $itemInfo["name"]="Summoning Assasin";break;
                        case 3: $itemInfo["name"]="Summoning Bali";break;
                        case 4: $itemInfo["name"]="Summoning Soldier";break;
                        case 5: $itemInfo["name"]="Summoning Yeti";break;
                        case 6: $itemInfo["name"]="Summoning Dark Knight";break;
                    }

                    $itemInfo["img"]="{$itemInfo["group"]}{$itemInfo["id"]}{$itemInfo['level']}"; //group +id+lvl
                    unset($itemInfo["level"]);
                    break;
                case 30:
                    if($itemInfo["level"]==1)
                        $itemInfo["name"]="Jewel of Bless mix x20";
                    elseif($itemInfo["level"]==2)
                        $itemInfo["name"]="Jewel of Bless mix x30";
                    else
                        $itemInfo["name"]="Jewel of Bless mix x10";

                    $itemInfo["img"]="{$itemInfo["group"]}{$itemInfo["id"]}7"; //group +id+lvl
                    unset($itemInfo["level"]);
                    break;
                case 31:
                    if($itemInfo["level"]==1)
                        $itemInfo["name"]="Jewel of Soul mix x20";
                    elseif($itemInfo["level"]==2)
                        $itemInfo["name"]="Jewel of Soul mix x30";
                    else
                        $itemInfo["name"]="Jewel of Soul mix x10";
                    $itemInfo["img"]="{$itemInfo["group"]}{$itemInfo["id"]}7"; //group +id+lvl
                    unset($itemInfo["level"]);
                    break;
                case 136:
                    if($itemInfo["level"]==1)
                        $itemInfo["name"]="Jewel of Life mix x20";
                    elseif($itemInfo["level"]==2)
                        $itemInfo["name"]="Jewel of Life mix x30";
                    else
                        $itemInfo["name"]="Jewel of Life mix x10";
                   // $itemInfo["img"]="{$itemInfo["group"]}{$itemInfo["id"]}"; //group +id+lvl
                    unset($itemInfo["level"]);
                    break;
                case 137:
                    if($itemInfo["level"]==1)
                        $itemInfo["name"]="Jewel of Creation mix x20";
                    elseif($itemInfo["level"]==2)
                        $itemInfo["name"]="Jewel of Creation mix x30";
                    else
                        $itemInfo["name"]="Jewel of Creation mix x10";
                  //  $itemInfo["img"]="{$itemInfo["group"]}{$itemInfo["id"]}"; //group +id+lvl
                    unset($itemInfo["level"]);
                    break;
                case 138:
                    if($itemInfo["level"]==1)
                        $itemInfo["name"]="Jewel of Guardian mix x20";
                    elseif($itemInfo["level"]==2)
                        $itemInfo["name"]="Jewel of Guardian mix x30";
                    else
                        $itemInfo["name"]="Jewel of Guardian mix x10";
                  //  $itemInfo["img"]="{$itemInfo["group"]}{$itemInfo["id"]}"; //group +id+lvl
                    unset($itemInfo["level"]);
                    break;
                case 140:
                    if($itemInfo["level"]==1)
                        $itemInfo["name"]="Jewel of Harmony mix x20";
                    elseif($itemInfo["level"]==2)
                        $itemInfo["name"]="Jewel of Harmony mix x30";
                    else
                        $itemInfo["name"]="Jewel of Harmony mix x10";
                   // $itemInfo["img"]="{$itemInfo["group"]}{$itemInfo["id"]}"; //group +id+lvl
                    unset($itemInfo["level"]);
                    break;
                case 141:
                    if($itemInfo["level"]==1)
                        $itemInfo["name"]="Jewel of Chaos mix x20";
                    elseif($itemInfo["level"]==2)
                        $itemInfo["name"]="Jewel of Chaos mix x30";
                    else
                        $itemInfo["name"]="Jewel of Chaos mix x10";
                   // $itemInfo["img"]="{$itemInfo["group"]}{$itemInfo["id"]}"; //group +id+lvl
                    unset($itemInfo["level"]);
                    break;
                case 142:
                    if($itemInfo["level"]==1)
                        $itemInfo["name"]="Jewel of Lower Refining Stone mix x20";
                    elseif($itemInfo["level"]==2)
                        $itemInfo["name"]="Jewel of Lower Refining Stone mix x30";
                    else
                        $itemInfo["name"]="Jewel of Lower Refining Stone mix x10";
                  //  $itemInfo["img"]="{$itemInfo["group"]}{$itemInfo["id"]}"; //group +id+lvl
                    unset($itemInfo["level"]);
                    break;
                case 143:
                    if($itemInfo["level"]==1)
                        $itemInfo["name"]="Jewel of Higher Refining Stone mix x20";
                    elseif($itemInfo["level"]==2)
                        $itemInfo["name"]="Jewel of Higher Refining Stone mix x30";
                    else
                        $itemInfo["name"]="Jewel of Higher Refining Stone mix x10";
                   // $itemInfo["img"]="{$itemInfo["group"]}{$itemInfo["id"]}"; //group +id+lvl
                    unset($itemInfo["level"]);
                    break;
            }
            if(isset($itemInfo["lifeOpt"]))
            {
                $itemInfo["lifeOpt"] = "Automatic HP recovery +".$itemInfo["lifeOpt"];
            }

        }

        return $itemInfo;
    }

    /**
     * @param array $itemInfo
     * @return array
     * Чтение сидов
     */
    static private function Seeds($itemInfo)
    {
        if (!isset($itemInfo["level"]))
            $itemInfo["level"] = 0;

        if (!isset($itemInfo["curDur"]))
            $itemInfo["curDur"] = 0;

        $itemInfo["img"]="{$itemInfo["group"]}{$itemInfo["id"]}{$itemInfo['level']}"; //group +id+lvl
        if($itemInfo["id"] == 60 || $itemInfo["id"] == 100 || $itemInfo["id"] == 106 || $itemInfo["id"] ==112 || $itemInfo["id"] ==118 || $itemInfo["id"] ==124) //fire
        {
            if($itemInfo["id"]>=100)
            {
                $itemInfo["img"]="{$itemInfo["group"]}{$itemInfo["id"]}{$itemInfo['level']}"; //group +id+lvl
            }
            else
            {
                $itemInfo["img"]="{$itemInfo["group"]}{$itemInfo["id"]}0";
            }
            switch($itemInfo["level"])
            {
                    case 0: $itemInfo["lifeOpt"]="(level type) Attack/Wizardy increase"; break;
                    case 1: $itemInfo["lifeOpt"]="Attack speed increas";break;
                    case 2: $itemInfo["lifeOpt"]="Maximum attack/wizardy increase";break;
                    case 3: $itemInfo["lifeOpt"]="Minimum attack/wizardy increase";break;
                    case 4: $itemInfo["lifeOpt"]="Attack/wizardy increase";break;
                    case 5: $itemInfo["lifeOpt"]="Increase AG cost decrease";break;
                    default :$itemInfo["lifeOpt"]="Unknown item class {$itemInfo["curDur"]}-{$itemInfo["level"]}"; break;
            }
        }
        else if($itemInfo["id"] == 61 || $itemInfo["id"] == 101 || $itemInfo["id"] ==107 || $itemInfo["id"] ==113 || $itemInfo["id"] ==119 || $itemInfo["id"] ==125) //water
        {
            if($itemInfo["id"]>=100)
            {
                $itemInfo["img"]="{$itemInfo["group"]}{$itemInfo["id"]}{$itemInfo['level']}"; //group +id+lvl
            }
            else
            {
                $itemInfo["img"]="{$itemInfo["group"]}{$itemInfo["id"]}0";
            }
            switch($itemInfo["level"])
            {
                case 0: if( $itemInfo["curDur"] == 0)
                    $itemInfo["lifeOpt"]="Block rating increase";
                    break;
                case 1: $itemInfo["lifeOpt"]="Defense increase";break;
                case 2: $itemInfo["lifeOpt"]="Shield protection increase";break;
                case 3: $itemInfo["lifeOpt"]="Damage reduction";break;
                case 4: $itemInfo["lifeOpt"]="Damage reflection";break;
                default :$itemInfo["lifeOpt"]="Unknown item class {$itemInfo["curDur"]}-{$itemInfo["level"]}"; break;
            }
        }
        else if($itemInfo["id"] == 62 || $itemInfo["id"] == 102 || $itemInfo["id"] ==108 || $itemInfo["id"] ==114 || $itemInfo["id"] ==120 || $itemInfo["id"] ==126) //ice
        {
            if($itemInfo["id"]>=100)
            {
                $itemInfo["img"]="{$itemInfo["group"]}{$itemInfo["id"]}{$itemInfo['level']}"; //group +id+lvl
            }
            else
            {
                $itemInfo["img"]="{$itemInfo["group"]}{$itemInfo["id"]}0";
            }
            switch($itemInfo["level"])
            {
                case 0: $itemInfo["lifeOpt"]="Monster destruction for the life increase";break;
                case 1: $itemInfo["lifeOpt"]="Monster destruction for the mana increase";break;
                case 2: $itemInfo["lifeOpt"]="Skill attack increase";break;
                case 3: $itemInfo["lifeOpt"]="Attack rating increase";break;
                case 4: $itemInfo["lifeOpt"]="Item Durability increase";break;
                default :$itemInfo["lifeOpt"]="Unknown item class {$itemInfo["curDur"]}-{$itemInfo["level"]}"; break;
            }
        }
        else if($itemInfo["id"] == 63 || $itemInfo["id"] == 103 || $itemInfo["id"] ==109 || $itemInfo["id"] ==115 || $itemInfo["id"] ==121 || $itemInfo["id"] ==127) //wind
        {
            if($itemInfo["id"]>=100)
            {
                $itemInfo["img"]="{$itemInfo["group"]}{$itemInfo["id"]}{$itemInfo['level']}"; //group +id+lvl
            }
            else
            {
                $itemInfo["img"]="{$itemInfo["group"]}{$itemInfo["id"]}0";
            }

            switch($itemInfo["level"])
            {
                case 0: $itemInfo["lifeOpt"]="Automatic life recovery increase";break;
                case 1: $itemInfo["lifeOpt"]="Maximum life increase";break;
                case 2: $itemInfo["lifeOpt"]="Maximum mana increase";break;
                case 3: $itemInfo["lifeOpt"]="Automatic mana recovery increase";break;
                case 4: $itemInfo["lifeOpt"]="Maximum AG increase";break;
                case 5: $itemInfo["lifeOpt"]="Maximum AG value increase";break;
                default :$itemInfo["lifeOpt"]="Unknown item class {$itemInfo["level"]}"; break;
            }
        }
        else if($itemInfo["id"] == 64 || $itemInfo["id"] == 104 || $itemInfo["id"] ==110 || $itemInfo["id"] ==116 || $itemInfo["id"] ==122 || $itemInfo["id"] ==128) //lignhtning
        {
            if($itemInfo["id"]>=100)
            {
                $itemInfo["img"]="{$itemInfo["group"]}{$itemInfo["id"]}{$itemInfo['level']}"; //group +id+lvl
            }
            else
            {
                $itemInfo["img"]="{$itemInfo["group"]}{$itemInfo["id"]}0";
            }

            switch($itemInfo["level"])
            {
                case 0: $itemInfo["lifeOpt"]="Exellent damage increase";break;
                case 1: $itemInfo["lifeOpt"]="Exellent damage rate increase";break;
                case 2: $itemInfo["lifeOpt"]="Critical damage increase";break;
                case 3: $itemInfo["lifeOpt"]="Critical damage rate increase";break;
                default :$itemInfo["lifeOpt"]="Unknown item class {$itemInfo["level"]}"; break;
            }
        }
        else if($itemInfo["id"] == 65 || $itemInfo["id"] == 105 || $itemInfo["id"] ==111 || $itemInfo["id"] ==117 || $itemInfo["id"] ==123 || $itemInfo["id"] ==129) //earth
        {
            if($itemInfo["id"]>=100)
            {
                $itemInfo["img"]="{$itemInfo["group"]}{$itemInfo["id"]}{$itemInfo['level']}"; //group +id+lvl
            }
            else
            {
                $itemInfo["img"]="{$itemInfo["group"]}{$itemInfo["id"]}0";
            }
            switch($itemInfo["level"])
            {

                case 2: $itemInfo["lifeOpt"]="Increases Health"; break;
                default :$itemInfo["lifeOpt"]="Unknown item class {$itemInfo["level"]}"; break;
            }

        }
        return $itemInfo;
    }

    /**
     * чтение 13 группы (peng& rings)
     * @param $itemInfo
     * @param $hbase
     * @return mixed
     */
    static private function read13($itemInfo,$hbase)
    {
        $got = self::readfile("{$itemInfo["group"]}.{$itemInfo["id"]}");
        $itemInfo["x"] = $got[1];
        $itemInfo["y"] = $got[2];
        $itemInfo["name"]= htmlspecialchars(str_replace("\n",'',$got[0]),ENT_QUOTES);
        $biz  = array(8,9,21,22,23,24,12,13,25,26,27,28); //ринги
        $pend  = array(12,13,25,26,27,28); //пенданты
        $itemInfo["descr"] = $got[9]; //то, что может вписать пользователь для отображения инфы про вещи
        $itemInfo["equipmentn"] = $got[8];
        switch($itemInfo["id"])
        {
            case 24: $addname = "Max mana increased +"; break;
            case 28: $addname = "Max AG increased +";break;
            case 30: $addname = "Additional damage +"; break;
            default: $addname = "Automatic HP recovery +";
        }

        if(in_array($itemInfo["id"], $biz)) //если это rings
        {

            $itemInfo = self::getLifeOpt($itemInfo,1);

            if(in_array($itemInfo["id"],$pend))
                $itemInfo = self::getExcellent($itemInfo,1);
            else
                $itemInfo = self::getExcellent($itemInfo,2);

            $itemInfo["maxDur"] = $got["7"] + self::CalcDur($itemInfo["level"]);

            if(isset($itemInfo["exc"]))
            {
                $itemInfo["maxDur"] = $itemInfo["maxDur"] + 15;
            }
            $itemInfo["equipment"] = self::getEq($got[4]);

            if($itemInfo["ispvp"]!=0)
            {
                if ($got[5]!="no") //pvp
                   $itemInfo["pvp"] = self::getEq($got[5]);
            }


            if(trim($got[6])!="no|no" && $itemInfo["ancnum"]>0) //ancient
            {
                $t = self::ancopt(trim($got[6]),$itemInfo["ancnum"]);
                if(is_array($t))
                {
                    $itemInfo+=$t;
                }
            }

            if(isset($itemInfo["isSkill"])) //skill
            {
                $itemInfo["skillname"]="Have specific skill";
            }

            if ($itemInfo["harmonyOpt"]>0 && $itemInfo["harmonyLvl"]>=0 && $hbase!=null)
            {
                if (isset($hbase[3][$itemInfo["harmonyOpt"]]["name"]) && isset($hbase[3][$itemInfo["harmonyOpt"]][$itemInfo["harmonyLvl"]]["req"]))
                    $itemInfo["harmony"] = $hbase[3][$itemInfo["harmonyOpt"]]["name"]." +".$hbase[3][$itemInfo["harmonyOpt"]][$itemInfo["harmonyLvl"]]["req"];
            }
            $itemInfo["img"]="{$itemInfo["group"]}{$itemInfo["id"]}0"; //group +id+lvl
        }
        else if ($itemInfo["id"] == 30) //Cape of Lord
        {
            $addname="";
            $itemInfo = self::getLifeOpt($itemInfo,4,1);
            $itemInfo = self::getExcellent($itemInfo,3);



            $itemInfo["defence"]=$got[3]+$itemInfo["level"]*2;


            $itemInfo["maxDur"] = $got["7"] + self::CalcDur($itemInfo["level"]);
            $itemInfo["equipment"] = self::getEq($got[4]);
            $itemInfo["lvlreq"] = 180+($itemInfo["level"]*4);


            if($itemInfo["ispvp"]!=0)
            {
                if ($got[5]!="no") //pvp
                $itemInfo["pvp"] = self::getEq($got[6]);
            }

            if(isset($itemInfo["isSkill"])) //skill
            {
                $itemInfo["skillname"]="Have specific skill";
            }

            $itemInfo["img"]="{$itemInfo["group"]}{$itemInfo["id"]}0"; //group +id+lvl
        }
        else
        {
            $itemInfo = self::getLifeOpt($itemInfo,1);
            switch($itemInfo["id"])
            {
                case 7:
                    if($itemInfo['level'] ==1 )
                    {
                        $itemInfo["name"]="Sperman";
                        unset($itemInfo["level"]);
                        $itemInfo["img"]="{$itemInfo["group"]}{$itemInfo["id"]}7"; //group +id+lvl
                    }
                    break;
                case 11:
                    if($itemInfo["level"]==1)
                    {
                        $itemInfo["name"]="Life Stone";
                        unset($itemInfo["level"]);
                        $itemInfo["img"]="{$itemInfo["group"]}{$itemInfo["id"]}7"; //group +id+lvl
                    }
                    else
                    {
                        $itemInfo["name"]="Guardian";
                        unset($itemInfo["level"]);
                        $itemInfo["img"]="{$itemInfo["group"]}{$itemInfo["id"]}7"; //group +id+lvl
                    }
                    break;
                //crest of monarch
                case 14:
                    if($itemInfo['level'] == 1)
                    {
                        $itemInfo["name"]="Crest of Monarch";
                        unset($itemInfo["level"]);
                        $itemInfo["img"]="{$itemInfo["group"]}{$itemInfo["id"]}1"; //group +id+lvl
                    }
                    else
                        $itemInfo["img"]="{$itemInfo["group"]}{$itemInfo["id"]}0"; //group +id+lvl
                    unset($itemInfo["curDur"]);
                    break;
                //warror's rings
                case 20:
                    if ($itemInfo['level'] ==1 || $itemInfo['level'] == 2)
                    {
                        $itemInfo["name"] = "Ring Of Warrior";
                        unset($itemInfo["level"]);
                        $itemInfo["img"]="{$itemInfo["group"]}{$itemInfo["id"]}7"; //group +id+lvl
                    }
                    break;
                //cape of lord
                case 30:
                    $f = array($got[11],$got[8]);
                    $itemInfo = self::getStats($itemInfo,$f); //требования силы, аги
                    //$itemInfo = self::getLifeOpt($itemInfo,4); //лайф опции
                    $itemInfo = self::getExcellent($itemInfo,3);



                    if (isset($itemInfo["exc"]))
                        $itemInfo["defence"] = self::getDef($itemInfo,$f,true);
                    else
                        $itemInfo["defence"] = self::getDef($itemInfo,$f);


                    $itemInfo["maxDur"] = $got["11"] + self::CalcDur($itemInfo["level"]);

                    if(isset($itemInfo["exc"]))
                    {
                        $itemInfo["maxDur"] = $itemInfo["maxDur"] + 15;
                        $itemInfo["str"] = intval($itemInfo["str"] + $itemInfo["str"]*0.132);
                        $itemInfo["agi"] = intval($itemInfo["agi"] + $itemInfo["agi"]*0.132);
                    }

                    $itemInfo["equipment"] = self::getEq($got[4]);

                    $itemInfo["speed"] = (int)$got[4];

                    if ($got[6]!="no") //pvp
                    $itemInfo["pvp"] = self::getEq($got[6]);


                    if($got[7]!="no|no") //ancient
                    {
                        $an = explode("|",$got[7]);
                        if ($an[0]!="no") $itemInfo["anc1"] = str_replace("\n",'',$an[0]);
                        if ($an[1]!="no") $itemInfo["anc2"] = str_replace("\n",'',$an[1]);
                    }

                    if(isset($itemInfo["isSkill"])) //skill
                    {
                        $itemInfo["skillname"]="Have specific skill";
                    }

                    if ($itemInfo["harmonyOpt"]>0 && $itemInfo["harmonyLvl"]>=0 && $hbase!=null)
                    {
                        if (isset($hbase[3][$itemInfo["harmonyOpt"]]["name"]) && isset($hbase[3][$itemInfo["harmonyOpt"]][$itemInfo["harmonyLvl"]]["req"]))
                            $itemInfo["harmony"] = $hbase[3][$itemInfo["harmonyOpt"]]["name"]." +".$hbase[3][$itemInfo["harmonyOpt"]][$itemInfo["harmonyLvl"]]["req"];
                    }
                    $itemInfo["img"]="{$itemInfo["group"]}{$itemInfo["id"]}0"; //group +id+lvl
                    break;
                //dark raven or dark hourse
                case 31:
                    if($itemInfo['level'] == 1)
                    {
                        $itemInfo["name"]= "Spirit of Dark Raven";
                        $itemInfo["img"]="{$itemInfo["group"]}{$itemInfo["id"]}1"; //group +id+lvl
                        $itemInfo["descr"] = "Raven for Dark Lord"; //то, что может вписать пользователь для отображения инфы про вещи
                        $itemInfo["img"]="{$itemInfo["group"]}{$itemInfo["id"]}0"; //group +id+lvl
                        unset($itemInfo['level']);
                    }
                    else
                    {
                        $itemInfo["name"]= "Spirit of Dark Horse";
                        $itemInfo["img"]="{$itemInfo["group"]}{$itemInfo["id"]}0"; //group +id+lvl
                        $itemInfo["descr"] = "Horse for Dark Lord"; //то, что может вписать пользователь для отображения инфы про вещи
                        unset($itemInfo['level']);
                        $itemInfo["img"]="{$itemInfo["group"]}{$itemInfo["id"]}1"; //group +id+lvl
                    }
                    break;
                case 37:
                    switch ($itemInfo["excnum"])
                    {
                        case 0: $itemInfo["name"] = "Red ".$itemInfo["name"]; break;
                        case 1: $itemInfo["name"] = "Black ".$itemInfo["name"]; break;
                        case 2: $itemInfo["name"] = "Blue ".$itemInfo["name"]; break;
                        case 4: $itemInfo["name"] = "Gold ".$itemInfo["name"]; break;
                    }
                    $itemInfo["img"]="{$itemInfo["group"]}{$itemInfo["id"]}{$itemInfo['level']}"; //group +id+lvl
                    break;

                default:
                    $itemInfo["img"]="{$itemInfo["group"]}{$itemInfo["id"]}{$itemInfo['level']}"; //group +id+lvl
                break;
            }
        }

        if(isset($itemInfo["lifeOpt"]))
            $itemInfo["lifeOpt"] = $addname.$itemInfo["lifeOpt"];

        return $itemInfo;
    }

    /**
     * чтение 14 группы
     * @param $itemInfo
     * @param $hbase
     * @return mixed
     */
    static private function read14($itemInfo,$hbase)
    {
        $got = self::readfile("{$itemInfo["group"]}.{$itemInfo["id"]}");
        $itemInfo["x"] = $got[1];
        $itemInfo["y"] = $got[2];
        $itemInfo["descr"] = $got[5]; //то, что может вписать пользователь для отображения инфы про вещи
        $itemInfo["equipmentn"] = $got[4];
        switch($itemInfo["id"])
        {

            case 11:
                switch ($itemInfo["level"])
                {
                    case 1: $itemInfo["name"]="Star";$itemInfo["level"].="_";break;
                    case 2: $itemInfo["name"]="FireCracker";$itemInfo["level"].="_";break;
                    case 5: $itemInfo["name"]="Silver Medal";$itemInfo["level"].="_";break;
                    case 6: $itemInfo["name"]="Gold Medal";$itemInfo["level"].="_";break;
                    case 7: $itemInfo["name"]="Box of Heaven";$itemInfo["level"].="_";break;
                    case 8: $itemInfo["name"]="Box of Kundun +1";$itemInfo["level"].="_";break;
                    case 9: $itemInfo["name"]="Box of Kundun +2";$itemInfo["level"].="_";break;
                    case 10: $itemInfo["name"]="Box of Kundun +3";$itemInfo["level"].="_";break;
                    case 11: $itemInfo["name"]="Box of Kundun +4";$itemInfo["level"].="_";break;
                    case 12: $itemInfo["name"]="Box of Kundun +5";$itemInfo["level"].="_";break;
                    case 13: $itemInfo["name"]="Heart Of Lord";break;
                }

                $itemInfo["img"]="{$itemInfo["group"]}{$itemInfo["id"]}{$itemInfo["level"]}"; //group +id+lvl
                unset($itemInfo["level"]);

                break;
                case 7:
                    if($itemInfo["level"]==1)
                    {
                        $itemInfo["name"]="Potion of Soul";
                        $itemInfo["img"]="{$itemInfo["group"]}{$itemInfo["id"]}7"; //group +id+lvl
                    }
                    break;
                case 12:
                    if($itemInfo["level"]==1)
                    {
                        $itemInfo["name"]="Heart";
                        unset($itemInfo["level"]);
                        $itemInfo["img"]="{$itemInfo["group"]}{$itemInfo["id"]}0";
                    }
                    elseif($itemInfo["level"]==2)
                    {
                        $itemInfo["name"]="Pergamin";
                        unset($itemInfo["level"]);
                        $itemInfo["img"]="{$itemInfo["group"]}{$itemInfo["id"]}7";
                    }
                    break;

                case 21:
                    if($itemInfo["level"]==1)
                    {
                        $itemInfo["name"]="Stone";
                        $itemInfo["img"]="{$itemInfo["group"]}{$itemInfo["id"]}{$itemInfo["level"]}";
                        unset($itemInfo["level"]);
                    }
                    elseif($itemInfo["level"]==3)
                    {
                        $itemInfo["name"]="Sing of Lord";
                        $itemInfo["img"]="{$itemInfo["group"]}{$itemInfo["id"]}{$itemInfo["level"]}";
                        unset($itemInfo["level"]);
                    }
                    break;
            // lost map
            case 28:
                $itemInfo["name"]= htmlspecialchars(str_replace("\n",'',$got[0]),ENT_QUOTES);
                $itemInfo["img"] = "{$itemInfo["group"]}{$itemInfo["id"]}2";
                break;
                case 32:
                    if($itemInfo["level"]==1)
                    {
                        $itemInfo["name"]="Pink Candy Box";
                        $itemInfo["img"]="{$itemInfo["group"]}{$itemInfo["id"]}{$itemInfo["level"]}";
                        unset($itemInfo["level"]);
                    }
                    break;
                case 33:
                    if($itemInfo["level"]==1)
                    {
                        $itemInfo["name"]="Orange Candy Box";
                        $itemInfo["img"]="{$itemInfo["group"]}{$itemInfo["id"]}{$itemInfo["level"]}";
                        unset($itemInfo["level"]);
                    }
                    break;
                case 34:
                    if($itemInfo["level"]==1)
                    {
                        $itemInfo["name"]="Blue Candy Box";
                        $itemInfo["img"]="{$itemInfo["group"]}{$itemInfo["id"]}{$itemInfo["level"]}";
                        unset($itemInfo["level"]);
                    }
                    break;
            default:
                $itemInfo["name"]= htmlspecialchars(str_replace("\n",'',$got[0]),ENT_QUOTES);
                $itemInfo["img"]="{$itemInfo["group"]}{$itemInfo["id"]}{$itemInfo['level']}"; //group +id+lvl
                $itemInfo["equipment"] = self::getEq($got[3]);
                break;
        }


        return $itemInfo;
    }

    /**
     * чтение 15 группы  скруллы
     * @param $itemInfo
     * @return mixed
     */
    static private function readScrolls($itemInfo)
    {
        $got = self::readfile("{$itemInfo["group"]}.{$itemInfo["id"]}");
        $itemInfo["x"] = $got[1];
        $itemInfo["y"] = $got[2];
        $itemInfo["name"]= htmlspecialchars(str_replace("\n",'',$got[0]),ENT_QUOTES);
        $itemInfo["ene"]= $got[4];
        $itemInfo["descr"] = $got[6]; //то, что может вписать пользователь для отображения инфы про вещи
        $itemInfo["img"]="{$itemInfo["group"]}{$itemInfo["id"]}0"; //group +id+lvl
        $itemInfo["equipment"] = self::getEq($got[3]);
        if ($itemInfo["equipment"][count($itemInfo["equipment"])-1] == 0)
            unset($itemInfo["equipment"][count($itemInfo["equipment"])-1]);
        $itemInfo["equipmentn"] = $got[4];
        return $itemInfo;
    }

    /**
     * Рассчет лайф опций
     * @param $itemInfo оинформация о вещи
     * @param $mult множитель (для каждой группы свой)
     * @param int $isWings
     * @return mixed дополненный массив с информацией о вещи
     */
    private static function getLifeOpt($itemInfo,$mult,$isWings = 0)
    {
        if($isWings == 0) // если это не винги
        {
            if ($itemInfo["excnum"]>63)
            {
                $itemInfo["lifeOpt"] = $mult * $itemInfo["intopt"] + $mult * 4;
                $itemInfo["lifeLvl"] = $itemInfo["intopt"] + 4;
                $itemInfo["excnum"] -= 64;
            }
            else
            {
                if ($itemInfo["intopt"]>0)
                {
                    $itemInfo["lifeOpt"] = $mult * $itemInfo["intopt"];
                    $itemInfo["lifeLvl"] = $itemInfo["intopt"];
                }
                else
                    $itemInfo["lifeLvl"] = 0;
            }
        }
        else
        {
            $wing_opt[0]="Additional wizardy damage ";
            $wing_opt[1]="Additional damage ";
            $wing_opt[2]="Automatic HP recovery ";
            $wing_opt[3]="Additional defence ";

            $optName="";

            $dw_wing = array(1,4); //wings dw
            $sum_wing = array(41,42);
            $dk_wing = array(2,5); // dk
            $elf_wing = array(0,3); // elf
            $thss = array(36,37,38,39,40,43,50);//винги для 3-го класса

            if ($itemInfo["group"]==12 or ($itemInfo["group"]==13 and $itemInfo["id"]==30))
            {

                if (in_array($itemInfo["id"],$dw_wing)) // опции на винги дв
                {
                    if(($itemInfo["excnum"]>=0 && $itemInfo["excnum"]<=31 && $itemInfo["intopt"]>0) or ($itemInfo["excnum"]>=64 && $itemInfo["excnum"]<=95 )) //HP rec
                    {
                        if ($itemInfo["excnum"]>=0 && $itemInfo["excnum"]<=31)
                        {
                            $optName= $wing_opt[0]."+".($itemInfo["intopt"]*4)."%";
                            $itemInfo["lifeLvl"] = $itemInfo["intopt"];
                        }
                        else if($itemInfo["excnum"]>=64 && $itemInfo["excnum"]<=95)
                        {
                            $optName= $wing_opt[0]."+".($itemInfo["intopt"]*4 +16)."%";
                            $itemInfo["lifeLvl"] = $itemInfo["intopt"] + 4;
                            $itemInfo["excnum"]-=64;
                        }
                        $itemInfo["lifeOpt"] = $optName;
                        return $itemInfo;
                    }
                    else //add dmg
                    {
                        if($itemInfo["excnum"]>=32 && $itemInfo["excnum"]<=63)
                        {
                            $optName= $wing_opt[0]."+".($itemInfo["intopt"]*4)."%";
                            $itemInfo["lifeLvl"] = $itemInfo["intopt"];
                            $itemInfo["excnum"]-=32;
                        }
                        else if($itemInfo["excnum"]>=96)
                        {
                            $optName= $wing_opt[0]."+".($itemInfo["intopt"]*4+16)."%";
                            $itemInfo["lifeLvl"] = $itemInfo["intopt"] + 4;
                            $itemInfo["excnum"]-=96;
                        }
                    }
                    if ($itemInfo["lifeLvl"]>0 && isset($optName))
                    {
                        $itemInfo["lifeOpt"] = $optName;
                        return $itemInfo;
                    }
                }
                if (in_array($itemInfo["id"],$sum_wing)) // опции на винги sum
                {
                    if(($itemInfo["excnum"]>=0  && $itemInfo["excnum"]<=31 ) or ($itemInfo["excnum"]>63 && $itemInfo["excnum"]<=95 )) //hp rec
                    {
                        if($itemInfo["id"] == 42)
                            $optName = "Additional Curse Spell ";
                        else
                            $optName = "Additional wizardy damage ";

                        if ($itemInfo["excnum"]>=0  && $itemInfo["excnum"]<=31)
                        {
                            $optName.= "+".($itemInfo["intopt"]*4)."%";
                            $itemInfo["lifeLvl"] = $itemInfo["intopt"];
                            $itemInfo["excnum"]-=15;
                        }
                        else if($itemInfo["excnum"]>=64 && $itemInfo["excnum"]<=95 )
                        {
                            $optName.= "+".($itemInfo["intopt"]*4+16)."%";
                            $itemInfo["lifeLvl"] = $itemInfo["intopt"] + 4;
                            $itemInfo["excnum"]-=64;
                        }
                        else
                            unset($optName);
                    }
                    else //add dmg
                    {
                        if($itemInfo["excnum"]>=32 && $itemInfo["excnum"]<=63)
                        {
                            $optName=$wing_opt[0]."+".($itemInfo["intopt"]*4)."%";
                            $itemInfo["lifeLvl"] = $itemInfo["intopt"];
                            $itemInfo["excnum"]-=32;
                        }
                        else if($itemInfo["excnum"]>=96)
                        {
                            $optName=$wing_opt[0]."+".($itemInfo["intopt"]*4+16)."%";
                            $itemInfo["lifeLvl"] = $itemInfo["intopt"] + 4;
                            $itemInfo["excnum"]-=96;
                        }
                    }
                    if ($itemInfo["lifeLvl"]>0 && isset($optName))
                    {
                        $itemInfo["lifeOpt"] = $optName;
                        return $itemInfo;
                    }
                }
                else if(in_array($itemInfo["id"],$dk_wing)) //опции на винги дк
                {
                    if($itemInfo["id"] == 5) //2е венги
                    {
                        if(($itemInfo["excnum"]>=0  && $itemInfo["excnum"]<=31 ) or ($itemInfo["excnum"]>63 && $itemInfo["excnum"]<=95 )) //HP rec
                        {
                            if ($itemInfo["excnum"]>=0  && $itemInfo["excnum"]<=31)
                            {
                                $optName = $wing_opt[2]. "+{$itemInfo["intopt"]}%";
                                $itemInfo["lifeLvl"] = $itemInfo["intopt"];
                            }
                            else if($itemInfo["excnum"]>63 && $itemInfo["excnum"]<=95)
                            {
                                $optName = $wing_opt[2]. "+".($itemInfo["intopt"]+4)."%";
                                $itemInfo["lifeLvl"] = $itemInfo["intopt"] + 4;
                                $itemInfo["excnum"]-=64;
                            }
                        }
                        else //add dmg
                        {


                            if($itemInfo["excnum"]>=32 && $itemInfo["excnum"]<=61)
                            {
                                $optName=$wing_opt[1]. "+".($itemInfo["intopt"]*4)."%";
                                $itemInfo["lifeLvl"] = $itemInfo["intopt"];
                                $itemInfo["excnum"]-=32;
                            }
                            else if($itemInfo["excnum"]>=96)
                            {
                                $optName=$wing_opt[1]. "+".($itemInfo["intopt"]*4+16)."%";
                                $itemInfo["lifeLvl"] = $itemInfo["intopt"] + 4;
                                $itemInfo["excnum"]-=96;
                            }
                        }
                    }
                    else // 1е
                    {
                        if(($itemInfo["excnum"]>=0  && $itemInfo["excnum"]<=31 ) or ($itemInfo["excnum"]>63 && $itemInfo["excnum"]<=95 )) //HP rec
                        {
                            if ($itemInfo["excnum"]>=0  && $itemInfo["excnum"]<=31)
                            {
                                $optName.=$wing_opt[1]. "+".($itemInfo["intopt"]*4)."%";
                                $itemInfo["lifeLvl"] = $itemInfo["intopt"];
                            }
                            else if($itemInfo["excnum"]>63 && $itemInfo["excnum"]<=95)
                            {
                                $optName=$wing_opt[1]. "+".($itemInfo["intopt"]*4+16)."%";
                                $itemInfo["lifeLvl"] = $itemInfo["intopt"] + 4;
                                $itemInfo["excnum"]-=64;
                            }
                        }
                        else //add dmg
                        {
                            if($itemInfo["excnum"]>=32 && $itemInfo["excnum"]<=61)
                            {
                                $optName =$wing_opt[2]. "+".($itemInfo["intopt"])."%";
                                $itemInfo["lifeLvl"] = $itemInfo["intopt"];
                                $itemInfo["excnum"]-=32;
                            }
                            else if($itemInfo["excnum"]>=96)
                            {
                                $optName = $wing_opt[2]."+".($itemInfo["intopt"]+4)."%";
                                $itemInfo["lifeLvl"] = $itemInfo["intopt"] + 4;
                                $itemInfo["excnum"]-=96;
                            }
                        }
                    }
                    if ($itemInfo["lifeLvl"]>0 && isset($optName))
                    {
                        $itemInfo["lifeOpt"] = $optName;
                        return $itemInfo;
                    }
                }
                else if(in_array($itemInfo["id"],$elf_wing)) // elf wings opt
                {
                    if(($itemInfo["excnum"]>=0  && $itemInfo["excnum"]<=31 ) or ($itemInfo["excnum"]>63 && $itemInfo["excnum"]<=95 )) // wiz dmg
                    {
                        if ($itemInfo["excnum"]>=0  && $itemInfo["excnum"]<=31)
                        {
                            $optName =$wing_opt[2]."+".($itemInfo["intopt"])."%";
                            $itemInfo["lifeLvl"] = $itemInfo["intopt"];
                        }
                        else if($itemInfo["excnum"]>63 && $itemInfo["excnum"]<=95)
                        {
                            $optName.=$wing_opt[2]."+".($itemInfo["intopt"]+4)."%";
                            $itemInfo["lifeLvl"] = $itemInfo["intopt"] + 4;
                            $itemInfo["excnum"]-=64;
                        }
                    }
                    else //HP rec
                    {
                        if($itemInfo["excnum"]>=32 && $itemInfo["excnum"]<=61)
                        {
                            $optName= $wing_opt[1]."+".($itemInfo["intopt"]*4)."%";
                            $itemInfo["lifeLvl"] = $itemInfo["intopt"];
                            $itemInfo["excnum"]-=32;
                        }
                        else if($itemInfo["excnum"]>=96)
                        {
                            $optName= $wing_opt[1]."+".($itemInfo["intopt"]*4+16)."%";
                            $itemInfo["lifeLvl"] = $itemInfo["intopt"] + 4;
                            $itemInfo["excnum"]-=96;
                        }

                    }
                    if ($itemInfo["lifeLvl"]>0 && isset($optName))
                    {
                        $itemInfo["lifeOpt"] = $optName;
                        return $itemInfo;
                    }
                }
                else if($itemInfo["group"]==13 && $itemInfo["id"]==30) //cape of lord
                {
                    if ($itemInfo["excnum"]<=31 && $itemInfo["intopt"]>0)
                    {
                        $optName= $wing_opt[1]."+".($itemInfo["intopt"]*4)."%";
                        $itemInfo["lifeLvl"] = $itemInfo["intopt"];
                        $itemInfo["excnum"]-=16;
                    }
                    else if($itemInfo["excnum"]>=80)
                    {
                        $optName= $wing_opt[1]."+".($itemInfo["intopt"]*4+16)."%";
                        $itemInfo["lifeLvl"] = $itemInfo["intopt"] + 4;
                        $itemInfo["excnum"]-=80;
                    }
                    if($itemInfo["lifeLvl"]>0 && isset($optName))
                    {
                        $itemInfo["lifeOpt"] = $optName;
                        return $itemInfo;
                    }
                }
                else if ($itemInfo["group"]==12 and $itemInfo["id"]==49) //Cape of Fighter
                {
                    if($itemInfo["excnum"]<=15 or ($itemInfo["excnum"]>=64 && $itemInfo["excnum"]<=79 )) //hp recovery
                    {
                        if ($itemInfo["excnum"]<=15 && $itemInfo["intopt"]>0)
                        {
                            $optName =$wing_opt[2]. "+".($itemInfo["intopt"])."%";
                            $itemInfo["lifeLvl"] = $itemInfo["intopt"];
                        }
                        else if($itemInfo["excnum"]>=64 && $itemInfo["excnum"]<=79 )
                        {
                            $optName = $wing_opt[2]."+".($itemInfo["intopt"]+4)."%";
                            $itemInfo["lifeLvl"] = $itemInfo["intopt"] + 4;
                            $itemInfo["excnum"]-=64;
                        }
                        else
                        {
                            unset($optName);
                        }
                        if($itemInfo["lifeLvl"]>0 && isset($optName))
                        {
                            $itemInfo["lifeOpt"] = $optName;
                            return $itemInfo;
                        }
                    }
                    else //wizardy dmg
                    {
                        if($itemInfo["excnum"]>=32 && $itemInfo["excnum"]<=47)
                        {
                            $optName= $wing_opt[1]."+".($itemInfo["intopt"]*4)."%";
                            $itemInfo["lifeLvl"] = $itemInfo["intopt"];
                            $itemInfo["excnum"]-=32;
                        }
                        else if($itemInfo["excnum"]>=96)
                        {
                            $optName= $wing_opt[1]."+".($itemInfo["intopt"]*4+16)."%";
                            $itemInfo["lifeLvl"] = $itemInfo["intopt"] + 4;
                            $itemInfo["excnum"]-=96;
                        }

                    }

                    if($itemInfo["lifeLvl"]>0 && isset($optName))
                    {
                        $itemInfo["lifeOpt"] = $optName;
                        return $itemInfo;
                    }

                }
                else if ($itemInfo["group"]==12 and $itemInfo["id"]==6) //2nd mg wings
                {
                    if($itemInfo["excnum"]<=15 or ($itemInfo["excnum"]>=64 && $itemInfo["excnum"]<=79 )) //add dmg
                    {

                        if ($itemInfo["excnum"]<=15 && $itemInfo["intopt"]>0)
                        {
                            $optName = $wing_opt[0]." +".($itemInfo["intopt"]*4)."%";
                            $itemInfo["lifeLvl"] = $itemInfo["intopt"];
                        }
                        else if($itemInfo["excnum"]>=64 && $itemInfo["excnum"]<=79 )
                        {
                            $optName = $wing_opt[0]."+".($itemInfo["intopt"]*4+16)."%";
                            $itemInfo["lifeLvl"] = $itemInfo["intopt"] + 4;
                            $itemInfo["excnum"]-=64;

                        }
                        if($itemInfo["lifeLvl"]>0 && isset($optName))
                        {
                            $itemInfo["lifeOpt"] = $optName;
                            return $itemInfo;
                        }
                    }
                    else //wizardy dmg
                    {
                        if($itemInfo["excnum"]>=32 && $itemInfo["excnum"]<=47 && $itemInfo["intopt"]>0)
                        {
                            $optName= $wing_opt[1]."+".($itemInfo["intopt"]*4)."%";
                            $itemInfo["lifeLvl"] = $itemInfo["intopt"];
                            $itemInfo["excnum"]-=32;
                        }
                        else if($itemInfo["excnum"]>=96)
                        {
                            $optName= $wing_opt[1]."+".($itemInfo["intopt"]*4+16)."%";
                            $itemInfo["lifeLvl"] = $itemInfo["intopt"] + 4;
                            $itemInfo["excnum"]-=96;
                        }
                        if($itemInfo["lifeLvl"]>0 && isset($optName))
                        {
                            $itemInfo["lifeOpt"] = $optName;
                            return $itemInfo;
                        }
                    }
                    return $itemInfo;
                }
                else if (in_array($itemInfo["id"],$thss)) //3thd class
                {
                    #region суммонерские 3и винги
                    if($itemInfo["group"]==12 && $itemInfo["id"]==43) // sumoner
                    {
                        if(($itemInfo["excnum"]>=32 && $itemInfo["excnum"]<=47) OR $itemInfo["excnum"]>=96)
                        {
                            if($itemInfo["excnum"]>=32 && $itemInfo["excnum"]<96)
                            {
                                if($itemInfo["excnum"]-32>=0)
                                {
                                $optName="Additional Curse Spell +".($itemInfo["intopt"]*4);
                                $itemInfo["lifeLvl"] = $itemInfo["intopt"];
                                $itemInfo["excnum"]-=32;
                                }
                            }
                            else
                            {
                                if($itemInfo["excnum"]-96>=0)
                                {
                                $optName="Additional Curse Spell +".($itemInfo["intopt"]*4+16)."%";
                                $itemInfo["lifeLvl"] = $itemInfo["intopt"] + 4;
                                $itemInfo["excnum"]-=96;
                                }
                            }
                            if($itemInfo["lifeLvl"]>0 && isset($optName))
                            {
                                $itemInfo["lifeOpt"] = $optName;
                                return $itemInfo;
                            }
                        }
                        else if (($itemInfo["excnum"]>=16 && $itemInfo["excnum"]<=31) or ($itemInfo["excnum"]>=80 && $itemInfo["excnum"]<=95))
                        {
                            if($itemInfo["excnum"]>=16 && $itemInfo["excnum"]<80)
                            {
                                if($itemInfo["excnum"]-16>=0)
                                {
                                $optName="Additional Wizardry dmg + ".($itemInfo["intopt"]*4);
                                $itemInfo["lifeLvl"] = $itemInfo["intopt"];
                                $itemInfo["excnum"]-=16;
                                }
                            }
                            else
                            {
                                if($itemInfo["excnum"]-80>=0)
                                {
                                $optName="Additional Wizardry dmg +".($itemInfo["intopt"]*4+16)."%";
                                $itemInfo["lifeLvl"] = $itemInfo["intopt"] + 4;
                                $itemInfo["excnum"]-=80;
                                }
                            }
                            if($itemInfo["lifeLvl"]>0 && isset($optName))
                            {
                                $itemInfo["lifeOpt"] = $optName;
                                return $itemInfo;
                            }
                        }
                        elseif (($itemInfo["excnum"]>=0 && $itemInfo["excnum"]<=47) OR($itemInfo["excnum"]>=64 && $itemInfo["excnum"]<=79))
                        {
                            if($itemInfo["excnum"]==0)
                            {
                                $optName=" HP Recovery +".$itemInfo["intopt"]."%".$optName;
                                $itemInfo["lifeLvl"] = $itemInfo["intopt"];
                            }
                            else
                            {
                                if($itemInfo["excnum"]-64>=0)
                                {
                                $optName=" HP Recovery +".($itemInfo["intopt"]+4)."%".$optName;
                                $itemInfo["lifeLvl"] = $itemInfo["intopt"] + 4;
                                $itemInfo["excnum"]-=64;
                                }
                            }

                            if($itemInfo["lifeLvl"]>0 && isset($optName))
                            {
                                $itemInfo["lifeOpt"] = $optName;
                                return $itemInfo;
                            }
                        }

                        return $itemInfo;
                    }
                    #endregion

                    #region dl 3и винги
                    else if($itemInfo["group"]==12 && $itemInfo["id"]==40)
                    {
                        if(($itemInfo["excnum"]>=32 && $itemInfo["excnum"]<=47) OR $itemInfo["excnum"]>=96)
                        {

                            if($itemInfo["excnum"]>=32 && $itemInfo["excnum"]<96)
                            {
                                if($itemInfo["excnum"]-32>=0)
                                {
                                $optName="Additional Defence +".($itemInfo["intopt"]*4);
                                $itemInfo["lifeLvl"] = $itemInfo["intopt"];
                                $itemInfo["excnum"]-=32;
                                }
                            }
                            else
                            {
                                if($itemInfo["excnum"]-96>=0)
                                {
                                $optName="Additional Defence  +".($itemInfo["intopt"]*4+16)."%";
                                $itemInfo["lifeLvl"] = $itemInfo["intopt"] + 4;
                                $itemInfo["excnum"]-=96;
                                }
                            }
                            if($itemInfo["lifeLvl"]>0 && isset($optName))
                            {
                                $itemInfo["lifeOpt"] = $optName;
                                return $itemInfo;
                            }
                        }
                        else if (($itemInfo["excnum"]>=16 && $itemInfo["excnum"]<=31) or ($itemInfo["excnum"]>=80 && $itemInfo["excnum"]<=95))
                        {
                            if($itemInfo["excnum"]>=16 && $itemInfo["excnum"]<80)
                            {
                                if($itemInfo["excnum"]-16>=0)
                                {
                                $optName.="Additional dmg +".($itemInfo["intopt"]*4);
                                $itemInfo["lifeLvl"] = $itemInfo["intopt"];
                                $itemInfo["excnum"]-=16;
                                }
                            }
                            else
                            {
                                if($itemInfo["excnum"]-80>=0)
                                {
                                $optName.="Additional dmg +".($itemInfo["intopt"]*4+16)."%";
                                $itemInfo["lifeLvl"] = $itemInfo["intopt"] + 4;
                                $itemInfo["excnum"]-=80;
                                }
                            }
                            if($itemInfo["lifeLvl"]>0 && isset($optName))
                            {
                                $itemInfo["lifeOpt"] = $optName;
                                return $itemInfo;
                            }
                        }
                        elseif (($itemInfo["excnum"]>=0 && $itemInfo["excnum"]<=47 && $itemInfo["intopt"]>0) OR($itemInfo["excnum"]>=64 && $itemInfo["excnum"]<=79))
                        {
                            if($itemInfo["excnum"]==0)
                            {
                                $optName=" HP Recovery +".$itemInfo["intopt"]."%".$optName;
                                $itemInfo["lifeLvl"] = $itemInfo["intopt"];
                            }
                            else
                            {
                                if($itemInfo["excnum"]-64>=0)
                                {
                                $optName=" HP Recovery +".($itemInfo["intopt"]+4)."%".$optName;
                                $itemInfo["lifeLvl"] = $itemInfo["intopt"] + 4;
                                $itemInfo["excnum"]-=64;
                                }
                            }

                            if($itemInfo["lifeLvl"]>0 && isset($optName))
                            {
                                $itemInfo["lifeOpt"] = $optName;
                                return $itemInfo;
                            }
                        }
                        return $itemInfo;
                    }
                    #endregion
                    #region mg 3и винги
                    else if($itemInfo["group"]==12 && $itemInfo["id"]==39)
                    {
                        if(($itemInfo["excnum"]>=32 && $itemInfo["excnum"]<=47) OR $itemInfo["excnum"]>=96)
                        {
                            if($itemInfo["excnum"]>=32 && $itemInfo["excnum"]<96)
                            {
                                if($itemInfo["excnum"]-32>=0)
                                {
                                $optName="Wizardry dmg +".($itemInfo["intopt"]*4);
                                $itemInfo["lifeLvl"] = $itemInfo["intopt"];
                                $itemInfo["excnum"]-=32;
                                }
                            }
                            else
                            {
                                if($itemInfo["excnum"]-96>=0)
                                {
                                $optName="Wizardry dmg +".($itemInfo["intopt"]*4+16)."%";
                                $itemInfo["lifeLvl"] = $itemInfo["intopt"] + 4;
                                $itemInfo["excnum"]-=96;
                                }
                            }
                            if($itemInfo["lifeLvl"]>0 && isset($optName))
                            {
                                $itemInfo["lifeOpt"] = $optName;
                                return $itemInfo;
                            }
                        }
                        else if (($itemInfo["excnum"]>=16 && $itemInfo["excnum"]<=31) or ($itemInfo["excnum"]>=80 && $itemInfo["excnum"]<=95))
                        {
                            if($itemInfo["excnum"]>=16 && $itemInfo["excnum"]<80)
                            {
                                if($itemInfo["excnum"]-16>=0)
                                {
                                $optName="Additional dmg +".($itemInfo["intopt"]*4);
                                $itemInfo["lifeLvl"] = $itemInfo["intopt"];
                                $itemInfo["excnum"]-=16;
                                }
                            }
                            else
                            {
                                if($itemInfo["excnum"]-80>=0)
                                {
                                $optName="Additional dmg +".($itemInfo["intopt"]*4+16)."%";
                                $itemInfo["lifeLvl"] = $itemInfo["intopt"] + 4;
                                $itemInfo["excnum"]-=80;
                                }
                            }
                            if($itemInfo["lifeLvl"]>0 && isset($optName))
                            {
                                $itemInfo["lifeOpt"] = $optName;
                                return $itemInfo;
                            }
                        }
                        elseif (($itemInfo["excnum"]>=0 && $itemInfo["excnum"]<=47) OR($itemInfo["excnum"]>=64 && $itemInfo["excnum"]<=79))
                        {
                            if($itemInfo["excnum"]==0 && $itemInfo["intopt"]>0)
                            {
                                $optName=" HP Recovery +".$itemInfo["intopt"]."%".$optName;
                                $itemInfo["lifeLvl"] = $itemInfo["intopt"];
                            }
                            else
                            {
                                if($itemInfo["excnum"]-64>=0)
                                {
                                $optName=" HP Recovery +".($itemInfo["intopt"]+4)."%".$optName;
                                $itemInfo["lifeLvl"] = $itemInfo["intopt"] + 4;
                                $itemInfo["excnum"]-=64;
                                }
                            }

                            if($itemInfo["lifeLvl"]>0 && isset($optName))
                            {
                                $itemInfo["lifeOpt"] = $optName;
                                return $itemInfo;
                            }
                        }
                        return $itemInfo;
                    }
                    #endregion
                    #region elf 3и винги
                    else if($itemInfo["group"]==12 && $itemInfo["id"]==38)
                    {
                        if(($itemInfo["excnum"]>=32 && $itemInfo["excnum"]<=47) OR $itemInfo["excnum"]>=96)
                        {
                            if($itemInfo["excnum"]>=32 && $itemInfo["excnum"]<96)
                            {
                                if($itemInfo["excnum"]-32>=0)
                                {
                                $optName="Additional Defence +".($itemInfo["intopt"]*4);
                                $itemInfo["lifeLvl"] = $itemInfo["intopt"];
                                $itemInfo["excnum"]-=32;
                                }
                            }
                            else
                            {
                                if($itemInfo["excnum"]-96>=0)
                                {
                                $optName="Additional Defence +".($itemInfo["intopt"]*4+16)."%";
                                $itemInfo["lifeLvl"] = $itemInfo["intopt"] + 4;
                                $itemInfo["excnum"]-=96;
                                }
                            }
                            if($itemInfo["lifeLvl"]>0 && isset($optName))
                            {
                                $itemInfo["lifeOpt"] = $optName;
                                return $itemInfo;
                            }
                        }
                        else if (($itemInfo["excnum"]>=16 && $itemInfo["excnum"]<=31) or ($itemInfo["excnum"]>=80 && $itemInfo["excnum"]<=95))
                        {
                            if($itemInfo["excnum"]>=16 && $itemInfo["excnum"]<80)
                            {
                                if($itemInfo["excnum"]-16>=0)
                                {
                                $optName.="Additional dmg +".($itemInfo["intopt"]*4);
                                $itemInfo["lifeLvl"] = $itemInfo["intopt"];
                                $itemInfo["excnum"]-=16;
                                }
                            }
                            else
                            {
                                if($itemInfo["excnum"]-80>=0)
                                {
                                $optName.="Additional dmg +".($itemInfo["intopt"]*4+16)."%";
                                $itemInfo["lifeLvl"] = $itemInfo["intopt"] + 4;
                                $itemInfo["excnum"]-=80;
                                }
                            }
                            if($itemInfo["lifeLvl"]>0 && isset($optName))
                            {
                                $itemInfo["lifeOpt"] = $optName;
                                return $itemInfo;
                            }
                        }
                        elseif (($itemInfo["excnum"]>=0 && $itemInfo["excnum"]<=15) OR($itemInfo["excnum"]>=64 && $itemInfo["excnum"]<=79))
                        {
                            if($itemInfo["excnum"]>=0 && $itemInfo["excnum"]<=15)
                            {
                                $optName="+".$itemInfo["intopt"]."% HP Recovery";
                                $itemInfo["lifeLvl"] = $itemInfo["intopt"];
                            }
                            else if(($itemInfo["excnum"]>=64 && $itemInfo["excnum"]<=79))
                            {
                                if($itemInfo["excnum"]-64>=0)
                                {
                                $optName="+".($itemInfo["intopt"]+4)."% HP Recovery";
                                $itemInfo["lifeLvl"] = $itemInfo["intopt"] + 4;
                                $itemInfo["excnum"]-=64;
                                }
                            }

                            if($itemInfo["lifeLvl"]>0 && isset($optName))
                            {
                                $itemInfo["lifeOpt"] = $optName;
                                return $itemInfo;
                            }
                        }
                        return $itemInfo;
                    }
                    #endregion
                    //region dw 3и винги
                    else if($itemInfo["group"]==12 && $itemInfo["id"]==37)
                    {
                        if(($itemInfo["excnum"]>=32 && $itemInfo["excnum"]<=47) OR $itemInfo["excnum"]>=96)
                        {
                            if($itemInfo["excnum"]>=32 && $itemInfo["excnum"]<96)
                            {
                                if($itemInfo["excnum"] - 32 >=0)
                                {
                                $optName.="Additional Defence +".($itemInfo["intopt"]*4);
                                $itemInfo["lifeLvl"] = $itemInfo["intopt"];
                                $itemInfo["excnum"]-=32;
                                }
                            }
                            else
                            {
                                if($itemInfo["excnum"] - 96 >=0)
                                {
                                $optName.="Additional Defence +".($itemInfo["intopt"]*4+16)."%";
                                $itemInfo["lifeLvl"] = $itemInfo["intopt"] + 4;
                                $itemInfo["excnum"]-=96;
                                }
                            }
                            if($itemInfo["lifeLvl"]>0 && isset($optName))
                            {
                                $itemInfo["lifeOpt"] = $optName;
                                return $itemInfo;
                            }
                        }
                        else if (($itemInfo["excnum"]>=16 && $itemInfo["excnum"]<=31) or ($itemInfo["excnum"]>=80 && $itemInfo["excnum"]<=95))
                        {
                            if($itemInfo["excnum"]>=16 && $itemInfo["excnum"]<=31)
                            {
                                if($itemInfo["excnum"]-16>=0)
                                {
                                $optName.="Wizardry dmg +".($itemInfo["intopt"]*4);
                                $itemInfo["lifeLvl"] = $itemInfo["intopt"];
                                $itemInfo["excnum"]-=16;
                                }
                            }
                            else
                            {
                                if($itemInfo["excnum"]-80>=0)
                                {
                                $optName.="Wizardry dmg +".($itemInfo["intopt"]*4+16)."%";
                                $itemInfo["lifeLvl"] = $itemInfo["intopt"] + 4;
                                $itemInfo["excnum"]-=80;
                                }
                            }
                            if($itemInfo["lifeLvl"]>0 && isset($optName))
                            {
                                $itemInfo["lifeOpt"] = $optName;
                                return $itemInfo;
                            }
                        }
                        elseif (($itemInfo["excnum"]>=0 && $itemInfo["excnum"]<=47) OR($itemInfo["excnum"]>=64 && $itemInfo["excnum"]<=79))
                        {

                            if($itemInfo["excnum"]==0)
                            {
                                $optName="+".$itemInfo["intopt"]."%  HP Recovery";
                                $itemInfo["lifeLvl"] = $itemInfo["intopt"];
                            }
                            else
                            {
                                if($itemInfo["excnum"]-64>=0)
                                {
                                $optName="+".($itemInfo["intopt"]+4)."%  HP Recovery";
                                $itemInfo["lifeLvl"] = $itemInfo["intopt"] + 4;
                                $itemInfo["excnum"]-=64;
                                }
                            }

                            if($itemInfo["lifeLvl"]>0 && isset($optName))
                            {
                                $itemInfo["lifeOpt"] = $optName;
                                return $itemInfo;
                            }
                        }

                        if ( isset($itemInfo["lifeLvl"]) && $itemInfo["lifeLvl"]>0)
                            return $itemInfo;
                        else
                        {
                            unset($itemInfo["lifeLvl"],$itemInfo["lifeOpt"]);
                        }
                    }
                    //endregion
                   //region dk 3и винги
                    else if($itemInfo["group"]==12 && $itemInfo["id"]==36)
                    {
                        if(($itemInfo["excnum"]>=32 && $itemInfo["excnum"]<=47) OR $itemInfo["excnum"]>=96)
                        {
                            if($itemInfo["excnum"]>=32 && $itemInfo["excnum"]<96)
                            {
                                if($itemInfo["excnum"]-32>=0)
                                {
                                $optName="Additional Defence +".($itemInfo["intopt"]*4);
                                $itemInfo["lifeLvl"] = $itemInfo["intopt"];
                                $itemInfo["excnum"]-=32;
                                }
                            }
                            else
                            {
                                if($itemInfo["excnum"]-96>=0)
                                {
                                $optName="Additional Defence +".($itemInfo["intopt"]*4+16)."%";
                                $itemInfo["lifeLvl"] = $itemInfo["intopt"] + 4;
                                $itemInfo["excnum"]-=96;
                                }
                            }
                            if($itemInfo["lifeLvl"]>0 && isset($optName))
                            {
                                $itemInfo["lifeOpt"] = $optName;
                                return $itemInfo;
                            }
                        }
                        else if (($itemInfo["excnum"]>=16 && $itemInfo["excnum"]<=31) or ($itemInfo["excnum"]>=80 && $itemInfo["excnum"]<=95))
                        {
                            if($itemInfo["excnum"]>=16 && $itemInfo["excnum"]<80)
                            {
                                if($itemInfo["excnum"]-16>=0)
                                {
                                $optName="Additional dmg +".($itemInfo["intopt"]*4);
                                $itemInfo["lifeLvl"] = $itemInfo["intopt"];
                                $itemInfo["excnum"]-=16;
                                }
                            }
                            else
                            {
                                if($itemInfo["excnum"]-80>=0)
                                {
                                $optName="Additional dmg +".($itemInfo["intopt"]*4+16)."%";
                                $itemInfo["lifeLvl"] = $itemInfo["intopt"] + 4;
                                $itemInfo["excnum"]-=80;
                                }
                            }
                            if($itemInfo["lifeLvl"]>0 && isset($optName))
                            {
                                $itemInfo["lifeOpt"] = $optName;
                                return $itemInfo;
                            }
                        }
                        elseif (($itemInfo["excnum"]>=0 && $itemInfo["excnum"]<=47) OR($itemInfo["excnum"]>=64 && $itemInfo["excnum"]<=79))
                        {

                            if($itemInfo["excnum"]==0 && $itemInfo["intopt"]>0)
                            {
                                $optName="HP Recovery +".$itemInfo["intopt"]."%";
                                $itemInfo["lifeLvl"] = $itemInfo["intopt"];
                            }
                            else if($itemInfo["excnum"]>=64)
                            {
                                if($itemInfo["excnum"]-64>=0)
                                {
                                $optName="HP Recovery +".($itemInfo["intopt"]+4)."%";
                                $itemInfo["lifeLvl"] = $itemInfo["intopt"] + 4;
                                $itemInfo["excnum"]-=64;
                                }
                            }

                            if($itemInfo["lifeLvl"]>0 && isset($optName))
                            {
                                $itemInfo["lifeOpt"] = $optName;
                                return $itemInfo;
                            }
                        }
                        return $itemInfo;
                    }
                    #endregion
                    #region rf 3и винги
                    else if($itemInfo["group"]==12 && $itemInfo["id"]==50)
                    {
                        if(($itemInfo["excnum"]>=32 && $itemInfo["excnum"]<=47) OR $itemInfo["excnum"]>=96)
                        {
                            if($itemInfo["excnum"]>=32 && $itemInfo["excnum"]<96)
                            {
                                if($itemInfo["excnum"]-32>=0)
                                {
                                $optName.="Additional Defence +".($itemInfo["intopt"]*4);
                                $itemInfo["lifeLvl"] = $itemInfo["intopt"];
                                $itemInfo["excnum"]-=32;
                                }
                            }
                            else
                            {
                                if($itemInfo["excnum"]-96>=0)
                                {
                                $optName.="Additional Defence +".($itemInfo["intopt"]*4+16)."%";
                                $itemInfo["lifeLvl"] = $itemInfo["intopt"] + 4;
                                $itemInfo["excnum"]-=96;
                                }
                            }
                            if($itemInfo["lifeLvl"]>0 && isset($optName))
                            {
                                $itemInfo["lifeOpt"] = $optName;
                                return $itemInfo;
                            }
                        }
                        else if (($itemInfo["excnum"]>=16 && $itemInfo["excnum"]<=31) or ($itemInfo["excnum"]>=80 && $itemInfo["excnum"]<=95))
                        {
                            if($itemInfo["excnum"]>=16 && $itemInfo["excnum"]<80)
                            {
                                if($itemInfo["excnum"]-16>=0)
                                {
                                $optName.="Additional dmg +".($itemInfo["intopt"]*4);
                                $itemInfo["lifeLvl"] = $itemInfo["intopt"];
                                $itemInfo["excnum"]-=16;
                                }
                            }
                            else
                            {
                                if($itemInfo["excnum"]-80>=0)
                                {
                                $optName.="Additional dmg +".($itemInfo["intopt"]*4+16)."%";
                                $itemInfo["lifeLvl"] = $itemInfo["intopt"] + 4;
                                $itemInfo["excnum"]-=80;
                                }
                            }
                            if($itemInfo["lifeLvl"]>0 && isset($optName))
                            {
                                $itemInfo["lifeOpt"] = $optName;
                                return $itemInfo;
                            }
                        }
                        elseif (($itemInfo["excnum"]>=0 && $itemInfo["excnum"]<=47) OR($itemInfo["excnum"]>=64 && $itemInfo["excnum"]<=79))
                        {

                            if(($itemInfo["excnum"]>=0 && $itemInfo["excnum"]<=47) && $itemInfo["intopt"]>0)
                            {
                                $optName="HP Recovery +".$itemInfo["intopt"]."%";
                                $itemInfo["lifeLvl"] = $itemInfo["intopt"];
                            }
                            else if ($itemInfo["excnum"]>=64 && $itemInfo["excnum"]<=79)
                            {
                                if($itemInfo["excnum"]-64>=0)
                                {
                                $optName="HP Recovery +".($itemInfo["intopt"]+4)."%";
                                $itemInfo["lifeLvl"] = $itemInfo["intopt"] + 4;
                                $itemInfo["excnum"]-=64;
                                }
                            }
                            if($itemInfo["lifeLvl"]>0 && isset($optName))
                            {
                                $itemInfo["lifeOpt"] = $optName;
                                return $itemInfo;
                            }
                        }
                        return $itemInfo;
                    }
                    #endregion
                }
                else // все остальные винги
                {
                    if ($itemInfo["excnum"] <32 or ($itemInfo["excnum"]>=64 && $itemInfo["excnum"]<96) ) // wizardy dmg
                    {
                        if($itemInfo["excnum"]<64)
                        {
                            $optName=$wing_opt[0]."+".($itemInfo["intopt"]*4)."%";
                            $itemInfo["lifeLvl"] = $itemInfo["intopt"];
                        }
                        else
                        {
                            $optName=$wing_opt[0]."+".($itemInfo["intopt"]*4+16)."%";
                            $itemInfo["lifeLvl"] = $itemInfo["intopt"] + 4;
                            $itemInfo["excnum"]-=64;
                        }
                        if($itemInfo["lifeLvl"]>0 && isset($optName))
                        {
                            $itemInfo["lifeOpt"] = $optName;
                            return $itemInfo;
                        }
                    }
                    elseif($itemInfo["excnum"]>=32 or $itemInfo["excnum"] >=96) // add dmg
                    {
                        if($itemInfo["excnum"]>=32 && $itemInfo["excnum"]<96)
                        {
                            $optName =$wing_opt[1]."+".($itemInfo["intopt"]*4)."%";
                            $itemInfo["lifeLvl"] = $itemInfo["intopt"];
                            $itemInfo["excnum"]-=32;
                        }
                        else
                        {
                            $optName=$wing_opt[1]."+".($itemInfo["intopt"]*4+16)."%";
                            $itemInfo["lifeLvl"] = $itemInfo["intopt"] + 4;
                            $itemInfo["excnum"]-=96;
                        }
                        if($itemInfo["lifeLvl"]>0 && isset($optName))
                        {
                            $itemInfo["lifeOpt"] = $optName;
                            return $itemInfo;
                        }

                    }

                    if($itemInfo["excnum"]<=15 or ($itemInfo["excnum"]>=64 && $itemInfo["excnum"]<=79 )) //add dmg
                    {
                        if ($itemInfo["excnum"]<=15 && $itemInfo["intopt"]>0)
                        {
                            $optName= $wing_opt[1]."+".($itemInfo["intopt"]*4)."%";
                            $itemInfo["lifeLvl"] = $itemInfo["intopt"];
                        }
                        else if($itemInfo["excnum"]>=64 && $itemInfo["excnum"]<=79 )
                        {
                            $optName= $wing_opt[1]."+".($itemInfo["intopt"]*4+16)."%";
                            $itemInfo["lifeLvl"] = $itemInfo["intopt"] + 4;
                            $itemInfo["excnum"]-=64;
                        }
                        else
                        {
                            unset($optName);
                        }
                        if($itemInfo["lifeLvl"]>0 && isset($optName))
                        {
                            $itemInfo["lifeOpt"] = $optName;
                            return $itemInfo;
                        }
                    }
                    else //wizardy dmg
                    {
                        if($itemInfo["excnum"]>=32 && $itemInfo["excnum"]<=47)
                        {
                            $optName= $wing_opt[0]."+".($itemInfo["intopt"]*4)."%";
                            $itemInfo["lifeLvl"] = $itemInfo["intopt"];
                            $itemInfo["excnum"]-=32;
                        }
                        else if($itemInfo["excnum"]>=96)
                        {
                            $optName= $wing_opt[0]."+".($itemInfo["intopt"]*4+16)."%";
                            $itemInfo["lifeLvl"] = $itemInfo["intopt"] + 4;
                            $itemInfo["excnum"]-=96;
                        }
                        if($itemInfo["lifeLvl"]>0 && isset($optName))
                        {
                            $itemInfo["lifeOpt"] = $optName;
                            return $itemInfo;
                        }

                    }
                }
            }

            if(isset($optName) && $itemInfo["lifeLvl"]>0)
                $itemInfo["lifeOpt"] = $optName;
        }
        return $itemInfo;
    }

    /**
     * Экселлентные опции
     * @param $itemInfo
     * @param integer $type
     * 1 оружие или пенданты
     * 2 щиты, сеты, кольца
     * 3 винги и плащи
     * 4 fenrir
     */
    private static function getExcellent($itemInfo,$type)
    {

        if($itemInfo["group"] == 12)
        {
            $wings = array(0,1,2); //винги в 12 группе
            if (in_array($itemInfo["id"],$wings))
            {
                return $itemInfo;
            }
        }
        $excoptar = array();
        $ex = $itemInfo["excnum"];
        switch($type)
        {
            case 1://weapons
                $excoptar[0]="Mana After Hunting Monsters +mana/8";
                $excoptar[1]="Life After Hunting Monsters +life/8";
                $excoptar[2]="Increase Attacking(Wizardy) speed +7";
                $excoptar[3]="Increase Damage +2%";
                $excoptar[4]="Increase Damage +Level/20";
                $excoptar[5]="Excellent Damage Rate +10%";
                break;
            case 2://armors
                $excoptar[0]="Increase Rate of Zen 30%";
                $excoptar[1]="Defense Success Rate +10%";
                $excoptar[2]="Reflect Damage +5%";
                $excoptar[3]="Damage Decrease +4%";
                $excoptar[4]="Increase Max Mana +4%";
                $excoptar[5]="Increase Max Hp +4%";
                break;
            case 3: //2nd wings
                $excoptar[0]="Increase Life +".(50+$itemInfo["level"] * 5);
                $excoptar[1]="Increase Mana +".(50+$itemInfo["level"] * 5);
                $excoptar[2]="Ignore Enemy&#39;s defense 3%";
                if($itemInfo["group"] == 13 && $itemInfo["id"]==30)
                {
                    $excoptar[3]="Increase comand +".(10+$itemInfo["level"] * 5);
                    $excoptar[4]="";
                }
                else
                {
                    $excoptar[3]="+50 Max Stamina";
                    $excoptar[4]="Wizardry Speed +5";
                }

                if($itemInfo["group"] == 12 && $itemInfo["id"]==49)
                    $excoptar[3]="";

               // $excoptar[5]="Not Used";
                break;
            case 4: //fenrir
                switch ($ex)
                {
                    case 1: $excoptar[0] = "Plazma Storm Skill<br>Increase final damage 10%"; break;
                    case 2: $excoptar[0] = "Plazma Storm Skill<br>Absorb final damage 10%"; break;
                    case 4: $excoptar[0] = "Plazma Storm Skill<br>Increase final damage 10%<br>Absorb final damage 10%"; break;
                }
                //$ex=0;
                break;
            //3rd wings
            case 5:

                $excoptar[0]="";
                $excoptar[1]="Ignor opponent&#39;s defensive power by 5%";
                $excoptar[2]="Return's the enemy&#39;s attack power in 5%";
                $excoptar[3]="Complete recovery of life in 5% rate";
                $excoptar[4]="Complete recover of Mana in 5% rate";

                break;
                 //2.5 wings
            case 6:
                $excoptar[0]="Ignor oppinent's defensive power by 3%";
                $excoptar[1]="Complete recovery of life in 5% rate";
                $excoptar[2]="";
                $excoptar[3]="";
                $excoptar[4]="";
                $excoptar[5]="";
                break;
        }

        $isExc = 0;
        if($type == 5)
        {
            /*if (($ex-32)>=0)
            {
                $ex -=32;
                //$isExc++;
            }
            if (($ex-16)>=0)
            {
                $ex -=16;
            }*/
            if (($ex-8)>=0)
            {
                $ex -=8;
                $isExc++;
            }
            else
                unset($excoptar[4]);
            if (($ex-4)>=0)
            {
                $ex -=4;
                $isExc++;
            }
            else
                unset($excoptar[3]);
            if (($ex-2)>=0)
            {
                $ex -=2;
                $isExc++;
            }
            else
                unset($excoptar[2]);
            if (($ex-1)>=0)
            {
                $ex -=1;
                $isExc++;
            }
            else
                unset($excoptar[1]);
        }
        else
        {
            if (($ex-32)>=0)
            {
                $ex -=32;
                $isExc++;
            }
            else
                unset($excoptar[5]);
            if (($ex-16)>=0)
            {
                $ex -=16;
                $isExc++;
            }
            else
                unset($excoptar[4]);
            if (($ex-8)>=0)
            {
                $ex -=8;
                $isExc++;
            }
            else
                unset($excoptar[3]);
            if (($ex-4)>=0)
            {
                $ex -=4;
                $isExc++;
            }
            else
                unset($excoptar[2]);
            if (($ex-2)>=0)
            {
                $ex -=2;
                $isExc++;
            }
            else
                unset($excoptar[1]);
            if (($ex-1)>=0)
            {
                $ex -=1;
                $isExc++;
            }
            else
                unset($excoptar[0]);
        }
        /*if ($ex==0)
            unset( $excoptar[0]);*/

        if($isExc>0)
            $itemInfo["exc"] = $excoptar;

        $itemInfo["etype"]= $type;

        return $itemInfo;
    }

    private static function getEq($input)
    {
        return  array_diff(explode("|",trim(str_replace("\n",'',$input))), array(""));
    }

    /**
     * Вычисление требований к вещи
     * @param $itemInfo
     * @param $file
     * [0]itemdroplevel
     * [1]str
     * [2]agi
     * @return mixed
     */
    private static function getStats($itemInfo,$file)
    {
        /*
         str = intval(0.03*itemdroplevel*str)+20+0.09*itemlevel*str)+(itemoption*5)
         agi = intval(0.03*itemdroplevel*agi)+20+0.09*itemlevel*agi)
         str = intval(0.03*itemdroplevel*str)+20+0.09*itemlevel*str)
         agi = intval(0.03*itemdroplevel*agi)+20+0.09*itemlevel*agi)
         // RequireStrength = (RequireStrength * (ItemDropLevel + ItemLevel * 3) * 3) / 100 + 20
         // RequireAgility = (RequireAgility * (ItemDropLevel + ItemLevel * 3) * 3) / 100 + 20
        // RequireEnergy = (RequireEnergy * (ItemDropLevel + ItemLevel * 3 ) ) * 4) / 100 + 20
        -- For Items from section 5 where Slot = 1
        // RequireEnergy = (RequireEnergy * (ItemDropLevel + ItemLevel) ) * 3) / 100 + 20
         */
        $itemInfo["str"] = intval(($file[1] * ($file[0] + $itemInfo["level"] *3) *3)/100 + 20);
        if($itemInfo["group"]<6)
            $itemInfo["agi"] = intval(($file[2] * ($file[0] + $itemInfo["level"] *3) *3)/100 + 20);
        else
            $itemInfo["agi"] = intval((0.03*$file[0]*$file[2])+20+0.09*$itemInfo["level"]*$file[2]);
        //$itemInfo["str"] = intval(((0.03*$file[0]*$file[1])+20+0.09*$itemInfo["level"]*$file[1])+($itemInfo["lifeOpt"]));
        //

        return $itemInfo;
    }

    /**
     * рассчет прочности вещи
     * @param integer $level уровень вещи
     * @return int
     */
    private static function CalcDur($level)
    {
        $level++;
        $array = array(1=>1,2=>1,3=>1,4=>1,5=>2,6=>2,7=>2,8=>2,9=>2,10=>3,11=>4,12=>5,13=>6,14=>7,15=>8);
        $dur = 0;
        for ($i=1;$i<$level;$i++)
        {
           $dur+=$array[$i];
        }
        return $dur;
        //TODO: оптимизировать, возмонжно, изменить алгоритм
    }

    /**
     * Рассчет демейджа у вещи
     * @param array $itemInfo информация по вещи
     * @return int дмг
     */
    static private function GetDmg($itemInfo)
    {
        /*
         * dmg_min = for lvl 1-9 +3; 10 +4; 11 +5; 12 +6; 13+7
           dmg_max = for lvl 1-9 +3; 10 +4; 11 +5; 12 +6; 13+7
           dmg_min = normal +30
           dmg_max = normal + 30
         */
        $dmg = 0;

        if ($itemInfo["level"]-9 <=0)
            $dmg = $itemInfo["level"]*3;
        else if($itemInfo["level"]-9>0)
        {
            $dmg = 27;
            for($i=10; $i<=$itemInfo["level"];$i++)
            {
                $dmg += $i - 9 + 3;
            }
        }
        return $dmg;
    }

    /**
     * рассчет дефенса вещи (кроме щитов)
     * @param $itemInfo данные по вещи
     * @param $file данные по базе
     * @param bool $isExe экселлент
     * @return int деф
     */
    private static function getDef($itemInfo,$file,$isExe = false)
    {
        //def = for lvl 1-9 +3; 10 +4; 11 +5; 12 +6; 13+7
        //def = +def*itemlevel // for shields
        //$file [0] def
        //$file [1] itemdroplevel
        $def = 0;

        if ($itemInfo["level"]-9 <=0)
            $def = $itemInfo["level"]*3;
        else
        {
            $def = 27;
            for($i=10; $i<=$itemInfo["level"];$i++)
            {
                $def += $i - 9 + 3;
            }

            $def+=$file[0];

            if($isExe)
                $def+= intval($file[0]*12/$file[1])+4+intval($itemInfo["level"]/5);

        }
        return $def;
    }

    /**
     * рассчет уровня, наличия скилла, лака на вещи
     * @param $itemInfo - массив с данными по вещи
     * @return array
     */
    static private function getOtions($itemInfo)
    {

        if($itemInfo["intopt"]>=128)
        {
            $itemInfo["isSkill"] = 1;
            $itemInfo["intopt"]-=128;
        }

        $itemInfo["level"] = (integer)($itemInfo["intopt"]/8);
        $itemInfo["intopt"] -= $itemInfo["level"]*8;

        if ($itemInfo["intopt"]>3)
        {
            $itemInfo["isLuck"] = 1;
            $itemInfo["intopt"] -= 4;
        }

        if (!isset($itemInfo["level"]))
            $itemInfo["level"]=0;

        return $itemInfo;
    }

    /**
     * чтение нужного файла с данными
     * @param $name название файла итема
     * @return array|null
     */
    static private function readfile($name)
    {
        $filep = "build/muonline/_dat/items/$name.mwc";
        if(file_exists($filep))
        {
            return file($filep);
        }
        else
            return null;
    }

    /**
     * из 16 в 10 систему
     * @param $hex
     * @param $begin откуда начать
     * @param $length чем закончить
     * @return number 10чное число
     */
    static public function dehex($hex,$begin,$length)
    {
        return hexdec(substr($hex,$begin,$length));
    }

    /**
     * функция поиска свободного места в сундуке
     * @param $item_hex (string) хекс сундука
     * @param $x
     * @param $y
     * @param $itembd краткая база вещей
     * @return int номер позиции для вещи
     */
    static public function smartsearch64($item_hex,$x,$y,$itembd)
    {

        if(empty($x) || empty($y) || $x<=0 || $y<=0)
            return -1;
        $item_hex=strtoupper($item_hex);
        $col_i = (int)strlen($item_hex)/64;
        //$col_i = 120;

        $itemarr = array();
        for ($i=0;$i<$col_i;$i++)
        {
            if (!isset($itemarr[$i]) || strlen($itemarr[$i])==64)
                $itemarr[$i] = substr($item_hex,$i*64, 64);

            if ($itemarr[$i]!="FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF" && strlen($itemarr[$i])==64)
            {
                $it["id"] = hexdec(substr($itemarr[$i],0,2)); // ID
                $it["group"] = hexdec(substr($itemarr[$i],18,1)); // group
              //  $xin = substr($itembd[$it["group"]][$it["id"]][1],0,1);
              //  $yin = substr($itembd[$it["group"]][$it["id"]][1],1,1);

                if ($it["group"] == 7 || ($it["group"]>=9 && $it["group"]<=11 && $it["id"]!=128) || $it["group"]==15)
                {
                    $xin = substr($itembd[$it["group"]][0][1],0,1);
                    $yin = substr($itembd[$it["group"]][0][1],1,1);
                }
                else
                {
                    $xin = substr($itembd[$it["group"]][$it["id"]][1],0,1);
                    $yin = substr($itembd[$it["group"]][$it["id"]][1],1,1);
                }
                $j=$xin*$yin;
                $str=$i;
                $x1=0;
                while($j>0)
                {
                    if($x1<=$xin)
                    {
                        $itemarr[$str]="not_empty";
                        $str++;
                        $x1++;
                        if($x1==$xin)
                        {
                            $str +=(8-$xin);
                            $x1=0;
                        }
                    }
                    $j--;
                }
            }
        }
        $c=0;

        for ($i=0;$i<$col_i;$i++)
        {
            $j = $x * $y;
            $str = $i;
            $x1=0;
            $found=0;
            $ind = ((floor($i/8)+1)*8)-1; // правый конец строки
            $raz = $i+($x-1);

            while($j>0)
            {
                if($x1<=$x)
                {
                    if (strlen($itemarr[$str])==64 && $str<$col_i && $raz<=$ind) $found++; else {$j=0;$found=0;}
                    $str++;
                    $x1++;
                    if($x1==$x)
                    {
                        $str +=(8-$x);
                        $ind+=8;
                        $x1=0;
                    }
                }
                $j--;
            }
            if ($found == $x*$y && ($i-$col_i)>0)
            {
               // unset($itemarr);
                return $i;
            }
        }
        unset($itemarr);
        return -1;
    }


    static public function search($item_hex,$x,$y,$itembd,$size,$col_i)
    {
        if(empty($x) || empty($y) || $x<=0 || $y<=0 || empty($itembd))
            return -1;
        $item_hex=strtoupper($item_hex);
       // $col_i = (int)strlen($item_hex)/$size;

        $emptyItm="";
        for ($z_ =0;$z_<$size;$z_++)
        {
            $emptyItm.="F";
        }
        //$col_i = 120;

        $itemarr = array();
        for ($i=0;$i<$col_i;$i++)
        {
            if (!isset($itemarr[$i]) || strlen($itemarr[$i])==$size)
                $itemarr[$i] = substr($item_hex,$i*$size, $size);

            if ($itemarr[$i]!=$emptyItm && strlen($itemarr[$i])==$size)
            {
                $it["id"] = hexdec(substr($itemarr[$i],0,2)); // ID
                $it["group"] = hexdec(substr($itemarr[$i],18,1)); // group

                if ($it["group"] == 7 || ($it["group"]>=9 && $it["group"]<=11 && $it["id"]!=128) || $it["group"]==15)
                {
                    $xin = substr($itembd[$it["group"]][0][1],0,1);
                    $yin = substr($itembd[$it["group"]][0][1],1,1);
                }
                else
                {
                    $xin = substr($itembd[$it["group"]][$it["id"]][1],0,1);
                    $yin = substr($itembd[$it["group"]][$it["id"]][1],1,1);
                }
                $j=$xin*$yin;
                $str=$i;
                $x1=0;
                while($j>0)
                {
                    if($x1<=$xin)
                    {
                        $itemarr[$str]="not_empty";
                        $str++;
                        $x1++;
                        if($x1==$xin)
                        {
                            $str +=(8-$xin);
                            $x1=0;
                        }
                    }
                    $j--;
                }
            }
        }
        $c=0;

        for ($i=0;$i<$col_i;$i++)
        {
            $j = $x * $y;
            $str = $i;
            $x1=0;
            $found=0;
            $ind = ((floor($i/8)+1)*8)-1; // правый конец строки
            $raz = $i+($x-1);

            while($j>0)
            {
                if($x1<=$x)
                {
                    if (strlen($itemarr[$str])==64 && $str<$col_i && $raz<=$ind) $found++; else {$j=0;$found=0;}
                    $str++;
                    $x1++;
                    if($x1==$x)
                    {
                        $str +=(8-$x);
                        $ind+=8;
                        $x1=0;
                    }
                }
                $j--;
            }
            if ($found == $x*$y && $i<$col_i)
            {
                return $i;
            }
        }
        return -1;
    }

    /**
     * функция поиска свободного места в сундуке
     * @param $item_hex (string) хекс сундука
     * @param $x
     * @param $y
     * @param $itembd краткая база вещей
     * @return int номер позиции для вещи
     */
    static public function smartsearch32($item_hex,$x,$y,$itembd)
    {
        if(empty($x) || empty($y) || $x<=0 || $y<=0 || empty($itembd))
            return -1;
      //  if ($tt==0)
       // {
       //     if (substr($item_hex,0,2)=='0x') $item_hex=substr($item_hex,2);
       //     else $item_hex=strtoupper(urlencode(bin2hex($item_hex)));
       // }
        $item_hex=strtoupper($item_hex);
        $col_i = strlen($item_hex)/32-1;
        //$col_i = 120;

        $itemarr = array();
        for ($i=0;$i<$col_i;$i++)
        {
            if (!isset($itemarr[$i]) || strlen($itemarr[$i])==32)$itemarr[$i] = substr($item_hex,$i*32, 32);
            if ($itemarr[$i]!="FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF" && strlen($itemarr[$i])==32)
            {
                $it["id"] = hexdec(substr($itemarr[$i],0,2)); // ID
                $it["group"] = hexdec(substr($itemarr[$i],18,1)); // group

                if ($it["group"] == 7 || ($it["group"]>=9 && $it["group"]<=11 && $it["id"]!=128) || $it["group"]==15)
                {
                    $xin = substr($itembd[$it["group"]][0][1],0,1);
                    $yin = substr($itembd[$it["group"]][0][1],1,1);
                }
                else
                {
                    $xin = substr($itembd[$it["group"]][$it["id"]][1],0,1);
                    $yin = substr($itembd[$it["group"]][$it["id"]][1],1,1);
                }
                $j=$xin*$yin;
                $str=$i;
                $x1=0;
                while($j>0)
                {
                    if($x1<=$xin)
                    {
                        $itemarr[$str]="not_empty";
                        $str++;
                        $x1++;
                        if($x1==$xin)
                        {
                            $str +=(8-$xin);
                            $x1=0;
                        }
                    }
                    $j--;
                }
            }
        }
        $c=0;

        for ($i=0;$i<$col_i;$i++)
        {
            $j = $x * $y;
            $str = $i;
            $x1=0;
            $found=0;
            $ind = ((floor($i/8)+1)*8)-1; // правый конец строки
            $raz = $i+($x-1);

            while($j>0)
            {
                if($x1<=$x)
                {
                    if (strlen($itemarr[$str])==32 && $str<$col_i && $raz<=$ind) $found++; else {$j=0;$found=0;}
                    $str++;
                    $x1++;
                    if($x1==$x)
                    {
                        $str +=(8-$x);
                        $ind+=8;
                        $x1=0;
                    }
                }
                $j--;
            }
            if ($found == $x*$y )
                return $i;
        }

        return -1;
    }

    /**
     * раделение вещи на логические классы: АА или камни, для подсветки
     * @param $input
     * @return mixed
     */
    static private function getHilight($input)
    {
        if (isset(self::$divine[$input["group"]]))
        {
            if(is_array(self::$divine[$input["group"]]))
            {
                if (isset(self::$divine[$input["group"]][$input["id"]]))
                {
                    $input["isDivine"] = 1;
                    return $input;
                }
            }
            else
            {
                if(self::$divine[$input["group"]] == $input["id"])
                {
                    $input["isDivine"] = 1;
                    return $input;
                }
            }
        }
        elseif (isset(self::$jewels[$input["group"]]))
        {
            if(is_array(self::$jewels[$input["group"]]))
            {
                if (isset(self::$jewels[$input["group"]][$input["id"]]))
                {
                    $input["isJewel"] = 1;
                    return $input;
                }
            }
            else
            {
                if(self::$jewels[$input["group"]] == $input["id"])
                {
                    $input["isJewel"] = 1;
                    return $input;
                }
            }
        }
        return $input;
    }


}

//endregion


/**
 * @param $hex хекс
 * @param $harm база хармони опций
 * @param int $type тип отображения
 * @param int $inx
 * @param string $saddr
 * @return string
 */
function itemShow($hex,$harm,$type=0,$inx=0,$saddr="")
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
    if (is_array($item["exc"]))
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
        if (file_exists("theme/imgs/items/{$item["img"]}.gif"))
            $d_caption.="<li><img src='{$saddr}theme/imgs/items/{$item["img"]}.gif' border='0'></li>";
        else if (file_exists("theme/imgs/items/{$item["img"]}.png"))
            $d_caption.="<li><img src='{$saddr}theme/imgs/items/{$item["img"]}.png' border='0'></li>";
        else
            $d_caption.="<li>{$item["img"]}.gif/png</li>";

    }

    if (file_exists("theme/imgs/items/{$item["img"]}.gif"))
        $img="<img src='{$saddr}theme/imgs/items/{$item["img"]}.gif' border='0'>";
    else if (file_exists("theme/imgs/items/{$item["img"]}.png"))
        $img="<img src='{$saddr}theme/imgs/items/{$item["img"]}.png' border='0'>";
   // else
   //     $img="<li>{$item["img"]}.gif/png</li>";


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

    if($item["isLuck"]>0)
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

    if(is_array($item["exc"]))
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
        return "<a href='#' rel='{$saddr}sbase.php?act=1&h={$itm}'>{$img}</a>";
}

/**
 * вывод в строке по итемам
 * @param $hex
 * @param $harm
 * @param int $type
 * @return array
 */
function itemListShow($hex,$harm,$type=0)
{
    if($type==0)
        $item = rItem::Read($hex,$harm); //читаем вещь
    else
        $item = $hex;

    if (isset($item["isDivine"]) && $item["isDivine"] == 1)
    {
        $name_class = "I_Divine";
    }
    if (isset($item["isJewel"]) && $item["isJewel"] == 1)
    {
        $name_class = "I_Normal7";
    }

    if (is_array($item["exc"]))
    {
        $wings = array(0,1,2,3,4,5,6,7,8,9,36,37,38,39,40,41,42,43,49,50,130,131,132,133,134,135,262,263,264,265); //винги в 12 группе
        if(($item["group"] == 12 && in_array($item["id"],$wings)) OR ($item["group"]==13 && $item["id"]==30))
        {
            $name_class = "I_Normal";
        }
        else
        {
            $item["name"] = "Excellent ".$item["name"];
            $name_class = "I_Excellent";
        }
    }

    if(!isset($name_class) or $name_class == "I_Normal")
    {
        if ($item["level"]>6)
            $name_class = "I_Normal7";
        else if($item["level"]>2)
            $name_class = "I_Normal3";
        else
            $name_class = "I_Normal";
    }

    if (isset($item["level"]) && $item["level"]>0)
        $item["name"].=" +".$item["level"];

    if($item["isLuck"]>0)
    {
        $item["name"].= " +luck";
        if($name_class != "I_Excellent" && $item["level"]<3 && $name_class!= "I_NormalOpt" && $name_class!="I_Divine" && !isset($item["isJewel"]))
            $name_class = "I_NormalOpt";
    }

    if(isset($item["isSkill"]))
    {
        $item["name"].= " +skill";
        if($name_class != "I_Excellent" && $item["level"]<3 && $name_class!= "I_NormalOpt" && $name_class!="I_Divine" && !isset($item["isJewel"]))
            $name_class = "I_NormalOpt";
    }
    if(isset($item["lifeOpt"]) && $item["lifeLvl"]>0)
    {
        $item["name"].= " +options";
        if($name_class != "I_Excellent" && $item["level"]<3 && $name_class!= "I_NormalOpt" && $name_class!="I_Divine" && !isset($item["isJewel"]))
            $name_class = "I_NormalOpt";
    }

    if(isset($item["anc1"]) && $item["anc1"]!="no" && strlen($item["anc1"])>2)
    {
        $item["name"] = $item["anc1"]." ".$item["name"];
        $name_class = "I_Ancient_l";
    }
    else if(isset($item["anc2"]) && $item["anc2"]!="no" && strlen($item["anc2"])>2)
    {
        $item["name"] = $item["anc2"]." ".$item["name"];
        $name_class = "I_Ancient_l";

    }

    if($item["ispvp"]>0)
    {
        $item["name"].= " +pvp";
    }

    if(isset($item["harmony"]))
    {
        $item["name"].= " +harmony";
    }

    $return = array($item["name"],$name_class);

    return $return;
}

function Sockets($hex)
{
    if(file_exists("build/muonline/_dat/items/sockets.php"))
    {
      if ($hex!="FFFFFFFFFF" or $hex!="0000000000")
      {
          require "build/muonline/_dat/items/sockets.php";
          $i=0;
          $sockets = "";
          $j =1;
          while ($i<10)
          {
              if ($socket[0][substr($hex,$i,2)]!="") $sockets.="<br>Socket $j:".$socket[0][substr($hex,$i,2)];
              $i+=2;
              $j++;
          }
      }
    }
    else
    {
    $sockets =array();
    if ($hex!="FFFFFFFFFF" or $hex!="0000000000")
    {
        $socket = array( 'FE' => 'Empty socket',

            '0F' => 'Fire((Level 1) Attack,Wizardry Increase)',
            '32' => 'Fire((Level 2) Attack,Wizardry Increase)',
            '64' => 'Fire((Level 3) Attack,Wizardry Increase)',
            '96' => 'Fire((Level 4) Attack,Wizardry Increase)',
            'C8' => 'Fire((Level 5) Attack,Wizardry Increase)',

            '01' => 'Fire(Attack speed Increase +10)',
            '33' => 'Fire(Attack speed Increase +30)',
            '65' => 'Fire(Attack speed Increase +50)',
            '97' => 'Fire(Attack speed Increase +70)',
            'C9' => 'Fire(Attack speed Increase +100)',

            '02' => 'Fire(Maximum attack,Wizardry increase 50)',
            '34' => 'Fire(Maximum attack,Wizardry increase 75)',
            '66' => 'Fire(Maximum attack,Wizardry increase 100)',
            '98' => 'Fire(Maximum attack,Wizardry increase 125)',
            'CA' => 'Fire(Maximum attack,Wizardry increase 150)',

            '03' => 'Fire (Increase Maximum Damage/Skill Power) + 30',

            '04' => 'Fire(Attack, Wizardry increase 50)',
            '36' => 'Fire(Attack, Wizardry increase 75)',
            '68' => 'Fire(Attack, Wizardry increase 100)',
            '9A' => 'Fire(Attack, Wizardry increase 125)',
            'CC' => 'Fire(Attack, Wizardry increase 150)',

            '05' => 'Fire(AG cost decrease +2 %)',
            '37' => 'Fire(AG cost decrease +3 %)',
            '69' => 'Fire(AG cost decrease +4 %)',
            '9B' => 'Fire(AG cost decrease +5 %)',
            'CD' => 'Fire(AG cost decrease +6 %)',
            '06' => 'Fire (Decrease AG Use) + 40',

            //'3F' => 'Fire( Minimum attack,Wizardry increase +16 )',
            '35' => 'Fire( Minimum attack,Wizardry increase +17 )',
            '67' => 'Fire( Minimum attack,Wizardry increase +18 )',
            '99' => 'Fire( Minimum attack,Wizardry increase +19 )',

            '38' => 'Fire (Decrease AG Use) + 1',

            'CB' => 'Fire( Minimum attack,Wizardry increase +20 )',

            '0A' => 'Water(Block rating increase +1% )',
            '3C' => 'Water(Block rating increase +2% )',
            '6E' => 'Water(Block rating increase +3% )',
            'A0' => 'Water(Block rating increase +4% )',
            'D2' => 'Water(Block rating increase +5% )',


            '0D' => 'Water(Damage reduction +1%)',
            '3F' => 'Water(Damage reduction +2%)',
            '71' => 'Water(Damage reduction +3%)',
            'A3' => 'Water(Damage reduction +4%)',
            'D5' => 'Water(Damage reduction +5%)',

            '0E' => 'Water(Damage reflection +1%)',
            '40' => 'Water(Damage reflection +2%)',
            '72' => 'Water(Damage reflection +3%)',
            'A4' => 'Water(Damage reflection +4%)',
            'D6' => 'Water(Damage reflection +5%)',

            '0B' => 'Water(Defense increase 50)',
            '3D' => 'Water(Defense increase 75)',
            '6F' => 'Water(Defense increase 100)',
            'A1' => 'Water(Defense increase 125)',
            'D3' => 'Water(Defense increase 150)',

            '0C' => 'Water(Shild protection increase +1%)',
            '3E' => 'Water(Shild protection increase +2%)',
            '70' => 'Water(Shild protection increase +3%)',
            'A2' => 'Water(Shild protection increase +4%)',
            'D4' => 'Water(Shild protection increase +5%)',

            '10' => 'Ice( Monster destruction for the Life increase)lvl 1',
            '42' => 'Ice( Monster destruction for the Life increase)lvl 2',
            '74' => 'Ice( Monster destruction for the Life increase)lvl 3',
            'A6' => 'Ice( Monster destruction for the Life increase)lvl 4',
            'D8' => 'Ice( Monster destruction for the Life increase)lvl 5',

            '11' => 'Ice(Monster destruction for the Mana increase) lvl 1',
            '43' => 'Ice(Monster destruction for the Mana increase) lvl 2',
            '75' => 'Ice(Monster destruction for the Mana increase) lvl 3',
            'A7' => 'Ice(Monster destruction for the Mana increase) lvl 4',
            'D9' => 'Ice(Monster destruction for the Mana increase) lvl 5',

            '12' => 'Ice(Skill attack increase 80)',
            '44' => 'Ice(Skill attack increase 100)',
            '76' => 'Ice(Skill attack increase 120)',
            'A8' => 'Ice(Skill attack increase 150)',
            'DA' => 'Ice(Skill attack increase 170)',

            '13' => 'Ice(Attack rating increase 500)',
            '45' => 'Ice(Attack rating increase 550)',
            '77' => 'Ice(Attack rating increase 600)',
            'A9' => 'Ice(Attack rating increase 650)',
            'DB' => 'Ice(Attack rating increase 700)',

            '14' => 'Ice(Item durability increase 30%)',
            '46' => 'Ice(Item durability increase 32%)',
            '78' => 'Ice(Item durability increase 34%)',
            'AA' => 'Ice(Item durability increase 36%)',
            'DC' => 'Ice(Item durability increase 38%)',

            '15' => 'Wind(Automatic Life recovery increase 6)',
            '47' => 'Wind(Automatic Life recovery increase 12)',
            '79' => 'Wind(Automatic Life recovery increase 20)',
            'AB' => 'Wind(Automatic Life recovery increase 30)',
            'DD' => 'Wind(Automatic Life recovery increase 40)',

            '16' => 'Wind(Maximum Life increase +1%)',
            '48' => 'Wind(Maximum Life increase +2%)',
            '7A' => 'Wind(Maximum Life increase +3%)',
            'AC' => 'Wind(Maximum Life increase +4%)',
            'DE' => 'Wind(Maximum Life increase +5%)',

            '17' => 'Wind(Maximum Mana increase +4%)',
            '49' => 'Wind(Maximum Mana increase +5%)',
            '7B' => 'Wind(Maximum Mana increase +6%)',
            'AD' => 'Wind(Maximum Mana increase +7%)',
            'DF' => 'Wind(Maximum Mana increase +8%)',

            '18' => 'Wind(Automatic Mana recovery increase 30)',
            '4A' => 'Wind(Automatic Mana recovery increase 90)',
            '7C' => 'Wind(Automatic Mana recovery increase 160)',
            'AE' => 'Wind(Automatic Mana recovery increase 230)',
            'E0' => 'Wind(Automatic Mana recovery increase 300)',

            '19' => 'Wind(Maximum AG increase 100)',
            '4B' => 'Wind(Maximum AG increase 150)',
            '7D' => 'Wind(Maximum AG increase 200)',
            'AF' => 'Wind(Maximum AG increase 250)',
            'E1' => 'Wind(Maximum AG increase 300)',

            '1A' => 'Wind(AG value increase 20)',
            '4C' => 'Wind(AG value increase 40)',
            '7E' => 'Wind(AG value increase 60)',
            'B0' => 'Wind(AG value increase 80)',
            'E2' => 'Wind(AG value increase 100)',
            '1B' => 'Wind (Increase AG Amount) + 3',
            '4D' => 'Wind (Increase AG Amount) + 1',

            '1E' => 'Lightning(Excellent damage rate increase+8 %)',
            '50' => 'Lightning(Excellent damage rate increase+9 %)',
            '82' => 'Lightning(Excellent damage rate increase+10 %)',
            'B4' => 'Lightning(Excellent damage rate increase+12 %)',
            'E6' => 'Lightning(Excellent damage rate increase+14 %)',

            '1F' => 'Lightning(Critical damage increase 100)',
            '51' => 'Lightning(Critical damage increase 130)',
            '83' => 'Lightning(Critical damage increase 160)',
            'B5' => 'Lightning(Critical damage increase 200)',
            'E7' => 'Lightning(Critical damage increase 240)',

            '20' => 'Lightning(Critical damage rate increase +8 %)',
            '52' => 'Lightning(Critical damage rate increase +9 %)',
            '84' => 'Lightning(Critical damage rate increase +10 %)',
            'B6' => 'Lightning(Critical damage rate increase +12 %)',
            'E8' => 'Lightning(Critical damage rate increase +14 %)',

            '1D' => 'Lightning(Excellent damage increase 100)',
            '4F' => 'Lightning(Excellent damage increase 130)',
            '81' => 'Lightning(Excellent damage increase 160)',
            'B3' => 'Lightning(Excellent damage increase 200)',
            'E5' => 'Lightning(Excellent damage increase 240)',

            '21' => 'Lightning (Increase Critical Damage Success Rate) + 8',
            '53' => 'Lightning (Increase Critical Damage Success Rate) + 1',
            '85' => 'Lightning (Increase Critical Damage Success Rate) + 1',
            'B7' => 'Lightning (Increase Critical Damage Success Rate) + 1',
            'E9' => 'Lightning (Increase Critical Damage Success Rate) + 1',

            '24' => 'Earth(Health Increase 50)',
            '56' => 'Earth(Health Increase 90)',
            '88' => 'Earth(Health Increase 130)',
            'BA' => 'Earth(Health Increase 170)',
            'EC' => 'Earth(Health Increase 220)',

            '25' => 'Ground (Increase Stamina) + 30',
            '57' => 'Ground (Increase Stamina) + 1',
            '89' => 'Ground (Increase Stamina) + 1',
            'BB' => 'Ground (Increase Stamina) + 1',
            'ED' => 'Ground (Increase Stamina) + 1');
        $i=0;
        $sockets = "";

        $j =1;
        while ($i<10)
        {
            if ($socket[substr($hex,$i,2)]!="") $sockets.="<br>Socket $j:".$socket[substr($hex,$i,2)];
            $i+=2;
            $j++;
        }
    }
    }
    return $sockets;
}




