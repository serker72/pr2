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
	/*$supplier_id=abs((int)$_POST['supplier_id']);
	$current_id=abs((int)$_POST['current_id']);
	
	$_bd=new BDetailsGroup;
	$arr=$_bd->GetItemsByIdArr($supplier_id,$current_id);*/
	
	//получим список позиций по фильтру
	$_pg=new SuppliersGroup;
	
	$dec=new DBDecorator;
	
	
	
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['code'])))>0) $dec->AddEntry(new SqlEntry('p.code',SecStr(iconv("utf-8","windows-1251",$_POST['code'])), SqlEntry::LIKE));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['full_name'])))>0) $dec->AddEntry(new SqlEntry('p.full_name',SecStr(iconv("utf-8","windows-1251",$_POST['full_name'])), SqlEntry::LIKE));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['inn'])))>0) $dec->AddEntry(new SqlEntry('p.inn',SecStr(iconv("utf-8","windows-1251",$_POST['inn'])), SqlEntry::LIKE));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['kpp'])))>0) $dec->AddEntry(new SqlEntry('p.kpp',SecStr(iconv("utf-8","windows-1251",$_POST['kpp'])), SqlEntry::LIKE));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['legal_address'])))>0) $dec->AddEntry(new SqlEntry('p.legal_address',SecStr(iconv("utf-8","windows-1251",$_POST['legal_address'])), SqlEntry::LIKE));
	
	$dec->AddEntry(new SqlEntry('p.is_org',1, SqlEntry::NE));
	
	if(isset($_POST['except_ids'])&&is_array($_POST['except_ids'])){
		foreach($_POST['except_ids'] as $k=>$v){
			$dec->AddEntry(new SqlEntry('p.id',abs((int)$v), SqlEntry::NE));	
		}
		
	}
	
	
	$ret=$_pg->GetItemsForBill('task/suppliers_list.html',  $dec,true,$all7,$result);
	

	
}elseif(isset($_POST['action'])&&($_POST['action']=="transfer_suppliers")){
	$_pg=new SuppliersGroup;
	
	$dec=new DBDecorator;
	
	$dec->AddEntry(new SqlEntry('p.is_org',1, SqlEntry::NE));
	
	
	$dec->AddEntry(new SqlEntry('p.id',NULL, SqlEntry::IN_VALUES, NULL, $_POST['selected_ids']));
	
	$ret=$_pg->GetItemsForBill('task/suppliers_list_in_card.html',  $dec,true,$all7,$result);
		
}
elseif(isset($_POST['action'])&&($_POST['action']=="find_users")){
	/*$supplier_id=abs((int)$_POST['supplier_id']);
	$current_id=abs((int)$_POST['current_id']);
	
	$_bd=new BDetailsGroup;
	$arr=$_bd->GetItemsByIdArr($supplier_id,$current_id);*/
	
	//получим список позиций по фильтру
	$_pg=new UsersSGroup;
	
	$dec=new DBDecorator;
	
	
	
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['login'])))>0) $dec->AddEntry(new SqlEntry('p.login',SecStr(iconv("utf-8","windows-1251",$_POST['login'])), SqlEntry::LIKE));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['name_s'])))>0) $dec->AddEntry(new SqlEntry('p.name_s',SecStr(iconv("utf-8","windows-1251",$_POST['name_s'])), SqlEntry::LIKE));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['position_s'])))>0) $dec->AddEntry(new SqlEntry('up.name',SecStr(iconv("utf-8","windows-1251",$_POST['position_s'])), SqlEntry::LIKE));
	
		
	if(isset($_POST['except_ids'])&&is_array($_POST['except_ids'])){
		foreach($_POST['except_ids'] as $k=>$v){
			$dec->AddEntry(new SqlEntry('p.id',abs((int)$v), SqlEntry::NE));	
		}
		
	}
	
	
	$ret=$_pg->GetItemsForBill('task/users_list.html',  $dec,true,$all7,$result);
	

	
}elseif(isset($_POST['action'])&&($_POST['action']=="transfer_users")){
	$_pg=new UsersSGroup;
	
	$dec=new DBDecorator;
	
	//$dec->AddEntry(new SqlEntry('p.is_org',1, SqlEntry::NE));
	
	
	$dec->AddEntry(new SqlEntry('p.id',NULL, SqlEntry::IN_VALUES, NULL, $_POST['selected_ids']));
	
	$ret=$_pg->GetItemsForBill('task/users_list_in_card.html',  $dec,true,$all7,$result);
		
}

//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	

?>