<?
session_start();
header('Content-type: text/html; charset=windows-1251');

require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/discr_table_objects.php');
require_once('../classes/actionlog.php');

require_once('../classes/pl_positem.php');
require_once('../classes/positem.php');
require_once('../classes/posgroupitem.php');
require_once('../classes/posgroupgroup.php');

require_once('../classes/posdimitem.php');
require_once('../classes/posdimgroup.php');
require_once('../classes/posgroup.php');

require_once('../classes/bdetailsgroup.php');
require_once('../classes/bdetailsitem.php');
require_once('../classes/suppliersgroup.php');
require_once('../classes/supplieritem.php');

require_once('../classes/billitem.php');


require_once('../classes/billpospmformer.php');

require_once('../classes/maxformer.php');

require_once('../classes/isposgroup.php');
require_once('../classes/ispreparegroup.php');
require_once('../classes/wfitem.php');
require_once('../classes/wfgroup.php');
require_once('../classes/user_s_item.php');
require_once('../classes/isnotesitem.php');
require_once('../classes/isnotesgroup.php');

require_once('../classes/iswf_group.php');
require_once('../classes/iswf_item.php');
require_once('../classes/posgroupgroup.php');

$au=new AuthUser();
$result=$au->Auth();
$log=new ActionLog;

if($result===NULL){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();		
}

function FindIndex($value, $array){
	$r=-1;
	if(count($array)>0) foreach($array as $k=>$v){
		if($v==$value){
			$r=$k;
			break;	
		}
	}
	return $r;
}


$ret='';

