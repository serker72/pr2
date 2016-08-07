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

require_once('classes/app_contract_group.php');
require_once('classes/user_pos_item.php');

require_once('classes/user_to_user.php');
require_once('classes/rl/rl_man.php');

$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Заявки на договора');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

	if(isset($_GET['from']))
	{
		 $from=abs((int)$_GET['from']);
	//elseif(isset($_SESSION['bills_from'])){
		//$from=abs((int)$_SESSION['bills_from']);
	}else $from=0;
	//$_SESSION['bills_from']=$from;

if(!isset($_GET['print'])){
	if(!isset($_POST['print'])){
		$print=0;
	}else $print=abs((int)$_POST['print']); 
}else $print=abs((int)$_GET['print']);

if(($print==1)&&!$au->user_rights->CheckAccess('w',823)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}

if(!$au->user_rights->CheckAccess('w',1150)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}




//журнал событий 
$log=new ActionLog;
if($print==0){
	$log->PutEntry($result['id'],'открыл раздел Заявки на договора',NULL,1150);
}else{
	$log->PutEntry($result['id'],'открыл раздел Заявки на договора: версия для печати',NULL,823);
}

//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->assign('do_restrict', !in_array($result['id'], array(1,2,3)));

if($print==0) $smarty->display('top.html');
else $smarty->display('top_print_alb.html');
unset($smarty);


	

$_menu_id=84;
	if($print==0) include('inc/menu.php');
	
			
	if($print==0) $print_add='';
	else $print_add='_print';
	
	
	
	//демонстрация страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	

	
	
