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



require_once('classes/an_pl_changes.class.php');


require_once('classes/user_to_user.php');


$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Отчет Изменения в прайс-листе');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

$log=new ActionLog;



if(!$au->user_rights->CheckAccess('w',766)/*&&!$au->user_rights->CheckAccess('w',764)*/){
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

if(!isset($_GET['sortmode'])){
	if(!isset($_POST['sortmode'])){
		$sortmode=0;
	}else $sortmode=abs((int)$_POST['sortmode']); 
}else $sortmode=abs((int)$_GET['sortmode']);

if(!isset($_GET['sortmode2'])){
	if(!isset($_POST['sortmode2'])){
		$sortmode2=0;
	}else $sortmode2=abs((int)$_POST['sortmode2']); 
}else $sortmode2=abs((int)$_GET['sortmode2']);

if($print!=0){
	if(!$au->user_rights->CheckAccess('w',767)/*&&!$au->user_rights->CheckAccess('w',765)*/){
	  header("HTTP/1.1 403 Forbidden");
	  header("Status: 403 Forbidden");
	  include("403.php");
	  die();	
	}
}

$log->PutEntry($result['id'],'перешел в Отчет Изменения в прайс-листе',NULL,766,NULL,NULL);	


//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->assign('do_restrict', !in_array($result['id'], array(1,2,3))&&($print==1));

if($print==0) $smarty->display('top.html');
else $smarty->display('top_print.html');
unset($smarty);

$_menu_id=63;


	if($print==0) include('inc/menu.php');
	
	if($print==0) $print_add='';
	else $print_add='_print';
	
	//демонстрация страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	
	if(!isset($_GET['tab_page'])) $tab_page=1;
	else $tab_page=abs((int)$_GET['tab_page']);
	
	
	
//******************************************************************************************************************
//вкладка отчет КП
	
	
	$an=new AnPlChanges;
	$prefix='_1';
	$decorator=new DBDecorator;
	
	if($print==0) $print_add='';
	else $print_add='_print';

	$decorator->AddEntry(new UriEntry('print',$print));
	
	
	if(!isset($_GET['pdate1'.$prefix])){
	
			$_pdate1=DateFromdmY(date("d.m.Y"))-60*60*24*30*3;
			$pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
		
	}else $pdate1 = $_GET['pdate1'.$prefix];
	
	
	
	if(!isset($_GET['pdate2'.$prefix])){
			
			$_pdate2=DateFromdmY(date("d.m.Y"));//+60*60*24*30*3;
			$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
	}else $pdate2 = $_GET['pdate2'.$prefix];
	
	
	$decorator->AddEntry(new UriEntry('pdate1',$pdate1));
	$decorator->AddEntry(new UriEntry('pdate2',$pdate2));
	
	
	//вид кп
	
	/*if(isset($_GET['price_kind_id'.$prefix])&&(abs((int)$_GET['price_kind_id'.$prefix])>0)){
		$decorator->AddEntry(new UriEntry('price_kind_id',$_GET['price_kind_id'.$prefix]));
		$decorator->AddEntry(new SqlEntry('p.price_kind_id',SecStr($_GET['price_kind_id'.$prefix]), SqlEntry::E));		 
	} */
	
	
	//группа, подгруппа, пр-ль
	if(isset($_GET['group_id'.$prefix])&&(abs((int)$_GET['group_id'.$prefix])>0)){
		$decorator->AddEntry(new UriEntry('group_id',$_GET['group_id'.$prefix]));
		//$decorator->AddEntry(new SqlEntry('b.price_kind_id',SecStr($_GET['price_kind_id'.$prefix]), SqlEntry::E));		 
	} 
	
	if(isset($_GET['producer_id'.$prefix])&&(abs((int)$_GET['producer_id'.$prefix])>0)){
		$decorator->AddEntry(new UriEntry('producer_id',$_GET['producer_id'.$prefix]));
		$decorator->AddEntry(new SqlEntry('pos.producer_id',SecStr($_GET['producer_id'.$prefix]), SqlEntry::E));		 
	} 
	
	if(isset($_GET['two_group_id'.$prefix])&&(abs((int)$_GET['two_group_id'.$prefix])>0)){
		$decorator->AddEntry(new UriEntry('two_group_id',$_GET['two_group_id'.$prefix]));
		$decorator->AddEntry(new SqlEntry('pos.group_id',SecStr($_GET['two_group_id'.$prefix]), SqlEntry::E));		 
	}elseif(isset($_GET['group_id'.$prefix])&&(abs((int)$_GET['group_id'.$prefix])>0)){
		//$decorator->AddEntry(new SqlEntry('eq.group_id',SecStr($_GET['group_id'.$prefix]), SqlEntry::E));	
		
		$_pgg=new PosGroupGroup;
		$arr=$_pgg->GetItemsByIdArr(abs((int)$_GET['group_id'.$prefix]));
		$arg=array();
		foreach($arr as $k=>$v){
			if(!in_array($v['id'],$arg)) $arg[]=$v['id'];
			$arr2=$_pgg->GetItemsByIdArr($v['id']);
			foreach($arr2 as $kk=>$vv){
				if(!in_array($vv['id'],$arg))  $arg[]=$vv['id'];
			}
		}
		
		if(count($arg)>0){
			 
			$decorator->AddEntry(new SqlEntry('pos.group_id', NULL, SqlEntry::IN_VALUES, NULL,$arg));	
		}
	}
	
	//наименование оборудования
	
	if(isset($_GET['eq_name'.$prefix])&&(strlen($_GET['eq_name'.$prefix])>0)){
		if(isset($_GET['print'])&&($_GET['print']==1)){
		  $decorator->AddEntry(new SqlEntry('pos.name',NULL, SqlEntry::E_SET, NULL, explode(';',SecStr(iconv("utf-8","windows-1251",$_GET['eq_name'.$prefix]))))); 
		  $decorator->AddEntry(new UriEntry('eq_name',iconv("utf-8","windows-1251",$_GET['eq_name'.$prefix])));
		}else{
		  $decorator->AddEntry(new SqlEntry('pos.name',NULL, SqlEntry::E_SET, NULL, explode(';',SecStr($_GET['eq_name'.$prefix])))); 
		  $decorator->AddEntry(new UriEntry('eq_name',$_GET['eq_name'.$prefix]));
			
		}
	}
	
	
	//в компанию
	
	/*if(isset($_GET['supplier_name'.$prefix])&&(strlen($_GET['supplier_name'.$prefix])>0)){
		if(isset($_GET['print'])&&($_GET['print']==1)){
		  $decorator->AddEntry(new SqlEntry('p.supplier_name',NULL, SqlEntry::LIKE_SET, NULL, explode(';',SecStr(iconv("utf-8","windows-1251",$_GET['supplier_name'.$prefix]))))); 
		  $decorator->AddEntry(new UriEntry('supplier_name',iconv("utf-8","windows-1251",$_GET['supplier_name'.$prefix])));
		}else{
		  $decorator->AddEntry(new SqlEntry('p.supplier_name',NULL, SqlEntry::LIKE_SET, NULL, explode(';',SecStr($_GET['supplier_name'.$prefix])))); 
		  $decorator->AddEntry(new UriEntry('supplier_name',$_GET['supplier_name'.$prefix]));
			
		}
	}*/
	
	//менеджер
	
	if(isset($_GET['manager_name'.$prefix])&&(strlen($_GET['manager_name'.$prefix])>0)){
		if(isset($_GET['print'])&&($_GET['print']==1)){
		  $decorator->AddEntry(new SqlEntry('mn.name_s',NULL, SqlEntry::E_SET, NULL, explode(';',SecStr(iconv("utf-8","windows-1251",$_GET['manager_name'.$prefix]))))); 
		  $decorator->AddEntry(new UriEntry('manager_name',iconv("utf-8","windows-1251",$_GET['manager_name'.$prefix])));
		}else{
		  $decorator->AddEntry(new SqlEntry('mn.name_s',NULL, SqlEntry::E_SET, NULL, explode(';',SecStr($_GET['manager_name'.$prefix])))); 
		  $decorator->AddEntry(new UriEntry('manager_name',$_GET['manager_name'.$prefix]));
			
		}
	}
	
	//ограничения по сотруднику
	$limited_user=NULL;
	if($au->FltUser($result)){
		//echo 'z';
		$_u_to_u=new UserToUser();
		$u_to_u=$_u_to_u->GetExtendedViewedUserIdsArr($result['id']);
		$limited_user=$u_to_u['sector_ids'];
		
		$decorator->AddEntry(new SqlEntry('mn.id', NULL, SqlEntry::IN_VALUES, NULL,$limited_user));	
	}
	//print_r($limited_user);
	
	
	if(!isset($_GET['sortmode'.$prefix])){
		$sortmode=5;	
	}else{
		$sortmode=abs((int)$_GET['sortmode'.$prefix]);
	}
	
	
	
	switch($sortmode){
		case 0:
			$decorator->AddEntry(new SqlOrdEntry('pos.id',SqlOrdEntry::DESC));
		break;
		case 1:
			$decorator->AddEntry(new SqlOrdEntry('pos.id',SqlOrdEntry::ASC));
		break;
		
		case 2:
			$decorator->AddEntry(new SqlOrdEntry('pos.code',SqlOrdEntry::DESC));
		break;	
		case 3:
			$decorator->AddEntry(new SqlOrdEntry('pos.code',SqlOrdEntry::ASC));
		break;
		
		case 4:
			$decorator->AddEntry(new SqlOrdEntry('pos.name',SqlOrdEntry::DESC));
		break;	
		case 5:
			$decorator->AddEntry(new SqlOrdEntry('pos.name',SqlOrdEntry::ASC));
		break;
		
		case 6:
			$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
		break;	
		case 7:
			$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::ASC));
		break;
		 
		
		

		
		default:
			$decorator->AddEntry(new SqlOrdEntry('pos.name',SqlOrdEntry::ASC));
		break;	
		
	}
	
	$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::ASC));
	
	$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	
	$filetext=$an->ShowData($result['org_id'], 
	 DateFromdmY($pdate1),
	  DateFromdmY($pdate2),
	  $prefix,
	  'an_pl_changes/an_pl_changes'.$print_add.'.html',
	  $decorator,
	  'an_pl_changes.php',
	  isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])||(($print==1)&&isset($_GET['tab_page'])&&($_GET['tab_page']==1)), 
	  $au->user_rights->CheckAccess('w',767),
	  $result,
	  $au->user_rights->CheckAccess('w',701)
	  );
	
	
	$sm->assign('log',$filetext);
		
	
	$sm->assign('has_kp',$au->user_rights->CheckAccess('w',766));
	
	
	
	//фиксировать открытие отчета
	if( isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])||(($print==1)&&isset($_GET['tab_page'])&&($_GET['tab_page']==1))  ){
		$log->PutEntry($result['id'],'открыл Отчет Изменения в прайс-листе',NULL,766,NULL, NULL);	
	}
	
	
	
	
	
	
	
	
	
	
	
	//общие поля
	
	$sm->assign('pdate', date("d.m.Y H:i:s"));
	//$username=$result['login'];
	$username=stripslashes($result['name_s']).' '.$username;	
	
	$sm->assign('username',$username);
	
	$sm->assign('tab_page',$tab_page);
	
	
	$content=$sm->fetch('an_pl_changes/an_pl_changes_form'.$print_add.'.html');
	
	
	
	
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