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



require_once('classes/lead_fileitem.php');
require_once('classes/lead_filegroup.php');
 

require_once('classes/supplier_country_group.php');
require_once('classes/supplier_city_item.php');
 
require_once('classes/lead_history_group.php');
require_once('classes/docstatusitem.php');

require_once('classes/lead_history_item.php'); 

require_once('classes/lead_view_item.php');
require_once('classes/kpgroup.php');

require_once('classes/an_lead_actions.class.php');

require_once('classes/doc_in.class.php');
require_once('classes/doc_out.class.php');
require_once('classes/doc_vn.class.php');
require_once('classes/tender_history_item.php');


$_pch=new PeriodChecker;
$pch_date=$_pch->GetDate();



$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Лид');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}


$_dem=new Lead_AbstractItem;

$_plan1=new Lead_Group;
$available_users=$_plan1->GetAvailableLeadIds($result['id']);

$_plan=new Lead_Group;


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

if(!isset($_GET['show_tz'])){
	if(!isset($_POST['show_tz'])){
		$show_tz=0;
	}else $show_tz=1; 
}else $show_tz=1;

$object_id=array();
switch($action){
	case 0:
	$object_id[]=949;
	break;
	case 1:
	$object_id[]=950;
	break;
	case 2:
	$object_id[]=950;
	break;
	default:
	$object_id[]=950;
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
	
	
	$_tg=new lead_Group;
	
	if(!$au->user_rights->CheckAccess('w',953)){	
		$available_tenders=$_tg->GetAvailableLeadIds($result['id']);
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
	
	$params['tender_id']=abs((int)$_POST['tender_id']);
	
	
	 $params['probability']=abs((float)str_replace(',','.',$_POST['probability']));
	  
	$params['ptime_finish']= SecStr($_POST['ptime_finish_h']).':'.SecStr($_POST['ptime_finish_m']).':00';
	
	 
	
	 
	$params['topic']= SecStr($_POST['topic']);
	$params['description']= SecStr($_POST['description']);
	
	
 	$params['pdate_finish']= date('Y-m-d', DateFromdmY($_POST['pdate_finish']));
	
	$price=abs((float)eregi_replace("[ ]+","", str_replace(',','.',$_POST['max_price'])));
 	if($_POST['max_price']=="") $params['max_price']=NULL;
	else  $params['max_price']=$price;
	
		 $params['currency_id']=abs((int)$_POST['currency_id']);
	
 
	
	$params['status_id']=18;
	
	$params['producer_id']= abs((int)$_POST['producer_id']);
	if(isset($_POST['wo_producer'])) $params['wo_producer']=1; else $params['wo_producer']=0;
			
	 
	$_res=new Lead_Resolver();
		
		
	$code=	$_res->instance->Add($params);
	 
	//$code=1;
	//запись в журнале
	if($code>0){
		$log->PutEntry($result['id'],'создал лид',NULL,949,NULL,NULL,$code);	
		
		foreach($params as $k=>$v){
			
		  
				
				$log->PutEntry($result['id'],'создал лид',NULL,949, NULL, 'в поле '.$k.' установлено значение '.$v,$code);		
			 
		}
		
	}
	
	  
	
	
	
	
	 
	//приложим файлы!
	//upload_file_6A83_tmp" value="_ZpaGsu91PI.jpg" 
	$fmi=new LeadFileItem;
	foreach($_POST as $k=>$v){
	  if(eregi("^upload_file_",$k)){
		    $filename=eregi_replace("^upload_file_", '', eregi_replace("_tmp$",".tmp",$k));
		  $fmi->Add(array('bill_id'=>$code, 'filename'=>SecStr(basename($filename)), 'orig_name'=>SecStr($v), 'user_id'=>$result['id'], 'pdate'=>time()));
		  
		   $log->PutEntry($result['id'], 'прикрепил файл к лиду', NULL, 949, NULL,'Служебное имя файла: '.SecStr(basename($filename)).' Имя файла: '.SecStr($v),$code);
		   
		   
	  }
	}
	 
	
	
	
	 //контрагенты
		$_supplier=new SupplierItem;
		$_sg=new Lead_SupplierGroup;
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
				  $log->PutEntry($result['id'],'добавил контрагента в лид',NULL,949,NULL,$description,$code);	
			  }elseif($v['action']==1){
				  $log->PutEntry($result['id'],'редактировал контрагента в лиде',NULL,949,NULL,$description,$code);
			  }elseif($v['action']==2){
				  $log->PutEntry($result['id'],'удалил контрагента из лида',NULL,949,NULL,$description,$code);
			  }
			  
		  }
		  
		  
		  
	if($au->user_rights->CheckAccess('w',949)&&($_POST['do_confirm']==1)){
	
	
		$_res->instance->Edit($code,array('is_confirmed'=>1, 'user_confirm_id'=>$result['id'], ' 	confirm_pdate'=>time()),true,$result);
					  
		$log->PutEntry($result['id'],'автоматически утвердил заполнение лида',NULL,949, NULL, NULL,$code);	
		
		
	}else{
		$log->PutEntry($result['id'],'отказался от автоматического утверждения заполнения лида',NULL,949, NULL, NULL,$code);	
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
		 header("Location: leads.php#user_".$code);
		die();
	}elseif(isset($_POST['doNewEdit'])){
		  
		header("Location: ed_lead.php?action=1&id=".$code.'&from_begin='.$from_begin);
		die();	
		
	}else{
		header("Location: leads.php");
		die();
	}
	 
	
	die();
	


}elseif(($action==1)&&(isset($_POST['doEdit'])||isset($_POST['doEditStay'])
	||isset($_POST['to_work'])
	||isset($_POST['to_rework'])
	||isset($_POST['to_cancel'])
	||isset($_POST['to_view'])
	||isset($_POST['to_defer'])
	
	||isset($_POST['to_win'])
	||isset($_POST['to_fail'])
	||isset($_POST['to_restore_work'])
	
	)){
	 
	
	//редактирование возможно, если позволяет статус
	$_res=new Lead_Resolver();
		
	
	//поля формируем в зависимости от их активности в текущем статусе
	$_roles=new Lead_FieldRules($result); //var_dump($_roles->GetTable());
	$field_rights0=$_roles->GetFields($editing_user, $result['id']);	
	$field_rights1=$_roles->GetFields($editing_user, $result['id'], $_POST['current_status_id']);
	$field_rights=array(); 
	foreach($field_rights0 as $k=>$v) $field_rights[$k]=$v&&$field_rights1[$k];
	
			
	
	$params=array();

	
	//поля формируем в зависимости от их активности в текущем статусе
	$condition =in_array($_POST['current_status_id'],$_editable_status_id)&&in_array($editing_user['status_id'],$_editable_status_id);
	
	if($condition){
	
	
		 $params['manager_id']=abs((int)$_POST['manager_id']);
	
		$params['kind_id']=abs((int)$_POST['kind_id']);
		
		$params['eq_type_id']=abs((int)$_POST['eq_type_id']);
		
		
		 		  
	$params['ptime_finish']= SecStr($_POST['ptime_finish_h']).':'.SecStr($_POST['ptime_finish_m']).':00';
	
	
		$price=abs((float)eregi_replace("[ ]+","", str_replace(',','.',$_POST['max_price'])));
 	if($_POST['max_price']=="") $params['max_price']=NULL;
	else $params['max_price']=$price;
	
		 $params['currency_id']=abs((int)$_POST['currency_id']);
		 
		$params['topic']= SecStr($_POST['topic']);
		$params['description']= SecStr($_POST['description']);
		
		
	 	$params['pdate_finish']= date('Y-m-d', DateFromdmY($_POST['pdate_finish']));
		
		$params['producer_id']= abs((int)$_POST['producer_id']);
		if(isset($_POST['wo_producer'])) $params['wo_producer']=1; else $params['wo_producer']=0;
		
		if(isset($_POST['probability']))  $params['probability']=abs((float)str_replace(',','.',$_POST['probability']));
	
		
	}
	else{
		//кроме основного условия, еще может сработать дополнительное:	
		if(($editing_user['is_confirmed_done']==0)&&($editing_user['manager_id']==0)){
			$params['manager_id']=abs((int)$_POST['manager_id']);
		}
		
		
		//спецправа на смену менеджера
		if(($editing_user['is_confirmed']==1)&&($editing_user['is_confirmed_done']==0)&&in_array($editing_user['status_id'], array(33,2,28))&&($au->user_rights->CheckAccess('w',980)||($editing_user['manager_id']==$result['id']))){
			
			$params['manager_id']=abs((int)$_POST['manager_id']);
			
			if(($_POST['manager_delete_previous']==1)&&($params['manager_id']!=0)&&($params['manager_id']!=$editing_user['manager_id'])){
				//удалить из карты контрагента предыдущего куратора, внести запись в журнал об этом
				
				
				$_suresp=new SupplierResponsibleUserItem;
				$_tsup=new Lead_SupplierItem;
				$tsupplier=$_tsup->GetItemByFields(array('sched_id'=>$id));
				if($tsupplier!==false){
			
					$tsupplier_id=$tsupplier['supplier_id'];
					
					$test_resp=$_suresp->GetItemByFields(array('supplier_id'=>$tsupplier_id, 'user_id'=>$editing_user['manager_id']));
					
					$_tui=new UserSItem;
					$tui=$_tui->GetItemById($editing_user['manager_id']);
					
					if($test_resp!==false){
						$description=SecStr('контрагент '.$tsupplier['code'].' '.$tsupplier['full_name'].' '.$tui['name_s'].' '.$tui['login'].', лид '.$editing_user['code']);
				   
				  
						$_suresp->Del($test_resp['id']);
						$log->PutEntry($result['id'],'удалил ответственного сотрудника из карты контрагента при смене куратора лида',NULL,910,NULL,$description,$tsupplier_id);	
						
						$log->PutEntry($result['id'],'удалил ответственного сотрудника из карты контрагента при смене куратора лида',NULL,950,NULL,$description,$id);	
					}
				}
			}
		}
		
		
		
		
		
		//смена прогноз даты
		if(
								 (($editing_user['is_confirmed']==1)&&($editing_user['is_confirmed_done']==0)&&$au->user_rights->CheckAccess('w',966))||
								 (($editing_user['is_confirmed']==1)&&($editing_user['is_confirmed_done']==0)&&($editing_user['manager_id']==$result['id']))||
								 (($editing_user['status_id']==29)&&eregi("Тендерный специалист",$result['position_name']))
								 ){
									 
		
			$params['pdate_finish']= date('Y-m-d', DateFromdmY($_POST['pdate_finish']));
			$params['ptime_finish']= SecStr($_POST['ptime_finish_h']).':'.SecStr($_POST['ptime_finish_m']).':00';
			
			if(($editing_user['status_id']==29)&&eregi("Тендерный специалист",$result['position_name'])&&
			(
				($params['pdate_finish']!=$editing_user['pdate_finish'])||
				($editing_user['ptime_finish']!=SecStr($_POST['ptime_finish_h']).':'.SecStr($_POST['ptime_finish_m']).':00')
			 
				
			)){
				//комментарии в т и л, записи в журнал в т и л
				$descr=SecStr('смена прогнозируемой даты заключения контракта по лиду '.$editing_user['code'].' тендерным специалистом '.$result['name_s'].', установлена дата '.$_POST['pdate_finish'].' '.$_POST['ptime_finish_h'].':'.$_POST['ptime_finish_m'].':00');
				$log->PutEntry($result['id'],'редактировал лид',NULL,950, NULL,$descr ,$id);	
				
				$_lh=new Lead_HistoryItem;
				$_lh->Add(array(
					'sched_id'=>$id,
					'pdate'=>time(),
					'user_id'=>0,
					'txt'=>SecStr('<div>Автоматический комментарий: '.$descr.'</div>')
				
				));
				
				//если определен ТЕНДЕР
				if($editing_user['tender_id']!=0){
					$descr=SecStr('смена прогнозируемой даты заключения контракта по связанному лиду '.$editing_user['code'].' тендерным специалистом '.$result['name_s'].', установлена дата '.$_POST['pdate_finish'].' '.$_POST['ptime_finish_h'].':'.$_POST['ptime_finish_m'].':00');
					
					$log->PutEntry($result['id'],'редактировал лид',NULL,931, NULL,$descr ,$editing_user['tender_id']);
					$_lh=new Tender_HistoryItem;
					$_lh->Add(array(
						'sched_id'=>$editing_user['tender_id'],
						'pdate'=>time(),
						'user_id'=>0,
						'txt'=>SecStr('<div>Автоматический комментарий: '.$descr.'</div>')
					
					));
					
				}
				
			}
		}
	}
//	
	
	$_res->instance->Edit($id, $params);
	
		
	//$_dem->Edit($id, $params);
	//die();
	//запись в журнале
	//записи в лог. сравнить старые и новые записи
	foreach($params as $k=>$v){
		
		if(addslashes($editing_user[$k])!=$v){
			$log->PutEntry($result['id'],'редактировал лид',NULL,950, NULL, 'в поле '.$k.' установлено значение '.$v,$id);
			
					
		}	
		
	}
	
 	if($condition){
	
	//контрагенты
	 
		$_supplier=new SupplierItem;
		$_sg=new Lead_SupplierGroup;
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
				  $log->PutEntry($result['id'],'добавил контрагента к лиду',NULL,949,NULL,$description,$id);	
			  }elseif($v['action']==1){
				  $log->PutEntry($result['id'],'редактировал контрагента в лиде',NULL,949,NULL,$description,$id);
			  }elseif($v['action']==2){
				  $log->PutEntry($result['id'],'удалил контрагента из лида',NULL,949,NULL,$description,$id);
			  }
			  
		  }
	  
	}
	
	
	 
	 
	
	
	$_dsi=new DocStatusItem; 
	//обработка выделенных кнопок
 
	
	//обработка выделенных кнопок
	if(isset($_POST['to_work'])){
		
		if($field_rights['to_work']){
			
			$setted_status_id=28;
			$_res->instance->Edit($id,array( 'status_id'=>$setted_status_id),true, $result);
			
					  
			//$log->PutEntry($result['id'],'отправил задачу на доработку',NULL,905, NULL, NULL,$id);
			
			$stat=$_dsi->GetItemById($setted_status_id);
			$log->PutEntry($result['id'],'смена статуса лида',NULL,950,NULL,'установлен статус '.$stat['name'],$id);
			
			 
					
		}		
	}
	
	if(isset($_POST['to_cancel'])){
		
		if($field_rights['to_cancel']){
			$setted_status_id=37;
			$our_data=array( 'status_id'=>$setted_status_id);
			//занести ПРИЧИНЫ ОТКАЗА
 
			$our_data['fail_reason_id']=abs((int)$_POST['status_change_comment_id']);
			$our_data['fail_reason']=SecStr($_POST['status_change_comment']);
			
			$_fi=new Lead_FailItem;
			$fi=$_fi->GetItemById($our_data['fail_reason_id']);
			
				
			
			$_res->instance->Edit($id,$our_data,true, $result);
			
			
					  
			//внести комментарий
			 
			$_tsi=new Lead_HistoryItem;
			$_tsi->Add(array(
				'sched_id'=>$id,
				'pdate'=>time(),
				'user_id'=>0,
				'txt'=>SecStr('<div>Автоматический комментарий: сотрудник '.$result['name_s'].' перевел лид в статус "Отказ", причина: '.$fi['name'].' '.$our_data['fail_reason'].'</div>')
			
			));
			
			
						  
			 
			$stat=$_dsi->GetItemById($setted_status_id);
			$log->PutEntry($result['id'],'смена статуса лида',NULL,950,NULL,'установлен статус '.$stat['name'].', причина: '.$fi['name'].' '.$our_data['fail_reason'],$id);
			
			
			 
					
		}		
	}
	
	if(isset($_POST['to_view'])){
		
		if($field_rights['to_view']){
			
			$setted_status_id=35;
			$_res->instance->Edit($id,array( 'status_id'=>$setted_status_id),true, $result);
			
					  
			//внести комментарий
			$comment=SecStr('<div>Автоматический комментарий: сотрудник '.$result['name_s'].' перевел лид в статус "На рассмотрении", приняв следующие положения:</div>');
			
			
			if($_POST['status_change_review_comment_1']==1) $comment.=SecStr('<div>Коммерческое предложение по лиду согласовано.</div>');
			
			if($_POST['status_change_review_comment_2']==1) $comment.=SecStr('<div>Коммерческое предложение по лиду получено покупателем.</div>');
			
			if($_POST['status_change_review_comment_3']==1) $comment.=SecStr('<div>Покупатель по коммерческому предложению претензий не имеет.</div>');
			
			 
			$_tsi=new Lead_HistoryItem;
			$_tsi->Add(array(
				'sched_id'=>$id,
				'pdate'=>time(),
				'user_id'=>0,
				'txt'=>$comment
			
			));
			
			
			$stat=$_dsi->GetItemById($setted_status_id);
			$log->PutEntry($result['id'],'смена статуса лида',NULL,950,NULL,'установлен статус '.$stat['name'],$id);
			
			 
					
		}		
	}
	
	
	if(isset($_POST['to_defer'])){
		
		if($field_rights['to_defer']){
			
			$setted_status_id=25;
			$_res->instance->Edit($id,array( 'status_id'=>$setted_status_id, 'pdate_finish'=>date('Y-m-d', DateFromdmY($_POST['new_defer_pdate']))),true, $result);
			
					  
			$log->PutEntry($result['id'],'редактировал лид',NULL,950, NULL, 'установлена новая прогнозируемая дата заключения контракта '.SecStr($_POST['new_defer_pdate']),$id);
			
			$stat=$_dsi->GetItemById($setted_status_id);
			$log->PutEntry($result['id'],'смена статуса лида',NULL,950,NULL,'установлен статус '.$stat['name'],$id);
			
			 
					
		}		
	}
	
	
	if(isset($_POST['to_win'])){
		
		if($field_rights['to_win']){
			
			$setted_status_id=30;
			$_res->instance->Edit($id,array( 'status_id'=>$setted_status_id),true, $result);
			
					  
			//$log->PutEntry($result['id'],'отправил задачу на доработку',NULL,905, NULL, NULL,$id);
			
			$stat=$_dsi->GetItemById($setted_status_id);
			$log->PutEntry($result['id'],'смена статуса лида',NULL,950,NULL,'установлен статус '.$stat['name'],$id);
			
			 
					
		}		
	}
	
	if(isset($_POST['to_fail'])){
		
		if($field_rights['to_fail']){
			
			$setted_status_id=31;
			$_res->instance->Edit($id,array( 'status_id'=>$setted_status_id),true, $result);
			
					  
			//$log->PutEntry($result['id'],'отправил задачу на доработку',NULL,905, NULL, NULL,$id);
			
			$stat=$_dsi->GetItemById($setted_status_id);
			$log->PutEntry($result['id'],'смена статуса лида',NULL,950,NULL,'установлен статус '.$stat['name'],$id);
			
			 
					
		}		
	}
	
	if(isset($_POST['to_rework'])){
		
		if($field_rights['to_rework']&&$au->user_rights->CheckAccess('w',981)){
			
			$setted_status_id=28;
			$_res->instance->Edit($id,array( 'status_id'=>$setted_status_id),true, $result);
			
					  
			 
			 
			$stat=$_dsi->GetItemById($setted_status_id);
			
			$comment= ('Лид отправлен на доработку, установлен статус '.$stat['name'].', причина: '.SecStr($_POST['status_change_comment']));
			 
			
			//создадим запись в ленту
			if(strlen($_POST['status_change_comment'])>0){
				$_len=new Lead_HistoryItem;
				$len_params=array();
				$len_params['sched_id']=$id;
				$len_params['txt']=SecStr('<div>'.$comment.'</div>');
				$len_params['user_id']=$result['id'];
				$len_params['pdate']=time();
				
				 $_len->Add($len_params);
			} 
			
			
			
			$stat=$_dsi->GetItemById($setted_status_id);
			$log->PutEntry($result['id'],'смена статуса лида',NULL,950,NULL,SecStr($comment),$id);
			$log->PutEntry($result['id'],'смена статуса лида',NULL,950,NULL,'установлен статус '.$stat['name'],$id);
			
			 
					
		}		
	}
	
	
	
	if(isset($_POST['to_restore_work'])){
		
		if($field_rights['to_restore_work']&&$au->user_rights->CheckAccess('w',988)){
			
			 
			
			$setted_status_id=28;
			$_res->instance->Edit($id,array('is_confirmed_done'=>0),true, $result);
			$_res->instance->Edit($id,array('status_id'=>$setted_status_id),true, $result);
			
					  
			 
			 
			$stat=$_dsi->GetItemById($setted_status_id);
			
			$comment= ('Лид возвращен в работу, установлен статус '.$stat['name'].', причина: '.SecStr($_POST['status_change_comment']));
			 
			
			//создадим запись в ленту
			if(strlen($_POST['status_change_comment'])>0){
				$_len=new Lead_HistoryItem;
				$len_params=array();
				$len_params['sched_id']=$id;
				$len_params['txt']=SecStr('<div>'.$comment.'</div>');
				$len_params['user_id']=$result['id'];
				$len_params['pdate']=time();
				
				 $_len->Add($len_params);
			} 
			
			
			
			$stat=$_dsi->GetItemById($setted_status_id);
			$log->PutEntry($result['id'],'смена статуса лида',NULL,950,NULL,SecStr($comment),$id);
			$log->PutEntry($result['id'],'смена статуса лида',NULL,950,NULL,'установлен статус '.$stat['name'],$id);
			
			 
					
		}		
	}
	
	
	 
	 
		//утверждение заполнения
		
		$_res=new Lead_Resolver();
		
		if($editing_user['is_confirmed_done']==0){
		  
		  
		  	
		  if($editing_user['is_confirmed']==1){
			 
			  
			  // 
			  if(($au->user_rights->CheckAccess('w',965)) ){
				  if((!isset($_POST['is_confirmed']))&&($_res->instance->DocCanUnconfirmPrice($id,$rss32))){
					  
					  
					  $_res->instance->Edit($id,array('is_confirmed'=>0, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,$result);
					  
					  $log->PutEntry($result['id'],'снял утверждение заполнения',NULL,965, NULL, NULL,$id);	
					  
				  }
			  } 
			  
		  }else{
			  //есть права
			  if($au->user_rights->CheckAccess('w',950) ){
				  if(isset($_POST['is_confirmed'])&&($_POST['is_confirmed']==1)&&($_res->instance->DocCanConfirmPrice($id,$rss32))){
					  
					  $_res->instance->Edit($id,array('is_confirmed'=>1, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,$result);
					  
					  $log->PutEntry($result['id'],'утвердил заполнение',NULL,950, NULL, NULL,$id);	
					  
					   
					  //die();
				  }
			  } 
		  }
		}
		
		
		//утверждение выполнения
		if($editing_user['is_confirmed']==1){
		  if($editing_user['is_confirmed_done']==1){
			  
			  
			  
			  //есть права: либо сам утв.+есть права, либо есть искл. права:
			 
			 
			//  if(($au->user_rights->CheckAccess('w',950))&&($field_rights['can_unconfirm_done'])){
			if(!isset($_POST['is_confirmed_done'])&&($_res->instance->DocCanUnconfirmShip($id, $rss32))){
				
				$can_confirm_shipping=$au->user_rights->CheckAccess('w',950)&&(($au->user_rights->CheckAccess('w',958))/*||($editing_user['manager_id']==$result['id'])*/);
				if($can_confirm_shipping){	  
					  //echo 'zzzzzzzzzzzz';
					  $_res->instance->Edit($id,array('is_confirmed_done'=>0, 'user_confirm_done_id'=>$result['id'], 'confirm_done_pdate'=>time()),true,$result);
					  
					  $log->PutEntry($result['id'],'снял утверждение выполнения',NULL,950, NULL, NULL,$id);	
				}
			}
			 // }
			  
		  }else{
			   
			  //есть права
			  
			   if(isset($_POST['is_confirmed_done'])&&($_res->instance->DocCanConfirmShip($id, $rss32))){
				    $can_confirm_shipping=$au->user_rights->CheckAccess('w',950)&&(($au->user_rights->CheckAccess('w',956))/*||($editing_user['manager_id']==$result['id'])*/);
					if($can_confirm_shipping){	
					
					 
					 $_res->instance->Edit($id,array('is_confirmed_done'=>1, 'user_confirm_done_id'=>$result['id'], 'confirm_done_pdate'=>time()),true,$result);
					  
					  $log->PutEntry($result['id'],'утвердил выполнение',NULL,950, NULL, NULL,$id);	
					}
						  
				}
			    
		  }
		}
		
		
		
		//утверждение приема работы
		if($editing_user['is_confirmed_done']==1){
		  if($editing_user['is_fulfiled']==1){
			  //есть права: либо сам утв.+есть права, либо есть искл. права:
			 
			 
			  if(($au->user_rights->CheckAccess('w',959)) ){
				  if((!isset($_POST['is_fulfiled'])) &&($_res->instance->DocCanUnconfirmFulfil($id, $rss32))){
					  
					  //echo 'zzzzzzzzzzzz';
					  $_res->instance->Edit($id,array('is_fulfiled'=>0, 'user_fulfiled_id'=>$result['id'], 'fulfiled_pdate'=>time()),true,$result);
					  
					  $log->PutEntry($result['id'],'снял утверждение принятия работы',NULL,950, NULL, NULL,$id);	
				  }
			  }
			  
		  }else{
			   
			  //есть права
			  if($au->user_rights->CheckAccess('w',957) ){
				  if(isset($_POST['is_fulfiled'])&&($_res->instance->DocCanConfirmFulfil($id, $rss32))){
					 
					 $_res->instance->Edit($id,array('is_fulfiled'=>1, 'user_fulfiled_id'=>$result['id'], 'fulfiled_pdate'=>time()),true,$result);
					  
					  $log->PutEntry($result['id'],'утвердил принятие работы',NULL,950, NULL, NULL,$id);	
						  
				  }
			  } 
		  }
		}
	
	
	
	//die();
	
	//перенаправления
	if(isset($_POST['doEdit'])){
		//header("Location: shedule.php#user_".$id);
		
		/*if($editing_user['task_id']>0) header("Location: ed_tender.php?action=1&id=".$editing_user['task_id'].'&from_begin='.$from_begin);
		
		else */
		header("Location: leads.php#user_".$code);
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
	 
		header("Location: ed_lead.php?action=1&id=".$id.'&from_begin='.$from_begin);
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
	 
	$log->PutEntry($result['id'],'открыл лид',NULL,950, NULL, 'лид № '.$editing_user['code'],$id);
	 
	//отметим лид как просмотренный
	$_tview=new Lead_ViewItem;
	$test_view=$_tview->GetItemByFields(array('lead_id'=>$id, 'user_id'=>$result['id']));
	if($test_view===false) $_tview->Add(array('lead_id'=>$id, 'user_id'=>$result['id']));	
	
	//лента задачи - вызовем ее для отметки комментариев, как прочитанных
		$_hg=new Lead_HistoryGroup;
		$history= $_hg->ShowHistory(
			$editing_user['id'],
			 'lead/lenta'.$print_add.'.html', 
			 new DBDecorator(), 
			 $can_modify_ribbon,
			 true,
			 false,
			 $result,
			 $au->user_rights->CheckAccess('w',951),
			 $au->user_rights->CheckAccess('w',952),$history_data,true,true
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


$_menu_id=75;
	
	if($print==0) include('inc/menu.php');
	
	
	//демонстрация  страницы
	$smarty = new SmartyAdm;
	
	$sm1=new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	//создание позиции
	 if($action==0){
		 
		 
		//виды лидов
		$_tks=new LeadKindGroup;
		$tks=$_tks->GetItemsArr();
		//var_dump($tks);
		$_ids=array(); $_vals=array();
		$_ids[]=0; $_vals[]='-выберите-';
		foreach($tks as $k=>$v){
			$_ids[]=$v['id'];
			$_vals[]=$v['name'];
		}
		$sm1->assign('tender_ids', $_ids); $sm1->assign('tender_vals', $_vals);
		
		
		//валюты
		 $_curr=new PlCurrGroup;
		$sm1->assign('currs', $_curr->GetItemsArr(0));
		 
		
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
		 
		 
		if(isset($_GET['tender_id'])&&($_GET['tender_id']!=0)){
			 $tender_id=abs((int)$_GET['tender_id']);
			
			 $sm1->assign('kind_id', 3);
			 
			 //подставить контрагента, ответственных из ТЕНДЕРА
			 
			 $_tender=new Tender_Item;
			 $tender=$_tender->GetItemById($tender_id);
			 $sm1->assign('tender', $tender);
			 
			 //контрагенты
			$_suppliers1=new Tender_SupplierGroup;
			$sup1=$_suppliers1->GetItemsByIdArr($tender_id);
			$sup2=array();
			if(count($sup1)>0) $sup2[]=$sup1[0];
			$sm1->assign('suppliers', $sup2);
			
			//отвеств сотр-к
			$_user_s=new UserSItem;
			$user_s=$_user_s->GetItemById($tender['manager_id']);
			 
			$sm1->assign('manager_id', $tender['manager_id']);
			$sm1->assign('manager_string', $user_s['name_s']);
			
			//оборудование из тендера
			$sm1->assign('eq_type_id', $tender['eq_type_id']);
			 	
		}else $tender_id=0;
		
		 $sm1->assign('tender_id', $tender_id); 
	 
		
		
		$_prods=new PlProdGroup;
		$prods=$_prods->GetItemsArr();
		$sm1->assign('prods',  $prods); 
		
		
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
		
		
		//получим список тех, кто может снять утверждение заполнения
		$_usg1=new UsersSGroup;
		$usg1=$_usg1->GetUsersByRightArr('w', 965);
		$sm1->assign('can_unconfirm_users',$usg1);
		$sm1->assign('can_unconfirm',$au->user_rights->CheckAccess('w',965));
		
		
		 
		$sm1->assign('session_id', session_id());
		
		$sm1->assign('can_expand_types', $au->user_rights->CheckAccess('w',939));
		$sm1->assign('can_add_supplier', $au->user_rights->CheckAccess('w',87));
		
		$sm1->assign('can_modify_supplier', ($tender_id==0));
		$sm1->assign('can_modify_manager', ($tender_id==0));
		$sm1->assign('can_modify_eq_type', ($tender_id==0));
		
		$sm1->assign('can_confirm', $au->user_rights->CheckAccess('w',949));
		
		
		$user_form=$sm1->fetch('lead/create_lead.html');
		 
		
		
		$sm->assign('has_tz', $au->user_rights->CheckAccess('w',1005));
		$sm->assign('tzs','Работа с ТЗ по лиду в данном режиме невозможна. Пожалуйста, сохраните и утвердите лид для создания по нему ТЗ.');
		
		$sm->assign('kp_ins','Работа с входящими КП по лиду в данном режиме невозможна. Пожалуйста, сохраните и утвердите лид для создания по нему входящего КП.');
		
		
	 }elseif($action==1){
		//редактирование позиции
		
		if($print==0) $print_add='';
		else $print_add='_print';
		
		
		//построим доступы
		$_roles=new Lead_FieldRules($result); //var_dump($_roles->GetTable());
		$field_rights=$_roles->GetFields($editing_user, $result['id']);
		$sm1->assign('field_rights', $field_rights);
		
		//var_dump($field_rights);
		
		
		//построим доступы
		$can_modify=in_array($editing_user['status_id'],$_editable_status_id);
		$can_modify_ribbon=!in_array($editing_user['status_id'], array(30, 31, 32, 3, 10));
		
		
		 
		$can_modify_pdate_finish=in_array($editing_user['status_id'],$_editable_status_id)||
								 (($editing_user['is_confirmed']==1)&&($editing_user['is_confirmed_done']==0)&&$au->user_rights->CheckAccess('w',966))||
								 (($editing_user['is_confirmed']==1)&&($editing_user['is_confirmed_done']==0)&&($editing_user['manager_id']==$result['id']))||
								 (($editing_user['status_id']==29)&&eregi("Тендерный специалист",$result['position_name']));
								 
		 $sm1->assign('can_modify_pdate_finish', $can_modify_pdate_finish);						 
		
		 if($editing_user['tender_id']!=0){
			 
			 //подставить ТЕНДЕР
			 
			 $_tender=new Tender_Item;
			 $tender=$_tender->GetItemById($editing_user['tender_id']);
			 $sm1->assign('tender', $tender);
			 
		 
			 	
		}
		
		
		$_wg=new Lead_WorkingGroup;
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
		
		$working_time_unf=$_wg->CalcWorkingTime($id, 3, $zz, $times,$is_working);
		$editing_user['times_3']=$times;
		$editing_user['working_time_unf_3']=$working_time_unf;
		$editing_user['3_is_working']=$is_working;
		 
		$working_time_unf=$_wg->CalcWorkingTime($id, 4, $zz, $times,$is_working);
		$editing_user['times_4']=$times;
		$editing_user['working_time_unf_4']=$working_time_unf;
		$editing_user['4_is_working']=$is_working;
		
		$working_time_unf=$_wg->CalcWorkingTime($id, 5, $zz, $times, $is_working );
		$editing_user['times_5']=$times;
		$editing_user['working_time_unf_5']=$working_time_unf;
		$editing_user['5_is_working']=$is_working;
		
		
		 
		$working_time_unf=$_wg->CalcWorkingTime($id, 6, $zz, $times,$is_working);
		$editing_user['times_6']=$times;
		$editing_user['working_time_unf_6']=$working_time_unf;
		$editing_user['6_is_working']=$is_working;
		
		 
		  
		 
		$working_time_unf=$_wg->CalcTotalWorkingTime($id, $zz, $times,$is_working );
		$editing_user['times_total']=$times;
		$editing_user['working_time_unf_total']=$working_time_unf;
		$editing_user['total_is_working']=$is_working;
		
		
		$_res=new Lead_Resolver();
		
		$editing_user['pdate']=date('d.m.Y H:i:s', $editing_user['pdate']);
		
		
		
		
		if($editing_user['pdate_finish']!='')  $editing_user['pdate_finish']=datefromYmd($editing_user['pdate_finish']);
		
		
		$from_hrs=array();
		//$from_hrs[]='';
		for($i=0;$i<=23;$i++) $from_hrs[]=sprintf("%02d",$i);
		$sm1->assign('ptime_finish_hs',$from_hrs);
	 
				
		$from_ms=array();
		//$from_ms[]='';
		for($i=0;$i<=59;$i++) $from_ms[]=sprintf("%02d",$i);
		$sm1->assign('ptime_finish_ms',$from_ms);
		 
		if($editing_user['ptime_finish']!=""){
			$sm1->assign('ptime_finish_h',substr($editing_user['ptime_finish'],  0,2 ));
			$sm1->assign('ptime_finish_m',substr($editing_user['ptime_finish'],  3,2 )); 
		}
		 
		  
		  //виды тендеров
		$_tks=new LeadKindGroup;
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
		
		
		
		//типы оборудования 
		$_eqs=new Lead_EqTypeGroup;
		$eqs=$_eqs->GetItemsArr();
		$_ids=array(); $_vals=array();
		$_ids[]=0; $_vals[]='-выберите-';
		foreach($eqs as $k=>$v){
			$_ids[]=$v['id'];
			$_vals[]=$v['name'];
		}
		$sm1->assign('eq_ids', $_ids); $sm1->assign('eq_vals', $_vals);
		 $_tki=new Lead_EqTypeItem;
		$tki=$_tki->GetItemById($editing_user['eq_type_id']);
		$editing_user['eq_name']=$tki['name'];   
		
		$sm1->assign('opfs_total', $eqs);
		
		
		$_prods=new PlProdGroup;
		$prods=$_prods->GetItemsArr();
		$sm1->assign('prods',  $prods); 
		
		
		//валюты
		$_curr=new PlCurrGroup;
		$sm1->assign('currs', $_curr->GetItemsArr($editing_user['currency_id']));
		
		
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
		
		
		
		//блок аннулирования
		
		$editing_user['can_annul']=$_res->instance->DocCanAnnul($editing_user['id'],$reason,$editing_user, $result)&&$au->user_rights->CheckAccess('w',950);
		if(!$au->user_rights->CheckAccess('w',950)) $reason='недостаточно прав для данной операции';
		$editing_user['can_annul_reason']=$reason;
		
		 
		
		$editing_user['can_restore']=$_res->instance->DocCanRestore($editing_user['id'],$reason,$editing_user)&&$au->user_rights->CheckAccess('w',961);
			if(!$au->user_rights->CheckAccess('w',961)) $reason='недостаточно прав для данной операции';
		
		
		
			//получим список тех, кто может снять утверждение заполнения
		$_usg1=new UsersSGroup;
		$usg1=$_usg1->GetUsersByRightArr('w', 965);
		$sm1->assign('can_unconfirm_users',$usg1);
		$sm1->assign('can_unconfirm',$au->user_rights->CheckAccess('w',965));
		
		
		 
		
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
			  if($au->user_rights->CheckAccess('w',950)){
				  //есть права + сам утвердил
				  $can_confirm_price=true;	
			  }else{
				  $can_confirm_price=false;
			  }
		  }else{
			  //95
			  $can_confirm_price=$au->user_rights->CheckAccess('w',950)&&in_array($editing_user['status_id'],$_editable_status_id)  ;
		  }
		}
		$sm1->assign('can_confirm',$can_confirm_price);
		
		
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
				$can_confirm_shipping=$au->user_rights->CheckAccess('w',950)&&(($au->user_rights->CheckAccess('w',958))/*||($editing_user['manager_id']==$result['id'])*/);
		  }else{
		  //ставим утв	
			  $can_confirm_shipping=$au->user_rights->CheckAccess('w',950)&&(($au->user_rights->CheckAccess('w',956))/*||($editing_user['manager_id']==$result['id'])*/);
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
			   $can_confirm_shipping=$au->user_rights->CheckAccess('w',959);
		  }else{
			  //95
			  $can_confirm_shipping=$au->user_rights->CheckAccess('w',957);
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
		$_hg=new Lead_HistoryGroup;
		$history= $_hg->ShowHistory(
			$editing_user['id'],
			 'lead/lenta'.$print_add.'.html', 
			 new DBDecorator(), 
			 $can_modify_ribbon,
			 true,
			 false,
			 $result,
			 $au->user_rights->CheckAccess('w',951),
			 $au->user_rights->CheckAccess('w',952),$history_data,true,true
			 );
		$sm1->assign('lenta',$history);
		$sm1->assign('lenta_len',count($history_data));
		
		
		
		 
		//контрагенты
		$_suppliers=new Lead_SupplierGroup;
		$sup=$_suppliers->GetItemsByIdArr($editing_user['id']);
		$sm1->assign('suppliers', $sup);
		
		
	    
		//связанные документы: ТЗ, КП...
		$_docs=$_res->instance->GetFactTZ($id);
		$sm1->assign('binded_tzs', $_docs);
			
			
		
		
	 
		
		
		$sm1->assign('can_modify', $can_modify);  
		 $sm1->assign('can_modify_ribbon', $can_modify_ribbon);  
		 
		 
		 $sm1->assign('can_modify_supplier', ( $can_modify&&($editing_user['tender_id']==0)));
		
		//смена менеджера при проставленной 1 галочке 
		$sm1->assign('can_change_manager', ($editing_user['is_confirmed']==1)&&($editing_user['is_confimed_done']==0)&&(in_array($editing_user['status_id'], array(33,2,28)))&&($au->user_rights->CheckAccess('w',980)||($editing_user['manager_id']==$result['id'])));   
		 
		 $sm1->assign('can_modify_manager', ( $can_modify&&($editing_user['tender_id']==0)));
		$sm1->assign('can_modify_eq_type', ( $can_modify&&($editing_user['tender_id']==0)));
		 
		$sm1->assign('can_add_supplier', $au->user_rights->CheckAccess('w',87)); 
		
		$sm1->assign('can_expand_types', $au->user_rights->CheckAccess('w',939));
		$sm1->assign('can_modify_iam',  ($editing_user['is_confirmed_done']==0)&&($editing_user['manager_id']==0));
		
		//блокировка оппераций смены статуса, если лид - тендерный.
		$sm1->assign('tender_blocked', ($editing_user['tender_id']!=0)&&($editing_user['kind_id']==3));
		 
		 
		 //права на кнопку отправить на доработку...
		 $sm1->assign('can_rework', $au->user_rights->CheckAccess('w',981));
		 
		  //права на кнопку вернуть в работу в статусе отказ
		 $sm1->assign('can_to_restore_work', $au->user_rights->CheckAccess('w',988));
		 
		 
		//права редактировать состав причин отказа
		$sm1->assign('can_edit_fail_reasons',$au->user_rights->CheckAccess('w',982));
		
		//права на создание ТЗ
		$sm1->assign('can_create_tz', ($au->user_rights->CheckAccess('w',1007)||($au->user_rights->CheckAccess('w',1006)&&(($result['id']==$editing_user['manager_id'])||($result['id']==$editing_user['created_id'])))));
		
		
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
			  
			  
			  
			  
			  $ffg=new LeadFileGroup(1,  $id,  new FileDocFolderItem(1,  $id, new LeadFileItem(1)));;
			  
			  $filetext=$ffg->ShowFiles('lead/lead_files_list.html', $decorator,0,10000,'ed_lead.php', 'lead_file.html', 'swfupl-js/lead_files.php',  
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
		$user_form=$sm1->fetch('lead/edit_lead'.$print_add.'.html');
		$sm->assign('wo_producer', $editing_user['wo_producer']);
		  
		 
/*****************************************************************************************************/
//блок ТЗ по Лиду
		$sm->assign('has_tz', $au->user_rights->CheckAccess('w',1005));
		//$sm->assign('tzs','Работа с ТЗ по лиду в данном режиме невозможна. Пожалуйста, сохраните и утвердите лид для создания по нему ТЗ.');		 
		 
		 //вывести список ТЗ
		 $_tzs=new TZ_Group;
		 
		 
		$_tzs->SetAuthResult($result);
		$_tzs->SetPageName('ed_lead.php');
 
 
		$prefix='_tzs';
		
		if(isset($_GET['from'.$prefix])) $from=abs((int)$_GET['from'.$prefix]);
		  else $from=0;
		if(isset($_GET['to_page'.$prefix])) $to_page=abs((int)$_GET['to_page'.$prefix]);
		  else $to_page=ITEMS_PER_PAGE;
		  
		$decorator=new DBDecorator;
		
		
		$decorator->AddEntry(new UriEntry('action',1));
		$decorator->AddEntry(new UriEntry('id',$id));
		$decorator->AddEntry(new UriEntry('lead_id',$id));
		$decorator->AddEntry(new SqlEntry('p.lead_id',$id, SqlEntry::E));
		
		 
		 //контроль видимости
		if(!$au->user_rights->CheckAccess('w',1008)){
			$decorator->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::IN_VALUES, NULL,$_tzs->GetAvailableTZIds($result['id'])));	
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
			$decorator->AddEntry(new SqlEntry('p.pdate',  DateFromdmY($given_pdate1), SqlEntry::BETWEEN, DateFromdmY($given_pdate2)));
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
		  $cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^'.$prefix.'tz_'.$prefix.'status_id_', $k)) $cou_stat++;
		  
		  if($cou_stat>0){
			  //есть кукисы
			  foreach($_COOKIE as $k=>$v) if(eregi('^'.$prefix.'tz_'.$prefix.'status_id_', $k)) $status_ids[]=(int)eregi_replace('^'.$prefix.'tz_'.$prefix.'status_id_','',$k);
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
				$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
			break;	
			case 3:
				$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::ASC));
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
			
			 
			
			default:
					
				$decorator->AddEntry(new SqlOrdEntry('s.weight',SqlOrdEntry::DESC));
				
				$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::DESC));
				 
			break;	
			
		}
		 
		$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
		
		
	
		 
		
		$leads=$_tzs->ShowPos(
				
			'tz/table.html',  //0
			 $decorator, //1
			  /*($au->user_rights->CheckAccess('w',1007)||($au->user_rights->CheckAccess('w',1006)&&(($result['id']==$editing_user['manager_id'])||($result['id']==$editing_user['created_id']))))&&($editing_user['is_confirmed']==1)*/ false, //2
			  $au->user_rights->CheckAccess('w',1009), //3
			  $from, //4
			  $to_page, //5
			  true, //6
			  false,  //7
			  $au->user_rights->CheckAccess('w',1012), //8
			  $au->user_rights->CheckAccess('w',1013),  //9
			  $au->user_rights->CheckAccess('w',1010), //10
			  $au->user_rights->CheckAccess('w',1011), //11
			  $au->user_rights->CheckAccess('w',1014), //12
			  
			  
			$prefix //13
			 );

 	
		
		$sm->assign('tzs', $leads);
		 
		 
		 
		 
