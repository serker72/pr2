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

require_once('classes/taskgroup.php');
require_once('classes/taskitem.php');

require_once('classes/taskincominggroup.php');
require_once('classes/taskoutcominggroup.php');
require_once('classes/taskallgroup.php');


$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'“екущие задачи');

$au=new AuthUser();
$result=$au->Auth();

$log=new ActionLog;

if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

if(!$au->user_rights->CheckAccess('w',585)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}




if(!isset($_GET['tab_page'])){
	if(!isset($_POST['tab_page'])){
		$tab_page=1;
	}else $tab_page=abs((int)$_POST['tab_page']); 
}else $tab_page=abs((int)$_GET['tab_page']);

if(!isset($_GET['sortmode_1'])){
	if(!isset($_POST['sortmode_1'])){
		$sortmode_1=0;
	}else $sortmode_1=abs((int)$_POST['sortmode_1']); 
}else $sortmode_1=abs((int)$_GET['sortmode_1']);

if(!isset($_GET['sortmode_2'])){
	if(!isset($_POST['sortmode_2'])){
		$sortmode_2=0;
	}else $sortmode_2=abs((int)$_POST['sortmode_2']); 
}else $sortmode_2=abs((int)$_GET['sortmode_2']);


if(!isset($_GET['sortmode_3'])){
	if(!isset($_POST['sortmode_3'])){
		$sortmode_3=0;
	}else $sortmode_3=abs((int)$_POST['sortmode_3']); 
}else $sortmode_3=abs((int)$_GET['sortmode_3']);





//журнал событий 
$log=new ActionLog;
$log->PutEntry($result['id'],'открыл раздел «адачи',NULL,585);
//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->display('top.html');
unset($smarty);


