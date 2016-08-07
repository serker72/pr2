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

require_once('suppliers_users_num.php');


//отчет  Забытые клиенты
class AnSchedForgotten{

	public function ShowData($other_date=NULL,   $template, DBDecorator $dec2,  $pagename='files.php',  $do_it=false, $can_print=false, $can_edit=false, $can_call=false, &$alls, $result=NULL, $can_view_reasons=false){
		
		 
		 	//сколько сотрудников
		$_sotr_arr=array();
		
		$_suppliers_arr=array();
		
		
		$_pdates=array(); 
		if($other_date!==NULL) $_pdates[]=datefromdmy($other_date)+23*60*60+59*60;
		$_pdates[]=time();
		
		$sm=new SmartyAdm;
		$alls=array(
			'ids'=>array(),
			'data'=>array()
		);
		
		$_cg=new SupplierCitiesGroup;
	 
		$_sr=new SupplierResponsibleUserGroup;
		
		if($do_it){
			
			$has_content=false; $print=0; $prefix=0;
			$fields=$dec2->GetUris();
			foreach($fields as $k=>$v){
				
				 
				if($v->GetName()=='has_content') $has_content=$v->GetValue();
				
				 if($v->GetName()=='print') $print=$v->GetValue();
				  if($v->GetName()=='prefix') $prefix=$v->GetValue();
			}
			 
		  

			foreach($_pdates as $k1=>$pdate){
				 /*
				 1=>'Нет лидов, нет звонков последние 3 месяца, не запланированы звонки, встречи, командировки на ближайшие 3 месяца, и дата заведения контрагента была более 3 месяцев назад',
			2=>'Есть холодные лиды, но нет звонков 1 месяц, и дата заведения контрагента была более 1 месяца назад',
			3=>'Есть теплые лиды, но нет звонков 2 недели, и дата заведения контрагента была более 2 недель назад',
			4=>'Есть горячие лиды, но нет звонков 1 неделю, и дата заведения контрагента была более 1 недели назад',
			5=>'Есть холодные лиды, но нет ни встреч, ни командировок 6 месяцев, не запланированы звонки, встречи, командировки на ближайшие 6 месяцев, и дата заведения контрагента была более 6 месяцев назад',
			6=>'Есть теплые лиды, но нет ни встреч, ни командировок 3 месяца, не запланированы звонки, встречи, командировки на ближайшие 3 месяца, и дата заведения контрагента была более 3 месяцев назад',
			7=>'Есть горячие лиды или тендер, но нет ни встреч, ни командировок 2 месяца, не запланированы звонки, встречи, командировки на ближайшие 2 месяца, и дата заведения контрагента была более 3 месяцев назад'*/
				$sql='('.$this->GainSql(1, $dec2).'
				
					  and p.id not in(select distinct a.supplier_id from  lead_suppliers as a inner join lead as b on b.id=a.sched_id where b.is_confirmed=1 and b.status_id not in(18,33,34,32,36,30,31,3)) 
					  and p.id not in(select distinct a.supplier_id from sched_contacts as a inner join sched as b on b.id=a.sched_id where b.kind_id=4 and b.is_confirmed=1 and b.confirm_done_pdate>="'.($pdate-3*30*24*60*60-1).'")
					  and p.active_first_pdate<="'.($pdate-3*30*24*60*60-1).'"
					  
					   and p.id not in(select distinct a.supplier_id from sched_suppliers as a inner join sched as b on b.id=a.sched_id where b.kind_id in(2,3,4) and b.is_confirmed=1 and (b.pdate_beg between "'.date('Y-m-d').'" and  "'.date('Y-m-d',$pdate+3*30*24*60*60-1).'"))
					   
					  )';
					  
					 
					 $sql.='
						UNION ALL
					 ('.$this->GainSql(2, $dec2).'
					  and p.id in(select distinct a.supplier_id from  lead_suppliers as a inner join lead as b on b.id=a.sched_id where b.is_confirmed=1 and (b.probability between 0 and 29.99) and b.status_id not in(18,33,34,32,36,30,31,3))
					  and p.id not in(select distinct a.supplier_id from sched_contacts as a inner join sched as b on b.id=a.sched_id where  b.kind_id=4 and b.is_confirmed=1 and b.confirm_done_pdate>="'.($pdate-30*24*60*60-1).'")
					 and p.active_first_pdate<="'.($pdate-30*24*60*60-1).'"
					 
					 )';
					 
					$sql.='
					 UNION ALL
					 ('.$this->GainSql(3, $dec2).'
					 and p.id in(select distinct a.supplier_id from  lead_suppliers as a inner join lead as b on b.id=a.sched_id where b.is_confirmed=1 and (b.probability between 30 and 69.99) and b.status_id not in(18,33,34,32,36,30,31,3))
					  and p.id not in(select distinct a.supplier_id from sched_contacts as a inner join sched as b on b.id=a.sched_id where b.kind_id=4 and b.is_confirmed=1 and b.confirm_done_pdate>="'.($pdate-14*24*60*60-1).'")
					  
					  and p.active_first_pdate<="'.($pdate-14*24*60*60-1).'"
					 )';
					 
					 $sql.='
					 UNION ALL
					 ('.$this->GainSql(4, $dec2).'
					 and p.id in(select distinct a.supplier_id from  lead_suppliers as a inner join lead as b on b.id=a.sched_id where b.is_confirmed=1 and (b.probability >=70) and b.status_id not in(18,33,34,32,36,30,31,3))
					 and p.id not in(select distinct a.supplier_id from sched_contacts as a inner join sched as b on b.id=a.sched_id where  b.kind_id=4 and b.is_confirmed=1 and b.confirm_done_pdate>="'.($pdate-7*24*60*60-1).'")
					 
					 and p.active_first_pdate<="'.($pdate-7*24*60*60-1).'"
					 
					 )';
					 
					$sql.='
					 UNION ALL
					 ('.$this->GainSql(5, $dec2).'
					  and p.id in(select distinct a.supplier_id from  lead_suppliers as a inner join lead as b on b.id=a.sched_id where b.is_confirmed=1 and (b.probability between 0 and 29.99) and b.status_id not in(18,33,34,32,36,30,31,3))
					 and p.id not in(select distinct a.supplier_id from sched_suppliers as a inner join sched as b on b.id=a.sched_id where b.kind_id in(2,3) and b.is_confirmed=1 and b.pdate_beg>="'.date('Y-m-d',$pdate-6*30*24*60*60-1).'")
					 
					  and p.id not in(select distinct a.supplier_id from sched_suppliers as a inner join sched as b on b.id=a.sched_id where b.kind_id in(2,3,4) and b.is_confirmed=1 and (b.pdate_beg between "'.date('Y-m-d').'" and  "'.date('Y-m-d',$pdate+6*30*24*60*60-1).'"))
					
					 and p.active_first_pdate<="'.($pdate-6*30*24*60*60-1).'"
					 )	
					 ';
					 
					  
					$sql.=' UNION ALL
					 ('.$this->GainSql(6, $dec2).' 
					 	 and p.id in(select distinct a.supplier_id from  lead_suppliers as a inner join lead as b on b.id=a.sched_id where b.is_confirmed=1 and (b.probability between 30 and 69.99) and b.status_id not in(18,33,34,32,36,30,31,3))
					 and p.id not in(select distinct a.supplier_id from sched_suppliers as a inner join sched as b on b.id=a.sched_id where b.kind_id in(2,3) and b.is_confirmed=1 and b.pdate_beg>="'.date('Y-m-d',$pdate-3*30*24*60*60-1).'")
					 
					 and p.id not in(select distinct a.supplier_id from sched_suppliers as a inner join sched as b on b.id=a.sched_id where b.kind_id in(2,3,4) and b.is_confirmed=1 and (b.pdate_beg between "'.date('Y-m-d').'" and  "'.date('Y-m-d',$pdate+3*30*24*60*60-1).'"))
					 
					 and p.active_first_pdate<="'.($pdate-3*30*24*60*60-1).'"
					 )';
					 
					 
					$sql.=' UNION ALL
					 ('.$this->GainSql(7, $dec2).'
					 
					  and (
					  	p.id in(select distinct a.supplier_id from  lead_suppliers as a inner join lead as b on b.id=a.sched_id where b.is_confirmed=1 and (b.probability >=70) and b.status_id not in(18,33,34,32,36,30,31,3))
						or
						p.id in(select distinct a.supplier_id from  tender_suppliers as a inner join tender as b on b.id=a.sched_id where b.is_confirmed=1 and b.status_id not in(18,33,34,32,36,30,31,3))
						
						)
					 and p.id not in(select distinct a.supplier_id from sched_suppliers as a inner join sched as b on b.id=a.sched_id where b.kind_id in(2,3) and b.is_confirmed=1 and b.pdate_beg>="'.date('Y-m-d',$pdate-2*30*24*60*60-1).'")
					 
					 and p.id not in(select distinct a.supplier_id from sched_suppliers as a inner join sched as b on b.id=a.sched_id where b.kind_id in(2,3,4) and b.is_confirmed=1 and (b.pdate_beg between "'.date('Y-m-d').'" and  "'.date('Y-m-d',$pdate+2*30*24*60*60-1).'"))
					 
					 and p.active_first_pdate<="'.($pdate-2*30*24*60*60-1).'"
					 )	
					';
				 
				
				
				// $sql.=' limit 15 ';
				
				$ord_flt=$dec2->GenFltOrd();
				if(strlen($ord_flt)>0){
					$sql.='  order by '.$ord_flt;
				}			  
				  
				//echo  $sql.'<br><br>';  
				
				 
				$set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				
				 
				 
				
				for($i=0; $i<$rc; $i++){
					
					$f=mysqli_fetch_array($rs);
					foreach($f as $k=>$v) $f[$k]=stripslashes($v);
					  
					
					$f['resps']=$_sr->GetUsersArr($f['id'], $ids);
					
					
					$csg=$_cg->GetItemsByIdArr($f['id']);
					$f['cities']=$csg;
					
					//var_dump($csg); 
					
					//$alls[]=$f;
					
					if(!in_array($f['id'], $alls['ids'])){
						//нет вообще нигде, добавить
						$f['reason_ids']=array($f['reason_id']);
						$f['reason_names']=array($this->reasons[$f['reason_id']]);
						$alls['ids'][]=$f['id'];
						$alls['data'][]=$f;	
						
						//занести предыдущие даты
						 
						//
						$datesarr=array(array(),array());
						 
						$datesarr[$k1][]=$this->reasons[$f['reason_id']];
						
						$alls['data'][count($alls['data'])-1]['rdates']=$datesarr;
						
						$_suppliers_arr[]=$f['id'];
					}else{
						//уже был, добавить только причину
						//найти ключ - номер в ids
						$key=array_search ($f['id'] , $alls['ids'] );
						$alls['data'][$key]['reason_ids'][]=$f['reason_id'];
						$alls['data'][$key]['reason_names'][]=$this->reasons[$f['reason_id']];
						//$pdate	
						
						$alls['data'][$key]['rdates'][$k1][]=$this->reasons[$f['reason_id']];
						
						 
					}
				}
			
			}
			
		  /*echo '<pre>';
		  print_r($alls);
		  echo '</pre>';
		  */
		  
		  		  
		  $sm->assign('items',$alls);
		}
		
	   
	   
	   
	   
	  
	   $_user_ids=array('','','','');
	   $fields=$dec2->GetUris();
	   $user=''; $supplier=''; $city=''; $share_user='';  $branch='';  $country=''; $fo='';
		foreach($fields as $k=>$v){
			
			//echo $v->GetValue();
			
		 
				
		 
			if($v->GetName()=='user') $user=$v->GetValue();
			if($v->GetName()=='supplier') $supplier=$v->GetValue();
		 	if($v->GetName()=='city') $city=$v->GetValue();
			
				if($v->GetName()=='branch') $branch=$v->GetValue();
			
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
		
		//город
		if(strlen($city)>0){
			$_ids=explode(';', $city);
			
			$sql='select c.*, r.name as region_name, o.name as okrug_name, sc.name as country_name
		
		 from sprav_city as c
		 left join sprav_region as r on c.region_id=r.id
		 left join sprav_district as o on o.id=c.district_id
		 left join sprav_country as sc on c.country_id=sc.id
		
		where  c.id in('.implode(', ', $_ids).') order by c.name';
			
		//	 echo $sql;
			 
			 
			$set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			$our_users=array();
			for($i=0; $i<$rc; $i++){
				
				$f=mysqli_fetch_array($rs);
				
					$f['fullname']=$f['name'];
				if(strlen($f['okrug_name'])>0) $f['fullname'].=', '.$f['okrug_name'];
				if(strlen($f['region_name'])>0) $f['fullname'].=', '.$f['region_name'];
				if(strlen($f['country_name'])>0) $f['fullname'].=', '.$f['country_name'];
				
				
				$our_users[]=$f;
			}
			$sm->assign("our_cities", $our_users);
		 	 
		}
		
		//страна
		if(strlen($country)>0){
			$_ids=explode(';', $country);
			
			$sql='select c.* 
		
		 from sprav_country as c
		
		where  c.id in('.implode(', ', $_ids).') order by c.name';
			
		//	 echo $sql;
			 
			 
			$set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			$our_users=array();
			for($i=0; $i<$rc; $i++){
				
				$f=mysqli_fetch_array($rs);
				
					 
				$our_users[]=$f;
			}
			$sm->assign("our_countries", $our_users);
		 	 
		}
		
		//фед. округ
		if(strlen($fo)>0){
			$_ids=explode(';', $fo);
			
			$sql='select c.* 
		 from sprav_district as c
		
		where  c.id in('.implode(', ', $_ids).') order by c.name';
			
		//	 echo $sql;
			 
			 
			$set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			$our_users=array();
			for($i=0; $i<$rc; $i++){
				
				$f=mysqli_fetch_array($rs);
				
					 
				$our_users[]=$f;
			}
			$sm->assign("our_fos", $our_users);
		 	 
		}
		
		
		//отрасли
		if(strlen($branch)>0){
			$_ids=explode(';', $branch);
			
			$sql='select p.*, r.name as parent_name from supplier_branches as p left join supplier_branches as r on p.parent_id=r.id where p.id in('.implode(', ', $_ids).') order by r.name, p.name ';
		//	echo $sql;
			 
			 
			$set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			$our_users=array();
			for($i=0; $i<$rc; $i++){
				
				$f=mysqli_fetch_array($rs);
				
				$val='';
			if($f['parent_name']!="") $val.=$f['parent_name'].' - ';
			$val.=$f['name'];
				
				$f['name']=$val;
				
				$our_users[]=$f;
			}
			$sm->assign("our_branches", $our_users);
		 
		}
		  
	   
	   
	      $link=$dec2->GenFltUri('&', $prefix);
	    $link=$pagename.'?'.eregi_replace('&sortmode=[[:digit:]]+','',$link).'&doSub'.$prefix.'=1';
		$sm->assign('link',$link);
		//$sm->assign('sortmode',$sortmode);
	   
	   //сколько записей
		$sm->assign('no', count($alls));
		//сколько сотрудников
		$sm->assign('sotr_no', count($_sotr_arr));
		
		
		//сколько контрагентов
		$sm->assign('supplier_no', count($_suppliers_arr));
		$total_sup_no= $this->CalcTotalSuppliers($dec2);
		$sm->assign('total_supplier_no',$total_sup_no);
		$sm->assign('active_supplier_no', $total_sup_no - count($_suppliers_arr));
		
		$total_managers_no=$this->CalcTotalManagers($dec2);
		$sm->assign('sotr_no',$total_managers_no);
		 
		$sm->assign('can_print',$can_print);
		
		$sm->assign('do_compare',($other_date!==NULL));
		
		$pdates=array(); foreach($_pdates as $k=>$v) $pdates[]=date('d.m.Y', $v);
		
		 
		$sm->assign('dates', $pdates);
		
		$sm->assign('can_edit',$can_edit);
		$sm->assign('can_call',$can_edit);
		
		$sm->assign('can_view_reasons',$can_view_reasons);
		
		
		
		$sm->assign('do_it',$do_it);	
	
		$sm->assign('pagename',$pagename);
		//$sm->assign('extended_an',$extended_an);	
		
		
		//формируем мини-отчет...
		if($other_date!==NULL){
			
			
			
			$usersdata=$this->GetTotalManagers($dec2);
			foreach($usersdata as $k=>$v){
				$v['total']=$this->CalcTotalSuppliersByManager($dec2, $v['id']);
				//$v['stat_total']=$this->CalcStatSuppliersByManager($v['id']);
				//как найти неактивных??? посчитать, сколько раз этот сотрудник попал в наш отчет на заданную!!! дату
				$data=array(); 
				foreach($_pdates as $k1=>$pdate){
					//echo $pdate;
					$stat=array();
					
					$stat['inactive']=0;
					$stat['active']=0;
					$stat['inactive_percent']=0;
					$stat['active_percent']=0;
					$stat['total']=$this->CalcTotalSuppliersByManager($dec2, $v['id'], $pdate);
					$stat['stat_total']=$this->CalcStatSuppliersByManager($v['id'], $pdate);
					
					
					foreach($alls['data'] as $k2=>$v1){
						//if(in_array($v['id'], 	
						//число причин по дате 1 и 2 если 0 - то активен,
						foreach($v1['resps'] as $k3=>$v3){
							if($v3['user_id']==$v['id']){
								//echo 'сотрудник '.$v['name_s'].' контрагент '.$v1['full_name'].' дата '.date("d.m.Y",$pdate).', число причин:'. count( $v1['rdates'][$k1]).'<br>';
								
								
								$stat['inactive']+=count( $v1['rdates'][$k1]);
								
									
							}
						}
						 
					}
					
					
					
					$stat['active']=$stat['total']-$stat['inactive'];
					if($stat['total']>0) $stat['inactive_percent']=round(100*$stat['inactive']/$v['total']);
					if($stat['total']>0) $stat['active_percent']=round(100*$stat['active']/$v['total']);
					
					 
					
					$data[]=array('date'=>date("d.m.Y",$pdate),
								  'stat'=>$stat);
					 
													  
					
				}
				
				
				//$v['percent_active']=round(100*$data[1]['stat']['active_percent']/$data[0]['stat']['active_percent']);
				$v['percent_active']=round(100*$data[1]['stat']['active']/ $data[0]['stat']['active'], 2);
				$v['percent_inactive']=round(100*$data[1]['stat']['inactive']/ $data[0]['stat']['inactive'], 2);
				
				
				
				
				
				$v['data']=$data;
				
				$usersdata[$k]=$v;	
			}
			
			//получить общий отчет по данным
			$summary=array();
			foreach($_pdates as $k1=>$pdate){
				
				$active=0; $inactive=0;  $_total_sup_no= $this->CalcTotalSuppliers($dec2, $pdate);
				$_total_stat_no= $this->CalcStatSuppliers( $pdate);
				foreach($alls['data'] as $k2=>$v2){
					//if(count($v2['reason_ids'])>0) $inactive++;
					//if(($v2['user_id']==$v['id'])&&(count($v1['reason_ids'])>0)) $inactive++;
					if(count($v2['rdates'][$k1])>0) $inactive++;
				}
				$active=$_total_sup_no-$inactive;
				
				//echo " $_total_stat_no <br>";
				
				$summary[]=array(	'date'=>date("d.m.Y",$pdate),
											'inactive'=>$inactive,
											'active'=>$active,
											'total'=>$_total_sup_no,
											'total_stat'=>$_total_stat_no
											);
				
			}
			
			//total_sup_no
		
		
			
			$active_percent=round(100*$summary[1]['active']/ $summary[0]['active'],2);
			$inactive_percent=round(100*$summary[1]['inactive']/ $summary[0]['inactive'],2);
			
			$sm->assign('active_percent', $active_percent);
			$sm->assign('inactive_percent', $inactive_percent);
			
			$sm->assign('summary', $summary); 
		}else{
			$usersdata=$this->GetTotalManagers($dec2);
			foreach($usersdata as $k=>$v){
				$v['total']=$this->CalcTotalSuppliersByManager($dec2, $v['id']);
				$v['stat_total']=$this->CalcStatSuppliersByManager($v['id']);
				//как найти неактивных??? посчитать, сколько раз этот сотрудник попал в наш отчет на текущую дату
				$inactive=0;
				foreach($alls['data'] as $k1=>$v1){
					//if(in_array($v['id'], 	
					foreach($v1['resps'] as $k2=>$v2){
						if(($v2['user_id']==$v['id'])&&(count($v1['reason_ids'])>0)) $inactive++;
					}
				}
				
				$v['inactive']=$inactive;
				$v['active']=$v['total']-$inactive;
				
				$usersdata[$k]=$v;	
			}
			$sm->assign('stat_total_supplier_no', $this->CalcStatSuppliers());
		}
		
		$sm->assign('usersdata',$usersdata);
		
			
		return $sm->fetch($template);
	}
	
	
	
	
	
	
	
	
	//подсчет общего числа контрагентов по заданным менеджерам
	protected function CalcTotalSuppliersByManager($decorator, $user_id, $pdate=NULL){
		$sql='
		select    
		 count(distinct p.id)
		
		 from supplier as p 
			left join opf as po on p.opf_id=po.id 
			left join supplier_responsible_user as sr on sr.supplier_id=p.id
			left join user as u on u.id=sr.user_id
			left join supplier_sprav_city as ssc on ssc.supplier_id=p.id
			left join sprav_city as sc on ssc.city_id=sc.id
			left join  sprav_country as sc1 on sc1.id=sc.country_id
			left join user as crea on crea.id=p.created_id
		where 
			p.is_customer=1 and p.is_org=0 and p.is_active=1 and u.id="'.$user_id.'"
		';
		
		$db_flt=$decorator->GenFltSql(' and ');
				if(strlen($db_flt)>0){
					$sql.=' and '.$db_flt;
				//	$sql_count.=' where '.$db_flt;	
				}
		
		if($pdate!==NULL){
			$sql.=' and p.active_first_pdate<"'.$pdate.'"';
		}
		
		//echo $sql;		
		$set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
			
		$f=mysqli_fetch_array($rs);		
		
		return (int)$f[0];
	}
	
	
	
