<?php

/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 28.08.2015
 *
 **/
class m_login extends ausermodel
{
    /**
     * проверка, залогинен ли админ
     *
     * @return bool
     */
    public function isLogged()
    {
        if(!empty($_SESSION["mwcauser"]) && !empty($_SESSION["mwcapwd"]) && !empty($_SESSION["mwcapoints"]))
            return true;
        return false;
    }

}