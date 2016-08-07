<?
session_start();
Header("Cache-Control: no-store, no-cache, must-revalidate"); //дл€ протокола HTTP/1.1
Header("Pragma: no-cache"); // дл€ протокола HTTP/1.1
Header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT"); // дата и врем€ генерации страницы
header("Expires: " . date("r")); // дата и врем€ врем€, когда страница будет считатьс€ устаревшей



require_once('classes/global.php');
require_once('classes/authuser.php');
require_once('classes/smarty/SmartyAdm.class.php');
require_once('classes/smarty/Smarty.class.php');
require_once('classes/discr_table_objects.php');
require_once('classes/actionlog.php');

require_once('classes/petitiongroup.php');
require_once('classes/petitionitem.php');
require_once('classes/petitionkinditem.php');


require_once('classes/petitionmygroup.php');
require_once('classes/petitionallgroup.php');
require_once('classes/petitionkindgroup.php');

require_once('classes/petitionstatusitem.php');
 

require_once('classes/petitionuseritem.php');
//require_once('classes/tasksupplieritem.php');
require_once('classes/useritem.php');
require_once('classes/supplieritem.php');
require_once('classes/petitionfileitem.php');
require_once('classes/petitionuserpresetgroup.php');

require_once('classes/petitioncreator.php');

require_once('classes/petition_purpose_group.php');
require_once('classes/petition_purpose_item.php');

require_once('classes/petition_vyhreason_group.php');

require_once('classes/petition_client_group.php');
require_once('classes/petition_client_item.php');


require_once('classes/supplier_country_group.php');
require_once('classes/supplier_city_item.php');
require_once('classes/supplier_region_item.php');
require_once('classes/supplier_district_item.php');

require_once('classes/user_s_group.php');

require_once('classes/doc_in.class.php');
require_once('classes/sched.class.php');

$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'—оздание за€влени€');


$au=new AuthUser();
$result=$au->Auth();

$log=new ActionLog;

if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

if(!$au->user_rights->CheckAccess('w',724)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}


$log=new ActionLog();

if(!isset($_GET['action'])){
	if(!isset($_POST['action'])){
		$action=0;
	}else $action=abs((int)$_POST['action']);
}else $action=abs((int)$_GET['action']);


if(!isset($_GET['kind_id'])){
	if(!isset($_POST['kind_id'])){
//		$kind_id=0;
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();			

	}else $kind_id=abs((int)$_POST['kind_id']);
}else $kind_id=abs((int)$_GET['kind_id']);



$lc=new petitionCreator;

$mi=new petitionItem;
 
