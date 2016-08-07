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

require_once('classes/taskgroup.php');
require_once('classes/taskitem.php');
require_once('classes/taskkinditem.php');

require_once('classes/taskincominggroup.php');
require_once('classes/taskoutcominggroup.php');
require_once('classes/taskallgroup.php');
require_once('classes/taskkindgroup.php');

require_once('classes/taskstatusitem.php');
require_once('classes/taskhistoryitem.php');

require_once('classes/taskuseritem.php');
require_once('classes/tasksupplieritem.php');
require_once('classes/useritem.php');
require_once('classes/supplieritem.php');
require_once('classes/taskfileitem.php');


$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Создание задачи');


$au=new AuthUser();
$result=$au->Auth();

$log=new ActionLog;

if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

if(!$au->user_rights->CheckAccess('w',586)){
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


$mi=new TaskItem;
$hi=new taskhistoryitem;
if(isset($_POST['makeOrder'])){
	
	
	//отправка задачи
	$order_params=array();
	$order_params['txt']=SecStr($_POST['txt']);
	$order_params['pdate']=time();
	$order_params['status_id']=1;
	
	$order_params['kind_id']=abs((int)$_POST['kind_id']);
	
  	$order_params['task_pdate']=DateYmd_from_dmY($_POST['task_pdate']);
	if(isset($_POST['do_time'])&&($_POST['do_time']==1)) {
		$order_params['task_ptime']=$_POST['task_ptime'];	
	}
	$order_params['task_pdate']=DateYmd_from_dmY($_POST['task_pdate']);
	
  	
  	$order_params['user_id']=$result['id'];
	$order_params['status_id']=1; //$order_params['pdate'];
	
	$_claim_kind_item=new TaskKindItem;
	$ckind=$_claim_kind_item->GetItemById($order_params['kind_id']);
	
	$claim_id=$mi->Add($order_params);
	
	
	$log->PutEntry($result['id'],'создал задачу',NULL,586,NULL,'Номер задачи: '.$claim_id.' Описание задачи:'.$order_params['txt'].' Вид задачи: '.SecStr($ckind['name']),$claim_id);
	
	
	//ответственные
	$_tui=new TaskUserItem;
	$_ui=new UserItem;
	foreach($_POST as $k=>$v){
	  if(eregi("^user_id_",$k)){
		  $user_id=abs((int)$v);
		  $_tui->Add(array('user_id'=>$user_id, 'task_id'=>$claim_id));
		  
		  $user=$_ui->GetItemById($user_id);
		  $log->PutEntry($result['id'], 'назначил ответственного сотрудника по задаче', $user_id, 586, NULL,'Номер задачи: '.$claim_id.' Сотрудник: '.SecStr($user['name_s']).' '.SecStr($user['login']),$claim_id);
	  }
	}
	
	//контрагенты
	$_tsi=new TaskSupplierItem;
	$_si=new SupplierItem;
	foreach($_POST as $k=>$v){
	  if(eregi("^supplier_id_",$k)){
		  $user_id=abs((int)$v);
		  $_tsi->Add(array('supplier_id'=>$user_id, 'task_id'=>$claim_id));
		  
		  $user=$_si->GetItemById($user_id);
		  $log->PutEntry($result['id'], 'назначил контрагента по задаче', NULL, 586, NULL,'Номер задачи: '.$claim_id.' Контрагент: '.SecStr($user['full_name']),$claim_id);
	  }
	}
	
	
	//создание события задачи
	$params=array();
	$params['txt']=SecStr($_POST['txt']);
	$params['user_id']=$result['id'];
	$params['task_id']=$claim_id;
	$params['pdate']=$order_params['pdate'];
	$params['status_id']=$order_params['status_id'];
	$params['is_new']=1;
	
	
	$history_id=$hi->Add($params);
	
	$_si=new TaskStatusItem;
	$_status=$_si->GetItemById($params['status_id']);
	$log->PutEntry($result['id'],'создал событие по задаче',NULL,587,NULL,'Номер задачи: '.$claim_id.' Описание события: '.$params['txt'].' Статус задачи: '.SecStr($_status['name']),$claim_id);
	
	
	
	//файлы задачи
	
	
	$fmi=new TaskFileItem;
   foreach($_POST as $k=>$v){
	  if(eregi("^upload_file_",$k)){
		  //echo eregi_replace("^upload_file_", '', eregi_replace("_tmp$",".tmp",$k)).' = '.$v;
		  
		  $filename=eregi_replace("^upload_file_", '', eregi_replace("_tmp$",".tmp",$k));
		  $fmi->Add(array('history_id'=>$history_id, 'filename'=>SecStr(basename($filename)), 'orig_name'=>SecStr($v)));
		  $log->PutEntry($result['id'], 'прикрепил файл к задаче', NULL, 587, NULL,'Номер задачи: '.$claim_id.' Служебное имя файла: '.SecStr(basename($filename)).'  Имя файла: '.SecStr($v),$claim_id);
		  
	  }
	}
	
	
	header("Location: tasks.php");
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
	
	
	$ckg=new TaskKindGroup;
	$sm1->assign('items',$ckg->GetItemsArr());
	
	$sm1->assign('now',date('d.m.Y'));
	
	//$sm1->assign('is_dealer',($result['group_id']==3));
	$llg=$sm1->fetch('task/tasks_create_form.html');
	
	
	$sm->assign('log',$llg);
	
	
	
	$content=$sm->fetch('task/tasks_create.html');
	
	
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