<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 27.09.2015
 * регистрация
 **/

class register extends muController
{
    public function action_index()
    {
        $this->view->out("register");
    }

    public function action_captcha()
    {
        require('libraries/kapcha/kcaptcha.php');

        if(isset($_REQUEST[session_name()])){
            session_start();
        }
        $captcha = new KCAPTCHA();

        if($_REQUEST[session_name()]){
            $_SESSION['captcha_keystring'] = $captcha->getKeyString();
        }
    }

    public function action_setregister()
    {
        if(!empty($_POST["rlogin"]) && !empty($_POST["rpwd"]) && !empty($_POST["rrpwd"]) && !empty($_POST["rmail"]) && !empty($_POST["rcaptch"]))
        {
            $login = $_POST["rlogin"];
            if(!preg_match("#^[aA-zZ0-9\-_]+$#",$login))
            {
                echo "0::".$this->view->getVal("l_err1");
                return;
            }

            $pwd = $_POST["rpwd"];
            if(!preg_match("#^[aA-zZ0-9\-_]+$#",$pwd))
            {
                echo "0::".$this->view->getVal("l_err2");
                return;
            }
            $rpwd = $_POST["rrpwd"];

            if($pwd != $rpwd)
            {
                echo "0::".$this->view->getVal("l_err3");
                return;
            }


            $email = $_POST["rmail"];
            $cp = $_POST["rcaptch"];

            if($cp != $_SESSION["captcha_keystring"])
            {
                echo "0::".$this->view->getVal("l_err4");
                return;
            }



            if(!$this->model->checkLogin($login))
            {
                echo "0::".$this->view->getVal("l_err5");
                return;
            }

            $this->model->reg($login,$pwd,$email);
            unset($_SESSION["captcha_keystring"]);

            echo "1::".$this->view->getVal("l_err6");
        }
    }
}