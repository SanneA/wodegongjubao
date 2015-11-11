<?php

class m_acontrol extends ausermodel
{

    /**
     * список админов
     * @return array
     */
    public function getAdminsList()
    {
        $q = $this->db->query("SELECT ma.id,ma.name,mg.g_name  FROM
mwc_admin ma,
mwc_group mg
WHERE
mg.id = ma.access");

        $admins = array();

        $path = "build".DIRECTORY_SEPARATOR.$_SESSION["mwcabuild"].DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$_SESSION["mwclang"].DIRECTORY_SEPARATOR."group.php";
        if(file_exists($path))
        {
            require $path;
        }
        else
            $lang = array();

        while ($r = $q->FetchRow())
        {
            if(!empty($lang[$r["g_name"]]))
                $r["g_name"] = $lang[$r["g_name"]];
            $admins[$r["id"]] = array($r["name"],$r["g_name"]);
        }

        return $admins;
    }

    /**
     * информация о пользователе
     * @param int $id
     * @return mixed
     */
    public function getInfo($id)
    {
        return $this->db->query("SELECT ma.id,ma.name,ma.access,ma.umail ,ma.nick  FROM
mwc_admin ma
WHERE ma.id=$id")->FetchRow();
    }

    /**
     * возвращает список групп
     * @return array
     */
    public function getCurrentList()
    {
        $admins = array();

        $path = "build".DIRECTORY_SEPARATOR.$_SESSION["mwcabuild"].DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$_SESSION["mwclang"].DIRECTORY_SEPARATOR."group.php";
        if(file_exists($path))
        {
            require $path;
        }
        else
            $lang = array();

        $ar = self::getGroups();

        $ai = new ArrayIterator($ar);

        foreach($ai as $i_=>$val)
        {
            if(!empty($lang[$val]))
                $val = $lang[$val];
            $admins[$i_] = $val;
        }
        return $admins;
    }

    /**
     * @param $id
     * @param string $name
     * @param string$nick
     * @param int $access
     * @param string $pwd
     * @param string $mail
     */
    public function editAdmin($id,$name,$nick,$access,$pwd,$mail)
    {
        if(is_null($pwd))
            $this->db->query("UPDATE mwc_admin SET name='$name', nick='$nick',access=$access,umail = $mail WHERE id = $id");
        else
            $this->db->query("UPDATE mwc_admin SET name='$name', nick='$nick',access=$access,pwd=$pwd,umail = $mail WHERE id = $id");
    }

    /**
     * @param string $name
     * @param string $nick
     * @param int $access
     * @param string $pwd
     * @param string $mail
     */
    public function addAdmin ($name,$nick,$access,$pwd,$mail)
    {
        $this->db->query("INSERT INTO mwc_admin (name, pwd, nick, access, sacc, umail) VALUES ('$name',$pwd,'$nick',$access,'$nick',$mail)");
    }

    /**
     * удалить админа по ид
     * @param $id
     */
    public function delAdmin($id)
    {
        $this->db->query("DELETE FROM mwc_admin WHERE id = $id");
    }
}
