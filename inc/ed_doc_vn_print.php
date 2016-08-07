<?
 $sm1=new SmartyAdm;
	
		
		if($print==0) $print_add='';
		else $print_add='_print';
		
	 
		//var_dump($field_rights);
		
		
		
		
	$_res=new  DocVn_Resolver($editing_user['kind_id']);
		
		
		//построим доступы
		$_roles=$_res->rules_instance;
		$field_rights=$_roles->GetFields($editing_user, $result['id']);
		$sm1->assign('field_rights', $field_rights);
		
		 
		
		$sm1->assign('manager_id', $editing_user['manager_id']);
		$_uis=new UserSItem; $uis=$_uis->getitembyid($editing_user['manager_id']);
		$editing_user['manager_string']= $uis['name_s'];
		
		 
		$_kind=new DocVn_KindItem; $kind=$_kind->getitembyid($editing_user['kind_id']);
		$editing_user['kind_name']=$kind['name'];
		
		$_leads=new DocVn_LeadGroup;
		$leads=$_leads->GetItemsByIdArr($editing_user['id']);
		
		$lead=$leads[0];
		$editing_user['lead_id']=$lead['lead_id'];
		$editing_user['lead_string']=$lead['code'];
		
	 
		$editing_user['pdate']=date('d.m.Y', $editing_user['pdate']);
		
		 
		
		//кто отправил
		$_user=new UserSItem;
		$send_who=$_user->GetItemById($editing_user['send_user_id']);
		$sm1->assign('send_who', $send_who['name_s']);
		
		
		
		//связанная командировка
		$_sched=new Sched_MissionItem;
		$si=$_sched->getitembyid($editing_user['sched_id']);
		
		//метод должен также возвращать, СКОЛЬКО вых/праздничных дней выпадает
		$_hd=new HolyDates;
		$pdate1=datefromdmy(datefromYmd($si['pdate_beg']));	 $pdate2=datefromdmy(datefromYmd($si['pdate_end']));	
		$hd_count=0;
		$hdays=array();
		for($pdate=$pdate1; $pdate<=$pdate2; $pdate=$pdate+24*60*60){
			if($_hd->IsHolyday($pdate)) {
				$hd_count++;
				$hdays[]=date('d.m.Y', $pdate);
			}
		}
		$sm1->assign('hdays', $hdays);
		$editing_user['hd_count']=$hd_count;
		
		$_opt=new DocVn_VyhDateGroup;
		$sm1->assign('vyh_otp_dates', $_opt->GetItemsArrById($editing_user['id']));
		
		 
		
		//города
		$_csg=new Sched_CityGroup;
		$csg=$_csg->GetItemsByIdArr($si['id']);
		//	
	 	$sm1->assign('cities', $csg);
		
		//контрагенты
		$_suppliers=new Sched_SupplierGroup;
			$sup=$_suppliers->GetItemsByIdArr($si['id']);
	 
		$sm1->assign('suppliers', $sup);
		
		 
		$from_hrs=array();
		$from_hrs[]='';
		for($i=0;$i<=23;$i++) $from_hrs[]=sprintf("%02d",$i);
		$sm1->assign('ptime_beg_h',$from_hrs);
		
				
		$from_ms=array();
		$from_ms[]='';
		for($i=0;$i<=59;$i++) $from_ms[]=sprintf("%02d",$i);
		$sm1->assign('ptime_beg_m',$from_ms);
		   
		//причины работы в вых
		$_kinds=new DocVn_VyhReasonGroup;
		$kinds=$_kinds->GetItemsArr(0);
		$kind_ids=array(0); $kind_vals=array('-выберите-'); $kind_name='';
		foreach($kinds as $k=>$v){
			$kind_ids[]=$v['id']; $kind_vals[]=$v['name'];	if($v['id']==$kind_id) $kind_name=$v['name'];
		}
		$sm1->assign('vyh_reason_ids', $kind_ids); $sm1->assign('vyh_reason_names', $kind_vals);
		$sm1->assign('vyh_reason_id', 0); 
		
		
		//статьи расхода 
		
		$_exps=new DocVn_ExpensesBlock;
		//валюты в таблице
		$_curr=new PlCurrGroup;
		$kind_ids=array(0); $kind_vals=array('-выберите-'); $kind_name='';
		$currs1= $_curr->GetItemsArr(0);
		foreach($currs1 as $k=>$v){
			$kind_ids[]=$v['id']; $kind_vals[]=$v['signature'];
		}
		$sm1->assign('curr_ids', $kind_ids); $sm1->assign('curr_names', $kind_vals);
		$data=$_exps->ConstructById($id,NULL,$result);
		$sm1->assign('exps', $data);
		//ITOGO
		$itogo=$_exps->CalcItogoArr($data, 0, NULL, $result);
		
		$sm1->assign('itogo',$itogo);
		
		
		
		
		$editing_user['pdate_beg']=datefromYmd($si['pdate_beg']);
		$editing_user['pdate_end']=datefromYmd($si['pdate_end']);
		
		$editing_user['ptime_beg_hr']=substr($si['ptime_beg'],  0,2 );
		$editing_user['ptime_beg_mr']=substr($si['ptime_beg'],  3,2 );
		
		$editing_user['ptime_end_hr']=substr($si['ptime_end'],  0,2 );
		$editing_user['ptime_end_mr']=substr($si['ptime_end'],  3,2 ); 	 	
		$editing_user['sched_str']=$si['code']; 
	
	
	
		
		//возможность РЕДАКТИРОВАНИЯ - 
			//пол-ль - создал
	//или пол-ль - в списке видящих
	 	
		//$can_modify=true;
		
		$can_modify=$field_rights['common'];   //in_array($editing_user['status_id'],$_editable_status_id);
		
		 
		
		 
		 

		$can_modify_ribbon=!in_array($editing_user['status_id'],array(3));
	 
	 	//лента документа
		
		$ribbon_decorator=new DBDecorator();
		$ribbon_decorator->AddEntry(new SqlEntry('pdate',$editing_user['dir_pdate'], SqlEntry::L));
		
		
		$_hg=new DocVn_HistoryGroup;
		$history= $_hg->ShowHistory(
			$editing_user['id'],
			 'doc_vn/lenta'.$print_add.'.html', 
			 $ribbon_decorator, 
			 $can_modify_ribbon,
			 true,
			 false,
			 $result,
			 $au->user_rights->CheckAccess('w',1093),
			 $au->user_rights->CheckAccess('w',1094),$history_data,true,true
			 );
		$sm1->assign('lenta',$history);
		$sm1->assign('lenta_len',count($history_data));
		 
		 
		
		
		//блок аннулирования
		
		$editing_user['can_annul']=$_res->instance->DocCanAnnul($editing_user['id'],$reason,$editing_user)&&$au->user_rights->CheckAccess('w',1096);
		if(!$au->user_rights->CheckAccess('w',1096)) $reason='недостаточно прав для данной операции';
		$editing_user['can_annul_reason']=$reason;
		
		 
		
		$editing_user['can_restore']=$_res->instance->DocCanRestore($editing_user['id'],$reason,$editing_user)&&$au->user_rights->CheckAccess('w',1097);
			if(!$au->user_rights->CheckAccess('w',1097)) $reason='недостаточно прав для данной операции';
		
		
		
		
			
		 
		
		//блок утверждения!
		if(($editing_user['is_confirmed']==1)&&($editing_user['user_confirm_id']!=0)){
			$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['user_confirm_id']);
			$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.date("d.m.Y H:i:s",$editing_user['confirm_pdate']);
			
			 
			$sm1->assign('confirmer',$confirmer);
			$sm1->assign('user_confirmer',$_user_confirmer);
			
			$sm1->assign('is_confirmed_confirmer',$confirmer);
			
			$sm1->assign('is_confirm_pdate', date("d.m.Y",$editing_user['confirm_pdate']));
		}
		
		$can_confirm_price=false;
		if($editing_user['is_confirmed_done']==0){
			
			  
		  
		  if($editing_user['is_confirmed']==1){
			  
			   $can_confirm_price=$au->user_rights->CheckAccess('w',1098)&&$field_rights['to_confirm'];
			  
		  }else{
			  //95
			  $can_confirm_price=$au->user_rights->CheckAccess('w',1091)&&$field_rights['to_confirm'] ;
		  }
		}
		$sm1->assign('can_confirm',$can_confirm_price);
		
		
		//согласование служебки
		//рук отдела
		if(($editing_user['is_ruk']==1)&&($editing_user['user_ruk_id']!=0)){
			$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['user_ruk_id']);
			$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s']; ///*.' '.date("d.m.Y H:i:s",$editing_user['ruk_pdate']*/);
			
			$sm1->assign('is_ruk_confirmer',$_user_confirmer);
		}
	    $can_confirm_price=false;
		//найти руководителя отдела
		
		$_ug=new DocVn_UsersSGroup;
		$user_ruk=$_ug->GetRuk($uis);
		// var_dump($user_ruk);
		
	   	if($editing_user['is_ruk']==1){
			
			 $can_confirm_price=($au->user_rights->CheckAccess('w',1100)||($user_ruk['id']==$result['id']))&&$field_rights['to_ruk_sz'];
			
		}else{
			//95
			$can_confirm_price=($au->user_rights->CheckAccess('w',1099)||($user_ruk['id']==$result['id']))&&$field_rights['to_ruk_sz'] ;
		}
		
		$sm1->assign('can_ruk_sz',$can_confirm_price);
		
		
		
		//директор
		if(($editing_user['is_dir']==1)&&($editing_user['user_dir_id']!=0)){
			$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['user_dir_id']);
			$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s']; //.' '.date("d.m.Y H:i:s",$editing_user['dir_pdate']);
			
			$sm1->assign('is_dir_confirmer',$_user_confirmer);
			$sm1->assign('dir_pdate',date("d.m.Y",$editing_user['dir_pdate']));
		}
	    $can_confirm_price=false;
		//найти директора
		
		$_ug=new DocVn_UsersSGroup;
		$user_ruk=$_ug->GetDir($uis);
		// var_dump($user_ruk);
		
	   	if($editing_user['is_dir']==1){
			
			 $can_confirm_price=($au->user_rights->CheckAccess('w',1112)||($user_ruk['id']==$result['id']))&&$field_rights['to_dir_sz'];
			
		}else{
			//95
			$can_confirm_price=($au->user_rights->CheckAccess('w',1111)||($user_ruk['id']==$result['id']))&&$field_rights['to_dir_sz'] ;
		}
		
		$sm1->assign('can_dir_sz',$can_confirm_price);
		
		
		
		
		//блок утверждения выполнения
		if(($editing_user['is_confirmed_done']==1)&&($editing_user['confirm_done_id']!=0)){
			$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['confirm_done_id']);
			$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.date("d.m.Y H:i:s",$editing_user['confirm_done_pdate']);
			
			 
			$sm1->assign('is_confirmed_done_confirmer',$_user_confirmer);
		}
		
		$can_confirm_price=false;
		if($editing_user['is_confirmed']==1){
			
			  
		  
		  if($editing_user['is_confirmed_done']==1){
			  
			   $can_confirm_price=$au->user_rights->CheckAccess('w',1117)&&$field_rights['to_done'];
			  
		  }else{
			  //95
			  $can_confirm_price=$au->user_rights->CheckAccess('w',1091)&&$field_rights['to_done'] ;
		  }
		}
		$sm1->assign('can_confirm_done',$can_confirm_price);
		$editing_user['confirm_done_pdate']=date("d.m.Y", $editing_user['confirm_done_pdate']);
		
		//согласование отчета
		//рук отдела
		if(($editing_user['is_ruk_ot']==1)&&($editing_user['ruk_ot_id']!=0)){
			$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['ruk_ot_id']);
			$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'];//.' '.date("d.m.Y H:i:s",$editing_user['ruk_ot_pdate']);
			
			$sm1->assign('is_ruk_ot_confirmer',$_user_confirmer);
		}
	    $can_confirm_price=false;
		//найти руководителя отдела
		
		$_ug=new DocVn_UsersSGroup;
		$user_ruk=$_ug->GetRuk($uis);
		// var_dump($user_ruk);
		
	   	if($editing_user['is_ruk_ot']==1){
			
			 $can_confirm_price=($au->user_rights->CheckAccess('w',1110)||($user_ruk['id']==$result['id']))&&$field_rights['to_ruk_ot'];
			
		}else{
			//95
			$can_confirm_price=($au->user_rights->CheckAccess('w',1109)||($user_ruk['id']==$result['id']))&&$field_rights['to_ruk_ot'] ;
		}
		
		$sm1->assign('can_ruk_ot',$can_confirm_price);
		
		
		
		//директор - отчет
		if(($editing_user['is_dir_ot']==1)&&($editing_user['dir_ot_id']!=0)){
			$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['dir_ot_id']);
			$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'];//' '.date("d.m.Y H:i:s",$editing_user['dir_ot_pdate']);
			
			$sm1->assign('is_dir_ot_confirmer',$_user_confirmer);
			$sm1->assign('dir_ot_pdate',date("d.m.Y",$editing_user['dir_ot_pdate']));
		}
	    $can_confirm_price=false;
		//найти директора
		
		$_ug=new DocVn_UsersSGroup;
		$user_ruk=$_ug->GetDir($uis);
		// var_dump($user_ruk);
		
	   	if($editing_user['is_dir_ot']==1){
			
			 $can_confirm_price=($au->user_rights->CheckAccess('w',1114)||($user_ruk['id']==$result['id']))&&$field_rights['to_dir_ot'];
			
		}else{
			//95
			$can_confirm_price=($au->user_rights->CheckAccess('w',1113)||($user_ruk['id']==$result['id']))&&$field_rights['to_dir_ot'] ;
		}
		
		$sm1->assign('can_dir_ot',$can_confirm_price);
		
		
		 
		
		
		
		
		 
		 
		 
		
		//получим список тех, кто может снять утверждение заполнения
		$_usg1=new UsersSGroup;
		$usg1=$_usg1->GetUsersByRightArr('w', 1098);
		$sm1->assign('can_unconfirm_users',$usg1);
		$sm1->assign('can_unconfirm',$au->user_rights->CheckAccess('w',1098));
		
		
		 
		$sm1->assign('session_id', session_id());
		
		$sm1->assign('can_add_supplier', $au->user_rights->CheckAccess('w',87));
		
	
		$sm1->assign('can_expand_sut', $au->user_rights->CheckAccess('w',1115));
		
		
		
		
		
		
		
		
		$sm1->assign('can_modify', $can_modify);
		$sm1->assign('can_modify_plan', $field_rights['plan']);
		$sm1->assign('can_modify_fact', $field_rights['fact']);
	 
		
		$sm1->assign('can_create', $au->user_rights->CheckAccess('w',1090));  
		
		
		
	 
		//прикрепленные файлы
	 
			 //файлы 
			 $can_modify_files=$can_modify;
			 
			  if(isset($_GET['folder_id'])) $folder_id=abs((int)$_GET['folder_id']);
			  else $folder_id=0;
			 
			  $decorator=new DBDecorator;
			  
			  $decorator->AddEntry(new SqlOrdEntry('pdate',SqlOrdEntry::DESC));
			 // $decorator->AddEntry(new SqlEntry('id',$id, SqlEntry::E));
			$decorator->AddEntry(new UriEntry('id',$id));
			  //$decorator->AddEntry(new SqlEntry('user_d_id',$user_id, SqlEntry::E));
			  
			  
			  $decorator->AddEntry(new SqlEntry('folder_id',$folder_id, SqlEntry::E));
			 $decorator->AddEntry(new UriEntry('folder_id',$folder_id));
		
			  $navi_dec=new DBDecorator;
			  $navi_dec->AddEntry(new UriEntry('action',1));
			  
			  
			  if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
			  else $from=0;
			  
			  if(isset($_GET['to_page'])) $to_page=abs((int)$_GET['to_page']);
			  else $to_page=ITEMS_PER_PAGE;
			  
			  $ffg=new DocVnFileGroup(1,  $id,  new FileDocFolderItem(1,  $id, new DocVnFileItem(1)));;
			  
			  $filetext=$ffg->ShowFiles('doc_file/incard_list.html',  $decorator,$from,$to_page,'ed_doc_vn.php', 'doc_vn_file.html', 'swfupl-js/doc_vn_files.php',  
			  $can_modify_files,  
			  $can_modify_files, 
			 $can_modify_files , 
			  $folder_id, 
			  false, 
			false , 
			 false, 
			 false ,    
			  '_incard',  
			  
			 $can_modify_files,  
			   $result, 
			   $navi_dec,   'file_' 
			   );
		 
				
			$sm1->assign('files', $filetext);
	 
		
		$_dsi=new docstatusitem; $dsi=$_dsi->GetItemById($editing_user['status_id']);
		$editing_user['status_name']=$dsi['name'];
		$sm1->assign('bill', $editing_user);
			
		
		
		//найти рук-ля отдела
		$_ug=new DocVn_UsersSGroup;
		$ruk=$_ug->GetRuk($uis);
		 
		$sm1->assign('ruk', $ruk);
		
		$_upos=new UserPosItem;
		$upos=$_upos->getitembyid($uis['position_id']);
		$uis['position_name']=$upos['name'];
		$sm1->assign('manager', $uis);
		
		$_vh=new DocVn_VyhReasonItem;
		$vh=$_vh->GetItemById($editing_user['vyh_reason_id']);
	 
		$sm1->assign('vh', $vh);
		
		
		//другое ИТОГО
		$_block=new DocVn_ExpensesBlock;
		$sm1->assign('itogof', $_block->CalcFactItogoArr($id,NULL,$result));
		
		$sm1->assign('print_pdate', date("d.m.Y H:i:s"));
		//$username=$result['login'];
		$username=stripslashes($result['name_s']); //.' '.$username;	
		$sm1->assign('print_username',$username);
		
		
		if($printmode==0) $our_template='doc_vn/edit_doc_vn_'.$printmode.'print.html';
		elseif($printmode==3)  $our_template='doc_vn/edit_doc_vn_'.$printmode.'print.html';
		elseif($printmode==4){
			$our_template='doc_vn/edit_doc_vn_0print.html';
			$sm1->assign('has_sign', true);
		}
		elseif($printmode==5){
			$our_template='doc_vn/edit_doc_vn_3print.html';
			$sm1->assign('has_sign', true);
		}
		
		
		$html=$sm1->fetch($our_template);
?>