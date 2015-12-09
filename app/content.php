<?php

/**
 * MuWebCloneEngine
 * version: 1.6
 * by epmak
 * 25.08.2015
 *
 **/

/**
 * Class content
 * шаблонизатор.
 */
class content
{
    private $vars = array(); //массив значений на которые будем заменять
    private $debug; //дебаг: показывать или нет пустые переменные
    private $themName; //название темы
    private $lng = array();
    private $clang; //текущий язык
    private $error = array();
    private $adr ;
    private $separator; //разделитель
    private $container ="";
    private $notWrite = 0;
    public $defHtml = "index"; //отображаемая по умолчанию главная страница
    private $adedDic = array(); //список подключенных словарей
    private $connectCss = array();
    private $connectjs = array();

    /**
     * @param string $adr - адресс сайта
     * @param string $theme - назщвание темы
     * @param string $lang - язык
     * @param string $separator - суффикс и преффикс показывающий признак, что слово ключевое
     * @param int $debug - 1 - режим дебага, пр икотором все не заполненные ключевые фразы видны
     * @throws Exception
     */
    public function __construct($adr,$theme,$lang,$separator="|",$debug=0)
    {
        $this->debug = $debug;
        $this->clang = $lang;
        $this->themName = $theme;
        $this->adr = $adr;
        $this->separator = $separator;

        $this->vars["site"]=$this->adr;
        $this->vars["theme"]=$this->themName;
        $this->vars["global_js"]="";
        $this->vars["global_css"]="";

        $path = "theme". DIRECTORY_SEPARATOR .$this->themName. DIRECTORY_SEPARATOR ."html". DIRECTORY_SEPARATOR ."public". DIRECTORY_SEPARATOR ."index.html";
        if(!file_exists($path))
        {
            throw new Exception("there is no theme \"{$this->themName}\" or ".$this->themName. DIRECTORY_SEPARATOR ."html". DIRECTORY_SEPARATOR ."public". DIRECTORY_SEPARATOR ."index.html doesn't exists.");
        }
    }

    static public function gContent($path)
    {
        return @file_get_contents($path);
    }


    /**
     * выводит весь словарь
     *
     * @return array
     */
 /*   public function getAbs()
    {
        return $this->lng;
    }*/

    /**
     * Вывод отдельного слова по идентификатору
     *
     * @param mixed $id идентификатор
     * @return string
     */
    public function getVal($id)
    {
        return $this->vars[$id];
       // return $this->lng[$id];
    }

    /**
     * возвращает адрес сервера
     *
     * @return mixed
     */
    public function getAdr()
    {
        return $this->adr;
    }

    /**
     * Добавляет язык к контенту
     *
     * @param  string $file - название файла "словаря"
     * @return object $this content object
     */
    public function add_dict($file)
    {
        if(is_array($file))
        {
            //$this->lng+=$file;
            foreach ($file as $d=>$v)
                $this->vars[$d] = $v;
        }
        else
        {
            $filead = "build". DIRECTORY_SEPARATOR .tbuild. DIRECTORY_SEPARATOR ."lang".DIRECTORY_SEPARATOR .$this->clang. DIRECTORY_SEPARATOR .$file.".php";

            if (file_exists($filead))
            {
                if(!empty($this->adedDic[$file])) // если словарь уже подключен, второй раз лопатить смысла нет
                    return $this;

                $this->adedDic[$file] = 1;
                /** @var $lang array */
                require $filead;

                if (isset($lang) && is_array($lang))
                {
                    //$this->lng+=$lang;
                    foreach ($lang as $d=>$v)
                    {
                        $this->vars[$d] = $v;
                    }

                }
            }
        }
        return $this;
    }

    /**
     * возвращает текущий язык
     *
     * @return string
     */
    public function cLAng()
    {
        return $this->clang;
    }

    /**
     * добаляет в словарь
     *
     * @param string|array $name - резервированное слово(без "|"), если массив, то ассоциативный кгде ключ - заресервированное слово, а значение, то, на что нужно слово заменить
     * @param mixed $val - значение зарезервированного слова
     * @return $this content object
     */
    public function set($name, $val=NULL)
    {
        if(is_array($name))
        {
            $this->add_dict($name);
        }
        else if(!is_null($val))
            $this->vars[$name] = $val;


        return $this;
    }

    /**
     * заменяет название элемента в "словаре" (!в словаре должно присутствовать выражение $where)
     * @param string $what - что вставить
     * @param string $where - за место чего
     * @return $this
     */
    public function replace($what,$where)
    {
        if (!empty($this->vars[$what]))
        {
            $this->set($where,$this->vars[$what]);
           // unset($this->vars[$what]);
        }
        return $this;
    }

    /**
     * обрабатывает шаблон, ища ключевые слова и заменяя их на данные из словаря
     *
     * @param string $path - пусть до файла шаблона
     * @return string - обработанный текст шаблона
     */
    private function collect_dictionary($path)
    {
        $content = self::gContent($path);

        $cur_c = new ArrayIterator($this->vars);

        foreach($cur_c as $key => $val)
        {
            if(is_array($val))
                continue;
            $content = str_replace($this->separator.$key.$this->separator, $val, $content);
        }


        if ($this->debug == 0)
            $content = preg_replace("/[\{$this->separator}]+[A-Za-z0-9_]{1,25}[\{$this->separator}]+/", " ", $content);

        return $content;
    }

