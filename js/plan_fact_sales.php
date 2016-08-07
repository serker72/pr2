<?
session_start();
header('Content-type: text/html; charset=windows-1251');

require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/discr_table_objects.php');
require_once('../classes/actionlog.php');


require_once('../classes/plan_fact_sales.class.php');

require_once('../classes/plan_fact_fact_item.class.php');

require_once('../classes/plan_fact_fact_notesitem.php');


require_once('../classes/useritem.php');
require_once('../classes/pl_curritem.php');
require_once('../classes/supplier_city_item.php');
require_once('../classes/price_kind_item.php');
require_once('../classes/pl_positem.php');

require_once('../classes/pl_posgroup.php');
require_once('../classes/pl_prodgroup.php');
require_once('../classes/pl_proditem.php');

require_once('../classes/currency/currency_solver.class.php');

require_once('../classes/supplieritem.php');
require_once('../classes/opfitem.php');


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
if(isset($_POST['action'])&&($_POST['action']=="save_changes")){
	
	$_pfs=new PlanFactSales;
	//$_pfs_item=new PlanFactSalesItem;
	$_ui=new UserItem; $_ci=new PlCurrItem;
	
	
	
	$data=$_POST['data'];
	
	//field_%{$user_id}%_%{$year}%_%{$month_no}%_%{$plan_or_fact}%
	if(is_array($data)) foreach($data as $k=>$v){
		$valarr=explode(';',$v);
		$params=array();
		$params['user_id']=abs((int)$valarr[0]);
		$params['currency_id']=abs((int)$valarr[4]);
		
		$user=$_ui->GetItemById($params['user_id']);
		$curr=$_ci->GetItemById($params['currency_id']);
		
		
		$exist=$_pfs->GetSales($valarr[2], $valarr[1], $valarr[0], $valarr[3], $valarr[4], $user['department_id'], $result['org_id'], 
		$au->user_rights->CheckAccess('w',785), 
		$au->user_rights->CheckAccess('w',786),  
		$au->user_rights->CheckAccess('w',787),  
		$au->user_rights->CheckAccess('w',788),
		NULL, 
		$au->user_rights->CheckAccess('w',813)
		 );
		
		
		
		$params['plan_or_fact']=abs((int)$valarr[3]);
		$params['month']=abs((int)$valarr[2]);
		$params['year']=abs((int)$valarr[1]);
		
		
		
		$valarr[5]=str_replace(',','.',$valarr[5]);
		$params['value']=abs((float)$valarr[5]);
		
		$params['pdate']=time();
		$params['posted_user_id']=$result['id'];
		$params['org_id']=$result['org_id'];
		
		$do_it=true;
		if($exist['can_modify']){
			if($exist['data']===false){
				//создаем
				//получить айди объекта системы...
				if($params['plan_or_fact']==0){
					 $object_id=785;
					 $ob_name='план';
				}else{
					 $object_id=787;
					 $ob_name='факт';
				}
				
								
				$_pfs->item->Add($params);
				
				
				$desciption='создал '.$ob_name.' продаж';
				$value=SecStr('создан '.$ob_name.' продаж дл€ сотрудника '.$user['name_s'].' ('.$user['login'].') на '.$_pfs->GetMonthByNumber($params['month']).' '.$params['year'].', значение: '.$params['value'].' '.$curr['signature']);
				
				
					
			}else{
				//правим
				//получить айди объекта системы...
				if($params['plan_or_fact']==0){
					 $object_id=786;
					 $ob_name='план';
				}else{
					if($au->user_rights->CheckAccess('w',813)) $object_id=813;
					else $object_id=788;
					$ob_name='факт';
					if($exist['restricted_by_period']) $do_it=false;
				}
				
				if($do_it) $_pfs->item->Edit($exist['data']['id'], $params);
				
				$desciption='редактировал '.$ob_name.' продаж';
				$value=SecStr('отредактирован '.$ob_name.' продаж дл€ сотрудника '.$user['name_s'].' ('.$user['login'].') на '.$_pfs->GetMonthByNumber($params['month']).' '.$params['year'].', старое значение: '.$exist['data']['value'].' '.$curr['signature'].', новое значение: '.$params['value'].' '.$curr['signature']);
				
			}
			
			if($do_it) $log->PutEntry($result['id'], $desciption, $params['user_id'], $object_id, NULL, $value, $params['user_id']);
			
		}
			
	}

	
}

