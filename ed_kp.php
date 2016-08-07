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

require_once('classes/posgroupgroup.php');
require_once('classes/positem.php');

require_once('classes/posdimitem.php');
require_once('classes/bdetailsitem.php');
require_once('classes/bdetailsgroup.php');

require_once('classes/kpitem.php');
require_once('classes/kppositem.php');
require_once('classes/kpposgroup.php');
require_once('classes/billpospmformer.php');

require_once('classes/user_s_item.php');

require_once('classes/posgroupitem.php');


require_once('classes/orgitem.php');
require_once('classes/opfitem.php');

require_once('classes/kpnotesgroup.php');
require_once('classes/kpnotesitem.php');

require_once('classes/kpcreator.php');

require_once('classes/pergroup.php');

require_once('classes/period_checker.php');

require_once('classes/pl_disitem.php');
require_once('classes/pl_disgroup.php');
require_once('classes/pl_positem.php');
require_once('classes/pl_dismaxvalgroup.php');

require_once('classes/supcontract_item.php');
require_once('classes/supcontract_group.php');

require_once('classes/kp_pospmformer.php');

require_once('classes/pl_currgroup.php');
require_once('classes/pl_curritem.php');
require_once('classes/kp_supply_group.php');
require_once('classes/kp_paymode_group.php');
require_once('classes/kp_paymode_item.php');
require_once('classes/kp_form_item.php');
require_once('classes/user_s_group.php');
require_once('classes/kp_warranty_group.php');
require_once('classes/kp_warranty_item.php');


require_once('classes/kp_supply_pdate_group.php');
require_once('classes/kp_supply_pdate_item.php');
require_once('classes/kp_supply_item.php');

require_once('classes/price_kind_item.php');

require_once('classes/round_define.class.php');

require_once('classes/pl_rule/pl_rules.class.php');

require_once('classes/pl_posgroup.php');


require_once('classes/user_to_user.php');

require_once('classes/supplieritem.php');
require_once('classes/suppliercontactitem.php');
require_once('classes/lead.class.php');
require_once('classes/tz.class.php');


$_orgitem=new OrgItem;


$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Редактирование коммерческого предложения');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

$_bill=new KpItem;
$_bpi=new KpPosItem;
$_position=new PosItem;
$_round_define=new RoundDefine;

$log=new ActionLog;

$_posgroupgroup=new PosGroupGroup;


$lc=new KpCreator;

$_supgroup=new SuppliersGroup;
$_opf=new OpfItem;


$_pch=new PeriodChecker;
$pch_date=$_pch->GetDate();


if(!isset($_GET['action'])){
	if(!isset($_POST['action'])){
		$action=0;
	}else $action=abs((int)$_POST['action']);
}else $action=abs((int)$_GET['action']);

if(!isset($_GET['print'])){
	if(!isset($_POST['print'])){
		$print=0;
	}else $print=abs((int)$_POST['print']); 
}else $print=abs((int)$_GET['print']);

if($print!=0){
	if(!$au->user_rights->CheckAccess('w',712)){
	  header("HTTP/1.1 403 Forbidden");
	  header("Status: 403 Forbidden");
	  include("403.php");
	  die();	
	}
}

if(!isset($_GET['from_begin'])){
	if(!isset($_POST['from_begin'])){
		$from_begin=0;
	}else $from_begin=1; 
}else $from_begin=1;


$object_id=array();
switch($action){
	case 0:
	$object_id[]=696;
	break;
	case 1:
	$object_id[]=701;
	$object_id[]=712;
	break;
	case 2:
	$object_id[]=713;
	break;
	default:
	$object_id[]=696;
	break;
}
//echo $object_id;
//die();
$cond=false;
foreach($object_id as $k=>$v){
if($au->user_rights->CheckAccess('w',$v)){
	$cond=$cond||true;
}
}
if(!$cond){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}

$_editable_status_id=array();
$_editable_status_id[]=1;

if($action==0){
	$orgitem=$_orgitem->getitembyid($result['org_id']);	
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
	$editing_user=$_bill->GetItemById($id);
	if($editing_user===false){
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include('404.php');
		die();
	}
	
	
	
	
	$orgitem=$_orgitem->getitembyid($editing_user['org_id']);
	foreach($editing_user as $k=>$v) $editing_user[$k]=stripslashes($v);
	
	
	
	
}


//журнал событий 
if($action==1){
	$log=new ActionLog;
 
		$log->PutEntry($result['id'],'открыл коммерческое предложение',NULL,701, NULL, $editing_user['code'], $id);			
	
}

/*
echo '<pre>';
print_r($_POST);
echo '</pre>';
*/


