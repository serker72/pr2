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
require_once('classes/bdr.class.php');
require_once('classes/kp_in.class.php');



 

require_once('classes/supplier_country_group.php');
require_once('classes/supplier_city_item.php');
 
require_once('classes/lead_history_group.php');
require_once('classes/docstatusitem.php');

 

require_once('classes/pl_positem.php');
require_once('classes/kp_supply_pdate_item.php');

require_once('classes/kpitem.php');

require_once('classes/currency/currency_solver.class.php');

$_pch=new PeriodChecker;
$pch_date=$_pch->GetDate();



$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'БДР/БДДС');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}


$_dem=new BDR_AbstractItem;

$_plan1=new BDR_Group;
$available_users=$_plan1->GetAvailableBDRIds($result['id']);

$_plan=new BDR_Group;


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
	$object_id[]=1039;
	break;
	case 1:
	$object_id[]=1041;
	break;
	case 2:
	$object_id[]=1041;
	break;
	default:
	$object_id[]=1041;
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
	 if(!isset($_GET['kp_out_id'])){
		if(!isset($_POST['kp_out_id'])){
			header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			include('404.php');
			die();
		}else $kp_out_id=abs((int)$_POST['kp_out_id']);	
	}else $kp_out_id=abs((int)$_GET['kp_out_id']);
	
	/*$_lead=new Lead_Item;
	$lead=$_lead->GetItemById($lead_id);
	if($lead===false){
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include('404.php');
		die();
	}
	 */
	 
	$_kp_out=new KpOut_Item;
	$kp_out=$_kp_out->GetItemById($kp_out_id);
	if($kp_out===false){
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
	
	
	if(!isset($_GET['version_id'])){
		if(!isset($_POST['version_id'])){
			$version_id=NULL;
		}else $version_id=abs((int)$_POST['version_id']);	
	}else $version_id=abs((int)$_GET['version_id']);
	
	//проверка наличия пользователя
	$editing_user=$_dem->getitembyid($id, $version_id); 
	
	if($editing_user===false){
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include('404.php');
		die();
	}
	foreach($editing_user as $k=>$v) $editing_user[$k]=stripslashes($v);
	
	
	$_tg=new BDR_Group;
	
	if(!$au->user_rights->CheckAccess('w',1040)){	
		$available_tenders=$_tg->GetAvailableBDRIds($result['id']);
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
	
	
	$_kp_in=new KpIn_Item;
	$kp_in=$_kp_in->GetItemById($editing_user['kp_in_id']);
	
	
	$_tz=new TZ_Item;
	$tz=$_tz->GetItemById($editing_user['tz_id']);
		 
	$_kp_out=new KpOut_Item;
	$kp_out=$_kp_out->GetItemById($editing_user['kp_out_id']); 
 
}

 
  



//обработка данных

if(($action==0)&&(isset($_POST['doNew'])||isset($_POST['doNewEdit']))){
	 
	
	
	
	
	$params=array(); $version_params=array();
	//обычная загрузка прочих параметров
	$params['created_id']=$result['id'];
	$params['pdate']=time();
	
	$params['manager_id']=abs((int)$_POST['manager_id']);
	
	 
	$params['lead_id']=abs((int)$_POST['lead_id']);
	$params['tz_id']=abs((int)$_POST['tz_id']);
	$params['kp_in_id']=abs((int)$_POST['kp_in_id']);
	$params['kp_out_id']=abs((int)$_POST['kp_out_id']);
	$params['project_code']= SecStr($_POST['project_code']);
	
	
	$version_params['description']= SecStr($_POST['description']);
	$version_params['given_pdate']=time();
	$version_params['version_created_id']=$result['id'];
	
	$version_params['course_dol']=abs((float)$_POST['course_dol']);
	$version_params['course_euro']=abs((float)$_POST['course_euro']);
	
	//сохранить дату и режим ввода курса валюты
	$version_params['pdate_course']=DateFromdmY($_POST['pdate_course']);
	
	if(isset($_POST['is_my_course'])) $version_params['is_my_course']=1; else $version_params['is_my_course']=0;
	
	$version_params['currency_id']=abs((float)$_POST['currency_id']);
	
 //	$params['given_pdate']=  DateFromdmY($_POST['given_pdate']) ;
	
	$version_params['beg_month']=abs((int)$_POST['beg_month']);
	$version_params['beg_year']=abs((int)$_POST['beg_year']);
	$version_params['end_month']=abs((int)$_POST['end_month']);
	$version_params['end_year']=abs((int)$_POST['end_year']); 
	 
	
	$params['status_id']=1;
	 		
	 
	$_res=new BDR_Resolver();
		
		
	$code=	$_res->instance->Add($params, $version_params);
	
	
	 
	 
	//$code=1;
	//запись в журнале
	if($code>0){
		$log->PutEntry($result['id'],'создал БДР',NULL,1039,NULL,NULL,$code);	
		
		foreach($params as $k=>$v){
			 
				$log->PutEntry($result['id'],'создал БДР',NULL,1039, NULL, 'в поле '.$k.' установлено значение '.$v,$code);		
			 
		}
		
		foreach($version_params as $k=>$v){
			 
				$log->PutEntry($result['id'],'создал БДР',NULL,1039, NULL, 'в поле '.$k.' установлено значение '.$v,$code);		
			 
		}
		
		//занести позиции
		//словарь прибыли
		
		$positions=array();
		foreach($_POST as $k=>$v) if(eregi("^p_id_",$k)&&($v!=0)){
			$account_id=abs((int)$v);
			$positions[]=array(
				'bdr_id'=>$code,
				'version_id'=>NULL,
				'account_id'=>$account_id,
				'cost'=>abs((float)$_POST['p_cost_'.$account_id]),
				'notes'=>SecStr($_POST['p_notes_'.$account_id])		
			);
		}
		
		$log_entries=$_res->instance->AddPositions($code,NULL,0,$positions,$result);
		
		/*
		echo '<pre>';
		print_r($positions);
		echo '</pre>';
		die();*/
		
		//заносим в журнал сведения о добавке позиций
		$_pos=new BDR_AccountItem;  $_curr_itm=new PlCurrItem; 
		foreach($log_entries as $k=>$v){
			  
			$pos=$_pos->GetItemById($v['account_id']);
			if($pos!==false) {
			  
			  $description=SecStr($pos['name'].'<br />   стоимость с НДС '.$v['cost'].' руб. <br />Примечание: '.$v['notes']);
				
			  if($v['action']==0){
				  $log->PutEntry($result['id'],'добавил позицию выручки по БДР',NULL,1039,NULL,$description,$code);	
			  }elseif($v['action']==1){
				  $log->PutEntry($result['id'],'редактировал позицию выручки по БДР',NULL,1039,NULL,$description,$code);
			  }elseif($v['action']==2){
				  $log->PutEntry($result['id'],'удалил позицию выручки по БДР',NULL,1039,NULL,$description,$code);
			  }
			  
			}
		  }
		  
		
		//словарь затрат
		
		$positions=array();
		foreach($_POST as $k=>$v) if(eregi("^m_id_",$k)&&($v!=0)){
			$account_id=abs((int)$v);
			$positions[]=array(
				'bdr_id'=>$code,
				'version_id'=>NULL,
				'account_id'=>$account_id,
				'cost'=>abs((float)$_POST['m_cost_'.$account_id]),
				'notes'=>SecStr($_POST['m_notes_'.$account_id])		
			);
		}
		
		$log_entries=$_res->instance->AddPositions($code,NULL, 1,$positions,$result);
		
		/*
		echo '<pre>';
		print_r($positions);
		echo '</pre>';
		die();*/
		
		//заносим в журнал сведения о добавке позиций
		$_pos=new BDR_AccountItem;  $_curr_itm=new PlCurrItem; 
		foreach($log_entries as $k=>$v){
			  
			$pos=$_pos->GetItemById($v['account_id']);
			if($pos!==false) {
			  
			  $description=SecStr($pos['name'].'<br />   стоимость  без НДС '.$v['cost'].' руб. <br />Примечание: '.$v['notes']);
				
			  if($v['action']==0){
				  $log->PutEntry($result['id'],'добавил позицию затрат по БДР',NULL,1039,NULL,$description,$code);	
			  }elseif($v['action']==1){
				  $log->PutEntry($result['id'],'редактировал позицию затрат по БДР',NULL,1039,NULL,$description,$code);
			  }elseif($v['action']==2){
				  $log->PutEntry($result['id'],'удалил позицию затрат по БДР',NULL,1039,NULL,$description,$code);
			  }
			  
			}
		  }  
			
	}
	
	  
		  
		  
	if($au->user_rights->CheckAccess('w',1039)&&($_POST['do_confirm']==1)){
	
	
		$_res->instance->Edit($code, NULL, array(), array('is_confirmed'=>1, 'user_confirm_id'=>$result['id'], ' 	confirm_pdate'=>time()),true,$result);
					  
		$log->PutEntry($result['id'],'автоматически утвердил заполнение БДР',NULL,1039, NULL, NULL,$code);	
		
		
	}else{
		$log->PutEntry($result['id'],'отказался от автоматического утверждения заполнения БДР',NULL,1039, NULL, NULL,$code);	
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
		 header("Location: ed_lead.php?action=1&id=".$_POST['lead_id']."&show_bdr=1");
		die();
	}elseif(isset($_POST['doNewEdit'])){
		  
		header("Location: ed_bdr.php?action=1&id=".$code.'&from_begin='.$from_begin);
		die();	
		
	}else{
		header("Location: leads.php");
		die();
	}
	 
	
	die();
	


}elseif(($action==1)&&(isset($_POST['doEdit'])||isset($_POST['doEditStay'])
	 ||isset($_POST['EditAndNewVersion'])
	
	)){
	 
	
	//редактирование возможно, если позволяет статус
	$_res=new BDR_Resolver();
	
	
	//создаем новую версию
	if(isset($_POST['EditAndNewVersion'])){
		 
		if($au->user_rights->CheckAccess('w',1052)){

			$_res->instance->AddVersion($id, $version_no);
			$log->PutEntry($result['id'],'создал новую версию БДР',NULL,1052, NULL, 'создана версия № '.$version_no,$id);
		}
		
	}
	
	//поля формируем в зависимости от их активности в текущем статусе
/* 	$_roles=new TZ_FieldRules($result); //var_dump($_roles->GetTable());
	$field_rights0=$_roles->GetFields($editing_user, $result['id']);	
	$field_rights1=$_roles->GetFields($editing_user, $result['id'], $_POST['current_status_id']);
	$field_rights=array(); 
	foreach($field_rights0 as $k=>$v) $field_rights[$k]=$v&&$field_rights1[$k];
	*/ 
			
	
	$params=array(); $version_params=array();

	
	//поля формируем в зависимости от их активности в текущем статусе
	$condition =in_array($_POST['current_status_id'],$_editable_status_id)&&in_array($editing_user['status_id'],$_editable_status_id);
	
	if($condition){
	 
		$version_params['description']= SecStr($_POST['description']);
		$version_params['currency_id']=abs((float)$_POST['currency_id']);
		
	// 	$params['given_pdate']=   DateFromdmY($_POST['given_pdate']) ;
		
		$version_params['course_dol']=abs((float)$_POST['course_dol']);
		$version_params['course_euro']=abs((float)$_POST['course_euro']);
		
		//сохранить дату и режим ввода курса валюты
		$version_params['pdate_course']=DateFromdmY($_POST['pdate_course']);
		
		if(isset($_POST['is_my_course'])) $version_params['is_my_course']=1; else $version_params['is_my_course']=0;
		
		
		$version_params['beg_month']=abs((int)$_POST['beg_month']);
		$version_params['beg_year']=abs((int)$_POST['beg_year']);
		$version_params['end_month']=abs((int)$_POST['end_month']);
		$version_params['end_year']=abs((int)$_POST['end_year']); 
		 
		
	}
	else{
		//кроме основного условия, еще может сработать дополнительное:	
	    /*if($field_rights['to_select_eq']){
			$params['pl_position_id']=abs((int)$_POST['pl_position_id']);
			
		}
		*/
	 
		
		
		
		
	}
 
	
	
	$_res->instance->Edit($id, NULL, $params,  $version_params);
	
	//print_r($version_params); die();
		
	//$_dem->Edit($id, $params);
	//die();
	//запись в журнале
	//записи в лог. сравнить старые и новые записи
	foreach($params as $k=>$v){
		
		if(addslashes($editing_user[$k])!=$v){
			  
			
			$log->PutEntry($result['id'],'редактировал БДР',NULL,1041, NULL, 'в поле '.$k.' установлено значение '.$v,$id);
			
					
		}	
		
	}
	
  
    if($condition){
		//занести позиции
		//словарь прибыли
		
		$positions=array();
		foreach($_POST as $k=>$v) if(eregi("^p_id_",$k)&&($v!=0)){
			$account_id=abs((int)$v);
			$positions[]=array(
				'bdr_id'=>$id,
				'version_id'=>NULL,
				'account_id'=>$account_id,
				'cost'=>abs((float)$_POST['p_cost_'.$account_id]),
				'notes'=>SecStr($_POST['p_notes_'.$account_id])		
			);
		}
		
		
		
		$log_entries=$_res->instance->AddPositions($id,NULL,0,$positions,$result);
		
		/**/
		//die();
		
		/*echo '<pre>';
		print_r($log_entries);
		echo '</pre>';
		die();*/
		
		//заносим в журнал сведения о добавке позиций
		$_pos=new BDR_AccountItem;  $_curr_itm=new PlCurrItem; 
		foreach($log_entries as $k=>$v){
			  
			$pos=$_pos->GetItemById($v['account_id']);
			if($pos!==false) {
			  
			  $description=SecStr($pos['name'].'<br />   стоимость с НДС '.$v['cost'].' руб. <br />Примечание: '.$v['notes']);
				
			  if($v['action']==0){
				  $log->PutEntry($result['id'],'добавил позицию выручки по БДР',NULL,1041,NULL,$description,$id);	
			  }elseif($v['action']==1){
				  $log->PutEntry($result['id'],'редактировал позицию выручки по БДР',NULL,1041,NULL,$description,$id);
			  }elseif($v['action']==2){
				  $log->PutEntry($result['id'],'удалил позицию выручки по БДР',NULL,1041,NULL,$description,$id);
			  }
			  
			}
		  }
			
		
		
		//словарь затрат
		
		$positions=array();
		foreach($_POST as $k=>$v) if(eregi("^m_id_",$k)&&($v!=0)){
			$account_id=abs((int)$v);
			$positions[]=array(
				'bdr_id'=>$id,
				'version_id'=>NULL,
				'account_id'=>$account_id,
				'cost'=>abs((float)$_POST['m_cost_'.$account_id]),
				'notes'=>SecStr($_POST['m_notes_'.$account_id])		
			);
		}
		
		
		
		$log_entries=$_res->instance->AddPositions($id,NULL,1,$positions,$result);
		
		/**/
		//die();
		
		/*echo '<pre>';
		print_r($log_entries);
		echo '</pre>';
		die();*/
		
		//заносим в журнал сведения о добавке позиций
		$_pos=new BDR_AccountItem;  $_curr_itm=new PlCurrItem; 
		foreach($log_entries as $k=>$v){
			  
			$pos=$_pos->GetItemById($v['account_id']);
			if($pos!==false) {
			  
			  $description=SecStr($pos['name'].'<br />   стоимость без НДС '.$v['cost'].' руб. <br />Примечание: '.$v['notes']);
				
			  if($v['action']==0){
				  $log->PutEntry($result['id'],'добавил позицию затрат по БДР',NULL,1041,NULL,$description,$id);	
			  }elseif($v['action']==1){
				  $log->PutEntry($result['id'],'редактировал затрат выручки по БДР',NULL,1041,NULL,$description,$id);
			  }elseif($v['action']==2){
				  $log->PutEntry($result['id'],'удалил позицию затрат по БДР',NULL,1041,NULL,$description,$id);
			  }
			  
			}
		  }	
	}
	
	 
	
	$_dsi=new DocStatusItem; 
	//обработка выделенных кнопок
 
	
	 
	 
	 
		//утверждение заполнения
		
		$_res=new BDR_Resolver();
		
		include('inc/ed_bdr_confirm_include.php');
		
		 
	
	
	//die();
	
	//перенаправления
	if(isset($_POST['doEdit'])){
		 
		header("Location: ed_lead.php?action=1&id=".$editing_user['lead_id']."&show_bdr=1");
		die();
	}elseif(isset($_POST['doWork'])||isset($_POST['doEditStay'])
		||isset($_POST['EditAndNewVersion'])
		||isset($_POST['to_work'])
		||isset($_POST['to_rework'])
		||isset($_POST['to_cancel'])
		||isset($_POST['to_view'])
		||isset($_POST['to_defer'])
		
		||isset($_POST['to_win'])
		||isset($_POST['to_fail'])
		||isset($_POST['to_restore_work'])
	
	){
	 
		header("Location: ed_bdr.php?action=1&id=".$id.'&from_begin='.$from_begin);
		die();	
		
	}else{
		header("Location: leads.php");
		die();
	}
	
	die();
 

}elseif(($action==1)&&(isset($_POST['doEditBDDS'])||isset($_POST['doEditStayBDDS'])
	 
	
	)){
/****************************************************************************************************/
//правка блока БДДС	 
	
	//редактирование возможно, если позволяет статус
	$_res=new BDR_Resolver();
	
	
 	
	
	$params=array(); $version_params=array();

	
	//поля формируем в зависимости от их активности в текущем статусе
	$condition =in_array($_POST['current_status_id'],$_editable_status_id)&&in_array($editing_user['status_id'],$_editable_status_id);
	
	 
	
	//$_res->instance->Edit($id, NULL, $params,  $version_params);
	
	//print_r($version_params); die();
		
	//$_dem->Edit($id, $params);
	//die();
	//запись в журнале
	//записи в лог. сравнить старые и новые записи
	 
	
  
    if($condition){
		//занести позиции
		//словарь прибыли
		
		 
			//bdds_value_%{$recgr.account_group}%_%{$account.account_id}%_%{$account.monthes[$smarty.foreach.fm.index]}%_%{$account.years[$smarty.foreach.fm.index]}%
		$positions=array();
		foreach($_POST as $k=>$v) if(eregi("^bdds_value_0_",$k)){
			
			if($v=="") $cost=NULL;
			else $cost=abs((float)$v);
			
			$ds_account_id=eregi_replace("_([0-9]+)_([0-9]+)$", "",  eregi_replace("^bdds_value_0_","", $k ));
			$year=eregi_replace("^bdds_value_0_".$ds_account_id."_([0-9]+)_","", $k );
			$month=eregi_replace("_([0-9]+)$", "",  eregi_replace("^bdds_value_0_".$ds_account_id.'_',"", $k ));
			
			$positions[]=array(
				'bdr_id'=>$id,
				'version_id'=>NULL,
				'ds_account_id'=>$ds_account_id,
				'year'=>$year,
				'month'=>$month,
				'cost'=>$cost
			);
		}
		
		
		/*echo '<pre>';
		print_r($positions);
		echo '</pre>';
		*/
		
		$log_entries=$_res->instance->AddBDDSPositions($id, NULL,0,$positions,$result);
		
		/**/
		//die();
		
		/*echo '<pre>';
		print_r($log_entries);
		echo '</pre>';
		die();*/
		
		//заносим в журнал сведения о добавке позиций
		$_pos=new BDDS_AccountItem;
		foreach($log_entries as $k=>$v){
			  
			$pos=$_pos->GetItemById($v['ds_account_id']);
			if($pos!==false) {
			  
			  $description=SecStr($pos['name'].'<br />   стоимость '.$v['cost'].' руб. ');
				
			  if($v['action']==0){
				  $log->PutEntry($result['id'],'добавил позицию поступления ДС по БДДС',NULL,1041,NULL,$description,$id);	
			  }elseif($v['action']==1){
				  $log->PutEntry($result['id'],'редактировал позицию поступления ДС по БДДС',NULL,1041,NULL,$description,$id);
			  }elseif($v['action']==2){
				  $log->PutEntry($result['id'],'удалил позицию поступления ДС по БДДС',NULL,1041,NULL,$description,$id);
			  }
			  
			}
		  }
			
		
		
		//словарь затрат
		
		$positions=array();
		foreach($_POST as $k=>$v) if(eregi("^bdds_value_1_",$k)){
			
			if($v=="") $cost=NULL;
			else $cost=abs((float)$v);
			
			$ds_account_id=eregi_replace("_([0-9]+)_([0-9]+)$", "",  eregi_replace("^bdds_value_1_","", $k ));
			$year=eregi_replace("^bdds_value_1_".$ds_account_id."_([0-9]+)_","", $k );
			$month=eregi_replace("_([0-9]+)$", "",  eregi_replace("^bdds_value_1_".$ds_account_id.'_',"", $k ));
			
			$positions[]=array(
				'bdr_id'=>$id,
				'version_id'=>NULL,
				'ds_account_id'=>$ds_account_id,
				'year'=>$year,
				'month'=>$month,
				'cost'=>$cost
			);
		}
		
		
		/*echo '<pre>';
		print_r($positions);
		echo '</pre>';
		*/
		
		$log_entries=$_res->instance->AddBDDSPositions($id, NULL,1,$positions,$result);
		
		/**/
		//die();
		
		/*echo '<pre>';
		print_r($log_entries);
		echo '</pre>';
		die();*/
		
		//заносим в журнал сведения о добавке позиций
		$_pos=new BDDS_AccountItem;
		foreach($log_entries as $k=>$v){
			  
			$pos=$_pos->GetItemById($v['ds_account_id']);
			if($pos!==false) {
			  
			  $description=SecStr($pos['name'].'<br />   стоимость '.$v['cost'].' руб. ');
				
			  if($v['action']==0){
				  $log->PutEntry($result['id'],'добавил позицию оплат по БДДС',NULL,1041,NULL,$description,$id);	
			  }elseif($v['action']==1){
				  $log->PutEntry($result['id'],'редактировал позицию оплат по БДДС',NULL,1041,NULL,$description,$id);
			  }elseif($v['action']==2){
				  $log->PutEntry($result['id'],'удалил позицию оплат по БДДС',NULL,1041,NULL,$description,$id);
			  }
			  
			}
		  }
	}
	
	 
	
	$_dsi=new DocStatusItem; 
	//обработка выделенных кнопок
 
		$_res=new BDR_Resolver();
		
		include('inc/ed_bdr_confirm_include.php');
		
		 
	
	//die();
	
	//перенаправления
	if(isset($_POST['doEditBDDS'])){
		 
		header("Location: ed_lead.php?action=1&id=".$editing_user['lead_id']."&show_bdr=1");
		die();
	}elseif(isset($_POST['doEditStayBDDS'])
	
	){
	 
		header("Location: ed_bdr.php?action=1&id=".$id);
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
	 
	$log->PutEntry($result['id'],'открыл БДР',NULL,1041, NULL, 'БДР № '.$editing_user['code'],$id);
	 
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
		 
		 
	 	
		//по КП Исх. получить: кпв, лид, тз
		
		$_lead=new Lead_Item;
	    $lead=$_lead->GetItemById($kp_out['lead_id']);
	    $sm1->assign('lead', $lead);
		
		$_kp_in=new KpIn_Item;
	    $kp_in=$_kp_in->GetItemById($kp_out['kp_in_id']);
	    $sm1->assign('kp_in', $kp_in);
		
		
		$_tz=new TZ_Item;
	    $tz=$_tz->GetItemById($kp_out['tz_id']);
	    $sm1->assign('tz', $tz);
		 
	    $sm1->assign('kp_out', $kp_out);
		
		
		 //подставить контрагента, ответственных из лида
		 
		 
		 //контрагенты
		$_suppliers1=new Lead_SupplierGroup;
		$sup1=$_suppliers1->GetItemsByIdArr($lead['id']);
		$sup2=array();
		if(count($sup1)>0) $sup2[]=$sup1[0];
		$sm1->assign('suppliers', $sup2);
		
		//отвеств сотр-к
		$_user_s=new UserSItem;
		$user_s=$_user_s->GetItemById($lead['manager_id']);
		 
		$sm1->assign('manager_id', $lead['manager_id']);
		$sm1->assign('manager_string', $user_s['name_s']);
		
		
		//станок, срок поставки, валюта - из ВХ КП
		$_supply=new KpSupplyPdateItem;
		$_pl=new Tender_EqTypeItem; $_curr=new PlCurrItem; 
		$supply=$_supply->GetItemById($kp_in['supply_pdate_id']);
		$pl=$_pl->GetItemById($kp_in['eq_type_id']);
		//$curr=$_curr->GetItemById($kp_in['currency_id']);
		$sm1->assign('eq_str', $pl['name']); 
		$sm1->assign('srok_str', $supply['name']); 
		//$sm1->assign('curr_str', $curr['signature']); 
		
		$_curr=new PlCurrGroup;
		$sm1->assign('currs', $_curr->GetItemsArr($kp_in['currency_id']));
			
		
		
		
		//курсы валют - подтянуть текущие
		$_curs=new CurrencySolver;
		$rates=$_curs->GetActual();
		//var_dump($rates);
		//2 - euro 3 - dollar
		 
		$sm1->assign('course_dol', round(CurrencySolver::Convert(1, $rates, 3, 1),5)); 
		$sm1->assign('course_euro', round(CurrencySolver::Convert(1, $rates, 2, 1),5)); 
		
			 
		
		 $sm1->assign('lead_id', $lead_id); 
	 
		

		$sm1->assign('now_time',  date('d.m.Y H:i:s')); 
		$sm1->assign('now_date',  date('d.m.Y')); 
		 
	 	
		//подгрузим данные по прибыли по умолчанию
		$_pb=new BDR_P_Block;
		$pdata=$_pb->ConstructById(0,0);
		$sm1->assign('pdata',  $pdata);
		
		//подгрузим данные по расходам по умолчанию
		$_pm=new BDR_M_Block;
		$mdata=$_pm->ConstructById($kp_in['id'],0,0);
		$sm1->assign('mdata',  $mdata);
		
		$sm1->assign('can_add_accounts',$au->user_rights->CheckAccess('w',1053));
		
		/*echo '<pre>';
		var_dump($pdata);
		echo '</pre>';
		*/
		
		/*
		$_temp=new BDR_AccountVersionGroup;
		echo '<pre>';
		var_dump($_temp->GetItemsByIdArr(0,0,0));
		echo '</pre>';
		*/
	 	
		
		//годы, месяцы
		$months=array(0,1,2,3,4,5,6,7,8,9,10,11,12);
		$month_names=array('-выберите-','январь','февраль','март','апрель','май','июнь','июль','август','сентябрь','октябрь','ноябрь','декабрь');
		$years=array(0); $year_names=array('-выберите-');
		//с 2012 по текущий + 15 лет
		for($i=2012; $i<=((int)date('Y')+15); $i++) { $years[]=$i; $year_names[]=$i;  }
		
		$sm1->assign('months', $months);
		$sm1->assign('month_names', $month_names);
		$sm1->assign('years', $years);
		$sm1->assign('year_names', $year_names);
		$sm1->assign('beg_month', 0); $sm1->assign('beg_year', 0); $sm1->assign('end_month', 0); $sm1->assign('end_year', 0);
		
		
		//получим список тех, кто может снять утверждение заполнения
		$_usg1=new UsersSGroup;
		$usg1=$_usg1->GetUsersByRightArr('w', 1043);
		$sm1->assign('can_unconfirm_users',$usg1);
		$sm1->assign('can_unconfirm',$au->user_rights->CheckAccess('w',1043));
		
		
		 
		$sm1->assign('session_id', session_id());
		
	  
		
		$sm1->assign('can_calc_gain',  $au->user_rights->CheckAccess('w',1042));
		$sm1->assign('can_confirm', $au->user_rights->CheckAccess('w',1042));
		
		
		$user_form=$sm1->fetch('bdr/create_bdr.html');
		 
		
	 
		
		
	 }elseif($action==1){
		//редактирование позиции
		
		if($print==0) $print_add='';
		else $print_add='_print';
		
		
		//построим доступы
		/*$_roles=new TZ_FieldRules($result); //var_dump($_roles->GetTable());
		$field_rights=$_roles->GetFields($editing_user, $result['id']);
		$sm1->assign('field_rights', $field_rights);
		
		//var_dump($field_rights);
		
		
		
		
	 */
	 
	 	$_wg=new BDR_WorkingGroup;
		 
		
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
	 
		//echo $zz;
		
		
		
		//echo $editing_user['vid'];	 
			 
		$sm1->assign('lead', $lead);
		$sm1->assign('tz', $tz);
		$sm1->assign('kp_in', $kp_in);
		$sm1->assign('kp_out', $kp_out);
			 
		 
		
		
		
		$_res=new BDR_Resolver();
		
		
		
		
		//станок, срок поставки, валюта - из ВХ КП
		$_supply=new KpSupplyPdateItem;
		$_pl=new Tender_EqTypeItem; $_curr=new PlCurrItem; 
		$supply=$_supply->GetItemById($kp_in['supply_pdate_id']);
		$pl=$_pl->GetItemById($kp_in['eq_type_id']);
		//$curr=$_curr->GetItemById($kp_in['currency_id']);
		$sm1->assign('eq_str', $pl['name']); 
		$sm1->assign('srok_str', $supply['name']); 
		//$sm1->assign('curr_str', $curr['signature']); 
		
		
		$_curr=new PlCurrGroup;
		$sm1->assign('currs', $_curr->GetItemsArr($editing_user['currency_id']));
		
		
		 //контрагенты
		$_suppliers1=new Lead_SupplierGroup;
		$sup1=$_suppliers1->GetItemsByIdArr($lead['id']);
		$sup2=array();
		if(count($sup1)>0) $sup2[]=$sup1[0];
		$sm1->assign('suppliers', $sup2);
		
		
		
		//отвеств сотр-к
		$_user_s=new UserSItem;
		$user_s=$_user_s->GetItemById($editing_user['manager_id']);
		 
		$sm1->assign('manager_id', $editing_user['manager_id']);
		$sm1->assign('manager_string', $user_s['name_s']);
		
		//подгрузим данные по прибыли 
		$_pb=new BDR_P_Block;
		$pdata=$_pb->ConstructById($id,$editing_user['vid']);
		$sm1->assign('pdata',  $pdata);
		
		
		 //подгрузим данные по затратм 
		$_mb=new BDR_M_Block;
		$mdata=$_mb->ConstructById($editing_user['kp_in_id'], $id,$editing_user['vid']);
		$sm1->assign('mdata',  $mdata);
		
		//var_dump($mdata);
		
		//годы, месяцы
		$months=array(0,1,2,3,4,5,6,7,8,9,10,11,12);
		$month_names=array('-выберите-','январь','февраль','март','апрель','май','июнь','июль','август','сентябрь','октябрь','ноябрь','декабрь');
		$years=array(0); $year_names=array('-выберите-');
		//с 2012 по текущий + 15 лет
		for($i=2012; $i<=((int)date('Y')+15); $i++) { $years[]=$i; $year_names[]=$i;  }
		
		$sm1->assign('months', $months);
		$sm1->assign('month_names', $month_names);
		$sm1->assign('years', $years);
		$sm1->assign('year_names', $year_names);
		
		
		//построим доступы
		$can_modify=in_array($editing_user['status_id'],$_editable_status_id)
			&&($editing_user['vid']==$_res->instance->GetActiveVersionId($id))
		;
		
		//список версий
		$versions=$_res->instance->GetVersions($editing_user['id']);
		$sm1->assign('versions', $versions);
		$sm1->assign('can_make_version', $au->user_rights->CheckAccess('w',1052)
			&&($editing_user['vid']==$_res->instance->GetActiveVersionId($id))
		);
		
		
		//var_dump($versions); 
		
		$editing_user['pdate_d']=date('d.m.Y', $editing_user['pdate']);
		$editing_user['pdate']=date('d.m.Y H:i:s', $editing_user['pdate']);
		$editing_user['pdate_course']=date('d.m.Y', $editing_user['pdate_course']);
		
		
		
		
		if($editing_user['given_pdate']!='')  $editing_user['given_pdate']=date('d.m.Y', $editing_user['given_pdate']);
		
		
		   
		
	  
		
		//блок аннулирования
		
		$editing_user['can_annul']=$_res->instance->DocCanAnnul($editing_user['id'],$reason,$editing_user, $result)&&$au->user_rights->CheckAccess('w',1012);
		if(!$au->user_rights->CheckAccess('w',1012)) $reason='недостаточно прав для данной операции';
		$editing_user['can_annul_reason']=$reason;
		
		 
		
		$editing_user['can_restore']=$_res->instance->DocCanRestore($editing_user['id'],$reason,$editing_user)&&$au->user_rights->CheckAccess('w',1013);
			if(!$au->user_rights->CheckAccess('w',1013)) $reason='недостаточно прав для данной операции';
		
		
		
			//получим список тех, кто может снять утверждение заполнения
		$_usg1=new UsersSGroup;
		$usg1=$_usg1->GetUsersByRightArr('w', 1043);
		$sm1->assign('can_unconfirm_users',$usg1);
		$sm1->assign('can_unconfirm',$au->user_rights->CheckAccess('w',1043));
		
		 
		 
		
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
		if($editing_user['is_confirmed_version']==0){
			
			  
		  
		  if($editing_user['is_confirmed']==1){
			  if($au->user_rights->CheckAccess('w',1043)){
				  //есть права + сам утвердил
				  $can_confirm_price=true;	
			  }else{
				  $can_confirm_price=false;
			  }
		  }else{
			  //95
			  $can_confirm_price=$au->user_rights->CheckAccess('w',1041)&&in_array($editing_user['status_id'],$_editable_status_id)  ;
		  }
		}
		$can_confirm_price=$can_confirm_price&&($editing_user['vid']==$_res->instance->GetActiveVersionId($id));
		$sm1->assign('can_confirm',$can_confirm_price);
		
		  
		$reason='';
		
		
		$sm1->assign('can_unconfirm_by_document',(int)$_res->instance->DocCanUnconfirmPrice($editing_user['id'],$reason));
		$sm1->assign('can_unconfirm_by_document_reason',$reason);
		
		
		
		//блок утв. фин службой
		if(($editing_user['is_confirmed_version']==1)&&($editing_user['user_confirm_version_id']!=0)){
			$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['user_confirm_version_id']);
			$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.date("d.m.Y H:i:s",$editing_user['confirm_version_pdate']);
			
			$sm1->assign('is_confirmed_version_confirmer',$confirmer);
		}
		
		$can_confirm_shipping=false;
		if($editing_user['is_confirmed']==1){
		
		   
		  if($editing_user['is_confirmed_version']==1){
				$can_confirm_shipping=($au->user_rights->CheckAccess('w',1051)/*||($result['main_department_id']==5)*/ );
		  }else{
		  //ставим утв	
			  $can_confirm_shipping=($au->user_rights->CheckAccess('w',1050)/*||($result['main_department_id']==5)*/ );
		  }
		}
		// + есть галочка утв. цен
		$can_confirm_shipping=$can_confirm_shipping&&($editing_user['is_confirmed']==1)&&($editing_user['vid']==$_res->instance->GetActiveVersionId($id));
		
		$sm1->assign('can_modify_v',$can_confirm_shipping);
		
		
		//блок утв. ген дир.
		if(($editing_user['is_confirmed_version1']==1)&&($editing_user['user_confirm_version_id1']!=0)){
			$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['user_confirm_version_id1']);
			$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.date("d.m.Y H:i:s",$editing_user['confirm_version_pdate1']);
			
			$sm1->assign('is_confirmed_version1_confirmer',$confirmer);
		}
		
		$can_confirm_shipping=false;
		if($editing_user['is_confirmed']==1){
		
		   
		  if($editing_user['is_confirmed_version1']==1){
				$can_confirm_shipping=($au->user_rights->CheckAccess('w',1056)||($result['position_id']==8) );
		  }else{
		  //ставим утв	
			  $can_confirm_shipping=($au->user_rights->CheckAccess('w',1055)||($result['position_id']==8) );
		  }
		}
		// + есть галочка утв. цен
		$can_confirm_shipping=$can_confirm_shipping&&($editing_user['is_confirmed']==1)&&($editing_user['vid']==$_res->instance->GetActiveVersionId($id));
		
		$sm1->assign('can_modify_v1',$can_confirm_shipping);
		
		 
		
		
		
	 
		//Примечания
		$rg=new BDRNotesGroup;
		$sm1->assign('notes',$rg->GetItemsByIdArr($editing_user['id'], 0,0, $editing_user['is_confirmed']==1, $au->user_rights->CheckAccess('w',1048), $au->user_rights->CheckAccess('w',1049), $result['id']));
		$sm1->assign('can_notes',true);
		$sm1->assign('can_notes_edit',$au->user_rights->CheckAccess('w',1047)/*&&($editing_user['is_confirmed_price']==0)*/);
		
		
		$sm1->assign('can_modify', $can_modify);  
		$sm1->assign('can_calc_gain',  $au->user_rights->CheckAccess('w',1042) && 
									 ($editing_user['is_confirmed']==0)&&
									 in_array($editing_user['status_id'], $_editable_status_id)
									  
									 );	
		
		//var_dump($lead);
		 
		 
		/*$gain=$_res->instance->CalcGain($id, $editing_user['vid']);
		print_r($gain);  */
		
		
		$_dsi=new docstatusitem; $dsi=$_dsi->GetItemById($editing_user['status_id']);
		$editing_user['status_name']=$dsi['name'];
		$sm1->assign('bill', $editing_user);
		
		
		
		if(isset($_GET['force_print'])) $sm1->assign('force_print', 1);
		
	 
		
		$sm1->assign('files', $filetext);
		$user_form=$sm1->fetch('bdr/edit_bdr'.$print_add.'.html');
		  
  		
/*******************************************************************************************************/		
		//построим форму БДДС
		//$sm1=new SmartyAdm;
		$_bdds=new BDDS_Block($editing_user['id'], $editing_user['vid']);
		$bddsdata=$_bdds->ConstructById($balance, $check);
		$sm1->assign('bddsdata',$bddsdata );
		$sm1->assign('check', $check);
		
		 $sm1->assign('check_formatted', number_format($check,2, '.',' '));
		 
		 
		//echo '<pre>';
		 //var_dump($_bdds->ConstructById($balance, $check));
		//var_dump($balance); var_dump($check);
		//echo '</pre>'; 
		
		/*$_dds_gr=new BDDS_AccountValuesGroup;
		$_dds_gr->GetItemsByIdArr($editing_user['id'], $editing_user['vid'],0);*/
		 
		 $bdds_form=$sm1->fetch('bdr/edit_bdds'.$print_add.'.html');
		
		
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
		
			$decorator->AddEntry(new SqlEntry('object_id',NULL, SqlEntry::IN_VALUES, NULL, array(1038,
1039,
1040,
1041,
1042,
1043,
1044,
1045,
1046,
1047,
1048,
1049,
1050,
1051,
1052

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
			//$sm->assign('has_ship', ($editing_user['is_confirmed_shipping']==1));
			
			$llg=$log->ShowLog('syslog/log_doc.html',$decorator,$from,$to_page,'ed_tz.php',true,true,true);
			
			$sm->assign('syslog',$llg);		
				
		} 
		
		
	}
	
	 
	$sm->assign('from_begin',$from_begin);	 
	
	
	$sm->assign('print_pdate', date("d.m.Y H:i:s"));
	//$username=$result['login'];
	$username=stripslashes($result['name_s']).' '.$username;	
	$sm->assign('print_username',$username);
	
	$sm->assign('users',$user_form);
	$sm->assign('bdds',$bdds_form);
	
	$content=$sm->fetch('bdr/ed_bdr_page'.$print_add.'.html');
	
	
	
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