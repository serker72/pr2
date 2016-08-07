<?
session_start();
header('Content-type: text/html; charset=windows-1251');

require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/discr_table_objects.php');
require_once('../classes/actionlog.php');

require_once('../classes/positem.php');
require_once('../classes/posgroupitem.php');
require_once('../classes/posgroupgroup.php');

require_once('../classes/posdimitem.php');
require_once('../classes/posdimgroup.php');

require_once('../classes/pl_positem.php');
require_once('../classes/pl_posgroup.php');
require_once('../classes/pl_dismaxvalitem.php');
require_once('../classes/pl_disitem.php');

require_once('../classes/pl_posgroup_forbill.php');

require_once('../classes/pl_groupgroup.php');
require_once('../classes/pl_groupitem.php');

require_once('../classes/pl_pospriceitem.php');
require_once('../classes/pl_curritem.php');
require_once('../classes/price_kind_item.php');
require_once('../classes/pl_prodgroup.php');
require_once('../classes/pl_changes_mass_send.php');

require_once('../classes/user_s_group.php');
require_once('../classes/user_pos_group.php');

require_once('../classes/user_dep_group.php');

require_once('../classes/pl_rule/pl_rule.class.php');
require_once('../classes/pl_rule/pl_rule_item.class.php');
require_once('../classes/pl_rule/pl_rules.class.php');

$au=new AuthUser();
$result=$au->Auth();
$log=new ActionLog;

if($result===NULL){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();		
}

