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

    /**
     * регистрация. простая
     * @param string $login
     * @param string $pwd
     * @param string $mail
     * @throws ADODB_Exception
     */
    public function reg($login,$pwd,$mail)
    {
        if((int)$this->cfg["usemd5"]>0)
            $r_password = "[dbo].[fn_md5]('{$pwd}','{$login}')";
        else
            $r_password = "'".$pwd."'";

        $this->db->query("INSERT INTO memb_info (memb___id,memb_name,memb__pwd,mail_addr,sno__numb,bloc_code,ctl1_code) VALUES('$login','$login', $r_password,'$mail',1,0,1)");
        $this->db->SQLog("registered new account: $login","register",7);
    }

    /**
     * регистрация, с использованием активации на почту
     * @param string $login
     * @param string $pwd
     * @param string $mail
     * @param int $group
     * @return int ид пользователя
     * @throws ADODB_Exception
     */
    public function regM($login,$pwd,$mail,$group)
    {
        if((int)$this->cfg["usemd5"]>0)
            $r_password = "[dbo].[fn_md5]('{$pwd}','{$login}')";
        else
            $r_password = "'".$pwd."'";

        $this->db->query("INSERT INTO memb_info (memb___id,memb_name,memb__pwd,mail_addr,sno__numb,bloc_code,ctl1_code,MWCpoints) VALUES('$login','$login', $r_password,'$mail',1,0,1,$group)");
        $id = $this->db->lastId("memb_info");

        $this->db->SQLog("registered new account: $login","register",7);
        return $id;
    }

    /**
     * @param int $id
     * @param string $hash
     * @return bool
     * @throws ADODB_Exception
     */
    public function activate($id,$hash)
    {
        $info = $this->db->query("SELECT mail_addr FROM memb_info WHERE memb_guid=$id")->FetchRow();
        if(md5($id."-=-".$info["mail_addr"]) == $hash)
        {
            $this->db->query("UPDATE memb_info SET MWCpoints = 5 WHERE memb_guid=$id AND MWCpoints !=5");
            return true;
        }
        return false;
    }
}