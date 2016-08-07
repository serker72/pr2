<?
session_start();
header('Content-type: text/html; charset=windows-1251');

require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/discr_table_objects.php');
require_once('../classes/actionlog.php');

  
require_once('../classes/tender.class.php');
require_once('../classes/tender_history_fileitem.php');
require_once('../classes/tender_history_item.php');
require_once('../classes/tender_history_group.php');
require_once('../classes/docstatusitem.php');

$au=new AuthUser();
$result=$au->Auth(false,false,false);
$log=new ActionLog;

if($result===NULL){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();		
}
 

$ret='';

$timeout=15;

if(isset($_GET['action'])&&($_GET['action']=="calc_new_kind1")){
	$_rem=new Tender_PopupGroup;
	
	//проверим МАРКЕР
	$sql='select * from tender_marker where kind_id=1 and user_id="'.$result['id'].'" and ptime>="'.(time()-$timeout*60).'"';
	$set=new mysqlset($sql);
	$rs=$set->GetResult();
	$rc=$set->GetResultNumRows();
	if($rc>0){
		//маркер есть, возвращаем -1, ничего не делаем
		$ret=-1;
		
		//echo ' HAS MARK ';
	}else{
		//маркера нет, возвращаем актуальное число и вносим новый маркер	
		$ret=$_rem->CalcKind1();
		
		$_mark=new Tender_MarkerItem;
		$mark=$_mark->GetItemByFields(array('kind_id'=>1, 'user_id'=>$result['id']));
		if($mark===false) $_mark->Add(array('kind_id'=>1, 'ptime'=>time(),  'user_id'=>$result['id']));
		else $_mark->Edit($mark['id'], array('ptime'=> time()));
		
		//echo ' NO MARK ';
	}
	
	
	
}
elseif(isset($_GET['action'])&&($_GET['action']=="calc_new_kind2")){
	$_rem=new Tender_PopupGroup;
	
	//проверим МАРКЕР
	$sql='select * from tender_marker where kind_id=2  and user_id="'.$result['id'].'" and ptime>="'.(time()-$timeout*60).'"';
	$set=new mysqlset($sql);
	$rs=$set->GetResult();
	$rc=$set->GetResultNumRows();
	if($rc>0){
		//маркер есть, возвращаем -1, ничего не делаем
		$ret=-1;
		
		//echo ' HAS MARK ';
	}else{
		//маркера нет, возвращаем актуальное число и вносим новый маркер	
		$ret=$_rem->CalcKind2($result['id']);
		
		$_mark=new Tender_MarkerItem;
		$mark=$_mark->GetItemByFields(array('kind_id'=>2, 'user_id'=>$result['id']));
		if($mark===false) $_mark->Add(array('kind_id'=>2, 'ptime'=>time(),  'user_id'=>$result['id']));
		else $_mark->Edit($mark['id'], array('ptime'=> time()));
		
		//echo ' NO MARK ';
	}
	
	
	
}
elseif(isset($_GET['action'])&&($_GET['action']=="load_kind1")){
	//подгрузка данных для показа
	$_rem=new Tender_PopupGroup;
	$data=$_rem->ShowKind1();
	$sm=new SmartyAj;
	
	$sm->assign('session_id', session_id());
	$sm->assign('items', $data);
	$ret=$sm->fetch('tender/tender_head_data1.html');
	
}
elseif(isset($_GET['action'])&&($_GET['action']=="load_kind2")){
	//подгрузка данных для показа
	$_rem=new Tender_PopupGroup;
	$data=$_rem->ShowKind2($result['id']);
	$sm=new SmartyAj;
	
	
	$sm->assign('items', $data);
	$ret=$sm->fetch('tender/tender_head_data2.html');
	


}elseif(isset($_POST['action'])&&($_POST['action']=="put_statuses")){
	
	$_ti=new Tender_Item;
	$_th=new Tender_HistoryItem;
	$_tf=new Tender_HistoryFileItem;
	$_stat=new DocStatusItem;
	
	$log=new ActionLog;
	
	$data=$_POST['data'];
	if(is_array($data))	foreach($data as $k=>$val){
		$v=explode("/", $val);
		if($v[1]==0) continue;
		
		$params=array();
		$params['status_id']=(int)$v[1];	
		$stat=$_stat->GetItembyid($params['status_id']);
		
		if($params['status_id']==29){
			$params['pdate_itog']=datefromdmy($v[2]);
			$comment= ('Установлен статус '.$stat['name'].', комментарий: '.SecStr(iconv('utf-8', 'windows-1251', $v[3])).', дата подведения итога конкурса: '.$v[2]);	
		}else $comment= ('Установлен статус '.$stat['name']);
		
		$tender_id=abs((int)$v[0]);
		$_ti->Edit($tender_id, $params,true,$result);
		
		
		
		$log->PutEntry($result['id'],'смена статуса тендера',NULL,931,NULL,$comment,$tender_id);
			
		
		//if($params['status_id']==29){
			//внесем коммент и файлы
			$len_params=array();
				$len_params['sched_id']=$tender_id;
				$len_params['txt']=SecStr('<div>'.$comment.'</div>');
				$len_params['user_id']=$result['id'];
				$len_params['pdate']=time();
				
				$len_code= $_th->Add($len_params);
				
			if(isset($v[4])){
				$files1=explode('|',$v[4]);
				$files2=explode('|', $v[5]);
				
				foreach($files1 as $kk=>$vv){
					  
						$fp=array('history_id '=>$len_code, 'filename'=>SecStr(basename($vv)), 'orig_name'=>SecStr($files2[$kk]));
						   
						//print_r($fp);  
						$file_id=$_tf->Add($fp);
				}
			}
		//}
	}

}
 
 
 