$_menu_id=53;
	include('inc/menu.php');
	
	
	
	//демонстраци€ страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	
	
	//вход€щие задачи
	$_tg=new TaskIncomingGroup;
	$prefix=$_tg->prefix;
	
	if(isset($_GET['from'.$prefix])) $from=abs((int)$_GET['from'.$prefix]);
	else $from=0;
	
	if(isset($_GET['to_page'.$prefix])) $to_page=abs((int)$_GET['to_page'.$prefix]);
	else $to_page=ITEMS_PER_PAGE;
	
	$decorator=new DBDecorator;
	//є
	if(isset($_GET['id'.$prefix])&&(strlen($_GET['id'.$prefix])>0)){
		$decorator->AddEntry(new UriEntry('id',$_GET['id'.$prefix]));
		$decorator->AddEntry(new SqlEntry('t.id',abs((int)$_GET['id'.$prefix]), SqlEntry::LIKE));
	}
	
	
	//срок выполнени€
	if(!isset($_GET['pdate1'.$prefix])){
	
			$_pdate1=DateFromdmY(date("d.m.Y"))-60*60*24*30*3;
			$pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
		
	}else $pdate1 = $_GET['pdate1'.$prefix];
	
	
	
	if(!isset($_GET['pdate2'.$prefix])){
			
			$_pdate2=DateFromdmY(date("d.m.Y"))+60*60*24*30*3;
			$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
	}else $pdate2 = $_GET['pdate2'.$prefix];
	
	
	$decorator->AddEntry(new UriEntry('pdate1',$pdate1));
	$decorator->AddEntry(new UriEntry('pdate2',$pdate2));
	$decorator->AddEntry(new SqlEntry('t.task_pdate',DateYmd_from_dmY($pdate1), SqlEntry::BETWEEN,DateYmd_from_dmY($pdate2)));
	
	
	if(!isset($_GET['given_pdate1'.$prefix])){
	
			$_pdate1=DateFromdmY(date("d.m.Y"))-60*60*24*30*3;
			$pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
		
	}else $pdate1 = $_GET['given_pdate1'.$prefix];
	
	
	
	if(!isset($_GET['given_pdate2'.$prefix])){
			
			$_pdate2=DateFromdmY(date("d.m.Y"))+60*60*24;
			$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
	}else $pdate2 = $_GET['given_pdate2'.$prefix];
	
	
	$decorator->AddEntry(new UriEntry('given_pdate1',$pdate1));
	$decorator->AddEntry(new UriEntry('given_pdate2',$pdate2));
	$decorator->AddEntry(new SqlEntry('t.pdate',DateFromdmY($pdate1), SqlEntry::BETWEEN,DateFromdmY($pdate2)+60*60*24));
	
	
	
	
	
	
	if(isset($_GET['status_all_5'.$prefix])&&($_GET['status_all_5'.$prefix]==1)){
		$decorator->AddEntry(new SqlEntry('t.status_id',5, SqlEntry::NE));
		$decorator->AddEntry(new UriEntry('status_all_5',1));
	}elseif(isset($_GET['status_all_5'.$prefix])&&($_GET['status_all_5'.$prefix]==0)){
		if(isset($_GET['status_id'.$prefix])){
			if($_GET['status_id'.$prefix]>0){
				$decorator->AddEntry(new SqlEntry('t.status_id',abs((int)$_GET['status_id'.$prefix]), SqlEntry::E));
			}
			$decorator->AddEntry(new UriEntry('status_id',$_GET['status_id'.$prefix]));
		}else{
		
		  if(isset($_COOKIE['task_status_id'])){
				  $status_id=$_COOKIE['task_status_id'];
		  }else $status_id=0;
		  
		  if($status_id>0) $decorator->AddEntry(new SqlEntry('t.status_id',$status_id, SqlEntry::E));
		  $decorator->AddEntry(new UriEntry('status_id',$status_id));
		}
	}else{
		if((count($_GET)>1)&&($tab_page==1)) {
			$decorator->AddEntry(new UriEntry('status_all_5',0));	
			
			if(isset($_GET['status_id'.$prefix])){
				if($_GET['status_id'.$prefix]>0){
					$decorator->AddEntry(new SqlEntry('t.status_id',abs((int)$_GET['status_id'.$prefix]), SqlEntry::E));
				}
				$decorator->AddEntry(new UriEntry('status_id',$_GET['status_id'.$prefix]));
			}else{
			
			  if(isset($_COOKIE['task_status_id'])){
					  $status_id=$_COOKIE['task_status_id'];
			  }else $status_id=0;
			  
			  if($status_id>0) $decorator->AddEntry(new SqlEntry('t.status_id',$status_id, SqlEntry::E));
			  $decorator->AddEntry(new UriEntry('status_id',$status_id));
			}
			
		}else {
			$decorator->AddEntry(new UriEntry('status_all_5',1));	
			$decorator->AddEntry(new SqlEntry('t.status_id',5, SqlEntry::NE));
		}
	}
	
	
	
	
	if(isset($_GET['kind_id'.$prefix])){
		if($_GET['kind_id'.$prefix]>0){
			$decorator->AddEntry(new SqlEntry('t.kind_id',abs((int)$_GET['kind_id'.$prefix]), SqlEntry::E));
		}
		$decorator->AddEntry(new UriEntry('kind_id',$_GET['kind_id'.$prefix]));
	}
	
	
	//постановщик
	if(isset($_GET['user_name'.$prefix])&&(strlen($_GET['user_name'.$prefix])>0)){
		$decorator->AddEntry(new UriEntry('user_name',$_GET['user_name'.$prefix]));
		$decorator->AddEntry(new SqlEntry('name_s',SecStr($_GET['user_name'.$prefix]), SqlEntry::LIKE));
	}
	
	//к-т
	if(isset($_GET['supplier_name'.$prefix])&&(strlen($_GET['supplier_name'.$prefix])>0)){
		$decorator->AddEntry(new UriEntry('supplier_name',$_GET['supplier_name'.$prefix]));
		
		$decorator->AddEntry(new SqlEntry('t.id','select task_id from task_supplier where supplier_id in(select id from supplier where full_name like "%'.SecStr($_GET['supplier_name'.$prefix]).'%")', SqlEntry::IN_SQL));
	}
	
	//отв сотр
	if(isset($_GET['otv_user_name'.$prefix])&&(strlen($_GET['otv_user_name'.$prefix])>0)){
		$decorator->AddEntry(new UriEntry('otv_user_name',$_GET['otv_user_name'.$prefix]));
		$decorator->AddEntry(new SqlEntry('t.id','select task_id from task_user where user_id in(select id from user where name_s like "%'.SecStr($_GET['otv_user_name'.$prefix]).'%" or login like "%'.SecStr($_GET['otv_user_name'.$prefix]).'%")', SqlEntry::IN_SQL));
	}
	
	
	//сортировка
	if(!isset($_GET['sortmode'.$prefix])){
		$sortmode=3;	
	}else{
		$sortmode=abs((int)$_GET['sortmode'.$prefix]);
	}
	
	
	
	switch($sortmode){
		case 0:
			$decorator->AddEntry(new SqlOrdEntry('t.id',SqlOrdEntry::DESC));
		break;
		case 1:
			$decorator->AddEntry(new SqlOrdEntry('t.id',SqlOrdEntry::ASC));
		break;
		case 2:
			$decorator->AddEntry(new SqlOrdEntry('t.task_pdate',SqlOrdEntry::DESC));
			$decorator->AddEntry(new SqlOrdEntry('t.task_ptime',SqlOrdEntry::DESC));
		break;	
		case 3:
			$decorator->AddEntry(new SqlOrdEntry('t.task_pdate',SqlOrdEntry::ASC));
			$decorator->AddEntry(new SqlOrdEntry('t.task_ptime',SqlOrdEntry::ASC));
		break;
		case 4:
			$decorator->AddEntry(new SqlOrdEntry('t.pdate',SqlOrdEntry::DESC));
		break;
		case 5:
			$decorator->AddEntry(new SqlOrdEntry('t.pdate',SqlOrdEntry::ASC));
		break;	
		case 6:
			$decorator->AddEntry(new SqlOrdEntry('kind_name',SqlOrdEntry::DESC));
		break;
		case 7:
			$decorator->AddEntry(new SqlOrdEntry('kind_name',SqlOrdEntry::ASC));
		break;
		
		case 8:
			$decorator->AddEntry(new SqlOrdEntry('status_name',SqlOrdEntry::DESC));
		break;
		case 9:
			$decorator->AddEntry(new SqlOrdEntry('status_name',SqlOrdEntry::ASC));
		break;
		
		
		
		default:
			$decorator->AddEntry(new SqlOrdEntry('t.id',SqlOrdEntry::ASC));
		break;	
		
	}
	
	
	$decorator->AddEntry(new UriEntry('sortmode', $sortmode));
	
	
	
	
	$decorator->AddEntry(new UriEntry('tab_page',1));
	
	
	
	$ships=$_tg->ShowPos($result['id'], 'task/tasks.html', $decorator, false, $au->user_rights->CheckAccess('w',586), $alls, $from,$to_page);
	//$ships='–аздел находитс€ в разработке.';
	
	$res= $_tg->_thg->CountNewOrders($result['id']);
	if($res>0) $sm->assign('incoming_tasks_count','('.$res.')');
		
	$sm->assign('tasks_1',$ships); 
	
	
	
	
	
	
