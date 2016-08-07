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

 

require_once('classes/suppliersgroup.php');
require_once('classes/supplieritem.php');
 

require_once('classes/user_s_item.php');

 

 
require_once('classes/orgitem.php');
require_once('classes/opfitem.php');

 

require_once('classes/suppliercontactitem.php');
require_once('classes/supcontract_group.php');



 
require_once('classes/doc_in_filegroup.php');
require_once('classes/doc_in_fileitem.php');

require_once('classes/supplier_country_group.php');
require_once('classes/supplier_city_item.php');

require_once('classes/supplier_cities_group.php');

require_once('classes/doc_in.class.php');
require_once('classes/lead.class.php');
require_once('classes/doc_in1_field_rules.php');

require_once('classes/doc_out.class.php');

$_pch=new PeriodChecker;
$pch_date=$_pch->GetDate();



$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Вх. документ');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}


$_dem=new DocIn_AbstractItem;

$_plan=new DocIn_AbstractGroup;
 

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

$object_id=array();
switch($action){
	case 0:
	$object_id[]=1079;
	break;
	case 1:
	$object_id[]=1080;
	break;
	case 2:
	$object_id[]=1080;
	break;
	default:
	$object_id[]=1080;
	break;
}

$_editable_status_id=array();
$_editable_status_id[]=1;
//$_editable_status_id[]=9;
$_editable_status_id[]=18;


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
	 
	if(!isset($_GET['kind_id'])){
		if(!isset($_POST['kind_id'])){
			/*header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			include('404.php');
			die();*/
			$kind_id=0;
		}else $kind_id=abs((int)$_POST['kind_id']);	
	}else $kind_id=abs((int)$_GET['kind_id']);
	
	
	 
	
}elseif(($action==1)){

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
	
	//видим:
	$_res=new DocIn_Resolver($editing_user['kind_id']);
	
	
	$available_docs=$_res->group_instance->GetAvailableDocIds($result['id']);
	 
	 

	
	if(!in_array($editing_user['id'], $available_docs)){
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include('404.php');
		die();
	}
	
	
}


//файловый блок

