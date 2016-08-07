<?

require_once('abstractgroup.php');

// абстрактная группа
class TaskUserGroup extends AbstractGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='task_user';
		$this->pagename='view.php';		
		$this->subkeyname='task_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
	//список позиций
	public function GetItemsArrById($id){
		$arr=Array();
		//$set=new MysqlSet('select * from '.$this->tablename);
		
		$sql='select u.* 
		from '.$this->tablename.' as t
		inner join user as u on u.id=t.user_id
		
		 where '.$this->subkeyname.'="'.$id.'" order by name_s asc, id asc';
		
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