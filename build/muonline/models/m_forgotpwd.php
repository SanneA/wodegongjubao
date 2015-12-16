<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 16.12.2015
 *
 **/

class m_forgotpwd extends MuonlineUser
{
    /**
     * @param string $mail
     * @param string $akk
     * @return int 0/1
     * @throws Exception
     */
    public function valid($mail,$akk)
    {
        $q = $this->db->query("SELECT count(*) as cnt FROM MEMB_INFO WHERE mail_addr='$mail' and memb___id='$akk'")->FetchRow();
        return $q["cnt"];
    }

    /**
     * @param string $akk
     * @return string|NULL
     * @throws Exception
     */
    public function viewPwd($akk)
    {
        $q = $this->db->query("SELECT memb__pwd FROM MEMB_INFO WHERE memb___id='$akk'")->FetchRow();
        return $q["memb__pwd"];
    }

    /**
     * генератор паролей
     * @param $number
     * @return string
     * http://www.softtime.ru
     */
    private function generate_password($number)
    {
        $arr = array(
            'a','b','c','d','e','f',
            'g','h','i','j','k','l',
            'm','n','o','p','r','s',
            't','u','v','x','y','z',
            'A','B','C','D','E','F',
            'G','H','I','J','K','L',
            'M','N','O','P','R','S',
            'T','U','V','X','Y','Z',
            '1','2','3','4','5','6',
            '7','8','9','0'
        );
        // Генерируем пароль
        $pass = "";
        for($i = 0; $i < $number; $i++)
        {
            // Вычисляем случайный индекс массива
            $index = rand(0, count($arr) - 1);
            $pass .= $arr[$index];
        }
        return $pass;
    }

    /**
     * @param string $akk
     * @param int $useMd5
     * @return string
     * @throws Exception
     */
    public function getNewPwd($akk,$useMd5=0)
    {
        $password = self::generate_password(8);
        if($useMd5 != 1)
        {
            $this->db->query("UPDATE MEMB_INFO SET memb__pwd = '{$password}' WHERE memb___id='$akk'");
        }
        else
            $this->db->query("UPDATE MEMB_INFO SET memb__pwd = [dbo].[fn_md5]('{$password}','{$akk}') WHERE memb___id='$akk'");
        return $password;
    }

}