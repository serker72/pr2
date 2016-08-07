<?
 
require_once('supplieritem.php');
require_once('orgitem.php');
require_once('opfitem.php');
 
require_once('sched.class.php');
 
require_once('user_s_group.php');


//отчет  Воронка продаж
class AnSchedFunnel{

	public function ShowData($pdate1, $pdate2, $_users=NULL, $limited_user=NULL,  $template, DBDecorator $dec2,  $pagename='files.php',  $do_it=false, $can_print=false,  &$alls, $result=NULL){
		/*
			echo date('d.m.Y H:i:s', $pdate1).' vs '; 
		
		echo date('d.m.Y H:i:s', $pdate2).' <br> '; */ 
		 
		 
		$alls=array();
		
		$sm=new SmartyAdm;
		 
		
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
			 
			 //найти массив сотрудников для итераций
			$_ug=new UsersSGroup;
			
			$users=$_ug->GetItemsArr(0,1,1);
			
			$our_users=array();
			foreach($users as $k=>$v){
				if(in_array($v['id'],$limited_user)) continue;
				if(($_users!==NULL)&&!in_array($v['id'], $_users)) continue;
				//Оставить всех сотрудников отдела продаж
				
			 	if(!( /*(($v['position_id']==33)||($v['position_id']==37))&&*/($v['department_id']==1) )) continue;
				
				$our_users[]=$v;
			}
			
			//print_r($our_users);
			//первая итерация - работаем по всем сотрудникам
			$alls[]=$this->UserIteration($pdate1,$pdate2,  $our_users, true);
			
			//остальные итерации - по отдельным сотрудникам
			foreach($our_users as $k=>$v) $alls[]=$this->UserIteration($pdate1, $pdate2, array($v));
			
		 
		    
 
			
		  /*echo '<pre>';
		  print_r($alls);
		  echo '</pre>';
		  */
		  
		  		  
		  $sm->assign('items',$alls);
		}
		
	   
	   
	   
	   
	  
	   $_user_ids=array('','','','');
	   $fields=$dec2->GetUris();
	   $user=''; $supplier=''; $city=''; $share_user='';  $branch='';
		foreach($fields as $k=>$v){
			
			//echo $v->GetValue();
			
		 
				
		 
			if($v->GetName()=='user') $user=$v->GetValue();
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
		 
		  
	   
	   
	      $link=$dec2->GenFltUri('&', $prefix);
	    $link=$pagename.'?'.eregi_replace('&sortmode=[[:digit:]]+','',$link).'&doSub'.$prefix.'=1';
		$sm->assign('link',$link);
		//$sm->assign('sortmode',$sortmode);
	   
	   //сколько записей
		$sm->assign('no', count($alls));
		//сколько сотрудников
		$sm->assign('sotr_no', count($_sotr_arr));
		
		
	
		 
		$sm->assign('can_print',$can_print);
		

		 
		$sm->assign('dates', $pdates);
		
		$sm->assign('can_edit',$can_edit);
		$sm->assign('can_call',$can_edit);
		
		$sm->assign('can_view_reasons',$can_view_reasons);
		
		
		
		$sm->assign('do_it',$do_it);	
	
		$sm->assign('pagename',$pagename);
		//$sm->assign('extended_an',$extended_an);	
		
		
	
		
		$sm->assign('usersdata',$usersdata);
		
			
		return $sm->fetch($template);
	}
	
	
	
