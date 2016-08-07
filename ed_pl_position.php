<?
session_start();
Header("Cache-Control: no-store, no-cache, must-revalidate"); //для протокола HTTP/1.1
Header("Pragma: no-cache"); // для протокола HTTP/1.1
Header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT"); // дата и время генерации страницы
header("Expires: " . date("r")); // дата и время время, когда страница будет считаться устаревшей


require_once('classes/global.php');
require_once('classes/authuser.php');
require_once('classes/smarty/SmartyAdm.class.php');
require_once('classes/smarty/Smarty.class.php');
require_once('classes/discr_table_group.php');
require_once('classes/discr_table_objects.php');
require_once('classes/discr_table_user.php');
require_once('classes/actionlog.php');

require_once('classes/positem.php');
require_once('classes/pl_positem.php');

require_once('classes/posdimgroup.php');
require_once('classes/posdimitem.php');
require_once('classes/posgroupgroup.php');
require_once('classes/posgroupitem.php');

require_once('classes/pl_proditem.php');
require_once('classes/pl_prodgroup.php');

require_once('classes/pl_disgroup.php');
require_once('classes/pl_dismaxvalgroup.php');

require_once('classes/pl_dismaxvalitem.php');
require_once('classes/pl_disitem.php');

require_once('classes/pl_groupgroup.php');
require_once('classes/pl_groupitem.php');
require_once('classes/pl_posgroup.php');
require_once('classes/pl_currgroup.php');
require_once('classes/pl_curritem.php');
require_once('classes/pl_pospriceitem.php');


require_once('classes/kp_form_item.php');
require_once('classes/kp_form_group.php');

require_once('classes/pl_changes_send.php');

require_once('classes/pl_rule/pl_rules.class.php');
require_once('classes/pl_rule/pl_rule_kind_group.class.php');


require_once('classes/messageitem.php');
require_once('classes/posgroupitem.php');
require_once('classes/rl/rl_man.php');
require_once('classes/discr_man.php');
require_once('classes/rl_sender/rl_eq_sender.php');


require_once('classes/kp_supply_pdate_group.php');
require_once('classes/kp_supply_pdate_item.php');


$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Позиция прайс-листа');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

$ui=new PosItem;
//$lc=new LoginCreator;
$log=new ActionLog;

if(!isset($_GET['action'])){
	if(!isset($_POST['action'])){
		$action=0;
	}else $action=abs((int)$_POST['action']);
}else $action=abs((int)$_GET['action']);

switch($action){
	case 0:
	$object_id=601;
	break;
	case 1:
	$object_id=602;
	break;
	case 2:
	$object_id=603;
	break;
	default:
	$object_id=601;
	break;
}
//echo $object_id;
//die();
if(!$au->user_rights->CheckAccess('w',$object_id)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}

if($action==0){
	
	//опция ли это 
	if(!isset($_GET['parent_id'])){
		if(!isset($_POST['parent_id'])){
		header("HTTP/1.1 404 Not Found");
			$parent_id=0;
			}else $parent_id=abs((int)$_POST['parent_id']);	
	}else $parent_id=abs((int)$_GET['parent_id']);
	
	
	if($parent_id==0){
	
	
	  if(!isset($_GET['group_id'])){
		  if(!isset($_POST['group_id'])){
		  header("HTTP/1.1 404 Not Found");
			  header("Status: 404 Not Found");
			  include('404.php');
			  die();
			  }else $group_id=abs((int)$_POST['group_id']);	
	  }else $group_id=abs((int)$_GET['group_id']);
	  
	  
	  if(!isset($_GET['group_id2'])){
		  if(!isset($_POST['group_id'])){
		  header("HTTP/1.1 404 Not Found");
			  header("Status: 404 Not Found");
			  include('404.php');
			  die();
			  }else $group_id2=abs((int)$_POST['group_id2']);	
	  }else $group_id2=abs((int)$_GET['group_id2']);
	  
	  
	  if(!isset($_GET['producer_id'])){
		  if(!isset($_POST['group_id'])){
		  header("HTTP/1.1 404 Not Found");
			  header("Status: 404 Not Found");
			  include('404.php');
			  die();
			  }else $producer_id=abs((int)$_POST['producer_id']);	
	  }else $producer_id=abs((int)$_GET['producer_id']);
	  
	
	}else{
		//создаем опцию
		$parent_position=$ui->getitembyid($parent_id);
		$producer_id=$parent_position['producer_id'];
		$group_id=0;
		$group_id2=0;	
		
		
		$_gi=new PosGroupItem;
		$gi=$_gi->GetItemById($parent_position['group_id']);
		
		if($gi['parent_group_id']>0){
			$gi2=$_gi->GetItemById($gi['parent_group_id']);	
			$group_id2=$gi['id'];
			$group_id=$gi2['id'];
			//echo $group_id; die();
		}else{
			
		}
	}
	
	
}


if(($action==1)||($action==2)){
	if(!isset($_GET['id'])){
		if(!isset($_POST['id'])){
			header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			include('404.php');
			die();
		}else $id=abs((int)$_POST['id']);	
	}else $id=abs((int)$_GET['id']);
	
	//проверка наличия пользователя
	$editing_user=$ui->GetItemById($id);
	if($editing_user===false){
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include('404.php');
		die();
	}
	foreach($editing_user as $k=>$v) $editing_user[$k]=stripslashes($v);
	
}


if($action==1){
	$log=new ActionLog;
	$log->PutEntry($result['id'],'открыл позицию номенклатуры/прайс-листа',NULL,68, NULL, $editing_user['code'].' '.$editing_user['name'],$id);			
}


