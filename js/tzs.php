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

require_once('../classes/lead.class.php');

require_once('../classes/tender.class.php');
require_once('../classes/lead_history_fileitem.php');
require_once('../classes/lead_history_item.php');
require_once('../classes/lead_history_group.php');

require_once('../classes/filecontents.php');
 
require_once('../classes/quick_suppliers_group.php'); 

require_once('../classes/supplieritem.php'); 
require_once('../classes/supplier_responsible_user_group.php');

require_once('../classes/tz.class.php');


require_once('../classes/tz_view.class.php');
require_once('../classes/pl_prodgroup.php');
require_once('../classes/pl_posgroup.php');

 

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
  

 if(isset($_POST['action'])&&($_POST['action']=="check_unconfirm_price")){
	
		$id=abs((int)$_POST['id']);
		
	
		
		  
		$_dem=new TZ_AbstractItem;
		$dem=$_dem->Getitembyid($id);
		
		$_res=new TZ_Resolver($dem['kind_id']);
		
		
		$_ki=$_res->instance;
		
		
		
		if(!$_ki->DocCanUnconfirmPrice($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
}elseif(isset($_POST['action'])&&($_POST['action']=="check_confirm_price")){
		$id=abs((int)$_POST['id']);
		
	
		
		  
		$_dem=new TZ_AbstractItem;
		$dem=$_dem->Getitembyid($id);
		
		$_res=new TZ_Resolver($dem['kind_id']);
		
		
		$_ki=$_res->instance;
		
		
		
		if(!$_ki->DocCanConfirmPrice($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
	
 

}elseif(isset($_POST['action'])&&($_POST['action']=="check_confirm_ship")){
		$id=abs((int)$_POST['id']);
		
	
		
		  
		$_dem=new TZ_AbstractItem;
		$dem=$_dem->Getitembyid($id);
		
		$_res=new TZ_Resolver($dem['kind_id']);
		
		
		$_ki=$_res->instance;
		
		
		
		if(!$_ki->DocCanConfirmShip($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
	
}elseif(isset($_POST['action'])&&($_POST['action']=="check_confirm_3")){
		$id=abs((int)$_POST['id']);
		
	
		
		  
		$_dem=new TZ_AbstractItem;
		$dem=$_dem->Getitembyid($id);
		
		$_res=new TZ_Resolver($dem['kind_id']);
		
		
		$_ki=$_res->instance;
		
		
		
		if(!$_ki->docCanConfirm3($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
	
}


//udalenie-annulirovabie
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_annul")){
	$id=abs((int)$_POST['id']);
	
	if(isset($_POST['from_card'])&&($_POST['from_card']==1)) $from_card=1;
	else $from_card=0;
	
	$note=SecStr(iconv("utf-8","windows-1251",$_POST['note']));
	
	$_ki=new TZ_AbstractItem;
	
		$trust=$_ki->getitembyid($id);
		
	$_res=new TZ_Resolver($trust['kind_id']);
	$_ti=$_res->instance;	
	
	$_si=new UserSItem;
	

	
	$log=new ActionLog;
	$_stat=new DocStatusItem;
	
	
	
	if(($trust['status_id']==1)&&($trust['is_confirmed']==0)){
		//удаление	
		if($au->user_rights->CheckAccess('w',1012)){
			$_ti->Edit($id,array('status_id'=>3),false,$result);
			
			$stat=$_stat->GetItemById(3);
			$log->PutEntry($result['id'],'аннулирование ТЗ',NULL,1012,NULL,'ТЗ '.$trust['code'].': установлен статус '.$stat['name'].' , причина: '.$note,$id);	
			
			 
			 	 
			//внести примечание
			/*$_ni=new Lead_HistoryItem;
			 
			$_ni->Add(array(
				'sched_id'=>$id,
				'user_id'=>$result['id'],
				'txt'=>'Автоматический комментарий: документ был аннулирован пользователем '.SecStr($result['name_s']).' , причина: '.$note,
				 
				'pdate'=>time()
					));	 */
		}
	}elseif($trust['status_id']==3){
		//разудаление
		if($au->user_rights->CheckAccess('w',1013)){
			$_ti->Edit($id,array('status_id'=>1, 'restore_pdate'=>time()),false,$result);
			
			$stat=$_stat->GetItemById(18);
			$log->PutEntry($result['id'],'восстановление ТЗ',NULL,1013,NULL,'ТЗ № '.$trust['code'].': установлен статус '.$stat['name'],$id);
			
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
	 
	 
			$template='tz/table.html';
	 
	 
	  
	  $acg=new tz_Group;
	   $acg->setauthresult($result);
	  
	  $dec=new  DBDecorator;
	  
	  $dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	
	
	  $ret=$acg->ShowPos(
		

			'tz/table.html',  //0
			 $dec, //1
			  ($au->user_rights->CheckAccess('w',1007)||($au->user_rights->CheckAccess('w',1006)&&(($result['id']==$trust['manager_id'])||($result['id']==$trust['created_id']))))&&($editing_user['is_confirmed']==1), //2
			  $au->user_rights->CheckAccess('w',1009), //3
			  0, //4
			  10000, //5
			  false, //6
			  true,  //7
			  $au->user_rights->CheckAccess('w',1012), //8
			  $au->user_rights->CheckAccess('w',1013),  //9
			  $au->user_rights->CheckAccess('w',1010), //10
			  $au->user_rights->CheckAccess('w',1011), //11
			  $au->user_rights->CheckAccess('w',1014), //12
			'_tzs'
			 );
	  
	 
	}else{
		$editing_user=$_ti->getitembyid($id);
		$sm=new SmartyAj;
		
		
		
		//блок аннулирования
		
		$editing_user['can_annul']=$_ti->DocCanAnnul($editing_user['id'],$reason, NULL, $result)&&$au->user_rights->CheckAccess('w',1012);
		if(!$au->user_rights->CheckAccess('w',1012)) $reason='недостаточно прав для данной операции';
		$editing_user['can_annul_reason']=$reason;
		
		//$editing_user['binded_to_annul']=$_ti->GetBindedDocumentsToAnnul($editing_user['id']);
		
		
		$editing_user['can_restore']=$_ti->DocCanRestore($editing_user['id'],$reason)&&$au->user_rights->CheckAccess('w',1013);
			if(!$au->user_rights->CheckAccess('w',1013)) $reason='недостаточно прав для данной операции';
		
		$stat=$_stat->Getitembyid($editing_user['status_id']);
		$editing_user['status_name']=$stat['name'];
		
		$sm->assign('bill',$editing_user);
		$ret=$sm->fetch('tz/toggle_annul_card.html');		
	}
		
}

 

//utv- razutv
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_confirm_price")){
	$id=abs((int)$_POST['id']);
	 
	
		$_ki=new TZ_AbstractItem;
	
		$trust=$_ki->getitembyid($id);
		
	$_res=new TZ_Resolver($trust['kind_id']);
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
		if($au->user_rights->CheckAccess('w',1011)&&$_ti->DocCanUnconfirmPrice($id, $rss)){
			    
				$_ti->Edit($id,array('is_confirmed'=>0, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()), true,$result);
				
				$log->PutEntry($result['id'],'снял утверждение заполнения ТЗ',NULL,1011, NULL, NULL,$id);
				 
					
			 
		} 
		
	}else{
		//есть права
		if($au->user_rights->CheckAccess('w',1010)&&$_ti->DocCanConfirmPrice($id, $rss)){
			 
				$_ti->Edit($id,array('is_confirmed'=>1, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'утвердил заполнения  ТЗ',NULL,1010, NULL, NULL,$id);	
				
			 	
			 
		} 
	}
	
	
	
	$acg=new TZ_Group;
	
	$shorter=abs((int)$_POST['shorter']);
	 
			$template='tz/table.html';
	 
	
	 $acg->setauthresult($result);
	
	$dec=new  DBDecorator;
	
	$dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	 
	
	$ret= $acg->ShowPos(
		

			'tz/table.html',  //0
			 $dec, //1
			 ($au->user_rights->CheckAccess('w',1007)||($au->user_rights->CheckAccess('w',1006)&&(($result['id']==$trust['manager_id'])||($result['id']==$trust['created_id']))))&&($editing_user['is_confirmed']==1), //2
			  $au->user_rights->CheckAccess('w',1009), //3
			  0, //4
			  10000, //5
			  false, //6
			  true,  //7
			  $au->user_rights->CheckAccess('w',1012), //8
			  $au->user_rights->CheckAccess('w',1013),  //9
			  $au->user_rights->CheckAccess('w',1010), //10
			  $au->user_rights->CheckAccess('w',1011), //11
			  $au->user_rights->CheckAccess('w',1014), //12
			'_tzs'
			
			 );
	
		
 
    

}elseif(isset($_POST['action'])&&($_POST['action']=="redraw_is_confirmed_price_confirmer")){
	$state=abs((int)$_POST['state']);
	if($state==0){
		$ret='';	
	}elseif($state==1){
		$ret=$result['position_s'].' '.$result['name_s'].' '.date("d.m.Y H:i:s",time());	
	}
	
}
  

elseif(isset($_POST['action'])&&($_POST['action']=="load_pdf_addresses")){
	
	$id=abs((int)$_POST['id']);
	
	$_item=new TZ_Item;
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
			where ( supplier_id in(select distinct supplier_id from tz_suppliers where  sched_id="'.$item['id'].'"))
			and id in(select distinct contact_id from supplier_contact_data where kind_id=5)
			and id in(select distinct contact_id from tz_suppliers_contacts where sc_id in (select distinct id from tz_suppliers where  sched_id="'.$item['id'].'"))
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
	$ret=$sm->fetch('tz/pdf_addresses.html');

}

elseif(isset($_POST['action'])&&($_POST['action']=="has_files")){
//есть ли файлы по записи план-ка?
	$count_of_files=0;	
	$id=abs((int)$_POST['id']);
	
	$sql='select count(*) from tz_file where bill_id="'.$id.'" ';
	
	$set=new mysqlset($sql);
	$rs=$set->GetResult();
	
	$f=mysqli_fetch_array($rs);
	
	$count_of_files+=(int)$f[0];
	
 
	
	$ret=$count_of_files;
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
	
	 
 
   
}elseif(isset($_POST['action'])&&($_POST['action']=="fail_unconfirm_price")){
	
	 
	
	 
	
	$id=abs((int)$_POST['id']);
	$_ti=new TZ_Item;
	$ti=$_ti->getitembyid($id);
	 
	$log->PutEntry($result['id'],'отказ снять утверждение заполнения ТЗ',NULL,1011, NULL, 'Попытка снять утверждение заполнения ТЗ № '.$ti['code'].': в действии отказано, причина - недостаточно прав',$id);	
	 
}

//будет ли доступ к тендеру при указанном менеджере указанному сотруднику
elseif(isset($_POST['action'])&&($_POST['action']=="scan_available_by_user_id")){
	$_tg=new TZ_Group;
	
	$manager_id=abs((int)$_POST['manager_id']);
	
	$res=$_tg->ScanAvailableByUserId($manager_id, $result['id']);
	if($res) $ret=1;
	else $ret=0;
	
	 
}
 

//настройка реестра
elseif(isset($_POST['action'])&&(($_POST['action']=="mode_reestr"))){
	$_views=new TZ_ViewsGroup;
	$_view=new TZ_ViewsItem;
	
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
	$_views=new TZ_ViewsGroup;
	$_view=new TZ_ViewsItem;
	
	 
	
	$_views->Clear($result['id']);
	 
}


elseif(isset($_POST['action'])&&(($_POST['action']=="add_supplier"))){
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
		
		
			
		
		 $ret=$sm->fetch('tz/supplierstz_many_table.html');
	}
 

}elseif($_POST['action']=="find_many_suppliers"){
	
	
	
	$_pg=new Quick_SupplierGroup;
	
	$dec=new DBDecorator;
	
	$dec->AddEntry(new SqlEntry('p.org_id',$result['org_id'], SqlEntry::E));
	
	$dec->AddEntry(new SqlEntry('p.is_supplier',1, SqlEntry::E));
	
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
 		
	 $ret=$_pg->GetItemsForBill('tz/supplierstz_many_list.html',  $dec,true,$all7,$result, 0, (strlen(SecStr(iconv("utf-8","windows-1251",$_POST['contact'])))>0));  
	 
	
	 
	
	

	
} 
elseif($_POST['action']=="retrieve_only_contacts"){
	$_sc=new TZ_SupplierContactGroup;
	
	
	$supplier_id=abs((int)$_POST['supplier_id']);
	$current_id=abs((int)$_POST['current_id']);
	$current_k_id=abs((int)$_POST['current_k_id']);
	
	
	
	
	$alls=$_sc->GetItemsByIdArr($supplier_id,$current_id, $current_k_id); 
	$sm=new SmartyAj;
	
	
	$sm->assign('supplier_id', $supplier_id);
	$sm->assign('items', $alls);
	
	 $ret=$sm->fetch('tz/supplierstz_only_contacts.html');
	 

}

//подгрузка данных для диалога выбора оборудования
elseif($_POST['action']=="load_gr"){
	$_rl=new RLMan;
	
	$restricted_groups=$_rl->GetBlockedItemsArr($result['id'],1,'w', 'catalog_group', 0);
	$restricted_producers=$_rl->GetBlockedItemsArr($result['id'],34,'w', 'pl_producer', 0);
	
	$_grs=new PosGroupGroup;
	$decorator=new DBDecorator;
	
	$decorator->AddEntry(new SqlEntry('p.parent_group_id', 0, SqlEntry::E));
	
	if(count($restricted_groups)>0){
		$decorator->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::NOT_IN_VALUES, NULL,$restricted_groups));
	}
	
	$decorator->AddEntry(new SqlOrdEntry('p.name',SqlOrdEntry::ASC));
	
	//ТОЛЬКО ОБОРУДОВАНИЕ
	$decorator->AddEntry(new SqlEntry('p.id', 1, SqlEntry::E));
	
	$data=$_grs->GetItemsByDecArr($decorator);
	//var_dump($data);
	
	$ret='<option value="0">-выберите-</option>';
	foreach($data as $k=>$v){
		 $ret.='<option value="'.$v['id'].'"';
		 if($v['id']==1) $ret.=' selected ';
		 $ret.='>'.$v['name'].'</option>';
	}
	
}
elseif($_POST['action']=="load_prod"){
	$_rl=new RLMan;
	
	$restricted_groups=$_rl->GetBlockedItemsArr($result['id'],1,'w', 'catalog_group', 0);
	$restricted_producers=$_rl->GetBlockedItemsArr($result['id'],34,'w', 'pl_producer', 0);
	
	$gr_id=abs((int)$_POST['gr_id']);
	
	$_grs=new PlProdGroup;
	
	$decorator=new DBDecorator;
	$decorator->AddEntry(new SqlEntry('p.group_id', $gr_id, SqlEntry::E));
	
	if(count($restricted_producers)>0){
		$decorator->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::NOT_IN_VALUES, NULL,$restricted_producers));
	}
	
	$decorator->AddEntry(new SqlOrdEntry('p.name',SqlOrdEntry::ASC));
	
	$data=$_grs->GetItemsByDecArr($decorator);
	//var_dump($data);
	
	$ret='<option value="0">-выберите-</option>';
	foreach($data as $k=>$v) $ret.='<option value="'.$v['id'].'">'.$v['name'].'</option>';
}
elseif($_POST['action']=="load_gr1"){
	$_rl=new RLMan;
	
	$restricted_groups=$_rl->GetBlockedItemsArr($result['id'],1,'w', 'catalog_group', 0);
	$restricted_producers=$_rl->GetBlockedItemsArr($result['id'],34,'w', 'pl_producer', 0);
	
	$_grs=new PosGroupGroup;
	$decorator=new DBDecorator;
	
	$gr_id=abs((int)$_POST['gr_id']);
	$prod_id=abs((int)$_POST['prod_id']);
	
	$decorator->AddEntry(new SqlEntry('p.parent_group_id', $gr_id, SqlEntry::E));
	$decorator->AddEntry(new SqlEntry('p.producer_id', $prod_id, SqlEntry::E));
	
	if(count($restricted_groups)>0){
		$decorator->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::NOT_IN_VALUES, NULL,$restricted_groups));
	}
	
	$decorator->AddEntry(new SqlOrdEntry('p.name',SqlOrdEntry::ASC));
	
	$data=$_grs->GetItemsByDecArr($decorator);
	//var_dump($data);
	
	$ret='<option value="0">-выберите-</option>';
	foreach($data as $k=>$v) $ret.='<option value="'.$v['id'].'">'.$v['name'].'</option>';
	
}
elseif($_POST['action']=="load_eq"){
	$_rl=new RLMan;
	
	$restricted_groups=$_rl->GetBlockedItemsArr($result['id'],1,'w', 'catalog_group', 0);
	$restricted_producers=$_rl->GetBlockedItemsArr($result['id'],34,'w', 'pl_producer', 0);
	
	$_grs=new PlPosGroup;
	$decorator=new DBDecorator;
	
	$gr_id1=abs((int)$_POST['gr_id1']);
	$prod_id=abs((int)$_POST['prod_id']);
	
	if(abs((int)$_POST['gr_id1'])>0) $decorator->AddEntry(new SqlEntry('p.group_id', abs((int)$_POST['gr_id1']), SqlEntry::E));
	if(abs((int)$_POST['prod_id'])>0)  $decorator->AddEntry(new SqlEntry('p.producer_id',  abs((int)$_POST['prod_id']), SqlEntry::E));
	if(abs((int)$_POST['gr_id'])>0) $decorator->AddEntry(new SqlEntry('p.group_id','select distinct id from  catalog_group where parent_group_id="'.abs((int)$_POST['gr_id']).'"', SqlEntry::IN_SQL)); //$decorator->AddEntry(new SqlEntry('p.group_id', abs((int)$_POST['gr_id']), SqlEntry::E));
	
	
	//отфильтровать оборудование с пустой печатной формой
	$decorator->AddEntry(new SqlEntry('p.txt_for_kp', "", SqlEntry::NE));
	//$decorator->AddEntry(new SqlEntry('pp.kp_form_id', 0, SqlEntry::NE));
	
	
	$decorator->AddEntry(new SqlEntry('p.parent_id', 0, SqlEntry::E));
	
	if(count($restricted_groups)>0){
		$decorator->AddEntry(new SqlEntry('p.group_id', NULL, SqlEntry::NOT_IN_VALUES, NULL,$restricted_groups));
	}
	if(count($restricted_producers)>0){
		$decorator->AddEntry(new SqlEntry('p.producer_id', NULL, SqlEntry::NOT_IN_VALUES, NULL,$restricted_producers));
	}
	
	
	$decorator->AddEntry(new SqlOrdEntry('p.name',SqlOrdEntry::ASC));
	
	$data=$_grs->GetItemsByDecArr($decorator);
	//var_dump($data);
	
	$ret='<option value="0">-выберите-</option>';
	foreach($data as $k=>$v) $ret.='<option value="'.$v['id'].'">'.$v['name'].'</option>';
	
}

