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

require_once('classes/missiongroup.php');
require_once('classes/missionitem.php');


require_once('classes/missionstatusitem.php');

require_once('classes/missionhistorygroup.php');
require_once('classes/missionexpgroup.php');

require_once('classes/supplieritem.php');
require_once('classes/supplier_city_item.php');
require_once('classes/supplier_region_item.php');
require_once('classes/supplier_district_item.php');

require_once('classes/user_s_item.php');



$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Командировка');

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



if(!isset($_GET['id'])){
	if(!isset($_POST['id'])){
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include("404.php");
		die();
	}else $id=abs((int)$_POST['id']); 
}else $id=abs((int)$_GET['id']);

if(!isset($_GET['print'])){
	if(!isset($_POST['print'])){
		$print=0;
	}else $print=abs((int)$_POST['print']); 
}else $print=abs((int)$_GET['print']);


if(!isset($_GET['tab_page'])){
	if(!isset($_POST['tab_page'])){
		$tab_page=0;
	}else $tab_page=abs((int)$_POST['tab_page']); 
}else $tab_page=abs((int)$_GET['tab_page']);


$mi=new MissionItem;
$claim=$mi->GetItemById($id);
$log=new ActionLog;
$hg=new MissionHistoryGroup;

/*$_tug=new TaskUserGroup;
$tug=$_tug->GetItemsArrById($id);
$tusers=array();
foreach($tug as $k=>$v) $tusers[]=$v['id'];*/

if($claim===false){
	header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include("404.php");
		die();
}

if(!$au->user_rights->CheckAccess('w',593)&&($claim['sent_user_id']!=0)&&($claim['sent_user_id']!=$result['id'])){
	//не наш заказ!
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();		
}


//журнал событий 
 
	$log=new ActionLog;
	$log->PutEntry($result['id'],'открыл карту командировки',NULL,591, NULL, $claim['code'] ,$id);


//$log->PutEntry($result['id'],'открыл историю заявки',NULL,48,NULL,'Номер заявки: '.$claim['id'],$id);


