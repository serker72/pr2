<?
require_once('price_kind_group.php');
require_once('kpitem.php');

require_once('posgroupgroup.php');
require_once('pl_prodgroup.php');
require_once('kpnotesgroup.php');

require_once('kp_form_item.php');
require_once('rl/rl_man.php');

require_once('kp_rights_group.php');


//отчет рентабельность КП

class AnKpRent{
	public $view_rules; //экземпляр класса группы правил просмотра КП
	public $_currency_solver, $rates;
	
	function __construct(){
		$this->view_rules=new KpRightsGroup;
		$this->_currency_solver=new CurrencySolver;
				$this->rates=$this->_currency_solver->GetActual();	
	}
	
	
	
	public function ShowData(
	$org_id, $pdate1, $pdate2,
	$prefix,
	$template, 
	DBDecorator $dec,
	$pagename='files.php', 
	$do_it=false, 
	$can_print=false,
	$result=NULL,
	$can_edit=false,
	$can_view_re_extended=false,
	$can_view_all=false 
	 
	){
		$_users=array();
		$_suppliers=array();
		
		$_pk=new PriceKindGroup;
		$_prg=new PlProdGroup;
		$_pgg=new PosGroupGroup;	
		$_kp=new KpItem;
		$_notes_group=new KpNotesGroup;
		
		$_rl=new RLMan;
		
		$pdate2+=24*60*60-1;
		
		
		
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			
			$db_flt=' and '.$db_flt;
		}
		
		
		
		
		//фильтровать оборудование из закрытых категорий, пр-лей
		$flt= ''; $flts=array();
		$restricted_prods=$_rl->GetBlockedItemsArr($result['id'], 34, 'w', 'pl_producer', 0);
		if(count($restricted_prods)>0) $flts[]='  eq.producer_id not in('.implode(', ', $restricted_prods).') ';
		
		//запросить из базы список закрытых для пол-ля категорий
		$restricted_cats=$_rl->GetBlockedItemsArr($result['id'], 1, 'w', 'catalog_group', 0);
		if(count($restricted_cats)>0){
			//если закрыта подгруппа
			$flts[]='  eq.group_id not in('.implode(', ', $restricted_cats).') ';
			//если закрыта группа - учесть ее подгруппы
			$flts[]='  eq.group_id not in(select id from catalog_group where parent_group_id in('.implode(', ', $restricted_cats).') )';
		}
		
		
		
		
		$sm=new SmartyAdm;
		
		
		
		
		$sql='select p.*,
					
					 sp.full_name as supplier_name,  
					spo.name as opf_name, 
					c.name, c.position, 
					u.name_s as confirmed_price_name, u.login as confirmed_price_login,
					pk.name as price_kind_name,
					sd.name as supply_pdate_name,
					st.name as status_name,
					
					mn.id as manager_id, u.name_s as  manager_name, u.login as manager_login,
					curr.signature as profit_signature
				 
					
				from kp as p	
				
					 left join supplier as sp on p.supplier_id=sp.id
					left join opf as spo on spo.id=sp.opf_id 
								
					
					left join user as u on p.user_confirm_price_id=u.id
					left join user as mn on p.manager_id=mn.id
					left join pl_price_kind as pk on p.price_kind_id=pk.id
					left join kp_supply_pdate_mode as sd on p.supply_pdate_id=sd.id
					left join document_status as st on st.id=p.status_id
					left join pl_currency as curr on p.profit_currency=curr.id
					left join supplier_contact as c on p.contact_id=c.id
					
				 
				where p.org_id="'.$org_id.'" and p.is_confirmed_price=1
					and pdate between '.$pdate1.'  and '.$pdate2.'
					';
		
		$sql.=$db_flt;
		