    /**
     * Функция очищаент контенер
     */
    public function clearContainer()
    {
        $this->container = "";
    }

    /**
     * возвращает информацию из конетенра
     *
     * @return string
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * позволяет включить или отключить режими забиси в буфер и выводить сразу на экран
     * @param bool|int $val
     */
    public function showOnly($val)
    {
        if((int)$val>0)
            $this->notWrite = 1;
        else
            $this->notWrite = 0;
    }

    /**
     * пишет в контейнер данные
     * @param string $value
     */
    public function setFromCache($value)
    {
        $this->container = $value;
    }


    /**
     * задает, в какую переменную будут помещены данные из контенера
     *
     * @param string $cname - название переменной
     * @param int $isClean - если >0 то после добавления в словарь данные из контерена будут удалены
     * @return $this
     */
    public function setFContainer($cname,$isClean=0)
    {
        if(!empty($this->container))
        {
            $this->set($cname,$this->container);
            if((int)$isClean>0)
                $this->clearContainer();
        }
        return $this;
    }

    /**
     * функция выводит на экран или возвращает строку с содержимым шаблона и скрипта
     * @param string $tpl - название шаблона
     * @param int $type - если 1, данные собираются в контенер, иначе просто выводятся на экран? если 2, то не выводится а возвращается
     * @param string $folder - папка под группу файлов (обычно для модуля)
     * @param int $gentime , вывод времени генерации скрипта, если не 0
     * @return mixed|string
     */
    public function out($tpl,$folder="",$type=1,$gentime=0)
    {
        if(empty($folder))
            $folder = "public";

        $path = "theme". DIRECTORY_SEPARATOR .$this->themName. DIRECTORY_SEPARATOR ."html". DIRECTORY_SEPARATOR .$folder. DIRECTORY_SEPARATOR .$tpl.".html";


        if(file_exists($path))
        {
            $jspath = "theme". DIRECTORY_SEPARATOR .$this->themName. DIRECTORY_SEPARATOR ."js". DIRECTORY_SEPARATOR .$folder.".".$tpl.".js";
            if(file_exists($jspath))
            {
                if(empty($this->connectjs[$folder.".".$tpl.".js"]))
                {
                    $this->vars["global_js"].= "/*imputed $tpl*/ ".trim(@file_get_contents($jspath));
                    $this->connectjs[$folder.".".$tpl.".js"] = 1;
                }
            }


            $csspath = "theme". DIRECTORY_SEPARATOR .$this->themName. DIRECTORY_SEPARATOR ."css".DIRECTORY_SEPARATOR .$folder.".".$tpl.".css";

            if(file_exists($csspath))
            {
                if(empty($this->connectCss[$folder.".".$tpl.".css"]))
                {
                    $this->vars["global_css"].= "{/*imputed $tpl*/}  ".trim(@file_get_contents($csspath));
                    $this->connectCss[$folder.".".$tpl.".css"] = 1;
                }

            }

            if ($gentime!=0)
            {
                $this->vars["gentime"] = round(microtime()-$gentime,4);
            }

            $content = $this->collect_dictionary($path);

            if($type == 1 && $this->notWrite == 0) //если собираем
            {
                $this->container.=$content;
            }
            else if($type != 2)
                echo $content;

            return $content;
        }
        else
        {
            $this->errortext("file \"$path\" doesn't exists");
        }
    }

    /**
     * глобальный вывод на экран
     *
     * @param string $args - зарезервированное слово, в которое сольется весь накомпленный контейнер
     * @param string $tpl - файл шаблона, в который все будет сливаться
     * @param string $folder - папка
     * @param int $gentime - время microtime() для подсчета времени выполнения
     */
    public function global_out($tpl,$folder="", $args="page",$gentime=0)
    {
        $this->setFContainer($args); //суем из контенера в переменную
        $this->out($tpl,$folder,0,$gentime);
        //$this->clearContainer();
    }

    /**
     * культурно показывает ошибки на экран
     *
     * @param string $msg - заглавие ошибки
     * @param string $descr - подробности ошибки
     */
    public static function showError($msg,$descr=" ")
    {
        if(file_exists("theme". DIRECTORY_SEPARATOR ."error.html"))
        {
            $content = file_get_contents("theme". DIRECTORY_SEPARATOR ."error.html");
            $c = array("|msg|"=>$msg,"|msg_desc|"=>$descr);
            foreach($c as $key => $val)
                $content = str_replace($key, $val, $content);
            echo $content;
        }
        else
        {
            die($msg);
        }

    }

    /**
     * вывод ошибки по номеру на экран
     * @param int $erNum номер ошибки
     */
    public function error($erNum)
    {
        $this->add_dict("errors");
        $this->replace("err".$erNum,"msg_desc");
        $this->out("error","public");
    }

    /**
     * вывод ошибки с заданным текстом
     *
     * @param string $text
     */
    public function errortext($text)
    {
        $this->add_dict("errors");
        $this->set("msg_desc",$text);
        $this->out("error","public");

    }

}