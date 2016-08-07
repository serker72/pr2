<?
session_start();
header('Content-type: text/html; charset=windows-1251');

require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/discr_table_objects.php');
require_once('../classes/actionlog.php');

 
$au=new AuthUser();
$result=$au->Auth();
$log=new ActionLog;

if($result===NULL){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();		
}
 

$ret='';
if(isset($_POST['action'])&&($_POST['action']=="log")){
	//произвольная запись в журнал
	$object_id=abs((int)$_POST['object_id']);
	$description=iconv("utf-8","windows-1251", $_POST['description']);
	$details=iconv("utf-8","windows-1251", $_POST['details']);
	
	
	 
		
	$log->PutEntry($result['id'], $description,NULL,$object_id,NULL,$details);	
					  	
	
}

//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>