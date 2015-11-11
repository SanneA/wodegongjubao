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



}