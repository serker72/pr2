<?
require_once('price_kind_group.php');
require_once('kpitem.php');

require_once('posgroupgroup.php');
require_once('pl_prodgroup.php');
require_once('kpnotesgroup.php');

require_once('kp_form_item.php');


require_once('pl_currgroup.php');
require_once('pl_curritem.php');
require_once('plan_fact_sales.class.php');
require_once('supplier_country_group.php');
require_once('supplier_city_item.php');
require_once('currency/currency_solver.class.php');

class AnPlanFactSales{
	public $opo_dep_ids;
	
	 
	public function ShowData(
	$org_id, 
	
	$prefix,
	$template, 
	DBDecorator $dec,
	DBDecorator $dec1,
	DBDecorator $dec2,
	DBDecorator $dec3,
	$pagename='files.php', 
	$do_it=false, 
	$can_print=false,
	$result=NULL,
	$can_view_all=false
	){
		
		
		$_pk=new PriceKindGroup;
		$_prg=new PlProdGroup;
		$_pgg=new PosGroupGroup;	
		$_kp=new KpItem;
		$_notes_group=new KpNotesGroup;
		
		$_pfs=new PlanFactSales;
		$_currs=new PlCurrGroup;
		$_countries=new SupplierCountryGroup;
		$_curr=new PlCurrItem;
		
		$pdate2+=24*60*60-1;
		
		
		
		
		
		$sm=new SmartyAdm;
		
		
		$_user_ids=NULL;
		if(!$can_view_all){
			
			$_user_ids=array();
			//найти самого пользователя и его подчиненных (если есть)
			//все должны быть включены в п/ф	
			//отделы - только  относящиеся к найденным пол-лям
			
			$_usg=new UsersSGroup;
			 $_usg->GetSubordinates($result['id'], $podd);
			
			//найдем пол-ля и его подчиненных
			$sql='select * from user where  is_in_plan_fact_sales=1 and (id="'.$result['id'].'"  or id in('.implode(', ', $podd).' ))   order by name_s asc';
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				 
				foreach($f as $k=>$v) $f[$k]=stripslashes($v);
				
				//if(($f['department_id']!=0)&&!in_array($f['department_id'], $_department_ids)) $_department_ids[]=$f['department_id'];
				
				$_user_ids[]=$f['id'];
				$users[]=$f;
			}
		 	
			//если пол-ль - рук-ль отдела (в дол-ти есть флаг is_ruk_otd)
			//то видит всех сотр-ков отдела
			$_pos=new UserPosItem;
			$pos=$_pos->Getitembyid($result['position_id']);
			if($pos['is_ruk_otd']){
				$sql='select * from user where  is_in_plan_fact_sales=1 and department_id="'.$result['department_id'].'" and id<>"'.$result['id'].'"  order by name_s asc';	
				
				//echo $sql;
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				for($i=0; $i<$rc; $i++){
					$f=mysqli_fetch_array($rs);
					 
					foreach($f as $k=>$v) $f[$k]=stripslashes($v);
					
					//if(($f['department_id']!=0)&&!in_array($f['department_id'], $_department_ids)) $_department_ids[]=$f['department_id'];
					
					if(!in_array($f['id'],$_user_ids)){
						$_user_ids[]=$f['id'];
						$users[]=$f;
					}
				}
			}
			
			 
			//echo 'zzzzzzzz';
			
		}
		
		
		
		//отберем пользователей и отделы
		//добавить фильтр по из_актив и наличию записей в плане/факте.
		//получить год из $dec2->AddEntry(new UriEntry('year',date('Y')));
		
		  $fields=$dec2->GetUris();
		  foreach($fields as $k=>$v){
 			  if($v->GetName()=='year'){
				 $year=$v->GetValue();  
			  }
			  
		  }
		
		$user_flt1=''; 
			$user_flt1=' and ((p.is_active=1) or (p.is_active=0 and p.id in(select distinct user_id from  plan_fact_sales where year="'.$year.'" )) or (p.is_active=0 and p.id in(select distinct user_id from  plan_fact_fact where year="'.$year.'" and is_confirmed=1 ))) ';
		
		
		$users=array();
		
		$sql='select p.*, dep.name as dep_name, dep.default_currency_id
			 from user as p inner join user_department as dep on dep.id=p.department_id
			 where
			 	  p.is_in_plan_fact_sales=1
				  
				
				'.$user_flt1;
		if($_user_ids!==NULL) $sql.=' and p.id in('.implode(', ',$_user_ids).')';	
				
