<?php

/**
 * Class Tools
 * функции, не вошеджие не в 1 другой класс
 */
class Tools
{
    /**
     * @param null|string $path адрес
     * перенаправление на нужную встраницу. В случае пустого передаваемого параметра сделает перезагрузку страницы.
     */
    public static function go($path=NULL)
    {
        if($path == NULL)
        {
            if(!empty($_SERVER["REQUEST_URI"]))
                $path = "http://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
            else
                $path = "http://".$_SERVER["HTTP_HOST"];
        }
        header("Location: {$path}");
        die();
    }

    /**
     * @param $obj
     * функция позволяет увидеть содержимое всех переданных в нее параметров
     */
    public static function debug($obj)
    {
        $numargs = func_num_args();
        if($numargs>1)
        {
            $arg_list = func_get_args();
            for ($i = 0; $i < $numargs; $i++)
            {
                print "<pre>";
                print_r($arg_list[$i]);
                print "</pre>";
            }
        }
        else
        {
            print "<pre>";
            print_r($obj);
            print "</pre>";
        }
    }

    /**
     * @param array $args - массив с данными для заполнения элемента
     * @param mixed $chosen - какой элемент должен быть выбран по умолчанию
     * @param string $name - название и id элемента
     * @param array|string $others - любые html-атрибуты элемента.
     * либо в виде строки: "style='width:12px;' onchange='alert(123);'",
     * либо в виде ассоциативного массива: array("style"=>"width:12px;","onchange"=>"alert(123);")
     * @return string - html код элемента select
     */
    public static function htmlSelect($args,$name,$chosen=-1,$others="")
    {
        if(is_array($others))
        {
            $htmAttr="";
            foreach ($others as $id => $val)
            {
                $htmAttr.=" $id=\"$val\"";
            }
        }
        else
            $htmAttr = $others;

        $text = "<select name=\"{$name}\" id=\"{$name}\" {$htmAttr}>";
        $wassel = 0;

        if(!empty($args) && is_array($args))
        {
            foreach ($args as $id=>$val)
            {
                $text.="<option value=\"$id\"";
                if ($chosen == $id && $wassel == 0)
                {
                    $text.=" SELECTED ";
                    $wassel=1;
                }
                $text.=">$val</option>";
            }
        }
        $text.="</select>";
        return $text;
    }

    /**
     *
     * @param array $collect:
     * [0] запись перед чекбоксом
     * [1] имя = id
     * [2] значение
     * [3] функции js
     * [4] нажат? (1/0)
     * [5] css класс
     * @return string сгенерированный html код
     */
    public static function bChbx($collect)
    {
        $return = "";
        foreach ($collect as $array)
        {
            if (isset($array[4]) && $array[4]>0)
                $array[4]="CHECKED";
            else
                $array[4]="";
            if(!isset($array[3]))
                $array[3] = "";
            if(!isset($array[5]))
                $array[5] = "";

            $return.= " $array[0] <input type='checkbox' name='$array[1]' id='$array[1]' value='$array[2]' $array[3]  $array[4] class='$array[5]'>";
        }

        return $return;
    }

    /**
     * снимает эффект экранирования htmlspecialchars
     * @param string $str экранированная строка
     * @return string string исходная строка
     */
    public static function unhtmlentities ($str)
    {
        $trans_tbl = get_html_translation_table (HTML_ENTITIES);
        $trans_tbl = array_flip ($trans_tbl);
        return strtr ($str, $trans_tbl);
    }

    /**
     * @param string $date
     * @param bool|false $type
     * @return bool|string
     * конвертация даты из бд в человекопонятную дату
     */
    public static function transDate($date= "0000-00-00",$type=false)
    {
        if (trim($date) == "0000-00-00" or $date==NULL or $date=="1970-01-01 00:00:00" or $date=="1970-01-01")
            return "-/-";
        if (!$type)
            return @date("d-m-Y",strtotime($date));
        return @date("d-m-Y H:i",strtotime($date));
    }

    /**
     * @param $date
     * @param bool|false $type
     * @return bool|string
     * конвертация даты в дату, пригодную для бд(смена формата даты))
     */
    public static function intransDate($date,$type=false)
    {
        if ($date == NULL)
            return "-/-";
        if (!$type)
            return @date("Y-m-d",strtotime($date));
        return @date("Y-m-d H:i:s",strtotime($date));
    }

    /**
     * @param datetime $a
     * @param datetime $b - вычитаемое
     * @param bool|false $type true - разница в днях, false - в часах
     * @return int
     * узнать разницу между датами
     */
    public static function dateDif($a,$b,$type=false)
    {
        $a = strtotime($a);
        $b = strtotime($b);
        if (!$type)
            $c = floor(($a - $b)/86400);
        else
            $c = floor(($a - $b)/3600);
        return (int)$c;
    }

    /**
     * пагинатор, то есть возвращает значения для выборки,сколько страниц (min,max,count)
     * @param int $count поличество записей в общем
     * @param int $perpage сколько записей на страницу
     * @param int $curpage текущая страница
     * @return array min,max,count
     */
    public static function paginate($count,$perpage,$curpage=1)
    {
        $total = floor($count/$perpage); //сколько страниц
        $ost = $count % $perpage; //сколько страниц в остатке

        if ($ost>0)
            $total++; // если есть еще страницы

        $return["min"] = ($curpage-1)*$perpage;
        $return["max"] = $curpage*$perpage ;
        $return["count"] = $total;

        return $return;
    }

    /**
     * возвращает асоцифтивный массив с названиями папков всех билдов (папка build)
     * @return array
     */
    public static function getAllBuilds()
    {
        $list = scandir("build");
        $ai = new ArrayIterator($list);
        $sel = array();

        foreach ($ai as $id=>$v)
        {
            if($v!="." && $v!=".." && $v!=".htaccess")
            {
                $sel[$v] = $v;
            }

        }
        $sel[-1] = "...";

        return $sel;
    }

    /**
     * форматирует числа с отступами по английской манере
     * @param  int   $num
     * @param string $pos
     * @return string
     */
    public static function number($num,$pos="")
    {
        return number_format($num, 2, ',', ' ')." ".$pos;
    }

    static public function linkDec($link)
    {
        return str_replace("&amp;","&",$link);
    }
}