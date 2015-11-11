<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 27.09.2015
 *
 **/
class m_register extends MuonlineUser
{
    /**
     * проверка валидности логина
     * @param $login
     * @return bool
     * @throws ADODB_Exception
     * @throws Exception
     */
    public function checkLogin($login)
    {
        $r = $this->db->query("SELECT count(*) as cnt FROM memb_info WHERE memb___id = '$login'")->FetchRow();
        if($r["cnt"] == 0)
            return true;

        return false;
    }

    public function reg($login,$pwd,$mail)
    {
        if((int)$this->cfg["usemd5"]>0)
            $r_password = "[dbo].[fn_md5]('{$pwd}','{$login}')";
        else
            $r_password = "'".$pwd."'";

        $this->db->query("INSERT INTO memb_info (memb___id,memb_name,memb__pwd,mail_addr,sno__numb,bloc_code,ctl1_code) VALUES('$login','$login', $r_password,'$mail',1,0,1)");
        $this->db->SQLog("registered new account: $login","register",7);
    }
}