<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 31.10.2015
 *
 **/
class m_webshop extends MuonlineUser
{
    private $wcfg;

    function addItemlist($ilength)
    {
        $inventory = self::getWH($_SESSION["mwcuser"],$ilength);

        $length = strlen($inventory)/$ilength;
        $coumt = 0;

        $path = "build/muonline/_dat/items/harmony.php";
        if(file_exists($path))
            require $path;
        else
            $harm = array();

        $sel = "<SELECT name='itm' id='itm' class='selectbox'>";
        for($i=0;$i<$length;$i++)
        {
            $target = substr($inventory,$i*$ilength,$ilength);
            if ($target != "FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF" && $target != "FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF")
            {
                $id = rItem::dehex($target,0,2);
                $group = rItem::dehex($target,18,1);
                if(self::isDenied($id,$group))//если вещь нельзя выкладывать
                    continue;

                $ret = itemListShow($target,$harm);
                $sel.="<option class='{$ret[1]}' value='$i'>{$ret[0]}</option>";
                $coumt ++;
            }
        }
        $sel.="</SELECT>";

        return $sel;
    }

    /**
     * узнать, не запрещена ли вещь
     * @param int $id
     * @param int $group
     * @return bool
     */
    function isDenied($id,$group)
    {
        if(!empty($this->wcfg[$group]))
        {
            if(in_array($id,$this->wcfg[$group]))
                return true;
        }

        return false;
    }

    /**
     * узнать запрещенные к продаже вещи
     * @param array $array
     */
    function setFilters($array)
    {
        for ($i=0;$i<16;$i++)
        {
            $this->wcfg[$i] = explode(",",$array["denied".$i]);
        }
    }

    /**
     * добавление вещей в веб
     * @param int $id
     * @param int $price цена
     * @param int $leng размер хекса вещи
     * @throws ADODB_Exception
     * @throws Exception
     */
    function adding($id,$price,$leng)
    {
        if($price<=0)
            return ;

        $path = "build/muonline/_dat/items/harmony.php";
        if(file_exists($path))
            require $path;
        else
            $harm = array();

        $needItm = $this->db->query("EXEC MWC_WHITEM{$leng} '{$_SESSION["mwcuser"]}',$id,{$leng}")->FetchRow();
        $wegot = rItem::Read($needItm["item"],$harm);
        $emptyItem = str_pad("", $leng,"F",STR_PAD_BOTH);

        if(self::isDenied($wegot["id"],$wegot["group"]) || $wegot["hex"] == $emptyItem) //если пустое место или запрещенная вещь, то прекращаем работу
            return;

        $puting = $this->db->query("exec MWC_REPLACEWH$leng '{$_SESSION["mwcuser"]}','{$wegot["hex"]}','$emptyItem'")->FetchRow();//выкладываем саму вещь

        if($puting["statez"] == 0)//если все хорошо
        {
            if(!isset($wegot["serial2"]))
                $wegot["serial2"]="FFFFFFFF";

            if(!empty($wegot["intopt"]))
                $wegot["intopt"] = 1;
            else
                $wegot["intopt"] = 0;

            if(!empty($wegot["excnum"]))
                $wegot["excnum"] = 1;
            else
                $wegot["excnum"] = 0;

            if(!empty($wegot["ancnum"]))
                $wegot["ancnum"] = 1;
            else
                $wegot["ancnum"] = 0;

            if(!empty($wegot["isSkill"]))
                $wegot["isSkill"] = 1;
            else
                $wegot["isSkill"] = 0;

            if(!empty($wegot["harmonyOpt"]))
                $wegot["harmonyOpt"] = 1;
            else
                $wegot["harmonyOpt"] = 0;

            if(!empty($wegot["sockHex"]) && ($wegot["sockHex"]!="0000000000" && $wegot["sockHex"]!="FFFFFFFFFF"))
                $wegot["sockHex"] = 1;
            else
                $wegot["sockHex"] = 0;

            if(empty($wegot["equipmentn"]))
                $wegot["equipmentn"] = 'All';


            $this->db->query("INSERT INTO mwc_web_shop ([col_itemID],[col_idemGroup],[col_Name],[col_hex],[col_serial],[col_serial2],[col_level],[col_isOpt],[col_isExc],[col_isAnc],[col_isSock],[col_isSkill],[col_isPVP],[col_isHarmony],[col_eq],[col_prise],[col_priseType],[col_isMy],[col_user]) VALUES ({$wegot["id"]},{$wegot["group"]},'{$wegot["name"]}','{$wegot["hex"]}','{$wegot["serial1"]}','{$wegot["serial2"]}',{$wegot["level"]},{$wegot["intopt"]},{$wegot["excnum"]},{$wegot["ancnum"]},{$wegot["sockHex"]},{$wegot["isSkill"]},{$wegot["ispvp"]},{$wegot["harmonyOpt"]},'{$wegot["equipmentn"]}',{$price},1,0,'{$_SESSION["mwcuser"]}')");
            $this->db->SQLog("User {$_SESSION["mwcuser"]} try to sell {$wegot["hex"]} for $price","webshop",12,false);
            echo $this->db->lastId("mwc_web_shop");
        }
        else
            echo "0";

    }
}