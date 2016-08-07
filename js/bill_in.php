<?
session_start();
header('Content-type: text/html; charset=windows-1251');

require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/discr_table_objects.php');
require_once('../classes/actionlog.php');

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

require_once('../classes/bill_in_item.php');

require_once('../classes/billgroup.php');

require_once('../classes/bill_in_posgroup.php');

require_once('../classes/billpospmformer.php');

require_once('../classes/maxformer.php');
require_once('../classes/opfitem.php');
require_once('../classes/bill_in_group.php');

require_once('../classes/billnotesgroup.php');
require_once('../classes/billnotesitem.php');
require_once('../classes/billpositem.php');
require_once('../classes/billpospmitem.php');
require_once('../classes/posdimitem.php');

require_once('../classes/billdates.php');
require_once('../classes/billreports.php');
require_once('../classes/billprepare.php');

require_once('../classes/user_s_item.php');

require_once('../classes/pl_disgroup.php');
require_once('../classes/pl_disitem.php');
require_once('../classes/pl_dismaxvalgroup.php');
require_once('../classes/pl_dismaxvalitem.php');

require_once('../classes/pl_posgroup.php');
require_once('../classes/pl_positem.php');

require_once('../classes/posgroupgroup.php');

require_once('../classes/pl_positem.php');
require_once('../classes/pl_posgroup.php');
require_once('../classes/pl_dismaxvalitem.php');
require_once('../classes/pl_disitem.php');

require_once('../classes/pl_posgroup_forbill.php');
require_once('../classes/billposgroup_forbill.php');

require_once('../classes/supcontract_item.php');
require_once('../classes/supcontract_group.php');
require_once('../classes/acc_in_item.php');

$au=new AuthUser();
$result=$au->Auth();
$log=new ActionLog;

if($result===NULL){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();		
}

function FindIndex($value, $array){
	$r=-1;
	if(count($array)>0) foreach($array as $k=>$v){
		if($v==$value){
			$r=$k;
			break;	
		}
	}
	return $r;
}
function FindIndex2($value, $value2, $array, $array2){
	$r=-1;
	if(count($array)>0) foreach($array as $k=>$v){
		if(($v==$value)&&($array2[$k]==$value2)){
			$r=$k;
			break;	
		}
	}
	return $r;
}
function InRestrictedPair($pl_position_id, $out_bill_id, $pairs){
	$res=false;	
	
	foreach($pairs as $k=>$vv){
		$v=explode(';',$vv);
		if(($v[0]==$pl_position_id)&&($v[1]==$out_bill_id)){
			$res=true;
			break;	
		}
	}
	
	return $res;
}

$ret='';

