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

require_once('../classes/pl_currgroup.php');


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

require_once('../classes/doc_vn.class.php');
require_once('../classes/sched.class.php');

require_once('../classes/lead.class.php'); 
 
 
require_once('../classes/doc_vn_history_fileitem.php');
require_once('../classes/doc_vn_history_item.php');
require_once('../classes/doc_vn_history_group.php');

require_once('../classes/filecontents.php');
 
require_once('../classes/quick_suppliers_group.php'); 

require_once('../classes/supplieritem.php'); 
require_once('../classes/supplier_responsible_user_group.php');

require_once('../classes/doc_vn_view.class.php');

require_once('../classes/doc_vn_filegroup.php');
require_once('../classes/doc_vn_fileitem.php');
require_once('../classes/holy_dates.php');
 

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



if(isset($_POST['action'])&&(($_POST['action']=="find_missions"))){
	
	$prefix=2;
	
	$_pg=new Sched_Group;
	
	$decorator=new DBDecorator;
	
	$viewed_ids=$_pg->GetAvailableUserIds($result['id'],false,2);
	
	$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		//видимые сотрудники
	
	
	$decorator->AddEntry(new SqlEntry('p.manager_id', NULL, SqlEntry::IN_VALUES, NULL,$viewed_ids));	
	
	$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
	
	$decorator->AddEntry(new SqlEntry('p.created_id',$result['id'], SqlEntry::E));
	
	$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
		
	
	if(isset($_POST['pdate1'])&&isset($_POST['pdate2'])&&(strlen($_POST['pdate1'])>0)&&(strlen($_POST['pdate2'])>0)){	
		$pdate1 = $_POST['pdate1'];
		$pdate2 = $_POST['pdate2'];
		
		$decorator->AddEntry(new SqlEntry('p.pdate_beg',date('Y-m-d', DateFromdmY($pdate1)), SqlEntry::BETWEEN,date('Y-m-d', DateFromdmY($pdate2))));
	}
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['code'])))>0){
	 
			$names=explode(';', trim(iconv("utf-8","windows-1251",$_POST['code'])));
			foreach($names as $k=>$v) $names[$k]=SecStr($v);
			
			$decorator->AddEntry(new SqlEntry('p.code', NULL, SqlEntry::LIKE_SET, NULL,$names));	
	}
	
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['city'])))>0){
			$names=explode(';', trim(iconv("utf-8","windows-1251",$_POST['city'])));
			foreach($names as $k=>$v) $names[$k]=SecStr($v); //'name like "%'.SecStr($v).'%"';
			
			$decorator->AddEntry(new SqlEntry('c.name', NULL, SqlEntry::LIKE_SET, NULL,$names));	
	}
	
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['supplier'])))>0){
			$names=explode(';', trim(iconv("utf-8","windows-1251",$_POST['supplier'])));
			foreach($names as $k=>$v) $names[$k]=SecStr($v); //'name like "%'.SecStr($v).'%"';
			
			$decorator->AddEntry(new SqlEntry('sup.full_name', NULL, SqlEntry::LIKE_SET, NULL,$names));	
	}
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['status'])))>0){
			$names=explode(';', trim(iconv("utf-8","windows-1251",$_POST['status'])));
			foreach($names as $k=>$v) $names[$k]=SecStr($v); //'name like "%'.SecStr($v).'%"';
			
			$decorator->AddEntry(new SqlEntry('s.name', NULL, SqlEntry::LIKE_SET, NULL,$names));	
	}
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['manager'])))>0){
	 
			$names=explode(';', trim(iconv("utf-8","windows-1251",$_POST['manager'])));
			foreach($names as $k=>$v) $names[$k]=SecStr($v);
			
			$decorator->AddEntry(new SqlEntry('u.name_s', NULL, SqlEntry::LIKE_SET, NULL,$names));	
	} 
	 
	if(isset($_POST['already_loaded'])&&is_array($_POST['already_loaded'])) $dec->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::NOT_IN_VALUES, NULL,$_POST['already_loaded']));	
	
	
	$decorator->AddEntry(new SqlOrdEntry('p.pdate_beg',SqlOrdEntry::DESC));
	$decorator->AddEntry(new SqlOrdEntry('p.ptime_beg',SqlOrdEntry::DESC));
	 
	
	
	$ret=$_pg->ShowPos($prefix, //0
			 'doc_vn/mission_list.html', //1
			  $decorator, //2
			  $au->user_rights->CheckAccess('w',905), //3
			  0, //4
			  10000, //5
			  false, //6
			  true,  //7
			  $au->user_rights->CheckAccess('w',905), //8
			  $au->user_rights->CheckAccess('w',905),  //9
			  $au->user_rights->CheckAccess('w',905), //10
			  $au->user_rights->CheckAccess('w',905), //11
			  $au->user_rights->CheckAccess('w',905), //12
			  $au->user_rights->CheckAccess('w',905), //13
			  $au->user_rights->CheckAccess('w',915), //14
			  $au->user_rights->CheckAccess('w',916), //15
			  
			  
			  $au->user_rights->CheckAccess('w',923), //16
			  $au->user_rights->CheckAccess('w',924), //17
			  $au->user_rights->CheckAccess('w',925), //18
			  $au->user_rights->CheckAccess('w',926), //19
			  $au->user_rights->CheckAccess('w',927) //20
			   );
	 
	
	

	
} 

