<?php
/**
 * MuWebCloneEngine
 * version: 1.6
 * by epmak
 * 24.08.2015
 *
 **/

class Controller
{
    public $model; //инстанс класса модели
    public $view; //инстанс класса шаблонизатора
    private $tick; //для измерения времени генерации страницы
    private $serverNum; //номер сервера (по умолчанию всегда 0), нужен для верной генерации кеша и служебных файликов
    protected $pages; //массив со всеми страницами
    protected $showMain = 1;// показывать ли полное окно или только кусок модуля
    protected $configs = array(); //конфигурации к модулю
    protected $postField; //поля для валидации из POST - массива
    protected $getField; //поля для валидации из GET - массива
    protected $needValid = true; //проверять или нет пост и гет


    public function __construct(Model $model,content $view,$pages,$server)
    {
        $this->view = $view;
        $this->model = $model;
        $this->tick =  microtime(); //для проверки времени генерации
        $this->pages = $pages;
        $this->serverNum = $server;

        if(!empty($_SESSION["mwcbuild"]))
            $build = $_SESSION["mwcbuild"];
        else if(!empty($_SESSION["whosconfig"]))
            $build = $_SESSION["whosconfig"];
        else if(!empty($_SESSION["mwcabuild"]))
            $build = $_SESSION["mwcabuild"];
        else
            $build = NULL;

        if(!is_null($build))
            $this->configs = Configs::readCfg(get_class($this),$build); //подгружаем конфиги модуля сразу

        self::validate();
    }

    public function init(){}
    public function action_index(){}
    public function action_title(){}

    /**
     * эмуляция не ооп работы модуля
     *
     * @param string $mpath где модуль
     */
    public function genNonMVC($mpath)
    {
        if($this->isCached(__FUNCTION__,basename($mpath,".php"))) //кешик
            return;

        try{
            $this->view->showOnly(true);
            $muuser = $this->model;
            $content = $this->view;
            $page = $this;
            $db = $this->model->getDBins();

            ob_start();
            require_once $mpath;
            $cnt = ob_get_contents();
            ob_clean();

            if(!empty($cnt))
                $this->view->setFromCache($cnt);
            $this->view->showOnly(false);
        }
        catch(Exception $e)
        {
            echo $e->getMessage();
            $this->view->showOnly(false);
        }

        if($this->cacheNeed(basename($mpath,".php"))) //если нужен кеш
            $this->doCache(basename($mpath,".php")."_".__FUNCTION__);
    }

    public function showError($er = 2)
    {
        $this->view->error($er);
    }

    /**
     * глобальное отображение всей собранной страницы
     */
    public function parentOut($bg=null)
    {
        /*
         * выводим на экран сгенеренные данные модулей в ключевое
         * слово "page" в шаблон "index.html" в папке "theme/тема/html/public"
         */

        if($this->showMain == 1 && is_null($bg))
            $this->view->global_out($this->view->defHtml,"public","page",$this->tick);
        else
            echo $this->view->getContainer();
    }

    /**
     * узнать настройки данного модуля
     *
     * @return bool|array
     */
    public function getPProperties($name=NULL)
    {
        if(is_null($name))
            $name = get_class($this);

        if(!empty($this->pages[$name]))
            return $this->pages[$name];
        return false;
    }

    /**
     * возвращает разницу ремени создания файла и текущего
     *
     * @return int
     */
    protected function cacheDif($fname)
    {
        $path = "build".DIRECTORY_SEPARATOR.tbuild.DIRECTORY_SEPARATOR."_dat".DIRECTORY_SEPARATOR."cache".DIRECTORY_SEPARATOR.$this->serverNum."_".$this->view->cLAng()."_$fname";

        if(file_exists($path))
        {
            return time() - filemtime($path);
        }
        else
            return 0;
    }

    /**
     * удаляем файлик кеша
     */
    protected function cacheDelete($fname)
    {
        $path = "build".DIRECTORY_SEPARATOR.tbuild.DIRECTORY_SEPARATOR."_dat".DIRECTORY_SEPARATOR."cache".DIRECTORY_SEPARATOR.$this->serverNum."_".$this->view->cLAng()."_$fname";

        if(file_exists($path))
        {
            unlink($path);
        }
    }

    /**
     * Определяет, нужно ли кешировать файл или уже есть кешик
     * @param string|null $name название модуля
     * @return bool
     */
    protected function cacheNeed($name = null)
    {
        if(is_null($name))
            $name = get_class($this);

        if($this->pages[$name]["caching"]>0)
            return true;
        return false;

    }

