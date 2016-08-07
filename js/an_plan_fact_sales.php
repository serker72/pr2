<?
session_start();
header('Content-type: text/html; charset=windows-1251');


require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/discr_table_objects.php');
require_once('../classes/actionlog.php');
require_once('../classes/supplier_city_item.php');
require_once('../classes/smarty/SmartyAj.class.php');

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


if(($_GET['term'])&&($_GET['action'])&&($_GET['action']=='load_user') )	{
		$limited_user=NULL; $flt='';
	if($au->FltUser($result)){
		//echo 'z';
		$_u_to_u=new UserToUser();
		$u_to_u=$_u_to_u->GetExtendedViewedUserIdsArr($result['id']);
		$limited_user=$u_to_u['sector_ids'];
		$flt=' and u.id in('.implode(', ', $limited_user).') ';
	}
	
	$sql='select u.*, up.name as position_s from user as u left join user_position as up on u.position_id=up.id  where is_active=1 and is_in_plan_fact_sales=1 and (name_s like "%'.iconv("utf-8","windows-1251",SecStr($_GET['term'])).'%" or login like "%'.iconv("utf-8","windows-1251",SecStr($_GET['term'])).'%")  '.$flt.' order by name_s asc ';
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

elseif(($_GET['term'])&&($_GET['action'])&&($_GET['action']=='load_eq') )	{
	
	
	//$sql='select * from user where is_active=1 and is_in_plan_fact_sales=1 and name_s like "%'.iconv("utf-8","windows-1251",SecStr($_GET['term'])).'%" or login like "%'.iconv("utf-8","windows-1251",SecStr($_GET['term'])).'%" order by name_s asc ';
	
	$sql='select p.name, p.id from catalog_position as p inner join pl_position as pl on p.id=pl.position_id
	where p.parent_id=0
	and p.name like "%'.iconv("utf-8","windows-1251",SecStr($_GET['term'])).'%"	
		order by p.name asc';
	
	
	//echo $sql;
	
	$set=new mysqlset($sql);
	$rs=$set->GetResult();
	$rc=$set->GetResultNumRows();
	$ret_arrs=array();
	for($i=0; $i<$rc; $i++){
		$v=mysqli_fetch_array($rs);

		$ret_arrs[]='{"id":"'.$v['name'].'","label":"'.$v['name'].'","value":"'.$v['name'].'"}';
	}
	
	
	$ret='['.implode(', ',$ret_arrs).']';
	
	
}
elseif(isset($_POST['action'])&&($_POST['action']=='add_city_to_form') )	{
	$city_id=abs((int)$_POST['city_id']);
	$_city=new SupplierCityItem;
	$city=$_city->GetFullCity($city_id);
	 
	$sm=new SmartyAj;
	$sm->assign('prefix', $_POST['prefix']);
	$sm->assign('city', $city);
	$ret=$sm->fetch('an_plan_fact_sales/cities_row.html');
}

//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	

?>