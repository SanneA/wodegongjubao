<?php

/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 10.09.2015
 *
 **/
class m_ammanager extends Model
{
    /**
     * список страниц
     * @return array
     */
    public function getPageList($folder)
    {
        if(!empty($folder))
        {
            $q = $this->db->query("SELECT id,pname,ptitle,ppath,caching,ison,server,mname,mpath from mwc_pages WHERE tbuild='{$_SESSION["mwccfgread"]}' and ppath = '$folder'");
        }
        else
            $q = $this->db->query("SELECT id,pname,ptitle,ppath,caching,ison,server,mname,mpath from mwc_pages WHERE tbuild='{$_SESSION["mwccfgread"]}'");
        $ars = array();
        while($r = $q->FetchRow())
        {
            $ars[] = $r;
        }

        return $ars;
    }

    /**
     * список всех указанных папок контроллеров
     * @return array
     */
    public function getFolerList()
    {
        $q= $this->db->query("SELECT mp.ppath FROM mwc_pages mp WHERE tbuild = '{$_SESSION["mwccfgread"]}' GROUP BY mp.ppath");
        $return = array();

        while ($r = $q->FetchRow())
        {
            $return[$r["ppath"]] = $r["ppath"];
        }

        return $return;
    }

    /**
     * информация о странице
     * @param int $pid
     * @return array
     */
    public function knowInfo($pid)
    {
        $info = $this->db->query("SELECT id,pname,ptitle,ppath,caching,ison,server,mname,mpath from mwc_pages where id = $pid")->FetchRow();
        return $info;
    }

    public  function applyPage($id,$ptitle,$mname,$ppath,$mmname,$mpath,$cache,$ison)
    {
        $mmname = is_null($mmname) ? "NULL" : "'$mmname'";
        $mpath = is_null($mpath) ? "NULL" : "'$mpath'";
        $this->db->query("UPDATE mwc_pages SET pname='$mname',ptitle='$ptitle',ppath='$ppath',caching = $cache,ison=$ison,mname=$mmname,mpath=$mpath WHERE id = $id");
    }

    /**
     * получение групп пользователей, что не привязаны к данной странице
     * @param int $id номер страницы
     * @return array
     */
    public function getGroups($id)
    {
        $q = $this->db->query("SELECT * FROM mwc_group mg WHERE mg.id NOT IN (SELECT ma.goupId FROM mwc_access ma WHERE ma.pageId = $id)");
        $retAr = array();
        while($r = $q->FetchRow())
        {
            $retAr[$r["id"]] = $r["g_name"];
        }

        return $retAr;
    }

    /**
     * Возвращает список групп, которые имеют доступ к странице
     * @param int $id
     * @return array
     */
    public function getInGroups($id)
    {
        $q = $this->db->query("SELECT * FROM mwc_group mg WHERE mg.id IN (SELECT ma.goupId FROM mwc_access ma WHERE ma.pageId = $id)");
        $retAr = array();
        while($r = $q->FetchRow())
        {
            $retAr[$r["id"]] = $r["g_name"];
        }

        return $retAr;
    }

    /**
     * Добавить доступ к странице для группы
     * @param int $page
     * @param int $group
     */
    public function setInAccess($page,$group)
    {
        $this->db->query("INSERT INTO mwc_access (goupId, pageId, server) VALUES ($group,$page,{$_SESSION["mwcserver"]})");
    }

    /**
     * Добавить доступ к странице для группы
     * @param int $page
     * @param array $group массив с названиями групп
     */
    public function setInGroupAccess($page,$group)
    {
        $this->db->query("INSERT INTO mwc_access (goupId, pageId, server) SELECT id, $page, {$_SESSION["mwcserver"]} FROM mwc_group WHERE g_name IN ($group)");
    }

    /**
     * удалить группу из доступа к странице
     * @param int $page
     * @param int $group
     */
    public function delGroup($page,$group)
    {
        $this->db->query("DELETE FROM mwc_access WHERE goupId = $group and  pageId = $page");
    }

    /**
     * добавить новую страницу
     * @param string $cname
     * @param string $sfolder
     * @param string $title
     * @param string $mname
     * @param string $mfolder
     * @param int $cache
     * @return int последний вставленный id
     */
    public function addNewPage($cname,$sfolder,$title,$mname,$mfolder,$cache)
    {
        $this->db->query("INSERT INTO mwc_pages (pname, ptitle, ppath, caching,ison, server, mname, mpath,tbuild) VALUES ($cname,$title,$sfolder,$cache,1,{$_SESSION["mwcserver"]},$mname,$mfolder,'{$_SESSION["mwccfgread"]}')");

        return $this->db->lastId("mwc_pages");
    }

    /**
     * удаление страницы со всеми разрешениями
     * @param int $id
     */
    public function delPage($id)
    {
        $this->db->query("DELETE FROM mwc_access WHERE pageId = $id; DELETE FROM mwc_pages WHERE id = $id;");
    }

}