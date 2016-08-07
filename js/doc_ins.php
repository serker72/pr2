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

require_once('../classes/doc_in.class.php');

require_once('../classes/lead.class.php'); 
 
 
require_once('../classes/doc_in_history_fileitem.php');
require_once('../classes/doc_in_history_item.php');
require_once('../classes/doc_in_history_group.php');

require_once('../classes/filecontents.php');
 
require_once('../classes/quick_suppliers_group.php'); 

require_once('../classes/supplieritem.php'); 
require_once('../classes/supplier_responsible_user_group.php');

require_once('../classes/doc_in_view.class.php');

require_once('../classes/doc_in_filegroup.php');
require_once('../classes/doc_in_fileitem.php');
 

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
 		
	if($_POST['action']=="find_suppliers_ship") $ret=$_pg->GetItemsForBill('doc_in/ship_suppliers_list.html',  $dec,true,$all7,$result, 0, (strlen(SecStr(iconv("utf-8","windows-1251",$_POST['contact'])))>0)); 
	elseif($_POST['action']=="find_many_suppliers") $ret=$_pg->GetItemsForBill('doc_in/suppliers_many_list.html',  $dec,true,$all7,$result, 0, (strlen(SecStr(iconv("utf-8","windows-1251",$_POST['contact'])))>0));  
	elseif($_POST['action']=="find_many_suppliers_15") $ret=$_pg->GetItemsForBill('doc_in/suppliers_15_list.html',  $dec,true,$all7,$result, 0, (strlen(SecStr(iconv("utf-8","windows-1251",$_POST['contact'])))>0));  
	else $ret=$_pg->GetItemsForBill('doc_in/suppliers_list.html',  $dec,true,$all7,$result, 0, (strlen(SecStr(iconv("utf-8","windows-1251",$_POST['contact'])))>0)); 
	
	
	 
	
	

	
} 
elseif(isset($_POST['action'])&&(($_POST['action']=="retrieve_contacts")||($_POST['action']=="retrieve_only_contacts"))){
	$_sc=new DocIn_SupplierContactGroup;
	
	
	$supplier_id=abs((int)$_POST['supplier_id']);
	$current_id=abs((int)$_POST['current_id']);
	$current_k_id=abs((int)$_POST['current_k_id']);
	
	
	
	
	$alls=$_sc->GetItemsByIdArr($supplier_id,$current_id, $current_k_id); 
	$sm=new SmartyAj;
	
	
	$sm->assign('supplier_id', $supplier_id);
	$sm->assign('items', $alls);
	
	if($_POST['action']=="retrieve_only_contacts") $ret=$sm->fetch('doc_in/suppliers_only_contacts.html');
	else $ret=$sm->fetch('doc_in/suppliers_contacts.html');

}
  