if(isset($_GET['action'])&&($_GET['action']=="retrieve_supplier")){
	$_si=new SupplierItem;
	
	$si=$_si->GetItemById(abs((int)$_GET['id']));
	
	
	$_opf=new OpfItem;
	$opf=$_opf->GetItemById($si['opf_id']);
	
	$_bi=new BDetailsItem;
	$bi=$_bi->GetItemByFields(array('is_basic'=>1, 'user_id'=>$si['id']));
	
	$_sci=new SupContractItem;
	$sci=$_sci->GetItemByFields(array('is_basic'=>1, 'user_id'=>$si['id'], 'is_incoming'=>1));
	
	if($si!==false){
		$rret=array();
		foreach($si as $k=>$v){
			if(
			($k=='contract_no')||
			($k=='contract_pdate')||
			($k=='contract_pdate')) continue;
			
			
			$rret[]='"'.$k.'":"'.htmlspecialchars(str_replace("\r", "", str_replace("\n",  "",  $v))).'"';
		}
		
		$rret[]='"opf_name":"'.htmlspecialchars($opf['name']).'"';
		
		if($bi!==false){
			$rret[]='"bdetails_id_string":" р/с '.addslashes($bi['rs'].', '.$bi['bank']).', '.$bi['city'].'"';
			$rret[]='"bdetails_id":"'.htmlspecialchars($bi['id']).'"';
		}
		
		if($sci!==false){
			$rret[]='"contract_no_string":"'.addslashes($sci['contract_no']).'"';
			$rret[]='"contract_no":"'.addslashes($sci['contract_no']).'"';
			$rret[]='"contract_id":"'.addslashes($sci['id']).'"';
		
			$rret[]='"contract_pdate_string":"'.addslashes($sci['contract_pdate']).'"';
			$rret[]='"contract_pdate":"'.addslashes($sci['contract_pdate']).'"';
			
			
		}
		
		$ret='{'.implode(', ',$rret).'}';
	}
	
}elseif(isset($_POST['action'])&&($_POST['action']=="find_suppliers")){
	
	
	//получим список позиций по фильтру
	$_pg=new SuppliersGroup;
	
	$dec=new DBDecorator;
	
	
	
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['code'])))>0) $dec->AddEntry(new SqlEntry('p.code',SecStr(iconv("utf-8","windows-1251",$_POST['code'])), SqlEntry::LIKE));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['full_name'])))>0) $dec->AddEntry(new SqlEntry('p.full_name',SecStr(iconv("utf-8","windows-1251",$_POST['full_name'])), SqlEntry::LIKE));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['inn'])))>0) $dec->AddEntry(new SqlEntry('p.inn',SecStr(iconv("utf-8","windows-1251",$_POST['inn'])), SqlEntry::LIKE));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['kpp'])))>0) $dec->AddEntry(new SqlEntry('p.kpp',SecStr(iconv("utf-8","windows-1251",$_POST['kpp'])), SqlEntry::LIKE));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['legal_address'])))>0) $dec->AddEntry(new SqlEntry('p.legal_address',SecStr(iconv("utf-8","windows-1251",$_POST['legal_address'])), SqlEntry::LIKE));
	
	
	
	$ret=$_pg->GetItemsForBill('bills/suppliers_list.html',  $dec,true,$all7,$result);
	

	
}elseif(isset($_POST['action'])&&($_POST['action']=="load_bdetails")){
	$supplier_id=abs((int)$_POST['supplier_id']);
	$current_id=abs((int)$_POST['current_id']);
	
	$_bd=new BDetailsGroup;
	$arr=$_bd->GetItemsByIdArr($supplier_id,$current_id);
	
	$sm=new SmartyAj;
	$sm->assign('pos',$arr);
	
	$ret=$sm->fetch('bills/bdetails_list.html');

}elseif(isset($_POST['action'])&&($_POST['action']=="load_condetails")){
	$supplier_id=abs((int)$_POST['supplier_id']);
	$current_id=abs((int)$_POST['current_id']);
	
	$_bd=new SupContractGroup();
	$arr=$_bd->GetItemsByIdArr($supplier_id, $current_id, 1);
	
	//print_r($arr);
	
	$sm=new SmartyAj;
	$sm->assign('pos2',$arr);
	
	$ret=$sm->fetch('bills/contracts_list.html');
	

	
}elseif(isset($_GET['action'])&&($_GET['action']=="retrieve_bdetails")){
	//получим bdetails из списка
	$_si=new BDetailsItem;
	
	$si=$_si->GetItemById(abs((int)$_GET['id']));
	
	
	if($si!==false){
		$rret=array();
		foreach($si as $k=>$v){
			$rret[]='"'.$k.'":"'.addslashes($v).'"';
		}
		
		$ret='{'.implode(', ',$rret).'}';
	}

}elseif(isset($_GET['action'])&&($_GET['action']=="retrieve_contracts")){
	//получим bdetails из списка
	$_si=new SupContractItem;
	
	$si=$_si->GetItemById(abs((int)$_GET['id']));
	
	
	if($si!==false){
		$rret=array();
		foreach($si as $k=>$v){
			$rret[]='"'.$k.'":"'.addslashes($v).'"';
		}
		
		$ret='{'.implode(', ',$rret).'}';
	}
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="load_positions")){
	//вывод позиций к.в. для счета
	
	$except_id=abs((int)$_POST['bill_id']);
	$_bi1=new BillItem;
	$bi1=$_bi1->GetItemById($except_id);
	
	
	
	
	$already_in_bill=array();
	
	$complex_positions=$_POST['complex_positions'];
	
	
	foreach($complex_positions as $kk=>$vv){
		$valarr=explode(';',$vv);
		//>md5($h['pl_position_id'].'_'.$h['position_id'].'_'.$h['pl_discount_id'].'_'.$h['pl_discount_value'].'_'.$h['pl_discount_rub_or_percent']),
		
		
		$already_in_bill[]=array($valarr[0],'position_id'=>$valarr[16],'pl_discount_id'=>$valarr[3],'pl_discount_value'=>$valarr[4], 'pl_discount_rub_or_percent'=>$valarr[5]);	//используем этот массив для формирования доступных позиций...
	}
	
	//print_r($complex_positions);
	
	
	$_kpg=new BillPrepare;
	
	$_mf=new MaxFormer;
	
	
	
	//echo mysqlSet::$inst_count.' запросов к БД на выборку<br />';
	
	//получим список товаров п-листа, находящийся в карте счета
	$alls=$_kpg->GetItemsByIdArr($complex_positions, $except_id, false);
	
	/*
	echo '<pre>';
	print_r($alls);
	echo '</pre>';
	*/
	
	
	$_pi=new PosItem;
	$_out_bi=new BillItem;
	
	$arr=array();
	foreach($alls as $kk=>$vv){
		$loaded_data=explode(';',$complex_positions[$kk]);
		
		$v=array();
		
		//подгрузка названия и прочих параметров из п-л
		$v=$vv;
		
		 
		//подставим значения, если они заданы ранее
		 
		$v['hash']=md5($vv['pl_position_id'].'_'.$vv['position_id'].'_'.$loaded_data[3].'_'.$loaded_data[4].'_'.$loaded_data[5].'_'.$loaded_data[18]);
		
		//подставить цены, скидки, +/-
		$v['quantity']=$loaded_data[1];
		
		$v['price']=$loaded_data[2];
		
		$v['price_f']=$loaded_data[17];
		$v['price_pm']=$loaded_data[12];
		$v['has_pm']=$loaded_data[8];
		$v['cost']=round($loaded_data[17]*$loaded_data[1],2); //??????
		$v['total']=$loaded_data[13];
		$v['plus_or_minus']=$loaded_data[9];
		$v['rub_or_percent']=$loaded_data[10];
		$v['value']=$loaded_data[11];
		$v['discount_rub_or_percent']=$loaded_data[14];
		$v['discount_value']=$loaded_data[15];
		$v['nds_proc']=NDS;
		$v['nds_summ']=sprintf("%.2f",$loaded_data[13]-$loaded_data[13]/((100+NDS)/100));//   $loaded_data[1];
		$v['pl_discount_id']=$loaded_data[3];
		$v['pl_discount_value']=$loaded_data[4];
		$v['pl_discount_rub_or_percent']=$loaded_data[5];
		
		
		$v['out_bill_id']=$loaded_data[18];
		if($v['out_bill_id']>0){
			
			$out_bill=$_out_bi->GetItemById($loaded_data[18]);
			$v['out_bill_code']=$out_bill['code'];	
		}
		
		
		 $v['in_rasp']=$_mf->MaxInShI($except_id, $v['position_id'], $v['pl_position_id'], $v['pl_discount_id'], $v['pl_discount_value'], $v['pl_discount_rub_or_percent'],  NULL, $v['out_bill_id']); 
		
		$arr[]=$v;
		
	
	}
	
	
	
	
	$sm=new SmartyAj;
	 $sm->assign('BILLUP',BILLUP);
	
	$sm->assign('pospos',$arr);
	
	$_pvd=new PlDisGroup;
	$sm->assign('discs1',$_pvd->GetItemsArr());
	
	
	if($bi1['is_confirmed_price']==1){
		$sm->assign('cannot_view_pm',!$au->user_rights->CheckAccess('w',629));
	}else $sm->assign('cannot_view_pm',!$au->user_rights->CheckAccess('w',612));
	
	
	
	$sm->assign('can_exclude_positions',$au->user_rights->CheckAccess('w',129));
	
	
	//тов группы
	$_posgroupgroup=new PosGroupGroup;
		$posgroupgroup=$_posgroupgroup->GetItemsArr(); // //>GetItemsTreeArr();
		$st_ids=array(); $st_names=array();
		$st_ids[]=0; $st_names[]='-выберите-';
		foreach($posgroupgroup as $k=>$v){
			$st_ids[]=$v['id'];
			$st_names[]=$v['name'];
				
		}
		$sm->assign('tov_group_ids', $st_ids);
		$sm->assign('tov_group_names', $st_names);
		
		
		$as=new mysqlSet('select * from catalog_dimension order by name asc');
		$rs=$as->GetResult();
		$rc=$as->GetResultNumRows();
		$acts=array();
		$acts[]=array('name'=>'');
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			$acts[]=$f;
		}
		$sm->assign('dim',$acts);
	
	
	
	
	//утверждены ли цены
	$can_mod_object_only=false;
	if(isset($bi1['is_confirmed_price'])&&($bi1['is_confirmed_price']==1)&&($bi1['is_confirmed_shipping']==0)){
		$can_mod_object_only=true;
	}
	$sm->assign('can_mod_object_only',$can_mod_object_only);
	
	
	$can_mod_pm_only=false;
	if(isset($bi1['is_confirmed_price'])&&($bi1['is_confirmed_price']==1)&&($bi1['is_confirmed_shipping']==1)&&($_bi1->HasShsorAccs($bi1['id']))&&
		$au->user_rights->CheckAccess('w',523)){
		$can_mod_pm_only=true;
	}
	$sm->assign('can_mod_pm_only',$can_mod_pm_only);
	
	$sm->assign('can_change_storage',$au->user_rights->CheckAccess('w',133)&&(!$can_mod_pm_only));
	
	
	
	
	$ret.=$sm->fetch("bills_in/positions_edit_set.html");
	
	/*$ret.= mysqlSet::$inst_count.' запросов к БД на выборку<br />';
$ret.=  nonSet::$inst_count.' запросов на обновление БД<br />';
$ret.=  mysqlSet::$inst_count+nonSet::$inst_count.' всего запросов к БД<br />';


$ret.=  (time()-$_big_time_marker_begin).' сек. <br />';*/
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="transfer_positions")){
	//перенос выбранных позиций к.в. на страницу счет
		
	$id=abs((int)$_POST['id']);
	
	
	
	$complex_positions=$_POST['complex_positions'];
	
	$alls=array();
	
	$_position=new PosItem;
	$_pli=new PlPosItem;
	$_dim=new PosDimItem;
	
	$_mf=new MaxFormer;

	$_pmd=new PlDisMaxValGroup;
	
	$_out_bi=new BillItem;
	
	foreach($complex_positions as $k=>$kv){
		$f=array();	
		$v=explode(';',$kv);
		//print_r($v);
		//$do_add=true;
		if($v[1]<=0) continue;
		
		$f['position_id']=$v[16];
		$f['pl_position_id']=$v[0];
		
		$position=$_position->GetItemById($v[16]);
		if($position===false) continue;
		
		$f['quantity']=$v[1];
		
		
		$f['position_name']=$position['name'];
		$f['dimension_id']=$position['dimension_id'];
		
		$dim=$_dim->GetItemById($f['dimension_id']);
		$f['dim_name']=$dim['name'];
		
		$f['price']=$v[2];
		
		//+/-
		$f['has_pm']=$v[8];
		$f['rub_or_percent']=$v[10];
		$f['plus_or_minus']=$v[9];
		$f['value']=$v[11];
		$f['discount_rub_or_percent']=$v[14];
		//$f['discount_plus_or_minus']=$v[13];
		$f['discount_value']=$v[15];
		
		
		
		$f['price_f']=$v[17];
		
		
		//cena +-
		if($v[8]==1){
		
			$f['price_pm']=$v[12];
		}else $f['price_pm']=$f['price_f'];
		
		
		
		
		
		//st-t'
		$f['cost']=round($f['price_f']*$f['quantity'],2);;
		
		
		
		//vsego
		$f['total']=$v[13]; //round($f['price_pm']*$f['quantity'],2);
		
		
		
		if(($f['has_pm']==1)&&($f['rub_or_percent']==1)&&($f['value']>0)){
				$f['value_from_percent']=round(((float)$f['price_pm']-(float)$f['price_f']),2);
			}
			
			if(($f['has_pm']==1)&&($f['value']>0)&&($f['discount_rub_or_percent']==1)&&($f['discount_value']>0)){
				$f['discount_value_from_percent']=round(($f['price_pm']-$f['price_f'])*$f['discount_value']/100,2);
			}
		
		
		/*
		$f['quantity_confirmed']=$_mf->MaxInKomplekt($komplekt_id, $f['id']); //!!!!!! SDLEAT POZJE
		$f['max_quantity']=$_mf->MaxForBill($komplekt_id, $f['id']); 
		$f['in_rasp']=$_mf->MaxInShI($id, $f['id'],NULL,$f['storage_id']);
		*/
		$f['nds_proc']=NDS;
		$f['nds_summ']=sprintf("%.2f",($f['total']-$f['total']/((100+NDS)/100)));
		
		
		//скидки счета
	
		$f['pl_discount_id']=0; 
		
		$f['pl_discount_value']=0;
		
		$f['pl_discount_rub_or_percent']=0;
		
		
		//родительский счет
		$f['out_bill_id']=$v[18];
		if($f['out_bill_id']>0){
			
			$out_bill=$_out_bi->GetItemById($v[18]);
			$f['out_bill_code']=$out_bill['code'];	
		}
		
	
		
		$f['in_rasp']=$_mf->MaxInShI($id, $f['position_id'], $f['pl_position_id'], $f['pl_discount_id'], $f['pl_discount_value'], $f['pl_discount_rub_or_percent'] ,NULL, $f['out_bill_id']);
		
		
		$f['hash']=md5($f['pl_position_id'].'_'.$f['position_id'].'_'.$f['pl_discount_id'].'_'.$f['pl_discount_value'].'_'.$f['pl_discount_rub_or_percent'].'_'.$f['out_bill_id']);
		
		
	//	$ret.=$v.' ';
		$alls[]=$f;
	}
	
	$sm=new SmartyAj;
	$sm->assign('pospos',$alls);
	
	
	
	$sm->assign('can_modify',true);
	
	$_bill=new BillInItem;
	$bill=$_bill->getItemById($id);
	$sm->assign('bill',$bill);
	
	
	if($bill['is_confirmed_price']==1){
		$sm->assign('cannot_view_pm',!$au->user_rights->CheckAccess('w',629));
	}else $sm->assign('cannot_view_pm',!$au->user_rights->CheckAccess('w',612));
	
	
	$sm->assign('can_add_positions',$au->user_rights->CheckAccess('w',609)); 
	$sm->assign('can_delete_positions',$au->user_rights->CheckAccess('w',611)); 
		
	$sm->assign('BILLUP',BILLUP);
	$ret=$sm->fetch("bills_in/positions_on_page_set.html");
	
	
}

