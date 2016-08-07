<?

require_once('abstractgroup.php');

// абстрактная группа
class TaskSupplierGroup extends AbstractGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='task_supplier';
		$this->pagename='view.php';		
		$this->subkeyname='task_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
	//список позиций
	public function GetItemsArrById($id){
		$arr=Array();
		//$set=new MysqlSet('select * from '.$this->tablename);
		
		$sql='select u.full_name, opf.name as opf_name, u.id as id 
		from '.$this->tablename.' as t
		inner join supplier as u on u.id=t.supplier_id
		left join opf as opf on u.opf_id=opf.id
		 where '.$this->subkeyname.'="'.$id.'" order by full_name asc, id asc';
		
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