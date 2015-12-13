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

    public function __construct()
    {
        $this->db = connect::start();
        $this->init();
    }

    /**
     * функция, запускаемая с конструктором
     */
    public function init()
    {

    }

    public function __call($name, $arguments)
    {
        throw new Exception("undefined function $name in ".get_class($this));
    }

    /**
     * запись лога в БД
     * @param string $msg
     * @param string $file
     * @param int $errNo
     * @param bool|true $isValid
     */
    public function toLog($msg,$file="1",$errNo = 0, $isValid = true)
    {
        $this->db->SQLog($msg,$file,$errNo, $isValid);
    }

    /**
     * доступ к бд, для функциональных модулей
     * @return connect|null
     */
    public function getDBins()
    {
        return $this->db;
    }
}