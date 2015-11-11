<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 17.10.2015
 *
 **/
class m_freepoints extends MuonlineUser
{
    /**
     * применяем разброс поинтов
     * @param array $params
     * @throws ADODB_Exception
     * @throws Exception
     */
    public  function applyPoints($params)
    {
        $character = self::chracterInfo($params["char"].$_SESSION["mwcuser"]);
        if($character["Class"]<=50)
        {
            $params["Leadership"] = 0;
        }
        $this->db->query("UPDATE  Character SET
Leadership = Leadership + {$params["Leadership"]},
Energy = Energy + {$params["Energy"]},
Vitality = Vitality + {$params["Vitality"]},
Dexterity = Dexterity + {$params["Dexterity"]},
Strength = Strength + {$params["Strength"]},
LevelUpPoint = LevelUpPoint - {$params["lost"]}
WHERE
Name='{$params["char"]}'
AND AccountID='{$_SESSION["mwcuser"]}'");
        $this->db->SQLog("character {$params["char"]} add lvlup points LevelUpPoint:{$character["LevelUpPoint"]} - Strength:{$params["Strength"]} - Dexterity:{$params["Dexterity"]} - Vitality:{$params["Vitality"]} - Energy:{$params["Energy"]} - Leadership:{$params["Leadership"]} = LevelUpPoint:{$character["LevelUpPoint"]}",'freepoints',10);
    }

}