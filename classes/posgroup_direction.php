<?
require_once('abstractitem.php');

//��������� �� �����������
class PosGroupDirection extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='catalog_group_direction'; //position - storage
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	//�������� ��������� � �����������
	public function AddGroupsToGroupArray($dir_id,  $groups){
		$sql='delete from '.$this->tablename.' where direction_id="'.$dir_id.'"';
		$ns=new NonSet($sql);
		
		if(count($groups)>0){
			$sc=array();
			foreach($groups as $v){
				$sc[]='('.$dir_id.', '.$v.')';	
			}
			$ss=join(', ',$sc);
			$sql='insert into '.$this->tablename.' (direction_id,group_id) values '.$ss;
			$ns=new NonSet($sql);
		}
		
		
		
	}
	
	
	
	//�������� ����������� � ���������
	public function AddDirsToGroupArray($group_id,  $dirs){
		$sql='delete from '.$this->tablename.' where group_id="'.$group_id.'"';
		$ns=new NonSet($sql);
		if(count($dirs)>0){
			$sc=array();
			foreach($dirs as $v){
				$sc[]='('.$group_id.', '.$v.')';	
			}
			$ss=join(', ',$sc);
			$sql='insert into '.$this->tablename.' (group_id, direction_id) values '.$ss;
			$ns=new NonSet($sql);
		}
		
	}
	
	
	
	
	//������ ��������� �� ���� �����������
	public function GetGroupsByDirArr($id){
		$arr=array();
		
		 $sql='
		select distinct c.id as id, c.name as name, "1" as is_in  from  catalog_group as c inner join '.$this->tablename.' as bc on c.id=bc.group_id where bc.direction_id="'.$id.'" order by name';
		$set=new mysqlSet($sql);
		$tc=$set->GetResultNumRows();
		
		$rs=$set->GetResult();
		for($i=0;$i<$tc;$i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	//������ ����������� �� ���� ���������
	public function GetDirsByGroupArr($id){
		$arr=array();
		
		$sql='
		select distinct c.id as id, c.name as name, "1" as is_in from pl_direction as c inner join '.$this->tablename.' as bc on c.id=bc.direction_id where bc.group_id="'.$id.'" order by name';
		$set=new mysqlSet($sql);
		$tc=$set->GetResultNumRows();
		
		$rs=$set->GetResult();
		for($i=0;$i<$tc;$i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
	//������ ��������� �� ���� �����������
	public function GetAllGroupsByDirArr($id){
		$arr=array();
			
		
		$sql='select 
				distinct c.id as id, c.name as name,  IF(ct.id is not null,1,0) as is_in
			from catalog_group as c 
			left join '.$this->tablename.' as ct on ct.group_id=c.id and ct.direction_id="'.$id.'" 
		order by name';
		
		$set=new mysqlSet($sql);
		$tc=$set->GetResultNumRows();
		
		$rs=$set->GetResult();
		for($i=0;$i<$tc;$i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
	//������ ����������� �� ���� ���������
	public function GetAllDirsByGroupArr($id){
		$arr=array();
		
		
		$sql='select 
				distinct c.id as id, c.name as name,   IF(ct.id is not null,1,0) as is_in 
			from pl_direction as c 
			left join '.$this->tablename.' as ct on ct.direction_id=c.id and ct.group_id="'.$id.'" 
		order by name';
		
		//echo $sql;
		
		$set=new mysqlSet($sql);
		$tc=$set->GetResultNumRows();
		
		$rs=$set->GetResult();
		for($i=0;$i<$tc;$i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$arr[]=$f;
		}
		
		return $arr;
	}
}
?>