elseif(isset($_GET['action'])&&($_GET['action']=="retrieve_mission")){
	$_si=new Sched_AbstractItem;
	
	$si=$_si->GetItemById(abs((int)$_GET['id']));
	
	//метод должен также возвращать, СКОЛЬКО вых/праздничных дней выпадает
	$_hd=new HolyDates;
	$pdate1=datefromdmy(datefromYmd($si['pdate_beg']));	 $pdate2=datefromdmy(datefromYmd($si['pdate_end']));	
	$hd_count=0;
	for($pdate=$pdate1; $pdate<=$pdate2; $pdate=$pdate+24*60*60){
		if($_hd->IsHolyday($pdate)) $hd_count++;
	}
	
	
	
	$si['hd_count']=$hd_count;
	
	foreach($si as $k=>$v){
		if(($k=='pdate_beg')||($k=='pdate_end')) $v=datefromYmd($v);
		
		if($k=='ptime_beg'){
			$si['ptime_beg_hr']=substr($si['ptime_beg'],  0,2 );
			$si['ptime_beg_mr']=substr($si['ptime_beg'],  3,2 ); 	
		}
		
		if($k=='ptime_end'){
			$si['ptime_end_hr']=substr($si['ptime_end'],  0,2 );
			$si['ptime_end_mr']=substr($si['ptime_end'],  3,2 ); 	
		}
		
		$si[$k]=iconv('windows-1251', 'utf-8', $v);	
	}
	
	$_ui=new UserSItem;
	$user=$_ui->GetItemById($si['manager_id']);
	$si['manager_string']=iconv('windows-1251', 'utf-8',$user['name_s']);
	
	
	
	 
	$ret=json_encode($si); 
	
	 
}


elseif(isset($_POST['action'])&&($_POST['action']=="retrieve_mission_cities")){
	$_si=new Sched_AbstractItem;
	
	$si=$_si->GetItemById(abs((int)$_POST['id']));
	
	//города
			$_csg=new Sched_CityGroup;
			$csg=$_csg->GetItemsByIdArr($si['id']);
		//	
	$sm=new SmartyAj;
	$sm->assign('cities', $csg);
	$sm->assign('has_header', true);
	$ret=$sm->fetch('doc_vn/cities_table.html');		
}
 