//список поставщиков по ТЗ
elseif($_GET['action']=="load_tz_suppliers"){
	$id=abs((int)$_GET['id']);
	
	$_supplierstz=new TZ_SupplierGroup;
	$sup=$_supplierstz->GetItemsByIdArr($id);
	
	$data=array();
	
	foreach($sup as $v){
		
		$contacts=array();
		foreach($v['contacts'] as $vv){
			$contacts[]=array(
				'id'=>$vv['id'],
				'name'=>iconv('windows-1251','utf-8', $vv['name']),
				'position'=> iconv('windows-1251','utf-8', $vv['position'])
			);
		}
		
		$data[]=array(
			'id'=>$v['id'],
			'full_name'=>iconv('windows-1251','utf-8', $v['full_name']),
			'opf_name'=>iconv('windows-1251','utf-8', $v['opf_name']),
///			'note'=>iconv('windows-1251','utf-8', $v['note']),
			'contacts'=>$contacts
			);
	}
	
	//var_dump($data);
	
	$ret=json_encode($data);
}

elseif($_POST['action']=="load_tz_suppliers"){
	$id=abs((int)$_POST['id']);
	
	$_supplierstz=new TZ_SupplierGroup;
	$sup=$_supplierstz->GetItemsByIdArr($id);
	
	//var_dump($sup);
	
	$sm=new SmartyAj;
	$sm->assign('suppliers', $sup);
	$ret=$sm->fetch('tz/supplierstz_many_table_dialog.html'); 
}