	//подсчет общего числа контрагентов по заданным менеджерам
	protected function CalcTotalSuppliers($decorator, $pdate=NULL){
		$sql='
		select    
		 count(distinct p.id)
		
		 from supplier as p 
			left join opf as po on p.opf_id=po.id 
			left join supplier_responsible_user as sr on sr.supplier_id=p.id
			left join user as u on u.id=sr.user_id
			left join supplier_sprav_city as ssc on ssc.supplier_id=p.id
			left join sprav_city as sc on ssc.city_id=sc.id
			left join  sprav_country as sc1 on sc1.id=sc.country_id
			left join user as crea on crea.id=p.created_id
		where 
			p.is_customer=1 and p.is_org=0 and p.is_active=1		
		';
		
		$db_flt=$decorator->GenFltSql(' and ');
				if(strlen($db_flt)>0){
					$sql.=' and '.$db_flt;
				//	$sql_count.=' where '.$db_flt;	
				}
		
		if($pdate!==NULL){
			$sql.=' and p.active_first_pdate<"'.$pdate.'"';
		}
		//echo $sql;		
		$set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
			
		$f=mysqli_fetch_array($rs);		
		
		return (int)$f[0];
	}
	
	//подсчет числа контрагентов по СТАТИСТИКЕ на дату по менеджерУ
	protected function CalcStatSuppliersByManager( $user_id, $pdate=NULL){
		if($pdate===NULL) $pdate=time();
		
		$_sni=new SuppliersUsersNum;
		$ret=$_sni->GetNum($pdate, $user_id);
		
		return $ret;
	}
	
