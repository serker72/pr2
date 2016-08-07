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


require_once('../classes/rl/rl_recorditem.php');
require_once('../classes/rl/rl_grobjectitem.php');
require_once('../classes/rl/rl_objectitem.php');
require_once('../classes/rl/rl_man.php');
require_once('../classes/rl/rl_table.php');


require_once('../classes/messageitem.php');
require_once('../classes/posgroupitem.php');
require_once('../classes/discr_man.php');

require_once('../classes/rl_sender/rl_sec_sender.php');


$au=new AuthUser();
$result=$au->Auth();
$log=new ActionLog;

if($result===NULL){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();		
}
if(!$au->user_rights->CheckAccess('w',1)){
	
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}
 


$ret='';
if(isset($_POST['action'])&&($_POST['action']=="retrive_has_record")){
	
	//найдем набор действий для этого объекта
	$_rlg=new RLGrObjectItem;
	
	$rlg=$_rlg->GetItemByfields(array('tablename'=>SecStr($_POST['tablename']),
										'additional_id'=>abs((int)$_POST['additional_id'])));
							
	//найдем запись для ограничений
	$_rlr=new RLRecordItem;
	
	$rlr=$_rlr->GetItemByFields(array('record_id'=>abs((int)$_POST['record_id']), 'rl_group_id'=>$rlg['id']));
											
	
	if($rlr!==false) $ret=1;
	
	
}


elseif(isset($_POST['action'])&&($_POST['action']=="save_data")){
	//сохранение данных	
	$is_checked=abs((int)$_POST['is_checked']);
	$record_id=abs((int)$_POST['record_id']);
	$tablename=SecStr($_POST['tablename']);
	$additional_id=abs((int)$_POST['additional_id']);
	
	$record_name=SecStr(iconv('utf-8','windows-1251',$_POST['record_name']));
	$record_description=SecStr(iconv('utf-8','windows-1251',$_POST['record_description']));
	
	
	$users=($_POST['users']);
	$objects=($_POST['objects']);
	$letters=($_POST['letters']);
	$actions=($_POST['actions']);
	
	
	//найдем набор действий для этого объекта
	$_rlg=new RLGrObjectItem;
	
	$rlg=$_rlg->GetItemByfields(array('tablename'=>$tablename,
										'additional_id'=>$additional_id));
	$value='Объект '.SecStr($rlg['name']).', запись '.SecStr($record_name).', описание записи: '.SecStr($record_description);
	
	
	
							
	//найдем запись для ограничений
	$_rlr=new RLRecordItem;
	
	$rlr=$_rlr->GetItemByFields(array('record_id'=>$record_id, 'rl_group_id'=>$rlg['id']));
	
	
	
	//для рассылки сообщений о смене оборудования...
	$_mm=new MessageItem;
	$_gi=new PosGroupItem;
	$_dman=new DiscrMan;
	$_sender=new RlSecSender;
	
	
	$eqs=array();
	if($rlr['rl_group_id']==5){
		$sql='select p.id from catalog_position as p inner join pl_position as pl on p.id=pl.position_id where p.parent_id=0 and ( 
			p.group_id="'.$rlr['record_id'].'" 
			or p.group_id in (select id from catalog_group where parent_group_id="'.$rlr['record_id'].'" ) 
			or  p.group_id in (select id from catalog_group where id in( select id from catalog_group where parent_group_id="'.$rlr['record_id'].'" ))
			
			)';
		
		/*$gi=$_gi->GetItemById($rlr['record_id']);
		if($gi['parent_group_id']>0){
			$gi2=$_gi->GetItemById($gi['parent_group_id']);	
			
			//if(in_array($gi['parent_group_id'], $restricted_groups)) $put=$put&&false;
			$sql.=' or p.group_id="'.$gi2['id'].'" ';
			if($gi2['parent_group_id']>0){
				$gi3=$_gi->GetItemById($gi2['parent_group_id']);		
				
				//$f['group_name']=stripslashes($gi3['name'].'-> '.$gi2['name'].'-> '.$gi['name']);
				//if(in_array($gi2['parent_group_id'], $restricted_groups)) $put=$put&&false;
				$sql.=' or p.group_id="'.$gi3['id'].'" ';
			}else{
				
				//$f['group_name']=stripslashes($gi2['name'].'-> '.$gi['name']);	
			}
		}else{
				
			//if(in_array($f['group_id'], $restricted_groups)) $put=$put&&false;
		}
		*/
		
		//$sql.=' ) ';
			
		$set=new mysqlset($sql);
		$rs=$set->getResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$eqs[]=$f['id'];	
		}
			
	}elseif($rlr['rl_group_id']==6){
		$sql='select p.id from catalog_position as p inner join pl_position as pl on p.id=pl.position_id where p.parent_id=0 and   p.producer_id="'.$rlr['record_id'].'" ';
		
		$set=new mysqlset($sql);
		$rs=$set->getResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$eqs[]=$f['id'];	
		}
	}
	
	
