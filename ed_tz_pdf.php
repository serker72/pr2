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

 require_once('classes/pl_currgroup.php');
require_once('classes/pl_curritem.php');

require_once('classes/suppliersgroup.php');
require_once('classes/supplieritem.php');
 

 

require_once('classes/user_s_item.php');

 

 
require_once('classes/orgitem.php');
require_once('classes/opfitem.php');

 

require_once('classes/suppliercontactitem.php');
require_once('classes/supcontract_group.php');

require_once('classes/tender.class.php');

 
require_once('classes/lead.class.php');
require_once('classes/tz.class.php');



require_once('classes/tz_fileitem.php');
require_once('classes/tz_filegroup.php');
 

require_once('classes/supplier_country_group.php');
require_once('classes/supplier_city_item.php');
 
require_once('classes/lead_history_group.php');
require_once('classes/docstatusitem.php');

require_once('classes/lead_history_item.php'); 

require_once('classes/lead_view_item.php');

require_once('classes/user_dep_item.php');
require_once('classes/user_main_dep_item.php');

require_once('classes/usercontactdataitem.php'); 
require_once('classes/phpmailer/class.phpmailer.php');

$_pch=new PeriodChecker;
$pch_date=$_pch->GetDate();



$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'ТЗ');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}


$_dem=new TZ_AbstractItem;

$_plan1=new TZ_Group;
$available_users=$_plan1->GetAvailableTZIds($result['id']);

$_plan=new TZ_Group;


$_supplier=new SupplierItem;
 $log=new ActionLog;
 $_supgroup=new SuppliersGroup;

 
$_orgitem=new OrgItem;
$orgitem=$_orgitem->GetItemById($result['org_id']);
$_opf=new OpfItem;
$opfitem=$_opf->GetItemById($orgitem['opf_id']);

if(!isset($_GET['action'])){
	if(!isset($_POST['action'])){
		$action=0;
	}else $action=abs((int)$_POST['action']);
}else $action=abs((int)$_GET['action']);

if(!isset($_GET['from_begin'])){
	if(!isset($_POST['from_begin'])){
		$from_begin=0;
	}else $from_begin=1; 
}else $from_begin=1;

$object_id=array();
switch($action){
	case 0:
	$object_id[]=1006;
	break;
	case 1:
	$object_id[]=1009;
	break;
	case 2:
	$object_id[]=1009;
	break;
	default:
	$object_id[]=1009;
	break;
}

$_editable_status_id=array();
$_editable_status_id[]=1;
 

	
  if(isset($_GET['addresses'])){
	$addresses=$_GET['addresses'];
}else $addresses='';

//массив адресатов
$_addresses=explode(',',$addresses);

if(!isset($_GET['print'])){
	if(!isset($_POST['print'])){
		$print=1;
	}else $print=abs((int)$_POST['print']); 
}else $print=abs((int)$_GET['print']);

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
 
	


 
	if(!isset($_GET['id'])){
		if(!isset($_POST['id'])){
			header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			include('404.php');
			die();
		}else $id=abs((int)$_POST['id']);	
	}else $id=abs((int)$_GET['id']);
	
	//проверка наличия пользователя
	$editing_user=$_dem->GetItemByFields(array('id'=>$id));
	if($editing_user===false){
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include('404.php');
		die();
	}
	foreach($editing_user as $k=>$v) $editing_user[$k]=stripslashes($v);
	
	
	$_tg=new TZ_Group;
	
	if(!$au->user_rights->CheckAccess('w',1008)){	
		$available_tenders=$_tg->GetAvailableTZIds($result['id']);
		$is_shown=in_array($id, $available_tenders);
	
		if(!$is_shown){
			header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			include('404.php');
			die();
		}
	}
	
 
 

  

 //журнал событий 
