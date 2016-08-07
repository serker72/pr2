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

 require_once('classes/pl_currgroup.php');
require_once('classes/pl_curritem.php');

require_once('classes/suppliersgroup.php');
require_once('classes/supplieritem.php');
 

 

require_once('classes/user_s_item.php');

 

 
require_once('classes/orgitem.php');
require_once('classes/opfitem.php');

 

require_once('classes/suppliercontactitem.php');
require_once('classes/supcontract_group.php');

require_once('classes/tender.class.php');

 
require_once('classes/lead.class.php');
require_once('classes/tz.class.php');



require_once('classes/tz_fileitem.php');
require_once('classes/tz_filegroup.php');
 

require_once('classes/supplier_country_group.php');
require_once('classes/supplier_city_item.php');
 
require_once('classes/lead_history_group.php');
require_once('classes/docstatusitem.php');

require_once('classes/lead_history_item.php'); 

require_once('classes/lead_view_item.php');

require_once('classes/pl_positem.php');

require_once('classes/kpitem.php');

require_once('classes/kp_in.class.php');
require_once('classes/bdr.class.php');

$_pch=new PeriodChecker;
$pch_date=$_pch->GetDate();



$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'ТЗ');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}


$_dem=new TZ_AbstractItem;

$_plan1=new TZ_Group;
$available_users=$_plan1->GetAvailableTZIds($result['id']);

$_plan=new TZ_Group;


$_supplier=new SupplierItem;
 $log=new ActionLog;
 $_supgroup=new SuppliersGroup;

 
$_orgitem=new OrgItem;
$orgitem=$_orgitem->GetItemById($result['org_id']);
$_opf=new OpfItem;
$opfitem=$_opf->GetItemById($orgitem['opf_id']);

if(!isset($_GET['action'])){
	if(!isset($_POST['action'])){
		$action=0;
	}else $action=abs((int)$_POST['action']);
}else $action=abs((int)$_GET['action']);

if(!isset($_GET['from_begin'])){
	if(!isset($_POST['from_begin'])){
		$from_begin=0;
	}else $from_begin=1; 
}else $from_begin=1;

$object_id=array();
switch($action){
	case 0:
	$object_id[]=1006;
	break;
	case 1:
	$object_id[]=1009;
	break;
	case 2:
	$object_id[]=1009;
	break;
	default:
	$object_id[]=1009;
	break;
}

$_editable_status_id=array();
$_editable_status_id[]=1;
 



if(!isset($_GET['print'])){
	if(!isset($_POST['print'])){
		$print=0;
	}else $print=abs((int)$_POST['print']); 
}else $print=abs((int)$_GET['print']);

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

if(!isset($_GET['print'])){
	if(!isset($_POST['print'])){
		$print=0;
	}else $print=abs((int)$_POST['print']); 
}else $print=abs((int)$_GET['print']);

 
	


if($action==0){
	 if(!isset($_GET['lead_id'])){
		if(!isset($_POST['lead_id'])){
			header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			include('404.php');
			die();
		}else $lead_id=abs((int)$_POST['lead_id']);	
	}else $lead_id=abs((int)$_GET['lead_id']);
	
	$_lead=new Lead_Item;
	$lead=$_lead->GetItemById($lead_id);
	if($lead===false){
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include('404.php');
		die();
	}
	 
	
}elseif(($action==1)||($action==2)){

	if(!isset($_GET['id'])){
		if(!isset($_POST['id'])){
			header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			include('404.php');
			die();
		}else $id=abs((int)$_POST['id']);	
	}else $id=abs((int)$_GET['id']);
	
	//проверка наличия пользователя
	$editing_user=$_dem->GetItemByFields(array('id'=>$id));
	if($editing_user===false){
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include('404.php');
		die();
	}
	foreach($editing_user as $k=>$v) $editing_user[$k]=stripslashes($v);
	
	
	$_tg=new TZ_Group;
	
	if(!$au->user_rights->CheckAccess('w',1008)){	
		$available_tenders=$_tg->GetAvailableTZIds($result['id']);
		$is_shown=in_array($id, $available_tenders);
	
		if(!$is_shown){
			header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			include('404.php');
			die();
		}
	}
	
	
	//найти родительский лид
	$_lead=new Lead_Item;
	$lead=$_lead->getitembyid($editing_user['lead_id']);
 
}

 
 //файловый блок

if($action==2){
	if(!$au->user_rights->CheckAccess('w',1009)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}
	
		
	
	if(!isset($_GET['file_id'])){
		if(!isset($_POST['file_id'])){
			header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			include("404.php");
			die();
		}else $file_idabs((int)$_POST['file_id']);
	}else $file_id=abs((int)$_GET['file_id']);
	
	$_pfi=new TZFileItem;
	
	$file=$_pfi->GetItemById($file_id);
	
	if($file!==false){
		$_pfi->Del($file_id);
		
		$log->PutEntry($result['id'],'удалил файл ТЗ',NULL, 1009,NULL,'имя файла '.SecStr($file['orig_name']));
	}
	
	header("Location: ed_tz.php?action=1&id=".$id.'&folder_id='.$folder_id);
	die();
}




//обработка данных

