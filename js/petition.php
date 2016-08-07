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
require_once('../classes/supplieritem.php');
require_once('../classes/opfitem.php');

require_once('../classes/supplier_cities_item.php');
require_once('../classes/supplier_cities_group.php');


require_once('../classes/supplier_city_item.php');
require_once('../classes/supplier_region_item.php');
require_once('../classes/supplier_district_item.php');


require_once('../classes/petition_purpose_item.php');

require_once('../classes/petitionnotesgroup.php');
require_once('../classes/petitionnotesitem.php');
require_once('../classes/petitionitem.php');
require_once('../classes/docstatusitem.php');

require_once('../classes/petitionallgroup.php');

require_once('../classes/petition_purpose_group.php');
require_once('../classes/petition_purpose_item.php');

require_once('../classes/petition_client_group.php');
require_once('../classes/petition_client_item.php');

require_once('../classes/petitionfilegroup.php');
require_once('../classes/petitionfileitem.php');

require_once('../classes/petition_view.class.php');
require_once('../classes/sched.class.php');

require_once('../classes/holy_dates.php');
require_once('../classes/period_checker.php');

require_once('../classes/petition_vyhdate_group.php');

$au=new AuthUser();
$result=$au->Auth(false,false);
$log=new ActionLog;

if($result===NULL){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();		
}


$_editable_status_id=array();
$_editable_status_id[]=1;
$_editable_status_id[]=2;

$ret='';

 
if(isset($_GET['action'])&&($_GET['action']=="load_city")){
	$_si=new SupplierCitiesItem;
	
	$si=$_si->GetOne(abs((int)$_GET['selected_supplier_id']));
	
	if($si===false){
		$ret='{"id":"0", "txt":""}';	
	}else{
		$ret='{"id":"'.$si['id'].'", "txt":"'.SecStr($si['name'].', '.$si['okrug_name'].', '.$si['region_name']).'"}';
		
	}
	
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="find_city")){
	
	$_pg=new SupplierCitiesGroup;
	
	
	$sm=new SmartyAj;
	
	$pg=$_pg->GetItemsByIdArr(abs((int)$_POST['supplier_id']),  abs((int)$_POST['selected_city_id']));
	
	$sm->assign('pos', $pg);
	
	
	$ret=$sm->fetch('mission/cities_list.html');
	
	
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="transfer_city")){
	
	$city_id=abs((int)$_POST['selected_city_id']);
	$supplier_id=abs((int)$_POST['supplier_id']);
	
	$_ci=new SupplierCityItem;
	$_ri=new SupplierRegionItem;
	$_di=new SupplierDistrictItem;
	
	$ci=$_ci->GetItemById($city_id);
	$ri=$_ri->GetItemById($ci['region_id']);
	$di=$_di->GetItemById($ci['district_id']);
	
	$ret=$ci['name'].', '.$di['name'].', '.$ri['name'];
	
}

elseif(isset($_POST['action'])&&($_POST['action']=="add_clients_fake")){
	//добавка примечаний на форму создания заявки
	
	$sm=new SmartyAj;
	
	$sm->assign('tempid',time());
	
	//$sm->assign('pdate',date('d.m.Y H:i:s'));
	//$sm->assign('user_name_s',$result['name_s']);
	//$sm->assign('user_login',$result['login']);
	$sm->assign('client_name',SecStr(iconv("utf-8","windows-1251",$_POST['client_name'])));
	$sm->assign('purpose_txt',SecStr(iconv("utf-8","windows-1251",$_POST['purpose_txt'])));
	$sm->assign('purpose_id',SecStr(iconv("utf-8","windows-1251",$_POST['purpose_id'])));
	
	$_ppi=new petitionpurposeitem; $ppi=$_ppi->GetItemById(abs((int)$_POST['purpose_id']));
	$sm->assign('purpose_name', $ppi['name']);
	
	$sm->assign('can_edit',true);
	
	$sm->assign('word','clients');
	$sm->assign('named','Клиенты');
	
	$ret=$sm->fetch('petition/clients_fake.html');
	
}


//РАБОТА С ПРИМЕЧАНИЯМИ
elseif(isset($_POST['action'])&&($_POST['action']=="redraw_notes")){
	$sm=new SmartyAj;
	
	$user_id=abs((int)$_POST['user_id']);
	
	$rg=new PetitionNotesGroup;
	
	$sm->assign('items',$rg->GetItemsByIdArr($user_id,0,0,false,  $au->user_rights->CheckAccess('w',834), $au->user_rights->CheckAccess('w',835), $result['id']));
	$sm->assign('word','notes');
	$sm->assign('user_id',$user_id);
	$sm->assign('named','Примечания');
	
	$sm->assign('can_edit', $au->user_rights->CheckAccess('w',833));
	
	
	$ret=$sm->fetch('petition/d_notes.html');
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="add_notes")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',833)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	
	
	$ri=new PetitionNotesItem;
	$ri->Add(array(
				'note'=>SecStr(iconv("utf-8","windows-1251",$_POST['note'])),
				'pdate'=>time(),
				'user_id'=>$user_id,
				'posted_user_id'=>$result['id']
			));
	
	$log->PutEntry($result['id'],'добавил примечания по заявлению', NULL,833, NULL,SecStr(iconv("utf-8","windows-1251",$_POST['note'])),$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="edit_notes")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',833)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	$id=abs((int)$_POST['id']);
	
	
	$ri=new PetitionNotesItem;
	$ri->Edit($id,
				array(
				'note'=>SecStr(iconv("utf-8","windows-1251",$_POST['note'])),
				'pdate'=>time(),
				'posted_user_id'=>$result['id']/*,
				'user_id'=>$user_id*/
			));
	
	$log->PutEntry($result['id'],'редактировал примечания по заявлению', NULL,833,NULL, SecStr(iconv("utf-8","windows-1251",$_POST['note'])),$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="delete_notes")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',833)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	$id=abs((int)$_POST['id']);
	
	
	$ri=new PetitionNotesItem;
	$ri->Del($id);
	
	$log->PutEntry($result['id'],'удалил примечания по заявлению', NULL,833,NULL,NULL,$user_id);
	
}


