<?php  //ERROR_REPORTING( E_ALL ^ E_WARNING ^ E_NOTICE ^ E_ERROR);

/**
 * Класс-обертка над adodb для
 * для работы с базой данных
 * v 1.1
 **/
include "libraries". DIRECTORY_SEPARATOR ."adodb5". DIRECTORY_SEPARATOR ."adodb-exceptions.inc.php";
include "libraries". DIRECTORY_SEPARATOR ."adodb5". DIRECTORY_SEPARATOR ."adodb.inc.php";

class connect
{
    static protected  $inst = null;
    private $resId; // идентификатор ресурсов
    private $iserror; //идентификатор ошибки
    private $btype; //тип подключения
    private $lastq;//последний запрос
    private $ntype;
    private $cons = array(
        "SQL",
        "MPDO",
        "ODBC",
        "MYSQL",
        "PDO"
    );

    private $suf="";

    /**
     * почти конструктор
     * @param null|int|string $type
     * @param null|string $host
     * @param null|string $base
     * @param null|string $user
     * @param null|string $pwd
     * @return connect|null
     */
    static public function start($type = NULL,$host = NULL,$base = NULL,$user = NULL,$pwd = NULL)
    {
        if(self::$inst==null)
        {
            self::$inst = new connect($type,$host,$base,$user,$pwd);
        }
        return self::$inst;
    }

    /**
     * @param int $type
     * @param string $host
     * @param string $base
     * @param string $user
     * @param string $pwd
     * @throws Exception
     */
    private function __construct ($type = NULL,$host = NULL,$base = NULL,$user = NULL,$pwd = NULL)
    {
        if(is_null($type) && is_null($host) && is_null($base) && is_null($user) && is_null($pwd))
        {
            if(empty($_SESSION["mwcserver"]))
                $srv = 0;
            else
                $srv = $_SESSION["mwcserver"];

            $cfg = Configs::readCfg("main",tbuild);
            $type = $cfg["ctype"];
            $host = $cfg["db_host"][$srv];
            $base = $cfg["db_name"][$srv];
            $user = $cfg["db_user"][$srv];
            $pwd = $cfg["db_upwd"][$srv];
        }

        $this->iserror=false;
        $this->btype=$type;
        global $ADODB_FETCH_MODE;
        $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
        switch ($type)
        {
            case "SQL":
            case 1:
                $this->mmsql($host,$base,$user,$pwd);$this->ntype=1;break;    //ms sql connection
            case "MPDO":
            case 2:
                $this->pdo_mssql($host,$base,$user,$pwd);$this->ntype=2;break; //pdo ms sql connection
            case "ODBC":
            case 3:
                $this->odbc_mmsql($host,$base,$user,$pwd);$this->ntype=3;break; //odbc mssql connection
            case "MYSQL":
            case 4:
                $this->mysql($host,$base,$user,$pwd);$this->ntype=4;break;  // mysql connection
            case "PDO":
            case 5:
                $this->pdo_mysql($host,$base,$user,$pwd);$this->ntype=5; break; //pdo mysql connection
            default:
                throw new Exception("Unknown connect Type '$type'");
        }
    }

    /**
     * получение суффикса dbo для mssql когда это надо для кроссбазового запроса
     * @return string
     */
    public function getSuf()
    {
        return $this->suf;
    }

    /**
     * логи в таблицу Mwc_logs
     *
     * @param string $msg - текст лога
     * @param string $file - файл, в котором ахтунг
     * @param int $errNo - номер ошибки
     * @param bool|true $isValid - экранировать ли текст лога?
     * @throws Exception
     */
    public function SQLog($msg,$file="1",$errNo = 0, $isValid = true)
    {
        if ($file == "1")
            $file = basename (__FILE__,'.php');

        if($isValid == true)
            $msg = htmlspecialchars($msg,ENT_QUOTES);

        if($this->btype<=3)
            $dt = "GETDATE()";
        else
            $dt = "NOW()";

        try{
            self::query("INSERT INTO mwce_settings.{$this->suf}mwc_logs(col_ErrNum,col_msg,col_mname,col_createTime,tbuild) VALUES($errNo,'$msg','{$file}',$dt,'".tbuild."')");
        }
        catch(Exception $e)
        {
            @file_put_contents("log/[".@date("d_m_Y", time())."]SQL_".tbuild."_error.txt",$msg,FILE_APPEND);
        }

    }

    /**
     * возвращает поддерживаемые подключения
     *
     * @return array
     */
    public function SupportedCon()
    {
        return $this->cons;
    }

    /**
     * возвращает тип подключения
     *
     * @return int
     */
    public function ConType()
    {
        return $this->ntype;
    }

    /**
     * функция возвращает последний insert id
     * @param string $tbname - название таблицы, куда была последняя вставка
     * @return int id
     */
    public function lastId($tbname=null)
    {
        if ($this->ntype <4) // ms
        {
            if (!$tbname)
                return NULL;

            $res = self::query("SELECT IDENT_CURRENT('{$tbname}') as lastid")->FetchRow();
            return $res["lastid"];
        }
        else
        {
            $res = self::query("SELECT LAST_INSERT_ID()")->FetchRow();
            return $res["LAST_INSERT_ID()"];
        }
    }

