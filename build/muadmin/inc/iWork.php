<?php
/**
* Класс для чтения атрибутов вещей из Item(kor).txt
* by [p4f] epmak 
* v 1.8
* last update 23.02.13
**/


#region Описание
# Класс предназначен для чтения атрибутов из базы вещей для MuOnline
# подходит для серверов до появления Rage Fighter (s6)
# Читает файл типа Item(kor).txt/itemsettyoe.txt/itemsetoption.txt/skill.txt/itemaddoption.txt и возвращает ассоциативный массив с информацией по каждой вещи. 
# Как применять:
/*
include "<путь_до_файла_класса>";
$robj = new readitem("Item.txt"); //читаем базу
print "<pre>";
print_r($robj->item); //переменная содержит полученные данные
print "</pre>";
*/
# Формат возвращаеммого массива:
/*      [Weapons]
[index] => 0       Index    
[slot] => 0        Slot    
[skill] => 0       Skill (name or number)       
[x] => 1           X    
[y] => 2           Y    
[serial] => 1      Serial  
[option] => 1      Option   
[drop] => 1        Drop      
[level] => 6       Level  
[dMin] => 6        MinDmg             
[dMax] => 11       MaxDmg             
[speed] => 50      AttSpeed             
[dur] => 20        Dur   
[mdur] => 0        MagDur            
[mpow] => 0        MagPower
[lreq] => 0        LvlReq  
[sreq] => 40       StrReq  
[areq] => 40       AgiReq  
[ereq] => 0        EneReq  
[vreq] => 0        VitReq  
[creq] => 0        CmdReq 
[sattr] => 1       SetAttr  
[dw] => 1          DW/SM   
[dk] => 1          DK/BK   
[elf] => 1         ELF/ME    
[mg] => 1          MG   
[dl] => 1          DL   
[sum] => 1         SUM 
[name] => Kris     Name         
[group] => 0       Item Group
[pvp] = >x|y       pvp option
[anc] = > x|y      ancient set
*/
/*
skill[номер скилла]
name название
rLevel	уровень, с которого доступен
damage  максимальный урон
mana - сколько маны тратится
BP		=	Agility Gauge Usage (Requirement)
rEnergy - сколько ене нужно
rLeader - сколько цмд нужно
*/
#endregion

/*
директива для медленных серверов
*/
//ini_set("max_execution_time","60");
#region claas readitem
class readitem
{
#region переменные
	var $item; //массив с данными о вещах

	
	private $firstS = -1; //идентификатор для корректного  отображения вещей при парсинге игцн (ничего более умного пока не придумал)

	private $skill;	//база скиллов
	private $pvpBase; //пвп 
	private $pvpOpt = array(1=>"Attack Success Rate increase",
	2=>"Additional Damage",
	3=>"Defense Success Rate increase",
	4=>"Defensive Skill",
	5=>"Max HP increase",
	6=>"Max SD increase",
	7=>"SD Auto Recovery",
	8=>"SD Recovery Rate increase");
	
	private $ancbase = array(0 =>"Increase Strengh",
	1 =>"Increase Vitality",
	2 =>"Increase Energy",
	3 => "Increase Agility",
	5 => "Increase minimum attack damage",
	6 => "Increase maximum attack damage",
	7 => "Increase magic damage",
	8 => "Increase Damage",
	9 => "Increase attack successfull rate",
	10 => "Increase defencive skill",
	11 => "Increase maximum life",
	12 => "Increase maximum mana",
	13 => "Increase maximum AG",
	14 => "Increase AG",
	15 => "Critical damage rate",
	16 => "Increase critical damage",
	17 => "Excellent damage rate",
	18 => "Increase excellent damage",
	19 => "Increase skill damage",
	20 => "Double damage rate",
	21 => "Ignore enermy's defense",
	22 => "Increase shield's defence",
	23 => "Increase Damage When using two handed weapons");
	protected $isoA; //массив с названиями сетов
	protected $set; //анциент