/*if($action==1){
	$log=new ActionLog;
	 
	$log->PutEntry($result['id'],'открыл печатную форму ТЗ',NULL,1009, NULL, 'ТЗ № '.$editing_user['code'],$id);
 
} */

		if($print==0) $print_add='';
		else $print_add='_print';


	//демонстрация  страницы
	$smarty = new SmartyAdm;
	
	$sm1=new SmartyAdm;
	
	$sm=new SmartyAdm;
	
 
	 
		//редактирование позиции
		
		if($print==0) $print_add='';
		else $print_add='_print';
		
		
		//построим доступы
		/*$_roles=new Lead_FieldRules($result); //var_dump($_roles->GetTable());
		$field_rights=$_roles->GetFields($editing_user, $result['id']);
		$sm1->assign('field_rights', $field_rights);*/
		
		//var_dump($field_rights);
		
		
		//построим доступы
		$can_modify=in_array($editing_user['status_id'],$_editable_status_id);
		
		
			 
			 $_lead=new Lead_Item;
			 $lead=$_lead->GetItemById($editing_user['lead_id']);
			 $sm1->assign('lead', $lead);
			 
		 
			 	
		 
		
		
		$_res=new TZ_Resolver();
		
		$editing_user['pdate']=date('d.m.Y H:i:s', $editing_user['pdate']);
		
		
		
		
		if($editing_user['given_pdate']!='')  $editing_user['given_pdate']=date('d.m.Y', $editing_user['given_pdate']);
		
		
		   
		
	  
		
		//блок аннулирования
		
		$editing_user['can_annul']=$_res->instance->DocCanAnnul($editing_user['id'],$reason,$editing_user, $result)&&$au->user_rights->CheckAccess('w',1012);
		if(!$au->user_rights->CheckAccess('w',1012)) $reason='недостаточно прав для данной операции';
		$editing_user['can_annul_reason']=$reason;
		
		 
		
		$editing_user['can_restore']=$_res->instance->DocCanRestore($editing_user['id'],$reason,$editing_user)&&$au->user_rights->CheckAccess('w',1013);
			if(!$au->user_rights->CheckAccess('w',1013)) $reason='недостаточно прав для данной операции';
		
		
		
			//получим список тех, кто может снять утверждение заполнения
		$_usg1=new UsersSGroup;
		$usg1=$_usg1->GetUsersByRightArr('w', 1011);
		$sm1->assign('can_unconfirm_users',$usg1);
		$sm1->assign('can_unconfirm',$au->user_rights->CheckAccess('w',1011));
		
		
		 
		
		//блок утверждения!
		if(($editing_user['is_confirmed']==1)&&($editing_user['user_confirm_id']!=0)){
			$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['user_confirm_id']);
			$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.date("d.m.Y H:i:s",$editing_user['confirm_pdate']);
			
			 
			$sm1->assign('confirmer',$confirmer);
			
			$sm1->assign('is_confirmed_confirmer',$confirmer);
		}
		
		$can_confirm_price=false;
		if($editing_user['is_confirmed_done']==0){
			
			  
		  
		  if($editing_user['is_confirmed']==1){
			  if($au->user_rights->CheckAccess('w',1011)){
				  //есть права + сам утвердил
				  $can_confirm_price=true;	
			  }else{
				  $can_confirm_price=false;
			  }
		  }else{
			  //95
			  $can_confirm_price=$au->user_rights->CheckAccess('w',1010)&&in_array($editing_user['status_id'],$_editable_status_id)  ;
		  }
		}
		$sm1->assign('can_confirm',$can_confirm_price);
		
		  
		$reason='';
		
		
		$sm1->assign('can_unconfirm_by_document',(int)$_res->instance->DocCanUnconfirmShip($editing_user['id'],$reason));
		$sm1->assign('can_unconfirm_by_document_reason',$reason);
		
		
		
		//отвеств сотр-к
		$_user_s=new UserSItem;
		$user_s=$_user_s->GetItemById($editing_user['manager_id']);
		$editing_user['manager_string']=$user_s['name_s'];
		
		
		
	 
		
		 
		//контрагенты
		$_suppliers=new Lead_SupplierGroup;
		$sup=$_suppliers->GetItemsByIdArr($editing_user['lead_id']);
		$sm1->assign('suppliers', $sup);
		
		
	    
			//поставщики
		$_supplierstz=new TZ_SupplierGroup;
		$sup=$_supplierstz->GetItemsByIdArr($editing_user['id']);
		$sm1->assign('supplierstz', $sup);
		
		
		
	 
		
		
		$sm1->assign('can_modify', $can_modify);  
		   
		//доступ к соотв/не соотв ТЗ КП
		$sm1->assign('can_modify_fulfil', $au->user_rights->CheckAccess('w',1015)&&($editing_user['is_confirmed']==1));  
		//fulful_kp_1_confirmer
		if(($editing_user['fulful_kp']==1)&&($editing_user['fulfil_user_id']!=0)){
			$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['fulfil_user_id']);
			$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.date("d.m.Y H:i:s",$editing_user['fulfil_pdate']);
			
			 
			 
			$sm1->assign('fulful_kp_1_confirmer',$confirmer);
		}
		
		//fulful_kp_2_confirmer
		if(($editing_user['fulful_kp']==2)&&($editing_user['fulfil_user_id']!=0)){
			$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['fulfil_user_id']);
			$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.date("d.m.Y H:i:s",$editing_user['fulfil_pdate']);
			
			 
			 
			$sm1->assign('fulful_kp_2_confirmer',$confirmer);
		}   
		
		
		
		//технический отдел
		$sm1->assign('can_modify_fulfil1', $au->user_rights->CheckAccess('w',1027)&&($editing_user['is_confirmed']==1));  
		//fulful_kp_1_confirmer
		if(($editing_user['fulful_kp1']==1)&&($editing_user['fulfil_user_id1']!=0)){
			$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['fulfil_user_id1']);
			$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.date("d.m.Y H:i:s",$editing_user['fulfil_pdate1']);
			
			 
			 
			$sm1->assign('fulful_kp1_1_confirmer',$confirmer);
		}
		
		//fulful_kp_2_confirmer
		if(($editing_user['fulful_kp1']==2)&&($editing_user['fulfil_user_id1']!=0)){
			$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['fulfil_user_id1']);
			$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.date("d.m.Y H:i:s",$editing_user['fulfil_pdate1']);
			
			 
			 
			$sm1->assign('fulful_kp1_2_confirmer',$confirmer);
		} 
		
		
		
		
		$_dsi=new docstatusitem; $dsi=$_dsi->GetItemById($editing_user['status_id']);
		$editing_user['status_name']=$dsi['name'];
		$sm1->assign('bill', $editing_user);
		
		
		
		
		
		//реестр прикрепленных файлов
		$folder_id=0;
			 
			  $decorator=new DBDecorator;
			  
			  $decorator->AddEntry(new SqlOrdEntry('pdate',SqlOrdEntry::DESC));
			 
			$decorator->AddEntry(new UriEntry('id',$id));
			  
			  $decorator->AddEntry(new SqlEntry('folder_id',$folder_id, SqlEntry::E));
			 $decorator->AddEntry(new UriEntry('folder_id',$folder_id));
		
			  $navi_dec=new DBDecorator;
			  $navi_dec->AddEntry(new UriEntry('action',1));
			  
			  
			  
			  
			  $ffg=new TZFileGroup(1,  $id,  new FileDocFolderItem(1,  $id, new TZFileItem(1)));;
			  
			  $filetext=$ffg->ShowFiles('lead/lead_files_list'.$print_add.'.html', $decorator,0,10000,'ed_tz.php', 'tz_file.html', 'swfupl-js/tz_files.php',   
			  $can_modify,    
			 $can_modify, 
			 false , 
			  $folder_id, 
			  false, 
			false , 
			 false, 
			 false ,    
			  '',  
			  
			 false,  
			   $result, 
			   $navi_dec, 'file_' 
			   );
		
		
		$sm1->assign('files', $filetext);
	 	  
  