    /**
     * возвращает закешированный модуль иначе, пустую строку
     * @param string $fname название функции
     * @return string
     */
    protected function cacheGive($fname)
    {

        $path = "build".DIRECTORY_SEPARATOR.tbuild.DIRECTORY_SEPARATOR."_dat".DIRECTORY_SEPARATOR."cache".DIRECTORY_SEPARATOR.$this->serverNum."_".$this->view->cLAng()."_$fname";

        if(file_exists($path))
        {
            return file_get_contents($path);
        }
        return "";
    }

    /**
     * пишем кеш
     *
     * @param string $fname
     * @param string $content
     */
    protected function cacheWrite($fname,$content)
    {
        $path = "build".DIRECTORY_SEPARATOR.tbuild.DIRECTORY_SEPARATOR."_dat".DIRECTORY_SEPARATOR."cache".DIRECTORY_SEPARATOR.$this->serverNum."_".$this->view->cLAng()."_$fname";
        $h = fopen($path,"w");
        fwrite($h,$content);
        fclose($h);
    }


    /**
     * функция подхвата кешироваиия вернет true в случае, если есть актуальная копия в кеше
     *
     * @param string $fname - название экшена
     * @param string|null $name название модуля
     * @return bool
     */
    protected function isCached($fname,$name=null)
    {

        $prop = $this->getPProperties($name);
        if(!is_null($name))
            $fname = $name."_".$fname;

        if($this->cacheNeed($name) && $this->cacheDif($fname) <= $prop["caching"]) //если модуль кешируется и кеш еще актуален, вместо работы модуля берем кеш
        {
            $cache = $this->cacheGive($fname);
            if(empty($cache))
                return false;

            $this->view->setFromCache($this->cacheGive($fname)); //суем в контейнер данные
            return true;
        }
        return false;

    }

    /**
     * пишем кеш для экшена
     *
     * @param string $fname - экшен
     */
    protected function doCache($fname)
    {
        $cache = $this->view->getContainer();

        if(!empty($cache))
            $this->cacheWrite($fname, $cache); //пишем кеш
    }

    /**
     * фиильтрация данных
     */
    protected function validate()
    {
        if(!$this->needValid) //если выставлен флаг, что не надо валидации, значит, не надо валидации :)
        {
            return;
        }

        if(empty($this->postField))
            self::clearPost();
        else
            self::customPostValid();

        if(empty($this->getField))
            self::clearGet();
        else
            self::customGetValid();
    }

    protected function clearGet()
    {
        $ai = new ArrayIterator($_GET);
        foreach ($ai as $id=>$v)
        {
            if(!empty($GLOBALS["get_".$id."_v"]) && $GLOBALS["get_".$id."_v"] == true)
                continue;
            $v = trim(htmlspecialchars($v,ENT_QUOTES));
            $v = preg_replace("/(\&lt\;br \/\&gt\;)|(\&lt\;br\&gt\;)/",' <br /> ',$v);
            //$v = str_replace(' ','', preg_replace("/[^[:digit:]A-Za-zА-Яа-я_@.\!\]\[ \-+()=]/ui",'',$v));

            if ($_GET[$id] != $v)
            {
                $this->model->toLog("GET -> {$_GET[$id]} != {$v}, URI:{$_SERVER["REQUEST_URI"]} ","validation",4);
            }
            $_GET[$id] = $v;
            $GLOBALS["get_".$id."_v"] = true;
        }
    }

    /**
     * очистка от нежелательных символов POST массива
     */
    protected function clearPost()
    {
        $ai = new ArrayIterator($_POST);
        foreach ($ai as $id=>$v)
        {
            if(!empty($GLOBALS["post_".$id."_v"]) && $GLOBALS["post_".$id."_v"] == true)
                continue;
            if(is_array($v))
                continue;

            $v = trim(htmlspecialchars(self::checkText($v),ENT_QUOTES));
            //$v = trim(htmlspecialchars(self::checkText($v),ENT_QUOTES));
            //$v =  preg_replace("/(\&lt\;br \/\&gt\;)|(\&lt\;br\&gt\;)/",' <br /> ',$v);
            if(function_exists("get_magic_quotes_gpc"))
            {
                if (get_magic_quotes_gpc()) $v = stripslashes($v);
                $v = str_replace('`','&quot;',$v);
            }

            if (trim($_POST[$id])!=$v)
            {
                $this->model->toLog("POST -> {$_POST[$id]} != {$v}, URI:{$_SERVER["REQUEST_URI"]} ","validation",4);
            }
            $_POST[$id]=$v;
            $GLOBALS["post_".$id."_v"] = true;
        }
    }

