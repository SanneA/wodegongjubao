<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 23.01.2016
 *
 **/
class m_reset extends MuonlineUser
{
    public function getRes($config)
    {
        $charInfo = self::chracterInfo($_SESSION["mwccharacter"],$_SESSION["mwcuser"]);
        $needZen = ($charInfo[$config["rescolumn"]]+1) * $config["resZen"];

        if($needZen > $config["maxZenPrice"])
            $needZen = $config["maxZenPrice"];

        if($charInfo["Class"]>=16 && $charInfo["Class"]<=19)
            $class =16;
        elseif($charInfo["Class"]>=32 && $charInfo["Class"]<=35)
            $class =32;
        elseif($charInfo["Class"]>=48 && $charInfo["Class"]<=50)
            $class =48;
        elseif($charInfo["Class"]>=64 && $charInfo["Class"]<=66)
            $class =64;
        elseif($charInfo["Class"]>=80 && $charInfo["Class"]<=83)
            $class =80;
        elseif($charInfo["Class"]>=96 && $charInfo["Class"]<=98)
            $class =96;

        $points = $config["points"] * ($charInfo[$config["rescolumn"]]+1);

        $q = "";

        if($config["envClean"] == 1)
            $q.=",Inventory = dct.Inventory";
        if($config["skillClean"] == 1)
            $q.=",MagicList = dct.MagicList";

        $this->db->query("
        UPDATE Character SET
cLevel = 1,
Experience = 0,
LevelUpPoint = $points,
Strength = dct.Strength,
Dexterity = dct.Dexterity,
Vitality = dct.Vitality,
Energy = dct.Energy,
Leadership = dct.Leadership,
{$config["rescolumn"]} = {$config["rescolumn"]}+1,
Money = Money - $needZen
$q
FROM
Character INNER JOIN DefaultClassType dct ON dct.Class = $class
WHERE
Name = '{$_SESSION["mwccharacter"]}'");

    }

}