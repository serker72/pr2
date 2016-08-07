<?
session_start();
header('Content-type: text/html; charset=windows-1251');

require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/discr_table_objects.php');
require_once('../classes/actionlog.php');

require_once('../classes/suppliersgroup.php');
require_once('../classes/user_s_group.php');

require_once('../classes/docstatusitem.php');

require_once('../classes/memoallgroup.php');

 

require_once('../classes/memofilegroup.php');
require_once('../classes/memofileitem.php');

require_once('../classes/memo_view.class.php');
require_once('../classes/sched.class.php');


require_once('../classes/memonotesgroup.php');
require_once('../classes/memonotesitem.php');
require_once('../classes/memo_field_rules.php');
require_once('../classes/memoitem.php');


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


if(isset($_POST['action'])&&($_POST['action']=="find_users")){
	 
	
	//получим список позиций по фильтру
	$_pg=new UsersSGroup;
	
	$dec=new DBDecorator;
	
	
	
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['login'])))>0) $dec->AddEntry(new SqlEntry('p.login',SecStr(iconv("utf-8","windows-1251",$_POST['login'])), SqlEntry::LIKE));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['name_s'])))>0) $dec->AddEntry(new SqlEntry('p.name_s',SecStr(iconv("utf-8","windows-1251",$_POST['name_s'])), SqlEntry::LIKE));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['position_s'])))>0) $dec->AddEntry(new SqlEntry('up.name',SecStr(iconv("utf-8","windows-1251",$_POST['position_s'])), SqlEntry::LIKE));
	
		
	if(isset($_POST['except_ids'])&&is_array($_POST['except_ids'])){
		foreach($_POST['except_ids'] as $k=>$v){
			$dec->AddEntry(new SqlEntry('p.id',abs((int)$v), SqlEntry::NE));	
		}
		
	}
	
	
	$ret=$_pg->GetItemsForBill('memo/users_list.html',  $dec,true,$all7,$result);
	

	
}elseif(isset($_POST['action'])&&($_POST['action']=="transfer_users")){
	$_pg=new UsersSGroup;
	
	$dec=new DBDecorator;
	
	//$dec->AddEntry(new SqlEntry('p.is_org',1, SqlEntry::NE));
	
	
	$dec->AddEntry(new SqlEntry('p.id',NULL, SqlEntry::IN_VALUES, NULL, $_POST['selected_ids']));
	
	$ret=$_pg->GetItemsForBill('memo/users_list_in_card.html',  $dec,true,$all7,$result);
		
}

//подсветка утверждений карты
elseif(isset($_POST['action'])&&($_POST['action']=="redraw_is_confirmed_confirmer")){
	 
		$ret=$result['position_name'].' '.$result['name_s'].' '.' '.$result['login'].' '.date("d.m.Y H:i:s",time());	
	 
}




//настройка реестра
elseif(isset($_POST['action'])&&(($_POST['action']=="mode_reestr"))){
	$_views=new Memo_ViewGroup;
	$_view=new Memo_ViewItem;
	
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
	$_views=new Memo_ViewGroup;
 
	 
	
	$_views->Clear($result['id']);
	 
}



//РАБОТА С ПРИМЕЧАНИЯМИ
elseif(isset($_POST['action'])&&($_POST['action']=="redraw_notes")){
	$sm=new SmartyAj;
	
	$user_id=abs((int)$_POST['user_id']);
	
	$rg=new MemoNotesGroup;
	
	$sm->assign('items',$rg->GetItemsByIdArr($user_id,0,0,false,  $au->user_rights->CheckAccess('w',1128), $au->user_rights->CheckAccess('w',1129), $result['id']));
	$sm->assign('word','notes');
	$sm->assign('user_id',$user_id);
	$sm->assign('named','Примечания');
	
	$sm->assign('can_edit', $au->user_rights->CheckAccess('w',1127));
	
	
	$ret=$sm->fetch('memo/d_notes.html');
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="add_notes")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',1127)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	
	
	$ri=new MemoNotesItem;
	$ri->Add(array(
				'note'=>SecStr(iconv("utf-8","windows-1251",$_POST['note'])),
				'pdate'=>time(),
				'user_id'=>$user_id,
				'posted_user_id'=>$result['id']
			));
	
	$log->PutEntry($result['id'],'добавил примечания по служебной записке', NULL,1127, NULL,SecStr(iconv("utf-8","windows-1251",$_POST['note'])),$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="edit_notes")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',1127)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	$id=abs((int)$_POST['id']);
	
	
	$ri=new MemoNotesItem;
	$ri->Edit($id,
				array(
				'note'=>SecStr(iconv("utf-8","windows-1251",$_POST['note'])),
				'pdate'=>time(),
				'posted_user_id'=>$result['id']/*,
				'user_id'=>$user_id*/
			));
	
	$log->PutEntry($result['id'],'редактировал примечания по служебной записке', NULL,1127,NULL, SecStr(iconv("utf-8","windows-1251",$_POST['note'])),$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="delete_notes")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',1127)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	$id=abs((int)$_POST['id']);
	
	
	$ri=new MemoNotesItem;
	$ri->Del($id);
	
	$log->PutEntry($result['id'],'удалил примечания по служебной записке', NULL,1127,NULL,NULL,$user_id);
	
}