	//подсчет числа контрагентов по СТАТИСТИКЕ на дату по менеджерАМ
	protected function CalcStatSuppliers( $pdate=NULL){
		if($pdate===NULL) $pdate=time();
		
		$_sni=new SuppliersUsersNum;
		$ret=$_sni->GetNumTotal($pdate);
		
		return $ret;
	}
	
	
	
	//формирование списка заданных менеджеров
	protected function GetTotalManagers($decorator){
		$sql='
		select    
		  distinct u.*
		
		 from supplier as p 
			left join opf as po on p.opf_id=po.id 
			inner join supplier_responsible_user as sr on sr.supplier_id=p.id
			inner join user as u on u.id=sr.user_id
			left join supplier_sprav_city as ssc on ssc.supplier_id=p.id
			left join sprav_city as sc on ssc.city_id=sc.id
			left join  sprav_country as sc1 on sc1.id=sc.country_id
			left join user as crea on crea.id=p.created_id
		where 
			p.is_customer=1 and p.is_org=0 and p.is_active=1		
		';
		
		$db_flt=$decorator->GenFltSql(' and ');
				if(strlen($db_flt)>0){
					$sql.=' and '.$db_flt;
				//	$sql_count.=' where '.$db_flt;	
				}
		$sql.=' order by u.name_s';
		//echo $sql;		
		$set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
		
		$arr=array();
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){	
			$f=mysqli_fetch_array($rs);		
			
			$arr[]=$f;	
		}
		