elseif(isset($_GET['action'])&&($_GET['action']=="calc_new_kind3")){
	$_rem=new Tender_PopupGroup;
	
	
	$timeout3=24*60*60;
	
	//проверим МАРКЕР
	$sql='select * from tender_marker where kind_id=3 and user_id="'.$result['id'].'" and ptime>="'.(time()-$timeout3*60).'"';
	$set=new mysqlset($sql);
	$rs=$set->GetResult();
	$rc=$set->GetResultNumRows();
	if($rc>0){
		//маркер есть, возвращаем -1, ничего не делаем
		$ret=-1;
		
		//echo ' HAS MARK ';
	}else{
		//маркера нет, возвращаем актуальное число и вносим новый маркер	
		$ret=$_rem->CalcKind3($result['id']);
		
		$_mark=new Tender_MarkerItem;
		$mark=$_mark->GetItemByFields(array('kind_id'=>3, 'user_id'=>$result['id']));
		if($mark===false) $_mark->Add(array('kind_id'=>3, 'ptime'=>time(),  'user_id'=>$result['id']));
		else $_mark->Edit($mark['id'], array('ptime'=> time()));
		
		//echo ' NO MARK ';
	}
	
	
	
} 

elseif(isset($_GET['action'])&&($_GET['action']=="load_kind3")){
	//подгрузка данных для показа
	$_rem=new Tender_PopupGroup;
	$data=$_rem->ShowKind3($result['id']);
	$sm=new SmartyAj;
	
	$sm->assign('session_id', session_id());
	$sm->assign('items', $data);
	$ret=$sm->fetch('tender/tender_head_data3.html');
	
 

}elseif(isset($_POST['action'])&&($_POST['action']=="put_statuses3")){
	
	$_ti=new Tender_Item;
	$_th=new Tender_HistoryItem;
	$_tf=new Tender_HistoryFileItem;
	$_stat=new DocStatusItem;
	
	$log=new ActionLog;
	
	$data=$_POST['data'];
	if(is_array($data))	foreach($data as $k=>$val){
		$v=explode("/", $val);
		//
		$tender_id=abs((int)$v[0]);
		
		if($v[1]==1) {
		
			$params=array();
			$params['status_id']=36;	
			$stat=$_stat->GetItembyid($params['status_id']);
			
			
			$ti=$_ti->GetItembyid($tender_id); $old_stat=$_stat->getitembyid($ti['status_id']);
			 
			$comment= (' Статус сменен с '.$old_stat['name'].' на '.$stat['name']);
			
			
			$_ti->Edit($tender_id, $params,true,$result);
			
			
			
			$log->PutEntry($result['id'],'смена статуса тендера',NULL,931,NULL,$comment,$tender_id);
			
			$comment='Автоматический комментарий: '.$comment;
		}else{
			$comment=SecStr('Причина, по которой пользователь подтвердил, что работа по тендеру ведется: '.iconv('utf-8', 'windows-1251', $v[2]));
		}
		
		 
			//внесем коммент  
			$len_params=array();
				$len_params['sched_id']=$tender_id;
				$len_params['txt']=SecStr('<div>'.$comment.'</div>');
				$len_params['user_id']=0;
				$len_params['pdate']=time();
				
				$len_code= $_th->Add($len_params);
				
			 
		 
	}

}


