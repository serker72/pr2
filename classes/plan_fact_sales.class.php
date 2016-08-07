<?

require_once('plan_fact_sales_item.php');
require_once('plan_fact_sales_group.php');

require_once('plan_fact_fact_item.class.php');
require_once('supplier_country_group.php');
require_once('supplier_city_item.php');

require_once('pl_currgroup.php');
require_once('pl_curritem.php');
require_once('user_pos_item.php');
require_once('user_s_group.php');

//план-факт продаж
class PlanFactSales{
	public $item; protected $years; protected $monthes; protected $monthes_no;
	public $currencies;
	
	protected $quartals;
	
	function __construct(){
		$this->item=new PlanFactSalesItem;
		$this->currencies=new PlCurrGroup;
		
		$this->years=array();
		for($i=2013; $i<=(int)date('Y')+1; $i++){
			 $this->years[]=$i;
			 
		}
		
		$this->monthes=array(); $this->monthes_no=array();
		for($i=1; $i<=12; $i++){
			//$this->monthes[]=array('no'=>$i, 'name'=>date('F', mktime(0,0,0,1,$i,2014)));
			$this->monthes_no[]=$i;
		}
		$this->monthes[]=array('no'=>1, 'name'=>'январь');
		$this->monthes[]=array('no'=>2, 'name'=>'‘евраль');		
		$this->monthes[]=array('no'=>3, 'name'=>'ћарт');
		$this->monthes[]=array('no'=>4, 'name'=>'јпрель');		
		$this->monthes[]=array('no'=>5, 'name'=>'ћай');
		$this->monthes[]=array('no'=>6, 'name'=>'»юнь');		
		$this->monthes[]=array('no'=>7, 'name'=>'»юль');
		$this->monthes[]=array('no'=>8, 'name'=>'јвгуст');		
		$this->monthes[]=array('no'=>9, 'name'=>'—ент€брь');
		$this->monthes[]=array('no'=>10, 'name'=>'ќкт€брь');		
		$this->monthes[]=array('no'=>11, 'name'=>'Ќо€брь');
		$this->monthes[]=array('no'=>12, 'name'=>'ƒекабрь');		
		
		$this->quartals[]=array('no'=>1, 'name'=>'1 квартал');
		$this->quartals[]=array('no'=>2, 'name'=>'2 квартал');
		$this->quartals[]=array('no'=>3, 'name'=>'3 квартал');
		$this->quartals[]=array('no'=>4, 'name'=>'4 квартал');
		
	}
	
	
	public function Show($year, $result, 
		$template, 
		$selected_currencies, //выбранные дл€ просмотра валюты в отделе
		$can_view_all=false, 
		$can_add_plan=false, 
		$can_edit_plan=false, 
		$can_add_fact=false, 
		$can_edit_fact=false, //права на корр-ку факта в рамках 31 дн€ после окончани€ периода
		$org_id=1,
		$can_edit_fact_super=false, //права на корр-ку факта в любой период
		$limited_user
		){
		$sm=new SmartyAdm();
		$alls=array();
		
		$users=array(); $_user_ids=array();
		$departments=array(); $_department_ids=array();
		
		
		if($limited_user!==NULL){
			 
			$lim_user_flt=' and id in('.implode(', ', $limited_user).')';
		}
		
			
		if(!$can_view_all){
			
			$_usg=new UsersSGroup;
			 $_usg->GetSubordinates($result['id'], $podd);
			
			//найти самого пользовател€ и его подчиненных (если есть)
			//все должны быть включены в п/ф	
			//отделы - только  относ€щиес€ к найденным пол-л€м
			
			//найдем пол-л€ и его подчиненных
			$sql='select * from user where  is_in_plan_fact_sales=1 and (id="'.$result['id'].'" or id in('.implode(', ', $podd).' )) '.$lim_user_flt.' order by name_s asc';
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				 
				foreach($f as $k=>$v) $f[$k]=stripslashes($v);
				
				if(($f['department_id']!=0)&&!in_array($f['department_id'], $_department_ids)) $_department_ids[]=$f['department_id'];
				
				$_user_ids[]=$f['id'];
				$users[]=$f;
			}
		 	
			//если пол-ль - рук-ль отдела (в дол-ти есть флаг is_ruk_otd)
			//то видит всех сотр-ков отдела
			$_pos=new UserPosItem;
			$pos=$_pos->Getitembyid($result['position_id']);
			if($pos['is_ruk_otd']){
				$sql='select * from user where  is_in_plan_fact_sales=1 and department_id="'.$result['department_id'].'" and id<>"'.$result['id'].'"  '.$lim_user_flt.' order by name_s asc';	
				
				//echo $sql;
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				for($i=0; $i<$rc; $i++){
					$f=mysqli_fetch_array($rs);
					 
					foreach($f as $k=>$v) $f[$k]=stripslashes($v);
					
					if(($f['department_id']!=0)&&!in_array($f['department_id'], $_department_ids)) $_department_ids[]=$f['department_id'];
					
					if(!in_array($f['id'],$_user_ids)){
						$_user_ids[]=$f['id'];
						$users[]=$f;
					}
				}
			}
			
			
			
			
			//отделы
			
			$dep_flt=''; 
			$dep_flt=' where p.id in( '.implode(', ',$_department_ids).')';
			$sql='select p.*, c.id as currency_id, c.signature from user_department as p left join pl_currency as c on c.id=p.default_currency_id '.$dep_flt.' order by p.ord desc';
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				 
				foreach($f as $k=>$v) $f[$k]=stripslashes($v);
				$departments[]=$f;
			}
			
			
		}else{
			//найти все отделы и все пол-лей в п/ф
			//отделы - только  относ€щиес€ к найденным пол-л€м
			
			
			//пол-ли 
			$sql='select * from user where  is_in_plan_fact_sales=1  '.$lim_user_flt.' order by name_s asc';
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				 
				foreach($f as $k=>$v) $f[$k]=stripslashes($v);
				if(($f['department_id']!=0)&&!in_array($f['department_id'], $_department_ids)) $_department_ids[]=$f['department_id'];
				
				$_user_ids[]=$f['id'];
				$users[]=$f;
			}
			
			
			//отделы
			$dep_flt=''; 
			$dep_flt=' where p.id in( '.implode(', ',$_department_ids).')';
			$sql='select p.*, c.id as currency_id, c.signature from user_department as p left join pl_currency as c on c.id=p.default_currency_id '.$dep_flt.' order by p.ord desc';
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				 
				foreach($f as $k=>$v) $f[$k]=stripslashes($v);
				$departments[]=$f;
			}
		 
			
		}
		
	//print_r($departments);
		
		//формируем таблицу
		$_curr_item=new PlCurrItem;
		foreach($departments as $dk=>$dv){
			//разбирать массив выбранных валют.
			//если дл€ данного отдела выбрана определенна€ валюта - работать с ней!
			//подставить ее в пол€ $dv[currency_id], $dv[signature]
			
			if(isset($selected_currencies[$dv['id']])){
				$curr_item=$_curr_item->GetItemById($selected_currencies[$dv['id']]);
				if($curr_item!==false){
					$dv['currency_id']=$curr_item['id'];
					$dv['signature']=$curr_item['signature'];	
				}
			}
			 
			//echo $dv['name'].' ';
			//в каждом отделе - выводим сотрудников, работающих в отчете и состо€щих в _user_ids
			
			$in_users=array();
			
			$user_flt=''; if(count($_user_ids)>0) $user_flt=' and id in('.implode(', ',$_user_ids).')';
			
			//добавить фильтр по из_актив и наличию записей в плане/факте.
			$user_flt1=''; 
			$user_flt1=' and ((is_active=1) or (is_active=0 and id in(select distinct user_id from  plan_fact_sales where year="'.$year.'" )) or (is_active=0 and id in(select distinct user_id from  plan_fact_fact where year="'.$year.'" and is_confirmed=1 ))) ';
			
			$sql1='select * from user where department_id="'.$dv['id'].'" and  is_in_plan_fact_sales=1 '.$user_flt.'  '.$user_flt1.' order by name_s asc';
			
			//echo $sql1;
			
			$set1=new mysqlset($sql1);
			$rs1=$set1->GetResult();
			$rc1=$set1->GetResultNumRows();
			
			//итого по мес€цам
			$itogo_by_monthes=array();
			foreach($this->monthes as $mk=>$mv){
				for($j=0; $j<=1; $j++){
					$itogo_by_monthes[]=array('no'=>$mv['no'],
										   'name'=>$mv['name'],
										  'plan_or_fact'=>$j,
										  'value'=>0
										  );
				}
			}
			
			//итого по отделу
			$itogo_by_dep=array();
			for($j=0; $j<=1; $j++){
				$itogo_by_dep[]=array(
										  'plan_or_fact'=>$j,
										  'value'=>0
										  );
			}
			
			
			//% по мес€цам
			$percents_by_monthes=array();
			foreach($this->monthes as $mk=>$mv){
			 
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
			
			
			//перебор сотрудников
			for($i=0; $i<$rc1; $i++){
				$f=mysqli_fetch_array($rs1);
				foreach($f as $k=>$v) $f[$k]=stripslashes($v);
				
				
				//перебор по мес€цам года.
				//сотруднику заносим массив мес€цев и план/факт
				$user_monthes=array(); 
				
				$itogo_by_user=array(
					array('plan_or_fact'=>0,
						  'value'=>0),
					array('plan_or_fact'=>1,
						  'value'=>0)	  
				);
				foreach($this->monthes as $mk=>$mv){
					
					//перебираем план и факт. значени€
					for($j=0; $j<=1; $j++){
						
						//если отдел - один из ќѕќ - то нужно подставл€ть сумму по реестру договоров
						//иначе - считаем как обычно.
						
						
						$sales=$this->GetSales($mv['no'],$year,$f['id'],$j, $dv['currency_id'], $dv['id'], $org_id, $can_add_plan, $can_edit_plan, $can_add_fact, $can_edit_fact, NULL, $can_edit_fact_super);
						
						//echo " $mv[no], $year, $f[id], $j, <br>";
						//print_r($sales); echo '<br>';
						
						$user_monthes[]=array('no'=>$mv['no'],
										   'name'=>$mv['name'],
										  'plan_or_fact'=>$j,
										  'can_modify'=>$sales['can_modify'],
										   
										//  добавить пол€ по корректировке факт 31 день
										  
										  'restricted_by_period'=>$sales['restricted_by_period'],
										  'data'=>$sales['data']);
										  
						//пополним итого по мес€цам
						foreach($itogo_by_monthes as $ik=>$iv){
							if(($iv['no']==$mv['no'])&&($iv['plan_or_fact']==$j)) {
								$iv['value']=$iv['value']+(float)$sales['data']['value'];
								//echo $iv['value'];
								
								$iv['value_f']=number_format($iv['value'],2,'.', ' ');
							}
							$itogo_by_monthes[$ik]=array();							
							$itogo_by_monthes[$ik]=$iv;
							//print_r($iv);
						}
						
						//пополним итого по сотр-ку
						foreach($itogo_by_user as $ik=>$iv){
							if(($iv['plan_or_fact']==$j)) {
								$iv['value']=$iv['value']+(float)$sales['data']['value'];
								//echo $iv['value'];
								$iv['value_f']=number_format($iv['value'],2,'.', ' ');
							}
							$itogo_by_user[$ik]=array();							
							$itogo_by_user[$ik]=$iv;
						}
						
						//пополним итого по отделу
						foreach($itogo_by_dep as $ik=>$iv){
							if(($iv['plan_or_fact']==$j)) {
								$iv['value']=$iv['value']+(float)$sales['data']['value'];
								//echo $iv['value'];
								$iv['value_f']=number_format($iv['value'],2,'.', ' ');
							}
							$itogo_by_dep[$ik]=array();							
							$itogo_by_dep[$ik]=$iv;
						}
					}
				}
				
				$f['user_monthes']=$user_monthes;
				$f['itogo_by_user']=$itogo_by_user;
				
				
				
				$in_users[]=$f;				
			}
			//print_r($itogo_by_dep);
			
			//найдем % по мес€цам				
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
			
			//найдем % вып. плана по всему отделу...
			$percents_by_dep=0; $plan=0; $fact=0;
			foreach($itogo_by_dep as $ik=>$iv){
				if($iv['plan_or_fact']==0){
					$plan=$iv['value'];
				}elseif($iv['plan_or_fact']==1){
					$fact=$iv['value'];
				}
			}
			if($plan>0) $percents_by_dep=round(100*$fact/$plan,2);
			else $percents_by_dep=0;
			
			
			$dv['plan']=$plan;
			$dv['fact']=$fact;
			
			$dv['plan_f']=number_format($plan, 2, '.', ' ');
			$dv['fact_f']=number_format($fact,2, '.', ' ');;
			
			//найдем норму вып. плана
			$days= floor((mktime(0,0,0,date('m'), date('d'), date('Y')) - mktime(0,0,0,1, 1, date('Y')) )/(24*60*60));
			//echo $days;
			$norma=$plan*$days/365;
			$dv['norma'] =round($norma,2);
			$dv['norma_f']= number_format(round($norma,2), 2, '.', ' ');;
			
			$dv['percents'] =$percents_by_dep;
			
			
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
				
				$iv['plan_f']=number_format($iv['plan'],2,'.', ' ');
				$iv['fact_f']=number_format($iv['fact'],2,'.', ' ');
				$percents_by_quart[$ik]=$iv;
			}
			
			
			$alls[]=array('dep'=>$dv,
						  'users'=>$in_users,
						  'itogo_by_monthes'=>$itogo_by_monthes,
						  'percents_by_monthes'=>$percents_by_monthes,
						  'itogo_by_dep'=>$itogo_by_dep,
						  'percents_by_dep'=>$percents_by_dep,
						  'percents_by_quart'=>$percents_by_quart
						  );
		}
		
		
		$sm->assign('items', $alls);
		 
		 
		//страны
		$_cous=new SupplierCountryGroup;
		$cous=$_cous->GetItemsArr();
		$sm->assign('cous', $cous);
		 
		//сотрудники, кто может редактировать любой факт продаж
		$_usg=new UsersSGroup;
		$users=$_usg->GetUsersByRightArr('w', 813);
		$sm->assign('users_instead', $users);
		
		 
		//print_r($itogo_by_monthes);
		//валюты
		$sm->assign('currencies',$this->currencies->GetItemsArr());
		
		
		$sm->assign('years', $this->years);
		$sm->assign('monthes', $this->monthes);

		$sm->assign('year', $year);
		
		$sm->assign('can_view_all', $can_view_all);
		$sm->assign('can_add_plan', $can_add_plan);
		$sm->assign('can_edit_plan', $can_edit_plan);
		$sm->assign('can_add_fact', $can_add_fact);
		$sm->assign('can_edit_fact', $can_edit_fact);
		
		
		$sm->assign('can_edit_fact_super', $can_edit_fact_super);
		
		/*$_si=new SupplierCityItem;
		$city=$_si->GetFullCity(2104);
		print_r($city);
		*/
		
		
		return $sm->fetch($template);
	}
	
	
	
	public function GetSales($month, $year, $user_id, $plan_or_fact, $currency_id,  $department_id, $org_id, $can_add_plan=false, 
		$can_edit_plan=false, 
		$can_add_fact=false, 
		$can_edit_fact=false,
		$dec=NULL,
		$can_edit_fact_super=false
		){
		/*
		array('can_modify'=>
			'data'=>
			
			);
		*/	
		
		
		
		if(($plan_or_fact==1)&&in_array($department_id, array(1,3,4))){
			$_pff=new PlanFactFactItem;
			$result=$_pff->GetSales($month, $year, $user_id, $plan_or_fact, $currency_id,  $department_id, $org_id, $can_add_plan, 
		$can_edit_plan, 
		$can_add_fact, 
		$can_edit_fact,
		$dec,
		$can_edit_fact_super);
			
		}else{
			//echo 'zz';
			$result=$this->item->GetSales($month, $year, $user_id, $plan_or_fact, $currency_id,  $department_id, $org_id, $can_add_plan, 
		$can_edit_plan, 
		$can_add_fact, 
		$can_edit_fact,
		$dec,
		$can_edit_fact_super);
		}
		return $result;	
	}
	
	
	public function GetMonthByNumber($no){
		
		foreach($this->monthes as $k=>$v){
			if($v['no']==$no) return $v['name'];
		}
	}
	
	
	public function GetMonthes(){
		return $this->monthes;
	}
}
?>