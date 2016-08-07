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

require_once('../classes/bdr.class.php');


require_once('../classes/bdr_view.class.php');
require_once('../classes/pl_prodgroup.php');
require_once('../classes/pl_posgroup.php');

require_once('../classes/currency/currency_solver.class.php');

 

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
  
if(isset($_POST['action'])&&($_POST['action']=="redraw_p")){
	
		//$id=abs((int)$_POST['id']);
		$fields=$_POST['fields'];
		
		$accounts=array(
			'ids'=>array(), //айди статей
			'data'=>array() //переданные данные
		);
		foreach($fields as $k=>$v){
			$valarr=explode('|',$v);
			
			$accounts['ids'][]=$valarr[0];
			$accounts['data'][$valarr[0]]=array(
				'id'=>$valarr[0],
				'cost'=>$valarr[1],
				'notes'=>iconv('utf-8','windows-1251',$valarr[2])
			)	;
		}
		
		
		$_block=new BDR_P_Block;
		
		$sm=new SmartyAj;
		$data=$_block->ConstructByAccounts($accounts);
		
		$sm->assign('items',$data);
		$sm->assign('can_modify',true);
		
		$ret=$sm->fetch('bdr/p_table.html');
		
		//print_r($accounts);
		 
}

elseif(isset($_POST['action'])&&($_POST['action']=="redraw_m")){
	
		//$id=abs((int)$_POST['id']);
		$fields=$_POST['fields'];

		if(isset($_POST['adding_field'])) $adding_field=abs((int)$_POST['adding_field']);
		else $adding_field=NULL;
		
		$kp_in_id=abs((int)$_POST['kp_in_id']);	
		
		$accounts=array(
			'ids'=>array(), //айди статей
			'data'=>array() //переданные данные
		);
		foreach($fields as $k=>$v){
			$valarr=explode('|',$v);
			
			$accounts['ids'][]=$valarr[0];
			$accounts['data'][$valarr[0]]=array(
				'id'=>$valarr[0],
				'cost'=>$valarr[1],
				'notes'=>iconv('utf-8','windows-1251',$valarr[2])
			)	;
		}
		
		
		$_block=new BDR_M_Block;
		
		//adding_field
		if($adding_field!==NULL){
			//нужно расширить блок accounts за счет добавления в него записи adding_field и всех ее предков
			//получить массив предков
			$parent_ids=array(); $parent_ids[]=$adding_field;
			$_block->GetParents($adding_field, $parent_ids);
			
			//перебрать массив предков, если их нет в блоке accounts - внести с нулевой стоимостью и пустым примечанием
			
			foreach($parent_ids as $k=>$node){
				if(!in_array($node, $accounts['ids'])) {
					$accounts['ids'][]=$node;
					$accounts['data'][$node]=array(
						'id'=>$node,
						'cost'=>0,
						'notes'=>''
					)	;	
				}
			}
			
		}
			
		
		/*
		echo '<pre>';
		var_dump($adding_field);
		var_dump($accounts);
		echo '</pre>';*/
		
		$sm=new SmartyAj;
		$data=$_block->ConstructByAccounts($accounts, $kp_in_id);
		
		$sm->assign('items',$data);
		$sm->assign('can_modify',true);
		$sm->assign('has_header',true);

		
		$ret=$sm->fetch('bdr/m_table.html');
		
		//print_r($accounts);
		 
}
 
//расчет прибыли
  
