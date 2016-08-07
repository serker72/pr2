<?
session_start();
Header("Cache-Control: no-store, no-cache, must-revalidate"); //для протокола HTTP/1.1
Header("Pragma: no-cache"); // для протокола HTTP/1.1
Header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT"); // дата и время генерации страницы
header("Expires: " . date("r")); // дата и время время, когда страница будет считаться устаревшей


require_once('classes/global.php');
require_once('classes/authuser.php');
require_once('classes/smarty/SmartyAdm.class.php');
require_once('classes/smarty/Smarty.class.php');
require_once('classes/discr_table_objects.php');
require_once('classes/actionlog.php');

require_once('classes/petitiongroup.php');
require_once('classes/petitionitem.php');

require_once('classes/petitionkinditem.php');
require_once('classes/docstatusitem.php');

 

require_once('classes/petitionusergroup.php');
require_once('classes/petitionuseritem.php');
//require_once('classes/tasksuppliergroup.php');

require_once('classes/petitionnotesgroup.php');
require_once('classes/petitionnotesitem.php');

require_once('classes/petition_purpose_group.php');
require_once('classes/petition_purpose_item.php');

require_once('classes/petition_client_group.php');
require_once('classes/petition_client_item.php');

require_once('classes/petition_vyhreason_group.php');
require_once('classes/petition_vyhreason_item.php');


require_once('classes/supplier_country_group.php');
require_once('classes/supplier_city_item.php');
require_once('classes/supplier_region_item.php');
require_once('classes/supplier_district_item.php');


require_once('classes/supplier_region_item.php');
require_once('classes/petitionfileitem.php');
require_once('classes/petitionallgroup.php');
require_once('classes/orgitem.php');

require_once('classes/user_s_group.php');

require_once( "classes/phpqr/qrlib.php");  

require_once('classes/sched.class.php');

require_once('classes/usercontactdataitem.php'); 
require_once('classes/petition_field_rules.php');

require_once('classes/phpmailer/class.phpmailer.php');



$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Заявление');

$au=new AuthUser();
$result=$au->Auth();

$log=new ActionLog;

if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

if(!$au->user_rights->CheckAccess('w',725)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}

if($print!=0){
	if(!$au->user_rights->CheckAccess('w',826)){
	  header("HTTP/1.1 403 Forbidden");
	  header("Status: 403 Forbidden");
	  include("403.php");
	  die();	
	}
}



if(!isset($_GET['action'])){
	if(!isset($_POST['action'])){
		$action=0;
	}else $action=abs((int)$_POST['action']);
}else $action=abs((int)$_GET['action']);


if(!isset($_GET['id'])){
	if(!isset($_POST['id'])){
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include("404.php");
		die();
	}else $id=abs((int)$_POST['id']); 
}else $id=abs((int)$_GET['id']);

if(!isset($_GET['print'])){
	if(!isset($_POST['print'])){
		$print=0;
	}else $print=abs((int)$_POST['print']); 
}else $print=abs((int)$_GET['print']);

if(!isset($_GET['printmode'])){
	if(!isset($_POST['printmode'])){
		$printmode=0;
	}else $printmode=abs((int)$_POST['printmode']); 
}else $printmode=abs((int)$_GET['printmode']);


if(!isset($_GET['tab_page'])){
	if(!isset($_POST['tab_page'])){
		$tab_page=0;
	}else $tab_page=abs((int)$_POST['tab_page']); 
}else $tab_page=abs((int)$_GET['tab_page']); 


$mi=new petitionItem;
$claim=$mi->GetItemById($id);
$editing_user=$claim;
$log=new ActionLog;
 
$_pg=new PetitionAllGroup; 

$_tug=new petitionUserGroup;
$tug=$_tug->GetItemsArrById($id);
$tusers=array();
foreach($tug as $k=>$v) $tusers[]=$v['id'];

$_editable_status_id=array();
$_editable_status_id[]=1;
$_editable_status_id[]=18;



if($claim===false){
	header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include("404.php");
		die();
}




	$available_docs=$_pg->GetAvailableDocIds($result['id']);
	 
	 

	
	if(!in_array($editing_user['id'], $available_docs)){
		header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
	}
	
	
 

$log=new ActionLog;
	$log->PutEntry($result['id'],'открыл карту заявления',NULL,725, NULL, $claim['code'] ,$id);




