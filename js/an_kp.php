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

require_once('../classes/pl_prodgroup.php');
require_once('../classes/posgroupgroup.php');


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


if(isset($_POST['action'])&&($_POST['action']=='toggle_producers')){
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