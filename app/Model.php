<?php
/**
 * MuWebCloneEngine
 * version: 1.6
 * by epmak
 * 24.08.2015
 *
 **/

class Model
{
    protected $db;


    public function __construct(connect $db)
    {
        $this->db = $db;

        $this->init();
    }

    public function init()
    {

    }

    /**
     * запись лога в БД
     *
     * @param string $msg
     * @param string $file
     * @param int $errNo
     * @param bool|true $isValid
     */
    public function toLog($msg,$file="1",$errNo = 0, $isValid = true)
    {
        $this->db->SQLog($msg,$file,$errNo, $isValid);
    }

    public function getDBins()
    {
        return $this->db;
    }
}