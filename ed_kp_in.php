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
require_once('classes/kp_in.class.php');
require_once('classes/bdr.class.php');




require_once('classes/kp_in_fileitem.php');
require_once('classes/kp_in_filegroup.php');
 

require_once('classes/supplier_country_group.php');
require_once('classes/supplier_city_item.php');
 
require_once('classes/lead_history_group.php');
require_once('classes/docstatusitem.php');

require_once('classes/lead_history_item.php'); 

require_once('classes/lead_view_item.php');
require_once('classes/kp_in_field_rules.php');
require_once('classes/kp_out_field_rules.php');



$_pch=new PeriodChecker;
$pch_date=$_pch->GetDate();



$smarty = new SmartyAdm;


$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}


$_dem=new KpIn_AbstractItem;

$_plan1=new KpIn_Group;
$available_users=$_plan1->GetAvailableKpInIds($result['id']);

$_plan=new KpIn_Group;


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
	$object_id[]=1018;
	break;
	case 1:
	$object_id[]=1021;
	break;
	case 2:
	$object_id[]=1021;
	break;
	default:
	$object_id[]=1021;
	break;
}

$_editable_status_id=array();
$_editable_status_id[]=1;
 

if(!isset($_GET['kind_id'])){
		if(!isset($_POST['kind_id'])){
			$kind_id=0;
		}else $kind_id=abs((int)$_POST['kind_id']);	
	}else $kind_id=abs((int)$_GET['kind_id']);
	

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
	
	
	 if(!isset($_GET['tz_id'])){
		if(!isset($_POST['tz_id'])){
			header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			include('404.php');
			die();
		}else $tz_id=abs((int)$_POST['tz_id']);	
	}else $tz_id=abs((int)$_GET['tz_id']);
	
	
	 if(!isset($_GET['supplier_id'])){
		if(!isset($_POST['supplier_id'])){
			/*header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			include('404.php');
			die();*/
			$supplier_id=0;
		}else $supplier_id=abs((int)$_POST['supplier_id']);	
	}else $supplier_id=abs((int)$_GET['supplier_id']);
	
	
	 if(!isset($_GET['kp_in_id'])){
		if(!isset($_POST['kp_in_id'])){
			/*header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			include('404.php');
			die();*/
			$kp_in_id=0;
		}else $kp_in_id=abs((int)$_POST['kp_in_id']);	
	}else $kp_in_id=abs((int)$_GET['kp_in_id']);
	
	
	
	$_lead=new Lead_Item;
	$lead=$_lead->GetItemById($lead_id);
	if($lead===false){
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include('404.php');
		die();
	}
	
	$_tz=new Tz_Item;
	$tz=$_tz->GetItemById($tz_id);
	if($tz===false){
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include('404.php');
		die();
	}
	
	
	$_kp_in=new KpIn_Item;
	$kp_in=$_kp_in->GetItemById($kp_in_id);
	 
	 
	 
	
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
	
	
	$_tg=new KpIn_Group;
	
	if(!$au->user_rights->CheckAccess('w',1020)){	
		$available_tenders=$_tg->GetAvailableKpInIds($result['id']);
		$is_shown=in_array($id, $available_tenders);
	
		if(!$is_shown){
			header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			include('404.php');
			die();
		}
	}
	
 
}

$add='';
if(isset($editing_user['kind_id'])){
	if($editing_user['kind_id']==0) $add=' вх.';
	elseif($editing_user['kind_id']==1) $add=' исх.';
}else{
	if($kind_id==0) $add=' вх.';
	elseif($kind_id==1) $add=' исх.';
	
}


$smarty->assign("SITETITLE",'КП'.$add);
 
 //файловый блок