//udalenie-annulirovabie
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_annul")){
	$id=abs((int)$_POST['id']);
	
	if(isset($_POST['from_card'])&&($_POST['from_card']==1)) $from_card=1;
	else $from_card=0;
	
	$note=SecStr(iconv("utf-8","windows-1251",$_POST['note']));
	
	$_ti=new PetitionItem;
	
	$_si=new UserSItem;
	
	$trust=$_ti->getitembyid($id);
	
	$log=new ActionLog;
	$_stat=new DocStatusItem;
	
	
	
	if($_ti->DocCanAnnul($id, $rss2)&&($trust['is_confirmed']==0)){
		//аннулирование	
		if($au->user_rights->CheckAccess('w',827)){
			$_ti->Edit($id,array('status_id'=>3),false,$result);
			
			$stat=$_stat->GetItemById(5);
			$log->PutEntry($result['id'],'аннулирование заявления',NULL,827,NULL,'заявление № '.$trust['code'].': установлен статус '.$stat['name'],$id);	
			
			//уд-ть связанные документы
			//$_ti->AnnulBindedDocuments($id);	
			
			//внести примечание
			$_ni=new PetitionNotesItem;
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
		if($au->user_rights->CheckAccess('w',828)){
			$_ti->Edit($id,array('status_id'=>18, 'restore_pdate'=>time()),false,$result);
			
			$stat=$_stat->GetItemById(2);
			$log->PutEntry($result['id'],'восстановление заявления',NULL,828,NULL,'заявление № '.$trust['code'].': установлен статус '.$stat['name'],$id);
			
			//внести примечание
			$_ni=new PetitionNotesItem;
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
	  $template='petition/petitions.html';
	  
	  
	  
	  $acg=new PetitionAllGroup;
	  
	  $dec=new  DBDecorator;
	  $dec->AddEntry(new SqlEntry('t.id',$id, SqlEntry::E));	
	 
	  $acg->SetAuthResult($result);
	 
	   $ret=$acg->ShowPos($result['id'], //0
	 $template, //1
	 $dec, //2
	 true, //3
	 $au->user_rights->CheckAccess('w',724), //4
	 $alls, //5
	 0,  //6
	 10000, //7
	 $result, //8
	 $au->user_rights->CheckAccess('w',827), //9
	 $au->user_rights->CheckAccess('w',725), //10
	 $au->user_rights->CheckAccess('w',826), //11
	 false, //12
	  $au->user_rights->CheckAccess('w',829), //13
	 $au->user_rights->CheckAccess('w',830), //14
	 $au->user_rights->CheckAccess('w',831), //15
	 $au->user_rights->CheckAccess('w',832), //16
	 $au->user_rights->CheckAccess('w',828),  //17
	  $au->user_rights->CheckAccess('w',1135), //18
		 $au->user_rights->CheckAccess('w',1136)  //19
	 
	 );
	}else{
		$editing_user=$_ti->getitembyid($id);
		$sm=new SmartyAj;
		
		$_stat=new DocStatusItem;
		$stat=$_stat->getitembyid($editing_user['status_id']);
		$editing_user['status']=$stat['name'];
		
		//блок аннулирования
		
		$editing_user['can_annul']=$_ti->DocCanAnnul($editing_user['id'],$reason)&&$au->user_rights->CheckAccess('w',827);
		if(!$au->user_rights->CheckAccess('w',827)) $reason='недостаточно прав для данной операции';
		$editing_user['can_annul_reason']=$reason;
		
	 
		
		$editing_user['can_restore']=$_ti->DocCanRestore($editing_user['id'],$reason)&&$au->user_rights->CheckAccess('w',828);
			if(!$au->user_rights->CheckAccess('w',828)) $reason='недостаточно прав для данной операции';
		
		
		
		$sm->assign('bill',$editing_user);
		$ret=$sm->fetch('petition/toggle_annul_card.html');		
	}
		
	
 

}elseif(isset($_POST['action'])&&($_POST['action']=="check_confirm")){
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new PetitionItem;
		
		
		if(!$_ki->DocCanConfirm($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
	
	
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_confirm")){
	$id=abs((int)$_POST['id']);
	 
	
	$_ti=new PetitionItem;
	$_si=new UserSItem;
	
	$trust=$_ti->getitembyid($id);
	 
	
	 
	
	if($trust['is_confirmed']==1){
		//есть права: либо сам утв.+есть права, либо есть искл. права:
		if($au->user_rights->CheckAccess('w',830)){
			if($_ti->DocCanUnconfirm($id)){
				$_ti->Edit($id,array('is_confirmed'=>0, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'снял утверждение заполнения',NULL,830, NULL, NULL,$id);
				 
					
			}
		} 
		
	}else{
		//есть права
		if($au->user_rights->CheckAccess('w',829)){
			if($_ti->DocCanconfirm($id)){
				$_ti->Edit($id,array('is_confirmed'=>1, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'утвердил заполнение',NULL,829, NULL, NULL,$id);					 	
			}
		} 
	}
	
	
	
	 $template='petition/petitions.html';
	  
	  
	  
	  $acg=new PetitionAllGroup;
	  
	  $dec=new  DBDecorator;
	  $dec->AddEntry(new SqlEntry('t.id',$id, SqlEntry::E));	
	 
	  $acg->SetAuthResult($result);
	 
	   $ret=$acg->ShowPos($result['id'], //0
	 $template, //1
	 $dec, //2
	 true, //3
	 $au->user_rights->CheckAccess('w',724), //4
	 $alls, //5
	 0,  //6
	 10000, //7
	 $result, //8
	 $au->user_rights->CheckAccess('w',827), //9
	 $au->user_rights->CheckAccess('w',725), //10
	 $au->user_rights->CheckAccess('w',826), //11
	 false, //12
	  $au->user_rights->CheckAccess('w',829), //13
	 $au->user_rights->CheckAccess('w',830), //14
	 $au->user_rights->CheckAccess('w',831), //15
	 $au->user_rights->CheckAccess('w',832), //16
	 $au->user_rights->CheckAccess('w',828),  //17
	  $au->user_rights->CheckAccess('w',1135), //18
		 $au->user_rights->CheckAccess('w',1136)  //19
	 
	 );
	 
	
		
 
}
 elseif(isset($_POST['action'])&&($_POST['action']=="toggle_confirm_ruk")){
	$id=abs((int)$_POST['id']);
	$_ti=new PetitionItem;
	
	$_si=new UserSItem;
	
	$trust=$_ti->getitembyid($id);
	 
	$_ug=new UsersSGroup;
	$_ui=new UserSItem; $uis=$_ui->getitembyid($trust['manager_id']);
	$user_ruk=$_ug->GetRuk($uis); 
	
	if($trust['is_ruk']!=0){
		//есть права: либо сам утв.+есть права, либо есть искл. права:
		 if(($au->user_rights->CheckAccess('w',832))||($user_ruk['id']==$result['id'])){
			 
			if($_ti->DocCanUnconfirmRuk($id,$reas)){
			
				$_ti->Edit($id,array('is_ruk'=>0, 'user_ruk_id'=>$result['id'], 'ruk_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'снял утверждение в роли руководителя отдела',NULL,832, NULL, NULL,$id);
				
			}
				
			 
		} 
		
	}else{
		//есть права
		if($au->user_rights->CheckAccess('w',831)||($user_ruk['id']==$result['id'])){
			 
			if($_ti->DocCanConfirmRuk($id,$reas)){
				$_ti->Edit($id,array('is_ruk'=>1, 'user_ruk_id'=>$result['id'], 'ruk_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'утвердил в роли руководителя отдела',NULL,831, NULL, NULL,$id);	
				//	echo 'zzzzzzzzzzzzzzzzzzzzzzz';	
			}
			 
		} 
	}
	
	
	
	
	
	 $template='petition/petitions.html';
	  
	  
	  
	  $acg=new PetitionAllGroup;
	  
	  $dec=new  DBDecorator;
	  $dec->AddEntry(new SqlEntry('t.id',$id, SqlEntry::E));	
	 
	  $acg->SetAuthResult($result);
	 
	   $ret=$acg->ShowPos($result['id'], //0
	 $template, //1
	 $dec, //2
	 true, //3
	 $au->user_rights->CheckAccess('w',724), //4
	 $alls, //5
	 0,  //6
	 10000, //7
	 $result, //8
	 $au->user_rights->CheckAccess('w',827), //9
	 $au->user_rights->CheckAccess('w',725), //10
	 $au->user_rights->CheckAccess('w',826), //11
	 false, //12
	  $au->user_rights->CheckAccess('w',829), //13
	 $au->user_rights->CheckAccess('w',830), //14
	 $au->user_rights->CheckAccess('w',831), //15
	 $au->user_rights->CheckAccess('w',832), //16
	 $au->user_rights->CheckAccess('w',828),  //17
	 $au->user_rights->CheckAccess('w',1135), //18
	 $au->user_rights->CheckAccess('w',1136)  //19
	 );
	 
	
	
		
}

elseif(isset($_POST['action'])&&($_POST['action']=="toggle_confirm_ruk_un")){
	$id=abs((int)$_POST['id']);
	$_ti=new PetitionItem;
	$_si=new UserSItem;
	
	$trust=$_ti->getitembyid($id);
	 
	$_ug=new UsersSGroup;
	$_ui=new UserSItem; $uis=$_ui->getitembyid($trust['manager_id']);
	$user_ruk=$_ug->GetRuk($uis); 
	
	$ruk_not=SecStr(iconv('utf-8', 'windows-1251', $_POST['note']));
	
	 
	//есть права
	if($au->user_rights->CheckAccess('w',831)||($user_ruk['id']==$result['id'])){
		 
		if($_ti->DocCanConfirmRuk($id,$reas)){
			$_ti->Edit($id,array('is_ruk'=>2, 'user_ruk_id'=>$result['id'], 'ruk_pdate'=>time(), 'ruk_not'=>$ruk_not),true,$result);
			
			$log->PutEntry($result['id'],'не утвердил в роли руководителя отдела',NULL,831, NULL, NULL,$id);	
			//	echo 'zzzzzzzzzzzzzzzzzzzzzzz';	
		}
		 
	} 
 
	
	
	
	 $template='petition/petitions.html';
	  
	  
	  
	  $acg=new PetitionAllGroup;
	  
	  $dec=new  DBDecorator;
	  $dec->AddEntry(new SqlEntry('t.id',$id, SqlEntry::E));	
	 
	  $acg->SetAuthResult($result);
	 
	   $ret=$acg->ShowPos($result['id'], //0
	 $template, //1
	 $dec, //2
	 true, //3
	 $au->user_rights->CheckAccess('w',724), //4
	 $alls, //5
	 0,  //6
	 10000, //7
	 $result, //8
	 $au->user_rights->CheckAccess('w',827), //9
	 $au->user_rights->CheckAccess('w',725), //10
	 $au->user_rights->CheckAccess('w',826), //11
	 false, //12
	  $au->user_rights->CheckAccess('w',829), //13
	 $au->user_rights->CheckAccess('w',830), //14
	 $au->user_rights->CheckAccess('w',831), //15
	 $au->user_rights->CheckAccess('w',832), //16
	 $au->user_rights->CheckAccess('w',828),  //17
	 $au->user_rights->CheckAccess('w',1135), //18
	 $au->user_rights->CheckAccess('w',1136)  //19
	 );
	 
	
	
		
}


elseif(isset($_POST['action'])&&($_POST['action']=="toggle_confirm_dir")){
	$id=abs((int)$_POST['id']);
	$_ti=new PetitionItem;
	
	$_si=new UserSItem;
	
	$trust=$_ti->getitembyid($id);
	 
	$_ug=new UsersSGroup;
	$_ui=new UserSItem; $uis=$_ui->getitembyid($trust['manager_id']);
	$user_ruk=$_ug->GetDir($uis); 
	
	if($trust['is_dir']!=0){
		//есть права: либо сам утв.+есть права, либо есть искл. права:
		 if(($au->user_rights->CheckAccess('w',1136))||($user_ruk['id']==$result['id'])){
			 
			if($_ti->DocCanUnconfirmDir($id,$reas)){
			
				$_ti->Edit($id,array('is_dir'=>0, 'user_dir_id'=>$result['id'], 'dir_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'снял утверждение в роли генерального директора',NULL,1136, NULL, NULL,$id);
				
			}
				
			 
		} 
		
	}else{
		//есть права
		if($au->user_rights->CheckAccess('w',1135)||($user_ruk['id']==$result['id'])){
			 
			if($_ti->DocCanConfirmDir($id,$reas)){
				$_ti->Edit($id,array('is_dir'=>1, 'user_dir_id'=>$result['id'], 'dir_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'утвердил в роли генерального директора',NULL,1135, NULL, NULL,$id);	
				//	echo 'zzzzzzzzzzzzzzzzzzzzzzz';	
			}
			 
		} 
	}
	
	
	
	
	
	 $template='petition/petitions.html';
	  
	  
	  
	  $acg=new PetitionAllGroup;
	  
	  $dec=new  DBDecorator;
	  $dec->AddEntry(new SqlEntry('t.id',$id, SqlEntry::E));	
	 
	  $acg->SetAuthResult($result);
	 
	   $ret=$acg->ShowPos($result['id'], //0
	 $template, //1
	 $dec, //2
	 true, //3
	 $au->user_rights->CheckAccess('w',724), //4
	 $alls, //5
	 0,  //6
	 10000, //7
	 $result, //8
	 $au->user_rights->CheckAccess('w',827), //9
	 $au->user_rights->CheckAccess('w',725), //10
	 $au->user_rights->CheckAccess('w',826), //11
	 false, //12
	  $au->user_rights->CheckAccess('w',829), //13
	 $au->user_rights->CheckAccess('w',830), //14
	 $au->user_rights->CheckAccess('w',831), //15
	 $au->user_rights->CheckAccess('w',832), //16
	 $au->user_rights->CheckAccess('w',828),  //17
	 $au->user_rights->CheckAccess('w',1135), //18
	 $au->user_rights->CheckAccess('w',1136)  //19
	 );
	 
	
	
		
} 

elseif(isset($_POST['action'])&&($_POST['action']=="toggle_confirm_dir_un")){
	$id=abs((int)$_POST['id']);
	$_ti=new PetitionItem;
	$_si=new UserSItem;
	
	$trust=$_ti->getitembyid($id);
	 
	$_ug=new UsersSGroup;
	$_ui=new UserSItem; $uis=$_ui->getitembyid($trust['manager_id']);
	$user_ruk=$_ug->GetRuk($uis); 
	
	$ruk_not=SecStr(iconv('utf-8', 'windows-1251', $_POST['note']));
	
	 
	//есть права
	if($au->user_rights->CheckAccess('w',1135)||($user_ruk['id']==$result['id'])){
		 
		if($_ti->DocCanConfirmDir($id,$reas)){
			$_ti->Edit($id,array('is_dir'=>2, 'user_dir_id'=>$result['id'], 'dir_pdate'=>time(), 'dir_not'=>$ruk_not),true,$result);
			
			$log->PutEntry($result['id'],'не утвердил в роли генерального директора',NULL,1135, NULL, NULL,$id);	
			//	echo 'zzzzzzzzzzzzzzzzzzzzzzz';	
		}
		 
	} 
 
	
	
	
	 $template='petition/petitions.html';
	  
	  
	  
	  $acg=new PetitionAllGroup;
	  
	  $dec=new  DBDecorator;
	  $dec->AddEntry(new SqlEntry('t.id',$id, SqlEntry::E));	
	 
	  $acg->SetAuthResult($result);
	 
	   $ret=$acg->ShowPos($result['id'], //0
	 $template, //1
	 $dec, //2
	 true, //3
	 $au->user_rights->CheckAccess('w',724), //4
	 $alls, //5
	 0,  //6
	 10000, //7
	 $result, //8
	 $au->user_rights->CheckAccess('w',827), //9
	 $au->user_rights->CheckAccess('w',725), //10
	 $au->user_rights->CheckAccess('w',826), //11
	 false, //12
	  $au->user_rights->CheckAccess('w',829), //13
	 $au->user_rights->CheckAccess('w',830), //14
	 $au->user_rights->CheckAccess('w',831), //15
	 $au->user_rights->CheckAccess('w',832), //16
	 $au->user_rights->CheckAccess('w',828),  //17
	 $au->user_rights->CheckAccess('w',1135), //18
	 $au->user_rights->CheckAccess('w',1136)  //19
	 );
	 
	
	
		
}

//РАБОТА С КЛИЕНТАМИ по заявлению
elseif(isset($_POST['action'])&&($_POST['action']=="redraw_clients")){
	$sm=new SmartyAj;
	
	$user_id=abs((int)$_POST['user_id']);
	
	$rg=new PetitionClientGroup;
	$_pet=new PetitionItem;
	$pet=$_pet->getitembyid($user_id);
	
	$sm->assign('clients',$rg->GetItemsByIdArr($user_id, $au->user_rights->CheckAccess('w',725)&&in_array($pet['status_id'],$_editable_status_id), $au->user_rights->CheckAccess('w',725)&&in_array($pet['status_id'],$_editable_status_id)));
	$sm->assign('word','clients');
	$sm->assign('user_id',$user_id);
	$sm->assign('named','Клиенты');
	
	//$sm->assign('can_edit', $au->user_rights->CheckAccess('w',833));
	
	
	$ret=$sm->fetch('petition/clients.html');
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="add_clients")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',725)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	

	$_ppi=new PetitionPurposeItem;
	$ppi=$_ppi->GetItemById(abs((int)$_POST['purpose_id']));
	
	$_pet=new PetitionItem;
	$pet=$_pet->getitembyid($user_id);
	
	$_pci=new PetitionClientItem;
	$params=array();
	$params['petition_id']=$user_id;
	$params['name']=SecStr(iconv("utf-8","windows-1251",$_POST['client_name']));
	$params['purpose_id']=abs((int)$_POST['purpose_id']);
	$params['purpose_txt']=SecStr(iconv("utf-8","windows-1251",$_POST['purpose_txt']));
	
	
	$_pci->Add($params);
	
	
	$log->PutEntry($result['id'],'добавил клиента по заявлению', NULL,725, NULL,'Номер заявления: '.$pet['code'].' Клиент: '.$params['name'].', цель визита: '.SecStr($ppi['name']).' '.$params['purpose_txt'],$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="edit_clients")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',725)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	$id=abs((int)$_POST['id']);
	
	$_pet=new PetitionItem;
	$pet=$_pet->getitembyid($user_id);
	
	$ri=new PetitionClientItem;
	$rri=$ri->GetItemById($id);
	
	$_ppi=new PetitionPurposeItem;
	$ppi=$_ppi->GetItemById(abs((int)$_POST['purpose_id']));
	
	
	$params=array();
	//$params['petition_id']=$user_id;
	$params['name']=SecStr(iconv("utf-8","windows-1251",$_POST['client_name']));
	$params['purpose_id']=abs((int)$_POST['purpose_id']);
	$params['purpose_txt']=SecStr(iconv("utf-8","windows-1251",$_POST['purpose_txt']));
	
	
	$ri->Edit($id, $params);
	
 
	$log->PutEntry($result['id'],'редактировал клиента по заявлению', NULL,725,NULL,  'Номер заявления: '.$pet['code'].' Клиент: '.$params['name'].', цель визита: '.SecStr($ppi['name']).' '.$params['purpose_txt'],$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="delete_clients")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',725)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	$id=abs((int)$_POST['id']);
	
	
	$_pet=new PetitionItem;
	$pet=$_pet->getitembyid($user_id);
	
	 
	
	$ri=new PetitionClientItem;
	$rri=$ri->GetItemById($id);
	
	$ri->Del($id);
	
	$log->PutEntry($result['id'],'удалил клиента по заявлению', NULL,725,NULL,'Номер заявления: '.$pet['code'].' Клиент: '.SecStr($rri['name']).'',$user_id);
	
 
//работа с датами работы в выходные
}elseif(isset($_POST['action'])&&($_POST['action']=="load_vyh_date")){
	
	$complex_positions=$_POST['complex_positions'];
	
	$dates=array(); $ids=time();
	foreach($complex_positions as $k=>$v){
		$valarr=explode(';',$v);
		$ids++;
		$dates[]=array(
			'id'=>$ids,
			'pdate'=>$valarr[0],
			'time_from_h'=>$valarr[1],
			'time_from_m'=>$valarr[2],
			'time_to_h'=>$valarr[3],
			'time_to_m'=>$valarr[4]
		);
	}
	
	if(count($dates)==0) $dates[]=array(
			'id'=>$ids,
			'pdate'=>'',
			'time_from_h'=>'09',
			'time_from_m'=>'00',
			'time_to_h'=>'18',
			'time_to_m'=>'00'
		);
	
	$sm=new SmartyAj;
	
	$sm->assign('vyh_dates', $dates);
	$sm->assign('can_modify', true);
	
	$ret=$sm->fetch('petition/vyh_dates_edit.html');
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="transfer_empty_date")){
	 $dates=array(); $ids=time();
	 $dates[]=array(
			'id'=>$ids,
			'pdate'=>'',
			'time_from_h'=>'09',
			'time_from_m'=>'00',
			'time_to_h'=>'18',
			'time_to_m'=>'00'
		);
	
	$sm=new SmartyAj;
	
	$sm->assign('vyh_dates', $dates);
	$sm->assign('can_modify', true);
	
	$ret=$sm->fetch('petition/vyh_dates_edit_row.html');
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="transfer_vyh_date")){
	
	$complex_positions=$_POST['complex_positions'];
	
	$dates=array(); $ids=time();
	foreach($complex_positions as $k=>$v){
		$valarr=explode(';',$v);
		$ids++;
		$dates[]=array(
			'id'=>$ids,
			'pdate'=>$valarr[0],
			'w_day'=>PetitionVyhDateGroup::$weekdays[(int)date('w', Datefromdmy($valarr[0]))],
			'time_from_h'=>$valarr[1],
			'time_from_m'=>$valarr[2],
			'time_to_h'=>$valarr[3],
			'time_to_m'=>$valarr[4]
		);
		
		//echo '<tr><td>';
		//var_dump(PetitionVyhDateGroup::$weekdays[(int)date('w', Datefromdmy($valarr[0]))]);
	}
	
	 
	
	$sm=new SmartyAj;
	
	$sm->assign('vyh_dates', $dates);
	$sm->assign('can_modify', true);
	
	$ret=$sm->fetch('petition/vyh_dates_onpage.html');
	
	
 

//работа с клиентами в режиме +
}elseif(isset($_POST['action'])&&($_POST['action']=="load_cli")){
	
	$complex_positions=$_POST['complex_positions'];
	
	$dates=array(); $ids=time();
	foreach($complex_positions as $k=>$v){
		$valarr=explode(';',$v);
		$ids++;
		$dates[]=array(
			'id'=>$ids,
			'name'=>iconv('utf-8', 'windows-1251', $valarr[0]),
			'purpose_id'=>$valarr[1],
			'purpose_txt'=>iconv('utf-8', 'windows-1251',$valarr[2]) 
		);
	}
	
	if(count($dates)==0) $dates[]=array(
			'id'=>$ids,
			'name'=>'',
			'purpose_id'=>'0',
			'purpose_txt'=>'' 
		);
	
	$sm=new SmartyAj;
	
	$_ppg=new PetitionPurposeGroup;
	$ppg=$_ppg->GetItemsArr(0);
	$sm->assign('purposes', $ppg);
	
	$sm->assign('clients', $dates);
	$sm->assign('can_modify', true);
	
	$ret=$sm->fetch('petition/cli_edit.html');
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="transfer_empty_cli")){
	 $dates=array(); $ids=time();
	 $dates[]=array(
			'id'=>$ids,
			'name'=>'',
			'purpose_id'=>'0',
			'purpose_txt'=>'' 
		);
	
	$sm=new SmartyAj;
	
	$sm->assign('clients', $dates);
	$sm->assign('can_modify', true);
	
	$_ppg=new PetitionPurposeGroup;
	$ppg=$_ppg->GetItemsArr(0);
	$sm->assign('purposes', $ppg);
	
	$ret=$sm->fetch('petition/cli_edit_row.html');
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="transfer_cli")){
	
	$complex_positions=$_POST['complex_positions'];
	$_pi=new PetitionPurposeItem;
	
	
	$dates=array(); $ids=time();
	foreach($complex_positions as $k=>$v){
		$valarr=explode(';',$v);
		$ids++;
		
		$pi=$_pi->GetItemById($valarr[1]);	
		$dates[]=array(
			'id'=>$ids,
			'name'=>iconv('utf-8', 'windows-1251',$valarr[0]),
			'purpose_id'=>$valarr[1],
			'purpose_txt'=>iconv('utf-8', 'windows-1251',$valarr[2]),
			'purpose_name'=>$pi['name']
		);
	}
	
	 
	
	$sm=new SmartyAj;
	
	$sm->assign('clients', $dates);
	$sm->assign('can_modify', true);
	
	$ret=$sm->fetch('petition/cli_onpage.html');
	
	
 

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
	
	$ret=$sm->fetch('petition/vyh_otp_dates_edit.html');
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="transfer_empty_otp_date")){
	 $dates=array(); $ids=time();
	 $dates[]=array(
			'id'=>$ids,
			'pdate'=>''
		);
	
	$sm=new SmartyAj;
	
	$sm->assign('vyh_otp_dates', $dates);
	$sm->assign('can_modify', true);
	
	$ret=$sm->fetch('petition/vyh_otp_dates_edit_row.html');
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="transfer_vyh_otp_date")){
	
	$complex_positions=$_POST['complex_positions'];
	
	$dates=array(); $ids=time();
	foreach($complex_positions as $k=>$v){
		$valarr=explode(';',$v);
		$ids++;
		$dates[]=array(
			'id'=>$ids,
			'pdate'=>$valarr[0],
			'w_day'=>PetitionVyhDateGroup::$weekdays[(int)date('w', Datefromdmy($valarr[0]))],
		);
	}
	
	 
	
	$sm=new SmartyAj;
	
	$sm->assign('vyh_otp_dates', $dates);
	$sm->assign('can_modify', true);
	
	$ret=$sm->fetch('petition/vyh_otp_dates_onpage.html');
	
	
}


//настройка реестра
elseif(isset($_POST['action'])&&(($_POST['action']=="mode_reestr"))){
	$_views=new Petition_ViewGroup;
	$_view=new Petition_ViewItem;
	
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
	$_views=new Petition_ViewGroup;
 
	 
	
	$_views->Clear($result['id']);
	 
}
elseif(isset($_POST['action'])&&(($_POST['action']=="find_missions"))){
	
	
	$_pg=new Sched_Group;
	
	$decorator=new DBDecorator;
	
	$manager_id=abs((int)$_POST['manager_id']);
 
	$viewed_ids=$_pg->GetAvailableUserIds($manager_id,false,3);
		$prefix=3;	
	
 
	
	$decorator->AddEntry(new SqlEntry('p.manager_id', NULL, SqlEntry::IN_VALUES, NULL,$viewed_ids));	
	
 
	//Только текущего сотрудника, только вып. встречи???, только по москве и области? только статус Утвержден!
	$decorator->AddEntry(new SqlEntry('p.manager_id',$manager_id, SqlEntry::E));
	 
	$decorator->AddEntry(new SqlEntry('p.id','select distinct sr.sched_id from  sched_cities as sr inner join sprav_city as u on u.id=sr.city_id inner join sprav_region as sc on sc.id=u.region_id where sc.name LIKE "%Московская область%" ', SqlEntry::IN_SQL));
	
	
	//статус Запланирован!
	$decorator->AddEntry(new SqlEntry('p.status_id',22, SqlEntry::E));
	
	//даты встречи не должны быть заняты в других документах вида 6 текущего сотрудника
	$sql='select distinct p.given_pdate from    petition as p    where p.is_dir=1 and p.manager_id="'.$manager_id.'" and p.id<>"'.abs((int)$_POST['id']).'" and p.kind_id=6';
	
	// echo '<tr><td>';
	//echo $sql;
	$set=new mysqlset($sql);
	$rs=$set->GetResult();
	$rc=$set->GetResultNumRows();
	$except_dates=array();
	for($i=0; $i<$rc; $i++){
		$f=mysqli_fetch_array($rs);
		$except_dates[]=$f[0];//date('Y-m-d',$f[0]);
		
		//echo date('Y-m-d',$f[0]);
	}
	
	 
	/*echo '<tr><td>';
	var_dump($except_dates);
	  
	 */
	$_except_dates=array();
	foreach($except_dates as $pdate) $_except_dates[]=' (pc.pdate_beg<="'.date('Y-m-d', $pdate).'" and pc.pdate_end>="'.date('Y-m-d', $pdate).'")';
	
	if(count($_except_dates)>0){
		
		$vyhs=implode(' or ', $_except_dates);
		
//		echo 'select distinct pc.id from  sched as pc where '.$vyhs.'';
		$decorator->AddEntry(new SqlEntry('p.id','select distinct pc.id from  sched as pc where '.$vyhs.'', SqlEntry::NOT_IN_SQL));
	}
	
	
	
	
		
	
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
			 'petition/mission_list.html', //1
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
	
	//определить место и доп. инфо
	$_kind=new Sched_KindMeetItem;
	$kind=$_kind->GetItemById($si['meet_id']);
	
	$si['meet_name']=$kind['name'];  
	
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
	$ret=$sm->fetch('petition/suppliers_table.html');	
} 
 



elseif(isset($_POST['action'])&&($_POST['action']=="load_pdf_addresses")){
	
	$id=abs((int)$_POST['id']);
	
	$_item=new PetitionItem;
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
	$ret=$sm->fetch('petition/pdf_addresses.html');

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
	
	
	
	
	$ffg=new PetitionFileGroup(1,  $id,  new FileDocFolderItem(1,  $id, new PetitionFileItem(1)));;
			  
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
	
	$sm->assign('items', $alls);
	$ret=$sm->fetch('petition/pdf_files.html');

}



elseif(isset($_POST['action'])&&($_POST['action']=="has_files")){
//есть ли файлы по записи план-ка?
	$count_of_files=0;	
	$id=abs((int)$_POST['id']);
	
	$sql='select count(*) from petition_file where user_d_id="'.$id.'" ';
	
	$set=new mysqlset($sql);
	$rs=$set->GetResult();
	
	$f=mysqli_fetch_array($rs);
	
	$count_of_files+=(int)$f[0];
	
 
	
	$ret=$count_of_files;
}

    

/***************************************************************************************************/
//работа с командировками/встречами по заявлению на отпуск за работу в выходные
elseif(isset($_POST['action'])&&(($_POST['action']=="find_zmissions"))){
	
	
	
	$_pg=new Sched_Group;
	
	$decorator=new DBDecorator;
	
	$manager_id=abs((int)$_POST['manager_id']);
	
	$vyh_reason_id=abs((int)$_POST['vyh_reason_id']);
	
	if($vyh_reason_id==1){
		//выставка	
		$viewed_ids=$_pg->GetAvailableUserIds($manager_id,false,3);
		$prefix=3;
	}elseif($vyh_reason_id==2){
		//командировка
		$viewed_ids=$_pg->GetAvailableUserIds($manager_id,false,2);
		$prefix=2;
	}elseif($vyh_reason_id==4){
		//встреча
		$viewed_ids=$_pg->GetAvailableUserIds($manager_id,false,3);
		$prefix=3;
		
	}else{
			$prefix=2;
			$viewed_ids=$_pg->GetAvailableUserIds($manager_id,false,2);
	
	}
		 
		
	
	

 	//условия выбора встречи-ком-ки:
	
	 
	//1. только текущего сотрудника
	$decorator->AddEntry(new SqlEntry('p.manager_id',$manager_id, SqlEntry::E));
	
	
	//2. в период В или К попадают выходные дни
	$_hd=new HolyDates; $_pch=new PeriodChecker; $init_pdate=datefromdmy($_pch->GetDate());
	$nowd=mktime(0,0,0, date("m"), date('d'), date('Y'));
	$_vyhs=array(); $curr_pdate=$init_pdate;
	while($curr_pdate<=$nowd){
		
		if($_hd->IsHolyday($curr_pdate)) $_vyhs[]=' (pc.pdate_beg<="'.date('Y-m-d', $curr_pdate).'" and pc.pdate_end>="'.date('Y-m-d', $curr_pdate).'")';
		
		$curr_pdate+=24*60*60;	
	}
	
 	/*echo '<tr><td>';
	var_dump($_vyhs);
	echo '</td></tr>'; */
	if(count($_vyhs)>0){
		
		$vyhs=implode(' or ', $_vyhs);
		$decorator->AddEntry(new SqlEntry('p.id','select distinct pc.id from  sched as pc where pc.kind_id=p.kind_id and ('.$vyhs.')', SqlEntry::IN_SQL));
	}
	
	
	//3. ТРЕТЬЯ галочка 
	$decorator->AddEntry(new SqlEntry('p.is_fulfiled',1, SqlEntry::E));
	
	
	//4. есть хотя бы 1 выходной день, не расписанный в других заявлениях (с 3 галочкой is_dir==1)
	//исключить дни, расписанные в других заявлениях вида 3 этого сотрудника, имеющие галочку is_dir==1
	//что значит исключить дни? значит составить список дат, которые не должны попадать в интервалы показываемых командировок/встреч 
	//формируем такой список
	
	$sql='select distinct pc.pdate from  petition_vyh_date as pc inner join petition as p on pc.petition_id=p.id  where p.is_dir=1 and p.manager_id="'.$manager_id.'" and p.id<>"'.abs((int)$_POST['id']).'" and p.kind_id=3';
	
	// echo '<tr><td>';
	//echo $sql;
	$set=new mysqlset($sql);
	$rs=$set->GetResult();
	$rc=$set->GetResultNumRows();
	$except_dates=array();
	for($i=0; $i<$rc; $i++){
		$f=mysqli_fetch_array($rs);
		$except_dates[]=$f[0];//date('Y-m-d',$f[0]);
	}
	
	/* 
	var_dump($except_dates);
	  
	*/
	$_except_dates=array();
	foreach($except_dates as $pdate) $_except_dates[]=' (pc.pdate_beg<="'.date('Y-m-d', $pdate).'" and pc.pdate_end>="'.date('Y-m-d', $pdate).'")';
	
	if(count($_except_dates)>0){
		
		$vyhs=implode(' or ', $_except_dates);
		
//		echo 'select distinct pc.id from  sched as pc where '.$vyhs.'';
		$decorator->AddEntry(new SqlEntry('p.id','select distinct pc.id from  sched as pc where '.$vyhs.'', SqlEntry::NOT_IN_SQL));
	}
	//echo '</td></tr>';
	
	 
	
	 	
	
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
			 'petition/mission_list.html', //1
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
	$ret=$sm->fetch('petition/cities_table.html');		
 
 
 
}elseif(isset($_POST['action'])&&($_POST['action']=="transfer_mission_vyh_date")){
	//подгрузка доступных дат при выборе командировки
	$id=abs((int)$_POST['id']);
	$manager_id=abs((int)$_POST['manager_id']);
	 
	$_pi=new PetitionItem;
	
	$dates=$_pi->GetDatesByMission($id,0, $manager_id); 
	
	$sm=new SmartyAj;
	
	$sm->assign('vyh_dates', $dates);
	$sm->assign('can_modify', true);
	
	$ret=$sm->fetch('petition/vyh_dates_onpage.html');
	
 
}elseif(isset($_POST['action'])&&($_POST['action']=="check_vyh_date")){
	//проверка дат  - корректны ли - при редактировании дат
	$dates=$_POST['dates']; $dates1=array();
	foreach($dates as $v) $dates1[]=DateFromdmY($v);
	$id=abs((int)$_POST['id']);
	$sched_id=abs((int)$_POST['sched_id']);
	$user_id=abs((int)$_POST['user_id']);
	
	$_pi=new PetitionItem;
	
	$res=$_pi->CheckByVyhDates($dates1, $sched_id, $user_id, $id, $rss); //CheckByVyhDates($dates, $sched_id=0, $user_id=0, $except_id=0, &$rss)
	if($res) $ret=0;
	else{
		$ret=$rss;	
	}

 

}elseif(isset($_POST['action'])&&($_POST['action']=="check_vyh_otp_date")){
	//проверка дат  - корректны ли - при редактировании дат
	$dates=$_POST['dates']; $dates1=array();
	foreach($dates as $v) $dates1[]=DateFromdmY($v);
	$id=abs((int)$_POST['id']);
	$sched_id=abs((int)$_POST['sched_id']);
	$user_id=abs((int)$_POST['user_id']);
	
	$_pi=new PetitionItem;
	
	$res=$_pi->CheckByVyhOtpDates($dates1, $sched_id, $user_id, $id, $rss);  
	if($res) $ret=0;
	else{
		$ret=$rss;	
	}

 
}elseif(isset($_POST['action'])&&($_POST['action']=="check_mission_date")){
	//проверка дат  - корректны ли - при редактировании дат
	/*$dates=$_POST['dates']; $dates1=array();
	foreach($dates as $v) $dates1[]=DateFromdmY($v);*/
	
	$pdate=datefromdmy($_POST['pdate']);
	
	$id=abs((int)$_POST['id']);
	$sched_id=abs((int)$_POST['sched_id']);
	$user_id=abs((int)$_POST['user_id']);
	
	$_pi=new PetitionItem;
	
	$res=$_pi->CheckMissionDate($pdate, $sched_id, $user_id, $id, $rss);
	if($res) $ret=0;
	else{
		$ret=$rss;	
	}

}



//подгрузка пол-лей для выбора ответственного
elseif(isset($_POST['action'])&&($_POST['action']=="load_users")){
 
	$manager_id=abs((int)$_POST['manager_id']);
	$complex_positions=$_POST['complex_positions'];
	$except_users=$_POST['except_users'];
	
 
	$_kpg=new  UsersSGroup;
	
 	$dec=new DBDecorator;
	
	$limited_user=NULL;
	if($au->FltUser($result)){
		//echo 'z';
		$_u_to_u=new UserToUser();
		$u_to_u=$_u_to_u->GetExtendedViewedUserIdsArr($result['id']);
		$limited_user=$u_to_u['sector_ids'];
		$dec->AddEntry(new SqlEntry('u.id', NULL, SqlEntry::IN_VALUES, NULL,$limited_user));	
	}
	
	
	if(is_array($except_users)&&(count($except_users)>0)){
		$dec->AddEntry(new SqlEntry('u.id', NULL, SqlEntry::NOT_IN_VALUES, NULL,$except_users));
	}
	
	$dec->AddEntry(new SqlEntry('u.is_active', 1, SqlEntry::E));
	$dec->AddEntry(new SqlEntry('u.id', $result['id'], SqlEntry::NE));
	
	$dec->AddEntry(new SqlEntry('u.id', 2, SqlEntry::NE));
	$dec->AddEntry(new SqlEntry('u.name_s', "Резерв", SqlEntry::NE));
	
	$dec->AddEntry(new SqlOrdEntry('name_s',SqlOrdEntry::ASC));
	
	$alls=$_kpg->GetItemsByDecArr($dec);  
	 
  
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
	 
	 
	
 
	
	$ret.=$sm->fetch("petition/managers_set.html");
	
	 
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
		
	
		
		$_ki=new PetitionItem;
		
		
		if(!$_ki->DocCanConfirmDir($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
	
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="check_unconfirm_dir")){
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new PetitionItem;
		
		
		if(!$_ki->DocCanUnconfirmDir($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
	
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="check_confirm_ruk")){
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new PetitionItem;
		
		
		if(!$_ki->DocCanConfirmRuk($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
	
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="check_unconfirm_ruk")){
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new PetitionItem;
		
		
		if(!$_ki->DocCanUnconfirmRuk($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
	
	
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="check_vyh_bol_date")){
	//проверка дат  - корректны ли - при редактировании дат
	$dates=$_POST['dates']; $dates1=array();
	foreach($dates as $v) $dates1[]=DateFromdmY($v);
	$id=abs((int)$_POST['id']);
	 
	$user_id=abs((int)$_POST['user_id']);
	
	$_pi=new PetitionItem;
	
	$res=$_pi->CheckByVyhBolDates($dates1,  $user_id, $id, $rss);  
	if($res) $ret=0;
	else{
		$ret=$rss;	
	}

 
}

//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	

?>