elseif(isset($_POST['action'])&&(($_POST['action']=="calc_new_total")||($_POST['action']=="calc_new_nds"))){
	//подсчет нового итого
		
	
	$alls=array();
	$complex_positions=$_POST['complex_positions'];
	
	foreach($complex_positions as $k=>$valarr){
		$f=array();	
		
		$v=explode(';',$valarr);
		
		$f['quantity']=$v[1];
		$f['id']=$v[0];
		

		
		$f['price']=$v[3];
		
		//+/-
		$f['has_pm']=$v[8];
		$f['rub_or_percent']=$v[10];
		$f['plus_or_minus']=$v[9];
		$f['value']=$v[11];
		
		
		//cena +-
		if($f['has_pm']==1){
			
			$f['price_pm']=$v[12];
			
		}else $f['price_pm']=$f['price'];
		
		//st-t'
		$f['cost']=$f['price']*$f['quantity'];
		
		
		//vsego
		$f['total']=$v[13];
		
		
		$alls[]=$f;
		
		/*echo '<pre>';
		print_r($f);
		echo '</pre>';*/
		
	}
	
	
	$_bpf=new BillPosPMFormer;
	if($_POST['action']=="calc_new_total") $ret=$_bpf->CalcCost($alls);
	
	if($_POST['action']=="calc_new_nds") $ret=$_bpf->CalcNDS($alls);
	
}

