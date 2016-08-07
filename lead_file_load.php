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


require_once('classes/lead_fileitem.php');

require_once('classes/lead.class.php');

//�������� ��������� ��������



$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

if(!isset($_GET['id'])){
	header("HTTP/1.1 404 Not Found");
	header("Status: 404 Not Found");
	include("404.php");
	die();	
}



$id=abs((int)$_GET['id']);

$_fmi=new LeadFileItem;
$fmi=$_fmi->GetItemById(abs((int)$_GET['id']));
if($fmi===false){
	header("HTTP/1.1 404 Not Found");
	header("Status: 404 Not Found");
	include("404.php");
	die();	
}


//���������� , ������� ����� 65
/*if($au->user_rights->CheckAccess('w',805)){
	
}else{
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}
*/
//������ � ������ �������
require_once('classes/filedocfolderitem.php');
$_user=new Lead_Item;
$user=$_user->GetItemById($fmi['bill_id']);

if($fmi['folder_id']==0){
	$_folder_desrc='';
}else{
	$_ff=new FileDocFolderItem(1, $fmi['sched_id'], new LeadFileItem(1));
	$_ff->SetTablename('tender_file_folder');
	
	$ff=strip_tags($_ff->DrawNavigCli($fmi['folder_id'], '', '/', false));  
	$_folder_desrc=',  �����: '.$ff;
}
 

$log=new ActionLog;
$log->PutEntry($result['id'],'������ ���� ����',NULL, 950, NULL,SecStr('���� '.$fmi['orig_name'].' ��� ���� '.  $user['code'].' '.$_folder_desrc),$user['id']);


$filename=$_fmi->GetStoragePath().$fmi['filename'];
if(is_file($filename)){
	
	 header("HTTP/1.1 200 OK");
	  header("Connection: close");
	  header("Content-Type: application/octet-stream");
	  header("Accept-Ranges: bytes");
	  header("Content-Disposition: Attachment; filename=\"".eregi_replace("[[:space:]]","_",$fmi['orig_name'])."\"");
	  header("Content-Length: ".filesize($_fmi->GetStoragePath().$fmi['filename'])); 
	 readfile($_fmi->GetStoragePath().$fmi['filename']); 	
}else{
	header("HTTP/1.1 404 Not Found");
	header("Status: 404 Not Found");
	include("404.php");
	die();	
}
exit(0);
?>