if(($action==0)&&(isset($_POST['doNew'])||isset($_POST['doNewEdit']))){
	if(!$au->user_rights->CheckAccess('w',601)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}	
	
	$params=array();
	
	
   
   
    //обычная загрузка прочих параметров
	$params['code']=SecStr($_POST['code']);
	$group_id=abs((int)$_POST['group_id']);
	$group_id2=abs((int)$_POST['group_id2']);
	$group_id3=abs((int)$_POST['group_id3']);
	
	if($group_id3>0) $params['group_id']=$group_id3;
	elseif($group_id2>0) $params['group_id']=$group_id2;
	else $params['group_id']=$group_id;
	
	
	//$params['group_id']=abs((int)$_POST['group_id']);
	
	$params['name']=SecStr($_POST['name']);
	$params['name_en']=SecStr($_POST['name_en']);
 
	
	$params['dimension_id']=abs((int)$_POST['dimension_id']);
	$params['producer_id']=abs((int)$_POST['producer_id']);
	
	$params['parent_id']=abs((int)$_POST['parent_id']);
	$params['pl_group_id']=abs((int)$_POST['pl_group_id']);
	
	if(isset($_POST['is_active'])) $params['is_active']=1; else $params['is_active']=0;  
	
	$params['pre_quantity']=abs((float)$_POST['pre_quantity']);
	
	$params['notes']=SecStr($_POST['notes']);
	
	$params['txt_for_kp']=SecStr($_POST['txt_for_kp']);
	$params['txt_for_kp_en']=SecStr($_POST['txt_for_kp_en']);

	if(isset($_POST['is_install'])) $params['is_install']=1;
	else $params['is_install']=0;
	
	if(isset($_POST['is_delivery'])) $params['is_delivery']=1;
	else $params['is_delivery']=0;
	
	/*if(isset($_POST['is_mandatory'])) $params['is_mandatory']=1;
	else $params['is_mandatory']=0;*/
	
	
	if(isset($_POST['supply_pdate_id'])) $params['supply_pdate_id']=abs((int)$_POST['supply_pdate_id']);
	
	$params['is_mandatory']=abs((int)$_POST['is_mandatory']);
	
	$code=$ui->Add($params);
	
	//запись в журнале
	if($code>0){
		$log->PutEntry($result['id'],'создал позицию каталога',NULL,67,NULL,$params['name'],$code);	
		
		foreach($params as $k=>$v){
			$log->PutEntry($result['id'],'создал позицию каталога',NULL,67, NULL, 'в поле '.$k.' установлено значение '.$v,$code);		
			 
		}
		
		
		if($au->user_rights->CheckAccess('w',601)&&isset($_POST['to_pl'])){
			//добавим позицию в прайслист
			$_pl=new PlPosItem;
			
			$test_pl=$_pl->GetItemByFields(array('position_id'=>$code));
			
			//if($test_pl===false){
				$params1=array();
				
				
				if($test_pl===false) $params1['position_id']=$code;
				
				$params1['kp_form_id']=abs((int)$_POST['kp_form_id']);
				
				//определим, какая скидка активна - заносим ее
				//если неактивно ни одной - обнуляем скидку
				//print_r($_POST);
				
				$active_discount_id=0;
				foreach($_POST as $k=>$v) if(eregi('^discount_[0-9]+$', $k)){
					$_id=abs((int)eregi_replace('^discount_', '', $k));	
					if((trim($v)!="")&&(abs((int)$v)>0)){
					//echo($_id);	
						$active_discount_id=$_id;
						break;
					}
				}
				
				if($active_discount_id!=0){
					$params1['discount_id']=$active_discount_id;
					$params1['discount_value']=abs((float)str_replace(",",".",$_POST['discount_'.$active_discount_id]));
					$params1['discount_rub_or_percent']=abs((int)$_POST['discount_rub_or_percent_'.$active_discount_id]);
					
				}else{
					$params1['discount_id']=1;
					$params1['discount_value']=0;
					$params1['discount_rub_or_percent']=1;
				}
				
				
			    
				//работа с блоками базовых скидок и к-тов рент-ти
				if($au->user_rights->CheckAccess('w',748)) $params1['discount_base']=abs((float)str_replace(",",".",$_POST['discount_base']));
				if($au->user_rights->CheckAccess('w',748)) $params1['discount_add']=abs((float)str_replace(",",".",$_POST['discount_add']));
				
				if($au->user_rights->CheckAccess('w',750)) $params1['profit_exw']=abs((float)str_replace(",",".",$_POST['profit_exw']));
				if($au->user_rights->CheckAccess('w',750)) $params1['profit_ddpm']=abs((float)str_replace(",",".",$_POST['profit_ddpm']));
				
				
				if(isset($_POST['delivery_ddpm'])) $params1['delivery_ddpm']=abs((float)str_replace(",",".",$_POST['delivery_ddpm']));
				if(isset($_POST['delivery_ddpm_currency'])) $params1['delivery_ddpm_currency']=abs((int)str_replace(",",".",$_POST['delivery_ddpm_currency']));
				
				
				if(isset($_POST['duty_ddpm'])) $params1['duty_ddpm']=abs((float)str_replace(",",".",$_POST['duty_ddpm']));
				if(isset($_POST['duty_ddpm_currency'])) $params1['duty_ddpm_currency']=abs((int)str_replace(",",".",$_POST['duty_ddpm_currency']));
				
				if(isset($_POST['svh_broker'])) $params1['svh_broker']=abs((float)str_replace(",",".",$_POST['svh_broker']));
				if(isset($_POST['svh_broker_currency'])) $params1['svh_broker_currency']=abs((int)str_replace(",",".",$_POST['svh_broker_currency']));
				
				if(isset($_POST['delivery_value'])) $params1['delivery_value']=abs((float)str_replace(",",".",$_POST['delivery_value']));
				if(isset($_POST['delivery_value_currency'])) $params1['delivery_value_currency']=abs((int)str_replace(",",".",$_POST['delivery_value_currency']));
				
				if(isset($_POST['delivery_rub'])) $params1['delivery_rub']=abs((float)str_replace(",",".",$_POST['delivery_rub']));
				if(isset($_POST['delivery_rub_currency'])) $params1['delivery_rub_currency']=abs((int)str_replace(",",".",$_POST['delivery_rub_currency']));
				
				if(isset($_POST['customs'])) $params1['customs']=abs((float)str_replace(",",".",$_POST['customs']));
				
				if(isset($_POST['broker_costs'])) $params1['broker_costs']=abs((float)str_replace(",",".",$_POST['broker_costs']));
				
				
				if($test_pl===false) $code1=$_pl->Add($params1);	
				else{
					$code1=	$test_pl['id'];
					$_pl->Edit($test_pl['id'], $params1);
				}
					
				foreach($params1 as $kk=>$vv){
				
					$log->PutEntry($result['id'],'создал позицию прайс-листа',NULL,601, NULL,'позиция '.SecStr($params['name']).': в поле '.$kk.' установлено значение '.SecStr($vv),$code1);
					
					//$log->PutEntry($result['id'],'создал позицию прайс-листа',NULL,601, NULL,'позиция '.SecStr($params['name']).': в поле '.$kk.' установлено значение '.SecStr($vv),$code);
					
					
				
				}
				
				//	//внеесем цену
				 
				//внесем цену
				$_curr=new PlCurrItem;
				$_price=new PlPositionPriceItem;
					
				$price_params=array();
				$price_params['price']=((float)str_replace(",",".",$_POST['price']));
				$price_params['currency_id']=abs((float)str_replace(",",".",$_POST['currency_id']));
				$price_params['price_kind_id']=PRICE_KIND_DEFAULT_ID;
				$test_price=$_price->GetItemByFields(array('pl_position_id'=>$code1, 'currency_id'=>$price_params['currency_id'], 'price_kind_id'=>$price_params['price_kind_id']));
				
				$curr=$_curr->GetItemById($price_params['currency_id']);
				
				$do_log_price=false;
				if($test_price===false){
					//цены нет, внести ее	
					$price_params['pl_position_id']=$code1;
					$_price->Add($price_params);
					$do_log_price=true;
					
				}else{
					//цена есть, редактировать ее
					$_price->Edit($test_price['id'], $price_params);
					$do_log_price=((float)$price_params['price']!=(float)$test_price['price']);
				}
				 
				if($do_log_price) {
					$log->PutEntry($result['id'],'редактировал позицию прайс-листа',NULL,602, NULL,'позиция '.SecStr($params['name']).': Установлена цена '.$price_params['price'].' '.SecStr($curr['signature']),$code1);
				
					//спецзапись для отчета Изменения в ПЛ:
					if($params['parent_id']!=0){
						$log->PutEntry($result['id'],'создана опция позиции',NULL,601, NULL,'опция '.SecStr($params['code']).' '.SecStr($params['name']).': Установлена цена '.$price_params['price'].' '.SecStr($curr['signature']),$params['parent_id']);
					}
				}
				//работаем с ограничениями скидок
				
				
				 
			
				
			//}
		}
	}
	
	
	//перенаправления
	if(isset($_POST['doNew'])){
		if($parent_id==0) header("Location: pricelist.php?group_id_1=".abs((int)$_POST['group_id'])."&two_group_id_1=".abs((int)$_POST['group_id2'])."&producer_id_1=".abs((int)$_POST['producer_id']).'&price_kind_id_1='.PRICE_KIND_DEFAULT_ID."&doShow_1=1#user_".$code);
		else header("Location: ed_pl_position.php?action=1&id=".$parent_id."#options");
		die();
	}elseif(isset($_POST['doNewEdit'])){
		//если есть доступ к объекту 11 - правка пользователя - то переход туда		
		if(!$au->user_rights->CheckAccess('w',68)){
			header("HTTP/1.1 403 Forbidden");
			header("Status: 403 Forbidden");
			include("403.php");
			die();	
		}
		header("Location: ed_pl_position.php?action=1&id=".$code);
		die();	
		
	}else{
		header("Location: pricelist.php?group_id_1=".abs((int)$_POST['group_id'])."&two_group_id_1=".abs((int)$_POST['group_id2'])."&producer_id_1=".abs((int)$_POST['producer_id']).'&price_kind_id_1='.PRICE_KIND_DEFAULT_ID."&doShow_1=1#user_".$code);
		die();
	}
	
	die();
	
}elseif(($action==1)&&(isset($_POST['doEdit'])||isset($_POST['doEditStay']))){
	//редактирование pozicii
	if(!$au->user_rights->CheckAccess('w',602)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}	
	
	
	$params=array();
	
	
	$params['code']=SecStr($_POST['code']);
	//$params['group_id']=abs((int)$_POST['group_id']);
	
	$group_id=abs((int)$_POST['group_id']);
	$group_id2=abs((int)$_POST['group_id2']);
	$group_id3=abs((int)$_POST['group_id3']);
	
	if($group_id3>0) $params['group_id']=$group_id3;
	elseif($group_id2>0) $params['group_id']=$group_id2;
	else $params['group_id']=$group_id;
	
	
	$params['name']=SecStr($_POST['name']);
	$params['name_en']=SecStr($_POST['name_en']);
	 
	
	$params['dimension_id']=abs((int)$_POST['dimension_id']);
	$params['producer_id']=abs((int)$_POST['producer_id']);
	
	$params['pl_group_id']=abs((int)$_POST['pl_group_id']);
	
	$params['pre_quantity']=abs((float)$_POST['pre_quantity']);
	$params['notes']=SecStr($_POST['notes']);
	
	if(isset($_POST['is_active'])) $params['is_active']=1; else $params['is_active']=0;   
	
	$params['txt_for_kp']=SecStr($_POST['txt_for_kp']);
	$params['txt_for_kp_en']=SecStr($_POST['txt_for_kp_en']);
	
	$params['photo_for_kp']=SecStr($_POST['photo_for_kp']);
	
	
	if(isset($_POST['is_install'])) $params['is_install']=1;
	else $params['is_install']=0;
	
	if(isset($_POST['is_delivery'])) $params['is_delivery']=1;
	else $params['is_delivery']=0;
	
	
	if(isset($_POST['is_mandatory'])) $params['is_mandatory']=1;
	else $params['is_mandatory']=0;
	
	$params['is_mandatory']=abs((int)$_POST['is_mandatory']);
	
	if(isset($_POST['supply_pdate_id'])) $params['supply_pdate_id']=abs((int)$_POST['supply_pdate_id']);
	
	$ui->Edit($id,$params);
	
	
	
	//die();
	//записи в лог. сравнить старые и новые записи
	foreach($params as $k=>$v){
		
		if($k=='is_active'){
			if(addslashes($editing_user[$k])!=$v){
			  if($v==0) $log->PutEntry($result['id'],'снял активность позиции',NULL,68, NULL,SecStr($params['code'].' '.$params['name']),$id);
			  elseif($v==1) $log->PutEntry($result['id'],'установил активность позиции',NULL,68, NULL,SecStr($params['code'].' '.$params['name']),$id);
			}
			continue;	
		}
		
		
		if(addslashes($editing_user[$k])!=$v){
			if($k=='text_for_kp') $value=SecStr(substr(strip_tags($v), 0, 255).'...');
						else $value=SecStr($v);
			
			$log->PutEntry($result['id'],'редактировал позицию каталога',NULL,68, NULL, 'в поле '.$k.' установлено значение '.$value,$id);		
		}
	}
	
	
	
	
	
	
	
	//если заполнился текст кп и фото кп - 
	//рассылать сообщения всем , у кого есть доступ к данному оборудованию, 
	//что доступна форма КП!
	$_sender=new RlEqSender;
	$send_condition=true;
	
	/* echo '<pre>';


	var_dump($editing_user['photo_for_kp']);
	
	var_dump($_POST['photo_for_kp']);

	
	var_dump($editing_user['txt_for_kp']);
	
	var_dump($_POST['txt_for_kp']);
	

	echo '</pre>';
	die(); */
	
	//было пустым хотя бы одно из полей
	$send_condition=$send_condition&&(strlen(trim(strip_tags($editing_user['txt_for_kp'])))==0 || strlen(trim(strip_tags($editing_user['photo_for_kp'])))==0 ||  ($editing_user['photo_for_kp']=='/img/no.gif') );
	
	//заполнены все поля
	$send_condition=$send_condition&&(strlen(trim(strip_tags($_POST['txt_for_kp'])))>0 && strlen(trim(strip_tags($_POST['photo_for_kp'])))>0 &&  ($_POST['photo_for_kp']!='/img/no.gif') );
	
	if($send_condition){ //696
		
		$_dman=new DiscrMan;
		$_sender->LoadAndSend($id, $_dman->GetUsersByRight('w', 696));
		//die();
	}
	
	
	//работа с позицией п.л.
	$_pi=new PlPosItem;
	$test_pl=$_pi->GetItemByFields(array('position_id'=>$id));
	
	if(isset($_POST['to_pl'])){ //галочка стоит
		if($test_pl!==false){ //есть в базе в пл
				//правим	
				
				$params1=array();
				//$params1['price']=abs((float)str_replace(",",".",$_POST['price']));
				$params1['kp_form_id']=abs((int)$_POST['kp_form_id']);
				
				//определим, какая скидка активна - заносим ее
				//если неактивно ни одной - обнуляем скидку
				//print_r($_POST);
				
				$active_discount_id=0;
				foreach($_POST as $k=>$v) if(eregi('^discount_[0-9]+$', $k)){
					$_id=abs((int)eregi_replace('^discount_', '', $k));	
					if((trim($v)!="")&&(abs((int)$v)>0)){
					//echo($_id);	
						$active_discount_id=$_id;
						break;
					}
				}
				
				if($active_discount_id!=0){
					$params1['discount_id']=$active_discount_id;
					$params1['discount_value']=abs((float)str_replace(",",".",$_POST['discount_'.$active_discount_id]));
					$params1['discount_rub_or_percent']=abs((int)$_POST['discount_rub_or_percent_'.$active_discount_id]);
					
				}else{
					$params1['discount_id']=1;
					$params1['discount_value']=0;
					$params1['discount_rub_or_percent']=0;
				}
					
				if($au->user_rights->CheckAccess('w',748)) $params1['discount_base']=abs((float)str_replace(",",".",$_POST['discount_base']));
				if($au->user_rights->CheckAccess('w',748)) $params1['discount_add']=abs((float)str_replace(",",".",$_POST['discount_add']));
				
				if($au->user_rights->CheckAccess('w',750)) $params1['profit_exw']=abs((float)str_replace(",",".",$_POST['profit_exw']));
				if($au->user_rights->CheckAccess('w',750)) $params1['profit_ddpm']=abs((float)str_replace(",",".",$_POST['profit_ddpm']));
				
				if(isset($_POST['delivery_ddpm'])) $params1['delivery_ddpm']=abs((float)str_replace(",",".",$_POST['delivery_ddpm']));
				
				
				if(isset($_POST['duty_ddpm'])) $params1['duty_ddpm']=abs((float)str_replace(",",".",$_POST['duty_ddpm']));
				 
				if(isset($_POST['svh_broker'])) $params1['svh_broker']=abs((float)str_replace(",",".",$_POST['svh_broker']));
				 
				
				
					if(isset($_POST['delivery_value'])) $params1['delivery_value']=abs((float)str_replace(",",".",$_POST['delivery_value']));
				 
				
				if(isset($_POST['delivery_rub'])) $params1['delivery_rub']=abs((float)str_replace(",",".",$_POST['delivery_rub']));
				
				
				if(isset($_POST['customs'])) $params1['customs']=abs((float)str_replace(",",".",$_POST['customs']));
				
				if(isset($_POST['broker_costs'])) $params1['broker_costs']=abs((float)str_replace(",",".",$_POST['broker_costs']));
			 
				
				
				$_pi->Edit($test_pl['id'], $params1);
				
				
				foreach($params1 as $kk=>$vv){
					if($test_pl[$kk]!=$vv){
						if($kk=='text_for_kp') $value=SecStr(substr(strip_tags($vv), 0, 255).'...');
						else $value=SecStr($vv);
						
						$log->PutEntry($result['id'],'редактировал позицию прайс-листа',NULL,602, NULL,'позиция '.SecStr($params['name']).': в поле '.$kk.' установлено значение '.$value,$test_pl['id']);
						
						//дополнительные записи (для анализа в отчете Изменения в п/л)
						if($kk=='discount_base'){
							$log->PutEntry($result['id'],'редактировал базовую скидку поставщика',NULL,602, NULL,'позиция '.SecStr($params['code']).' '.SecStr($params['name']).': старое значение '.$test_pl[$kk].' %, новое значение '.SecStr($vv).' %',$id);	
						}elseif($kk=='discount_add'){
							$log->PutEntry($result['id'],'редактировал дополнительную скидку поставщика',NULL,602, NULL,'позиция '.SecStr($params['code']).' '.SecStr($params['name']).': старое значение '.$test_pl[$kk].' %, новое значение '.SecStr($vv).' %',$id);	
						}elseif($kk=='profit_exw'){
							$log->PutEntry($result['id'],'редактировал рентабельность ExW',NULL,602, NULL,'позиция '.SecStr($params['code']).' '.SecStr($params['name']).': старое значение '.$test_pl[$kk].' %, новое значение '.SecStr($vv).' %',$id);	
						}elseif($kk=='profit_ddpm'){
							$log->PutEntry($result['id'],'редактировал рентабельность DDPM',NULL,602, NULL,'позиция '.SecStr($params['code']).' '.SecStr($params['name']).': старое значение '.$test_pl[$kk].' %, новое значение '.SecStr($vv). ' %',$id);	
						}elseif($kk=='delivery_ddpm'){
							$log->PutEntry($result['id'],'редактировал доставку до Москвы',NULL,602, NULL,'позиция '.SecStr($params['code']).' '.SecStr($params['name']).': старое значение '.$test_pl[$kk]. ' &euro;, новое значение '.SecStr($vv).'  &euro;',$id);	
						}elseif($kk=='duty_ddpm'){
							$log->PutEntry($result['id'],'редактировал сбор',NULL,602, NULL,'позиция '.SecStr($params['code']).' '.SecStr($params['name']).': старое значение '.$test_pl[$kk]. ' &euro;, новое значение '.SecStr($vv).'  &euro;',$id);	
						}elseif($kk=='svh_broker'){
							$log->PutEntry($result['id'],'редактировал СВХ, брокер',NULL,602, NULL,'позиция '.SecStr($params['code']).' '.SecStr($params['name']).': старое значение '.$test_pl[$kk]. ' &euro;, новое значение '.SecStr($vv).'  &euro;',$id);	
						}elseif($kk=='delivery_value'){
							$log->PutEntry($result['id'],'редактировал  Доставка + растаможка',NULL,602, NULL,'позиция '.SecStr($params['code']).' '.SecStr($params['name']).': старое значение '.$test_pl[$kk]. ' &euro;, новое значение '.SecStr($vv).'  &euro;',$id);	
						}elseif($kk=='delivery_rub'){
							$log->PutEntry($result['id'],'редактировал  Доставка рублевая часть',NULL,602, NULL,'позиция '.SecStr($params['code']).' '.SecStr($params['name']).': старое значение '.$test_pl[$kk]. ' &euro;, новое значение '.SecStr($vv).'  &euro;',$id);	
						}
						
						
						 
					
					}
				}
				
				//пометим изменение цен для отправки сообщений
				$global_flag_of_send_messages=false;
				$changed_price_kinds=array();
				if(isset($params1['discount_base'])&&((float)$params1['discount_base']!=(float)$test_pl['discount_base'])){
					$global_flag_of_send_messages=$global_flag_of_send_messages||true;
					if(!in_array(1,$changed_price_kinds)) $changed_price_kinds[]=1;
					if(!in_array(2,$changed_price_kinds)) $changed_price_kinds[]=2;
				}
				if(isset($params1['discount_add'])&&((float)$params1['discount_add']!=(float)$test_pl['discount_add'])){
					$global_flag_of_send_messages=$global_flag_of_send_messages||true;
					if(!in_array(1,$changed_price_kinds)) $changed_price_kinds[]=1;
					if(!in_array(2,$changed_price_kinds)) $changed_price_kinds[]=2;
				}
				if(isset($params1['profit_exw'])&&((float)$params1['profit_exw']!=(float)$test_pl['profit_exw'])){
					$global_flag_of_send_messages=$global_flag_of_send_messages||true;
					if(!in_array(2,$changed_price_kinds)) $changed_price_kinds[]=2;
				}
				if(isset($params1['profit_ddpm'])&&((float)$params1['profit_ddpm']!=(float)$test_pl['profit_ddpm'])){
					$global_flag_of_send_messages=$global_flag_of_send_messages||true;
					if(!in_array(1,$changed_price_kinds)) $changed_price_kinds[]=1;
				}
				
				if(isset($params1['delivery_value'])&&((float)$params1['delivery_value']!=(float)$test_pl['delivery_value'])){
					$global_flag_of_send_messages=$global_flag_of_send_messages||true;
					if(!in_array(1,$changed_price_kinds)) $changed_price_kinds[]=1;
				}
				
				if(isset($params1['delivery_rub'])&&((float)$params1['delivery_rub']!=(float)$test_pl['delivery_rub'])){
					$global_flag_of_send_messages=$global_flag_of_send_messages||true;
					if(!in_array(1,$changed_price_kinds)) $changed_price_kinds[]=1;
				}
				
				
				if(isset($params1['broker_costs'])&&((float)$params1['broker_costs']!=(float)$test_pl['broker_costs'])){
					$global_flag_of_send_messages=$global_flag_of_send_messages||true;
					if(!in_array(1,$changed_price_kinds)) $changed_price_kinds[]=1;
				}
				
				if(isset($params1['customs'])&&((float)$params1['customs']!=(float)$test_pl['customs'])){
					$global_flag_of_send_messages=$global_flag_of_send_messages||true;
					if(!in_array(1,$changed_price_kinds)) $changed_price_kinds[]=1;
				}
				
				
				//внесем цену
				$_curr=new PlCurrItem;
				$_price=new PlPositionPriceItem;
					
				$price_params=array();
				$price_params['price']=((float)str_replace(",",".",$_POST['price']));
				$price_params['currency_id']=abs((float)str_replace(",",".",$_POST['currency_id']));
				$price_params['price_kind_id']=PRICE_KIND_DEFAULT_ID;
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
				 
				if($do_log_price){
					 $log->PutEntry($result['id'],'редактировал позицию прайс-листа',NULL,602, NULL,'позиция '.SecStr($params['name']).': Установлена цена '.$price_params['price'].' '.SecStr($curr['signature']),$id);
					
					//дополнительные записи (для анализа в отчете Изменения в п/л)
				
					if($test_pl['parent_id']!=0){
						$log->PutEntry($result['id'],'изменена цена',NULL,602, NULL,'опция  '.SecStr($params['code']).' '.SecStr($params['name']).': Старая базовая цена поставщика: '.$test_price['price'].' '.SecStr($curr['signature']).', новая цена '.$price_params['price'].' '.SecStr($curr['signature']),$test_pl['parent_id']);
					}else{
						$log->PutEntry($result['id'],'изменена цена',NULL,602, NULL,'позиция '.SecStr($params['code']).' '.SecStr($params['name']).': Старая базовая цена поставщика: '.$test_price['price'].' '.SecStr($curr['signature']).', новая цена '.$price_params['price'].' '.SecStr($curr['signature']),$id);
					}
					
					
					//изменилась цена:
					// это опция - разослать изменения только этой опции
					// это позиция - разослать изменения только этой позиции
					//отдельным письмом по exw, по dddpm
					 
					$global_flag_of_send_messages=$global_flag_of_send_messages||true;
					if(!in_array(1,$changed_price_kinds)) $changed_price_kinds[]=1;
					if(!in_array(2,$changed_price_kinds)) $changed_price_kinds[]=2;
				 
					
					
					
				}
				
				
				//рассылка сообщений об изменении цен
				if($global_flag_of_send_messages){
					$_pls=new PlChangesSend($id, $test_price['price'], $price_params['price'], $price_params['currency_id'], $changed_price_kinds, $result);
					$_pls->Send();
					 
					//die();
				}
				
				
				
		}else{ //нет в базе в пл.
			//добавим
				$params1=array();
				$params1['position_id']=$id;
				
				//$params1['price']=abs((float)str_replace(",",".",$_POST['price']));
				$params1['kp_form_id']=abs((int)$_POST['kp_form_id']);
				
				$active_discount_id=0;
				foreach($_POST as $k=>$v) if(eregi('^discount_[0-9]+$', $k)){
					$_id=abs((int)eregi_replace('^discount_', '', $k));	
					if((trim($v)!="")&&(abs((int)$v)>0)){
					//echo($_id);	
						$active_discount_id=$_id;
						break;
					}
				}
				
				if($active_discount_id!=0){
					$params1['discount_id']=$active_discount_id;
					$params1['discount_value']=abs((float)str_replace(",",".",$_POST['discount_'.$active_discount_id]));
					$params1['discount_rub_or_percent']=abs((int)$_POST['discount_rub_or_percent_'.$active_discount_id]);
					
				}else{
					$params1['discount_id']=1;
					$params1['discount_value']=0;
					$params1['discount_rub_or_percent']=0;
				}
				
				if($au->user_rights->CheckAccess('w',748)) $params1['discount_base']=abs((float)str_replace(",",".",$_POST['discount_base']));
				if($au->user_rights->CheckAccess('w',748)) $params1['discount_add']=abs((float)str_replace(",",".",$_POST['discount_add']));
				
				if($au->user_rights->CheckAccess('w',750)) $params1['profit_exw']=abs((float)str_replace(",",".",$_POST['profit_exw']));
				if($au->user_rights->CheckAccess('w',750)) $params1['profit_ddpm']=abs((float)str_replace(",",".",$_POST['profit_ddpm']));
				
				
				if(isset($_POST['delivery_ddpm'])) $params1['delivery_ddpm']=abs((float)str_replace(",",".",$_POST['delivery_ddpm']));	
				
				
				if(isset($_POST['duty_ddpm'])) $params1['duty_ddpm']=abs((float)str_replace(",",".",$_POST['duty_ddpm']));
				 
				if(isset($_POST['svh_broker'])) $params1['svh_broker']=abs((float)str_replace(",",".",$_POST['svh_broker']));
				
					if(isset($_POST['delivery_value'])) $params1['delivery_value']=abs((float)str_replace(",",".",$_POST['delivery_value']));
				 
				if(isset($_POST['delivery_rub'])) $params1['delivery_rub']=abs((float)str_replace(",",".",$_POST['delivery_rub']));
				
				
				if(isset($_POST['customs'])) $params1['customs']=abs((float)str_replace(",",".",$_POST['customs']));
				
				if(isset($_POST['broker_costs'])) $params1['broker_costs']=abs((float)str_replace(",",".",$_POST['broker_costs']));
				 				 
					
				$code1=$_pi->Add($params1);
				foreach($params1 as $kk=>$vv){
					
					if($kk=='text_for_kp') $value=SecStr(substr(strip_tags($vv), 0, 255).'...');
					else $value=SecStr($vv);
						
					
					$log->PutEntry($result['id'],'создал позицию прайс-листа',NULL,601, NULL,'позиция '.SecStr($params['name']).': в поле '.$kk.' установлено значение '.$value,$code1);
					
					
					//$log->PutEntry($result['id'],'создал позицию прайс-листа',NULL,601, NULL,'позиция '.SecStr($params['name']).': в поле '.$kk.' установлено значение '.$value,$id);
					
				}
				
				
				//внесем цену
				$_curr=new PlCurrItem;
				$_price=new PlPositionPriceItem;
					
				$price_params=array();
				$price_params['price']=((float)str_replace(",",".",$_POST['price']));
				$price_params['currency_id']=abs((float)str_replace(",",".",$_POST['currency_id']));
				$price_params['price_kind_id']=PRICE_KIND_DEFAULT_ID;
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
				 
				if($do_log_price) {
					$log->PutEntry($result['id'],'редактировал позицию прайс-листа',NULL,602, NULL,'позиция '.SecStr($params['name']).': Установлена цена '.$price_params['price'].' '.SecStr($curr['signature']),$id);
					//дополнительная запись для отчета Изменения в ПЛ
					if($params['parent_id']!=0){
				 
						$log->PutEntry($result['id'],'создана опция позиции',NULL,601, NULL,'опция '.SecStr($params['code']).' '.SecStr($params['name']).': Установлена цена '.$price_params['price'].' '.SecStr($curr['signature']),$editing_user['parent_id']);
					 
					}
						
				}
				
				
				
				
				
		}
	}else{ //галочки нет
		
		//die();
		
		if($test_pl!==false){ //есть в базе в пл
			//удалить, если снимаем из формы
			if(($_POST['was_pl']==1)&&$au->user_rights->CheckAccess('w',603)&&$_pi->CanDelete($test_pl['id'])){
				$_pi->Del($test_pl['id']);
				$log->PutEntry($result['id'],'удалил позицию прайс-листа',NULL,603, NULL,'позиция '.SecStr($test_pl['name']),$test_pl['id']);
				
				if($test_pl['parent_id']!=0){
					$log->PutEntry($result['id'],'удалил опцию прайс-листа',NULL,603, NULL,  'опция '.SecStr($test_pl['code']).' '.SecStr($test_pl['name']),$test_pl['parent_id']);
				}
				 
			}
		}else{ //нет в базе в пл.
			//не делать ничего
		}
	
	}
	
	 
	
	
	
	
	
	//перенаправления
	if(isset($_POST['doEdit'])){
		if($editing_user['parent_id']==0) header("Location: pricelist.php?group_id_1=".abs((int)$_POST['group_id'])."&two_group_id_1=".abs((int)$_POST['group_id2'])."&producer_id_1=".abs((int)$_POST['producer_id']).'&price_kind_id_1='.PRICE_KIND_DEFAULT_ID."&doShow_1=1#user_".$id);
		else header("Location: ed_pl_position.php?action=1&id=".$editing_user['parent_id']."#options");
		die();
	}elseif(isset($_POST['doEditStay'])){
		//если есть доступ к объекту 11 - правка пользователя - то переход туда		
		if(!$au->user_rights->CheckAccess('w',68)){
			header("HTTP/1.1 403 Forbidden");
			header("Status: 403 Forbidden");
			include("403.php");
			die();	
		}
		header("Location: ed_pl_position.php?action=1&id=".$id);
		die();	
		
	}else{
		header("Location: pricelist.php?group_id_1=".abs((int)$_POST['group_id'])."&two_group_id_1=".abs((int)$_POST['group_id2'])."&producer_id_1=".abs((int)$_POST['producer_id']).'&price_kind_id_1='.PRICE_KIND_DEFAULT_ID."&doShow=_11#user_".$id);
		die();
	}
	
	
	die();
}elseif(($action==2)){
	if(!$au->user_rights->CheckAccess('w',603)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}	
	
	if($ui->CanDelete($id)){
		$ui->Del($id);
	
		$log->PutEntry($result['id'],'удалил позицию каталога',NULL,69, NULL, NULL,$id);	
	
	}
	header("Location: pricelist.php");
	die();
}







