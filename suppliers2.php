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
require_once('classes/discr_table_group.php');
require_once('classes/discr_table_objects.php');
require_once('classes/actionlog.php');
require_once('classes/suppliersgroup2.php');

require_once('classes/supplier_to_user.php');

$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'–еестр контрагентов');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}


	if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
	elseif(isset($_SESSION['suppliers_from'])){
		$from=abs((int)$_SESSION['suppliers_from']);
	}else $from=0;
	$_SESSION['suppliers_from']=$from;




if(!isset($_GET['print'])){
	if(!isset($_POST['print'])){
		$print=0;
	}else $print=abs((int)$_POST['print']); 
}else $print=abs((int)$_GET['print']);

 


if(!$au->user_rights->CheckAccess('w',1)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}

$log=new ActionLog;
/*if($print==0){
	$log->PutEntry($result['id'],'открыл раздел  онтрагенты',NULL,91);
}else{
	$log->PutEntry($result['id'],'открыл раздел  онтрагенты: верси€ дл€ печати',NULL,91);
}*/


//  $smarty->display('top_print.html');
 

 
	
	
	//демонстраци€ стартовой страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	 
	
	
	if($print==0) $print_add='';
	else $print_add='_print';
	
	
	if(isset($_GET['to_page'])) $to_page=abs((int)$_GET['to_page']);
	else $to_page=ITEMS_PER_PAGE;
	
	
	
	
	$decorator=new DBDecorator;
	
	
	//только ћосква и ћќ
	 $decorator->AddEntry(new SqlEntry('p.id','select distinct supplier_id from  supplier_sprav_city where city_id=1 or city_id in (select id from sprav_city where region_id=62)', SqlEntry::IN_SQL));
	 
	
		$decorator->AddEntry(new SqlEntry('p.is_active',1, SqlEntry::E));
		$decorator->AddEntry(new UriEntry('is_active',1));
	
	
	if(!isset($_GET['sortmode'])){
		$sortmode=3;	
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
		
		case 12:
			$decorator->AddEntry(new SqlOrdEntry('u.name_s',SqlOrdEntry::DESC));
		break;
		case 13:
			$decorator->AddEntry(new SqlOrdEntry('u.name_s',SqlOrdEntry::ASC));
		break;
		
		
		case 14:
			$decorator->AddEntry(new SqlOrdEntry('sc.name',SqlOrdEntry::DESC));
		break;
		case 15:
			$decorator->AddEntry(new SqlOrdEntry('sc.name',SqlOrdEntry::ASC));
		break;
		
		
		case 16:
			$decorator->AddEntry(new SqlOrdEntry('sc1.name',SqlOrdEntry::DESC));
		break;
		case 17:
			$decorator->AddEntry(new SqlOrdEntry('sc1.name',SqlOrdEntry::ASC));
		break;
		
		case 18:
			$decorator->AddEntry(new SqlOrdEntry('crea.name_s',SqlOrdEntry::DESC));
		break;
		case 19:
			$decorator->AddEntry(new SqlOrdEntry('crea.name_s',SqlOrdEntry::ASC));
		break;
		
		default:
			$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::ASC));
		break;	
		
	}
	$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	
	$decorator->AddEntry(new UriEntry('to_page',$to_page));
	
	
	
	//ограничени€ по к-ту
	$limited_supplier=NULL;
	
	if($au->FltSupplier($result)){  
		 
		
		$_s_to_u=new SupplierToUser;
		$s_to_u=$_s_to_u->GetExtendedViewedUserIdsArr($result['id'], $result);
		$limited_supplier=$s_to_u['sector_ids'];
	}
	//var_dump($limited_supplier);


 
	
	
	$ug=new SuppliersGroup2;
	 $uug= $ug->GetItems('suppliers/suppliers_export.html',$decorator,0,1000000,false,$au->user_rights->CheckAccess('w',543), $limited_supplier,  $result, $au->user_rights->CheckAccess('w',914),$alls);
	
	echo $uug;
	
	
	 
	  echo $content;
	unset($smarty);

 
?>