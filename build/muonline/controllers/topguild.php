<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 30.10.2015
 * топ гильдий
 **/
//
class topguild extends muController
{
    public function action_index()
    {
        $list = $this->model->fetGuildList($this->configs["guildCount"]);

        if(!empty($list))
        {
            $ai = new ArrayIterator($list);
            foreach ($ai as $id=>$vals)
            {
                $vals["Glogo"] = $this->model->GuildLogo($vals["Glogo"],$vals["G_Name"],64,86400);
                if(!empty($vals["alianses"])) //сборка через запятую альянса
                {
                    $aliance = explode(",,",$vals["alianses"]);
                    $vals["alianses"] = implode(",",$aliance);
                }

                if ($vals["isLords"]>0)
                {
                    $vals["img"] = " <img src='".$this->view->getAdr()."theme/imgs/csowner.gif' border='0'>";
                }
                else
                    $vals["img"]="";

                $this->view
                    ->add_dict($vals)
                    ->set("id",$id+1)
                    ->out("center","topguild");
            }
            $this->view->setFContainer("centerguild",true);
        }
        $this->view->out("main","topguild");
    }

}