    /**
     * возврат исходного текста после htmlspecialchars
     *
     * @param string $str
     * @return string
     */
    static protected function decode ($str)
    {
        $trans_tbl = get_html_translation_table (HTML_ENTITIES);
        $trans_tbl = array_flip ($trans_tbl);
        $ret= strtr ($str, $trans_tbl);
        return preg_replace("/scri/",'',$ret);
    }

    /**
     * снятие последствий htmlspecialchars для ссылок
     *
     * @param $link
     * @return string
     */
    static public function linkDec($link)
    {
        return str_replace("&amp;","&",$link);
    }

    /**
     * проверка текста на сюрпризы со вложенными тегами
     *
     * @param string $text
     * @return string
     */
    static public function checkText($text)
    {
        return preg_replace("!<script[^>]*>|</script>|<(\s{0,})iframe(\s{0,})>|</(\s{0,})iframe(\s{0,})>!isU",'!removed bad word!',$text);
    }

    /**
     * фильтр пост массива, если не пустой $postField
     */
    protected function customPostValid()
    {
        if(!empty($_POST))
        {
            $ai = new ArrayIterator($_POST);
            foreach ($ai as $id=>$val)
            {
                if(!empty($GLOBALS["post_".$id."_v"]) && $GLOBALS["post_".$id."_v"] == true)
                    continue;

                if(!empty($this->postField[$id]))
                {
                    $val = trim($val);

                    if(!empty($this->postField[$id]["type"]))
                        $type = $this->postField[$id]["type"];
                    else
                        $type = gettype($val);

                    if(!empty($this->postField[$id]["maxLength"]))
                        $val = substr($val,0,(int)$this->postField[$id]["maxLength"]);

                    $val = self::paramsControl($val,$type);

                    if(function_exists("get_magic_quotes_gpc"))
                    {
                        if(function_exists("stripslashes"))
                        {
                            if (get_magic_quotes_gpc())
                                $val = stripslashes($val);
                        }

                        $v = str_replace('`','&quot;',$val);
                    }

                    if($_POST[$id] != $val)
                        $this->model->toLog("-> POST[$id] = $val",get_class($this),4);

                    $_POST[$id] = $val;
                    $GLOBALS["post_".$id."_v"] = true;
                }
            }
        }
    }

    /**
     * фильтр пост массива, если не пустой $getField
     */
    protected function customGetValid()
    {
        if(!empty($_GET))
        {
            $ai = new ArrayIterator($_GET);
            foreach ($ai as $id=>$val)
            {
                if(!empty($GLOBALS["get_".$id."_v"]) && $GLOBALS["get_".$id."_v"] == true)
                    continue;

                if(!empty($this->getField[$id]))
                {
                    $val = trim($val);

                    if(!empty($this->getField[$id]["type"]))
                        $type = $this->getField[$id]["type"];
                    else
                        $type = gettype($val);

                    if(!empty($this->getField[$id]["maxLength"]))
                        $val = substr($val,0,(int)$this->getField[$id]["maxLength"]);

                    $val = self::paramsControl($val,$type);

                    if(function_exists("get_magic_quotes_gpc"))
                    {
                        if(function_exists("stripslashes"))
                        {
                            if (get_magic_quotes_gpc())
                                $val = stripslashes($val);
                        }

                        $val = str_replace('`','&quot;',$val);
                    }

                    $val = preg_replace("/(\&lt\;br \/\&gt\;)|(\&lt\;br\&gt\;)/",' <br /> ',$val);

                    if($_GET[$id] != $val)
                        $this->model->toLog("-> GET[$id] = $val",get_class($this),4);

                    $_GET[$id] = $val;
                    $GLOBALS["get_".$id."_v"] = true;
                }
            }
        }
    }

    /**
     * приведение типов по парамету
     * @param number|string $param
     * @param string $type
     * @return bool|float|int|string
     */
    protected function paramsControl($param,$type)
    {
        switch($type)
        {
            case "double":
            case "float": $param = floatval($param);break;
            case "int":
            case "integer": $param = intval($param);break;
            case "str":
            case "string": $param = htmlspecialchars(self::checkText($param),ENT_QUOTES);break;
            case "bool":
            case "boolean": $param = boolval($param);break;
            case "array": break;
            case "date": $param = date_::intransDate($param); break;
            case "datetime": $param = date_::intransDate($param,true); break;
            default: $param = htmlspecialchars(self::checkText($param),ENT_QUOTES);break;
        }

        return $param;
    }
}