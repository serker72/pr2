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

require_once('classes/sorderitem.php');
require_once('classes/sreclamitem.php');
require_once('classes/sreclamhistorygroup.php');
require_once('classes/statusitem.php');
require_once('classes/statusgroup.php');

require_once('classes/user_s_group.php');

require_once('classes/contract_maintenance_item.php');
require_once('classes/contract_maintenance_group.php');

require_once('classes/contract_maintenance_statusgroup.php');
require_once('classes/contract_maintenance_historygroup.php');



$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'События договора на сопровождение');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckFactAddress())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}
require_once('inc/restr.php');


//внесение изменений в права
if(isset($_POST['doInp'])){
	
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
				
				//установить проверку, есть ли права на администрирование данного объекта данным пользователем
				if(!$au->user_rights->CheckAccess('x',$regs[2])){
					continue;
				}
				
				
				//public function PutEntry($user_subject_id, $description, $user_object_id=NULL, $object_id=NULL, $user_group_id=NULL)
				if($state==1){
					$man->GrantAccess($regs[3], $regs[1], $regs[2]);
					$pro=$au->GetProfile();
					$log->PutEntry($pro['id'], "установил доступ ".$regs[1],$regs[3],$regs[2]);
					
				}else{
					$man->RevokeAccess($regs[3], $regs[1], $regs[2]);
					$pro=$au->GetProfile();
					$log->PutEntry($pro['id'], "удалил доступ ".$regs[1],$regs[3],$regs[2]);
				}
				
				
			}
		}
	}
	
	header("Location: contract_maintenance.php");	
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



$cmi = new ContractMaintenanceItem;
$cm_item = $cmi->GetItemById($id);
$log=new ActionLog;

if($cm_item===false){
	header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include("404.php");
		die();
}

//if(!$au->user_rights->CheckAccess('r',157)&&($cm_item['s_user_id']!=0)&&($cm_item['s_user_id']!=$result['id'])){
if((!$au->user_rights->CheckAccess('r',157)&&($cm_item['s_user_id']!=0)&&($cm_item['s_user_id']!=$result['id'])) && (!$au->user_rights->CheckAccess('r',158)&&($cm_item['d_user_id']!=0)&&($cm_item['d_user_id']!=$result['id']))){
	//не наш заказ!
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();		
}







if(!$au->user_rights->CheckAccess('r', ($result['dealer_id'] == 1 ? 158 : 157))){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}

$log->PutEntry($result['id'], 'открыл историю договора на обслуживание', NULL, ($result['dealer_id'] == 1 ? 158 : 157), NULL, 'Номер договора на обслуживание: '.$cm_item['id'], $cm_item['id']);

//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

if($print==0) $smarty->display('top.html');
else $smarty->display('top_print.html');
unset($smarty);

$_menu_id=43;

	if($print==0) include('inc/menu.php');
	
	
	
	//демонстрация страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	//строим вкладку администрирования
	if(HAS_ADMIN_TABS){
            $sm->assign('has_admin',
                    $au->user_rights->CheckAccess('x', 150)||
                    $au->user_rights->CheckAccess('x', 151)||
                    $au->user_rights->CheckAccess('x', 155)||
                    $au->user_rights->CheckAccess('x', 156)||
                    $au->user_rights->CheckAccess('x', 157)||
                    $au->user_rights->CheckAccess('x', 158)
            );
            $dto=new DiscrTableObjects($result['id'],array('150', '151', '155', '156', '157', '158'));
            $admin=$dto->Draw('contract_maintenance_history.php','admin/admin_objects.html');
            $sm->assign('admin',$admin);
	}
	
	
	$cm_item['pdate']=date("d.m.Y H:i:s",$cm_item['pdate']);
	$cm_item['txt']=($cm_item['txt']);
	
	/*$ssi=new StatusItem;
	$status=$ssi->GetItemById($order['status_id']);
	$order['status']=$status['name'];*/
	$sm->assign('cm_item',$cm_item);
	
	
	
	$hg=new ContractMaintenanceHistoryGroup;
	
	if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
	else $from=0;
	
	if(isset($_GET['to_page'])) $to_page=abs((int)$_GET['to_page']);
	else $to_page=ITEMS_PER_PAGE;
	
	$decorator=new DBDecorator;
	
	$decorator->AddEntry(new SqlOrdEntry('pdate',SqlOrdEntry::DESC));
	
//	$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	
	$decorator->AddEntry(new UriEntry('to_page',$to_page));
	
	if($print==0) $print_add='';
	else $print_add='_print';
	
	$llg=$hg->ShowHistory($id,'contract_maintenance/history_list'.$print_add.'.html',$decorator,$from,$to_page,$au->user_rights->CheckAccess('r', ($result['dealer_id'] == 1 ? 156 : 155)));
	
	
	$sm->assign('log',$llg);
	
	
	
	//Вкладка "журнал событий"
	$sm->assign('has_syslog',$au->user_rights->CheckAccess('r',3));
	if($au->user_rights->CheckAccess('r',3)){
		
			$decorator=new DBDecorator;
	
	
		
			
			
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
		
			$decorator->AddEntry(new SqlEntry('object_id',NULL, SqlEntry::IN_VALUES, NULL, array(150, 151, 152, 153, 154, 155, 156, 157, 158)));
			$decorator->AddEntry(new SqlEntry('affected_object_id',$id, SqlEntry::E));
			//$decorator->AddEntry(new UriEntry('action',1));
			$decorator->AddEntry(new UriEntry('id',$id));
			$decorator->AddEntry(new UriEntry('tab_page',2));
			
			
			
			$llg=$log->ShowLog('syslog/log_doc.html',$decorator,$from,$to_page,'spret_history.php');
			
			$sm->assign('syslog',$llg);	
		
	}
	
	
	
		$sm->assign('pdate_signed', date("d.m.Y H:i:s"));
	$sm->assign('user_signed',$result);
	
	
	
	$sm->assign('tab_page',$tab_page);
	
	
	$content=$sm->fetch('contract_maintenance/history'.$print_add.'.html');
	
	
	
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

//пометить прочитанными все изменения
$hg->ToggleRead($id,$result['id'],false);	
?>