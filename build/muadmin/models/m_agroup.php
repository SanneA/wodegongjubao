<?php

/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 09.09.2015
 *
 **/
class m_agroup extends Model
{
    /**
     * получить список групп
     * @return array
     */
    public function getGroups()
    {
        $q = $this->db->query("SELECT * FROM mwc_group");
        $retAr = array();
        while($r = $q->FetchRow())
        {
            $retAr[$r["id"]] = $r["g_name"];
        }

        return $retAr;
    }

    /**
     * удаляет группу и все параметры доступа
     * @param INT $id номер группы в базе
     */
    public function delGroup($id)
    {
        $inf = $this->db->query("SELECT g_name FROM mwc_group WHERE id = $id")->FetchRow();
        $this->db->query("DELETE FROM mwc_access WHERE goupId = $id;  DELETE FROM mwc_group WHERE id = $id");

        $path = "build".DIRECTORY_SEPARATOR.$_SESSION["mwccfgread"].DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$_SESSION["mwclang"].DIRECTORY_SEPARATOR."group.php";

        if(file_exists($path))
            require $path;
        else
            return;

        unset($lang[$inf["g_name"]]);
        $ai = new ArrayIterator($lang);
        foreach ($ai as $id=>$val)
        {
            $content.='$lang["'.$id.'"]="'.$val.'"; ';
        }

        if(!empty($content))
        {
            $h = fopen($path,"w");
            fwrite($h,'<?php '.$content);
            fclose($h);
        }
    }

    /**
     * добавить новую группу
     * @param string $name
     */
    public function addGroup($name)
    {
        $path = "build".DIRECTORY_SEPARATOR.$_SESSION["mwccfgread"].DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$_SESSION["mwclang"].DIRECTORY_SEPARATOR."group.php";
        if(file_exists($path))
        {
            require $path;
        }
        else
            $lang = array();

        $i =1;
        while(!empty($lang["auto_grp".$i]))
            $i++;
        $lang["auto_grp".$i] = $name;

        $this->db->query("INSERT INTO mwc_group (g_name) VALUES('auto_grp$i')");

        $content = '';
        $ai = new ArrayIterator($lang);
        foreach ($ai as $id=>$val)
        {
            $content.='$lang["'.$id.'"]="'.$val.'"; ';
        }

        if(!empty($content))
        {
            $h = fopen($path,"w");
            fwrite($h,'<?php '.$content);
            fclose($h);
        }
    }


    /**
     * Список страниц, доступных группе
     * @param INT $gnum номер группы
     * @return array
     */
    public function pageList($gnum)
    {
        $pages = array();
        $q = $this->db->query("SELECT mp.ptitle,ma.aid FROM mwc_pages mp,mwc_access ma WHERE mp.id = ma.pageId AND mp.tbuild='{$_SESSION["mwccfgread"]}' and ma.goupId = $gnum");

        $path = "build".DIRECTORY_SEPARATOR.$_SESSION["mwccfgread"].DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$_SESSION["mwclang"].DIRECTORY_SEPARATOR."titles.php";
        if(file_exists($path))
            require $path;
        else
            $lang = array();

        while ($r = $q->FetchRow())
        {
            if(!empty($lang[$r["ptitle"]]))
                $name_ = $lang[$r["ptitle"]];
            else
                $name_ = $r["ptitle"];

            $pages[$r["aid"]] = $name_;
        }

        return $pages;
    }

    /**
     * Страницы, что не отмечены для группы, как доступные
     * @param int $gnum номер группы
     * @return array
     */
    public function nonpageList($gnum)
    {
        $pages = array();
        $q = $this->db->query("SELECT
  mp.ptitle,
  mp.id
 FROM
  mwc_pages mp
 WHERE
  mp.tbuild='{$_SESSION["mwccfgread"]}'
  AND mp.id NOT IN (SELECT ma.pageId FROM mwc_access ma WHERE ma.goupId = $gnum)");

        $path = "build".DIRECTORY_SEPARATOR.$_SESSION["mwccfgread"].DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$_SESSION["mwclang"].DIRECTORY_SEPARATOR."titles.php";

        if(file_exists($path))
        {
            require $path;
        }
        else
            $lang = array();

        while ($r = $q->FetchRow())
        {
            if(!empty($lang[trim($r["ptitle"])]))
                $name_ = $lang[$r["ptitle"]];
            else
                $name_ = $r["ptitle"];

            $pages[$r["id"]] = $name_;
        }

        return $pages;
    }

    /**
     * добавление новой страницы в правила
     * @param int $group
     * @param int $page
     */
    public function addNewPage($group,$page)
    {
        $this->db->query("INSERT INTO mwc_access (goupId, pageId, server) VALUES($group,$page,{$_SESSION["mwcserver"]})");
    }

    /**
     * удаление страницы из правил
     * @param int $id
     */
    public function dellPage($id)
    {
        $this->db->query("DELETE FROM mwc_access WHERE aid = $id");
    }


}