$ret='';
if(isset($_POST['action'])&&($_POST['action']=="edit_items")){
	if(!$au->user_rights->CheckAccess('w',602)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	//print_r($_POST);
	
	//die();
	/*
	"checked_ids[]":checked_ids,
						"price[]":price,
						"discount_id[]":discount_id,
						"discount_value[]":discount_value,
						"discount_rub_or_percent[]":discount_rub_or_percent,
						"dl_value[]":dl_value,
						"dl_rub_or_percent[]":dl_rub_or_percent*/
						
						
						
	$checked_ids=$_POST['checked_ids'];
	$price_kind_id=abs((int)$_POST['price_kind_id']);
	$_pi=new PlPosItem;
	$_price=new PlPositionPriceItem;
	$_curr=new PlCurrItem;
	
	foreach($checked_ids as $k=>$v){
		$id=abs((int)$v);
		
		$pi=$_pi->GetItemById($id);
		
		if($pi!==false){	
			$params=array();
			
			
			//определим, какая скидка активна - заносим ее
			//если неактивно ни одной - обнуляем скидку
			$discount_ids=explode(',',$_POST['discount_id'][$k]);
			$discount_values=explode(',',$_POST['discount_value'][$k]);
			$discount_rub_or_percents=explode(',',$_POST['discount_rub_or_percent'][$k]);
			$dl_values=explode(',',$_POST['dl_value'][$k]);
			$dl_rub_or_percents=explode(',',$_POST['dl_rub_or_percent'][$k]);
			
			
			$active_discount_offset=-1;
			$active_discount_id=0;
			/*foreach($discount_values as $kk=>$vv){
				if(abs((float)str_replace(",",".",$vv))>0){
					$active_discount_offset=$kk;
					$active_discount_id=abs((int)$discount_ids[$kk]);
					break;
				}
			}
			*/
			
			if($active_discount_id!=0){
				$params['discount_id']=$active_discount_id;
				$params['discount_value']=abs((float)str_replace(",",".",$discount_values[$active_discount_offset]));
				$params['discount_rub_or_percent']=abs((int)$discount_rub_or_percents[$active_discount_offset]);
				
			}else{
				$params['discount_id']=1;
				$params['discount_value']=0;
				$params['discount_rub_or_percent']=1;
			}
			
			
			
			$_pi->Edit($id, $params);
			
			foreach($params as $kk=>$vv){
				if($pi[$kk]!=$vv){	
					$log->PutEntry($result['id'],'редактировал позицию прайс-листа',NULL,602, NULL,'позиция '.SecStr($pi['name']).': в поле '.$kk.' установлено значение '.SecStr($vv),$id);
				
				}
			}
			
			//внесем цену
			$price_params=array();
			$price_params['price']=((float)str_replace(",",".",$_POST['price'][$k]));
			$price_params['currency_id']=abs((int)str_replace(",",".",$_POST['currency_id'][$k]));
			$price_params['price_kind_id']=$price_kind_id;
			
			$test_price=$_price->GetItemByFields(array('pl_position_id'=>$id, 'currency_id'=>$price_params['currency_id'], 'price_kind_id'=>$price_params['price_kind_id']));
			
			$curr=$_curr->GetItemById($price_params['currency_id']);
			
			$do_log_price=false;
			if($test_price===false){
				//цены нет, внести ее	
				$price_params['pl_position_id']=$id;
				$_price->Add($price_params);
				$do_log_price=true;
				
			}else{
				//цена есть, редактировать ее
				$_price->Edit($test_price['id'], $price_params);
				$do_log_price=((float)$price_params['price']!=(float)$test_price['price']);
			}
			 
			if($do_log_price) $log->PutEntry($result['id'],'редактировал позицию прайс-листа',NULL,602, NULL,'позиция '.SecStr($pi['name']).': Установлена цена '.$price_params['price'].' '.SecStr($curr['signature']),$id);
			
			//внесем ограничения на скидку
			//разобрать блок ограничений
			//если текущее ограничение равно пустой строке - удалить если есть такое
			//если равно строке - внести строку
			if($au->user_rights->CheckAccess('w',605)) foreach($discount_ids as $kk=>$vv){
				$_mvi=new PlDisMaxValItem;
				$_test_mvi=$_mvi->GetItemByFields(array('pl_position_id'=>$id, 'discount_id'=>abs((int)$vv), 'price_kind_id'=>$price_kind_id));
				$_dis=new PlDisItem;
				$_test_dis=$_dis->GetItemById($vv);
				
				if(trim($dl_values[$kk])==""){
					if($_test_mvi!==false){
						$_mvi->Del($_test_mvi['id']);
						//запись в журнал о снятии ограничения	
						$log->PutEntry($result['id'],'редактировал позицию прайс-листа',NULL,602, NULL,'позиция '.SecStr($pi['name']).': удалено максимальное значение поля '.SecStr($_test_dis['name']),$id);
					}
				}else{
					
					if($_test_mvi!==false){
						$m_params=array();
						
						$m_params['value']=abs((float)$dl_values[$kk]);
						$m_params['rub_or_percent']=abs((int)$dl_rub_or_percents[$kk]);
						$m_params['price_kind_id']=$price_kind_id;
						$_mvi->Edit($_test_mvi['id'], $m_params);
					}else{
						$m_params=array();
						$m_params['pl_position_id']=$id;
						$m_params['discount_id']=abs((int)$vv);
						$m_params['value']=abs((float)$dl_values[$kk]);
						$m_params['rub_or_percent']=abs((int)$dl_rub_or_percents[$kk]);
						$m_params['price_kind_id']=$price_kind_id;
						
						$_mvi->Add($m_params);
					}
					if($m_params['rub_or_percent']==0) $descr='руб.';
					elseif($m_params['rub_or_percent']==1) $descr='%';
					
					//запись в журнал об установке ограничения
					$log->PutEntry($result['id'],'редактировал позицию прайс-листа',NULL,602, NULL,'позиция '.SecStr($pi['name']).': установлено максимальное значение поля '.SecStr($_test_dis['name']).': '.$m_params['value'].' '.$descr,$id);
				}
			}
			
		}
	}

}
elseif(isset($_POST['action'])&&($_POST['action']=="edit_base_items")){
	//редактируем ПЛ поставщика
	
	if(!$au->user_rights->CheckAccess('w',602)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	//print_r($_POST);
	
	//die();
	/*
	"checked_ids[]":checked_ids,
						"price[]":price,
						"discount_id[]":discount_id,
						"discount_value[]":discount_value,
						"discount_rub_or_percent[]":discount_rub_or_percent,
						"dl_value[]":dl_value,
						"dl_rub_or_percent[]":dl_rub_or_percent*/
						
						
						
	$checked_ids=$_POST['checked_ids'];
	$price_kind_id=abs((int)$_POST['price_kind_id']);
	$_pi=new PlPosItem;
	$_price=new PlPositionPriceItem;
	$_curr=new PlCurrItem;
	$_pkg=new PriceKindItem;
	
	//пометим изменение цен для отправки сообщений
	$global_flag_of_send_messages=false;
	$pl_positions_ids=array(); $pl_option_ids=array(); $changed_price_kinds=array(); $with_option_ids=array();
	
	foreach($checked_ids as $k=>$v){
		$id=abs((int)$v);
		
		$pi=$_pi->GetItemById($id);
		
		if($pi!==false){	
			$params=array();
			$local_flag_of_send_messages=false;
			
			 
			
			//работа с блоками базовых скидок и к-тов рент-ти
			if($au->user_rights->CheckAccess('w',748)) $params['discount_base']=abs((float)str_replace(",",".",$_POST['discount_bases'][$k]));
			if($au->user_rights->CheckAccess('w',748)) $params['discount_add']=abs((float)str_replace(",",".",$_POST['discount_adds'][$k]));
			
			if($au->user_rights->CheckAccess('w',750)) $params['profit_exw']=abs((float)str_replace(",",".",$_POST['profit_exws'][$k]));
			if($au->user_rights->CheckAccess('w',750)) $params['profit_ddpm']=abs((float)str_replace(",",".",$_POST['profit_ddpms'][$k]));
			
			
			if($au->user_rights->CheckAccess('w',746)) $params['delivery_ddpm']=abs((float)str_replace(",",".",$_POST['delivery_ddpms'][$k]));
			
			if($au->user_rights->CheckAccess('w',746)) $params['duty_ddpm']=abs((float)str_replace(",",".",$_POST['duty_ddpms'][$k]));
			
			if($au->user_rights->CheckAccess('w',746)) $params['svh_broker']=abs((float)str_replace(",",".",$_POST['svh_brokers'][$k]));
			
			//пополним список измененного обор-ия для рассылки
			if(isset($params['discount_base'])&&((float)$params['discount_base']!=(float)$pi['discount_base'])){
				$global_flag_of_send_messages=$global_flag_of_send_messages||true;
				if(!in_array(1,$changed_price_kinds)) $changed_price_kinds[]=1;
				if(!in_array(2,$changed_price_kinds)) $changed_price_kinds[]=2;
				$local_flag_of_send_messages=$local_flag_of_send_messages||true;
				
			}
			if(isset($params['discount_add'])&&((float)$params['discount_add']!=(float)$pi['discount_add'])){
				$global_flag_of_send_messages=$global_flag_of_send_messages||true;
				if(!in_array(1,$changed_price_kinds)) $changed_price_kinds[]=1;
				if(!in_array(2,$changed_price_kinds)) $changed_price_kinds[]=2;
				$local_flag_of_send_messages=$local_flag_of_send_messages||true;
			}
			if(isset($params['profit_exw'])&&((float)$params['profit_exw']!=(float)$pi['profit_exw'])){
				$global_flag_of_send_messages=$global_flag_of_send_messages||true;
				if(!in_array(2,$changed_price_kinds)) $changed_price_kinds[]=2;
				$local_flag_of_send_messages=$local_flag_of_send_messages||true;
			}
			if(isset($params['profit_ddpm'])&&((float)$params['profit_ddpm']!=(float)$pi['profit_ddpm'])){
				$global_flag_of_send_messages=$global_flag_of_send_messages||true;
				if(!in_array(1,$changed_price_kinds)) $changed_price_kinds[]=1;
				$local_flag_of_send_messages=$local_flag_of_send_messages||true;
			}
			
			if(isset($params['delivery_ddpm'])&&((float)$params['delivery_ddpm']!=(float)$pi['delivery_ddpm'])){
				$global_flag_of_send_messages=$global_flag_of_send_messages||true;
				if(!in_array(1,$changed_price_kinds)) $changed_price_kinds[]=1;
				$local_flag_of_send_messages=$local_flag_of_send_messages||true;
			}
			
			if(isset($params['duty_ddpm'])&&((float)$params['duty_ddpm']!=(float)$pi['profit_ddpm'])){
				$global_flag_of_send_messages=$global_flag_of_send_messages||true;
				if(!in_array(1,$changed_price_kinds)) $changed_price_kinds[]=1;
				$local_flag_of_send_messages=$local_flag_of_send_messages||true;
			}
			
			if(isset($params['svh_broker'])&&((float)$params['svh_broker']!=(float)$pi['svh_broker'])){
				$global_flag_of_send_messages=$global_flag_of_send_messages||true;
				if(!in_array(1,$changed_price_kinds)) $changed_price_kinds[]=1;
				$local_flag_of_send_messages=$local_flag_of_send_messages||true;
			}
			
			if($local_flag_of_send_messages){
				 //Добавить id в массивы
				if($pi['parent_id']==0){
					//оборудование (+опции)
					if(!in_array($pi['id'], $pl_positions_ids)) $pl_positions_ids[]=$pi['id'];
					if(!in_array($pi['id'], $with_option_ids)) $with_option_ids[]=$pi['id'];
					
					//echo 'zz';
				}else{
					//опция
					//опцию добавлять только в том случае, если ее parent_id нет в списке with_option_ids
					if(!in_array($pi['id'], $pl_option_ids)&&!in_array($pi['parent_id'], $with_option_ids)) $pl_option_ids[]=$pi['id'];
				}
			}
			
			$_pi->Edit($id, $params);
			
			foreach($params as $kk=>$vv){
				if($pi[$kk]!=$vv){	
					$log->PutEntry($result['id'],'редактировал позицию прайс-листа',NULL,602, NULL,'позиция '.SecStr($pi['name']).': в поле '.$kk.' установлено значение '.SecStr($vv),$id);
					
					//дополнительные записи (для анализа в отчете Изменения в п/л)
					if($kk=='discount_base'){
						$log->PutEntry($result['id'],'редактировал базовую скидку поставщика',NULL,602, NULL,'позиция '.SecStr($pi['code']).' '.SecStr($pi['name']).': старое значение '.$pi[$kk].' %, новое значение '.SecStr($vv).' %',$id);	
					}elseif($kk=='discount_add'){
						$log->PutEntry($result['id'],'редактировал дополнительную скидку поставщика',NULL,602, NULL,'позиция '.SecStr($pi['code']).' '.SecStr($pi['name']).': старое значение '.$pi[$kk].' %, новое значение '.SecStr($vv).' %',$id);	
					}elseif($kk=='profit_exw'){
						$log->PutEntry($result['id'],'редактировал рентабельность ExW',NULL,602, NULL,'позиция '.SecStr($pi['code']).' '.SecStr($pi['name']).': старое значение '.$pi[$kk].' %, новое значение '.SecStr($vv).' %',$id);	
					}elseif($kk=='profit_ddpm'){
						$log->PutEntry($result['id'],'редактировал рентабельность DDPM',NULL,602, NULL,'позиция '.SecStr($pi['code']).' '.SecStr($pi['name']).': старое значение '.$pi[$kk].' %, новое значение '.SecStr($vv). ' %',$id);	
					}elseif($kk=='delivery_ddpm'){
						$log->PutEntry($result['id'],'редактировал доставку до Москвы',NULL,602, NULL,'позиция '.SecStr($pi['code']).' '.SecStr($pi['name']).': старое значение '.$pi[$kk]. ' , новое значение '.SecStr($vv).'  ',$id);	
					}elseif($kk=='duty_ddpm'){
						$log->PutEntry($result['id'],'редактировал сбор',NULL,602, NULL,'позиция '.SecStr($pi['code']).' '.SecStr($pi['name']).': старое значение '.$pi[$kk]. ' , новое значение '.SecStr($vv).'  ',$id);	
					}elseif($kk=='svh_broker'){
						$log->PutEntry($result['id'],'редактировал СВХ, брокер',NULL,602, NULL,'позиция '.SecStr($pi['code']).' '.SecStr($pi['name']).': старое значение '.$pi[$kk]. ' , новое значение '.SecStr($vv).'  ',$id);	
					}
					
				}
			}
			
			//внесем цену
			$price_params=array();
			$price_params['price']=((float)str_replace(",",".",$_POST['price'][$k]));
			$price_params['currency_id']=abs((int)str_replace(",",".",$_POST['currency_id'][$k]));
			$price_params['price_kind_id']=$price_kind_id;
			
			$test_price=$_price->GetItemByFields(array('pl_position_id'=>$id, 'currency_id'=>$price_params['currency_id'], 'price_kind_id'=>$price_params['price_kind_id']));
			
			$curr=$_curr->GetItemById($price_params['currency_id']);
			
			$do_log_price=false;
			if($test_price===false){
				//цены нет, внести ее	
				$price_params['pl_position_id']=$id;
				$_price->Add($price_params);
				$do_log_price=true;
				
			}else{
				//цена есть, редактировать ее
				$_price->Edit($test_price['id'], $price_params);
				$do_log_price=($price_params['price']!=$test_price['price']);
			}
			 
			if($do_log_price) {
				$log->PutEntry($result['id'],'редактировал позицию прайс-листа',NULL,602, NULL,'позиция '.SecStr($pi['name']).': Установлена цена '.$price_params['price'].' '.SecStr($curr['signature']),$id);
				
				//дополнительные записи (для анализа в отчете Изменения в п/л)
				
				if($pi['parent_id']!=0){
					$log->PutEntry($result['id'],'изменена цена',NULL,602, NULL,'опция  '.SecStr($pi['code']).' '.SecStr($pi['name']).': Старая базовая цена поставщика: '.$test_price['price'].' '.SecStr($curr['signature']).', новая цена '.$price_params['price'].' '.SecStr($curr['signature']),$pi['parent_id']);
				}else{
					$log->PutEntry($result['id'],'изменена цена',NULL,602, NULL,'позиция '.SecStr($pi['code']).' '.SecStr($pi['name']).': Старая базовая цена поставщика: '.$test_price['price'].' '.SecStr($curr['signature']).', новая цена '.$price_params['price'].' '.SecStr($curr['signature']),$id);
				}
				
				
				$global_flag_of_send_messages=$global_flag_of_send_messages||true;
				if(!in_array(1,$changed_price_kinds)) $changed_price_kinds[]=1;
				if(!in_array(2,$changed_price_kinds)) $changed_price_kinds[]=2;
				//Добавить id в массивы
				if($pi['parent_id']==0){
					//оборудование без опций!
					if(!in_array($pi['id'], $pl_positions_ids)) $pl_positions_ids[]=$pi['id'];
				}else{
					//опция
					//опцию добавлять только в том случае, если ее parent_id нет в списке with_option_ids
					if(!in_array($pi['id'], $pl_option_ids)&&!in_array($pi['parent_id'], $with_option_ids)) $pl_option_ids[]=$pi['id'];
				}
				 
			}
		
			
		}
	}
	
	
	if($global_flag_of_send_messages){
		
		//?????? дополнительные записи (для анализа в отчете Изменения в п/л)
				
		$_pls=new PlChangesMassSend($pl_positions_ids, $pl_option_ids, $with_option_ids, $pi['group_id'], $curr['id'], $changed_price_kinds, $result);
		$_pls->Send();
	}

}



elseif(isset($_POST['action'])&&($_POST['action']=="edit_base_items_aet")){
	//редактируем ПЛ поставщика
	
	if(!$au->user_rights->CheckAccess('w',602)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	//print_r($_POST);
	
	//die();
	/*
	"checked_ids[]":checked_ids,
						"price[]":price,
						"discount_id[]":discount_id,
						"discount_value[]":discount_value,
						"discount_rub_or_percent[]":discount_rub_or_percent,
						"dl_value[]":dl_value,
						"dl_rub_or_percent[]":dl_rub_or_percent*/
						
						
						
	$checked_ids=$_POST['checked_ids'];
	$price_kind_id=abs((int)$_POST['price_kind_id']);
	$_pi=new PlPosItem;
	$_price=new PlPositionPriceItem;
	$_curr=new PlCurrItem;
	$_pkg=new PriceKindItem;
	
	//пометим изменение цен для отправки сообщений
	$global_flag_of_send_messages=false;
	$pl_positions_ids=array(); $pl_option_ids=array(); $changed_price_kinds=array(); $with_option_ids=array();
	
	foreach($checked_ids as $k=>$v){
		$id=abs((int)$v);
		
		$pi=$_pi->GetItemById($id);
		
		if($pi!==false){	
			$params=array();
			$local_flag_of_send_messages=false;
			
			 
			
			//работа с блоками базовых скидок и к-тов рент-ти
			if($au->user_rights->CheckAccess('w',748)) $params['discount_base']=abs((float)str_replace(",",".",$_POST['discount_bases'][$k]));
			if($au->user_rights->CheckAccess('w',748)) $params['discount_add']=abs((float)str_replace(",",".",$_POST['discount_adds'][$k]));
			
			if($au->user_rights->CheckAccess('w',750)) $params['profit_exw']=abs((float)str_replace(",",".",$_POST['profit_exws'][$k]));
			if($au->user_rights->CheckAccess('w',750)) $params['profit_ddpm']=abs((float)str_replace(",",".",$_POST['profit_ddpms'][$k]));
			
			
			if($au->user_rights->CheckAccess('w',746)) $params['delivery_value']=abs((float)str_replace(",",".",$_POST['delivery_values'][$k]));
			
			 if($au->user_rights->CheckAccess('w',746)) $params['delivery_rub']=abs((float)str_replace(",",".",$_POST['delivery_rubs'][$k]));
			
			
			if($au->user_rights->CheckAccess('w',746)) $params['duty_ddpm']=abs((float)str_replace(",",".",$_POST['duty_ddpms'][$k]));
			
			if($au->user_rights->CheckAccess('w',746)) $params['svh_broker']=abs((float)str_replace(",",".",$_POST['svh_brokers'][$k]));
			
			if($au->user_rights->CheckAccess('w',746)) $params['broker_costs']=abs((float)str_replace(",",".",$_POST['broker_costs'][$k]));
			
			if($au->user_rights->CheckAccess('w',746)) $params['customs']=abs((float)str_replace(",",".",$_POST['customs'][$k]));
			
			//пополним список измененного обор-ия для рассылки
			if(isset($params['discount_base'])&&((float)$params['discount_base']!=(float)$pi['discount_base'])){
				$global_flag_of_send_messages=$global_flag_of_send_messages||true;
				if(!in_array(1,$changed_price_kinds)) $changed_price_kinds[]=1;
				if(!in_array(2,$changed_price_kinds)) $changed_price_kinds[]=2;
				$local_flag_of_send_messages=$local_flag_of_send_messages||true;
				
			}
			if(isset($params['discount_add'])&&((float)$params['discount_add']!=(float)$pi['discount_add'])){
				$global_flag_of_send_messages=$global_flag_of_send_messages||true;
				if(!in_array(1,$changed_price_kinds)) $changed_price_kinds[]=1;
				if(!in_array(2,$changed_price_kinds)) $changed_price_kinds[]=2;
				$local_flag_of_send_messages=$local_flag_of_send_messages||true;
			}
			if(isset($params['profit_exw'])&&((float)$params['profit_exw']!=(float)$pi['profit_exw'])){
				$global_flag_of_send_messages=$global_flag_of_send_messages||true;
				if(!in_array(2,$changed_price_kinds)) $changed_price_kinds[]=2;
				$local_flag_of_send_messages=$local_flag_of_send_messages||true;
			}
			if(isset($params['profit_ddpm'])&&((float)$params['profit_ddpm']!=(float)$pi['profit_ddpm'])){
				$global_flag_of_send_messages=$global_flag_of_send_messages||true;
				if(!in_array(1,$changed_price_kinds)) $changed_price_kinds[]=1;
				$local_flag_of_send_messages=$local_flag_of_send_messages||true;
			}
			
		 
			if(isset($params['delivery_value'])&&((float)$params['delivery_value']!=(float)$pi['delivery_value'])){
				$global_flag_of_send_messages=$global_flag_of_send_messages||true;
				if(!in_array(1,$changed_price_kinds)) $changed_price_kinds[]=1;
				$local_flag_of_send_messages=$local_flag_of_send_messages||true;
			}
			
			if(isset($params['delivery_rub'])&&((float)$params['delivery_rub']!=(float)$pi['delivery_rub'])){
				$global_flag_of_send_messages=$global_flag_of_send_messages||true;
				if(!in_array(1,$changed_price_kinds)) $changed_price_kinds[]=1;
				$local_flag_of_send_messages=$local_flag_of_send_messages||true;
			}
			
			if(isset($params['duty_ddpm'])&&((float)$params['duty_ddpm']!=(float)$pi['profit_ddpm'])){
				$global_flag_of_send_messages=$global_flag_of_send_messages||true;
				if(!in_array(1,$changed_price_kinds)) $changed_price_kinds[]=1;
				$local_flag_of_send_messages=$local_flag_of_send_messages||true;
			}
			
			if(isset($params['svh_broker'])&&((float)$params['svh_broker']!=(float)$pi['svh_broker'])){
				$global_flag_of_send_messages=$global_flag_of_send_messages||true;
				if(!in_array(1,$changed_price_kinds)) $changed_price_kinds[]=1;
				$local_flag_of_send_messages=$local_flag_of_send_messages||true;
			}
			
			if(isset($params['broker_costs'])&&((float)$params['broker_costs']!=(float)$pi['broker_costs'])){
				$global_flag_of_send_messages=$global_flag_of_send_messages||true;
				if(!in_array(1,$changed_price_kinds)) $changed_price_kinds[]=1;
				$local_flag_of_send_messages=$local_flag_of_send_messages||true;
			}
			
			if(isset($params['customs'])&&((float)$params['customs']!=(float)$pi['customs'])){
				$global_flag_of_send_messages=$global_flag_of_send_messages||true;
				if(!in_array(1,$changed_price_kinds)) $changed_price_kinds[]=1;
				$local_flag_of_send_messages=$local_flag_of_send_messages||true;
			}
			
			if($local_flag_of_send_messages){
				 //Добавить id в массивы
				if($pi['parent_id']==0){
					//оборудование (+опции)
					if(!in_array($pi['id'], $pl_positions_ids)) $pl_positions_ids[]=$pi['id'];
					if(!in_array($pi['id'], $with_option_ids)) $with_option_ids[]=$pi['id'];
					
					//echo 'zz';
				}else{
					//опция
					//опцию добавлять только в том случае, если ее parent_id нет в списке with_option_ids
					if(!in_array($pi['id'], $pl_option_ids)&&!in_array($pi['parent_id'], $with_option_ids)) $pl_option_ids[]=$pi['id'];
				}
			}
			
			$_pi->Edit($id, $params);
			
			foreach($params as $kk=>$vv){
				if($pi[$kk]!=$vv){	
					$log->PutEntry($result['id'],'редактировал позицию прайс-листа',NULL,602, NULL,'позиция '.SecStr($pi['name']).': в поле '.$kk.' установлено значение '.SecStr($vv),$id);
					
					//дополнительные записи (для анализа в отчете Изменения в п/л)
					if($kk=='discount_base'){
						$log->PutEntry($result['id'],'редактировал базовую скидку поставщика',NULL,602, NULL,'позиция '.SecStr($pi['code']).' '.SecStr($pi['name']).': старое значение '.$pi[$kk].' %, новое значение '.SecStr($vv).' %',$id);	
					}elseif($kk=='discount_add'){
						$log->PutEntry($result['id'],'редактировал дополнительную скидку поставщика',NULL,602, NULL,'позиция '.SecStr($pi['code']).' '.SecStr($pi['name']).': старое значение '.$pi[$kk].' %, новое значение '.SecStr($vv).' %',$id);	
					}elseif($kk=='profit_exw'){
						$log->PutEntry($result['id'],'редактировал рентабельность ExW',NULL,602, NULL,'позиция '.SecStr($pi['code']).' '.SecStr($pi['name']).': старое значение '.$pi[$kk].' %, новое значение '.SecStr($vv).' %',$id);	
					}elseif($kk=='profit_ddpm'){
						$log->PutEntry($result['id'],'редактировал рентабельность DDPM',NULL,602, NULL,'позиция '.SecStr($pi['code']).' '.SecStr($pi['name']).': старое значение '.$pi[$kk].' %, новое значение '.SecStr($vv). ' %',$id);	
					}elseif($kk=='delivery_value'){
						$log->PutEntry($result['id'],'редактировал Доставка + растаможка',NULL,602, NULL,'позиция '.SecStr($pi['code']).' '.SecStr($pi['name']).': старое значение '.$pi[$kk]. ' , новое значение '.SecStr($vv).'  ;',$id);	
						
					}elseif($kk=='delivery_rub'){
						$log->PutEntry($result['id'],'редактировал Доставка рублевая часть
',NULL,602, NULL,'позиция '.SecStr($pi['code']).' '.SecStr($pi['name']).': старое значение '.$pi[$kk]. ' ;, новое значение '.SecStr($vv).'  ;',$id);	
					 
					 }elseif($kk=='duty_ddpm'){
						$log->PutEntry($result['id'],'редактировал сбор',NULL,602, NULL,'позиция '.SecStr($pi['code']).' '.SecStr($pi['name']).': старое значение '.$pi[$kk]. ' , новое значение '.SecStr($vv).'  ',$id);	
					}elseif($kk=='svh_broker'){
						$log->PutEntry($result['id'],'редактировал СВХ, брокер',NULL,602, NULL,'позиция '.SecStr($pi['code']).' '.SecStr($pi['name']).': старое значение '.$pi[$kk]. ' , новое значение '.SecStr($vv).'  ',$id);	
					}elseif($kk=='broker_costs'){
						$log->PutEntry($result['id'],'редактировал затраты брокера',NULL,602, NULL,'позиция '.SecStr($pi['code']).' '.SecStr($pi['name']).': старое значение '.$pi[$kk]. ' , новое значение '.SecStr($vv).'  ',$id);	
					}elseif($kk=='customs'){
						$log->PutEntry($result['id'],'редактировал пошлину, %',NULL,602, NULL,'позиция '.SecStr($pi['code']).' '.SecStr($pi['name']).': старое значение '.$pi[$kk]. ' , новое значение '.SecStr($vv).'  ',$id);	
					}
					
					
				}
			}
			
			//внесем цену
			$price_params=array();
			$price_params['price']=((float)str_replace(",",".",$_POST['price'][$k]));
			$price_params['currency_id']=abs((int)str_replace(",",".",$_POST['currency_id'][$k]));
			$price_params['price_kind_id']=$price_kind_id;
			
			$test_price=$_price->GetItemByFields(array('pl_position_id'=>$id, 'currency_id'=>$price_params['currency_id'], 'price_kind_id'=>$price_params['price_kind_id']));
			
			$curr=$_curr->GetItemById($price_params['currency_id']);
			
			$do_log_price=false;
			if($test_price===false){
				//цены нет, внести ее	
				$price_params['pl_position_id']=$id;
				$_price->Add($price_params);
				$do_log_price=true;
				
			}else{
				//цена есть, редактировать ее
				$_price->Edit($test_price['id'], $price_params);
				$do_log_price=($price_params['price']!=$test_price['price']);
			}
			 
			if($do_log_price) {
				$log->PutEntry($result['id'],'редактировал позицию прайс-листа',NULL,602, NULL,'позиция '.SecStr($pi['name']).': Установлена цена '.$price_params['price'].' '.SecStr($curr['signature']),$id);
				
				//дополнительные записи (для анализа в отчете Изменения в п/л)
				
				if($pi['parent_id']!=0){
					$log->PutEntry($result['id'],'изменена цена',NULL,602, NULL,'опция  '.SecStr($pi['code']).' '.SecStr($pi['name']).': Старая базовая цена поставщика: '.$test_price['price'].' '.SecStr($curr['signature']).', новая цена '.$price_params['price'].' '.SecStr($curr['signature']),$pi['parent_id']);
				}else{
					$log->PutEntry($result['id'],'изменена цена',NULL,602, NULL,'позиция '.SecStr($pi['code']).' '.SecStr($pi['name']).': Старая базовая цена поставщика: '.$test_price['price'].' '.SecStr($curr['signature']).', новая цена '.$price_params['price'].' '.SecStr($curr['signature']),$id);
				}
				
				
				$global_flag_of_send_messages=$global_flag_of_send_messages||true;
				if(!in_array(1,$changed_price_kinds)) $changed_price_kinds[]=1;
				if(!in_array(2,$changed_price_kinds)) $changed_price_kinds[]=2;
				//Добавить id в массивы
				if($pi['parent_id']==0){
					//оборудование без опций!
					if(!in_array($pi['id'], $pl_positions_ids)) $pl_positions_ids[]=$pi['id'];
				}else{
					//опция
					//опцию добавлять только в том случае, если ее parent_id нет в списке with_option_ids
					if(!in_array($pi['id'], $pl_option_ids)&&!in_array($pi['parent_id'], $with_option_ids)) $pl_option_ids[]=$pi['id'];
				}
				 
			}
		
			
		}
	}
	
	
	if($global_flag_of_send_messages){
		
		//?????? дополнительные записи (для анализа в отчете Изменения в п/л)
				
		$_pls=new PlChangesMassSend($pl_positions_ids, $pl_option_ids, $with_option_ids, $pi['group_id'], $curr['id'], $changed_price_kinds, $result);
		$_pls->Send();
	}

}


elseif(isset($_POST['action'])&&($_POST['action']=="update_price")){
	if(!$au->user_rights->CheckAccess('w',602)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$_curr=new PlCurrItem;
	$_price=new PlPositionPriceItem;
	$_pi=new PlPosItem;
	
	
	$id=abs((int)$_POST['id']);
	$pi=$_pi->GetItemById($id);
	
	
	//внесем цену
	$price_params=array();
	$price_params['price']=((float)str_replace(",",".",$_POST['price']));
	$price_params['currency_id']=abs((int)str_replace(",",".",$_POST['currency_id']));
	$price_params['price_kind_id']=abs((int)str_replace(",",".",$_POST['price_kind_id']));
	
	$test_price=$_price->GetItemByFields(array('pl_position_id'=>$id, 'currency_id'=>$price_params['currency_id'], 'price_kind_id'=>$price_params['price_kind_id']));
	
	$curr=$_curr->GetItemById($price_params['currency_id']);
	
	$do_log_price=false;
	if($test_price===false){
		//цены нет, внести ее	
		$price_params['pl_position_id']=$id;
		$_price->Add($price_params);
		$do_log_price=true;
		
	}else{
		//цена есть, редактировать ее
		$_price->Edit($test_price['id'], $price_params);
		$do_log_price=($price_params['price']!=$test_price['price']);
	}
	 
	if($do_log_price) $log->PutEntry($result['id'],'редактировал позицию прайс-листа',NULL,602, NULL,'позиция '.SecStr($pi['name']).': Установлена цена '.$price_params['price'].' '.SecStr($curr['signature']),$id);

	 
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="load_price")){
	//загрузка базовой цены 
	
	$_curr=new PlCurrItem;
	$_price=new PlPositionPriceItem;
	$id=abs((int)$_POST['id']);
	
	$currency_id=abs((int)str_replace(",",".",$_POST['currency_id']));
	$price_kind_id=abs((int)str_replace(",",".",$_POST['price_kind_id']));
	$test_price=$_price->GetItemByFields(array('pl_position_id'=>$id, 'currency_id'=>$currency_id, 'price_kind_id'=>$price_kind_id));
	
	//$curr=$_curr->GetItemById($price_params['currency_id']);
	//print_r(array('pl_position_id'=>$id, 'currency_id'=>$currency_id, 'price_kind_id'=>$price_kind_id));

	$ret=$test_price['price'];
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="load_price_exw")){
	//загрузка базовой цены 
	
	$id=abs((int)$_POST['id']);
	
	$currency_id=abs((int)str_replace(",",".",$_POST['currency_id']));
	$price_kind_id=abs((int)str_replace(",",".",$_POST['price_kind_id']));
	
	/*$_curr=new PlCurrItem;
	$_price=new PlPositionPriceItem;
	
	$test_price=$_price->GetItemByFields(array('pl_position_id'=>$id, 'currency_id'=>$currency_id, 'price_kind_id'=>$price_kind_id));
	
	//$curr=$_curr->GetItemById($price_params['currency_id']);
	

	$ret=$test_price['price'];
	*/
	$_pl=new PlPosItem;
	$pl=$_pl->GetItemById($id);
	
	//нужно понимать, опция это или нет... надо написать функцию-диспетчер!!!!
	if($pl['parent_id']==0) $price=$_pl->CalcPrice($id, $currency_id,NULL, $price_kind_id, NULL);
	else  $price=$_pl->CalcOptionPrice($id, $currency_id,NULL, $price_kind_id, NULL);
	
	$ret=$price;
	
}

elseif(isset($_POST['action'])&&($_POST['action']=="add_items")){
	if(!$au->user_rights->CheckAccess('w',601)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
			
	//print_r($_POST);
	
	/*die();
	*/
						
	$checked_ids=$_POST['checked_ids'];
	$_pi=new PlPosItem;
	$_ppi=new PosItem;
	
	foreach($checked_ids as $k=>$v){
		$id=abs((int)$v);
		
		$ppi=$_ppi->GetItemById($id);
		$pi=$_pi->GetItemByFields(array('position_id'=>$id));
		
		if(($ppi!==false)&&($pi===false)){	
			$params=array();
			$params['position_id']=$id;
			$params['price']=((float)str_replace(",",".",$_POST['price'][$k]));
			
			
			//определим, какая скидка активна - заносим ее
			//если неактивно ни одной - обнуляем скидку
			$discount_ids=explode(',',$_POST['discount_id'][$k]);
			$discount_values=explode(',',$_POST['discount_value'][$k]);
			$discount_rub_or_percents=explode(',',$_POST['discount_rub_or_percent'][$k]);
			$dl_values=explode(',',$_POST['dl_value'][$k]);
			$dl_rub_or_percents=explode(',',$_POST['dl_rub_or_percent'][$k]);
			
			
			$active_discount_offset=-1;
			$active_discount_id=0;
			foreach($discount_values as $kk=>$vv){
				if(abs((float)str_replace(",",".",$vv))>0){
					$active_discount_offset=$kk;
					$active_discount_id=abs((int)$discount_ids[$kk]);
					break;
				}
			}
			
			
			if($active_discount_id!=0){
				$params['discount_id']=$active_discount_id;
				$params['discount_value']=abs((float)str_replace(",",".",$discount_values[$active_discount_offset]));
				$params['discount_rub_or_percent']=abs((int)$discount_rub_or_percents[$active_discount_offset]);
				
			}else{
				$params['discount_id']=1;
				$params['discount_value']=0;
				$params['discount_rub_or_percent']=0;
			}
			
			//print_r($params);
			
			
			
			
			
			$code=$_pi->Add($params);
			
			foreach($params as $kk=>$vv){
			
					$log->PutEntry($result['id'],'создал позицию прайс-листа',NULL,601, NULL,'позиция '.SecStr($ppi['name']).': в поле '.$kk.' установлено значение '.SecStr($vv),$code);
				
				
			}
			
			
			//внесем ограничения на скидку
			//разобрать блок ограничений
			//если текущее ограничение равно пустой строке - удалить если есть такое
			//если равно строке - внести строку
			if($au->user_rights->CheckAccess('w',605)) foreach($discount_ids as $kk=>$vv){
				$_mvi=new PlDisMaxValItem;
				$_test_mvi=$_mvi->GetItemByFields(array('pl_position_id'=>$code, 'discount_id'=>abs((int)$vv)));
				$_dis=new PlDisItem;
				$_test_dis=$_dis->GetItemById($vv);
				
				if(trim($dl_values[$kk])!=""){
					if($_test_mvi!==false){
						$m_params=array();
						/*$m_params['pl_position_id']=$id;
						$m_params['discount_id']=abs((int)$vv);*/
						$m_params['value']=abs((float)$dl_values[$kk]);
						$m_params['rub_or_percent']=abs((int)$dl_rub_or_percents[$kk]);
						
						$_mvi->Edit($_test_mvi['id'], $m_params);
					}else{
						$m_params=array();
						$m_params['pl_position_id']=$code;
						$m_params['discount_id']=abs((int)$vv);
						$m_params['value']=abs((float)$dl_values[$kk]);
						$m_params['rub_or_percent']=abs((int)$dl_rub_or_percents[$kk]);
						$_mvi->Add($m_params);
					}
					if($m_params['rub_or_percent']==0) $descr='руб.';
					elseif($m_params['rub_or_percent']==1) $descr='%';
					
					//print_r($m_params);
					
					//запись в журнал об установке ограничения
					$log->PutEntry($result['id'],'создал позицию прайс-листа',NULL,601, NULL,'позиция '.SecStr($ppi['name']).': установлено максимальное значение поля '.SecStr($_test_dis['name']).': '.$m_params['value'].' '.$descr,$id);
				}
			}
			
			
			
			
			
		}
	}
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="find_pos")){

//получим список позиций по фильтру
	$_pg=new PlPosGroupForBill;
	
	$dec=new DBDecorator;
	
	$name=SecStr(iconv("utf-8","windows-1251",$_POST['qry']));
	$group_id=abs((int)$_POST['group_id']);
	
	//$except_id=abs((int)$_POST['except_id']);
	//$dec->AddEntry(new SqlEntry('p.id',$except_id, SqlEntry::NE));
	
	$except_ids=$_POST['except_ids'];
	if(count($except_ids)>0){
		$dec->AddEntry(new SqlEntry('pl.id', NULL, SqlEntry::NOT_IN_VALUES, NULL,$except_ids));		
		
	}
	
	if(strlen($name)>0) $dec->AddEntry(new SqlEntry('p.name',$name, SqlEntry::LIKE));
	
	//if($group_id>0) $dec->AddEntry(new SqlEntry('p.group_id',$group_id, SqlEntry::E));
	if($group_id>0) {
		$dec->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		$dec->AddEntry(new SqlEntry('p.group_id',$group_id, SqlEntry::E));
		
		//найти подподгруппы
		$_pgg=new PosGroupGroup;
		$arr=$_pgg->GetItemsByIdArr($group_id);
		$arg=array();
		foreach($arr as $k=>$v){
			if(!in_array($v['id'],$arg)) $arg[]=$v['id'];
			$arr2=$_pgg->GetItemsByIdArr($v['id']);
			foreach($arr2 as $kk=>$vv){
				if(!in_array($vv['id'],$arg))  $arg[]=$vv['id'];
			}
		}
		
		if(count($arg)>0){
			$dec->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			$dec->AddEntry(new SqlEntry('p.group_id', NULL, SqlEntry::IN_VALUES, NULL,$arg));	
		}
		
		$dec->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
	}
	
	if(abs((int)$_POST['dimension_id'])>0) $dec->AddEntry(new SqlEntry('p.dimension_id',abs((int)$_POST['dimension_id']), SqlEntry::E));
	
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['length'])))>0) $dec->AddEntry(new SqlEntry('p.length',SecStr(iconv("utf-8","windows-1251",$_POST['length'])), SqlEntry::E));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['width'])))>0) $dec->AddEntry(new SqlEntry('p.width',SecStr(iconv("utf-8","windows-1251",$_POST['width'])), SqlEntry::E));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['height'])))>0) $dec->AddEntry(new SqlEntry('p.height',SecStr(iconv("utf-8","windows-1251",$_POST['height'])), SqlEntry::E));
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['diametr'])))>0) $dec->AddEntry(new SqlEntry('p.diametr',SecStr(iconv("utf-8","windows-1251",$_POST['diametr'])), SqlEntry::E));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['weight'])))>0) $dec->AddEntry(new SqlEntry('p.weight',SecStr(iconv("utf-8","windows-1251",$_POST['weight'])), SqlEntry::E));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['volume'])))>0) $dec->AddEntry(new SqlEntry('p.volume',SecStr(iconv("utf-8","windows-1251",$_POST['volume'])), SqlEntry::E));
	
	
	$_pg->itemsname='pospos';
	
	//нужен другой метод, который бы возвращал данные в нужном формате
	
	
	$_pg->ShowPos('bills/position_edit_finded.html', $dec,0,1000,false,false,true);
	
	$items=$_pg->items;
	
	//добавим стоимости, кол-во
	foreach($items as $k=>$v){
		
		$items[$k]['quantity']=0;	
		$items[$k]['price_pm']=$items[$k]['price_f'];
		$items[$k]['cost']=0;
		$items[$k]['total']=0;
		$items[$k]['nds_proc']=NDS;
		$items[$k]['nds_summ']=0;
		$items[$k]['nds_summ']=0;
		$items[$k]['value']=0;
		$items[$k]['discount_value']=0;
		/*$items[$k]['pl_discount_id']=1;
		$items[$k]['pl_discount_value']=0;
		$items[$k]['pl_discount_rub_or_percent']=0;*/
		
		$items[$k]['hash']=md5($items[$k]['pl_position_id'].'_'.$items[$k]['position_id'].'_'.$items[$k]['pl_discount_id'].'_'.$items[$k]['pl_discount_value'].'_'.$items[$k]['pl_discount_rub_or_percent']);
	}
	
	//print_r($items);
	
	$sm=new SmartyAj;
	
	$sm->assign('pospos', $items);
	$sm->assign('cannot_view_pm',!$au->user_rights->CheckAccess('w',130));
	$ret=$sm->fetch('bills/position_edit_finded.html');
	
	
	
}


