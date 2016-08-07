<?
session_start();
header('Content-type: text/html; charset=windows-1251');

require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/discr_table_objects.php');
require_once('../classes/actionlog.php');
 

require_once('../classes/user_s_item.php');


require_once('../classes/posgroupgroup.php');

require_once('../classes/supcontract_item.php');
require_once('../classes/supcontract_group.php');

require_once('../classes/schednotesgroup.php');
require_once('../classes/schednotesitem.php');


require_once('../classes/user_s_group.php');

require_once('../classes/suppliersgroup.php');
require_once('../classes/suppliercontactgroup.php');

require_once('../classes/suppliercontactdatagroup.php');
require_once('../classes/usercontactdatagroup.php');
require_once('../classes/supplier_city_group.php');
require_once('../classes/supplier_city_item.php');

require_once('../classes/supplier_district_group.php');
require_once('../classes/supplier_cities_group.php');
require_once('../classes/suppliercontactgroup.php');


require_once('../classes/supplier_to_user.php');



require_once('../classes/sched.class.php');
require_once('../classes/sched_history_fileitem.php');
require_once('../classes/sched_history_item.php');
require_once('../classes/sched_history_group.php');

require_once('../classes/filecontents.php');
 
require_once('../classes/quick_suppliers_group.php');
require_once('../classes/sched_fileitem.php');

require_once('../classes/supplier_tool.class.php');

require_once('../classes/supplier_tool_view.class.php');


require_once('../classes/pl_prodgroup.php');
require_once('../classes/posgroupgroup.php');
require_once('../classes/pl_posgroup.php');
require_once('../classes/pl_positem.php');
 

$au=new AuthUser();
$result=$au->Auth(false,false);
$log=new ActionLog;

if($result===NULL){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();		
}
 

$ret='';


//настройка реестра
if(isset($_POST['action'])&&(($_POST['action']=="mode_reestr"))){
	$_views=new SupplierTool_ViewGroup;
	$_view=new  SupplierTool_ViewItem;
	
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
	$_views=new SupplierTool_ViewGroup;
	 
	 
	
	$_views->Clear($result['id']);
	 
}
else
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
	
	

}elseif(isset($_POST['action'])&&($_POST['action']=="toggle_eqs_by_category")){
	$_pl=new PlPosGroup;
	
	$group_id=abs((int)$_POST['group_id2']);
	$current_id=abs((int)$_POST['current_id']);
	
	$ret=$_pl->GetItemsByIdOpt($group_id, $current_id, 'name', true,'-выберите-'/*,'and p.id not in(select distinct pl_position_id from supplier_tools where supplier_id='.abs((int)$_POST['supplier_id']).')'*/);

}elseif(isset($_POST['action'])&&($_POST['action']=="toggle_eqs_by_producer")){
	$_pl=new PlPosGroup;
	
	$group_id=abs((int)$_POST['group_id']);
	$current_id=abs((int)$_POST['current_id']);
	
	$ret=$_pl->GetItemsByProducerIdOpt($group_id, $current_id, 'name', true,'-выберите-'/*,'and p.id not in(select distinct pl_position_id from supplier_tools where supplier_id='.abs((int)$_POST['supplier_id']).')'*/);

}elseif(isset($_POST['action'])&&($_POST['action']=="toggle_eqs_by_group")){
	$_pl=new PlPosGroup;
	
	$group_id=abs((int)$_POST['group_id']);
	$current_id=abs((int)$_POST['current_id']);
	
	$ret=$_pl->GetItemsByGroupIdIdOpt($group_id, $current_id, 'name', true,'-выберите-'/*'and p.id not in(select distinct pl_position_id from supplier_tools where supplier_id='.abs((int)$_POST['supplier_id']).')'*/);

}elseif(isset($_POST['action'])&&($_POST['action']=="toggle_eqs_init")){
	$current_id=abs((int)$_POST['current_id']);
	//оборудование
		$sql_eq='select pl.id as pl_id, pl.discount_id, pl.discount_value, pl.discount_rub_or_percent, pl.position_id, 
					 
					p.* 
					
					
				from pl_position as pl
					inner join catalog_position as p on p.id=pl.position_id
					 
				where parent_id=0 and p.is_active=1 
				/*and p.id not in(select distinct pl_position_id from supplier_tools where supplier_id='.abs((int)$_POST['supplier_id']).')*/
	 	';
		
		///echo $sql_eq;
		 
		$set=new mysqlSet($sql_eq);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
	 	$ids=array();
		$ids[]=array('id'=>0, 'name'=>'-выберите-');
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$ids[]=$f;
		}
		
		foreach($ids as $k=>$v){
			$ret.='<option value="'.$v['id'].'"	';
			if($v['id']==$current_id) $ret.=' selected';
			$ret.='>'.$v['name'].'</option>';
		}

}elseif(isset($_POST['action'])&&($_POST['action']=="toggle_group_init")){
 

		
		
		//тов группы
		$as=new mysqlSet('select * from catalog_group where parent_group_id=0 order by id asc, name asc');
		$rs=$as->GetResult();
		$rc=$as->GetResultNumRows();
		$acts=array();
		//$acts[]=array('name'=>'');
		$gr_ids=array(); $gr_names=array();
		
		$acts[]=array('id'=>0, 'name'=>'-выберите-');
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			$acts[]=$f;
			
			$gr_ids[]=$f['id'];
			$gr_names[]=$f['name'];
		}
	 
	 foreach($acts as $k=>$v){
			$ret.='<option value="'.$v['id'].'"	>'.$v['name'].'</option>';
		}
}



