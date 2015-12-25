<?php
/**
 * Created by epmak
 * Date: 19.01.14
 * MuWebClone
 * класс парса для топа
 * upDate: 25.12.15
 * v 1.1
 */

/**
 * Class Parse
 * класс по работе с топами голосований подобным MMOTOP
 * для работы требуется версия php не ниже 5.х
 * подключенная библиотека CURL (в случае, если используется https)
 */

class TopParse {
    //http(s) адрес до списка проголосовавших
    private $adr;
    //разделитель для отделения столбцов
    private $limiter;
    //сгенерированный массив
    private $out = NULL;
    //внутренний указатель безопасное соединение или нет
    private $isHttps = false;

    /*
     *  Массивы опций
     *  array [fields] описание полей
     *       [acc] -  порядковый номер столбца (с 0) для распознания названия аккаунта(персонажа) Обязателен!
     *       [vote] - порядковый номер столбца (с 0) для распознания кол-ва голосов Обязателен!
     *       [date] -  порядковый номер столбца (с 0) для распознания даты Обязателен!
     *       [ip] -  ip-адрес
     *       [id] - порядковый номер голоса в топе
     *  array [order] - фильтровать по
     *       [name] - "charname" имя акка/персонажа
     *       [total] - true/false - накопительно или вся статистика.
     *  array [lstorage] - настройки для локального хранения файла
     *       [path] - место хранения файла
     *       [cache] - возраст (в сек), после которого последует запрос на генерацию нового, по умолчанию 3600 (1 час)
     *
     */
    private $options = array(
        "order" => array(),
        "lstorage" => array(),
        "fields" => array(
            "acc" => 3,
            "vote" => 4,
            "date" => 1,
            "ip" => 2,
            "id" => 0,
        )
    );
    private $cnt =0;

    /**
     * Parse constructor.
     * @param string $adress  адрес файла статистики
     * @param string $delimiter разделитель
     * @param array $options дополнительные параметры
     */
    public function  __construct($adress,$delimiter,$options="")
    {
        self::parce($adress,$delimiter,$options);
    }

    /**
     * функция - оболочка для парса данных
     * @param string $adress адрес файла статистики
     * @param string $delimiter разделитель
     * @param array  $options дополнительные параметры
     * @return array
     */
    public function parce($adress="",$delimiter="",$options="")
    {
        if (!empty($adress))
            $this->adr = $adress;

        if(!empty($delimiter))
            $this->limiter = $delimiter;

        if(!empty($options) && is_array($options))
            $this->options = $options;

        self::CheckHttps($adress); //проверяем, адресс на безопасное соединение
        self::getData(); // получаем дату

        return $this->out;
    }

    /**
     * возвращает результат работы парсинга
     * @return array
     */
    public function getResult()
    {
        return $this->out;
    }

