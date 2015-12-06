 <?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 26.09.2015
 *
 **/
class muonlineMenu extends Model
{

    /**
     * генерация списка меню
     * @return array
     * @throws ADODB_Exception
     * @throws Exception
     */
    public function getMenu($build,$title)
    {
        $q = $this->db->query("Select
        *
        FROM
        mwce_settings.{$this->db->getSuf()}mwc_menu_type mmt,
        mwce_settings.{$this->db->getSuf()}mwc_menu mu
        WHERE
        mu.mtype = mmt.id
        AND mmt.tbuild = '$build' and mmt.ttitle = '$title' order by mu.col_Seq");


        $path = "build".DIRECTORY_SEPARATOR.tbuild.DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$_SESSION["mwclang"].DIRECTORY_SEPARATOR."titles.php";

        if(file_exists($path))
            require $path;
        else
            $lang = array();

        $menu = array();

        while ($r = $q->FetchRow())
        {
            if(!empty($lang[$r["mtitle"]]))
                $r["mtitle"] = $lang[$r["mtitle"]];
            $menu[] = $r;
        }

        return $menu;
    }

}