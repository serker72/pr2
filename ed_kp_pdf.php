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
require_once('classes/discr_table_group.php');
require_once('classes/discr_table_objects.php');
require_once('classes/discr_table_user.php');
require_once('classes/actionlog.php');

require_once('classes/posgroupgroup.php');
require_once('classes/positem.php');

require_once('classes/pl_groupitem.php');

require_once('classes/posdimitem.php');
require_once('classes/bdetailsitem.php');
require_once('classes/bdetailsgroup.php');

require_once('classes/kpitem.php');
require_once('classes/kppositem.php');
require_once('classes/kpposgroup.php');
require_once('classes/billpospmformer.php');

require_once('classes/user_s_item.php');
require_once('classes/user_s_group.php');


require_once('classes/orgitem.php');
require_once('classes/opfitem.php');

require_once('classes/kpnotesgroup.php');
require_once('classes/kpnotesitem.php');

require_once('classes/kpcreator.php');

require_once('classes/pergroup.php');

require_once('classes/period_checker.php');

require_once('classes/pl_disitem.php');
require_once('classes/pl_disgroup.php');
require_once('classes/pl_positem.php');
require_once('classes/pl_dismaxvalgroup.php');

require_once('classes/supcontract_item.php');
require_once('classes/supcontract_group.php');

require_once('classes/kp_pospmformer.php');

require_once('classes/pl_currgroup.php');
require_once('classes/pl_curritem.php');
require_once('classes/kp_supply_group.php');
require_once('classes/kp_supply_item.php');
require_once('classes/kp_paymode_group.php');
require_once('classes/kp_paymode_item.php');
require_once('classes/kp_form_item.php');
require_once('classes/user_s_group.php');
require_once('classes/usercontactdatagroup.php');

require_once('classes/kp_warranty_group.php');
require_once('classes/kp_warranty_item.php');


require_once('classes/kp_supply_pdate_group.php');
require_once('classes/kp_supply_pdate_item.php');


require_once('classes/phpmailer/class.phpmailer.php');

require_once('classes/orgitem.php');
require_once('classes/opfitem.php');

require_once('classes/supplieritem.php');
require_once('classes/suppliercontactitem.php');

require_once('classes/suppliercontactgroup.php');
require_once('classes/lead.class.php');
require_once('classes/tz.class.php');
require_once('classes/lead_history_item.php');
require_once('classes/docstatusitem.php');

require_once('classes/pl_proditem.php');

$_orgitem=new OrgItem;


$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Редактирование коммерческого предложения');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

$_bill=new KpItem;
$_bpi=new KpPosItem;
$_position=new PosItem;


$log=new ActionLog;

$_posgroupgroup=new PosGroupGroup;


$lc=new KpCreator;

$_supgroup=new SuppliersGroup;
$_opf=new OpfItem;


$_pch=new PeriodChecker;
$pch_date=$_pch->GetDate();


 
if(!isset($_GET['print'])){
	if(!isset($_POST['print'])){
		$print=0;
	}else $print=abs((int)$_POST['print']); 
}else $print=abs((int)$_GET['print']);

if($print!=0){
	if(!$au->user_rights->CheckAccess('w',712)){
	  header("HTTP/1.1 403 Forbidden");
	  header("Status: 403 Forbidden");
	  include("403.php");
	  die();	
	}
}

	$object_id[]=701;
	$object_id[]=712;
	
	
//echo $object_id;
//die();
$cond=false;
foreach($object_id as $k=>$v){
if($au->user_rights->CheckAccess('w',$v)){
	$cond=$cond||true;
}
}
if(!$cond){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}

$_editable_status_id=array();
$_editable_status_id[]=1;

 
 
