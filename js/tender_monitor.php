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

require_once('../classes/tender_monitor.class.php');


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
		
	
		
		  
		$_dem=new TenderMonitor_AbstractItem;
		$dem=$_dem->Getitembyid($id);
		
		$_res=new TenderMonitor_Resolver($dem['kind_id']);
		
		
		$_ki=$_res->instance;
		
		
		
		if(!$_ki->DocCanUnconfirmPrice($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
}elseif(isset($_POST['action'])&&($_POST['action']=="check_confirm_price")){
		$id=abs((int)$_POST['id']);
		
	
		
		  
		$_dem=new TenderMonitor_AbstractItem;
		$dem=$_dem->Getitembyid($id);
		
		$_res=new TenderMonitor_Resolver($dem['kind_id']);
		
		
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
	
	$_ki=new TenderMonitor_AbstractItem;
	
		$trust=$_ki->getitembyid($id);
		
	$_res=new TenderMonitor_Resolver($trust['kind_id']);
	$_res->SetAuthResult($result['id']);
	$_ti=$_res->instance;	
	
	$_si=new UserSItem;
	

	
	$log=new ActionLog;
	$_stat=new DocStatusItem;
	
	
	
	if($_ti->DocCanAnnul($id, $rss123)){
		//удаление	
		if($trust['kind_id']==1){
			if($au->user_rights->CheckAccess('w',995)){
				//$_ti->Edit($id,array('status_id'=>3),false,$result);
				$_ti->Del($id);
				
				
				$_eq=new Tender_EqTypeItem;
				$eq=$_eq->GetItemById($trust['eq_type_id']);
				
				//$stat=$_stat->GetItemById(3);
				$log->PutEntry($result['id'],'удаление мониторинга тендеров по виду оборудования',NULL,995,NULL,SecStr('вид оборудования: '.$eq['name']),$id);	
			 
			}
		}elseif($trust['kind_id']==2){
			if($au->user_rights->CheckAccess('w',994)){
				$_ti->Del($id);
				
				
				$_eq=new SupplierItem;
				$eq=$_eq->GetItemById($trust['supplier_id']);
				$_opf=new opfitem;
				$opf=$_opf->getitembyid($eq['opf_id']);
				
				//$stat=$_stat->GetItemById(3);
				$log->PutEntry($result['id'],'удаление мониторинга тендеров по контрагенту',NULL,994,NULL,SecStr('контрагент: '.$opf['name'].' '.$eq['full_name']),$id);
			}
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
	 
	 
	 if($trust['kind_id']==1) $template='tender_monitor/table1.html';
	 elseif($trust['kind_id']==2)  $template='tender_monitor/table2.html';
	 
	 
	  
	  $acg= $_res->group_instance; //new Tender_Group;
	  
	  $dec=new  DBDecorator;
	  
	  $dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	  
	  if($trust['kind_id']==1){
	
	 	 $ret=$acg->ShowPos(
		

			 $template,  //0
			 $dec, //1
			  $au->user_rights->CheckAccess('w',993), //2
			  0, //3
			  10000, //4
			  false, //5
			  true,  //6
			   $au->user_rights->CheckAccess('w',995), //7
			  $au->user_rights->CheckAccess('w',997),  //8
			  $au->user_rights->CheckAccess('w',999),  //9
			  $au->user_rights->CheckAccess('w',939)  //10
			  
			
			 );
	  }elseif($trust['kind_id']==2){
		   $ret=$acg->ShowPos(
		

			 $template,  //0
			 $dec, //1
			  $au->user_rights->CheckAccess('w',992), //2
			  0, //3
			  10000, //4
			  false, //5
			  true,  //6
			   $au->user_rights->CheckAccess('w',994), //7
			  $au->user_rights->CheckAccess('w',996),  //8
			  $au->user_rights->CheckAccess('w',998),  //9
			  $au->user_rights->CheckAccess('w',87)  //10
			  
			
			 );
	  }
	  
	 
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
	 
	
		$_ki=new TenderMonitor_AbstractItem;
	
		$trust=$_ki->getitembyid($id);
		
	$_res=new TenderMonitor_Resolver($trust['kind_id']);
	$_res->SetAuthResult($result);
	$_ti=$_res->instance;	
	
	$_si=new UserSItem;
	
	
	$_si=new UserSItem;
	 
	if($trust['confirm_pdate']==0) $trust['confirm_pdate']='-';
	else $trust['confirm_pdate']=date("d.m.Y H:i:s",$trust['confirm_pdate']);
	
	
	$si=$_si->getitembyid($trust['user_confirm_id']);
	$trust['confirmed_price_name']=$si['name_s'];
	$trust['confirmed_price_login']=$si['login'];
	
	$bill_id=$id;
	$_eq=new Tender_EqTypeItem;
	$eq=$_eq->GetItembyId($trust['eq_type_id']);
	$_si=new SupplierItem;
	$si=$_si->getitembyid($trust['supplier_id']);
	
	if($trust['is_confirmed']==1){
		//есть права: либо сам утв.+есть права, либо есть искл. права:
		
		if($trust['kind_id']==1){
			if($au->user_rights->CheckAccess('w',999)&&$_ti->DocCanUnconfirmPrice($id, $rss)){
					
					$_ti->Edit($id,array('is_confirmed'=>0, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()), true,$result);
					
					$log->PutEntry($result['id'],'снял утверждение заполнения мониторинга тендеров',NULL,999, NULL, SecStr($eq['name']),$id);
					 
						
				 
			} 
		}elseif($trust['kind_id']==2){
			if($au->user_rights->CheckAccess('w',998)&&$_ti->DocCanUnconfirmPrice($id, $rss)){
					
					$_ti->Edit($id,array('is_confirmed'=>0, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()), true,$result);
					
					$log->PutEntry($result['id'],'снял утверждение заполнения мониторинга тендеров',NULL,998, NULL, SecStr($si['full_name']),$id);
					 
						
				 
			} 
		}
		
	}else{
		//есть права
		if($trust['kind_id']==1){
			if($au->user_rights->CheckAccess('w',997)&&$_ti->DocCanConfirmPrice($id, $rss)){
				 
					$_ti->Edit($id,array('is_confirmed'=>1, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,$result);
					
					$log->PutEntry($result['id'],'утвердил заполнения мониторинга тендеров',NULL,997, NULL, SecStr($eq['name']),$id);	
					
					
				 
			} 
		}elseif($trust['kind_id']==2){
			if($au->user_rights->CheckAccess('w',996)&&$_ti->DocCanConfirmPrice($id, $rss)){
				 
					$_ti->Edit($id,array('is_confirmed'=>1, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,$result);
					
					$log->PutEntry($result['id'],'утвердил заполнения мониторинга тендеров',NULL,996, NULL, SecStr($si['full_name']),$id);	
					
					
				 
			} 
		}
	}
	
	
	
	if($trust['kind_id']==1) $template='tender_monitor/table1.html';
	 elseif($trust['kind_id']==2)   $template='tender_monitor/table2.html';
	 
	 
	  
	  $acg= $_res->group_instance; //new Tender_Group;
	  
	  $dec=new  DBDecorator;
	  
	  $dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	  
	  if($trust['kind_id']==1){
	
	 	 $ret=$acg->ShowPos(
		

			 $template,  //0
			 $dec, //1
			  $au->user_rights->CheckAccess('w',993), //2
			  0, //3
			  10000, //4
			  false, //5
			  true,  //6
			   $au->user_rights->CheckAccess('w',995), //7
			  $au->user_rights->CheckAccess('w',997),  //8
			  $au->user_rights->CheckAccess('w',999),  //9
			  $au->user_rights->CheckAccess('w',939)  //10
			  
			
			 );
	  }elseif($trust['kind_id']==2){
		   $ret=$acg->ShowPos(
		

			 $template,  //0
			 $dec, //1
			  $au->user_rights->CheckAccess('w',992), //2
			  0, //3
			  10000, //4
			  false, //5
			  true,  //6
			   $au->user_rights->CheckAccess('w',994), //7
			  $au->user_rights->CheckAccess('w',996),  //8
			  $au->user_rights->CheckAccess('w',998),  //9
			  $au->user_rights->CheckAccess('w',87)  //10
			  
			
			 );
	  }
	
		
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
  


//РАБОТА С типами оборудования
elseif(isset($_POST['action'])&&($_POST['action']=="redraw_types_dics")){
	$sm=new SmartyAj;
	if(isset($_POST['user_id'])) $user_id=abs((int)$_POST['user_id']);
	else $user_id=0;
	
	$opg=new Tender_EqTypeGroup;
	$sm->assign('opfs_total', $opg->GetItemsArr());
	$sm->assign('prefix',1);
	
	$ret=$sm->fetch('tender_monitor/d_types.html');
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="redraw_types_page")){
	//$sm=new SmartyAj;
	
	if(isset($_POST['user_id'])) $user_id=abs((int)$_POST['user_id']);
	else $user_id=0;
	
	$opg=new Tender_EqTypeGroup;
	
	$decorator=new DBDecorator;
	
	$decorator->AddEntry(new SqlEntry('id','select distinct eq_type_id from  tender_monitor where status_id=2', SqlEntry::NOT_IN_SQL));
	
	$ret=$opg->GetItemsOptDec($user_id, $decorator, 'name',true);
	
	
	
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


//добавка
elseif(isset($_POST['action'])&&($_POST['action']=="add_kind_1")){
	
	if(!$au->user_rights->CheckAccess('w',993)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$_ti=new TenderMonitor1_Item;
	$_eq=new Tender_EqTypeItem;
	$id=abs((int)$_POST['data']);
	
	$params=array();
	$params['kind_id']=1;
	$params['eq_type_id']=$id;
	$params['created_id']=$result['id'];
	$params['pdate']=time();
	
	$params['given_pdate']=DateFromdmY($_POST['given_pdate']);
	
	$code=$_ti->Add($params);
	
	$eq=$_eq->GetItemById($id);
	
	
	
	$log->PutEntry($result['id'],'добавил мониторинг тендеров по виду оборудования',NULL,993,NULL,SecStr(' вид оборудования: '. $eq['name']), $code);
	
}

elseif(isset($_POST['action'])&&($_POST['action']=="add_kind_2")){
	
	if(!$au->user_rights->CheckAccess('w',992)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$_ti=new TenderMonitor2_Item;
	$_si=new SupplierItem; $_opf=new opfitem;
	$id=abs((int)$_POST['data']);
	
	$params=array();
	$params['kind_id']=2;
	$params['supplier_id']=$id;
	$params['created_id']=$result['id'];
	$params['pdate']=time();
	
	$params['given_pdate']=DateFromdmY($_POST['given_pdate']);
	
	$code=$_ti->Add($params);
	
	$si=$_si->GetItemById($id);
	$opf=$_opf->getitembyid($si['opf_id']);
	
	
	
	$log->PutEntry($result['id'],'добавил мониторинг тендеров по контрагенту',NULL,992,NULL,SecStr(' контрагент: '. $opf['name']. ' '. $si['full_name']), $code);
	
}


//работа с контрагентами
elseif( ($_POST['action']=="find_suppliers") ){
	
	
	
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
			
		
			$dec->AddEntry(new SqlEntry('p.id','select distinct supplier_id from supplier_contact where  '.implode(' or ',$names).'', SqlEntry::IN_SQL));
		
	}
	
	//	if(isset($_POST['already_loaded'])&&is_array($_POST['already_loaded'])) $dec->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::NOT_IN_VALUES, NULL,$_POST['already_loaded']));
	
	$dec->AddEntry(new SqlEntry('p.id','select distinct supplier_id from  tender_monitor', SqlEntry::NOT_IN_SQL));
	
	//ограничения по к-ту
	$limited_supplier=NULL;
	
	
	//только свои...
	if($au->FltSupplier($result)){  
		 
		
		$_s_to_u=new SupplierToUser;
		$s_to_u=$_s_to_u->GetExtendedViewedUserIdsArr($result['id'], $result);
		$limited_supplier=$s_to_u['sector_ids'];
		
		$dec->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::IN_VALUES, NULL,$limited_supplier));
	}
	
	//is_customer
	$dec->AddEntry(new SqlEntry('p.is_customer',1, SqlEntry::E));
	
 		
 
  $ret=$_pg->GetItemsForBill('tender_monitor/suppliers_list.html',  $dec,true,$all7,$result, 0, (strlen(SecStr(iconv("utf-8","windows-1251",$_POST['contact'])))>0));  
	 
	
	
	 
	

	
} 
   
//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>