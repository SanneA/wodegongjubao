<?php

/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 21.10.2015
 *
 **/
class html_
{
    /**
     * @param array $args - массив с данными для заполнения элемента
     * @param mixed $chosen - какой элемент должен быть выбран по умолчанию
     * @param string $name - название и id элемента
     * @param array|string $others - любые html-атрибуты элемента.
     * либо в виде строки: "style='width:12px;' onchange='alert(123);'",
     * либо в виде ассоциативного массива: array("style"=>"width:12px;","onchange"=>"alert(123);")
     * @return string - html код элемента select
     */
    public static function select($args,$name,$chosen=-1,$others="")
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
    public static function checkbox($collect)
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
}