elseif(isset($_GET['action'])&&($_GET['action']=="calc_gain")){
	
		//$id=abs((int)$_POST['id']);
		$mfields=$_GET['mfields'];
		
		$maccounts=array(
			'ids'=>array(), //айди статей
			'data'=>array() //переданные данные
		);
		foreach($mfields as $k=>$v){
			$valarr=explode('|',$v);
			
			$maccounts['ids'][]=$valarr[0];
			$maccounts['data'][$valarr[0]]=array(
				'id'=>$valarr[0],
				'cost'=>$valarr[1],
				'notes'=>iconv('utf-8','windows-1251',$valarr[2])
			)	;
		}
		
		
		$pfields=$_GET['pfields'];
		
		$paccounts=array(
			'ids'=>array(), //айди статей
			'data'=>array() //переданные данные
		);
		foreach($pfields as $k=>$v){
			$valarr=explode('|',$v);
			
			$paccounts['ids'][]=$valarr[0];
			$paccounts['data'][$valarr[0]]=array(
				'id'=>$valarr[0],
				'cost'=>$valarr[1],
				'notes'=>iconv('utf-8','windows-1251',$valarr[2])
			)	;
		}
		
		$id=abs((int)$_GET['id']);
		$version_id=abs((int)$_GET['version_id']);
		
		
		$_item=new BDR_Item;
		
		 
		$ret=json_encode( $_item->CalcGainByAccounst($id, $version_id, $maccounts, $paccounts));
		 
		//print_r($accounts);
		 
}


