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
require_once('classes/discr_table_objects.php');
require_once('classes/actionlog.php');

require_once('classes/missiongroup.php');
require_once('classes/missionitem.php');

require_once('classes/missionstatusitem.php');
require_once('classes/missionhistoryitem.php');


require_once('classes/useritem.php');
require_once('classes/supplieritem.php');
require_once('classes/missionfileitem.php');
require_once('classes/missionexpitem.php');
require_once('classes/missionexpnameitem.php');
require_once('classes/missionexpgroup.php');
require_once('classes/missionexpnamegroup.php');

require_once('classes/user_s_group.php');

require_once('classes/opfitem.php');



require_once('classes/supplier_city_item.php');
require_once('classes/supplier_region_item.php');
require_once('classes/supplier_district_item.php');


$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Создание командировки');


$au=new AuthUser();
$result=$au->Auth();

$log=new ActionLog;

if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

if(!$au->user_rights->CheckAccess('w',592)){
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


$mi=new MissionItem;
$hi=new MissionHistoryItem;

$_exi=new MissionExpItem;
if(isset($_POST['makeOrder'])){
	
	
	//отправка задачи
	$order_params=array();
	$order_params['txt']=SecStr($_POST['txt']);
	$order_params['pdate']=time();
	$order_params['status_id']=1;
	
	if($au->user_rights->CheckAccess('w',593)){
		$order_params['sent_user_id']=abs((int)$_POST['sent_user_id']);
	}else $order_params['sent_user_id']=$result['id'];
	
  	
  	$order_params['user_id']=$result['id'];
	
	$order_params['city_id']=abs((int)$_POST['city_id']);
	$order_params['supplier_id']=abs((int)$_POST['supplier_id']);
	
	$order_params['pdate_begin']=Datefromdmy($_POST['pdate_begin']);
	$order_params['pdate_end']=Datefromdmy($_POST['pdate_end']);
	

	$claim_id=$mi->Add($order_params);
	
	
	//запись в журнал
	$_ci=new SupplierCityItem;
	$_ri=new SupplierRegionItem;
	$_di=new SupplierDistrictItem;
	
	$ci=$_ci->GetItemById($order_params['city_id']);
	$ri=$_ri->GetItemById($ci['region_id']);
	$di=$_di->GetItemById($ci['district_id']);
	
	$_si=new SupplierItem;
	$_opf=new OpfItem;
	
	
	$si=$_si->getitembyid($order_params['supplier_id']);
	
	$opf=$_opf->getitembyid($si['opf_id']);
	
	
	
	$log->PutEntry($result['id'],'создал командировку',NULL,592,NULL,'Номер командировки: '.$claim_id.', Период с: '.$_POST['pdate_begin'].' по: '.$_POST['pdate_end'].', Контрагент: '.$opf['name'].' '.$si['full_name'].', город: '.$ci['name'].', '.$di['name'].', '.$ri['name'].', Описание командировки:'.$order_params['txt'],$claim_id);
	
	//внесем плановые расходы:
	$_mei=new MissionExpItem;
	$_men=new MissionExpNameItem;
	foreach($_POST as $k=>$v){
	  if(eregi("^exp_id_",$k)){
		  $user_id=abs((int)eregi_replace("^exp_id_","", $k));
		  $value=abs((float)str_replace(",",".",$v));
		  
		  
		  $_mei->Add(array('exp_id'=>$user_id, 'mission_id'=>$claim_id, 'plan'=>$value));
		  
		  
		 //print_r(array('exp_id'=>$user_id, 'mission_id'=>$claim_id, 'plan'=>$value));
		  
		  $user=$_men->GetItemById($user_id);
		  $log->PutEntry($result['id'], 'назначил плановый расход по командировке', NULL, 592, NULL,'Номер командировки: '.$claim_id.' '.SecStr($user['name']).' '.SecStr($value).' руб.',$claim_id);
	  }
	}
	
	
	
	
	//создание события задачи
	$params=array();
	$params['txt']=SecStr($_POST['txt']);
	$params['user_id']=$result['id'];
	$params['mission_id']=$claim_id;
	$params['pdate']=$order_params['pdate'];
	$params['status_id']=$order_params['status_id'];
	$params['is_new']=1;
	
	
	$history_id=$hi->Add($params);
	
	$_si=new MissionStatusItem;
	$_status=$_si->GetItemById($params['status_id']);
	$log->PutEntry($result['id'],'создал событие по командировке',NULL,594,NULL,'Номер командировки: '.$claim_id.' Описание события: '.$params['txt'].' Статус командировки: '.SecStr($_status['name']),$claim_id);
	
	
	
	//файлы задачи
	
	
	$fmi=new MissionFileItem;
   foreach($_POST as $k=>$v){
	  if(eregi("^upload_file_",$k)){
		  //echo eregi_replace("^upload_file_", '', eregi_replace("_tmp$",".tmp",$k)).' = '.$v;
		  
		  $filename=eregi_replace("^upload_file_", '', eregi_replace("_tmp$",".tmp",$k));
		  $fmi->Add(array('history_id'=>$history_id, 'filename'=>SecStr(basename($filename)), 'orig_name'=>SecStr($v)));
		  $log->PutEntry($result['id'], 'прикрепил файл к командировке', NULL, 594, NULL,'Номер командировки: '.$claim_id.' Служебное имя файла: '.SecStr(basename($filename)).'  Имя файла: '.SecStr($v),$claim_id);
		  
	  }
	}
	
	
	//print_r($_POST); die();
	
	header("Location: missions.php");
	die();
	
}

//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->display('top.html');
unset($smarty);



	include('inc/menu.php');
	
	
	
	//демонстрация страницы
	$smarty = new SmartyAdm;
	
	
	$sm=new SmartyAdm;
	
	
	
	//создание заказа
	$sm1=new SmartyAdm;
	
	
	$sm1->assign('session_id',session_id());
	
	
	
	
	$sm1->assign('pdate_begin',date('d.m.Y'));
	$sm1->assign('pdate_end',date('d.m.Y', time()+24*60*60*7));
	
	
	
	//сотр-к в коман-ку
	$_ug=new UsersSGroup;
	
	$_users=$_ug->GetItemsArr($result['id'], 1,1); //GetItemsOpt($result['id'],'name_s', false);
	
	$sm1->assign('users', $_users);
	//$sm1->assign('current_user', $result['id']);
	$sm1->assign('can_all_missions', $au->user_rights->CheckAccess('w',593));
	
	
	//статьи расходов
	$_me=new MissionExpNameGroup;
	$me=$_me->GetItemsArr();
	$sm1->assign('ras', $me);
	
	
	
	
	$llg=$sm1->fetch('mission/mission_create_form.html');
	
	
	$sm->assign('log',$llg);
	
	
	
	$content=$sm->fetch('mission/mission_create.html');
	
	
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