	#region "базы" атрибутов для гупп вещей
	protected $gW = array("index", "slot", "skill","x","y","serial","option","drop","name","level","dMin","dMax","speed","dur","mdur","mpow","lreq","sreq","areq","ereq","vreq","creq","sattr","dw","dk","elf","mg","dl","sum","rf");//weap
	protected $gA = array("index","slot","skill","x","y","serial","option","drop","name","level","def","block","dur","lreq","sreq","areq","ereq","vreq","creq","sattr","dw","dk","elf","mg","dl","sum","rf"); //arm
	protected $gWn = array("index","slot","skill","x","y","serial","option","drop","name","level","def","dur","lreq","ereq","sreq","areq","creq","buymoney","dw","dk","elf","mg","dl","sum","rf");//12 ICGN
	//protected $gWn = array("index","slot","skill","x","y","serial","option","drop","name","level","def","dur","lreq","ereq","sreq","areq","creq","unknown","buymoney","dw","dk","elf","mg","dl","sum","rf");//12
	protected $gP = array("index","slot","skill","x","y","serial","option","drop","name","level","dur","lreq","ereq","sreq","areq","vreq","creq","Res7","sattr","dw","dk","elf","mg","dl","sum","rf");//13
	protected $gJ = array("index","slot","skill","x","y","serial","option","drop","name","value","level");//14
	protected $gS = array("index","slot","skill","x","y","serial","option","drop","name","level","lreq","ereq","BuyMoney","dw","dk","elf","mg","dl","sum","rf");//15
	#endregion
#endregion
	
	
	/**
	* Конструктор класса
	* @file - адрес до файла с вещами
	**/
	function __construct($file,$spath="",$iso="",$ist="",$pvp="")
	{
		
		//region Skills
		if ($spath)
		$this->readSkill($spath);
		//endregion
		
		#region ancient
		if ($iso && $ist)
		$this->readAncb($iso,$ist);
		#endregion
		
		#region pvp
		if ($pvp)
		$this->readPvp($pvp);
		#endregion
		
		if (file_exists($file))
		{
			$dataAr = file($file);
			$i=0;
			$iter = new ArrayIterator($dataAr);
            $cGroup =0; //группа вещи
			
			foreach($iter as $id=>$value)
			{
				$value = trim($value);
				if (substr($value,0,2)!="//" && strlen($value)>0 && $value!="end")
				{
					if (strlen($value)>0 && strlen($value)<3)
                        $cGroup = $value;
					else
					{						
						switch ($cGroup)
						{
						case 0:
						case 1:
						case 2:
						case 3:
						case 4:
						case 5: $this->item[$i]= self::readWeapons($value); break;
						case 6:
						case 7:
						case 8:
						case 9:
						case 10:
						case 11: $this->item[$i]= self::readArmors($value); break;
						case 12: $this->item[$i]= self::read12($value); break;
						case 13: $this->item[$i]= self::read13($value); break;
						case 14: $this->item[$i]= self::read14($value); break;
						case 15: $this->item[$i]= self::readSkiils($value); break;
						default: $this->item[$i]["error"] = "Unknown item Group";break;
						}
						
						$this->item[$i]["group"]= $cGroup; //указываем группу вещи
						$this->item[$i]["pvp"]= self::knowPvp($this->item[$i]["group"],$this->item[$i]["index"]); //pvp
						$this->item[$i]["anc"]= self::readAnc($this->item[$i]["group"],$this->item[$i]["index"]); //узнаем анц итемы
						$i++;
					}
				}
			}
		}
		else
		$this->item["error"]="Can't found $file";
		
	}
	/**
	* Читаем pvp опции
	* @pvp(string) адрес до itemaddoption
	**/
	protected function readPvp($pvp)
	{
        $dataAr = self::readFile($pvp);
        if (is_array($dataAr))
		{
			$da = new ArrayIterator($dataAr);
			foreach($da as $id=>$value)
			{
				$value = trim($value);
				if (substr($value,0,2)!="//" && strlen($value)>0 && $value!="end")
				{
					unset($temp);
					$temp = self::getReq($value);
					if (isset($temp[2]) && isset($this->pvpOpt[$temp[2]]))
					{
						$this->pvpBase[$temp[0]][$temp[1]][0] = $this->pvpOpt[$temp[2]]." + ".$temp[3];
						if(!isset($temp[4]) || !isset($this->pvpOpt[$temp[4]]))
							continue;
						if(!isset($temp[5]))
							$temp[5] = 0;
						$this->pvpBase[$temp[0]][$temp[1]][1] = $this->pvpOpt[$temp[4]]." + ".$temp[5];
					}
				}
			}
		}
	}
	
