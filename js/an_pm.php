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
require_once('../classes/billgroup.php');


require_once('../classes/billpospmformer.php');

require_once('../classes/maxformer.php');
require_once('../classes/opfitem.php');


require_once('../classes/billnotesgroup.php');
require_once('../classes/billnotesitem.php');
require_once('../classes/billpositem.php');
require_once('../classes/billpospmitem.php');
require_once('../classes/posdimitem.php');

require_once('../classes/billdates.php');
require_once('../classes/billreports.php');
require_once('../classes/billprepare.php');

require_once('../classes/user_s_item.php');
require_once('../classes/an_pm.php');


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


$ret='';
if(isset($_POST['action'])&&($_POST['action']=="load_positions")){
	//вывод позиций к.в. для счета
	$dec_sep=' ';
	
	$row_ids=$_POST['row_ids'];
	
	
	$an=new AnPm;
	
	$an->ShowData('',$result['org_id'],DateFromDmy(date('d.m.Y',0)),DateFromDmy('31.12.2030'),'an_pm/an_pm_list.html',new DBDecorator,'an_pm.php',true,false,DEC_SEP,$alls,true,1,1,1,NULL, $au->user_rights->CheckAccess('w',363));
	
	$items=array();
	$total_quantity=0; $total_pm=0; $total_marja=0; $total_summa=0;
	foreach($alls as $k=>$v){
		$bill=array();
		
		$subs=array();
		foreach($v['subs'] as $kk=>$vv){
			if(!in_array($vv['p_id'],$row_ids)) continue;
			$subs[]=$vv;
				$total_quantity+=$vv['quantity'];
						
						$total_pm+=$vv['unf_vydacha'];
						$total_marja+=$vv['unf_discount_given'];
		}
		
		$bill=$v;
		$bill['subs']=$subs;
		
		if(count($subs)>0){
			 $items[]=$bill;
			 
			  $total_summa+=round($v['total_unf'],2);
		}
		
	}
	
	//var_dump($row_ids);
	//var_dump($alls);
	//var_dump($items);
	
	$sm=new SmartyAj;
	//$sm->assign('do_it',true);
	$sm->assign('items',$items);
	
	 $sm->assign('total_summa',number_format($total_summa,2,'.',$dec_sep));
	  $sm->assign('total_quantity',number_format($total_quantity,2,'.',$dec_sep));
		  $sm->assign('total_pm',number_format($total_pm,2,'.',$dec_sep));
		  $sm->assign('total_marja',number_format($total_marja,2,'.',$dec_sep));
		  
	$sm->assign('view_full_version',$au->user_rights->CheckAccess('w',363));
	
	$ret.=$sm->fetch("an_pm/an_pm_edit_list.html");
	
}elseif(isset($_POST['action'])&&($_POST['action']=="transfer_positions")){
	
	
	
	$complex_data=$_POST['complex_data'];
	//$valar=array();
	foreach($complex_data as $k=>$v){
		//echo $v."<br />";
		
		$valar=explode(';',$v);
		$table_id=abs((int)$valar[0]);
		$discount_given=(float)$valar[1];
		
		if($au->user_rights->CheckAccess('w',365)||$au->user_rights->CheckAccess('w',363)){
			$_ki=new BillItem;
			 $_bpi=new BillPosItem;
			  $_bpm=new BillPosPMItem;
			
			 $bpi=$_bpi->GetItemById($table_id);
			  if($bpi!==false){
				  $bill=$_ki->GetItemById($bpi['bill_id']);
			
				  if($bill['status_id']!=10) continue;
				  
				  $bpm=$_bpm->GetItemByFields(array('bill_position_id'=>$bpi['id']));
				  
				  if($bpm!==false){
					  $_bpm->Edit($bpm['id'], array('discount_given'=>$discount_given,
					  	'discount_given_pdate'=>time(),
						'discount_given_user_id'=>$result['id']
					  
					  ));	
					  
					  //запись в журнал по счету
					 if($bpm['discount_given']!=$discount_given) $log->PutEntry($result['id'],'задал полученный +/- позиции исходящего счета', NULL,365, NULL,'позиция '.SecStr($bpi['name']).', старая сумма полученного +/- '.$bpm['discount_given'].' руб. '.', новая сумма полученного +/- '.$discount_given.' руб.',$bpi['bill_id']);
				  }
			  }
		}
	}
	
	
	
	
}


//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>