if($action==2){
	if(!$au->user_rights->CheckAccess('w',1021)){
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
	
	$_pfi=new KpInFileItem;
	
	$file=$_pfi->GetItemById($file_id);
	
	if($file!==false){
		$_pfi->Del($file_id);
		
		$log->PutEntry($result['id'],'удалил файл вход/исход КП',NULL, 1021,NULL,'имя файла '.SecStr($file['orig_name']));
	}
	
	header("Location: ed_kp_in.php?action=1&id=".$id.'&folder_id='.$folder_id);
	die();
}




//обработка данных

if(($action==0)&&(isset($_POST['doNew'])||isset($_POST['doNewEdit']))){
	 
	
	
	
	
	$params=array();
	//обычная загрузка прочих параметров
	$params['created_id']=$result['id'];
	$params['pdate']=time();
	
	$params['kind_id']=abs((int)$_POST['kind_id']);;
	
	$params['manager_id']=abs((int)$_POST['manager_id']);
	
	 
	$params['lead_id']=abs((int)$_POST['lead_id']);
	$params['tz_id']=abs((int)$_POST['tz_id']);
	$params['kp_in_id']=abs((int)$_POST['kp_in_id']);
	
	
	$params['description']= SecStr($_POST['description']);
	
	
 	$params['given_pdate']=  DateFromdmY($_POST['given_pdate']) ;
	
	 
	
	$params['status_id']=1;
	
	
	if(($au->user_rights->CheckAccess('w',1015)||($result['department_id']==4))){
			
			//вносим станок, стоимость и прочее...
			if(isset($_POST['eq_type_id'])) $params['eq_type_id']=abs((int)$_POST['eq_type_id']);
			if(isset($_POST['quantity'])) $params['quantity']=abs((int)$_POST['quantity']);
			
			if(isset($_POST['supply_pdate_id'])) $params['supply_pdate_id']=abs((int)$_POST['supply_pdate_id']);
			
			if(isset($_POST['cost'])) $params['cost']=abs((float)$_POST['cost']);
			
			if(isset($_POST['currency_id'])) $params['currency_id']=abs((int)$_POST['currency_id']);
			
			
				
		}
		
	 		
	 
	$_res=new KpIn_Resolver($params['kind_id']);
		
		
	$code=	$_res->instance->Add($params);
	 
	//$code=1;
	//запись в журнале
	if($code>0){
		$log->PutEntry($result['id'],'создал вход/исход КП',NULL,1018,NULL,NULL,$code);	
		
		foreach($params as $k=>$v){
			
		  
				
				$log->PutEntry($result['id'],'создал вход/исход КП',NULL,1018, NULL, 'в поле '.$k.' установлено значение '.$v,$code);		
			 
		}
		
		
		//поставщики
	 
		$_supplier=new SupplierItem;
		$_sg=new KpIn_SupplierGroup;
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
				  'sched_id'=>$code,
				   
				  'supplier_id'=>$supplier_id,
				  'contacts'=>$contacts,
				  'note'=> SecStr($_POST['suppliertz_note_'.$hash])
			  );
			  
		  }
		}
		
		$log_entries=$_sg->AddSuppliers($code, $positions); 
		
		//print_r($log_entries); die();
		
		
		//die();
		//запишем в журнал
		 foreach($log_entries as $k=>$v){
			   $supplier=$_supplier->GetItemById($v['supplier_id']);
			  $opf=$_opf->GetItemById($supplier['opf_id']); 
			 
			  $description=SecStr($supplier['full_name'].' '.$opf['name'].', примечание: '.$v['note']);
			 
			  
			  if($v['action']==0){
				  $log->PutEntry($result['id'],'добавил поставщика по КП вх.',NULL,1018,NULL,$description,$id);	
			  }elseif($v['action']==1){
				  $log->PutEntry($result['id'],'редактировал поставщика по КП вх.',NULL,1018,NULL,$description,$id);
			  }elseif($v['action']==2){
				  $log->PutEntry($result['id'],'удалил поставщика по КП вх.',NULL,1018,NULL,$description,$id);
			  }
			  
		  } 
	 
		
	}
	
	  
	
	
	
	
	  	 
	 
	
	
	 
	//приложим файлы!
	//upload_file_6A83_tmp" value="_ZpaGsu91PI.jpg" 
	$fmi=new KpInFileItem;
	foreach($_POST as $k=>$v){
	  if(eregi("^upload_file_",$k)){
		    $filename=eregi_replace("^upload_file_", '', eregi_replace("_tmp$",".tmp",$k));
		  $fmi->Add(array('bill_id'=>$code, 'filename'=>SecStr(basename($filename)), 'orig_name'=>SecStr($v), 'user_id'=>$result['id'], 'pdate'=>time()));
		  
		   $log->PutEntry($result['id'], 'прикрепил файл к вход/исход КП', NULL, 1018, NULL,'Служебное имя файла: '.SecStr(basename($filename)).' Имя файла: '.SecStr($v),$code);
		   
		   
	  }
	}
	 
	
	  
		  
		  
	if($au->user_rights->CheckAccess('w',1018)&&($_POST['do_confirm']==1)){
	
	
		$_res->instance->Edit($code,array('is_confirmed'=>1, 'user_confirm_id'=>$result['id'], ' 	confirm_pdate'=>time()),true,$result);
					  
		$log->PutEntry($result['id'],'автоматически утвердил заполнение вход/исход КП',NULL,1018, NULL, NULL,$code);	
		
		
	}else{
		$log->PutEntry($result['id'],'отказался от автоматического утверждения заполнения вход/исход КП',NULL,1018, NULL, NULL,$code);	
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
		 header("Location: ed_lead.php?action=1&id=".$_POST['lead_id']);
		die();
	}elseif(isset($_POST['doNewEdit'])){
		  
		header("Location: ed_kp_in.php?action=1&id=".$code.'&from_begin='.$from_begin);
		die();	
		
	}else{
		header("Location: leads.php");
		die();
	}
	 
	
	die();
	


}elseif(($action==1)&&(isset($_POST['doEdit'])||isset($_POST['doEditStay'])
	 ||isset($_POST['to_refuse'])
	
	)){
	 
	
	//редактирование возможно, если позволяет статус
	$_res=new KpIn_Resolver($editing_user['kind_id']);
		
	
	//поля формируем в зависимости от их активности в текущем статусе
 	if($editing_user['kind_id']==0) $_roles=new KpIn_FieldRules($result);
	else  $_roles=new KpInOut_FieldRules($result);  //var_dump($_roles->GetTable());
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
		
		
	 	$params['given_pdate']=   DateFromdmY($_POST['given_pdate']) ;
		
		 
		
	}
	else{
		//кроме основного условия, еще может сработать дополнительное:	
	    
		
 
		
		
	}
