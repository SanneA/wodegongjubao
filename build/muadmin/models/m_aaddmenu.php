<?php

/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 16.09.2015
 * работа с меню в бд
 **/

class m_aaddmenu extends Model
{
    /**
     * список существующих меню
     * @return array
     */
    public function getMenuList()
    {
        $q = $this->db->query("SELECT * FROM MWC_menu_type WHERE tbuild='{$_SESSION["mwccfgread"]}'");
        $ma = array();
        while($result = $q->FetchRow())
        {
            $ma[$result["id"]]=$result["ttitle"];
        }

        return $ma;
    }

    /**
     * Добавить в меню
     * @param string $title
     * @param int $type
     * @param string $link
     * @param int $modul
     */
    public function addToMenu($title,$type,$link,$modul,$seq)
    {
        $this->db->query("INSERT INTO mwc_menu (mtitle,mtype,link,modul,col_Seq) VALUES ('$title',$type,'{$link}','{$modul}',$seq)");
    }

    /**
     * Добавить новый тип меню
     * возвратит либо числовой идентификатор нового типа, либо false
     * @param string $name
     * @return bool|int
     */
    public function newMenu($name)
    {
        $count = $this->db->query("SELECT COUNT(*) as cnt FROM mwc_menu_type WHERE ttitle='{$name}' AND tbuild='{$_SESSION["mwccfgread"]}'")->FetchRow();

        if ($count["cnt"]==0)
        {
            $this->db->query("INSERT INTO mwc_menu_type (ttitle,tbuild) VALUES ('{$name}','{$_SESSION["mwccfgread"]}')");
            return $this->db->lastId("mwc_menu_type");
        }
        return false;
    }

    /**
     * удалить меню по его идентификатору
     * @param int $id
     */
    public function delMenu($id)
    {
        $this->db->query("DELETE from mwc_menu WHERE mtype = $id; DELETE FROM mwc_menu_type WHERE id = $id");
    }

    /**
     * возращает список позиций в меню определенного типа
     * @param int $m_id
     * @return array
     */
    public function getMtitles($m_id)
    {
        $q = $this->db->query("SELECT
 mm.id,
 mm.mtitle,
 mm.link,
 mt.ttitle as mtype
FROM
 mwc_menu mm,
 mwc_menu_type mt
WHERE
 mm.mtype = $m_id
 AND mt.id = mm.mtype
 AND mt.tbuild = '{$_SESSION["mwccfgread"]}' order by mm.col_Seq");
        $return = array();

        while ($r = $q->FetchRow())
        {
            $return[$r["id"]] = array("mtitle"=>$r["mtitle"],"link"=>$r["link"],"mtype"=>$r["mtype"]);
        }

        return $return;
    }

    /**
     * возвращает информацию по меню
     * @param int $id
     * @return array
     */
    public function getInfoPerPage($id)
    {
        return $this->db->query("SELECT * FROM mwc_menu WHERE id = $id")->FetchRow();
    }


    /**
     * список зарегенных страниц
     * @return array
     */
    public function pageList()
    {
        $array = array(-1=>"...");
        $q = $this->db->query("SELECT pname,ptitle FROM mwc_pages WHERE tbuild='{$_SESSION["mwccfgread"]}'");

        $lpath = "build".DIRECTORY_SEPARATOR.$_SESSION["mwccfgread"].DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$_SESSION["mwclang"].DIRECTORY_SEPARATOR."titles.php";
        if (file_exists($lpath))
            require $lpath;
        else
            $lang = array();

        while ($r = $q->FetchRow())
        {
            if(!empty($lang[$r["ptitle"]]))
                $array[$r["pname"]] = $lang[$r["ptitle"]];
            else
                $array[$r["pname"]] = $r["ptitle"];
        }

        return $array;
    }

    /**
     * доавбляем позицию в меню
     * @param string $title
     * @param int $type
     * @param string $link
     * @param string $modul
     * @param int $id
     */
    public function editToMenu($title,$type,$link,$modul,$id,$seq)
    {
        $this->db->query("UPDATE mwc_menu  SET mtitle = '$title',mtype=$type,link='{$link}',modul = '{$modul}', col_Seq=$seq WHERE id = $id");
    }

    /**
     * удалить позицию из меню по идентификатору
     * @param int $id
     */
    public function delMpos($id)
    {
        $this->db->query("DELETE from mwc_menu WHERE id = $id");
    }
}