		return $arr;
	}
	
	
	
	
	
	
	
	//подсчет заданных менеджеров
	protected function CalcTotalManagers($decorator){
		$sql='
		select    
		 count(distinct u.id)
		
		 from supplier as p 
			left join opf as po on p.opf_id=po.id 
			inner join supplier_responsible_user as sr on sr.supplier_id=p.id
			inner join user as u on u.id=sr.user_id
			left join supplier_sprav_city as ssc on ssc.supplier_id=p.id
			left join sprav_city as sc on ssc.city_id=sc.id
			left join  sprav_country as sc1 on sc1.id=sc.country_id
			left join user as crea on crea.id=p.created_id
		where 
			p.is_customer=1 and p.is_org=0 and p.is_active=1		
		';
		
		$db_flt=$decorator->GenFltSql(' and ');
				if(strlen($db_flt)>0){
					$sql.=' and '.$db_flt;
				//	$sql_count.=' where '.$db_flt;	
				}
		
		//echo $sql;		
		$set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
			
		$f=mysqli_fetch_array($rs);		
		
		return (int)$f[0];
	}
	
	
	 
	
	
	
	 
	
	protected function GainSql($reason_id, $decorator){
		$sql='
		select    
		 distinct  p.id, "'.$reason_id.'" as reason_id, p.code, p.full_name, p.inn, p.kpp, p.legal_address, 
		 po.name as opf_name
		
		 from supplier as p 
			left join opf as po on p.opf_id=po.id 
			left join supplier_responsible_user as sr on sr.supplier_id=p.id
			left join user as u on u.id=sr.user_id
			left join supplier_sprav_city as ssc on ssc.supplier_id=p.id
			left join sprav_city as sc on ssc.city_id=sc.id
			left join  sprav_country as sc1 on sc1.id=sc.country_id
			left join user as crea on crea.id=p.created_id
		where 
			p.is_customer=1 and p.is_org=0 and p.is_active=1		
		';
		
		$db_flt=$decorator->GenFltSql(' and ');
				if(strlen($db_flt)>0){
					$sql.=' and '.$db_flt;
				//	$sql_count.=' where '.$db_flt;	
				}
		return $sql;	
	}
	
	
	public $reasons;
	function __construct(){
		$this->reasons=array(
			1=>'Нет лидов, нет звонков последние 3 месяца, не запланированы звонки, встречи, командировки на ближайшие 3 месяца, и дата заведения контрагента была более 3 месяцев назад',
			2=>'Есть холодные лиды, но нет звонков 1 месяц, и дата заведения контрагента была более 1 месяца назад',
			3=>'Есть теплые лиды, но нет звонков 2 недели, и дата заведения контрагента была более 2 недель назад',
			4=>'Есть горячие лиды, но нет звонков 1 неделю, и дата заведения контрагента была более 1 недели назад',
			5=>'Есть холодные лиды, но нет ни встреч, ни командировок 6 месяцев, не запланированы звонки, встречи, командировки на ближайшие 6 месяцев, и дата заведения контрагента была более 6 месяцев назад',
			6=>'Есть теплые лиды, но нет ни встреч, ни командировок 3 месяца, не запланированы звонки, встречи, командировки на ближайшие 3 месяца, и дата заведения контрагента была более 3 месяцев назад',
			7=>'Есть горячие лиды или тендер, но нет ни встреч, ни командировок 2 месяца, не запланированы звонки, встречи, командировки на ближайшие 2 месяца, и дата заведения контрагента была более 3 месяцев назад'
		
		);
	}
}
?>