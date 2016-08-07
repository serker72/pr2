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

require_once('classes/memogroup.php');
require_once('classes/memoitem.php');

require_once('classes/memokinditem.php');
require_once('classes/memostatusitem.php');

require_once('classes/memoallgroup.php');

require_once('classes/memonotesgroup.php');
require_once('classes/memonotesitem.php');


require_once( "classes/phpqr/qrlib.php");  

require_once('classes/sched.class.php');

require_once('classes/usercontactdataitem.php'); 

require_once('classes/user_dep_item.php');
 
require_once('classes/phpmailer/class.phpmailer.php');
 

require_once('classes/memousergroup.php');
//require_once('classes/tasksuppliergroup.php');


$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Служебная записка');

$au=new AuthUser();
$result=$au->Auth();

$log=new ActionLog;

if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

if(!$au->user_rights->CheckAccess('w',731)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}



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


$mi=new memoItem;
$claim=$mi->GetItemById($id);
$log=new ActionLog;
$hg=new memoHistoryGroup;


$editing_user=$claim;
 
$_pg=new MemoAllGroup; 

$_tug=new memoUserGroup;
$tug=$_tug->GetItemsArrById($id);
$tusers=array();
foreach($tug as $k=>$v) $tusers[]=$v['id'];



$_editable_status_id=array();
//$_editable_status_id[]=1;
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
	
	


$log->PutEntry($result['id'],'открыл служебную записку',NULL,731,NULL,'Номер служебной записки: '.$claim['code'],$id);


$action=1;




