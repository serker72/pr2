<?
require_once('billpospmformer.php');
require_once('supplieritem.php');
require_once('orgitem.php');
require_once('opfitem.php');
require_once('bdetailsgroup.php');
require_once('sched.class.php');
require_once('sched_history_group.php');
require_once('sched_filegroup.php');
require_once('sched_fileitem.php');

require_once('tender.class.php');
require_once('lead.class.php');
require_once('array_sorter.php');

require_once('supplieritem.php');
require_once('orgitem.php');
require_once('opfitem.php');
 
require_once('sched.class.php');
require_once('sched_history_group.php');
require_once('sched_filegroup.php');
require_once('sched_fileitem.php');

require_once('doc_in.class.php');
require_once('doc_out.class.php');
require_once('doc_vn.class.php');

require_once('petitiongroup.php');
require_once('memogroup.php');

require_once('lead.class.php');


//действи€ по лиду
class AnLeadActions{

	public function ShowData($lead_id, $lead=NULL,   $user_ids,  $viewed_ids_arr, $pdate1, $pdate2,  $template, DBDecorator $dec2,$pagename='files.php',  $do_it=false, $can_print=false, $can_edit=false, &$alls, $result=NULL,   $sortmode=NULL, $is_fulfil=NULL){
		
		
		$_li=new Lead_Item;
		if($lead===NULL) $lead=$_li->getitembyid($lead_id);
		
		
		
		 
		 	//сколько сотрудников
		$_sotr_arr=array();
		
		$_suppliers_arr=array();
		//сколько контрагентов
	 	 $_supplier_names=array();
		
		//сколько встреч по командировкам
		$_meets_by_koms=array();
		
		//сколько вход, исход звонков
		$_in_arr=array(); $_out_arr=array();
		$_meets_by_koms=array();
		$_count_1=array(); $_count_2=array(); $_count_3=array(); $_count_5=array(); $_count_6=array(); $_count_7=array();
		 
		
		
		$sm=new SmartyAdm;
		$alls=array();
		
			$_cg=new Sched_CityGroup;
		$_sg=new Sched_SupplierGroup;
		
		
		if($do_it){
			
			$has_content=false; $print=0; $prefix=0; $has_holdings=0;
			$fields=$dec2->GetUris();
			foreach($fields as $k=>$v){
				
				 
				if($v->GetName()=='has_content') $has_content=$v->GetValue();
				
				if($v->GetName()=='print') $print=$v->GetValue();
				if($v->GetName()=='prefix') $prefix=$v->GetValue();
				if($v->GetName()=='has_holdings') $has_holdings=$v->GetValue();
			}
			
		 
			
			//—ќЅџ“»я ѕЋјЌ»–ќ¬ў» ј: заметки, задачи, звонки, встречи по контрагенту.
		
			$sql='( ';
			
			$sql.='select distinct p.id,  p.kind_id as kind_id,  "" as kkind_id, p.kind_id as document_type_id,
			
			p.code, p.pdate_beg, p.pdate_end, p.ptime_beg, p.ptime_end, p.pdate, p.plan_or_fact, p.	incoming_or_outcoming, p.priority, p.topic, p.report,
			
			
		s.name as status_name,
		u.name_s as manager_name, u.login as manager_login, u.is_active as manager_is_active,
		
		up.name_s as confirmed_price_name, up.login as confirmed_price_login, p.confirm_pdate as confirm_price_pdate,
		us.name_s as confirmed_shipping_name, us.login as confirmed_shipping_login, p.confirm_done_pdate as confirm_shipping_pdate,
		
			m.name as meet_name,
			
			u1.name_s as user_name_1, u1.login as user_login_1,
			u2.name_s as user_name_2, u2.login as user_login_2,
			
			uf.name_s as confirmed_fulfil_name, uf.login as confirmed_fulfil_login,
			par.code as parent_code, par.topic as parent_topic, ps.name as parent_status_name,
			
			cr.name_s as cr_name, cr.login as cr_login, cr.is_active as cr_is_active,
			p.id as sched_id 
			
					 
					 
				from sched as p
				left join document_status as s on s.id=p.status_id
				left join user as u on u.id=p.manager_id
				left join user as up on up.id=p.user_confirm_id
				left join user as us on us.id=p.user_confirm_done_id
				left join sched_meet as m on p.meet_id=m.id
				
				left join sched_cities as sc on sc.sched_id=p.id
				left join sprav_city as c on sc.city_id=c.id
				
				left join sched_suppliers as ss on ss.sched_id=p.id
				left join supplier as sup on ss.supplier_id=sup.id
				
				left join sched_contacts as ss1 on ss1.sched_id=p.id
				left join supplier as sup1 on ss1.supplier_id=sup1.id
				
				
				left join sched_task_users as stu on stu.sched_id=p.id and stu.kind_id=1
				left join user as u1 on u1.id=stu.user_id
				
				
				left join sched_task_users as stu2 on stu2.sched_id=p.id and stu2.kind_id=2
				left join user as u2 on u2.id=stu2.user_id
				
				left join user as uf on uf.id=p.user_fulfiled_id
				left join sched as par on par.id=p.task_id
				left join document_status as ps on ps.id=par.status_id
				
				left join user as cr on cr.id=p.created_id
				 
				';
		 
				//фильтр по дате
				$date_flt='
				where 
				p.kind_id<>2 and 
				(sup.id in (select distinct supplier_id from lead_suppliers where sched_id="'.$lead_id.'")
				or sup1.id in (select distinct supplier_id from lead_suppliers where sched_id="'.$lead_id.'")
				)
				
				and
				(
				(p.kind_id in(1,2,3,4) and p.pdate_beg between "'.date('Y-m-d', DateFromdmY($pdate1)).'" and "'.date('Y-m-d', DateFromdmY($pdate2)).'")
				or (p.kind_id=5 and p.pdate between "'.  DateFromdmY($pdate1) .'" and "'. DateFromdmY($pdate2).'")
				)
				
				';
				$sql.=$date_flt;
				
				//echo $sql;
				
				
				//фильтр по вовлеченным сотрудникам
				if(count($user_ids)>0){
					$sort_flt='';
					$sort_flt='
					and(
					(p.kind_id in(2,3,4) and (p.manager_id in('.implode(', ',$user_ids).') or p.created_id in('.implode(', ',$user_ids).'))) or
					(p.kind_id=1 and p.id in(select distinct sched_id from  sched_task_users where user_id in ('.implode(', ',$user_ids).') )) or
					(p.kind_id=5 and (p.manager_id in('.implode(', ',$user_ids).') or p.id in(select distinct sched_id from  sched_users where user_id in ('.implode(', ',$user_ids).')) ))
					)
					';
					
					$sql.=$sort_flt;	
					 
				}
				
				  
				
				//фильтр по видимости
				if(count($viewed_ids_arr)>0){
					
					$su_flt='';	
					
					$_su_flt=array();
					if(count($viewed_ids_arr[1])>0) $_su_flt[]='(p.kind_id=1 and p.id in(select distinct sched_id from  sched_task_users where user_id in ('.implode(', ',$viewed_ids_arr[1]).'))) ';
					if(count($viewed_ids_arr[2])>0) $_su_flt[]='(p.kind_id in(2) and (p.manager_id in('.implode(', ',$viewed_ids_arr[2]).') or p.created_id in('.implode(', ',$viewed_ids_arr[2]).'))) ';
					if(count($viewed_ids_arr[3])>0) $_su_flt[]='(p.kind_id in(3) and (p.manager_id in('.implode(', ',$viewed_ids_arr[3]).') or p.created_id in('.implode(', ',$viewed_ids_arr[3]).'))) ';
					if(count($viewed_ids_arr[4])>0) $_su_flt[]='(p.kind_id in(4) and (p.manager_id in('.implode(', ',$viewed_ids_arr[4]).') or p.created_id in('.implode(', ',$viewed_ids_arr[4]).'))) ';
					if(count($viewed_ids_arr[5])>0) $_su_flt[]='(p.kind_id=5 and (p.manager_id in('.implode(', ',$viewed_ids_arr[5]).') or p.id in(select distinct sched_id from  sched_users where user_id in ('.implode(', ',$viewed_ids_arr[5]).')))) ';
					
					if(count($_su_flt)>0) $su_flt=' and ('.implode(' or ',$_su_flt).') ';
					
					
					
					$sql.=$su_flt;
				}
				
				//фильтр по совершенности/несовершенности
				if(($is_fulfil!==NULL)&&is_array($is_fulfil)){
					
					
					$_flt=array();
					foreach($is_fulfil as $k=>$v){
						
						
						if($v==1){
							$_flt[]='(p.kind_id=1 and p.is_confirmed_done=1) ';
							$_flt[]='(p.kind_id=2 and p.is_fulfiled=1) ';
							$_flt[]='(p.kind_id=3 and p.is_fulfiled=1) ';
							$_flt[]='(p.kind_id=4 and p.is_confirmed_done=1) ';
							
						}
						elseif($v==2){
							$_flt[]='(p.kind_id=1 and p.is_confirmed_done<>1) ';
							$_flt[]='(p.kind_id=2 and p.is_fulfiled<>1) ';
							$_flt[]='(p.kind_id=3 and p.is_fulfiled<>1) ';
							$_flt[]='(p.kind_id=4 and p.is_confirmed_done<>1) ';	
							$_flt[]='(p.kind_id=5) ';	
						}
					}
					
					$su_flt='';	
					$su_flt='
					and(
					('.implode(' OR ',$_flt).') 
					)
					';
					$sql.=$su_flt;	
				}
				
				
				$db_flt=$dec2->GenFltSql(' and ');
				if(strlen($db_flt)>0){
					$sql.=' and '.$db_flt;
				//	$sql_count.=' where '.$db_flt;	
				}
				  
				  $db_flt=$dec2->GenFltHavingSql(' and ');
				if(strlen($db_flt)>0){
					$sql.=' having '.$db_flt;
				//	$sql_count.=' where '.$db_flt;	
				}
				 
				
				
				$sql.=' ) ';
				
				
				//прикрепить: служебки на командировки по лиду
			  	$sql.=' UNION ALL ( ';
				
				$sql.='select 
				
				
				distinct p.id,  6 as kind_id,  p.kind_id  as kkind_id, 6 as document_type_id,
			
			p.code, sc.pdate_beg, sc.pdate_end, sc.ptime_beg, sc.ptime_end, p.pdate, sc.plan_or_fact, "" as incoming_or_outcoming, "" as priority, sc.topic, sc.report,
			
			
		s.name as status_name,
		u.name_s as manager_name, u.login as manager_login, u.is_active as manager_is_active,
		
		up.name_s as confirmed_price_name, up.login as confirmed_price_login, p.confirm_pdate as confirm_price_pdate,
		"" as confirmed_shipping_name, "" as confirmed_shipping_login, "" as confirm_shipping_pdate,
		
			"" as meet_name,
			
			"" as user_name_1, "" as user_login_1,
			"" as user_name_2, "" as user_login_2,
			
			"" as confirmed_fulfil_name, "" as confirmed_fulfil_login,
			"" as parent_code, "" as parent_topic, "" as parent_status_name,
			
			cr.name_s as cr_name, cr.login as cr_login, cr.is_active as cr_is_active,
			p.sched_id as sched_id
			
				
					 
				from doc_vn as p
				left join document_status as s on s.id=p.status_id
				left join user as u on u.id=p.manager_id
				left join user as up on up.id=p.user_confirm_id
			 	 
			 	left join sched as sc on p.sched_id=sc.id and sc.kind_id=2
				left join sched_suppliers as ss on ss.sched_id=sc.id
				left join sched_cities as cit on cit.sched_id=sc.id
				left join sprav_city as city on city.id=cit.city_id
				left join supplier as sup on ss.supplier_id=sup.id
				
				left join doc_vn_leads as dl on dl.doc_out_id=p.id
				left join lead as ld on ld.id=dl.lead_id
				  
				
				left join user as cr on cr.id=p.created_id
			 	
				left join user as send on send.id=p.user_ruk_id
				left join doc_vn_kind as kind on p.kind_id=kind.id
				
				
				where
				ld.id="'.$lead_id.'"
				and p.is_confirmed=1
				
				and (  sc.pdate_beg between "'.date('Y-m-d', DateFromdmY($pdate1)).'" and "'.date('Y-m-d', DateFromdmY($pdate2)).'")
				';
				
				
				//фильтр по вовлеченным сотрудникам
				if(count($user_ids)>0){
					$sort_flt='';
					$sort_flt='
					 and p.manager_id in('.implode(', ',$user_ids).')  
					
					';
					
					$sql.=$sort_flt;	
					 
				}
				
				  
				
				//фильтр по видимости
				//...
				if(count($viewed_ids_arr)>0){
					
					$su_flt='';	
					
					$_su_flt=array();
					if(count($viewed_ids_arr[6])>0) $_su_flt[]='p.id in ('.implode(', ',$viewed_ids_arr[6]).')';
					 
					
					if(count($_su_flt)>0) $su_flt=' and ('.implode(' or ',$_su_flt).') ';
					
					
					
					$sql.=$su_flt;
				}
				
				//фильтр по совершенности/несовершенности
				//...
				
				
				$db_flt=$dec2->GenFltSql(' and ');
				if(strlen($db_flt)>0){
					$sql.=' and '.$db_flt;
				//	$sql_count.=' where '.$db_flt;	
				}
				  
				  $db_flt=$dec2->GenFltHavingSql(' and ');
				if(strlen($db_flt)>0){
					$sql.=' having '.$db_flt;
				//	$sql_count.=' where '.$db_flt;	
				}
				
				
				$sql.=' ) ';
				 
				
				//командировки из служебок по лиду
			 	$sql.=' UNION ALL ( ';
				
				
				$sql.='select distinct p.id,  p.kind_id as kind_id,  "" as kkind_id, p.kind_id as document_type_id,
			
			p.code, p.pdate_beg, p.pdate_end, p.ptime_beg, p.ptime_end, p.pdate, p.plan_or_fact, p.	incoming_or_outcoming, p.priority, p.topic, p.report,
			
			
		s.name as status_name,
		u.name_s as manager_name, u.login as manager_login, u.is_active as manager_is_active,
		
		up.name_s as confirmed_price_name, up.login as confirmed_price_login, p.confirm_pdate as confirm_price_pdate,
		us.name_s as confirmed_shipping_name, us.login as confirmed_shipping_login, p.confirm_done_pdate as confirm_shipping_pdate,
		
			m.name as meet_name,
			
			u1.name_s as user_name_1, u1.login as user_login_1,
			u2.name_s as user_name_2, u2.login as user_login_2,
			
			uf.name_s as confirmed_fulfil_name, uf.login as confirmed_fulfil_login,
			par.code as parent_code, par.topic as parent_topic, ps.name as parent_status_name,
			
			cr.name_s as cr_name, cr.login as cr_login, cr.is_active as cr_is_active ,
			p.id as sched_id 
			
					 
					 
				from sched as p
				left join document_status as s on s.id=p.status_id
				left join user as u on u.id=p.manager_id
				left join user as up on up.id=p.user_confirm_id
				left join user as us on us.id=p.user_confirm_done_id
				left join sched_meet as m on p.meet_id=m.id
				
				left join sched_cities as sc on sc.sched_id=p.id
				left join sprav_city as c on sc.city_id=c.id
				
				left join sched_suppliers as ss on ss.sched_id=p.id
				left join supplier as sup on ss.supplier_id=sup.id
				
				left join sched_contacts as ss1 on ss1.sched_id=p.id
				left join supplier as sup1 on ss1.supplier_id=sup1.id
				
				
				left join sched_task_users as stu on stu.sched_id=p.id and stu.kind_id=1
				left join user as u1 on u1.id=stu.user_id
				
				
				left join sched_task_users as stu2 on stu2.sched_id=p.id and stu2.kind_id=2
				left join user as u2 on u2.id=stu2.user_id
				
				left join user as uf on uf.id=p.user_fulfiled_id
				left join sched as par on par.id=p.task_id
				left join document_status as ps on ps.id=par.status_id
				
				left join user as cr on cr.id=p.created_id
				 
				';
		 
				//фильтр по дате
				$date_flt='
				where 
				p.kind_id=2 
				
				and p.id in (select distinct v.sched_id from doc_vn as v inner join doc_vn_leads as dl on dl.doc_out_id=v.id
				where dl.lead_id="'.$lead_id.'")
				  
				
				
				
				and
				(
				  p.pdate_beg between "'.date('Y-m-d', DateFromdmY($pdate1)).'" and "'.date('Y-m-d', DateFromdmY($pdate2)).'"
				 
				)
				
				';
				$sql.=$date_flt;
				
				//echo $sql;
				
				
				//фильтр по вовлеченным сотрудникам
				if(count($user_ids)>0){
					$sort_flt='';
					$sort_flt='
					and(
					 p.manager_id in('.implode(', ',$user_ids).')  
					)
					';
					
					$sql.=$sort_flt;	
					 
				}
				
				  
				
				//фильтр по видимости
				if(count($viewed_ids_arr)>0){
					
					$su_flt='';	
					
					$_su_flt=array();
					 
					if(count($viewed_ids_arr[2])>0) $_su_flt[]='(p.kind_id in(2) and (p.manager_id in('.implode(', ',$viewed_ids_arr[2]).') or p.created_id in('.implode(', ',$viewed_ids_arr[2]).'))) ';
					 
					
					if(count($_su_flt)>0) $su_flt=' and ('.implode(' or ',$_su_flt).') ';
					
					
					
					$sql.=$su_flt;
				}
				
				//фильтр по совершенности/несовершенности
				if(($is_fulfil!==NULL)&&is_array($is_fulfil)){
					
					
					$_flt=array();
					foreach($is_fulfil as $k=>$v){
						
						
						if($v==1){
							 
							$_flt[]='(p.kind_id=2 and p.is_fulfiled=1) ';
							 
							
						}
						elseif($v==2){
							 
							$_flt[]='(p.kind_id=2 and p.is_fulfiled<>1) ';
							 
						}
					}
					
					$su_flt='';	
					$su_flt='
					and(
					('.implode(' OR ',$_flt).') 
					)
					';
					$sql.=$su_flt;	
				}
				
				
				$db_flt=$dec2->GenFltSql(' and ');
				if(strlen($db_flt)>0){
					$sql.=' and '.$db_flt;
				//	$sql_count.=' where '.$db_flt;	
				}
				  
				  $db_flt=$dec2->GenFltHavingSql(' and ');
				if(strlen($db_flt)>0){
					$sql.=' having '.$db_flt;
				//	$sql_count.=' where '.$db_flt;	
				}
				 
				
				
				$sql.=' ) ';
				 
				
				//вход, 
				$sql.=' UNION ALL ( ';
				
				$sql.='select 
				
				
				distinct p.id,  7 as kind_id,  p.kind_id  as kkind_id, 7 as document_type_id,
			
			p.code, p.pdate as pdate_beg, "" as pdate_end, "" as ptime_beg, "" as ptime_end, p.pdate, "" as plan_or_fact, 0 as incoming_or_outcoming, "" as priority, p.topic, "" as report,
			
			
		s.name as status_name,
		u.name_s as manager_name, u.login as manager_login, u.is_active as manager_is_active,
		
		up.name_s as confirmed_price_name, up.login as confirmed_price_login, p.confirm_pdate as confirm_price_pdate,
		"" as confirmed_shipping_name, "" as confirmed_shipping_login, "" as confirm_shipping_pdate,
		
			"" as meet_name,
			
			"" as user_name_1, "" as user_login_1,
			"" as user_name_2, "" as user_login_2,
			
			"" as confirmed_fulfil_name, "" as confirmed_fulfil_login,
			"" as parent_code, "" as parent_topic, "" as parent_status_name,
			
			cr.name_s as cr_name, cr.login as cr_login, cr.is_active as cr_is_active,
			"" as sched_id 
			
				
					 
				from doc_in as p
				left join document_status as s on s.id=p.status_id
				left join user as u on u.id=p.manager_id
				left join user as up on up.id=p.user_confirm_id
			 	 
			 
				left join doc_in_suppliers as ss on ss.sched_id=p.id
				left join supplier as sup on ss.supplier_id=sup.id
				
				left join doc_in_leads as dl on dl.doc_out_id=p.id
				left join lead as ld on ld.id=dl.lead_id
				  
				
				left join user as cr on cr.id=p.created_id
			 	
				left join user as send on send.id=p.user_view_id
				left join doc_in_kind as kind on p.kind_id=kind.id
				
				
				where
				ld.id="'.$lead_id.'"
				and p.is_confirmed=1
				
				and (  p.pdate between "'.DateFromdmY($pdate1 ).'" and "'.  DateFromdmY($pdate2).'")
				';
				
				
				//фильтр по вовлеченным сотрудникам
				if(count($user_ids)>0){
					$sort_flt='';
					$sort_flt='
					 and p.manager_id in('.implode(', ',$user_ids).')  
					
					';
					
					$sql.=$sort_flt;	
					 
				}
				
				  
				
				//фильтр по видимости
				//...
				if(count($viewed_ids_arr)>0){
					
					$su_flt='';	
					
					$_su_flt=array();
					if(count($viewed_ids_arr[7])>0) $_su_flt[]='p.id in ('.implode(', ',$viewed_ids_arr[7]).')';
					 
					
					if(count($_su_flt)>0) $su_flt=' and ('.implode(' or ',$_su_flt).') ';
					
					
					
					$sql.=$su_flt;
				}
				
				//фильтр по совершенности/несовершенности
				//...
				
				
				$db_flt=$dec2->GenFltSql(' and ');
				if(strlen($db_flt)>0){
					$sql.=' and '.$db_flt;
				//	$sql_count.=' where '.$db_flt;	
				}
				  
				  $db_flt=$dec2->GenFltHavingSql(' and ');
				if(strlen($db_flt)>0){
					$sql.=' having '.$db_flt;
				//	$sql_count.=' where '.$db_flt;	
				}
				
				
				$sql.=' ) ';
				
				
				//исход
				$sql.=' UNION ALL ( ';
				
				$sql.='select 
				
				
				distinct p.id,  8 as kind_id,  p.kind_id  as kkind_id, 8 as document_type_id,
			
			p.code, p.pdate as pdate_beg, "" as pdate_end, "" as ptime_beg, "" as ptime_end, p.pdate, "" as plan_or_fact, 1 as incoming_or_outcoming, "" as priority, p.topic, "" as report,
			
			
		s.name as status_name,
		u.name_s as manager_name, u.login as manager_login, u.is_active as manager_is_active,
		
		up.name_s as confirmed_price_name, up.login as confirmed_price_login, p.confirm_pdate as confirm_price_pdate,
		"" as confirmed_shipping_name, "" as confirmed_shipping_login, "" as confirm_shipping_pdate,
		
			"" as meet_name,
			
			"" as user_name_1, "" as user_login_1,
			"" as user_name_2, "" as user_login_2,
			
			"" as confirmed_fulfil_name, "" as confirmed_fulfil_login,
			"" as parent_code, "" as parent_topic, "" as parent_status_name,
			
			cr.name_s as cr_name, cr.login as cr_login, cr.is_active as cr_is_active,
			"" as sched_id 
			
				
					 
				from doc_out as p
				left join document_status as s on s.id=p.status_id
				left join user as u on u.id=p.manager_id
				left join user as up on up.id=p.user_confirm_id
			 	 
			 
				left join doc_out_suppliers as ss on ss.sched_id=p.id
				left join supplier as sup on ss.supplier_id=sup.id
				
				left join doc_out_leads as dl on dl.doc_out_id=p.id
				left join lead as ld on ld.id=dl.lead_id
				  
				
				left join user as cr on cr.id=p.created_id
			 	
				left join user as send on send.id=p.send_user_id
				
				
				where
				ld.id="'.$lead_id.'"
				and p.is_confirmed=1
				
				and (  p.pdate between "'.DateFromdmY($pdate1 ).'" and "'.  DateFromdmY($pdate2).'")
				';
				
				
				//фильтр по вовлеченным сотрудникам
				if(count($user_ids)>0){
					$sort_flt='';
					$sort_flt='
					 and p.manager_id in('.implode(', ',$user_ids).')  
					
					';
					
					$sql.=$sort_flt;	
					 
				}
				
				  
				
				//фильтр по видимости
				//...
				if(count($viewed_ids_arr)>0){
					
					$su_flt='';	
					
					$_su_flt=array();
					if(count($viewed_ids_arr[8])>0) $_su_flt[]='p.id in ('.implode(', ',$viewed_ids_arr[8]).')';
					 
					
					if(count($_su_flt)>0) $su_flt=' and ('.implode(' or ',$_su_flt).') ';
					
					
					
					$sql.=$su_flt;
				}
				
				//фильтр по совершенности/несовершенности
				//...
				
				
				$db_flt=$dec2->GenFltSql(' and ');
				if(strlen($db_flt)>0){
					$sql.=' and '.$db_flt;
				//	$sql_count.=' where '.$db_flt;	
				}
				  
				  $db_flt=$dec2->GenFltHavingSql(' and ');
				if(strlen($db_flt)>0){
					$sql.=' having '.$db_flt;
				//	$sql_count.=' where '.$db_flt;	
				}
				
				
			 	$sql.=' ) ';
				
				
				
				
				
				 
				
				//√ЋќЅјЋ№Ќјя сортировка
				
				$ord_flt=$dec2->GenFltOrd();
				if(strlen($ord_flt)>0){
					$sql.=' order by '.$ord_flt;
				}		
				
					  
				  
			 //	echo  '<pre>'.$sql.'</pre><br><br>';  
				  
				$set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				
				 
				$_hg=new Sched_HistoryGroup;
				
				$_tsg=new Tender_SupplierGroup; $_lsg=new Lead_SupplierGroup;
				
				$_vsg=new DocVn_SupplierGroup; $_isg=new DocIn_SupplierGroup; $_osg=new DocOut_SupplierGroup;
				
				for($i=0; $i<$rc; $i++){
					
					$f=mysqli_fetch_array($rs);
					foreach($f as $k=>$v) $f[$k]=stripslashes($v);
					
					
					
					$f['pdate_beg']=DateFromYmd($f['pdate_beg']);
					
					if($f['pdate_end']!=="") $f['pdate_end']=DateFromYmd($f['pdate_end']);
					
					$f['pdate']=date('d.m.Y H:i:s', $f['pdate']);
					
					if($f['confirm_price_pdate']!=0) $f['confirm_price_pdate']=date('d.m.Y H:i:s', $f['confirm_price_pdate']);
					else $f['confirm_price_pdate']='-';
					
					 
					if($f['confirm_shipping_pdate']!=0) $f['confirm_shipping_pdate']=date('d.m.Y H:i:s', $f['confirm_shipping_pdate']);
					else $f['confirm_shipping_pdate']='-';
					 
					$_res=new Sched_Resolver($f['kind_id']);
					$f['contact_value']=$_res->instance->ConstructContacts($f['id'], $f,  ($print===0));
					if($f['kind_id']==4){
						
							if($f['incoming_or_outcoming']==0) if(!in_array($f['id'], $_in_arr)) $_in_arr[]=$f['id'];
							
							if($f['incoming_or_outcoming']==1) if(!in_array($f['id'], $_out_arr)) $_out_arr[]=$f['id'];				
						
						
							$_addr=new SchedContactItem;
							
							
							 
							 $sql2='select s.*, opf.name as opf_name from supplier as s left join opf as opf on s.opf_id=opf.id inner join sched_contacts as sc on sc.supplier_id=s.id where sc.sched_id="'.$f['id'].'"  order by s.full_name';
			
			 
			 
							$set2=new mysqlSet($sql2);//,$to_page, $from,$sql_count);
							$rs2=$set2->GetResult();
							$rc2=$set2->GetResultNumRows();
							 
							 
							
							
							for($i2=0; $i2<$rc2; $i2++){
							
								$v2=mysqli_fetch_array($rs2);
								//$our_users[]=$f;
								
								if(!in_array($v2['id'], $_suppliers_arr)) {
										$_suppliers_arr[]=$v2['id'];
										$_supplier_names[]=$v2['full_name'].', '.$v2['opf_name'];
									}
									
								$f['call_suppliers'][]=$v2;
								
							}
					 }
					 
					
					if($f['kind_id']==1){
						
						
						//if($has_content){
							
							$f['content']=$_hg->ShowHistory($f['id'],'',new DBDecorator, false,false,false,$result,false, false,$rr,  false);
							//var_dump($f['content']);
						//}
						
						$f['suppliers']=$_sg->GetItemsByIdArr($f['id']);
						
						 
						foreach($f['suppliers'] as $sk=>$sv) if(!in_array($sv['supplier_id'],$_suppliers_arr)){
							 $_suppliers_arr[]=$sv['supplier_id'];
							 $_supplier_names[]=$sv['full_name'].', '.$sv['opf_name'];
						}
						
						if(!in_array($f['id'], $_count_1)) $_count_1[]=$f['id'];				
					}
					
					
					if(($f['kind_id']==2)||($f['kind_id']==3)){
						//города, контрагенты
						$f['cities']=$_cg->GetItemsByIdArr($f['id']);
						$f['suppliers']=$_sg->GetItemsByIdArr($f['id']);
						
						 
						foreach($f['suppliers'] as $sk=>$sv) {
							if(!in_array($sv['supplier_id'],$_suppliers_arr)) {
								$_suppliers_arr[]=$sv['supplier_id'];
								$_supplier_names[]=$sv['full_name'].', '.$sv['opf_name'];
							}
							if($f['kind_id']==2){
									$t_arr=array($sv['supplier_id'], $f['id']);
									if(!in_array($t_arr, $_meets_by_koms)) $_meets_by_koms[]=$t_arr;
							}	
						}
						
						if($f['kind_id']==2) if(!in_array($f['id'], $_count_2)) $_count_2[]=$f['id'];
						if($f['kind_id']==3) if(!in_array($f['id'], $_count_3)) $_count_3[]=$f['id'];
						
					}
					
					
				 	if($f['kind_id']==6){
						$f['suppliers']=$_vsg->GetItemsByIdArr($f['sched_id']);
						 
						 
						 
						foreach($f['suppliers'] as $sk=>$sv) {
							if(!in_array($sv['supplier_id'],$_suppliers_arr)) {
								$_suppliers_arr[]=$sv['supplier_id'];
								$_supplier_names[]=$sv['full_name'].', '.$sv['opf_name'];
							}
						}
						
						if(!in_array($f['id'], $_count_6)) $_count_6[]=$f['id'];
					}
					
					 
					if($f['kind_id']==7){
						$f['suppliers']=$_isg->GetItemsByIdArr($f['id']);
						 
						foreach($f['suppliers'] as $sk=>$sv) {
							if(!in_array($sv['supplier_id'],$_suppliers_arr)) {
								$_suppliers_arr[]=$sv['supplier_id'];
								$_supplier_names[]=$sv['full_name'].', '.$sv['opf_name'];
							}
						}
						
						if(!in_array($f['id'], $_count_7)) $_count_7[]=$f['id'];
					}
					
					if($f['kind_id']==8){
						$f['suppliers']=$_osg->GetItemsByIdArr($f['id']);
						 
						foreach($f['suppliers'] as $sk=>$sv) {
							if(!in_array($sv['supplier_id'],$_suppliers_arr)) {
								$_suppliers_arr[]=$sv['supplier_id'];
								$_supplier_names[]=$sv['full_name'].', '.$sv['opf_name'];
							}
						}
						
						if(!in_array($f['id'], $_count_8)) $_count_8[]=$f['id'];
					}
					  
					
					
					if($f['kind_id']==5){
						$f['share_users']=$_res->instance->GetUsersArr($f['id'], $f);
						
						$f['suppliers']=$_sg->GetItemsByIdArr($f['id']);
						
						 
						foreach($f['suppliers'] as $sk=>$sv) if(!in_array($sv['supplier_id'],$_suppliers_arr)){
							 $_suppliers_arr[]=$sv['supplier_id'];
							 $_supplier_names[]=$sv['full_name'].', '.$sv['opf_name'];
						}
						
						if(!in_array($f['id'], $_count_5)) $_count_5[]=$f['id'];
					}
					
					 
					
					if(!in_array($f['manager_id'], $_sotr_arr)) $_sotr_arr[]=$f['manager_id'];
					
					
					
					$alls[]=$f;
				}
				
		 //print_r($alls);
			
		//сортировка по городу		
		 if($sortmode===12){
			$alls=ArrayStringArrSorter::SortArr($alls, 'cities',  'name', 1);
		 }elseif($sortmode===13){
			$alls=ArrayStringArrSorter::SortArr($alls, 'cities', 'name', 0);
			 
		 }
		 
		 //сортировка по контрагенту		
		 if($sortmode===14){
			$alls=ArrayStringSorter::SortArr($alls, 'contact_value',   1);
			$alls=ArrayStringArrSorter::SortArr($alls, 'suppliers', 'full_name', 1);
		 }elseif($sortmode===15){
			$alls=ArrayStringSorter::SortArr($alls, 'contact_value',  0); 
			$alls=ArrayStringArrSorter::SortArr($alls, 'suppliers', 'full_name', 1);
		 }
			 
		  $sm->assign('items',$alls);
		}
		
	   
	   
	   
	   
	  
	   $_user_ids=array('','','','');
	   $fields=$dec2->GetUris();
	   $user=''; $supplier=''; $city=''; $share_user=''; $country=''; $fo='';
		foreach($fields as $k=>$v){
			
			//echo $v->GetValue();
			
		 
				
		 
			if($v->GetName()=='user') $user=$v->GetValue();
			if($v->GetName()=='supplier') $supplier=$v->GetValue();
		 	if($v->GetName()=='city') $city=$v->GetValue();
			
				
			if($v->GetName()=='country') $country=$v->GetValue();
			if($v->GetName()=='fo') $fo=$v->GetValue();
			
			
			
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
		 
		  
	   
	   
	      $link=$dec2->GenFltUri('&', $prefix);
	    $link=$pagename.'?'.eregi_replace('&sortmode=[[:digit:]]+','',$link).'&doSub'.$prefix.'=1';
		
		$link=eregi_replace('&action'.$prefix,'&action',$link);
		$link=eregi_replace('&id'.$prefix,'&id',$link);
		$sm->assign('link',$link);
		//$sm->assign('sortmode',$sortmode);
	   
	   //сколько записей
		$sm->assign('no', count($alls));
		//сколько сотрудников
		$sm->assign('sotr_no', count($_sotr_arr));
		
		
		//сколько вход, исход звонков
		$sm->assign('count_40', count($_in_arr)); $sm->assign('count_41', count($_out_arr));
		
		//сколько встреч по командировкам
		$sm->assign('count_21', count($_meets_by_koms));
		
		$sm->assign('count_1', count($_count_1));
		$sm->assign('count_2', count($_count_2));
		$sm->assign('count_3', count($_count_3));
		$sm->assign('count_5', count($_count_5));
		
		$sm->assign('count_6', count($_count_6));
		$sm->assign('count_7', count($_count_7));
		
		
		//сколько контрагентов
		$sm->assign('suppliers_no', count($_suppliers_arr));
		
		$sql='select s.*, opf.name as opf_name from supplier as s left join opf as opf on s.opf_id=opf.id where s.id in('.implode(', ', $_suppliers_arr).') order by s.full_name';
			
			 
			 
			$set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			$our_users=array();
			for($i=0; $i<$rc; $i++){
				
				$f=mysqli_fetch_array($rs);
				$our_users[]=$f['full_name'].', '.$f['opf_name'];
			}
		
		$sm->assign('supplier_list', implode('; ',$our_users));
		
		 
		$sm->assign('can_print',$can_print);
		
		$sm->assign('can_edit',$can_edit);
		$sm->assign('do_it',$do_it);	
	
		$sm->assign('pagename',$pagename);
		//$sm->assign('extended_an',$extended_an);	
			
		return $sm->fetch($template);
	}
	
	
	
}
?>