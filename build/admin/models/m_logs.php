<?php

/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 18.09.2015
 *
 **/
class m_logs extends Model
{

    /**
     * список событий
     * @return array
     */
    public function getEvList()
    {
        $epath = "build".DIRECTORY_SEPARATOR.$_SESSION["mwccfgread"].DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$_SESSION["mwclang"].DIRECTORY_SEPARATOR."errors.php";
        $list = array();

        if(file_exists($epath))
        {
            require $epath;

            $ai = new ArrayIterator($lang);


            foreach ($ai as $lid => $val)
            {
                if(substr($lid,0,3) == "err")
                {
                    $er_num = (int)str_replace('err','',$lid);
                    $list[$er_num] = $val;
                }
            }
        }

        return $list;
    }

    /**
     * Возвращает список файлов логов
     * @return array
     */
    public function getFilelist()
    {
        $epath = "log";
        $fils = scandir($epath);

        $files = array();
        $ai = new ArrayIterator($fils);

        foreach ($ai as $fid => $fname)
        {
            if($fname != "." && $fname != ".." && $fname != ".htaccess")
                $files[$fid] = $fname;
        }
        return $files;
    }


    /**
     * список файлов из собитий
     * @return array
     */
    public function fileList()
    {
        $q = $this->db->query("SELECT col_mname FROM mwc_logs GROUP BY col_mname");
        $ret = array();

        while ($r = $q->FetchRow())
        {
            $ret[$r["col_mname"]] = $r["col_mname"];
        }

        return $ret;
    }


    /**
     * выборка логов
     * @param $top
     * @param $event
     * @param $file
     * @param $begin
     * @param $end
     * @param $msg
     * @return array
     * @throws ADODB_Exception
     * @throws Exception
     */
    public function showLogsArr($top,$event,$file,$begin,$end,$msg)
    {

        if ($file!="0")
            $file = "AND col_mname = '$file'";
        else
            $file = "";

        if($event>0)
            $event = "AND col_ErrNum = $event";
        else
            $event = "";

        if(!is_null($msg))
        {
            $msg = "AND col_msg like '%$msg%'";
        }
        else
            $msg = "";


        if($this->db->ConType() >3)
            $q = $this->db->query("SELECT * FROM mwc_logs WHERE tbuild = '{$_SESSION["mwccfgread"]}' AND col_createTime BETWEEN '$begin' and '$end' $file $event $msg ORDER by col_LogID DESC limit $top");
        else
            $q = $this->db->query("SELECT TOP $top * FROM mwc_logs WHERE tbuild = '{$_SESSION["mwccfgread"]}' AND col_createTime BETWEEN  convert(datetime,'$begin',120) and convert(datetime,'$end',120) $file $event $msg ORDER by col_LogID DESC ");

        $ret = array();

        while ($r = $q->FetchRow())
        {
            $ret[] = $r;
        }

        return $ret;
    }

    /**
     * возвращает содержимое лога
     * @param $name
     * @return bool|string
     */
    public function getFileContent($name)
    {
        $file = "log".DIRECTORY_SEPARATOR.$name;
        if(file_exists($file))
            return htmlspecialchars(file_get_contents($file));
        return false;
    }
}