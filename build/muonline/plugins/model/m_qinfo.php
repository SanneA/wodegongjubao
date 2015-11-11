<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 03.10.2015
 *
 **/
class m_qinfo extends MuonlineUser
{
    /**
     * узнать кол-во онлайна
     * @return int|null
     * @throws ADODB_Exception
     * @throws Exception
     */
    public function knowOnline()
    {
        $q = $this->db->query("SELECT count(*) as cnt FROM MEMB_STAT WHERE ConnectStat !=0")->FetchRow();
        return $q["cnt"];
    }
}