<?php

/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 04.12.2015
 *
 **/
class m_downloads extends Model
{
    /**
     * @return array
     * @throws ADODB_Exception
     */
    public function getList()
    {
        return $this->db->query("SELECT col_id,col_pik,CAST(col_desc as TEXT) as col_desc,CAST(col_address as TEXT) as col_address,CAST(col_title as TEXT) as col_title,tbuild FROM mwce_settings.dbo.mwc_downloads")->GetRows();
    }


    /**
     * @param int $id
     * @return array
     * @throws ADODB_Exception
     */
    public function info($id)
    {
        return $this->db->query("SELECT col_id,col_pik,CAST(col_desc as TEXT) as col_desc,CAST(col_address as TEXT) as col_address,CAST(col_title as TEXT) as col_title,tbuild FROM mwce_settings.dbo.mwc_downloads WHERE col_id = $id")->FetchRow();
    }
}