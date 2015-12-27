<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 26.09.2015
 * класс пользователя муолнайл (порт с 1.5.3)
 **/
class MuonlineUser extends Model
{
    protected $isLoged = false; //bool  залогиненный ли пользователь
    protected $user;
    protected $cfg;
    protected $characters;
    protected $character;
    protected $unicCfg;

    public function __construct()
    {
        parent::__construct();
        if(!empty($_SESSION["mwcuser"]) && !empty($_SESSION["mwcpwd"]))
        {
            $this->user["login"] = $_SESSION["mwcuser"];
            $this->user["pwd"] = $_SESSION["mwcpwd"];
            $this->isLoged = true;
        }
        else
            $this->isLoged = false;

        $this->unicCfg = Configs::readCfg("unic",tbuild);
    }

    /**
     * аунтификация
     * @param string $login
     * @param string $password
     * @return bool
     * @throws ADODB_Exception
     * @throws Exception
     */
    public function auth($login,$password)
    {
        if($this->isLoged)
            return true;

        $maincfg = Configs::readCfg("main",tbuild);//читаем конфиг на предмет мд5 и не только
        $this->cfg = $maincfg;

        //region мд5 да,не/иди нафиг

        if((int)$maincfg["usemd5"]>0)
            $r_password = "[dbo].[fn_md5]('{$password}','{$login}')";
        else
            $r_password = "'$password'";
        //endregion

        $login = substr($login,0,10);
        $result = $this->db->query("SELECT bloc_code FROM MEMB_INFO WHERE memb___id='{$login}' AND memb__pwd = $r_password")->FetchRow();
        $about = self::aboutUser($login);//узнаем все о пользователе в любом случае

        if(!empty($result)) // если аунтификация удалась
        {
            if((int)$result["bloc_code"]>0 && $about["mwc_timeban"] != "0" && strtotime($about["mwc_timeban"])>time()) //если юзверь забаненный и время бана не истекло и бан не был навсегда
                return false;

            $this->db->query("UPDATE MEMB_INFO SET mwc_tryes = 0, mwc_timeban = NULL WHERE memb___id='{$login}'");//раз есть логин удачный, снимаем усе и сразу

            $this->user["login"] = $_SESSION["mwcuser"] = $login;
            $this->user["pwd"]  = $_SESSION["mwcpwd"] = $password;

            $_SESSION["mwcpoints"] = $about["MWCpoints"];//делаем пользователя пользователем

            $this->isLoged = true;
            return true;
        }
        elseif (!empty($about) && (int)$about["bloc_code"] == 0) //если пользователь есть, но пароль был явно неверный и нету бана ы...
        {
            if($about["mwc_tryes"] >= $maincfg["tryCount"] ) //если кол-во попыток слишком большое, то временно даем банан юзверю ..пускай кушает
            {
                $this->db->query("UPDATE MEMB_INFO SET mwc_tryes =0,mwc_timeban =DATEADD(HOUR,{$maincfg["banMin"]},GETDATE()) WHERE memb___id='{$login}'");
                $this->db->SQLog("Account $login baned for {$maincfg["banMin"]} min for wrong password",'muonlineUser',6);//сообщаем одминчегам ^_^
            }
            else
                $this->db->query("UPDATE MEMB_INFO SET mwc_tryes +=1 WHERE memb___id='{$login}'");
        }
        return false;
    }

