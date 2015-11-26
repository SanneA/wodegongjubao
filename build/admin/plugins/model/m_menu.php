<?php

/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 31.08.2015
 *
 **/
class m_menu extends Model
{
    /**
     * выбирает из базы менюшку и возвращает ее
     * @return array
     */
    public function getMenu()
    {
        $path = "build".DIRECTORY_SEPARATOR.$_SESSION["mwcabuild"].DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$_SESSION["mwclang"].DIRECTORY_SEPARATOR."titles.php";
        if(file_exists($path))
            require $path;
        else
            $lang = array();

        $return = array();
        $i=0;
        //$q = $this->db->query("SELECT id,mtitle,mtype,link,server,modul FROM mwc_menu WHERE tbuild='{$_SESSION["mwcabuild"]}' and server={$_SESSION["mwcserver"]} AND mtype = 2"); //выбираем меню админа только для нашего билда
        $q = $this->db->query("SELECT mm.id,mm.mtitle,mm.mtype,mm.link,mm.server,mm.modul FROM mwc_menu mm, mwc_menu_type mmt WHERE  mm.mtype = mmt.id AND mmt.tbuild='admin' AND mmt.ttitle='adminmenu' order by mm.col_Seq"); //выбираем меню админа только для нашего билда
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