if(($action==0)&&(isset($_POST['doNew'])||isset($_POST['doNewEdit']))){
	 
	
	
	
	
	$params=array();
	//обычная загрузка прочих параметров
	$params['created_id']=$result['id'];
	$params['pdate']=time();
	
	$params['manager_id']=abs((int)$_POST['manager_id']);
	
	 
	$params['lead_id']=abs((int)$_POST['lead_id']);
	
	
	$params['description']= SecStr($_POST['description']);
	
	
 //	$params['given_pdate']=  DateFromdmY($_POST['given_pdate']) ;
	
	 
	
	$params['status_id']=1;
	 		
	 
	$_res=new TZ_Resolver();
		
		
	$code=	$_res->instance->Add($params);
	 
	//$code=1;
	//запись в журнале
	if($code>0){
		$log->PutEntry($result['id'],'создал ТЗ',NULL,1006,NULL,NULL,$code);	
		
		foreach($params as $k=>$v){
			
		  
				
				$log->PutEntry($result['id'],'создал ТЗ',NULL,1006, NULL, 'в поле '.$k.' установлено значение '.$v,$code);		
			 
		}
		
	}
	
	  
	
	
	
	
	  	 
	 
	
	
	 
	//приложим файлы!
	//upload_file_6A83_tmp" value="_ZpaGsu91PI.jpg" 
	$fmi=new TZFileItem;
	foreach($_POST as $k=>$v){
	  if(eregi("^upload_file_",$k)){
		    $filename=eregi_replace("^upload_file_", '', eregi_replace("_tmp$",".tmp",$k));
		  $fmi->Add(array('bill_id'=>$code, 'filename'=>SecStr(basename($filename)), 'orig_name'=>SecStr($v), 'user_id'=>$result['id'], 'pdate'=>time()));
		  
		   $log->PutEntry($result['id'], 'прикрепил файл к ТЗ', NULL, 1006, NULL,'Служебное имя файла: '.SecStr(basename($filename)).' Имя файла: '.SecStr($v),$code);
		   
		   
	  }
	}
	 
	
	  
		  
		  
	if($au->user_rights->CheckAccess('w',1006)&&($_POST['do_confirm']==1)){
	
	
		$_res->instance->Edit($code,array('is_confirmed'=>1, 'user_confirm_id'=>$result['id'], ' 	confirm_pdate'=>time()),true,$result);
					  
		$log->PutEntry($result['id'],'автоматически утвердил заполнение ТЗ',NULL,1006, NULL, NULL,$code);	
		
		
	}else{
		$log->PutEntry($result['id'],'отказался от автоматического утверждения заполнения ТЗ',NULL,1006, NULL, NULL,$code);	
	}
	 
	
	//отправим сообщение о создании тендера	
	//$_res->instance->NewTenderMessage($code);	
	
	/*
	echo '<pre>';
	print_r($_POST);
	print_r($params);
	echo '</pre>';
	die();
	*/
	//перенаправления
	if(isset($_POST['doNew'])){
		 header("Location: ed_lead.php?action=1&id=".$_POST['lead_id']."&show_tz=1");
		die();
	}elseif(isset($_POST['doNewEdit'])){
		  
		header("Location: ed_tz.php?action=1&id=".$code.'&from_begin='.$from_begin);
		die();	
		
	}else{
		header("Location: leads.php");
		die();
	}
	 
	
	die();
	


}elseif(($action==1)&&(isset($_POST['doEdit'])||isset($_POST['doEditStay'])
	 
	
	)){
	 
	
	//редактирование возможно, если позволяет статус
	$_res=new TZ_Resolver();
		
	
	//поля формируем в зависимости от их активности в текущем статусе
 	$_roles=new TZ_FieldRules($result); //var_dump($_roles->GetTable());
	$field_rights0=$_roles->GetFields($editing_user, $result['id']);	
	$field_rights1=$_roles->GetFields($editing_user, $result['id'], $_POST['current_status_id']);
	$field_rights=array(); 
	foreach($field_rights0 as $k=>$v) $field_rights[$k]=$v&&$field_rights1[$k];
	 
			
	
	$params=array();

	
	//поля формируем в зависимости от их активности в текущем статусе
	$condition =in_array($_POST['current_status_id'],$_editable_status_id)&&in_array($editing_user['status_id'],$_editable_status_id);
	
	if($condition){
	
	
		// $params['manager_id']=abs((int)$_POST['manager_id']);
	
		
		
		 		  
	 
		$params['description']= SecStr($_POST['description']);
		
		
	// 	$params['given_pdate']=   DateFromdmY($_POST['given_pdate']) ;
		
		 
		
	}
	else{
		//кроме основного условия, еще может сработать дополнительное:	
	    if($field_rights['to_select_eq']){
			$params['pl_position_id']=abs((int)$_POST['pl_position_id']);
			
		}

		
		//правка соответствия КП ТЗ - отдел закупок
		/*if($au->user_rights->CheckAccess('w',1015)&&($editing_user['is_confirmed']==1)){
			
			$mode_params=array();
			if(isset($_POST['fulful_kp_2'])){
				$mode_params['fulful_kp']=2;
				$mode_params['fulfil_pdate']=time();
				$mode_params['fulfil_user_id']=$result['id'];
				$mode_params['fulfil_kp_not']=SecStr($_POST['fulfil_kp_not']);		
			}elseif(isset($_POST['fulful_kp_1'])){
				$mode_params['fulful_kp']=1;
				$mode_params['fulfil_pdate']=time();
				$mode_params['fulfil_user_id']=$result['id'];	
			}else{
				$mode_params['fulful_kp']=0;
				$mode_params['fulfil_pdate']=time();
				$mode_params['fulfil_user_id']=$result['id'];	
			}
			
			if($editing_user['fulful_kp']!=$mode_params['fulful_kp']) foreach($mode_params as $k=>$v) $params[$k]=$v;
		}
		
		//правка соответствия КП ТЗ - технический отдел
		if($au->user_rights->CheckAccess('w',1027)&&($editing_user['is_confirmed']==1)){
			
			$mode_params=array();
			if(isset($_POST['fulful_kp1_2'])){
				$mode_params['fulful_kp1']=2;
				$mode_params['fulfil_pdate1']=time();
				$mode_params['fulfil_user_id1']=$result['id'];
				$mode_params['fulfil_kp_not1']=SecStr($_POST['fulfil_kp_not1']);		
			}elseif(isset($_POST['fulful_kp1_1'])){
				$mode_params['fulful_kp1']=1;
				$mode_params['fulfil_pdate1']=time();
				$mode_params['fulfil_user_id1']=$result['id'];	
			}else{
				$mode_params['fulful_kp1']=0;
				$mode_params['fulfil_pdate1']=time();
				$mode_params['fulfil_user_id1']=$result['id'];	
			}
			
			if($editing_user['fulful_kp1']!=$mode_params['fulful_kp1']) foreach($mode_params as $k=>$v) $params[$k]=$v;
		}
		
		*/
		
		
		
		
	}
//	
	
	$_res->instance->Edit($id, $params);
	
		
	//$_dem->Edit($id, $params);
	//die();
	//запись в журнале
	//записи в лог. сравнить старые и новые записи
	foreach($params as $k=>$v){
		
		if(addslashes($editing_user[$k])!=$v){
			if($k=='fulful_kp'){
				if($v==0){
					$log->PutEntry($result['id'],'редактировал ТЗ',NULL,1009, NULL, "снял выбор соответствия ТЗ КП - отдел закупок",$id);
				}elseif($v==1){
					$log->PutEntry($result['id'],'редактировал ТЗ',NULL,1009, NULL, "установил, что ТЗ соответствует КП - отдел закупок",$id);
				}elseif($v==2){
					$log->PutEntry($result['id'],'редактировал ТЗ',NULL,1009, NULL, "установил, что ТЗ не соответствует КП - отдел закупок, несоответствия: ".$params['fulfil_kp_not'],$id);
				}
				
				continue;	
			}
			
			if($k=='fulful_kp1'){
				if($v==0){
					$log->PutEntry($result['id'],'редактировал ТЗ',NULL,1009, NULL, "снял выбор соответствия ТЗ КП - технический отдел",$id);
				}elseif($v==1){
					$log->PutEntry($result['id'],'редактировал ТЗ',NULL,1009, NULL, "установил, что ТЗ соответствует КП  - технический отдел",$id);
				}elseif($v==2){
					$log->PutEntry($result['id'],'редактировал ТЗ',NULL,1009, NULL, "установил, что ТЗ не соответствует КП  - технический отдел, несоответствия: ".$params['fulfil_kp_not1'],$id);
				}
				
				continue;	
			}
			
			if($k=='pl_position_id'){
				if($v!=0){
					$_pl=new PlPosItem; $pl=$_pl->GetItemById($v);
				
					$log->PutEntry($result['id'],'редактировал ТЗ',NULL,1009, NULL, SecStr('выбрано оборудование '.$pl['name']),$id);
					continue;
				}
			}
			
			
			$log->PutEntry($result['id'],'редактировал ТЗ',NULL,1009, NULL, 'в поле '.$k.' установлено значение '.$v,$id);
			
					
		}	
		
	}
	
  
	
	if(
	($editing_user['is_not_eq']==1)&&
			
			!in_array($editing_user['status_id'], array(3,27))&&
			
			($lead['status_id']==28)
	
	){
	//поставщики
	 
		$_supplier=new SupplierItem;
		$_sg=new TZ_SupplierGroup;
		$_opf=new OpfItem;
		
		
		$positions=array();
		
		foreach($_POST as $k=>$v){
		  if(eregi("^suppliertz_id_([0-9]+)",$k)){
			  
			  $hash=abs((int)eregi_replace("^suppliertz_id_","",$k));
			  
			  $supplier_id=$hash; //abs((int)$_POST['new_share_user_id_'.$hash]);
			   
			  //найдем контакты
			  $contacts=array();
			  
			  foreach($_POST as $k1=>$v1) if(eregi("^suppliertz_contact_id_".$supplier_id."_([0-9]+)",$k1)){
			  	$contacts[]=abs((int)$v1);
			  }
			  
			 
			  $positions[]=array(
				  'sched_id'=>$id,
				   
				  'supplier_id'=>$supplier_id,
				  'contacts'=>$contacts,
				  'note'=> SecStr($_POST['suppliertz_note_'.$hash])
			  );
			  
		  }
		}
		
		$log_entries=$_sg->AddSuppliers($id, $positions); 
		//die();
		//запишем в журнал
		 foreach($log_entries as $k=>$v){
			   $supplier=$_supplier->GetItemById($v['supplier_id']);
			  $opf=$_opf->GetItemById($supplier['opf_id']); 
			 
			  $description=SecStr($supplier['full_name'].' '.$opf['name'].', примечание: '.$v['note']);
			 
			  
			  if($v['action']==0){
				  $log->PutEntry($result['id'],'добавил поставщика по ТЗ',NULL,1009,NULL,$description,$id);	
			  }elseif($v['action']==1){
				  $log->PutEntry($result['id'],'редактировал поставщика по ТЗ',NULL,1009,NULL,$description,$id);
			  }elseif($v['action']==2){
				  $log->PutEntry($result['id'],'удалил поставщика по ТЗ',NULL,1009,NULL,$description,$id);
			  }
			  
		  } 
	 
	}
	
	
	$_dsi=new DocStatusItem; 
	//обработка выделенных кнопок
 
	
	 
	 
	 
		//утверждение заполнения
		
		$_res=new TZ_Resolver();
		
		if($editing_user['is_not_eq']==0){
		  
		  
		  	
		  if($editing_user['is_confirmed']==1){
			 
			  
			  // 
			  if(($au->user_rights->CheckAccess('w',1011)) ){
				  if((!isset($_POST['is_confirmed']))&&($_res->instance->DocCanUnconfirmPrice($id,$rss32))){
					  
					  
					  $_res->instance->Edit($id,array('is_confirmed'=>0, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,$result);
					  
					  $log->PutEntry($result['id'],'снял утверждение заполнения',NULL,1011, NULL, NULL,$id);	
					  
				  }
			  } 
			  
		  }else{
			  //есть права
			  if($au->user_rights->CheckAccess('w',1010) ){
				  if(isset($_POST['is_confirmed'])&&($_POST['is_confirmed']==1)&&($_res->instance->DocCanConfirmPrice($id,$rss32))){
					  
					  $_res->instance->Edit($id,array('is_confirmed'=>1, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,$result);
					  
					  $log->PutEntry($result['id'],'утвердил заполнение',NULL,1010, NULL, NULL,$id);	
					  
					   
					  //die();
				  }
			  } 
		  }
		}
		
		
		
		//утверждение отсутствия оборудования в ПЛ
		if($editing_user['is_confirmed']==1){
		  if($editing_user['is_not_eq']==1){
			  
			  
			  
			  //есть права: либо сам утв.+есть права, либо есть искл. права:
			 
			 	if(!isset($_POST['is_not_eq'])&&($_res->instance->DocCanUnconfirmShip($id, $rss32))){
				
				$can_confirm_shipping=$au->user_rights->CheckAccess('w',1029);
				if($can_confirm_shipping){	  
					  //echo 'zzzzzzzzzzzz';
					  $_res->instance->Edit($id,array('is_not_eq'=>0, 'not_eq_id'=>$result['id'], 'not_eq_pdate'=>time()),true,$result);
					  
					  $log->PutEntry($result['id'],'снял утверждение отсутствия оборудования по ТЗ в ПЛ',NULL,1029, NULL, NULL,$id);	
				}
			}
			 // }
			  
		  }else{
			   
			  //есть права
			  
			   if(isset($_POST['is_not_eq'])&&($_res->instance->DocCanConfirmShip($id, $rss32))){
				    $can_confirm_shipping=$au->user_rights->CheckAccess('w',1028);
					if($can_confirm_shipping){	
					
					 
					 $_res->instance->Edit($id,array('is_not_eq'=>1, 'not_eq_id'=>$result['id'], 'not_eq_pdate'=>time()),true,$result);
					  
					  $log->PutEntry($result['id'],'утвердил отсутствие оборудования по ТЗ в ПЛ',NULL,1028, NULL, NULL,$id);	
					}
						  
				}
			    
		  }
		}
		
		
		//утверждение наличия оборудования в ПЛ
		if($editing_user['is_not_eq']==1){
		  if($editing_user['force_eq_in']==1){
			  
			  
			  
			  //есть права: либо сам утв.+есть права, либо есть искл. права:
			 
			 	if(!isset($_POST['force_eq_in'])&&($_res->instance->DocCanUnconfirm3($id, $rss32))){
				
				$can_confirm_shipping=$au->user_rights->CheckAccess('w',1031)||($result['department_id']==4);
				if($can_confirm_shipping){	  
					  //echo 'zzzzzzzzzzzz';
					  $_res->instance->Edit($id,array('force_eq_in'=>0, 'force_eq_in_id'=>$result['id'], 'force_eq_in_pdate'=>time()),true,$result);
					  
					  $log->PutEntry($result['id'],'снял утверждение наличия оборудования в прайс-листе',NULL,1031, NULL, NULL,$id);	
				}
			}
			 // }
			  
		  }else{
			   
			  //есть права
			  
			   if(isset($_POST['force_eq_in'])&&($_res->instance->DocCanConfirm3($id, $rss32))){
				    $can_confirm_shipping=$au->user_rights->CheckAccess('w',1030)||($result['department_id']==4);
					if($can_confirm_shipping){
						
						/*if($k=='pl_position_id'){
				if($v!=0){
					$_pl=new PlPosItem; $pl=$_pl->GetItemById($v);
				
					$log->PutEntry($result['id'],'редактировал ТЗ',NULL,1009, NULL, SecStr('выбрано оборудование '.$pl['name']),$id);
					continue;
				}
			}*/
					$params=array('force_eq_in'=>1, 'force_eq_in_id'=>$result['id'], 'force_eq_in_pdate'=>time());
					if(isset($_POST['pl_position_id'])) $params['pl_position_id']=abs((int)$_POST['pl_position_id']);	
					
					 
					 $_res->instance->Edit($id,$params,true,$result);
					  
					  $log->PutEntry($result['id'],'утвердил наличие оборудования в прайс-листе',NULL,1030, NULL, NULL,$id);
					  
					  if(isset($_POST['pl_position_id'])&& ($params['pl_position_id']!=0)){
						  $_pl=new PlPosItem; $pl=$_pl->GetItemById($params['pl_position_id']);
					  
						  $log->PutEntry($result['id'],'редактировал ТЗ',NULL,1009, NULL, SecStr('выбрано оборудование '.$pl['name']),$id);
						  
					  }	
					}
						  
				}
			    
		  }
		}
		 
	
	
	//die();
	
	//перенаправления
	if(isset($_POST['doEdit'])){
		 
		header("Location: ed_lead.php?action=1&id=".$editing_user['lead_id']."&show_tz=1");
		die();
	}elseif(isset($_POST['doWork'])||isset($_POST['doEditStay'])
		||isset($_POST['to_work'])
		||isset($_POST['to_rework'])
		||isset($_POST['to_cancel'])
		||isset($_POST['to_view'])
		||isset($_POST['to_defer'])
		
		||isset($_POST['to_win'])
		||isset($_POST['to_fail'])
		||isset($_POST['to_restore_work'])
	
	){
	 
		header("Location: ed_tz.php?action=1&id=".$id.'&from_begin='.$from_begin);
		die();	
		
	}else{
		header("Location: leads.php");
		die();
	}
	
	die();
}


 //журнал событий 
if($action==1){
	$log=new ActionLog;
	 
	$log->PutEntry($result['id'],'открыл ТЗ',NULL,1009, NULL, 'ТЗ № '.$editing_user['code'],$id);
	 
	//отметим лид как просмотренный
/*	$_tview=new Lead_ViewItem;
	$test_view=$_tview->GetItemByFields(array('lead_id'=>$id, 'user_id'=>$result['id']));
	if($test_view===false) $_tview->Add(array('lead_id'=>$id, 'user_id'=>$result['id']));	*/		
} 



//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

if($print==0) $smarty->display('top.html');
else $smarty->display('top_print.html');
unset($smarty);


$_menu_id=75;
	
	if($print==0) include('inc/menu.php');
	
	
	//демонстрация  страницы
	$smarty = new SmartyAdm;
	
	$sm1=new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	//создание позиции
	 if($action==0){
		 
		 
	 
		 
		 
		if(isset($_GET['lead_id'])&&($_GET['lead_id']!=0)){
			 $lead_id=abs((int)$_GET['lead_id']);
			
			 
			 
			 //подставить контрагента, ответственных из ТЕНДЕРА
			 
			 $_lead=new Lead_Item;
			 $lead=$_lead->GetItemById($lead_id);
			 $sm1->assign('lead', $lead);
			 
			 //контрагенты
			$_suppliers1=new Lead_SupplierGroup;
			$sup1=$_suppliers1->GetItemsByIdArr($lead_id);
			$sup2=array();
			if(count($sup1)>0) $sup2[]=$sup1[0];
			$sm1->assign('suppliers', $sup2);
			
			//отвеств сотр-к
			$_user_s=new UserSItem;
			$user_s=$_user_s->GetItemById($lead['manager_id']);
			 
			$sm1->assign('manager_id', $lead['manager_id']);
			$sm1->assign('manager_string', $user_s['name_s']);
			
			
			 	
		} 
		
		 $sm1->assign('lead_id', $lead_id); 
	 
		

		$sm1->assign('now_time',  date('d.m.Y H:i:s')); 
		$sm1->assign('now_date',  date('d.m.Y')); 
		 
	 
	 	
		
		
		//получим список тех, кто может снять утверждение заполнения
		$_usg1=new UsersSGroup;
		$usg1=$_usg1->GetUsersByRightArr('w', 1011);
		$sm1->assign('can_unconfirm_users',$usg1);
		$sm1->assign('can_unconfirm',$au->user_rights->CheckAccess('w',1011));
		
		
		 
		$sm1->assign('session_id', session_id());
		
	  
		
		$sm1->assign('can_confirm', $au->user_rights->CheckAccess('w',1010));
		
		
		$user_form=$sm1->fetch('tz/create_tz.html');
		 
		
	 
		
		
	 }elseif($action==1){
		//редактирование позиции
		
		if($print==0) $print_add='';
		else $print_add='_print';
		
		
		//построим доступы
		$_roles=new TZ_FieldRules($result); //var_dump($_roles->GetTable());
		$field_rights=$_roles->GetFields($editing_user, $result['id']);
		$sm1->assign('field_rights', $field_rights);
		
		//var_dump($field_rights);
		
		
		$_wg=new TZ_WorkingGroup;
		$working_time_unf=$_wg->CalcWorkingTime($id, 0, $zz, $times, $is_working );
		$editing_user['times_0']=$times;
		$editing_user['working_time_unf_0']=$working_time_unf;
		$editing_user['0_is_working']=$is_working;
		
		$working_time_unf=$_wg->CalcWorkingTime($id, 1, $zz, $times, $is_working );
		$editing_user['times_1']=$times;
		$editing_user['working_time_unf_1']=$working_time_unf;
		$editing_user['1_is_working']=$is_working;
		
		
		$working_time_unf=$_wg->CalcWorkingTime($id, 3, $zz, $times, $is_working );
		$editing_user['times_3']=$times;
		$editing_user['working_time_unf_3']=$working_time_unf;
		$editing_user['3_is_working']=$is_working;
		
		
		 
		$working_time_unf=$_wg->CalcWorkingTime($id, 4, $zz, $times,$is_working);
		$editing_user['times_4']=$times;
		$editing_user['working_time_unf_4']=$working_time_unf;
		$editing_user['4_is_working']=$is_working;
		
		$working_time_unf=$_wg->CalcTotalWorkingTime($id, $zz, $times,$is_working );
		$editing_user['times_total']=$times;
		$editing_user['working_time_unf_total']=$working_time_unf;
		$editing_user['total_is_working']=$is_working;
		
		
		/*$working_time_unf=$_wg->CalcWorkingTime($id, 2, $zz, $times);
		$editing_user['times_2']=$times;
		$editing_user['working_time_unf_2']=$working_time_unf;*/
		//echo $zz;
		
		//построим доступы
		$can_modify=in_array($editing_user['status_id'],$_editable_status_id);
		
		
			 
			 $_lead=new Lead_Item;
			 $lead=$_lead->GetItemById($editing_user['lead_id']);
			 $sm1->assign('lead', $lead);
			 
		 
		
		
		
		$_res=new TZ_Resolver();
		
		
		//проверка наличия КП по ЛИДУ
		//$_kp=new KpItem;
		//$kp=$_kp->GetItemByFields(array('is_confirmed_price'=>1,'lead_id'=>$editing_user['lead_id']));
		//
		
		$has_kp=$_res->instance->HasActiveKP($id, $kp);
		$sm1->assign('has_kp', $has_kp);
		$sm1->assign('kp', $kp);
		//var_dump($kp); 
		
		
		$editing_user['pdate']=date('d.m.Y H:i:s', $editing_user['pdate']);
		
		
		
		
		if($editing_user['given_pdate']!='')  $editing_user['given_pdate']=date('d.m.Y', $editing_user['given_pdate']);
		
		
		   
		
	  
		
		//блок аннулирования
		
		$editing_user['can_annul']=$_res->instance->DocCanAnnul($editing_user['id'],$reason,$editing_user, $result)&&$au->user_rights->CheckAccess('w',1012);
		if(!$au->user_rights->CheckAccess('w',1012)) $reason='недостаточно прав для данной операции';
		$editing_user['can_annul_reason']=$reason;
		
		 
		
		$editing_user['can_restore']=$_res->instance->DocCanRestore($editing_user['id'],$reason,$editing_user)&&$au->user_rights->CheckAccess('w',1013);
			if(!$au->user_rights->CheckAccess('w',1013)) $reason='недостаточно прав для данной операции';
		
		
		
			//получим список тех, кто может снять утверждение заполнения
		$_usg1=new UsersSGroup;
		$usg1=$_usg1->GetUsersByRightArr('w', 1011);
		$sm1->assign('can_unconfirm_users',$usg1);
		$sm1->assign('can_unconfirm',$au->user_rights->CheckAccess('w',1011));
		
		
		 
		
		//блок утверждения!
		if(($editing_user['is_confirmed']==1)&&($editing_user['user_confirm_id']!=0)){
			$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['user_confirm_id']);
			$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.date("d.m.Y H:i:s",$editing_user['confirm_pdate']);
			
			 
			$sm1->assign('confirmer',$confirmer);
			
			$sm1->assign('is_confirmed_confirmer',$confirmer);
		}
		
		$can_confirm_price=false;
		if($editing_user['is_not_eq']==0){
			
			  
		  
		  if($editing_user['is_confirmed']==1){
			  if($au->user_rights->CheckAccess('w',1011)){
				  //есть права + сам утвердил
				  $can_confirm_price=true;	
			  }else{
				  $can_confirm_price=false;
			  }
		  }else{
			  //95
			  $can_confirm_price=$au->user_rights->CheckAccess('w',1010)&&in_array($editing_user['status_id'],$_editable_status_id)  ;
		  }
		}
		$sm1->assign('can_confirm',$can_confirm_price);
		
		  
		$reason='';
		
		
		$sm1->assign('can_unconfirm_by_document',(int)$_res->instance->DocCanUnconfirmPrice($editing_user['id'],$reason));
		$sm1->assign('can_unconfirm_by_document_reason',$reason);
		
		
		
		//блок утв. выполнения
		if(($editing_user['is_not_eq']==1)&&($editing_user['not_eq_id']!=0)){
			$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['not_eq_id']);
			$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.date("d.m.Y H:i:s",$editing_user['not_eq_pdate']);
			
			$sm1->assign('is_confirmed_done_confirmer',$confirmer);
		}
		
		$can_confirm_shipping=false;
		if($editing_user['is_confirmed']==1){
		
		   
		  if($editing_user['is_not_eq']==1){
				$can_confirm_shipping=$au->user_rights->CheckAccess('w',1029) ;
		  }else{
		  //ставим утв	
			  $can_confirm_shipping=$au->user_rights->CheckAccess('w',1028) ;
		  }
		}
		// + есть галочка утв. цен
		$can_confirm_shipping=$can_confirm_shipping&&($editing_user['is_confirmed']==1);
		
		
		
		$sm1->assign('can_confirm_done',$can_confirm_shipping);
		
		
		
		//блок 3 галочки
		if(($editing_user['force_eq_in']==1)&&($editing_user['force_eq_in_id']!=0)){
			$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['force_eq_in_id']);
			$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.date("d.m.Y H:i:s",$editing_user['not_eq_pdate']);
			
			$sm1->assign('is_confirmed_force_eq_in_confirmer',$confirmer);
		}
		
		$can_confirm_shipping=false;
		if($editing_user['is_not_eq']==1){
		
		   
		   
		   if($editing_user['force_eq_in']==1){
			   $can_confirm_shipping= $au->user_rights->CheckAccess('w',1031)||($result['department_id']==4);
		   }else{
				$can_confirm_shipping=$au->user_rights->CheckAccess('w',1030)||($result['department_id']==4);
		   } 
		   
		  
		}
		// + есть галочка утв. выполнения
		$can_confirm_shipping=$can_confirm_shipping&&($editing_user['is_not_eq']==1);
		
		
		$sm1->assign('can_confirm_force_eq_in',$can_confirm_shipping);
		
		
		
		
		
		
		
		
		//отвеств сотр-к
		$_user_s=new UserSItem;
		$user_s=$_user_s->GetItemById($editing_user['manager_id']);
		$editing_user['manager_string']=$user_s['name_s'];
		
		
		
	 
		
		 
		//контрагенты
		$_suppliers=new Lead_SupplierGroup;
		$sup=$_suppliers->GetItemsByIdArr($editing_user['lead_id']);
		$sm1->assign('suppliers', $sup);
		
		
	    
		//поставщики
		$_supplierstz=new TZ_SupplierGroup;
		$sup=$_supplierstz->GetItemsByIdArr($editing_user['id']);
		$sm1->assign('supplierstz', $sup);
		
		
	 	//оборудование (если выбрано)
		$_pl=new PlPosItem;
		$pl=$_pl->GetItemById($editing_user['pl_position_id']);
		if($pl!==false) $editing_user['pl_position_string']=$pl['name'];
		
		//Примечания
		$rg=new TzNotesGroup;
		$sm1->assign('notes',$rg->GetItemsByIdArr($editing_user['id'], 0,0, $editing_user['is_confirmed']==1, $au->user_rights->CheckAccess('w',1033), $au->user_rights->CheckAccess('w',1034), $result['id']));
		$sm1->assign('can_notes',true);
		$sm1->assign('can_notes_edit',$au->user_rights->CheckAccess('w',1032)/*&&($editing_user['is_confirmed_price']==0)*/);
		
		
		$sm1->assign('can_modify', $can_modify);  
		
		//var_dump($lead);
		
		//
		$sm1->assign('can_modify_suppliers', 
			($editing_user['is_not_eq']==1)&&
			
			!in_array($editing_user['status_id'], array(3,27))&&
			
			($lead['status_id']==28)
			
		);  
		
		//создание КП исх.
		$sm1->assign('can_create_kp', ($au->user_rights->CheckAccess('w',1019)||($au->user_rights->CheckAccess('w',1018)&&(($result['id']==$editing_user['manager_id'])||($result['id']==$editing_user['created_id'])))));
		
		 
		
		  
		
		
		$_dsi=new docstatusitem; $dsi=$_dsi->GetItemById($editing_user['status_id']);
		$editing_user['status_name']=$dsi['name'];
		$sm1->assign('bill', $editing_user);
		
		
		
		
		
		//реестр прикрепленных файлов
		$folder_id=0;
			 
			  $decorator=new DBDecorator;
			  
			  $decorator->AddEntry(new SqlOrdEntry('pdate',SqlOrdEntry::DESC));
			 
			$decorator->AddEntry(new UriEntry('id',$id));
			  
			  $decorator->AddEntry(new SqlEntry('folder_id',$folder_id, SqlEntry::E));
			 $decorator->AddEntry(new UriEntry('folder_id',$folder_id));
		
			  $navi_dec=new DBDecorator;
			  $navi_dec->AddEntry(new UriEntry('action',1));
			  
			  
			  
			  
			  $ffg=new TZFileGroup(1,  $id,  new FileDocFolderItem(1,  $id, new TZFileItem(1)));;
			  
			  $filetext=$ffg->ShowFiles('doc_file/incard_list.html', $decorator,0,10000,'ed_tz.php', 'tz_file.html', 'swfupl-js/tz_files.php',   
			  $can_modify,    
			 $can_modify, 
			 false , 
			  $folder_id, 
			  false, 
			false , 
			 false, 
			 false ,    
			  '',  
			  
			 false,  
			   $result, 
			   $navi_dec, 'file_' 
			   );
		
		
		$sm1->assign('files', $filetext);
		$user_form=$sm1->fetch('tz/edit_tz'.$print_add.'.html');
		  
  
		 
		 
		 
		
		
