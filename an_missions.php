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




require_once('classes/an_missions.php');




$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Отчет о командировках');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}


if(!isset($_GET['print'])){
	if(!isset($_POST['print'])){
		$print=0;
	}else $print=abs((int)$_POST['print']); 
}else $print=abs((int)$_GET['print']);



if($print!=0){
	if(!$au->user_rights->CheckAccess('w',718)){
	  header("HTTP/1.1 403 Forbidden");
	  header("Status: 403 Forbidden");
	  include("403.php");
	  die();	
	}
}


if(!isset($_GET['tab_page'])){
	if(!isset($_POST['tab_page'])){
		$tab_page=1;
	}else $tab_page=abs((int)$_POST['tab_page']); 
}else $tab_page=abs((int)$_GET['tab_page']);





$log=new ActionLog;


if(!$au->user_rights->CheckAccess('w',717)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}


$log->PutEntry($result['id'],'перешел в Отчет о командировках',NULL,717,NULL,NULL);	


//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

if($print==0) $smarty->display('top.html');
else $smarty->display('top_print.html');
unset($smarty);


$_menu_id=55;

	if($print==0) include('inc/menu.php');
	
	
	//демонстрация страницы
	$smarty = new SmartyAdm;
	



	
	
	$sm=new SmartyAdm;
	
	
	$as=new AnMissions;
	$prefix=$as->prefix;
	
	$decorator=new DBDecorator;
	
	if($print==0) $print_add='';
	else $print_add='_print';

	
	
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
	
	if(isset($_GET['current_month'.$prefix])&&($_GET['current_month'.$prefix]==1)){
		$current_month=1;
		$decorator->AddEntry(new UriEntry('current_month',1));
	}else{
		$current_month=0;
		$decorator->AddEntry(new UriEntry('current_month',0));
	}
	
	
	if(isset($_GET['only_excess'.$prefix])&&($_GET['only_excess'.$prefix]==1)){
		$only_excess=1;
		$decorator->AddEntry(new UriEntry('only_excess',1));
	}else{
		$only_excess=0;
		$decorator->AddEntry(new UriEntry('only_excess',0));
	}
	
	if(isset($_GET['only_no_excess'.$prefix])&&($_GET['only_no_excess'.$prefix]==1)){
		$only_no_excess=1;
		$decorator->AddEntry(new UriEntry('only_no_excess',1));
	}else{
		$only_no_excess=0;
		$decorator->AddEntry(new UriEntry('only_no_excess',0));
	}
	
	
	//поставщик
	
	if(isset($_GET['supplier_name'.$prefix])&&(strlen($_GET['supplier_name'.$prefix])>0)){
		if(isset($_GET['print'])&&($_GET['print']==1)){
		  $decorator->AddEntry(new SqlEntry('supplier.full_name',NULL, SqlEntry::LIKE_SET, NULL, explode(';',SecStr(iconv("utf-8","windows-1251",$_GET['supplier_name'.$prefix]))))); 
		  $decorator->AddEntry(new UriEntry('supplier_name',iconv("utf-8","windows-1251",$_GET['supplier_name'.$prefix])));
		}else{
		  $decorator->AddEntry(new SqlEntry('supplier.full_name',NULL, SqlEntry::LIKE_SET, NULL, explode(';',SecStr($_GET['supplier_name'.$prefix])))); 
		  $decorator->AddEntry(new UriEntry('supplier_name',$_GET['supplier_name'.$prefix]));
			
		}
	}
	
	//сотрудник
	if(isset($_GET['manager_name'.$prefix])&&(strlen($_GET['manager_name'.$prefix])>0)){
		if(isset($_GET['print'])&&($_GET['print']==1)){
		
		  $decorator->AddEntry(new SqlEntry('su.name_s',NULL, SqlEntry::LIKE_SET, NULL, explode(';',SecStr(iconv("utf-8","windows-1251",$_GET['manager_name'.$prefix]))))); 
		  $decorator->AddEntry(new UriEntry('manager_name',iconv("utf-8","windows-1251",$_GET['manager_name'.$prefix])));
		}else{
		  $decorator->AddEntry(new SqlEntry('su.name_s',NULL, SqlEntry::LIKE_SET, NULL, explode(';',SecStr($_GET['manager_name'.$prefix])))); 
		  $decorator->AddEntry(new UriEntry('manager_name',$_GET['manager_name'.$prefix]));

		}
	}
	
	//город
	if(isset($_GET['city_name'.$prefix])&&(strlen($_GET['city_name'.$prefix])>0)){
		if(isset($_GET['print'])&&($_GET['print']==1)){
		  $decorator->AddEntry(new SqlEntry('city.name',NULL, SqlEntry::LIKE_SET, NULL, explode(';',SecStr(iconv("utf-8","windows-1251",$_GET['city_name'.$prefix]))))); 
		  $decorator->AddEntry(new UriEntry('city_name',iconv("utf-8","windows-1251",$_GET['city_name'.$prefix])));
		}else{
		 $decorator->AddEntry(new SqlEntry('city.name',NULL, SqlEntry::LIKE_SET, NULL, explode(';',SecStr($_GET['city_name'.$prefix])))); 
		  $decorator->AddEntry(new UriEntry('city_name',$_GET['city_name'.$prefix]));

		}
	}
	
	
	if(isset($_GET['only_no_excess'.$prefix])&&($_GET['only_no_excess'.$prefix]==1)){
		$only_no_excess=1;
		$decorator->AddEntry(new UriEntry('only_no_excess',1));
	}else{
		$only_no_excess=0;
		$decorator->AddEntry(new UriEntry('only_no_excess',0));
	}
	
	
	
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
		}
	}else{
		
		
		if((count($_GET)>1)) {
			
			
			$decorator->AddEntry(new UriEntry('status_all_5',0));	
			
			if(isset($_GET['status_id'.$prefix])){
				if($_GET['status_id'.$prefix]>0){
					$decorator->AddEntry(new SqlEntry('t.status_id',abs((int)$_GET['status_id'.$prefix]), SqlEntry::E));
				}
				$decorator->AddEntry(new UriEntry('status_id',$_GET['status_id'.$prefix]));
			}
			
		}else {
			$decorator->AddEntry(new UriEntry('status_all_5',1));	
			$decorator->AddEntry(new SqlEntry('t.status_id',5, SqlEntry::NE));
		}
	}
	
	
	
	
	
	
	
	if(!isset($_GET['sortmode'.$prefix])){
		if(!isset($_POST['sortmode'.$prefix])){
			$sortmode=0;
		}else $sortmode=abs((int)$_POST['sortmode'.$prefix]); 
	}else $sortmode=abs((int)$_GET['sortmode'.$prefix]);

	$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	
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
	
	
	$decorator->AddEntry(new SqlOrdEntry('t.id',SqlOrdEntry::DESC));
	
	$decorator->AddEntry(new UriEntry('print',$_GET['print']));
	
	
	//$filetext=$as->ShowData( datefromdmy($pdate1), datefromdmy($pdate2), $current_month, $only_excess, $only_no_excess, $decorator, 'an_missions/an_missions'.$print_add.'.html','an_missions.php',  $au->user_rights->CheckAccess('w',718),isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])||($print==1));
	
	$filetext='GYDEX. В работе!
';
	
	
	$sm->assign('log',$filetext);
	

	
	
	//фиксировать открытие отчета
	if( isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])||($print==1)){
		$log->PutEntry($result['id'],'открыл отчет Оригиналы оригиналы договоров и учредительных документов',NULL,717,NULL, NULL);	
	}
	
	
	
	
	//общие поля
	
	$sm->assign('pdate', date("d.m.Y H:i:s"));
	//$username=$result['login'];
	$username=stripslashes($result['name_s']).' '.$username;	
	
	$sm->assign('username',$username);
	
	$sm->assign('tab_page',$tab_page);
	
	$content=$sm->fetch('an_missions/an_missions_form'.$print_add.'.html');
	
	
	
	
	
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