/*
error_reporting(-1);
 
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);*/
 
	
	
	if(!isset($_GET['id'])){
		if(!isset($_POST['id'])){
			header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			include('404.php');
			die();
		}else $id=abs((int)$_POST['id']);	
	}else $id=abs((int)$_GET['id']);
	
	//проверка наличия пользователя
	$editing_user=$_bill->GetItemById($id);
	if($editing_user===false){
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include('404.php');
		die();
	}
	
	
	/*$podd=array();
	if(!$au->user_rights->CheckAccess('w',763)){
		
		$_usg=new UsersSGroup;
		
		$_usg->GetSubordinates($result['id'], $podd);
		
		if(!in_array($editing_user['user_manager_id'], $podd)){
			header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			include('404.php');
			die();
		}
		
	}*/
	
	
	$orgitem=$_orgitem->getitembyid($editing_user['org_id']);
	foreach($editing_user as $k=>$v) $editing_user[$k]=stripslashes($v);
	
	
 
		$log->PutEntry($result['id'],'получил pdf-версию коммерческого предложения',NULL,712, NULL, $editing_user['code'], $id);	
	 	
	
	if($editing_user['pdate']==0) $editing_user['pdate_f']='-';
		else $editing_user['pdate_f']=date("d-m-Y", $editing_user['pdate']);	

	
	
	if($editing_user['pdate']==0) $editing_user['pdate']='-';
		else $editing_user['pdate']=date("d.m.Y", $editing_user['pdate']);
		
		
	
	if($editing_user['valid_pdate']==0) $editing_user['valid_pdate']='-';
		else $editing_user['valid_pdate']=date("d.m.Y", $editing_user['valid_pdate']);
		
		if($editing_user['supply_pdate']==0) $editing_user['supply_pdate']='-';
		else $editing_user['supply_pdate']=date("d.m.Y", $editing_user['supply_pdate']);
		
		


	//позиции
	$_bpg=new KpPosGroup;
	$bpg=$_bpg->GetItemsByIdArr($editing_user['id']);

	
	//стоимость и итого
	$_bpf=new KpPosPMFormer; //BillPosPMFormer;
	$total_cost=$_bpf->CalcCost($bpg);
	$total_nds=$_bpf->CalcNDS($bpg);
	//$sm1->assign('positions',$bpg);
	
	//
	
	//подставить валюту в общую сумму
	
	//$sm1->assign('total_nds',$total_nds);
		
		
	//найдем форму	
	$main_id=0; $main_index=-1;
	foreach($bpg as $k=>$v){
		if($v['parent_id']==0){
			$main_id=$v['pl_position_id'];
			$main_index=$k;
			break;	
		}
	}
	$primary_position=$bpg[$main_index];
	$_pl=new PlPosItem;
	$pl=$_pl->GetItemById($main_id);
	$_kp_form=new KpFormItem;
	$kp_form=$_kp_form->GetItemById($pl['kp_form_id']);
	
	
	//var_dump($pl); // 
	//echo $pl['producer_id'];
	
	
	if($kp_form===false){
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include('404.php');
		die();
	}
	
	$sm=new SmartyAdm;
	$header=$sm->fetch('kp_pdf/common/header.html');
	
	$sm=new SmartyAdm;
	$footer=$sm->fetch('kp_pdf/common/footer.html');
	
	$sm=new SmartyAdm;
	
	$sm->assign('producer_id', $pl['producer_id']);
	
	
	$primary_position['txt_for_kp']=eregi_replace('{name}',   $primary_position['position_name'], $primary_position['txt_for_kp']);
	$sm->assign('primary_position', $primary_position);
	
	
	//пересортируем позиции по группам
	$grops=array(); $_pos=new PosItem; $_gr=new PlGroupItem;
	foreach($bpg as $k=>$v){
		/*echo '<pre>';
		print_r($v);
		echo '</pre>';	*/
		if($v['parent_id']!=0){
			$pos=$_pos->GetItemById($v['position_id']);
			//print_r($pos);
			
			if(!in_array($pos['pl_group_id'], $grops)) $grops[]=$pos['pl_group_id'];
		}
	}
	$pos_sorted=array();
	/*foreach($grops as $k=>$v){
		$gr=$_gr->GetItemById($v);
		
		
		$arr=array(
			'id'=>$v,
			'name'=>$gr['name']
		);
		
		
		
	}*/
	if(count($grops)>0){
		$sql='select * from pl_group where id in('.implode(', ', $grops).')';
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);	
			
			
			/*$arr=array(
				'id'=>$f['id'],
				'name'=>$f['name']
			);
			$pos_sorted[]=$arr;*/
			$arr=array(
				
				'name'=>$f['name'],
				'items'=>array()
			);
			$pos_sorted[$f['id']]=$arr;
			
		}
			
	}
	
	
	
	
	
	//подтянем опции
	foreach($bpg as $k=>$v){
		/*echo '<pre>';
		print_r($v);
		echo '</pre>';	*/
		if($v['parent_id']!=0){
			$pos=$_pos->GetItemById($v['position_id']);
			//print_r($pos);
			
			//if(!in_array($pos['pl_group_id'], $grops)) $grops[]=$pos['pl_group_id'];
			
			$arr=$pos_sorted[$pos['pl_group_id']];
			$items=$arr['items'];
			$items[]=$v;
			$arr['items']=$items;
			$pos_sorted[$pos['pl_group_id']]=$arr;
		}
	}
	
	//добавим станок в таблицу опций
	$sql='select * from pl_group where id=1';
	$set=new mysqlset($sql);
	$rs=$set->GetResult();
	$f=mysqli_fetch_array($rs);	
	
	foreach($bpg as $k=>$v){
	 
		if($v['parent_id']==0){
			//$pos_sorted[1]=array('name'=>$f['name'],'items'=>array($v));
			array_unshift($pos_sorted, array('name'=>$f['name'],'items'=>array($v)));
		}
	}
	
	/*echo '<pre>';
	print_r($pos_sorted);
	echo '</pre>';*/
	
	$sm->assign('pospos',$pos_sorted);
	$sm->assign('pospos_old',$bpg);
	
	 
	$total_cost_base=0;
	foreach($bpg as $k=>$v){
		$total_cost_base+=$v['price']*$v['quantity'];
	}
	//$sm->assign('total_cost_base',number_format($total_cost/(1-$primary_position['pl_discount_value']/100),  0, ',', ' '));
	$sm->assign('total_cost_base',number_format($total_cost_base,  0, ',', ' '));
 
	
	$sm->assign('total_cost',number_format($total_cost, 0, ',', ' '));
	
	
	
	$currency=$_bpf->GetCurrency($bpg);	
	//print_r($currency);	
	$sm->assign('signature',$currency['signature']);
	
	
	//поставка
	$_ki=new KpSupplyItem;
	$ki=$_ki->GetItemById($editing_user['supply_id']);
	$sm->assign('supply',$ki);
	
	//срок поставки
	$_ks=new KpSupplyPdateItem;
	$ks=$_ks->GetItemById($editing_user['supply_pdate_id']);  
	$sm->assign('supply_pdate_name', $ks['name']);
	
	//усл оплаты
	$_ki=new KpPaymodeItem;
	$ki=$_ki->GetItemById($editing_user['paymode_id']);
	$sm->assign('paymode',$ki);
	
	//кто подписал
	$_ui=new UserSItem;
	$user=$_ui->GetItemById($editing_user['user_dir_pr_id']);
	$sm->assign('d_user',$user);
	//контакты
	$rg=new UserContactDataGroup;
	$sm->assign('d_contacts',$rg->GetItemsByIdArr($editing_user['user_dir_pr_id']));
		
	$_orgitem=new OrgItem;	
	$orgitem=$_orgitem->getitembyid($editing_user['org_id']);
	$_opf=new OpfItem;
	$opf=$_opf->GetItemById($orgitem['opf_id']);
	$sm->assign('org', $orgitem);
	$sm->assign('opf', $opf);
		
	//кто подписал
	$_ui=new UserSItem;
	$user=$_ui->GetItemById($editing_user['user_manager_id']);
	$sm->assign('m_user',$user);
	//контакты
	$rg=new UserContactDataGroup;
	$sm->assign('m_contacts',$rg->GetItemsByIdArr($editing_user['user_manager_id']));		
	
	
	//гарантия
	$_ki=new KpWarrantyItem;
	$ki=$_ki->GetItemById($editing_user['warranty_id']);
	$sm->assign('warranty',$ki);
	
	
 	
	
	//поставщики
		
		
		 
		$_sup=new SupplierItem; $_sup_opf=new OpfItem;
		$sup=$_sup->GetItemById($editing_user['supplier_id']);
		$sup_opf=$_sup_opf->GetItemById($sup['opf_id']);
		$editing_user['supplier_string']=$sup_opf['name'].' '.$sup['full_name'];
		$_cont=new SupplierContactItem;
		$cont=$_cont->GetItemById($editing_user['contact_id']);
		$editing_user['contact_string']=$cont['name'];//.', '.$cont['position'];
		
		
	
	$sm->assign('bill', $editing_user);
	
	
	$html=$sm->fetch('kp_pdf/'.$kp_form['file_template'].'/body.html');
	//echo $html;
	
	//die();


	
	
	$tmp=time();
	
	$f=fopen(ABSPATH.'/tmp/'.$tmp.'.html','w');
	fputs($f, $html);
	fclose($f);
	
	$cd = "cd ".ABSPATH.'/tmp';
	exec($cd);
	
	$comand = "wkhtmltopdf-i386 --encoding windows-1251 --page-size A4 --margin-top 35mm --margin-bottom 27mm --margin-left 10mm --margin-right 10mm --footer-html ".SITEURL."/tpl-sm/pl_pdf/pdf_footer.html --header-html ".SITEURL."/tpl-sm/kp_pdf/common/pdf_header.html  ".SITEURL.'/tmp/'.$tmp.'.html'."  ".ABSPATH.'/tmp/'."$tmp.pdf";
	


