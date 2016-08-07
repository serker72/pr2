<?

require_once('abstractgroup.php');

//  
class PetitionClientGroup extends AbstractGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='petition_client';
		$this->pagename='view.php';		
		$this->subkeyname='petition_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
	//список позиций
	public function GetItemsArr($current_id=0,  $is_shown=0){
		$arr=Array();
		//$set=new MysqlSet('select * from '.$this->tablename);
		if($is_shown==0) $set=new MysqlSet('select * from '.$this->tablename.' order by id asc');
		else $set=new MysqlSet('select * from '.$this->tablename.' where '.$this->vis_name.'="1" order by  id asc');
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
	
	public function GetItemsByIdArr(
		$id, 
	 
		$can_edit=false, 
		$can_delete=false
		){
		
	 
		$arr=array();
		
		$sql='select p.*, pc.name as purpose_name from '.$this->tablename.' as p left join petition_purpose as pc on p.purpose_id=pc.id where p.'.$this->subkeyname.'="'.$id.'" order by  p.id asc';
		//echo $sql;
		
		$set=new MysqlSet($sql);
		 
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			 
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			 
			 
			
			 
			$f['can_edit']=$can_edit;
			$f['can_delete']=$can_delete;
			
			
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
}
?>