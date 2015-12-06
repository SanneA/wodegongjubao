<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 24.10.2015
 *
 **/
class m_top100 extends MuonlineUser
{
    protected $unicCfg;

    public  function init()
    {
        $this->unicCfg = Configs::readCfg("unic",tbuild);
    }

    /**
     * @param string $classes
     * @return array
     * @throws ADODB_Exception
     * @throws Exception
     */
    public function getChars($classes)
    {
        $q = $this->db->query("SELECT TOP 100
 ch.Name,
 ch.Class,
 ch.cLevel,
 ch.{$this->unicCfg["rescolumn"]},
 ch.Strength,
 ch.Dexterity,
 ch.Vitality,
 ch.Energy,
 ch.Leadership,
 ch.AccountID,
 ch.{$this->unicCfg["grescolumn"]},
 gm.G_Name as guild,
 CONVERT(varchar(max),gld.G_Mark,2) as g_mark,
 ms.ConnectStat,
 ac.GameIDC,
 CONVERT(varchar(max),ms.ConnectTM,120) as ConnectTM,
 CONVERT(varchar(max),ms.DisConnectTM ,120) as DisConnectTM,ms.ServerName
FROM
 [Character] ch
 left join [GuildMember] gm ON gm.Name COLLATE DATABASE_DEFAULT = ch.Name COLLATE DATABASE_DEFAULT
 left join [Guild] gld on gld.G_Name COLLATE DATABASE_DEFAULT  = gm.G_Name COLLATE DATABASE_DEFAULT and gld.G_Mark is not null
 left join AccountCharacter ac on ac.Id COLLATE DATABASE_DEFAULT = ch.AccountID COLLATE DATABASE_DEFAULT
 left join MEMB_STAT ms on ms.memb___id COLLATE DATABASE_DEFAULT = ch.AccountID COLLATE DATABASE_DEFAULT,
 MEMB_INFO mi
WHERE
 mi.memb___id COLLATE DATABASE_DEFAULT = ch.AccountID COLLATE DATABASE_DEFAULT
 AND ch.CtlCode not in (1,17) $classes
 order by ch.{$this->unicCfg["rescolumn"]} desc,ch.{$this->unicCfg["grescolumn"]} desc, ch.cLevel desc, ch.Name desc");

        $list = array();

        while($r = $q->FetchRow())
        {
            $list[] = $r;
        }

        return $list;
    }

}