/******************************************************************************************************/
	 	//покажем журнал событий по позиции
		if($au->user_rights->CheckAccess('w',3)){
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
		
			$decorator->AddEntry(new SqlEntry('object_id',NULL, SqlEntry::IN_VALUES, NULL, array(1005,
1006,
1007,
1008,
1009,
1010,
1011,
1012,
1013,
1014,
1015,
1016,
1028,
1029,
1030,
1031,
1032,
1033,
1034
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
			$sm->assign('has_ship', ($editing_user['is_confirmed_shipping']==1));
			
			$llg=$log->ShowLog('syslog/log_doc.html',$decorator,$from,$to_page,'ed_tz.php',true,true,true);
			
			$sm->assign('syslog',$llg);		
				
		} 
		
		
		
		
/****************************************************************************************************/
//реестры связанных документов
		
		
		
		 
		 
/*****************************************************************************************************/
//блок КП вход по Лиду
		$sm->assign('has_kpins', $au->user_rights->CheckAccess('w',1017));
		 
		 
		 //вывести список КП вход
		 $_tzs=new KpIn_Group;
		 
		 
		$_tzs->SetAuthResult($result);
		$_tzs->SetPageName('ed_tz.php');
 
 
		$prefix='_kp_ins';
		
		if(isset($_GET['from'.$prefix])) $from=abs((int)$_GET['from'.$prefix]);
		  else $from=0;
		if(isset($_GET['to_page'.$prefix])) $to_page=abs((int)$_GET['to_page'.$prefix]);
		  else $to_page=ITEMS_PER_PAGE;
		  
		$decorator=new DBDecorator;
		
		
		$decorator->AddEntry(new UriEntry('action',1));
		$decorator->AddEntry(new UriEntry('id',$id));
		$decorator->AddEntry(new UriEntry('tz_id',$id));
		$decorator->AddEntry(new SqlEntry('p.tz_id',$id, SqlEntry::E));
		
		$decorator->AddEntry(new SqlEntry('p.kind_id',0, SqlEntry::E));
		$decorator->AddEntry(new UriEntry('kind_id',0));
		 
		 //контроль видимости
		if(!$au->user_rights->CheckAccess('w',1020)){
			$decorator->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::IN_VALUES, NULL,$_tzs->GetAvailableKpInIds($result['id'])));	
		}
	 	
	 
		 
		 if(isset($_GET['code'.$prefix])&&(strlen($_GET['code'.$prefix])>0)){
		  $decorator->AddEntry(new SqlEntry('p.code',SecStr($_GET['code'.$prefix]), SqlEntry::LIKE));
		  $decorator->AddEntry(new UriEntry('code',$_GET['code'.$prefix]));
		}
		
		
		 
		
		//фильтр по контрагенту
		if(isset($_GET['supplier_name'.$prefix])&&(strlen($_GET['supplier_name'.$prefix])>0)){
			$names=explode(';', trim($_GET['supplier_name'.$prefix]));
			foreach($names as $k=>$v) $names[$k]=SecStr($v);
			
			$decorator->AddEntry(new SqlEntry('sup.full_name', NULL, SqlEntry::LIKE_SET, NULL,$names));	
			
			$decorator->AddEntry(new UriEntry('supplier_name',$_GET['supplier_name'.$prefix]));
		}
		
		//фильтр по поставщику
		if(isset($_GET['suppliertz_name'.$prefix])&&(strlen($_GET['suppliertz_name'.$prefix])>0)){
			$names=explode(';', trim($_GET['suppliertz_name'.$prefix]));
			foreach($names as $k=>$v) $names[$k]=SecStr($v);
			
			$decorator->AddEntry(new SqlEntry('sup1.full_name', NULL, SqlEntry::LIKE_SET, NULL,$names));	
			
			$decorator->AddEntry(new UriEntry('suppliertz_name',$_GET['suppliertz_name'.$prefix]));
		}
	 	 
	
		 
		if(!isset($_GET['pdate1'.$prefix])){
	
				$_given_pdate1=DateFromdmY('01.01.2015'); //DateFromdmY(date("d.m.Y"))-3*60*60*24*30;
				$given_pdate1=date("d.m.Y", $_given_pdate1);//"01.01.2006";
				
			
		}else{
			 $given_pdate1 = $_GET['pdate1'.$prefix];
			 $_given_pdate1= DateFromdmY($_GET['pdate1'.$prefix]);
		}
		
		
		
		if(!isset($_GET['pdate2'.$prefix])){
				
				$_given_pdate2=DateFromdmY(date("d.m.Y"))+30*60*60*24;
				$given_pdate2=date("d.m.Y", $_given_pdate2);//"01.01.2006";	
				
				//$decorator->AddEntry(new UriEntry('pdate2',$given_pdate2));
		}else{
			 $given_pdate2 = $_GET['pdate2'.$prefix];
			  $_given_pdate2= DateFromdmY($_GET['pdate2'.$prefix]);
		}
		
		
		
		if(isset($_GET['pdate1'.$prefix])&&isset($_GET['pdate2'.$prefix])&&($_GET['pdate2'.$prefix]!="")&&($_GET['pdate2'.$prefix]!="-")&&($_GET['pdate1'.$prefix]!="")&&($_GET['pdate1'.$prefix]!="-")){
			
			$decorator->AddEntry(new UriEntry('pdate1',$given_pdate1));
			$decorator->AddEntry(new UriEntry('pdate2',$given_pdate2));
			$decorator->AddEntry(new SqlEntry('p.given_pdate',  DateFromdmY($given_pdate1), SqlEntry::BETWEEN, DateFromdmY($given_pdate2)));
		}else{
					$decorator->AddEntry(new UriEntry('pdate1',''));
				$decorator->AddEntry(new UriEntry('pdate1',''));
		} 
		
	 	
		 
		  
		
		//блок фильтров статуса
		
		$status_ids=array();
	  	$cou_stat=0;   
		if(isset($_GET[$prefix.'statuses'])&&is_array($_GET[$prefix.'statuses'])) $cou_stat=count($_GET[$prefix.'statuses']);
		if($cou_stat>0){
		  //есть гет-запросы	
		  $status_ids=$_GET[$prefix.'statuses'];
		  
	  	}else{
		  $cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^'.$prefix.'kp_in_'.$prefix.'status_id_', $k)) $cou_stat++;
		  
		  if($cou_stat>0){
			  //есть кукисы
			  foreach($_COOKIE as $k=>$v) if(eregi('^'.$prefix.'kp_in_'.$prefix.'status_id_', $k)) $status_ids[]=(int)eregi_replace('^'.$prefix.'kp_in_'.$prefix.'status_id_','',$k);
		  }else{
			  //ничего нет - выбираем ВСЕ!	
			  $decorator->AddEntry(new UriEntry('all_statuses',1));
		  }
	  }
	   
	     if(count($status_ids)>0){
			  $of_zero=true; foreach($status_ids as $k=>$v) if($v>0) $of_zero=$of_zero&&false;
			  
			  if($of_zero){
				  //ничего нет - выбираем ВСЕ!	
				  $decorator->AddEntry(new UriEntry('all_statuses',1));
			  }else{
			  
				  foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry('status_id_'.$v,1));
				  $decorator->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));	
				   foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry($prefix.'statuses[]',$v));
			  }
		  } 
		
		
		
		if(isset($_GET['manager_name'.$prefix])&&(strlen($_GET['manager_name'.$prefix])>0)){
			$decorator->AddEntry(new SqlEntry('u.name_s',SecStr($_GET['manager_name'.$prefix]), SqlEntry::LIKE));
			$decorator->AddEntry(new UriEntry('manager_name',$_GET['manager_name'.$prefix]));
		} 
		
		  
		$decorator->AddEntry(new UriEntry('pdate',$pdate));
		
		
		
		
		if(!isset($_GET['sortmode'.$prefix])){
			$sortmode=-1;	
		}else{
			$sortmode=((int)$_GET['sortmode'.$prefix]);
		}
		
			
			
		switch($sortmode){
			case 0:
				$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::DESC));
			break;
			case 1:
				$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::ASC));
			break;
		 	case 2:
				$decorator->AddEntry(new SqlOrdEntry('p.given_pdate',SqlOrdEntry::DESC));
			break;	
			case 3:
				$decorator->AddEntry(new SqlOrdEntry('p.given_pdate',SqlOrdEntry::ASC));
			break; 
			
			case 4:
				$decorator->AddEntry(new SqlOrdEntry('lead.code',SqlOrdEntry::DESC));
			break;	
			case 5:
				$decorator->AddEntry(new SqlOrdEntry('lead.code',SqlOrdEntry::ASC));
			break;
			case 6:
				$decorator->AddEntry(new SqlOrdEntry('sup.full_name',SqlOrdEntry::DESC));
			break;	
			case 7:
				$decorator->AddEntry(new SqlOrdEntry('sup.full_name',SqlOrdEntry::ASC));
			break;
			case 8:
				$decorator->AddEntry(new SqlOrdEntry('u.name_s',SqlOrdEntry::DESC));
				
			break;	
			case 9:
				$decorator->AddEntry(new SqlOrdEntry('u.name_s',SqlOrdEntry::ASC));
				
			break;
			 
			case 10:
				$decorator->AddEntry(new SqlOrdEntry('p.status_id',SqlOrdEntry::DESC));
				
			break;	
			case 11:
				$decorator->AddEntry(new SqlOrdEntry('p.status_id',SqlOrdEntry::ASC));
				
			break;
			
			
			 
			 
			
			default:
					
				$decorator->AddEntry(new SqlOrdEntry('s.weight',SqlOrdEntry::DESC));
				
				$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::DESC));
				 
			break;	
			
		}
		 
		$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
		
		
	
		 
		
		$kpins=$_tzs->ShowPos(
				
			'kp_in/table.html',  //0
			 $decorator, //1
			 /* ($au->user_rights->CheckAccess('w',1019)||($au->user_rights->CheckAccess('w',1018)&&(($result['id']==$editing_user['manager_id'])||($result['id']==$editing_user['created_id']))))&&($editing_user['is_confirmed']==1)*/ false, //2
			  $au->user_rights->CheckAccess('w',1021), //3
			  $from, //4
			  $to_page, //5
			  true, //6
			  false,  //7
			  $au->user_rights->CheckAccess('w',1024), //8
			  $au->user_rights->CheckAccess('w',1025),  //9
			  $au->user_rights->CheckAccess('w',1022), //10
			  $au->user_rights->CheckAccess('w',1023), //11
			  $au->user_rights->CheckAccess('w',1026), //12
			  
			  
				$prefix, //13
			  0
			 );

 
		$sm->assign('kp_ins', $kpins);		 




