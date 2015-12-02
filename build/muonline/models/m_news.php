<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 20.09.2015
 *
 **/
class m_news extends Model
{
    public function getNewsList($cont)
    {
        /*
        $totalnews = $this->db->query("SELECT Count(*) as countz,MIN(nid) as min_id FROM MWC_news")->FetchRow(); //СЃРѕР±РёСЂР°РµРј РѕР±С‰РµРµ РєРѕР»РёС‡РµСЃС‚РІРѕ РЅРѕРІРѕСЃС‚РµР№, Рё СѓР·РЅР°РµРј РјРёРЅРёРјР°Р»СЊРЅС‹Р№ РЅРѕРјРµСЂ РЅРѕРІРѕСЃС‚Рё (РІ СЃР»СѓС‡Р°Рµ СѓРґР°Р»СЏР»РѕРє)
        $arr = Tools::paginate($totalnews["countz"],$cfg_news["vNcount"],$pNew);


        if ($this->db->ConType()<3) //ms sql
        {
            $q = $this->db->query(" WITH CTEResults AS
(
    SELECT autothor, convert(date, indate) as indate, CAST(ntitle as TEXT) as ntitle,ntype, CAST(news as TEXT) as news, ROW_NUMBER() OVER (ORDER BY nid DESC) AS RowNum
    FROM MWC_news
)

SELECT *
FROM CTEResults
WHERE RowNum BETWEEN {$arr["min"]} AND {$arr["max"]};");

        }
        else if ($this->db->ConType()==3) //ms sql
        {
            $q = $this->db->query(" WITH CTEResults AS
(
    SELECT autothor, convert(date, indate) as indate, ntitle,ntype,news, ROW_NUMBER() OVER (ORDER BY nid DESC) AS RowNum
    FROM MWC_news
)

SELECT *
FROM CTEResults
WHERE RowNum BETWEEN {$arr["min"]} AND {$arr["max"]};");

        }
        else
            $q = $this->db->query("SELECT autothor, indate, ntitle, ntype, news FROM MWC_news order by nid desc LIMIT {$arr["min"]},{$cfg_news["vNcount"]}");
*/

        if ($this->db->ConType()<=3) //ms sql
        {
            if($this->db->ConType() == 3)
                $q = $this->db->query("SELECT TOP $cont
autothor,
convert(date, indate,120) as indate,
 ntitle,
 ntype,
 news
 FROM mwce_settings.{$this->db->getSuf()}mwc_news ORDER BY nid DESC");
            else
                $q = $this->db->query("SELECT TOP $cont
autothor,
convert(date, indate,120) as indate,
 CAST(ntitle as TEXT) as ntitle,ntype,
 CAST(news as TEXT) as news
 FROM  mwce_settings.{$this->db->getSuf()}mwc_news ORDER BY nid DESC");
        }
        else
        {
            $q = $this->db->query("SELECT * FROM mwce_settings.{$this->db->getSuf()}mwc_news ORDER by nid desc LIMIT $cont");
        }

        $newsArray = array();

        while ($r = $q->FetchRow())
        {
            $newsArray[] = $r;
        }

        return $newsArray;

    }

}