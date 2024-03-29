<?php
/**
 * MuWebCloneEngine
 * Created by epmak
 *
 */

/**
 * Class CastleSiege v 0.2
 * отображение текущего периода

 $castle = new CastleSiege("6-12-2014 00:00",array(
"period1" => 86400, //начало регистраии
"period2" => 75600, //Сдача Sign of Lord
"period3" => 1800, //Объявление участников на осаду
"period4" => 1800, //Подготовка к осаде
"period5" => 3600, //осада
"period6" => 60, //кончилась осада
));
 */

class CastleSiege {

    private $dateBegin;
    private $period = array();
    private $pattern;

    /**
     * функция вернет текущий период времени
     * @param datetime $begin дата начала (цифры, англ. яз)
     * @param array $periods массив с периодами в формате "название периода" => длительность, кол-во секунд
     * @param string $datePattern
     * @throws Exception
     */
    public function __construct($begin, $periods, $datePattern = "d.m.Y H:i")
    {
        if(is_array($periods))
        {
            $this->dateBegin = $begin;
            $this->period = $periods;
            $this->pattern = $datePattern;
        }
        else
            throw new Exception("periods must be an array with duration parts");
    }

    /**
     * Функция возвращает текущий период
     * @return array|string
     * [название периода] => Array
                            (
                            [pBegin] => начало
                            [pEnd] => завершение
                            )
     */
    public function getPeriod()
    {
        $forNow = time();
        $tdate = @strtotime($this->dateBegin);
        $cdate = @strtotime($this->dateBegin);
        foreach($this->period as $id=>$val)
        {
            $cdate+=$val;
            if($forNow >= $tdate && $forNow <= $cdate)
            {
                return array("name"=> $id,
                    "pBegin" => @date($this->pattern,$tdate),
                    "pEnd" => @date($this->pattern,$cdate-1),
                );
            }
            $tdate+=$val;
        }

        return "undefined";
    }

    /**
     * Функция возвращает  расписание с поменткой текущего пероиода
     * @return array|string
     * [название периода] => Array
    (
    [pBegin] => начало
    [pEnd] => завершение
    [checked] => 1/0 (текущий или нет)
    )
     */
    public function getAll()
    {
        $forNow = time();
        $tdate = $cdate = @strtotime($this->dateBegin);
        $return = array();
        foreach($this->period as $id=>$val)
        {
            $cdate+=$val;
            $return[$id] = array(
                "pBegin" => @date($this->pattern,$tdate),
                "pEnd" => @date($this->pattern,$cdate-1),
                "checked" => 0
            );

            if($forNow >= $tdate && $forNow <= $cdate)
            {
                $return[$id]["checked"] = 1;
            }
            $tdate+=$val;
        }

        return $return;
    }
}