<?php
/**
 * MuWebCloneEngine
 * version: 1.6
 * by epmak
 * 24.08.2015
 *
 **/

/**
 * Class PController
 * контроллер для плагинов
 */
class PController
{
    public $model;
    public $view;
    public $cfg;
    private $tick;
    private $plugins;
    private $serverNum;
    protected $showAll = 1;// показывать ли полное окно или только кусок модуля (если кому-то приспичит аяксить мплагины, то это будет очень полезное свойство)
    protected $configs = array(); //конфигурации к модулю

    public function __construct(Model $model,content $view,$plugins,$server)
    {
        $this->view = $view;
        $this->model = $model;
        $this->tick =  microtime(); //для проверки времени генерации
        $this->plugins = $plugins;
        $this->serverNum = $server;
    }

    public function init(){}
    public function action_index(){}

    public function showError($er = 2)
    {
        $this->view->error($er);
    }

    public function parentOut($name=null)
    {
        /*
         * суем данные с плагина в переменную, с его именем
         */
        if(is_null($name))
        {
            $name = get_class($this);
        }
        
        if($this->showAll == 1)
            $this->view->setFContainer("plugin_".$name,1);
        else
        {
            echo $this->view->getContainer();
            die();
        }
            
    }

    /**
     * эмуляция не ооп работы модуля
     *
     * @param string $mpath где модуль
     */
    public function genNonMVC($mpath)
    {
        if($this->isCached(__FUNCTION__,basename($mpath,".php"))) //кешик
            return;

        try
        {
            $this->view->showOnly(true);
            $muuser = $this->model;
            $content = $this->view;
            $page = $this;
            $db = $this->model->getDBins();


            ob_start();
            require_once $mpath;
            $cnt = ob_get_contents();
            ob_clean();

            if (!empty($cnt))
                $this->view->setFromCache($cnt);
            $this->view->showOnly(false);
        }
        catch (Exception $e)
        {
            echo $e->getMessage();
            $this->view->showOnly(false);
        }

        if($this->cacheNeed(basename($mpath,".php"))) //если нужен кеш
            $this->doCache(basename($mpath,".php")."_".__FUNCTION__);

        if($this->showAll != 1)
        {
            echo $this->view->getContainer();
            die();
        }

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

        if(!empty($this->plugins[$name]))
            return $this->plugins[$name];
        return false;
    }

    /**
     * возвращает разницу ремени создания файла и текущего
     *
     * @return int
     */
    protected function cacheDif($fname)
    {
        $path = "build".DIRECTORY_SEPARATOR.tbuild.DIRECTORY_SEPARATOR."_dat".DIRECTORY_SEPARATOR."cache".DIRECTORY_SEPARATOR.$this->serverNum."_".$this->view->cLAng()."_plugin_".get_class($this)."_$fname";

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
        $path = "build".DIRECTORY_SEPARATOR.tbuild.DIRECTORY_SEPARATOR."_dat".DIRECTORY_SEPARATOR."cache".DIRECTORY_SEPARATOR.$this->serverNum."_".$this->view->cLAng()."_plugin_".get_class($this)."_$fname";

        if(file_exists($path))
        {
            unlink($path);
        }
    }

    /**
     * Определяет, нужно ли кешировать файл или уже есть кешик
     * @return bool
     */
    protected function cacheNeed($name = null)
    {
        if(is_null($name))
            $name = get_class($this);

        if($this->plugins[$name]["pcache"]>0)
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
        $path = "build".DIRECTORY_SEPARATOR.tbuild.DIRECTORY_SEPARATOR."_dat".DIRECTORY_SEPARATOR."cache".DIRECTORY_SEPARATOR.$this->serverNum."_".$this->view->cLAng()."_plugin_$fname";

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
        $path = "build".DIRECTORY_SEPARATOR.tbuild.DIRECTORY_SEPARATOR."_dat".DIRECTORY_SEPARATOR."cache".DIRECTORY_SEPARATOR.$this->serverNum."_".$this->view->cLAng()."_plugin_$fname";
        $h = fopen($path,"w");
        fwrite($h,$content);
        fclose($h);
    }


    /**
     * функция подхвата кешироваиия вернет true в случае, если есть актуальная копия в кеше
     *
     * @param string $fname - название экшена
     * @return bool
     */
    protected function isCached($fname,$name=null)
    {
        $prop = $this->getPProperties($name);

        if(!is_null($name))
            $fname = $name."_".$fname;

        if($this->cacheDif($fname) <= $prop["pcache"]) //если модуль кешируется и кеш еще актуален, вместо работы модуля берем кеш
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