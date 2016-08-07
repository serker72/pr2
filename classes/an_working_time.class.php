<?
 
require_once('supplieritem.php');
require_once('orgitem.php');
require_once('opfitem.php');
 
require_once('tender.class.php');
require_once('lead.class.php');
require_once('tz.class.php');
require_once('kp_in.class.php');
require_once('bdr.class.php');

require_once('discr_man.php');
//require_once('abstract_working_group.php');

//отчет  Время обработки документов
class AnWorkingTime{

	public function ShowData(  $template, array $doc_kinds, array $deps, DBDecorator $dec2,  $pagename='files.php',  $do_it=false, $can_print=false,  &$alls, $result=NULL){
		
		$au=new AuthUser(); //false,false,false);
		if($result===NULL) $result->Auth(false,false,false);
		
		$this->init($result);
		
		
		$sm=new SmartyAdm;
		$alls=array(
			'ids'=>array(),
			'data'=>array()
		);
		
		if($do_it){
			
			$has_content=false; $print=0; $prefix=0;
			$fields=$dec2->GetUris();
			foreach($fields as $k=>$v){
				
				 
				if($v->GetName()=='has_content') $has_content=$v->GetValue();
				
				 if($v->GetName()=='print') $print=$v->GetValue();
				  if($v->GetName()=='prefix') $prefix=$v->GetValue();
			}
			
			
			$_sqls=array();
			foreach($doc_kinds as $kind){
				$_sqls[]='('.$this->GainSql($kind,  $dec2).' )';
				
			};
			
			$sql=implode(' UNION ALL ', $_sqls);
			$ord_flt=$dec2->GenFltOrd();
			if(strlen($ord_flt)>0){
				$sql.='  order by '.$ord_flt;
			}			  
			  
		//	echo  '<textarea cols="100" rows="10">'.$sql.'</textarea><br><br>';  
			
			 
			$set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			 
			 
			
			for($i=0; $i<$rc; $i++){
				
				$f=mysqli_fetch_array($rs);
				foreach($f as $k=>$v) $f[$k]=stripslashes($v);
				
				
				$counters=array(); $counters_unf=array();
				//подсчет времен работы
				foreach($this->counters as $counter_id=>$cv){
					if($counter_id==-1){
						$counters_unf[$counter_id]=$this->doc_defs[$f['kind']]['working']->CalcTotalWorkingTime($f['id'], $formatted, $arr, $working);
						$counters[$counter_id]=$formatted; 
						
					}else{
						if(!in_array($counter_id, $deps)) continue;
						
						$counters_unf[$counter_id]=$this->doc_defs[$f['kind']]['working']->CalcWorkingTime($f['id'], $counter_id, $formatted, $arr, $working);
						$counters[$counter_id]=$formatted; 
						
					}
				}
				$f['counters']=$counters; $f['counters_unf']=$counters_unf; 
				
				$f['can_edit']=$this->doc_defs[$f['kind']]['can_edit'];
				$f['pdate_f']=date('d.m.Y', $f['pdate']);
				$f['pdate_fl']=date('d.m.Y H:i:s', $f['pdate']);
				
				//контрагенты
				$f['suppliers']=$this->doc_defs[$f['kind']]['supplier_gr']->GetItemsByIdArr($f['supplier_gr_id']);
				
				
				$f['pagename']=$this->doc_defs[$f['kind']]['pagename'];
				
				$alls['data'][]=$f;
			}
			
			
			$sm->assign('items',$alls); 
			
			//статистика
			$total_times=0;
			foreach($alls['data'] as $k=>$v) $total_times+=$v['counters_unf'][-1];
			$total_times= Abstract_WorkingGroup::FormatTime($total_times);
			$sm->assign('total_times', $total_times);
			
			//разобьем по видам
			$stats=array();
			foreach($doc_kinds as $kind){
								
				$counters=array();
				foreach($alls['data'] as $k=>$v){
					if($v['kind']!=$kind) continue;
					
					foreach($this->counters as $counter_id=>$cv){
						
						if(($counter_id!=-1)&&!in_array($counter_id, $deps)) continue;
						
						if(!isset($counters[$counter_id])) $counters[$counter_id]=0;
						
						$counters[$counter_id]+=$v['counters_unf'][$counter_id]	;
					}
					
				}
				
				foreach($counters as $k=>$v) $counters[$k]=Abstract_WorkingGroup::FormatTime($v);
				
				$stats[]=array(
					'name'=>$this->doc_defs[$kind]['name'],
					'counters'=>$counters
				);
			};
			
			
			/*echo '<pre>';
			print_r($stats);
			echo '</pre>';
			*/
			$sm->assign('stats', $stats);
					
		  
		}
		
		
		
		
		$fields=$dec2->GetUris();
		$user=''; $supplier=''; $city=''; $share_user='';  $branch='';
		foreach($fields as $k=>$v){
			
			//echo $v->GetValue();
			
		 
				
		 
			if($v->GetName()=='manager_name') $user=$v->GetValue();
			if($v->GetName()=='supplier') $supplier=$v->GetValue();
		 	if($v->GetName()=='city') $city=$v->GetValue();
			
				if($v->GetName()=='branch') $branch=$v->GetValue();
			
			
			
			
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
		
		 
		//сотрудник
		if(strlen($user)>0){
				$_ids=explode(';', $user);
				
				$sql='select p.*, up.name as position_s from user as p
				left join user_position as up on up.id=p.position_id
				 where p.id in('.implode(', ', $_ids).') order by name_s';
				
				 
				 
				$set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				
				$our_users=array();
				for($i=0; $i<$rc; $i++){
					
					$f=mysqli_fetch_array($rs);
					$our_users[]=$f;
				}
				$sm->assign("our_users", $our_users);
			 
			}
		//контрагент
		if(strlen($supplier)>0){
			$_ids=explode(';', $supplier);
			
			$sql='select s.*, opf.name as opf_name from supplier as s left join opf as opf on s.opf_id=opf.id where s.id in('.implode(', ', $_ids).') order by s.full_name';
			
			 
			 
			$set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			$our_users=array();
			for($i=0; $i<$rc; $i++){
				
				$f=mysqli_fetch_array($rs);
				$our_users[]=$f;
			}
			$sm->assign("our_suppliers", $our_users);
		 
		}
		
		
	   
	      $link=$dec2->GenFltUri('&', $prefix);
	    $link=$pagename.'?'.eregi_replace('&sortmode=[[:digit:]]+','',$link).'&doSub'.$prefix.'=1';
		$sm->assign('link',$link);
		//$sm->assign('sortmode',$sortmode);
	   
	   
		 
		$sm->assign('can_print',$can_print);
		
	 
		$sm->assign('do_it',$do_it);	
	
		$sm->assign('pagename',$pagename);
		//$sm->assign('extended_an',$extended_an);	
		
		$kinds1=array();
		foreach($this->counters as $k=>$v) if(($k!=-1)&&!in_array($k, $deps)) continue; else $kinds1[$k]=$v;
		
		
		$sm->assign('kinds',$kinds1); // $this->counters); 
			
		return $sm->fetch($template);
		 
		 
		
	}
	
	
	
	 
	
	protected $counters, $doc_defs;
	function __construct(){
		$this->counters=array(); $this->doc_defs=array();
		
		$this->counters=array(
			-1=>'Общее время обработки',
			0=>'Время обработки менеджером',
			1=>'Время обработки отделом закупок',
			2=>'Время обработки техническим отделом',
			3=>'Время обработки поставщиком',
			4=>'Время проверки координаторами ДПиМ',
			5=>'Время обработки финансовой службой',
			6=>'Время обработки генеральным директором',
			
		);	
	}
	
	
	//инициализация - зависит от id пользователя
	protected function init($result){
		$_tg=new Tender_Group; $_lg=new Lead_Group; $_tzg=new TZ_Group; $_kp_in=new KpIn_Group; $_kp_out=new KpIn_Out_Group; $_bdr=new BDR_Group;
		
		 
		$_man=new DiscrMan;
		
		$this->doc_defs[0]=array(
			'kind'=>0,
			'name'=>'Тендер',
			'tablename'=>'tender',
			'available_ids'=>$_tg->GetAvailableTenderIds($result['id']),
			'working'=>new Tender_WorkingGroup,
			'kind_id'=>NULL,
			'can_edit'=>$_man->CheckAccess($result['id'],'w',931),
			'supplier_gr'=>new Tender_SupplierGroup(),
			'supplier_gr_id'=>'id',
			'pagename'=>'ed_tender.php'
		);	
		
		$this->doc_defs[1]=array(
			'kind'=>1,
			'name'=>'Лид',
			'tablename'=>'lead',
			'available_ids'=>$_lg->GetAvailableLeadIds($result['id']),
			'working'=>new Lead_WorkingGroup,
			'kind_id'=>NULL,
			'can_edit'=>$_man->CheckAccess($result['id'],'w',950),
			'supplier_gr'=>new Lead_SupplierGroup(),
			'supplier_gr_id'=>'id',
			'pagename'=>'ed_lead.php'
		);	
		
		$this->doc_defs[2]=array(
			'kind'=>2,
			'name'=>'ТЗ',
			'tablename'=>'tz',
			'available_ids'=>$_tzg->GetAvailableTZIds($result['id']),
			'working'=>new TZ_WorkingGroup,
			'kind_id'=>NULL,
			'can_edit'=>$_man->CheckAccess($result['id'],'w',1009),
			'supplier_gr'=>new Lead_SupplierGroup,
			'supplier_gr_id'=>'lead_id',
			'pagename'=>'ed_tz.php'
		);	
		
		$this->doc_defs[3]=array(
			'kind'=>3,
			'name'=>'КП вх.',
			'tablename'=>'kp_in',
			'available_ids'=>$_kp_in->GetAvailableKpInIds($result['id']),
			'working'=>new KpIn_WorkingGroup,
			'kind_id'=>0,
			'can_edit'=>$_man->CheckAccess($result['id'],'w',1021),
			'supplier_gr'=>new Lead_SupplierGroup,
			'supplier_gr_id'=>'lead_id',
			'pagename'=>'ed_kp_in.php'
		);	
		
		$this->doc_defs[4]=array(
			'kind'=>4,
			'name'=>'КП исх.',
			'tablename'=>'kp_in',
			'available_ids'=>$_kp_out->GetAvailableKpInIds($result['id']),
			'working'=>new KpOut_WorkingGroup,
			'kind_id'=>1,
			'can_edit'=>$_man->CheckAccess($result['id'],'w',1021),
			'supplier_gr'=>new Lead_SupplierGroup,
			'supplier_gr_id'=>'lead_id',
			'pagename'=>'ed_kp_in.php'
		);	
		
		$this->doc_defs[5]=array(
			'kind'=>5,
			'name'=>'БДР/БДДС',
			'tablename'=>'bdr',
			'available_ids'=>$_bdr->GetAvailableBDRIds($result['id']),
			'working'=>new BDR_WorkingGroup,
			'kind_id'=>NULL,
			'can_edit'=>$_man->CheckAccess($result['id'],'w',1041),
			'supplier_gr'=>new Lead_SupplierGroup,
			'supplier_gr_id'=>'lead_id',
			'pagename'=>'ed_bdr.php'
		);	
		
	}
	
	
	protected function GainSql($kind, $decorator){
		$sql='
		select    
		 distinct  p.id, "'.$kind.'" as kind, p.code, p.pdate, p.manager_id, p.created_id,
		 crea.name_s as crea_name_s, mn.name_s as mn_name_s,
		 st.name as status_name,
		 "'.$this->doc_defs[$kind]['name'].'" as kind_name,
		 
		p.'.$this->doc_defs[$kind]['supplier_gr_id'].' as supplier_gr_id
		 
		 
		
		 from '.$this->doc_defs[$kind]['tablename'].' as p 
			 
			left join user as mn on mn.id=p.manager_id
			 
			left join user as crea on crea.id=p.created_id
			left join document_status as st on st.id=p.status_id
		 
			 	
		';
		
		$db_flt=$decorator->GenFltSql(' and ');
				if(strlen($db_flt)>0){
					$sql.=' where '.$db_flt;
				//	$sql_count.=' where '.$db_flt;	
				}
		return $sql;	
	}
	
}
?>