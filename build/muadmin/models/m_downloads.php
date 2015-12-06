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
     * @param array $params
     * @throws ADODB_Exception
     */
    public function add($params)
    {
        if(empty($params["title"]))
            $params["title"] = "NULL";
        else
            $params["title"] = "N'{$params["title"]}'";

        if(empty($params["desc"]))
            $params["desc"] = "NULL";
        else
            $params["desc"] = "N'{$params["desc"]}'";

        $this->db->query("INSERT INTO mwce_settings.dbo.mwc_downloads (col_title,col_desc,col_address,col_pik,tbuild) VALUES ({$params["title"]},{$params["desc"]},N'{$params["address"]}',0,'{$_SESSION["mwccfgread"]}')");
    }

    /**
     * @param array $params
     * @throws ADODB_Exception
     */
    public function edit($params)
    {
        if(empty($params["title"]))
            $params["title"] = "NULL";
        else
            $params["title"] = "N'{$params["title"]}'";

        if(empty($params["desc"]))
            $params["desc"] = "NULL";
        else
            $params["desc"] = "N'{$params["desc"]}'";

        $this->db->query("UPDATE mwce_settings.dbo.mwc_downloads SET col_title = {$params["title"]},col_desc={$params["desc"]},col_address = N'{$params["address"]}' WHERE col_id =".$params["id"]);

    }

    /**
     * @param int $id
     * @return array
     * @throws ADODB_Exception
     */
    public function info($id)
    {
        return $this->db->query("SELECT * FROM mwce_settings.dbo.mwc_downloads WHERE col_id = $id")->FetchRow();
    }

    /**
     * @param int $id
     * @throws ADODB_Exception
     */
    public function delete($id)
    {
        $this->db->query("DELETE FROM mwce_settings.dbo.mwc_downloads WHERE col_id = $id");
    }
}