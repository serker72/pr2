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

require_once('classes/missionstatusgroup.php');

require_once('classes/missiongroup.php');
require_once('classes/missionitem.php');


require_once('classes/missionfilegroup.php');
require_once('classes/missionfileitem.php');

require_once('classes/missionhistoryitem.php');

require_once('classes/missionstatusitem.php');

require_once('classes/missionitem.php');

require_once('classes/missionexpitem.php');
require_once('classes/missionexpnameitem.php');

require_once('classes/user_s_group.php');

require_once('classes/missionhistorygroup.php');

$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'����� ������� ������������');

$au=new AuthUser();
$result=$au->Auth();


if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}
$mi=new missionItem;
$hi=new missionHistoryItem;
$log=new ActionLog;

$hg=new missionHistoryGroup;

if(!$au->user_rights->CheckAccess('w',594)){
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



$claim=$mi->GetItemById($id);
if($claim===false){
	header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include("404.php");
		die();
}



if(!$au->user_rights->CheckAccess('w',593)&&($claim['sent_user_id']!=0)&&($claim['sent_user_id']!=$result['id'])){
	//�� ��� �����!
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();		
}







if(isset($_POST['makeOrder'])){
	//�������� ������� ������
	
	$params=array();
	$params['txt']=SecStr($_POST['txt']);
	$params['user_id']=$result['id'];
	$params['mission_id']=$id;
	$params['pdate']=time();
	$params['status_id']=abs((int)$_POST['status_id']);
	
	
	
	
	
	$params['is_new']=1;
	
	
	//$hg->ToggleRead(
	$hg->ToggleRead($id,$result['id']);
	
	$history_id=$hi->Add($params);
	
	$_si=new missionStatusItem;
	$_status=$_si->GetItemById($params['status_id']);
	
	$log->PutEntry($result['id'],'������ ������� �� ������������',NULL,594,NULL,'����� ������������: '.$id.' �������� �������: '.$params['txt'].' ������ ������������: '.SecStr($_status['name']),$id);
	
	
	
	$fmi=new MissionFileItem;
   foreach($_POST as $k=>$v){
	  if(eregi("^upload_file_",$k)){
		  //echo eregi_replace("^upload_file_", '', eregi_replace("_tmp$",".tmp",$k)).' = '.$v;
		  
		  $filename=eregi_replace("^upload_file_", '', eregi_replace("_tmp$",".tmp",$k));
		  $fmi->Add(array('history_id'=>$history_id, 'filename'=>SecStr(basename($filename)), 'orig_name'=>SecStr($v)));
		  
		  $log->PutEntry($result['id'], '��������� ���� � ������������', NULL, 594, NULL,'����� ������������: '.$claim['id'].' ��������� ��� �����: '.SecStr(basename($filename)).' ��� �����: '.SecStr($v),$id);
	  }
	}
	
	
	
	
	//����� �� �-��
	$_mexp=new MissionExpItem;
	$_mexn=new MissionExpNameItem;
	//����.
	foreach($_POST as $k=>$v){
	  if(eregi("^exp_id_plan_",$k)){
		   $v=abs((float)str_replace(",",".",$v));
		  
		  $exp_id=abs((int)eregi_replace("^exp_id_plan_", "", $k));
		  
		  $mexp=$_mexp->getitembyfields(array('exp_id'=>$exp_id, 'mission_id'=>$id));
		  if($mexp===false){
				$miid=$_mexp->Add(array('exp_id'=>$exp_id, 'mission_id'=>$id));  
		  }else $miid=$mexp['id'];
		  
		  if(round((float)$v,2)!=$mexp['plan']){
			 $can=true; $_ob_id=594;
			 if($mexp['plan']>0){
				 $can=$au->user_rights->CheckAccess('w',597);
				 $_ob_id=597;
			 }
			 
			 if($can){
				 $_mexp->Edit($miid, array('plan'=> round((float)$v,2)));
				 
				 $mexn=$_mexn->GetItemById($exp_id);
				 $log->PutEntry($result['id'], '��������� �������� ������ �� ������������', NULL, $_ob_id, NULL,'����� ������������: '.$claim['id'].' ��� �������: '.SecStr($mexn['name']).' �����: '.round((float)$v,2),$id);
				 	 
			 }
			 
		  }
	  }
	}
	
	//����.
	foreach($_POST as $k=>$v){
	  if(eregi("^exp_id_fact_",$k)){
		  $exp_id=abs((int)eregi_replace("^exp_id_fact_", "", $k));
		  
		  $mexp=$_mexp->getitembyfields(array('exp_id'=>$exp_id, 'mission_id'=>$id));
		  if($mexp===false){
				$miid=$_mexp->Add(array('exp_id'=>$exp_id, 'mission_id'=>$id));  
		  }else $miid=$mexp['id'];
		  
		  if(round((float)$v,2)!=$mexp['fact']){
			 $can=true; $_ob_id=594;
			 if($mexp['fact']>0){
				 $can=$au->user_rights->CheckAccess('w',597);
				 $_ob_id=597;
			 }
			 
			 if($can){
				 $_mexp->Edit($miid, array('fact'=> round((float)$v,2)));
				 
				 $mexn=$_mexn->GetItemById($exp_id);
				 $log->PutEntry($result['id'], '��������� ����������� ������ �� ������������', NULL, $_ob_id, NULL,'����� ������������: '.$claim['id'].' ��� �������: '.SecStr($mexn['name']).' �����: '.round((float)$v,2),$id);
				 	 
			 }
			 
		  }
	  }
	}
	
	
	
	//������� �����: �����, ������, ��������
	$mi_params=array();
	
	
	//�������� ����������� ����� �������
	
	if(isset($_POST['status_id'])){
		 
		 $_can_change_status=true;
		
		 if($_can_change_status) $mi_params['status_id']=abs((int)$_POST['status_id']); 
		 
	}
	
	
	
	$mmi=new MissionItem;
	$mmi->Edit($id,$mi_params);
	
	foreach($mi_params as $k=>$v){
		
		if(addslashes($claim[$k])!=$v){
		  if($k=='status_id'){
			  $log->PutEntry($result['id'],'������������ ������ ������������',NULL,594,NULL,'����� ������������: '.$id.' ������ ������������: '.SecStr($_status['name']),$id);
			  continue;	
		  }
		  
		 
		
			$log->PutEntry($result['id'],'������������ ������������',NULL,594, NULL, '� ���� '.$k.' ����������� �������� '.$v,$id);		
		}
	}
	
	
	
	header("Location: missionhistory.php?id=".$id);
	die();
	
}



//������ � �������
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->display('top.html');
unset($smarty);



	include('inc/menu.php');
	
	
	
	//������������ ��������
	$smarty = new SmartyAdm;
	
	
	$sm=new SmartyAdm;
	
	
	
	
	//�������� ������
	$sm1=new SmartyAdm;
	
	//$claim['s_user_id']=$result['id'];
	
	$claim['pdate']=date("d.m.Y H:i:s", $claim['pdate']);
	
	$sm1->assign('session_id',session_id());
	
	$sg=new MissionStatusGroup;
	$statuses=$sg->GetItemsArr();
	$status_ids=array(); $status_names=array();
	foreach($statuses as $k=>$v){
		$status_ids[]=$v['id'];
		$status_names[]=$v['name'];	
	}
	$sm1->assign('status_ids',$status_ids);
	$sm1->assign('status_names',$status_names);
	
	
	//����� ������ ����� ��������� �������
	$sm1->assign('can_change_ras',$au->user_rights->CheckAccess('w',597));
	
	//�������
	$_me=new MissionExpGroup;
	$claim['plan']=$_me->CalcPlan($id);
	$claim['fact']=$_me->CalcFact($id);
	
	$ras=$_me->GetItemsByIdArr($id);
	$sm1->assign('ras',$ras);
	
	
	$sm1->assign('claim',$claim);
	
	//����� ������ ������, ���� ���� w ����� �� ��-� 71
	//��� ���� ������!=4
	/*$cannot_change_status=false;
	if($claim['status_id']==4){
		if($au->user_rights->CheckAccess('w',77)){
			
		}else $cannot_change_status=true;
	}
	
	
	if($claim['kind_id']==13){
			
		$sm1->assign('can_back_status',$au->user_rights->CheckAccess('w',118)); //������������� ������ �� ���� ��������
	}else{
		$sm1->assign('can_back_status',$au->user_rights->CheckAccess('w',112)); //����� ��������
	}
	
	
	$sm1->assign('cannot_change_status',$cannot_change_status);
	
	
	//�������� ����������� �� ������ - ��-� 112
	
	//������������ ����������� � ������������ �� ��-� 112 - ����� �������� ������
	$sql='select u.* from user as u inner join user_rights as ur on u.id=ur.user_id where ur.right_id=2 and ur.object_id=112';
	$tset=new MysqlSet($sql);
	$trs=$tset->GetResult();
	$trc=$tset->GetResultNumRows();
	
	$users_list='';
	for($i=0; $i<$trc; $i++){
		$f=mysqli_fetch_array($trs);
		if(strlen($users_list)>0) $users_list.=', ';	
		$users_list.=stripslashes($f['name_s'].' ('.$f['login'].')');
	}
	
	$sm1->assign('cannot_back_status_message','������ �������� ��� ��� ����������.\n����� �� ������ �������� ����� ���������� '.$users_list.'.\n����������, ���������� � ������������ �����������.');
	
	
	//������������ ����������� � ������������ �� ��-� 118 - ������������� ������ �� ���� ��������
	$sql='select u.* from user as u inner join user_rights as ur on u.id=ur.user_id where ur.right_id=2 and ur.object_id=118';
	$tset=new MysqlSet($sql);
	$trs=$tset->GetResult();
	$trc=$tset->GetResultNumRows();
	
	$users_list='';
	for($i=0; $i<$trc; $i++){
		$f=mysqli_fetch_array($trs);
		if(strlen($users_list)>0) $users_list.=', ';	
		$users_list.=stripslashes($f['name_s'].' ('.$f['login'].')');
	}
	
	$sm1->assign('cannot_annul_garant_status_message','������ �������� ��� ��� ����������.\n����� �� ������ �������� ����� ���������� '.$users_list.'.\n����������, ���������� � ������������ �����������.');
	
	//������������ ����������� � ������������ �� ��-� 77 - ��������� ������� ����� ������������
	$sql='select u.* from user as u inner join user_rights as ur on u.id=ur.user_id where ur.right_id=2 and ur.object_id=77';
	$tset=new MysqlSet($sql);
	$trs=$tset->GetResult();
	$trc=$tset->GetResultNumRows();
	
	$users_list='';
	for($i=0; $i<$trc; $i++){
		$f=mysqli_fetch_array($trs);
		if(strlen($users_list)>0) $users_list.=', ';	
		$users_list.=stripslashes($f['name_s'].' ('.$f['login'].')');
	}
	
	$sm1->assign('cannot_change_status_message','������ �������� ��� ��� ����������.\n����� �� ������ �������� ����� ���������� '.$users_list.'.\n����������, ���������� � ������������ �����������.');
	
	
	
	$ug=new UsersSGroup;
	$statuses=$ug->GetItemsArr(0,1);
	$status_ids=array(); $status_names=array();
	foreach($statuses as $k=>$v){
		$status_ids[]=$v['id'];
		$status_names[]=$v['name_s'].' '.$v['login'];	
	}
	$sm1->assign('man_ids',$status_ids);
	$sm1->assign('man_names',$status_names);
	*/
	
	$llg=$sm1->fetch('mission/missionhistory_create_form.html');
	
	
	$sm->assign('log',$llg);
	
	$content=$sm->fetch('mission/missionhistory_create.html');
	
	
	
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