if($action==2){
	if(!$au->user_rights->CheckAccess('w',1080)){
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
	
	$_pfi=new DocInFileItem;;
	
	$file=$_pfi->GetItemById($file_id);
	
	if($file!==false){
		$_pfi->Del($file_id);
		
		$log->PutEntry($result['id'],'удалил файл вх. документа',NULL, 1080,NULL,'имя файла '.SecStr($file['orig_name']));
	}
	
	header("Location: ed_doc_in.php?action=1&id=".$id.'&folder_id='.$folder_id);
	die();
}


 



//обработка данных

if(($action==0)&&(isset($_POST['doNew'])||isset($_POST['doNewEdit']))){
	 
	
	
	
	
	$params=array();
	//обычная загрузка прочих параметров
	$params['created_id']=$result['id'];
	
	$params['kind_id']=abs((int)$_POST['kind_id']);
	$params['manager_id']=abs((int)$_POST['manager_id']);
	
	$params['topic']= SecStr($_POST['topic']);
	$params['description']= SecStr($_POST['description']);
	
	$params['status_id']=18;
		
	$params['pdate']=time();
	
 	if(isset($_POST['is_urgent'])) $params['is_urgent']=1; else $params['is_urgent']=0;

	

	
	$code=$_dem->Add($params);
	 
	//$code=1;
	//запись в журнале
	if($code>0){
		$log->PutEntry($result['id'],'создал входящий документ',NULL,1079,NULL,NULL,$code);	
		
		foreach($params as $k=>$v){
			
		  
				
				$log->PutEntry($result['id'],'создал входящий документ',NULL,1079, NULL, 'в поле '.$k.' установлено значение '.$v,$code);		
			 
		}
		
	
	
	 
	
	// создадим контрагентов, лиды, файлы, подписанты-согласов.
	  
 
		//контрагенты
		$_supplier=new SupplierItem;
		$_sg=new DocIn_SupplierGroup; 
		$_opf=new OpfItem;
		
		
		$positions=array();
		
		foreach($_POST as $k=>$v){
		  if(eregi("^supplier_id_([0-9]+)",$k)){
			  
			  $hash=abs((int)eregi_replace("^supplier_id_","",$k));
			  
			  $supplier_id=$hash;  
			  //найдем контакты
			  $contacts=array();
			  
			  foreach($_POST as $k1=>$v1) if(eregi("^supplier_contact_id_".$supplier_id."_([0-9]+)",$k1)){
			  	$contacts[]=abs((int)$v1);
			  }
			  
			 
			  $positions[]=array(
				  'sched_id'=>$code,
				   
				  'supplier_id'=>$supplier_id,
				  'contacts'=>$contacts,
				  'note'=> SecStr($_POST['supplier_note_'.$hash])
			  );
			  
		  }
		}
		
		$log_entries=$_sg->AddSuppliers($code, $positions); 
		//die();
		//запишем в журнал
		 foreach($log_entries as $k=>$v){
			   $supplier=$_supplier->GetItemById($v['supplier_id']);
			  $opf=$_opf->GetItemById($supplier['opf_id']); 
			 
			  $description=SecStr($supplier['full_name'].' '.$opf['name'].', примечание: '.$v['note']);
			 
			  
			  if($v['action']==0){
				  $log->PutEntry($result['id'],'добавил контрагента во входящий документ',NULL,1079,NULL,$description,$code);	
			  }elseif($v['action']==1){
				  $log->PutEntry($result['id'],'редактировал контрагента во входящем документе',NULL,1079,NULL,$description,$code);
			  }elseif($v['action']==2){
				  $log->PutEntry($result['id'],'удалил контрагента из входящего документа',NULL,1079,NULL,$description,$code);
			  }
			  
		  }
		
		
		  //файлы
		  $fmi=new DocInFileItem;
			foreach($_POST as $k=>$v){
			  if(eregi("^upload_file_",$k)){
					$filename=eregi_replace("^upload_file_", '', eregi_replace("_tmp$",".tmp",$k));
				  $fmi->Add(array('bill_id'=>$code, 'filename'=>SecStr(basename($filename)), 'orig_name'=>SecStr($v), 'user_id'=>$result['id'], 'pdate'=>time()));
				  
				   $log->PutEntry($result['id'], 'прикрепил файл к входящему документу', NULL, 1079, NULL,'Служебное имя файла: '.SecStr(basename($filename)).' Имя файла: '.SecStr($v),$code);
				   
				   
			  }
			}
	 
		  
		  //лиды
		 $positions=array();
		 $positions[]=array(
				  'doc_out_id'=>$code,
				   
				  'lead_id'=>abs((int)$_POST['lead_id']),
				  'pdate'=>time()
			  );
			   
		 $_ld=new DocIn_LeadGroup; $_lead=new Lead_Item;
		 // 
		 $log_entries=$_ld->AddItems($code, $positions, $result);
		  foreach($log_entries as $k=>$v){
			   $lead=$_lead->GetItemById($v['lead_id']);
			  
			  $description=SecStr($lead['code'].'');
			 
			  
			  if($v['action']==0){
				  $log->PutEntry($result['id'],'добавил лид в входящий документ',NULL,1079,NULL,$description,$code);	
			  }elseif($v['action']==1){
				  $log->PutEntry($result['id'],'редактировал лид в входящем документе',NULL,1079,NULL,$description,$code);
			  }elseif($v['action']==2){
				  $log->PutEntry($result['id'],'удалил лид из входящего документа',NULL,1079,NULL,$description,$code);
			  }
			  
		  }
		  
		  
		   
		  
			
			
		  //желаете ли вы утвердить?				  
		  if($au->user_rights->CheckAccess('w',1079)&&($_POST['do_confirm']==1)){
		  	  
			  $_res=new DocIn_Resolver($params['kind_id']);
		  
			  $_res->instance->Edit($code,array('is_confirmed'=>1, 'user_confirm_id'=>$result['id'], ' 	confirm_pdate'=>time()),true,$result);
							
			  $log->PutEntry($result['id'],'автоматически утвердил заполнение входящего документа',NULL,1079, NULL, NULL,$code);	
			  
			  
		  }else{
			  $log->PutEntry($result['id'],'отказался от автоматического утверждения заполнения входящего документа',NULL,1079, NULL, NULL,$code);	
		  }
	
	}
	
	
	
	//перенаправления
	if(isset($_POST['doNew'])){
		header("Location: doc_ins.php#user_".$code);
		die();
	}elseif(isset($_POST['doNewEdit'])){
		//если есть доступ к объекту 11 - правка пользователя - то переход туда		
		 
		header("Location: ed_doc_in.php?action=1&id=".$code.'&from_begin='.$from_begin);
		die();	
		
	}else{
		header("Location: doc_ins.php");
		die();
	}
	 
	
	die();
	


}elseif(($action==1)&&(isset($_POST['doEdit'])||isset($_POST['doEditStay'])
	||isset($_POST['to_view'])
	||isset($_POST['to_sign_2'])
	||isset($_POST['to_reg'])
	||isset($_POST['notActual'])
	||isset($_POST['doActual']))){


	 
	
	//редактирование возможно, если позволяет статус
	
	 
	$condition =in_array($_POST['current_status_id'],$_editable_status_id)&&in_array($editing_user['status_id'],$_editable_status_id);
	
	$_res=new DocIn_Resolver($editing_user['kind_id']);
	
	
	
		//поля формируем в зависимости от их активности в текущем статусе
	$_roles=$_res->rules_instance; //var_dump($_roles->GetTable());
	$field_rights0=$_roles->GetFields($editing_user, $result['id']);	
	$field_rights1=$_roles->GetFields($editing_user, $result['id'], $_POST['current_status_id']);
	$field_rights=array(); 
	foreach($field_rights0 as $k=>$v) $field_rights[$k]=$v&&$field_rights1[$k];
	
	
	
	
	$params=array();
	
	if($condition){
		
		//обычная загрузка прочих параметров
		$params['manager_id']=abs((int)$_POST['manager_id']);
		if(isset($_POST['is_urgent'])) $params['is_urgent']=1; else $params['is_urgent']=0;
		
		//print_r($_POST); die();
	}
			
	if($field_rights['topic_txt'])	$params['topic']= SecStr($_POST['topic']);
	if($field_rights['topic_txt'])	$params['description']= SecStr($_POST['description']);
	
	if($field_rights['view_comment'])	$params['view_comment']= SecStr($_POST['view_comment']);
		
		
		
	$_res->instance->Edit($id, $params);
	
	
	//$_dem->Edit($id, $params);
	//die();
	//запись в журнале
	//записи в лог. сравнить старые и новые записи
	foreach($params as $k=>$v){
		
		if(addslashes($editing_user[$k])!=$v){
			 
			
			$log->PutEntry($result['id'],'редактировал входящий документ',NULL,1080, NULL, 'в поле '.$k.' установлено значение '.$v,$id);
			
					
		}
		
		
	}	
		
	if($condition){	
		
		// правим контрагентов, лиды, файлы, подписанты-согласов.
	  
 
		//контрагенты
		$_supplier=new SupplierItem;
		$_sg=new DocIn_SupplierGroup; 
		$_opf=new OpfItem;
		
		
		$positions=array();
		
		foreach($_POST as $k=>$v){
		  if(eregi("^supplier_id_([0-9]+)",$k)){
			  
			  $hash=abs((int)eregi_replace("^supplier_id_","",$k));
			  
			  $supplier_id=$hash;  
			  //найдем контакты
			  $contacts=array();
			  
			  foreach($_POST as $k1=>$v1) if(eregi("^supplier_contact_id_".$supplier_id."_([0-9]+)",$k1)){
			  	$contacts[]=abs((int)$v1);
			  }
			  
			 
			  $positions[]=array(
				  'sched_id'=>$id,
				   
				  'supplier_id'=>$supplier_id,
				  'contacts'=>$contacts,
				  'note'=> SecStr($_POST['supplier_note_'.$hash])
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
				  $log->PutEntry($result['id'],'добавил контрагента в входящий документ',NULL,1080,NULL,$description,$id);	
			  }elseif($v['action']==1){
				  $log->PutEntry($result['id'],'редактировал контрагента в входящем документе',NULL,1080,NULL,$description,$id);
			  }elseif($v['action']==2){
				  $log->PutEntry($result['id'],'удалил контрагента из входящего документа',NULL,1080,NULL,$description,$id);
			  }
			  
		  }
		
		
		  
		  //лиды
		 $positions=array();
		 $positions[]=array(
				  'doc_out_id'=>$id,
				   
				  'lead_id'=>abs((int)$_POST['lead_id']),
				  'pdate'=>time()
			  );
			   
		 $_ld=new DocIn_LeadGroup; $_lead=new Lead_Item;
		 // 
		 $log_entries=$_ld->AddItems($id, $positions, $result);
		  foreach($log_entries as $k=>$v){
			   $lead=$_lead->GetItemById($v['lead_id']);
			  
			  $description=SecStr($lead['code'].'');
			 
			  
			  if($v['action']==0){
				  $log->PutEntry($result['id'],'добавил лид в входящий документ',NULL,1080,NULL,$description,$id);	
			  }elseif($v['action']==1){
				  $log->PutEntry($result['id'],'редактировал лид в входящем документе',NULL,1080,NULL,$description,$id);
			  }elseif($v['action']==2){
				  $log->PutEntry($result['id'],'удалил лид из входящего документа',NULL,1080,NULL,$description,$id);
			  }
			  
		  }
		  
		  
		  
		
	}
		       
	 
	 
	 
	//утверждение  заполнения
	
	$_res=new DocIn_Resolver($editing_user['kind_id']);
	
	if($field_rights['to_confirm']){	
	  if($editing_user['is_confirmed']==1){
		  //есть права: либо сам утв.+есть права, либо есть искл. права:
		  if(($au->user_rights->CheckAccess('w',1087))){
			  if((!isset($_POST['is_confirmed']))&&($_res->instance->DocCanUnconfirmPrice($id,$rss32))){
				  
				  
				  $_res->instance->Edit($id,array('is_confirmed'=>0, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,$result);
				  
				  $log->PutEntry($result['id'],'снял утверждение заполнения',NULL,1087, NULL, NULL,$id);	
				  
			  }
		  } 
		  
	  }else{
		  //есть права
		  if($au->user_rights->CheckAccess('w',1080)){
			  if(isset($_POST['is_confirmed'])&&($_POST['is_confirmed']==1)&&($_res->instance->DocCanConfirmPrice($id,$rss32))){
				  
				  $_res->instance->Edit($id,array('is_confirmed'=>1, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,$result);
				  
				  $log->PutEntry($result['id'],'утвердил заполнение',NULL,1080, NULL, NULL,$id);	
				  
				   
				  //die();
			  }
		  } 
	  }
	}
	
	
	//утверждение рассмотрения
 	if($field_rights['is_viewed']){	
	  if($editing_user['is_viewed']==1){
		  //есть права: либо сам утв.+есть права, либо есть искл. права:
		  if($au->user_rights->CheckAccess('w',1089)||($editing_user['manager_id']==$result['id'])){
			  if((!isset($_POST['is_viewed']))&&($_res->instance->DocCanUnconfirmView($id,$rss32))){
				  
				  
				  $_res->instance->Edit($id,array('is_viewed'=>0, 'user_view_id'=>$result['id'], 'view_pdate'=>time()), true,$result);
				  
				  $log->PutEntry($result['id'],'снял утверждение рассмотрения',NULL,1089, NULL, NULL,$id);	
				  
			  }
		  } 
		  
	  }else{
		  //есть права
		  if($au->user_rights->CheckAccess('w',1088)||($editing_user['manager_id']==$result['id'])){
			  if(isset($_POST['is_viewed'])&&($_POST['is_viewed']==1)&&($_res->instance->DocCanConfirmView($id,$rss32))){
				  
				  $_res->instance->Edit($id,array('is_viewed'=>1, 'user_view_id'=>$result['id'], 'view_pdate'=>time()),true,$result);
				  
				  $log->PutEntry($result['id'],'утвердил рассмотрение',NULL,1088, NULL, NULL,$id);	
				  
				   
				  //die();
			  }
		  } 
	  }
	} 
		 
	
	 
	 
  
			 
	
	$_dsi=new DocStatusItem; 
	//обработка выделенных кнопок
 
	
	//обработка выделенных кнопок
	if(isset($_POST['to_view'])){
		
		if($field_rights['to_view']){
			
			$setted_status_id=35;
			$_res->instance->Edit($id,array( 'status_id'=>$setted_status_id),true, $result);
			
			$stat=$_dsi->GetItemById($setted_status_id);
			$log->PutEntry($result['id'],'смена статуса вх. документа',NULL,1080,NULL,'установлен статус '.$stat['name'],$id);
			
			//отправить письмо всем согласователям
			//$_sgns=new DocOut_SignGroup;
			//$_sgns->SendMessagesToSigners($id, 1);
			
			$_msg=new DocIn_Messages;
			$_msg->SendMessageToView($id);
			
			 
					
		}		
	}
	
	 
	
	
	//die();
	
	//перенаправления
	if(isset($_POST['doEdit'])){
		header("Location: doc_ins.php#user_".$id);
		die();
	}elseif(isset($_POST['doEditStay'])
		||isset($_POST['to_view'])
		||isset($_POST['to_sign_2'])
		||isset($_POST['to_reg'])
		||isset($_POST['notActual'])
		||isset($_POST['doActual'])){
	
	 
		header("Location: ed_doc_in.php?action=1&id=".$id.'&from_begin='.$from_begin);
		die();	
		
	}else{
		header("Location: doc_ins.php");
		die();
	}
	
	die();
}


 //журнал событий 
if($action==1){
	$log=new ActionLog;
	 
	$log->PutEntry($result['id'],'открыл входящий документ',NULL,1080, NULL, 'входящий документ № '.$editing_user['id'],$id);
	 
				
} 



//работа с хедером
//работа с хедером
$stop_popup=true;

require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

if($print==0) $smarty->display('top.html');
else $smarty->display('top_print.html');
unset($smarty);

	$_menu_id=81;	
	if($print==0) include('inc/menu.php');
	
	
	//демонстрация  страницы
	$smarty = new SmartyAdm;
	
	$sm1=new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	//создание позиции
	 if($action==0){
		 
		$sm1->assign('manager_id', $result['id']);
		$sm1->assign('manager_string', $result['name_s']);
		
		
		
		$sm1->assign('now_time',  date('d.m.Y H:i:s')); 
		$sm1->assign('now_date',  date('d.m.Y')); 
		
		
		
		//виды
		$_kinds=new DocIn_KindGroup;
		$kinds=$_kinds->GetItemsArr(0);
		$kind_ids=array(0); $kind_vals=array('-выберите-');
		foreach($kinds as $k=>$v){
			$kind_ids[]=$v['id']; $kind_vals[]=$v['name'];	
		}
		$sm1->assign('kind_ids', $kind_ids); $sm1->assign('kind_vals', $kind_vals);
		$sm1->assign('kind_id', $kind_id); 
		   
		
		
		//копируем данные
		if(isset($_GET['copyfrom'])){
			$old_doc=$_dem->getitembyid(abs((int)$_GET['copyfrom']));
			
			foreach($old_doc as $k=>$v) $old_doc[$k]=stripslashes($v);	
			
			$_res=new DocIn_Resolver($old_doc['kind_id']);
			
			$sm1->assign('kind_id', $old_doc['kind_id']); 
			
			
			
			
			$_leads=new DocIn_LeadGroup;
			$leads=$_leads->GetItemsByIdArr($old_doc['id']);
			
			$lead=$leads[0];
			$old_doc['lead_id']=$lead['lead_id'];
			$old_doc['lead_string']=$lead['code'];
		 
			//контрагенты
			$_suppliers=new DocIn_SupplierGroup;
			$sup=$_suppliers->GetItemsByIdArr($old_doc['id']);
			$sm1->assign('suppliers', $sup);
			
			
			
		 
			
			//прикрепленные файлы
	 
			 //файлы 
			 $can_modify_files=false;
			 
			  $folder_id=0;
			 
			  $decorator=new DBDecorator;
			  
			  $decorator->AddEntry(new SqlOrdEntry('pdate',SqlOrdEntry::DESC));
			 // $decorator->AddEntry(new SqlEntry('id',$id, SqlEntry::E));
				$decorator->AddEntry(new UriEntry('id',$old_doc['id']));
			  //$decorator->AddEntry(new SqlEntry('user_d_id',$user_id, SqlEntry::E));
			  
			  
			  $decorator->AddEntry(new SqlEntry('folder_id',$folder_id, SqlEntry::E));
			 $decorator->AddEntry(new UriEntry('folder_id',$folder_id));
		
			  $navi_dec=new DBDecorator;
			  $navi_dec->AddEntry(new UriEntry('action',0));
			  
			  
			  if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
			  else $from=0;
			  
			  if(isset($_GET['to_page'])) $to_page=abs((int)$_GET['to_page']);
			  else $to_page=ITEMS_PER_PAGE;
			  
			  $ffg=new DocInFileGroup(1,  $old_doc['id'],  new FileDocFolderItem(1,  $old_doc['id'], new DocInFileItem(1)));;
			  
			  $filetext=$ffg->ShowFiles('doc_in/uploaded_files_list.html',  $decorator,$from,$to_page,'ed_doc_in.php', 'doc_in_file.html', 'swfupl-js/doc_in_files.php',  
			  $can_modify_files,  
			  $can_modify_files, 
			 $can_modify_files , 
			  $folder_id, 
			  false, 
			false , 
			 false, 
			 false ,    
			  '_incard',  
			  
			 $can_modify_files,  
			   $result, 
			   $navi_dec,   'file_' 
			   );
			   
			$sm1->assign('filetext', $filetext);   
			
			$sm1->assign('old_doc', $old_doc);
		}else{
			//не копируем - форма создания
			$old_doc=array();
			if($kind_id==2){
				$old_doc['topic']='КП ';
			}
			
			
			$sm1->assign('old_doc', $old_doc);
			 
		}
		
		
		//получим список тех, кто может снять утверждение заполнения
		$_usg1=new UsersSGroup;
		$usg1=$_usg1->GetUsersByRightArr('w', 1087);
		$sm1->assign('can_unconfirm_users',$usg1);
		$sm1->assign('can_unconfirm',$au->user_rights->CheckAccess('w',1087));
		
		
		 
		$sm1->assign('session_id', session_id());
		
		$sm1->assign('can_add_supplier', $au->user_rights->CheckAccess('w',87));
		
	 
		
		$sm1->assign('can_confirm', $au->user_rights->CheckAccess('w',1079));
		
		$sm1->assign('can_modify_manager', true);
		
		
		
		
		$user_form=$sm1->fetch('doc_in/create_doc_in.html');
		 
		 
	
	
	 }elseif($action==1){
		//редактирование позиции
		
		if($print==0) $print_add='';
		else $print_add='_print';
		
		
		$_res=new  DocIn_Resolver($editing_user['kind_id']);
		
		
		//построим доступы
		$_roles=$_res->rules_instance;
		$field_rights=$_roles->GetFields($editing_user, $result['id']);
		$sm1->assign('field_rights', $field_rights);
		
		 
		
		$sm1->assign('manager_id', $editing_user['manager_id']);
		$_uis=new UserSItem; $uis=$_uis->getitembyid($editing_user['manager_id']);
		$editing_user['manager_string']= $uis['name_s'];
		
		 
		$_kind=new DocIn_KindItem; $kind=$_kind->getitembyid($editing_user['kind_id']);
		$editing_user['kind_name']=$kind['name'];
		
		$_leads=new DocIn_LeadGroup;
		$leads=$_leads->GetItemsByIdArr($editing_user['id']);
		
		$lead=$leads[0];
		$editing_user['lead_id']=$lead['lead_id'];
		$editing_user['lead_string']=$lead['code'];
		
		
		//if($editing_user['reg_pdate']!=0) $editing_user['reg_pdate']=date('d.m.Y', $editing_user['reg_pdate']);
		
		$editing_user['pdate']=date('d.m.Y H:i:s', $editing_user['pdate']);
		
		//if($editing_user['received_pdate']!=0) $editing_user['received_pdate']=date('d.m.Y', $editing_user['received_pdate']);
		
		//if($editing_user['send_pdate']!=0) $editing_user['send_pdate']=date('d.m.Y', $editing_user['send_pdate']);
		
		//кто отправил
		$_user=new UserSItem;
		$send_who=$_user->GetItemById($editing_user['send_user_id']);
		$sm1->assign('send_who', $send_who['name_s']);
		
		
		
		
		  
		
		//возможность РЕДАКТИРОВАНИЯ - 
			//пол-ль - создал
	//или пол-ль - в списке видящих
	 	
		//$can_modify=true;
		
		$can_modify=in_array($editing_user['status_id'],$_editable_status_id);
		
		 
		
		 
		//контрагенты
		$_suppliers=new DocIn_SupplierGroup;
		$sup=$_suppliers->GetItemsByIdArr($editing_user['id']);
		$sm1->assign('suppliers', $sup);
		 

		$can_modify_ribbon=!in_array($editing_user['status_id'],array(3));
	 
	 	//лента документа
		$_hg=new DocIn_HistoryGroup;
		$history= $_hg->ShowHistory(
			$editing_user['id'],
			 'doc_in/lenta'.$print_add.'.html', 
			 new DBDecorator(), 
			 $can_modify_ribbon,
			 true,
			 false,
			 $result,
			 $au->user_rights->CheckAccess('w',1081),
			 $au->user_rights->CheckAccess('w',1082),$history_data,true,true
			 );
		$sm1->assign('lenta',$history);
		$sm1->assign('lenta_len',count($history_data));
		 
		 
		
		
		//блок аннулирования
		
		$editing_user['can_annul']=$_res->instance->DocCanAnnul($editing_user['id'],$reason,$editing_user)&&$au->user_rights->CheckAccess('w',1080);
		if(!$au->user_rights->CheckAccess('w',1080)) $reason='недостаточно прав для данной операции';
		$editing_user['can_annul_reason']=$reason;
		
		 
		
		$editing_user['can_restore']=$_res->instance->DocCanRestore($editing_user['id'],$reason,$editing_user)&&$au->user_rights->CheckAccess('w',1086);
			if(!$au->user_rights->CheckAccess('w',1086)) $reason='недостаточно прав для данной операции';
		
		
		
		
			
		 
		
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
		if($editing_user['is_viewed']==0){
			
			  
		  
		  if($editing_user['is_confirmed']==1){
			  if($au->user_rights->CheckAccess('w',1087)){
				  //есть права + сам утвердил
				  $can_confirm_price=true;	
			  }else{
				  $can_confirm_price=false;
			  }
		  }else{
			  //95
			  $can_confirm_price=$au->user_rights->CheckAccess('w',1080)&&in_array($editing_user['status_id'],$_editable_status_id)  ;
		  }
		}
		$sm1->assign('can_confirm',$can_confirm_price);
		
		
		//блок утв. получения
		if(($editing_user['is_viewed']==1)&&($editing_user['user_view_id']!=0)){
			$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['user_view_id']);
			$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.date("d.m.Y H:i:s",$editing_user['view_pdate']);
			
			$sm1->assign('is_viewed_confirmer',$confirmer);
		}
		
		$can_confirm_shipping=false;
		if($editing_user['is_confirmed']==1){
		
		  if($editing_user['is_viewed']==1){
			 $can_confirm_shipping=$au->user_rights->CheckAccess('w',1089)||($editing_user['manager_id']==$result['id']);
			 
		  }else{
			  //95
			  $can_confirm_shipping=$au->user_rights->CheckAccess('w',1088)||($editing_user['manager_id']==$result['id']);
		  }
		}
		// + есть галочка утв. цен
		$can_confirm_shipping=$can_confirm_shipping&&($editing_user['is_confirmed']==1);
		
		
		$sm1->assign('can_confirm_viewed',$can_confirm_shipping);
		  
		
		
		//связанные исх док-ты
		$_out_g=new DocOut_AbstractGroup;
		$decorator=new DBDecorator();
		$decorator->AddEntry(new SqlOrdEntry('pdate',SqlOrdEntry::DESC));
		$decorator->AddEntry(new SqlEntry('doc_in_id',$id, SqlEntry::E));
		
		$binded=$_out_g->ShowPosArrByDec($decorator);
		$sm1->assign('binded',$binded);
		
		
		 
		
		//получим список тех, кто может снять утверждение заполнения
		$_usg1=new UsersSGroup;
		$usg1=$_usg1->GetUsersByRightArr('w', 1087);
		$sm1->assign('can_unconfirm_users',$usg1);
		$sm1->assign('can_unconfirm',$au->user_rights->CheckAccess('w',1087));
		
		
		 
		$sm1->assign('session_id', session_id());
		
		$sm1->assign('can_add_supplier', $au->user_rights->CheckAccess('w',87));
		
		$sm1->assign('can_copy', $au->user_rights->CheckAccess('w',1084));
		
		
		
		
		
		
		$sm1->assign('can_modify', $can_modify);
		
		$sm1->assign('can_modify_manager', $can_modify);
		
		
		
		//для заметки - возможность править контрагентов
		$sm1->assign('can_modify_suppliers', $can_modify);
		  

		
		 
		
		
		$sm1->assign('can_create', $au->user_rights->CheckAccess('w',1079));  
		
		
		
	 
		//прикрепленные файлы
	 
			 //файлы 
			 $can_modify_files=$can_modify;
			 
			  if(isset($_GET['folder_id'])) $folder_id=abs((int)$_GET['folder_id']);
			  else $folder_id=0;
			 
			  $decorator=new DBDecorator;
			  
			  $decorator->AddEntry(new SqlOrdEntry('pdate',SqlOrdEntry::DESC));
			 // $decorator->AddEntry(new SqlEntry('id',$id, SqlEntry::E));
			$decorator->AddEntry(new UriEntry('id',$id));
			  //$decorator->AddEntry(new SqlEntry('user_d_id',$user_id, SqlEntry::E));
			  
			  
			  $decorator->AddEntry(new SqlEntry('folder_id',$folder_id, SqlEntry::E));
			 $decorator->AddEntry(new UriEntry('folder_id',$folder_id));
		
			  $navi_dec=new DBDecorator;
			  $navi_dec->AddEntry(new UriEntry('action',1));
			  
			  
			  if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
			  else $from=0;
			  
			  if(isset($_GET['to_page'])) $to_page=abs((int)$_GET['to_page']);
			  else $to_page=ITEMS_PER_PAGE;
			  
			  $ffg=new DocInFileGroup(1,  $id,  new FileDocFolderItem(1,  $id, new DocInFileItem(1)));;
			  
			  $filetext=$ffg->ShowFiles('doc_file/incard_list.html',  $decorator,$from,$to_page,'ed_doc_in.php', 'doc_in_file.html', 'swfupl-js/doc_in_files.php',  
			  $can_modify_files,  
			  $can_modify_files, 
			 $can_modify_files , 
			  $folder_id, 
			  false, 
			false , 
			 false, 
			 false ,    
			  '_incard',  
			  
			 $can_modify_files,  
			   $result, 
			   $navi_dec,   'file_' 
			   );
				
			/*public function ShowFiles($template, DBDecorator $dec,$from=0,$to_page=ITEMS_PER_PAGE,$pagename='files.php', $loadname='load.html', $uploader_name='/swfupl-js/files.php', 
	$can_load=false, 
	$can_delete=false, 
	$can_edit=false, 
	$folder_id=0, 
	$can_create_folder=false, 
	$can_edit_folder=false, 
	$can_delete_folder=false, 
	$can_move_folder=false, 
	$id_prefix='',
	$can_edit_own=false,
	$result=NULL ,
	
	$navi_decorator=NULL, $elem_id_prefix=''
	){*/		
				
			$sm1->assign('files', $filetext);
	 
		
		$_dsi=new docstatusitem; $dsi=$_dsi->GetItemById($editing_user['status_id']);
		$editing_user['status_name']=$dsi['name'];
		$sm1->assign('bill', $editing_user);
		
		switch($editing_user['kind_id']){
			case 1:	
				$user_form=$sm1->fetch('doc_in/edit_kind_1'.$print_add.'.html');
			break;
			
			case 2:	
				$user_form=$sm1->fetch('doc_in/edit_kind_2'.$print_add.'.html');
			break;
			 
		
		}
		
		/*
		echo '<pre>';
			var_dump($field_rights);
			echo '</pre>';*/
		
	 
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
		
			$decorator->AddEntry(new SqlEntry('object_id',NULL, SqlEntry::IN_VALUES, NULL, array(1062,
1079,
1080,
1081,
1082,
1083,
1084,
1085,
1086,
1087,
1088,
1089


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
			 
			$llg=$log->ShowLog('syslog/log_doc.html',$decorator,$from,$to_page,'ed_doc_in.php',true,true,true);
			
			$sm->assign('syslog',$llg);		
				
		} 
		
		
	}
	
	
	$sm->assign('print_pdate', date("d.m.Y H:i:s"));
	//$username=$result['login'];
	$username=stripslashes($result['name_s']); //.' '.$username;	
	$sm->assign('print_username',$username);
	
	$sm->assign('users',$user_form);
	$content=$sm->fetch('doc_in/ed_doc_in_page'.$print_add.'.html');
	
	
	
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