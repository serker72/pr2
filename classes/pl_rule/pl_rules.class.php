<?

 

//  правила опций  
class PlRules extends AbstractGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='pl_rule';
		$this->pagename='pricelist.php';		
		$this->subkeyname='group_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
	//список правил в карте оборудовани€ 
	public function GetRules($parent_id, $template, $is_ajax=false){
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		
		$arr=$this->GetRulesArr($parent_id);
		
		$sm->assign('items', $arr);
		
		$ret=$sm->fetch($template);
	}
	
	public function GetRulesArr($parent_id){
		$arr=array();
		
		
		$sql='select p.*, k.name as kind_name,
			pc.code as pc_code, pc.name as pc_name
		from pl_rule as p 
			left join pl_rule_kind as k on p.kind_id=k.id
			left join catalog_position as pc on p.option_id=pc.id
		where p.parent_id="'.$parent_id.'" order by p.ord desc, p.id desc';
		
		//echo $sql;
		
		
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);	
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			$f['items']=$this->GetItemsArr($f['id']);
			
			$arr[]=$f;
		}
		
		
		
		return $arr;
	}
	
	
	
	//получение массива подчиненных опций правила
	public function GetItemsArr($rule_id){
		$arr=array();
		
		$sql='select p.*,
		pc.code as pc_code, pc.name as pc_name
		 from pl_rule_item as p
		left join catalog_position as pc on p.option_id=pc.id
		
		where p.rule_id="'.$rule_id.'" order by p.id asc';
		
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);	
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			 
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
	
	
	
	//сформировать массив правил дл€ клиентской части
	public function GetRulesCli($parent_id /*id оборудовани€*/){
		$arr=array();
		
		
		$sql='select p.*, k.name as kind_name,
			pc.code as pc_code, pc.name as pc_name
		from pl_rule as p 
			left join pl_rule_kind as k on p.kind_id=k.id
			left join catalog_position as pc on p.option_id=pc.id
		where p.parent_id="'.$parent_id.'" order by p.kind_id asc, p.ord desc';
		
		//echo $sql;
		
		
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);	
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			$f['items']=$this->GetItemsArr($f['id']);
			
			$arr[]=$f;
		}
		
		
		
		return $arr;	
		
	}
	
	
}
?>