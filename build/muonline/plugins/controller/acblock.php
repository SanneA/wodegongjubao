<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 03.10.2015
 * акции и конкурсы
 **/
class acblock extends muPController
{
    protected $needValid = false; //выключаем валидацию POST & GET
    public function action_index()
    {
        $this->view
            ->out("acblock");
    }

}