	/**
	* Читаем anc вещи
	* @iso(string) адрес до itemsetoption
	* @ist(string) адрес до itemsettype
	**/
	protected function readAncb($iso,$ist)
	{
        $dataAr = self::readFile($iso);
		if (is_array($dataAr))
        {
			$iter = new ArrayIterator($dataAr);
			
			foreach($iter as $id=>$value)
			{
				$value = trim($value);
				if (substr($value,0,2)!="//" && strlen($value)>0 && $value!="end")
				{
					unset($temp);
					$temp = self::getReq($value);
					if (strlen(substr($temp[1],1,-1))>3)
					{
						$this->isoA[$temp[0]]["name"]=substr($temp[1],1,-1);
						$c = count($temp);
						$op=0;
						for ($i=2;$i<39;)
						{
							if (isset($temp[$i]) && isset($this->ancbase[$temp[$i]]) && $this->ancbase[$temp[$i]]>-1)
							{
								$this->isoA[$temp[0]]["options"][$op]=$this->ancbase[$temp[$i]]." +".$temp[$i+1];	
								$op++;
							}
							if ($i+1==25) $i = 30;
							if ($i>=38) break;//узнали все опции и сваливаем
							$i+=2;
						}
					}
				}
			}
			unset ($dataAr);

            $dataAr = self::readFile($ist);
			$iter1 = new ArrayIterator($dataAr);
            $cGroup=0;
			foreach($iter1 as $id=>$value)
			{
				$value = trim($value);
				if (substr($value,0,2)!="//" && strlen($value)>0 && $value!="end")
				{
					if (strlen($value)>0 && strlen($value)<3)
					$cGroup = $value;
					else
					{
						unset($temp);
						$temp = self::getReq($value);
						$this->set[$cGroup][$temp[0]]["5"]["id"]= $temp[1];
						if(!empty($this->isoA[$temp[1]]["name"]))
							$this->set[$cGroup][$temp[0]]["5"]["name"]= $this->isoA[$temp[1]]["name"];
						else
							$this->set[$cGroup][$temp[0]]["5"]["name"]= "no";

						$this->set[$cGroup][$temp[0]]["A"]["id"]= $temp[2];
						if(!empty($this->isoA[$temp[2]]["name"]))
							$this->set[$cGroup][$temp[0]]["A"]["name"]= $this->isoA[$temp[2]]["name"];
						else
							$this->set[$cGroup][$temp[0]]["A"]["name"] ="no";
					}
				}
			}
		}
	}
	
	
	/**
	* Читаем базу скиллов с сервера
	* @spath(string) адрес до файла
	**/
	protected function readSkill($spath)
	{
        $dataAr = self::readFile($spath);
		if (is_array($dataAr))
		{

			foreach($dataAr as $id=>$value)
			{
				$value = trim($value);
				if (substr($value,0,2)!="//" && strlen($value)>0)
				{
					unset($temp);
					$temp = self::getReq($value);
					if(empty($temp))
						continue;
					$this->skill[$temp[0]]["name"] = substr($temp[1],1,-1);
					$this->skill[$temp[0]]["rLevel"] =$temp[2];
					$this->skill[$temp[0]]["damage"] =$temp[3];
					$this->skill[$temp[0]]["mana"] =$temp[4];
					$this->skill[$temp[0]]["bp"] =$temp[5];
					$this->skill[$temp[0]]["rEnergy"] =$temp[6];
					$this->skill[$temp[0]]["rLeader"] =$temp[7];
					$this->skill[$temp[0]]["dw"] =$temp[18];
					$this->skill[$temp[0]]["dk"] =$temp[19];
					$this->skill[$temp[0]]["elf"] =$temp[20];
					$this->skill[$temp[0]]["mg"] =$temp[21];
					$this->skill[$temp[0]]["dl"] =$temp[22];
					$this->skill[$temp[0]]["sum"] =$temp[23];
					$this->skill[$temp[0]]["rf"] =$temp[24];
				}
			}
		}
		else $this->skill["error"]="can't read skill file!";
	}
    /**
     * чтение файла
     * @param array|string $file
     * @return array|bool
     */
    protected  function readFile($file)
    {
        if (!is_array($file))
        {
            if (file_exists($file))
            {
                $return = file($file);
                if (is_array($return))
                    return $return;
                return false;
            }
        }
        else
            return $file;
    }


    /**
	* Узнать какие пвп - опции есть на вещи
	* @g(int) группа вещи
	* @id(int) номер в группе
	* <return>(string)"opt1|opt2"</return>
	* <return>(string)""</return>
	**/
	function knowPvp($g,$id)
	{

		$string ="no";
		if (is_array($this->pvpBase))
		{
			if(!isset($this->pvpBase[$g][$id][0]))
				$this->pvpBase[$g][$id][1] = "no";

			if(!isset($this->pvpBase[$g][$id][1]))
				$this->pvpBase[$g][$id][1] = "no";
			if (isset($this->pvpBase[$g][$id][0]))
				$string = $this->pvpBase[$g][$id][0]."|".$this->pvpBase[$g][$id][1];
		}
		return $string;
	}
	
	/**
	* возвращает массив с данными (циверками) 
	* @itmar - строка о вещи из базы
	**/
	function getReq ($itmar,$num=3)
	{
		unset($replaced);
		preg_match_all("/([-]?([0-9]{1,}))|([\"]{1}([A-Za-z0-9\&\(\)']{1,20}([\s]{0,})|[-]?){1,5}[\"]{1})|([\"]{1}([A-Za-z0-9\&\(\)'\\\,.]{1,20}[\"]{1}))/", $itmar, $replaced);
		//preg_match_all("/([-]?([0-9]{1,}))|([\"]{1}((.*)[^\\]+[^\"]+)[\"]{1}))|([\"]{1}([A-Za-z0-9\&\(\)'\\\,.]{1,20}[\"]{1}))/", $itmar, $replaced);

		return $replaced[0];
	}

	public function debug($Var)
	{
		print "<pre>";
		print_r($Var);
		print "</pre>";
	}
	
	/**
	* читаем оружие
	* возвращает название вещи
	* @itmar - строка о вещи из базы
	**/
	function readWeapons($itmar)
	{
		$get = self::getReq ($itmar);
		if ($this->firstS==-1) 
		{
			if (count($get)<30)
			$this->firstS = 0;
			else
			$this->firstS = 1;
		}
        $ar = array();
		
		if (count($get)<2)
		$ar["error"]="Can't read item info! $itmar";
		else
		{
			if ($this->firstS == 0)
			$j=0;
			else
                $j = abs(count($get)-30);

			foreach ($this->gW as $i=>$val)
			{
				$ar[$val] = $get[$j];
				$j++;
			}
			
			if ($ar["skill"]>0 && $this->skill[$ar["skill"]])
			$ar["skill"] = $this->skill[$ar["skill"]]["name"];
		}
		return $ar;
	}
	