elseif(isset($_POST['action'])&&($_POST['action']=="save_empty_changes")){
	
	$_pfs=new PlanFactSales;
	//$_pfs_item=new PlanFactSalesItem;
	$_ui=new UserItem; $_ci=new PlCurrItem;
	
	
	
	$data=$_POST['data'];
	
	//field_%{$user_id}%_%{$year}%_%{$month_no}%_%{$plan_or_fact}%
	if(is_array($data)) foreach($data as $k=>$v){
		$valarr=explode(';',$v);
		$params=array();
		$params['user_id']=abs((int)$valarr[0]);
		$params['currency_id']=abs((int)$valarr[4]);
		
		$user=$_ui->GetItemById($params['user_id']);
		$curr=$_ci->GetItemById($params['currency_id']);
		
		
		$exist=$_pfs->GetSales($valarr[2], $valarr[1], $valarr[0], $valarr[3], $valarr[4], $user['department_id'], $result['org_id'], 
		$au->user_rights->CheckAccess('w',785), 
		$au->user_rights->CheckAccess('w',786),  
		$au->user_rights->CheckAccess('w',787),  
		$au->user_rights->CheckAccess('w',788),
		NULL, 
		$au->user_rights->CheckAccess('w',813)
		 );
		
		
		
		$params['plan_or_fact']=abs((int)$valarr[3]);
		$params['month']=abs((int)$valarr[2]);
		$params['year']=abs((int)$valarr[1]);
		
		
		
		$valarr[5]=str_replace(',','.',$valarr[5]);
		$params['value']=abs((float)$valarr[5]);
		
		$params['pdate']=time();
		$params['posted_user_id']=$result['id'];
		$params['org_id']=$result['org_id'];
		
		$do_it=true;
		if($exist['can_modify']){
			if($exist['data']===false){
				//создаем
				//получить айди объекта системы...
				/*if($params['plan_or_fact']==0){
					 $object_id=785;
					 $ob_name='план';
				}else{
					 $object_id=787;
					 $ob_name='факт';
				}
				
								
				$_pfs->item->Add($params);
				
				
				$desciption='создал '.$ob_name.' продаж';
				$value=SecStr('создан '.$ob_name.' продаж дл€ сотрудника '.$user['name_s'].' ('.$user['login'].') на '.$_pfs->GetMonthByNumber($params['month']).' '.$params['year'].', значение: '.$params['value'].' '.$curr['signature']);
				
				*/
					
			}else{
				//правим
				//получить айди объекта системы...
				if($params['plan_or_fact']==0){
					 $object_id=786;
					 $ob_name='план';
				}else{
					if($au->user_rights->CheckAccess('w',813)) $object_id=813;
					else $object_id=788;
					$ob_name='факт';
					if($exist['restricted_by_period']) $do_it=false;
				}
				
				if($do_it) $_pfs->item->Del($exist['data']['id']);
				
				$desciption='удалил '.$ob_name.' продаж';
				$value=SecStr('удален '.$ob_name.' продаж дл€ сотрудника '.$user['name_s'].' ('.$user['login'].') на '.$_pfs->GetMonthByNumber($params['month']).' '.$params['year'].', старое значение: '.$exist['data']['value'].' '.$curr['signature'].'');
				
			}
			
			if($do_it) $log->PutEntry($result['id'], $desciption, $params['user_id'], $object_id, NULL, $value, $params['user_id']);
			
		}
			
	}

	
}


