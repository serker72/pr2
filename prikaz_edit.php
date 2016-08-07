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

require_once('classes/myclaimgroup.php');
require_once('classes/myorderitem.php');


require_once('classes/claimfilegroup.php');
require_once('classes/claimfileitem.php');

require_once('classes/claimhistoryitem.php');

require_once('classes/claimstatusitem.php');

require_once('classes/claimkindgroup.php');
require_once('classes/myclaimitem.php');
require_once('classes/claimfileitem.php');
require_once('classes/claimhistoryitem.php');
require_once('classes/claimstatusitem.php');
require_once('classes/claimkinditem.php');



$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Создание заявки');

$au=new AuthUser();
$result=$au->Auth();

if(($result===NULL)||(!$au->CheckFactAddress())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}
require_once('inc/restr.php');


$mi=new MyClaimItem($result['id']);
$hi=new ClaimHistoryItem();

$log=new ActionLog();

//внесение изменений в права
if(isset($_POST['doInp'])){
	
	$man=new DiscrMan;
	$log=new ActionLog;
	
	foreach($_POST as $k=>$v){
		if(eregi("^do_edit_",$k)&&($v==1)){
			//echo($k);
			//do_edit_w_4_2
			//1st letter - 	right
			//2nd figure - object_id
			//3rd figure - user_id
			eregi("^do_edit_([[:alpha:]])_([[:digit:]]+)_([[:digit:]]+)$",$k,$regs);
			//var_dump($regs);
			if(($regs!==NULL)&&isset($_POST['state_'.$regs[1].'_'.$regs[2].'_'.$regs[3]])){
				$state=$_POST['state_'.$regs[1].'_'.$regs[2].'_'.$regs[3]];
				
				//установить проверку, есть ли права на администрирование данного объекта данным пользователем
				if(!$au->user_rights->CheckAccess('x',$regs[2])){
					continue;
				}
				
				
				//public function PutEntry($user_subject_id, $description, $user_object_id=NULL, $object_id=NULL, $user_group_id=NULL)
				if($state==1){
					$man->GrantAccess($regs[3], $regs[1], $regs[2]);
					$pro=$au->GetProfile();
					$log->PutEntry($pro['id'], "установил доступ ".$regs[1],$regs[3],$regs[2]);
					
				}else{
					$man->RevokeAccess($regs[3], $regs[1], $regs[2]);
					$pro=$au->GetProfile();
					$log->PutEntry($pro['id'], "удалил доступ ".$regs[1],$regs[3],$regs[2]);
				}
				
				
			}
		}
	}
	
	header("Location: myclaim.php");	
	die();
}

if(!$au->user_rights->CheckAccess('w',49)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}


if(!isset($_GET['action'])){
	if(!isset($_POST['action'])){
		$action=0;
	}else $action=abs((int)$_POST['action']);
}else $action=abs((int)$_GET['action']);


if(isset($_POST['makeOrder'])){
	//отправка заявки
	$order_params=array();
	$order_params['txt']=SecStr($_POST['txt']);
	$order_params['pdate']=time();
	$order_params['status_id']=1;
	
	$order_params['kind_id']=abs((int)$_POST['kind_id']);
	$order_params['order_id']=0;
  
	$order_params['status_id']=1; //$order_params['pdate'];
	
	if(isset($result['fact_address_id'])&&($result['fact_address_id']!==false)) $order_params['fact_address_id']=$result['fact_address_id'];
	
	//$order_params['summ']=0;
	
	$_claim_kind_item=new ClaimKindItem;
	$ckind=$_claim_kind_item->GetItemById($order_params['kind_id']);
	
	$claim_id=$mi->Add($order_params);
	
	$log->PutEntry($result['id'],'создал заявку',NULL,49,NULL,'Номер заявки: '.$claim_id.' Описание заявки:'.$order_params['txt'].' Вид заявки: '.SecStr($ckind['name']));
	
	
	$params=array();
	$params['txt']=SecStr($_POST['txt']);
	$params['user_id']=$result['id'];
	$params['claim_id']=$claim_id;
	$params['pdate']=$order_params['pdate'];
	$params['status_id']=$order_params['status_id'];
	$params['is_new']=1;
	
	if(isset($result['fact_address_id'])&&($result['fact_address_id']!==false)) $params['fact_address_id']=$result['fact_address_id'];
	
	$history_id=$hi->Add($params);
	
	$_si=new ClaimStatusItem;
	$_status=$_si->GetItemById($params['status_id']);
	$log->PutEntry($result['id'],'создал событие по заявке',NULL,50,NULL,'Номер заявки: '.$claim_id.' Описание события: '.$params['txt'].' Статус заявки: '.SecStr($_status['name']));
	
	$fmi=new ClaimFileItem;	
   foreach($_POST as $k=>$v){
	  if(eregi("^upload_file_",$k)){
		  //echo eregi_replace("^upload_file_", '', eregi_replace("_tmp$",".tmp",$k)).' = '.$v;
		  
		  $filename=eregi_replace("^upload_file_", '', eregi_replace("_tmp$",".tmp",$k));
		  $fmi->Add(array('history_id'=>$history_id, 'filename'=>SecStr(basename($filename)), 'orig_name'=>SecStr($v)));
		  $log->PutEntry($result['id'], 'прикрепил файл к заявке', NULL, 50, NULL,'Номер заявки: '.$order_id.' Служебное имя файла: '.SecStr(basename($filename)).'  Имя файла: '.SecStr($v));
		  
	  }
	}
	
	header("Location: myclaim.php");
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
	
	
	//строим вкладку администрирования
	if(HAS_ADMIN_TABS){
	$sm->assign('has_admin',$au->user_rights->CheckAccess('x',47)||
							$au->user_rights->CheckAccess('x',49)||
							$au->user_rights->CheckAccess('x',50)
	);
	$dto=new DiscrTableObjects($result['id'],array('47','49','50'));
	$admin=$dto->Draw('create_myclaim.php','admin/admin_objects.html');
	$sm->assign('admin',$admin);
	}
	
	//создание заказа
	$sm1=new SmartyAdm;
	
	
	$sm1->assign('session_id',session_id());
	
	
	$ckg=new ClaimKindGroup;
	$sm1->assign('items',$ckg->GetItemsArr());
	
	
	$llg=$sm1->fetch('claim/createmyclaim_form.html');
	
	
	$sm->assign('log',$llg);
	
	$content=$sm->fetch('claim/createmyclaim.html');
	
	
	
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