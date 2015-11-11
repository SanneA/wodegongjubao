<?php

/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 14.09.2015
 *
 **/
class m_apman extends Model
{
    public function getlist()
    {
        $q = $this->db->query("SELECT * FROM mwc_plugins WHERE tbuild='{$_SESSION["mwccfgread"]}' order by seq");
        $res = array();

        while ($r = $q->FetchRow())
        {
            $res[] = $r;
        }

        return $res;
    }

    /**
     * информация по плагину по его ид
     * @param int $id
     * @return array
     */
    public function getInfo($id)
    {
        return $this->db->query("SELECT * FROM mwc_plugins WHERE pid=$id")->FetchRow();
    }


    /**
     * возвращает массив с незарегистрированными плагинами
     * @return array
     */
    public function getNonRegPlugins()
    {
        $dir = scandir("build".DIRECTORY_SEPARATOR.$_SESSION["mwccfgread"].DIRECTORY_SEPARATOR."plugins".DIRECTORY_SEPARATOR."controller");

        $curPlugins = array();

        foreach ($dir as $val)
        {
            if($val!="." && $val!="..")
            {
                $bname = basename($val,".php");
                $curPlugins[$bname] = $bname;
            }

        }

        $q = $this->db->query("SELECT pname,pid FROM mwc_plugins WHERE tbuild='{$_SESSION["mwccfgread"]}'");

        while($r = $q->FetchRow())
        {
            if(isset($curPlugins[$r["pname"]]))
                unset($curPlugins[$r["pname"]]);
        }
        return $curPlugins;
    }

    /**
     * Добавление нового плагина
     * @param string $name
     */
    public function addPlugin($name)
    {
        $this->db->query("INSERT INTO mwc_plugins (pname,tbuild)VALUES ('$name','{$_SESSION["mwccfgread"]}')");
    }

    /**
     * обновить данные по плагину
     * @param int $pid
     * @param string $plugin
     * @param int $cache
     * @param int $state
     * @param string $model
     */
    public function applyPlugin($pid,$plugin,$cache,$state,$model,$seq)
    {
        $this->db->query("UPDATE mwc_plugins SET mname = $model, pcache=$cache, pname='$plugin', pstate=$state, seq = $seq WHERE pid = $pid");
    }

    /**
     * получение групп пользователей, что не привязаны к данному плагину
     * @param int $id номер страницы
     * @return array
     */
    public function getGroups($id)
    {
        $q = $this->db->query("SELECT * FROM mwc_group mg WHERE mg.id NOT IN (SELECT ma.col_groupID FROM mwc_pluginsaccess ma WHERE ma.col_pluginID = $id)");
        $retAr = array();
        while($r = $q->FetchRow())
        {
            $retAr[$r["id"]] = $r["g_name"];
        }

        return $retAr;
    }

    /**
     * Возвращает список групп, которые имеют доступ к плагину
     * @param int $id
     * @return array
     */
    public function getInGroups($id)
    {
        $q = $this->db->query("SELECT * FROM mwc_group mg WHERE mg.id IN (SELECT ma.col_groupID FROM mwc_pluginsaccess ma WHERE ma.col_pluginID = $id)");
        $retAr = array();
        while($r = $q->FetchRow())
        {
            $retAr[$r["id"]] = $r["g_name"];
        }

        return $retAr;
    }

    /**
     * Добавить доступ к плагину для группы
     * @param int $plugin
     * @param int $group
     */
    public function setInAccess($plugin,$group)
    {
        $this->db->query("INSERT INTO mwc_pluginsaccess (col_groupID, col_pluginID) VALUES ($group,$plugin)");
    }


    /**
     * удалить группу из доступа к плагину
     * @param int $plugin
     * @param int $group
     */
    public function delGroup($plugin,$group)
    {
        $this->db->query("DELETE FROM mwc_pluginsaccess WHERE col_groupID = $group and  col_pluginID = $plugin");
    }

    /**
     * удалить плагин
     * @param int $id
     */
    public function delPlugin($id)
    {
        $this->db->query("DELETE FROM mwc_pluginsaccess WHERE col_pluginID = $id; DELETE FROM mwc_plugins WHERE pid = $id");
    }

}