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

require_once('../classes/doc_outs.class.php');

require_once('../classes/tender.class.php');
require_once('../classes/lead_history_fileitem.php');
require_once('../classes/lead_history_item.php');
require_once('../classes/lead_history_group.php');

require_once('../classes/filecontents.php');
 
require_once('../classes/quick_suppliers_group.php'); 

require_once('../classes/supplieritem.php'); 
require_once('../classes/supplier_responsible_user_group.php');

require_once('../classes/lead_view.class.php');
 

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
	
}elseif(isset($_POST['action'])&&(($_POST['action']=="find_suppliers")||($_POST['action']=="find_suppliers_ship")||($_POST['action']=="find_many_suppliers")||($_POST['action']=="find_many_suppliers_15"))){
	
	
	
	$_pg=new Quick_SupplierGroup;
	
	$dec=new DBDecorator;
	
	$dec->AddEntry(new SqlEntry('p.org_id',$result['org_id'], SqlEntry::E));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['opf'])))>0){
			$names=explode(';', trim(iconv("utf-8","windows-1251",$_POST['opf'])));
			foreach($names as $k=>$v) $names[$k]='name like "%'.SecStr($v).'%"';
			
			$dec->AddEntry(new SqlEntry('p.opf_id','select id from opf where '.implode(' or ', $names), SqlEntry::IN_SQL));
	}
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['code'])))>0){
	 
			$names=explode(';', trim(iconv("utf-8","windows-1251",$_POST['code'])));
			foreach($names as $k=>$v) $names[$k]=SecStr($v);
			
			$dec->AddEntry(new SqlEntry('p.code', NULL, SqlEntry::LIKE_SET, NULL,$names));	
	}
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['full_name'])))>0){
		 
		$names=explode(';', trim(iconv("utf-8","windows-1251",$_POST['full_name'])));
			foreach($names as $k=>$v) $names[$k]=SecStr($v);
			
			$dec->AddEntry(new SqlEntry('p.full_name', NULL, SqlEntry::LIKE_SET, NULL,$names));
	}
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['inn'])))>0){
		 
		
		$names=explode(';', trim(iconv("utf-8","windows-1251",$_POST['inn'])));
			foreach($names as $k=>$v) $names[$k]=SecStr($v);
			
			$dec->AddEntry(new SqlEntry('p.inn', NULL, SqlEntry::LIKE_SET, NULL,$names));
	}
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['kpp'])))>0) {
 
		$names=explode(';', trim(iconv("utf-8","windows-1251",$_POST['kpp'])));
			foreach($names as $k=>$v) $names[$k]=SecStr($v);
			
			$dec->AddEntry(new SqlEntry('p.kpp', NULL, SqlEntry::LIKE_SET, NULL,$names));
	}
	 
 
 
 
	 if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['city'])))>0) {
	 
		$names=explode(';', trim(iconv("utf-8","windows-1251",$_POST['city'])));
			foreach($names as $k=>$v) $names[$k]='name like "%'.SecStr($v).'%"';
			
			 
			$dec->AddEntry(new SqlEntry('p.id','select distinct supplier_id from supplier_sprav_city where city_id in( select id from sprav_city where '.implode(' or ',$names).')', SqlEntry::IN_SQL));
		
	}
	
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['contact'])))>0) {
	 
		$names=explode(';', trim(iconv("utf-8","windows-1251",$_POST['contact'])));
			foreach($names as $k=>$v) $names[$k]='name like "%'.SecStr($v).'%"';
			
		
			$dec->AddEntry(new SqlEntry('p.id','select distinct supplier_id from supplier_contact where  '.implode(' or ',$names).'', SqlEntry::IN_SQL));
		
	}
	
		if(isset($_POST['already_loaded'])&&is_array($_POST['already_loaded'])) $dec->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::NOT_IN_VALUES, NULL,$_POST['already_loaded']));	
	
	
	//ограничения по к-ту
	$limited_supplier=NULL;
	//ограничений нет
 		
	if($_POST['action']=="find_suppliers_ship") $ret=$_pg->GetItemsForBill('lead/ship_suppliers_list.html',  $dec,true,$all7,$result, 0, (strlen(SecStr(iconv("utf-8","windows-1251",$_POST['contact'])))>0)); 
	elseif($_POST['action']=="find_many_suppliers") $ret=$_pg->GetItemsForBill('lead/suppliers_many_list.html',  $dec,true,$all7,$result, 0, (strlen(SecStr(iconv("utf-8","windows-1251",$_POST['contact'])))>0));  
	elseif($_POST['action']=="find_many_suppliers_15") $ret=$_pg->GetItemsForBill('lead/suppliers_15_list.html',  $dec,true,$all7,$result, 0, (strlen(SecStr(iconv("utf-8","windows-1251",$_POST['contact'])))>0));  
	else $ret=$_pg->GetItemsForBill('lead/suppliers_list.html',  $dec,true,$all7,$result, 0, (strlen(SecStr(iconv("utf-8","windows-1251",$_POST['contact'])))>0)); 
	
	
	 
	
	

	
} 
elseif(isset($_POST['action'])&&(($_POST['action']=="retrieve_contacts")||($_POST['action']=="retrieve_only_contacts"))){
	$_sc=new lead_SupplierContactGroup;
	
	
	$supplier_id=abs((int)$_POST['supplier_id']);
	$current_id=abs((int)$_POST['current_id']);
	$current_k_id=abs((int)$_POST['current_k_id']);
	
	
	
	
	$alls=$_sc->GetItemsByIdArr($supplier_id,$current_id, $current_k_id); 
	$sm=new SmartyAj;
	
	
	$sm->assign('supplier_id', $supplier_id);
	$sm->assign('items', $alls);
	
	if($_POST['action']=="retrieve_only_contacts") $ret=$sm->fetch('lead/suppliers_only_contacts.html');
	else $ret=$sm->fetch('lead/suppliers_contacts.html');

}
  

