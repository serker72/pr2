<?
require_once('NonSet.php');
require_once('abstractgroup.php');

// абстрактная группа
class PayCodeGroup extends AbstractGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='payment_code';
		$this->pagename='view.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
	//список позиций
	public function GetItemsArr($current_id=0){
		$arr=array();
		
		$sql='select * from '.$this->tablename.' order by name asc, id asc';
		
		//echo $sql;
		
		$set=new MysqlSet($sql);
		
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
	
	
}
?>