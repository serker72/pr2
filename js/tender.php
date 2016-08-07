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



require_once('../classes/tender.class.php');
require_once('../classes/tender_history_fileitem.php');
require_once('../classes/tender_history_item.php');
require_once('../classes/tender_history_group.php');

require_once('../classes/filecontents.php');
 
require_once('../classes/quick_suppliers_group.php'); 

require_once('../classes/supplieritem.php'); 
require_once('../classes/supplier_responsible_user_group.php');

 
require_once('../classes/lead.class.php');
require_once('../classes/lead_history_item.php');

require_once('../classes/pl_currgroup.php');

require_once('../classes/pl_curritem.php');

require_once('../classes/tender_view.class.php');

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
			
		
                        // KSK - 01.04.2016
                        // добавляем условие выбора записей для отображения is_shown=1
			//$dec->AddEntry(new SqlEntry('p.id','select distinct supplier_id from supplier_contact where  '.implode(' or ',$names).'', SqlEntry::IN_SQL));
			$dec->AddEntry(new SqlEntry('p.id','select distinct supplier_id from supplier_contact where  is_shown=1 and ('.implode(' or ',$names).')', SqlEntry::IN_SQL));
		
	}
	
		if(isset($_POST['already_loaded'])&&is_array($_POST['already_loaded'])) $dec->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::NOT_IN_VALUES, NULL,$_POST['already_loaded']));	
	
	
	//ограничения по к-ту
	$limited_supplier=NULL;
	//ограничений нет
 		
	if($_POST['action']=="find_suppliers_ship") $ret=$_pg->GetItemsForBill('tender/ship_suppliers_list.html',  $dec,true,$all7,$result, 0, (strlen(SecStr(iconv("utf-8","windows-1251",$_POST['contact'])))>0)); 
	elseif($_POST['action']=="find_many_suppliers") $ret=$_pg->GetItemsForBill('tender/suppliers_many_list.html',  $dec,true,$all7,$result, 0, (strlen(SecStr(iconv("utf-8","windows-1251",$_POST['contact'])))>0));  
	elseif($_POST['action']=="find_many_suppliers_15") $ret=$_pg->GetItemsForBill('tender/suppliers_15_list.html',  $dec,true,$all7,$result, 0, (strlen(SecStr(iconv("utf-8","windows-1251",$_POST['contact'])))>0));  
	else $ret=$_pg->GetItemsForBill('tender/suppliers_list.html',  $dec,true,$all7,$result, 0, (strlen(SecStr(iconv("utf-8","windows-1251",$_POST['contact'])))>0)); 
	
	
	 
	
	

	
} 
elseif(isset($_POST['action'])&&(($_POST['action']=="retrieve_contacts")||($_POST['action']=="retrieve_only_contacts"))){
	$_sc=new Tender_SupplierContactGroup;
	
	
	$supplier_id=abs((int)$_POST['supplier_id']);
	$current_id=abs((int)$_POST['current_id']);
	$current_k_id=abs((int)$_POST['current_k_id']);
	
	
	
	
	$alls=$_sc->GetItemsByIdArr($supplier_id,$current_id, $current_k_id); 
	$sm=new SmartyAj;
	
	
	$sm->assign('supplier_id', $supplier_id);
	$sm->assign('items', $alls);
	
	if($_POST['action']=="retrieve_only_contacts") $ret=$sm->fetch('tender/suppliers_only_contacts.html');
	else $ret=$sm->fetch('tender/suppliers_contacts.html');

}
  