		$db_flt=$dec1->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			
			$db_flt=' and '.$db_flt;
		}
				
		$sql.=$db_flt;
		
		$ord_flt=$dec1->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}
		
		//echo $sql;
		
		
		$year=date('Y');
		$_monthes=$_pfs->GetMonthes();
		$rep_monthes=array();
		$currency_id=CURRENCY_DEFAULT_ID;
		
		$_department_ids=array(); 
		 
		//найдем курсы валют
		$_csolver=new CurrencySolver;
		$rates=$_csolver->GetActual();
		
		
		if($do_it){  
		  $set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
		  $rs=$set->GetResult();
		  $rc=$set->GetResultNumRows();
		  
		 // echo $sql.'<br>';
		 // echo ' результат: '.$rc;
		  
		  //формируем массив месяцев, находим выбранный год
		  //найдем валюту
		 
		  $fields=$dec2->GetUris();
		  foreach($fields as $k=>$v){
			  if($v->GetName()=='month'){
				 //echo $v->GetName().'='.$v->GetValue().'<br>';  
				 foreach($_monthes as $kk=>$vv) if($vv['no']==$v->GetValue()) $rep_monthes[]=$vv;
			  }
			  if($v->GetName()=='year'){
				 $year=$v->GetValue();  
			  }
			  if($v->GetName()=='currency_id'){
				 $currency_id=$v->GetValue();  
			  }
		  }
		  $curr=$_curr->GetItemById($currency_id);
		  //print_r($rep_monthes);
		  
		  //echo 'Валюта отчета: '; print_r($curr);
		 
		
		 // print_r($rates);
		   
		  
		  //итого по месяцам
		  $itogo_by_monthes=array();
		  foreach($rep_monthes as $mk=>$mv){
			  for($j=0; $j<=1; $j++){
				  $itogo_by_monthes[]=array('no'=>$mv['no'],
										 'name'=>$mv['name'],
										'plan_or_fact'=>$j,
										'value'=>0
										);
			  }
		  }
		  
		  //итого по отчету
		  $itogo_by_rep=array();
		  for($j=0; $j<=1; $j++){
			  $itogo_by_rep[]=array(
										'plan_or_fact'=>$j,
										'value'=>0
										);
		  }
		  
		  //% по месяцам
		  $percents_by_monthes=array();
		  foreach($rep_monthes as $mk=>$mv){
		   
				  $percents_by_monthes[]=array('no'=>$mv['no'],
										 'name'=>$mv['name'],
										
										'value'=>0
										);
			   
		  }
		  
		  
		  //выполнение по кварталам
		  $percents_by_quart=array();
		  foreach($this->quartals as $mk=>$mv){
			  $percents_by_quart[]=array('no'=>$mv['no'],
										 'name'=>$mv['name'],
										
										'plan'=>0,
										'fact'=>0,
										'value'=>0
										);	
		  }
			
		  
		  
		  //!!!!!!! перебор сотрудников - основной цикл
		  for($i=0; $i<$rc; $i++){
			  
			  
			  $f=mysqli_fetch_array($rs);
			  foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			  
			  if(($f['department_id']!=0)&&!in_array($f['department_id'], $_department_ids)) $_department_ids[]=$f['department_id'];
			  
			  //перебор по месяцам года.
			  //сотруднику заносим массив месяцев и план/факт
			  $user_monthes=array(); 
			  
			  $itogo_by_user=array(
				  array('plan_or_fact'=>0,
						'value'=>0),
				  array('plan_or_fact'=>1,
						'value'=>0)	  
			  );
			  
			  //echo $f['default_currency_id'];
			  
			  $user_monthes=array();
			  foreach($rep_monthes as $mk=>$mv){
					
				//перебираем план и факт. значения
				for($j=0; $j<=1; $j++){
			  		
					 
					//ограничение по оборудованию:
					//если сотрудник - не в ОПО
					//и это факт продаж
					//и есть фильтры по фактам продаж -
					//значит возвращать нулевые данные - НЕВЕРНО, оставляем как для отделов ОПО
					if(($j==1)&&(!in_array($f['department_id'], $this->opo_dep_ids))){
						
						/*$sales=array(
							'can_modify'=>false,
							'restricted_by_period'=>false,
							$data=>array(
								'department_id'=>$f['department_id'],
								'user_id'=>$f['id'],
								'plan_or_fact'=>1,
								'month'=>$mv['no'],
								'year'=>$year,
								'currency_id'=>$f['default_currency_id'],
								'value'=>0
							) 
		
						);*/
						$sales=$_pfs->GetSales($mv['no'],$year, $f['id'],$j,  $f['default_currency_id'],  $f['department_id'],  $org_id,
					false,false,false,false, $dec3);
					}else{ 
						$sales=$_pfs->GetSales($mv['no'],$year, $f['id'],$j,  $f['default_currency_id'],  $f['department_id'],  $org_id,
					false,false,false,false, $dec3);
					}
						
					
					//если совпадает $f['default_currency_id'] - выбранная валюта отдела
					// с $currency_id - выбранная валюта отчета - то сумма остается без изменений.
					//иначе - сконвертировать из $f['default_currency_id'] в  $currency_id
					$sales['data']['value']=CurrencySolver::Convert($sales['data']['value'], $rates,$f['default_currency_id'],  $currency_id);
						
					  //echo " $mv[no], $year, $f[id], $j, <br>";
					//  print_r($sales); echo '<br>';
					
			 
					  
					$user_monthes[]=array('no'=>$mv['no'],
										   'name'=>$mv['name'],
										  'plan_or_fact'=>$j,
										 
										  'value'=>round((float)$sales['data']['value'],2),
										   'value_f'=>number_format(round((float)$sales['data']['value'],2),2,'.',' '))
										  
										  ;
					 
					 
					//пополним итого по месяцам
					foreach($itogo_by_monthes as $ik=>$iv){
						if(($iv['no']==$mv['no'])&&($iv['plan_or_fact']==$j)) {
							$iv['value']=round($iv['value']+(float)$sales['data']['value'],2);
							//echo $iv['value'];
							$iv['value_f']=number_format($iv['value'], 2, '.', ' ');
						}
						$itogo_by_monthes[$ik]=array();							
						$itogo_by_monthes[$ik]=$iv;
						//print_r($iv);
					}
					
					//пополним итого по сотр-ку
					foreach($itogo_by_user as $ik=>$iv){
						if(($iv['plan_or_fact']==$j)) {
							$iv['value']=round($iv['value']+(float)$sales['data']['value'],2);
							//echo $iv['value'];
							$iv['value_f']=number_format($iv['value'], 2, '.', ' ');
						}
						$itogo_by_user[$ik]=array();							
						$itogo_by_user[$ik]=$iv;
					}  
					
					//пополним итого по отчету
					foreach($itogo_by_rep as $ik=>$iv){
						if(($iv['plan_or_fact']==$j)) {
							$iv['value']=round($iv['value']+(float)$sales['data']['value'],2);
							//echo $iv['value'];
							$iv['value_f']=number_format($iv['value'], 2, '.', ' ');
						}
						$itogo_by_rep[$ik]=array();							
						$itogo_by_rep[$ik]=$iv;
					}
									  
				}
			  }
			  
			  $f['user_monthes']=$user_monthes;
			  $f['itogo_by_user']=$itogo_by_user;
			  
			  $users[]=$f;
		  }
		}
		
		//$sm->assign('users',$users);
		
		//нужно разбить сотрудников по отделам!
		//$_department_ids[]
		//строим массив по отделам
		$report_departments=array();
		//$users_by_deps=array();
		foreach($users as $k=>$v){
			//
			//print_r($v);
			
			$_dep=array('id'=>$v['department_id'],
						'name'=>$v['dep_name'],
						'users'=>array(),
						'dep_monthes'=>array(),
						'dep_percent_monthes'=>array(),
						'itogo_plan_dep'=>0,
						'itogo_plan_dep_f'=>0,
						'itogo_fact_dep'=>0,
						'itogo_fact_dep_f'=>0,
						'percents_by_dep'=>0
						);
						
			if(!in_array($_dep, $report_departments)) $report_departments[]=$_dep;			
		}
			
		
		
		//занесем пользователей и их план/факт данные по месяцам в отделы
		foreach($report_departments as $k=>$v){
			foreach($users as $kk=>$user){
				if($user['department_id']==$v['id']){
					 $v['users'][]=$user;	
					
						 
				}
			}
			$report_departments[$k]=$v;
		}
		
		//найти итоговые суммы план/факт по отделу
		foreach($report_departments as $k=>$v){
			$itogo_plan_dep=0;
			$itogo_fact_dep=0;
			
			//$dep_monthes=array();
			foreach($v['users'] as $kk=>$user){
				foreach($user['user_monthes'] as $kkk=>$vvv){
					if($vvv['plan_or_fact']==0) $itogo_plan_dep+=$vvv['value'];
					elseif($vvv['plan_or_fact']==1) $itogo_fact_dep+=$vvv['value'];	
					
					////найти суммы план/факт по отделу
					// $f['user_monthes']=$user_monthes;
					 
					/*$dep_monthes[]=array('no'=>$vvv['no'],
										   'name'=>$vvv['name'],
										  'plan_or_fact'=>$j,
										 
										  'value'=>round((float)$sales['data']['value'],2),
										   'value_f'=>number_format(round((float)$sales['data']['value'],2),2,'.',' '))
										  
										  ; */
					
				}
			}
			
			//$v['dep_monthes']=$dep_monthes;
			
			$v['itogo_plan_dep']=$itogo_plan_dep;
			$v['itogo_plan_dep_f']=number_format($itogo_plan_dep,2,'.', ' ');
			
			$v['itogo_fact_dep']=$itogo_fact_dep;
			$v['itogo_fact_dep_f']=number_format($itogo_fact_dep,2,'.', ' ');
			
			//найдем процент выполнения плана отделом за год
			if($itogo_plan_dep>0) $percents_by_dep=round(100*$itogo_fact_dep/$itogo_plan_dep,2);
			else $percents_by_dep=0;
			
			$v['percents_by_dep']=$percents_by_dep;
			$v['percents_by_dep_f']=number_format($percents_by_dep,2,'.', ' ');
			
			
			$report_departments[$k]=$v;
		}
		
		//найти суммы план/факт по отделу с разбивкой по месяцам, процент выполнения плана за месяцы отделом
		foreach($report_departments as $k=>$dep){
			$dep_monthes=array(); $dep_percent_monthes=array();
			
			//перебираем месяцы отчета
			foreach($rep_monthes as $k1=>$month){
				//перебираем сотрудников и их данные по месяцам
				
				$month_plan=0; $month_fact=0; 
				foreach($dep['users'] as $k2=>$user)  foreach($user['user_monthes'] as $k3=>$data) if($month['no']==$data['no']){
					if($data['plan_or_fact']==0) $month_plan+=$data['value'];
					elseif($data['plan_or_fact']==1) $month_fact+=$data['value'];	
				}
				
				$dep_monthes[]=array('no'=>$month['no'],
										   'name'=>$month['name'],
										  'plan_or_fact'=>0,										 
										  'value'=>$month_plan,
										   'value_f'=>number_format($month_plan,2,'.',' ')); 
										   
				$dep_monthes[]=array('no'=>$month['no'],
										   'name'=>$month['name'],
										  'plan_or_fact'=>1,										 
										  'value'=>$month_fact,
										   'value_f'=>number_format($month_fact,2,'.',' ')); 
				
				$month_percent=0;
				//найдем процент выполнения плана отделом за этот месяц
				if($month_plan>0) $month_percent=round(100*$month_fact/$month_plan,2);
				else $month_percent=0;
										   
				$dep_percent_monthes[]=array('no'=>$month['no'],
										   'name'=>$month['name'],
										 								 
										  'value'=>$month_percent,
										   'value_f'=>number_format($month_percent,2,'.',' ')); 									   
			}
			
			$dep['dep_percent_monthes']=$dep_percent_monthes;
			$dep['dep_monthes']=$dep_monthes;
			$report_departments[$k]=$dep;
		}
		
		 
		
		/*
		echo '<pre>';
		var_dump($report_departments);
		echo '</pre>';
		*/
		
		
		
		$sm->assign('dep_users',$report_departments);
		
		//найдем % по месяцам				
		foreach($percents_by_monthes as $pk=>$pv){
			$plan=0; $fact=0;
			foreach($itogo_by_monthes as $mk=>$mv){
				if(($pv['no']==$mv['no'])&&($mv['plan_or_fact']==0)){
					$plan=$mv['value'];
				}elseif(($pv['no']==$mv['no'])&&($mv['plan_or_fact']==1)){
					$fact=$mv['value'];
				}
			}
			
			if($plan>0) $percent=round(100*$fact/$plan,2);
			else $percent=0;
			$pv['value']=$percent;
			$percents_by_monthes[$pk]=$pv;
		}
		
		//найдем % вып. плана по всему отчету...
		$percents_by_rep=0; $plan=0; $fact=0;
		foreach($itogo_by_rep as $ik=>$iv){
			if($iv['plan_or_fact']==0){
				$plan=$iv['value'];
			}elseif($iv['plan_or_fact']==1){
				$fact=$iv['value'];
			}
		}
		if($plan>0) $percents_by_rep=round(100*$fact/$plan,2);
		else $percents_by_rep=0;
		
		$plan_f=number_format($plan, 2, '.', ' ');
		
		$sm->assign('plan',$plan_f);
		
		$fact_f=number_format($fact, 2, '.', ' ');
		$sm->assign('fact',$fact_f);
		
		//найдем норму вып. плана
		$days= floor((mktime(0,0,0,date('m'), date('d'), date('Y')) - mktime(0,0,0,1, 1, date('Y')) )/(24*60*60));
		//echo $days;
		$norma=$plan*$days/365;
		$sm->assign('norma',round($norma,2));
		
		$sm->assign('norma_f',number_format(round($norma,2),2,'.', ' '));
		
		//найти квартальное выполнение плана
			foreach($percents_by_quart as $ik=>$iv){
				//$iv[no]
				foreach($itogo_by_monthes as $mk=>$mv){
					/*$is_in_quart=(($mv['no']*($iv['no']-1))>$iv['no'])&&
									;
					*/
					$is_in_quart=false;
					if($iv['no']==1){
						if(($mv['no']==1)||	($mv['no']==2)||($mv['no']==3)){
							$is_in_quart=true;	
						}
					}elseif($iv['no']==2){
						if(($mv['no']==4)||	($mv['no']==5)||($mv['no']==6)){
							$is_in_quart=true;	
						}
					}elseif($iv['no']==3){
						if(($mv['no']==7)||	($mv['no']==8)||($mv['no']==9)){
							$is_in_quart=true;	
						}
					}elseif($iv['no']==4){
						if(($mv['no']==10)||($mv['no']==11)||($mv['no']==12)){
							$is_in_quart=true;	
						}
					}
					
					if($is_in_quart&&($mv['plan_or_fact']==0)){
						//echo $iv['name'].':'.$mv['name'].'<br>';
						$iv['plan']+=$mv['value'];
					}elseif($is_in_quart&&($mv['plan_or_fact']==1)){
						//echo $iv['name'].':'.$mv['name'].'<br>';
						$iv['fact']+=$mv['value'];
					}
					
					$percents_by_quart[$ik]=$iv;
				}
			}
			foreach($percents_by_quart as $ik=>$iv){
				if($iv['plan']!=0) $iv['value']=round(100*$iv['fact']/$iv['plan'],2);
				else $iv['value']=0;
				
				$iv['plan_f']=number_format($iv['plan'], 2, '.', ' ');
				$iv['fact_f']=number_format($iv['fact'], 2, '.', ' ');
				
				$iv['value_f']=number_format($iv['value'], 2, '.', ' ');
				$percents_by_quart[$ik]=$iv;
			}
		
		
		
		//заполним шаблон полями
		 
		$current_eq_name='';
		$current_group_id='';
		$current_producer_id='';
		$current_two_group_id='';
		
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			if($v->GetName()=='eq_name') $current_eq_name=$v->GetValue();
		
			if($v->GetName()=='group_id') $current_group_id=$v->GetValue();
			if($v->GetName()=='producer_id') $current_producer_id=$v->GetValue();
			if($v->GetName()=='two_group_id') $current_two_group_id=$v->GetValue();
			
			//echo $v->GetName().'='.$v->GetValue().'<br>';
			$sm->assign($v->GetName(),$v->GetValue());
		}
		if($current_eq_name!=''){
			$current_group_id='0';
			$current_producer_id='0';
			$current_two_group_id='0';
			$sm->assign('group_id',$current_group_id);
			$sm->assign('producer_id',$current_producer_id);
			$sm->assign('two_group_id',$current_two_group_id);
			
		}
		
		
		//виды цен 
		$price_kinds=$_pk->GetItemsByFieldsArr(array('is_calc_price'=>1));
		$sm->assign('price_kinds',$price_kinds);	
		
		//годы
		$years=array();
		for($i=2013; $i<=(int)date('Y')+1; $i++){
			$years[]=$i;	
		}
		$sm->assign('years', $years);
		
		//месяцы
		$monthes=$_pfs->GetMonthes();
		$fields=$dec->GetUris(); $_selected_monthes=array();
		foreach($fields as $k=>$v){
			if(($v->GetName()=='month')&&!in_array($v->GetValue(), $_selected_monthes)) $_selected_monthes[]=$v->GetValue();
		}
		foreach($monthes as $k=>$v){
			if(in_array($v['no'], $_selected_monthes)) {
				$v['is_selected']=true;
				$monthes[$k]=$v;	
			}
		}
		
		$sm->assign('monthes', $monthes);
		
		
		
		
		//валюты
		$sm->assign('currencies',$_currs->GetItemsArr());
		$sm->assign('curr',$curr);
		
		//страны
		$sm->assign('cous', $_countries->GetItemsArr());
		 
		
		//отделы
		$departments=array();
		$fields=$dec->GetUris(); $_selected_deps=array();
		foreach($fields as $k=>$v){
			if(($v->GetName()=='department_id')&&!in_array($v->GetValue(), $_selected_deps)) $_selected_deps[]=$v->GetValue();
		}
		
		 
		
		$sql='select p.*, c.id as currency_id, c.signature from user_department as p left join pl_currency as c on c.id=p.default_currency_id 
		where p.id in(select distinct department_id from user where is_active=1 and is_in_plan_fact_sales=1 )
		 order by p.ord desc';
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			 
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$departments[]=$f;
		}
		
		 
		foreach($departments as $k=>$v){
			if(in_array($v['id'], $_selected_deps)) {
				$v['is_selected']=true;  
				$departments[$k]=$v;	
			}
		}
		//print_r($departments);
		
		$sm->assign('departments',$departments);
		
		
		
		//города
		$cities=array();
		$fields=$dec->GetUris();  $_city_ids=array(); 
		foreach($fields as $k=>$v){
			if(eregi('^city_selected_',$v->GetName())&&!in_array($v->GetValue(), $_city_ids)) $_city_ids[]=$v->GetValue();
		}
		$_city=new SupplierCityItem;
		foreach($_city_ids as $k=>$v){
			$city=$_city->GetFullCity($v);
			$cities[]=$city;
		}
		$sm->assign('cities',$cities);
		
		//месяцы
		$sm->assign('rep_monthes', $rep_monthes);
		$sm->assign('rep_monthes_count', count($rep_monthes));
		
		//год
		$sm->assign('year', $year);
		
		//курс валют
		$sm->assign('rates', $rates);
		
		
		//итого:
		$sm->assign('itogo_by_monthes', $itogo_by_monthes);
		$sm->assign('itogo_by_rep', $itogo_by_rep);
		$sm->assign('percents_by_monthes', $percents_by_monthes);
		$sm->assign('percents_by_rep',$percents_by_rep);
		
		$sm->assign('percents_by_quart',$percents_by_quart);
		
		
		
		
		$supplier='';
		
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			 
				if($v->GetName()=='supplier_name') $supplier=$v->GetValue();
		 
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
		
		
		
		//ссылка для кнопок сортировки
		$link=$dec->GenFltUri('&',$prefix );
		$link=$pagename.'?'.eregi_replace('&sortmode'.$prefix.'=[[:digit:]]+','',$link).'&doSub'.$prefix.'=1';
		$sm->assign('link',$link);
		//$sm->assign('sortmode',$sortmode);
		
		//echo $link;
		
		$sm->assign('pagename',$pagename);
		
		$sm->assign('can_print',$can_print);
		$sm->assign('can_view_all',$can_view_all);
		 
		$sm->assign('do_it',$do_it);	
		
	 	$sm->assign('prefix',$prefix);
		
		
		$sm->assign('pagename',$pagename);
			
		return $sm->fetch($template);
	}
	
	
	
	
	
	protected $quartals;
	function __construct(){
		
		$this->quartals[]=array('no'=>1, 'name'=>'1 квартал');
		$this->quartals[]=array('no'=>2, 'name'=>'2 квартал');
		$this->quartals[]=array('no'=>3, 'name'=>'3 квартал');
		$this->quartals[]=array('no'=>4, 'name'=>'4 квартал');
		
		$this->opo_dep_ids=array(1,3,4);
		
	}
	
}
?>