<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 10.10.2015
 * управление персонажами
 **/
class editchars extends aController
{
    public function action_index()
    {
        $this->view
            ->out("main","editchars");
    }

    /**
     * поиск персонажа али чара
     */
    public function action_search()
    {
        if(!empty($_POST["typesearch"]) && !empty($_POST["charoracc"]))
        {
            $type = (int)$_POST["typesearch"];
            if($type == 1)
            {
                $charInfo = $this->model->getChar($_POST["charoracc"]);
                if(!empty($charInfo))
                {
                    if($charInfo["ConnectStat"]<1)
                    {
                        $charInfo["ConnectStat"] = "<b style='color:red'>Offline</b>";
                        $charInfo["ServerName"].=" ".Tools::transDate($charInfo["DisConnectTM"],true);
                    }
                    else
                    {
                        $charInfo["ConnectStat"] = "<b style='color:green'>Online</b>";
                        $charInfo["ServerName"].=" ".Tools::transDate($charInfo["ConnectTM"],true);
                    }

                    $this->view
                        ->add_dict($charInfo)
                        ->set("classlist",Tools::htmlSelect($this->model->class,'Class',$charInfo['Class'],"style='width:100px;'"))
                        ->out("character","editchars");
                }
            }
            else
            {
                $account = $this->model->getAccIfo($_POST["charoracc"]);
                if(!empty($account["Characters"]))
                {
                    $chars = "";

                    foreach ($account["Characters"] as $names)
                    {
                        if(!empty($chars))
                            $chars.=", ";
                        $chars.="<span style='cursor: pointer;' onclick=\"document.getElementById('charoracc').value='$names';document.getElementById('typesearch').selectedIndex = 0;searchObject();\">$names</span>";
                    }

                    $account["Characters"] = $chars;
                }

                $this->view
                    ->add_dict($account)
                    ->out("account","editchars");
            }
        }
        else
            echo " 0o?";
    }

    /**
     * сохранить
     */
    public function action_save()
    {
        if(!empty($_POST["typesearch"]) && !empty($_POST["charoracc"]) && !empty($_POST["nm"]) && $_POST["typesearch"]==1)
        {
            if(!empty($_POST["cLevel"]))
                $params["cLevel"] = (int)$_POST["cLevel"];
            else
                return;

            if(!empty($_POST["LevelUpPoint"]))
                $params["LevelUpPoint"] = (int)$_POST["LevelUpPoint"];
            else
                $params["LevelUpPoint"] = 0;

            if(!empty($_POST["Strength"]))
                $params["Strength"] = (int)$_POST["Strength"];
            else
                $params["Strength"] = 0;

            if(!empty($_POST["Dexterity"]))
                $params["Dexterity"] = (int)$_POST["Dexterity"];
            else
                $params["Dexterity"] = 0;

            if(!empty($_POST["Vitality"]))
                $params["Vitality"] = (int)$_POST["Vitality"];
            else
                $params["Vitality"] = 0;

            if(!empty($_POST["Energy"]))
                $params["Energy"] = (int)$_POST["Energy"];
            else
                $params["Energy"] = 0;

            if(!empty($_POST["Leadership"]))
                $params["Leadership"] = (int)$_POST["Leadership"];
            else
                $params["Leadership"] = 0;

            if(!empty($_POST["Money"]))
                $params["Money"] = (int)$_POST["Money"];
            else
                $params["Money"] = 0;

            if(!empty($_POST["MapNumber"]))
                $params["MapNumber"] = (int)$_POST["MapNumber"];
            else
                $params["MapNumber"] = 0;

            if(!empty($_POST["MapPosX"]))
                $params["MapPosX"] = (int)$_POST["MapPosX"];
            else
                $params["MapPosX"] = 0;

            if(!empty($_POST["MapPosY"]))
                $params["MapPosY"] = (int)$_POST["MapPosY"];
            else
                $params["MapPosY"] = 0;

            if($_POST["nm"]!=$_POST["charoracc"])
                return;
            else
                $params["Name"] = $_POST["charoracc"];

            $this->model->saveChar($params);
        }
        elseif(!empty($_POST["typesearch"]) && !empty($_POST["charoracc"]) && !empty($_POST["nm"]) && $_POST["typesearch"]==2)
        {
            if($_POST["charoracc"] == $_POST["nm"])
            {
                $params["account"] = $_POST["charoracc"];
            }
            else
                return;

            if(!empty($_POST["memb_name"]))
                $params["memb_name"] = substr($_POST["memb_name"],0,10);
            else
                return;

            if(!empty($_POST["mail_addr"]))
                $params["mail_addr"] = substr($_POST["mail_addr"],0,50);
            else
                return;

            if(!isset($_POST["bloc_code"]))
                $params["bloc_code"] = 0;
            else
                $params["bloc_code"] = (int)$_POST["bloc_code"];

            if(!isset($_POST["mwc_bankZ"]))
                $params["mwc_bankZ"] = 0;
            else
                $params["mwc_bankZ"] = (int)$_POST["mwc_bankZ"];

            if(!empty($_POST["memb__pwd"]))
                $params["memb__pwd"] = substr($_POST["memb__pwd"],0,10);

            if(!isset($_POST["mwc_credits"]))
                $params["mwc_credits"] = 0;
            else
                $params["mwc_credits"] = (int)$_POST["mwc_credits"];

            $this->model->saveAcc($params);
        }
    }
}