/***********************************************************************************************************/
//исход задачи
	$_tg=new TaskOutcomingGroup;
	$prefix=$_tg->prefix;
	
	if(isset($_GET['from'.$prefix])) $from=abs((int)$_GET['from'.$prefix]);
	else $from=0;
	
	if(isset($_GET['to_page'.$prefix])) $to_page=abs((int)$_GET['to_page'.$prefix]);
	else $to_page=ITEMS_PER_PAGE;
	
	$decorator=new DBDecorator;
	
	//є
	if(isset($_GET['id'.$prefix])&&(strlen($_GET['id'.$prefix])>0)){
		$decorator->AddEntry(new UriEntry('id',$_GET['id'.$prefix]));
		$decorator->AddEntry(new SqlEntry('t.id',abs((int)$_GET['id'.$prefix]), SqlEntry::LIKE));
	}
	
	
	
	
	//срок выполнени€
	if(!isset($_GET['pdate1'.$prefix])){
	
			$_pdate1=DateFromdmY(date("d.m.Y"))-60*60*24*30*3;
			$pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
		
	}else $pdate1 = $_GET['pdate1'.$prefix];
	
	
	
	if(!isset($_GET['pdate2'.$prefix])){
			
			$_pdate2=DateFromdmY(date("d.m.Y"))+60*60*24*30*3;
			$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
	}else $pdate2 = $_GET['pdate2'.$prefix];
	
	
	$decorator->AddEntry(new UriEntry('pdate1',$pdate1));
	$decorator->AddEntry(new UriEntry('pdate2',$pdate2));
	$decorator->AddEntry(new SqlEntry('t.task_pdate',DateYmd_from_dmY($pdate1), SqlEntry::BETWEEN,DateYmd_from_dmY($pdate2)));
	
	
	if(!isset($_GET['given_pdate1'.$prefix])){
	
			$_pdate1=DateFromdmY(date("d.m.Y"))-60*60*24*30*3;
			$pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
		
	}else $pdate1 = $_GET['given_pdate1'.$prefix];
	
	
	
	if(!isset($_GET['given_pdate2'.$prefix])){
			
			$_pdate2=DateFromdmY(date("d.m.Y"))+60*60*24;
			$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
	}else $pdate2 = $_GET['given_pdate2'.$prefix];
	
	
	$decorator->AddEntry(new UriEntry('given_pdate1',$pdate1));
	$decorator->AddEntry(new UriEntry('given_pdate2',$pdate2));
	$decorator->AddEntry(new SqlEntry('t.pdate',DateFromdmY($pdate1), SqlEntry::BETWEEN,DateFromdmY($pdate2)+60*60*24));
	
	
	
	if(isset($_GET['status_all_5'.$prefix])&&($_GET['status_all_5'.$prefix]==1)){
		$decorator->AddEntry(new SqlEntry('t.status_id',5, SqlEntry::NE));
		$decorator->AddEntry(new UriEntry('status_all_5',1));
	}elseif(isset($_GET['status_all_5'.$prefix])&&($_GET['status_all_5'.$prefix]==0)){
		if(isset($_GET['status_id'.$prefix])){
			if($_GET['status_id'.$prefix]>0){
				$decorator->AddEntry(new SqlEntry('t.status_id',abs((int)$_GET['status_id'.$prefix]), SqlEntry::E));
			}
			$decorator->AddEntry(new UriEntry('status_id',$_GET['status_id'.$prefix]));
		}else{
		
		  if(isset($_COOKIE['task_status_id'])){
				  $status_id=$_COOKIE['task_status_id'];
		  }else $status_id=0;
		  
		  if($status_id>0) $decorator->AddEntry(new SqlEntry('t.status_id',$status_id, SqlEntry::E));
		  $decorator->AddEntry(new UriEntry('status_id',$status_id));
		}
	}else{
		if((count($_GET)>1)&&($tab_page==2)) {
			$decorator->AddEntry(new UriEntry('status_all_5',0));	
			
			if(isset($_GET['status_id'.$prefix])){
				if($_GET['status_id'.$prefix]>0){
					$decorator->AddEntry(new SqlEntry('t.status_id',abs((int)$_GET['status_id'.$prefix]), SqlEntry::E));
				}
				$decorator->AddEntry(new UriEntry('status_id',$_GET['status_id'.$prefix]));
			}else{
			
			  if(isset($_COOKIE['task_status_id'])){
					  $status_id=$_COOKIE['task_status_id'];
			  }else $status_id=0;
			  
			  if($status_id>0) $decorator->AddEntry(new SqlEntry('t.status_id',$status_id, SqlEntry::E));
			  $decorator->AddEntry(new UriEntry('status_id',$status_id));
			}
			
		}else {
			$decorator->AddEntry(new UriEntry('status_all_5',1));	
			$decorator->AddEntry(new SqlEntry('t.status_id',5, SqlEntry::NE));
		}
	}
	
	
	if(isset($_GET['kind_id'.$prefix])){
		if($_GET['kind_id'.$prefix]>0){
			$decorator->AddEntry(new SqlEntry('t.kind_id',abs((int)$_GET['kind_id'.$prefix]), SqlEntry::E));
		}
		$decorator->AddEntry(new UriEntry('kind_id',$_GET['kind_id'.$prefix]));
	}
	
	
	//постановщик
	if(isset($_GET['user_name'.$prefix])&&(strlen($_GET['user_name'.$prefix])>0)){
		$decorator->AddEntry(new UriEntry('user_name',$_GET['user_name'.$prefix]));
		$decorator->AddEntry(new SqlEntry('name_s',SecStr($_GET['user_name'.$prefix]), SqlEntry::LIKE));
	}
	
	//к-т
	if(isset($_GET['supplier_name'.$prefix])&&(strlen($_GET['supplier_name'.$prefix])>0)){
		$decorator->AddEntry(new UriEntry('supplier_name',$_GET['supplier_name'.$prefix]));
		
		$decorator->AddEntry(new SqlEntry('t.id','select task_id from task_supplier where supplier_id in(select id from supplier where full_name like "%'.SecStr($_GET['supplier_name'.$prefix]).'%")', SqlEntry::IN_SQL));
	}
	
	//отв сотр
	if(isset($_GET['otv_user_name'.$prefix])&&(strlen($_GET['otv_user_name'.$prefix])>0)){
		$decorator->AddEntry(new UriEntry('otv_user_name',$_GET['otv_user_name'.$prefix]));
		$decorator->AddEntry(new SqlEntry('t.id','select task_id from task_user where user_id in(select id from user where name_s like "%'.SecStr($_GET['otv_user_name'.$prefix]).'%" or login like "%'.SecStr($_GET['otv_user_name'.$prefix]).'%")', SqlEntry::IN_SQL));
	}
	
	
	//сортировка
		if(!isset($_GET['sortmode'.$prefix])){
		$sortmode=3;	
	}else{
		$sortmode=abs((int)$_GET['sortmode'.$prefix]);
	}
	
	
	
	switch($sortmode){
		case 0:
			$decorator->AddEntry(new SqlOrdEntry('t.id',SqlOrdEntry::DESC));
		break;
		case 1:
			$decorator->AddEntry(new SqlOrdEntry('t.id',SqlOrdEntry::ASC));
		break;
		case 2:
			$decorator->AddEntry(new SqlOrdEntry('t.task_pdate',SqlOrdEntry::DESC));
			$decorator->AddEntry(new SqlOrdEntry('t.task_ptime',SqlOrdEntry::DESC));
		break;	
		case 3:
			$decorator->AddEntry(new SqlOrdEntry('t.task_pdate',SqlOrdEntry::ASC));
			$decorator->AddEntry(new SqlOrdEntry('t.task_ptime',SqlOrdEntry::ASC));
		break;
		case 4:
			$decorator->AddEntry(new SqlOrdEntry('t.pdate',SqlOrdEntry::DESC));
		break;
		case 5:
			$decorator->AddEntry(new SqlOrdEntry('t.pdate',SqlOrdEntry::ASC));
		break;	
		case 6:
			$decorator->AddEntry(new SqlOrdEntry('kind_name',SqlOrdEntry::DESC));
		break;
		case 7:
			$decorator->AddEntry(new SqlOrdEntry('kind_name',SqlOrdEntry::ASC));
		break;
		
		case 8:
			$decorator->AddEntry(new SqlOrdEntry('status_name',SqlOrdEntry::DESC));
		break;
		case 9:
			$decorator->AddEntry(new SqlOrdEntry('status_name',SqlOrdEntry::ASC));
		break;
		
		
		
		default:
			$decorator->AddEntry(new SqlOrdEntry('t.id',SqlOrdEntry::ASC));
		break;	
		
	}
	$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	
	
	
	
	
	$decorator->AddEntry(new UriEntry('tab_page',2));
	
	
	
	$ships=$_tg->ShowPos($result['id'], 'task/tasks.html', $decorator, false, $au->user_rights->CheckAccess('w',586), $alls, $from,$to_page);
	
	//$ships='–аздел находитс€ в разработке.';
	
	$res= $_tg->_thg->CountNewOrders($result['id']);
	if($res>0) $sm->assign('outcoming_tasks_count','('.$res.')');
		
	$sm->assign('tasks_2',$ships); 	
	
	



