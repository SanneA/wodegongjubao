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
            if (!empty($ar) && isset($ar[4]) && isset($ar[3]) && isset($ar[1]) && strlen(trim($ar[3]))<=10)
            {
                $ar[4] =  str_replace("\n", "", trim($ar[4]));
                $credist = str_replace(array("(votes)","(account)"), array("{$ar[4]}","'{$ar[3]}'"),$configs["mmo_price"]);
                $ar[1] = date_::intransDate($ar[1],true);
                $q.="IF EXISTS (SELECT * FROM [MWC_MMO_TOP] WHERE col_memb_id = '{$ar[3]}')
BEGIN
  IF EXISTS (SELECT * FROM [MWC_MMO_TOP] WHERE col_memb_id = '{$ar[3]}' AND DATEDIFF(HOUR,col_LastVote,'".$ar[1]."')>=1)
  BEGIN
    UPDATE [MWC_MMO_TOP] SET col_votes = col_votes + {$ar[4]},col_LastVote='".$ar[1]."' WHERE col_memb_id = '{$ar[3]}';
    INSERT INTO MWC_logs(col_ErrNum,col_msg,col_mname,col_createTime) VALUES(42,'Account {$ar[3]} add 1 vote from mmo top','vote',GETDATE());
    $credist;
  END
END
ELSE
BEGIN
  INSERT INTO [MWC_MMO_TOP] (col_memb_id, col_LastVote,col_votes) VALUES('{$ar[3]}','".$ar[1]."',{$ar[4]});
  INSERT INTO mwce_settings.dbo.MWC_logs(col_ErrNum,col_msg,col_mname,col_createTime,tbuild) VALUES(15,'Account {$ar[3]} add {$ar[4]} vote from mmo top ','vote',GETDATE(),'".tbuild."');
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