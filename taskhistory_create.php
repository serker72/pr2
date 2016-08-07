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

require_once('classes/taskstatusgroup.php');

require_once('classes/taskgroup.php');
require_once('classes/taskitem.php');


require_once('classes/taskfilegroup.php');
require_once('classes/taskfileitem.php');

require_once('classes/taskhistoryitem.php');

require_once('classes/taskstatusitem.php');

require_once('classes/taskitem.php');



require_once('classes/user_s_group.php');

require_once('classes/taskhistorygroup.php');

$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Новое событие задачи');

$au=new AuthUser();
$result=$au->Auth();


if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}
$mi=new TaskItem;
$hi=new TaskHistoryItem;
$log=new ActionLog;

$hg=new TaskHistoryGroup;

if(!$au->user_rights->CheckAccess('w',587)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}

if(!isset($_GET['id'])){
	if(!isset($_POST['id'])){
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include("404.php");
		die();	
	}else $id=abs((int)$_POST['id']);
}else $id=abs((int)$_GET['id']);



$claim=$mi->GetItemById($id);
if($claim===false){
	header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include("404.php");
		die();
}


$_tug=new TaskUserGroup;
$tug=$_tug->GetItemsArrById($id);
$tusers=array();
foreach($tug as $k=>$v) $tusers[]=$v['id'];

if(!$au->user_rights->CheckAccess('w',589)&&($claim['user_id']!=0)&&($claim['user_id']!=$result['id'])&&!in_array($result['id'],$tusers)){
	//и еще проверить, есть ли сотр-к в списке отв.
	//не наш заказ!
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();		
}