    /**
     * узнать о пользователе все, что вписано в запрос (если null, то о текущем)
     * @param string|null $login
     * @throws ADODB_Exception
     * @throws Exception
     * @return array
     */
    public function aboutUser($login = null)
    {
        if(is_null($login))
            $login = $this->user["login"];

        $info = $this->db->query("SELECT
 mi.memb_name,
 mi.mail_addr,
 mi.mwc_bankZ,
 mi.MWCpoints,
 mi.bloc_code,
 mi.mwc_timeban,
 mi.mwc_tryes,
 mi.mwc_credits,
 wh.Money
FROM
 MEMB_INFO mi
 LEFT JOIN warehouse wh ON wh.AccountID COLLATE DATABASE_DEFAULT = mi.memb___id COLLATE DATABASE_DEFAULT
WHERE mi.memb___id='{$login}'")->FetchRow();

        if($login == $this->user["login"]) //если текущий пользователь, то возвращаем полную инфу
        {
            $this->user += $info;
            return $this->user;
        }

        return $info; //если нет, то только ту, что в запросе
    }

    /**
     * проверка на онлайн аккаунта
     * @param null|string $login
     * @return int
     * @throws ADODB_Exception
     * @throws Exception
     */
    public function isOnline($login=null)
    {
        if(!is_null($login))
            $user = $login;
        else
            $user = $this->user["login"];

        $result = $this->db->query("SELECT ConnectStat FROM MEMB_STAT WHERE memb___id='$user'")->FetchRow();
        return $result["ConnectStat"];
    }

    /**
     * узнать список персонажей на акке
     * @param null|string $login
     * @return array
     * @throws ADODB_Exception
     * @throws Exception
     */
    public function getCharlist($login = null)
    {
        if(!is_null($login))
        {
            $user = $login;
            if(!empty($this->characters))
                return $this->characters;
        }
        else
            $user = $this->user["login"];

        $q = $this->db->query("SELECT Name FROM Character WHERE AccountID='{$user}'");
        $chars = array();

        while($r = $q->FetchRow())
        {
            $chars[$r["Name"]] = $r["Name"];
        }
        $this->characters = $chars;
        return $chars;
    }

    /**
     * @param string $name
     * @param string $login
     * @return mixed
     * @throws ADODB_Exception
     * @throws Exception
     */
    public function chracterInfo($name,$login)
    {
        if(!empty($this->character[$name.$login]))
            return $this->character[$name.$login];


        $info = $this->db->query("Select Leadership,Energy,Vitality,Dexterity,Strength,Class,LevelUpPoint,cLevel,{$this->unicCfg["rescolumn"]},{$this->unicCfg["grescolumn"]} FROM Character WHERE Name='$name' AND AccountID='$login'")->FetchRow();
        $this->character[$name.$login] = $info;

        return $info;
    }

    /**
     * создание лого гильдии
     * @param string $hex
     * @param string $name
     * @param int $size
     * @param int $livetime
     * @return string
     */
    function GuildLogo($hex,$name,$size=64,$livetime)
    {
        $path="theme/imgs/guilds/";
        $ftime = @filemtime($path.$name."-".$size.".png");
        if(file_exists($path.$name."-".$size.".png") && (time() - $ftime <= $livetime))
        {
            return $path.$name."-".$size.".png";
        }
        else
        {
            if(substr($hex,0,2) == "0x")
                $hex = strtolower(substr($hex,2));
            else
                $hex = strtolower($hex);

            $pixelSize	= $size / 8;
            $img = ImageCreate($size,$size);

            if(@preg_match('/[^a-zA-Z0-9]/',$hex) || $hex == '') $hex = '0044450004445550441551554515515655555566551551660551166000566600';
            else $hex = stripslashes($hex);

            for ($y = 0; $y < 8; $y++)
            {
                for ($x = 0; $x < 8; $x++)
                {
                    $offset	= ($y*8)+$x;
                    switch(substr($hex, $offset, 1))
                    {
                        case "0":$c1 = "0";$c2 = "0";$c3 = "0";break;
                        case "1":$c1 = "0";$c2 = "0";$c3 = "0";break;
                        case "2":$c1 = "128";$c2 = "128";$c3 = "128";break;
                        case "3":$c1 = "255";$c2 = "255";$c3 = "255";break;
                        case "4":$c1 = "255";$c2 = "0";$c3 = "0";break;
                        case "5":$c1 = "255";$c2 = "128";$c3 = "0";break;
                        case "6":$c1 = "255";$c2 = "255";$c3 = "0";break;
                        case "7":$c1 = "128";$c2 = "255";$c3 = "0";break;
                        case "8":$c1 = "0";$c2 = "255";$c3 = "0";break;
                        case "9":$c1 = "0";$c2 = "255";$c3 = "128";break;
                        case "a":$c1 = "0";$c2 = "255";$c3 = "255";break;
                        case "b":$c1 = "0";$c2 = "128";$c3 = "255";break;
                        case "c":$c1 = "0";$c2 = "0";$c3 = "255";break;
                        case "d":$c1 = "128";$c2 = "0";$c3 = "255";break;
                        case "e":$c1 = "255";$c2 = "0";$c3 = "255";break;
                        case "f":$c1 = "255";$c2 = "0";$c3 = "128";break;
                        default: $c1 = "255";$c2 = "255";$c3 = "255"; break;
                    }

                    $row[$x] 		= $x*$pixelSize;
                    $row[$y] 		= $y*$pixelSize;
                    $row2[$x] 		= $row[$x] + $pixelSize;
                    $row2[$y]		= $row[$y] + $pixelSize;
                    $color[$y][$x]	= imagecolorallocate($img, $c1, $c2, $c3);
                    imagefilledrectangle($img, $row[$x], $row[$y], $row2[$x], $row2[$y], $color[$y][$x]);
                }
            }
            Imagepng($img,$path.$name."-".$size.".png");
            Imagedestroy($img);
            return $path.$name."-".$size.".png";
        }
    }

    /**
     * возвращает сундучек из игры
     * @param $user
     * @param $len
     * @return string
     * @throws ADODB_Exception
     */
    public function getWH($user,$len)
    {
        $r = $this->db->query("SELECT CONVERT(VARCHAR(".(120*$len)."), Items, 2) as Items FROM warehouse WHERE AccountID='$user'")->FetchRow();
        return strtoupper($r["Items"]);
    }

    /**
     * положить зен в сундук
     *
     * @param int $zen
     * @param string|NULL $login
     * @throws Exception
     */
    public function setWhZen($zen,$login = NULL)
    {
        if(!is_null($login))
        {
            $user = $login;
        }
        else
            $user = $this->user["login"];

        $this->db->query("UPDATE warehouse SET Money = Money + $zen WHERE AccountID='$user'");
    }

}