elseif(isset($_POST['action'])&&($_POST['action']=="retrieve_mission_suppliers")){
	$_si=new Sched_AbstractItem;
	
	$si=$_si->GetItemById(abs((int)$_POST['id']));
	
			//контрагенты
			$_suppliers=new Sched_SupplierGroup;
			$sup=$_suppliers->GetItemsByIdArr($si['id']);
	//		$sm1->assign('suppliers', $sup);
	$sm=new SmartyAj;
	$sm->assign('suppliers', $sup);
	$sm->assign('has_header', true);
	$ret=$sm->fetch('doc_vn/suppliers_table.html');	
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
		
	
		
		  
		$_dem=new DocVn_AbstractItem;
		$dem=$_dem->Getitembyid($id);
		
		$_res=new DocVn_Resolver($dem['kind_id']);
		
		
		$_ki=$_res->instance;
		
		
		
		if(!$_ki->DocCanUnconfirmPrice($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
}elseif(isset($_POST['action'])&&($_POST['action']=="check_confirm_price")){
		$id=abs((int)$_POST['id']);
		
	
		
		  
		$_dem=new DocVn_AbstractItem;
		$dem=$_dem->Getitembyid($id);
		
		$_res=new DocVn_Resolver($dem['kind_id']);
		
		
		$_ki=$_res->instance;
		
		 
		
		if(!$_ki->DocCanConfirmPrice($id, $rss55)) $ret=$rss55;
		else $ret=0;
		 
		
		//если ноль - то все хорошо
	
 

}elseif(isset($_POST['action'])&&($_POST['action']=="check_create")){
		$sched_id=abs((int)$_POST['sched_id']);
		
	
		
		  
		$_dem=new DocVn_AbstractItem;
		$dem=$_dem->Getitembyid($id);
		
		$_res=new DocVn_Resolver($dem['kind_id']);
		
		
		$_ki=$_res->instance;
		
		 
		
		if(!$_ki->DocCanByMission($sched_id, $rss55)) $ret=$rss55;
		else $ret=0;
		 
		
		//если ноль - то все хорошо
	
}


//udalenie-annulirovabie
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_annul")){
	$id=abs((int)$_POST['id']);
	
	if(isset($_POST['from_card'])&&($_POST['from_card']==1)) $from_card=1;
	else $from_card=0;
	
	$note=SecStr(iconv("utf-8","windows-1251",$_POST['note']));
	
	$_ki=new DocVn_AbstractItem;
	
		$trust=$_ki->getitembyid($id);
		
	$_res=new DocVn_Resolver($trust['kind_id']);
	$_ti=$_res->instance;	
	
	$_si=new UserSItem;
	

	
	$log=new ActionLog;
	$_stat=new DocStatusItem;
	
	
	
	if(($trust['status_id']==18)&& ($trust['is_confirmed']==0)){
		//удаление	
		if($au->user_rights->CheckAccess('w',1091)){
			$_ti->Edit($id,array('status_id'=>3),false,$result);
			
			$stat=$_stat->GetItemById(3);
			$log->PutEntry($result['id'],'аннулирование вн. документа',NULL,1091,NULL,'вн. документ '.$trust['code'].': установлен статус '.$stat['name'],$id);	
			
			 
			 	 
			//внести примечание
			$_ni=new DocVn_HistoryItem;
			 
			$_ni->Add(array(
				'sched_id'=>$id,
				'user_id'=>0,
				'txt'=>'Автоматический комментарий: документ был аннулирован пользователем '.SecStr($result['name_s']).' , причина: '.$note,
				 
				'pdate'=>time()
					));	 
		}
	}elseif($trust['status_id']==3){
		//разудаление
		
		if($au->user_rights->CheckAccess('w',1098)){
			$_ti->Edit($id,array('status_id'=>18, 'restore_pdate'=>time()), false,$result);
			
			$stat=$_stat->GetItemById(18);
			$log->PutEntry($result['id'],'восстановление вн. документа',NULL,1098,NULL,'вн. документ № '.$trust['code'].': установлен статус '.$stat['name'],$id);
			
		 
		}
		
	}
	
	if($from_card==0){
	 $acg=$_res->group_instance;
	
	$shorter=abs((int)$_POST['shorter']);
	
	 $prefix=$trust['kind_id'];
	 
	$template='doc_vn/table'.$prefix.'.html';
	 
	
	 $acg->setauthresult($result);
	
	$dec=new  DBDecorator;
	
	$dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	 
	
	
	$ret= $acg->ShowPos(
		

		$template,  //0
			 $dec, //1
			 $au->user_rights->CheckAccess('w',1090), //2
			   $au->user_rights->CheckAccess('w',1091), //3
			  0, //4
			  10000, //5
			  false, //6
			  true,  //7
			  $au->user_rights->CheckAccess('w',1091), //8
			  $au->user_rights->CheckAccess('w',1097),  //9
			  $au->user_rights->CheckAccess('w',1091), //10
			  $au->user_rights->CheckAccess('w',1098), //11
			  $au->user_rights->CheckAccess('w',1091), //12
			  $prefix //13
			 );
	  
	 
	}else{
		$editing_user=$_ti->getitembyid($id);
		$sm=new SmartyAj;
		
		
		
		//блок аннулирования
		
		$editing_user['can_annul']=$_res->instance->DocCanAnnul($editing_user['id'],$reason, NULL, $result)&&$au->user_rights->CheckAccess('w',1091);
		if(!$au->user_rights->CheckAccess('w',1091)) $reason='недостаточно прав для данной операции';
		$editing_user['can_annul_reason']=$reason;
		
		//$editing_user['binded_to_annul']=$_ti->GetBindedDocumentsToAnnul($editing_user['id']);
		
		
		$editing_user['can_restore']=$_res->instance->DocCanRestore($editing_user['id'],$reason)&&$au->user_rights->CheckAccess('w',1097);
			if(!$au->user_rights->CheckAccess('w',1097)) $reason='недостаточно прав для данной операции';
		
		$stat=$_stat->Getitembyid($editing_user['status_id']);
		$editing_user['status_name']=$stat['name'];
		
		$sm->assign('bill',$editing_user);
		$ret=$sm->fetch('doc_vn/toggle_annul_card.html');		
	}
		
}

 

//utv- razutv
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_confirm_price")){
	$id=abs((int)$_POST['id']);
	 
	
		$_ki=new DocVn_AbstractItem;
	
		$trust=$_ki->getitembyid($id);
		
	$_res=new DocVn_Resolver($trust['kind_id']);
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
		if($au->user_rights->CheckAccess('w',1098)&&$_ti->DocCanUnconfirmPrice($id, $rss)){
			    
				$_ti->Edit($id,array('is_confirmed'=>0, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()), true,$result);
				
				$log->PutEntry($result['id'],'снял утверждение заполнения вн. документа',NULL,1091, NULL, NULL,$id);
				 
					
			 
		} 
		
	}else{
		//есть права
		if($au->user_rights->CheckAccess('w',1091)&&$_ti->DocCanConfirmPrice($id, $rss)){
			 
				$_ti->Edit($id,array('is_confirmed'=>1, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'утвердил заполнения вн. документа',NULL,1091, NULL, NULL,$id);	
				
			 	
			 
		} 
	}
	
	
	
	$acg=$_res->group_instance;
	
	$shorter=abs((int)$_POST['shorter']);
	
	 $prefix=$trust['kind_id'];
	 
	$template='doc_vn/table'.$prefix.'.html';
	 
	
	 $acg->setauthresult($result);
	
	$dec=new  DBDecorator;
	
	$dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	 
	
	
	$ret= $acg->ShowPos(
		

			$template,  //0
			 $dec, //1
			 $au->user_rights->CheckAccess('w',1090), //2
			   $au->user_rights->CheckAccess('w',1091), //3
			  0, //4
			  10000, //5
			  false, //6
			  true,  //7
			  $au->user_rights->CheckAccess('w',1091), //8
			  $au->user_rights->CheckAccess('w',1097),  //9
			  $au->user_rights->CheckAccess('w',1091), //10
			  $au->user_rights->CheckAccess('w',1098), //11
			  $au->user_rights->CheckAccess('w',1091), //12
			  $prefix //13
			 );
	
		
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
	
	$_hi=new DocVn_HistoryItem; $_hg=new DocVn_HistoryGroup; $_dsi=new DocStatusItem; 
	$_file=new DocVn_HistoryFileItem;
	
	$_sch=new DocVn_Item;
	$sch=$_sch->GetItemById($id);
	$count_hi=$_hg->CountHistory($id);
	
	
	$params=array();
	$params['sched_id']=$id;
	$params['txt']=SecStr(iconv("utf-8","windows-1251",$_POST['comment']));
	$params['user_id']=$result['id'];
	$params['pdate']=time();
	
	$code=$_hi->Add($params);
	
	$log->PutEntry($result['id'],'добавлен комментарий к внутреннему документу', NULL,1091,NULL, $params['txt'],$id);
	
	 
	$files_server=$_POST['files_server'];
	$files_client=$_POST['files_client'];
	
	foreach($files_server as $k=>$file_server){
		$file_id=$_file->Add(array(
			'history_id'=>$code,
			'filename'=>SecStr(iconv("utf-8","windows-1251",$file_server)),
			'orig_name'=>SecStr(iconv("utf-8","windows-1251",$files_client[$k])),
		));	
		
		$log->PutEntry($result['id'],'прикреплен файл к комментарию  к внутреннему документу', NULL,1091,NULL, 'Комментарий '.$params['txt'].',  файл '.SecStr(iconv("utf-8","windows-1251",$files_client[$k])),$id);
		 
		
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
	$_hr=new DocVn_HistoryGroup;
	
	$dec=new DBDecorator();
	$dec->AddEntry(new SqlEntry('o.id',$code, SqlEntry::E));
	
	$ret=$_hr->ShowHistory($id, 'doc_vn/lenta.html', $dec, true, false, true,  $result,
			 $au->user_rights->CheckAccess('w',1093),
			 $au->user_rights->CheckAccess('w',1094));
			 
			 
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="edit_comment")){
	$id=abs((int)$_POST['id']);
	$comment_id=abs((int)$_POST['comment_id']);
	
	$_hi=new DocVn_HistoryItem;
	$_file=new DocVn_HistoryFileItem;
	
	$params=array();
	//$params['sched_id']=$id;
	$params['txt']=SecStr(iconv("utf-8","windows-1251",$_POST['comment']));
	//$params['user_id']=$result['id'];
	//$params['pdate']=time();
	
	 $_hi->Edit($comment_id, $params);
	
	$log->PutEntry($result['id'],'редактирован комментарий к внутреннему документу', NULL,1091,NULL, $params['txt'],$id);
	
	
	$files_server=$_POST['files_server'];
	$files_client=$_POST['files_client'];
	
	foreach($files_server as $k=>$file_server){
		$file_id=$_file->Add(array(
			'history_id'=>$comment_id,
			'filename'=>SecStr(iconv("utf-8","windows-1251",$file_server)),
			'orig_name'=>SecStr(iconv("utf-8","windows-1251",$files_client[$k])),
		));	
		
		$log->PutEntry($result['id'],'прикреплен файл к комментарию к внутреннему документу', NULL,1091,NULL, 'Комментарий '.$params['txt'].',  файл '.SecStr(iconv("utf-8","windows-1251",$files_client[$k])),$id);
		
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
	$_hr=new DocVn_HistoryGroup;
	
	$dec=new DBDecorator();
	$dec->AddEntry(new SqlEntry('o.id',$comment_id, SqlEntry::E));
	
	$ret=$_hr->ShowHistory($id, 'doc_vn/lenta.html', $dec, true, false, true,  $result,
			 $au->user_rights->CheckAccess('w',1093),
			 $au->user_rights->CheckAccess('w',1094));
	
}

elseif(isset($_POST['action'])&&($_POST['action']=="toggle_comment")){
	$id=abs((int)$_POST['id']);
	$comment_id=abs((int)$_POST['comment_id']);
	
	$_hi=new DocVn_HistoryItem;
	$hi=$_hi->GetItemById($comment_id);
	
	if($hi['is_shown']==1){
		$_hi->Edit($comment_id, array('is_shown'=>0));
		$log->PutEntry($result['id'],'скрыт комментарий к внутреннему документу', NULL,1091,NULL, 'Комментарий '.$hi['txt'].' ',$id);
		$ret=0;
	}else{
		$_hi->Edit($comment_id, array('is_shown'=>1));
		$log->PutEntry($result['id'],'показан комментарий к внутреннему документу', NULL,1091,NULL, 'Комментарий '.$hi['txt'].' ',$id);
		$ret=1;
	}
	
}


elseif(isset($_POST['action'])&&($_POST['action']=="load_pdf_addresses")){
	
	$id=abs((int)$_POST['id']);
	
	$_item=new DocVn_AbstractItem;
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
			where ( supplier_id in(select distinct supplier_id from sched_suppliers where  sched_id="'.$item['sched_id'].'") )
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
	$ret=$sm->fetch('doc_vn/pdf_addresses.html');

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
	
	
	
	
	$ffg=new DocVnFileGroup(1,  $id,  new FileDocFolderItem(1,  $id, new DocVnFileItem(1)));;
			  
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
	$ret=$sm->fetch('doc_vn/pdf_files.html');

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
	$_views=new DocVn_ViewsGroup1;
	$_view=new DocVn_ViewsItem1;
	
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
	$_views=new DocVn_ViewsGroup1;
	$_view=new DocVn_ViewsItem1;
	
	 
	
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
	
	
	//кроме статусов:
	$decorator->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::NOT_IN_VALUES, NULL,array(3,18, 33, 32, 36, 31)));	
	
	//менеджер
	$decorator->AddEntry(new SqlEntry('p.manager_id',abs((int)$_POST['manager_id']), SqlEntry::E));
	
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
		

			'doc_vn/lead_list.html',  //0
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

