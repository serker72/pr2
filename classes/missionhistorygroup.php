<?

require_once('abstractgroup.php');
require_once('missionfilegroup.php');
require_once('missionitem.php');

// абстрактная группа
class MissionHistoryGroup extends AbstractGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='mission_history';
		$this->pagename='missionhistory.php';		
		$this->subkeyname='mission_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
	public function ShowHistory($order_id, $template, DBDecorator $dec,$from=0,$to_page=ITEMS_PER_PAGE, $can_create_task){
		$sm=new SmartyAdm;
		
		$ofg=new MissionFileGroup;
		
		$sql='select o.*, u.login as login, u.name_s as name_s, s.name as last_status from '.$this->tablename.' as o
				left join user as u on o.user_id=u.id	
				left join mission_status as s on o.status_id=s.id
		 where o.'.$this->subkeyname.'="'.$order_id.'" ';
		
		$sql_count='select count(*) from '.$this->tablename.' as o 
				left join user as u on o.user_id=u.id	
				left join mission_status as s on o.status_id=s.id
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
		//echo $sql;
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
		$sm->assign('can_create_task',$can_create_task);
		
		//ссылка для кнопок сортировки
		$link=$dec->GenFltUri();
		$link=$this->pagename.'?'.eregi_replace('&sortmode=[[:digit:]]+','',$link);
		$sm->assign('link',$link);
		
		return $sm->fetch($template);
		
	}
	
	//подсчет новых историй данного заказа
	public function CountNew($order_id,$user_id){
		$ts=new mysqlSet('select * from mission where id="'.$order_id.'"');
		$trs=$ts->GetResult();
		$trc=$ts->GetResultNumRows();
		if($trc==0) return 0;
		$order=mysqli_fetch_array($trs);
		
		if(($order['user_id']==0)||($order['sent_user_id']==$user_id)){
		
			$ts=new mysqlSet('select count(*) from mission_history where mission_id="'.$order_id.'" and is_new="1" and user_id<>"'.$user_id.'"');
			
			$trs=$ts->GetResult();
			$g=mysqli_fetch_array($trs);
			
			return $g[0];
		}else return 0;
	}
	
	
	
	
	
	
	//подсчет числа заданий с новыми событиями
	public function CountNewOrders($user_id){
		
		$man=new DiscrMan;
		/*if($man->CheckAccess($user_id,'w',593)){
			$ts=new mysqlSet('select count(distinct id) from mission where id in(select distinct mission_id from mission_history where user_id<>"'.$user_id.'" and is_new=1)');
			
		}else{*/
			 $ts=new mysqlSet('select count(distinct id) from mission where (sent_user_id="'.$user_id.'" OR user_id="0") and id in(select distinct mission_id from mission_history where user_id<>"'.$user_id.'" and is_new=1) and status_id<>5');
			 
			
		//}
		$trs=$ts->GetResult();
		$g=mysqli_fetch_array($trs);
		
		return $g[0];
	}
	
	//пометить прочитанными все изменения
	/*public function ToggleRead($order_id, $user_id){
		
	}
	*/
	
	//пометить прочитанными все изменения
	public function ToggleRead($order_id, $user_id,$force_update=true){
		$can_obnul=false;
		
		if($force_update){
			$can_obnul=true;
		}else{
			//это режим для самой страницы истории.
			//проверить, одно ли событие. если одно - то не занулять
			
			
			
			$_oi=new TaskItem;
			$oi=$_oi->GetItemById($order_id);
			
			$sql='select count(*) from '.$this->tablename.' where '.$this->subkeyname.'="'.$order_id.'" and user_id<>"'.$oi['user_id'].'" /*and status_id<>5*/ ';
			$s=new mysqlset($sql);
			
			$rs=$s->getResult();
			$f=mysqli_fetch_array($rs);
			if($f[0]>0) $can_obnul=true;
			
		}
		
		
		if($can_obnul) $ts=new NonSet('update '.$this->tablename.' set is_new="0" where '.$this->subkeyname.'="'.$order_id.'" and is_new="1" and user_id<>"'.$user_id.'"');	
	}
}
?>