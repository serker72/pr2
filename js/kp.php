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

require_once('../classes/kpitem.php');
require_once('../classes/kpgroup.php');



require_once('../classes/billpospmformer.php');
require_once('../classes/kp_pospmformer.php');

require_once('../classes/maxformer.php');
require_once('../classes/opfitem.php');


require_once('../classes/kpnotesgroup.php');
require_once('../classes/kpnotesitem.php');
require_once('../classes/kppositem.php');
require_once('../classes/kppospmitem.php');
require_once('../classes/posdimitem.php');

require_once('../classes/billdates.php');
require_once('../classes/billreports.php');
require_once('../classes/kpprepare.php');

require_once('../classes/user_s_item.php');
require_once('../classes/user_s_group.php');

require_once('../classes/pl_disgroup.php');
require_once('../classes/pl_disitem.php');
require_once('../classes/pl_dismaxvalgroup.php');
require_once('../classes/pl_dismaxvalitem.php');

require_once('../classes/pl_posgroup.php');
require_once('../classes/pl_positem.php');

require_once('../classes/posgroupgroup.php');

require_once('../classes/supcontract_item.php');
require_once('../classes/supcontract_group.php');


require_once('../classes/pl_posgroup_forkp.php');

require_once('../classes/pl_currgroup.php');
require_once('../classes/kp_supply_group.php');
require_once('../classes/kp_paymode_group.php');
require_once('../classes/kp_paymode_item.php');
require_once('../classes/supplier_cities_group.php');
require_once('../classes/supplier_city_group.php');
require_once('../classes/pl_posgroup.php');

require_once('../classes/user_s_group.php');
require_once('../classes/suppliercontactgroup.php');

require_once('../classes/suppliercontactdatagroup.php');
require_once('../classes/usercontactdatagroup.php');



require_once('../classes/sched.class.php');

require_once('../classes/kp_view.class.php');

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


function FindIndex3($value_db,  $array_complex){
	$r=-1;
	if(count($array_complex)>0) foreach($array_complex as $k=>$v){
		$c=explode(';', $v);
		if($value_db==$c[0]){
			$r=$k;
			break;	
		}
		
		/*if(($v==$value)&&($array2[$k]==$value2)){
			$r=$k;
			break;	
		}*/
	}
	return $r;
}

