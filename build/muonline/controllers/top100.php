<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 24.10.2015
 * топ 100 персонажей
 **/
class top100 extends muController
{
    public function action_index()
    {
        $this->view
            ->out("main","top100");
    }

    public function action_all()
    {
        if($this->isCached("top100All")) //кешик
            return;
        $filtr  = self::build();
        $users = $this->model->getChars($filtr);

        if(!empty($users))
        {
            $ai = new ArrayIterator($users);

            foreach($ai as $nom=>$vals)
            {
                $vals["Class"] = Character::_class($vals["Class"]);

                if(empty($vals["guild"]))
                    $vals["guild"] = "-/-";

                if(!empty($vals["gens"]))
                {
                    if ($vals["gens"] == 1)
                        $vals["gens"] = '<img src="'.$this->view->getAdr().'theme/'.$this->view->getVal("theme").'/images/Gens_mark_D.gif" alt="D" title="Duprian">';
                    elseif ($vals["gens"] == 2)
                        $vals["gens"] = '<img src="'.$this->view->getAdr().'theme/'.$this->view->getVal("theme").'/images/Gens_mark_V.gif" alt="V" title="Vanert">';
                }
                else
                    $vals["gens"] = "-/-";

                $this->view
                    ->add_dict($vals)
                    ->set("id",$nom+1)
                    ->out("center","top100");
            }
        }

        $this->view
            ->setFContainer("centerchars",true)
            ->out("top","top100");

        if($this->cacheNeed()) //если нужен кеш
            $this->doCache("top100All");
    }

    public function action_DWSMGM()
    {
        if($this->isCached("top100DWSMGM")) //кешик
            return;
        $filtr  = self::build();
        $users = $this->model->getChars($filtr." and ch.Class in (0,1,2,3)");

        if(!empty($users))
        {
            $ai = new ArrayIterator($users);

            foreach($ai as $nom=>$vals)
            {
                $vals["Class"] = Character::_class($vals["Class"]);

                if(empty($vals["guild"]))
                    $vals["guild"] = "-/-";

                if(!empty($vals["gens"]))
                {
                    if ($vals["gens"] == 1)
                        $vals["gens"] = '<img src="'.$this->view->getAdr().'theme/'.$this->view->getVal("theme").'/images/Gens_mark_D.gif" alt="D" title="Duprian">';
                    elseif ($vals["gens"] == 2)
                        $vals["gens"] = '<img src="'.$this->view->getAdr().'theme/'.$this->view->getVal("theme").'/images/Gens_mark_V.gif" alt="V" title="Vanert">';
                }
                else
                    $vals["gens"] = "-/-";

                $this->view
                    ->add_dict($vals)
                    ->set("id",$nom+1)
                    ->out("center","top100");
            }
        }

        $this->view
            ->setFContainer("centerchars",true)
            ->out("top","top100");

        if($this->cacheNeed()) //если нужен кеш
            $this->doCache("top100DWSMGM");
    }

    public function action_DKBKBM()
    {
        if($this->isCached("top100DKBKBM")) //кешик
            return;

        $filtr  = self::build();
        $users = $this->model->getChars($filtr." and ch.Class in (16,17,18,19)");

        if(!empty($users))
        {
            $ai = new ArrayIterator($users);

            foreach($ai as $nom=>$vals)
            {
                $vals["Class"] = Character::_class($vals["Class"]);

                if(empty($vals["guild"]))
                    $vals["guild"] = "-/-";

                if(!empty($vals["gens"]))
                {
                    if ($vals["gens"] == 1)
                        $vals["gens"] = '<img src="'.$this->view->getAdr().'theme/'.$this->view->getVal("theme").'/images/Gens_mark_D.gif" alt="D" title="Duprian">';
                    elseif ($vals["gens"] == 2)
                        $vals["gens"] = '<img src="'.$this->view->getAdr().'theme/'.$this->view->getVal("theme").'/images/Gens_mark_V.gif" alt="V" title="Vanert">';
                }
                else
                    $vals["gens"] = "-/-";

                $this->view
                    ->add_dict($vals)
                    ->set("id",$nom+1)
                    ->out("center","top100");
            }
        }

        $this->view
            ->setFContainer("centerchars",true)
            ->out("top","top100");

        if($this->cacheNeed()) //если нужен кеш
            $this->doCache("top100DKBKBM");
    }

