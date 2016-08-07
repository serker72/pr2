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


require_once('classes/memofileitem.php');
 
 
require_once('classes/memoitem.php');


//открытие вложения к заказу



$au=new AuthUser();
$result=$au->Auth();

if(($result===NULL)||(!$au->CheckOrgId())){
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

$_oif=new MemoFileItem;
$oif=$_oif->GetItemById($id);
if($oif===false){
	header("HTTP/1.1 404 Not Found");
	header("Status: 404 Not Found");
	include("404.php");
	die();	
}

 
$_oi=new MemoItem;
$oi=$_oi->GetItemById($oif['bill_id']);
if($oi===false){
	header("HTTP/1.1 404 Not Found");
	header("Status: 404 Not Found");
	include("404.php");
	die();	
}



//посмотреть может- сотрудник, имеющий право 93
if($au->user_rights->CheckAccess('w',731)){
	
}else{
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}


//запись в журнал событий
require_once('classes/filedocfolderitem.php');
$_user=new MemoItem;
$user=$_user->GetItemById($oif['bill_id']);

if($fmi['folder_id']==0){
	$_folder_desrc='';
}else{
	$_ff=new FileDocFolderItem(1, $oif['bill_id'], new MemoFileItem(1));
	$_ff->SetTablename('memo_file_folder');
	
	$ff=strip_tags($_ff->DrawNavigCli($oif['folder_id'], '', '/', false));  
	$_folder_desrc=',  папка: '.$ff;
}
 

$log=new ActionLog;
$log->PutEntry($result['id'],'скачал файл служебной записки',NULL,731, NULL,SecStr('файл '.$oif['orig_name'].' для '.  $user['code'].' '.$user['name'].$_folder_desrc),$user['id']);



$filename=$_oif->GetStoragePath().$oif['filename'];
if(is_file($filename)){
	
	 header("HTTP/1.1 200 OK");
	  header("Connection: close");
	  header("Content-Type: application/octet-stream");
	  header("Accept-Ranges: bytes");
	  header("Content-Disposition: Attachment; filename=".eregi_replace("[[:space:]]","_",iconv('windows-1251','utf-8',$oif['orig_name'])));
	  header("Content-Length: ".filesize($_oif->GetStoragePath().$oif['filename'])); 
	 readfile($_oif->GetStoragePath().$oif['filename']); 	
}else{
	header("HTTP/1.1 404 Not Found");
	header("Status: 404 Not Found");
	include("404.php");
	die();	
}



exit(0);
?>