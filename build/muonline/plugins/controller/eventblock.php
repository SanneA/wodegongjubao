<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 03.10.2015
 *
 **/
class eventblock extends muPController
{
    protected $needValid = false; //выключаем валидацию POST & GET

    public function action_index()
    {
        $this->view
            ->out("eventblock");
    }

}