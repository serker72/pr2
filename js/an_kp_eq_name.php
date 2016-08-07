<?
session_start();
header('Content-type: text/html; charset=windows-1251');



require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/discr_table_objects.php');
require_once('../classes/actionlog.php');

require_once('../classes/pl_positem.php');
require_once('../classes/positem.php');
require_once('../classes/posgroupitem.php');
require_once('../classes/posgroupgroup.php');

require_once('../classes/posdimitem.php');
require_once('../classes/posdimgroup.php');
require_once('../classes/posgroup.php');

require_once('../classes/bdetailsgroup.php');
require_once('../classes/bdetailsitem.php');
require_once('../classes/suppliersgroup.php');
require_once('../classes/supplieritem.php');

require_once('../classes/billitem.php');
require_once('../classes/billgroup.php');


require_once('../classes/billpospmformer.php');

require_once('../classes/maxformer.php');
require_once('../classes/opfitem.php');


require_once('../classes/billnotesgroup.php');
require_once('../classes/billnotesitem.php');
require_once('../classes/billpositem.php');
require_once('../classes/billpospmitem.php');
require_once('../classes/posdimitem.php');
require_once('../classes/suppliersgroup.php');

require_once('../classes/billdates.php');
require_once('../classes/billreports.php');
require_once('../classes/billprepare.php');

require_once('../classes/user_s_item.php');


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

//if($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
if($_GET['term']) 	{
	 
	$sql='select distinct e.name as eq_name 
		from kp as p 
		inner join kp_position as kpp on kpp.kp_id=p.id and kpp.parent_id=0    
		inner join catalog_position as e on e.id=kpp.position_id 
		where p.org_id='.$result['org_id'].' and p.is_confirmed_price=1 and e.name like "%'.iconv("utf-8","windows-1251",SecStr($_GET['term'])).'%" order by eq_name asc ';
	//echo $sql;
	
	$set=new mysqlset($sql);
	$rs=$set->GetResult();
	$rc=$set->GetResultNumRows();
	$ret_arrs=array();
	for($i=0; $i<$rc; $i++){
		$v=mysqli_fetch_array($rs);

		//$ret.=$v['full_name']."|".$v['full_name']."\n";
		$ret_arrs[]='{"id":"'.$v['eq_name'].'","label":"'.$v['eq_name'].'","value":"'.$v['eq_name'].'"}';
	}
	
	//$ret="Choice1|Choice1\n";
	
	$ret='['.implode(', ',$ret_arrs).']';
	
	
}

//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	

?>