/*****************************************************************************************************/
//блок КП исход по Лиду, не из ПЛ
		$sm->assign('has_kpins', $au->user_rights->CheckAccess('w',1017));
		
		
		
		  
		 $_tzs=new KpIn_Out_Group;
		 
		 
		$_tzs->SetAuthResult($result);
		$_tzs->SetPageName('ed_tz.php');
 
 
		$prefix='_kp_outs';
		
		if(isset($_GET['from'.$prefix])) $from=abs((int)$_GET['from'.$prefix]);
		  else $from=0;
		if(isset($_GET['to_page'.$prefix])) $to_page=abs((int)$_GET['to_page'.$prefix]);
		  else $to_page=ITEMS_PER_PAGE;
		  
		$decorator=new DBDecorator;
		
		
		$decorator->AddEntry(new UriEntry('action',1));
		$decorator->AddEntry(new UriEntry('id',$id));
		$decorator->AddEntry(new UriEntry('tz_id',$id));
		$decorator->AddEntry(new SqlEntry('p.tz_id',$id, SqlEntry::E));
		
		$decorator->AddEntry(new SqlEntry('p.kind_id',1, SqlEntry::E));
		$decorator->AddEntry(new UriEntry('kind_id',1));
		 
		 //контроль видимости
		if(!$au->user_rights->CheckAccess('w',1020)){
			$decorator->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::IN_VALUES, NULL,$_tzs->GetAvailableKpInIds($result['id'])));	
		}
	 	
	 
		 
		 if(isset($_GET['code'.$prefix])&&(strlen($_GET['code'.$prefix])>0)){
		  $decorator->AddEntry(new SqlEntry('p.code',SecStr($_GET['code'.$prefix]), SqlEntry::LIKE));
		  $decorator->AddEntry(new UriEntry('code',$_GET['code'.$prefix]));
		}
		
		
		 
		
		//фильтр по контрагенту
		if(isset($_GET['supplier_name'.$prefix])&&(strlen($_GET['supplier_name'.$prefix])>0)){
			$names=explode(';', trim($_GET['supplier_name'.$prefix]));
			foreach($names as $k=>$v) $names[$k]=SecStr($v);
			
			$decorator->AddEntry(new SqlEntry('sup.full_name', NULL, SqlEntry::LIKE_SET, NULL,$names));	
			
			$decorator->AddEntry(new UriEntry('supplier_name',$_GET['supplier_name'.$prefix]));
		}
		
		
	 	 //фильтр по поставщику
		if(isset($_GET['suppliertz_name'.$prefix])&&(strlen($_GET['suppliertz_name'.$prefix])>0)){
			$names=explode(';', trim($_GET['suppliertz_name'.$prefix]));
			foreach($names as $k=>$v) $names[$k]=SecStr($v);
			
			$decorator->AddEntry(new SqlEntry('sup1.full_name', NULL, SqlEntry::LIKE_SET, NULL,$names));	
			
			$decorator->AddEntry(new UriEntry('suppliertz_name',$_GET['suppliertz_name'.$prefix]));
		}
	
		 
		if(!isset($_GET['pdate1'.$prefix])){
	
				$_given_pdate1=DateFromdmY('01.01.2015'); //DateFromdmY(date("d.m.Y"))-3*60*60*24*30;
				$given_pdate1=date("d.m.Y", $_given_pdate1);//"01.01.2006";
				
			
		}else{
			 $given_pdate1 = $_GET['pdate1'.$prefix];
			 $_given_pdate1= DateFromdmY($_GET['pdate1'.$prefix]);
		}
		
		
		
		if(!isset($_GET['pdate2'.$prefix])){
				
				$_given_pdate2=DateFromdmY(date("d.m.Y"))+30*60*60*24;
				$given_pdate2=date("d.m.Y", $_given_pdate2);//"01.01.2006";	
				
				//$decorator->AddEntry(new UriEntry('pdate2',$given_pdate2));
		}else{
			 $given_pdate2 = $_GET['pdate2'.$prefix];
			  $_given_pdate2= DateFromdmY($_GET['pdate2'.$prefix]);
		}
		
		
		
		if(isset($_GET['pdate1'.$prefix])&&isset($_GET['pdate2'.$prefix])&&($_GET['pdate2'.$prefix]!="")&&($_GET['pdate2'.$prefix]!="-")&&($_GET['pdate1'.$prefix]!="")&&($_GET['pdate1'.$prefix]!="-")){
			
			$decorator->AddEntry(new UriEntry('pdate1',$given_pdate1));
			$decorator->AddEntry(new UriEntry('pdate2',$given_pdate2));
			$decorator->AddEntry(new SqlEntry('p.given_pdate',  DateFromdmY($given_pdate1), SqlEntry::BETWEEN, DateFromdmY($given_pdate2)));
		}else{
					$decorator->AddEntry(new UriEntry('pdate1',''));
				$decorator->AddEntry(new UriEntry('pdate1',''));
		} 
		
	 	
		 
		  
		
		//блок фильтров статуса
		
		$status_ids=array();
	  	$cou_stat=0;   
		if(isset($_GET[$prefix.'statuses'])&&is_array($_GET[$prefix.'statuses'])) $cou_stat=count($_GET[$prefix.'statuses']);
		if($cou_stat>0){
		  //есть гет-запросы	
		  $status_ids=$_GET[$prefix.'statuses'];
		  
	  	}else{
		  $cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^'.$prefix.'kp_in_'.$prefix.'status_id_', $k)) $cou_stat++;
		  
		  if($cou_stat>0){
			  //есть кукисы
			  foreach($_COOKIE as $k=>$v) if(eregi('^'.$prefix.'kp_in_'.$prefix.'status_id_', $k)) $status_ids[]=(int)eregi_replace('^'.$prefix.'kp_in_'.$prefix.'status_id_','',$k);
		  }else{
			  //ничего нет - выбираем ВСЕ!	
			  $decorator->AddEntry(new UriEntry('all_statuses',1));
		  }
	  }
	   
	     if(count($status_ids)>0){
			  $of_zero=true; foreach($status_ids as $k=>$v) if($v>0) $of_zero=$of_zero&&false;
			  
			  if($of_zero){
				  //ничего нет - выбираем ВСЕ!	
				  $decorator->AddEntry(new UriEntry('all_statuses',1));
			  }else{
			  
				  foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry('status_id_'.$v,1));
				  $decorator->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));	
				   foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry($prefix.'statuses[]',$v));
			  }
		  } 
		
		
		
		if(isset($_GET['manager_name'.$prefix])&&(strlen($_GET['manager_name'.$prefix])>0)){
			$decorator->AddEntry(new SqlEntry('u.name_s',SecStr($_GET['manager_name'.$prefix]), SqlEntry::LIKE));
			$decorator->AddEntry(new UriEntry('manager_name',$_GET['manager_name'.$prefix]));
		} 
		
		  
		$decorator->AddEntry(new UriEntry('pdate',$pdate));
		
		
		
		
		if(!isset($_GET['sortmode'.$prefix])){
			$sortmode=-1;	
		}else{
			$sortmode=((int)$_GET['sortmode'.$prefix]);
		}
		
			
			
		switch($sortmode){
			case 0:
				$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::DESC));
			break;
			case 1:
				$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::ASC));
			break;
		 	case 2:
				$decorator->AddEntry(new SqlOrdEntry('p.given_pdate',SqlOrdEntry::DESC));
			break;	
			case 3:
				$decorator->AddEntry(new SqlOrdEntry('p.given_pdate',SqlOrdEntry::ASC));
			break; 
			
			case 4:
				$decorator->AddEntry(new SqlOrdEntry('lead.code',SqlOrdEntry::DESC));
			break;	
			case 5:
				$decorator->AddEntry(new SqlOrdEntry('lead.code',SqlOrdEntry::ASC));
			break;
			case 6:
				$decorator->AddEntry(new SqlOrdEntry('sup.full_name',SqlOrdEntry::DESC));
			break;	
			case 7:
				$decorator->AddEntry(new SqlOrdEntry('sup.full_name',SqlOrdEntry::ASC));
			break;
			case 8:
				$decorator->AddEntry(new SqlOrdEntry('u.name_s',SqlOrdEntry::DESC));
				
			break;	
			case 9:
				$decorator->AddEntry(new SqlOrdEntry('u.name_s',SqlOrdEntry::ASC));
				
			break;
			 
			case 10:
				$decorator->AddEntry(new SqlOrdEntry('p.status_id',SqlOrdEntry::DESC));
				
			break;	
			case 11:
				$decorator->AddEntry(new SqlOrdEntry('p.status_id',SqlOrdEntry::ASC));
				
			break;
			
			
			 
			 
			
			default:
					
				$decorator->AddEntry(new SqlOrdEntry('s.weight',SqlOrdEntry::DESC));
				
				$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::DESC));
				 
			break;	
			
		}
		 
		$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
		
		
	
		 
		
		$kpins=$_tzs->ShowPos(
				
			'kp_in/table.html',  //0
			 $decorator, //1
			  /*($au->user_rights->CheckAccess('w',1019)||($au->user_rights->CheckAccess('w',1018)&&(($result['id']==$editing_user['manager_id'])||($result['id']==$editing_user['created_id']))))&&($editing_user['is_confirmed']==1)*/ false, //2
			  $au->user_rights->CheckAccess('w',1021), //3
			  $from, //4
			  $to_page, //5
			  true, //6
			  false,  //7
			  $au->user_rights->CheckAccess('w',1024), //8
			  $au->user_rights->CheckAccess('w',1025),  //9
			  $au->user_rights->CheckAccess('w',1022), //10
			  $au->user_rights->CheckAccess('w',1023), //11
			  $au->user_rights->CheckAccess('w',1026), //12
			  
			  
				$prefix, //13
			  1
			 );

 
		$sm->assign('kp_outs', $kpins);		 