//	


	if(($au->user_rights->CheckAccess('w',1015)||($result['department_id']==4))&&($editing_user['fulful_kp']==0)&&($editing_user['kind_id']==0)){
			
			//вносим станок, стоимость и прочее...
			if(isset($_POST['eq_type_id'])) $params['eq_type_id']=abs((int)$_POST['eq_type_id']);
			if(isset($_POST['quantity'])) $params['quantity']=abs((int)$_POST['quantity']);
			
			if(isset($_POST['supply_pdate_id'])) $params['supply_pdate_id']=abs((int)$_POST['supply_pdate_id']);
			
			if(isset($_POST['cost'])) $params['cost']=abs((float)$_POST['cost']);
			
			if(isset($_POST['currency_id'])) $params['currency_id']=abs((int)$_POST['currency_id']);
			
			
				
		}
		
	
	$_res->instance->Edit($id, $params);
	
	 
		
	//$_dem->Edit($id, $params);
	//die();
	//запись в журнале
	//записи в лог. сравнить старые и новые записи
	foreach($params as $k=>$v){
		
		if(addslashes($editing_user[$k])!=$v){
			 
			 
			$log->PutEntry($result['id'],'редактировал вход/исход КП',NULL,1021, NULL, 'в поле '.$k.' установлено значение '.$v,$id);
			
					
		}	
		
	}
	
  	
	//отдельным блоком - проверки соответствия тз кп
	$params1=array();
	 //правка соответствия КП ТЗ - отдел закупок
		if(($au->user_rights->CheckAccess('w',1015)||($result['department_id']==4))&&($editing_user['is_confirmed']==1)){
			
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
			
			if($editing_user['fulful_kp']!=$mode_params['fulful_kp']) foreach($mode_params as $k=>$v) $params1[$k]=$v;
		}
		
		 //правка соответствия КП ТЗ - тех отдел
		if(($au->user_rights->CheckAccess('w',1027)||($result['department_id']==7))&&($editing_user['is_confirmed']==1)){
			
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
			
			if($editing_user['fulful_kp1']!=$mode_params['fulful_kp1']) foreach($mode_params as $k=>$v) $params1[$k]=$v;
		} 
	 
	$_res->instance->Edit($id, $params1, true, $result);
	
	foreach($params1 as $k=>$v){
		
		if(addslashes($editing_user[$k])!=$v){
			 
			if($k=='fulful_kp'){
				if($v==0){
					$log->PutEntry($result['id'],'редактировал вход/исход КП',NULL,1021, NULL, "снял выбор соответствия ТЗ КП - отдел закупок",$id);
				}elseif($v==1){
					$log->PutEntry($result['id'],'редактировал вход/исход КП',NULL,1021, NULL, "установил, что ТЗ соответствует КП - отдел закупок",$id);
				}elseif($v==2){
					$log->PutEntry($result['id'],'редактировал вход/исход КП',NULL,1021, NULL, "установил, что ТЗ не соответствует КП - отдел закупок, несоответствия: ".$params['fulfil_kp_not'],$id);
				}
				
				continue;	
			}
			
			if($k=='fulful_kp1'){
				if($v==0){
					$log->PutEntry($result['id'],'редактировал вход/исход КП',NULL,1021, NULL, "снял выбор соответствия ТЗ КП - технический отдел",$id);
				}elseif($v==1){
					$log->PutEntry($result['id'],'редактировал вход/исход КП',NULL,1021, NULL, "установил, что ТЗ соответствует КП  - технический отдел",$id);
				}elseif($v==2){
					$log->PutEntry($result['id'],'редактировал вход/исход КП',NULL,1021, NULL, "установил, что ТЗ не соответствует КП  - технический отдел, несоответствия: ".$params['fulfil_kp_not1'],$id);
				}
				
				continue;	
			}
			//$log->PutEntry($result['id'],'редактировал вход/исход КП',NULL,1021, NULL, 'в поле '.$k.' установлено значение '.$v,$id);
			
					
		}	
		
	}
	
	if($editing_user['kind_id']==1){
		//утверждение вложения файла КП
		if($editing_user['is_confirmed']==1){
	 		
			 
		  //есть ли файл с датой загрузки более поздней, чем дата 1й галочки БДР
		  //найти БДР
		  $_bdr=new BDR_Item;
		  $bdr=$_bdr->GetItemByFields(array('kp_out_id'=>$id, 'status_id'=>40));
		  //var_dump($bdr);
		  $_files_group=new KpInFileGroup(1,  $id,  new FileDocFolderItem(1,  $id, new KpInFileItem(1)));
		  $has_files=$_files_group->HasFileByPdate($bdr['confirm_pdate']);
			  
		  
		  if($editing_user['is_prepared_kp']==1){
			  
			  
			   
			 	if(!isset($_POST['is_prepared_kp'])&&($_res->instance->DocCanUnconfirmPrepare($id, $rss32))){
				
				$can_confirm_shipping=($au->user_rights->CheckAccess('w',1058)||(/*($result['main_department_id']==1) 
		&&	*/($result['position_id']==36)) );
				if($can_confirm_shipping){	  
					  //echo 'zzzzzzzzzzzz';
					  $_res->instance->Edit($id, array('is_prepared_kp'=>0, 'prepared_kp_user_id'=>$result['id'], 'prepared_kp_pdate'=>time()),true,$result);
					  
					  $log->PutEntry($result['id'],'снял утверждение подготовки, вложения файла КП',NULL,1058, NULL, NULL,$id);	
				}
			}
			 
		  }else{
			   
			  //есть права
			  
			   if(isset($_POST['is_prepared_kp'])&&($_res->instance->DocCanConfirmPrepare($id, $rss32))){
				    $can_confirm_shipping=(($au->user_rights->CheckAccess('w',1057)||(/*($result['main_department_id']==1) 
		&&*/	($result['position_id']==36)) ))&&($bdr!==false)&&$has_files;
					if($can_confirm_shipping){	
					
					 
					 $_res->instance->Edit($id, array('is_prepared_kp'=>1, 'prepared_kp_user_id'=>$result['id'], 'prepared_kp_pdate'=>time()), true,$result);
					  
					  $log->PutEntry($result['id'],'утвердил подготовку, вложение файла КП',NULL,1057, NULL, NULL,$id);	
					}
						  
				}
			    
		  }
		 
		}	
		
	}
	
	
	$_dsi=new DocStatusItem; 
	//обработка выделенных кнопок
 
	
	 if(isset($_POST['to_refuse'])){
		
		if($field_rights['to_refuse']){
			$setted_status_id=34;
			$our_data=array( 'status_id'=>$setted_status_id);
			//занести ПРИЧИНЫ ОТКАЗА
 
			$our_data['fail_reason_id']=abs((int)$_POST['status_change_comment_id']);
			$our_data['fail_reason']=SecStr($_POST['status_change_comment']);
			
			$_fi=new Lead_FailItem;
			$fi=$_fi->GetItemById($our_data['fail_reason_id']);
			
				
			
			$_res->instance->Edit($id,$our_data,true, $result);
			
			
					  
			//внести комментарий
			$_nkp=new KpInNotesItem; 
			$comment=SecStr('Автоматическое примечание: сотрудник '.$result['name_s'].' перевел КП вх. в статус "Отказ", причина: '.$fi['name'].' '.$our_data['fail_reason']);
				
				$_nkp->Add(array(
					'note'=>$comment,
					'pdate'=>time(),
					'user_id'=>$id,
					'posted_user_id'=>0
				));
				
			
			/* 
			$_tsi=new Lead_HistoryItem;
			$_tsi->Add(array(
				'sched_id'=>$id,
				'pdate'=>time(),
				'user_id'=>0,
				'txt'=>SecStr('<div>Автоматический комментарий: сотрудник '.$result['name_s'].' перевел лид в статус "Отказ", причина: '.$fi['name'].' '.$our_data['fail_reason'].'</div>')
			
			));
			
			*/
						  
			 
			$stat=$_dsi->GetItemById($setted_status_id);
			$log->PutEntry($result['id'],'смена статуса КП вх.',NULL,1021,NULL,'установлен статус '.$stat['name'].', причина: '.$fi['name'].' '.$our_data['fail_reason'],$id);
			
			
			 
					
		}		
	}
	 
	 
		//утверждение заполнения
		
		$_res=new KpIn_Resolver($editing_user['kind_id']);
		
		if($editing_user['is_confirmed_done']==0){
		  
		  
		  	
		  if($editing_user['is_confirmed']==1){
			 
			  
			  // 
			  if(($au->user_rights->CheckAccess('w',1023)) ){
				  if((!isset($_POST['is_confirmed']))&&($_res->instance->DocCanUnconfirmPrice($id,$rss32))){
					  
					  
					  $_res->instance->Edit($id,array('is_confirmed'=>0, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,$result);
					  
					  $log->PutEntry($result['id'],'снял утверждение заполнения',NULL,1023, NULL, NULL,$id);	
					  
				  }
			  } 
			  
		  }else{
			  //есть права
			  if($au->user_rights->CheckAccess('w',1022) ){
				  if(isset($_POST['is_confirmed'])&&($_POST['is_confirmed']==1)&&($_res->instance->DocCanConfirmPrice($id,$rss32))){
					  
					  $_res->instance->Edit($id,array('is_confirmed'=>1, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,$result);
					  
					  $log->PutEntry($result['id'],'утвердил заполнение',NULL,1022, NULL, NULL,$id);	
					  
					   
					  //die();
				  }
			  } 
		  }
		}
		
		
		 
	
	
	//die();
	
	//перенаправления
	if(isset($_POST['doEdit'])){
		 
		header("Location: ed_lead.php?action=1&id=".$editing_user['lead_id']);
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
		||isset($_POST['to_refuse'])
	
	){
	 
		header("Location: ed_kp_in.php?action=1&id=".$id.'&from_begin='.$from_begin);
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
	 
	$log->PutEntry($result['id'],'открыл вход/исход КП',NULL,1021, NULL, 'вход/исход КП № '.$editing_user['code'],$id);
	 
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
			
			
			 $_tz=new TZ_Item;
			 $tz=$_tz->GetItemById($tz_id);
			 $sm1->assign('tz', $tz);
			
			
			//поставщики
			$_supplierstz=new TZ_SupplierGroup;
			$sup=$_supplierstz->GetItemsByIdArr($tz_id);
			$sup1=array();
			foreach($sup as $k=>$v){
				if($supplier_id==$v['id']){
					$sup1[]=$v;
				}
			}
			
			$sm1->assign('supplierstz', $sup1); 
		
		
	 
			 	
		} 
		
		 $sm1->assign('lead_id', $lead_id);
		 $sm1->assign('tz_id', $tz_id); 
		 
		  $sm1->assign('kp_in_id', $kp_in_id); 
		  
	   if($kind_id==0){
	   	//ТИП ОБОРУДОВАНИЯ
			
			$_eqs=new Tender_EqTypeGroup;
			$eqs=$_eqs->GetItemsArr();
			$_ids=array(); $_vals=array();
			$_ids[]=0; $_vals[]='-выберите-';
			foreach($eqs as $k=>$v){
				$_ids[]=$v['id'];
				$_vals[]=$v['name'];
			}
			$sm1->assign('eq_ids', $_ids); $sm1->assign('eq_vals', $_vals);
			 
		
			$_tki=new Tender_EqTypeItem;
			 
				
			$eq_type_id=$lead['eq_type_id'];
			$sm1->assign('eq_type_id', $eq_type_id);
			 
			$tki=$_tki->GetItemById($eq_type_id);
			$eq_name=$tki['name'];   
			
			$sm1->assign('opfs_total', $eqs);
			
			
			
			//валюты
			$_curr=new PlCurrGroup;
			$currs=array(); $currs[]=array('id'=>0, 'signature'=>'-выберите-');
			$currs1= $_curr->GetItemsArr(0); foreach($currs1 as $k=>$v) $currs[]=$v;
			$sm1->assign('currs', $currs);
				
			
			//сроки поставки
			 
			$_ksm=new KpInSupplyPdateGroup;
			$ksm=$_ksm->GetItemsByIdArr(1);
			$_ids=array(0); $_vals=array('-выберите-');
			foreach($ksm as $k=>$v){
				$_ids[]=$v['id']; $_vals[]=$v['name'];	
			}
			$sm1->assign('supply_pdate_id_ids',$_ids);
			$sm1->assign('supply_pdate_vals',$_vals);
			
			$sm1->assign('can_select_eq', 
			
				($au->user_rights->CheckAccess('w',1054)||($result['department_id']==4))
			);
	   	
	   }elseif($kind_id==1){
		   	//исходящее КП
			 $_kp_in=new KpIn_Item;
			 $kp_in=$_kp_in->GetItemById($kp_in_id);
			 $sm1->assign('kp_in', $kp_in);
			 
			 
			 //поставщики
			 $_supplierstz=new KpIn_SupplierGroup;
			 $sup=$_supplierstz->GetItemsByIdArr($kp_in['id']);
			 $sm1->assign('supplierstz', $sup); 
			 
			 //файлы входящего КП			 
			 //реестр прикрепленных файлов
			  $folder_id=0;
			  
			  $decorator=new DBDecorator;
			  
			  $decorator->AddEntry(new SqlOrdEntry('pdate',SqlOrdEntry::DESC));
			  
			  $decorator->AddEntry(new UriEntry('kp_in_id',$kp_in['id']));
			  
			  $decorator->AddEntry(new SqlEntry('folder_id',$folder_id, SqlEntry::E));
			  $decorator->AddEntry(new UriEntry('folder_id',$folder_id));
			  
			  $navi_dec=new DBDecorator;
			  $navi_dec->AddEntry(new UriEntry('action',0));
			   
			  
			  $ffg=new KpInFileGroup(1,  $kp_in['id'],  new FileDocFolderItem(1,  $kp_in['id'], new KpInFileItem(1)));;
			  
			  $filetext=$ffg->ShowFiles('doc_file/incard_list.html', $decorator,0,10000,'ed_kp_in.php', 'kp_in_file.html', 'swfupl-js/kp_in_files.php',   
			  false,    
			  false, 
			  false , 
			  $folder_id, 
			  false, 
			  false , 
			  false, 
			  false ,    
			  '',  
			  
			  false,  
			  $result, 
			  $navi_dec,  'in_file_' 
			  );
			  
			  
			  $sm1->assign('in_files', $filetext);
			   
	   }
		

		$sm1->assign('now_time',  date('d.m.Y H:i:s')); 
		$sm1->assign('now_date',  date('d.m.Y')); 
		 
	 
	 	
		
		
		//получим список тех, кто может снять утверждение заполнения
		$_usg1=new UsersSGroup;
		$usg1=$_usg1->GetUsersByRightArr('w', 1023);
		$sm1->assign('can_unconfirm_users',$usg1);
		$sm1->assign('can_unconfirm',$au->user_rights->CheckAccess('w',1023));
		
		
		 
		$sm1->assign('session_id', session_id());
		
	  
		
		$sm1->assign('can_confirm', $au->user_rights->CheckAccess('w',1022));
		
		$sm1->assign('can_expand_types', $au->user_rights->CheckAccess('w',939));
		
		
		 
		switch($kind_id){
			case 0:	
				$user_form=$sm1->fetch('kp_in/create_kp_in.html');
			break;
			
			case 1:	
				$user_form=$sm1->fetch('kp_in/create_kp_out.html');
			break;
		}
		
		
	 }elseif($action==1){
		//редактирование позиции
		
		if($print==0) $print_add='';
		else $print_add='_print';
		
		
		//построим доступы
		if($editing_user['kind_id']==0) $_roles=new KpIn_FieldRules($result);
		else $_roles=new KpInOut_FieldRules($result);//var_dump($_roles->GetTable());
		$field_rights=$_roles->GetFields($editing_user, $result['id']);
		$sm1->assign('field_rights', $field_rights);
		
		//var_dump($field_rights);
		
		//интервалы времени в обработке
		if($editing_user['kind_id']==0) $_wg=new KpIn_WorkingGroup;
		else $_wg=new KpOut_WorkingGroup;
		
		$working_time_unf=$_wg->CalcWorkingTime($id, 0, $zz, $times,$is_working);
		$editing_user['times_0']=$times;
		$editing_user['working_time_unf_0']=$working_time_unf;
		$editing_user['0_is_working']=$is_working;
		
		
		
		$working_time_unf=$_wg->CalcWorkingTime($id, 1, $zz, $times,$is_working);
		$editing_user['times_1']=$times;
		$editing_user['working_time_unf_1']=$working_time_unf;
		$editing_user['1_is_working']=$is_working;
		
		
		
		$working_time_unf=$_wg->CalcWorkingTime($id, 2, $zz, $times,$is_working);
		$editing_user['times_2']=$times;
		$editing_user['working_time_unf_2']=$working_time_unf;
		$editing_user['2_is_working']=$is_working;
		
		
		$working_time_unf=$_wg->CalcWorkingTime($id, 4, $zz, $times,$is_working);
		$editing_user['times_4']=$times;
		$editing_user['working_time_unf_4']=$working_time_unf;
		$editing_user['4_is_working']=$is_working;
		 
		
		$working_time_unf=$_wg->CalcTotalWorkingTime($id, $zz, $times,$is_working );
		$editing_user['times_total']=$times;
		$editing_user['working_time_unf_total']=$working_time_unf;
		$editing_user['total_is_working']=$is_working;
		
		
		
		
		//построим доступы
		$can_modify=in_array($editing_user['status_id'],$_editable_status_id);
		
		if($editing_user['kind_id']==0) $can_modify_files=$can_modify;
		elseif($editing_user['kind_id']==1) $can_modify_files=!in_array($editing_user['status_id'],array(3,27)); //если не аннулирован, и не неактуален?
		
		
			 
			 $_lead=new Lead_Item;
			 $lead=$_lead->GetItemById($editing_user['lead_id']);
			 $sm1->assign('lead', $lead);
			 
		 
		 $_tz=new TZ_Item;
		 $tz=$_tz->GetItemById($editing_user['tz_id']);
		 $sm1->assign('tz', $tz);
		
		$_kp_in=new KpIn_Item;
		$kp_in=$_kp_in->GetItemById($editing_user['kp_in_id']);
		$sm1->assign('kp_in', $kp_in);
		
		
		if($editing_user['kind_id']==0){ 
			//поставщики
			$_supplierstz=new KpIn_SupplierGroup;
			$sup=$_supplierstz->GetItemsByIdArr($editing_user['id']);
			$sm1->assign('supplierstz', $sup); 
			
			
			
			//ТИП ОБОРУДОВАНИЯ
			
			$_eqs=new Tender_EqTypeGroup;
			$eqs=$_eqs->GetItemsArr();
			$_ids=array(); $_vals=array();
			$_ids[]=0; $_vals[]='-выберите-';
			foreach($eqs as $k=>$v){
				$_ids[]=$v['id'];
				$_vals[]=$v['name'];
			}
			$sm1->assign('eq_ids', $_ids); $sm1->assign('eq_vals', $_vals);
			 
		
			$_tki=new Tender_EqTypeItem;
			if($editing_user['eq_type_id']==0){
				
				$editing_user['eq_type_id']=$lead['eq_type_id'];
			}
			$tki=$_tki->GetItemById($editing_user['eq_type_id']);
			$editing_user['eq_name']=$tki['name'];   
			
			$sm1->assign('opfs_total', $eqs);
			
			
			
			//валюты
			$_curr=new PlCurrGroup;
			$currs=array(); $currs[]=array('id'=>0, 'signature'=>'-выберите-');
			$currs1= $_curr->GetItemsArr($editing_user['currency_id']); foreach($currs1 as $k=>$v) $currs[]=$v;
			$sm1->assign('currs', $currs);
				
			
			//сроки поставки
			 
			$_ksm=new KpInSupplyPdateGroup;
			$ksm=$_ksm->GetItemsByIdArr(1);
			$_ids=array(0); $_vals=array('-выберите-');
			foreach($ksm as $k=>$v){
				$_ids[]=$v['id']; $_vals[]=$v['name'];	
			}
			$sm1->assign('supply_pdate_id_ids',$_ids);
			$sm1->assign('supply_pdate_vals',$_vals);
			
			$sm1->assign('can_select_eq', 
			
				($au->user_rights->CheckAccess('w',1054)||($result['department_id']==4))&&($editing_user['fulful_kp']==0)
			);
			
			
			//причины отказа
			$_fails=new Lead_FailGroup;
			$fails=$_fails->GetItemsArr();
			$_ids=array(); $_vals=array();
			$_ids[]=0; $_vals[]='-выберите-';
			foreach($fails as $k=>$v){
				$_ids[]=$v['id'];
				$_vals[]=$v['name'];
			}
			$sm1->assign('fail_ids', $_ids); $sm1->assign('fail_vals', $_vals);
			
			 $_tki=new Lead_FailItem;
			$tki=$_tki->GetItemById($editing_user['fail_reason_id']);
			$editing_user['fail_name']=$tki['name'];   
			
			
			
		}elseif($editing_user['kind_id']==1) {
			
			
			//поставщики
			$_supplierstz=new KpIn_SupplierGroup;
			$sup=$_supplierstz->GetItemsByIdArr($editing_user['kp_in_id']);
			$sm1->assign('supplierstz', $sup); 
			
			
			//оборудование (если выбрано)
			/*$_pl=new PlPosItem;
			$pl=$_pl->GetItemById($kp_in['pl_position_id']);
			if($pl!==false) $editing_user['pl_position_string']=$pl['name'];*/
			
			
			$_eqs=new Tender_EqTypeGroup;
			$eqs=$_eqs->GetItemsArr();
			$_ids=array(); $_vals=array();
			$_ids[]=0; $_vals[]='-выберите-';
			foreach($eqs as $k=>$v){
				$_ids[]=$v['id'];
				$_vals[]=$v['name'];
			}
			$sm1->assign('eq_ids', $_ids); $sm1->assign('eq_vals', $_vals);
			 
		
			$_tki=new Tender_EqTypeItem;
			 
			 
			$tki=$_tki->GetItemById($kp_in['eq_type_id']);
			$editing_user['eq_name']=$tki['name'];   
			
			$sm1->assign('opfs_total', $eqs);
			
			
			//валюты
			$_curr=new PlCurrGroup;
			$sm1->assign('currs', $_curr->GetItemsArr($kp_in['currency_id']));
				
			
			//сроки поставки
			 
			$_ksm=new KpInSupplyPdateGroup;
			$ksm=$_ksm->GetItemsByIdArr(1);
			$_ids=array(0); $_vals=array('-выберите-');
			foreach($ksm as $k=>$v){
				$_ids[]=$v['id']; $_vals[]=$v['name'];	
			}
			$sm1->assign('supply_pdate_id_ids',$_ids);
			$sm1->assign('supply_pdate_vals',$_vals);
		}
		
		
		$_res=new KpIn_Resolver($editing_user['kind_id']);
		
		$editing_user['pdate']=date('d.m.Y H:i:s', $editing_user['pdate']);
		
		
		
		
		if($editing_user['given_pdate']!='')  $editing_user['given_pdate']=date('d.m.Y', $editing_user['given_pdate']);
		
		
		   
		
	  
		
		//блок аннулирования
		
		$editing_user['can_annul']=$_res->instance->DocCanAnnul($editing_user['id'],$reason,$editing_user, $result)&&$au->user_rights->CheckAccess('w',1024);
		if(!$au->user_rights->CheckAccess('w',1024)) $reason='недостаточно прав для данной операции';
		$editing_user['can_annul_reason']=$reason;
		
		 
		
		$editing_user['can_restore']=$_res->instance->DocCanRestore($editing_user['id'],$reason,$editing_user)&&$au->user_rights->CheckAccess('w',1025);
			if(!$au->user_rights->CheckAccess('w',1025)) $reason='недостаточно прав для данной операции';
		
		
		
			//получим список тех, кто может снять утверждение заполнения
		$_usg1=new UsersSGroup;
		$usg1=$_usg1->GetUsersByRightArr('w', 1023);
		$sm1->assign('can_unconfirm_users',$usg1);
		$sm1->assign('can_unconfirm',$au->user_rights->CheckAccess('w',1023));
		
		
		 
		
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
		if($editing_user['is_confirmed_done']==0){
			
			  
		  
		  if($editing_user['is_confirmed']==1){
			  if($au->user_rights->CheckAccess('w',1023)){
				  //есть права + сам утвердил
				  $can_confirm_price=true;	
			  }else{
				  $can_confirm_price=false;
			  }
		  }else{
			  //95
			  $can_confirm_price=$au->user_rights->CheckAccess('w',1022)&&in_array($editing_user['status_id'],$_editable_status_id)  ;
		  }
		}
		$sm1->assign('can_confirm',$can_confirm_price);
		
		  
		  
		  
		  
		 //доступ к соотв/не соотв ТЗ КП отдел закупок
		$sm1->assign('can_modify_fulfil', ($au->user_rights->CheckAccess('w',1015)||($result['department_id']==4))&&($editing_user['is_confirmed']==1));  
		//fulful_kp_1_confirmer
		if(($editing_user['fulful_kp']==1)&&($editing_user['fulfil_user_id']!=0)){
			$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['fulfil_user_id']);
			$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.date("d.m.Y H:i:s",$editing_user['fulfil_pdate']);
			
			 
			 
			$sm1->assign('fulful_kp_1_confirmer',$confirmer);
		}
		
		//fulful_kp_2_confirmer
		if(($editing_user['fulful_kp']==2)&&($editing_user['fulfil_user_id']!=0)){
			$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['fulfil_user_id']);
			$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.date("d.m.Y H:i:s",$editing_user['fulfil_pdate']);
			
			 
			 
			$sm1->assign('fulful_kp_2_confirmer',$confirmer);
		}   
		
 
		  
		 //доступ к соотв/не соотв ТЗ КП тех отдел
		$sm1->assign('can_modify_fulfil1', ($au->user_rights->CheckAccess('w',1027)||($result['department_id']==7))&&($editing_user['is_confirmed']==1));  
		//fulful_kp_1_confirmer
		if(($editing_user['fulful_kp1']==1)&&($editing_user['fulfil_user_id1']!=0)){
			$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['fulfil_user_id1']);
			$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.date("d.m.Y H:i:s",$editing_user['fulfil_pdate1']);
			
			 
			 
			$sm1->assign('fulful_kp1_1_confirmer',$confirmer);
		}
		
		//fulful_kp_2_confirmer
		if(($editing_user['fulful_kp1']==2)&&($editing_user['fulfil_user_id1']!=0)){
			$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['fulfil_user_id1']);
			$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.date("d.m.Y H:i:s",$editing_user['fulfil_pdate1']);
			
			 
			 
			$sm1->assign('fulful_kp1_2_confirmer',$confirmer);
		}   
		  
		  
		  
		  
		  
		  
		  
		  
		  
		  
		  
		  
		  
		$reason='';
		
		
		$sm1->assign('can_unconfirm_by_document',(int)$_res->instance->DocCanUnconfirmShip($editing_user['id'],$reason));
		$sm1->assign('can_unconfirm_by_document_reason',$reason);
		
		
		
		//отвеств сотр-к
		$_user_s=new UserSItem;
		$user_s=$_user_s->GetItemById($editing_user['manager_id']);
		$editing_user['manager_string']=$user_s['name_s'];
		
		
		
	 
		
		 
		//контрагенты
		$_suppliers=new Lead_SupplierGroup;
		$sup=$_suppliers->GetItemsByIdArr($editing_user['lead_id']);
		$sm1->assign('suppliers', $sup);
		
		
	    
		//Примечания
		$rg=new KpInNotesGroup;
		$sm1->assign('notes',$rg->GetItemsByIdArr($editing_user['id'], 0,0, $editing_user['is_confirmed']==1, $au->user_rights->CheckAccess('w',1036), $au->user_rights->CheckAccess('w',1037), $result['id']));
		$sm1->assign('can_notes',true);
		$sm1->assign('can_notes_edit',$au->user_rights->CheckAccess('w',1035)/*&&($editing_user['is_confirmed_price']==0)*/);
		
		
	 
		
		
		$sm1->assign('can_modify', $can_modify);  
		   
		//может создать исх кП   
		$sm1->assign('can_create_kp', $au->user_rights->CheckAccess('w',1018));  
		
		//можно создать БДР
		$sm1->assign('can_create_bdr',   $au->user_rights->CheckAccess('w',1039));  
		
		$sm1->assign('can_expand_types', $au->user_rights->CheckAccess('w',939));
		
		//права редактировать состав причин отказа
		$sm1->assign('can_edit_fail_reasons',$au->user_rights->CheckAccess('w',982));
		
		
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
			  
			  
			  
			  
			  $ffg=new KpInFileGroup(1,  $id,  new FileDocFolderItem(1,  $id, new KpInFileItem(1)));;
			  
			  $filetext=$ffg->ShowFiles('doc_file/incard_list.html', $decorator,0,10000,'ed_kp_in.php', 'kp_in_file.html', 'swfupl-js/kp_in_files.php',   
			  $can_modify_files,    
			 $can_modify_files, 
			 false , 
			  $folder_id, 
			  false, 
			false , 
			 false, 
			 false ,    
			  '',  
			  
			 false,  
			   $result, 
			   $navi_dec, 'file_', $kp_files
			   );
		
		
		$sm1->assign('files', $filetext);
		
		if($editing_user['kind_id']==1){
			//файлы входящего КП			 
			 //реестр прикрепленных файлов
			  $folder_id=0;
			  
			  $decorator=new DBDecorator;
			  
			  $decorator->AddEntry(new SqlOrdEntry('pdate',SqlOrdEntry::DESC));
			  
			  $decorator->AddEntry(new UriEntry('id',$id));
			  
			  $decorator->AddEntry(new SqlEntry('folder_id',$folder_id, SqlEntry::E));
			  $decorator->AddEntry(new UriEntry('folder_id',$folder_id));
			  
			  $navi_dec=new DBDecorator;
			  $navi_dec->AddEntry(new UriEntry('action',0));
			   
			  
			  $ffg=new KpInFileGroup(1,  $editing_user['kp_in_id'],  new FileDocFolderItem(1,  $editing_user['kp_in_id'], new KpInFileItem(1)));;
			  
			  $filetext=$ffg->ShowFiles('doc_file/incard_list.html', $decorator,0,10000,'ed_kp_in.php', 'kp_in_file.html', 'swfupl-js/kp_in_files.php',   
			  false,    
			  false, 
			  false , 
			  $folder_id, 
			  false, 
			  false , 
			  false, 
			  false ,    
			  '',  
			  
			  false,  
			  $result, 
			  $navi_dec,  'in_file_' 
			  );
			  
			  
			  $sm1->assign('in_files', $filetext);	
			  
			  
			  //утверждение вложения КП исх.
			  if(($editing_user['is_prepared_kp']==1)&&($editing_user['prepared_kp_user_id']!=0)){
					$confirmer='';
					$_user_temp=new UserSItem;
					$_user_confirmer=$_user_temp->GetItemById($editing_user['prepared_kp_user_id']);
					$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.date("d.m.Y H:i:s",$editing_user['prepared_kp_pdate']);
					
					$sm1->assign('is_prepared_kp_confirmer',$confirmer);
				}
				
				$can_confirm_shipping=false;
				if($editing_user['is_confirmed']==1){
				
				   
				  if($editing_user['is_prepared_kp']==1){
						$can_confirm_shipping=($au->user_rights->CheckAccess('w',1058)||(/*($result['main_department_id']==1) 
		&&*/	($result['position_id']==36)) );
				  }else{
				  //ставим утв	
					  $can_confirm_shipping=($au->user_rights->CheckAccess('w',1057)||(/*($result['main_department_id']==1) 
		&&	*/($result['position_id']==36)) );
				  }
				}
				// + есть галочка утв. цен
				$can_confirm_shipping=$can_confirm_shipping&&($editing_user['is_confirmed']==1);
				// $kp_files
				
				//есть ли файл с датой загрузки более поздней, чем дата 1й галочки БДР
				//найти БДР
				$_bdr=new BDR_Item;
				$bdr=$_bdr->GetItemByFields(array('kp_out_id'=>$id, 'status_id'=>40));
				//var_dump($bdr);
				$_files_group=new KpInFileGroup(1,  $id,  new FileDocFolderItem(1,  $id, new KpInFileItem(1)));
				$has_files=$_files_group->HasFileByPdate($bdr['confirm_pdate']);
				//var_dump($has_files);		
				$can_confirm_shipping= $can_confirm_shipping&&($bdr!==false)&&$has_files;
			  
				
				
				$sm1->assign('can_confirm_prepare_kp',$can_confirm_shipping);
							
		}
		
		
	//	$user_form=$sm1->fetch('kp_in/edit_kp_in'.$print_add.'.html');
		  
  
		 switch($editing_user['kind_id']){
			case 0:	
				$user_form=$sm1->fetch('kp_in/edit_kp_in'.$print_add.'.html');
			break;
			
			case 1:
				
				$user_form=$sm1->fetch('kp_in/edit_kp_out'.$print_add.'.html');
			break;
		 }
		 
		
		
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
		
			$decorator->AddEntry(new SqlEntry('object_id',NULL, SqlEntry::IN_VALUES, NULL, array(1017,
1018,
1019,
1020,
1021,
1022,
1023,
1024,
1025,
1026,
1015,
1027,
1057,
1058,
1035,
1036,
1037,
1054
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
			
			$llg=$log->ShowLog('syslog/log_doc.html',$decorator,$from,$to_page,'ed_kp_in.php',true,true,true);
			
			$sm->assign('syslog',$llg);		
				
		} 
		
		
/*****************************************************************************************************/
//вкладки связанных документов
		//if($editing_user['		
		
/*****************************************************************************************************/
//блок КП исход по Лиду, не из ПЛ
		$sm->assign('has_kp_outs', $au->user_rights->CheckAccess('w',1017)&&($editing_user['kind_id']==0));
		
		
		
		  
		 $_tzs=new KpIn_Out_Group;
		 
		 
		$_tzs->SetAuthResult($result);
		$_tzs->SetPageName('ed_kp_in.php');
 
 
		$prefix='_kp_outs';
		
		if(isset($_GET['from'.$prefix])) $from=abs((int)$_GET['from'.$prefix]);
		  else $from=0;
		if(isset($_GET['to_page'.$prefix])) $to_page=abs((int)$_GET['to_page'.$prefix]);
		  else $to_page=ITEMS_PER_PAGE;
		  
		$decorator=new DBDecorator;
		
		
		$decorator->AddEntry(new UriEntry('action',1));
		$decorator->AddEntry(new UriEntry('id',$id));
		$decorator->AddEntry(new UriEntry('kp_in_id',$id));
		$decorator->AddEntry(new SqlEntry('p.kp_in_id',$id, SqlEntry::E));
		
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
		$_plans->SetPageName('ed_kp_in.php');
 
		
		if(isset($_GET['from'.$prefix])) $from=abs((int)$_GET['from'.$prefix]);
		  else $from=0;
		if(isset($_GET['to_page'.$prefix])) $to_page=abs((int)$_GET['to_page'.$prefix]);
		  else $to_page=ITEMS_PER_PAGE;
		  
		$decorator=new DBDecorator;
		
		
		if($editing_user['kind_id']==0){
			$decorator->AddEntry(new SqlEntry('t.kp_in_id',$id, SqlEntry::E));
			 
			 
			$decorator->AddEntry(new UriEntry('action',1));
			$decorator->AddEntry(new UriEntry('id',$id));
			$decorator->AddEntry(new UriEntry('kp_in_id',$id));
		}
		else{
			$decorator->AddEntry(new SqlEntry('t.kp_out_id',$id, SqlEntry::E));
			 
			 
			$decorator->AddEntry(new UriEntry('action',1));
			$decorator->AddEntry(new UriEntry('id',$id));
			$decorator->AddEntry(new UriEntry('kp_out_id',$id));
		}
		
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

 	


		
		
	}
	
	 
	$sm->assign('from_begin',$from_begin);	 
	
	
	$sm->assign('print_pdate', date("d.m.Y H:i:s"));
	//$username=$result['login'];
	$username=stripslashes($result['name_s']).' '.$username;	
	$sm->assign('print_username',$username);
	
	if(isset($editing_user['kind_id'])) $sm->assign('kind_id', $editing_user['kind_id']);
	else $sm->assign('kind_id', $kind_id);
	
	
	$sm->assign('users',$user_form);
	$content=$sm->fetch('kp_in/ed_kp_in_page'.$print_add.'.html');
	
	
	
	
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