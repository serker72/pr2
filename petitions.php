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

require_once('classes/petitiongroup.php');
require_once('classes/petitionitem.php');

require_once('classes/petitionmygroup.php');
require_once('classes/petitionallgroup.php');



$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Заявления');

$au=new AuthUser();
$result=$au->Auth();

$log=new ActionLog;

if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

if(!$au->user_rights->CheckAccess('w',723)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}

//журнал событий 
$log=new ActionLog;
$log->PutEntry($result['id'],'открыл раздел Заявления',NULL,723);


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



//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->display('top.html');
unset($smarty);


$_menu_id=59;
	include('inc/menu.php');
	
	
	
	//демонстрация страницы
	$smarty = new SmartyAdm;
	
	
	if(!$au->user_rights->CheckAccess('w',1139)){
		$content='<p>У Вас нет доступа в этот раздел.</p><p>Просьба обратиться к администраторам программы.</p>';
		
	}else{
	
		$sm=new SmartyAdm;
		
		
		
		
		
		
		
	/***********************************************************************************************************/
	//мои заявления
		$_tg=new PetitionAllGroup;
		$_tg->SetAuthResult($result);
		
		$prefix=$_tg->prefix;
		
		if(isset($_GET['from'.$prefix])) $from=abs((int)$_GET['from'.$prefix]);
		else $from=0;
		
		if(isset($_GET['to_page'.$prefix])) $to_page=abs((int)$_GET['to_page'.$prefix]);
		else $to_page=ITEMS_PER_PAGE;
		
		$decorator=new DBDecorator;
		
		
		//№
		if(isset($_GET['code'.$prefix])&&(strlen($_GET['code'.$prefix])>0)){
			$decorator->AddEntry(new UriEntry('code',$_GET['code'.$prefix]));
			$decorator->AddEntry(new SqlEntry('t.code',SecStr($_GET['code'.$prefix]), SqlEntry::LIKE));
		}
		
		if(isset($_GET['force_create'])&&(strlen($_GET['force_create'])>0)){
			$decorator->AddEntry(new UriEntry('force_create',$_GET['force_create']));
	
		}
		
		
		//срок выполнения
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
		$decorator->AddEntry(new SqlEntry('t.pdate',DateFromdmY($pdate1), SqlEntry::BETWEEN,DateFromdmY($pdate2)));
		
		
		if(!isset($_GET['given_pdate1'.$prefix])){
		
				$_pdate1=DateFromdmY(date("d.m.Y"))-60*60*24*30*3;
				$pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
			
		}else $pdate1 = $_GET['given_pdate1'.$prefix];
		
		
		
		if(!isset($_GET['given_pdate2'.$prefix])){
				
				$_pdate2=DateFromdmY(date("d.m.Y"))+60*60*24;
				$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
		}else $pdate2 = $_GET['given_pdate2'.$prefix];
		
		
		if(isset($_GET['given_pdate1'.$prefix])&&(strlen($_GET['given_pdate1'.$prefix])>0)) $decorator->AddEntry(new UriEntry('given_pdate1',$pdate1));
		if(isset($_GET['given_pdate2'.$prefix])&&(strlen($_GET['given_pdate2'.$prefix])>0)) $decorator->AddEntry(new UriEntry('given_pdate2',$pdate2));
		
		if((strlen($_GET['given_pdate1'.$prefix])>0)||(strlen($_GET['given_pdate2'.$prefix])>0)) {
			//$decorator->AddEntry(new SqlEntry('t.given_pdate',DateFromdmY($pdate1), SqlEntry::BETWEEN,DateFromdmY($pdate2)+60*60*24));
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));	
		 
			
			
			$decorator->AddEntry(new SqlEntry('t.id','select id from petition where  kind_id in(1,2) and  t.begin_pdate between "'.DateFromdmY($pdate1).'" and "'.(DateFromdmY($pdate2)+60*60*24-1).'"', SqlEntry::IN_SQL));
			
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			
			$decorator->AddEntry(new SqlEntry('t.id','select id from petition where  kind_id in(4,5,6) and  t.given_pdate between "'.DateFromdmY($pdate1).'" and "'.(DateFromdmY($pdate2)+60*60*24-1).'"', SqlEntry::IN_SQL));
			
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			
			$decorator->AddEntry(new SqlEntry('t.id','select id from petition where  kind_id in(3,8) and  t.id in(select distinct petition_id from petition_vyh_date_otp where pdate between "'.DateFromdmY($pdate1).'" and "'.(DateFromdmY($pdate2)+60*60*24-1).'")', SqlEntry::IN_SQL));
			
		 	$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));	
		 
			
		}
		
		
		
		
		
		//блок фильтров статуса
		
		
		$status_ids=array();
			$cou_stat=0;   
			if(isset($_GET['statuses'.$prefix])&&is_array($_GET['statuses'.$prefix])) $cou_stat=count($_GET['statuses'.$prefix]);
			if($cou_stat>0){
			  //есть гет-запросы	
			  $status_ids=$_GET['statuses'.$prefix];
			  
			}else{
			  $cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^petition_status'.$prefix.'_id_', $k)) $cou_stat++;
			  
			  if($cou_stat>0){
				  //есть кукисы
				  foreach($_COOKIE as $k=>$v) if(eregi('^petition_status'.$prefix.'_id_', $k)) $status_ids[]=(int)eregi_replace('^petition_status'.$prefix.'_id_','',$k);
			  }else{
				  //ничего нет - выбираем ВСЕ!	
				  $decorator->AddEntry(new UriEntry('all_statuses',1));
			  }
		  }
		   
			 if(count($status_ids)>0){
				  $of_zero=true; foreach($status_ids as $k=>$v) if($v>0) $of_zero=$of_zero&&false;
				  
				  if($of_zero){
					  //ничего нет - выбираем ВСЕ!	
					  $decorator->AddEntry(new UriEntry('all_statuses',1));
				  }else{
				  
					  foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry('status_id_'.$v,1));
					  $decorator->AddEntry(new SqlEntry('t.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));	
					  
					   foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry('statuses'.$prefix.'[]',$v));
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
			$decorator->AddEntry(new SqlEntry('u.name_s',SecStr($_GET['user_name'.$prefix]), SqlEntry::LIKE));
		}
		
		
		
		//отв сотр
		if(isset($_GET['otv_user_name'.$prefix])&&(strlen($_GET['otv_user_name'.$prefix])>0)){
			$decorator->AddEntry(new UriEntry('otv_user_name',$_GET['otv_user_name'.$prefix]));
			$decorator->AddEntry(new SqlEntry('t.id','select petition_id from petition_user where user_id in(select id from user where name_s like "%'.SecStr($_GET['otv_user_name'.$prefix]).'%" or login like "%'.SecStr($_GET['otv_user_name'.$prefix]).'%")', SqlEntry::IN_SQL));
		}
		
		
		//контроль видимости
		if(!$au->user_rights->CheckAccess('w',727)){
				$decorator->AddEntry(new SqlEntry('t.id', NULL, SqlEntry::IN_VALUES, NULL,$_tg->GetAvailableDocIds($result['id'])));	
			}
			 
		//var_dump($_tg->GetAvailableDocIds($result['id']));
		
		
		//сортировка
			if(!isset($_GET['sortmode'.$prefix])){
			$sortmode=0;	
		}else{
			$sortmode=abs((int)$_GET['sortmode'.$prefix]);
		}
		
		
		
		
		switch($sortmode){
			case 0:
				$decorator->AddEntry(new SqlOrdEntry('t.code',SqlOrdEntry::DESC));
			break;
			case 1:
				$decorator->AddEntry(new SqlOrdEntry('t.code',SqlOrdEntry::ASC));
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
			
			case 10:
				$decorator->AddEntry(new SqlOrdEntry('t.given_pdate',SqlOrdEntry::DESC));
			break;
			case 11:
				$decorator->AddEntry(new SqlOrdEntry('t.given_pdate',SqlOrdEntry::ASC));
			break;
			
			
			default:
				$decorator->AddEntry(new SqlOrdEntry('t.code',SqlOrdEntry::ASC));
			break;	
			
		}
		$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
		
		
		
		
		
		$decorator->AddEntry(new UriEntry('tab_page',2));
		
		
		
		$ships=$_tg->ShowPos($result['id'], //0
		 "petition/petitions.html", //1
		 $decorator, //2
		 false, //3
		 $au->user_rights->CheckAccess('w',724), //4
		 $alls, //5
		 $from,  //6
		 $to_page, //7
		 $result, //8
		 $au->user_rights->CheckAccess('w',827), //9
		 $au->user_rights->CheckAccess('w',725), //10
		 $au->user_rights->CheckAccess('w',826), //11
		 true,
		 $au->user_rights->CheckAccess('w',829), //13
		 $au->user_rights->CheckAccess('w',830),  //14
		 $au->user_rights->CheckAccess('w',831),  //15
		 $au->user_rights->CheckAccess('w',832),  //16
		 
		 $au->user_rights->CheckAccess('w',828), //17
		 $au->user_rights->CheckAccess('w',1135), //18
		 $au->user_rights->CheckAccess('w',1136)  //19
		 );
		//$ships='Раздел находится в разработке.';
		
	 
			
		$sm->assign('petitions_2',$ships); 	
		
		
	 
		
		
		$content=$sm->fetch('petition/petitions_tabs.html');
	}
	
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