if(($action==0)&&(isset($_POST['doNew'])||isset($_POST['doNewEdit'])||isset($_POST['send_email']))){
	if(!$au->user_rights->CheckAccess('w',696)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}	
	
	
	
	
	
	$params=array();
	//обычная загрузка прочих параметров
	$params['org_id']=abs((int)$result['org_id']);
	
	$params['pdate']=DateFromdmY($_POST['pdate'])+(time() -DateFromdmY($_POST['pdate']));
	
	
	$params['lead_id']=abs((int)$_POST['lead_id']);
	$params['tz_id']=abs((int)$_POST['tz_id']);
	
	$params['supplier_id']=abs((int)$_POST['supplier_id']);
	$params['contact_id']=abs((int)$_POST['contact_id']);
	
	/*$params['contract_id']=abs((int)$_POST['contract_id']);
	$params['bdetails_id']=abs((int)$_POST['bdetails_id']);
	*/
	
	
	$lc->ses->ClearOldSessions();
	
	$params['code']=SecStr($lc->GenLogin($result['id'])); //SecStr($_POST['code']);
	
	
	$params['is_confirmed_price']=0;
	$params['is_confirmed_shipping']=0;
	
	
	$params['manager_id']=$result['id'];
	$params['price_kind_id']=abs((int)$_POST['price_kind_id']);
	
	$params['lang_rus']=abs((int)$_POST['lang_rus']);
	$params['lang_en']=abs((int)$_POST['lang_en']);
	
	$params['print_form_has_komplekt']=abs((int)$_POST['print_form_has_komplekt']);
	
	/*$params['supplier_bill_no']=SecStr($_POST['supplier_bill_no']);
	
	
	if(strlen($_POST['supplier_bill_pdate'])==10) $params['supplier_bill_pdate']=DateFromdmY($_POST['supplier_bill_pdate']);*/
	
	
	$params['valid_pdate']=DateFromdmY($_POST['valid_pdate']);
	//$params['supply_pdate']=DateFromdmY($_POST['supply_pdate']);
	$params['supply_pdate_id']=abs((int)$_POST['supply_pdate_id']);
	
	
/*	$params['supplier_name']=SecStr($_POST['supplier_name']);
	$params['supplier_fio']=SecStr($_POST['supplier_fio']);*/
	
	
	$params['install_mode']=abs((int)$_POST['install_mode']);
	$params['install_value']=SecStr($_POST['install_value']);
	$params['install_currency_id']=abs((int)$_POST['install_currency_id']);
	$params['install_notes']=SecStr($_POST['install_notes']);
	
	
	$params['supply_id']=abs((int)$_POST['supply_id']);
	$params['supply_city']=SecStr($_POST['supply_city']);
	$params['supply_out_city']=SecStr($_POST['supply_out_city']);
	
	
	$params['paymode_id']=abs((int)$_POST['paymode_id']);
	$params['paymode_pred']=SecStr($_POST['paymode_pred']);
	$params['paymode_pered_otgr']=SecStr($_POST['paymode_pered_otgr']);
	$params['paymode_pnr']=SecStr($_POST['paymode_pnr']);
	
	
	/*if($params['paymode_id']==2){
		$params['paymode_txt']=SecStr($_POST['paymode_txt']);	
		if(isset($_POST['paymode_has_delay'])) $params['paymode_has_delay']=1; else $params['paymode_has_delay']=0;
		
		$params['paymode_delay']=abs((int)$_POST['paymode_delay']);
		$params['paymode_delay_mode']=abs((int)$_POST['paymode_delay_mode']);
	}*/
	
	if(isset($_POST['paymode_id'])) {
			 
			$_kpp=new KpPaymodeItem; $kpp=$_kpp->getitembyid(abs((int)$_POST['paymode_id']));
			if($kpp['is_standart']==1){
				$params['paymode_pred']=$kpp['pred'];
				$params['paymode_pered_otgr']=$kpp['pered_otgr'];
				$params['paymode_pnr']=$kpp['pnr'];
			}else{
				 
				if(isset($_POST['paymode_txt'])) $params['paymode_txt']=SecStr($_POST['paymode_txt']);	
				if(isset($_POST['paymode_has_delay'])) $params['paymode_has_delay']=1; else $params['paymode_has_delay']=0;
				
				if(isset($_POST['paymode_delay'])) $params['paymode_delay']=abs((int)$_POST['paymode_delay']);
				if(isset($_POST['paymode_delay_mode'])) $params['paymode_delay_mode']=abs((int)$_POST['paymode_delay_mode']);
	 
				$params['paymode_pred']='';
				$params['paymode_pered_otgr']='';
				$params['paymode_pnr']='';
			}
		}
		
	
	
	$params['delivery_mode']=abs((int)$_POST['delivery_mode']);
	$params['delivery_value']=SecStr($_POST['delivery_value']);
	$params['delivery_currency_id']=abs((int)$_POST['delivery_currency_id']);
	$params['delivery_notes']=SecStr($_POST['delivery_notes']);
	
	
	$params['user_dir_pr_id']=abs((int)$_POST['user_dir_pr_id']);
	
	$params['user_manager_id']=abs((int)$_POST['user_manager_id']);
	
	
	$params['warranty_id']=abs((int)$_POST['warranty_id']);
	$params['warranty_text']=SecStr($_POST['warranty_text']);
	
	$params['profit_percent']=abs((float)$_POST['profit_percent']);
	$params['profit_value']=abs((float)$_POST['profit_value']);
	$params['profit_currency']=abs((int)$_POST['profit_currency']);
	
	$params['manager_txt']=SecStr($_POST['manager_txt']);
	
	
	$code=$_bill->Add($params);
	//$code=1;
	//запись в журнале
	if($code>0){
		$log->PutEntry($result['id'],'создал коммерческое предложение',NULL,696,NULL,NULL,$code);	
		
		foreach($params as $k=>$v){
			
			if($k=='supplier_id'){
				$_sup=new SupplierItem; $_sup_opf=new OpfItem;
				$sup=$_sup->GetItemById($v);
				$sup_opf=$_sup_opf->GetItemById($sup['opf_id']);
				
				$log->PutEntry($result['id'],'создал коммерческое предложение',NULL,696, NULL, SecStr('установлен контрагент '.$sup_opf['name'].' '.$sup['full_name']),$code);	
				continue;	
			}
			
			if($k=='contact_id'){
				$_cont=new SupplierContactItem;
				$cont=$_cont->GetItemById($v);
				
				$log->PutEntry($result['id'],'создал коммерческое предложение',NULL,696, NULL, SecStr('установлен контакт '.$cont['name'].', '.$cont['position']),$code);	
				continue;	
			}
				
		 
		  
				$log->PutEntry($result['id'],'создал коммерческое предложение',NULL,696, NULL, 'в поле '.$k.' установлено значение '.$v,$code);		
			 
		}
		
	}
	
	
	if(($code>0)&&($au->user_rights->CheckAccess('w',697))){
		//позиции
		$positions=array();
		
		$_pos=new plPosItem;
		$_pdi=new PosDimItem;
		
		
		$_pldi=new pldisitem;
		
		$_curr_itm=new PlCurrItem;
			
		foreach($_POST as $k=>$v){
		  if(eregi("^new_hash_([0-9a-z]+)",$k)){
			  
			  $hash=eregi_replace("^new_hash_","",$k);
			  
			  $pos_id=abs((int)$_POST['new_position_id_'.$hash]);
			 
			  
			  $pms=NULL;
			  
			  
			 
			  $dimension=$_pdi->GetItemById(abs((int)$_POST['new_dimension_id_'.$hash]));
			  $pos=$_pos->GetItemById(abs((int)$_POST['new_pl_position_id_'.$hash]));
			  
			  //определим имя в зависимости от выбранных языков
			 //название и текст для позиции в зависимости от выбора языка в пл
				$name=''; 
				
				if($pos['parent_id']!=0){
					if($params['lang_rus']==1){
						$name.= $pos['name'];
					}
					if(($params['lang_rus']==1)&&($params['lang_en']==1)){
						$name.= ' / ';
					}
					if($params['lang_en']==1){
						$name.= $pos['name_en'];	
					}
				}else{
					$name.= $pos['name'];
				}				  
			  
			  $positions[]=array(
				  'kp_id'=>$code,
				  
				  'position_id'=>$pos_id,
				  'pl_position_id'=>abs((int)$_POST['new_pl_position_id_'.$hash]),
				  'parent_id'=>abs((int)$_POST['new_parent_id_'.$hash]),
				  'currency_id'=>abs((int)$_POST['new_currency_id_'.$hash]),
				  'pl_discount_id'=>abs((int)$_POST['new_pl_discount_id_'.$hash]),
				  'pl_discount_value'=>((float)str_replace(",",".",$_POST['new_pl_discount_value_'.$hash])),
				  'pl_discount_rub_or_percent'=>abs((int)$_POST['new_pl_discount_rub_or_percent_'.$hash]),
				  'price_kind_id'=>abs((int)$_POST['new_price_kind_id_'.$hash]),
				  'delivery_ddpm'=>((float)str_replace(",",".",$_POST['new_delivery_ddpm_'.$hash])),
				  'print_form_has_komplekt'=>abs((int)$_POST['new_print_form_has_komplekt_'.$hash]),
				  
				  'name'=>SecStr($name), //SecStr($pos['name']),
				  'dimension'=>SecStr($dimension['name']),
				  'quantity'=>((float)str_replace(",",".",$_POST['new_quantity_'.$hash])),
				  'price'=>(float)str_replace(",",".",$_POST['new_price_'.$hash]),
				  'extra_charges'=>(float)str_replace(",",".",$_POST['new_extra_charges_'.$hash]),
				  'price_f'=>(float)str_replace(",",".",$_POST['new_price_f_'.$hash]),
				  'price_pm'=>(float)str_replace(",",".",$_POST['new_price_pm_'.$hash]),
				  //'total'=>(float)str_replace(",",".",$_POST['new_price_pm_'.$hash])*(float)str_replace(",",".",$_POST['new_quantity_'.$hash]),
				  'total'=>(float)str_replace(",",".",$_POST['new_total_'.$hash]),
				  'pms'=>$pms
			  );
			  
		  }
		}
			
		
		
		/*echo '<pre>';
		//print_r($_POST);
		print_r($positions);
		echo '</pre>';
		die();
		*/
		
		//внесем позиции
		$_bill->AddPositions($code,$positions);
		//die();
		//запишем в журнал
		foreach($positions as $k=>$v){
			$pos=$_pos->GetItemById($v['pl_position_id']);
			if($pos!==false) {
				$curr_itm=$_curr_itm->GetItemById($v['currency_id']);
				$curr_name=SecStr($curr_itm['signature']);
				
				$descr=SecStr($pos['name']).'<br /> кол-во '.$v['quantity'].'<br /> цена '.$v['price'].' '.$curr_name.' <br />';
				$descr.=' цена со скидкой '.$v['price_f'].' '.$curr_name.' <br />';
				if($v['pl_discount_value']>0){
					$dis=$_pldi->GetItemById($v['pl_discount_id']);
					$descr.=SecStr($dis['name']).' '.$v['pl_discount_value'].'';
					if($v['pl_discount_rub_or_percent']==0) $descr.=' '.$curr_name.' <br />';
					else  $descr.=' % <br />';
				}
				
				
				
				$log->PutEntry($result['id'],'добавил позицию коммерческого предложения', NULL, 701,NULL,$descr,$code);	
				
			
			}
		}	
	}
	
	//автоматически утвердить КП
	
	if($au->user_rights->CheckAccess('w',709)&&($_POST['do_confirm']==1)){
	
	
		$_bill->Edit($code,array('is_confirmed_price'=>1, 'is_auto_confirmed_price'=>1, 'user_confirm_price_id'=>$result['id'], 'confirm_price_pdate'=>time()),true,$result);
					  
		$log->PutEntry($result['id'],'автоматически утвердил коммерческое предложение',NULL,709, NULL, NULL,$code);	
		
		$_kni=new KpNotesItem;
		$notes_params=array();
		$notes_params['is_auto']=1;
		$notes_params['user_id']=$code;
		$notes_params['pdate']=time();
		$notes_params['posted_user_id']=$result['id'];
	  
	  
		$notes_params['note']='Автоматическое примечание: коммерческое предложение было автоматически утверждено при создании пользователем '.SecStr($result['name_s'].' '.$result['login']);
		$_kni->Add($notes_params);
		
		 //очистим кукисы
		$_pl_cook=new PlPosGroup;
		$_pl_cook->MakeMemory(0);
				  
	}
	 
	
	//перенаправления
	if($au->user_rights->CheckAccess('w',843)&&isset($_POST['send_email'])&&($_POST['send_email']==1)&&isset($_POST['email'])&&($_POST['do_confirm']==1)){
		//отправка pdf-формы на email
		header("Location: ed_kp_pdf.php?action=1&id=".$code.'&send_email=1&email='.$_POST['email']);
		die();	
		
	}elseif(isset($_POST['doNew'])){
		header("Location: kps.php#user_".$code);
		die();
	}elseif(isset($_POST['doNewEdit'])){
		//если есть доступ к объекту 11 - правка пользователя - то переход туда		
		if(!$au->user_rights->CheckAccess('w',701)){
			header("HTTP/1.1 403 Forbidden");
			header("Status: 403 Forbidden");
			include("403.php");
			die();	
		}
		header("Location: ed_kp.php?action=1&id=".$code.'&from_begin='.$from_begin);
		die();	
		
	}else{
		header("Location: kps.php");
		die();
	}
	
	
	die();
	
}elseif(($action==1)&&(isset($_POST['doEdit'])||isset($_POST['doEditStay']))){
	//редактирование pozicii
	if(!$au->user_rights->CheckAccess('w',701)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}	
	
	//редактирование возможно, если is_confirmed==0
	
	$condition=true;
	$condition=in_array($_POST['current_status_id'],$_editable_status_id)&&in_array($editing_user['status_id'],$_editable_status_id);
	
	if($condition){
		$params=array();
		//обычная загрузка прочих параметров
		
		$params['supplier_id']=abs((int)$_POST['supplier_id']);
		$params['contact_id']=abs((int)$_POST['contact_id']);
	
		
		if(isset($_POST['lang_rus'])) $params['lang_rus']=abs((int)$_POST['lang_rus']);
		if(isset($_POST['lang_en'])) $params['lang_en']=abs((int)$_POST['lang_en']);
		if(isset($_POST['print_form_has_komplekt'])) $params['print_form_has_komplekt']=abs((int)$_POST['print_form_has_komplekt']);
		
		if(strlen($_POST['valid_pdate'])==10) $params['valid_pdate']=DateFromdmY($_POST['valid_pdate']);
		
		if(isset($_POST['supply_pdate_id'])) $params['supply_pdate_id']=abs((int)$_POST['supply_pdate_id']);
		
		
		if(isset($_POST['price_kind_id'])) $params['price_kind_id']=abs((int)$_POST['price_kind_id']);
		
		if(isset($_POST['install_mode'])) $params['install_mode']=abs((int)$_POST['install_mode']);
	
		
		if(isset($_POST['paymode_id'])) $params['paymode_id']=abs((int)$_POST['paymode_id']);
		if(isset($_POST['paymode_pred'])) $params['paymode_pred']=SecStr($_POST['paymode_pred']);
		if(isset($_POST['paymode_pered_otgr'])) $params['paymode_pered_otgr']=SecStr($_POST['paymode_pered_otgr']);
		if(isset($_POST['paymode_pnr'])) $params['paymode_pnr']=SecStr($_POST['paymode_pnr']);
		if(isset($_POST['paymode_id'])) {
			 
			$_kpp=new KpPaymodeItem; $kpp=$_kpp->getitembyid(abs((int)$_POST['paymode_id']));
			if($kpp['is_standart']==1){
				$params['paymode_pred']=$kpp['pred'];
				$params['paymode_pered_otgr']=$kpp['pered_otgr'];
				$params['paymode_pnr']=$kpp['pnr'];
			}else{
				 
				if(isset($_POST['paymode_txt'])) $params['paymode_txt']=SecStr($_POST['paymode_txt']);	
				if(isset($_POST['paymode_has_delay'])) $params['paymode_has_delay']=1; else $params['paymode_has_delay']=0;
				
				if(isset($_POST['paymode_delay'])) $params['paymode_delay']=abs((int)$_POST['paymode_delay']);
				if(isset($_POST['paymode_delay_mode'])) $params['paymode_delay_mode']=abs((int)$_POST['paymode_delay_mode']);
	 
				$params['paymode_pred']='';
				$params['paymode_pered_otgr']='';
				$params['paymode_pnr']='';
			}
		}
		
		
		
		
		if(isset($_POST['user_dir_pr_id'])) $params['user_dir_pr_id']=abs((int)$_POST['user_dir_pr_id']);
		
		if(isset($_POST['user_manager_id'])) $params['user_manager_id']=abs((int)$_POST['user_manager_id']);
		
		
		if(isset($_POST['warranty_id'])) {
			$params['warranty_id']=abs((int)$_POST['warranty_id']);
			if($params['warranty_id']==1) $params['warranty_text']='';
			
		}
		if(isset($_POST['warranty_text'])) $params['warranty_text']=SecStr($_POST['warranty_text']);
	
		if(isset($_POST['manager_txt'])) $params['manager_txt']=SecStr($_POST['manager_txt']);
	
		
		$_bill->Edit($id, $params,false,$result);
		//die();
		//запись в журнале
		//записи в лог. сравнить старые и новые записи
		foreach($params as $k=>$v){
			
			if(addslashes($editing_user[$k])!=$v){
				
				if($k=='supplier_id'){
					$_sup=new SupplierItem; $_sup_opf=new OpfItem;
					$sup=$_sup->GetItemById($v);
					$sup_opf=$_sup_opf->GetItemById($sup['opf_id']);
					
					$log->PutEntry($result['id'],'редактировал коммерческое предложение',NULL,701, NULL, SecStr('установлен контрагент '.$sup_opf['name'].' '.$sup['full_name']),$id);	
					continue;	
				}
				
				if($k=='contact_id'){
					$_cont=new SupplierContactItem;
					$cont=$_cont->GetItemById($v);
					
					$log->PutEntry($result['id'],'редактировал коммерческое предложение',NULL,701, NULL, SecStr('установлен контакт '.$cont['name'].', '.$cont['position']),$id);	
					continue;	
				}
				
				
				
				$log->PutEntry($result['id'],'редактировал коммерческое предложение',NULL,701, NULL, 'в поле '.$k.' установлено значение '.$v,$id);
				
						
			}
			
			
		}
		
	}
	
	
	
	$condition_positions=$condition;
	//правим позиции. можно их править, если у счета не утв. отгрузка....
	$condition_positions=$condition_positions||(($editing_user['status_id']!=3)&&($editing_user['is_confirmed_price']==0))
	;
	
	
	//правим позиции	
	
	if($condition_positions){	
		
		
		if($au->user_rights->CheckAccess('w',697)){
		  $positions=array();
		  
		  $_pos=new PlPosItem;
		  $_pdi=new PosDimItem;
		  //$_kpi=new KomplPosItem;
		  
		  $_pldi=new pldisitem;
		  
		 
		  
		  foreach($_POST as $k=>$v){
			if(eregi("^new_hash_([0-9a-z]+)",$k)){
				
				$hash=eregi_replace("^new_hash_","",$k);
				
				$pos_id=abs((int)$_POST['new_position_id_'.$hash]);
				
				
				if($_POST['new_has_pm_'.$hash]==0) $pms=NULL;
				else{
					$pms=array(
						'plus_or_minus'=>abs((int)$_POST['new_plus_or_minus_'.$hash]),
						'rub_or_percent'=>abs((int)$_POST['new_rub_or_percent_'.$hash]),
						'value'=>(float)str_replace(",",".",$_POST['new_value_'.$hash]),
						
						'discount_rub_or_percent'=>abs((int)$_POST['new_discount_rub_or_percent_'.$hash]),
						'discount_value'=>(float)str_replace(",",".",$_POST['new_discount_value_'.$hash])
					);	
				}
				$dimension=$_pdi->GetItemById(abs((int)$_POST['new_dimension_id_'.$hash]));
			  	$pos=$_pos->GetItemById(abs((int)$_POST['new_pl_position_id_'.$hash]));
				
				  //определим имя в зависимости от выбранных языков
			 //название и текст для позиции в зависимости от выбора языка в пл
				$name=''; 
				
				if($pos['parent_id']!=0){
					if($params['lang_rus']==1){
						$name.= $pos['name'];
					}
					if(($params['lang_rus']==1)&&($params['lang_en']==1)){
						$name.= ' / ';
					}
					if($params['lang_en']==1){
						$name.= $pos['name_en'];	
					}
				}else{
					$name.= $pos['name'];
				}				  
				
				
				$positions[]=array(
					'kp_id'=>$id,
					'position_id'=>$pos_id,
					'pl_position_id'=>abs((int)$_POST['new_pl_position_id_'.$hash]),
					'parent_id'=>abs((int)$_POST['new_parent_id_'.$hash]),
					'currency_id'=>abs((int)$_POST['new_currency_id_'.$hash]),
					'pl_discount_id'=>abs((int)$_POST['new_pl_discount_id_'.$hash]),
					'pl_discount_value'=>((float)str_replace(",",".",$_POST['new_pl_discount_value_'.$hash])),
					'pl_discount_rub_or_percent'=>abs((int)$_POST['new_pl_discount_rub_or_percent_'.$hash]),
					'price_kind_id'=>abs((int)$_POST['new_price_kind_id_'.$hash]),
					'delivery_ddpm'=>((float)str_replace(",",".",$_POST['new_delivery_ddpm_'.$hash])),
					'print_form_has_komplekt'=>abs((int)$_POST['new_print_form_has_komplekt_'.$hash]),
					
					'name'=>SecStr($name),//SecStr($pos['name']),
					'dimension'=>SecStr($dimension['name']),
					'quantity'=>((float)str_replace(",",".",$_POST['new_quantity_'.$hash])),
					'price'=>(float)str_replace(",",".",$_POST['new_price_'.$hash]),
					'extra_charges'=>(float)str_replace(",",".",$_POST['new_extra_charges_'.$hash]),
					'price_f'=>(float)str_replace(",",".",$_POST['new_price_f_'.$hash]),
					'price_pm'=>(float)str_replace(",",".",$_POST['new_price_pm_'.$hash]),
					'total'=>(float)str_replace(",",".",$_POST['new_total_'.$hash]),
					'pms'=>$pms
				);
				
			}
		  }
		  
		  
		/*  echo '<pre>';
		  //print_r($_POST);
		  print_r($positions);
		  echo '</pre>';
		  die();*/
		  		  //внесем позиции
		  
		  
		  
		 $can_change_cascade=false;
		 $log_entries=$_bill->AddPositions($id,$positions,$can_change_cascade,false,$result);
		  
		 
		  //выводим в журнал сведения о редактировании позиций
		  foreach($log_entries as $k=>$v){
			  
			  $pos=$_pos->GetItemById($v['pl_position_id']);
			if($pos!==false) {
			  
			  $_curr_itm=new PlCurrItem;
			  $curr_itm=$_curr_itm->GetItemById($v['currency_id']);
			$curr_name=SecStr($curr_itm['signature']);
			  
			  $description=SecStr($pos['name'].'<br /> кол-во '.$v['quantity'].'<br /> цена '.$v['price'].' '.$curr_name.' <br />');
				$description.=' цена со скидкой '.$v['price_f'].' '.$curr_name.' <br />';
				if($v['pl_discount_value']>0){
					$dis=$_pldi->GetItemById($v['pl_discount_id']);
					$description.=SecStr($dis['name']).' '.$v['pl_discount_value'].'';
					if($v['pl_discount_rub_or_percent']==0) $description.=' '.$curr_name.'. <br />';
					else  $description.=' % <br />';
				}
			 
			  
			  if($v['action']==0){
				  $log->PutEntry($result['id'],'добавил позицию коммерческого предложения',NULL,701,NULL,$description,$id);	
			  }elseif($v['action']==1){
				  $log->PutEntry($result['id'],'редактировал позицию коммерческого предложения',NULL,701,NULL,$description,$id);
			  }elseif($v['action']==2){
				  $log->PutEntry($result['id'],'удалил позицию коммерческого предложения',NULL,701,NULL,$description,$id);
			  }
			  
			
			  
			  
			  
			}
		  }
		 
		
		}
	}
	
	
	
	//утверждение цен
	
	if($editing_user['is_confirmed_shipping']==0){
	  if($editing_user['is_confirmed_price']==1){
		  //есть права: либо сам утв.+есть права, либо есть искл. права:
		  if(($au->user_rights->CheckAccess('w',711))){
			  
			  
			  $check_confirm=true;
			  
			  if($au->user_rights->CheckAccess('w',711)&&$au->user_rights->CheckAccess('w',763)){
				 $check_confirm=true; 
			  //}elseif($au->user_rights->CheckAccess('w',711)&&!$au->user_rights->CheckAccess('w',763)&&($result['id']==$editing_user['manager_id'])){
			  }elseif($au->user_rights->CheckAccess('w',711)||$au->user_rights->CheckAccess('w',763)){	  
			  	 $check_confirm=true; 
			  }else  $check_confirm=false;
					  
			  
			  
			  if($check_confirm&&(!isset($_POST['is_confirmed_price']))&&in_array($editing_user['status_id'], array(2,9,10,27))&&in_array($_POST['current_status_id'], array(2,9,10,27))){
				  
				  //&&($editing_user['status_id']==5)&&($_POST['current_status_id']==5)
				  $_bill->Edit($id,array('is_auto_confirmed_price'=>0, 'is_confirmed_price'=>0, 'user_confirm_price_id'=>$result['id'], 'confirm_price_pdate'=>time()),true,$result);
				  
				  $log->PutEntry($result['id'],'снял утверждение коммерческого предложения',NULL,711, NULL, NULL,$id);	
				 // $_bill->FreeBindedPayments($id);
			  }
		  }else{
			  //нет прав	
		  }
		  
	  }else{
		  //есть права
		  if($au->user_rights->CheckAccess('w',709)||$au->user_rights->CheckAccess('w',817)){
			  
			  $check_confirm=true;
			  $test_bill=$_bill->getitembyid($id);
			  if($au->user_rights->CheckAccess('w',817)&&(($test_bill['warranty_id']==2)||($test_bill['paymode_id']==2))){
				  $check_confirm=true;
			  }elseif($au->user_rights->CheckAccess('w',709)&&!(($test_bill['warranty_id']==2)||($test_bill['paymode_id']==2))){
				  $check_confirm=true;
			  }else $check_confirm=false;
			  
			  if($check_confirm&&isset($_POST['is_confirmed_price'])&&($_POST['is_confirmed_price']==1)&&in_array($editing_user['status_id'], array(1))&&in_array($_POST['current_status_id'], array(1))){
				  
				  $_bill->Edit($id,array('is_confirmed_price'=>1, 'is_auto_confirmed_price'=>0, 'user_confirm_price_id'=>$result['id'], 'confirm_price_pdate'=>time()),true,$result);
				  
				  $log->PutEntry($result['id'],'утвердил коммерческое предложение',NULL,709, NULL, NULL,$id);	
				  
				  //очистим кукисы
				  $_pl_cook=new PlPosGroup;
				  $_pl_cook->MakeMemory(0);
				  
			  }
		  }else{
			  //do nothing
		  }
	  }
	}
	
	
	
	

	
	
	//die();
	
	//перенаправления
		//перенаправления
	if(isset($_POST['send_email'])&&($_POST['send_email']==1)&&isset($_POST['email'])){
		//отправка pdf-формы на email
		header("Location: ed_kp_pdf.php?action=1&id=".$id.'&send_email=1&email='.$_POST['email']);
		die();	
		
	}elseif(isset($_POST['doEdit'])){
		header("Location: kps.php#user_".$id);
		die();
	}elseif(isset($_POST['doEditStay'])){
		//если есть доступ к объекту 11 - правка пользователя - то переход туда		
		if(!$au->user_rights->CheckAccess('w',701)){
			header("HTTP/1.1 403 Forbidden");
			header("Status: 403 Forbidden");
			include("403.php");
			die();	
		}
		header("Location: ed_kp.php?action=1&id=".$id.'&from_begin='.$from_begin);
		die();	
		
	}else{
		header("Location: kps.php");
		die();
	}
	
	die();
}






