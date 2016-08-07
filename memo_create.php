<?
session_start();
Header("Cache-Control: no-store, no-cache, must-revalidate"); //��� ��������� HTTP/1.1
Header("Pragma: no-cache"); // ��� ��������� HTTP/1.1
Header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT"); // ���� � ����� ��������� ��������
header("Expires: " . date("r")); // ���� � ����� �����, ����� �������� ����� ��������� ����������



require_once('classes/global.php');
require_once('classes/authuser.php');
require_once('classes/smarty/SmartyAdm.class.php');
require_once('classes/smarty/Smarty.class.php');
require_once('classes/discr_table_objects.php');
require_once('classes/actionlog.php');

require_once('classes/memogroup.php');
require_once('classes/memoitem.php');
require_once('classes/memokinditem.php');

require_once('classes/memoincominggroup.php');
require_once('classes/memooutcominggroup.php');
require_once('classes/memoallgroup.php');
require_once('classes/memokindgroup.php');

require_once('classes/memostatusitem.php');
require_once('classes/memohistoryitem.php');

require_once('classes/memouseritem.php');
//require_once('classes/tasksupplieritem.php');
require_once('classes/useritem.php');
require_once('classes/supplieritem.php');
require_once('classes/memofileitem.php');

require_once('classes/memocreator.php');


$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'�������� ��������� �������');


$au=new AuthUser();
$result=$au->Auth();

$log=new ActionLog;

if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

if(!$au->user_rights->CheckAccess('w',730)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}

if(!isset($_GET['kind_id'])){
	if(!isset($_POST['kind_id'])){
 		$kind_id=1;
	/*	header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();		*/	

	}else $kind_id=abs((int)$_POST['kind_id']);
}else $kind_id=abs((int)$_GET['kind_id']);



$log=new ActionLog();

if(!isset($_GET['action'])){
	if(!isset($_POST['action'])){
		$action=0;
	}else $action=abs((int)$_POST['action']);
}else $action=abs((int)$_GET['action']);

$lc=new MemoCreator;

$mi=new MemoItem;
$hi=new Memohistoryitem;
if(isset($_POST['makeOrder'])||isset($_POST['makeSaveOrder'])){
	
	
	// 
	$order_params=array();
	$order_params['txt']=SecStr($_POST['txt']);
	$order_params['topic']=SecStr($_POST['topic']);
	$order_params['pdate']=time();
	 
	
	$lc->ses->ClearOldSessions();
		$order_params['code']=SecStr($lc->GenLogin($result['id'])); //SecStr($_POST['code']);
	//$order_params['code']=SecStr($_POST['code']);
	
	$order_params['kind_id']=1; //abs((int)$_POST['kind_id']);
	
  	$order_params['pdate']=DatefromdmY($_POST['pdate']);
	 
  	
  	$order_params['user_id']=$result['id'];
	$order_params['status_id']=18; //$order_params['pdate'];
	
	$order_params['manager_id']=abs((int)$_POST['manager_id']);
	
	
	$_claim_kind_item=new MemoKindItem;
	$ckind=$_claim_kind_item->GetItemById($order_params['kind_id']);
	
	
	
	
	
	$claim_id=$mi->Add($order_params);
	
	
	
	$log->PutEntry($result['id'],'������ ��������� �������',NULL,730,NULL,'����� ��������� �������: '.$order_params['code'].' �������� ��������� �������:'.$order_params['txt'].' ��� ��������� �������: '.SecStr($ckind['name']),$claim_id);
	
	if($au->user_rights->CheckAccess('w',735)){
		if(isset($_POST['is_confirmed'])){
			 
			$mi->Edit($claim_id, array('is_confirmed'=>1, 'confirm_pdate'=>time(), 'user_confirm_id'=>$result['id']), true, $result);
			$log->PutEntry($result['id'],'�������� ���������� ��������� �������',NULL,730, NULL, NULL,$claim_id);	
				
		}else{
			$log->PutEntry($result['id'],'��������� ��������� ���������� ��������� �������',NULL,730, NULL, NULL,$claim_id);	
		}
	}
	
	//��������� �����
	if(isset($_POST['old_doc_id'])&&isset($_POST['has_files'])&&($_POST['has_files']==1)){
		$mi->CopyFiles(	abs((int)$_POST['old_doc_id']), $claim_id, $result['id']);
	}
	
	 
	
	//����� ������
	
	
	$fmi=new memoFileItem;
   foreach($_POST as $k=>$v){
	  if(eregi("^upload_file_",$k)){
		  //echo eregi_replace("^upload_file_", '', eregi_replace("_tmp$",".tmp",$k)).' = '.$v;
		  
		  $filename=eregi_replace("^upload_file_", '', eregi_replace("_tmp$",".tmp",$k));
		  $fmi->Add(array(
		  	'bill_id'=>$claim_id, 
		  					'filename'=>SecStr(basename($filename)), 
							'orig_name'=>SecStr($v),
							'user_id'=>$result['id'],
							'pdate'=>time() 
		  ));
		  $log->PutEntry($result['id'], '��������� ���� � ��������� �������', NULL, 731, NULL,'����� ��������� �������: '.$order_params['code'].' ��������� ��� �����: '.SecStr(basename($filename)).'  ��� �����: '.SecStr($v),$claim_id);
		  
	  }
	}
	
	
	if(isset($_POST['makeSaveOrder'])){
		header("Location: memo_my_history.php?id=".$claim_id);
	}else header("Location: memos.php");
	die();
	
	header("Location: memos.php");
	die();
	
}

