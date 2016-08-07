<?

require_once('classes/orgitem.php');
require_once('classes/opfitem.php');

$sm=new SmartyAdm;
if(isset($result)&&($result!==NULL)){
	$sm->assign('authed',true);
	
	$sm->assign('login',$result['login']);
	$sm->assign('email_s',$result['email_s']);

	$sm->assign('name',stripslashes($result['name_s']));
	$_menu_org=new OrgItem;
	$_menu_opf=new OpfItem;
	
	$menu_org=$_menu_org->GetItemById($result['org_id']);
	$menu_opf=$_menu_opf->GetItemById($menu_org['opf_id']);
	$sm->assign('org_name',stripslashes($menu_opf['name'].' '.$menu_org['full_name']));

	
	
	//новая позиция каталога
	if($au->user_rights->CheckAccess('w',67)){
		$sm->assign('has_new_position',true);	
	}
	
	if($au->user_rights->CheckAccess('w',70)){
		$sm->assign('has_new_tovgr',true);	
	}
	
	if($au->user_rights->CheckAccess('w',87)){
		$sm->assign('has_new_supplier',true);	
	}
	
	
	if($au->user_rights->CheckAccess('w',586)){
		$sm->assign('has_new_task',true);	
	}
	
	if($au->user_rights->CheckAccess('w',592)){
		$sm->assign('has_new_mission',true);	
	}
	
	/*
	if($au->user_rights->CheckAccess('w',100)){
		$sm->assign('has_new_is',true);	
	}
	*/
	if($au->user_rights->CheckAccess('w',105)){
		$sm->assign('has_new_wf',true);	
	}
	
	
	if($au->user_rights->CheckAccess('w',696)){
		$sm->assign('has_new_kp',true);	
	}
	
	if($au->user_rights->CheckAccess('w',730)){
		$sm->assign('has_new_memo',true);	
	}
	
	if($au->user_rights->CheckAccess('w',724)){
		$sm->assign('has_new_petition',true);	
	}
	
	$sm->assign('has_plan',$au->user_rights->CheckAccess('w',903));	
	
	$sm->assign('has_tender_zayav',$au->user_rights->CheckAccess('w',976));	
	
	$sm->assign('has_tender',$au->user_rights->CheckAccess('w',931));	
	
	$sm->assign('has_lead',$au->user_rights->CheckAccess('w',948));	
	
}

if(!isset($stop_popup)) $stop_popup=false;
$sm->assign('stop_popup', $stop_popup);




$header_res=$sm->fetch('header.html');
unset($sm);

$_is_ipad=(strpos($_SERVER['HTTP_USER_AGENT'],'iPad')!==false)
||(strpos($_SERVER['HTTP_USER_AGENT'],'iPhone')!==false)
||(strpos($_SERVER['HTTP_USER_AGENT'],'Android')!==false)
||(strpos($_SERVER['HTTP_USER_AGENT'],'android')!==false)
||(strpos($_SERVER['HTTP_USER_AGENT'],'Mobile')!==false)
||(strpos($_SERVER['HTTP_USER_AGENT'],'mobile')!==false
);
@$smarty->assign('is_ipad',$_is_ipad);

?>