//	
	$sm1->assign('print_pdate', date("d.m.Y H:i:s"));
		//$username=$result['login'];
		$username=stripslashes($result['name_s']); //.' '.$username;	
		$sm1->assign('print_username',$username);
	 $html=$sm1->fetch('tz/edit_tz'.$print_add.'.html');


	
		  $tmp=time();
	
	$f=fopen(ABSPATH.'/tmp/'.$tmp.'.html','w');
	fputs($f, $html);
	fclose($f);
	
	$cd = "cd ".ABSPATH.'/tmp';
	exec($cd);
	
	
	//скомпилируем подвал
	$sm=new SmartyAdm;
	$sm->assign('print_pdate', date("d.m.Y H:i:s"));
		//$username=$result['login'];
		$username=stripslashes($result['name_s']); //.' '.$username;	
		$sm->assign('print_username',$username);
	$foot=$sm->fetch('plan_pdf/pdf_footer_unlogo.html');
	$ftmp='f'.time();
	
	$f=fopen(ABSPATH.'/tmp/'.$ftmp.'.html','w');
	fputs($f, $foot);
	fclose($f);
	
	
	
	$comand = "wkhtmltopdf-i386 --encoding windows-1251 --page-size A4 --margin-top 10mm --margin-bottom 27mm --margin-left 10mm --margin-right 10mm  --footer-html ".SITEURL."/tmp/".$ftmp.".html  ".SITEURL.'/tmp/'.$tmp.'.html'."  ".ABSPATH.'/tmp/'."$tmp.pdf";
	
 

