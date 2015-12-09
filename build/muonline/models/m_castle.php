<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 09.12.2015
 *
 **/
class m_castle extends Model
{
    public function getInfo()
    {
        return $this->db->query("SELECT TOP 1 SIEGE_START_DATE,OWNER_GUILD FROM MuCastle_DATA")->FetchRow();
    }

}