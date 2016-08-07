<?

require_once('abstractgroup.php');
require_once('missionblink.php');

require_once('missionhistorygroup.php');
require_once('missionexpgroup.php');

// абстрактная группа
class MissionGroup extends AbstractGroup {
	public $_thg;
	public $_tblink;
	public $_exp;
	
	public $prefix='';
	
	//установка всех имен
	protected function init(){
		$this->tablename='mission';
		$this->pagename='missions.php';		
		$this->subkeyname='order_id';	
		$this->vis_name='is_shown';		
		
		$this->_thg=new MissionHistoryGroup;
		$this->_tblink=new MissionBlink;
		$this->_exp=new MissionExpGroup;
	}
	
	
	
	public function ShowPos($user_id, $template, $pdate_begin, $pdate_end, DBDecorator $dec, $is_ajax=false, $can_create_mission=false, $can_all_missions=false,  &$alls, $from=0, $to_page=ITEMS_PER_PAGE){
		
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		$alls=array();
		
		
		$this->GainSql($user_id, $sql, $sql_count, $can_all_missions);
		
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$sql.=' and '.$db_flt;
			$sql_count.=' and '.$db_flt;	
		}
		
		
		
		if(($pdate_begin!==NULL)&&($pdate_end!==NULL)){
			/*$date_flt='
			(
			(t.pdate_begin>="'.$pdate_begin.'" AND t.pdate_end<="'.$pdate_end.'") or
			
			(t.pdate_begin<"'.$pdate_begin.'" AND t.pdate_end<="'.$pdate_end.'") or
			(t.pdate_begin>="'.$pdate_begin.'" AND t.pdate_end>"'.$pdate_end.'") or
			(t.pdate_begin<"'.$pdate_begin.'" AND t.pdate_end>"'.$pdate_end.'")
			
			)
			';	
			*/
			
			$date_flt='
			(
				(t.pdate_begin between "'.$pdate_begin.'" AND "'.$pdate_end.'")  or
				(t.pdate_end  between "'.$pdate_begin.'" AND "'.$pdate_end.'")
		)
			';
			$sql.=' and '.$date_flt;
			$sql_count.=' and '.$date_flt;			
		}
		
		
		$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}
		//echo $sql.'<br>';
		
		$set=new mysqlSet($sql,$to_page, $from,$sql_count);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$total=$set->GetResultNumRowsUnf();
		
		
		//page
		if($from>$total) $from=ceil($total/$to_page)*$to_page;
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
			
			
			$this->_tblink->OverallBlink($f['id'],$color,$f);		
			$f['color']=$color;
			
			$f['pdate_begin_unf']=$f['pdate_begin'];
			$f['pdate_end_unf']=$f['pdate_end'];
			
			$f['pdate_begin']=date("d.m.Y", $f['pdate_begin']);
			$f['pdate_end']=date("d.m.Y", $f['pdate_end']);
			
			
			//метод проверки новых данных
//			var_dump($this->_thg);
			$count_new=$this->_thg->CountNew($f['id'], $user_id);
			
			$f['is_new']=($count_new>0);	
			
			$f['plan']=$this->_exp->CalcPlan($f['id']);
			
			$f['fact']=$this->_exp->CalcFact($f['id']);
			
			
			
			//print_r($f);	
			$alls[]=$f;
		}	
			
		
		
		
		
		//заполним шаблон полями
		
		//$current_kind='';
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			//if($v->GetName()=='kind_id') $current_kind=$v->GetValue();
			
		/*	if($v->GetName()=='sector_id') $current_sector=$v->GetValue();
			if($v->GetName()=='supplier_name') $current_supplier=$v->GetValue();
			if($v->GetName()=='storage_id') $current_storage=$v->GetValue();
			
			
			if($v->GetName()=='user_confirm_id') $current_user_confirm_id=$v->GetValue();
		
			
			if($v->GetName()=='sh_i_id'){
				$current_sh_i_id=$v->GetValue();
				continue;	
			}*/
				$sm->assign($v->GetName(),$v->GetValue());	
		}
		
			//vidy
		/*$_sts=new TaskKindGroup;
		$sts=$_sts->GetItemsArr($current_kind);
		
		$sts[]=array('id'=>0, 'name'=>'все задачи');
		foreach($sts as $kk=>$v){
			if($v['id']==$current_kind) $sts[$kk]['is_current']=true;
			else $sts[$kk]['is_current']=false;
			
		}
		$sm->assign('sc', $sts);
		
		*/
		
		
		
		
		$sm->assign('pagename',$this->pagename);
		
		$sm->assign('from',$from);
		$sm->assign('to_page',$to_page);
		$sm->assign('pages',$pages);
		$sm->assign('items',$alls);
		
		$sm->assign('can_create_mission', $can_create_mission);
		$sm->assign('can_all_missions', $can_all_missions);
		$sm->assign('prefix',$this->prefix);
		
		
			//ссылка для кнопок сортировки
		$link=$dec->GenFltUri();
		$link=$this->pagename.'?'.eregi_replace('&sortmode'.$this->prefix.'=[[:digit:]]+','',$link);
		$sm->assign('link',$link);
		
		
		return $sm->fetch($template);
	
	}
	
	
	public function GainSql($user_id, &$sql, &$sql_count, $can_all_missions){
		
		$can='';
		
		if(!$can_all_missions){
			$can.=' and t.sent_user_id="'.$user_id.'" ';	
			
		}
		
		$sql='select t.*,
		  city.name as city_name,
		  supplier.full_name as supplier_full_name,
		  opf.name as opf_name,
		  su.login as sent_login, su.name_s as sent_name_s,
		  u.login as login, u.name_s as name_s,
		  st.name as status_name
		from mission as t
			left join sprav_city as city on city.id=t.city_id
			left join supplier as supplier on supplier.id=t.supplier_id
			left join opf as opf on supplier.opf_id=opf.id
			left join user as su on su.id=t.sent_user_id
			left join user as u on u.id=t.user_id
			left join mission_status as st on st.id=t.status_id
		
		where t.id<>0 
		'.$can;
		
		
		
		
		$sql_count='select count(*)
		from mission as t
			left join sprav_city as city on city.id=t.city_id
			left join supplier as supplier on supplier.id=t.supplier_id
			left join opf as opf on supplier.opf_id=opf.id
			left join user as su on su.id=t.sent_user_id
			left join user as u on u.id=t.user_id
			left join mission_status as st on st.id=t.status_id
		
		where t.id<>0 
		'.$can;
		
		
		
		
	}
	
	
	
}
?>