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
 
require_once('classes/filecontents.php');
 

require_once('classes/user_s_item.php');

 

 
require_once('classes/orgitem.php');
require_once('classes/opfitem.php');

 

require_once('classes/suppliercontactitem.php');
require_once('classes/supcontract_group.php');

require_once('classes/tender.class.php');

require_once('classes/sched.class.php');



require_once('classes/tender_fileitem.php');
require_once('classes/tender_filegroup.php');
 

require_once('classes/supplier_country_group.php');
require_once('classes/supplier_city_item.php');
 
require_once('classes/tender_history_group.php');
require_once('classes/docstatusitem.php');

require_once('classes/tender_history_item.php');
require_once('classes/tender_history_group.php');

require_once('classes/tender_history_fileitem.php');

require_once('classes/tender_view_item.php');

require_once('classes/supplier_responsible_user_item.php');

require_once('classes/lead_history_item.php');


$_pch=new PeriodChecker;
$pch_date=$_pch->GetDate();



$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Тендер');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}


$_dem=new Tender_AbstractItem;

$_plan1=new Sched_Group;
$available_users=$_plan1->GetAvailableUserIds($result['id']);

$_plan=new Tender_Group;


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
	$object_id[]=930;
	break;
	case 1:
	$object_id[]=931;
	break;
	case 2:
	$object_id[]=931;
	break;
	default:
	$object_id[]=931;
	break;
}

$_editable_status_id=array();
$_editable_status_id[]=1;
 
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
	 
	 
	
}elseif(($action==1)||($action==2)||($action==10)||($action==3)||($action==4)){

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
	
	
	$_tg=new Tender_Group;
	
	if(!$au->user_rights->CheckAccess('w',934)){	
		$available_tenders=$_tg->GetAvailableTenderIds($result['id']);
		$is_shown=in_array($id, $available_tenders);
	
		if(!$is_shown){
			header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			include('404.php');
			die();
		}
	}
	
 
}

 
 


//обработка данных