//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->display('top.html');
unset($smarty);


$_menu_id=48;
	include('inc/menu.php');
	
	
	//демонстрация стартовой страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	
	if($action==0){
		//создание позиции
		
		$sm1=new SmartyAdm;
		//тест
		
		
	
		$_dim_group=new PosDimGroup;
		$_group_group=new PosGroupGroup;
		$_pl_prods=new PlProdGroup;
		$_pl_opts=new PlGroupGroup;
		
		$dim_gr=$_dim_group->GetItemsArr();
		$dim_ids=array(); $dim_vals=array();
		foreach($dim_gr as $k=>$v){
			$dim_ids[]=$v['id']; $dim_vals[]=$v['name'];
		}
		$sm1->assign('dim_ids',$dim_ids); 
		$sm1->assign('dims',$dim_vals);
		$sm1->assign('dim_items',$dim_gr);
		
		//раздел
		$dim_gr=$_group_group->GetItemsArr($group_id);
		$dim_ids=array(); $dim_vals=array();
		foreach($dim_gr as $k=>$v){
			$dim_ids[]=$v['id']; $dim_vals[]=$v['name'];
		}
		$sm1->assign('group_ids',$dim_ids); 
		$sm1->assign('group_values',$dim_vals);
		$sm1->assign('group_id',$group_id);
		$sm1->assign('items',$dim_gr);
		
		//категория
		$dim_gr=$_group_group->GetItemsByIdArr($group_id, $group_id2);
		$dim_ids=array(); $dim_vals=array();
		foreach($dim_gr as $k=>$v){
			$dim_ids[]=$v['id']; $dim_vals[]=$v['name'];
		}
		$sm1->assign('group_ids2',$dim_ids); 
		$sm1->assign('group_values2',$dim_vals);
		$sm1->assign('group_id2',$group_id2);
		
		//подкатегория
		$dim_gr=$_group_group->GetItemsByIdArr($group_id2,  $group_id3);
		$dim_ids=array(); $dim_vals=array();
		foreach($dim_gr as $k=>$v){
			$dim_ids[]=$v['id']; $dim_vals[]=$v['name'];
		}
		$sm1->assign('group_ids3',$dim_ids); 
		$sm1->assign('group_values3',$dim_vals);
		$sm1->assign('group_id3',$group_id2);
		
		
		
		//пр-ль
		$dim_gr=$_pl_prods->GetItemsArr($producer_id);
		$dim_ids=array(); $dim_vals=array();
		foreach($dim_gr as $k=>$v){
			$dim_ids[]=$v['id']; $dim_vals[]=$v['name'];
		}
		$sm1->assign('producer_ids',$dim_ids); 
		$sm1->assign('producer_values',$dim_vals);
		$sm1->assign('producer_id',$producer_id);
		$sm1->assign('parent_id',$parent_id);
		
		
		//группа опций
		$dim_gr=$_pl_opts->GetItemsArr(0);
		$dim_ids=array(); $dim_vals=array();
		foreach($dim_gr as $k=>$v){
			$dim_ids[]=$v['id']; $dim_vals[]=$v['name'];
		}
		$sm1->assign('pl_group_ids',$dim_ids); 
		$sm1->assign('pl_group_values',$dim_vals);
		$sm1->assign('plgrs', $dim_gr);
		
		
		//$sm1->assign('items',$dim_gr);
		//$gr_gr=$_group_group->GetItemsArr();
		
		
		//скидки и их ограничения
		//$_dgv=new PlDisMaxValGroup;
		//$sm1->assign('discs', $_dgv->GetItemsByIdArr(0));
		
		//валюты
		
		$_pl_prod=new PlProdItem;
		$currency_id=CURRENCY_DEFAULT_ID;
		$pl_prod=$_pl_prod->Getitembyid($producer_id);
		if($pl_prod!==false) $currency_id=$pl_prod['currency_id'];
		$_curr=new PlCurrGroup;
		$sm1->assign('currs', $_curr->GetItemsArr($currency_id ));
		
		
		//сроки поставки
		$_ksm=new KpSupplyPdateGroup;
		$ksm=$_ksm->GetItemsByIdArr($group_id);
		$_ids=array(); $_vals=array();
		foreach($ksm as $k=>$v){
			$_ids[]=$v['id']; $_vals[]=$v['name'];	
		}
		$sm1->assign('supply_pdate_id_ids',$_ids);
		$sm1->assign('supply_pdate_vals',$_vals);
		
		
		//формы КП
		$_kpg=new KpFormGroup;
	 
		$up=$_kpg->GetItemsArr(0);
		$uu_ids=array(); $uu_names=array();
		$uu_ids[]=0;
		$uu_names[]='-выберите-';	
		foreach($up as $k=>$v){
			$uu_ids[]=$v['id'];
			$uu_names[]=$v['name'];	
		}
		
		//$sm1->assign('position_ids',$up);
		$sm1->assign('kp_form_ids',$uu_ids);
		$sm1->assign('kp_form_values',$uu_names);
		
		
		
		
		$sm1->assign('can_create',$au->user_rights->CheckAccess('w',601)); 
		$sm1->assign('can_edit',$au->user_rights->CheckAccess('w',602)); 

		$sm1->assign('can_expand_groups',$au->user_rights->CheckAccess('w',70)); 
		
		$sm1->assign('can_expand_dims',$au->user_rights->CheckAccess('w',71)); 
		
		$sm1->assign('can_add_to_pl',$au->user_rights->CheckAccess('w',601)); 
		$sm1->assign('can_del_pl',$au->user_rights->CheckAccess('w',603));
		
		$sm1->assign('can_max_val',$au->user_rights->CheckAccess('w',605));
		
		
		$sm1->assign('can_view_rent_koef',$au->user_rights->CheckAccess('w',749));
		$sm1->assign('can_view_base_discount',$au->user_rights->CheckAccess('w',747));
		$sm1->assign('can_edit_base_discount',$au->user_rights->CheckAccess('w',748));
		$sm1->assign('can_edit_rent_koef',$au->user_rights->CheckAccess('w',750));
		$sm1->assign('can_view_base_price',$au->user_rights->CheckAccess('w',745));
		$sm1->assign('can_edit_base_price',$au->user_rights->CheckAccess('w',746));
		
		
	
		$user_form=$sm1->fetch('pl/pl_position_create.html');
	}elseif($action==1){
		//редактирование позиции
		
		
		
		
		$sm1=new SmartyAdm;
		
		
		$sm1->assign('session_id',session_id());
		
		
		
		$_dim_group=new PosDimGroup;
		$_group_group=new PosGroupGroup;
		$dim_gr=$_dim_group->GetItemsArr();
		$_pl_prods=new PlProdGroup;
		$_pl_opts= new PlGroupGroup;
		
		
		$dim_ids=array(); $dim_vals=array();
		foreach($dim_gr as $k=>$v){
			$dim_ids[]=$v['id']; $dim_vals[]=$v['name'];
		}
		$sm1->assign('dim_ids',$dim_ids); 
		$sm1->assign('dims',$dim_vals);
		$sm1->assign('dim',$editing_user['dimension_id']);
		$sm1->assign('dim_items',$dim_gr);
		
		
		//пр-ль
		$dim_gr=$_pl_prods->GetItemsArr($producer_id);
		$dim_ids=array(); $dim_vals=array();
		foreach($dim_gr as $k=>$v){
			$dim_ids[]=$v['id']; $dim_vals[]=$v['name'];
		}
		$sm1->assign('producer_ids',$dim_ids); 
		$sm1->assign('producer_values',$dim_vals);
		//$sm1->assign('producer_id',$producer_id);
		
		
			//группа опций
		$dim_gr=$_pl_opts->GetItemsArr($editing_user['parent_id']);
		$dim_ids=array(); $dim_vals=array();
		foreach($dim_gr as $k=>$v){
			$dim_ids[]=$v['id']; $dim_vals[]=$v['name'];
		}
		$sm1->assign('pl_group_ids',$dim_ids); 
		$sm1->assign('pl_group_values',$dim_vals);
		$sm1->assign('plgrs', $dim_gr);
		
		
		
		
		
		$_gi=new PosGroupItem;
		$gi=$_gi->GetItemById($editing_user['group_id']);
		if($gi['parent_group_id']>0){
			$gi2=$_gi->GetItemById($gi['parent_group_id']);	
			if($gi2['parent_group_id']>0){
				$gi3=$_gi->GetItemById($gi2['parent_group_id']);		
				$current_one_id=$gi3['id'];
				$current_two_id=$gi2['id'];
				$current_three_id=$gi['id'];
			}else{
				$current_one_id=$gi2['id'];
				$current_two_id=$gi['id'];
				$current_three_id=0;	
			}
		}else{
			$current_one_id=$gi['id'];
			$current_two_id=0;
			$current_three_id=0;	
		}
		
		/*echo $current_one_id;
			echo $current_two_id=0;
			echo $current_three_id=0;
		*/
		
		
		$dim_gr=$_group_group->GetItemsArr();
		$dim_ids=array(); $dim_vals=array(); 
		
		foreach($dim_gr as $k=>$v){
			$dim_ids[]=$v['id']; $dim_vals[]=$v['name'];
			
		}
		$sm1->assign('group_ids',$dim_ids); 
		$sm1->assign('group_values',$dim_vals);
		$sm1->assign('group_id',$current_one_id); //$editing_user['group_id']);
		$sm1->assign('items',$dim_gr);
		
		
		
		
		
		//подгруппы
		$dim_gr=$_group_group->GetItemsByIdArr($current_one_id);
		$dim_ids=array(); $dim_vals=array(); 
		$dim_ids[]=''; $dim_vals[]='-выберите-';
		foreach($dim_gr as $k=>$v){
			$dim_ids[]=$v['id']; $dim_vals[]=$v['name'];
			
		}
		$sm1->assign('gr_ids2',$dim_ids); 
		$sm1->assign('gr_names2',$dim_vals);
		$sm1->assign('gr_id2',$current_two_id);
		
		//подподгруппы
		if($current_two_id>0){
			$dim_gr=$_group_group->GetItemsByIdArr($current_two_id);
			$dim_ids=array(); $dim_vals=array(); 
			$dim_ids[]=''; $dim_vals[]='-выберите-';
			foreach($dim_gr as $k=>$v){
				$dim_ids[]=$v['id']; $dim_vals[]=$v['name'];
				
			}
			$sm1->assign('gr_ids3',$dim_ids); 
			$sm1->assign('gr_names3',$dim_vals);
			$sm1->assign('gr_id3',$current_three_id);
		}
		
		
		$gr_gr=$_group_group->GetItemsArr();
		
		
		$sm1->assign('can_expand_groups',$au->user_rights->CheckAccess('w',70)); 
		
		$sm1->assign('can_expand_dims',$au->user_rights->CheckAccess('w',71)); 
		
		$sm1->assign('can_create',$au->user_rights->CheckAccess('w',601)); 
		$sm1->assign('can_edit',$au->user_rights->CheckAccess('w',602)); 
		
		
		//сроки поставки
		$_ksm=new KpSupplyPdateGroup;
		$ksm=$_ksm->GetItemsByIdArr($current_one_id);
		$_ids=array(); $_vals=array();
		foreach($ksm as $k=>$v){
			$_ids[]=$v['id']; $_vals[]=$v['name'];	
		}
		$sm1->assign('supply_pdate_id_ids',$_ids);
		$sm1->assign('supply_pdate_vals',$_vals);
		
		
		
		//опции
		if($editing_user['parent_id']==0){
			$_pl_p=new PlPosGroup;
		
			$pl_options=$_pl_p->ShowOptionsArr($editing_user['id']);
			$sm1->assign('pl_options', $pl_options);
			$sm1->assign('can_delete',$au->user_rights->CheckAccess('w',603)); 
			
			
			//правила для опций
			$_rules=new PlRules;
			$_rk=new PlRuleKindGroup;
			$sm1->assign('rules', $_rules->GetRulesArr($editing_user['id']));
			$sm1->assign('rules_kinds', $_rk->GetItemsOpt(0,'name',false));
			
			 
		}
		
		
		
		//работа с позицией прайс-листа
		$sm1->assign('can_add_to_pl',$au->user_rights->CheckAccess('w',601)); 
		$sm1->assign('can_edit_pl',$au->user_rights->CheckAccess('w',602)); 
		$sm1->assign('can_del_pl',$au->user_rights->CheckAccess('w',603));
		$sm1->assign('can_max_val',$au->user_rights->CheckAccess('w',605));
		
		
		$sm1->assign('can_view_rent_koef',$au->user_rights->CheckAccess('w',749));
		$sm1->assign('can_view_base_discount',$au->user_rights->CheckAccess('w',747));
		$sm1->assign('can_edit_base_discount',$au->user_rights->CheckAccess('w',748));
		$sm1->assign('can_edit_rent_koef',$au->user_rights->CheckAccess('w',750));
		$sm1->assign('can_view_base_price',$au->user_rights->CheckAccess('w',745));
		$sm1->assign('can_edit_base_price',$au->user_rights->CheckAccess('w',746));
		
		
		
		$_pi=new PlPosItem;
		
		$ppi=$_pi->GetItemByFields(array('position_id'=>$editing_user['id']));
		
		
		
			
		//скидки и их ограничения
		//$_dgv=new PlDisMaxValGroup;
		//$sm1->assign('discs', $_dgv->GetItemsByIdArr($ppi['id']));
		
		
		$_pl_prod=new PlProdItem;
		$currency_id=CURRENCY_DEFAULT_ID;
		$pl_prod=$_pl_prod->Getitembyid($editing_user['producer_id']);
		if($pl_prod!==false) $currency_id=$pl_prod['currency_id'];
		
		//echo $currency_id;
		
		//валюты
		$_curr=new PlCurrGroup;
		$sm1->assign('currs', $_curr->GetItemsArr($currency_id));
		
		$_curr_item=new PlCurrItem;
		$curr_item=$_curr_item->GetItemById($currency_id);
		$sm1->assign('signature', $curr_item['signature']); 
		
		
		//формы КП
		$_kpg=new KpFormGroup;
	 
		$up=$_kpg->GetItemsArr($editing_user['kp_form_id']);
		$uu_ids=array(); $uu_names=array();
		$uu_ids[]=0;
		$uu_names[]='-выберите-';	
		foreach($up as $k=>$v){
			$uu_ids[]=$v['id'];
			$uu_names[]=$v['name'];	
		}
		
		//$sm1->assign('position_ids',$up);
		$sm1->assign('kp_form_ids',$uu_ids);
		$sm1->assign('kp_form_values',$uu_names);
		
		
		
		
		/*echo '<pre>';
		print_r($_dgv->GetItemsByIdArr($ppi['id']));
		print_r($ppi);
		echo '</pre>';
		*/
		
		if($ppi!==false){
			//есть в п.л.
			$sm1->assign('can_to_pl', $au->user_rights->CheckAccess('w',603)&&$_pi->CanDelete($ppi['id']));
			$sm1->assign('has_pl', 1);
			
			$sm1->assign('price_f', $_pi->CalcPriceF($ppi['id'], $currency_id, NULL, $ppi));
			
			$sm1->assign('can_pl_fields', $au->user_rights->CheckAccess('w',602));
			
			$_price=new PlPositionPriceItem;
			$price=$_price->GetItemByFields(array('pl_position_id'=>$editing_user['id'], 'currency_id'=>$currency_id, 'price_kind_id'=>PRICE_KIND_DEFAULT_ID));
			$ppi['price']=$price['price'];
			$ppi['currency_id']=$price['currency_id']; 
			// echo $editing_user['price'];
				 
		}else{
			//нет в п.л.
			
			$sm1->assign('can_to_pl', $au->user_rights->CheckAccess('w',601));
			$sm1->assign('has_pl', 0);
			
			$sm1->assign('price_f','-');
			
			
			$sm1->assign('can_pl_fields', $au->user_rights->CheckAccess('w',601));
		}
		
		$sm1->assign('PRICE_KIND_DEFAULT_ID', PRICE_KIND_DEFAULT_ID);
		$sm1->assign('position',$editing_user);
		$sm1->assign('pl_item', $ppi);
		
		$user_form=$sm1->fetch('pl/pl_position_edit.html');
		
		//покажем журнал событий по позиции
		if($au->user_rights->CheckAccess('w',517)){
			$sm->assign('has_syslog',true);
			
			$decorator=new DBDecorator;
	
	
		
			
			
			
			
			if(isset($_GET['user_subj_login'])&&(strlen($_GET['user_subj_login'])>0)){
				$decorator->AddEntry(new SqlEntry('s.login',SecStr($_GET['user_subj_login']), SqlEntry::LIKE));
				$decorator->AddEntry(new UriEntry('user_subj_login',$_GET['user_subj_login']));
			}
			
			if(isset($_GET['description'])&&(strlen($_GET['description'])>0)){
				$decorator->AddEntry(new SqlEntry('l.description',SecStr($_GET['description']), SqlEntry::LIKE));
				$decorator->AddEntry(new UriEntry('description',$_GET['description']));
			}
			
			if(isset($_GET['object_id'])&&(strlen($_GET['object_id'])>0)){
				$decorator->AddEntry(new SqlEntry('l.object_id',SecStr($_GET['object_id']), SqlEntry::E));
				$decorator->AddEntry(new UriEntry('object_id',$_GET['object_id']));
			}
			
			if(isset($_GET['user_obj_login'])&&(strlen($_GET['user_obj_login'])>0)){
				$decorator->AddEntry(new SqlEntry('o.login',SecStr($_GET['user_obj_login']), SqlEntry::LIKE));
				$decorator->AddEntry(new UriEntry('user_obj_login',$_GET['user_obj_login']));
			}
			
			if(isset($_GET['user_group_id'])&&(strlen($_GET['user_group_id'])>0)){
				$decorator->AddEntry(new SqlEntry('l.user_group_id',SecStr($_GET['user_group_id']), SqlEntry::E));
				$decorator->AddEntry(new UriEntry('user_group_id',$_GET['user_group_id']));
			}
			
			if(isset($_GET['ip'])&&(strlen($_GET['ip'])>0)){
				$decorator->AddEntry(new SqlEntry('ip',SecStr($_GET['ip']), SqlEntry::LIKE));
				$decorator->AddEntry(new UriEntry('ip',$_GET['ip']));
			}
			
			
			
			//сортировку можно подписать как дополнительный параметр для UriEntry
			if(!isset($_GET['sortmode'])){
				$sortmode=0;	
			}else{
				$sortmode=abs((int)$_GET['sortmode']);
			}
			
			
			switch($sortmode){
				case 0:
					$decorator->AddEntry(new SqlOrdEntry('pdate',SqlOrdEntry::DESC));
				break;
				case 1:
					$decorator->AddEntry(new SqlOrdEntry('pdate',SqlOrdEntry::ASC));
				break;
				case 2:
					$decorator->AddEntry(new SqlOrdEntry('s.login',SqlOrdEntry::DESC));
				break;	
				case 3:
					$decorator->AddEntry(new SqlOrdEntry('s.login',SqlOrdEntry::ASC));
				break;
				case 4:
					$decorator->AddEntry(new SqlOrdEntry('l.description',SqlOrdEntry::DESC));
				break;
				case 5:
					$decorator->AddEntry(new SqlOrdEntry('l.description',SqlOrdEntry::ASC));
				break;	
				case 6:
					$decorator->AddEntry(new SqlOrdEntry('ob.name',SqlOrdEntry::DESC));
				break;
				case 7:
					$decorator->AddEntry(new SqlOrdEntry('ob.name',SqlOrdEntry::ASC));
				break;
				case 8:
					$decorator->AddEntry(new SqlOrdEntry('o.login',SqlOrdEntry::DESC));
				break;	
				case 9:
					$decorator->AddEntry(new SqlOrdEntry('o.login',SqlOrdEntry::ASC));
				break;
				case 10:
					$decorator->AddEntry(new SqlOrdEntry('gr.name',SqlOrdEntry::DESC));
				break;
				case 11:
					$decorator->AddEntry(new SqlOrdEntry('gr.name',SqlOrdEntry::ASC));
				break;	
				case 12:
					$decorator->AddEntry(new SqlOrdEntry('ip',SqlOrdEntry::DESC));
				break;
				case 13:
					$decorator->AddEntry(new SqlOrdEntry('ip',SqlOrdEntry::ASC));
				break;	
				default:
					$decorator->AddEntry(new SqlOrdEntry('pdate',SqlOrdEntry::DESC));
				break;	
				
			}
			$decorator->AddEntry(new SqlOrdEntry('id',SqlOrdEntry::DESC));
			
			$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
			
			
			
			if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
			else $from=0;
			
			if(isset($_GET['to_page'])) $to_page=abs((int)$_GET['to_page']);
			else $to_page=ITEMS_PER_PAGE;
			$decorator->AddEntry(new UriEntry('to_page',$to_page));
		
			$decorator->AddEntry(new SqlEntry('object_id',NULL, SqlEntry::IN_VALUES, NULL, array(64,
70,
150,
151,
67,
71,
68,
69,
600,
601,
602,
603,
604,
605

)));
			$decorator->AddEntry(new SqlEntry('affected_object_id',$id, SqlEntry::E));
			$decorator->AddEntry(new UriEntry('action',1));
			$decorator->AddEntry(new UriEntry('id',$id));
			$decorator->AddEntry(new UriEntry('do_show_log',1));
			if(!isset($_GET['do_show_log'])){
				$do_show_log=false;
			}else{
				$do_show_log=true;
			}
			$sm->assign('do_show_log',$do_show_log);
			
			$llg=$log->ShowLog('syslog/log_doc.html',$decorator,$from,$to_page,'ed_pl_position.php',true,true,true);
			
			$sm->assign('syslog',$llg);		
				
		}
		
		
	}
	
	
	$sm->assign('users',$user_form);
	$content=$sm->fetch('catalog/position.html');
	
	
	
	$smarty->assign('fast_menu', $menu_arr_fast);
	  $smarty->assign('main_menu',$menu_res);
	$smarty->assign('content',$content);
	$smarty->display('page.html');
	unset($smarty);


$smarty = new SmartyAdm;

//работа с футером
require_once('inc/footer.php');
if(isset($footer_res)){
	$smarty->assign('footer',$footer_res);
}else $smarty->assign('footer','');

$smarty->display('bottom.html');
unset($smarty);
?>