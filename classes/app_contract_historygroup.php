<?

require_once('abstractgroup.php');
require_once('app_contract_history_filegroup.php');

// абстрактная группа
class AppContractHistoryGroup extends AbstractGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='app_contract_history';
		$this->pagename='app_contracthistory.php';		
		$this->subkeyname='app_contract_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
	public function ShowHistory($order_id, $template, DBDecorator $dec, $from=0, $to_page=ITEMS_PER_PAGE, $can_create_order) {
		$sm=new SmartyAdm;
		
		$ofg=new AppContractHistoryFileGroup;
		
		$sql='select o.*, u.login as login, u.group_id, u.name_s, s.name as last_status
                      from '.$this->tablename.' as o
			left join user as u on o.user_id=u.id	
			left join app_contract_status as s on o.status_id=s.id
		where o.'.$this->subkeyname.'="'.$order_id.'" ';
		
		$sql_count='select count(*) from '.$this->tablename.' as o 
			left join user as u on o.user_id=u.id	
			left join app_contract_status as s on o.status_id=s.id
		where o.'.$this->subkeyname.'="'.$order_id.'" ';
		
		
					 
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$sql.=' and '.$db_flt;
			$sql_count.=' and '.$db_flt;	
		}
		
		$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}
		
                //echo '<p>'.$sql.'</p>';
		//echo $sql_count;
		
		$set=new mysqlSet($sql,$to_page, $from,$sql_count);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$total=$set->GetResultNumRowsUnf();
		
		
		//page
		$navig = new PageNavigator($this->pagename,$total,$to_page,$from,10,'&'.$dec->GenFltUri());
		$navig->SetFirstParamName('from');
		$navig->setDivWrapperName('alblinks');
		$navig->setPageDisplayDivName('alblinks1');			
		$pages= $navig->GetNavigator();
		
		$alls=array();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['pdate']=date("d.m.Y H:i:s",$f['pdate']);
			
			$f['txt']=nl2br($f['txt']);
			$f['files']=$ofg->GetItemsByIdArr($f['id']);
			//print_r($f);	
			$alls[]=$f;
		}
		
		//заполним шаблон полями
		$current_status='';
		
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			if($v->GetName()=='status_id'){
				 $current_status=$v->GetValue();
			}
			
			
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
		
	
		
		
		$sm->assign('id',$order_id);
		$sm->assign('from',$from);
		$sm->assign('to_page',$to_page);
		$sm->assign('pages',$pages);
		$sm->assign('items',$alls);
		$sm->assign('can_create_order',$can_create_order);
		$sm->assign('can_edit',$can_create_order);
		$sm->assign('has_header', true);
		
		//ссылка для кнопок сортировки
		$link=$dec->GenFltUri();
		$link=$this->pagename.'?'.eregi_replace('&sortmode=[[:digit:]]+','',$link);
		$sm->assign('link',$link);
		
		return $sm->fetch($template);
		
	}
	
	//подсчет новых историй данного заказа
	public function CountHistory($order_id){
		$ts=new mysqlSet('select count(*) from app_contract_history where app_contract_id="'.$order_id.'"');
			
			$trs=$ts->GetResult();
			$g=mysqli_fetch_array($trs);
			
			return $g[0];
	}
	
	//подсчет новых историй данного заказа
	public function CountNew($order_id){
		$ts=new mysqlSet('select count(*) from app_contract_history where app_contract_id="'.$order_id.'" and is_new="1"');
			
			$trs=$ts->GetResult();
			$g=mysqli_fetch_array($trs);
			
			return $g[0];
	}
	
	//подсчет заказов с новыми данными
	public function CountNewOrders($user_id){
		
	}
	
	//пометить прочитанными все изменения
	public function ToggleRead($order_id, $user_id){
		
	}

	//список позиций
	public function GetItemsArr($order_id, $current_id=0,  $is_shown=0){
		$arr = Array();
		//$set = new MysqlSet('select * from '.$this->tablename);
		if($is_shown == 0) $set = new MysqlSet('select * from '.$this->tablename.' where app_contract_id="'.$order_id.'" order by id asc');
		else $set = new MysqlSet('select * from '.$this->tablename.' where '.$this->vis_name.'="1" and app_contract_id="'.$order_id.'" order by ord desc, id asc');
		$rs = $set->GetResult();
		$rc = $set->GetResultNumRows();
		for($i = 0; $i < $rc; $i++){
			$f = mysqli_fetch_array($rs);
			$f['is_current'] = (bool)($f['id'] == $current_id);
			foreach($f as $k => $v) $f[$k] = stripslashes($v);
			$arr[] = $f;
		}
		
		return $arr;
	}
        
}
?>