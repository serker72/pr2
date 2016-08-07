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


require_once('../classes/sh_i_notesgroup.php');
require_once('../classes/sh_i_notesitem.php');

require_once('../classes/sh_i_item.php');
require_once('../classes/user_s_item.php');

require_once('../classes/shipreports.php');
require_once('../classes/acc_group.php');
require_once('../classes/sh_i_group.php');
require_once('../classes/sh_i_prepare.php');


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
	
	//внедрить storage_id, sector_id из карты распоряжения
	$storage_id=abs((int)$_POST['storage_id']);
	$sector_id=abs((int)$_POST['sector_id']);
	$komplekt_ved_id=abs((int)$_POST['komplekt_ved_id']);
	
	$except_id=abs((int)$_POST['ship_id']);
	
	$bill_id=abs((int)$_POST['bill_id']);


	$complex_positions=$_POST['complex_positions'];
	
	
	//GetItemsByIdArr($id,$current_id=0)
	$_kpg=new ShIPrepare;
	 //new BillPosGroup;
	$_mf=new MaxFormer;
	
	//нужно получить позиции по счету на опр объект опр участок (количества могут быть ссуммированны)
	//echo $storage_id.' '.$sector_id;
	$alls=$_kpg->GetItemsByIdArr($bill_id,$storage_id,$sector_id);
	
	
	
	
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
				
				$v['price_pm']=$valarr[10];//$v['price']+$slag;
			}else $v['price_pm']=$v['price'];
			
			$v['cost']=$v['price']*$v['quantity'];
			$v['total']=$valarr[11];//$v['price_pm']*$v['quantity'];
		
			
			$v['nds_proc']=NDS;
			$v['nds_summ']=sprintf("%.2f",($v['total']-$v['total']/((100+NDS)/100)));
			
		}else{
			$v['quantity']=0;
			
			$v['cost']=0;
			$v['total']=0;
			
			
			
		
			$v['nds_proc']=NDS;
			$v['nds_summ']=sprintf("%.2f",($v['total']-$v['total']/((100+NDS)/100)));
			
		}
		
		
		//echo $bill_id.' '.$v['position_id'].' '.$except_id.' '.$storage_id.' '.$sector_id.' '.$komplekt_ved_id;
		  $v['max_quantity']=$_mf->MaxForShI($bill_id, $v['position_id'],$except_id,$storage_id,$sector_id, $v['komplekt_ved_id']); 
		  
		   $v['hash']=md5($v['position_id'].'_'.$v['komplekt_ved_id']);
		  
		  $arr[]=$v;
	
		}
	
	

	
	$sm=new SmartyAj;
	$sm->assign('pospos',$arr);
	
	$ret.=$sm->fetch("ships/positions_edit_set.html");
}elseif(isset($_POST['action'])&&($_POST['action']=="transfer_positions")){
	//перенос выбранных позиций к.в. на страницу счет
	
	//внедрить storage_id, sector_id из карты распоряжения
	$storage_id=abs((int)$_POST['storage_id']);
	$sector_id=abs((int)$_POST['sector_id']);
	$komplekt_ved_id=abs((int)$_POST['komplekt_ved_id']);
		
	$bill_id=abs((int)$_POST['bill_id']);
		
	$complex_positions=$_POST['complex_positions'];
	
		
	$alls=array();
	
	$_position=new PosItem;
	$_dim=new PosDimItem;
	$_mf=new MaxFormer;
	
	//foreach($selected_quantities as $k=>$v){
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
		
		$f['price']=(float)$v[3];
		
		//+/-
		$f['has_pm']=(int)$v[2];
		$f['rub_or_percent']=(int)$v[4];
		$f['plus_or_minus']=(int)$v[5];
		$f['value']=(float)$v[6];
		
		$f['komplekt_ved_id']=$v[7];
		
		
		if($f['komplekt_ved_id']!=0) $f['komplekt_ved_name']='Заявка № '.$f['komplekt_ved_id'];
		else $f['komplekt_ved_name']='-';
		
		$f['in_acc']=$_mf->MaxInAcc($bill_id,$f['id'],0,0,$storage_id,$sector_id,$v[7]);
			
			
		$f['in_bill']=$_mf->MaxInBill($bill_id, $f['id'],$storage_id,$sector_id,$v[7]);
		$f['not_in_bill']= $f['in_bill']- $_mf->MaxInShI($bill_id, $f['id'],0,$storage_id,$sector_id,$v[7]);//$f['quantity'];
			
			
		//cena +-
		if((int)$v[2]==1){
			
			$f['price_pm']=$v[10]; //$f['price']+$slag;
		}else $f['price_pm']=$f['price'];
		
		//st-t'
		$f['cost']=$f['price']*$f['quantity'];
		
		
		//vsego
		$f['total']=$v[11];//$f['price_pm']*$f['quantity'];
		
		$f['nds_proc']=NDS;
		$f['nds_summ']=sprintf("%.2f",($f['total']-$f['total']/((100+NDS)/100)));
		
		$f['hash']=md5($f['id'].'_'.$f['komplekt_ved_id']);
		
		
	//	$ret.=$v.' ';
		$alls[]=$f;
	}
	
	$sm=new SmartyAj;
	$sm->assign('pospos',$alls);
	$sm->assign('cannot_view_pm',!$au->user_rights->CheckAccess('w',130));
	
	$sm->assign('can_add_positions',$au->user_rights->CheckAccess('w',216)); 
	$sm->assign('can_del_positions',$au->user_rights->CheckAccess('w',218)); 
	
	
	
	
	
	$sm->assign('can_modify',true);
	
	
	$ret=$sm->fetch("ships/positions_on_page_set.html");
}elseif(isset($_POST['action'])&&(($_POST['action']=="calc_new_total")||($_POST['action']=="calc_new_nds"))){
	//подсчет нового итого
		
	$bill_id=abs((int)$_POST['bill_id']);
	$selected_positions=$_POST['selected_positions'];
	$selected_quantities=$_POST['selected_quantities'];
	$selected_has_pms=$_POST['selected_has_pms'];
	$selected_prices=$_POST['selected_prices'];
	$selected_rub_or_percents=$_POST['selected_rub_or_percents'];
	$selected_plus_or_minuses=$_POST['selected_plus_or_minuses'];
	$selected_values=$_POST['selected_values'];
	
	$alls=array();
	

	
	foreach($selected_quantities as $k=>$v){
		$f=array();	
	
		if($v<=0) continue;
		
		$f['quantity']=$v;
		$f['id']=$selected_positions[$k];
		

		
		$f['price']=$selected_prices[$k];
		
		//+/-
		$f['has_pm']=$selected_has_pms[$k];
		$f['rub_or_percent']=$selected_rub_or_percents[$k];
		$f['plus_or_minus']=$selected_plus_or_minuses[$k];
		$f['value']=$selected_values[$k];
		
		
		//cena +-
		if($selected_has_pms[$k]==1){
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
		
		
		//vsego
		$f['total']=$f['price_pm']*$f['quantity'];
		

		$alls[]=$f;
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
	
	$_ship=new ShIItem;
	
	$user_id=abs((int)$_POST['user_id']);
	
	$rg=new ShINotesGroup;
	
	$sm->assign('items',$rg->GetItemsByIdArr($user_id,0,0,false,$au->user_rights->CheckAccess('w',341), $au->user_rights->CheckAccess('w',350),$result['id']));
	$sm->assign('word','notes');
	$sm->assign('user_id',$user_id);
	$sm->assign('named','Примечания');
	
	$sm->assign('can_edit', $au->user_rights->CheckAccess('w',220));
	
	
	$ret=$sm->fetch('ships/d_notes.html');
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="add_notes")){
	//dostup
	$user_id=abs((int)$_POST['user_id']);
	$_ship=new ShIItem;
	
	
	if(!$au->user_rights->CheckAccess('w',220)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	
	
	$ri=new ShINotesItem;
	$ri->Add(array(
				'note'=>SecStr(iconv("utf-8","windows-1251",$_POST['note'])),
				'pdate'=>time(),
				'user_id'=>$user_id,
				'posted_user_id'=>$result['id']
			));
	
	$log->PutEntry($result['id'],'добавил примечания распоряжению на отгрузку', NULL,220, NULL,SecStr(iconv("utf-8","windows-1251",$_POST['note']),9),$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="edit_notes")){
	//dostup
	$user_id=abs((int)$_POST['user_id']);
	
	$_ship=new ShIItem;
	
	
	if(!$au->user_rights->CheckAccess('w',220)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	
	$id=abs((int)$_POST['id']);
	
	
	$ri=new SHINotesItem;
	$ri->Edit($id,
				array(
				'note'=>SecStr(iconv("utf-8","windows-1251",$_POST['note'])),
				'pdate'=>time(),
				'posted_user_id'=>$result['id']/*,
				'user_id'=>$user_id*/
			));
	
	$log->PutEntry($result['id'],'редактировал примечания по распоряжению на отгрузку', NULL,220,NULL, SecStr(iconv("utf-8","windows-1251",$_POST['note']),9),$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="delete_notes")){
	//dostup
	$user_id=abs((int)$_POST['user_id']);
	
	$_ship=new ShIItem;
	
	
	if(!$au->user_rights->CheckAccess('w',220)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	
	$id=abs((int)$_POST['id']);
	
	
	$ri=new ShINotesItem;
	$ri->Del($id);
	
	$log->PutEntry($result['id'],'удалил примечания по распоряжению на отгрузку', NULL,220,NULL,NULL,$user_id);
	
}
//utv- razutv
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_confirm")){
	$id=abs((int)$_POST['id']);
	$_ti=new ShIItem;
	
	$_si=new UserSItem;
	
	$trust=$_ti->getitembyid($id);
	if($trust['confirm_pdate']==0) $trust['confirm_pdate']='-';
	else $trust['confirm_pdate']=date("d.m.Y H:i:s",$trust['confirm_pdate']);
	
	
	$si=$_si->getitembyid($trust['user_confirm_id']);
	$trust['confirmed_price_name']=$si['name_s'];
	$trust['confirmed_price_login']=$si['login'];
	
	$bill_id=$trust['bill_id'];
	
	
	$_acg=new AccGroup;
	$has_acg=$_acg->CountByShid($trust['id'],1);
	
	if($trust['is_confirmed']==1){
		//есть права: либо сам утв.+есть права, либо есть искл. права:
		if(($au->user_rights->CheckAccess('w',225))||$au->user_rights->CheckAccess('w',96)){
			if((($trust['status_id']==7)||($trust['status_id']==8)||($trust['status_id']==2))){
				if($_ti->DocCanUnconfirm($id, $reas)){
				$_ti->Edit($id,array('is_confirmed'=>0, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'снял утверждение распоряжения на отгрузку',NULL,93, NULL, NULL,$bill_id);
				
				$log->PutEntry($result['id'],'снял утверждение распоряжения на отгрузку',NULL,225, NULL, NULL,$id);
				}
			}
		}else{
			//нет прав	
		}
		
	}else{
		//есть права
		if($au->user_rights->CheckAccess('w',224)||$au->user_rights->CheckAccess('w',96)){
			if($trust['status_id']==1){
				$_ti->Edit($id,array('is_confirmed'=>1, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'утвердил распоряжения на отгрузку',NULL,93, NULL, NULL,$bill_id);
				
				$log->PutEntry($result['id'],'утвердил распоряжения на отгрузку',NULL,224, NULL, NULL,$id);	
					
			}
		}else{
			//do nothing
		}
	}
	
	
	
	$shorter=abs((int)$_POST['shorter']);
	if($shorter==0) $template='ships/all_ships_list.html';
	else $template='ships/ships_list.html';
	
	
	$acg=new ShIGroup;
	
	$dec=new  DBDecorator;
	
	$dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	
	
	$ret=$acg->ShowAllPos( $template, $dec, $au->user_rights->CheckAccess('w',219)||$au->user_rights->CheckAccess('w',285), $au->user_rights->CheckAccess('w',226),0,100, $au->user_rights->CheckAccess('w',224), $au->user_rights->CheckAccess('w',96),false,true,$au->user_rights->CheckAccess('w',227),NULL,$au->user_rights->CheckAccess('w',225));
	
	
		
}
//udalenie-annulirovabie
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_annul")){
	$id=abs((int)$_POST['id']);
	
	$shorter=abs((int)$_POST['shorter']);
	if($shorter==0) $template='ships/all_ships_list.html';
	else $template='ships/ships_list.html';
	
	$note=SecStr(iconv("utf-8","windows-1251",$_POST['note']));
	
	if(isset($_POST['from_card'])&&($_POST['from_card']==1)) $from_card=1;
	else $from_card=0;
	
	$_ti=new ShIItem;
	
	$_si=new UserSItem;
	
	$trust=$_ti->getitembyid($id);
	
	
	$log=new ActionLog;
	$_stat=new DocStatusItem;
	
	if(($trust['status_id']==1)&&($trust['is_confirmed']==0)){
		//удаление	
		if($au->user_rights->CheckAccess('w',226)){
			$_ti->Edit($id,array('status_id'=>3, 'confirm_pdate'=>time(), 'user_confirm_id'=>$result['id']),false,$result);
			
			$stat=$_stat->GetItemById(3);
			$log->PutEntry($result['id'],'аннулирование распоряжения на отгрузку',NULL,93,NULL,'распоряжение на отгрузку № '.$trust['id'].': установлен статус '.$stat['name'],$trust['bill_id']);
			
			$log->PutEntry($result['id'],'аннулирование распоряжения на отгрузку',NULL,226,NULL,'распоряжение на отгрузку № '.$trust['id'].': установлен статус '.$stat['name'],$id);
			
			
			//уд-ть связанные документы
			$_ti->AnnulBindedDocuments($id);
			
			//внести примечание
			$_ni=new ShINotesItem;
			$_ni->Add(array(
				'user_id'=>$id,
				'posted_user_id'=>$result['id'],
				'note'=>'Автоматическое примечание: документ был аннулирован пользователем '.SecStr($result['name_s']).' ('.$result['login'].'), причина: '.$note,
				'is_auto'=>1,
				'pdate'=>time()
					));		
		}
	}elseif($trust['status_id']==3){
		//разудаление
		if($au->user_rights->CheckAccess('w',227)){
			$_ti->Edit($id,array('status_id'=>1, 'confirm_pdate'=>time(), 'user_confirm_id'=>$result['id']),false,$result);
			
			$stat=$_stat->GetItemById(1);
			$log->PutEntry($result['id'],'восстановление распоряжения на отгрузку',NULL,93,NULL,'распоряжение на отгрузку № '.$trust['id'].': установлен статус '.$stat['name'],$trust['bill_id']);		
			
			
			$log->PutEntry($result['id'],'восстановление распоряжения на отгрузку',NULL,227,NULL,'распоряжение на отгрузку № '.$trust['id'].': установлен статус '.$stat['name'],$id);	
			
			//внести примечание
			$_ni=new ShINotesItem;
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
	
		$acg=new ShIGroup;
		
		$dec=new  DBDecorator;
		
		$dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
		
		
		$ret=$acg->ShowAllPos( $template, $dec, $au->user_rights->CheckAccess('w',219)||$au->user_rights->CheckAccess('w',285), $au->user_rights->CheckAccess('w',226),0,100, $au->user_rights->CheckAccess('w',224), $au->user_rights->CheckAccess('w',96),false,true,$au->user_rights->CheckAccess('w',227),NULL,$au->user_rights->CheckAccess('w',225));
	}else{
		$editing_user=$_ti->getitembyid($id);
		$sm=new SmartyAj;
		
		
		
		//блок аннулирования
		
		$editing_user['can_annul']=$_ti->DocCanAnnul($editing_user['id'],$reason)&&$au->user_rights->CheckAccess('w',226);
		if(!$au->user_rights->CheckAccess('w',226)) $reason='недостаточно прав для данной операции';
		$editing_user['can_annul_reason']=$reason;
		
		$editing_user['binded_to_annul']=$_ti->GetBindedDocumentsToAnnul($editing_user['id']);
		
		$sm->assign('ship',$editing_user);
		$ret=$sm->fetch('ships/toggle_annul_card.html');	
	}
}
elseif(isset($_POST['action'])&&($_POST['action']=="find_acc_pos")){
	//dostup
	$_kr=new ShipReports;
	
	$id=abs((int)$_POST['id']);
	$bill_id=abs((int)$_POST['bill_id']);
	$sh_i_id=abs((int)$_POST['sh_i_id']);
	
	
	$pl_position_id=abs((int)$_POST['pl_position_id']);
	$pl_discount_id=abs((int)$_POST['pl_discount_id']);
	$pl_discount_value=abs((int)$_POST['pl_discount_value']);
	$pl_discount_rub_or_percent=abs((int)$_POST['pl_discount_rub_or_percent']);
	$kp_id=abs((int)$_POST['kp_id']);
	
	
	//$ret=$_kr->InAcc($id,$bill_id, 'ships/in_accs.html',$result['org_id'],true,$sh_i_id,$storage_id,$sector_id,$komplekt_ved_id,$au->user_rights->CheckAccess('w',87));
	$ret=$_kr->InAcc($id,$bill_id, 'ships/in_accs.html',$result['org_id'],true, $sh_i_id, $pl_position_id, $pl_discount_id, $pl_discount_value,  $pl_discount_rub_or_percent, $au->user_rights->CheckAccess('w',87) ,NULL, $kp_id );
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="find_sh_pos")){
	//dostup
	$_kr=new ShipReports;
	
	$id=abs((int)$_POST['id']);
	$bill_id=abs((int)$_POST['bill_id']);
	$sh_i_id=abs((int)$_POST['sh_i_id']);
	
	$kp_id=abs((int)$_POST['kp_id']);
	
	
	$ret=$_kr->InSh($id,$bill_id,'ships/in_shs.html',$result['org_id'],true,$sh_i_id, $storage_id,$sector_id,$komplekt_ved_id, $au->user_rights->CheckAccess('w',87));
	
	
}

elseif(isset($_POST['action'])&&($_POST['action']=="find_bills_pos")){
	//dostup
	$_kr=new ShipReports;
	
	$id=abs((int)$_POST['id']);
	$bill_id=abs((int)$_POST['bill_id']);
	$sh_i_id=abs((int)$_POST['sh_i_id']);
	$sector_id=abs((int)$_POST['sector_id']);
	$storage_id=abs((int)$_POST['storage_id']);
	$komplekt_ved_id=abs((int)$_POST['komplekt_ved_id']);
	
	$ret=$_kr->InBills($id,$bill_id,'ships/in_bills.html',$result['org_id'],true,$sh_i_id,$storage_id,$sector_id,$komplekt_ved_id, $au->user_rights->CheckAccess('w',87));
	
	
}

elseif(isset($_POST['action'])&&($_POST['action']=="toggle_eq")){
	//выравнивание
	$id=abs((int)$_POST['id']);
	
	$_sh=new ShIItem;
	
	if($au->user_rights->CheckAccess('w',293)){
		
		$args=$_POST['args'];
		
		//$_sh_p=new ShIPosItem();
		
		
		$_sh->DoEq($id,$args,$output);
		
		$ret='<script>alert("'.$output.'"); location.reload();</script>';
		
	}else{
		$ret='<script>alert("У Вас недостаточно прав для данного действия.");</script>';	
	}
	
}elseif(isset($_POST['action'])&&($_POST['action']=="toggle_scan_eq")){
	//выравнивание
	$id=abs((int)$_POST['id']);
	$_sh=new ShIItem;
	
	if($au->user_rights->CheckAccess('w',293)){
		
		$args=$_POST['args'];
		
		//$_sh_p=new ShIPosItem();
		
		
		$_sh->ScanEq($id,$args,$output);
		
		$ret=$output;
		
	}else{
		$ret='<script>alert("У Вас недостаточно прав для данного действия.");</script>';	
	}
	
}elseif(isset($_POST['action'])&&($_POST['action']=="check_unconfirm")){
	//проверить, есть ли заявки с таким номером для такого уч.
	
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new ShIItem;
		
		
		if(!$_ki->DocCanUnconfirm($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
	
}elseif(isset($_POST['action'])&&($_POST['action']=="check_confirm")){

		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new ShIItem;
		
		 //>CanConfirmByPositions($id,$rss)) $ret=$rss;
		
		if(!$_ki->DocCanConfirm($id, $rss55)) $ret=$rss55;
		else $ret=0;
		
		//если ноль - то все хорошо
	
}

//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>