if(($action==0)&&(isset($_POST['doNew'])||isset($_POST['doNewEdit']))){
	 
	
	
	
	
	$params=array();
	//обычная загрузка прочих параметров
	$params['created_id']=$result['id'];
	$params['pdate']=time();
	
	$params['manager_id']=abs((int)$_POST['manager_id']);
	
	$params['kind_id']=abs((int)$_POST['kind_id']);
	
	$params['eq_type_id']=abs((int)$_POST['eq_type_id']);
	
	$params['fz_id']=abs((int)$_POST['fz_id']);
	 
	  
	$params['ptime_finish']= SecStr($_POST['ptime_finish_h']).':'.SecStr($_POST['ptime_finish_m']).':00';
	
	$params['ptime_claiming']= SecStr($_POST['ptime_claiming_h']).':'.SecStr($_POST['ptime_claiming_m']).':00';
	
	
	 
	$params['topic']= SecStr($_POST['topic']);
	$params['link']= SecStr($_POST['link']);
	
	
	$params['pdate_placing']= date('Y-m-d', DateFromdmY($_POST['pdate_placing']));
	$params['pdate_claiming']= date('Y-m-d', DateFromdmY($_POST['pdate_claiming']));
	$params['pdate_finish']= date('Y-m-d', DateFromdmY($_POST['pdate_finish']));
	
	
	$params['max_price']=abs((float)eregi_replace("[ ]+","", str_replace(',','.',$_POST['max_price'])));
		 $params['currency_id']=abs((int)$_POST['currency_id']);
		  
 
	
	$params['status_id']=18;
			
	 
	$_res=new Tender_Resolver();
		
		
	$code=	$_res->instance->Add($params);
	 
	//$code=1;
	//запись в журнале
	if($code>0){
		$log->PutEntry($result['id'],'создал тендер',NULL,930,NULL,NULL,$code);	
		
		foreach($params as $k=>$v){
			
		  
				
				$log->PutEntry($result['id'],'создал тендер',NULL,930, NULL, 'в поле '.$k.' установлено значение '.$v,$code);		
			 
		}
		
	}
	
	  
	
	
	
	
	  	 
	 
	 
	 
	 //в отдельный блок - утверждение заполнения задачи, чтобы сработал его перехват и отправились сообщения
	//if(isset($params['manager_id'])&&($params['manager_id']>0)){ 
		$new_params=array();
		$new_params['is_confirmed']=1;
		$new_params['user_confirm_id']=$result['id'];
		$new_params['confirm_pdate']=time();
		 
		
		$_res->instance->Edit($code, $new_params, true, $result);
		$log->PutEntry($result['id'],'автоматическое утверждение при создании тендера',NULL,930, NULL, '',$code);		 
	//}
	// die();
	 
	 
	//приложим файлы!
	//upload_file_6A83_tmp" value="_ZpaGsu91PI.jpg" 
	$fmi=new TenderFileItem;
	foreach($_POST as $k=>$v){
	  if(eregi("^upload_file_",$k)){
		    $filename=eregi_replace("^upload_file_", '', eregi_replace("_tmp$",".tmp",$k));
		  $fmi->Add(array('bill_id'=>$code, 'filename'=>SecStr(basename($filename)), 'orig_name'=>SecStr($v), 'user_id'=>$result['id'], 'pdate'=>time()));
		  
		   $log->PutEntry($result['id'], 'прикрепил файл к тендеру', NULL, 930, NULL,'Служебное имя файла: '.SecStr(basename($filename)).' Имя файла: '.SecStr($v),$code);
		   
		   
	  }
	}
	 
	
	
	
	 //контрагенты
		$_supplier=new SupplierItem;
		$_sg=new Tender_SupplierGroup;
		$_opf=new OpfItem;
		
		
		$positions=array();
		
		foreach($_POST as $k=>$v){
		  if(eregi("^supplier_id_([0-9]+)",$k)){
			  
			  $hash=abs((int)eregi_replace("^supplier_id_","",$k));
			  
			  $supplier_id=$hash; //abs((int)$_POST['new_share_user_id_'.$hash]);
			  //$right_id=abs((int)$_POST['new_share_right_id_'.$hash]);
			  
			  
			  
			  //найдем контакты
			  $contacts=array();
			  //supplier_contact_id_%{$suppliers[supsec].id}%_%{$contact.id}%
			  
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
				  $log->PutEntry($result['id'],'добавил контрагента в тендер',NULL,930,NULL,$description,$code);	
			  }elseif($v['action']==1){
				  $log->PutEntry($result['id'],'редактировал контрагента в тендере',NULL,930,NULL,$description,$code);
			  }elseif($v['action']==2){
				  $log->PutEntry($result['id'],'удалил контрагента из тендера',NULL,930,NULL,$description,$code);
			  }
			  
		  }
	
	//отправим сообщение о создании тендера	
	$_res->instance->NewTenderMessage($code);	
	
	/*
	echo '<pre>';
	print_r($_POST);
	print_r($params);
	echo '</pre>';
	die();
	*/
	//перенаправления
	if(isset($_POST['doNew'])){
		 header("Location: tenders.php#user_".$code);
		die();
	}elseif(isset($_POST['doNewEdit'])){
		  
		header("Location: ed_tender.php?action=1&id=".$code.'&from_begin='.$from_begin);
		die();	
		
	}else{
		header("Location: tenders.php");
		die();
	}
	 
	
	die();
	


}elseif(($action==1)&&(
	isset($_POST['doEdit'])||isset($_POST['doEditStay'])||
	
	isset($_POST['to_work'])||
	isset($_POST['to_refuse'])||
	isset($_POST['to_cancel'])||
	isset($_POST['to_set_konk'])||
	isset($_POST['to_go_konk'])||
	isset($_POST['to_no_go_konk'])||
	isset($_POST['to_claim_konk'])||
	isset($_POST['to_win'])||
	isset($_POST['to_fail'])||
	isset($_POST['to_rework'])||
	isset($_POST['to_restore_work'])
	
	
	)){
	 
	
	//редактирование возможно, если позволяет статус
	$_res=new Tender_Resolver();
		
	
	
	
	//поля формируем в зависимости от их активности в текущем статусе
	$_roles=new Tender_FieldRules($result); //var_dump($_roles->GetTable());
	$field_rights0=$_roles->GetFields($editing_user, $result['id']);	
	$field_rights1=$_roles->GetFields($editing_user, $result['id'], $_POST['current_status_id']);
	$field_rights=array(); 
	foreach($field_rights0 as $k=>$v) $field_rights[$k]=$v&&$field_rights1[$k];
	
	
		
	
	$params=array();

	
	//поля формируем в зависимости от их активности в текущем статусе
	$condition =in_array($_POST['current_status_id'],$_editable_status_id)&&in_array($editing_user['status_id'],$_editable_status_id);
	
	if($condition){
	
	
		if($au->user_rights->CheckAccess('w',1148)) $params['manager_id']=abs((int)$_POST['manager_id']);
	
		$params['kind_id']=abs((int)$_POST['kind_id']);
		
		$params['eq_type_id']=abs((int)$_POST['eq_type_id']);
		
		
		$params['fz_id']=abs((int)$_POST['fz_id']);
	
		
		
		 $params['max_price']=abs((float)eregi_replace("[ ]+","", str_replace(',','.',$_POST['max_price'])));
		 $params['currency_id']=abs((int)$_POST['currency_id']);
		  
	$params['ptime_finish']= SecStr($_POST['ptime_finish_h']).':'.SecStr($_POST['ptime_finish_m']).':00';
	
	$params['ptime_claiming']= SecStr($_POST['ptime_claiming_h']).':'.SecStr($_POST['ptime_claiming_m']).':00';  
		
		
		
		 
		$params['topic']= SecStr($_POST['topic']);
		$params['link']= SecStr($_POST['link']);
		
		
		$params['pdate_placing']= date('Y-m-d', DateFromdmY($_POST['pdate_placing']));
		$params['pdate_claiming']= date('Y-m-d', DateFromdmY($_POST['pdate_claiming']));
		$params['pdate_finish']= date('Y-m-d', DateFromdmY($_POST['pdate_finish']));
		
	}
	else{
		//кроме основного условия, еще может сработать дополнительное:	
		/*if(($editing_user['is_confirmed_done']==0)&&($editing_user['manager_id']==0)){
			if($au->user_rights->CheckAccess('w',979)) $params['manager_id']=abs((int)$_POST['manager_id']);
		}*/
		
		//спецправа на смену менеджера
		//$sm1->assign('can_change_manager', $au->user_rights->CheckAccess('w',979)); 
		//if(($editing_user['is_confirmed']==1)&&($editing_user['is_confirmed_done']==0)&&in_array($editing_user['status_id'], array(33,2,28))&&($au->user_rights->CheckAccess('w',979)||($editing_user['manager_id']==$result['id']))){
			
		//возможность менять отв. сотр: права 1148, 979 или рук-ль ОП
		$dec=new DBDecorator();
		//.dep.name
		$dec ->AddEntry(new SqlEntry('pos.name','руководитель отдела', SqlEntry::LIKE));
		$dec ->AddEntry(new SqlEntry('dep.name','отдел продаж', SqlEntry::LIKE));
		$dec ->AddEntry(new SqlEntry('u.is_active',1, SqlEntry::E));
		$ug=new UsersSGroup;
		$users=$ug->GetItemsByDecArr($dec);
		$user_ids=array(); foreach($users as $k=>$v) $user_ids[]=$v['id'];
		
		$can_select_manager=true;
		
		if($editing_user['is_confirmed']==0) $can_select_manager=$can_select_manager&&$au->user_rights->CheckAccess('w',1148);
		else $can_select_manager=$can_select_manager&&$au->user_rights->CheckAccess('w',979);
		
		if(in_array($result['id'], $user_ids)) $can_select_manager=$can_select_manager||true;	
		
		if($can_select_manager){	
			$params['manager_id']=abs((int)$_POST['manager_id']);
			
			if(($_POST['manager_delete_previous']==1)&&($params['manager_id']!=0)&&($params['manager_id']!=$editing_user['manager_id'])){
				//удалить из карты контрагента предыдущего куратора, внести запись в журнал об этом
				
				
				$_suresp=new SupplierResponsibleUserItem;
				$_tsup=new Tender_SupplierItem;
				$tsupplier=$_tsup->GetItemByFields(array('sched_id'=>$id));
				if($tsupplier!==false){
			
					$tsupplier_id=$tsupplier['supplier_id'];
					
					$test_resp=$_suresp->GetItemByFields(array('supplier_id'=>$tsupplier_id, 'user_id'=>$editing_user['manager_id']));
					
					$_tui=new UserSItem;
					$tui=$_tui->GetItemById($editing_user['manager_id']);
					
					if($test_resp!==false){
						$description=SecStr('контрагент '.$tsupplier['code'].' '.$tsupplier['full_name'].' '.$tui['name_s'].' '.$tui['login'].', тендер '.$editing_user['code']);
				   
				  
						$_suresp->Del($test_resp['id']);
						$log->PutEntry($result['id'],'удалил ответственного сотрудника из карты контрагента при смене куратора тендера',NULL,910,NULL,$description,$tsupplier_id);	
						
						$log->PutEntry($result['id'],'удалил ответственного сотрудника из карты контрагента при смене куратора тендера',NULL,931,NULL,$description,$id);	
					}
				}
			}
		}
		
		
		//условие на допустимость смены срока подачи заявок
		/*
		статусы НЕ:
		Заявлен, Выигран, Проигран, Отменен, аннулирован
		*/
		if($au->user_rights->CheckAccess('w',963)&&($editing_user['is_confirmed']==1)&&!in_array($editing_user['status_id'], array(29,30,31,32,3))){
			$params['pdate_claiming']= date('Y-m-d', DateFromdmY($_POST['pdate_claiming']));
			$params['ptime_claiming']= SecStr($_POST['ptime_claiming_h']).':'.SecStr($_POST['ptime_claiming_m']).':00'; 
			
			if($editing_user['status_id']==36){
				//проверить даты
				//контрольная дата - сегодня + 48 часов
				//если выставленная дата больше ее, то сменим статус на В работе и снимем 2 утверждения
				$controld=DateFromdmY(date('d.m.Y'))+48*60*60;
				$newd=DateFromdmY($_POST['pdate_claiming']);
				if($newd>$controld){
					$_res->instance->Edit($id, array('is_fulfiled'=>0, 'is_confirmed_done'=>0, 'confirm_done_pdate'=>time(),'fulfiled_pdate'=>time()),true,$result);
				
					$_res->instance->Edit($id, array('status_id'=>28),true,$result);
					
					$comment=SecStr('при смене срока подачи заявок с '.datefromYmd($editing_user['pdate_claiming']).' на '.($_POST['pdate_claiming']).' тендер автоматически переведен в статус В работе.');
					
					$log->PutEntry($result['id'],'редактировал тендер',NULL,931, NULL, $comment,$id);
					
					//создадим запись в ленту
			 
					$_len=new Tender_HistoryItem;
					$len_params=array();
					$len_params['sched_id']=$id;
					$len_params['txt']=('<div>Автоматический комментарий: '.$comment.'</div>');
					$len_params['user_id']=0;
					$len_params['pdate']=time();
					
					 $_len->Add($len_params);
			 
						
				}
				
			}
		}
	}
//	
	
	$_res->instance->Edit($id, $params,true,$result);
	
		
	//$_dem->Edit($id, $params);
	//die();
	//запись в журнале
	//записи в лог. сравнить старые и новые записи
	foreach($params as $k=>$v){
		
		if(addslashes($editing_user[$k])!=$v){
			$log->PutEntry($result['id'],'редактировал тендер',NULL,931, NULL, 'в поле '.$k.' установлено значение '.$v,$id);
			
					
		}	
		
	}
	
 	if($condition){
	
	//контрагенты
	 
		$_supplier=new SupplierItem;
		$_sg=new Tender_SupplierGroup;
		$_opf=new OpfItem;
		
		
		$positions=array();
		
		foreach($_POST as $k=>$v){
		  if(eregi("^supplier_id_([0-9]+)",$k)){
			  
			  $hash=abs((int)eregi_replace("^supplier_id_","",$k));
			  
			  $supplier_id=$hash; //abs((int)$_POST['new_share_user_id_'.$hash]);
			  //$right_id=abs((int)$_POST['new_share_right_id_'.$hash]);
			  
			  
			  
			  //найдем контакты
			  $contacts=array();
			  //supplier_contact_id_%{$suppliers[supsec].id}%_%{$contact.id}%
			  
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
				  $log->PutEntry($result['id'],'добавил контрагента к тендеру',NULL,931,NULL,$description,$id);	
			  }elseif($v['action']==1){
				  $log->PutEntry($result['id'],'редактировал контрагента в тендере',NULL,931,NULL,$description,$id);
			  }elseif($v['action']==2){
				  $log->PutEntry($result['id'],'удалил контрагента из тендера',NULL,931,NULL,$description,$id);
			  }
			  
		  }
	  
	}
	
	
	 
	 
	
	
	$_dsi=new DocStatusItem; 
	//обработка выделенных кнопок
	if(isset($_POST['to_work'])){
		
		if($field_rights['to_work']){
			
			$setted_status_id=28;
			$_res->instance->Edit($id,array( 'status_id'=>$setted_status_id),true, $result);
			
					  
			//$log->PutEntry($result['id'],'отправил задачу на доработку',NULL,905, NULL, NULL,$id);
			
			$stat=$_dsi->GetItemById($setted_status_id);
			$log->PutEntry($result['id'],'смена статуса тендера',NULL,931,NULL,'установлен статус '.$stat['name'],$id);
			
			 
					
		}		
	}
	
	if(isset($_POST['to_refuse'])){
		
		if($field_rights['to_refuse']&&$au->user_rights->CheckAccess('w',973)){
			$setted_status_id=37;
			$our_data=array( 'status_id'=>$setted_status_id);
			//занести ПРИЧИНЫ ОТКАЗА
 
			$our_data['fail_reason_id']=abs((int)$_POST['status_change_comment_id']);
			$our_data['fail_reason']=SecStr($_POST['status_change_comment']);
			
			$_fi=new Tender_FailItem;
			$fi=$_fi->GetItemById($our_data['fail_reason_id']);
			
				
			
			$_res->instance->Edit($id,$our_data , true, $result);
			
					  
			 
			$stat=$_dsi->GetItemById($setted_status_id);
			
			$comment= ('Установлен статус '.$stat['name'].', причина: '.$fi['name'].' '.$our_data['fail_reason']);
			$log->PutEntry($result['id'],'смена статуса тендера',NULL,931,NULL,$comment,$id);
			
			
			//создадим запись в ленту
			 
				$_len=new Tender_HistoryItem;
				$len_params=array();
				$len_params['sched_id']=$id;
				$len_params['txt']=SecStr('<div>'.$comment.'</div>');
				$len_params['user_id']=$result['id'];
				$len_params['pdate']=time();
				
				 $_len->Add($len_params);
			 
			//те же комменты - в дочерние лиды!
				$_lic=new Lead_HistoryItem;
				$sql='select p.id, p.code from lead as p where p.tender_id="'.$id.'" and p.status_id<>3';
				$leads=array();
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				for($i=0; $i<$rc; $i++) {
					$leads[]=mysqli_fetch_array($rs);	
				}
				
				foreach($leads as $k=>$v){
					$len_params['sched_id']=$v['id'];
					$len_params['user_id']=0;
					$len_params['txt']=SecStr('<div>Автоматический комментарий: лид автоматически переведен в статус Подтвердите отказ при переходе в статус Подтвердите отказ связанного тендера '.$editing_user['code'].': причина: '.$fi['name'].' '.$our_data['fail_reason'].'</div>');
					$_lic->Add($len_params);	
				}
				 
					
		}		
	}
	
	
	if(isset($_POST['to_cancel'])){
		
		if($field_rights['to_cancel']&&$au->user_rights->CheckAccess('w',974)){
			$setted_status_id=32;
			$our_data=array( 'status_id'=>$setted_status_id);
			 
				
			
			$_res->instance->Edit($id,$our_data , true, $result);
			
					  
			 
			$stat=$_dsi->GetItemById($setted_status_id);
			
			$comment= ('Установлен статус '.$stat['name'].', причина: '.SecStr($_POST['status_change_comment']));
			$log->PutEntry($result['id'],'смена статуса тендера',NULL,931,NULL,$comment,$id);
			
			
			//создадим запись в ленту
			 
				$_len=new Tender_HistoryItem;
				$len_params=array();
				$len_params['sched_id']=$id;
				$len_params['txt']=SecStr('<div>'.$comment.'</div>');
				$len_params['user_id']=$result['id'];
				$len_params['pdate']=time();
				
				 $_len->Add($len_params);
			 
			 
					
		}		
	}
	
	
	if(isset($_POST['to_set_konk'])){
		
		if($field_rights['to_set_konk']){
			$setted_status_id=26;
			$our_data=array( 'status_id'=>$setted_status_id);
			
			
			
			$_res->instance->Edit($id,$our_data , true, $result);
			
					  
			 
			$stat=$_dsi->GetItemById($setted_status_id);
			
			$comment= ('Установлен статус '.$stat['name']);//.', причина: '.SecStr($_POST['status_change_comment']));
			$log->PutEntry($result['id'],'смена статуса тендера',NULL,931,NULL,$comment,$id);
			
			
			//создадим запись в ленту
			/*if(strlen($_POST['status_change_comment'])>0){
				$_len=new Tender_HistoryItem;
				$len_params=array();
				$len_params['sched_id']=$id;
				$len_params['txt']=SecStr('<div>'.$comment.'</div>');
				$len_params['user_id']=$result['id'];
				$len_params['pdate']=time();
				
				 $_len->Add($len_params);
			}*/
			 
					
		}		
	}
	
	
	if(isset($_POST['to_go_konk'])){
		
		if($field_rights['to_go_konk']&&$au->user_rights->CheckAccess('w',975)){
			$setted_status_id=23;
			$our_data=array( 'status_id'=>$setted_status_id);
			 
				
			
			$_res->instance->Edit($id,$our_data , true, $result);
			
					  
			 
			$stat=$_dsi->GetItemById($setted_status_id);
			
			$comment= ('Установлен статус '.$stat['name']);//.', причина: '.SecStr($_POST['status_change_comment']));
			$log->PutEntry($result['id'],'смена статуса тендера',NULL,931,NULL,$comment,$id);
			
			
			//создадим запись в ленту
			/*if(strlen($_POST['status_change_comment'])>0){
				$_len=new Tender_HistoryItem;
				$len_params=array();
				$len_params['sched_id']=$id;
				$len_params['txt']=SecStr('<div>'.$comment.'</div>');
				$len_params['user_id']=$result['id'];
				$len_params['pdate']=time();
				
				 $_len->Add($len_params);
			}*/
			 
					
		}		
	}
	
	
	if(isset($_POST['to_no_go_konk'])){
		
		if($field_rights['to_no_go_konk']&&$au->user_rights->CheckAccess('w',973)){
			$setted_status_id=37;
			$our_data=array( 'status_id'=>$setted_status_id);
			//занести ПРИЧИНЫ ОТКАЗА
 
			$our_data['fail_reason_id']=abs((int)$_POST['status_change_comment_id']);
			$our_data['fail_reason']=SecStr($_POST['status_change_comment']);
			
			$_fi=new Tender_FailItem;
			$fi=$_fi->GetItemById($our_data['fail_reason_id']);
			
				
			
			$_res->instance->Edit($id,$our_data , true, $result);
			
					  
			 
			$stat=$_dsi->GetItemById($setted_status_id);
			
			$comment= ('Установлен статус '.$stat['name'].', причина: '.$fi['name'].' '.$our_data['fail_reason']);
			$log->PutEntry($result['id'],'смена статуса тендера',NULL,931,NULL,$comment,$id);
			
			
			//создадим запись в ленту
			 
				$_len=new Tender_HistoryItem;
				$len_params=array();
				$len_params['sched_id']=$id;
				$len_params['txt']=SecStr('<div>'.$comment.'</div>');
				$len_params['user_id']=$result['id'];
				$len_params['pdate']=time();
				
				 $_len->Add($len_params);
			 
			 
					
		}		
	}
	
	
	
	if(isset($_POST['to_claim_konk'])){
		
		if($field_rights['to_claim_konk']&&$au->user_rights->CheckAccess('w',976)){
			$setted_status_id=29;
			$our_data=array( 'status_id'=>$setted_status_id);
			 
			/*echo '<pre>';  
			print_r($_POST);
			echo '</pre>';
			die();	*/		
			
			
			
			
			$our_data['pdate_itog']=datefromdmy($_POST['pdate_itog']);
			 
				
			
			$_res->instance->Edit($id,$our_data , true, $result);
			
					  
			 
			$stat=$_dsi->GetItemById($setted_status_id);
			
			$comment= ('Установлен статус '.$stat['name'].', комментарий: '.SecStr($_POST['status_change_comment']).', дата подведения итога конкурса: '.$_POST['pdate_itog']);
			$log->PutEntry($result['id'],'смена статуса тендера',NULL,931,NULL,$comment,$id);
			
			//$log->PutEntry($result['id'],'редактирование тендера',NULL,931,NULL,'дата подведения итога конкурса: '.$_POST['pdate_itog'],$id);
			//создадим запись в ленту
			 
				$_len=new Tender_HistoryItem;
				$len_params=array();
				$len_params['sched_id']=$id;
				$len_params['txt']=SecStr('<div>'.$comment.'</div>');
				$len_params['user_id']=$result['id'];
				$len_params['pdate']=time();
				
				$len_code= $_len->Add($len_params);
			 
			 //прикрепим файлы
			 
			 
			 
			 $fmi=new Tender_HistoryFileItem;
			  foreach($_POST as $k=>$v){
				if(eregi("^tender_upload_file_",$k)){
					  $filename=eregi_replace("^tender_upload_file_", '', eregi_replace("_tmp$",".tmp",$k));
					  
					$fp=array('history_id '=>$len_code, 'filename'=>SecStr(basename($filename)), 'orig_name'=>SecStr($v));
					   
					//print_r($fp);  
					$file_id=$fmi->Add($fp);
					
					// $log->PutEntry($result['id'], 'прикрепил файл к задаче', NULL, 904, NULL,'Служебное имя файла: '.SecStr(basename($filename)).' Имя файла: '.SecStr($v),$code);
					
					$_ct=new FileContents(SecStr(iconv("utf-8","windows-1251", ($v))), $fmi->GetStoragePath().basename($filename));
		
					$contents='';
					
					try {
						$contents=$_ct->GetContents();
					} catch (Exception $e) {
						//echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
					}
					
					$fmi->Edit($file_id, array('text_contents'=>SecStr($contents))); 
					 
				}
			  }
			//die();
		}		
	}
	
	
	if(isset($_POST['to_win'])){
		
		if($field_rights['to_win']&&$au->user_rights->CheckAccess('w',977)){
			$setted_status_id=30;
			$our_data=array( 'status_id'=>$setted_status_id);
			 
				
			
			$_res->instance->Edit($id,$our_data , true, $result);
			
					  
			 
			$stat=$_dsi->GetItemById($setted_status_id);
			
			$comment= ('Установлен статус '.$stat['name']);//.', причина: '.SecStr($_POST['status_change_comment']));
			$log->PutEntry($result['id'],'смена статуса тендера',NULL,931,NULL,$comment,$id);
			
			
			//создадим запись в ленту
			/*if(strlen($_POST['status_change_comment'])>0){
				$_len=new Tender_HistoryItem;
				$len_params=array();
				$len_params['sched_id']=$id;
				$len_params['txt']=SecStr('<div>'.$comment.'</div>');
				$len_params['user_id']=$result['id'];
				$len_params['pdate']=time();
				
				 $_len->Add($len_params);
			}*/
			 
					
		}		
	}
	
	
	if(isset($_POST['to_fail'])){
		
		if($field_rights['to_fail']&&$au->user_rights->CheckAccess('w',978)){
			$setted_status_id=31;
			$our_data=array( 'status_id'=>$setted_status_id);
			 
				
			
			$_res->instance->Edit($id,$our_data , true, $result);
			
					  
			 
			$stat=$_dsi->GetItemById($setted_status_id);
			
			$comment= ('Установлен статус '.$stat['name']);//.', причина: '.SecStr($_POST['status_change_comment']));
			$log->PutEntry($result['id'],'смена статуса тендера',NULL,931,NULL,$comment,$id);
			
			
			//создадим запись в ленту
			/*if(strlen($_POST['status_change_comment'])>0){
				$_len=new Tender_HistoryItem;
				$len_params=array();
				$len_params['sched_id']=$id;
				$len_params['txt']=SecStr('<div>'.$comment.'</div>');
				$len_params['user_id']=$result['id'];
				$len_params['pdate']=time();
				
				 $_len->Add($len_params);
			}*/
			 
					
		}		
	}
	
	
	if(isset($_POST['to_rework'])){
		
		if($field_rights['to_rework']&&$au->user_rights->CheckAccess('w',983)){
			
			
			$our_data=array( 'is_fulfiled'=>0, 'is_confirmed_done'=>0);
			$_res->instance->Edit($id,$our_data);
			
			$setted_status_id=28;
			$our_data=array( 'status_id'=>$setted_status_id);
			 
				
			
			$_res->instance->Edit($id,$our_data , true, $result);
			
					  
			 
			$stat=$_dsi->GetItemById($setted_status_id);
			
			$comment= ('Тендер отправлен на доработку, установлен статус '.$stat['name'].', причина: '.SecStr($_POST['status_change_comment']));
			$log->PutEntry($result['id'],'смена статуса тендера',NULL,931,NULL,$comment,$id);
			
			
			//создадим запись в ленту
			if(strlen($_POST['status_change_comment'])>0){
				$_len=new Tender_HistoryItem;
				$len_params=array();
				$len_params['sched_id']=$id;
				$len_params['txt']=SecStr('<div>'.$comment.'</div>');
				$len_params['user_id']=$result['id'];
				$len_params['pdate']=time();
				
				 $_len->Add($len_params);
			} 
			 
					
		}		
	}
	
	
	
	if(isset($_POST['to_restore_work'])){
		
		if($field_rights['to_restore_work']&&$au->user_rights->CheckAccess('w',987)){
			
			 
			
			$setted_status_id=28;
			$_res->instance->Edit($id,array('is_confirmed_done'=>0, 'is_fulfiled'=>0),true, $result);
			 
			$_res->instance->Edit($id,array(  'status_id'=>$setted_status_id),true, $result);
			
					  
			 
			 
			$stat=$_dsi->GetItemById($setted_status_id);
			
			$comment= ('Тендер возвращен в работу, установлен статус '.$stat['name'].', причина: '.SecStr($_POST['status_change_comment']));
			 
			
			//создадим запись в ленту
			if(strlen($_POST['status_change_comment'])>0){
				$_len=new Tender_HistoryItem;
				$len_params=array();
				$len_params['sched_id']=$id;
				$len_params['txt']=SecStr('<div>'.$comment.'</div>');
				$len_params['user_id']=$result['id'];
				$len_params['pdate']=time();
				
				 $_len->Add($len_params);
			} 
			
			
			
			$stat=$_dsi->GetItemById($setted_status_id);
			$log->PutEntry($result['id'],'смена статуса тендера',NULL,931,NULL,'установлен статус '.$stat['name'],$id);
			
			 
					
		}		
	}
	
	 
		//утверждение заполнения
		
		$_res=new Tender_Resolver();
		
		if($editing_user['is_confirmed_done']==0){
		  
		  
		  	
		  if($editing_user['is_confirmed']==1){
			 
			  
			  // 
			  if($au->user_rights->CheckAccess('w',962)||($au->user_rights->CheckAccess('w',1116)&&in_array($editing_user['status_id'], array(2,18,33))) ){
				  if((!isset($_POST['is_confirmed']))&&($_res->instance->DocCanUnconfirmPrice($id,$rss32))){
					  
					  
					  $_res->instance->Edit($id,array('is_confirmed'=>0, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,$result);
					  
					  $log->PutEntry($result['id'],'снял утверждение заполнения',NULL,962, NULL, NULL,$id);	
					  
				  }
			  } 
			  
		  }else{
			  //есть права
			  if($au->user_rights->CheckAccess('w',931) ){
				  
				 
				  if(isset($_POST['is_confirmed'])&&($_POST['is_confirmed']==1)&&($_res->instance->DocCanConfirmPrice($id,$rss32))){
					  
					  $_res->instance->Edit($id,array('is_confirmed'=>1, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,$result);
					  
					  $log->PutEntry($result['id'],'утвердил заполнение',NULL,931, NULL, NULL,$id);	
					  
					   
					 
				  } 
			  } 
		  }
		}
		
		
		//утверждение выполнения
		if($editing_user['is_confirmed']==1){
		  if($editing_user['is_confirmed_done']==1){
			   /*if($editing_user['is_confirmed_done']==1){
				$can_confirm_shipping=$au->user_rights->CheckAccess('w',931)&&(($au->user_rights->CheckAccess('w',937))||($editing_user['manager_id']==$result['id']));
		  }else{
		  //ставим утв	
			  $can_confirm_shipping=$au->user_rights->CheckAccess('w',931)&&(($au->user_rights->CheckAccess('w',935))||($editing_user['manager_id']==$result['id']));
		  }*/
			  
			  
			  //есть права: либо сам утв.+есть права, либо есть искл. права:
			 
			 
			//  if(($au->user_rights->CheckAccess('w',931))&&($field_rights['can_unconfirm_done'])){
			if(!isset($_POST['is_confirmed_done'])&&($_res->instance->DocCanUnconfirmShip($id, $rss32))){
				
				$can_confirm_shipping=$au->user_rights->CheckAccess('w',931)&&(($au->user_rights->CheckAccess('w',937))/*||($editing_user['manager_id']==$result['id'])*/);
				if($can_confirm_shipping){	  
					  //echo 'zzzzzzzzzzzz';
					  $_res->instance->Edit($id,array('is_confirmed_done'=>0, 'user_confirm_done_id'=>$result['id'], 'confirm_done_pdate'=>time()),true,$result);
					  
					  $log->PutEntry($result['id'],'снял утверждение выполнения',NULL,931, NULL, NULL,$id);	
				}
			}
			 // }
			  
		  }else{
			   
			  //есть права
			  
			   if(isset($_POST['is_confirmed_done'])&&($_res->instance->DocCanConfirmShip($id, $rss32))){
				    $can_confirm_shipping=$au->user_rights->CheckAccess('w',931)&&(($au->user_rights->CheckAccess('w',935))/*||($editing_user['manager_id']==$result['id'])*/);
					if($can_confirm_shipping){	
					
					 
					 $_res->instance->Edit($id,array('is_confirmed_done'=>1, 'user_confirm_done_id'=>$result['id'], 'confirm_done_pdate'=>time()),true,$result);
					  
					  $log->PutEntry($result['id'],'утвердил выполнение',NULL,931, NULL, NULL,$id);	
					}
						  
				}
			    
		  }
		}
		
		
		
		//утверждение приема работы
		if($editing_user['is_confirmed_done']==1){
		  if($editing_user['is_fulfiled']==1){
			  //есть права: либо сам утв.+есть права, либо есть искл. права:
			 
			 
			  if(($au->user_rights->CheckAccess('w',938)) ){
				  if((!isset($_POST['is_fulfiled'])) &&($_res->instance->DocCanUnconfirmFulfil($id, $rss32))){
					  
					  //echo 'zzzzzzzzzzzz';
					  $_res->instance->Edit($id,array('is_fulfiled'=>0, 'user_fulfiled_id'=>$result['id'], 'fulfiled_pdate'=>time()),true,$result);
					  
					  $log->PutEntry($result['id'],'снял утверждение принятия работы',NULL,931, NULL, NULL,$id);	
				  }
			  }
			  
		  }else{
			   
			  //есть права
			  if($au->user_rights->CheckAccess('w',936) ){
				  if(isset($_POST['is_fulfiled'])&&($_res->instance->DocCanConfirmFulfil($id, $rss32))){
					 
					 $_res->instance->Edit($id,array('is_fulfiled'=>1, 'user_fulfiled_id'=>$result['id'], 'fulfiled_pdate'=>time()),true,$result);
					  
					  $log->PutEntry($result['id'],'утвердил принятие работы',NULL,931, NULL, NULL,$id);	
						  
				  }
			  } 
		  }
		}
	
	
	
	//die();
	
	//перенаправления
	if(isset($_POST['doEdit'])){
		//header("Location: shedule.php#user_".$id);
		
		if($editing_user['task_id']>0) header("Location: ed_tender.php?action=1&id=".$editing_user['task_id'].'&from_begin='.$from_begin);
		
		else header("Location: tenders.php#user_".$code);
		die();
	}elseif(isset($_POST['doEditStay'])||
	isset($_POST['to_work'])||
	isset($_POST['to_refuse'])||
	isset($_POST['to_cancel'])||
	isset($_POST['to_set_konk'])||
	isset($_POST['to_go_konk'])||
	isset($_POST['to_no_go_konk'])||
	isset($_POST['to_claim_konk'])||
	isset($_POST['to_win'])||
	isset($_POST['to_fail'])||
	isset($_POST['to_rework'])||
	isset($_POST['to_restore_work'])
	){
	 
		header("Location: ed_tender.php?action=1&id=".$id.'&from_begin='.$from_begin);
		die();	
		
	}else{
		header("Location: tenders.php");
		die();
	}
	
	die();
}


 //журнал событий 
if($action==1){
	$log=new ActionLog;
	 
	$log->PutEntry($result['id'],'открыл тендер',NULL,931, NULL, 'тендер № '.$editing_user['code'],$id);
	 
	//отметим тендер как просмотренный
	$_tview=new Tender_ViewItem;
	 
	$do_view=false;
	if($au->user_rights->CheckAccess('w',975)&&($editing_user['status_id']==26)){
		$do_view=$do_view||true;
	}elseif($au->user_rights->CheckAccess('w',947)&&($editing_user['manager_id']==0)&&($editing_user['is_confirmed']==1)){
		$do_view=$do_view||true;
	}
	if($do_view){
		$test_view=$_tview->GetItemByFields(array('tender_id'=>$id, 'user_id'=>$result['id']));
		if($test_view===false) $_tview->Add(array('tender_id'=>$id, 'user_id'=>$result['id']));		
	}
	
	//вызовем ленту комментариев, чтобы отметить прочитанными комментарии и корректно работал счетчик в меню
	$_hg=new Tender_HistoryGroup;
		$history= $_hg->ShowHistory(
			$editing_user['id'],
			 'tender/lenta'.$print_add.'.html', 
			 new DBDecorator(), 
			 $can_modify_ribbon,
			 true,
			 false,
			 $result,
			 $au->user_rights->CheckAccess('w',932),
			 $au->user_rights->CheckAccess('w',933),$history_data,true,true
			 );
			 
				
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


$_menu_id=72;
	
	if($print==0) include('inc/menu.php');
	
	
	//демонстрация  страницы
	$smarty = new SmartyAdm;
	
	$sm1=new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	//создание позиции
	 if($action==0){
		 
		 
		//виды тендеров
		$_tks=new TenderKindGroup;
		$tks=$_tks->GetItemsArr();
		//var_dump($tks);
		$_ids=array(); $_vals=array();
		$_ids[]=0; $_vals[]='-выберите-';
		foreach($tks as $k=>$v){
			$_ids[]=$v['id'];
			$_vals[]=$v['name'];
		}
		$sm1->assign('tender_ids', $_ids); $sm1->assign('tender_vals', $_vals);
		$sm1->assign('can_expand_kinds', $au->user_rights->CheckAccess('w',1146));
		$sm1->assign('kinds_total', $_tks->GetItemsList($au->user_rights->CheckAccess('w',1146), $au->user_rights->CheckAccess('w',1147)));
		
		//валюты
		$_curr=new PlCurrGroup;
		$sm1->assign('currs', $_curr->GetItemsArr(1));
		
		
		//типы оборудования 
		$_eqs=new Tender_EqTypeGroup;
		$eqs=$_eqs->GetItemsArr();
		$_ids=array(); $_vals=array();
		$_ids[]=0; $_vals[]='-выберите-';
		foreach($eqs as $k=>$v){
			$_ids[]=$v['id'];
			$_vals[]=$v['name'];
		}
		$sm1->assign('eq_ids', $_ids); $sm1->assign('eq_vals', $_vals);
		 
		//print_r($eqs); 
		 
		$sm1->assign('opfs_total', $eqs); 
		
		
		//виды ФЗ
		$_fzs=new Tender_FZGroup;
		$fzs=$_fzs->GetItemsArr();
		$_ids=array(); $_vals=array();
		$_ids[]=0; $_vals[]='-выберите-';
		foreach($fzs as $k=>$v){
			$_ids[]=$v['id'];
			$_vals[]=$v['name'];
		}
		$sm1->assign('fz_ids', $_ids); $sm1->assign('fz_vals', $_vals);
		 
		//print_r($eqs); 
		 
		$sm1->assign('fzs_total', $fzs); 
		
		
		
		 
		 
		if(isset($_GET['supplier_id'])&&($_GET['supplier_id']!=0)){
			//если задан КОНТРАГЕНТ:
			//подставить в карту город контрагента
			//вызвать диалог выбора контрагента, после чего в нем для:
			//случаев 2 -3- развернуть все контакты выбранного контрагента
			//случая 4 - развернуть все контакты с телефонами выбранного контрагента
			
			$supplier_id=abs((int)$_GET['supplier_id']);
			$sm1->assign('supplier_id', $supplier_id);
			
			$_si=new SupplierItem;
			$supplier=$_si->getitembyid($supplier_id);
			$sm1->assign('supplier', $supplier);
			
			 
			
		 
		} 
		 
		 
		 
		
		 
		 
	//	$sm1->assign('now',  DateFromYmd($datetime)); 
		
		
		$sm1->assign('now_time',  date('d.m.Y H:i:s')); 
		$sm1->assign('now_date',  date('d.m.Y')); 
		 
	 
	 	$from_hrs=array();
		//$from_hrs[]='';
		for($i=0;$i<=23;$i++) $from_hrs[]=sprintf("%02d",$i);
		$sm1->assign('ptime_finish_h',$from_hrs);
		$sm1->assign('ptime_claiming_h',$from_hrs);
		
				
		$from_ms=array();
		//$from_ms[]='';
		for($i=0;$i<=59;$i++) $from_ms[]=sprintf("%02d",$i);
		$sm1->assign('ptime_finish_m',$from_ms);
		$sm1->assign('ptime_claiming_m',$from_ms);
		
		
		
		 
		$sm1->assign('session_id', session_id());
		
		$sm1->assign('can_expand_types', $au->user_rights->CheckAccess('w',939));
		$sm1->assign('can_add_supplier', $au->user_rights->CheckAccess('w',87));
		
		$sm1->assign('can_expand_fz', $au->user_rights->CheckAccess('w',964));
		
		
		//возможность менять отв. сотр: права 1148 или рук-ль ОП
		$dec=new DBDecorator();
		//.dep.name
		$dec ->AddEntry(new SqlEntry('pos.name','руководитель отдела', SqlEntry::LIKE));
		$dec ->AddEntry(new SqlEntry('dep.name','отдел продаж', SqlEntry::LIKE));
		$dec ->AddEntry(new SqlEntry('u.is_active',1, SqlEntry::E));
		$ug=new UsersSGroup;
		$users=$ug->GetItemsByDecArr($dec);
		$user_ids=array(); foreach($users as $k=>$v) $user_ids[]=$v['id'];
		
		$sm1->assign('can_select_manager', $au->user_rights->CheckAccess('w',1148)||in_array($result['id'], $user_ids));
		
		
		
		//получим список тех, кто может снять утверждение заполнения
		$_usg1=new UsersSGroup;
		$usg1=$_usg1->GetUsersByRightArr('w', 962);
		$sm1->assign('can_unconfirm_users',$usg1);
		$sm1->assign('can_unconfirm',$au->user_rights->CheckAccess('w',962)||$au->user_rights->CheckAccess('w',1116));
		
		
		
		$user_form=$sm1->fetch('tender/create_tender.html');
		 
	
	 }elseif($action==1){
		//редактирование позиции
		
		if($print==0) $print_add='';
		else $print_add='_print';
		
		
		//построим доступы
		$can_modify=in_array($editing_user['status_id'],$_editable_status_id);
		$can_modify_ribbon=!in_array($editing_user['status_id'], array(30, 31, 32, 3, 10));
		
		
		//построим доступы
		$_roles=new Tender_FieldRules($result); //var_dump($_roles->GetTable());
		$field_rights=$_roles->GetFields($editing_user, $result['id']);
		$sm1->assign('field_rights', $field_rights);
		
		//можно менять срок подачи заявок
		 $can_modify_claiming=($au->user_rights->CheckAccess('w',963)&&($editing_user['is_confirmed']==1)&&!in_array($editing_user['status_id'], array(29,30,31,32,3)));
		$sm1->assign('can_modify_claiming', $can_modify_claiming);
		 
		//валюты
		$_curr=new PlCurrGroup;
		$sm1->assign('currs', $_curr->GetItemsArr($editing_user['currency_id']));
		
		
		$_wg=new Tender_WorkingGroup;
		$working_time_unf=$_wg->CalcTotalWorkingTime($id, $zz, $times,$is_working );
 //CalcWorkingTime($id, $zz, $times);
		
		$editing_user['times']=$times;
		$editing_user['working_time_unf']=$working_time_unf;
		
		 
		
		
		$_res=new Tender_Resolver();
		
		$editing_user['pdate']=date('d.m.Y H:i:s', $editing_user['pdate']);
		
		
		if($editing_user['pdate_placing']!='')  $editing_user['pdate_placing']=datefromYmd($editing_user['pdate_placing']);
		
		if($editing_user['pdate_claiming']!='')  $editing_user['pdate_claiming']=datefromYmd($editing_user['pdate_claiming']);
		
		if($editing_user['pdate_finish']!='')  $editing_user['pdate_finish']=datefromYmd($editing_user['pdate_finish']);
		
		if($editing_user['pdate_itog']!='')  $editing_user['pdate_itog']=date('d.m.Y', $editing_user['pdate_itog']);
		
		
		
		$from_hrs=array();
		//$from_hrs[]='';
		for($i=0;$i<=23;$i++) $from_hrs[]=sprintf("%02d",$i);
		$sm1->assign('ptime_finish_hs',$from_hrs);
		$sm1->assign('ptime_claiming_hs',$from_hrs);
	 
		$from_ms=array();
		//$from_ms[]='';
		for($i=0;$i<=59;$i++) $from_ms[]=sprintf("%02d",$i);
		$sm1->assign('ptime_finish_ms',$from_ms);
		$sm1->assign('ptime_claiming_ms',$from_ms);
		
		if($editing_user['ptime_finish']!=""){
			$sm1->assign('ptime_finish_h',substr($editing_user['ptime_finish'],  0,2 ));
			$sm1->assign('ptime_finish_m',substr($editing_user['ptime_finish'],  3,2 )); 
		}
		if($editing_user['ptime_claiming']!=""){
			$sm1->assign('ptime_claiming_h',substr($editing_user['ptime_claiming'],  0,2 ));
			$sm1->assign('ptime_claiming_m',substr($editing_user['ptime_claiming'],  3,2 )); 
		}
		  
		  
		  //виды тендеров
		$_tks=new TenderKindGroup;
		$tks=$_tks->GetItemsArr();
		//var_dump($tks);
		$_ids=array(); $_vals=array();
		$_ids[]=0; $_vals[]='-выберите-';
		foreach($tks as $k=>$v){
			$_ids[]=$v['id'];
			$_vals[]=$v['name'];
		}
		$sm1->assign('tender_ids', $_ids); $sm1->assign('tender_vals', $_vals);
		$_tki=new TenderKindItem;
		$tki=$_tki->GetItemById($editing_user['kind_id']);
		$editing_user['kind_name']=$tki['name'];
		
		
		$sm1->assign('kinds_total', $_tks->GetItemsList($au->user_rights->CheckAccess('w',1146), $au->user_rights->CheckAccess('w',1147)));
		$sm1->assign('can_expand_kinds', $au->user_rights->CheckAccess('w',1146));
		
		
		
		//типы оборудования 
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
		$tki=$_tki->GetItemById($editing_user['eq_type_id']);
		$editing_user['eq_name']=$tki['name'];   
		
		$sm1->assign('opfs_total', $eqs);
		
		
	//виды ФЗ
		$_fzs=new Tender_FZGroup;
		$fzs=$_fzs->GetItemsArr();
		$_ids=array(); $_vals=array();
		$_ids[]=0; $_vals[]='-выберите-';
		foreach($fzs as $k=>$v){
			$_ids[]=$v['id'];
			$_vals[]=$v['name'];
		}
		$sm1->assign('fz_ids', $_ids); $sm1->assign('fz_vals', $_vals);
		 
		//print_r($eqs); 
		 
		$sm1->assign('fzs_total', $fzs); 
		
		
		
		
		
		
		//причины отказа
		$_fails=new Tender_FailGroup;
		$fails=$_fails->GetItemsArr();
		$_ids=array(); $_vals=array();
		$_ids[]=0; $_vals[]='-выберите-';
		foreach($fails as $k=>$v){
			$_ids[]=$v['id'];
			$_vals[]=$v['name'];
		}
		$sm1->assign('fail_ids', $_ids); $sm1->assign('fail_vals', $_vals);
		
		 $_tki=new Tender_FailItem;
		$tki=$_tki->GetItemById($editing_user['fail_reason_id']);
		$editing_user['fail_name']=$tki['name'];   
		
		
		
		
		
		
		
		
		
		//блок аннулирования
		
		$editing_user['can_annul']=$_res->instance->DocCanAnnul($editing_user['id'],$reason,$editing_user)&&($au->user_rights->CheckAccess('w',972)||($result['id']==$editing_user['created_id']) );
		if(!($au->user_rights->CheckAccess('w',972)||($result['id']==$editing_user['created_id']))) $reason='недостаточно прав для данной операции';
		$editing_user['can_annul_reason']=$reason;
		
		 
		
		$editing_user['can_restore']=$_res->instance->DocCanRestore($editing_user['id'],$reason,$editing_user)&&$au->user_rights->CheckAccess('w',931);
			if(!$au->user_rights->CheckAccess('w',931)) $reason='недостаточно прав для данной операции';
		
		
		
		
		 
		
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
			  
			  $can_confirm_price=$au->user_rights->CheckAccess('w',931);
			  
			  
			  
		  }else{
			  //95
			  $can_confirm_price=$au->user_rights->CheckAccess('w',931)&&in_array($editing_user['status_id'],$_editable_status_id)  ;
		  }
		}
		$sm1->assign('can_confirm',$can_confirm_price);
		
		
			//получим список тех, кто может снять утверждение заполнения
		$_usg1=new UsersSGroup;
		$usg1=$_usg1->GetUsersByRightArr('w', 962);
		$sm1->assign('can_unconfirm_users',$usg1);
		$sm1->assign('can_unconfirm',$au->user_rights->CheckAccess('w',962)||($au->user_rights->CheckAccess('w',1116)&&in_array($editing_user['status_id'], array(2,18,33))));
		
		
		
		
		//блок утв. выполнения
		if(($editing_user['is_confirmed_done']==1)&&($editing_user['user_confirm_done_id']!=0)){
			$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['user_confirm_done_id']);
			$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.date("d.m.Y H:i:s",$editing_user['confirm_done_pdate']);
			
			$sm1->assign('is_confirmed_done_confirmer',$confirmer);
		}
		
		$can_confirm_shipping=false;
		if($editing_user['is_confirmed']==1){
		
		   
		  if($editing_user['is_confirmed_done']==1){
				$can_confirm_shipping=$au->user_rights->CheckAccess('w',931)&&(($au->user_rights->CheckAccess('w',937))/*||($editing_user['manager_id']==$result['id'])*/);
		  }else{
		  //ставим утв	
			  $can_confirm_shipping=$au->user_rights->CheckAccess('w',931)&&(($au->user_rights->CheckAccess('w',935))/*||($editing_user['manager_id']==$result['id'])*/);
		  }
		}
		// + есть галочка утв. цен
		$can_confirm_shipping=$can_confirm_shipping&&($editing_user['is_confirmed']==1);
		
		
		
		$sm1->assign('can_confirm_done',$can_confirm_shipping);
		
		
		
		//блок утв. принятия
		if(($editing_user['is_fulfiled']==1)&&($editing_user['user_fulfiled_id']!=0)){
			$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['user_fulfiled_id']);
			$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.date("d.m.Y H:i:s",$editing_user['fulfiled_pdate']);
			
			$sm1->assign('is_fulfiled_confirmer',$confirmer);
		}
		
		$can_confirm_shipping=false;
		if($editing_user['is_confirmed_done']==1){
		
		  if($editing_user['is_fulfiled']==1){
			   $can_confirm_shipping=$au->user_rights->CheckAccess('w',938);
		  }else{
			  //95
			  $can_confirm_shipping=$au->user_rights->CheckAccess('w',936);
		  }
		}
		// + есть галочка утв. цен
		$can_confirm_shipping=$can_confirm_shipping&&($editing_user['is_confirmed_done']==1);
		
		
		$sm1->assign('can_confirm_fulfil',$can_confirm_shipping);
		
		
		
		$reason='';
		
		
		$sm1->assign('can_unconfirm_by_document',(int)$_res->instance->DocCanUnconfirmShip($editing_user['id'],$reason));
		$sm1->assign('can_unconfirm_by_document_reason',$reason);
		
		
		
		//отвеств сотр-к
		$_user_s=new UserSItem;
		$user_s=$_user_s->GetItemById($editing_user['manager_id']);
		$editing_user['manager_string']=$user_s['name_s'];
		
		
	
		
		//лента задачи
		$_hg=new Tender_HistoryGroup;
		$history= $_hg->ShowHistory(
			$editing_user['id'],
			 'tender/lenta'.$print_add.'.html', 
			 new DBDecorator(), 
			 $can_modify_ribbon,
			 true,
			 false,
			 $result,
			 $au->user_rights->CheckAccess('w',932),
			 $au->user_rights->CheckAccess('w',933),$history_data,true,true
			 );
		$sm1->assign('lenta',$history);
		$sm1->assign('lenta_len',count($history_data));
		
		
		
		 
		//контрагенты
		$_suppliers=new Tender_SupplierGroup;
		$sup=$_suppliers->GetItemsByIdArr($editing_user['id']);
		$sm1->assign('suppliers', $sup);
		
		
	    
		
		
		
	 
		
		
		$sm1->assign('can_modify', $can_modify);  
		 $sm1->assign('can_modify_ribbon', $can_modify_ribbon);  
		 
		//смена менеджера при проставленной 1 галочке 
		//$sm1->assign('can_change_manager', ($editing_user['is_confirmed']==1)&&($editing_user['is_confimed_done']==0)&&(in_array($editing_user['status_id'], array(33,2,28)))&&($au->user_rights->CheckAccess('w',979)||($editing_user['manager_id']==$result['id'])));
		
		//возможность менять отв. сотр: права 1148, 979 или рук-ль ОП
		$dec=new DBDecorator();
		//.dep.name
		$dec ->AddEntry(new SqlEntry('pos.name','руководитель отдела', SqlEntry::LIKE));
		$dec ->AddEntry(new SqlEntry('dep.name','отдел продаж', SqlEntry::LIKE));
		$dec ->AddEntry(new SqlEntry('u.is_active',1, SqlEntry::E));
		$ug=new UsersSGroup;
		$users=$ug->GetItemsByDecArr($dec);
		$user_ids=array(); foreach($users as $k=>$v) $user_ids[]=$v['id'];
		
		$can_select_manager=true;
		
		if($editing_user['is_confirmed']==0) $can_select_manager=$can_select_manager&&$au->user_rights->CheckAccess('w',1148);
		else $can_select_manager=$can_select_manager&&$au->user_rights->CheckAccess('w',979);
		
		if(in_array($result['id'], $user_ids)) $can_select_manager=$can_select_manager||true;
		
		
		
		$sm1->assign('can_select_manager', $can_select_manager);
		
		  
		 
		$sm1->assign('can_add_supplier', $au->user_rights->CheckAccess('w',87)); 
		
		$sm1->assign('can_expand_types', $au->user_rights->CheckAccess('w',939));
		$sm1->assign('can_modify_iam',  ($editing_user['is_confirmed_done']==0)&&($editing_user['manager_id']==0));
		
		 
		$sm1->assign('can_expand_fz', $au->user_rights->CheckAccess('w',964));
		
		//права на кнопки смены статуса
		$sm1->assign('can_to_refuse', $au->user_rights->CheckAccess('w',973));
		$sm1->assign('can_to_cancel', $au->user_rights->CheckAccess('w',974));
		$sm1->assign('can_to_go_konk', $au->user_rights->CheckAccess('w',975));
		$sm1->assign('can_to_claim_konk', $au->user_rights->CheckAccess('w',976));
		$sm1->assign('can_to_win', $au->user_rights->CheckAccess('w',977));
		$sm1->assign('can_to_fail', $au->user_rights->CheckAccess('w',978));
		$sm1->assign('can_to_rework', $au->user_rights->CheckAccess('w',983));
		
		
		//права редактировать состав причин отказа
		$sm1->assign('can_edit_fail_reasons',$au->user_rights->CheckAccess('w',982));
		
		
		
		//права на кнопку Создать лид в карте тендера
		$sm1->assign('can_create_lead', $au->user_rights->CheckAccess('w',949)&&($editing_user['is_confirmed']==1) );
		
		//право на прикрепление лида
		$sm1->assign('can_join_lead', $au->user_rights->CheckAccess('w',948)&&in_array($editing_user['status_id'], array(28,26,23,29)) );
		
		  //права на кнопку вернуть в работу в статусе отказ
		 $sm1->assign('can_to_restore_work', $au->user_rights->CheckAccess('w',987));
		 
		
		
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
			  
			  
			  
			  
			  $ffg=new TenderFileGroup(1,  $id,  new FileDocFolderItem(1,  $id, new TenderFileItem(1)));;
			  
			  $filetext=$ffg->ShowFiles('tender/tender_files_list.html', $decorator,0,10000,'ed_tender.php', 'tender_file.html', 'swfupl-js/tender_files.php',  
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
			   $navi_dec, 'file_' 
			   );
		
		
		$sm1->assign('files', $filetext);
		 
		 
		 
		$user_form=$sm1->fetch('tender/edit_tender'.$print_add.'.html');
		 
		
	 
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
		
			$decorator->AddEntry(new SqlEntry('object_id',NULL, SqlEntry::IN_VALUES, NULL, array(929,
930,
931,
932,
933,
934,
935,
936,
937,
938,
962
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
			
			$llg=$log->ShowLog('syslog/log_doc.html',$decorator,$from,$to_page,'ed_tender.php',true,true,true);
			
			$sm->assign('syslog',$llg);		
				
		} 
		
		
	}
	

/********************************************************************************************/	
	//вкладка ЛИДЫ
	$sm1=new smartyadm;
	$sm1->assign('bill', $editing_user);
	
	//$sm1->assign('can_create_lead', $au->user_rights->CheckAccess('w',949)&&($editing_user['is_confirmed']==1));
	if(($action==1)&&($au->user_rights->CheckAccess('w',948))){
	
		$_plans=new Lead_Group;
		$_plans->SetAuthResult($result);
		$_plans->SetPageName('ed_tender.php');
 
 
		$prefix='_leads';
		
		if(isset($_GET['from'.$prefix])) $from=abs((int)$_GET['from'.$prefix]);
		  else $from=0;
		if(isset($_GET['to_page'.$prefix])) $to_page=abs((int)$_GET['to_page'.$prefix]);
		  else $to_page=ITEMS_PER_PAGE;
		  
		$decorator=new DBDecorator;
		
		
		$decorator->AddEntry(new UriEntry('action',1));
		$decorator->AddEntry(new UriEntry('id',$id));
		$decorator->AddEntry(new UriEntry('tender_id',$id));
		 $decorator->AddEntry(new SqlEntry('p.tender_id',$id, SqlEntry::E));
		
		//контроль видимости
		if(!$au->user_rights->CheckAccess('w',953)){
			$decorator->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::IN_VALUES, NULL,$_plans->GetAvailableLeadIds($result['id'])));	
		}
	 	
	 
		 
		 if(isset($_GET['code'.$prefix])&&(strlen($_GET['code'.$prefix])>0)){
		  $decorator->AddEntry(new SqlEntry('p.code',SecStr($_GET['code'.$prefix]), SqlEntry::LIKE));
		  $decorator->AddEntry(new UriEntry('code',$_GET['code'.$prefix]));
		}
		
		
		if(isset($_GET['topic'.$prefix])&&(strlen($_GET['topic'.$prefix])>0)){
		  $decorator->AddEntry(new SqlEntry('p.topic',SecStr($_GET['topic'.$prefix]), SqlEntry::LIKE));
		  $decorator->AddEntry(new UriEntry('topic',$_GET['topic'.$prefix]));
		}
		
		
		 
		if(isset($_GET['eq_name'.$prefix])&&(strlen($_GET['eq_name'.$prefix])>0)){
			$decorator->AddEntry(new SqlEntry('p.eq_type_id',abs((int)$_GET['eq_name'.$prefix]), SqlEntry::E));
			$decorator->AddEntry(new UriEntry('eq_name',$_GET['eq_name'.$prefix]));
		}  
		
		
		if(isset($_GET['kind_name'.$prefix])&&(strlen($_GET['kind_name'.$prefix])>0)){
			$decorator->AddEntry(new SqlEntry('p.kind_id',abs((int)$_GET['kind_name'.$prefix]), SqlEntry::E));
			$decorator->AddEntry(new UriEntry('kind_name',$_GET['kind_name'.$prefix]));
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
			$decorator->AddEntry(new SqlEntry('p.pdate_finish',date('Y-m-d', DateFromdmY($given_pdate1)), SqlEntry::BETWEEN,date('Y-m-d', DateFromdmY($given_pdate2))));
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
		  $cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^'.$prefix.'sched_'.$prefix.'status_id_', $k)) $cou_stat++;
		  
		  if($cou_stat>0){
			  //есть кукисы
			  foreach($_COOKIE as $k=>$v) if(eregi('^'.$prefix.'sched_'.$prefix.'status_id_', $k)) $status_ids[]=(int)eregi_replace('^'.$prefix.'sched_'.$prefix.'status_id_','',$k);
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
				$decorator->AddEntry(new SqlOrdEntry('eq.name',SqlOrdEntry::DESC));
			break;	
			case 3:
				$decorator->AddEntry(new SqlOrdEntry('eq.name',SqlOrdEntry::ASC));
			break; 
			
			case 4:
				$decorator->AddEntry(new SqlOrdEntry('p.topic',SqlOrdEntry::DESC));
			break;	
			case 5:
				$decorator->AddEntry(new SqlOrdEntry('p.topic',SqlOrdEntry::ASC));
			break;
			case 6:
				$decorator->AddEntry(new SqlOrdEntry('kind.name',SqlOrdEntry::DESC));
			break;	
			case 7:
				$decorator->AddEntry(new SqlOrdEntry('kind.name',SqlOrdEntry::ASC));
			break;
			case 8:
				$decorator->AddEntry(new SqlOrdEntry('sup.full_name',SqlOrdEntry::DESC));
				
			break;	
			case 9:
				$decorator->AddEntry(new SqlOrdEntry('sup.full_name',SqlOrdEntry::ASC));
				
			break;
			case 10:
				$decorator->AddEntry(new SqlOrdEntry('u.name_s',SqlOrdEntry::DESC));
				
			break;	
			case 11:
				$decorator->AddEntry(new SqlOrdEntry('u.name_s',SqlOrdEntry::ASC));
				
			break;
			
			case 12:
				$decorator->AddEntry(new SqlOrdEntry('p.status_id',SqlOrdEntry::DESC));
				
			break;	
			case 13:
				$decorator->AddEntry(new SqlOrdEntry('p.status_id',SqlOrdEntry::ASC));
				
			break;
			
			
			/*case 14:
				$decorator->AddEntry(new SqlOrdEntry('p.pdate_placing',SqlOrdEntry::DESC));
				 
			break;	
			case 15:
				$decorator->AddEntry(new SqlOrdEntry('p.pdate_placing',SqlOrdEntry::ASC));
			break;
			
			
			case 16:
				$decorator->AddEntry(new SqlOrdEntry('p.pdate_claiming',SqlOrdEntry::DESC));
				 
			break;	
			case 17:
				$decorator->AddEntry(new SqlOrdEntry('p.pdate_claiming',SqlOrdEntry::ASC));
			break;
			*/
			
			case 18:
				$decorator->AddEntry(new SqlOrdEntry('p.pdate_finish',SqlOrdEntry::DESC));
				 
			break;	
			case 19:
				$decorator->AddEntry(new SqlOrdEntry('p.pdate_finish',SqlOrdEntry::ASC));
			break;
			
			
			default:
					
				$decorator->AddEntry(new SqlOrdEntry('s.weight',SqlOrdEntry::DESC));
				
				$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::DESC));
				 
			break;	
			
		}
		 
		$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
		
		
	
		 
		
		$leads=$_plans->ShowPos(
				
			'lead/table.html',  //0
			 $decorator, //1
			  $au->user_rights->CheckAccess('w',949)&&($editing_user['is_confirmed']==1), //2
			  $au->user_rights->CheckAccess('w',950), //3
			  $from, //4
			  $to_page, //5
			  true, //6
			  false,  //7
			  $au->user_rights->CheckAccess('w',950), //8
			  $au->user_rights->CheckAccess('w',961),  //9
			  $au->user_rights->CheckAccess('w',950), //10
			  $au->user_rights->CheckAccess('w',950), //11
			  $au->user_rights->CheckAccess('w',950), //12
			  $au->user_rights->CheckAccess('w',950), //13
			  $au->user_rights->CheckAccess('w',956), //14
			  $au->user_rights->CheckAccess('w',957), //15
			 
	 
			  $au->user_rights->CheckAccess('w',958), //16
			  $au->user_rights->CheckAccess('w',959), //17
			  
			$prefix //18
			 );


 





 
		
		
		
		
		$sm->assign('leads', $leads);
		  
	}
	
	//$sm->assign('leads', $sm1->fetch('tender/nested_leads.html'));	
	
	 
		 
	$sm->assign('from_begin',$from_begin);
	
	$sm->assign('print_pdate', date("d.m.Y H:i:s"));
	//$username=$result['login'];
	$username=stripslashes($result['name_s']).' '.$username;	
	$sm->assign('print_username',$username);
	
	$sm->assign('users',$user_form);
	$content=$sm->fetch('tender/ed_tender_page'.$print_add.'.html');
	
	
	
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