elseif(isset($_POST['action'])&&($_POST['action']=="check_confirm")){
		$id=abs((int)$_POST['id']);
		
	    
		$_dem=new Tender_AbstractItem;
		$dem=$_dem->Getitembyid($id);
		
		$_res=new Tender_Resolver($dem['kind_id']);
		
		
		$_ki=$_res->instance;
		
		
		if(!$_ki->DocCanConfirmShip($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
	



	
}elseif(isset($_POST['action'])&&($_POST['action']=="check_unconfirm")){
	//проверить, есть ли заявки с таким номером для такого уч.
	
		$id=abs((int)$_POST['id']);
		
	
		
	  
		$_dem=new Tender_AbstractItem;
		$dem=$_dem->Getitembyid($id);
		
		$_res=new Tender_Resolver($dem['kind_id']);
		
		
		$_ki=$_res->instance;
		
		
		
		if(!$_ki->DocCanUnconfirmShip($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
}elseif(isset($_POST['action'])&&($_POST['action']=="check_confirm")){
		$id=abs((int)$_POST['id']);
		
	
		
	  
		$_dem=new Tender_AbstractItem;
		$dem=$_dem->Getitembyid($id);
		
		$_res=new Tender_Resolver($dem['kind_id']);
		
		
		$_ki=$_res->instance;
		
		
		if(!$_ki->DocCanConfirmShip($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
	



 

}elseif(isset($_POST['action'])&&($_POST['action']=="check_unconfirm_fulfil")){
	//проверить, есть ли заявки с таким номером для такого уч.
	
		$id=abs((int)$_POST['id']);
		
		$_ki=new Tender_AbstractItem;
		$f=$_ki->GetItemById($id);
		
	  
		//$_ki=new Sched_TaskItem;
		$_res=new Tender_Resolver($f['kind_id']);
		 
		
		
		if(!$_res->instance->DocCanUnconfirmFulfil($id,$rss55,$f)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
}elseif(isset($_POST['action'])&&($_POST['action']=="check_confirm_fulfil")){
		$id=abs((int)$_POST['id']);
		
		$_ki=new Tender_AbstractItem;
		$f=$_ki->GetItemById($id);
		
	  
		//$_ki=new Sched_TaskItem;
		$_res=new Tender_Resolver($f['kind_id']);
		 
		
		
		if(!$_res->instance->DocCanConfirmFulfil($id,$rss55,$f)) $ret=$rss55;
		else $ret=0;
		
		//если ноль - то все хорошо
	

}


elseif(isset($_POST['action'])&&($_POST['action']=="check_unconfirm_price")){
	
		$id=abs((int)$_POST['id']);
		
	
		
		  
		$_dem=new Tender_AbstractItem;
		$dem=$_dem->Getitembyid($id);
		
		$_res=new Tender_Resolver($dem['kind_id']);
		
		
		$_ki=$_res->instance;
		
		
		
		if(!$_ki->DocCanUnconfirmPrice($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
}elseif(isset($_POST['action'])&&($_POST['action']=="check_confirm_price")){
		$id=abs((int)$_POST['id']);
		
	
		
		  
		$_dem=new Tender_AbstractItem;
		$dem=$_dem->Getitembyid($id);
		
		$_res=new Tender_Resolver($dem['kind_id']);
		
		
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
	
	$_ki=new Tender_AbstractItem;
	
		$trust=$_ki->getitembyid($id);
		
	$_res=new Tender_Resolver($trust['kind_id']);
	$_ti=$_res->instance;	
	
	$_si=new UserSItem;
	

	
	$log=new ActionLog;
	$_stat=new DocStatusItem;
	
	
	
	if(($trust['status_id']==18)&&($trust['is_confirmed']==0)){
		//удаление	
		if($au->user_rights->CheckAccess('w',905)){
			$_ti->Edit($id,array('status_id'=>3),false,$result);
			
			$stat=$_stat->GetItemById(3);
			$log->PutEntry($result['id'],'аннулирование тендера',NULL,931,NULL,'тендер '.$trust['code'].': установлен статус '.$stat['name'],$id);	
			
			 
			//внести примечание
			/*$_ni=new BillNotesItem;
			$_ni->Add(array(
				'user_id'=>$id,
				'posted_user_id'=>$result['id'],
				'note'=>'Автоматическое примечание: документ был аннулирован пользователем '.SecStr($result['name_s']).' ('.$result['login'].'), причина: '.$note,
				'is_auto'=>1,
				'pdate'=>time()
					));	*/
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
	 
	 
			$template='tender/table.html';
	 
	 
	  
	  $acg=new Tender_Group;
	  $acg->setauthresult($result);
	  
	  $dec=new  DBDecorator;
	  
	  $dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	
	
	  $ret=$acg->ShowPos(
		

			'tender/table.html',  //0
			 $dec, //1
			  $au->user_rights->CheckAccess('w',930), //2
			  $au->user_rights->CheckAccess('w',931), //3
			  0, //4
			  10000, //5
			  false, //6
			  true,  //7
			  $au->user_rights->CheckAccess('w',972), //8
			  $au->user_rights->CheckAccess('w',931),  //9
			  $au->user_rights->CheckAccess('w',931), //10
			  $au->user_rights->CheckAccess('w',962), //11
			  $au->user_rights->CheckAccess('w',931), //12
			  $au->user_rights->CheckAccess('w',931), //13
			  $au->user_rights->CheckAccess('w',935), //14
			  $au->user_rights->CheckAccess('w',936), //15
			 
	 
			  $au->user_rights->CheckAccess('w',937), //16
			  $au->user_rights->CheckAccess('w',938), //17
			  $au->user_rights->CheckAccess('w',1116) //18
			
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
	 
	
		$_ki=new Tender_AbstractItem;
	
		$trust=$_ki->getitembyid($id);
		
	$_res=new Tender_Resolver($trust['kind_id']);
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
		if($au->user_rights->CheckAccess('w',962)&&$_ti->DocCanUnconfirmPrice($id, $rss)){
			    
				$_ti->Edit($id,array('is_confirmed'=>0, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()), true,$result);
				
				$log->PutEntry($result['id'],'снял утверждение заполнения тендера',NULL,962, NULL, NULL,$id);
				 
					
			 
		} 
		
	}else{
		//есть права
		if($au->user_rights->CheckAccess('w',931)&&$_ti->DocCanConfirmPrice($id, $rss)){
			 
				$_ti->Edit($id,array('is_confirmed'=>1, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'утвердил заполнения тендера',NULL,931, NULL, NULL,$id);	
				
			 	
			 
		} 
	}
	
	
	
	$acg=new Tender_Group;
	
	$shorter=abs((int)$_POST['shorter']);
	 
			$template='tender/table.html';
	 
	 $acg->setauthresult($result);
	
	
	$dec=new  DBDecorator;
	
	$dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	//if($shorter!=0) $dec->AddEntry(new SqlEntry('p.komplekt_ved_id',$trust['komplekt_ved_id'], SqlEntry::E));
	
	$ret= $acg->ShowPos(
		

			'tender/table.html',  //0
			 $dec, //1
			  $au->user_rights->CheckAccess('w',930), //2
			  $au->user_rights->CheckAccess('w',931), //3
			  0, //4
			  10000, //5
			  false, //6
			  true,  //7
			  $au->user_rights->CheckAccess('w',972), //8
			  $au->user_rights->CheckAccess('w',931),  //9
			  $au->user_rights->CheckAccess('w',931), //10
			  $au->user_rights->CheckAccess('w',962), //11
			  $au->user_rights->CheckAccess('w',931), //12
			  $au->user_rights->CheckAccess('w',931), //13
			  $au->user_rights->CheckAccess('w',935), //14
			  $au->user_rights->CheckAccess('w',936), //15
			 
	 
			  $au->user_rights->CheckAccess('w',937), //16
			  $au->user_rights->CheckAccess('w',938), //17
			  $au->user_rights->CheckAccess('w',1116) //18
			
			 );
	
		
}


 

elseif(isset($_POST['action'])&&($_POST['action']=="toggle_confirm_shipping")){
	$id=abs((int)$_POST['id']);
	  
	$note=SecStr(iconv('utf-8', 'windows-1251', $_POST['note']));
	
		$_ki=new Tender_AbstractItem;
	
		$trust=$_ki->getitembyid($id);
		
	$_res=new Tender_Resolver($trust['kind_id']);
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
		if(($au->user_rights->CheckAccess('w',931))){
			 
			if($_ti->DocCanUnconfirmShip($id,$reas)){
			
				$_ti->Edit($id,array('is_confirmed_done'=>0, 'user_confirm_done_id'=>$result['id'], 'confirm_done_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'снял утверждение выполнения тендера',NULL,931, NULL, ''.$note,$id);
				
			}
				
		}
		
	}else{
		//есть права
		if($au->user_rights->CheckAccess('w',931)){
			 
			if($_ti->DocCanConfirmShip($id,$reas)){
				$_ti->Edit($id,array('is_confirmed_done'=>1, 'user_confirm_done_id'=>$result['id'], 'confirm_done_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'утвердил выполнение тендера',NULL,931, NULL, NULL,$id);	
				//	echo 'zzzzzzzzzzzzzzzzzzzzzzz';	
			}
			 
		} 
	}
	
	
		
	$acg=new Tender_Group;
	 $acg->setauthresult($result);
	
	$shorter=abs((int)$_POST['shorter']);
	 
			$template='tender/table.html';
		 
	
	

	$dec=new  DBDecorator;
	
	$dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	 
	 
	
	$ret= $acg->ShowPos(
		

			'tender/table.html',  //0
			 $dec, //1
			  $au->user_rights->CheckAccess('w',930), //2
			  $au->user_rights->CheckAccess('w',931), //3
			  0, //4
			  10000, //5
			  false, //6
			  true,  //7
			  $au->user_rights->CheckAccess('w',972), //8
			  $au->user_rights->CheckAccess('w',931),  //9
			  $au->user_rights->CheckAccess('w',931), //10
			  $au->user_rights->CheckAccess('w',962), //11
			  $au->user_rights->CheckAccess('w',931), //12
			  $au->user_rights->CheckAccess('w',931), //13
			  $au->user_rights->CheckAccess('w',935), //14
			  $au->user_rights->CheckAccess('w',936), //15
			 
	 
			  $au->user_rights->CheckAccess('w',937), //16
			  $au->user_rights->CheckAccess('w',938), //17
			  $au->user_rights->CheckAccess('w',1116) //18
			
			 );
	
	
	
		
}


 



elseif(isset($_POST['action'])&&($_POST['action']=="toggle_confirm_fulfil")){
	$id=abs((int)$_POST['id']);
	  
	$note=SecStr(iconv('utf-8', 'windows-1251', $_POST['note']));
	
		$_ki=new Tender_AbstractItem;
	
		$trust=$_ki->getitembyid($id);
		
	$_res=new Tender_Resolver($trust['kind_id']);
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
		if(($au->user_rights->CheckAccess('w',938))){
			 
			if($_ti->DocCanUnconfirmFulfil($id,$reas)){
			
				$_ti->Edit($id,array('is_fulfiled'=>0, 'user_fulfiled_id'=>$result['id'], 'fulfiled_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'снял утверждение работы тендера',NULL,931, NULL, '',$id);
				
			}
				
		}
		
	}else{
		//есть права
		if($au->user_rights->CheckAccess('w',936)){
			 
			if($_ti->DocCanConfirmFulfil($id,$reas)){
				$_ti->Edit($id,array('is_fulfiled'=>1, 'user_fulfiled_id'=>$result['id'], 'fulfiled_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'утвердил работу тендера',NULL,931, NULL, NULL,$id);	
				//	echo 'zzzzzzzzzzzzzzzzzzzzzzz';	
			}
			 
		} 
	}
	
	
		
	$acg=new Tender_Group;
	 $acg->setauthresult($result);
	
	$shorter=abs((int)$_POST['shorter']);
	 
	 
			$template='tender/table.html';
	 
	 
	
	

	$dec=new  DBDecorator;
	
	$dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	 
	 
	
	$ret= $acg->ShowPos(
		

			'tender/table.html',  //0
			 $dec, //1
			  $au->user_rights->CheckAccess('w',930), //2
			  $au->user_rights->CheckAccess('w',931), //3
			  0, //4
			  10000, //5
			  false, //6
			  true,  //7
			  $au->user_rights->CheckAccess('w',972), //8
			  $au->user_rights->CheckAccess('w',931),  //9
			  $au->user_rights->CheckAccess('w',931), //10
			  $au->user_rights->CheckAccess('w',962), //11
			  $au->user_rights->CheckAccess('w',931), //12
			  $au->user_rights->CheckAccess('w',931), //13
			  $au->user_rights->CheckAccess('w',935), //14
			  $au->user_rights->CheckAccess('w',936), //15
			 
	 
			  $au->user_rights->CheckAccess('w',937), //16
			  $au->user_rights->CheckAccess('w',938), //17
				$au->user_rights->CheckAccess('w',1116) //18  
			
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
		
		
			
		
		if($_POST['action']=="add_supplier_15") $ret=$sm->fetch('tender/suppliers_15_table.html');
		else $ret=$sm->fetch('tender/suppliers_many_table.html');
	}
}





//подгрузка пол-лей для выбора ответственного
elseif(isset($_POST['action'])&&($_POST['action']=="load_users")){
	
	 $supplier_id=abs((int)$_POST['supplier_id']);
	 
	
	$_resp=new SupplierResponsibleUserGroup;
	$resp=$_resp->GetUsersArr($supplier_id, $resp_ids);
 
	$already_in_bill=array();
	
	$complex_positions=$_POST['complex_positions'];
	$except_users=$_POST['except_users'];
	
 
	$_kpg=new Tender_UsersSGroup;
	
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
				  
	 
		 
		 
		  
		  if(in_array($v['id'], $resp_ids)) $v['is_in']=1;
		  else continue;
		  
		  $v['hash']=md5($v['user_id']);
		  
		 // print_r($v);
		  
		  //$alls[$k]=$v;
		  $arr[]=$v;
		
	}
	
	$sm=new SmartyAj;
	 
	$sm->assign('pospos',$arr);
	 
	 
	
 
	
	$ret.=$sm->fetch("tender/managers_set.html");
	
	 
}
	

//подгрузка пол-лей для выбора ответственного
elseif(isset($_POST['action'])&&($_POST['action']=="load_users_1")){
	
	 $supplier_id=abs((int)$_POST['supplier_id']);
	 
	 
 
	$already_in_bill=array();
	
	$complex_positions=$_POST['complex_positions'];
	$except_users=$_POST['except_users'];
	
 
	$_kpg=new Tender_UsersSGroup;
	
 	$dec=new DBDecorator;
	
	//менеджеры ДПИМ
	//$dec->AddEntry(new SqlEntry('p.main_department_id', 1, SqlEntry::E));
	//$dec->AddEntry(new SqlEntry('p.position_id', NULL, SqlEntry::IN_VALUES, NULL,array(33,37)));
	
	//сотрудники отдела: 
	if(!$au->user_rights->CheckAccess('w',1149)){
		$dec->AddEntry(new SqlEntry('p.department_id',$result['department_id'], SqlEntry::E));
	}
	
	$alls=$_kpg->GetItemsForBill($dec);  
	 
  
	/*echo '<pre>';
	print_r(($alls));
	echo '</pre>';*/
	 
	 
	foreach($alls as $kk=>$v){
				  
	   
		  
		  $v['is_in']=1;
		  
		  $v['hash']=md5($v['user_id']);
		  
		 // print_r($v);
		  
		  //$alls[$k]=$v;
		  $arr[]=$v;
		
	}
	
	$sm=new SmartyAj;
	 
	$sm->assign('pospos',$arr);
	 
	 
	
 
	
	$ret.=$sm->fetch("tender/managers_set1.html");
	
	  
   

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
	
	$_hi=new tender_HistoryItem; $_hg=new tender_HistoryGroup; $_dsi=new DocStatusItem; 
	$_file=new tender_HistoryFileItem;
	
	$_sch=new tender_Item;
	$sch=$_sch->GetItemById($id);
	$count_hi=$_hg->CountHistory($id);
	
	
	$params=array();
	$params['sched_id']=$id;
	$params['txt']=SecStr(iconv("utf-8","windows-1251",$_POST['comment']));
	$params['user_id']=$result['id'];
	$params['pdate']=time();
	
	$code=$_hi->Add($params);
	
	$log->PutEntry($result['id'],'добавлен комментарий к тендеру', NULL,931,NULL, $params['txt'],$id);
	
	
	$files_server=$_POST['files_server'];
	$files_client=$_POST['files_client'];
	
	foreach($files_server as $k=>$file_server){
		$file_id=$_file->Add(array(
			'history_id'=>$code,
			'filename'=>SecStr(iconv("utf-8","windows-1251",$file_server)),
			'orig_name'=>SecStr(iconv("utf-8","windows-1251",$files_client[$k])),
		));	
		
		$log->PutEntry($result['id'],'прикреплен файл к комментарию  к тендеру', NULL,931,NULL, 'Комментарий '.$params['txt'].',  файл '.SecStr(iconv("utf-8","windows-1251",$files_client[$k])),$id);
		 
		
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
					  
			$log->PutEntry($result['id'],'начал выполнение тендера',NULL,931, NULL, NULL,$id);
			
			$stat=$_dsi->GetItemById(24);
			$log->PutEntry($result['id'],'смена статуса тендера',NULL,931,NULL,'установлен статус '.$stat['name'],$id);	
		
	}
	
	
	//вывести что получилось
	$_hr=new tender_HistoryGroup;
	
	$dec=new DBDecorator();
	$dec->AddEntry(new SqlEntry('o.id',$code, SqlEntry::E));
	
	$ret=$_hr->ShowHistory($id, 'tender/lenta.html', $dec, true, false, true,  $result,
			 $au->user_rights->CheckAccess('w',932),
			 $au->user_rights->CheckAccess('w',933));
			 
			 
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="edit_comment")){
	$id=abs((int)$_POST['id']);
	$comment_id=abs((int)$_POST['comment_id']);
	
	$_hi=new tender_HistoryItem;
	$_file=new tender_HistoryFileItem;
	
	$params=array();
	//$params['sched_id']=$id;
	$params['txt']=SecStr(iconv("utf-8","windows-1251",$_POST['comment']));
	//$params['user_id']=$result['id'];
	//$params['pdate']=time();
	
	 $_hi->Edit($comment_id, $params);
	
	$log->PutEntry($result['id'],'редактирован комментарий к тендеру', NULL,931,NULL, $params['txt'],$id);
	
	
	$files_server=$_POST['files_server'];
	$files_client=$_POST['files_client'];
	
	foreach($files_server as $k=>$file_server){
		$file_id=$_file->Add(array(
			'history_id'=>$comment_id,
			'filename'=>SecStr(iconv("utf-8","windows-1251",$file_server)),
			'orig_name'=>SecStr(iconv("utf-8","windows-1251",$files_client[$k])),
		));	
		
		$log->PutEntry($result['id'],'прикреплен файл к комментарию к тендеру', NULL,931,NULL, 'Комментарий '.$params['txt'].',  файл '.SecStr(iconv("utf-8","windows-1251",$files_client[$k])),$id);
		
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
	$_hr=new tender_HistoryGroup;
	
	$dec=new DBDecorator();
	$dec->AddEntry(new SqlEntry('o.id',$comment_id, SqlEntry::E));
	
	$ret=$_hr->ShowHistory($id, 'tender/lenta.html', $dec, true, false, true,  $result,
			 $au->user_rights->CheckAccess('w',932),
			 $au->user_rights->CheckAccess('w',933));
	
}

elseif(isset($_POST['action'])&&($_POST['action']=="toggle_comment")){
	$id=abs((int)$_POST['id']);
	$comment_id=abs((int)$_POST['comment_id']);
	
	$_hi=new tender_HistoryItem;
	$hi=$_hi->GetItemById($comment_id);
	
	if($hi['is_shown']==1){
		$_hi->Edit($comment_id, array('is_shown'=>0));
		$log->PutEntry($result['id'],'скрыт комментарий к тендеру', NULL,931,NULL, 'Комментарий '.$hi['txt'].' ',$id);
		$ret=0;
	}else{
		$_hi->Edit($comment_id, array('is_shown'=>1));
		$log->PutEntry($result['id'],'показан комментарий к тендеру', NULL,931,NULL, 'Комментарий '.$hi['txt'].' ',$id);
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
	$ret=$sm->fetch('tender/pdf_addresses.html');

}

elseif(isset($_POST['action'])&&($_POST['action']=="has_files")){
//есть ли файлы по записи план-ка?
	$count_of_files=0;	
	$id=abs((int)$_POST['id']);
	
	$sql='select count(*) from tender_file where bill_id="'.$id.'" ';
	
	$set=new mysqlset($sql);
	$rs=$set->GetResult();
	
	$f=mysqli_fetch_array($rs);
	
	$count_of_files+=(int)$f[0];
	
	
	$sql='select count(*) from tender_history_file where history_id in(select id from tender_history where sched_id="'.$id.'" )';
	
	
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
	
	$opg=new Tender_EqTypeGroup;
	$sm->assign('opfs_total', $opg->GetItemsArr());
	
	$ret=$sm->fetch('tender/d_types.html');
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="redraw_types_page")){
	//$sm=new SmartyAj;
	
	if(isset($_POST['user_id'])) $user_id=abs((int)$_POST['user_id']);
	else $user_id=0;
	
	$opg=new Tender_EqTypeGroup;
	$ret=$opg->GetItemsOpt($user_id);
	
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="add_type")){
	
	//dostup
	/*if(!$au->user_rights->CheckAccess('w',19)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	*/
	$qi=new Tender_EqTypeItem;
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
	
	$qi=new Tender_EqTypeItem;
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
	
	$qi=new Tender_EqTypeItem;
	
	$id=abs((int)$_POST['id']);
	$qi->Del($id);
	
	//$log->PutEntry($result['id'],'удалил ОПФ',NULL,19,NULL,$params['name']);
 

}elseif(isset($_POST['action'])&&($_POST['action']=="fail_unconfirm_price")){
	
	 
	
	 
	
	$id=abs((int)$_POST['id']);
	$_ti=new Tender_Item;
	$ti=$_ti->getitembyid($id);
	 
	$log->PutEntry($result['id'],'отказ снять утверждение заполнения тендера',NULL,962, NULL, 'Попытка снять утверждение заполнения тендера № '.$ti['code'].': в действии отказано, причина - недостаточно прав',$id);	
	 
}


//РАБОТА С ФЗ
elseif(isset($_POST['action'])&&($_POST['action']=="redraw_fzs_dics")){
	$sm=new SmartyAj;
	if(isset($_POST['user_id'])) $user_id=abs((int)$_POST['user_id']);
	else $user_id=0;
	
	$opg=new Tender_FZGroup;
	$sm->assign('fzs_total', $opg->GetItemsArr());
	
	$ret=$sm->fetch('tender/fzs.html');
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="redraw_fzs_page")){
	//$sm=new SmartyAj;
	
	if(isset($_POST['user_id'])) $user_id=abs((int)$_POST['user_id']);
	else $user_id=0;
	
	$opg=new Tender_FZGroup;
	$ret=$opg->GetItemsOpt($user_id);
	
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="add_fz")){
	
	//dostup
	/*if(!$au->user_rights->CheckAccess('w',19)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	*/
	$qi=new Tender_FZItem;
	$params=array();
	$params['name']=SecStr(iconv("utf-8","windows-1251",$_POST['fz']),9);
	$qi->Add($params);
	
	//$log->PutEntry($result['id'],'добавил ОПФ',NULL,19,NULL,$params['name']);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="edit_fz")){
	/*if(!$au->user_rights->CheckAccess('w',19)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}*/
	
	$qi=new Tender_FZItem;
	$params=array();
	$id=abs((int)$_POST['id']);
	$params['name']=SecStr(iconv("utf-8","windows-1251",$_POST['question']),9);
	$qi->Edit($id,$params);	
	
	//$log->PutEntry($result['id'],'редактировал ОПФ',NULL,19,NULL,$params['name']);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="delete_fz")){
	
	/*if(!$au->user_rights->CheckAccess('w',13)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}*/
	
	$qi=new Tender_FZItem;
	
	$id=abs((int)$_POST['id']);
	$qi->Del($id);
	
	//$log->PutEntry($result['id'],'удалил ОПФ',NULL,19,NULL,$params['name']);
 

}
//будет ли доступ к тендеру при указанном менеджере указанному сотруднику
elseif(isset($_POST['action'])&&($_POST['action']=="scan_available_by_user_id")){
	$_tg=new Tender_Group;
	
	$manager_id=abs((int)$_POST['manager_id']);
	
	$res=$_tg->ScanAvailableByUserId($manager_id, $result['id']);
	if($res) $ret=1;
	else $ret=0;
	
	 
}

elseif(isset($_POST['action'])&&($_POST['action']=="check_status2326")){
		$id=abs((int)$_POST['id']);
		
	    
		$_dem=new Tender_Item;
		$dem=$_dem->Getitembyid($id);
		
		 
		
		if(!$_dem->DocCanPutStatus2326($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
	




}

elseif(isset($_POST['action'])&&($_POST['action']=="scan_leads")){
		//выбор лидов для возможного прикрепления
		
		$tender_id=abs((int)$_POST['tender_id']);
		
	    
		$_tsup=new Tender_SupplierGroup;
		$suppliers=$_tsup->GetItemsByIdArr($tender_id);
		//print_r($suppliers);
		$supplier_ids=array(); foreach($suppliers as $k=>$v) if(!in_array($v['supplier_id'], $supplier_ids)) $supplier_ids[]=$v['supplier_id'];
		
		$decorator=new DBDecorator;
		$_plans=new Lead_Group;
		
		if(!$au->user_rights->CheckAccess('w',953)){
			$decorator->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::IN_VALUES, NULL,$_plans->GetAvailableLeadIds($result['id'])));	
		}
		$decorator->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::IN_VALUES, NULL,array(28,35)));	
		
		
		//не тендерный
		$decorator->AddEntry(new SqlEntry('p.kind_id', 3, SqlEntry::NE));	
		
		
		//фильтр по контрагенту
		$decorator->AddEntry(new SqlEntry('sup.id', NULL, SqlEntry::IN_VALUES, NULL,$supplier_ids));
		 
		//сортировка 
		$decorator->AddEntry(new SqlOrdEntry('s.weight',SqlOrdEntry::DESC));
				
		$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::DESC));
		
		$docs1=$_plans->ShowPos(
		

			'tender/join_lead_table.html',  //0
			 $decorator, //1
			  $au->user_rights->CheckAccess('w',949), //2
			  $au->user_rights->CheckAccess('w',950), //3
			  0, //4
			  10000,  //5
			  true, //6
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
		
		$ret=$docs1;




}


elseif(isset($_POST['action'])&&($_POST['action']=="compare_fields_leads")){
		//сравнение отдельных полей лида и тендера
		
		$tender_id=abs((int)$_POST['tender_id']);
		
		$lead_id=abs((int)$_POST['tender_id']);
		
		$_tender=new Tender_Item; $_lead=new Lead_Item;
		$tender=$_tender->GetItemById($tender_id); $lead=$_lead->GetItemById($lead_id);
		
		
		$sm1=new SmartyAj;
		
		//валюты, виды оборудования
		
		//типы оборудования 
		$_eqs=new Tender_EqTypeGroup;
		$eqs=$_eqs->GetItemsArr();
		$_ids=array(); $_vals=array();
		$_ids[]=0; $_vals[]='-выберите-';
		foreach($eqs as $k=>$v){
			$_ids[]=$v['id'];
			$_vals[]=$v['name'];
		}
		$sm1->assign('eq_ids', $_ids); $sm1->assign('eq_vals', $_vals);
		 $_tki=new Tender_EqTypeItem;
		$tki= $_tki->GetItemById($tender['eq_type_id']);
		$tender['eq_name']=$tki['name'];
		$tki= $_tki->GetItemById($lead['eq_type_id']);
		$lead['eq_name']=$tki['name'];
		
		
		//валюты
		$_curr=new PlCurrGroup;
		//$sm1->assign('currs', $_curr->GetItemsArr());
		$_curr=new PlCurrItem;
		$curr=$_curr->GetItemById($tender['currency_id']);
		$tender['curr_name']=$curr['signature'];
		$curr=$_curr->GetItemById($lead['currency_id']);
		$lead['curr_name']=$curr['signature'];
		
		$tender['max_price_f']=number_format($tender['max_price'],2,'.',' ');  
		if($lead['max_price']!="") $lead['max_price_f']=number_format($lead['max_price'],2,'.',' '); else $lead['max_price_f']='-'; 
		
		$sm1->assign('tender', $tender); $sm1->assign('lead', $lead); 
		
		$ret=$sm1->fetch('tender/join_lead_fields.html');
		
}

elseif(isset($_POST['action'])&&($_POST['action']=="join_lead")){
	
		
		$tender_id=abs((int)$_POST['tender_id']);
		
		$lead_id=abs((int)$_POST['lead_id']);
		
		$_tender=new Tender_Item; $_lead=new Lead_Item;
		$tender=$_tender->GetItemById($tender_id); $lead=$_lead->GetItemById($lead_id);
		
		$eq_type_id=$_POST['eq_type_id'];
		$max_price=$_POST['max_price'];
		
		$_curr=new PlCurrItem; $_tki=new Tender_EqTypeItem;
		
		$l_params=array();
		
		if($eq_type_id=='lead')
			$l_params['eq_type_id']=$lead['eq_type_id'];
		else
			$l_params['eq_type_id']=$tender['eq_type_id'];
			
		if($max_price=='lead'){
			$l_params['max_price']=$lead['max_price'];
			$l_params['currency_id']=$lead['currency_id'];
		}else{
			$l_params['max_price']=$tender['max_price'];
			$l_params['currency_id']=$tender['currency_id'];
		}
			
		//внести обычные поля
		$l_params['kind_id']=3;
		$l_params['tender_id']=$tender_id;
		
		//уравнять ответственного
		$l_params['manager_id']=$tender['manager_id'];
		
		//дата лида
		$l_params['pdate_finish']=date('Y-m-d',DateFromdmY(datefromymd($tender['pdate_claiming']))-7*24*60*60);
		
		$_lead->Edit($lead_id, $l_params, true,  $result);
		
		$log->PutEntry($result['id'],'прикрепление лида к тендеру',NULL,950, NULL, 'лид '.$lead['code'].' прикреплен к тендеру '.$tender['code'].', сотрудник подтвердил корректность данных',$lead_id);
		$log->PutEntry($result['id'],'прикрепление лида к тендеру',NULL,931, NULL, 'лид '.$lead['code'].' прикреплен к тендеру '.$tender['code'].', сотрудник подтвердил корректность данных',$tender_id);
		
		//все внести в журнал событий
		foreach($l_params as $k=>$v){
			if(addslashes($lead[$k])!=$v){
				
				if($k=='kind_id'){
					$log->PutEntry($result['id'],'редактировал лид',NULL,950, NULL, 'в поле Вид лида установлено значение Тендерный',$lead_id);
					continue;	
				}
				
				if($k=='manager_id'){
					$_ui=new UserSItem;
					$user=$_ui->GetItemById($v);
					$log->PutEntry($result['id'],'редактировал лид',NULL,950, NULL, 'в поле Ответственный сотрудник установлено значение '.$user['name_s'],$lead_id);
					continue;	
				}
				
				if($k=='currency_id'){
					 
					$cuur=$_curr->GetItemById($v);
					$log->PutEntry($result['id'],'редактировал лид',NULL,950, NULL, 'в поле Макс. цена контр-та установлено значение '.$l_params['max_price'].' '.$curr['signature'],$lead_id);
					continue;	
				}
				
				$log->PutEntry($result['id'],'редактировал лид',NULL,950, NULL, 'в поле '.$k.' установлено значение '.$v,$lead_id);
				
						
			}	
		}
		
		//завести комментарий в лид и тендер
		$_lhi=new Lead_HistoryItem;
		$_lhi->Add(array(
			'sched_id'=>$lead_id,
			'pdate'=>time(),
			'user_id'=>0,
			'txt'=>SecStr('<div>Автоматический комментарий: Лид '.$lead['code'].' прикреплен к тендеру '.$tender['code'].', сотрудник '.$result['name_s'].' подтвердил корректность данных.</div>')
		));
		
		$_thi=new Tender_HistoryItem;
		$_thi->Add(array(
			'sched_id'=>$tender_id,
			'pdate'=>time(),
			'user_id'=>0,
			'txt'=>SecStr('<div>Автоматический комментарий: Лид '.$lead['code'].' прикреплен к тендеру '.$tender['code'].', сотрудник '.$result['name_s'].' подтвердил корректность данных.</div>')
		));
		
		if($lead['status_id']==35){
			//лид на рассмотрении - тендер - в ждет контроля 26
			$_tender->Edit($tender_id, array('status_id'=>26), true, $result);
			$_thi=new Tender_HistoryItem;
			$_thi->Add(array(
				'sched_id'=>$tender_id,
				'pdate'=>time(),
				'user_id'=>0,
				'txt'=>SecStr('<div>Автоматический комментарий: Тендер переведен в статус "Ждет контроля при прикреплении к нему лида '.$lead['code'].' в статусе "На рассмотрении" сотрудником '.$result['name_s'].'.</div>')
			));
			
			
				$log->PutEntry($result['id'],'прикрепление лида к тендеру',NULL,931, NULL, SecStr('Тендер переведен в статус "Ждет контроля" при прикреплении к нему лида '.$lead['code'].' в статусе "На рассмотрении" сотрудником '.$result['name_s'].'.'),$tender_id);
		}
	
}


//настройка реестра
elseif(isset($_POST['action'])&&(($_POST['action']=="mode_reestr"))){
	$_views=new Tender_ViewsGroup;
	$_view=new Tender_ViewsItem;
	
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
	$_views=new Tender_ViewsGroup;
	$_view=new Tender_ViewsItem;
	
	 
	
	$_views->Clear($result['id']);
	 


}elseif(isset($_POST['action'])&&($_POST['action']=="check_docs_unconfirm_ship")){
	 	//проверка связанных документов
		$id=abs((int)$_POST['id']);
		
	
		
	  
		$_dem=new Tender_AbstractItem;
		$dem=$_dem->Getitembyid($id);
		
		$_res=new Tender_Resolver($dem['kind_id']);
		
		
		$_ki=$_res->instance;
		
		
		
		if($_ki->DocUncomfirmShipDocs($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
}elseif(isset($_POST['action'])&&($_POST['action']=="check_docs_unconfirm_fulfil")){
	 	//проверка связанных документов
		$id=abs((int)$_POST['id']);
		
	
		
	  
		$_dem=new Tender_AbstractItem;
		$dem=$_dem->Getitembyid($id);
		
		$_res=new Tender_Resolver($dem['kind_id']);
		
		
		$_ki=$_res->instance;
		
		
		
		if($_ki->DocUncomfirmFulfilDocs($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
}





//РАБОТА С видами тендеров
elseif(isset($_POST['action'])&&($_POST['action']=="redraw_kinds_dics")){
	$sm=new SmartyAj;
	if(isset($_POST['user_id'])) $user_id=abs((int)$_POST['user_id']);
	else $user_id=0;
	
	$opg=new TenderKindGroup;
	$sm->assign('kinds_total', $opg->GetItemsList($au->user_rights->CheckAccess('w',1146), $au->user_rights->CheckAccess('w',1147)));
	
	
	$ret=$sm->fetch('tender/kinds.html');
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="redraw_kinds_page")){
	//$sm=new SmartyAj;
	
	if(isset($_POST['user_id'])) $user_id=abs((int)$_POST['user_id']);
	else $user_id=0;
	
	$opg=new TenderKindGroup;
	$ret=$opg->GetItemsOpt($user_id);
	
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="add_kind")){
	
	//dostup
	/*if(!$au->user_rights->CheckAccess('w',19)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	*/
	$qi=new TenderKindItem;
	$params=array();
	$params['name']=SecStr(iconv("utf-8","windows-1251",$_POST['kind']),9);
	$qi->Add($params);
	
	//$log->PutEntry($result['id'],'добавил ОПФ',NULL,19,NULL,$params['name']);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="edit_kind")){
	/*if(!$au->user_rights->CheckAccess('w',19)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}*/
	
	$qi=new TenderKindItem;
	$params=array();
	$id=abs((int)$_POST['id']);
	$params['name']=SecStr(iconv("utf-8","windows-1251",$_POST['question']),9);
	$qi->Edit($id,$params);	
	
	//$log->PutEntry($result['id'],'редактировал ОПФ',NULL,19,NULL,$params['name']);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="delete_kind")){
	
	/*if(!$au->user_rights->CheckAccess('w',13)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}*/
	
	$qi=new TenderKindItem;
	
	$id=abs((int)$_POST['id']);
	$qi->Del($id);
	
	//$log->PutEntry($result['id'],'удалил ОПФ',NULL,19,NULL,$params['name']);
 

}







//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>