//накладываем выделенные данные на общую таблицу
function Naloj($vv, $loaded_data, &$v){
		$_pi=new PlPosItem;
		
		$position=$_pi->GetItemById( $vv['pl_position_id']);
		//подставим значения, если они заданы ранее
		
		$v['position_id']=$loaded_data[16];
		$v['pl_position_id']=$loaded_data[16];
		 
		$v['hash']=md5($vv['pl_position_id'].'_'.$vv['position_id'].'_'.$loaded_data[3].'_'.$loaded_data[4].'_'.$loaded_data[5]);
		
		//подставить цены, скидки, +/-
		$v['quantity']=$loaded_data[1];
		
		
		//для переноса цены с доставкой и цены без доставки
		//$v['price']=$loaded_data[2];
		
		
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
		
		$v['txt_for_kp']=$position['txt_for_kp'];
		$v['photo_for_kp']=$position['photo_for_kp'];
		
		$v['price_kind_id']=$loaded_data[20];
		
		$v['code']=$position['code'];
			
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
	$sci=$_sci->GetItemByFields(array('is_basic'=>1, 'user_id'=>$si['id'], 'is_incoming'=>0));
	
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
	$_pg=new Sched_SupplierGroup;
	
	$dec=new DBDecorator;
	
	$dec->AddEntry(new SqlEntry('org_id',$result['org_id'], SqlEntry::E));
	
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['code'])))>0) $dec->AddEntry(new SqlEntry('p.code',SecStr(iconv("utf-8","windows-1251",$_POST['code'])), SqlEntry::LIKE));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['full_name'])))>0) $dec->AddEntry(new SqlEntry('p.full_name',SecStr(iconv("utf-8","windows-1251",$_POST['full_name'])), SqlEntry::LIKE));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['inn'])))>0) $dec->AddEntry(new SqlEntry('p.inn',SecStr(iconv("utf-8","windows-1251",$_POST['inn'])), SqlEntry::LIKE));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['kpp'])))>0) $dec->AddEntry(new SqlEntry('p.kpp',SecStr(iconv("utf-8","windows-1251",$_POST['kpp'])), SqlEntry::LIKE));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['legal_address'])))>0) $dec->AddEntry(new SqlEntry('p.legal_address',SecStr(iconv("utf-8","windows-1251",$_POST['legal_address'])), SqlEntry::LIKE));
	
	if(isset($_POST['already_loaded'])&&is_array($_POST['already_loaded'])) $dec->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::NOT_IN_VALUES, NULL,$_POST['already_loaded']));	
	
	
	
	//ограничения по к-ту
	$limited_supplier=NULL;
	
	/*if($au->FltSupplier($result)){  
		 
		
		$_s_to_u=new SupplierToUser;
		$s_to_u=$_s_to_u->GetExtendedViewedUserIdsArr($result['id'], $result);
		$limited_supplier=$s_to_u['sector_ids'];
		
		$dec->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::IN_VALUES, NULL,$limited_supplier));	
	}
	*/
	
	
	
	
	
	 $ret=$_pg->GetItemsForBill('kp/suppliers_list.html',  $dec,true,$all7,$result);


}elseif(isset($_POST['action'])&&(($_POST['action']=="retrieve_contacts"))){
	$_sc=new Sched_SupplierContactGroup;
	
	
	$supplier_id=abs((int)$_POST['supplier_id']);
	$current_id=abs((int)$_POST['current_id']);
	$current_k_id=abs((int)$_POST['current_k_id']);
	
	
	
	
	$alls=$_sc->GetItemsByIdArr($supplier_id,$current_id, $current_k_id); 
	$sm=new SmartyAj;
	
	
	$sm->assign('supplier_id', $supplier_id);
	$sm->assign('items', $alls);
	
	  $ret=$sm->fetch('kp/suppliers_only_contacts.html');
	 

 	

	
}elseif(isset($_POST['action'])&&($_POST['action']=="load_bdetails")){
	$supplier_id=abs((int)$_POST['supplier_id']);
	$current_id=abs((int)$_POST['current_id']);
	
	$_bd=new BDetailsGroup;
	$arr=$_bd->GetItemsByIdArr($supplier_id,$current_id);
	
	$sm=new SmartyAj;
	$sm->assign('pos',$arr);
	
	$ret=$sm->fetch('kp/bdetails_list.html');

}elseif(isset($_POST['action'])&&($_POST['action']=="load_condetails")){
	$supplier_id=abs((int)$_POST['supplier_id']);
	$current_id=abs((int)$_POST['current_id']);
	
	$_bd=new SupContractGroup();
	$arr=$_bd->GetItemsByIdArr($supplier_id, $current_id, 0);
	
	//print_r($arr);
	
	$sm=new SmartyAj;
	$sm->assign('pos2',$arr);
	
	$ret=$sm->fetch('kp/contracts_list.html');
	

	
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
	
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="load_positions")){
	//вывод позиций
	
	$except_id=abs((int)$_POST['bill_id']);
	$_bi1=new KpItem;
	$bi1=$_bi1->GetItemById($except_id);
	$main_id=abs((int)$_POST['main_id']); //id главной позиции
	$currency_id=abs((int)$_POST['currency_id']); //id главной позиции
	
	
	$already_in_bill=array();
	
	$complex_positions=$_POST['complex_positions'];
	
	
	foreach($complex_positions as $kk=>$vv){
		$valarr=explode(';',$vv);
		
		
		$already_in_bill[]=array($valarr[0],'position_id'=>$valarr[16],'pl_discount_id'=>$valarr[3],'pl_discount_value'=>$valarr[4], 'pl_discount_rub_or_percent'=>$valarr[5], 'currency_id'=>$valarr[19], 'parent_id'=>$valarr[18]);	//используем этот массив для формирования доступных позиций...
	}
	
	//print_r($complex_positions);
	
	
	$_kpg=new KpPrepare;
	
	$_mf=new MaxFormer;
	
	
	//нам нужно получить $price_kind_id
	//получим его из первой позиции КП
	$price_kind_id=PRICE_KIND_DEFAULT_ID;
	try{
		$_c=$complex_positions[0];
		$c=	explode(';',$_c);
		$price_kind_id=$c[20];
	} catch (Exception $e) {
   // echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
	}
	
	//echo mysqlSet::$inst_count.' запросов к БД на выборку<br />';
	
	//получим список товаров п-листа, c наложением товаров в КП
	$alls=$_kpg->GetItemsByIdArr($complex_positions, $main_id, $currency_id,  $price_kind_id, false);
	
	/*
	echo '<pre>';
	print_r($alls);
	echo '</pre>';
	*/
	
	
	$_pi=new PlPosItem;
	
	$arr=array();
	foreach($alls as $kk=>$vv){
		$index=FindIndex3( $vv['pl_position_id'],  $complex_positions);
		
		//echo $index;
		$v=array();
		
		//подгрузка названия и прочих параметров из п-л
		$v=$vv;
		  
		if($index>-1){
		  $loaded_data=explode(';',$complex_positions[$index]);
		  
		 
		  //подставим значения, если они заданы ранее
		  Naloj($vv, $loaded_data, $v);
		  
		  	
		//опции - внести в общий цикл.
		  $options=array();
		  foreach($vv['options'] as $k2=>$vv2){
			  
			  
			  $index2=FindIndex3( $vv2['pl_position_id'],  $complex_positions);
		  
			  //echo $index2."<br>";
			  
			  $v2=array();
			  
			  //подгрузка названия и прочих параметров из п-л
			  $v2=$vv2;
			
			  if($index2>-1){
				$loaded_data2=explode(';',$complex_positions[$index2]);
				
			   
				//подставим значения, если они заданы ранее
				Naloj($vv2, $loaded_data2, $v2);
				
			  }
		  	  $options[]=$v2;
		   }
		   
		   $v['options']=$options;
		}
		
		$arr[]=$v;
		
		
	
		
	
	}
	
	
	
	
	$sm=new SmartyAj;
	 $sm->assign('BILLUP',BILLUP);
	
	$sm->assign('pospos',$arr);
	
	$_pvd=new PlDisGroup; $discs1=$_pvd->GetItemsArr();
	$sm->assign('discs_for_js',$discs1);
	
	//print_r($discs1[1]['id']);
	//echo count($discs1);
	
	$sm->assign('cannot_view_pm',!$au->user_rights->CheckAccess('w',700));
	
	$sm->assign('can_override_manager_discount', $au->user_rights->CheckAccess('w',751)); //скидка менеджера без ограничений
	$sm->assign('can_override_ruk_discount', $au->user_rights->CheckAccess('w',752));
	
	$sm->assign('can_ruk_discount',$au->user_rights->CheckAccess('w',753));
	
	$sm->assign('can_unselect_pnr', $au->user_rights->CheckAccess('w',755));
		
	
	
	$ret.=$sm->fetch("kp/list_obor.html");

	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="transfer_positions")){
	//перенос выбранных позиций к.в. на страницу счет
		
	$id=abs((int)$_POST['id']);
	
	$main_id=abs((int)$_POST['main_id']); //id главной позиции
	$currency_id=abs((int)$_POST['currency_id']); //id главной позиции
	
	
	$complex_positions=$_POST['complex_positions'];
	
	$arr=array();
	
	$_position=new PosItem;
	$_pli=new PlPosItem;
	$_dim=new PosDimItem;
	
	$_mf=new MaxFormer;

	$_pmd=new PlDisMaxValGroup;
	$_kpf=new KpPosPMFormer;
	$_kpg=new KpPrepare;
	
	//нам нужно получить $price_kind_id
	//получим его из первой позиции КП
	$price_kind_id=PRICE_KIND_DEFAULT_ID;
	try{
		$_c=$complex_positions[0];
		$c=	explode(';',$_c);
		$price_kind_id=$c[20];
	} catch (Exception $e) {
   // echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
	}
	
	$alls=$_kpg->GetItemsByIdArr($complex_positions,  $main_id, $currency_id,  $price_kind_id, false);
		
		//print_r($alls);
	
	foreach($alls as $k=>$vv){
	//echo $index;
		$v=array();
		
		$index=FindIndex3( $vv['pl_position_id'],  $complex_positions);
		
		//подгрузка названия и прочих параметров из п-л
		$v=$vv;
		  
		if($index>-1){
		  $loaded_data=explode(';',$complex_positions[$index]);
		  
		 
		  //подставим значения, если они заданы ранее
		  Naloj($vv, $loaded_data, $v);
		  
		  $arr[]=$v;
		  
		  //перебор опций
		  //опции - внести в общий цикл.
		  $options=array();
		  foreach($vv['options'] as $k2=>$vv2){
			  
			  
			  $index2=FindIndex3( $vv2['pl_position_id'],  $complex_positions);
		  
			  //echo $index2."<br>";
			  
			  $v2=array();
			  
			  //подгрузка названия и прочих параметров из п-л
			  $v2=$vv2;
			
			  if($index2>-1){
				$loaded_data2=explode(';',$complex_positions[$index2]);
				
			   
				//подставим значения, если они заданы ранее
				Naloj($vv2, $loaded_data2, $v2);
				
				$arr[]=$v2;
			  }
		   }
		}
	}
	
	
	
	$sm=new SmartyAj;
	
	/*
	
	
	foreach($arr as $k=>$v){
		echo "$v[name] $v[pl_discount_id] $v[is_install] $v[is_calc_price] $v[total]<br>";	
	}*/
	
	$_kpf->CalcCost($arr,true);
	$sm->assign('pospos',$arr);
	 
	
	//виды скидок
	$_pd=new PlDisGroup;
	
	$sm->assign('discs1',$_pd->GetItemsArr());	
	
	$sm->assign('can_modify',true);
	
	$_bill=new KpItem;
	$bill=$_bill->getItemById($id);
	$sm->assign('bill',$bill);
	
	
	$sm->assign('cannot_view_pm',!$au->user_rights->CheckAccess('w',700));
	
	
	$sm->assign('can_add_positions',$au->user_rights->CheckAccess('w',697)); 
	$sm->assign('can_delete_positions',$au->user_rights->CheckAccess('w',699)); 
	$sm->assign('can_ruk_discount',$au->user_rights->CheckAccess('w',753));
	$sm->assign('can_unselect_pnr', $au->user_rights->CheckAccess('w',755));
	
		
	$sm->assign('BILLUP',BILLUP);
	
	
	if(!isset($_POST[ "template_name"])) 	
	$ret=$sm->fetch("kp/positions_on_page_set.html");
	else $ret=$sm->fetch("kp/positions_on_page_set_rcn.html");
	
	
}