	/**
	* читаем амуницию
	* возвращает название вещи
	* @itmar - строка о вещи из базы
	**/
	function readArmors($itmar)
	{
		$get = self::getReq ($itmar);

        $ar = array();
		
		if (count($get)<2)
		$ar["error"]="Can't read item info! $itmar";
		else
		{
			if ($this->firstS == 0)
			$j=0;
			else
			$j = abs(count($get)-1-26);

			foreach ($this->gA as $i=>$val)
			{
				$ar[$val] = $get[$j];
				$j++;
			}
			if ($ar["skill"]>0 && $this->skill[$ar["skill"]])
			$ar["skill"] = $this->skill[$ar["skill"]]["name"];
		}
		return $ar;
	}
	
	/**
	* читаем группу 12 (венги)
	* возвращает название вещи
	* @itmar - строка о вещи из базы
	**/
	function read12($itmar)
	{
		$get = self::getReq ($itmar,7);
		unset($ar);
		
		if (count($get)<2)
		$ar["error"]="Can't read item info! $itmar";
		else
		{
			if ($this->firstS == 0)
			$j=0;
			else
			$j = abs(count($get)-25);

			foreach ($this->gWn as $i=>$val)
			{
				$ar[$val] = $get[$j];
				$j++;
			}
		}

		return $ar;
	}
	
	/**
	* читаем группу 13
	* возвращает название вещи
	* @itmar - строка о вещи из базы
	**/
	function read13($itmar)
	{
		$get = self::getReq ($itmar);
		unset($ar);
		
		if (count($get)<2)
		$ar["error"]="Can't read item info! $itmar";
		else
		{
			if ($this->firstS == 0)
			$j=0;
			else
			$j = abs(count($get)-26);
			
			foreach ($this->gP as $i=>$val)
			{
				if(!isset($get[$j]))
					$get[$j] =0;
				$ar[$val] = $get[$j];
				$j++;
			}
		}
		return $ar;
	}
	
	
	/**
	* читаем группу 14
	* возвращает название вещи
	* @itmar - строка о вещи из базы
	**/
	function read14($itmar)
	{
		$get = self::getReq ($itmar);
		unset($ar);
		
		
		if (count($get)<2)
		$ar["error"]="Can't read item info! $itmar";
		else
		{
			if ($this->firstS == 0)
			$j=0;
			else
			$j = abs(count($get)-11);
			
			foreach ($this->gJ as $i=>$val)
			{
				if(!isset($get[$j]))
					$get[$j] = 0;
				$ar[$val] = $get[$j];
				$j++;
			}
		}
		return $ar;
	}
	
	/**
	* читаем манию и скиллы
	* возвращает название вещи
	* @itmar - строка о вещи из базы
	**/
	function readSkiils($itmar)
	{
		$get = self::getReq ($itmar,6);
		
		if (count($get)<2)
		$ar["error"]="Can't read item info! $itmar";
		else
		{
			if ($this->firstS == 0)
			$j=0;
			else
			$j = abs(count($get)-20);

			
			foreach ($this->gS as $i=>$val)
			{
				$ar[$val] = $get[$j];
				$j++;
			}

		}
		return $ar;
	}
	
	
	/**
	* Узнать в каких анциент сетах состоит вещь
	* @g(int) группа вещи
	* @id(int) номер в группе
	* <return>(string)"set1|set2"</return>
	* <return>(string)"no|no"</return>
	**/
	function readAnc($group,$id,$type=0)
	{
		$string="no|no";
		if (is_array($this->set))
		{
			
			if (!isset($this->set[$group][$id][5]["name"]))
			$string = "no|";
			else
			$string = htmlspecialchars($this->set[$group][$id][5]["name"])."|";
			
			if (!isset($this->set[$group][$id]["A"]["name"]))
			$string.= "no";
			else
			$string.= htmlspecialchars($this->set[$group][$id]["A"]["name"]);
		}
		return $string;
	}
}
#endregion


#region Класс предназначен для создания "базы" вещей на сайт

class createIbase extends readitem
{
	private $iDir;
	private $error = false;
    #region equipment
	private $eq = array("dw" => array(1=> "Dark Wizard",2=> "Soul Master",3=>"Grand Master"),
	"dk" => array(1=> "Dark Knight",2=> "Blade Knight",3=>"Blade Master"),
	"elf" => array(1=> "Elf",2=> "Muse Elf",3=>"Hight Elf"),
	"mg" => array(1=> "Magic Gladiator",2=> "Duel Master",3=> "Duel Master"),
	"dl" => array(1 => "Dark Lord",2 => "Lord Emperor",3 => "Lord Emperor"),
	"sum" => array(1 => "Summoner",2 => "Bloody Summoner",3 => "Dimention Master"),
	"rf" => array(1 => "Rage Fighter",2 => "Fist Master",3 => "Fist Master")
	);
    private $classnums;
#endregion


