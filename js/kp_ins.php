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

require_once('../classes/kp_in.class.php');


require_once('../classes/kp_in_view.class.php');
require_once('../classes/kp_in_fileitem.php');
require_once('../classes/kp_in_filegroup.php');
 

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
		
	
		
		  
		$_dem=new KpIn_AbstractItem;
		$dem=$_dem->Getitembyid($id);
		
		$_res=new KpIn_Resolver($dem['kind_id']);
		
		
		$_ki=$_res->instance;
		
		
		
		if(!$_ki->DocCanUnconfirmPrice($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
}elseif(isset($_POST['action'])&&($_POST['action']=="check_confirm_price")){
		$id=abs((int)$_POST['id']);
		
	
		
		  
		$_dem=new KpIn_AbstractItem;
		$dem=$_dem->Getitembyid($id);
		
		$_res=new KpIn_Resolver($dem['kind_id']);
		
		
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
	
	$_ki=new KpIn_AbstractItem;
	
		$trust=$_ki->getitembyid($id);
		
	$_res=new KpIn_Resolver($trust['kind_id']);
	$_ti=$_res->instance;	
	
	$_si=new UserSItem;
	

	
	$log=new ActionLog;
	$_stat=new DocStatusItem;
	
	
	
	if(($trust['status_id']==1)&&($trust['is_confirmed']==0)){
		//удаление	
		if($au->user_rights->CheckAccess('w',1024)){
			$_ti->Edit($id,array('status_id'=>3),false,$result);
			
			$stat=$_stat->GetItemById(3);
			$log->PutEntry($result['id'],'аннулирование вход/исход КП',NULL,1024,NULL,'вход/исход КП '.$trust['code'].': установлен статус '.$stat['name'].' , причина: '.$note,$id);	
			
			 
			 	 
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
		if($au->user_rights->CheckAccess('w',1025)){
			$_ti->Edit($id,array('status_id'=>1, 'restore_pdate'=>time()),false,$result);
			
			$stat=$_stat->GetItemById(18);
			$log->PutEntry($result['id'],'восстановление вход/исход КП',NULL,1025,NULL,'вход/исход КП № '.$trust['code'].': установлен статус '.$stat['name'],$id);
			
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
	 
	 
			$template='kp_in/table.html';
	 
	 
	 if($trust['kind_id']==0) $acg=new KpIn_Group;
	 else $acg=new KpIn_Out_Group;
	 
	   $acg->setauthresult($result);
	  
	  $dec=new  DBDecorator;
	  
	  $dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	
	   
	if($trust['kind_id']==0) $prefix='_kp_ins';
	else $prefix='_kp_outs';
	
	  $ret=$acg->ShowPos(
		

			'kp_in/table.html',  //0
			 $dec, //1
			  ($au->user_rights->CheckAccess('w',1019)||($au->user_rights->CheckAccess('w',1018)&&(($result['id']==$trust['manager_id'])||($result['id']==$trust['created_id']))))&&($trust['is_confirmed']==1), //2
			  $au->user_rights->CheckAccess('w',1021), //3 
			  0, //4
			  10000, //5
			  false, //6
			  true,  //7
			 $au->user_rights->CheckAccess('w',1024), //8
			  $au->user_rights->CheckAccess('w',1025),  //9
			  $au->user_rights->CheckAccess('w',1022), //10
			  $au->user_rights->CheckAccess('w',1023), //11
			  $au->user_rights->CheckAccess('w',1026), //12
			$prefix,
			$trust['kind_id']
			 );
	  
	 
	}else{
		$editing_user=$_ti->getitembyid($id);
		$sm=new SmartyAj;
		
		
		
		//блок аннулирования
		
		$editing_user['can_annul']=$_ti->DocCanAnnul($editing_user['id'],$reason, NULL, $result)&&$au->user_rights->CheckAccess('w',1024);
		if(!$au->user_rights->CheckAccess('w',1024)) $reason='недостаточно прав для данной операции';
		$editing_user['can_annul_reason']=$reason;
		
		//$editing_user['binded_to_annul']=$_ti->GetBindedDocumentsToAnnul($editing_user['id']);
		
		
		$editing_user['can_restore']=$_ti->DocCanRestore($editing_user['id'],$reason)&&$au->user_rights->CheckAccess('w',1025);
			if(!$au->user_rights->CheckAccess('w',1025)) $reason='недостаточно прав для данной операции';
		
		$stat=$_stat->Getitembyid($editing_user['status_id']);
		$editing_user['status_name']=$stat['name'];
		
		$sm->assign('bill',$editing_user);
		$ret=$sm->fetch('kp_in/toggle_annul_card.html');		
	}
		
}

 

//utv- razutv
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_confirm_price")){
	$id=abs((int)$_POST['id']);
	 
	
		$_ki=new KpIn_AbstractItem;
	
		$trust=$_ki->getitembyid($id);
		
	$_res=new KpIn_Resolver($trust['kind_id']);
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
		if($au->user_rights->CheckAccess('w',1023)&&$_ti->DocCanUnconfirmPrice($id, $rss)){
			    
				$_ti->Edit($id,array('is_confirmed'=>0, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()), true,$result);
				
				$log->PutEntry($result['id'],'снял утверждение заполнения вход/исход КП',NULL,1023, NULL, NULL,$id);
				 
					
			 
		} 
		
	}else{
		//есть права
		if($au->user_rights->CheckAccess('w',1022)&&$_ti->DocCanConfirmPrice($id, $rss)){
			 
				$_ti->Edit($id,array('is_confirmed'=>1, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'утвердил заполнения  вход/исход КП',NULL,1022, NULL, NULL,$id);	
				
			 	
			 
		} 
	}
	
	
	
	 if($trust['kind_id']==0) $acg=new KpIn_Group;
	 else $acg=new KpIn_Out_Group;
	
	$shorter=abs((int)$_POST['shorter']);
	 
			$template='kp_in/table.html';
	 
	
	 $acg->setauthresult($result);
	
	$dec=new  DBDecorator;
	
	$dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	 
	if($trust['kind_id']==0) $prefix='_kp_ins';
	else $prefix='_kp_outs';
	
	$ret= $acg->ShowPos(
		

			'kp_in/table.html',  //0
			 $dec, //1
			  ($au->user_rights->CheckAccess('w',1019)||($au->user_rights->CheckAccess('w',1018)&&(($result['id']==$trust['manager_id'])||($result['id']==$trust['created_id']))))&&($trust['is_confirmed']==1), //2
			  $au->user_rights->CheckAccess('w',1021), //3 
			  0, //4
			  10000, //5
			  false, //6
			  true,  //7
			 $au->user_rights->CheckAccess('w',1024), //8
			  $au->user_rights->CheckAccess('w',1025),  //9
			  $au->user_rights->CheckAccess('w',1022), //10
			  $au->user_rights->CheckAccess('w',1023), //11
			  $au->user_rights->CheckAccess('w',1026), //12
			$prefix,
			$trust['kind_id']
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
	
	$_item=new KpIn_Item;
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
			where ( supplier_id in(select distinct supplier_id from lead_suppliers where  sched_id="'.$item['lead_id'].'") or  supplier_id in(select distinct supplier_id from sched_contacts where  sched_id="'.$item['lead_id'].'"))
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
	$ret=$sm->fetch('kp_in/pdf_addresses.html');

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
	
	
	
	
	$ffg=new KpInFileGroup(1,  $id,  new FileDocFolderItem(1,  $id, new KpInFileItem(1)));;
	
	$filetext=$ffg->ShowFiles('doc_file/incard_list.html', $decorator,0,10000,'ed_kp_in.php', 'kp_in_file.html', 'swfupl-js/kp_in_files.php',   
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
	$ret=$sm->fetch('kp_in/pdf_files.html');

}



elseif(isset($_POST['action'])&&($_POST['action']=="has_files")){
//есть ли файлы по записи план-ка?
	$count_of_files=0;	
	$id=abs((int)$_POST['id']);
	
	$sql='select count(*) from kp_in_file where bill_id="'.$id.'" ';
	
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
	$_ti=new KpIn_Item;
	$ti=$_ti->getitembyid($id);
	 
	$log->PutEntry($result['id'],'отказ снять утверждение заполнения вход/исход КП',NULL,1023, NULL, 'Попытка снять утверждение заполнения вход/исход КП № '.$ti['code'].': в действии отказано, причина - недостаточно прав',$id);	
	 
}

//будет ли доступ к тендеру при указанном менеджере указанному сотруднику
elseif(isset($_POST['action'])&&($_POST['action']=="scan_available_by_user_id")){
	$_tg=new KpIn_Group;
	
	$manager_id=abs((int)$_POST['manager_id']);
	
	$res=$_tg->ScanAvailableByUserId($manager_id, $result['id']);
	if($res) $ret=1;
	else $ret=0;
	
	 
}
 

//настройка реестра
elseif(isset($_POST['action'])&&(($_POST['action']=="mode_reestr"))){
	$_views=new KpIn_ViewsGroup;
	$_view=new KpIn_ViewsItem;
	
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
	$_views=new KpIn_ViewsGroup;
	$_view=new KpIn_ViewsItem;
	
	 
	
	$_views->Clear($result['id']);
	 
}



//РАБОТА С ПРИМЕЧАНИЯМИ
elseif(isset($_POST['action'])&&($_POST['action']=="redraw_notes")){
	$sm=new SmartyAj;
	
	$user_id=abs((int)$_POST['user_id']);
	
	$rg=new KpInNotesGroup;
	
	$sm->assign('items',$rg->GetItemsByIdArr($user_id,0,0,false, $au->user_rights->CheckAccess('w',1036), $au->user_rights->CheckAccess('w',1037), $result['id']));
	$sm->assign('word','notes');
	$sm->assign('user_id',$user_id);
	$sm->assign('named','Примечания');
	
	$sm->assign('can_edit', $au->user_rights->CheckAccess('w',1035));
	
	
	$ret=$sm->fetch('kp_in/d_notes.html');
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="add_notes")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',1035)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	
	
	$ri=new KpInNotesItem;
	$ri->Add(array(
				'note'=>SecStr(iconv("utf-8","windows-1251",$_POST['note'])),
				'pdate'=>time(),
				'user_id'=>$user_id,
				'posted_user_id'=>$result['id']
			));
	
	$log->PutEntry($result['id'],'добавил примечания по КП', NULL,1035, NULL,SecStr(iconv("utf-8","windows-1251",$_POST['note']),9),$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="edit_notes")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',1035)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	$id=abs((int)$_POST['id']);
	
	
	$ri=new KpInNotesItem;
	$ri->Edit($id,
				array(
				'note'=>SecStr(iconv("utf-8","windows-1251",$_POST['note'])),
				'pdate'=>time(),
				'posted_user_id'=>$result['id']/*,
				'user_id'=>$user_id*/
			));
	
	$log->PutEntry($result['id'],'редактировал примечания по КП', NULL,1035,NULL, SecStr(iconv("utf-8","windows-1251",$_POST['note']),9),$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="delete_notes")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',1035)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	$id=abs((int)$_POST['id']);
	
	
	$ri=new KpInNotesItem;
	$ri->Del($id);
	
	$log->PutEntry($result['id'],'удалил примечания по КП', NULL,1035,NULL,NULL,$user_id);
	
}

//РАБОТА С типами оборудования
elseif(isset($_POST['action'])&&($_POST['action']=="redraw_types_dics")){
	$sm=new SmartyAj;
	if(isset($_POST['user_id'])) $user_id=abs((int)$_POST['user_id']);
	else $user_id=0;
	
	$opg=new Tender_EqTypeGroup;
	$sm->assign('opfs_total', $opg->GetItemsArr());
	
	$ret=$sm->fetch('kp_in/d_types.html');
	
	
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
 
}


//РАБОТА С прчинами отказа
elseif(isset($_POST['action'])&&($_POST['action']=="redraw_fails_dics")){
	$sm=new SmartyAj;
	if(isset($_POST['user_id'])) $user_id=abs((int)$_POST['user_id']);
	else $user_id=0;
	
	$opg=new Lead_FailGroup;
	$sm->assign('fails_total', $opg->GetItemsArr());
	
	$ret=$sm->fetch('kp_in/fails.html');
	
	
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


//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>