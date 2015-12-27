<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 03.10.2015
 *
 **/
class login extends muPController
{
    //массив с проверяемыми и используемыми полями из POST
    protected $postField = array(
        "username" => array("type"=>"str","maxLength"=>20),
        "password" => array("type"=>"str","maxLength"=>20),
    );


    public function action_index()
    {
        $this->view->add_dict("login");

        if(empty($_SESSION["mwcuser"]) && empty($_SESSION["mwcpwd"])) //неавторизованный
        {
            if(isset($_REQUEST["secure-Btn"]))
            {
                $login = !empty($_POST["username"]) ? $_POST["username"] : NULL;
                $pwd = !empty($_POST["password"]) ? $_POST["password"] : NULL;

                if(!is_null($login) && !is_null($pwd))
                {
                    if($this->model->auth($login,$pwd))
                    {
                        Tools::go($this->view->getAdr()."page/".$this->configs["pageIn"].".html");
                    }
                    else
                    {
                        $_GET["p"] = "error";
                        $_GET["get"] = 8;
                    }

                }
            }

            $this->view
                ->out("login","login");
        }
        else //авторизованный
        {
            $characters = $this->model->getCharacters();
            $characters[-1]="...";

            if(isset($_REQUEST["chosedchar"]) && in_array($_POST["chosedchar"],$characters))
            {
                $_SESSION["mwccharacter"] = $_POST["chosedchar"];
            }

            if(!empty($_SESSION["mwccharacter"]))
                $choosed = $_SESSION["mwccharacter"];
            else
                $choosed = -1;

            $money = $this->model->aboutUser();
            $money["mwc_bankZ"] = Tools::number($money["mwc_bankZ"],0);
            $money["mwc_credits"] = Tools::number($money["mwc_credits"],0);

            $this->view
                ->set("charlist",html_::select($characters,"chosedchar",$choosed,"class='selectbox' onchange='loginarea.submit()'"))
                ->add_dict($money)
                ->out("userPanel","login");

            if(isset($_REQUEST["btn-out"])) //выход
            {
                unset($_SESSION["mwcuser"],$_SESSION["mwcpwd"],$_SESSION["mwcpoints"],$_SESSION["mwccharacter"]);
                Tools::go($this->view->getAdr());
            }
        }
    }
}