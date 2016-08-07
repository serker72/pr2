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

require_once('../classes/rl/rl_man.php');

require_once('../classes/pl_prodgroup.php');


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
	 
	$_rl=new RLMan;
	
	//фильтровать оборудование из закрытых категорий, пр-лей
	$flt= '';
	$restricted_prods=$_rl->GetBlockedItemsArr($result['id'], 34, 'w', 'pl_producer', 0);
	if(count($restricted_prods)>0) $flt.=' and producer_id not in('.implode(', ', $restricted_prods).') ';
	
	//запросить из базы список закрытых для пол-ля категорий
	$restricted_cats=$_rl->GetBlockedItemsArr($result['id'], 1, 'w', 'catalog_group', 0);
	if(count($restricted_cats)>0){
		//если закрыта подгруппа
		$flt.=' and group_id not in('.implode(', ', $restricted_cats).') ';
		//если закрыта группа - учесть ее подгруппы
		$flt.=' and group_id not in(select id from catalog_group where parent_group_id in('.implode(', ', $restricted_cats).') )';
	}
	
	 
	$sql='select distinct p.name
		from catalog_position as p
		inner join pl_position as pl on p.id=pl.position_id 
		where 
		p.parent_id=0 and
		p.name like "%'.iconv("utf-8","windows-1251",SecStr($_GET['term'])).'%" 
		'.$flt.'
		
		order by p.name asc ';
	//echo $sql;
	
	$set=new mysqlset($sql);
	$rs=$set->GetResult();
	$rc=$set->GetResultNumRows();
	$ret_arrs=array();
	for($i=0; $i<$rc; $i++){
		$v=mysqli_fetch_array($rs);

		//$ret.=$v['full_name']."|".$v['full_name']."\n";
		$ret_arrs[]='{"id":"'.$v['name'].'","label":"'.$v['name'].'","value":"'.$v['name'].'"}';
	}
	
	//$ret="Choice1|Choice1\n";
	
	$ret='['.implode(', ',$ret_arrs).']';
	
	
}

elseif(isset($_POST['action'])&&($_POST['action']=='toggle_producers')){
	 $_prg=new PlProdGroup;
	 
	 $group_id=abs((int)$_POST['group_id']);
	 
	 $ret=$_prg->GetItemsByIdOpt($group_id, 0, 'name', true, '-все-');
	
	
}
elseif(isset($_POST['action'])&&($_POST['action']=='toggle_two_groups')){
	 $_pgg=new PosGroupGroup;
	 
	 $group_id=abs((int)$_POST['group_id']);
	 $producer_id=abs((int)$_POST['producer_id']);
	 
	 $ret=$_pgg->GetItemsOptByCategoryProducer($group_id, $producer_id,0, 'name', true, '-все-');
	
	
}

//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	

?>