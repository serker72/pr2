<?

require_once('abstractgroup.php');
require_once('discr_man.php');


// абстрактная группа
class KpRightsGroup extends AbstractGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='kp_rights';
		$this->pagename='view.php';		
		$this->subkeyname='group_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
	//фильтрующий запрос по оборудованию
	public function GetListSql($result, $inner_right_id=NULL){
		$sql='select id from catalog_position where parent_id=0 ';	
		
		//найти список доступных прав
		$_man=new DiscrMan;
		$avail_rights=array();  $flt='';
		if($inner_right_id!==NULL) $flt=' where right_id='.$inner_right_id;
		$set=new MysqlSet('select * from '.$this->tablename.' '.$flt.' order by id asc');
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			if($_man->CheckAccess($result['id'], 'w', $f['object_id'])){
				//echo 'zzzzzzzzzzzzzzzz';
				
				// $avail_rights[]=$f['object_id'];	
				$rule='(  producer_id="'.$f['producer_id'].'" and (
				group_id="'.$f['parent_group_id'].'"
				or group_id in(select id from catalog_group where parent_group_id="'.$f['parent_group_id'].'")
				or group_id in(select id from catalog_group where parent_group_id in( select id from catalog_group where parent_group_id="'.$f['parent_group_id'].'"))
				)
				
				)';
				
				if(!in_array($rule, $avail_rights)) $avail_rights[]=$rule;
					 
			}
		}
		
		if(count($avail_rights)>0) {
			$sql=$sql.' and ('.implode(' or ',$avail_rights) . ')';	
		}
		else{
			//если нет доступных прав - то вернуть запрос на никакое оборудование
			$sql.=' and id=-1 ';
				
		}
		
		return $sql;
	}
	
	
	//получить список доступного оборудования по определенным правам
	public function GetList($result, $inner_right_id=NULL){
		$sql=$this->GetListSql($result, $inner_right_id);
		$eqs=array();
		$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				$eqs[]=$f['id'];
			}
		
		if(count($eqs)==0) $eqs[]=-1;
		return $eqs;
	}
	
	
	
	//получить список подчиненных менеджеров
	public function GetManagers($result){
		//var_dump($result);
		$managers=array();
		if(eregi('Руководитель отдела', $result['position_name'])){
			
			
			$sql='select id from user where department_id="'.$result['department_id'].'" and id<>"'.$result['id'].'"';
			
			//echo $sql;
			
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				$managers[]=$f['id'];
			}
				
		}
		
		//добавить подчиненных менеджеров у Фурсова - рук-ль группы продаж
		
		//подчииненные менеджеры - через поле Менеджер в их картах
		$_usg=new UsersSGroup;
		$dec1=new DbDecorator;
		$dec1->AddEntry(new SqlEntry('u.manager_id',$result['id'], SqlEntry::E));
		$dec1->AddEntry(new SqlOrdEntry('name_s',SqlOrdEntry::ASC));
		
		$pod=$_usg->GetItemsByDecArr($dec1);
		
		 
		
		//$podd=array();
		foreach($pod as $k=>$v){
			//$podd[]=$v['id'];
			if(!in_array($v['id'], $managers)) $managers[]=$v['id'];
		}
		
		
		
		if(count($managers)==0) $managers[]=-1;
		return $managers;	
	}
	
	
	
	
	
	
	
	
	
	
	//список позиций
	public function GetItemsArr($current_id=0,  $is_shown=0){
		$arr=Array();
		//$set=new MysqlSet('select * from '.$this->tablename);
		if($is_shown==0) $set=new MysqlSet('select * from '.$this->tablename.' order by id asc');
		else $set=new MysqlSet('select * from '.$this->tablename.' where '.$this->vis_name.'="1" order by ord desc, id asc');
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