    /**
	* @itemDir - папка, куда будет создаваться картотака на вещи
	* @file - адрес item(kor) 
	* @spath - адрес skill(kor)
	* @iso - адрес itemsetoption
	* @ist - адрес itemsettype
	* @pvp - адрес itemaddoption
	**/
	function __construct($itemDir,$file,$spath= NULL,$iso=NULL,$ist=NULL,$pvp=NULL)
	{
		$this->iDir = $itemDir;
		#region Skills 
		if (isset($spath))
            $this->readSkill($spath);
		#endregion
		
		#region ancient
		if (isset($iso) && isset($ist))
		{
			$this->readAncb($iso,$ist);
			$this->Writeanc();
		}
		#endregion
		
		#region pvp
		if (isset($pvp))
            $this->readPvp($pvp);
		#endregion
		
		$inquick = "<?php\r\n/**\r\n* File was generated by MWC item generator\r\n**/\r\n";
        $cGroup =0;

        $dataAr = self::readFile($file);
		if (is_array($dataAr))
		{
			$i=0;
			$iter = new ArrayIterator($dataAr);
			foreach($iter as $id=>$value)
			{
				$value = trim($value);
				if (substr($value,0,2)!="//" && strlen($value)>0 && $value!="end")
				{
					
					if (strlen($value)>0 && strlen($value)<3)
                        $cGroup = $value;
					
					else 
					{

						switch ($cGroup)
						{
						case 0:
						case 1:
						case 2:
						case 3:
						case 4:
						case 5: $this->item[$i]= self::readWeapons($value);break;
						case 6:
						case 7:
						case 8:
						case 9:
						case 10:
						case 11: $this->item[$i]= self::readArmors($value); break;
						case 12: $this->item[$i]= self::read12($value); break;
						case 13: $this->item[$i]= self::read13($value); break;
						case 14: $this->item[$i]= self::read14($value); break;
						case 15: $this->item[$i]= self::readSkiils($value); break;
						default: $this->item[$i]["error"] = "Unknown item Group";break;
						}
						
						$this->item[$i]["group"]= $cGroup; //указываем группу вещи
						$this->item[$i]["pvp"]= $this->knowPvp($this->item[$i]["group"],$this->item[$i]["index"]); //pvp
						$this->item[$i]["anc"]= $this->readAnc($this->item[$i]["group"],$this->item[$i]["index"]); //узнаем анц итемы
						self::WriteItem($this->item[$i]);
                        if ($this->item[$i]["index"]>=0 && isset($this->item[$i]["name"]) && strlen($this->item[$i]["name"])>2)
                            $inquick.='$item['.$this->item[$i]["group"].']['.$this->item[$i]["index"].'][0]='.$this->item[$i]["name"].'; $item['.$this->item[$i]["group"].']['.$this->item[$i]["index"].'][1]='.$this->item[$i]["x"].''.$this->item[$i]["y"].';'.chr(10);
						$i++;
					}
				}
			}
			$this->write("items.php",$inquick);
			$this->AncBase();
		}
		else
		{
            $this->item["error"]="Can't found $file";
            $this->error = true;
		}

	}


	
	/**
	* Создает базу для быстрого определения сета на вещь
	**/
	private function AncBase()
	{
		if (is_array($this->set))
		{
			$iter = new RecursiveArrayIterator($this->set);

			$toF="<?php\r\n/**\r\n* Acient Set base for short info\r\n* Generated by MWC[".@date('d-m-Y')."]\r\n**/\r\n";
			foreach($iter as $id=>$value)
			{
				foreach(new RecursiveArrayIterator($value) as $i=>$v)
				{
					if ($this->set[$id][$i][5]["name"])
					$toF.='$anc['.$id.']['.$i.'][5]="'.$this->set[$id][$i][5]["name"].'";';
					if ($this->set[$id][$i]["A"]["name"])
					$toF.='$anc['.$id.']['.$i.']["A"]="'.$this->set[$id][$i]["A"]["name"].'";';
					
					$toF.=chr(10);
				}
			}
			self::write("/anc/base.php",$toF);
			unset($toF);
		}
	}
	
	
	/**
	* Записываем в базу anc сеты, точнее опции по порядку, каждая опция с новой строки, 
	* записываются в _dat/iinfo/anc/<номер_сета>.mwc
	**/
	private function Writeanc()
	{
		$iter = new RecursiveArrayIterator($this->isoA);
		foreach ($iter as $i=>$v)
		{
			$content = htmlspecialchars($v["name"]).chr(10);
			
			foreach (new RecursiveArrayIterator($v["options"]) as $id=>$val)
		    {
			 $content.= htmlspecialchars($val).chr(10);
			}
			$this->write("anc/$i.mwc",$content);
		}
	}
	