//добавка
elseif(isset($_POST['action'])&&($_POST['action']=="add")){
	
	if(!$au->user_rights->CheckAccess('w',87)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$_ti=new SupplierTool_Item;
 
 
	
	$params=array();
	$params['created_id']=$result['id'];
	$params['pdate']=time();
	
	$params['year']=SecStr(iconv('utf-8','windows-1251',$_POST['year']));
	$params['notes']=SecStr(iconv('utf-8','windows-1251',$_POST['notes']));
	$params['name']=SecStr(iconv('utf-8','windows-1251',$_POST['name']));
	$params['pl_position_id']=abs((int)$_POST['pl_position_id']);
	$params['not_in_pl']=abs((int)$_POST['not_in_pl']);
	$params['supplier_id']=abs((int)$_POST['supplier_id']);
	
	$code=$_ti->Add($params);
	
	 
	
	$_pl=new PlPosItem;
	$pl=$_pl->GetItemById($params['pl_position_id']);
	
	
	
	$log->PutEntry($result['id'],'добавил станок в карту контрагента',NULL, 87,NULL,SecStr($pl['name'].' '.$params['name']),(int) $params['supplier_id']);
	
}



//добавка
elseif(isset($_POST['action'])&&($_POST['action']=="edit")){
	
	if(!$au->user_rights->CheckAccess('w',87)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$_ti=new SupplierTool_Item;
 
 $id=abs((int)$_POST['id']);
	
	$params=array();
	 
	
	$params['year']=SecStr(iconv('utf-8','windows-1251',$_POST['year']));
	$params['notes']=SecStr(iconv('utf-8','windows-1251',$_POST['notes']));
	$params['name']=SecStr(iconv('utf-8','windows-1251',$_POST['name']));
	$params['pl_position_id']=abs((int)$_POST['pl_position_id']);
	$params['not_in_pl']=abs((int)$_POST['not_in_pl']);
	$params['supplier_id']=abs((int)$_POST['supplier_id']);
	
	 $_ti->Edit($id,$params);
	
	 
	
	$_pl=new PlPosItem;
	$pl=$_pl->GetItemById($params['pl_position_id']);
	
	
	
	$log->PutEntry($result['id'],'редактировал станок в карте контрагента',NULL, 87,NULL,SecStr($pl['name'].' '.$params['name']),(int) $params['supplier_id']);
	
}



else
 
if(isset($_POST['action'])&&($_POST['action']=="check_unconfirm_price")){
	
		$id=abs((int)$_POST['id']);
		
	
		
		  
		$_dem=new SupplierTool_AbstractItem;
		$dem=$_dem->Getitembyid($id);
		
		$_res=new SupplierTool_Resolver();
		
		
		$_ki=$_res->instance;
		
		
		
		if(!$_ki->DocCanUnconfirmPrice($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
}elseif(isset($_POST['action'])&&($_POST['action']=="check_confirm_price")){
		$id=abs((int)$_POST['id']);
		
	
		
		  
		$_dem=new SupplierTool_AbstractItem;
		$dem=$_dem->Getitembyid($id);
		
		$_res=new SupplierTool_Resolver($dem['kind_id']);
		
		
		$_ki=$_res->instance;
		
		
		
		if(!$_ki->DocCanConfirmPrice($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
	
}


//udalenie-annulirovabie
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_annul")){
	$id=abs((int)$_POST['id']);
	
	if(isset($_POST['from_card'])&&($_POST['from_card']==1)) $from_card=1;
	else $from_card=0;
	
	//$note=SecStr(iconv("utf-8","windows-1251",$_POST['note']));
	
	$_ki=new SupplierTool_AbstractItem;
	
		$trust=$_ki->getitembyid($id);
		
	$_res=new SupplierTool_Resolver();
	$_res->SetAuthResult($result['id']);
	$_ti=$_res->instance;	
	
	$_si=new UserSItem;
	

	
	$log=new ActionLog;
	$_stat=new DocStatusItem;
	
		$_pl=new PlPosItem;
	$pl=$_pl->GetItemById($trust['pl_position_id']);
	
	if($_ti->DocCanAnnul($id, $rss123)){
		//удаление	
		 
			if($au->user_rights->CheckAccess('w',1004)){
				//$_ti->Edit($id,array('status_id'=>3),false,$result);
				$_ti->Del($id);
				
				
				 
				
				//$stat=$_stat->GetItemById(3);
				$log->PutEntry($result['id'],'удаление станка из карты контрагента',NULL,1004,NULL,SecStr($pl['name'].' '.$trust['name']),$trust['supplier_id']);	
			 
			}
		 
	}elseif($trust['status_id']==3){
		//разудаление
		if($au->user_rights->CheckAccess('w',931)){
			$_ti->Edit($id,array('status_id'=>18, 'restore_pdate'=>time()),false,$result);
			
			$stat=$_stat->GetItemById(18);
			$log->PutEntry($result['id'],'восстановление тендера',NULL,931,NULL,'тендер № '.$trust['code'].': установлен статус '.$stat['name'],$id);
			
			//внести примечание
			/*$_ni=new BillNotesItem;
			$_ni->Add(array(
				'user_id'=>$id,
				'posted_user_id'=>$result['id'],
				'note'=>'Автоматическое примечание: документ был восстановлен пользователем '.SecStr($result['name_s']).' ('.$result['login'].')',
				'is_auto'=>1,
				'pdate'=>time()
					));	*/	
			
		}
		
	}
	
	if($from_card==0){
	  $shorter=abs((int)$_POST['shorter']);
	 
	 
	  $template='suppliers/supplier_tools.html';
	 
	 
	 
	  
	  $acg= $_res->group_instance; //new Tender_Group;
	  
	  $dec=new  DBDecorator;
	  
	  $dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	  
	  
	
	 	 $ret=$acg->ShowPos(
		

			 $template,  //0
			 $dec, //1
			  $au->user_rights->CheckAccess('w',87), //2
			  0, //3
			  10000, //4
			  false, //5
			  true,  //6
			   $au->user_rights->CheckAccess('w',1004), //7
			  $au->user_rights->CheckAccess('w',87),  //8
			  $au->user_rights->CheckAccess('w',1004),  //9
			  $au->user_rights->CheckAccess('w',87)  //10
			  
			
			 );
	 
	  
	 
	}else{
		$editing_user=$_ti->getitembyid($id);
		$sm=new SmartyAj;
		
		
		
		//блок аннулирования
		
		$editing_user['can_annul']=$_res->instance->DocCanAnnul($editing_user['id'],$reason,$editing_user)&&($au->user_rights->CheckAccess('w',972)||($result['id']==$editing_user['created_id']) );
		if(!($au->user_rights->CheckAccess('w',972)||($result['id']==$editing_user['created_id']))) $reason='недостаточно прав для данной операции';
		$editing_user['can_annul_reason']=$reason;
		
		//$editing_user['binded_to_annul']=$_ti->GetBindedDocumentsToAnnul($editing_user['id']);
		
		
		$editing_user['can_restore']=$_ti->DocCanRestore($editing_user['id'],$reason)&&$au->user_rights->CheckAccess('w',931);
			if(!$au->user_rights->CheckAccess('w',931)) $reason='недостаточно прав для данной операции';
		
		$stat=$_stat->Getitembyid($editing_user['status_id']);
		$editing_user['status_name']=$stat['name'];
		
		$sm->assign('bill',$editing_user);
		$ret=$sm->fetch('tender/toggle_annul_card.html');		
	}
		
}

 

//utv- razutv
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_confirm_price")){
	$id=abs((int)$_POST['id']);
	 
	
		$_ki=new SupplierTool_AbstractItem;
	
		$trust=$_ki->getitembyid($id);
		
	$_res=new SupplierTool_Resolver();
	$_res->SetAuthResult($result);
	$_ti=$_res->instance;	
	
	$_si=new UserSItem;
	
	 
	 
	if($trust['confirm_pdate']==0) $trust['confirm_pdate']='-';
	else $trust['confirm_pdate']=date("d.m.Y H:i:s",$trust['confirm_pdate']);
	
	
	$si=$_si->getitembyid($trust['user_confirm_id']);
	$trust['confirmed_price_name']=$si['name_s'];
	$trust['confirmed_price_login']=$si['login'];
	
	$bill_id=$id;
	
		$_pl=new PlPosItem;
	$pl=$_pl->GetItemById($trust['pl_position_id']);
	 
	
	if($trust['is_confirmed']==1){
		//есть права: либо сам утв.+есть права, либо есть искл. права:
		
		 
			if($au->user_rights->CheckAccess('w',1004)&&$_ti->DocCanUnconfirmPrice($id, $rss)){
					
					$_ti->Edit($id,array('is_confirmed'=>0, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()), true,$result);
					
					$log->PutEntry($result['id'],'снял утверждение станка в карте контрагента',NULL,1004, NULL, SecStr($pl['name'].' '.$trust['name']),$trust['supplier_id']);
					 
				 		
				 
			} 
		 
		
	}else{
		//есть права
		 
			if($au->user_rights->CheckAccess('w',87)&&$_ti->DocCanConfirmPrice($id, $rss)){
				 
					$_ti->Edit($id,array('is_confirmed'=>1, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,$result);
					
					$log->PutEntry($result['id'],'утвердил заполнения станка в карте контрагента',NULL,87, NULL, SecStr($pl['name'].' '.$trust['name']),$trust['supplier_id']);
					
					
					
					
				 
			} 
		 
	}
	
	
	
	  $template='suppliers/supplier_tools.html';
	 
	 
	 
	  
	  $acg= $_res->group_instance; //new Tender_Group;
	  
	  $dec=new  DBDecorator;
	  
	  $dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	  
	  
	
	 	 $ret=$acg->ShowPos(
		

			 $template,  //0
			 $dec, //1
			  $au->user_rights->CheckAccess('w',87), //2
			  0, //3
			  10000, //4
			  false, //5
			  true,  //6
			   $au->user_rights->CheckAccess('w',1004), //7
			  $au->user_rights->CheckAccess('w',87),  //8
			  $au->user_rights->CheckAccess('w',1004),  //9
			  $au->user_rights->CheckAccess('w',87)  //10
			  
			
			 );
	
		
}


 


//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>