//РАБОТА С ПРИМЕЧАНИЯМИ
elseif(isset($_POST['action'])&&($_POST['action']=="redraw_notes")){
	$sm=new SmartyAj;
	
	$user_id=abs((int)$_POST['user_id']);
	
	$rg=new TzNotesGroup;
	
	$sm->assign('items',$rg->GetItemsByIdArr($user_id,0,0,false, $au->user_rights->CheckAccess('w',1033), $au->user_rights->CheckAccess('w',1034), $result['id']));
	$sm->assign('word','notes');
	$sm->assign('user_id',$user_id);
	$sm->assign('named','Примечания');
	
	$sm->assign('can_edit', $au->user_rights->CheckAccess('w',1032));
	
	
	$ret=$sm->fetch('tz/d_notes.html');
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="add_notes")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',1032)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	
	
	$ri=new TzNotesItem;
	$ri->Add(array(
				'note'=>SecStr(iconv("utf-8","windows-1251",$_POST['note'])),
				'pdate'=>time(),
				'user_id'=>$user_id,
				'posted_user_id'=>$result['id']
			));
	
	$log->PutEntry($result['id'],'добавил примечания по ТЗ', NULL,1032, NULL,SecStr(iconv("utf-8","windows-1251",$_POST['note']),9),$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="edit_notes")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',1032)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	$id=abs((int)$_POST['id']);
	
	
	$ri=new TzNotesItem;
	$ri->Edit($id,
				array(
				'note'=>SecStr(iconv("utf-8","windows-1251",$_POST['note'])),
				'pdate'=>time(),
				'posted_user_id'=>$result['id']/*,
				'user_id'=>$user_id*/
			));
	
	$log->PutEntry($result['id'],'редактировал примечания по ТЗ', NULL,1032,NULL, SecStr(iconv("utf-8","windows-1251",$_POST['note']),9),$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="delete_notes")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',1032)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	$id=abs((int)$_POST['id']);
	
	
	$ri=new TzNotesItem;
	$ri->Del($id);
	
	$log->PutEntry($result['id'],'удалил примечания по ТЗ', NULL,1032,NULL,NULL,$user_id);
	
}




//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>