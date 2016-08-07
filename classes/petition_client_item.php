<?
require_once('abstractitem.php');

// 
class PetitionClientItem extends AbstractItem{
	
	//установка всех имен
	protected function init(){
		$this->tablename='petition_client';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='petition_id';	
	}
	
	
	
	public function GetClientByPetitionId($id){
		$res=false;
		//$set=new MysqlSet('select * from '.$this->tablename);
		
		$sql='select t.* 
		from '.$this->tablename.' as t
	 
		 where '.$this->subkeyname.'="'.$id.'" limit 1';
		
		$set=new MysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		if($rc>0){
			$res=mysqli_fetch_array($rs);
			
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
		}
		return $res;
	}
	
}
?>