/**********************************************************************************************************/
//все задачи - только по правам
	if($au->user_rights->CheckAccess('w',589)){
		$_tg=new TaskAllGroup;
		$prefix=$_tg->prefix;
		
		
		//echo $_GET['from'.$prefix];
		
		
		if(isset($_GET['from'.$prefix])) $from=abs((int)$_GET['from'.$prefix]);
		else $from=0;
		
		if(isset($_GET['to_page'.$prefix])) $to_page=abs((int)$_GET['to_page'.$prefix]);
		else $to_page=ITEMS_PER_PAGE;
		
		$decorator=new DBDecorator;
	
	//є
	if(isset($_GET['id'.$prefix])&&(strlen($_GET['id'.$prefix])>0)){
		$decorator->AddEntry(new UriEntry('id',$_GET['id'.$prefix]));
		$decorator->AddEntry(new SqlEntry('t.id',abs((int)$_GET['id'.$prefix]), SqlEntry::LIKE));
	}
	
		
		//срок выполнени€
	if(!isset($_GET['pdate1'.$prefix])){
	
			$_pdate1=DateFromdmY(date("d.m.Y"))-60*60*24*30*3;
			$pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
		
	}else $pdate1 = $_GET['pdate1'.$prefix];
	
	
	
	if(!isset($_GET['pdate2'.$prefix])){
			
			$_pdate2=DateFromdmY(date("d.m.Y"))+60*60*24*30*3;
			$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
	}else $pdate2 = $_GET['pdate2'.$prefix];
	
	
	$decorator->AddEntry(new UriEntry('pdate1',$pdate1));
	$decorator->AddEntry(new UriEntry('pdate2',$pdate2));
	$decorator->AddEntry(new SqlEntry('t.task_pdate',DateYmd_from_dmY($pdate1), SqlEntry::BETWEEN,DateYmd_from_dmY($pdate2)));
	
	
	if(!isset($_GET['given_pdate1'.$prefix])){
	
			$_pdate1=DateFromdmY(date("d.m.Y"))-60*60*24*30*3;
			$pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
		
	}else $pdate1 = $_GET['given_pdate1'.$prefix];
	
	
	
	if(!isset($_GET['given_pdate2'.$prefix])){
			
			$_pdate2=DateFromdmY(date("d.m.Y"))+60*60*24;
			$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
	}else $pdate2 = $_GET['given_pdate2'.$prefix];
	
	
	$decorator->AddEntry(new UriEntry('given_pdate1',$pdate1));
	$decorator->AddEntry(new UriEntry('given_pdate2',$pdate2));
	$decorator->AddEntry(new SqlEntry('t.pdate',DateFromdmY($pdate1), SqlEntry::BETWEEN,DateFromdmY($pdate2)+60*60*24));
	
	
	
	if(isset($_GET['status_all_5'.$prefix])&&($_GET['status_all_5'.$prefix]==1)){
		$decorator->AddEntry(new SqlEntry('t.status_id',5, SqlEntry::NE));
		$decorator->AddEntry(new UriEntry('status_all_5',1));
	}elseif(isset($_GET['status_all_5'.$prefix])&&($_GET['status_all_5'.$prefix]==0)){
		if(isset($_GET['status_id'.$prefix])){
			if($_GET['status_id'.$prefix]>0){
				$decorator->AddEntry(new SqlEntry('t.status_id',abs((int)$_GET['status_id'.$prefix]), SqlEntry::E));
			}
			$decorator->AddEntry(new UriEntry('status_id',$_GET['status_id'.$prefix]));
		}else{
		
		  if(isset($_COOKIE['task_status_id'])){
				  $status_id=$_COOKIE['task_status_id'];
		  }else $status_id=0;
		  
		  if($status_id>0) $decorator->AddEntry(new SqlEntry('t.status_id',$status_id, SqlEntry::E));
		  $decorator->AddEntry(new UriEntry('status_id',$status_id));
		}
	}else{
		if((count($_GET)>1)&&($tab_page==3)) {
			$decorator->AddEntry(new UriEntry('status_all_5',0));	
			
			if(isset($_GET['status_id'.$prefix])){
				if($_GET['status_id'.$prefix]>0){
					$decorator->AddEntry(new SqlEntry('t.status_id',abs((int)$_GET['status_id'.$prefix]), SqlEntry::E));
				}
				$decorator->AddEntry(new UriEntry('status_id',$_GET['status_id'.$prefix]));
			}else{
			
			  if(isset($_COOKIE['task_status_id'])){
					  $status_id=$_COOKIE['task_status_id'];
			  }else $status_id=0;
			  
			  if($status_id>0) $decorator->AddEntry(new SqlEntry('t.status_id',$status_id, SqlEntry::E));
			  $decorator->AddEntry(new UriEntry('status_id',$status_id));
			}
			
		}else {
			$decorator->AddEntry(new UriEntry('status_all_5',1));	
			$decorator->AddEntry(new SqlEntry('t.status_id',5, SqlEntry::NE));
		}
	}
	
	
	
	if(isset($_GET['kind_id'.$prefix])){
		if($_GET['kind_id'.$prefix]>0){
			$decorator->AddEntry(new SqlEntry('t.kind_id',abs((int)$_GET['kind_id'.$prefix]), SqlEntry::E));
		}
		$decorator->AddEntry(new UriEntry('kind_id',$_GET['kind_id'.$prefix]));
	}
	
	
	//постановщик
	if(isset($_GET['user_name'.$prefix])&&(strlen($_GET['user_name'.$prefix])>0)){
		$decorator->AddEntry(new UriEntry('user_name',$_GET['user_name'.$prefix]));
		$decorator->AddEntry(new SqlEntry('name_s',SecStr($_GET['user_name'.$prefix]), SqlEntry::LIKE));
	}
	
	//к-т
	if(isset($_GET['supplier_name'.$prefix])&&(strlen($_GET['supplier_name'.$prefix])>0)){
		$decorator->AddEntry(new UriEntry('supplier_name',$_GET['supplier_name'.$prefix]));
		
		$decorator->AddEntry(new SqlEntry('t.id','select task_id from task_supplier where supplier_id in(select id from supplier where full_name like "%'.SecStr($_GET['supplier_name'.$prefix]).'%")', SqlEntry::IN_SQL));
	}
	
	//отв сотр
	if(isset($_GET['otv_user_name'.$prefix])&&(strlen($_GET['otv_user_name'.$prefix])>0)){
		$decorator->AddEntry(new UriEntry('otv_user_name',$_GET['otv_user_name'.$prefix]));
		$decorator->AddEntry(new SqlEntry('t.id','select task_id from task_user where user_id in(select id from user where name_s like "%'.SecStr($_GET['otv_user_name'.$prefix]).'%" or login like "%'.SecStr($_GET['otv_user_name'.$prefix]).'%")', SqlEntry::IN_SQL));
	}
	
	
	//сортировка
		if(!isset($_GET['sortmode'.$prefix])){
		$sortmode=3;	
	}else{
		$sortmode=abs((int)$_GET['sortmode'.$prefix]);
	}
	
	
	
	switch($sortmode){
		case 0:
			$decorator->AddEntry(new SqlOrdEntry('t.id',SqlOrdEntry::DESC));
		break;
		case 1:
			$decorator->AddEntry(new SqlOrdEntry('t.id',SqlOrdEntry::ASC));
		break;
		case 2:
			$decorator->AddEntry(new SqlOrdEntry('t.task_pdate',SqlOrdEntry::DESC));
			$decorator->AddEntry(new SqlOrdEntry('t.task_ptime',SqlOrdEntry::DESC));
		break;	
		case 3:
			$decorator->AddEntry(new SqlOrdEntry('t.task_pdate',SqlOrdEntry::ASC));
			$decorator->AddEntry(new SqlOrdEntry('t.task_ptime',SqlOrdEntry::ASC));
		break;
		case 4:
			$decorator->AddEntry(new SqlOrdEntry('t.pdate',SqlOrdEntry::DESC));
		break;
		case 5:
			$decorator->AddEntry(new SqlOrdEntry('t.pdate',SqlOrdEntry::ASC));
		break;	
		case 6:
			$decorator->AddEntry(new SqlOrdEntry('kind_name',SqlOrdEntry::DESC));
		break;
		case 7:
			$decorator->AddEntry(new SqlOrdEntry('kind_name',SqlOrdEntry::ASC));
		break;
		
		case 8:
			$decorator->AddEntry(new SqlOrdEntry('status_name',SqlOrdEntry::DESC));
		break;
		case 9:
			$decorator->AddEntry(new SqlOrdEntry('status_name',SqlOrdEntry::ASC));
		break;
		
		
		
		default:
			$decorator->AddEntry(new SqlOrdEntry('t.id',SqlOrdEntry::ASC));
		break;	
		
	}
	$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	
	
		
		
		
		$decorator->AddEntry(new UriEntry('tab_page',3));
		
		
		
		$ships=$_tg->ShowPos($result['id'], 'task/tasks.html', $decorator, false, $au->user_rights->CheckAccess('w',586), $alls, $from, $to_page);
		//$ships='–аздел находитс€ в разработке.';
		
			
		$sm->assign('tasks_all',$ships); 
		
		$res= $_tg->_thg->CountNewOrders($result['id']);
		if($res>0) $sm->assign('all_tasks_count','('.$res.')');
	
		
	}
	
	$sm->assign('has_all_tasks',  $au->user_rights->CheckAccess('w',589));
	






	
	
	
	
	
	

	
	
	$content=$sm->fetch('task/tasks_tabs.html');
	
	
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