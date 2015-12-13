<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 17.10.2015
 *
 **/
class Character
{
    /**
     * название класса по номеру
     * @param int $num
     * @return string
     */
    static function _class($num)
    {
        switch($num)
        {
            case 0: return "Dark Wizard";
            case 1: return  "Soul Master";
            case 2: return  "Grand Master";
            case 3: return  "Grand Master";
            case 16: return  "Dark Knight";
            case 17: return  "Blade Knight";
            case 18: return  "Blade Master";
            case 19: return  "Blade Master";
            case 32: return  "Fairy Elf";
            case 33: return  "Muse Elf";
            case 34: return  "High Elf";
            case 35: return  "High Elf";
            case 48: return  "Magic Gladiator";
            case 49: return  "Duel Master";
            case 50: return  "Duel Master";
            case 64: return  "Dark Lord";
            case 65: return  "Lord Emperor";
            case 66: return  "Lord Emperor";
            case 80: return  "Summoner";
            case 81: return  "Bloody Summoner";
            case 82: return  "Dimension Master";
            case 83: return  "Dimension Master";
            case 96: return  "Rage Fighter";
            case 97: return  "Fist Master";
            case 98: return  "Fist Master";
            default: return "unknown";
        }
    }

    /**
     * возвращает массив с номерами возможных
     * классов для указанного персонажа
     *
     * @param string $name
     * @return array
     */
    static public function getClassNums($name)
    {
        switch(strtolower($name))
        {
            case "wizard":
            case "dw":
                return array(0,1,2,3);
                break;
            case "knight":
            case "dk":
                return array(16,17,18,19);
                break;
            case "elf":
            case "me":
                return array(32,33,34,35);
                break;
            case "gladiator":
            case "mg":
                return array(48,49,50);
                break;
            case "lord":
            case "dl":
                return array(64,65,66);
                break;
            case "summoner":
            case "sum":
                return array(80,81,82,83);
                break;
            case "fighter":
            case "rf":
                return array(96,97,98);
                break;
        }

        return array();
    }

    /**
     * проверка на поддрежку 65к в стате
     * @param $stat
     * @return int
     */
    static function stats65 ($stat){ return $stat = ($stat <0) ? 65535+ $stat : $stat; }
}