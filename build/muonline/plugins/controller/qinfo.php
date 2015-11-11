<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 03.10.2015
 *
 **/
class qinfo extends muPController
{
    public function action_index()
    {
        $total=0;
        $this->view->add_dict("qinfo");

        foreach ($this->configs as $cname=>$vals)
        {
            if(substr($cname,0,6) == "server")
            {
                $params = explode(";",$vals);
                $params[1] = trim(strtolower($params[1]));
                if($params[1]!="online" && $params[1]!="offline")
                    $params[1] = "offline";

                if($params[1] == "offline")
                {
                    $online=0;
                }
                else
                {
                    if($params[2]!="localhost") //если не локальный сервер ТО
                    {
                        $online = (int)file_get_contents("http://".Tools::unhtmlentities($params[2]));//получаем данные онлайна с другого сервера
                    }
                    else
                    {
                        $online = $this->model->knowOnline();
                    }
                }

                $total+=$online;

                $this->view
                    ->set(array(
                        "name" =>$params[0],
                        "qstate"=>$params[1],
                        "onlinecount" =>$online
                    ))
                    ->out("center","plugin_qinfo");
            }
            else
                continue;
        }
        $this->view
            ->setFContainer("qcenter",true)
            ->set("totalonline",$total)
            ->out("main","plugin_qinfo");
    }

}