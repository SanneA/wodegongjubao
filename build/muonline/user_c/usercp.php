<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 18.10.2015
 * настройки аккаунта
 **/
class usercp extends muController
{
    public function action_index()
    {
        if(isset($_REQUEST["applyusercp"])) //нажата кнопка "применить"
        {
            if(self::applyucp())
            {
                $this->view
                    ->set("resultst","color:green")
                    ->replace("l_usercpok","showmsgusercp");
            }
            else
            {
                $this->view
                    ->set("resultst","color:red")
                    ->replace("l_usercpnot","showmsgusercp");

            }
        }

        $this->view
            ->out("usercp");
    }

    /**
     * применение(или нет?) настроек
     */
    private function applyucp()
    {
        if(empty($_POST["memb_pwd"]))
            return false;
        else
            $params["memb_pwd"] = $_POST["memb_pwd"];

        if(!empty($_POST["memb_name"]))
            $params["memb_name"] = substr($_POST["memb_name"],0,10);
        else
            return false;

        if(!empty($_POST["memb_newpwd"]))
            $params["memb_newpwd"] = substr($_POST["memb_newpwd"],0,10);

        if(!empty($_POST["memb_newrpwd"]))
            $params["memb_newrpwd"] = substr($_POST["memb_newrpwd"],0,10);


        if(!empty($params["memb_newpwd"]) && !empty($params["memb_newrpwd"]) && $params["memb_newpwd"]!=$params["memb_newrpwd"])
        {
            unset($params["memb_newpwd"],$params["memb_newpwd"]);
        }

        $this->model->applyChanges($params);
        return true;
    }
}