//работа с суточными
elseif(isset($_POST['action'])&&($_POST['action']=="add_doc_sut")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',1115)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	
	
	$_cpi=new DocVn_SutItem;
	
	$params=array();
	$params['org_id']=$result['org_id'];
	$params['begin_pdate']=Datefromdmy($_POST['begin_pdate']);
	
	 
	$params['min_days']=abs((int)str_replace(',','.', $_POST['min_days']));	
	$params['cost_rus']=abs((float)str_replace(',','.', $_POST['cost_rus']));	
	$params['cost_not_rus']=abs((float)str_replace(',','.', $_POST['cost_not_rus']));	
	$params['last_day_cost']=abs((float)str_replace(',','.', $_POST['last_day_cost']));	
	
	$params['notes']=SecStr(iconv('utf-8', 'windows-1251', $_POST['notes']));
	
	
	
	$code=$_cpi->add($params);
	
	 
		
		 
	$log->PutEntry($result['id'], 'создал % вывода наличных', NULL, 1115, NULL, 'действие с '.$_POST['begin_pdate'].',   Мин. оплачиваемый срок, дней: '.$params['min_days'].',  Суточные руб./сутки по России: '.$params['cost_rus'].',  Суточные руб./сутки ВНЕ России: '.$params['cost_not_rus'].', Стоимость за последний день ВНЕ России, руб.: '.$params['last_day_cost'].' ', $code);
	 
	
		
}
elseif(isset($_POST['action'])&&($_POST['action']=="del_doc_sut")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',1115)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$id=abs((int)$_POST['id']);	
	
	
	$_cpi=new DocVn_SutItem;
	
	$cpi=$_cpi->GetItemById($id);
	
	$_cpi->Del($id);
	
	$log->PutEntry($result['id'], 'удалил % вывода наличных', NULL, 1115, NULL, 'действие с '.date('d.m.Y',$cpi['begin_pdate']).',   Мин. оплачиваемый срок, дней: '.$cpi['min_days'].',  Суточные руб./сутки по России: '.$cpi['cost_rus'].',  Суточные руб./сутки ВНЕ России: '.$cpi['cost_not_rus'].', Стоимость за последний день ВНЕ России, руб.: '.$cpi['last_day_cost'].' ', $id);
}
elseif(isset($_POST['action'])&&($_POST['action']=="edit_doc_sut")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',1115)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	
	
	$_cpi=new DocVn_SutItem;
	
	$id=abs((int)$_POST['id']);	
	
	
	$params=array();
	$params['org_id']=$result['org_id'];
	$params['begin_pdate']=Datefromdmy($_POST['begin_pdate']);
	
	 
	$params['min_days']=abs((int)str_replace(',','.', $_POST['min_days']));	
	$params['cost_rus']=abs((float)str_replace(',','.', $_POST['cost_rus']));	
	$params['cost_not_rus']=abs((float)str_replace(',','.', $_POST['cost_not_rus']));	
	$params['last_day_cost']=abs((float)str_replace(',','.', $_POST['last_day_cost']));	
	
	
	$params['notes']=SecStr(iconv('utf-8', 'windows-1251', $_POST['notes']));
	
	
	
	$code=$_cpi->Edit($id, $params);
	
	 
		
		 
	$log->PutEntry($result['id'], 'редактировал правило расчета суточных', NULL, 1115, NULL, 'действие с '.$_POST['begin_pdate'].',   Мин. оплачиваемый срок, дней: '.$params['min_days'].',  Суточные руб./сутки по России: '.$params['cost_rus'].',  Суточные руб./сутки ВНЕ России: '.$params['cost_not_rus'].', Стоимость за последний день ВНЕ России, руб.: '.$params['last_day_cost'].' ', $id);
	 
	
		
}