    public function action_ElfMEHE()
    {
        if($this->isCached("top100ElfMEHE")) //кешик
            return;

        $filtr  = self::build();
        $users = $this->model->getChars($filtr." and ch.Class in (32,33,34,35)");

        if(!empty($users))
        {
            $ai = new ArrayIterator($users);

            foreach($ai as $nom=>$vals)
            {
                $vals["Class"] = Character::_class($vals["Class"]);

                if(empty($vals["guild"]))
                    $vals["guild"] = "-/-";

                if(!empty($vals["gens"]))
                {
                    if ($vals["gens"] == 1)
                        $vals["gens"] = '<img src="'.$this->view->getAdr().'theme/'.$this->view->getVal("theme").'/images/Gens_mark_D.gif" alt="D" title="Duprian">';
                    elseif ($vals["gens"] == 2)
                        $vals["gens"] = '<img src="'.$this->view->getAdr().'theme/'.$this->view->getVal("theme").'/images/Gens_mark_V.gif" alt="V" title="Vanert">';
                }
                else
                    $vals["gens"] = "-/-";

                $this->view
                    ->add_dict($vals)
                    ->set("id",$nom+1)
                    ->out("center","top100");
            }
        }

        $this->view
            ->setFContainer("centerchars",true)
            ->out("top","top100");

        if($this->cacheNeed()) //если нужен кеш
            $this->doCache("top100ElfMEHE");
    }

    public function action_SBSDMS()
    {
        if($this->isCached("top100SBSDMS")) //кешик
            return;

        $filtr  = self::build();
        $users = $this->model->getChars($filtr." and ch.Class in (80,81,82,83)");

        if(!empty($users))
        {
            $ai = new ArrayIterator($users);

            foreach($ai as $nom=>$vals)
            {
                $vals["Class"] = Character::_class($vals["Class"]);

                if(empty($vals["guild"]))
                    $vals["guild"] = "-/-";

                if(!empty($vals["gens"]))
                {
                    if ($vals["gens"] == 1)
                        $vals["gens"] = '<img src="'.$this->view->getAdr().'theme/'.$this->view->getVal("theme").'/images/Gens_mark_D.gif" alt="D" title="Duprian">';
                    elseif ($vals["gens"] == 2)
                        $vals["gens"] = '<img src="'.$this->view->getAdr().'theme/'.$this->view->getVal("theme").'/images/Gens_mark_V.gif" alt="V" title="Vanert">';
                }
                else
                    $vals["gens"] = "-/-";

                $this->view
                    ->add_dict($vals)
                    ->set("id",$nom+1)
                    ->out("center","top100");
            }
        }

        $this->view
            ->setFContainer("centerchars",true)
            ->out("top","top100");

        if($this->cacheNeed()) //если нужен кеш
            $this->doCache("top100SBSDMS");
    }