/*********** OPO ****************************************************************************/		
	
	//покажем лог
	$log=new AppContractGroup;
	//Разбор переменных запроса
	if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
	else $from=0;
	
	if(isset($_GET['to_page'])) $to_page=abs((int)$_GET['to_page']);
	else $to_page=ITEMS_PER_PAGE;
	
	$decorator=new DBDecorator;
	//$decorator->AddEntry(new SqlEntry('p.org_id',abs((int)$result['org_id']), SqlEntry::E));
	
	//echo $result['org_id'];
	
	if(!isset($_GET['sortmode'])){
		$sortmode=0;	
	}else{
		$sortmode=abs((int)$_GET['sortmode']);
	}
	
	
	if(isset($_GET['id'])&&(strlen($_GET['id'])>0)){
		$decorator->AddEntry(new SqlEntry('p.id',SecStr($_GET['id']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('id',$_GET['id']));
	}
	
	
	if(isset($_GET['supplier_name'])&&(strlen($_GET['supplier_name'])>0)){
		if(isset($_GET['print'])&&($_GET['print']==1)){
		$decorator->AddEntry(new SqlEntry('sp.full_name',SecStr(iconv('utf-8','windows-1251',$_GET['supplier_name'])), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('supplier_name',iconv('utf-8','windows-1251',$_GET['supplier_name'])));
		}else{
			$decorator->AddEntry(new SqlEntry('sp.full_name',SecStr($_GET['supplier_name']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('supplier_name',$_GET['supplier_name']));
		}
	}
	
	if(isset($_GET['supplier_is_new'])&&(strlen($_GET['supplier_is_new'])>0)){
		if($_GET['supplier_is_new']==0) {
			
			$decorator->AddEntry(new SqlEntry('p.supplier_is_new',0, SqlEntry::E));
			$decorator->AddEntry(new UriEntry('supplier_is_new',0));
		}
		if($_GET['supplier_is_new']==1) {
			
			$decorator->AddEntry(new SqlEntry('p.supplier_is_new',1, SqlEntry::E));
			$decorator->AddEntry(new UriEntry('supplier_is_new',1));
		}
	}
	
	if(isset($_GET['city'])&&(strlen($_GET['city'])>0)){
		if(isset($_GET['print'])&&($_GET['print']==1)){
		$decorator->AddEntry(new SqlEntry('c.name',SecStr(iconv('utf-8','windows-1251',$_GET['city'])), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('city',iconv('utf-8','windows-1251',$_GET['city'])));
		}else{
			$decorator->AddEntry(new SqlEntry('c.name',SecStr($_GET['city']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('city',$_GET['city']));
		}
	}
	
	if(isset($_GET['contract_no'])&&(strlen($_GET['contract_no'])>0)){
		if(isset($_GET['print'])&&($_GET['print']==1)){
		$decorator->AddEntry(new SqlEntry('p.contract_no',SecStr(iconv('utf-8','windows-1251',$_GET['contract_no'])), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('contract_no',iconv('utf-8','windows-1251',$_GET['contract_no'])));
		}else{
			$decorator->AddEntry(new SqlEntry('p.contract_no',SecStr($_GET['contract_no']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('contract_no',$_GET['contract_no']));
		}
	}
	
	
	 
	
	
	//блок фильтров статуса
	
	
	$status_ids=array();
	  	$cou_stat=0;   
		if(isset($_GET['statuses'])&&is_array($_GET['statuses'])) $cou_stat=count($_GET['statuses']);
		if($cou_stat>0){
		  //есть гет-запросы	
		  $status_ids=$_GET['statuses'];
		  
	  	}else{
		  $cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^fact_opo_status_id_', $k)) $cou_stat++;
		  
		  if($cou_stat>0){
			  //есть кукисы
			  foreach($_COOKIE as $k=>$v) if(eregi('^fact_opo_status_id_', $k)) $status_ids[]=(int)eregi_replace('^fact_opo_status_id_','',$k);
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
				  $decorator->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));	
				   foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry('statuses[]',$v));
			  }
		  } 
		
	

	
	
	if(isset($_GET['manager_name'])&&(strlen($_GET['manager_name'])>0)){
		if(isset($_GET['print'])&&($_GET['print']==1)){
		$decorator->AddEntry(new SqlEntry('mn.name_s',SecStr(iconv('utf-8','windows-1251',$_GET['manager_name'])), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('manager_name',iconv('utf-8','windows-1251',$_GET['manager_name'])));
		}else{
			$decorator->AddEntry(new SqlEntry('mn.name_s',SecStr($_GET['manager_name']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('manager_name',$_GET['manager_name']));
		}
	}
	
	
	$_rl=new RLMan;
	//отбор по себе и подчиненным сотр-кам
	if(!$au->user_rights->CheckAccess('w',784)){
		
		//если есть права просмотра ВСЕХ фактов по определенному поставщику - 
		if($au->user_rights->CheckAccess('w',845)){
			
			$restricted_producers=$_rl->GetBlockedItemsArr($result['id'],34,'w', 'pl_producer', 0);
			//var_dump($restricted_producers);
			$decorator->AddEntry(new SqlEntry('p.producer_id', NULL, SqlEntry::NOT_IN_VALUES, NULL,$restricted_producers));	
		}else{
		//иначе
		
			
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
			
			$decorator->AddEntry(new SqlEntry('p.user_id',$result['id'], SqlEntry::E));
			
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			
			$decorator->AddEntry(new SqlEntry('p.user_id','select id from user where manager_id="'.$result['id'].'" and is_in_plan_fact_sales=1 and is_active=1', SqlEntry::IN_SQL));
			
			//если тек. сотр-к является руководителем отдела - добавить ему руководимых сотрудников
			$_pos=new UserPosItem;
			$pos=$_pos->Getitembyid($result['position_id']);
			if($pos['is_ruk_otd']){
				$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			
				$decorator->AddEntry(new SqlEntry('p.user_id','select id from user where is_active=1 and is_in_plan_fact_sales=1 and department_id="'.$result['department_id'].'" and id<>"'.$result['id'].'"', SqlEntry::IN_SQL));
			}
			
			
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
		}
	}
	
	 
	
	//ограничения по сотруднику
	$limited_user=NULL;
	if($au->FltUser($result)){
		//echo 'z';
		$_u_to_u=new UserToUser();
		$u_to_u=$_u_to_u->GetExtendedViewedUserIdsArr($result['id']);
		$limited_user=$u_to_u['sector_ids'];
		
	}
	//print_r($limited_user);
	
	//сортировку можно подписать как дополнительный параметр для UriEntry
	
	
	switch($sortmode){
		case 0:
			$decorator->AddEntry(new SqlOrdEntry('p.id',SqlOrdEntry::DESC));
		break;
		case 1:
			$decorator->AddEntry(new SqlOrdEntry('p.id',SqlOrdEntry::ASC));
		break;
		case 2:
			$decorator->AddEntry(new SqlOrdEntry('p.month',SqlOrdEntry::DESC));
		break;	
		case 3:
			$decorator->AddEntry(new SqlOrdEntry('p.month',SqlOrdEntry::ASC));
		break;
		
		case 4:
			$decorator->AddEntry(new SqlOrdEntry('p.year',SqlOrdEntry::DESC));
		break;	
		case 5:
			$decorator->AddEntry(new SqlOrdEntry('p.year',SqlOrdEntry::ASC));
		break;
		case 6:
			$decorator->AddEntry(new SqlOrdEntry('us_name',SqlOrdEntry::DESC));
		break;
		case 7:
			$decorator->AddEntry(new SqlOrdEntry('us_name',SqlOrdEntry::ASC));
		break;
		
		case 8:
			$decorator->AddEntry(new SqlOrdEntry('eq.name',SqlOrdEntry::DESC));
		break;
		case 9:
			$decorator->AddEntry(new SqlOrdEntry('eq.name',SqlOrdEntry::ASC));
		break;
		
		case 10:
			$decorator->AddEntry(new SqlOrdEntry('sp.full_name',SqlOrdEntry::DESC));
		break;
		case 11:
			$decorator->AddEntry(new SqlOrdEntry('sp.full_name',SqlOrdEntry::ASC));
		break;
		
		case 12:
			$decorator->AddEntry(new SqlOrdEntry('c.name',SqlOrdEntry::DESC));
		break;
		case 13:
			$decorator->AddEntry(new SqlOrdEntry('c.name',SqlOrdEntry::ASC));
		break;
		
		case 14:
			$decorator->AddEntry(new SqlOrdEntry('prod.name',SqlOrdEntry::DESC));
		break;
		case 15:
			$decorator->AddEntry(new SqlOrdEntry('prod.name',SqlOrdEntry::ASC));
		break;
		
		case 16:
			$decorator->AddEntry(new SqlOrdEntry('p.supplier_is_new',SqlOrdEntry::DESC));
		break;
		case 17:
			$decorator->AddEntry(new SqlOrdEntry('p.supplier_is_new',SqlOrdEntry::ASC));
		break;
		
	
		default:
			$decorator->AddEntry(new SqlOrdEntry('p.id',SqlOrdEntry::DESC));
		break;	
		
	}
	//$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
	
	$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	
	$decorator->AddEntry(new UriEntry('to_page',$to_page));
	
	$decorator->AddEntry(new UriEntry('print',$print));
	
	//$log->AutoAnnul();
	//$log->AutoEq();
	$log->SetAuthResult($result);
	
	
	
	$llg=$log->ShowPos('app_contract/app_contract_list'.$print_add.'.html', 
	  $decorator, 
	  $from, 
	  $to_page,   
	  
		$au->user_rights->CheckAccess('w',793) , 
		$au->user_rights->CheckAccess('w',791), 
		true,
		false ,
		$au->user_rights->CheckAccess('w',794),
		$au->user_rights->CheckAccess('w',792), 
		$au->user_rights->CheckAccess('w',788), 
		$au->user_rights->CheckAccess('w',813),
		$au->user_rights->CheckAccess('w',823),
		$temp_alls,
		$au->user_rights->CheckAccess('w',825),
		$limited_user
		
	);
	
	
	$sm->assign('log',$llg);
	$sm->assign('has_kps', $au->user_rights->CheckAccess('w',844));
	
	
	
	
	
	$sm->assign('pdate', date("d.m.Y H:i:s"));
	//$username=$result['login'];
	$username=stripslashes($result['name_s']).' '.$username;	
	
	$sm->assign('username',$username);
	
 
	
	$content=$sm->fetch('app_contract/app_contract'.$print_add.'.html');
	
	
	
	
	
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