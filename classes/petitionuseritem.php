<?
require_once('abstractitem.php');

//абстрактный элемент
class PetitionUserItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='petition_user';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='petition_id';
		
	}
	
	
	
	public function GetUserByPetitionId($id){
		$res=false;
		//$set=new MysqlSet('select * from '.$this->tablename);
		
		$sql='select u.*,  up.name as position_name, t.id as t_id 
		from '.$this->tablename.' as t
		inner join user as u on u.id=t.user_id
		left join user_position as up on u.position_id=up.id
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