//������ � �������
//������ � �������
$stop_popup=true;
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->display('top.html');
unset($smarty);


$_menu_id=60;

	include('inc/menu.php');
	
	
	
	//������������ ��������
	$smarty = new SmartyAdm;
	
	
	$sm=new SmartyAdm;
	
	
	
	//�������� ������
	$sm1=new SmartyAdm;
	
	
	$sm1->assign('session_id',session_id());
	
	
	//$lc->ses->ClearOldSessions();
		
	//$sm1->assign('code', $lc->GenLogin($result['id']));
	
	$sm1->assign('kind_id', $kind_id);
	
	
	
	$ckg=new memoKindGroup;
	$sm1->assign('items',$ckg->GetItemsArr());
	
	$sm1->assign('now',date('d.m.Y'));
	
	//$sm1->assign('is_dealer',($result['group_id']==3));
	
	//���� ��������
	
	
	
		//�������� ������
   if(isset($_GET['copyfrom'])){
	  $old_doc=$mi->getitembyid(abs((int)$_GET['copyfrom']));
	  
	  foreach($old_doc as $k=>$v) $old_doc[$k]=stripslashes($v);
	  
	  
	  $_cu=new UserSItem();
	  $manager=$_cu->GetItemById($old_doc['manager_id']);
	//  $sm1->assign('manager_string', $manager['name_s']);
	  $sm1->assign('has_files', abs((int)$_GET['has_files']));
	  
	//  $sm1->assign('manager_id', $old_doc['manager_id']);
	  $sm1->assign('old_doc', $old_doc);	
	  
   }else{
			
	
		
   }
   
   $sm1->assign('manager_id', $result['id']);
   $sm1->assign('manager_string', $result['name_s']);
	
	
	
	//������ �����������
	 
	$_usg=new UsersSGroup; $dec_us=new DBDecorator;
	$limited_user=NULL;
	if($au->FltUser($result)){
		//echo 'z';
		$_u_to_u=new UserToUser();
		$u_to_u=$_u_to_u->GetExtendedViewedUserIdsArr($result['id']);
		$limited_user=$u_to_u['sector_ids'];
		$dec_us->AddEntry(new SqlEntry('u.id', NULL, SqlEntry::IN_VALUES, NULL,$limited_user));	
	}
	
	//�������� �� ������������� ������???
	$_ui=new UserSItem;
	$user=$_ui->GetItemById($result['id']);
	$_upos=new UserPosItem;
	$upos=$_upos->GetItemById($user['position_id']);
	if($upos['is_ruk_otd']==1){
		//������ ����������� �� ����������� ������ ����� ������
		$dec_us->AddEntry(new SqlEntry('u.department_id', $user['department_id'], SqlEntry::E));	
	}
	$dec_us->AddEntry(new SqlOrdEntry('u.name_s',SqlOrdEntry::ASC));
	
	
	
	$managers=$_usg->GetItemsByDecArr($dec_us);
	$sm1->assign('can_modify_manager',$au->user_rights->CheckAccess('w',1145)||($upos['is_ruk_otd']==1) );
	$sm1->assign('managers', $managers);
	
	
	
	$sm1->assign('can_confirm',$au->user_rights->CheckAccess('w',735) );
	
	
	
	
	$llg=$sm1->fetch('memo/memos_create_form.html');
	
	
	$sm->assign('log',$llg);
	
	
	
	$content=$sm->fetch('memo/memos_create.html');
	
	
	$smarty->assign('fast_menu', $menu_arr_fast);
	  $smarty->assign('main_menu',$menu_res);
	$smarty->assign('content',$content);
	$smarty->display('page.html');
	unset($smarty);


$smarty = new SmartyAdm;

//������ � �������
require_once('inc/footer.php');
if(isset($footer_res)){
	$smarty->assign('footer',$footer_res);
}else $smarty->assign('footer','');

$smarty->display('bottom.html');
unset($smarty);
?>