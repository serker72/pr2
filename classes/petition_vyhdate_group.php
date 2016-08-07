<?

require_once('abstractgroup.php');

//  группа дат работы в выходные
class PetitionVyhDateGroup extends AbstractGroup {
	
	static public $weekdays=array(
			0=>'воскресенье',
			1=>'понедельник',
			2=>'вторник',
			3=>'среда',
			4=>'четверг',
			5=>'пятница',
			6=>'суббота',
			
		
		);
	
	//установка всех имен
	protected function init(){
		$this->tablename='petition_vyh_date';
		$this->pagename='view.php';		
		$this->subkeyname='petition_id';	
		$this->vis_name='is_shown';		
		
		
		
		
	}
	
	
	//список позиций
	public function GetItemsArrById($id){
		$arr=Array();
		//$set=new MysqlSet('select * from '.$this->tablename);
		
		$sql='select u.* 
		from '.$this->tablename.' as u
	 
		
		 where '.$this->subkeyname.'="'.$id.'" order by pdate asc,  id asc';
		
		//echo $sql;
		
		$set=new MysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['pdate_unf']=$f['pdate'];
			
			$f['w']=date('w', $f['pdate']);
			$f['w_day']=self::$weekdays[(int)$f['w']];
			
			$f['pdate']=date('d.m.Y', $f['pdate']);
			
			$f['time_from_h']=sprintf("%02d",$f['time_from_h']);
			$f['time_from_m']=sprintf("%02d",$f['time_from_m']);
			$f['time_to_h']=sprintf("%02d",$f['time_to_h']);
			$f['time_to_m']=sprintf("%02d",$f['time_to_m']);
			
			$arr[]=$f;
		}
		
		return $arr;
	}
	
 
	
}
?>