elseif(isset($_GET['action'])&&($_GET['action']=="calc_new_kind4")){
	$_rem=new Tender_PopupGroup;
	
	$timeout=60; 
	 
	$do_go=true;
 	$kind=4;
	
	 
	$do_go=(/*($result['main_department_id']==1)&&*/($result['department_id']==1)&&in_array($result['position_id'], array(33,37)));
	
	if($do_go){
	
		
		//проверим МАРКЕР
		$sql='select * from tender_marker where kind_id="'.$kind.'" and user_id="'.$result['id'].'" and ptime>="'.(time()-60*60).'"';
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		//$rc=0;
		
		
		if($rc>0){
			//маркер есть, возвращаем -1, ничего не делаем
			$ret=-1;
			
			//echo ' HAS MARK ';
		}else{
			//маркера нет, возвращаем актуальное число и вносим новый маркер	
			$ret=$_rem->CalcKind4($result['id']);
			
			$_mark=new Tender_MarkerItem;
			$mark=$_mark->GetItemByFields(array('kind_id'=>$kind, 'user_id'=>$result['id']));
			if($mark===false) $_mark->Add(array('kind_id'=>$kind, 'ptime'=>time(),  'user_id'=>$result['id']));
			
			//else $_mark->Edit($mark['id'], array('ptime'=> time()));
			
			//echo ' NO MARK ';
		}
	
	}else{
		
		//echo ' NO TIME ';
		
		$ret=-1;
		
	}
	
}

elseif(isset($_GET['action'])&&($_GET['action']=="load_kind4")){
	//подгрузка данных для показа
	$_rem=new Tender_PopupGroup;
	$data=$_rem->ShowKind4($result['id'],$fail_ids, $fail_vals);
	$sm=new SmartyAj;
	
	$kind=abs((int)$_GET['kind']);
	$sm->assign('session_id', session_id());
	$sm->assign('data_kind', $kind);
	
	$sm->assign('session_id', session_id());
	$sm->assign('fail_ids', $fail_ids);
	$sm->assign('fail_vals', $fail_vals);
	
	$sm->assign('items', $data);
	
	$ret=$sm->fetch('tender/tender_head_data4.html');
	
}


