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



 
require_once('classes/doc_out_filegroup.php');
require_once('classes/doc_out_fileitem.php');

require_once('classes/supplier_country_group.php');
require_once('classes/supplier_city_item.php');

require_once('classes/supplier_cities_group.php');

require_once('classes/doc_out.class.php');
require_once('classes/lead.class.php');
require_once('classes/doc_out1_field_rules.php');

require_once('classes/doc_in.class.php');


$_pch=new PeriodChecker;
$pch_date=$_pch->GetDate();



$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Исх. документ');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}


$_dem=new DocOut_AbstractItem;

$_plan=new DocOut_AbstractGroup;
 

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
	$object_id[]=1064;
	break;
	case 1:
	$object_id[]=1065;
	break;
	case 2:
	$object_id[]=1065;
	break;
	default:
	$object_id[]=1065;
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
	$_res=new DocOut_Resolver($editing_user['kind_id']);
	
	
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
	if(!$au->user_rights->CheckAccess('w',1065)){
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
	
	$_pfi=new DocOutFileItem;;
	
	$file=$_pfi->GetItemById($file_id);
	
	if($file!==false){
		$_pfi->Del($file_id);
		
		$log->PutEntry($result['id'],'удалил файл исх. документа',NULL, 1065,NULL,'имя файла '.SecStr($file['orig_name']));
	}
	
	header("Location: ed_doc_out.php?action=1&id=".$id.'&folder_id='.$folder_id);
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
	
 	$params['doc_in_id']=abs((int)$_POST['doc_in_id']);

	

	
	$code=$_dem->Add($params);
	 
	//$code=1;
	//запись в журнале
	if($code>0){
		$log->PutEntry($result['id'],'создал исходящий документ',NULL,1064,NULL,NULL,$code);	
		
		foreach($params as $k=>$v){
			
		  
				
				$log->PutEntry($result['id'],'создал исходящий документ',NULL,1064, NULL, 'в поле '.$k.' установлено значение '.$v,$code);		
			 
		}
		
	
	
	 
	
	// создадим контрагентов, лиды, файлы, подписанты-согласов.
	  
 
		//контрагенты
		$_supplier=new SupplierItem;
		$_sg=new DocOut_SupplierGroup; 
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
				  $log->PutEntry($result['id'],'добавил контрагента в исходящий документ',NULL,1064,NULL,$description,$code);	
			  }elseif($v['action']==1){
				  $log->PutEntry($result['id'],'редактировал контрагента в исходящем документе',NULL,1064,NULL,$description,$code);
			  }elseif($v['action']==2){
				  $log->PutEntry($result['id'],'удалил контрагента из исходящего документа',NULL,1064,NULL,$description,$code);
			  }
			  
		  }
		
		
		  //файлы
		  $fmi=new DocOutFileItem;
			foreach($_POST as $k=>$v){
			  if(eregi("^upload_file_",$k)){
					$filename=eregi_replace("^upload_file_", '', eregi_replace("_tmp$",".tmp",$k));
				  $fmi->Add(array('bill_id'=>$code, 'filename'=>SecStr(basename($filename)), 'orig_name'=>SecStr($v), 'user_id'=>$result['id'], 'pdate'=>time()));
				  
				   $log->PutEntry($result['id'], 'прикрепил файл к исходящему документу', NULL, 1064, NULL,'Служебное имя файла: '.SecStr(basename($filename)).' Имя файла: '.SecStr($v),$code);
				   
				   
			  }
			}
	 
		  
		  //лиды
		 $positions=array();
		 $positions[]=array(
				  'doc_out_id'=>$code,
				   
				  'lead_id'=>abs((int)$_POST['lead_id']),
				  'pdate'=>time()
			  );
			   
		 $_ld=new DocOut_LeadGroup; $_lead=new Lead_Item;
		 // 
		 $log_entries=$_ld->AddItems($code, $positions, $result);
		  foreach($log_entries as $k=>$v){
			   $lead=$_lead->GetItemById($v['lead_id']);
			  
			  $description=SecStr($lead['code'].'');
			 
			  
			  if($v['action']==0){
				  $log->PutEntry($result['id'],'добавил лид в исходящий документ',NULL,1064,NULL,$description,$code);	
			  }elseif($v['action']==1){
				  $log->PutEntry($result['id'],'редактировал лид в исходящем документе',NULL,1064,NULL,$description,$code);
			  }elseif($v['action']==2){
				  $log->PutEntry($result['id'],'удалил лид из исходящего документа',NULL,1064,NULL,$description,$code);
			  }
			  
		  }
		  
		  
		  
		  //согласователи
		  $positions=array();
	 	  foreach($_POST as $k=>$v){
			  if(eregi("^new_sign_1_id_",$k)){
				  
				  $hash=eregi_replace("^new_sign_1_id_","",$k);
				  
				  $supplier_id=abs((int)$v);;  
				  
				  
				 
				  $positions[]=array(
					  'doc_out_id'=>$code,
					   
					  'user_id'=>$supplier_id,
					  'sign_kind_id'=>1,
					  'is_signed'=> 0
				  );
				  
			  }
			}
		  
		  $_signs=new DocOut_SignGroup; $_ui=new UserSItem;
		  $log_entries=$_signs->AddItems($code, 1, $positions, $result);
		  
		 /* echo '<pre>';
		  print_r($positions);
		  print_r($log_entries);
		  
		  echo '</pre>';*/
		  
		  
		  foreach($log_entries as $k=>$v){
			   $user=$_ui->GetItemById($v['user_id']);
			  
			  $description=SecStr($user['name_s'].'');
			 
			  
			  if($v['action']==0){
				  $log->PutEntry($result['id'],'добавил согласователя в исходящий документ',NULL,1064,NULL,$description,$code);	
			  }elseif($v['action']==1){
				  $log->PutEntry($result['id'],'редактировал согласователя в исходящем документе',NULL,1064,NULL,$description,$code);
			  }elseif($v['action']==2){
				  $log->PutEntry($result['id'],'удалил согласователя из исходящего документа',NULL,1064,NULL,$description,$code);
			  }
			  
		  }	
		  
		  //подписанты
		  $positions=array();
	 	  foreach($_POST as $k=>$v){
			  if(eregi("^new_sign_2_id_",$k)){
				  
				  $hash=eregi_replace("^new_sign_2_id_","",$k);
				  
				  $supplier_id=abs((int)$v);;  
				  
				  
				 
				  $positions[]=array(
					  'doc_out_id'=>$code,
					   
					  'user_id'=>$supplier_id,
					  'sign_kind_id'=>2,
					  'is_signed'=> 0
				  );
				  
			  }
			}
		  
		  $_signs=new DocOut_SignGroup; $_ui=new UserSItem;
		  $log_entries=$_signs->AddItems($code, 2, $positions, $result);
		  
		 /* echo '<pre>';
		  print_r($positions);
		  print_r($log_entries);
		  
		  echo '</pre>';*/
		  
		  
		  foreach($log_entries as $k=>$v){
			   $user=$_ui->GetItemById($v['user_id']);
			  
			  $description=SecStr($user['name_s'].'');
			 
			  
			  if($v['action']==0){
				  $log->PutEntry($result['id'],'добавил подписанта в исходящий документ',NULL,1064,NULL,$description,$code);	
			  }elseif($v['action']==1){
				  $log->PutEntry($result['id'],'редактировал подписанта в исходящем документе',NULL,1064,NULL,$description,$code);
			  }elseif($v['action']==2){
				  $log->PutEntry($result['id'],'удалил подписанта из исходящего документа',NULL,1064,NULL,$description,$code);
			  }
			  
		  }	
	 
			
			
		  //желаете ли вы утвердить?				  
		  if($au->user_rights->CheckAccess('w',1064)&&($_POST['do_confirm']==1)){
		  	  
			  $_res=new DocOut_Resolver($params['kind_id']);
		  
			  $_res->instance->Edit($code,array('is_confirmed'=>1, 'user_confirm_id'=>$result['id'], ' 	confirm_pdate'=>time()),true,$result);
							
			  $log->PutEntry($result['id'],'автоматически утвердил заполнение исходящего документа',NULL,1064, NULL, NULL,$code);	
			  
			  
		  }else{
			  $log->PutEntry($result['id'],'отказался от автоматического утверждения заполнения исходящего документа',NULL,1064, NULL, NULL,$code);	
		  }
	
	}
	
	
	
	//перенаправления
	if(isset($_POST['doNew'])){
		header("Location: doc_outs.php#user_".$code);
		die();
	}elseif(isset($_POST['doNewEdit'])){
		//если есть доступ к объекту 11 - правка пользователя - то переход туда		
		 
		header("Location: ed_doc_out.php?action=1&id=".$code.'&from_begin='.$from_begin);
		die();	
		
	}else{
		header("Location: doc_outs.php");
		die();
	}
	 
	
	die();
	


}elseif(($action==1)&&(isset($_POST['doEdit'])||isset($_POST['doEditStay'])
	||isset($_POST['to_sign_1'])
	||isset($_POST['to_sign_2'])
	||isset($_POST['to_reg'])
	||isset($_POST['notActual'])
	||isset($_POST['doActual']))){


	 
	
	//редактирование возможно, если позволяет статус
	
	 
	$condition =in_array($_POST['current_status_id'],$_editable_status_id)&&in_array($editing_user['status_id'],$_editable_status_id);
	
	$_res=new DocOut_Resolver($editing_user['kind_id']);
	
	
	
		//поля формируем в зависимости от их активности в текущем статусе
	$_roles=$_res->rules_instance; //var_dump($_roles->GetTable());
	$field_rights0=$_roles->GetFields($editing_user, $result['id']);	
	$field_rights1=$_roles->GetFields($editing_user, $result['id'], $_POST['current_status_id']);
	$field_rights=array(); 
	foreach($field_rights0 as $k=>$v) $field_rights[$k]=$v&&$field_rights1[$k];
	
	
	
	
	$params=array();
	
	if($condition){
		
		//обычная загрузка прочих параметров
		
		//print_r($_POST); die();
	}
			
	if($field_rights['topic_txt'])	$params['topic']= SecStr($_POST['topic']);
	if($field_rights['topic_txt'])	$params['description']= SecStr($_POST['description']);
	
	if($field_rights['received_data'])	$params['received_who']= SecStr($_POST['received_who']);
	if($field_rights['received_data'])	$params['received_reg_no']= SecStr($_POST['received_reg_no']);
	if($field_rights['received_data'])	$params['received_pdate']= datefromdmy($_POST['received_pdate']);
	
		
		
		
	$_res->instance->Edit($id, $params);
	
	
	//$_dem->Edit($id, $params);
	//die();
	//запись в журнале
	//записи в лог. сравнить старые и новые записи
	foreach($params as $k=>$v){
		
		if(addslashes($editing_user[$k])!=$v){
			 
			
			$log->PutEntry($result['id'],'редактировал исходящий документ',NULL,1065, NULL, 'в поле '.$k.' установлено значение '.$v,$id);
			
					
		}
		
		
	}	
		
	if($condition){	
		
		// правим контрагентов, лиды, файлы, подписанты-согласов.
	  
 
		//контрагенты
		$_supplier=new SupplierItem;
		$_sg=new DocOut_SupplierGroup; 
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
				  $log->PutEntry($result['id'],'добавил контрагента в исходящий документ',NULL,1065,NULL,$description,$id);	
			  }elseif($v['action']==1){
				  $log->PutEntry($result['id'],'редактировал контрагента в исходящем документе',NULL,1065,NULL,$description,$id);
			  }elseif($v['action']==2){
				  $log->PutEntry($result['id'],'удалил контрагента из исходящего документа',NULL,1065,NULL,$description,$id);
			  }
			  
		  }
		
		
		  
		  //лиды
		 $positions=array();
		 $positions[]=array(
				  'doc_out_id'=>$id,
				   
				  'lead_id'=>abs((int)$_POST['lead_id']),
				  'pdate'=>time()
			  );
			   
		 $_ld=new DocOut_LeadGroup; $_lead=new Lead_Item;
		 // 
		 $log_entries=$_ld->AddItems($id, $positions, $result);
		  foreach($log_entries as $k=>$v){
			   $lead=$_lead->GetItemById($v['lead_id']);
			  
			  $description=SecStr($lead['code'].'');
			 
			  
			  if($v['action']==0){
				  $log->PutEntry($result['id'],'добавил лид в исходящий документ',NULL,1065,NULL,$description,$id);	
			  }elseif($v['action']==1){
				  $log->PutEntry($result['id'],'редактировал лид в исходящем документе',NULL,1065,NULL,$description,$id);
			  }elseif($v['action']==2){
				  $log->PutEntry($result['id'],'удалил лид из исходящего документа',NULL,1065,NULL,$description,$id);
			  }
			  
		  }
		  
		  
		  
		  //согласователи
		  $positions=array();
	 	  foreach($_POST as $k=>$v){
			  if(eregi("^new_sign_1_id_",$k)){
				  
				  $hash=eregi_replace("^new_sign_1_id_","",$k);
				  
				  $supplier_id=abs((int)$v);  
				  
				  
				 
				  $positions[]=array(
					  'doc_out_id'=>$id,
					   
					  'user_id'=>$supplier_id,
					  'sign_kind_id'=>1,
					  'is_signed'=> 0
				  );
				  
			  }
			}
		  
		  $_signs=new DocOut_SignGroup; $_ui=new UserSItem;
		  $log_entries=$_signs->AddItems($id, 1, $positions, $result);
		  
		 /* echo '<pre>';
		  print_r($positions);
		  print_r($log_entries);
		  
		  echo '</pre>';*/
		  
		  
		  foreach($log_entries as $k=>$v){
			   $user=$_ui->GetItemById($v['user_id']);
			  
			  $description=SecStr($user['name_s'].'');
			 
			  
			  if($v['action']==0){
				  $log->PutEntry($result['id'],'добавил согласователя в исходящий документ',NULL,1065,NULL,$description,$id);	
			  }elseif($v['action']==1){
				  $log->PutEntry($result['id'],'редактировал согласователя в исходящем документе',NULL,1065,NULL,$description,$id);
			  }elseif($v['action']==2){
				  $log->PutEntry($result['id'],'удалил согласователя из исходящего документа',NULL,1065,NULL,$description,$id);
			  }
			  
		  }	
		  
		  //подписанты
		  $positions=array();
	 	  foreach($_POST as $k=>$v){
			  if(eregi("^new_sign_2_id_",$k)){
				  
				  $hash=eregi_replace("^new_sign_2_id_","",$k);
				  
				  $supplier_id=abs((int)$v);;  
				  
				  
				 
				  $positions[]=array(
					  'doc_out_id'=>$id,
					   
					  'user_id'=>$supplier_id,
					  'sign_kind_id'=>2,
					  'is_signed'=> 0
				  );
				  
			  }
			}
		  
		  $_signs=new DocOut_SignGroup; $_ui=new UserSItem;
		  $log_entries=$_signs->AddItems($id, 2, $positions, $result);
		  
		 /* echo '<pre>';
		  print_r($positions);
		  print_r($log_entries);
		  
		  echo '</pre>';*/
		  
		  
		  foreach($log_entries as $k=>$v){
			   $user=$_ui->GetItemById($v['user_id']);
			  
			  $description=SecStr($user['name_s'].'');
			 
			  
			  if($v['action']==0){
				  $log->PutEntry($result['id'],'добавил подписанта в исходящий документ',NULL,1065,NULL,$description,$id);	
			  }elseif($v['action']==1){
				  $log->PutEntry($result['id'],'редактировал подписанта в исходящем документе',NULL,1065,NULL,$description,$id);
			  }elseif($v['action']==2){
				  $log->PutEntry($result['id'],'удалил подписанта из исходящего документа',NULL,1065,NULL,$description,$id);
			  }
			  
		  }	
		
	}
		       
	 
	 
	 
	//утверждение  заполнения
	
	$_res=new DocOut_Resolver($editing_user['kind_id']);
	
	if($field_rights['to_confirm']){	
	  if($editing_user['is_confirmed']==1){
		  //есть права: либо сам утв.+есть права, либо есть искл. права:
		  if(($au->user_rights->CheckAccess('w',1072))){
			  if((!isset($_POST['is_confirmed']))&&($_res->instance->DocCanUnconfirmPrice($id,$rss32))){
				  
				  
				  $_res->instance->Edit($id,array('is_confirmed'=>0, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,$result);
				  
				  $log->PutEntry($result['id'],'снял утверждение заполнения',NULL,1072, NULL, NULL,$id);	
				  
			  }
		  } 
		  
	  }else{
		  //есть права
		  if($au->user_rights->CheckAccess('w',1065)){
			  if(isset($_POST['is_confirmed'])&&($_POST['is_confirmed']==1)&&($_res->instance->DocCanConfirmPrice($id,$rss32))){
				  
				  $_res->instance->Edit($id,array('is_confirmed'=>1, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,$result);
				  
				  $log->PutEntry($result['id'],'утвердил заполнение',NULL,1065, NULL, NULL,$id);	
				  
				   
				  //die();
			  }
		  } 
	  }
	}
	
	
	//утверждение получения
	if($field_rights['to_receive']){	
	  if($editing_user['is_received']==1){
		  //есть права: либо сам утв.+есть права, либо есть искл. права:
		  if($au->user_rights->CheckAccess('w',1078)||($editing_user['manager_id']==$result['id'])){
			  if((!isset($_POST['is_received']))&&($_res->instance->DocCanUnconfirmReceive($id,$rss32))){
				  
				  
				  $_res->instance->Edit($id,array('is_received'=>0, 'user_received_id'=>$result['id'], 'receive_pdate'=>time()), true,$result);
				  
				  $log->PutEntry($result['id'],'снял утверждение получения',NULL,1078, NULL, NULL,$id);	
				  
			  }
		  } 
		  
	  }else{
		  //есть права
		  if($au->user_rights->CheckAccess('w',1077)||($editing_user['manager_id']==$result['id'])){
			  if(isset($_POST['is_received'])&&($_POST['is_received']==1)&&($_res->instance->DocCanConfirmReceive($id,$rss32))){
				  
				  $_res->instance->Edit($id,array('is_received'=>1, 'user_received_id'=>$result['id'], 'receive_pdate'=>time()),true,$result);
				  
				  $log->PutEntry($result['id'],'утвердил получение',NULL,1077, NULL, NULL,$id);	
				  
				   
				  //die();
			  }
		  } 
	  }
	}
		 
	
	//сохраняем подписи
	if($field_rights['to_sign_1_checks']){
		//анализируем данные формы
		$positions=array();
		foreach($_POST as $k=>$v){
			if(eregi("^new_is_signed_reason_1_",$k)){
				
				$hash=eregi_replace("^new_is_signed_reason_1_","",$k);
				
				$user_id=abs((int)$_POST['new_sign_1_id_'.$hash]);
				$is_signed=0;
				if(isset($_POST['new_is_signed_1_1_'.$hash])) $is_signed=1;
				elseif(isset($_POST['new_is_signed_1_2_'.$hash])) $is_signed=2;
				
				
				$positions[]=array(
					'doc_out_id'=>$id,
					 
					'user_id'=>$user_id,
					'sign_kind_id'=>1,
					'is_signed'=> $is_signed,
					'notes'=>SecStr($v)
				);
				
			}
		 }
		 
		
		  $_signs=new DocOut_SignGroup; $_ui=new UserSItem;
		  $log_entries=$_signs->SaveSigns($id, 1,  $positions, $editing_user, $result);
		  
		 /* echo '<pre>';
		  print_r($positions);
		  print_r($log_entries);
		  
		  echo '</pre>'; 
		  die();
		  */
		  
		  foreach($log_entries as $k=>$v){
			  $user=$_ui->GetItemById($v['user_id']);
			   
			  $user1=$_ui->GetItemById($v['user_sign_id']);
			  
			//  $description=SecStr($user['name_s'].'');
			 
			  
			 if($v['action']==1){
				  $description='Сотрудник '.$user1['name_s'].' ';
				  
				  if($v['is_signed']==0){
					  $description.=' снял согласование ';
				  }elseif($v['is_signed']==1){
					  $description.=' согласовал ';
				  }elseif($v['is_signed']==2){
					  $description.=' отправил на доработку ';  
				  }
				  
				  $description.=' документ '.$editing_user['code'].' при согласовании за сотрудника '.$user['name_s'];
				  $log->PutEntry($result['id'],'редактирование исходящего документа',NULL,1065,NULL,SecStr($description),$id);
			  }
			  
		  }	 
		  	
	}
	
	
	//сохраняем подписи
	if($field_rights['to_sign_2_checks']){
		//анализируем данные формы
		$positions=array();
		foreach($_POST as $k=>$v){
			if(eregi("^new_is_signed_reason_2_",$k)){
				
				$hash=eregi_replace("^new_is_signed_reason_2_","",$k);
				
				$user_id=abs((int)$_POST['new_sign_2_id_'.$hash]);
				$is_signed=0;
				if(isset($_POST['new_is_signed_2_1_'.$hash])) $is_signed=1;
				elseif(isset($_POST['new_is_signed_2_2_'.$hash])) $is_signed=2;
				
				
				$positions[]=array(
					'doc_out_id'=>$id,
					 
					'user_id'=>$user_id,
					'sign_kind_id'=>2,
					'is_signed'=> $is_signed,
					'notes'=>SecStr($v)
				);
				
			}
		 }
		
		 
		  $_signs=new DocOut_SignGroup; $_ui=new UserSItem;
		  $log_entries=$_signs->SaveSigns($id, 2,  $positions, $editing_user, $result);
		  
		 /* echo '<pre>';
		  print_r($positions);
		  print_r($log_entries);
		  
		  echo '</pre>'; 
		  die();
		  */
		  
		  foreach($log_entries as $k=>$v){
			  $user=$_ui->GetItemById($v['user_id']);
			   
			  $user1=$_ui->GetItemById($v['user_sign_id']);
			  
			//  $description=SecStr($user['name_s'].'');
			 
			  
			 if($v['action']==1){
				  $description='Сотрудник '.$user1['name_s'].' ';
				  
				  if($v['is_signed']==0){
					  $description.=' снял утверждение ';
				  }elseif($v['is_signed']==1){
					  $description.=' утвердил ';
				  }elseif($v['is_signed']==2){
					  $description.=' отправил на доработку ';  
				  }
				  
				  $description.=' документ '.$editing_user['code'].' при утверждении за сотрудника '.$user['name_s'];
				  $log->PutEntry($result['id'],'редактирование исходящего документа',NULL,1065,NULL,SecStr($description),$id);
			  }
			  
		  }	 
		  	
	}
	 
  
			 
	
	$_dsi=new DocStatusItem; 
	//обработка выделенных кнопок
 
	
	//обработка выделенных кнопок
	if(isset($_POST['to_sign_1'])){
		
		if($field_rights['to_sign_1']){
			
			$setted_status_id=41;
			$_res->instance->Edit($id,array( 'status_id'=>$setted_status_id),true, $result);
			
			$stat=$_dsi->GetItemById($setted_status_id);
			$log->PutEntry($result['id'],'смена статуса исх. документа',NULL,1065,NULL,'установлен статус '.$stat['name'],$id);
			
			//отправить письмо всем согласователям
			$_sgns=new DocOut_SignGroup;
			$_sgns->SendMessagesToSigners($id, 1);
			
			 
					
		}		
	}
	
	if(isset($_POST['to_sign_2'])){
		
		if($field_rights['to_sign_2']){
			
			$setted_status_id=43;
			$_res->instance->Edit($id,array( 'status_id'=>$setted_status_id),true, $result);
			
			$stat=$_dsi->GetItemById($setted_status_id);
			$log->PutEntry($result['id'],'смена статуса исх. документа',NULL,1065,NULL,'установлен статус '.$stat['name'],$id);
			
			//отправить письмо всем подписантам
			$_sgns=new DocOut_SignGroup;
			$_sgns->SendMessagesToSigners($id, 2); 
					
		}		
	}
	
	if(isset($_POST['to_reg'])){
		
		if($field_rights['to_reg']){
			
			$setted_status_id=44;
			$_res->instance->Edit($id,array( 'status_id'=>$setted_status_id),true, $result);
			
			//$_res->instance->Edit($id,array( 'status_id'=>$setted_status_id, 'reg_pdate'=>DateFromDMY($_POST['reg_pdate']), 'reg_no'=>SecStr($_POST['reg_no'])),true, $result);
			
			$stat=$_dsi->GetItemById($setted_status_id);
			$log->PutEntry($result['id'],'смена статуса исх. документа',NULL,1065,NULL,'установлен статус '.$stat['name'],$id);
			
			 
					
		}		
	}
		 
	 

	
	
	//die();
	
	//перенаправления
	if(isset($_POST['doEdit'])){
		header("Location: doc_outs.php#user_".$id);
		die();
	}elseif(isset($_POST['doEditStay'])
		||isset($_POST['to_sign_1'])
		||isset($_POST['to_sign_2'])
		||isset($_POST['to_reg'])
		||isset($_POST['notActual'])
		||isset($_POST['doActual'])){
	
	 
		header("Location: ed_doc_out.php?action=1&id=".$id.'&from_begin='.$from_begin);
		die();	
		
	}else{
		header("Location: doc_outs.php");
		die();
	}
	
	die();
}


 //журнал событий 
if($action==1){
	$log=new ActionLog;
	 
	$log->PutEntry($result['id'],'открыл исходящий документ',NULL,1065, NULL, 'исходящий документ № '.$editing_user['id'],$id);
	 
				
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

	$_menu_id=80;	
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
		$_kinds=new DocOut_KindGroup;
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
			
			$_res=new DocOut_Resolver($old_doc['kind_id']);
			
			$sm1->assign('kind_id', $old_doc['kind_id']); 
			
			
			
			
			$_leads=new DocOut_LeadGroup;
			$leads=$_leads->GetItemsByIdArr($old_doc['id']);
			
			$lead=$leads[0];
			$old_doc['lead_id']=$lead['lead_id'];
			$old_doc['lead_string']=$lead['code'];
		 
			//контрагенты
			$_suppliers=new DocOut_SupplierGroup;
			$sup=$_suppliers->GetItemsByIdArr($old_doc['id']);
			$sm1->assign('suppliers', $sup);
			
			
			
			//списки согласователей, подписантов
			$_signs=new DocOut_SignGroup;
			$sign1=$_signs->GetItemsByIdArr($old_doc['id'], 1, false, $editing_user, $result);
			$sign2=$_signs->GetItemsByIdArr($old_doc['id'], 2,  false, $editing_user, $result);
			//снять галочки, если есть
			foreach($sign1 as $k=>$v){ $sign1[$k]['is_signed']=0; $sign1[$k]['signer_1']=''; $sign1[$k]['signer_2']=''; }
			foreach($sign2 as $k=>$v){ $sign2[$k]['is_signed']=0; $sign2[$k]['signer_1']=''; $sign2[$k]['signer_2']=''; }
			
			
			$sm1->assign('sign1', $sign1); $sm1->assign('sign2', $sign2); 
			
			
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
			  
			  $ffg=new DocOutFileGroup(1,  $old_doc['id'],  new FileDocFolderItem(1,  $old_doc['id'], new DocOutFileItem(1)));;
			  
			  $filetext=$ffg->ShowFiles('doc_out/uploaded_files_list.html',  $decorator,$from,$to_page,'ed_doc_out.php', 'doc_out_file.html', 'swfupl-js/doc_out_files.php',  
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
			
			
			//ответ на вх док
			$_doc_r=new DocIn_AbstractItem;
			$doc_in_id= $old_doc['doc_in_id'];
			$doc=$_doc_r->getitembyid($doc_in_id);
			
			$sm1->assign('doc_in', $doc);
			
			$sm1->assign('old_doc', $old_doc);
		//копируем данные
		}elseif(isset($_GET['doc_in_id'])){	
			//ответ на вх док
			 
			$_doc_r=new DocIn_AbstractItem;
			$doc_in_id= abs((int)$_GET['doc_in_id']);
			$doc=$_doc_r->getitembyid($doc_in_id);
			//$_doc_res=new DocIn_Resolver($doc['id']);
			
			$sm1->assign('doc_in_id', $doc_in_id);
			$sm1->assign('doc_in', $doc);
			
			$sm1->assign('kind_id', 1); 
			
			$old_doc=array();
				
			$_leads=new DocIn_LeadGroup;
			$leads=$_leads->GetItemsByIdArr($doc_in_id);
			
			
				
			$lead=$leads[0];
			$old_doc['lead_id']=$lead['lead_id'];
			$old_doc['lead_string']=$lead['code'];
			
			//var_dump($lead);
		 
			//контрагенты
			$_suppliers=new DocIn_SupplierGroup;
			$sup=$_suppliers->GetItemsByIdArr($doc_in_id);
			$sm1->assign('suppliers', $sup);
			
			
			
			$old_doc['topic']='Ответ на: '.$doc['topic'];
			$old_doc['doc_in_id']=$doc['id'];
			 
			$sm1->assign('old_doc', $old_doc);
			
			
		}else{
			//не копируем - форма создания
			   
			 
		}
		
		
		//получим список тех, кто может снять утверждение заполнения
		$_usg1=new UsersSGroup;
		$usg1=$_usg1->GetUsersByRightArr('w', 1072);
		$sm1->assign('can_unconfirm_users',$usg1);
		$sm1->assign('can_unconfirm',$au->user_rights->CheckAccess('w',1072));
		
		
		 
		$sm1->assign('session_id', session_id());
		
		$sm1->assign('can_add_supplier', $au->user_rights->CheckAccess('w',87));
		
	 
		
		$sm1->assign('can_confirm', $au->user_rights->CheckAccess('w',1064));
		
		
		
		
		$user_form=$sm1->fetch('doc_out/create_doc_out.html');
		 
		 
	
	
	 }elseif($action==1){
		//редактирование позиции
		
		if($print==0) $print_add='';
		else $print_add='_print';
		
		
		$_res=new  DocOut_Resolver($editing_user['kind_id']);
		
		
		//построим доступы
		$_roles=$_res->rules_instance;
		$field_rights=$_roles->GetFields($editing_user, $result['id']);
		$sm1->assign('field_rights', $field_rights);
		
		 
		
		//вход док-т
		$_doc_r=new DocIn_AbstractItem;
		$doc_in_id= $editing_user['doc_in_id'];
		$doc=$_doc_r->getitembyid($doc_in_id);
		//$_doc_res=new DocIn_Resolver($doc['id']);
		
		$sm1->assign('doc_in_id', $doc_in_id);
		$sm1->assign('doc_in', $doc);
		
		$sm1->assign('manager_id', $editing_user['manager_id']);
		$_uis=new UserSItem; $uis=$_uis->getitembyid($editing_user['manager_id']);
		$editing_user['manager_string']= $uis['name_s'];
		
		 
		$_kind=new DocOut_KindItem; $kind=$_kind->getitembyid($editing_user['kind_id']);
		$editing_user['kind_name']=$kind['name'];
		
		$_leads=new DocOut_LeadGroup;
		$leads=$_leads->GetItemsByIdArr($editing_user['id']);
		
		$lead=$leads[0];
		$editing_user['lead_id']=$lead['lead_id'];
		$editing_user['lead_string']=$lead['code'];
		
		
		if($editing_user['reg_pdate']!=0) $editing_user['reg_pdate']=date('d.m.Y', $editing_user['reg_pdate']);
		
		$editing_user['pdate']=date('d.m.Y H:i:s', $editing_user['pdate']);
		
		if($editing_user['received_pdate']!=0) $editing_user['received_pdate']=date('d.m.Y', $editing_user['received_pdate']);
		
		if($editing_user['send_pdate']!=0) $editing_user['send_pdate']=date('d.m.Y', $editing_user['send_pdate']);
		
		//кто отправил
		$_user=new UserSItem;
		$send_who=$_user->GetItemById($editing_user['send_user_id']);
		$sm1->assign('send_who', $send_who['name_s']);
		
		
		//списки согласователей, подписантов
		$_signs=new DocOut_SignGroup;
		$sign1=$_signs->GetItemsByIdArr($editing_user['id'], 1, false, $editing_user, $result);
		$sign2=$_signs->GetItemsByIdArr($editing_user['id'], 2,  false, $editing_user, $result);
		
		$sm1->assign('sign1', $sign1); $sm1->assign('sign2', $sign2); 
		
		 
		 
		
		 
		  
		
		//возможность РЕДАКТИРОВАНИЯ - 
			//пол-ль - создал
	//или пол-ль - в списке видящих
	 	
		//$can_modify=true;
		
		$can_modify=in_array($editing_user['status_id'],$_editable_status_id);
		
		 
		
		 
		//контрагенты
		$_suppliers=new DocOut_SupplierGroup;
		$sup=$_suppliers->GetItemsByIdArr($editing_user['id']);
		$sm1->assign('suppliers', $sup);
		 

		$can_modify_ribbon=!in_array($editing_user['status_id'],array(3,46));
	 
	 	//лента документа
		$_hg=new DocOut_HistoryGroup;
		$history= $_hg->ShowHistory(
			$editing_user['id'],
			 'doc_out/lenta'.$print_add.'.html', 
			 new DBDecorator(), 
			 $can_modify_ribbon,
			 true,
			 false,
			 $result,
			 $au->user_rights->CheckAccess('w',1066),
			 $au->user_rights->CheckAccess('w',1067),$history_data,true,true
			 );
		$sm1->assign('lenta',$history);
		$sm1->assign('lenta_len',count($history_data));
		 
		 
		
		
		//блок аннулирования
		
		$editing_user['can_annul']=$_res->instance->DocCanAnnul($editing_user['id'],$reason,$editing_user)&&$au->user_rights->CheckAccess('w',1065);
		if(!$au->user_rights->CheckAccess('w',1065)) $reason='недостаточно прав для данной операции';
		$editing_user['can_annul_reason']=$reason;
		
		 
		
		$editing_user['can_restore']=$_res->instance->DocCanRestore($editing_user['id'],$reason,$editing_user)&&$au->user_rights->CheckAccess('w',1071);
			if(!$au->user_rights->CheckAccess('w',1071)) $reason='недостаточно прав для данной операции';
		
		
		
		
			
		 
		
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
		if($editing_user['is_received']==0){
			
			  
		  
		  if($editing_user['is_confirmed']==1){
			  if($au->user_rights->CheckAccess('w',1072)){
				  //есть права + сам утвердил
				  $can_confirm_price=true;	
			  }else{
				  $can_confirm_price=false;
			  }
		  }else{
			  //95
			  $can_confirm_price=$au->user_rights->CheckAccess('w',1065)&&in_array($editing_user['status_id'],$_editable_status_id)  ;
		  }
		}
		$sm1->assign('can_confirm',$can_confirm_price);
		
		
		//блок утв. получения
		if(($editing_user['is_received']==1)&&($editing_user['user_received_id']!=0)){
			$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['user_received_id']);
			$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.date("d.m.Y H:i:s",$editing_user['receive_pdate']);
			
			$sm1->assign('is_received_confirmer',$confirmer);
		}
		
		$can_confirm_shipping=false;
		if($editing_user['is_confirmed']==1){
		
		  if($editing_user['is_received']==1){
			 $can_confirm_shipping=$au->user_rights->CheckAccess('w',1078)||($editing_user['manager_id']==$result['id']);
			 
		  }else{
			  //95
			  $can_confirm_shipping=$au->user_rights->CheckAccess('w',1077)||($editing_user['manager_id']==$result['id']);
		  }
		}
		// + есть галочка утв. цен
		$can_confirm_shipping=$can_confirm_shipping&&($editing_user['is_confirmed']==1);
		
		
		$sm1->assign('can_confirm_receive',$can_confirm_shipping);
		  
		
		
		 
		
		//получим список тех, кто может снять утверждение заполнения
		$_usg1=new UsersSGroup;
		$usg1=$_usg1->GetUsersByRightArr('w', 1072);
		$sm1->assign('can_unconfirm_users',$usg1);
		$sm1->assign('can_unconfirm',$au->user_rights->CheckAccess('w',1072));
		
		
		 
		$sm1->assign('session_id', session_id());
		
		$sm1->assign('can_add_supplier', $au->user_rights->CheckAccess('w',87));
		
		$sm1->assign('can_copy', $au->user_rights->CheckAccess('w',1069));
		
		
		
		
		//связанные вх док-ты
		$_out_g=new DocIn_AbstractGroup;
		$decorator=new DBDecorator();
		$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
		$decorator->AddEntry(new SqlEntry('p.id',$editing_user['doc_in_id'], SqlEntry::E));
		
		$binded=$_out_g->ShowPosArrByDec($decorator);
		$sm1->assign('binded',$binded);
		 
		
		
		$sm1->assign('can_modify', $can_modify);
		
		
		//для заметки - возможность править контрагентов
		$sm1->assign('can_modify_suppliers', $can_modify);
		  

		
		 
		
		
		$sm1->assign('can_create', $au->user_rights->CheckAccess('w',1064));  
		
		
		
		//$sm1->assign('statuses', $_dem->GetStatuses($editing_user['status_id']));
		
		
		 
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
			  
			  $ffg=new DocOutFileGroup(1,  $id,  new FileDocFolderItem(1,  $id, new DocOutFileItem(1)));;
			  
			  $filetext=$ffg->ShowFiles('doc_file/incard_list.html',  $decorator,$from,$to_page,'ed_doc_out.php', 'doc_out_file.html', 'swfupl-js/doc_out_files.php',  
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
				$user_form=$sm1->fetch('doc_out/edit_kind_1'.$print_add.'.html');
			break;
			
			case 2:	
				$user_form=$sm1->fetch('doc_out/edit_kind_2'.$print_add.'.html');
			break;
			case 3:	
				$user_form=$sm1->fetch('doc_out/edit_kind_3'.$print_add.'.html');
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
		
			$decorator->AddEntry(new SqlEntry('object_id',NULL, SqlEntry::IN_VALUES, NULL, array(1061,
1064,
1065,
1066,
1067,
1068,
1069,
1070,
1071,
1072,
1073,
1074,
1075,
1076,
1077,
1078

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
			 
			$llg=$log->ShowLog('syslog/log_doc.html',$decorator,$from,$to_page,'ed_doc_out.php',true,true,true);
			
			$sm->assign('syslog',$llg);		
				
		} 
		
		
	}
	
	
	$sm->assign('print_pdate', date("d.m.Y H:i:s"));
	//$username=$result['login'];
	$username=stripslashes($result['name_s']); //.' '.$username;	
	$sm->assign('print_username',$username);
	
	$sm->assign('users',$user_form);
	$content=$sm->fetch('doc_out/ed_doc_out_page'.$print_add.'.html');
	
	
	
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