    /**
     * парсинг и подбивка данных
     */
    private function getData()
    {
        if(
            empty($this->options["fields"]) || ! is_array($this->options["fields"])
            || empty($this->options["fields"]["acc"]) || empty($this->options["fields"]["vote"]) || empty($this->options["fields"]["date"])
        )
            return;

        if ($this->isHttps === false)     //если не защищенное соединение, подключаемся сразу
            $tempA = self::getPage($this->adr);
        else
            $tempA = self::getSslPage($this->adr);


        if (!empty($tempA)) //данные получены
        {
            $this->out = array();
            $iter1 = new ArrayIterator($tempA); //на случай, если массив будет большой
            $i=0;

            foreach ($iter1 as $id=>$v)
            {
                if (empty($v))
                    continue;

                $ar = explode($this->limiter,$v);

                if (trim($ar[$this->options["fields"]["acc"]])=="")
                    continue;
                if(!empty($this->options["order"]) && !empty($this->options["order"]["name"]))
                {
                    if(trim($ar[$this->options["fields"]["acc"]]) != $this->options["order"]["name"])
                        continue;

                    if(!empty($this->options["order"]["total"]) && $this->options["order"]["total"] === true) //общее кол-во голосов по акку
                    {
                        $this->out["acc"] = $ar[$this->options["fields"]["acc"]];
                        if(!empty($this->out["vote"]))
                            $this->out["vote"] += (int)$ar[$this->options["fields"]["vote"]];
                        else
                            $this->out["vote"] = (int)$ar[$this->options["fields"]["vote"]];
                    }
                    else //вся стата по персонажу
                    {
                        $out["acc"] = $ar[$this->options["fields"]["acc"]];
                        $out["vote"] = $ar[$this->options["fields"]["vote"]];
                        $out["date"] = $ar[$this->options["fields"]["date"]];
                        if(!empty($ar[$this->options["fields"]["ip"]]))
                            $out["ip"] = $ar[$this->options["fields"]["ip"]];
                        if(!empty($ar[$this->options["fields"]["id"]]))
                            $out["id"] = $ar[$this->options["fields"]["id"]];

                        $this->out[$i] = $out;
                        $i++;
                        unset($out);
                    }
                }
                else
                {
                    $out["acc"] = $ar[$this->options["fields"]["acc"]];
                    $out["vote"] = $ar[$this->options["fields"]["vote"]];
                    $out["date"] = $ar[$this->options["fields"]["date"]];
                    if(!empty($this->options["fields"]["ip"]) && !empty($ar[$this->options["fields"]["ip"]]))
                        $out["ip"] = $ar[$this->options["fields"]["ip"]];
                    if(!empty($this->options["fields"]["id"]) && !empty($ar[$this->options["fields"]["id"]]))
                        $out["id"] = $ar[$this->options["fields"]["id"]];

                    $this->out[$i] = $out;
                    $i++;
                    unset($out);
                }

            }
        }
    }

    /**
     * curl приходит на выручку  случае https
     * @param string $url
     * @return array
     */
    protected function getSslPage($url)
    {
        if(!empty($this->options["lstorage"]) && !empty($this->options["lstorage"]["path"])) //если есть настройка о том, где хранится файл кеша, сначала проверяем его
        {
            if(file_exists($this->options["lstorage"]["path"]))
            {
                $time = filemtime($this->options["lstorage"]["path"]);
                if(empty($this->options["lstorage"]["cache"]))
                    $this->options["lstorage"]["cache"] = 3600;

                if(time() - $time < $this->options["lstorage"]["cache"]) //если файл еще не считается неактуальным, то берем его
                {
                    return file($this->options["lstorage"]["path"]);
                }

            }
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_REFERER, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $result = curl_exec($ch);
        curl_close($ch);

        if(!empty($this->options["lstorage"]["path"]))
            self::write($result,$this->options["lstorage"]["path"]);

        $result = explode("\n",$result);
        return $result;
    }

    /**
     * @param string $url
     * @return array
     */
    protected function getPage($url)
    {
        if(!empty($this->options["lstorage"]) && !empty($this->options["lstorage"]["path"])) //если есть настройка о том, где хранится файл кеша, сначала проверяем его
        {
            if(file_exists($this->options["lstorage"]["path"]))
            {
                $time = filemtime($this->options["lstorage"]["path"]);
                if(empty($this->options["lstorage"]["cache"]))
                    $this->options["lstorage"]["cache"] = 3600;

                if(time() - $time < $this->options["lstorage"]["cache"]) //если файл еще не считается неактуальным, то берем его
                {
                    return file($this->options["lstorage"]["path"]);
                }
            }
        }

        if(!empty($this->options["lstorage"]["path"]))
        {
            $tmp = file($url);
            $result = implode("\n",$tmp);
            self::write($result,$this->options["lstorage"]["path"]);
            return $tmp;
        }

        return file($url);
    }

    /**
     * функция проверки адреса на ащищенное соединение
     * @param string $url  адрес
     */
    private function CheckHttps($url)
    {
        if(strpos($url,"https://") === false)
            $this->isHttps = false;
        else
            $this->isHttps = true;
    }

    /**
     * Запись данных в кеш
     * @param string $content
     * @param string $path
     */
    private function write($content,$path)
    {
        echo $path;
        $h = fopen($path,"w");
        fwrite($h,$content);
        fclose($h);
    }

} 