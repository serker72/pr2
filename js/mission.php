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
require_once('../classes/supplieritem.php');
require_once('../classes/opfitem.php');

require_once('../classes/supplier_cities_item.php');
require_once('../classes/supplier_cities_group.php');


require_once('../classes/supplier_city_item.php');
require_once('../classes/supplier_region_item.php');
require_once('../classes/supplier_district_item.php');


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


if(isset($_POST['action'])&&($_POST['action']=="find_suppliers")){
	
	//получим список позиций по фильтру
	$_pg=new SuppliersGroup;
	
	$dec=new DBDecorator;
	
	
	
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['code'])))>0) $dec->AddEntry(new SqlEntry('p.code',SecStr(iconv("utf-8","windows-1251",$_POST['code'])), SqlEntry::LIKE));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['full_name'])))>0) $dec->AddEntry(new SqlEntry('p.full_name',SecStr(iconv("utf-8","windows-1251",$_POST['full_name'])), SqlEntry::LIKE));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['inn'])))>0) $dec->AddEntry(new SqlEntry('p.inn',SecStr(iconv("utf-8","windows-1251",$_POST['inn'])), SqlEntry::LIKE));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['kpp'])))>0) $dec->AddEntry(new SqlEntry('p.kpp',SecStr(iconv("utf-8","windows-1251",$_POST['kpp'])), SqlEntry::LIKE));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['legal_address'])))>0) $dec->AddEntry(new SqlEntry('p.legal_address',SecStr(iconv("utf-8","windows-1251",$_POST['legal_address'])), SqlEntry::LIKE));
	
	$dec->AddEntry(new SqlEntry('p.is_org',1, SqlEntry::NE));
	
	/*if(isset($_POST['except_ids'])&&is_array($_POST['except_ids'])){
		foreach($_POST['except_ids'] as $k=>$v){
			$dec->AddEntry(new SqlEntry('p.id',abs((int)$v), SqlEntry::NE));	
		}
		
	}
	*/
	
	$ret=$_pg->GetItemsForBill('mission/suppliers_list.html',  $dec,true,$all7,$result, abs((int)$_POST['selected_supplier_id']));
	

	
}elseif(isset($_POST['action'])&&($_POST['action']=="transfer_suppliers")){
	$_si=new SupplierItem;
	$_opf=new OpfItem;
	
	
	$si=$_si->getitembyid(abs((int)$_POST['selected_supplier_id']));
	
	$opf=$_opf->getitembyid($si['opf_id']);
	
	$ret=$opf['name'].' '.$si['full_name'];
	
	
	
}
elseif(isset($_GET['action'])&&($_GET['action']=="load_city")){
	$_si=new SupplierCitiesItem;
	
	$si=$_si->GetOne(abs((int)$_GET['selected_supplier_id']));
	
	if($si===false){
		$ret='{"id":"0", "txt":""}';	
	}else{
		$ret='{"id":"'.$si['id'].'", "txt":"'.SecStr($si['name'].', '.$si['okrug_name'].', '.$si['region_name']).'"}';
		
	}
	
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="find_city")){
	
	$_pg=new SupplierCitiesGroup;
	
	
	$sm=new SmartyAj;
	
	$pg=$_pg->GetItemsByIdArr(abs((int)$_POST['supplier_id']), abs((int)$_POST['selected_city_id']));
	
	$sm->assign('pos', $pg);
	
	
	$ret=$sm->fetch('mission/cities_list.html');
	
	
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="transfer_city")){
	
	$city_id=abs((int)$_POST['selected_city_id']);
	$supplier_id=abs((int)$_POST['supplier_id']);
	
	$_ci=new SupplierCityItem;
	$_ri=new SupplierRegionItem;
	$_di=new SupplierDistrictItem;
	
	$ci=$_ci->GetItemById($city_id);
	$ri=$_ri->GetItemById($ci['region_id']);
	$di=$_di->GetItemById($ci['district_id']);
	
	$ret=$ci['name'].', '.$di['name'].', '.$ri['name'];
	
}

//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	

?>