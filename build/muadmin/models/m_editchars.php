<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 10.10.2015
 *
 **/
class m_editchars extends ausermodel
{
    private $dbase;
    public $class = array(
        0=>"Dark Wizard",
        1=>"Soul Master",
        2=>"Grand Master",
        3=>"Grand Master(jpn)",
        16=>"Dark Knight",
        17=>"Blade Knight",
        18=>"Blade Master",
        19=>"Blade Master(jpn)",
        32=>"Fairy Elf",
        33=>"Muse Elf",
        34=>"High Elf",
        35=>"High Elf(jpn)",
        48=>"Magic Gladiator",
        49=>"Duel Master",
        50=>"Duel Master(jpn)",
        64=>"Dark Lord",
        65=>"Lord Emperor",
        66=>"Lord Emperor(jpn)",
        80=>"Summoner",
        81=>"Bloody Summoner",
        82=>"Dimension Master",
        83=>"Dimension Master(jpn)",
        96=>"Rage Fighter",
        97=>"Fist Master",
        98=>"Fist Master(jpn)",
    );

    public function init()
    {
        //$this->dbase;
        $main = Configs::readCfg("main",$_SESSION["mwccfgread"]);
        $this->dbase = $main["db_name"][0];
    }

    /**
     * показать все по аккаунту
     * @param string $acc
     * @return mixed
     * @throws ADODB_Exception
     * @throws Exception
     */
    public function getAccIfo($acc)
    {
        $result = $this->db->query("SELECT * FROM {$this->dbase}.dbo.MEMB_INFO WHERE memb___id='{$acc}'")->FetchRow();
        $chrs = array();
        $q = $this->db->query("SELECT Name FROM {$this->dbase}.dbo.character WHERE AccountID='$acc'");
        while ($r = $q->FetchRow())
        {
            $chrs[]= $r["Name"];
        }
        $result["Characters"] = $chrs;
        return $result;
    }

    /**
     * узнаем все по персонажу
     * @param string $char
     * @return mixed
     * @throws ADODB_Exception
     * @throws Exception
     */
    public function getChar($char)
    {
         return $this->db->query("SELECT ch.*,mi.ConnectStat,mi.ServerName,mi.ConnectTM,mi.DisConnectTM FROM {$this->dbase}.dbo.Character ch LEFT JOIN {$this->dbase}.dbo.MEMB_STAT mi ON mi.memb___id COLLATE DATABASE_DEFAULT = ch.AccountID COLLATE DATABASE_DEFAULT WHERE ch.Name='$char'")->FetchRow();
    }

    public function saveChar($params)
    {
        $this->db->query("UPDATE {$this->dbase}.dbo.Character SET cLevel ={$params["cLevel"]}, LevelUpPoint={$params["LevelUpPoint"]}, Strength={$params["Strength"]}, Dexterity={$params["Dexterity"]}, Vitality={$params["Vitality"]},Energy={$params["Energy"]},Leadership={$params["Leadership"]},Money={$params["Money"]},MapNumber={$params["MapNumber"]},MapPosX={$params["MapPosX"]},MapPosY={$params["MapPosY"]} WHERE Name='{$params["Name"]}'");
        $this->db->SQLog("{$_SESSION["mwcauser"]} edit char {$params["Name"]}","m_editchars",7);
    }
    public function saveAcc($params)
    {
        if(!empty($params["memb__pwd"]))
        {
            $cfg = Configs::readCfg("main",$_SESSION["mwccfgread"]);
            if((int)$cfg["usemd5"] == 1)
            {
                $r_password = ",memb__pwd = [dbo].[fn_md5]('{$params["memb__pwd"]}','{$params["account"]}')";
            }
            else
                $r_password = ",memb__pwd = '{$params["memb__pwd"]}'";
        }
        else
            $r_password = "";

        $this->db->query("UPDATE {$this->dbase}.dbo.MEMB_INFO SET memb_name = '{$params["memb_name"]}', mail_addr = '{$params["mail_addr"]}', bloc_code = '{$params["bloc_code"]}', mwc_bankZ = {$params["mwc_bankZ"]}, mwc_credits = {$params["mwc_credits"]} $r_password WHERE memb___id='{$params["account"]}'");
        $this->db->SQLog("{$_SESSION["mwcauser"]} edit account {{$params["account"]}}","m_editchars",7);
    }

}