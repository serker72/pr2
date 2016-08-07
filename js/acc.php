<?
session_start();
header('Content-type: text/html; charset=windows-1251');

require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/discr_table_objects.php');
require_once('../classes/actionlog.php');

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

require_once('../classes/billposgroup.php');

require_once('../classes/maxformer.php');


require_once('../classes/sh_i_posgroup.php');
require_once('../classes/sh_i_notesitem.php');
require_once('../classes/sh_i_notesgroup.php');

require_once('../classes/acc_notesgroup.php');
require_once('../classes/acc_notesitem.php');

require_once('../classes/acc_posgroup.php');
require_once('../classes/acc_notesitem.php');

require_once('../classes/acc_item.php');
require_once('../classes/acc_group.php');
require_once('../classes/user_s_item.php');
require_once('../classes/accreports.php');

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
	//вывод позиций исх. счета для распоряжения
	
	$except_id=abs((int)$_POST['acc_id']);
	
	$bill_id=abs((int)$_POST['bill_id']);
	
	$sh_i_id=abs((int)$_POST['sh_i_id']);
	
	
	$complex_positions=$_POST['complex_positions'];
	
	
	$storage_id=abs((int)$_POST['storage_id']);
	$sector_id=abs((int)$_POST['sector_id']);
	$komplekt_ved_id=abs((int)$_POST['komplekt_ved_id']);
	
	//GetItemsByIdArr($id,$current_id=0)
	$_kpg=new ShIPosGroup;
	$_mf=new MaxFormer;
	
	
	
	$alls=$_kpg->GetItemsByIdArr($sh_i_id);
	
	
	$arr=array();
	$joined_positions=array();
	foreach($complex_positions as $kk=>$vv){
		$valarr=explode(';',$vv);
		
		$joined_positions[]=array('position_id'=>$valarr[0],'komplekt_ved_id'=>$valarr[7]);	
	}
	
	foreach($alls as $k=>$v){
		
		//откуда взять sector_id???? iz komplet_ved
		
		if(!in_array(array('position_id'=>$v['position_id'],'komplekt_ved_id'=>$v['komplekt_ved_id']),$joined_positions)){
			$joined_positions[]=array('position_id'=>$v['position_id'],'komplekt_ved_id'=>$v['komplekt_ved_id'] );
		}
		
		//$storage_id; d
	}
	
	//print_r($joined_positions);
	
	foreach($joined_positions as $kk=>$vv){
		//foreach($alls as $k=>$v){
		  
		 $v=array();
		 
		  
		  //print_r($v);
		 
		  $in_alls=false;
		  //подгрузка названия и прочих параметров из списка позиций заявки
		  //уч-к, склад, заявка - могут быть изменены потом
		  foreach($alls as $ck=>$cv){
		  		//echo $cv['position_id'].' vs '.$vv['position_id'].'<br />';
				//echo $cv['komplekt_ved_id'].'va '.$vv['komplekt_ved_id'].'<br />';
				if(
				($cv['position_id']==$vv['position_id'])
				&&
				($cv['komplekt_ved_id']==$vv['komplekt_ved_id'])				
				
				){
					$v=$cv;
					$in_alls=true;
					//echo 'est: '.$v['position_name'].' '.$v['komplekt_ved_id'].'<br />';	
					break;
				}
		  
		  }
		  
		
		   //подставим значения, если они заданы ранее
		 
		  //ищем перебором массива  $complex_positions
		  $index=-1;
		  foreach($complex_positions as $ck=>$ccv){
		  	$cv=explode(';',$ccv);
			
			if(
				($cv[0]==$vv['position_id'])&&
				
				($cv[7]==$vv['komplekt_ved_id'])	
				){
					$index=$ck;
					//echo 'nashli';
					break;	
				}
		  	
		  }
	
		
		if($index>-1){
			  //echo 'nn';
			  $valarr=explode(';',$complex_positions[$index]);
			  
				$v['quantity']=$valarr[1];
			
			  $v['price']=$valarr[3];
			  $v['rub_or_percent']=$valarr[4];
			  $v['plus_or_minus']=$valarr[5];
			  $v['value']=$valarr[6];
			  $v['has_pm']=$valarr[2];
			
			
			if($v['has_pm']){
				$slag=0;
				if($v['rub_or_percent']==0){
					$slag=$v['value'];
				}else{
					$slag=$v['price']*$v['value']/100.0;
				}
				
				if($v['plus_or_minus']==1) $slag=-1.0*$slag;
				$v['price_pm']=$v['price']+$slag;
			}else $v['price_pm']=$v['price'];
			
			$v['cost']=$v['price']*$v['quantity'];
			$v['total']=$v['price_pm']*$v['quantity'];
		
			
			$v['nds_proc']=NDS;
			$v['nds_summ']=sprintf("%.2f",($v['total']-$v['total']/((100+NDS)/100)));
			
		}else{
			$v['quantity']=0;
			//$v['price']=0;
			//$v['rub_or_percent']=0;
			//$v['plus_or_minus']=0;
			//$v['value']=0;
			
			
			//$v['has_pm']=0;
			
			//$v['price_pm']=0;
			$v['cost']=0;
			$v['total']=0;
			
			
			
		
			$v['nds_proc']=NDS;
			$v['nds_summ']=sprintf("%.2f",($v['total']-$v['total']/((100+NDS)/100)));
			
		}
	
		$v['max_quantity']=$_mf->MaxForAcc($sh_i_id, $v['position_id'], $except_id, $v['komplekt_ved_id']);
		
		
		
		//всего в соответствующей строке счета
		$v['max_bill_quantity']=$_mf->MaxInBill($bill_id,$v['position_id'],$storage_id,$sector_id,$v['komplekt_ved_id']);
		
		
		//всего в соотв. строке заявки
		$v['max_komplekt_quantity']=$_mf->MaxInKomplekt($v['komplekt_ved_id'],$v['position_id']);
		
		
		
		
		$arr[]=$v;
		
	}
	
	
	$sm=new SmartyAj;
	$sm->assign('pospos',$arr);
	
	$_bill=new BillItem;
	$bill=$_bill->GetItemById($bill_id);
	
	if($bill['status_id']==10){
		$sm->assign('cannot_view_pm',!$au->user_rights->CheckAccess('w',365));
	}else $sm->assign('cannot_view_pm',!$au->user_rights->CheckAccess('w',234));
	
	$sm->assign('can_exclude_positions',$au->user_rights->CheckAccess('w',233));
	
	
	$sm->assign('can_add_positions',$au->user_rights->CheckAccess('w',229)||$au->user_rights->CheckAccess('w',235)); 
		$sm->assign('can_del_positions',$au->user_rights->CheckAccess('w',229)||$au->user_rights->CheckAccess('w',235)); 
	
	$sm->assign('pos_change_low_mode',abs((int)$_POST['change_low_mode']));
	$sm->assign('pos_change_high_mode',abs((int)$_POST['change_high_mode']));
	$sm->assign('PPUP',PPUP);
	
	$ret.=$sm->fetch("acc/positions_edit_set.html");
}elseif(isset($_POST['action'])&&($_POST['action']=="transfer_positions")){
	//перенос выбранных позиций к.в. на страницу счет
		
	$bill_id=abs((int)$_POST['bill_id']);
	$sh_i_id=abs((int)$_POST['sh_i_id']);
	
	$complex_positions=$_POST['complex_positions'];
	
	$storage_id=abs((int)$_POST['storage_id']);
	$sector_id=abs((int)$_POST['sector_id']);
	$komplekt_ved_id=abs((int)$_POST['komplekt_ved_id']);
	
	$alls=array();
	
	$_position=new PosItem;
	$_dim=new PosDimItem;
	$_mf=new MaxFormer;
	
	foreach($complex_positions as $k=>$kv){
		
		$f=array();	
		$v=explode(';',$kv);
		
		//$do_add=true;
		if($v[1]<=0) continue;
		$position=$_position->GetItemById($v[0]);
		if($position===false) continue;
		
		$f['quantity']=$v[1];
		$f['id']=$v[0];
		
		$f['position_name']=$position['name'];
		$f['dimension_id']=$position['dimension_id'];
		
		$dim=$_dim->GetItemById($f['dimension_id']);
		$f['dim_name']=$dim['name'];
		
		$f['price']=$v[3];
		
		//+/-
		$f['has_pm']=(int)$v[2];
		$f['rub_or_percent']=(int)$v[4];
		$f['plus_or_minus']=(int)$v[5];
		$f['value']=(float)$v[6];
		
		$f['komplekt_ved_id']=$v[7];
		
		
		if($f['komplekt_ved_id']!=0) $f['komplekt_ved_name']='Заявка № '.$f['komplekt_ved_id'];
		else $f['komplekt_ved_name']='-';
		
		
		//cena +-
		if((int)$v[2]==1){
			$slag=0;
			if($f['rub_or_percent']==0){
				$slag=$f['value'];
			}else{
				$slag=$f['price']*$f['value']/100.0;
			}
			
			if($f['plus_or_minus']==1) $slag=-1.0*$slag;
			$f['price_pm']=$f['price']+$slag;
		}else $f['price_pm']=$f['price'];
		
		//st-t'
		$f['cost']=$f['price']*$f['quantity'];
		
		
		
		$f['in_bill']=$_mf->MaxInBill($bill_id, $f['id'],$storage_id,$sector_id,$v[7]);
				$f['in_rasp']=$_mf->MaxInShI($bill_id, $f['id'],$sh_i_id,$storage_id,$sector_id,$v[7]);
				$f['in_acc']=$_mf->MaxInAcc($bill_id, $f['id'],0,$sh_i_id,$storage_id,$sector_id,$v[7]);
		
		
		
		//vsego
		$f['total']=$f['price_pm']*$f['quantity'];
		
		$f['nds_proc']=NDS;
		$f['nds_summ']=sprintf("%.2f",($f['total']-$f['total']/((100+NDS)/100)));
		
	//	$ret.=$v.' ';
	
		$f['hash']=md5($f['id'].'_'.$f['komplekt_ved_id']);
		
		$alls[]=$f;
	}
	
	$sm=new SmartyAj;
	$sm->assign('pospos',$alls);
	//$sm->assign('cannot_view_pm',!$au->user_rights->CheckAccess('w',243));
	
	$_bill=new BillItem;
	$bill=$_bill->GetItemById($bill_id);
	
	if($bill['status_id']==10){
		$sm->assign('cannot_view_pm',!$au->user_rights->CheckAccess('w',365));
	}else $sm->assign('cannot_view_pm',!$au->user_rights->CheckAccess('w',234));
	
	
	$sm->assign('can_modify',true);
	
	$sm->assign('change_high_mode',abs((int)$_POST['change_high_mode']));
	$sm->assign('change_low_mode',abs((int)$_POST['change_low_mode']));
	$sm->assign('can_add_positions',$au->user_rights->CheckAccess('w',229)||$au->user_rights->CheckAccess('w',235)); 
	$sm->assign('can_del_positions',$au->user_rights->CheckAccess('w',229)||$au->user_rights->CheckAccess('w',235)); 
	
	
	$ret=$sm->fetch("acc/positions_on_page_set.html");
}elseif(isset($_POST['action'])&&(($_POST['action']=="calc_new_total")||($_POST['action']=="calc_new_nds"))){
	//подсчет нового итого
		
	
	
	$alls=array();
	$complex_positions=$_POST['complex_positions'];
	
	//print_r($complex_positions);
	foreach($complex_positions as $k=>$valarr){
		$f=array();	
		
		$v=explode(';',$valarr);
		
		$f['quantity']=$v[1];
		$f['id']=$v[0];
		

		
		$f['price']=$v[3];
		
		//+/-
		$f['has_pm']=$v[2];
		$f['rub_or_percent']=$v[4];
		$f['plus_or_minus']=$v[5];
		$f['value']=$v[6];
		
		
		//cena +-
		if($f['has_pm']==1){
			
			
			$f['price_pm']=$v[10];			
			
		}else $f['price_pm']=$f['price'];
		
		//st-t'
		$f['cost']=$f['price']*$f['quantity'];
		
		
		//vsego
		//$f['total']=$f['price_pm']*$f['quantity'];
		$f['total']=$v[11];
		
		
		$alls[]=$f;
		
		/*echo '<pre>';
		print_r($f);
		echo '</pre>';*/
		
	}
	
	
	$_bpf=new BillPosPMFormer;
	if($_POST['action']=="calc_new_total") $ret=$_bpf->CalcCost($alls);
	
	if($_POST['action']=="calc_new_nds") $ret=$_bpf->CalcNDS($alls);
}
elseif(isset($_POST['action'])&&($_POST['action']=="redraw_is_confirmed_confirmer")){
	$state=abs((int)$_POST['state']);
	if($state==0){
		$ret='';	
	}elseif($state==1){
		$ret=$result['position_s'].' '.$result['name_s'].' '.' '.$result['login'].' '.date("d.m.Y H:i:s",time());	
	}
	
}
//РАБОТА С ПРИМЕЧАНИЯМИ
elseif(isset($_POST['action'])&&($_POST['action']=="redraw_notes")){
	$sm=new SmartyAj;
	
	$user_id=abs((int)$_POST['user_id']);
	
	$_acc=new AccItem;
	
	$rg=new AccNotesGroup;
	
	$sm->assign('items',$rg->GetItemsByIdArr($user_id,0,0,false,$editing_user['is_confirmed']==1, $au->user_rights->CheckAccess('w',342), $au->user_rights->CheckAccess('w',351), $result['id']));
	$sm->assign('word','notes');
	$sm->assign('user_id',$user_id);
	$sm->assign('named','Примечания');
	
	$sm->assign('can_edit', $au->user_rights->CheckAccess('w',236));
	
	
	$ret=$sm->fetch('acc/d_notes.html');
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="add_notes")){
	//dostup
	$user_id=abs((int)$_POST['user_id']);
	$_acc=new AccItem;
	
	if(!$au->user_rights->CheckAccess('w',236)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	
	
	$ri=new AccNotesItem;
	$ri->Add(array(
				'note'=>SecStr(iconv("utf-8","windows-1251",$_POST['note'])),
				'pdate'=>time(),
				'user_id'=>$user_id,
				'posted_user_id'=>$result['id']
			));
	
	$log->PutEntry($result['id'],'добавил примечания реализации товара', NULL,236, NULL,SecStr(iconv("utf-8","windows-1251",$_POST['note']),9),$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="edit_notes")){
	//dostup
	$user_id=abs((int)$_POST['user_id']);
	$_acc=new AccItem;
	
	if(!$au->user_rights->CheckAccess('w',236)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	
	$id=abs((int)$_POST['id']);
	
	
	$ri=new AccNotesItem;
	$ri->Edit($id,
				array(
				'note'=>SecStr(iconv("utf-8","windows-1251",$_POST['note'])),
				'pdate'=>time(),
				'posted_user_id'=>$result['id']/*,
				'user_id'=>$user_id*/
			));
	
	$log->PutEntry($result['id'],'редактировал примечания по реализации товара', NULL,236,NULL, SecStr(iconv("utf-8","windows-1251",$_POST['note']),9),$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="delete_notes")){
	//dostup
	$user_id=abs((int)$_POST['user_id']);
	
	$_acc=new AccItem;
	
	if(!$au->user_rights->CheckAccess('w',236)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	
	$id=abs((int)$_POST['id']);
	
	
	$ri=new AccNotesItem;
	$ri->Del($id);
	
	$log->PutEntry($result['id'],'удалил примечания по реализации товара', NULL,236,NULL,NULL,$user_id);
	
}
//utv- razutv
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_confirm")){
	$id=abs((int)$_POST['id']);
	$_ti=new AccItem;
	
	$_si=new UserSItem;
	
	$trust=$_ti->getitembyid($id);
	if($trust['confirm_pdate']==0) $trust['confirm_pdate']='-';
	else $trust['confirm_pdate']=date("d.m.Y H:i:s",$trust['confirm_pdate']);
	
	
	$si=$_si->getitembyid($trust['user_confirm_id']);
	$trust['confirmed_price_name']=$si['name_s'];
	$trust['confirmed_price_login']=$si['login'];
	
	$bill_id=$trust['bill_id'];
	
	if($trust['is_confirmed']==1){
		if($_ti->DocCanUnConfirm($id,$reason)){
		//есть права:
		$can=true;
		if(!$_ti->ParentBillHasPms($trust['id'], $trust)||($trust['inventory_id']!=0)) $can=$can&&$au->user_rights->CheckAccess('w',241);
		else $can=$can&&$au->user_rights->CheckAccess('w',721);
				
		  if($can){
			  if($trust['status_id']==5){
				  $_ti->Edit($id,array('is_confirmed'=>0, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,$result);
				  
				  $log->PutEntry($result['id'],'снял утверждение реализации товара',NULL,93, NULL, NULL,$bill_id);
				  
				  $log->PutEntry($result['id'],'снял утверждение реализации товара',NULL,219, NULL, NULL,$trust['sh_i_id']);
				  
				  $log->PutEntry($result['id'],'снял утверждение реализации товара',NULL,721, NULL, NULL,$id);
				  
				  
					  
			  }
		  }else{
			  //нет прав	
		  }
		}
	}else{
		
		
		//есть права
		if($_ti->DocCanConfirm($id,$reason)){
		
				
		
		  if($au->user_rights->CheckAccess('w',240)||$au->user_rights->CheckAccess('w',96)){
			 if($trust['status_id']==4){
				  $_ti->Edit($id,array('is_confirmed'=>1, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,$result);
				  
				  $log->PutEntry($result['id'],'утвердил реализации товара',NULL,93, NULL, NULL,$bill_id);	
				  
				   $log->PutEntry($result['id'],'утвердил реализации товара',NULL,219, NULL, NULL,$trust['sh_i_id']);	
				    $log->PutEntry($result['id'],'утвердил реализации товара',NULL,240, NULL, NULL,$id);	
					  
			  }
		  }else{
			  //do nothing
		  }
		}else{
			//echo $reason; die();	
		}
	}
	
	
	
	
	
	$shorter=abs((int)$_POST['shorter']);
	if($shorter==0) $template='acc/all_accs_list.html';
	else $template='acc/acc_list.html';
	
	
	$acg=new AccGroup;
	
	$dec=new  DBDecorator;
	
	$dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	
	$ret=$acg->ShowAllPos($template,$dec, $au->user_rights->CheckAccess('w',235)||$au->user_rights->CheckAccess('w',286), $au->user_rights->CheckAccess('w',242),0,100, $au->user_rights->CheckAccess('w',240),  $au->user_rights->CheckAccess('w',96),false,true, $au->user_rights->CheckAccess('w',243),NULL,$au->user_rights->CheckAccess('w',241));
		
}//udalenie-annulirovabie
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_annul")){
	$id=abs((int)$_POST['id']);
	
	$shorter=abs((int)$_POST['shorter']);
	if($shorter==0) $template='acc/all_accs_list.html';
	else $template='acc/acc_list.html';
	
	$note=SecStr(iconv("utf-8","windows-1251",$_POST['note']));
	
	if(isset($_POST['from_card'])&&($_POST['from_card']==1)) $from_card=1;
	else $from_card=0;
	
	$_ti=new AccItem;
	
	$_si=new UserSItem;
	
	$trust=$_ti->getitembyid($id);
	
	$log=new ActionLog;
	$_stat=new DocStatusItem;
	
	
	if(($trust['status_id']==4)&&($trust['is_confirmed']==0)){
		//удаление	
		if($au->user_rights->CheckAccess('w',242)){
			$_ti->Edit($id,array('status_id'=>6, 'confirm_pdate'=>time(), 'user_confirm_id'=>$result['id']),false,$result);	
			
			$stat=$_stat->GetItemById(6);
			$log->PutEntry($result['id'],'аннулирование реализации',NULL,93,NULL,'реализация № '.$trust['id'].': установлен статус '.$stat['name'],$trust['bill_id']);
			
			$log->PutEntry($result['id'],'аннулирование реализации',NULL,219,NULL,'реализация № '.$trust['id'].': установлен статус '.$stat['name'],$trust['sh_i_id']);
			
			$log->PutEntry($result['id'],'аннулирование реализации',NULL,242,NULL,'реализация № '.$trust['id'].': установлен статус '.$stat['name'],$trust['id']);	
			
			//внести примечание
			$_ni=new AccNotesItem;
			$_ni->Add(array(
				'user_id'=>$id,
				'posted_user_id'=>$result['id'],
				'note'=>'Автоматическое примечание: документ был аннулирован пользователем '.SecStr($result['name_s']).' ('.$result['login'].'), причина: '.$note,
				'is_auto'=>1,
				'pdate'=>time()
					));	
		}
	}elseif($trust['status_id']==6){
		//разудаление
		if($au->user_rights->CheckAccess('w',243)){
			$_ti->Edit($id,array('status_id'=>4, 'confirm_pdate'=>time(), 'user_confirm_id'=>$result['id']),false,$result);	
			
			$stat=$_stat->GetItemById(4);
			$log->PutEntry($result['id'],'восстановление реализации',NULL,93,NULL,'реализация № '.$trust['id'].': установлен статус '.$stat['name'],$trust['bill_id']);	
			
			$log->PutEntry($result['id'],'восстановление реализации',NULL,219,NULL,'реализация № '.$trust['id'].': установлен статус '.$stat['name'],$trust['sh_i_id']);	
			
			$log->PutEntry($result['id'],'восстановление реализации',NULL,243,NULL,'реализация № '.$trust['id'].': установлен статус '.$stat['name'],$trust['id']);
			
			//внести примечание
			$_ni=new AccNotesItem;
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
		$acg=new AccGroup;
		
		$dec=new  DBDecorator;
		
		$dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
		
		$ret=$acg->ShowAllPos($template,$dec, $au->user_rights->CheckAccess('w',235)||$au->user_rights->CheckAccess('w',286), $au->user_rights->CheckAccess('w',242), 0,100,  $au->user_rights->CheckAccess('w',240),  $au->user_rights->CheckAccess('w',96),false,true,$au->user_rights->CheckAccess('w',243),NULL,$au->user_rights->CheckAccess('w',241));
			
	}else{
		$editing_user=$_ti->getitembyid($id);
		$sm=new SmartyAj;
		
		
		
		//блок аннулирования
		
		$editing_user['can_annul']=$_ti->DocCanAnnul($editing_user['id'],$reason)&&$au->user_rights->CheckAccess('w',242);
		if(!$au->user_rights->CheckAccess('w',242)) $reason='недостаточно прав для данной операции';
		$editing_user['can_annul_reason']=$reason;
		
		
		$editing_user['can_restore']=$_ti->DocCanRestore($editing_user['id'],$reason)&&$au->user_rights->CheckAccess('w',243);
			if(!$au->user_rights->CheckAccess('w',243)) $reason='недостаточно прав для данной операции';
		
		//$editing_user['binded_to_annul']=$_ti->GetBindedDocumentsToAnnul($editing_user['id']);
		
		$sm->assign('acc',$editing_user);
		$ret=$sm->fetch('acc/toggle_annul_card.html');	
	}
}
elseif(isset($_POST['action'])&&($_POST['action']=="find_shs_pos")){
	//dostup
	$_kr=new AccReports;
	
	$id=abs((int)$_POST['id']);
	$bill_id=abs((int)$_POST['bill_id']);
	$sh_i_id=abs((int)$_POST['sh_i_id']);
	
		$pl_position_id=abs((int)$_POST['pl_position_id']);
	$pl_discount_id=abs((int)$_POST['pl_discount_id']);
	$pl_discount_value=abs((int)$_POST['pl_discount_value']);
	$pl_discount_rub_or_percent=abs((int)$_POST['pl_discount_rub_or_percent']);
	$kp_id=abs((int)$_POST['kp_id']);
	
	
	
	
	$ret=$_kr->InSh($id,$bill_id,'acc/in_shs.html',$result['org_id'],true,$sh_i_id, $pl_position_id, $pl_discount_id, $pl_discount_value,  $pl_discount_rub_or_percent, NULL, $kp_id );
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="find_acc_pos")){
	//dostup
	$_kr=new AccReports;
	
	$id=abs((int)$_POST['id']);
	$except_id=abs((int)$_POST['except_id']);
	$bill_id=abs((int)$_POST['bill_id']);
	$sh_i_id=abs((int)$_POST['sh_i_id']);
	
		$pl_position_id=abs((int)$_POST['pl_position_id']);
	$pl_discount_id=abs((int)$_POST['pl_discount_id']);
	$pl_discount_value=abs((int)$_POST['pl_discount_value']);
	$pl_discount_rub_or_percent=abs((int)$_POST['pl_discount_rub_or_percent']);
	$kp_id=abs((int)$_POST['kp_id']);
	
	
	
	$ret=$_kr->InAcc($id, $except_id, $bill_id,'acc/in_accs.html',$result['org_id'],true,$sh_i_id, $pl_position_id, $pl_discount_id, $pl_discount_value,  $pl_discount_rub_or_percent, NULL, $kp_id );
	
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="has_nakl_save")){
	$id=abs((int)$_POST['id']);
	$state=abs((int)$_POST['state']);
	
	$_ti=new AccItem;
	
	$_si=new UserSItem;
	
	$trust=$_ti->getitembyid($id);
	
	
	if($state==0){
		//есть права: либо сам утв.+есть права, либо есть искл. права:
		if(($au->user_rights->CheckAccess('w',241))&&($trust['is_confirmed']==1)&&($trust['has_nakl']==1)){
			
				$_ti->Edit($id,array('has_nakl'=>0, 'has_nakl_confirm_user_id'=>$result['id'], 'has_nakl_confirm_pdate'=>time()));
				
				
				
				$log->PutEntry($result['id'],'снял наличие оригинала товарной накладной',NULL,241, NULL, NULL,$id);
				
				
			
		}
		
	}else{
		//есть права
		
		
		  if(($au->user_rights->CheckAccess('w',241))&&($trust['is_confirmed']==1)&&($trust['has_nakl']==0)){
				//$ret.='zzzzzzzzzzzzzzzzzzzzzzz';
				  $_ti->Edit($id,array('has_nakl'=>1, 'has_nakl_confirm_user_id'=>$result['id'], 'has_nakl_confirm_pdate'=>time()));
				  
				 
				    $log->PutEntry($result['id'],'утвердил наличие оригинала товарной накладной',NULL,240, NULL, NULL,$id);	
					  
			 
		  }
	}
	
	
	$ret='';
		
}
elseif(isset($_POST['action'])&&($_POST['action']=="has_fakt_save")){
	$id=abs((int)$_POST['id']);
	$state=abs((int)$_POST['state']);
	
	$_ti=new AccItem;
	
	$_si=new UserSItem;
	
	$trust=$_ti->getitembyid($id);
	
	
	if($state==0){
		//есть права: либо сам утв.+есть права, либо есть искл. права:
		if(($au->user_rights->CheckAccess('w',241))&&($trust['is_confirmed']==1)&&($trust['has_fakt']==1)){
			
				$_ti->Edit($id,array('has_fakt'=>0, 'has_fakt_confirm_user_id'=>$result['id'], 'has_fakt_confirm_pdate'=>time()));
				
				
				
				$log->PutEntry($result['id'],'снял наличие оригинала товарной накладной',NULL,241, NULL, NULL,$id);
				
				
			
		}
		
	}else{
		//есть права
		
		
		  if(($au->user_rights->CheckAccess('w',240))&&($trust['is_confirmed']==1)&&($trust['has_fakt']==0)){
				//$ret.='zzzzzzzzzzzzzzzzzzzzzzz';
				  $_ti->Edit($id,array('has_fakt'=>1, 'has_fakt_confirm_user_id'=>$result['id'], 'has_fakt_confirm_pdate'=>time()));
				  
				 
				    $log->PutEntry($result['id'],'утвердил наличие оригинала товарной накладной',NULL,240, NULL, NULL,$id);	
					  
			 
		  }
	}
	
	
	$ret='';
		
}
elseif(isset($_POST['action'])&&($_POST['action']=="has_akt_save")){
	$id=abs((int)$_POST['id']);
	$state=abs((int)$_POST['state']);
	
	$_ti=new AccItem;
	
	$_si=new UserSItem;
	
	$trust=$_ti->getitembyid($id);
	
	
	if($state==0){
		//есть права: либо сам утв.+есть права, либо есть искл. права:
		if(($au->user_rights->CheckAccess('w',241))&&($trust['is_confirmed']==1)&&($trust['has_akt']==1)){
			
				$_ti->Edit($id,array('has_akt'=>0, 'has_akt_confirm_user_id'=>$result['id'], 'has_akt_confirm_pdate'=>time()));
				
				
				
				$log->PutEntry($result['id'],'снял наличие оригинала акта',NULL,241, NULL, NULL,$id);
				
				
			
		}
		
	}else{
		//есть права
		
		
		  if(($au->user_rights->CheckAccess('w',240))&&($trust['is_confirmed']==1)&&($trust['has_akt']==0)){
				//$ret.='zzzzzzzzzzzzzzzzzzzzzzz';
				  $_ti->Edit($id,array('has_akt'=>1, 'has_akt_confirm_user_id'=>$result['id'], 'has_akt_confirm_pdate'=>time()));
				  
				 
				    $log->PutEntry($result['id'],'утвердил наличие оригинала акта',NULL,240, NULL, NULL,$id);	
					  
			 
		  }
	}
	
	
	$ret='';
		
}elseif(isset($_POST['action'])&&($_POST['action']=="check_confirm")){
	
	
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new AccItem;
		
		 //CanConfirmByPositions($id,$rss)) $ret=$rss;
		if(!$_ki->DocCanConfirm($id,$rss12)) $ret=$rss12;
		else $ret=0;
		//if(!$_ki->DocCanConfirm($id,$rss)) $ret=$rss;
		
		//echo $id;
		
		//если ноль - то все хорошо
	
}elseif(isset($_POST['action'])&&($_POST['action']=="check_confirm_by_pos")){
	
	
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new AccItem;
		
		 
		if(!$_ki->CanConfirmByPositions($id,$rss)) $ret=$rss;
		else $ret=0;
		//if(!$_ki->DocCanConfirm($id,$rss)) $ret=$rss;
		
		//echo $id;
		
		//если ноль - то все хорошо
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="check_unconfirm")){
	
	
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new AccItem;
		
		
		if(!$_ki->DocCanUnConfirm($id,$rss13)) $ret=$rss13;
		else $ret=0;
		
		
		//если ноль - то все хорошо
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="refuse_to_confirm")){
	
	
		$id=abs((int)$_POST['id']);
		$refuse=abs((int)$_POST['refuse']);
		
		
		$description='отказ от утверждения реализации';
		
		if($refuse==1){
			$description.=' при первом подтверждении ';	
		}else{
			$description.=' при втором подтверждении ';	
		}
		
		//$_ki=new AccItem;
		
		$log->PutEntry($result['id'], $description,NULL,721, NULL, NULL,$id);	
					  	
	
}

//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>