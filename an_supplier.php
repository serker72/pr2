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
/*require_once('classes/fileitem.php');
require_once('classes/filegroup.php');
*/
require_once('classes/an_supplier.php');




$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Ведомость по контрагентам');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

$log=new ActionLog;



if(!$au->user_rights->CheckAccess('w',111)&&!$au->user_rights->CheckAccess('w',358)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}


if(!isset($_GET['print'])){
	if(!isset($_POST['print'])){
		$print=0;
	}else $print=abs((int)$_POST['print']); 
}else $print=abs((int)$_GET['print']);

if($print!=0){
	if(!$au->user_rights->CheckAccess('w',358)){
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

if($print==0) $smarty->display('top.html');
else $smarty->display('top_print.html');
unset($smarty);


	if($print==0) include('inc/menu.php');
	
	
	
	//демонстрация страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	
	
	//декоратор используем для многостраничности (если понадобится)
	$decorator=new DBDecorator;
	
	
	if($print==0) $print_add='';
	else $print_add='_print';
	
	if(!isset($_GET['pdate1'])){
	
			$_pdate1=DateFromdmY(date("d.m.Y"))-60*60*24*30*3;
			$pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
		
	}else $pdate1 = $_GET['pdate1'];
	
	
	
	if(!isset($_GET['pdate2'])){
			
			$_pdate2=DateFromdmY(date("d.m.Y"))+60*60*24;
			$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
	}else $pdate2 = $_GET['pdate2'];
	
	
	$decorator->AddEntry(new UriEntry('pdate1',$pdate1));
	$decorator->AddEntry(new UriEntry('pdate2',$pdate2));
	
	
	
	
	
	if(isset($_GET['supplier_name'])&&(strlen($_GET['supplier_name'])>0)){
		
		if(isset($_GET['print'])&&($_GET['print']==1)){
			 $supplier_name=SecStr(iconv("utf-8","windows-1251",$_GET['supplier_name']));
			 $decorator->AddEntry(new UriEntry('supplier_name',iconv("utf-8","windows-1251",$_GET['supplier_name'])));
		}else{
			 $supplier_name=SecStr($_GET['supplier_name']);
			 $decorator->AddEntry(new UriEntry('supplier_name',$_GET['supplier_name']));
		}
	}else $supplier_name='';

	//var_dump($supplier_name);
	
	if(isset($_GET['extended_an'])&&($_GET['extended_an']==1)){
		$extended_an=1;
		$decorator->AddEntry(new UriEntry('extended_an',1));	
			
	}else{
		$extended_an=0;
		$decorator->AddEntry(new UriEntry('extended_an',0));	
	}
	
	if(isset($_GET['similar_firms'])&&($_GET['similar_firms']==1)){
		$similar_firms=1;
		$decorator->AddEntry(new UriEntry('similar_firms',1));	
			
	}else{
		$similar_firms=0;
		$decorator->AddEntry(new UriEntry('similar_firms',0));	
	}
	
	
	if(isset($_GET['by_contract'])&&($_GET['by_contract']==1)){
		$by_contract=1;
		$decorator->AddEntry(new UriEntry('by_contract',1));	
			
	}else{
		$by_contract=0;
		$decorator->AddEntry(new UriEntry('by_contract',0));	
	}
	
	
	
	$decorator->AddEntry(new UriEntry('print',$_GET['print']));
	$decorator->AddEntry(new UriEntry('tab_page',1));
	
	if($print==0){
		$template='an_supplier/an_supplier_list.html';	
	}else{
		if(isset($_GET['do_print_ved'])){
			$template='an_supplier/an_supplier_list'.$print_add.'.html';
		}elseif(isset($_GET['do_print_akt'])){
			$template='an_supplier/an_supplier_list_sverka.html';
		}else{
			$template='an_supplier/an_supplier_list'.$print_add.'.html';	
		}
	}
	
	
	$as=new AnSupplier;
	$filetext=$as->ShowData($supplier_name, $result['org_id'], DateFromdmY($pdate1), DateFromdmY($pdate2), $extended_an, $template,$decorator,'an_supplier.php', isset($_GET['doSub'])||isset($_GET['doSub_x'])||($print==1),$au->user_rights->CheckAccess('w',358), DEC_SEP, $similar_firms, $by_contract );
	
	
	
	$sm->assign('log',$filetext);
	
	
	
	//общие поля
	
	$sm->assign('pdate', date("d.m.Y H:i:s"));
	//$username=$result['login'];
	$username=stripslashes($result['name_s']).' '.$username;	
	
	$sm->assign('username',$username);
	
	$sm->assign('tab_page',$tab_page);
	
	
	
	$content=$sm->fetch('an_supplier/an_supplier_form'.$print_add.'.html');
	
	
	$smarty->assign('fast_menu', $menu_arr_fast);
	  $smarty->assign('main_menu',$menu_res);
	$smarty->assign('content',$content);
	if($print==0) $smarty->display('page.html');
	else echo $content;
	unset($smarty);


$smarty = new SmartyAdm;

//работа с футером
require_once('inc/footer.php');
if(isset($footer_res)){
	$smarty->assign('footer',$footer_res);
}else $smarty->assign('footer','');

if($print==0) $smarty->display('bottom.html');
else $smarty->display('bottom_print.html');
unset($smarty);
?>