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
require_once('classes/actionlog.php');
require_once('classes/orgsgroup.php');



$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Реестр организаций');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

	if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
	elseif(isset($_SESSION['org_from'])){
		$from=abs((int)$_SESSION['org_from']);
	}else $from=0;
	$_SESSION['org_from']=$from;


if(isset($_POST['doInp'])){
	if(!$au->user_rights->CheckAccess('x',117)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}
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
				
				if($state==1){
					$man->GrantAccess($regs[3], $regs[1], $regs[2]);
					$pro=$au->GetProfile();
					$log->PutEntry($pro['id'], "установил доступ ".$regs[1],$regs[3],$regs[2]);
					//PutEntry($user_subject_id, $description, $user_object_id=NULL, $object_id=NULL){
				}else{
					$man->RevokeAccess($regs[3], $regs[1], $regs[2]);
					$pro=$au->GetProfile();
					$log->PutEntry($pro['id'], "удалил доступ ".$regs[1],$regs[3],$regs[2]);
				}
				
			}
		}
	}
	
	header("Location: organizations.php");	
	die();
}


if(!$au->user_rights->CheckAccess('w',117)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}
$log=new ActionLog;
$log->PutEntry($result['id'],'открыл раздел Мои организации',NULL,117);

//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->display('top.html');
unset($smarty);

	$_menu_id=31;

	include('inc/menu.php');
	
	
	//демонстрация стартовой страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	//строим вкладку администрирования
	/*$sm->assign('has_admin',$au->user_rights->CheckAccess('x',120)||
							$au->user_rights->CheckAccess('x',121)||
							$au->user_rights->CheckAccess('x',122)||
							$au->user_rights->CheckAccess('x',123)||
							$au->user_rights->CheckAccess('x',124)||
							$au->user_rights->CheckAccess('x',117)
							);
	$dto=new DiscrTableObjects($result['id'],array('120','121','122','123','124','117'));
	$admin=$dto->Draw('organizations.php','admin/admin_objects.html');
	$sm->assign('admin',$admin);
	*/
	
	
	//Разбор переменных запроса
	/*if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
	else $from=0;*/
	
	if(isset($_GET['to_page'])) $to_page=abs((int)$_GET['to_page']);
	else $to_page=ITEMS_PER_PAGE;
	
	$decorator=new DBDecorator;
	
	if(isset($_GET['code'])&&(strlen($_GET['code'])>0)){
		$decorator->AddEntry(new SqlEntry('p.code',SecStr($_GET['code']), SqlEntry::E));
		$decorator->AddEntry(new UriEntry('code',$_GET['code']));
	}
	
	
	if(isset($_GET['is_active'])){
		$decorator->AddEntry(new SqlEntry('p.is_active',1, SqlEntry::E));
		$decorator->AddEntry(new UriEntry('is_active',1));
	}else{
		if(count($_GET)>0) $decorator->AddEntry(new UriEntry('is_active',0));	
		else {
			$decorator->AddEntry(new UriEntry('is_active',1));	
			$decorator->AddEntry(new SqlEntry('p.is_active',1, SqlEntry::E));
		}
	}
	
	
	if(isset($_GET['legal_address'])&&(strlen($_GET['legal_address'])>0)){
		$decorator->AddEntry(new SqlEntry('p.legal_address',SecStr($_GET['legal_address']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('legal_address',$_GET['legal_address']));
	}
	
	if(isset($_GET['inn'])&&(strlen($_GET['inn'])>0)){
		$decorator->AddEntry(new SqlEntry('p.inn',SecStr($_GET['inn']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('inn',$_GET['inn']));
	}
	
	if(isset($_GET['kpp'])&&(strlen($_GET['kpp'])>0)){
		$decorator->AddEntry(new SqlEntry('p.kpp',SecStr($_GET['kpp']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('kpp',$_GET['kpp']));
	}
	
	if(isset($_GET['full_name'])&&(strlen($_GET['full_name'])>0)){
		$decorator->AddEntry(new SqlEntry('p.full_name',SecStr($_GET['full_name']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('full_name',$_GET['full_name']));
	}
	
	
	if(!isset($_GET['sortmode'])){
		$sortmode=1;	
	}else{
		$sortmode=abs((int)$_GET['sortmode']);
	}
	
	
	switch($sortmode){
		case 0:
			$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::DESC));
		break;
		case 1:
			$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::ASC));
		break;
		case 2:
			$decorator->AddEntry(new SqlOrdEntry('p.full_name',SqlOrdEntry::DESC));
		break;	
		case 3:
			$decorator->AddEntry(new SqlOrdEntry('p.full_name',SqlOrdEntry::ASC));
		break;
		case 4:
			$decorator->AddEntry(new SqlOrdEntry('p.inn',SqlOrdEntry::DESC));
		break;
		case 5:
			$decorator->AddEntry(new SqlOrdEntry('p.inn',SqlOrdEntry::ASC));
		break;	
		case 6:
			$decorator->AddEntry(new SqlOrdEntry('p.legal_address',SqlOrdEntry::DESC));
		break;
		case 7:
			$decorator->AddEntry(new SqlOrdEntry('p.legal_address',SqlOrdEntry::ASC));
		break;
		
		case 8:
			$decorator->AddEntry(new SqlOrdEntry('p.kpp',SqlOrdEntry::DESC));
		break;
		case 9:
			$decorator->AddEntry(new SqlOrdEntry('p.kpp',SqlOrdEntry::ASC));
		break;
		
		case 10:
			$decorator->AddEntry(new SqlOrdEntry('opf_name',SqlOrdEntry::DESC));
		break;
		case 11:
			$decorator->AddEntry(new SqlOrdEntry('opf_name',SqlOrdEntry::ASC));
		break;
		
		default:
			$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::ASC));
		break;	
		
	}
	$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	
	$decorator->AddEntry(new UriEntry('to_page',$to_page));
	
	
	
	
	
	$ug=new OrgsGroup;
	$ug->SetAuthResult($result);
	$uug= $ug->GetItems('org/orgs.html',$decorator,$from,$to_page,
	$au->user_rights->CheckAccess('w',467)||$au->user_rights->CheckAccess('w',468),
	$au->user_rights->CheckAccess('w',821)
	);
	
	
	$sm->assign('users',$uug);
	$content=$sm->fetch('org/org_l_page.html');
	
	
	
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