<?php

/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 17.09.2015
 *
 **/
class m_lmanage extends Model
{
    private $fileList = null;


    /**
     * генерация списка файлов языка
     * @return array
     */
    public function getFileList()
    {
        if(!is_null($this->fileList))
            return $this->fileList;

        $path = "build".DIRECTORY_SEPARATOR.$_SESSION["mwccfgread"].DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$_SESSION["mwclang"];

        $ars = scandir($path);

        $ai = new ArrayIterator($ars);
        $files = array();

        foreach ($ai as $val)
        {
             if($val!="." && $val!="..")
                 $files[]=basename($val,".php");
        }
        unset($this->fileList);
        $this->fileList = $files;
        return $files;
    }

}