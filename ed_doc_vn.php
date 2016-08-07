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

 
 require_once('classes/pl_currgroup.php');
require_once('classes/pl_curritem.php');
 
require_once('classes/orgitem.php');
require_once('classes/opfitem.php');

 

require_once('classes/suppliercontactitem.php');
require_once('classes/supcontract_group.php');



 
require_once('classes/doc_vn_filegroup.php');
require_once('classes/doc_vn_fileitem.php');

require_once('classes/supplier_country_group.php');
require_once('classes/supplier_city_item.php');

require_once('classes/supplier_cities_group.php');

require_once('classes/doc_vn.class.php');
require_once('classes/lead.class.php');
require_once('classes/doc_vn1_field_rules.php');

require_once('classes/sched.class.php');
require_once('classes/petitiongroup.php');

$_pch=new PeriodChecker;
$pch_date=$_pch->GetDate();



$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Служебная записка на командировку');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}


$_dem=new DocVn_AbstractItem;

$_plan=new DocVn_AbstractGroup;
 

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
	$object_id[]=1090;
	break;
	case 1:
	$object_id[]=1091;
	$object_id[]=1102;
	break;
	case 2:
	$object_id[]=1091;
	break;
	default:
	$object_id[]=1091;
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
	
	 
	//если отчет - то перейти на страницу отчета
	if($editing_user['kind_id']==2){
		header("Location: ed_doc_vn_ot.php?action=1&id=".$id);
		die();
	} 
	foreach($editing_user as $k=>$v) $editing_user[$k]=stripslashes($v);
	
	//видим:
	$_res=new DocVn_Resolver($editing_user['kind_id']);
	
	
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
	if(!$au->user_rights->CheckAccess('w',1091)){
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
		
		$log->PutEntry($result['id'],'удалил файл вн. документа',NULL, 1091,NULL,'имя файла '.SecStr($file['orig_name']));
	}
	
	header("Location: ed_doc_vn.php?action=1&id=".$id.'&folder_id='.$folder_id);
	die();
}


 



//обработка данных