//работа с группами опций
if(isset($_POST['action'])&&($_POST['action']=="redraw_plgr_dics")){
	$sm=new SmartyAj;
	if(isset($_POST['user_id'])) $user_id=abs((int)$_POST['user_id']);
	else $user_id=0;
	
	$opg=new PlGroupGroup;
	$sm->assign('opfs_total', $opg->GetItemsArr());
	$sm->assign('word', 'plgr');
	$sm->assign('named', 'Группа опций');
	
	
	$ret=$sm->fetch('pl/plgrs.html');
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="redraw_plgr_page")){
	//$sm=new SmartyAj;
	
	if(isset($_POST['user_id'])) $user_id=abs((int)$_POST['user_id']);
	else $user_id=0;
	
	$opg=new PlGroupGroup;
	$ret=$opg->GetItemsOpt($user_id);
	
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="add_plgr")){
	
	//dostup
	/*if(!$au->user_rights->CheckAccess('w',19)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	*/
	$qi=new PlGroupItem;
	$params=array();
	$params['name']=SecStr(iconv("utf-8","windows-1251",$_POST['plgr']),9);
	
	$params['name_en']=SecStr(iconv("utf-8","windows-1251",$_POST['plgr_en']),9);
	$params['ord']=abs((int)$_POST['ord']);
	$qi->Add($params);
	
	//$log->PutEntry($result['id'],'добавил ОПФ',NULL,19,NULL,$params['name']);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="edit_plgr")){
	/*if(!$au->user_rights->CheckAccess('w',19)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}*/
	
	$qi=new PlGroupItem;
	$params=array();
	$id=abs((int)$_POST['id']);
	$params['name']=SecStr(iconv("utf-8","windows-1251",$_POST['question']),9);
	$params['name_en']=SecStr(iconv("utf-8","windows-1251",$_POST['question_en']),9);
	
	
	$params['ord']=abs((int)$_POST['ord']);
	$qi->Edit($id,$params);	
	
	//$log->PutEntry($result['id'],'редактировал ОПФ',NULL,19,NULL,$params['name']);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="delete_plgr")){
	
	/*if(!$au->user_rights->CheckAccess('w',13)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}*/
	
	$qi=new PlGroupItem;
	
	$id=abs((int)$_POST['id']);
	$qi->Del($id);
	
	//$log->PutEntry($result['id'],'удалил ОПФ',NULL,19,NULL,$params['name']);
 

}elseif(isset($_POST['action'])&&($_POST['action']=="toggle_categs_by_razd_producer")){
	//получить список категорий по группе и пр-лю
	 
	$_prg=new PosGroupGroup;
	$group_id=abs((int)$_POST['group_id']);
	$producer_id=abs((int)$_POST['producer_id']);
	
	$ret=$_prg->GetItemsOptByCategoryProducer($group_id, $producer_id,0,'name',true);
	 
 
}elseif(isset($_POST['action'])&&($_POST['action']=="redraw_producer")){
	//получить список категорий по группе и пр-лю
	 
	$_prg=new PlProdGroup;
	$group_id=abs((int)$_POST['group_id']);
	 
	$pls=$_prg->GetItemsByIdArr($group_id);
	 
	 
	 
	$ret=$_prg->GetItemsByIdOpt($group_id, $pls[0]['id'],'name',true,''); //>GetItemsOptByCategoryProducer($group_id, $producer_id,0,'name',true);
	 


//получить список оборудования по категории
}elseif(isset($_POST['action'])&&($_POST['action']=="toggle_eqs_by_category")){
	$_pl=new PlPosGroup;
	
	$group_id=abs((int)$_POST['group_id2']);
	$current_id=abs((int)$_POST['current_id']);
	
	$ret=$_pl->GetItemsByIdOpt($group_id, $current_id, 'name', true);

 

}elseif(isset($_POST['action'])&&($_POST['action']=="toggle_subcategs_by_categ")){
	//получить список подкатегорий по категории
	 
	$_prg=new PosGroupGroup;
	$group_id2=abs((int)$_POST['group_id2']);
	$producer_id=abs((int)$_POST['producer_id']);
	
	$ret=$_prg->GetItemsOptByCategoryProducer($group_id2, $producer_id,0,'name',true);
	 
 
 




//обработка диалога выбора менеджера для подписи печатной формы КП
}elseif(isset($_POST['action'])&&($_POST['action']=="load_managers")){
	$user_id=abs((int)$_POST['user_id']);
	$pos_id=abs((int)$_POST['pos_id']);
	$dep_id=abs((int)$_POST['dep_id']);
	
	$decorator=new DBDecorator();
	$_usg=new UsersSGroup;
	
	$decorator->AddEntry(new SqlOrdEntry('name_s',SqlOrdEntry::ASC)); 
	if($pos_id>0) $decorator->AddEntry(new SqlEntry('u.position_id',$pos_id, SqlEntry::E));
	if($dep_id>0) $decorator->AddEntry(new SqlEntry('u.department_id',$dep_id, SqlEntry::E));
	$decorator->AddEntry(new SqlEntry('u.is_active',1, SqlEntry::E));
	
	$decorator->AddEntry(new SqlEntry('u.group_id',1, SqlEntry::E));
	
	$decorator->AddEntry(new SqlEntry('u.department_id', NULL, SqlEntry::IN_VALUES, NULL,array(16,17,19,20,21)));	
	
	
	$ret=$_usg->GetItemsByDecOpt($user_id, 'name_s',$decorator,true);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="load_pos")){
	$user_id=abs((int)$_POST['user_id']);
	$pos_id=abs((int)$_POST['pos_id']);
	$dep_id=abs((int)$_POST['dep_id']);
	
	$decorator=new DBDecorator();
	$_usg=new UserPosGroup;
	
	$decorator->AddEntry(new SqlOrdEntry('u.name',SqlOrdEntry::ASC)); 
	if($user_id>0) $decorator->AddEntry(new SqlEntry('us.id',$user_id, SqlEntry::E));
	if($dep_id>0) $decorator->AddEntry(new SqlEntry('us.department_id',$dep_id, SqlEntry::E));
	$decorator->AddEntry(new SqlEntry('us.is_active',1, SqlEntry::E));
	
	//$decorator->AddEntry(new SqlEntry('u.id', NULL, SqlEntry::IN_VALUES, NULL,array(16,17,19,20,21)));	
	
	$ret=$_usg->GetItemsByDecOpt( $pos_id, 'name',$decorator,true,'-все должности-');
	
}elseif(isset($_POST['action'])&&($_POST['action']=="load_deps")){
	$user_id=abs((int)$_POST['user_id']);
	$pos_id=abs((int)$_POST['pos_id']);
	$dep_id=abs((int)$_POST['dep_id']);
	
	$decorator=new DBDecorator();
	$_usg=new UserDepGroup;
	
	$decorator->AddEntry(new SqlOrdEntry('u.name',SqlOrdEntry::ASC)); 
	if($user_id>0) $decorator->AddEntry(new SqlEntry('us.id',$user_id, SqlEntry::E));
	if($pos_id>0) $decorator->AddEntry(new SqlEntry('us.position_id',$pos_id, SqlEntry::E));
	$decorator->AddEntry(new SqlEntry('us.is_active',1, SqlEntry::E));
	
	
		
	
	
	$decorator->AddEntry(new SqlEntry('u.id', NULL, SqlEntry::IN_VALUES, NULL,array(16,17,19,20,21)));	
	
	
	$ret=$_usg->GetItemsByDecOpt( $dep_id, 'name',$decorator,true,'-все отделы-');
	


//загрузка опций в фильтр
}elseif(isset($_POST['action'])&&($_POST['action']=="prefind_options")){
	$parent_id=abs((int)$_POST['parent_id']);
	$price_kind_id=abs((int)$_POST['price_kind_id']);
	
	
	$_plg=new PlPosGroup;
	
	
	$decorator=new DBDecorator();
	if(isset($_POST['id'])&&(strlen($_POST['id'])>0)){
			$decorator->AddEntry(new SqlEntry('p.id',SecStr($_POST['id']), SqlEntry::LIKE));
	}
	
	if(isset($_POST['code'])&&(strlen($_POST['code'])>0)){
			$decorator->AddEntry(new SqlEntry('p.code',SecStr($_POST['code']), SqlEntry::LIKE));
	}
	
	if(isset($_POST['name'])&&(strlen($_POST['name'])>0)){
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
			$decorator->AddEntry(new SqlEntry('p.name',SecStr(iconv('utf-8', 'windows-1251',$_POST['name'])), SqlEntry::LIKE));
			
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			
			$decorator->AddEntry(new SqlEntry('p.name_en',SecStr(iconv('utf-8', 'windows-1251',$_POST['name'])), SqlEntry::LIKE));
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
	}
	
	
	if(isset($_POST['dimension_id'])&&(strlen($_POST['dimension_id'])>0)){
			$decorator->AddEntry(new SqlEntry('p.dimension_id',SecStr($_POST['dimension_id']), SqlEntry::E));
	}
	
	
	if(isset($_POST['except_ids'])&&(is_array($_POST['except_ids']))){
	//echo '<tr>zzzzzzz</tr>';
		//$decorator->AddEntry(new SqlEntry('p.id',NULL, SqlEntry::NOT_IN_VALUES, NULL, $_POST['except_ids']));
			//$dec2->AddEntry(new SqlEntry('cg.group_id', NULL, SqlEntry::IN_VALUES, NULL,$arg));	
	}
	
	$options=$_plg->ShowOptionsArr($parent_id, CURRENCY_DEFAULT_ID, $price_kind_id, NULL, NULL, false, $decorator);
	
	foreach($options as $k=>$v){
		if(in_array($v['pl_id'], $_POST['except_ids'])) $v['is_checked']=true;
		$options[$k]=$v;	
	}
	
	$sm=new SmartyAj;
	
	$sm->assign('options', $options);
	
	$sm->assign('can_view_prices',$au->user_rights->CheckAccess('w',842));
	
	
	$ret=$sm->fetch('pl/options_selecter_row.html');
	
 
	
 
//загрузка опций в фильтр из карты
}elseif(isset($_POST['action'])&&($_POST['action']=="prefind_options_card")){
	$parent_id=abs((int)$_POST['parent_id']);
	$price_kind_id=abs((int)$_POST['price_kind_id']);
	
	
	$_plg=new PlPosGroup;
	
	
	$decorator=new DBDecorator();
	if(isset($_POST['id'])&&(strlen($_POST['id'])>0)){
			$decorator->AddEntry(new SqlEntry('p.id',SecStr($_POST['id']), SqlEntry::LIKE));
	}
	
	if(isset($_POST['code'])&&(strlen($_POST['code'])>0)){
			$decorator->AddEntry(new SqlEntry('p.code',SecStr($_POST['code']), SqlEntry::LIKE));
	}
	
	if(isset($_POST['name'])&&(strlen($_POST['name'])>0)){
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
			$decorator->AddEntry(new SqlEntry('p.name',SecStr(iconv('utf-8', 'windows-1251',$_POST['name'])), SqlEntry::LIKE));
			
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			
			$decorator->AddEntry(new SqlEntry('p.name_en',SecStr(iconv('utf-8', 'windows-1251',$_POST['name'])), SqlEntry::LIKE));
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
	}
	
	
	if(isset($_POST['dimension_id'])&&(strlen($_POST['dimension_id'])>0)){
			$decorator->AddEntry(new SqlEntry('p.dimension_id',SecStr($_POST['dimension_id']), SqlEntry::E));
	}
	
	
	if(isset($_POST['except_ids'])&&(is_array($_POST['except_ids']))){
	//echo '<tr>zzzzzzz</tr>';
		//$decorator->AddEntry(new SqlEntry('p.id',NULL, SqlEntry::NOT_IN_VALUES, NULL, $_POST['except_ids']));
			//$dec2->AddEntry(new SqlEntry('cg.group_id', NULL, SqlEntry::IN_VALUES, NULL,$arg));	
	}
	
	$options=$_plg->ShowOptionsArr($parent_id, CURRENCY_DEFAULT_ID, $price_kind_id, NULL, NULL, false, $decorator);
	
	foreach($options as $k=>$v){
		if(in_array($v['pl_id'], $_POST['except_ids'])) $v['is_checked']=true;
		$options[$k]=$v;	
	}
	
	$sm=new SmartyAj;
	
	$sm->assign('options', $options);
	
	$sm->assign('can_view_prices',$au->user_rights->CheckAccess('w',842));
		
	
	$ret=$sm->fetch('pl/rules/options_selecter_row.html');
	
	 
	


/*************** правила взаимозамен опций ********************************************/
}elseif(isset($_POST['action'])&&($_POST['action']=="reload_rules")){
	$_rules=new PlRules;
	$parent_id=abs((int)$_POST['parent_id']);
	
	$rules=$_rules->GetRulesArr($parent_id);
	
	$sm=new SmartyAj;
	$sm->assign('rules', $rules);
	
	$sm->assign('can_edit',$au->user_rights->CheckAccess('w',68)); 
	$ret=$sm->fetch('pl/rules/list.html');
	
}elseif(isset($_POST['action'])&&($_POST['action']=="add_rule")){
	$_rule=new PlRuleItem;
	 
	$params=array();
	$params['parent_id']=abs((int)$_POST['parent_id']);
	$params['option_id']=abs((int)$_POST['option_id']);
	$params['kind_id']=abs((int)$_POST['kind_id']);
	$params['is_fixed']=abs((int)$_POST['is_fixed']);
	$params['quantity']=abs((float)str_replace(',','.', $_POST['quantity']));
	
	$_rule->Add($params);
	
	$descr='';
	$_pl=new PlPosItem;
	$pl=$_pl->getitembyid($params['parent_id']);
	$pl1=$_pl->getitembyid($params['option_id']);
	
	$descr='Оборудование '.$pl['name'].': ';

	if($params['kind_id']==1){
		$descr.='связанные опции;';		
	}elseif($params['kind_id']==2){
		$descr.='взаимоисключающие опции;';
	}
	$descr.= ' добавлена опция '.$pl1['name'];
	if($params['quantity']>0) $descr.=' рекомендуемое кол-во: '.$params['quantity'];
	if($params['is_fixed']==1) $descr.='; строго; ';
	
	$log->PutEntry($result['id'], 'добавил правило взаимосвязи опций', NULL,68,NULL,SecStr($descr),$params['parent_id']);
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="edit_rule")){
	$_rule=new PlRuleItem;
	
	
	$id=abs((int)$_POST['id']);
	
	$rule=$_rule->GetItemById($id);
	 
	$params=array();
	 
	$params['option_id']=abs((int)$_POST['option_id']);
	$params['kind_id']=abs((int)$_POST['kind_id']);
	$params['is_fixed']=abs((int)$_POST['is_fixed']);
	$params['quantity']=abs((float)str_replace(',','.', $_POST['quantity']));
	
	$_rule->Edit($id,$params);
	
	
	$descr='';
	$_pl=new PlPosItem;
	$pl=$_pl->getitembyid($rule['parent_id']);
	$pl1=$_pl->getitembyid($params['option_id']);
	
	$descr='Оборудование '.$pl['name'].': ';

	if($params['kind_id']==1){
		$descr.='связанные опции;';		
	}elseif($params['kind_id']==2){
		$descr.='взаимоисключающие опции;';
	}
	$descr.= ' добавлена опция '.$pl1['name'];
	if($params['quantity']>0) $descr.=' рекомендуемое кол-во: '.$params['quantity'];
	if($params['is_fixed']==1) $descr.='; строго; ';
	
	$log->PutEntry($result['id'], 'редактировал правило взаимосвязи опций', NULL,68,NULL,SecStr($descr),$rule['parent_id']);
	
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="del_rule")){
	$_rule=new PlRuleItem;
	
	$id=abs((int)$_POST['id']);
	 
	 $rule=$_rule->GetItemById($id);
	
	$_rule->Del($id);
	
	$descr='';
	$_pl=new PlPosItem;
	$pl=$_pl->getitembyid($rule['parent_id']);
	$pl1=$_pl->getitembyid($rule['option_id']);
	
	$descr='Оборудование '.$pl['name'].': ';

	if($rule['kind_id']==1){
		$descr.='связанные опции;';		
	}elseif($rule['kind_id']==2){
		$descr.='взаимоисключающие опции;';
	}
	$descr.= ' удалена опция '.$pl1['name'];
	if($rule['quantity']>0) $descr.=' рекомендуемое кол-во: '.$rule['quantity'];
	if($rule['is_fixed']==1) $descr.='; строго; ';
	
	$log->PutEntry($result['id'], 'удалил правило взаимосвязи опций', NULL,68,NULL,SecStr($descr),$rule['parent_id']);
	
	
 
}elseif(isset($_POST['action'])&&($_POST['action']=="add_item")){
	$_rule1=new PlRuleItemItem;
	$_rule=new PlRuleItem;
	 
	$params=array();
	$params['rule_id']=abs((int)$_POST['rule_id']);
	$params['option_id']=abs((int)$_POST['option_id']);
	 
	$params['is_fixed']=abs((int)$_POST['is_fixed']);
	$params['is_mandatory']=abs((int)$_POST['is_mandatory']);
	$params['quantity']=abs((float)str_replace(',','.', $_POST['quantity']));
	
	
		
	$_rule1->Add($params);
	
	$rule=$_rule->GetItemById($params['rule_id']);
	
	$descr='';
	$_pl=new PlPosItem;
	$pl=$_pl->getitembyid($rule['parent_id']);
	$pl1=$_pl->getitembyid($rule['option_id']);
	$pl2=$_pl->getitembyid($params['option_id']);
	
	
	
	$descr='Оборудование '.$pl['name'].': ';

	if($rule['kind_id']==1){
		$descr.='связанные опции;';		
	}elseif($rule['kind_id']==2){
		$descr.='взаимоисключающие опции;';
	}
	$descr.= ' для опции '.$pl1['name'];
	if($rule['quantity']>0) $descr.=' рекомендуемое кол-во: '.$rule['quantity'];
	if($rule['is_fixed']==1) $descr.='; строго; ';
	
	
	$descr.= ' добавлена связанная опция '.$pl2['name'];
	if($params['quantity']>0) $descr.=' рекомендуемое кол-во: '.$params['quantity'];
	if($params['is_fixed']==1) $descr.='; строго; ';
	if($params['is_mandatory']==1) $descr.=' обязательное условие ';
	

	
	$log->PutEntry($result['id'], 'добавил связанную опцию в правило взаимосвязи опций', NULL,68,NULL,SecStr($descr),$rule['parent_id']);
	

}
elseif(isset($_POST['action'])&&($_POST['action']=="edit_item")){
	$_rule1=new PlRuleItemItem;
	
	$_rule=new PlRuleItem;
	
	$id=abs((int)$_POST['id']);
	$rule1=$_rule1->GetItemById($id);
	 
	$params=array();
	 
	$params['option_id']=abs((int)$_POST['option_id']);
	 
	$params['is_fixed']=abs((int)$_POST['is_fixed']);
	$params['quantity']=abs((float)str_replace(',','.', $_POST['quantity']));
	$params['is_mandatory']=abs((int)$_POST['is_mandatory']);
	
	$_rule1->Edit($id,$params);
	
	
	
	$rule=$_rule->GetItemById($rule1['rule_id']);
	
	$descr='';
	$_pl=new PlPosItem;
	$pl=$_pl->getitembyid($rule['parent_id']);
	$pl1=$_pl->getitembyid($rule['option_id']);
	$pl2=$_pl->getitembyid($params['option_id']);
	
	
	
	$descr='Оборудование '.$pl['name'].': ';

	if($rule['kind_id']==1){
		$descr.='связанные опции;';		
	}elseif($rule['kind_id']==2){
		$descr.='взаимоисключающие опции;';
	}
	$descr.= ' для опции '.$pl1['name'];
	if($rule['quantity']>0) $descr.=' рекомендуемое кол-во: '.$rule['quantity'];
	if($rule['is_fixed']==1) $descr.='; строго; ';
	
	
	$descr.= ' добавлена связанная опция '.$pl2['name'];
	if($params['quantity']>0) $descr.=' рекомендуемое кол-во: '.$params['quantity'];
	if($params['is_fixed']==1) $descr.='; строго; ';
	if($params['is_mandatory']==1) $descr.=' обязательное условие ';
	

	
	$log->PutEntry($result['id'], 'редактировал связанную опцию в правило взаимосвязи опций', NULL,68,NULL,SecStr($descr),$rule['parent_id']);
}
elseif(isset($_POST['action'])&&($_POST['action']=="del_item")){
	$_rule1=new PlRuleItemItem;
	$_rule=new PlRuleItem;
	
	$id=abs((int)$_POST['id']);
	
	$rule1=$_rule1->GetItemById($id);
	 
	$params=array();
	 
	 
	
	$_rule1->Del($id);
	
	
	$rule=$_rule->GetItemById($rule1['rule_id']);
	
		$descr='';
	$_pl=new PlPosItem;
	$pl=$_pl->getitembyid($rule['parent_id']);
	$pl1=$_pl->getitembyid($rule['option_id']);
	$pl2=$_pl->getitembyid($rule1['option_id']);
	
	
	$descr='Оборудование '.$pl['name'].': ';

	if($rule['kind_id']==1){
		$descr.='связанные опции;';		
	}elseif($rule['kind_id']==2){
		$descr.='взаимоисключающие опции;';
	}
	$descr.= ' для опции '.$pl1['name'];
	if($rule['quantity']>0) $descr.=' рекомендуемое кол-во: '.$rule['quantity'];
	if($rule['is_fixed']==1) $descr.='; строго; ';
	
	
	$descr.= ' удалена связанная опция '.$pl2['name'];
	if($rule1['quantity']>0) $descr.=' рекомендуемое кол-во: '.$rule1['quantity'];
	if($rule1['is_fixed']==1) $descr.='; строго; ';
	if($rule1['is_mandatory']==1) $descr.=' обязательное условие ';
	

	
	$log->PutEntry($result['id'], 'редактировал связанную опцию в правило взаимосвязи опций', NULL,68,NULL,SecStr($descr),$rule['parent_id']);
}



elseif(isset($_POST['action'])&&($_POST['action']=="log_mandatory_option")){
	 
	$descr=SecStr(iconv('utf-8', 'windows-1251', $_POST['descr']));	
	
	$_pl=new PlPosItem;
	
	$option_id=abs((int)$_POST['option_id']);
	$id=abs((int)$_POST['id']);
	
	$ob=$_pl->GetItemById($id);
	$opt=$_pl->GetItemById($option_id);
	
	$val=' Оборудование '.$ob['id'].' '.$ob['code'].' '.$ob['name'].', опция '.$opt['id'].' '.$opt['code'].' '.$opt['name'].' ';
	
	
	$log->PutEntry($result['id'], $descr, NULL, NULL,NULL,SecStr($val));
}




//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>