<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 25.12.2015
 *
 **/
class m_nullstat extends MuonlineUser
{
    /**
     * получаем список статов нулевого чара
     * @param int $class
     * @return string|NULL
     * @throws Exception
     */
    public function getStat($class)
    {
        return $this->db->query("SELECT
      [Strength]
      ,[Dexterity]
      ,[Vitality]
      ,[Energy]
      ,[Leadership]
  FROM [dbo].[DefaultClassType] WHERE [Class] = $class")->FetchRow();
    }

    public function getNulled($credit,$level)
    {
        $charInfo = self::chracterInfo($_SESSION["mwccharacter"],$_SESSION["mwcuser"]);

        if($level>$charInfo["cLevel"])
            throw new Exception("l_err_nolvl");//недостаточный уровень

        $class =0;

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

        $stats = self::getStat($class);
        if(empty($stats))
        {
            $this->db->SQLog("warning character {$_SESSION["mwccharacter"]} has unknown class $class","nullstat",1); //если нету результата по статам, значит в базе нету класса, стучим админу.
            throw new Exception("l_err_erroro0");
        }
        else
        {
            $freePt = 0;
            $freePt+= $charInfo["Dexterity"] - $stats["Dexterity"];
            $freePt+= $charInfo["Strength"] - $stats["Strength"];
            $freePt+= $charInfo["Vitality"] - $stats["Vitality"];
            $freePt+= $charInfo["Energy"] - $stats["Energy"];
            $freePt+= $charInfo["Leadership"] - $stats["Leadership"];

            $this->db->query("UPDATE Character SET [Strength] ={$stats["Strength"]}
      ,[Dexterity] ={$stats["Dexterity"]}
      ,[Vitality] ={$stats["Vitality"]}
      ,[Energy]  ={$stats["Energy"]}
      ,[Leadership] = {$stats["Leadership"]}
      ,[LevelUpPoint] = [LevelUpPoint]+ $freePt
      WHERE Name='{$_SESSION["mwccharacter"]}'; UPDATE MEMB_INFO SET mwc_credits = mwc_credits - $credit WHERE memb___id='{$_SESSION["mwcuser"]}'");

        }


    }

}