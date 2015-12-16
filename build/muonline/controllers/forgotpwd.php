<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 16.12.2015
 * восстановление пароля
 **/
class forgotpwd extends muController
{
    protected $postField = array(
        "f_mail"=>array("type"=>"str"),
        "f_login"=>array("type"=>"str")
    );

    public function init()
    {
        $this->configs = Configs::readCfg("register",tbuild);
    }

    public function action_index()
    {
        if(!empty($_REQUEST["f_rename"]))
        {
            if($this->model->valid($_POST["f_mail"],$_POST["f_login"])>0)
            {
                $maincfg = Configs::readCfg("main",tbuild);

                if($maincfg["usemd5"] ==0)
                {
                    $pwd = $this->model->viewPwd($_POST["f_login"]);
                }
                else
                {
                    $pwd = $this->model->getNewPwd($_POST["f_login"],1);
                }

                if($this->configs["useMail"]!=1)
                    echo "<script>alert('password is: $pwd');</script>";
                else
                {
                    $c_mail = Configs::readCfg("mail",tbuild);

                    require "libraries/PHPMailer/PHPMailerAutoload.php";

                    $pop = new POP3();
                    $pop->Authorise($c_mail["mailhost"],$c_mail["mailport"],$c_mail["mailtmout"], $c_mail["mailboxf"], $c_mail["mailpbf"], $c_mail["maildlvl"]);
                    $mail = new PHPMailer();
                    $mail->CharSet="UTF-8";

                    $mail->Host       = $c_mail["mailhost"];
                    $mail->SMTPAuth   = true;
                    $mail->Username   = $c_mail["mailboxf"];
                    $mail->Password   = $c_mail["mailpbf"];

                    $mail->SetFrom($c_mail["mailboxf"], $c_mail["mailnamefrom"]);
                    $mail->AddReplyTo($c_mail["mailboxf"],$c_mail["mailnib"]);
                    $mail->Subject    = "Register";
                    $mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test

                    $this->view
                        ->set("fl_login",$_POST["f_login"])
                        ->set("fl_pwd",$pwd);

                    $mail->MsgHTML($this->view->out("register","mail",2));
                    $mail->AddAddress($_POST['f_mail']);

                    if(!$mail->Send())
                        $this->model->toLog("Mail error:".$mail->ErrorInfo,"register",13);
                    echo "<script>alert('Password was send to your e-mail');</script>";
                }
            }

        }
        $this->view->out("forgotpwd");
    }

}