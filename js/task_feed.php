<?
session_start();
header('Content-type: text/html; charset=windows-1251');

require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/discr_table_objects.php');
require_once('../classes/actionlog.php');

require_once('../classes/suppliersgroup.php');
require_once('../classes/user_s_group.php');

require_once('../classes/taskgroup.php');


require_once('../classes/taskallgroup.php');
require_once('../classes/taskincominggroup.php');
require_once('../classes/taskoutcominggroup.php');


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


$mode=$_GET['mode'];

switch($mode){
	case "_1":
		$_tg=new TaskIncomingGroup;
	break;	
	case "_2":
		$_tg=new TaskOutcomingGroup;
	break;	
	case "_3":
		$_tg=new TaskAllGroup;
	break;	
	
	default:
		$_tg=new TaskIncomingGroup;
	break;	
	
}





//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	

?>