if(isset($_POST['makeOrder'])||isset($_POST['makeSaveOrder'])){
	
	
	//отправка за€влени€
	$order_params=array();
	$order_params['txt']=SecStr($_POST['txt']);
	$order_params['pdate']=time();
	 
	
	$lc->ses->ClearOldSessions();
		$order_params['code']=SecStr($lc->GenLogin($result['id'])); //SecStr($_POST['code']);
	
	//$order_params['code']=SecStr($_POST['code']);
	
	$order_params['kind_id']=abs((int)$_POST['kind_id']);
	$order_params['city_id']=abs((int)$_POST['city_id']);
	$order_params['manager_id']=abs((int)$_POST['manager_id']);
		$order_params['user_id']=$result['id'];
	
	
	$order_params['vyh_reason_id']=abs((int)$_POST['vyh_reason_id']);
	$order_params['vyh_reason']=SecStr($_POST['vyh_reason']);
	
	$order_params['exh_name']=SecStr($_POST['exh_name']);
	
  	$order_params['pdate']=DatefromdmY($_POST['pdate']);
	if(isset($_POST['sched_id'])) $order_params['sched_id']=abs((int)$_POST['sched_id']);
	 
	if(isset($_POST['given_pdate'])) $order_params['given_pdate']=DatefromdmY($_POST['given_pdate']);
	
	if(isset($_POST['time_h'])) $order_params['time_h']=abs((int)$_POST['time_h']);
	if(isset($_POST['time_m'])) $order_params['time_m']=abs((int)$_POST['time_m']);
	
	
	if(isset($_POST['time_from_h'])) $order_params['time_from_h']=abs((int)$_POST['time_from_h']);
	if(isset($_POST['time_from_m'])) $order_params['time_from_m']=abs((int)$_POST['time_from_m']);
	
	if(isset($_POST['time_to_h'])) $order_params['time_to_h']=abs((int)$_POST['time_to_h']);
	if(isset($_POST['time_to_m'])) $order_params['time_to_m']=abs((int)$_POST['time_to_m']);
	
	
	if(isset($_POST['begin_pdate'])) $order_params['begin_pdate']=DatefromdmY($_POST['begin_pdate']);
	if(isset($_POST['end_pdate'])) $order_params['end_pdate']=DatefromdmY($_POST['end_pdate']);
	
	if(isset($_POST['by_graf_or_not'])) $order_params['by_graf_or_not']=abs((int)$_POST['by_graf_or_not']);
	
	$order_params['instead_id']=abs((int)$_POST['instead_id']);
	if(isset($_POST['wo_instead'])) $order_params['wo_instead']=1; else $order_params['wo_instead']=0;
	if(isset($_POST['wo_sched'])) $order_params['wo_sched']=1; else $order_params['wo_sched']=0;
  	
  
	$order_params['status_id']=18; //$order_params['pdate'];
	
	
	
	
	$_claim_kind_item=new petitionKindItem;
	$ckind=$_claim_kind_item->GetItemById($order_params['kind_id']);
	
	$claim_id=$mi->Add($order_params);
	
	
	$log->PutEntry($result['id'],'создал за€вление',NULL,724,NULL,'Ќомер за€влени€: '.$order_params['code'].' ќписание за€влени€:'.$order_params['txt'].' ¬ид за€влени€: '.SecStr($ckind['name']),$claim_id);
	
	
	/*if(isset($_POST['is_confirmed'])){
	 
		$mi->Edit($claim_id, array('is_confirmed'=>1, 'confirm_pdate'=>time(), 'user_confirm_id'=>$result['id']), true, $result);
		$log->PutEntry($result['id'],'утвердил заполнение за€влени€',NULL,724, NULL, NULL,$id);	
				   
	}*/
	
	
	if($au->user_rights->CheckAccess('w',829)){
		if(isset($_POST['is_confirmed'])){
			 
			$mi->Edit($claim_id, array('is_confirmed'=>1, 'confirm_pdate'=>time(), 'user_confirm_id'=>$result['id']), true, $result);
			$log->PutEntry($result['id'],'утвердил заполнение за€влени€',NULL,724, NULL, NULL,$claim_id);	
				
		}else{
			$log->PutEntry($result['id'],'отказалс€ утвердить заполнение за€влени€',NULL,724, NULL, NULL,$claim_id);	
		}
	}
	
	
	
	//ответственные
	/*$_tui=new PetitionUserItem;
	$_ui=new UserItem;
	$ord=0;
	foreach($_POST as $k=>$v){
	  if(eregi("^user_id",$k)){
		  $user_id=abs((int)$v);
		  $_tui->Add(array('user_id'=>$user_id, 'petition_id'=>$claim_id,'ord'=>$ord));
		  
		  $user=$_ui->GetItemById($user_id);
		  $log->PutEntry($result['id'], 'назначил получател€ за€влени€', $user_id, 724, NULL,'Ќомер за€влени€: '.$order_params['code'].' —отрудник: '.SecStr($user['name_s']).' '.SecStr($user['login']),$claim_id);
		  
		  $ord++;
	  }
	}
	*/
	 
	
	//добавим даты работы в выходные... нужен метод правки/добавлени€ позиций!
	$positions=array();	
	foreach($_POST as $k=>$v){
	  if(eregi("^new_vyh_date_pdate_",$k)){
		  
		  $hash=eregi_replace("^new_vyh_date_pdate_","",$k);
		   $positions[]=array(
				  'petition_id'=>$claim_id,
				  
				  
				  'pdate'=>Datefromdmy($_POST['new_vyh_date_pdate_'.$hash]),
				  'time_from_h'=>abs((int)$_POST['new_vyh_date_time_from_h_'.$hash]),
				  'time_from_m'=>abs((int)$_POST['new_vyh_date_time_from_m_'.$hash]),
				  'time_to_h'=>abs((int)$_POST['new_vyh_date_time_to_h_'.$hash]),
				  'time_to_m'=>abs((int)$_POST['new_vyh_date_time_to_m_'.$hash])
				   
			  );
	  }
	}
	
	//внесем даты
	$log_entries=$mi->AddVyhDates($code,$positions);
	//die();
	//запишем в журнал
	foreach($log_entries as $k=>$v){
		 
			$descr=SecStr('ƒата '.date('d.m.Y',$v['pdate']).' с '.$v['time_from_h'].':'.$v['time_from_m'].' по '.$v['time_to_h'].':'.$v['time_to_m'].'  ');
			 
			
			$log->PutEntry($result['id'],'добавил врем€ работы в выходные', NULL, 724,NULL,$descr,$claim_id);	
	 
	}
	
	
	//добавление дат отпуска за работу в выходные
	$positions=array();	
	foreach($_POST as $k=>$v){
	  if(eregi("^new_vyh_otp_date_pdate_",$k)){
		  
		  $hash=eregi_replace("^new_vyh_otp_date_pdate_","",$k);
		   $positions[]=array(
				  'petition_id'=>$claim_id,
				  
				  
				  'pdate'=>Datefromdmy($_POST['new_vyh_otp_date_pdate_'.$hash])
				   
			  );
	  }
	}
	
	//внесем даты
	$log_entries=$mi->AddVyhDatesOtp($code,$positions);
	//die();
	//запишем в журнал
	foreach($log_entries as $k=>$v){
		 
			$descr=SecStr('ƒата '.date('d.m.Y',$v['pdate']));
			 
			
			$log->PutEntry($result['id'],'добавил дату отпуска за работу в выходные', NULL, 724,NULL,$descr,$claim_id);	
	 
	}
	
	/*echo '<pre>';
	print_r($positions);
	
	echo '</pre>';	
	die(); */
	//файлы задачи
	
	
	$fmi=new PetitionFileItem;
   foreach($_POST as $k=>$v){
	  if(eregi("^upload_file_",$k)){
		  //echo eregi_replace("^upload_file_", '', eregi_replace("_tmp$",".tmp",$k)).' = '.$v;
		  
		  $filename=eregi_replace("^upload_file_", '', eregi_replace("_tmp$",".tmp",$k));
		  $fmi->Add(array('user_d_id'=>$claim_id, 
		  					'filename'=>SecStr(basename($filename)), 
							'orig_name'=>SecStr($v),
							'user_id'=>$result['id'],
							'pdate'=>time() 
							));
		  $log->PutEntry($result['id'], 'прикрепил файл к за€влению', NULL, 725, NULL,'Ќомер за€влени€: '.$order_params['code'].' —лужебное им€ файла: '.SecStr(basename($filename)).'  »м€ файла: '.SecStr($v),$claim_id);
		  
	  }
	}
	
	if(isset($_POST['makeSaveOrder'])){
		header("Location: petition_my_history.php?id=".$claim_id);
	}else header("Location: petitions.php");
	die();
	
}

