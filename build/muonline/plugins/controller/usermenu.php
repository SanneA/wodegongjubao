<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 26.09.2015
 *
 **/
class usermenu extends muPController
{
    public function action_index()
    {
        if($this->isCached("usermenu")) //кешик
            return;

        $menu = $this->model->getMenu(tbuild,'usermenu');

        if(!empty($menu))
        {
            $AImenu = new ArrayIterator($menu);
            foreach ($AImenu as $res)
            {
                if(substr($res["link"],0,4) != "http") //если не вбита сторонняя ссылка
                {
                    $res["link"] = $this->view->getAdr().$res["link"];
                }


                $this->view
                    ->set(array("mtitle"=>$res["mtitle"],"link"=>$res["link"]))
                    ->out("center","usermenu");
            }

            $this->view
                ->setFContainer("menucontent",true)
                ->out("usermenu","usermenu");
        }

        if($this->cacheNeed()) //если нужен кеш
            $this->doCache("usermenu");
    }

}