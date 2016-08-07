<?

require_once('abstractgroup.php');

// абстрактная группа
class PetitionUserPresetGroup extends AbstractGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='petition_user_preset';
		$this->pagename='view.php';		
		$this->subkeyname='petition_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
	//список позиций
	public function GetItemsArr ( ){
		$arr=Array();
		//$set=new MysqlSet('select * from '.$this->tablename);
		
		$sql='select u.*, up.name as position_name
		from '.$this->tablename.' as t
		inner join user as u on u.id=t.user_id
		left join user_position as up on u.position_id=up.id
		  order by t.ord desc, u.name_s asc, u.id asc';
		
		$set=new MysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
	
	
}
?>