if(($action==1)&&(isset($_POST['doEdit'])
	||isset($_POST['doEditStay'])
	||isset($_POST['send_ruk_sz'])
	||isset($_POST['to_rework_sz'])
	||isset($_POST['send_dir_sz'])
	||isset($_POST['to_escal'])
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
	$_roles= new Memo_FieldRules; //var_dump($_roles->GetTable());
	$field_rights0=$_roles->GetFields($editing_user, $result['id']);	
	$field_rights1=$_roles->GetFields($editing_user, $result['id'], $_POST['current_status_id']);
	$field_rights=array(); 
	foreach($field_rights0 as $k=>$v) $field_rights[$k]=$v&&$field_rights1[$k];
	
	
	$params=array();
	
	//if($condition){
		//сохраняем данные 
		
	if($condition&&$field_rights['common']) if(isset($_POST['txt'])) $params['txt']=SecStr($_POST['txt']);
	
	if($condition&&$field_rights['common']) if(isset($_POST['topic'])) $params['topic']=SecStr($_POST['topic']);
	
	if($condition&&$field_rights['common']) if(isset($_POST['manager_id'])) $params['manager_id']=abs((int)$_POST['manager_id']);
		
		
	if($field_rights['ruk_not']) if(isset($_POST['ruk_not'])) $params['ruk_not']=SecStr($_POST['ruk_not']);	
	
	if($field_rights['dir_not']) if(isset($_POST['dir_not'])) $params['dir_not']=SecStr($_POST['dir_not']);	
		    
		/*echo '<pre>';
		print_r($positions);
		
		echo '</pre>';	
		die(); */
		
		
		 
	//}
	
	 
	$mi->Edit($id, $params, false, $result);
	
	foreach($params as $k=>$v){
		
		if(addslashes($editing_user[$k])!=$v){
			
			 
			  
			$log->PutEntry($result['id'],'редактировал служебную записку',NULL,731, NULL, 'в поле '.$k.' установлено значение '.$v,$id);
		}
		
	}
	
	
	//утверждение заполнения
	
	if($field_rights['to_confirm']){	
	  
	  if($editing_user['is_confirmed']==1){
		  //есть права
		  
		  if($au->user_rights->CheckAccess('w',737)){
			  if((!isset($_POST['is_confirmed']))&&($mi->DocCanUnconfirm($id))){
				  
				
				  $mi->Edit($id,array('is_confirmed'=>0, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,$result);
				  
				  $log->PutEntry($result['id'],'снял утверждение заполнения служебной записки',NULL,737, NULL, NULL,$id);	
				  
			  }
		  } 
		  
	  }else{
		  //есть права
		  if($au->user_rights->CheckAccess('w',735)){
			  if(isset($_POST['is_confirmed'])&&($mi->DocCanConfirm($id))){
				  
				  $mi->Edit($id,array('is_confirmed'=>1, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,$result);
				  
				  $log->PutEntry($result['id'],'утвердил заполнение служебной записки',NULL,735, NULL, NULL,$id);	
				   
				  //die();
			  }
		  }
	  }
	}
	
	
	$_roles= new Memo_FieldRules; //var_dump($_roles->GetTable());
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
		  if(($au->user_rights->CheckAccess('w',1121))||($user_ruk['id']==$result['id'])){
			   
				if($editing_user['is_ruk'] !=0){
				  
				  $mi->Edit($id,array('is_ruk'=>0, 'user_ruk_id'=>$result['id'], 'ruk_pdate'=>time()),true,$result);
				  
				  $log->PutEntry($result['id'],'снял утверждение служебной записки рук-лем отдела',NULL,1121, NULL, NULL,$id);	
				  
				}
		  } 
		  
	  }else{
		  //есть права
		  if($au->user_rights->CheckAccess('w',1120)||($user_ruk['id']==$result['id'])){
			  if($_POST['is_ruk']==1){
				  
				  if($editing_user['is_ruk'] !=1){
					  $mi->Edit($id,array('is_ruk'=>1, 'user_ruk_id'=>$result['id'], 'ruk_pdate'=>time()),true,$result);
					  
					  $log->PutEntry($result['id'],'утвердил служебную записку рук-лем отдела',NULL,1120, NULL, NULL,$id);	
				  }
				   
				  //die();
			  }elseif($_POST['is_ruk']==2){
				  
				   if($editing_user['is_ruk'] !=2){
				  
					  $mi->Edit($id,array('is_ruk'=>2, 'user_ruk_id'=>$result['id'], 'ruk_pdate'=>time()),true,$result);
					  
					  $log->PutEntry($result['id'],'не утвердил служебную записку рук-лем отдела',NULL,1120, NULL, NULL,$id);	
				   }
				   
				  //die();
			  }
		  } 
	  }
	}
	
	$_roles= new Memo_FieldRules; //var_dump($_roles->GetTable());
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
			
			if(($au->user_rights->CheckAccess('w',1123))||($user_ruk['id']==$result['id'])){
				 if($editing_user['is_dir'] !=0){
				  
				  
				  $mi->Edit($id,array('is_dir'=>0, 'user_dir_id'=>$result['id'], 'dir_pdate'=>time()),true,$result);
				  
				  $log->PutEntry($result['id'],'снял утверждение служебной записки ген. директором',NULL,1123, NULL, NULL,$id);	
				  
				 }
			  }
		 }else{
		  
	 
		 	if($au->user_rights->CheckAccess('w',1122)||($user_ruk['id']==$result['id'])){
			  if($_POST['is_dir']==1){
				  if($editing_user['is_dir'] !=1){
				  
				  $mi->Edit($id,array('is_dir'=>1, 'user_dir_id'=>$result['id'], 'dir_pdate'=>time()),true,$result);
				  
				  $log->PutEntry($result['id'],'утвердил служебную записку ген. директором',NULL,1122, NULL, NULL,$id);	
				  }
				   
				  //die();
			  }elseif($_POST['is_dir']==2){
				   if($editing_user['is_dir'] !=2){
				  
				  $mi->Edit($id,array('is_dir'=>2, 'user_dir_id'=>$result['id'], 'dir_pdate'=>time()),true,$result);
				  
				  $log->PutEntry($result['id'],'не утвердил служебную записку ген. директором',NULL,1122, NULL, NULL,$id);	
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
		
		$_roles= new Memo_FieldRules; //var_dump($_roles->GetTable());
	$field_rights0=$_roles->GetFields($editing_user, $result['id']);	
	$field_rights1=$_roles->GetFields($editing_user, $result['id'], $_POST['current_status_id']);
	$field_rights=array(); 
	foreach($field_rights0 as $k=>$v) $field_rights[$k]=$v&&$field_rights1[$k];
	
		
		if($field_rights['send_ruk_sz']){
			
			$setted_status_id=41;
			$mi->Edit($id,array( 'status_id'=>$setted_status_id),true, $result);
			
			$stat=$_dsi->GetItemById($setted_status_id);
			$log->PutEntry($result['id'],'смена статуса служебной записки',NULL,731,NULL,'установлен статус '.$stat['name'],$id);
			
			//отправить письмо всем согласователям
			//$_sgns=new DocOut_SignGroup;
			//$_sgns->SendMessagesToSigners($id, 1);
			
			//$_msg=new DocIn_Messages;
			//$_msg->SendMessageToView($id);
			
			//сообщение руководителю отдела
			 
					
		}		
	}
	
	
	if(isset($_POST['send_dir_sz'])){
		
		$_roles= new Memo_FieldRules; //var_dump($_roles->GetTable());
	$field_rights0=$_roles->GetFields($editing_user, $result['id']);	
	$field_rights1=$_roles->GetFields($editing_user, $result['id'], $_POST['current_status_id']);
	$field_rights=array(); 
	foreach($field_rights0 as $k=>$v) $field_rights[$k]=$v&&$field_rights1[$k];
	
		
		if($field_rights['send_dir_sz']){
			
			$setted_status_id=43;
			$mi->Edit($id,array( 'status_id'=>$setted_status_id),true, $result);
			
			$stat=$_dsi->GetItemById($setted_status_id);
			$log->PutEntry($result['id'],'смена статуса служебной записки',NULL,731,NULL,'установлен статус '.$stat['name'],$id);
			
			//отправить письмо всем согласователям
			//$_sgns=new DocOut_SignGroup;
			//$_sgns->SendMessagesToSigners($id, 1);
			
			//$_msg=new DocIn_Messages;
			//$_msg->SendMessageToView($id);
			
			//сообщение директору
			 
					
		}		
	}
	
	
	if(isset($_POST['to_rework_sz'])){
		
		$_roles= new Memo_FieldRules; //var_dump($_roles->GetTable());
	$field_rights0=$_roles->GetFields($editing_user, $result['id']);	
	$field_rights1=$_roles->GetFields($editing_user, $result['id'], $_POST['current_status_id']);
	$field_rights=array(); 
	foreach($field_rights0 as $k=>$v) $field_rights[$k]=$v&&$field_rights1[$k];
	
		
		
		if($field_rights['to_rework_sz']){
			
			$setted_status_id=33;
			$mi->Edit($id,array( 'status_id'=>$setted_status_id),true, $result);
			
			$stat=$_dsi->GetItemById($setted_status_id);
			$log->PutEntry($result['id'],'смена статуса служебной записки',NULL,731,NULL,'установлен статус '.$stat['name'],$id);
			
			 
			//снять утверждения, согласования сз
			$_res->instance->Edit($id,array('is_ruk'=>0, 'is_dir'=>0, '	user_ruk_id'=>0, 'user_dir_id'=>0, 'ruk_pdate'=>time(),'dir_pdate'=>time()));
			 
					
		}		
	}
	
	
	if(isset($_POST['to_escal'])){
		
		$_roles= new Memo_FieldRules; //var_dump($_roles->GetTable());
	$field_rights0=$_roles->GetFields($editing_user, $result['id']);	
	$field_rights1=$_roles->GetFields($editing_user, $result['id'], $_POST['current_status_id']);
	$field_rights=array(); 
	foreach($field_rights0 as $k=>$v) $field_rights[$k]=$v&&$field_rights1[$k];
	
		
		
		$_ug=new UsersSGroup;
		$_ui=new UserSItem; $uis=$_ui->getitembyid($editing_user['manager_id']);
		$user_ruk=$_ug->GetDir($uis);
 		if(($field_rights['to_escal'])&&(($editing_user['manager_id']==$result['id'])||($editing_user['user_id']==$result['id']))&&( $user_ruk['id']!=$result['id'])){
	 		
		 	$setted_status_id=43;
			$mi->Edit($id,array( 'status_id'=>$setted_status_id),true, $result);
			
			$stat=$_dsi->GetItemById($setted_status_id);
			$log->PutEntry($result['id'],'смена статуса служебной записки',NULL,731,NULL,'установлен статус '.$stat['name'],$id);
		}
		
		 	
	}
	
	if(isset($_POST['doEdit'])){
		header("Location: memos.php");
		die();
	}elseif(isset($_POST['doEditStay'])
	
	||isset($_POST['send_ruk_sz'])
	||isset($_POST['to_rework_sz'])
	||isset($_POST['send_dir_sz'])
	||isset($_POST['to_escal'])
	){
		//если есть доступ к объекту 11 - правка пользователя - то переход туда		
		if(!$au->user_rights->CheckAccess('w',731)){
			header("HTTP/1.1 403 Forbidden");
			header("Status: 403 Forbidden");
			include("403.php");
			die();	
		}
		header("Location: memo_my_history.php?action=1&id=".$id);
		die();	
		
	}else{
		header("Location: memos.php");
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




$_menu_id=60;


	if($print==0) include('inc/menu.php');
	
	
	
	//демонстрация страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	//построим доступы
	$_roles= new Memo_FieldRules;
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
	
	$_claim_kind=new MemoKindItem;
	$claim_kind=$_claim_kind->GetItemById($claim['kind_id']);
	$editing_user['kind_name']=$claim_kind['name'];
	
	
		//кем создано
	require_once('classes/user_s_item.php');
	$_cu=new UserSItem();
	$cu=$_cu->GetItemById($editing_user['user_id']);
	if($cu!==false){
		$ccu=$cu['name_s']; //.' ('.$cu['login'].')';
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
	
	
	//является ли руководителем отдела???
	$_ui=new UserSItem;
	$user=$_ui->GetItemById($result['id']);
	$_upos=new UserPosItem;
	$upos=$_upos->GetItemById($user['position_id']);
	if($upos['is_ruk_otd']==1){
		//ввести ограничения на сотрудников только этого отдела
		$dec_us->AddEntry(new SqlEntry('u.department_id', $user['department_id'], SqlEntry::E));	
	}
	
	$dec_us->AddEntry(new SqlOrdEntry('u.name_s',SqlOrdEntry::ASC));
	
	$managers=$_usg->GetItemsByDecArr($dec_us);
	$sm->assign('can_modify_manager',$au->user_rights->CheckAccess('w',1145)||($upos['is_ruk_otd']==1) );
	$sm->assign('managers', $managers);
	
	$_dep=new UserDepItem;
	$dep=$_dep->GetItemById($manager['department_id']);
	
	$sm->assign('created_by_user',$manager);
	$sm->assign('created_by_print',$manager['name_s']);
	$sm->assign('created_by_pos_print',$manager['position_name'].', отдел: '.$dep['name']);
	
	
	
	
	//Примечания
	$rg=new MemoNotesGroup;
	$sm->assign('notes',$rg->GetItemsByIdArr(
								$editing_user['id'],
								0,
								0,
								($editing_user['is_confirmed']==1),
								 $au->user_rights->CheckAccess('w',1128),
								  $au->user_rights->CheckAccess('w',1129),
								  $result['id'] 
								  
								  ));
	$sm->assign('can_notes',true);
	$sm->assign('can_notes_edit',$au->user_rights->CheckAccess('w',1127));
	
		//блок аннулирования
		
	$editing_user['can_annul']=$mi->DocCanAnnul($editing_user['id'],$reason)&&$au->user_rights->CheckAccess('w',1125);
	if(!$au->user_rights->CheckAccess('w',1125)) $reason='недостаточно прав для данной операции';
	$editing_user['can_annul_reason']=$reason;
	
	
	$editing_user['can_restore']=$mi->DocCanRestore($editing_user['id'],$reason)&&$au->user_rights->CheckAccess('w',1126);
	if(!$au->user_rights->CheckAccess('w',1126)) $reason='недостаточно прав для данной операции';
	
	
	
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
		   $can_confirm_price=$au->user_rights->CheckAccess('w',737) ;
	  }else{
		  //95
		  $can_confirm_price=$au->user_rights->CheckAccess('w',735) ;
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
		
		 $can_confirm_price=($au->user_rights->CheckAccess('w',1121)||($user_ruk['id']==$result['id']))&&$field_rights['to_ruk_sz'];
		
	}else{
		//95
		$can_confirm_price=($au->user_rights->CheckAccess('w',1120)||($user_ruk['id']==$result['id']))&&$field_rights['to_ruk_sz'] ;
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
		
		 $can_confirm_price=($au->user_rights->CheckAccess('w',1123)||($user_ruk['id']==$result['id']))&&$field_rights['to_dir_sz'];
		
	}else{
		//95
		$can_confirm_price=($au->user_rights->CheckAccess('w',1122)||($user_ruk['id']==$result['id']))&&$field_rights['to_dir_sz'] ;
	}
	
	$sm->assign('can_dir_sz',$can_confirm_price);
	
	
	
	
	//общие поля для версии для печати
	//кто сгенерировал
	$ui1=new UserItem;
	$user1=$ui1->GetItemById($result['id']);
	foreach($user1 as $k=>$v) $user1[$k]=stripslashes($v);
	$sm->assign('user_signed',$user1);
	
	//$sm->assign('pdate',date('d.m.Y H:i:s'));
	$sm->assign('pdate_signed', date("d.m.Y H:i:s"));
	
	$editing_user['pdate']=date('d.m.Y', $editing_user['pdate']);
	
	
	//организация
	$_org=new OrgItem; $_opf=new opfitem;
	$org=$_org->getitembyid($result['org_id']);
	$opf=$_opf->GetItemById($org['opf_id']);
	$org['opf']=$opf['name'];
	$sm->assign('org', $org);
	
	
	$sm->assign('claim',$editing_user);
	
	$sm->assign('can_modify', ($field_rights['common'])&&in_array($editing_user['status_id'],$_editable_status_id)&&$au->user_rights->CheckAccess('w',731));
	
	$sm->assign('can_print', $au->user_rights->CheckAccess('w',1124)); 
	
	$sm->assign('can_edit',$au->user_rights->CheckAccess('w',731)); 
	
	
	$sm->assign('can_escal', ($field_rights['to_escal'])&&(($editing_user['manager_id']==$result['id'])||($editing_user['user_id']==$result['id']))&&( $user_ruk['id']!=$result['id']));
	
	
	//прикрепленные файлы: их наличие уточняется при копировании сз
	$dec_files=new DBDecorator;
	
	$dec_files->AddEntry(new SqlOrdEntry('pdate',SqlOrdEntry::DESC));
	
	 
	$dec_files->AddEntry(new SqlEntry('folder_id',0, SqlEntry::E));
 
	$dec_files->AddEntry(new UriEntry('bill_id',$id));
	
	$ffg=new MemoFileGroup(1, $id,  new FileDocFolderItem(1,  $id, new MemoFileItem(1)));
	
	$ffg->SetDocIdName('bill_id');
	
	
	$ffg->ShowFiles(
		'doc_file/list.html',
		$dec_files,
		0,
		1000,
		'memo_files.php',
		'load_memo.html', 
		'swfupl-js/memo_files.php',
		$au->user_rights->CheckAccess('w',1130),
		$au->user_rights->CheckAccess('w',1133)/*&&($user['is_confirmed_shipping']==0)*/,
		$au->user_rights->CheckAccess('w',1131),
		$folder_id,
		$au->user_rights->CheckAccess('w',1130), 
		$au->user_rights->CheckAccess('w',1130), 
		$au->user_rights->CheckAccess('w',1133), 
		$au->user_rights->CheckAccess('w',1133),
	
	
	'',
	 $au->user_rights->CheckAccess('w',1132),
	 $result,
 NULL, '', $filesdata
	 );
	 
	// echo count($filesdata);
	$sm->assign('has_files', (int)count($filesdata)>0);
	
		
		
	//подвал + qr - код
	if($print!=0){
		 $PNG_WEB_DIR = ABSPATH.'tmp/';
		 $PNG_TEMP_DIR =ABSPATH.'classes/phpqr/temp/';
		  $errorCorrectionLevel='Q';
		 $matrixPointSize = 1;
		  
		 
		 $data= 'Служебная записка '.$editing_user['code'].', статус: '.$editing_user['status'].', вид: '.$editing_user['kind_name'];
		 if($editing_user['is_ruk']==1) $data.=' утверждено руководителем отдела: '.$confirmer;
  		if($editing_user['is_dir']==1) $data.=' утверждено генеральным директором: '.$confirmerd;
  		
		
		$data=iconv('windows-1251', 'utf-8', $data);
		
		$filename = $PNG_TEMP_DIR.'memo_'.$id.'.png';	  
		
		 QRcode::png($data, $filename, $errorCorrectionLevel, $matrixPointSize,  2);   
		
	 	
		
		 
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
	
	 
	
	//общие поля для версии для печати
	//кто сгенерировал
 	$ui1=new UserItem;
	$user1=$ui1->GetItemById($result['id']);
	foreach($user1 as $k=>$v) $user1[$k]=stripslashes($v);
	$sm->assign('user_signed',$user1);
	
	//$sm->assign('pdate',date('d.m.Y H:i:s'));
	$sm->assign('pdate_signed', date("d.m.Y H:i:s"));
	 
	
	if($printmode==1) $sm->assign('has_sign', true);
	
	if($print==0) $template='memo/memo_my_history.html';
	elseif($print==1) $template='memo/memohistory_print'.$kind.'.html';
	
	else $template='memo/memohistory'.$print_add.'.html';
	
	
	
	//Вкладка "журнал событий"
	$sm->assign('has_syslog',$au->user_rights->CheckAccess('w',734));
	if($au->user_rights->CheckAccess('w',734)){
		
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
		
			$decorator->AddEntry(new SqlEntry('object_id',NULL, SqlEntry::IN_VALUES, NULL, array(729,
730,
731,
733,
734,
735,
737,
1120,
1121,
1122,
1123,
1124,
1125,
1126,
1127,
1128,
1129,
1130,
1131,
1132,
1133,
1134

)));
			$decorator->AddEntry(new SqlEntry('affected_object_id',$id, SqlEntry::E));
			//$decorator->AddEntry(new UriEntry('action',1));
			$decorator->AddEntry(new UriEntry('id',$id));
			$decorator->AddEntry(new UriEntry('tab_page',2));
			
			
			
			$llg=$log->ShowLog('syslog/log_doc.html',$decorator,$from,$to_page,'memo_my_history.php');
			
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
			$_ree_fi=new MemoFileItem;
			 
			$sql='select * from memo_file where bill_id="'.$id.'" ';
	
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
					
				 
				 
			 
				$log->PutEntry($result['id'],'отправил на электронную почту pdf-версию заявления',NULL,1124, NULL, ' № '.$editing_user['code'].', адрес эл. почты '.$email,$id);
				
				
				
				//коммент
				//примечания
				   $_lhi=new MemoNotesItem;
				  
				   $notes_params=array();
				 
				  
				 
					$notes_params['note']='Автоматическое примечание: служебная записка № '.$editing_user['code'].' была отправлена на электронную почту  '.$email.' '.date('d.m.Y H:i:s').' пользователем '.SecStr($result['name_s'].' ').'.';
					 
					
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
			$txt.='<div><strong>Служебная записка была отправлена на следующие адреса:</strong></div>';
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
		
		
			$log->PutEntry($result['id'],'получил pdf-версию служебной записки',NULL,1124, NULL, ' № '.$editing_user['code'],$id);
			 
			header('Content-type: application/pdf');
			header('Content-Disposition: attachment; filename="'.iconv('windows-1251', 'utf-8', $editing_user['code']).'.pdf'.'"');
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