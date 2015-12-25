<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 25.12.2015
 *
 * сброс статов
 **/
class nullstat extends muController
{
    public function action_index()
    {
        $errHandle =0;

        if(empty($_SESSION["mwccharacter"])) //если не выбран персонаж
        {
            $this->view
                ->replace("l_err_char","msg")
                ->set("disabled","DISABLED");
            $errHandle = 1;
        }
        else if($this->model->isOnline()) //проверяем на онлайн
        {
            $this->view
                ->replace("l_err_online","msg")
                ->set("disabled","DISABLED");
            $errHandle = 1;
        }

        if($errHandle != 1)
        {
            $info = $this->model->aboutUser();

            if($this->configs["needCred"]>$info["mwc_credits"]) //недостаточно кредитов
            {
                $this->view
                    ->replace("l_err_notcred","msg")
                    ->set("disabled","DISABLED");
                $errHandle = 1;
            }



            //нажата кнопка смены статов
            if(isset($_REQUEST["getNulled"]) && $errHandle == 0)
            {
                try{
                    $this->model->getNulled($this->configs["needCred"],$this->configs["needLvl"]);
                    Tools::go($this->view->getAdr()."page/freepoints.html");//загрузка распределения статов в случае удачного сброса
                }
                catch(Exception $e)
                {
                    $this->view
                        ->replace($e->getMessage(),"msg")
                        ->set("disabled","DISABLED");
                }
            }
        }

        $this->view
            ->add_dict($this->configs)
            ->out("main",get_class($this));
    }

}