elseif(isset($_POST['action'])&&($_POST['action']=="find_pos")){

//получим список позиций по фильтру
	$_pg=new PlPosGroupForKp;
	
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
	
	
	$_pg->ShowPos('kp/position_edit_finded.html', $dec,0,1000,false,false,true);
	
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
		/*$items[$k]['pl_discount_id']=1;
		$items[$k]['pl_discount_value']=0;
		$items[$k]['pl_discount_rub_or_percent']=0;*/
		
		$items[$k]['hash']=md5($items[$k]['pl_position_id'].'_'.$items[$k]['position_id'].'_'.$items[$k]['pl_discount_id'].'_'.$items[$k]['pl_discount_value'].'_'.$items[$k]['pl_discount_rub_or_percent']);
	}
	
	//print_r($items);
	
	$sm=new SmartyAj;
	
	$sm->assign('pospos', $items);
	$sm->assign('cannot_view_pm',!$au->user_rights->CheckAccess('w',700));
	$ret=$sm->fetch('kp/position_edit_finded.html');
	
	
	
}

elseif(isset($_POST['action'])&&(($_POST['action']=="calc_new_total")||($_POST['action']=="calc_new_nds")||($_POST['action']=="calc_new_signature"))){
	//подсчет нового итого
		
	
	$alls=array();
	$complex_positions=$_POST['complex_positions'];
	
	foreach($complex_positions as $k=>$valarr){
		$f=array();	
		
		$v=explode(';',$valarr);
		
		$f['quantity']=$v[1];
		$f['id']=$v[0];
		$f['pl_position_id']=$v[0];
		
		
		//скидки
		$f['pl_discount_id']=$v[3];
		$f['pl_discount_value']=$v[4];
		$f['pl_discount_rub_or_percent']=$v[5];
		
		
		
		$f['price']=$v[2];
		
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
		
		$f['parent_id']=$v[18];
		$f['currency_id']=$v[19];
		$f['price_kind_id']=$v[20];
		
		//vsego
		$f['total']=$v[13];
		
		
		$alls[]=$f;
		
		/*echo '<pre>';
		print_r($f);
		echo '</pre>';*/
		
	}
	
	
	$_bpf=new KpPosPMFormer;
	$cost=$_bpf->CalcCost($alls, true);
	$currency=$_bpf->GetCurrency($alls);
	
	//var_dump($currency);
	
	if($_POST['action']=="calc_new_total") $ret=$cost; //.' '.$currency['signature'];
	if($_POST['action']=="calc_new_signature") $ret=$currency['signature'];
	
	if($_POST['action']=="calc_new_nds") $ret=$_bpf->CalcNDS($alls);
	
}

//РАБОТА С ПРИМЕЧАНИЯМИ
elseif(isset($_POST['action'])&&($_POST['action']=="redraw_notes")){
	$sm=new SmartyAj;
	
	$user_id=abs((int)$_POST['user_id']);
	
	$rg=new KpNotesGroup;
	
	$sm->assign('items',$rg->GetItemsByIdArr($user_id,0,0,false, $au->user_rights->CheckAccess('w',703), $au->user_rights->CheckAccess('w',704), $result['id']));
	$sm->assign('word','notes');
	$sm->assign('user_id',$user_id);
	$sm->assign('named','Примечания');
	
	$sm->assign('can_edit', $au->user_rights->CheckAccess('w',702));
	
	
	$ret=$sm->fetch('kp/d_notes.html');
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="add_notes")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',702)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	
	
	$ri=new KpNotesItem;
	$ri->Add(array(
				'note'=>SecStr(iconv("utf-8","windows-1251",$_POST['note'])),
				'pdate'=>time(),
				'user_id'=>$user_id,
				'posted_user_id'=>$result['id']
			));
	
	$log->PutEntry($result['id'],'добавил примечания по коммерческому предложению', NULL,702, NULL,SecStr(iconv("utf-8","windows-1251",$_POST['note']),9),$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="edit_notes")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',702)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	$id=abs((int)$_POST['id']);
	
	
	$ri=new KpNotesItem;
	$ri->Edit($id,
				array(
				'note'=>SecStr(iconv("utf-8","windows-1251",$_POST['note'])),
				'pdate'=>time(),
				'posted_user_id'=>$result['id']/*,
				'user_id'=>$user_id*/
			));
	
	$log->PutEntry($result['id'],'редактировал примечания по коммерческому предложению', NULL,702,NULL, SecStr(iconv("utf-8","windows-1251",$_POST['note']),9),$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="delete_notes")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',702)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	$id=abs((int)$_POST['id']);
	
	
	$ri=new KpNotesItem;
	$ri->Del($id);
	
	$log->PutEntry($result['id'],'удалил примечания по коммерческому предложению', NULL,702,NULL,NULL,$user_id);
	
}