//РАБОТА С ПРИМЕЧАНИЯМИ
elseif(isset($_POST['action'])&&($_POST['action']=="redraw_notes")){
	$sm=new SmartyAj;
	
	$user_id=abs((int)$_POST['user_id']);
	
	$rg=new BillNotesGroup;
	
	$sm->assign('items',$rg->GetItemsByIdArr($user_id,0,0,false,$au->user_rights->CheckAccess('w',615), $au->user_rights->CheckAccess('w',616), $result['id']));
	$sm->assign('word','notes');
	$sm->assign('user_id',$user_id);
	$sm->assign('named','Примечания');
	
	$sm->assign('can_edit', $au->user_rights->CheckAccess('w',614));
	
	
	$ret=$sm->fetch('bills_in/d_notes.html');
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="add_notes")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',614)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	
	
	$ri=new BillNotesItem;
	$ri->Add(array(
				'note'=>SecStr(iconv("utf-8","windows-1251",$_POST['note'])),
				'pdate'=>time(),
				'user_id'=>$user_id,
				'posted_user_id'=>$result['id']
			));
	
	$log->PutEntry($result['id'],'добавил примечания по входящему счету', NULL,614, NULL,SecStr(iconv("utf-8","windows-1251",$_POST['note']),9),$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="edit_notes")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',614)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	$id=abs((int)$_POST['id']);
	
	
	$ri=new BillNotesItem;
	$ri->Edit($id,
				array(
				'note'=>SecStr(iconv("utf-8","windows-1251",$_POST['note'])),
				'pdate'=>time(),
				'posted_user_id'=>$result['id']/*,
				'user_id'=>$user_id*/
			));
	
	$log->PutEntry($result['id'],'редактировал примечания по входящему счету', NULL,614,NULL, SecStr(iconv("utf-8","windows-1251",$_POST['note']),9),$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="delete_notes")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',614)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	$id=abs((int)$_POST['id']);
	
	
	$ri=new BillNotesItem;
	$ri->Del($id);
	
	$log->PutEntry($result['id'],'удалил примечания по входящему счету', NULL,614,NULL,NULL,$user_id);
	
}
//работа с датами
elseif(isset($_POST['action'])&&($_POST['action']=="retrieve_ethalon_pdate_payment_contract")){
	
	$_si=new SupplierItem;
	$_bd=new BillDates;
	
	$contract_id=abs((int)$_POST['contract_id']);
	$_sci=new SupContractItem;
	$sci=$_sci->GetItemById($contract_id);
	
	$supplier=$_si->GetItemById(abs((int)$_POST['supplier_id']));
	if($supplier!==false){
		$ethalon=$_bd->FindEthalon(datefromdmy($_POST['pdate_shipping_plan']),$sci['contract_prolongation'], $sci['contract_prolongation_mode']);
		
		$ret=$ethalon;  //date("d.m.Y",$ethalon);
		
	}
}
elseif(isset($_POST['action'])&&($_POST['action']=="retrieve_ethalon_full_pdate_payment_contract")){
	
	$_si=new SupplierItem;
	$_bd=new BillDates;
	
	$contract_id=abs((int)$_POST['contract_id']);
	$_sci=new SupContractItem;
	$sci=$_sci->GetItemById($contract_id);
	
	$supplier=$_si->GetItemById(abs((int)$_POST['supplier_id']));
	if($supplier!==false){
		$ethalon=$_bd->FindEthalon(datefromdmy($_POST['pdate_shipping_plan']),$sci['contract_prolongation'], $sci['contract_prolongation_mode']);
		
		$ret=date("d.m.Y",$ethalon);
		
	}
}
elseif(isset($_POST['action'])&&($_POST['action']=="compare_pdate_payment")){
	$ethalon_pdate_payment_contract=abs((int)$_POST['ethalon_pdate_payment_contract']);
	$pdate_payment_contract=datefromdmy($_POST['pdate_payment_contract']);
	
	$contract_id=abs((int)$_POST['contract_id']);
	$_sci=new SupContractItem;
	$sci=$_sci->GetItemById($contract_id);
	
	if($pdate_payment_contract<$ethalon_pdate_payment_contract){
		$ret="Вы выбрали дату оплаты по договору раньше отсрочки по договору с данным поставщиком. \nДата отсрочки: ".date('d.m.Y',$ethalon_pdate_payment_contract);	
	}elseif($pdate_payment_contract>$ethalon_pdate_payment_contract){
		$ret="Вы выбрали дату оплаты по договору позднее отсрочки по договору с данным поставщиком. \nДата отсрочки: ".date('d.m.Y',$ethalon_pdate_payment_contract);
	}else $ret="";
	
}
//подсветка утверждений карты
elseif(isset($_POST['action'])&&($_POST['action']=="redraw_is_confirmed_shipping_confirmer")){
	$state=abs((int)$_POST['state']);
	if($state==0){
		$ret='';	
	}elseif($state==1){
		$ret=$result['position_s'].' '.$result['name_s'].' '.' '.$result['login'].' '.date("d.m.Y H:i:s",time());	
	}
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="redraw_is_confirmed_price_confirmer")){
	$state=abs((int)$_POST['state']);
	if($state==0){
		$ret='';	
	}elseif($state==1){
		$ret=$result['position_s'].' '.$result['name_s'].' '.' '.$result['login'].' '.date("d.m.Y H:i:s",time());	
	}
	
}
//utv- razutv
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_confirm_price")){
	$id=abs((int)$_POST['id']);
	$flag_to_payments=abs((int)$_POST['flag_to_payments']);
	
	$_ti=new BillInItem;
	
	$_si=new UserSItem;
	
	$trust=$_ti->getitembyid($id);
	if($trust['confirm_price_pdate']==0) $trust['confirm_price_pdate']='-';
	else $trust['confirm_price_pdate']=date("d.m.Y H:i:s",$trust['confirm_price_pdate']);
	
	
	$si=$_si->getitembyid($trust['user_confirm_price_id']);
	$trust['confirmed_price_name']=$si['name_s'];
	$trust['confirmed_price_login']=$si['login'];
	
	$bill_id=$id;
	
	if($trust['is_confirmed_price']==1){
		//есть права: либо сам утв.+есть права, либо есть искл. права:
		if(($au->user_rights->CheckAccess('w',622))||$au->user_rights->CheckAccess('w',96)){
			if(($trust['status_id']==2)||($trust['status_id']==9)||($trust['status_id']==10)){
				$_ti->Edit($id,array('is_confirmed_price'=>0, 'user_confirm_price_id'=>$result['id'], 'confirm_price_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'снял утверждение цен',NULL,622, NULL, NULL,$bill_id);
				$_ti->FreeBindedPayments($bill_id);
				
					
			}
		}else{
			//нет прав	
		}
		
	}else{
		//есть права
		if($au->user_rights->CheckAccess('w',620)||$au->user_rights->CheckAccess('w',96)){
			if(($trust['status_id']==1)){
				$_ti->Edit($id,array('is_confirmed_price'=>1, 'user_confirm_price_id'=>$result['id'], 'confirm_price_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'утвердил цены',NULL,620, NULL, NULL,$bill_id);	
				
				if($flag_to_payments==1) $_ti->BindPayments($bill_id,$result['org_id']);		
			}
		}else{
			//do nothing
		}
	}
	
	
	
	
	$shorter=abs((int)$_POST['shorter']);
	if($shorter==0) $template='bills_in/bills_list.html';
	else $template='bills_in/bills_list_komplekt.html';
	
	
	$acg=new BillInGroup;
	
	$dec=new  DBDecorator;
	
	$dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	//if($shorter!=0) $dec->AddEntry(new SqlEntry('p.komplekt_ved_id',$trust['komplekt_ved_id'], SqlEntry::E));
	
	$ret=$acg->ShowPos($template,$dec,0,100, 
	$au->user_rights->CheckAccess('w',128), 
	$au->user_rights->CheckAccess('w',613)||$au->user_rights->CheckAccess('w',625), 
	$au->user_rights->CheckAccess('w',626), '', $au->user_rights->CheckAccess('w',620),$au->user_rights->CheckAccess('w',96),false,true,$au->user_rights->CheckAccess('w',627),NULL,NULL,$au->user_rights->CheckAccess('w',621),$au->user_rights->CheckAccess('w',622), $au->user_rights->CheckAccess('w',623));
	
		
}elseif(isset($_POST['action'])&&($_POST['action']=="scan_confirm_price")){
	$id=abs((int)$_POST['id']);
	$_ti=new BillInItem;
	
	$_si=new UserSItem;
	
	$trust=$_ti->getitembyid($id);
	if($trust['confirm_price_pdate']==0) $trust['confirm_price_pdate']='-';
	else $trust['confirm_price_pdate']=date("d.m.Y H:i:s",$trust['confirm_price_pdate']);
	
	
	$si=$_si->getitembyid($trust['user_confirm_price_id']);
	$trust['confirmed_price_name']=$si['name_s'];
	$trust['confirmed_price_login']=$si['login'];
	
	$bill_id=$id;
	
	$trust=$_ti->getitembyid($id);
	if($trust['confirm_price_pdate']==0) $trust['confirm_price_pdate']='-';
	else $trust['confirm_price_pdate']=date("d.m.Y H:i:s",$trust['confirm_price_pdate']);
	
	
	$si=$_si->getitembyid($trust['user_confirm_price_id']);
	$trust['confirmed_price_name']=$si['name_s'];
	$trust['confirmed_price_login']=$si['login'];
	
	
	$sm=new SmartyAj;
	
	
	$sm->assign('can_confirm_price', $au->user_rights->CheckAccess('w',620));
	$sm->assign('can_super_confirm_price', $au->user_rights->CheckAccess('w',96));
	
	//$itm=array();
	
	$sm->assign('filename','bill_in.php');
	$sm->assign('item',$trust);
	$sm->assign('user_id',$result['id']);
	
	$ret=$sm->fetch('bills_in/toggle_confirm_price.html');
	
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_confirm_shipping")){
	$id=abs((int)$_POST['id']);
	$_ti=new BillInItem;
	
	$_si=new UserSItem;
	
	$trust=$_ti->getitembyid($id);
	if($trust['confirm_shipping_pdate']==0) $trust['confirm_shipping_pdate']='-';
	else $trust['confirm_shipping_pdate']=date("d.m.Y H:i:s",$trust['confirm_shipping_pdate']);
	
	
	$si=$_si->getitembyid($trust['user_confirm_shipping_id']);
	$trust['confirmed_shipping_name']=$si['name_s'];
	$trust['confirmed_shipping_login']=$si['login'];
	
	$bill_id=$id;
	
	if($trust['is_confirmed_shipping']==1){
		//есть права: либо сам утв.+есть права, либо есть искл. права:
		if(($au->user_rights->CheckAccess('w',623))||$au->user_rights->CheckAccess('w',96)){
			if(($trust['status_id']==2)||($trust['status_id']==9)||($trust['status_id']==10)){
			if($_ti->DocCanUnconfirmShip($id,$reas)){
			
				$_ti->Edit($id,array('is_confirmed_shipping'=>0, 'user_confirm_shipping_id'=>$result['id'], 'confirm_shipping_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'снял утверждение приемки',NULL,623, NULL, NULL,$bill_id);
				
			}
				
			}
		}else{
			//нет прав	
		}
		
	}else{
		//есть права
		if($au->user_rights->CheckAccess('w',621)||$au->user_rights->CheckAccess('w',96)){
			if(($trust['status_id']==2)||($trust['status_id']==9)||($trust['status_id']==10)){
			if($_ti->DocCanConfirmShip($id,$reas)){
				$_ti->Edit($id,array('is_confirmed_shipping'=>1, 'user_confirm_shipping_id'=>$result['id'], 'confirm_shipping_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'утвердил приемку',NULL,621, NULL, NULL,$bill_id);	
				//	echo 'zzzzzzzzzzzzzzzzzzzzzzz';	
			}
			}
		}else{
			//do nothing
		}
	}
	
	
	
	
	$shorter=abs((int)$_POST['shorter']);
	if($shorter==0) $template='bills_in/bills_list.html';
	else $template='bills_in/bills_list_komplekt.html';
	
	
	$acg=new BillInGroup;
	
	$dec=new  DBDecorator;
	
	$dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	//if($shorter!=0) $dec->AddEntry(new SqlEntry('p.komplekt_ved_id',$trust['komplekt_ved_id'], SqlEntry::E));
	
	$ret=$acg->ShowPos($template,$dec,0,100, $au->user_rights->CheckAccess('w',128), $au->user_rights->CheckAccess('w',613)||$au->user_rights->CheckAccess('w',625), $au->user_rights->CheckAccess('w',626), '', $au->user_rights->CheckAccess('w',620),$au->user_rights->CheckAccess('w',96),false,true,$au->user_rights->CheckAccess('w',627),NULL,NULL,$au->user_rights->CheckAccess('w',621),$au->user_rights->CheckAccess('w',622), $au->user_rights->CheckAccess('w',623));
	
	
	
		
}
//udalenie-annulirovabie
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_annul")){
	$id=abs((int)$_POST['id']);
	
	if(isset($_POST['from_card'])&&($_POST['from_card']==1)) $from_card=1;
	else $from_card=0;
	
	$note=SecStr(iconv("utf-8","windows-1251",$_POST['note']));
	
	$_ti=new BillInItem;
	
	$_si=new UserSItem;
	
	$trust=$_ti->getitembyid($id);
	
	$log=new ActionLog;
	$_stat=new DocStatusItem;
	
	
	
	if(($trust['status_id']==1)&&($trust['is_confirmed']==0)){
		//удаление	
		if($au->user_rights->CheckAccess('w',626)){
			$_ti->Edit($id,array('status_id'=>3),false,$result);
			
			$stat=$_stat->GetItemById(3);
			$log->PutEntry($result['id'],'аннулирование входящего счета',NULL,626,NULL,'входящий счет № '.$trust['code'].': установлен статус '.$stat['name'],$id);	
			
			//уд-ть связанные документы
			$_ti->AnnulBindedDocuments($id);	
			
			//внести примечание
			$_ni=new BillNotesItem;
			$_ni->Add(array(
				'user_id'=>$id,
				'posted_user_id'=>$result['id'],
				'note'=>'Автоматическое примечание: документ был аннулирован пользователем '.SecStr($result['name_s']).' ('.$result['login'].'), причина: '.$note,
				'is_auto'=>1,
				'pdate'=>time()
					));	
		}
	}elseif($trust['status_id']==3){
		//разудаление
		if($au->user_rights->CheckAccess('w',627)){
			$_ti->Edit($id,array('status_id'=>1),false,$result);
			
			$stat=$_stat->GetItemById(1);
			$log->PutEntry($result['id'],'восстановление входящего счета',NULL,627,NULL,'входящий счет № '.$trust['code'].': установлен статус '.$stat['name'],$id);
			
			//внести примечание
			$_ni=new BillNotesItem;
			$_ni->Add(array(
				'user_id'=>$id,
				'posted_user_id'=>$result['id'],
				'note'=>'Автоматическое примечание: документ был восстановлен пользователем '.SecStr($result['name_s']).' ('.$result['login'].')',
				'is_auto'=>1,
				'pdate'=>time()
					));		
			
		}
		
	}
	
	if($from_card==0){
	  $shorter=abs((int)$_POST['shorter']);
	  if($shorter==0) $template='bills_in/bills_list.html';
	  else $template='bills_in/bills_list_komplekt.html';
	  
	  
	  $acg=new BillInGroup;
	  
	  $dec=new  DBDecorator;
	  
	  $dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	  if($shorter!=0) $dec->AddEntry(new SqlEntry('p.komplekt_ved_id',$trust['komplekt_ved_id'], SqlEntry::E));
	  
	  $ret=$acg->ShowPos($template,$dec,0,100, $au->user_rights->CheckAccess('w',128), $au->user_rights->CheckAccess('w',613)||$au->user_rights->CheckAccess('w',625), $au->user_rights->CheckAccess('w',626), '', $au->user_rights->CheckAccess('w',620),$au->user_rights->CheckAccess('w',96),false,true,$au->user_rights->CheckAccess('w',627),NULL,NULL,$au->user_rights->CheckAccess('w',621),$au->user_rights->CheckAccess('w',622), $au->user_rights->CheckAccess('w',623));
	
	}else{
		$editing_user=$_ti->getitembyid($id);
		$sm=new SmartyAj;
		
		
		
		//блок аннулирования
		
		$editing_user['can_annul']=$_ti->DocCanAnnul($editing_user['id'],$reason)&&$au->user_rights->CheckAccess('w',626);
		if(!$au->user_rights->CheckAccess('w',626)) $reason='недостаточно прав для данной операции';
		$editing_user['can_annul_reason']=$reason;
		
		$editing_user['binded_to_annul']=$_ti->GetBindedDocumentsToAnnul($editing_user['id']);
		
		
		$editing_user['can_restore']=$_ti->DocCanRestore($editing_user['id'],$reason)&&$au->user_rights->CheckAccess('w',627);
			if(!$au->user_rights->CheckAccess('w',627)) $reason='недостаточно прав для данной операции';
		
		
		
		$sm->assign('bill',$editing_user);
		$ret=$sm->fetch('bills_in/toggle_annul_card.html');		
	}
		
}
elseif(isset($_POST['action'])&&($_POST['action']=="scan_confirm_shipping")){
	$id=abs((int)$_POST['id']);
	$_ti=new BillInItem;
	
	$_si=new UserSItem;
	
	$trust=$_ti->getitembyid($id);
	if($trust['confirm_shipping_pdate']==0) $trust['confirm_shipping_pdate']='-';
	else $trust['confirm_shipping_pdate']=date("d.m.Y H:i:s",$trust['confirm_shipping_pdate']);
	
	
	$si=$_si->getitembyid($trust['user_confirm_shipping_id']);
	$trust['confirmed_shipping_name']=$si['name_s'];
	$trust['confirmed_shipping_login']=$si['login'];
	
	$bill_id=$id;
	
	
	
	
	$trust=$_ti->getitembyid($id);
	if($trust['confirm_shipping_pdate']==0) $trust['confirm_shipping_pdate']='-';
	else $trust['confirm_shipping_pdate']=date("d.m.Y H:i:s",$trust['confirm_shipping_pdate']);
	
	
	$si=$_si->getitembyid($trust['user_confirm_shipping_id']);
	$trust['confirmed_shipping_name']=$si['name_s'];
	$trust['confirmed_shipping_login']=$si['login'];
	
	
	$sm=new SmartyAj;
	
	
	$sm->assign('can_confirm_shipping', $au->user_rights->CheckAccess('w',620));
	$sm->assign('can_super_confirm_shipping', $au->user_rights->CheckAccess('w',96));
	
	//$itm=array();
	
	$sm->assign('filename','bill_in.php');
	$sm->assign('item',$trust);
	$sm->assign('user_id',$result['id']);
	
	$ret=$sm->fetch('bills_in/toggle_confirm_ship.html');
		
}elseif(isset($_POST['action'])&&($_POST['action']=="find_sh_pos")){
	//dostup
	$_kr=new BillReports;
	
	//	//link_in_sh("%{$pospos[pospossec].position_id}%", "%{$pospos[pospossec].pl_position_id}%", "%{$pospos[pospossec].pl_discount_id}%", "%{$pospos[pospossec].pl_discount_value}%", "%{$pospos[pospossec].pl_discount_rub_or_percent}%");
	
	$bill_id=abs((int)$_POST['bill_id']);
	
	$position_id=abs((int)$_POST['position_id']);
	$pl_position_id=abs((int)$_POST['pl_position_id']);
	$pl_discount_id=abs((int)$_POST['pl_discount_id']);
	$pl_discount_value=abs((int)$_POST['pl_discount_value']);
	$pl_discount_rub_or_percent=abs((int)$_POST['pl_discount_rub_or_percent']);
	$out_bill_id=abs((int)$_POST['out_bill_id']);
	
	
	
	$ret=$_kr->InSh($position_id,$bill_id,'bills_in/in_shs.html',$result['org_id'],true, $pl_position_id, $pl_discount_id, $pl_discount_value,  $pl_discount_rub_or_percent,  $out_bill_id );
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="draw_positions")){
	$id=abs((int)$_POST['id']);
	
	$_bill=new BillInItem;
	
	$bill=$_bill->GetItemById($id);
	
	//bills_in/position_actions.html" bill=$bill action=1}%
	$sm=new SmartyAj;
	
	$sm->assign('filename','bill_in.php');
	
	$_bpg=new BillInPosGroup;
	$bpg=$_bpg->GetItemsByIdArr($bill['id']);
	//print_r($bpg);
	$sm->assign('positions',$bpg);
	$sm->assign('has_positions',true);
	$_bpf=new BillPosPMFormer;
	

	
	$total_cost=$_bpf->CalcCost($bpg);
	$total_nds=$_bpf->CalcNDS($bpg);
	$sm->assign('total_cost',$total_cost);
	$sm->assign('total_nds',$total_nds);
	
	$sm->assign('action',1);
	$sm->assign('bill',$bill);
	
	
	
	
	
	$ret=$sm->fetch('bills_in/position_actions.html');
		
}
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_eq")){
	//выравнивание
	if($au->user_rights->CheckAccess('w',624)){
		$id=abs((int)$_POST['id']);
		$args=$_POST['args'];
		
		//$_sh_p=new ShIPosItem();
		$_sh=new BillInItem;
		
		$_sh->DoEq($id,$args,$output);
		
		$ret='<script>alert("'.$output.'"); location.reload();</script>';
		
	}else{
		$ret='<script>alert("У Вас недостаточно прав для данного действия.");</script>';	
	}
	
}elseif(isset($_POST['action'])&&($_POST['action']=="toggle_scan_eq")){
	//выравнивание
	if($au->user_rights->CheckAccess('w',624)){
		$id=abs((int)$_POST['id']);
		$args=$_POST['args'];
		
		//$_sh_p=new ShIPosItem();
		$_sh=new BillInItem;
		
		$_sh->ScanEq($id,$args,$output);
		
		$ret=$output;
		
	}else{
		$ret='<script>alert("У Вас недостаточно прав для данного действия.");</script>';	
	}
	
}elseif(isset($_POST['action'])&&($_POST['action']=="check_unconfirm")){
	//проверить, есть ли заявки с таким номером для такого уч.
	
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new BillInItem;
		
		
		if(!$_ki->DocCanUnconfirmShip($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
}elseif(isset($_POST['action'])&&($_POST['action']=="check_confirm")){
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new BillInItem;
		
		
		if(!$_ki->DocCanConfirmShip($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
	

}elseif(isset($_POST['action'])&&($_POST['action']=="check_unconfirm_price")){
	
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new BillInItem;
		
		
		if(!$_ki->DocCanUnconfirmPrice($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
}elseif(isset($_POST['action'])&&($_POST['action']=="check_confirm_price")){
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new BillInItem;
		
		
		if(!$_ki->DocCanConfirmPrice($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
	
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="update_discount_given")){
	//проверить, есть ли заявки с таким номером для такого уч.
		//header('Content-type: text/html; charset=windows-1251');
		
		$bill_id=abs((int)$_POST['bill_id']);
		$table_id=abs((int)$_POST['table_id']);
		
		$discount_given=(float)$_POST['discount_given'];
		
		if($au->user_rights->CheckAccess('w',629)||$au->user_rights->CheckAccess('w',363)){
			$_ki=new BillInItem;
			$bill=$_ki->GetItemById($bill_id);
			
			if($bill['status_id']==10){
			  $_bpi=new BillPosItem;
			  $_bpm=new BillPosPMItem;
			  
			  $bpi=$_bpi->GetItemById($table_id);
			  if($bpi!==false){
				  $bpm=$_bpm->GetItemByFields(array('bill_position_id'=>$bpi['id']));
				  
				  if($bpm!==false){
					  $_bpm->Edit($bpm['id'], array('discount_given'=>$discount_given,
					  	'discount_given_pdate'=>time(),
						'discount_given_user_id'=>$result['id']
					  
					  ));	
					  
					  //запись в журнал по счету
					  $log->PutEntry($result['id'],'задал полученный +/- позиции входящего счета', NULL,629, NULL,'позиция '.SecStr($bpi['name']).', старая сумма полученного +/- '.$bpm['discount_given'].' руб. '.', новая сумма полученного +/- '.$discount_given.' руб.',$bill_id);
				  }
			  }
			  
			  $sm=new SmartyAj;
			  
			  $item=array(
			  	'manager_name'=>$result['name_s'],
				'manager_login'=>$result['login'],
				'discount_given_pdate'=>date('d.m.Y H:i:s')
			  );
			  $sm->assign('item',$item);
			  
			  $ret=$sm->fetch('bills_in/positions_pm_saver.html');
			}else{
				$ret='Статус данного счета не позволяет вносить суммы выданных +/-. Для работы с выданными +/- необходимо, чтобы статус счета был "Выполнен".';	
			}
		}else{
			$ret='У Вас недостаточно прав для даннго действия.';
		}
		
		//если ноль - то все хорошо
	
}elseif(isset($_POST['action'])&&($_POST['action']=="is_in_buh_save")){
	
		//header('Content-type: text/html; charset=windows-1251');
		
		$id=abs((int)$_POST['id']);
		$state=abs((int)$_POST['state']);
		$_bi=new BillInItem;
		
		$bill=$_bi->GetItemById($id);
		
		$can=$_bi->CanIsInBuh($id, $rss22, $bill, $au->user_rights->CheckAccess('w',634), $au->user_rights->CheckAccess('w',635));
		
		if($can){
			
			
			if($state==1){
				$_bi->Edit($id, array(
					'is_in_buh'=>$state,
					'in_buh_pdate'=>time(),
					'user_in_buh_id'=>$result['id']
				));	
				
				 $log->PutEntry($result['id'],SecStr('установил флаг "счет в бухгалтерии"'), NULL,634, NULL,'счет № '.$bill['code'],$id);
			}else{
				
				$_bi->Edit($id, array(
					'is_in_buh'=>$state,
					'in_buh_pdate'=>0,
					'user_in_buh_id'=>0
				));
				
				$log->PutEntry($result['id'],SecStr('снял флаг "счет в бухгалтерии"'), NULL,635, NULL,'счет № '.$bill['code'],$id);
			}
		}
		
		$ret='';
}elseif(isset($_POST['action'])&&($_POST['action']=="mass_is_in_buh_update")){
	
		//header('Content-type: text/html; charset=windows-1251');
		
		$id=abs((int)$_POST['id']);
		
		$_bi=new BillInItem;
		
		$marked_as_not_in=$_POST['marked_as_not_in'];
		$marked_as_in=$_POST['marked_as_in'];
		
		if(is_array($marked_as_not_in)) foreach($marked_as_not_in as $k=>$v){
			$bill=$_bi->GetItemById($v);
		
			$can=$_bi->CanIsInBuh($v, $rss22, $bill, $au->user_rights->CheckAccess('w',634), $au->user_rights->CheckAccess('w',635));
			
			if($can){
				$_bi->Edit($v, array(
					'is_in_buh'=>0,
					'in_buh_pdate'=>0,
					'user_in_buh_id'=>0
				));
				
				$log->PutEntry($result['id'],SecStr('снял флаг "счет в бухгалтерии"'), NULL,634, NULL,'счет № '.$bill['code'],$v);	
			}
			
		}
		
		
		if(is_array($marked_as_in)) foreach($marked_as_in as $k=>$v){
			$bill=$_bi->GetItemById($v);
		
			$can=$_bi->CanIsInBuh($v, $rss22, $bill, $au->user_rights->CheckAccess('w',634), $au->user_rights->CheckAccess('w',635));
			
			if($can){
				$_bi->Edit($v, array(
					'is_in_buh'=>1,
					'in_buh_pdate'=>time(),
					'user_in_buh_id'=>$result['id']
				));	
				
				 $log->PutEntry($result['id'],SecStr('установил флаг "счет в бухгалтерии"'), NULL,634, NULL,'счет № '.$bill['code'],$v);
			}
			
		}
		
		
		
		$ret='';
}
elseif(isset($_POST['action'])&&($_POST['action']=="redraw_is_in_buh_confirmer")){
	$state=abs((int)$_POST['state']);
	if($state==0){
		$ret='';	
	}elseif($state==1){
		$ret=$result['position_s'].' '.$result['name_s'].' '.' '.$result['login'].' '.date("d.m.Y H:i:s",time());	
	}
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="find_changed_accs")){
	//формируем сообщение об изменении сумм связ реализации
	$message='';
		
	$id=abs((int)$_POST['id']);
	
	$bill_id=abs((int)$_POST['bill_id']);
	
	$changed_positions=$_POST['changed_positions'];
	
	//$alls=array();
	
	$_position=new PosItem;
	$_dim=new PosDimItem;
	
	$_mf=new MaxFormer;
	
	$pairs1=array(); $pairs2=array(); $changed_totals=array();
	
	$_ai=new AccInItem;
	
	
	if(count($changed_positions)>0){
	
		foreach($changed_positions as $k=>$kv){
			$v=explode(';',$kv);
			
			$pairs1[]=' (bill_id="'.$bill_id.'" ) ';
			$pairs2[]=' (position_id="'.$v[0].'" 
						
						and pl_position_id="'.$v[1].'" 
						and pl_discount_id="'.$v[2].'" 
						and pl_discount_value="'.$v[3].'" 
						and pl_discount_rub_or_percent="'.$v[4].'" 
						and out_bill_id="'.$v[6].'" 
						) ';
			
			$changed_totals[]=array(
				'position_id'=>$v[0],
				'pl_position_id'=>$v[1],
				'pl_discount_id'=>$v[2],
				'pl_discount_value'=>$v[3],
				'pl_discount_rub_or_percent'=>$v[4],
				'price_pm'=>$v[5],
				'out_bill_id'=>$v[6]
			);
		}
		//print_r($changed_totals);
		
		$sql='select id, given_pdate from acceptance 
		where 
			('.implode(' or ', $pairs1).') 
			and id in(select distinct acceptance_id from acceptance_position where '.implode(' or ',$pairs2).' )';
		
		//$message=$sql;
		$set=new mysqlSet($sql);
		$rc=$set->GetResultNumRows();
		$rs=$set->GetResult();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$old_summ=$_ai->CalcCost($f['id']);
			
			//как рассчитывать сумму измененного поступления????
			$new_summ=round($_ai->CalcCost($f['id'], NULL,$changed_totals),2);
			
			
			$message.='№ '.$f['id'].' от '.date('d.m.Y',$f['given_pdate']).' с '.$old_summ.' руб. на '.$new_summ.' руб.'."\n";
		}
		if(strlen($message)>0) $message="Изменится сумма поступлений: \n".$message."\n";
	}
	
	$ret=$message;
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="find_pos_in")){

//получим список позиций по фильтру
	$_pg=new PlPosGroupForBill;
	
	$dec=new DBDecorator;
	
	$name=SecStr(iconv("utf-8","windows-1251",$_POST['qry']));
	$group_id=abs((int)$_POST['group_id']);
	
	//$except_id=abs((int)$_POST['except_id']);
	//$dec->AddEntry(new SqlEntry('p.id',$except_id, SqlEntry::NE));
	
	$except_ids=$_POST['except_ids'];
	if(count($except_ids)>0){
		$dec->AddEntry(new SqlEntry('pl.id', NULL, SqlEntry::NOT_IN_VALUES, NULL,$except_ids));		
		
	}
	
	if(strlen($name)>0) $dec->AddEntry(new SqlEntry('p.name',$name, SqlEntry::LIKE));
	
	//if($group_id>0) $dec->AddEntry(new SqlEntry('p.group_id',$group_id, SqlEntry::E));
	if($group_id>0) {
		$dec->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		$dec->AddEntry(new SqlEntry('p.group_id',$group_id, SqlEntry::E));
		
		//найти подподгруппы
		$_pgg=new PosGroupGroup;
		$arr=$_pgg->GetItemsByIdArr($group_id);
		$arg=array();
		foreach($arr as $k=>$v){
			if(!in_array($v['id'],$arg)) $arg[]=$v['id'];
			$arr2=$_pgg->GetItemsByIdArr($v['id']);
			foreach($arr2 as $kk=>$vv){
				if(!in_array($vv['id'],$arg))  $arg[]=$vv['id'];
			}
		}
		
		if(count($arg)>0){
			$dec->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			$dec->AddEntry(new SqlEntry('p.group_id', NULL, SqlEntry::IN_VALUES, NULL,$arg));	
		}
		
		$dec->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
	}
	
	if(abs((int)$_POST['dimension_id'])>0) $dec->AddEntry(new SqlEntry('p.dimension_id',abs((int)$_POST['dimension_id']), SqlEntry::E));
	
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['length'])))>0) $dec->AddEntry(new SqlEntry('p.length',SecStr(iconv("utf-8","windows-1251",$_POST['length'])), SqlEntry::E));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['width'])))>0) $dec->AddEntry(new SqlEntry('p.width',SecStr(iconv("utf-8","windows-1251",$_POST['width'])), SqlEntry::E));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['height'])))>0) $dec->AddEntry(new SqlEntry('p.height',SecStr(iconv("utf-8","windows-1251",$_POST['height'])), SqlEntry::E));
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['diametr'])))>0) $dec->AddEntry(new SqlEntry('p.diametr',SecStr(iconv("utf-8","windows-1251",$_POST['diametr'])), SqlEntry::E));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['weight'])))>0) $dec->AddEntry(new SqlEntry('p.weight',SecStr(iconv("utf-8","windows-1251",$_POST['weight'])), SqlEntry::E));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['volume'])))>0) $dec->AddEntry(new SqlEntry('p.volume',SecStr(iconv("utf-8","windows-1251",$_POST['volume'])), SqlEntry::E));
	
	
	$_pg->itemsname='pospos';
	
	//нужен другой метод, который бы возвращал данные в нужном формате
	
	
	$_pg->ShowPos('bills_in/position_edit_finded.html', $dec,0,1000,false,false,true);
	
	$items=$_pg->items;
	
	//добавим стоимости, кол-во
	foreach($items as $k=>$v){
		
		$items[$k]['quantity']=0;	
		$items[$k]['price_pm']=$items[$k]['price_f'];
		$items[$k]['cost']=0;
		$items[$k]['total']=0;
		$items[$k]['nds_proc']=NDS;
		$items[$k]['nds_summ']=0;
		$items[$k]['nds_summ']=0;
		$items[$k]['value']=0;
		$items[$k]['discount_value']=0;
		$items[$k]['out_bill_id']=0;
		
		$items[$k]['in_rasp']=0;
		
		$items[$k]['hash']=md5($items[$k]['pl_position_id'].'_'.$items[$k]['position_id'].'_'.$items[$k]['pl_discount_id'].'_'.$items[$k]['pl_discount_value'].'_'.$items[$k]['pl_discount_rub_or_percent'].'_'.$items[$k]['out_bill_id']);
	}
	
	//print_r($items);
	
	$sm=new SmartyAj;
	
	$sm->assign('pospos', $items);
	$sm->assign('cannot_view_pm',!$au->user_rights->CheckAccess('w',612));
	$ret=$sm->fetch('bills_in/position_edit_finded.html');
	
	
	
}

