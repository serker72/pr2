<?
session_start();
header('Content-type: text/html; charset=windows-1251');

require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/discr_table_objects.php');
require_once('../classes/actionlog.php');

  
require_once('../classes/lead.class.php');
require_once('../classes/lead_history_fileitem.php');
require_once('../classes/lead_history_item.php');
require_once('../classes/lead_history_group.php');
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



if(isset($_GET['action'])&&(($_GET['action']=="calc_new_kind123")/*||($_GET['action']=="calc_new_kind2")||($_GET['action']=="calc_new_kind3")*/)){
	$_rem=new Lead_PopupGroup;
	
	$timeout=60*24; 
	 
	$do_go=true;
 	$kind=1;
	
	if(((int)date('H')<13)&&((int)date('H')>=9)){
		$kind=1;
	}elseif(((int)date('H')<18)&&((int)date('H')>=13)){
		$kind=2;
	}elseif(((int)date('H')<=23)&&((int)date('H')>=18)){
		$kind=3;
	}
	
	
	if($do_go){
	
		
		//проверим МАРКЕР
		$sql='select * from lead_marker where kind_id="'.$kind.'" and user_id="'.$result['id'].'" and ptime>="'. mktime(0,0,0,date('n'),date('j'),date('Y')) .'" and ptime<="'. mktime(23,59,59,date('n'),date('j'),date('Y')) .'"';
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
			$ret=$_rem->CalcKind123($result['id']);
			
			$_mark=new lead_MarkerItem;
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

elseif(isset($_GET['action'])&&($_GET['action']=="load_kind1")){
	//подгрузка данных для показа
	$_rem=new Lead_PopupGroup;
	$data=$_rem->ShowKind123($result['id']);
	$sm=new SmartyAj;
	
	$sm->assign('session_id', session_id());
	$sm->assign('items', $data);
	$ret=$sm->fetch('lead/lead_head_data1.html');
	
}



elseif(isset($_POST['action'])&&($_POST['action']=="put_work_statuses")){
	
	$_ti=new Lead_Item;
	$_th=new Lead_HistoryItem;
	$_tf=new Lead_HistoryFileItem;
	$_stat=new DocStatusItem;
	
	$_lci=new Lead_CountsItem;
	$log=new ActionLog;
	
	$data=$_POST['data'];
	if(is_array($data))	foreach($data as $k=>$val){
		$v=explode("/", $val);
		
		$tender_id=abs((int)$v[0]);
		$ti=$_ti->GetItemById($tender_id);
		
		if($v[1]==0) {
			
			 
			$lci=$_lci->GetItemByFields(array('user_id'=>$result['id'], 'lead_id'=>$tender_id, 'kind_id'=>1));
			
			if($lci===false) $num_refuse=1;
			else $num_refuse=(int)$lci['value'] + 1;
			
			$log->PutEntry($result['id'],'отказ сотрудника принять лид в работу в '.$num_refuse.' раз',NULL,950,NULL,'отказ сотрудника принять лид в работу, лид '.$ti['code'],$tender_id);
			
			if(($lci!==false)&&($lci['value']>=2)){
				$_lci->Edit($lci['id'], array('value'=>0));
				
				
				//найти прошлые отказы
				 $_tender_an=new Tender_LogAn;
				$refuse_comment=$_tender_an->GetRefuses($result['id'], $tender_id, 950, 'отказ сотрудника принять лид в работу 3 раз подряд', 'отказ сотрудника принять лид в работу в 1 раз', 'отказ сотрудника принять лид в работу в 2 раз', 'отказ сотрудника принять лид в работу в 3 раз');
				
				
				//внесем коммент
				$len_params=array();
				$len_params['sched_id']=$tender_id;
				$len_params['txt']=SecStr('<div>Автоматический комментарий: Сотрудник '.$result['name_s'].' отказался 3 раза подряд принять лид в работу.</div>'.$refuse_comment);
				$len_params['user_id']=0;
				$len_params['pdate']=time();
				
				$len_code= $_th->Add($len_params);
				
				$log->PutEntry($result['id'],'отказ сотрудника принять лид в работу 3 раз подряд',NULL,950,NULL,'отказ сотрудника принять лид в работу три раза подряд',$tender_id);
				
				
			}else{
				if($lci!==false) $_lci->Edit($lci['id'], array('value'=>((int)$lci['value']+1)));
				else $_lci->Add(array('user_id'=>$result['id'], 'lead_id'=>$tender_id, 'kind_id'=>1));
				
				
			}
			
			
				
			 
			
		}else{
		
			$params=array();
			$params['status_id']=(int)$v[1];	
			$stat=$_stat->GetItembyid($params['status_id']);
			
			$comment= ('Установлен статус '.$stat['name']);
			
			
			$_ti->Edit($tender_id, $params,true,$result);
			
			
			
			$log->PutEntry($result['id'],'смена статуса лида',NULL,950,NULL,$comment,$tender_id);
			
			$len_params=array();
			$len_params['sched_id']=$tender_id;
			$len_params['txt']='Автоматический комментарий: сотрудником '.$result['name_s'].' '.$comment;
			$len_params['user_id']=0;
			$len_params['pdate']=time();
			
			$len_code= $_th->Add($len_params);
			 
			
		 
		}
	}

}
 
 
 
elseif(isset($_GET['action'])&&($_GET['action']=="calc_new_kind4")){
	$_rem=new Lead_PopupGroup;
	
	$timeout=60*24; 
	 
	$do_go=true;
 	$kind=4;
	
	 
	
	
	if($do_go){
	
		
		//проверим МАРКЕР
		$sql='select * from lead_marker where kind_id="'.$kind.'" and user_id="'.$result['id'].'" and ptime>="'. mktime(0,0,0,date('n'),date('j'),date('Y')) .'" and ptime<="'. mktime(23,59,59,date('n'),date('j'),date('Y')) .'"';
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
			
			$_mark=new lead_MarkerItem;
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
	$_rem=new Lead_PopupGroup;
	$data=$_rem->ShowKind4($result['id'],$fail_ids, $fail_vals);
	$sm=new SmartyAj;
	
	$sm->assign('session_id', session_id());
	$sm->assign('fail_ids', $fail_ids);
	$sm->assign('fail_vals', $fail_vals);
	
	$sm->assign('items', $data);
	
	$ret=$sm->fetch('lead/lead_head_data4.html');
	
}

elseif(isset($_POST['action'])&&($_POST['action']=="put_work_statuses4")){
	
	$_ti=new Lead_Item;
	$_th=new Lead_HistoryItem;
	$_tf=new Lead_HistoryFileItem;
	$_stat=new DocStatusItem;
	
	$_lci=new Lead_CountsItem;
	$log=new ActionLog;
	
	$data=$_POST['data'];
	if(is_array($data))	foreach($data as $k=>$val){
		$v=explode("/", $val);
		
		$tender_id=abs((int)$v[0]);
		
		 
		
			$params=array();
			
			
			
			if($v[1]==34){
				 
				 $params['status_id']=37;
			
			
				$stat=$_stat->GetItembyid($params['status_id']);
				
				$comment= ('Установлен статус '.$stat['name']);
				 
				$params['fail_reason_id']=abs((int)$v[2]);
				$params['fail_reason']=SecStr(iconv('utf-8','windows-1251',$v[3]));
				
				$_fi=new Lead_FailItem;
				$fi=$_fi->GetItemById($params['fail_reason_id']);
				
				$comment.=', причина '.SecStr($fi['name']).' '.$params['fail_reason'];
					
			}elseif($v[1]==25){
				
				$params['status_id']=(int)$v[1];
			
			
				$stat=$_stat->GetItembyid($params['status_id']);
				
				$comment= ('Установлен статус '.$stat['name']);
				
				$params['pdate_finish']=date('Y-m-d', datefromdmy($v[4]));
				
				$comment.=', новая прогнозируемая дата заключения контракта: '.$v[4];
			}
			
				
			
			
			$_ti->Edit($tender_id, $params,true,$result);
			
			
			
			$log->PutEntry($result['id'],'смена статуса лида',NULL,950,NULL,$comment,$tender_id);
				
			 
			$len_params=array();
			$len_params['sched_id']=$tender_id;
			$len_params['txt']='Автоматический комментарий: сотрудником '.$result['name_s'].' '.$comment;
			$len_params['user_id']=0;
			$len_params['pdate']=time();
			
			$len_code= $_th->Add($len_params);
			
		 
		 
	}

}



elseif(isset($_GET['action'])&&($_GET['action']=="calc_new_kind5")){
	$_rem=new Lead_PopupGroup;
	
	
	 
	$do_go=true;
 	$kind=abs((int)$_GET['kind']);
	
	
	if($kind==5){
		$timeout=30*24*60; 
	}elseif($kind==6){
		$timeout=14*24*60; 
	}elseif($kind==7){
		$timeout=7*24*60; 
	}
	 
	
	
	if($do_go){
	
		
		//проверим МАРКЕР
		$sql='select * from lead_marker where kind_id="'.$kind.'" and user_id="'.$result['id'].'" and ptime>="'.(time()-$timeout*60).'"';
		
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
			$ret=$_rem->CalcKind5($result['id'],$kind);
			
			$_mark=new lead_MarkerItem;
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
	$_rem=new Lead_PopupGroup;
	
	$kind=abs((int)$_GET['kind']);
	
	
	$data=$_rem->ShowKind5($result['id'],$kind);
	$sm=new SmartyAj;
	
	$sm->assign('session_id', session_id());
	$sm->assign('data_kind', $kind);
 
	$sm->assign('items', $data);
	
	$ret=$sm->fetch('lead/lead_head_data5.html');
	
}


elseif(isset($_POST['action'])&&($_POST['action']=="put_work_statuses5")){
	
	$_ti=new Lead_Item;
	$_th=new Lead_HistoryItem;
	$_tf=new Lead_HistoryFileItem;
	$_stat=new DocStatusItem;
	
	$_lci=new Lead_CountsItem;
	$log=new ActionLog;
	
	$kind=abs((int)$_POST['kind']);
	
	$data=$_POST['data'];
	if(is_array($data))	foreach($data as $k=>$val){
		$v=explode("/", $val);
		
		$tender_id=abs((int)$v[0]);
		
		$ti=$_ti->GetItemById($tender_id);
		
		if($v[1]==0) {
			
			 
			$lci=$_lci->GetItemByFields(array('user_id'=>$result['id'], 'lead_id'=>$tender_id, 'kind_id'=>2));
			
			if($lci===false) $num_refuse=1;
			else $num_refuse=(int)$lci['value'] + 1;
			
			$log->PutEntry($result['id'],'отказ сотрудника дать комментарий по лиду, по которому не было комментариев более 7 дней, в '.$num_refuse.' раз',NULL,950,NULL,'отказ сотрудника дать комментарий по лиду, по которому не было комментариев более 7 дней, лид '.$ti['code'],$tender_id);
			
			if(($lci!==false)&&($lci['value']>=2)){
				$_lci->Edit($lci['id'], array('value'=>0));
				
				
				//найти прошлые отказы
				$_tender_an=new Tender_LogAn;
				$refuse_comment=$_tender_an->GetRefuses($result['id'], $tender_id, 950, 'отказ сотрудника дать комментарий по лиду, по которому не было комментариев более 7 дней, три раза подряд', 'отказ сотрудника дать комментарий по лиду, по которому не было комментариев более 7 дней, в 1 раз', 'отказ сотрудника дать комментарий по лиду, по которому не было комментариев более 7 дней, в 2 раз', 'отказ сотрудника дать комментарий по лиду, по которому не было комментариев более 7 дней, в 3 раз');
				
				
				
				
				//внесем коммент
				$len_params=array();
				$len_params['sched_id']=$tender_id;
				$len_params['txt']=SecStr('<div>Автоматический комментарий: Сотрудник '.$result['name_s'].' отказался три раза подряд дать комментарий по лиду, по которому не было комментариев более 7 дней.</div>'.$refuse_comment);
				$len_params['user_id']=0;
				$len_params['pdate']=time();
				
				$len_code= $_th->Add($len_params);
				
				$log->PutEntry($result['id'],'отказ сотрудника дать комментарий по лиду, по которому не было комментариев более 7 дней, три раза подряд',NULL,950,NULL,'отказ сотрудника дать комментарий по лиду, по которому не было комментариев более 7 дней, три раза подряд',$tender_id);
				
				
			}else{
				if($lci!==false) $_lci->Edit($lci['id'], array('value'=>((int)$lci['value']+1)));
				else $_lci->Add(array('user_id'=>$result['id'], 'lead_id'=>$tender_id, 'kind_id'=>2));
				
				
			}
			
			
				
			 
			
		}else{
		
			 
			
			
			//$log->PutEntry($result['id'],'смена статуса лида',NULL,950,NULL,$comment,$tender_id);
				
			 //внесем коммент
				$len_params=array();
				$len_params['sched_id']=$tender_id;
				$len_params['txt']=SecStr(iconv('utf-8', 'windows-1251', $v[2]));
				$len_params['user_id']=$result['id'];
				$len_params['pdate']=time();
				
				$len_code= $_th->Add($len_params);
			
		 
		}
	}

}
 
//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>