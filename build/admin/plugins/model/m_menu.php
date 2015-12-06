<?php

/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 31.08.2015
 *
 **/
class m_menu extends Menu
{
    /**
     * выбирает из базы менюшку и возвращает ее
     * @return array
     */
    public function getMenu($build="",$menu="")
    {
        return parent::getMenu('admin','adminmenu');
    }
}