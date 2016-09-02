<?
session_start();
header('Content-type: text/html; charset=windows-1251');

require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/discr_table_objects.php');
require_once('../classes/actionlog.php');
require_once('../classes/db_decorator.php');
 
require_once('../classes/lead.class.php');

require_once('../classes/supplieritem.php'); 
require_once('../classes/quick_suppliers_group.php'); 

require_once('../classes/app_contract_group.php');
require_once('../classes/app_contract_item.php');
require_once('../classes/app_contract_notesitem.php');

require_once('../classes/app_contract_historygroup.php');
require_once('../classes/app_contract_historyitem.php');

require_once('../classes/app_contract_history_filegroup.php');
require_once('../classes/app_contract_history_fileitem.php');

require_once('../classes/filecontents.php');
 

$au=new AuthUser();
$result=$au->Auth(false,false);
$log=new ActionLog;

if($result===NULL){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();		
}
 
$_appc_item = new AppContractItem;
$appcg = new AppContractGroup;
$appcg_view = new AppContract_ViewGroup;

$ret = '';
$rec_upd = false;
  
 if(isset($_POST['action']) && (($_POST['action']=="check_confirm") || ($_POST['action']=="check_unconfirm") || ($_POST['action']=="toggle_confirm") || ($_POST['action']=="toggle_annul"))){
    if(!isset($_GET['id'])){
        if(!isset($_POST['id'])){
            header("HTTP/1.1 404 Not Found");
            header("Status: 404 Not Found");
            include("404.php");
            die();		
        } else $id = abs((int)$_POST['id']);
    } else $id = abs((int)$_GET['id']); 

    $appc_item = $_appc_item->Getitembyid($id);
 }

 if(isset($_POST['action'])&&($_POST['action']=="check_unconfirm")){
    //если ноль - то все хорошо
    if(!$_appc_item->CanUnconfirm($id, $rss55)) $ret = $rss55;
    else $ret=0;
} elseif(isset($_POST['action'])&&($_POST['action']=="check_confirm")){
    //если ноль - то все хорошо
    if(!$_appc_item->CanConfirm($id, $rss55)) $ret = $rss55;
    else $ret=0;
//utv- razutv
} elseif(isset($_POST['action'])&&($_POST['action']=="toggle_confirm")){
    if($appc_item['is_confirmed'] == 1){
        //есть права: либо сам утв.+есть права, либо есть искл. права:
        if($au->user_rights->CheckAccess('w',1155) && $_appc_item->CanUnconfirm($id, $rss)){
            $_appc_item->Edit($id, array('status_id'=>4, 'is_confirmed'=>0, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()), true, $result);
            $log->PutEntry($result['id'], 'сн€л утверждение за€вки на договор', NULL, 1155, NULL, NULL, $appc_item['code']);
            $rec_upd = true;
        } 
    } else {
        if($au->user_rights->CheckAccess('w',1154) && $_appc_item->CanConfirm($id, $rss)){
            $_appc_item->Edit($id, array('status_id'=>2, 'is_confirmed'=>1, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()), true, $result);
            $log->PutEntry($result['id'], 'утвердил за€вку на договор', NULL, 1154, NULL, NULL, $appc_item['code']);
            $rec_upd = true;
        } 
    }
} elseif(isset($_POST['action'])&&($_POST['action']=="toggle_annul")){
    if ((($appc_item['status_id'] == 1) || ($appc_item['status_id'] == 4)) && ($appc_item['is_confirmed'] == 0)) {
        if($au->user_rights->CheckAccess('w',1156)){
            $_appc_item->Edit($id, array('status_id'=>3), true, $result);
            $log->PutEntry($result['id'], 'аннулирование за€вки на договор', NULL, 1156, NULL, NULL, $appc_item['code']);
            
            //внести примечание
            $note = SecStr(iconv("utf-8","windows-1251", $_POST['note']));
            $_ni = new AppContractNotesItem;
            $_ni->Add(array(
                'app_contract_id' => $id,
                'posted_user_id' => $result['id'],
                'note' => 'јвтоматическое примечание: документ был аннулирован пользователем '.SecStr($result['name_s']).' ('.$result['login'].'), причина: '.$note,
                'is_auto' => 1,
                'pdate' => time()
            ));	
        } 
    } elseif ($appc_item['status_id'] == 3) {
        if($au->user_rights->CheckAccess('w',1157)){
            $_appc_item->Edit($id, array('status_id'=>4, 'restore_pdate'=>time()), true, $result);
            $log->PutEntry($result['id'], 'восстановление за€вки на договор', NULL, 1157, NULL, NULL, $appc_item['code']);
            
            //внести примечание
            $_ni = new AppContractNotesItem;
            $_ni->Add(array(
                'app_contract_id' => $id,
                'posted_user_id' => $result['id'],
                'note' => 'јвтоматическое примечание: документ был восстановлен пользователем '.SecStr($result['name_s']).' ('.$result['login'].')',
                'is_auto' => 1,
                'pdate' => time()
            ));	
        } 
    }
}
elseif(isset($_POST['action']) && ($_POST['action']=="add_supplier")){
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
		
		
			
		
		$ret=$sm->fetch('app_contract/suppliers_many_table.html');
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
elseif(isset($_POST['action']) && ($_POST['action']=="find_many_suppliers")){
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
                        // добавл€ем условие выбора записей дл€ отображени€ is_shown=1
			//$dec->AddEntry(new SqlEntry('p.id','select distinct supplier_id from supplier_contact where  '.implode(' or ',$names).'', SqlEntry::IN_SQL));
			$dec->AddEntry(new SqlEntry('p.id','select distinct supplier_id from supplier_contact where  is_shown=1 and ('.implode(' or ',$names).')', SqlEntry::IN_SQL));
		
	}
	
		if(isset($_POST['already_loaded'])&&is_array($_POST['already_loaded'])) $dec->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::NOT_IN_VALUES, NULL,$_POST['already_loaded']));	
	
	
	//ограничени€ по к-ту
	$limited_supplier=NULL;
	//ограничений нет
 		
	$ret=$_pg->GetItemsForBill('app_contract/suppliers_many_list.html',  $dec,true,$all7,$result, 0, (strlen(SecStr(iconv("utf-8","windows-1251",$_POST['contact'])))>0));
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
	
	if($_POST['action']=="retrieve_only_contacts") $ret=$sm->fetch('app_contract/suppliers_only_contacts.html');
	else $ret=$sm->fetch('app_contract/suppliers_contacts.html');
    
}
elseif(isset($_POST['action'])&&($_POST['action']=="add_comment")){
	$id=abs((int)$_POST['id']);
	
	$_hi = new AppContractHistoryItem; 
        $_hg = new AppContractHistoryGroup; 
        $_dsi = new DocStatusItem; 
	$_file = new AppContractHistoryFileItem;
	
	$_sch=new AppContractItem;
	$sch=$_sch->GetItemById($id);
	$count_hi=$_hg->CountHistory($id);
	
	
	$params=array();
	$params['app_contract_id']=$id;
	$params['txt']=SecStr(iconv("utf-8","windows-1251",$_POST['comment']));
	$params['user_id']=$result['id'];
	$params['pdate']=time();
	$params['status_id']=$sch['status_id'];
	
	$code=$_hi->Add($params);
	
	$log->PutEntry($result['id'],'добавлен комментарий к за€вке на договор', NULL,1150,NULL, $params['txt'],$id);
	
	
	/*if(isset($_POST['probability'])){
		$a_params=array();
		$a_params['probability']=abs((float)str_replace(',','.',$_POST['probability']));
		$_sch->Edit($id, $a_params);
		if($sch['probability']!=$a_params['probability']){
			$log->PutEntry($result['id'],'изменена веро€тность заключени€ контракта', NULL,950,NULL, 'старое значение: '.$sch['probability'].', новое значение: '.$a_params['probability'],$id);
	
		}
		
	}
	
	if(isset($_POST['max_price'])){
		$a_params=array();
		if(strlen($_POST['max_price'])==0){
			$a_params['max_price']=NULL;
		}else{
		
			$a_params['max_price']=abs((float)str_replace(',','.',$_POST['max_price']));
		
		}
		
		$a_params['currency_id']=abs((int)$_POST['currency_id']);
		
		$_ci=new PlCurrItem;
		$oldci=$_ci->GetItemById($sch['currency_id']);
		$newci=$_ci->GetItemById($a_params['currency_id']);
		
		$_sch->Edit($id, $a_params);
		if(($sch['max_price']!=$a_params['max_price'])||($sch['currency_id']!=$a_params['currency_id'])){
			$log->PutEntry($result['id'],'изменена макс. цена контракта', NULL,950,NULL, 'старое значение: '.$sch['max_price'].' '.$oldci['signature'].', новое значение: '.$a_params['max_price'].' '.$newci['signature'],$id);
	
		}
		
	}*/
	
	$files_server=$_POST['files_server'];
	$files_client=$_POST['files_client'];
	
	foreach($files_server as $k=>$file_server){
		$file_id=$_file->Add(array(
			'history_id'=>$code,
			'filename'=>SecStr(iconv("utf-8","windows-1251",$file_server)),
			'orig_name'=>SecStr(iconv("utf-8","windows-1251",$files_client[$k])),
		));	
		
		$log->PutEntry($result['id'],'прикреплен файл к комментарию  к за€вке на договор', NULL,950,NULL, ' омментарий '.$params['txt'].',  файл '.SecStr(iconv("utf-8","windows-1251",$files_client[$k])),$id);
		 
		
		$_ct=new FileContents(SecStr(iconv("utf-8","windows-1251", $files_client[$k])), $_file->GetStoragePath().$file_server);
		
		$contents='';
		
		try {
    		$contents=$_ct->GetContents();
		} catch (Exception $e) {
			//echo '¬ыброшено исключение: ',  $e->getMessage(), "\n";
		}
		
		$_file->Edit($file_id, array('text_contents'=>SecStr($contents)));
	}
	
	//отправить сообщени€ всем имеющим права 922 участникам задачи (кроме автора)
		
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
	$topic='Ќовый комментарий в задаче GYDEX.ѕланировщик';
	$_mi=new MessageItem;  
	
	$_fi=new SchedFileItem;
	$_user_item=new UserSItem;
	if(count($users_to_send)>0){
		$_item=new Sched_TaskItem();
		$item=$_item->GetItemById($params['sched_id']);	
	}
	foreach($users_to_send as $k1=>$user){
		
		$txt='<div>';
		$txt.='<em>ƒанное сообщение сгенерировано автоматически.</em>';
		$txt.=' </div>';
		
		
		$txt.='<div>&nbsp;</div>';
		
		$txt.='<div>';
		$txt.='”важаемый(а€) '.$user['name_s'].'!';
		$txt.='</div>';
		$txt.='<div>&nbsp;</div>';
		
		
		$txt.='<div>';
		$txt.='<strong>¬ доступной ¬ам задаче GYDEX.ѕланировщик </strong>';
		 
		 
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
		
		
		$txt.='</em><strong>, по€вилс€ новый комментарий  от пользовател€ '.SecStr($from_user['name_s']).':</strong></div> ';
		
		$txt.=' <div>&nbsp;</div>';
		
		$txt.=$params['txt'];
		$txt.=' <div>&nbsp;</div>';

	//	$txt.='<div>ƒл€ просмотра комментари€ просьба перейти в карту задачи по ссылке.</div>';
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
			$txt.='<div>  комментарию прикреплено '.$files_count.' файлов: '.implode(', ',$files).'.</div>';
		}
		
		$txt.='<div>&nbsp;</div>';
	
		$txt.='<div>';
		$txt.='C уважением, программа "'.SITETITLE.'".';
		$txt.='</div>';
		
		$_mi->Send(0,0,array('from_id'=>-1, 'to_id'=>$user['id'],'pdate'=>time(), 'parent_id'=>0, 'txt'=>SecStr($txt), 'topic'=>$topic),false);	
	}
	
	*/
	
	
	//
	/*if(($sch['status_id']==23)&&($count_hi==0)){
		$_sch->Edit($id,array('status_id'=>24));
					  
			$log->PutEntry($result['id'],'начал выполнение лида',NULL,950, NULL, NULL,$id);
			
			$stat=$_dsi->GetItemById(24);
			$log->PutEntry($result['id'],'смена статуса лида',NULL,950,NULL,'установлен статус '.$stat['name'],$id);	
		
	}*/
	
	
	//вывести что получилось
	$_hr=new AppContractHistoryGroup;
	
	$dec=new DBDecorator();
	$dec->AddEntry(new SqlEntry('o.id',$code, SqlEntry::E));
	
	$ret=$_hr->ShowHistory($id, 'app_contract/lenta.html', $dec, true, false, true,  $result,
			 $au->user_rights->CheckAccess('w',1158),
			 $au->user_rights->CheckAccess('w',1159));
}			 
			 
	


//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>