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
     * @param int $num число
     * @param int $nums кол-во символов после запятой
     * @return string
     */
    public static function number($num,$nums=2)
    {
        return number_format($num, $nums, ',', ' ');
    }

    static public function linkDec($link)
    {
        return str_replace("&amp;","&",$link);
    }
}