		if(count($flts)>0) {
			$flt=' and p.id in(select kp_id from kp_position where position_id in(select eq.id from catalog_position as eq where '.implode(' and ',$flts).')) ';
			$sql.=$flt;
		}
		
		
		$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}
		
		
		
		$alls=array();
		 
		
		//заполним шаблон полями
		 
		$current_eq_name='';
		$current_group_id='';
		$current_producer_id='';
		$current_two_group_id='';
		$extended_form=0;
		$tab_page=2;
		
		$supplier='';
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			if($v->GetName()=='eq_name') $current_eq_name=$v->GetValue();
		
			if($v->GetName()=='group_id') $current_group_id=$v->GetValue();
			if($v->GetName()=='producer_id') $current_producer_id=$v->GetValue();
			if($v->GetName()=='two_group_id') $current_two_group_id=$v->GetValue();
			if($v->GetName()=='tab_page') $tab_page=$v->GetValue();
			if($v->GetName()=='extended_form') $extended_form=$v->GetValue();
			
				if($v->GetName()=='supplier_name') $supplier=$v->GetValue();
			
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
		
		
		
		//echo $sql;
		if($do_it){  
		 $set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
		  $rs=$set->GetResult();
		  $rc=$set->GetResultNumRows();
		  
		 //echo $sql.'<br>';
		 // echo ' результат: '.$rc;
		  
		   //подчиненные менеджеры (если есть)
		$subords=$this->view_rules->GetManagers($result);
		
		//оборудование по спецправам (если есть)
		$eqs=$this->view_rules->GetList($result, 2);
		
		
		  for($i=0; $i<$rc; $i++){
			  
			  
			  $f=mysqli_fetch_array($rs);
			  foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			  
			  $f['pdate']=date("d.m.Y",$f['pdate']);
			
				if($f['supply_pdate']>0) $f['supply_pdate']=date("d.m.Y",$f['supply_pdate']);
				else $f['supply_pdate']='-';
				
				if($f['valid_pdate']>0) $f['valid_pdate']=date("d.m.Y",$f['valid_pdate']);
				else $f['valid_pdate']='-';
				
				
				
				
				$positions=$_kp->GetPositionsArr($f['id'], false);
				
				$f['positions']=$positions;
				
				$f['total_cost']=$_kp->CalcCost($f['id'],  $positions,  $result);
				$f['currency']=$_kp->currency;
				
				 
				 
				//есть ли печатная форма...
				$main_id=0; $txt_kp='';
				foreach($positions as $k=>$v){
					if($v['parent_id']==0){
						$main_id=$v['pl_position_id'];
						$txt_kp=$v['txt_for_kp'];
						break;	
					}
				}	
				$_pl=new PlPosItem;
				$pl=$_pl->GetItemById($main_id);
				$_kp_form=new KpFormItem;
				$kp_form=$_kp_form->GetItemById($pl['kp_form_id']);
				$f['has_print_form']=(($kp_form!==false)&&(strlen($txt_kp)>0));
				
				
				if($f['confirm_price_pdate']!=0) $f['confirm_price_pdate']=date("d.m.Y H:i:s",$f['confirm_price_pdate']);
				else $f['confirm_price_pdate']='-';
				
				
				//отчет цветочек
				if($extended_form==1) $f['extended_re']=$this->ShowExtendedRe($f['code'], $f, $positions);
				
				
				
				//флаг доступности операций
			$has_access=false;
			
			$has_access=$has_access||$can_view_all;
			//свои кп
			if(!$has_access) $has_access=$has_access||($result['id']== $f['user_manager_id']);
			//кп подчиненных менеджеров
			if(!$has_access) $has_access=$has_access||in_array($f['user_manager_id'], $subords);		
			
			//кп по Особым правам по оборудованию
			if(!$has_access) $has_access=$has_access||in_array($main_id, $eqs);
			
			$f['has_access']=$has_access;
				
				
				$f['notes']=$_notes_group->GetItemsByIdArr($f['id'],0,0,false,false,false,0,false);
			  
			  	if(!in_array($f['user_manager_id'], $_users)) $_users[]=$f['user_manager_id'];
			
			if(!in_array($f['supplier_name'].', '.$f['opf_name'],  $_suppliers)) $_suppliers[]=$f['supplier_name'].', '.$f['opf_name'];
			  
			  
			  $alls[]=$f;	
		  }
			  
			 
		  
		}
		
		//echo ' счетов: '.count($alls);	
		$sm->assign('items',$alls);
		
		
		
		
		//итого
		$sm->assign('total_kp', count($alls));
		$sm->assign('total_managers', count($_users));
		$sm->assign('total_suppliers', count($_suppliers));
		$sm->assign('total_suppliers_list', $_suppliers);
		
		
		
		//виды цен КП
		$price_kinds=$_pk->GetItemsByFieldsArr(array('is_calc_price'=>1));
		$sm->assign('price_kinds',$price_kinds);	
		
		
		//группы, подгруппы, пр-ль
		$groups=$_pgg->GetItemsArr(0);
		$groups1=array();
		foreach($groups as $k=>$v){
			if(	$_rl->CheckFullAccess($result['id'], $v['id'], 1, 'w', 'catalog_group', 0)) $groups1[]=$v; 
		}
		 
		
		
		$sm->assign('groups',$groups1);
		
		$producers=array(); $producers1=array();
		if(($current_group_id!='')&&($current_group_id>0)){
			$producers=$_prg->GetItemsByIdArr($current_group_id);
		}
		
		foreach($producers as $k=>$v){
			if(	$_rl->CheckFullAccess($result['id'], $v['id'], 34, 'w', 'pl_producer', 0)) $producers1[]=$v; 
		}
		$sm->assign('producers',$producers1);
		
		$two_groups=array(); $two_groups1=array();
		if(($current_group_id!='')&&($current_group_id>0)&&($current_producer_id!='')&&($current_producer_id>0)){
			$two_groups=$_pgg->GetItemsArrByCategoryProducer($current_group_id, $current_producer_id);
		}
		foreach($two_groups as $k=>$v){
			if(	$_rl->CheckFullAccess($result['id'], $v['id'], 1, 'w', 'catalog_group', 0)) $two_groups1[]=$v; 
		}
		
		$sm->assign('two_groups',$two_groups1);
		
		
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
		$link=$pagename.'?'.eregi_replace('&sortmode'.$prefix.'=[[:digit:]]+','',$link).'&doSub'.$prefix.'=1&tab_page='.$tab_page;
		$sm->assign('link',$link);
		//$sm->assign('sortmode',$sortmode);
		
		
		$sm->assign('pagename',$pagename);
		
		$sm->assign('can_print',$can_print);
		$sm->assign('can_edit',$can_edit);
		$sm->assign('can_view_re_extended', $can_view_re_extended);
		 
		$sm->assign('do_it',$do_it);	
		
	 	$sm->assign('prefix',$prefix);
		
		
		$sm->assign('pagename',$pagename);
		//$sm->assign('loadname',$loadname);		
			
		return $sm->fetch($template);
	}
	
	
	
	
	//данные по рентабельности расширенные
	public function ShowExtendedRe($code, $kp, $positions){
		 $ret='';
				$_kpf=new KpPosPMFormer;
		
				 
				
				$_kp=new KpItem;
				
				 
			 	
				
				$id=$kp['id'];
				
			 
				
				$_kpf=new KpPosPMFormer;
					
				$_pl=new PlPosItem; $_pi=new PlPositionPriceItem;
				
				
				
				//$ret='<h3>Итого по позициям коммерческого предложения:</h3>';
				
				$currency=$_kpf->GetCurrency($positions);
				
				$init_cost=$_kpf->CalcCostSupplier($positions);
				
				$ret.='Сумма закупки: '.$init_cost.' '.$currency['signature'].'<br>';
				
				//print_r($currency);
				
				$base_prices=0;
				$base_prices_exw=0;
				$group_id=0; $deliveries=0; $nds=0; $sbor=0; $svh=0; $strah=0; $comission=0; $itog_ddpm=0; $delivery_value=0; $delivery_rub=0; $delivery_rub_r=0;  $delivery_rub_r_was=0; $broker_costs=0; $customs_values=0;
				
				$pnr=0; $has_pnr=false;
				//найдем ПНР
				foreach($positions as $k=>$v){
					if($v['is_install']==1){
						$has_pnr=true;
						$pnr=$_pl->DispatchCalcPriceF($v['position_id'],  $v['currency_id'],   NULL,  NULL,  NULL, NULL, NULL,   /*3*/ $kp['price_kind_id'],  NULL,  $v['extra_charges']);
					}
				}
				
				
				foreach($positions as $k=>$v){
					$pl=$_pl->getitembyid($v['position_id']);
					
					$calculation_form_id=$_pl->GetCalculationFormId($pl['producer_id']);
					
					$group_id=$v['two_group_id'];
					if(($kp['price_kind_id']==1)||($kp['price_kind_id']==2)){
					
						$pi=$_pi->GetItemByFields(array('pl_position_id'=>$v['position_id'], 'currency_id'=>$v['currency_id'], 'price_kind_id'=>3));
						//echo $pi['price'];
						$base_price=$pi['price'];
						$base_prices+=$v['quantity']* $base_price;
						
						//найти цену Exw поставщика
						$base_price_exw=$_pl->DispatchCalcPriceF($v['position_id'],  $v['currency_id'],   NULL,  NULL,  NULL, NULL, NULL,  3,  NULL,  $v['extra_charges']);
						$base_prices_exw+=$v['quantity']*$base_price_exw;
						
						 
					}
					//найти остальные данные для ddpm
					if($kp['price_kind_id']==1){
						/*
						доставку до москвы
					НДС тамож
					сбор
					СВХ брокер
					Страховка
					комиссия за перевод дс
					цена итог ддпм
						*/
						 if($v['parent_id']==0){
							
							
							if($calculation_form_id==1){
							
								$delivery=$pl['delivery_ddpm']*$v['quantity'];  //$_pl->CalcOptionPriceF($f['position_id'], $v['currency_id'],    NULL,  NULL, NULL,  NULL,  NULL, $kp['price_kind_id'], NULL);
								$deliveries+=$delivery; //$base_price_exw
								
								 
								$nds_per_position=$_pl->CalcNDS($base_price_exw,  $v['pl_position_id'],  $v['currency_id'], NULL, 3,NULL,true);
								$nds_=($v['quantity']*$nds_per_position);
								$nds+=$nds_;
								
								$sbor_=$v['quantity']*$pl['duty_ddpm'];
								$sbor+=$sbor_;
								
								
								$svh_=$v['quantity']*$pl['svh_broker'];
								$svh+=$svh_;
								
								$strah_=($v['quantity']*$_pl->CalcInsur($base_price_exw,  $nds_per_position,   $v['pl_position_id'], $v['currency_id'], NULL, 3, NULL, true));
								$strah+=$strah_;
								
								$comission_=($v['quantity']*$_pl->CalcComission($base_price_exw, $nds_per_position,   $v['pl_position_id'], $v['currency_id'], NULL, 3, NULL, true));
								$comission+=$comission_;
								
								$itog_ddpm_ =$base_price_exw*$v['quantity']+$delivery+$nds_+$sbor_+$svh_+$strah_+$comission_;
								$itog_ddpm+=$itog_ddpm_;
							
							}elseif($calculation_form_id==2){
								
								$customs_value=$_pl->CalcCustomsValue($base_price_exw,  $v['pl_position_id'],  $v['currency_id'], NULL, 3, NULL, true,  $this->rates);
								$customs_values+=$v['quantity']*$customs_value;
								
								$delivery_value=$pl['delivery_value']*$v['quantity'];
								
								
								$delivery_rub=$v['quantity']*$pl['delivery_rub'];
								
								$delivery_rub_r=$v['quantity']*$_pl->CalcDeliveryRub( $v['pl_position_id'],  $v['currency_id'],  NULL, 3, NULL, $this->rates, true);
								//$delivery_rub_r_was=($v['price_f']-$delivery_value);
								
								
								$nds_per_position=$_pl->CalcNDS($base_price_exw,  $v['pl_position_id'],  $v['currency_id'], NULL, 3,NULL,true);
								$nds_=($v['quantity']*$nds_per_position);
								$nds+=$nds_;
								
								$sbor_=$v['quantity']*$pl['duty_ddpm'];
								$sbor+=$sbor_;
								
								
								$svh_=$v['quantity']*$pl['svh_broker'];
								$svh+=$svh_;
								
								$broker_cost=$pl['broker_costs'];
								$broker_costs+=$v['quantity']*$pl['broker_costs'];
								
								$strah_=($v['quantity']*$_pl->CalcInsur($base_price_exw,  $nds_per_position,   $v['pl_position_id'], $v['currency_id'], NULL, 3, NULL, true));
								$strah+=$strah_;
								
								$comission_=0;//($v['quantity']*$_pl->CalcComission($base_price_exw, $nds_per_position,   $v['pl_position_id'], $v['currency_id'], NULL, 3, NULL, true));
								$comission+=$comission_;
								
								
								$itog_ddpm_=$base_price_exw*$v['quantity']+$delivery_value+$delivery_rub_r+$nds_+$sbor_+$svh_+$strah_+$comission_+$customs_value+$broker_cost;
								$itog_ddpm+=$itog_ddpm_;
							 }
							
						 }else{
							$delivery=0; 
							$deliveries+=$v['quantity']*$delivery;
							
							if($calculation_form_id==1){
								 
								$nds_per_position=$_pl->CalcOptionNDS($base_price_exw, 	$v['pl_position_id'], $v['currency_id'], NULL, 3, NULL);
								$nds_=($v['quantity']*$nds_per_position);
								$nds+=$nds_;
								
								 
								
								$sbor_=0;
								$sbor+=$v['quantity']*$sbor_;
								
								$svh_=0;
								$svh+=$v['quantity']*$svh_;
								
								$strah_=($v['quantity']*$_pl->CalcOptionInsur($base_price_exw, $nds_,   $v['pl_position_id'], $v['currency_id'], NULL, 3, NULL,true));
								$strah+=$strah_;
								
								$comission_=($v['quantity']*$_pl->CalcOptionComission($base_price_exw, $nds_,   $v['pl_position_id'],  $v['currency_id'], NULL, 3,NULL,true));
								$comission+=$comission_;
								
								$itog_ddpm_=$base_price_exw*$v['quantity']+$delivery+$nds_+$sbor_+$svh_+$strah_+$comission_;
								$itog_ddpm+=$itog_ddpm_;
							}elseif($calculation_form_id==2){
								
								$customs_value=$_pl->CalcOptionCustomsValue($base_price_exw,  $v['pl_position_id'],  $v['currency_id'], NULL, 3, NULL, true, $this->rates);
								$customs_values+=$v['quantity']*$customs_value;
								
								$broker_cost=$pl['broker_costs'];
								$broker_costs+=$v['quantity']*$pl['broker_costs'];
								
								$nds_per_position=$_pl->CalcOptionNDS($base_price_exw, 	$v['pl_position_id'], $v['currency_id'], NULL, 3, NULL);
								$nds_=($v['quantity']*$nds_per_position);
								$nds+=$nds_;
								
								 
								
								$sbor_=0;
								$sbor+=$v['quantity']*$sbor_;
								
								$svh_=0;
								$svh+=$v['quantity']*$svh_;
								
								$strah_=($v['quantity']*$_pl->CalcOptionInsur($base_price_exw, $nds_,   $v['pl_position_id'], $v['currency_id'], NULL, 3, NULL,true));
								$strah+=$strah_;
								
								$comission_=0;//($v['quantity']*$_pl->CalcOptionComission($base_price_exw, $nds_,   $v['pl_position_id'],  $v['currency_id'], NULL, 3,NULL,true));
								$comission+=$comission_;
								
								
								$itog_ddpm_=$base_price_exw*$v['quantity']+$nds_+$sbor_+$svh_+$strah_+$comission_+$customs_value+$broker_cost;
								$itog_ddpm+=$itog_ddpm_;
							}
							 
						 }
						 
						// echo $v['pl_position_id'].': '.$strah_.'<br>';
					}
				}
				
				if(($kp['price_kind_id']==1)||($kp['price_kind_id']==2)){
					if($has_pnr){
						$ret.='Cумма ПНР: '.round($pnr, $_pl->_round_define->DefineDigits($group_id)).'  '.$currency['signature'].'<br />'	;
					}else{
						$ret.='ПНР не включены в КП.<br />'	;
					}
					
					$ret.='Сумма базовых цен поставщика: '.round($base_prices, $_pl->_round_define->DefineDigits($group_id)).'  '.$currency['signature'].'<br>'; 
					
					
					
					if($kp['price_kind_id']==2) $ret.='<b>';
					$ret.='Сумма цен ExW поставщика: '.round($base_prices_exw, $_pl->_round_define->DefineDigits($group_id)).'  '.$currency['signature'].'<br>'; 
					if($kp['price_kind_id']==2) $ret.='</b>';
					
					
				
				}
				
				if($kp['price_kind_id']==1){
					if($calculation_form_id==1){
					
						$ret.='Стоимость доставки: '.round($deliveries, $_pl->_round_define->DefineDigits($group_id)).'  '.$currency['signature'].'<br>'; 
						
						$ret.='Сумма тамож. НДС: '.round($nds, $_pl->_round_define->DefineDigits($group_id)).'  '.$currency['signature'].'<br>'; 
						
						$ret.='Сумма сбора : '.round($sbor, $_pl->_round_define->DefineDigits($group_id)).'  '.$currency['signature'].'<br>'; 
						
						$ret.='Сумма СВХ, брокер: '.round($svh, $_pl->_round_define->DefineDigits($group_id)).'  '.$currency['signature'].'<br>'; 
						
						$ret.='Сумма страховки, 0,2% : '.round($strah, $_pl->_round_define->DefineDigits($group_id)).'  '.$currency['signature'].'<br>'; 
						//Комиссия за перевод д/с, 1% 
						
						$ret.='Сумма комиссии за перевод д/с, 1% : '.round($comission, $_pl->_round_define->DefineDigits($group_id)).'  '.$currency['signature'].'<br>'; 
					
					
					}else{
						$ret.='Стоимость пошлины: '.round($customs_values, $_pl->_round_define->DefineDigits($group_id)).'  '.$currency['signature'].'<br>';  
				
				
						$ret.='Стоимость затрат брокера: '.round($broker_costs, $_pl->_round_define->DefineDigits($group_id)).'  '.$currency['signature'].'<br>';  
				
						
						$ret.='Стоимость доставки до границы: '.round($delivery_value, $_pl->_round_define->DefineDigits($group_id)).'  '.$currency['signature'].'<br>';  
						
						$ret.='Стоимость доставки после границы: '.round($delivery_rub_r, $_pl->_round_define->DefineDigits($group_id)).'  '.$currency['signature'].'/ '.$delivery_rub.' руб.<br>';
						
						
						$ret.='Сумма тамож. НДС: '.round($nds, $_pl->_round_define->DefineDigits($group_id)).'  '.$currency['signature'].'<br>'; 
						
						$ret.='Сумма сбора : '.round($sbor, $_pl->_round_define->DefineDigits($group_id)).'  '.$currency['signature'].'<br>'; 
						
						$ret.='Сумма СВХ, брокер: '.round($svh, $_pl->_round_define->DefineDigits($group_id)).'  '.$currency['signature'].'<br>'; 
						
						$ret.='Сумма страховки, 0,1	% : '.round($strah, $_pl->_round_define->DefineDigits($group_id)).'  '.$currency['signature'].'<br>'; 
						//Комиссия за перевод д/с, 1% 
						
						//$ret.='Сумма комиссии за перевод д/с, 1% : '.round($comission, $_pl->_round_define->DefineDigits($group_id)).'  '.$currency['signature'].'<br>'; 
					
						
					 }
					
					
					
					
					if($kp['price_kind_id']==1) $ret.='<b>';
					//$ret.='Сумма итоговых цен DDPM : '.round($itog_ddpm,  $_pl->_round_define->DefineDigits($group_id)).'  '.$currency['signature'].'<br>'; 
					
					if($kp['price_kind_id']==1) $ret.='Сумма итоговых цен DDPM : '. ($_kpf->CalcCostSupplier($positions)).'  '.$currency['signature'].'<br>'; 
					else $ret.='Сумма итоговых цен DDPM : '.round($itog_ddpm,  $_pl->_round_define->DefineDigits($group_id)).'  '.$currency['signature'].'<br>'; 
					
					if($kp['price_kind_id']==1) $ret.='</b>';
					
				}
				 
				$ret.= '<strong>Сумма КП: '.$_kp->CalcCost($id,$positions).'  '.$currency['signature'].'</strong>';
				
				return $ret;	
	}
}
?>