/***************************************************************************************************/
//вкладка БДР 
		$prefix='_bdrs';
		
		$_plans=new BDR_Group;
		$_plans->SetAuthResult($result);
		$_plans->SetPageName('ed_tz.php');
 
		
		if(isset($_GET['from'.$prefix])) $from=abs((int)$_GET['from'.$prefix]);
		  else $from=0;
		if(isset($_GET['to_page'.$prefix])) $to_page=abs((int)$_GET['to_page'.$prefix]);
		  else $to_page=ITEMS_PER_PAGE;
		  
		$decorator=new DBDecorator;
		
		
		 $decorator->AddEntry(new SqlEntry('t.tz_id',$id, SqlEntry::E));
		 
		 
		$decorator->AddEntry(new UriEntry('action',1));
		$decorator->AddEntry(new UriEntry('id',$id));
		$decorator->AddEntry(new UriEntry('tz_id',$id));
		 
		
		//контроль видимости
		if(!$au->user_rights->CheckAccess('w',1040)){
			$decorator->AddEntry(new SqlEntry('t.id', NULL, SqlEntry::IN_VALUES, NULL,$_plans->GetAvailableBDRIds($result['id'])));	
		}
	 	
		  
	 
		 
		 if(isset($_GET['code'.$prefix])&&(strlen($_GET['code'.$prefix])>0)){
		  $decorator->AddEntry(new SqlEntry('t.code',SecStr($_GET['code'.$prefix]), SqlEntry::LIKE));
		  $decorator->AddEntry(new UriEntry('code',$_GET['code'.$prefix]));
		}
		
		
		 
		
		//фильтр по контрагенту
		if(isset($_GET['supplier_name'.$prefix])&&(strlen($_GET['supplier_name'.$prefix])>0)){
			$names=explode(';', trim($_GET['supplier_name'.$prefix]));
			foreach($names as $k=>$v) $names[$k]=SecStr($v);
			
			$decorator->AddEntry(new SqlEntry('sup.full_name', NULL, SqlEntry::LIKE_SET, NULL,$names));	
			
			$decorator->AddEntry(new UriEntry('supplier_name',$_GET['supplier_name'.$prefix]));
		}
		
		
	 	 
	
		 
		if(!isset($_GET['pdate1'.$prefix])){
	
				$_given_pdate1=DateFromdmY('01.01.2015'); //DateFromdmY(date("d.m.Y"))-3*60*60*24*30;
				$given_pdate1=date("d.m.Y", $_given_pdate1);//"01.01.2006";
				
			
		}else{
			 $given_pdate1 = $_GET['pdate1'.$prefix];
			 $_given_pdate1= DateFromdmY($_GET['pdate1'.$prefix]);
		}
		
		
		
		if(!isset($_GET['pdate2'.$prefix])){
				
				$_given_pdate2=DateFromdmY(date("d.m.Y"))+30*60*60*24;
				$given_pdate2=date("d.m.Y", $_given_pdate2);//"01.01.2006";	
				
				//$decorator->AddEntry(new UriEntry('pdate2',$given_pdate2));
		}else{
			 $given_pdate2 = $_GET['pdate2'.$prefix];
			  $_given_pdate2= DateFromdmY($_GET['pdate2'.$prefix]);
		}
		
		
		
		if(isset($_GET['pdate1'.$prefix])&&isset($_GET['pdate2'.$prefix])&&($_GET['pdate2'.$prefix]!="")&&($_GET['pdate2'.$prefix]!="-")&&($_GET['pdate1'.$prefix]!="")&&($_GET['pdate1'.$prefix]!="-")){
			
			$decorator->AddEntry(new UriEntry('pdate1',$given_pdate1));
			$decorator->AddEntry(new UriEntry('pdate2',$given_pdate2));
			$decorator->AddEntry(new SqlEntry('t.pdate',  DateFromdmY($given_pdate1), SqlEntry::BETWEEN, DateFromdmY($given_pdate2)));
		}else{
					$decorator->AddEntry(new UriEntry('pdate1',''));
				$decorator->AddEntry(new UriEntry('pdate1',''));
		} 
		
	 	
		 
		  
		
		//блок фильтров статуса
		
		$status_ids=array();
	  	$cou_stat=0;   
		if(isset($_GET[$prefix.'statuses'])&&is_array($_GET[$prefix.'statuses'])) $cou_stat=count($_GET[$prefix.'statuses']);
		if($cou_stat>0){
		  //есть гет-запросы	
		  $status_ids=$_GET[$prefix.'statuses'];
		  
	  	}else{
		  $cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^'.$prefix.'bdr_'.$prefix.'status_id_', $k)) $cou_stat++;
		  
		  if($cou_stat>0){
			  //есть кукисы
			  foreach($_COOKIE as $k=>$v) if(eregi('^'.$prefix.'bdr_'.$prefix.'status_id_', $k)) $status_ids[]=(int)eregi_replace('^'.$prefix.'bdr_'.$prefix.'status_id_','',$k);
		  }else{
			  //ничего нет - выбираем ВСЕ!	
			  $decorator->AddEntry(new UriEntry('all_statuses',1));
		  }
	  }
	   
	     if(count($status_ids)>0){
			  $of_zero=true; foreach($status_ids as $k=>$v) if($v>0) $of_zero=$of_zero&&false;
			  
			  if($of_zero){
				  //ничего нет - выбираем ВСЕ!	
				  $decorator->AddEntry(new UriEntry('all_statuses',1));
			  }else{
			  
				  foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry('status_id_'.$v,1));
				  $decorator->AddEntry(new SqlEntry('t.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));	
				   foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry($prefix.'statuses[]',$v));
			  }
		  } 
		
		
		
		if(isset($_GET['manager_name'.$prefix])&&(strlen($_GET['manager_name'.$prefix])>0)){
			$decorator->AddEntry(new SqlEntry('u.name_s',SecStr($_GET['manager_name'.$prefix]), SqlEntry::LIKE));
			$decorator->AddEntry(new UriEntry('manager_name',$_GET['manager_name'.$prefix]));
		} 
		
		  
		$decorator->AddEntry(new UriEntry('pdate',$pdate));
		
		
		
		
		if(!isset($_GET['sortmode'.$prefix])){
			$sortmode=-1;	
		}else{
			$sortmode=((int)$_GET['sortmode'.$prefix]);
		}
		
			
			
		switch($sortmode){
			case 0:
				$decorator->AddEntry(new SqlOrdEntry('t.code',SqlOrdEntry::DESC));
			break;
			case 1:
				$decorator->AddEntry(new SqlOrdEntry('t.code',SqlOrdEntry::ASC));
			break;
		 	case 2:
				$decorator->AddEntry(new SqlOrdEntry('t.pdate',SqlOrdEntry::DESC));
			break;	
			case 3:
				$decorator->AddEntry(new SqlOrdEntry('t.pdate',SqlOrdEntry::ASC));
			break; 
			
			case 4:
				$decorator->AddEntry(new SqlOrdEntry('sup.full_name',SqlOrdEntry::DESC));
			break;	
			case 5:
				$decorator->AddEntry(new SqlOrdEntry('sup.full_name',SqlOrdEntry::ASC));
			break;
			case 6:
				$decorator->AddEntry(new SqlOrdEntry('u.name_s',SqlOrdEntry::DESC));
			break;	
			case 7:
				$decorator->AddEntry(new SqlOrdEntry('u.name_s',SqlOrdEntry::ASC));
			break;
			case 8:
				$decorator->AddEntry(new SqlOrdEntry('lead.code',SqlOrdEntry::DESC));
				
			break;	
			case 9:
				$decorator->AddEntry(new SqlOrdEntry('lead.code',SqlOrdEntry::ASC));
				
			break;
			 
			case 10:
				$decorator->AddEntry(new SqlOrdEntry('t.status_id',SqlOrdEntry::DESC));
				
			break;	
			case 11:
				$decorator->AddEntry(new SqlOrdEntry('t.status_id',SqlOrdEntry::ASC));
				
			break;
			
		
		
			 
			
			default:
					
				$decorator->AddEntry(new SqlOrdEntry('s.weight',SqlOrdEntry::DESC));
				
				$decorator->AddEntry(new SqlOrdEntry('t.code',SqlOrdEntry::DESC));
				 
			break;	
			
		}
		 
		$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
		
		
	
		 
		
		$bdrs=$_plans->ShowPos(
				
			'bdr/table.html',  //0
			 $decorator, //1
			  false, //2
			  $au->user_rights->CheckAccess('w',1041), //3
			  $from, //4
			  $to_page, //5
			  true, //6
			  false,  //7
			  $au->user_rights->CheckAccess('w',1044), //8
			  $au->user_rights->CheckAccess('w',1045),  //9
			  $au->user_rights->CheckAccess('w',1042), //10
			  $au->user_rights->CheckAccess('w',1043), //11
			  $au->user_rights->CheckAccess('w',1050), //12
			   $au->user_rights->CheckAccess('w',1051), //13
			    $au->user_rights->CheckAccess('w',1046), //14
			  
			  
			$prefix //15
			 );

 
		
		$sm->assign('bdrs', $bdrs);
		  
		$sm->assign('has_bdrs', $au->user_rights->CheckAccess('w',1038) );

 	
		
		
		//видимость вкладок кп, переводы, бдр/бддс
		$kp_not_by_pl=($editing_user['force_eq_in']==0);
		$sm->assign('kp_not_by_pl', $kp_not_by_pl);
		
	}
	
	 
	$sm->assign('from_begin',$from_begin);	
	
	 	 
	
	
	$sm->assign('print_pdate', date("d.m.Y H:i:s"));
	//$username=$result['login'];
	$username=stripslashes($result['name_s']).' '.$username;	
	$sm->assign('print_username',$username);
	
	$sm->assign('users',$user_form);
	$content=$sm->fetch('tz/ed_tz_page'.$print_add.'.html');
	
	
	
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