elseif(isset($_POST['action'])&&($_POST['action']=="check_confirm")){
		$id=abs((int)$_POST['id']);
		
	    
		$_dem=new DocIn_AbstractItem;
		$dem=$_dem->Getitembyid($id);
		
		$_res=new DocIn_Resolver($dem['kind_id']);
		
		
		$_ki=$_res->instance;
		
		
		if(!$_ki->DocCanConfirmShip($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
	



	
}elseif(isset($_POST['action'])&&($_POST['action']=="check_unconfirm")){
	//проверить, есть ли заявки с таким номером для такого уч.
	
		$id=abs((int)$_POST['id']);
		
	
		
	  
		$_dem=new DocIn_AbstractItem;
		$dem=$_dem->Getitembyid($id);
		
		$_res=new DocIn_Resolver($dem['kind_id']);
		
		
		$_ki=$_res->instance;
		
		
		
		if(!$_ki->DocCanUnconfirmShip($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
}elseif(isset($_POST['action'])&&($_POST['action']=="check_confirm")){
		$id=abs((int)$_POST['id']);
		
	
		
	  
		$_dem=new DocIn_AbstractItem;
		$dem=$_dem->Getitembyid($id);
		
		$_res=new DocIn_Resolver($dem['kind_id']);
		
		
		$_ki=$_res->instance;
		
		
		if(!$_ki->DocCanConfirmShip($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
	



  
}


elseif(isset($_POST['action'])&&($_POST['action']=="check_unconfirm_price")){
	
		$id=abs((int)$_POST['id']);
		
	
		
		  
		$_dem=new DocIn_AbstractItem;
		$dem=$_dem->Getitembyid($id);
		
		$_res=new DocIn_Resolver($dem['kind_id']);
		
		
		$_ki=$_res->instance;
		
		
		
		if(!$_ki->DocCanUnconfirmPrice($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
}elseif(isset($_POST['action'])&&($_POST['action']=="check_confirm_price")){
		$id=abs((int)$_POST['id']);
		
	
		
		  
		$_dem=new DocIn_AbstractItem;
		$dem=$_dem->Getitembyid($id);
		
		$_res=new DocIn_Resolver($dem['kind_id']);
		
		
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
	
	$_ki=new DocIn_AbstractItem;
	
		$trust=$_ki->getitembyid($id);
		
	$_res=new DocIn_Resolver($trust['kind_id']);
	$_ti=$_res->instance;	
	
	$_si=new UserSItem;
	

	
	$log=new ActionLog;
	$_stat=new DocStatusItem;
	
	
	
	if(($trust['status_id']==18)&& ($trust['is_confirmed']==0)){
		//удаление	
		if($au->user_rights->CheckAccess('w',1080)){
			$_ti->Edit($id,array('status_id'=>3),false,$result);
			
			$stat=$_stat->GetItemById(3);
			$log->PutEntry($result['id'],'аннулирование вх. документа',NULL,1080,NULL,'вх. документ '.$trust['code'].': установлен статус '.$stat['name'],$id);	
			
			 
			 	 
			//внести примечание
			$_ni=new DocIn_HistoryItem;
			 
			$_ni->Add(array(
				'sched_id'=>$id,
				'user_id'=>0,
				'txt'=>'Автоматический комментарий: документ был аннулирован пользователем '.SecStr($result['name_s']).' , причина: '.$note,
				 
				'pdate'=>time()
					));	 
		}
	}elseif($trust['status_id']==3){
		//разудаление
		
		if($au->user_rights->CheckAccess('w',1086)){
			$_ti->Edit($id,array('status_id'=>18, 'restore_pdate'=>time()), false,$result);
			
			$stat=$_stat->GetItemById(18);
			$log->PutEntry($result['id'],'восстановление вх. документа',NULL,1086,NULL,'вх. документ № '.$trust['code'].': установлен статус '.$stat['name'],$id);
			
		 
		}
		
	}
	
	if($from_card==0){
	 $acg=$_res->group_instance;
	
	$shorter=abs((int)$_POST['shorter']);
	
	 $prefix=$trust['kind_id'];
	 
	$template='doc_in/table'.$prefix.'.html';
	 
	
	 $acg->setauthresult($result);
	
	$dec=new  DBDecorator;
	
	$dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	 
	
	
	$ret= $acg->ShowPos(
		

			$template,  //0
			 $dec, //1
			  $au->user_rights->CheckAccess('w',1079), //2
			   $au->user_rights->CheckAccess('w',1080), //3
			  0, //4
			  10000, //5
			  false, //6
			  true,  //7
			  $au->user_rights->CheckAccess('w',1085), //8
			  $au->user_rights->CheckAccess('w',1086),  //9
			  $au->user_rights->CheckAccess('w',1080), //10
			  $au->user_rights->CheckAccess('w',1087), //11
			  $au->user_rights->CheckAccess('w',1080), //12
			  $prefix //13
			 );
	  
	 
	}else{
		$editing_user=$_ti->getitembyid($id);
		$sm=new SmartyAj;
		
		
		
		//блок аннулирования
		
		$editing_user['can_annul']=$_res->instance->DocCanAnnul($editing_user['id'],$reason, NULL, $result)&&$au->user_rights->CheckAccess('w',1080);
		if(!$au->user_rights->CheckAccess('w',1080)) $reason='недостаточно прав для данной операции';
		$editing_user['can_annul_reason']=$reason;
		
		//$editing_user['binded_to_annul']=$_ti->GetBindedDocumentsToAnnul($editing_user['id']);
		
		
		$editing_user['can_restore']=$_res->instance->DocCanRestore($editing_user['id'],$reason)&&$au->user_rights->CheckAccess('w',1086);
			if(!$au->user_rights->CheckAccess('w',1086)) $reason='недостаточно прав для данной операции';
		
		$stat=$_stat->Getitembyid($editing_user['status_id']);
		$editing_user['status_name']=$stat['name'];
		
		$sm->assign('bill',$editing_user);
		$ret=$sm->fetch('doc_in/toggle_annul_card.html');		
	}
		
}

 

//utv- razutv
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_confirm_price")){
	$id=abs((int)$_POST['id']);
	 
	
		$_ki=new DocIn_AbstractItem;
	
		$trust=$_ki->getitembyid($id);
		
	$_res=new DocIn_Resolver($trust['kind_id']);
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
		if($au->user_rights->CheckAccess('w',1087)&&$_ti->DocCanUnconfirmPrice($id, $rss)){
			    
				$_ti->Edit($id,array('is_confirmed'=>0, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()), true,$result);
				
				$log->PutEntry($result['id'],'снял утверждение заполнения вх. документа',NULL,1087, NULL, NULL,$id);
				 
					
			 
		} 
		
	}else{
		//есть права
		if($au->user_rights->CheckAccess('w',1080)&&$_ti->DocCanConfirmPrice($id, $rss)){
			 
				$_ti->Edit($id,array('is_confirmed'=>1, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'утвердил заполнения вх. документа',NULL,1080, NULL, NULL,$id);	
				
			 	
			 
		} 
	}
	
	
	
	$acg=$_res->group_instance;
	
	$shorter=abs((int)$_POST['shorter']);
	
	 $prefix=$trust['kind_id'];
	 
	$template='doc_in/table'.$prefix.'.html';
	 
	
	 $acg->setauthresult($result);
	
	$dec=new  DBDecorator;
	
	$dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	 
	
	
	$ret= $acg->ShowPos(
		

			$template,  //0
			 $dec, //1
			  $au->user_rights->CheckAccess('w',1079), //2
			   $au->user_rights->CheckAccess('w',1080), //3
			  0, //4
			  10000, //5
			  false, //6
			  true,  //7
			  $au->user_rights->CheckAccess('w',1085), //8
			  $au->user_rights->CheckAccess('w',1086),  //9
			  $au->user_rights->CheckAccess('w',1080), //10
			  $au->user_rights->CheckAccess('w',1087), //11
			  $au->user_rights->CheckAccess('w',1080), //12
			  $prefix //13
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
		
		
			
		
		if($_POST['action']=="add_supplier_15") $ret=$sm->fetch('doc_in/suppliers_15_table.html');
		else $ret=$sm->fetch('doc_in/suppliers_many_table.html');
	}
}





//подгрузка пол-лей для выбора ответственного
elseif(isset($_POST['action'])&&($_POST['action']=="load_users")){
	
	/* $supplier_id=abs((int)$_POST['supplier_id']);
	 
	
	$_resp=new SupplierResponsibleUserGroup;
	$resp=$_resp->GetUsersArr($supplier_id, $resp_ids);
 
	$already_in_bill=array();
*/	
	$manager_id=abs((int)$_POST['manager_id']);
	$complex_positions=$_POST['complex_positions'];
	$except_users=$_POST['except_users'];
	
 
	$_kpg=new DocIn_UsersSGroup;
	
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
				  
	 
		  
		  
		 /* if(in_array($v['id'], $resp_ids)) $v['is_in']=1;
		  else $v['is_in']=0; 
		  */
		  
		  /*if(in_array($v['id'], $resp_ids)) $v['is_in']=1;
		  else continue;
		  */
		  
		  if($manager_id==$v['id'])  $v['is_in']=1;
		  
		  $v['hash']=md5($v['user_id']);
		  
		 // print_r($v);
		  
		  //$alls[$k]=$v;
		  $arr[]=$v;
		
	}
	
	$sm=new SmartyAj;
	 
	$sm->assign('pospos',$arr);
	 
	 
	
 
	
	$ret.=$sm->fetch("doc_in/managers_set.html");
	
	 
	
	
 

   

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
	
	$_hi=new DocIn_HistoryItem; $_hg=new DocIn_HistoryGroup; $_dsi=new DocStatusItem; 
	$_file=new DocIn_HistoryFileItem;
	
	$_sch=new DocIn_Item;
	$sch=$_sch->GetItemById($id);
	$count_hi=$_hg->CountHistory($id);
	
	
	$params=array();
	$params['sched_id']=$id;
	$params['txt']=SecStr(iconv("utf-8","windows-1251",$_POST['comment']));
	$params['user_id']=$result['id'];
	$params['pdate']=time();
	
	$code=$_hi->Add($params);
	
	$log->PutEntry($result['id'],'добавлен комментарий к входящему документу', NULL,1080,NULL, $params['txt'],$id);
	
	 
	$files_server=$_POST['files_server'];
	$files_client=$_POST['files_client'];
	
	foreach($files_server as $k=>$file_server){
		$file_id=$_file->Add(array(
			'history_id'=>$code,
			'filename'=>SecStr(iconv("utf-8","windows-1251",$file_server)),
			'orig_name'=>SecStr(iconv("utf-8","windows-1251",$files_client[$k])),
		));	
		
		$log->PutEntry($result['id'],'прикреплен файл к комментарию  к входящему документу', NULL,1080,NULL, 'Комментарий '.$params['txt'].',  файл '.SecStr(iconv("utf-8","windows-1251",$files_client[$k])),$id);
		 
		
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
	$_hr=new DocIn_HistoryGroup;
	
	$dec=new DBDecorator();
	$dec->AddEntry(new SqlEntry('o.id',$code, SqlEntry::E));
	
	$ret=$_hr->ShowHistory($id, 'doc_in/lenta.html', $dec, true, false, true,  $result,
			 $au->user_rights->CheckAccess('w',1081),
			 $au->user_rights->CheckAccess('w',1082));
			 
			 
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="edit_comment")){
	$id=abs((int)$_POST['id']);
	$comment_id=abs((int)$_POST['comment_id']);
	
	$_hi=new DocIn_HistoryItem;
	$_file=new DocIn_HistoryFileItem;
	
	$params=array();
	//$params['sched_id']=$id;
	$params['txt']=SecStr(iconv("utf-8","windows-1251",$_POST['comment']));
	//$params['user_id']=$result['id'];
	//$params['pdate']=time();
	
	 $_hi->Edit($comment_id, $params);
	
	$log->PutEntry($result['id'],'редактирован комментарий к входящему документу', NULL,1080,NULL, $params['txt'],$id);
	
	
	$files_server=$_POST['files_server'];
	$files_client=$_POST['files_client'];
	
	foreach($files_server as $k=>$file_server){
		$file_id=$_file->Add(array(
			'history_id'=>$comment_id,
			'filename'=>SecStr(iconv("utf-8","windows-1251",$file_server)),
			'orig_name'=>SecStr(iconv("utf-8","windows-1251",$files_client[$k])),
		));	
		
		$log->PutEntry($result['id'],'прикреплен файл к комментарию к входящему документу', NULL,1080,NULL, 'Комментарий '.$params['txt'].',  файл '.SecStr(iconv("utf-8","windows-1251",$files_client[$k])),$id);
		
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
	$_hr=new DocIn_HistoryGroup;
	
	$dec=new DBDecorator();
	$dec->AddEntry(new SqlEntry('o.id',$comment_id, SqlEntry::E));
	
	$ret=$_hr->ShowHistory($id, 'doc_in/lenta.html', $dec, true, false, true,  $result,
			 $au->user_rights->CheckAccess('w',1081),
			 $au->user_rights->CheckAccess('w',1082));
	
}

elseif(isset($_POST['action'])&&($_POST['action']=="toggle_comment")){
	$id=abs((int)$_POST['id']);
	$comment_id=abs((int)$_POST['comment_id']);
	
	$_hi=new DocIn_HistoryItem;
	$hi=$_hi->GetItemById($comment_id);
	
	if($hi['is_shown']==1){
		$_hi->Edit($comment_id, array('is_shown'=>0));
		$log->PutEntry($result['id'],'скрыт комментарий к входящему документу', NULL,1080,NULL, 'Комментарий '.$hi['txt'].' ',$id);
		$ret=0;
	}else{
		$_hi->Edit($comment_id, array('is_shown'=>1));
		$log->PutEntry($result['id'],'показан комментарий к входящему документу', NULL,1080,NULL, 'Комментарий '.$hi['txt'].' ',$id);
		$ret=1;
	}
	
}


elseif(isset($_POST['action'])&&($_POST['action']=="load_pdf_addresses")){
	
	$id=abs((int)$_POST['id']);
	
	$_item=new DocIn_AbstractItem;
	$item=$_item->GetItemById($id);
	
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
			where ( supplier_id in(select distinct supplier_id from doc_out_suppliers where  sched_id="'.$item['id'].'") )
			and id in(select distinct contact_id from supplier_contact_data where kind_id=5)
			)
		UNION ALL
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
	$ret=$sm->fetch('doc_in/pdf_addresses.html');

}


elseif(isset($_POST['action'])&&($_POST['action']=="load_pdf_filelist")){
	//список приложенных файлов для выбора для отправки
	$id=abs((int)$_POST['id']);
	
	
	$folder_id=0;
			 
	$decorator=new DBDecorator;
	
	$decorator->AddEntry(new SqlOrdEntry('pdate',SqlOrdEntry::DESC));
	
	$decorator->AddEntry(new UriEntry('id',$id));
	
	$decorator->AddEntry(new SqlEntry('folder_id',$folder_id, SqlEntry::E));
	$decorator->AddEntry(new UriEntry('folder_id',$folder_id));
	
	$navi_dec=new DBDecorator;
	//$navi_dec->AddEntry(new UriEntry('action',1));
	
	
	
	
	$ffg=new DocInFileGroup(1,  $id,  new FileDocFolderItem(1,  $id, new DocInFileItem(1)));;
			  
			  $filetext=$ffg->ShowFiles('doc_file/incard_list.html',  $decorator,0,1000,'ed_doc_in.php', 'doc_in_file.html', 'swfupl-js/doc_in_files.php',    
	false,    
	false, 
	false , 
	$folder_id, 
	false, 
	false , 
	false, 
	false ,    
	'',  
	
	false,  
	 $result, 
	 $navi_dec, 'file_', $alls 
	 );
	
	
	 
	//print_r($alls);
	 
		
	$sm=new SmartyAj;
	
	$sm->assign('items', $alls);
	$ret=$sm->fetch('doc_in/pdf_files.html');

}



elseif(isset($_POST['action'])&&($_POST['action']=="has_files")){
//есть ли файлы по записи план-ка?
	$count_of_files=0;	
	$id=abs((int)$_POST['id']);
	
	$sql='select count(*) from doc_in_file where bill_id="'.$id.'" ';
	
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
     

//настройка реестра
elseif(isset($_POST['action'])&&(($_POST['action']=="mode_reestr"))){
	$_views=new DocIn_ViewsGroup1;
	$_view=new DocIn_ViewsItem1;
	
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
	$_views=new DocIn_ViewsGroup1;
	$_view=new DocIn_ViewsItem1;
	
	 
	
	$_views->Clear($result['id']);
	 
 

}


 

/****************************************************************************************************/
//poisk i podtanovka lidov
elseif(isset($_POST['action'])&&($_POST['action']=="find_many_leads")){
	
	$_plans=new Lead_Group;
	$_plans->SetAuthResult($result);
	
	$decorator=new DBDecorator;
	$prefix='';	
		
	//контроль видимости
	if(!$au->user_rights->CheckAccess('w',953)){
		$decorator->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::IN_VALUES, NULL,$_plans->GetAvailableLeadIds($result['id'])));	
	}
	
	 if(isset($_POST['code'.$prefix])&&(strlen($_POST['code'.$prefix])>0)){
		  $decorator->AddEntry(new SqlEntry('p.code',SecStr(iconv('utf-8', 'windows-1251', $_POST['code'.$prefix])), SqlEntry::LIKE));
		  $decorator->AddEntry(new UriEntry('code',$_POST['code'.$prefix]));
		}
	
	if(!isset($_POST['pdate1'.$prefix])){
	
				$_given_pdate1=DateFromdmY('01.01.2015'); //DateFromdmY(date("d.m.Y"))-3*60*60*24*30;
				$given_pdate1=date("d.m.Y", $_given_pdate1);//"01.01.2006";
				
			
		}else{
			 $given_pdate1 = $_POST['pdate1'.$prefix];
			 $_given_pdate1= DateFromdmY($_POST['pdate1'.$prefix]);
		}
		
		
		
		if(!isset($_POST['pdate2'.$prefix])){
				
				$_given_pdate2=DateFromdmY(date("d.m.Y"))+30*60*60*24;
				$given_pdate2=date("d.m.Y", $_given_pdate2);//"01.01.2006";	
				
				//$decorator->AddEntry(new UriEntry('pdate2',$given_pdate2));
		}else{
			 $given_pdate2 = $_POST['pdate2'.$prefix];
			  $_given_pdate2= DateFromdmY($_POST['pdate2'.$prefix]);
		}
		
		
		
		if(isset($_POST['pdate1'.$prefix])&&isset($_POST['pdate2'.$prefix])&&($_POST['pdate2'.$prefix]!="")&&($_POST['pdate2'.$prefix]!="-")&&($_POST['pdate1'.$prefix]!="")&&($_POST['pdate1'.$prefix]!="-")){
			
			$decorator->AddEntry(new UriEntry('pdate1',$given_pdate1));
			$decorator->AddEntry(new UriEntry('pdate2',$given_pdate2));
			$decorator->AddEntry(new SqlEntry('p.pdate_finish',date('Y-m-d', DateFromdmY($given_pdate1)), SqlEntry::BETWEEN,date('Y-m-d', DateFromdmY($given_pdate2))));
		}else{
					$decorator->AddEntry(new UriEntry('pdate1',''));
				$decorator->AddEntry(new UriEntry('pdate1',''));
		} 
		
		
		if(isset($_POST['state_name'.$prefix])&&(strlen($_POST['state_name'.$prefix])>0)){
			//$decorator->AddEntry(new SqlEntry('p.producer_id',abs((int)$_POST['prod_name'.$prefix]), SqlEntry::E));
			$state_name=abs((int)$_POST['state_name'.$prefix]);
			if($state_name==0){
				$decorator->AddEntry(new SqlEntry('p.probability',0, SqlEntry::BETWEEN,29.99));
			}elseif($state_name==1){
				$decorator->AddEntry(new SqlEntry('p.probability',30, SqlEntry::BETWEEN,69.99));
			}elseif($state_name==2){
				$decorator->AddEntry(new SqlEntry('p.probability',70, SqlEntry::GE));
			}
			
			$decorator->AddEntry(new UriEntry('state_name',$_POST['state_name'.$prefix]));
		} 
		
		
		if(isset($_POST['kind_name'.$prefix])&&(strlen($_POST['kind_name'.$prefix])>0)){
			$decorator->AddEntry(new SqlEntry('p.kind_id',abs((int)$_POST['kind_name'.$prefix]), SqlEntry::E));
			$decorator->AddEntry(new UriEntry('kind_name',$_POST['kind_name'.$prefix]));
		} 
		
		
		if(isset($_POST['eq_name'.$prefix])&&(strlen($_POST['eq_name'.$prefix])>0)){
			$decorator->AddEntry(new SqlEntry('p.eq_type_id',abs((int)$_POST['eq_name'.$prefix]), SqlEntry::E));
			$decorator->AddEntry(new UriEntry('eq_name',$_POST['eq_name'.$prefix]));
		}  
		
		
		if(isset($_POST['topic'.$prefix])&&(strlen($_POST['topic'.$prefix])>0)){
		  $decorator->AddEntry(new SqlEntry('p.topic',SecStr(iconv('utf-8', 'windows-1251', $_POST['topic'.$prefix])), SqlEntry::LIKE));
		  $decorator->AddEntry(new UriEntry('topic',$_POST['topic'.$prefix]));
		}
		
		//фильтр по контрагенту
		if(isset($_POST['supplier_name'.$prefix])&&(strlen($_POST['supplier_name'.$prefix])>0)){
			$names=explode(';', trim($_POST['supplier_name'.$prefix]));
			foreach($names as $k=>$v) $names[$k]=SecStr(iconv('utf-8', 'windows-1251', $v));
			
			$decorator->AddEntry(new SqlEntry('sup.full_name', NULL, SqlEntry::LIKE_SET, NULL,$names));	
			
			$decorator->AddEntry(new UriEntry('supplier_name',$_POST['supplier_name'.$prefix]));
		}
		
		
		if(isset($_POST['prod_name'.$prefix])&&(strlen($_POST['prod_name'.$prefix])>0)){
			$decorator->AddEntry(new SqlEntry('p.producer_id',abs((int)$_POST['prod_name'.$prefix]), SqlEntry::E));
			$decorator->AddEntry(new UriEntry('prod_name',$_POST['prod_name'.$prefix]));
		} 
		
		
		if(isset($_POST['manager_name'.$prefix])&&(strlen($_POST['manager_name'.$prefix])>0)){
			$decorator->AddEntry(new SqlEntry('u.name_s',SecStr(iconv('utf-8', 'windows-1251', $_POST['manager_name'.$prefix])), SqlEntry::LIKE));
			$decorator->AddEntry(new UriEntry('manager_name',$_POST['manager_name'.$prefix]));
		} 
		
		$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::DESC));
		
		
		
		$ret=$_plans->ShowPos(
		

			'doc_out/lead_list.html',  //0
			 $decorator, //1
			  $au->user_rights->CheckAccess('w',949), //2
			  $au->user_rights->CheckAccess('w',950), //3
			  0, //4
			  1000, //5
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

		
	
}
//подгрузить состояния, виды, типы
elseif(isset($_GET['action'])&&($_GET['action']=="load_eq_types")){
	
 //типы оборудования 
		$_eqs=new Lead_EqTypeGroup;
		$eqs1=$_eqs->GetItemsArr();
		$eqs[]=array('id'=>'', 'name'=>iconv('windows-1251', 'utf-8','-все-'));
		foreach($eqs1 as $k=>$v){
			 $eqs[]=array('id'=>$v['id'], 'name'=>iconv('windows-1251', 'utf-8', $v['name']));
			 
		}
		
		//print_r($eqs);
		
		$ret=json_encode($eqs);
		
}
elseif(isset($_GET['action'])&&($_GET['action']=="load_prod_names")){
		
		//производители
		$_prods=new PlProdGroup;
		$prods1=$_prods->GetItemsArr();
		$prods[]=array('id'=>'', 'name'=>iconv('windows-1251', 'utf-8','-все-'));
		$prods[]=array('id'=>'0', 'name'=>iconv('windows-1251', 'utf-8','-не в линейке-'));
		foreach($prods1 as $k=>$v) $prods[]=array('id'=>$v['id'], 'name'=>iconv('windows-1251', 'utf-8', $v['name']));
		
		$ret=json_encode($prods);
}
elseif(isset($_GET['action'])&&($_GET['action']=="load_states")){		
		//состояния
		$states=array();
		$states[]=array('id'=>'', 'name'=>iconv('windows-1251', 'utf-8','-все-'));
		$states[]=array('id'=>'0', 'name'=>iconv('windows-1251', 'utf-8','холодный'));
		$states[]=array('id'=>'1', 'name'=>iconv('windows-1251', 'utf-8','теплый'));
		$states[]=array('id'=>'2', 'name'=>iconv('windows-1251', 'utf-8','горячий'));
		
		$ret=json_encode($states);

}

elseif(isset($_GET['action'])&&($_GET['action']=="load_kinds")){	
		$_tks=new LeadKindGroup;
		
		$tks1=$_tks->GetItemsArr();
		$tks[]=array('id'=>'', 'name'=>iconv('windows-1251', 'utf-8','-все-'));
		foreach($tks1 as $k=>$v) $tks[]=array('id'=>$v['id'], 'name'=>iconv('windows-1251', 'utf-8', $v['name']));
		$ret=json_encode($tks);
}


elseif(isset($_GET['action'])&&($_GET['action']=="retrieve_lead")){
	$_si=new Lead_Item;
	
	$si=$_si->GetItemById(abs((int)$_GET['id']));
	
 
	
	if($si!==false){
		
		foreach($si as $k=>$v) $si[$k]=iconv('windows-1251', 'utf-8', $v);
		 
		
		$ret=json_encode($si);
		//$ret='{'.implode(', ',$rret).'}';
	}
	
}

//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>