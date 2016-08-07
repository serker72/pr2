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
require_once('classes/fileitem.php');
require_once('classes/filegroup.php');


require_once('classes/an_weq_bills.php');
require_once('classes/an_weq_bills_in.php');


require_once('classes/an_waa_bills.php');
require_once('classes/an_waa_bills_in.php');


$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'ќтчет Ѕез автовыравнивани€, Ѕез автоаннулировани€');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

$log=new ActionLog;


if(!$au->user_rights->CheckAccess('w',97)&&!$au->user_rights->CheckAccess('w',606)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}

if(!isset($_GET['print'])){
	if(!isset($_POST['print'])){
		$print=0;
	}else $print=($_POST['print']); 
}else $print=($_GET['print']);




if($print!=0){
	if(!$au->user_rights->CheckAccess('w',97)&&!$au->user_rights->CheckAccess('w',606)){
	  header("HTTP/1.1 403 Forbidden");
	  header("Status: 403 Forbidden");
	  include("403.php");
	  die();	
	}
}


//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

if($print===0) $smarty->display('top.html');
else $smarty->display('top_print_alb.html');
unset($smarty);



	if($print===0) include('inc/menu.php');
	
	if($print===0) $print_add='';
	else $print_add='_print';
	
	
	//демонстраци€ страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;



//******************* вкладка автовыравнивание

	$sm2=new SmartyAdm;

	
	
	
	if(!isset($_GET['tab_page'])) $tab_page=1;
	else $tab_page=abs((int)$_GET['tab_page']);
	
	
	
	
