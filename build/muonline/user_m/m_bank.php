<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 11.10.2015
 *
 **/
class m_bank extends MuonlineUser
{
    /**
     * положить денги из веб банка в сундук
     * @param int $zen
     * @throws ADODB_Exception
     * @throws Exception
     */
    public function putInWh($zen)
    {
        $this->db->query("UPDATE memb_info SET mwc_bankZ = mwc_bankZ-$zen WHERE memb___id='{$this->user["login"]}';
 UPDATE warehouse SET Money = Money + $zen WHERE AccountID = '{$this->user["login"]}'");
        $this->db->SQLog("{$this->user["login"]} put $zen from web bank to warehouse","m_bank",9);
    }

    /**
     * положить в веб банк
     * @param int $zen
     * @throws ADODB_Exception
     * @throws Exception
     */
    public function putInWeb($zen)
    {
        $this->db->query("UPDATE memb_info SET mwc_bankZ = mwc_bankZ+$zen WHERE memb___id='{$this->user["login"]}';
 UPDATE warehouse SET Money = Money - $zen WHERE AccountID = '{$this->user["login"]}'");
        $this->db->SQLog("{$this->user["login"]} put $zen from warehouse to web bank","m_bank",9);
    }

}