	/**
	* получает @chars - массив с параметрами вещи
	* <return>строку с текстом "Can be equipment by <class>|Can be equipment by <class>" или "no", если нет указания на классы</return>
	**/
	private function CanEq($chars)
	{
        $string="";
		$part = "Can be equipment by ";
        $this->classnums ="";
		if (isset($chars["dw"]) && $chars["dw"]>0)
        {
            $string.= $part.$this->eq["dw"][$chars["dw"]]."|";
            $this->classnums.="dw,";
        }
		if (isset($chars["dk"]) &&  $chars["dk"]>0)
        {
            $string.= $part.$this->eq["dk"][$chars["dk"]]."|";
            $this->classnums.="dk,";
        }
		if (isset($chars["elf"]) && $chars["elf"]>0)
        {
            $string.= $part.$this->eq["elf"][$chars["elf"]]."|";
            $this->classnums.="elf,";
        }
		if (isset($chars["mg"]) && $chars["mg"]>0)
        {
            $string.= $part.$this->eq["mg"][$chars["mg"]]."|";
            $this->classnums.="mg,";
        }
		if (isset($chars["dl"]) && $chars["dl"]>0)
        {
            $string.= $part.$this->eq["dl"][$chars["dl"]]."|";
            $this->classnums.="dl,";
        }
		if (isset($chars["sum"]) && $chars["sum"]>0)
        {
            $string.= $part.$this->eq["sum"][$chars["sum"]]."|";
            $this->classnums.= "sum,";
        }
		if (isset($chars["rf"]) && $chars["rf"]>0)
        {
            $string.= $part.$this->eq["rf"][$chars["rf"]];
            $this->classnums.= "rf,";
        }
		if (strlen($string)<2)
            $string = "no";
        else
            $this->classnums = substr($this->classnums,0,-1);
		return $string;
	}
	
	/**
	* Запись в 1 файл 1 вещи 
	* @itar - массив, с описанием вещей
	**/
	function WriteItem($itar)
	{
		switch($itar["group"])
		{
		case 0:
		case 1:
		case 2:
		case 3:
		case 4:
		case 5: $this->WriteWeapon($itar); break;
		case 6:
		case 7:
		case 8:
		case 9:
		case 10:
		case 11: $this->WriteArmor($itar); break;
		case 12: $this->Write12($itar); break;
		case 13: $this->Write13($itar); break;
		case 14: $this->Write14($itar); break;
		case 15: $this->WriteScrolls($itar); break;
		}
	}
	
	/**
	* Создаем файл для 1 вещи(оружия)
	* в папке $iDir с расширением mwc
	**/
	private function WriteWeapon($war)
	{
   /* формата
	0 name
	1 x
	2 y
    3 itemdroplvl
	4 dmin
	5 dmax
    6 str
    7 agil
    8 ene
    9 vit
    10 com
    11 durability
	12 speed
	13 requipment by
	14 wizardy
	15 pvp
	16 anc
    17 skill
	*/
        if (!isset($war["skill"]))
            $war["skill"] = "no";
		$content = substr($war["name"],1,-1).chr(10);
		$content.= $war["x"].chr(10);
		$content.= $war["y"].chr(10);
		$content.= $war["level"].chr(10);
		$content.= $war["dMin"].chr(10);
		$content.= $war["dMax"].chr(10);
		$content.= $war["sreq"].chr(10);
		$content.= $war["areq"].chr(10);
		$content.= $war["ereq"].chr(10);
		$content.= $war["vreq"].chr(10);
		$content.= $war["creq"].chr(10);
        if ($war["mdur"]>0)
            $content.= $war["mdur"].chr(10);
        else
            $content.= $war["dur"].chr(10);
		$content.= $war["speed"].chr(10);
		$content.= $this->CanEq($war).chr(10);
		$content.= $war["mpow"].chr(10);
		$content.= $war["pvp"].chr(10);
		$content.= $war["anc"].chr(10);
		$content.= $war["skill"].chr(10);
		$content.= $this->classnums;

		if ($content!="")
		$this->write($war["group"].".".$war["index"].".mwc",$content);
	}
	
	private function WriteArmor($war)
	{
		/* 0-name
		1-x
		2-y
		3-def
		4-block(speed on gloves)
		5-equip
		6-pvp
		7-anc
		8-droplvl
		9-str
		10 - agi
		11 - def
		12- skill
		*/
		$content = substr($war["name"],1,-1).chr(10);
		$content.= $war["x"].chr(10);
		$content.= $war["y"].chr(10);
		$content.= $war["def"].chr(10);
		$content.= $war["block"].chr(10);
		$content.= $this->CanEq($war).chr(10);
		$content.= $war["pvp"].chr(10);
		$content.= $war["anc"].chr(10);
		$content.= $war["level"].chr(10);
		$content.= $war["sreq"].chr(10);
		$content.= $war["areq"].chr(10);
		$content.= $war["dur"].chr(10);
        $content.= $war["skill"].chr(10);
        $content.= $this->classnums;
		if ($content!="")
		$this->write($war["group"].".".$war["index"].".mwc",$content);
	}
	
