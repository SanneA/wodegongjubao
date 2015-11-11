<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 30.10.2015
 *
 **/
class m_topguild extends MuonlineUser
{
    /**
     * список гильдий
     * @param int $top
     * @return mixed
     * @throws ADODB_Exception
     * @throws Exception
     */
    public function fetGuildList($top)
    {
        return $this->db->query("SELECT top $top
 gd.G_Name,
 CONVERT(varchar(66),gd.G_Mark,2) as Glogo,
 (select COUNT(*) from GuildMember where G_Name = gd.G_Name) as members,
 gd.G_Score,
 CONVERT(varchar(max),(SELECT G_Name + ',,' FROM guild  WHERE (G_Union = gd.G_Union or  G_Union = gd.number or number=gd.G_Union) and G_Union >0 and G_Name <> gd.G_Name FOR XML PATH('')),2) as alianses,
 (SELECT COUNT(*) FROM MuCastle_DATA WHERE OWNER_GUILD = gd.G_Name AND [MAP_SVR_GROUP] = 0) as isLords
FROM
 Guild gd")->GetRows();
    }

}