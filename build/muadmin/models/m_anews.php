<?php

/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 02.09.2015
 * управление новостями
 **/
class m_anews extends Model
{

    /**
     * возвращает список новостей
     * @param int $num количество новостей
     * @return array
     */
    public function getNhistory($num = 20)
    {
        if($this->db->ConType()<4) //ms sql
            return $this->db->query("SELECT TOP $num ntitle,indate,nid,autothor FROM mwc_news order by indate desc")->GetArray();
        else
            return $this->db->query("SELECT ntitle,indate,nid,autothor FROM mwc_news order by indate desc limit $num")->GetArray();
    }

    /**
     * получить данные по конкретной новости
     * @param  int $nuwsNum новер новости
     * @return array
     */
    public function ninfo($nuwsNum)
    {
        if($this->db->ConType()<4) //ms sql
            return $this->db->query("SELECT nid,autothor, convert(date, indate,120) as indate,  ntitle,ntype,  news,  ntags FROM mwc_news WHERE nid=$nuwsNum")->FetchRow();
            //return $this->db->query("SELECT nid,autothor, convert(date, indate,120) as indate, CAST(ntitle as TEXT) as ntitle,ntype, CAST(news as TEXT) as news, CAST(ntags as TEXT) as ntags FROM mwc_news WHERE nid=$nuwsNum")->FetchRow();
        else
            return $this->db->query("SELECT nid,autothor,indate,ntitle,ntype,news,ntags FROM mwc_news WHERE nid=$nuwsNum")->FetchRow();

    }

    /**
     * применить изменения к новости
     * @param array $news
     */
    public function apply($news)
    {
        if(!empty($news["ntag"]))
        {
            $news["ntag"] = "ntags=N'{$news["ntag"]}',";
        }
        else
            $news["ntag"]="";
        $this->db->query("UPDATE mwc_news SET ntitle = N'{$news["ntitlez"]}',{$news["ntag"]} news=N'{$news["newsinput"]}' WHERE nid={$news["id"]}");
    }

    /**
     * добавить новость
     * @param array $news
     */
    public function addnews($news)
    {
        if(!empty($news["ntag"]))
        {
            $news["ntag"] = "N'{$news["ntag"]}'";
        }
        else
            $news["ntag"]="' '";

        $this->db->query("INSERT INTO mwc_news (autothor,  ntitle, news, ntags) VALUES('{$news["autor"]}',N'{$news["ntitlez"]}',N'{$news["newsinput"]}',{$news["ntag"]})");
    }

    /**
     * удаление новости
     */
    public function delnews($id)
    {
        $this->db->query("DELETE FROM mwc_news WHERE nid=$id");
    }

}