	private function Write12($war)
	{
		/* 0-name
		1-x
		2-y
		3-def
		4-equip
		5-pvp
		6-anc
		7 - skill
		8 - level
		9 - def
		10 -dur
		11 -"lreq"
		12 -"ereq"
		13 - "sreq",
		14 - "areq"
		15- зарезервировано под вписание руками html - код
		*/
		$content = substr($war["name"],1,-1).chr(10);
		$content.= $war["x"].chr(10);
		$content.= $war["y"].chr(10);
		$content.= $war["def"].chr(10);
		$content.= $this->CanEq($war).chr(10);
		$content.= $war["pvp"].chr(10);
		$content.= $war["anc"].chr(10);
		$content.= $war["skill"].chr(10);
		$content.= $war["level"].chr(10);
		$content.= $war["def"].chr(10);
		$content.= $war["dur"].chr(10);
		$content.= $war["lreq"].chr(10);
		$content.= $war["ereq"].chr(10);
		$content.= $war["ereq"].chr(10);
		$content.= $war["sreq"].chr(10);
		$content.= $war["areq"].chr(10);
        $content.= $this->classnums;

		if ($content!="")
		$this->write($war["group"].".".$war["index"].".mwc",$content);
	}
	
	private function Write13($war)
	{
		/* 0-name
		1-x
		2-y
		3-def
		4-equip
		5-pvp
		6-anc
		7-dur
		*/
		$content = substr($war["name"],1,-1).chr(10);
		$content.= $war["x"].chr(10);
		$content.= $war["y"].chr(10);
        if(!isset($war["def"]))
            $war["def"]=0;
		$content.= $war["def"].chr(10);
		$content.= $this->CanEq($war).chr(10);
		$content.= $war["pvp"].chr(10);
		$content.= $war["anc"].chr(10);
		$content.= $war["dur"].chr(10);
        $content.= $this->classnums;

		if ($content!="")
		$this->write($war["group"].".".$war["index"].".mwc",$content);
	}
	
	private function Write14($war)
	{
		/* 0-name
		1-x
		2-y
		3-equip
		*/
		$content = substr($war["name"],1,-1).chr(10);
		$content.= $war["x"].chr(10);
		$content.= $war["y"].chr(10);
		$content.= $this->CanEq($war).chr(10);
        $content.= $this->classnums;

		if ($content!="")
		$this->write($war["group"].".".$war["index"].".mwc",$content);
	}
	
	private function WriteScrolls($war)
	{
		/* 0-name
		1-x
		2-y
		3-equip
		4-level
		5-ene
		*/
		$content = substr($war["name"],1,-1).chr(10);
		$content.= $war["x"].chr(10);
		$content.= $war["y"].chr(10);
		$content.= $this->CanEq($war);
		$content.= $war["lreq"].chr(10);
		$content.= $war["ereq"].chr(10);
        $content.= $this->classnums;

		if ($content!="")
		$this->write($war["group"].".".$war["index"].".mwc",$content);
	}	
	
	private function write($fname,$content)
	{
		$h = fopen ($this->iDir."/".$fname,"w");
		fwrite($h,$content);
		fclose($h);
	}
	
}
#endregion


#region Класс для парсинга хармони опций (JewelOfHarmonyOption.txt)
#region описание
/*
v 1.0
создается массив типа:
$harm[тип*][номер опции]["name"]="Increase the minimum damage";
$harm[тип*][номер опции]["mlevel"]=0; - минимально возможный уровень
$harm[тип*][номер опции][уровнь]["zen"]=100000;  - сколько стоит повесить опцию ? 
$harm[тип*][номер опции][уровень]["req"]=1; - численное значение опции

тип* - бывает 3 вида :
1 - оружие
2 - петы
3 - амуниция
*/
#endregion

class BuildHarmony
{	
	/**
	* @path - путь к файлу для парсинга
	* @towrite - в какую папку класть harmony.php(базу хармони с сервера)
	**/
	function __construct($path,$towrite)
	{

        $dataAr = self::readFile($path);


		if (is_array($dataAr))
		{

			$i=0;
			$toF = "<?php\r\n/**\r\n* Harmony option base\r\n* Generated with MWC 1.6\r\n* Generated date: ".@date("d-m-Y")."\r\n**/\r\n";
			
			foreach($dataAr as $value)
			{
				$value = trim($value);
				if (substr($value,0,2)!="//" && strlen($value)>0 && $value!="end")
				{
					unset($temp);
					$temp = self::getReq($value,7);
					if(count($temp)<3)
					$group = $temp[0];
					else
					{
						$toF.='$harm'."[{$group}][{$temp[0]}][\"name\"]=\"".htmlspecialchars(substr($temp[1],1,-1))."\";\r\n";
						$toF.='$harm'."[{$group}][{$temp[0]}][\"mlevel\"]={$temp[3]};\r\n";
						$level =0;
						for($i = 4;$i<36;$i++)
						{
							if ($i%2==0) //уровень
							{
                                if(empty($temp[$i]))
                                    $temp[$i] = 0;
								$toF.='$harm'."[{$group}][{$temp[0]}][{$level}][\"req\"]={$temp[$i]};\r\n";
								$level++;
							}
							else //зены
                            {
                                if(!isset($temp[$i]) or empty($temp[$i]))
                                    $temp[$i]=0;
								$toF.='$harm'."[{$group}][{$temp[0]}][{$level}][\"zen\"]={$temp[$i]};\r\n";
                            }
							
						}
					}
				}
			}
			$this->write($towrite."/harmony.php",$toF);
		}

	}


