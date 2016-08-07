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
require_once('classes/tender_fileitem2.php');
require_once('classes/tender_filegroup2.php');

 

require_once('classes/tender.class.php');



if(!isset($_GET['folder_id'])){
	if(!isset($_POST['folder_id'])){
		$folder_id=0;
	}else $folder_id=abs((int)$_POST['folder_id']); 
}else $folder_id=abs((int)$_GET['folder_id']);	


$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'����� �������');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

if(!isset($_GET['bill_id'])){
	if(!isset($_POST['bill_id'])){
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include("404.php");
		die();		
	}else $bill_id=abs((int)$_POST['bill_id']);
	
}else $bill_id=abs((int)$_GET['bill_id']); 

$_user=new Tender_Item;
$user=$_user->GetItemById($bill_id);
if(($user===false)){
	header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include("404.php");
		die();	
}

$log=new ActionLog;



if(!$au->user_rights->CheckAccess('w',931)){ //705)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}



//������ ������� 
$_folder_desrc='';
if($folder_id==0){
	$_folder_desrc='';
}else{
	$_ff=new FileDocFolderItem(2, $bill_id, new tenderFileItem2(2));
	$_ff->SetTablename('tender_file_folder');
	
	$ff=strip_tags($_ff->DrawNavigCli($folder_id, '', '/', false)); 
	$_folder_desrc=' �����: '.$ff;
}
	
$log->PutEntry($result['id'],'������ ������ ������ �������',NULL,931, NULL, $user['code'].$_folder_desrc,$bill_id);	




//�������� ������
if(isset($_GET['action'])&&($_GET['action']==2)){
	if(!$au->user_rights->CheckAccess('w',931)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}
	
	if(isset($_GET['id'])) $id=abs((int)$_GET['id']);
	else{
			header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			include("404.php");
			die();	
	}
	
	$_file=new TenderFileItem2;
	$file=$_file->GetItemById($id);
	
	if($file!==false){
		$_file->Del($id);
		
		$log->PutEntry($result['id'],'������ ����',NULL,931,NULL,'��� ����� '.SecStr($file['orig_name']),$bill_id);
	}
	
	header("Location: tender_files2.php?bill_id=".$bill_id);
	die();
}



//�������� �����
if(isset($_GET['action'])&&($_GET['action']==3)){
	
	if(!$au->user_rights->CheckAccess('w',931)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}	
	
	
	
	if(isset($_GET['id'])) $id=abs((int)$_GET['id']);
	else{
			header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			include("404.php");
			die();	
	}
	
	$_ff=new FileDocFolderItem(2, $bill_id, new TenderFileItem2(2));
	$_ff->SetTablename('tender_file_folder');
	$_ff->SetDocIdName('doc_id');
		
	
	
	$file=$_ff->GetItemById($id);
	
	
	
	if($file!==false){
		$_ff->Del($id,$file,$result,931);
		
		$log->PutEntry($result['id'],'������ �����',NULL,931,NULL,'��� ����� '.SecStr($file['filename']));
		//echo 'zzz';
	}
	
	header("Location:tender_files2.php?bill_id=".$bill_id.'&folder_id='.$folder_id);
	die();
}


//����������� ����� � ������
if(isset($_GET['action'])&&($_GET['action']==4)){
	
	if(!$au->user_rights->CheckAccess('w',931)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}	
	
	
	
	//
	$move_folder_id=abs((int)$_GET['move_folder_id']);
	
	//���������� �����
	$files=$_GET['check_file'];
	
	if(is_array($files)&&(count($files)>0)){
		foreach($files as $v){	
	
	
		  $_file=new TenderFileItem2(2);
		  
		 
		  $_file->Edit($v, array('folder_id'=>$move_folder_id));
		  
		  //������ � ������, ���������...
	
		}
	}
	
	//���������� �����
	$files=$_GET['fcheck_file'];
	
	
	if(is_array($files)&&(count($files)>0)){
		foreach($files as $v){	
	
	
		 	
			$_ff=new FileDocFolderItem(2, $bill_id, new TenderFileItem2(2));
	$_ff->SetTablename('tender_file_folder');
	$_ff->SetDocIdName('doc_id');
		
		    $_ff->Edit($v, array('parent_id'=>$move_folder_id), NULL,$result, 931);
			
			//������ � ������, ���������...
		}
	}
	
	
	header("Location:tender_files2.php?bill_id=".$bill_id.'&folder_id='.$folder_id);
	die();
}

//������ � �������
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->display('top.html');
unset($smarty);


//$_menu_id=NULL;
	include('inc/menu.php');
	
	
	
	//������������ ��������
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	
	
	
	
	$decorator=new DBDecorator;
	
	$decorator->AddEntry(new SqlOrdEntry('pdate',SqlOrdEntry::DESC));
	
	
	if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
	else $from=0;
	
	if(isset($_GET['to_page'])) $to_page=abs((int)$_GET['to_page']);
	else $to_page=ITEMS_PER_PAGE;
	
	
	$decorator->AddEntry(new SqlEntry('folder_id',$folder_id, SqlEntry::E));
	$decorator->AddEntry(new UriEntry('folder_id',$folder_id));
	
	$decorator->AddEntry(new UriEntry('bill_id',$bill_id));
	
	$ffg=new TenderFileGroup2(2, $bill_id,  new FileDocFolderItem(2,  $bill_id, new TenderFileItem2(2)));
	
	 
	
	$filetext=$ffg->ShowFiles('doc_file/list.html', $decorator,$from,$to_page, 'tender_files2.php', 'tender_file2.html', 'swfupl-js/tender_files2.php', 
	
	 
	  $au->user_rights->CheckAccess('w',931),
	 $au->user_rights->CheckAccess('w',931),
	 $au->user_rights->CheckAccess('w',931),
	$folder_id, 
	 $au->user_rights->CheckAccess('w',931), 
	 $au->user_rights->CheckAccess('w',931),
	  $au->user_rights->CheckAccess('w',931),
	  $au->user_rights->CheckAccess('w',931),
	 
	
	'',
	 $au->user_rights->CheckAccess('w',931),
	 $result
	
	 );
	
	
	
	$sm->assign('log',$filetext);
	
	foreach($user as $k=>$v){
		$user[$k]=stripslashes($v);
	}
	
	
	$sm->assign('bill',$user);
	$content=$sm->fetch('tender/files.html');
	
	
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