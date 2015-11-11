<?php

/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 28.08.2015
 *
 **/

/**
 * Class validation
 *
 * валидация.
 */
class validation
{
    private $db;

    public function __construct(connect $db)
    {
        $this->db = $db;

        $this->clearGet();
        $this->clearPost();
    }

    /**
     * очистка от нежелательных символов GET массива
     */
    protected function clearGet()
    {
        $ai = new ArrayIterator($_GET);
        foreach ($ai as $id=>$v)
        {
            $v = trim(htmlspecialchars($v,ENT_QUOTES));
            $v = preg_replace("/(\&lt\;br \/\&gt\;)|(\&lt\;br\&gt\;)/",' <br /> ',$v);
            //$v = str_replace(' ','', preg_replace("/[^[:digit:]A-Za-zА-Яа-я_@.\!\]\[ \-+()=]/ui",'',$v));

            if ($_GET[$id] != $v)
            {
                $this->db->SQLog("GET -> {$_GET[$id]} != {$v}, URI:{$_SERVER["REQUEST_URI"]} ","validation",4);
            }
            $_GET[$id] = $v;
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
                $this->db->SQLog("POST -> {$_POST[$id]} != {$v}, URI:{$_SERVER["REQUEST_URI"]} ","validation",4);
            }
            $_POST[$id]=$v;
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

}