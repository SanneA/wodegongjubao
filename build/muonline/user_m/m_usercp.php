<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 18.10.2015
 *
 **/
class m_usercp extends MuonlineUser
{
    public function applyChanges($params)
    {
        $maincfg = Configs::readCfg("main","muonline");//читаем конфиг на предмет мд5 и не только

        //region мд5 да,не/иди нафиг
        if((int)$maincfg["usemd5"]>0)
            $r_password = "[dbo].[fn_md5]('{$params["memb_pwd"]}','{$_SESSION["mwcuser"]}')";
        else
            $r_password = "'{$params["memb_pwd"]}'";
        //endregion


        if(!empty($params["memb_newpwd"]))
        {
            if((int)$maincfg["usemd5"]>0)
                $new_password = ",memb__pwd = [dbo].[fn_md5]('{$params["memb_newpwd"]}','{$_SESSION["mwcuser"]}')";
            else
                $new_password = ",memb__pwd = '{$params["memb_newpwd"]}'";
        }
        else
            $new_password = "";

        $this->db->query("UPDATE MEMB_INFO SET memb_name='{$params["memb_name"]}' $new_password WHERE memb___id='{$_SESSION["mwcuser"]}' AND memb__pwd = $r_password");
        $this->db->SQLog("acount {$_SESSION["mwcuser"]} change self profile","usercp",11);

        if(!empty($params["memb_newpwd"]))
        {
            $_SESSION["mwcpwd"] = $params["memb_newpwd"];
        }
    }

}