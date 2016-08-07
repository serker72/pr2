<?

require_once('abstractgroup.php');

// абстрактная группа
class MissionExpGroup extends AbstractGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='mission_expenses';
		$this->pagename='view.php';		
		$this->subkeyname='mission_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
	//таблица платежей по командировке
	public function GetItemsByIdArr($id, $current_id=0){
		$arr=array();
		//$set=new MysqlSet('select * from '.$this->tablename);
		
		
		$sql='select n.*, t.plan, t.fact from mission_expenses_name as n left join  mission_expenses as t  on t.exp_id=n.id and t.mission_id="'.$id.'" order by n.ord desc, n.id asc';
		
		//echo $sql;
		
		$set=new MysqlSet($sql);
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			$f['plan']=round((float)$f['plan'],2);
			$f['fact']=round((float)$f['fact'],2);
			
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
	
	public function CalcPlan($id){
		$sql='select sum(plan) from '.$this->tablename.' as t where t.'.$this->subkeyname.'="'.$id.'" ';
		
		$set=new MysqlSet($sql);
		
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		return round((float)$f[0],2);
	}
	
	public function CalcFact($id){
		$sql='select sum(fact) from '.$this->tablename.' as t where t.'.$this->subkeyname.'="'.$id.'" ';
		
		$set=new MysqlSet($sql);
		
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		return round((float)$f[0],2);
		
	}
	
}
?>