    public function action_DLLE()
    {
        if($this->isCached("top100DLLE")) //кешик
            return;

        $filtr  = self::build();
        $users = $this->model->getChars($filtr." and ch.Class in (64,65,66)");

        if(!empty($users))
        {
            $ai = new ArrayIterator($users);

            foreach($ai as $nom=>$vals)
            {
                $vals["Class"] = Character::_class($vals["Class"]);

                if(empty($vals["guild"]))
                    $vals["guild"] = "-/-";

                if(!empty($vals["gens"]))
                {
                    if ($vals["gens"] == 1)
                        $vals["gens"] = '<img src="'.$this->view->getAdr().'theme/'.$this->view->getVal("theme").'/images/Gens_mark_D.gif" alt="D" title="Duprian">';
                    elseif ($vals["gens"] == 2)
                        $vals["gens"] = '<img src="'.$this->view->getAdr().'theme/'.$this->view->getVal("theme").'/images/Gens_mark_V.gif" alt="V" title="Vanert">';
                }
                else
                    $vals["gens"] = "-/-";

                $this->view
                    ->add_dict($vals)
                    ->set("id",$nom+1)
                    ->out("center","top100");
            }
        }

        $this->view
            ->setFContainer("centerchars",true)
            ->out("top","top100");

        if($this->cacheNeed()) //если нужен кеш
            $this->doCache("top100DLLE");
    }

    public function action_RFFM()
    {
        if($this->isCached("top100RFFM")) //кешик
            return;

        $filtr  = self::build();
        $users = $this->model->getChars($filtr." and ch.Class in (96,97,98,99)");

        if(!empty($users))
        {
            $ai = new ArrayIterator($users);

            foreach($ai as $nom=>$vals)
            {
                $vals["Class"] = Character::_class($vals["Class"]);

                if(empty($vals["guild"]))
                    $vals["guild"] = "-/-";

                if(!empty($vals["gens"]))
                {
                    if ($vals["gens"] == 1)
                        $vals["gens"] = '<img src="'.$this->view->getAdr().'theme/'.$this->view->getVal("theme").'/images/Gens_mark_D.gif" alt="D" title="Duprian">';
                    elseif ($vals["gens"] == 2)
                        $vals["gens"] = '<img src="'.$this->view->getAdr().'theme/'.$this->view->getVal("theme").'/images/Gens_mark_V.gif" alt="V" title="Vanert">';
                }
                else
                    $vals["gens"] = "-/-";

                $this->view
                    ->add_dict($vals)
                    ->set("id",$nom+1)
                    ->out("center","top100");
            }
        }

        $this->view
            ->setFContainer("centerchars",true)
            ->out("top","top100");

        if($this->cacheNeed()) //если нужен кеш
            $this->doCache("top100RFFM");
    }

    public function action_MGDM()
    {
        if($this->isCached("top100MGDM")) //кешик
            return;

        $filtr  = self::build();
        $users = $this->model->getChars($filtr." and ch.Class in (48,49,50)");

        if(!empty($users))
        {
            $ai = new ArrayIterator($users);

            foreach($ai as $nom=>$vals)
            {
                $vals["Class"] = Character::_class($vals["Class"]);

                if(empty($vals["guild"]))
                    $vals["guild"] = "-/-";

                if(!empty($vals["gens"]))
                {
                    if ($vals["gens"] == 1)
                        $vals["gens"] = '<img src="'.$this->view->getAdr().'theme/'.$this->view->getVal("theme").'/images/Gens_mark_D.gif" alt="D" title="Duprian">';
                    elseif ($vals["gens"] == 2)
                        $vals["gens"] = '<img src="'.$this->view->getAdr().'theme/'.$this->view->getVal("theme").'/images/Gens_mark_V.gif" alt="V" title="Vanert">';
                }
                else
                    $vals["gens"] = "-/-";

                $this->view
                    ->add_dict($vals)
                    ->set("id",$nom+1)
                    ->out("center","top100");
            }
        }

        $this->view
            ->setFContainer("centerchars",true)
            ->out("top","top100");

        if($this->cacheNeed()) //если нужен кеш
            $this->doCache("top100MGDM");
    }

    /**
     * список спрятанных
     * @return mixed
     */
    protected function build()
    {
        if(!empty($this->configs["hidenicks"]))
        {
            $chars = explode(",",$this->configs["hidenicks"]);
            $filtr = "";
            foreach ($chars as $vals)
            {
                if(!empty($filtr))
                    $filtr.=",";
                $filtr.="'$vals'";
            }

            return " AND ch.Name NOT IN($filtr)";
        }
        return "";
    }
}