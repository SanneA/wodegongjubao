<?php

/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 02.12.2015
 * общий класс менюшек
 **/

class Menu extends Model
{
    /**
     * @param string $build название билда
     * @param string $name название меню
     * @return array
     * @throws ADODB_Exception
     */
    public function getMenu($build,$name)
    {
        $path = "build".DIRECTORY_SEPARATOR.$build.DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$_SESSION["mwclang"].DIRECTORY_SEPARATOR."titles.php";
        if(file_exists($path))
            require $path;
        else
            $lang = array();

        $return = array();
        $i=0;

        $q = $this->db->query("SELECT mm.id,mm.mtitle,mm.mtype,mm.link,mm.server,mm.modul FROM mwc_menu mm, mwc_menu_type mmt WHERE  mm.mtype = mmt.id AND mmt.tbuild='$build' AND mmt.ttitle='$name' order by mm.col_Seq"); //выбираем меню админа только для нашего билда
        while ($r = $q->FetchRow())
        {
            if(!empty($lang[$r["mtitle"]]))
                $r["mtitle"] = $lang[$r["mtitle"]];

            $r["link"] = Tools::linkDec($r["link"]); //снимает экранирование с амперсанда

            $return[$i] = $r;

            $i++;
        }

        return $return;
    }
}