//подсветка утверждений карты
elseif(isset($_POST['action'])&&($_POST['action']=="redraw_is_confirmed_shipping_confirmer")){
	$state=abs((int)$_POST['state']);
	if($state==0){
		$ret='';	
	}elseif($state==1){
		$ret=$result['position_name'].' '.$result['name_s'].' '.' '.date("d.m.Y H:i:s",time());	
	}
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="redraw_is_confirmed_price_confirmer")){
	$state=abs((int)$_POST['state']);
	if($state==0){
		$ret='';	
	}elseif($state==1){
		$ret=$result['position_name'].' '.$result['name_s'].' '.' '.date("d.m.Y H:i:s",time());	
	}
	
}
//utv- razutv
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_confirm_price")){
	$id=abs((int)$_POST['id']);
	$flag_to_payments=abs((int)$_POST['flag_to_payments']);
	
	$_ti=new KpItem;
	
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
		if(($au->user_rights->CheckAccess('w',711))||$au->user_rights->CheckAccess('w',96)){
			
			  $check_confirm=true;
			  
			  if($au->user_rights->CheckAccess('w',711)&&$au->user_rights->CheckAccess('w',763)){
				 $check_confirm=true; 
			 // }elseif($au->user_rights->CheckAccess('w',711)&&!$au->user_rights->CheckAccess('w',763)&&($result['id']==$trust['manager_id'])){
			 }elseif($au->user_rights->CheckAccess('w',711)||$au->user_rights->CheckAccess('w',763)){	 
			  	 $check_confirm=true; 
			  }else  $check_confirm=false;
					  
			
			
			if($check_confirm&&($trust['status_id']==2)||($trust['status_id']==9)||($trust['status_id']==10)||($trust['status_id']==27)){
				$_ti->Edit($id,array('is_confirmed_price'=>0, 'is_auto_confirmed_price'=>0,'user_confirm_price_id'=>$result['id'], 'confirm_price_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'снял утверждение коммерческого предложения',NULL,711, NULL, NULL,$bill_id);
					
			}
		}else{
			//нет прав	
		}
		
	}else{
		//есть права
		if($au->user_rights->CheckAccess('w',709)||$au->user_rights->CheckAccess('w',817)){
			
			$check_confirm=true;
			  
			  if($au->user_rights->CheckAccess('w',817)&&(($trust['warranty_id']==2)||($trust['paymode_id']==2))){
				  $check_confirm=true;
			  }elseif($au->user_rights->CheckAccess('w',709)&&!(($trust['warranty_id']==2)||($trust['paymode_id']==2))){
				  $check_confirm=true;
			  }else $check_confirm=false;
			
			if($check_confirm&&($trust['status_id']==1)){
				$_ti->Edit($id,array('is_confirmed_price'=>1,  'is_auto_confirmed_price'=>0,'user_confirm_price_id'=>$result['id'], 'confirm_price_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'утвердил коммерческое предложение',NULL,709, NULL, NULL,$bill_id);	
				
				
			}
		}else{
			//do nothing
		}
	}
	
	
	$acg=new KpGroup;
	
	$shorter=abs((int)$_POST['shorter']);
	if($shorter==0) $template='kp/kps_list.html';
	else{
		 $template='kp/kps_list_inner.html';
		$acg->prefix='_kps';	 
	}
	
	
	
	
	$dec=new  DBDecorator;
	
	$dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	//if($shorter!=0) $dec->AddEntry(new SqlEntry('p.komplekt_ved_id',$trust['komplekt_ved_id'], SqlEntry::E));
	
	
	
	
	$acg->SetAuthResult($result);
	
	 
	
	
	$ret=$acg->ShowPos($template, //0
	$dec, //1
	0, //2
	100, //3
	   $au->user_rights->CheckAccess('w',696), //4
	  $au->user_rights->CheckAccess('w',701)||$au->user_rights->CheckAccess('w',712), //5
	  $au->user_rights->CheckAccess('w',713),  //6
	  '', //7
	  $au->user_rights->CheckAccess('w',709), //8
	  $au->user_rights->CheckAccess('w',96),   //9
	  false, //10
	  true,  //11
	    $au->user_rights->CheckAccess('w',714),  //12
		NULL, //13
	  $au->user_rights->CheckAccess('w',711), //14
	  $au->user_rights->CheckAccess('w',843), //15
	  $au->user_rights->CheckAccess('w',762), //16
	   $au->user_rights->CheckAccess('w',712), //17
	   $au->user_rights->CheckAccess('w',816), //27
	   $au->user_rights->CheckAccess('w',763), //19
	   $au->user_rights->CheckAccess('w',824), //20
	    $au->user_rights->CheckAccess('w',842), //21
		0 //22
		 
		
	   );
	
	
		
}elseif(isset($_POST['action'])&&($_POST['action']=="scan_confirm_price")){
	$id=abs((int)$_POST['id']);
	$_ti=new KpItem;
	
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
	
	
	$sm->assign('can_confirm_price', $au->user_rights->CheckAccess('w',709));
	$sm->assign('can_super_confirm_price', $au->user_rights->CheckAccess('w',96));
	
	//$itm=array();
	
	$sm->assign('filename','bill.php');
	$sm->assign('item',$trust);
	$sm->assign('user_id',$result['id']);
	
	$ret=$sm->fetch('kp/toggle_confirm_price.html');
	
	
}

//udalenie-annulirovabie
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_annul")){
	$id=abs((int)$_POST['id']);
	
	if(isset($_POST['from_card'])&&($_POST['from_card']==1)) $from_card=1;
	else $from_card=0;
	
	$note=SecStr(iconv("utf-8","windows-1251",$_POST['note']));
	
	$_ti=new KpItem;
	
	$_si=new UserSItem;
	
	$trust=$_ti->getitembyid($id);
	
	$log=new ActionLog;
	$_stat=new DocStatusItem;
	
	
	
	if(($trust['status_id']==1)&&($trust['is_confirmed']==0)){
		//удаление	
		if($au->user_rights->CheckAccess('w',713)){
			$_ti->Edit($id,array('status_id'=>3),false,$result);
			
			$stat=$_stat->GetItemById(3);
			$log->PutEntry($result['id'],'аннулирование коммерческого предложения',NULL,713,NULL,'коммерческое предложение № '.$trust['code'].': установлен статус '.$stat['name'],$id);	
			
			//уд-ть связанные документы
			//$_ti->AnnulBindedDocuments($id);	
			
			//внести примечание
			$_ni=new KpNotesItem;
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
		if($au->user_rights->CheckAccess('w',714)){
			$_ti->Edit($id,array('status_id'=>1),false,$result);
			
			$stat=$_stat->GetItemById(1);
			$log->PutEntry($result['id'],'восстановление коммерческого предложения',NULL,714,NULL,'коммерческое предложение № '.$trust['code'].': установлен статус '.$stat['name'],$id);
			
			//внести примечание
			$_ni=new KpNotesItem;
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
		  $acg=new KpGroup;
	  $shorter=abs((int)$_POST['shorter']);
	  if($shorter==0) $template='kp/kps_list.html';
	 else{
		 $template='kp/kps_list_inner.html';
		$acg->prefix='_kps';	 
	}
	  
	  
	
	  
	  $dec=new  DBDecorator;
	  
	  $dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	//  if($shorter!=0) $dec->AddEntry(new SqlEntry('p.komplekt_ved_id',$trust['komplekt_ved_id'], SqlEntry::E));
	  
	  $acg->SetAuthResult($result);
	  
	  
	
	   
	   $ret=$acg->ShowPos($template, //0
	$dec, //1
	0, //2
	100, //3
	   $au->user_rights->CheckAccess('w',696), //4
	  $au->user_rights->CheckAccess('w',701)||$au->user_rights->CheckAccess('w',712), //5
	  $au->user_rights->CheckAccess('w',713),  //6
	  '', //7
	  $au->user_rights->CheckAccess('w',709), //8
	  $au->user_rights->CheckAccess('w',96),   //9
	  false, //10
	  true,  //11
	    $au->user_rights->CheckAccess('w',714),  //12
		NULL, //13
	  $au->user_rights->CheckAccess('w',711), //14
	  $au->user_rights->CheckAccess('w',843), //15
	  $au->user_rights->CheckAccess('w',762), //16
	   $au->user_rights->CheckAccess('w',712), //17
	   $au->user_rights->CheckAccess('w',816), //18
	   $au->user_rights->CheckAccess('w',763), //19
	   $au->user_rights->CheckAccess('w',824), //20
	    $au->user_rights->CheckAccess('w',842), //21
		0 //22
		 
		
	   );
	   
	}else{
		$editing_user=$_ti->getitembyid($id);
		$sm=new SmartyAj;
		
		
		
		//блок аннулирования
		
		$editing_user['can_annul']=$_ti->DocCanAnnul($editing_user['id'],$reason)&&$au->user_rights->CheckAccess('w',713);
		if(!$au->user_rights->CheckAccess('w',713)) $reason='недостаточно прав для данной операции';
		$editing_user['can_annul_reason']=$reason;
		
		$editing_user['binded_to_annul']=$_ti->GetBindedDocumentsToAnnul($editing_user['id']);
		
		
		$editing_user['can_restore']=$_ti->DocCanRestore($editing_user['id'],$reason)&&$au->user_rights->CheckAccess('w',714);
			if(!$au->user_rights->CheckAccess('w',714)) $reason='недостаточно прав для данной операции';
		
		
		
		$sm->assign('bill',$editing_user);
		$ret=$sm->fetch('kp/toggle_annul_card.html');		
	}
		
	
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
	
	
	$ret=$_kr->InSh($id,$bill_id,'kp/in_shs.html',$result['org_id'],true, $pl_position_id, $pl_discount_id, $pl_discount_value,  $pl_discount_rub_or_percent );
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="draw_positions")){
	$id=abs((int)$_POST['id']);
	
	$_bill=new BillItem;
	
	$bill=$_bill->GetItemById($id);
	
	//kp/position_actions.html" bill=$bill action=1}%
	$sm=new SmartyAj;
	
	$sm->assign('filename','bill.php');
	
	$_bpg=new BillPosGroup;
	$bpg=$_bpg->GetItemsByIdArr($bill['id']);
	//print_r($bpg);
	$sm->assign('positions',$bpg);
	$sm->assign('has_positions',true);
	$_bpf=new BillPosPMFormer;
	
	$_pld=new PlDisGroup;
	$sm->assign('discs1',$_pld->GetItemsArr());
	
	$total_cost=$_bpf->CalcCost($bpg);
	$total_nds=$_bpf->CalcNDS($bpg);
	$sm->assign('total_cost',$total_cost);
	$sm->assign('total_nds',$total_nds);
	
	$sm->assign('action',1);
	$sm->assign('bill',$bill);
	
	
	/*
	$_bpg=new BillPosGroup;
		$bpg=$_bpg->GetItemsByIdArr($editing_user['id']);
		//print_r($bpg);
		$sm1->assign('positions',$bpg);
		//получим виды скидок
		$_pld=new PlDisGroup;
		$sm1->assign('discs1',$_pld->GetItemsArr());
		
		//стоимость и итого
		$_bpf=new BillPosPMFormer;
		$total_cost=$_bpf->CalcCost($bpg);
		$total_nds=$_bpf->CalcNDS($bpg);
		$sm1->assign('total_cost',$total_cost);
		$sm1->assign('total_nds',$total_nds);
		*/
	
	
	
	$ret=$sm->fetch('kp/position_actions.html');
		
}
elseif(isset($_POST['action'])&&($_POST['action']=="check_unconfirm")){
	//проверить, есть ли заявки с таким номером для такого уч.
	
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new KpItem;
		
		
		if(!$_ki->DocCanUnconfirmShip($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
}elseif(isset($_POST['action'])&&($_POST['action']=="check_confirm")){
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new KpItem;
		
		
		if(!$_ki->DocCanConfirmShip($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
	

}elseif(isset($_POST['action'])&&($_POST['action']=="check_unconfirm_price")){
	
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new KpItem;
		
		
		if(!$_ki->DocCanUnconfirmPrice($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
}elseif(isset($_POST['action'])&&($_POST['action']=="check_confirm_price")){
		$id=abs((int)$_POST['id']);
		
	 
		
		$_ki=new KpItem;
		
		
		if(!$_ki->DocCanConfirmPrice($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
		 
		
		//если ноль - то все хорошо
	
	
	
}
elseif(isset($_GET['action'])&&($_GET['action']=="retrieve_paymode")){
	
		
	$_pmi=new KpPaymodeItem;
	$pmi=$_pmi->GetItemById(abs((int)$_GET['paymode_id']));
	
	if($pmi!==false){
		
		$rret=array();
		foreach($pmi as $k=>$v){
			
			
			$rret[]='"'.$k.'":"'.htmlspecialchars(str_replace("\r", "", str_replace("\n",  "",  $v))).'"';
		}
		
		
		$ret='{'.implode(', ',$rret).'}';
	}
	
	
	
	
}

//подтянуть город п-ка
elseif(isset($_POST['action'])&&($_POST['action']=="load_supply_out_city")){
	
	$_sg=new SupplierCitiesGroup;
	$arr=$_sg->GetItemsByIdArr($result['org_id']);
	
	
		
	$ret=$arr[0]['name'].', '.$arr[0]['okrug_name'].', '.$arr[0]['region_name'];
	
	
}

//подтянуть код позиции установки
elseif(isset($_GET['action'])&&($_GET['action']=="preload_install")){
	
	$positions=$_GET['positions']; $costs=$_GET['costs'];
	
	$_pl=new PlPosItem;
	$code=''; $value='';
	//v($positions);
	
	foreach($positions as $k=>$v){
		$pl=$_pl->GetItemBYId(abs((int)$v));	
		
		
		if($pl['is_install']==1){
			//var_dump($pl);
			if(strlen($pl['code'])>0) $code='Позиция № '.$pl['code'];
			else $code='Позиция № '.$pl['id'];
			
			$value=$costs[$k];
			
			break;	
		}
	}
	
	
	$ret='{"value":"'.$value.'", "notes":"'.$code.'"}';
}
//проверить введенный код позиции установки
elseif(isset($_POST['action'])&&($_POST['action']=="check_install")){
	$positions=$_POST['positions'];
	$install_notes=SecStr($_POST['install_notes']);
	
	
	
	$_pl=new PlPosItem;
	 
	//v($positions);
	$code=0;
	//проверить, есть ли такая опция
	$has=false; $its_id=0; 
	foreach($positions as $k=>$v){
		$pl=$_pl->GetItemBYId(abs((int)$v));	
		
		//регулярным выражением получить либо код, либо айди
		if(eregi("PCC[0-9]+", $install_notes, $reg)){
			//echo $reg[0];	
			if($reg[0]==$pl['code']){
				$has=true; $its_id=$pl['id'];	
			}
		}elseif(eregi("[0-9]+", $install_notes, $reg)){
			//echo $reg[0];
			if($reg[0]==$pl['id']){
				$has=true; $its_id=$pl['id'];	
			}
		}
		
		
		/*if(($install_notes==$pl['id'])||($install_notes==$pl['code'])){
			$has=true; $its_id=$pl['id'];	
		}*/
	}
	
	if(!$has){
		$code=1;	
	}else{
		//проверить, является ли опция опцией установки
		$pl=$_pl->GetItemById($its_id);
		if($pl['is_install']!=1){
			$code=2;
		}
	}
	
	$ret=$code;
}

//подтянуть код позиции доставки
elseif(isset($_GET['action'])&&($_GET['action']=="preload_delivery")){
	$positions=$_GET['positions']; $costs=$_GET['costs'];
	
	$_pl=new PlPosItem;
	$code=''; $value='';
	//v($positions);
	
	foreach($positions as $k=>$v){
		$pl=$_pl->GetItemBYId(abs((int)$v));	
		
		
		if($pl['is_delivery']==1){
			//var_dump($pl);
			if(strlen($pl['code'])>0) $code='Позиция № '.$pl['code'];
			else $code='Позиция № '.$pl['id'];
			
			$value=$costs[$k];
			
			break;	
		}
	}
	
	
	$ret='{"value":"'.$value.'", "notes":"'.$code.'"}';
}
//проверить введенный код позиции доставки
elseif(isset($_POST['action'])&&($_POST['action']=="check_delivery")){
	$positions=$_POST['positions'];
	$install_notes=SecStr($_POST['delivery_notes']);
	
	$_pl=new PlPosItem;
	 
	//v($positions);
	$code=0;
	//проверить, есть ли такая опция
	$has=false; $its_id=0; 
	foreach($positions as $k=>$v){
		$pl=$_pl->GetItemBYId(abs((int)$v));	
		
		//регулярным выражением получить либо код, либо айди
		if(eregi("PCC[0-9]+", $install_notes, $reg)){
			//echo $reg[0];	
			if($reg[0]==$pl['code']){
				$has=true; $its_id=$pl['id'];	
			}
		}elseif(eregi("[0-9]+", $install_notes, $reg)){
			//echo $reg[0];
			if($reg[0]==$pl['id']){
				$has=true; $its_id=$pl['id'];	
			}
		}
		
		/*if(($install_notes==$pl['id'])||($install_notes==$pl['code'])){
			$has=true; $its_id=$pl['id'];	
		}*/
	}
	
	if(!$has){
		$code=1;	
	}else{
		//проверить, является ли опция опцией доставки
		$pl=$_pl->GetItemById($its_id);
		if($pl['is_delivery']!=1){
			$code=2;
		}
	}
	
	$ret=$code;
}



elseif(isset($_POST['action'])&&($_POST['action']=="cookie_positions")){
	//записываем позиции в cookie для перенос в п/л
	
	$except_id=abs((int)$_POST['bill_id']);
	$_bi1=new KpItem;
	$bi1=$_bi1->GetItemById($except_id);
	$main_id=abs((int)$_POST['main_id']); //id главной позиции
	$currency_id=abs((int)$_POST['currency_id']); //id главной позиции
	
	//сбросим старые значения
	$_pl=new PlPosGroup;
	$_pl->MakeMemory(0);
	
	 
	$complex_positions=$_POST['complex_positions'];
	
	//внесем новые значения
	foreach($complex_positions as $kk=>$vv){
		$valarr=explode(';',$vv);
		
		//перенесли в кл. часть!
		/*
		$.cookie('quantity%{$items[rowsec].pl_id}%%{$prefix}%');
		$.cookie('discount_%{$discs1.id}%_%{$items[rowsec].pl_id}%%{$prefix}%');
		
		опции:
		$.cookie('q_a%{$option.pl_id}%%{$prefix}%');
		$.cookie('quantity%{$option.pl_id}%%{$prefix}%');
		
		if($main_id==$valarr[0]){
			setcookie('quantity'.$valarr[0].'_1',abs((float)$valarr[1]),time()+ 84000);
			setcookie('discount_'.$valarr[3].'_'.$valarr[0].'_1',abs((float)$valarr[4]),time()+ 84000);
		}else{
			setcookie('q_a'.$valarr[0].'_1',1, 84000);	
			setcookie('quantity'.$valarr[0].'_1',abs((float)$valarr[1]), time()+84000);	
		}
		*/
	}
	
	//print_r($complex_positions);
	
	
		//echo mysqlSet::$inst_count.' запросов к БД на выборку<br />';
	
	
}

elseif(isset($_POST['action'])&&($_POST['action']=="copy_build")){
	//готовим позиции КП к копированию
	//url="ed_kp.php?action=0&from_begin=1&price_kind_id="+$("#price_kind_id%{$prefix}%").val()+url;
				
	
	// url=url+'&pl_position_id[]='+opt_id+';'+parent_id+';'+quantity+';'+currency_id+';'+price+';'+discount_id+';'+discount_value+';'+discount_rub_or_percent+';'+$("#price_kind_id%{$prefix}%").val();
	
	
	
	$id=abs((int)$_POST['id']);
	$_kp=new KpItem;
	$kp=$_kp->GetItemById($id);
	
	$data='ed_kp.php?action=0&from_begin=1&is_copied=1&price_kind_id='.$kp['price_kind_id'];
	
	$positions=$_kp->GetPositionsArr($id,false);
	foreach($positions as $k=>$v){
		$data.='&pl_position_id[]='.$v['pl_position_id'].';'.$v['parent_id'].';'.$v['quantity'].';'.$v['currency_id'].';'.$v['price'].';'.$v['pl_discount_id'].';'.$v['pl_discount_value'].';'.$v['pl_discount_rub_or_percent'].';'.$v['price_kind_id'].';'.$v['print_form_has_komplekt'].';'.$v['extra_charges'];
	}
	
	$ret=$data;
}

elseif(isset($_POST['action'])&&($_POST['action']=="find_init_cost")){
	
	$id=abs((int)$_POST['id']);
	$_kp=new KpItem;
	$kp=$_kp->GetItemById($id);
	$positions=$_kp->GetPositionsArr($id,false);
	
	$_kpf=new KpPosPMFormer;
	
	$init_cost=$_kpf->CalcCostSupplier($positions);
	
	$ret=$init_cost;
	
	
	$_code=762;
	if($kp['is_confirmed_price']==0) $_code=816;
	if($au->user_rights->CheckAccess('w',$_code)){
		$log->PutEntry($result['id'],'просмотр  рентабельности КП',NULL,$_code, NULL, $kp['code'], $kp['id']);	
	}
	
}


elseif(isset($_POST['action'])&&($_POST['action']=="show_extended_re")){
	
	if($au->user_rights->CheckAccess('w',824)){
			
	 	$_currency_solver=new CurrencySolver;
		$rates=$_currency_solver->GetActual();
		
		$_kpf=new KpPosPMFormer;
		
		$code=SecStr(iconv('utf-8', 'windows-1251',$_POST['code']));
		
		$_kp=new KpItem;
		
		$kp=$_kp->GetItemByFields(array('code'=>$code));
		
		$log=new ActionLog;
		$log->PutEntry($result['id'],'просмотр расширенных данных по рентабельности КП',NULL,824, NULL, $kp['code'], $kp['id']);
		
		
		$id=$kp['id'];
		
		$positions=$_kp->GetPositionsArr($id,false);
		
		$_kpf=new KpPosPMFormer;
		
	
		
		//перебрать позиции... 
		//если вид цен =2 - то только базовую цену, цену exw п-ка
		
		//если вид цен =1 -то:
		/*
			базовую цену,  $base_prices
			цену exw п-ка, $base_prices_exw
			доставку до москвы
			НДС тамож
			сбор
			СВХ брокер
			Страховка
			комиссия за перевод дс
			цена итог ддпм
		*/
		
		//	айди базовой цены поставщика = 3
		//айди валюты брать для позиции кп
		
		$_pl=new PlPosItem; $_pi=new PlPositionPriceItem;
		
		$ret='<b>Итого по позициям коммерческого предложения:</b><ul>';
		
		$currency=$_kpf->GetCurrency($positions);
		//print_r($currency);
		
		$base_prices=0;
		$base_prices_exw=0;
		$group_id=0; $deliveries=0; $nds=0; $sbor=0; $svh=0; $strah=0; $comission=0; $itog_ddpm=0; $delivery_value=0; $delivery_rub=0; $delivery_rub_r=0;  $delivery_rub_r_was=0; $broker_costs=0; $customs_values=0;
		
		$pnr=0; $has_pnr=false;
		//найдем ПНР
		foreach($positions as $k=>$v){
			if($v['is_install']==1){
				$has_pnr=true;
				$pnr=$_pl->DispatchCalcPriceF($v['position_id'],  $v['currency_id'],   NULL,  NULL,  NULL, NULL, NULL,   /*3*/ $kp['price_kind_id'],  NULL,  $v['extra_charges']);
			}
		}
		
		//echo $pnr;
		
		foreach($positions as $k=>$v){
			$pl=$_pl->getitembyid($v['position_id']);
			
			$calculation_form_id=$_pl->GetCalculationFormId($pl['producer_id']);
			
			$group_id=$v['two_group_id'];
			if(($kp['price_kind_id']==1)||($kp['price_kind_id']==2)){
			
				$pi=$_pi->GetItemByFields(array('pl_position_id'=>$v['position_id'], 'currency_id'=>$v['currency_id'], 'price_kind_id'=>3));
				//echo $pi['price'];
				$base_price=$pi['price'];
				$base_prices+=$v['quantity']* $base_price;
				
				//найти цену Exw поставщика
				$base_price_exw=$_pl->DispatchCalcPriceF($v['position_id'],  $v['currency_id'],   NULL,  NULL,  NULL, NULL, NULL,  3,  NULL,  $v['extra_charges']);
				$base_prices_exw+=$v['quantity']*$base_price_exw;
				
				 
			}
			//найти остальные данные для ddpm
			if($kp['price_kind_id']==1){
				/*
				доставку до москвы
			НДС тамож
			сбор
			СВХ брокер
			Страховка
			комиссия за перевод дс
			цена итог ддпм
				*/
				 if($v['parent_id']==0){
					 
					 
					 if($calculation_form_id==1){
					 
						$delivery=$pl['delivery_ddpm']*$v['quantity'];  //$_pl->CalcOptionPriceF($f['position_id'], $v['currency_id'],    NULL,  NULL, NULL,  NULL,  NULL, $kp['price_kind_id'], NULL);
						$deliveries+=$delivery; //$base_price_exw
						
						 
						$nds_per_position=$_pl->CalcNDS($base_price_exw,  $v['pl_position_id'],  $v['currency_id'], NULL, 3,NULL,true);
						$nds_=($v['quantity']*$nds_per_position);
						$nds+=$nds_;
						
						$sbor_=$v['quantity']*$pl['duty_ddpm'];
						$sbor+=$sbor_;
						
						
						$svh_=$v['quantity']*$pl['svh_broker'];
						$svh+=$svh_;
						
						$strah_=($v['quantity']*$_pl->CalcInsur($base_price_exw,  $nds_per_position,   $v['pl_position_id'], $v['currency_id'], NULL, 3, NULL, true));
						$strah+=$strah_;
						
						$comission_=($v['quantity']*$_pl->CalcComission($base_price_exw, $nds_per_position,   $v['pl_position_id'], $v['currency_id'], NULL, 3, NULL, true));
						$comission+=$comission_;
						
						$itog_ddpm_ =$base_price_exw*$v['quantity']+$delivery+$nds_+$sbor_+$svh_+$strah_+$comission_;
						$itog_ddpm+=$itog_ddpm_;
					
					 }elseif($calculation_form_id==2){
						
						$customs_value=$_pl->CalcCustomsValue($base_price_exw,  $v['pl_position_id'],  $v['currency_id'], NULL, 3, NULL, true, $rates);
						$customs_values+=$v['quantity']*$customs_value;
						
						
						$delivery_value=$pl['delivery_value']*$v['quantity'];
						
						
						$delivery_rub=$v['quantity']*$pl['delivery_rub'];
						
						$delivery_rub_r=$v['quantity']*$_pl->CalcDeliveryRub( $v['pl_position_id'],  $v['currency_id'],  NULL, 3, NULL, $rates, true);
						
						$nds_per_position=$_pl->CalcNDS($base_price_exw,  $v['pl_position_id'],  $v['currency_id'], NULL, 3,NULL,true);
						$nds_=($v['quantity']*$nds_per_position);
						$nds+=$nds_;
						
						$sbor_=$v['quantity']*$pl['duty_ddpm'];
						$sbor+=$sbor_;
						
						$broker_cost=$pl['broker_costs'];
						$broker_costs+=$v['quantity']*$pl['broker_costs'];
						
						$svh_=$v['quantity']*$pl['svh_broker'];
						$svh+=$svh_;
						
						$strah_=($v['quantity']*$_pl->CalcInsur($base_price_exw,  $nds_per_position,   $v['pl_position_id'], $v['currency_id'], NULL, 3, NULL, true));
						$strah+=$strah_;
						
						$comission_=0; //($v['quantity']*$_pl->CalcComission($base_price_exw, $nds_per_position,   $v['pl_position_id'], $v['currency_id'], NULL, 3, NULL, true));
						$comission+=$comission_;
						
						
						$itog_ddpm_=$base_price_exw*$v['quantity']+$delivery_value+$delivery_rub_r+$nds_+$sbor_+$svh_+$strah_+/*$comission_+*/$customs_value+$broker_cost;
						$itog_ddpm+=$itog_ddpm_;
					 }
					
				 }else{
					$delivery=0; 
					$deliveries+=$v['quantity']*$delivery;
					
					if($calculation_form_id==1){
					 
						$nds_per_position=$_pl->CalcOptionNDS($base_price_exw, 	$v['pl_position_id'], $v['currency_id'], NULL, 3, NULL);
						$nds_=($v['quantity']*$nds_per_position);
						$nds+=$nds_;
						
						 
						
						$sbor_=0;
						$sbor+=$v['quantity']*$sbor_;
						
						$svh_=0;
						$svh+=$v['quantity']*$svh_;
						
						$strah_=($v['quantity']*$_pl->CalcOptionInsur($base_price_exw, $nds_,   $v['pl_position_id'], $v['currency_id'], NULL, 3, NULL,true));
						$strah+=$strah_;
						
						$comission_=($v['quantity']*$_pl->CalcOptionComission($base_price_exw, $nds_,   $v['pl_position_id'],  $v['currency_id'], NULL, 3,NULL,true));
						$comission+=$comission_;
						
						
						$itog_ddpm_=$base_price_exw*$v['quantity']+$delivery+$nds_+$sbor_+$svh_+$strah_+$comission_;
						$itog_ddpm+=$itog_ddpm_;
					
					}elseif($calculation_form_id==2){
						
						$customs_value=$_pl->CalcOptionCustomsValue($base_price_exw,   $v['pl_position_id'],   $v['currency_id'],  NULL, 3, NULL, true, $rates);
						$customs_values+=$v['quantity']*$customs_value;
						//echo $customs_values.'<br>';
						
						$broker_cost=$pl['broker_costs'];
						$broker_costs+=$v['quantity']*$pl['broker_costs'];
						
						
						$nds_per_position=$_pl->CalcOptionNDS($base_price_exw, 	$v['pl_position_id'], $v['currency_id'], NULL, 3, NULL);
						$nds_=($v['quantity']*$nds_per_position);
						$nds+=$nds_;
						
						 
						
						$sbor_=0;
						$sbor+=$v['quantity']*$sbor_;
						
						$svh_=0;
						$svh+=$v['quantity']*$svh_;
						
						$strah_=($v['quantity']*$_pl->CalcOptionInsur($base_price_exw, $nds_,   $v['pl_position_id'], $v['currency_id'], NULL, 3, NULL,true));
						$strah+=$strah_;
						
						$comission_=0; //($v['quantity']*$_pl->CalcOptionComission($base_price_exw, $nds_,   $v['pl_position_id'],  $v['currency_id'], NULL, 3,NULL,true));
						$comission+=$comission_;
						
						$itog_ddpm_=$base_price_exw*$v['quantity']+$nds_+$sbor_+$svh_+$strah_+/*$comission_+*/$customs_value+$broker_cost;
						$itog_ddpm+=$itog_ddpm_;
					}
					 
					
				 }
				 
				// echo $v['pl_position_id'].': '.$strah_.'<br>';
			}
		}
		
		if(($kp['price_kind_id']==1)||($kp['price_kind_id']==2)){
			if($has_pnr){
				$ret.='<li>Cумма ПНР: '.round($pnr, $_pl->_round_define->DefineDigits($group_id)).'  '.$currency['signature'].'<br />'	;
			}else{
				$ret.='<li>ПНР не включены в КП.<br />'	;
			}
			
			$ret.='<li>Сумма базовых цен поставщика: '.round($base_prices, $_pl->_round_define->DefineDigits($group_id)).'  '.$currency['signature'].'<br>'; 
			
			
			
			if($kp['price_kind_id']==2) $ret.='<b>';
			$ret.='<li>Сумма цен ExW поставщика: '.round($base_prices_exw, $_pl->_round_define->DefineDigits($group_id)).'  '.$currency['signature'].'<br>'; 
			if($kp['price_kind_id']==2) $ret.='</b>';
			
			
		
		}
		
		if($kp['price_kind_id']==1){
			 if($calculation_form_id==1){
			
				$ret.='<li>Стоимость доставки: '.round($deliveries, $_pl->_round_define->DefineDigits($group_id)).'  '.$currency['signature'].'<br>'; 
				
				$ret.='<li>Сумма тамож. НДС: '.round($nds, $_pl->_round_define->DefineDigits($group_id)).'  '.$currency['signature'].'<br>'; 
				
				$ret.='<li>Сумма сбора : '.round($sbor, $_pl->_round_define->DefineDigits($group_id)).'  '.$currency['signature'].'<br>'; 
				
				$ret.='<li>Сумма СВХ, брокер: '.round($svh, $_pl->_round_define->DefineDigits($group_id)).'  '.$currency['signature'].'<br>'; 
				
				$ret.='<li>Сумма страховки, 0,2% : '.round($strah, $_pl->_round_define->DefineDigits($group_id)).'  '.$currency['signature'].'<br>'; 
				//Комиссия за перевод д/с, 1% 
				
				$ret.='<li>Сумма комиссии за перевод д/с, 1% : '.round($comission, $_pl->_round_define->DefineDigits($group_id)).'  '.$currency['signature'].'<br>'; 
			
			
			 }else{
				$ret.='<li>Стоимость пошлины: '.round($customs_values, $_pl->_round_define->DefineDigits($group_id)).'  '.$currency['signature'].'<br>';  
				
				
				$ret.='<li>Стоимость затрат брокера: '.round($broker_costs, $_pl->_round_define->DefineDigits($group_id)).'  '.$currency['signature'].'<br>';  
				
				
				
				$ret.='<li>Стоимость доставки до границы: '.round($delivery_value, $_pl->_round_define->DefineDigits($group_id)).'  '.$currency['signature'].'<br>';  
				
				$ret.='<li>Стоимость доставки после границы: '.round($delivery_rub_r, $_pl->_round_define->DefineDigits($group_id)).'  '.$currency['signature'].'/ '.$delivery_rub.' руб.<br>';
				
				
				$ret.='<li>Сумма тамож. НДС: '.round($nds, $_pl->_round_define->DefineDigits($group_id)).'  '.$currency['signature'].'<br>'; 
				
				$ret.='<li>Сумма сбора : '.round($sbor, $_pl->_round_define->DefineDigits($group_id)).'  '.$currency['signature'].'<br>'; 
				
				$ret.='<li>Сумма СВХ, брокер: '.round($svh, $_pl->_round_define->DefineDigits($group_id)).'  '.$currency['signature'].'<br>'; 
				
				$ret.='<li>Сумма страховки, 0,1% : '.round($strah, $_pl->_round_define->DefineDigits($group_id)).'  '.$currency['signature'].'<br>'; 
				//Комиссия за перевод д/с, 1% 
				
				//$ret.='<li>Сумма комиссии за перевод д/с, 1% : '.round($comission, $_pl->_round_define->DefineDigits($group_id)).'  '.$currency['signature'].'<br>'; 
			 }
			
			if($kp['price_kind_id']==1) $ret.='<b>';
			//$ret.='Сумма итоговых цен DDPM : '.round($itog_ddpm,  $_pl->_round_define->DefineDigits($group_id)).'  '.$currency['signature'].'<br>'; 
			
			if($kp['price_kind_id']==1) $ret.='<li>Сумма итоговых цен DDPM : '. ($_kpf->CalcCostSupplier($positions)).'  '.$currency['signature'].'<br>'; 
			else $ret.='<li>Сумма итоговых цен DDPM : '.round($itog_ddpm,  $_pl->_round_define->DefineDigits($group_id)).'  '.$currency['signature'].'<br>'; 
			
			if($kp['price_kind_id']==1) $ret.='</b>';
			
		}
		 
		$ret.= '<li><strong>Сумма КП: '.$_kp->CalcCost($id,$positions).'  '.$currency['signature'].'</strong>';
		
		$ret.='</ul>';
	//	$ret.=$_pl->_round_define->DefineDigits($group_id);
	}
	
}



elseif(isset($_POST['action'])&&($_POST['action']=="load_pdf_addresses")){
	//получить список контактов к-та с эл. почтой (ее айди=5)
	//получить список сотр-ков с эл. почтой
	$_sdg=new SupplierContactDataGroup;
	$_udg=new UserContactDataGroup;
	
	//ограничения по сотруднику
	$limited='';
	$limited_user=NULL;
	if($au->FltUser($result)){
		//echo 'z';
		$_u_to_u=new UserToUser();
		$u_to_u=$_u_to_u->GetExtendedViewedUserIdsArr($result['id']);
		$limited_user=$u_to_u['sector_ids'];
		$limited=' and id in('.implode(', ', $limited_user).') ';
	}
	
	
	
	
	$sql='
		(select "0" as kind, name as name_s, "" as login, position as position_s, id, "" as email_s
			from supplier_contact
			where supplier_id="'.abs((int)$_POST['supplier_id']).'"
			and id in(select distinct contact_id from supplier_contact_data where kind_id=5)
			)
		UNION ALL
		(select "1" as kind, u.name_s as name_s, u.login as login, up.name as position_s, u.id, u.email_s as email_s		
			from user as u
			left join user_position as up on up.id=u.position_id
			where u.is_active=1 
			/*and u.id in(select distinct user_id from user_contact_data where kind_id=5)*/ '.$limited.'
			
		)		
		order by 1 asc, 2 asc';
		
	//echo $sql;	
	$set=new mysqlset($sql);
	$rs=$set->GetResult();
	$rc=$set->GetResultnumrows();
	$alls=array(); $old=array();
	for($i=0; $i<$rc; $i++){
		$f=mysqli_fetch_array($rs);
		foreach($f as $k=>$v) $f[$k]=stripslashes($v);
		
		if($f['kind']==0) $data=$_sdg->GetItemsByIdArr($f['id']);
		else{
			 $data=$_udg->GetItemsByIdArr($f['id']);
			 
			 $was_in=false; foreach($data as $k=>$v) if(($v['kind_id']==5)&&($v['value']==$f['email_s'])) $was_in=$was_in||true;
			 //добавить адрес из карты
			 if(!$was_in) $data[]=array('id'=>0, 'kind_id'=>5, 'value'=>$f['email_s']);
		}
		
		$data1=array();
		foreach($data as $k=>$v){
			if($v['kind_id']==5) $data1[]=$v;	
		}
		
		
		$f['is_begin']=($i==0);
		$f['has_hr']=($f['kind']==1)&&($old['kind']==0);
		
		$f['data']=$data1;
		
		$alls[]=$f;	
		$old=$f;
	}
	
	//print_r($alls);
		
	$sm=new SmartyAj;
	
	$sm->assign('items', $alls);
	$ret=$sm->fetch('kp/pdf_addresses.html');

}


//настройка реестра
elseif(isset($_POST['action'])&&(($_POST['action']=="mode_reestr"))){
	$_views=new KP_ViewGroup;
	$_view=new KP_ViewItem;
	
	$cols=$_POST['cols'];
	
	$_views->Clear($result['id']);
	$ord=0;
	foreach($cols as $k=>$v){
		$params=array();
		$params['col_id']=(int)$v;
		$params['user_id']=$result['id'];
		$params['ord']=$ord;
			
		$ord+=10;
		$_view->Add($params);
		
		 
	}
}
elseif(isset($_POST['action'])&&(($_POST['action']=="mode_reestr_clear"))){
	$_views=new KP_ViewGroup;
 
	 
	
	$_views->Clear($result['id']);
	 
}


//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>