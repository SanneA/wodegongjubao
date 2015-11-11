<?php

/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 27.08.2015
 *
 **/

/**
 * Class usermodel
 * данные по пользователю
 */
class ausermodel extends Model
{
    private $user = array();
    private $islogin;

    public function __construct($db)
    {
        parent::__construct($db); //инициализируем сначала перента

        if(!empty($_SESSION["mwcauser"]) && !empty($_SESSION["mwcapwd"]) && !empty($_SESSION["mwcapoints"]))
        {
            $this->user["login"] = $_SESSION["mwcauser"];
            $this->user["pwd"] = $_SESSION["mwcapwd"];
            $this->user["group"] = $_SESSION["mwcapoints"];

            $this->islogin = 1;
        }
        else
            $this->islogin = 0;
    }

    /**
     * получение данных о текущем пользователе
     *
     * @return array
     */
    public function knowAbout()
    {
        if($this->islogin == 0) //если не залогинен - нечего тут делать
            return array();

        $info = $this->publicInfo();
        if(count($info)<3)
        {
            $result = $this->db->query("SELECT
ma.id,
ma.lastdate,
ma.nick,
ma.umail,
mg.g_name,
ma.access
FROM
mwc_admin ma,
mwc_group mg
WHERE
ma.name = '{$this->user["login"]}'
and mg.id = ma.access")->FetchRow();


            $this->user["id"] = $result["id"];
            $this->user["lastdate"] = $result["lastdate"];
            $this->user["nick"] = $result["nick"];
            $this->user["umail"] = $result["umail"];
            $this->user["group"] = $result["access"];
            $_SESSION["mwcauser"] = $this->user["login"];
            $_SESSION["mwcapwd"] = $this->user["pwd"];
            $_SESSION["mwcaid"] = $result["id"];

            $_SESSION["mwcapoints"] = $result["access"];

            $path = "build".DIRECTORY_SEPARATOR.$_SESSION["mwcabuild"].DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$_SESSION["mwclang"].DIRECTORY_SEPARATOR;
            if (file_exists($path."group.php"))
            {
                require_once($path."group.php");
                $this->user["groupName"] = $lang[$result["g_name"]];
            }
            else
                $this->user["groupName"] = $result["g_name"];

            $_SESSION["mwcaname"]=$result["nick"];
            $_SESSION["mwcagroupname"] = $this->user["groupName"];

            return $this->publicInfo();
        }
        return $info;
    }

    /**
     * функция -фильтр, убирает из данных те вещи, которые никому знать не надо
     *
     * @return array - данные по пользователю
     */
    private function publicInfo()
    {
        $return = $this->user;
        unset($return["pwd"]);
        return $return;
    }


    /**
     * разлогин
     */
    public function logOut()
    {

        unset($_SESSION["mwcauser"],$_SESSION["mwcapwd"],$_SESSION["mwcuid"],$_SESSION["mwctp"],$_SESSION["mwcapoints"]);
        $this->islogin = 0;
    }

    /**
     * аунтификация, если все прошло хорошо, то вернет true иначе - false
     *
     * @param string $login
     * @param string $pwd
     * @return bool
     */
    public function auth($login,$pwd)
    {
        $result = $this->db->query("SELECT name FROM mwc_admin WHERE name='{$login}' and pwd='".md5($pwd)."'");
        if($result->RecordCount()>0)
        {
            $this->islogin = 1;
            $this->user["login"] = $login;
            $this->user["pwd"] = $pwd;
            $this->knowAbout();
            return true;
        }
        return false;
    }

    public function getDBins()
    {
        return $this->db;
    }

    public function isLogged()
    {
        return $this->islogin;
    }

    /**
     * список групп
     * @return array
     */
    public function getGroups()
    {
        $q = $this->db->query("SELECT id,g_name FROM mwc_group");
        $ret = array();
        while($r = $q->FetchRow())
        {
            $ret[$r["id"]] = $r["g_name"];
        }

        return $ret;
    }

}