    /**
     * чтение файла
     * @param array|string $file
     * @return array|bool
     */
    private function readFile($file)
    {
        if (!is_array($file))
        {
            if (file_exists($file))
            {
                $return = file($file);
                if (is_array($return))
                    return $return;
                return false;
            }
        }
        else
           return $file;
        return false;
    }



	/**
	* возвращает массив с данными (циверками) 
	* @itmar - строка о вещи из базы
	**/
	function getReq ($itmar,$num=3)
	{
		unset($replaced);
		//preg_match_all("/([-]?([0-9]{1,}))|([\"]{1}([A-Za-z0-9\(\),']{1,20}([\s]{0,})|[-]?){1,7}[\"]{1})|([\"]{1}([A-Za-z0-9\(\)'\\\,.]{1,20}[\"]{1}))/", $itmar, $replaced);
		preg_match_all("/([-]?([0-9]{1,}))|([\"]{1}((.*)[^\"]+)[\"]{1})/", $itmar, $replaced);
		return $replaced[0];
	}
	
	private function write($fname,$content)
	{
		$h = fopen ($fname,"w");
		fwrite($h,$content);
		fclose($h);
	}
}
#endregion

//region класс работы с сокетами
class SoketInfo
{
    private $sokets = array();
    private $screen = "";


    public function __construct($pathtofile,$type=0)
    {
        if($type == 0)
            $this->sokets = self::readFile($pathtofile);
        else
            $this->sokets = $pathtofile;
        if($this->sokets != false)
        {
            self::Parse();
        }
    }

    /**
     * Чтение файла
     * @param $file адрес до файла
     * @return array|bool прочитанный файл в виде массива
     */
    protected  function readFile($file)
    {
        if (!is_array($file))
        {
            if (file_exists($file))
            {
                $return = file($file);

                if (is_array($return))
                    return $return;
                return false;
            }
        }
        else
            return $file;
    }

    /**
     * возвращает массив с данными (циверками)
     * @itmar - строка о вещи из базы
     **/
    public function getReq ($itmar)
    {
        $replaced=array();
       // preg_match_all("/( [-] ? ([0-9]{1,}) ) | ([\"]{1}( (.*)[^\"]+ )[\"]{1})/", $itmar, $replaced);
        preg_match_all("/([-]?([0-9]{1,}))| ([\"]{1}([A-Za-z0-9\&\(\)']{1,}([\s]{0,})|[-]?){1,}[\"]{1})|([\"]{1}([A-Za-z0-9\&\(\)\'\\\,.]{1,}[\"]{1})  )/", $itmar, $replaced);

        return $replaced[0];
    }

    /***
     * узнать стихию
     * @param $num немер стихии
     * @return string название
     */
    protected function KnowElements($num)
    {
        switch($num)
        {
            case 0: return "Ground";
            case 1: return "Fire";
            case 2: return "Water";
            case 3: return "Ice";
            case 4: return "Wind";
            case 5: return "Lightning";
            case 6: return "Earth";
            default: return "Unknown";
        }
    }

    /**
     * парсинг данных
     */
    private function Parse()
    {
        $group = 0;
        $i=0;
        $j=0;
        // $mass = array();
        $this->screen="<?php \r\n //Generated with MWC generate tool ".@date('d-m-Y')."\r\n";

        foreach ($this->sokets as $id=>$val)
        {
            $val = trim($val);
            if(substr($val,0,2)!="//")
            {
                $temp = self::getReq($val);
                if(count($temp)==1)
                {
                    $group = $temp[0];
                }
                else if(count($temp)>3)
                {
                    //    группа  ид        стихия   уровень/имя
                    //  $mass[$group][$temp[0]][$temp[1]][0] = htmlspecialchars(substr($temp[3],1,-1));
                    //  $mass[$group][$temp[0]][$temp[1]][1] = $temp[4];
                    //   $mass[$group][$temp[0]][$temp[1]][2] = $temp[5];
                    //   $mass[$group][$temp[0]][$temp[1]][3] = $temp[6];
                    //  $mass[$group][$temp[0]][$temp[1]][4] = $temp[7];
                    //    $mass[$group][$temp[0]][$temp[1]][5] = $temp[8];

                    // $hex = array();
                    for($i=0;$i<5;$i++)
                    {
                        $hex = sprintf("%02X", ($temp[0] + 50*$i));
                        if($hex=="00")
                            $hex="0F";
                        $this->screen.='  $socket['.$group.']["'.$hex.'"]="('.self::KnowElements($temp[1]).' lvl '.($i+1).')'.htmlspecialchars(substr($temp[3],1,-1)).' '.($temp[5+$i]).'";'."\n";
                    }
                }
            }
        }
        $this->screen.='  $socket[0]["FE"]="Empty Socket";'."\n";
        $this->screen.='  $socket[1]["FE"]="Empty Socket";'."\n";
        $this->screen.='  $socket[2]["FE"]="Empty Socket";'."\n";
    }

    /**
     * сохранить полученные данные
     * @param $addres адрес и название файла куда и как сохранить
     */
    public function Save($addres)
    {
        $handle = fopen($addres,"w");
        fwrite($handle,$this->screen);
        fclose($handle);
    }


}
//endregion