if(($action==1)&&(isset($_POST['doEdit'])
	||isset($_POST['doEditStay'])
	||isset($_POST['send_ruk_sz'])
	||isset($_POST['to_rework_sz'])
	||isset($_POST['send_dir_sz'])
	)){
	//редактирование pozicii
	if(!$au->user_rights->CheckAccess('w',725)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}	
	
	
	$condition=true;
	$condition=in_array($_POST['current_status_id'],$_editable_status_id)&&in_array($editing_user['status_id'],$_editable_status_id);
	
	
	
	
		//поля формируем в зависимости от их активности в текущем статусе
	$_roles= new Petition_FieldRules; //var_dump($_roles->GetTable());
	$field_rights0=$_roles->GetFields($editing_user, $result['id']);	
	$field_rights1=$_roles->GetFields($editing_user, $result['id'], $_POST['current_status_id']);
	$field_rights=array(); 
	foreach($field_rights0 as $k=>$v) $field_rights[$k]=$v&&$field_rights1[$k];
	
	
	
	
	if($condition){
		//сохраняем данные 
		$params=array();
		if(isset($_POST['given_pdate'])) $params['given_pdate']=datefromdmy($_POST['given_pdate']);
		
		if(isset($_POST['manager_id'])) $params['manager_id']=abs((int)$_POST['manager_id']);
		
		if(isset($_POST['time_h'])) $params['time_h']=abs((int)$_POST['time_h']);
		if(isset($_POST['time_m'])) $params['time_m']=abs((int)$_POST['time_m']);
		
		if(isset($_POST['time_from_h'])) $params['time_from_h']=abs((int)$_POST['time_from_h']);
		if(isset($_POST['time_from_m'])) $params['time_from_m']=abs((int)$_POST['time_from_m']);
		
		if(isset($_POST['time_to_h'])) $params['time_to_h']=abs((int)$_POST['time_to_h']);
		if(isset($_POST['time_to_m'])) $params['time_to_m']=abs((int)$_POST['time_to_m']);
			
			
		if(isset($_POST['begin_pdate'])) $params['begin_pdate']=datefromdmy($_POST['begin_pdate']);	
		if(isset($_POST['end_pdate'])) $params['end_pdate']=datefromdmy($_POST['end_pdate']);
		
		if(isset($_POST['city_id'])) $params['city_id']=abs((int)$_POST['city_id']);	
		
		if(isset($_POST['sched_id'])) $params['sched_id']=abs((int)$_POST['sched_id']);	
		
		if(isset($_POST['exh_name'])) $params['exh_name']=SecStr($_POST['exh_name']);	
		if(isset($_POST['txt'])) $params['txt']=SecStr($_POST['txt']);
		
		if(isset($_POST['vyh_reason_id'])) $params['vyh_reason_id']=abs((int)$_POST['vyh_reason_id']);
		if(isset($_POST['vyh_reason'])) $params['vyh_reason']=SecStr($_POST['vyh_reason']);
		
		if(isset($_POST['by_graf_or_not'])) $params['by_graf_or_not']=abs((int)$_POST['by_graf_or_not']);
		
		
		if(isset($_POST['instead_id'])) $params['instead_id']=abs((int)$_POST['instead_id']);
		if(isset($_POST['wo_instead'])) $params['wo_instead']=1; else $params['wo_instead']=0;
		if(isset($_POST['wo_sched'])) $params['wo_sched']=1; else $params['wo_sched']=0;
		 
		$mi->Edit($id, $params, false, $result);
		
		foreach($params as $k=>$v){
			
			if(addslashes($editing_user[$k])!=$v){
				
				if($k=='given_pdate'){
					$log->PutEntry($result['id'],'редактировал заявление',NULL,725,NULL,'установлена дата действия: '.$_POST['given_pdate'],$id);
					continue;	
				}
				if($k=='begin_pdate'){
					$log->PutEntry($result['id'],'редактировал заявление',NULL,725,NULL,'установлена дата начала действия: '.$_POST['begin_pdate'],$id);
					continue;	
				}
				if($k=='end_pdate'){
					$log->PutEntry($result['id'],'редактировал заявление',NULL,725,NULL,'установлена дата окончания действия: '.$_POST['end_pdate'],$id);
					continue;	
				}
				if($k=='city_id'){
					$_city=new SupplierCityItem;
					$city=$_city->GetFullCity($v);
					
					$log->PutEntry($result['id'],'редактировал заявление',NULL,725,NULL,'установлен город: '.SecStr($city['fullname']),$id);
					continue;	
				}
				
				  
				$log->PutEntry($result['id'],'редактировал заявление',NULL,725, NULL, 'в поле '.$k.' установлено значение '.$v,$id);
			}
			
		}
		
		//непоср. рук-ль...
		//user_id
		
		$_tug=new PetitionUserItem;
		$_user=new UserSItem;
		
		$old_users=$_tug->GetUserByPetitionId($id);	
		if(isset($_POST['user_id'])&&($_POST['user_id']!=$old_users['id'])){
			if($old_users!==false)
			$_tug->Edit( $old_users['t_id'], array('user_id'=>abs((int)$_POST['user_id'])));
			else 
			$_tug->Add( array('petition_id'=>$id, 'user_id'=>abs((int)$_POST['user_id'])));
			
			
			$user=$_user->GetItemById(abs((int)$_POST['user_id']));
			
			$log->PutEntry($result['id'],'редактировал заявление',NULL,725, NULL, 'установлен непосредственный руководитель '.SecStr($user['name_s'].' '.$user['login'].', '.$user['position_s']),$id);
		}
		
		
		//внесем даты работы в выходные
		
		$positions=array();	
		foreach($_POST as $k=>$v){
		  if(eregi("^new_vyh_date_pdate_",$k)){
			  
			  $hash=eregi_replace("^new_vyh_date_pdate_","",$k);
			   $positions[]=array(
					  'petition_id'=>$id,
					  
					  
					  'pdate'=>Datefromdmy($_POST['new_vyh_date_pdate_'.$hash]),
					  'time_from_h'=>abs((int)$_POST['new_vyh_date_time_from_h_'.$hash]),
					  'time_from_m'=>abs((int)$_POST['new_vyh_date_time_from_m_'.$hash]),
					  'time_to_h'=>abs((int)$_POST['new_vyh_date_time_to_h_'.$hash]),
					  'time_to_m'=>abs((int)$_POST['new_vyh_date_time_to_m_'.$hash])
					   
				  );
		  }
		}
		
		//внесем даты
		$log_entries=$mi->AddVyhDates($id,$positions);
		//die();
		//запишем в журнал
		foreach($log_entries as $k=>$v){
			 
				$descr=SecStr('Дата '.date('d.m.Y',$v['pdate']).' с '.$v['time_from_h'].':'.$v['time_from_m'].' по '.$v['time_to_h'].':'.$v['time_to_m'].'  ');
				 
				if($v['action']==0)
					$log->PutEntry($result['id'],'добавил время работы в выходные', NULL, 725,NULL,$descr,$id);
				elseif($v['action']==1)	
					$log->PutEntry($result['id'],'редактировал время работы в выходные', NULL, 725,NULL,$descr,$id);
				elseif($v['action']==2)	
					$log->PutEntry($result['id'],'удалил время работы в выходные', NULL, 725,NULL,$descr,$id);	
				
				 
			 
		}
		
		
		
		//внесем даты Отпуска за работу в выходные
		
		$positions=array();	
		foreach($_POST as $k=>$v){
		  if(eregi("^new_vyh_otp_date_pdate_",$k)){
			  
			  $hash=eregi_replace("^new_vyh_otp_date_pdate_","",$k);
			   $positions[]=array(
					  'petition_id'=>$id,
					  
					  
					  'pdate'=>Datefromdmy($_POST['new_vyh_otp_date_pdate_'.$hash]) 
					   
				  );
		  }
		}
		
		//внесем даты
		$log_entries=$mi->AddVyhDatesOtp($id,$positions);
		//die();
		//запишем в журнал
		foreach($log_entries as $k=>$v){
			 
				$descr=SecStr('Дата '.date('d.m.Y',$v['pdate']));
				 
				if($v['action']==0)
					$log->PutEntry($result['id'],'добавил дату отпуска за работу в выходные', NULL, 725,NULL,$descr,$id);
				elseif($v['action']==1)	
					$log->PutEntry($result['id'],'редактировал отпуска за работу в выходные', NULL, 725,NULL,$descr,$id);
				elseif($v['action']==2)	
					$log->PutEntry($result['id'],'удалил отпуска за работу в выходные', NULL, 725,NULL,$descr,$id);	
				
				 
			 
		}
		
		/*echo '<pre>';
		print_r($positions);
		
		echo '</pre>';	
		die(); */
		
		
		 
	}
	
	
	//прочие параметры
	
	$params=array();
	if($field_rights['ruk_not']) if(isset($_POST['ruk_not'])) $params['ruk_not']=SecStr($_POST['ruk_not']);	
	
	if($field_rights['dir_not']) if(isset($_POST['dir_not'])) $params['dir_not']=SecStr($_POST['dir_not']);	
	
	$mi->Edit($id, $params, false, $result);
		
	foreach($params as $k=>$v){
		
		if(addslashes($editing_user[$k])!=$v){
			
			
			$log->PutEntry($result['id'],'редактировал заявление',NULL,725, NULL, 'в поле '.$k.' установлено значение '.$v,$id);
		}
		
	}
	
	
	
	//утверждение заполнения
	if($field_rights['to_confirm']){	
	  
	  if($editing_user['is_confirmed']==1){
		  //есть права
		  
		  if($au->user_rights->CheckAccess('w',830)){
			  if((!isset($_POST['is_confirmed']))&&($mi->DocCanUnconfirm($id))){
				  
				
				  $mi->Edit($id,array('is_confirmed'=>0, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,$result);
				  
				  $log->PutEntry($result['id'],'снял утверждение заполнения заявления',NULL,830, NULL, NULL,$id);	
				  
			  }
		  } 
		  
	  }else{
		  //есть права
		  if($au->user_rights->CheckAccess('w',829)){
			  if(isset($_POST['is_confirmed'])&&($mi->DocCanConfirm($id))){
				  
				  $mi->Edit($id,array('is_confirmed'=>1, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,$result);
				  
				  $log->PutEntry($result['id'],'утвердил заполнение заявления',NULL,829, NULL, NULL,$id);	
				   
				  //die();
			  }
		  }
	  }
	}
	
	
	//СЗ согласовано/не согласовано
	/*if($field_rights['to_ruk_sz']){	
	  
		$_ug=new UsersSGroup;
		$_ui=new UserSItem; $uis=$_ui->getitembyid($editing_user['manager_id']);
		$user_ruk=$_ug->GetRuk($uis);
		// var_dump($user_ruk);
		
	   	if($editing_user['is_ruk']==1){
			
			 
		  //есть права: либо сам утв.+есть права, либо есть искл. права:
		  if(($au->user_rights->CheckAccess('w',832))||($user_ruk['id']==$result['id'])){
			  if((!isset($_POST['is_ruk']))){
				  
				  
				  $mi->Edit($id,array('is_ruk'=>0, 'user_ruk_id'=>$result['id'], 'ruk_pdate'=>time()),true,$result);
				  
				  $log->PutEntry($result['id'],'снял утверждение заявления рук-лем отдела',NULL,725, NULL, NULL,$id);	
				  
			  }
		  } 
		  
	  }else{
		  //есть права
		  if($au->user_rights->CheckAccess('w',831)||($user_ruk['id']==$result['id'])){
			  if(isset($_POST['is_ruk'])&&($_POST['is_ruk']==1)){
				  
				  $mi->Edit($id,array('is_ruk'=>1, 'user_ruk_id'=>$result['id'], 'ruk_pdate'=>time()),true,$result);
				  
				  $log->PutEntry($result['id'],'утвердил заявление рук-лем отдела',NULL,725, NULL, NULL,$id);	
				  
				   
				  //die();
			  }
		  } 
	  }
	}
	
	
	//СЗ утверждено/не утверждено
	if($field_rights['to_dir_sz']){	
	  
		$_ug=new UsersSGroup;
		$_ui=new UserSItem; $uis=$_ui->getitembyid($editing_user['manager_id']);
		$user_ruk=$_ug->GetDir($uis);
		// var_dump($user_ruk);
		
	   	if($editing_user['is_dir']==1){
			
			 
		  //есть права: либо сам утв.+есть права, либо есть искл. права:
		  if(($au->user_rights->CheckAccess('w',1136))||($user_ruk['id']==$result['id'])){
			  if((!isset($_POST['is_dir']))){
				  
				  
				  $mi->Edit($id,array('is_dir'=>0, 'user_dir_id'=>$result['id'], 'dir_pdate'=>time()),true,$result);
				  
				  $log->PutEntry($result['id'],'снял утверждение заявления ген. директором',NULL,1091, NULL, NULL,$id);	
				  
			  }
		  } 
		  
	  }else{
		  //есть права
		  if($au->user_rights->CheckAccess('w',1135)||($user_ruk['id']==$result['id'])){
			  if(isset($_POST['is_dir'])&&($_POST['is_dir']==1)){
				  
				  $mi->Edit($id,array('is_dir'=>1, 'user_dir_id'=>$result['id'], 'dir_pdate'=>time()),true,$result);
				  
				  $log->PutEntry($result['id'],'утвердил заявление ген. директором',NULL,1091, NULL, NULL,$id);	
				  
				   
				  //die();
			  }
		  } 
	  }
	}*/
	
	
	$_roles= new Petition_FieldRules; //var_dump($_roles->GetTable());
	$field_rights0=$_roles->GetFields($editing_user, $result['id']);	
	$field_rights1=$_roles->GetFields($editing_user, $result['id'], $_POST['current_status_id']);
	$field_rights=array(); 
	foreach($field_rights0 as $k=>$v) $field_rights[$k]=$v&&$field_rights1[$k];
	
	
	
	//СЗ согласовано/не согласовано
	if($field_rights['to_ruk_sz']){	
	  
		$_ug=new UsersSGroup;
		$_ui=new UserSItem; $uis=$_ui->getitembyid($editing_user['manager_id']);
		$user_ruk=$_ug->GetRuk($uis);
		// var_dump($user_ruk);
		
		
		 
	   	 
	   if(!isset($_POST['is_ruk'])){	
			 
		  //есть права: либо сам утв.+есть права, либо есть искл. права:
		  if(($au->user_rights->CheckAccess('w',832))||($user_ruk['id']==$result['id'])){
			   
				if($editing_user['is_ruk'] !=0){
				  
				  $mi->Edit($id,array('is_ruk'=>0, 'user_ruk_id'=>$result['id'], 'ruk_pdate'=>time()),true,$result);
				  
				  $log->PutEntry($result['id'],'снял утверждение заявления рук-лем отдела',NULL,832, NULL, NULL,$id);	
				  
				}
		  } 
		  
	  }else{
		  //есть права
		  if($au->user_rights->CheckAccess('w',831)||($user_ruk['id']==$result['id'])){
			  if($_POST['is_ruk']==1){
				  
				  if($editing_user['is_ruk'] !=1){
					  $mi->Edit($id,array('is_ruk'=>1, 'user_ruk_id'=>$result['id'], 'ruk_pdate'=>time()),true,$result);
					  
					  $log->PutEntry($result['id'],'утвердил заявление рук-лем отдела',NULL,831, NULL, NULL,$id);	
				  }
				   
				  //die();
			  }elseif($_POST['is_ruk']==2){
				  
				   if($editing_user['is_ruk'] !=2){
				  
					  $mi->Edit($id,array('is_ruk'=>2, 'user_ruk_id'=>$result['id'], 'ruk_pdate'=>time()),true,$result);
					  
					  $log->PutEntry($result['id'],'не утвердил заявление рук-лем отдела',NULL,831, NULL, NULL,$id);	
				   }
				   
				  //die();
			  }
		  } 
	  }
	}
	
	$_roles= new Petition_FieldRules; //var_dump($_roles->GetTable());
	$field_rights0=$_roles->GetFields($editing_user, $result['id']);	
	$field_rights1=$_roles->GetFields($editing_user, $result['id'], $_POST['current_status_id']);
	$field_rights=array(); 
	foreach($field_rights0 as $k=>$v) $field_rights[$k]=$v&&$field_rights1[$k];
	
	
	
	//СЗ утверждено/не утверждено
	if($field_rights['to_dir_sz']){	
	  
		$_ug=new UsersSGroup;
		$_ui=new UserSItem; $uis=$_ui->getitembyid($editing_user['manager_id']);
		$user_ruk=$_ug->GetDir($uis);
		// var_dump($user_ruk);
		
		
		/*echo '<pre>';
		print_r($_POST);
		
		echo '</pre>';
		die();
		*/
		  
		 if(!isset($_POST['is_dir'])){	
			
			if(($au->user_rights->CheckAccess('w',1136))||($user_ruk['id']==$result['id'])){
				 if($editing_user['is_dir'] !=0){
				  
				  
				  $mi->Edit($id,array('is_dir'=>0, 'user_dir_id'=>$result['id'], 'dir_pdate'=>time()),true,$result);
				  
				  $log->PutEntry($result['id'],'снял утверждение заявления ген. директором',NULL,1136, NULL, NULL,$id);	
				  
				 }
			  }
		 }else{
		  
	 
		 	if($au->user_rights->CheckAccess('w',1135)||($user_ruk['id']==$result['id'])){
			  if($_POST['is_dir']==1){
				  if($editing_user['is_dir'] !=1){
				  
				  $mi->Edit($id,array('is_dir'=>1, 'user_dir_id'=>$result['id'], 'dir_pdate'=>time()),true,$result);
				  
				  $log->PutEntry($result['id'],'утвердил заявление ген. директором',NULL,1135, NULL, NULL,$id);	
				  }
				   
				  //die();
			  }elseif($_POST['is_dir']==2){
				   if($editing_user['is_dir'] !=2){
				  
				  $mi->Edit($id,array('is_dir'=>2, 'user_dir_id'=>$result['id'], 'dir_pdate'=>time()),true,$result);
				  
				  $log->PutEntry($result['id'],'не утвердил заявление ген. директором',NULL,1135, NULL, NULL,$id);	
				   }
				   
				  //die();
			  }
		  } 
	  } 
	}
	
	
	
	$_dsi=new DocStatusItem; 
	//обработка выделенных кнопок
 
	
	//обработка выделенных кнопок
	if(isset($_POST['send_ruk_sz'])){
		
		if($field_rights['send_ruk_sz']){
			
			$setted_status_id=41;
			$mi->Edit($id,array( 'status_id'=>$setted_status_id),true, $result);
			
			$stat=$_dsi->GetItemById($setted_status_id);
			$log->PutEntry($result['id'],'смена статуса заявления',NULL,725,NULL,'установлен статус '.$stat['name'],$id);
			
			//отправить письмо всем согласователям
			//$_sgns=new DocOut_SignGroup;
			//$_sgns->SendMessagesToSigners($id, 1);
			
			//$_msg=new DocIn_Messages;
			//$_msg->SendMessageToView($id);
			
			//сообщение руководителю отдела
			 
					
		}		
	}
	
	
	if(isset($_POST['send_dir_sz'])){
		
		if($field_rights['send_dir_sz']){
			
			$setted_status_id=43;
			$mi->Edit($id,array( 'status_id'=>$setted_status_id),true, $result);
			
			$stat=$_dsi->GetItemById($setted_status_id);
			$log->PutEntry($result['id'],'смена статуса заявления',NULL,725,NULL,'установлен статус '.$stat['name'],$id);
			
			//отправить письмо всем согласователям
			//$_sgns=new DocOut_SignGroup;
			//$_sgns->SendMessagesToSigners($id, 1);
			
			//$_msg=new DocIn_Messages;
			//$_msg->SendMessageToView($id);
			
			//сообщение директору
			 
					
		}		
	}
	
	
	if(isset($_POST['to_rework_sz'])){
		
		if($field_rights['to_rework_sz']){
			
			$setted_status_id=33;
			$mi->Edit($id,array( 'status_id'=>$setted_status_id),true, $result);
			
			$stat=$_dsi->GetItemById($setted_status_id);
			$log->PutEntry($result['id'],'смена статуса вн. документа',NULL,725,NULL,'установлен статус '.$stat['name'],$id);
			
			/*
			$comment=SecStr($_POST['status_change_comment']);
			
		 	  
			//внести комментарий			 
			$_tsi=new DocVn_HistoryItem;
			$_tsi->Add(array(
				'sched_id'=>$id,
				'pdate'=>time(),
				'user_id'=>0,
				'txt'=>SecStr('<div>Автоматический комментарий: сотрудник '.$result['name_s'].' отправил документ на доработку, причина: '.$comment.'</div>')
			
			));
			*/
			//снять утверждения, согласования сз
			$_res->instance->Edit($id,array('is_ruk'=>0, 'is_dir'=>0, '	user_ruk_id'=>0, 'user_dir_id'=>0, 'ruk_pdate'=>time(),'dir_pdate'=>time()));
			 
					
		}		
	}
	
	
	if(isset($_POST['doEdit'])){
		header("Location: petitions.php");
		die();
	}elseif(isset($_POST['doEditStay'])
	
	||isset($_POST['send_ruk_sz'])
	||isset($_POST['to_rework_sz'])
	||isset($_POST['send_dir_sz'])
	){
		//если есть доступ к объекту 11 - правка пользователя - то переход туда		
		if(!$au->user_rights->CheckAccess('w',725)){
			header("HTTP/1.1 403 Forbidden");
			header("Status: 403 Forbidden");
			include("403.php");
			die();	
		}
		header("Location: petition_my_history.php?action=1&id=".$id);
		die();	
		
	}else{
		header("Location: petitions.php");
		die();
	}
	
	die();
}

 
//работа с хедером
//работа с хедером
$stop_popup=true;

require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

if($print==0) $smarty->display('top.html');

unset($smarty);






$_menu_id=59;
	if($print==0) include('inc/menu.php');
	
	
	
	//демонстрация страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	//построим доступы
	$_roles= new Petition_FieldRules;
	$field_rights=$_roles->GetFields($editing_user, $result['id']);
	$sm->assign('field_rights', $field_rights);
	
	
	$_monthes=array(
	'01'=>'января',
	'02'=>'февраля',
	'03'=>'марта',
	'04'=>'апреля',
	'05'=>'мая',
	'06'=>'июня',
	'07'=>'июля',
	'08'=>'августа',
	'09'=>'сентября',
	'10'=>'октября',
	'11'=>'ноября',
	'12'=>'декабря',
	
	);
	
	
	$ssi=new DocStatusItem;
	$status=$ssi->GetItemById($editing_user['status_id']);
	$editing_user['status']=$status['name'];
	
	$_claim_kind=new petitionKindItem;
	$claim_kind=$_claim_kind->GetItemById($claim['kind_id']);
	$editing_user['kind_name']=$claim_kind['name'];
	
	$editing_user['time_h']=sprintf("%02d",  $editing_user['time_h']);
	$editing_user['time_m']=sprintf("%02d",  $editing_user['time_m']); 
	
	$editing_user['time_from_h']=sprintf("%02d",  $editing_user['time_from_h']);
	$editing_user['time_from_m']=sprintf("%02d",  $editing_user['time_from_m']); 
	
	$editing_user['time_to_h']=sprintf("%02d",  $editing_user['time_to_h']);
	$editing_user['time_to_m']=sprintf("%02d",  $editing_user['time_to_m']); 
	
	$editing_user['pdate_d']=date('d', $editing_user['pdate']);
	$editing_user['pdate_m']=date('m', $editing_user['pdate']);
	$editing_user['pdate_Y']=date('Y', $editing_user['pdate']);
	
	$editing_user['pdate']=date('d.m.Y', $editing_user['pdate']);
	
	
	$num_days=$editing_user['end_pdate']-$editing_user['begin_pdate'];
	$num_days=floor($num_days/(24*60*60))+1;
	$sm->assign('num_days', $num_days);
	
	
	 $editing_user['begin_pdate_d']=date('d', $editing_user['begin_pdate']);
	 
	 $editing_user['begin_pdate_m']=$_monthes[date('m', $editing_user['begin_pdate'])];
	   $editing_user['begin_pdate_Y']=date('Y', $editing_user['begin_pdate']);
	
	$editing_user['end_pdate_d']=date('d', $editing_user['end_pdate']);
	$editing_user['end_pdate_m']=$_monthes[date('m', $editing_user['end_pdate'])];
	$editing_user['end_pdate_Y']=date('Y', $editing_user['end_pdate']);
	
	
	if($editing_user['given_pdate']!=0) $editing_user['given_pdate']=date('d.m.Y', $editing_user['given_pdate']);
	else $editing_user['given_pdate']='-';
	
	if($editing_user['begin_pdate']!=0) $editing_user['begin_pdate']=date('d.m.Y', $editing_user['begin_pdate']);
	else $editing_user['begin_pdate']='-';
	
	if($editing_user['end_pdate']!=0) $editing_user['end_pdate']=date('d.m.Y', $editing_user['end_pdate']);
	else $editing_user['end_pdate']='-';
	
	
	
	//город
	$_pci=new SupplierCityItem;
	$city=$_pci->GetFullCity($editing_user['city_id']);
	$editing_user['city']=$city['fullname'];
	
	
	 
	
	
	//кем создано
	require_once('classes/user_s_item.php');
	$_cu=new UserSItem();
	$cu=$_cu->GetItemById($editing_user['user_id']);
	if($cu!==false){
		$ccu=$cu['name_s'].' ('.$cu['login'].')';
	}else $ccu='-';
	$sm->assign('created_by',$ccu);
	
	
	//сотрудник
	//поле менеджер
	$manager=$_cu->GetItemById($editing_user['manager_id']);
	//$sm1->assign('manager_id', $result['id']);
	$sm->assign('manager_string', $manager['name_s']);
	
	//список сотрудников
	 
	$_usg=new UsersSGroup; $dec_us=new DBDecorator;
	$limited_user=NULL;
	if($au->FltUser($result)){
		//echo 'z';
		$_u_to_u=new UserToUser();
		$u_to_u=$_u_to_u->GetExtendedViewedUserIdsArr($result['id']);
		$limited_user=$u_to_u['sector_ids'];
		$dec_us->AddEntry(new SqlEntry('u.id', NULL, SqlEntry::IN_VALUES, NULL,$limited_user));	
	}
	
	$dec_us->AddEntry(new SqlOrdEntry('u.name_s',SqlOrdEntry::ASC));
	
	$managers=$_usg->GetItemsByDecArr($dec_us);
	$sm->assign('can_modify_manager',$au->user_rights->CheckAccess('w',1144) );
	$sm->assign('managers', $managers);
	
	$sm->assign('created_by_print',$manager['name_s']);
	$sm->assign('created_by_pos_print',$manager['position_name']);
	
	
	
	
	$from_hrs=array();
	$from_hrs[]='';
		for($i=0;$i<=23;$i++) {
			 if(in_array($editing_user['kind_id'], array(4,5))) if(($i<8)||($i>19)) continue;
			 
			 $from_hrs[]=sprintf("%02d",$i);
			 
		}
		$sm->assign('from_hrs',$from_hrs);
		 
		$from_ms=array();
		$from_ms[]='';
		for($i=0;$i<=59;$i++) $from_ms[]=sprintf("%02d",$i);
		$sm->assign('from_ms',$from_ms);
	
	//кто вып-т обязанности
	$_ii=new UserSItem;
	$instead=$_ii->getitembyid($editing_user['instead_id']);
	$editing_user['instead_string']=$instead['name_s'];
	
		 
	 
	//Примечания
	$rg=new PetitionNotesGroup;
	$sm->assign('notes',$rg->GetItemsByIdArr(
								$editing_user['id'],
								0,
								0,
								($editing_user['is_confirmed']==1),
								 $au->user_rights->CheckAccess('w',834),
								  $au->user_rights->CheckAccess('w',835),
								  $result['id'] 
								  
								  ));
	$sm->assign('can_notes',true);
	$sm->assign('can_notes_edit',$au->user_rights->CheckAccess('w',833));
		
	
	//для видов 4-5 - причины ухода/прихода
	$_ea_gr=new PetitionEarlyReasonGroup;
	$sm->assign('vyh_early',$_ea_gr->GetItemsArr(0));
	$_ea_item=new PetitionEarlyReasonItem;
	$ea_item=$_ea_item->GetItemById($editing_user['vyh_reason_id']);
	$editing_user['ea_reason']=$ea_item['name'];
	
	
	 
	
	//для видов 6 и 7 - подгрузить виды целей визита
	$_ppg=new PetitionPurposeGroup;
	$ppg=$_ppg->GetItemsArr(0);
	$sm->assign('purposes', $ppg);	
	
	
	//для вида 7 - встреча и ее характеристики
	$_si=new Sched_MeetItem;
	
	$si=$_si->GetItemById($editing_user['sched_id']);
	
	//определить место и доп. инфо
	$_kind=new Sched_KindMeetItem;
	$kind=$_kind->GetItemById($si['meet_id']);
	
	$si['meet_name']=$kind['name'];  
	
	foreach($si as $k=>$v){
		if(($k=='pdate_beg')||($k=='pdate_end')) $si[$k]=datefromYmd($v);
		
		if($k=='ptime_beg'){
			$si['ptime_beg_h']=substr($si['ptime_beg'],  0,2 );
			$si['ptime_beg_m']=substr($si['ptime_beg'],  3,2 ); 	
		}
		
		if($k=='ptime_end'){
			$si['ptime_end_h']=substr($si['ptime_end'],  0,2 );
			$si['ptime_end_m']=substr($si['ptime_end'],  3,2 ); 	
		}
		
		 
	}
	 
	$sm->assign('sched', $si);
	
	//контрагенты встречи
	//контрагенты
		$_suppliers=new Sched_SupplierGroup;
			$sup=$_suppliers->GetItemsByIdArr($si['id']);
	 
		$sm->assign('suppliers', $sup);
	
	//города контрагентов встречи/ком-ки
	$_cities=new Sched_CityGroup;
	$cts=$_cities->GetItemsByIdArr($si['id']);
	 
		$sm->assign('cities', $cts);
	
	//для вида 3 - подгрузить блок дат работы в выходные, блок дат отпуска
	$_pvrg=new PetitionVyhReasonGroup; $_pvri=new PetitionVyhReasonItem;
	$sm->assign('vyh_reasons',$_pvrg->GetItemsArr(0));
	
	$sm->assign('vyh_reason_name',$_pvri->GetItemById($editing_user['vyh_reason_id']));
	
	
	
	$sm->assign('vyh_dates', $mi->GetVyhdates($editing_user['id']));
	
	$sm->assign('vyh_otp_dates', $mi->GetVyhdatesOtp($editing_user['id']));
	
	
	//блок аннулирования
		
	$editing_user['can_annul']=$mi->DocCanAnnul($editing_user['id'],$reason)&&$au->user_rights->CheckAccess('w',827);
	if(!$au->user_rights->CheckAccess('w',827)) $reason='недостаточно прав для данной операции';
	$editing_user['can_annul_reason']=$reason;
	
	
	$editing_user['can_restore']=$mi->DocCanRestore($editing_user['id'],$reason)&&$au->user_rights->CheckAccess('w',828);
	if(!$au->user_rights->CheckAccess('w',828)) $reason='недостаточно прав для данной операции';
	
	
	
	//блок утверждения заполнения!
	if(($editing_user['is_confirmed']==1)&&($editing_user['user_confirm_id']!=0)){
		$confirmer='';
		$_user_temp=new UserSItem;
		$_user_confirmer=$_user_temp->GetItemById($editing_user['user_confirm_id']);
		$confirmer=$_user_confirmer['position_name'].' '.$_user_confirmer['name_s'].' '.' '.date("d.m.Y H:i:s",$editing_user['confirm_pdate']);
		
		 
		
		$sm->assign('confirmer',$confirmer);
		
		$sm->assign('is_confirmed_price_confirmer',$confirmer);
		
		$sm->assign('is_confirmed_price_confirmer_record',$manager);
	}
	
	$can_confirm_price=false;
	if($field_rights['to_confirm']){	
		
	  if($editing_user['is_confirmed']==1){
		   $can_confirm_price=$au->user_rights->CheckAccess('w',830) ;
	  }else{
		  //95
		  $can_confirm_price=$au->user_rights->CheckAccess('w',829) ;
	  }
	}
	$sm->assign('can_confirm_price',$can_confirm_price);
	
	
	
	
	
	
	//рук отдела
	if(($editing_user['is_ruk']!=0)&&($editing_user['user_ruk_id']!=0)){
		$confirmer='';
		$_user_temp=new UserSItem;
		$_user_confirmer=$_user_temp->GetItemById($editing_user['user_ruk_id']);
		$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.date("d.m.Y H:i:s",$editing_user['ruk_pdate']);
		
		if($editing_user['is_ruk']==1){
			 $sm->assign('is_confirmed_ruk_confirmer_1',$confirmer);
			$sm->assign('ruk_ot_sign', $_user_confirmer);
			$sm->assign('ruk_ot_pdate', date("d.m.Y H:i:s",$editing_user['ruk_pdate']));
			
			
		}elseif($editing_user['is_ruk']==2) $sm->assign('is_confirmed_ruk_confirmer_2',$confirmer);
		
		$sm->assign('is_confirmed_ruk_confirmer_record',$_user_confirmer);
	}
	$can_confirm_price=false;
	//найти руководителя отдела
	
	$_uis=new UserSItem;
	$uis=$_uis->GetItemById($editing_user['manager_id']);
	
	$_ug=new UsersSGroup;
	$user_ruk=$_ug->GetRuk($uis);
	// var_dump($user_ruk);
	$sm->assign('ruk_ot', $user_ruk);
	
	if($editing_user['is_ruk']==1){
		
		 $can_confirm_price=($au->user_rights->CheckAccess('w',832)||($user_ruk['id']==$result['id']))&&$field_rights['to_ruk_sz'];
		
	}else{
		//95
		$can_confirm_price=($au->user_rights->CheckAccess('w',831)||($user_ruk['id']==$result['id']))&&$field_rights['to_ruk_sz'] ;
	}
	
	$sm->assign('can_ruk_sz',$can_confirm_price);
	
	
	
	//директор
	if(($editing_user['is_dir']!=0)&&($editing_user['user_dir_id']!=0)){
		$confirmer='';
		$_user_temp=new UserSItem;
		$_user_confirmer=$_user_temp->GetItemById($editing_user['user_dir_id']);
		$confirmerd=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.date("d.m.Y H:i:s",$editing_user['dir_pdate']);
		
		if($editing_user['is_dir']==1) {
			$sm->assign('is_confirmed_dir_confirmer_1',$confirmerd);
			$sm->assign('gen_dir_sign', $_user_confirmer);
			$sm->assign('gen_dir_pdate', date("d.m.Y H:i:s",$editing_user['dir_pdate']));
			
			
		}elseif($editing_user['is_dir']==2) $sm->assign('is_confirmed_dir_confirmer_2',$confirmerd);
		
		$sm->assign('is_confirmed_dir_confirmer_record',$_user_confirmer);
	}
	$can_confirm_price=false;
	//найти директора
	
	$_ug=new UsersSGroup;
	$user_ruk=$_ug->GetDir($uis);
	// var_dump($user_ruk);
	$sm->assign('gen_dir', $user_ruk);
	
	if($editing_user['is_dir']==1){
		
		 $can_confirm_price=($au->user_rights->CheckAccess('w',1136)||($user_ruk['id']==$result['id']))&&$field_rights['to_dir_sz'];
		
	}else{
		//95
		$can_confirm_price=($au->user_rights->CheckAccess('w',1135)||($user_ruk['id']==$result['id']))&&$field_rights['to_dir_sz'] ;
	}
	
	$sm->assign('can_dir_sz',$can_confirm_price);
	
	
	
	
	 /*
   //рук отдела
	if(($editing_user['is_ruk']==1)&&($editing_user['user_ruk_id']!=0)){
		$confirmer='';
		$_user_temp=new UserSItem;
		$_user_confirmer=$_user_temp->GetItemById($editing_user['user_ruk_id']);
		$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.date("d.m.Y H:i:s",$editing_user['ruk_pdate']);
		
		$sm->assign('ruk_ot_sign', $_user_confirmer);
		
		$sm->assign('is_confirmed_ruk_confirmer',$confirmer);
	}
	$can_confirm_price=false;
	//найти руководителя отдела
	
	$_uis=new UserSItem;
	$uis=$_uis->GetItemById($editing_user['manager_id']);
	
	$_ug=new UsersSGroup;
	$user_ruk=$_ug->GetRuk($uis);
	// var_dump($user_ruk);
	$sm->assign('ruk_ot', $user_ruk);
	
	if($editing_user['is_ruk']==1){
		
		 $can_confirm_price=($au->user_rights->CheckAccess('w',832)||($user_ruk['id']==$result['id']))&&$field_rights['to_ruk_sz'];
		
	}else{
		//95
		$can_confirm_price=($au->user_rights->CheckAccess('w',831)||($user_ruk['id']==$result['id']))&&$field_rights['to_ruk_sz'] ;
	}
	
	$sm->assign('can_ruk_sz',$can_confirm_price);
	
	
	
	//директор
	if(($editing_user['is_dir']==1)&&($editing_user['user_dir_id']!=0)){
		$confirmer='';
		$_user_temp=new UserSItem;
		$_user_confirmer=$_user_temp->GetItemById($editing_user['user_dir_id']);
		$confirmerd=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.date("d.m.Y H:i:s",$editing_user['dir_pdate']);
		
		$sm->assign('is_confirmed_dir_confirmer',$confirmerd);
		
		$sm->assign('gen_dir_sign', $_user_confirmer);
	}
	$can_confirm_price=false;
	//найти директора
	
	$_ug=new UsersSGroup;
	$user_ruk=$_ug->GetDir($uis);
	// var_dump($user_ruk);
	$sm->assign('gen_dir', $user_ruk);
	
	if($editing_user['is_dir']==1){
		
		 $can_confirm_price=($au->user_rights->CheckAccess('w',1136)||($user_ruk['id']==$result['id']))&&$field_rights['to_dir_sz'];
		
	}else{
		//95
		$can_confirm_price=($au->user_rights->CheckAccess('w',1135)||($user_ruk['id']==$result['id']))&&$field_rights['to_dir_sz'] ;
	}
	
	$sm->assign('can_dir_sz',$can_confirm_price);
	*/
	
	
	
	//общие поля для версии для печати
	//кто сгенерировал
	$ui1=new UserItem;
	$user1=$ui1->GetItemById($result['id']);
	foreach($user1 as $k=>$v) $user1[$k]=stripslashes($v);
	$sm->assign('user_signed',$user1);
	
	$sm->assign('created_by_user',$manager);
	
	//$sm->assign('pdate',date('d.m.Y H:i:s'));
	$sm->assign('pdate_signed', date("d.m.Y H:i:s"));
	
	//организация
	$_org=new OrgItem; $_opf=new opfitem;
	$org=$_org->getitembyid($result['org_id']);
	$opf=$_opf->GetItemById($org['opf_id']);
	$org['opf']=$opf['name'];
	$sm->assign('org', $org);
	
	
	$sm->assign('claim',$editing_user);
	
	$sm->assign('can_modify', ($field_rights['common'])&&in_array($editing_user['status_id'],$_editable_status_id)&&$au->user_rights->CheckAccess('w',725));
	
	$sm->assign('can_print', $au->user_rights->CheckAccess('w',826)); 
	
	$sm->assign('can_edit',$au->user_rights->CheckAccess('w',725)); 
		
	
	//подвал + qr - код
	if($print!=0){
		 $PNG_WEB_DIR = ABSPATH.'tmp/';
		 $PNG_TEMP_DIR =ABSPATH.'classes/phpqr/temp/';
		 $errorCorrectionLevel='Q';
		 $matrixPointSize = 1;
		 $data= 'Заявление '.$editing_user['code'].', статус: '.$editing_user['status'].', вид: '.$editing_user['kind_name'];
		 if($editing_user['is_ruk']==1) $data.=' утверждено руководителем отдела: '.$confirmer;
  		if($editing_user['is_dir']==1) $data.=' утверждено генеральным директором: '.$confirmerd;
  		
		
		$data=iconv('windows-1251', 'utf-8', $data);
		
		$filename = $PNG_TEMP_DIR.'petition_'.$id.'.png';	  
		
		 QRcode::png($data, $filename, $errorCorrectionLevel, $matrixPointSize, 2);   
		
	 	
		
		 
		rename($filename, $PNG_WEB_DIR.basename($filename));
		 
		 
		$sm->assign('qr', 'tmp/'.basename($filename));
		
		//подготовим подвал
		$sm_foot=new SmartyAdm;
		$sm_foot->assign('qr', 'tmp/'.basename($filename));
		$sm_foot->assign('user_signed',$user1);
		$sm_foot->assign('pdate_signed', date("d.m.Y H:i:s"));
		$sm_foot->assign('SITEURL', SITEURL);
		$footerh=$sm_foot->fetch('petition/petition_footer.html');
		
		
	}
	
	$kind='';
	if($editing_user['kind_id']==1) $kind='_1';
	elseif($editing_user['kind_id']==2) $kind='_2';
	elseif($editing_user['kind_id']==3) $kind='_3';
	elseif($editing_user['kind_id']==4) $kind='_4';
	elseif($editing_user['kind_id']==5) $kind='_5';
	elseif($editing_user['kind_id']==8) $kind='_8';
	
	if($printmode==1) $sm->assign('has_sign', true);
	 
	 
	
	if($print==0) $template='petition/petition_my_history.html';
	elseif($print==1) $template='petition/petitionhistory_print'.$kind.'.html';
	
	else $template='petition/petitionhistory'.$print_add.'.html';
	
	
	//Вкладка "журнал событий"
	$sm->assign('has_syslog',$au->user_rights->CheckAccess('w',3));
	if($au->user_rights->CheckAccess('w',3)){
		
			$decorator=new DBDecorator;
	
	
		
		
			
			
			if(isset($_GET['user_subj_login'])&&(strlen($_GET['user_subj_login'])>0)){
				$decorator->AddEntry(new SqlEntry('s.login',SecStr($_GET['user_subj_login']), SqlEntry::LIKE));
				$decorator->AddEntry(new UriEntry('user_subj_login',$_GET['user_subj_login']));
			}
			
			if(isset($_GET['description'])&&(strlen($_GET['description'])>0)){
				$decorator->AddEntry(new SqlEntry('l.description',SecStr($_GET['description']), SqlEntry::LIKE));
				$decorator->AddEntry(new UriEntry('description',$_GET['description']));
			}
			
			if(isset($_GET['object_id'])&&(strlen($_GET['object_id'])>0)){
				$decorator->AddEntry(new SqlEntry('l.object_id',SecStr($_GET['object_id']), SqlEntry::E));
				$decorator->AddEntry(new UriEntry('object_id',$_GET['object_id']));
			}
			
			if(isset($_GET['user_obj_login'])&&(strlen($_GET['user_obj_login'])>0)){
				$decorator->AddEntry(new SqlEntry('o.login',SecStr($_GET['user_obj_login']), SqlEntry::LIKE));
				$decorator->AddEntry(new UriEntry('user_obj_login',$_GET['user_obj_login']));
			}
			
			if(isset($_GET['user_group_id'])&&(strlen($_GET['user_group_id'])>0)){
				$decorator->AddEntry(new SqlEntry('l.user_group_id',SecStr($_GET['user_group_id']), SqlEntry::E));
				$decorator->AddEntry(new UriEntry('user_group_id',$_GET['user_group_id']));
			}
			
			if(isset($_GET['ip'])&&(strlen($_GET['ip'])>0)){
				$decorator->AddEntry(new SqlEntry('ip',SecStr($_GET['ip']), SqlEntry::LIKE));
				$decorator->AddEntry(new UriEntry('ip',$_GET['ip']));
			}
			
			
			
			//сортировку можно подписать как дополнительный параметр для UriEntry
			if(!isset($_GET['sortmode'])){
				$sortmode=0;	
			}else{
				$sortmode=abs((int)$_GET['sortmode']);
			}
			
			
			switch($sortmode){
				case 0:
					$decorator->AddEntry(new SqlOrdEntry('pdate',SqlOrdEntry::DESC));
				break;
				case 1:
					$decorator->AddEntry(new SqlOrdEntry('pdate',SqlOrdEntry::ASC));
				break;
				case 2:
					$decorator->AddEntry(new SqlOrdEntry('s.login',SqlOrdEntry::DESC));
				break;	
				case 3:
					$decorator->AddEntry(new SqlOrdEntry('s.login',SqlOrdEntry::ASC));
				break;
				case 4:
					$decorator->AddEntry(new SqlOrdEntry('l.description',SqlOrdEntry::DESC));
				break;
				case 5:
					$decorator->AddEntry(new SqlOrdEntry('l.description',SqlOrdEntry::ASC));
				break;	
				case 6:
					$decorator->AddEntry(new SqlOrdEntry('ob.name',SqlOrdEntry::DESC));
				break;
				case 7:
					$decorator->AddEntry(new SqlOrdEntry('ob.name',SqlOrdEntry::ASC));
				break;
				case 8:
					$decorator->AddEntry(new SqlOrdEntry('o.login',SqlOrdEntry::DESC));
				break;	
				case 9:
					$decorator->AddEntry(new SqlOrdEntry('o.login',SqlOrdEntry::ASC));
				break;
				case 10:
					$decorator->AddEntry(new SqlOrdEntry('gr.name',SqlOrdEntry::DESC));
				break;
				case 11:
					$decorator->AddEntry(new SqlOrdEntry('gr.name',SqlOrdEntry::ASC));
				break;	
				case 12:
					$decorator->AddEntry(new SqlOrdEntry('ip',SqlOrdEntry::DESC));
				break;
				case 13:
					$decorator->AddEntry(new SqlOrdEntry('ip',SqlOrdEntry::ASC));
				break;	
				default:
					$decorator->AddEntry(new SqlOrdEntry('pdate',SqlOrdEntry::DESC));
				break;	
				
			}
			$decorator->AddEntry(new SqlOrdEntry('id',SqlOrdEntry::DESC));
			
			$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
			
			
			
			if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
			else $from=0;
			
			if(isset($_GET['to_page'])) $to_page=abs((int)$_GET['to_page']);
			else $to_page=ITEMS_PER_PAGE;
			$decorator->AddEntry(new UriEntry('to_page',$to_page));
		
			$decorator->AddEntry(new SqlEntry('object_id',NULL, SqlEntry::IN_VALUES, NULL, array(723,
1139,
724,
725,
727,
728,
826,
827,
828,
829,
830,
831,
832,
1135,
1136,
833,
834,
835,
836,
837,
838,
839,
1144


)));
			$decorator->AddEntry(new SqlEntry('affected_object_id',$id, SqlEntry::E));
			//$decorator->AddEntry(new UriEntry('action',1));
			$decorator->AddEntry(new UriEntry('id',$id));
			$decorator->AddEntry(new UriEntry('tab_page',2));
			
			
			
			$llg=$log->ShowLog('syslog/log_doc.html',$decorator,$from,$to_page,'petition_my_history.php');
			
			$sm->assign('syslog',$llg);	
		
	}
	
	
	
	if(isset($_GET['force_print'])) $sm->assign('force_print',1);
	
	$sm->assign('tab_page',$tab_page);
	
	$content=$sm->fetch($template);
	
	
	
	
	
	
	
	$smarty->assign('fast_menu', $menu_arr_fast);
	  $smarty->assign('main_menu',$menu_res);
	$smarty->assign('content',$content);
	if($print==0) $smarty->display('page.html');
	else{
		
		//echo $content; die();
		//печать заявления
	 
		
			  $tmp=time();
	
		$f=fopen(ABSPATH.'/tmp/'.$tmp.'.html','w');
		fputs($f, $content);
		fclose($f);
		
		
		
		
		$footer=fopen(ABSPATH.'/tmp/f'.$tmp.'.html','w');
		fputs($footer,$footerh);
		fclose($footer);
		
		$cd = "cd ".ABSPATH.'/tmp';
		exec($cd);
			
		
		//$comand = "wkhtmltopdf-i386 --encoding windows-1251 --page-size A4 --margin-top 35mm --margin-bottom 27mm --margin-left 10mm --margin-right 10mm --footer-html ".SITEURL."/tpl-sm/pl_pdf/pdf_footer.html --header-html ".SITEURL."/tpl-sm/kp_pdf/common/pdf_header.html ".SITEURL.'/tmp/'.$tmp.'.html'."  ".ABSPATH.'/tmp/'."$tmp.pdf";
		
		$comand = "wkhtmltopdf-i386 --encoding windows-1251 --page-size A4 --margin-top 10mm --margin-bottom 25mm --margin-left 10mm --margin-right 10mm --footer-html ".ABSPATH."/tmp/f".$tmp.".html  ".SITEURL.'/tmp/'.$tmp.'.html'."  ".ABSPATH.'/tmp/'."$tmp.pdf";
	
 

		exec($comand);
		
		if(isset($_GET['send_email'])&&($_GET['send_email']==1)&&isset($_GET['email'])){
			$emails=$_GET['email'];
			$_addresses=explode(',',$emails);
			
			$file_ids=explode(',', $_GET['file_ids']);
			
			 
			
			
			//возможные файлы для вложения в письмо
			//один файл - без подписей, второй - с подписью
			//первый - уже есть!
			$filename=ABSPATH.'/tmp/'."$tmp.pdf";
			
			
			
			$filenames_to_send=array();
			if(in_array('s0', $file_ids)) $filenames_to_send[]=array(
				'fullname'=>$filename,
				'name'=>''.$editing_user['code'].'.pdf'
			
			);
			
			if(in_array('s1', $file_ids)) {
			//второй - переполучить
				$sm->assign('has_sign', true);
				$content=$sm->fetch($template);
				
				//echo $content; die();
				
				$f=fopen(ABSPATH.'/tmp/sig'.$tmp.'.html','w');
				fputs($f, $content);
				fclose($f);
			
				
				$comand = "wkhtmltopdf-i386 --encoding windows-1251 --page-size A4 --margin-top 10mm --margin-bottom 25mm --margin-left 10mm --margin-right 10mm --footer-html ".ABSPATH."/tmp/f".$tmp.".html  ".SITEURL.'/tmp/sig'.$tmp.'.html '.ABSPATH.'/tmp/sig'.$tmp.'.pdf';
				
				exec($comand);
				
				$filename=ABSPATH.'/tmp/sig'.$tmp.'.pdf';
				
				$filenames_to_send[]=array(
				  'fullname'=>$filename,
				  'name'=>''.$editing_user['code'].'_с_подписями.pdf'
				);		
			}
		 
			
			//получим файлы, прикрепленные к задаче
			$_ree_fi=new PetitionFileItem;
			 
			$sql='select * from petition_file where user_d_id="'.$id.'" ';
	
			$set=new mysqlset($sql);
			$rs=$set->GetResult();	
			$rc=$set->GetResultnumrows();
		
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				
				if(in_array($f['id'], $file_ids)) $filenames_to_send[]=array(
														'fullname'=>$_ree_fi->GetStoragePath().$f['filename'],
														'name'=>$f['orig_name'],
														'pdate'=>$f['pdate']
													
													);
			}
			
			/*var_dump(in_array('0', $file_ids));	
			print_r($filenames_to_send); die(); 
			 */
			
			$_filenames=array();
			foreach($filenames_to_send as $k=>$v) $_filenames[]=$v['name'];
			
			//var_dump($_filenames); 
			 
			$org=$_org->Getitembyid($result['org_id']);
			$opf=$_opf->getitembyid($org['opf_id']);
			
			
			$was_sent_to_supplier=false; $file_was_sent=false;
			 
			//массив адресов клиентов, куда отправили
			$_was_sent_to_addr=array();
			
			//var_dump($sprav_emails);
			
		 
			
			
			//использовать класс отправки сообщения
			foreach($_addresses as $k=>$email){
				
				//найти ФИО по адресу эл.почты...
				//1) в карте к-та
				$has_cont=false; $user_name='контрагент';
				/*$_sdi=new SupplierContactDataItem;
				$sdi=$_sdi->GetItemByFields(array('value'=>$email));
				if($sdi!==false){
					$_sci=new SupplierContactItem;
					$sci=$_sci->GetItemById($sdi['contact_id']);
					if($sci!==false){
						$user_name=$sci['name'];
						$has_cont=true;
					}
				}*/
				
				//2) в карте сотр-ка
				if(!$has_cont){
					$_uci=new UserContactDataItem;
					$_ui=new UserItem;
					$uci=$_uci->GetItemByFields(array('value'=>$email));
					$ui=$_ui->GetItemById($uci['user_id']);
					if($ui!==false) $user_name=$ui['name_s'];
					
				}
				
				
				
				
				
				$mail = new PHPMailer();
				$body = "<div>Уважаемый(ая) %contact_name%!</div> <div>&nbsp;</div> <div><i>Это сообщение сформировано автоматически, просьба не отвечать на него.</i></div> <div>&nbsp;</div> <div>Отправляем Вам следующие документы: %docs%.</div> <div>&nbsp;</div> <div>Благодарим Вас за то, что Вы обратились к нам!</div> <div>С уважением, компания %opf_name% %company_name% .</div>
	 "; 
				
				$body=str_replace('%contact_name%',  $user_name,$body);
				$body=str_replace('%docs%', implode(', ',$_filenames),  $body);
				$body=str_replace('%company_name%', $org['full_name'],  $body);
				$body=str_replace('%opf_name%', $opf['name'],  $body);
				
				
			
				$mail->SetFrom(FEEDBACK_EMAIL, $opf['name'].' '.$org['full_name']);
			
				  
			
				$mail->AddAddress(trim($email),  $email);
				
				$mail->Subject = 'документы для Вас'; 
				$mail->Body=$body;
				
				//echo $body;
				
				foreach($filenames_to_send as $k=>$v) {
					$mail->AddAttachment($v['fullname'],  $v['name']);  
					
					$file_was_sent=$file_was_sent||true;
				}
				 
				$mail->CharSet = "windows-1251";
				$mail->IsHTML(true);  
				
				if(!$mail->Send())
				{
					//echo "Ошибка отправки письма: " . $mail->ErrorInfo;
				}
				else 
				{
	
					// echo "Письмо отправленно!";
				}
				
				
				 
					//фиксируем отправку КП клиенту
					 
					if(in_array($email, $sprav_emails)) {
						$was_sent_to_supplier=$was_sent_to_supplier||true;
						//$addition='ТЗ было отправлено поставщику: ';
						$_was_sent_to_addr[]=$email;
					}
					
				 
				 
			 
				$log->PutEntry($result['id'],'отправил на электронную почту pdf-версию заявления',NULL,826, NULL, ' № '.$editing_user['code'].', адрес эл. почты '.$email,$id);
				
				
				
				//коммент
				//примечания
				   $_lhi=new PetitionNotesItem;
				  
				   $notes_params=array();
				 
				  
				 
					$notes_params['note']='Автоматическое примечание: Заявление № '.$editing_user['code'].' было отправлено на электронную почту  '.$email.' '.date('d.m.Y H:i:s').' пользователем '.SecStr($result['name_s'].' ').'.';
					 
					
					$_lhi->Add(array(
						'user_id'=>$editing_user['id'],
						'pdate'=>time(),
						'posted_user_id'=>0,
						'note'=>$notes_params['note'],
						 'is_auto'=>1,
						'pdate'=>time()
					));	  
			}	
			 
			//была зафиксирована отправка 
			
			
			//var_dump($was_sent_to_supplier); var_dump($file_was_sent);
			
			 
			 
		
			
			$sm=new SmartyAdm;
			
			$txt='';
			$txt.='<div><strong>Заявление было отправлено на следующие адреса:</strong></div>';
			$txt.='<ul>';
			
			foreach($_addresses as $k=>$email){
				$txt.='<li>'.$email.'</li>';
			}
			$txt.='</ul>';
			
			if(count($_filenames)>0){
				$txt.='<div>&nbsp;</div>';
				$txt.='<div><strong>Были приложены следующие файлы:</strong></div>';
				$txt.='<ul>';
				foreach($_filenames as $k=>$file){
					$txt.='<li>'.$file.'</li>';
				}
				$txt.='</ul>';
			}
			
		 
			//$txt.='<p></p>';			
			
			$sm->assign('message', $txt);
			
			$sm->display('page_email.html');
			
			 
		
			
		}else{
		
		
			$log->PutEntry($result['id'],'получил pdf-версию заявления',NULL,826, NULL, ' № '.$editing_user['code'],$id);
			 
			header('Content-type: application/pdf');
			header('Content-Disposition: attachment; filename="'.iconv('windows-1251', 'utf-8',$editing_user['code']).'.pdf'.'"');
			readfile(ABSPATH.'/tmp/'.$tmp.'.pdf');
		}
		
	
	 
		unlink(ABSPATH.'/tmp/'.$tmp.'.pdf');
		unlink(ABSPATH.'/tmp/sig'.$tmp.'.pdf');
		
		unlink(ABSPATH.'/tmp/'.$tmp.'.html');
		unlink(ABSPATH.'/tmp/sig'.$tmp.'.html');
		
		unlink(ABSPATH.'/tmp/f'.$tmp.'.html');
		 
	
	}
	
	



$smarty = new SmartyAdm;

//работа с футером
require_once('inc/footer.php');
if(isset($footer_res)){
	$smarty->assign('footer',$footer_res);
}else $smarty->assign('footer','');

if($print==0) $smarty->display('bottom.html');
 
unset($smarty);
 
?>