//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->display('top.html');
unset($smarty);






	if($print==0) include('inc/menu.php');
	
	
	
	//демонстрация страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	
	
	
	$ssi=new MissionStatusItem;
	$status=$ssi->GetItemById($claim['status_id']);
	$claim['status']=$status['name'];
	

	$claim['txt']=($claim['txt']);
	
	
	$claim['pdate']=date('d.m.Y H:i:s', $claim['pdate']);
	
	$claim['pdate_begin']=date('d.m.Y', $claim['pdate_begin']);
	
	$claim['pdate_end']=date('d.m.Y', $claim['pdate_end']);
	
	
	
	
	//кто создал
	$_ui=new UserItem;
	$_opf=new OpfItem;
	
	$ui=$_ui->Getitembyid($claim['user_id']);
	
		
		$claim['org']=$ui['name_s'].' ('.$ui['login'].')';
		//echo 'zzzzzzzz';
	
	//кто отправлен
	$ui=$_ui->Getitembyid($claim['sent_user_id']);
	
		
		$claim['sent_user']=$ui['name_s'].' ('.$ui['login'].')';
	
	
	
	
	//контрагент
	$_si=new SupplierItem;
	$si=$_si->getitembyid($claim['supplier_id']);
	$opf=$_opf->GetItemById($si['opf_id']);
	
	$claim['supplier']=$opf['name'].' '.$si['full_name'];
	
	
	//город
	$_ci=new SupplierCityItem;
	$ci=$_ci->GetItemById($claim['city_id']);
	
	$_ri=new SupplierRegionItem;
	$ri=$_ri->GetItemById($ci['region_id']);
	
	$_di=new SupplierDistrictItem;
	$di=$_di->GetItemById($ci['district_id']);
	
	
	$claim['city']=$ci['name'].', '.$di['name'].', '.$ri['name'];
	
	//суммы
	$_me=new MissionExpGroup;
	$claim['plan']=$_me->CalcPlan($id);
	$claim['fact']=$_me->CalcFact($id);
	
	$ras=$_me->GetItemsByIdArr($id);
	$sm->assign('ras',$ras);
	
	
	
	$sm->assign('claim',$claim);
	
	
	$hg=new MissionHistoryGroup;
	
	if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
	else $from=0;
	
	if(isset($_GET['to_page'])) $to_page=abs((int)$_GET['to_page']);
	else $to_page=ITEMS_PER_PAGE;
	
	$decorator=new DBDecorator;
	
	$decorator->AddEntry(new SqlOrdEntry('id',SqlOrdEntry::DESC));
	
	$decorator->AddEntry(new UriEntry('id',$id));
	
	$decorator->AddEntry(new UriEntry('to_page',$to_page));
	
	if($print==0) $print_add='';
	else $print_add='_print';
	
	
	
	
	$llg=$hg->ShowHistory($id, 'mission/missionhistory_list'.$print_add.'.html', $decorator, $from,$to_page,$au->user_rights->CheckAccess('w',594));
	
	
	$sm->assign('log',$llg);
	
	
	
	
	
	if($print==0) $template='mission/missionhistory.html';
	elseif($print==1) $template='mission/missionhistory_print.html';
	
	else $template='mission/missionhistory'.$print_add.'.html';
	
	
	//Вкладка "журнал событий"
	$sm->assign('has_syslog',$au->user_rights->CheckAccess('w',3));
	if($au->user_rights->CheckAccess('w',3)){
		
			$decorator=new DBDecorator;
	
	
		
			if(!isset($_GET['pdate1'])){
			
					$_pdate1=DateFromdmY(date("d.m.Y"))-60*60*24*30;
					$pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
				
			}else $pdate1 = $_GET['pdate1'];
			
			
			
			if(!isset($_GET['pdate2'])){
					
					$_pdate2=DateFromdmY(date("d.m.Y"))+60*60*24;
					$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
			}else $pdate2 = $_GET['pdate2'];
			
			$decorator->AddEntry(new SqlEntry('pdate',DateFromdmY($pdate1), SqlEntry::BETWEEN,DateFromdmY($pdate2)));
			$decorator->AddEntry(new UriEntry('pdate1',$pdate1));
			$decorator->AddEntry(new UriEntry('pdate2',$pdate2));
			
			
			if(isset($_GET['user_subj_login'])&&(strlen($_GET['user_subj_login'])>0)){
				$decorator->AddEntry(new SqlEntry('s.login',SecStr($_GET['user_subj_login']), SqlEntry::LIKE));
				$decorator->AddEntry(new UriEntry('user_subj_login',$_GET['user_subj_login']));
			}
			
			if(isset($_GET['description'])&&(strlen($_GET['description'])>0)){
				$decorator->AddEntry(new SqlEntry('l.description',SecStr($_GET['description']), SqlEntry::LIKE));
				$decorator->AddEntry(new UriEntry('description',$_GET['description']));
			}
			
			if(isset($_GET['object_id'])&&(strlen($_GET['object_id'])>0)){
				$decorator->AddEntry(new SqlEntry('l.object_id',SecStr($_GET['object_id']), SqlEntry::E));
				$decorator->AddEntry(new UriEntry('object_id',$_GET['object_id']));
			}
			
			if(isset($_GET['user_obj_login'])&&(strlen($_GET['user_obj_login'])>0)){
				$decorator->AddEntry(new SqlEntry('o.login',SecStr($_GET['user_obj_login']), SqlEntry::LIKE));
				$decorator->AddEntry(new UriEntry('user_obj_login',$_GET['user_obj_login']));
			}
			
			if(isset($_GET['user_group_id'])&&(strlen($_GET['user_group_id'])>0)){
				$decorator->AddEntry(new SqlEntry('l.user_group_id',SecStr($_GET['user_group_id']), SqlEntry::E));
				$decorator->AddEntry(new UriEntry('user_group_id',$_GET['user_group_id']));
			}
			
			if(isset($_GET['ip'])&&(strlen($_GET['ip'])>0)){
				$decorator->AddEntry(new SqlEntry('ip',SecStr($_GET['ip']), SqlEntry::LIKE));
				$decorator->AddEntry(new UriEntry('ip',$_GET['ip']));
			}
			
			
			
			//сортировку можно подписать как дополнительный параметр для UriEntry
			if(!isset($_GET['sortmode'])){
				$sortmode=0;	
			}else{
				$sortmode=abs((int)$_GET['sortmode']);
			}
			
			
			switch($sortmode){
				case 0:
					$decorator->AddEntry(new SqlOrdEntry('pdate',SqlOrdEntry::DESC));
				break;
				case 1:
					$decorator->AddEntry(new SqlOrdEntry('pdate',SqlOrdEntry::ASC));
				break;
				case 2:
					$decorator->AddEntry(new SqlOrdEntry('s.login',SqlOrdEntry::DESC));
				break;	
				case 3:
					$decorator->AddEntry(new SqlOrdEntry('s.login',SqlOrdEntry::ASC));
				break;
				case 4:
					$decorator->AddEntry(new SqlOrdEntry('l.description',SqlOrdEntry::DESC));
				break;
				case 5:
					$decorator->AddEntry(new SqlOrdEntry('l.description',SqlOrdEntry::ASC));
				break;	
				case 6:
					$decorator->AddEntry(new SqlOrdEntry('ob.name',SqlOrdEntry::DESC));
				break;
				case 7:
					$decorator->AddEntry(new SqlOrdEntry('ob.name',SqlOrdEntry::ASC));
				break;
				case 8:
					$decorator->AddEntry(new SqlOrdEntry('o.login',SqlOrdEntry::DESC));
				break;	
				case 9:
					$decorator->AddEntry(new SqlOrdEntry('o.login',SqlOrdEntry::ASC));
				break;
				case 10:
					$decorator->AddEntry(new SqlOrdEntry('gr.name',SqlOrdEntry::DESC));
				break;
				case 11:
					$decorator->AddEntry(new SqlOrdEntry('gr.name',SqlOrdEntry::ASC));
				break;	
				case 12:
					$decorator->AddEntry(new SqlOrdEntry('ip',SqlOrdEntry::DESC));
				break;
				case 13:
					$decorator->AddEntry(new SqlOrdEntry('ip',SqlOrdEntry::ASC));
				break;	
				default:
					$decorator->AddEntry(new SqlOrdEntry('pdate',SqlOrdEntry::DESC));
				break;	
				
			}
			$decorator->AddEntry(new SqlOrdEntry('id',SqlOrdEntry::DESC));
			
			$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
			
			
			
			if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
			else $from=0;
			
			if(isset($_GET['to_page'])) $to_page=abs((int)$_GET['to_page']);
			else $to_page=ITEMS_PER_PAGE;
			$decorator->AddEntry(new UriEntry('to_page',$to_page));
		
			$decorator->AddEntry(new SqlEntry('object_id',NULL, SqlEntry::IN_VALUES, NULL, array(591,
592,
593,
594,
595,
597,
596
)));
			$decorator->AddEntry(new SqlEntry('affected_object_id',$id, SqlEntry::E));
			//$decorator->AddEntry(new UriEntry('action',1));
			$decorator->AddEntry(new UriEntry('id',$id));
			$decorator->AddEntry(new UriEntry('tab_page',2));
			
			
			
			$llg=$log->ShowLog('syslog/log.html',$decorator,$from,$to_page,'missionhistory.php');
			
			$sm->assign('syslog',$llg);	
		
	}
	
	
	
	$sm->assign('tab_page',$tab_page);
	
	$content=$sm->fetch($template);
	
	
	
	
	
	
	
	$smarty->assign('fast_menu', $menu_arr_fast);
	  $smarty->assign('main_menu',$menu_res);
	$smarty->assign('content',$content);
	if($print==0) $smarty->display('page.html');
	elseif($print==3) {}
	else echo $content;
	unset($smarty);
	
	
	



$smarty = new SmartyAdm;

//работа с футером
require_once('inc/footer.php');
if(isset($footer_res)){
	$smarty->assign('footer',$footer_res);
}else $smarty->assign('footer','');

$smarty->display('bottom.html');
unset($smarty);
//пометить прочитанными все изменения
$hg->ToggleRead($id,$result['id'],false);	
?>