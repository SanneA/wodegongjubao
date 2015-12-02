<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 03.10.2015
 *
 **/
class m_servertop extends MuonlineUser
{
    /**
     * получить список $top сильнейших игоков
     * @param int $top
     * @param string $hide - список непоказываемых игроков
     * @return array
     * @throws ADODB_Exception
     * @throws Exception
     */
    public function getPlayers($top,$hide)
    {
        if(!empty($hide))
        {
            $hidersar = explode(",",$hide);
            if (count($hidersar)>1)
            {
                $s = "";
                foreach ($hidersar as $hd)
                {
                    $s.="'{$hd}',";
                }

                $hiders = "AND ch.Name NOT IN(".substr($s,0,-1).")";
            }
            else
            {
                $hiders = "";
            }
        }

        $q = $this->db->query("SELECT TOP $top
ch.Name,
ch.Class,
ch.cLevel,
ch.{$this->unicCfg["rescolumn"]},
ch.PkCount,
ch.PkLevel,
ms.ConnectStat,
ch.{$this->unicCfg["grescolumn"]},
CONVERT(varchar(max),ms.ConnectTM,120) as ConnectTM,
CONVERT(varchar(max), ms.DisConnectTM ,120) as DisConnectTM
FROM  [Character] ch
left join [GuildMember] gm ON gm.Name COLLATE DATABASE_DEFAULT = ch.Name COLLATE DATABASE_DEFAULT
left join MEMB_STAT ms on ms.memb___id COLLATE DATABASE_DEFAULT = ch.AccountID COLLATE DATABASE_DEFAULT
WHERE  ch.CtlCode != '1' and ch.CtlCode != '17' {$hiders}
ORDER BY  ch.{$this->unicCfg["rescolumn"]} DESC");
        $player = array();

        while ($r = $q->FetchRow())
        {
            $player[$r["Name"]] = $r;
        }
        return $player;
    }

    /**
     * Возвращает топ $top сильнейших гильдий
     * @param int $top
     * @param string $hide - какие гилдии скрывать
     * @return array
     * @throws ADODB_Exception
     * @throws Exception
     */
    public function getGuilds($top,$hide)
    {
        if(!empty($hide))
        {
            $hidersar = explode(",",$hide);
            if (count($hidersar)>1)
            {
                $s = "";
                foreach ($hidersar as $hd)
                {
                    $s.="'{$hd}',";
                }

                $hiders = "WHERE gd.G_Name NOT IN(".substr($s,0,-1).")";
            }
            else
            {
                $hiders = "";
            }
        }

        $g = $this->db->query("SELECT TOP $top
 gd.G_Name,
 gd.G_Count,
 CONVERT(varchar(66),gd.G_Mark,2) as Glogo,
 (select COUNT(*) from GuildMember where G_Name = gd.G_Name) as members,
 gd.G_Score
FROM
 Guild gd
 $hiders
 ORDER BY gd.G_Score DESC");

        $guilds = array();

        while ($r = $g->FetchRow())
        {

            $guilds[$r["G_Name"]] = $r;
        }

        return $guilds;
    }
}