<?php
/**
 * Created by epmak
 * Date: 19.01.14
 * MuWebClone
 * класс парса для топа
 */

/**
 * Class TopParse
 * класс по работе с топами голосований MMOTOP и Q-TOP
 * для работы требуется версия php не ниже 5.х
 * подключенная библиотека CURL (в случае, если используется https)
 */

class TopParse {
    //http(s) адрес до списка проголосовавших
    private $adr;
    //разделитель для отделения столбцов
    private $limiter;
    //сгенерированный массив
    private $out;
    //внутренний указатель безопасное соединение или нет
    private $isHttps = false;
    /*
     *  Массив опций
     *  [statisic] принимает значения
     *        false - выбираем всех подряд и все подряд записи
     *        true - записи носят накопительный характер, то есть, все голоса сложаться, ип, дата, номер будут последними
     *  [onlyone] кого выбираем
     *        false - выбираем всех
     *        <ник> - выбираем конкретного персонажа/аккаунт
     */
    private $options = array("statisic"=>"false","onlyone"=>"false");
    private $cnt =0;

    public function  __construct($adress,$delimiter,$options="")
    {
        self::CheckHttps($adress);
        $this->adr = $adress;
        $this->limiter = $delimiter;
        if($options!="")
            $this->options = $options;
    }

    /**
     * функция - оболочка для парса данных
     * @param string       $adress
     * @param string       $delimiter
     * @param array|string $options
     * @return array
     */
    public function parce($adress="",$delimiter="",$options="")
    {
        //region переменные
        if (empty($adress))
            $ad = $this->adr;
        else
        {
            $ad = $adress;
            $this->adr = $adress;
        }

        if(empty($delimiter))
            $del = $this->limiter;
        else
        {
            $del = $delimiter;
            $this->limiter = $delimiter;
        }

        if($options!="")
            $this->options = $options;
        //endregion

        self::getData();
        return $this->out;
    }

    /**
     * основная функция по разбору полученных данных
     */
    private function getData()
    {
        if ($this->isHttps == false)
        {
            $tempA = file($this->adr);
        }
        else
        {
            $tempA = self::getSslPage($this->adr);
        }

        if (!empty($tempA))
        {
            $this->out = array();
            $iter1 = new ArrayIterator($tempA); //на случай, если массив будет большой
            $tempB = array();
            $b=-1;

            foreach ($iter1 as $id=>$v)
            {
                if (empty($v))
                    continue;
                $ar = explode($this->limiter,$v);
                if($this->cnt==0)
                {
                    $this->cnt = count($ar);
                    if ($this->cnt>5)
                    {
                        $nick = 4;
                        $voice = 5;
                    }
                    else
                    {
                        $nick = 3;
                        $voice = 4;
                    }
                }
                if (trim($ar[$nick])=="")
                    continue;

                if ($this->options["statisic"]=="false")
                {
                    if ($this->options["onlyone"]=="false")
                        $this->out[$id] = $ar;
                    else
                    {
                        if(trim($ar[$nick]) == $this->options["onlyone"])
                        {
                            $this->out[$id] = $ar;
                        }
                    }
                }
                else
                {
                    if ($this->options["onlyone"]=="false")
                    {

                        if(!isset($tempB[trim($ar[$nick])]))
                        {
                            $tempB[trim($ar[$nick])] = $id; //запоминаем на данный ник данные
                            $b = $id;
                        }
                        else
                        {
                            $b = $tempB[$ar[$nick]];
                           // echo $v[$nick]."<br>";
                        }

                        //в случае ммо ип есть, на q топе нету
                        $this->out[$b][0] = $ar[0];
                        $this->out[$b][1] = $ar[1];
                        $this->out[$b][2] = $ar[2];

                        if (!isset($this->out[$b][$nick]))
                            $this->out[$b][$nick] = $ar[$nick];

                        if (isset( $this->out[$b][$voice]))
                            $this->out[$b][$voice] += $ar[$voice];
                        else
                            $this->out[$b][$voice] = $ar[$voice];
                    }
                    else
                    {

                        if(trim($ar[$nick]) == $this->options["onlyone"])
                        {
                            if($b<0)
                            {
                                $b = $id;
                            }
                            //в случае ммо ип есть, на q топе нету
                            $this->out[$b][0] = $ar[0];
                            $this->out[$b][1] = $ar[1];
                            $this->out[$b][2] = $ar[2];

                            if (!isset($this->out[$b][$nick]))
                                $this->out[$b][$nick] = $ar[$nick];

                            if (isset( $this->out[$b][$voice]))
                                $this->out[$b][$voice] += $ar[$voice];
                            else
                                $this->out[$b][$voice] = $ar[$voice];
                        }
                    }

                }
            }
        }
    }

    /**
     * curl приходит на выручку  случае https
     * @param string $url
     * @return array
     */
    protected function getSslPage($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_REFERER, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $result = curl_exec($ch);
        curl_close($ch);
        $result = explode("\n",$result);
        return $result;
    }

    /**
     * функция проверки адреса на ащищенное соединение
     * @param string $url  адрес
     */
    private function CheckHttps($url)
    {
        if (substr(strtolower($url),0,5) == "https")
            $this->isHttps = true;
        else
            $this->isHttps = false;
    }

} 