elseif(isset($_POST['action'])&&($_POST['action']=="put_work_statuses4")){
	
	$_ti=new Tender_Item;
	$_th=new Tender_HistoryItem;
	$_tf=new Tender_HistoryFileItem;
	$_stat=new DocStatusItem;
	
	$_lci=new Tender_CountsItem;
	$log=new ActionLog;
	
	$kind=abs((int)$_POST['kind']);
	
	$_roles=new Tender_FieldRules($result); //var_dump($_roles->GetTable());
	
	
	$data=$_POST['data'];
	if(is_array($data))	foreach($data as $k=>$val){
		$v=explode("/", $val);
		
		$tender_id=abs((int)$v[0]);
		 $ti=$_ti->GetItemById($tender_id);
		
		
		if($v[1]==0) {
			 
			 
			 
			 //принять в работу
			 $params=array();
			 $params['manager_id']=$result['id'];
			 $_ti->Edit($tender_id, $params, true, $result);
			 
			 $log->PutEntry($result['id'],'правка тендера',NULL,931,NULL,SecStr('установлен ответственный сотрудник '.$result['name_s']),$tender_id);
			 
			 
			
		}else{
		
			 //отложить
			 
			 
			$lci=$_lci->GetItemByFields(array('user_id'=>$result['id'], 'tender_id'=>$tender_id, 'kind_id'=>1));
			
			if($lci===false) $num_refuse=1;
			else $num_refuse=(int)$lci['value'] + 1;
			
			$log->PutEntry($result['id'],'отказ сотрудника взять в работу тендер в '.$num_refuse.' раз',NULL,931,NULL,'отказ сотрудника взять в работу тендер '.$ti['code'],$tender_id);
			
			if(($lci!==false)&&($lci['value']>=2)){
				$_lci->Edit($lci['id'], array('value'=>0));
				
				
				
				
				//найти прошлые отказы
				//найти прошлую запись об отказе этого сотрудника
				$_tender_an=new Tender_LogAn;
				$refuse_comment=$_tender_an->GetRefuses($result['id'], $tender_id, 931, 'отказ сотрудника взять в работу тендер 3 раз подряд', 'отказ сотрудника взять в работу тендер в 1 раз', 'отказ сотрудника взять в работу тендер в 2 раз', 'отказ сотрудника взять в работу тендер в 3 раз');
				
				
				
				//внесем коммент
				$len_params=array();
				$len_params['sched_id']=$tender_id;
				$len_params['txt']=SecStr('<div>Автоматический комментарий: Сотрудник '.$result['name_s'].' отказался 3 раза подряд взять в работу тендер '.$ti['code'].'.</div>'.$refuse_comment);
				$len_params['user_id']=0;
				$len_params['pdate']=time();
				
				$len_code= $_th->Add($len_params);
				
				$log->PutEntry($result['id'],'отказ сотрудника взять в работу тендер 3 раз подряд',NULL,931,NULL,'отказ сотрудника взять в работу тендер '.$ti['code'],$tender_id);
				
				
			}else{
				if($lci!==false) $_lci->Edit($lci['id'], array('value'=>((int)$lci['value']+1)));
				else $_lci->Add(array('user_id'=>$result['id'], 'tender_id'=>$tender_id, 'kind_id'=>1));
				
				
			}
			
			
			 
			
		 
		}
	}

}
	
	
elseif(isset($_GET['action'])&&($_GET['action']=="calc_new_kind5")){
	$_rem=new Tender_PopupGroup;
	
	
	 
	$do_go=true;
 	$kind=abs((int)$_GET['kind']);
	
	
	/*
	проверка тендеров и оповещение трижды в день
	if($kind==5){
		$timeout=30*24*60; 
	}elseif($kind==6){
		$timeout=14*24*60; 
	}elseif($kind==7){
		$timeout=24*60; 
	}*/
	
	if(((int)date('H')<13)&&((int)date('H')>=9)){
		$kind=5;
	}elseif(((int)date('H')<18)&&((int)date('H')>=13)){
		$kind=6;
	}elseif(((int)date('H')<=23)&&((int)date('H')>=18)){
		$kind=7;
	}
	 
	
	
	if($do_go){
	
		
		//проверим МАРКЕР
		//$sql='select * from tender_marker where kind_id="'.$kind.'" and user_id="'.$result['id'].'" and ptime>="'.(time()-$timeout*60).'"';
		
		$sql='select * from tender_marker where kind_id="'.$kind.'" and user_id="'.$result['id'].'" and ptime>="'. mktime(0,0,0,date('n'),date('j'),date('Y')) .'" and ptime<="'. mktime(23,59,59,date('n'),date('j'),date('Y')) .'"';
		
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		//$rc=0;
		
		
		if($rc>0){
			//маркер есть, возвращаем -1, ничего не делаем
			$ret=-1;
			
			//echo ' HAS MARK ';
		}else{
			//маркера нет, возвращаем актуальное число и вносим новый маркер	
			$ret=$_rem->CalcKind567($result['id'],$kind);
			
			$_mark=new Tender_MarkerItem;
			$mark=$_mark->GetItemByFields(array('kind_id'=>$kind, 'user_id'=>$result['id']));
			if($mark===false) $_mark->Add(array('kind_id'=>$kind, 'ptime'=>time(),  'user_id'=>$result['id']));
			
			else $_mark->Edit($mark['id'], array('ptime'=> time()));
			
			//echo ' NO MARK ';
		}
	
	}else{
		
		//echo ' NO TIME ';
		
		$ret=-1;
		
	}
	
}