exec($comand);
	
	
		 
	if(isset($_GET['send_email'])&&($_GET['send_email']==1)&&isset($_GET['email'])){
		$emails=$_GET['email'];
		$_addresses=explode(',',$emails);
		
		 
		$filename=ABSPATH.'/tmp/'."$tmp.pdf";
		 
		
		$filenames_to_send=array();
		$filenames_to_send[]=array(
			'fullname'=>$filename,
			'name'=>'ТЗ_'.$editing_user['code'].'.pdf'
		
		);
		
		
		if($_GET['with_files']==1){
			//получим файлы, прикрепленные к задаче
			$_ree_fi=new TZFileItem;
			//$_hi_fi=new Lead_HistoryFileItem;
			
			$sql='select * from tz_file where bill_id="'.$id.'" ';
	
			$set=new mysqlset($sql);
			$rs=$set->GetResult();	
			$rc=$set->GetResultnumrows();
		
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				$filenames_to_send[]=array(
					'fullname'=>$_ree_fi->GetStoragePath().$f['filename'],
					'name'=>$f['orig_name']
				
				);
			}
			
			 
		}
		 
		
		 $_filenames=array();
		foreach($filenames_to_send as $k=>$v) $_filenames[]=$v['name'];
		
		//var_dump($_filenames); 
		 
		$org=$_orgitem->Getitembyid($result['org_id']);
		$opf=$_opf->getitembyid($org['opf_id']);
		
		
		$was_sent_to_supplier=false;
		//ищем все адреса почты контрагентов-поставщиков, среди них найдем каждый отправляемый
		//если было хотя бы одно совпадение - то останавливать счетчики 0,1 в ТЗ И ЛИДЕ, включать счетчики 3 в ТЗ И ЛИДЕ
		$_supplierstz=new TZ_SupplierGroup;
		$sup=$_supplierstz->GetItemsByIdArr($editing_user['id']);
		
		$_scg=new SupplierContactGroup;
		$sprav_emails=array();
		foreach($sup as $ks=>$vs){
			$scg=$_scg->GetItemsByIdArr($vs['supplier_id']);
			
			foreach($scg as $kc=>$vc){
				foreach($vc['data'] as $kd=>$vd) if($vd['kind_id']==5) $sprav_emails[]=$vd['value']; 	
			}
		}
		
		
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
			$body = "<div>Уважаемый(ая) %contact_name%!</div> <div>&nbsp;</div> <div><i>Это сообщение сформировано автоматически, просьба не отвечать на него.</i></div> <div>&nbsp;</div><div>Просим Вас рассмотреть возможность поставки в наш адрес следующего оборудования.</div> <div>&nbsp;</div> <div>Отправляем Вам следующие документы: %docs%.</div>   <div>С уважением, %manager% <br> Компания %opf_name% %company_name%.</div>
 "; 
 
 			
			 $_mi=new UserSItem;
			 $mi=$_mi->GetItemById($editing_user['manager_id']);
			 	
			 $contact_str='';
			 
			 $contact_str.=$mi['name_s'];
			 
			 
			
			 
			 $_di=new UserDepItem; $_mdi=new UserMainDepItem;
			 $mdi=$_mdi->GetItemById($mi['main_department_id']);
			 $di=$_di->GetItemById($mi['department_id']);
			 //if($mdi!==false) $contact_str.='<br>'.$mdi['name'];
			 if($di!==false) $contact_str.='<br>'.$di['name'];
			  $contact_str.='<br>'.$mi['position_s'];
			 
			 $rg=new UserContactDataGroup;
			 foreach($rg->GetItemsByIdArr($editing_user['manager_id']) as $k=>$v){
				$contact_str.='<div>'.$v['pc_name'].': '.$v['value']."</div>"; 
			 }
 			$body=str_replace('%manager%',  $contact_str,$body);
			
			$body=str_replace('%contact_name%',  $user_name,$body);
			$body=str_replace('%docs%', implode(', ',$_filenames),  $body);
			$body=str_replace('%company_name%', $org['full_name'],  $body);
			$body=str_replace('%opf_name%', $opf['name'],  $body);
			
			
		
			$mail->SetFrom(FEEDBACK_EMAIL, $opf['name'].' '.$org['full_name']);
		
			  
		
			$mail->AddAddress(trim($email),  $email);
		
			$mail->Subject = "запрос от ".$opf['name'].' '.$org['full_name']; 
			$mail->Body=$body;
			
			//echo $body; die();
			
			foreach($filenames_to_send as $k=>$v) $mail->AddAttachment($v['fullname'], $v['name']);  
			 
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
			
			$addition='';
			if(in_array($email, $sprav_emails)) {
				$was_sent_to_supplier=$was_sent_to_supplier||true;
				$addition='ТЗ было отправлено поставщику: ';
				
			}
			
		 
		 	$log->PutEntry($result['id'],'отправил на электронную почту pdf-версию ТЗ',NULL,1009, NULL, $addition.'ТЗ № '.$editing_user['code'].', адрес эл. почты '.$email,$id);
			
			//внесем автомат. комментарии об отправке ТЗ
			$_kni=new TzNotesItem;
			$notes_params=array();
			$notes_params['is_auto']=1;
			$notes_params['user_id']=$editing_user['id'];
			$notes_params['pdate']=time();
			$notes_params['posted_user_id']=0; //$result['id'];
		  
		 
			$notes_params['note']='Автоматическое примечание: '.$addition.'pdf-форма ТЗ была отправлена на электронную почту '.$email.' '.date('d.m.Y H:i:s').' '.$paste.' пользователем '.SecStr($result['name_s'].' '.$result['login']);
			$_kni->Add($notes_params);
			
			
		}	
		 
		
		
		 //остановим счетчики 0,1  лида, ТЗ
		if(($was_sent_to_supplier)){
			$_wi=new TZ_WorkingItem;
			$_wi->Add(array('sched_id'=>$id, 'kind_id'=>0, 'in_or_out'=>1, 'pdate'=>time()));
			$_wi->Add(array('sched_id'=>$id, 'kind_id'=>1, 'in_or_out'=>1, 'pdate'=>time()));
				  
				  
			$_wi=new Lead_WorkingItem;
			$_wi->Add(array('sched_id'=>$editing_user['lead_id'], 'kind_id'=>0, 'in_or_out'=>1, 'pdate'=>time()));
			$_wi->Add(array('sched_id'=>$editing_user['lead_id'], 'kind_id'=>1, 'in_or_out'=>1, 'pdate'=>time()));
			//запустим счетчик 3 тз, лида
			$_wi=new TZ_WorkingItem;
			$_wi->Add(array('sched_id'=>$id, 'kind_id'=>3, 'in_or_out'=>0, 'pdate'=>time()));
				  
				  
			$_wi=new Lead_WorkingItem;
			$_wi->Add(array('sched_id'=>$editing_user['lead_id'], 'kind_id'=>3, 'in_or_out'=>0, 'pdate'=>time()));
		
		}
		
		 
		//перейти в карту  
		/*if(!isset($_GET['doClose'])){
			header("Location: ed_sched.php?action=1&id=".$editing_user['id'].'&from_begin=1');
		}else{*/
			/*echo '<script type="text/javascript"> alert("Запись планировщика была отправлена на адреса электронной почты: '.$_GET['email'].'"); window.close();</script>';	*/
			
			$sm=new SmartyAdm;
			
			$txt='';
			$txt.='<div><strong>ТЗ было отправлено на следующие адреса:</strong></div>';
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
			
			//отметим, что оно не новое
			$_item=new TZ_Item;
			$_item->Edit($id, array('was_sent'=>1));
			
			
			
		//}
		//die();	
		
		
	}else{
		
	
		$log->PutEntry($result['id'],'получил pdf-версию ТЗ',NULL,1009, NULL, 'ТЗ № '.$editing_user['code'],$id);
		 
		header('Content-type: application/pdf');
		header('Content-Disposition: attachment; filename="ТЗ_'.$editing_user['code'].'.pdf'.'"');
		readfile(ABSPATH.'/tmp/'.$tmp.'.pdf');
	}
	


	unlink(ABSPATH.'/tmp/'.$tmp.'.pdf');
	unlink(ABSPATH.'/tmp/'.$tmp.'.html');
	unlink(ABSPATH.'/tmp/'.$ftmp.'.html');
	
	exit;	
?>