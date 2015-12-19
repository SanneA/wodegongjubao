<?php

/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 18.09.2015
 *
 **/
class logs extends aController
{

    public function action_index()
    {
        $evList = $this->model->getEvList();
        $evList[0] = "...";

        $filList = $this->model->fileList();
        $filList[-1] = "...";

        $this->view
            ->set(array(
                "evlist" => html_::select($evList,"eventnum",0,'style="width:120px;"'),
                "lilesc" => html_::select($filList,"lilesc",-1,'style="width:120px;"'),
                "datefrom" => date_::intransDate("-1 week"),
                "dateto" => date_::intransDate("+1 week")
            ))
            ->out("main","logs");
    }

    /**
     * контент логов из базы
     */
    public function action_getlist()
    {
        $top = !empty($_POST["topst"]) ? (int)$_POST["topst"] : 100;
        $event = !empty($_POST["eventnum"]) ? (int)$_POST["eventnum"] : NULL;
        $file = !empty($_POST["lilesc"]) && $_POST["lilesc"] != "-1" ? $_POST["lilesc"] : 0;
        $msg = !empty($_POST["inevmsg"]) ? $_POST["inevmsg"] : NULL;

        if(!empty($_POST["dbegin"]) && !empty($_POST["dend"]))
        {
            $begin = date_::intransDate($_POST["dbegin"]);
            $end = date_::intransDate($_POST["dend"]);

            if($begin == "-/-" || $end == "-/-")
                return;
        }
        else
            return;

        $logs = $this->model->showLogsArr($top,$event,$file,$begin,$end,$msg);

        $ai = new ArrayIterator($logs);

        foreach ( $ai as $id=>$val )
        {
            $val["col_createTime"] = date_::transDate($val["col_createTime"],true);

            $this->view
                ->add_dict($val)
                ->set("id",$id+1)
                ->out("center","logs");
        }
    }


    /**
     * логи из файлов
     */
    public function action_fileindex()
    {
        $files = $this->model->getFilelist();
        $files[0] = "...";
        $this->view
            ->set("filelist",html_::select($files,"chosfile",0,"style='width:100px;' onchange='setfile(this.value);'"))
            ->out("filemain","logs");
    }

    /**
     * контент выбранного файла
     */
    public function action_getflist()
    {
        if(isset($_GET["id"]))
        {
            $i = (int)$_GET["id"];
            $ars = $this->model->getFilelist();
            if(!empty($ars[$i]))
            {
                echo $this->model->getFileContent($ars[$i]);
            }
            else
                echo "hello ;)";
        }
    }

}