//список доступных позиций по исх счетам
elseif(isset($_POST['action'])&&($_POST['action']=="find_pos_other_bills")){
	$_pg=new BillPosGroupForBill;
	
	$dec=new DBDecorator;
	
	$name=SecStr(iconv("utf-8","windows-1251",$_POST['qry']));
	$group_id=abs((int)$_POST['group_id']);
	
	//$except_id=abs((int)$_POST['except_id']);
	//$dec->AddEntry(new SqlEntry('p.id',$except_id, SqlEntry::NE));
	
	
	$dec->AddEntry(new SqlEntry('b.org_id',$result['org_id'], SqlEntry::E));
	$dec->AddEntry(new SqlEntry('b.inventory_id',0, SqlEntry::E));
	
	$except_ids=$_POST['except_pairs'];
	

	
	if(strlen($name)>0) $dec->AddEntry(new SqlEntry('p.name',$name, SqlEntry::LIKE));
	

	if($group_id>0) {
		$dec->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		$dec->AddEntry(new SqlEntry('cat.group_id',$group_id, SqlEntry::E));
		
		//найти подподгруппы
		$_pgg=new PosGroupGroup;
		$arr=$_pgg->GetItemsByIdArr($group_id);
		$arg=array();
		foreach($arr as $k=>$v){
			if(!in_array($v['id'],$arg)) $arg[]=$v['id'];
			$arr2=$_pgg->GetItemsByIdArr($v['id']);
			foreach($arr2 as $kk=>$vv){
				if(!in_array($vv['id'],$arg))  $arg[]=$vv['id'];
			}
		}
		
		if(count($arg)>0){
			$dec->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			$dec->AddEntry(new SqlEntry('cat.group_id', NULL, SqlEntry::IN_VALUES, NULL,$arg));	
		}
		
		$dec->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
	}
	
	if(abs((int)$_POST['dimension_id'])>0) $dec->AddEntry(new SqlEntry('cat.dimension_id',abs((int)$_POST['dimension_id']), SqlEntry::E));
	
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['length'])))>0) $dec->AddEntry(new SqlEntry('cat.length',SecStr(iconv("utf-8","windows-1251",$_POST['length'])), SqlEntry::E));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['width'])))>0) $dec->AddEntry(new SqlEntry('cat.width',SecStr(iconv("utf-8","windows-1251",$_POST['width'])), SqlEntry::E));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['height'])))>0) $dec->AddEntry(new SqlEntry('cat.height',SecStr(iconv("utf-8","windows-1251",$_POST['height'])), SqlEntry::E));
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['diametr'])))>0) $dec->AddEntry(new SqlEntry('cat.diametr',SecStr(iconv("utf-8","windows-1251",$_POST['diametr'])), SqlEntry::E));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['weight'])))>0) $dec->AddEntry(new SqlEntry('cat.weight',SecStr(iconv("utf-8","windows-1251",$_POST['weight'])), SqlEntry::E));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['volume'])))>0) $dec->AddEntry(new SqlEntry('cat.volume',SecStr(iconv("utf-8","windows-1251",$_POST['volume'])), SqlEntry::E));
	
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['out_bill_code'])))>0) $dec->AddEntry(new SqlEntry('b.code',SecStr(iconv("utf-8","windows-1251",$_POST['out_bill_code'])), SqlEntry::LIKE));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['supplier_name'])))>0){
		$suppliers=explode(';', SecStr(iconv("utf-8","windows-1251",$_POST['supplier_name'])));
		$our_suppliers=array();
		foreach($suppliers as $k=>$v) if(strlen(trim($v))>0) $our_suppliers[]='"'.trim($v).'"';
		if(count($our_suppliers)) $dec->AddEntry(new SqlEntry('sup.full_name', NULL, SqlEntry::IN_VALUES, NULL,$our_suppliers));		
	}
	
	$_pg->itemsname='pospos';
	
	
	$dec->AddEntry(new SqlOrdEntry('position_name',SqlOrdEntry::ASC));
	
	$dec->AddEntry(new SqlOrdEntry('out_bill_code',SqlOrdEntry::DESC));
	
	
	$_pg->ShowPos('bills_in/position_edit_finded.html',  $dec, true);
	
	$items=$_pg->items;
	
	//добавим стоимости, кол-во
	$items2=array();
	foreach($items as $k=>$v){
		
		if(InRestrictedPair($v['pl_position_id'], $v['out_bill_id'], $except_ids)) continue;
		
		//$items[$k]['quantity']=0;	
		$items[$k]['price_f']=0; 
		$items[$k]['price']=0; 
		$items[$k]['price_pm']=0; 
		$items[$k]['has_pm']=0;
		$items[$k]['cost']=0;
		$items[$k]['total']=0;
		$items[$k]['nds_proc']=NDS;
		$items[$k]['nds_summ']=0;
		$items[$k]['nds_summ']=0;
		$items[$k]['value']=0;
		$items[$k]['discount_value']=0;
		//$items[$k]['out_bill_id']=$v['bill_id'];
		
		$items[$k]['in_rasp']=0;
		
		$items[$k]['hash']=md5($items[$k]['pl_position_id'].'_'.$items[$k]['position_id'].'_'.$items[$k]['pl_discount_id'].'_'.$items[$k]['pl_discount_value'].'_'.$items[$k]['pl_discount_rub_or_percent'].'_'.$items[$k]['out_bill_id']);
		
		$items2[]=$items[$k];
	}
	
	//print_r($items);
	
	$sm=new SmartyAj;
	
	$sm->assign('pospos', $items2);
	$sm->assign('by_bill',true);
	$sm->assign('cannot_view_pm',!$au->user_rights->CheckAccess('w',612));
		
	$ret=$sm->fetch('bills_in/position_edit_finded.html');
	
	
}



//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>