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

require_once('classes/filedocfolderitem.php');

require_once('classes/app_contract_history_fileitem.php');

require_once('classes/app_contract_item.php');
require_once('classes/app_contract_historyitem.php');

//открытие почтового вложения



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



$id = abs((int)$_GET['id']);

$_fmi=new AppContractHistoryFileItem;
$fmi=$_fmi->GetItemById(abs((int)$_GET['id']));
if($fmi===false){
	header("HTTP/1.1 404 Not Found");
	header("Status: 404 Not Found");
	include("404.php");
	die();	
}


//посмотреть , имеющий право 65
/*if($au->user_rights->CheckAccess('w',805)){
	
}else{
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}
*/

$_user = new AppContractHistoryItem;
$user = $_user->GetItemById($fmi['history_id']);

$_app_c = new AppContractItem;
$app_c = $_app_c->GetItemById($user['app_contract_id']);

/*
if($fmi['folder_id']==0){
	$_folder_desrc='';
}else{
	$_ff=new FileDocFolderItem(1, $fmi['history_id'], new AppContractHistoryFileItem(1));
	$_ff->SetTablename('app_contrac_file_folder');
	
	$ff=strip_tags($_ff->DrawNavigCli($fmi['folder_id'], '', '/', false));  
	$_folder_desrc=',  папка: '.$ff;
}
 */

//запись в журнал событий
$log=new ActionLog;
$log->PutEntry($result['id'],'скачал файл из ленты комментариев для заявки на договор',NULL, 1150, NULL,SecStr('файл '.$fmi['orig_name'].' из ленты комментариев для заявки на договор '.  $app_c['code'].' '.$_folder_desrc), $app_c['id']);


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