elseif(isset($_GET['action'])&&($_GET['action']=="load_kind5")){
	//подгрузка данных для показа
	$_rem=new Tender_PopupGroup;
	$data=$_rem->ShowKind567($result['id'],$fail_ids, $fail_vals);
	$sm=new SmartyAj;
	
	$kind=abs((int)$_GET['kind']);
	$sm->assign('session_id', session_id());
	$sm->assign('data_kind', $kind);
	
	$sm->assign('session_id', session_id());
	$sm->assign('fail_ids', $fail_ids);
	$sm->assign('fail_vals', $fail_vals);
	
	$sm->assign('items', $data);
	
	$ret=$sm->fetch('tender/tender_head_data5.html');
	
}



elseif(isset($_POST['action'])&&($_POST['action']=="put_work_statuses5")){
	
	$_ti=new Tender_Item;
	$_th=new Tender_HistoryItem;
	$_tf=new Tender_HistoryFileItem;
	$_stat=new DocStatusItem;
	
	$_lci=new Tender_CountsItem;
	$log=new ActionLog;
	$_dsi=new DocStatusItem;
	
	$kind=abs((int)$_POST['kind']);
	
	$_roles=new Tender_FieldRules($result); //var_dump($_roles->GetTable());
	
	
	$data=$_POST['data'];
	if(is_array($data))	foreach($data as $k=>$val){
		$v=explode("/", $val);
		
		$tender_id=abs((int)$v[0]);
		
		$ti=$_ti->GetItemById($tender_id);
		
		
		if($v[1]==0) {
			 
			 
			 
			 //принять в работу
			  
			 $field_rights=$_roles->GetFields($ti, $result['id']);
		
			  
			
			 if($field_rights['to_work']){
			
				$setted_status_id=28;
				$_ti->Edit($tender_id,array( 'status_id'=>$setted_status_id),true, $result);
				
				 
				$stat=$_dsi->GetItemById($setted_status_id);
				$log->PutEntry($result['id'],'смена статуса тендера',NULL,931,NULL,'установлен статус '.$stat['name'],$tender_id);
				
				
					$len_params=array();
					$len_params['sched_id']=$tender_id;
					$len_params['txt']='Автоматический комментарий: сотрудником '.$result['name_s'].' '.'установлен статус '.$stat['name'];
					$len_params['user_id']=0;
					$len_params['pdate']=time();
					
					$len_code= $_th->Add($len_params);
			
				 
						
			}		
			
		}else{
		
			 //отложить
			 
			 
			$lci=$_lci->GetItemByFields(array('user_id'=>$result['id'], 'tender_id'=>$tender_id, 'kind_id'=>2));
			
			
			if($lci===false) $num_refuse=1;
			else $num_refuse=(int)$lci['value'] + 1;
				
			$log->PutEntry($result['id'],'отказ сотрудника принять в работу тендер в '.$num_refuse.' раз',NULL,931,NULL,'отказ сотрудника принять в работу тендер '.$ti['code'],$tender_id);
			
			if(($lci!==false)&&($lci['value']>=2)){
				$_lci->Edit($lci['id'], array('value'=>0));
				
				//найти прошлые отказы
				 $_tender_an=new Tender_LogAn;
				$refuse_comment=$_tender_an->GetRefuses($result['id'], $tender_id, 931, 'отказ сотрудника принять в работу тендер 3 раз подряд', 'отказ сотрудника принять в работу тендер в 1 раз', 'отказ сотрудника принять в работу тендер в 2 раз', 'отказ сотрудника принять в работу тендер в 3 раз');
				
				
				
				
				//внесем коммент
				$len_params=array();
				$len_params['sched_id']=$tender_id;
				$len_params['txt']=SecStr('<div>Автоматический комментарий: Сотрудник '.$result['name_s'].' отказался три раза подряд принять в работу тендер '.$ti['code'].'.</div>'.$refuse_comment);
				$len_params['user_id']=0;
				$len_params['pdate']=time();
				
				$len_code= $_th->Add($len_params);
				
				$log->PutEntry($result['id'],'отказ сотрудника принять в работу тендер 3 раз подряд',NULL,931,NULL,'отказ сотрудника принять в работу тендер '.$ti['code'],$tender_id);
				
				
			}else{
				if($lci!==false) $_lci->Edit($lci['id'], array('value'=>((int)$lci['value']+1)));
				else $_lci->Add(array('user_id'=>$result['id'], 'tender_id'=>$tender_id, 'kind_id'=>2));
				
				
			}
			
			
			 
			
		 
		}
	}

}
	