elseif(isset($_POST['action'])&&($_POST['action']=="save_fact_opo")){
	//сохраним факт. продажу по ќѕќ
	if($au->user_rights->CheckAccess('w',787)){
			
		$_pff=new PlanFactFactItem;
		$_pfs=new PlanFactSales;
		
		$_notes=new PlanFactFactNotesItem;
		
		$params=array();
		$params['month']=abs((int)$_POST['month']);
		$params['year']=abs((int)$_POST['year']);
		$params['user_id']=abs((int)$_POST['user_id']);
		 
		
		//$params['supplier_name']=SecStr(iconv('utf-8', 'windows-1251//TRANSLIT', $_POST['supplier_name']));
		
		
		$params['supplier_id']=abs((int)$_POST['supplier_id']);
		
		$params['supplier_is_new']=abs((int)$_POST['supplier_is_new']);
		$params['eq_name']=SecStr(iconv('utf-8', 'windows-1251//TRANSLIT',$_POST['eq_name']));
	//	$params['eq_id']=abs((int)$_POST['eq_id']);
	
		$params['eq_is_new']=abs((int)$_POST['eq_is_new']);
		$params['contract_sum']=abs((float)str_replace(',','.', $_POST['contract_sum']));
		$params['contract_currency_id']=abs((int)$_POST['contract_currency_id']);
		$params['contract_no']=SecStr(iconv('utf-8', 'windows-1251//TRANSLIT', $_POST['contract_no']));
		
		$params['producer_id']=abs((int)$_POST['producer_id']);
		
		$params['city_id']=abs((int)$_POST['city_id']);
		$params['price_kind_id']=abs((int)$_POST['price_kind_id']);
		
		$params['status_id']=2;
		$params['posted_user_id']=$result['id'];
		$params['pdate']=time();
		
		
		$params['is_confirmed']=1;
		$params['user_confirm_id']=$result['id'];
		$params['confirm_pdate']=time();
		$params['org_id']=$result['org_id'];
		
		
		
		
		$id=$_pff->Add($params);
		
		
		$value='';
		
		$_city=new SupplierCityItem;
		$city=$_city->GetFullCity($params['city_id']);
		$_ui=new UserItem; $_ci=new PlCurrItem; $_pki=new PriceKindItem; $_pl=new PlPosItem;
		$_prod=new  PlProdItem;
		$prod=$_prod->GetItemById($params['producer_id']);
		//$pl=$_pl->GetItemById($params['eq_id']);
		
		$user=$_ui->GetItemById($params['user_id']);
		$curr=$_ci->GetItemById($params['contract_currency_id']);
		$pki=$_pki->GetItemById($params['price_kind_id']);
		
		$value.='‘акт продаж за '.$_pfs->GetMonthByNumber($params['month']).' '.$params['year'].': договор на сумму '.$params['contract_sum'].' '.$curr['signature'].'; є договора:'.$params['contract_no'].', вид цен '.$pki['name'];//.'; название клиента: '.$params['supplier_name'];
		
		$_si=new SupplierItem; $_opf=new OpfItem;
		$si=$_si->GetItemById($params['supplier_id']); $opf=$_opf->GetItemById($si['opf_id']);
		$value.='; название клиента: '.$opf['name'].' '.$si['full_name'];
		
		
		if($params['supplier_is_new']==1) $value.=', новый клиент';
		
		$value.='; название станка: '.$params['eq_name'];
		if($params['eq_is_new']==1) $value.=', новое оборудование';
		else $value.=', б/у';
		
		$value.='; производитель: '.$prod['name'];
		
		$value.='; город '.$city['fullname'];
		
		
		$value=SecStr($value);
		
		$log->PutEntry($result['id'], 'создал факт продаж', $params['user_id'], 787, NULL, $value, $id);
		
		 
		if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['notes'])))>0){
		  $_notes->Add(array(
					  'note'=>SecStr(iconv("utf-8","windows-1251",$_POST['notes'])),
					  'pdate'=>time(),
					  'user_id'=>$id,
					  'posted_user_id'=>$result['id']
				  ));
		  
		  $log->PutEntry($result['id'],'добавил примечани€ к факту продаж', NULL,787, NULL,SecStr(iconv("utf-8","windows-1251",$_POST['notes'])),$id);
		}
		
	}
	//$object_id=787;
}
elseif(isset($_POST['action'])&&($_POST['action']=="save_exist_fact_opo")){
	//сохраним факт. продажу по ќѕќ
	if($au->user_rights->CheckAccess('w',788)||$au->user_rights->CheckAccess('w',813)){
			
		$_pff=new PlanFactFactItem;
		$_pfs=new PlanFactSales;
		
		$_notes=new PlanFactFactNotesItem;
		
		$id=abs((int)$_POST['id']);
		
		$params=array();
		if($au->user_rights->CheckAccess('w',825)){
			$params['month']=abs((int)$_POST['month']);
			$params['year']=abs((int)$_POST['year']);
		}
		/*$params['user_id']=abs((int)$_POST['user_id']);
		 */
		
		//$params['supplier_name']=SecStr(iconv('utf-8', 'windows-1251//TRANSLIT', $_POST['supplier_name']));
		$params['supplier_id']=abs((int)$_POST['supplier_id']);
		
		$params['supplier_is_new']=abs((int)$_POST['supplier_is_new']);
		$params['eq_name']=SecStr(iconv('utf-8', 'windows-1251//TRANSLIT',$_POST['eq_name']));
 
	
		$params['eq_is_new']=abs((int)$_POST['eq_is_new']);
		$params['contract_sum']=abs((float)str_replace(',','.', $_POST['contract_sum']));
		//$params['contract_currency_id']=abs((int)$_POST['contract_currency_id']);
		$params['contract_no']=SecStr(iconv('utf-8', 'windows-1251//TRANSLIT', $_POST['contract_no']));
		
		$params['producer_id']=abs((int)$_POST['producer_id']);
		
		$params['city_id']=abs((int)$_POST['city_id']);
		$params['price_kind_id']=abs((int)$_POST['price_kind_id']);
		
		$params['status_id']=2;
		$params['posted_user_id']=$result['id'];
		$params['pdate']=time();
		
		
		$params['is_confirmed']=1;
		$params['user_confirm_id']=$result['id'];
		$params['confirm_pdate']=time();
		 
		$params['user_id']=abs((int)$_POST['user_id']);
		
		$_pff->Edit($id, $params);
		
		
		$value='';
		
		$_city=new SupplierCityItem;
		$city=$_city->GetFullCity($params['city_id']);
		$_ui=new UserItem; $_ci=new PlCurrItem; $_pki=new PriceKindItem; $_pl=new PlPosItem;
		$_prod=new  PlProdItem;
		$prod=$_prod->GetItemById($params['producer_id']);
		//$pl=$_pl->GetItemById($params['eq_id']);
		
		$user=$_ui->GetItemById($params['user_id']);
		$curr=$_ci->GetItemById($params['contract_currency_id']);
		$pki=$_pki->GetItemById($params['price_kind_id']);
		
		$value.='‘акт продаж за '.$_pfs->GetMonthByNumber($params['month']).' '.$params['year'].': договор на сумму '.$params['contract_sum'].' '.$curr['signature'].'; є договора:'.$params['contract_no'].', вид цен '.$pki['name'];//.'; название клиента: '.$params['supplier_name'];
		
		$_si=new SupplierItem; $_opf=new OpfItem;
		$si=$_si->GetItemById($params['supplier_id']); $opf=$_opf->GetItemById($si['opf_id']);
		$value.='; название клиента: '.$opf['name'].' '.$si['full_name'];
		
		if($params['supplier_is_new']==1) $value.=', новый клиент';
		
		$value.='; название станка: '.$params['eq_name'];
		if($params['eq_is_new']==1) $value.=', новое оборудование';
		else $value.=', б/у';
		
		$value.='; производитель: '.$prod['name'];
		
		$value.='; город '.$city['fullname'];
		
		
		$value=SecStr($value);
		
		$log->PutEntry($result['id'], 'редактировал факт продаж', $params['user_id'], 788, NULL, $value, $id);
		
		 
		if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['notes'])))>0){
		  $_notes->Add(array(
					  'note'=>SecStr(iconv("utf-8","windows-1251",$_POST['notes'])),
					  'pdate'=>time(),
					  'user_id'=>$id,
					  'posted_user_id'=>$result['id']
				  ));
		  
		  $log->PutEntry($result['id'],'добавил примечани€ к факту продаж', NULL,788, NULL,SecStr(iconv("utf-8","windows-1251",$_POST['notes'])),$id);
		}
		
	}
	//$object_id=787;
}

