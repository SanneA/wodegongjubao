<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 09.12.2015
 * информация про осаду замка
 **/
class castle extends muController
{
    public function action_index()
    {
        $info = $this->model->getInfo(); //целпяем информацию с базы по поводу вдадельцев замка и даты старты периода
        if(!empty($info))
        {
            $csObj = new CastleSiege($info["SIEGE_START_DATE"],$this->configs); //подцепляем расписание и узнаем текущий статус
            $list = $csObj->getAll();

            if(!empty($list))
            {
                foreach($list as $id=>$value)
                {
                    if($value["checked"]>0)
                        $this->view->set("ischecked","checkedState");
                    else
                        $this->view->set("ischecked","");

                    $this->view
                        ->add_dict($value)
                        ->replace($id,"period")
                        ->out("center",get_class($this));
                }
                $this->view->setFContainer("cslist",true);
            }
        }

        $this->view->out("main",get_class($this));
    }

}