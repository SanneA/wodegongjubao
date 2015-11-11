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

    /**
     * @param int $type
     * @param string $host
     * @param string $base
     * @param string $user
     * @param string $pwd
     * @throws Exception
     */
    public function __construct ($type,$host,$base,$user,$pwd)
    {
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
            throw new ADODB_Exception("Unknown connect Type");
        }
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

        self::query("INSERT INTO mwc_logs(col_ErrNum,col_msg,col_mname,col_createTime) VALUES($errNo,'$msg','{$file}',$dt)");
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


    private function mmsql($host,$base,$user,$pwd)
    {
        if (function_exists("mssql_connect"))
        {
            $this->resId = ADONewConnection('mssql');
            $this->resId->PConnect($host,$user,$pwd,$base);
        }
        else
            throw new ADODB_Exception("mssql_connect is NOT supported!");
    }

    private function mysql($host,$base,$user,$pwd)
    {
        if (function_exists("mysql_connect"))
        {
            $dsn = "mysql://$user:$pwd@$host/$base?clientflags=65536";
            $this->resId = ADONewConnection($dsn);
            self::query("SET names 'utf8'");
        }
        else
            throw new ADODB_Exception("mysql_connect is NOT supported!");
    }


    private function odbc_mmsql($host,$base,$user,$pwd)
    {
        if (function_exists("odbc_connect"))
        {
            $this->resId = ADONewConnection('odbc_mssql');
            $dsn = "Driver={SQL Server};Server=".$host.";Database=".$base.";";
            $this->resId->debug=false;
            $this->resId->PConnect($dsn,$user,$pwd);
        }
        else
            throw new ADODB_Exception("odbc_connect is NOT supported!");
    }

    private function pdo_mssql($host,$base,$user,$pwd)
    {
        $drivers = PDO::getAvailableDrivers();
        if (in_array("mssql",$drivers))
        {
            $this->resId =& NewADOConnection("pdo_mssql://{$user}:{$pwd}@{$host}/{$base}");
        }
        else
            throw new ADODB_Exception("PDO_mssql is NOT supported!");
    }

    private function pdo_mysql($host,$base,$user,$pwd)
    {
        $drivers = PDO::getAvailableDrivers();

        if (in_array("mysql",$drivers))
        {
            $this->resId =& NewADOConnection("pdo_mysql://{$user}:{$pwd}@{$host}/{$base}");
        }
        else
            throw new ADODB_Exception("PDO_mysql is NOT supported!");
    }

    public function getMsg()
    {
        return $this->resId->lastMessage;
    }
    public function Msg()
    {
        return $this->resId->ErrorMsg();
    }

    public function query($qtext)
    {
        try
        {
            $this->lastq = $qtext;
            return $this->resId->Execute($qtext);
        }
        catch (ADODB_Exception $ex)
        {
            error_reporting(0);
            $agr = $ex->getTrace();
            $agr[0]["args"][3] = htmlspecialchars($agr[0]["args"][3],ENT_QUOTES);
            $agr[0]["args"][4] = htmlspecialchars($agr[0]["args"][4],ENT_QUOTES);
            $agr[3]["file"] = basename($agr[3]["file"],'.php');
            $agr[3]["line"] = htmlspecialchars($agr[3]["line"],ENT_QUOTES);
            self::SQLog("<b>Error:</b> {$agr[0]["args"][3]}<br> <b>Query:</b> {$agr[0]["args"][4]} <br> <b>File: </b>{$agr[3]["file"]}<BR><b>Line: </b>{$agr[3]["line"]}",$agr[3]["file"],1,false);
            throw $ex;
        }
    }

    public function  getQuery()
    {
        return $this->lastq;
    }

    public function getADOInst()
    {
        return $this->resId;
    }
}