elseif(isset($_GET['action'])&&($_GET['action']=="calc_new_kind8")){
	$_rem=new Tender_PopupGroup;
	
	$timeout=60*24; 
	 
	$do_go=true;
 	$kind=8;
	
	 
	 
	
	if($do_go){
	
		
		//проверим МАРКЕР
		$sql='select * from tender_marker where kind_id="'.$kind.'" and user_id="'.$result['id'].'" and ptime>="'. mktime(0,0,0,date('n'),date('j'),date('Y')) .'" and ptime<="'. mktime(23,59,59,date('n'),date('j'),date('Y')) .'"';
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		//$rc=0;
		
		
		if($rc>0){
			//маркер есть, возвращаем -1, ничего не делаем
			$ret=-1;
			
			//echo ' HAS MARK ';
		}else{
			//маркера нет, возвращаем актуальное число и вносим новый маркер	
			$ret=$_rem->CalcKind8($result['id']);
			
			$_mark=new Tender_MarkerItem;
			$mark=$_mark->GetItemByFields(array('kind_id'=>$kind, 'user_id'=>$result['id']));
			if($mark===false) $_mark->Add(array('kind_id'=>$kind, 'ptime'=>time(),  'user_id'=>$result['id']));
			
			//else $_mark->Edit($mark['id'], array('ptime'=> time()));
			
			//echo ' NO MARK ';
		}
	
	}else{
		
		//echo ' NO TIME ';
		
		$ret=-1;
		
	}
	
}
elseif(isset($_GET['action'])&&($_GET['action']=="load_kind8")){
	//подгрузка данных для показа
	$_rem=new Tender_PopupGroup;
	$data=$_rem->ShowKind8($result['id'],$fail_ids, $fail_vals);
	$sm=new SmartyAj;
	
	$kind=abs((int)$_GET['kind']);
	$sm->assign('session_id', session_id());
	$sm->assign('data_kind', $kind);
	
	$sm->assign('session_id', session_id());
	$sm->assign('fail_ids', $fail_ids);
	$sm->assign('fail_vals', $fail_vals);
	
	$sm->assign('items', $data);
	
	$ret=$sm->fetch('tender/tender_head_data8.html');
	
}

