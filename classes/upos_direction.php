<?
require_once('abstractitem.php');

//должность по направлению
class UposDirection extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='user_position_direction'; //position - storage
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
	//добавить должности к направлению
	public function AddPositionsToDirArray($dir_id,  $pos){
		$sql='delete from '.$this->tablename.' where direction_id="'.$dir_id.'"';
		$ns=new NonSet($sql);
		
		if(count($pos)>0){
			$sc=array();
			foreach($pos as $v){
				$sc[]='('.$dir_id.', '.$v.')';	
			}
			$ss=join(', ',$sc);
			$sql='insert into '.$this->tablename.' (direction_id,position_id) values '.$ss;
			$ns=new NonSet($sql);
		}
		
		
		
	}
	
	
	
	//добавить направления к должности
	public function AddDirsToPositionArray($position_id,  $dirs){
		$sql='delete from '.$this->tablename.' where position_id="'.$position_id.'"';
		$ns=new NonSet($sql);
		if(count($dirs)>0){
			$sc=array();
			foreach($dirs as $v){
				$sc[]='('.$position_id.', '.$v.')';	
			}
			$ss=join(', ',$sc);
			$sql='insert into '.$this->tablename.' (position_id, direction_id) values '.$ss;
			$ns=new NonSet($sql);
		}
		
	}
	
	
	
	
	//список должностей по айди направления
	public function GetPositionsByDirArr($id){
		$arr=array();
		
		 /*$sql='
		select distinct c.id as id, c.name as name, "1" as is_in  from user_position as c inner join '.$this->tablename.' as bc on c.id=bc.position_id where bc.direction_id="'.$id.'" order by name';*/
		$sql='
		select distinct c.id as id, c.name as name, "1" as is_in  from user_department as c inner join '.$this->tablename.' as bc on c.id=bc.position_id where bc.direction_id="'.$id.'" order by name';
		
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
	
	//список направлений по айди должности
	public function GetDirsByPositionArr($id){
		$arr=array();
		
		$sql='
		select distinct c.id as id, c.name as name, "1" as is_in from pl_direction as c inner join '.$this->tablename.' as bc on c.id=bc.direction_id where bc.position_id="'.$id.'" order by name';
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
	
	
	//список должностей по айди направления
	public function GetAllPositionsByDirArr($id){
		$arr=array();
			
		
		$sql='select 
				distinct c.id as id, c.name as name,  IF(ct.id is not null,1,0) as is_in
			from user_position as c 
			left join '.$this->tablename.' as ct on ct.position_id=c.id and ct.direction_id="'.$id.'" 
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
	
	
	//список направлений по айди должности
	public function GetAllDirsByPostionArr($id){
		$arr=array();
		
		
		$sql='select 
				distinct c.id as id, c.name as name,   IF(ct.id is not null,1,0) as is_in 
			from pl_direction as c 
			left join '.$this->tablename.' as ct on ct.direction_id=c.id and ct.position_id="'.$id.'" 
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