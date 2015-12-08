<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 07.11.2015
 *
 **/
class m_webmarket extends MuonlineUser
{

    protected $items = array();
    /**
     * @param array $params
     * @return mixed
     * @throws ADODB_Exception
     */
    public function getlist($params)
    {
        $filter = '';
        if(!empty($params["name"]))
        {
            if(!empty($filter))
                $filter.=" AND ";

            $filter.=" col_Name like '%{$params["name"]}%'";
        }

        if($params["lvlf"]>-1 && $params["lvlt"]>-1)
        {
            if(!empty($filter))
                $filter.=" AND ";

            $filter.=" col_level BETWEEN {$params["lvlf"]} AND {$params["lvlt"]}";
        }

        if( !empty($params["ismyitem"]))
        {
            $filter.=" AND col_user ='{$_SESSION["mwcuser"]}'";
        }

        if(!empty($params["isExc"]))
            $filter.=" AND col_isExc !='0'";

        if(!empty($params["isAnc"]))
            $filter.=" AND col_isAnc !='0'";

        if(!empty($params["isSkill"]))
            $filter.=" AND col_isSkill !='0'";

        if(!empty($params["isOpt"]))
            $filter.=" AND col_isOpt !='0'";

        if(!empty($params["isPVP"]))
            $filter.=" AND col_isPVP !='0'";

        if(!empty($params["isHarmony"]))
            $filter.=" AND col_isHarmony !='0'";

        if(!empty($filter))
            $filter = " WHERE ".$filter;

        return $this->db->query("WITH CTEwResults AS(
            SELECT *,
            ROW_NUMBER() OVER (ORDER BY col_shopID DESC) AS RowNum
             FROM mwc_web_shop $filter )
            SELECT * FROM CTEwResults WHERE RowNum BETWEEN {$params["min"]} AND {$params["max"]} ORDER BY col_isMy DESC ;")->GetRows();

    }

    /**
     * количество записей, удовледтворяющих запросу
     * @param array $params
     * @return mixed
     * @throws ADODB_Exception
     */
    public function getCount($params)
    {
        $filter = '';
        if(!empty($params["name"]))
        {
            if(!empty($filter))
                $filter.=" AND ";

            $filter.=" col_Name like '%{$params["name"]}%'";
        }

        if($params["lvlf"]>-1 && $params["lvlt"]>-1)
        {
            if(!empty($filter))
                $filter.=" AND ";

            $filter.=" col_level BETWEEN {$params["lvlf"]} AND {$params["lvlt"]}";
        }

        if( !empty($params["ismyitem"]))
        {
            $filter.=" AND col_user ='{$_SESSION["mwcuser"]}'";
        }

        if(!empty($params["isExc"]))
            $filter.=" AND col_isExc !='0'";

        if(!empty($params["isAnc"]))
            $filter.=" AND col_isAnc !='0'";

        if(!empty($params["isSkill"]))
            $filter.=" AND col_isSkill !='0'";

        if(!empty($params["isOpt"]))
            $filter.=" AND col_isOpt !='0'";

        if(!empty($params["isPVP"]))
            $filter.=" AND col_isPVP !='0'";

        if(!empty($params["isHarmony"]))
            $filter.=" AND col_isHarmony !='0'";

        if(!empty($filter))
            $filter = "WHERE $filter";

        $q = $this->db->query("SELECT COUNT(*) as cnt FROM mwc_web_shop $filter")->FetchRow();
        return $q["cnt"];
    }

    /**
     * @param int $id
     * @return mixed
     * @throws ADODB_Exception
     */
    public function getInfo($id)
    {
        if(!empty($this->items[$id]))
            return $this->items[$id];
        $r = $this->db->query("SELECT * FROM mwc_web_shop WHERE col_shopID = $id")->FetchRow();
        $this->items[$id] = $r;

        return $r;
    }

    /**
     * снять с продажи вещь
     * @param int $place
     * @param int $ilen
     * @return bool
     * @throws ADODB_Exception
     */
    public function dropItm($place,$ilen,$inom,$hex)
    {
        $res = $this->db->query("EXEC MWC_REPLACEWHNUM$ilen '{$_SESSION["mwcuser"]}' , $place ,'$hex'")->FetchRow();
        if($res["statez"] == 0)
        {
            $this->db->query("DELETE FROM mwc_web_shop WHERE col_shopID = $inom");
            $this->db->SQLog("User {$_SESSION["mwcuser"]} drop item #$inom to wareghouse","webmarket",12);
            return true;
        }

        return false;
    }

    /**
     * @param int $place
     * @param int $ilen
     * @param int $inom
     * @param string $hex
     * @return bool
     * @throws ADODB_Exception
     */
    public function buyItm($place,$ilen,$inom,$hex)
    {
        $res = $this->db->query("EXEC MWC_REPLACEWHNUM$ilen '{$_SESSION["mwcuser"]}' , $place ,'$hex'")->FetchRow();
        if($res["statez"] == 0)
        {
            $itm = self::getInfo($inom);
            $this->db->query("UPDATE MEMB_INFO SET mwc_bankZ = mwc_bankZ - {$itm["col_prise"]} WHERE memb___id = '{$_SESSION["mwcuser"]}'; UPDATE MEMB_INFO SET mwc_bankZ = mwc_bankZ + {$itm["col_prise"]} WHERE memb___id = '{$itm["col_user"]}';");
            $this->db->query("DELETE FROM mwc_web_shop WHERE col_shopID = $inom");
            $this->db->SQLog("User {$_SESSION["mwcuser"]} buy item #$inom hex:{$itm["col_hex"]} price: ".$itm["col_prise"],"webmarket",12);
            return true;
        }
        return false;
    }
}