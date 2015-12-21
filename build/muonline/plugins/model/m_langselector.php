<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 21.12.2015
 *
 **/
class m_langselector extends MuonlineUser
{
    public function getLangs()
    {
        $dirs = scandir("build/".tbuild."/lang");
        $ret = array();
        foreach($dirs as $vals)
        {
            if($vals=="." || $vals=="..")
                continue;
            $ret[$vals] = $vals;
        }
        return $ret;
    }

}