//	echo $sql;
	
	if($rlr!==false){
		//запись есть!
		$rl_record_id=$rlr['id'];
		if($is_checked==0){
			$_rlr->Del($rl_record_id);
			
			
			if(($rlr['rl_group_id']==5)||($rlr['rl_group_id']==6)){
				$_dman=new DiscrMan;
				$_sender->LoadAndSend($eqs, $_dman->GetUsersByRight('w', 696));
			}
			
			$log->PutEntry($result['id'],'выключил разграничение доступа к записи', NULL, 1,NULL,$value,$record_id);
		}
	}else{
		//записи нет!
		if($is_checked==1) {
			$rl_record_id=$_rlr->Add(array('record_id'=>$record_id, 'rl_group_id'=>$rlg['id']));
			$log->PutEntry($result['id'],'включил разграничение доступа  к записи',NULL, 1,NULL,$value,$record_id);
		}
	}
	
	
	$_rlm=new RLMan;
	$_rlo=new RLObjectItem;
	if($is_checked==1) {
		//проверять и заносить права!
		
		foreach($users as $k=>$user){
			
			$letter=$letters[$k];
			$object=$objects[$k];
			$action=$actions[$k];
			
			$has=$_rlm->CheckAccess($rl_record_id,$user,$letter,$object);
			
			
			$rlo=$_rlo->GetItemById($object);
			$value1=$value.', '.SecStr($rlo['name']);
			
			if(($action==0)&&($has)){
				//отнять право
				echo 'away '.$rl_record_id.' '.$user.' '.$letter.' '.$object;
				$_rlm->RevokeAccess($rl_record_id, $user, $letter,$object);	
				
				$log->PutEntry($result['id'],'удалил доступ к записи',$user, 1,NULL,$value1,$record_id);
				
				
			}
			elseif(($action==1)&&(!$has)){
				//дать право
				echo 'to '.$rl_record_id.' '.$user.' '.$letter.' '.$object;
				$_rlm->GrantAccess($rl_record_id, $user, $letter,  $object);
				
				if(($rlr['rl_group_id']==5)||($rlr['rl_group_id']==6)){
					//print_r($eqs); print_r(array($user));
					 
					$_sender->LoadAndSend($eqs, array($user));
				}
				
				
				$log->PutEntry($result['id'],'установил доступ к записи',$user, 1,NULL,$value1,$record_id);
			}
			
		}
			
		
	}
	
}
//retrive_table

elseif(isset($_POST['action'])&&($_POST['action']=="retrive_table")){
	 
	$record_id=abs((int)$_POST['record_id']);
	$tablename=SecStr($_POST['tablename']);
	$additional_id=abs((int)$_POST['additional_id']);
	
	
	//найдем набор действий для этого объекта
	$_rlg=new RLGrObjectItem;
	
	$rlg=$_rlg->GetItemByfields(array('tablename'=>$tablename,
										'additional_id'=>$additional_id));
	$value='Объект '.SecStr($rlg['name']);
							
	//найдем запись для ограничений
	$_rlr=new RLRecordItem;
	
	$rlr=$_rlr->GetItemByFields(array('record_id'=>$record_id, 'rl_group_id'=>$rlg['id']));
	
	
	if($rlr!==false){
		
		$_tab=new RlTable($rlg['id'], $rlr['id']);
		$ret=$_tab->Draw('admin_records/admin_users_byrow.html');
			
	}else $ret='Для выбранного объекта не включено разграничение доступа. Пожалуйста, отметьте галочку "Включить разграничение доступа" и нажмите "Сохранить и остаться" для включения разграничения доступа и дальнейшего редактирования прав.';
}

elseif(isset($_POST['action'])&&($_POST['action']=="draw_row_rights")){
	//вывод позиций вх. счета для распоряжения
	
	$user_id=abs((int)$_POST['user_id']);
	$gr_id=abs((int)$_POST['gr_id']);
	$record_id=abs((int)$_POST['record_id']);
	
	$_ui=new UserSItem;
	$user=$_ui->Getitembyid($user_id);
	//$object_id=abs((int)$_POST['object_id']);
	
	//echo $gr_id;
	$sm=new SmartyAj;
	
	//найдем запись для ограничений
	$_rlr=new RLRecordItem;
	
	$rlr=$_rlr->GetItemByFields(array('record_id'=>$record_id, 'rl_group_id'=>$gr_id));
	
	
	$dt=new RLTable($gr_id,  $rlr['id']);
	
	
	
	
	$rights=$dt->GetTableRowArr($user_id);
	
	$sm->assign('user',$user);
	$sm->assign('rights_arr',$rights);
	
	
	$ret.=$sm->fetch("admin_records/admin_users_row.html");
}

//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>