	//итерация по сотруднику
	protected function UserIteration($pdate1, $pdate2, $users, $force_title=false){
		
		
	
		
		$_user_ids=array();
		foreach($users as $k=>$v) $_user_ids[]=$v['id'];
		
		$data=array(
			'caption'=>'',
			'actions'=>0,
			'kps'=>0,
			'leads'=>0,
			'contracts'=>0
		);
		
		/*if(count($users)>1) $data['caption']='Общая воронка продаж';
		else $data['caption']=$users[0]['name_s'];*/
		
		if($force_title) $data['caption']='Общая воронка продаж';
		else $data['caption']=$users[0]['name_s'];
		
		
		//1. действия - звонки, встречи, командировки. по каждому контрагенту считаем только один из звонков, одну из комадировок, одну из встреч
		
		
		//встреча + командировка
		$cter=0;
		$sql='select count(distinct s.id) from supplier as s
		where s.is_customer=1
			and s.id in(select sk.supplier_id from  
									sched_suppliers as sk 
									inner join sched as ss on ss.id=sk.sched_id
									where ss.is_confirmed_done=1 
											and 
											(
												ss.kind_id=3
												or
												(
												ss.kind_id=2
												and sk.not_meet=0											
												)
											)
											
											and (ss.confirm_done_pdate between "'.$pdate1.'" and "'.$pdate2.'")
											and ss.manager_id in('.implode(', ',$_user_ids).')
					)
					';
		$set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);				
		$cter+=(int)$f[0];
		$data['meets']=$cter;
		
		
		//командировка
		/*$cter=0;
		$sql='select count(distinct s.id) from supplier as s
		where s.is_customer=1
			and s.id in(select sk.supplier_id from  
									sched_suppliers as sk 
									inner join sched as ss on ss.id=sk.sched_id
									where ss.is_confirmed_done=1 
											and ss.kind_id=2
											and sk.not_meet=0
											and (ss.confirm_done_pdate between "'.$pdate1.'" and "'.$pdate2.'")
											and ss.manager_id in('.implode(', ',$_user_ids).')
					)
					';
		$set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);				
		$cter+=(int)$f[0];
		
		$data['missions']=$cter;*/
		
		//звонок
		$cter=0;
		$sql='select count(distinct s.id) from supplier as s
		where s.is_customer=1
			and s.id in(select sk.supplier_id from  
									sched_contacts as sk 
									inner join sched as ss on ss.id=sk.sched_id
									where ss.is_confirmed_done=1 
											and ss.kind_id=4
											and (ss.confirm_done_pdate between "'.$pdate1.'" and "'.$pdate2.'")
											and ss.manager_id in('.implode(', ',$_user_ids).')
					)
					';
		$set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);				
		$cter+=(int)$f[0];
		
		//echo "$sql <br>";
		$data['calls']=$cter;
		
		
		//2. КП - считаем все КП с датой утверждения внутри периода
		$sql='select count(s.id) from kp as s
		where s.is_confirmed_price=1
			and s.user_manager_id in('.implode(', ',$_user_ids).')
			and (s.confirm_price_pdate between "'.$pdate1.'" and "'.$pdate2.'")
			';
		//echo "$sql <br>";
		$set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);	
		$data['kps']=(int)$f[0];
		
		
		//3. ЛИДЫ в статусе кроме создан, аннулирован дата простановки 1й галочки
		$sql='select count(s.id) from lead as s
		where s.status_id not in(3,18)
			and s.manager_id in('.implode(', ',$_user_ids).')
			and (s.confirm_pdate between "'.$pdate1.'" and "'.$pdate2.'")
			';
		//echo "$sql <br>";
		$set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);	
		$data['leads']=(int)$f[0];
		
		//4. КОНТРАКТЫ - это тендеры в статусе выигран, дата простановки 2й галочки
		//+ договоры и приложения, утвержденные, дата: месяц, год - внутри нашего периода
		$cter=0;
		$sql='select count(s.id) from tender as s
		where s.status_id=30
			and s.manager_id in('.implode(', ',$_user_ids).')
			and (s.confirm_done_pdate between "'.$pdate1.'" and "'.$pdate2.'")
			';
		//echo "$sql <br>";
		$set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);	
		
		$cter+=(int)$f[0];
		
		//echo $cter.' ';
		
		$sql='select count(s.id) from plan_fact_fact as s
		where s.is_confirmed=1
			and s.user_id in('.implode(', ',$_user_ids).')
			and (s.month between "'.date('m', $pdate1).'" and "'.date('m',$pdate2).'")
			and (s.year between "'.date('Y', $pdate1).'" and "'.date('Y',$pdate2).'")
			';
		//echo "$sql <br>";
		$set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);	
		
		$cter+=(int)$f[0];
		//echo (int)$f[0];
		//echo $cter.' ';
		$data['contracts']=$cter;
		
		return $data;
		
	}
	
	
	
	
}
?>