exec($comand);
	
	
	if(isset($_GET['send_email'])&&($_GET['send_email']==1)&&isset($_GET['email'])){
		$emails=$_GET['email'];
		$_emails=explode(',',$emails);
		
		
		$filename=ABSPATH.'/tmp/'."$tmp.pdf";
		
		
		
		
		 //%{$contact.pc_name}%:
		 //%{$contact.value}%
		 $contact_str='';
		 foreach($rg->GetItemsByIdArr($editing_user['user_manager_id']) as $k=>$v){
			$contact_str.='<div>'.$v['pc_name'].': '.$v['value']."</div>"; 
		 }
		
		//добавить реквизиты организации
		$contact_str.='<div></div>';
		$contact_str.='<div>'.SecStr($opf['name'].' '.$orgitem['full_name']).'</div>';
		
		
		//использовать класс отправки сообщения
		
		$was_sent_to_supplier=false;
		//ищем все адреса почты контрагента, среди них найдем каждый отправляемый
		//если было хотя бы одно совпадение - то отправлять лид (при его наличии) в статус 35 на рассмотрении
		//а ТЗ - в статус КП отправлено контрагенту
		$_scg=new SupplierContactGroup;
		$scg=$_scg->GetItemsByIdArr($editing_user['supplier_id']);
		$sprav_emails=array();
		foreach($scg as $kc=>$vc){
			foreach($vc['data'] as $kd=>$vd) if($vd['kind_id']==5) $sprav_emails[]=$vd['value']; 	
		}
		
		foreach($_emails as $k=>$email){
			$mail = new PHPMailer();
			$body = "<div>Уважаемый партнер!</div>
<div>&nbsp;</div>
<div><i>Это сообщение сформировано автоматически, просьба не отвечать на него.</i></div>
<div>&nbsp;</div>

<div>Вам направлено коммерческое предложение, доступное в приложенном файле. Просьба ознакомиться и связаться с Вашим менеджером.</div>
<div>&nbsp;</div>
<div>Ваш менеджер: ".$user['name_s']."</div>
".$contact_str."

 "; 
			$mail->SetFrom(FEEDBACK_EMAIL, $opf['name'].' '.$orgitem['full_name']);
		
			$mail->AddAddress(trim($email),  $email);
		
			$mail->Subject = "коммерческое предложение для Вас!"; 
			$mail->Body=$body;
			$mail->AddAttachment($filename, 'КП_ООО_Аэротэк_№_'.$editing_user['code'].'_'.$editing_user['pdate_f'].'.pdf'); 
			$mail->CharSet = "windows-1251";
			$mail->IsHTML(true);  
			
			if(!$mail->Send())
			{
				//echo "Ошибка отправки письма: " . $mail->ErrorInfo;
			}
			else 
			{
				// echo "Письмо отправленно!";
				/*
				*/
				
			}
			if(in_array($email, $sprav_emails)) $was_sent_to_supplier=$was_sent_to_supplier||true;
			
			
			$log->PutEntry($result['id'],'отправил pdf-форму коммерческого предложения на эл.почту',NULL, 701, NULL, 'коммерческое предложение '.$editing_user['code'].', адрес эл. почты '.$email,$editing_user['id']);
			
			$_kni=new KpNotesItem;
			$notes_params=array();
			$notes_params['is_auto']=1;
			$notes_params['user_id']=$editing_user['id'];
			$notes_params['pdate']=time();
			$notes_params['posted_user_id']=$result['id'];
		  
		  	if(isset($_GET['doClose'])) $paste='';
			else $paste='при утверждении';
			
			
			$notes_params['note']='Автоматическое примечание: pdf-форма коммерческого предложения была отправлена на электронную почту '.$email.' '.date('d.m.Y H:i:s').' '.$paste.' пользователем '.SecStr($result['name_s'].' '.$result['login']);
			$_kni->Add($notes_params);
			
		}	
		
		
		//удалим старые вложения к КП
		$_bill->ClearLostFiles();
		
		
		//при наличии лида по КП и ТЗ по лиду провести им смену статуса
		  //ЖС + примечания
		  if(($editing_user['lead_id']!=0)&&($was_sent_to_supplier)){
			  //лид - в статус 35
			  $_lead=new Lead_Item;
			  $_lh=new Lead_HistoryItem;
			  
			  $_dsi=new DocStatusItem;
			  $status=$_dsi->GetItemById(35);
			  $lead=$_lead->GetItemById($editing_user['lead_id']);
			  $_lead->Edit($editing_user['lead_id'], array('status_id'=>35),true,$result);
			  
			  $comment=" лид $lead[code] переведен в статус $status[name] при отправке сотрудником $result[name_s] КП $editing_user[code] на электронную почту клиента";
			  
			  $_lh->Add(array(
				  'sched_id'=>$editing_user['lead_id'],
				  'user_id'=>0,
				  'pdate'=>time(),
				  'txt'=>"<div>Автоматический комментарий: $comment</div>"
				  ));
				  
			  $log->PutEntry($result['id'],'автоматическая смена статуса лида', NULL, 950, NULL, SecStr($comment,10),$editing_user['lead_id']);
			  
			  
			  
			  //ТЗ - в статус 39
			  $_tz=new TZ_Item;
			  $status=$_dsi->GetItemById(39);
			  //$tz=$_tz->GetItemById(
			  $sql='select * from tz where lead_id="'.$editing_user['lead_id'].'" and status_id<>3 and is_confirmed=1';
			  $set=new mysqlset($sql);
			  $rs=$set->GetResult();
			  $rc=$set->GetResultNumRows();
			  for($i=0; $i<$rc; $i++){
				  $f=mysqli_fetch_array($rs);
				  $tz=$_tz->getitembyid($f['id']);
				  
				  $_tz->Edit($f['id'], array('status_id'=>39),true,$result);
				  
				  $comment=" ТЗ $tz[code] переведено в статус $status[name] при отправке сотрудником $result[name_s] КП $editing_user[code] на электронную почту клиента";	
				  
				  $log->PutEntry($result['id'],'автоматическая смена статуса ТЗ', NULL, 1009, NULL, SecStr($comment,10),$f['id']);
				  
				  //остановим счетчики лида, ТЗ
				  $_wi=new TZ_WorkingItem;
				  $_wi->Add(array('sched_id'=>$f['id'], 'kind_id'=>0, 'in_or_out'=>1, 'pdate'=>time()));
			  
			  }
			  
			  
			  
			  $_wi=new Lead_WorkingItem;
			  $_wi->Add(array('sched_id'=>$editing_user['lead_id'], 'kind_id'=>0, 'in_or_out'=>1, 'pdate'=>time()));
		  }
		
		
		
		
		//перейти в карту КП
		if(!isset($_GET['doClose'])){
			header("Location: ed_kp.php?action=1&id=".$editing_user['id'].'&from_begin=1');
		}else{
			/*echo '<script type="text/javascript"> alert("Коммерческое предложение было отправлено на адреса электронной почты: '.$_GET['email'].'"); window.close();</script>';	*/
			
			$sm=new SmartyAdm;
			
			$txt='';
			$txt.='<div><strong>Коммерческое предложение было отправлено на следующие адреса:</strong></div>';
			$txt.='<ul>';
			
			foreach($_emails as $k=>$email){
				$txt.='<li>'.$email.'</li>';
			}
			$txt.='</ul>';
			
		//	if(count($_filenames)>0){
				$txt.='<div>&nbsp;</div>';
				$txt.='<div><strong>Были приложены следующие файлы:</strong></div>';
				$txt.='<ul>';
				//foreach($_filenames as $k=>$file){
					$txt.='<li>'.'Коммерческое_предложение_ООО_Аэротэк_№_'.$editing_user['code'].'_'.$editing_user['pdate_f'].'.pdf'.'</li>';
				//}
				$txt.='</ul>';
			//}
			
		 
			//$txt.='<p></p>';			
			
			$sm->assign('message', $txt);
			
			$sm->display('page_email.html');
			
			
			
			
			
			
		}
		die();	
		
		
	}else{
		
	
		
		//$mpdf->Output($primary_position['position_name'].'_'.$editing_user['code'].'.pdf','D');
		header('Content-type: application/pdf');
		header('Content-Disposition: attachment; filename="Коммерческое_предложение_ООО_Аэротэк_№_'.$editing_user['code'].'_'.$editing_user['pdate_f'].'.pdf'.'"');
		readfile(ABSPATH.'/tmp/'.$tmp.'.pdf');
	}
	


	unlink(ABSPATH.'/tmp/'.$tmp.'.pdf');
	unlink(ABSPATH.'/tmp/'.$tmp.'.html');
	
	exit;

?>