if(isset($_POST['action'])&&($_POST['action']=="load_positions")){
	//вывод позиций склада-участка для межсклада
	
	$id=abs((int)$_POST['id']);
	$storage_id=abs((int)$_POST['storage_id']);
	$sector_id=abs((int)$_POST['sector_id']);
	$selected_positions=$_POST['selected_positions'];
	$selected_quantities=$_POST['selected_quantities'];
	
	
	
	
	//фильтрация по заданным параметрам
	
	$dec=new DBDecorator;
	
	$name=SecStr(iconv("utf-8","windows-1251",$_POST['qry']));
	$group_id=abs((int)$_POST['group_id']);
	
	if(strlen($name)>0) $dec->AddEntry(new SqlEntry('ap.name',$name, SqlEntry::LIKE));
	//if($group_id>0) $dec->AddEntry(new SqlEntry('cat.group_id',$group_id, SqlEntry::E));
	if($group_id>0) {
		$dec->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		$dec->AddEntry(new SqlEntry('cat.group_id',$group_id, SqlEntry::E));
		
		//найти подподгруппы
		$_pgg=new PosGroupGroup;
		$arr=$_pgg->GetItemsByIdArr($group_id);
		$arg=array();
		foreach($arr as $k=>$v){
			if(!in_array($v['id'],$arg)) $arg[]=$v['id'];
			$arr2=$_pgg->GetItemsByIdArr($v['id']);
			foreach($arr2 as $kk=>$vv){
				if(!in_array($vv['id'],$arg))  $arg[]=$vv['id'];
			}
		}
		
		if(count($arg)>0){
			$dec->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			$dec->AddEntry(new SqlEntry('cat.group_id', NULL, SqlEntry::IN_VALUES, NULL,$arg));	
		}
		
		$dec->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
	}
	
	if(abs((int)$_POST['dimension_id'])>0) $dec->AddEntry(new SqlEntry('cat.dimension_id',abs((int)$_POST['dimension_id']), SqlEntry::E));
	
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['length'])))>0) $dec->AddEntry(new SqlEntry('cat.length',SecStr(iconv("utf-8","windows-1251",$_POST['length'])), SqlEntry::E));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['width'])))>0) $dec->AddEntry(new SqlEntry('cat.width',SecStr(iconv("utf-8","windows-1251",$_POST['width'])), SqlEntry::E));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['height'])))>0) $dec->AddEntry(new SqlEntry('cat.height',SecStr(iconv("utf-8","windows-1251",$_POST['height'])), SqlEntry::E));
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['diametr'])))>0) $dec->AddEntry(new SqlEntry('cat.diametr',SecStr(iconv("utf-8","windows-1251",$_POST['diametr'])), SqlEntry::E));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['weight'])))>0) $dec->AddEntry(new SqlEntry('cat.weight',SecStr(iconv("utf-8","windows-1251",$_POST['weight'])), SqlEntry::E));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['volume'])))>0) $dec->AddEntry(new SqlEntry('cat.volume',SecStr(iconv("utf-8","windows-1251",$_POST['volume'])), SqlEntry::E));
	
	
	$sm=new SmartyAj;
	
	$_is=new WfItem;
	$is=$_is->GetItemById($id);
	//print_r($is);
	if(($is===false)||($is['is_confirmed']==0)){
		 $sm->assign('has_filter',true);
		 $has_filter=true;
	}else{
		 $sm->assign('has_filter',false);
		 $has_filter=false;
	}
	
	
	
	
	
	
	
	$_posgroupgroup=new PosGroupGroup;
	
	$_kpg=new IsPrepareGroup;
	
	$_mf=new MaxFormer;
	$alls=$_kpg->GetItemsByStorSec($storage_id, $sector_id,$result['org_id'],$id,$dec);
	$alls1=array();
	foreach($alls as $k=>$v){
		//подставим значения, если они заданы ранее
		$index=FindIndex($v['position_id'],$selected_positions);
		//$ret.=$v['position_id'];
		
		
		if($index>-1){
			$v['quantity']=$selected_quantities[$index];
			
		}else{
			$v['quantity']=0;
			
		}
		
		
		$v['max_fact_quantity']=$v['quantity'];
		
		
		
		if(!$has_filter&&($v['quantity']==0)){
			
		}else{
			$alls1[]=$v;
		}
		
	}
	
	
	$sm->assign('pospos',$alls1);
	
	$sm->assign('is_confirmed',abs((int)$_POST['is_confirmed']));
	
	$sm->assign('pos_change_low_mode',abs((int)$_POST['change_low_mode']));
	
	
	
	
	
	
	//тов группы
	$posgroupgroup=$_posgroupgroup->GetItemsArr(); // >GetItemsTreeArr();
	$st_ids=array(); $st_names=array();
	$st_ids[]=0; $st_names[]='-выберите-';
	foreach($posgroupgroup as $k=>$v){
		$st_ids[]=$v['id'];
		$st_names[]=$v['name'];
			
	}
	$sm->assign('tov_group_ids', $st_ids);
	$sm->assign('tov_group_names', $st_names);
	
	$as=new mysqlSet('select * from catalog_dimension order by name asc');
	$rs=$as->GetResult();
	$rc=$as->GetResultNumRows();
	$acts=array();
	$acts[]=array('name'=>'');
	for($i=0; $i<$rc; $i++){
		$f=mysqli_fetch_array($rs);
		foreach($f as $k=>$v) $f[$k]=stripslashes($v);
		
		$acts[]=$f;
	}
	$sm->assign('dim',$acts);
	
	
	
	
	$ret.=$sm->fetch("wf/positions_edit_set.html");
}elseif(isset($_POST['action'])&&($_POST['action']=="transfer_positions")){
	//перенос выбранных позиций к.в. на страницу счет
	
	$id=abs((int)$_POST['id']);	
	$storage_id=abs((int)$_POST['storage_id']);
	$sector_id=abs((int)$_POST['sector_id']);
	
	//ввести данные со страницы
	$new_selected_positions=$_POST['new_selected_positions'];
	$new_selected_quantities=$_POST['new_selected_quantities'];
	$new_selected_fact_quantities=$_POST['new_selected_fact_quantities'];
	
	
	$selected_positions=$_POST['selected_positions'];
	$selected_quantities=$_POST['selected_quantities'];

	$selected_fact_quantities=$_POST['selected_fact_quantities'];
	
	$alls=array();
	$_itm=new WfItem;
	$item=$_itm->GetItemById($id);
	
	$_position=new PlPosItem;
	$_dim=new PosDimItem;
	$_iwg=new IswfGroup;
	
	$_to_put_positions=array();
	
	//написать обработчик, который бы сверял изменения
	//print_r($new_selected_fact_quantities);
	
	//перебрать значения со страницы
	//если среди них есть новое значение - сверить количества, внести новое
	//если его количество равно нулю - убираем
	//если среди них нет нового значения - добавить его
	
	
	foreach($new_selected_quantities as $k=>$v){
		$f=array();	
		//$do_add=true;
		
		if($v<=0) continue;
		$position=$_position->GetItemByFields(array('position_id'=>$new_selected_positions[$k]));
		if($position===false) continue;
		
		
		//проверка наличия нового значения
		$is_in=false; $in_index=-1;
		//$is_in - это проверка, нашлась ли позиция со страницы среди позиций диалога
		foreach($selected_positions as $kk=>$vv){
			if($vv==$new_selected_positions[$k]){
				$is_in=true;
				$in_index=$kk;
				break;
			}
		}
		//нужно где-то сделать проверку, нашлась ли позиция из диалога среди позиций со страницы, если нет - то добавить ее
		
		if($is_in){
			 $f['quantity']=$selected_quantities[$in_index];
			 if($selected_quantities[$in_index]==0) continue; //пропускаем нулевые количества
		}else $f['quantity']=$v;
		
		$f['id']=$position['position_id'];
		$f['position_id']=$position['position_id'];
		$f['pl_position_id']=$position['id'];
		
		
		$f['position_name']=$position['name'];
		$f['dimension_id']=$position['dimension_id'];
		
		$dim=$_dim->GetItemById($f['dimension_id']);
		$f['dim_name']=$dim['name'];
		
		//обработка фактически перемещаемого количества
		if($is_in){
			$fact_quantity=$selected_fact_quantities[$in_index];
		}else{
			$fact_quantity=$new_selected_fact_quantities[$k];	
		}
		$old_fact_quantity=$_iwg->FactKol($id,$f['id']);
		
		$delta_quantity=$fact_quantity-$old_fact_quantity;
		
		if($delta_quantity>0){
			$_p=array();
			$_p['position_id']=$f['position_id'];
			$_p['pl_position_id']=$f['pl_position_id'];
			$_p['name']=$f['position_name'];
			
			$_p['dimension']=$f['dim_name'];
			$_p['quantity']=$delta_quantity;
			
			$_to_put_positions[]=$_p;
			//echo 'zzzzzzzzzzzzzzzzzzzzzzz';
		}
		
		
		
		$f['fact_quantity']=$_iwg->FactKol($id,$f['id'])+$delta_quantity;
		
				
	
		$alls[]=$f;
	}
	
	
	//проверка, нашлась ли позиция из диалога среди позиций со страницы, если нет - то добавить ее
		
	foreach($selected_quantities as $k=>$v){
		
		$is_in=false; $in_index=-1;
		//$is_in - это проверка, нашлась ли позиция из диалога среди позиций со страницы
		foreach($new_selected_positions as $kk=>$vv){
			if($vv==$selected_positions[$k]){
				$is_in=true;
				$in_index=$kk;
				break;
			}
		}
		
		//есть такая, пропускаем
		if($is_in) continue;
		
		$f=array();	
		//$do_add=true;
		
		if($v<=0) continue;
		$position=$_position->GetItemByFields(array('position_id'=>$selected_positions[$k]));
		if($position===false) continue;
		
		$f['quantity']=$v;
		
		$f['id']=$position['position_id'];
		$f['position_id']=$position['position_id'];
		$f['pl_position_id']=$position['id'];
		
		
		
		$f['position_name']=$position['name'];
		$f['dimension_id']=$position['dimension_id'];
		
		$dim=$_dim->GetItemById($f['dimension_id']);
		$f['dim_name']=$dim['name'];
		
		//обработка фактически перемещаемого количества
		$fact_quantity=$selected_fact_quantities[$k];
		$old_fact_quantity=$_iwg->FactKol($id,$f['id']);
		
		$delta_quantity=$fact_quantity-$old_fact_quantity;
		
		if($delta_quantity>0){
			$_p=array();
			$_p['position_id']=$f['position_id'];
			$_p['pl_position_id']=$f['pl_position_id'];
			$_p['name']=$f['position_name'];
			
			$_p['dimension']=$f['dim_name'];
			$_p['quantity']=$delta_quantity;
			
			$_to_put_positions[]=$_p;
			//echo 'zzzzzzzzzzzzzzzzzzzzzzz';
		}
		
		
		
		$f['fact_quantity']=$_iwg->FactKol($id,$f['id'])+$delta_quantity;
		
				
	
		$alls[]=$f;
	}
	
	
	
	
	
	$sm=new SmartyAj;
	$sm->assign('pospos',$alls);
	
	$sm->assign('can_modify', ($item===false)||($item['is_confirmed']==0) );
	$sm->assign('change_low_mode', abs((int)$_POST['change_low_mode']));
	
	$sm->assign('can_add_positions',$au->user_rights->CheckAccess('w',256)); 
	$sm->assign('can_del_positions',$au->user_rights->CheckAccess('w',258)); 
		
	
	
	$ret=$sm->fetch("wf/positions_on_page_set.html");
}
//utv- razutv
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_confirm")){
	$id=abs((int)$_POST['id']);
	$_ti=new WfItem;
	
	$_si=new UserSItem;
	
	$trust=$_ti->getitembyid($id);
	
	
	if($trust['is_confirmed_fill_wf']==1){
		//есть права: либо сам утв.+есть права, либо есть искл. права:
		if(($au->user_rights->CheckAccess('w',263))||$au->user_rights->CheckAccess('w',109)){
			if(($trust['status_id']==2)){
				$_ti->Edit($id,array('is_confirmed_fill_wf'=>0, 'user_confirm_fill_wf_id'=>$result['id'], 'confirm_fill_wf_pdate'=>time()),true);
				
				$log->PutEntry($result['id'],'снял утверждение заполнения распоряжения на списание',NULL,263, NULL, NULL,$id);
				
				
					
			}
		}else{
			//нет прав	
		}
		
	}else{
		//есть права
		if($_ti->DocCanConfirmFillWf($id,$reason)){
		
		  if($au->user_rights->CheckAccess('w',108)||$au->user_rights->CheckAccess('w',109)){
			  if(($trust['status_id']==1)){
				  $_ti->Edit($id,array('is_confirmed_fill_wf'=>1, 'user_confirm_fill_wf_id'=>$result['id'], 'confirm_fill_wf_pdate'=>time()),true);
				  
				  $log->PutEntry($result['id'],'утвердил заполнение распоряжения на списание',NULL,108, NULL, NULL,$id);	
					  
			  }
		  }else{
			  //do nothing
		  }
		}
	}
	
	
	
	
	
	$shorter=abs((int)$_POST['shorter']);
	/*if($shorter==0) $template='acc/all_accs_list.html';
	else*/
	 $template='wf/wf_list.html';
	
	
	$acg=new WfGroup;
	
	$dec=new  DBDecorator;
	
	$dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	
	$ret=$acg->ShowPos('wf/wf_list.html',$dec,0,1000,
	 $au->user_rights->CheckAccess('w',106)||$au->user_rights->CheckAccess('w',290), 
	 $au->user_rights->CheckAccess('w',107),
	 $au->user_rights->CheckAccess('w',108),
	 $au->user_rights->CheckAccess('w',109),
	 false,true,$au->user_rights->CheckAccess('w',138),NULL, 
	$au->user_rights->CheckAccess('w',263), 
	$au->user_rights->CheckAccess('w',264),
	$au->user_rights->CheckAccess('w',265)); 
		
}
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_confirm_wf")){
	$id=abs((int)$_POST['id']);
	$_ti=new WfItem;
	
	$_si=new UserSItem;
	
	$trust=$_ti->getitembyid($id);
	
	if($trust['is_confirmed']==1){
		//есть права: либо сам утв.+есть права, либо есть искл. права:
		if(($au->user_rights->CheckAccess('w',265))||$au->user_rights->CheckAccess('w',109)){
			if(($trust['status_id']==1)||($trust['status_id']==2)||($trust['status_id']==17)){
				$_ti->Edit($id,array('is_confirmed'=>0, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true);
				
				$log->PutEntry($result['id'],'снял утверждение списания по распоряжению на списание',NULL,265, NULL, NULL,$id);
				
				
					
			}
		}else{
			//нет прав	
		}
		
	}else{
		//есть права
		if($_ti->DocCanConfirm($id,$reason)){
		
		  if($au->user_rights->CheckAccess('w',264)||$au->user_rights->CheckAccess('w',109)){
			  if(($trust['status_id']==1)||($trust['status_id']==2)||($trust['status_id']==17)){
				  $_ti->Edit($id,array('is_confirmed'=>1, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true);
				  
				  $log->PutEntry($result['id'],'утвердил списание по распоряжению на списание',NULL,264, NULL, NULL,$id);	
					  
			  }
		  }else{
			  //do nothing
		  }
		}
	}
	
	
	
	
	
	$shorter=abs((int)$_POST['shorter']);
	/*if($shorter==0) $template='acc/all_accs_list.html';
	else*/
	 $template='wf/wf_list.html';
	
	
	$acg=new WfGroup;
	
	$dec=new  DBDecorator;
	
	$dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	
	$ret=$acg->ShowPos('wf/wf_list.html',$dec,0,1000,
	 $au->user_rights->CheckAccess('w',106)||$au->user_rights->CheckAccess('w',290), 
	 $au->user_rights->CheckAccess('w',107),
	 $au->user_rights->CheckAccess('w',108),
	 $au->user_rights->CheckAccess('w',109),
	 false,true,$au->user_rights->CheckAccess('w',139),NULL, 
	$au->user_rights->CheckAccess('w',263), 
	$au->user_rights->CheckAccess('w',264),
	$au->user_rights->CheckAccess('w',265)); 
		
}
//udalenie-annulirovabie
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_annul")){
	$id=abs((int)$_POST['id']);
	
	$shorter=abs((int)$_POST['shorter']);
	$template='wf/wf_list.html';
	
	$note=SecStr(iconv("utf-8","windows-1251",$_POST['note']));
	
	if(isset($_POST['from_card'])&&($_POST['from_card']==1)) $from_card=1;
	else $from_card=0;
	
	$_ti=new WfItem;
	
	$_si=new UserSItem;
	
	$trust=$_ti->getitembyid($id);
	
	$log=new ActionLog;
	$_stat=new DocStatusItem;
	
	
	if(($trust['status_id']==1)&&($trust['is_confirmed_fill_wf']==0)&&($trust['is_j']==0)){
		//удаление	
		if($au->user_rights->CheckAccess('w',107)){
			$_ti->Edit($id,array('status_id'=>3, 'confirm_fill_wf_pdate'=>time(), 'user_confirm_fill_wf_id'=>$result['id']));	
			
			$stat=$_stat->GetItemById(3);
			$log->PutEntry($result['id'],'аннулирование распоряжения на списание',NULL,107,NULL,'распоряжение на списание № '.$trust['id'].': установлен статус '.$stat['name'],$trust['id']);	
			
			//внести примечание
			$_ni=new IsNotesItem;
			$_ni->Add(array(
				'user_id'=>$id,
				'posted_user_id'=>$result['id'],
				'note'=>'Автоматическое примечание: документ был аннулирован пользователем '.SecStr($result['name_s']).' ('.$result['login'].'), причина: '.$note,
				'is_auto'=>1,
				'pdate'=>time()
					));	
			
		}
	}elseif(($trust['status_id']==3)&&($trust['is_j']==0)){
		//разудаление
		if($au->user_rights->CheckAccess('w',138)){
			$_ti->Edit($id,array('status_id'=>1, 'confirm_fill_wf_pdate'=>time(), 'user_confirm_fill_wf_id'=>$result['id']));	
			
			$stat=$_stat->GetItemById(1);
			$log->PutEntry($result['id'],'восстановление распоряжения на списание',NULL,138,NULL,'распоряжение на списание № '.$trust['id'].': установлен статус '.$stat['name'],$trust['id']);	
			//внести примечание
			$_ni=new IsNotesItem;
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
		$acg=new WfGroup;
	
	$dec=new  DBDecorator;
	
	$dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	
	$ret=$acg->ShowPos('wf/wf_list.html',$dec,0,1000,
	 $au->user_rights->CheckAccess('w',106)||$au->user_rights->CheckAccess('w',290), 
	 $au->user_rights->CheckAccess('w',107),
	 $au->user_rights->CheckAccess('w',108),
	 $au->user_rights->CheckAccess('w',109),
	 false,true,$au->user_rights->CheckAccess('w',138),NULL, 
	$au->user_rights->CheckAccess('w',263), 
	$au->user_rights->CheckAccess('w',264),
	$au->user_rights->CheckAccess('w',265));
	}else{
		$editing_user=$_ti->getitembyid($id);
		$sm=new SmartyAj;
		
		
		
		//блок аннулирования
		
		$editing_user['can_annul']=$_ti->DocCanAnnul($editing_user['id'],$reason)&&$au->user_rights->CheckAccess('w',102);
		if(!$au->user_rights->CheckAccess('w',102)) $reason='недостаточно прав для данной операции';
		$editing_user['can_annul_reason']=$reason;
		
		//$editing_user['binded_to_annul']=$_ti->GetBindedDocumentsToAnnul($editing_user['id']);
		
		$sm->assign('bill',$editing_user);
		$ret=$sm->fetch('is/toggle_annul_card.html');	
	}

}
//РАБОТА С ПРИМЕЧАНИЯМИ
elseif(isset($_POST['action'])&&($_POST['action']=="redraw_notes")){
	$sm=new SmartyAj;
	
	$user_id=abs((int)$_POST['user_id']);
	
	$rg=new IsNotesGroup;
	
	$sm->assign('items',$rg->GetItemsByIdArr($user_id,0,0,false,false,false,$result['id']));
	$sm->assign('word','notes');
	$sm->assign('user_id',$user_id);
	$sm->assign('named','Примечания');
	
	$sm->assign('can_edit', $au->user_rights->CheckAccess('w',259));
	
	
	$ret=$sm->fetch('wf/d_notes.html');
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="add_notes")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',259)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	
	
	$ri=new IsNotesItem;
	$ri->Add(array(
				'note'=>SecStr(iconv("utf-8","windows-1251",$_POST['note'])),
				'pdate'=>time(),
				'user_id'=>$user_id,
				'posted_user_id'=>$result['id']
			));
	
	$log->PutEntry($result['id'],'добавил примечания распоряжению на списание', NULL,259, NULL,SecStr(iconv("utf-8","windows-1251",$_POST['note']),9),$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="edit_notes")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',259)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	$id=abs((int)$_POST['id']);
	
	
	$ri=new IsNotesItem;
	$ri->Edit($id,
				array(
				'note'=>SecStr(iconv("utf-8","windows-1251",$_POST['note'])),
				'pdate'=>time(),
				'posted_user_id'=>$result['id']/*,
				'user_id'=>$user_id*/
			));
	
	$log->PutEntry($result['id'],'редактировал примечания по распоряжению на списание', NULL,259,NULL, SecStr(iconv("utf-8","windows-1251",$_POST['note']),9),$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="delete_notes")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',259)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	$id=abs((int)$_POST['id']);
	
	
	$ri=new IsNotesItem;
	$ri->Del($id);
	
	$log->PutEntry($result['id'],'удалил примечания по распоряжению на списание', NULL,259,NULL,NULL,$user_id);
	
}elseif(isset($_POST['action'])&&(($_POST['action']=="redraw_is_confirmed_confirmer")||($_POST['action']=="redraw_is_confirmed_wf_confirmer"))){
	$state=abs((int)$_POST['state']);
	if($state==0){
		$ret='';	
	}elseif($state==1){
		$ret=$result['position_s'].' '.$result['name_s'].' '.' '.$result['login'].' '.date("d.m.Y H:i:s",time());	
	}
	

}
//РАБОТА С РАСПоряжениями по списанию
elseif(isset($_POST['action'])&&($_POST['action']=="redraw_rasp_wf")){
	$sm=new SmartyAj;
	
	$user_id=abs((int)$_POST['user_id']);
	
	$rg=new IswfGroup;
	
	$sm->assign('items',$rg->GetItemsByIdArr($user_id));
	$sm->assign('word','rasp_wf');
	$sm->assign('user_id',$user_id);
	$sm->assign('named','распоряжения по списанию');
	
	$sm->assign('can_edit', $au->user_rights->CheckAccess('w',101));
	
	
	$ret=$sm->fetch('is/rasp_wf.html');
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="delete_rasp_wf")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',101)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	$id=abs((int)$_POST['id']);
	
	
	$ri=new IswfItem;
	//$ri->Del($id);
	
	//$log->PutEntry($result['id'],'удалил распоряжение по списанию', NULL,101,NULL,NULL,$user_id);
	
}
//обновление позиций при удалении списания
elseif(isset($_POST['action'])&&($_POST['action']=="redraw_positions")){
	$id=abs((int)$_POST['id']);
	$change_low_mode=abs((int)$_POST['change_low_mode']);
	$is_confirmed=abs((int)$_POST['is_confirmed']);
	$_pos=new IsPosGroup;
	$pos=$_pos->GetItemsByIdArr($id);
	
	$sm=new SmartyAj;
	
	$sm->assign('positions',$pos);
	$sm->assign('has_positions',true);
	
	$sm->assign('change_low_mode',$change_low_mode);
	$sm->assign('is_confirmed',$is_confirmed);
	$ret=$sm->fetch("is/position_actions.html");
		
}
elseif(isset($_POST['action'])&&($_POST['action']=="check_komplekt")){
	$id=abs((int)$_POST['id']);	
	
	$_is=new IsItem;
	
	//нужно проверить, есть ли связанный счет
	//если он есть, то заявку не проверяем
	$set1=new mysqlset('select count(*) from bill where org_id="'.$result['org_id'].'" and interstore_id="'.$id.'"');
					
					
	$rs1=$set1->getResult();
	$f1=mysqli_fetch_array($rs1);
	if((int)$f1[0]>0){
		$ret=1;
	}else{
	  $test=$_is->CheckKomplekt($id,$result['org_id'],$komplekt_ved_id);
	  
	  if($test) $ret=1;
	  else $ret=0;
	}
}
elseif(isset($_POST['action'])&&($_POST['action']=="check_unconfirm")){
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new WfItem;
		
		
		if(!$_ki->DocCanUnconfirm($id,$rss)) $ret=$rss;
		else $ret=0;
		
		
		//если ноль - то все хорошо
	
}elseif(isset($_POST['action'])&&($_POST['action']=="check_confirm")){
	
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new WfItem;
		
		
		if(!$_ki->DocCanConfirm($id,$rss)) $ret=$rss;
		else $ret=0;
		
		
		//если ноль - то все хорошо
	
}elseif(isset($_POST['action'])&&($_POST['action']=="check_unconfirm_fill_wf")){
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new WfItem;
		
		
		if(!$_ki->DocCanUnConfirmFillWf($id,$rss)) $ret=$rss;
		else $ret=0;
		
		
		//если ноль - то все хорошо
	
}elseif(isset($_POST['action'])&&($_POST['action']=="check_confirm_fill_wf")){
	
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new WfItem;
		
		
		if(!$_ki->DocCanConfirmFillWf($id,$rss)) $ret=$rss;
		else $ret=0;
		
		
		//если ноль - то все хорошо
	
}

//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>