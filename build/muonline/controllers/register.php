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
        if($this->configs["useCaptcha"] == 1 && empty($_POST["rcaptch"])) // если капча вкоючена
        {
            echo "0::".$this->view->getVal("l_err4");
            return;
        }

        if(!empty($_POST["rlogin"]) && !empty($_POST["rpwd"]) && !empty($_POST["rrpwd"]) && !empty($_POST["rmail"]))
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

            if($this->configs["useCaptcha"]!=0)
            {
                $cp = $_POST["rcaptch"];

                if($cp != $_SESSION["captcha_keystring"])
                {
                    echo "0::".$this->view->getVal("l_err4");
                    return;
                }
                unset($_SESSION["captcha_keystring"]);
            }


            if(!$this->model->checkLogin($login))
            {
                echo "0::".$this->view->getVal("l_err5");
                return;
            }

            //l_err7
            if($this->configs["useMail"]==0)
            {
                $this->model->reg($login,$pwd,$email);
                echo "1::".$this->view->getVal("l_err6");
            }
            else
            {
                $id = $this->model->regM($login,$pwd,$email,$this->configs["defGrp"]);
                if($id>0)
                {
                    //region отсылаем почту
                    $hash = md5($id."-=-".$email);
                    $c_mail = Configs::readCfg("mail",tbuild);

                    require "libraries/PHPMailer/PHPMailerAutoload.php";

                    $pop = new POP3();
                    $pop->Authorise($c_mail["mailhost"],$c_mail["mailport"],$c_mail["mailtmout"], $c_mail["mailboxf"], $c_mail["mailpbf"], $c_mail["maildlvl"]);
                    $mail = new PHPMailer();
                    $mail->CharSet="UTF-8";
                    //  $mail->IsSMTP();
                    //$mail->SMTPDebug  = $c_mail["maildlvl"];
                    $mail->Host       = $c_mail["mailhost"];
                    $mail->SMTPAuth   = true;
                    $mail->Username   = $c_mail["mailboxf"];
                    $mail->Password   = $c_mail["mailpbf"];

                    $mail->SetFrom($c_mail["mailboxf"], $c_mail["mailnamefrom"]);
                    $mail->AddReplyTo($c_mail["mailboxf"],$c_mail["mailnib"]);
                    $mail->Subject    = "Register";
                    $mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test

                    $this->view->set("hash",$hash."_".$id);
                    $mail->MsgHTML($this->view->out("register","mail",2));
                    $mail->AddAddress($email);

                    if(!$mail->Send())
                        $this->model->toLog("Mail error:".$mail->ErrorInfo,"register",13);

                    //endregion

                    echo "1::".$this->view->getVal("l_err7");
                }
                else
                    echo "0::".$this->view->getVal("l_err5");
            }

        }
    }

    /**
     * активация аккаунта
     */
    public function action_activate()
    {
        if(!empty($_GET["get"]))
        {
            $params = explode("_",$_GET["get"]);
            if($this->model->activate((int)$params[1],$params[0]))
                Tools::go($this->view->getAdr());
        }
    }
}