    /**
     * mssql подключение (устаревшее)
     * @param string $host
     * @param string $base
     * @param string $user
     * @param string $pwd
     * @throws Exception
     */
    private function mmsql($host,$base,$user,$pwd)
    {
        if (function_exists("mssql_connect"))
        {
            $this->resId = ADONewConnection('mssql');
            if(!empty($base))
                $this->resId->PConnect($host,$user,$pwd,$base);
            else
                $this->resId->PConnect($host,$user,$pwd);
            $this->suf="[dbo].";
        }
        else
            throw new Exception("mssql_connect is NOT supported!");
    }

    /**
     * mysql подключение (устаревшее)
     * @param string $host
     * @param string $base
     * @param string $user
     * @param string $pwd
     * @throws Exception
     */
    private function mysql($host,$base,$user,$pwd)
    {
        if (function_exists("mysql_connect"))
        {
            if(empty($base))
            {
                $dsn = "mysql://$user:$pwd@$host";
                self::query("CREATE DATABASE mwce_settings CHARACTER SET utf8 COLLATE utf8_general_ci; USE mwce_settings;");
            }
            else
                $dsn = "mysql://$user:$pwd@$host/$base?clientflags=65536";

            $this->resId = ADONewConnection($dsn);
            self::query("SET names 'utf8'");
        }
        else
            throw new Exception("mysql_connect is NOT supported!");
    }

    /**
     * @param string $host
     * @param string $base
     * @param string $user
     * @param string $pwd
     * @throws Exception
     */
    private function odbc_mmsql($host,$base,$user,$pwd)
    {
        if (function_exists("odbc_connect"))
        {
            $this->resId = ADONewConnection('odbc_mssql');
            if(!empty($base))
                $dsn = "Driver={SQL Server};Server=".$host.";Database=".$base.";";
            else
                $dsn = "Driver={SQL Server};Server=".$host.";";
            $this->resId->debug=false;
            $this->resId->PConnect($dsn,$user,$pwd);

            $this->suf="[dbo].";
        }
        else
            throw new Exception("odbc_connect is NOT supported!");
    }

    /**
     * @param string $host
     * @param string $base
     * @param string $user
     * @param string $pwd
     * @throws Exception
     */
    private function pdo_mssql($host,$base,$user,$pwd)
    {
        $drivers = PDO::getAvailableDrivers();
        if (in_array("mssql",$drivers))
        {
            if(!empty($base))
            $this->resId = NewADOConnection("pdo_mssql://{$user}:{$pwd}@{$host}/{$base}");
            else
            $this->resId = NewADOConnection("pdo_mssql://{$user}:{$pwd}@{$host}");

            $this->suf="[dbo].";
        }
        else
            throw new Exception("PDO_mssql is NOT supported!");
    }

    /**
     * @param string $host
     * @param string $base
     * @param string $user
     * @param string $pwd
     * @throws Exception
     */
    private function pdo_mysql($host,$base,$user,$pwd)
    {
        $drivers = PDO::getAvailableDrivers();

        if (in_array("mysql",$drivers))
        {
            if(empty($base))
            {
                $this->resId = NewADOConnection("pdo_mysql://{$user}:{$pwd}@{$host}");
                self::query("CREATE DATABASE mwce_settings CHARACTER SET utf8 COLLATE utf8_general_ci; USE mwce_settings;");
            }
            else
                $this->resId = NewADOConnection("pdo_mysql://{$user}:{$pwd}@{$host}/{$base}");

            self::query("SET names 'utf8'");
        }
        else
            throw new Exception("PDO_mysql is NOT supported!");
    }

    /**
     * сообщение от mssql сервера (в большинстве случаев не требуется)
     * @return string
     */
    public function getMsg()
    {
        return $this->resId->lastMessage;
    }

    /**
     * сообщение от mssql сервера (в большинстве случаев не требуется)
     * @return string
     */
    public function Msg()
    {
        return $this->resId->ErrorMsg();
    }

    /**
     * запрос в базу данных
     * @param string $qtext
     * @return mixed
     * @throws Exception
     */
    public function query($qtext)
    {
        try
        {
            $this->lastq = $qtext;
            return $this->resId->Execute($qtext);
        }
        catch (Exception $ex)
        {
            error_reporting(0);
            $agr = $ex->getTrace();
            $agr[0]["args"][3] = htmlspecialchars($agr[0]["args"][3],ENT_QUOTES);
            $agr[0]["args"][4] = htmlspecialchars($agr[0]["args"][4],ENT_QUOTES);
            $agr[3]["file"] = basename($agr[3]["file"],'.php');
            $agr[3]["line"] = htmlspecialchars($agr[3]["line"],ENT_QUOTES);
            self::SQLog("<b>Error:</b> {$agr[0]["args"][3]}<br> <b>Query:</b> {$agr[0]["args"][4]} <br> <b>File: </b>{$agr[3]["file"]}<BR><b>Line: </b>{$agr[3]["line"]}",$agr[3]["file"],1,false);
            throw new Exception("Error in database.");
        }
    }

    /**
     * получение текста последнего запроса
     * @return mixed
     */
    public function  getQuery()
    {
        return $this->lastq;
    }

    /**
     * возвращает инстанс adodb, что похволяет пользоваться неописанными
     * в данном классе функциями напрямую
     * @return mixed
     */
    public function getADOInst()
    {
        return $this->resId;
    }
}
