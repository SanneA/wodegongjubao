<?php

/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 04.12.2015
 * загрузки
 **/
class downloads extends muController
{
    protected $needValid = false; //нечего тут проверять
    
    public function action_index()
    {
        $list = $this->model->getList();

        if(!empty($list))
        {
            $ai = new ArrayIterator($list);
            foreach ($ai as $item) {
                $item["col_desc"] = Tools::unhtmlentities($item["col_desc"]);
                $item["col_address"] = Tools::unhtmlentities($item["col_address"]);
                $item["col_address"] = Tools::linkDec($item["col_address"]);
                $this->view
                    ->add_dict($item)
                    ->out("center",get_class($this));
            }
        }
        $this->view
            ->setFContainer("donsloads",true)
            ->out("main",get_class($this));
    }

}