/*****************************************************************************************************/
//блок КП вход по Лиду
		$sm->assign('has_kpins', $au->user_rights->CheckAccess('w',1017));
		//$sm->assign('tzs','Работа с ТЗ по лиду в данном режиме невозможна. Пожалуйста, сохраните и утвердите лид для создания по нему ТЗ.');		 
		 
		 //вывести список КП вход
		 $_tzs=new KpIn_Group;
		 
		 
		$_tzs->SetAuthResult($result);
		$_tzs->SetPageName('ed_lead.php');
 
 
		$prefix='_kp_ins';
		
		if(isset($_GET['from'.$prefix])) $from=abs((int)$_GET['from'.$prefix]);
		  else $from=0;
		if(isset($_GET['to_page'.$prefix])) $to_page=abs((int)$_GET['to_page'.$prefix]);
		  else $to_page=ITEMS_PER_PAGE;
		  
		$decorator=new DBDecorator;
		
		
		$decorator->AddEntry(new UriEntry('action',1));
		$decorator->AddEntry(new UriEntry('id',$id));
		$decorator->AddEntry(new UriEntry('lead_id',$id));
		$decorator->AddEntry(new SqlEntry('p.lead_id',$id, SqlEntry::E));
		
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
		$_tzs->SetPageName('ed_lead.php');
 
 
		$prefix='_kp_outs';
		
		if(isset($_GET['from'.$prefix])) $from=abs((int)$_GET['from'.$prefix]);
		  else $from=0;
		if(isset($_GET['to_page'.$prefix])) $to_page=abs((int)$_GET['to_page'.$prefix]);
		  else $to_page=ITEMS_PER_PAGE;
		  
		$decorator=new DBDecorator;
		
		
		$decorator->AddEntry(new UriEntry('action',1));
		$decorator->AddEntry(new UriEntry('id',$id));
		$decorator->AddEntry(new UriEntry('lead_id',$id));
		$decorator->AddEntry(new SqlEntry('p.lead_id',$id, SqlEntry::E));
		
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
		$_plans->SetPageName('ed_lead.php');
		
		if(isset($_GET['from'.$prefix])) $from=abs((int)$_GET['from'.$prefix]);
		  else $from=0;
		if(isset($_GET['to_page'.$prefix])) $to_page=abs((int)$_GET['to_page'.$prefix]);
		  else $to_page=ITEMS_PER_PAGE;
		  
		$decorator=new DBDecorator;
		
		
		 $decorator->AddEntry(new SqlEntry('t.lead_id',$id, SqlEntry::E));
		 
		 
		$decorator->AddEntry(new UriEntry('action',1));
		$decorator->AddEntry(new UriEntry('id',$id));
		$decorator->AddEntry(new UriEntry('lead_id',$id));
		 
		
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

 
		 
		 
		 