elseif(isset($_POST['action'])&&($_POST['action']=="load_eq_id")){
	//загрузка станков по выбранному отделу...
	$department_id=abs((int)$_POST['department_id']);
	$_pg=new PlPosGroup;
	
	$arr=$_pg->GetItemsArrByDepartmentId($department_id);
	
	$ret='<option value="0" selected="selected">-выберите-</option>';			
	foreach($arr as $k=>$v){
		$ret.='<option value="'.$v['id'].'">'.$v['name'].'</option>';
	}
	
}

elseif(isset($_POST['action'])&&($_POST['action']=="load_producer_id")){
	//загрузка станков по выбранному отделу...
	$department_id=abs((int)$_POST['department_id']);
	$_pg=new PlProdGroup;
	
	$arr=$_pg->GetItemsArr(); //>GetItemsArrByDepartmentId($department_id);
	
	$ret='<option value="0" selected="selected">-выберите-</option>';			
	foreach($arr as $k=>$v){
		$ret.='<option value="'.$v['id'].'">'.$v['name'].'</option>';
	}
	
}

elseif(isset($_POST['action'])&&($_POST['action']=="get_mult")){
	//загрузка станков по выбранному отделу...
	$from_id=abs((int)$_POST['from_id']);
	$to_id=abs((int)$_POST['to_id']);
	
	
	$_pg=new PlProdGroup;
	/*
	$arr=$_pg->GetItemsArr(); //>GetItemsArrByDepartmentId($department_id);
	
	$ret='<option value="0" selected="selected">-выберите-</option>';			
	foreach($arr as $k=>$v){
		$ret.='<option value="'.$v['id'].'">'.$v['name'].'</option>';
	}*/
	
	$_solver=new CurrencySolver;
	
	$rates=$_solver->GetActual();
	
	$ret=CurrencySolver::Convert(1,$rates,$from_id, $to_id);
	
}




//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>