//работа со справочником затрат
elseif(isset($_POST['action'])&&($_POST['action']=="find_branches")){
	$branch_id=abs((int)$_POST['branch_id']);
	$_bg=new BDR_AccountGroup;
	
	$sm=new SmartyAj;
	
	$_sbi=new BDR_AccountItem;
	$sbi=$_sbi->getitembyid($branch_id);
	
	
	$sm->assign('pos', $_bg->LoadBranchArr($branch_id, 1));
	$sm->assign('can_edit_branch', $au->user_rights->CheckAccess('w',1053));
	
	$sm->assign('parent_id', $branch_id);
	if($sbi!==false) $sm->assign('parent_parent_id', $sbi['parent_id']);
	else $sm->assign('parent_parent_id',0);
	
	$ret=$sm->fetch('bdr/maccounts_list.html');
	


}elseif(isset($_POST['action'])&&($_POST['action']=="add_branch")){
	
	if(!$au->user_rights->CheckAccess('w',1053)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	
	$branch_id=abs((int)$_POST['branch_id']);
	$name=SecStr(iconv("utf-8","windows-1251",$_POST['name']));
	$code=SecStr(iconv("utf-8","windows-1251",$_POST['code']));
	
	$_sbi=new BDR_AccountItem;
	
	$_sbi->Add(array('parent_id'=>$branch_id, 'name'=>$name,'code'=>$code,'p_or_m'=>1));
	
	$log->PutEntry($result['id'],'добавил статью затрат', NULL,1053, NULL,$name);	
			

 

}elseif(isset($_POST['action'])&&($_POST['action']=="del_branch")){
	
	if(!$au->user_rights->CheckAccess('w',1053)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	
	$branch_id=abs((int)$_POST['branch_id']);
	 
	$_sbi=new BDR_AccountItem;
	
	$sbi=$_sbi->GetItemById($branch_id);
	
	$_sbi->Del($branch_id);
	
	$log->PutEntry($result['id'],'удалил статью затрат', NULL,1053, NULL,SecStr($sbi['name']));	


}elseif(isset($_POST['action'])&&($_POST['action']=="edit_branch")){
	
	if(!$au->user_rights->CheckAccess('w',1053)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	
	$branch_id=abs((int)$_POST['branch_id']);
	$name=SecStr(iconv("utf-8","windows-1251",$_POST['name']));
	$code=SecStr(iconv("utf-8","windows-1251",$_POST['code']));
	
	$_sbi=new BDR_AccountItem;
	$sbi=$_sbi->GetItemById($branch_id);
	
	$_sbi->Edit($branch_id, array( 'name'=>$name, 'code'=>$code));
	
	$log->PutEntry($result['id'],'отредактировал статью затрат', NULL,1053, NULL,'Старое название: '.SecStr($sbi['name']).', новое название: '.$name);	
			
			

}
elseif(isset($_GET['action'])&&($_GET['action']=="retrieve_branch")){
	$_si=new BDR_AccountItem;
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






 if(isset($_POST['action'])&&($_POST['action']=="check_unconfirm_price")){
	
		$id=abs((int)$_POST['id']);
		
	
		
		  
		$_dem=new BDR_AbstractItem;
		$dem=$_dem->Getitembyid($id);
		
		$_res=new BDR_Resolver( );
		
		
		$_ki=$_res->instance;
		
		
		
		if(!$_ki->DocCanUnconfirmPrice($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
}elseif(isset($_POST['action'])&&($_POST['action']=="check_confirm_price")){
		$id=abs((int)$_POST['id']);
		
	
		
		  
		$_dem=new BDR_AbstractItem;
		$dem=$_dem->Getitembyid($id);
		
		$_res=new BDR_Resolver( );
		
		
		$_ki=$_res->instance;
		
		
		
		if(!$_ki->DocCanConfirmPrice($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
	
 

}elseif(isset($_POST['action'])&&($_POST['action']=="check_confirm")){
		$id=abs((int)$_POST['id']);
		
	
		
		  
		$_dem=new BDR_AbstractItem;
		$dem=$_dem->Getitembyid($id);
		
		$_res=new BDR_Resolver($dem['kind_id']);
		
		
		$_ki=$_res->instance;
		
		
		
		if(!$_ki->DocCanConfirmShip($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
	

}elseif(isset($_POST['action'])&&($_POST['action']=="check_unconfirm")){
		$id=abs((int)$_POST['id']);
		
	
		
		  
		$_dem=new BDR_AbstractItem;
		$dem=$_dem->Getitembyid($id);
		
		$_res=new BDR_Resolver($dem['kind_id']);
		
		
		$_ki=$_res->instance;
		
		
		
		if(!$_ki->DocCanUnConfirmShip($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
	
} 

//udalenie-annulirovabie
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_annul")){
	$id=abs((int)$_POST['id']);
	
	if(isset($_POST['from_card'])&&($_POST['from_card']==1)) $from_card=1;
	else $from_card=0;
	
	$note=SecStr(iconv("utf-8","windows-1251",$_POST['note']));
	
	$_ki=new BDR_AbstractItem;
	
		$trust=$_ki->getitembyid($id);
		
	$_res=new BDR_Resolver($trust['kind_id']);
	$_ti=$_res->instance;	
	
	$_si=new UserSItem;
	

	
	$log=new ActionLog;
	$_stat=new DocStatusItem;
	
	
	
	if(($trust['status_id']==1)&&($trust['is_confirmed']==0)){
		//удаление	
		if($au->user_rights->CheckAccess('w',1044)){
			$_ti->Edit($id, NULL, array('status_id'=>3), array(), false,$result);
			
			$stat=$_stat->GetItemById(3);
			$log->PutEntry($result['id'],'аннулирование БДР',NULL,1044,NULL,'БДР '.$trust['code'].': установлен статус '.$stat['name'].' , причина: '.$note,$id);	
			
			 
			 	 
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
		if($au->user_rights->CheckAccess('w',1045)){
			$_ti->Edit($id, NULL, array('status_id'=>1, 'restore_pdate'=>time()), array(), false,$result);
			
			$stat=$_stat->GetItemById(1);
			$log->PutEntry($result['id'],'восстановление БДР',NULL,1045,NULL,'БДР № '.$trust['code'].': установлен статус '.$stat['name'],$id);
			
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
	 
	 
			$template='bdr/table.html';
	 
	 $prefix='_bdrs';
	  
	  $acg=new BDR_Group;
	   $acg->setauthresult($result);
	  
	  $dec=new  DBDecorator;
	  
	  $dec->AddEntry(new SqlEntry('t.id',$id, SqlEntry::E));
	
	
	  $ret=$acg->ShowPos(
		

			'bdr/table.html',  //0
			 $dec, //1
			 false, //2
			  $au->user_rights->CheckAccess('w',1041), //3
			  0, //4
			  10000, //5
			  false, //6
			  true,  //7
			   $au->user_rights->CheckAccess('w',1044), //8
			  $au->user_rights->CheckAccess('w',1045),  //9
			  $au->user_rights->CheckAccess('w',1042), //10
			  $au->user_rights->CheckAccess('w',1043), //11
			  $au->user_rights->CheckAccess('w',1050), //12
			   $au->user_rights->CheckAccess('w',1051), //13
			    $au->user_rights->CheckAccess('w',1046), //14
			  
			  
			$prefix //15
			 );
	  
	 
	}else{
		$editing_user=$_ti->getitembyid($id);
		$sm=new SmartyAj;
		
		
		
		//блок аннулирования
		
		$editing_user['can_annul']=$_ti->DocCanAnnul($editing_user['id'],$reason, NULL, $result)&&$au->user_rights->CheckAccess('w',1044);
		if(!$au->user_rights->CheckAccess('w',1044)) $reason='недостаточно прав для данной операции';
		$editing_user['can_annul_reason']=$reason;
		
		//$editing_user['binded_to_annul']=$_ti->GetBindedDocumentsToAnnul($editing_user['id']);
		
		
		$editing_user['can_restore']=$_ti->DocCanRestore($editing_user['id'],$reason)&&$au->user_rights->CheckAccess('w',1045);
			if(!$au->user_rights->CheckAccess('w',1045)) $reason='недостаточно прав для данной операции';
		
		$stat=$_stat->Getitembyid($editing_user['status_id']);
		$editing_user['status_name']=$stat['name'];
		
		$sm->assign('bill',$editing_user);
		$ret=$sm->fetch('bdr/toggle_annul_card.html');		
	}
		
}

 

//utv- razutv
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_confirm_price")){
	$id=abs((int)$_POST['id']);
	 
	
		$_ki=new BDR_AbstractItem;
	
		$trust=$_ki->getitembyid($id);
		
	$_res=new BDR_Resolver($trust['kind_id']);
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
		if($au->user_rights->CheckAccess('w',1043)&&$_ti->DocCanUnconfirmPrice($id, $rss)){
			    
				$_ti->Edit($id,NULL, array(), array('is_confirmed'=>0, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()), true,$result);
				
				$log->PutEntry($result['id'],'снял утверждение заполнения БДР',NULL,1043, NULL, NULL,$id);
				 
					
			 
		} 
		
	}else{
		//есть права
		if($au->user_rights->CheckAccess('w',1042)&&$_ti->DocCanConfirmPrice($id, $rss)){
			 
				$_ti->Edit($id,NULL, array(), array('is_confirmed'=>1, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'утвердил заполнения  БДР',NULL,1042, NULL, NULL,$id);	
				
			 	
			 
		} 
	}
	
	
	
	$acg=new BDR_Group;
	
	$shorter=abs((int)$_POST['shorter']);
	 
		
			$template='bdr/table.html';
	 
	 $prefix='_bdrs';
	  
	  $acg=new BDR_Group;
	   $acg->setauthresult($result);
	  
	  $dec=new  DBDecorator;
	  
	  $dec->AddEntry(new SqlEntry('t.id',$id, SqlEntry::E));
	
	
	  $ret=$acg->ShowPos(
		

			'bdr/table.html',  //0
			 $dec, //1
			 false, //2
			  $au->user_rights->CheckAccess('w',1041), //3
			  0, //4
			  10000, //5
			  false, //6
			  true,  //7
			   $au->user_rights->CheckAccess('w',1044), //8
			  $au->user_rights->CheckAccess('w',1045),  //9
			  $au->user_rights->CheckAccess('w',1042), //10
			  $au->user_rights->CheckAccess('w',1043), //11
			  $au->user_rights->CheckAccess('w',1050), //12
			   $au->user_rights->CheckAccess('w',1051), //13
			    $au->user_rights->CheckAccess('w',1046), //14
			  
			  
			$prefix //15
			 );
	  
		
 
    

}elseif(isset($_POST['action'])&&($_POST['action']=="redraw_is_confirmed_price_confirmer")){
	$state=abs((int)$_POST['state']);
	if($state==0){
		$ret='';	
	}elseif($state==1){
		$ret=$result['position_s'].' '.$result['name_s'].' '.date("d.m.Y H:i:s",time());	
	}
	
}
 
 elseif(isset($_POST['action'])&&($_POST['action']=="toggle_confirm_shipping")){
	$id=abs((int)$_POST['id']);
	  
	//$note=SecStr(iconv('utf-8', 'windows-1251', $_POST['note']));
	
		$_ki=new BDR_AbstractItem;
	
		$trust=$_ki->getitembyid($id);
		
	$_res=new BDR_Resolver($trust['kind_id']);
	$_ti=$_res->instance;	
	
	$_si=new UserSItem;
	
	
	 
	 
	if($trust['confirm_done_pdate']==0) $trust['confirm_done_pdate']='-';
	else $trust['confirm_done_pdate']=date("d.m.Y H:i:s",$trust['confirm_done_pdate']);
	
	
	$si=$_si->getitembyid($trust['user_confirm_done_id']);
	$trust['confirmed_shipping_name']=$si['name_s'];
	$trust['confirmed_shipping_login']=$si['login'];
	
	$bill_id=$id;
	
	if($trust['is_confirmed_version']==1){
		//есть права: либо сам утв.+есть права, либо есть искл. права:
		if(($au->user_rights->CheckAccess('w',1051))){
			 
			if($_ti->DocCanUnconfirmShip($id,$reas)){
			
				$_ti->Edit($id, NULL, array(), array('is_confirmed_version'=>0, 'user_confirm_version_id'=>$result['id'], 'confirm_version_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'снял утверждение прибыли БДР',NULL,1051, NULL, 'результат звонка: '.$note,$bill_id);
				
			}
				
		}
		
	}else{
		//есть права
		if($au->user_rights->CheckAccess('w',1050)){
			 
			if($_ti->DocCanConfirmShip($id,$reas)){
				$_ti->Edit($id,NULL, array(), array('is_confirmed_version'=>1, 'user_confirm_version_id'=>$result['id'], 'confirm_version_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'утвердил прибыль БДР',NULL,1050, NULL, NULL,$bill_id);
				
				
				 
				//	echo 'zzzzzzzzzzzzzzzzzzzzzzz';	
			}
			 
		} 
	}
	
	
		$template='bdr/table.html';
	 
	 $prefix='_bdrs';
	  
	  $acg=new BDR_Group;
	   $acg->setauthresult($result);
	  
	  $dec=new  DBDecorator;
	  
	  $dec->AddEntry(new SqlEntry('t.id',$id, SqlEntry::E));
	
	
	  $ret=$acg->ShowPos(
		

			'bdr/table.html',  //0
			 $dec, //1
			 false, //2
			  $au->user_rights->CheckAccess('w',1041), //3
			  0, //4
			  10000, //5
			  false, //6
			  true,  //7
			   $au->user_rights->CheckAccess('w',1044), //8
			  $au->user_rights->CheckAccess('w',1045),  //9
			  $au->user_rights->CheckAccess('w',1042), //10
			  $au->user_rights->CheckAccess('w',1043), //11
			  $au->user_rights->CheckAccess('w',1050), //12
			   $au->user_rights->CheckAccess('w',1051), //13
			    $au->user_rights->CheckAccess('w',1046), //14
			  
			  
			$prefix //15
			 );
	  
		
	
		
} 



//РАБОТА С ПРИМЕЧАНИЯМИ
elseif(isset($_POST['action'])&&($_POST['action']=="redraw_notes")){
	$sm=new SmartyAj;
	
	$user_id=abs((int)$_POST['user_id']);
	
	$rg=new BDRNotesGroup;
	
	$sm->assign('items',$rg->GetItemsByIdArr($user_id,0,0,false, $au->user_rights->CheckAccess('w',1048), $au->user_rights->CheckAccess('w',1049), $result['id']));
	$sm->assign('word','notes');
	$sm->assign('user_id',$user_id);
	$sm->assign('named','Примечания');
	
	$sm->assign('can_edit', $au->user_rights->CheckAccess('w',1047));
	
	
	$ret=$sm->fetch('bdr/d_notes.html');
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="add_notes")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',1047)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	
	
	$ri=new BdrNotesItem;
	$ri->Add(array(
				'note'=>SecStr(iconv("utf-8","windows-1251",$_POST['note'])),
				'pdate'=>time(),
				'user_id'=>$user_id,
				'posted_user_id'=>$result['id']
			));
	
	$log->PutEntry($result['id'],'добавил примечания по БДР', NULL,1047, NULL,SecStr(iconv("utf-8","windows-1251",$_POST['note']),9),$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="edit_notes")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',1047)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	$id=abs((int)$_POST['id']);
	
	
	$ri=new BdrNotesItem;
	$ri->Edit($id,
				array(
				'note'=>SecStr(iconv("utf-8","windows-1251",$_POST['note'])),
				'pdate'=>time(),
				'posted_user_id'=>$result['id']/*,
				'user_id'=>$user_id*/
			));
	
	$log->PutEntry($result['id'],'редактировал примечания по БДР', NULL,1047,NULL, SecStr(iconv("utf-8","windows-1251",$_POST['note']),9),$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="delete_notes")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',1047)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	$id=abs((int)$_POST['id']);
	
	
	$ri=new BdrNotesItem;
	$ri->Del($id);
	
	$log->PutEntry($result['id'],'удалил примечания по БДР', NULL,1047,NULL,NULL,$user_id);
	
}



//настройка реестра
elseif(isset($_POST['action'])&&(($_POST['action']=="mode_reestr"))){
	$_views=new BDR_ViewsGroup;
	$_view=new BDR_ViewsItem;
	
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
	$_views=new BDR_ViewsGroup;
	$_view=new BDR_ViewsItem;
	
	 
	
	$_views->Clear($result['id']);
	 
 


}elseif(isset($_GET['action'])&&($_GET['action']=="load_courses_by_date")){
	//dostup
	 
	$pdate=$_GET['pdate'];
	
	$_solver=new CurrencySolver();
	$rates=$_solver->GetToDate($pdate);
	
	$data=array();
	
	$data['dol']=round(CurrencySolver::Convert(1, $rates, 3, 1),5);
	$data['eur']=round(CurrencySolver::Convert(1, $rates, 2, 1),5);
	
	$ret=json_encode($data);
	
	/*
	$id=abs((int)$_POST['id']);
	
	
	$ri=new BdrNotesItem;
	$ri->Del($id);
	
	$log->PutEntry($result['id'],'удалил примечания по БДР', NULL,1047,NULL,NULL,$user_id);*/
	
}


elseif(isset($_POST['action'])&&($_POST['action']=="load_pdf_addresses")){
	
	$id=abs((int)$_POST['id']);
	
	$_item=new BDR_Item;
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
			where ( supplier_id in(select distinct supplier_id from lead_suppliers where  sched_id="'.$item['lead_id'].'"))
			and id in(select distinct contact_id from supplier_contact_data where kind_id=5)
			and id in(select distinct contact_id from lead_suppliers_contacts where sc_id in (select distinct id from lead_suppliers where  sched_id="'.$item['lead_id'].'"))
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
	$ret=$sm->fetch('bdr/pdf_addresses.html');

}

elseif(isset($_POST['action'])&&($_POST['action']=="has_files")){
//есть ли файлы по записи план-ка?
	$count_of_files=0;	
	$id=abs((int)$_POST['id']);
	
	$sql='select count(*) from bdr_file where bill_id="'.$id.'" ';
	
	$set=new mysqlset($sql);
	$rs=$set->GetResult();
	
	$f=mysqli_fetch_array($rs);
	
	$count_of_files+=(int)$f[0];
	
 
	
	$ret=$count_of_files;
}


//работа с БДДС
elseif(isset($_POST['action'])&&($_POST['action']=="redraw_bdds")){
	
		$id=abs((int)$_POST['id']);
		$ds_account_ids=$_POST['ds_account_ids'];
		$ds_account_values=$_POST['ds_account_values'];
		
		
		$_bdds=new BDDS_Block($id);
		
		
		$sm=new SmartyAj;
		$data=$_bdds->ConstructByAccounts($ds_account_ids, $ds_account_values, $balance, $check);
		 //->ConstructByAccounts($accounts);
		
		$sm->assign('items',$data);
		$sm->assign('can_modify',true);
		$sm->assign('has_header',true);
		
		$sm->assign('check', $check);
		$sm->assign('check_formatted', number_format($check,2,  '.',' '));
		
		$ret=$sm->fetch('bdr/bdds_table.html');
		
		//print_r($accounts);
		 
}


//добавление позиции
elseif(isset($_POST['action'])&&($_POST['action']=="add_account")){
	
		$id=abs((int)$_POST['id']);
		$_bdr=new BDR_Item; 
		
		$params=array();
		$params['bdr_id']=$id;
		$params['version_id']=$_bdr->GetActiveVersionId($id);
		$params['p_or_m']=abs((int)$_POST['p_or_m']);
		$params['name']=SecStr(iconv('utf-8', 'windows-1251', $_POST['name']));
		$params['quantity']=1;
		
		$_account=new BDDS_AccountItem;
		
		$_account->Add($params);
		
		 
		
		
		$comment=$params['name'];
		if($params['p_or_m']==0) $comment.=' в блок Поступления ДС ';
		else  $comment.=' в блок Оплаты ';
		
		$log->PutEntry($result['id'],'добавил позицию БДДС', NULL,1041,NULL,$comment,$id);
		 
		 
}

//правка позиции
elseif(isset($_POST['action'])&&($_POST['action']=="edit_account")){
	
		$id=abs((int)$_POST['id']);
		
		$account_id=abs((int)$_POST['account_id']);
		$_bdr=new BDR_Item; 
		
		$params=array();
		//$params['bdr_id']=$id;
		//$params['version_id']=$_bdr->GetActiveVersionId($id);
		//$params['p_or_m']=abs((int)$_POST['p_or_m']);
		$params['name']=SecStr(iconv('utf-8', 'windows-1251', $_POST['name']));
		//$params['quantity']=1;
		
		$_account=new BDDS_AccountItem;
		
		$_account->Edit($account_id, $params);
		
		 
		
		
		$comment=$params['name'];
		//if($params['p_or_m']==0) $comment.=' в блок Поступления ДС ';
		//else  $comment.=' в блок Оплаты ';
		
		
		$log->PutEntry($result['id'],'редактировал позицию БДДС', NULL,1041,NULL,$comment,$id);
		 
		 
}

//удаление позиции
elseif(isset($_POST['action'])&&($_POST['action']=="delete_account")){
	
		$id=abs((int)$_POST['id']);
		
		$account_id=abs((int)$_POST['account_id']);
		$_bdr=new BDR_Item; 
		
		 
		
		$_account=new BDDS_AccountItem;
		$account=$_account->getitembyid($account_id);
		
		$_account->Del($account_id);
		
		 
		
		
		$comment=$account['name'];
		//if($params['p_or_m']==0) $comment.=' в блок Поступления ДС ';
		//else  $comment.=' в блок Оплаты ';
		
		
		$log->PutEntry($result['id'],'удалил позицию БДДС', NULL,1041,NULL,$comment,$id);
		 
		 
}


elseif(isset($_POST['action'])&&($_POST['action']=="load_pdf_filelist")){
	//список приложенных файлов для выбора для отправки
	$id=abs((int)$_POST['id']);
	
	$alls=array();
	/*
	$folder_id=0;
			 
	$decorator=new DBDecorator;
	
	$decorator->AddEntry(new SqlOrdEntry('pdate',SqlOrdEntry::DESC));
	
	$decorator->AddEntry(new UriEntry('id',$id));
	
	$decorator->AddEntry(new SqlEntry('folder_id',$folder_id, SqlEntry::E));
	$decorator->AddEntry(new UriEntry('folder_id',$folder_id));
	
	$navi_dec=new DBDecorator;
	//$navi_dec->AddEntry(new UriEntry('action',1));
	
	
	
	
	$ffg=new BDRFileGroup(1,  $id,  new FileDocFolderItem(1,  $id, new KpInFileItem(1)));;
	
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
	
	*/
	 
	//print_r($alls);
	 
		
	$sm=new SmartyAj;
	
	$sm->assign('items', $alls);
	$ret=$sm->fetch('bdr/pdf_files.html');

}

//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>