/*******************************************************************************************************/
//вкладка действия по лиду


	$prefix='_actions';
	$_actions=new AnLeadActions;
	
		
	 
		
		$decorator=new DBDecorator;
			
			//активен или неактивен
			$decorator->AddEntry(new UriEntry('is_active',  $editing_user['is_active']));
			
			/*
			if($print==0) $print_add='';
			else $print_add='_print';*/
		
			//$decorator->AddEntry(new UriEntry('print',$print));
			$decorator->AddEntry(new UriEntry('prefix',$prefix));
			$decorator->AddEntry(new UriEntry('id',  $id));
			$decorator->AddEntry(new UriEntry('action',  $action));
			 
			
			//фильтры по сотруднику
			 
			if(isset($_GET['user'.$prefix])&&(strlen($_GET['user'.$prefix])>0)){
				$_users=explode(';', $_GET['user'.$prefix]);
				$decorator->AddEntry(new UriEntry('user',  $_GET['user'.$prefix]));
		
				
			}else $_users=NULL;
			
			
			//блок фильтров статуса
			$decorator->AddEntry(new SqlEntry('p.status_id', 3, SqlEntry::NE));
			$decorator->AddEntry(new SqlEntry('p.status_id', 18, SqlEntry::NE));
			
			 
			$status_ids=array();
			$cou_stat=0;   
			if(isset($_GET[$prefix.'statuses'])&&is_array($_GET[$prefix.'statuses'])) $cou_stat=count($_GET[$prefix.'statuses']);
			if($cou_stat>0){
			  //есть гет-запросы	
			  $status_ids=$_GET[$prefix.'statuses'];
			  
			}else{
				
				 $decorator->AddEntry(new UriEntry('all_statuses',1));
			}
			
			if(count($status_ids)>0){
				$of_zero=true; foreach($status_ids as $k=>$v) if($v>0) $of_zero=$of_zero&&false;
				
				if($of_zero){
					//ничего нет - выбираем ВСЕ!	
					$decorator->AddEntry(new UriEntry('all_statuses',1));
				}else{
				
					foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry('status_id_'.$v,1));
					
					foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry($prefix.'statuses[]',$v));
					
					$decorator->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));
				 
				}
			}
			
			
			 //выбрать виды действий
		 	$kinds=array();
			$cou_stat=0;   
			if(isset($_GET[$prefix.'kinds'])&&is_array($_GET[$prefix.'kinds'])) $cou_stat=count($_GET[$prefix.'kinds']);
			if($cou_stat>0){
			  //есть гет-запросы	
			  $kinds=$_GET[$prefix.'kinds'];
			  
			}else{
				
				 $decorator->AddEntry(new UriEntry('all_kinds',1));
			}
			
			if(count($kinds)>0){
				$of_zero=true; foreach($kinds as $k=>$v) if($v>0) $of_zero=$of_zero&&false;
				
				if($of_zero){
					//ничего нет - выбираем ВСЕ!	
					$decorator->AddEntry(new UriEntry('all_kinds',1));
				}else{
				
					foreach($kinds as $k=>$v) $decorator->AddEntry(new UriEntry('kind_id_'.$v,1));
					$decorator->AddEntry(new SqlHavingEntry('`document_type_id`', NULL, SqlHavingEntry::IN_VALUES, NULL,$kinds));	
					foreach($kinds as $k=>$v) $decorator->AddEntry(new UriEntry($prefix.'kinds[]',$v));
					
			
				}
			} 
		  
			
			 //совершенные/несовершенные действия
			$is_fulfil=NULL;
		 	$kinds=array();
			$cou_stat=0;   
			if(isset($_GET[$prefix.'is_fulfil'])&&is_array($_GET[$prefix.'is_fulfil'])) $cou_stat=count($_GET[$prefix.'is_fulfil']);
			if($cou_stat>0){
			  //есть гет-запросы	
			  $kinds=$_GET[$prefix.'is_fulfil'];
			  
			}else{
				
				 $decorator->AddEntry(new UriEntry('all_is_fulfil',1));
			}
			
			if(count($kinds)>0){
				$of_zero=true; foreach($kinds as $k=>$v) if($v>0) $of_zero=$of_zero&&false;
				
				if($of_zero){
					//ничего нет - выбираем ВСЕ!	
					$decorator->AddEntry(new UriEntry('all_is_fulfil',1));
				}else{
				
					foreach($kinds as $k=>$v) {
						$decorator->AddEntry(new UriEntry('is_fulfil_'.$v,1));
					//$decorator->AddEntry(new SqlHavingEntry('`document_type_id`', NULL, SqlHavingEntry::IN_VALUES, NULL,$kinds));	
						$decorator->AddEntry(new UriEntry($prefix.'is_fulfil[]',$v));
					
						if($v==1) $is_fulfil[]=1; 
						elseif($v==2) $is_fulfil[]=2;
					}
				}
			} 
			 
			
			if(!isset($_GET['pdate_1'.$prefix])){
			
					$_pdate1=DateFromdmY(date("d.m.Y"))-60*60*24*30*3;
					$pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
				
			}else $pdate1 = $_GET['pdate_1'.$prefix];
			
			
			
			if(!isset($_GET['pdate_2'.$prefix])){
					
					$_pdate2=DateFromdmY(date("d.m.Y"))+60*60*24*30*3;
					$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
			}else $pdate2 = $_GET['pdate_2'.$prefix];
			
			
			$decorator->AddEntry(new UriEntry('pdate_1',$pdate1));
			$decorator->AddEntry(new UriEntry('pdate_2',$pdate2));
			
			
		if(!isset($_GET['sortmode'.$prefix])){
			$sortmode=-1;	
		}else{
			$sortmode=abs((int)$_GET['sortmode'.$prefix]);
		}
		
		$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
		 
		 
		switch($sortmode){
			case 0:
				$decorator->AddEntry(new SqlOrdEntry('5',SqlOrdEntry::DESC));
			break;
			case 1:
				$decorator->AddEntry(new SqlOrdEntry('5',SqlOrdEntry::ASC));
			break;
			case 2:
				$decorator->AddEntry(new SqlOrdEntry('6',SqlOrdEntry::DESC));
				$decorator->AddEntry(new SqlOrdEntry('8',SqlOrdEntry::DESC));
			break;	
			case 3:
				$decorator->AddEntry(new SqlOrdEntry('6',SqlOrdEntry::ASC));
				$decorator->AddEntry(new SqlOrdEntry('8',SqlOrdEntry::ASC));
			break;
			
			case 4:
				$decorator->AddEntry(new SqlOrdEntry('11',SqlOrdEntry::DESC));
			break;	
			case 5:
				$decorator->AddEntry(new SqlOrdEntry('11',SqlOrdEntry::ASC));
			break;
			case 6:
				$decorator->AddEntry(new SqlOrdEntry('12',SqlOrdEntry::DESC));
			break;	
			case 7:
				$decorator->AddEntry(new SqlOrdEntry('12',SqlOrdEntry::ASC));
			break;
			case 8:
				$decorator->AddEntry(new SqlOrdEntry('26',SqlOrdEntry::DESC));
				
			break;	
			case 9:
				$decorator->AddEntry(new SqlOrdEntry('26',SqlOrdEntry::ASC));
				
			break;
			case 10:
				$decorator->AddEntry(new SqlOrdEntry('13',SqlOrdEntry::DESC));
				
			break;	
			case 11:
				$decorator->AddEntry(new SqlOrdEntry('13',SqlOrdEntry::ASC));
				
			break;
			
			case 12:
				//$decorator->AddEntry(new SqlOrdEntry('39',SqlOrdEntry::DESC));
				
			break;	
			case 13:
				//$decorator->AddEntry(new SqlOrdEntry('39',SqlOrdEntry::ASC));
				
			break;
			
			case 14:
				//$decorator->AddEntry(new SqlOrdEntry('sup.full_name',SqlOrdEntry::DESC));
				//$decorator->AddEntry(new SqlOrdEntry('sup1.full_name',SqlOrdEntry::DESC));
				
			break;	
			case 15:
			//	$decorator->AddEntry(new SqlOrdEntry('sup.full_name',SqlOrdEntry::ASC));
				//$decorator->AddEntry(new SqlOrdEntry('sup1.full_name',SqlOrdEntry::ASC));
			break;
			
			case 16:
				$decorator->AddEntry(new SqlOrdEntry('16',SqlOrdEntry::DESC));
				
			break;	
			case 17:
				$decorator->AddEntry(new SqlOrdEntry('16',SqlOrdEntry::ASC));
				
			break;
			
			case 18:
				$decorator->AddEntry(new SqlOrdEntry('17',SqlOrdEntry::DESC));
				
			break;	
			case 19:
				$decorator->AddEntry(new SqlOrdEntry('17',SqlOrdEntry::ASC));
				
			break;
			
			case 20:
				$decorator->AddEntry(new SqlOrdEntry('27',SqlOrdEntry::DESC));
				
			break;	
			case 21:
				$decorator->AddEntry(new SqlOrdEntry('27',SqlOrdEntry::ASC));
				
			break;
			
			case 22:
				$decorator->AddEntry(new SqlOrdEntry('29',SqlOrdEntry::DESC));
				
			break;	
			case 23:
				$decorator->AddEntry(new SqlOrdEntry('29',SqlOrdEntry::ASC));
				
			break;
			
			case 24:
				$decorator->AddEntry(new SqlOrdEntry('36',SqlOrdEntry::DESC));
				
			break;	
			case 25:
				$decorator->AddEntry(new SqlOrdEntry('36',SqlOrdEntry::ASC));
				
			break;
			
			default:
				$decorator->AddEntry(new SqlOrdEntry('6',SqlOrdEntry::DESC));
				$decorator->AddEntry(new SqlOrdEntry('8',SqlOrdEntry::DESC));
				$decorator->AddEntry(new SqlOrdEntry('1',SqlOrdEntry::ASC));
		
			break;	
			
		}	
			
		 
		//видимость документов текущим сотрудником...
		 $viewed_ids_arr=array();
		 
		/* $_list=new Sched_Group;
		 
		for($i=1; $i<=5; $i++) $viewed_ids_arr[$i]=$_list->GetAvailableUserIds($result['id'],false,$i);
		
		$_dvn=new DocVn_Group;
		$viewed_ids_arr[6]=$_dvn->GetAvailableDocIds($result['id']);
		$_llist=new DocIn_Group;
		$viewed_ids_arr[7]=$_llist->GetAvailableDocIds($result['id']);
		$_llist=new DocOut_Group;
		$viewed_ids_arr[8]=$_llist->GetAvailableDocIds($result['id']);*/
	 
		
		$actions=$_actions->ShowData($editing_user['id'],  NULL, $_users,  $viewed_ids_arr,   $pdate1,   $pdate2,  'lead/lead_actions.html', $decorator, 'ed_lead.php',     true,    $au->user_rights->CheckAccess('w',903),   $au->user_rights->CheckAccess('w',905),  $alls, $result,   $sortmode,  $is_fulfil);
		
		
		 
 
		
		$sm->assign('actions', $actions);
		  
		 
		 
	 
		
		
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
		
			$decorator->AddEntry(new SqlEntry('object_id',NULL, SqlEntry::IN_VALUES, NULL, array(948,
949,
950,
951,
952,
953,
954,
955,
956,
957,
958,
959,
960,
961,
965,
966,
980,
981,
982,
988

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
			
			$llg=$log->ShowLog('syslog/log_doc.html',$decorator,$from,$to_page,'ed_lead.php',true,true,true);
			
			$sm->assign('syslog',$llg);		
				
		} 
		
		
	}
	
	
		
	 
	$sm->assign('from_begin',$from_begin);	
	$sm->assign('show_tz',$show_tz);	
	if($action==1){
		$_lc=new Lead_Item;
		$has_tz=$_lc->HasActiveTZ($id, $rss);
		$sm->assign('has_doc_tz', $has_tz);
		//	
	}
	
	
	$sm->assign('print_pdate', date("d.m.Y H:i:s"));
	//$username=$result['login'];
	$username=stripslashes($result['name_s']).' '.$username;	
	$sm->assign('print_username',$username);
	
	$sm->assign('users',$user_form);
	
	
	$content=$sm->fetch('lead/ed_lead_page'.$print_add.'.html');
	
	 
	
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