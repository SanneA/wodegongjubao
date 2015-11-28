<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 03.10.2015
 *
 **/
class servertop extends muPController
{
    public function action_index()
    {
        if($this->isCached("servertop")) //кешик
            return;

        //region персонажи
        $players = $this->model->getPlayers($this->configs["topplayers"],$this->configs["hidepers"]);

        if(!empty($players))
        {
            $ai = new ArrayIterator($players);
            foreach ($ai as $pname => $pvals)
            {
                $this->view
                    ->add_dict($pvals)
                    ->out("players","servertop");
            }
            $this->view->setFContainer("playerstop",true);
        }
        //endregion

        //todo: дописать функцию для генерации лого гильдии
        //region гльдии
        $guilds = $this->model->getGuilds($this->configs["topguilds"],$this->configs["hideguilds"]);
        if(!empty($guilds))
        {
            $ai = new ArrayIterator($guilds);
            foreach ($ai as $gname => $gvals)
            {
                $this->view
                    ->add_dict($gvals)
                    ->out("guilds","servertop");
            }
            $this->view->setFContainer("guildtop",true);
        }
        //endregion

        $this->view
            ->out("main","servertop");

        if($this->cacheNeed()) //если нужен кеш
            $this->doCache("servertop");
    }
}