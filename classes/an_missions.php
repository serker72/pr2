<?
require_once('abstractgroup.php');
require_once('billpospmformer.php');
require_once('supplieritem.php');

require_once('orgitem.php');
require_once('opfitem.php');
require_once('posonstor.php');
require_once('posgroupgroup.php');
require_once('posgroupitem.php');
require_once('acc_item.php');

require_once('an_missions_blink.php');

require_once('missionhistorygroup.php');
require_once('missionexpgroup.php');



//отчет о командировках
class AnMissions{
	public $prefix='';
	
	function __construct(){
	
		$this->_thg=new MissionHistoryGroup;
		$this->_tblink=new AnMissionBlink;
		$this->_exp=new MissionExpGroup;
	
	}
	
	public function ShowData($pdate1,$pdate2, $current_month, $only_excess, $only_no_excess, DBDecorator $dec, $template, $pagename='an_missions.php',$can_print=false,$do_show_data=true){
		
			
		$_bpm=new BillPosPMFormer;
		$_si=new SupplierItem;
		//$supplier=$_si->GetItemById($supplier_id);
		
		
		$sm=new SmartyAdm;
		$alls=array();
		
			
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$db_flt=' where '.$db_flt;
		//	$sql_count.=' and '.$db_flt;	
		}
		
		$date_flt='';
		if($current_month==1){
			
			$month=date('m' );
			$year=date('Y' );
			$day=date('d' );
			
			$pdate1=mktime(0,0,0,$month,1,$year);
			
			$pdate2=time();
			
			
			
			
		}
		
		if(($pdate1!==NULL)&&($pdate2!==NULL)){
			/*$date_flt='
			(
			(t.pdate_begin>="'.$pdate1.'" AND t.pdate_end<="'.$pdate2.'") or
			
			(t.pdate_begin<"'.$pdate1.'" AND t.pdate_end<="'.$pdate2.'") or
			(t.pdate_begin>="'.$pdate1.'" AND t.pdate_end>"'.$pdate2.'") or
			(t.pdate_begin<"'.$pdate1.'" AND t.pdate_end>"'.$pdate2.'")
			
			)
			';	*/
			
			$date_flt='
			(
				(t.pdate_begin between "'.$pdate1.'" AND "'.$pdate2.'")  or
				(t.pdate_end  between "'.$pdate1.'" AND "'.$pdate2.'")
		)
			';
			
			$date_flt=' and '.$date_flt;
				
		}
		/*
		echo date('d.m.Y', $pdate1);
						echo date('d.m.Y', $pdate2);
			
		*/
		
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
		
		
					';
					
			$sql.='  '.$db_flt.'  '.$date_flt;
		
			$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}
		
		
		
		
		
		if($do_show_data){
		  //echo $sql;
		  
		  $set=new mysqlSet($sql);
		  $rs=$set->GetResult();
		  $rc=$set->GetResultNumRows();
		  
		  
		  
	      
		  $delta=0;
		  $plan=0;
		  $fact=0;
		  
		  $users=array(); $suppliers=array(); $cities=array(); 
		  
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
			  
			
			  
			  $f['plan']=$this->_exp->CalcPlan($f['id']);
			  
			  $f['fact']=$this->_exp->CalcFact($f['id']);
			  
			  $f['delta']=round((float)$f['plan']-(float)$f['fact'],2);
			  
			  
			  if($only_excess&&((float)$f['fact']<=(float)$f['plan'])) continue;
			  
			  if($only_no_excess&&((float)$f['fact']>(float)$f['plan'])) continue;
			  
			  
			  if(!in_array($f['sent_user_id'], $users)) $users[]=$f['sent_user_id'];
			  if(!in_array($f['supplier_id'], $suppliers)) $suppliers[]=$f['supplier_id'];
			  if(!in_array($f['city_id'], $cities)) $cities[]=$f['city_id'];
			  
			  
			  
			  $delta+=$f['delta'];
			  $plan+=$f['plan'];
			  $fact+=$f['fact'];
			  
			  
			  $alls[]=$f;
		  }
				
		}
		
		//заполним шаблон полями
	
		
		$sortmode=0;
		
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			
			if($v->GetName()=='sortmode') $sortmode=$v->GetValue();
			
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
		
		
		
		$sm->assign('items',$alls);
		$sm->assign('pagename',$pagename);
		
		$sm->assign('can_print',$can_print);
		
		$sm->assign('fact',round($fact,2));
		$sm->assign('plan',round($plan,2));
		$sm->assign('delta',round($delta,2));
		
		$sm->assign('count_of_users',count($users));
		$sm->assign('count_of_cities',count($cities));
		$sm->assign('count_of_suppliers',count($suppliers));
		
		
		//ссылка для кнопок сортировки
		$link=$dec->GenFltUri('&', $this->prefix);
		//echo $link;
		$link=$this->pagename.'?'.eregi_replace('&sortmode'.$this->prefix.'=[[:digit:]]+','',$link).'&doSub'.$this->prefix.'=1';
		$sm->assign('link',$link);
		$sm->assign('sortmode',$sortmode);
		
		$sm->assign('prefix', $this->prefix);
		
		$sm->assign('do_it',$do_show_data);
		
		return $sm->fetch($template);
	}
	
	
	
}
?>