<?
session_start();
header('Content-type: text/html; charset=windows-1251');


require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/discr_table_objects.php');
require_once('../classes/actionlog.php');

require_once('../classes/user_to_user.php');


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


if($_GET['term']) 	{
		$limited_user=NULL; $flt='';
	if($au->FltUser($result)){
		//echo 'z';
		$_u_to_u=new UserToUser();
		$u_to_u=$_u_to_u->GetExtendedViewedUserIdsArr($result['id']);
		$limited_user=$u_to_u['sector_ids'];
		$flt=' and id in('.implode(', ', $limited_user).') ';
	}
	
	
	$sql='select * from user where (name_s like "%'.iconv("utf-8","windows-1251",SecStr($_GET['term'])).'%" or login like "%'.iconv("utf-8","windows-1251",SecStr($_GET['term'])).'%") '.$flt.' order by name_s asc ';
	//echo $sql;
	
	$set=new mysqlset($sql);
	$rs=$set->GetResult();
	$rc=$set->GetResultNumRows();
	$ret_arrs=array();
	for($i=0; $i<$rc; $i++){
		$v=mysqli_fetch_array($rs);

		$ret_arrs[]='{"id":"'.$v['name_s'].'","label":"'.$v['name_s'].', '.$v['position_s'].'","value":"'.$v['name_s'].'"}';
	}
	
	
	$ret='['.implode(', ',$ret_arrs).']';
	
	
}
//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	

?>