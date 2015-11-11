<?php

/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 26.08.2015
 *
 **/

/**
 * Class builder
 * класс формирует и работает с таблицей,
 * где зарегистрированы модели и контроллеры модулей.
 */
class builder
{
    private $DB;    //инстанс класса работы с базой данных
    private $build; //к какому проекту относится
    private $lang;
    private $server;

    public function __construct($db,$tbuild,$lang,$server)
    {
        $this->DB = $db;
        $this->build = $tbuild;
        $this->lang = $lang;
        $this->server = $server;

        $this->checkBase();
    }


    /**
     * генерация файла страниц
     */
    public function genPage()
    {
        if($this->DB->ConType() < 4)//если ms sql
            $q = $this->DB->query("SELECT * FROM mwc_pages WHERE tbuild = '{$this->build}'");
        else
            $q = $this->DB->query("SELECT *, (SELECT GROUP_CONCAT(goupId) FROM mwc_access WHERE pageId = id) AS groups FROM mwc_pages  WHERE tbuild = '{$this->build}'");

        $inf = '<?php ';
        /*
         * title название страницы (для заголовка, берет из языка)
         * ppath название папке в билде, где лежит котроллер или просто обычный файл(если модуль написан без ооп)
         * caching время кеширования (В секундах)
         * chonline функция проверки при загрузке
         * ison включен ли?
         * server номер сервера
         * mname название модели для файла(если есть)
         * mpath путь до модели(если есть)
         */

        while($res = $q->FetchRow())
        {
            $inf.='$page["'.$res["pname"].'"]["title"]="'.$res["ptitle"].'"; ';
            $inf.='$page["'.$res["pname"].'"]["ppath"]="'.$res["ppath"].'"; ';
            $inf.='$page["'.$res["pname"].'"]["caching"]="'.$res["caching"].'"; ';
            //$inf.='$page["'.$res["pname"].'"]["chonline"]="'.$res["chonline"].'"; ';
            $inf.='$page["'.$res["pname"].'"]["ison"]="'.$res["ison"].'"; ';
            $inf.='$page["'.$res["pname"].'"]["server"]="'.$res["server"].'"; ';

            if(!empty($res["mname"]) && !empty($res["mpath"]))
            {
                $inf.='$page["'.$res["pname"].'"]["mname"]="'.$res["mname"].'"; ';
                $inf.='$page["'.$res["pname"].'"]["mpath"]="'.$res["mpath"].'"; ';
            }

            if($this->DB->ConType() < 4)//если ms sql ...да, страшный костыль...
            {
                $q1 = $this->DB->query("SELECT goupId FROM mwc_access WHERE pageID = ".$res["id"]);
                $grps = "";
                while ($gr = $q1->FetchRow())
                    $grps.=$gr["goupId"].",";

                $grps = substr($grps,0,-1);

                $inf.='$page["'.$res["pname"].'"]["groups"]="'.$grps.'"; ';
            }
            else
            {
                $inf.='$page["'.$res["pname"].'"]["groups"]="'.$res["groups"].'"; ';
            }

            $inf.="\r\n";

        }

        $this->writef("build".DIRECTORY_SEPARATOR.$this->build.DIRECTORY_SEPARATOR."_dat".DIRECTORY_SEPARATOR.$this->server."_".$this->lang."_pages.php",$inf);

    }

    /**
     * запись базы плагинов
     */
    public function genPlugin()
    {

        if($this->DB->ConType() < 4)//если ms sql
            $q = $this->DB->query("SELECT * FROM mwc_plugins WHERE tbuild = '{$this->build}' order by seq asc");
        else
            $q = $this->DB->query("SELECT *, (SELECT GROUP_CONCAT(col_groupID) FROM mwc_pluginsaccess WHERE col_pluginID = pid) AS groups FROM mwc_plugins WHERE tbuild = '{$this->build}'");

        $inf ='<?php ';
    /*
     * pstate статус плагина: 0/1 выкл/откл
     * pcache кеширование в секундах
     * pserver сервер
     * mname название модели (Если есть)
     */

        while ($res=$q->FetchRow())
        {
            $inf.='$plugin["'.$res["pname"].'"]["pstate"]="'.$res["pstate"].'"; ';
            $inf.='$plugin["'.$res["pname"].'"]["pcache"]="'.$res["pcache"].'"; ';
            $inf.='$plugin["'.$res["pname"].'"]["pserver"]="'.$res["pserver"].'"; ';

            if(!empty($res["mname"]))
                $inf.='$plugin["'.$res["pname"].'"]["mname"]="'.$res["mname"].'"; ';

            if($this->DB->ConType() < 4)//если ms sql ...да, страшный костыль...
            {
                $q1 = $this->DB->query("SELECT col_groupID FROM mwc_pluginsaccess WHERE col_pluginID = ".$res["pid"]);
                $grps = "";
                while ($gr = $q1->FetchRow())
                    $grps.=$gr["col_groupID"].",";

                $grps = substr($grps,0,-1);

                $inf.='$plugin["'.$res["pname"].'"]["groups"]="'.$grps.'"; ';
            }
            else
            {
                $inf.='$plugin["'.$res["pname"].'"]["groups"]="'.$res["groups"].'"; ';
            }

            $inf.="\r\n";
        }

        $this->writef("build".DIRECTORY_SEPARATOR.$this->build.DIRECTORY_SEPARATOR."_dat".DIRECTORY_SEPARATOR.$this->server."_".$this->lang."_plugins.php",$inf);

    }

    /**
     * принудительное обновление баз доступа к модулям
     *
     * @param int $type 0 - модули и плагины, 1- только модули, 2- только плагины
     */
    public function refresh($type=0)
    {
        if($type == 0)
        {
            unlink("build".DIRECTORY_SEPARATOR.$this->build.DIRECTORY_SEPARATOR."_dat".DIRECTORY_SEPARATOR.$this->server."_".$this->lang."_pages.php");
            unlink("build".DIRECTORY_SEPARATOR.$this->build.DIRECTORY_SEPARATOR."_dat".DIRECTORY_SEPARATOR.$this->server."_".$this->lang."_plugins.php");
        }
        elseif($type == 1)
            unlink("build".DIRECTORY_SEPARATOR.$this->build.DIRECTORY_SEPARATOR."_dat".DIRECTORY_SEPARATOR.$this->server."_".$this->lang."_pages.php");
        else
            unlink("build".DIRECTORY_SEPARATOR.$this->build.DIRECTORY_SEPARATOR."_dat".DIRECTORY_SEPARATOR.$this->server."_".$this->lang."_plugins.php");

        $this->checkBase();
    }

    /**
     * запись файла
     *
     * @param string $fname полый адрес до хранения файла
     * @param string $content что писать
     */
    public function writef($fname,$content)
    {
        $fh = fopen($fname,"w");
        fwrite($fh,$content);
        fclose($fh);
    }

    public function checkBase()
    {
        if(!file_exists("build".DIRECTORY_SEPARATOR.$this->build.DIRECTORY_SEPARATOR."_dat".DIRECTORY_SEPARATOR.$this->server."_".$this->lang."_pages.php"))
        {
            $this->genPage();
        }

        if(!file_exists("build".DIRECTORY_SEPARATOR.$this->build.DIRECTORY_SEPARATOR."_dat".DIRECTORY_SEPARATOR.$this->server."_".$this->lang."_plugins.php"))
        {
            $this->genPlugin();
        }
    }

}