//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');


$smarty->assign('do_restrict', !in_array($result['id'], array(1,2,3)));

if($print==0) $smarty->display('top.html');
else $smarty->display('top_print.html');
unset($smarty);


$_menu_id=51;
	if($print==0) include('inc/menu.php');
	
	
	//демонстрация стартовой страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	$opf=$_opf->GetItemById($orgitem['opf_id']);
	
	
	
	if($action==0){
		//создание позиции
		
		$sm1=new SmartyAdm;
		$sm1->assign('now',date("d.m.Y"));
		
		$sm1->assign('org',stripslashes($opf['name'].' '.$orgitem['full_name']));
		$sm1->assign('org_id',$result['org_id']);
		
		 
		
		
		
		$lang_rus=abs((int)$_GET['lang_rus']);

		$lang_en=abs((int)$_GET['lang_en']);
		if(($lang_rus==0)&&($lang_en==0)) $lang_rus=1;
		$sm1->assign('lang_rus',$lang_rus);
		$sm1->assign('lang_en',$lang_en);
		
		$print_form_has_komplekt=abs((int)$_GET['print_form_has_komplekt']);
		$sm1->assign('print_form_has_komplekt',$print_form_has_komplekt);
		
		//передать позиции...
		$positions=array();
		
		$_pdm=new PlDisMaxValGroup;
		$_pli=new PlPosItem;
		$_bpf=new BillPosPMFormer;
		$_kpf=new KpPosPMFormer;
		
		
		$delivery_mode=0; $delivery_value=''; $delivery_notes='';
		$install_mode=0; $install_value=''; $install_notes='';
		
		$producer_id=0;    
		$primary_position_id=0; $pl_discount_id=0; $pl_discount_value=0;
		
		
		$digits=0; //разряды для округления
		
		$group_id=0; //айди родительской группы (для определения шаблона)
		
		foreach($_GET['pl_position_id'] as $k=>$v){
			// url=url+'&pl_position_id[]='+opt_id+';'+parent_id+';'+quantity+';'+currency_id+';'+price+';'+discount_id+';'+discount_value+';'+discount_rub_or_percent+';'+price_kind_id+print_form_has_komplekt
			$valarr=explode(';', $v);
			
			$pos_id=$valarr[0];
			$qua=$valarr[2];
			$price_kind_id=$valarr[8];
			$print_form_has_komplekt=$valarr[9];
			$extra_charges=$valarr[10];
			
			//var_dump($print_form_has_komplekt);
			
			$sql='select 
				p.id as pl_position_id,  p.discount_id as pl_discount_id,	/*p.discount_value as pl_discount_value, p.discount_rub_or_percent as pl_discount_rub_or_percent,*/
				
				pos.name as position_name, pos.name_en as position_name_en, pos.id as position_id, pos.parent_id, pos.code,
				
				dim.name as dim_name, dim.id as dimension_id ,
				
				pr.price as price, pr.currency_id as currency_id,
				curr.signature,
				pos.txt_for_kp, pos.txt_for_kp_en,
				pos.photo_for_kp, pos.is_install, pos.is_delivery, pos.producer_id, pos.is_mandatory,
				pr_kind.is_calc_price, p.delivery_ddpm,
				pos.group_id, parent_gr.id as parent_group_id, parent_gr.parent_group_id as parent_group_parent_group_id 
				
				
			from pl_position as p 
				inner join catalog_position as pos on p.position_id=pos.id 
				left join catalog_dimension as dim on pos.dimension_id=dim.id 
				left join pl_position_price as pr on pr.pl_position_id=p.id and pr.currency_id="'.$valarr[3].'"
				left join pl_currency as curr on curr.id=pr.currency_id	
				left join pl_price_kind as pr_kind on pr_kind.id="'.$valarr[8].'"
				
				left join catalog_group as current_gr on pos.group_id=current_gr.id
				left join catalog_group as parent_gr on parent_gr.id=current_gr.parent_group_id 	
				 
				
				where p.id="'.$pos_id.'" order by position_name asc, p.id asc';
		  
		 // echo $sql.'<br>';		
				
		  $set=new mysqlset($sql);
		  $rs=$set->getResult();
		  $rc=$set->getResultNumRows();
		  $h=mysqli_fetch_array($rs);
			
			//также получить набор макс. скидок для позиции
		  $max_vals=array();
		  $max_vals=$_pdm->GetItemsByKindPosArr($h['pl_position_id'], $price_kind_id,0, $result['id']); //GetItemsByIdArr($h['pl_position_id']);
		  
		  
		 if($qua>0){
			 //определим главную позицию для отрисовки скидок в карте КП
			 if($h['parent_id']==0){
				 $primary_position_id=$h['position_id'];
				 $pl_discount_id=$valarr[5];
				 $pl_discount_value=$valarr[6];
				 
			 }
			 
			 
			//нужно правильно вычислять цены...
			
			
			$price=$_pli->DispatchCalcPrice( $h['pl_position_id'],$h['currency_id'],  NULL,  $price_kind_id,NULL,NULL,NULL,$extra_charges);
			
			//$price_f=$_pli->CalcPriceF($h['pl_position_id'],$h['currency_id'], $h['price'],NULL,$valarr[5], $valarr[6], $valarr[7],$price_kind_id);
			
			$price_f=$_pli->DispatchCalcPriceF($h['pl_position_id'],$h['currency_id'],  $h['price'], NULL, $valarr[5], $valarr[6], $valarr[7],$price_kind_id,NULL, $extra_charges);
			
			
			$digits=$_round_define->DefineDigits($h['group_id']);				  
			
			//echo $digits;
			//echo $price_f.' ';
			
			$cost=round($price_f*$qua,$digits);
			$total=$cost;
			
			if($h['is_install']==1){
				$install_mode=1;
				$install_notes='Позиция № '; 
				if(strlen($h['code'])>0) $install_notes.=$h['code']; else $install_notes.=$h['pl_position_id']; 
				$install_value=$total;
			}
			
			if($h['is_delivery']==1){
				$delivery_mode=1;
				$delivery_notes='Позиция № '; 
				if(strlen($h['code'])>0) $delivery_notes.=$h['code']; else $delivery_notes.=$h['pl_position_id']; 
				$delivery_value=$total;
			}
			
			//$producer_id=$h['is_install'];
			$producer_id=$h['producer_id'];
			
			//название и текст для позиции в зависимости от выбора языка в пл
			$name=''; $txt_for_kp='';
			
			if($h['parent_id']!=0){
				if($lang_rus==1){
					$name.= $h['position_name'];			
					$txt_for_kp.=$h['txt_for_kp'];
				}
				if(($lang_rus==1)&&($lang_en==1)){
					$name.= ' / ';			
					$txt_for_kp.=' / ';
				}
				if($lang_en==1){
					$name.= $h['position_name_en'];			
					$txt_for_kp.=$h['txt_for_kp_en'];
				}
			}else{
				$name.= $h['position_name'];			
				$txt_for_kp.=$h['txt_for_kp'];
			}
			
			//вычислить группы
			
			//, parent_gr.parent_group_id as parent_group_parent_group_id, 
			//если parent_group_parent_group_id>0 - значит найти самую родительскую группу
			
			if($h['parent_group_parent_group_id']==0){
					$h['three_group_id']=0;
					$h['two_group_id']=$h['group_id'];
					$h['parent_group_id']=$h['parent_group_id'];
					
				}else{
					  $h['three_group_id']=$h['two_group_id'];
					  $h['two_group_id']=$h['group_id'];
					  $h['parent_group_id']=$h['parent_group_parent_group_id']; 
				}
			
			if($h['parent_id']==0){
				
				
				//echo 'zzzzzzzzzzzzzzz';
				//echo $h['parent_id']; var_dump( $h['group_id']); var_dump( $h['two_group_id']); var_dump( $h['parent_group_parent_group_id']); 
				//echo '<br>';
				$group_id=$h['parent_group_id'];
			}
			
			
			
			
			
			//echo $valarr[5];
			$positions[]=array(
					  'pl_position_id'=>$pos_id,
					  'code'=>$h['code'],
					  'parent_id'=>$h['parent_id'],
					  'currency_id'=>$h['currency_id'],
					  'position_id'=>$h['position_id'],
					  'hash'=>md5($h['pl_position_id'].'_'.$h['position_id'].'_'.$h['pl_discount_id'].'_'.$h['pl_discount_value'].'_'.$h['pl_discount_rub_or_percent']),
					  'position_name'=>$name, //$h['position_name'],
					  'dim_name'=>$h['dim_name'],
					  'dimension_id'=>$h['dimension_id'],
					  'signature'=>$h['signature'],
					  'quantity'=>$qua,
					  'price'=>$price,
					  'price_f'=>$price_f,
					  'price_pm'=>$price_f,
					  'has_pm'=>false,
					  'cost'=>$cost,
					  'total'=>$total,
					  'plus_or_minus'=>0,
					  'rub_or_percent'=>0,
					  'value'=>0,
					  'in_rasp'=>0,
					  'txt_for_kp'=>$txt_for_kp, //$h['txt_for_kp'],
					  'photo_for_kp'=>$h['photo_for_kp'],
					  
					  'is_delivery'=>$h['is_delivery'],
					  'is_install'=>$h['is_install'],
					  'is_mandatory'=>$h['is_mandatory'],
					  'is_calc_price'=>$h['is_calc_price'],
					  
					  'discount_rub_or_percent'=> $valarr[7],
					  'discount_value'=>$valarr[6],
					  'nds_proc'=>NDS,
					  'nds_summ'=>sprintf("%.2f",($price_f-$price_f/((100+NDS)/100))),
					  'pl_discount_id'=>	$valarr[5],
					  'pl_discount_value'=>	$valarr[6],
					  'pl_discount_rub_or_percent'=> $valarr[7],
					  'price_kind_id'=> $valarr[8],
					  'delivery_ddpm'=> $h['delivery_ddpm'],
					  'discs1'=>$max_vals,
					  
					/*  'two_group_id'=>$h['group_id'], //группа товаров п/л - информационное поле
					  'parent_group_id'=>$h['parent_group_id'], //раздел п/л
					  */
					  
					    'three_group_id' =>$h['three_group_id'],
				   'two_group_id' =>$h['two_group_id'],
				  'parent_group_id' =>$h['parent_group_id'], 
					  
					  
					  'producer_id'=>$h['producer_id'],
					  'print_form_has_komplekt'=>$print_form_has_komplekt,
					  'extra_charges'=>$extra_charges
											  
				  );
		  }
		  
		  
		 
		  
		  
		  
		//	print_r($valarr);
		}
		
		
		
		/*
		echo '<pre>';
		print_r($positions);
		echo '</pre>';
		*/
		
		 //скорректировать скидки и стоимости  в процессе подсчета стоимости КП	 
		$sm1->assign('total_cost',$_kpf->CalcCost($positions, true));
		$sm1->assign('total_nds',$_kpf->CalcNDS($positions));
		
		//подставить валюту в общую сумму
		$currency=$_kpf->GetCurrency($positions);	
		//print_r($currency);	
		$sm1->assign('signature',$currency['signature']);
		
		
		//получить правила по $primary_position_id
		$_rules=new PlRules;
		$rules=$_rules->GetRulesCli($primary_position_id);
		$sm1->assign('rules',$rules);
		
		
		//найти и подставить рентабельность в карту
		$_kpf->CalcProfit($positions, $profit_percent, $profit_value, $profit_currency);
		$sm1->assign('profit_percent',$profit_percent);
		$sm1->assign('profit_value',$profit_value);
		$sm1->assign('profit_currency',$profit_currency);
		
		
		if(count($positions)>0) {
			 $sm1->assign('has_positions',true);
			
		}
		$sm1->assign('can_modify', true);
		
		//получим виды скидок
		$_pld=new PlDisGroup;
		$sm1->assign('discs1',$_pld->GetItemsArr());
		//print_r($_pld->GetItemsArr());
		
		
		$sm1->assign('positions',$positions);  
		
		
		//скидка в самой карте (не в таблице)
		//echo $primary_position_id; 
		//echo $pl_discount_value;		
		$dis_in_card=$_pdm->GetItemsByKindPosArr($primary_position_id, $price_kind_id,0, $result['id']); 
		//print_r($dis_in_card);
		$sm1->assign('primary_position_id', $primary_position_id);
		$sm1->assign('pl_discount_id', $pl_discount_id);
		$sm1->assign('pl_discount_value', $pl_discount_value);
		
		$sm1->assign('dis_in_card', $dis_in_card);
		
		$sm1->assign('can_override_manager_discount', $au->user_rights->CheckAccess('w',751)); //скидка менеджера без ограничений
		$sm1->assign('can_override_ruk_discount', $au->user_rights->CheckAccess('w',752));
		
		$sm1->assign('can_ruk_discount',$au->user_rights->CheckAccess('w',753));
		
		// у пленки RCN подставить срок действия 30 дней, иначе - подставить 3 мес.
		$multip=3;
		if($producer_id==10) $multip=1;
		$sm1->assign('valid_pdate',date("d.m.Y", time()+$multip*30*24*60*60));
		
		
		
		//вид КП
		$sm1->assign('price_kind_id', $_GET['price_kind_id']);
		$_pr_kind=new PriceKindItem;
		$pr_kind=$_pr_kind->GetItemById(abs((int)$_GET['price_kind_id']));
		$sm1->assign('price_kind_name', $pr_kind['name']);
		
		
		//текущие значения установки, доставки
		$sm1->assign('delivery_mode', $delivery_mode);
		$sm1->assign('delivery_value', $delivery_value);
		$sm1->assign('delivery_notes', $delivery_notes);
		
		$sm1->assign('install_mode', $install_mode);
		$sm1->assign('install_value', $install_value);
		$sm1->assign('install_notes', $install_notes);
		
		
		//разряды для округления
		$sm1->assign('digits', $digits);
		//echo $digits;
			
			
			
		//задан ЛИД
		if(isset($_GET['lead_id'])&&((int)$_GET['lead_id']>0)){
			$lead_id=abs((int)$_GET['lead_id']);
			$tz_id=abs((int)$_GET['tz_id']);
			
			$sm1->assign('lead_id', $lead_id);	
			$sm1->assign('tz_id', $tz_id);	
			
			 $_lead=new Lead_Item;
			 $lead=$_lead->GetItemById($lead_id);
			 $sm1->assign('lead', $lead);
			 
			  $_tz=new TZ_Item;
			 $tz=$_tz->GetItemById($tz_id);
			 $sm1->assign('tz', $tz);
			 
			 $sm1->assign('user_manager_id', $lead['manager_id']);
			 
			 //контрагент, контакт
			  
			$_lead_suppliers=new Lead_SupplierGroup;
			$lead_suppliers=$_lead_suppliers->GetItemsByIdArr($lead_id);
			
			if(isset($lead_suppliers[0])){
				$_supplier=new SupplierItem;
				$supplier=$_supplier->GetItemById(	$lead_suppliers[0]['supplier_id']);
				//$sm1->assign('supplier', $supplier);
				
				//print_r($lead_suppliers);
				
				$sm1->assign('supplier_id', $lead_suppliers[0]['supplier_id']);
				
				$sm1->assign('supplier_string', $lead_suppliers[0]['opf_name'].' '.$lead_suppliers[0]['full_name']);
				
				if(isset($lead_suppliers[0]['contacts'][0])){ 
					$sm1->assign('contact_id', $lead_suppliers[0]['contacts'][0]['contact_id']);
					
					$sm1->assign('contact_string', $lead_suppliers[0]['contacts'][0]['name']);
				
				}
			}
		 
		}
		
		
		/*$lc->ses->ClearOldSessions();
		
		$sm1->assign('code', $lc->GenLogin($result['id']));*/
		$sm1->assign('code', '');
		
		$sm1->assign('cannot_view_pm',!$au->user_rights->CheckAccess('w',700));
		$sm1->assign('can_create',$au->user_rights->CheckAccess('w',696)); 
		$sm1->assign('can_edit',$au->user_rights->CheckAccess('w',701)); 
		
		
		$sm1->assign('can_edit_quantities',$au->user_rights->CheckAccess('w',715)); 
		
		$sm1->assign('can_add_positions',$au->user_rights->CheckAccess('w',697)); 
		$sm1->assign('can_delete_positions',$au->user_rights->CheckAccess('w',699));
		
		$sm1->assign('can_delete_mandatory_positions',$au->user_rights->CheckAccess('w',768));
		
		$sm1->assign('can_unconfirm',$au->user_rights->CheckAccess('w',711)); 
		$sm1->assign('can_confirm',$au->user_rights->CheckAccess('w',709)); 
		
		$sm1->assign('can_ruk_discount',$au->user_rights->CheckAccess('w',753)); 
		
		$sm1->assign('can_unselect_pnr', $au->user_rights->CheckAccess('w',755));
		$sm1->assign('can_send_email',$au->user_rights->CheckAccess('w',843));
		
		
		$sm1->assign('can_exclude_valid_pdate',$au->user_rights->CheckAccess('w',818));
		$sm1->assign('can_lower_supply_pdate',$au->user_rights->CheckAccess('w',819));
		
		$sm1->assign('can_view_prices',$au->user_rights->CheckAccess('w',842));
		
		$sm1->assign('can_change_supply_pdate',$au->user_rights->CheckAccess('w',986));
		
		
		$sm1->assign('can_confirm_unstandart',$au->user_rights->CheckAccess('w',817)); //можно утв-ть с нест. условиями
		//получить список сотрудников, кто может утв-ть с нестд. услвоями:
		$_usg=new UsersSGroup;
		$unst=$_usg->GetUsersByRightArr('w', 817);
		foreach($unst as $k=>$v){
			if(in_array($v['id'], array(2,3))) unset($unst[$k]);
		}
		
		$sm1->assign('confirm_unstandart_users',$unst); 
		
		
		//дата начала периода
		$sm1->assign('pch_date', $pch_date);
		//массив запрещенных периодов
		$_pergroup=new PerGroup;
		$sm1->assign('cdates', $_pergroup->GetItemsByIdArr($result['org_id'],0,1));
		
		
		$sm1->assign('BILLUP',BILLUP);
		$sm1->assign('NDS',NDS);
		
		
		
		//валюты
		$_curr=new PlCurrGroup;
		$currs=$_curr->GetItemsArr($currency['currency_id']);
		$_ids=array(); $_vals=array();
		foreach($currs as $k=>$v){
			$_ids[]=$v['id']; $_vals[]=$v['signature'];	
		}
		$sm1->assign('delivery_currency_id_ids',$_ids);
		$sm1->assign('delivery_currency_id_vals',$_vals);
		
		
		
		// Базис поставки:
		
		//как получить producer_id? по  оборудованию
		
		
		$_ks=new KpSupplyGroup; $_ksi=new KpSupplyItem;
		$ks=$_ks->GetItemsByFieldsArr(array('producer_id'=>$producer_id/*, 'price_kind_id'=>abs((int)$_GET['price_kind_id']*/));
		$ksi=$_ksi->GetItemByFields(array('producer_id'=>$producer_id, 'price_kind_id'=>abs((int)$_GET['price_kind_id'])));
		$_ids=array(); $_vals=array();
		$_ids[]=0; $_vals[]='';
		
		foreach($ks as $k=>$v){
			$_ids[]=$v['id']; $_vals[]=$v['name'];	
		}
		
		$sm1->assign('supply_id_ids',$_ids);
		$sm1->assign('supply_id_vals',$_vals);
		$sm1->assign('supply_id', $ksi['id']); 
		
		//Условия оплаты:
		$_ks=new KpPaymodeGroup;
		$ks=$_ks->GetItemsByIdArr($group_id);
		$_ids=array(); $_vals=array();
		foreach($ks as $k=>$v){
			$_ids[]=$v['id']; $_vals[]=$v['name'];	
		}
		$sm1->assign('paymode_id_ids',$_ids);
		$sm1->assign('paymode_id_vals',$_vals);
		//if($group_id==2) $sm1->assign('paymode_id',36);
		
		
		
		//подгрузить текущие условия оплаты
		//найти тек. стандартные условия оплаты	
		$_ki=new KpPaymodeItem;
		$ki=$_ki->GetItemByFields(array('group_id'=>$group_id, 'is_standart'=>1));
		//$ki=$_ki->GetItemById(1);
		$sm1->assign('paymode_pred',$ki['pred']);
		$sm1->assign('paymode_pered_otgr',$ki['pered_otgr']);
		$sm1->assign('paymode_pnr',$ki['pnr']);
		
		
		//сроки поставки
		$_ksm=new KpSupplyPdateGroup;
		$ksm=$_ksm->GetItemsByIdArr($group_id);
		$_ids=array(); $_vals=array();
		foreach($ksm as $k=>$v){
			$_ids[]=$v['id']; $_vals[]=$v['name'];	
		}
		$sm1->assign('supply_pdate_id_ids',$_ids);
		$sm1->assign('supply_pdate_vals',$_vals);
		
		//supply_pdate_id ???
		//echo $primary_position_id; die();
		$_t_pos=new PosItem;
		$t_pos=$_t_pos->GetItemById($primary_position_id);
		$sm1->assign('supply_pdate_id', $t_pos['supply_pdate_id']);
		
		
		//директора
		$_ug=new UsersSGroup;
		$ug=$_ug->GetUsersByPositionKeyArr('can_sign_as_dir_pr', 1);
		$_ids=array(); $_vals=array();
		foreach($ug as $k=>$v){
			$_ids[]=$v['id']; $_vals[]=$v['name_s'].' '.$v['position_name'];	
		}
		$sm1->assign('user_dir_pr_id_ids',$_ids);
		$sm1->assign('user_dir_pr_id_vals',$_vals);
		
		
		//менеджеры
		//ограничения по сотруднику
			$limited_user=NULL;
		if($au->FltUser($result)){
			//echo 'z';
			$_u_to_u=new UserToUser();
			$u_to_u=$_u_to_u->GetExtendedViewedUserIdsArr($result['id']);
			$limited_user=$u_to_u['sector_ids'];
		}
		
		$_ug=new UsersSGroup;
		//$ug=$_ug->GetUsersByPositionKeyArr('can_sign_as_manager', 1, array(1,3,4));
		$dec=new DBDecorator();
		//.dep.name
		$dec ->AddEntry(new SqlEntry('pos.name','Менеджер', SqlEntry::LIKE));
		$dec ->AddEntry(new SqlEntry('dep.name','отдел продаж', SqlEntry::LIKE));
		$dec ->AddEntry(new SqlEntry('u.is_active',1, SqlEntry::E));
		$ug=new UsersSGroup;
		$users=$_ug->GetItemsByDecArr($dec);
		
		
		
		$_ids=array(); $_vals=array();
		$_ids[]=0; $_vals[]='-нет-';
		foreach($users as $k=>$v){
			if(($limited_user!==NULL)&&!in_array($v['id'], $limited_user)) continue;
			 
			//дилеров и внешних партнеров видят только они (сами себя) + администраторы
			//if(in_array($v['department_id'], array(16,17))&&!(in_array($result['department_id'], array(16,17)) || in_array($result['id'], array(1,2,3))  )) continue;
			
			
			$_ids[]=$v['id']; $_vals[]=$v['name_s'].' '.$v['position_name'];	
		}
		if(!in_array($result['id'], $_ids)){
			$_ids[]=$result['id']; $_vals[]=$result['name_s'].' '.$result['position_name'];		
		}
		
		$sm1->assign('user_manager_id_ids',$_ids);
		$sm1->assign('user_manager_id_vals',$_vals);
		
		
		//гарантия
		$_ks=new KpWarrantyGroup;
		$ks=$_ks->GetItemsArr();
		$_ids=array(); $_vals=array();
		foreach($ks as $k=>$v){
			$_ids[]=$v['id']; $_vals[]=$v['name'];	
		}
		$sm1->assign('warranty_id_ids',$_ids);
		$sm1->assign('warranty_id_vals',$_vals);
		
		
		//кп скопировано - вывести сообщение при утв-ии
		if(isset($_GET['is_copied'])&&($_GET['is_copied']==1)) $sm1->assign('is_copied', 1); else  $sm1->assign('is_copied', 0);
	
	
	
		//получим список тех, кто может превышать скидку рук-ля (кроме 2,3)
		$_usg1=new UsersSGroup;
		$usg1=$_usg1->GetUsersByRightArr('w', 752);
		//print_r($users);
		foreach($usg1 as $k=>$v){
			if(in_array($v['id'], array(2,3))) unset($usg1[$k]);
		}
		$sm1->assign('users_can_override_ruk_discount', $usg1);
		
	 
	
		//найдем шаблон из группы
		$_pgi=new PosGroupItem;
		$pgi=$_pgi->GetItemById($group_id);
		
		//var_dump($group_id);
		
		//$user_form=$sm1->fetch('kp/kp_create.html');
		$user_form=$sm1->fetch('kp/'.$pgi['kp_create']);
		
		
		
	
		
		if($au->user_rights->CheckAccess('w',3)){
			$sm->assign('has_syslog',true);
			
			$sm->assign('syslog','В данном режиме просмотр журнала событий коммерческого предложения недоступен.<br />
 Пожалуйста, нажмите кнопку "Создать коммерческое предложение и перейти к утверждению" на вкладке "Коммерческое предложение" для получения возможности просмотра журнала событий.');		
		}
		
		
		
	}elseif($action==1){
		//редактирование позиции
		
		
		
		
		if($print==0) $print_add='';
		else $print_add='_print';
		
		
		$sm1=new SmartyAdm;
		
		
		//даты
		$editing_user['pdate']=date("d.m.Y H:i:s",$editing_user['pdate']);
		
		//кем создано
		require_once('classes/user_s_item.php');
		$_cu=new UserSItem();
		$cu=$_cu->GetItemById($editing_user['manager_id']);
		if($cu!==false){
			$ccu=$cu['name_s'];
		}else $ccu='-';
		$sm1->assign('created_by',$ccu);
		
		
			
		//задан ЛИД
		if($editing_user['lead_id']>0){
			$lead_id=$editing_user['lead_id'];
			
			$sm1->assign('lead_id', $lead_id);	
			
			 $_lead=new Lead_Item;
			 $lead=$_lead->GetItemById($lead_id);
			 $sm1->assign('lead', $lead);
			 
			 
			 $tz_id=$editing_user['tz_id'];
			
			$sm1->assign('tz_id', $tz_id);	
			
			 $_tz=new TZ_Item;
			 $tz=$_tz->GetItemById($tz_id);
			 $sm1->assign('tz', $tz);
			 
		}
		
		
		
		
		if($editing_user['valid_pdate']==0) $editing_user['valid_pdate']='-';
		else $editing_user['valid_pdate']=date("d.m.Y", $editing_user['valid_pdate']);
		
		if($editing_user['supply_pdate']==0) $editing_user['supply_pdate']='-';
		else $editing_user['supply_pdate']=date("d.m.Y", $editing_user['supply_pdate']);
		
		
		
		$primary_position_id=0; $pl_discount_id=0; $pl_discount_value=0;
		$_pdm=new PlDisMaxValGroup;
		
		
		$digits=0; //разряды для округления
		
		$group_id=0; //группа для вычисления шаблона
		
		if(!isset($_GET['pl_position_id'])){
			//позиции из карты КП
			
			
			//вид КП
			$sm1->assign('price_kind_id', $editing_user['price_kind_id']);
			$_pr_kind=new PriceKindItem;
			$pr_kind=$_pr_kind->GetItemById($editing_user['price_kind_id']);
			$sm1->assign('price_kind_name', $pr_kind['name']);
			
			
			
			//позиции!
			$sm1->assign('has_positions',true);
			$_bpg=new KpPosGroup;
			$bpg=$_bpg->GetItemsByIdArr($editing_user['id']);
			
			$producer_id=0;
			foreach($bpg as $k=>$v){ $producer_id=$v['producer_id'];
				$digits=$_round_define->DefineDigits($v['two_group_id']);
				if($v['parent_id']==0) $group_id=$v['parent_group_id'];
				
				//var_dump( $v['parent_group_id']);
			}
			//echo $group_id;
			//print_r($bpg);
			
			//получим виды скидок
			$_pld=new PlDisGroup;
			$sm1->assign('discs1',$_pld->GetItemsArr());
			
			//стоимость и итого
			$_bpf=new KpPosPMFormer; //BillPosPMFormer;
			$total_cost=$_bpf->CalcCost($bpg);
			$total_nds=$_bpf->CalcNDS($bpg);
			$sm1->assign('positions',$bpg);
			
			$sm1->assign('total_cost',$total_cost);
			
			//получить переменные для скидки...
			//$primary_position_id=0; $pl_discount_id=0; $pl_discount_value=0;
			foreach($bpg as $k=>$v){
				if($v['parent_id']==0){
					$primary_position_id=$v['position_id'];
					$pl_discount_id=$v['pl_discount_id'];
					$pl_discount_value=$v['pl_discount_value'];
				}
			}
			
			// Базис поставки:
			
			$_ks=new KpSupplyGroup;
			$ks=$_ks->GetItemsByFieldsArr(array('producer_id'=>$producer_id/*, 'price_kind_id'=>abs((int)$_GET['price_kind_id']*/));
			
			$_ids=array(); $_vals=array();
			$_ids[]=0; $_vals[]='';
			foreach($ks as $k=>$v){
				$_ids[]=$v['id']; $_vals[]=$v['name'];	
			}
			//echo 'zzzzzzzzzzzzzzzzz';
			$sm1->assign('supply_id_ids',$_ids);
			$sm1->assign('supply_id_vals',$_vals);
			
			
			//подставить валюту в общую сумму
			$currency=$_bpf->GetCurrency($bpg);	
			//print_r($currency);	
			$sm1->assign('signature',$currency['signature']);
			$sm1->assign('total_nds',$total_nds);
		
		}else{
			//позиции из ПЛ (после редактирования)
			
			$positions=array();
		
			
			$_pli=new PlPosItem;
			$_bpf=new BillPosPMFormer;
			$_kpf=new KpPosPMFormer;
			
			
			$delivery_mode=0; $delivery_value=''; $delivery_notes='';
			$install_mode=0; $install_value=''; $install_notes='';
			
			$producer_id=0; 
	
			$lang_rus=abs((int)$_GET['lang_rus']);
			$lang_en=abs((int)$_GET['lang_en']);
			
			if(($lang_rus==0)&&($lang_en==0)) $lang_rus=1;
			$editing_user['lang_rus']=$lang_rus;
			$editing_user['lang_en']=$lang_en;
			
			$print_form_has_komplekt=abs((int)$_GET['print_form_has_komplekt']);
			$editing_user['print_form_has_komplekt']=$print_form_has_komplekt;
			
			
			foreach($_GET['pl_position_id'] as $k=>$v){
				// url=url+'&pl_position_id[]='+opt_id+';'+parent_id+';'+quantity+';'+currency_id+';'+price+';'+discount_id+';'+discount_value+';'+discount_rub_or_percent+';'+price_kind_id
				$valarr=explode(';', $v);
				
				$pos_id=$valarr[0];
				$qua=$valarr[2];
				$price_kind_id=$valarr[8];
				$print_form_has_komplekt=$valarr[9];
				$extra_charges=$valarr[10];
				
				$sql='select 
					p.id as pl_position_id,  p.discount_id as pl_discount_id,	/*p.discount_value as pl_discount_value, p.discount_rub_or_percent as pl_discount_rub_or_percent,*/
					
					pos.name as position_name, pos.name_en as position_name_en,  pos.id as position_id, pos.parent_id, pos.code,
					
					dim.name as dim_name, dim.id as dimension_id ,
					
					pr.price as price, pr.currency_id as currency_id,
					curr.signature,
					pos.txt_for_kp, pos.txt_for_kp_en,
					pos.photo_for_kp, pos.is_install, pos.is_delivery, pos.producer_id, pos.is_mandatory,
					pr_kind.is_calc_price, p.delivery_ddpm,
					pos.group_id, parent_gr.id as parent_group_id, parent_gr.parent_group_id as parent_group_parent_group_id
					
					
				from pl_position as p 
					inner join catalog_position as pos on p.position_id=pos.id 
					left join catalog_dimension as dim on pos.dimension_id=dim.id 
					left join pl_position_price as pr on pr.pl_position_id=p.id and pr.currency_id="'.$valarr[3].'"
					left join pl_currency as curr on curr.id=pr.currency_id	
					left join pl_price_kind as pr_kind on pr_kind.id="'.$valarr[8].'"
					
					left join catalog_group as current_gr on pos.group_id=current_gr.id
					left join catalog_group as parent_gr on parent_gr.id=current_gr.parent_group_id 	 
					
					where p.id="'.$pos_id.'" order by position_name asc, p.id asc';
			  
			 // echo $sql.'<br>';		
					
			  $set=new mysqlset($sql);
			  $rs=$set->getResult();
			  $rc=$set->getResultNumRows();
			  $h=mysqli_fetch_array($rs);
				
				//также получить набор макс. скидок для позиции
			  $max_vals=array();
			  $max_vals=$_pdm->GetItemsByKindPosArr($h['pl_position_id'], $price_kind_id, 0, $result['id']); //GetItemsByIdArr($h['pl_position_id']);
			  
			  
			 if($qua>0){
				  //определим главную позицию для отрисовки скидок в карте КП
				 if($h['parent_id']==0){
					 $primary_position_id=$h['position_id'];
					 $pl_discount_id=$valarr[5];
					 $pl_discount_value=$valarr[6];
				 }
				 
				//нужно правильно вычислять цены...
				
				$price=$_pli->DispatchCalcPrice( $h['pl_position_id'],$h['currency_id'],  NULL,  $price_kind_id,NULL,NULL,NULL, $extra_charges);
				
				//$price_f=$_pli->CalcPriceF($h['pl_position_id'],$h['currency_id'], $h['price'],NULL,$valarr[5], $valarr[6], $valarr[7],$price_kind_id);
				
				$price_f=$_pli->DispatchCalcPriceF($h['pl_position_id'],$h['currency_id'],  $h['price'],NULL,$valarr[5], $valarr[6], $valarr[7],$price_kind_id,NULL, $extra_charges);
				
				$digits=$_round_define->DefineDigits($h['group_id']);		
				
				$cost=round($price_f*$qua,$digits);
				$total=$cost;
				
				if($h['is_install']==1){
					$install_mode=1;
					$install_notes='Позиция № '; 
					if(strlen($h['code'])>0) $install_notes.=$h['code']; else $install_notes.=$h['pl_position_id']; 
					$install_value=$total;
				}
				
				if($h['is_delivery']==1){
					$delivery_mode=1;
					$delivery_notes='Позиция № '; 
					if(strlen($h['code'])>0) $delivery_notes.=$h['code']; else $delivery_notes.=$h['pl_position_id']; 
					$delivery_value=$total;
				}
				
				//$producer_id=$h['is_install'];
				$producer_id=$h['producer_id'];
				
				//название и текст для позиции в зависимости от выбора языка в пл
				$name=''; $txt_for_kp='';
				
				if($h['parent_id']!=0){
					if($lang_rus==1){
						$name.= $h['position_name'];			
						$txt_for_kp.=$h['txt_for_kp'];
					}
					if(($lang_rus==1)&&($lang_en==1)){
						$name.= ' / ';			
						$txt_for_kp.=' / ';
					}
					if($lang_en==1){
						$name.= $h['position_name_en'];			
						$txt_for_kp.=$h['txt_for_kp_en'];
					}
				}else{
					$name.= $h['position_name'];			
					$txt_for_kp.=$h['txt_for_kp'];
				}
				
				if($h['parent_group_parent_group_id']==0){
					$h['three_group_id']=0;
					$h['two_group_id']=$h['group_id'];
					$h['parent_group_id']=$h['parent_group_id'];
					
				}else{
					  $h['three_group_id']=$h['two_group_id'];
					  $h['two_group_id']=$h['group_id'];
					  $h['parent_group_id']=$h['parent_group_parent_group_id']; 
				}
				$group_id=$h['parent_group_id'];
				 
				
				//echo $valarr[5];
				$positions[]=array(
						  'pl_position_id'=>$pos_id,
						  'code'=>$h['code'],
						  'parent_id'=>$h['parent_id'],
						  'currency_id'=>$h['currency_id'],
						  'position_id'=>$h['position_id'],
						  'hash'=>md5($h['pl_position_id'].'_'.$h['position_id'].'_'.$h['pl_discount_id'].'_'.$h['pl_discount_value'].'_'.$h['pl_discount_rub_or_percent']),
						  'position_name'=>$name, //$h['position_name'],
						  'dim_name'=>$h['dim_name'],
						  'dimension_id'=>$h['dimension_id'],
						  'signature'=>$h['signature'],
						  'quantity'=>$qua,
						  'price'=>$price,
						  'price_f'=>$price_f,
						  'price_pm'=>$price_f,
						  'has_pm'=>false,
						  'cost'=>$cost,
						  'total'=>$total,
						  'plus_or_minus'=>0,
						  'rub_or_percent'=>0,
						  'value'=>0,
						  'in_rasp'=>0,
						  'txt_for_kp'=>$txt_for_kp, //$h['txt_for_kp'],
						  'photo_for_kp'=>$h['photo_for_kp'],
						  
						  'is_delivery'=>$h['is_delivery'],
						  'is_install'=>$h['is_install'],
						  'is_mandatory'=>$h['is_mandatory'],
						  'is_calc_price'=>$h['is_calc_price'],
						  
						  'discount_rub_or_percent'=> $valarr[7],
						  'discount_value'=>$valarr[6],
						  'nds_proc'=>NDS,
						  'nds_summ'=>sprintf("%.2f",($price_f-$price_f/((100+NDS)/100))),
						  'pl_discount_id'=>	$valarr[5],
						  'pl_discount_value'=>	$valarr[6],
						  'pl_discount_rub_or_percent'=> $valarr[7],
						  'price_kind_id'=> $valarr[8],
						  'delivery_ddpm'=> $h['delivery_ddpm'],
						  'discs1'=>$max_vals,
						  
						 /* 'two_group_id'=>$h['group_id'], //группа товаров п/л - информационное поле
						  'parent_group_id'=>$h['parent_group_id'], //раздел п/л
						 */
						   'three_group_id' =>$h['three_group_id'],
				   'two_group_id' =>$h['two_group_id'],
				  'parent_group_id' =>$h['parent_group_id'], 
					  
						 
						  'producer_id'=>$h['producer_id'],
						  
						  'print_form_has_komplekt'=>$print_form_has_komplekt,
						  'extra_charges'=>$extra_charges
												  
					  );
			  }
			  
			  
//			 var_dump($primary_position_id); die();
				$_t_pos=new PosItem;
				$t_pos=$_t_pos->GetItemById($primary_position_id);
				$editing_user['supply_pdate_id']= $t_pos['supply_pdate_id'];
			  
			  
			  
			//	print_r($valarr);
			}
			
			
			
			//echo $install_mode;
			/*echo '<pre>';
			print_r($positions);
			echo '</pre>';
			*/
			
			
			 //скорректировать скидки и стоимости  в процессе подсчета стоимости КП	 
			$sm1->assign('total_cost',$_kpf->CalcCost($positions, true));
			$sm1->assign('total_nds',$_kpf->CalcNDS($positions));
			
			//подставить валюту в общую сумму
			$currency=$_kpf->GetCurrency($positions);	
			//print_r($currency);	
			$sm1->assign('signature',$currency['signature']);
			
			if(count($positions)>0) {
				 $sm1->assign('has_positions',true);
				
			}
			
		
			
			//получить правила по $primary_position_id
			$_rules=new PlRules;
			$rules=$_rules->GetRulesCli($primary_position_id);
			$sm1->assign('rules',$rules);
			
			
			
			//найти и подставить рентабельность в карту
			$_kpf->CalcProfit($positions, $profit_percent, $profit_value, $profit_currency);
			$sm1->assign('profit_percent',$profit_percent);
			$sm1->assign('profit_value',$profit_value);
			$sm1->assign('profit_currency',$profit_currency);
	
	
			//получим виды скидок
			$_pld=new PlDisGroup;
			$sm1->assign('discs1',$_pld->GetItemsArr());
			
			$sm1->assign('positions',$positions);  
			
			
			//вид КП
			$sm1->assign('price_kind_id', $_GET['price_kind_id']);
			$_pr_kind=new PriceKindItem;
			$pr_kind=$_pr_kind->GetItemById(abs((int)$_GET['price_kind_id']));
			$sm1->assign('price_kind_name', $pr_kind['name']);
			
			
			//текущие значения установки, доставки
			$sm1->assign('delivery_mode', $delivery_mode);
			$sm1->assign('delivery_value', $delivery_value);
			$sm1->assign('delivery_notes', $delivery_notes);
			
			$editing_user['install_mode']=$install_mode;
			$sm1->assign('install_mode', $install_mode);
			$sm1->assign('install_value', $install_value);
			$sm1->assign('install_notes', $install_notes);
	
	
	// Базис поставки:
			
			//как получить producer_id? по  оборудованию
			
			
			$_ks=new KpSupplyGroup; $_ksi=new KpSupplyItem;
			$ks=$_ks->GetItemsByFieldsArr(array('producer_id'=>$producer_id/*, 'price_kind_id'=>abs((int)$_GET['price_kind_id']*/));
			$ksi=$_ksi->GetItemByFields(array('producer_id'=>$producer_id, 'price_kind_id'=>abs((int)$_GET['price_kind_id'])));
			
			//echo $ksi['name'];
			
			$_ids=array(); $_vals=array();
			$_ids[]=0; $_vals[]='';
			foreach($ks as $k=>$v){
				$_ids[]=$v['id']; $_vals[]=$v['name'];	
			}
			$sm1->assign('supply_id_ids',$_ids);
			$sm1->assign('supply_id_vals',$_vals);
			$sm1->assign('supply_id', $ksi['id']); 
			$editing_user['supply_id']=$ksi['id'];
			
		}
		
		
		//валюты
		$_curr=new PlCurrGroup;
		$currs=$_curr->GetItemsArr($currency['currency_id']);
		$_ids=array(); $_vals=array();
		foreach($currs as $k=>$v){
			$_ids[]=$v['id']; $_vals[]=$v['signature'];	
		}
		$sm1->assign('delivery_currency_id_ids',$_ids);
		$sm1->assign('delivery_currency_id_vals',$_vals);
		
		
		
		//разряды для округления
		$sm1->assign('digits', $digits);
		
		
		
		
		//сроки поставки
		$_ksm=new KpSupplyPdateGroup;
		//$ksm=$_ksm->GetItemsArr(0);
		$ksm=$_ksm->GetItemsByIdArr($group_id);
		$_ids=array(); $_vals=array();
		foreach($ksm as $k=>$v){
			$_ids[]=$v['id']; $_vals[]=$v['name'];	
		}
		$sm1->assign('supply_pdate_id_ids',$_ids);
		$sm1->assign('supply_pdate_vals',$_vals);
			
		
		
		//Условия оплаты:
		$_ks=new KpPaymodeGroup;
		$ks=$_ks->GetItemsByIdArr($group_id);
		$_ids=array(); $_vals=array();
		foreach($ks as $k=>$v){
			$_ids[]=$v['id']; $_vals[]=$v['name'];	
		}
		$sm1->assign('paymode_id_ids',$_ids);
		$sm1->assign('paymode_id_vals',$_vals);
		
		//подгрузить текущие условия	
		$_ki=new KpPaymodeItem;
		$ki=$_ki->GetItemById($editing_user['paymode_id']);
		
		$sm1->assign('paymode_is_standart',$ki['is_standart']);
		
		
		
		
		//директора
		$_ug=new UsersSGroup;
		$ug=$_ug->GetUsersByPositionKeyArr('can_sign_as_dir_pr', 1);
		$_ids=array(); $_vals=array();
		foreach($ug as $k=>$v){
			$_ids[]=$v['id']; $_vals[]=$v['name_s'].' '.$v['position_name'];	
		}
		$sm1->assign('user_dir_pr_id_ids',$_ids);
		$sm1->assign('user_dir_pr_id_vals',$_vals);
		
		
		//менеджеры
		//ограничения по сотруднику
			$limited_user=NULL;
		if($au->FltUser($result)){
			//echo 'z';
			$_u_to_u=new UserToUser();
			$u_to_u=$_u_to_u->GetExtendedViewedUserIdsArr($result['id']);
			$limited_user=$u_to_u['sector_ids'];
		}
		
		//$_ug=new UsersSGroup;
		//$ug=$_ug->GetUsersByPositionKeyArr('can_sign_as_manager', 1, array(1,3,4));
		
		$_ug=new UsersSGroup;
		//$ug=$_ug->GetUsersByPositionKeyArr('can_sign_as_manager', 1, array(1,3,4));
		$dec=new DBDecorator();
		//.dep.name
		$dec ->AddEntry(new SqlEntry('pos.name','Менеджер', SqlEntry::LIKE));
		$dec ->AddEntry(new SqlEntry('dep.name','отдел продаж', SqlEntry::LIKE));
		$dec ->AddEntry(new SqlEntry('u.is_active',1, SqlEntry::E));
		$ug=new UsersSGroup;
		$users=$_ug->GetItemsByDecArr($dec);
		
		$_ids=array(); $_vals=array();
		$_ids[]=0; $_vals[]='-нет-';
		foreach($users as $k=>$v){
			if(($limited_user!==NULL)&&!in_array($v['id'], $limited_user)) continue;
			
			//дилеров и внешних партнеров видят только они (сами себя) + администраторы
			//if(in_array($v['department_id'], array(16,17))&&!(in_array($result['department_id'], array(16,17)) || in_array($result['id'], array(1,2,3))  )) continue;
			
			
			$_ids[]=$v['id']; $_vals[]=$v['name_s'].' '.$v['position_name'];	
		}
		if(!in_array($result['id'], $_ids)){
			$_ids[]=$result['id']; $_vals[]=$result['name_s'].' '.$result['position_name'];		
		}
		
		$sm1->assign('user_manager_id_ids',$_ids);
		$sm1->assign('user_manager_id_vals',$_vals);
		
		
		//гарантия
		$_ks=new KpWarrantyGroup;
		$ks=$_ks->GetItemsArr();
		$_ids=array(); $_vals=array();
		foreach($ks as $k=>$v){
			$_ids[]=$v['id']; $_vals[]=$v['name'];	
		}
		$sm1->assign('warranty_id_ids',$_ids);
		$sm1->assign('warranty_id_vals',$_vals);
		
		
		
		
		//скидка в самой карте (не в таблице)
		//echo $primary_position_id; 
		//echo $editing_user['price_kind_id'];		
		$dis_in_card=$_pdm->GetItemsByKindPosArr($primary_position_id, $editing_user['price_kind_id'],0,$result['id']); 
		//print_r($dis_in_card);
		$sm1->assign('primary_position_id', $primary_position_id);
		$sm1->assign('pl_discount_id', $pl_discount_id);
		$sm1->assign('pl_discount_value', $pl_discount_value);
		
		$sm1->assign('dis_in_card', $dis_in_card);
		
		$sm1->assign('can_override_manager_discount', $au->user_rights->CheckAccess('w',751)); //скидка менеджера без ограничений
		$sm1->assign('can_override_ruk_discount', $au->user_rights->CheckAccess('w',752));
		
		$sm1->assign('can_ruk_discount',$au->user_rights->CheckAccess('w',753));
		
		$sm1->assign('can_change_supply_pdate',$au->user_rights->CheckAccess('w',986));
		
		 
		
		
		//блок аннулирования
		
		$editing_user['can_annul']=$_bill->DocCanAnnul($editing_user['id'],$reason,$editing_user)&&$au->user_rights->CheckAccess('w',713);
		if(!$au->user_rights->CheckAccess('w',713)) $reason='недостаточно прав для данной операции';
		$editing_user['can_annul_reason']=$reason;
		
		//$editing_user['binded_to_annul']=$_bill->GetBindedDocumentsToAnnul($editing_user['id']);
		
		
		$editing_user['can_restore']=$_bill->DocCanRestore($editing_user['id'],$reason,$editing_user)&&$au->user_rights->CheckAccess('w',713);
			if(!$au->user_rights->CheckAccess('w',714)) $reason='недостаточно прав для данной операции';
		
		
		
		
		$sm1->assign('org',stripslashes($opf['name'].' '.$orgitem['full_name']));
		$sm1->assign('org_id',$result['org_id']);
		
		
		
		
		//возможность РЕДАКТИРОВАНИЯ - только если is_confirmed_price==0
		$sm1->assign('can_modify', in_array($editing_user['status_id'],$_editable_status_id)); 
		
		
		//если у счета утверждены цены - просматривать можно при наличии прав 365 (выдача +/- в счете)
		//в других статусах: 130 (работа с +/-)
		$sm1->assign('cannot_view_pm',!$au->user_rights->CheckAccess('w',700));
		
		
		$sm1->assign('not_changed_pos',true);
		
	
		//поставщики
		
		
		 
		$_sup=new SupplierItem; $_sup_opf=new OpfItem;
		$sup=$_sup->GetItemById($editing_user['supplier_id']);
		$sup_opf=$_sup_opf->GetItemById($sup['opf_id']);
		$editing_user['supplier_string']=$sup_opf['name'].' '.$sup['full_name'];
		$_cont=new SupplierContactItem;
		$cont=$_cont->GetItemById($editing_user['contact_id']);
		$editing_user['contact_string']=$cont['name'].', '.$cont['position'];
		
		
	
		
		
		
		//Примечания
		$rg=new KpNotesGroup;
		$sm1->assign('notes',$rg->GetItemsByIdArr($editing_user['id'], 0,0, $editing_user['is_confirmed_price']==1, $au->user_rights->CheckAccess('w',703), $au->user_rights->CheckAccess('w',704), $result['id']));
		$sm1->assign('can_notes',true);
		$sm1->assign('can_notes_edit',$au->user_rights->CheckAccess('w',702)/*&&($editing_user['is_confirmed_price']==0)*/);
		
		
		$sm1->assign('BILLUP',BILLUP);
		$sm1->assign('NDS',NDS);
		
		$sm1->assign('can_print',$au->user_rights->CheckAccess('w',712)); 
		//также проверить, есть ли для главной позиции КП шаблон для печати
		$main_id=0; $txt_kp='';
		foreach($bpg as $k=>$v){
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
		$sm1->assign('has_print_form',(($kp_form!==false)&&(strlen($txt_kp)>0)));
		
		
		$cannot_edit_reason='';
		$sm1->assign('can_edit_quantities',$au->user_rights->CheckAccess('w',715)&&in_array($editing_user['status_id'],$_editable_status_id)&&$_bill->CanEditQuantities($editing_user['id'],$cannot_edit_reason,$editing_user)); 
		if(strlen($cannot_edit_reason)>0) $cannot_edit_reason.=', либо ';
		$sm1->assign('cannot_edit_reason',$cannot_edit_reason);
		
		
		
		
		
		//кнопка доступна, если есть права и не утв-на отгрузка счета
		$sm1->assign('can_add_positions',$au->user_rights->CheckAccess('w',697)&&(($editing_user['is_confirmed_price']==0)&&($editing_user['status_id']!=3)));
		
		
		
		
	
		
		$sm1->assign('can_delete_positions',$au->user_rights->CheckAccess('w',699)); 
		
		
		//проверка закрыотого периода
		$not_in_closed_period=$_bill->CheckClosePdate($editing_user['id'], $closed_period_reason);
		$sm1->assign('not_in_closed_period', $not_in_closed_period);
		$sm1->assign('closed_period_reason', $closed_period_reason);
		
		
				
		
		//дата начала периода
		$sm1->assign('pch_date', $pch_date);
		//массив запрещенных периодов
		$_pergroup=new PerGroup;
		$sm1->assign('cdates', $_pergroup->GetItemsByIdArr($result['org_id'],0,1));
		
	
	
	
		//блок утверждения цен!
		if(($editing_user['is_confirmed_price']==1)&&($editing_user['user_confirm_price_id']!=0)){
			$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['user_confirm_price_id']);
			if($editing_user['is_auto_confirmed_price']==1)
				$confirmer='Автоматически утверждено при создании документа пользователем: '.$_user_confirmer['position_name'].' '.$_user_confirmer['name_s'].' '.' '.date("d.m.Y H:i:s",$editing_user['confirm_price_pdate']);
			else $confirmer=$_user_confirmer['position_name'].' '.$_user_confirmer['name_s'].' '.' '.date("d.m.Y H:i:s",$editing_user['confirm_price_pdate']);
			
						
			$sm1->assign('confirmer',$confirmer);
			
			$sm1->assign('is_confirmed_price_confirmer',$confirmer);
		}
		
		$can_confirm_price=false;
		if($editing_user['is_confirmed_shipping']==0){
			
			  
		  
		  if($editing_user['is_confirmed_price']==1){
			  if($au->user_rights->CheckAccess('w',711)&&$au->user_rights->CheckAccess('w',763)){
				  //полные права
				  $can_confirm_price=true;	
			  //}elseif($au->user_rights->CheckAccess('w',711)&&!$au->user_rights->CheckAccess('w',763)&&($result['id']==$editing_user['manager_id'])){
			 }elseif($au->user_rights->CheckAccess('w',711)||$au->user_rights->CheckAccess('w',763)){	  
				  //есть права + сам утвердил
				  $can_confirm_price=true;	
			  }else{
				  $can_confirm_price=false;
			  }
		  }else{
			  //709
			  $can_confirm_price=$au->user_rights->CheckAccess('w',709)&&in_array($editing_user['status_id'],$_editable_status_id)  ;
		  }
		}
		$sm1->assign('can_confirm_price',$can_confirm_price);
		
		
		
		
		$reason='';
		
		//получим список тех, кто может превышать скидку рук-ля (кроме 2,3)
		$_usg1=new UsersSGroup;
		$usg1=$_usg1->GetUsersByRightArr('w', 752);
		//print_r($users);
		foreach($usg1 as $k=>$v){
			if(in_array($v['id'], array(2,3))) unset($usg1[$k]);
		}
		$sm1->assign('users_can_override_ruk_discount', $usg1);
	

		
		
		$sm1->assign('can_create_outcoming_bill', false);//$au->user_rights->CheckAccess('w',92)); 
		
		$sm1->assign('can_create',$au->user_rights->CheckAccess('w',696)); 
		$sm1->assign('can_edit',$au->user_rights->CheckAccess('w',701)); 
		$sm1->assign('can_exclude_positions',$au->user_rights->CheckAccess('w',129)); 
		
		$sm1->assign('can_ruk_discount',$au->user_rights->CheckAccess('w',753));
		$sm1->assign('can_unselect_pnr', $au->user_rights->CheckAccess('w',755));
		
		$sm1->assign('can_delete_mandatory_positions',$au->user_rights->CheckAccess('w',768));
		
		$sm1->assign('can_send_email',$au->user_rights->CheckAccess('w',843));
		$sm1->assign('can_view_re',$au->user_rights->CheckAccess('w',762));
		$sm1->assign('can_view_re_unconfirmed',$au->user_rights->CheckAccess('w',816));
		
		
		$sm1->assign('can_exclude_valid_pdate',$au->user_rights->CheckAccess('w',818));
		$sm1->assign('can_lower_supply_pdate',$au->user_rights->CheckAccess('w',819));
		
		$sm1->assign('can_view_re_extended',$au->user_rights->CheckAccess('w',824)); //расширенный просмотр рентабельности
		
		$sm1->assign('can_view_prices',$au->user_rights->CheckAccess('w',842));
		
		
		
		$sm1->assign('can_confirm_unstandart',$au->user_rights->CheckAccess('w',817)); //можно утв-ть с нест. условиями
		//получить список сотрудников, кто может утв-ть с нестд. услвоями:
		$_usg=new UsersSGroup;
		$unst=$_usg->GetUsersByRightArr('w', 817);
		foreach($unst as $k=>$v){
			if(in_array($v['id'], array(2,3))) unset($unst[$k]);
		}
		
		$sm1->assign('confirm_unstandart_users',$unst); 
		
		
		//найдем шаблон из группы
		$_pgi=new PosGroupItem;
		
	//	var_dump( $group_id);
		$pgi=$_pgi->GetItemById($group_id);
		
	//	var_dump( $pgi['kp_edit']);
		//$user_form=$sm1->fetch('kp/kp_edit'.$print_add.'.html');
		
		
		$sm1->assign('bill',$editing_user);
		
		$user_form=$sm1->fetch('kp/'.$pgi['kp_edit']);
		
		//покажем журнал событий по позиции
		if($au->user_rights->CheckAccess('w',716)){
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
		
			$decorator->AddEntry(new SqlEntry('object_id',NULL, SqlEntry::IN_VALUES, NULL, array(695,
696,
697,
698,
699,
700,
701,
702,
703,
704,
705,
706,
707,
708,
709,
710,
711,
712,
713,
714,
715,
716

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
			
			$llg=$log->ShowLog('syslog/log_doc.html',$decorator,$from,$to_page,'ed_kp.php',true,true,true);
			
			$sm->assign('syslog',$llg);		
				
		}
		
		
	}
	
	
	$sm->assign('users',$user_form);
	$sm->assign('from_begin',$from_begin);
	
	$sm->assign('print_pdate', date("d.m.Y H:i:s"));
	//$username=$result['login'];
	$username=stripslashes($result['name_s']).' '.$username;	
	$sm->assign('print_username',$username);
	
	$content=$sm->fetch('kp/ed_kp_page'.$print_add.'.html');
	
	
	
	$smarty->assign('fast_menu', $menu_arr_fast);
	  $smarty->assign('main_menu',$menu_res);
	$smarty->assign('content',$content);
	if($print==0) $smarty->display('page.html');
	else echo $content;
	
	unset($smarty);


$smarty = new SmartyAdm;

//работа с футером
require_once('inc/footer.php');
if(isset($footer_res)){
	$smarty->assign('footer',$footer_res);
}else $smarty->assign('footer','');

if($print==0) $smarty->display('bottom.html');
else $smarty->display('bottom_print.html');
unset($smarty);
?>