/********************************************************************************************************/	
	//вход. счета без ав	
	
	$log=new AnWeqBillsIn;
	$prefix=$log->prefix;
	
	$log->SetPageName('an_waaq.php');
	
	$decorator=new DBDecorator;
	
	$decorator->AddEntry(new SqlEntry('p.org_id',abs((int)$result['org_id']), SqlEntry::E));
	
	
	if(!isset($_GET['sortmode'.$prefix])){
		$sortmode=0;	
	}else{
		$sortmode=abs((int)$_GET['sortmode'.$prefix]);
	}
	
	
	
	
	
	
	$decorator->AddEntry(new SqlEntry('p.status_id',NULL, SqlEntry::IN_VALUES,NULL, array(2,9)));
	
	
	
	
			$_pdate1=0;//DateFromdmY(date("d.m.Y"))-3*60*60*24*30;
			$pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
		
	
			$_pdate2=DateFromdmY(date("d.m.Y"))+60*60*24;
			$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
	
	$decorator->AddEntry(new SqlEntry('p.cannot_eq',1, SqlEntry::E));
	
	if(isset($_GET['code'.$prefix])&&(strlen($_GET['code'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('p.code',SecStr($_GET['code'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('code',$_GET['code'.$prefix]));
	}
	
	if(isset($_GET['supplier_bill_no'.$prefix])&&(strlen($_GET['supplier_bill_no'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('p.supplier_bill_no',SecStr($_GET['supplier_bill_no'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('supplier_bill_no',$_GET['supplier_bill_no'.$prefix]));
	}
	
	
	if(isset($_GET['supplier_name'.$prefix])&&(strlen($_GET['supplier_name'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('sp.full_name',SecStr($_GET['supplier_name'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('supplier_name',$_GET['supplier_name'.$prefix]));
		//$sortmode=5;
	}
	
	if(isset($_GET['manager_name'.$prefix])&&(strlen($_GET['manager_name'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('mn.name_s',SecStr($_GET['manager_name'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('manager_name',$_GET['manager_name'.$prefix]));
	}
	
	if(isset($_GET['utv_name'.$prefix])&&(strlen($_GET['utv_name'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('utv.name_s',SecStr($_GET['utv_name'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('utv_name',$_GET['utv_name'.$prefix]));
	}
	
	
	if(!isset($_GET['sortmode'.$prefix])){
		if(!isset($_POST['sortmode'.$prefix])){
			$sortmode=0;
		}else $sortmode=abs((int)$_POST['sortmode'.$prefix]); 
	}else $sortmode=abs((int)$_GET['sortmode'.$prefix]);
	
	
	//сортировку можно подписать как дополнительный параметр дл€ UriEntry
	
	
	switch($sortmode){
		case 0:
			$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
		break;
		case 1:
			$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::ASC));
		break;
		case 2:
			$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::DESC));
		break;	
		case 3:
			$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::ASC));
		break;
		
		case 4:
			$decorator->AddEntry(new SqlOrdEntry('supplier_name',SqlOrdEntry::DESC));
		break;	
		case 5:
			$decorator->AddEntry(new SqlOrdEntry('supplier_name',SqlOrdEntry::ASC));
		break;
	
		case 10:
			$decorator->AddEntry(new SqlOrdEntry('confirmed_price_name',SqlOrdEntry::DESC));
			
		break;	
		case 11:
			$decorator->AddEntry(new SqlOrdEntry('confirmed_price_name',SqlOrdEntry::ASC));
			
		break;
		
		default:
			$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
		break;	
		
	}
	
	$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	
	
	
	//echo 'an_weq/an_weq_form'.$print_add.'.html';
	$log->SetAuthResult($result);
	
	$llg=$log->ShowPos('an_weq/bills_list'.$print_add.'.html',$decorator,$from,$to_page, $au->user_rights->CheckAccess('w',128), $au->user_rights->CheckAccess('w',613)||$au->user_rights->CheckAccess('w',625), $au->user_rights->CheckAccess('w',626),  '2', $au->user_rights->CheckAccess('w',620), $au->user_rights->CheckAccess('w',96),true,false,$au->user_rights->CheckAccess('w',627),$limited_sector, NULL,$au->user_rights->CheckAccess('w',621),$au->user_rights->CheckAccess('w',622), $au->user_rights->CheckAccess('w',623), $au->user_rights->CheckAccess('w',624),	isset($_GET['doFilter'.$prefix])||isset($_GET['doFilter'.$prefix.'_x'])||(($print===$prefix)&&isset($_GET['tab_page'])&&($_GET['tab_page']==1))	);
	
	
	$sm2->assign('log',$llg);
	
	$sm2->assign('has_1',  $au->user_rights->CheckAccess('w',606));
	
	
	$sm2->assign('print',$print);
	
	

/********************************************************************************************************/	
	//исход счета без а/в
	//$sm2=new SmartyAdm;
	
	$log=new AnWeqBills;
	$prefix=$log->prefix;
	
	$log->SetPageName('an_waaq.php');
	
	$decorator=new DBDecorator;
	
	$decorator->AddEntry(new SqlEntry('p.org_id',abs((int)$result['org_id']), SqlEntry::E));
	
	
	if(!isset($_GET['sortmode'.$prefix])){
		$sortmode=0;	
	}else{
		$sortmode=abs((int)$_GET['sortmode'.$prefix]);
	}
	
	
	
	
	
	
	$decorator->AddEntry(new SqlEntry('p.status_id',NULL, SqlEntry::IN_VALUES,NULL, array(2,9)));
	
	
	
	
			$_pdate1=0;//DateFromdmY(date("d.m.Y"))-3*60*60*24*30;
			$pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
		
	
			$_pdate2=DateFromdmY(date("d.m.Y"))+60*60*24;
			$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
	
	$decorator->AddEntry(new SqlEntry('p.cannot_eq',1, SqlEntry::E));
	
	if(isset($_GET['code'.$prefix])&&(strlen($_GET['code'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('p.code',SecStr($_GET['code'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('code',$_GET['code'.$prefix]));
	}
	
	if(isset($_GET['supplier_bill_no'.$prefix])&&(strlen($_GET['supplier_bill_no'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('p.supplier_bill_no',SecStr($_GET['supplier_bill_no'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('supplier_bill_no',$_GET['supplier_bill_no'.$prefix]));
	}
	
	
	if(isset($_GET['supplier_name'.$prefix])&&(strlen($_GET['supplier_name'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('sp.full_name',SecStr($_GET['supplier_name'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('supplier_name',$_GET['supplier_name'.$prefix]));
		//$sortmode=5;
	}
	
	if(isset($_GET['manager_name'.$prefix])&&(strlen($_GET['manager_name'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('mn.name_s',SecStr($_GET['manager_name'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('manager_name',$_GET['manager_name'.$prefix]));
	}
	
	if(isset($_GET['utv_name'.$prefix])&&(strlen($_GET['utv_name'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('utv.name_s',SecStr($_GET['utv_name'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('utv_name',$_GET['utv_name'.$prefix]));
	}
	
	
	if(!isset($_GET['sortmode'.$prefix])){
		if(!isset($_POST['sortmode'.$prefix])){
			$sortmode=0;
		}else $sortmode=abs((int)$_POST['sortmode'.$prefix]); 
	}else $sortmode=abs((int)$_GET['sortmode'.$prefix]);
	
	
	//сортировку можно подписать как дополнительный параметр дл€ UriEntry
	
	
	switch($sortmode){
		case 0:
			$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
		break;
		case 1:
			$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::ASC));
		break;
		case 2:
			$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::DESC));
		break;	
		case 3:
			$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::ASC));
		break;
		
		case 4:
			$decorator->AddEntry(new SqlOrdEntry('supplier_name',SqlOrdEntry::DESC));
		break;	
		case 5:
			$decorator->AddEntry(new SqlOrdEntry('supplier_name',SqlOrdEntry::ASC));
		break;
	
		case 10:
			$decorator->AddEntry(new SqlOrdEntry('confirmed_price_name',SqlOrdEntry::DESC));
			
		break;	
		case 11:
			$decorator->AddEntry(new SqlOrdEntry('confirmed_price_name',SqlOrdEntry::ASC));
			
		break;
		
		default:
			$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
		break;	
		
	}
	
	$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	
	
	
	//echo 'an_weq/an_weq_form'.$print_add.'.html';
	$log->SetAuthResult($result);
	
	$llg=$log->ShowPos('an_weq/bills_list'.$print_add.'.html',$decorator,$from,$to_page, $au->user_rights->CheckAccess('w',128), $au->user_rights->CheckAccess('w',93)||$au->user_rights->CheckAccess('w',283), $au->user_rights->CheckAccess('w',94),  '2', $au->user_rights->CheckAccess('w',95), $au->user_rights->CheckAccess('w',96),true,false,$au->user_rights->CheckAccess('w',131),$limited_sector, NULL,$au->user_rights->CheckAccess('w',195),$au->user_rights->CheckAccess('w',196), $au->user_rights->CheckAccess('w',197), $au->user_rights->CheckAccess('w',292),	isset($_GET['doFilter'.$prefix])||isset($_GET['doFilter'.$prefix.'_x'])||(($print===$prefix)&&isset($_GET['tab_page'])&&($_GET['tab_page']==1))	);
	
	
	$sm2->assign('log2',$llg);
	
	$sm2->assign('has_2',  $au->user_rights->CheckAccess('w',97));
	
	
	$sm2->assign('print',$print);
	
	
	//вносим все в общую форму вкладок дл€ счетов
	
	$c1=$sm2->fetch('an_weq/an_weq_form'.$print_add.'.html');
	
	$sm->assign('log',$c1);
	
	
	
	
	
	
	
	
	
//*************** без а/а ********************************************************

	$sm2=new SmartyAdm;
	
//********************************************************************************************************	
	// вход счета без аа	
	
	$log=new AnWaaBillsIn;
	$log->SetPageName('an_waaq.php');
	$prefix=$log->prefix;
	
	
	$decorator=new DBDecorator;
	
	$decorator->AddEntry(new SqlEntry('p.org_id',abs((int)$result['org_id']), SqlEntry::E));
	
	
	
	if(!isset($_GET['sortmode'.$prefix])){
		$sortmode=0;	
	}else{
		$sortmode=abs((int)$_GET['sortmode'.$prefix]);
	}
	
	
	
	
	
			$_pdate1=0;//DateFromdmY(date("d.m.Y"))-3*60*60*24*30;
			$pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
		
	
			$_pdate2=DateFromdmY(date("d.m.Y"))+60*60*24;
			$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
	
	$decorator->AddEntry(new SqlEntry('p.cannot_an',1, SqlEntry::E));
	
	if(isset($_GET['code'.$prefix])&&(strlen($_GET['code'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('p.code',SecStr($_GET['code'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('code',$_GET['code'.$prefix]));
	}
	
	if(isset($_GET['supplier_bill_no'.$prefix])&&(strlen($_GET['supplier_bill_no'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('p.supplier_bill_no',SecStr($_GET['supplier_bill_no'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('supplier_bill_no',$_GET['supplier_bill_no'.$prefix]));
	}
	
	
	
	if(isset($_GET['supplier_name'.$prefix])&&(strlen($_GET['supplier_name'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('sp.full_name',SecStr($_GET['supplier_name'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('supplier_name',$_GET['supplier_name'.$prefix]));
		//$sortmode=5;
	}
	
	if(isset($_GET['manager_name'.$prefix])&&(strlen($_GET['manager_name'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('mn.name_s',SecStr($_GET['manager_name'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('manager_name',$_GET['manager_name'.$prefix]));
	}
	
	
	if(isset($_GET['utv_name'.$prefix])&&(strlen($_GET['utv_name'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('utv.name_s',SecStr($_GET['utv_name'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('utv_name',$_GET['utv_name'.$prefix]));
	}
	
	
	
	
	//сортировку можно подписать как дополнительный параметр дл€ UriEntry
	
	
	switch($sortmode){
		case 0:
			$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
		break;
		case 1:
			$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::ASC));
		break;
		case 2:
			$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::DESC));
		break;	
		case 3:
			$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::ASC));
		break;
		
		case 4:
			$decorator->AddEntry(new SqlOrdEntry('supplier_name',SqlOrdEntry::DESC));
		break;	
		case 5:
			$decorator->AddEntry(new SqlOrdEntry('supplier_name',SqlOrdEntry::ASC));
		break;
		
		
		case 10:
			$decorator->AddEntry(new SqlOrdEntry('confirmed_price_name',SqlOrdEntry::DESC));
			
		break;	
		case 11:
			$decorator->AddEntry(new SqlOrdEntry('confirmed_price_name',SqlOrdEntry::ASC));
			
		break;
		
		default:
			$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
		break;	
		
	}
	
	$decorator->AddEntry(new UriEntry('tab_page',2));
	
	$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	
		
	
	$log->SetAuthResult($result);
	
	$llg=$log->ShowPos('an_waa/bills_list'.$print_add.'.html',$decorator,$from,$to_page, $au->user_rights->CheckAccess('w',128), $au->user_rights->CheckAccess('w',613)||$au->user_rights->CheckAccess('w',625), $au->user_rights->CheckAccess('w',626),  '4', $au->user_rights->CheckAccess('w',620), $au->user_rights->CheckAccess('w',96),true,false,$au->user_rights->CheckAccess('w',627),$limited_sector,NULL,$au->user_rights->CheckAccess('w',621),$au->user_rights->CheckAccess('w',622), $au->user_rights->CheckAccess('w',623), $au->user_rights->CheckAccess('w',624),	isset($_GET['doFilter'.$prefix])||isset($_GET['doFilter'.$prefix.'_x'])||(($print===$prefix)&&isset($_GET['tab_page'])&&($_GET['tab_page']==2))	);
	
	$sm2->assign('print',$print);
	$sm2->assign('log',$llg);
	
	
	$sm2->assign('has_1',  $au->user_rights->CheckAccess('w',606));
	

//!!!!!!! исход счета без а/а ****************************************************************************************
	
	$log=new AnWaaBills;
	$log->SetPageName('an_waaq.php');
	$prefix=$log->prefix;
	
	
	$decorator=new DBDecorator;
	
	$decorator->AddEntry(new SqlEntry('p.org_id',abs((int)$result['org_id']), SqlEntry::E));
	
	
	
	if(!isset($_GET['sortmode'.$prefix])){
		$sortmode=0;	
	}else{
		$sortmode=abs((int)$_GET['sortmode'.$prefix]);
	}
	
	
	
	
	
			$_pdate1=0;//DateFromdmY(date("d.m.Y"))-3*60*60*24*30;
			$pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
		
	
			$_pdate2=DateFromdmY(date("d.m.Y"))+60*60*24;
			$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
	
	$decorator->AddEntry(new SqlEntry('p.cannot_an',1, SqlEntry::E));
	
	if(isset($_GET['code'.$prefix])&&(strlen($_GET['code'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('p.code',SecStr($_GET['code'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('code',$_GET['code'.$prefix]));
	}
	
	if(isset($_GET['supplier_bill_no'.$prefix])&&(strlen($_GET['supplier_bill_no'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('p.supplier_bill_no',SecStr($_GET['supplier_bill_no'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('supplier_bill_no',$_GET['supplier_bill_no'.$prefix]));
	}
	
	
	
	if(isset($_GET['supplier_name'.$prefix])&&(strlen($_GET['supplier_name'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('sp.full_name',SecStr($_GET['supplier_name'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('supplier_name',$_GET['supplier_name'.$prefix]));
		//$sortmode=5;
	}
	
	if(isset($_GET['manager_name'.$prefix])&&(strlen($_GET['manager_name'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('mn.name_s',SecStr($_GET['manager_name'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('manager_name',$_GET['manager_name'.$prefix]));
	}
	
	
	if(isset($_GET['utv_name'.$prefix])&&(strlen($_GET['utv_name'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('utv.name_s',SecStr($_GET['utv_name'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('utv_name',$_GET['utv_name'.$prefix]));
	}
	
	
	
	
	//сортировку можно подписать как дополнительный параметр дл€ UriEntry
	
	
	switch($sortmode){
		case 0:
			$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
		break;
		case 1:
			$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::ASC));
		break;
		case 2:
			$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::DESC));
		break;	
		case 3:
			$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::ASC));
		break;
		
		case 4:
			$decorator->AddEntry(new SqlOrdEntry('supplier_name',SqlOrdEntry::DESC));
		break;	
		case 5:
			$decorator->AddEntry(new SqlOrdEntry('supplier_name',SqlOrdEntry::ASC));
		break;
		
		
		case 10:
			$decorator->AddEntry(new SqlOrdEntry('confirmed_price_name',SqlOrdEntry::DESC));
			
		break;	
		case 11:
			$decorator->AddEntry(new SqlOrdEntry('confirmed_price_name',SqlOrdEntry::ASC));
			
		break;
		
		default:
			$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
		break;	
		
	}
	
	$decorator->AddEntry(new UriEntry('tab_page',2));
	
	$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	
		
	
	$log->SetAuthResult($result);
	
	$llg=$log->ShowPos('an_waa/bills_list'.$print_add.'.html',$decorator,$from,$to_page, $au->user_rights->CheckAccess('w',128), $au->user_rights->CheckAccess('w',93)||$au->user_rights->CheckAccess('w',283), $au->user_rights->CheckAccess('w',94),  '4', $au->user_rights->CheckAccess('w',95), $au->user_rights->CheckAccess('w',96),true,false,$au->user_rights->CheckAccess('w',131),$limited_sector,NULL,$au->user_rights->CheckAccess('w',195),$au->user_rights->CheckAccess('w',196), $au->user_rights->CheckAccess('w',197), $au->user_rights->CheckAccess('w',292),	isset($_GET['doFilter'.$prefix])||isset($_GET['doFilter'.$prefix.'_x'])||(($print===$prefix)&&isset($_GET['tab_page'])&&($_GET['tab_page']==2))	);
	
	$sm2->assign('print',$print);
	$sm2->assign('log2',$llg);
	
	
	$sm2->assign('has_2',  $au->user_rights->CheckAccess('w',97));
	
	
	
	
	
	$sm2->assign('print',$print);
	
	$c2=$sm2->fetch('an_waa/an_waa_form'.$print_add.'.html');
	
	$sm->assign('log2',$c2);
		
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
//**************************************** общие пол€ *********************************************
	
	$sm->assign('pdate', date("d.m.Y H:i:s"));
	//$username=$result['login'];
	$username=stripslashes($result['name_s']).' '.$username;	
	
	$sm->assign('username',$username);
	
	$sm->assign('tab_page',$tab_page);
	
	
	$content=$sm->fetch('an_waaq/an_waaq_form'.$print_add.'.html');
	
	
	
	
	$smarty->assign('fast_menu', $menu_arr_fast);
	  $smarty->assign('main_menu',$menu_res);
	$smarty->assign('content',$content);
	if($print===0) $smarty->display('page.html');
	else echo $content;
	unset($smarty);


$smarty = new SmartyAdm;

//работа с футером
require_once('inc/footer.php');
if(isset($footer_res)){
	$smarty->assign('footer',$footer_res);
}else $smarty->assign('footer','');

if($print===0) $smarty->display('bottom.html');
else $smarty->display('bottom_print.html');
unset($smarty);
?>