<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 03.10.2015
 *
 **/
class m_login extends MuonlineUser
{
    /**
     * список персонажей
     * @return array
     * @throws ADODB_Exception
     * @throws Exception
     */
    public function getCharacters()
    {
        $q = $this->db->query("SELECT Name FROM Character WHERE AccountID='{$this->user["login"]}'");

        $chars = array();

        while ($r = $q->FetchRow())
        {
            $chars[$r["Name"]] = $r["Name"];
        }
        return $chars;
    }

    /**
     * Узнать финансы
     * @return array
     * @throws ADODB_Exception
     * @throws Exception
     */
    public function getMoney()
    {
        $info  = $this->db->query("SELECT mwc_bankZ,mwc_credits FROM MEMB_INFO WHERE memb___id='{$this->user["login"]}'")->FetchRow();
        return $info;
    }
}