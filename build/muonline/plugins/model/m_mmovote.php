<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 19.12.2015
 *
 **/
class m_mmovote extends MuonlineUser
{
    public function getPrize($array)
    {
        $configs = Configs::readCfg("plugin_mmovote",tbuild);
        $q="";
        $contis = 0;
        $ai = new ArrayIterator($array);
        foreach ($ai as $ar)
        {
            if (!empty($ar) && isset($ar["vote"]) && isset($ar["acc"]) && isset($ar["date"]) && strlen(trim($ar["acc"]))<=10)
            {
                $ar["vote"] =  str_replace("\n", "", trim($ar["vote"]));
                $credist = str_replace(array("(votes)","(account)"), array("{$ar["vote"]}","'{$ar["acc"]}'"),$configs["mmo_price"]);
                $ar["date"] = date_::intransDate($ar["date"],true);
                $q.="IF EXISTS (SELECT * FROM [MWC_MMO_TOP] WHERE col_memb_id = '{$ar["acc"]}')
BEGIN
  IF EXISTS (SELECT * FROM [MWC_MMO_TOP] WHERE col_memb_id = '{$ar["acc"]}' AND DATEDIFF(HOUR,col_LastVote,'".$ar["date"]."')>=1)
  BEGIN
    UPDATE [MWC_MMO_TOP] SET col_votes = col_votes + {$ar["vote"]},col_LastVote='".$ar["date"]."' WHERE col_memb_id = '{$ar["acc"]}';
    INSERT INTO MWC_logs(col_ErrNum,col_msg,col_mname,col_createTime) VALUES(42,'Account {$ar["acc"]} add 1 vote from mmo top','vote',GETDATE());
    $credist;
  END
END
ELSE
BEGIN
  INSERT INTO [MWC_MMO_TOP] (col_memb_id, col_LastVote,col_votes) VALUES('{$ar["acc"]}','".$ar["date"]."',{$ar["vote"]});
  INSERT INTO mwce_settings.dbo.MWC_logs(col_ErrNum,col_msg,col_mname,col_createTime,tbuild) VALUES(15,'Account {$ar["acc"]} add {$ar["vote"]} vote from mmo top ','vote',GETDATE(),'".tbuild."');
  $credist;
END ";
                $contis++;
                if($contis >99)
                {
                    $this->db->query($q);
                    $q="";
                    $contis=0;
                }
            }
        }
        if($contis>0)
            $this->db->query($q);

        $this->db->SQLog("Check mmo top complete... ","mmovote",42);

    }

}