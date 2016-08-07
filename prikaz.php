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

require_once('classes/prikazgroup.php');


$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Приказы');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}
//require_once('inc/restr.php');



if(!$au->user_rights->CheckAccess('w',805)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}

//журнал событий 
$log=new ActionLog;
$log->PutEntry($result['id'],'открыл раздел Приказы',NULL,805);

//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->display('top.html');
unset($smarty);


$_menu_id=8;
	include('inc/menu.php');
	
	
	
	//демонстрация страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	 
	
	$mog=new PrikazGroup;
	
	//Разбор переменных запроса
	if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
	else $from=0;
	
	if(isset($_GET['to_page'])) $to_page=abs((int)$_GET['to_page']);
	else $to_page=ITEMS_PER_PAGE;
	
	$decorator=new DBDecorator;
	
	
	
	if(!isset($_GET['pdate1'])){
	
			$_pdate1=DateFromdmY('01.12.2011'); //DateFromdmY(date("d.m.Y"))-60*60*24*30*12;
			$pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
		
	}else $pdate1 = $_GET['pdate1'];
	
	
	
	if(!isset($_GET['pdate2'])){
			
			$_pdate2=DateFromdmY(date("d.m.Y"))+60*60*24;
			$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
	}else $pdate2 = $_GET['pdate2'];
	
	$decorator->AddEntry(new SqlEntry('o.pdate',DateFromdmY($pdate1), SqlEntry::BETWEEN,DateFromdmY($pdate2)));
	$decorator->AddEntry(new UriEntry('pdate1',$pdate1));
	$decorator->AddEntry(new UriEntry('pdate2',$pdate2));
	
	if(isset($_GET['login'])&&(strlen($_GET['login'])>0)){
		$decorator->AddEntry(new SqlEntry('u.login',SecStr($_GET['login']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('login',$_GET['login']));
	}
	
	if(isset($_GET['vhod_no'])&&(strlen($_GET['vhod_no'])>0)){
		$decorator->AddEntry(new SqlEntry('u.vhod_no',SecStr($_GET['vhod_no']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('vhod_no',$_GET['vhod_no']));
	}
	
	/*
	if(isset($_GET['to_login'])&&(strlen($_GET['to_login'])>0)){
		$decorator->AddEntry(new SqlEntry('m.login',SecStr($_GET['to_login']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('to_login',$_GET['to_login']));
	}*/
	
	if(isset($_GET['id'])&&(strlen($_GET['id'])>0)){
		$decorator->AddEntry(new SqlEntry('o.id',abs((int)$_GET['id']), SqlEntry::E));
		$decorator->AddEntry(new UriEntry('id',$_GET['id']));
	}
	
	
	
	/*if(isset($_GET['status_all_11'])){
		$decorator->AddEntry(new SqlEntry('o.status_id',4, SqlEntry::NE));
		$decorator->AddEntry(new UriEntry('status_all_11',1));
	}else{
		if(count($_GET)>0) {
			$decorator->AddEntry(new UriEntry('status_all_11',0));	
			
			if(isset($_GET['status_id'])&&(strlen($_GET['status_id'])>0)){
				$decorator->AddEntry(new SqlEntry('o.status_id',abs((int)$_GET['status_id']), SqlEntry::E));
				$decorator->AddEntry(new UriEntry('status_id',$_GET['status_id']));
			}
			
		}else {
			$decorator->AddEntry(new UriEntry('status_all_11',1));	
			$decorator->AddEntry(new SqlEntry('o.status_id',4, SqlEntry::NE));
		}
	}*/
	
	/*if(isset($_GET['kind_id'])&&(strlen($_GET['kind_id'])>0)){
		$decorator->AddEntry(new SqlEntry('o.kind_id',abs((int)$_GET['kind_id']), SqlEntry::E));
		$decorator->AddEntry(new UriEntry('kind_id',$_GET['kind_id']));
	}
	
	
	if(isset($_GET['summ'])&&(strlen($_GET['summ'])>0)){
		$decorator->AddEntry(new SqlEntry('o.summ',abs((int)$_GET['summ']), SqlEntry::E));
		$decorator->AddEntry(new UriEntry('summ',$_GET['summ']));
	}*/
	
	//сортировку можно подписать как дополнительный параметр для UriEntry
	if(!isset($_GET['sortmode'])){
		$sortmode=0;	
	}else{
		$sortmode=abs((int)$_GET['sortmode']);
	}
	
	
	switch($sortmode){
		case 0:
			$decorator->AddEntry(new SqlOrdEntry('o.pdate',SqlOrdEntry::DESC));
		break;
		case 1:
			$decorator->AddEntry(new SqlOrdEntry('o.pdate',SqlOrdEntry::ASC));
		break;
		
		case 2:
			$decorator->AddEntry(new SqlOrdEntry('o.id',SqlOrdEntry::DESC));
		break;
		case 3:
			$decorator->AddEntry(new SqlOrdEntry('o.id',SqlOrdEntry::ASC));
		break;	
		case 4:
			$decorator->AddEntry(new SqlOrdEntry('o.vhod_no',SqlOrdEntry::DESC));
		break;
		case 5:
			$decorator->AddEntry(new SqlOrdEntry('o.vhod_no',SqlOrdEntry::ASC));
		break;
		
		
		default:
			$decorator->AddEntry(new SqlOrdEntry('o.pdate',SqlOrdEntry::DESC));
		break;	
		
	}
	$decorator->AddEntry(new SqlOrdEntry('id',SqlOrdEntry::DESC));
	
	$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	
	$decorator->AddEntry(new UriEntry('to_page',$to_page));
	
	
	$llg=$mog->ShowData('prikaz/prikaz_list.html',$decorator,$from,$to_page,  $au->user_rights->CheckAccess('w',806),  $au->user_rights->CheckAccess('w',807),  $au->user_rights->CheckAccess('w',808)); //ShowOrders($result['id'], 'claim/s.html', $decorator, $from,$to_page, $au->user_rights->CheckAccess('w',52),$au->user_rights->CheckAccess('w',53));
	
	
	$sm->assign('log',$llg);
	
	
	
	
	$content=$sm->fetch('prikaz/prikaz_form.html');
	
	
	

	
	
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