elseif(isset($_POST['action'])&&($_POST['action']=="put_work_statuses8")){
	
	$_ti=new Tender_Item;
	$_th=new Tender_HistoryItem;
	$_tf=new Tender_HistoryFileItem;
	$_stat=new DocStatusItem;
	
	$_lci=new Tender_CountsItem;
	$log=new ActionLog;
	$_dsi=new DocStatusItem;
	
	$kind=abs((int)$_POST['kind']);
	
	$_roles=new Tender_FieldRules($result); //var_dump($_roles->GetTable());
	
	
	$data=$_POST['data'];
	if(is_array($data))	foreach($data as $k=>$val){
		$v=explode("/", $val);
		
		$tender_id=abs((int)$v[0]);
		
		$ti=$_ti->GetItemById($tender_id);
		
		
		if($v[1]==0) {
			 
			 
			 
			 //коммент
			  	//внесем коммент
				$len_params=array();
				$len_params['sched_id']=$tender_id;
				$len_params['txt']=SecStr(iconv('utf-8','windows-1251', $v[2]));
				$len_params['user_id']=$result['id'];
				$len_params['pdate']=time();
				
				$len_code= $_th->Add($len_params);
				
				$log->PutEntry($result['id'],'внес комментарий в тендер',NULL,931,NULL,$len_params['txt'],$tender_id);
			
		}elseif($v[1]==1){
			// в отказ
			$params=array();
			$params['status_id']=37;
			
			
			$stat=$_dsi->GetItembyid($params['status_id']);
			
			$comment= ('Установлен статус '.$stat['name']);
			
			
			 
			 
			$params['fail_reason_id']=abs((int)$v[3]);
			$params['fail_reason']=SecStr(iconv('utf-8','windows-1251',$v[4]));
			
			$_fi=new Lead_FailItem;
			$fi=$_fi->GetItemById($params['fail_reason_id']);
			
			$comment.=', причина '.SecStr($fi['name']).' '.$params['fail_reason'];
					
			 
				
			
			
			$_ti->Edit($tender_id, $params,true,$result);
			
			
			
			$log->PutEntry($result['id'],'смена статуса тендера',NULL,951,NULL,$comment,$tender_id);
			 
			
			 	$len_params=array();
			$len_params['sched_id']=$tender_id;
			$len_params['txt']='Автоматический комментарий: сотрудником '.$result['name_s'].' '.$comment;
			$len_params['user_id']=0;
			$len_params['pdate']=time();
			
			$len_code= $_th->Add($len_params);
			
			
		 
		}
	}

}

elseif(isset($_GET['action'])&&(($_GET['action']=="calc_new_kind9")||($_GET['action']=="calc_new_kind10"))){
	$_rem=new Tender_PopupGroup;
	
	$timeout=60*60; 
	 
	$do_go=true;
 	if($_GET['action']=="calc_new_kind9") $kind=9;
	else $kind=10;
	
	 
	 
	
	if($do_go){
	
		
		//проверим МАРКЕР
		$sql='select * from tender_marker where kind_id="'.$kind.'"  and  ptime>="'.( time()-$timeout) .'"';
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		//$rc=0;
		//echo $sql;
		
		if($rc>0){
			//маркер есть, возвращаем -1, ничего не делаем
			$ret=-1;
			
			//echo ' HAS MARK ';
		}else{
			//маркера нет, рассылаем сообщение определенного вида и вносим новый маркер	
			$ret=0;
			 
			if($_GET['action']=="calc_new_kind9") $_rem->Send910(9);
			else  $_rem->Send910(10);
			
			
			$_mark=new Tender_MarkerItem;
			$mark=$_mark->GetItemByFields(array('kind_id'=>$kind  ));
			if($mark===false) $_mark->Add(array('kind_id'=>$kind, 'ptime'=>time()));
			
			//else $_mark->Edit($mark['id'], array('ptime'=> time()));
			
			//echo ' NO MARK ';
		}
	
	}else{
		
		//echo ' NO TIME ';
		
		$ret=-1;
		
	}
	
}

	
//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>