//udalenie-annulirovabie
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_annul")){
	$id=abs((int)$_POST['id']);
	
	if(isset($_POST['from_card'])&&($_POST['from_card']==1)) $from_card=1;
	else $from_card=0;
	
	$note=SecStr(iconv("utf-8","windows-1251",$_POST['note']));
	
	$_ti=new MemoItem;
	
	$_si=new UserSItem;
	
	$trust=$_ti->getitembyid($id);
	
	$log=new ActionLog;
	$_stat=new DocStatusItem;
	
	
	
	if($_ti->DocCanAnnul($id, $rss2)&&($trust['is_confirmed']==0)){
		//аннулирование	
		if($au->user_rights->CheckAccess('w',1125)){
			$_ti->Edit($id,array('status_id'=>3),false,$result);
			
			$stat=$_stat->GetItemById(5);
			$log->PutEntry($result['id'],'аннулирование служебной записки',NULL,1125,NULL,'заявление № '.$trust['code'].': установлен статус '.$stat['name'],$id);	
			
			//уд-ть связанные документы
			//$_ti->AnnulBindedDocuments($id);	
			
			//внести примечание
			$_ni=new MemoNotesItem;
			$_ni->Add(array(
				'user_id'=>$id,
				'posted_user_id'=>$result['id'],
				'note'=>'Автоматическое примечание: документ был аннулирован пользователем '.SecStr($result['name_s']).' ('.$result['login'].'), причина: '.$note,
				'is_auto'=>1,
				'pdate'=>time()
					));	
		}
	}elseif($_ti->DocCanRestore($id, $rss2)){
		//разудаление
		if($au->user_rights->CheckAccess('w',1126)){
			$_ti->Edit($id,array('status_id'=>18, 'restore_pdate'=>time()),false,$result);
			
			$stat=$_stat->GetItemById(2);
			$log->PutEntry($result['id'],'восстановление служебной записки',NULL,1126,NULL,'служебная записка № '.$trust['code'].': установлен статус '.$stat['name'],$id);
			
			//внести примечание
			$_ni=new MemoNotesItem;
			$_ni->Add(array(
				'user_id'=>$id,
				'posted_user_id'=>$result['id'],
				'note'=>'Автоматическое примечание: документ был восстановлен пользователем '.SecStr($result['name_s']).' ('.$result['login'].')',
				'is_auto'=>1,
				'pdate'=>time()
					));		
			
		}
		
	}
	
	if($from_card==0){
	  $shorter=abs((int)$_POST['shorter']);
	  $template='memo/memos.html';
	  
	  
	  
	  $acg=new MemoAllGroup;
	  
	  $dec=new  DBDecorator;
	  $dec->AddEntry(new SqlEntry('t.id',$id, SqlEntry::E));	
	 
	  $acg->SetAuthResult($result);
	 
	   $ret=$acg->ShowPos($result['id'], //0
	 $template, //1
	 $dec, //2
	 true, //3	 
	 $au->user_rights->CheckAccess('w',730), //4
	 $alls, //5
	 0,  //6
	 10000, //7
	 $result, //8
	$au->user_rights->CheckAccess('w',1125), //9
	 $au->user_rights->CheckAccess('w',731), //10
	 $au->user_rights->CheckAccess('w',1124), //11
	 false,
	 $au->user_rights->CheckAccess('w',735), //13
	 $au->user_rights->CheckAccess('w',737),  //14
	 $au->user_rights->CheckAccess('w',1120),  //15
		 $au->user_rights->CheckAccess('w',1121),  //16
		 
		 $au->user_rights->CheckAccess('w',1126), //17
		 
		 $au->user_rights->CheckAccess('w',1122), //18
  	     $au->user_rights->CheckAccess('w',1123)  //19
	 
	 );
	}else{
		$editing_user=$_ti->getitembyid($id);
		$sm=new SmartyAj;
		
		$_stat=new DocStatusItem;
		$stat=$_stat->getitembyid($editing_user['status_id']);
		$editing_user['status']=$stat['name'];
		
		//блок аннулирования
		
		$editing_user['can_annul']=$_ti->DocCanAnnul($editing_user['id'],$reason)&&$au->user_rights->CheckAccess('w',1125);
		if(!$au->user_rights->CheckAccess('w',1125)) $reason='недостаточно прав для данной операции';
		$editing_user['can_annul_reason']=$reason;
		
	 
		
		$editing_user['can_restore']=$_ti->DocCanRestore($editing_user['id'],$reason)&&$au->user_rights->CheckAccess('w',1126);
			if(!$au->user_rights->CheckAccess('w',1126)) $reason='недостаточно прав для данной операции';
		
		
		
		$sm->assign('bill',$editing_user);
		$ret=$sm->fetch('memo/toggle_annul_card.html');		
	}
		
 


}elseif(isset($_POST['action'])&&($_POST['action']=="check_confirm")){
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new MemoItem;
		
		
		if(!$_ki->DocCanConfirm($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
	
	
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_confirm")){
	$id=abs((int)$_POST['id']);
	 
	
	$_ti=new MemoItem;
	$_si=new UserSItem;
	
	$trust=$_ti->getitembyid($id);
	 
	
	 
	
	if($trust['is_confirmed']==1){
		//есть права: либо сам утв.+есть права, либо есть искл. права:
		if($au->user_rights->CheckAccess('w',737)){
			if($_ti->DocCanUnconfirm($id)){
				$_ti->Edit($id,array('is_confirmed'=>0, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'снял утверждение заполнения',NULL,737, NULL, NULL,$id);
				 
					
			}
		} 
		
	}else{
		//есть права
		if($au->user_rights->CheckAccess('w',735)){
			if($_ti->DocCanconfirm($id)){
				$_ti->Edit($id,array('is_confirmed'=>1, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'утвердил заполнение',NULL,735, NULL, NULL,$id);					 	
			}
		} 
	}
	
	
	
	 $template='memo/memos.html';
	  
	  
	  
	  $acg=new MemoAllGroup;
	  
	  $dec=new  DBDecorator;
	  $dec->AddEntry(new SqlEntry('t.id',$id, SqlEntry::E));	
	 
	  $acg->SetAuthResult($result);
	 
	   $ret=$acg->ShowPos($result['id'], //0
	 $template, //1
	 $dec, //2
	true, //3	 
	 $au->user_rights->CheckAccess('w',730), //4
	 $alls, //5
	 0,  //6
	 10000, //7
	 $result, //8
	$au->user_rights->CheckAccess('w',1125), //9
	 $au->user_rights->CheckAccess('w',731), //10
	 $au->user_rights->CheckAccess('w',1124), //11
	 false,
	 $au->user_rights->CheckAccess('w',735), //13
	 $au->user_rights->CheckAccess('w',737),  //14
	 $au->user_rights->CheckAccess('w',1120),  //15
		 $au->user_rights->CheckAccess('w',1121),  //16
		 
		 $au->user_rights->CheckAccess('w',1126), //17
		 
		 $au->user_rights->CheckAccess('w',1122), //18
  	     $au->user_rights->CheckAccess('w',1123)  //19
	 );
	 
	
		
}
 
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_confirm_ruk")){
	$id=abs((int)$_POST['id']);
	$_ti=new MemoItem;
	$_si=new UserSItem;
	
	$trust=$_ti->getitembyid($id);
	 
	$_ug=new UsersSGroup;
	$_ui=new UserSItem; $uis=$_ui->getitembyid($trust['manager_id']);
	$user_ruk=$_ug->GetRuk($uis); 
	
	if($trust['is_ruk']!=0){
		//есть права: либо сам утв.+есть права, либо есть искл. права:
		 if(($au->user_rights->CheckAccess('w',1121))||($user_ruk['id']==$result['id'])){
			 
			if($_ti->DocCanUnconfirmRuk($id,$reas)){
			
				$_ti->Edit($id,array('is_ruk'=>0, 'user_ruk_id'=>$result['id'], 'ruk_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'снял утверждение в роли руководителя отдела',NULL,1121, NULL, NULL,$id);
				
			}
				
			 
		} 
		
	}else{
		//есть права
		if($au->user_rights->CheckAccess('w',1120)||($user_ruk['id']==$result['id'])){
			 
			if($_ti->DocCanConfirmRuk($id,$reas)){
				$_ti->Edit($id,array('is_ruk'=>1, 'user_ruk_id'=>$result['id'], 'ruk_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'утвердил в роли руководителя отдела',NULL,1120, NULL, NULL,$id);	
				//	echo 'zzzzzzzzzzzzzzzzzzzzzzz';	
			}
			 
		} 
	}
	
	
	
	
	
	 $template='memo/memos.html';
	  
	  
	  
	  $acg=new MemoAllGroup;
	  
	  $dec=new  DBDecorator;
	  $dec->AddEntry(new SqlEntry('t.id',$id, SqlEntry::E));	
	 
	  $acg->SetAuthResult($result);
	 
	   $ret=$acg->ShowPos($result['id'], //0
	 $template, //1
	 $dec, //2
	true, //3	 
	 $au->user_rights->CheckAccess('w',730), //4
	 $alls, //5
	 0,  //6
	 10000, //7
	 $result, //8
	$au->user_rights->CheckAccess('w',1125), //9
	 $au->user_rights->CheckAccess('w',731), //10
	 $au->user_rights->CheckAccess('w',1124), //11
	 false,
	 $au->user_rights->CheckAccess('w',735), //13
	 $au->user_rights->CheckAccess('w',737),  //14
	 $au->user_rights->CheckAccess('w',1120),  //15
		 $au->user_rights->CheckAccess('w',1121),  //16
		 
		 $au->user_rights->CheckAccess('w',1126), //17
		 
		 $au->user_rights->CheckAccess('w',1122), //18
  	     $au->user_rights->CheckAccess('w',1123)  //19
	 );
	 
	
	
		
}


elseif(isset($_POST['action'])&&($_POST['action']=="toggle_confirm_ruk_un")){
	$id=abs((int)$_POST['id']);
	$_ti=new MemoItem;
	$_si=new UserSItem;
	
	$trust=$_ti->getitembyid($id);
	 
	$_ug=new UsersSGroup;
	$_ui=new UserSItem; $uis=$_ui->getitembyid($trust['manager_id']);
	$user_ruk=$_ug->GetRuk($uis); 
	
	$ruk_not=SecStr(iconv('utf-8', 'windows-1251', $_POST['note']));
	
	 
	//есть права
	if($au->user_rights->CheckAccess('w',1120)||($user_ruk['id']==$result['id'])){
		 
		if($_ti->DocCanConfirmRuk($id,$reas)){
			$_ti->Edit($id,array('is_ruk'=>2, 'user_ruk_id'=>$result['id'], 'ruk_pdate'=>time(), 'ruk_not'=>$ruk_not),true,$result);
			
			$log->PutEntry($result['id'],'не утвердил в роли руководителя отдела',NULL,1120, NULL, NULL,$id);	
			//	echo 'zzzzzzzzzzzzzzzzzzzzzzz';	
		}
		 
	} 
 
	
	
	
	
	 $template='memo/memos.html';
	  
	  
	  
	  $acg=new MemoAllGroup;
	  
	  $dec=new  DBDecorator;
	  $dec->AddEntry(new SqlEntry('t.id',$id, SqlEntry::E));	
	 
	  $acg->SetAuthResult($result);
	 
	   $ret=$acg->ShowPos($result['id'], //0
	 $template, //1
	 $dec, //2
	true, //3	 
	 $au->user_rights->CheckAccess('w',730), //4
	 $alls, //5
	 0,  //6
	 10000, //7
	 $result, //8
	$au->user_rights->CheckAccess('w',1125), //9
	 $au->user_rights->CheckAccess('w',731), //10
	 $au->user_rights->CheckAccess('w',1124), //11
	 false,
	 $au->user_rights->CheckAccess('w',735), //13
	 $au->user_rights->CheckAccess('w',737),  //14
	 $au->user_rights->CheckAccess('w',1120),  //15
		 $au->user_rights->CheckAccess('w',1121),  //16
		 
		 $au->user_rights->CheckAccess('w',1126), //17
		 
		 $au->user_rights->CheckAccess('w',1122), //18
  	     $au->user_rights->CheckAccess('w',1123)  //19
	 );
	 
	
	
		
}

elseif(isset($_POST['action'])&&($_POST['action']=="toggle_confirm_dir")){
	$id=abs((int)$_POST['id']);
		$_ti=new MemoItem;
	 $_si=new UserSItem;
	
	$trust=$_ti->getitembyid($id);
	 
	$_ug=new UsersSGroup;
	$_ui=new UserSItem; $uis=$_ui->getitembyid($trust['manager_id']);
	$user_ruk=$_ug->GetDir($uis); 
	
	if($trust['is_dir']!=0){
		//есть права: либо сам утв.+есть права, либо есть искл. права:
		 if(($au->user_rights->CheckAccess('w',1123))||($user_ruk['id']==$result['id'])){
			 
			if($_ti->DocCanUnconfirmDir($id,$reas)){
			
				$_ti->Edit($id,array('is_dir'=>0, 'user_dir_id'=>$result['id'], 'dir_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'снял утверждение в роли генерального директора',NULL,1123, NULL, NULL,$id);
				
			}
				
			 
		} 
		
	}else{
		//есть права
		if($au->user_rights->CheckAccess('w',1122)||($user_ruk['id']==$result['id'])){
			 
			if($_ti->DocCanConfirmDir($id,$reas)){
				$_ti->Edit($id,array('is_dir'=>1, 'user_dir_id'=>$result['id'], 'dir_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'утвердил в роли генерального директора',NULL,1122, NULL, NULL,$id);	
				//	echo 'zzzzzzzzzzzzzzzzzzzzzzz';	
			}
			 
		} 
	}
	
	
	
	
	 $template='memo/memos.html';
	  
	  
	  
	  $acg=new MemoAllGroup;
	  
	  $dec=new  DBDecorator;
	  $dec->AddEntry(new SqlEntry('t.id',$id, SqlEntry::E));	
	 
	  $acg->SetAuthResult($result);
	 
	   $ret=$acg->ShowPos($result['id'], //0
	 $template, //1
	 $dec, //2
	true, //3	 
	 $au->user_rights->CheckAccess('w',730), //4
	 $alls, //5
	 0,  //6
	 10000, //7
	 $result, //8
	$au->user_rights->CheckAccess('w',1125), //9
	 $au->user_rights->CheckAccess('w',731), //10
	 $au->user_rights->CheckAccess('w',1124), //11
	 false,
	 $au->user_rights->CheckAccess('w',735), //13
	 $au->user_rights->CheckAccess('w',737),  //14
	 $au->user_rights->CheckAccess('w',1120),  //15
		 $au->user_rights->CheckAccess('w',1121),  //16
		 
		 $au->user_rights->CheckAccess('w',1126), //17
		 
		 $au->user_rights->CheckAccess('w',1122), //18
  	     $au->user_rights->CheckAccess('w',1123)  //19
	 );
	 
	
	
		
}


elseif(isset($_POST['action'])&&($_POST['action']=="toggle_confirm_dir_un")){
	$id=abs((int)$_POST['id']);
	$_ti=new MemoItem;
	$_si=new UserSItem;
	
	$trust=$_ti->getitembyid($id);
	 
	$_ug=new UsersSGroup;
	$_ui=new UserSItem; $uis=$_ui->getitembyid($trust['manager_id']);
	$user_ruk=$_ug->GetRuk($uis); 
	
	$ruk_not=SecStr(iconv('utf-8', 'windows-1251', $_POST['note']));
	
	 
	//есть права
	if($au->user_rights->CheckAccess('w',1122)||($user_ruk['id']==$result['id'])){
		 
		if($_ti->DocCanConfirmDir($id,$reas)){
			$_ti->Edit($id,array('is_dir'=>2, 'user_dir_id'=>$result['id'], 'dir_pdate'=>time(), 'dir_not'=>$ruk_not),true,$result);
			
			$log->PutEntry($result['id'],'не утвердил в роли генерального директора',NULL,1122, NULL, NULL,$id);	
			//	echo 'zzzzzzzzzzzzzzzzzzzzzzz';	
		}
		 
	} 
 
	
	
	
	
	 $template='memo/memos.html';
	  
	  
	  
	  $acg=new MemoAllGroup;
	  
	  $dec=new  DBDecorator;
	  $dec->AddEntry(new SqlEntry('t.id',$id, SqlEntry::E));	
	 
	  $acg->SetAuthResult($result);
	 
	   $ret=$acg->ShowPos($result['id'], //0
	 $template, //1
	 $dec, //2
	true, //3	 
	 $au->user_rights->CheckAccess('w',730), //4
	 $alls, //5
	 0,  //6
	 10000, //7
	 $result, //8
	$au->user_rights->CheckAccess('w',1125), //9
	 $au->user_rights->CheckAccess('w',731), //10
	 $au->user_rights->CheckAccess('w',1124), //11
	 false,
	 $au->user_rights->CheckAccess('w',735), //13
	 $au->user_rights->CheckAccess('w',737),  //14
	 $au->user_rights->CheckAccess('w',1120),  //15
		 $au->user_rights->CheckAccess('w',1121),  //16
		 
		 $au->user_rights->CheckAccess('w',1126), //17
		 
		 $au->user_rights->CheckAccess('w',1122), //18
  	     $au->user_rights->CheckAccess('w',1123)  //19
	 );
	 
	
	
		
}
 
 
elseif(isset($_POST['action'])&&($_POST['action']=="load_pdf_addresses")){
	
	$id=abs((int)$_POST['id']);
	
	$_item=new MemoItem;
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
	$ret=$sm->fetch('memo/pdf_addresses.html');

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
	
	
	
	
	$ffg=new MemoFileGroup(1,  $id,  new FileDocFolderItem(1,  $id, new MemoFileItem(1)));;
			  
			  $filetext=$ffg->ShowFiles('doc_file/incard_list.html',  $decorator,0,1000,'ed_doc_out.php', 'doc_out_file.html', 'swfupl-js/doc_out_files.php',    
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
	
	$_item=new MemoItem;
	$item=$_item->getitembyid($id);
	
	$_rr=new Memo_FieldRules;
	$field_rights=$_rr->GetFields($item,$result['id'], $item['status_id']);
	
	$sm->assign('field_rights', $field_rights);
	$sm->assign('items', $alls);
	$ret=$sm->fetch('memo/pdf_files.html');

}



elseif(isset($_POST['action'])&&($_POST['action']=="has_files")){
//есть ли файлы по записи план-ка?
	$count_of_files=0;	
	$id=abs((int)$_POST['id']);
	
	$sql='select count(*) from memo_file where bill_id="'.$id.'" ';
	
	$set=new mysqlset($sql);
	$rs=$set->GetResult();
	
	$f=mysqli_fetch_array($rs);
	
	$count_of_files+=(int)$f[0];
	
 
	
	$ret=$count_of_files;
}

elseif(isset($_GET['action'])&&($_GET['action']=="retrieve_zmanager")){
	$_si=new UserSItem;
	
	$si=$_si->GetItemById(abs((int)$_GET['id']));
	
	 
 
	
	if($si!==false){
		$rret=array();
		foreach($si as $k=>$v){
			 
			
			$rret[]='"'.$k.'":"'.htmlspecialchars(str_replace("\r", "", str_replace("\n",  "",  $v))).'"';
		}
		
		 
		
		$ret='{'.implode(', ',$rret).'}';
	}
	
 
	
  	
 
}elseif(isset($_POST['action'])&&($_POST['action']=="check_confirm_dir")){
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new MemoItem;
		
		
		if(!$_ki->DocCanConfirmDir($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
	
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="check_unconfirm_dir")){
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new MemoItem;
		
		
		if(!$_ki->DocCanUnconfirmDir($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
	
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="check_confirm_ruk")){
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new MemoItem;
		
		
		if(!$_ki->DocCanConfirmRuk($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
	
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="check_unconfirm_ruk")){
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new MemoItem;
		
		
		if(!$_ki->DocCanUnconfirmRuk($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
	
	
	
}
    

//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	

?>