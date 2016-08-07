<?
session_start();
header('Content-type: text/html; charset=windows-1251');

require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/discr_table_objects.php');
require_once('../classes/actionlog.php');

require_once('../classes/supplier_city_group.php');
require_once('../classes/supplier_city_item.php');

require_once('../classes/bdr.class.php'); 



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
	
	
	$flt='';
	/*if(strlen($_GET['branch_id'])>0){
		$flt=' and p.parent_id="'.(int)$_GET['branch_id'].'" ';	
	}*/
	
	$sql='select p.*, r.name as parent_name, q.name as parent_name2,
	count(ch.id) as c_h 
	from bdr_account as p left join bdr_account as r on p.parent_id=r.id 
	left join bdr_account as q on r.parent_id=q.id 
	
	left join bdr_account as ch on p.id=ch.parent_id
	 
	where p.from_kp_in=0 
		and p.p_or_m=1 
		and  p.name LIKE "%'.SecStr(iconv("utf-8","windows-1251",$_GET['term'])).'%" '.$flt.' 
	group by p.id
	
	having c_h=0
	
	
	order by r.name, p.name';
	//echo $sql;
	
	$set=new MysqlSet($sql);
		
	$rs=$set->GetResult();
	$rc=$set->GetResultNumRows();	
	
	$ret_arrs=array();
	
	for($i=0; $i<$rc; $i++){
			$v=mysqli_fetch_array($rs);
			foreach($v as $k=>$v1) $v[$k]=stripslashes($v1);
			
			
			$val=''.$v['code'].' ';
			if($v['parent_name2']!="") $val.=$v['parent_name2'].' - ';
			if($v['parent_name']!="") $val.=$v['parent_name'].' - ';
			$val.=$v['name'];
			
			$ret_arrs[]=array(
				'id'=>$v['id'],
				'label'=>iconv('windows-1251','utf-8', $val),
				'value'=>iconv('windows-1251','utf-8', $v['name'])				
			);
			
			//$ret_arrs[]='{"id":"'.$v['id'].'","label":"'.$val.'","value":"'.$v['name'].'"}';
	 
	
	//$ret_arrs[]='{"id":"66","label":"dddd","value":"ddd1"}';
	
	}
	//$ret='['.implode(', ',$ret_arrs).']';
	$ret=json_encode($ret_arrs);
	
}

//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	

?>