if(isset($_POST['makeOrder'])){
	//создание события заказа
	
	$params=array();
	$params['txt']=SecStr($_POST['txt']);
	$params['user_id']=$result['id'];
	$params['task_id']=$id;
	$params['pdate']=time();
	$params['status_id']=abs((int)$_POST['status_id']);
	
	
	
	
	
	$params['is_new']=1;
	
	
	//$hg->ToggleRead(
	$hg->ToggleRead($id,$result['id']);
	
	$history_id=$hi->Add($params);
	
	$_si=new TaskStatusItem;
	$_status=$_si->GetItemById($params['status_id']);
	
	$log->PutEntry($result['id'],'создал событие по задаче',NULL,587,NULL,'Номер задачи: '.$id.' Описание события: '.$params['txt'].' Статус задачи: '.SecStr($_status['name']),$id);
	
	
	
	$fmi=new TaskFileItem;
   foreach($_POST as $k=>$v){
	  if(eregi("^upload_file_",$k)){
		  //echo eregi_replace("^upload_file_", '', eregi_replace("_tmp$",".tmp",$k)).' = '.$v;
		  
		  $filename=eregi_replace("^upload_file_", '', eregi_replace("_tmp$",".tmp",$k));
		  $fmi->Add(array('history_id'=>$history_id, 'filename'=>SecStr(basename($filename)), 'orig_name'=>SecStr($v)));
		  
		  $log->PutEntry($result['id'], 'прикрепил файл к задаче', NULL, 587, NULL,'Номер задачи: '.$claim['id'].' Служебное имя файла: '.SecStr(basename($filename)).' Имя файла: '.SecStr($v),$id);
	  }
	}
	
	//обновим заказ: сумма, статус, менеджер
	$mi_params=array();
	
	
	//проверка возможности смены статуса
	
	if(isset($_POST['status_id'])){
		 
		 $_can_change_status=true;
		
		 if($_can_change_status) $mi_params['status_id']=abs((int)$_POST['status_id']); 
		 
	}
	
	
	
	$mmi=new TaskItem;
	$mmi->Edit($id,$mi_params);
	
	foreach($mi_params as $k=>$v){
		
		if(addslashes($claim[$k])!=$v){
		  if($k=='status_id'){
			  $log->PutEntry($result['id'],'редактировал статус задачи',NULL,587,NULL,'Номер задачи: '.$id.' Статус задачи: '.SecStr($_status['name']),$id);
			  continue;	
		  }
		  
		 
		
			$log->PutEntry($result['id'],'редактировал задачу',NULL,587, NULL, 'в поле '.$k.' установлено значение '.$v,$id);		
		}
	}
	
	
	header("Location: taskhistory.php?id=".$id);
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
	
	$claim['s_user_id']=$result['id'];
	
	$claim['pdate']=date("d.m.Y H:i:s", $claim['pdate']);
	$sm1->assign('claim',$claim);
	$sm1->assign('session_id',session_id());
	
	$sg=new TaskStatusGroup;
	$statuses=$sg->GetItemsArr();
	$status_ids=array(); $status_names=array();
	foreach($statuses as $k=>$v){
		$status_ids[]=$v['id'];
		$status_names[]=$v['name'];	
	}
	$sm1->assign('status_ids',$status_ids);
	$sm1->assign('status_names',$status_names);
	
	
	//можно менять статус, если есть w права на об-т 71
	//или если статус!=4
	/*$cannot_change_status=false;
	if($claim['status_id']==4){
		if($au->user_rights->CheckAccess('w',77)){
			
		}else $cannot_change_status=true;
	}
	
	
	if($claim['kind_id']==13){
			
		$sm1->assign('can_back_status',$au->user_rights->CheckAccess('w',118)); //аннулирование заявок на увел гарантию
	}else{
		$sm1->assign('can_back_status',$au->user_rights->CheckAccess('w',112)); //Откат статусов
	}
	
	
	$sm1->assign('cannot_change_status',$cannot_change_status);
	
	
	//добавить ограничения на статус - об-т 112
	
	//перечисление сотрудников с ограничением на об-т 112 - откат статусов заявок
	$sql='select u.* from user as u inner join user_rights as ur on u.id=ur.user_id where ur.right_id=2 and ur.object_id=112';
	$tset=new MysqlSet($sql);
	$trs=$tset->GetResult();
	$trc=$tset->GetResultNumRows();
	
	$users_list='';
	for($i=0; $i<$trc; $i++){
		$f=mysqli_fetch_array($trs);
		if(strlen($users_list)>0) $users_list.=', ';	
		$users_list.=stripslashes($f['name_s'].' ('.$f['login'].')');
	}
	
	$sm1->assign('cannot_back_status_message','Данное действие для Вас недоступно.\nПрава на данное действие имеют сотрудники '.$users_list.'.\nПожалуйста, обратитесь к компетентным сотрудникам.');
	
	
	//перечисление сотрудников с ограничением на об-т 118 - аннулирование заявок на увел гарантию
	$sql='select u.* from user as u inner join user_rights as ur on u.id=ur.user_id where ur.right_id=2 and ur.object_id=118';
	$tset=new MysqlSet($sql);
	$trs=$tset->GetResult();
	$trc=$tset->GetResultNumRows();
	
	$users_list='';
	for($i=0; $i<$trc; $i++){
		$f=mysqli_fetch_array($trs);
		if(strlen($users_list)>0) $users_list.=', ';	
		$users_list.=stripslashes($f['name_s'].' ('.$f['login'].')');
	}
	
	$sm1->assign('cannot_annul_garant_status_message','Данное действие для Вас недоступно.\nПрава на данное действие имеют сотрудники '.$users_list.'.\nПожалуйста, обратитесь к компетентным сотрудникам.');
	
	//перечисление сотрудников с ограничением на об-т 77 - изменение статуса после аннулирована
	$sql='select u.* from user as u inner join user_rights as ur on u.id=ur.user_id where ur.right_id=2 and ur.object_id=77';
	$tset=new MysqlSet($sql);
	$trs=$tset->GetResult();
	$trc=$tset->GetResultNumRows();
	
	$users_list='';
	for($i=0; $i<$trc; $i++){
		$f=mysqli_fetch_array($trs);
		if(strlen($users_list)>0) $users_list.=', ';	
		$users_list.=stripslashes($f['name_s'].' ('.$f['login'].')');
	}
	
	$sm1->assign('cannot_change_status_message','Данное действие для Вас недоступно.\nПрава на данное действие имеют сотрудники '.$users_list.'.\nПожалуйста, обратитесь к компетентным сотрудникам.');
	
	
	
	$ug=new UsersSGroup;
	$statuses=$ug->GetItemsArr(0,1);
	$status_ids=array(); $status_names=array();
	foreach($statuses as $k=>$v){
		$status_ids[]=$v['id'];
		$status_names[]=$v['name_s'].' '.$v['login'];	
	}
	$sm1->assign('man_ids',$status_ids);
	$sm1->assign('man_names',$status_names);
	*/
	
	$llg=$sm1->fetch('task/taskhistory_create_form.html');
	
	
	$sm->assign('log',$llg);
	
	$content=$sm->fetch('task/taskhistory_create.html');
	
	
	
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