if(($action==0)&&(isset($_POST['doNew'])||isset($_POST['doNewEdit']))){
	 
	
	
	
	
	$params=array();
	//обычная загрузка прочих параметров
	$params['created_id']=$result['id'];
	
	$params['kind_id']=abs((int)$_POST['kind_id']);
	$params['manager_id']=abs((int)$_POST['manager_id']);
	
	$params['sched_id']= SecStr($_POST['sched_id']);
	
	//$params['description']= SecStr($_POST['description']);
	
	$params['status_id']=18;
		
	$params['pdate']=datefromdmy($_POST['pdate']);
	
 	$params['vyh_reason_id']= abs((int)$_POST['vyh_reason_id']);
	

	if(isset($_POST['vyh_later'])) $params['vyh_later']=1; else $params['vyh_later']=0;
	
	
	$code=$_dem->Add($params);	
	 
	//$code=1;
	//запись в журнале
	if($code>0){
		$log->PutEntry($result['id'],'создал внутренний документ',NULL,1090,NULL,NULL,$code);	
		
		foreach($params as $k=>$v){
			
		  
				
				$log->PutEntry($result['id'],'создал внутренний документ',NULL,1090, NULL, 'в поле '.$k.' установлено значение '.$v,$code);		
			 
		}
		
	
	$_res=new DocVn_Resolver($params['kind_id']);
	 
	
	// создадим расходы, лиды, файлы, дни отдыха
	  	//расходы...
		//словарь затрат
		
		$positions=array();
		foreach($_POST as $k=>$v) if(eregi("^p_hash_",$k)){
			$hash=$v;
			
			
			$expenses_id=abs((int)$_POST['p_id_'.$hash]);
			if($expenses_id==0) continue;
			
			$data=array();
			$data['doc_vn_id']=$code;
				 
			$data['expenses_id']=$expenses_id;
			if(isset($_POST['exp_plan_'.$hash])) $data['plan']=abs((float)$_POST['exp_plan_'.$hash]);
			if(isset($_POST['exp_fact_'.$hash])) $data['fact']=abs((float)$_POST['exp_fact_'.$hash]);
			
			if(isset($_POST['exp_plan_currency_id_'.$hash])) $data['plan_currency_id']=abs((int)$_POST['exp_plan_currency_id_'.$hash]);
			if(isset($_POST['exp_fact_currency_id_'.$hash])) $data['fact_currency_id']=abs((int)$_POST['exp_fact_currency_id_'.$hash]);
			
			if(isset($_POST['exp_fact_l_or_korp_'.$hash])) $data['fact_l_or_korp']=abs((int)$_POST['exp_fact_l_or_korp_'.$hash]);
			
			
			if(isset($_POST['exp_doc_name_'.$hash])) $data['doc_name']=SecStr($_POST['exp_doc_name_'.$hash])	;
			if(isset($_POST['exp_doc_no_'.$hash])) $data['doc_no']=SecStr($_POST['exp_doc_no_'.$hash])	;
			if($_POST['exp_doc_pdate_'.$hash]=="") $data['doc_pdate']=NULL; else $data['doc_pdate']=datefromdmy($_POST['exp_doc_pdate_'.$hash])	;
			
			if(isset($_POST['p_pdate_'.$hash])) $data['pdate']=abs((int)$_POST['p_pdate_'.$hash])	;
			
			$positions[]=$data;
		}
		
		$log_entries=$_res->instance->AddPositions($code, $positions, $result);
		
		/*
		echo '<pre>';
		print_r($positions);
		echo '</pre>';
		die();*/
		
		//заносим в журнал сведения о добавке позиций
		$_pos=new DocVn_ExpensesKindsItem;  $_curr_itm=new PlCurrItem; 
		foreach($log_entries as $k=>$v){
			  
			$pos=$_pos->GetItemById($v['expenses_id']); $curr1=$_curr_itm->GetItemById($v['plan_currency_id']); $curr2=$_curr_itm->GetItemById($v['fact_currency_id']);
			if($pos!==false) {
			  
			  $description=SecStr($pos['name'].'<br />  план. '.$v['plan'].' '.$curr1['signature'].',  факт. '.$v['fact'].' '.$curr2['signature']);
			  
			  if($v['fact_l_or_korp']==0) $description.=', наличные или личная карта';
			  else $description.=', корпоративная карта';
			  
			  $description.=', документ '.$v['doc_name'].', № '.$v['doc_no'];
			  if($data['doc_pdate']!=NULL) $description.=', дата '.date('d.m.Y',$v['doc_pdate']);
				
			  if($v['action']==0){
				  $log->PutEntry($result['id'],'добавил позицию затрат по входящему документу',NULL,1090,NULL,$description,$code);	
			  }elseif($v['action']==1){
				  $log->PutEntry($result['id'],'редактировал позицию затрат по входящему документу',NULL,1090,NULL,$description,$code);
			  }elseif($v['action']==2){
				  $log->PutEntry($result['id'],'удалил позицию затрат по входящему документу',NULL,1090,NULL,$description,$code);
			  }
			  
			}
		  }  
		
 
		 
		
		  //файлы
		  $fmi=new DocVnFileItem;
			foreach($_POST as $k=>$v){
			  if(eregi("^upload_file_",$k)){
					$filename=eregi_replace("^upload_file_", '', eregi_replace("_tmp$",".tmp",$k));
				  $fmi->Add(array('bill_id'=>$code, 'filename'=>SecStr(basename($filename)), 'orig_name'=>SecStr($v), 'user_id'=>$result['id'], 'pdate'=>time()));
				  
				   $log->PutEntry($result['id'], 'прикрепил файл к входящему документу', NULL, 1090, NULL,'Служебное имя файла: '.SecStr(basename($filename)).' Имя файла: '.SecStr($v),$code);
				   
				   
			  }
			}
	 
		  
		  //лиды
		 $positions=array();
		 $positions[]=array(
				  'doc_out_id'=>$code,
				   
				  'lead_id'=>abs((int)$_POST['lead_id']),
				  'pdate'=>time()
			  );
			   
		 $_ld=new DocVn_LeadGroup; $_lead=new Lead_Item;
		 // 
		 $log_entries=$_ld->AddItems($code, $positions, $result);
		  foreach($log_entries as $k=>$v){
			   $lead=$_lead->GetItemById($v['lead_id']);
			  
			  $description=SecStr($lead['code'].'');
			 
			  
			  if($v['action']==0){
				  $log->PutEntry($result['id'],'добавил лид в внутренний документ',NULL,1090,NULL,$description,$code);	
			  }elseif($v['action']==1){
				  $log->PutEntry($result['id'],'редактировал лид в входящем документе',NULL,1090,NULL,$description,$code);
			  }elseif($v['action']==2){
				  $log->PutEntry($result['id'],'удалил лид из входящего документа',NULL,1090,NULL,$description,$code);
			  }
			  
		  }
		  
		  
		  //даты отдыха
		  //добавление дат отпуска за работу в выходные
		$positions=array();	
		foreach($_POST as $k=>$v){
		  if(eregi("^new_vyh_otp_date_pdate_",$k)){
			  
			  $hash=eregi_replace("^new_vyh_otp_date_pdate_","",$k);
			   $positions[]=array(
					  'doc_vn_id'=>$code,
					  
					  
					  'pdate'=>Datefromdmy($_POST['new_vyh_otp_date_pdate_'.$hash])
					   
				  );
		  }
		}
		
		//внесем даты
		$_block=new DocVn_VyhDateGroup;
		$log_entries=$_block->AddVyhDates($code,$positions);
		//die();
		//запишем в журнал
		foreach($log_entries as $k=>$v){
			 
				$descr=SecStr('Дата '.date('d.m.Y',$v['pdate']));
				 
				
				$log->PutEntry($result['id'],'добавил дату отпуска за работу в выходные', NULL, 1090,NULL,$descr,$code);	
		 
		} 
		  
			
			
		  //желаете ли вы утвердить?				  
		  if($au->user_rights->CheckAccess('w',1090)&&($_POST['do_confirm']==1)){
		  	  
			  $_res=new DocVn_Resolver($params['kind_id']);
		  
			  $_res->instance->Edit($code,array('is_confirmed'=>1, 'user_confirm_id'=>$result['id'], ' 	confirm_pdate'=>time()),true,$result);
							
			  $log->PutEntry($result['id'],'автоматически утвердил заполнение входящего документа',NULL,1090, NULL, NULL,$code);	
			  
			  
		  }else{
			  $log->PutEntry($result['id'],'отказался от автоматического утверждения заполнения входящего документа',NULL,1090, NULL, NULL,$code);	
		  }
	
	}
	
	
	
	
	//перенаправления
	if(isset($_POST['doNew'])){
		header("Location: doc_inners.php#user_".$code);
		die();
	}elseif(isset($_POST['doNewEdit'])){
		//если есть доступ к объекту 11 - правка пользователя - то переход туда		
		 
		header("Location: ed_doc_vn.php?action=1&id=".$code.'&from_begin='.$from_begin);
		die();	
		
	}else{
		header("Location: doc_inners.php");
		die();
	}
	 
	
	die();
	


}elseif(($action==1)&&(isset($_POST['doEdit'])||isset($_POST['doEditStay'])
	||isset($_POST['send_ruk_sz'])
	||isset($_POST['to_rework_sz'])
	||isset($_POST['send_dir_sz'])
	||isset($_POST['to_rework_ot'])
	||isset($_POST['send_ruk_ot'])
	||isset($_POST['send_dir_ot']))){


	 
	
	//редактирование возможно, если позволяет статус
	
	 
	$condition =in_array($_POST['current_status_id'],$_editable_status_id)&&in_array($editing_user['status_id'],$_editable_status_id);
	
	$_res=new DocVn_Resolver($editing_user['kind_id']);
	
	
	
		//поля формируем в зависимости от их активности в текущем статусе
	$_roles=$_res->rules_instance; //var_dump($_roles->GetTable());
	$field_rights0=$_roles->GetFields($editing_user, $result['id']);	
	$field_rights1=$_roles->GetFields($editing_user, $result['id'], $_POST['current_status_id']);
	$field_rights=array(); 
	foreach($field_rights0 as $k=>$v) $field_rights[$k]=$v&&$field_rights1[$k];
	
	
	
	
	$params=array();
	
	if($condition){
		
		//обычная загрузка прочих параметров
//		$params['manager_id']=abs((int)$_POST['manager_id']);
	//	if(isset($_POST['is_urgent'])) $params['is_urgent']=1; else $params['is_urgent']=0;
		
		//print_r($_POST); die();
	}
			
	if($field_rights['common'])	{
		
		$params['pdate']= datefromdmy($_POST['pdate']);
	 
		$params['manager_id']=abs((int)$_POST['manager_id']);
		
		$params['sched_id']= SecStr($_POST['sched_id']);
	
	 	$params['vyh_reason_id']= abs((int)$_POST['vyh_reason_id']);
		
		if(isset($_POST['vyh_later'])) $params['vyh_later']=1; else $params['vyh_later']=0;
	}
	
	 
		
		
	$_res->instance->Edit($id, $params);
	
	
	//$_dem->Edit($id, $params);
	//die();
	//запись в журнале
	//записи в лог. сравнить старые и новые записи
	foreach($params as $k=>$v){
		
		if(addslashes($editing_user[$k])!=$v){
			 
			
			$log->PutEntry($result['id'],'редактировал внутренний документ',NULL,1091, NULL, 'в поле '.$k.' установлено значение '.$v,$id);
			
					
		}
		
		
	}	
	
	if($field_rights['plan']||$field_rights['fact']){
		$positions=array();
		foreach($_POST as $k=>$v) if(eregi("^p_hash_",$k)){
			$hash=$v;
			
			
			$expenses_id=abs((int)$_POST['p_id_'.$hash]);
			if($expenses_id==0) continue;
			
			$data=array();
			$data['doc_vn_id']=$id;
				 
			$data['expenses_id']=$expenses_id;
			if(isset($_POST['exp_plan_'.$hash])) $data['plan']=abs((float)$_POST['exp_plan_'.$hash]);
			if(isset($_POST['exp_fact_'.$hash])) $data['fact']=abs((float)$_POST['exp_fact_'.$hash]);
			
			if(isset($_POST['exp_plan_currency_id_'.$hash])) $data['plan_currency_id']=abs((int)$_POST['exp_plan_currency_id_'.$hash]);
			if(isset($_POST['exp_fact_currency_id_'.$hash])) $data['fact_currency_id']=abs((int)$_POST['exp_fact_currency_id_'.$hash]);
			
			if(isset($_POST['exp_fact_l_or_korp_'.$hash])) $data['fact_l_or_korp']=abs((int)$_POST['exp_fact_l_or_korp_'.$hash]);
			
			
			if(isset($_POST['exp_doc_name_'.$hash])) $data['doc_name']=SecStr($_POST['exp_doc_name_'.$hash])	;
			if(isset($_POST['exp_doc_no_'.$hash])) $data['doc_no']=SecStr($_POST['exp_doc_no_'.$hash])	;
			if($_POST['exp_doc_pdate_'.$hash]=="") $data['doc_pdate']=NULL; else $data['doc_pdate']=datefromdmy($_POST['exp_doc_pdate_'.$hash])	;
			
			if(isset($_POST['p_pdate_'.$hash])) $data['pdate']=abs((int)$_POST['p_pdate_'.$hash])	;
			
			$positions[]=$data;
		}
		
		$log_entries=$_res->instance->AddPositions($id, $positions, $result);
		
		/*
		echo '<pre>';
		print_r($positions);
		echo '</pre>';
		die();*/
		
		//заносим в журнал сведения о добавке позиций
		$_pos=new DocVn_ExpensesKindsItem;  $_curr_itm=new PlCurrItem; 
		foreach($log_entries as $k=>$v){
			  
			$pos=$_pos->GetItemById($v['expenses_id']); $curr1=$_curr_itm->GetItemById($v['plan_currency_id']); $curr2=$_curr_itm->GetItemById($v['fact_currency_id']);
			if($pos!==false) {
			  
			  $description=SecStr($pos['name'].'<br />  план. '.$v['plan'].' '.$curr1['signature'].',  факт. '.$v['fact'].' '.$curr2['signature']);
			  
			  if($v['fact_l_or_korp']==0) $description.=', наличные или личная карта';
			  else $description.=', корпоративная карта';
			  
			  $description.=', документ '.$v['doc_name'].', № '.$v['doc_no'];
			  if($data['doc_pdate']!=NULL) $description.=', дата '.date('d.m.Y',$v['doc_pdate']);
				
			  if($v['action']==0){
				  $log->PutEntry($result['id'],'добавил позицию затрат по входящему документу',NULL,1090,NULL,$description,$id);	
			  }elseif($v['action']==1){
				  $log->PutEntry($result['id'],'редактировал позицию затрат по входящему документу',NULL,1090,NULL,$description,$id);
			  }elseif($v['action']==2){
				  $log->PutEntry($result['id'],'удалил позицию затрат по входящему документу',NULL,1090,NULL,$description,$id);
			  }
			  
			}
		  }  
		
		
	}
	
		
	if($field_rights['common'])	{	
		
		// правим  ,  лиды, файлы, 
	  
 		
		 
		  
		  //лиды
		 $positions=array();
		 $positions[]=array(
				  'doc_out_id'=>$id,
				   
				  'lead_id'=>abs((int)$_POST['lead_id']),
				  'pdate'=>time()
			  );
			   
		 $_ld=new DocVn_LeadGroup; $_lead=new Lead_Item;
		 // 
		 $log_entries=$_ld->AddItems($id, $positions, $result);
		  foreach($log_entries as $k=>$v){
			   $lead=$_lead->GetItemById($v['lead_id']);
			  
			  $description=SecStr($lead['code'].'');
			 
			  
			  if($v['action']==0){
				  $log->PutEntry($result['id'],'добавил лид во внутренний документ',NULL,1091,NULL,$description,$id);	
			  }elseif($v['action']==1){
				  $log->PutEntry($result['id'],'редактировал лид во входящем документе',NULL,1091,NULL,$description,$id);
			  }elseif($v['action']==2){
				  $log->PutEntry($result['id'],'удалил лид из входящего документа',NULL,1091,NULL,$description,$id);
			  }
			  
		  }
		  
		  
		    //даты отдыха
		  //добавление дат отпуска за работу в выходные
		$positions=array();	
		foreach($_POST as $k=>$v){
		  if(eregi("^new_vyh_otp_date_pdate_",$k)){
			  
			  $hash=eregi_replace("^new_vyh_otp_date_pdate_","",$k);
			   $positions[]=array(
					  'doc_vn_id'=>$id,
					  
					  
					  'pdate'=>Datefromdmy($_POST['new_vyh_otp_date_pdate_'.$hash])
					   
				  );
		  }
		}
		
		//внесем даты
		$_block=new DocVn_VyhDateGroup;
		$log_entries=$_block->AddVyhDates($id,$positions);
		//die();
		//запишем в журнал
		foreach($log_entries as $k=>$v){
			 
				$descr=SecStr('Дата '.date('d.m.Y',$v['pdate']));
				 
				 
				 
			  if($v['action']==0){
				 	$log->PutEntry($result['id'],'добавил дату отпуска за работу в выходные', NULL, 1091,NULL,$descr,$id);	
			  }elseif($v['action']==1){
				  $log->PutEntry($result['id'],'редактировал дату отпуска за работу в выходные', NULL, 1091,NULL,$descr,$id);	
			  }elseif($v['action']==2){
				  $log->PutEntry($result['id'],'удалил дату отпуска за работу в выходные', NULL, 1091,NULL,$descr,$id);	
			  }
		 
		} 
		
	}
		       
	 
	 
	 
	//утверждение  заполнения
	
	$_res=new DocVn_Resolver($editing_user['kind_id']);
	
	if($field_rights['to_confirm']){	
	  if($editing_user['is_confirmed']==1){
		  //есть права: либо сам утв.+есть права, либо есть искл. права:
		  if(($au->user_rights->CheckAccess('w',1098))){
			  if((!isset($_POST['is_confirmed']))&&($_res->instance->DocCanUnconfirmPrice($id,$rss32))){
				  
				  
				  $_res->instance->Edit($id,array('is_confirmed'=>0, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,$result);
				  
				  $log->PutEntry($result['id'],'снял утверждение заполнения',NULL,1098, NULL, NULL,$id);	
				  
			  }
		  } 
		  
	  }else{
		  //есть права
		  if($au->user_rights->CheckAccess('w',1091)){
			  if(isset($_POST['is_confirmed'])&&($_POST['is_confirmed']==1)&&($_res->instance->DocCanConfirmPrice($id,$rss32))){
				  
				  $_res->instance->Edit($id,array('is_confirmed'=>1, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,$result);
				  
				  $log->PutEntry($result['id'],'утвердил заполнение',NULL,1091, NULL, NULL,$id);	
				  
				   
				  //die();
			  }
		  } 
	  }
	}
	
	//СЗ согласовано/не согласовано
	if($field_rights['to_ruk_sz']){	
	  
		$_ug=new DocVn_UsersSGroup;
		$_ui=new UserSItem; $uis=$_ui->getitembyid($editing_user['manager_id']);
		$user_ruk=$_ug->GetRuk($uis);
		// var_dump($user_ruk);
		
	   	if($editing_user['is_ruk']==1){
			
			 
		  //есть права: либо сам утв.+есть права, либо есть искл. права:
		  if(($au->user_rights->CheckAccess('w',1100))||($user_ruk['id']==$result['id'])){
			  if((!isset($_POST['is_ruk']))){
				  
				  
				  $_res->instance->Edit($id,array('is_ruk'=>0, 'user_ruk_id'=>$result['id'], 'ruk_pdate'=>time()),true,$result);
				  
				  $log->PutEntry($result['id'],'снял утверждение СЗ рук-лем отдела',NULL,1091, NULL, NULL,$id);	
				  
			  }
		  } 
		  
	  }else{
		  //есть права
		  if($au->user_rights->CheckAccess('w',1099)||($user_ruk['id']==$result['id'])){
			  if(isset($_POST['is_ruk'])&&($_POST['is_ruk']==1)){
				  
				  $_res->instance->Edit($id,array('is_ruk'=>1, 'user_ruk_id'=>$result['id'], 'ruk_pdate'=>time()),true,$result);
				  
				  $log->PutEntry($result['id'],'утвердил СЗ рук-лем отдела',NULL,1091, NULL, NULL,$id);	
				  
				   
				  //die();
			  }
		  } 
	  }
	}
	
	
	//СЗ утверждено/не утверждено
	if($field_rights['to_dir_sz']){	
	  
		$_ug=new DocVn_UsersSGroup;
		$_ui=new UserSItem; $uis=$_ui->getitembyid($editing_user['manager_id']);
		$user_ruk=$_ug->GetDir($uis);
		// var_dump($user_ruk);
		
	   	if($editing_user['is_dir']==1){
			
			 
		  //есть права: либо сам утв.+есть права, либо есть искл. права:
		  if(($au->user_rights->CheckAccess('w',1112))||($user_ruk['id']==$result['id'])){
			  if((!isset($_POST['is_dir']))){
				  
				  
				  $_res->instance->Edit($id,array('is_dir'=>0, 'user_dir_id'=>$result['id'], 'dir_pdate'=>time()),true,$result);
				  
				  $log->PutEntry($result['id'],'снял утверждение СЗ ген. директором',NULL,1091, NULL, NULL,$id);	
				  
			  }
		  } 
		  
	  }else{
		  //есть права
		  if($au->user_rights->CheckAccess('w',1111)||($user_ruk['id']==$result['id'])){
			  if(isset($_POST['is_dir'])&&($_POST['is_dir']==1)){
				  
				  $_res->instance->Edit($id,array('is_dir'=>1, 'user_dir_id'=>$result['id'], 'dir_pdate'=>time()),true,$result);
				  
				  $log->PutEntry($result['id'],'утвердил СЗ ген. директором',NULL,1091, NULL, NULL,$id);	
				  
				   
				  //die();
			  }
		  } 
	  }
	}
	
	
	//утверждение выполнения
	if($field_rights['to_done']){	
	  if($editing_user['is_confirmed_done']==1){
		  //есть права: либо сам утв.+есть права, либо есть искл. права:
		  if(($au->user_rights->CheckAccess('w',1117))){
			  if((!isset($_POST['is_confirmed_done']))/*&&($_res->instance->DocCanUnconfirmPrice($id,$rss32))*/){
				  
				  
				  $_res->instance->Edit($id,array('is_confirmed_done'=>0, 'confirm_done_id'=>$result['id'], 'confirm_done_pdate'=>time()),true,$result);
				  
				  $log->PutEntry($result['id'],'снял утверждение выполнения',NULL,1098, NULL, NULL,$id);	
				  
			  }
		  } 
		  
	  }else{
		  //есть права
		  if($au->user_rights->CheckAccess('w',1091)){
			  if(isset($_POST['is_confirmed_done'])&&($_POST['is_confirmed_done']==1)/*&&($_res->instance->DocCanConfirmPrice($id,$rss32))*/){
				  
				  $_res->instance->Edit($id,array('is_confirmed_done'=>1, 'confirm_done_id'=>$result['id'], 'confirm_done_pdate'=>time()),true,$result);
				  
				  $log->PutEntry($result['id'],'утвердил выполнение',NULL,1091, NULL, NULL,$id);	
				  
				   
				  //die();
			  }
		  } 
	  }
	}
	
	
	
	//ОТЧЕТ согласовано/не согласовано
	if($field_rights['to_ruk_ot']){	
	  
		$_ug=new DocVn_UsersSGroup;
		$_ui=new UserSItem; $uis=$_ui->getitembyid($editing_user['manager_id']);
		$user_ruk=$_ug->GetRuk($uis);
		// var_dump($user_ruk);
		
	   	if($editing_user['is_ruk_ot']==1){
			
			 
		  //есть права: либо сам утв.+есть права, либо есть искл. права:
		  if(($au->user_rights->CheckAccess('w',1110))||($user_ruk['id']==$result['id'])){
			  if((!isset($_POST['is_ruk_ot']))){
				  
				  
				  $_res->instance->Edit($id,array('is_ruk_ot'=>0, 'ruk_ot_id'=>$result['id'], 'ruk_ot_pdate'=>time()),true,$result);
				  
				  $log->PutEntry($result['id'],'снял утверждение отчета рук-лем отдела',NULL,1091, NULL, NULL,$id);	
				  
			  }
		  } 
		  
	  }else{
		  //есть права
		  if($au->user_rights->CheckAccess('w',1109)||($user_ruk['id']==$result['id'])){
			  if(isset($_POST['is_ruk_ot'])&&($_POST['is_ruk_ot']==1)){
				  
				  $_res->instance->Edit($id,array('is_ruk_ot'=>1, 'ruk_ot_id'=>$result['id'], 'ruk_ot_pdate'=>time()),true,$result);
				  
				  $log->PutEntry($result['id'],'утвердил отчет рук-лем отдела',NULL,1091, NULL, NULL,$id);	
				  
				   
				  //die();
			  }
		  } 
	  }
	}
	
	
	//ОТЧЕТ утверждено/не утверждено
	if($field_rights['to_dir_ot']){	
	  
		$_ug=new DocVn_UsersSGroup;
		$_ui=new UserSItem; $uis=$_ui->getitembyid($editing_user['manager_id']);
		$user_ruk=$_ug->GetDir($uis);
		// var_dump($user_ruk);
		
	   	if($editing_user['is_dir_ot']==1){
			
			 
		  //есть права: либо сам утв.+есть права, либо есть искл. права:
		  if(($au->user_rights->CheckAccess('w',1114))||($user_ruk['id']==$result['id'])){
			  if((!isset($_POST['is_dir_ot']))){
				  
				  
				  $_res->instance->Edit($id,array('is_dir_ot'=>0, 'dir_ot_id'=>$result['id'], 'dir_ot_pdate'=>time()),true,$result);
				  
				  $log->PutEntry($result['id'],'снял утверждение отчета ген. директором',NULL,1091, NULL, NULL,$id);	
				  
			  }
		  } 
		  
	  }else{
		  //есть права
		  if($au->user_rights->CheckAccess('w',1113)||($user_ruk['id']==$result['id'])){
			  if(isset($_POST['is_dir_ot'])&&($_POST['is_dir_ot']==1)){
				  
				  $_res->instance->Edit($id,array('is_dir_ot'=>1, 'dir_ot_id'=>$result['id'], 'dir_ot_pdate'=>time()),true,$result);
				  
				  $log->PutEntry($result['id'],'утвердил отчет ген. директором',NULL,1091, NULL, NULL,$id);	
				  
				   
				  //die();
			  }
		  } 
	  }
	}
	
	
	
	
	
	
	
	 
	 
	 
  
			 
	
	$_dsi=new DocStatusItem; 
	//обработка выделенных кнопок
 
	
	//обработка выделенных кнопок
	if(isset($_POST['send_ruk_sz'])){
		
		if($field_rights['send_ruk_sz']){
			
			$setted_status_id=41;
			$_res->instance->Edit($id,array( 'status_id'=>$setted_status_id),true, $result);
			
			$stat=$_dsi->GetItemById($setted_status_id);
			$log->PutEntry($result['id'],'смена статуса вн. документа',NULL,1091,NULL,'установлен статус '.$stat['name'],$id);
			
			//отправить письмо всем согласователям
			//$_sgns=new DocOut_SignGroup;
			//$_sgns->SendMessagesToSigners($id, 1);
			
			//$_msg=new DocIn_Messages;
			//$_msg->SendMessageToView($id);
			
			//сообщение руководителю отдела
			 
					
		}		
	}
	
	
	if(isset($_POST['send_dir_sz'])){
		
		if($field_rights['send_dir_sz']){
			
			$setted_status_id=43;
			$_res->instance->Edit($id,array( 'status_id'=>$setted_status_id),true, $result);
			
			$stat=$_dsi->GetItemById($setted_status_id);
			$log->PutEntry($result['id'],'смена статуса вн. документа',NULL,1091,NULL,'установлен статус '.$stat['name'],$id);
			
			//отправить письмо всем согласователям
			//$_sgns=new DocOut_SignGroup;
			//$_sgns->SendMessagesToSigners($id, 1);
			
			//$_msg=new DocIn_Messages;
			//$_msg->SendMessageToView($id);
			
			//сообщение директору
			 
					
		}		
	}
	
	
	if(isset($_POST['to_rework_sz'])){
		
		if($field_rights['to_rework_sz']){
			
			$setted_status_id=33;
			$_res->instance->Edit($id,array( 'status_id'=>$setted_status_id),true, $result);
			
			$stat=$_dsi->GetItemById($setted_status_id);
			$log->PutEntry($result['id'],'смена статуса вн. документа',NULL,1091,NULL,'установлен статус '.$stat['name'],$id);
			
			
			$comment=SecStr($_POST['status_change_comment']);
			
		 	  
			//внести комментарий			 
			$_tsi=new DocVn_HistoryItem;
			$_tsi->Add(array(
				'sched_id'=>$id,
				'pdate'=>time(),
				'user_id'=>0,
				'txt'=>SecStr('<div>Автоматический комментарий: сотрудник '.$result['name_s'].' отправил документ на доработку, причина: '.$comment.'</div>')
			
			));
			
			//снять утверждения, согласования сз
			$_res->instance->Edit($id,array('is_ruk'=>0, 'is_dir'=>0, '	user_ruk_id'=>0, 'user_dir_id'=>0, 'ruk_pdate'=>time(),'dir_pdate'=>time()));
			 
					
		}		
	}
	
	
	
	
	
	if(isset($_POST['send_ruk_ot'])){
		
		if($field_rights['send_ruk_ot']){
			
			$setted_status_id=48;
			$_res->instance->Edit($id,array( 'status_id'=>$setted_status_id),true, $result);
			
			$stat=$_dsi->GetItemById($setted_status_id);
			$log->PutEntry($result['id'],'смена статуса вн. документа',NULL,1091,NULL,'установлен статус '.$stat['name'],$id);
			
			//отправить письмо всем согласователям
			//$_sgns=new DocOut_SignGroup;
			//$_sgns->SendMessagesToSigners($id, 1);
			
			//$_msg=new DocIn_Messages;
			//$_msg->SendMessageToView($id);
			
			//сообщение руководителю отдела
			 
					
		}		
	}
	
	
	if(isset($_POST['send_dir_ot'])){
		
		if($field_rights['send_dir_ot']){
			
			$setted_status_id=50;
			$_res->instance->Edit($id,array( 'status_id'=>$setted_status_id),true, $result);
			
			$stat=$_dsi->GetItemById($setted_status_id);
			$log->PutEntry($result['id'],'смена статуса вн. документа',NULL,1091,NULL,'установлен статус '.$stat['name'],$id);
			
			//отправить письмо всем согласователям
			//$_sgns=new DocOut_SignGroup;
			//$_sgns->SendMessagesToSigners($id, 1);
			
			//$_msg=new DocIn_Messages;
			//$_msg->SendMessageToView($id);
			
			//сообщение директору
			 
					
		}		
	}
	
	
	if(isset($_POST['to_rework_ot'])){
		
		if($field_rights['to_rework_ot']){
			
			$setted_status_id=2;
			$_res->instance->Edit($id,array('status_id'=>$setted_status_id),true, $result);
			
			$stat=$_dsi->GetItemById($setted_status_id);
			$log->PutEntry($result['id'],'смена статуса вн. документа',NULL,1091,NULL,'установлен статус '.$stat['name'],$id);
			
			
			$comment=SecStr($_POST['status_change_comment']);
			
		 	  
			//внести комментарий			 
			$_tsi=new DocVn_HistoryItem;
			$_tsi->Add(array(
				'sched_id'=>$id,
				'pdate'=>time(),
				'user_id'=>0,
				'txt'=>SecStr('<div>Автоматический комментарий: сотрудник '.$result['name_s'].' отправил документ на доработку, причина: '.$comment.'</div>')
			
			));
			 
			 
			//снять утверждения, согласования сз, утв. вып-ия
			$_res->instance->Edit($id,array('is_ruk_ot'=>0, 'is_dir_ot'=>0, 'ruk_ot_id'=>0, 'dir_ot_id'=>0, 'ruk_ot_pdate'=>time(),'dir_ot_pdate'=>time(), 'is_confirmed_done'=>0, 'confirm_done_id'=>0, 'confirm_done_pdate'=>time()));
					
		}		
	}
	
	 
	
	
	//die();
	
	//перенаправления
	if(isset($_POST['doEdit'])){
		header("Location: doc_inners.php#user_".$id);
		die();
	}elseif(isset($_POST['doEditStay'])
		||isset($_POST['send_ruk_sz'])
		||isset($_POST['to_rework_sz'])
		||isset($_POST['send_dir_sz'])
		||isset($_POST['to_rework_ot'])
		||isset($_POST['send_ruk_ot'])
		||isset($_POST['send_dir_ot'])){

	
	 
		header("Location: ed_doc_vn.php?action=1&id=".$id.'&from_begin='.$from_begin);
		die();	
		
	}else{
		header("Location: doc_inners.php");
		die();
	}
	
	die();
}


 //журнал событий 
if($action==1){
	$log=new ActionLog;
	 
	$log->PutEntry($result['id'],'открыл внутренний документ',NULL,1091, NULL, 'внутренний документ № '.$editing_user['id'],$id);
	
	//пометим вн док как просмотренный
	$_tview=new DocVn_ViewItem;
	 
	$test_view=$_tview->GetItemByFields(array('doc_vn_id'=>$id, 'user_id'=>$result['id']));
	if($test_view===false) $_tview->Add(array('doc_vn_id'=>$id, 'user_id'=>$result['id']));		
	 
				
} 



//работа с хедером
$stop_popup=true;

require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

if($print==0) $smarty->display('top.html');
else $smarty->display('top_print.html');
unset($smarty);

	$_menu_id=82;	
	if($print==0) include('inc/menu.php');
	
	
	//демонстрация  страницы
	$smarty = new SmartyAdm;
	
	$sm1=new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	//создание позиции
	 if($action==0){
		 
		
		$sm1->assign('now_time',  date('d.m.Y H:i:s')); 
		$sm1->assign('now_date',  date('d.m.Y')); 
		
		
		
		//виды
		$_kinds=new DocVn_KindGroup;
		$kinds=$_kinds->GetItemsArr(0);
		$kind_ids=array(0); $kind_vals=array('-выберите-'); $kind_name='';
		foreach($kinds as $k=>$v){
			$kind_ids[]=$v['id']; $kind_vals[]=$v['name'];	if($v['id']==$kind_id) $kind_name=$v['name'];
		}
		$sm1->assign('kind_ids', $kind_ids); $sm1->assign('kind_vals', $kind_vals);
		$sm1->assign('kind_id', $kind_id); 
		$sm1->assign('kind_name', $kind_name); 
		
		
		
		 
		$from_hrs=array();
		$from_hrs[]='';
		for($i=0;$i<=23;$i++) $from_hrs[]=sprintf("%02d",$i);
		$sm1->assign('ptime_beg_h',$from_hrs);
		
				
		$from_ms=array();
		$from_ms[]='';
		for($i=0;$i<=59;$i++) $from_ms[]=sprintf("%02d",$i);
		$sm1->assign('ptime_beg_m',$from_ms);
		   
		//причины работы в вых
		$_kinds=new DocVn_VyhReasonGroup;
		$kinds=$_kinds->GetItemsArr(0);
		$kind_ids=array(0); $kind_vals=array('-выберите-'); $kind_name='';
		foreach($kinds as $k=>$v){
			$kind_ids[]=$v['id']; $kind_vals[]=$v['name'];	if($v['id']==$kind_id) $kind_name=$v['name'];
		}
		$sm1->assign('vyh_reason_ids', $kind_ids); $sm1->assign('vyh_reason_names', $kind_vals);
		$sm1->assign('vyh_reason_id', 0); 
		
		
		//статьи расхода (по умолчанию)
		
		$_exps=new DocVn_ExpensesBlock;
		//валюты в таблице
		$_curr=new PlCurrGroup;
		$kind_ids=array(0); $kind_vals=array('-выберите-'); $kind_name='';
		$currs1= $_curr->GetItemsArr(0);
		foreach($currs1 as $k=>$v){
			$kind_ids[]=$v['id']; $kind_vals[]=$v['signature'];
		}
		$sm1->assign('curr_ids', $kind_ids); $sm1->assign('curr_names', $kind_vals);
		$data=$_exps->ConstructById(0,NULL,$result);
		$sm1->assign('exps', $data);
		//ITOGO
		$itogo=$_exps->CalcItogoArr($data, 0, NULL, $result);
		
		$sm1->assign('itogo',$itogo);
		
		
		//echo $_exps->CalcSut(790, NULL, $result);
		
		
		
		//копируем данные
		/*if(isset($_GET['copyfrom'])){
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
			  
			  $filetext=$ffg->ShowFiles('doc_in/uploaded_files_list.html',  $decorator,$from,$to_page,'ed_doc_vn.php', 'doc_in_file.html', 'swfupl-js/doc_in_files.php',  
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
			 
		}*/
		
		
		//получим список тех, кто может снять утверждение заполнения
		$_usg1=new UsersSGroup;
		$usg1=$_usg1->GetUsersByRightArr('w', 1098);
		$sm1->assign('can_unconfirm_users',$usg1);
		$sm1->assign('can_unconfirm',$au->user_rights->CheckAccess('w',1098));
		
		
		 
		$sm1->assign('session_id', session_id());
		
		$sm1->assign('can_add_supplier', $au->user_rights->CheckAccess('w',87));
		
	 
		
		$sm1->assign('can_confirm', $au->user_rights->CheckAccess('w',1090));
		
		$sm1->assign('can_expand_sut', $au->user_rights->CheckAccess('w',1115));
		
		
		
		
		$user_form=$sm1->fetch('doc_vn/create_doc_vn.html');
		 
		 
	
	
	 }elseif($action==1){
		//редактирование позиции
		
		if($print==0) $print_add='';
		else $print_add='_print';
		
		
		$_res=new  DocVn_Resolver($editing_user['kind_id']);
		
		
		//построим доступы
		$_roles=$_res->rules_instance;
		$field_rights=$_roles->GetFields($editing_user, $result['id']);
		$sm1->assign('field_rights', $field_rights);
		
		 
		
		$sm1->assign('manager_id', $editing_user['manager_id']);
		$_uis=new UserSItem; $uis=$_uis->getitembyid($editing_user['manager_id']);
		$editing_user['manager_string']= $uis['name_s'];
		
		 
		$_kind=new DocVn_KindItem; $kind=$_kind->getitembyid($editing_user['kind_id']);
		$editing_user['kind_name']=$kind['name'];
		
		$_leads=new DocVn_LeadGroup;
		$leads=$_leads->GetItemsByIdArr($editing_user['id']);
		
		$lead=$leads[0];
		$editing_user['lead_id']=$lead['lead_id'];
		$editing_user['lead_string']=$lead['code'];
		
	 
		$editing_user['pdate']=date('d.m.Y', $editing_user['pdate']);
		
		 
		
		//кто отправил
		$_user=new UserSItem;
		$send_who=$_user->GetItemById($editing_user['send_user_id']);
		$sm1->assign('send_who', $send_who['name_s']);
		
		
		
		//связанная командировка
		$_sched=new Sched_MissionItem;
		$si=$_sched->getitembyid($editing_user['sched_id']);
		
		//метод должен также возвращать, СКОЛЬКО вых/праздничных дней выпадает
		$_hd=new HolyDates;
		$pdate1=datefromdmy(datefromYmd($si['pdate_beg']));	 $pdate2=datefromdmy(datefromYmd($si['pdate_end']));	
		$hd_count=0;
		for($pdate=$pdate1; $pdate<=$pdate2; $pdate=$pdate+24*60*60){
			if($_hd->IsHolyday($pdate)) $hd_count++;
		}
		$editing_user['hd_count']=$hd_count;
		
		$_opt=new DocVn_VyhDateGroup;
		$sm1->assign('vyh_otp_dates', $_opt->GetItemsArrById($editing_user['id']));
		
		 
		
		//города
		$_csg=new Sched_CityGroup;
		$csg=$_csg->GetItemsByIdArr($si['id']);
		//	
	 	$sm1->assign('cities', $csg);
		
		//контрагенты
		$_suppliers=new Sched_SupplierGroup;
			$sup=$_suppliers->GetItemsByIdArr($si['id']);
	 
		$sm1->assign('suppliers', $sup);
		
		 
		$from_hrs=array();
		$from_hrs[]='';
		for($i=0;$i<=23;$i++) $from_hrs[]=sprintf("%02d",$i);
		$sm1->assign('ptime_beg_h',$from_hrs);
		
				
		$from_ms=array();
		$from_ms[]='';
		for($i=0;$i<=59;$i++) $from_ms[]=sprintf("%02d",$i);
		$sm1->assign('ptime_beg_m',$from_ms);
		   
		//причины работы в вых
		$_kinds=new DocVn_VyhReasonGroup;
		$kinds=$_kinds->GetItemsArr(0);
		$kind_ids=array(0); $kind_vals=array('-выберите-'); $kind_name='';
		foreach($kinds as $k=>$v){
			$kind_ids[]=$v['id']; $kind_vals[]=$v['name'];	if($v['id']==$kind_id) $kind_name=$v['name'];
		}
		$sm1->assign('vyh_reason_ids', $kind_ids); $sm1->assign('vyh_reason_names', $kind_vals);
		$sm1->assign('vyh_reason_id', 0); 
		
		
		//статьи расхода 
		
		$_exps=new DocVn_ExpensesBlock;
		//валюты в таблице
		$_curr=new PlCurrGroup;
		$kind_ids=array(0); $kind_vals=array('-выберите-'); $kind_name='';
		$currs1= $_curr->GetItemsArr(0);
		foreach($currs1 as $k=>$v){
			$kind_ids[]=$v['id']; $kind_vals[]=$v['signature'];
		}
		$sm1->assign('curr_ids', $kind_ids); $sm1->assign('curr_names', $kind_vals);
		$data=$_exps->ConstructById($id,NULL,$result);
		$sm1->assign('exps', $data);
		//ITOGO
		$itogo=$_exps->CalcItogoArr($data, 0, NULL, $result);
		
		$sm1->assign('itogo',$itogo);
		
		
		
		
		$editing_user['pdate_beg']=datefromYmd($si['pdate_beg']);
		$editing_user['pdate_end']=datefromYmd($si['pdate_end']);
		
		$editing_user['ptime_beg_hr']=substr($si['ptime_beg'],  0,2 );
		$editing_user['ptime_beg_mr']=substr($si['ptime_beg'],  3,2 );
		
		$editing_user['ptime_end_hr']=substr($si['ptime_end'],  0,2 );
		$editing_user['ptime_end_mr']=substr($si['ptime_end'],  3,2 ); 	 	
		$editing_user['sched_str']=$si['code']; 
	
		
		
		//связанные заявления вида 3 - на отпуск за работу в выходной день в связи с ком-кой
		$_tg=new PetitionAllGroup;
		$_tg->SetAuthResult($result);
		$decorator1=new DBDecorator;
		$decorator1->AddEntry(new SqlEntry('t.kind_id',3, SqlEntry::E));
		$decorator1->AddEntry(new SqlEntry('t.vyh_reason_id',2, SqlEntry::E));
		$decorator1->AddEntry(new SqlEntry('t.sched_id',$editing_user['sched_id'], SqlEntry::E));
		$decorator1->AddEntry(new SqlOrdEntry('t.code',SqlOrdEntry::DESC));
		$petitions=$_tg->ShowPos($result['id'], //0
		 "petition/petitions.html", //1
		 $decorator1, //2
		 false, //3
		 $au->user_rights->CheckAccess('w',724), //4
		 $alls, //5
		 $from,  //6
		 $to_page, //7
		 $result, //8
		 $au->user_rights->CheckAccess('w',827), //9
		 $au->user_rights->CheckAccess('w',725), //10
		 $au->user_rights->CheckAccess('w',826), //11
		 true,
		 $au->user_rights->CheckAccess('w',829), //13
		 $au->user_rights->CheckAccess('w',830),  //14
		 $au->user_rights->CheckAccess('w',831),  //15
		 $au->user_rights->CheckAccess('w',832),  //16
		 
		 $au->user_rights->CheckAccess('w',828), //17
		 $au->user_rights->CheckAccess('w',1135), //18
		 $au->user_rights->CheckAccess('w',1136)  //19
		 );
		 
		 $sm1->assign('petitions', $alls);
		
		//возможность РЕДАКТИРОВАНИЯ - 
			//пол-ль - создал
	//или пол-ль - в списке видящих
	 	
		//$can_modify=true;
		
		$can_modify=$field_rights['common'];   //in_array($editing_user['status_id'],$_editable_status_id);
		
		 
		
		 
		 

		$can_modify_ribbon=!in_array($editing_user['status_id'],array(3));
	 
	 	//лента документа
		$_hg=new DocVn_HistoryGroup;
		$history= $_hg->ShowHistory(
			$editing_user['id'],
			 'doc_vn/lenta'.$print_add.'.html', 
			 new DBDecorator(), 
			 $can_modify_ribbon,
			 true,
			 false,
			 $result,
			 $au->user_rights->CheckAccess('w',1093),
			 $au->user_rights->CheckAccess('w',1094),$history_data,true,true
			 );
		$sm1->assign('lenta',$history);
		$sm1->assign('lenta_len',count($history_data));
		 
		 
		
		
		//блок аннулирования
		
		$editing_user['can_annul']=$_res->instance->DocCanAnnul($editing_user['id'],$reason,$editing_user)&&$au->user_rights->CheckAccess('w',1096);
		if(!$au->user_rights->CheckAccess('w',1096)) $reason='недостаточно прав для данной операции';
		$editing_user['can_annul_reason']=$reason;
		
		 
		
		$editing_user['can_restore']=$_res->instance->DocCanRestore($editing_user['id'],$reason,$editing_user)&&$au->user_rights->CheckAccess('w',1097);
			if(!$au->user_rights->CheckAccess('w',1097)) $reason='недостаточно прав для данной операции';
		
		
		
		
			
		 
		
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
			  
			   $can_confirm_price=$au->user_rights->CheckAccess('w',1098)&&$field_rights['to_confirm'];
			  
		  }else{
			  //95
			  $can_confirm_price=$au->user_rights->CheckAccess('w',1091)&&$field_rights['to_confirm'] ;
		  }
		}
		$sm1->assign('can_confirm',$can_confirm_price);
		
		
		//согласование служебки
		//рук отдела
		if(($editing_user['is_ruk']==1)&&($editing_user['user_ruk_id']!=0)){
			$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['user_ruk_id']);
			$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.date("d.m.Y H:i:s",$editing_user['ruk_pdate']);
			
			$sm1->assign('is_ruk_confirmer',$confirmer);
		}
	    $can_confirm_price=false;
		//найти руководителя отдела
		
		$_ug=new DocVn_UsersSGroup;
		$user_ruk=$_ug->GetRuk($uis);
		// var_dump($user_ruk);
		
	   	if($editing_user['is_ruk']==1){
			
			 $can_confirm_price=($au->user_rights->CheckAccess('w',1100)||($user_ruk['id']==$result['id']))&&$field_rights['to_ruk_sz'];
			
		}else{
			//95
			$can_confirm_price=($au->user_rights->CheckAccess('w',1099)||($user_ruk['id']==$result['id']))&&$field_rights['to_ruk_sz'] ;
		}
		
		$sm1->assign('can_ruk_sz',$can_confirm_price);
		
		
		
		//директор
		if(($editing_user['is_dir']==1)&&($editing_user['user_dir_id']!=0)){
			$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['user_dir_id']);
			$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.date("d.m.Y H:i:s",$editing_user['dir_pdate']);
			
			$sm1->assign('is_dir_confirmer',$confirmer);
		}
	    $can_confirm_price=false;
		//найти директора
		
		$_ug=new DocVn_UsersSGroup;
		$user_ruk=$_ug->GetDir($uis);
		// var_dump($user_ruk);
		
	   	if($editing_user['is_dir']==1){
			
			 $can_confirm_price=($au->user_rights->CheckAccess('w',1112)||($user_ruk['id']==$result['id']))&&$field_rights['to_dir_sz'];
			
		}else{
			//95
			$can_confirm_price=($au->user_rights->CheckAccess('w',1111)||($user_ruk['id']==$result['id']))&&$field_rights['to_dir_sz'] ;
		}
		
		$sm1->assign('can_dir_sz',$can_confirm_price);
		
		
		
		
		//блок утверждения выполнения
		if(($editing_user['is_confirmed_done']==1)&&($editing_user['confirm_done_id']!=0)){
			$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['confirm_done_id']);
			$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.date("d.m.Y H:i:s",$editing_user['confirm_done_pdate']);
			
			 
			$sm1->assign('is_confirmed_done_confirmer',$confirmer);
		}
		
		$can_confirm_price=false;
		if($editing_user['is_confirmed']==1){
			
			  
		  
		  if($editing_user['is_confirmed_done']==1){
			  
			   $can_confirm_price=$au->user_rights->CheckAccess('w',1117)&&$field_rights['to_done'];
			  
		  }else{
			  //95
			  $can_confirm_price=$au->user_rights->CheckAccess('w',1091)&&$field_rights['to_done'] ;
		  }
		}
		$sm1->assign('can_confirm_done',$can_confirm_price);
		
		
		//согласование отчета
		//рук отдела
		if(($editing_user['is_ruk_ot']==1)&&($editing_user['ruk_ot_id']!=0)){
			$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['ruk_ot_id']);
			$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.date("d.m.Y H:i:s",$editing_user['ruk_ot_pdate']);
			
			$sm1->assign('is_ruk_ot_confirmer',$confirmer);
		}
	    $can_confirm_price=false;
		//найти руководителя отдела
		
		$_ug=new DocVn_UsersSGroup;
		$user_ruk=$_ug->GetRuk($uis);
		// var_dump($user_ruk);
		
	   	if($editing_user['is_ruk_ot']==1){
			
			 $can_confirm_price=($au->user_rights->CheckAccess('w',1110)||($user_ruk['id']==$result['id']))&&$field_rights['to_ruk_ot'];
			
		}else{
			//95
			$can_confirm_price=($au->user_rights->CheckAccess('w',1109)||($user_ruk['id']==$result['id']))&&$field_rights['to_ruk_ot'] ;
		}
		
		$sm1->assign('can_ruk_ot',$can_confirm_price);
		
		
		
		//директор - отчет
		if(($editing_user['is_dir_ot']==1)&&($editing_user['dir_ot_id']!=0)){
			$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['dir_ot_id']);
			$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.date("d.m.Y H:i:s",$editing_user['dir_ot_pdate']);
			
			$sm1->assign('is_dir_ot_confirmer',$confirmer);
		}
	    $can_confirm_price=false;
		//найти директора
		
		$_ug=new DocVn_UsersSGroup;
		$user_ruk=$_ug->GetDir($uis);
		// var_dump($user_ruk);
		
	   	if($editing_user['is_dir_ot']==1){
			
			 $can_confirm_price=($au->user_rights->CheckAccess('w',1114)||($user_ruk['id']==$result['id']))&&$field_rights['to_dir_ot'];
			
		}else{
			//95
			$can_confirm_price=($au->user_rights->CheckAccess('w',1113)||($user_ruk['id']==$result['id']))&&$field_rights['to_dir_ot'] ;
		}
		
		$sm1->assign('can_dir_ot',$can_confirm_price);
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		 
		 
		 
		
		//получим список тех, кто может снять утверждение заполнения
		$_usg1=new UsersSGroup;
		$usg1=$_usg1->GetUsersByRightArr('w', 1098);
		$sm1->assign('can_unconfirm_users',$usg1);
		$sm1->assign('can_unconfirm',$au->user_rights->CheckAccess('w',1098));
		
		
		 
		$sm1->assign('session_id', session_id());
		
		$sm1->assign('can_add_supplier', $au->user_rights->CheckAccess('w',87));
		
	
		$sm1->assign('can_expand_sut', $au->user_rights->CheckAccess('w',1115));
		
		
		
		
		
		
		
		
		$sm1->assign('can_modify', $can_modify);
		$sm1->assign('can_modify_plan', $field_rights['plan']);
		$sm1->assign('can_modify_fact', $field_rights['fact']);
	 
		
		$sm1->assign('can_create', $au->user_rights->CheckAccess('w',1090));  
		
		
		
	 
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
			  
			  $ffg=new DocVnFileGroup(1,  $id,  new FileDocFolderItem(1,  $id, new DocVnFileItem(1)));;
			  
			  $filetext=$ffg->ShowFiles('doc_file/incard_list.html',  $decorator,$from,$to_page,'ed_doc_vn.php', 'doc_vn_file.html', 'swfupl-js/doc_vn_files.php',  
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
		
		if(isset($_GET['force_print'])) $sm1->assign('force_print',1);
		
		
		switch($editing_user['kind_id']){
			case 1:	
				$user_form=$sm1->fetch('doc_vn/edit_kind_1'.$print_add.'.html');
			break;
			
			case 2:	
				$user_form=$sm1->fetch('doc_vn/edit_kind_2'.$print_add.'.html');
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
		
			$decorator->AddEntry(new SqlEntry('object_id',NULL, SqlEntry::IN_VALUES, NULL, array(1063,
1090,
1091,
1092,
1093,
1094,
1095,
1096,
1097,
1098,
1099,
1100,
1109,
1110,
1111,
1112,
1113,
1114,
1117,
1115
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
			 
			$llg=$log->ShowLog('syslog/log_doc.html',$decorator,$from,$to_page,'ed_doc_vn.php',true,true,true);
			
			$sm->assign('syslog',$llg);		
				
		} 
		
		
	}
	
	
	
	
	
	$sm->assign('print_pdate', date("d.m.Y H:i:s"));
	//$username=$result['login'];
	$username=stripslashes($result['name_s']); //.' '.$username;	
	$sm->assign('print_username',$username);
	
	$sm->assign('users',$user_form);
	$content=$sm->fetch('doc_vn/ed_doc_vn_page'.$print_add.'.html');
	
	
	
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