elseif(isset($_POST['action'])&&($_POST['action']=="check_confirm")){
		$id=abs((int)$_POST['id']);
		
	    
		$_dem=new lead_AbstractItem;
		$dem=$_dem->Getitembyid($id);
		
		$_res=new lead_Resolver($dem['kind_id']);
		
		
		$_ki=$_res->instance;
		
		
		if(!$_ki->DocCanConfirmShip($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
	



	
}elseif(isset($_POST['action'])&&($_POST['action']=="check_unconfirm")){
	//проверить, есть ли заявки с таким номером для такого уч.
	
		$id=abs((int)$_POST['id']);
		
	
		
	  
		$_dem=new lead_AbstractItem;
		$dem=$_dem->Getitembyid($id);
		
		$_res=new lead_Resolver($dem['kind_id']);
		
		
		$_ki=$_res->instance;
		
		
		
		if(!$_ki->DocCanUnconfirmShip($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
}elseif(isset($_POST['action'])&&($_POST['action']=="check_confirm")){
		$id=abs((int)$_POST['id']);
		
	
		
	  
		$_dem=new lead_AbstractItem;
		$dem=$_dem->Getitembyid($id);
		
		$_res=new lead_Resolver($dem['kind_id']);
		
		
		$_ki=$_res->instance;
		
		
		if(!$_ki->DocCanConfirmShip($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
	



 

}elseif(isset($_POST['action'])&&($_POST['action']=="check_unconfirm_fulfil")){
	//проверить, есть ли заявки с таким номером для такого уч.
	
		$id=abs((int)$_POST['id']);
		
		$_ki=new lead_AbstractItem;
		$f=$_ki->GetItemById($id);
		
	  
		//$_ki=new Sched_TaskItem;
		$_res=new lead_Resolver($f['kind_id']);
		 
		
		
		if(!$_res->instance->DocCanUnconfirmFulfil($id,$rss55,$f)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
}elseif(isset($_POST['action'])&&($_POST['action']=="check_confirm_fulfil")){
		$id=abs((int)$_POST['id']);
		
		$_ki=new lead_AbstractItem;
		$f=$_ki->GetItemById($id);
		
	  
		//$_ki=new Sched_TaskItem;
		$_res=new lead_Resolver($f['kind_id']);
		 
		
		
		if(!$_res->instance->DocCanConfirmFulfil($id,$rss55,$f)) $ret=$rss55;
		else $ret=0;
		
		//если ноль - то все хорошо
	

}


elseif(isset($_POST['action'])&&($_POST['action']=="check_unconfirm_price")){
	
		$id=abs((int)$_POST['id']);
		
	
		
		  
		$_dem=new lead_AbstractItem;
		$dem=$_dem->Getitembyid($id);
		
		$_res=new lead_Resolver($dem['kind_id']);
		
		
		$_ki=$_res->instance;
		
		
		
		if(!$_ki->DocCanUnconfirmPrice($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
}elseif(isset($_POST['action'])&&($_POST['action']=="check_confirm_price")){
		$id=abs((int)$_POST['id']);
		
	
		
		  
		$_dem=new lead_AbstractItem;
		$dem=$_dem->Getitembyid($id);
		
		$_res=new lead_Resolver($dem['kind_id']);
		
		
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
	
	$note=SecStr(iconv("utf-8","windows-1251",$_POST['note']));
	
	$_ki=new lead_AbstractItem;
	
		$trust=$_ki->getitembyid($id);
		
	$_res=new lead_Resolver($trust['kind_id']);
	$_ti=$_res->instance;	
	
	$_si=new UserSItem;
	

	
	$log=new ActionLog;
	$_stat=new DocStatusItem;
	
	
	
	if(($trust['status_id']==18)&&($trust['is_confirmed']==0)){
		//удаление	
		if($au->user_rights->CheckAccess('w',905)){
			$_ti->Edit($id,array('status_id'=>3),false,$result);
			
			$stat=$_stat->GetItemById(3);
			$log->PutEntry($result['id'],'аннулирование лида',NULL,950,NULL,'лид '.$trust['code'].': установлен статус '.$stat['name'],$id);	
			
			 //автоматически сбросить  в ноль вероятность
			 $_ti->Edit($id,array('probability'=>0));
				 
			$log->PutEntry($_result['id'],'автоматическая смена вероятности заключения контракта лида',NULL,950,NULL,'лид '.$trust['code'].', установлено значение 0% '.' при установлении статуса '.$stat['name'],$trust['id']);
			 
			 	 
			//внести примечание
			$_ni=new Lead_HistoryItem;
			 
			$_ni->Add(array(
				'sched_id'=>$id,
				'user_id'=>0,
				'txt'=>'Автоматический комментарий: документ был аннулирован пользователем '.SecStr($result['name_s']).' , причина: '.$note,
				 
				'pdate'=>time()
					));	 
		}
	}elseif($trust['status_id']==3){
		//разудаление
		if($au->user_rights->CheckAccess('w',950)){
			$_ti->Edit($id,array('status_id'=>18, 'restore_pdate'=>time()),false,$result);
			
			$stat=$_stat->GetItemById(18);
			$log->PutEntry($result['id'],'восстановление лида',NULL,950,NULL,'лид № '.$trust['code'].': установлен статус '.$stat['name'],$id);
			
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
	 
	 
			$template='lead/table.html';
	 
	 
	  
	  $acg=new lead_Group;
	   $acg->setauthresult($result);
	  
	  $dec=new  DBDecorator;
	  
	  $dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
		
		$prefix='_leads';
	
	  $ret=$acg->ShowPos(
		

			'lead/table.html',  //0
			 $dec, //1
			  $au->user_rights->CheckAccess('w',949), //2
			  $au->user_rights->CheckAccess('w',950), //3
			  0, //4
			  10000, //5
			  false, //6
			  true,  //7
			  $au->user_rights->CheckAccess('w',950), //8
			  $au->user_rights->CheckAccess('w',961),  //9
			  $au->user_rights->CheckAccess('w',950), //10
			  $au->user_rights->CheckAccess('w',965), //11
			  $au->user_rights->CheckAccess('w',950), //12
			  $au->user_rights->CheckAccess('w',950), //13
			  $au->user_rights->CheckAccess('w',956), //14
			  $au->user_rights->CheckAccess('w',957), //15
			 
	 
			  $au->user_rights->CheckAccess('w',958), //16
			  $au->user_rights->CheckAccess('w',959), //17
			   $prefix //18
			
			 );
	  
	 
	}else{
		$editing_user=$_ti->getitembyid($id);
		$sm=new SmartyAj;
		
		
		
		//блок аннулирования
		
		$editing_user['can_annul']=$_ti->DocCanAnnul($editing_user['id'],$reason, NULL, $result)&&$au->user_rights->CheckAccess('w',950);
		if(!$au->user_rights->CheckAccess('w',950)) $reason='недостаточно прав для данной операции';
		$editing_user['can_annul_reason']=$reason;
		
		//$editing_user['binded_to_annul']=$_ti->GetBindedDocumentsToAnnul($editing_user['id']);
		
		
		$editing_user['can_restore']=$_ti->DocCanRestore($editing_user['id'],$reason)&&$au->user_rights->CheckAccess('w',905);
			if(!$au->user_rights->CheckAccess('w',905)) $reason='недостаточно прав для данной операции';
		
		$stat=$_stat->Getitembyid($editing_user['status_id']);
		$editing_user['status_name']=$stat['name'];
		
		$sm->assign('bill',$editing_user);
		$ret=$sm->fetch('lead/toggle_annul_card.html');		
	}
		
}

 

//utv- razutv
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_confirm_price")){
	$id=abs((int)$_POST['id']);
	 
	
		$_ki=new lead_AbstractItem;
	
		$trust=$_ki->getitembyid($id);
		
	$_res=new lead_Resolver($trust['kind_id']);
	$_ti=$_res->instance;	
	
	$_si=new UserSItem;
	
	
	$_si=new UserSItem;
	 
	if($trust['confirm_pdate']==0) $trust['confirm_pdate']='-';
	else $trust['confirm_pdate']=date("d.m.Y H:i:s",$trust['confirm_pdate']);
	
	
	$si=$_si->getitembyid($trust['user_confirm_id']);
	$trust['confirmed_price_name']=$si['name_s'];
	$trust['confirmed_price_login']=$si['login'];
	
	$bill_id=$id;
	
	if($trust['is_confirmed']==1){
		//есть права: либо сам утв.+есть права, либо есть искл. права:
		if($au->user_rights->CheckAccess('w',965)&&$_ti->DocCanUnconfirmPrice($id, $rss)){
			    
				$_ti->Edit($id,array('is_confirmed'=>0, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()), true,$result);
				
				$log->PutEntry($result['id'],'снял утверждение заполнения лида',NULL,965, NULL, NULL,$id);
				 
					
			 
		} 
		
	}else{
		//есть права
		if($au->user_rights->CheckAccess('w',905)&&$_ti->DocCanConfirmPrice($id, $rss)){
			 
				$_ti->Edit($id,array('is_confirmed'=>1, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'утвердил заполнения лида',NULL,950, NULL, NULL,$id);	
				
			 	
			 
		} 
	}
	
	
	
	$acg=new lead_Group;
	
	$shorter=abs((int)$_POST['shorter']);
	 
			$template='lead/table.html';
	 
	
	 $acg->setauthresult($result);
	
	$dec=new  DBDecorator;
	
	$dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	//if($shorter!=0) $dec->AddEntry(new SqlEntry('p.komplekt_ved_id',$trust['komplekt_ved_id'], SqlEntry::E));
	 $prefix='_leads';
	
	$ret= $acg->ShowPos(
		

			'lead/table.html',  //0
			 $dec, //1
			  $au->user_rights->CheckAccess('w',949), //2
			  $au->user_rights->CheckAccess('w',950), //3
			  0, //4
			  10000, //5
			  false, //6
			  true,  //7
			  $au->user_rights->CheckAccess('w',950), //8
			  $au->user_rights->CheckAccess('w',961),  //9
			  $au->user_rights->CheckAccess('w',950), //10
			  $au->user_rights->CheckAccess('w',965), //11
			  $au->user_rights->CheckAccess('w',950), //12
			  $au->user_rights->CheckAccess('w',950), //13
			  $au->user_rights->CheckAccess('w',956), //14
			  $au->user_rights->CheckAccess('w',957), //15
			 
	 
			  $au->user_rights->CheckAccess('w',958), //16
			  $au->user_rights->CheckAccess('w',959), //17
			  $prefix
			
			 );
	
		
}


 

elseif(isset($_POST['action'])&&($_POST['action']=="toggle_confirm_shipping")){
	$id=abs((int)$_POST['id']);
	  
	$note=SecStr(iconv('utf-8', 'windows-1251', $_POST['note']));
	
		$_ki=new lead_AbstractItem;
	
		$trust=$_ki->getitembyid($id);
		
	$_res=new lead_Resolver($trust['kind_id']);
	$_ti=$_res->instance;	
	
	$_si=new UserSItem;
	
	
	 
	 
	if($trust['confirm_done_pdate']==0) $trust['confirm_done_pdate']='-';
	else $trust['confirm_done_pdate']=date("d.m.Y H:i:s",$trust['confirm_done_pdate']);
	
	
	$si=$_si->getitembyid($trust['user_confirm_done_id']);
	$trust['confirmed_shipping_name']=$si['name_s'];
	$trust['confirmed_shipping_login']=$si['login'];
	
	$bill_id=$id;
	
	if($trust['is_confirmed_done']==1){
		//есть права: либо сам утв.+есть права, либо есть искл. права:
		if(($au->user_rights->CheckAccess('w',950))){
			 
			if($_ti->DocCanUnconfirmShip($id,$reas)){
			
				$_ti->Edit($id,array('is_confirmed_done'=>0, 'user_confirm_done_id'=>$result['id'], 'confirm_done_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'снял утверждение выполнения лида',NULL,950, NULL, ' '.$id,$bill_id);
				
			}
				
		}
		
	}else{
		//есть права
		if($au->user_rights->CheckAccess('w',950)){
			 
			if($_ti->DocCanConfirmShip($id,$reas)){
				$_ti->Edit($id,array('is_confirmed_done'=>1, 'user_confirm_done_id'=>$result['id'], 'confirm_done_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'утвердил выполнение лида',NULL,950, NULL, NULL,$id);	
				//	echo 'zzzzzzzzzzzzzzzzzzzzzzz';	
			}
			 
		} 
	}
	
	
		
	$acg=new lead_Group;
	
	 $acg->setauthresult($result);
	$shorter=abs((int)$_POST['shorter']);
	 
			$template='lead/table.html';
		 
	
	

	$dec=new  DBDecorator;
	
	$dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	 
	 
	
	$ret= $acg->ShowPos(
		

			'lead/table.html',  //0
			 $dec, //1
			  $au->user_rights->CheckAccess('w',949), //2
			  $au->user_rights->CheckAccess('w',950), //3
			  0, //4
			  10000, //5
			  false, //6
			  true,  //7
			  $au->user_rights->CheckAccess('w',950), //8
			  $au->user_rights->CheckAccess('w',961),  //9
			  $au->user_rights->CheckAccess('w',950), //10
			  $au->user_rights->CheckAccess('w',965), //11
			  $au->user_rights->CheckAccess('w',950), //12
			  $au->user_rights->CheckAccess('w',950), //13
			  $au->user_rights->CheckAccess('w',956), //14
			  $au->user_rights->CheckAccess('w',957), //15
			 
	 
			  $au->user_rights->CheckAccess('w',958), //16
			  $au->user_rights->CheckAccess('w',959), //17
			'_leads'
			
			 );
	
	
	
		
}


 



elseif(isset($_POST['action'])&&($_POST['action']=="toggle_confirm_fulfil")){
	$id=abs((int)$_POST['id']);
	  
	$note=SecStr(iconv('utf-8', 'windows-1251', $_POST['note']));
	
		$_ki=new lead_AbstractItem;
	
		$trust=$_ki->getitembyid($id);
		
	$_res=new lead_Resolver($trust['kind_id']);
	$_ti=$_res->instance;	
	
	$_si=new UserSItem;
	
	
	 
	 
	if($trust['confirm_done_pdate']==0) $trust['confirm_done_pdate']='-';
	else $trust['confirm_done_pdate']=date("d.m.Y H:i:s",$trust['confirm_done_pdate']);
	
	
	$si=$_si->getitembyid($trust['user_confirm_done_id']);
	$trust['confirmed_shipping_name']=$si['name_s'];
	$trust['confirmed_shipping_login']=$si['login'];
	
	$bill_id=$id;
	
	 
	
	if($trust['is_fulfiled']==1){
		//есть права: либо сам утв.+есть права, либо есть искл. права:
		if(($au->user_rights->CheckAccess('w',959))){
			 
			if($_ti->DocCanUnconfirmFulfil($id,$reas)){
			
				$_ti->Edit($id,array('is_fulfiled'=>0, 'user_fulfiled_id'=>$result['id'], 'fulfiled_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'снял утверждение работы лида',NULL,950, NULL, '',$id);
				
			}
				
		}
		
	}else{
		//есть права
		if($au->user_rights->CheckAccess('w',957)){
			 
			if($_ti->DocCanConfirmFulfil($id,$reas)){
				$_ti->Edit($id,array('is_fulfiled'=>1, 'user_fulfiled_id'=>$result['id'], 'fulfiled_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'утвердил работу лида',NULL,950, NULL, NULL,$id);	
				//	echo 'zzzzzzzzzzzzzzzzzzzzzzz';	
			}
			 
		} 
	}
	
	
		
	$acg=new lead_Group;
	
	 $acg->setauthresult($result);
	$shorter=abs((int)$_POST['shorter']);
	 
	 
			$template='lead/table.html';
	 
	 
	
	

	$dec=new  DBDecorator;
	
	$dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	 
	 
	
	$ret= $acg->ShowPos(
		

			'lead/table.html',  //0
			 $dec, //1
			  $au->user_rights->CheckAccess('w',949), //2
			  $au->user_rights->CheckAccess('w',950), //3
			  0, //4
			  10000, //5
			  false, //6
			  true,  //7
			  $au->user_rights->CheckAccess('w',950), //8
			  $au->user_rights->CheckAccess('w',961),  //9
			  $au->user_rights->CheckAccess('w',950), //10
			  $au->user_rights->CheckAccess('w',965), //11
			  $au->user_rights->CheckAccess('w',950), //12
			  $au->user_rights->CheckAccess('w',950), //13
			  $au->user_rights->CheckAccess('w',956), //14
			  $au->user_rights->CheckAccess('w',957), //15
			 
	 
			  $au->user_rights->CheckAccess('w',958), //16
			  $au->user_rights->CheckAccess('w',959), //17
			  '_leads'
			
			 );
	
	
		
}



 

elseif(isset($_POST['action'])&&(($_POST['action']=="add_supplier")||($_POST['action']=="add_supplier_15"))){
	$_si=new SupplierItem;
	
	$si=$_si->GetItemById(abs((int)$_POST['supplier_id']));
	
	
	$_opf=new OpfItem;
	$opf=$_opf->GetItemById($si['opf_id']);
	
	
	$si['opf_name']= $opf['name'];
	
	//var_dump($_POST['already_loaded']);
	
	if((is_array($_POST['already_loaded'])&&!in_array( $_POST['supplier_id'], $_POST['already_loaded']))||!is_array($_POST['already_loaded'])){
	
		$sm=new SmartyAj;
		
		$_csg=new SupplierCitiesGroup;
		$csg=$_csg->GetItemsByIdArr($_POST['supplier_id']);
		$si['cities']= $csg;	
		
		//загрузить выбранные контакты
		//contact_ids
		$contact_ids=$_POST['contact_ids'];
		$_sg=new SupplierContactGroup;
		//$si['contacts']=$_sg->GetItemsByIdArr($_POST['supplier_id']);
		$contacts1=$_sg->GetItemsByIdArr($_POST['supplier_id']);
		$contacts=array();
		foreach($contacts1 as $k=>$v){
			if(in_array($v['id'], $contact_ids)) $contacts[]=$v;	
		}
		$si['contacts']=$contacts;
		
			
		$sm->assign('suppliers', array($si));
		$sm->assign('has_header', false);
		$sm->assign('can_modify', true);
		
		
			
		
		if($_POST['action']=="add_supplier_15") $ret=$sm->fetch('lead/suppliers_15_table.html');
		else $ret=$sm->fetch('lead/suppliers_many_table.html');
	}
}





//подгрузка пол-лей для выбора ответственного
elseif(isset($_POST['action'])&&($_POST['action']=="load_users")){
	
	 $supplier_id=abs((int)$_POST['supplier_id']);
	/*
	$sched_id=abs((int)$_POST['sched_id']);
	$_bi1=new Tender_AbstractItem;
	$bi1=$_bi1->GetItemById($sched_id);
	
	*/
	
	$_resp=new SupplierResponsibleUserGroup;
	$resp=$_resp->GetUsersArr($supplier_id, $resp_ids);
 
	$already_in_bill=array();
	
	$complex_positions=$_POST['complex_positions'];
	$except_users=$_POST['except_users'];
	
 
	$_kpg=new lead_UsersSGroup;
	
 	$dec=new DBDecorator;
	
	$limited_user=NULL;
	if($au->FltUser($result)){
		//echo 'z';
		$_u_to_u=new UserToUser();
		$u_to_u=$_u_to_u->GetExtendedViewedUserIdsArr($result['id']);
		$limited_user=$u_to_u['sector_ids'];
		$dec->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::IN_VALUES, NULL,$limited_user));	
	}
	
	
	if(is_array($except_users)&&(count($except_users)>0)){
		$dec->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::NOT_IN_VALUES, NULL,$except_users));
	}
	
	
	$alls=$_kpg->GetItemsForBill($dec);  
	 
  
	/*echo '<pre>';
	print_r(($alls));
	echo '</pre>';*/
	 
	 
	foreach($alls as $kk=>$v){
				  
	 
		 
		  
		  //print_r($vv);
		  
		
		   //подставим значения, если они заданы ранее
		 
		  //ищем перебором массива  $complex_positions
		  /*$index=-1;
		  foreach($complex_positions as $ck=>$ccv){
		  	$cv=explode(';',$ccv);
			
			if(
				($cv[0]==$v['id'])
				 
				){
					$index=$ck;
					//echo 'nashli'.$vv['position_id'].' - '.$index;
					break;	
				}
		  	
		  }
		  
		  
		  if($index>-1){
			  //echo 'nn '.' '.$v['position_id'];
			  //var_dump($position['id']);
			  
			  
			  $valarr=explode(';',$complex_positions[$index]);
			  $v['is_in']=1;
			  
			  
			  
			  
		  }else{
			  //echo 'no no ';
			   $v['is_in']=0;
			 
		  }
		  
		  */
		  
		 /* if(in_array($v['id'], $resp_ids)) $v['is_in']=1;
		  else $v['is_in']=0; 
		  */
		  
		  if(in_array($v['id'], $resp_ids)) $v['is_in']=1;
		  else continue;
		  
		  $v['hash']=md5($v['user_id']);
		  
		 // print_r($v);
		  
		  //$alls[$k]=$v;
		  $arr[]=$v;
		
	}
	
	$sm=new SmartyAj;
	 
	$sm->assign('pospos',$arr);
	 
	 
	
 
	
	$ret.=$sm->fetch("lead/managers_set.html");
	
	 
	
	
 

   

}elseif(isset($_POST['action'])&&($_POST['action']=="redraw_is_confirmed_price_confirmer")){
	$state=abs((int)$_POST['state']);
	if($state==0){
		$ret='';	
	}elseif($state==1){
		$ret=$result['position_s'].' '.$result['name_s'].' '.date("d.m.Y H:i:s",time());	
	}
	
}

elseif(isset($_POST['action'])&&($_POST['action']=="add_comment")){
	$id=abs((int)$_POST['id']);
	
	$_hi=new lead_HistoryItem; $_hg=new lead_HistoryGroup; $_dsi=new DocStatusItem; 
	$_file=new lead_HistoryFileItem;
	
	$_sch=new lead_Item;
	$sch=$_sch->GetItemById($id);
	$count_hi=$_hg->CountHistory($id);
	
	
	$params=array();
	$params['sched_id']=$id;
	$params['txt']=SecStr(iconv("utf-8","windows-1251",$_POST['comment']));
	$params['user_id']=$result['id'];
	$params['pdate']=time();
	
	$code=$_hi->Add($params);
	
	$log->PutEntry($result['id'],'добавлен комментарий к лиду', NULL,950,NULL, $params['txt'],$id);
	
	
	if(isset($_POST['probability'])){
		$a_params=array();
		$a_params['probability']=abs((float)str_replace(',','.',$_POST['probability']));
		$_sch->Edit($id, $a_params);
		if($sch['probability']!=$a_params['probability']){
			$log->PutEntry($result['id'],'изменена вероятность заключения контракта', NULL,950,NULL, 'старое значение: '.$sch['probability'].', новое значение: '.$a_params['probability'],$id);
	
		}
		
	}
	
	$files_server=$_POST['files_server'];
	$files_client=$_POST['files_client'];
	
	foreach($files_server as $k=>$file_server){
		$file_id=$_file->Add(array(
			'history_id'=>$code,
			'filename'=>SecStr(iconv("utf-8","windows-1251",$file_server)),
			'orig_name'=>SecStr(iconv("utf-8","windows-1251",$files_client[$k])),
		));	
		
		$log->PutEntry($result['id'],'прикреплен файл к комментарию  к лиду', NULL,950,NULL, 'Комментарий '.$params['txt'].',  файл '.SecStr(iconv("utf-8","windows-1251",$files_client[$k])),$id);
		 
		
		$_ct=new FileContents(SecStr(iconv("utf-8","windows-1251", $files_client[$k])), $_file->GetStoragePath().$file_server);
		
		$contents='';
		
		try {
    		$contents=$_ct->GetContents();
		} catch (Exception $e) {
			//echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
		}
		
		$_file->Edit($file_id, array('text_contents'=>SecStr($contents)));
	}
	
	//отправить сообщения всем имеющим права 922 участникам задачи (кроме автора)
		
	/*	
	$users_to_send=array();
	$sql='select * from user where is_active=1 and id<>"'.$params['user_id'].'" and id in( select distinct user_id from sched_task_users where  sched_id="'.$params['sched_id'].'") and id in(select distinct user_id from user_rights where right_id=2 and object_id=922)';
	
	$set=new mysqlset($sql);
	$rs=$set->GetResult();
	$rc=$set->GetResultNumRows();
	
	
	$users_to_send=array();
	for($i=0; $i<$rc; $i++){
		$f=mysqli_fetch_array($rs);
		
		$users_to_send[]=$f;
	}
	$topic='Новый комментарий в задаче GYDEX.Планировщик';
	$_mi=new MessageItem;  
	
	$_fi=new SchedFileItem;
	$_user_item=new UserSItem;
	if(count($users_to_send)>0){
		$_item=new Sched_TaskItem();
		$item=$_item->GetItemById($params['sched_id']);	
	}
	foreach($users_to_send as $k1=>$user){
		
		$txt='<div>';
		$txt.='<em>Данное сообщение сгенерировано автоматически.</em>';
		$txt.=' </div>';
		
		
		$txt.='<div>&nbsp;</div>';
		
		$txt.='<div>';
		$txt.='Уважаемый(ая) '.$user['name_s'].'!';
		$txt.='</div>';
		$txt.='<div>&nbsp;</div>';
		
		
		$txt.='<div>';
		$txt.='<strong>В доступной Вам задаче GYDEX.Планировщик </strong>';
		 
		 
		$txt.='<strong><a href="ed_sched_task.php?action=1&id='.$params['sched_id'].'#lenta_commment_'.$code.'" target="_blank">'.$_item->ConstructFullName($params['sched_id'], $item).'</a></strong>';
		if($item['pdate_beg']!="") $txt.=',<strong> крайний срок:</strong> <em>'.DateFromYmd($item['pdate_beg']).' '.$item['ptime_beg'].'</em>';
		$txt.=', <strong>ваша роль:</strong> <em>';
		
		//найдем роли...
		$sql2=' select distinct k.kind_id, p.name 
		from sched_task_users as k
		inner join sched_task_users_kind as p on p.id=k.kind_id
		where k.sched_id="'.$params['sched_id'].'" and k.user_id="'.$user['id'].'"
		order by k.kind_id';
		
		//echo $sql2;
		
		$set2=new mysqlset($sql2);
		$rs2=$set2->GetResult();
		$rc2=$set2->GetResultNumRows();
		
		
		$roles=array();
		for($k=0; $k<$rc2; $k++){
			$h=mysqli_fetch_array($rs2);
			$roles[]=$h['name'];
			
			
			
		}
		
		$txt.=implode(', ', $roles);
		
		$from_user=$_user_item->GetItemById($params['user_id']);
		
		
		$txt.='</em><strong>, появился новый комментарий  от пользователя '.SecStr($from_user['name_s']).':</strong></div> ';
		
		$txt.=' <div>&nbsp;</div>';
		
		$txt.=$params['txt'];
		$txt.=' <div>&nbsp;</div>';

	//	$txt.='<div>Для просмотра комментария просьба перейти в карту задачи по ссылке.</div>';
		//найдем файлы
		 
		$sql2=' select id, orig_name
		from  sched_history_file
		where history_id="'.$code.'" 
		order by orig_name';
		
		//echo $sql2;
		
		$set2=new mysqlset($sql2);
		$rs2=$set2->GetResult();
		$files_count=$set2->GetResultNumRows();
		
		//_file
		$files=array();
		for($k=0; $k<$files_count; $k++){
			$h=mysqli_fetch_array($rs2);
			$files[]='<a href="sched_lenta_file.html?id='.$h['id'].'" class="sched_report_file_link" target="_blank">'.$h['orig_name'].'</a>';
			
			
		}
		
		if($files_count>0){
			$txt.='<div>К комментарию прикреплено '.$files_count.' файлов: '.implode(', ',$files).'.</div>';
		}
		
		$txt.='<div>&nbsp;</div>';
	
		$txt.='<div>';
		$txt.='C уважением, программа "'.SITETITLE.'".';
		$txt.='</div>';
		
		$_mi->Send(0,0,array('from_id'=>-1, 'to_id'=>$user['id'],'pdate'=>time(), 'parent_id'=>0, 'txt'=>SecStr($txt), 'topic'=>$topic),false);	
	}
	
	*/
	
	
	//
	if(($sch['status_id']==23)&&($count_hi==0)){
		$_sch->Edit($id,array('status_id'=>24));
					  
			$log->PutEntry($result['id'],'начал выполнение лида',NULL,950, NULL, NULL,$id);
			
			$stat=$_dsi->GetItemById(24);
			$log->PutEntry($result['id'],'смена статуса лида',NULL,950,NULL,'установлен статус '.$stat['name'],$id);	
		
	}
	
	
	//вывести что получилось
	$_hr=new lead_HistoryGroup;
	
	$dec=new DBDecorator();
	$dec->AddEntry(new SqlEntry('o.id',$code, SqlEntry::E));
	
	$ret=$_hr->ShowHistory($id, 'lead/lenta.html', $dec, true, false, true,  $result,
			 $au->user_rights->CheckAccess('w',951),
			 $au->user_rights->CheckAccess('w',952));
			 
			 
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="edit_comment")){
	$id=abs((int)$_POST['id']);
	$comment_id=abs((int)$_POST['comment_id']);
	
	$_hi=new lead_HistoryItem;
	$_file=new lead_HistoryFileItem;
	
	$params=array();
	//$params['sched_id']=$id;
	$params['txt']=SecStr(iconv("utf-8","windows-1251",$_POST['comment']));
	//$params['user_id']=$result['id'];
	//$params['pdate']=time();
	
	 $_hi->Edit($comment_id, $params);
	
	$log->PutEntry($result['id'],'редактирован комментарий к лиду', NULL,950,NULL, $params['txt'],$id);
	
	
	$files_server=$_POST['files_server'];
	$files_client=$_POST['files_client'];
	
	foreach($files_server as $k=>$file_server){
		$file_id=$_file->Add(array(
			'history_id'=>$comment_id,
			'filename'=>SecStr(iconv("utf-8","windows-1251",$file_server)),
			'orig_name'=>SecStr(iconv("utf-8","windows-1251",$files_client[$k])),
		));	
		
		$log->PutEntry($result['id'],'прикреплен файл к комментарию к лиду', NULL,950,NULL, 'Комментарий '.$params['txt'].',  файл '.SecStr(iconv("utf-8","windows-1251",$files_client[$k])),$id);
		
		$_ct=new FileContents(SecStr(iconv("utf-8","windows-1251", $files_client[$k])), $_file->GetStoragePath().$file_server);
		
		$contents='';
		
		try {
    		$contents=$_ct->GetContents();
		} catch (Exception $e) {
			//echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
		}
		
		$_file->Edit($file_id, array('text_contents'=>SecStr($contents)));
	}
	
	//вывести что получилось
	$_hr=new lead_HistoryGroup;
	
	$dec=new DBDecorator();
	$dec->AddEntry(new SqlEntry('o.id',$comment_id, SqlEntry::E));
	
	$ret=$_hr->ShowHistory($id, 'lead/lenta.html', $dec, true, false, true,  $result,
			 $au->user_rights->CheckAccess('w',951),
			 $au->user_rights->CheckAccess('w',952));
	
}

elseif(isset($_POST['action'])&&($_POST['action']=="toggle_comment")){
	$id=abs((int)$_POST['id']);
	$comment_id=abs((int)$_POST['comment_id']);
	
	$_hi=new lead_HistoryItem;
	$hi=$_hi->GetItemById($comment_id);
	
	if($hi['is_shown']==1){
		$_hi->Edit($comment_id, array('is_shown'=>0));
		$log->PutEntry($result['id'],'скрыт комментарий к лиду', NULL,950,NULL, 'Комментарий '.$hi['txt'].' ',$id);
		$ret=0;
	}else{
		$_hi->Edit($comment_id, array('is_shown'=>1));
		$log->PutEntry($result['id'],'показан комментарий к лиду', NULL,950,NULL, 'Комментарий '.$hi['txt'].' ',$id);
		$ret=1;
	}
	
}

elseif(isset($_POST['action'])&&($_POST['action']=="load_pdf_addresses")){
	
	$id=abs((int)$_POST['id']);
	
	
	
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
		/*(select "0" as kind, name as name_s, "" as login, position as position_s, id, "" as email_s
			from supplier_contact
			where ( supplier_id in(select distinct supplier_id from sched_suppliers where  sched_id="'.$id.'") or  supplier_id in(select distinct supplier_id from sched_contacts where  sched_id="'.$id.'"))
			and id in(select distinct contact_id from supplier_contact_data where kind_id=5)
			)
		UNION ALL*/
		(select "1" as kind, name_s as name_s, login as login, position_s as position_s, id, email_s as email_s		
			from user
			where is_active=1 
			/*and id in(select distinct user_id from user_contact_data where kind_id=5)*/ '.$limited.'
			
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
	$ret=$sm->fetch('lead/pdf_addresses.html');

}

elseif(isset($_POST['action'])&&($_POST['action']=="has_files")){
//есть ли файлы по записи план-ка?
	$count_of_files=0;	
	$id=abs((int)$_POST['id']);
	
	$sql='select count(*) from lead_file where bill_id="'.$id.'" ';
	
	$set=new mysqlset($sql);
	$rs=$set->GetResult();
	
	$f=mysqli_fetch_array($rs);
	
	$count_of_files+=(int)$f[0];
	
	
	$sql='select count(*) from lead_history_file where history_id in(select id from lead_history where sched_id="'.$id.'" )';
	
	
	$set=new mysqlset($sql);
	$rs=$set->GetResult();
	
	$f=mysqli_fetch_array($rs);
	$count_of_files+=(int)$f[0];
	
	$ret=$count_of_files;
}

elseif(isset($_GET['action'])&&($_GET['action']=="retrieve_manager")){
	$_si=new UserSItem;
	
	$si=$_si->GetItemById(abs((int)$_GET['id']));
	
	 
 
	
	if($si!==false){
		$rret=array();
		foreach($si as $k=>$v){
			 
			
			$rret[]='"'.$k.'":"'.htmlspecialchars(str_replace("\r", "", str_replace("\n",  "",  $v))).'"';
		}
		
		 
		
		$ret='{'.implode(', ',$rret).'}';
	}
	
}
elseif(isset($_GET['action'])&&($_GET['action']=="iam_manager")){
	$_si=new UserSItem;
	
	$si=$_si->GetItemById($result['id']);
	
	 
 
	
	if($si!==false){
		$rret=array();
		foreach($si as $k=>$v){
			 
			
			$rret[]='"'.$k.'":"'.htmlspecialchars(str_replace("\r", "", str_replace("\n",  "",  $v))).'"';
		}
		
		 
		
		$ret='{'.implode(', ',$rret).'}';
	}
	
}
//нахождение и подстановка единственного куратора в карту
elseif(isset($_GET['action'])&&($_GET['action']=="scan_manager")){
	
	$supplier_id=abs((int)$_GET['supplier_id']);
	
	$_resp=new SupplierResponsibleUserGroup;
	$resp=$_resp->GetUsersArr($supplier_id, $resp_ids);
	if(count($resp_ids)==1){
	
		$_si=new UserSItem;
		
		$si=$_si->GetItemById($resp_ids[0]);
		
		  
		if($si!==false){
			$rret=array();
			foreach($si as $k=>$v){
				 
				
				$rret[]='"'.$k.'":"'.htmlspecialchars(str_replace("\r", "", str_replace("\n",  "",  $v))).'"';
			}
			 
			$ret='{'.implode(', ',$rret).'}';
		}
		
	}else{
		$ret='{"id":"0"}';	
	}
	
}



//проверка доступности контрагента сотруднику
elseif(isset($_POST['action'])&&($_POST['action']=="check_managers_to_supplier")){
	//0 - все ОК
	//не 0 - нет доступа
	$supplier_ids=$_POST['supplier_ids'];
		
	$_s_to=new SupplierToUser;
	$manager_id=abs((int)$_POST['manager_id']);
	
	$res=true; $output=array();
	foreach($supplier_ids as $k=>$supplier_id){
		$supplier_id=abs((int)$supplier_id);
		
	
		
		$data=$_s_to->GetExtendedViewedUserIdsArr($manager_id, $result);
		if(!in_array($supplier_id, $data['sector_ids'])) {
			$res=$res&&false;
			$output[]=$supplier_id;
		}
		/*echo $manager_id.' '; echo $supplier_id.' ';
		var_dump($data['sector_ids']);*/
	}
	
	
	if($res) $ret= 0;
	else $ret=implode(';',$output);
	
	 
}


//РАБОТА С типами оборудования
elseif(isset($_POST['action'])&&($_POST['action']=="redraw_types_dics")){
	$sm=new SmartyAj;
	if(isset($_POST['user_id'])) $user_id=abs((int)$_POST['user_id']);
	else $user_id=0;
	
	$opg=new lead_EqTypeGroup;
	$sm->assign('opfs_total', $opg->GetItemsArr());
	
	$ret=$sm->fetch('lead/d_types.html');
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="redraw_types_page")){
	//$sm=new SmartyAj;
	
	if(isset($_POST['user_id'])) $user_id=abs((int)$_POST['user_id']);
	else $user_id=0;
	
	$opg=new lead_EqTypeGroup;
	$ret=$opg->GetItemsOpt($user_id);
	
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="add_type")){
	
	//dostup
	/*if(!$au->user_rights->CheckAccess('w',19)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	*/
	$qi=new lead_EqTypeItem;
	$params=array();
	$params['name']=SecStr(iconv("utf-8","windows-1251",$_POST['opf']),9);
	$qi->Add($params);
	
	//$log->PutEntry($result['id'],'добавил ОПФ',NULL,19,NULL,$params['name']);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="edit_type")){
	/*if(!$au->user_rights->CheckAccess('w',19)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}*/
	
	$qi=new lead_EqTypeItem;
	$params=array();
	$id=abs((int)$_POST['id']);
	$params['name']=SecStr(iconv("utf-8","windows-1251",$_POST['question']),9);
	$qi->Edit($id,$params);	
	
	//$log->PutEntry($result['id'],'редактировал ОПФ',NULL,19,NULL,$params['name']);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="delete_type")){
	
	/*if(!$au->user_rights->CheckAccess('w',13)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}*/
	
	$qi=new lead_EqTypeItem;
	
	$id=abs((int)$_POST['id']);
	$qi->Del($id);
	
	//$log->PutEntry($result['id'],'удалил ОПФ',NULL,19,NULL,$params['name']);
}

elseif(isset($_POST['action'])&&($_POST['action']=="check_pdate_finish")){
	 
	
		$tender_id=abs((int)$_POST['tender_id']);
		$pdate=$_POST['pdate'];
		$ptime_h=$_POST['ptime_h'];
		$ptime_m=$_POST['ptime_m'];
		
		
	
		
		$_ki=new Lead_Item;
		
		
		
		if(!$_ki->ControlPdateFinish($tender_id, $pdate, $ptime_h, $ptime_m, $reason)) {
			$ret=$reason;
			//var_dump($reason);
		}
		else $ret=0;
		
		
		//если ноль - то все хорошо
}

elseif(isset($_POST['action'])&&($_POST['action']=="get_probability")){
	 
	
		$id=abs((int)$_POST['id']);
		 
		
	
		
		$_ki=new Lead_Item;
		
		
		$ki=$_ki->getitembyid($id);
		
		$ret=$ki['probability'];	 
		
		 
 

}elseif(isset($_POST['action'])&&($_POST['action']=="fail_unconfirm_price")){
	
	 
	
	 
	
	$id=abs((int)$_POST['id']);
	$_ti=new Lead_Item;
	$ti=$_ti->getitembyid($id);
	 
	$log->PutEntry($result['id'],'отказ снять утверждение заполнения лида',NULL,965, NULL, 'Попытка снять утверждение заполнения лида № '.$ti['code'].': в действии отказано, причина - недостаточно прав',$id);	
	 
}

//будет ли доступ к тендеру при указанном менеджере указанному сотруднику
elseif(isset($_POST['action'])&&($_POST['action']=="scan_available_by_user_id")){
	$_tg=new Lead_Group;
	
	$manager_id=abs((int)$_POST['manager_id']);
	
	$res=$_tg->ScanAvailableByUserId($manager_id, $result['id']);
	if($res) $ret=1;
	else $ret=0;
	
	 
}


//РАБОТА С прчинами отказа
elseif(isset($_POST['action'])&&($_POST['action']=="redraw_fails_dics")){
	$sm=new SmartyAj;
	if(isset($_POST['user_id'])) $user_id=abs((int)$_POST['user_id']);
	else $user_id=0;
	
	$opg=new Lead_FailGroup;
	$sm->assign('fails_total', $opg->GetItemsArr());
	
	$ret=$sm->fetch('lead/fails.html');
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="redraw_fails_page")){
	//$sm=new SmartyAj;
	
	if(isset($_POST['user_id'])) $user_id=abs((int)$_POST['user_id']);
	else $user_id=0;
	
	$opg=new Lead_FailGroup;
	$ret=$opg->GetItemsOpt($user_id);
	
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="add_fail")){
	
	//dostup
	/*if(!$au->user_rights->CheckAccess('w',19)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	*/
	$qi=new Lead_FailItem;
	$params=array();
	$params['name']=SecStr(iconv("utf-8","windows-1251",$_POST['opf']),9);
	$qi->Add($params);
	
	//$log->PutEntry($result['id'],'добавил ОПФ',NULL,19,NULL,$params['name']);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="edit_fail")){
	/*if(!$au->user_rights->CheckAccess('w',19)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}*/
	
	$qi=new Lead_FailItem;
	$params=array();
	$id=abs((int)$_POST['id']);
	$params['name']=SecStr(iconv("utf-8","windows-1251",$_POST['question']),9);
	$qi->Edit($id,$params);	
	
	//$log->PutEntry($result['id'],'редактировал ОПФ',NULL,19,NULL,$params['name']);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="delete_fail")){
	
	/*if(!$au->user_rights->CheckAccess('w',13)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}*/
	
	$qi=new Lead_FailItem;
	
	$id=abs((int)$_POST['id']);
	$qi->Del($id);
	
	//$log->PutEntry($result['id'],'удалил ОПФ',NULL,19,NULL,$params['name']);
 

}


//настройка реестра
elseif(isset($_POST['action'])&&(($_POST['action']=="mode_reestr"))){
	$_views=new Lead_ViewsGroup;
	$_view=new Lead_ViewsItem;
	
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
	$_views=new Lead_ViewsGroup;
	$_view=new Lead_ViewsItem;
	
	 
	
	$_views->Clear($result['id']);
	 
 

}elseif(isset($_POST['action'])&&($_POST['action']=="check_docs_unconfirm_ship")){
	 	//проверка связанных документов
		$id=abs((int)$_POST['id']);
		
	
		
	  
		$_dem=new lead_AbstractItem;
		$dem=$_dem->Getitembyid($id);
		
		$_res=new lead_Resolver($dem['kind_id']);
		
		
		$_ki=$_res->instance;
		
		
		
		if($_ki->DocUncomfirmShipDocs($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
}elseif(isset($_POST['action'])&&($_POST['action']=="check_docs_unconfirm_fulfil")){
	 	//проверка связанных документов
		$id=abs((int)$_POST['id']);
		
	
		
	  
		$_dem=new lead_AbstractItem;
		$dem=$_dem->Getitembyid($id);
		
		$_res=new lead_Resolver($dem['kind_id']);
		
		
		$_ki=$_res->instance;
		
		
		
		if($_ki->DocUncomfirmFulfilDocs($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
 

}elseif(isset($_POST['action'])&&($_POST['action']=="check_docs_binded_child")){
	 	//проверка связанных дочерних
		$id=abs((int)$_POST['id']);
		
	
		
	  	$_ki=new Lead_Item;
	 	
		$data=$_ki->FindBindedChild($id);
		
		
		if(count($data)>0) $ret=implode(' ', $data);
		else $ret=0;
		
}

//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>