//работа с хедером
//работа с хедером
$stop_popup=true;

require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->display('top.html');
unset($smarty);


$_menu_id=59;
	include('inc/menu.php');
	
	//echo date('d.m.Y H:i:s', 1447887600);
	
	//демонстраци€ страницы
	$smarty = new SmartyAdm;
	
	
	$sm=new SmartyAdm;
	
	
	
	//создание заказа
	$sm1=new SmartyAdm;
	
	
	$sm1->assign('session_id',session_id());
	
	
	//$lc->ses->ClearOldSessions();
		
	//$sm1->assign('code', $lc->GenLogin($result['id']));
	$sm1->assign('kind_id', $kind_id);
	
	
	$ckg=new petitionKindGroup;
	$sm1->assign('items',$ckg->GetItemsArr());
	
	$sm1->assign('now',date('d.m.Y'));
	
	
	
	//поле менеджер
	$sm1->assign('manager_id', $result['id']);
	$sm1->assign('manager_string', $result['name_s']);
	
	//список сотрудников
	 
	$_usg=new UsersSGroup; $dec_us=new DBDecorator;
	$limited_user=NULL;
	if($au->FltUser($result)){
		//echo 'z';
		$_u_to_u=new UserToUser();
		$u_to_u=$_u_to_u->GetExtendedViewedUserIdsArr($result['id']);
		$limited_user=$u_to_u['sector_ids'];
		$dec_us->AddEntry(new SqlEntry('u.id', NULL, SqlEntry::IN_VALUES, NULL,$limited_user));	
	}
	
	$dec_us->AddEntry(new SqlOrdEntry('u.name_s',SqlOrdEntry::ASC));
	
	$managers=$_usg->GetItemsByDecArr($dec_us);
	$sm1->assign('can_modify_manager',$au->user_rights->CheckAccess('w',1144) );
	$sm1->assign('managers', $managers);
	
	
	//предустановленные получатели
	//пока - грузим всех возможных сотрудников с правом 831
	$_pug=new UsersSGroup;   // PetitionUserPresetGroup;
	$dec_users=new DBDecorator;
	$dec_users->AddEntry(new SqlEntry('is_active',1, SqlEntry::E));
	$dec_users->AddEntry(new SqlEntry('group_id',1, SqlEntry::E));	
	
	$dec_users->AddEntry(new SqlOrdEntry('name_s', SqlOrdEntry::ASC));
		
	//GetUserIdsByRightArr
	
	$ruk_ids=array();
	$ruk_ids=$_pug->GetUserIdsByRightArr('w', 831);
	
	//удалим Ў
	foreach($ruk_ids as $k=>$v){
		if(in_array($v, array(2,3))) unset($ruk_ids[$k]);
	}
	
	$dec_users->AddEntry(new SqlEntry('u.id', NULL, SqlEntry::IN_VALUES, NULL,$ruk_ids));	
	
	$sm1->assign('users',$_pug->GetItemsByDecArr($dec_users)); //>GetItemsArr());
	
	
	//дл€ видов 4-5 - причины ухода/прихода
	$_ea_gr=new PetitionEarlyReasonGroup;
	$sm1->assign('vyh_early',$_ea_gr->GetItemsArr(0));
	
	//причины работы в выходные
	$_pvrg=new PetitionVyhReasonGroup;
	$sm1->assign('vyh_reasons',$_pvrg->GetItemsArr(0));
	
	
	//дл€ видов 6 и 7 - подгрузить виды целей визита
	$_ppg=new PetitionPurposeGroup;
	$ppg=$_ppg->GetItemsArr(0);
	$sm1->assign('purposes', $ppg);
	
	//дл€ видов 6-7-  подгрузить город
	$_city=new SupplierCityItem;
	$city=$_city->GetFullCity(1);
	$_cous=new SupplierCountryGroup;
		$cous=$_cous->GetItemsArr();
		$sm1->assign('cous', $cous);
		 
	 
	$sm1->assign('city_id', 1);
	$sm1->assign('city', $city['fullname']);
	
	
	$from_hrs=array();
	$from_hrs[]='';
		for($i=0;$i<=23;$i++) {
			 if(in_array($kind_id, array(4,5))) if(($i<8)||($i>19)) continue;
			 
			 $from_hrs[]=sprintf("%02d",$i);
			 
		}
		$sm1->assign('from_hrs',$from_hrs);
		$sm1->assign('from_hr',"");
				
		$from_ms=array();
		$from_ms[]='';
		for($i=0;$i<=59;$i++) $from_ms[]=sprintf("%02d",$i);
		$sm1->assign('from_ms',$from_ms);
		$sm1->assign('from_m',"");
	
	$sm1->assign('user_id', $result['id']);
	
	
	$sm1->assign('can_confirm',$au->user_rights->CheckAccess('w',829) );
	
	
	//дл€ вида 3 - из внутренней служебки
	if($kind_id==3){
		if(isset($_GET['sched_id'])&&isset($_GET['doc_vn_id'])){
			$sched_id=abs((int)$_GET['sched_id']);
			$_sched=new Sched_AbstractItem;
			$sched=$_sched->getitembyid($sched_id);
			if($sched!==false){
				$sm1->assign('vyh_reason_id',2);
				$sm1->assign('sched_id', $sched_id);
				$sm1->assign('manager_id', $sched['manager_id']);
				$sm1->assign('can_modify', true);
				
				//подт€нуть даты
				$doc_vn_id=abs((int)$_GET['doc_vn_id']);
				$_doc_vn=new DocVn_AbstractItem;
				$doc_vn=$_doc_vn->getitembyid($doc_vn_id);
				if($doc_vn!==false){
					$_opt=new DocVn_VyhDateGroup;
					$sm1->assign('vyh_otp_dates', $_opt->GetItemsArrById($doc_vn_id));
				}
			}
			
			
				
		}
		
	}
	
	//$sm1->assign('is_dealer',($result['group_id']==3));
	$llg=$sm1->fetch('petition/petitions_create_form.html');
	
	
	$sm->assign('log',$llg);
	
	
	
	$content=$sm->fetch('petition/petitions_create.html');
	
	
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