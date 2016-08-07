<?

require_once('abstractgroup.php');
require_once('posgroupgroup.php');
require_once('posgroupitem.php');
require_once('positem.php');
require_once('pl_positem.php');

require_once('pl_disgroup.php');
require_once('pl_dismaxvalgroup.php');
require_once('pl_dismaxvalitem.php');
require_once('pl_prodgroup.php');
require_once('pl_currgroup.php');
require_once('price_kind_group.php');
require_once('price_kind_item.php');
require_once('rl/rl_man.php');
require_once('round_define.class.php');

require_once('currency/currency_solver.class.php');

require_once('user_s_group.php');

require_once('pl_rule/pl_rules.class.php');

//  группа прайс-лист
class PlPosGroup extends AbstractGroup {
	
	public $_round_define;
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='pl_position';
		$this->pagename='pricelist.php';		
		$this->subkeyname='position_id';	
		$this->vis_name='is_shown';		
		$this->itemsname='items';
		$this->items=array();
		
		$this->_round_define=new RoundDefine;
		
	}
	
	
	public function ShowPos($template, DBDecorator $dec, DBDecorator $decorator_for_equipment,$from=0,$to_page=ITEMS_PER_PAGE,
		$can_edit=false, 
		$can_delete=false, $is_ajax=false, 
		$can_expand_groups=false,
		$can_add_group=false, 
		$can_edit_group=false, 
		$can_delete_group=false, 
		$can_add=false, 
		$can_max_val=false, 
		$can_create_bill=false, $prefix='', 
		$can_create_incoming_bill=false, $memory=0, 
		$can_create_kp=false, 
		$result=NULL,
		$can_view_by_supplier=false, //Просмотр ПЛ поставщика
		$can_print_by_supplier=false, //Печать ПЛ поставщика
		$can_print_exw=false, //Печать ПЛ ExWorks
		$can_print_ddpm=false, //Печать ПЛ DDPM
		$can_view_base_price=false, //Просмотр цен поставщика
		$can_edit_base_price=false, //Редактирование цен поставщика
		$can_view_base_discount=false, //Просмотр скидок поставщика
		$can_edit_base_discount=false, //Редактирование скидок поставщика
		$can_view_rent_koef=false, //Просмотр коэффициента рентабельности поставщика
		$can_edit_rent_koef=false, //Редактирование коэффициента рентабельности поставщика
		$can_print=false, //печать пл.
		$can_override_manager_discount=false, //скидка менеджера без ограничений - неактивна
		$can_override_ruk_discount=false, //скидка рук-ля без ограничений
		$can_ruk_discount=false, //Доступ к скидке рук-ля 
		$can_print_price_f=false, //Печать базового ПЛ п-ка со скидками
		$can_unselect_pnr=false, //Возможность отключить ПНР
		$can_obor=false, //доступ к разделам ПЛ
		$can_tools=false,
		$can_spare=false,
		$can_service=false,
		$can_unselect_mandatory=false, //можно снять выд-ие обязат. опций
		$can_admin_records=false, //администрирование на уровне записей
		$can_select_another_contact=false, //можно включать чужие контакты в ПЛ
		$can_view_prices=false //просмотр цен, скидок в ПЛ и неутв. КП
		//$currency_id=CURRENCY_DEFAULT_ID //айди валюты
		){
		
		
		$alls=array();
		
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		
		$_pi=new PlPosItem;
		$_rl=new RLMan;
		
		$_rules=new PlRules;
		
		//раскрывать ли опции...
		$fields=$dec->GetUris();
		$current_id='';
		$current_price_kind_id=0;
		foreach($fields as $k=>$v){
			if($v->GetName()=='id') $current_id=$v->GetValue();
			if($v->GetName()=='price_kind_id') $current_price_kind_id=$v->GetValue();
		}
		$do_show_options=($current_id!=='');
		
		
		//определим вид цены
		$_price_kind=new PriceKindItem;
		$price_kind=$_price_kind->GetItemById($current_price_kind_id);
		
		
		
		//определить валюту (по связи с параметром group_id)
		$current_group_id=''; $current_producer_id=0;
		foreach($fields as $k=>$v){
			if($v->GetName()=='group_id') $current_group_id=$v->GetValue();
			if($v->GetName()=='producer_id') $current_producer_id=$v->GetValue();
			 
		}
		$currency_id=$this->GetCurrencyId($current_group_id);
		//echo $currency_id;
		
		//определим шаблон, с каким работать
		 
		$inner_template=$this->GetTemplate($current_price_kind_id,$current_group_id, $current_producer_id );
		 
		//определим форму расчета цен поставщика
		$calculation_form_id=$_pi->GetCalculationFormId($current_producer_id);
		//echo $calculation_form_id;
		
		$this->GainSql($sql, $sql_count,  $currency_id, $current_price_kind_id);
				 
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$sql.=' and '.$db_flt;
			$sql_count.=' and '.$db_flt;	
		}
		
		$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}
		//echo $sql.'<br>';
		
		$fields=$dec->GetUris();
		$current_id=''; $doShow='';
		foreach($fields as $k=>$v){
			
			if($v->GetName()=='id') $current_id=$v->GetValue();
			if($v->GetName()=='doShow') $doShow=$v->GetValue();
		}
		
		$_currency_solver=new CurrencySolver;
		$rates=$_currency_solver->GetActual();
		
		//$f['rate']= CurrencySolver::Convert(1, $rates, $f['currency_id'], 1);
		//print_r($rates);
		
		if($doShow==1){
		
			$set=new mysqlSet($sql,$to_page, $from,$sql_count);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			$total=$set->GetResultNumRowsUnf();
			
			
			//page
			if($from>$total) $from=ceil($total/$to_page)*$to_page;
			$navig = new PageNavigator($this->pagename,$total,$to_page,$from,10,'&'.$dec->GenFltUri('&', $prefix));
			$navig->SetFirstParamName('from'.$prefix);
			$navig->setDivWrapperName('alblinks');
			$navig->setPageDisplayDivName('alblinks1');			
			$pages= $navig->GetNavigator();
			
			$alls=array();
			$_gi=new PosGroupItem;
			
			
			$_dgv=new PlDisMaxValGroup;
			
			
			$mandatories=array();
			$recomended=array();
			$pre_quantities=array();
			
			$has_print_form=false;
			
			$has_re=false;
			$has_max_sk=false; $_dis_max_val_item=new PlDisMaxValItem;
			
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				foreach($f as $k=>$v) $f[$k]=stripslashes($v);
				//$f['pdate']=date("d.m.Y H:i:s",$f['pdate']);
				
				$f['rate']= CurrencySolver::Convert(1, $rates, 1, $f['currency_id']);
				
				
				$gi=$_gi->GetItemById($f['group_id']);
				if($gi['parent_group_id']>0){
					$gi2=$_gi->GetItemById($gi['parent_group_id']);	
					if($gi2['parent_group_id']>0){
						$gi3=$_gi->GetItemById($gi2['parent_group_id']);		
						
						$f['group_name']=stripslashes($gi3['name'].'-> '.$gi2['name'].'-> '.$gi['name']);
					}else{
						
						$f['group_name']=stripslashes($gi2['name'].'-> '.$gi['name']);	
					}
				}else{
					
					
				}
				
				
				//var_dump($this->_auth_result['id']);
				//виды скидок, включая их ограничения
			
				$f['discs1']=$_dgv->GetItemsByKindPosArr($f['pl_id'], $current_price_kind_id, 0, $this->_auth_result['id']); //$_dgv->GetItemsByIdArr($f['pl_id']);
				
				
				//цена: из прайс-листа или расчетная? определим по виду цены
				//$price_kind['is_calc_price'] 
				
				//цена со скидкой
				if($price_kind['is_calc_price']==0){
					
					//базовая цена Exw
					$f['price_f']=$_pi->CalcPriceF($f['pl_id'], $f['currency_id'],  NULL, NULL, NULL,  NULL,  NULL, $current_price_kind_id, $price_kind);
					
					
					
					if( $calculation_form_id==1){
						//считать НДС
						$f['nds']=$_pi->CalcNDS($f['price_f'], 	$f['pl_id'], $f['currency_id'], NULL, $current_price_kind_id, $price_kind,  true);
						
						//страховка
						$f['insur']=$_pi->CalcInsur($f['price_f'], $f['nds'],   $f['pl_id'], $f['currency_id'], NULL, $current_price_kind_id, $price_kind, true);
						
						//комиссия
						$f['commission']=$_pi->CalcComission($f['price_f'], $f['nds'],   $f['pl_id'], $f['currency_id'], NULL, $current_price_kind_id, $price_kind, true);
						
						//итоговая цена ddpm
						//форма расчета - из поставщика, если 1, то:
						
						$f['price_ddpm']=$f['price_f']+$f['delivery_ddpm']+$f['nds']+$f['duty_ddpm']+$f['svh_broker']+$f['insur']+$f['commission'];
						
						
						
						$f['nds']=round($f['nds'], 0);  $f['insur']=round($f['insur'], 0); $f['commission']=round($f['commission'], 0); 
						
						$f['price_ddpm']=$f['price_f']+$f['delivery_ddpm']+$f['nds']+$f['duty_ddpm']+$f['svh_broker']+$f['insur']+$f['commission'];
					
					}elseif( $calculation_form_id==2){
						//если 2, то:
						//echo 'zzzzzzzzzzzz';
						$f['delivery_rub_d']=$_pi->CalcDeliveryRub($f['pl_id'], $f['currency_id'], NULL, $current_price_kind_id, $price_kind, $rates, true);
						
						
						//считать пошлину
						$f['customs_value']=$_pi->CalcCustomsValue($f['price_f'],$f['pl_id'], $f['currency_id'], NULL,$current_price_kind_id, $price_kind, true);
						
						//считать НДС
						$f['nds']=$_pi->CalcNDS($f['price_f'], 	$f['pl_id'], $f['currency_id'], NULL, $current_price_kind_id, $price_kind,  true);
						
						//страховка
						$f['insur']=$_pi->CalcInsur($f['price_f'], $f['nds'],   $f['pl_id'], $f['currency_id'], NULL, $current_price_kind_id, $price_kind, true);
						
						//комиссия
						//$f['commission']=$_pi->CalcComission($f['price_f'], $f['nds'],   $f['pl_id'], $f['currency_id'], NULL, $current_price_kind_id, $price_kind, true);
						
						
						$f['price_ddpm']=$f['price_f']+$f['delivery_rub_d']+$f['delivery_value']+$f['nds']+$f['duty_ddpm']+$f['svh_broker']+$f['insur']; //+$f['commission'];
						
						
						$f['nds']=round($f['nds'], 0);  $f['insur']=round($f['insur'], 0); $f['commission']=round($f['commission'], 0); 
						
						$f['price_ddpm']=$f['price_f']+$f['delivery_rub_d']+$f['delivery_value']+$f['nds']+$f['duty_ddpm']+$f['svh_broker']+$f['insur']/*+$f['commission']*/+$f['broker_costs']+$f['customs_value'];
					
					}
					$digits=$this->_round_define->DefineDigits($f['group_id']);
					
					
					
					
					 
					$f['price_ddpm']=round($f['price_ddpm'], $digits);
				}else{
					 //переопределить цену...
					 $f['price']=$_pi->CalcPrice($f['pl_id'], $f['currency_id'],NULL,$current_price_kind_id,$price_kind);
							
					 $f['price_f']=0;  
					 
					 //текущая рентабельность. необходима для формирования подтверждения при вводе скидки руководителя
					 if($current_price_kind_id==1) $f['current_profit']=$f['profit_ddpm'];
					 else $f['current_profit']=$f['profit_exw'];
				}
				
				
				
				$f['print_form_has_komplekt']=1;	
				
				$f['extra_charges']=0;	
				
				
				$f['can_delete']=$_pi->CanDelete($f['pl_id']);
				
				
				//найдем опции:
				
				if($do_show_options) $f['options']=$this->ShowOptionsArr($f['id'], $f['currency_id'], $current_price_kind_id);
				
				//проверим доступность печатной формы...
				if($f['id']==$current_id){
					$has_print_form=(($f['kp_form_id']!=0)&&(strlen(trim(strip_tags($f['txt_for_kp'])))>0));
					//проверить, заданы ли максимальные скидки
					//$current_price_kind_id
					//$_dis_max_val_item
					
					$_m1=$_dis_max_val_item->GetItemByFields(array('discount_id'=>1, 'price_kind_id'=>$current_price_kind_id, 'pl_position_id'=>$f['pl_id'], 'rub_or_percent'=>1));
					$_m2=$_dis_max_val_item->GetItemByFields(array('discount_id'=>2, 'price_kind_id'=>$current_price_kind_id, 'pl_position_id'=>$f['pl_id'], 'rub_or_percent'=>1));
					
					
					$has_max_sk=($_m1!==false)&&($_m2!==false) ;
				
					//проверить, заданы ли рентабельности...
					if($current_price_kind_id==2) $has_re=($f['profit_exw']!=0);
					elseif($current_price_kind_id==1) $has_re=($f['profit_ddpm']!=0);
				
					
					//echo 'zz';
				}
				
				
				
				//построим правила опций для оборудования...
				$f['rules']=$_rules->GetRulesCli($f['id']);
				
				
				//выделим все опции:
				//обязательные
				//рекомендуемые
				foreach($f['options'] as $ok=>$ov){
					if($ov['is_mandatory']==1) $mandatories[]=$ov['position_id'];	
					if($ov['is_mandatory']==2) $recomended[]=$ov['position_id'];	
					if( $ov['pre_quantity']>0) $pre_quantities[]=array('id'=>$ov['position_id'], 'quantity'=>$ov['pre_quantity']);	
					 
				}
				
				
				
				//print_r($f);	
				$alls[]=$f;
			}
		}
		
		//var_dump($mandatories); 
		sort($recomended);
		$sm->assign('mandatories', array_reverse( $mandatories));
		$sm->assign('recomended',  array_reverse($recomended ));
		
		$sm->assign('pre_quantities',  ($pre_quantities ));
		//var_dump($pre_quantities);
		
		//заполним шаблон полями
		$current_action='';
		$current_object='';
		$current_group='';
		$current_two_group='';
		$current_three_group='';
		$current_producer_id='';
		$current_id='';
		//$current_price_kind_id=1; Определили ранее
		
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			
			
			if($v->GetName()=='dimension_id') $current_dimension_id=$v->GetValue();
			if($v->GetName()=='group_id') $current_group_id=$v->GetValue();
			if($v->GetName()=='two_group_id') $current_two_group=$v->GetValue();
			if($v->GetName()=='three_group_id') $current_three_group=$v->GetValue();
			
			if($v->GetName()=='producer_id') $current_producer_id=$v->GetValue();
			if($v->GetName()=='id') $current_id=$v->GetValue();
			
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
		//фильтр по обор-ю
		$this->GainSql($sql_eq);
		$db_flt=$decorator_for_equipment->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$sql_eq.=' and '.$db_flt;
		}
		
		$fields=$decorator_for_equipment->GetUris();
		$current_id='';
		foreach($fields as $k=>$v){
			
			if($v->GetName()=='id') $current_id=$v->GetValue();
		}
		$ord_flt=$decorator_for_equipment->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql_eq.=' order by '.$ord_flt;
		}
		
		
		//echo $sql_eq.'<br>';
		$set=new mysqlSet($sql_eq );
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		$acts=array();
		$acts[]=array('name'=>'-все-');
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['is_current']=($current_id==$f['id']); 
			$acts[]=$f;
		}
		$sm->assign('ids',$acts);
		
		
		
		
		
		//единицы изм
		$as=new mysqlSet('select * from catalog_dimension order by name asc');
		$rs=$as->GetResult();
		$rc=$as->GetResultNumRows();
		$acts=array();
		$acts[]=array('name'=>'');
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['is_current']=($current_dimension_id==$f[0]); 
			$acts[]=$f;
		}
		$sm->assign('dim',$acts);
		
		//тов группы
		$as=new mysqlSet('select * from catalog_group where parent_group_id=0 order by id asc, name asc');
		$rs=$as->GetResult();
		$rc=$as->GetResultNumRows();
		$acts=array();
		//$acts[]=array('name'=>'');
		$gr_ids=array(); $gr_names=array();
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['is_current']=($current_group_id==$f[0]); 
			
			//проверка доступа
			$f['has_access']=$_rl->CheckFullAccess($result['id'], $f['id'], 1, 'w', 'catalog_group', 0);
			
			$acts[]=$f;
			
			$gr_ids[]=$f['id'];
			$gr_names[]=$f['name'];
		}
		$sm->assign('group',$acts);
		
		
		//виды скидок
		$_dg=new PlDisGroup;
		$dg=$_dg->GetItemsArr();
		$sm->assign('discs', $dg);
		
		
		
		//валюты
		$_curg=new PlCurrGroup;
		$curg=$_curg->GetItemsArr();
		$sm->assign('currencies', $curg);
		
		
		
		//виды цен
		$_pkg=new PriceKindGroup;
		$pkg=$_pkg->GetItemsOptById($current_group_id, $current_price_kind_id, 'name', true,'-выберите-', $result);
		$sm->assign('price_kinds', $pkg);
		
		
		//группы
		
		$sm->assign('group_ids',$gr_ids);
		$sm->assign('group_names',$gr_names);
		
		//подгруппы
		if($current_group_id>0){
			$as=new mysqlSet('select * from catalog_group where parent_group_id="'.$current_group_id.'" and producer_id="'.$current_producer_id.'" order by name asc');
			$rs=$as->GetResult();
			$rc=$as->GetResultNumRows();
			$acts=array();
			$acts[]=array('name'=>'-выберите-');
			$gr_ids=array(); $gr_names=array();
			
			
			$restricted_ids=array();		
			
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				foreach($f as $k=>$v) $f[$k]=stripslashes($v);
				$f['is_current']=($current_two_group==$f[0]); 
				
				//проверка доступа
				//$f['has_access']=$_rl->CheckFullAccess($result['id'], $f['id'], 1, 'w', 'catalog_group', 0);
				//if(!$f['has_access']) $restricted_ids[]='"'.$f['id'].'"';
				
				$acts[]=$f;
				
				$gr_ids[]=$f['id'];
				$gr_names[]=$f['name'];
			}
			
			
			
			$sm->assign('two_group',$acts);
			
			$sm->assign('two_group_ids',$gr_ids);
			$sm->assign('two_group_names',$gr_names);
			
			if($current_two_group>0){	
			
				$as=new mysqlSet('select * from catalog_group where parent_group_id="'.$current_two_group.'" order by name asc');
				$rs=$as->GetResult();
				$rc=$as->GetResultNumRows();
				$acts=array();
				$acts[]=array('name'=>'');
				$gr_ids=array(); $gr_names=array();
				
				for($i=0; $i<$rc; $i++){
					$f=mysqli_fetch_array($rs);
					foreach($f as $k=>$v) $f[$k]=stripslashes($v);
					$f['is_current']=($current_three_group==$f[0]); 
					
					
					//проверка доступа
				//$f['has_access']=$_rl->CheckFullAccess($result['id'], $f['id'], 1, 'w', 'catalog_group', 0);
				//if(!$f['has_access']) $restricted_ids[]='"'.$f['id'].'"';
					
					$acts[]=$f;
					
					$gr_ids[]=$f['id'];
					$gr_names[]=$f['name'];
				}
			
				$sm->assign('three_group',$acts);
				
				$sm->assign('three_group_ids',$gr_ids);
				$sm->assign('three_group_names',$gr_names);
			}
			
			
			
		}
		
		$restricted_ids1=$_rl->GetBlockedItemsArr($result['id'],1,'w', 'catalog_group', 0);
		$restricted_ids=array();
		foreach($restricted_ids1 as $k=>$v) $restricted_ids[$k]='"'.$v.'"';
		//print_r($restricted_ids);
		//передать массив запрещенных подгрупп
		$sm->assign('restricted_two_groups', ' var restricted_two_groups=new Array('.implode(',', $restricted_ids).'); ');
		
		//производители
		$_pd=new PlProdGroup;
		
		$pds=$_pd->GetItemsByIdArr($current_group_id, $current_producer_id);
		array_unshift($pds, array('id'=>'', 'name'=>'-выберите-'));
		
		$pds1=array();
		$restricted_ids=array();		
		foreach($pds as $k=>$v){
			
			//проверка доступа
				$v['has_access']=$_rl->CheckFullAccess($result['id'], $v['id'], 34, 'w', 'pl_producer', 0);
				if(!$v['has_access']) $restricted_ids[]='"'.$v['id'].'"';
				
			$pds1[]=$v;	
		}
		
		$sm->assign('producers',$pds1);
		//передать массив запрещенных пр-лей
		$sm->assign('restricted_producers', ' var rp=new Array('.implode(',', $restricted_ids).'); ');
				
		
		
		
		
		$sm->assign('can_expand_groups', $can_expand_groups);
		
		$sm->assign('from',$from);
		$sm->assign('to_page',$to_page);
		$sm->assign('pages',$pages);
		$sm->assign('has_print_form',$has_print_form);
		
		$sm->assign('has_re',$has_re);
		$sm->assign('has_max_sk',$has_max_sk);
		
		$sm->assign($this->itemsname,$alls);
		$this->items=$alls;
		
		$sm->assign('can_add',$can_add);
		$sm->assign('can_edit',$can_edit);
		$sm->assign('can_delete',$can_delete);
		
		$sm->assign('prefix',$prefix);
		
		$sm->assign('can_add_group',$can_add_group);
		$sm->assign('can_edit_group',$can_edit_group);
		$sm->assign('can_delete_group',$can_delete_group);
		
		$sm->assign('can_max_val',$can_max_val);
		
		$sm->assign('can_create_bill',$can_create_bill);
		$sm->assign('can_create_incoming_bill', $can_create_incoming_bill);
		
		$sm->assign('can_create_kp',$can_create_kp);
		
		$sm->assign('can_view_by_supplier',$can_view_by_supplier);
		$sm->assign('can_print_by_supplier',$can_print_by_supplier);
		$sm->assign('can_print_exw',$can_print_exw);
		$sm->assign('can_print_ddpm',$can_print_ddpm);
		$sm->assign('can_view_base_price',$can_view_base_price);
		$sm->assign('can_edit_base_price',$can_edit_base_price);
		$sm->assign('can_view_base_discount',$can_view_base_discount);
		$sm->assign('can_edit_base_discount',$can_edit_base_discount);
		$sm->assign('can_view_rent_koef',$can_view_rent_koef);
		$sm->assign('can_edit_rent_koef',$can_edit_rent_koef);
		
		$sm->assign('can_override_manager_discount', $can_override_manager_discount); //скидка менеджера без ограничений -неактивна
		$sm->assign('can_override_ruk_discount', $can_override_ruk_discount); //скидка руководителя без ограничений
		
		//получим список тех, кто может превышать скидку рук-ля (кроме 2,3)
		$_usg=new UsersSGroup;
		$users=$_usg->GetUsersByRightArr('w', 752);
		//print_r($users);
		foreach($users as $k=>$v){
			if(in_array($v['id'], array(2,3))) unset($users[$k]);
		}
		$sm->assign('users_can_override_ruk_discount', $users);
		
		
		$sm->assign('can_ruk_discount', $can_ruk_discount);
		$sm->assign('can_print_price_f', $can_print_price_f);
		$sm->assign('can_unselect_pnr', $can_unselect_pnr);
		
		$sm->assign('can_admin_records', $can_admin_records); //администрирование на уровне записей
		
		$sm->assign('can_select_another_contact', $can_select_another_contact); 
		
		
		$sm->assign('can_obor',$can_obor); //доступ к разделам ПЛ
		$sm->assign('can_tools',$can_tools);
		$sm->assign('can_spare',$can_spare);
		$sm->assign('can_service',$can_service);
		
		$sm->assign('can_view_prices', $can_view_prices);
		
		$sm->assign('can_unselect_mandatory', $can_unselect_mandatory);
		
		
		$sm->assign('can_print',$can_print);
		$sm->assign('result_id', $result['id']); //id текущего пол-ля
		
		$sm->assign('pagename',$this->pagename);
		
		
		$sm->assign('inner_template', $inner_template); //динамически определяемый шаблон списка
		
		//можно запоминать в кукис настройку
		$sm->assign('memory',$memory);
		//print_r($_COOKIE);
		
		
		//ссылка для кнопок сортировки
		$link=$dec->GenFltUri('&', $prefix);
		$link=$this->pagename.'?'.eregi_replace('&sortmode'.$prefix.'=[[:digit:]]+','',$link);
		$sm->assign('link',$link);
		
		return $sm->fetch($template);
	}
	
	
	
	public function GainSql(&$sql, &$sql_count, $currency_id=CURRENCY_DEFAULT_ID, $price_kind_id=PRICE_KIND_DEFAULT_ID){
		$sql='select pl.id as pl_id, pl.discount_id, pl.discount_value, pl.discount_rub_or_percent, pl.position_id, 
					pl.discount_base, pl.discount_add, pl.profit_exw, pl.profit_ddpm, pl.kp_form_id, p.txt_for_kp,
					p.is_install, p.is_mandatory,  pl.delivery_ddpm, pl.duty_ddpm, pl.svh_broker, p.pre_quantity, pl.delivery_value, pl.delivery_rub, pl.customs, pl.broker_costs,
					
					dis.name,
					plp.price,  curr.id as currency_id, curr.signature,  plp.price_kind_id,
					p.*,
					d.name as dim_name,
					g.name as group_name,
					pp.name as producer_name,
					
					  pr_kind.is_calc_price
					
					
				from '.$this->tablename.' as pl
					inner join catalog_position as p on p.id=pl.'.$this->subkeyname.'
					left join catalog_dimension as d on p.dimension_id=d.id
					left join catalog_group as g on p.group_id=g.id
					left join pl_producer as pp on p.producer_id=pp.id
					left join pl_discount as dis on dis.id=pl.discount_id
					
					left join pl_currency as curr on curr.id=pp.currency_id
					
					left join pl_position_price as plp on plp.pl_position_id=pl.id and plp.currency_id=pp.currency_id and plp.price_kind_id='.$price_kind_id.'
					
					left join pl_price_kind as pr_kind on pr_kind.id='.$price_kind_id.'
				where parent_id=0 and p.is_active=1 
					';
					
					
					
		$sql_count='select count(*)
				from '.$this->tablename.' as pl
					inner join catalog_position as p on p.id=pl.'.$this->subkeyname.'
					left join catalog_dimension as d on p.dimension_id=d.id
					left join catalog_group as g on p.group_id=g.id
					left join pl_producer as pp on p.producer_id=pp.id
					left join pl_discount as dis on dis.id=pl.discount_id
					left join pl_position_price as plp on plp.pl_position_id=pl.id and plp.currency_id=pp.currency_id and plp.price_kind_id='.$price_kind_id.'
					left join pl_currency as curr on curr.id=pp.currency_id
					left join pl_price_kind as pr_kind on pr_kind.id='.$price_kind_id.'
				where parent_id=0 and p.is_active=1
					';
	}
	
	
	
	
	
	
	public function GainSqlOptions($id, &$sql, $currency_id=CURRENCY_DEFAULT_ID, $price_kind_id=PRICE_KIND_DEFAULT_ID){
		$sql='select pl.id as pl_id,  pl.discount_id, pl.discount_value, pl.discount_rub_or_percent, pl.position_id,
			pl.discount_base, pl.discount_add, pl.profit_exw, pl.profit_ddpm, 
			p.is_install,  p.is_mandatory, pl.delivery_ddpm, pl.duty_ddpm, pl.svh_broker, p.pre_quantity,  pl.delivery_value, pl.delivery_rub, pl.customs, pl.broker_costs,
					dis.name, 
					plp.price, curr.id as currency_id, curr.signature,  plp.price_kind_id,
					
					p.*,
					d.name as dim_name,
					g.name as group_name,
					pp.name as producer_name,
					plg.name as pl_group_name, plg.name_en as pl_group_name_en, plg.ord as pl_group_ord,
					  pr_kind.is_calc_price
					
					
				from '.$this->tablename.' as pl
					inner join catalog_position as p on p.id=pl.'.$this->subkeyname.'
					left join catalog_dimension as d on p.dimension_id=d.id
					left join catalog_group as g on p.group_id=g.id
					left join pl_producer as pp on p.producer_id=pp.id
					left join pl_group as plg on plg.id=p.pl_group_id
					left join pl_discount as dis on dis.id=pl.discount_id
					left join pl_position_price as plp on plp.pl_position_id=pl.id and plp.currency_id=pp.currency_id and plp.price_kind_id='.$price_kind_id.'
					left join pl_currency as curr on curr.id=pp.currency_id
					left join pl_price_kind as pr_kind on pr_kind.id='.$price_kind_id.'
				where parent_id="'.$id.'" and p.is_active=1
				
				order by plg.ord desc, plg.id asc, p.name asc, p.code asc, p.id asc  
					';
					
		//echo $sql.'<p>';
	}
	
	
	
	
	
	
	
	
	
	
	//метод для нахождения опций и их групп
	public function ShowOptions($id, $template, $can_edit=false, $can_delete=false, $is_ajax=false, $can_expand_groups=false,$can_add_group=false, $can_edit_group=false, $can_delete_group=false, $can_add=false, $can_max_val=false, $can_create_bill=false, $prefix='', $can_create_incoming_bill=false, $currency_id=CURRENCY_DEFAULT_ID){
		
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		
		
		$alls=$this->ShowOptionsArr($id,$currency_id);
		
		
		
		//виды скидок
		$_dg=new PlDisGroup;
		$dg=$_dg->GetItemsArr();
		$sm->assign('discs', $dg);
		
		
		//валюты
		$_curg=new PlCurrGroup;
		$curg=$_curg->GetItemsArr();
		$sm->assign('currencies', $curg);
		
		
		$sm->assign('can_expand_groups', $can_expand_groups);
		
		$sm->assign('from',$from);
		$sm->assign('to_page',$to_page);
		$sm->assign('pages',$pages);
		$sm->assign($this->itemsname,$alls);
		$this->items=$alls;
		
		$sm->assign('can_add',$can_add);
		$sm->assign('can_edit',$can_edit);
		$sm->assign('can_delete',$can_delete);
		
		$sm->assign('prefix',$prefix);
		
		$sm->assign('can_add_group',$can_add_group);
		$sm->assign('can_edit_group',$can_edit_group);
		$sm->assign('can_delete_group',$can_delete_group);
		
		$sm->assign('can_max_val',$can_max_val);
		
		$sm->assign('can_create_bill',$can_create_bill);
		$sm->assign('can_create_incoming_bill', $can_create_incoming_bill);
		
		$sm->assign('pagename',$this->pagename);
		
		return $sm->fetch($template);
	}
	
	//формирование набора опций
	public function ShowOptionsArr($id,  $currency_id=CURRENCY_DEFAULT_ID, $price_kind_id=PRICE_KIND_DEFAULT_ID, $base_pki=NULL, $base_pi=NULL, $for_pdf=false, $decorator=NULL){
		$opt_arr=array();
			
		
		$this->GainSqlOptions($id, $sql,  $currency_id, $price_kind_id);
		
		if($decorator!==NULL){
			$db_flt=$decorator->GenFltSql(' and ');
			if(strlen($db_flt)>0){
			//	$sql.=' and '.$db_flt;	
			
				$sql=eregi_replace('where', 'where '.$db_flt.' and ',$sql);
				
			}
		}
		
		
		
		
		//echo $sql.'<br>';
		
		
		
		
		$_pki=new PriceKindItem;
		$price_kind=$_pki->GetItemById($price_kind_id);
		
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		$_gi=new PosGroupItem;
		$_pi=new PlPosItem;
		
		$_dgv=new PlDisMaxValGroup;
		
		$_currency_solver=new CurrencySolver;
		$rates=$_currency_solver->GetActual();
		
		



		
		$old_group_id=0;
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			//$f['rate']= CurrencySolver::Convert(1, $rates, $f['currency_id'], 1);
			
			//определим форму расчета цен поставщика
			$calculation_form_id=$_pi->GetCalculationFormId($f['producer_id']);
		
			
			
			if($f['pl_group_id']!=$old_group_id){
				$opt_arr[]=array('kind'=>'group', 'name'=>$f['pl_group_name'], 'name_en'=>$f['pl_group_name_en'], 'pl_group_id'=>$f['pl_group_id']);
			}
			
			$f['kind']='option';
			
			
			if(!$for_pdf){
				$gi=$_gi->GetItemById($f['group_id']);
				if($gi['parent_group_id']>0){
					$gi2=$_gi->GetItemById($gi['parent_group_id']);	
					if($gi2['parent_group_id']>0){
						$gi3=$_gi->GetItemById($gi2['parent_group_id']);		
						
						$f['group_name']=stripslashes($gi3['name'].'-> '.$gi2['name'].'-> '.$gi['name']);
					}else{
						
						$f['group_name']=stripslashes($gi2['name'].'-> '.$gi['name']);	
					}
				}else{
					
					
				}
			}
			
			
			//виды скидок, включая их ограничения
		
			if(!$for_pdf) $f['discs1']=$_dgv->GetItemsByKindPosArr($f['pl_id'], $price_kind_id); //>GetItemsByIdArr($f['pl_id']);
			
			
			//цена: из прайс-листа или расчетная? определим по виду цены
			//$price_kind['is_calc_price'] 
			
			
			//цена со скидкой
			if($price_kind['is_calc_price']==0){
				$f['price_f']=$_pi->CalcOptionPriceF($f['pl_id'], $f['currency_id'],   NULL, $f, NULL,  NULL,  NULL, $price_kind_id , $price_kind);
				
				
				if($calculation_form_id==1){
				
					//считать НДС
					$f['nds']=$_pi->CalcOptionNDS($f['price_f'], 	$f['pl_id'], $f['currency_id'], NULL, $price_kind_id, $price_kind);
					
					//страховка
					$f['insur']=$_pi->CalcOptionInsur($f['price_f'], $f['nds'],   $f['pl_id'], $f['currency_id'], NULL, $price_kind_id, $price_kind);
					
					//комиссия
					$f['commission']=$_pi->CalcOptionComission($f['price_f'], $f['nds'],   $f['pl_id'], $f['currency_id'], NULL, $price_kind_id, $price_kind);
					
					//итоговая цена ddpm
					$f['price_ddpm']=$f['price_f']+$f['delivery_ddpm']+$f['nds']+$f['duty_ddpm']+$f['svh_broker']+$f['insur']+$f['commission'];
				
				}elseif($calculation_form_id==2){
					
					//считать  пошлину
					$f['customs_value']=$_pi->CalcOptionCustomsValue($f['price_f'],$f['pl_id'], $f['currency_id'], NULL, $price_kind_id, $price_kind, true);
					
					
					$f['delivery_rub_d']=$_pi->CalcDeliveryRub($f['pl_id'], $f['currency_id'], NULL, $price_kind_id, $price_kind, $rates, true);
					
					//считать НДС
					$f['nds']=$_pi->CalcOptionNDS($f['price_f'], 	$f['pl_id'], $f['currency_id'], NULL, $price_kind_id, $price_kind);
					
					//страховка
					$f['insur']=$_pi->CalcOptionInsur($f['price_f'], $f['nds'],   $f['pl_id'], $f['currency_id'], NULL, $price_kind_id, $price_kind);
					
					//комиссия
					//$f['commission']=$_pi->CalcOptionComission($f['price_f'], $f['nds'],   $f['pl_id'], $f['currency_id'], NULL, $price_kind_id, $price_kind);
					
					
						
					$f['price_ddpm']=$f['price_f']+$f['delivery_rub_d']+$f['delivery_value']+$f['nds']+$f['duty_ddpm']+$f['svh_broker']+$f['insur']/*+$f['commission']*/+$f['customs_value']+$f['broker_costs'];
				}
				
				
			}else{
				 //переопределить цену
				 $f['price']=$_pi->CalcOptionPrice($f['pl_id'], $f['currency_id'], NULL, $price_kind_id,$price_kind,$base_pki, $base_pi);
				 //base_pki, base_pi - из аргументов функции
				 $f['price_f']=0; //$_pi->CalcPriceF($f['pl_id'],$currency_id);
				 
			}
			
			$f['print_form_has_komplekt']=1;
			
			$f['extra_charges']=0;	
			
			if(!$for_pdf) $f['can_delete']=$_pi->CanDelete($f['pl_id']);
			
			//print_r($f);	
			$opt_arr[]=$f;
			
			$old_group_id=$f['pl_group_id'];
		}
		
		
		
		
		
	 
		return $opt_arr;
	}
	
	
	
	
	
	
	//оборудование в тегах option - выбор по категории
	public function GetItemsByIdOpt($id, $current_id=0,$fieldname='name', $do_no=false, $no_caption='-выберите-', $no_sql=''){
		$txt='';
		
		$flt=$no_sql;
		if($id!=0){
			$flt.=' and p.group_id="'.$id.'" ';	
		}
		$sql='select pl.*, p.name from '.$this->tablename.' as pl inner join catalog_position as p on p.id=pl.'.$this->subkeyname.' where parent_id=0 and p.is_active=1 '.$flt.' order by '.$fieldname.' asc';
		 
		//echo $sql;
		
		$set=new mysqlSet($sql);
		$tc=$set->GetResultNumRows();
		if($do_no){
		  $txt.="<option value=\"\" ";
		  if($current_id==0) $txt.='selected="selected"';
		  $txt.=">". $no_caption."</option>";
		}
		
		if($tc>0){
			$rs=$set->GetResult();
			for($i=0;$i<$tc;$i++){
				$f=mysqli_fetch_array($rs);
				$txt.="<option value=\"$f[id]\" ";
				
				if($current_id==$f['id']) $txt.='selected="selected"';
				
				$txt.=">".htmlspecialchars(stripslashes($f[$fieldname]))."</option>";
			}
		}
		return $txt;
	}
	
	//оборудование в тегах option - выбор по производителю
	public function GetItemsByProducerIdOpt($id, $current_id=0,$fieldname='name', $do_no=false, $no_caption='-выберите-', $no_sql=''){
		$txt='';
		
		$flt=$no_sql;
		if($id!=0){
			$flt.=' and p.producer_id="'.$id.'" ';	
		}
		$sql='select pl.*, p.name from '.$this->tablename.' as pl inner join catalog_position as p on p.id=pl.'.$this->subkeyname.' where parent_id=0 and p.is_active=1 '.$flt.' order by '.$fieldname.' asc';
		 
		//echo $sql;
		
		$set=new mysqlSet($sql);
		$tc=$set->GetResultNumRows();
		if($do_no){
		  $txt.="<option value=\"\" ";
		  if($current_id==0) $txt.='selected="selected"';
		  $txt.=">". $no_caption."</option>";
		}
		
		if($tc>0){
			$rs=$set->GetResult();
			for($i=0;$i<$tc;$i++){
				$f=mysqli_fetch_array($rs);
				$txt.="<option value=\"$f[id]\" ";
				
				if($current_id==$f['id']) $txt.='selected="selected"';
				
				$txt.=">".htmlspecialchars(stripslashes($f[$fieldname]))."</option>";
			}
		}
		return $txt;
	}
	
	//оборудование в тегах option - выбор по первой категории
	public function GetItemsByGroupIdIdOpt($id, $current_id=0,$fieldname='name', $do_no=false, $no_caption='-выберите-', $no_sql=''){
		$txt='';
		
		$flt=$no_sql;
		if($id!=0){
			$flt.=' and p.group_id in(select id from catalog_group where parent_group_id="'.$id.'" )';	
		}
		$sql='select pl.*, p.name from '.$this->tablename.' as pl inner join catalog_position as p on p.id=pl.'.$this->subkeyname.' where parent_id=0 and p.is_active=1 '.$flt.' order by '.$fieldname.' asc';
		 
		//echo $sql;
		
		$set=new mysqlSet($sql);
		$tc=$set->GetResultNumRows();
		if($do_no){
		  $txt.="<option value=\"\" ";
		  if($current_id==0) $txt.='selected="selected"';
		  $txt.=">". $no_caption."</option>";
		}
		
		if($tc>0){
			$rs=$set->GetResult();
			for($i=0;$i<$tc;$i++){
				$f=mysqli_fetch_array($rs);
				$txt.="<option value=\"$f[id]\" ";
				
				if($current_id==$f['id']) $txt.='selected="selected"';
				
				$txt.=">".htmlspecialchars(stripslashes($f[$fieldname]))."</option>";
			}
		}
		return $txt;
	}
	
	
	
	
	
	
	//получение массива данных обор-ия по айди отдела (через направления отдела)
	public function GetItemsArrByDepartmentId($department_id){
		
		
		$alls=array();		
		$this->GainSql($sql, $sql_count,  CURRENCY_DEFAULT_ID, 1);
		/*
		восстановить после уточнения направлений работы
		$sql.=' and p.group_id in(select distinct group_id from catalog_group_direction where direction_id in(select distinct direction_id from user_position_direction where position_id="'.$department_id.'"))
				order by p.name asc';
		*/
		
		$sql.=' 
				order by p.name asc';
		//echo $sql;
				
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		for($i=0;$i<$rc;$i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$alls[]=array('id'=>$f['id'], 'name'=>$f['name']);
		}
				
		return $alls;			
	}
	
	
	//определение валюты по айди раздела
	public function GetCurrencyId($group_id){
		$sql=' select currency_id from pl_currency_group where group_id="'.$group_id.'"';
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$f=mysqli_fetch_array($rs);
		
		return (int)$f[0];
		
	}
	
	//определение шаблона по айди раздела
	public function GetTemplate($price_kind_id, $group_id, $producer_id){
		$sql=' select list_template_name from pl_price_kind_group where price_kind_id="'.$price_kind_id.'" and  group_id="'.$group_id.'"';
		
		//echo $sql;
		
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$f=mysqli_fetch_array($rs);
		
		$name=$f[0];
		
		//если цена == 3 - то запросить форму из шаблона...
		if($price_kind_id==3){
			$sql=' select f.template_add from pl_delivery_form as f
			inner join pl_producer as p on p.delivery_form_id=f.id
			
			
			 where p.id="'.$producer_id.'" and  p.group_id="'.$group_id.'"';
			 
			 $set=new mysqlSet($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			$f=mysqli_fetch_array($rs);
			$name=$f[0].$name;
		}
		
		
		
		return $name;
		
	}
	
	
	
	
	
	
	//очистка ранее заданных значений
	public function MakeMemory($memory){
		if($memory==0){
			
			
			foreach($_COOKIE as $k=>$v) if(
				eregi('quantity', $k)||
				eregi('extra_charges', $k)||
				eregi('q_a', $k)||
				eregi('print_form_has_komplekt', $k)||
				eregi('discount', $k)){
				 //unset($_COOKIE[$k]);
				 @setcookie($k, '',1,'/');
				 //echo 'find '.$k;
				 
			}
			
			
		}else{
			//print_r($_COOKIE);
		}	
	}
	
	
	//получение набора итемов по декоратору
	public function GetItemsByDecArr(DBDecorator $dec){
		$res=array();
		
		
		$sql='select p.* from catalog_position as p
		/*left join pl_position as pp on pp.position_id=p.position_id*/
		
		 ';
		
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$sql.=' where '.$db_flt;
		 
			
		}
		
		$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}
		
		//echo $sql;
		$item=new mysqlSet($sql);
		$result=$item->getResult();
		$rc=$item->getResultNumRows();
		 
		for($i=0;$i<$rc; $i++){
			$f=mysqli_fetch_array($result);
			
			foreach($res as $k=>$v) $f[$k]=stripslashes($v);	
			 
			$res[]=$f;
		}
		
		return $res;
	}
	
}
?>