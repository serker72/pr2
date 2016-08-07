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

require_once('classes/missiongroup.php');
require_once('classes/missionitem.php');




$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",' омандировки');

$au=new AuthUser();
$result=$au->Auth();

$log=new ActionLog;

if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

if(!$au->user_rights->CheckAccess('w',591)){
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

if(!isset($_GET['sortmode'])){
	if(!isset($_POST['sortmode'])){
		$sortmode=0;
	}else $sortmode=abs((int)$_POST['sortmode']); 
}else $sortmode=abs((int)$_GET['sortmode']);


//журнал событий 
$log=new ActionLog;
$log->PutEntry($result['id'],'открыл раздел  омандировки',NULL,591);



//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->display('top.html');
unset($smarty);


$_menu_id=54;
	include('inc/menu.php');
	
	
	
	//демонстраци€ страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	
	
	//вход€щие задачи
	$_tg=new MissionGroup;
	$prefix=$_tg->prefix;
	
	if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
	else $from=0;
	
	if(isset($_GET['to_page'])) $to_page=abs((int)$_GET['to_page']);
	else $to_page=ITEMS_PER_PAGE;
	
	$decorator=new DBDecorator;
	
	
	//є
	if(isset($_GET['id'.$prefix])&&(strlen($_GET['id'.$prefix])>0)){
		$decorator->AddEntry(new UriEntry('id',$_GET['id'.$prefix]));
		$decorator->AddEntry(new SqlEntry('t.id',abs((int)$_GET['id'.$prefix]), SqlEntry::LIKE));
	}
	
	
	
	
	
	
	
	//дата создани€
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
	$decorator->AddEntry(new SqlEntry('t.pdate',DateFromdmY($pdate1), SqlEntry::BETWEEN,DateFromdmY($pdate2)+60*60*24));
	
	
	
	//период 
	if(!isset($_GET['pdate_begin'.$prefix])){
	
			$_pdate_begin=DateFromdmY(date("d.m.Y"))-60*60*24*30*3;
			$pdate_begin=date("d.m.Y", $_pdate_begin);//"01.01.2006";
		
	}else $pdate_begin = $_GET['pdate_begin'.$prefix];
	
	
	
	if(!isset($_GET['pdate_end'.$prefix])){
			
			$_pdate_end=DateFromdmY(date("d.m.Y"))+60*60*24*30*3;
			$pdate_end=date("d.m.Y", $_pdate_end);//"01.01.2006";	
	}else $pdate_end = $_GET['pdate_end'.$prefix];
	
	
	$decorator->AddEntry(new UriEntry('pdate_begin',$pdate_begin));
	$decorator->AddEntry(new UriEntry('pdate_end',$pdate_end));
	
	
	
	
	
	//статус
	
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
		
		  if(isset($_COOKIE['mis_status_id'])){
				  $status_id=$_COOKIE['mis_status_id'];
		  }else $status_id=0;
		  
		  if($status_id>0) $decorator->AddEntry(new SqlEntry('t.status_id',$status_id, SqlEntry::E));
		  $decorator->AddEntry(new UriEntry('status_id',$status_id));
		}
	}else{
		
		
		if((count($_GET)>1)) {
			
			
			$decorator->AddEntry(new UriEntry('status_all_5',0));	
			
			if(isset($_GET['status_id'.$prefix])){
				if($_GET['status_id'.$prefix]>0){
					$decorator->AddEntry(new SqlEntry('t.status_id',abs((int)$_GET['status_id'.$prefix]), SqlEntry::E));
				}
				$decorator->AddEntry(new UriEntry('status_id',$_GET['status_id'.$prefix]));
			}else{
			
			  if(isset($_COOKIE['mis_status_id'])){
					  $status_id=$_COOKIE['mis_status_id'];
			  }else $status_id=0;
			  
			  if($status_id>0) $decorator->AddEntry(new SqlEntry('t.status_id',$status_id, SqlEntry::E));
			  $decorator->AddEntry(new UriEntry('status_id',$status_id));
			}
			
		}else {
			$decorator->AddEntry(new UriEntry('status_all_5',1));	
			$decorator->AddEntry(new SqlEntry('t.status_id',5, SqlEntry::NE));
		}
	}
	
	//к-т
	if(isset($_GET['supplier_name'.$prefix])&&(strlen($_GET['supplier_name'.$prefix])>0)){
		$decorator->AddEntry(new UriEntry('supplier_name',$_GET['supplier_name'.$prefix]));
		
		$decorator->AddEntry(new SqlEntry('supplier.full_name',SecStr($_GET['supplier_name'.$prefix]), SqlEntry::LIKE));
	}
	
	
	//город
	if(isset($_GET['city_name'.$prefix])&&(strlen($_GET['city_name'.$prefix])>0)){
		$decorator->AddEntry(new UriEntry('city_name',$_GET['city_name'.$prefix]));
		
		$decorator->AddEntry(new SqlEntry('city.name',SecStr($_GET['city_name'.$prefix]), SqlEntry::LIKE));
	}
	
	
	//ком сотр-к
	if(isset($_GET['otv_user_name'.$prefix])&&(strlen($_GET['otv_user_name'.$prefix])>0)){
		$decorator->AddEntry(new UriEntry('otv_user_name',$_GET['otv_user_name'.$prefix]));
		$decorator->AddEntry(new SqlEntry('su.name_s',SecStr($_GET['otv_user_name'.$prefix]), SqlEntry::LIKE));
	}
	
	
	//создал
	if(isset($_GET['user_name'.$prefix])&&(strlen($_GET['user_name'.$prefix])>0)){
		$decorator->AddEntry(new UriEntry('user_name',$_GET['user_name'.$prefix]));
		$decorator->AddEntry(new SqlEntry('u.name_s',SecStr($_GET['user_name'.$prefix]), SqlEntry::LIKE));
	}
	
	
	//сортировка
		if(!isset($_GET['sortmode'.$prefix])){
		$sortmode=0;	
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
			$decorator->AddEntry(new SqlOrdEntry('t.pdate_begin',SqlOrdEntry::DESC));
			$decorator->AddEntry(new SqlOrdEntry('t.pdate_end',SqlOrdEntry::DESC));
		break;	
		case 3:
			$decorator->AddEntry(new SqlOrdEntry('t.pdate_begin',SqlOrdEntry::ASC));
			$decorator->AddEntry(new SqlOrdEntry('t.pdate_end',SqlOrdEntry::ASC));
		break;
		case 4:
			$decorator->AddEntry(new SqlOrdEntry('supplier_full_name',SqlOrdEntry::DESC));
		break;
		case 5:
			$decorator->AddEntry(new SqlOrdEntry('supplier_full_name',SqlOrdEntry::ASC));
		break;	
		case 6:
			$decorator->AddEntry(new SqlOrdEntry('city_name',SqlOrdEntry::DESC));
		break;
		case 7:
			$decorator->AddEntry(new SqlOrdEntry('city_name',SqlOrdEntry::ASC));
		break;
		
		case 8:
			$decorator->AddEntry(new SqlOrdEntry('sent_name_s',SqlOrdEntry::DESC));
		break;
		case 9:
			$decorator->AddEntry(new SqlOrdEntry('sent_name_s',SqlOrdEntry::ASC));
		break;
		
		
		case 10:
			$decorator->AddEntry(new SqlOrdEntry('status_name',SqlOrdEntry::DESC));
		break;
		case 11:
			$decorator->AddEntry(new SqlOrdEntry('status_name',SqlOrdEntry::ASC));
		break;
		
		
		case 12:
			$decorator->AddEntry(new SqlOrdEntry('t.pdate',SqlOrdEntry::DESC));
		break;
		case 13:
			$decorator->AddEntry(new SqlOrdEntry('t.pdate',SqlOrdEntry::ASC));
		break;
		
		
		
		default:
			$decorator->AddEntry(new SqlOrdEntry('t.id',SqlOrdEntry::ASC));
		break;	
		
	}
	$decorator->AddEntry(new UriEntry('sortmode'.$prefix,$sortmode));
	
	
	
	
	//$decorator->AddEntry(new UriEntry('tab_page',1));
	
	
	//без спецправ - только свои командировки
	if(!$au->user_rights->CheckAccess('w',593)){
		$decorator->AddEntry(new SqlEntry('t.sent_user_id',$result['id'], SqlEntry::E));	
	}
	
	//$ships=$_tg->ShowPos($result['id'], 'mission/missions.html', Datefromdmy($pdate_begin), Datefromdmy($pdate_end), $decorator, false, $au->user_rights->CheckAccess('w',592),   $au->user_rights->CheckAccess('w',593),  $alls, $from,$to_page);
	$ships='GYDEX. ¬ работе!';
	
	//$res= $_tg->_thg->CountNewOrders($result['id']);
	//if($res>0) $sm->assign('incoming_tasks_count','('.$res.')');
		
	$sm->assign('tasks_1',$ships); 
	
	
	
	
	




	
	
	
	
	
	

	
	
	$content=$sm->fetch('mission/missions_tabs.html');
	
	
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