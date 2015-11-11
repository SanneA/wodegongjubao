<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 17.10.2015
 * свободные поинты
 **/
class freepoints extends muController
{
    public function action_index()
    {
        $characters = $this->model->getCharlist();
        if(!empty($characters))
        {
            $characters[0]="...";

            $this->view
                ->add_dict("freepoints")
                ->set("chlist",Tools::htmlSelect($characters,"charsChoose","0","class='selectbox' onchange=' genIn({element:\"getfreeptcontent\",address:\"".$this->view->getAdr()."pagebg/freepoints/choose/\"+this.value+\".html\"});'"))
                ->out("main","freePoints");
        }
    }

    /**
     * форма с распределением
     */
    public function action_choose()
    {
        if(!empty($_GET["get"]))
        {
            $character = $this->model->chracterInfo(substr($_GET["get"],0,10),$_SESSION["mwcuser"]);

            if($character["Class"]<=50)
            {
                $this->view
                    ->set("notusest","text-decoration:line-through")
                    ->set("notuse","disabled");
            }

            if(!empty($character))
            {
                $this->view
                    ->add_dict($character)
                    ->out("form","freePoints");
            }
        }
    }

    public function action_apply()
    {
        if(empty($_POST["char"]))
            return;
        else
            $params["char"] = substr($_POST["char"],0,10);

        if(!empty($_POST["Strength"]))
            $params["Strength"] = (int) $_POST["Strength"];
        else
            $params["Strength"] = 0;

        if(!empty($_POST["Dexterity"]))
            $params["Dexterity"] = (int) $_POST["Dexterity"];
        else
            $params["Dexterity"] = 0;

        if(!empty($_POST["Vitality"]))
            $params["Vitality"] = (int) $_POST["Vitality"];
        else
            $params["Vitality"] = 0;

        if(!empty($_POST["Energy"]))
            $params["Energy"] = (int) $_POST["Energy"];
        else
            $params["Energy"] = 0;

        if(!empty($_POST["Leadership"]))
            $params["Leadership"] = (int) $_POST["Leadership"];
        else
            $params["Leadership"] = 0;

        $character = $this->model->chracterInfo($params["char"],$_SESSION["mwcuser"]);

        $params["lost"] = $params["Strength"] + $params["Dexterity"] + $params["Vitality"] + $params["Energy"] + $params["Leadership"];

        if(($character["LevelUpPoint"] - $params["lost"]) >=0
        && $params["Strength"]>=0 && $params["Dexterity"]>=0 &&  $params["Vitality"]>=0 && $params["Energy"]>=0 && $params["Leadership"]>=0)
        {
            $this->model->applyPoints($params);
        }

    }

}