//работа со справочником затрат
elseif(isset($_POST['action'])&&($_POST['action']=="find_branches")){
	$branch_id=abs((int)$_POST['branch_id']);
	$_bg=new DocVn_ExpensesKindsGroup;
	
	$sm=new SmartyAj;
	
	$_sbi=new DocVn_ExpensesKindsItem;
	$sbi=$_sbi->getitembyid($branch_id);
	
	$branch_id=abs((int)$_POST['branch_id']);
	
	$already_in=$_POST['already_in'];
	$sm->assign('pos', $_bg->LoadBranchArr($branch_id, $already_in));
	$sm->assign('can_edit_branch', $au->user_rights->CheckAccess('w',1092));
	
	$sm->assign('parent_id', $branch_id);
	if($sbi!==false) $sm->assign('parent_parent_id', $sbi['parent_id']);
	else $sm->assign('parent_parent_id',0);
	
	$ret=$sm->fetch('doc_vn/maccounts_list.html');
	


}elseif(isset($_POST['action'])&&($_POST['action']=="add_branch")){
	
	if(!$au->user_rights->CheckAccess('w',1092)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	
	$branch_id=abs((int)$_POST['branch_id']);
	$name=SecStr(iconv("utf-8","windows-1251",$_POST['name']));
//	$code=SecStr(iconv("utf-8","windows-1251",$_POST['code']));
	
	$_sbi=new DocVn_ExpensesKindsItem;
	
	$_sbi->Add(array('parent_id'=>$branch_id, 'name'=>$name));
	
	$log->PutEntry($result['id'],'добавил статью затрат', NULL,1092, NULL,$name);	
			

 

}elseif(isset($_POST['action'])&&($_POST['action']=="del_branch")){
	
	if(!$au->user_rights->CheckAccess('w',1092)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	
	$branch_id=abs((int)$_POST['branch_id']);
	 
	$_sbi=new DocVn_ExpensesKindsItem;
	
	$sbi=$_sbi->GetItemById($branch_id);
	
	$_sbi->Del($branch_id);
	
	$log->PutEntry($result['id'],'удалил статью затрат', NULL,1092, NULL,SecStr($sbi['name']));	


}elseif(isset($_POST['action'])&&($_POST['action']=="edit_branch")){
	
	if(!$au->user_rights->CheckAccess('w',1092)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	
	$branch_id=abs((int)$_POST['branch_id']);
	$name=SecStr(iconv("utf-8","windows-1251",$_POST['name']));
	//$code=SecStr(iconv("utf-8","windows-1251",$_POST['code']));
	
	$_sbi=new DocVn_ExpensesKindsItem;
	$sbi=$_sbi->GetItemById($branch_id);
	
	$_sbi->Edit($branch_id, array( 'name'=>$name/*, 'code'=>$code*/));
	
	$log->PutEntry($result['id'],'отредактировал статью затрат', NULL,1092, NULL,'Старое название: '.SecStr($sbi['name']).', новое название: '.$name);	
			
			

}
elseif(isset($_GET['action'])&&($_GET['action']=="retrieve_branch")){
	$_si=new DocVn_ExpensesKindsItem;
	$id=abs((int)$_GET['id']);
	$si=$_si->GetItemById($id);
	
	
	 
	if($si!==false){
		$rret=array();
		foreach($si as $k=>$v){
			 
			
			
			$rret[]='"'.$k.'":"'.htmlspecialchars(str_replace("\r", "", str_replace("\n",  "",  $v))).'"';
		}
		
		
		
		$rret[]='"branch_subbranch":"'.$_si->CountSubs($id).'"';
		 
		$ret='{'.implode(', ',$rret).'}';
	}
	
}

elseif(isset($_POST['action'])&&($_POST['action']=="redraw_expenses")){
	
		//$id=abs((int)$_POST['id']);
		$fields=$_POST['fields'];

		if(isset($_POST['adding_field'])) $adding_field=abs((int)$_POST['adding_field']);
		else $adding_field=NULL;
		
		$sched_id=abs((int)$_POST['sched_id']);	
		$id=abs((int)$_POST['id']);	
		
		$accounts=array(
			'ids'=>array(), //айди статей
			'hashes'=>array(), //хэши: айди статьи_дата
			
			'data'=>array() //переданные данные
		);
		
		//fields.push(nid+"|"+$("#exp_plan_"+hash).val()+"|"+$("#exp_plan_currency_id_"+hash).val()+"|"+$("#exp_fact_"+hash).val()+"|"+$("#exp_fact_currency_id_"+hash).val()+"|"+$("#exp_fact_l_or_korp_"+hash).val()+"|"+$("#exp_doc_name_"+hash).val()+"|"+$("#exp_doc_no_"+hash).val()+"|"+$("#exp_doc_pdate_"+hash).val()+"|"+$("#p_pdate_"+hash).val()+"|"+hash);
		
		
		foreach($fields as $k=>$v){
			$valarr=explode('|',$v);
			
			if(isset($valarr[8])&&($valarr[8]=="")) $valarr[8]=NULL;
			
			$accounts['ids'][]=$valarr[0];
			$accounts['hashes'][]=md5($valarr[0].'_'.(int)$valarr[9]);
			$accounts['data'][md5($valarr[0].'_'.(int)$valarr[9])]=array(
				'id'=>$valarr[0],
				'plan'=>(float)$valarr[1],
				'plan_currency_id'=>(int)$valarr[2],
				'fact'=>(float)$valarr[3],
				'fact_currency_id'=>(int)$valarr[4],
				'fact_l_or_korp'=>(int)$valarr[5],
				'pdate'=>(int)$valarr[9],
				 
				'doc_name'=>iconv('utf-8','windows-1251',$valarr[6]),
				'doc_no'=>iconv('utf-8','windows-1251',$valarr[7]),
				'doc_pdate'=>$valarr[8],
				'hash'=>md5($valarr[0].'_'.(int)$valarr[9])
			)	;
		}
		
		
		$_block=new DocVn_ExpensesBlock;
		
		//adding_field
	 
		$accounts['ids'][]=$adding_field;
		$tm=time();
		$accounts['hashes'][]=md5($adding_field.'_'.$tm);
		$accounts['data'][md5($adding_field.'_'.$tm)]=array(
			'id'=>$adding_field,
			'plan'=>0,
			'plan_currency_id'=>DocVn_ExpensesBlock::$sut_currency_id,
			'fact'=>0,
			'fact_currency_id'=>DocVn_ExpensesBlock::$sut_currency_id,
			'fact_l_or_korp'=>0,
			'pdate'=>$tm,
			 
			'doc_name'=>'',
			'doc_no'=>'',
			'doc_pdate'=>NULL,
			'hash'=>md5($adding_field.'_'.$tm)
		)	;	
	 
			
		
		
		 /*
		echo '<pre>';
		var_dump($adding_field);
		var_dump($accounts);
		echo '</pre>';*/
		
		$sm=new SmartyAj;
		$data=$_block->ConstructByAccounts($accounts, $sched_id,$result);
		
		$itogo=$_block->CalcItogoArr($data, $id, NULL, $result);
		
		$sm->assign('itogo',$itogo);
		$sm->assign('items',$data);
		
		if($id==0){
			$sm->assign('can_modify_plan',true);
			$sm->assign('can_modify_fact',false); //позже актуализировать!
		}else{
			$_itm=new DocVn_AbstractItem;
			$itm=$_itm->GetItemById($id);
			$_res=new DocVn_Resolver($itm['kind_id']);
			
			$field_rules=$_res->rules_instance->GetFields($itm,$result['id']);
			$sm->assign('can_modify_plan',$field_rules['plan']);
			$sm->assign('can_modify_fact',$field_rules['fact']);	
		}
		
		$sm->assign('has_header',true);
		
		
		//валюты в таблице
		$_curr=new PlCurrGroup;
		$kind_ids=array(0); $kind_vals=array('-выберите-'); $kind_name='';
		$currs1= $_curr->GetItemsArr(0);
		foreach($currs1 as $k=>$v){
			$kind_ids[]=$v['id']; $kind_vals[]=$v['signature'];
		}
		$sm->assign('curr_ids', $kind_ids); $sm->assign('curr_names', $kind_vals);
		
		$ret=$sm->fetch('doc_vn/exp_table.html');
		
		//print_r($accounts);
		 




//работа с датами отпуска за работу в выходные
}elseif(isset($_POST['action'])&&($_POST['action']=="load_vyh_otp_date")){
	
	$complex_positions=$_POST['complex_positions'];
	
	$dates=array(); $ids=time();
	foreach($complex_positions as $k=>$v){
		$valarr=explode(';',$v);
		$ids++;
		$dates[]=array(
			'id'=>$ids,
			'pdate'=>$valarr[0] 
			 
		);
	}
	
	if(count($dates)==0) $dates[]=array(
			'id'=>$ids,
			'pdate'=>'' 
			 
		);
	
	$sm=new SmartyAj;
	
	$sm->assign('vyh_otp_dates', $dates);
	$sm->assign('can_modify', true);
	
	$ret=$sm->fetch('doc_vn/vyh_otp_dates_edit.html');
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="transfer_empty_otp_date")){
	 $dates=array(); $ids=time();
	 $dates[]=array(
			'id'=>$ids,
			'pdate'=>''
		);
	
	$sm=new SmartyAj;
	
	$sm->assign('vyh_otp_dates', $dates);
	$sm->assign('can_modify', true);
	
	$ret=$sm->fetch('doc_vn/vyh_otp_dates_edit_row.html');
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="transfer_vyh_otp_date")){
	
	$complex_positions=$_POST['complex_positions'];
	
	$dates=array(); $ids=time();
	foreach($complex_positions as $k=>$v){
		$valarr=explode(';',$v);
		$ids++;
		$dates[]=array(
			'id'=>$ids,
			'pdate'=>$valarr[0]
		);
	}
	
	 
	
	$sm=new SmartyAj;
	
	$sm->assign('vyh_otp_dates', $dates);
	$sm->assign('can_modify', true);
	
	$ret=$sm->fetch('doc_vn/vyh_otp_dates_onpage.html');
	
	
}


//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>